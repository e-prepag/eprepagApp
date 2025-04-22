<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/main.php";
require_once $raiz_do_projeto."includes/gamer/constantes.php";
set_time_limit(6000);

//if (b_IsBKOUsuarioComposicaoFifo()) {
	
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
	$max          = 10000; //$qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;
	$registros	  = $max;
	
	if (!empty($vg_id)) {
		$varse1 .= "&vg_id=$vg_id";
	}
	if (!empty($ug_id)||$ug_id=='0') {
		$varse1 .= "&ug_id=$ug_id";
	}
	if (!empty($pin_no)) {
		$varse1 .= "&pin_no=$pin_no";
	}
	if (!empty($valor_pin)) {
		$varse1 .= "&valor_pin=$valor_pin";
	}
	if (!empty($order_no)) {
		$varse1 .= "&order_no=$order_no";
	}
	if(!empty($tf_v_data_inclusao_ini)) {
		$varse1 .= "&tf_v_data_inclusao_ini=$tf_v_data_inclusao_ini";
	}
	if(!empty($tf_v_data_inclusao_fim)) {
		$varse1 .= "&tf_v_data_inclusao_fim=$tf_v_data_inclusao_fim";
	}
	if(!empty($pin_operacao)||($pin_operacao=='0')) {
		$varse1 .= "&pin_operacao=$pin_operacao";
	}
	if(!empty($lote_16)) {
		$varse1 .= "&lote_16=$lote_16";
	}
	if (isset($dd_exclui_testes) ) {
		$varse1 .= "&dd_exclui_testes=$dd_exclui_testes";
	}
	if (isset($dd_seul) ) {
		$varse1 .= "&dd_seul=$dd_seul";
	}


	if(!($lote_16=="T" || $lote_16=="L16" || $lote_16=="L17")) {
		$lote_16 = "T";
	}


	$stotal = "";
	$sql_n = "SELECT (
                        SELECT 
                            COUNT(*) 
                        FROM 
                            pins_gocash pgc 
                        WHERE 
                            COALESCE(
                                (
                                    SELECT 
                                        pgcl_id 
                                    FROM 
                                        pins_gocash_lote16 pgcl 
                                    WHERE 
                                        pgcl.pgcl_pin_number = pgc.pgc_pin_number
                                ),0
                            )>0
                    )	as used_lote_16 , 
                    (
                        SELECT 
                            COUNT(*) 
                        FROM 
                            pins_gocash_lote16
                    ) AS total_lote_16 ";
    
