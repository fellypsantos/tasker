<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginValidation;
use App\Services\AuthService;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginValidation $request)
    {
        $credentials = $request->validated();

        try {
            $response = $this->authService->doLogin($credentials);

            return response()->json($response);
        } catch (ValidationException $ex) {
            return response()->json(['message' => $ex->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
