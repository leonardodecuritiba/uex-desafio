<?php

namespace App\Infrastructure\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Infrastructure\Http\Requests\Auth\ForgotPasswordRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\JsonResponse;

class ForgotPasswordController extends Controller
{
    public function __invoke(ForgotPasswordRequest $request): JsonResponse
    {
        $status = Password::sendResetLink($request->only('email'));

        Log::info('password.forgot', [
            'status' => $status,
        ]);

        return response()->json([
            'message' => 'Se o e-mail existir, enviaremos instruções.',
        ]);
    }
}

