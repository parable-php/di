<?php declare(strict_types=1);

namespace Parable\Di\Tests\Classes;

class Dependencies
{
    public string $value = 'totally different';

    public function __construct(
        public NoDependencies $fakeObject
    ) {
        $this->value = $fakeObject->value;
    }
}
