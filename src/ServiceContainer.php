<?php

declare(strict_types=1);

namespace Pisarevskii\SimpleDIC;

use Closure;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

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

		// closure,

//		dump($id, $this->services[ $id ], is_callable($this->services[ $id ] ));
//		if ( is_callable($this->services[ $id ] ) ) {
//			return $this->services[ $id ]( $this );
//		}

		if ( $service instanceof Closure ) {
			return $service( $this );
		}

		// passed class name
		if ( is_string($service) && class_exists( $service ) ) {
			return new $service( $this );
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
