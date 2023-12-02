<?php

declare( strict_types=1 );

namespace PisarevskiiTests\SimpleDIC\Assets;

class ClassWithConstructorSupertypes {

	private ParentClass $parent_class;
	private AbstractClass $abstract_class;
	private SomeInterface $some;

	public function __construct( ParentClass $parent_class, AbstractClass $abstract_class, SomeInterface $some ) {
		$this->parent_class = $parent_class;
		$this->abstract_class = $abstract_class;
		$this->some = $some;
	}
}