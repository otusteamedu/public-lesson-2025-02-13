<?php

namespace App\Domain\DTO;

readonly final class UserProfileModel
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
