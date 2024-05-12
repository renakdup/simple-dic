<?php

declare(strict_types=1);

namespace RenakdupTests\SimpleDIC\Assets;

class ClassWithConstructorDepsException {

	public SimpleClass $simple_class;
	public string $string;
	public array $array;

	public function __construct(
		SimpleClass $simple_class,
		string $string,
		array $array = [ 1, 2, 3 ]
	) {
		$this->simple_class = $simple_class;
		$this->string = $string;
		$this->array = $array;
	}
}