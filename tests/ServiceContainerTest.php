<?php

declare( strict_types=1 );

namespace PisarevskiiTests\SimpleDIC;

use PHPUnit\Framework\TestCase;
use Pisarevskii\SimpleDIC\ServiceContainer;
use Pisarevskii\SimpleDIC\ServiceContainerException;
use Pisarevskii\SimpleDIC\ServiceContainerNotFoundException;
use PisarevskiiTests\SimpleDIC\Assets\ClassInvocable;
use PisarevskiiTests\SimpleDIC\Assets\ClassWithConstructorDeps;
use PisarevskiiTests\SimpleDIC\Assets\ClassWithConstructorDeps2;
use PisarevskiiTests\SimpleDIC\Assets\ClassWithConstructorDepsException;
use PisarevskiiTests\SimpleDIC\Assets\StaticClass;
use PisarevskiiTests\SimpleDIC\Assets\SimpleClass;
use stdClass;

final class ServiceContainerTest extends TestCase {
	private ?ServiceContainer $container;

	protected function setUp(): void {
		$this->container = new ServiceContainer();
	}

	protected function tearDown(): void {
		$this->container = null;
	}

	/**
	 * @return void
	 * @throws \Psr\Container\ContainerExceptionInterface
	 * @throws \Psr\Container\NotFoundExceptionInterface
	 */
	public function test_get__primitives() {
		$this->container->bind( $name = 'service', $value = 1 );
		self::assertSame( $value, $this->container->get( $name ) );

		$this->container->bind( $name = 'service', $value = '5' );
		self::assertSame( $value, $this->container->get( $name ) );

		$this->container->bind( $name = 'service', $value = 'string' );
		self::assertSame( $value, $this->container->get( $name ) );

		$this->container->bind( $name = 'service', $value = [ 'array' ] );
		self::assertSame( $value, $this->container->get( $name ) );
	}

	public function test_get__simple_class() {
		$this->container->bind( $name = 'service', $value = new stdClass() );
		self::assertSame( $value, $this->container->get( $name ) );
	}

	public function test_get__callback() {
		$this->container->bind( $name = 'service', function () {
			return new stdClass();
		} );
		self::assertEquals( new stdClass(), $this->container->get( $name ) );
	}

	public function test_get__callback_pass_params() {
		$this->container->bind( 'id', $value1 = 100 );
		$this->container->bind( 'title', $value2 = 'Title of article' );
		$this->container->bind( $service = 'service', function ( $c ) {
			$obj        = new stdClass();
			$obj->id = $c->get( 'id' );
			$obj->title = $c->get( 'title' );

			return $obj;
		} );

		$expected = new stdClass();
		$expected->id = $value1;
		$expected->title = $value2;
		self::assertEquals( $expected, $this->container->get( $service ) );
	}

	public function test_get__object_from_class() {
		$this->container->bind( $name = 'service', SimpleClass::class );
		self::assertEquals( new SimpleClass(), $this->container->get( $name ) );

		$this->container->bind( $name2 = 'service2', 'PisarevskiiTests\SimpleDIC\Assets\SimpleClass' );
		self::assertEquals( new SimpleClass(), $this->container->get( $name2 ) );
	}

	public function test_get__autowiring() {
		$obj1 = new SimpleClass();
		$this->container->bind( $name = SimpleClass::class, SimpleClass::class );
		self::assertEquals( new SimpleClass(), $this->container->get( $name ) );

		$obj2 = new ClassWithConstructorDeps( $obj1 );
		$this->container->bind( $name = ClassWithConstructorDeps::class, ClassWithConstructorDeps::class );
		self::assertEquals( $obj2, $this->container->get( $name ) );

		$obj3 = new ClassWithConstructorDeps2( $obj2 );
		$this->container->bind( $name = ClassWithConstructorDeps2::class, ClassWithConstructorDeps2::class );
		self::assertEquals( $obj3, $this->container->get( $name ) );
	}

	public function test_get__autowiring_invocable() {
		$this->container->bind( SimpleClass::class, SimpleClass::class );
		$this->container->bind( $name = ClassInvocable::class, ClassInvocable::class );

		self::assertInstanceOf( $name, $this->container->get( $name ) );
		self::assertSame( 'Function is called from ClassInvocable', $this->container->get( $name )() );
	}

	public function test_get__autowiring_container_not_found_exception() {
		self::expectException( ServiceContainerNotFoundException::class );

		$this->container->get( 'not-exist-service' );
	}

	public function test_get__autowiring_container_exception() {
		self::expectException( ServiceContainerException::class );

		$this->container->bind( SimpleClass::class, SimpleClass::class );
		$this->container->bind( ClassWithConstructorDepsException::class, ClassWithConstructorDepsException::class );
		$this->container->get( ClassWithConstructorDepsException::class );
	}

	public function test_has() {
		$this->container->bind( $name = 'service', new stdClass() );

		self::assertTrue( $this->container->has( $name ) );
		self::assertFalse( $this->container->has( 'not-exist' ) );
	}

// TODO:: do we need it?
//	public function test_get__static_method_from_array() {
//		$this->container->bind( $name = 'service', [ StaticClass::class, 'get_string' ] );
//		self::assertSame( StaticClass::get_string(), $this->container->get( $name ) );
//	}
}
