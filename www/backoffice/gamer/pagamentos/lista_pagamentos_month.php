<?php
$pagina_titulo = "Lista de pagamentos por dia";

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/main.php";
require_once $raiz_do_projeto."banco/bradesco/inc_urls_bradesco.php";
require_once $raiz_do_projeto."banco/bancodobrasil/inc_urls_bancodobrasil.php";
require_once $raiz_do_projeto."includes/gamer/constantes.php";
require_once $raiz_do_projeto."includes/inc_Pagamentos.php";
require_once $raiz_do_projeto."includes/functionsPagamento.php";

	$time_start_stats = getmicrotime();

	//paginacao
	$p = $_REQUEST['p'];
	if(!$p) $p = 1;
	$registros = 50;
	$registros_total = 0;
	if(!$tf_v_canal) {
		$tf_v_canal = "M";
	}

	if(!$btPesquisar && !$tf_v_data_year && !$tf_v_data_month) {
		$tf_v_data_year = date("Y");
		$tf_v_data_month = date("m");
//echo "Reset: Y-m: ".$tf_v_data_year."-".$tf_v_data_month."<br>";
	}
	if($tf_v_data_year) {
		$tf_v_data_year_last_month = date("m",mktime(0, 0, 0, 0, $tf_v_data_month, date("Y")));
	} else {
		$tf_v_data_year = date("Y");
	}
	// Vamos listar sempre todos os meses até o atual
	$tf_v_data_month = "";
	
	//Validacoes
	$msg = "";	

	//Recupera as vendas
	if($msg == ""){

//		$sql  = "select 
//				TO_DATE(TO_CHAR(EXTRACT(YEAR FROM pgt.datainicio),'9999')||'-'||TO_CHAR(EXTRACT(MONTH FROM pgt.datainicio),'99'),'YYYY-MM') as imonth, count(*) as n,  
//				sum(subtotal)/100 as sum_subtotal, sum(total)/100 as sum_total, sum(taxas) as sum_taxas 
//			from tb_pag_compras pgt
//			where 1=1 "; 

		$sql  = "select 
				date_part('year', pgt.datainicio) as iyear, date_part('month', pgt.datainicio) as imonth, count(*) as n,  
				sum(subtotal)/100 as sum_subtotal, sum(total)/100 as sum_total, sum(taxas) as sum_taxas 
			from tb_pag_compras pgt
			where 1=1 and total>0 "; 
				// "and tipo_deposito = 0 "

//echo "tf_v_tipo_transacao: ".$tf_v_tipo_transacao."<br>";
		// Avoid empty value
		if(!$tf_v_tipo_transacao) $tf_v_tipo_transacao = "Completos";		
		// Validate
		if(($tf_v_tipo_transacao!="Completos") && ($tf_v_tipo_transacao!="Incompletos")  && ($tf_v_tipo_transacao!="Todos") ) $tf_v_tipo_transacao = "Completos";		
//		if($tf_v_tipo_transacao=="Completos")	$var_data = "datacompra";
//			else								$var_data = "datainicio";		

		// Filtros Where
		$sql_where = "";

		if($tf_v_data_year) {
			if($tf_v_data_month) {
//				$sql_where .= " and TO_DATE(TO_CHAR(EXTRACT(YEAR FROM pgt.datainicio),'9999')||'-'||TO_CHAR(EXTRACT(MONTH FROM pgt.datainicio),'9999')||'-'||TO_CHAR(EXTRACT(DAY FROM pgt.datainicio),'99'),'YYYY-MM-DD') between '2009-01-01' and '".$tf_v_data_year."-".$tf_v_data_month."-01' ";
				$sql_where .= " and date_part('year', pgt.datainicio)=".$tf_v_data_year." and date_part('month', pgt.datainicio)=".$tf_v_data_month." ";
			} else {
				$sql_where .= " and date_part('year', pgt.datainicio)=".$tf_v_data_year." ";
			}
		}
		if($tf_v_canal) {
			if(($tf_v_canal=="M") || ($tf_v_canal=="E") || ($tf_v_canal=="LR") || ($tf_v_canal=="LO") ) {
				$sql_where .= " and pgt.tipo_cliente='".$tf_v_canal."'";
			}
		}

	    $a_formas_aceitas = array("5", "6", "9", "A", "B", "E", "EG", "P", "C", "Z");
		if(!in_array($tf_v_forma_pagamento, $a_formas_aceitas)) {
			$tf_v_forma_pagamento = ""; 
		}
		if($tf_v_forma_pagamento) {
			if($tf_v_forma_pagamento=="C") {
				$sql_where_forma = " and ".getSQLWhereParaPagtoOnline(true)." ";
			} elseif($tf_v_forma_pagamento=="EG") {
				$sql_where_forma = " and pgt.iforma='E' and valorpagtogocash>0 ";
			} else {
				$sql_where_forma = " and pgt.iforma='".$tf_v_forma_pagamento."' ";
			}
		}

/*
		if(!b_IsPagtoOnline($tf_v_forma_pagamento))
			$tf_v_forma_pagamento=""; 
		if($tf_v_forma_pagamento) {
			$sql_where .= " and pgt.iforma='".$tf_v_forma_pagamento."' ";
		}
*/
		if($tf_v_tipo_transacao=="Completos") {
			$sql_where .= " and pgt.status=3 ";
		} else if($tf_v_tipo_transacao=="Incompletos") {
			$sql_where .= " and (not pgt.status=3) ";
		}
		if($tf_v_deposito_em_saldo) {
			if($tf_v_deposito_em_saldo=="P") {
				$sql .= " and tipo_deposito = 0 ";
			} elseif($tf_v_deposito_em_saldo=="D") {
				$s_ids = "";
				foreach($GLOBALS['TIPO_DEPOSITO'] as $key => $val) {$s_ids .= (($s_ids=="")?"":",").$val;}
				$sql .= " and tipo_deposito in ($s_ids) ";
			}elseif(in_array($tf_v_deposito_em_saldo, $GLOBALS['TIPO_DEPOSITO'])) {
				$sql .= " and tipo_deposito = $tf_v_deposito_em_saldo ";
			}
		}
		
		$sql .= $sql_where;
		$sql .= $sql_where_forma;
		if($tf_v_usuario) {
			$sql .= " and pgt.idcliente=".$tf_v_usuario." ";

//			if($tf_v_usuario!="Todos") {
//				$sql .= " and pgt.cliente_nome='".$tf_v_usuario."'";
//			}
		}

//		$sql .= " group by TO_DATE(TO_CHAR(EXTRACT(YEAR FROM pgt.datainicio),'9999')||'-'||TO_CHAR(EXTRACT(MONTH FROM pgt.datainicio),'99'),'YYYY-MM') ";
		$sql .= " group by iyear, imonth ";

//if(b_IsUsuarioReinaldo()) { 
//echo "(R) ".str_replace("\b", "\b<br>", $sql)."<br>";
//}

		$rs_total = SQLexecuteQuery($sql);
		if($rs_total) $registros_total = pg_num_rows($rs_total);

		$rs_transacoes = SQLexecuteQuery($sql);
		if(!$rs_transacoes || pg_num_rows($rs_transacoes) == 0) {
			$msg = "Nenhum pagamento encontrado.\n";
		} else {

			$total_pagamentos = 0;
			$total_taxas = 0;
			$total_pagtos = 0;
			while($rs_transacoes_row = pg_fetch_array($rs_transacoes)){ 
				$total_pagamentos += ($rs_transacoes_row['sum_total']-$rs_transacoes_row['sum_taxas']);
				$total_taxas += $rs_transacoes_row['sum_taxas'];
				$total_pagtos += $rs_transacoes_row['n'];
			}
	
//			$sql .= " order by TO_DATE(TO_CHAR(EXTRACT(YEAR FROM pgt.datainicio),'9999')||'-'||TO_CHAR(EXTRACT(MONTH FROM pgt.datainicio),'99'),'YYYY-MM') desc ";
			$sql .= " order by iyear desc, imonth desc ";
			$sql .= " offset " . ($p - 1) * $registros . " limit " . $registros;
//echo $sql;
			// Ler novamente para esta página
			$rs_transacoes = SQLexecuteQuery($sql);
		}

/*
		// Lista de usuários
		$sql_nomes = "select pgt.cliente_nome as cliente_nome, count(*) as n from tb_pag_compras pgt where 1=1 ".$sql_where."group by pgt.cliente_nome order by pgt.cliente_nome";
//echo $sql_nomes;
		$rs_transacoes_nomes = SQLexecuteQuery($sql_nomes);
*/
	}

	$varsel = "&tf_v_data_year=$tf_v_data_year&tf_v_data_month=$tf_v_data_month";
	$varsel .= "&tf_v_tipo_transacao=$tf_v_tipo_transacao&tf_v_canal=$tf_v_canal&tf_v_forma_pagamento=$tf_v_forma_pagamento";


