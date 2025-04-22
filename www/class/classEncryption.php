<?php

class Encryption
{
	var $pCrypt = "Chave Nao Mexer";
	
    // Information
    // --------
    // File:		xor.php
    // Description:	Exclusive OR encryption 
    //     function
    // Programmer:	MerlinCorey
    // LastEdit:	24 June 2003
    // --------
    // Changes:		- Required function receive
    //     crypt string instead of DEFINE'ing it in
    //     order to run multiple XORs at one time
    //				- Added some example implementatio
    //     n
    // --------
    function xorcrypt($pString, $pCrypt)
    {
    	
    	// String for crypted string
    	$strCrypted = "";
    	// Integer for cipher position
    	$intPos = 0;
    	// Integer for length of cipher string
    	$intCryptLen = strlen($pCrypt);
    	// Integer for length of passed string
    	$intStringLen = strlen($pString);
    	
    	// Go through each character in passed string
    	for ($intCur = 0; $intCur < $intStringLen; $intCur++)
    	{
    		// Check key postion
    		if ($intPos >= $intCryptLen)
    			$intPos = 0;
    		// XOR character verse cipher
    		$strCrypted .= $pString[$intCur] ^ $pCrypt[$intPos];
    		// Go to next position in key
    		$intPos++;
    	}
    	
    	// Return crypted string
    	return $strCrypted;
    }
    
    function encrypt($pString){
    	
    	return base64_encode($this->xorcrypt($pString, $this->pCrypt));
    }
    
    function decrypt($pString){
    	
    	return $this->xorcrypt(base64_decode($pString), $this->pCrypt);
    }
    
}

?>
