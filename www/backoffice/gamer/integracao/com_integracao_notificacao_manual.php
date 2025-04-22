<?php 
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/main.php";

set_time_limit ( 3000 ) ;

$run_silently = "OK"; 

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

$time_start = getmicrotime();

$msg = "";
$msg_notify = "";
//	$ip_id = $_REQUEST['ip_id'];
$store_id = $_REQUEST['store_id'];
$order_id = $_REQUEST['order_id'];

// Procura última venda cadastrada para este pedido
$sql  = "select iph_ip_vg_id ";
$sql .= "from tb_integracao_pedido_historico iph ";
$sql .= " where 1=1 ";
$sql .= " and iph.iph_ip_vg_id > 0 ";
$sql .= " and iph.iph_ip_store_id = '" . $store_id . "' ";
$sql .= " and iph.iph_ip_order_id = '" . $order_id . "' ";
$sql .= " order by iph_data_inclusao desc limit 1";
$rs = SQLexecuteQuery($sql);
if($rs && pg_num_rows($rs) > 0) {
        $rs_row = pg_fetch_array($rs);

        $vg_id = $rs_row["iph_ip_vg_id"];
} else {
        echo "<p align='center'><font style='color:red' face='Arial, Helvetica, sans-serif' size='2'>Venda não encontrada para store_id = '" . $store_id . "' e order_id = '" . $order_id . "' </font></p>";
        die("Stop");
}

echo "<p align='center' class='text'><font style='color:blue' face='Arial, Helvetica, sans-serif' size='2'>Venda ".$vg_id." encontrada para store_id = '" . $store_id . "' e order_id = '" . $order_id . "' </font></p>";

if(!$store_id) $msg = "Erro: Forneça o identificador do parceiro";
if(!$order_id) $msg = "Erro: Forneça o identificador do pedido";

if($store_id && $order_id) {
        $sql  = "select * ";
        $sql .= "from tb_integracao_pedido_historico iph ";
        $sql .= " where 1=1 ";
//		$sql .= " and iph.iph_ip_id = '" . $ip_id . "' ";
        $sql .= " and iph.iph_ip_store_id = '" . $store_id . "' ";
        $sql .= " and iph.iph_ip_order_id = '" . $order_id . "' ";
//		$sql .= " and iph.iph_ip_vg_id = ".$vg_id." ";
        $sql .= " order by iph_data_inclusao desc";

//echo $sql."<br>\n";
//die("Stop");
        $rs = SQLexecuteQuery($sql);

        if($rs && pg_num_rows($rs) > 0) {

                if($BtnNotify) {
                        // Monta o passo 4 da Integração - Notify partner
                        $sql = "SELECT * FROM tb_integracao_pedido ip 
                                WHERE 1=1
                                and ip_store_id = '".$store_id."'
                                and ip_order_id = '".$order_id."'
                                and ip_vg_id = ".$vg_id." 
                                and ip_id = ".$ip_id."";
grava_log_integracao_tmp(str_repeat("-", 80)."\nSelect registro de integração para o notify (A3 manual)\n".$sql."\n");
//echo "$sql<br>";

				$rs_ped = SQLexecuteQuery($sql);
//echo "nrows ped: ".pg_num_rows($rs_ped)."<br>";

				if(!$rs_ped) {
					$msg_1 = date("Y-m-d H:i:s")." - Erro ao recuperar transação de integração (manual) Nada encontrado (ip_id: ".$ip_id.", store_id: '".$store_id."', order_id: ".$order_id.", vg_id: $vg_id).\n   $sql\n";
					echo $msg_1;
grava_log_integracao_tmp(str_repeat("-", 80)."\n".$msg_1);
				} elseif(pg_num_rows($rs_ped)>1) {
					$msg_1 = date("Y-m-d H:i:s")." - Erro ao recuperar transação de integração (manual) Encontradas mais de uma transação (n=".pg_num_rows($rs_ped).") (ip_id: ".$ip_id.", store_id: '".$store_id."', order_id: ".$order_id.", vg_id: $vg_id).\n   $sql\n";
					echo $msg_1;
grava_log_integracao_tmp(str_repeat("-", 80)."\n".$msg_1);
				} else {
					$rs_ped_row = pg_fetch_array($rs_ped);

					// Get parameters
					$post_parameters = "store_id=".$rs_ped_row["ip_store_id"]."&";

					$post_parameters .= "transaction_id=".$rs_ped_row["ip_transaction_id"]."&";
					$post_parameters .= "order_id=".$rs_ped_row["ip_order_id"]."&";
					$post_parameters .= "amount=".$rs_ped_row["ip_amount"]."&";
					if(strlen($rs_ped_row["ip_product_id"])>0) {
						$post_parameters .= "product_id=".$rs_ped_row["ip_product_id"]."&";
					}
					$post_parameters .= "client_email=".$rs_ped_row["ip_client_email"]."&";
					$post_parameters .= "client_id=".$rs_ped_row["ip_client_id"]."&";

					$post_parameters .= "currency_code=".$rs_ped_row["ip_currency_code"]."";

					// Do notify
					$notify_url = getPartner_notify_url_By_ID($store_id);
					//echo "<center><p style='color:green'>Do notify (manual) at '$notify_url' with parameters '".htmlentities($post_parameters)."'</p></center>";
					
					$sret = getIntegracaoCURL($notify_url, $post_parameters);

					$msg_notify = "Retorno de NOTIFY MANUAL com sucesso: <br>'RET: [".$sret."]'<br><br>Header:<pre>".print_r($sret, true)."</pre>"; //substr($sret, strpos($sret, "CODRETEPP"))."'\n";
					grava_log_integracao("".str_repeat("V",80)."\n   sret: ".print_r($sret, true)."\n  ".$msg_notify."\n   notify_url: '$notify_url'\n   post_parameters: ".$post_parameters."\n".str_repeat("M",80)."\n");

					$s_msg = "URL: $notify_url, \nPOST: $post_parameters\n$sret\n";
					send_debug_info_by_email("E-Prepag - Testing integration (notificação manual) (integracao_b_origem: $integracao_b_origem)", $s_msg, $rs_ped_row["ip_store_id"]);
				
				}//end else do if(!$rs_ped)
		} //end if($BtnNotify)
	} //end if($rs && pg_num_rows($rs) > 0) 
}//end if($store_id && $order_id)

