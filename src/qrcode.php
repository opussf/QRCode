<?php

class QRCode {
	var $input;
	var $encodedStr = "";

	function QRCode( ) {
	}
	function encode( $input ) {
		$this->input = $input;
		$this->determineMode();
	}
	function determineMode() {
		$modes = array(
			1 => array( 0 => "0","1","2","3","4","5","6","7","8","9" ),
			2 => array( 0 => "0","1","2","3","4","5","6","7","8","9",
					"A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z",
					" ", "$", "%", "*", "+", "-", ".", "/", ":" ),
		);
		/* Do not need to expand the 8-bit characater set.
		for( $lcv = 0; $lcv <= 255; $lcv++ ) {
		$modes[4][$lcv] = chr( $lcv );
		}
		*/

		$mode = 1;
		$chars = str_split( $this->input );
		foreach( $chars as $char ) {
			if( ! in_array( $char, $modes[$mode] ) ) {
				$mode *= 2;
				//print( "mode: $mode\n" );
			}
			if( $mode > 2 ) { break; } // at 8-bit mode already, break
		}
		$this->mode = $mode;
		//print( "mode: $mode\n" );
		//print decbin( $mode );
		return $mode;

	}

	function __toString( ) {
		return( $this->encodedStr );
	}
}



?>