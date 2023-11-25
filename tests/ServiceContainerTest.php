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
	public function test_get_primitives() {
		$container = new ServiceContainer();

		$container->set( $name = 'service', $value = 1 );
		self::assertSame( $value, $container->get( $name ) );

		$container->set( $name = 'service', $value = '5' );
		self::assertSame( $value, $container->get( $name ) );

		$container->set( $name = 'service', $value = 'string' );
		self::assertSame( $value, $container->get( $name ) );

		$container->set( $name = 'service', $value = [ 'array' ] );
		self::assertSame( $value, $container->get( $name ) );

		$container->set( $name = 'service', $value = new stdClass() );
		self::assertSame( $value, $container->get( $name ) );
	}

	public function test_get_callbacks() {
		$container = new ServiceContainer();

		$container->set( $name = 'service', $value = function () {
			return new stdClass();
		} );
		self::assertInstanceOf( stdClass::class, $container->get( $name ) );
	}

//	public function test_get_static_method_from_array() {
//		$container = new ServiceContainer();
//
//		$container->set( $name = 'service', [ StaticClass::class, 'get_string' ] );
//		self::assertSame( StaticClass::get_string(), $container->get( $name ) );
//	}
//
//	public function test_get_static_method_from_array2() {
//		$container = new ServiceContainer();
//
//		$container->set( $name = 'service', [ StaticClass::class, 'get_string' ] );
//		self::assertSame( StaticClass::get_string(), $container->get( $name ) );
//	}

	public function test_get_object_from_string() {
		$container = new ServiceContainer();

		$container->set( $name = 'service', 'PisarevskiiTests\SimpleDIC\Assets\SimpleClass' );
		self::assertInstanceOf( SimpleClass::class, $container->get( $name ) );
	}

	public function test_has() {
		$container = new ServiceContainer();
		$container->set( $name = 'service', $target_object = new stdClass() );

		self::assertTrue( $container->has( $name ) );
		self::assertFalse( $container->has( 'not-exist' ) );
	}
}
