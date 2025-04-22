<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/main.php";
require_once $raiz_do_projeto."includes/gamer/constantes.php";
if (b_IsBKOUsuarioComposicaoFifo()) {

$time_start_stats = getmicrotime();

//Explicativo
require_once $raiz_do_projeto."class/classDescriptionReport.php";
//$descricao = new DescriptionReport('historico_usuario');
//echo $descricao->MontaAreaDescricao();

	if(!$inicial)  $inicial     = 0;
	if(!$range)    $range       = 1;
	if(!$ordem)    $ordem       = 0;
	if($Pesquisar) $total_table = 0;
	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
	$img_anterior = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
	$max          = 50; //$qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;
	$registros	  = $max;
	
	if (!empty($vg_id)) {
		$varse1 .= "&vg_id=$vg_id";
	}
	if (!empty($ug_id)||$ug_id=='0') {
		$varse1 .= "&ug_id=$ug_id";
	}
	if(!empty($tf_v_data_inclusao_ini)) {
		$varse1 .= "&tf_v_data_inclusao_ini=$tf_v_data_inclusao_ini";
	}
	if(!empty($tf_v_data_inclusao_fim)) {
		$varse1 .= "&tf_v_data_inclusao_fim=$tf_v_data_inclusao_fim";
	}
	if (isset($dd_exclui_testes) ) {
		$varse1 .= "&dd_exclui_testes=$dd_exclui_testes";
	}
	if (!empty($tf_opr_codigo)) {
		$varse1 .= "&tf_opr_codigo=$tf_opr_codigo";
	}

	require_once ($raiz_do_projeto . "class/classIntegracaoPinCash.php");
	$operacao_array = VetorIntegrator();
	require_once "/www/includes/bourls.php";
?>
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
<form id="form1" name="form1" method="post" onsubmit="return VerificaMotivo();">
<table class="table txt-preto fontsize-pp">
    <tr>
        <td valign="top">
            <table class="table">
                <tr>
                    <td valign="top">
                        <table class="table">
                                <tr>
                                    <td colspan="4" bgcolor="#DDDDDD">&nbsp;&nbsp;Dados do pagamento</td>
                                </tr>
                                <tr>
									<td align="right">Data da Ação: </font></td>
									<td align="left">
										<input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php echo $tf_v_data_inclusao_ini ?>" size="11" maxlength="10">
										&nbsp;&agrave;&nbsp; 
										<input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php echo $tf_v_data_inclusao_fim ?>" size="11" maxlength="10">
									</td>
                                    <td align="right">ID do Usuário: </td>
                                    <td>
										<input name="ug_id" type="text" id="ug_id" size="20" value="<?php echo $ug_id;?>"/>
									</td>
                                </tr>
								<tr>
                                    <td align="right"></td>
                                    <td>
									</td>
                                    <td align="right">ID da Venda: </td>
                                    <td>
										<input name="vg_id" type="text" id="vg_id" size="20" value="<?php echo $vg_id;?>"/>
									</td>
                                </tr>
								<tr>
                                    <td align="right">Publisher:</td>
                                    <td>
									<select name="tf_opr_codigo" id="tf_opr_codigo" class="form2">
										<option value="" <?php if($tf_opr_codigo == "") echo "selected" ?>>Selecione</option>
										<?php 
										$sql = "select * from operadoras ope where opr_status = '1' and opr_codigo NOT IN (".$dd_operadora_EPP_Cash.",".$dd_operadora_EPP_Cash_LH .") order by opr_nome";
										$rs_operadoras = SQLexecuteQuery($sql);
										while($rs_operadoras_row = pg_fetch_array($rs_operadoras))
										{
										?>
										<option value="<?php echo $rs_operadoras_row['opr_codigo']; ?>"
										<?php 
											if ($tf_opr_codigo == $rs_operadoras_row['opr_codigo'] || $rs_operadoras_row['opr_codigo'] == $buscaOper) 
												echo " selected";
										?>><?php echo $rs_operadoras_row['opr_nome']; ?></option>
										<?php } ?>
									</select>
									</td>
                                    <td align="right" colspan="2">&nbsp;
										<input type="checkbox" name="dd_exclui_testes" <?php echo ((isset($dd_exclui_testes))?" checked":"")?> value="1"> Exclui usuários de testes (Reynaldo, Wagner, Fabio e Glaucia)
									</td>
                                </tr>
                                <tr>
                                    <td colspan="4" align="center"><input name="btn_pesquisar" type="submit" id="btn_pesquisar" value="Pesquisar" /></td>
                                </tr>
                            </table>
                    </td>
                </tr>
                <tr align="center">
                <td>
<p align="center">
<?php
if ($btn_pesquisar=="Pesquisar") {

//echo "dd_exclui_testes (".((isset($dd_exclui_testes))?"ON":"off")."): <pre>".print_r($dd_exclui_testes, true)."</pre><br>";
	$where = "";
	$where_main = "";
	if (!empty($vg_id)) {
		 $where .= " and idvenda in (". $vg_id . ") \n";
	}
	if (!empty($ug_id)||$ug_id=='0') {
		 $where .= " and idcliente in (". $ug_id . ") \n";
	}
	if(strlen($tf_v_data_inclusao_ini) && strlen($tf_v_data_inclusao_fim)) {
		 $where .= " and (dataconfirma between '".converteData(addslashes($tf_v_data_inclusao_ini))." 00:00:00' and '".converteData(addslashes($tf_v_data_inclusao_fim))." 23:59:59') \n";
	}
	if (isset($dd_exclui_testes) ) {
		 $where .= " and idcliente not in (9093, 53916, 2745, 9845, 3468) \n";
	}
	if (!empty($tf_opr_codigo)) {
		$where_main .= " and vgm_opr_codigo = $tf_opr_codigo \n";
	}

	$sql  = "
		select total_pagamento,venda,data,total_transacao,cesta,idcliente from (
		(
		select valorpagtogocash as total_pagamento,idvenda as venda, dataconfirma as data,total/100 as total_transacao,cesta,idcliente 
		from tb_pag_compras pag2
		where valorpagtogocash>0
			$where 
			and tipo_deposito = 0
		)
		union all 
		(
		select scfu_valor as total_pagamento,idvenda as venda, dataconfirma as data,total/100 as total_transacao,cesta,idcliente 
		from tb_pag_compras pag2
			left outer join ( 
				(select sum(scfu_valor) as scfu_valor, count(*) as scfu_qtde, vg_id, scf_canal 
				from ( 
					select scfu_valor, scfu.vg_id, scf.scf_canal 
					from saldo_composicao_fifo_utilizado scfu 
					INNER JOIN saldo_composicao_fifo scf ON (scfu.scf_id=scf.scf_id) 
				) scfu_int 
				group by vg_id, scf_canal 
				) 
			) scfu on scfu.vg_id = pag2.idvenda 
		where valorpagtosaldo>0
			$where	
			and scf_canal = 'C'
		)
		) pagamento_gocash
		inner join tb_venda_games vg on vg.vg_id = pagamento_gocash.venda
		inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
		where vgm_opr_codigo NOT IN (".$dd_operadora_EPP_Cash.",".$dd_operadora_EPP_Cash_LH .")
		$where_main
		group by total_pagamento,venda,data,total_transacao,cesta,idcliente";
	//echo str_replace("\n", "<br>\n", $sql)."<br>";
	$res_tmp = SQLexecuteQuery($sql);
	if ($res_tmp) {
		$total_table = pg_num_rows($res_tmp);
		$total_geral = 0;
		$total_geral_pedidos = 0;
		$vetor_calculo_financeiro = array();
		while ($res_tmp_row = pg_fetch_array ($res_tmp)) {
			$total_geral += $res_tmp_row['total_pagamento'];
			if (!in_array($res_tmp_row['venda'], $vetor_calculo_financeiro)) {
				$total_geral_pedidos += $res_tmp_row['total_transacao'];
				$vetor_calculo_financeiro[] = $res_tmp_row['venda'];
			}
		}//end while
	}

	$max_reg = (($inicial + $max)>$total_table)?$total_table:$max;

	$sql .= " ORDER BY data DESC\n ";
	$sql .= " limit $max offset $inicial ";

	//if(b_IsUsuarioWagner()) { 
	//echo "<br><br>(R) ".str_replace("\n", "<br>\n", $sql)."<br><br>";
	//}

	$rsResposta = SQLexecuteQuery($sql);
?>
<table class="table">
	<tr>
        <td align="center">&nbsp;</td>
        <td align="left" colspan="4">
		</td>
        <td align="center">&nbsp;</td>
    </tr>

<?php
	if((pg_num_rows($rsResposta) != 0) && ($rsResposta)) {
?>
	<tr>
        <td align="center">&nbsp;</td>
        <td align="left" colspan="4"><b>Pesquisa <b><?php 
			echo " (".$total_table." registro"; 
			if($total_table>1) echo "s"; 
			echo ")"?></b>
		</td>
        <td align="center">&nbsp;</td>
    </tr>
	<tr bgcolor="#DDDDDD" align="center">
        <td>Data</td>
        <td><nobr>ID da Venda</nobr></td>
        <td><nobr>ID do usuário</nobr></td>
        <td><nobr>Total do Pedido</nobr></td>
        <td>Cesta</td>
        <td><nobr>Total pago com GoCASH</nobr></td>
    </tr>
<?php
	$backcolor1 = "#ddffff";
	$backcolor2 = "#ffffff";
	$bck = $backcolor1;
	$vetor_exibicao = array();
	
	while ($pgResposta = pg_fetch_array ($rsResposta)) {

	?>
		<tr<?php echo " bgcolor='".$bck."'" ?>>
			 <td align="center"><nobr><?php echo substr($pgResposta['data'],0,19);?></nobr></td>
			 <td align="center"><nobr><?php if (!in_array($pgResposta['venda'], $vetor_exibicao)) { if ($pgResposta['venda'] && ($pgResposta['venda']>0)) echo "<a href='/gamer/vendas/com_venda_detalhe.php?venda_id=".$pgResposta['venda']."' target='_blan'>" ?><?php echo $pgResposta['venda'];?><?php if ($pgResposta['venda']) echo "</a>"; ?></nobr></td>
			 <td align="center"><nobr><?php if ($pgResposta['idcliente']) echo "<a href='/gamer/usuarios/com_usuario_detalhe.php?usuario_id=".$pgResposta['idcliente']."' target='_blan'>" ?><?php echo $pgResposta['idcliente'];?><?php if ($pgResposta['idcliente']) echo "</a>" ?></nobr></td>
			 <td align="center"><nobr><?php echo number_format($pgResposta['total_transacao'], 2, '.', '.');?></nobr></td>
			 <td align="center"><nobr><?php echo $pgResposta['cesta'];?></nobr></td>
			 <?php 
				$vetor_exibicao[]=$pgResposta['venda']; 
			 }//end if array 
			 else {
			 ?>
			 <td align="center"><nobr>&nbsp;</nobr></td>
			 <td align="center"><nobr>&nbsp;</nobr></td>
			 <td align="center"><nobr>&nbsp;</nobr></td>
			 <?php } //end else do if array ?>
			 <td align="center"><nobr><?php echo number_format($pgResposta['total_pagamento'], 2, '.', '.');?></nobr></td>
		</tr>
	<?php
			if ($bck == $backcolor1)
				$bck = $backcolor2;
			else $bck = $backcolor1;
		} //end while ($pgResposta = pg_fetch_array ($rsResposta)) 
		
		?>
	<tr bgcolor="#DDDDDD" align="center" style="font-weight: bold">
        <td>Total</td>
        <td><?php echo $total_table; ?></td>
        <td align="right">Total Pedidos</td>
        <td><?php echo number_format($total_geral_pedidos, 2, '.', '.'); ?></td>
        <td align="right">Valor Total de Pagamento com GoCASH</td>
        <td><?php echo number_format($total_geral, 2, '.', '.'); ?></td>
    </tr>
	<tr>
		<td align="center"><nobr><?php
		
		$varse1 .= "&btn_pesquisar=Pesquisar";
	
		paginacao_query($inicial, $total_table, $max, 50, $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varse1);

		?></nobr></td>
	</tr>
	<?php
	} else {
		?>
			<tr<?php echo " bgcolor='".$bck."'" ?>>
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
	<tr>
		<td class="texto" align="center" colspan="7">
		<?php
		echo "Elapsed time: ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')."\n";
		?>
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?php
} // end if (b_IsBKOUsuarioComposicaoFifo())
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>