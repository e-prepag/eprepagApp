<?php include "../../commerce/includes/classPrincipal.php"; ?>
<?php include "../../commerce/includes/connect.php"; ?>
<?php
date_default_timezone_set('America/Fortaleza');

//print_r($_GET);
//echo '<hr>';

$datatransforma = date_parse($_GET['data_compra']);

//print_r($datatransforma);
//echo '<hr>';

$datamake = date('Y-m-d H:i:s',mktime($datatransforma['hour'],$datatransforma['minute'],$datatransforma['second'],$datatransforma['month'],$datatransforma['day'],$datatransforma['year']));

//echo $datamake;
//echo '<hr>';

//$sql 	= "SELECT * FROM tb_pag_compras WHERE numcompra=".$_GET['item_number']."";
$sqlupd = "UPDATE tb_pag_compras SET status=3, status_processed=0, datacompra= '".$datamake."', paypal_txn_id = '".$_GET['tx_id']."' WHERE numcompra=".$_GET['item_number'].""; 

//echo "sql: $sql<hr>"; 
//echo "sqlupd: $sqlupd<hr>"; 

//die('morri');
//echo "<br>$numcompra<br>"; 
//$rsCompra = $conn->Execute($sql) or die("Erro 21");
$ret = SQLexecuteQuery($sqlupd);
?>
Pagamento efetuado com sucesso.

<script>
//window.close();
</script>