<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_logout_while_logged_in()
    {
        /** @var User $user */
        $user = User::factory()->create(['password' => bcrypt('password')]);

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);

        $logoutResponse = $this
          ->withHeader('Authorization', "Bearer {$loginResponse['data']['access_token']}")
          ->postJson('/api/auth/logout');

        $logoutResponse
          ->assertOk()
          ->assertJson(fn (AssertableJson $json) => $json->where('message', 'Logged out')
          );

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }

    public function test_fail_to_logout_while_not_logged_in()
    {
        $response = $this->postJson('/api/auth/logout');

        $response
          ->assertStatus(401)
          ->assertJson(fn (AssertableJson $json) => $json->where('message', 'Unauthenticated')
          );
    }
}
