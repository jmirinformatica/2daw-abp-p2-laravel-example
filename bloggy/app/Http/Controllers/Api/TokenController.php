<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\UserStoreRequest;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

use App\Models\User;
use App\Models\Role;
use App\Http\Resources\UserResource;

class TokenController extends Controller
{
    public function user(Request $request) 
    {
        //$user = User::where('email', $request->user()->email)->first();

        return response()->json([
            "success" => true,
            "user"    => new UserResource($request->user()),
        ]);
    }

    public function register(UserStoreRequest $request) 
    {
        $validatedData = $request->validated();

        $user = new User([
            "name"      => $validatedData["name"],
            "email"     => $validatedData["email"],
            "password"  => Hash::make($validatedData["password"]),
        ]);
        $user->save();

        if ($request->has('avatar')) {
            $upload = $request->file('avatar');
            $user->uploadAvatar($upload);
            $user->save();
        }
        
        return $this->_generateTokenResponse($user);
    }

    public function login(Request $request) 
    {
        $credentials = $request->validate([
            'email'     => 'required|email',
            'password'  => 'required',
        ]);
 
        if (Auth::attempt($credentials)) {
            // Get user
            $user = User::where([
                ["email", "=", $credentials["email"]]
            ])->firstOrFail();
            // Revoke all old tokens
            $user->tokens()->delete();
            // Generate new token
            return $this->_generateTokenResponse($user); 
        } else {
            return response()->json([
                "success" => false,
                "message" => "Invalid login credentials"
            ], 401);
        }
    }

    public function logout(Request $request) 
    {
        // Revoke token used to authenticate current request...
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            "success" => true,
            "message" => "Current token revoked",
        ]);
    }

    protected function _generateTokenResponse(User $user)
    {
        $token = $user->createToken("authToken");

        return response()->json([
            "success"   => true,
            "authToken" => $token->plainTextToken,
            "tokenType" => "Bearer"
        ], 200);
    }
}