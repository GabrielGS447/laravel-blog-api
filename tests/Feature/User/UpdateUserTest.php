<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class UpdateUserTests extends TestCase {
  use RefreshDatabase;

  private $updateUserData = [
    'displayName' => 'John Doe',
    'email' => 'johndoe@mail.com',
    'password' => 'password',
  ];

  public function test_update_user_with_valid_data() {
    /** @var User */
    $user = User::factory()->create();

    $this->assertDatabaseHas('users', [
      'email' => $user->email,
    ]);

    $response = $this->actingAs($user)->patchJson("/api/users", $this->updateUserData);

    $response
      ->assertOk()
      ->assertJson(fn (AssertableJson $json) =>
        $json
          ->has('data.id')
          ->where('data.email', $this->updateUserData['email'])
          ->where('data.displayName', $this->updateUserData['displayName'])
          ->missing('data.password')
          ->etc()
      );

    $this->assertDatabaseHas('users', [
      'email' => $this->updateUserData['email'],
      'display_name' => $this->updateUserData['displayName'],
    ]);
  }

  public function test_fail_to_update_user_without_display_name() {
    /** @var User */
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patchJson("/api/users", array_merge($this->updateUserData, [
      'displayName' => '',
    ]));

    $response
      ->assertStatus(422)
      ->assertJson(fn (AssertableJson $json) =>
        $json
          ->has('errors.displayName')
          ->where('errors.displayName.0', 'The display name field is required.')
          ->etc()
      );

    $this->assertDatabaseMissing('users', [
      'email' => $this->updateUserData['email'],
    ]);
  }

  public function test_fail_to_update_user_without_email() {
    /** @var User */
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patchJson("/api/users", array_merge($this->updateUserData, [
      'email' => '',
    ]));

    $response
      ->assertStatus(422)
      ->assertJson(fn (AssertableJson $json) =>
        $json
          ->has('errors.email')
          ->where('errors.email.0', 'The email field is required.')
          ->etc()
      );

    $this->assertDatabaseMissing('users', [
      'email' => $this->updateUserData['email'],
    ]);
  }

  public function test_fail_to_update_user_with_invalid_email() {
    /** @var User */
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patchJson("/api/users", array_merge($this->updateUserData, [
      'email' => 'invalid-email',
    ]));

    $response
      ->assertStatus(422)
      ->assertJson(fn (AssertableJson $json) =>
        $json
          ->has('errors.email')
          ->where('errors.email.0', 'The email must be a valid email address.')
          ->etc()
      );

    $this->assertDatabaseMissing('users', [
      'email' => $this->updateUserData['email'],
    ]);
  }

  public function test_fail_to_update_user_with_existing_email() {
    /** @var User */
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patchJson("/api/users", array_merge($this->updateUserData, [
      'email' => $user->email,
    ]));

    $response
      ->assertStatus(422)
      ->assertJson(fn (AssertableJson $json) =>
        $json
          ->has('errors.email')
          ->where('errors.email.0', 'The email has already been taken.')
          ->etc()
      );

    $this->assertDatabaseMissing('users', [
      'email' => $this->updateUserData['email'],
    ]);
  }

  public function test_fail_to_update_user_without_password() {
    /** @var User */
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patchJson("/api/users", array_merge($this->updateUserData, [
      'password' => '',
    ]));

    $response
      ->assertStatus(422)
      ->assertJson(fn (AssertableJson $json) =>
        $json
          ->has('errors.password')
          ->where('errors.password.0', 'The password field is required.')
          ->etc()
      );

    $this->assertDatabaseMissing('users', [
      'email' => $this->updateUserData['email'],
    ]);
  }

  public function test_fail_to_update_user_with_invalid_password() {
    /** @var User */
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patchJson("/api/users", array_merge($this->updateUserData, [
      'password'=> '1234567',
    ]));

    $response
      ->assertStatus(422)
      ->assertJson(fn (AssertableJson $json) =>
        $json
          ->has('errors.password')
          ->where('errors.password.0', 'The password must be at least 8 characters.')
          ->etc()
      );

    $this->assertDatabaseMissing('users', [
      'email' => $this->updateUserData['email'],
    ]);
  }

  public function test_fail_to_update_user_without__being_authenticated() {
    $response = $this->patchJson("/api/users", $this->updateUserData);

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
