<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ListUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_users_without_including_posts()
    {
        $users = User::factory()->count(5)->create();

        $response = $this->getJson('/api/users');

        $response
          ->assertOk()
          ->assertJson(fn (AssertableJson $json) => $json
              ->has('data', 5)
              ->has('data.3', fn ($json) => $json
                  ->where('id', $users[3]->id)
                  ->where('displayName', $users[3]->display_name)
                  ->where('email', $users[3]->email)
                  ->missing('password')
                  ->missing('posts')
                  ->etc()
              )
              ->etc()
          );
    }

    public function test_list_users_including_posts()
    {
        $users = User::factory()->count(5)->hasPosts(3)->create();

        $response = $this->getJson('/api/users?includePosts=true');

        $response
          ->assertOk()
          ->assertJson(fn (AssertableJson $json) => $json
              ->has('data', 5)
              ->has('data.3', fn ($json) => $json
                  ->where('id', $users[3]->id)
                  ->where('displayName', $users[3]->display_name)
                  ->where('email', $users[3]->email)
                  ->missing('password')
                  ->has('posts', 3)
                  ->has('posts.1', fn ($json) => $json
                      ->where('id', $users[3]->posts[1]->id)
                      ->where('title', $users[3]->posts[1]->title)
                      ->where('body', $users[3]->posts[1]->body)
                      ->missing('user_id')
                      ->etc()
                  )
                  ->etc()
              )
              ->etc()
          );
    }
}
