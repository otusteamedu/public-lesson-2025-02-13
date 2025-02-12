<?php

namespace App\Infrastructure\Repository;

use App\ApiClient;
use App\Domain\DTO\CreateUserModel;
use App\Domain\DTO\UserModel;
use App\Domain\Repository\UserRepositoryInterface;
use App\DTO\CreateUserRequest;
use App\DTO\CreateUserResponse;
use App\DTO\GetUserParameterData;
use App\DTO\GetUserResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class UserRepository implements UserRepositoryInterface
{
    public function __construct(private ApiClient $apiClient)
    {
    }

    public function findById(int $id): ?UserModel
    {
        $parameters = new GetUserParameterData();
        $parameters->id = $id;
        $response = $this->apiClient->getUser($parameters);
        if ($response[2] !== Response::HTTP_OK) {
            return null;
        }
        /** @var GetUserResponse $data */
        $data = $response[0];

        return new UserModel($data->id, $data->login, $data->password);
    }

    public function save(CreateUserModel $user): int
    {
        $request = new CreateUserRequest();
        $request->login = $user->login;
        $request->password = $user->password;
        $response = $this->apiClient->createUser($request);
        /** @var CreateUserResponse $data */
        $data = $response[0];

        return $data->id;
    }
}
