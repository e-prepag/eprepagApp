<?php require_once __DIR__ . '/../../../includes/constantes_url.php'; ?>
<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
header("Content-Type: text/html; charset=ISO-8859-1",true);
require_once "../../../includes/constantes.php";
require_once DIR_INCS . "gamer/inc_ajax.php";

// include do arquivo contendo IPs DEV
require_once DIR_INCS . "configIP.php";

$server_url = '' . EPREPAG_URL . '';
if(checkIP()) {
    $server_url = $_SERVER['SERVER_NAME'];
    }

block_direct_calling();

require_once DIR_INCS . "main.php";
require_once DIR_INCS . "gamer/main.php"; 

//	if ($_SERVER['HTTPS']=="on") { //descomentar para implementar https

//validaSessao(1);

echo "<script language='javascript' type='text/javascript'>
//utilizar PIN
function usar_pin(){
		$(document).ready(function(){
			$.ajax({
				type: 'POST',
				url: 'http".(($_SERVER['HTTPS']=="on")?"s":"") ."://" . $server_url . "/ajax/gamer/ajax_pin_carga.php',
				data: ";
echo "$('#formAddPIN').serialize()";
$data =  array(
				'op'		=> 'uti',
			);
foreach($data as $key => $value) {
	echo " + '&".$key."=".$value."'";
}
echo ",
				beforeSend: function(){
					$('#box-resumo-pedido').html(\"<table><tr class='box-principal-class'><td><img src='/imagens/loading1.gif' border='0' title='Aguardando pagamento...'/></td><td><font size='1'> <b>Aguarde... Verificando.</b></font></td></tr></table>\");
				},
				success: function(txt){
					if (txt != 'ERRO') {
						$('#box-principal').html(txt);
					} 
				},
				error: function(){
					$('#box-principal').html('');
				}
			});
		});
}
</script>";
//} //end if ($_SERVER['HTTPS']=="on")

//Fechando Conexão
pg_close($connid);

?>