<?php


	$senhaCriptografada = '2ynTz/WT3wu9Q/KtrYcuyQ==';
	
	require_once "/www/class/classEncryption.php"; 
		
	$criptografia = new Encryption();
	$senha = $criptografia->decrypt($senhaCriptografada);
		
	echo $senha;
