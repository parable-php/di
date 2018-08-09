<?php

namespace Parable\Di;

use Parable\Di\Exceptions\ContainerException;
use Parable\Di\Exceptions\NotFoundException;
use ReflectionClass;

class Container
{
    public const STORED_DEPENDENCIES = 0;
    public const NEW_DEPENDENCIES    = 1;

    /**
     * @var object[]
     */
    protected $instances = [];

    /**
     * @var string[][]
     */
    protected $relationships = [];

    public function __construct()
    {
        /*
         * Store ourselves so we can inject this instance rather than creating
         * a secondary instance when requested.
         */
        $this->store($this);
    }

    /**
     * Returns a stored instance or creates a new one and stores it.
     *
     * @param string $id
     *
     * @return object
     */
    public function get(string $id)
    {
        $id = $this->normalize($id);

        if (!$this->has($id)) {
            $instance = $this->build($id);
            $this->store($instance);
        }

        return $this->instances[$id];
    }

    /**
     * Returns whether an instance is currently stored or not.
     *
     * @param string $id
     */
    public function has($id): bool
    {
        $id = $this->normalize($id);

        return isset($this->instances[$id]);
    }

    /**
     * Build a new instance with stored dependencies.
     *
     * @return object
     */
    public function build(string $id)
    {
        return $this->createInstance($id, self::STORED_DEPENDENCIES);
    }

    /**
     * Build a new instance with new dependencies.
     *
     * @return object
     */
    public function buildAll(string $id)
    {
        return $this->createInstance($id, self::NEW_DEPENDENCIES);
    }

    /**
     * Create an instance with either new or existing dependencies.
     *
     * @return object
     */
    protected function createInstance(string $id, int $storedDependencies)
    {
        $id = $this->normalize($id);

        if (interface_exists($id)) {
            throw ContainerException::fromMessage("Cannot create instance for interface '%s'.", $id);
        }

        try {
            $dependencies = $this->getDependenciesFor($id, $storedDependencies);
        } catch (\Exception $e) {
            throw ContainerException::fromMessage($e->getMessage());
        }

        return new $id(...$dependencies);
    }

    /**
     * Get the dependencies for an instance, based on the constructor.
     * Optionally use stored dependencies or always create new ones.
     *
     * @return object[]
     */
    public function getDependenciesFor(string $id, int $storedDependencies = self::STORED_DEPENDENCIES): array
    {
        $id = $this->normalize($id);

        try {
            $reflection = new ReflectionClass($id);
        } catch (\Exception $e) {
            throw ContainerException::fromMessage("Could not create instance of '%s'", $id);
        }

        $construct = $reflection->getConstructor();

        if (!$construct) {
            return [];
        }

        $parameters = $construct->getParameters();

        $relationships = [];
        $dependencies  = [];
        foreach ($parameters as $parameter) {
            $class = $parameter->getClass();
            if ($class === null) {
                throw ContainerException::fromMessage(
                    "Cannot inject value of type %s for constructor parameter \$%s",
                    $parameter->getType()->getName(),
                    $parameter->name
                );
            }

            $dependencyName = $this->normalize($class->name);

            $this->storeRelationship($id, $dependencyName);

            $relationships[] = $dependencyName;

            if ($storedDependencies === self::NEW_DEPENDENCIES) {
                $dependencies[] = $this->build($dependencyName);
            } elseif ($storedDependencies === self::STORED_DEPENDENCIES) {
                $dependencies[] = $this->get($dependencyName);
            } else {
                throw ContainerException::fromMessage('Invalid dependency type value passed: %d', $storedDependencies);
            }
        }

        return $dependencies;
    }

    /**
     * Clear the requested instance.
     */
    public function clear(string $id): void
    {
        $id = $this->normalize($id);

        if (!$this->has($id)) {
            throw NotFoundException::fromId($id);
        }

        unset($this->instances[$id]);

        $this->clearRelationship($id);
    }

    /**
     * Clear the relationship for the provided id.
     */
    protected function clearRelationship(string $id): void
    {
        // Clear from the left
        unset($this->relationships[$id]);
        // And clear from the right
        foreach ($this->relationships as $left => $right) {
            if ($right === $id) {
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
        foreach ($keep as $id) {
            $id = $this->normalize($id);

            if (!$this->has($id)) {
                throw NotFoundException::fromId($id);
            }

            $kept[$id] = $this->get($id);
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
     * Store the provided instance with the provided id, or the class name of the object.
     *
     * @param object $instance
     */
    public function store($instance, string $id = null): void
    {
        if ($id === null) {
            $id = get_class($instance);
        }

        $id = $this->normalize($id);

        $this->instances[$id] = $instance;
    }

    /**
     * Store the relationship between the two items.
     */
    protected function storeRelationship(string $class, string $dependency): void
    {
        $this->relationships[$class][$dependency] = true;

        if (isset($this->relationships[$class][$dependency]) && isset($this->relationships[$dependency][$class])) {
            throw ContainerException::fromMessage(
                "Cyclical dependency found between '%s' and '%s'.",
                $class,
                $dependency
            );
        }
    }

    /**
     * Normalize the id so it never has a prefixed \
     */
    protected function normalize(string $id): string
    {
        return ltrim($id, "\\");
    }
}
