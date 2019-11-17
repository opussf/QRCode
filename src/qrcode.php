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
        	$mode = 1;
        	$chars = str_split( $this->input );

        }
        //functio
        function __toString( ) {
                return( $this->encodedStr );
        }
}



?>