<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CreatePostTests extends TestCase {
  use RefreshDatabase;

  private $newPostData = [
    'title' => 'My first post',
    'body' => 'This is my first post',
  ];

  public function test_create_post_with_valid_data() {
    /** @var User */
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson("/api/posts", $this->newPostData);

    $response
      ->assertCreated()
      ->assertJson(fn (AssertableJson $json) =>
        $json
          ->has('data.id')
          ->where('data.title', $this->newPostData['title'])
          ->where('data.body', $this->newPostData['body'])
          ->where('data.userId', $user->id)
          ->etc()
      );

    $this->assertDatabaseHas('posts', [
      'title' => $this->newPostData['title'],
      'body' => $this->newPostData['body'],
      'user_id' => $user->id,
    ]);
  }

  public function test_fail_to_create_post_without_title() {
    /** @var User */
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson("/api/posts", array_merge($this->newPostData, [
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
  }

  public function test_fail_to_create_post_when_title_is_not_string() {
    /** @var User */
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson("/api/posts", array_merge($this->newPostData, [
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
  }

  public function test_fail_to_create_post_without_body() {
    /** @var User */
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson("/api/posts", array_merge($this->newPostData, [
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
  }

  public function test_fail_to_create_post_when_body_is_not_string() {
    /** @var User */
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson("/api/posts", array_merge($this->newPostData, [
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
  }

  public function test_fail_to_create_post_when_not_authenticated() {
    $response = $this->postJson("/api/posts", $this->newPostData);

    $response
      ->assertStatus(401)
      ->assertJson(fn (AssertableJson $json) =>
        $json
          ->has('message')
          ->where('message', 'Unauthenticated')
          ->etc()
      );
  }
}
