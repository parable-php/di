<?php declare(strict_types=1);

namespace Parable\Di\Tests\Classes;

class ScalarDependencyWithDefaultAndNonScalarReverse
{
    public function __construct(
        string $nope = 'hello',
        NoDependencies $fakeObject
    ) {
    }
}
