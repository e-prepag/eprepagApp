<?php 
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
set_time_limit(3000);	
@session_start();
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


$dd_operadora = $_SESSION['dd_operadora'];
$dd_mode = $_SESSION['dd_mode'];

$grid=new grid();

$time_start_stats = getmicrotime();
	
	$bg_col_01 = "#FFFFFF";
	$bg_col_02 = "#EEEEEE";
	$bg_col = $bg_col_01;

	if($_SESSION["tipo_acesso_pub"]=='PU') {
		$dd_operadora = $_SESSION["opr_codigo_pub"];
		$dd_mode = "S";

		if(strlen($dd_operadora)>0) { 
			$where_operadora = " and vgm_opr_codigo='".$dd_operadora."'";
		} else {
			$where_operadora = "";
		}
	}

	if(!$dd_mode || ($dd_mode!='V')) {
		$dd_mode = "S";
	}
	$smode = $dd_mode;
	$where_mode_data = "vg.vg_data_inclusao";	// default
	if($smode=='S') $where_mode_data = "vg.vg_data_concilia";

	$dd_operadora_nome = "";
	if($dd_operadora) {
		$resopr_nome = pg_exec($connid, "select opr_nome from operadoras where (opr_status = '1') and (opr_codigo='".$dd_operadora."') order by opr_ordem");
		if($pgopr_nome = pg_fetch_array ($resopr_nome)) { 
			$dd_operadora_nome = $pgopr_nome['opr_nome'];
		} 
		$where_operadora = " vgm_opr_codigo='".$dd_operadora."'";
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

//	print_r($_SESSION);
//	echo '<hr>';
	?>
    <form name="form1" method="get" action="abas.php">
        <?php if($_SESSION["tipo_acesso_pub"]=='PU') { ?>
            <span style="font-weight: bold"><?php echo LANG_STATISTICS_OUT; ?></span>
            <input type="hidden" name="script" id="script" value="<?php echo $script; ?>" />
            <input type="hidden" name="dd_mode" id="dd_mode" value="<?php echo $dd_mode; ?>">
        <?php } else { ?>	
        <?php echo LANG_STATISTICS_REPORT_TYPE; ?>:
        <input type="hidden" name="script" id="script" value="<?php echo $script; ?>" />
        <select name="dd_mode" id="dd_mode" class="combo_normal" >
          <option value="S" <?php if($dd_mode=="S") echo "selected" ?>><?php echo LANG_STATISTICS_OUT; ?></option>
          <option value="V" <?php if($dd_mode=="V") echo "selected" ?>><?php echo LANG_STATISTICS_SALES; ?></option>
        </select>
        <?php
          } 
        ?>
        </font>
		<?php echo LANG_STATISTICS_OPERATOR; ?>: 
        <?php
            if($_SESSION["tipo_acesso_pub"]=='PU') {
        ?>
            <span style="font-weight: bold"><?=$_SESSION["opr_nome"]?></span>
            <input type="hidden" name="dd_operadora" id="dd_operadora" value="<?php echo $dd_operadora; ?>">
            <input type="hidden" name="script" id="script" value="<?php echo $script; ?>" />
        <?php
          } else {
        ?>	
        <input type="hidden" name="script" id="script" value="<?php echo $script; ?>" />
        <select name="dd_operadora" id="dd_operadora" class="combo_normal" >
          <option value=""><?php echo LANG_POS_ALL_OPERATOR; ?></option>
          <?php
                while ($pgopr = pg_fetch_array ($resopr)) { 
            ?>
          <option value="<?php echo $pgopr['opr_codigo'] ?>" <?php if($pgopr['opr_codigo'] == $dd_operadora) echo "selected" ?>><?php echo $pgopr['opr_nome'] ?></option>
              <?php 
                } ?>
        </select>
        <?php
          } 
        ?>
        <input type="submit" name="button" id="button" value="<?php echo LANG_SELECT;?>" />
        </font>
		
    </form>
        <!--div id="pager-<?php echo $abanome;?>" style="top: 0px; position: static;">
            <form>
                <img src="grid/tablesorter.com/addons/pager/icons/first.png" class="first"/>
                <img src="grid/tablesorter.com/addons/pager/icons/prev.png" class="prev"/>
                <input type="text" class="pagedisplay"/>
                <img src="grid/tablesorter.com/addons/pager/icons/next.png" class="next"/>
                <img src="grid/tablesorter.com/addons/pager/icons/last.png" class="last"/>
                <select class="pagesize">
                    <option selected="selected" value="10">10</option>
                    <option  value="30">30</option>
                </select>
            </form>    
        </div-->       
    
    <br>
	<div id="count-results-<?php echo $abanome;?>" name="count-results-<?php echo $abanome;?>" align="left">
	</div>
    <?php

if($_SESSION['button'] == LANG_SELECT) {
	$imonth = date("n"); // or any value from 1-12
	$iyear	= date("Y"); // or any value >= 1
	$days_in_month = date("t",mktime(0,0,0,$imonth,1,$iyear));
//	echo "days_in_month: $days_in_month<br>";

	// Totais de Cadastros 
	$n_cadastros = get_ncadastros("E", addWhereClause($extra_where, $where_operadora), $smode);

	// Totais de vendas
	$sql = get_sql_query("E", "totais_de_vendas", addWhereClause($extra_where, $where_operadora), $smode);
	$total_vendas = 0;
	$n_vendas_total = 0;
	$vendas_estado = SQLexecuteQuery($sql);
	if($vendas_estado) {
		while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
			$total_vendas = $vendas_estado_row['vendas'];
			$n_vendas_total = $vendas_estado_row['n'];
		}
	} else {
		echo "<tr bgcolor='".$bg_col."'><td align='center' colspan='5'><font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font></td></tr>";
	}
//echo "$total_vendas em $n_vendas_total vendas<br>";

	// Totais de vendas ".LANG_STATISTICS_THIS_MONTH."
	$thismonth = mktime(0, 0, 0, date("m"), 1, date("Y")); 
	$extra_where_1 = " ($where_mode_data>='".date("Y-m-d H:i:s", $thismonth)."') ";
	$sql = get_sql_query("E", "totais_de_vendas", addWhereClause($extra_where_1, $where_operadora), $smode);
	$total_vendas_neste_mes = 0;
	$n_vendas_total_neste_mes = 0;
	$vendas_estado = SQLexecuteQuery($sql);
	if($vendas_estado) {
		while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
			$total_vendas_neste_mes = $vendas_estado_row['vendas'];
			$n_vendas_total_neste_mes = $vendas_estado_row['n'];
		}
	} else {
		echo "<tr bgcolor='".$bg_col."'><td align='center' colspan='5'><font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font></td></tr>";
	}
