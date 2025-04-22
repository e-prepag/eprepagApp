<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/gamer/constantes.php";
require_once $raiz_do_projeto . "class/util/Util.class.php";
require_once "/www/includes/bourls.php";
//Explicativo
//require_once $raiz_do_projeto."/www/web/prepag2/incs/classDescriptionReport.php";
//$descricao = new DescriptionReport('historico_usuario');
//echo $descricao->MontaAreaDescricao();

?>
<link rel="stylesheet" type="text/css" href="/css/cssClassLista.css" />
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>
<script language="javascript">
$(function(){
    var optDate = new Object();
    optDate.interval = 10000;

    setDateInterval('tf_v_data_inclusao_ini','tf_v_data_inclusao_fim',optDate);

});

	function VerificaMotivo() {
		return true;
		var teste = true;
		if((document.form1.tf_v_data_inclusao_ini.value=="") && (document.form1.tf_v_data_inclusao_fim.value=="") && (document.form1.ug_id.value=="") && (document.form1.vg_id.value=="")) { 
			teste = false; 
		} 
		if(teste) return true;
		else {
			alert('Você deve informar ao menos um parametro de filtro!');
			return false;
		}
	}//end function VerificaMotivo()
	</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<form id="form1" name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onsubmit="return VerificaMotivo();">
<table width="99%" border="0" align="center">
    <tr>
        <td valign="top">
            <table class="table">
                <tr>
                    <td valign="top">
                        <table class="table">
                                <tr>
                                    <td colspan="4" bgcolor="#DDDDDD">&nbsp;&nbsp;Dados da Consulta</td>
                                </tr>
                                <tr>
									<td align="right">Data da Geração: </font></td>
									<td align="left">
										<input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php if(isset($tf_v_data_inclusao_ini)) echo $tf_v_data_inclusao_ini ?>" size="11" maxlength="10">
										&nbsp;&agrave;&nbsp; 
										<input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php if(isset($tf_v_data_inclusao_fim)) echo $tf_v_data_inclusao_fim ?>" size="11" maxlength="10">
									</td>
                                    <td align="right">ID do Usuário: </td>
                                    <td>
										<input name="ug_id" type="text" id="ug_id" size="20" value="<?php if(isset($ug_id)) echo $ug_id;?>"/>
									</td>
                                </tr>
								<tr>
                                    <td align="right">Situação do Token: </td>
                                    <td>
										<select name="cc_status" id="cc_status">
											<option value="0">Ainda não confirmado</option>
											<option value="1">Confirmado</option>
											<option value="2">Cancelado pelo operador</option>
										</select>
									</td>
                                    <td align="right">ID da Venda: </td>
                                    <td>
										<input name="vg_id" type="text" id="vg_id" size="20" value="<?php if(isset($vg_id)) echo $vg_id;?>"/>
									</td>
                                </tr>
								<tr>
                                    <td align="right"></td>
                                    <td></td>
                                    <td align="right">&nbsp;</td>
                                    <td>
										<input type="checkbox" name="dd_exclui_testes" <?php echo ((isset($dd_exclui_testes))?" checked":"")?> value="1"> Exclui usuários de testes (Reynaldo, Wagner e Fabio)
									</td>
                                </tr>
                                <tr>
                                    <td colspan="4" align="center"><input name="btn_pesquisar" class="btn btn-info btn-sm" type="submit" id="btn_pesquisar" value="Pesquisar" /></td>
                                </tr>
                            </table>
                    </td>
                </tr>
                <tr align="center">
                <td>
