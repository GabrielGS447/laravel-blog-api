<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class GetUserByIdTests extends TestCase {
  use RefreshDatabase;

  public function test_get_user_by_id_without_including_posts() {
    $users = User::factory()->create();

    $response = $this->getJson("/api/users/{$users->id}");

    $response
      ->assertOk()
      ->assertJson(fn (AssertableJson $json) =>
        $json
          ->where('data.id', $users->id)
          ->where('data.displayName', $users->display_name)
          ->where('data.email', $users->email)
          ->missing('data.password')
          ->missing('data.posts')
          ->etc()
      );
  }

  public function test_get_user_by_id_including_posts() {
    $users = User::factory()->hasPosts(3)->create();

    $response = $this->getJson("/api/users/{$users->id}?includePosts=true");

    $response
      ->assertOk()
      ->assertJson(fn (AssertableJson $json) =>
        $json
          ->where('data.id', $users->id)
          ->where('data.displayName', $users->display_name)
          ->where('data.email', $users->email)
          ->missing('data.password')
          ->has('data.posts', 3)
          ->has('data.posts.1', fn ($json) => 
            $json
              ->where('id', $users->posts[1]->id)
              ->where('title', $users->posts[1]->title)
              ->where('body', $users->posts[1]->body)
              ->missing('user_id')
              ->etc()
          )
          ->etc()
      );
  }

  public function test_fail_to_get_user_by_id_when_its_not_found() {
    $response = $this->getJson('/api/users/1');

    $response
      ->assertNotFound()
      ->assertJson(fn (AssertableJson $json) =>
        $json
          ->where('message', 'Resource not found')
          ->etc()
      );
  }
}
