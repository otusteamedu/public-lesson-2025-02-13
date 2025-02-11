<?php

namespace App\Controller\Api\v1\CreateUser\Input;

use OpenApi\Attributes as OA;
use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Symfony\Component\Validator\Constraints as Assert;

#[Exclude]
readonly final class CreateUserRequest
{
    public function __construct(
        #[OA\Property(type: 'string', nullable: false)]
        #[Assert\NotBlank]
        public string $login,
        #[OA\Property(type: 'string', nullable: false)]
        #[Assert\NotBlank]
        public string $password,
    ) {
    }
}
