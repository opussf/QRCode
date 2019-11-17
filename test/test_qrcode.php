<?php
require_once('simpletest/autorun.php');
require_once('qrcode.php');

class Test_qrcode extends UnitTestCase {
	function setUp() {
		$this->qrcode = new QRCode();
	}
	function tearDown() {
		unset( $this->qrcode );
	}
	function test_qrcode_encode_8numbers() {
		$this->qrcode->encode( "01234567" );
		$this->assertEqual( $this->qrcode->input, "01234567" );
	}
	function test_qrcode_mode_numeric() {
		$this->qrcode->encode( "070993005993" );
	}
	function test_qrcode_mode_alphanumeric() {
		$this->qrcode->encode( "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ $%*+-./:");
	}
	function test_qrcode_mode_8bit() {
		$this->qrcode->encode( "3403da8" );
	}


	/*
	function test_csParse_profs() {
		$this->assertIsA( $this->csParse->profs, array() );
		$this->assertIsA( $this->csParse->profs[0], array() );
		$this->assertEqual( $this->csParse->profs[0], array( "Leatherworking", 450, 438 ) );
		$this->assertEqual( $this->csParse->profs[1], array( "Skinning", 450, 450 ) );
	}
	*/

}
?>