<?php 
	//error_reporting(E_ALL); 
	//ini_set("display_errors", 1); 
//die("ABANOME=".$_GET['abanome']);
	
	session_start();

	//desenvolvimento	
        require_once "../../../../includes/constantes.php";
	$_SERVER['DOCUMENT_ROOT'] = $raiz_do_projeto . "public_html/sys";
	require_once ($_SERVER['DOCUMENT_ROOT']."/includes/language/eprepag_lang_".$_SESSION['langNome'].".inc.php");

	require_once $raiz_do_projeto . "class/sys/classegrid.php";
	require_once $raiz_do_projeto . "includes/sys/inc_stats.php";

	$time_start_stats = getmicrotime();

	//echo $_GET['abanomeOld'].":_GET['abanomeOld']<br>";
	//echo $_GET['abanome'].":_GET['abanome']<br>";
	//echo $abanome.":abanome<br>";

	if (!empty($_GET['abanomeAux'])) {	
		$abanome	= 	$_GET['abanomeAux'];
	}
	elseif(!empty($_GET['abanome']))  {	
		$abanome	= 	$_GET['abanome'];
		$inicial	=	0;
	}
	elseif(!empty($abanome))  {	
		$abanome	= 	$abanome;
	}
	else {
		$abanome	= 	null;
	}
        
        $_SESSION["script"] = "POS_stats_abas.php";
	
//echo '<pre>';
//print_r($_GET);
//echo '</pre><hr>';


	if(empty($_GET['ncamp']))		$ncamp		= 'n';	else $ncamp		= $_GET['ncamp'];
	if(empty($_GET['inicial']))		$inicial	= 0;	else $inicial	= $_GET['inicial'];
	//echo "INICIAL: ".$inicial." GET: ".$_GET['inicial']."<br>";
	if(empty($_GET['range']))		$range		= 1;	else $range		= $_GET['range'];
	if(empty($_GET['ordem']))		$ordem		= 1;	else $ordem		= $_GET['ordem'];
	//echo "ORDEM: ".$ordem." GET: ".$_GET['ordem'].$_GET['abanomeAux']."<br>";
	if(empty($_GET['max']))			$max		= 100;	else $max		= $_GET['max']; //$qtde_reg_tela;
	if(empty($_GET['crescente']))	$crescente	= 'ASC';else $crescente	= $_GET['crescente']; 
	if($_GET['abanomeOld']<>$_GET['abanome']) {
		$ordem		= 1;
		$inicial	= 0;
	}
	if ($crescente=='ASC') {
		$crescente_depois = 'DESC';
	}
	else {
		$crescente_depois = 'ASC';
	}
	
	//echo"SCRIPT: ".$script.basename($_SERVER['PHP_SELF'])."<br>";
	$img_anterior = "/sys/imagens/anterior.gif";
	$img_proxima  = "/sys/imagens/images/proxima.gif";
	$default_add  = "abas.php";//nome_arquivo($PHP_SELF);//"abas.php"
	$range_qtde   = $qtde_range_tela;
	$varsel = "&script=".basename($_SERVER['PHP_SELF']);
	$varsel .= "&abanomeAux=".urlencode($abanome)."&dd_operadora=".$dd_operadora."&dd_mode=".$dd_mode."&ordem=".$ordem."&button=".$_GET['button'];
			
		

/*
echo '<pre>';
print_r($_GET);
echo '<hr>';
print_r($_SESSION);
echo '</pre><hr>';
echo $_SESSION['langNome'].":Linguagem<br>";
*/
//die("ABANOME=".$_GET['abanome']);

/*if (!empty($abanome)) {
	echo "entrou no IF<br>";
	$raiz_do_projeto = "C:\\Sites\\E-Prepag";
	//desenvolvimento	
	if(false) $raiz_do_projeto = "D:\\Projetos\\Outros\\E-Prepag\\Sites\\Producao";
	$_SERVER['DOCUMENT_ROOT'] = $raiz_do_projeto . "\\www\\web\\sys";	//"\\backoffice\\web";
	require_once ($_SERVER['DOCUMENT_ROOT']."/incs/lang/eprepag_lang_".$_SESSION['langNome'].".inc.php");
	echo ($_SERVER['DOCUMENT_ROOT']."/incs/lang/eprepag_lang_".$_SESSION['langNome'].".inc.php");
}*/
	
	$dd_operadora = $_SESSION['dd_operadora'];
	$dd_mode = $_SESSION['dd_mode'];
	$grid=new grid();

	$bg_col_01 = "#FFFFFF";
	$bg_col_02 = "#EEEEEE";
	$bg_col = $bg_col_01;
	
	if($_SESSION["tipo_acesso_pub"]=='PU') {
		$dd_operadora = $_SESSION["opr_codigo_pub"];
		$Submit = "Buscar";
	}

	$where_operadora_pos = "";
	$where_opr_1 = "";
	$where_opr_2 = "";

	if($_SESSION["tipo_acesso_pub"]=='PU') {
		$dd_operadora = $_SESSION["opr_codigo_pub"];
		if(($dd_operadora==13) || ($dd_operadora==16) || ($dd_operadora==17) ) { 
			$where_operadora_pos = " ve_jogo='".(($dd_operadora==13)?"OG":(($dd_operadora==16)?"HB":(($dd_operadora==17)?"MU":"??")))."'";
		} else {
			$where_operadora_pos = "";
		}
	}

	$dd_operadora_nome = "";
	if($dd_operadora) {
		$where_opr_1 = " (t0.opr_codigo = ".$dd_operadora.") ";

		if($dd_operadora==13)	//($dd_operadora_nome=='ONGAME') 
			$where_opr_2 = " (ve_jogo = 'OG') ";
		elseif  ($dd_operadora==17)	//($dd_operadora_nome=='MU ONLINE') 
			$where_opr_2 = " (ve_jogo = 'MU') ";
		elseif  ($dd_operadora==16)	//($dd_operadora_nome=='HABBO HOTEL') 
			$where_opr_2 = " (ve_jogo = 'HB') ";
		else
			$where_opr_2 = " (ve_jogo = 'xx') ";

		$resopr_nome = pg_exec($connid, "select opr_nome from operadoras where (opr_status = '1') and (opr_codigo='".$dd_operadora."') order by opr_ordem");
		if($pgopr_nome = pg_fetch_array ($resopr_nome)) { 
			$dd_operadora_nome = $pgopr_nome['opr_nome'];
		} 

	} 

	if($_SESSION["tipo_acesso_pub"]=='PU') {
		$sqlopr = "select opr_nome, opr_codigo from operadoras where (opr_status = '1') and (opr_codigo='".$dd_operadora."') order by opr_ordem";
	} else {
		$sqlopr = "select opr_nome, opr_codigo from operadoras where (opr_status != '0') order by opr_ordem";
	}
	$resopr = pg_exec($connid, $sqlopr);
	
	if(!empty($_SESSION['script'])) {
		$script  = $_SESSION['script'];
	}
        //$script  = "POS_stats_abas.php";
	//print_r($_SESSION);
	//echo '<hr>';
	//echo "dd_operadora: ".$dd_operadora."<br>";
	//echo "dd_mode: ".$dd_mode."<br>";
