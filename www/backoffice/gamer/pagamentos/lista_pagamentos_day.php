<?php 
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
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
set_time_limit(600);

$time_start_stats = getmicrotime();

//paginacao
$p = $_REQUEST['p'];
if(!$p) $p = 1;
$registros = 50;
$registros_total = 0;

if(!$btPesquisar) {
        if(!$tf_v_data_year && !$tf_v_data_month) {
                if(!$tf_v_data_year) {
                        $tf_v_data_year = date("Y");
                }
                if(!$tf_v_data_month) {
                        $tf_v_data_month = date("m");
                }

                $tf_v_data_year = date("Y", strtotime($tf_v_data_year."/".$tf_v_data_month."/01"));
                $tf_v_data_month = date("m", strtotime($tf_v_data_year."/".$tf_v_data_month."/01"));
//echo "Reset: Y-m: ".$tf_v_data_year."-".$tf_v_data_month."<br>";
        }
        if(!isset($tf_v_deposito_em_saldo)) {
                // para Luiz default -> todos
                if(b_IsUsuarioLuiz()) {
                        $tf_v_deposito_em_saldo = "";
                } else {
                        $tf_v_deposito_em_saldo = "P";
                }
        }
}
if($tf_v_data_month) {
        $tf_v_data_month_last_day = date("d",mktime(0, 0, 0, $tf_v_data_month+1, 0, $tf_v_data_year ));
}

//Validacoes
$msg = "";	

//Recupera as vendas
if($msg == ""){

        $sql  = "select 
                        TO_DATE(TO_CHAR(EXTRACT(YEAR FROM pgt.datainicio),'9999')||'-'||TO_CHAR(EXTRACT(MONTH FROM pgt.datainicio),'99')||'-'||TO_CHAR(EXTRACT(DAY FROM pgt.datainicio),'99'),'YYYY-MM-DD') as iday, count(*) as n,  
                        extract(dow from (TO_DATE(TO_CHAR(EXTRACT(YEAR FROM pgt.datainicio),'9999')||'-'||TO_CHAR(EXTRACT(MONTH FROM pgt.datainicio),'99')||'-'||TO_CHAR(EXTRACT(DAY FROM pgt.datainicio),'99'),'YYYY-MM-DD'))) as dow, 
                        sum(subtotal)/100 as sum_subtotal, sum(total)/100 as sum_total, sum(taxas) as sum_taxas 
                from tb_pag_compras pgt
                where 1=1 and total>0 "; 
                        // and tipo_deposito = 0 

        // Avoid empty value
        if(!$tf_v_tipo_transacao) $tf_v_tipo_transacao = "Completos";		
        // Validate
        if(($tf_v_tipo_transacao!="Completos") && ($tf_v_tipo_transacao!="Incompletos")  && ($tf_v_tipo_transacao!="Todos") ) $tf_v_tipo_transacao = "Completos";		

        // Filtros Where
        $sql_where = "";

        if($tf_v_data_year) {
                if($tf_v_data_month) {
                        $sql_where .= " and TO_DATE(TO_CHAR(EXTRACT(YEAR FROM pgt.datainicio),'9999')||'-'||TO_CHAR(EXTRACT(MONTH FROM pgt.datainicio),'9999')||'-'||TO_CHAR(EXTRACT(DAY FROM pgt.datainicio),'99'),'YYYY-MM-DD') between '".$tf_v_data_year."-".$tf_v_data_month."-01 00:00:00' and '".$tf_v_data_year."-".$tf_v_data_month."-".$tf_v_data_month_last_day." 23:59:59' ";
                } else {
                        $sql_where .= " and TO_DATE(TO_CHAR(EXTRACT(YEAR FROM pgt.datainicio),'9999')||'-'||TO_CHAR(EXTRACT(MONTH FROM pgt.datainicio),'9999')||'-'||TO_CHAR(EXTRACT(DAY FROM pgt.datainicio),'99'),'YYYY-MM-DD') between '".$tf_v_data_year."-01-01 00:00:00' and '".$tf_v_data_year."-12-31 23:59:59' ";
                }
        }

        if($tf_v_canal) {
                if(($tf_v_canal=="M") || ($tf_v_canal=="E") || ($tf_v_canal=="LR") || ($tf_v_canal=="LO") ) {
                        $sql_where .= " and pgt.tipo_cliente='".$tf_v_canal."'";
                }
        }

    $a_formas_aceitas = array("5", "6", "9", "A", "B", "E", "EG", "P", "CC","CD", "Z");
        $sql_where_forma = "";
        if(!in_array($tf_v_forma_pagamento, $a_formas_aceitas)) {
                $tf_v_forma_pagamento = ""; 
        }
        if($tf_v_forma_pagamento) {
                if($tf_v_forma_pagamento=="CC") {
                        $sql_where_forma = " and ".getSQLWhereParaPagtoCieloCredito()." ";
                } elseif($tf_v_forma_pagamento=="CD") {
                        $sql_where_forma = " and ".getSQLWhereParaPagtoCieloDebito()." ";
                } elseif($tf_v_forma_pagamento=="EG") {
                        $sql_where_forma = " and pgt.iforma='E' and valorpagtogocash>0 ";
                } else {
                        $sql_where_forma = " and pgt.iforma='".$tf_v_forma_pagamento."' ";
                }
        }
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

        }

        $sql .= " group by TO_DATE(TO_CHAR(EXTRACT(YEAR FROM pgt.datainicio),'9999')||'-'||TO_CHAR(EXTRACT(MONTH FROM pgt.datainicio),'99')||'-'||TO_CHAR(EXTRACT(DAY FROM pgt.datainicio),'99'),'YYYY-MM-DD') ";

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

                $sql .= " order by TO_DATE(TO_CHAR(EXTRACT(YEAR FROM pgt.datainicio),'9999')||'-'||TO_CHAR(EXTRACT(MONTH FROM pgt.datainicio),'99')||'-'||TO_CHAR(EXTRACT(DAY FROM pgt.datainicio),'99'),'YYYY-MM-DD') ";
                $sql .= " offset " . ($p - 1) * $registros . " limit " . $registros;
                // Ler novamente para esta página
                $rs_transacoes = SQLexecuteQuery($sql);
        }

}

