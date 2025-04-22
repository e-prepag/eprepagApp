<?php 
        require_once '../../../includes/constantes.php';
        require_once $raiz_do_projeto."backoffice/includes/topo.php";
        require_once $raiz_do_projeto."includes/main.php";
        require_once $raiz_do_projeto."includes/pdv/main.php";
	$time_start = getmicrotime();
	
	if(!$ncamp)    $ncamp       = 'vg_data_inclusao';
	if(!$inicial)  $inicial     = 0;
	if(!$range)    $range       = 1;
	if(!$ordem)    $ordem       = 1;
//	if($BtnSearch) $inicial     = 0;
//	if($BtnSearch) $range       = 1;
//	if($BtnSearch) $total_table = 0;
	if($BtnSearch=="Buscar") {
		$inicial     = 0;
		$range       = 1;
		$total_table = 0;
	}

	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
	$img_anterior = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
	$max          = 100; //$qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;

	$varsel = "&BtnSearch=1&tf_v_codigo=$tf_v_codigo&tf_v_status=$tf_v_status";
	$varsel .= "&tf_v_data_inclusao_ini=$tf_v_data_inclusao_ini&tf_v_data_inclusao_fim=$tf_v_data_inclusao_fim";
	$varsel .= "&tf_d_valor_pago=$tf_d_valor_pago&tf_d_num_docto=$tf_d_num_docto";
	$varsel .= "&tf_u_codigo=$tf_u_codigo&tf_u_nome_fantasia=$tf_u_nome_fantasia&tf_u_email=$tf_u_email&tf_u_responsavel=$tf_u_responsavel";
	$varsel .= "&tf_u_cnpj=$tf_u_cnpj&tf_v_repasse=$tf_v_repasse";
	$varsel .= "&tf_u_nome=$tf_u_nome&tf_u_rg=$tf_u_rg&tf_u_cpf=$tf_u_cpf";
	$varsel .= "&tf_v_valor=$tf_v_valor";
	$varsel .= "&tf_u_risco_classif=$tf_u_risco_classif&tf_v_status=$tf_v_status";
