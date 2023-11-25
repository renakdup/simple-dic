# Simple DI Container for WordPress
Simple DI Container for WordPress with autowiring allows you easily use it in your plugins and themes.

## Why Simple DI Container?
1. Easy to integrate into your project, just copy 1 file.
2. Simple DI Conteiner hasn't any dependencies on other scripts or libraries.
3. Supports Autowiring.
4. Allow you following the best practices while development.
5. Almost PSR7 compatible.


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
- Add auto-wiring
- Add supporting defaults primitives for autofilling
- Add bind with configuration?
- Add singleton setter and getter
- Add singleton getting for ServiceContainer
- Integrate CI
- Add badges with tests passed
- Save cache in opcache?
- Add configurations of ServiceContainer.
- Add on packegist
