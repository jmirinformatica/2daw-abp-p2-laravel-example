<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\Comment;
use App\Http\Resources\CommentResource;
use App\Http\Resources\CommentCollection;
use App\Http\Requests\CommentStoreRequest;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  int  $pid
     * @return \Illuminate\Http\Response
     */
    public function index($pid, Request $request)
    {
        if ($request->user()->cannot('viewAny', Comment::class)) {
            return response()->json([
                'success'  => false,
                'message' => 'Forbidden access'
            ], 403);
        }

        Log::debug("Query post $pid comments at DB...");
        $query = Comment::where("post_id", "=", $pid);

        // Pagination?
        $paginate = $request->query('paginate', 0);
        $data = $paginate ? $query->paginate() : $query->get();
        
        Log::debug("DB operation OK");
        return new CommentCollection($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  int  $pid
     * @param  CommentStoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store($pid, CommentStoreRequest $request)
    {
        if ($request->user()->cannot('create', Comment::class)) {
            return response()->json([
                'success'  => false,
                'message' => 'Forbidden access'
            ], 403);
        }

        $validatedData = $request->validated();
        
        $comment = Comment::where([
            ['post_id', '=', $pid],
            ['author_id', '=', auth()->user()->id],
        ])->first();
        
        if ($comment) {
            return response()->json([
                'success'  => false,
                'message' => 'User has already commented this post'
            ], 400);
        } else {
            Log::debug("Saving comment at DB...");
            $comment = Comment::create([
                "comment"   => $validatedData["comment"],
                "post_id"   => $pid,
                "author_id" => auth()->user()->id,
            ]);
            \Log::debug("DB operation OK");
    
            return response()->json([
                'success' => true,
                'data'    => new CommentResource($comment)
            ], 201);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $pid
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($pid, $id, Request $request)
    {
        $comment = Comment::find($id);

        if ($comment) {
            if ($request->user()->cannot('delete', $comment)) {
                return response()->json([
                'success'  => false,
                'message' => 'Forbidden access'
            ], 403);
            }
            if ($comment->post_id != $pid) {
                return response()->json([
                    'success'  => false,
                    'message' => 'Comment and post mismatch'
                ], 400);
            }
            $comment->delete();
            return response()->json([
                'success' => true,
                'data'    => new CommentResource($comment)
            ], 200);
        } else {
            return response()->json([
                'success'  => false,
                'message' => 'Comment not found'
            ], 404);
        }
    }
}
