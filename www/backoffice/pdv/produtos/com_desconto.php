<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";

	//Validacao
	//------------------------------------------------------------------------------------------------------------------
	$msg = "";

	//Processa Acoes
	if($msg == ""){

		//excluir desconto
		if($acao && $acao == "e"){

			//Validacao
			if(!$des_id || !is_numeric($des_id)) $msg = "Código do desconto inválido.\n";

			//exclui
			if($msg == ""){			
				$sql = "delete from tb_dist_descontos where des_id = $des_id ";
				SQLexecuteQuery($sql);
			}
		}

	}


	//Mostra a pagina
	//----------------------------------------------------------------------------------------------------------
	//Recupera desconto global
	if($msg == ""){
		$sql  = "select * from tb_dist_descontos des 
				 where des.des_opr_codigo = 0 and des.des_vg_pagto_tipo = 0 and des.des_ug_id = 0"; 
		$rs_global = SQLexecuteQuery($sql);
		if(!$rs_global || pg_num_rows($rs_global) != 1){
			$des_id_global = 0;
			$des_perc_desconto_global = 0;
		} else {
			$rs_global_row = pg_fetch_array($rs_global);
			$des_id_global = $rs_global_row['des_id'];
			$des_perc_desconto_global = $rs_global_row['des_perc_desconto'];
		}
		
	}

