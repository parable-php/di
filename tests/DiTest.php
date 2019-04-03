<?php declare(strict_types=1);

namespace Parable\Di\Tests;

use Parable\Di\Container;
use Parable\Di\Exceptions\ContainerException;
use Parable\Di\Exceptions\NotFoundException;
use Parable\Di\Tests\Classes\ScalarDependency;
use Parable\Di\Tests\Classes\CyclicalDependencyFirst;
use Parable\Di\Tests\Classes\CyclicalDependencySecond;
use Parable\Di\Tests\Classes\Dependencies;
use Parable\Di\Tests\Classes\DiAsDependency;
use Parable\Di\Tests\Classes\FakeInterface;
use Parable\Di\Tests\Classes\FakeWithInterface;
use Parable\Di\Tests\Classes\FakeWithInterfaceDependency;
use Parable\Di\Tests\Classes\NoDependencies;
use Parable\Di\Tests\Classes\ScalarDependencyWithDefault;
use Parable\Di\Tests\Classes\ScalarDependencyWithDefaultAndNonScalar;
use Parable\Di\Tests\Classes\ScalarDependencyWithDefaultAndNonScalarReverse;

class DiTest extends \PHPUnit\Framework\TestCase
{
    /** @var Container */
    protected $container;

    public function setUp()
    {
        $this->container = new Container();
    }

    public function testGetStoresAndRetrievesInstance()
    {
        $instance1 = $this->container->get(NoDependencies::class);
        $instance2 = $this->container->get(NoDependencies::class);

        self::assertInstanceOf(NoDependencies::class, $instance1);
        self::assertInstanceOf(NoDependencies::class, $instance2);
        self::assertSame($instance1, $instance2);
    }

    public function testHasWorksAsExpected()
    {
        self::assertFalse($this->container->has(NoDependencies::class));

        $this->container->get(NoDependencies::class);

        self::assertTrue($this->container->has(NoDependencies::class));
    }

    public function testBuildDoesNotStoreInstanceButDoesStoreDependencies()
    {
        self::assertFalse($this->container->has(NoDependencies::class));
        self::assertFalse($this->container->has(Dependencies::class));

        $this->container->build(Dependencies::class);

        // Since build uses stored dependencies, it will also create and store them
        self::assertTrue($this->container->has(NoDependencies::class));
        self::assertFalse($this->container->has(Dependencies::class));
    }

    public function testBuildReturnsInstanceWithStoredDependencies()
    {
        $noDependencies = $this->container->get(NoDependencies::class);
        $noDependencies->value = 'this has been changed';

        $dependencies = $this->container->build(Dependencies::class);

        self::assertSame($noDependencies, $dependencies->fakeObject);
        self::assertSame('this has been changed', $dependencies->fakeObject->value);
    }

    public function testBuildAllDoesNotStore()
    {
        self::assertFalse($this->container->has(NoDependencies::class));
        self::assertFalse($this->container->has(Dependencies::class));

        $this->container->buildAll(Dependencies::class);

        self::assertFalse($this->container->has(NoDependencies::class));
        self::assertFalse($this->container->has(Dependencies::class));
    }

    public function testBuildAllReturnsInstanceWithNewDependencies()
    {
        $noDependencies = $this->container->get(NoDependencies::class);
        $noDependencies->value = 'this has been changed';

        $dependencies = $this->container->buildAll(Dependencies::class);

        self::assertNotSame($noDependencies, $dependencies->fakeObject);
        self::assertSame('new', $dependencies->fakeObject->value);
    }

    public function testStoreCanStoreInstance()
    {
        $noDependencies = new NoDependencies();

        $this->container->store($noDependencies, "stored");

        self::assertTrue($this->container->has("stored"));
        self::assertSame($noDependencies, $this->container->get("stored"));

        self::assertFalse($this->container->has(NoDependencies::class));
    }

    public function testCreateInstanceWithInterfaceDependencyThrows()
    {
        self::expectException(ContainerException::class);
        self::expectExceptionMessage("Cannot create instance for interface `Parable\Di\Tests\Classes\FakeInterface`.");

        $this->container->get(FakeWithInterfaceDependency::class);
    }

    public function testStoreCanStoreInstanceForInterfaceGet()
    {
        $interfaceImplementor = $this->container->get(FakeWithInterface::class);
        $this->container->store($interfaceImplementor, FakeInterface::class);

        $fakeWithInterface = $this->container->get(FakeWithInterfaceDependency::class);

        self::assertInstanceOf(FakeInterface::class, $fakeWithInterface->fakeInterfaceObject);
    }

    public function testMappingWorksForInterface()
    {
        $this->container->map(FakeInterface::class, FakeWithInterface::class);
        $fakeWithInterface = $this->container->get(FakeWithInterfaceDependency::class);

        self::assertInstanceOf(FakeInterface::class, $fakeWithInterface->fakeInterfaceObject);
        self::assertInstanceOf(FakeWithInterface::class, $fakeWithInterface->fakeInterfaceObject);

        self::assertTrue($this->container->has(FakeInterface::class));
        self::assertTrue($this->container->has(FakeWithInterface::class));
    }

    public function testGetDependenciesForWorks()
    {
        $noDependencies = $this->container->get(NoDependencies::class);
        $noDependencies->value = 'nope';

        $dependencies = $this->container->getDependenciesFor(Dependencies::class);

        $instance = new Dependencies(...$dependencies);

        self::assertInstanceOf(Dependencies::class, $instance);
        self::assertInstanceOf(NoDependencies::class, $instance->fakeObject);
        self::assertSame('nope', $instance->fakeObject->value);
    }

