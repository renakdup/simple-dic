<?php

declare( strict_types=1 );

namespace Pisarevskii\SimpleDIC;

use Closure;
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
			throw new \Exception( "Service '{$id}' not found." );
		}

		$service = $this->services[ $id ];

		// closure, callable arrays, function in string,
//		dump($id, $this->services[ $id ], is_callable($this->services[ $id ] ));
//		if ( is_callable($this->services[ $id ] ) ) {
//			return $this->services[ $id ]( $this );
//		}

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
				$param_class = $param->getType()->getName();
				if ( class_exists( $param_class ) ) {
					if ( $this->has( $param_class ) ) {
						$constructor_args[] = $this->get( $param_class );
					}
				}
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
