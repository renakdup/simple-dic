<?php

declare( strict_types=1 );

namespace RenakdupTests\SimpleDIC\Assets;

class SimpleClass {

	public function get_string_test( $text ): string {
		if ( $text ) {
			return $text;
		}

		return 'method of class';
	}

}