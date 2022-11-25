<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class AuthController extends Controller
{
  public function login (Request $request) {
    $credentials = $request->only('email', 'password');

    if(!auth()->attempt($credentials)) {
      return response()->json([
        'message' => 'Invalid credentials'
      ], 401);
    }

    $user = $request->user();

    return response()->json([
      'data' => [
        'user' => new UserResource($user),
        'access_token' => $user->createToken('auth_token')->plainTextToken,
        'token_type' => 'Bearer',
      ]
    ]);
  }
}