//	$varsel .= "&tf_opr_codigo=$tf_opr_codigo&tf_o_valor_face=$tf_o_valor_face&tf_v_origem=$tf_v_origem";


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
		//repasse
		if($msg == "")
			if($tf_v_repasse){
				if(!is_moeda($tf_v_repasse))
					$msg = "Valor do repasse da venda é inválido.\n";
			}

		//status
		if($msg == "")
			if($tf_v_status){
				if($tf_v_status!=$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'] && $tf_v_status!=-1)
					$tf_v_status = $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'];
			}


		//Dados do Pagamento
		//------------------------------------------------------------------
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

	
	
		//Busca vendas
		//------------------------------------------------------------------------------------------------------------------
		if($msg == ""){

			$sql  = "select bbg_vg_id, bbg_ug_id, ug.ug_email, ug.ug_responsavel, ug.ug_nome_fantasia, ug.ug_nome, ug.ug_tipo_cadastro, 
				bbg_ug_id as vg_id, bbg_data_inclusao as vg_data_inclusao, 
				vg_pagto_tipo, vg_ultimo_status, vg_concilia, bbg_documento as vg_pagto_num_docto, 
				bbg_bco_codigo, bbg_valor as valor, bbg_valor_taxa as repasse 
			from  dist_boleto_bancario_games bbg 
				inner join dist_usuarios_games ug on bbg.bbg_ug_id=ug.ug_id 
				inner join tb_dist_venda_games vg on bbg.bbg_vg_id=vg.vg_id ";
			$sql .= " where 1=1  ";
			if($tf_v_codigo) 			$sql .= " and bbg_vg_id = '".$tf_v_codigo."' ";
			if($tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim) $sql .= " and bbg_data_inclusao between '".formata_data($tf_v_data_inclusao_ini,1)." 00:00:00' and '".formata_data($tf_v_data_inclusao_fim,1)." 23:59:59'";
			if($tf_d_banco) 			$sql .= " and bbg_bco_codigo = '".$tf_d_banco."' ";
			if($tf_d_valor) 			$sql .= " and bbg_valor = '".$tf_d_valor."' ";
			if($tf_d_num_docto) 		$sql .= " and upper(bbg_documento) like '%". strtoupper($tf_d_num_docto)."%' ";
			if($tf_u_codigo) 			$sql .= " and ug.ug_id = '".$tf_u_codigo."' ";
			if($tf_u_nome_fantasia) 	$sql .= " and upper(ug.ug_nome_fantasia) like '%".strtoupper($tf_u_nome_fantasia)."%' ";
			if($tf_u_email) 			$sql .= " and upper(ug.ug_email) like '%".strtoupper($tf_u_email)."%' ";
			if($tf_u_cnpj) 				$sql .= " and ug.ug_cnpj like '%".$tf_u_cnpj."%' ";
			if($tf_u_nome) 				$sql .= " and upper(ug.ug_nome) like '%" . strtoupper($tf_u_nome) . "%' ";
			if($tf_u_cpf) 				$sql .= " and ug.ug_cpf like '%" . $tf_u_cpf . "%' ";
			if($tf_u_rg) 				$sql .= " and ug.ug_rg like '%" . $tf_u_rg . "%' ";
			if($tf_u_risco_classif) 	$sql .= " and ug.ug_risco_classif =" . $RISCO_CLASSIFICACAO[$tf_u_risco_classif] . " ";
			if($tf_v_status) {
				if($tf_v_status==$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']) {
					$sql .= " and vg.vg_ultimo_status = '".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' ";
				} else {
					$sql .= " and (not vg.vg_ultimo_status = '".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."') ";
				}
			}


			$sql .= " group by bbg_vg_id, ug.ug_id, ug.ug_email, ug.ug_responsavel, ug.ug_nome_fantasia, ug.ug_nome, ug.ug_tipo_cadastro, 
						bbg_ug_id, bbg_data_inclusao, bbg_documento, bbg_bco_codigo, bbg_valor, bbg_valor_taxa, vg_pagto_tipo, 
						vg_ultimo_status, vg_concilia 
					 having 1=1 ";
			if($tf_v_valor) 		$sql .= " and bbg_valor = ".moeda2numeric($tf_v_valor)." ";
		
			$rs_venda = SQLexecuteQuery($sql);
			$total_table = pg_num_rows($rs_venda);

			//Total Geral
			$totalGeral_valor = 0;
			if($total_table > 0){
				while($rs_venda_row = pg_fetch_array($rs_venda)){
					$totalGeral_valor += $rs_venda_row['valor'];
				}
			}

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
				$msg = "Nenhum boleto Money Express LH encontrado.\n";
			} else {		
				$rs_venda = SQLexecuteQuery($sql);
				
				if($max + $inicial > $total_table)
					$reg_ate = $total_table;
				else
					$reg_ate = $max + $inicial;
			}
				
		}
	}
	
ob_end_flush();
?>
<link href="/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="/js/global.js"></script>
<script language="javascript">
    $(function(){
       var optDate = new Object();
            optDate.interval = 10000;

        setDateInterval('tf_v_data_inclusao_ini','tf_v_data_inclusao_fim',optDate);
        
    });
    
	function GP_popupAlertMsg(msg) { //v1.0
	  document.MM_returnValue = alert(msg);
	}
	function GP_popupConfirmMsg(msg) { //v1.0
	  document.MM_returnValue = confirm(msg);
	}
	function atuaOper(id){
		if (document.getElementById('tf_opr_codigo').value != '')
		window.location = 'com_pesquisa_vendas_lh_express.php?tf_opr_codigo=' + id;
	}

