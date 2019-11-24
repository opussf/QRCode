<?php
require_once('simpletest/autorun.php');
require_once('qrcode.php');

class Test_qrcode extends UnitTestCase {
	var $numbers8="01234567";
	var $alphanumeric="0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ $%*+-./:";


	function setUp() {
		$this->qrcode = new QRCode( false );
		$this->meat = file_get_contents( "meat.md" );
	}
	function tearDown() {
		unset( $this->qrcode );
	}
	function test_qrcode_encode_0len() {
		// How to handle 0 chars
		$this->qrcode->encode( "" );
		$this->assertEqual( $this->qrcode->input, "" );
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
		$this->qrcode->encode( "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ $%*+-./:" );
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
	function test_qrcode_bitstream_alphanumeric() {
		// just look at the length of this
		$this->qrcode->encode( "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ $%*+-./:" );
		$this->assertEqual( strlen( $this->qrcode->bitstream ), 263 );
	}
	function test_qrcode_bitstream_numeric_16() {
		$this->qrcode->encode( "0123456789012345" );
		$this->assertEqual( $this->qrcode->bitstream, "00010000010000000000110001010110011010100110111000010100111010100101" );
	}
	function test_qrcode_bitstream_numeric_999(){
		$this->qrcode->encode( "9999999999" );
		$this->assertEqual( $this->qrcode->bitstream, "000100000010101111100111111110011111111001111001" );
	}
	function test_qrcode_bitstream_alphanemeric_AC() {
		$this->qrcode->encode( "AC-42" );
		$this->assertEqual( $this->qrcode->bitstream, "0010000000001010011100111011100111001000010" );
	}
	function test_qrcode_bitstream_8bit() {
		$this->qrcode->encode( "They're Made out of Meat.\n\n\"They're made out of meat.\"\n \"Meat?\"\n" );
	}
	function test_qrcode_determine_versions_8numbers() {
		// determine versions for each mode
		$this->qrcode->encode( "01234567" );
		$this->assertEqual( $this->qrcode->versionMode, array( "L"=>1, "M"=>1, "Q"=>1, "H"=>1 ) );
	}
	function test_qrcode_determine_versions_numeric() {
		$this->qrcode->encode( "070993005993" );
		$this->assertEqual( $this->qrcode->versionMode, array( "L"=>1, "M"=>1, "Q"=>1, "H"=>1 ) );
	}
	function test_qrcode_determine_versions_alphanumeric() {
		// this is 256 code words
		$this->qrcode->encode( "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ $%*+-./:" );
		$this->assertEqual( $this->qrcode->versionMode, array( "L"=>2, "M"=>3, "Q"=>3, "H"=>4 ) );
	}
	function test_qrcode_determine_versions_numeric_16() {
		$this->qrcode->encode( "0123456789012345" );
		$this->assertEqual( $this->qrcode->versionMode, array( "L"=>1, "M"=>1, "Q"=>1, "H"=>1 ) );
	}
	function test_qrcode_determine_versions_8bit() {
		// cwc = 66
		$this->qrcode->encode( "They're Made out of Meat.\n\n\"They're made out of meat.\"\n \"Meat?\"\n" );
		$this->assertEqual( $this->qrcode->versionMode, array( "L"=>4, "M"=>5, "Q"=>6, "H"=>7 ) );
	}
	function test_qrcode_determine_versions_http() {
		// cwc = 76
		$this->qrcode->encode( "http://xh.zz9-za.com/checkin/html/554317a601bb9863322dd6b97ce39fd509a7ce7b" );
		$this->assertEqual( $this->qrcode->versionMode, array( "L"=>4, "M"=>5, "Q"=>6, "H"=>8 ) );
	}
	function test_qrcode_determine_versions_http2() {
		// cwc = 92
		$this->qrcode->encode( "http://pictures.zz9-za.com/shows/movies/Star_Wars%20EP%205_%20Empire%20Strikes%20Back.m4v" );
		$this->assertEqual( $this->qrcode->versionMode, array( "L"=>5, "M"=>6, "Q"=>8, "H"=>9 ) );
	}
	function test_qrcode_determine_versions_http3() {
		// cwc = 109
		$this->qrcode->encode( "http://pictures.zz9-za.com/shows/movies/To%20Wong%20Foo%20Thanks%20for%20Everything%2C%20Julie%20Newmar.mp4" );
		$this->assertEqual( $this->qrcode->versionMode, array( "L"=>6, "M"=>7, "Q"=>8, "H"=>10 ) );
	}
	function test_qrcode_determine_versions_meat() {
		$this->qrcode->encode( $this->meat );
		$this->assertEqual( $this->qrcode->versionMode, array( "L"=>18, "M"=>20, "Q"=>24, "H"=>28 ) );
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