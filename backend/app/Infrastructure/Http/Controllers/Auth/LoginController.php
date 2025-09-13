<?php

namespace App\Infrastructure\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Infrastructure\Http\Requests\Auth\LoginRequest;
use App\Infrastructure\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function __invoke(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials)) {
            Log::warning('auth.login.failed');
            return response()->json(['message' => 'Credenciais inválidas.'], 401);
        }

        $request->session()->regenerate();

        return response()->json([
            'data' => new UserResource($request->user()),
        ]);
    }
}