    public function testGetDependenciesForThrowsOnBadId()
    {
        self::expectException(ContainerException::class);
        self::expectExceptionMessage("Could not create instance for class `bla`.");

        $this->container->getDependenciesFor("bla");
    }

    public function testGetDependenciesForThrowsOnStringConstructorParameter()
    {
        self::expectException(ContainerException::class);
        self::expectExceptionMessage(
            'Cannot inject value for non-optional constructor parameter `$nope` without a default value.'
        );

        $this->container->getDependenciesFor(ScalarDependency::class);
    }

    public function testGetDependenciesForScalarWithDefaultSetsDefaultValueAppropriately()
    {
        $dependencies = $this->container->getDependenciesFor(ScalarDependencyWithDefault::class);

        self::assertSame(
            ['hello'],
            $dependencies
        );
    }

    public function testGetDependenciesForWillUseDefaultValueForScalarIfMixedWithActualDependency()
    {
        $dependencies = $this->container->getDependenciesFor(ScalarDependencyWithDefaultAndNonScalar::class);

        self::assertInstanceOf(NoDependencies::class, $dependencies[0]);
        self::assertSame('hello', $dependencies[1]);
    }

    public function testOptionalBeforeRequiredBreaksGetDependenciesFor()
    {
        self::expectException(ContainerException::class);
        self::expectExceptionMessage(
            'Cannot inject value for non-optional constructor parameter `$nope` without a default value.'
        );

        $this->container->getDependenciesFor(ScalarDependencyWithDefaultAndNonScalarReverse::class);
    }

    public function testGetDependenciesForWithNewDependenciesWorks()
    {
        $noDependencies = $this->container->get(NoDependencies::class);
        $noDependencies->value = 'nope';

        $dependencies = $this->container->getDependenciesFor(Dependencies::class, Container::USE_NEW_DEPENDENCIES);

        $instance = new Dependencies(...$dependencies);

        self::assertInstanceOf(Dependencies::class, $instance);
        self::assertInstanceOf(NoDependencies::class, $instance->fakeObject);

        self::assertNotSame($noDependencies, $instance->fakeObject);
        self::assertSame('new', $instance->fakeObject->value);
    }

    public function testGetDependenciesForDoesntLikeInvalidValuePassed()
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Invalid dependency type value passed: `666`');

        $this->container->getDependenciesFor(Dependencies::class, 666);
    }

    public function testIdsAreNormalized()
    {
        $this->container->get(NoDependencies::class);

        self::assertTrue($this->container->has(NoDependencies::class));
        self::assertTrue($this->container->has("Parable\Di\Tests\Classes\NoDependencies"));
        self::assertTrue($this->container->has("\Parable\Di\Tests\Classes\NoDependencies"));
        self::assertTrue($this->container->has("\\Parable\\Di\\Tests\\Classes\\NoDependencies"));
    }

    public function testClearWorks()
    {
        $this->container->get(NoDependencies::class);
        $this->container->get(Dependencies::class);

        self::assertTrue($this->container->has(NoDependencies::class));
        self::assertTrue($this->container->has(Dependencies::class));

        $this->container->clear(NoDependencies::class);

        self::assertFalse($this->container->has(NoDependencies::class));
        self::assertTrue($this->container->has(Dependencies::class));
    }

    public function testClearThrowsForMissingInstance()
    {
        self::expectException(NotFoundException::class);
        self::expectExceptionMessage("No instance found stored for `Parable\Di\Tests\Classes\NoDependencies`.");

        $this->container->clear(NoDependencies::class);
    }

    public function testClearAllWorks()
    {
        $this->container->get(NoDependencies::class);
        $this->container->get(Dependencies::class);

        self::assertTrue($this->container->has(NoDependencies::class));
        self::assertTrue($this->container->has(Dependencies::class));

        $this->container->clearAll();

        self::assertFalse($this->container->has(NoDependencies::class));
        self::assertFalse($this->container->has(Dependencies::class));
    }

    public function testClearExceptWorks()
    {
        $this->container->get(NoDependencies::class);
        $this->container->get(Dependencies::class);

        self::assertTrue($this->container->has(NoDependencies::class));
        self::assertTrue($this->container->has(Dependencies::class));

        $this->container->clearExcept([NoDependencies::class]);

        self::assertTrue($this->container->has(NoDependencies::class));
        self::assertFalse($this->container->has(Dependencies::class));
    }

    public function testClearExceptThrowsOnMissingInstance()
    {
        self::expectException(NotFoundException::class);
        self::expectExceptionMessage("No instance found stored for `Parable\Di\Tests\Classes\NoDependencies`.");

        $this->container->clearExcept([NoDependencies::class]);
    }

    public function testThrowsOnCyclicalDependency()
    {
        self::expectException(ContainerException::class);
        self::expectExceptionMessage(sprintf(
            "Cyclical dependency found between `%s` and `%s`.",
            CyclicalDependencySecond::class,
            CyclicalDependencyFirst::class
        ));

        $this->container->get(CyclicalDependencyFirst::class);
    }

    public function testDiContainerCanBeInjected()
    {
        $instance = $this->container->get(DiAsDependency::class);

        $this->assertSame($instance->container, $this->container);
    }
}
