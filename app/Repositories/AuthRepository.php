<?php

namespace App\Repositories;

use App\Contracts\AuthRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AuthRepository implements AuthRepositoryInterface
{
    public function findUserByEmail(string $email): ?User
    {
        $user = User::where('email', $email)->first();

        return $user;
    }

    public function generateNewToken(User $user): string
    {
        $token = $user->createToken('Login')->plainTextToken;

        return $token;
    }

    public function revokeAllTokens(User $user): bool
    {
        $affectedRows = DB::table('personal_access_tokens')->where('tokenable_id', $user->id)->delete();

        $success = $affectedRows > 0;

        return $success;
    }
}