?>
	<form name="form1" id="form1" method="get" action="abas.php">
            <input type="hidden" name="script" id="script" value="<?php echo $script; ?>" />
		<?php
			if($_SESSION["tipo_acesso_pub"]=='PU') {
				?>
				<span style="font-weight: bold"><?=$_SESSION["opr_nome"]?></span>
				<input type="hidden" name="dd_operadora" id="dd_operadora" value="<?=$dd_operadora?>">
                <?php
			} else {
				?>	
		        <?php echo LANG_STATISTICS_OPERATOR; ?>:
                <select name="dd_operadora" id="dd_operadora" class="combo_normal">
                <option value=""><?php echo LANG_POS_ALL_OPERATOR; ?></option>
				<?php
				$a_operadoras = array(13, 16, 17);	// "ONGAME", "HABBO HOTEL", "MU ONLINE"
	
				while ($pgopr = pg_fetch_array ($resopr)) { 
					if (in_array($pgopr['opr_codigo'], $a_operadoras)) {
						?>
						<option value="<?php echo $pgopr['opr_codigo'] ?>" <?php if($pgopr['opr_codigo'] == $dd_operadora) echo "selected" ?>><?php echo $pgopr['opr_nome'] ?></option>
				<?php
					} 
				} 
				?>
				</select>
				<?php
			} 
			?>
				<input type="submit" name="button" id="button" value="<?php echo LANG_SELECT;?>" />
				</font>			
	</form>
        <div id="pager-<?php echo $abanome;?>" style="top: 0px; position: static;">
            <form>
                <!--
				img src="grid/tablesorter.com/addons/pager/icons/first.png" class="first"/>
                <img src="grid/tablesorter.com/addons/pager/icons/prev.png" class="prev"/>
                <input type="text" class="pagedisplay"/>
                <img src="grid/tablesorter.com/addons/pager/icons/next.png" class="next"/>
                <img src="grid/tablesorter.com/addons/pager/icons/last.png" class="last"/>
                <select class="pagesize">
                    <option selected="selected" value="10">10</option>
                    <option  value="30">30</option>
                </select
				-->
            </form>    
        </div>       
    
<br>
<div id="count-results-<?php echo $abanome;?>" name="count-results-<?php echo $abanome;?>" align="left">
</div>
<?php

if($_SESSION['button'] == LANG_SELECT) {
//die('<hr>stop');
//	$days_in_month = MonthDays($iMonth, $iYear)

	$imonth = date("n"); // or any value from 1-12
	$iyear	= date("Y"); // or any value >= 1
	$days_in_month = date("t",mktime(0,0,0,$imonth,1,$iyear));
//	echo "days_in_month: $days_in_month<br>";

	// Totais de Cadastros 
	$n_cadastros = get_ncadastros("P", addWhereClause($extra_where, $where_opr_2), $smode);
//echo "n_cadastros: $n_cadastros<br>";

	// Totais de Vendas
	$sql = get_sql_query("P", "totais_de_vendas", addWhereClause($extra_where, $where_opr_2), $smode);
//echo "$sql<br>";
	$total_vendas = 0;
	$n_vendas = 0;
	
	$vendas_estado = SQLexecuteQuery($sql);
	//Adicionado por Wagner
        if($vendas_estado)
            $total_table = pg_num_rows($vendas_estado);
        else
            $total_table = 0;
//	$sql .= " order by ".$ordem; 
//	$sql .= " limit ".$max; 
//	$sql .= " offset ".$inicial;
//echo "$sql<br>";
//	$vendas_estado = SQLexecuteQuery($sql);
	
	
	if($vendas_estado) {
		while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
			$total_vendas = $vendas_estado_row['vendas'];
			$n_vendas = $vendas_estado_row['n'];
		}
	} else {
		echo "<tr bgcolor='".$bg_col."'><td align='center' colspan='5'><font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font></td></tr>";
	}

	// Datas Limites no BD
	$sql = get_sql_query("P", "datas_limites_no_bd", addWhereClause($extra_where, $where_opr_2), $smode);
	
	$data_min = date("Y-m-d");
	$data_max = date("Y-m-d");
	$vendas_estado = SQLexecuteQuery($sql);
	//Adicionado por Wagner
	if($vendas_estado)
            $total_table = pg_num_rows($vendas_estado);
        else
            $total_table = 0;
	//$sql .= " order by ".$ordem; 
	//$sql .= " limit ".$max; 
	//$sql .= " offset ".$inicial;
	//echo "$sql<br>";
	//$vendas_estado = SQLexecuteQuery($sql);
	

	if($vendas_estado) {
		while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
			$data_min = $vendas_estado_row['data_min'];
			$data_max = $vendas_estado_row['data_max'];
		}
		if(!$data_min) $data_min = date("Y-m-d");
		if(!$data_max) $data_max = date("Y-m-d");
	} else {
		echo "<tr bgcolor='".$bg_col."'><td align='center' colspan='5'><font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font></td></tr>";
	}

//echo $abanome.'<hr>';

