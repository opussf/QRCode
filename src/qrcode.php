<?php

if( $_GET['encode'] ) {
    $input = $_GET['encode'];
}

class QRCode {
	var $input;
	var $encodedStr = "";
	var $modeChars = array(
		1 => array( 0 => "0","1","2","3","4","5","6","7","8","9" ),
		2 => array( 0 => "0","1","2","3","4","5","6","7","8","9",
				"A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z",
				" ", "$", "%", "*", "+", "-", ".", "/", ":" ),
	);
	var $version = 1;	// 1 to 40
	var $bitstream = "";
	var $codeWords = array();

	var $versionMode = array();
	var $errCorrectionVersions = array( "L", "M", "Q", "H" ); // L = 7%, M = 15%, Q = 25%, H = 30%
	var $versionCapacity = array(
		// Number of codewords for: L M Q H (each 8 bits) (bits can be calculated)
		// This is from table 7 in the qr_standards doc.  (p28-p32)
		 1 => array(   19,   16,   13,    9),	 2 => array(   34,   28,   22,   16),	 3 => array(   55,   44,   34,   26),	 4 => array(   80,   64,   48,   36),
		 5 => array(  108,   86,   62,   46),	 6 => array(  136,  108,   76,   60),	 7 => array(  156,  124,   88,   66),	 8 => array(  194,  154,  110,   86),
		 9 => array(  232,  182,  132,  100),	10 => array(  274,  216,  154,  122),	11 => array(  324,  254,  180,  140),	12 => array(  370,  290,  206,  158),
		13 => array(  428,  334,  244,  180),	14 => array(  461,  365,  261,  197),	15 => array(  523,  415,  295,  223),	16 => array(  589,  453,  325,  253),

		40 => array( 2956, 2334, 1666, 1276),
	);


	function QRCode( $debug = false ) {
		$this->debug = $debug;
	}
	function encode( $input ) {
		//print( "input: ->$input<- ".strlen( $input )."\n" );
	    $this->bitstream = "";
	    $this->codeWords = array();
	    $this->versionMode = array();
		$this->input = $input;
		if( strlen( $input ) > 0 ) {
			$this->determineMode();
			$this->makeBitStream();
		}
	}
	function determineMode() {
		$mode = 1;
		$chars = str_split( $this->input );
		foreach( $chars as $char ) {
			if( ! in_array( $char, $this->modeChars[$mode] ) ) {
				$mode *= 2;
			}
			if( $mode > 2 ) { break; } // at 8-bit mode already, break
		}
		$this->mode = $mode;
		return $mode;
	}
	function makeBitStream() {
		//print( $this->input . "\n" );
		if( $this->mode == 1 ) $this->__useNumeric();
		if( $this->mode == 2 ) $this->__useAlphanumeric();
		if( $this->mode == 4 ) $this->__use8bit();
		$this->__appendTerminator();
		$this->__bitstreamToCodewords();
	}
	function valToPaddedBinary( $in, $size ) {
		// take a value in, pad it to $size bits
		$bin = strval( base_convert( $in, 10, 2 ) );
		$padSize = $size - strlen( $bin );
		if( $padSize > 0 ) {
			$bin = str_repeat( "0", $padSize ) . $bin;
		}
		//print( "val: $in  packed: $bin " . strlen( $bin ) ." ".gettype( $bin )."\n" );
		return $bin;
	}
	function __useNumeric() {
		// encode using Numeric mode
		$out = array();
		$bitSize = array( 1 => 4, 2 => 7, 3 => 10 );

		$modeBin = $this->valToPaddedBinary( $this->mode, 4 );
		$out[] = $modeBin;

		// TODO: use a table to look up this bit length
		$charCountBin = $this->valToPaddedBinary( strlen( $this->input ), 10 );
		$out[] = $charCountBin;

		// grab 3 characters at a time
		$vals = str_split( $this->input, 3 );
		foreach( $vals as $val ) {
			$out[] = $this->valToPaddedBinary( $val, $bitSize[strlen($val)] );
		}
		if( $this->debug ) {
			print(join("-", $out) . "\n");
		}
		$this->bitstream = join( "", $out );
	}
	function __useAlphanumeric() {
		// encode using alphanumeric mode
		$out = array();
		$bitSize = array( 1 => 6, 2 => 11 );

		$modeBin = $this->valToPaddedBinary( $this->mode, 4 );
		$out[] = $modeBin;

		$charCountBin = $this->valToPaddedBinary( strlen( $this->input ), 11 );
		$out[] = $charCountBin;

		$vals = str_split( $this->input, 2 );
		foreach( $vals as $val ) {
			//print( "$val\n" );
			$v = str_split( $val );
			//print_r( $v );
			$v1 = array_search( $v[0], $this->modeChars[2] );
			$v2 = array_search( $v[1], $this->modeChars[2] );
			$valLen = strLen( $val );
			if( $valLen == 2 ) {
				$sum = ($v1*45) + $v2;
			} else {
				$sum = $v1;
			}
			$out[] = $this->valToPaddedBinary( $sum, $bitSize[$valLen] );
			//print( $sum."\n" );
		}
		if( $this->debug ) {
			print(join("-", $out) . "\n");
		}
		$this->bitstream = join( "", $out );
	}
	function __use8bit() {
		// encode using 8bit
		$out = array();

		$modeBin = $this->valToPaddedBinary( $this->mode, 4 );
		$out[] = $modeBin;

		// TODO: make this 8 or 16 bits based on version
		$charCountBin = $this->valToPaddedBinary( strlen( $this->input ), 8 );
		$out[] = $charCountBin;

		$vals = str_split( $this->input );
		foreach( $vals as $val ) {
			$v = ord( $val );
			$vbin = $this->valToPaddedBinary( $v, 8 );
			$out[] = $vbin;
			//print( "$val -> $v -> $vbin \n" );
		}
		if( $this->debug ) {
			print(join("-", $out) . "\n");
		}
		$this->bitstream = join( "", $out );
	}
	function __appendTerminator() {
		// 8.4.8 and 8.4.9 describe this process.
		// It seems to say to pad out the last codeword with 0s.
		$codewordCount = intval( strlen( $this->bitstream ) / 8 ) + 1;
		$remainderBits = strlen( $this->bitstream ) % 8;
		//print( $this->input."\n" );
		//print( "len: ".strlen( $this->bitstream ). ", codewordCount: $codewordCount, remainder: $remainderBits\n" );
		// find the versions this can fit into.  1 version for each error correction mode:
		// versionMode = array( "L" => 1, "M" => 2, "Q" => 3, "H" => 4 )
		$errModeCount = count( $this->errCorrectionVersions );

		foreach( $this->versionCapacity as $version => $codewordCapacity ) {
			foreach( $this->errCorrectionVersions as $i => $errMode ) {
				//print( "version: $version, i: $i, errMode: $errMode, codeWords: ".$this->versionCapacity[$version][$i]."\n" );
				if( ! isset( $this->versionMode[$errMode] ) ) { // consider this value
					if( $codewordCount <= $this->versionCapacity[$version][$i] ) {
						$this->versionMode[$errMode] = $version;
					}

				}
			}
			if( count( $this->versionMode ) == $errModeCount ) { break; } // shortcut this once all errormode versions are found.
		}
		//print_r( $this->versionMode );
		//print( "=======\n" );

	}
	function __bitstreamToCodewords() {
		$codewords = str_split( $this->bitstream, 8 );
		if( $this->debug ) {
			print_r($codewords);
		}


	}

	function __toString( ) {
		return( $this->encodedStr );
	}
}

$qr = new QRCode();
$qr->encode( $input );

print( $qr );


?>