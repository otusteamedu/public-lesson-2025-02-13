<?php

namespace App\Infrastructure\Repository;

use App\Domain\DTO\CreateUserModel;
use App\Domain\DTO\UserModel;
use App\Domain\Repository\UserRepositoryInterface;

final readonly class UserRepository implements UserRepositoryInterface
{
    public function findById(int $id): ?UserModel
    {
        return new UserModel(0, '', '');
    }

    public function save(CreateUserModel $user): int
    {
        return 0;
    }
}
