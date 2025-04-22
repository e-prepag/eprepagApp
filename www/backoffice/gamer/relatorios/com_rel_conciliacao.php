<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/main.php";
require_once $raiz_do_projeto."public_html/sys/includes/language/eprepag_lang_pt.inc.php";

	$varsel = "&BtnSearch=1&tf_v_codigo=$tf_v_codigo&tf_v_status=$tf_v_status";
	$varsel .= "&tf_v_data_inclusao_ini=$tf_v_data_inclusao_ini&tf_v_data_inclusao_fim=$tf_v_data_inclusao_fim";
	$varsel .= "&tf_v_concilia=$tf_v_concilia&tf_d_forma_pagto=$tf_d_forma_pagto&tf_d_banco=$tf_d_banco&tf_d_local=$tf_d_local";
	$varsel .= "&tf_d_data_ini=$tf_d_data_ini&tf_d_data_fim=$tf_d_data_fim";
	$varsel .= "&tf_d_data_inclusao_ini=$tf_d_data_inclusao_ini&tf_d_data_inclusao_fim=$tf_d_data_inclusao_fim";
	$varsel .= "&tf_d_valor_pago=$tf_d_valor_pago&tf_d_num_docto=$tf_d_num_docto";
	$varsel .= "&tf_u_codigo=$tf_u_codigo&tf_u_nome=$tf_u_nome&tf_u_email=$tf_u_email&tf_u_cpf=$tf_u_cpf";
//	$varsel .= "&tf_v_valor=$tf_v_valor&tf_v_qtde_produtos=$tf_v_qtde_produtos&tf_v_qtde_itens=$tf_v_qtde_itens";

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
					$msg = "Código da venda deve ser numérico.\n";
			}
		//Data
		if($msg == "")
			if($tf_v_data_inclusao_ini || $tf_v_data_inclusao_fim){
				if(verifica_data($tf_v_data_inclusao_ini) == 0)	$msg = "A data inicial da venda é inválida.\n";
				if(verifica_data($tf_v_data_inclusao_fim) == 0)	$msg = "A data final da venda é inválida.\n";
			}
		//valor
		if($msg == "")
			if($tf_v_valor){

				if(!is_moeda($tf_v_valor))
					$msg = "Valor da venda é inválido.\n";
			}
		//Data concilia
		if($msg == "")
			if(!$tf_v_data_concilia_ini) $msg = "A data de conciliação inicial da venda deve ser preenchida.\n";
			else if(!$tf_v_data_concilia_fim) $msg = "A data de conciliação inicial da venda deve ser preenchida.\n";
			else {
				if(verifica_data($tf_v_data_concilia_ini) == 0)	$msg = "A data de conciliação inicial da venda é inválida.\n";
				if(verifica_data($tf_v_data_concilia_fim) == 0)	$msg = "A data de conciliação final da venda é inválida.\n";
			}
/*
		//qtde produtos
		if($msg == "")
			if($tf_v_qtde_produtos){
			
				if(!is_numeric($tf_v_qtde_produtos))
					$msg = "Qtde Produtos da venda deve ser numérico.\n";
			}
		//qtde itens
		if($msg == "")
			if($tf_v_qtde_itens){
			
				if(!is_numeric($tf_v_qtde_itens))
					$msg = "Qtde Itens da venda deve ser numérico.\n";
			}
*/

		//Dados do Pagamento
		//------------------------------------------------------------------
		//Data
		if($msg == "")
			if($tf_d_data_ini || $tf_d_data_fim){
				if(verifica_data($tf_d_data_ini) == 0)	$msg = "A data inicial dos dados do pagamento é inválida.\n";
				if(verifica_data($tf_d_data_fim) == 0)	$msg = "A data final dos dados do pagamento é inválida.\n";
			}
			
		//Data inclusao
		if($msg == "")
			if($tf_d_data_inclusao_ini || $tf_d_data_inclusao_fim){
				if(verifica_data($tf_d_data_inclusao_ini) == 0)	$msg = "A data de inclusão inicial dos dados do pagamento é inválida.\n";
				if(verifica_data($tf_d_data_inclusao_fim) == 0)	$msg = "A data de inclusão final dos dados do pagamento é inválida.\n";
			}
		//valor pago
		if($msg == "")
			if($tf_d_valor_pago){

				if(!is_moeda($tf_d_valor_pago))
					$msg = "Valor Pago dos dados do pagamento é inválido.\n";
			}
