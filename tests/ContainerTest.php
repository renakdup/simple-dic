<?php

declare( strict_types=1 );

namespace PisarevskiiTests\SimpleDIC;

use PHPUnit\Framework\TestCase;
use Pisarevskii\SimpleDIC\Container;
use Pisarevskii\SimpleDIC\ContainerException;
use Pisarevskii\SimpleDIC\ContainerNotFoundException;
use PisarevskiiTests\SimpleDIC\Assets\ClassInvocable;
use PisarevskiiTests\SimpleDIC\Assets\ClassWithConstructorDeps;
use PisarevskiiTests\SimpleDIC\Assets\ClassWithConstructorDeps2;
use PisarevskiiTests\SimpleDIC\Assets\ClassWithConstructorDepsException;
use PisarevskiiTests\SimpleDIC\Assets\StaticClass;
use PisarevskiiTests\SimpleDIC\Assets\SimpleClass;
use SplQueue;
use stdClass;

final class ContainerTest extends TestCase {
	private ?Container $container;

	protected function setUp(): void {
		$this->container = new Container();
	}

	protected function tearDown(): void {
		$this->container = null;
	}

	public function test_get__primitives() {
		$this->container->set( $name = 'service', $value = 1 );
		self::assertSame( $value, $this->container->get( $name ) );

		$this->container->set( $name = 'service', $value = '5' );
		self::assertSame( $value, $this->container->get( $name ) );

		$this->container->set( $name = 'service', $value = 'string' );
		self::assertSame( $value, $this->container->get( $name ) );

		$this->container->set( $name = 'service', $value = [ 'array' ] );
		self::assertSame( $value, $this->container->get( $name ) );

		$this->container->set( $name = 'service', $value = false );
		self::assertSame( $value, $this->container->get( $name ) );

		$this->container->set( $name = 'service', $value = null );
		self::assertSame( $value, $this->container->get( $name ) );
	}

	public function test_get__class() {
		$this->container->set( $name = 'service', $value = new stdClass() );
		self::assertSame( $value, $this->container->get( $name ) );
	}

	public function test_get__not_set_class() {
		self::assertEquals( new SplQueue(), $this->container->get( SplQueue::class ) );
	}

	public function test_get__callback() {
		$this->container->set( $name = 'service', function () {
			return new stdClass();
		} );
		self::assertEquals( new stdClass(), $this->container->get( $name ) );
	}

	public function test_get__callback_pass_params() {
		$this->container->set( 'id', $value1 = 100 );
		$this->container->set( 'title', $value2 = 'Title of article' );
		$this->container->set( $service = 'service', function ( $c ) {
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
		$this->container->set( $name = 'service', SimpleClass::class );
		self::assertEquals( new SimpleClass(), $this->container->get( $name ) );

		$this->container->set( $name2 = 'service2', 'PisarevskiiTests\SimpleDIC\Assets\SimpleClass' );
		self::assertEquals( new SimpleClass(), $this->container->get( $name2 ) );
	}

//	//TODO:: need to add supporting
//	public function test_get__singleton() {
//		$this->container->set( $name = 'service', function () {
//			$obj = new stdClass();
//			$obj->title = 'first title';
//			return $obj;
//		}, true );
//		self::assertObjectHasProperty( 'title', $this->container->get( $name ) );
//		self::assertSame( 'first title', $this->container->get( $name )->title );
//
//		$service = $this->container->get( $name );
//		$service->title = 'New title';
//
//		self::assertObjectHasProperty( 'changed title', $this->container->get( $name ) );
//	}

	public function test_get__autowiring_for_bind() {
		$obj1 = new SimpleClass();
		$this->container->set( $name = SimpleClass::class, SimpleClass::class );
		self::assertEquals( new SimpleClass(), $this->container->get( $name ) );

		$obj2 = new ClassWithConstructorDeps( $obj1 );
		$this->container->set( $name = ClassWithConstructorDeps::class, ClassWithConstructorDeps::class );
		self::assertEquals( $obj2, $this->container->get( $name ) );

		$obj3 = new ClassWithConstructorDeps2( $obj2 );
		$this->container->set( $name = ClassWithConstructorDeps2::class, ClassWithConstructorDeps2::class );
		self::assertEquals( $obj3, $this->container->get( $name ) );
	}

	public function test_get__autowiring_for_bind_invocable() {
		$this->container->set( SimpleClass::class, SimpleClass::class );
		$this->container->set( $name = ClassInvocable::class, ClassInvocable::class );

		self::assertInstanceOf( $name, $this->container->get( $name ) );
		self::assertSame( 'Function is called from ClassInvocable', $this->container->get( $name )() );
	}

	public function test_get__autowiring_not_set_deps() {
		$obj1 = new SimpleClass();
		$obj2 = new ClassWithConstructorDeps( $obj1 );
		$obj3 = new ClassWithConstructorDeps2( $obj2 );

		$this->container->set( $name = ClassWithConstructorDeps2::class, ClassWithConstructorDeps2::class );
		self::assertEquals( $obj3, $this->container->get( $name ) );

		$this->container->set( SplQueue::class , SplQueue::class );
		self::assertInstanceOf( SplQueue::class, $this->container->get( SplQueue::class ) );
	}

	public function test_get__autowiring_for_not_set_class() {
		$obj1 = new SimpleClass();
		$obj2 = new ClassWithConstructorDeps( $obj1 );
		$obj3 = new ClassWithConstructorDeps2( $obj2 );

		self::assertEquals( $obj3, $this->container->get(  ClassWithConstructorDeps2::class ) );
	}

//	public function test_get__bind_autowiring_container_not_found_exception_string() {
//		self::expectException( ServiceContainerNotFoundException::class );
//
//		$this->container->get( 'not-exist-service' );
//	}

//	public function test_get__bind_autowiring_container_not_found_exception_class() {
//		self::expectException( ServiceContainerNotFoundException::class );
//
//		$this->container->get( \stdClass::class );
//	}

	public function test_get__autowiring__container_exception() {
		self::expectException( ContainerException::class );

		$this->container->set( SimpleClass::class, SimpleClass::class );
		$this->container->set( ClassWithConstructorDepsException::class, ClassWithConstructorDepsException::class );
		$this->container->get( ClassWithConstructorDepsException::class );
	}

	public function test_has() {
		$this->container->set( $name = 'service', new stdClass() );

		self::assertTrue( $this->container->has( $name ) );
		self::assertFalse( $this->container->has( 'not-exist' ) );
	}

// TODO:: do we need it?
//	public function test_get__static_method_from_array() {
//		$this->container->bind( $name = 'service', [ StaticClass::class, 'get_string' ] );
//		self::assertSame( StaticClass::get_string(), $this->container->get( $name ) );
//	}
}
