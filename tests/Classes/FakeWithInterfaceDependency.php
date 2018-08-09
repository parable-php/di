<?php

namespace Parable\Di\Tests\Classes;

class FakeWithInterfaceDependency
{
    public $fakeInterfaceObject;

    public function __construct(FakeInterface $fakeInterfaceObject)
    {
        $this->fakeInterfaceObject = $fakeInterfaceObject;
    }
}
