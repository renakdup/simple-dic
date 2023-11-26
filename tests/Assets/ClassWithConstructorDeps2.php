<?php

declare( strict_types=1 );

namespace PisarevskiiTests\SimpleDIC\Assets;

class ClassWithConstructorDeps2 {

	private ClassWithConstructorDeps $class_with_constructor_deps;

	public function __construct( ClassWithConstructorDeps $class_with_constructor_deps ) {
		$this->class_with_constructor_deps = $class_with_constructor_deps;
	}

	public function get_text_simple_class(): string {
		return $this->class_with_constructor_deps->get_text_simple_class();
	}

}