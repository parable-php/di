# Parable DI Container

[![Build Status](https://travis-ci.com/parable-php/di.svg?branch=master)](https://travis-ci.com/parable-php/di)
[![Latest Stable Version](https://poser.pugx.org/parable-php/di/v/stable)](https://packagist.org/packages/parable-php/di)
[![Latest Unstable Version](https://poser.pugx.org/parable-php/di/v/unstable)](https://packagist.org/packages/parable-php/di)
[![License](https://poser.pugx.org/parable-php/di/license)](https://packagist.org/packages/parable-php/di)

Parable DI is a no-nonsense dependency injection container that gets the job done.

## Install

Php 8.0+ and [composer](https://getcomposer.org) are required.

```bash
$ composer require parable-php/di
```

## Usage

Example usage of a straightforward situation:

```php
use \Parable\Di\Container;

$container = new Container();

$app = $container->get(App::class);
$app->run();
```

Example usage of an interface-hinted dependency being mapped:

```php
use \Parable\Di\Container;

$container = new Container();

class App
{
    public function __construct(ThatInterface $classWithInterface)
    {
    }
    
    public function run()
    {
        echo "Run? RUN!";
    }
}

$container->map(ThatInterface::class, ClassWithInterface::class);

$app = $container->get(App::class);
$app->run();
```

The above situation can also be solved by instantiating and then storing `ClassWithInterface` under `ThatInterface`.

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

## API

- `get(string $name): object` - creates or gets instance
- `has(string $name): bool` - check if instance is stored
- `build(string $name): object` - build instance with stored deps, don't store
- `buildAll(string $name): object` - build instance with new deps, don't store
- `map(string $requested, string $replacement): void` - allow pre-emptively defining a replacement class to be instantiated when the requested name is retrieved or built. Use for lazy-loading classes, i.e. for interface deps.
- `getDependenciesFor(string $name, [int $storedDependencies]): array` - get dependencies for instance, with stored (default) or new deps 
- `store(object $instance, [string $name]): void` - store instance under name, or under instance name by default
- `clear(string $name): void` - clear instance
- `clearExcept(array $keep): void` - clear all except provided names
- `clearAll(): void` - clear all

Where `object` refers to any instance of any class.

## Contributing

Any suggestions, bug reports or general feedback is welcome. Use github issues and pull requests, or find me over at [devvoh.com](https://devvoh.com).

## License

All Parable components are open-source software, licensed under the MIT license.
