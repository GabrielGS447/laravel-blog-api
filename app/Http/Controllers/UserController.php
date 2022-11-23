<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserCollection;
use Illuminate\Http\Request;

class UserController extends Controller
{
  public function index() {
    return new UserCollection(User::all());
  }

  public function show(User $user) {
    return new UserResource($user);
  }
}
