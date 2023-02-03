<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostCollection;
use App\Http\Resources\PostResource;
use App\Models\Post;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $includeUser = request()->query('includeUser', false);

        $posts = Post::paginate(10);

        if ($includeUser) {
            $posts->load('user');
        }

        return new PostCollection($posts);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
        $includeUser = request()->query('includeUser', false);

        if ($includeUser) {
            return new PostResource($post->loadMissing('user'));
        }

        return new PostResource($post);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorePostRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePostRequest $request)
    {
        return new PostResource(Post::create($request->all()));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePostRequest  $request
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
        if ($post['user_id'] !== $request->user()->id) {
            return response()->json([
                'message' => 'You are not authorized to update this post.',
            ], 403);
        }

        $post->update($request->all());

        return new PostResource($post);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        if ($post['user_id'] !== request()->user()->id) {
            return response()->json([
                'message' => 'You are not authorized to update this post.',
            ], 403);
        }

        $post->delete();

        return response()->json(status: 204);
    }
}
