<?php 
require_once "../../../includes/constantes.php";
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "pdv/main.php";

validaSessao();

	$msg = "";

	if(!$venda_id) $msg = "Código da venda não fornecido.\n";
	elseif(!is_numeric($venda_id)) $msg = "Código da venda inválido.\n";

	//Processa acoes
	//----------------------------------------------------------------------------------------------------------
	if($msg == ""){

	}

	//Mostra a pagina
	//----------------------------------------------------------------------------------------------------------
	//Recupera a venda
	if($msg == ""){
		$sql  = "select * from tb_dist_venda_games vg " .
				"where vg.vg_id = " . $venda_id;
		$rs_venda = SQLexecuteQuery($sql);
		if(!$rs_venda || pg_num_rows($rs_venda) == 0) $msg = "Nenhuma venda encontrada.\n";
			$rs_venda_row = pg_fetch_array($rs_venda);
			$vg_ug_id 				= $rs_venda_row['vg_ug_id'];
			$vg_ultimo_status 		= $rs_venda_row['vg_ultimo_status'];
			$vg_ultimo_status_obs 	= $rs_venda_row['vg_ultimo_status_obs'];
			$vg_usuario_obs 		= $rs_venda_row['vg_usuario_obs'];
			$vg_pagto_tipo 			= $rs_venda_row['vg_pagto_tipo'];
			$vg_data_inclusao 		= $rs_venda_row['vg_data_inclusao'];
			$vg_pagto_data_inclusao = $rs_venda_row['vg_pagto_data_inclusao'];
			$vg_pagto_data 			= $rs_venda_row['vg_pagto_data'];
			$vg_pagto_banco 		= $rs_venda_row['vg_pagto_banco'];
			$vg_pagto_local 		= $rs_venda_row['vg_pagto_local'];
			$vg_pagto_valor_pago 	= $rs_venda_row['vg_pagto_valor_pago'];
			$vg_pagto_num_docto 	= $rs_venda_row['vg_pagto_num_docto'];
			$vg_concilia 			= $rs_venda_row['vg_concilia'];
			$vg_data_concilia 		= $rs_venda_row['vg_data_concilia'];
			$vg_user_id_concilia 	= trim($rs_venda_row['vg_user_id_concilia']);
			$vg_dep_codigo 			= $rs_venda_row['vg_dep_codigo'];
			$vg_bol_codigo 			= $rs_venda_row['vg_bol_codigo'];

 			$pagto_num_docto 	 = explode("\|", $vg_pagto_num_docto);
	}

	//Recupera modelos
	if($msg == ""){
		$sql  = "select * from tb_dist_venda_games vg " .
				"inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id " .
				"where vg.vg_id = " . $venda_id;
		$rs_venda_modelos = SQLexecuteQuery($sql);
		if(!$rs_venda_modelos || pg_num_rows($rs_venda_modelos) == 0) $msg = "Nenhum produto encontrado.(4335f)\n";
		else {
			$total_geral = 0; $qtde_itens = 0; $qtde_produtos = 0;
			while ($rs_venda_modelos_row = pg_fetch_array($rs_venda_modelos)){
				$qtde = $rs_venda_modelos_row['vgm_qtde'];
				$valor = $rs_venda_modelos_row['vgm_valor'];
				$perc_desconto = $rs_venda_modelos_row['vgm_perc_desconto'];
				$qtde_itens += $qtde;
				$qtde_produtos += 1;
				$geral = $valor*$qtde;
				$desconto = $geral*$perc_desconto/100;
				$repasse = $geral - $desconto;
				$qtde_total += $qtde;
				$total_geral += $geral;
				$total_desconto += $desconto;
				$total_repasse += $repasse;
			}
			pg_result_seek($rs_venda_modelos, 0); 
		}
	}

	//Recupera historico da venda
	if($msg == ""){
		$sql  = "select * from tb_dist_venda_games_historico vgh 
				 where vgh.vgh_vg_id = " . $venda_id . "
				 order by vgh_data_inclusao desc";
		$rs_venda_hist = SQLexecuteQuery($sql);
	}

	//Recupera dados do usuario
	if($msg == ""){
		$sql  = "select * from dist_usuarios_games ug " .
				"where ug.ug_id = " . $vg_ug_id;
		$rs_usuario = SQLexecuteQuery($sql);
		if(!$rs_usuario || pg_num_rows($rs_usuario) == 0) $msg = "Nenhum cliente encontrado.\n";
		else {
			$rs_usuario_row = pg_fetch_array($rs_usuario);
			$ug_login = $rs_usuario_row['ug_login'];

			$ug_responsavel = $rs_usuario_row['ug_responsavel'];
			$ug_email = $rs_usuario_row['ug_email'];
			$ug_nome_fantasia = $rs_usuario_row['ug_nome_fantasia'];
			$ug_cnpj = $rs_usuario_row['ug_cnpj'];

			$ug_cidade = $rs_usuario_row['ug_cidade'];
			$ug_estado = $rs_usuario_row['ug_estado'];
			$ug_tel_ddi = $rs_usuario_row['ug_tel_ddi'];
			$ug_tel_ddd = $rs_usuario_row['ug_tel_ddd'];
			$ug_tel = $rs_usuario_row['ug_tel'];
			
			$ug_tipo_cadastro = $rs_usuario_row['ug_tipo_cadastro'];
			$ug_nome = $rs_usuario_row['ug_nome'];
			$ug_cpf = $rs_usuario_row['ug_cpf'];
			$ug_rg = $rs_usuario_row['ug_rg'];
			
		}
	}

	//Recupera dados da forma de pagamento
	if($msg == ""){

		if($vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO']){
			$sql  = "select * from dist_boleto_bancario_games bbg " .
					"where bbg.bbg_vg_id = " . $venda_id;
			$rs_boleto = SQLexecuteQuery($sql);
			if(!$rs_boleto || pg_num_rows($rs_boleto) == 0) $msg = "Nenhum boleto encontrado.\n";
			else {
				$rs_boleto_row = pg_fetch_array($rs_boleto);
				$bbg_boleto_codigo = $rs_boleto_row['bbg_boleto_codigo'];
				$bbg_data_inclusao = $rs_boleto_row['bbg_data_inclusao'];
				$bbg_bco_codigo = $rs_boleto_row['bbg_bco_codigo'];
				$bbg_documento = $rs_boleto_row['bbg_documento'];
				$bbg_valor = $rs_boleto_row['bbg_valor'];
				$bbg_valor_taxa = $rs_boleto_row['bbg_valor_taxa'];
				$bbg_data_venc = $rs_boleto_row['bbg_data_venc'];
				$bbg_data_pago = $rs_boleto_row['bbg_data_pago'];
				$bbg_pago = $rs_boleto_row['bbg_pago'];
echo "$bbg_bco_codigo*<br>";

			}

		} elseif($vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['REDECARD_MASTERCARD'] || $vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['REDECARD_DINERS']){
			$sql  = "select * from tb_venda_games_redecard vgrc " .
					"where vgrc.vgrc_vg_id = " . $venda_id;
			$rs_redecard = SQLexecuteQuery($sql);
			if(!$rs_redecard || pg_num_rows($rs_redecard) == 0) $msg = "Nenhum redecard encontrado.\n";
			else {
				$rs_redecard_row = pg_fetch_array($rs_redecard);
				$vgrc_id = $rs_redecard_row['vgrc_id'];
				$vgrc_vg_id = $rs_redecard_row['vgrc_vg_id'];
				$vgrc_ug_id = $rs_redecard_row['vgrc_ug_id'];
				$vgrc_parcelas = $rs_redecard_row['vgrc_parcelas'];
				$vgrc_data_inclusao = $rs_redecard_row['vgrc_data_inclusao'];
				$vgrc_total = $rs_redecard_row['vgrc_total'];
				$vgrc_transacao = $rs_redecard_row['vgrc_transacao'];
				$vgrc_bandeira = $rs_redecard_row['vgrc_bandeira'];
				$vgrc_codver = $rs_redecard_row['vgrc_codver'];
				$vgrc_data_envio1 = $rs_redecard_row['vgrc_data_envio1'];
				$vgrc_ret2_data = $rs_redecard_row['vgrc_ret2_data'];
				$vgrc_ret2_nr_cartao = $rs_redecard_row['vgrc_ret2_nr_cartao'];
				$vgrc_ret2_origem_bin = $rs_redecard_row['vgrc_ret2_origem_bin'];
				$vgrc_ret2_numautor = $rs_redecard_row['vgrc_ret2_numautor'];
				$vgrc_ret2_numcv = $rs_redecard_row['vgrc_ret2_numcv'];
				$vgrc_ret2_numautent = $rs_redecard_row['vgrc_ret2_numautent'];
				$vgrc_ret2_numsqn = $rs_redecard_row['vgrc_ret2_numsqn'];
				$vgrc_ret2_codret = $rs_redecard_row['vgrc_ret2_codret'];
				$vgrc_ret2_msgret = $rs_redecard_row['vgrc_ret2_msgret'];
				$vgrc_ret4_ret = $rs_redecard_row['vgrc_ret4_ret'];
				$vgrc_ret4_codret = $rs_redecard_row['vgrc_ret4_codret'];
				$vgrc_ret4_msgret = $rs_redecard_row['vgrc_ret4_msgret'];
				$vgrc_usuario_ip = $rs_redecard_row['vgrc_usuario_ip'];
				$vgrc_ret2_endereco = $rs_redecard_row['vgrc_ret2_endereco'];
				$vgrc_ret2_numero = $rs_redecard_row['vgrc_ret2_numero'];
				$vgrc_ret2_complemento = $rs_redecard_row['vgrc_ret2_complemento'];
				$vgrc_ret2_cep = $rs_redecard_row['vgrc_ret2_cep'];
				$vgrc_ret2_respavs = $rs_redecard_row['vgrc_ret2_respavs'];
				$vgrc_ret2_msgavs = $rs_redecard_row['vgrc_ret2_msgavs'];
				
				$vgrc_ret2_numprg = $rs_redecard_row['vgrc_ret2_numprg'];
				$vgrc_ret2_nr_hash_cartao = $rs_redecard_row['vgrc_ret2_nr_hash_cartao'];
				$vgrc_ret2_cod_banco = $rs_redecard_row['vgrc_ret2_cod_banco'];
			}
		
		}
	}

	//Se conciliado, Recupera dados do usuario que conciliou
	if($msg == ""){

		if($vg_concilia == 1){
		
			if($vg_user_id_concilia == ""){
				$shn_nome = "Anonymous";
			} else {
				$sql  = "select * from usuarios urpp " .
						"where urpp.id = '" . $vg_user_id_concilia . "'";
				$rs_urpp = SQLexecuteQuery($sql);
				if(!$rs_urpp || pg_num_rows($rs_urpp) == 0){
					$shn_nome = "Anonymous";
				} else {
					$rs_urpp_row = pg_fetch_array($rs_urpp);
					$shn_nome = $rs_urpp_row['shn_nome'];
				}
			}
		}
	}

	$msg = $msgConciliaUsuario . $msg;

	$cor1 = "#F5F5FB";
	$cor2 = "#F5F5FB";
	$cor3 = "#FFFFFF"; 	


