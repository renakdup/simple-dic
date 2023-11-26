<?php

declare( strict_types=1 );

namespace Pisarevskii\SimpleDIC;

use Closure;
use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;

class ServiceContainer implements ContainerInterface {

	protected array $services = [];

	public function bind( string $id, $service ): void {
		$this->services[ $id ] = $service;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get( string $id ) {
		if ( ! $this->has( $id ) ) {
			throw new ServiceContainerNotFoundException( "Service '{$id}' not found in the ServiceContainer." );
		}

		$service = $this->services[ $id ];

		if ( $service instanceof Closure ) {
			return $service( $this );
		}

		if ( is_string( $service ) && class_exists( $service ) ) {
			$reflected_class = new ReflectionClass( $service );
			$constructor     = $reflected_class->getConstructor();

			if ( ! $constructor ) {
				return new $service();
			}

			$params = $constructor->getParameters();

			if ( ! $params ) {
				return new $service();
			}

			$constructor_args = [];
			foreach ( $params as $param ) {
				if ( $param_class = $param->getClass() ) {
					$constructor_args[] = $this->get( $param_class->getName() );
					continue;
				}

				try {
					$default_value = $param->getDefaultValue();
					if ( ! $default_value && $default_value !== null ) {
						throw new ServiceContainerException( 'Service "' . $reflected_class->getName() . '" could not be resolved due constructor parameter "' . $param->getName() . '"' );
					}
				} catch ( \ReflectionException $e ) {
					throw new ServiceContainerException( 'Service "' . $reflected_class->getName() . '" could not be resolved due the Reflection issue: ' . $e->getMessage() );
				}

				$constructor_args[] = $default_value;
			}

			return new $service( ...$constructor_args );
		}

		return $service;
	}

	/**
	 * @inheritdoc
	 */
	public function has( string $id ): bool {
		return isset( $this->services[ $id ] );
	}
}

class ServiceContainerNotFoundException extends InvalidArgumentException implements NotFoundExceptionInterface {

}

class ServiceContainerException extends InvalidArgumentException implements ContainerExceptionInterface {

}
