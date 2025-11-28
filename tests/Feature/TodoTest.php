<?php

namespace Tests\Feature;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsApi(User $user)
    {
        $token = $user->createToken('test')->plainTextToken;
        return $this->withHeader('Authorization', "Bearer {$token}");
    }

    public function test_can_create_todo(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsApi($user)->postJson('/api/todos', [
            'title' => 'My Todo',
            'description' => 'Test description',
            'due_date' => '2024-12-31',
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => 'My Todo']);

        $this->assertDatabaseHas('todos', [
            'user_id' => $user->id,
            'title' => 'My Todo',
        ]);
    }

    public function test_cannot_create_todo_without_title(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsApi($user)->postJson('/api/todos', [
            'description' => 'Test description',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_can_list_todos(): void
    {
        $user = User::factory()->create();
        Todo::factory()->count(3)->for($user)->create();

        $response = $this->actingAsApi($user)->getJson('/api/todos');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'current_page']);
    }

    public function test_can_filter_todos_by_status(): void
    {
        $user = User::factory()->create();
        Todo::factory()->for($user)->create(['title' => 'Buy milk', 'completed' => false]);
        Todo::factory()->for($user)->create(['title' => 'Finish project', 'completed' => true]);

        $response = $this->actingAsApi($user)->getJson('/api/todos?status=completed');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertTrue($data[0]['completed']);
    }

    public function test_can_search_todos(): void
    {
        $user = User::factory()->create();
        Todo::factory()->for($user)->create(['title' => 'Buy groceries']);
        Todo::factory()->for($user)->create(['title' => 'Call dentist']);

        $response = $this->actingAsApi($user)->getJson('/api/todos?search=groceries');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertStringContainsString('groceries', $data[0]['title']);
    }

    public function test_can_update_todo(): void
    {
        $user = User::factory()->create();
        $todo = Todo::factory()->for($user)->create(['title' => 'Old Title']);

        $response = $this->actingAsApi($user)->putJson("/api/todos/{$todo->id}", [
            'title' => 'New Title',
            'description' => 'Updated description',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'New Title']);

        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            'title' => 'New Title',
        ]);
    }

    public function test_can_delete_todo(): void
    {
        $user = User::factory()->create();
        $todo = Todo::factory()->for($user)->create();

        $response = $this->actingAsApi($user)->deleteJson("/api/todos/{$todo->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('todos', ['id' => $todo->id]);
    }

    public function test_can_toggle_todo_completion(): void
    {
        $user = User::factory()->create();
        $todo = Todo::factory()->for($user)->create(['completed' => false]);

        $response = $this->actingAsApi($user)->patchJson("/api/todos/{$todo->id}/toggle");

        $response->assertStatus(200)
            ->assertJsonFragment(['completed' => true]);

        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            'completed' => true,
        ]);
    }

    public function test_cannot_access_others_todo(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $todo = Todo::factory()->for($other)->create();

        $response = $this->actingAsApi($user)->getJson("/api/todos/{$todo->id}");

        $response->assertStatus(403);
    }

    public function test_can_create_todo_with_tags(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsApi($user)->postJson('/api/todos', [
            'title' => 'My Todo',
            'tags' => ['work', 'urgent'],
        ]);

        $response->assertStatus(201);
        $todo = Todo::where('title', 'My Todo')->first();
        $this->assertCount(2, $todo->tags);
    }
}