?>
<script language="JavaScript">
<!--
	function reload() {
		document.form1.action = "lista_pagamentos_month.php";
		document.form1.submit();
	}

//-->
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
    <table class="table txt-preto fontsize-pp">
    <tr valign="top" align="center">
      <td>
			<form name="form1" method="post" action="lista_pagamentos_month.php">
			<input type="hidden" id="id" name="id" value="">
			<input type="hidden" name="varsel" value="<?php echo $varsel; ?>">
            <table class="table">
				<?php if(isset($msg_retorno)) { ?>
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center" colspan="3">Msg: <b><font color="#339900"><?php echo $msg_retorno; ?></font></b></td>
    	        </tr>
				<?php } ?>
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center" colspan="3"><b>Lista de <?php echo ((($p - 1) * $registros)+1); ?> a <?php echo (($p*$registros)); ?> <?php echo " (Total: ".$registros_total." registro"?><?php if($registros_total>1) echo "s"; ?><?php echo ")"?></b></td>
    	        </tr>
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center"><b>Forma de Pagamento</b></td>
    	          <td class="texto" align="center"><b>Ano da Compra</b></td>
    	          <td>&nbsp;</td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center"><nobr>&nbsp;
					<select id='tf_v_canal' name='tf_v_canal' onChange='reload()'> 
						<option value=''<?php echo (($tf_v_canal=="")?" selected":""); ?>>Todos os canais de venda</option> 
						<option value='M'<?php echo (($tf_v_canal=="M")?" selected":"") ?>>Money</option>
						<option value='E'<?php echo (($tf_v_canal=="E")?" selected":"") ?>>Money Express</option>
						<option value='LR'<?php echo (($tf_v_canal=="LR")?" selected":"") ?>>Lanhouse Pré</option>
						<option value='LO'<?php echo (($tf_v_canal=="LO")?" selected":"") ?>>Lanhouse Pós</option>
					  </select>
				  </td>
    	          <td class="texto" align="center">&nbsp;
					<select id='tf_v_data_year' name='tf_v_data_year' onChange='reload()'> 
						<option value=''<?php echo (($tf_v_data_year=="")?" selected":""); ?>>Todos os anos</option> 
				  <?php
					$year_start = 2009;
					$year_now = date("Y");
					for($i=$year_now;$i>=$year_start;$i--) {
						?>
						<option value='<?php echo $i; ?>'<?php echo (($tf_v_data_year==$i)?" selected":""); ?>><?php echo $i; ?></option> 
						<?php
					}
				  ?>
					  </select>
				  </td>
				  <td class="texto" align="center">&nbsp;</td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center">
					<select id='tf_v_tipo_transacao' name='tf_v_tipo_transacao' onChange='reload()'> 
						<option value='Todos'<?php echo (($tf_v_tipo_transacao=="Todos")?" selected":"") ?>>Todos os pagamentos</option>
						<option value='Incompletos'<?php echo (($tf_v_tipo_transacao=="Incompletos")?" selected":"") ?>>Apenas pagamentos incompletos</option>
						<option value='Completos'<?php echo (($tf_v_tipo_transacao=="Completos")?" selected":"") ?>>Apenas pagamentos COMPLETOS</option>
					  </select>
				  </td>
    	          <td class="texto" align="center">
					<select id='tf_v_forma_pagamento' name='tf_v_forma_pagamento' class="form2"> 
						<option value='Todas'<?php echo (!in_array($tf_v_forma_pagamento, $a_formas_aceitas)?" selected":"") ?>>Todas as formas</option>
						<option value='5'<?php echo (($tf_v_forma_pagamento=="5")?" selected":"") ?>>Transferência entre contas Bradesco (BRD5)</option>
						<option value='6'<?php echo (($tf_v_forma_pagamento=="6")?" selected":"") ?>>Pagamento Fácil Bradesco (BRD6)</option>
						<option value='9'<?php echo (($tf_v_forma_pagamento=="9")?" selected":"") ?>>Pagamento BB - Débito sua Conta (BBR9)</option>
						<option value='A'<?php echo (($tf_v_forma_pagamento=="A")?" selected":"") ?>>Pagamento Banco Itaú (BITA)</option>
						<option value='B'<?php echo (($tf_v_forma_pagamento=="B")?" selected":"") ?>>Pagamento HiPay (HIPB)</option>
						<option value='E'<?php echo (($tf_v_forma_pagamento=="E")?" selected":"") ?>>PINs EPP (EPPE)</option>
						<option value='EG'<?php echo (($tf_v_forma_pagamento=="EG")?" selected":"") ?>>PINs EPP (EPPE) - GoCash</option>
						<option value='P'<?php echo (($tf_v_forma_pagamento=="P")?" selected":"") ?>>Pagamento PayPal (PYPP)</option>
						<option value='C'<?php echo (($tf_v_forma_pagamento=="C")?" selected":"") ?>>Pagamentos Cielo (F-M)</option>
						<option value='Z'<?php echo (($tf_v_forma_pagamento=="Z")?" selected":"") ?>>Pagamento Banco E-Prepag (BEPZ)</option>
					  </select>
				  </td>
				  <td class="texto" align="center">&nbsp;</td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center" colspan="2"><nobr>Código usuário: 

					<input name="tf_v_usuario" type="text" class="form2" value="<?php echo $tf_v_usuario ?>" size="10" maxlength="10">			
