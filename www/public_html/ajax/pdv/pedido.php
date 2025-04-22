<?php
//script equivalente a: "/www/web/prepag2/dist_commerce/ajaxPedido.php"
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
header("Content-Type: text/html; charset=ISO-8859-1",true);
header('P3P: CP="CAO PSA OUR"');

function isAjax() {return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));}
function block_direct_calling() {
    if(!isAjax()) {
           echo "Chamada não permitida<br>";
           die("Stop");
    }
}
block_direct_calling();
require_once "../../../includes/constantes.php";
require_once DIR_INCS . "configIP.php";
require_once DIR_CLASS . "pdv/classGamesUsuario.php";
require_once DIR_INCS . "rs_ws/inc_utils.php";
require_once DIR_INCS . "pdv/constantes.php";
@session_start();
        
$id = $GLOBALS['_SESSION']['venda'];

// include do arquivo contendo IPs DEV


$server_url = "www.e-prepag.com.br";
if(checkIP()) {
    $server_url = $_SERVER['SERVER_NAME'];
}

if ($id >= 0){
	$ret = get_status_pedido_lan($id);

	switch($ret) {
		case $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']:
			print "\n<script language='JavaScript' type='text/JavaScript'> refresh_snipet = 0;</script>";
                        include DIR_WEB . "ajax/pdv/incProcessado.php";
			break;
		case $GLOBALS['STATUS_VENDA']['PROCESSAMENTO_REALIZADO']:
			print "\n<script language='JavaScript' type='text/JavaScript'> refresh_snipet = 0;</script>";
                        include DIR_WEB . "ajax/pdv/incProcessado.php";
			break;
		case $GLOBALS['STATUS_VENDA']['VENDA_CANCELADA']:
			print "<p class='text-red'>Pedido cancelado automaticamente.</p>";
			print "\n<script language='JavaScript' type='text/JavaScript'> refresh_snipet = 0;</script>";
			break;
		case -1:
			print "<p class='text-red'>Número do pedido não informado.</p>";
			print "\n<script language='JavaScript' type='text/JavaScript'> refresh_snipet = 0;</script>";
			break;
		case -2:
			print "<p class='text-red'>Pedido não encontrado.</p>";
			print "\n<script language='JavaScript' type='text/JavaScript'> refresh_snipet = 0;</script>";
			break;
		default:
			print "<p><img src='/imagens/loading1.gif' border='0' title='Pedido aguardando processamento....'/></p><p class='txt-vermelho'>Pedido aguardando processamento.</p>";
			break;
	}//end switch
}//end if ($id >= 0)

function get_status_pedido_lan($vg_id) {

	$usuarioGames = unserialize($GLOBALS['_SESSION']['dist_usuarioGames_ser']);
        if(empty($vg_id) || !method_exists($usuarioGames, 'getId')) {
            return -1;
        }
        
        $sql = "select * from tb_dist_venda_games where vg_id = ".$vg_id." and vg_ug_id = ".$usuarioGames->getId()." order by vg_data_inclusao desc limit 1";
        $rs = SQLexecuteQuery($sql);
	if(!$rs || pg_num_rows($rs) == 0) {
		echo "Nenhum produto encontrado (ERRO: WM360).\n";
		return -2;
	} else {
		$rs_row = pg_fetch_array($rs);
		return $rs_row['vg_ultimo_status'];
        }
}//end function get_status_pedido


//Fechando Conexão
pg_close($_config['db_connid']);

?>