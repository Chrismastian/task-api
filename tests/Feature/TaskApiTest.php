<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receives_a_token(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertCreated()->assertJsonStructure(['user', 'token']);
    }

    public function test_login_rejects_wrong_password(): void
    {
        User::factory()->create(['email' => 'ada@example.com', 'password' => 'password123']);

        $this->postJson('/api/auth/login', [
            'email' => 'ada@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(422);
    }

    public function test_tasks_endpoint_requires_authentication(): void
    {
        $this->getJson('/api/tasks')->assertUnauthorized();
    }

    public function test_user_can_create_a_task(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/tasks', [
            'title' => 'Ship the portfolio',
            'priority' => 'high',
            'due_date' => '2026-10-15',
        ])->assertCreated()->assertJsonFragment(['title' => 'Ship the portfolio']);
    }

    public function test_marking_a_task_done_sets_completed_at(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $task = $user->tasks()->create(['title' => 'Write tests']);

        $this->putJson("/api/tasks/{$task->id}", ['status' => 'done'])
            ->assertOk()
            ->assertJsonPath('status', 'done');

        $this->assertNotNull($task->fresh()->completed_at);
    }

    public function test_user_cannot_touch_another_users_task(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $task = $owner->tasks()->create(['title' => 'Private task']);

        Sanctum::actingAs($intruder);

        // Must look like a missing resource, not a forbidden one.
        $this->getJson("/api/tasks/{$task->id}")->assertNotFound();
        $this->putJson("/api/tasks/{$task->id}", ['title' => 'Hijacked'])->assertNotFound();
        $this->deleteJson("/api/tasks/{$task->id}")->assertNotFound();

        $this->assertSame('Private task', $task->fresh()->title);
    }

    public function test_task_list_can_be_filtered_by_status(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $user->tasks()->create(['title' => 'A', 'status' => 'todo']);
        $user->tasks()->create(['title' => 'B', 'status' => 'done']);

        $this->getJson('/api/tasks?status=done')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.title', 'B');
    }
}