<?php
/*
?>
					<select id='tf_v_usuario' name='tf_v_usuario' onChange='reload()'> 
						<option value='Todos'<?php echo (($tf_v_usuario=="Todos")?" selected":"") ?>>Todos os usuários</option>
						<?php 
							$n_usuarios = 0;
							if($rs_transacoes_nomes && pg_num_rows($rs_transacoes_nomes) > 0) {
								$n_usuarios = pg_num_rows($rs_transacoes_nomes);
								while($rs_transacoes_nomes_row = pg_fetch_array($rs_transacoes_nomes)){ 
									?>
									<option value='<?php echo $rs_transacoes_nomes_row['cliente_nome'] ?>'<?php echo (($tf_v_usuario==$rs_transacoes_nomes_row['cliente_nome'])?" selected":"") ?>><?php echo $rs_transacoes_nomes_row['cliente_nome']." (".$rs_transacoes_nomes_row['n'].")"; ?></option>
									<?php
								}
							}
						?>
					  </select> 
<?php
*/
?>
					  <?php 
					  if($n_usuarios>0) echo '(N usuários: '.$n_usuarios.')';
					  ?></nobr>
				  </td>
				  <td class="texto" align="center">&nbsp;</td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center" colspan="2"><nobr>Depósito em Saldo: 
					<select id='tf_v_deposito_em_saldo' name='tf_v_deposito_em_saldo' class="form2"> 
						<option value=''<?php echo (($tf_v_deposito_em_saldo=="")?" selected":"") ?>>Todos os pagamentos</option>
						<option value='P'<?php echo (($tf_v_deposito_em_saldo=="P")?" selected":"") ?>>Apenas pagamentos para compra de PINs</option>
						<option value='D'<?php echo (($tf_v_deposito_em_saldo=="D")?" selected":"") ?>>Apenas Depósitos - Todos</option>
						<?php
