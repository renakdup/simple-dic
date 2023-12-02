<?php
/**
 * Simple PHP DIC - DI Container in one file.
 * Supports autowiring and allows you to easily use it in your simple PHP applications and
 * especially convenient for WordPress plugins and themes.
 *
 * Author: Andrei Pisarevskii
 * Author Email: renakdup@gmail.com
 * Author Site: https://wp-yoda.com/en/
 *
 * Version: 0.2.1
 * Source Code: https://github.com/renakdup/simple-php-dic
 *
 * Licence: MIT License
 */

declare( strict_types=1 );

namespace Pisarevskii\SimpleDIC;

use Closure;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;

use function array_key_exists;
use function class_exists;
use function is_string;

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
	 *
	 * @throws ContainerExceptionInterface Error while retrieving the entry.
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
	/**
	 * @var mixed[]
	 */
	protected array $services = [];

	/**
	 * @var mixed[]
	 */
	protected array $resolved = [];

	/**
	 * @param mixed $service
	 */
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

	/**
	 * @inheritdoc
	 */
	public function has( string $id ): bool {
		return array_key_exists( $id, $this->services );
	}

	/**
	 * @param string $id
	 *
	 * @return mixed
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	protected function resolve( string $id ) {
		if ( $this->has( $id ) ) {
			$service = $this->services[ $id ];

			if ( $service instanceof Closure ) {
				return $service( $this );
			} elseif ( is_string( $service ) && class_exists( $service ) ) {
				return $this->resolve_object( $service );
			}

			return $service;
		}

		if ( class_exists( $id ) ) {
			return $this->resolve_object( $id );
		}

		$message = "Service '{$id}' not found in the Container.\n"
				   . "Stack trace: \n"
				   . $this->get_stack_trace();
		throw new ContainerNotFoundException( $message );
	}

	/**
	 * @param class-string $service
	 *
	 * @return object
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	protected function resolve_object( string $service ): object {
		try {
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

				$default_value = $param->getDefaultValue();
				if ( ! $default_value && $default_value !== null ) {
					$message = 'Service "' . $reflected_class->getName() . '" could not be resolved,' .
							   'because parameter of constructor "' . $param->getName() . '" has not default value.' . "\n" .
							   "Stack trace: \n" .
							   $this->get_stack_trace();
					throw new ContainerException( $message );
				}

				$constructor_args[] = $default_value;
			}
		} catch ( ReflectionException $e ) {
			throw new ContainerException(
				"Service '{$service}' could not be resolved due the reflection issue:\n '" .
				$e->getMessage() . "'\n" .
				"Stack trace: \n" .
				$e->getTraceAsString()
			);
		}

		return new $service( ...$constructor_args );
	}

	protected function get_stack_trace(): string {
		$stackTraceArray  = debug_backtrace();
		$stackTraceString = '';

		foreach ( $stackTraceArray as $item ) {
			$file     = $item['file'] ?? '[internal function]';
			$line     = $item['line'] ?? '';
			$function = $item['function'] ?? ''; // @phpstan-ignore-line
			$class    = $item['class'] ?? '';
			$type     = $item['type'] ?? '';

			$stackTraceString .= "{$file}({$line}): ";
			if ( ! empty( $class ) ) {
				$stackTraceString .= "{$class}{$type}";
			}
			$stackTraceString .= "{$function}()\n";
		}

		return $stackTraceString;
	}
}

class ContainerNotFoundException extends InvalidArgumentException implements NotFoundExceptionInterface {}

class ContainerException extends InvalidArgumentException implements ContainerExceptionInterface {}