//echo "$total_vendas_neste_mes em $n_vendas_total vendas_neste_mes<br>";

	// Datas Limites no BD
	$sql = get_sql_query("E", "datas_limites_no_bd", addWhereClause($extra_where, $where_operadora), $smode);
	$data_min = date("Y-m-d");
	$data_max = date("Y-m-d");
	$vendas_estado = SQLexecuteQuery($sql);
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
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if($abanome == LANG_STATISTICS_FOR_MONTH) {
	// <?php echo LANG_STATISTICS_FOR_MONTH;
	$sql = get_sql_query("E", "por_mes", addWhereClause($extra_where, $where_operadora), $smode);
//echo "sql: $sql<br>";
	$vendas_estado = SQLexecuteQuery($sql);
	$bg_col = $bg_col_01;
	$n_dias = pg_num_rows($vendas_estado);

	//Adicionado por Wagner
	$total_table = pg_num_rows($vendas_estado);
	//echo $sql."<br>TOTAL: ".$total_table."<br>";
	$sql = substr($sql,0,strpos($sql, 'order'));
	$sql .= " order by ".$ordem." ".$crescente; 
	$sql .= " limit ".$max; 
	$sql .= " offset ".$inicial;
	//echo "$sql<br>";
	$vendas_estado = SQLexecuteQuery($sql);
	
	$colunas[] = LANG_MONTH;
	$colunas[] = LANG_NUMBER_SALES;
	$colunas[] = LANG_VALUE_SALES;
	//$colunas[] = LANG_VALUE;	
	
	if($vendas_estado) {
		while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
			$mes[] 			= mes_do_ano($vendas_estado_row['mes']);
			$nvenda[] 		= $vendas_estado_row['n'];
			$valorvendas[] 	= number_format(($vendas_estado_row['vendas']), 2, ',', '.');
			//$percvalor[] 	= number_format(100*($vendas_estado_row['vendas'])/(($total_vendas==0)?1:$total_vendas), 2, ',', '.');
			
			$previous_value = $vendas_estado_row['vendas'];
		}
		$retorno =  $grid->gera_grid_mes($colunas,$mes,$nvenda,$valorvendas,$percvalor,$crescente_depois,LANG_STATISTICS_FOR_MONTH,$ordem);
		require_once('grid.php');		
	} else {
		echo "<font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font>";
	}
}	