ob_end_flush();
?>

<?php $pagina_titulo = "Meus Pedidos"; ?>
<center>
<?php require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/cabecalho.php";  ?>

        <table width="100%" border="0" cellpadding="0" cellspacing="1" class="texto">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="2" bgcolor="#ECE9D8">Venda</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="150"><b>C&oacute;digo</b></td>
            <td><?php echo $venda_id ?></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Data</b></td>
            <td><?php echo formata_data_ts($vg_data_inclusao, 0, true, true) ?></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Valor</b></td>
            <td>
			<?php echo number_format($total_geral, 2, ',', '.') ?></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Repasse</b></td>
            <td>
			<?php echo number_format($total_repasse, 2, ',', '.') ?></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Status</b></td>
			<?php if($vg_ultimo_status == $GLOBALS['STATUS_VENDA']['VENDA_CANCELADA']){?>
			<td><font color="FF0000"><?php echo $GLOBALS['STATUS_VENDA_DESCRICAO'][$vg_ultimo_status] ?></font></td>
			<?php } else {?>
			<td><?php echo $GLOBALS['STATUS_VENDA_DESCRICAO'][$vg_ultimo_status] ?></td>
			<?php } ?>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Observações ao usuário</b></td>
			<td><?php echo str_replace("\n", "<br>", $vg_usuario_obs) ?></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Conciliação</b></td>
			<td><?php echo ($vg_concilia==1?"Conciliado":"Não conciliado") ?></td>
          </tr>
		</table>

        <table width="100%" border="0" cellpadding="0" cellspacing="1" class="texto">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="2" bgcolor="#ECE9D8">Produtos</font></td>
          </tr>
          <tr>
		  	<td>
				<table border="0" cellpadding="0" cellspacing="1" width="100%" align="center">
					<tr bgcolor="F0F0F0" class="texto">
					  <td align="center"><b>Produto</b></td>
					  <td align="center"><b>Quantidade</b></td>
					  <td align="right"><b>Preço Unitário</b></td>
					  <td align="right"><b>Preço Total</b></td>
	    	          <td align="center"><b>Desconto</b></td>
	    	          <td align="center"><b>Repasse</b></td>
					</tr>
		<?php
				$qtde_total = 0;
				$total_geral = 0;
				$total_desconto = 0;
				$total_repasse = 0;
				while ($rs_venda_modelos_row = pg_fetch_array($rs_venda_modelos)){
					$qtde = $rs_venda_modelos_row['vgm_qtde'];
					$valor = $rs_venda_modelos_row['vgm_valor'];
					$perc_desconto = $rs_venda_modelos_row['vgm_perc_desconto'];
					$geral = $valor*$qtde;
					$desconto = $geral*$perc_desconto/100;
					$repasse = $geral - $desconto;
					
					$qtde_total += $qtde;
					$total_geral += $geral;
					$total_desconto += $desconto;
					$total_repasse += $repasse;
					
		?>
					<tr class="texto" bgcolor="#F5F5FB">
					  <td width="200">
						&nbsp;&nbsp;
						<?php echo $rs_venda_modelos_row['vgm_nome_produto']?> 
						<?php if($rs_venda_modelos_row['vgm_nome_modelo']!=""){?> - <?php echo $rs_venda_modelos_row['vgm_nome_modelo']?><?}?>
					  </td>
					  <td align="center"><?php echo $qtde?></td>
					  <td align="right"><?php echo number_format($valor, 2, ',', '.')?></td>
	    	          <td align="right"><?php echo number_format($geral, 2, ',', '.')?></td>
	    	          <td align="right"><?php echo number_format($desconto, 2, ',', '.')?></td>
	    	          <td align="right"><?php echo number_format($repasse, 2, ',', '.')?></td>
					</tr>
			<?php	} ?>
					<tr bgcolor="F0F0F0" class="texto">
					  <td colspan="2">&nbsp;</td>
					  <td align="right"><b>Total</b></td>
	    	          <td align="right"><b><?php echo number_format($total_geral, 2, ',', '.')?></b></td>
	    	          <td align="right"><b><?php echo number_format($total_desconto, 2, ',', '.')?></b></td>
	    	          <td align="right"><b><?php echo number_format($total_repasse, 2, ',', '.')?></b></td>
					</tr>
				</table>
			</td>
		  </tr>
		</table>

        <table width="100%" border="0" cellpadding="0" cellspacing="0">
          <tr bgcolor="#FFFFFF" class="texto"> 
            <td bgcolor="#ECE9D8">Histórico</td>
          </tr>
          <tr bgcolor="#FFFFFF" class="texto"> 
            <td>
					  <table border='0' width="100%" cellpadding="0" cellspacing="01" class="texto" bgcolor="ffffff">
						<tr bgcolor="#ECE9D8"> 
						  <td align="center" width="150">Data</td>
						  <td align="center" width="250">Status</td>
						  <td align="center" width="494">Observações</td>
						</tr>
				<?php if($rs_venda_hist && pg_num_rows($rs_venda_hist) > 0){?>
					<?php while ($rs_venda_hist_row = pg_fetch_array($rs_venda_hist)) {
							if ($cor1 == $cor2) {$cor1 = $cor3;} else {$cor1 = $cor2;} ?>
							<tr bgcolor="<?php echo $cor1 ?>"> 
							  <td align="center"><?php echo formata_data_ts($rs_venda_hist_row['vgh_data_inclusao'], 0, true, true) ?></td>
							  <?php $vgh_status = $rs_venda_hist_row['vgh_status'];?>
							  <?php $statusNome = $GLOBALS['STATUS_VENDA_DESCRICAO'][$vgh_status]; ?>
							  <td><?php echo substr($statusNome, 0, strpos($statusNome, '.')) ?></td>
							  <td><?php echo str_replace("\n", "<br>", $rs_venda_hist_row['vgh_status_obs']) ?></td>
							</tr>
					<?php	} ?>
				<?php } ?>
					  </table>

			</td>
          </tr>
		</table>


        <table width="100%" border="0" cellpadding="0" cellspacing="1" class="texto">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="2" bgcolor="#ECE9D8" class="texto">Usuário</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="150"><b>C&oacute;digo</b></td>
            <td><a style="text-decoration:none" href="com_usuario_detalhe.php?usuario_id=<?php echo $vg_ug_id ?>"><?php echo $vg_ug_id ?></a></td>
		  </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="150"><b>Login</b></td>
            <td><a style="text-decoration:none" href="com_usuario_detalhe.php?usuario_id=<?php echo $vg_ug_id ?>"><?php echo $ug_login ?></a></td>
		  </tr>
