<?php

declare( strict_types=1 );

namespace PisarevskiiTests\SimpleDIC\Assets;

class ClassWithConstructor {

	public ClassWithConstructorPrimitives $obj_with_constructor_deps;

	public function __construct( ClassWithConstructorPrimitives $obj_with_constructor_deps ) {
		$this->obj_with_constructor_deps = $obj_with_constructor_deps;
	}
}