<?php

namespace Parable\Di\Exceptions;

abstract class AbstractException extends \Exception
{
    public static function fromMessage(string $message, ...$replacements): self
    {
        if (count($replacements) > 0) {
            $message = sprintf($message, ...$replacements);
        }

        return new static($message);
    }
}
