<?php declare(strict_types=1);

namespace Parable\Di\Tests\Classes;

class FakeWithInterface implements FakeInterface
{
    public function getValue(): string
    {
        return "yo";
    }
}
