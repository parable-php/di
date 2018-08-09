<?php

namespace Parable\Di\Tests\Classes;

class Dependencies
{
    public $value = 'totally different';

    public $fakeObject;

    public function __construct(NoDependencies $fakeObject)
    {
        $this->fakeObject = $fakeObject;
        $this->value = $fakeObject->value;
    }
}
