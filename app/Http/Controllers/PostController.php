<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::with('user')->latest()->get();
        return view('posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('posts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'caption'=> 'required',
            'image'=> 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imagePath = $request->file('image')->store('uploads', 'public');

        auth()->user()->posts()->create([
            'caption'=> $data['caption'],
            'image_path'=> $imagePath,
        ]);

        return redirect('/profile/' . auth()->user()->id);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return view('posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        // Check if the authenticated user is the same as the post user
        if(auth()->id() !== $post->user_id) {
            abort(403, "Unauthorized action!");
        }

        return view('posts.edit', compact('post'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        // Check ifthe authenticated user is the same as the post user
        if (auth()->id() !== $post->user_id) {
            abort(403, "Unauthorized action!");
        }

        $data = $request->validate([
            'caption'=> 'required',
        ]);

        $post->update($data);

        return redirect('/posts/' . $post->id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        // Check if the authenticated user is the same as the post user
        if(auth()->id() !== $post->user_id) {
            abort(403, "Unauthorized action!");
        }

        // Delete the image file
        Storage::disk('public')->delete($post->image_path);

        // Delete the post
        $post->delete();

        return redirect('/profile/' . auth()->user()->id);
    }
}
