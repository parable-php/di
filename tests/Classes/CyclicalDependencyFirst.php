<?php

namespace Parable\Di\Tests\Classes;

class CyclicalDependencyFirst
{
    public function __construct(
        CyclicalDependencySecond $second
    ) {
    }
}
