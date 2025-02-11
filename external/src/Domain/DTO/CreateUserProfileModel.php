<?php

namespace App\Domain\DTO;

readonly final class CreateUserProfileModel
{
    public function __construct(
        public string $login,
        public string $password,
        public string $name,
        public string $surname,
    ) {
    }
}
