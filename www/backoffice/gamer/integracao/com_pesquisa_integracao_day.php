<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/main.php";
require_once $raiz_do_projeto."class/gamer/classIntegracao.php";
require_once "/www/includes/bourls.php";

$time_start = getmicrotime();
	
	if(!$ncamp)    $ncamp       = 'ip_data_inclusao';
	if(!$inicial)  $inicial     = 0;
	if(!$range)    $range       = 1;
	if(!$ordem)    $ordem       = 1;

//	if($BtnSearch) $inicial     = 0;
//	if($BtnSearch) $range       = 1;
//	if($BtnSearch) $n_rows = 0;
	if($BtnSearch=="Buscar") {
		$inicial     = 0;
		$range       = 1;
		$n_rows = 0;
	}
	$total_pedidos = 0;
	$n_pedidos = 0;
	$a_totais_por_mes = array();
	$a_stores_por_mes = array();
	$a_days_por_mes = array();


	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
	$img_anterior = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
	$max          = 100; //$qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;

	$varsel = "&BtnSearch=1";
	$varsel .= "&tf_data=$tf_data";
	$varsel .= "&tf_data_ini=$tf_data_ini&tf_data_fim=$tf_data_fim&tf_data_conf_ini=$tf_data_conf_ini&tf_data_conf_fim=$tf_data_conf_fim&tf_store_id=$tf_store_id";
	$varsel .= "&tf_cliente_email=$tf_cliente_email&tf_amount=$tf_amount";
	$varsel .= "&tf_d_forma_pagto=$tf_d_forma_pagto&tf_v_codigo=$tf_v_codigo";
	$varsel .= "&tf_d_forma_pagto=$tf_d_forma_pagto";
//echo "tf_cliente_email: '".$tf_cliente_email."'<br>";

	$sql_where = "";
//echo "tf_d_forma_pagto: '".$tf_d_forma_pagto."'<br>";
//echo "tf_v_codigo: '".$tf_v_codigo."'<br>";
//echo "tf_confirmed: '".$tf_confirmed."'<br>";
	if(!($tf_data_ini && $tf_data_fim)) {
		$tf_data_ini = date("d/m/Y");
		$tf_data_fim = date("d/m/Y");
		$sql_where .= "and ip.ip_data_inclusao between '".formata_data_ts_integracao($tf_data_ini)." 00:00:00' and '".formata_data_ts_integracao($tf_data_fim)." 23:59:59' ";
	}
