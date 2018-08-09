## Parable DI Container

[![Build Status](https://travis-ci.org/parable-php/di.svg?branch=master)](https://travis-ci.org/parable-php/di)
[![Latest Stable Version](https://poser.pugx.org/parable-php/di/v/stable)](https://packagist.org/packages/parable-php/di)
[![Latest Unstable Version](https://poser.pugx.org/parable-php/di/v/unstable)](https://packagist.org/packages/parable-php/di)
[![License](https://poser.pugx.org/parable-php/di/license)](https://packagist.org/packages/parable-php/di)

A simple DI Container that gets the job done.

### Installation

```bash
$ composer require parable-php/di
```

### How to use

Example usage of a straightforward situation:

```php
use \Parable\Di\Container;

$container = new Container();

$app = $container->get(App::class);
$app->run();
```

Example usage of an interface-hinted dependency:

```php
use \Parable\Di\Container;

$container = new Container();

class App
{
    public function __construct(ThatInterface $classWithInterface)
    {
    }
}

$classWithInterface = $container->get(ClassWithInterface::class);
$container->store($classWithInterface, ThatInterface::class);

$app = $container->get(App::class);
$app->run();
```

Example usage of a class that needs the di itself:

```php
use \Parable\Di\Container;

$container = new Container();

class App
{
    public $container;
    public function __construct(\Parable\Di\Container $container)
    {
        $this->container = $container;
    }
}

$app = $container->get(App::class);
var_dump($app->container->has(App::class));

// output: bool(true)
```

For all other use cases, simply check the tests in `tests/DiTest.php`.

### Available public methods

- get(string $name)