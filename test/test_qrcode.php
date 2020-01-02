<?php
require_once('simpletest/autorun.php');
require_once('qrcode.php');

class Test_qrcode extends UnitTestCase {
	function setUp() {
		$this->qrcode = new QRCode( false );
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
		$this->assertEqual( $this->qrcode->mode, 1 );
	}
	function test_qrcode_mode_alphanumeric() {
		$this->qrcode->encode( "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ $%*+-./:");
		$this->assertEqual( $this->qrcode->mode, 2 );
	}
	function test_qrcode_mode_8bit() {
		$this->qrcode->encode( "3403da8" );
		$this->assertEqual( $this->qrcode->mode, 4 );
	}
	function test_qrcode_bitstream_8numbers() {
		$this->qrcode->encode( "01234567" );
		$this->assertEqual( $this->qrcode->bitstream, "00010000001000000000110001010110011000011" );
	}
	function test_qrcode_bitstream_numeric() {
		$this->qrcode->encode( "070993005993" );
		$this->assertEqual( $this->qrcode->bitstream, "000100000011000001000110111110000100000001011111100001" );
	}
	function test_qrcode_bitstream_numeric_16() {
		$this->qrcode->encode( "0123456789012345" );

	}
	function test_qrcode_bitstream_numeric_999(){
		$this->qrcode->encode( "9999999999" );
	}
	function test_qrcode_bitstream_alphanemeric() {
		$this->qrcode->encode( "AC-42" );
	}
	function test_qrcode_bitstream_8bit() {
		$this->qrcode->encode( "They're Made out of Meat.\n\n\"They're made out of meat.\"\n \"Meat?\"\n" );
	}
	function test_qrcode_bitstream_Robomatics(){
		$this->qrcode->encode( "Robomatics" );
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