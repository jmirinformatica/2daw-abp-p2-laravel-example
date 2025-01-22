<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\Post;
use App\Models\Like;
use App\Http\Resources\PostResource;
use App\Http\Resources\PostCollection;
use App\Http\Requests\PostStoreRequest;
use App\Http\Requests\PostUpdateRequest;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        Log::debug("Query posts at DB...");
        $query = Post::withCount(['comments','likes']);

        // Filters?
        if ($body = $request->get('body')) {
            $query->where('body', 'like', "%{$body}%");
        }
        if ($author_id = $request->get('author_id')) {
            $query->where('author_id', $author_id);
        }

        // Pagination?
        $paginate = $request->get('paginate', 0);
        $data = $paginate ? $query->paginate() : $query->get();
        
        Log::debug("DB operation OK");
        return new PostCollection($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  PostStoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PostStoreRequest $request)
    {
        if ($request->user()->cannot('create', Post::class)) {
            return response()->json([
                'success'  => false,
                'message' => 'Forbidden access'
            ], 403);
        }

        $validatedData = $request->validated();

        Log::debug("Saving post at DB...");
        $post = Post::create([
            'title'     => $validatedData['title'],
            'body'      => $validatedData['body'],
            'status_id' => $validatedData['status_id'],
            'author_id' => $request->user()->id,
        ]);
        Log::debug("DB operation OK");
        return response()->json([
            'success' => true,
            'data'    => new PostResource($post)
        ], 201);
    }
    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        Log::debug("Query post {$id} at DB...");
        $post = Post::find($id);
        Log::debug("DB operation OK");

        if (empty($post)) {
            return response()->json([
                'success'  => false,
                'message' => 'Post not found'
            ], 404);    
        }
        
        $post->loadCount(['comments','likes']);

        return response()->json([
            'success' => true,
            'data'    => new PostResource($post)
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  PostUpdateRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id, PostUpdateRequest $request)
    {
        $post = Post::find($id);

        if (empty($post)) {
            return response()->json([
                'success'  => false,
                'message' => 'Post not found'
            ], 404);
        }
        
        if ($request->user()->cannot('update', $post)) {
            return response()->json([
                'success'  => false,
                'message' => 'Forbidden access'
            ], 403);
        }

        $validatedData = $request->validated();

        Log::debug("Updating post {$id} at DB...");
        if (!empty($validatedData['title'])) {
            $post->title = $validatedData['title'];
        }
        if (!empty($validatedData['body'])) {
            $post->body = $validatedData['body'];
        }
        if (!empty($validatedData['status_id'])) {
            $post->status_id = $validatedData['status_id'];
        }

        if ($post->save()) {
            Log::debug("DB operation OK");
            return response()->json([
                'success' => true,
                'data'    => new PostResource($post)
            ], 200);
        } else {
            Log::debug("DB operation ERROR");
            return response()->json([
                'success'  => false,
                'message' => 'Error uploading file'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        $post = Post::find($id);

        if (empty($post)) {
            return response()->json([
                'success'  => false,
                'message' => 'Post not found'
            ], 404);
        }

        if ($request->user()->cannot('delete', $post)) {
            return response()->json([
                'success'  => false,
                'message' => 'Forbidden access'
            ], 403);
        }

        Log::debug("Deleting post {$id} from DB...");
        if ($post->delete()) {
            Log::debug("DB operation OK");
            return response()->json([
                'success' => true,
                'data'    => new PostResource($post)
            ], 200);
        } else {
            Log::debug("DB operation ERROR");
            return response()->json([
                'success'  => false,
                'message' => 'Error deleting file'
            ], 500);
        }
    }

    /**
     * Do like
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function like($id, Request $request) 
    {
        $post = Post::find($id);

        if (empty($post)) {
            return response()->json([
                'success'  => false,
                'message' => 'Post not found'
            ], 404);
        }

        if ($request->user()->cannot('like', $post)) {
            return response()->json([
                'success'  => false,
                'message' => 'Forbidden access'
            ], 403);
        }

        try {
            $like = Like::create([
                'user_id'  => $request->user()->id,
                'post_id' => $id
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => "Like already exists"
            ], 400);
        }
        
        return response()->json([
            'success' => true,
            'data'    => $like
        ], 200);
    }

    /**
     * Undo like
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function unlike($id, Request $request)
    {
        $post = Post::find($id);

        if (empty($post)) {
            return response()->json([
                'success'  => false,
                'message' => 'Post not found'
            ], 404);
        }

        if ($request->user()->cannot('like', $post)) {
            return response()->json([
                'success'  => false,
                'message' => 'Forbidden access'
            ], 403);
        }

        $like = Like::where([
            ['user_id', '=', $request->user()->id],
            ['post_id', '=', $id],
        ])->first();

        if ($like) {
            return response()->json([
                'success' => $like->delete(),
                'data'    => $like
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => "Like not exists"
            ], 404); 
        }
    }    
}
