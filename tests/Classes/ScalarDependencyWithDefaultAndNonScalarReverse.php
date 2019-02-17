<?php

namespace Parable\Di\Tests\Classes;

class ScalarDependencyWithDefaultAndNonScalarReverse
{
    public function __construct(
        string $nope = 'hello',
        NoDependencies $fakeObject
    ) {
    }
}
