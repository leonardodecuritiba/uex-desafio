<?php

namespace App\Infrastructure\Http\Controllers\Contacts;

use App\Application\Contacts\UseCases\SearchContacts;
use App\Domain\Common\Pagination;
use App\Domain\Common\Sort;
use App\Domain\Contacts\DTOs\ContactSearchFilters;
use App\Http\Controllers\Controller;
use App\Infrastructure\Http\Requests\Contacts\SearchContactsRequest;
use App\Infrastructure\Http\Resources\Contacts\ContactListItemResource;
use App\Infrastructure\Persistence\EloquentContactRepository;
use Illuminate\Http\JsonResponse;

class SearchContactsController extends Controller
{
    public function __construct(private readonly EloquentContactRepository $repo) {}

    public function __invoke(SearchContactsRequest $request): JsonResponse
    {
        $userId = $request->user()->id;

        $q = $request->input('q');
        $name = $request->input('name');
        $cpf = $request->input('cpf');
        $hasGeo = $request->has('has_geo') ? filter_var($request->input('has_geo'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null;

        $filters = new ContactSearchFilters(
            q: $q,
            name: $name,
            cpf: $cpf ? preg_replace('/\D+/', '', (string) $cpf) : null,
            hasGeo: $hasGeo,
        );

        // q discrimination: if q is 11 digits, prefer as cpf when cpf not provided
        if (! $filters->cpf && $filters->qLooksLikeCpf()) {
            $filters->cpf = $filters->qDigits();
        }

        $page = (int) ($request->input('page') ?? 1);
        $perPage = (int) ($request->input('per_page') ?? 20);
        $pg = new Pagination(page: max(1, $page), perPage: min(100, max(1, $perPage)));

        $sortBy = (string) ($request->input('sort') ?? 'created_at');
        $order = (string) ($request->input('order') ?? 'desc');
        $sort = new Sort(by: $sortBy, order: $order);

        $useCase = new SearchContacts($this->repo);
        $result = $useCase->handle($userId, $filters, $pg, $sort);

        return response()->json([
            'data' => ContactListItemResource::collection(collect($result->items)),
            'meta' => [
                'page' => $result->page,
                'per_page' => $result->perPage,
                'total' => $result->total,
                'last_page' => $result->lastPage,
                'sort' => $result->sortBy,
                'order' => $result->order,
                'filters' => $filters->toArray(),
            ],
        ]);
    }
}
