# Simple DI Container for WordPress
Simple DI Container with auto-wiring in a single file allows you to easily use it in your WordPress plugins and themes. 

## Why choose Simple DI Container?
1. Easy to integrate into your WordPress project, just copy one file.
2. Simple DI Container hasn't any dependencies on other scripts or libraries.
3. Supports auto-wiring `__constructor` parameters for classes as well as for scalar types that have default values.
4. Allow you following the best practices for developing your code.
5. PSR11 support can be activated (read more about below).

## How to integrate it in a project?
1. Just copy the file `./src/ServiceContainer.php` to your plugin directory or theme.
2. Rename `namespace` in the file from `Pisarevskii\SimpleDIC` to `<Your_Plugin_Name>\SimpleDIC`
3. Require this file.

## How to use it in code
Simple example:
```
use Pisarevskii\SimpleDIC\ServiceContainer;

// create the container
$container = new ServiceContainer();

// set service
$container->set(Paypal::class, function () {
    return new Paypal();
} );

// get service
$paypal = $container->get(Paypal::class);

// use this object
$paypal->pay();
```

If you want, you can use container inside a factory
```
$container->set('config', [
    'currency' => '$',
    'environment' => 'production',
]);

$container->set(Paypal::class, function (ServiceContainer $c) {
    return new Paypal($c->get('config'));
} );
```


## PSR11 Compatibility
This Simple DI Container compatible with PSR11 standards ver 2.0, to use it:
1. Just import PSR11 interfaces in `ServiceContainer.php`
```
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
```
2. Remove PSR11 interfaces from the `ServiceContainer.php` file:
```
######## PSR11 2.0 interfaces #########

..... PSR11 interfaces

######## PSR11 interfaces - END #########
```

> [!NOTE]
> Some plugins use PSR11 interfaces and these files are stored inside these plugins as well as PSR interfaces have versions and are usually not compatible between major versions.  
> **Due that I highly recommend you keep these interfaces inside the file and use PSR11 interfaces under your Plugin/Theme namespace.**


## More examples
```
$container->set('config.requests-limit', 100);
$container->set('config.', ['one', 'two, 'three']);
```

## Roadmap
- [x] Add binding services with configuration
- [x] Add auto-wiring for registered classes in DIC
- [x] Add auto-wiring for defaults primitives for auto-fillings
- [x] Add supporting invocable class
- [x] Add PSR11 interfaces in the ServiceContainer.php.
- [x] Add auto-wiring support for not bounded classes.
- [ ] Add singleton setter and getter
- [ ] Add singleton getting for ServiceContainer
- [ ] Integrate CI
- [ ] Add badges with tests passed
- [ ] PHP 8 named arguments and autowiring
- [ ] Save cache in opcache?
- [ ] Add configurations of ServiceContainer.
- [ ] Choose codestyle
- [ ] Add on packegist
