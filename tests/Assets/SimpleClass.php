<?php

declare( strict_types=1 );

namespace PisarevskiiTests\SimpleDIC\Assets;

class SimpleClass {

	public function get_string_test( $text ): string {
		if ( $text ) {
			return $text;
		}

		return 'method of class';
	}

}