//echo "tf_data_ini: '".$tf_data_ini."'<br>";
//echo "tf_data_fim: '".$tf_data_fim."'<br>";


	if(isset($BtnSearch)){
	
		//Validacao
		//------------------------------------------------------------------------------------------------------------------
		$msg = "";

		//------------------------------------------------------------------
		//Data
		if($msg == "")
			if($tf_data_ini || $tf_data_fim){
				if(verifica_data($tf_data_ini) == 0)	$msg = "A data de inclusão inicial do registro é inválida.\n";
				if(verifica_data($tf_data_fim) == 0)	$msg = "A data de inclusão final do registro é inválida.\n";
			}

		//------------------------------------------------------------------------------------------------------------------
		if($msg == ""){

			$sql_where = "";

//echo "$tf_data_ini - $tf_data_fim<br>";
			$filtro = array();
			if($tf_data_ini && $tf_data_fim) {
				$filtro['dataMin'] = $tf_data_ini;
				$filtro['dataMax'] = $tf_data_fim;
				$sql_where .= "and ip.ip_data_inclusao between '".formata_data_ts_integracao($filtro['dataMin'])." 00:00:00' and '".formata_data_ts_integracao($filtro['dataMax'])." 23:59:59' ";
			}
			if($tf_data_conf_ini && $tf_data_conf_fim) {
				$filtro['dataConfMin'] = $tf_data_conf_ini;
				$filtro['dataConfMax'] = $tf_data_conf_fim;
				$sql_where .= "and ip.ip_data_confirmed between '".formata_data_ts_integracao($filtro['dataConfMin'])." 00:00:00' and '".formata_data_ts_integracao($filtro['dataConfMax'])." 23:59:59' ";
			}

			if($tf_store_id) {
				$filtro['store_id'] = $tf_store_id;
				$sql_where .= "and ip.ip_store_id = '".$filtro['store_id']."' ";
			}

			if($tf_cliente_email) {
				$filtro['cliente_email'] = $tf_cliente_email;
				$sql_where .= "and ip.ip_client_email = '".$filtro['cliente_email']."' ";
			}

			if($tf_amount) {
				$filtro['amount'] = $tf_amount;
				$sql_where .= "and ip.ip_amount = ".(100*$filtro['amount'])." ";
			}
			if($tf_v_status) {
				$filtro['vg_status'] = $tf_v_status;
			}
			if($tf_d_forma_pagto) {
				$filtro['vg_forma_pagto'] = $tf_d_forma_pagto;
//echo "filtro['vg_forma_pagto']: '".$filtro['vg_forma_pagto']."'<br>";
			}
			if($tf_v_codigo) {
				$filtro['vg_id'] = $tf_v_codigo;
			}
			if($tf_confirmed) {
				$filtro['confirmed'] = $tf_confirmed;
			}
			if($tf_d_forma_pagto) {
				$filtro['vg_forma_pagto'] = $tf_d_forma_pagto;
//echo "filtro['vg_forma_pagto']: '".$filtro['vg_forma_pagto']."'<br>";
			}
			if($tf_parceiro_status) {
				$filtro['parceiro_status'] = $tf_parceiro_status;
			}
			$filtro['group_by_day'] = "1";

			$rs_pedidos = null;
			$ret = obter($filtro, $rs_pedidos);
			if($ret != "") $msg = $ret;
			else {
				$n_rows = pg_num_rows($rs_pedidos);

				if($n_rows == 0) {
					$msg = "Nenhum registro de integração encontrado.\n";
				} else {

					while($rs_pedidos_row = pg_fetch_array($rs_pedidos)){

						// Salva dados em array
						$ip_date = substr($rs_pedidos_row['ip_date'], 0, 10);
						$ip_store_id = $rs_pedidos_row['ip_store_id'];
						if(!in_array ($ip_store_id, $a_stores_por_mes)) {
							$a_stores_por_mes[] = $ip_store_id;
						}
						if(!in_array ($ip_date, $a_days_por_mes)) {
							$a_days_por_mes[] = $ip_date;
						}

						$a_totais_por_mes[$ip_date][$ip_store_id]['total']	= $rs_pedidos_row['total'];
						$a_totais_por_mes[$ip_date][$ip_store_id]['n']		= $rs_pedidos_row['n'];

						$total_pedidos += $rs_pedidos_row['total'];
						$n_pedidos += $rs_pedidos_row['n'];
					}
					// Calcula total para cada store_id / day
					$a_totais_por_store = array();
					$a_totais_por_day[$day] = array();
					$a_n_por_store[$store] = array();
					$a_n_por_day[$day] = array();

					foreach($a_days_por_mes as $key => $day) { 
						foreach($a_stores_por_mes as $key2 => $store) { 
							$a_totais_por_store[$store] += $a_totais_por_mes[$day][$store]['total'];
							$a_totais_por_day[$day] += $a_totais_por_mes[$day][$store]['total'];

							$a_n_por_store[$store] += $a_totais_por_mes[$day][$store]['n'];
							$a_n_por_day[$day] += $a_totais_por_mes[$day][$store]['n'];
						}
					}
				}
			}

			// Média diária
//						$n_days = ((count($a_days_por_mes))?count($a_days_por_mes):1);

//						$iday = date("d"); // or any value from 1-12
//						$imonth = date("n"); // or any value from 1-12
//						$iyear	= date("Y"); // or any value >= 1
//						$days_in_month = date("t",mktime(0,0,0,$imonth,1,$iyear));

			$date1 = substr($tf_data_ini, 6, 4)."-".substr($tf_data_ini, 3, 2)."-".substr($tf_data_ini, 0, 2);	//"24/03/2007"; -> "2007-03-24";
			$date2 = substr($tf_data_fim, 6, 4)."-".substr($tf_data_fim, 3, 2)."-".substr($tf_data_fim, 0, 2);	//"26/06/2007"; -> "2009-06-26";
/*
			$diff = abs(strtotime($date2) - strtotime($date1));
echo "diff: '$diff' (total: ".(365*60*60*24).")<br>";

			$years = floor($diff / (365*60*60*24));
			$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
			$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
			$n_days = $days+1;
//echo "$tf_data_ini - $tf_data_fim<br>";
//echo "$diff:  $days - $months - $years = $n_days<br>"; 

*/
//			$date1 = date('2011-12-01');
//			$date2 = date('2011-12-31');

			$date_diff = strtotime($date2) - strtotime($date1);
			$n_days = (int)($date_diff/(60 * 60 * 24))+1;	//( 60 * 60 * 24) // seconds into days

//echo "$date1 - $date2<br>";
//echo "$date_diff = $n_days<br>"; 

			$n_days = (($n_days)?$n_days:1);

			$iday = date("d"); // or any value from 1-12
			$imonth = date("n"); // or any value from 1-12
			$iyear	= date("Y"); // or any value >= 1
			$days_in_month = date("t",mktime(0,0,0,$imonth,1,$iyear));
			$days_in_month_prev = date("t",mktime(0,0,0,$imonth-1,1,$iyear));

/*
if(b_IsUsuarioReinaldo()) { 
echo "$date1 - $date2<br>";
echo "diff ($date_diff) = $n_days<br>"; 
echo "days_in_month: $days_in_month<br>";
echo "days_in_month_prev: $days_in_month_prev<br>";
}
*/
			// Para taxa de completos
//if(b_IsUsuarioReinaldo()) { 
			if($tf_confirmed=="2") {
				$filtro['group_by_day'] = "0";

				$rs_pedidos_full = null;
				$ret_full = obter($filtro, $rs_pedidos_full);
				if($ret_full != "") $msg_full = $ret_full;
				else {
					$n_rows_full = pg_num_rows($rs_pedidos_full);

					if($n_rows_full == 0) {
						$msg_full = "Nenhum registro de integração encontrado (FULL).\n";
					} else {
						while($rs_pedidos_full_row = pg_fetch_array($rs_pedidos_full)){
							// Salva dados em array
							$ip_store_id_full = $rs_pedidos_full_row['ip_store_id'];
							if(isset($a_stores_por_mes_full) && !in_array ($ip_store_id_full, $a_stores_por_mes_full)) {
								$a_stores_por_mes_full[] = $ip_store_id_full;
							}

							$a_totais_por_mes_full[$ip_store_id_full]['total']	= $rs_pedidos_full_row['total'];
							$a_totais_por_mes_full[$ip_store_id_full]['n']		= $rs_pedidos_full_row['n'];

							$total_pedidos_full += $rs_pedidos_full_row['total'];
							$n_pedidos_full += $rs_pedidos_full_row['n'];
						}
//echo "<pre>".print_r($a_totais_por_mes_full, true)."</pre>\n";
//echo "total_pedidos_full: $total_pedidos_full<br>";
//echo "n_pedidos_full: $n_pedidos_full<br>";
					}
				}
			}

//die("Stop");
//}

		}
	}
	
	//parceiros
	$sql  = "select distinct ip_store_id as parceiro, count(*) as n, ".getPartner_Names_SQL()." from tb_integracao_pedido group by ip_store_id order by opr_nome, ip_store_id;";
