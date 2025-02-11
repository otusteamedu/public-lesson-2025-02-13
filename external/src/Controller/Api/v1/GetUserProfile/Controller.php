<?php

namespace App\Controller\Api\v1\GetUserProfile;

use App\Controller\Api\v1\GetUserProfile\Output\GetUserProfileResponse;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
final readonly class Controller
{
    public function __construct(
        private Manager $manager,
        private SerializerInterface $serializer,
    ) {
    }

    #[Route(path: '/api/v1/get-user-profile/{id}', requirements: ['id'=>'\d+'], methods: ['GET'])]
    public function __invoke(int $id): Response
    {
        $result = $this->manager->getUser($id);

        return $result === null ?
            new Response(null, Response::HTTP_NOT_FOUND) :
            new JsonResponse(
                $this->serializer->serialize($result, JsonEncoder::FORMAT),
                Response::HTTP_OK,
                [],
                true
            );
    }
}
