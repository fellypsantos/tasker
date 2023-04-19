<?php

namespace App\Contracts;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface TaskRepositoryInterface
{
    public function getAll(): Collection;

    public function findTaskById(int $id, User $user): ?Task;

    public function findTaskByTitle(string $title, User $user): ?Task;

    public function createNewTask(array $data, User $user): Task;

    public function updateTaskInformation(array $updated_data, int $id, User $user);

    public function deleteTask(int $id, User $user): bool;
}