</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<table class="table fontsize-p txt-preto">
  <tr> 
    <td>
        <form name="form1" method="post" action="com_pesquisa_vendas_lh_express.php">
        <table class="table top20">
          <tr bgcolor="#F5F5FB"> 
            <td align="right"><input type="submit" name="BtnSearch" value="Buscar" class="btn btn-sm btn-info"></td>
          </tr>
		</table>
        <table class="table top20">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="6" bgcolor="#ECE9D8" class="texto">Venda</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td width="100" class="texto">Número do Pedido</font></td>
            <td>
              	<input name="tf_v_codigo" type="text" class="form2" value="<?php echo $tf_v_codigo ?>" size="8" maxlength="8">
			</td>
            <td class="texto">&nbsp;</font></td>
			<td>&nbsp;</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td class="texto">Valor</font></td>
            <td>
              	<input name="tf_v_valor" type="text" class="form2" value="<?php echo $tf_v_valor ?>" size="7" maxlength="7">
			</td>
            <td class="texto">&nbsp;</font></td>
            <td>&nbsp;</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td class="texto">Data Inclusão</font></td>
            <td class="texto">
              <input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php echo $tf_v_data_inclusao_ini ?>" size="9" maxlength="10">
              a 
              <input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php echo $tf_v_data_inclusao_fim ?>" size="9" maxlength="10">
			</td>
            <td class="texto">Status</font></td>
            <td>
				<select name="tf_v_status" class="field_dados" class="form2">
					<option value="" <?php  if($tf_v_status == "") echo "selected" ?>>Selecione</option>
					<option value="<?php  echo $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'] ?>"<?php  if($tf_v_status == $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']) echo " selected"; ?>><?php  echo 'Conciliado' ?></option>
					<option value="-1"<?php  if($tf_v_status == -1) echo " selected"; ?>><?php  echo 'Não Conciliado' ?></option>
				</select>
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
			<td colspan="2"></td>
		  </tr>
          <tr bgcolor="#F5F5FB"> 
            <td class="texto">Responsável</font></td>
            <td>
              	<input name="tf_u_responsavel" type="text" class="form2" value="<?php echo $tf_u_responsavel ?>" size="25" maxlength="14">
			</td>
            <td class="texto">Email</font></td>
            <td>
              	<input name="tf_u_email" type="text" class="form2" value="<?php echo $tf_u_email ?>" size="25" maxlength="100">
			</td>
		  </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td>Nome</font></td>
            <td>
              	<input name="tf_u_nome" type="text" class="form2" value="<?php echo $tf_u_nome ?>" size="25" maxlength="100">
			</td>
            <td colspan="2">&nbsp;</td>
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

		  <tr bgcolor="#F5F5FB" class="texto"> 
			<td class="texto">Classificação</td>
			<td colspan="3" class="texto">
				<select name="tf_u_risco_classif" class="field_dados" class="form2">
					<option value="" <?php  if($tf_u_risco_classif == "") echo "selected" ?>>Selecione</option>
				<?php  for($i=1; $i < count($RISCO_CLASSIFICACAO_NOMES)+1; $i++){ ?>
					<option value="<?php  echo $RISCO_CLASSIFICACAO_NOMES[$i] ?>" <?php  if($tf_u_risco_classif == $RISCO_CLASSIFICACAO_NOMES[$i]) echo "selected"; ?>><?php  echo $RISCO_CLASSIFICACAO_NOMES[$i] ?></option>
				<?php  } ?>
				</select>
			</td>
		  </tr>

		  
		</table>

            <table class="table">
          <tr bgcolor="#F5F5FB"> 
            <td align="right"><input type="submit" name="BtnSearch" value="Buscar" class="btn btn-sm btn-info"></td>
          </tr>
          <?php if($msg != ""){?><tr class="texto"><td align="center"><br><br><font color="#FF0000"><?php echo $msg?></font></td></tr><?php }?>
		</table>
		</form>

		<?php if($total_table > 0) { ?>
        <table width="894" border="0" cellpadding="0" cellspacing="2">
                <tr bgcolor="#00008C"> 
                  <td height="11" colspan="3" bgcolor="#FFFFFF"> 
				  	<table width="100%" border='0' cellpadding="1" cellspacing="1" class="texto">
				  	  <tr> 
						<td colspan="20" class="texto"> 
                          Exibindo resultados <strong><?php echo $inicial + 1 ?></strong> 
                          a <strong><?php echo $reg_ate ?></strong> de <strong><?php echo $total_table ?></strong></font> 
                        </td>
					  </tr>
					  <?php $ordem = ($ordem == 1)?2:1; ?>
                      <tr bgcolor="#ECE9D8"> 
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=vg_id&inicial=".$inicial.$varsel ?>" class="link_branco" ><font class="texto">C&oacute;d.</a> 
                          <?php if($ncamp == 'vg_id') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=vg_data_inclusao&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Data Inclusão</font></a>
                          <?php if($ncamp == 'vg_data_inclusao') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=vg_data_inclusao&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Banco</font></a>
                          <?php if($ncamp == 'bbg_bco_codigo') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>

                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=vg_pagto_tipo&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Forma de<br>Pagamento</font></a>
                          <?php if($ncamp == 'vg_pagto_tipo') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <!--td align="center"><strong><font class="texto">Dados de pagamento</font></strong></td-->
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=valor&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Valor</font></a>
                          <?php if($ncamp == 'valor') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_id&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">C&oacute;d.<br>Usuário</font></a>
                          <?php if($ncamp == 'ug_id') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><font class="texto">Nome Fantasia</font></strong></td>
                        <td align="center"><strong><font class="texto">Conciliado?</font></strong></td>
                      </tr>
					<?php
						$cor1 = $query_cor1;
						$cor2 = $query_cor1;
						$cor3 = $query_cor2;
						while($rs_venda_row = pg_fetch_array($rs_venda)){
							$cor1 = ($cor1 == $cor2)?$cor3:$cor2;
							$status = $rs_venda_row['vg_ultimo_status'];
							$pagto_tipo = $rs_venda_row['vg_pagto_tipo'];

						
							$pagto_tipo_descr = ($pagto_tipo==$PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC)? $GLOBALS['FORMAS_PAGAMENTO_DESCRICAO']['A']:$GLOBALS['FORMAS_PAGAMENTO_DESCRICAO'][$pagto_tipo];
//							if($pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['DEP_DOC_TRANSF']) $pagto_tipo = "Transf, DOC, Dep";
//							elseif($pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO']) $pagto_tipo = "Boleto";
							if($pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO']) $pagto_tipo_descr .= " (Express LH)";
							$pagto_tipo_descr .= " (<b><font color='darkgreen'>'$pagto_tipo'</font></b>)";

							//total
							$total_valor += $rs_venda_row['valor'];

							$bol_conciliado = (($rs_venda_row['vg_ultimo_status'] == $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']) ? "<font color='#009900'>Conciliado</font>" : "<font color='#FF0000'>Não Conciliado</font>");

					?>
                      <tr bgcolor="<?php echo $cor1 ?>"> 
                        <td nowrap valign="top" class="texto" align="center"><a style="text-decoration:none" href="com_venda_detalhe.php?venda_id=<?php echo $rs_venda_row['bbg_vg_id'] ?>&fila_ncamp=<?php echo "bbg_vg_id"?>&fila_ordem=<?php echo ($ordem == 1?2:1) ?><?=$varsel?>"><?php echo $rs_venda_row['bbg_vg_id'] ?></a></td>
                        <td nowrap valign="top" class="texto" align="center"><?php echo formata_data_ts($rs_venda_row['vg_data_inclusao'],0, true,true) ?></td>
                        <td nowrap valign="top" class="texto" align="center"><?php echo $rs_venda_row['bbg_bco_codigo'] ?></td>

                        <td nowrap valign="top" class="texto"><?php echo $pagto_tipo_descr ?></td>
                        <td nowrap valign="top" class="texto" align="right"><?php echo number_format($rs_venda_row['valor'], 2, ',','.') ?></td>
                        <td nowrap valign="top" class="texto" align="center"><a href="/pdv/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $rs_venda_row['bbg_ug_id']?>">
						<?php echo $rs_venda_row['bbg_ug_id'] ?></a></td>
                        <td nowrap valign="top" class="texto"><a href="/pdv/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $rs_venda_row['bbg_ug_id']?>"><?php echo ((strtoupper($rs_venda_row['ug_tipo_cadastro']) == 'PF' )?$rs_venda_row['ug_nome']:$rs_venda_row['ug_nome_fantasia']) ?></a> (<?php echo $rs_venda_row['ug_tipo_cadastro']?>)</td>
                        <td nowrap valign="top" class="texto" align="center"><?php echo $bol_conciliado . " ('" . $status . "')" ?></td>
                      </tr>
					<?php 	}	?>
                      <tr bgcolor="E5E5EB"> 
                        <td class="texto" align="right" colspan="3"><b>Total:</b></td>
                        <td class="texto" align="right"><?php echo number_format($total_valor, 2, ',','.') ?></td>
                        <td class="texto" align="right" colspan="5"></td>
                      </tr>
                      <tr bgcolor="D5D5DB"> 
                        <td class="texto" align="right" colspan="3"><b>Total Geral:</b></td>
                        <td class="texto" align="right"><?php echo number_format($totalGeral_valor, 2, ',','.') ?></td>
                        <td class="texto" align="right" colspan="5"></td>
                      </tr>
                      <tr> 
                        <td colspan="20" bgcolor="#FFFFFF" class="texto"><?php echo $search_msg . number_format(getmicrotime() - $time_start, 2, '.', '.') . $search_unit ?></font></td>
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
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</html>
