<?php

namespace App\Controller\Api\v1\GetUser;

use App\Controller\Api\v1\GetUser\Output\GetUserResponse;
use App\Domain\Service\UserService;

final readonly class Manager
{
    public function __construct(private UserService $userService)
    {
    }

    public function getUser(int $id): ?GetUserResponse
    {
        $user = $this->userService->findById($id);

        return $user === null ? null : new GetUserResponse($user->getId(), $user->getLogin(), $user->getPassword());
    }
}
