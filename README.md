# Simple DI Container for WordPress
Simple DI Container with auto-wiring in a single file allows you to easily use it in your WordPress plugins and themes. 

## Why choose Simple DI Container?
1. Easy to integrate into your WordPress project, just copy one file.
2. Simple DI Container hasn't any dependencies on other scripts or libraries.
3. Supports auto-wiring `__constructor` parameters for classes as well as for scalar types that have default values.
4. Allow you following the best practices for developing your code.
5. Supports PSR11 (read more about below).

## How to integrate it in a project?
1. Just copy the file `./src/Container.php` to your plugin directory or theme.
2. Rename `namespace` in the file from `Pisarevskii\SimpleDIC` to `<Your_Plugin_Name>\SimpleDIC`
3. Require this file.

## How to use it in code

### Get started:

1. Create a container
2. Set a service
3. Get a service
4. Use object
```php
use Pisarevskii\SimpleDIC\Container;

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
---

### Factory
Factory is an `anonymous function` that wrap creating an instance.  
Allows to configure how an object will be created and allows to use `Conainer` instance inside the factory.

```php
$container->set( Paypal::class, function () {
    return new Paypal();
} );
```

One of the main benefits as well is that the factory allows to creation of objects by Lazy Load. It means that object to be created just when you call `$constructor->get( Paypal::class )`.

[//]: # (> [!NOTE]  )

[//]: # (> If you get the same service from the Container several times, you will get the same object, because the object is created just 1 time, and then stored in storage.)

[//]: # (> ```php)

[//]: # (> $obj1 = $constructor->get&#40; Paypal::class &#41;;)

[//]: # (> $obj2 = $constructor->get&#40; Paypal::class &#41;;)

[//]: # (> var_dump&#40; $obj1 === $obj2 &#41; // true)

[//]: # (> ```)


---

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


## PSR11 Compatibility
This Simple DI Container compatible with PSR11 standards ver 2.0, to use it:
1. Just import PSR11 interfaces in `Container.php`
```php
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
```
2. Remove PSR11 interfaces from the `Container.php` file:
```php
######## PSR11 2.0 interfaces #########

#     ..... PSR11 interfaces

######## PSR11 interfaces - END #########
```

> [!NOTE]  
> Some plugins use PSR11 interfaces and these files are stored inside these plugins as well as PSR interfaces have versions and are usually not compatible between major versions.  
> **Due that I highly recommend you keep these interfaces inside the file and use PSR11 interfaces under your Plugin/Theme namespace.**


## More examples
```php
$container->set('config.requests-limit', 100);
$container->set('config.', ['one', 'two, 'three']);
```

## Roadmap
- [x] Add binding services with configuration
- [x] Add auto-wiring for registered classes in DIC
- [x] Add auto-wiring for defaults primitives for auto-fillings
- [x] Add supporting invocable class
- [x] Add PSR11 interfaces in the Container.php.
- [x] Add auto-wiring support for not bounded classes.
- [x] Add resolved service storage.
- [ ] Integrate CI with running autotests
- [ ] Add ability creating new instance of service every time
- [ ] Add supporting Code Driven IoC
- [ ] Add singleton getting for Container
- [ ] Add descriptions in the code for functions.
- [ ] Set definitions via constuctor
- [ ] Add `remove` method? 
- [ ] Add badges with tests passed
- [ ] PHP 8 named arguments and autowiring
- [ ] Save cache in opcache?
- [ ] Add configurations of Container.
- [ ] Choose codestyle
- [ ] Add on packegist
