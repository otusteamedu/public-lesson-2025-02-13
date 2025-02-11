<?php

namespace App\Controller\Api\v1\GetUserProfile\Output;

final readonly class GetUserProfileResponse
{
    public function __construct(
        public int $id,
        public string $login,
        public string $password,
        public string $name,
        public string $surname,
    ) {
    }
}
