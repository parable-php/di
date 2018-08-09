## Parable DI Container

A simple DI Container that gets the job done.

Example usage of a straightforward situation:

```php
use \Parable\Di\Container;

$app = $container->get(App::class);
$app->run();
```

Example usage of an interface-hinted dependency:

```php
use \Parable\Di\Container;

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