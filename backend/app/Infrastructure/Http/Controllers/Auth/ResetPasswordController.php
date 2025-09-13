<?php

namespace App\Infrastructure\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Infrastructure\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;

class ResetPasswordController extends Controller
{
    public function __invoke(ResetPasswordRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password', 'password_confirmation', 'token');

        $status = Password::reset($credentials, function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password),
            ])->setRememberToken(Str::random(60));

            $user->save();
        });

        Log::info('password.reset', [
            'status' => $status,
        ]);

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'errors' => [
                    ['field' => 'token', 'message' => 'Token inválido ou expirado.'],
                ],
            ], 422);
        }

        return response()->json([
            'message' => 'Senha alterada com sucesso.',
        ]);
    }
}

