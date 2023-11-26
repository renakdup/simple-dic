<?php

declare(strict_types=1);

namespace PisarevskiiTests\SimpleDIC\Assets;

class ClassWithConstructorDeps {

	private SimpleClass $simple_class;

	public function __construct(SimpleClass $simple_class) {
		$this->simple_class = $simple_class;
	}

	public function get_text_simple_class(): string {
		return $this->simple_class->get_string_test();
	}

}