# Simple DIC - PHP DI Container in one file for your WordPress.
[![Software License][ico-license]](LICENSE)
[![UnitTests](https://github.com/renakdup/simple-wordpress-dic/actions/workflows/phpunit.yaml/badge.svg)](https://github.com/renakdup/simple-wordpress-dic/actions/workflows/phpunit.yaml)
[![PHPStan](https://github.com/renakdup/simple-wordpress-dic/actions/workflows/phpstan.yaml/badge.svg)](https://github.com/renakdup/simple-wordpress-dic/actions/workflows/phpstan.yaml)



Simple DI Container with **autowiring** in a single file **without dependencies** allows you to easily use it in your PHP applications and especially convenient for **WordPress** plugins and themes. 

## Why choose Simple DI Container?
1. Easy to integrate into your PHP Application or WordPress project, just copy one file.
2. Simple PHP DI Container hasn't any dependencies on other scripts or libraries.
3. Supports auto-wiring `__constructor` parameters for classes as well as for scalar types that have default values.
4. Supports PHP ^8 and PHP 7.4.
5. Supports Lazy Load class instantiating.
6. Allow you following the best practices for developing your code.
7. Supports PSR11 (read more about below).
8. No phpcs conflicts.

## How to integrate it in a project?

1. Just copy the file `./src/Container.php` to your plugin directory or theme.
2. Rename `namespace` in the file from `Renakdup\SimpleDIC` to `<Your_Plugin_Name>\SimpleDIC`
3. Require this file.

## How to use it in code

### Get started:

1. Add file to your application and `include`.
2. Create a container.
3. Set a service
4. Get a service
5. Use object
```php
use Renakdup\SimpleDIC\Container;

// create container
$container = new Container();

// set service
$container->set( Paypal::class, function () {
    return new Paypal();
} );

// get service
$paypal = $container->get( Paypal::class );

// use this object
$paypal->pay();
```

SimpleDIC allows to set values for the container for primitive types:
```php
$container->set( 'requests_limit', 100 );
$container->set( 'post_type', 'products' );
$container->set( 'users_ids', [ 1, 2, 3, 4] );

$user_ids = $container->get( 'users_ids', [ 1, 2, 3, 4] );
```

Method `get()` can resolve not set object in the `$container` and then save resolved results in the `$container`. It means when you run `$container->get( $service )` several times you get the same object.

```php
$obj1 = $constructor->get( Paypal::class );
$obj2 = $constructor->get( Paypal::class );
var_dump( $obj1 === $obj2 ) // true
```

If you want to instantiate service several time use `make()` method. 

---

### Factory
Factory is an `anonymous function` that wrap creating an instance.  
Allows to configure how an object will be created and allows to use `Conainer` instance inside the factory.

```php
$container->set( Paypal::class, function () {
    return new Paypal();
} );
```

As well factories create objects in the Lazy Load mode. It means that object will be created just when you resolve it by using `get()` method:

```php
$container->set( Paypal::class, function () {
    return new Paypal();
} );

$paypal = $constructor->get( Paypal::class ); // PayPal instance created
```

---

### Container inside factory
**SimpleDIC** allows to get a `Container` instance inside a factory if you add parameter in a callback `( Container $c )`. This allows to get or resolve another services inside for building an object:
```php
$container->set( 'config', [
    'currency' => '$',
    'environment' => 'production',
] );

$container->set( Paypal::class, function ( Container $c ) {
    return new Paypal( $c->get('config') );
} );
```

---

### Autowiring
**SimpleDIС** autowiring feature **allows to `Container` automatically create and inject dependencies**.

I'll show an example:
```php
class PayPalSDK {}
class Logger {}

class Paypal {
    public function __constructor( PayPalSDK $pal_sdk, Logger $logger ) {
        //...
    }
}
```
And then when you create `Paypal::class`, you run `$container->get(Paypal::class)`, and `Container` identifies all classes in the constructor and resolves them. As if it's:
```php
new Paypal( new PayPalSDK(), new Logger() );
```
---
Container autowiring can resolve default values for *primitive* parameters in a constructor:
```php
class Logger {
    public function __constructor( $type = 'filestorage', $error_lvl = 1 ) {
        //...
    }
}
```

You can use **auto-wiring** feature that allows to `Container` create an instances that requires in the `__constructor` of class as well as it resolves constructor dependencies for 


> [!NOTE]
> But if object creating is more complex and requires configuring and you don't have parameters with default values in the constructor then you need to use `factory` for preparing service.

---


### Create an instance every time

Method `make()` resolves services by its name. It returns a new instance of service every time and supports auto-wiring.

```php
$conatainer->make( Paypal::class );
```

> [!NOTE]  
> Constructor's dependencies will not instantiate every time.  
> If dependencies were resolved before then they will be passed as resolved dependencies.

Consider example:
```php
class PayPalSDK {}

class Paypal {
    public PayPalSDK $pal_sdk;
    public function __constructor( PayPalSDK $pal_sdk ) {
        $this->pal_sdk = $pal_sdk;
    }
}

// if we create PayPal instances twice
$paypal1 = $container->make( Paypal::class );
$paypal2 = $container->make( Paypal::class );

var_dump( $paypal1 !== $paypal2 ); // true
var_dump( $paypal1->pal_sdk === $paypal2->pal_sdk ); // true
```
Dependencies of PayPal service will not be recreated and will be taken from already resolved objects.

---

## PSR11 Compatibility
in progress

## Roadmap
- [x] Add binding services with configuration
- [x] Add auto-wiring for registered classes in DIC
- [x] Add auto-wiring for defaults primitives for auto-fillings
- [x] Add supporting invocable classes
- [x] Add PSR11 interfaces in the Container.php.
- [x] Add auto-wiring support for not bounded classes.
- [x] Add resolved service storage (getting singleton).
- [x] Add ability to create new instances of service every time.
- [x] Improve performance.
- [x] Fix deprecated `Use ReflectionParameter::getType() and the ReflectionType APIs should be used instead.` for PHP8
- [x] Bind $container instance by default.
- [ ] Add `remove` method.
- [ ] Circular dependency protector.

## Nice to have
- [x] Integrate CI with running autotests
- [x] Add badges with tests passed
- [x] Add PHPStan analyzer
- [ ] Add code coverage badge
- [ ] Add descriptions in the code for functions.
- [ ] Choose codestyle
- [ ] Add on packegist
- [ ] Add if class exist checks in the Container file?
- [ ] Rename Container.php to SimpleContainer.php
- [ ] Show stack trace when I have a debug only?
- [ ] PHP 8 named arguments and autowiring.
- [ ] Allow to set definitions via `__constructor`.
- [ ] Add supporting Code Driven IoC.
- [ ] Add configurations of Container.
- [ ] Add Performance Benchmarks

## License

The MIT License (MIT). Please see the [License File](LICENSE) for more information.

[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg
