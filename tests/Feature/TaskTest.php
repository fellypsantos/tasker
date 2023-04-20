<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class TaskTest extends TestCase
{
    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory(1)->create(['name' => 'Dummy User'])->first();
    }

    public function test_user_get_empty_array_when_no_tasks_are_created()
    {
        $response = $this->actingAs($this->user)->getJson('tasks');

        $response->assertStatus(Response::HTTP_OK);

        $response->assertJsonCount(0);
    }

    public function test_user_can_get_tasks_created_by_him()
    {
        $first_task = Task::factory(5)->create(['user_id' => $this->user->id])->first();

        $response = $this->actingAs($this->user)->getJson('tasks');

        $response->assertStatus(Response::HTTP_OK);

        $response->assertJsonCount(5);

        $response->assertJson(function (AssertableJson $json) use ($first_task) {

            $json->hasAll(['0.id', '0.user_id', '0.title', '0.description', '0.completed', '0.created_at', '0.updated_at']);

            $json->whereAll([
                '0.title' => $first_task['title'],
                '0.description' => $first_task['description'],
                '0.completed' => false
            ]);
        });
    }

    public function test_user_can_get_tasks_created_by_him_and_not_from_other_users()
    {
        $user2 = User::factory(1)->create(['name' => 'Second User'])->first();

        Task::factory(3)->create(['user_id' => $user2->id]);

        Task::factory(5)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->getJson('tasks');

        $response->assertStatus(Response::HTTP_OK);

        $response->assertJsonCount(5);
    }

    public function test_user_can_get_single_task_by_id()
    {
        $task = Task::factory(5)->create(['user_id' => $this->user->id])->first();

        $response = $this->actingAs($this->user)->getJson("tasks/{$task->id}");

        $response->assertStatus(Response::HTTP_OK);

        $response->assertJson(function (AssertableJson $json) use ($task) {

            $json->hasAll(['id', 'user_id', 'title', 'description', 'completed', 'created_at', 'updated_at']);

            $json->whereAll([
                'title' => $task['title'],
                'description' => $task['description'],
                'completed' => false
            ]);
        });
    }

    public function test_user_cannot_get_task_by_id_if_not_owned_by_him()
    {
        $user2 = User::factory(1)->create(['name' => 'Second User'])->first();

        $task_from_user2 = Task::factory(5)->create(['user_id' => $user2->id])->first();

        $response = $this->actingAs($this->user)->getJson("tasks/{$task_from_user2->id}");

        $response->assertStatus(Response::HTTP_NOT_FOUND);

        $response->assertJsonCount(1);

        $response->assertJson(function (AssertableJson $json) {

            $json->has('message');

            $json->where('message', 'Task not found.');
        });
    }

    public function test_user_can_create_new_task_and_see_the_task_as_response()
    {
        $data = [
            'user_id' => $this->user->id,
            'title' => 'This is my task',
            'description' => 'This is the task description.'
        ];

        $response = $this->actingAs($this->user)->postJson('tasks', $data);

        $response->assertStatus(Response::HTTP_CREATED);

        $response->assertJson(function (AssertableJson $json) use ($data) {

            $json->hasAll(['id', 'user_id', 'title', 'description', 'completed', 'created_at', 'updated_at']);

            $json->whereAll([
                'title' => $data['title'],
                'description' => $data['description'],
                'completed' => false
            ]);
        });
    }

    public function test_user_cannot_create_new_task_with_missing_title()
    {
        $data = [];

        $response = $this->actingAs($this->user)->postJson('tasks', $data);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonCount(2);

        $response->assertJson(function (AssertableJson $json) {

            $json->hasAll(['message', 'errors']);

            $json->whereAll([
                'message' => 'The given data was invalid.',
                'errors.title.0' => 'The title field is required.'
            ]);
        });
    }

    public function test_user_cannot_create_new_task_if_not_logged_in()
    {
        $data = [];

        $response = $this->postJson('tasks', $data);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);

        $response->assertJsonCount(1);

        $response->assertJson(function (AssertableJson $json) {

            $json->has('message');

            $json->where('message', 'Unauthenticated.');
        });
    }

    public function test_user_can_update_task()
    {
        $task = Task::factory(1)->create(['user_id' => $this->user->id])->first();

        $updated_data = [
            'title' => $task->title,
            'description' => $task->description,
            'completed' => true
        ];

        $response = $this->actingAs($this->user)->putJson("tasks/{$task->id}", $updated_data);

        $response->assertStatus(Response::HTTP_OK);

        $response->assertJsonCount(7);

        $response->assertJson(function (AssertableJson $json) use ($updated_data) {

            $json->hasAll(['id', 'user_id', 'title', 'description', 'completed', 'created_at', 'updated_at']);

            $json->whereAll([
                'title' => $updated_data['title'],
                'description' => $updated_data['description'],
                'completed' => $updated_data['completed']
            ]);
        });
    }

    public function test_user_cannot_update_task_with_invalid_data()
    {
        $task = Task::factory(1)->create(['user_id' => $this->user->id])->first();

        $updated_data = [];

        $response = $this->actingAs($this->user)->putJson("tasks/{$task->id}", $updated_data);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonCount(2);

        $response->assertJson(function (AssertableJson $json) {

            $json->hasAll(['message', 'errors']);

            $json->whereAll([
                'message' => 'The given data was invalid.',
                'errors.title.0' => 'The title field is required.'
            ]);
        });
    }

    public function test_user_cannot_update_task_when_not_logged_in()
    {
        $task = Task::factory(1)->create(['user_id' => $this->user->id])->first();

        $updated_data = [
            'title' => $task->title,
            'description' => $task->description,
            'completed' => true
        ];

        $response = $this->putJson("tasks/{$task->id}", $updated_data);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);

        $response->assertJsonCount(1);

        $response->assertJson(function (AssertableJson $json) {

            $json->has('message');

            $json->where('message', 'Unauthenticated.');
        });
    }

    public function test_user_cannot_update_task_when_it_is_not_the_owner_or_not_exists()
    {
        $user2 = User::factory(1)->create(['name' => 'Second User'])->first();

        $task_from_user2 = Task::factory(1)->create(['user_id' => $user2->id])->first();

        $updated_data = [
            'title' => $task_from_user2->title,
            'description' => $task_from_user2->description,
            'completed' => true
        ];

        $response = $this->actingAs($this->user)->putJson("tasks/{$task_from_user2->id}", $updated_data);

        $response->assertStatus(Response::HTTP_NOT_FOUND);

        $response->assertJsonCount(1);

        $response->assertJson(function (AssertableJson $json) {

            $json->has('message');

            $json->where('message', 'Task not found.');
        });
    }

    public function test_user_can_delete_a_task()
    {
        $task = Task::factory(1)->create(['user_id' => $this->user->id])->first();

        $response = $this->actingAs($this->user)->delete("tasks/{$task->id}");

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function test_user_cannot_delete_a_task_when_it_is_not_the_owner_or_not_exists()
    {
        $user2 = User::factory(1)->create(['name' => 'Second User'])->first();

        $task_from_user2 = Task::factory(1)->create(['user_id' => $user2->id])->first();

        $response = $this->actingAs($this->user)->delete("tasks/{$task_from_user2->id}");

        $response->assertStatus(Response::HTTP_NOT_FOUND);

        $response->assertJsonCount(1);

        $response->assertJson(function (AssertableJson $json) {

            $json->has('message');

            $json->where('message', 'Task not found.');
        });
    }
}
