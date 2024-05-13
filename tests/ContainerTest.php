<?php

declare( strict_types=1 );

namespace RenakdupTests\SimpleDIC;

use Exception;
use PHPUnit\Framework\TestCase;
use Renakdup\SimpleDIC\Container;
use RenakdupTests\SimpleDIC\Assets\AbstractClass;
use RenakdupTests\SimpleDIC\Assets\ClassWithConstructorSupertypes;
use RenakdupTests\SimpleDIC\Assets\EmptyConstructor;
use RenakdupTests\SimpleDIC\Assets\InvocableClass;
use RenakdupTests\SimpleDIC\Assets\ClassWithConstructorPrimitives;
use RenakdupTests\SimpleDIC\Assets\ClassWithConstructor;
use RenakdupTests\SimpleDIC\Assets\ClassWithConstructorDepsException;
use RenakdupTests\SimpleDIC\Assets\ParentClass;
use RenakdupTests\SimpleDIC\Assets\PrivateConstructor;
use RenakdupTests\SimpleDIC\Assets\SomeInterface;
use RenakdupTests\SimpleDIC\Assets\SimpleClass;
use RenakdupTests\SimpleDIC\Assets\UseAbstractClass;
use RenakdupTests\SimpleDIC\Assets\UseInterfaceClass;
use SplQueue;
use stdClass;
use Error;

final class ContainerTest extends TestCase {
	private Container $container;

	protected function setUp(): void {
		$this->container = new Container();
	}

	protected function tearDown(): void {
		unset( $this->container );
	}

	public function test_get__primitives(): void {
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

	public function test_get__callback(): void {
		$this->container->set( $name = 'service', function () {
			return new stdClass();
		} );
		self::assertEquals( new stdClass(), $this->container->get( $name ) );
	}

	public function test_get__pass_container_parameter(): void {
		$this->container->set( 'id', $value1 = 100 );
		$this->container->set( 'title', $value2 = 'Title of article' );
		$this->container->set( $service = 'service', function ( Container $c ) {
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

	public function test_get__object(): void {
		$this->container->set( $name = 'service', $value = new stdClass() );
		self::assertSame( $value, $this->container->get( $name ) );
	}

	public function test_get__object_from_class(): void {
		$this->container->set( $name = 'service', SimpleClass::class );
		self::assertEquals( new SimpleClass(), $this->container->get( $name ) );

		$this->container->set( $name2 = 'service2', 'RenakdupTests\SimpleDIC\Assets\SimpleClass' );
		self::assertEquals( new SimpleClass(), $this->container->get( $name2 ) );
	}

	public function test_get__no_args_of_constructor(): void {
		$this->container->set( $name = 'service', EmptyConstructor::class );
		self::assertEquals( new EmptyConstructor(), $this->container->get( $name ) );
	}

	public function test_get__create_not_bound_service(): void {
		self::assertEquals( new SimpleClass(), $this->container->get( SimpleClass::class ) );
	}

	public function test_get__singleton_check_for_not_bounded(): void {
		self::assertSame(
			$this->container->get( SimpleClass::class ),
			$this->container->get( SimpleClass::class )
		);
	}

	public function test_get__singleton_check_changing_property(): void {
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

	public function test_get__singleton_for_resolved_child_dependencies(): void {
		/**
		 * @var $obj1 ClassWithConstructor
		 */
		$obj1 = $this->container->get( ClassWithConstructor::class );

		self::assertSame(
			$obj1->obj_with_constructor_deps->simple_class,
			$this->container->get( SimpleClass::class )
		);
	}

	public function test_get__singleton_autoregister_container(): void {
		$obj = $this->container->get( Container::class );
		$obj2 = $this->container->get( Container::class );

		self::assertSame( $this->container, $obj );
		self::assertSame( $this->container, $obj2 );
	}

	public function test_get__autowiring(): void {
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

	public function test_get__autowiring_for_invocable(): void {
		$this->container->set( SimpleClass::class, SimpleClass::class );
		$this->container->set( $name = InvocableClass::class, InvocableClass::class );

		self::assertInstanceOf( $name, $this->container->get( $name ) );
		self::assertSame( 'Function is called from ClassInvocable', $this->container->get( $name )() );
	}

	public function test_get__autowiring_for_not_bound_invocable(): void {
		self::assertInstanceOf( InvocableClass::class, $this->container->get( InvocableClass::class ) );
		self::assertSame( 'Function is called from ClassInvocable', $this->container->get( InvocableClass::class )() );
	}

	public function test_get__autowiring_not_bound_deps(): void {
		$obj = new ClassWithConstructor( new ClassWithConstructorPrimitives( new SimpleClass() ) );

		$this->container->set( $name = ClassWithConstructor::class, ClassWithConstructor::class );
		self::assertEquals( $obj, $this->container->get( $name ) );

		$this->container->set( SplQueue::class , SplQueue::class );
		self::assertInstanceOf( SplQueue::class, $this->container->get( SplQueue::class ) );
	}

	public function test_get__autowiring_for_not_bound_class(): void {
		$obj1 = new SimpleClass();
		$obj2 = new ClassWithConstructorPrimitives( $obj1 );
		$obj3 = new ClassWithConstructor( $obj2 );

		self::assertEquals( $obj3, $this->container->get(  ClassWithConstructor::class ) );
	}

	public function test_get__exception_not_found(): void {
		self::expectException( Exception::class );

		$this->container->get( 'not-exist-service' );
	}

	public function test_get__autowiring__container_exception(): void {
		self::expectException( Exception::class );

		$this->container->get( ClassWithConstructorDepsException::class );
	}

	public function test_get__error_for_not_bound_supertypes(): void {
		self::expectException( Error::class);
		$this->container->get( ClassWithConstructorSupertypes::class );
	}

	public function test_make(): void {
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

	public function test_make__exception(): void {
		self::expectException( Exception::class );

		$this->container->make( 'this-string-is-not-class' );
	}

	/**
	 * This Exception should never have been reached if the code works well.
	 * We should keep try-catch `ReflectionException` in the code, but the test is needed just to fit 100% of coverage.
	 */
	public function test_get__reflection_exception(): void {
		self::expectException( Exception::class );

		$fn = ( function () {
			$this->resolve_class( 'NotExistingClass' );
		} )->call( $this->container );
		$fn();
	}

	public function test_has(): void {
		$this->container->set( $name = 'service', new stdClass() );

		self::assertTrue( $this->container->has( $name ) );
		self::assertFalse( $this->container->has( 'not-exist' ) );
	}
}
