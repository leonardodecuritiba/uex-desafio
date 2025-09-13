<?php

namespace App\Infrastructure\Http\Controllers\Contacts;

use App\Application\Contacts\UseCases\CreateContact;
use App\Application\Contacts\UseCases\CreateContactInput;
use App\Application\Contacts\Services\ContactGeocodeOrchestrator;
use App\Infrastructure\Http\Requests\Contacts\CreateContactRequest;
use App\Infrastructure\Http\Resources\Contacts\ContactResource;
use App\Infrastructure\Persistence\EloquentContactRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class CreateContactController extends Controller
{
    public function __construct(private readonly EloquentContactRepository $repo, private readonly ContactGeocodeOrchestrator $geo) {}

    public function __invoke(CreateContactRequest $request): JsonResponse
    {
        $useCase = new CreateContact($this->repo);

        $input = new CreateContactInput(
            userId: $request->user()->id,
            name: (string) $request->string('name'),
            cpf: $request->input('cpf'),
            email: $request->input('email'),
            phone: $request->input('phone'),
            address: $request->input('address') ?? null,
        );

        // Geocodificação inline, com degradação graciosa
        $input->address = $this->geo->onCreate($input->address);

        try {
            $output = $useCase->handle($input);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'errors' => [
                    ['field' => 'cpf', 'message' => $e->getMessage()],
                ],
            ], 422);
        }

        return response()->json([
            'data' => new ContactResource((object) [
                'id' => $output->id,
                'name' => $output->name,
                'cpf' => $output->cpf,
                'email' => $output->email,
                'phone' => $output->phone,
                'address' => $output->address,
                'created_at' => now(),
            ]),
        ], 201);
    }
}
