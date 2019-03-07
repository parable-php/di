<?php declare(strict_types=1);

namespace Parable\Di\Tests\Classes;

class ScalarDependencyWithDefaultAndNonScalar
{
    public function __construct(
        NoDependencies $fakeObject,
        string $nope = 'hello'
    ) {
    }
}