// aqui vamos apresentar os modulos solicitados
	if($abanome == LANG_STATISTICS_FOR_CITY) {
		// Cidade
			$sql = get_sql_query("P", "por_cidade", addWhereClause($extra_where, $where_opr_2), $smode);
			
			$vendas_estado = SQLexecuteQuery($sql);
			//echo $sql."<br>PG_NUM_ROWS:".pg_num_rows($vendas_estado)."<br>";
			//Adicionado por Wagner
			if($vendas_estado)
                            $total_table = pg_num_rows($vendas_estado);
                        else
                            $total_table = 0;
			//echo "TOTAL_TABLE: [".$total_table."]<br>";
			$sql = substr($sql,0,strpos($sql, 'order'));
			$sql .= " order by ".$ordem." ".$crescente; 
			$sql .= " limit ".$max; 
			$sql .= " offset ".$inicial;
			//echo "$sql<br>";
			$vendas_estado = SQLexecuteQuery($sql);
			
			$previous_value = -1;
			$bg_col = $bg_col_01;
			
			$colunas[] = LANG_CITY;
			$colunas[] = LANG_STATE;
			$colunas[] = LANG_NUMBER_SALES;
			$colunas[] = LANG_VALUE_SALES;
			//$colunas[] = LANG_VALUE;
			
			if($vendas_estado) {
				while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
					
					$cidade[] = $vendas_estado_row['ve_cidade'];
					$estado[] = $vendas_estado_row['ve_estado'];
					$nven[]   = $vendas_estado_row['n'];
					$vven[]	  =	number_format(($vendas_estado_row['vendas']), 2, ',', '.');
					//$vper[]   = number_format(100*($vendas_estado_row['vendas'])/(($total_vendas==0)?1:$total_vendas), 2, ',', '.');
				}
				$previous_value = $vendas_estado_row['vendas'];
				$retorno =  $grid->gera_grid_cidade($colunas,$cidade,$estado,$nven,$vven,$vper,$crescente_depois,LANG_STATISTICS_FOR_CITY,$ordem);
				require_once('grid.php');			
		
			} else {
				echo "<font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font>";
			}
		//Fim
	}
		
	//Dia
	if($abanome == LANG_STATISTICS_FOR_DAY) {

		//echo "ENTROU DIA<BR>";
		// Por dia
		$sql = get_sql_query("P", "por_dia", addWhereClause($extra_where, $where_opr_2), $smode);
		
		$vendas_estado = SQLexecuteQuery($sql);
		//Adicionado por Wagner
		$total_table = pg_num_rows($vendas_estado);
		$sql = substr($sql,0,strpos($sql, 'order'));
		$sql .= " order by ".$ordem." ".$crescente; 
		$sql .= " limit ".$max; 
		$sql .= " offset ".$inicial;
		$vendas_estado = SQLexecuteQuery($sql);

		$bg_col = $bg_col_01;
		$n_dias = pg_num_rows($vendas_estado);

		$colunas[] = LANG_DAY;
		$colunas[] = LANG_WEEK;
		$colunas[] = LANG_NUMBER_SALES;
		$colunas[] = LANG_VALUE_SALES;
		//$colunas[] = LANG_VALUE;

		if($vendas_estado) {
			while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
				$dia[] 	= $vendas_estado_row['data'].'<br>';
				$dias[] = get_day_of_week($vendas_estado_row['data']).'<br>';
				$nven[] = $vendas_estado_row['n'].'<br>';
				$vven[] = number_format(($vendas_estado_row['vendas']), 2, ',', '.').'<br>';
				//$vper[] = number_format(100*($vendas_estado_row['vendas'])/(($total_vendas==0)?1:$total_vendas), 2, ',', '.').'<br>';

				$previous_value = $vendas_estado_row['vendas'];
			}
				$retorno =  $grid->gera_grid_dia($colunas,$dia,$dias,$nven,$vven,$vper,$crescente_depois,LANG_STATISTICS_FOR_DAY,$ordem);
				require_once('grid.php');				
		} else {
			echo "<font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font>";
		}
		
		echo "<br><table><tr bgcolor='#CCFFCC'><td align='right' colspan='2'><a name='#Totais'></a><b>".LANG_STATISTICS_TOTAL."</b></td><td align='center'><b>".$n_vendas."</b></td><td align='center'><b>".number_format(($total_vendas), 2, ',', '.')."</b> <br><b>";
		if(strlen($dd_operadora)==0) {
			echo "(EPP: ".number_format(($total_vendas*0.04), 2, ',', '.').")</b>";
		}
		echo "</td><td>&nbsp;</td></tr>";
		echo "<tr><td align='center' colspan='5'><b>".LANG_STATISTICS_MEDIUM."</b> ".LANG_STATISTICS_IN." ".$n_dias." ".LANG_DAYS.": R\$".number_format(($total_vendas/(($n_dias>0)?$n_dias:1)), 2, ',', '.')."/".LANG_DAYS."&nbsp;<br>";
		if(strlen($dd_operadora)==0) {
			echo "(EPP: R\$".number_format(($total_vendas*0.04/(($n_dias>0)?$n_dias:1)), 2, ',', '.')."/".LANG_DAYS.")<br>";
			echo (LANG_STATISTICS_PROJECTION_EPP).". $days_in_month ".LANG_DAYS.": R\$".number_format(($days_in_month*$total_vendas*0.04/(($n_dias>0)?$n_dias:1)), 2, ',', '.');
		} else {
			echo (LANG_STATISTICS_PROJECTION)." $days_in_month ".LANG_DAYS.": R\$".number_format(($days_in_month*$total_vendas/(($n_dias>0)?$n_dias:1)), 2, ',', '.');
		}
		echo "&nbsp;</td></tr>";
		echo "</table><br>";
	// Fim
}	
	// Dia da Semana
		if($abanome == LANG_STATISTICS_FOR_WEEK_DAY) {
			$sql = get_sql_query("P", "por_dia_da_semana", addWhereClause($extra_where, $where_opr_2), $smode);
			//echo "sql: $sql<br>";	
			
			$vendas_estado = SQLexecuteQuery($sql);
			//Adicionado por Wagner
			$total_table = pg_num_rows($vendas_estado);
			$sql = substr($sql,0,strpos($sql, 'order'));
			$sql .= " order by ".$ordem." ".$crescente; 
			$sql .= " limit ".$max; 
			$sql .= " offset ".$inicial;
			//echo $sql;
			$vendas_estado = SQLexecuteQuery($sql);

			$bg_col = $bg_col_01;
			$n_dias = pg_num_rows($vendas_estado);

			$colunas[] = LANG_WEEK;
			$colunas[] = LANG_NUMBER_SALES;
			$colunas[] = LANG_VALUE_SALES;
			//$colunas[] = LANG_VALUE;			
			
			if($vendas_estado) {
				while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
					$diasemana[] 	= get_day_of_week_db($vendas_estado_row['dow']);
					$nvenda[] 		= $vendas_estado_row['n'];
					$valorvendas[] 	= number_format(($vendas_estado_row['vendas']), 2, ',', '.');
					//$percvalor[] 	= number_format(100*($vendas_estado_row['vendas'])/(($total_vendas==0)?1:$total_vendas), 2, ',', '.');
					
					$previous_value = $vendas_estado_row['vendas'];
				}
				$retorno =  $grid->gera_grid_dia_semana($colunas,$diasemana,$nvenda,$valorvendas,$percvalor,$crescente_depois,LANG_STATISTICS_FOR_WEEK_DAY,$ordem);
				require_once('grid.php');				
			} else {
				echo "<font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font>";
			}
		}
	// Fim

	// Por Estabelecimento
	if($abanome == LANG_STATISTICS_FOR_ESTABLISHMENT) {
			// ".LANG_STATISTICS_FOR_ESTABLISHMENT."
			$sql = get_sql_query("P", "por_estabelecimento_barra", addWhereClause($extra_where, $where_opr_2), $smode);
			
			//echo "sql: $sql<br>";
			$vendas_estado = SQLexecuteQuery($sql);
			//Adicionado por Wagner
			$total_table = pg_num_rows($vendas_estado);
			$sql = substr($sql,0,strpos($sql, 'order'));
			$sql .= " order by ".$ordem." ".$crescente; 
			$sql .= " limit ".$max; 
			$sql .= " offset ".$inicial;
			$vendas_estado = SQLexecuteQuery($sql);
			
			//echo "pg_num_rows(vendas_estado): ".pg_num_rows($vendas_estado)."<br>";
			$previous_value = -1;
			$bg_col = $bg_col_01;
	
			$colunas[] = LANG_ESTABLISHMENT;
			$colunas[] = LANG_TYPE;
			$colunas[] = LANG_START_DATE;
			$colunas[] = LANG_LAST_DATE;
			$colunas[] = LANG_ABANDON;
			$colunas[] = LANG_FIRST_LAST;
			$colunas[] = LANG_CITY;
			$colunas[] = LANG_UF;
			$colunas[] = LANG_NUMBER_SALES;
			$colunas[] = LANG_VALUE_SALES;
			//$colunas[] = LANG_VALUE;		
					
			if($vendas_estado) {
				$i = 0;
				$a_vendas_ultimo_mes = array();
				$today1 = strtotime ('now');
				while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
					$a_vendas_ultimo_mes[$i++] = $vendas_estado_row;
					
					
					$estabe[] 		= $vendas_estado_row['ve_estabelecimento'];
					$tipo[] 		= $vendas_estado_row['ve_estabtipo'];
					$dtini[]		= date('Y-m-d',strtotime($vendas_estado_row['primeira_venda']));
					$dtfim[]		= date('Y-m-d',strtotime($vendas_estado_row['ultima_venda']));
					$aband[]		= intval(($today1 - strtotime($vendas_estado_row['ultima_venda']))/86400+1);
					//$aband[]		= $vendas_estado_row['abandonou'];
					$ultvenda[] 	= get_delay_alert_live($vendas_estado_row['primeira_venda'], $vendas_estado_row['ultima_venda']);
					$cidade[] 		= $vendas_estado_row['ve_cidade'];
					$uf[] 			= $vendas_estado_row['ve_estado'];
					$nvenda[] 		= $vendas_estado_row['n'];
					$valorvendas[] 	= number_format(($vendas_estado_row['vendas']), 2, ',', '.');
					//$percvalor[] 	= number_format(100*($vendas_estado_row['vendas'])/(($total_vendas==0)?1:$total_vendas), 2, ',', '.');
					
					$previous_value = $vendas_estado_row['vendas'];
				}
				$retorno =  $grid->gera_grid_estabelecimento($colunas,$estabe,$tipo,$dtini,$dtfim,$aband,$ultvenda,$cidade,$uf,$nvenda,$valorvendas,$percvalor,$crescente_depois,LANG_STATISTICS_FOR_ESTABLISHMENT,$ordem);
				require_once('grid.php');	
			} else {
				echo "<font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font>";
			}
	}
	// fim
	
	// Estado
	if($abanome == LANG_STATISTICS_FOR_STATE) {
		// ".LANG_STATISTICS_FOR_STATE."
		$sql = get_sql_query("P", "por_estado", addWhereClause($extra_where, $where_opr_2), $smode);
		
		$vendas_estado = SQLexecuteQuery($sql);
		//Adicionado por Wagner
		$total_table = pg_num_rows($vendas_estado);
		$sql = substr($sql,0,strpos($sql, 'order'));
		$sql .= " order by ".$ordem." ".$crescente; 
		$sql .= " limit ".$max; 
		$sql .= " offset ".$inicial;
		$vendas_estado = SQLexecuteQuery($sql);
		
		$previous_value = -1;
		$bg_col = $bg_col_01;
	
		$colunas[] = LANG_STATE;
		$colunas[] = LANG_NUMBER_SALES;
		$colunas[] = LANG_VALUE_SALES;
		//$colunas[] = LANG_VALUE;	
			
		if($vendas_estado) {
			while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
				
				$estado[] 		= $vendas_estado_row['ve_estado'];
				$nvenda[] 		= $vendas_estado_row['n'];
				$valorvendas[] 	= number_format(($vendas_estado_row['vendas']), 2, ',', '.');
				//$percvalor[] 	= number_format(100*($vendas_estado_row['vendas'])/(($total_vendas==0)?1:$total_vendas), 2, ',', '.');
				
				$previous_value = $vendas_estado_row['vendas'];
			}
			$retorno =  $grid->gera_grid_estado($colunas,$estado,$nvenda,$valorvendas,$percvalor,$crescente_depois,LANG_STATISTICS_FOR_STATE,$ordem);
			require_once('grid.php');			
		} else {
			echo "<font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font>";
		}
	}
	// fim
	
	// Jogo
	if($abanome == LANG_STATISTICS_FOR_GAME) {
		// ".LANG_STATISTICS_FOR_GAME."
		$sql = get_sql_query("P", "por_jogo", addWhereClause($extra_where, $where_opr_2), $smode);
		
		$vendas_estado = SQLexecuteQuery($sql);
		//Adicionado por Wagner
		$total_table = pg_num_rows($vendas_estado);
		$sql = substr($sql,0,strpos($sql, 'order'));
		$sql .= " order by ".$ordem." ".$crescente; 
		$sql .= " limit ".$max; 
		$sql .= " offset ".$inicial;
		$vendas_estado = SQLexecuteQuery($sql);
		
		//echo "sql: $sql<br>";
		$bg_col = $bg_col_01;
		$n_dias = pg_num_rows($vendas_estado);

		$colunas[] = LANG_GAME;
		$colunas[] = LANG_ITEM;
		$colunas[] = LANG_NUMBER_SALES;
		$colunas[] = LANG_VALUE_SALES;
		//$colunas[] = LANG_VALUE;		
		
		if($vendas_estado) {
			while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
				$nome_jogo = (strcmp($vendas_estado_row['ve_jogo'],"OG")==0)?"OnGame":((strcmp($vendas_estado_row['ve_jogo'],"MU")==0)?"Mu Online":((strcmp($vendas_estado_row['ve_jogo'],"HB")==0)?"Habbo Hotel":("???")));
				
				$jogo[] 		= $nome_jogo;
				$item[] 		= $vendas_estado_row['ve_valor'].".00";
				$nvenda[] 		= $vendas_estado_row['n'];
				$valorvendas[] 	= number_format(($vendas_estado_row['vendas']), 2, ',', '.');
				//$percvalor[] 	= number_format(100*($vendas_estado_row['vendas'])/(($total_vendas==0)?1:$total_vendas), 2, ',', '.');
				
				$previous_value = $vendas_estado_row['vendas'];
			}
			$retorno =  $grid->gera_grid_jogo($colunas,$jogo,$item,$nvenda,$valorvendas,$percvalor,$crescente_depois,LANG_STATISTICS_FOR_GAME,$ordem);
			require_once('grid.php');		
		} else {
			echo "<font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font>";
		}
	}
	// fim

	//por Mês
		if($abanome == LANG_STATISTICS_FOR_MONTH) {
				$sql = get_sql_query("P", "por_mes", addWhereClause($extra_where, $where_opr_2), $smode);
				//echo "sql: $sql<br>";
		
				$vendas_estado = SQLexecuteQuery($sql);
				//Adicionado por Wagner
				$total_table = pg_num_rows($vendas_estado);
				$sql = substr($sql,0,strpos($sql, 'order'));
				$sql .= " order by ".$ordem." ".$crescente; 
				$sql .= " limit ".$max; 
				$sql .= " offset ".$inicial;
				$vendas_estado = SQLexecuteQuery($sql);
				

				$bg_col = $bg_col_01;
				$n_dias = pg_num_rows($vendas_estado);
				
				$colunas[] = LANG_MONTH;
				$colunas[] = LANG_NUMBER_SALES;
				$colunas[] = LANG_VALUE_SALES;
				//$colunas[] = LANG_VALUE;
				
				if($vendas_estado) {
					while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
						$mes[] = mes_do_ano($vendas_estado_row['mes']);
						$nvenda[] = $vendas_estado_row['n'];
						$valorvendas[] = number_format(($vendas_estado_row['vendas']), 2, ',', '.');
						//$percvalor[] = number_format(100*($vendas_estado_row['vendas'])/(($total_vendas==0)?1:$total_vendas), 2, ',', '.');
							
						$previous_value = $vendas_estado_row['vendas'];
					}
					$retorno =  $grid->gera_grid_mes($colunas,$mes,$nvenda,$valorvendas,$percvalor,$crescente_depois,LANG_STATISTICS_FOR_MONTH,$ordem);
					require_once('grid.php');
				} else {
					echo "<font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font>";
				}
		}
	// fim
	
	// Publisher
	if($abanome == 'Publisher') {
		// Por Publisher
		$sql = get_sql_query("P", "por_publisher", addWhereClause($extra_where, $where_opr_2), $smode);
		
		$vendas_estado = SQLexecuteQuery($sql);
		//Adicionado por Wagner
		$total_table = pg_num_rows($vendas_estado);
		$sql = substr($sql,0,strpos($sql, 'order'));
		$sql .= " order by ".$ordem." ".$crescente; 
		$sql .= " limit ".$max; 
		$sql .= " offset ".$inicial;
		$vendas_estado = SQLexecuteQuery($sql);
		
		//echo "sql: $sql<br>";
		$bg_col = $bg_col_01;
		$n_dias = pg_num_rows($vendas_estado);
	
		$colunas[] = LANG_GAME;
		$colunas[] = LANG_NUMBER_SALES;
		$colunas[] = LANG_VALUE_SALES;
		//$colunas[] = LANG_VALUE;		
		
		if($vendas_estado) {
			while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
				$nome_jogo = (strcmp($vendas_estado_row['ve_jogo'],"OG")==0)?"OnGame":((strcmp($vendas_estado_row['ve_jogo'],"MU")==0)?"Mu Online":((strcmp($vendas_estado_row['ve_jogo'],"HB")==0)?"Habbo Hotel":("???")));
				
				$jogo[] 		= $nome_jogo."(".$vendas_estado_row['ve_jogo'].")";
				$nvenda[] 		= $vendas_estado_row['n'];
				$valorvendas[] 	= number_format(($vendas_estado_row['vendas']), 2, ',', '.');
				//$percvalor[] 	= number_format(100*($vendas_estado_row['vendas'])/(($total_vendas==0)?1:$total_vendas), 2, ',', '.');
				
				$previous_value = $vendas_estado_row['vendas'];
			}
			$retorno =  $grid->gera_grid_publisher($colunas,$jogo,$nvenda,$valorvendas,$percvalor,$crescente_depois,'Publisher',$ordem);
			require_once('grid.php');
		} else {
			echo "<font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font>";
		}
	}
	// Fim
