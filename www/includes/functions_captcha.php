<?php

function generateRandomCode() {
	$numbersAllowedInCode = false;	//	Set to FALSE for a 'Letters Only' Code
	$numberOfLetters = 4;			//	Allow Minimum 20 Pixels Per Letter - See codeBoxWidth Property Above)

	$GLOBALS['_SESSION']['verificationCode'] = "";
	$ret = "";
	for ($placebo = 1; $placebo<=$numberOfLetters;$placebo++) {
		if ((rand() > 0.49) || ($numbersAllowedInCode == false) ) {
			$number = 97 + rand(0,25);	//rand(97,122);
	        $char = chr($number);
	        $ret .= $char;
		} else {
			$number = 48 + rand(0,10);	//rand(48, 57);
	        $char = chr($number);
	        $ret .= $char;
		}
	}
	$GLOBALS['_SESSION']['verificationCode'] = $ret;
	$GLOBALS['_SESSION']['palavraCodigo'] = $ret;
	return $ret;
}

function translateCode($scode) {
	$numbersAllowedInCode = false;	//	Set to FALSE for a 'Letters Only' Code
	$numberOfLetters = 4;			//	Allow Minimum 20 Pixels Per Letter - See codeBoxWidth Property Above)

	$stmp = "";

	for ($placebo = 0;$placebo<$numberOfLetters;$placebo++) {
		$schar = ord(substr($scode, $placebo, 1)) + $placebo;
		$stmp.= str_pad($schar, 3, '0',STR_PAD_LEFT); 
	}
	return $stmp;
}