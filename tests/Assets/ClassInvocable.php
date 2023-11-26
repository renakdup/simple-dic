<?php

declare(strict_types=1);

namespace PisarevskiiTests\SimpleDIC\Assets;

class ClassInvocable {

	private SimpleClass $simple_class;

	public function __construct(SimpleClass $simple_class) {
		$this->simple_class = $simple_class;
	}

	public function __invoke(): string {
		return $this->simple_class->get_string_test( 'Function is called from ClassInvocable' );
	}

}