//echo "LANG_STATISTICS_FOR_PUBLISHER_THIS_MONTH_1:".LANG_STATISTICS_FOR_PUBLISHER_THIS_MONTH_1"<br>";
	
	// Por Publisher neste mes
	if($abanome == LANG_STATISTICS_FOR_PUBLISHER_THIS_MONTH_1) {
		// Por Publisher ".LANG_STATISTICS_THIS_MONTH."
		$thismonth = mktime(0, 0, 0, date("m"), 1, date("Y"));
		$extra_where = " (ve_data_inclusao>='".date("Y-m-d H:i:s", $thismonth)."') ";
	//echo "extra_where: '$extra_where'<br>where_opr_2: '$where_opr_2'<br>";
		$sql = get_sql_query("P", "por_publisher", addWhereClause($extra_where, $where_opr_2), $smode);
		
	//echo "sql: $sql<br>";
		$vendas_estado = SQLexecuteQuery($sql);
		//Adicionado por Wagner
		$total_table = pg_num_rows($vendas_estado);
		$sql = substr($sql,0,strpos($sql, 'order'));
		$sql .= " order by ".$ordem." ".$crescente; 
		$sql .= " limit ".$max; 
		$sql .= " offset ".$inicial;
		$vendas_estado = SQLexecuteQuery($sql);
		
		$extra_where = "";
		$bg_col = $bg_col_01;
		$n_dias = pg_num_rows($vendas_estado);
		
		$colunas[] = LANG_GAME;
		$colunas[] = LANG_NUMBER_SALES;
		$colunas[] = LANG_VALUE_SALES;
	
		if($vendas_estado) {
			$valtmp = 0;
			$ntmp = 0;
			while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
				
				$nome_jogo = (strcmp($vendas_estado_row['ve_jogo'],"OG")==0)?"OnGame":((strcmp($vendas_estado_row['ve_jogo'],"MU")==0)?"Mu Online":((strcmp($vendas_estado_row['ve_jogo'],"HB")==0)?"Habbo Hotel":("???")));
				
				$jogo[] 		= $nome_jogo." (".$vendas_estado_row['ve_jogo'].")";
				$nvenda[] 		= $vendas_estado_row['n'];
				$valorvendas[] 	= number_format(($vendas_estado_row['vendas']), 2, ',', '.');
				
				$previous_value = $vendas_estado_row['vendas'];
				$valtmp += $vendas_estado_row['vendas'];
				$ntmp += $vendas_estado_row['n'];
			}
			$retorno =  $grid->gera_grid_publisher_mes($colunas,$jogo,$nvenda,$valorvendas,$crescente_depois,LANG_STATISTICS_FOR_PUBLISHER_THIS_MONTH_1,$ordem);
			require_once('grid.php');
					
			echo "<br><table><tr bgcolor='#FFFFCC'><td align='center'><b>".LANG_REPORTS_TOTAL."</b></td><td align='center'><b>".$ntmp."</b></td><td align='center'><b>".number_format(($valtmp), 2, ',', '.')."</b></td></tr></table>";
		} else {
			echo "<font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font>";
		}
	}
	// Fim
	
	// Tipo de Estabelecimento
	if($abanome == LANG_STATISTICS_FOR_TYPE) {
		if($_SESSION["tipo_acesso_pub"]!='PU') {
			// ".LANG_STATISTICS_FOR_ESTABLISHMENT_TYPE."
			$sql = get_sql_query("P", "por_tipo_de_estabelecimento", addWhereClause($extra_where, $where_opr_2), $smode);
			
			$vendas_estado = SQLexecuteQuery($sql);
			//Adicionado por Wagner
			$total_table = pg_num_rows($vendas_estado);
			$sql = substr($sql,0,strpos($sql, 'order'));
			$sql .= " order by ".$ordem." ".$crescente; 
			$sql .= " limit ".$max; 
			$sql .= " offset ".$inicial;
			$vendas_estado = SQLexecuteQuery($sql);

			$bg_col = $bg_col_01;
			$previous_value = -1;
	
			$colunas[] = LANG_ESTABLISHMENT_TYPE;
			$colunas[] = LANG_NUMBER_SALES;
			$colunas[] = LANG_VALUE_SALES;
			//$colunas[] = LANG_VALUE;			
	
			if($vendas_estado) {
				while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
					
					$estabe[] 	= $vendas_estado_row['ve_estabtipo'];
					$nvenda[] 		= $vendas_estado_row['n'];
					$valorvendas[] 	= number_format(($vendas_estado_row['vendas']), 2, ',', '.');
					//$percvalor[] 	= number_format(100*($vendas_estado_row['vendas'])/(($total_vendas==0)?1:$total_vendas), 2, ',', '.');
					
					$previous_value = $vendas_estado_row['vendas'];
				}
			$retorno =  $grid->gera_grid_tipo_estabelecimento($colunas,$estabe,$nvenda,$valorvendas,$percvalor,$crescente_depois,LANG_STATISTICS_FOR_TYPE,$ordem);
			require_once('grid.php');			
			} else {
				echo "<font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font>";
			}
		}
	}
	// Fim
