<?php

namespace App\Infrastructure\Http\Controllers\Contacts;

use App\Application\Contacts\UseCases\DeleteContact;
use App\Application\Contacts\UseCases\DeleteContactInput;
use App\Infrastructure\Persistence\EloquentContactRepository;
use App\Http\Controllers\Controller;
use App\Models\Contact as ContactModel;
use Illuminate\Http\JsonResponse;

class DeleteContactController extends Controller
{
    public function __construct(private readonly EloquentContactRepository $repo) {}

    public function __invoke(int $id): JsonResponse
    {
        $user = request()->user();
        if (! $user) {
            return response()->json(['message' => 'Não autenticado.'], 401);
        }

        $model = ContactModel::find($id);
        if (! $model) {
            return response()->json(['message' => 'Contato não encontrado.'], 404);
        }
        if ($model->user_id !== $user->id) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $useCase = new DeleteContact($this->repo);
        $useCase->handle(new DeleteContactInput(contactId: $id, userId: $user->id));

        return response()->json(null, 204);
    }
}
