<?php
	require_once('simpletest/autorun.php');

	$test = new TestSuite('QR Code Tests');
	$test->addFile('test_qrcode.php');

	$test->run(new HTMLReporter( ) );

?>

