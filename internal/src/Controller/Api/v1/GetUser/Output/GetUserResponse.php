<?php

namespace App\Controller\Api\v1\GetUser\Output;

use OpenApi\Attributes as OA;

final readonly class GetUserResponse
{
    public function __construct(
        #[OA\Property(type: 'integer', nullable: false)]
        public int $id,
        #[OA\Property(type: 'string', nullable: false)]
        public string $login,
        #[OA\Property(type: 'string', nullable: false)]
        public string $password,
    ) {
    }
}
