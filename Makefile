
d.run:
	docker run --rm -it -v "${PWD}":/usr/src/myapp -w /usr/src/myapp pimlab/composer:2.0.0-alpha3-php7.4 sh