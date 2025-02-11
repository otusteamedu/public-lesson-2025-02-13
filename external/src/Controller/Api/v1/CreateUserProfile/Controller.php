<?php

namespace App\Controller\Api\v1\CreateUserProfile;

use App\Controller\Api\v1\CreateUserProfile\Input\CreateUserProfileRequest;
use App\Controller\Api\v1\CreateUserProfile\Output\CreateUserProfileResponse;
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

    #[Route(path: '/api/v1/create-user-profile', methods: ['POST'])]
    public function __invoke(#[MapRequestPayload] CreateUserProfileRequest $request): Response
    {
        return new JsonResponse(
            $this->serializer->serialize($this->manager->createUser($request), JsonEncoder::FORMAT),
            Response::HTTP_OK,
            [],
            true
        );
    }
}
