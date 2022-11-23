<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserCollection;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;

class UserController extends Controller
{
  public function index() {
    $includePosts = request()->query('includePosts', false);

    $users = User::all();

    if ($includePosts) {
      $users->load('posts');
    }

    return new UserCollection($users);
  }

  public function show(User $user) {
    $includePosts = request()->query('includePosts', false);

    if ($includePosts) {
      return new UserResource($user->loadMissing('posts'));
    }

    return new UserResource($user);
  }

  public function store(StoreUserRequest $request) {
    return new UserResource(User::create($request->all()));
  }

  public function update(UpdateUserRequest $request, User $user) {
    $user->update($request->all());

    return new UserResource($user);
  }

  public function destroy(User $user) {
    $user->delete();

    return response()->json(status: 204);
  }
}
