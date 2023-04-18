<?php

namespace App\Services;

use App\Repositories\AuthRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    protected $authRepository;

    public function __construct(AuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    public function doLogin(array $credentials): array
    {
        $user = $this->authRepository->findUserByEmail($credentials['email']);

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw new ValidationException('Invalid credentials');
        }

        $this->authRepository->revokeAllTokens($user);

        $token = $this->authRepository->generateNewToken($user);

        return ['token' => $token, 'user' => $user];
    }
}
