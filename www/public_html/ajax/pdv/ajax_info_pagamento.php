<?php 
 //error_reporting(E_ALL); 
 //ini_set("display_errors", 1); 

 header("Content-Type: text/html; charset=UTF-8",true);

 if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || ($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest'))
     die("Chamada não permitida!");
 
require_once "../../../includes/constantes.php";
require_once DIR_INCS . "inc_register_globals.php";	
require_once RAIZ_DO_PROJETO . "db/connect.php";
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "pdv/main.php";
?>

<script language="javascript" src="/js/jquery.js"></script>

<?php

echo date("H:i:s")."<br>";

// recupera cesta
$numcompra = preg_replace("/\D/", "", $numcompra);
// Recupera da base de dados da loja os dados da compra
$sql = "SELECT * FROM tb_pag_compras WHERE numcompra='".$numcompra."' ";
//echo "sql: $sql<br>"; 
//echo "<br>$numcompra<br>"; 
//$rsCompra = $conn->Execute($sql) or die("Erro 21");
$ret = SQLexecuteQuery($sql);
if(!$ret) {
	echo "Erro ao recuperar transação de pagamento (1a).<br>\nnumcompra: '$numcompra'<br>\n";
	die("Stop");
}
$ret_row = pg_fetch_array($ret);

echo utf8_encode("".(($ret_row['status']==3)?(($ret_row['datacompra'])?date("Y-m-d H:i:s", strtotime($ret_row['datacompra'])):"-"):
            "<table>"
        . "    <tr>"
        . "         <td valign='middle'>"
        . "             <img src='/imagens/loading1.gif' width='42' height='42' border='0' title='Aguardando pagamento...'>"
        . "         </td>"
        . "         <td>&nbsp;</td>"
        . "         <td valign='middle'>"
        . "             <font color='#FF0000' size='1'>O pagamento ainda não foi realizado. <br>Clique abaixo para efetua-lo.</font>"
        . "         </td>"
        . "     </tr>"
        . "</table>")."<br>"); 

//echo "Compra cadastrada em <b>".date("Y-m-d H:i:s", strtotime($ret_row['datainicio']))."</b><br>";

// Se completou compra -> para o refresh
	// sql: SELECT * FROM tb_pag_compras WHERE numcompra='2009082014370558633422'
if(($ret_row['status']==3)   ) { //    || ($numcompra=='2009082018122931137084')) {
?>
<script language="JavaScript">
    $("#link_bank").hide("slow");
    $("#pagamento_ok").show("slow");
    refresh_snipet = 0;
</script>
<?php
}	

// Se cancelou compra -> para o refresh
	// sql: SELECT * FROM tb_pag_compras WHERE numcompra='2009082014370558633422'
if(($ret_row['status']==-1)   ) { //    || ($numcompra=='2009082018122931137084')) {
?>
<script language="JavaScript">
    $("#link_bank").hide("slow");
    $("#pagamento_cancela").show("slow");
    refresh_snipet = 0;
</script>
<?php
}	

//Fechando Conexão
pg_close($connid);

?>