ob_end_flush();
?>
<html>
<head>
<title>E-Prepag - &Aacute;rea do Parceiro</title>
<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
<META HTTP-EQUIV="EXPIRES" CONTENT="0">
<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
<link rel="stylesheet" href="/css/css.css" type="text/css">

<script language="javascript">
function GP_popupAlertMsg(msg) { //v1.0
  document.MM_returnValue = alert(msg);
}
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}

</script>


</head>
<body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<center>
<form name="form1" method="post" action="com_integracao_notificacao_manual.php">
<input type="hidden" name="store_id" value="<?php echo $store_id ?>">
<input type="hidden" name="order_id" value="<?php echo $order_id ?>">

<table width="400" border="0" cellspacing="0" cellpadding="0">
  <tr> 
    <td width="400" valign="top"> 
        <table width="400" border="0" cellpadding="0" cellspacing="2">
          <tr> 
            <td colspan="5">
				<table width="400" border="0" cellpadding="0" cellspacing="0" dwcopytype="CopyTableCell">
					<tr> 
					    <td width="400" height="21" bgcolor="00008C">
							<font face="Arial, Helvetica, sans-serif" size="2" color="#FFFFFF"><b>Money - Integração - Notificação Manual</b></font></td>
					  </tr>
				</table>
		      <table border='0' width="100%" cellpadding="2" cellspacing="0">
        			<tr bgcolor=""> 
			          <td>&nbsp;</td>
			          <td>&nbsp;</td>
			        </tr>
		      </table>
			</td>
          </tr>
		</table>

		<?php if($msg=="") { ?>
        <table width="400" border="0" cellpadding="0" cellspacing="2">
          <tr bgcolor="#F5F5FB"> 
			<?php if(!$BtnNotify) { ?>
            <td align="right"><input type="submit" name="BtnNotify" value="Notificar" class="botao_search"></td>
			<?php } ?>
          </tr>
		</table>
        <table width="400" border="0" cellpadding="0" cellspacing="2">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="2" bgcolor="#ECE9D8" class="texto">Dados do Parceiro</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"><td width="100" class="texto">Parceiro ID</td><td class="texto_black">&nbsp;<?php echo $store_id ?></td></tr>
          <tr bgcolor="#F5F5FB"><td width="100" class="texto">Parceiro Nome</td><td class="texto_black">&nbsp;<?php echo getPartner_param_By_ID('partner_name', $store_id) ?></td></tr>

          <tr bgcolor="#FFFFFF"> 
            <td colspan="2" bgcolor="#ECE9D8" class="texto">Dados do Pedido</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"><td width="100" class="texto">order_id</td><td class="texto_black">&nbsp;<?php echo $order_id ?></td></tr>
		
		</table>
        <table width="400" border="0" cellpadding="0" cellspacing="2">
          <tr bgcolor="#F5F5FB"> 
			<?php 
			if($BtnNotify && $msg_notify) { 
				echo "<p style='color:blue'>".$msg_notify."</p>";
			} ?>
          </tr>
		</table>
		<?php } else { ?>
			<p style="color:red" class="texto"><?php echo $msg?></p>
		<?php } ?>


    </td>
  </tr>
  <tr> 
    <td width="400" valign="top"> 
        <table width="400" border="0" cellpadding="0" cellspacing="2">
          <tr bgcolor="#FFFFFF"> 
            <td bgcolor="#ECE9D8" class="texto">Dados do Pedido - modificações</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"><td width="100" class="texto">
		<?php 

				if(!$rs || pg_num_rows($rs) == 0) {
					echo "<p class='texto'>Sem registros</p>\n";
				} else {

					echo "<table width='100%' cellpadding='0' cellspacing='1' border='1' bordercolor='#cccccc' style='border-collapse:collapse;' align='center'>\n <tr class='texto' align='center'><td><b>ip_id</b></td> <td><b>store_id</b></td> <td><b>order_id</b></td> <td><b>vg_id</b></td> <td><b>data_inclusao</b></td> <td><b>status_confirmed</b></td></tr>\n";
					$ip_id = 0;
					while($rs_row = pg_fetch_array($rs)){

						$s_link = (($rs_row['iph_ip_vg_id']>0) ? "<a href='/gamer/vendas/com_venda_detalhe.php?venda_id=" . $rs_row['iph_ip_vg_id'] . "&fila_ncamp=vg_data_inclusao&fila_ordem=1&BtnSearch=1&tf_v_codigo=" . $rs_row['iph_ip_vg_id'] . "' target='_blank'>":"") . $rs_row['iph_ip_vg_id'] . (($rs_row['iph_ip_vg_id']>0)?"</a>":"");
						// $rs_row['iph_ip_vg_id']

						if($ip_id==0) {
							if($rs_row['iph_ip_vg_id'] == $vg_id) $ip_id = $rs_row['iph_ip_id'];
						}

						echo "<tr class='texto' align='center'><td>" . $rs_row['iph_ip_id'] . "</td><td style='color:". (($rs_historico_notify_row['ipnh_store_id']!=$rs_row['iph_ip_store_id'])?"red":"darkgreen") ."'>" . $rs_row['iph_ip_store_id'] . "</td><td style='color:". (($rs_historico_notify_row['ipnh_order_id']!=$rs_row['iph_ip_order_id'])?"red":"blue") ."'>" . $rs_row['iph_ip_order_id'] . "</td><td>" . $s_link . "</td><td><nobr>" . substr($rs_row['iph_data_inclusao'],0,19) . "</nobr></td><td>" . $rs_row['iph_ip_status_confirmed'] . "</td></tr>\n";
					} //end while
					echo "</table>\n";
					echo "ip_id encontrado: $ip_id<br>";

				} //end else do if(!$rs || pg_num_rows($rs) == 0)
		?>
			</td></tr>
		</table>
    </td>
  </tr>
</table>
<input type="hidden" name="ip_id" value="<?php echo $ip_id ?>">
</form>

</center>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</html>
