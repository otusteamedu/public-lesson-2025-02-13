<?php

namespace App\Controller\Api\v1\CreateUser;

use App\Controller\Api\v1\CreateUser\Input\CreateUserRequest;
use App\Controller\Api\v1\CreateUser\Output\CreateUserResponse;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
readonly final class Controller
{
    public function __construct(
        private Manager $manager,
        private SerializerInterface $serializer,
    ) {
    }

    #[OA\Post(
        operationId: 'createUser',
        description: 'Создание пользователя',
        requestBody: new OA\RequestBody(content: new OA\JsonContent(ref: new Model(type: CreateUserRequest::class))),
        tags: ['user'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Успешный ответ',
                content: new Model(type: CreateUserResponse::class),
            ),
            new OA\Response(
                response: 400,
                description: 'Ошибка валидации',
            ),
        ],
    )]
    #[Route(path: '/api/v1/create-user', methods: ['POST'])]
    public function __invoke(#[MapRequestPayload] CreateUserRequest $request): Response
    {
        return new JsonResponse(
            $this->serializer->serialize($this->manager->createUser($request), JsonEncoder::FORMAT),
            Response::HTTP_OK,
            [],
            true
        );
    }
}
