<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ListPostsTests extends TestCase
{
    use RefreshDatabase;

    public function test_list_posts_without_including_user()
    {
        User::factory()->count(3)->hasPosts(2)->create();

        $response = $this->getJson('/api/posts');

        $response
          ->assertOk()
          ->assertJson(fn (AssertableJson $json) => $json
              ->has('links')
              ->has('meta')
              ->has('data', 6)
              ->has('data.0', fn ($json) => $json
                  ->has('id')
                  ->has('title')
                  ->has('body')
                  ->has('userId')
                  ->has('createdAt')
                  ->has('updatedAt')
                  ->missing('user')
                  ->etc()
              )
              ->etc()
          );

        $this->assertDatabaseCount('posts', 6);

        $this->assertDatabaseHas('posts', [
            'title' => $response->json('data.0.title'),
            'body' => $response->json('data.0.body'),
        ]);
    }

    public function test_list_posts_including_user()
    {
        User::factory()->count(3)->hasPosts(2)->create();

        $response = $this->getJson('/api/posts?includeUser=true');

        $response
          ->assertOk()
          ->assertJson(fn (AssertableJson $json) => $json
              ->has('links')
              ->has('meta')
              ->has('data', 6)
              ->has('data.0', fn ($json) => $json
                  ->has('id')
                  ->has('title')
                  ->has('body')
                  ->has('userId')
                  ->has('createdAt')
                  ->has('updatedAt')
                  ->has('user', fn ($json) => $json
                      ->has('id')
                      ->has('displayName')
                      ->has('email')
                      ->missing('password')
                      ->etc()
                  )
                  ->etc()
              )
              ->etc()
          );

        $this->assertDatabaseCount('posts', 6);

        $this->assertDatabaseHas('posts', [
            'title' => $response->json('data.0.title'),
            'body' => $response->json('data.0.body'),
        ]);
    }
}