if($abanome == LANG_STATISTICS_FOR_WEEK_DAY) {
	// ".LANG_STATISTICS_FOR_WEEK_DAY."
	$sql = get_sql_query("E", "por_dia_da_semana", addWhereClause($extra_where, $where_operadora), $smode);
//echo "sql: $sql<br>";
	$vendas_estado = SQLexecuteQuery($sql);
	$bg_col = $bg_col_01;
	$n_dias = pg_num_rows($vendas_estado);
	
	//Adicionado por Wagner
	$total_table = pg_num_rows($vendas_estado);
	//echo $sql."<br>TOTAL: ".$total_table."<br>";
	$sql = substr($sql,0,strpos($sql, 'order'));
	$sql .= " order by ".$ordem." ".$crescente; 
	$sql .= " limit ".$max; 
	$sql .= " offset ".$inicial;
	//echo "$sql<br>";
	$vendas_estado = SQLexecuteQuery($sql);

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

if($abanome == LANG_STATISTICS_FOR_DAY) {
	// Por dia
	$sql = get_sql_query("E", "por_dia", addWhereClause($extra_where, $where_operadora), $smode);
//echo "sql: $sql<br>";
	$vendas_estado = SQLexecuteQuery($sql);
	$bg_col = $bg_col_01;
	$n_dias = pg_num_rows($vendas_estado);
	
	//Adicionado por Wagner
	$total_table = pg_num_rows($vendas_estado);
	//echo $sql."<br>TOTAL: ".$total_table."<br>";
	$sql = substr($sql,0,strpos($sql, 'order'));
	$sql .= " order by ".$ordem." ".$crescente; 
	$sql .= " limit ".$max; 
	$sql .= " offset ".$inicial;
	//echo "$sql<br>";
	$vendas_estado = SQLexecuteQuery($sql);

	$colunas[] = LANG_DAY;
	$colunas[] = LANG_WEEK;
	$colunas[] = LANG_NUMBER_SALES;
	$colunas[] = LANG_VALUE_SALES;
	//$colunas[] = LANG_VALUE;	

	if($vendas_estado) {
		while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
			$dia[] 	= $vendas_estado_row['data'];
			$dias[] = get_day_of_week($vendas_estado_row['data']);
			$nven[] = $vendas_estado_row['n'];
			$vven[] = number_format(($vendas_estado_row['vendas']), 2, ',', '.');
			//$vper[] = number_format(100*($vendas_estado_row['vendas'])/(($total_vendas==0)?1:$total_vendas), 2, ',', '.');
			
			$previous_value = $vendas_estado_row['vendas'];
		}
		$retorno =  $grid->gera_grid_dia($colunas,$dia,$dias,$nven,$vven,$vper,$crescente_depois,LANG_STATISTICS_FOR_DAY,$ordem);
		require_once('grid.php');			
	} else {
		echo "<br><font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font><br>";
	}
	


	echo "<br><br><table><tr bgcolor='#CCFFCC'><td align='right' colspan='2'><a name='#Totais'></a><b>Total</b></td><td align='center'><b>".$n_vendas_total."</b></td><td align='center'><b>".number_format(($total_vendas), 2, ',', '.')."</b> <br><b>";
	if($_SESSION["tipo_acesso_pub"]=='PU') {
		echo "(EPP: ".number_format(($total_vendas*0.04), 2, ',', '.').")</b>";
	}
	echo "</td><td>&nbsp;</td></tr>";
	echo "<tr><td align='center' colspan='5'><b>".LANG_STATISTICS_MEDIUM."</b> em ".$n_dias." ".LANG_DAYS.": R\$".number_format(($total_vendas/(($n_dias>0)?$n_dias:1)), 2, ',', '.')."/".LANG_DAY."&nbsp;<br>";
	if($_SESSION["tipo_acesso_pub"]=='PU') {
		echo "(EPP: R\$".number_format(($total_vendas*0.04/(($n_dias>0)?$n_dias:1)), 2, ',', '.')."/".LANG_DAY.")<br>";
		echo LANG_STATISTICS_PROJECTION_EPP." $days_in_month dias: R\$".number_format(($days_in_month*$total_vendas*0.04/(($n_dias>0)?$n_dias:1)), 2, ',', '.');
	} else {
		echo LANG_STATISTICS_PROJECTION." $days_in_month ".LANG_DAYS.": R\$".number_format(($days_in_month*$total_vendas/(($n_dias>0)?$n_dias:1)), 2, ',', '.');
	}
	echo "&nbsp;</td></tr>";
	echo "</table><br>";
	
}

