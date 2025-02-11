<?php

namespace App\Controller\Api\v1\CreateUser;

use App\Controller\Api\v1\CreateUser\Input\CreateUserRequest;
use App\Controller\Api\v1\CreateUser\Output\CreateUserResponse;
use App\Domain\DTO\CreateUserModel;
use App\Domain\Service\UserService;

readonly final class Manager
{
    public function __construct(private UserService $userService)
    {
    }

    public function createUser(CreateUserRequest $request): CreateUserResponse
    {
        $user = $this->userService->create(new CreateUserModel($request->login, $request->password));

        return new CreateUserResponse($user->getId());
    }
}
