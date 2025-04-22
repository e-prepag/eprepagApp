<?php 
if($_SESSION["tipo_acesso_pub"]=='PU') {
	//redireciona
	$strRedirect = "/sys/admin/commerce/index.php";
	ob_end_clean();
	header("Location: " . $strRedirect);
	exit;
	?><html><body onLoad="window.location='<?php echo $strRedirect?>'"><?php
	exit;
	
	ob_end_flush();
}

$varArquivo	=	isset($_GET['varArquivo'])	? $_GET['varArquivo']	: null;

// set headers
header('Content-Description: File Transfer');
header('Cache-Control: private',false);
header('Content-type: application/force-download'); 
header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename='.basename($varArquivo));
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . filesize($varArquivo));
header('Connection: close');
  
readfile($varArquivo);

?>
