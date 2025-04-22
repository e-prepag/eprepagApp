<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
require_once $raiz_do_projeto . "includes/pdv/corte_classPrincipal.php"; 
require_once "/www/includes/bourls.php";

	set_time_limit ( 30000 ) ; //	500mins
    
	$time_start = getmicrotime();
	
	if(!isset($ncamp) || !$ncamp)    $ncamp       = 'bpb_importacao';
	if(!isset($inicial) || !$inicial)  $inicial     = 0;
	if(!isset($range) || !$range)    $range       = 1;
	if(!isset($ordem) || !$ordem)    $ordem       = 1;
//	if($BtnSearch) $inicial     = 0;
//	if($BtnSearch) $range       = 1;
//	if($BtnSearch) $total_table = 0;
	if(isset($BtnSearch) && $BtnSearch=="Buscar") {
		$inicial     = 0;
		$range       = 1;
		$total_table = 0;
	}

//echo "qtde_reg_tela: $qtde_reg_tela<br>";
	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
	$img_anterior = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
	$max          = 100; //$qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;
    
    if(!isset($tf_b_data_inclusao_ini))
        $tf_b_data_inclusao_ini = null;
    
    if(!isset($tf_b_data_inclusao_fim))
        $tf_b_data_inclusao_fim = null;
    
    if(!isset($tf_b_data_concilia_ini))
        $tf_b_data_concilia_ini = null;
    
    if(!isset($tf_b_data_concilia_fim))
        $tf_b_data_concilia_fim = null;
    
    if(!isset($tf_b_data_cancelado_ini))
        $tf_b_data_cancelado_ini = null;
    
    if(!isset($tf_b_data_cancelado_fim))
        $tf_b_data_cancelado_fim = null;
    
    if(!isset($tf_b_valor))
        $tf_b_valor = null;
    
    if(!isset($tf_b_codigo))
        $tf_b_codigo = null;
    
    if(!isset($tf_b_status))
        $tf_b_status = null;
    
    if(!isset($tf_b_linha))
        $tf_b_linha = null;
    
    if(!isset($tf_b_bol_banco))
        $tf_b_bol_banco = null;
    
	$varsel = "&BtnSearch=1";
	$varsel .= "&tf_b_data_inclusao_ini=$tf_b_data_inclusao_ini&tf_b_data_inclusao_fim=$tf_b_data_inclusao_fim";
	$varsel .= "&tf_b_data_concilia_ini=$tf_b_data_concilia_ini&tf_b_data_concilia_fim=$tf_b_data_concilia_fim";
	$varsel .= "&tf_b_data_cancelado_ini=$tf_b_data_cancelado_ini&tf_b_data_cancelado_fim=$tf_b_data_cancelado_fim";
	$varsel .= "&tf_b_valor=$tf_b_valor&tf_b_codigo=$tf_b_codigo&tf_b_status=$tf_b_status&tf_b_linha=$tf_b_linha";
	$varsel .= "&tf_b_bol_banco=$tf_b_bol_banco";

	if(isset($BtnSearch)){
	
		//Validacao
		//------------------------------------------------------------------------------------------------------------------
		//$msg = "";

		//Usuario
		//------------------------------------------------------------------
		//codigo
		if($msg == "")
			if($tf_ug_id){
				if(!is_numeric($tf_ug_id)) $msg = "Código do usuário deve ser numérico.\n";
			}

		//Boleto
		//------------------------------------------------------------------
		//codigo
		if($msg == "")
			if($tf_b_codigo){
				if(!is_numeric($tf_b_codigo)) $msg = "Código do boleto deve ser numérico.\n";
			}
		//Data
		if($msg == "")
			if($tf_b_data_inclusao_ini || $tf_b_data_inclusao_fim){
				if(verifica_data($tf_b_data_inclusao_ini) == 0)	$msg = "A data inicial de inclusão do boleto é inválida.\n";
				if(verifica_data($tf_b_data_inclusao_fim) == 0)	$msg = "A data final de inclusão do boleto é inválida.\n";
			}
		//Data venc
		if($msg == "")
			if($tf_b_data_venc_ini || $tf_b_venc_inclusao_fim){
				if(verifica_data($tf_b_data_venc_ini) == 0)	$msg = "A data inicial de vencimento do boleto é inválida.\n";
				if(verifica_data($tf_b_data_venc_fim) == 0)	$msg = "A data final de vencimento do boleto é inválida.\n";
			}
		//Data Conciliacao
		if($msg == "")
			if($tf_b_data_concilia_ini || $tf_b_data_concilia_fim){
				if(!is_DateTime($tf_b_data_concilia_ini))	$msg = "A data inicial da conciliação do boleto é inválida.\n";
				if(!is_DateTime($tf_b_data_concilia_fim))	$msg = "A data final da conciliação do boleto é inválida.\n";
			}
		//Data Cancelado
		if($msg == "")
			if($tf_b_data_cancelado_ini || $tf_b_data_cancelado_fim){
				if(!is_DateTime($tf_b_data_cancelado_ini))	$msg = "A data inicial de cancelamento do boleto é inválida.\n";
				if(!is_DateTime($tf_b_data_cancelado_fim))	$msg = "A data final da cancelamento do boleto é inválida.\n";
			}
		//valor
		if($msg == "")
			if($tf_b_valor){
				if(!is_moeda($tf_b_valor)) $msg = "Valor da venda é inválido.\n";
			}

		//banco
		if($msg == "")
			if($tf_b_bol_banco){
				if(!($tf_b_bol_banco=="" || $tf_b_bol_banco=="237" || $tf_b_bol_banco=="341")) $msg = "Código do banco é inválido.\n";
			}

		//Busca boletos
		//------------------------------------------------------------------------------------------------------------------
		if($msg == ""){
			// bpb.bpb_dataocorr, bpb_datacredito, bpb_importacao, bbc_data_inclusao, bbc_data_venc, bbc_data_concilia, bpb_valortitulo, bpb_valorpago
			$sql = "select bpb_codigo, bpb_valortitulo, bpb_importacao, bpb_importacao, 
						bpb_identtitulo, bpb_bol_banco, coalesce(bbc_status, 0) as bbc_status, bbc_data_venc, bbc_data_concilia, bbc_data_cancelado,  
						ug.ug_id, ug.ug_tipo_cadastro, ug.ug_nome_fantasia, ug.ug_nome
					from boletos_pendentes_bradesco bpb 
						left outer join boleto_bancario_cortes bbc on bbc.bbc_bpb_codigo =bpb.bpb_codigo 
						left outer join dist_usuarios_games ug on ug.ug_id = bbc.bbc_ug_id
					where substr(bpb_identtitulo, 1, 1)='1'	";
						// and bbc_status = ".$GLOBALS['CORTE_STATUS']['CONCILIADO']."

			if($tf_ug_id) 				$sql .= " and ug.ug_id = ".$tf_ug_id." ";
			if($tf_nome) 				$sql .= " and (ug.ug_nome LIKE '%".strtoupper($tf_nome)."%' or ug.ug_nome_fantasia LIKE '%".strtoupper($tf_nome)."%') ";
			if($tf_b_codigo) 			$sql .= " and bbc.bbc_boleto_codigo = ".$tf_b_codigo." ";
			if($tf_b_status) 			$sql .= " and bbc.bbc_status = ".$tf_b_status." ";
			if($tf_b_data_inclusao_ini && $tf_b_data_inclusao_fim) $sql .= " and bpb.bpb_importacao between '".formata_data($tf_b_data_inclusao_ini,1)." 00:00:00' and '".formata_data($tf_b_data_inclusao_fim,1)." 23:59:59'";
			if($tf_b_data_venc_ini && $tf_b_data_venc_fim) $sql .= " and bbc.bbc_data_venc between '".formata_data($tf_b_data_venc_ini,1)." 00:00:00' and '".formata_data($tf_b_data_venc_fim,1)." 23:59:59'";
			if($tf_b_data_concilia_ini && $tf_b_data_concilia_fim) $sql .= " and bbc.bbc_data_concilia between '".formata_data_ts($tf_b_data_concilia_ini, 2, true, false)."' and '".formata_data_ts($tf_b_data_concilia_fim, 2, true, false)."' ";
			if($tf_b_data_cancelado_ini && $tf_b_data_cancelado_fim) $sql .= " and bbc.bbc_data_cancelado between '".formata_data_ts($tf_b_data_cancelado_ini, 2, true, false)."' and '".formata_data_ts($tf_b_data_cancelado_fim, 2, true, false)."' ";
			if($tf_b_valor) 			$sql .= " and bpb.bpb_valortitulo = ".moeda2numeric($tf_b_valor)." ";
			if($tf_b_bol_banco) 		$sql .= " and bpb.bpb_bol_banco = '$tf_b_bol_banco' ";
			
			$rs_boletos = SQLexecuteQuery($sql);
			$total_table = pg_num_rows($rs_boletos);
//echo $sql . "<br>";

			$bol_valor_total_i=0;
			while($u=pg_fetch_array($rs_boletos))
				$bol_valor_total_i+=$u['bpb_valortitulo'];

			//Ordem
			$sql .= " order by ".$ncamp;
			if($ordem == 1){
				$sql .= " desc ";
				$img_seta = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/seta_down.gif";
			} else {
				$sql .= " asc ";
				$img_seta = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/seta_up.gif";
			}
			$sql .= " limit ".$max; 
			$sql .= " offset ".$inicial;
//echo $sql . "<br>";

			if($total_table == 0) $msg = "Nenhum boleto encontrado.\n";
			else {		
				$rs_boletos = SQLexecuteQuery($sql);
				
				if($max + $inicial > $total_table) $reg_ate = $total_table;
				else $reg_ate = $max + $inicial;
				
				$ordem = ($ordem == 1)?2:1;
			}
		}
	}
    
    if(!isset($msgUsuario))
        $msgUsuario = null;
    
    if(!isset($msg))
        $msg = null;
    
	$msg = $msgUsuario . $msg;

	//Obtem os bancos disponiveis (apenas Bradesco e Itau, se usar bco-codigo com '237', '341' aparece o Bradesco duas vezes)
	$sql = "select * from bancos_financeiros where bco_id in (87, 88) ";
	$resbco = pg_exec($connid, $sql);
	
