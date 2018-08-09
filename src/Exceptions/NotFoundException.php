<?php

namespace Parable\Di\Exceptions;

class NotFoundException extends AbstractException
{
    public static function fromId(string $id)
    {
        return self::fromMessage("No instance found stored for '%s'.", $id);
    }
}
