<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
require_once $raiz_do_projeto . "includes/pdv/corte_classPrincipal.php"; 
require_once "/www/includes/bourls.php";
    set_time_limit(300); // 5min

	$time_start = getmicrotime();
	
	if(!isset($ncamp) || !$ncamp)    $ncamp       = 'cor_codigo';
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

	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
	$img_anterior = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
	$max          = 200;	//$qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;
    
    if(!isset($tf_v_codigo))
        $tf_v_codigo = null;
    
    if(!isset($tf_v_status))
        $tf_v_status = null;
    
    if(!isset($tf_v_data_concilia_ini))
        $tf_v_data_concilia_ini = null;
    
    if(!isset($tf_v_data_concilia_fim))
        $tf_v_data_concilia_fim = null;
    
    if(!isset($tf_v_boleto_codigo))
        $tf_v_boleto_codigo = null;
    
    if(!isset($tf_v_periodo_ini))
        $tf_v_periodo_ini = null;
    
    if(!isset($tf_v_periodo_fim))
        $tf_v_periodo_fim = null;
    
    if(!isset($tf_u_codigo))
        $tf_u_codigo = null;
    
    if(!isset($tf_u_nome))
        $tf_u_nome = null;
    
    if(!isset($tf_u_nome_fantasia))
        $tf_u_nome_fantasia = null;
    
    if(!isset($tf_u_cnpj))
        $tf_u_cnpj = null;
    
    if(!isset($tf_u_rg))
        $tf_u_rg = null;
    
    if(!isset($tf_u_cpf))
        $tf_u_cpf = null;

	$varsel = "&BtnSearch=1&tf_v_codigo=$tf_v_codigo&tf_v_status=$tf_v_status";
	$varsel .= "&tf_v_data_concilia_ini=$tf_v_data_concilia_ini&tf_v_data_concilia_fim=$tf_v_data_concilia_fim";
	$varsel .= "&tf_v_boleto_codigo=$tf_v_boleto_codigo";
	$varsel .= "&tf_v_periodo_ini=$tf_v_periodo_ini&tf_v_periodo_fim=$tf_v_periodo_fim";
	$varsel .= "&tf_u_codigo=$tf_u_codigo&tf_u_nome=$tf_u_nome&tf_u_nome_fantasia=$tf_u_nome_fantasia";
	$varsel .= "&tf_u_cnpj=$tf_u_cnpj&tf_u_rg=$tf_u_rg&tf_u_cpf=$tf_u_cpf";

	if(isset($BtnSearch)){
	
		//Validacao
		//------------------------------------------------------------------------------------------------------------------
		$msg = "";

		//Venda
		//------------------------------------------------------------------
		//codigo
		if($msg == "")
			if($tf_v_codigo){
				if(!is_numeric($tf_v_codigo))
					$msg = "Código do corte deve ser numérico.\n";
			}
		//Data Conciliacao
		if($msg == "")
			if($tf_v_data_concilia_ini || $tf_v_data_concilia_fim){
				if(!is_DateTime($tf_v_data_concilia_ini))	$msg = "A data inicial da conciliação do corte é inválida.\n";
				if(!is_DateTime($tf_v_data_concilia_fim))	$msg = "A data final da conciliação do corte é inválida.\n";
			}
		//codigo boleto
		if($msg == "")
			if($tf_v_boleto_codigo){
				if(!is_numeric($tf_v_boleto_codigo))
					$msg = "Código do boleto deve ser numérico.\n";
			}
		//Periodo
		if($msg == "")
			if($tf_v_periodo_ini || $tf_v_periodo_fim){
				if(verifica_data($tf_v_periodo_ini) == 0)	$msg = "A data inicial do período do corte é inválida.\n";
				if(verifica_data($tf_v_periodo_fim) == 0)	$msg = "A data final do período do corte é inválida.\n";
			}

		//Usuario
		//------------------------------------------------------------------
		//tf_u_codigo
		if($msg == "")
			if($tf_u_codigo){
			
				if(!is_numeric($tf_u_codigo))
					$msg = "Código do usuário deve ser numérico.\n";
			}

		//Busca vendas
		//------------------------------------------------------------------------------------------------------------------
		if($msg == ""){
			$sql  = "select *
					 from cortes cor
					 inner join dist_usuarios_games ug on ug.ug_id = cor.cor_ug_id
					 left join boleto_bancario_cortes bbc on bbc.bbc_boleto_codigo = cor.cor_bbc_boleto_codigo
		 			 where 1=1 ";
			if($tf_v_codigo) 			$sql .= " and cor.cor_codigo = '".$tf_v_codigo."' ";
			if($tf_v_status) 			$sql .= " and cor.cor_status = '".$tf_v_status."' ";
			if($tf_v_data_concilia_ini && $tf_v_data_concilia_fim) $sql .= " and cor.cor_data_concilia between '".formata_timestamp($tf_v_data_concilia_ini . ":00", 3)."' and '".formata_timestamp($tf_v_data_concilia_fim . ":00", 3)."' ";
			if($tf_v_boleto_codigo) 	$sql .= " and cor.cor_bbc_boleto_codigo = '".$tf_v_boleto_codigo."' ";
			if($tf_v_periodo_ini && $tf_v_periodo_fim) $sql .= " and cor.cor_periodo_ini >= '".formata_data($tf_v_periodo_ini,1)."' and cor.cor_periodo_fim <= '".formata_data($tf_v_periodo_fim,1)."'";
			
			if($tf_u_codigo) 			$sql .= " and ug.ug_id = '".$tf_u_codigo."' ";
			if($tf_u_nome_fantasia) 	$sql .= " and upper(ug.ug_nome_fantasia) like '%".strtoupper($tf_u_nome_fantasia)."%' ";
			if($tf_u_nome) 				$sql .= " and upper(ug.ug_nome) like '%" . strtoupper($tf_u_nome) . "%' ";
			if($tf_u_cnpj) 				$sql .= " and ug.ug_cnpj like '%".$tf_u_cnpj."%' ";
			if($tf_u_cpf) 				$sql .= " and ug.ug_cpf like '%" . $tf_u_cpf . "%' ";
			if($tf_u_rg) 				$sql .= " and ug.ug_rg like '%" . $tf_u_rg . "%' ";
		
			$rs_cortes = SQLexecuteQuery($sql);
			$total_table = pg_num_rows($rs_cortes);

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
//echo $sql;
			if($total_table == 0) {
				$msg = "Nenhum corte encontrado.\n";
			} else {		
				if($max + $inicial > $total_table) $reg_ate = $total_table;
				else $reg_ate = $max + $inicial;

                if(!isset($cor_venda_qtde_geral))
                    $cor_venda_qtde_geral = 0;
                
                if(!isset($cor_venda_bruta_geral))
                    $cor_venda_bruta_geral = 0;
                
                if(!isset($cor_venda_comissao_geral))
                    $cor_venda_comissao_geral = 0;
                
                if(!isset($cor_venda_liquida_geral))
                    $cor_venda_liquida_geral = 0;
                
				while($rs_cortes_row = pg_fetch_array($rs_cortes)){
					//total geral
					$cor_venda_qtde_geral 		+= $rs_cortes_row['cor_venda_qtde'];
					$cor_venda_bruta_geral 		+= $rs_cortes_row['cor_venda_bruta'];
					$cor_venda_comissao_geral 	+= $rs_cortes_row['cor_venda_comissao'];
					$cor_venda_liquida_geral 	+= $rs_cortes_row['cor_venda_liquida'];
				}
                
				$rs_cortes = SQLexecuteQuery($sql);
			}
				
		}
	}
	
	
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

    setDateInterval('tf_v_periodo_ini','tf_v_periodo_fim',optDate);
});
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="2">BackOffice - Lan Houses</a></li>
        <li><a href="index.php">Corte Semanal</a></li>
        <li class="active">Corte Semanal - Pesquisa de Cortes</li>
    </ol> 
