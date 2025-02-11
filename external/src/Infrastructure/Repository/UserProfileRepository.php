<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\UserProfile;
use App\Domain\Repository\UserProfileRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

readonly final class UserProfileRepository implements UserProfileRepositoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(UserProfile $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function findById(int $id): ?UserProfile
    {
        return $this->entityManager->find(UserProfile::class, $id);
    }
}
