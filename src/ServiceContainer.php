<?php

declare(strict_types=1);

namespace Pisarevskii\SimpleDIC;

use Closure;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class ServiceContainer implements ContainerInterface {

	protected array $services = [];

	public function set( string $id, $service ): void {
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

		if ( is_callable($this->services[ $id ] ) ) {
			return $this->services[ $id ]();
		}

//		if ( $service instanceof Closure ) {
//			return $service();
//		}

		if ( is_string($service) && class_exists( $service ) ) {
			return new $service();
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
