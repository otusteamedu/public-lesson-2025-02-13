<?php

namespace App\Domain\DTO;

readonly final class CreateUserModel
{
    public function __construct(
        public string $login,
        public string $password,
    ) {
    }
}
