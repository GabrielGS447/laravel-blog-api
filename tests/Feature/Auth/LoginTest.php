<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class LoginTests extends TestCase {
  use RefreshDatabase;

  public function test_login_with_valid_credentials() {
    $user = User::factory()->create([ 'password' => bcrypt('password') ]);

    $response = $this->postJson('/api/auth/login', [
      'email' => $user->email,
      'password' => 'password'
    ]);

    $response
      ->assertOk()
      ->assertJson(fn (AssertableJson $json) =>
        $json->where('data.user.id', $user->id)
          ->where('data.user.displayName', $user->display_name)
          ->where('data.user.email', $user->email)
          ->missing('data.user.password')
          ->has('data.access_token')
          ->where('data.token_type', 'Bearer')
          ->etc()
      );
  }

  public function test_fail_to_login_with_invalid_credentials() {
    $user = User::factory()->create([ 'password' => bcrypt('password') ]);

    $response = $this->postJson('/api/auth/login', [
      'email' => $user->email,
      'password' => 'wrong-password'
    ]);

    $response
      ->assertStatus(401)
      ->assertJson(fn (AssertableJson $json) =>
        $json->where('message', 'Invalid credentials')
      );
  }
}
