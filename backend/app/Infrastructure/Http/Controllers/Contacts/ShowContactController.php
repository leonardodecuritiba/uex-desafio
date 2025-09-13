<?php

namespace App\Infrastructure\Http\Controllers\Contacts;

use App\Application\Contacts\UseCases\ShowContact;
use App\Http\Controllers\Controller;
use App\Infrastructure\Http\Resources\Contacts\ContactResource;
use App\Infrastructure\Persistence\EloquentContactRepository;
use Illuminate\Http\JsonResponse;

class ShowContactController extends Controller
{
    public function __construct(private readonly EloquentContactRepository $repo) {}

    public function __invoke(int $id): JsonResponse
    {
        $user = request()->user();
        if (! $user) {
            return response()->json(['message' => 'Não autenticado.'], 401);
        }

        $useCase = new ShowContact($this->repo);
        $contact = $useCase->handle($id, $user->id);

        if (! $contact) {
            return response()->json(['message' => 'Contato não encontrado.'], 404);
        }

        return response()->json([
            'data' => new ContactResource((object) [
                'id' => $contact->id,
                'name' => $contact->name,
                'cpf' => $contact->cpf,
                'email' => $contact->email,
                'phone' => $contact->phone,
                'address' => $contact->address,
            ]),
        ]);
    }
}
