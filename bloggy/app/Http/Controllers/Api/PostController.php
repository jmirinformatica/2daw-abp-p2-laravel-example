<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Http\Requests\PostStoreRequest;
use App\Http\Requests\PostUpdateRequest;

use App\Models\Post;
use App\Models\File;
use App\Models\Like;
use App\Http\Resources\PostResource;
use App\Http\Resources\PostCollection;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Post::withCount(['comments']);

        // Filters
        if ($body = $request->get('body')) {
            $query->where('body', 'like', "%{$body}%");
        }
        
        if ($author_id = $request->get('author_id')) {
            $query->where('author_id', $author_id);
        }

        // Pagination
        $paginate = $request->get('paginate', 0);
        $data = $paginate ? $query->paginate() : $query->get();
        
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
            abort(403);
        }

        $validatedData = $request->validated();

        Log::debug("Saving post at DB...");
        $post = Post::create([
            'title'     => $validatedData['title'],
            'body'      => $validatedData['body'],
            'status_id' => $validatedData['status_id'],
            'author_id' => $request->user()->id,
        ]);
        Log::debug("DB storage OK");
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
    public function show($id)
    {
        $post = Post::find($id);
        
        if ($post) {
            if ($request->user()->cannot('view', $post)) {
                abort(403);
            }
            $post->loadCount(['likes','comments']);
            return response()->json([
                'success' => true,
                'data'    => new PostResource($post)
            ], 200);
        } else {
            return response()->json([
                'success'  => false,
                'message' => 'Post not found'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  PostUpdateRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(PostUpdateRequest $request, $id)
    {
        $post = Post::find($id);

        if (empty($post)) {
            return response()->json([
                'success'  => false,
                'message' => 'Post not found'
            ], 404);
        }
        
        if ($request->user()->cannot('update', $post)) {
            abort(403);
        }
        $validatedData = $request->validated();
        $upload        = $request->file('upload');
        
        if (is_null($upload) || $post->file->diskSave($upload)) {
            Log::debug("Updating DB...");
            if (!empty($validatedData['body'])) {
                $post->body = $validatedData['body'];
            }
            if (!empty($validatedData['latitude'])) {
                $post->latitude = $validatedData['latitude'];
            }
            if (!empty($validatedData['longitude'])) {
                $post->longitude = $validatedData['longitude'];
            }
            if (!empty($validatedData['visibility'])) {
                $post->visibility_id = $validatedData['visibility'];
            }
            $post->save();
            Log::debug("DB storage OK");
            return response()->json([
                'success' => true,
                'data'    => new PostResource($post)
            ], 200);
        } else {
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
    public function destroy($id)
    {
        $post = Post::find($id);

        if (empty($post)) {
            return response()->json([
                'success'  => false,
                'message' => 'Post not found'
            ], 404);
        }

        if ($request->user()->cannot('delete', $post)) {
            abort(403);
        }
        $post->delete();
        $post->file->diskDelete();
        
        return response()->json([
            'success' => true,
            'data'    => new PostResource($post)
        ], 200);
    }
}
