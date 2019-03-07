<?php declare(strict_types=1);

namespace Parable\Di\Tests\Classes;

class FakeWithInterfaceDependency
{
    public $fakeInterfaceObject;

    public function __construct(FakeInterface $fakeInterfaceObject)
    {
        $this->fakeInterfaceObject = $fakeInterfaceObject;
    }
}
