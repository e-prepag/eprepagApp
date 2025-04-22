<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/main.php";
$pagina_titulo = "Edita pagamento";
require_once $raiz_do_projeto."banco/bradesco/inc_urls_bradesco.php";
require_once $raiz_do_projeto."banco/bancodobrasil/inc_urls_bancodobrasil.php";
require_once $raiz_do_projeto."banco/itau/inc_config.php";
require_once $raiz_do_projeto."includes/constantesPagamento.php";
require_once $raiz_do_projeto."includes/inc_Pagamentos.php";
require_once $raiz_do_projeto."includes/functionsPagamento.php";


	$time_start_stats0 = getmicrotime();
	if(($pagto_tipo == $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) || ($pagto_tipo == $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO']) || ($pagto_tipo == $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_CREDITO']) ) {
	}

//echo "<pre>".print_r($_REQUEST, true)."</pre>";
//echo "numcompra: ".$id."<br>";

/*
Array
(
    [id] => 110444
    [varsel] => &tf_v_data_inclusao_ini=01/10/2011&tf_v_data_inclusao_fim=31/10/2011&tf_v_tipo_transacao=Todos&tf_v_canal=LR&tf_v_forma_pagamento=
    [tf_v_canal] => LR
    [tf_v_data_inclusao_ini] => 01/10/2011
    [tf_v_data_inclusao_fim] => 31/10/2011
    [tf_v_tipo_transacao] => Todos
    [tf_v_forma_pagamento] => Todas
    [tf_opr_codigo] => 
    [tf_v_codigo] => 1709309
    [tf_d_valor_pago] => 
)
*/
/*
	//Validacoes, id=idpagto
	if(isset($_GET['id'])) {
	//	echo "Unset _GET['id']<br>";
		unset($_GET['id']);
		if(!isset($_POST['id'])) {
	//		echo "Unset id<br>";
			unset($id);
		}
	}
*/
	$msg = "";	

	//Recupera as vendas
	if($msg == ""){
            
		$sql = "select pgt.*, 
					(case when pgt.tipo_cliente='M' then (select vg.vg_ultimo_status from tb_venda_games vg where vg.vg_id=pgt.idvenda) 
						when pgt.tipo_cliente='LR' then (select vg.vg_ultimo_status from tb_dist_venda_games vg where vg.vg_id=pgt.idvenda)
						end ) as vg_ultimo_status
				from tb_pag_compras pgt
				where idpagto=".$id.""; 
					// left outer join tb_venda_games vg on pgt.idvenda=vg.vg_id 
		//"and numcompra='2009082109203437249755' ";	

//echo "sql: ".$sql."<br>";
		if($tf_v_codigo) {
		}

		$rs_transacoes = SQLexecuteQuery($sql);
		if(!$rs_transacoes || pg_num_rows($rs_transacoes) == 0) {
			$msg = "Nenhum pagamento encontrado.\n";
		} else {
		}

	}
?>
<script language="JavaScript">
<!--
	// op='pag' -> completa pagamento (o mesmo da conciliação automatica,mas manual )
	// op='des' -> descancela uma venda cancelada mas com pagamento completo
	function processapagamento(op) {
		document.form1.action = "inc_mod_st.php";
		
		document.form1.op.value=op;
//alert("op: "+document.form1.op.value+", id:"+document.form1.id.value+"");
		document.form1.submit();
	}
-->
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="1">BackOffice - Money</a></li>
        <li><a href="lista_pagamentos.php">Voltar</a></li>
        <li class="active">Edita Pagamentos</li>
    </ol>
