<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\Comment;
use App\Http\Resources\CommentResource;
use App\Http\Requests\CommentStoreRequest;
use App\Http\Resources\PaginateCollection;

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
            abort(403);
        }

        $query = Comment::where("post_id", "=", $pid);
        $paginate = $request->query('paginate', 0);
        $data = $paginate ? $query->paginate() : $query->get();
        
        return response()->json([
            'success' => true,
            'data'    => new PaginateCollection($data, CommentResource::class)
        ], 200);
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
            abort(403);
        }

        $validatedData = $request->validated();
        
        $comment = Comment::where([
            ['post_id', '=', $pid],
            ['author_id', '=', auth()->user()->id],
        ])->first();
        
        if ($comment) {
            return response()->json([
                'success'  => false,
                'message' => 'Comment already created'
            ], 400);
        } else {
            Log::debug("Saving comment at DB...");
            $comment = Comment::create([
                "comment"    => $validatedData["comment"],
                "post_id"  => $pid,
                "author_id" => auth()->user()->id,
            ]);
            \Log::debug("DB storage OK");
    
            return response()->json([
                'success' => true,
                'data'    => new CommentResource($comment)
            ], 201);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $pid
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($pid, $id)
    {
        $comment = Comment::where([
            ['id', '=', $id],
            ['post_id', '=', $pid],
        ])->first();
        
        if ($comment) {
            if ($request->user()->cannot('view', $comment)) {
                abort(403);
            }
            return response()->json([
                'success' => true,
                'data'    => $comment
            ], 200);
        } else {
            return response()->json([
                'success'  => false,
                'message' => 'Comment not found'
            ], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $pid
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($pid, $id)
    {
        $comment = Comment::where([
            ['id', '=', $id],
            ['post_id', '=', $pid],
        ])->first();

        if ($comment) {
            if ($request->user()->cannot('delete', $comment)) {
                abort(403);
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
