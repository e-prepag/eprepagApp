<?php 

        require_once "../../../includes/constantes.php";   
        require_once DIR_INCS . "main.php";
        require_once DIR_INCS . "pdv/main.php";
        require_once DIR_INCS . "pdv/corte_classPrincipal.php";
        require_once DIR_CLASS . "pdv/classOperadorGamesUsuario.php";

	$_PaginaOperador2Permitido = 54; 
//	echo "<!-- (Oper: ".isSessionOperador().", Oper1: ".isSessionOperadorTipo1().", Oper2: ".isSessionOperadorTipo2()."-->";
	validaSessao(); 

	//login
	$usuarioGames = unserialize($_SESSION['dist_usuarioGames_ser']);
	$usuario_id = $usuarioGames->getId();

        $pagina_titulo = "Consulta Corte Semanal"; 
        include "../includes/cabecalho.php"; 

	$time_start = getmicrotime();

	if(!$ncamp)            $ncamp           = 'trn_data';
	if(!$inicial)          $inicial         = 0;
	if(!$range)            $range           = 1;
	if(!$ordem)            $ordem           = 0;
//	if($BtnSearch)         $inicial         = 0;
//	if($BtnSearch)         $range           = 1;
//	if($BtnSearch)         $total_table     = 0;
	if($BtnSearch=="Buscar") {
		$inicial     = 0;
		$range       = 1;
		$total_table = 0;
	}

	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "/imagens/proxima.gif";
	$img_anterior = "/imagens/anterior.gif";
	$max          = $qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;

	$varsel .= "";

	//Validacao
	//------------------------------------------------------------------------------------------------------------------
	$msg = "";
	$msgFatal = "";

	//Pesquisa
	if($msg == "" && $msgFatal == "")
		if(!$usuario_id || !is_numeric($usuario_id) || trim($usuario_id) == "") $msgFatal = "Código do usuário inválido.\n";

	//Busca cortes
	if($msg == "" && $msgFatal == ""){
		$sql = "select * from cortes c
				where c.cor_ug_id = $usuario_id
				order by cor_periodo_fim desc, cor_periodo_ini desc";
		$res_count = SQLexecuteQuery($sql);
		$total_table = pg_num_rows($res_count);
//		$sql2 = "select * from cortes c
//				where c.cor_ug_id = $usuario_id and cor_status=".$GLOBALS['CORTE_STATUS']['ABERTO']."
//				order by cor_periodo_fim desc, cor_periodo_ini desc";
//echo "sql: $sql2<br>";
	
		$sql .= " limit ".$max; 
		$sql .= " offset ".$inicial;
		$rs_cortes = SQLexecuteQuery($sql);
	}	
	if($msgFatal != "") $msg = $msgFatal;
	  
	if($max + $inicial > $total_table) $reg_ate = $total_table;
	else $reg_ate = $max + $inicial;
		
?>

<table width="100%" border="0" cellspacing="0" cellpadding="3">
        <tr valign="top">
          <td height="100%">  

		<table width="100%" border="0" cellspacing="0" cellpadding="3">
			<tr>
			   <td>  			
				<font size="1" face="Arial, Helvetica, sans-serif">- Será acrescentado taxa de R$<?php echo number_format($BOLETO_TAXA_ADICIONAL_BRADESCO, 2, ',','.') ?> aos boletos com valor menor que R$ <?php echo number_format($BOLETO_LIMITE_PARA_TAXA_ADICIONAL_BRADESCO, 2, ',','.') ?>.<br>- Se não deseja pagar esta taxa aceitaremos pagamentos por depósito em conta corrente ligue para (11) 4063-0656 <br>&nbsp;&nbsp;&nbsp; ou (11) 3030-9101. Se preferir, entre em contato pelo MSN (atendimento1@e-prepag.com.br)</font><br>
			  </td>
			</tr>
		</table>		

		    <table border='0' width="100%" cellpadding="0" cellspacing="0">
              <tr> 
                <td><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
                  <?php if($total_table > 0) { ?>
                  Exibindo resultados <strong><?php echo $inicial + 1 ?></strong> 
                  a <strong><?php echo $reg_ate ?></strong> de <strong><?php echo $total_table ?></strong></font> 
                  <?php } ?>
                </td>
                <td><div align="right"></div></td>
              </tr>
            </table>		
		<table width="100%" border='0' cellpadding="2" cellspacing="1" align="center">
		  <tr class="texto" bgcolor="#ECE9D8"> 
			<td align="center"><strong><font color="#666666">Período de Apuração</font></strong></td>
			<td align="center"><strong><font color="#666666">Qtde Vendas</font></strong></td>
			<td align="center"><strong><font color="#666666">Venda Bruta</font></strong></td>
			<td align="center"><strong><font color="#666666">Comissão</font></strong></td>
			<td align="center"><strong><font color="#666666">Venda Líquida</font></strong></td>
			<td align="center"><strong><font color="#666666">Status</font></strong></td>
			<td align="center"><strong><font color="#666666"></font></strong></td>
		  </tr>
