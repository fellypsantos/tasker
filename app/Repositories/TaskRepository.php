<?php

namespace App\Repositories;

use App\Contracts\TaskRepositoryInterface;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class TaskRepository implements TaskRepositoryInterface
{
    public function getAll(): Collection
    {
        return Task::all();
    }

    public function findTaskById(int $id, User $user): ?Task
    {
        return Task::find($id);
    }

    public function findTaskByTitle(string $title, User $user): ?Task
    {
        $task = Task::where('title', $title)->first();

        return $task;
    }

    public function createNewTask(array $data, User $user): Task
    {
        $task = new Task($data);

        $task->user_id = $user->id;

        $task->save();

        return $task;
    }

    public function updateTaskInformation(array $updated_data, int $id, User $user): Task
    {
        $task = Task::findOrFail($id);

        $task->update($updated_data);

        return $task;
    }

    public function deleteTask(int $id, User $user): bool
    {
        $task = Task::findOrFail($id);

        $result = $task->delete();

        return $result;
    }
}
