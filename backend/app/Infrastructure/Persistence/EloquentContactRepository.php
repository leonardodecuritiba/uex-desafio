<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Contacts\Entities\Contact as ContactEntity;
use App\Domain\Contacts\Repositories\ContactRepository;
use App\Models\Contact as ContactModel;
use App\Domain\Contacts\DTOs\ContactSearchFilters;
use App\Domain\Common\Pagination;
use App\Domain\Common\Sort;
use App\Domain\Common\PaginatedResult;

class EloquentContactRepository implements ContactRepository
{
    public function create(ContactEntity $contact): ContactEntity
    {
        $model = ContactModel::create([
            'user_id' => $contact->userId,
            'name' => $contact->name,
            'cpf' => $contact->cpf,
            'email' => $contact->email,
            'phone' => $contact->phone,
            'address' => $contact->address,
        ]);

        return new ContactEntity(
            id: $model->id,
            userId: $model->user_id,
            name: $model->name,
            cpf: $model->cpf,
            email: $model->email,
            phone: $model->phone,
            address: $model->address,
        );
    }

    public function existsCpfForUser(int $userId, string $cpf): bool
    {
        return ContactModel::where('user_id', $userId)->where('cpf', $cpf)->exists();
    }

    public function existsCpfForUserExceptId(int $userId, string $cpf, int $exceptId): bool
    {
        return ContactModel::where('user_id', $userId)
            ->where('cpf', $cpf)
            ->where('id', '!=', $exceptId)
            ->exists();
    }

    public function findById(int $id): ?ContactEntity
    {
        $m = ContactModel::find($id);
        if (! $m) return null;
        return new ContactEntity(
            id: $m->id,
            userId: $m->user_id,
            name: $m->name,
            cpf: $m->cpf,
            email: $m->email,
            phone: $m->phone,
            address: $m->address,
        );
    }

    public function findByIdForUser(int $id, int $userId): ?ContactEntity
    {
        $m = ContactModel::where('id', $id)->where('user_id', $userId)->first();
        if (! $m) return null;
        return new ContactEntity(
            id: $m->id,
            userId: $m->user_id,
            name: $m->name,
            cpf: $m->cpf,
            email: $m->email,
            phone: $m->phone,
            address: $m->address,
        );
    }

    public function update(ContactEntity $contact): ContactEntity
    {
        $m = ContactModel::findOrFail($contact->id);
        $m->fill([
            'name' => $contact->name,
            'cpf' => $contact->cpf,
            'email' => $contact->email,
            'phone' => $contact->phone,
        ]);
        // address merge já vem pronto na entidade
        $m->address = $contact->address;
        $m->save();

        return new ContactEntity(
            id: $m->id,
            userId: $m->user_id,
            name: $m->name,
            cpf: $m->cpf,
            email: $m->email,
            phone: $m->phone,
            address: $m->address,
        );
    }

    public function deleteByIdForUser(int $id, int $userId): bool
    {
        $m = ContactModel::where('id', $id)->where('user_id', $userId)->first();
        if (! $m) {
            return false;
        }
        return (bool) $m->delete();
    }

    public function search(int $userId, ContactSearchFilters $filters, Pagination $pg, Sort $sort): PaginatedResult
    {
        $query = ContactModel::query()->where('user_id', $userId);

        if ($filters->cpf) {
            $query->where('cpf', $filters->cpf);
        } else {
            if ($filters->name) {
                $query->whereRaw('LOWER(name) LIKE ?', ['%' . mb_strtolower($filters->name) . '%']);
            } elseif ($filters->q) {
                if ($filters->qLooksLikeCpf()) {
                    $query->where('cpf', $filters->qDigits());
                } else {
                    $query->whereRaw('LOWER(name) LIKE ?', ['%' . mb_strtolower($filters->q) . '%']);
                }
            }
        }

        if ($filters->hasGeo !== null) {
            if ($filters->hasGeo) {
                $query->whereNotNull('address->lat')->whereNotNull('address->lng');
            } else {
                $query->where(function ($q) {
                    $q->whereNull('address->lat')->orWhereNull('address->lng');
                });
            }
        }

        $sortCol = in_array($sort->by, ['created_at', 'name'], true) ? $sort->by : 'created_at';
        $order = $sort->order === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortCol, $order);

        $paginator = $query->paginate($pg->perPage, ['*'], 'page', $pg->page);
        $items = [];
        foreach ($paginator->items() as $m) {
            $items[] = new ContactEntity(
                id: $m->id,
                userId: $m->user_id,
                name: $m->name,
                cpf: $m->cpf,
                email: $m->email,
                phone: $m->phone,
                address: $m->address,
            );
        }

        return new PaginatedResult(
            items: $items,
            total: $paginator->total(),
            page: $paginator->currentPage(),
            perPage: $paginator->perPage(),
            lastPage: $paginator->lastPage(),
            sortBy: $sortCol,
            order: $order,
        );
    }
}
