<?php

declare(strict_types=1);

namespace PisarevskiiTests\SimpleDIC;

use PHPUnit\Framework\TestCase;
use Pisarevskii\SimpleDIC\ServiceContainer;
use PisarevskiiTests\SimpleDIC\Assets\StaticClass;
use PisarevskiiTests\SimpleDIC\Assets\SimpleClass;
use stdClass;

class ServiceContainerTest extends TestCase {

	/**
	 * @return void
	 * @throws \Psr\Container\ContainerExceptionInterface
	 * @throws \Psr\Container\NotFoundExceptionInterface
	 */
	public function test_get__primitives() {
		$container = new ServiceContainer();

		$container->bind( $name = 'service', $value = 1 );
		self::assertSame( $value, $container->get( $name ) );

		$container->bind( $name = 'service', $value = '5' );
		self::assertSame( $value, $container->get( $name ) );

		$container->bind( $name = 'service', $value = 'string' );
		self::assertSame( $value, $container->get( $name ) );

		$container->bind( $name = 'service', $value = [ 'array' ] );
		self::assertSame( $value, $container->get( $name ) );
	}

	public function test_get__object() {
		$container = new ServiceContainer();
		$container->bind( $name = 'service', $value = new stdClass() );

		self::assertSame( $value, $container->get( $name ) );
	}

	public function test_get__callbacks() {
		$container = new ServiceContainer();

		$container->bind( $name = 'service', $value = function () {
			return new stdClass();
		} );
		self::assertEquals( new stdClass(), $container->get( $name ) );
	}

	public function test_get__callback_with_param() {
		$container = new ServiceContainer();

		$container->bind( $name_title = 'title', $value_title = 'Title of article' );
		$container->bind( $name_service = 'service', function ($c) use ($name_title) {
			$obj = new stdClass();
			$obj->title = $c->get($name_title);
			return $obj;
		} );

		$obj = new stdClass();
		$obj->title = $value_title;

		self::assertEquals( $obj, $container->get( $name_service ) );
	}

	public function test_get__object_from_class() {
		$container = new ServiceContainer();

		$container->bind( $name = 'service', SimpleClass::class );
		self::assertEquals( new SimpleClass(), $container->get( $name ) );

		$container->bind( $name2 = 'service2', 'PisarevskiiTests\SimpleDIC\Assets\SimpleClass' );
		self::assertEquals( new SimpleClass(), $container->get( $name2 ) );
	}

	// TODO:: do we need it?
//	public function test_get__static_method_from_array() {
//		$container = new ServiceContainer();
//
//		$container->bind( $name = 'service', [ StaticClass::class, 'get_string' ] );
//		self::assertSame( StaticClass::get_string(), $container->get( $name ) );
//	}

	public function test_has() {
		$container = new ServiceContainer();
		$container->bind( $name = 'service', new stdClass() );

		self::assertTrue( $container->has( $name ) );
		self::assertFalse( $container->has( 'not-exist' ) );
	}
}
