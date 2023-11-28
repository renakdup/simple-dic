<?php
/**
 * Simple PHP DI Container (DIC) for WordPress with auto-wiring allows
 * you easily use it in your plugins and themes.
 *
 * Author: Andrei Pisarevskii
 * Author Email: renakdup@gmail.com
 * Author Site: https://wp-yoda.com/en/
 *
 * Version: 0.2
 * Source Code: https://github.com/renakdup/simple-wordpress-dic
 *
 * Licence: MIT License
 */

declare( strict_types=1 );

namespace Pisarevskii\SimpleDIC;

use Closure;
use InvalidArgumentException;
use ReflectionClass;

######## PSR11 2.0 interfaces #########
# If you want to support PSR11, then remove 3 interfaces below
# (ContainerInterface, ContainerExceptionInterface, NotFoundExceptionInterface)
# and import PSR11 interfaces in this file:
# -----
# use Psr\Container\ContainerExceptionInterface;
# use Psr\Container\ContainerInterface;
# use Psr\Container\NotFoundExceptionInterface;
###############################
interface ContainerInterface {
	/**
	 * Finds an entry of the container by its identifier and returns it.
	 *
	 * @param string $id Identifier of the entry to look for.
	 *
	 * @return mixed Entry.
	 * @throws ContainerExceptionInterface Error while retrieving the entry.
	 *
	 * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
	 */
	public function get( string $id );

	/**
	 * Returns true if the container can return an entry for the given identifier.
	 * Returns false otherwise.
	 *
	 * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
	 * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
	 *
	 * @param string $id Identifier of the entry to look for.
	 *
	 * @return bool
	 */
	public function has( string $id ): bool;
}

/**
 * Base interface representing a generic exception in a container.
 */
interface ContainerExceptionInterface extends \Throwable {}

/**
 * No entry was found in the container.
 */
interface NotFoundExceptionInterface extends ContainerExceptionInterface {}
######## PSR11 interfaces - END #########


###############################
#     Simple DIC code
###############################
class Container implements ContainerInterface {
	protected array $services = [];

	protected array $resolved = [];

	public function set( string $id, $service ): void {
		$this->services[ $id ] = $service;
		unset( $this->resolved[ $id ] );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get( string $id ) {
		if ( isset( $this->resolved[ $id ] ) || array_key_exists( $id, $this->resolved ) ) {
			return $this->resolved[ $id ];
		}

		$service = $this->resolve( $id );

		$this->resolved[ $id ] = $service;

		return $service;
	}

	protected function resolve( $id ) {
		if ( $this->has( $id ) ) {
			$service = $this->services[ $id ];

			if ( $service instanceof Closure ) {
				return $service( $this );
			} elseif ( is_string( $service ) && class_exists( $service ) ) {
				return $this->resolve_object( $service );
			} elseif ( is_scalar( $service ) || is_array( $service ) || $service === null ) {
				return $service;
			} elseif ( is_object( $service ) ) {
				return $service;
			}

			throw new ContainerNotFoundException( "Service '{$service}' not found in the Container." );
		} else {
			if ( is_string( $id ) && class_exists( $id ) ) {
				return $this->resolve_object( $id );
			}

			throw new ContainerNotFoundException( "Service '{$id}' not found in the Container." );
		}
	}

	protected function resolve_object( $service ) {
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
				if ( $this->has( $param_class->getName() ) ) {
					$constructor_args[] = $this->get( $param_class->getName() );
					continue;
				}

				$constructor_args[] = $this->get( $param_class->getName() );
				continue;
			}

			try {
				$default_value = $param->getDefaultValue();
				if ( ! $default_value && $default_value !== null ) {
					throw new ContainerException( 'Service "' . $reflected_class->getName() . '" could not be resolved due constructor parameter "' . $param->getName() . '"' );
				}
			} catch ( \ReflectionException $e ) {
				throw new ContainerException( 'Service "' . $reflected_class->getName() . '" could not be resolved because parameter of constructor "' . $param . '" has the Reflection issue while resolving: ' . $e->getMessage() );
			}

			$constructor_args[] = $default_value;
		}

		return new $service( ...$constructor_args );
	}

	/**
	 * @inheritdoc
	 */
	public function has( string $id ): bool {
		return array_key_exists( $id, $this->services );
	}
}

class ContainerNotFoundException extends InvalidArgumentException implements NotFoundExceptionInterface {}

class ContainerException extends InvalidArgumentException implements ContainerExceptionInterface {}