//echo "LANG_STATISTICS_TOTALS:".LANG_STATISTICS_TOTALS"<br>";

	// Totais
	if($abanome == LANG_STATISTICS_TOTALS) {	
		if($_SESSION["tipo_acesso_pub"]!='PU') {	
			echo "<tr bgcolor='#CCFFCC'><td align='right' colspan='2'><a name='#Totais'></a><b>".LANG_STATISTICS_TOTAL.":&nbsp;</b></td><td align='center'><b>".$n_vendas."</b></td><td align='center'><b>".number_format(($total_vendas), 2, ',', '.')."</b> <br><b>";
			if(strlen($dd_operadora)==0) {
				echo "(EPP: ".number_format(($total_vendas*0.04), 2, ',', '.').")</b>";
			}
			echo "</td><td>&nbsp;</td></tr>";
			echo "<tr><td align='center' colspan='5'><b>".(LANG_STATISTICS_MEDIUM)."</b> ".LANG_STATISTICS_IN." ".$n_dias." ".LANG_DAYS.": R\$".number_format(($total_vendas/(($n_dias>0)?$n_dias:1)), 2, ',', '.')."/".LANG_DAYS."&nbsp;<br>";
			if(strlen($dd_operadora)==0) {
				echo "(EPP: R\$".number_format(($total_vendas*0.04/(($n_dias>0)?$n_dias:1)), 2, ',', '.')."/".LANG_DAYS.")<br>";
				echo (LANG_STATISTICS_PROJECTION_EPP).". $days_in_month ".LANG_DAYS.": R\$".number_format(($days_in_month*$total_vendas*0.04/(($n_dias>0)?$n_dias:1)), 2, ',', '.');
			} else {
				echo (LANG_STATISTICS_PROJECTION)." $days_in_month ".LANG_DAYS.": R\$".number_format(($days_in_month*$total_vendas/(($n_dias>0)?$n_dias:1)), 2, ',', '.');
			}
			echo "&nbsp;</td></tr>";
			echo "</table><br>";	
		}
	}
	// Fim	
