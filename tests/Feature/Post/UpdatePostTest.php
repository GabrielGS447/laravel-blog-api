<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class UpdatePostTests extends TestCase {
  use RefreshDatabase;

  private $originalPostData = [
    'title' => 'Original Post Title',
    'body' => 'Original Post Body',
  ];

  private $updatePostData = [
    'title' => 'Updated Post Title',
    'body' => 'Updated Post Body',
  ];

  public function test_update_post_with_valid_data() {
    /** @var User */
    $user = User::factory()->create();
    $post = $user->posts()->create($this->originalPostData);

    $response = $this->actingAs($user)->patchJson("/api/posts/{$post->id}", $this->updatePostData);

    $response
      ->assertOk()
      ->assertJson(fn (AssertableJson $json) =>
        $json
          ->has('data.id')
          ->where('data.title', $this->updatePostData['title'])
          ->where('data.body', $this->updatePostData['body'])
          ->where('data.userId', $user->id)
          ->etc()
      );

    $this->assertDatabaseMissing('posts', [
      'id' => $post->id,
      'title' => $this->originalPostData['title'],
      'body' => $this->originalPostData['body'],
    ]);

    $this->assertDatabaseHas('posts', [
      'id' => $post->id,
      'title' => $this->updatePostData['title'],
      'body' => $this->updatePostData['body'],
      'user_id' => $user->id,
    ]);
  }

  public function test_fail_to_update_post_without_title() {
    /** @var User */
    $user = User::factory()->create();
    $post = $user->posts()->create($this->originalPostData);

    $response = $this->actingAs($user)->patchJson("/api/posts/{$post->id}", array_merge($this->updatePostData, [
      'title' => '',
    ]));

    $response
      ->assertStatus(422)
      ->assertJson(fn (AssertableJson $json) =>
        $json
          ->has('errors.title')
          ->where('errors.title.0', 'The title field is required.')
          ->etc()
      );

    $this->assertDatabaseHas('posts', [
      'id' => $post->id,
      'title' => $this->originalPostData['title'],
      'body' => $this->originalPostData['body'],
      'user_id' => $user->id,
    ]);

    $this->assertDatabaseMissing('posts', [
      'id' => $post->id,
      'title' => $this->updatePostData['title'],
      'body' => $this->updatePostData['body'],
    ]);
  }

  public function test_fail_to_update_post_when_title_is_not_string() {
    /** @var User */
    $user = User::factory()->create();
    $post = $user->posts()->create($this->originalPostData);

    $response = $this->actingAs($user)->patchJson("/api/posts/{$post->id}", array_merge($this->updatePostData, [
      'title' => 123,
    ]));

    $response
      ->assertStatus(422)
      ->assertJson(fn (AssertableJson $json) =>
        $json
          ->has('errors.title')
          ->where('errors.title.0', 'The title must be a string.')
          ->etc()
      );

    $this->assertDatabaseHas('posts', [
      'id' => $post->id,
      'title' => $this->originalPostData['title'],
      'body' => $this->originalPostData['body'],
      'user_id' => $user->id,
    ]);

    $this->assertDatabaseMissing('posts', [
      'id' => $post->id,
      'title' => $this->updatePostData['title'],
      'body' => $this->updatePostData['body'],
    ]);
  }

  public function test_fail_to_update_post_without_body() {
    /** @var User */
    $user = User::factory()->create();
    $post = $user->posts()->create($this->originalPostData);

    $response = $this->actingAs($user)->patchJson("/api/posts/{$post->id}", array_merge($this->updatePostData, [
      'body' => '',
    ]));

    $response
      ->assertStatus(422)
      ->assertJson(fn (AssertableJson $json) =>
        $json
          ->has('errors.body')
          ->where('errors.body.0', 'The body field is required.')
          ->etc()
      );

    $this->assertDatabaseHas('posts', [
      'id' => $post->id,
      'title' => $this->originalPostData['title'],
      'body' => $this->originalPostData['body'],
      'user_id' => $user->id,
    ]);

    $this->assertDatabaseMissing('posts', [
      'id' => $post->id,
      'title' => $this->updatePostData['title'],
      'body' => $this->updatePostData['body'],
    ]);
  }

  public function test_fail_to_update_post_when_body_is_not_string() {
    /** @var User */
    $user = User::factory()->create();
    $post = $user->posts()->create($this->originalPostData);

    $response = $this->actingAs($user)->patchJson("/api/posts/{$post->id}", array_merge($this->updatePostData, [
      'body' => 123,
    ]));

    $response
      ->assertStatus(422)
      ->assertJson(fn (AssertableJson $json) =>
        $json
          ->has('errors.body')
          ->where('errors.body.0', 'The body must be a string.')
          ->etc()
      );

    $this->assertDatabaseHas('posts', [
      'id' => $post->id,
      'title' => $this->originalPostData['title'],
      'body' => $this->originalPostData['body'],
      'user_id' => $user->id,
    ]);

    $this->assertDatabaseMissing('posts', [
      'id' => $post->id,
      'title' => $this->updatePostData['title'],
      'body' => $this->updatePostData['body'],
    ]);
  }

  public function test_fail_to_update_post_when_it_doesnt_belong_to_user() {
    /** @var User */
    $userOne = User::factory()->create();
    $userTwo = User::factory()->create();
    $post = $userTwo->posts()->create($this->originalPostData);

    $response = $this->actingAs($userOne)->patchJson("/api/posts/{$post->id}", $this->updatePostData);

    $response
      ->assertStatus(403)
      ->assertJson(fn (AssertableJson $json) =>
        $json
          ->has('message')
          ->where('message', 'You are not authorized to update this post.')
          ->etc()
      );

    $this->assertDatabaseHas('posts', [
      'id' => $post->id,
      'title' => $this->originalPostData['title'],
      'body' => $this->originalPostData['body'],
    ]);

    $this->assertDatabaseMissing('posts', [
      'id' => $post->id,
      'title' => $this->updatePostData['title'],
      'body' => $this->updatePostData['body'],
    ]);
  }

  public function test_fail_to_update_post_when_user_is_not_authenticated() {
    /** @var User */
    $user = User::factory()->create();
    $post = $user->posts()->create($this->originalPostData);

    $response = $this->patchJson("/api/posts/{$post->id}", $this->updatePostData);

    $response
      ->assertStatus(401)
      ->assertJson(fn (AssertableJson $json) =>
        $json
          ->has('message')
          ->where('message', 'Unauthenticated')
          ->etc()
      );

    $this->assertDatabaseHas('posts', [
      'id' => $post->id,
      'title' => $this->originalPostData['title'],
      'body' => $this->originalPostData['body'],
      'user_id' => $user->id,
    ]);

    $this->assertDatabaseMissing('posts', [
      'id' => $post->id,
      'title' => $this->updatePostData['title'],
      'body' => $this->updatePostData['body'],
    ]);
  }
}
