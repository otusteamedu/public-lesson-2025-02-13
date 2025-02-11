<?php

namespace App\Domain\DTO;

readonly final class UserModel
{
    public function __construct(
        public int $id,
        public string $login,
        public string $password,
    ) {
    }
}
