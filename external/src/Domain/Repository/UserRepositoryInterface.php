<?php

namespace App\Domain\Repository;

use App\Domain\DTO\CreateUserModel;
use App\Domain\DTO\UserModel;

interface UserRepositoryInterface
{
    public function findById(int $id): ?UserModel;

    public function save(CreateUserModel $user): int;
}