/*
		//tf_d_num_docto
		if($msg == "")
			if($tf_d_num_docto){
			
				if(!is_numeric($tf_d_num_docto))
					$msg = "Numero do Documento deve ser numérico.\n";
			}
*/
		//Usuario
		//------------------------------------------------------------------
		//tf_u_codigo
		if($msg == "")
			if($tf_u_codigo){
			
				if(!is_numeric($tf_u_codigo))
					$msg = "Código do usuário deve ser numérico.\n";
			}
	
		//Agrupar
		$sql_group = "";
		if($ag_u_codigo) 			$sql_group .= ", vg.vg_ug_id ";
		if($ag_d_forma_pagto) 		$sql_group .= ", vg.vg_pagto_tipo ";
		if($ag_user_id_concilia)	$sql_group .= ", vg.vg_user_id_concilia ";

		//Busca vendas
		//------------------------------------------------------------------------------------------------------------------
		if($msg == ""){
			$sql  = "select date_trunc('day', vg_data_concilia) as vg_data_concilia_aux, count(*) as qtde 
					 $sql_group
					 from tb_venda_games vg 
					 inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id 
 					 where 1=1 and vg.vg_concilia = 1 ";
			if($tf_v_codigo) 			$sql .= " and vg.vg_id = '".$tf_v_codigo."' ";
			if($tf_v_status) 			$sql .= " and vg.vg_ultimo_status = '".$tf_v_status."' ";
			if($tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim) $sql .= " and vg.vg_data_inclusao between '".formata_data($tf_v_data_inclusao_ini,1)." 00:00:00' and '".formata_data($tf_v_data_inclusao_fim,1)." 23:59:59'";
			if($tf_v_data_concilia_ini && $tf_v_data_concilia_fim) $sql .= " and vg.vg_data_concilia between '".formata_data($tf_v_data_concilia_ini,1)." 00:00:00' and '".formata_data($tf_v_data_concilia_fim,1)." 23:59:59'";
			if(!is_null($tf_v_concilia) && $tf_v_concilia != "")$sql .= " and vg.vg_concilia = '".$tf_v_concilia."' ";
			if($tf_d_forma_pagto) 		$sql .= " and vg.vg_pagto_tipo = '".$tf_d_forma_pagto."' ";
			if($tf_d_banco) 			$sql .= " and vg.vg_pagto_banco = '".$tf_d_banco."' ";
			if($tf_d_local) 			$sql .= " and vg.vg_pagto_local = '".$tf_d_local."' ";
			if($tf_d_data_ini && $tf_d_data_fim) $sql .= " and vg.vg_pagto_data between '".formata_data($tf_d_data_ini,1)." 00:00:00' and '".formata_data($tf_d_data_fim,1)." 23:59:59'";
			if($tf_d_data_inclusao_ini && $tf_d_data_inclusao_fim) $sql .= " and vg.vg_pagto_data_inclusao between '".formata_data($tf_d_data_inclusao_ini,1)." 00:00:00' and '".formata_data($tf_d_data_inclusao_fim,1)." 23:59:59'";
			if($tf_d_valor_pago) 		$sql .= " and vg.vg_pagto_valor_pago = ".moeda2numeric($tf_d_valor_pago)." ";
			if($tf_d_num_docto) 		$sql .= " and upper(vg.vg_pagto_num_docto) like '%". strtoupper($tf_d_num_docto)."%' ";
			if($tf_u_codigo) 			$sql .= " and ug.ug_id = '".$tf_u_codigo."' ";
			if($tf_u_nome) 				$sql .= " and upper(ug.ug_nome) like '%".strtoupper($tf_u_nome)."%' ";
			if($tf_u_email) 			$sql .= " and upper(ug.ug_email) like '%".strtoupper($tf_u_email)."%' ";
			if($tf_u_cpf) 				$sql .= " and ug.ug_cpf like '%".$tf_u_cpf."%' ";

			$sql .= " group by vg_data_concilia_aux $sql_group ";
			$sql .= " order by vg_data_concilia_aux desc, qtde desc $sql_group ";
                       
			$rs_venda = SQLexecuteQuery($sql);
			if(!$rs_venda) $msg = "Nenhuma venda encontrada.\n";
//echo $sql;

		}
	}