</div>
<center>
    <table class="txt-preto fontsize-pp">
    <tr valign="top" align="center">
      <td>
          <table class="table">
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center"><b>ID</b>&nbsp;</td>
    	          <td class="texto" align="center"><b>IDItau</b>&nbsp;</td>
    	          <td class="texto" align="center"><b>IDVenda</b>&nbsp;</td>
    	          <td class="texto" align="center"><b>Data inicio</b></td>
    	          <td class="texto" align="center"><b>Data pagto.</b></td>
    	          <td class="texto" align="center"><b>Usuário</b></td>
    	          <td class="texto" align="center"><b>Canal</b></td>
    	          <td class="texto" align="center"><b>Forma pagto.</b></td>
    	          <td class="texto" align="center" width="45%"><b>Cesta</b></td>
    	          <td class="texto" align="center"><b>Valor</b></td>
    	          <td class="texto" align="center"><b>Taxas</b></td>

    	          <td class="texto" align="center"><b><nobr>PINS EPP</nobr></b></td>
    	          <td class="texto" align="center"><b>Saldo</b></td>
    	          <td class="texto" align="center"><b><nobr>PINs GoCash</nobr></b></td>

				  <td class="texto" align="center"><b>Pagto</b></td>
    	          <td class="texto" align="center"><b>Venda</b></td>
    	          <td class="texto" align="center"><b>Sonda</b></td>
				</tr>
		<?php	

			$irows=0;
			if($rs_transacoes) {

				while($rs_transacoes_row = pg_fetch_array($rs_transacoes)){
					$irows++;
					$numcompra = $rs_transacoes_row['numcompra'];
			?>
    	        <tr bgcolor="F5F5FB" valign="top">
    	          <td class="texto" align="center">&nbsp;<?php echo $rs_transacoes_row['numcompra']?></td>
		          <td class="texto" align="center">&nbsp;<?php if($rs_transacoes_row['iforma']=='A') echo str_pad($rs_transacoes_row['id_transacao_itau'], 8, "0", STR_PAD_LEFT) ?></td>
				  <td class="texto" align="center">&nbsp;<?php echo str_pad($rs_transacoes_row['idvenda'], 8, "0", STR_PAD_LEFT)?></td>
    	          <td class="texto" align="center">&nbsp;<nobr><?php echo (($rs_transacoes_row['datainicio'])?formata_data_ts_pos($rs_transacoes_row['datainicio'], 0, true, true):"-") ?></nobr></td>
    	          <td class="texto" align="center">&nbsp;<nobr><font color='<?php echo (($rs_transacoes_row['datacompra'])?"#FF0000":"") ?>'><?php echo (($rs_transacoes_row['datacompra'])?formata_data_ts_pos($rs_transacoes_row['datacompra'], 0, true, true):"-") ?></font></nobr></td>
    	          <td class="texto" align="center"><nobr>&nbsp;<?php echo $rs_transacoes_row['cliente_nome']?>&nbsp;</nobr></td>
    	          <td class="texto" align="center">&nbsp;<?php echo get_tipo_cliente_descricao($rs_transacoes_row['tipo_cliente'])."<br>".getLogoBancoSmall($rs_transacoes_row['iforma']) ?></td>
    	          <td class="texto" align="center">&nbsp;<?php echo $FORMAS_PAGAMENTO_DESCRICAO[$rs_transacoes_row['iforma']]." (".$rs_transacoes_row['iforma'].")".(($rs_transacoes_row['cctype'])?"<br>(".$rs_transacoes_row['cctype'].")":"") ?>&nbsp;</td>
    	          <td class="texto" align="center" width="45%">&nbsp;<?php echo $rs_transacoes_row['cesta']?>&nbsp;</td>
    	          <td class="texto" align="center" style="color:<?php echo (($rs_transacoes_row['total']>0)?"#3300CC":"#CCCCCC")?>">&nbsp;<?php echo number_format($rs_transacoes_row['total']/100, 2, ',', '.')?></td>
    	          <td class="texto" align="center" style="color:<?php echo (($rs_transacoes_row['taxas']>0)?"#3300CC":"#CCCCCC")?>">&nbsp;<?php echo number_format(($rs_transacoes_row['taxas']), 2, ',', '.')?></td>  


    	          <td class="texto" align="center" style="color:<?php echo (($rs_transacoes_row['valorpagtopin']>0)?"#3300CC":"#CCCCCC")?>">&nbsp;<?php echo number_format(($rs_transacoes_row['valorpagtopin']), 2, ',', '.')?></td>  
    	          <td class="texto" align="center" style="color:<?php echo (($rs_transacoes_row['valorpagtosaldo']>0)?"#3300CC":"#CCCCCC")?>">&nbsp;<?php echo number_format(($rs_transacoes_row['valorpagtosaldo']), 2, ',', '.')?></td>  
    	          <td class="texto" align="center" style="color:<?php echo (($rs_transacoes_row['valorpagtogocash']>0)?"#3300CC":"#CCCCCC")?>">&nbsp;<?php echo number_format(($rs_transacoes_row['valorpagtogocash']), 2, ',', '.')?></td>  


				  <td class="texto" align="center" width="45%" title="<?php echo  (($rs_transacoes_row['status']=='1')?"Incompleto":(($rs_transacoes_row['status']=='3')?"COMPLETO":(($rs_transacoes_row['status']=='-1')?"CANCELADO":"DESCONHECIDO")) ) ?>">&nbsp;<?php echo "".(($rs_transacoes_row['status']=='3')?"<font color='#009933'>":"<font>").$rs_transacoes_row['status']."</font>"?>&nbsp;</td>
    	          <td class="texto" align="center" width="45%" title="<?php echo $STATUS_VENDA_PAG_DESCRICAO[$rs_transacoes_row['vg_ultimo_status']] ?>">&nbsp;<?php echo "<font color='".(($rs_transacoes_row['vg_ultimo_status']==5)?"#009933":"")."'>".$rs_transacoes_row['vg_ultimo_status']."</font>" ?>&nbsp;</td>
    	          <td class="texto" align="center">&nbsp;<nobr><?php 

//echo "iforma (ABC): ".$rs_transacoes_row['iforma']."<br>";
//echo "iforma (ABC2): ".$FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE']."<br>";
					if($rs_transacoes_row['iforma']==$FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) {
						$sonda = getTransacaoPagamentoOK("Transf", $rs_transacoes_row['numcompra'], $aline);
						echo "[".(($sonda)?"<font color='#009900'>OK</font>":"<font color='#FF0000'>none</font>")."]";
						echo ( (($rs_transacoes_row['status']=='1' && $sonda) || ($rs_transacoes_row['status']=='3' && !$sonda)) ?" <font color='#FF0000'>NO SYNC<font>":"");
					} else if($rs_transacoes_row['iforma']==$FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO']) {
                                                $sonda = getTransacaoPagamentoOK("PagtoFacil", $rs_transacoes_row['numcompra'], $aline);
						echo "[".(($sonda)?"<font color='#009900'>OK</font>":"<font color='#FF0000'>none</font>")."]";
						echo (( (($rs_transacoes_row['status']=='1' && $sonda) || ($rs_transacoes_row['status']=='3' && !$sonda)))?" <font color='#FF0000'>NO SYNC<font>":"");
					} else if($rs_transacoes_row['iforma']==$FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']) {
                                                $sonda = getTransacaoPagamentoOK("BancodoBrasil", $rs_transacoes_row['numcompra'], $aline);
						$dataconfirma = "'".substr($aline[3],6,4)."-".substr($aline[3],3,2)."-".substr($aline[3],0,2)."'";

						echo "[".(($sonda)?"<font color='#009900'>OK</font>":"<font color='#FF0000'>none</font>")."]";
						echo (( (($rs_transacoes_row['status']=='1' && $sonda) || ($rs_transacoes_row['status']=='3' && !$sonda)))?" <font color='#FF0000'>NO SYNC<font>":"");	//." <nobr>[".$dataconfirma."]</nobr>";
					} else if($rs_transacoes_row['iforma']==$FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE']) {
//echo "id_transacao_itau: ".$rs_transacoes_row['id_transacao_itau']."<br>";
//die("Stop");                                  

						$sonda = getTransacaoPagamentoOK("BancoItau", $rs_transacoes_row['id_transacao_itau'], $aline);
						$dataconfirma = "'".substr($aline[3],6,4)."-".substr($aline[3],3,2)."-".substr($aline[3],0,2)."'";

						echo "[".(($sonda)?"<font color='#009900'>OK</font>":"<font color='#FF0000'>none</font>")."]";
						echo (( (($rs_transacoes_row['status']=='1' && $sonda) || ($rs_transacoes_row['status']=='3' && !$sonda)))?" <font color='#FF0000'>NO SYNC<font>":"");	//." <nobr>[".$dataconfirma."]</nobr>";
                                       	} else if(b_IsPagtoCielo($rs_transacoes_row['iforma'])) {
                                                $sonda = getTransacaoPagamentoOK("Cielo", $rs_transacoes_row['numcompra'], $aline);
                                                $dataconfirma = substr($aline['data'], 0, 19);
						echo "[".(($sonda)?"<font color='#009900'>OK</font>":"<font color='#FF0000'>none</font>")."]";
						echo (( (($rs_transacoes_row['status']=='1' && $sonda) || ($rs_transacoes_row['status']=='3' && !$sonda)))?" <font color='#FF0000'>NO SYNC<font>":"");	//." <nobr>[".$dataconfirma."]</nobr>";
                                        }else if($rs_transacoes_row['iforma'] == $FORMAS_PAGAMENTO['PAGAMENTO_PIX']){
                                                $sonda = getTransacaoPagamentoOK($GLOBALS['PAGAMENTO_PIX_NOME_BANCO'], $rs_transacoes_row['numcompra'], $alinePIX);
                                                $dataconfirma = $dataconfirma = "'".substr(str_replace('T', ' ', $alinePIX->pix->horario),0,19)."'";
						echo "[".(($sonda)?"<font color='#009900'>OK</font>":"<font color='#FF0000'>none</font>")."]";
						echo (( (($rs_transacoes_row['status']=='1' && $sonda) || ($rs_transacoes_row['status']=='3' && !$sonda)))?" <font color='#FF0000'>NO SYNC<font>":"");	//." <nobr>[".$dataconfirma."]</nobr>";
                                        }
					$status = $rs_transacoes_row['status'];
//echo "status (ABC): ".$status."<br>";

				  ?></nobr><?php if(!$sonda && ($aline[6]!='000000') && (count($aline)>=6) && ($rs_transacoes_row['status']!='1')) echo "<br>Erro: <b>".$aline[6]."<br>"?></td>

    	        </tr>
		<?php
				}

				if($irows==0) {
			?>'
					<tr>
					  <td class="texto" align="center" colspan="12">&nbsp;<font color='#FF0000'>Não foram encontrados registros para os valores escolhidos (2)</font></td>
					</tr>
			<?php

				}

			} else {
		?>
    	        <tr>
    	          <td class="texto" align="center" colspan="7">&nbsp;<font color='#FF0000'>Não foram encontrados registros para os valores escolhidos</font></td>
    	        </tr>
		<?php
			}
		?>
			</table>
      </td>
    </tr>
    <tr><td>&nbsp;</td></tr>
    <tr valign="top" align="center">
      <td>
		  <form method=post name="form1" action="edita_pagamentos.php">
			<input type="hidden" name="op" value="">

			<input type="hidden" name="tf_v_data_inclusao_ini" value="<?php echo $tf_v_data_inclusao_ini; ?>">
			<input type="hidden" name="tf_v_data_inclusao_fim" value="<?php echo $tf_v_data_inclusao_fim; ?>">
			<input type="hidden" name="tf_v_tipo_transacao" value="<?php echo $tf_v_tipo_transacao; ?>">

			<input type="hidden" name="id" value="<?php echo $id; ?>">
			<input type="hidden" name="varsel" value="<?php echo $varsel; ?>">

			<input type="hidden" name="tf_v_forma_pagamento" value="<?php echo $tf_v_forma_pagamento; ?>">
			<input type="hidden" name="tf_opr_codigo" value="<?php echo $tf_opr_codigo; ?>">
			<input type="hidden" name="tf_v_codigo" value="<?php echo $tf_v_codigo; ?>">
			<input type="hidden" name="tf_d_valor_pago" value="<?php echo $tf_d_valor_pago; ?>">
		<?php
