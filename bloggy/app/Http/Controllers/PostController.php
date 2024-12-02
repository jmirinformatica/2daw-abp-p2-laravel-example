<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PostStoreRequest;
use App\Http\Requests\PostUpdateRequest;

class PostController extends Controller
{
    private bool $_pagination = true;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->user()->cannot('viewAny', Post::class)) {
            abort(403);
        }
        
        return $this->_index($request);
    }

    /**
     * Display a listing of the resource created by user.
     */
    public function myIndex(Request $request)
    {
        if ($request->user()->cannot('create', Post::class)) {
            abort(403);
        }

        return $this->_index($request, true);
    }

    private function _index(Request $request, bool $creator = false)
    {
        // Order and count
        $collectionQuery = Post::withCount('comments')
            ->orderBy('created_at', 'desc');
        
        // Filter?
        if ($search = $request->get('search')) {
            $collectionQuery->where('title', 'like', "%{$search}%");
        }

        if ($creator) {
            $collectionQuery->where('author_id', '=', $request->user()->id);
        }
        
        // Pagination
        $posts = $this->_pagination 
            ? $collectionQuery->paginate(5)->withQueryString() 
            : $collectionQuery->get();
        
        return view("posts.index", [
            "posts" => $posts,
            "search" => $search
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        if ($request->user()->cannot('create', Post::class)) {
            abort(403);
        }

        return view("posts.create");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PostStoreRequest $request)
    {
        // Validar dades del formulari
        $validatedData = $request->validated();
        
        // Desar dades a BD
        Log::debug("Saving post at DB...");
        $post = Post::create([
            'title'     => $validatedData['title'],
            'body'      => $validatedData['body'],
            'author_id' => auth()->user()->id,
        ]);

        if ($post) {
            // Patró PRG amb missatge d'èxit
            return redirect()->route('posts.show', $post)
                ->with('success', __(':resource successfully saved', [
                    'resource' => __('Post')
                ]));
        } else {
            // Patró PRG amb missatge d'error
            return redirect()->route("posts.create")
                ->with('error', __('ERROR saving data'));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Post $post)
    {
        if ($request->user()->cannot('view', $post)) {
            abort(403);
        }

        $post->loadCount('comments');

        return view("posts.show", [
            'post'     => $post,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Post $post)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PostUpdateRequest $request, Post $post)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Post $post)
    {
        //
    }
}
