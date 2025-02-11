<?php

namespace App\Controller\Api\v1\GetUserProfile;

use App\Controller\Api\v1\GetUserProfile\Output\GetUserProfileResponse;
use App\Domain\Service\UserProfileService;

final readonly class Manager
{
    public function __construct(private UserProfileService $userProfileService)
    {
    }

    public function getUser(int $id): ?GetUserProfileResponse
    {
        $userProfile = $this->userProfileService->findById($id);

        return $userProfile === null ? null :
            new GetUserProfileResponse(
                $userProfile->id,
                $userProfile->login,
                $userProfile->password,
                $userProfile->name,
                $userProfile->surname,
            );
    }
}