//echo "LANG_TOTAL_ESTABLISHMENT:".LANG_TOTAL_ESTABLISHMENT."<br>";

	// Total por Estabelecimento
		if($abanome == LANG_TOTAL_ESTABLISHMENT) {
			if($_SESSION["tipo_acesso_pub"]!='PU') {
			// ".LANG_STATISTICS_FOR_ESTABLISHMENT."
			$sql = get_sql_query("P", "por_estabelecimento_barra", addWhereClause($extra_where, $where_opr_2), $smode);
			//echo "sql: $sql<br>";
			$vendas_estado = SQLexecuteQuery($sql);
			//Adicionado por Wagner
			$total_table = pg_num_rows($vendas_estado);
			$sql = substr($sql,0,strpos($sql, 'order'));
			$sql .= " order by ".$ordem." ".$crescente; 
			$sql .= " limit ".$max; 
			$sql .= " offset ".$inicial;
			$vendas_estado = SQLexecuteQuery($sql);
			
			//echo "pg_num_rows(vendas_estado): ".pg_num_rows($vendas_estado)."<br>";
			$previous_value = -1;
			$bg_col = $bg_col_01;
			
			$colunas[] = LANG_ESTABLISHMENT;
			$colunas[] = LANG_ESTABLISHMENT_TYPE;
			$colunas[] = LANG_START_DATE;
			$colunas[] = LANG_LAST_DATE;
			$colunas[] = LANG_ABANDON;			
			$colunas[] = LANG_FIRST_LAST;
			$colunas[] = LANG_CITY;
			$colunas[] = LANG_UF;
			$colunas[] = LANG_NUMBER_SALES;
			$colunas[] = LANG_VALUE_SALES;
			//$colunas[] = LANG_VALUE;
			//echo "<pre>";
			//print_r($colunas);
			//echo "</pre>";
		
			if($vendas_estado) {
				$i = 0;
				$a_vendas_ultimo_mes = array();
				while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
					$bg_col = ($previous_value!=$vendas_estado_row['vendas'])?(($bg_col==$bg_col_01)?$bg_col_02:$bg_col_01):$bg_col;
					$a_vendas_ultimo_mes[$i++] = $vendas_estado_row;
					
					$estabe[] = $vendas_estado_row['ve_estabelecimento'];
					$tipo[] = $vendas_estado_row['ve_estabtipo'];
					$dtini[]		= date('Y-m-d',strtotime($vendas_estado_row['primeira_venda']));
					$dtfim[]		= date('Y-m-d',strtotime($vendas_estado_row['ultima_venda']));
					$aband[]		= intval(($today1 - strtotime($vendas_estado_row['ultima_venda']))/86400+1);					
					$ultvenda[] = get_delay_alert_live($vendas_estado_row['primeira_venda'], $vendas_estado_row['ultima_venda']);
					$cidade[] = $vendas_estado_row['ve_cidade'];
					$uf[] = $vendas_estado_row['ve_estado'];
					$nvenda[] = $vendas_estado_row['n'];
					$valorvendas[] = number_format(($vendas_estado_row['vendas']), 2, ',', '.');
					//$percvalor[] = number_format(100*($vendas_estado_row['vendas'])/(($total_vendas==0)?1:$total_vendas), 2, ',', '.');
					
					$previous_value = $vendas_estado_row['vendas'];
				}
				$retorno =  $grid->gera_grid_estabelecimento($colunas,$estabe,$tipo,$dtini,$dtfim,$aband,$ultvenda,$cidade,$uf,$nvenda,$valorvendas,$percvalor,$crescente_depois,LANG_TOTAL_ESTABLISHMENT,$ordem);
				require_once('grid.php');					
			} else {
				echo "<br><font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font><br>";
			}
		}
	}
	// Fim


	// Total por Totais-de-Vendas-Mês
		if($_SESSION["tipo_acesso_pub"]!='PU') {
			$previousmonth = mktime(0, 0, 0, date("m"), date("d")-$days_in_month, date("Y"));
			$extra_where = " (ve_data_inclusao>='".date("Y-m-d H:i:s", $previousmonth)."') ";
			$sql = get_sql_query("P", "totais_de_vendas", addWhereClause($extra_where, $where_opr_2), $smode);
	//	echo "sql: $sql<br>";
			$total_vendas = 0;
			$n_vendas = 0;
			$vendas_estado = SQLexecuteQuery($sql);

			if($vendas_estado) {
				while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
					$total_vendas = $vendas_estado_row['vendas'];
					$n_vendas = $vendas_estado_row['n'];
				}
			} else {
				echo "<table><tr bgcolor='".$bg_col."'><td align='center' colspan='5'><font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font></td></tr></table>";
			}			
		}
	// Fim

	// Total por Semana
		if($_SESSION["tipo_acesso_pub"]!='PU') {
			$previousweek = mktime(0, 0, 0, date("m"), date("d")-6, date("Y"));
			$extra_where = " (ve_data_inclusao>='".date("Y-m-d H:i:s", $previousweek)."') ";
			$sql = get_sql_query("P", "totais_de_vendas", addWhereClause($extra_where, $where_opr_2), $smode);
			$total_vendas = 0;
			$n_vendas = 0;

			$vendas_estado = SQLexecuteQuery($sql);
			
			if($vendas_estado) {
				while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
					$total_vendas = $vendas_estado_row['vendas'];
					$n_vendas = $vendas_estado_row['n'];
				}
			} else {
				echo "<tr bgcolor='".$bg_col."'><td align='center' colspan='5'><font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font></td></tr>";
			}
		}
	// Fim
