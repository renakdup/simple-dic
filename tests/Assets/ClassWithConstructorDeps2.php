<?php

declare( strict_types=1 );

namespace PisarevskiiTests\SimpleDIC\Assets;

class ClassWithConstructorDeps2 {

	private ClassWithConstructorDeps $obj_with_constructor_deps;

	public function __construct( ClassWithConstructorDeps $obj_with_constructor_deps ) {
		$this->obj_with_constructor_deps = $obj_with_constructor_deps;
	}
}