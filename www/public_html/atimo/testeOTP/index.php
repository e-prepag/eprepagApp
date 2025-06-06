<?php

	$meu_ip_1 = '201.93.162.169';
	$meu_ip_2 = '189.62.151.212';

	if ($_SERVER['REMOTE_ADDR'] == $meu_ip_1 || $_SERVER['REMOTE_ADDR'] == $meu_ip_2) {
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
	}

	require_once "./OTP/OTP.php";
	
	$classOTP = new OTP();
	
	$oi = $classOTP->getTeste();
	
	echo $oi;
	
