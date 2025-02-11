<?php

namespace App\Controller\Api\v1\CreateUserProfile\Output;

final readonly class CreateUserProfileResponse
{
    public function __construct(
        public int $id,
    ) {
    }
}
