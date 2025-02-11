<?php

namespace App\Domain\Service;

use App\Domain\DTO\CreateUserModel;
use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly final class UserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserPasswordHasherInterface    $passwordHasher,
    ) {
    }

    public function findById(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    public function create(CreateUserModel $userModel): User
    {
        $user = new User();
        $user->setLogin($userModel->login);
        $user->setPassword($this->passwordHasher->hashPassword($user, $userModel->password));
        $this->userRepository->save($user);

        return $user;
    }
}
