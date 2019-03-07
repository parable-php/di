<?php declare(strict_types=1);

namespace Parable\Di\Tests\Classes;

class CyclicalDependencySecond
{
    public function __construct(
        CyclicalDependencyFirst $second
    ) {
    }
}
