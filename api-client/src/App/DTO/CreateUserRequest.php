<?php
declare(strict_types=1);

namespace App\DTO;

use Articus\DataTransfer\Annotation as DTA;

class CreateUserRequest
{
    /**
     * @DTA\Data(field="login")
     * @DTA\Validator(name="Scalar", options={"type":"string"})
     */
    public ?string $login = null;

    /**
     * @DTA\Data(field="password")
     * @DTA\Validator(name="Scalar", options={"type":"string"})
     */
    public ?string $password = null;

}
