<?php

namespace Parable\Di\Tests\Classes;

class ScalarDependencyWithDefault
{
    public function __construct(
        string $nope = 'hello'
    ) {
    }
}
