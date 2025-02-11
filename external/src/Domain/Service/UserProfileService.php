<?php

namespace App\Domain\Service;

use App\Domain\DTO\CreateUserModel;
use App\Domain\DTO\CreateUserProfileModel;
use App\Domain\DTO\UserProfileModel;
use App\Domain\Entity\UserProfile;
use App\Domain\Repository\UserProfileRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;

readonly final class UserProfileService
{
    public function __construct(
        private UserProfileRepositoryInterface $userProfileRepository,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function findById(int $id): ?UserProfileModel
    {
        $user = $this->userRepository->findById($id);
        $userProfile = $this->userProfileRepository->findById($id);

        return ($user === null || $userProfile === null) ? null : new UserProfileModel(
            $id,
            $user->login,
            $user->password,
            $userProfile->getName(),
            $userProfile->getSurname(),
        );
    }

    public function create(CreateUserProfileModel $userProfileModel): UserProfile
    {
        $id = $this->userRepository->save(new CreateUserModel($userProfileModel->login, $userProfileModel->password));
        $userProfile = new UserProfile();
        $userProfile->setId($id);
        $userProfile->setName($userProfileModel->name);
        $userProfile->setSurname($userProfileModel->surname);
        $this->userProfileRepository->save($userProfile);

        return $userProfile;
    }
}
