<?php

namespace Tests\Unit\Infrastructure\Persistence;

use App\Infrastructure\Persistence\EloquentContactRepository;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EloquentContactRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_find_by_id_for_user_returns_only_when_owner_matches(): void
    {
        $repo = new EloquentContactRepository();
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();
        $c1 = Contact::factory()->create(['user_id' => $u1->id]);
        $c2 = Contact::factory()->create(['user_id' => $u2->id]);

        $found = $repo->findByIdForUser($c1->id, $u1->id);
        $this->assertNotNull($found);
        $this->assertSame($c1->id, $found->id);

        $notFound = $repo->findByIdForUser($c2->id, $u1->id);
        $this->assertNull($notFound);
    }
}

