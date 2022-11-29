<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class GetPostByIdTests extends TestCase {
  use RefreshDatabase;

  public function test_get_post_by_id_without_including_user() {
    /** @var User */
    $user = User::factory()->create();
    $post = $user->posts()->create([
      'title' => 'My first post',
      'body' => 'This is my first post',
    ]);

    $response = $this->getJson("/api/posts/{$post->id}");

    $response
      ->assertOk()
      ->assertJson(fn (AssertableJson $json) =>
        $json
          ->where('data.id', $post->id)
          ->where('data.title', $post->title)
          ->where('data.body', $post->body)
          ->where('data.userId', $user->id)
          ->missing('data.user')
          ->etc()
      );
  }

  public function test_get_post_by_id_including_user() {
    /** @var User */
    $user = User::factory()->create();
    $post = $user->posts()->create([
      'title' => 'My first post',
      'body' => 'This is my first post',
    ]);

    $response = $this->getJson("/api/posts/{$post->id}?includeUser=true");

    $response
      ->assertOk()
      ->assertJson(fn (AssertableJson $json) =>
        $json
          ->where('data.id', $post->id)
          ->where('data.title', $post->title)
          ->where('data.body', $post->body)
          ->where('data.userId', $user->id)
          ->has('data.user', fn ($json) =>
            $json
              ->where('id', $user->id)
              ->where('displayName', $user->display_name)
              ->where('email', $user->email)
              ->missing('password')
              ->etc()
          )
          ->etc()
      );
  }

  public function test_fail_to_get_post_by_id_when_its_not_found() {
    $response = $this->getJson('/api/posts/1');

    $response
      ->assertNotFound()
      ->assertJson(fn (AssertableJson $json) =>
        $json
          ->where('message', 'Resource not found')
          ->etc()
      );
  }
}
