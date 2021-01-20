<?php declare(strict_types=1);

namespace Parable\Di\Tests\Classes;

use Parable\Di\Container;

class DiAsDependency
{
    public function __construct(
        public Container $container
    ) {
    }
}