if($abanome == LANG_STATISTICS_FOR_GAME) {
	// ".LANG_STATISTICS_FOR_GAME."
	$sql = get_sql_query("E", "por_publisher", addWhereClause($extra_where, $where_operadora), $smode);
//echo "sql: ".$sql."<br>";
	$vendas_estado = SQLexecuteQuery($sql);
	$bg_col = $bg_col_01;
	$n_dias = pg_num_rows($vendas_estado);

	//Adicionado por Wagner
	$total_table = pg_num_rows($vendas_estado);
	//echo $sql."<br>TOTAL: ".$total_table."<br>";
	$sql = substr($sql,0,strpos($sql, 'order'));
	$sql .= " order by ".$ordem." ".$crescente; 
	$sql .= " limit ".$max; 
	$sql .= " offset ".$inicial;
	//echo "$sql<br>";
	$vendas_estado = SQLexecuteQuery($sql);

	$colunas[] = LANG_GAME;
	$colunas[] = LANG_NUMBER_SALES;
	$colunas[] = LANG_VALUE_SALES;
	//$colunas[] = LANG_VALUE;		

	
	if($vendas_estado) {
		while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
			$nome_jogo = $vendas_estado_row['ve_jogo'];
			
			$jogo[] 		= $nome_jogo;
			$nvenda[] 		= $vendas_estado_row['n'];
			$valorvendas[] 	= number_format(($vendas_estado_row['vendas']), 2, ',', '.');
			//$percvalor[] 	= number_format(100*($vendas_estado_row['vendas'])/(($total_vendas==0)?1:$total_vendas), 2, ',', '.');
			

			$previous_value = $vendas_estado_row['vendas'];
		}
		$retorno =  $grid->gera_grid_jogo_MONEY_STATS($colunas,$jogo,$nvenda,$valorvendas,$percvalor,$crescente_depois,LANG_STATISTICS_FOR_GAME,$ordem);
		require_once('grid.php');			
	} else {
		echo "<font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font>";
	}

}

