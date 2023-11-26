<?php

declare(strict_types=1);

namespace PisarevskiiTests\SimpleDIC\Assets;

class ClassWithConstructorDepsException {

	private SimpleClass $simple_class;
	private string $string;
	private array $array;

	public function __construct(
		SimpleClass $simple_class,
		string $string,
		array $array = [ 1, 2, 3 ]
	) {
		$this->simple_class = $simple_class;
		$this->string = $string;
		$this->array = $array;
	}

	public function get_text_simple_class(): string {
		return $this->simple_class->get_string_test();
	}

}