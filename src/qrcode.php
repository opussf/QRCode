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
	var $errorCorrectionVersion = "H";  // L = 7%, M = 15%, Q = 25%, H = 30%
	var $bitstream = "";
	var $codeWords = array();

	function QRCode( $debug = false ) {
		$this->debug = $debug;
	}
	function encode( $input ) {
		//print( "input: ->$input<- ".strlen( $input )."\n" );
	    $this->bitstream = "";
	    $this->codeWords = array();
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
		print( $this->input . "\n" );
		if( $this->mode == 1 ) $this->__useNumeric();
		if( $this->mode == 2 ) $this->__useAlphanumeric();
		if( $this->mode == 4 ) $this->__use8bit();
		$this->__bitstreamToCodewords();
	}
	function valToPaddedBinary( $in, $size ) {
		// take a value in, pad it to $size bits
		$bin = strval( base_convert( $in, 10, 2 ) );
		$padSize = $size - strlen( $bin );
		$bin = str_repeat( "0", $padSize ) . $bin;
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