if($abanome == LANG_STATISTICS_FOR_GAME_THIS_MONTH) {
	// ".LANG_STATISTICS_FOR_GAME." ".LANG_STATISTICS_THIS_MONTH."
	$thismonth = mktime(0, 0, 0, date("m"), 1, date("Y")); 
	$extra_where = " ($where_mode_data>='".date("Y-m-d H:i:s", $thismonth)."') ";
	$sql = get_sql_query("E", "por_publisher", addWhereClause($extra_where, $where_operadora), $smode);
//echo "sql: ".$sql."<br>";
	$vendas_estado = SQLexecuteQuery($sql);
	$extra_where = "";
	$bg_col = $bg_col_01;
	$n_dias = pg_num_rows($vendas_estado);

	//Adicionado por Wagner
	$total_table = pg_num_rows($vendas_estado);
	//echo $sql."<br>TOTAL: ".$total_table."<br>";
	$sql = substr($sql,0,strpos($sql, 'order'));
	$sql .= " order by ".$ordem." ".$crescente; 
	$sql .= " limit ".$max; 
	$sql .= " offset ".$inicial;
	//echo "$sql<br>";
	$vendas_estado = SQLexecuteQuery($sql);

	$colunas[] = LANG_GAME;
	$colunas[] = LANG_NUMBER_SALES;
	$colunas[] = LANG_VALUE_SALES;
	//$colunas[] = LANG_VALUE;	

	if($vendas_estado) {
		$valtmp = 0;
		$ntmp = 0;
		while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
			$bg_col = ($bg_col==$bg_col_01)?$bg_col_02:$bg_col_01;
			$nome_jogo = $vendas_estado_row['ve_jogo'];
			
			$jogo[] 			= $nome_jogo;
			$nvenda[] 			= $vendas_estado_row['n'];
			$valorvendas[] 		= number_format(($vendas_estado_row['vendas']), 2, ',', '.');
			//$percvenda[]	 	= number_format(100*($vendas_estado_row['vendas'])/(($total_vendas_neste_mes==0)?1:$total_vendas_neste_mes), 2, ',', '.');
			
			
			$previous_value = $vendas_estado_row['vendas'];
			$valtmp += $vendas_estado_row['vendas'];
			$ntmp += $vendas_estado_row['n'];
		}
		$retorno =  $grid->gera_grid_jogo_mes_MONEY_STATS2($colunas,$jogo,$nvenda,$valorvendas,$percvenda,$crescente_depois,LANG_STATISTICS_FOR_GAME_THIS_MONTH,$ordem);
		require_once('grid.php');		
		echo "<table><tr bgcolor='#FFFFCC'><td align='center'><b>Total</b></td><td align='center'><b>".$ntmp."</b></td><td align='center'><b>".number_format(($valtmp), 2, ',', '.')."</b></td><td>&nbsp;</td></tr></table>";
	} else {
		echo "<font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font>";
	}
}

if($abanome == LANG_STATISTICS_FOR_STATE) {
	// ".LANG_STATISTICS_FOR_STATE."
	$sql = get_sql_query("E", "por_estado", addWhereClause($extra_where, $where_operadora), $smode);
//echo "sql: ".$sql."<br>";
	$vendas_estado = SQLexecuteQuery($sql);
	$previous_value = -1;
	$bg_col = $bg_col_01;
	
	//Adicionado por Wagner
	$total_table = pg_num_rows($vendas_estado);
	//echo $sql."<br>TOTAL: ".$total_table."<br>";
	$sql = substr($sql,0,strpos($sql, 'order'));
	$sql .= " order by ".$ordem." ".$crescente; 
	$sql .= " limit ".$max; 
	$sql .= " offset ".$inicial;
	//echo "$sql<br>";
	$vendas_estado = SQLexecuteQuery($sql);

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

if($abanome == LANG_STATISTICS_FOR_CITY) {
	$sql = get_sql_query("E", "por_cidade", addWhereClause($extra_where, $where_operadora), $smode);
//echo "sql: ".$sql."<br>";
	$vendas_estado = SQLexecuteQuery($sql);
	$previous_value = -1;
	$bg_col = $bg_col_01;

	//Adicionado por Wagner
	$total_table = pg_num_rows($vendas_estado);
	//echo $sql."<br>TOTAL: ".$total_table."<br>";
	$sql = substr($sql,0,strpos($sql, 'order'));
	$sql .= " order by ".$ordem." ".$crescente; 
	$sql .= " limit ".$max; 
	$sql .= " offset ".$inicial;
	//echo "$sql<br>";
	$vendas_estado = SQLexecuteQuery($sql);

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
			$vven[]   = number_format(($vendas_estado_row['vendas']), 2, ',', '.');
			//$vper[]   = number_format(100*($vendas_estado_row['vendas'])/(($total_vendas==0)?1:$total_vendas), 2, ',', '.');
			
			
			$previous_value = $vendas_estado_row['vendas'];
		}
		$retorno =  $grid->gera_grid_cidade($colunas,$cidade,$estado,$nven,$vven,$vper,$crescente_depois,LANG_STATISTICS_FOR_CITY,$ordem);
		require_once('grid.php');		
	} else {
		echo "<font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font>";
	}
}