ob_end_flush();
?>
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>
<script language="javascript">
function GP_popupAlertMsg(msg) { //v1.0
  document.MM_returnValue = alert(msg);
}
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}

$(function(){
    var optDate = new Object();
        optDate.interval = 10000;

    setDateInterval('tf_b_data_inclusao_ini','tf_b_data_inclusao_fim',optDate);
    setDateInterval('tf_b_data_venc_ini','tf_b_data_venc_fim',optDate);
});
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="2">BackOffice - Lan Houses</a></li>
        <li><a href="index.php">Corte Semanal</a></li>
        <li class="active">Corte Semanal - Pesquisa de Boletos Pendentes (Remessas do banco)</li>
    </ol> 
</div>
<table class="table txt-preto fontsize-p">
  <tr> 
    <td width="891" valign="top"> 
        <form name="form1" method="post" action="corte_pesquisa_boletos_pendentes.php">		
            <table class="table">

          <tr bgcolor="#FFFFFF"> 
            <td colspan="4" bgcolor="#ECE9D8" class="texto">Boleto</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="100" class="texto">Banco</font></td>
            <td>
				<select name="tf_b_bol_banco" class="form2">
					<option value="" <?php if(isset($bco_id) && $bco_id == "") echo "selected" ?>>Selecione</option>
					<?php while($pgbco = pg_fetch_array($resbco)) { ?>
					<option value="<?php echo $pgbco['bco_codigo'] ?>" <?php if($pgbco['bco_codigo'] == $tf_b_bol_banco) echo "selected" ?>><?php echo $pgbco['bco_codigo'] . " - " . $pgbco['bco_nome'] ?></option>
					<?php } ?>

				</select>
			</td>
            <td class="texto">&nbsp;</td>
            <td class="texto">&nbsp;</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="100" class="texto">Código</font></td>
            <td>
              	<input name="tf_b_codigo" type="text" class="form2" value="<?php echo $tf_b_codigo ?>" size="7" maxlength="7">
			</td>
            <td class="texto">Status</font></td>
			<td>
				<select name="tf_b_status" class="form2">
					<option value="" <?php if($tf_b_status == "") echo "selected" ?>>Selecione</option>
					<?php foreach ($CORTE_BOLETO_STATUS_DESCRICAO as $statusId => $statusNome){ ?>
					<option value="<?php echo $statusId; ?>" <?php if ($tf_b_status == $statusId) echo "selected";?>><?php echo $statusId . " - " . substr($statusNome, 0, strpos($statusNome, '.')); ?></option>
					<?php } ?>
				</select>
			</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td class="texto">Valor</font></td>
            <td>
              	<input name="tf_b_valor" type="text" class="form2" value="<?php echo $tf_b_valor ?>" size="7" maxlength="7">
			</td>
            <td class="texto">Data Inclusão</font></td>
            <td class="texto">
              <input name="tf_b_data_inclusao_ini" type="text" class="form" id="tf_b_data_inclusao_ini" value="<?php echo $tf_b_data_inclusao_ini ?>" size="9" maxlength="10">
              a 
              <input name="tf_b_data_inclusao_fim" type="text" class="form" id="tf_b_data_inclusao_fim" value="<?php echo $tf_b_data_inclusao_fim ?>" size="9" maxlength="10">
			</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td class="texto">Linha Digitável</font></td>
            <td>
              	<input name="tf_b_linha" type="text" class="form2" value="<?php echo $tf_b_linha ?>" size="25" maxlength="70">
			</td>
            <td class="texto">Data Vencimento</font></td>
            <td class="texto">
              <input name="tf_b_data_venc_ini" type="text" class="form" id="tf_b_data_venc_ini" value="<?php if(isset($tf_b_data_venc_ini)) echo $tf_b_data_venc_ini ?>" size="9" maxlength="10">
              a 
              <input name="tf_b_data_venc_fim" type="text" class="form" id="tf_b_data_venc_fim" value="<?php if(isset($tf_b_data_venc_fim)) echo $tf_b_data_venc_fim ?>" size="9" maxlength="10">
			</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td class="texto">Data de Cancelamento</font></td>
            <td class="texto">
              <input name="tf_b_data_cancelado_ini" type="text" class="form" id="tf_b_data_cancelado_ini" value="<?php if(isset($tf_b_data_cancelado_ini)) echo $tf_b_data_cancelado_ini ?>" size="15" maxlength="16">
              a 
              <input name="tf_b_data_cancelado_fim" type="text" class="form" id="tf_b_data_cancelado_fim" value="<?php if(isset($tf_b_data_cancelado_fim)) echo $tf_b_data_cancelado_fim ?>" size="15" maxlength="16">
				<br>Formato: DD/MM/AAAA hh:mm
			</td>
            <td class="texto">Data Conciliação</font></td>
            <td class="texto">
              <input name="tf_b_data_concilia_ini" type="text" class="form" id="tf_b_data_concilia_ini" value="<?php if(isset($tf_b_data_concilia_ini)) echo $tf_b_data_concilia_ini ?>" size="15" maxlength="16">
              a 
              <input name="tf_b_data_concilia_fim" type="text" class="form" id="tf_b_data_concilia_fim" value="<?php if(isset($tf_b_data_concilia_fim)) echo $tf_b_data_concilia_fim ?>" size="15" maxlength="16">
				Formato: DD/MM/AAAA hh:mm
			</td>
          </tr>

		</table>

        <table width="894" border="0" cellpadding="0" cellspacing="2">
          <tr bgcolor="#F5F5FB"> 
            <td align="right"><input type="submit" name="BtnSearch" value="Buscar" class="btn btn-sm btn-info"></td>
          </tr>
          <?php if($msg != ""){?><tr class="texto"><td align="center"><br><br><font color="#FF0000"><?php echo $msg?></font></td></tr><?php }?>
		</table>
		</form>

		<?php if(isset($total_table) && $total_table > 0) { ?>
        <table width="894" border="0" cellpadding="0" cellspacing="2">
                <tr bgcolor="#00008C"> 
                  <td height="11" colspan="3" bgcolor="#FFFFFF"> 
				  	<table width="100%" border='0' cellpadding="2" cellspacing="1">
				  	  <tr> 
						<td colspan="20" class="texto"> 
                          Exibindo resultados <strong><?php echo $inicial + 1 ?></strong> 
                          a <strong><?php echo $reg_ate ?></strong> de <strong><?php echo $total_table ?></strong></font> 
                        </td>
					  </tr>
                      <tr  bgcolor="#ECE9D8"> 
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_id&inicial=".$inicial.$varsel ?>" class="link_branco" ><font class="texto">C&oacute;digo</a> 
                          <?php if($ncamp == 'ug_id') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_tipo_cadastro&inicial=".$inicial.$varsel ?>" class="link_branco" ><font class="texto">Tipo de<br>Cadastro</a> 
                          <?php if($ncamp == 'ug_tipo_cadastro') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><font class="texto">Nome/Nome Fantasia</a></strong></td>
                        
						<td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=bpb_bol_banco&inicial=".$inicial.$varsel ?>" class="link_branco" ><font class="texto">Banco</a> 
                          <?php if($ncamp == 'bpb_bol_banco') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
						<td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=bbc_boleto_codigo&inicial=".$inicial.$varsel ?>" class="link_branco" ><font class="texto">C&oacute;d.<br>Boleto</a> 
                          <?php if($ncamp == 'bbc_boleto_codigo') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><font class="texto">Doc. Num.</a></strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=bbc_data_inclusao&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Data<br>Inclusão</font></a>
                          <?php if($ncamp == 'bbc_data_inclusao') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=bbc_data_venc&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Data<br>Vencimento</font></a>
                          <?php if($ncamp == 'bbc_data_venc') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=bbc_data_concilia&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Data<br>Conciliação</font></a>
                          <?php if($ncamp == 'bbc_data_concilia') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=bpb_valortitulo&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Valor</font></a>
                          <?php if($ncamp == 'bpb_valortitulo') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=bbc_status&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Status</font></a>
                          <?php if($ncamp == 'bbc_status') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                      </tr>
					<?php
						$cor1 = $query_cor1;
						$cor2 = $query_cor1;
						$cor3 = $query_cor2;
						$bol_valor_total = 0;

						while($rs_boletos_row = pg_fetch_array($rs_boletos)){
							$cor1 = ($cor1 == $cor2)?$cor3:$cor2;
							$status = $rs_boletos_row['bbc_status'];
							$statusNome = $GLOBALS['CORTE_BOLETO_STATUS_DESCRICAO'][$status];
							$statusNome = substr($statusNome, 0, strpos($statusNome, '.'));
							if($rs_boletos_row['ug_tipo_cadastro'] == 'PF') $nome = $rs_boletos_row['ug_nome'];
							else $nome = $rs_boletos_row['ug_nome_fantasia'];

							$bol_valor_total += $rs_boletos_row['bpb_valortitulo'];

					?>
                      <tr bgcolor="<?php echo $cor1 ?>" class="texto"> 
						<td align="center"><?php echo $rs_boletos_row['ug_id'] ?></td>
				        <td align="center"><?php echo $rs_boletos_row['ug_tipo_cadastro'] ?></td>
				        <td><a style="text-decoration:none" href="/pdv/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $rs_boletos_row['ug_id'] ?>" title="Ir para Usuário"><?php echo $nome ?></a></td>

                        <td align="center"><?php echo $rs_boletos_row['bpb_bol_banco'] ?></td>
                        <td align="center"><?php echo $rs_boletos_row['bpb_codigo'] ?></td>
				        <td align="center"><?php echo $rs_boletos_row['bpb_identtitulo'] ?></td>
                        <td align="center"><?php echo formata_timestamp($rs_boletos_row['bpb_importacao'], 0) ?></td>
                        <td align="center"><?php echo formata_data($rs_boletos_row['bbc_data_venc'], 0) ?></td>
                        <td align="center"><?php if($rs_boletos_row['bbc_data_concilia']) echo formata_timestamp($rs_boletos_row['bbc_data_concilia'], 0) ?></td>
                        <td align="right"><?php echo number_format($rs_boletos_row['bpb_valortitulo'], 2, ',','.') ?></td>
                        <td><?php echo $statusNome ?></td>
                      </tr>
					<?php 	}	?>
                      <tr>
						<td colspan="7" bgcolor="#FFFFFF" class="texto">&nbsp;</font></td>
						<td bgcolor="#FFFFFF" class="texto" align="right"><strong>SUBTOTAL</strong></font></td>
						<td bgcolor="#FFFFFF" class="texto" align="right"><strong><?php echo number_format($bol_valor_total, 2, ',', '.') ?></strong></font></td>
						<td colspan="1" bgcolor="#FFFFFF" class="texto">&nbsp;</font></td>
					  </tr>
                      <tr>
						<td colspan="7" bgcolor="#FFFFFF" class="texto">&nbsp;</font></td>
						<td bgcolor="#FFFFFF" class="texto" align="right"><strong>TOTAL</strong></font></td>
						<td bgcolor="#FFFFFF" class="texto" align="right"><strong><?php echo number_format($bol_valor_total_i, 2, ',', '.') ?></strong></font></td>
						<td colspan="1" bgcolor="#FFFFFF" class="texto">&nbsp;</font></td>
					  </tr>
                      <tr> 
                        <td colspan="12" bgcolor="#FFFFFF" class="texto"><?php echo $search_msg . number_format(getmicrotime() - $time_start, 2, '.', '.') . $search_unit ?></font></td>
                      </tr>
					<?php paginacao_query($inicial, $total_table, $max, 20, $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel); ?>
                    </table>
				  </td>
                </tr>
              </table>
          <?php  }  ?>
    </td>
  </tr>
</table>
<?php require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php"; ?>
</html>
