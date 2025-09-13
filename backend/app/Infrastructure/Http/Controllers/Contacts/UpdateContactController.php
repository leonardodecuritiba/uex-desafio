<?php

namespace App\Infrastructure\Http\Controllers\Contacts;

use App\Application\Contacts\UseCases\UpdateContact;
use App\Application\Contacts\UseCases\UpdateContactInput;
use App\Infrastructure\Http\Requests\Contacts\UpdateContactRequest;
use App\Infrastructure\Http\Resources\Contacts\ContactResource;
use App\Infrastructure\Persistence\EloquentContactRepository;
use App\Http\Controllers\Controller;
use App\Models\Contact as ContactModel;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use RuntimeException;
use App\Application\Contacts\Services\ContactGeocodeOrchestrator;

class UpdateContactController extends Controller
{
    public function __construct(private readonly EloquentContactRepository $repo, private readonly ContactGeocodeOrchestrator $geo) {}

    public function __invoke(int $id, UpdateContactRequest $request): JsonResponse
    {
        $model = ContactModel::find($id);
        if (! $model) {
            return response()->json(['message' => 'Contato não encontrado.'], 404);
        }
        if ($model->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        // Concorrência otimista opcional
        $version = $request->input('version');
        if ($version && $model->updated_at && $model->updated_at->toISOString() !== $version) {
            return response()->json(['message' => 'Conflito de versão.'], 409);
        }

        $useCase = new UpdateContact($this->repo);
        $provided = [];
        foreach (['name', 'cpf', 'email', 'phone'] as $field) {
            if ($request->has($field)) {
                $provided[$field] = true;
            }
        }

        $incomingAddress = $request->has('address') ? ($request->input('address') ?? null) : null;
        // Geocodificar somente se mudou e elegível (orquestrador decide); degradação graciosa
        $nextAddress = $this->geo->onUpdate($model->address ?? [], $incomingAddress);

        $input = new UpdateContactInput(
            id: $id,
            userId: $request->user()->id,
            name: $request->has('name') ? (string) $request->input('name') : null,
            cpf: $request->has('cpf') ? $request->input('cpf') : null,
            email: $request->has('email') ? $request->input('email') : null,
            phone: $request->has('phone') ? $request->input('phone') : null,
            address: $nextAddress,
            provided: $provided,
            addressProvided: $request->has('address'),
        );

        try {
            $output = $useCase->handle($input);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'errors' => [
                    ['field' => 'cpf', 'message' => $e->getMessage()],
                ],
            ], 422);
        } catch (RuntimeException $e) {
            if ($e->getMessage() === 'NOT_FOUND') {
                return response()->json(['message' => 'Contato não encontrado.'], 404);
            }
            throw $e;
        }

        // atualizar o modelo para o timestamp
        $model->refresh();

        return response()->json([
            'data' => new ContactResource((object) [
                'id' => $output->id,
                'name' => $output->name,
                'cpf' => $output->cpf,
                'email' => $output->email,
                'phone' => $output->phone,
                'address' => $output->address,
                'created_at' => $model->created_at,
                'updated_at' => $model->updated_at,
            ]),
        ]);
    }
}