//						$TIPO_DEPOSITO_LEGENDA
							foreach($GLOBALS['TIPO_DEPOSITO'] as $key => $val) {
						?>
						<option value='<?php echo $val ?>'<?php echo (($tf_v_deposito_em_saldo==$val)?" selected":"") ?>>Apenas Depósito de '<?php echo $GLOBALS['TIPO_DEPOSITO_LEGENDA'][$val]?>'</option>
						<?php
							}
						?>
					  </select>
				  </nobr></td>
				  <td class="texto" align="center">&nbsp;</td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center">&nbsp;</td>
    	          <td class="texto" align="center">&nbsp;</td>
				  <td class="texto" align="center"><input type="submit" name="btPesquisar" value="Pesquisar" class="btn btn-sm btn-info"></td>
    	        </tr>
			</table>
			</form>
			
			<table border="0" cellspacing="01" align="center" width="90%">
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center"><b>Mês</b>&nbsp;</td>

				  <td class="texto" align="center"><b>N</b>&nbsp;</td>
    	          <td class="texto" align="center"><b>Total</b></td>
    	          <td class="texto" align="center"><b>Venda média</b></td>
    	          <td class="texto" align="center"><b>Venda diária</b></td>
    	          <td class="texto" align="center"><b>Taxas</b></td>
				</tr>
		<?php	

			$i=0;
			$irows=0;

			if($rs_transacoes) {

				$irows=0;
				$total_pagamentos_page = 0;
				$total_taxas_page = 0;
				$total_pagtos_page = 0;
				$n_dias_total = 0;
				while($rs_transacoes_row = pg_fetch_array($rs_transacoes)){ 
					$bgcolor = ((++$i) % 2)?" bgcolor='F5F5FB'":"";
					$bgcolor = ((($rs_transacoes_row['sum_total']-$rs_transacoes_row['sum_taxas'])>0)?$bgcolor:" bgcolor='FFCC33'");
					$irows++;
					$total_pagamentos_page += ($rs_transacoes_row['sum_total']-$rs_transacoes_row['sum_taxas']);
					$total_taxas_page += $rs_transacoes_row['sum_taxas'];
					$total_pagtos_page += $rs_transacoes_row['n'];
					
					$total_sem_taxa = $rs_transacoes_row['sum_total']-$rs_transacoes_row['sum_taxas'];
					$n_compras = (($rs_transacoes_row['n']>0)?$rs_transacoes_row['n']:1);
					$total_medio = $total_sem_taxa/(($n_compras>0)?$n_compras:1);

					// venda diária
					$n_dias = cal_days_in_month(CAL_GREGORIAN, $rs_transacoes_row['imonth'], $rs_transacoes_row['imonth']);	// days in the month
					if((date("m")==$rs_transacoes_row['imonth']) && (date("Y")==$rs_transacoes_row['iyear'])) {
						$n_dias = date("d");	
					}

					$total_diario = $total_sem_taxa/(($n_dias>0)?$n_dias:1);
					$n_dias_total += $n_dias;

			?>
    	        <tr<?php echo $bgcolor?> valign="top">
    	          <td class="texto" align="center"><nobr><?php echo str_pad($rs_transacoes_row['iyear'], 4, "0", STR_PAD_LEFT)."-".str_pad($rs_transacoes_row['imonth'], 2, "0", STR_PAD_LEFT); ?></nobr></td>

    	          <td class="texto" align="center">&nbsp;<?php echo $rs_transacoes_row['n']?></td>
    	          <td class="texto" align="center" style="color:<?php echo ((($total_sem_taxa)>0)?"#3300CC":"#FF0000"); ?>" title="<?php echo "Em $n_dias dias"; ?>"><?php echo number_format(($total_sem_taxa), 2, ',', '.')?></td>  
    	          <td class="texto" align="center" style="color:<?php echo ((($total_medio)>0)?"#3300CC":"#FF0000"); ?>">&nbsp;<?php echo number_format(($total_medio), 2, ',', '.')?></td>  

    	          <td class="texto" align="center" style="color:<?php echo ((($total_medio)>0)?"#3300CC":"#FF0000"); ?>">&nbsp;<?php echo number_format(($total_diario), 2, ',', '.')?></td>  

				  <td class="texto" align="center" style="color:<?php echo (($rs_transacoes_row['sum_taxas']>0)?"#3300CC":"#CCCCCC")?>">&nbsp;<?php echo number_format(($rs_transacoes_row['sum_taxas']), 2, ',', '.')?></td>  

    	        </tr>
		<?php
				}

				if($irows==0) {
			?>
					<tr>
					  <td class="texto" align="center" colspan="12">&nbsp;<font color='#FF0000'>Não foram encontrados registros para os valores escolhidos (2)</font></td>
					</tr>
			<?php
				} else {
			?>
			<?php
/*
					<tr>
					  <td class="texto" align="right" colspan="2">Subtotal&nbsp;<font color='#FF0000'></font></td>
					  <td class="texto" align="center" style="color:<_?php echo (($total_pagtos_page>0)?"#3300CC":"#CCCCCC")?_>">&nbsp;<_?php echo $total_pagtos_page?_>&nbsp;<font color='#FF0000'></font></td>
					  <td class="texto" align="center" style="color:<_?php echo (($total_pagamentos_page>0)?"#3300CC":"#CCCCCC")?_>">&nbsp;<_?php echo number_format($total_pagamentos_page, 2, ',', '.')?_>&nbsp;<font color='#FF0000'></font></td>
					  <td class="texto" align="center" style="color:<_?php echo (($total_taxas_page>0)?"#3300CC":"#CCCCCC")?_>">&nbsp;<_?php echo number_format($total_taxas_page, 2, ',', '.')?_>&nbsp;<font color='#FF0000'></font></td>
					</tr>
*/
				$n_pagtos = (($total_pagtos>0)?$total_pagtos:1);
			?>
					<tr>
					  <td class="texto" align="right">Total&nbsp;</td>
					  <td class="texto" align="center" style="color:<?php echo (($total_pagtos>0)?"#3300CC":"#CCCCCC")?>">&nbsp;<b><?php echo $total_pagtos ?></b>&nbsp;</td>
					  <td class="texto" align="center" style="color:<?php echo (($total_pagamentos>0)?"#3300CC":"#CCCCCC")?>" title="<?php echo "Em $n_dias_total dias"; ?>">&nbsp;<b><?php echo number_format($total_pagamentos, 2, ',', '.')?></b>&nbsp;</td>
					  <td class="texto" align="center" colspan="2">&nbsp;&nbsp;</td>
					  <td class="texto" align="center" style="color:<?php echo (($total_taxas>0)?"#3300CC":"#CCCCCC")?>">&nbsp;<b><?php echo number_format($total_taxas, 2, ',', '.')?></b>&nbsp;</td>
					</tr>
					<?php
				$day_of_month = date("j");
				$days_in_month = date("t");
//echo $day_of_month."/".$days_in_month."<br>";

//echo "total_pagamentos: ".number_format($total_pagamentos, 2, ',', '.')."<br>";

					?>
					<tr>
					  <td class="texto" align="right" colspan="2">Média mensal&nbsp;</td>
					  <td class="texto" align="center" style="color:<?php echo (($total_pagamentos>0)?"#3300CC":"#CCCCCC")?>">&nbsp;<b><?php echo number_format($total_pagamentos/(($registros_total>0?$registros_total:1)), 2, ',', '.')?></b>&nbsp;</td>
					  <td class="texto" align="center" style="color:<?php echo (($total_pagamentos/$n_pagtos>0)?"#3300CC":"#CCCCCC")?>">&nbsp;<b><?php echo number_format($total_pagamentos/$n_pagtos, 2, ',', '.')?></b>&nbsp;</td>
					  <td class="texto" align="center" style="color:<?php echo (($total_pagamentos/$n_pagtos>0)?"#3300CC":"#CCCCCC")?>">&nbsp;<b><?php echo number_format($total_pagamentos/$n_dias_total, 2, ',', '.')?></b>&nbsp;</td>
					  <td class="texto" align="center" style="color:<?php echo (($total_taxas>0)?"#3300CC":"#CCCCCC")?>">&nbsp;<b><?php echo number_format($total_taxas/(($registros_total>0?$registros_total:1)), 2, ',', '.')?></b>&nbsp;</td>
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
	</table>

	<br>&nbsp;
	<table border="0" cellspacing="0" bgcolor="#FFFFFF" width="100%">
    <tr>
      	<td align="center" class="texto"><nobr>
      		<?php if($p > 1){ ?>
         	<input type="button" name="btAnterior" value=" < " OnClick="window.location='?p=<?php echo $p-1?><?php echo $varsel?>';" class="btn btn-sm btn-info">
         	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<?php } ?>
      		<?php if($p < ($registros_total/$registros)){ ?>
         	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
         	<input type="button" name="btProximo" value=" > " OnClick="window.location='?p=<?php echo $p+1?><?php echo $varsel?>';" class="btn btn-sm btn-info">
			<?php } ?></nobr>
      	</td>
    </tr>
	</table>
	<br>&nbsp;

	<table border='0' cellpadding='0' cellspacing='1' bordercolor='#cccccc' style='border-collapse:collapse;'>	
	  <tr align="center"> 
		<td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td><td bgcolor="#FFFFFF" class="texto">Processamento em <?php echo number_format(getmicrotime() - $time_start_stats, 2, '.', '.') ?> s.</font></td><td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td>
	  </tr>
	</table>

	</div>
	</center>

<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</html>

<?php 
	function get_dia_da_semana($dow) {
		switch($dow) {
			case 0:
				$sout = "Dom";
				break;
			case 1:
				$sout = "2aF";
				break;
			case 2:
				$sout = "3aF";
				break;
			case 3:
				$sout = "4aF";
				break;
			case 4:
				$sout = "5aF";
				break;
			case 5:
				$sout = "6aF";
				break;
			case 6:
				$sout = "Sab";
				break;
			default:
				$sout = "???";
				break;
		}
		return $sout;
	}
?>