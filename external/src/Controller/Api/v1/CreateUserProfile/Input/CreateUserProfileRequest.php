<?php

namespace App\Controller\Api\v1\CreateUserProfile\Input;

use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Symfony\Component\Validator\Constraints as Assert;

#[Exclude]
readonly final class CreateUserProfileRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public string $login,
        #[Assert\NotBlank]
        public string $password,
        #[Assert\NotBlank]
        public string $name,
        #[Assert\NotBlank]
        public string $surname,
    ) {
    }
}
