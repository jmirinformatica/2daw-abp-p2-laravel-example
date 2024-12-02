<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

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
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        //
    }
}
