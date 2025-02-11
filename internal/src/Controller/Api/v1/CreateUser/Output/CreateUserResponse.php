<?php

namespace App\Controller\Api\v1\CreateUser\Output;

use OpenApi\Attributes as OA;

final readonly class CreateUserResponse
{
    public function __construct(
        #[OA\Property(type: 'integer', nullable: false)]
        public int $id,
    ) {
    }
}
