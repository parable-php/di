<?php

namespace Parable\Di\Tests\Classes;

class BadDependency
{
    public function __construct(
        string $nope
    ) {
    }
}