?>
<script language="javascript">
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
<table class="table fontsize-pp txt-preto">
  <tr> 
    <td  valign="top"> 
		
	<?php if($msg != ""){?>
        <table class="table">
          <tr><td align="center"><font color="#FF0000" size="2" face="Arial, Helvetica, sans-serif"><?php echo str_replace("\n", "<br>", $msg) ?></font></td></tr>
		</table>
	<?php }?>


        <table class="table w150">
          <tr bgcolor="#FFFFFF"> 
            <td colspan="2" bgcolor="#ECE9D8" align="center"><b>Desconto Default Global</b></td>
          </tr>
          <tr bgcolor="#F5F5FB">
                <td align="center"><b>Desconto</b></td>
                <td align="center">
                  <a class="link_azul" href="#" Onclick="window.open('com_desconto_selecao.php?opr_nome=&des_opr_codigo=0&perc_desconto=<?=urlencode(number_format($des_perc_desconto_global, 2, ',', '.')) ?>&des_vg_pagto_tipo=0&des_id=<?=$des_id_global?>','selecao', 'status=0,width=500,height=200,top=0,left=0');return false;"><?=number_format($des_perc_desconto_global, 2, ',', '.')?>%</a>&nbsp;
                </td>
		  </tr>
		</table>

        <table width="100%" border="0" cellpadding="0" cellspacing="0" class="texto">
          <tr bgcolor="#FFFFFF"> 
            <td width="30%" bgcolor="#ECE9D8" align="center"><b>Desconto Default por Operadora</b></td>
            <td width="10%">&nbsp;</td>
            <td width="60%" bgcolor="#ECE9D8" align="center"><b>Desconto Default por Forma de Pagamento</b></td>
          </tr>
          <tr bgcolor="#FFFFFF"> 
            <td valign="top">
		        <table width="100%" border="0" cellpadding="0" cellspacing="0" class="texto">
		          <tr>
				  	<td>
					<?php
						//Recupera desconto por operadora
						if($msg == ""){
							$sql  = "select opr.opr_codigo, opr.opr_nome, des.*
									 from operadoras opr
									 left join tb_dist_descontos des on opr.opr_codigo = des.des_opr_codigo
									 	and des.des_opr_codigo <> 0 and des.des_vg_pagto_tipo = 0 and des.des_ug_id = 0
									 where opr.opr_status = '1'
									 order by opr.opr_nome";
							$rs_porOperadora = SQLexecuteQuery($sql);
						}
					?>
						<table border="0" cellpadding="0" cellspacing="1" width="100%" align="center">
							<tr bgcolor="#F5F5FB" class="texto">
							  <td align="center"><b>Operadora</b></td>
							  <td align="center"><b>Desconto</b></td>
							  <td align="center"><img src="/images/deletar.gif"></td>
							</tr>
				<?php
						if($rs_porOperadora){
						while ($rs_porOperadora_row = pg_fetch_array($rs_porOperadora)){
							$des_perc_desconto = $rs_porOperadora_row['des_perc_desconto'];
				?>
							<tr class="texto" bgcolor="#F5F5FB">
							  <td>&nbsp;<?=$rs_porOperadora_row['opr_nome']?></td>
							  <td align="right">
							  <?php if(!is_null($des_perc_desconto)){ ?>
								<a class="link_azul" href="#" Onclick="window.open('com_desconto_selecao.php?opr_nome=<?=urlencode($rs_porOperadora_row['opr_nome'])?>&des_opr_codigo=<?=$rs_porOperadora_row['opr_codigo']?>&perc_desconto=<?=urlencode(number_format($des_perc_desconto, 2, ',', '.')) ?>&des_vg_pagto_tipo=0&des_id=<?=$rs_porOperadora_row['des_id']?>','selecao', 'status=0,width=500,height=200,top=0,left=0');return false;"><?=number_format($des_perc_desconto, 2, ',', '.')?>%</a>&nbsp;
							  <?php } else {?>
								<a class="link_azul" href="#" Onclick="window.open('com_desconto_selecao.php?opr_nome=<?=urlencode($rs_porOperadora_row['opr_nome'])?>&des_opr_codigo=<?=$rs_porOperadora_row['opr_codigo']?>&perc_desconto=<?=urlencode("0,00") ?>&des_vg_pagto_tipo=0&des_id=0','selecao', 'status=0,width=500,height=200,top=0,left=0');return false;">Inserir</a>&nbsp;
							  <?php } ?>
							  </td>
							  <td align="center">
							  <?php if(!is_null($des_perc_desconto)){ ?>
								<a class="link_azul" Onclick="return confirm('Deseja excluir este desconto ?');" href="?acao=e&des_id=<?=$rs_porOperadora_row['des_id']?>" title="Excluir desconto"><img src="../../images/deletar.gif" border="0"></a>
							  <?php } else {?>
								&nbsp;
							  <?php } ?>
							  </td>
							</tr>
                        <?php	}} ?>
						</table>
					</td>
				  </tr>
				</table>
            </td>
            <td>&nbsp;</td>
            <td valign="top">
		        <table width="100%" border="0" cellpadding="0" cellspacing="0" class="texto">
		          <tr>
				  	<td>
				        <table width="100%" border="0" cellpadding="0" cellspacing="0" class="texto">
				          <tr>
							<?php 
								//Recupera desconto por forma de pagamento
								foreach ($GLOBALS['FORMAS_PAGAMENTO_DESCRICAO'] as $formaId => $formaNome){
									$sql  = "select opr.opr_codigo, opr.opr_nome, des.*
											 from operadoras opr
											 left join tb_dist_descontos des on opr.opr_codigo = des.des_opr_codigo
											 	and des.des_opr_codigo <> 0 and des.des_vg_pagto_tipo = ".(($formaId == $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BANCO_ITAU_ONLINE'])?$PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC:(($formaId==$GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIX'])?$GLOBALS['PAGAMENTO_PIX_NUMERIC']:$formaId))." and des.des_ug_id = 0
											 where opr.opr_status = '1'
											 order by opr.opr_nome";
									$rs_porFormaPagto = SQLexecuteQuery($sql);
						  	?>
						  	<td valign="top">
								<table border="0" cellpadding="0" cellspacing="1" width="100%" align="center">
									<tr bgcolor="#ECE9D8" class="texto"> 
										<td colspan="3" align="center"><b><?=$formaNome?></b></td>
									</tr>
									<tr bgcolor="#F5F5FB" class="texto">
									  <td align="center"><b>Operadora</b></td>
									  <td align="center"><b>Desconto</b></td>
									  <td align="center"><img src="../../images/deletar.gif"></td>
									</tr>
							<?php
									if($rs_porFormaPagto){
									while ($rs_porFormaPagto_row = pg_fetch_array($rs_porFormaPagto)){
                                        
										$des_perc_desconto = $rs_porFormaPagto_row['des_perc_desconto'];
							?>
									<tr class="texto" bgcolor="#F5F5FB">
									  <td>&nbsp;<?=$rs_porFormaPagto_row['opr_nome']?></td>
									  <td align="right">
									  <?php if(!is_null($des_perc_desconto)){ ?>
										<a class="link_azul" href="#" Onclick="window.open('com_desconto_selecao.php?opr_nome=<?=urlencode($rs_porFormaPagto_row['opr_nome'])?>&des_opr_codigo=<?=$rs_porFormaPagto_row['opr_codigo']?>&perc_desconto=<?=urlencode(number_format($des_perc_desconto, 2, ',', '.')) ?>&des_vg_pagto_tipo=<?=$formaId?>&des_id=<?=$rs_porFormaPagto_row['des_id']?>','selecao', 'status=0,width=500,height=200,top=0,left=0');return false;"><?=number_format($des_perc_desconto, 2, ',', '.')?>%</a>&nbsp;
									  <?php } else {?>
										<a class="link_azul" href="#" Onclick="window.open('com_desconto_selecao.php?opr_nome=<?=urlencode($rs_porFormaPagto_row['opr_nome'])?>&des_opr_codigo=<?=$rs_porFormaPagto_row['opr_codigo']?>&perc_desconto=<?=urlencode("0,00") ?>&des_vg_pagto_tipo=<?=$formaId?>&des_id=0','selecao', 'status=0,width=500,height=200,top=0,left=0');return false;">Inserir</a>&nbsp;
									  <?php } ?>
									  </td>
									  <td align="center">
									  <?php if(!is_null($des_perc_desconto)){ ?>
										<a class="link_azul" Onclick="return confirm('Deseja excluir este desconto ?');" href="?acao=e&des_id=<?=$rs_porFormaPagto_row['des_id']?>" title="Excluir desconto"><img src="../../images/deletar.gif" border="0"></a>
									  <?php } else {?>
										&nbsp;
									  <?php } ?>
									  </td>
									</tr>
                                    <?php		}} ?>
								</table>
							</td>						
							<?php } ?>
								
						  </tr>
						</table>
					</td>
				  </tr>
				</table>
            </td>
          </tr>

    </td>
  </tr>
</table>
<?php require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php"; ?>
</html>
