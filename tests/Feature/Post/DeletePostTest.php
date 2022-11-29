<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class DeletePostTests extends TestCase {
  use RefreshDatabase;

  public function test_delete_post() {
    /** @var User */
    $user = User::factory()->create();
    $post = $user->posts()->create([
      'title' => 'My first post',
      'body' => 'This is my first post',
    ]);

    $response = $this->actingAs($user)->deleteJson("/api/posts/{$post->id}");

    $response->assertNoContent();

    $this->assertDatabaseMissing('posts', [
      'id' => $post->id,
      'title' => $post->title,
      'body' => $post->body,
      'user_id' => $user->id,
    ]);
  }

  public function test_fail_to_delete_post_when_its_not_found() {
    /** @var User */
    $user = User::factory()->create();

    $response = $this->actingAs($user)->deleteJson("/api/posts/1");

    $response
      ->assertNotFound()
      ->assertJson(fn (AssertableJson $json) =>
        $json
          ->where('message', 'Resource not found')
          ->etc()
      );
  }

  public function test_fail_to_delete_post_when_it_doesnt_belong_to_user() {
    /** @var User */
    $userOne = User::factory()->create();
    $userTwo = User::factory()->create();
    $post = $userTwo->posts()->create([
      'title' => 'My first post',
      'body' => 'This is my first post',
    ]);

    $response = $this->actingAs($userOne)->deleteJson("/api/posts/{$post->id}");

    $response->assertForbidden();

    $this->assertDatabaseHas('posts', [
      'id' => $post->id,
      'title' => $post->title,
      'body' => $post->body,
      'user_id' => $userTwo->id,
    ]);
  }

  public function test_fail_to_delete_post_when_user_is_not_authenticated() {
    /** @var User */
    $user = User::factory()->create();
    $post = $user->posts()->create([
      'title' => 'My first post',
      'body' => 'This is my first post',
    ]);

    $response = $this->deleteJson("/api/posts/{$post->id}");

    $response->assertUnauthorized();

    $this->assertDatabaseHas('posts', [
      'id' => $post->id,
      'title' => $post->title,
      'body' => $post->body,
      'user_id' => $user->id,
    ]);
  }
}
