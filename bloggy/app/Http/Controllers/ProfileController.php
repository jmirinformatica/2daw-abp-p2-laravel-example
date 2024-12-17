<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        /**************************************************/
        $request->user()->loadCount('posts');
        $request->user()->loadCount('comments');
        /**************************************************/
        
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }
        
        /**************************************************/
        if ($request->has('avatar')) {
            $upload = $request->file('avatar');
            $uploadName = $upload->getClientOriginalName();
            $uploadSize = $upload->getSize();
            Log::debug("Storing file '{$uploadName}' ($uploadSize)...");
            $path = $upload->storeAs(
                "avatars/{$request->user()->id}", // Path
                $uploadName,    // Filename
                'public'        // Disk
            );
            Log::debug("Uploaded file stored at $path");
            $request->user()->avatar = Storage::url($path);
        }
        /**************************************************/

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