//echo "sql: $sql<br>";
	$rs_parceiros = SQLexecuteQuery($sql);
/*
	//Clientes
	$sql  = "select distinct ip_client_email as cliente, count(*) as n from tb_integracao_pedido ip where 1=1 ".$sql_where." group by ip_client_email order by ip_client_email;";
//echo "sql: $sql<br>";
	$rs_clientes = SQLexecuteQuery($sql);
	$n_clientes = pg_num_rows($rs_clientes);
*/

ob_end_flush();

?>
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>
<script language="javascript">
$(function(){
   var optDate = new Object();
        optDate.interval = 10000;

    setDateInterval('tf_data_ini','tf_data_fim',optDate);
    setDateInterval('tf_data_conf_ini','tf_data_conf_fim',optDate);
    
    

});

function open_notify_window(store_id, order_id) { 
	window.open('/gamer/integracao/com_integracao_notificacao_manual.php?store_id='+store_id+'&order_id='+order_id,'mywindow',
			'width=1000,height=500');
  
}

function GP_popupAlertMsg(msg) { //v1.0
  document.MM_returnValue = alert(msg);
}
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}

</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<table class="table txt-preto fontsize-pp">
  <tr> 
    <td>
		<form name="form1" method="post" action="com_pesquisa_integracao_day.php">
        <table class="table">
          <tr bgcolor="#F5F5FB"> 
            <td align="right"><input type="submit" name="BtnSearch" value="Buscar" class="btn btn-info btn-sm"></td>
          </tr>
		</table>
        <table class="table">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="6" bgcolor="#ECE9D8" class="texto">Pedidos de integração</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td class="texto">Data de Inclusão:</font></td>
            <td class="texto">
              <input name="tf_data_ini" type="text" class="form" id="tf_data_ini" value="<?php echo $tf_data_ini ?>" size="9" maxlength="10">
              a 
              <input name="tf_data_fim" type="text" class="form" id="tf_data_fim" value="<?php echo $tf_data_fim ?>" size="9" maxlength="10">
			</td>
            <td class="texto">Data de Confirmação:</font></td>
            <td class="texto">
              <input name="tf_data_conf_ini" type="text" class="form" id="tf_data_conf_ini" value="<?php echo $tf_data_conf_ini ?>" size="9" maxlength="10">
              a 
              <input name="tf_data_conf_fim" type="text" class="form" id="tf_data_conf_fim" value="<?php echo $tf_data_conf_fim ?>" size="9" maxlength="10">
			</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td class="texto">Número do Pedido:</font></td>
            <td class="texto">
				<input name="tf_v_codigo" type="text" class="form2" value="<?php echo $tf_v_codigo ?>" size="7" maxlength="7">			
			</td>
            <td class="texto">Confirmado:</font></td>
            <td class="texto">
				<select name="tf_confirmed" class="form2">
					<option value="" <?php if($tf_confirmed == "") echo "selected" ?>>Selecione</option>
					<option value="2" <?php if ($tf_confirmed == "2") echo "selected";?>>1 - Sim (Completo)</option>
					<option value="1" <?php if ($tf_confirmed == "1") echo "selected";?>>0 - Não (com venda)</option>
					<option value="-1" <?php if ($tf_confirmed == "-1") echo "selected";?>>0 - Não (sem venda)</option>
				</select>
			</td>
          </tr>

			<tr bgcolor="#F5F5FB"> 
				<td class="texto"><nobr>Forma de Pagamento:</nobr></font></td>
				<td colspan="3">
					<select name="tf_d_forma_pagto" class="form2">
						<option value="" <?php if($tf_d_forma_pagto == "") echo "selected" ?>>Selecione</option>
						<option value="X" <?php if($tf_d_forma_pagto == "X") echo "selected" ?>>Todas as formas de pagamento online</option>
						<option value="Y" <?php if($tf_d_forma_pagto == "Y") echo "selected" ?>>Depósito e Boleto (sem online)</option>
						<?php foreach ($FORMAS_PAGAMENTO_DESCRICAO as $formaId => $formaNome){ ?>
							<option value="<?php echo $formaId; ?>" <?php if ($tf_d_forma_pagto == $formaId) echo "selected";?>><?php echo $formaId . " - " . $formaNome; ?></option>
						<?php } ?>
					</select>
				</td>
			</tr>


          <tr bgcolor="#FFFFFF"> 
            <td colspan="4" bgcolor="#ECE9D8" class="texto">Parceiros</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="100" class="texto">Parceiros:</td>
            <td>
				<select name="tf_store_id" class="form2">
					<option value="" <?php if($tf_ip_store_id == "") echo "selected" ?>>Selecione</option>
					<?php if($rs_parceiros) while($rs_parceiros_row = pg_fetch_array($rs_parceiros)){ ?>					
					<option value="<?php echo $rs_parceiros_row['parceiro']; ?>" <?php if ($tf_store_id == $rs_parceiros_row['parceiro']) echo "selected";?>><?php echo getPartner_name_By_ID($rs_parceiros_row["parceiro"])." (ID: ".$rs_parceiros_row["parceiro"].") ".$rs_parceiros_row["n"]." registro".(($rs_parceiros_row["n"]>1)?"s":"")." "; ; ?></option>
					<?php } ?>
				</select>
			</td>
            <td width="100" class="texto">Email:</td>
			<td>
				<input name="tf_cliente_email" type="text" class="form2" value="<?php echo $tf_cliente_email ?>" size="40" maxlength="256">
			</td>
		  </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="100" class="texto">Status de Parceiro:</td>
            <td>
				<select name="tf_parceiro_status" class="form2">
					<option value="" <?php if($tf_parceiro_status == "") echo "selected" ?>>Selecione</option>
					<option value="A" <?php if($tf_parceiro_status == "A") echo "selected" ?>>Apenas os ativos</option>
					<option value="I" <?php if($tf_parceiro_status == "I") echo "selected" ?>>Apenas os inativos</option>
				</select>
			</td>
            <td width="100" class="texto">&nbsp;</td>
			<td>&nbsp;
			</td>
		  </tr>

          <tr bgcolor="#FFFFFF"> 
            <td colspan="4" bgcolor="#ECE9D8" class="texto">Venda</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="100" class="texto">Valores:</td>
            <td>
              <input name="tf_amount" type="text" class="form" id="tf_amount" value="<?php echo $tf_amount ?>" size="9" maxlength="10">
			</td>
            <td width="100" class="texto">Status Venda:</td>
			<td>
				<select name="tf_v_status" class="form2">
					<option value="" <?php if($tf_v_status == "") echo "selected" ?>>Selecione</option>
					<?php foreach ($GLOBALS['STATUS_VENDA_DESCRICAO'] as $statusId => $statusNome){ ?>
						<option value="<?php echo $statusId; ?>" <?php if ($tf_v_status == $statusId) echo "selected";?>><?php echo $statusId . " - " . substr($statusNome, 0, strpos($statusNome, '.')); ?></option>
					<?php } ?>
				</select>
			</td>
		  </tr>

		
		</table>

        <table class="table">
          <tr bgcolor="#F5F5FB"> 
            <td align="right"><input type="submit" name="BtnSearch" value="Buscar" class="btn btn-info btn-sm"></td> 
          </tr>
		</table>
		</form>
        </td>
    </tr>
