<?php declare(strict_types=1);

namespace Parable\Di\Tests\Classes;

class FakeWithInterfaceDependency
{
    public function __construct(
        public FakeInterface $fakeInterfaceObject
    ) {
    }
}