if($abanome == LANG_STATISTICS_FOR_USER) {
	if($_SESSION["tipo_acesso_pub"]!='PU') {
		// ".LANG_STATISTICS_FOR_USER."	vg_ex_email
		$sql = get_sql_query("E", "por_usuario", addWhereClause($extra_where, $where_operadora), $smode);
//echo "sql: ".$sql."<br>";
		$vendas_estado = SQLexecuteQuery($sql);
		$previous_value = -1;
		$bg_col = $bg_col_01;
		$n_total_usuarios_compra = pg_num_rows($vendas_estado);

		//Adicionado por Wagner
		$total_table = pg_num_rows($vendas_estado);
		//echo $sql."<br>TOTAL: ".$total_table."<br>";
		$sql = substr($sql,0,strpos($sql, 'order'));
		$sql .= " order by ".$ordem." ".$crescente; 
		$sql .= " limit ".$max; 
		$sql .= " offset ".$inicial;
		//echo "$sql<br>";
		$vendas_estado = SQLexecuteQuery($sql);

		//echo "n_cadastros: ".$n_cadastros."<br>";
		//echo "pg_num_rows(vendas_estado): ".pg_num_rows($vendas_estado)."<br>";

		$colunas[] = LANG_USER;
		$colunas[] = LANG_START_DATE;
		$colunas[] = LANG_LAST_DATE;
		$colunas[] = LANG_ABANDON;			
		$colunas[] = LANG_FIRST_LAST;
		$colunas[] = LANG_NUMBER_SALES;
		$colunas[] = LANG_VALUE_SALES;
		//$colunas[] = LANG_VALUE;	

		if($vendas_estado) {
			$i = 0;
			$a_vendas_ultimo_mes = array();
			while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
				$a_vendas_ultimo_mes[$i++] = $vendas_estado_row;
		
				$user[] 	= $vendas_estado_row['ve_nome'];
				$dtini[]		= date('Y-m-d',strtotime($vendas_estado_row['primeira_venda']));
				$dtfim[]		= date('Y-m-d',strtotime($vendas_estado_row['ultima_venda']));
				$aband[]		= intval(($today1 - strtotime($vendas_estado_row['ultima_venda']))/86400+1);						
				$priulve[] 	= get_delay_alert_live($vendas_estado_row['primeira_venda'], $vendas_estado_row['ultima_venda']);
				$nven[] 	= $vendas_estado_row['n'];
				$vven[]		= number_format(($vendas_estado_row['vendas']), 2, ',', '.');
				//$vper[]		= number_format(100*($vendas_estado_row['vendas'])/(($total_vendas==0)?1:$total_vendas), 2, ',', '.');
				
				$previous_value = $vendas_estado_row['vendas'];
			}
			$retorno =  $grid->gera_grid_usuario($colunas,$user,$dtini,$dtfim,$aband,$priulve,$nven,$vven,$vper,$crescente_depois,LANG_STATISTICS_FOR_USER,$ordem);
			require_once('grid.php');			
		} else {
			echo "<font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font>";
		}
	}
}