<?php if($ug_tipo_cadastro == "PJ"){ ?>
		  <tr bgcolor="#F5F5FB">
            <td><b>Nome Fantasia</b></td>
            <td><?php echo $ug_nome_fantasia ?></td>
		  </tr>
<?php } ?>
		  
<?php if($ug_tipo_cadastro == "PF"){ ?>
		  <tr bgcolor="#F5F5FB">
            <td><b>Nome</b></td>
            <td><?php echo $ug_nome ?></td>
		  </tr>
<?php } ?>

          <tr bgcolor="#F5F5FB"> 
            <td><b>Email</b></td>
            <td><?php echo $ug_email ?></td>
		  </tr>

		</table>


<?php if($vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO']){ ?>
        <table width="100%" border="0" cellpadding="0" cellspacing="1" class="texto">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="2" bgcolor="#ECE9D8">Dados do Boleto</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="150"><b>Código</b></td>
            <td><?php echo $bbg_boleto_codigo ?></td>
		  </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="150"><b>Data de Inclusão</b></td>
            <td><?php if($bbg_data_inclusao) echo formata_data_ts($bbg_data_inclusao, 0, true, true) ?></td>
		  </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>Banco</b></td>
            <td><?php echo $bbg_bco_codigo ?></td>
		  </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>Data de Vencimento</b></td>
            <td><?php if($bbg_data_venc) echo formata_data_ts($bbg_data_venc, 0, false, false) ?></td>
		  </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>Valor</b></td>
            <td><?php if($bbg_valor) echo number_format($bbg_valor, 2, ',', '.') ?></td>
		  </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>Taxa de Serviço Bancário</b></td>
            <td><?php if($bbg_valor_taxa) echo number_format($bbg_valor_taxa, 2, ',', '.') ?></td>
		  </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>N. Docto</b></td>
            <td><?php echo $bbg_documento ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>Quitação</b></td>
            <td><?php echo ((is_null($bbg_pago) || $bbg_pago == 0)?("Não quitado"):("Quitado em " . formata_data_ts($bbg_data_pago, 0, false, false))) ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>Ver boleto</b></td>
			<?php
			$token = date('YmdHis') . "," . $venda_id . "," . $vg_ug_id;
			$objEncryption = new Encryption();
			$token_crypt = $objEncryption->encrypt($token);
//echo "bbg_bco_codigo: '$bbg_bco_codigo'<br>";
//echo "*$bbg_bco_codigo*<br>";

			switch($bbg_bco_codigo) {
				case $BOLETO_MONEY_BANCO_ITAU_COD_BANCO:
					$sboletoURL = "/SICOB/BoletoWebItauCommerce.php";
					break;
				case $BOLETO_MONEY_CAIXA_COD_BANCO:
					$sboletoURL = "/SICOB/BoletoWebCaixaDistCommerce.php";
					break;
				case $BOLETO_MONEY_BRADESCO_COD_BANCO:
					$sboletoURL = "/boletos/pdv/boleto_bradesco.php";
					break;
				default:
					$sboletoURL = "";
					break;
			}

			?>
            <td>
<?php
/*
				<a style="text-decoration:none" href="http://www.e-prepag.com.br/SICOB/BoletoWebCaixaDistCommerce.php?token=<_?)php echo $token_crypt?_>" target="_blank">Boleto</a>*
				&nbsp;&nbsp;&nbsp;*link válido por 5 min, após este período recarregar a página para pode acessá-lo.
*/
?>
				<?php if($sboletoURL) { ?>
				<a style="text-decoration:none" href="https://<?php echo $_SERVER["SERVER_NAME"] . $sboletoURL; ?>?token=<?php echo $token_crypt?>" target="_blank">Boleto</a>*
				&nbsp;&nbsp;&nbsp;*link válido por 5 min, após este período recarregar a página para pode acessá-lo.
				<?php } else { ?>
				Sem boleto
				<?php } ?>

			</td>
          </tr>
		</table>

<?php } elseif($vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['REDECARD_MASTERCARD'] || $vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['REDECARD_DINERS']){ ?>
        <table width="100%" border="0" cellpadding="0" cellspacing="1" class="texto">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="2" bgcolor="#ECE9D8">Dados Redecard</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="150"><b>Código</b></td>
            <td><?php echo $vgrc_id ?></td>
		  </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="150"><b>Data de Inclusão</b></td>
            <td><?php if($vgrc_data_inclusao) echo formata_data_ts($vgrc_data_inclusao, 0, true, true) ?></td>
		  </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>Parcelas</b></td>
            <td><?php echo $vgrc_parcelas ?></td>
		  </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>Valor</b></td>
            <td><?php if($vgrc_total) echo number_format($vgrc_total, 2, ',', '.') ?></td>
		  </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>Data de Envio</b></td>
            <td><?php if($vgrc_data_envio1) echo formata_data_ts($vgrc_data_envio1, 0, true, true) ?></td>
		  </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>IP do usuário</b></td>
            <td><?php echo $vgrc_usuario_ip ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>TRANSACAO</b></td>
            <td><?php echo $vgrc_transacao ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>BANDEIRA</b></td>
            <td><?php echo $vgrc_bandeira ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>CODVER</b></td>
            <td><?php echo $vgrc_codver ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>DATA</b></td>
            <td><?php echo $vgrc_ret2_data ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>NR_CARTAO</b></td>
            <td><?php echo $vgrc_ret2_nr_cartao ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>ORIGEM_BIN</b></td>
            <td><?php echo $vgrc_ret2_origem_bin ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>NUMAUTOR</b></td>
            <td><?php echo $vgrc_ret2_numautor ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>NUMCV</b></td>
            <td><?php echo $vgrc_ret2_numcv ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>NUMAUTENT</b></td>
            <td><?php echo $vgrc_ret2_numautent ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>NUMSQN</b></td>
            <td><?php echo $vgrc_ret2_numsqn ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>CODRET</b></td>
            <td><?php echo $vgrc_ret2_codret ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>MSGRET</b></td>
            <td><?php echo urldecode($vgrc_ret2_msgret) ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>ENDERECO</b></td>
            <td><?php echo $vgrc_ret2_endereco ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>NUMERO</b></td>
            <td><?php echo $vgrc_ret2_numero ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>COMPLEMENTO</b></td>
            <td><?php echo $vgrc_ret2_complemento ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>CEP</b></td>
            <td><?php echo $vgrc_ret2_cep ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>RESPAVS</b></td>
            <td><?php echo $vgrc_ret2_respavs ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>MSGAVS</b></td>
            <td><?php echo urldecode($vgrc_ret2_msgavs)  ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>NUMPRG</b></td>
            <td><?php echo $vgrc_ret2_numprg  ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>NR_HASH_CARTAO</b></td>
            <td><?php echo $vgrc_ret2_nr_hash_cartao ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>COD_BANCO</b></td>
            <td><?php echo $vgrc_ret2_cod_banco ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>Retorno Etapa 4</b></td>
            <td><?php echo $vgrc_ret4_ret ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>Retorno Etapa 4 - CODRET</b></td>
            <td><?php echo $vgrc_ret4_codret ?></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td><b>Retorno Etapa 4 - MSGRET</b></td>
            <td><?php echo urldecode($vgrc_ret4_msgret) ?></td>
          </tr>
		</table>

<?php } ?>





