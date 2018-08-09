<?php

namespace Parable\Di\Tests\Classes;

class CyclicalDependencySecond
{
    public function __construct(
        CyclicalDependencyFirst $second
    ) {
    }
}
