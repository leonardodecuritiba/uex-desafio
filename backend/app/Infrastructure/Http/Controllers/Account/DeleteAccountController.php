<?php

namespace App\Infrastructure\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Infrastructure\Http\Requests\Account\DeleteAccountRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Exclui a própria conta do usuário autenticado.
 * Fluxo:
 * 1) Valida senha atual (em DeleteAccountRequest)
 * 2) Remove o usuário (cascade nos contatos)
 * 3) Efetua logout e invalida a sessão
 */
class DeleteAccountController extends Controller
{
    public function __invoke(DeleteAccountRequest $request): JsonResponse
    {
        $user = $request->user();

        DB::transaction(function () use ($user) {
            User::find($user->id)->delete();
        });

        // Revoga sessão após exclusão
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::info('account.deleted');

        return response()->json(['message' => 'Conta excluída com sucesso.']);
    }
}