</table>
<table class="txt-preto fontsize-pp">
    <tr>
        <td>
		<?php if($n_rows > 0) { ?>
        <table class="table">
                <tr> 
                  <td> 
				  	<table class="table">
				  	  <tr> 
						<td colspan="20" class="texto"> 
                          Exibindo resultados <strong><?php echo $inicial + 1 ?></strong> 
                          a <strong><?php echo $reg_ate ?></strong> de <strong><?php echo $n_rows ?></strong></font> 
                        </td>
					  </tr>
					  <?php $ordem = ($ordem == 1)?2:1; ?>
                      <tr  bgcolor="#ECE9D8"><td align="center" rowspan="2" colspan="2"><strong><font class="texto">Data</font></strong></td>
						<?php foreach($a_stores_por_mes as $key2 => $store) { ?>
							<td align="center" colspan="2"><strong><font class="texto"><?php echo "<nobr>".getPartner_name_By_ID($store)."<br>".$store."</nobr>" ?></font></strong></td>
						<?php } ?>
						<td align="center" colspan="2"><strong><font class="texto">Total</font></strong></td>
					  </tr>
                      <tr bgcolor="#ECE9D8">
						<?php foreach($a_stores_por_mes as $key2 => $store) { ?>
							<td align="center"><font class="texto">n</font></td>
							<td align="center"><font class="texto">R$</font></td>
						<?php } ?>
						<td align="center"><font class="texto">n</font></td>
						<td align="center"><font class="texto">R$</font></td>
					  </tr>
					<?php
						$cor_hover = "#CCFFCC";
						$cor1 = "#FFFFFF";
						$cor2 = "#FFFFFF";
						$cor3 = "#DEFEFE";

						foreach($a_days_por_mes as $key => $day) {
							$cor1 = ($cor1 == $cor2)?$cor3:$cor2;
							echo "<tr bgcolor='".$cor1 ."' valign='top' onmouseover=\"bgColor='#CFDAD7'\" onmouseout=\"bgColor='".$cor1 ."'\">\n";
							echo "<td class='texto'><nobr>$day</nobr></td>";
							echo "<td class='texto'><nobr>".get_day_of_week($day)."</nobr></td>";
							foreach($a_stores_por_mes as $key2 => $store) {

								$total = $a_totais_por_mes[$day][$store]['total'];
								if(!$total) $total = 0;
								$n = $a_totais_por_mes[$day][$store]['n'];
								if(!$n) $n = 0;
					?>
						<td class="texto" width="100" align="center"><nobr><?php echo $n ?></nobr></td>
						<td class="texto" width="100" align="center"><nobr><?php echo number_format($total, 2, ',', '.') ?></nobr></td>
					<?php 	
							}
						?>
							<td class="texto" align="center" style='color:blue'><nobr><?php echo $a_n_por_day[$day] ?></nobr></td>
							<td class="texto" align="center" style='color:blue'><nobr><?php echo number_format($a_totais_por_day[$day], 2, ',', '.') ?></nobr></td>
						<?php 	
							echo "</tr>\n";
						}

						// Totais
						echo "<tr bgcolor='#ECE9D8'>\n";
						echo "<td class='texto' colspan='2' style='color:blue'>Total</td>";
						$total = 0;
						$n = 0;
						foreach($a_stores_por_mes as $key2 => $store) {
							$total += $a_totais_por_store[$store];
							$n += $a_n_por_store[$store];

						?>
							<td class="texto" align="center" title="<?php 
								if($tf_confirmed=="2") {
									echo "Taxa de completos: ".number_format( (100.*$a_n_por_store[$store] / (($a_totais_por_mes_full[$store]['n']>0)?$a_totais_por_mes_full[$store]['n']:1) ), 2, ',', '.')."%\n sobre ".$a_totais_por_mes_full[$store]['n']."" ;
								}
							?>"><nobr><?php echo $a_n_por_store[$store] ?></nobr></td>
							<td class="texto" align="center" style='color:blue' title="<?php 
								if($tf_confirmed=="2") {
									echo "Taxa de completos: ".number_format( (100.*$a_totais_por_store[$store] / (($a_totais_por_mes_full[$store]['total']>0)?$a_totais_por_mes_full[$store]['total']:1) ), 2, ',', '.')."%\n sobre R$".number_format( $a_totais_por_mes_full[$store]['total'], 2, ',', '.')."\n" ;
								}
								echo "Proj. neste mês: R$".number_format(($a_totais_por_store[$store]*$days_in_month/$n_days), 2, ',', '.')."";
							?>"><nobr><?php echo number_format($a_totais_por_store[$store], 2, ',', '.') ?></nobr></td>
						<?php 	
						}
						?>
						<td class="texto" align="center" title="<?php 
								if($tf_confirmed=="2") {
									echo "Taxa de completos: ".number_format( (100.*$n / (($n_pedidos_full>0)?$n_pedidos_full:1) ), 2, ',', '.')."%\n sobre ".$n_pedidos_full."" ;
								}
							?>"><nobr><?php echo $n ?></nobr></td>
						<td class="texto" align="center" style='color:blue' title="<?php 
								if($tf_confirmed=="2") {
									echo "Taxa de completos: ".number_format( (100.*$total / (($total_pedidos_full>0)?$total_pedidos_full:1) ), 2, ',', '.')."%\n sobre R$".number_format( $total_pedidos_full, 2, ',', '.')."\n" ;
								}
								echo "Proj. neste mês: R$".number_format(($total*$days_in_month/$n_days), 2, ',', '.')."";
							?>"><nobr><?php echo number_format($total, 2, ',', '.') ?></nobr></td>
						<?php 	
						echo "</tr>";
						
//						if(b_IsUsuarioReinaldo()) 
							{
							// Percentagens
							echo "<tr bgcolor='#ECE9D8'>";
							echo "<td class='texto' colspan='2' style='color:blue'><nobr>Perc do total (%)</nobr></td>";
							$total = 0;
							$n = 0;
							foreach($a_stores_por_mes as $key2 => $store) {
//								echo "$key2 => '$store' T: ".$a_totais_por_store[$store]." (N: ".$a_n_por_store[$store].")<br>";
								$total += $a_totais_por_store[$store];
								$n += $a_n_por_store[$store];
							}

							foreach($a_stores_por_mes as $key2 => $store) {
							?>
								<td class="texto" align="center"><nobr><?php echo number_format(100.*$a_n_por_store[$store]/$n, 2, ',', '.') ?></nobr></td>
								<td class="texto" align="center" style='color:blue'><nobr><?php echo number_format(100.*$a_totais_por_store[$store]/$total, 2, ',', '.') ?></nobr></td>
							<?php 	
							}
							?>
							<td class="texto" align="center"><nobr><?php echo number_format(100.*$n/$n, 2, ',', '.') ?></nobr></td>
							<td class="texto" align="center" style='color:blue'><nobr><?php echo number_format(100.*$total/$total, 2, ',', '.') ?></nobr></td>
							<?php 	
							echo "</tr>";
						}


						echo "<tr bgcolor='#ECE9D8'>\n";
						echo "<td class='texto' colspan='2' style='color:blue' title='Em $n_days dias'>Média diária</td>";
						foreach($a_stores_por_mes as $key2 => $store) {
						?>
							<td class="texto" align="center"><nobr><?php echo number_format($a_n_por_store[$store]/$n_days, 2, ',', '.') ?></nobr></td>
							<td class="texto" align="center" style='color:blue'><nobr><?php echo number_format($a_totais_por_store[$store]/$n_days, 2, ',', '.') ?></nobr></td>
						<?php 	
						}
						?>
						<td class="texto" align="center"><nobr><?php echo number_format($n/$n_days, 2, ',', '.') ?></nobr></td>
						<td class="texto" align="center" style='color:blue'><nobr><?php echo number_format($total/$n_days, 2, ',', '.') ?></nobr></td>
						<?php 	
						echo "</tr>";

						// Venda média
						echo "<tr bgcolor='#ECE9D8'>";
						echo "<td class='texto' colspan='2' style='color:blue' title='Em $n_days dias'>Venda média</td>";
						foreach($a_stores_por_mes as $key2 => $store) {
						?>
							<td class="texto" align="center" style='color:blue'>&nbsp;</td>
							<td class="texto" align="center" style='color:blue'><nobr><?php echo number_format($a_totais_por_store[$store]/(($a_n_por_store[$store]>0)?$a_n_por_store[$store]:1), 2, ',', '.') ?></nobr></td>
						<?php 	
						}
						?>
						<td class="texto" align="center" style='color:blue'>&nbsp;</nobr></td>
						<td class="texto" align="center" style='color:blue'><nobr><?php echo number_format($total/(($n>0)?$n:1), 2, ',', '.') ?></nobr></td>
						<?php 	
						echo "</tr>";



					?>
                      <tr> 
                        <td colspan="20" bgcolor="#FFFFFF" class="texto"><?php echo $search_msg . number_format(getmicrotime() - $time_start, 2, ',', '.') . $search_unit ?></font></td>
                      </tr>
                    </table>
				  </td>
                </tr>
              </table>
          <?php  }  ?>
    </td>
  </tr>
