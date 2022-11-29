<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class DeleteUserTests extends TestCase {
  use RefreshDatabase;

  public function test_delete_user() {
    /** @var User */
    $user = User::factory()->create();

    $response = $this->actingAs($user)->deleteJson("/api/users");

    $response->assertNoContent();

    $this->assertDatabaseMissing('users', [
      'id' => $user->id,
    ]);
  }

  public function test_fail_to_delete_user_when_not_authenticated() {
    $response = $this->deleteJson("/api/users");

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
