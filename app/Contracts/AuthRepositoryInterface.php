<?php

namespace App\Contracts;

use App\Models\User;

interface AuthRepositoryInterface
{
    public function findUserByEmail(string $email): ?User;

    public function generateNewToken(User $user): string;

    public function revokeAllTokens(User $user): bool;
}
