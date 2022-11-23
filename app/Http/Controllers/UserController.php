<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserCollection;
use App\Http\Requests\StoreUserRequest;

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


}
