<?php

namespace App\Exceptions;

use RuntimeException;

class InvalidTenantStatusTransitionException extends RuntimeException
{
    public static function unsupportedAction(string $action): self
    {
        return new self("Unsupported tenant status action [{$action}].");
    }
}
