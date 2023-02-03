<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CreateUserTests extends TestCase
{
    use RefreshDatabase;

    private $newUserData = [
        'displayName' => 'John Doe',
        'email' => 'johndoe@mail.com',
        'password' => 'password',
    ];

    public function test_create_user_with_valid_data()
    {
        $this->assertDatabaseMissing('users', [
            'email' => $this->newUserData['email'],
        ]);

        $response = $this->postJson('/api/users', $this->newUserData);

        $response
          ->assertCreated()
          ->assertJson(fn (AssertableJson $json) => $json
              ->has('data.id')
              ->where('data.email', $this->newUserData['email'])
              ->where('data.displayName', $this->newUserData['displayName'])
              ->missing('data.password')
              ->etc()
          );

        $this->assertDatabaseHas('users', [
            'email' => $this->newUserData['email'],
            'display_name' => $this->newUserData['displayName'],
        ]);
    }

    public function test_fail_to_create_user_without_display_name()
    {
        $response = $this->postJson('/api/users', array_merge($this->newUserData, [
            'displayName' => '',
        ]));

        $response
          ->assertStatus(422)
          ->assertJson(fn (AssertableJson $json) => $json
              ->has('errors.displayName')
              ->where('errors.displayName.0', 'The display name field is required.')
              ->etc()
          );

        $this->assertDatabaseMissing('users', [
            'email' => $this->newUserData['email'],
        ]);
    }

    public function test_fail_to_create_user_with_display_name_not_string()
    {
        $response = $this->postJson('/api/users', array_merge($this->newUserData, [
            'displayName' => 123,
        ]));

        $response
          ->assertStatus(422)
          ->assertJson(fn (AssertableJson $json) => $json
              ->has('errors.displayName')
              ->where('errors.displayName.0', 'The display name must be a string.')
              ->etc()
          );

        $this->assertDatabaseMissing('users', [
            'email' => $this->newUserData['email'],
        ]);
    }

    public function test_fail_to_create_user_without_email()
    {
        $response = $this->postJson('/api/users', array_merge($this->newUserData, [
            'email' => '',
        ]));

        $response
          ->assertStatus(422)
          ->assertJson(fn (AssertableJson $json) => $json
              ->has('errors.email')
              ->where('errors.email.0', 'The email field is required.')
              ->etc()
          );

        $this->assertDatabaseMissing('users', [
            'email' => $this->newUserData['email'],
        ]);
    }

    public function test_fail_to_create_user_with_invalid_email()
    {
        $response = $this->postJson('/api/users', array_merge($this->newUserData, [
            'email' => 'invalid-email',
        ]));

        $response
          ->assertStatus(422)
          ->assertJson(fn (AssertableJson $json) => $json
              ->has('errors.email')
              ->where('errors.email.0', 'The email must be a valid email address.')
              ->etc()
          );

        $this->assertDatabaseMissing('users', [
            'email' => $this->newUserData['email'],
        ]);
    }

    public function test_fail_to_create_user_with_existing_email()
    {
        User::factory()->create([
            'email' => $this->newUserData['email'],
        ]);

        $response = $this->postJson('/api/users', $this->newUserData);

        $response
          ->assertStatus(422)
          ->assertJson(fn (AssertableJson $json) => $json
              ->has('errors.email')
              ->where('errors.email.0', 'The email has already been taken.')
              ->etc()
          );

        $this->assertDatabaseHas('users', [
            'email' => $this->newUserData['email'],
        ]);
    }

    public function test_fail_to_create_user_without_password()
    {
        $response = $this->postJson('/api/users', array_merge($this->newUserData, [
            'password' => '',
        ]));

        $response
          ->assertStatus(422)
          ->assertJson(fn (AssertableJson $json) => $json
              ->has('errors.password')
              ->where('errors.password.0', 'The password field is required.')
              ->etc()
          );

        $this->assertDatabaseMissing('users', [
            'email' => $this->newUserData['email'],
        ]);
    }

    public function test_fail_to_create_user_with_password_not_string()
    {
        $response = $this->postJson('/api/users', array_merge($this->newUserData, [
            'password' => 123,
        ]));

        $response
          ->assertStatus(422)
          ->assertJson(fn (AssertableJson $json) => $json
              ->has('errors.password')
              ->where('errors.password.0', 'The password must be a string.')
              ->etc()
          );

        $this->assertDatabaseMissing('users', [
            'email' => $this->newUserData['email'],
        ]);
    }

    public function test_fail_to_create_user_with_password_less_than_8_characters()
    {
        $response = $this->postJson('/api/users', array_merge($this->newUserData, [
            'password' => '1234567',
        ]));

        $response
          ->assertStatus(422)
          ->assertJson(fn (AssertableJson $json) => $json
              ->has('errors.password')
              ->where('errors.password.0', 'The password must be at least 8 characters.')
              ->etc()
          );

        $this->assertDatabaseMissing('users', [
            'email' => $this->newUserData['email'],
        ]);
    }
}
