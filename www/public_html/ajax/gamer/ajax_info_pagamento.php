<?php header("Content-Type: text/html; charset=ISO-8859-1",true) ?>
<?php

// error_reporting(E_ALL); 
// ini_set("display_errors", 1); 
require_once "../../../includes/constantes.php";
require_once DIR_INCS . "inc_register_globals.php";	

require_once DIR_INCS . "main.php";
require_once DIR_INCS . "gamer/main.php";

require_once RAIZ_DO_PROJETO . "db/connect.php"; 

?>
<link href="/css/styles.css" rel="stylesheet" type="text/css" />
<script language="javascript" src="/js/jquery.js"></script>

<?php

echo date("H:i:s")."<br>";

// Recupera dados do pagamento
$sql = "SELECT * FROM tb_pag_compras WHERE numcompra='".$numcompra."' ";
//echo "sql: $sql<br>"; 
//echo "<br>$numcompra<br>"; 
//$rsCompra = $conn->Execute($sql) or die("Erro 21");
$ret = SQLexecuteQuery($sql);
if(!$ret) {
	echo "Erro ao recuperar transa��o de pagamento (1a).<br>\nnumcompra: '$numcompra'<br>\n";
	die("Stop");
}
$ret_row = pg_fetch_array($ret);

// Test for Partner Integration	=========================================================
$b_is_partner = false;
$integra��o_origem_id = "";
//if(strpos($ret_row['cesta'],"Aeria Points")!==false) {
	if (isset($_SESSION['integracao_is_parceiro']) && $_SESSION['integracao_is_parceiro']=="OK" && isset($_SESSION['integracao_origem_id']) && isset($_SESSION['integracao_origem_id'])) {
		$integra��o_origem_id = $_SESSION['integracao_origem_id'];
//		$b_is_partner = true;
	}	
///print_r2($_SESSION);
//}
if($b_is_partner) {
	echo (($integra��o_origem_id!="")?$integra��o_origem_id." - ".$_SESSION['integracao_order_id']."<br>":"-");
}
// End Partner Integration	=============================================================

echo "".(($ret_row['status']==3)  
			? "<font color='#330099' size='1'>Pagamento completo em ". 
				(($ret_row['datacompra']) 
						? date("Y-m-d H:i:s", strtotime($ret_row['datacompra'])) 
						: "-").
			"</font>" 
			: ""
		)."<br>"; 

// <table><tr><td valign='middle'><img src='../../dist_commerce/images/loading1.gif' width='42' height='42' border='0' title='Aguardando pagamento...'></td><td>&nbsp;</td><td valign='middle'><font color='#FF0000' size='1'>O pagamento ainda n�o foi realizado.<br>Clique abaixo para efetuar o pagamento.</font></td></tr></table>


//echo "Compra cadastrada em <b>".date("Y-m-d H:i:s", strtotime($ret_row['datainicio']))."</b><br>";

// Se terminou ou foi cancelado -> cancela integra��o
if(($ret_row['status']==3) || ($ret_row['status']==-1) ) { 
//	if($b_is_partner) {
	if(isset($_SESSION['integracao_is_parceiro'])) {
		$_SESSION['integracao_is_parceiro'] = "";
		$_SESSION['integracao_origem_id'] = "";
		$_SESSION['integracao_order_id'] = "";
		unset($_SESSION['integracao_is_parceiro']);
		unset($_SESSION['integracao_origem_id']);
		unset($_SESSION['integracao_order_id']);
	}
}

// Se completou compra -> para o refresh
	// sql: SELECT * FROM tb_pag_compras WHERE numcompra='2009082014370558633422'
if(($ret_row['status']==3)   ) { //    || ($numcompra=='2009082018122931137084')) {
?>
<script language="JavaScript" type="text/JavaScript">

//alert("#link_bank: " + $("#link_bank").innerHTML);
	//document.link_bank.visibility = 'hidden';
	$("#link_bank").hide("slow");
	$("#pagamento_ok").show("slow");
	refresh_snipet = 0;
</script>
<?php

	// Se for venda de integra��o -> retorna para o site do Parceiro
}	

// Se cancelou compra -> para o refresh
	// sql: SELECT * FROM tb_pag_compras WHERE numcompra='2009082014370558633422'
if(($ret_row['status']==-1)   ) { //    || ($numcompra=='2009082018122931137084')) {
?>
<script language="JavaScript" type="text/JavaScript">

//alert("#link_bank: " + $("#link_bank").innerHTML);
	//document.link_bank.visibility = 'hidden';
	$("#link_bank").hide("slow");
	$("#pagamento_cancela").show("slow");
	refresh_snipet = 0;
</script>
<?php
}

//Fechando Conex�o
pg_close($connid);

?>