</table>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</html>
<?php
	function obter($filtro, &$rs){

		$ret = "";
		$filtro = array_map("strtoupper", $filtro);

		$sql  = "select ";
		if($filtro['group_by_day'] == "1") {
			$sql .= "date_trunc('day', ip_data_inclusao) as ip_date, ";
		}
		$sql .= " ip_store_id, sum(ip_amount::float/100) as total, count(*) as n \n";
//		if(!is_null($filtro['confirmed'])) {
//			$sql .= ", coalesce(iph_ip_status_confirmed, 0) as iph_ip_status_confirmed_coalesced \n ";
//		}
		$sql .= "from tb_integracao_pedido ip \n";
//		$sql .= "	left outer join tb_pag_compras pc on ip.ip_vg_id = pc.idvenda ";
		$sql .= "	left outer join tb_venda_games vg on ip.ip_vg_id = vg.vg_id \n";

/*
		if(!is_null($filtro['confirmed'])) {
			$sql .= "	left outer join tb_integracao_pedido_historico iph 
					on 
					 (iph.iph_ip_id = ip.ip_id 
					and iph.iph_ip_store_id = ip.ip_store_id 
					and iph.iph_ip_order_id = ip.ip_order_id )
					";
					// "and iph.iph_ip_status_confirmed = 1 "
		}
*/
		if(!is_null($filtro) && $filtro != ""){
		
			if(!is_null($filtro['opr'])) {
			}

			if(!is_null($filtro['dataMin']) && !is_null($filtro['dataMax'])){
//echo "tf_data_ini: '".$filtro['dataMin']."' - tf_data_fim: '".$filtro['dataMax']."' <br>";
				$filtro['dataMin'] = formata_data_ts_integracao($filtro['dataMin']);
				$filtro['dataMax'] = formata_data_ts_integracao($filtro['dataMax']);
//echo "tf_data_ini: '".$filtro['dataMin']."' - tf_data_fim: '".$filtro['dataMax']."' <br>";
			}			
			if(!is_null($filtro['dataConfMin']) && !is_null($filtro['dataConfMax'])){
//echo "tf_data_conf_ini: '".$filtro['dataConfMin']."' - tf_data_fim: '".$filtro['dataConfMax']."' <br>";
				$filtro['dataConfMin'] = formata_data_ts_integracao($filtro['dataConfMin']);
				$filtro['dataConfMax'] = formata_data_ts_integracao($filtro['dataConfMax']);
//echo "tf_data_conf_ini: '".$filtro['dataConfMin']."' - tf_data_fim: '".$filtro['dataConfMax']."' <br>";
			}			

			$sql .= " where 1=1 \n";
//			$sql .= " and (not idvenda = 0) ";
			
//			$sql .= " and (" . (is_null($filtro['dataMin']) || is_null($filtro['dataMax'])?1:0);
//			$sql .= "=1 or ca.data between " . SQLaddFields($filtro['dataMin'], "") . " and " . SQLaddFields($filtro['dataMax'], "") . ")";

			if($filtro['dataMin'] && $filtro['dataMax']) {
				$sql .= " and (" . (is_null($filtro['dataMin']) || is_null($filtro['dataMax'])?1:0);
				$sql .= "=1 or ip.ip_data_inclusao between '".$filtro['dataMin']." 00:00:00' and '".$filtro['dataMax']." 23:59:59') \n";
			}
			if($filtro['dataConfMin'] && $filtro['dataConfMax']) {
				$sql .= " and (" . (is_null($filtro['dataConfMin']) || is_null($filtro['dataConfMax'])?1:0);
				$sql .= "=1 or ip.ip_data_confirmed between '".$filtro['dataConfMin']." 00:00:00' and '".$filtro['dataConfMax']." 23:59:59') \n";
			}

			$sql .= " and (" . (is_null($filtro['store_id'])?1:0);
			$sql .= "=1 or ip.ip_store_id = '" . SQLaddFields($filtro['store_id'], "") . "') \n";


//$sql_where .= "and ip.ip_client_email = '".$filtro['cliente_email']."' ";

			$sql .= " and (" . (is_null($filtro['cliente_email'])?1:0);
			$sql .= "=1 or upper(ip.ip_client_email) = '" . SQLaddFields(($filtro['cliente_email']), "") . "') \n";

			$sql .= " and (" . (is_null($filtro['amount'])?1:0);
			$sql .= "=1 or ip.ip_amount = '" . SQLaddFields(($filtro['amount']*100), "") . "') \n";


			if($filtro['vg_forma_pagto']) {
				if($filtro['vg_forma_pagto']=="X") {
//echo getListaCodigoNumericoParaPagtoOnline()."<br>";	// -> 5,6,7,9,10,13,11,12,999
//echo getListaCharacterParaPagtoOnline()."<br>";	//	-> '5','6','7','9','A','E','B','P','Z'

					$sql .= " and (" . (is_null($filtro['vg_forma_pagto'])?1:0);
					$sql .= "=1 or vg_pagto_tipo in (" . getListaCodigoNumericoParaPagtoOnline() . ") ) \n";
				} elseif($filtro['vg_forma_pagto']=="Y") {

					$sql .= " and (" . (is_null($filtro['vg_forma_pagto'])?1:0);
					$sql .= "=1 or vg_pagto_tipo in (".$GLOBALS['FORMAS_PAGAMENTO']['DEP_DOC_TRANSF'].", ".$GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO'].") ) \n";
				} else {
					$sql .= " and (" . (is_null($filtro['vg_forma_pagto'])?1:0);
					$sql .= "=1 or vg_pagto_tipo = " . getCodigoNumericoParaPagto($filtro['vg_forma_pagto']) . ") \n";
				}
			}

			$sql .= " and (" . (is_null($filtro['vg_status'])?1:0);
			$sql .= "=1 or vg.vg_ultimo_status = " . SQLaddFields($filtro['vg_status'], "") . ") \n";

			if($filtro['vg_id']) {
				$sql .= " and (" . (is_null($filtro['vg_id'])?1:0);
				$sql .= "=1 or (vg.vg_id = " . $filtro['vg_id'] . " or ip.ip_vg_id = '" . $filtro['vg_id'] . "') ) \n";
			}

			if($filtro['group_by_day'] == "1") {

				if(!is_null($filtro['confirmed'])) {

						$sql_subquery = " coalesce((	
											select iph_ip_status_confirmed 
											from tb_integracao_pedido_historico iph 
											where 1=1 
											and iph.iph_ip_id = ip.ip_id 
											and iph.iph_ip_store_id = ip.ip_store_id
											and iph.iph_ip_order_id = ip.ip_order_id
											and iph.iph_ip_vg_id = ip.ip_vg_id 
											and iph.iph_ip_status_confirmed = 1 
											limit 1
												), 0)  ";


						if($filtro['confirmed']==-1) {
							// -1 -> "0 - Não (sem venda)"
							$sql .= "and coalesce(vg_id, 0)=0 \n";
							// Não precisaria ver iph_ip_status_confirmed, se não tem vg_id não pode ter iph_ip_status_confirmed 
							//		mas testamos "just in case"
							$sql .= "and $sql_subquery = 0 \n";
						} elseif($filtro['confirmed']==1) {
							// 1 -> "0 - Não (com venda)"
							$sql .= "and coalesce(vg_id, 0)>0 \n";
							$sql .= "and $sql_subquery = 0 \n";
						} elseif($filtro['confirmed']==2) {
							// 2 -> "1 - Sim (Completo)"
							$sql .= "and coalesce(vg_id, 0)>0 \n";
							$sql .= "and $sql_subquery = 1 \n";
						}
				}
			}
			$sql .= " and (" . (is_null($filtro['client_id'])?1:0);
			$sql .= "=1 or ip.ip_client_id = " . SQLaddFields($filtro['client_id'], "") . ")  \n";

			if(($filtro['parceiro_status']=='A') || ($filtro['parceiro_status']=='I')) {
				$sql .= " and (" . (is_null($filtro['parceiro_status'])?1:0);
				$b_parceiro_status = (($filtro['parceiro_status']=='A')?true:(($filtro['parceiro_status']=='I')?false:false));
				$sql .= "=1 or ip.ip_store_id in (" . get_list_Partner_IDs($b_parceiro_status, true) . ") )  \n";
			}
		}

		$sql .= "group by ip_store_id";
		if($filtro['group_by_day'] == "1") {
			$sql .= ", date_trunc('day', ip_data_inclusao) ";
		}
		$sql .= "\n";
//		if(!is_null($filtro['confirmed'])) {
//			$sql .= ", coalesce(iph_ip_status_confirmed, 0) \n";
//		}
		$sql .= "order by ";
		if($filtro['group_by_day'] == "1") {
			$sql .= "ip_date, ";
		}
		$sql .= "ip_store_id \n";

//if(b_IsUsuarioReinaldo()) { 
//echo "(R) ".str_replace("\n", "<br>\n", $sql)."<br>\n";
//die("Stop");
//}

		$rs = SQLexecuteQuery($sql);
		if(!$rs) $ret = "Erro ao obter pedidos de integração(s).\n";

		return $ret;

	}
?>