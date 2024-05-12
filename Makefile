
php.connect74:
	docker run --rm -it -v "${PWD}":/usr/src/myapp -w /usr/src/myapp pimlab/composer:2.0.0-alpha3-php7.4 sh


php.connect82:
	docker run --rm -it -v "${PWD}":/usr/src/myapp -w /usr/src/myapp shopware/development:8.2-composer-2 bash