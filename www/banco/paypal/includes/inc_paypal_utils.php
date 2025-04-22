<?php
function grava_LOG_Paypal($stitle, $smsg) {
	$f_log_paypal_sucess = "C:/Sites/E-Prepag/backoffice/offweb/tarefas/log/LOG_pagtos_paypal_sucess.txt";
	file_put_contents($f_log_paypal_sucess, $stitle."\n".date("Y-m-d H:i:s")."\n  $smsg\n", FILE_APPEND|LOCK_EX); 

}

?>