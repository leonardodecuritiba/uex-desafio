<?php

namespace Tests\Unit\Application\Auth;

use App\Application\Auth\UseCases\RegisterUserInput;
use App\Application\Auth\UseCases\RegisterUser;
use App\Domain\Auth\Repositories\UserRepository;
use App\Models\User;
use Illuminate\Contracts\Hashing\Hasher;
use App\Application\Exceptions\DomainValidationException;
use PHPUnit\Framework\TestCase;

class RegisterUserTest extends TestCase
{
    public function test_creates_user_when_input_valid(): void
    {
        $fakeRepo = new class implements UserRepository {
            public array $created = [];
            public function findByEmail(string $email): ?User
            {
                return null;
            }
            public function create(string $name, string $email, string $hashedPassword): User
            {
                $user = new User();
                $user->id = 1;
                $user->name = $name;
                $user->email = $email;
                // Avoid Eloquent password "hashed" cast and Facade usage in unit tests
                $user->setRawAttributes(array_merge($user->getAttributes(), [
                    'password' => $hashedPassword,
                ]), true);
                $this->created[] = $user;
                return $user;
            }
        };

        $fakeHasher = new class implements Hasher {
            public function make($value, array $options = [])
            {
                return password_hash($value, PASSWORD_BCRYPT);
            }
            public function check($value, $hashedValue, array $options = [])
            {
                return password_verify($value, $hashedValue);
            }
            public function needsRehash($hashedValue, array $options = [])
            {
                return false;
            }
            public function info($hashedValue)
            {
                return [];
            }
        };
        $useCase = new RegisterUser($fakeRepo, $fakeHasher);

        $input = new RegisterUserInput('Alice', 'alice@gmail.com', 'secret123');
        $out = $useCase->handle($input);

        $this->assertSame(1, $out->id);
        $this->assertSame('Alice', $out->name);
        $this->assertSame('alice@gmail.com', $out->email);
        $this->assertTrue($fakeHasher->check('secret123', $fakeRepo->created[0]->password));
    }

    public function test_rejects_duplicate_email(): void
    {
        $fakeRepo = new class implements UserRepository {
            public function findByEmail(string $email): ?User
            {
                $u = new User();
                $u->email = $email;
                return $u;
            }
            public function create(string $name, string $email, string $hashedPassword): User
            {
                throw new \RuntimeException('should not create');
            }
        };

        $fakeHasher = new class implements Hasher {
            public function make($value, array $options = [])
            {
                return password_hash($value, PASSWORD_BCRYPT);
            }
            public function check($value, $hashedValue, array $options = [])
            {
                return password_verify($value, $hashedValue);
            }
            public function needsRehash($hashedValue, array $options = [])
            {
                return false;
            }
            public function info($hashedValue)
            {
                return [];
            }
        };
        $useCase = new RegisterUser($fakeRepo, $fakeHasher);

        $this->expectException(DomainValidationException::class);
        $useCase->handle(new RegisterUserInput('Bob', 'bob@gmail.com', 'secret123'));
    }
}
