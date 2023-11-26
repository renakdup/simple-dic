# Simple DI Container for WordPress
Simple DI Container for WordPress with auto-wiring allows you easily use it in your plugins and themes.

## Why choose Simple DI Container?
1. Easy to integrate into your project, just copy 1 file.
2. Simple DI Conteiner hasn't any dependencies on other scripts or libraries.
3. Supports Autowiring.
4. Allow you following the best practices while development.
5. Almost PSR7 compatible. Just need to rename


## How to integrate it in a project?
1. Just copy the file `./src/ServiceContainer.php` to your plugin directory or theme.
2. Rename `namespace` in the file from `Pisarevskii\SimpleDIC` to `<Your_Project_Name>\SimpleDIC`
3. Include this file.

That's it!

## How to use it in code

Simple example:
```
// create the container
$container = new ServiceContainer();

// set a service
$container->set(Paypal::class, function () {
    return new Paypal();
} );

// get the service
$paypal = $container->get(Paypal::class);

// use this object
$paypal->pay();
```

If you want, you can use conainer inside a factory
```
$container->set('config', [
    'currency' => '$',
    'environment' => 'production',
]);

$container->set(Paypal::class, function ($container) {
    return new Paypal($c->get('config'));
} );
```

Other examples
```
$container->set('config.requests-limit', 100);
$container->set('config.', ['one', 'two, 'three']);
```

## Roadmap
- [x] Add binding services with configuration
- [x] Add auto-wiring for registered classes in DIC
- [x] Add auto-wiring for defaults primitives for auto-fillings
- [ ] Check invocable class creating
- [ ] Add PSR7 interfaces in the ServiceContainer.php.
- [ ] Add singleton getting for ServiceContainer
- [ ] Add singleton setter and getter
- [ ] Choose codestyle
- [ ] Integrate CI
- [ ] PHP 8 named arguments and autowiring
- [ ] Add badges with tests passed
- [ ] Save cache in opcache?
- [ ] Add configurations of ServiceContainer.
- [ ] Add on packegist
