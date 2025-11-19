<?php ///
session_start();
if(empty($_SESSION["iduser_bko_pub"]))
    {
            echo '<script>alert("Faça login novamente");</script>';
            exit;
    }
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

$diretorioBase = '/www/arquivos_gerados/lotes/'; 

$varArquivo = isset($_GET['varArquivo']) ? $_GET['varArquivo'] : null;

if (!$varArquivo) {
    die("Arquivo não especificado.");
}

$nomeArquivoSeguro = basename($varArquivo);

$caminhoCompleto = $diretorioBase . $nomeArquivoSeguro;

if (!file_exists($caminhoCompleto) || !is_file($caminhoCompleto) || !is_readable($caminhoCompleto)) {
    http_response_code(404);
    die("Arquivo não encontrado.");
}

header('Content-Description: File Transfer');
header('Cache-Control: private', false);
header('Content-Type: application/octet-stream'); // "octet-stream" é mais padrão que "force-download"
header('Content-Disposition: attachment; filename="' . $nomeArquivoSeguro . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . filesize($caminhoCompleto));
header('Connection: close');

ob_clean();
flush();

readfile($caminhoCompleto);
exit;
?>

