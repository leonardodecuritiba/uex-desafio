<?php

namespace App\Infrastructure\Http\Controllers\Auth;

use App\Application\Auth\UseCases\RegisterUser;
use App\Application\Auth\UseCases\RegisterUserInput;
use App\Infrastructure\Http\Requests\Auth\RegisterRequest;
use App\Infrastructure\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class RegisterController extends Controller
{
    public function __construct(private readonly RegisterUser $registerUser) {}

    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $input = new RegisterUserInput(
            name: $request->string('name')->toString(),
            email: $request->string('email')->toString(),
            password: $request->string('password')->toString(),
        );

        $output = $this->registerUser->handle($input);

        return response()->json([
            'data' => new UserResource((object) [
                'id' => $output->id,
                'name' => $output->name,
                'email' => $output->email,
            ]),
        ], 201);
    }
}