<p align="center">
<?php
if (isset($btn_pesquisar) && $btn_pesquisar=="Pesquisar") {
        
	$sql  = "SELECT *,to_char(cc_data_inclusao,'DD/MM/YYYY HH24:MI:SS') as cc_data_inclusao_aux ";
	$sql .= "FROM codigo_confirmacao \n";
	if (!empty($vg_id)) {
		 $filtros[] = "cc_vg_id in (". $vg_id . ")";
	}
	if (!empty($ug_id)) {
		 $filtros[] = "cc_ug_id in (". $ug_id . ")";
	}
	if (!empty($cc_status)) {
		 $filtros[] = "cc_status in (". $cc_status . ")";
	}
	if(strlen($tf_v_data_inclusao_ini) && strlen($tf_v_data_inclusao_fim)) {
		 $filtros[] = "(cc_data_inclusao between '".Util::getData($tf_v_data_inclusao_ini,true)." 00:00:00' AND '".Util::getData($tf_v_data_inclusao_fim, true)." 23:59:59') \n";
	}
	if (isset($dd_exclui_testes) ) {
		 $filtros[] = "cc_ug_id not in (9093, 53916, 2745)";
	}
	if (is_array($filtros)) {
		$sql .= ' WHERE ' . implode(' AND ', $filtros);
	}
	$sql .= " ORDER BY cc_data_inclusao DESC\n";
echo $sql;
	$rsResposta = SQLexecuteQuery($sql);
?>
<table class="table">
	<tr>
        <td align="center">&nbsp;</td>
        <td align="left" colspan="4"><?php if(isset($stotal)) echo $stotal?>
		</td>
        <td align="center">&nbsp;</td>
    </tr>

<?php
	if($rsResposta && (pg_num_rows($rsResposta) > 0)) {
?>
	<tr>
        <td align="center">&nbsp;</td>
        <td align="left" colspan="4"><?php echo "Encontrado".((pg_num_rows($rsResposta)>0)?"s":"")." ".pg_num_rows($rsResposta)." registro".((pg_num_rows($rsResposta)>0)?"s":"")."";?>
		</td>
        <td align="center">&nbsp;</td>
    </tr>
	<tr>
        <td bgcolor="#DDDDDD" align="center">Data</td>
        <td bgcolor="#DDDDDD" align="center">Tipo Usuário</td>
        <td bgcolor="#DDDDDD" align="center">Token</td>
        <td bgcolor="#DDDDDD" align="center">Status</td>
        <td bgcolor="#DDDDDD" align="center">ID da Venda</td>
        <td bgcolor="#DDDDDD" align="center">ID do usuário</td>
        <td bgcolor="#DDDDDD" align="center">Tipo Pagamento</td>
    </tr>
<?php

	$backcolor1 = "#ddffff";
	$backcolor2 = "#ffffff";
	$bck = $backcolor1;
		while ($pgResposta = pg_fetch_array ($rsResposta)) {

	?>
		<tr<?php echo " bgcolor='".$bck."'" ?>>
			 <td align="center"><nobr><?php echo $pgResposta['cc_data_inclusao_aux'];?></nobr></td>
			 <td align="center"><nobr><?php echo $pgResposta['cc_tipo_usuario'];?></nobr></td>
			 <td align="center"><nobr><?php echo $pgResposta['cc_codigo'];?></nobr></td>
			 <td align="center"><nobr><?php echo $pgResposta['cc_status'];?></nobr></td>
			 
			<?php
				// pagamentos da integração de PINs EPP Cash geram um IDVenda próprio que não existe na tabela de vendas e não têm IDUsuario
			?>
			 <td align="center"><nobr><?php if ($pgResposta['cc_vg_id'] && ($pgResposta['cc_ug_id']>0)) echo "<a href='/gamer/vendas/com_venda_detalhe.php?venda_id=".$pgResposta['cc_vg_id']."' target='_blank'>" ?><?php echo $pgResposta['cc_vg_id'];?><?php if ($pgResposta['cc_vg_id']) echo "</a>" ?></nobr></td>

			 <td align="center"><nobr><?php if ($pgResposta['cc_ug_id']) echo "<a href='/gamer/usuarios/com_usuario_detalhe.php?usuario_id=".$pgResposta['cc_ug_id']."' target='_blank'>" ?><?php echo $pgResposta['cc_ug_id'];?><?php if ($pgResposta['cc_ug_id']) echo "</a>" ?></nobr></td>

			 <td align="center"><nobr><?php echo $pgResposta['cc_tipo_pagamento'];?></nobr></td>

		</tr>
	<?php
			if ($bck == $backcolor1)
				$bck = $backcolor2;
			else $bck = $backcolor1;
		} //end while ($pgResposta = pg_fetch_array ($rsResposta)) 
	} else {
		?>
			<tr<?php if(isset($bck)) echo " bgcolor='".$bck."'" ?>>
				<td align="center" colspan="7">&nbsp;<font color="red">Sem registros encontrados</font></td>
			</tr>
		<?php
	}
}//end if ($btn_pesquisar=="Pesquisar")
?>
</table>
</p>
               </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>