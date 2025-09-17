<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     path="/api/posts",
     *     summary="Get list of posts",
     *     tags={"Posts"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
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
     * @OA\Post(
     *     path="/api/posts",
     *     summary="Create new post",
     *     tags={"Posts"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "content", "category"},
     *             @OA\Property(property="title", type="string", maxLength=120, example="My First Post"),
     *             @OA\Property(property="content", type="string", example="This is the content of the post."),
     *             @OA\Property(property="category", type="string", example="Tech"),
     *             @OA\Property(
     *                 property="tags",
     *                 type="array",
     *                 @OA\Items(type="string", minLength=4, maxLength=20, example="laravel")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Post created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="My First Post"),
     *             @OA\Property(property="content", type="string", example="This is the content of the post."),
     *             @OA\Property(property="category", type="string", example="Tech"),
     *             @OA\Property(property="tags", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
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
    /**
     * @OA\Get(
     *     path="/api/posts/{id}",
     *     operationId="getPostById",
     *     tags={"Posts"},
     *     summary="Get a single post by ID",
     *     description="Returns a single post resource",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the post to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Post not found"
     *     )
     * )
     */
    public function show(Post $post)
    {
        return $post;
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * @OA\Put(
     *     path="/api/posts/{id}",
     *     operationId="updatePost",
     *     tags={"Posts"},
     *     summary="Update an existing post",
     *     description="Updates a post resource with optional fields",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the post to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="title",
     *                 type="string",
     *                 maxLength=120,
     *                 example="Updated post title"
     *             ),
     *             @OA\Property(
     *                 property="content",
     *                 type="string",
     *                 example="Updated content of the post."
     *             ),
     *             @OA\Property(
     *                 property="category",
     *                 type="string",
     *                 example="Tech"
     *             ),
     *             @OA\Property(
     *                 property="tags",
     *                 type="array",
     *                 @OA\Items(
     *                     type="string",
     *                     minLength=4,
     *                     maxLength=20,
     *                     example="Laravel"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post updated successfully",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid input"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 additionalProperties=@OA\Property(type="array", @OA\Items(type="string"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Post not found"
     *     )
     * )
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
    /**
     * @OA\Delete(
     *     path="/api/posts/{id}",
     *     operationId="deletePost",
     *     tags={"Posts"},
     *     summary="Delete a post",
     *     description="Deletes a post resource by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the post to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Post deleted successfully, no content returned"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Post not found"
     *     )
     * )
     */
    public function destroy(Post $post)
    {
        $post->delete();

        return response(status: 204);
    }
}
