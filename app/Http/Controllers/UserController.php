<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserCollection;

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
}