//echo "LANG_TOTAL_ESTABLISHMENT_MONTH:".LANG_TOTAL_ESTABLISHMENT_MONTH."<br>";
	
	// Total-Estabelecimento-Mês
		if($abanome == LANG_TOTAL_ESTABLISHMENT_MONTH) {
			if($_SESSION["tipo_acesso_pub"]!='PU') {
				// ".LANG_STATISTICS_FOR_ESTABLISHMENT." último mês
				$sql = get_sql_query("P", "por_estabelecimento", addWhereClause($extra_where, $where_opr_2), $smode);
				
				//echo "sql: $sql<br>";
				$vendas_estado = SQLexecuteQuery($sql);
				//Adicionado por Wagner
				$total_table = pg_num_rows($vendas_estado);
				$sql = substr($sql,0,strpos($sql, 'order'));
				$sql .= " order by ".$ordem." ".$crescente; 
				$sql .= " limit ".$max; 
				$sql .= " offset ".$inicial;
				//echo "sql: $sql<br>";
				$vendas_estado = SQLexecuteQuery($sql);
				
				$previous_value = -1;
				$bg_col = $bg_col_01;
				$total_vendas_mes = 0;
				$n_vendas_mes = 0;
				
				$colunas[] = LANG_ESTABLISHMENT;
				$colunas[] = 'Pos&nbsp;&nbsp;&nbsp;&nbsp;';
				$colunas[] = LANG_CITY;
				$colunas[] = LANG_UF;
				$colunas[] = LANG_NUMBER_SALES;
				$colunas[] = LANG_VALUE_SALES;
				//$colunas[] = LANG_VALUE;
		
				if($vendas_estado) {
					$iorder = 0;
					$i = 0;
					$a_vendas_ultimo_mes = array();
					$today1 = strtotime ('now');
				
					while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
						$a_vendas_ultimo_mes[$i++] = $vendas_estado_row;
						$bg_col = ($previous_value!=$vendas_estado_row['vendas'])?(($bg_col==$bg_col_01)?$bg_col_02:$bg_col_01):$bg_col;
						$ipos = getPositionInArray("P", $vendas_estado_row['ve_estabelecimento'], $iorder++, $a_vendas_ultimo_mes);
						
						//echo "IORDER: ".$iorder." - Venda Ultimo Mes: ".$a_vendas_ultimo_mes." - Vencimento: ".$vendas_estado_row['ve_estabelecimento']." - IPOS: $ipos<br>";
						
						$sarrow_alt = (($ipos>0)?"Up":(($ipos==0)?"Equal":"Down"));
						$sarrow = "<img src='/sys/imagens/".(($ipos>0)?"arrow_up_2.gif":(($ipos==0)?"equal.gif":"arrow_down_2.gif"))."' width='12' height='12' border='0' alt='$sarrow_alt'>";
		
						$estabe[] = $vendas_estado_row['ve_estabelecimento'];
						$pos[] = $sarrow.(($ipos>0)?"+":(($ipos==0)?"&nbsp;":"")).$ipos;
						$cidade[] = $vendas_estado_row['ve_cidade'];
						$uf[] = $vendas_estado_row['ve_estado'];
						$nvenda[] = $vendas_estado_row['n'];
						$valorvendas[] = number_format(($vendas_estado_row['vendas']), 2, ',', '.');
						//$percvalor[] = number_format(100*($vendas_estado_row['vendas'])/(($total_vendas==0)?1:$total_vendas), 2, ',', '.');		
						

						
						$previous_value = $vendas_estado_row['vendas'];
						$total_vendas_mes += $vendas_estado_row['vendas'];
						$n_vendas_mes += $vendas_estado_row['n'];
					}
					$retorno =  $grid->gera_grid_estabelecimento_mes($colunas,$estabe,$pos,$cidade,$uf,$nvenda,$valorvendas,$percvalor,$crescente_depois,LANG_TOTAL_ESTABLISHMENT_MONTH,$ordem);
					require_once('grid.php');						
				} else {
					echo "<br><font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font><br>";
				}
				echo "<table><tr bgcolor='#CCFFCC'><td align='right' colspan='5'><b>".LANG_REPORTS_TOTAL."</b>&nbsp;</td><td align='center'>".$n_vendas_mes."</td><td align='center'>".number_format(($total_vendas_mes), 2, ',', '.')."</td><td align='center'>&nbsp;</td></tr>";
				echo "</table><br>";

			}
		}
	// Fim	

	// 
		if($abanome == 'Total-Estabelecimento-Mês') {
			if($_SESSION["tipo_acesso_pub"]!='PU') {
		
			}
		}
	// Fim


//echo "LANG_STATISTICS_FOR_LAST_WEEK:".LANG_STATISTICS_FOR_LAST_WEEK."<br>";
	
	// Ultima semana
	if($abanome == LANG_STATISTICS_FOR_LAST_WEEK) {
		if($_SESSION["tipo_acesso_pub"]!='PU') {
			// Totais de Vendas Semana
			$previousweek = mktime(0, 0, 0, date("m"), date("d")-6, date("Y"));
			$extra_where = " (ve_data_inclusao>='".date("Y-m-d H:i:s", $previousweek)."') ";
			$sql = get_sql_query("P", "totais_de_vendas", addWhereClause($extra_where, $where_opr_2), $smode);
			$total_vendas = 0;
			$n_vendas = 0;
			$vendas_estado = SQLexecuteQuery($sql);
			//Adicionado por Wagner
			$total_table = pg_num_rows($vendas_estado);
			//$sql = substr($sql,0,strpos($sql, 'order'));
			//$sql .= " order by ".$ordem." ".$crescente; 
			//$sql .= " limit ".$max; 
			//$sql .= " offset ".$inicial;
			$vendas_estado = SQLexecuteQuery($sql);
				
			if($vendas_estado) {
				while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
					$total_vendas = $vendas_estado_row['vendas'];
					$n_vendas = $vendas_estado_row['n'];
				}
			} else {
				echo "<font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font>";
			}	
			
			// ".LANG_STATISTICS_FOR_ESTABLISHMENT." última semana
			$sql = get_sql_query("P", "por_estabelecimento", addWhereClause($extra_where, $where_opr_2), $smode);
			
			//echo "sql: $sql<br>";
			$vendas_estado = SQLexecuteQuery($sql);
			//Adicionado por Wagner
			$total_table = pg_num_rows($vendas_estado);
			$sql = substr($sql,0,strpos($sql, 'order'));
			$sql .= " order by ".$ordem." ".$crescente; 
			$sql .= " limit ".$max; 
			$sql .= " offset ".$inicial;
			$vendas_estado = SQLexecuteQuery($sql);
				
			$previous_value = -1;
			$bg_col = $bg_col_01;
			$total_vendas_sem = 0;
			$n_vendas_sem = 0;
			
			
				$colunas[] = LANG_ESTABLISHMENT;
				//$colunas[] = 'Pos&nbsp;&nbsp;&nbsp;&nbsp;';
				$colunas[] = LANG_TYPE;
				$colunas[] = LANG_CITY;
				$colunas[] = LANG_UF;
				$colunas[] = LANG_NUMBER_SALES;
				$colunas[] = LANG_VALUE_SALES;
				//$colunas[] = LANG_VALUE;	
			
			if($vendas_estado) {
				$iorder = 0;
				while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
					$ipos = getPositionInArray("P", $vendas_estado_row['ve_estabelecimento'], $iorder++, $a_vendas_ultimo_mes);
					$sarrow_alt = (($ipos>0)?"Up":(($ipos==0)?"Equal":"Down"));
					$sarrow = "<img src='/sys/imagens/".(($ipos>0)?"arrow_up_2.gif":(($ipos==0)?"equal.gif":"arrow_down_2.gif"))."' width='12' height='12' border='0' alt='$sarrow_alt'>";
	
					
					$estabelecimento[] 	= $vendas_estado_row['ve_estabelecimento'];
					$pos[] 				= $sarrow.(($ipos>0)?"+":(($ipos==0)?"&nbsp;":"")).$ipos;
					$tipo[] 			= $vendas_estado_row['ve_estabtipo'];
					$cidade[]			= $vendas_estado_row['ve_cidade'];
					$estado[] 			= $vendas_estado_row['ve_estado'];
					$nvenda[]			= $vendas_estado_row['n'];
					$valvenda[] 		= number_format(($vendas_estado_row['vendas']), 2, ',', '.');
					//$valorperc[]		= number_format(100*($vendas_estado_row['vendas'])/(($total_vendas==0)?1:$total_vendas), 2, ',', '.');
					
					$previous_value = $vendas_estado_row['vendas'];
					$total_vendas_sem += $vendas_estado_row['vendas'];
					$n_vendas_sem += $vendas_estado_row['n'];
				}
				$retorno =  $grid->gera_grid_ultima_semana($colunas,$estabelecimento,$pos,$tipo,$cidade,$estado,$nvenda,$valvenda,$valorperc,$crescente_depois,LANG_STATISTICS_FOR_LAST_WEEK,$ordem);
				require_once('grid.php');
			} else {
				echo "<font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font>";
			}
			echo "<table><tr bgcolor='#CCFFCC'><td align='right' colspan='5'><b>".LANG_REPORTS_TOTAL."</b>&nbsp;</td><td align='center'>".$n_vendas_sem."</td><td align='center'>".number_format(($total_vendas_sem), 2, ',', '.')."</td><td align='center'>&nbsp;</td></tr>";
			echo "</table><br>";		
		}
	}
	// fim
