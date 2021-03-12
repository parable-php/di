<?php declare(strict_types=1);

namespace Parable\Di\Exceptions;

use Exception;

class InstanceNotFoundException extends Exception
{
    public static function fromName(string $id)
    {
        return new self(sprintf(
            "No instance found stored for `%s`.",
            $id
        ));
    }
}