<?php if($vg_ultimo_status == $GLOBALS['STATUS_VENDA']['PEDIDO_EFETUADO'] || $vg_ultimo_status == $GLOBALS['STATUS_VENDA']['PEDIDO_EM_STANDBY'] || $vg_ultimo_status == $GLOBALS['STATUS_VENDA']['AGUARDANDO_PROCESSAMENTO']) {?>
			<table width="100%" border="0" cellpadding="0" cellspacing="1" class="texto">
			  <tr bgcolor="#FFFFFF"> 
				<td colspan="2" bgcolor="#ECE9D8" class="texto">Processa venda</font></td>
			  </tr>
			  <tr bgcolor="#F5F5FB" align="center">
				<td colspan="2"><b>Observações</b></td>
			  </tr>
			  <tr bgcolor="#F5F5FB">
				<td valign="top" colspan="2" align="center">
					<?php echo $vg_ultimo_status_obs ?>
				</td>
			  </tr>
			  <tr bgcolor="#F5F5FB"> 
				<td align="center" width="50%">&nbsp;</td>
				<td align="center" width="50%">&nbsp;</td>
			  </tr>
			</table>
<?php }?>

<?php if($vg_ultimo_status == $GLOBALS['STATUS_VENDA']['PROCESSAMENTO_REALIZADO'] || $vg_ultimo_status == $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']) {?>
			<table width="100%" border="0" cellpadding="0" cellspacing="1" class="texto">
			  <tr bgcolor="#FFFFFF"> 
				<td colspan="2" bgcolor="#ECE9D8" class="texto">Processa envio de email</font></td>
			  </tr>
			  <tr bgcolor="#F5F5FB">
				<td colspan="2"><b>Observações</b></td>
			  </tr>
			  <tr bgcolor="#F5F5FB">
				<td valign="top" colspan="2">
					<?php echo $vg_ultimo_status_obs ?>
				</td>
			  </tr>
			  <tr bgcolor="#F5F5FB"> 
				<td colspan="2" align="center"></td>
			  </tr>
			</table>
<?php }?>