//echo "$sql_n<br>";

	$rsperc = SQLexecuteQuery($sql_n);
	if((pg_num_rows($rsperc) != 0) && ($rsperc)) {
		$pgperc = pg_fetch_array ($rsperc);
		$stotal = "Percentagem de utilização do estoque de 16 chars: ".$pgperc['used_lote_16']." de ".$pgperc['total_lote_16']." registros (".number_format(100*$pgperc['used_lote_16']/$pgperc['total_lote_16'], 2, '.', '.')."%)";
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
                                    <td align="right">PIN No.</td>
                                    <td>
										<input name="pin_no" type="text" id="pin_no" size="17" value="<?php echo $pin_no;?>"/>
									</td>
                                    <td align="right">Valor: </td>
                                    <td>
										<input name="valor_pin" type="text" id="valor_pin" size="10" maxlength="10" value="<?php echo $valor_pin;?>"/>
									</td>
                                </tr>
								<tr>
                                    <td align="right">Order No.: </td>
                                    <td>
										<input name="order_no" type="text" id="order_no" size="20" value="<?php echo $order_no;?>"/>
									</td>
                                    <td align="right">ID da Venda: </td>
                                    <td>
										<input name="vg_id" type="text" id="vg_id" size="20" value="<?php echo $vg_id;?>"/>
									</td>
                                </tr>
								<tr>
                                    <td align="right">Utilizador: </td>
                                    <td>
										<select name="pin_operacao" id="pin_operacao" class="combo_normal">
											<option value=''<?php if(empty($pin_operacao)&&($pin_operacao<>'0')) echo "selected"?>>Todas</option>
											<option value='0'<?php if(($pin_operacao=='0')) echo "selected"?>>Utilizado na Loja</option>
											<?php foreach ($operacao_array as $key => $value) { ?>
											<option value=<?php echo "\"".$key.(($pin_operacao==$key)?"\" selected":"\""); ?>><?php echo $value; ?></option>
											<?php } ?>
										</select>
									</td>
                                    <td align="right" colspan="2">&nbsp;
										<input type="checkbox" name="dd_seul" id="dd_seul" <?php echo ((isset($dd_seul))?" checked":"")?> value="1"> Considerar horário de Seoul na geração do relatório</td>
                                    <td></td>
                                </tr>
								<tr>
                                    <td align="right">Filtro por lotes: </td>
                                    <td>										
										<select name="lote_16" id="lote_16">
											<option value="T"<?php echo (($lote_16=="T")?" selected":"") ?>>Todos os PINs Gocash</option>
											<option value="L17"<?php echo (($lote_16=="L17")?" selected":"") ?>>Apenas PINs Gocash de 17 chars (normais)</option>
											<option value="L16"<?php echo (($lote_16=="L16")?" selected":"") ?>>Apenas PINs Gocash de Lote 16 chars</option>
										</select>
									</td>
                                    <td align="right" colspan="2">&nbsp;
										<input type="checkbox" name="dd_exclui_testes" <?php echo ((isset($dd_exclui_testes))?" checked":"")?> value="1"> Exclui usuários de testes (Reynaldo, Wagner, Fabio e Glaucia)
									</td>
                                </tr>
                                <tr>
                                    <td colspan="4" align="center"><input name="btn_pesquisar" class="btn btn-sm btn-info" type="submit" id="btn_pesquisar" value="Pesquisar" /></td>
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

	if (isset($dd_seul) ) {
		 $aux_diff_horas= "12";
	}
	else {
		 $aux_diff_horas= "0";
	}
	$sql  = "SELECT * ";
	$sql .= ", coalesce((select pgcl_id from pins_gocash_lote16 pgcl where pgcl.pgcl_pin_number = pgc.pgc_pin_number),0) as b_is_lote16 \n";
	$sql .= "FROM pins_gocash pgc \n";
	$sql .= "WHERE 1=1 \n";
	if (!empty($vg_id)) {
		 $sql .= " AND pgc_vg_id in (". $vg_id . ") \n";
	}
	if (!empty($ug_id)||$ug_id=='0') {
		 $sql .= " AND pgc_ug_id in (". $ug_id . ") \n";
	}
	if (!empty($pin_no)) {
		 $sql .= " AND pgc_pin_number like '%". $pin_no . "%' \n";
	}
	if (!empty($valor_pin)) {
		 $sql .= " AND pgc_face_amount = ". $valor_pin . " \n";
	}
	if (!empty($order_no)) {
		 $sql .= " AND pgc_order_no in (". $order_no . ") \n";
	}
	if(strlen($tf_v_data_inclusao_ini) && strlen($tf_v_data_inclusao_fim)) {
		$sql .= " AND (pgc_pin_response_date between ('".converteData(addslashes($tf_v_data_inclusao_ini))." 00:00:00'::timestamp + '$aux_diff_horas hours'::interval) AND ('".converteData(addslashes($tf_v_data_inclusao_fim))." 23:59:59'::timestamp + '$aux_diff_horas hours'::interval)) \n";
	}
	if(!empty($pin_operacao)||($pin_operacao=='0')) {
		 $sql .= " AND pgc_opr_codigo = ". $pin_operacao . " \n";
	}
	if($lote_16) {
		if($lote_16=="L16") {
			$sql .= " AND coalesce((select pgcl_id from pins_gocash_lote16 pgcl where pgcl.pgcl_pin_number = pgc.pgc_pin_number),0)>0 \n";
		} elseif($lote_16=="L17") {
			$sql .= " AND coalesce((select pgcl_id from pins_gocash_lote16 pgcl where pgcl.pgcl_pin_number = pgc.pgc_pin_number),0)=0 \n";
		}
	}
	if (isset($dd_exclui_testes) ) {
		 $sql .= " AND pgc_ug_id not in (9093, 53916, 2745, 9845, 3468) \n";
	}
	//echo $sql."<br>";
	$res_tmp = SQLexecuteQuery($sql);
	if ($res_tmp) {
		$total_table = pg_num_rows($res_tmp);
		$total_geral = 0;
		while ($res_tmp_row = pg_fetch_array ($res_tmp)) {
			$total_geral += $res_tmp_row['pgc_face_amount'];
		}//end while
	}

	$max_reg = (($inicial + $max)>$total_table)?$total_table:$max;

	$sql .= " ORDER BY pgc_pin_response_date DESC\n ";
	$sql .= " limit $max offset $inicial ";

	if(b_IsUsuarioWagner()) { 
	echo "<br><br>(R) ".str_replace("\n", "<br>\n", $sql)."<br><br>";
	}

	$rsResposta = SQLexecuteQuery($sql);
?>
<table class="table">
	<tr>
        <td align="center">&nbsp;</td>
        <td align="left" colspan="4"><?php echo $stotal?>
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
        <td>PIN</td>
        <td>Valor</td>
        <td>Currency</td>
        <td>ID da Venda</td>
        <td>ID do usuário</td>
        <td>Order No</td>
    </tr>
<?php

	$backcolor1 = "#ddffff";
	$backcolor2 = "#ffffff";
	$bck = $backcolor1;
		while ($pgResposta = pg_fetch_array ($rsResposta)) {

	?>
		<tr<?php echo " bgcolor='".$bck."'" ?>>
			 <td align="center"><nobr><?php echo $pgResposta['pgc_pin_response_date'];?></nobr></td>
			 <td align="center"><nobr><?php echo $pgResposta['pgc_pin_number'];?></nobr></td>
			 <td align="center"><nobr><?php echo number_format($pgResposta['pgc_face_amount'], 2, '.', '.');?></nobr></td>
			 <td align="center"><nobr><?php echo $pgResposta['pgc_currency'];?></nobr></td>
			 
			<?php
				// pagamentos da integração de PINs EPP Cash geram um IDVenda próprio que não existe na tabela de vendas e não têm IDUsuario
			?>
			 <td align="center"><nobr><?php if ($pgResposta['pgc_vg_id'] && ($pgResposta['pgc_ug_id']>0)) echo "<a href='/gamer/vendas/com_venda_detalhe.php?venda_id=".$pgResposta['pgc_vg_id']."' target='_blan'>" ?><?php echo $pgResposta['pgc_vg_id'];?><?php if ($pgResposta['pgc_vg_id']) echo "</a>" ?></nobr></td>

			 <td align="center"><nobr><?php if ($pgResposta['pgc_ug_id']) echo "<a href='/gamer/usuarios/com_usuario_detalhe.php?usuario_id=".$pgResposta['pgc_ug_id']."' target='_blan'>" ?><?php echo $pgResposta['pgc_ug_id'];?><?php if ($pgResposta['pgc_ug_id']) echo "</a>" ?></nobr></td>

			 <td align="center"><nobr><?php echo $pgResposta['pgc_order_no'];?></nobr></td>

		</tr>
	<?php
			if ($bck == $backcolor1)
				$bck = $backcolor2;
			else $bck = $backcolor1;
		} //end while ($pgResposta = pg_fetch_array ($rsResposta)) 
		
		?>
	<tr bgcolor="#DDDDDD" align="center" style="font-weight: bold">
        <td>Total de PINs</td>
        <td><?php echo $total_table; ?></td>
        <td>Valor Total</td>
        <td><?php echo number_format($total_geral, 2, '.', '.'); ?></td>
        <td></td>
        <td></td>
        <td></td>
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
</table>
</form>
</body>
</html>
<?php
//} // end if (b_IsBKOUsuarioComposicaoFifo())
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>