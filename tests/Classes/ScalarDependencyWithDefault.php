<?php declare(strict_types=1);

namespace Parable\Di\Tests\Classes;

class ScalarDependencyWithDefault
{
    public function __construct(
        string $nope = 'hello'
    ) {
    }
}