<?php		  
$cor1 = "#f5f5fb";
$cor2 = "#f5f5fb";
$cor3 = "#E5E5Eb";

$Tot_QtdeVendas = 0.0;
$Tot_VendaBruta = 0.0;
$Tot_Comissão = 0.0;
$Tot_VendaLíquida = 0.0;

//echo "<pre>";
//print_r($GLOBALS['CORTE_STATUS']);
//print_r($GLOBALS['CORTE_STATUS_DESCRICAO']);
//print_r($GLOBALS['CORTE_BOLETO_STATUS']);
//print_r($GLOBALS['CORTE_BOLETO_STATUS_DESCRICAO']);
//echo "</pre>";
			if($rs_cortes)
			while($rs_cortes_row = pg_fetch_array($rs_cortes)){
				$cor1 = ($cor1 == $cor2 ? $cor3 : $cor2);
				$cor_status = $rs_cortes_row['cor_status'];
				$cor_status_descricao = $GLOBALS['CORTE_STATUS_DESCRICAO'][$rs_cortes_row['cor_status']];
				$cor_tipo_pagto = $rs_cortes_row['cor_tipo_pagto'];

					$Tot_QtdeVendas += $rs_cortes_row['cor_venda_qtde'];
					$Tot_VendaBruta += $rs_cortes_row['cor_venda_bruta'];
					$Tot_Comissão += $rs_cortes_row['cor_venda_comissao'];
					$Tot_VendaLíquida += $rs_cortes_row['cor_venda_liquida'];

?>
		  <tr class="texto" bgcolor="<?php echo $cor1 ?>"> 
			<td align="center"><?php echo formata_data($rs_cortes_row['cor_periodo_ini'], 0) ?> a <?php echo formata_data($rs_cortes_row['cor_periodo_fim'], 0) ?></td>
			<td align="right"><?php echo $rs_cortes_row['cor_venda_qtde'] ?> </font></td>
			<td align="right"><?php echo number_format ($rs_cortes_row['cor_venda_bruta'], 2, ',', '.') ?></td>
			<td align="right"><?php echo number_format ($rs_cortes_row['cor_venda_comissao'], 2, ',', '.') ?></td>
			<td align="right"><?php echo number_format ($rs_cortes_row['cor_venda_liquida'], 2, ',', '.') ?></td>
			<td align="center"><?php//=$cor_status?>
			<?php	if($rs_cortes_row['cor_status'] == $GLOBALS['CORTE_STATUS']['ABERTO']){ ?>
				<b><font color="#FF0000">
			<?php	}?>
			<?php echo substr($cor_status_descricao, 0, strpos($cor_status_descricao, ".")) ?>
			<?php	if($rs_cortes_row['cor_status'] == $GLOBALS['CORTE_STATUS']['ABERTO']){ ?>
				</font></b>
			<?php	}?>
			</td>					 <?php // Calculate time difference ?>
			<td align="center">&nbsp;<?php//="(".floor((time()-strtotime($rs_cortes_row['cor_periodo_ini']))/(60*60*24)).")"?>
<?php 
				if($rs_cortes_row['cor_status'] == $GLOBALS['CORTE_STATUS']['ABERTO']){
					if($rs_cortes_row['cor_bbc_boleto_codigo'] && $cor_tipo_pagto == $GLOBALS['CORTE_FORMAS_PAGAMENTO']['BOLETO_BANCARIO']){
						$sql = "select * from boleto_bancario_cortes bbc where bbc.bbc_boleto_codigo = " . $rs_cortes_row['cor_bbc_boleto_codigo'];
						$rs_boleto = SQLexecuteQuery($sql);
						if($rs_boleto && pg_num_rows($rs_boleto) > 0){
							$rs_boleto_row = pg_fetch_array($rs_boleto);
							$bbc_status = $rs_boleto_row['bbc_status'];
							if($bbc_status == $GLOBALS['CORTE_BOLETO_STATUS']['ABERTO'] || $bbc_status == $GLOBALS['CORTE_BOLETO_STATUS']['ENVIADO']){
?>								
				<a href="corte_boleto.php?bbc_boleto_codigo=<?php echo $rs_cortes_row['cor_bbc_boleto_codigo'] ?>" target="_blank" class="link_br">
				<font class="texto"><font color="0000FF">Emitir boleto</font></font>
				</a>
<?php
							}
						}
					}
				}

				$sql = "select bbc_status, bbc_data_concilia, bbc_data_cancelado  
						from boleto_bancario_cortes
						where bbc_boleto_codigo=".((is_null($rs_cortes_row['cor_bbc_boleto_codigo']))?0:$rs_cortes_row['cor_bbc_boleto_codigo']);
//echo "sql: $sql<br>";
				$res_boleto = SQLexecuteQuery($sql);
				$bbc_data = "";
				if($res_boleto) {
					$rs_boleto_row = pg_fetch_array($res_boleto);

					if($rs_boleto_row['bbc_status']==$GLOBALS['CORTE_BOLETO_STATUS']['CONCILIADO'])	{
						$bbc_data = $rs_boleto_row['bbc_data_concilia'];
					} else if($rs_boleto_row['bbc_status']==$GLOBALS['CORTE_BOLETO_STATUS']['CANCELADO'])	{
						$bbc_data = $rs_boleto_row['bbc_data_cancelado'];
					}
					if($bbc_data!="") {
						$sdatetmp = substr($bbc_data,0,19);	// -> '2008-01-03 08:03:26'
						echo "em ".substr($sdatetmp,8,2)."/".substr($sdatetmp,5,2)."/".substr($sdatetmp,0,4)." ".substr($sdatetmp,11,8); //date('d/m/Y - H:i:s', $bbc_data);
					}
				} else {
					echo "-";
				}
				
?>
			</td>
		  </tr>
<?php			
			}
		// Apresenta Total desta LH nesta página
