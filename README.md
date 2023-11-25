# Simple DI Container for WordPress
Simple DI Container for WordPress with autowiring allows you easily use it in your plugins and themes.

## Why Simple DI Container?
1. Easy to integrate into your project, just copy 1 file.
2. Simple DI Conteiner hasn't any dependencies on other scripts or libraries.
3. Supports Autowiring.
4. Allow you following the best practices while development.

## How to integrate it in my project?
1. Just copy the file `./src/ServiceContainer.php` to your plugin directory or theme.
2. Rename `namespace` in the file from `Pisarevskii\SimpleDIC` to `<Your_Project_Name>\SimpleDIC`
3. Include this file.

That's it!

## How to use it in code

Simple example:
```
$container = new ServiceContainer();

$container->set('config.requests-limit', 100);
$container->set('config.', ['one', 'two, 'three']);

$container->set(Paypal::class, function () {
    return new Paypal();
} );
```


## TODO
- Add autowiring
- Add autofilling defaults primitives
- Add bind with configuration?
- Add singleton setter and getter
- Add singleton getting for ServiceContainer
- Integrate CI
- Add badges with tests passed
- Add on packegist
