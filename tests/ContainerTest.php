<?php

declare( strict_types=1 );

namespace PisarevskiiTests\SimpleDIC;

use PHPUnit\Framework\TestCase;
use Pisarevskii\SimpleDIC\Container;
use Pisarevskii\SimpleDIC\ContainerException;
use Pisarevskii\SimpleDIC\ContainerInterface;
use Pisarevskii\SimpleDIC\ContainerNotFoundException;
use PisarevskiiTests\SimpleDIC\Assets\AbstractClass;
use PisarevskiiTests\SimpleDIC\Assets\ClassWithConstructorSupertypes;
use PisarevskiiTests\SimpleDIC\Assets\InvocableClass;
use PisarevskiiTests\SimpleDIC\Assets\ClassWithConstructorPrimitives;
use PisarevskiiTests\SimpleDIC\Assets\ClassWithConstructor;
use PisarevskiiTests\SimpleDIC\Assets\ClassWithConstructorDepsException;
use PisarevskiiTests\SimpleDIC\Assets\ParentClass;
use PisarevskiiTests\SimpleDIC\Assets\SomeInterface;
use PisarevskiiTests\SimpleDIC\Assets\SimpleClass;
use PisarevskiiTests\SimpleDIC\Assets\UseAbstractClass;
use PisarevskiiTests\SimpleDIC\Assets\UseInterfaceClass;
use SplQueue;
use stdClass;
use Error;

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

	public function test_get__callback() {
		$this->container->set( $name = 'service', function () {
			return new stdClass();
		} );
		self::assertEquals( new stdClass(), $this->container->get( $name ) );
	}

	public function test_get__pass_container_parameter() {
		$this->container->set( 'id', $value1 = 100 );
		$this->container->set( 'title', $value2 = 'Title of article' );
		$this->container->set( $service = 'service', function ( ContainerInterface $c ) {
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

	public function test_get__object() {
		$this->container->set( $name = 'service', $value = new stdClass() );
		self::assertSame( $value, $this->container->get( $name ) );
	}

	public function test_get__object_from_class() {
		$this->container->set( $name = 'service', SimpleClass::class );
		self::assertEquals( new SimpleClass(), $this->container->get( $name ) );

		$this->container->set( $name2 = 'service2', 'PisarevskiiTests\SimpleDIC\Assets\SimpleClass' );
		self::assertEquals( new SimpleClass(), $this->container->get( $name2 ) );
	}

	public function test_get__create_not_bound_service() {
		self::assertEquals( new SimpleClass(), $this->container->get( SimpleClass::class ) );
	}

	public function test_get__check_singleton_for_not_bounded() {
		self::assertSame(
			$this->container->get( SimpleClass::class ),
			$this->container->get( SimpleClass::class )
		);
	}

	public function test_get__check_changing_singleton_property() {
		$this->container->set( $name = 'service', function () {
			$obj        = new stdClass();
			$obj->title = 'first title';

			return $obj;
		} );

		$service        = $this->container->get( $name );
		$service->title = 'changed title';

		self::assertObjectHasProperty( 'title', $this->container->get( $name ) );
		self::assertSame( 'changed title', $this->container->get( $name )->title );
	}

	public function test_get__singleton_for_resolved_child_dependencies() {
		/**
		 * @var $obj1 ClassWithConstructor
		 */
		$obj1 = $this->container->get( ClassWithConstructor::class );

		self::assertSame(
			$obj1->obj_with_constructor_deps->simple_class,
			$this->container->get( SimpleClass::class )
		);
	}

	public function test_get__autowiring() {
		$expected = new SimpleClass();
		$this->container->set( $name = SimpleClass::class, SimpleClass::class );
		self::assertEquals( new SimpleClass(), $this->container->get( $name ) );

		$expected = new ClassWithConstructorPrimitives( $expected );
		$this->container->set( $name = ClassWithConstructorPrimitives::class, ClassWithConstructorPrimitives::class );
		self::assertEquals( $expected, $this->container->get( $name ) );

		$expected = new ClassWithConstructor( $expected );
		$this->container->set( $name = ClassWithConstructor::class, ClassWithConstructor::class );
		self::assertEquals( $expected, $this->container->get( $name ) );

		$expected = new ClassWithConstructorSupertypes( new ParentClass(), new UseAbstractClass(), new UseInterfaceClass() );
		$this->container->set( AbstractClass::class, UseAbstractClass::class );
		$this->container->set( SomeInterface::class, UseInterfaceClass::class );
		self::assertEquals( $expected, $this->container->get( ClassWithConstructorSupertypes::class ) );
	}

	public function test_get__autowiring_for_invocable() {
		$this->container->set( SimpleClass::class, SimpleClass::class );
		$this->container->set( $name = InvocableClass::class, InvocableClass::class );

		self::assertInstanceOf( $name, $this->container->get( $name ) );
		self::assertSame( 'Function is called from ClassInvocable', $this->container->get( $name )() );
	}

	public function test_get__autowiring_for_not_bound_invocable() {
		self::assertInstanceOf( InvocableClass::class, $this->container->get( InvocableClass::class ) );
		self::assertSame( 'Function is called from ClassInvocable', $this->container->get( InvocableClass::class )() );
	}

	public function test_get__autowiring_not_bound_deps() {
		$obj = new ClassWithConstructor( new ClassWithConstructorPrimitives( new SimpleClass() ) );

		$this->container->set( $name = ClassWithConstructor::class, ClassWithConstructor::class );
		self::assertEquals( $obj, $this->container->get( $name ) );

		$this->container->set( SplQueue::class , SplQueue::class );
		self::assertInstanceOf( SplQueue::class, $this->container->get( SplQueue::class ) );
	}

	public function test_get__autowiring_for_not_bound_class() {
		$obj1 = new SimpleClass();
		$obj2 = new ClassWithConstructorPrimitives( $obj1 );
		$obj3 = new ClassWithConstructor( $obj2 );

		self::assertEquals( $obj3, $this->container->get(  ClassWithConstructor::class ) );
	}

	public function test_get__exception_not_found() {
		self::expectException( ContainerNotFoundException::class );

		$this->container->get( 'not-exist-service' );
	}

	public function test_get__autowiring__container_exception() {
		self::expectException( ContainerException::class );

		$this->container->get( ClassWithConstructorDepsException::class );
	}

	public function test_get__error_for_not_bound_supertypes() {
		self::expectException( Error::class);
		$this->container->get( ClassWithConstructorSupertypes::class );
	}

	public function test_make() {
		/**
		 * @var $obj1 ClassWithConstructorPrimitives
		 * @var $obj2 ClassWithConstructorPrimitives
		 */
		$obj1 = $this->container->make( ClassWithConstructorPrimitives::class );
		$obj2 = $this->container->make( ClassWithConstructorPrimitives::class );

		self::assertNotSame( $obj1, $obj2 );
		self::assertEquals( $obj1, $obj2 );

		self::assertSame( $obj1->simple_class, $obj1->simple_class );
		self::assertSame( $obj1->array, $obj1->array );
		self::assertSame( $obj1->string, $obj1->string );
	}

	public function test_make__exception() {
		self::expectException( ContainerException::class );

		$this->container->make( 'this-string-is-not-class' );
	}

	public function test_has() {
		$this->container->set( $name = 'service', new stdClass() );

		self::assertTrue( $this->container->has( $name ) );
		self::assertFalse( $this->container->has( 'not-exist' ) );
	}
}