</div>
<table class="table fontsize-p txt-preto">
  <tr> 
    <td width="891" valign="top"> 
        <form name="form1" method="post" action="corte_pesquisa.php">		
        <table class="table">
          <tr bgcolor="#F5F5FB"> 
            <td align="right"><input type="submit" name="BtnSearch" value="Buscar" class="btn btn-sm btn-info"></td>
          </tr>
		</table>
            <table class="table">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="6" bgcolor="#ECE9D8" class="texto">Corte</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="100" class="texto">Código</font></td>
            <td>
              	<input name="tf_v_codigo" type="text" class="form2" value="<?php echo $tf_v_codigo ?>" size="7" maxlength="7">
			</td>
            <td class="texto">Status</font></td>
			<td>
				<select name="tf_v_status" class="form2">
					<option value="" <?php if($tf_v_status == "") echo "selected" ?>>Selecione</option>
					<?php foreach ($CORTE_STATUS_DESCRICAO as $statusId => $statusNome){ ?>
					<option value="<?php echo $statusId; ?>" <?php if ($tf_v_status == $statusId) echo "selected";?>><?php echo $statusId . " - " . substr($statusNome, 0, strpos($statusNome, '.')); ?></option>
					<?php } ?>
				</select>
			</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td class="texto">Cód. Boleto</font></td>
			<td>
				<input name="tf_v_boleto_codigo" type="text" class="form2" value="<?php echo $tf_v_boleto_codigo ?>" size="7" maxlength="7">
			</td>
            <td class="texto">Periodo</font></td>
            <td class="texto">
              <input name="tf_v_periodo_ini" type="text" class="form" id="tf_v_periodo_ini" value="<?php echo $tf_v_periodo_ini ?>" size="9" maxlength="10">
              a 
              <input name="tf_v_periodo_fim" type="text" class="form" id="tf_v_periodo_fim" value="<?php echo $tf_v_periodo_fim ?>" size="9" maxlength="10">
			</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td class="texto"></font></td>
            <td>
			</td>
            <td class="texto">Data Conciliação</font></td>
            <td class="texto">
              <input name="tf_v_data_concilia_ini" type="text" class="form" id="tf_v_data_concilia_ini" value="<?php echo $tf_v_data_concilia_ini ?>" size="15" maxlength="16">
              a 
              <input name="tf_v_data_concilia_fim" type="text" class="form" id="tf_v_data_concilia_fim" value="<?php echo $tf_v_data_concilia_fim ?>" size="15" maxlength="16">
				Formato: DD/MM/AAAA hh:mm
			</td>
          </tr>

          <tr bgcolor="#FFFFFF"> 
            <td colspan="4" bgcolor="#ECE9D8" class="texto">Usuário</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td class="texto">C&oacute;digo</font></td>
            <td>
              	<input name="tf_u_codigo" type="text" class="form2" value="<?php echo $tf_u_codigo ?>" size="7" maxlength="7">
			</td>
            <td class="texto">Nome Fantasia</font></td>
            <td>
              	<input name="tf_u_nome_fantasia" type="text" class="form2" value="<?php echo $tf_u_nome_fantasia ?>" size="25" maxlength="100">
			</td>
		  </tr>
          <tr bgcolor="#F5F5FB"> 
            <td class="texto">CNPJ</font></td>
            <td>
              	<input name="tf_u_cnpj" type="text" class="form2" value="<?php echo $tf_u_cnpj ?>" size="25" maxlength="14">
			</td>
            <td class="texto">Nome</font></td>
            <td>
              	<input name="tf_u_nome" type="text" class="form2" value="<?php echo $tf_u_nome ?>" size="25" maxlength="100">
			</td>
		  </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td>CPF</font></td>
            <td>
              	<input name="tf_u_cpf" type="text" class="form2" value="<?php echo $tf_u_cpf ?>" size="25" maxlength="14">
			</td>
            <td>RG</font></td>
            <td>
              	<input name="tf_u_rg" type="text" class="form2" value="<?php echo $tf_u_rg ?>" size="25" maxlength="14">
			</td>
		  </tr>
		</table>

            <table class="table">
          <tr bgcolor="#F5F5FB"> 
            <td align="right"><input type="submit" name="BtnSearch" value="Buscar" class="btn btn-sm btn-info"></td>
          </tr>
          <?php if(isset($msg) && $msg != ""){?><tr class="texto"><td align="center"><br><br><font color="#FF0000"><?php echo $msg?></font></td></tr><?php }?>
		</table>
		</form>


		<?php if(isset($total_table) && $total_table > 0) { ?>
		<table class="table fontsize-pp">
	  	  <tr> 
			<td colspan="20" class="texto"> 
              Exibindo resultados <strong><?php echo $inicial + 1 ?></strong> a <strong><?php echo $reg_ate ?></strong> 
              de <strong><?php echo $total_table ?></strong> 
            </td>
		  </tr>
		  <?php $ordem = ($ordem == 1)?2:1; ?>
		  <tr bgcolor="#ECE9D8"> 
			<td align="center" valign="top"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=cor_ug_id&inicial=".$inicial.$varsel ?>" class="link_branco" ><font class="texto">Cód<br>Usuário</font></a> 
          		<?php if($ncamp == 'cor_ug_id') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
          	</strong></td>
			<td align="center" valign="top"><b>Nome/Fantasia</b></td>
			<td align="center" valign="top"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=cor_codigo&inicial=".$inicial.$varsel ?>" class="link_branco" ><font class="texto">Cód<br>Corte</font></a> 
          		<?php if($ncamp == 'cor_codigo') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
          	</strong></td>
			<td align="center" valign="top"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=cor_periodo_ini&inicial=".$inicial.$varsel ?>" class="link_branco" ><font class="texto">Período de Apuração</font></a> 
          		<?php if($ncamp == 'cor_periodo_ini') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
          	</strong></td>
			<td align="center" valign="top"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=cor_venda_qtde&inicial=".$inicial.$varsel ?>" class="link_branco" ><font class="texto">Qtde<br>Vendas</font></a> 
          		<?php if($ncamp == 'cor_venda_qtde') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
          	</strong></td>
			<td align="center" valign="top"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=cor_venda_bruta&inicial=".$inicial.$varsel ?>" class="link_branco" ><font class="texto">Venda<br>Bruta</font></a> 
          		<?php if($ncamp == 'cor_venda_bruta') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
          	</strong></td>
			<td align="center" valign="top"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=cor_venda_comissao&inicial=".$inicial.$varsel ?>" class="link_branco" ><font class="texto">Comissão</font></a> 
          		<?php if($ncamp == 'cor_venda_comissao') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
          	</strong></td>
			<td align="center" valign="top"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=cor_venda_liquida&inicial=".$inicial.$varsel ?>" class="link_branco" ><font class="texto">Venda<br>Líquida</font></a> 
          		<?php if($ncamp == 'cor_venda_liquida') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
          	</strong></td>
			<td align="center" valign="top"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=cor_status&inicial=".$inicial.$varsel ?>" class="link_branco" ><font class="texto">Status</font></a> 
          		<?php if($ncamp == 'cor_status') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
          	</strong></td>
			<td align="center" valign="top"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=cor_tipo_pagto&inicial=".$inicial.$varsel ?>" class="link_branco" ><font class="texto">Tipo Pagto</font></a> 
          		<?php if($ncamp == 'cor_tipo_pagto') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
          	</strong></td>
			<td align="center" valign="top"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=cor_bbc_boleto_codigo&inicial=".$inicial.$varsel ?>" class="link_branco" ><font class="texto">Cód<br>Boleto</font></a> 
          		<?php if($ncamp == 'cor_bbc_boleto_codigo') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
          	</strong></td>
			<td align="center" valign="top"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=bbc_data_venc&inicial=".$inicial.$varsel ?>" class="link_branco" ><font class="texto">Boleto<br>Data Venc</font></a> 
          		<?php if($ncamp == 'bbc_data_venc') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
          	</strong></td>
		  </tr>
<?php
$cor1 = "#FFFFFF";
$cor2 = "#FFFFFF";
$cor3 = "#F5F5Fb";

			$cor_venda_qtde_total 		= 0;
			$cor_venda_bruta_total 		= 0;
			$cor_venda_comissao_total 	= 0;
			$cor_venda_liquida_total 	= 0;
			if($rs_cortes){
			while($rs_cortes_row = pg_fetch_array($rs_cortes)){
				$cor1 = ($cor1 == $cor2 ? $cor3 : $cor2);
				$cor_status = $rs_cortes_row['cor_status'];
				$cor_status_descricao = $GLOBALS['CORTE_STATUS_DESCRICAO'][$rs_cortes_row['cor_status']];
				$cor_tipo_pagto = $rs_cortes_row['cor_tipo_pagto'];
				
				//total
				$cor_venda_qtde_total 		+= $rs_cortes_row['cor_venda_qtde'];
				$cor_venda_bruta_total 		+= $rs_cortes_row['cor_venda_bruta'];
				$cor_venda_comissao_total 	+= $rs_cortes_row['cor_venda_comissao'];
				$cor_venda_liquida_total 	+= $rs_cortes_row['cor_venda_liquida'];
?>
		  <tr class="texto" bgcolor="<?php echo $cor1 ?>"> 
			<td align="center"><a class="link_br" href="/pdv/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $rs_cortes_row['cor_ug_id'] ?>"><font class="texto"><?php echo $rs_cortes_row['cor_ug_id'] ?></font></a></td>
		<?php if(strtoupper($rs_cortes_row['ug_tipo_cadastro']) == 'PF'){?>
			<td align="left"><?php echo $rs_cortes_row['ug_nome'] ?></font></td>
		<?php } else {?>	
			<td align="left"><?php echo $rs_cortes_row['ug_nome_fantasia'] ?></font></td>
		<?php }?>	
			<td align="center"><?php echo $rs_cortes_row['cor_codigo'] ?></font></td>
			<td align="center" nowrap><?php echo formata_data($rs_cortes_row['cor_periodo_ini'], 0) ?> a <?php echo formata_data($rs_cortes_row['cor_periodo_fim'], 0) ?></td>
			<td align="right"><?php echo $rs_cortes_row['cor_venda_qtde'] ?> </font></td>
			<td align="right"><?php echo number_format ($rs_cortes_row['cor_venda_bruta'], 2, ',', '.') ?></td>
			<td align="right"><?php echo number_format ($rs_cortes_row['cor_venda_comissao'], 2, ',', '.') ?></td>
			<td align="right"><?php echo number_format ($rs_cortes_row['cor_venda_liquida'], 2, ',', '.') ?></td>
			<td align="center" nowrap><?php echo substr($cor_status_descricao, 0, strpos($cor_status_descricao, ".")) ?></td>
			<td align="center" nowrap>
				<?php if($cor_tipo_pagto == $GLOBALS['CORTE_FORMAS_PAGAMENTO']['BOLETO_BANCARIO']){?>
					<?php if($rs_cortes_row['cor_bbc_boleto_codigo']){?>
					<a href="corte_boleto.php?bbc_boleto_codigo=<?php echo $rs_cortes_row['cor_bbc_boleto_codigo'] ?>" class="link_br" target="_blank">
					<font class="texto"><?php echo $GLOBALS['CORTE_FORMAS_PAGAMENTO_DESCRICAO'][$cor_tipo_pagto] ?></font>
					</a>
					<?php }else{ ?><?php echo $GLOBALS['CORTE_FORMAS_PAGAMENTO_DESCRICAO'][$cor_tipo_pagto] ?><?php } ?>
				<?php }?>
			</td>
			<td align="center">
				<?php if($rs_cortes_row['cor_bbc_boleto_codigo']){?>
				<a href="corte_boleto_detalhe.php?bbc_id=<?php echo $rs_cortes_row['cor_bbc_boleto_codigo'] ?>" class="link_br">
				<font class="texto"><?php echo $rs_cortes_row['cor_bbc_boleto_codigo'] ?></font>
				</a>
				<?php } ?>
			</td>
			<td align="center" nowrap><?php if($rs_cortes_row['bbc_data_venc']) echo formata_data($rs_cortes_row['bbc_data_venc'], 0) ?></td>
		  </tr>
        <?php }} ?>

          <tr bgcolor="E5E5EB"> 
            <td align="right" colspan="4"><b>Total:</b></td>
			<td align="right"><?php echo number_format($cor_venda_qtde_total, 0, '', '.') ?></td>
			<td align="right"><?php echo number_format($cor_venda_bruta_total, 2, ',', '.') ?></td>
			<td align="right"><?php echo number_format($cor_venda_comissao_total, 2, ',', '.') ?></td>
			<td align="right"><?php echo number_format($cor_venda_liquida_total, 2, ',', '.') ?></td>
            <td align="right" colspan="4"></td>
          </tr>
          <tr bgcolor="D5D5DB"> 
            <td align="right" colspan="4"><b>Total Geral:</b></td>
			<td align="right"><?php echo number_format($cor_venda_qtde_geral, 0, '', '.') ?></td>
			<td align="right"><?php echo number_format($cor_venda_bruta_geral, 2, ',', '.') ?></td>
			<td align="right"><?php echo number_format($cor_venda_comissao_geral, 2, ',', '.') ?></td>
			<td align="right"><?php echo number_format($cor_venda_liquida_geral, 2, ',', '.') ?></td>
            <td align="right" colspan="5"></td>
          </tr>
		<?php paginacao_query($inicial, $total_table, $max, 20, $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel); ?>


	</table>
	<?php  }  ?>

    </td>
  </tr>
</table>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</html>