$varsel = "&tf_v_data_year=$tf_v_data_year&tf_v_data_month=$tf_v_data_month";
$varsel .= "&tf_v_tipo_transacao=$tf_v_tipo_transacao&tf_v_canal=$tf_v_canal&tf_v_forma_pagamento=$tf_v_forma_pagamento";
?>
<script language="JavaScript">
<!--
	function reload() {
		document.form1.action = "lista_pagamentos_day.php";
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
<table class="txt-preto fontsize-pp table">
    <tr valign="top" align="center">
      <td>
			<form name="form1" method="post" action="lista_pagamentos_day.php">
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
    	          <td class="texto" align="center"><b>Mês da Compra</b></td>
    	          <td>&nbsp;</td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center"><nobr>&nbsp;
					<select id='tf_v_canal' name='tf_v_canal'> 
						<option value=''<?php echo (($tf_v_canal=="")?" selected":""); ?>>Todos os canais de venda</option> 
						<option value='M'<?php echo (($tf_v_canal=="M")?" selected":"") ?>>Money</option>
						<option value='E'<?php echo (($tf_v_canal=="E")?" selected":"") ?>>Money Express</option>
						<option value='LR'<?php echo (($tf_v_canal=="LR")?" selected":"") ?>>Lanhouse Pré</option>
						<option value='LO'<?php echo (($tf_v_canal=="LO")?" selected":"") ?>>Lanhouse Pós</option>
					  </select>
				  </td>
    	          <td class="texto" align="center">&nbsp;
					<select id='tf_v_data_year' name='tf_v_data_year'> 
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
					  </select> - 
					<select id='tf_v_data_month' name='tf_v_data_month'> 
						<option value=''<?php echo (($tf_v_data_month=="")?" selected":""); ?>>Todos os meses</option> 
				  <?php
					$month_start = 1;
					$month_now = (($tf_v_data_year==date("y"))?date("m"):12);
					for($i=$month_start;$i<=$month_now;$i++) {
						$simonth = (($i<10)?"0":"").$i;		// date("m") retorna "MM"
						?>
						<option value='<?php echo $simonth; ?>'<?php echo (($tf_v_data_month==$simonth)?" selected":""); ?>><?php echo Mes_Do_Ano($i); ?></option> 
						<?php
					}
				  ?>
					  </select>

				  </td>
				  <td class="texto" align="center">&nbsp;</td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center">
					<select id='tf_v_tipo_transacao' name='tf_v_tipo_transacao'> 
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
						<option value='CC'<?php echo (($tf_v_forma_pagamento=="CC")?" selected":"") ?>>Pagamentos Cielo CRÉDITO</option>
						<option value='CD'<?php echo (($tf_v_forma_pagamento=="CD")?" selected":"") ?>>Pagamentos Cielo DÉBITO</option>
						<option value='Z'<?php echo (($tf_v_forma_pagamento=="Z")?" selected":"") ?>>Pagamento Banco E-Prepag (BEPZ)</option>
					  </select>
				  </td>
				  <td class="texto" align="center">&nbsp;</td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center" colspan="2"><nobr>Código usuário: 

					<input name="tf_v_usuario" type="text" class="form2" value="<?php echo $tf_v_usuario ?>" size="10" maxlength="10">			
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
			
          <table class="table">
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center"><b>Data</b>&nbsp;</td>
    	          <td class="texto" align="center"><b>Dia</b></td>

				  <td class="texto" align="center" width="20%"><b>N</b>&nbsp;</td>
    	          <td class="texto" align="center"><b>Total(Pedido+Taxa)</b></td>
    	          <td class="texto" align="center"><b>Pedido</b></td>
    	          <td class="texto" align="center"><b>Venda média</b></td>
    	          <td class="texto" align="center"><b>Taxas</b></td>
				</tr>
		<?php	

			$i=0;
			$irows=0;

			if($rs_transacoes) {

				$irows=0;
				$total_pagamentos_page = 0;
                                $total_pedido_taxa = 0;
				$total_taxas_page = 0;
				$total_pagtos_page = 0;
				while($rs_transacoes_row = pg_fetch_array($rs_transacoes)){ 
					$bgcolor = ((++$i) % 2)?" bgcolor='F5F5FB'":"";
					$bgcolor = ((($rs_transacoes_row['sum_total']-$rs_transacoes_row['sum_taxas'])>0)?$bgcolor:" bgcolor='FFCC33'");
					$irows++;
					$total_pagamentos_page += ($rs_transacoes_row['sum_total']-$rs_transacoes_row['sum_taxas']);
					$total_taxas_page += $rs_transacoes_row['sum_taxas'];
					$total_pagtos_page += $rs_transacoes_row['n'];
					
					$total_sem_taxa = $rs_transacoes_row['sum_total']-$rs_transacoes_row['sum_taxas'];
					$n_compras = (($rs_transacoes_row['n']>0)?$rs_transacoes_row['n']:1);
					$total_medio = $total_sem_taxa/$n_compras;
                                        
                                        $total_pedido_taxa += $rs_transacoes_row['sum_total'];

			?>
    	        <tr<?php echo $bgcolor?> valign="top">
    	          <td class="texto" align="center"><nobr><?php echo $rs_transacoes_row['iday']?></nobr></td>
    	          <td class="texto" align="center">&nbsp;<?php echo get_dia_da_semana($rs_transacoes_row['dow']); ?></td>

    	          <td class="texto" align="center">&nbsp;<?php echo $rs_transacoes_row['n']?></td>
    	          <td class="texto" align="center" style="color:<?php echo ((($total_sem_taxa)>0)?"#3300CC":"#FF0000"); ?>"><?php echo number_format(($rs_transacoes_row['sum_total']), 2, ',', '.')?></td>  
    	          <td class="texto" align="center" style="color:<?php echo ((($total_sem_taxa)>0)?"#3300CC":"#FF0000"); ?>"><?php echo number_format(($total_sem_taxa), 2, ',', '.')?></td>  
    	          <td class="texto" align="center" style="color:<?php echo ((($total_medio)>0)?"#3300CC":"#FF0000"); ?>">&nbsp;<?php echo number_format(($total_medio), 2, ',', '.')?></td>  

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
				$n_pagtos = (($total_pagtos>0)?$total_pagtos:1);
				$s_media_amanha = "";
			?>
					<tr>
					  <td class="texto" align="right" colspan="2">Total&nbsp;</td>
					  <td class="texto" align="center" style="color:<?php echo (($total_pagtos>0)?"#3300CC":"#CCCCCC")?>">&nbsp;<b><?php echo $total_pagtos; ?></b>&nbsp;</td>
					  <td class="texto" align="center" style="color:<?php echo (($total_pedido_taxa>0)?"#3300CC":"#CCCCCC")?>">&nbsp;<b><?php echo number_format($total_pedido_taxa, 2, ',', '.'); ?></b>&nbsp;</td>
					  <td class="texto" align="center" style="color:<?php echo (($total_pagamentos>0)?"#3300CC":"#CCCCCC")?>" title="<?php echo $s_media_amanha; ?>">&nbsp;<b><?php echo number_format($total_pagamentos, 2, ',', '.'); ?></b>&nbsp;</td>
					  <td class="texto" align="center" >&nbsp;&nbsp;</td>
					  <td class="texto" align="center" style="color:<?php echo (($total_taxas>0)?"#3300CC":"#CCCCCC")?>">&nbsp;<b><?php echo number_format($total_taxas, 2, ',', '.'); ?></b>&nbsp;</td>
					</tr>
					<?php
				$day_of_month = date("j");
				$days_in_month = date("t");
                                $days_in_month_selected = date("t", strtotime($tf_v_data_year."/".$tf_v_data_month."/01"));
                                $days_in_month_selected = (($days_in_month_selected>0)?$days_in_month_selected:1);
				$vendas_total_mes_projecao = $total_pagamentos+(($days_in_month-$day_of_month)*($total_pagamentos/$day_of_month));	
					?>
					<tr>
					  <td class="texto" align="right" colspan="4">Média diária&nbsp;</td>
					  <td class="texto" align="center" style="color:<?php echo (($total_pagamentos>0)?"#3300CC":"#CCCCCC")?>" title="<?php 
							echo ( ($tf_v_data_year==date("Y")) && ($tf_v_data_month==date("m")) )?"Projeção de vendas para o mês: R$".number_format($vendas_total_mes_projecao, 2, ',', '.'):""; ?>">&nbsp;<b><?php echo number_format($total_pagamentos/(($registros_total>0?$registros_total:1)), 2, ',', '.')?></b>&nbsp;</td>
					  <td class="texto" align="center" style="color:<?php echo (($total_pagamentos/$n_pagtos>0)?"#3300CC":"#CCCCCC")?>">&nbsp;<b><?php echo number_format($total_pagamentos/$n_pagtos, 2, ',', '.')?></b>&nbsp;</td>
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
    <table class="table">
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
    <table class="table">	
	  <tr align="center"> 
		<td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td><td bgcolor="#FFFFFF" class="texto">Processamento em <?php echo number_format(getmicrotime() - $time_start_stats, 2, '.', '.') ?> s.</font></td><td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td>
	  </tr>
	</table>
	</div>
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