<?php

namespace Parable\Di;

use Parable\Di\Exceptions\ContainerException;
use Parable\Di\Exceptions\NotFoundException;
use ReflectionClass;

class Container
{
    public const USE_STORED_DEPENDENCIES = 0;
    public const USE_NEW_DEPENDENCIES    = 1;

    /**
     * @var object[]
     */
    protected $instances = [];

    /**
     * @var string[][]
     */
    protected $relationships = [];

    /**
     * @var string[]
     */
    protected $maps;

    public function __construct()
    {
        /*
         * Store ourself so we can inject the current instance rather than creating
         * a secondary instance when requested.
         */
        $this->store($this);
    }

    /**
     * Returns a stored instance or creates a new one and stores it.
     *
     * @return object
     */
    public function get(string $name)
    {
        $name = $this->getDefinitiveName($name);

        if (!$this->has($name)) {
            $instance = $this->build($name);
            $this->store($instance);
        }

        return $this->instances[$name];
    }

    /**
     * Returns whether an instance is currently stored or not.
     */
    public function has(string $name): bool
    {
        $name = $this->getDefinitiveName($name);

        return isset($this->instances[$name]);
    }

    /**
     * Build a new instance with stored dependencies.
     *
     * @return object
     */
    public function build(string $name)
    {
        return $this->createInstance($name, self::USE_STORED_DEPENDENCIES);
    }

    /**
     * Build a new instance with new dependencies.
     *
     * @return object
     */
    public function buildAll(string $name)
    {
        return $this->createInstance($name, self::USE_NEW_DEPENDENCIES);
    }

    /**
     * Create an instance with either new or existing dependencies.
     *
     * @return object
     */
    protected function createInstance(string $name, int $useStoredDependencies)
    {
        $name = $this->getDefinitiveName($name);

        if (interface_exists($name)) {
            throw ContainerException::fromMessage("Cannot create instance for interface '%s'.", $name);
        }

        try {
            $dependencies = $this->getDependenciesFor($name, $useStoredDependencies);
        } catch (\Exception $e) {
            throw ContainerException::fromMessage($e->getMessage());
        }

        return new $name(...$dependencies);
    }

    /**
     * Map the requested name to the replacement name. When the requested
     * name is retrieved, the replacement name will be used to build the instance.
     */
    public function map(string $requested, string $replacement): void
    {
        $this->maps[$this->normalize($requested)] = $this->normalize($replacement);
    }

    /**
     * Return the mapping if it exists, otherwise just return the requested name.
     */
    protected function getMapIfExists(string $requested): string
    {
        return $this->maps[$requested] ?? $requested;
    }

    /**
     * Get the dependencies for an instance, based on the constructor.
     * Optionally use stored dependencies or always create new ones.
     *
     * @return object[]
     */
    public function getDependenciesFor(string $name, int $useStoredDependencies = self::USE_STORED_DEPENDENCIES): array
    {
        $name = $this->getDefinitiveName($name);

        try {
            $reflection = new ReflectionClass($name);
        } catch (\Exception $e) {
            throw ContainerException::fromMessage('Could not create instance of %s', $name);
        }

        $constructor = $reflection->getConstructor();

        if (!$constructor) {
            return [];
        }

        $parameters = $constructor->getParameters();

        $relationships = [];
        $dependencies  = [];
        foreach ($parameters as $parameter) {
            $class = $parameter->getClass();
            if ($class === null) {
                throw ContainerException::fromMessage(
                    'Cannot inject value of type %s for constructor parameter $%s',
                    $parameter->getType()->getName(),
                    $parameter->name
                );
            }

            $dependencyName = $this->getDefinitiveName($class->name);

            $this->storeRelationship($name, $dependencyName);

            $relationships[] = $dependencyName;

            if ($useStoredDependencies === self::USE_NEW_DEPENDENCIES) {
                $dependencies[] = $this->build($dependencyName);
            } elseif ($useStoredDependencies === self::USE_STORED_DEPENDENCIES) {
                $dependencies[] = $this->get($dependencyName);
            } else {
                throw ContainerException::fromMessage(
                    'Invalid dependency type value passed: %d',
                    $useStoredDependencies
                );
            }
        }

        return $dependencies;
    }

    /**
     * Store the provided instance with the provided id, or the class name of the object.
     *
     * @param object $instance
     */
    public function store($instance, string $name = null): void
    {
        if ($name === null) {
            $name = get_class($instance);
        }

        $name = $this->getDefinitiveName($name);

        $this->instances[$name] = $instance;
    }

    /**
     * Clear the requested instance.
     */
    public function clear(string $name): void
    {
        $name = $this->getDefinitiveName($name);

        if (!$this->has($name)) {
            throw NotFoundException::fromId($name);
        }

        unset($this->instances[$name]);

        $this->clearRelationship($name);
    }

    /**
     * Clear the relationship for the provided id.
     */
    protected function clearRelationship(string $name): void
    {
        // Clear from the left
        unset($this->relationships[$name]);

        // And clear from the right
        foreach ($this->relationships as $left => $right) {
            if ($right === $name) {
                unset($this->relationships[$left]);
            }
        }
    }

    /**
     * Clear all instances except those provided.
     *
     * @param string[] $keep
     */
    public function clearExcept(array $keep): void
    {
        $kept = [];
        foreach ($keep as $name) {
            $name = $this->getDefinitiveName($name);

            if (!$this->has($name)) {
                throw NotFoundException::fromId($name);
            }

            $kept[$name] = $this->get($name);
        }

        $this->instances = $kept;
    }

    /**
     * Clear all instances and all relationships.
     */
    public function clearAll(): void
    {
        $this->instances     = [];
        $this->relationships = [];
    }

    /**
     * Store the relationship between the two items.
     */
    protected function storeRelationship(string $class, string $dependency): void
    {
        $this->relationships[$class][$dependency] = true;

        if (isset($this->relationships[$class][$dependency]) && isset($this->relationships[$dependency][$class])) {
            throw ContainerException::fromMessage(
                'Cyclical dependency found between %s and %s.',
                $class,
                $dependency
            );
        }
    }

    /**
     * Normalize the name so it never has a prefixed \, and return
     * the most appropriate name based on what's being requested.
     */
    protected function normalize(string $name): string
    {
        return ltrim($name, '\\');
    }

    /**
     * Get the definitive name for the provided string. If it's mapped,
     * get the replacement name. Always makes sure the name is normalized.
     */
    protected function getDefinitiveName(string $name): string
    {
        return $this->getMapIfExists($this->normalize($name));
    }
}