if($_SESSION["tipo_acesso_pub"]!='PU') {
	// Totais de vendas M�s
	$previousmonth = mktime(0, 0, 0, date("m"), date("d")-$days_in_month, date("Y"));
	$extra_where = " ($where_mode_data>='".date("Y-m-d H:i:s", $previousmonth)."') ";
	$sql = get_sql_query("E", "totais_de_vendas", addWhereClause($extra_where, $where_operadora), $smode);
	$total_vendas = 0;
	$n_vendas_mes = 0;
	$vendas_estado = SQLexecuteQuery($sql);
	if($vendas_estado) {
		while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
			$total_vendas = $vendas_estado_row['vendas'];
			$n_vendas_mes = $vendas_estado_row['n'];
		}
	} else {
		echo "<table><tr bgcolor='".$bg_col."'><td align='center' colspan='5'><font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font></td></tr></table>";
	}

	// Totais de vendas
	$previousweek = mktime(0, 0, 0, date("m"), date("d")-7, date("Y"));
	$extra_where = " ($where_mode_data>='".date("Y-m-d H:i:s", $previousweek)."') ";
	$sql = get_sql_query("E", "totais_de_vendas", addWhereClause($extra_where, $where_operadora), $smode);
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

if($abanome == LANG_STATISTICS_FOR_LAST_MONTH) {
	if($_SESSION["tipo_acesso_pub"]!='PU') {
		$sql = get_sql_query("E", "por_usuario", addWhereClause($extra_where, $where_operadora), $smode);
		//echo "sql: $sql<br>";
		$vendas_estado = SQLexecuteQuery($sql);

		//Adicionado por Wagner
		$total_table = pg_num_rows($vendas_estado);
		//echo $sql."<br>TOTAL: ".$total_table."<br>";
		$sql = substr($sql,0,strpos($sql, 'order'));
		$sql .= " order by ".$ordem." ".$crescente; 
		$sql .= " limit ".$max; 
		$sql .= " offset ".$inicial;
		//echo "$sql<br>";
		$vendas_estado = SQLexecuteQuery($sql);

		$previous_value = -1;
		$bg_col = $bg_col_01;
		$total_vendas_mes = 0;
		$n_vendas_mes = 0;
		
		$colunas[] = LANG_USER;
		$colunas[] = 'Pos';
		$colunas[] = LANG_NUMBER_SALES;
		$colunas[] = LANG_VALUE_SALES;
		//$colunas[] = LANG_VALUE;	;				
		
		if($vendas_estado) {
			$iorder = 0;
			while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
				$bg_col = ($previous_value!=$vendas_estado_row['vendas'])?(($bg_col==$bg_col_01)?$bg_col_02:$bg_col_01):$bg_col;
				$ipos = getPositionInArray("M", $vendas_estado_row['ve_nome'], $iorder++, $a_vendas_ultimo_mes);
				$sarrow_alt = (($ipos>0)?"Up":(($ipos==0)?"Equal":"Down"));
				$sarrow = "<img src='/sys/imagens/".(($ipos>0)?"arrow_up_2.gif":(($ipos==0)?"equal.gif":"arrow_down_2.gif"))."' width='12' height='12' border='0' alt='$sarrow_alt'>";
				
				$usuario[] = $vendas_estado_row['ve_nome'];
				$pos[] = $sarrow.(($ipos>0)?"+":(($ipos==0)?"&nbsp;":"")).$ipos;
				$nven[] = $vendas_estado_row['n'];
				$vven[] = number_format(($vendas_estado_row['vendas']), 2, ',', '.');
				//$vper[] = number_format(100*($vendas_estado_row['vendas'])/$total_vendas, 2, ',', '.');
				
				$previous_value = $vendas_estado_row['vendas'];
				$total_vendas_mes += $vendas_estado_row['vendas'];
				$n_vendas_mes += $vendas_estado_row['n'];
			}
			$retorno =  $grid->gera_grid_usuario_ultimo_mes($colunas,$usuario,$pos,$nven,$vven,$vper,$crescente_depois,LANG_STATISTICS_FOR_LAST_MONTH,$ordem);
			require_once('grid.php');			
		} else {
			echo "<br><font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font><br>";
		}
		echo "<br><br><table><tr bgcolor='#CCFFCC'><td align='right' colspan='2'><b>".LANG_STATISTICS_TOTAL."</b>&nbsp;</td><td align='center'>".$n_vendas_mes."</td><td align='center'>".number_format(($total_vendas_mes), 2, ',', '.')."</td><td align='center'>&nbsp;</td></tr>";
		echo "</table><br>";
	}
}