?>
		  <tr class="texto" bgcolor="#E5E5EB"> 
			<td align="right">Total:&nbsp;</td>
			<td align="right"><?php echo $Tot_QtdeVendas?></td>
			<td align="right"><?php echo number_format ($Tot_VendaBruta, 2, ',', '.') ?></td>
			<td align="right"><?php echo number_format ($Tot_Comissão, 2, ',', '.') ?></td>
			<td align="right"><?php echo number_format ($Tot_VendaLíquida, 2, ',', '.') ?></td>
			<td align="center">&nbsp;</td>
			<td align="center">&nbsp;</td>
		  </tr>
<?php
		// Calcula Total Geral desta LH
		$sql = "select sum(cor_venda_qtde) as Sum_cor_venda_qtde, sum(cor_venda_bruta) as Sum_cor_venda_bruta,  
				sum(cor_venda_comissao) as Sum_cor_venda_comissao, sum(cor_venda_liquida) as Sum_cor_venda_liquida 
				from cortes c 
				where c.cor_ug_id =".$usuario_id;
//echo "sql: $sql<br>";
		$res_totais = SQLexecuteQuery($sql);
		if($res_totais) {
			$res_totais_row = pg_fetch_array($res_totais);

			$Tot_QtdeVendasTotal = $res_totais_row[0];//['Sum_cor_venda_qtde'];
			$Tot_VendaBrutaTotal = $res_totais_row[1];//['Sum_cor_venda_bruta'];
			$Tot_ComissãoTotal = $res_totais_row[2];//['Sum_cor_venda_comissao'];
			$Tot_VendaLíquidaTotal = $res_totais_row[3];//['Sum_cor_venda_liquida'];
			?>
		  <tr class="texto" bgcolor="#D5D5DB"> 
			<td align="right">Total Geral:&nbsp;</td>
			<td align="right"><?php echo $Tot_QtdeVendasTotal?></td>
			<td align="right"><?php echo number_format ($Tot_VendaBrutaTotal, 2, ',', '.') ?></td>
			<td align="right"><?php echo number_format ($Tot_ComissãoTotal, 2, ',', '.') ?></td>
			<td align="right"><?php echo number_format ($Tot_VendaLíquidaTotal, 2, ',', '.') ?></td>
			<td align="center">&nbsp;</td>
			<td align="center">&nbsp;</td>
		  </tr>
			<?php
		}

?>
			<?php	paginacao_query($inicial, $total_table, $max, '10', $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel); ?>
		  <tr class="texto"> 
			<td align="center" colspan="7" width="100%"><b>Observação</b>: As datas na coluna da direita indicam quando o pagamento foi confirmado no sistema da E-Prepag.com.&nbsp;</td>
		  </tr>
		</table>

          </td>
        </tr>
      </table>

<?php include "../includes/rodape.php"; ?>
