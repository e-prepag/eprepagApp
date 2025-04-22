<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/main.php";
require_once $raiz_do_projeto."includes/gamer/constantes.php";
require_once "/www/includes/bourls.php";

if (b_IsBKOUsuarioComposicaoFifo()) {

$time_start_stats = getmicrotime();

//Explicativo
require_once $raiz_do_projeto."class/classDescriptionReport.php";;
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
<form id="form1" name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onsubmit="return VerificaMotivo();">
    <table class="table txt-preto fontsize-pp">
    <tr>
        <td valign="top">
            <table class="table" align="center">
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
                                                                                    $vetor_operadoras[$rs_operadoras_row['opr_codigo']]=$rs_operadoras_row['opr_nome'];
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
                                    <td colspan="4" align="center"><input name="btn_pesquisar" type="submit" id="btn_pesquisar" value="Pesquisar" class="btn btn-sm btn-info" /></td>
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
                 $where_saldo_nao_usado =" and (scf_data_deposito between '".converteData(addslashes($tf_v_data_inclusao_ini))." 00:00:00' and '".converteData(addslashes($tf_v_data_inclusao_fim))." 23:59:59')";
	}
	if (isset($dd_exclui_testes) ) {
		 $where .= " and idcliente not in (9093, 53916, 2745, 9845, 3468) \n";
	}
	if (!empty($tf_opr_codigo)) {
		$where_main .= " and vgm_opr_codigo = $tf_opr_codigo \n";
	}

	$sql  = "
		select sum(total_pagamento) as total,vgm_opr_codigo from (
		(
		select valorpagtogocash as total_pagamento,idvenda as venda, dataconfirma as data,total/100 as total_transacao,cesta,idcliente 
		from tb_pag_compras pag2
		where valorpagtogocash>0
			$where 
			and tipo_deposito = 0
		)
		) pagamento_gocash
		inner join tb_venda_games vg on vg.vg_id = pagamento_gocash.venda
		inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
		where vgm_opr_codigo NOT IN (".$dd_operadora_EPP_Cash.",".$dd_operadora_EPP_Cash_LH .")
		$where_main
		GROUP BY vgm_opr_codigo \n ";
	//echo str_replace("\n", "<br>\n", $sql)."<br>";
	$res_tmp = SQLexecuteQuery($sql);
	if ($res_tmp) {
		$total_table = pg_num_rows($res_tmp);
		$total_geral = 0;
		$total_geral_pedidos = 0;
		$vetor_calculo_financeiro = array();
		while ($res_tmp_row = pg_fetch_array ($res_tmp)) {
			$total_geral += $res_tmp_row['total'];
			$vetor_calculo_financeiro[$res_tmp_row['vgm_opr_codigo']]['VendasPagasGoCASH'] = $res_tmp_row['total'];
		}//end while
	}

	/*
        $max_reg = (($inicial + $max)>$total_table)?$total_table:$max;
        */
	//$sql .= " limit $max offset $inicial ";

	//if(b_IsUsuarioWagner()) { 
	//echo "<br><br>(R) ".str_replace("\n", "<br>\n", $sql)."<br><br>";
	//}

	//$rsResposta = SQLexecuteQuery($sql);
        
	$sql  = "
		select sum(total_pagamento) as total,vgm_opr_codigo from (
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
		GROUP BY vgm_opr_codigo \n ";
	
        //echo "<br><br>(R) ".str_replace("\n", "<br>\n", $sql)."<br><br>";

        $res_tmp = SQLexecuteQuery($sql);
	if ($res_tmp) {
		$total_table_saldo = pg_num_rows($res_tmp);
		$total_geral_saldo = 0;
		$total_geral_pedidos_saldo = 0;
		while ($res_tmp_row = pg_fetch_array ($res_tmp)) {
			$total_geral_saldo += $res_tmp_row['total'];
			$vetor_calculo_financeiro[$res_tmp_row['vgm_opr_codigo']]['SaldoUtilizadoGoCASH'] = $res_tmp_row['total'];
		}//end while
	}
	
        $sql = "select sum(scf_valor_disponivel) as total
                from saldo_composicao_fifo 
                where scf_canal = 'C'
                    ".$where_saldo_nao_usado;
        
        $rsSaldoNaoUtilizado = SQLexecuteQuery($sql);
        $rsSaldoNaoUtilizadoRow = pg_fetch_array ($rsSaldoNaoUtilizado);
        echo "Saldo através de depósito de GoCASH efetuado no período e não utilizado até o momento: R$ ".number_format($rsSaldoNaoUtilizadoRow['total'], 2, ',','.');
        //echo "<pre>".print_r($vetor_calculo_financeiro,true)."</pre>";
        reset($vetor_calculo_financeiro);
?>
<table class="table">
	<tr>
        <td align="center">&nbsp;</td>
        <td align="left" colspan="4">
		</td>
        <td align="center">&nbsp;</td>
    </tr>

<?php
	if((pg_num_rows($res_tmp) != 0) && ($res_tmp)) {
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
        <td>Publisher</td>
        <td><nobr>Saldo Utilizado (GoCASH)</nobr></td>
        <td><nobr>Vendas Pagas com GoCASH</nobr></td>
    </tr>
<?php
	$backcolor1 = "#ddffff";
	$backcolor2 = "#ffffff";
	$bck = $backcolor1;
	$vetor_exibicao = array();
	
	foreach ($vetor_calculo_financeiro as $publisher => $vetor) {
	?>
		<tr<?php echo " bgcolor='".$bck."'" ?>>
			 <td align="center"><nobr><?php echo $vetor_operadoras[$publisher];?></nobr></td>
			 <td align="right"><nobr>R$ <?php echo number_format($vetor['SaldoUtilizadoGoCASH'], 2, ',', '.');?></nobr></td>
			 <td align="right"><nobr>R$ <?php echo number_format($vetor['VendasPagasGoCASH'], 2, ',', '.')?></nobr></td>
		</tr>
	<?php
			if ($bck == $backcolor1)
				$bck = $backcolor2;
			else $bck = $backcolor1;
		} //end while ($pgResposta = pg_fetch_array ($rsResposta)) 
		
		?>
	<tr bgcolor="#DDDDDD" align="center" style="font-weight: bold">
        <td>Total <?php echo $total_table; ?></td>
        <td align="right">R$ <?php echo number_format($total_geral_saldo, 2, '.', '.'); ?></td>
        <td align="right">R$ <?php echo number_format($total_geral, 2, '.', '.'); ?></td>
    </tr>
	<tr>
		<td align="center"><nobr><?php
		
		$varse1 .= "&btn_pesquisar=Pesquisar";
	
		//paginacao_query($inicial, $total_table, $max, 50, $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varse1);

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
                   ATENÇÃO: Valores de PINs GoCASH foram contabilizados como R$ 17,00 cada PIN.
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