<?php declare(strict_types=1);

namespace Parable\Di\Tests\Classes;

class ScalarDependencyWithDefaultAndNonScalarReverse
{
    // yeah yeah, it's literally the point
    public function __construct(
        string $nope = 'hello',
        NoDependencies $fakeObject
    ) {
    }
}
