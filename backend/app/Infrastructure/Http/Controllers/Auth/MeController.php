<?php

namespace App\Infrastructure\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Infrastructure\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($request->user()),
        ]);
    }
}