if($abanome == LANG_STATISTICS_FOR_LAST_WEEK) {
	if($_SESSION["tipo_acesso_pub"]!='PU') {
		// ".LANG_STATISTICS_FOR_USER." �ltima semana
		$sql = get_sql_query("E", "por_usuario", addWhereClause($extra_where, $where_operadora), $smode);
		//echo "sql: $sql<br>";
		$vendas_estado = SQLexecuteQuery($sql);

		//Adicionado por Wagner
		$total_table = pg_num_rows($vendas_estado);
		//echo $sql."<br>TOTAL: ".$total_table."<br>";
		$sql = substr($sql,0,strpos($sql, 'order'));
		$sql .= " order by ".$ordem." ".$crescente; 
		$sql .= " limit ".$max; 
		$sql .= " offset ".$inicial;
		//echo "$sql<br>";
		$vendas_estado = SQLexecuteQuery($sql);

		$previous_value = -1;
		$bg_col = $bg_col_01;
		$total_vendas_sem = 0;
		$n_vendas_sem = 0;
		
		$colunas[] = LANG_USER;
		$colunas[] = 'Pos';
		$colunas[] = LANG_NUMBER_SALES;
		$colunas[] = LANG_VALUE_SALES;
		//$colunas[] = LANG_VALUE;	

		if($vendas_estado) {
			$iorder = 0;
			while ($vendas_estado_row = pg_fetch_array($vendas_estado)){
				$bg_col = ($previous_value!=$vendas_estado_row['vendas'])?(($bg_col==$bg_col_01)?$bg_col_02:$bg_col_01):$bg_col;
				$ipos = getPositionInArray("M", $vendas_estado_row['ve_nome'], $iorder++, $a_vendas_ultimo_mes);
				$sarrow_alt = (($ipos>0)?"Up":(($ipos==0)?"Equal":"Down"));
				$sarrow = "<img src='/sys/imagens/".(($ipos>0)?"arrow_up_2.gif":(($ipos==0)?"equal.gif":"arrow_down_2.gif"))."' width='12' height='12' border='0' alt='$sarrow_alt'>";

				
				$usuario[]= $vendas_estado_row['ve_nome'];
				$pos[]= $sarrow.(($ipos>0)?"+":(($ipos==0)?"&nbsp;":"")).$ipos;
				$nven[]= $vendas_estado_row['n'];
				$vven[]= number_format(($vendas_estado_row['vendas']), 2, ',', '.');
				//$vper[]= number_format(100*($vendas_estado_row['vendas'])/$total_vendas, 2, ',', '.');
				
				$previous_value = $vendas_estado_row['vendas'];
				$total_vendas_sem += $vendas_estado_row['vendas'];
				$n_vendas_sem += $vendas_estado_row['n'];
			}
			$retorno =  $grid->gera_grid_usuario_ultimo_mes($colunas,$usuario,$pos,$nven,$vven,$vper,$crescente_depois,LANG_STATISTICS_FOR_LAST_WEEK,$ordem);
			require_once('grid.php');					
		} else {
			echo "<br><font color='#FF0000'>".LANG_NOT_FOUND_2."&nbsp;</font></td><br>\n";
		}
		echo "<br><br><table><tr bgcolor='#CCFFCC'><td align='right' colspan='2'><b>".LANG_STATISTICS_TOTAL."</b>&nbsp;</td><td align='center'>".$n_vendas_sem."</td><td align='center'>".number_format(($total_vendas_sem), 2, ',', '.')."</td><td align='center'>&nbsp;</td></tr>\n";
		echo "</table><br>\n";
	}
}
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
	<td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td><td bgcolor="#FFFFFF" class="texto"><?php echo LANG_STATISTICS_SEARCH_MSG." ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')." ".LANG_STATISTICS_SEARCH_MSG_UNIT; ?></font></td><td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td>
  </tr>
</table>
