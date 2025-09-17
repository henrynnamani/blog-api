<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $posts = Post::query()->when($search, function ($query, $search) {
            $query->where('title', 'like', "%{$search}%")->orWhere('content', 'like', "%{$search}%");
        })->get();

        return $posts;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "title" => "required|max:120",
            "content" => "required|string",
            "category" => "required|string",
            "tags" => "array",
            "tags.*" => "string|min:4|max:20"
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid input',
                'errors' => $validator->errors()
            ], 400);
        }

        return Post::create($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return $post;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        $validator = Validator::make($request->all(), [
            "title" => "sometimes|max:120",
            "content" => "sometimes|string",
            "category" => "sometimes|string",
            "tags" => "array",
            "tags.*" => "string|min:4|max:20"
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid input',
                'errors' => $validator->errors()
            ], 400);
        }

        $post->fill($request->only(['title', 'content', 'category', 'tags']));
        $post->save();

        return $post;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        $post->delete();

        return response(status: 204);
    }
}