<?php if($vg_ultimo_status == $GLOBALS['STATUS_VENDA']['PEDIDO_EFETUADO'] || $vg_ultimo_status == $GLOBALS['STATUS_VENDA']['PEDIDO_EM_STANDBY'] || $vg_ultimo_status == $GLOBALS['STATUS_VENDA']['AGUARDANDO_PROCESSAMENTO']) {?>
			<table width="100%" border="0" cellpadding="0" cellspacing="1" class="texto">
			  <tr bgcolor="#FFFFFF"> 
				<td bgcolor="#ECE9D8" colspan="2">Cancelamento</font></td>
			  </tr>
			  <tr bgcolor="#F5F5FB"> 
				<td align="center"><b>Observações</b></td>
				<td align="center"><b>Observações ao usuário</b></td>
			  </tr>
			  <tr bgcolor="#F5F5FB">
				<td align="center"><?php echo $vg_ultimo_status_obs ?></td>
				<td align="center"><?php echo $vg_usuario_obs ?></td>
			</tr>
			  <tr bgcolor="#F5F5FB"><td colspan="2">&nbsp;</td></tr>
			  <tr bgcolor="#F5F5FB">
				<td align="center" colspan="2">&nbsp;</td>
			  </tr>
			</table>
<?php } ?>


			<table width="100%" border="0" cellpadding="0" cellspacing="1" class="texto">
			  <tr bgcolor="#F5F5FB"> 
				<td colspan="2" align="center">
					&nbsp;&nbsp;&nbsp;
					<input type="button" name="BtnVoltar" value="Voltar" class="botao_search" onClick="window.location='index.php'">
					&nbsp;&nbsp;&nbsp;
				</td>
			  </tr>
			</table>

    </td>
  </tr>
</table>
</center>
<?php
require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/footer.php";
?>
</html>
