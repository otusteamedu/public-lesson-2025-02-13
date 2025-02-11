<?php

namespace App\Controller\Api\v1\CreateUserProfile;

use App\Controller\Api\v1\CreateUserProfile\Input\CreateUserProfileRequest;
use App\Controller\Api\v1\CreateUserProfile\Output\CreateUserProfileResponse;
use App\Domain\DTO\CreateUserProfileModel;
use App\Domain\Service\UserProfileService;

readonly final class Manager
{
    public function __construct(private UserProfileService $userService)
    {
    }

    public function createUser(CreateUserProfileRequest $request): CreateUserProfileResponse
    {
        $user = $this->userService->create(
            new CreateUserProfileModel($request->login, $request->password, $request->name, $request->surname)
        );

        return new CreateUserProfileResponse($user->getId());
    }
}
