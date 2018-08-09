<?php

namespace Parable\Di\Tests\Classes;

class FakeWithInterface implements FakeInterface
{
    public function getValue(): string
    {
        return "yo";
    }
}