ob_end_flush();
require_once "/www/includes/bourls.php";
?>
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>
<script language="javascript">
    $(function(){
        var optDate = new Object();
            optDate.interval = 10000;

        setDateInterval('tf_v_data_concilia_ini','tf_v_data_concilia_fim',optDate);
        setDateInterval('tf_v_data_inclusao_ini','tf_v_data_inclusao_fim',optDate);
        setDateInterval('tf_d_data_ini','tf_d_data_fim',optDate);
        setDateInterval('tf_d_data_inclusao_ini','tf_d_data_inclusao_fim',optDate);
    });
    
function GP_popupAlertMsg(msg) { //v1.0
  document.MM_returnValue = alert(msg);
}
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
</script>
<script>
	function populate_local(combo1, combo2, combo2_default){

		opcoesAr = new Array(new Array("","Selecione"));

	<?php foreach ($PAGTO_BANCOS as $bancoId => $bancoNome){ ?>
		if(combo1[combo1.selectedIndex].value == "<?php echo $bancoId ?>"){ 
		<?php 
                if(isset($PAGTO_LOCAIS[$bancoId])){
                    foreach ($PAGTO_LOCAIS[$bancoId] as $localId => $localNome){ 
                ?>
			opcoesAr[opcoesAr.length] = new Array("<?=$localId?>","<?=$localNome?>");
                <?php }} ?>
		}
	<?php } ?>
		
		//limpa combo
		for(var i=combo2.length-1; i>=0; i--) combo2.options[i] = null;
		//popula combo
		for(var i=0; i<opcoesAr.length; i++) combo2.options[i] = new Option(opcoesAr[i][1], opcoesAr[i][0]);
		//seleciona opcao
		for(var i=combo2.length-1; i>=0; i--) if(combo2[i].value == combo2_default) combo2.selectedIndex = i;
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
    <td> <form name="form1" method="post" action="com_rel_conciliacao.php">
        <table class="table txt-preto fontsize-pp">
          <tr bgcolor="#F5F5FB"> 
            <td align="right"><input type="submit" name="BtnSearch" value="Buscar" class="btn btn-sm btn-info"></td>
          </tr>
		</table>
        <table class="table txt-preto fontsize-pp">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="6" bgcolor="#ECE9D8" class="texto">Venda</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="100" class="texto">Número do Pedido</font></td>
            <td>
              	<input name="tf_v_codigo" type="text" class="form2" value="<?php echo $tf_v_codigo ?>" size="7" maxlength="7">
			</td>
            <td class="texto">Status</font></td>
			<td>
				<select name="tf_v_status" class="form2">
					<option value="" <?php if($tf_v_status == "") echo "selected" ?>>Selecione</option>
					<?php foreach ($STATUS_VENDA_DESCRICAO as $statusId => $statusNome){ ?>
					<option value="<?php echo $statusId; ?>" <?php if ($tf_v_status == $statusId) echo "selected";?>><?php echo $statusId . " - " . substr($statusNome, 0, strpos($statusNome, '.')); ?></option>
					<?php } ?>
				</select>
			</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td class="texto">Data de Conciliação</font></td>
            <td class="texto">
              <input name="tf_v_data_concilia_ini" type="text" class="form" id="tf_v_data_concilia_ini" value="<?php echo $tf_v_data_concilia_ini ?>" size="9" maxlength="10">
              a 
              <input name="tf_v_data_concilia_fim" type="text" class="form" id="tf_v_data_concilia_fim" value="<?php echo $tf_v_data_concilia_fim ?>" size="9" maxlength="10">
			</td>
            <td class="texto">Data Inclusão</font></td>
            <td class="texto">
              <input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php echo $tf_v_data_inclusao_ini ?>" size="9" maxlength="10">
              a 
              <input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php echo $tf_v_data_inclusao_fim ?>" size="9" maxlength="10">
			</td>
          </tr>
          <tr bgcolor="#FFFFFF"> 
            <td colspan="4" bgcolor="#ECE9D8" class="texto">Dados do Pagamento</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="100" class="texto">Forma de Pagamento</td>
            <td colspan="3">
				<select name="tf_d_forma_pagto" class="form2">
					<option value="" <?php if($tf_d_forma_pagto == "") echo "selected" ?>>Selecione</option>
					<?php foreach ($FORMAS_PAGAMENTO_DESCRICAO_NUMERICO as $formaId => $formaNome){ ?>
					<option value="<?php echo $formaId; ?>" <?php if ($tf_d_forma_pagto == $formaId) echo "selected";?>><?php echo $formaId . " - " . $formaNome; ?></option>
					<?php } ?>
				</select>
			</td>
		  </tr>
		  <tr bgcolor="#F5F5FB">
            <td width="100" class="texto">Banco</td>
            <td colspan="3">
				<select name="tf_d_banco" class="form2" onchange="populate_local(document.form1.tf_d_banco, document.form1.tf_d_local, '');">
					<option value="" <?php if($tf_d_banco == "") echo "selected" ?>>Selecione</option>
					<?php foreach ($PAGTO_BANCOS as $bancoId => $bancoNome){ ?>
					<option value="<?php echo $bancoId; ?>" <?php if ($tf_d_banco == $bancoId) echo "selected";?>><?php echo $bancoNome; ?></option>
					<?php } ?>
				</select>
			</td>
		  </tr>
		  <tr bgcolor="#F5F5FB">
            <td width="100" class="texto">Local</td>
            <td colspan="3">
				<select name="tf_d_local" class="form2"></select>
			</td>
		  </tr>

<script>populate_local(document.form1.tf_d_banco, document.form1.tf_d_local, "<?=$tf_d_local?>");</script>
		  <tr bgcolor="#F5F5FB">
            <td class="texto">N. Docto</font></td>
            <td>
              	<input name="tf_d_num_docto" type="text" class="form2" value="<?php echo $tf_d_num_docto ?>" size="25" maxlength="15">
			</td>
            <td class="texto">Data Informada</font></td>
            <td class="texto">
              <input name="tf_d_data_ini" type="text" class="form" id="tf_d_data_ini" value="<?php echo $tf_d_data_ini ?>" size="9" maxlength="10">
              a 
              <input name="tf_d_data_fim" type="text" class="form" id="tf_d_data_fim" value="<?php echo $tf_d_data_fim ?>" size="9" maxlength="10">
			</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td class="texto">Valor Pago</font></td>
            <td>
              	<input name="tf_d_valor_pago" type="text" class="form2" value="<?php echo $tf_d_valor_pago ?>" size="7" maxlength="7">
			</td>
            <td class="texto">Data Inclusão</font></td>
            <td class="texto">
              <input name="tf_d_data_inclusao_ini" type="text" class="form" id="tf_d_data_inclusao_ini" value="<?php echo $tf_d_data_inclusao_ini ?>" size="9" maxlength="10">
              a 
              <input name="tf_d_data_inclusao_fim" type="text" class="form" id="tf_d_data_inclusao_fim" value="<?php echo $tf_d_data_inclusao_fim ?>" size="9" maxlength="10">
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
            <td class="texto">Nome</font></td>
            <td>
              	<input name="tf_u_nome" type="text" class="form2" value="<?php echo $tf_u_nome ?>" size="25" maxlength="100">
			</td>
		  </tr>
          <tr bgcolor="#F5F5FB"> 
            <td class="texto">Email</font></td>
            <td>
              	<input name="tf_u_email" type="text" class="form2" value="<?php echo $tf_u_email ?>" size="25" maxlength="100">
			</td>
            <td class="texto">CPF</font></td>
            <td>
              	<input name="tf_u_cpf" type="text" class="form2" value="<?php echo $tf_u_cpf ?>" size="25" maxlength="14">
			</td>
		  </tr>

          <tr bgcolor="#FFFFFF"> 
            <td colspan="4" bgcolor="#ECE9D8" class="texto">Agrupar por</font></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td colspan="4" class="texto">
              	<input name="ag_user_id_concilia" type="checkbox" value="vg_user_id_concilia" <?php if($ag_user_id_concilia == "vg_user_id_concilia") echo "checked" ?>>Conciliador
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
              	<input name="ag_d_forma_pagto" type="checkbox" value="vg_pagto_tipo" <?php if($ag_d_forma_pagto == "vg_pagto_tipo") echo "checked" ?>>Forma de Pagamento
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
              	<input name="ag_u_codigo" type="checkbox" value="vg_ug_id" <?php if($ag_u_codigo == "vg_ug_id") echo "checked" ?>>Usuário
			</td>
		  </tr>
		  
		</table>

        <table class="table txt-preto fontsize-pp">
          <tr bgcolor="#F5F5FB"> 
            <td align="right"><input type="submit" name="BtnSearch" value="Buscar" class="btn btn-sm btn-info"></td>
          </tr>
          <?php if($msg != ""){?><tr class="texto"><td align="center"><br><br><font color="#FF0000"><?php echo $msg?></font></td></tr><?php }?>
		</table>
		</form>

		<?php if($rs_venda && pg_num_rows($rs_venda) > 0) { ?>
        <table class="table txt-preto fontsize-pp">
                <tr bgcolor="#00008C"> 
                    <td height="11" colspan="3" class="text-center" bgcolor="#FFFFFF"> 
				  	<table width="300px" border='0' cellpadding="2" cellspacing="1">
					<?php
						$cor1 = $query_cor1;
						$cor2 = $query_cor1;
						$cor3 = $query_cor2;
						while($rs_venda_row = pg_fetch_array($rs_venda)){
							$cor1 = ($cor1 == $cor2)?$cor3:$cor2;

							$vg_ug_id = $rs_venda_row['vg_ug_id'];
							$vg_pagto_tipo = $rs_venda_row['vg_pagto_tipo'];
							$vg_user_id_concilia = $rs_venda_row['vg_user_id_concilia'];

							$qtde = $rs_venda_row['qtde'];
							$vg_data_concilia_aux = $rs_venda_row['vg_data_concilia_aux'];
							if($vg_data_concilia_aux != $vg_data_concilia_aux2){
?>
                      <tr  bgcolor="#ECE9D8"> 
                        <td align="center" colspan="4">
							<font class="texto"><strong>Data de conciliação: </strong><?php echo formata_data_ts($rs_venda_row['vg_data_concilia_aux'], 0, false, false) ?> (<?php echo get_day_of_week($rs_venda_row['vg_data_concilia_aux'])?>)</font>
						</td>
					  </tr>
                      <tr  bgcolor="#ECE9D8"> 
						<?php if($ag_user_id_concilia){ ?><td><strong><font class="texto">&nbsp;Conciliador</font></strong></td><?php } ?>
						<?php if($ag_d_forma_pagto){ ?><td><strong><font class="texto">&nbsp;Forma de Pagamento</font></strong></td><?php } ?>
						<?php if($ag_u_codigo){ ?><td><strong><font class="texto">&nbsp;Usuário</font></strong></td><?php } ?>
                        <td align="center"><strong><font class="texto">Qtde</font></strong></td>
                      </tr>
<?php
							}
							$vg_data_concilia_aux2 = $vg_data_concilia_aux;

							
							if($ag_user_id_concilia){
								$shn_nome = "Anonymous";
								if($vg_user_id_concilia && trim($vg_user_id_concilia) != ""){
									$sql  = "select * from usuarios urpp where urpp.id = '" . $vg_user_id_concilia . "'";
									$rs_urpp = SQLexecuteQuery($sql);
									if($rs_urpp && pg_num_rows($rs_urpp) == 1){
										$rs_urpp_row = pg_fetch_array($rs_urpp);
										$shn_nome = $rs_urpp_row['shn_nome'];
									}
								}
							}
							if($ag_d_forma_pagto){
								if($vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['DEP_DOC_TRANSF']) $pagto_tipo = "Transf, DOC, Dep";
								else if($vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO']) $pagto_tipo = "Boleto";
								else $pagto_tipo = $GLOBALS['FORMAS_PAGAMENTO_DESCRICAO'][$vg_pagto_tipo];
							}
							if($ag_u_codigo){
								$ug_nome = "Anonymous";
								if($vg_ug_id && trim($vg_ug_id) != ""){
									$sql  = "select * from usuarios_games ug where ug.ug_id = '" . $vg_ug_id . "'";
									$rs_ug = SQLexecuteQuery($sql);
									if($rs_ug && pg_num_rows($rs_ug) == 1){
										$rs_ug_row = pg_fetch_array($rs_ug);
										$ug_nome = $rs_ug_row['ug_nome'];
									}
								}
							}
					?>
                      <tr bgcolor="<?php echo $cor1 ?>"> 
						<?php if($ag_user_id_concilia){ ?><td><font class="texto">&nbsp;<?=$shn_nome?></font></td><?php } ?>
						<?php if($ag_d_forma_pagto){ ?><td><font class="texto">&nbsp;<?=$pagto_tipo?></font></td><?php } ?>
						<?php if($ag_u_codigo){ ?><td><font class="texto">&nbsp;<?=$ug_nome?></font></td><?php } ?>
                        <td class="texto" align="right"><?php echo number_format($rs_venda_row['qtde'], 0, '','.') ?></td>
                      </tr>
					<?php 	}	?>
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
