<?php

include_once("config.inc.php");
include_once(PATH_INCLUDE_GAMER."classPrincipal.php");
include_once(PATH_INCLUDE_GAMER."classAlawar.php");
include_once(PATH_INCLUDE_GAMER."classSrvUtils.php");

$certificateID = '1852904816934';
$email = 'fabioss13@gmail.com';
$gameID = '1397'; /* Farm Frenzy: Ancient Rome */

$activationKeyAlawar = '';
$errorsAlawar = '';

$alawar = new AlawarAPI($certificateID, AFFILIATE_PID_ALAWAR, $email, AFFILIATE_SECRET_KEY, AFFILIATE_LOCALE_ALAWAR, $gameID);
$alawar->Execute();

if ($alawar->foundErrors()) {	
	$errorsAlawar = $alawar->getErrors();	
}
else {
	$activationKeyAlawar = $alawar->getGameActivationKey();
	$errorsAlawar = $ERRORS_ALAWAR_ID["NO_ERROR"];
}


echo "<pre>";
print_r($alawar);
echo "<hr>";
print_r($activationKeyAlawar);
echo "<hr>";
print_r($errorsAlawar);
echo "</pre>";

?>