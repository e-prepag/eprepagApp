<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/inc_functions.php";

$time_start_stats = getmicrotime();

if(!isset($dd_ano))   $dd_ano      = date('Y');
if(!isset($dd_mes))   $dd_mes      = date('m');

//Geraldo o vetor contendo os prefixos previstos
$sql = "select opr_codigo,opr_prefixo_ponto_certo from operadoras where opr_distribui_ponto_certo = 1;";
$ret_vetor = SQLexecuteQuery($sql);
if(!$ret_vetor) die("Erro na instrução que seleciona Lista de Publisher que distribuem PINs pela rede Potno Certo.\n");
else {
    while($ret_vetor_row = pg_fetch_array($ret_vetor)){
        $vetorPublishers[$ret_vetor_row['opr_prefixo_ponto_certo']] = $ret_vetor_row['opr_codigo'];
    }//end while
}//end else do if(!$ret_vetor)


//Recupera as vendas
if(!empty($dd_ano) && !empty($dd_mes)){
    
        // Query para Levantamento de Dados
        $sql  = "SELECT round(CAST (extract (day from data_transacao::date) as int), 0) as dia,
                        round(CAST (extract (month from data_transacao::date) as int),0) as mes, 
                        round(CAST (extract (year from data_transacao::date) as int),0) as ano, 
                        count(*) as quantidade, 
                        sum(valor) as total
                 FROM pos_transacoes_ponto_certo WHERE (EXTRACT (month FROM data_transacao) = ".((int)$dd_mes).") and (extract (year from data_transacao) = ".$dd_ano.") 
                ";
        if(!empty($opr_codigo)) {
            $sql .= " AND opr_codigo = ".$opr_codigo;
        }//end 
        $sql .= " 
                 GROUP BY dia, mes, ano
                 ORDER BY dia;"; 
	//echo $sql;
	$rs_pins = SQLexecuteQuery($sql);
	if($rs_pins) $registros_total = pg_num_rows($rs_pins);
	if(!$rs_pins || pg_num_rows($rs_pins) == 0) $msg = "Nenhum pin encontrado.\n";
        
} //end if(!empty($dd_ano) && !empty($dd_mes))
?>
    <div class="col-md-12">
        <ol class="breadcrumb top10">
            <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
            <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
            <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
        </ol>
    </div>

    <form name="form1" method="post" action="" onSubmit="return validador();">
    <table class="table txt-preto fontsize-pp">
    <tr><td>&nbsp;</td></tr>
	<tr valign="top" align="center">
      <td align="center">
        <input type="hidden" name="p" value="<?php if(isset($p)) echo $p; ?>">
        <table class="table">
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center" colspan="3"><b><?php echo " (Total: ".$registros_total." registro"?><?php if($registros_total>1) echo "s"; ?><?php echo ")"?></b></td>
    	        </tr>
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center"><b>Publisher</b></td>
    	          <td class="texto" align="center"><b>Ano</b></td>
    	          <td class="texto" align="center"><b>Mês</b></td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
                  <td class="texto" align="center">
                    <select name="opr_codigo" id="opr_codigo" class="combo_normal">
                            <option value="" <?php  if(empty($opr_codigo)) echo "selected" ?>>Todos</option>
                        <?php  
                            if(isset($vetorPublishers)){
                                foreach ($vetorPublishers as $key => $value) { 
                        ?>
                            <option value="<?php  echo $value ?>" <?php  if($opr_codigo == $value) echo "selected" ?>><?php  echo $key; ?></option>
                        <?php  }} ?>
                    </select>    
                  </td>
                  <td class="texto" align="center">
                    <select name="dd_ano" id="dd_ano" class="combo_normal">
                      <?php  for($i =  date('Y'); $i >= (int)(substr($inic_oper_data, 6)) ; $i--) { ?>
                            <option value="<?php  echo $i ?>" <?php  if($dd_ano == $i) echo "selected" ?>><?php  echo $i; ?></option>
                      <?php  } ?>
                    </select>    
                  </td>
    	          <td class="texto" align="center">
                      <select name="dd_mes" id="dd_mes" class="combo_normal">
                      <?php
                            for ($codigoMes=1; $codigoMes<=12; $codigoMes++){
                                   switch ($codigoMes){
                                           case 1:  $nomeMes = "Janeiro"; break;
                                           case 2:  $nomeMes = "Fevereiro"; break;
                                           case 3:  $nomeMes = "Março"; break;
                                           case 4:  $nomeMes = "Abril"; break;
                                           case 5:  $nomeMes = "Maio"; break;
                                           case 6:  $nomeMes = "Junho"; break;
                                           case 7:  $nomeMes = "Julho"; break;
                                           case 8:  $nomeMes = "Agosto"; break;
                                           case 9:  $nomeMes = "Setembro"; break;
                                           case 10: $nomeMes = "Outubro"; break;
                                           case 11: $nomeMes = "Novembro"; break;
                                           case 12: $nomeMes = "Dezembro"; break;
                                   }
                                   if (strlen($codigoMes) == 1){
                                           $codigoMes = '0'.$codigoMes;
                                   }

                                   echo '<option value="'.$codigoMes.'"';
                                   if ($dd_mes == $codigoMes){
                                           echo ' SELECTED';
                                   }
                                   echo '>'.$nomeMes.'</option>';
                            }
                        ?>
                        </select>
		  </td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center" colspan="3">
                      <input type="submit" name="btPesquisar" value="Pesquisar" class="btn btn-sm btn-info">
        	  </td>
    	        </tr>
        </table>
        
        <table class="table table-bordered">
                	<?php	
			$i=0;
			$irows=0;
			if($rs_pins && $registros_total > 0) {
                                ?>
                                <tr bgcolor="F0F0F0">
                                      <td class="texto" align="center"><b>Dia</b>&nbsp;</td>
                                      <td class="texto" align="center"><b>Dia da Semana</b>&nbsp;</td>
                                      <td class="texto" align="center"><b>Quantidade</b>&nbsp;</td>
                                      <td class="texto" align="center"><b>Total</b>&nbsp;</td>
                                </tr>
                                <?php
                                $total_geral = 0;
                                $total_quantidade = 0;
				while($rs_pins_row = pg_fetch_array($rs_pins)){ 
					$bgcolor = (($i++) % 2)?" bgcolor=\"F5F5FB\"":"";
					$irows++;
                                        $total_geral += $rs_pins_row['total'];
                                        $total_quantidade += $rs_pins_row['quantidade'];
                                        $dia =  ((strlen($rs_pins_row['dia'])<=1)?"0":"").$rs_pins_row['dia']."/".((strlen($rs_pins_row['mes'])<=1)?"0":"").$rs_pins_row['mes']."/".$rs_pins_row['ano'];
                                        $dia_calculo =  $rs_pins_row['ano']."/".((strlen($rs_pins_row['mes'])<=1)?"0":"").$rs_pins_row['mes']."/".((strlen($rs_pins_row['dia'])<=1)?"0":"").$rs_pins_row['dia'];
                                        $dia_sem = date("w", strtotime($dia_calculo));
                                        if($dia_sem==1) {
                                            echo "<tr bgcolor='#000000'><td colspan='4' height='1'></td></tr>\n";
                                        }

 			?>
                        <tr<?php echo $bgcolor?> valign="top">
                            <td class="texto" align="center">&nbsp;<?php echo $dia;?></td>
                            <td class="texto" align="center">&nbsp;<?php echo get_day_of_week($dia_calculo);?></td>
                            <td class="texto" align="center">&nbsp;<?php echo $rs_pins_row['quantidade']; ?></td>
                            <td class="texto" align="right">&nbsp;R$ <?php echo number_format($rs_pins_row['total'], 2, ",", "."); ?></td>
                        </tr>
                        <?php	
                                }//end while
                        ?>
			<tr bgcolor="F0F0F0">
 			  <td class="texto" align="center">&nbsp;</td>
 			  <td class="texto" align="center">&nbsp;</td>
			  <td class="texto" align="center"><b>Quantidade Total: <?php echo $total_quantidade;?></b></td>
			  <td class="texto" align="right"><b>Total Geral: R$<?php echo number_format($total_geral, 2, ",", ".");;?></b></td>
			</tr>
                	<?php
			} 
			if($irows==0) {
                        ?>
					<tr>
					  <td class="texto" align="center" colspan="4">&nbsp;<font color='#FF0000'>N&atilde;o foram encontradas dados para o período</font></td>
					</tr>
			<?php
                        }
			?>
			</table>
      </td>
    </tr>
	</table>

	<br>&nbsp;
	<br>&nbsp;

	<table border='0' cellpadding='0' cellspacing='1' bordercolor='#cccccc' style='border-collapse:collapse;'>	
	  <tr align="center"> 
		<td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td><td bgcolor="#FFFFFF" class="texto">Processamento em <?php echo number_format(getmicrotime() - $time_start_stats, 2, '.', '.') ?> s.</font></td><td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td>
	  </tr>
	</table>

	</form>
	</center>

</body>
</html>
<?php
//phpinfo();
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>