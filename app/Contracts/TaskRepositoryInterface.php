<?php

namespace App\Contracts;

use App\Models\Task;
use App\Models\User;

interface TaskRepositoryInterface
{
    public function getAll();

    public function findTaskById(int $id): ?Task;

    public function findTaskByTitle(string $title): ?Task;

    public function createNewTask(array $data, User $user): Task;

    public function updateTaskInformation(array $updated_data, int $id);

    public function deleteTask(int $id): bool;
}
