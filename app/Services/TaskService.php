<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\TaskRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class TaskService
{
    protected $taskRepository;

    public function __construct(TaskRepository $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    public function getAll(User $user)
    {
        return $user->tasks;
    }

    public function createNewTask(array $data)
    {
        $user = auth()->user();

        $task = $this->taskRepository->findTaskByTitle($data['title'], $user);

        if ($task) {
            throw new ValidationException(null, 'Task with same title already exists.');
        }

        return $this->taskRepository->createNewTask($data, $user);
    }

    public function getTask(int $id)
    {
        $task = $this->taskRepository->findTaskById($id);

        if (!$task) throw new ModelNotFoundException('Task not found.');

        return $task;
    }

    public function updateTask(array $data, int $id)
    {
        $task = $this->taskRepository->findTaskById($id);

        if (!$task) throw new ModelNotFoundException('Task not found.');

        $task_updated = $this->taskRepository->updateTaskInformation($data, $id);

        return $task_updated;
    }

    public function deleteTask(int $id)
    {
        $task = $this->taskRepository->findTaskById($id);

        if (!$task) throw new ModelNotFoundException('Task not found.');

        $result = $task->delete();

        return $result;
    }
}
