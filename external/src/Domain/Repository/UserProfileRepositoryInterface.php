<?php

namespace App\Domain\Repository;

use App\Domain\Entity\UserProfile;

interface UserProfileRepositoryInterface
{
    public function findById(int $id): ?UserProfile;

    public function save(UserProfile $user): void;
}
