<?php

declare( strict_types=1 );

namespace RenakdupTests\SimpleDIC\Assets;

use stdClass;

class ClassWithConstructor
{
	private stdClass $std;
	public ClassWithConstructorPrimitives $obj_with_constructor_deps;

	public function __construct( ClassWithConstructorPrimitives $obj_with_constructor_deps, $std = new stdClass() ) {
		$this->obj_with_constructor_deps = $obj_with_constructor_deps;
		$this->std = $std;
	}
}