//echo "LANG_STATISTICS_FOR_LAST_MONTH:".LANG_STATISTICS_FOR_LAST_MONTH."<br>";
	if($abanome == LANG_STATISTICS_FOR_LAST_MONTH) {
		if($_SESSION["tipo_acesso_pub"]!='PU') {
			// Ultimo mes
				// Totais de Vendas Mês
				$previousmonth = mktime(0, 0, 0, date("m"), date("d")-$days_in_month, date("Y"));
				$extra_where = " (ve_data_inclusao>='".date("Y-m-d H:i:s", $previousmonth)."') ";
				$sql = get_sql_query("P", "totais_de_vendas", addWhereClause($extra_where, $where_opr_2), $smode);
				//echo "sql: $sql<br>";
				$total_vendas = 0;
				$n_vendas = 0;
				
				$vendas_estado = SQLexecuteQuery($sql);
				//Adicionado por Wagner
				$total_table = pg_num_rows($vendas_estado);
				//$sql .= " order by ".$ordem." ".$crescente; 
				//$sql .= " limit ".$max; 
				//$sql .= " offset ".$inicial;
				//echo $sql;
				//$vendas_estado = SQLexecuteQuery($sql);
				
				if($vendas_estado) {
					while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
						$total_vendas = $vendas_estado_row['vendas'];
						$n_vendas = $vendas_estado_row['n'];
					}
				} else {
					echo "<font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font>";
				}	
				
				// ".LANG_STATISTICS_FOR_ESTABLISHMENT." último mês
				$sql = get_sql_query("P", "por_estabelecimento", addWhereClause($extra_where, $where_opr_2), $smode);
				
				//echo "sql: $sql<br>";
				$vendas_estado = SQLexecuteQuery($sql);
				//Adicionado por Wagner
				$total_table = pg_num_rows($vendas_estado);
				//echo $sql."<br>TOTAL: ".$total_table."<br>";
				$sql = substr($sql,0,strpos($sql, 'order'));
				$sql .= " order by ".$ordem." ".$crescente; 
				$sql .= " limit ".$max; 
				$sql .= " offset ".$inicial;
				$vendas_estado = SQLexecuteQuery($sql);
				
				$previous_value = -1;
				$bg_col = $bg_col_01;
				$total_vendas_mes = 0;
				$n_vendas_mes = 0;
				
				$colunas[] = LANG_ESTABLISHMENT;
				//$colunas[] = 'Pos';
				$colunas[] = LANG_TYPE;
				$colunas[] = LANG_CITY;
				$colunas[] = LANG_UF;
				$colunas[] = LANG_NUMBER_SALES;
				$colunas[] = LANG_VALUE_SALES;
				//$colunas[] = LANG_VALUE;	
						
				if($vendas_estado) {
					$iorder = 0;
					while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
						$bg_col = ($previous_value!=$vendas_estado_row['vendas'])?(($bg_col==$bg_col_01)?$bg_col_02:$bg_col_01):$bg_col;
						$ipos = getPositionInArray("P", $vendas_estado_row['ve_estabelecimento'], $iorder++, $a_vendas_ultimo_mes);
						$sarrow_alt = (($ipos>0)?"Up":(($ipos==0)?"Equal":"Down"));
						$sarrow = "<img src='/sys/imagens/".(($ipos>0)?"arrow_up_2.gif":(($ipos==0)?"equal.gif":"arrow_down_2.gif"))."' width='12' height='12' border='0' alt='$sarrow_alt'>";
		
						
						$estabelecimento[] 	= $vendas_estado_row['ve_estabelecimento'];
						$pos[] 				= $sarrow.(($ipos>0)?"+":(($ipos==0)?"&nbsp;":"")).$ipos;
						$tipo[] 			= $vendas_estado_row['ve_estabtipo'];
						$cidade[]			= $vendas_estado_row['ve_cidade'];
						$estado[] 			= $vendas_estado_row['ve_estado'];
						$nvenda[]			= $vendas_estado_row['n'];
						$valvenda[] 		= number_format(($vendas_estado_row['vendas']), 2, ',', '.');
						//$valorperc[]		= number_format(100*($vendas_estado_row['vendas'])/(($total_vendas==0)?1:$total_vendas), 2, ',', '.');
						
						$previous_value = $vendas_estado_row['vendas'];
						$total_vendas_mes += $vendas_estado_row['vendas'];
						$n_vendas_mes += $vendas_estado_row['n'];
					}
					//echo "colunas: ".$colunas."estabelecimento: ".$estabelecimento."pos: ".$pos."tipo: ".$tipo."cidade: ".$cidade."estado: ".$estado."nvenda: ".$nvenda."valvenda: ".$valvenda."valorperc: ".$valorperc."<br>";
					$retorno =  $grid->gera_grid_ultimo_mes($colunas,$estabelecimento,$pos,$tipo,$cidade,$estado,$nvenda,$valvenda,$valorperc,$crescente_depois,LANG_STATISTICS_FOR_LAST_MONTH,$ordem);
					require_once('grid.php');			
				} else {
					echo "<tr bgcolor='".$bg_col."'><td align='center' colspan='5'><font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font></td></tr>";
				}
				echo "<table><tr bgcolor='#CCFFCC'><td align='right' colspan='5'><b>".LANG_REPORTS_TOTAL."</b>&nbsp;</td><td align='center'>".$n_vendas_mes."</td><td align='center'>".number_format(($total_vendas_mes), 2, ',', '.')."</td><td align='center'>&nbsp;</td></tr>";		
		}
	}
	// Fim
	/*
	if(function_exists(paginacao_query)) 
	echo "EXISTE A FUNCAUN<br>";
	else echo"NAUN EXISTE A FUNCAUN<br>";
	echo "inicial:".$inicial."<br>";
	echo "total_table:".$total_table."<br>";
	echo "max:".$max."<br>";
	echo "img_anterior:".$img_anterior."<br>";
	echo "img_proxima:".$img_proxima."<br>";
	echo "default_add:".$default_add."<br>";
	echo "range:".$range."<br>";
	echo "range_qtde:".$range_qtde."<br>";
	echo "ncamp:".$ncamp."<br>";
	echo "varsel:".$varsel."<br>";
	*/
	if($max + $inicial > $total_table)
		$reg_ate = $total_table;
	else
		$reg_ate = $max + $inicial;
	paginacao_query($inicial, $total_table, $max, 20, $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel);
// fim
?>
<script type="text/javascript">
	$("#count-results-<?php echo $abanome;?>").html("<font class='texto'><?php echo LANG_INTEGRATION_SHOW_RESULTS;?> <strong><?php echo $inicial + 1 ?></strong> <?php echo LANG_INTEGRATION_UNTIL;?> <strong> <?php echo ($inicial + $max) ?> </strong> <?php echo LANG_INTEGRATION_BY;?> <strong><?php echo $total_table ?></strong></font> ");
</script>
<?php
}
?>
<br><br>
<table border='0' cellpadding='0' cellspacing='1' width='80%' bordercolor='#cccccc' style='border-collapse:collapse;'>	
	<tr align="center"> 
		<td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td>
		<td bgcolor="#FFFFFF" class="texto"><?php echo LANG_STATISTICS_SEARCH_MSG." ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')." ".LANG_STATISTICS_SEARCH_MSG_UNIT; ?></font></td><td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td>
	</tr>
</table>