//echo "<br>";
//echo "status: '$status'<br>";
//echo "sonda: '$sonda'<br>";
			// é o mesmo caso de "completa pagamento por sonda" em bkov2_prepag/commerce/includes/functions_vendaGames.php 
			// só que aqui é manual, caso algo não funcione automáticamente na conciliação
			if ((($status=='1')&& $sonda) ) {	// || ($id == 506422)
		?>
			<input type="button" name="btAceita" value="Aceita pagamento" OnClick="processapagamento('pag');" class="botao_simples">		
		<?php
			}

// Dummy  
//  2011-08-21
//if ($numcompra == "20110817194418646" || $numcompra == "20110817193944429") {
//	$sonda = true;
//}
// 2011-08-23
//	Nro do pedido: 5472778	(numcompra = '20110819205124942')
//	Nro do pedido: 9625735	(numcompra = '20110819194838530')
//if ($numcompra == "20110819205124942" || $numcompra == "20110819194838530") {
//	$sonda = true;
//}

//	Pedido: 2230473
//	Data: 21/08/2011 - 11:44:57
//if ($numcompra == "20110821114457983") {
//	$sonda = true;
//}

//	Pedido: 1553913
//	Data: 21/08/2011 - 14:02
//if ($numcompra == "20110821140241608") {
//	$sonda = true;
//}
/*
//$a_sondas_confirmadas = array('20110823201412605', '20110820225642182', '20110820222102917');
$a_sondas_confirmadas = array('20110808220653539');
if (in_array($numcompra, $a_sondas_confirmadas)) {
	$sonda = true;
}

*/
/*
$a_sondas_confirmadas = array('20111003115058707');
if (in_array($numcompra, $a_sondas_confirmadas)) {
	$sonda = true;
}
*/
/*
$a_sondas_confirmadas = array('20111004221513835');
if (in_array($numcompra, $a_sondas_confirmadas)) {
	$sonda = true;
}
*/
/*
$a_sondas_confirmadas = array('20111028221238953');
if (in_array($numcompra, $a_sondas_confirmadas)) {
	$sonda = true;
}
*/
/*
	// Banco Itaú sem Sonda desde 2011-11-15 0:00:00
		$a_itau_completos = array(
					"20111116113102839", "20111116102646153", "20111115211113556", "20111115210501904", "20111115200733997", "20111115155052041", "20111115153948671", "20111115152104026", "20111115145015517", "20111115140431104", "20111115133050958", "20111115130304228", "20111115075818203", "20111116105059591", "20111116104036996", "20111116102056244", "20111116101905775", "20111116092451463", "20111116092351853", "20111116081910948", "20111116020232662", "20111116001720568", "20111115232327873", "20111115230139468", "20111115223158554", "20111115211155007", "20111115203203037", "20111115191915726", "20111115185828814", "20111115181428008", "20111115174411096", "20111115173713728", "20111115172758612", "20111115171025364", "20111115170823577", "20111115165513211", "20111115164648308", "20111115164305010", "20111115161239194", "20111115161233553", "20111115160319407", "20111115160044023", "20111115152944400", "20111115151004990", "20111115150355722", "20111115142201039", "20111115141819117", "20111115141722339", "20111115141423123", "20111115135842402", "20111115133530972", "20111115133021632", "20111115124341756", "20111115124151922", "20111115123835657", "20111115122630467", "20111115113906713", "20111115113700403", "20111115113309839", "20111115111101174", "20111115092355389", "20111115084725729", "20111115084431608", "20111115005131576"
					);
		if(in_array("".$numcompra."", $a_itau_completos)) {
			$sonda = true;
			$dataconfirma = "'2011-11-15 12:00:00'";
		} else {
			echo "desconhecido<br>";
		}
*/
/*
		// Banco Itaú LHs Pre sem Sonda desde 2011-11-15 0:00:00
			$a_itau_completos = array(
				"20111115211113556", "20111115145015517", "20111116113102839", "20111115133050958", "20111115130304228", "20111115153948671", "20111115075818203", "20111115210501904","20111115140431104", "20111115200733997", "20111115155052041", "20111115152104026"
					);
*/
/*
		// Banco Itaú Gamer sem Sonda desde 2011-11-17 0:00:00
			$a_itau_completos = array(
				"20111116191630141"
					);
		if(in_array("".$numcompra."", $a_itau_completos)) {
			$sonda = true;
			$dataconfirma = "'2011-11-17 12:00:00'";
		} else {
//			echo "desconhecido<br>";
		}
*/
//echo "'".$numcompra."' -. status: '$status', '".(($sonda)?"SONDA _OK":"sonda_none")."'<br>";
			// descancela venda
			if ($status=='-1' && $sonda) {
		?>
			<input type="button" name="btDescancela" value="Descancela Pagto" OnClick="processapagamento('des');" class="botao_simples">		
		<?php
			}
		?>
		  </form>
      </td>
    </tr>
	</table>

	<table border='0' cellpadding='0' cellspacing='1' bordercolor='#cccccc' style='border-collapse:collapse;'>	
	  <tr align="center"> 
		<td bgcolor="#FFFFFF" class="texto">&nbsp;</td><td bgcolor="#FFFFFF" class="texto">Processamento em <?php echo number_format(getmicrotime() - $time_start_stats0, 2, '.', '.') ?> s.</font></td><td bgcolor="#FFFFFF" class="texto">&nbsp;</td>
	  </tr>
	</table>

	</div>
	</center>

</body>
</html>
