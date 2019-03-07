<?php declare(strict_types=1);

namespace Parable\Di\Tests\Classes;

class CyclicalDependencyFirst
{
    public function __construct(
        CyclicalDependencySecond $second
    ) {
    }
}
