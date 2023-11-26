<?php

declare( strict_types=1 );

namespace PisarevskiiTests\SimpleDIC\Assets;

class StaticClass {

	public static function get_string(): string {
		return 'static method of class';
	}

}