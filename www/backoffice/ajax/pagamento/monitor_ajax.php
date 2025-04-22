<?php 
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
//die("Stop");
	header("Content-Type: text/html; charset=ISO-8859-1",true);

	require_once '../../../includes/constantes.php';
        require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
        require_once $raiz_do_projeto."includes/main.php";
        require_once $raiz_do_projeto."includes/gamer/main.php";
	require_once $raiz_do_projeto."includes/inc_Pagamentos.php";

	$time_start = getmicrotime();

	$nitens = 30;

//	echo "tipo: ".$tipo."<br>";
	// Define delay in seconds
	if($tipo=="M") {
		$date_delay = 2;
//		echo "date_delay: ".$date_delay."<br>";
	} else if($tipo=="L") {
		$date_delay = 2;
//		echo "date_delay: ".$date_delay."<br>";
	} else {
	}

	$date_now = date("Y-m-d H:i:s");
//	echo "date_now: ".$date_now."<br>\n";
//	$date_now_adjusted = date("Y-m-d H:i:s", strtotime($date_now.' -1 hour'));
//	echo "date_now_adjusted: ".$date_now_adjusted."<br>\n";

	// get data from session
	if($tipo=="M") {
		$date_prev = $_SESSION["date_prev_M"];
	} else if($tipo=="L") {
		$date_prev = $_SESSION["date_prev_L"];
	} else {
		$date_prev = $date_now;
	}
//	echo "date_prev: ".$date_prev."<br>";
//	echo "date_now: ".$date_now."<br>";
//	echo "Diff: ".intval((strtotime($date_now)-strtotime($date_prev)))."<br><br>";

	// is time to reload?
	if(strtotime($date_now)>(strtotime($date_prev)+$date_delay)) {
//		echo "<font color='#FF0000'>RELOAD</font><br>";

		$sql_query = "*";
		$sret = "";

//echo "tipo: ".$tipo."<br>\n";
//echo "date_now: ".$date_now."<br>\n";
		$date_prev_adjusted = date("Y-m-d H:i:s", strtotime($date_prev.' -10 hour'));
//echo "date_prev_adjusted: ".$date_prev_adjusted."<br>\n";


		// Save data in session
		if($tipo=="M") {
			$_SESSION["date_prev_M"] = $date_now;

			$sql_query = get_sql_monitor($tipo, $date_prev_adjusted, $nitens);

if($_SESSION['userlogin_bko']=='WAGNER') {
//echo "sql_query: ".$sql_query."<br>\n";
}
		} else if($tipo=="L") {
			$_SESSION["date_prev_L"] = $date_now;

			$sql_query = get_sql_monitor($tipo, $date_prev_adjusted, $nitens);

//echo "sql_query: ".$sql_query."<br>\n";
		} else {
//			echo "<font color='#0000FF'>TIPO DESCONHECIDO: ".$tipo."</font><br>";
		}
		
		if($sql_query){


			$nGradientsTri = 120;
			$GradientsTri = GradientTri("5AAA20", "FFD784", "FFFFFF", $nGradientsTri);
			$bin_size = 60*60/(2*$nGradientsTri);
			$stmp = "";

//echo "date_now: ".$date_now."<br>\n";
echo "".$date_now."<br>\n";		// " (".number_format($bin_size, 2, '.', '.')."s/color) "
//echo $sql_query."<br>\n";
//die("Stop");
//echo "session_id : ".session_id()."<br>\n";

			$rs_venda = SQLexecuteQuery($sql_query);
			if($rs_venda && pg_num_rows($rs_venda)>0) {
//echo "N: ".pg_num_rows($rs_venda)." registros<br>\n";
				$total_qtde = 0;
				$total_valor = 0;
				$total_valor_cum = 0;
				$total_delay = 0;
				$total_delay_val = 0;
				$n_integracao = 0;
				$n_total = 0;
				$s_ids = "";

				$sret = "<table cellpadding='0' cellspacing='2' border='1' bordercolor='#cccccc' style='border-collapse:collapse;'>\n";
				$sret .= "<tr><td align='center' width='8%'><font color='#00008C' size='1' face='Arial, Helvetica, sans-serif'>&nbsp;</font></td>\n";
				$sret .= "<td align='center' width='23%'><font color='#00008C' size='1' face='Arial, Helvetica, sans-serif'>hora</font></td>\n";
				$sret .= "<td align='center' width='23%'><font color='#00008C' size='1' face='Arial, Helvetica, sans-serif'>qtde</font></td>\n";
				$sret .= "<td align='center' width='23%'><font color='#00008C' size='1' face='Arial, Helvetica, sans-serif'>valor</font></td>\n";
				$sret .= "<td align='center' width='23%'><font color='#00008C' size='1' face='Fixed, Arial, Helvetica, sans-serif'>&nbsp;&nbsp;&nbsp;&nbsp;delay&nbsp;&nbsp;&nbsp;&nbsp;</font></td>\n";
				$sret .= "<td align='center'><font color='#00008C' size='1' face='Fixed, Arial, Helvetica, sans-serif'>&nbsp;&nbsp;&nbsp;&nbsp;opr&nbsp;&nbsp;&nbsp;&nbsp;</font></td>\n";
				$sret .= "</tr>\n";
				while($rs_venda_row = pg_fetch_array($rs_venda)){

//echo "OK ".$rs_venda_row["vg_canal"]."<br>\n";
					$b_is_vg_pagto_tipo_online = b_IsPagtoOnline($rs_venda_row["vg_pagto_tipo"])||($rs_venda_row["vg_pagto_tipo"]==$GLOBALS['PAGAMENTO_MCOIN_NUMERIC']);
					$b_is_vg_pagto_integracao = (isset($rs_venda_row["vg_integracao_parceiro_origem_id"]) && $rs_venda_row["vg_integracao_parceiro_origem_id"]!="");
					if($b_is_vg_pagto_integracao) $n_integracao++;
					$n_total++;

					$vg_drupal_order_id = 0;
					if($tipo=="M") {
						$stitle = (($b_is_vg_pagto_tipo_online)?"Pagto Online":"Money")."  (".getDescricaoPagtoOnline($rs_venda_row["vg_pagto_tipo"]).")";
						$s_ids .= $rs_venda_row["vg_id"].", ";
						$vg_drupal_order_id = (($b_is_vg_pagto_integracao)?"":$rs_venda_row["vg_drupal_order_id"]);
					} elseif($tipo=="L") {
						$stitle = "LanHouses";
                                                $vg_drupal_order_id = $rs_venda_row["vg_drupal_order_id"];
					}
					$icolor = (int)($rs_venda_row["delay"]*2*$nGradientsTri/(60*60));
					if($icolor>=2*$nGradientsTri) $icolor = 2*$nGradientsTri-1;

//$stmp .= $nGradientsTri.", ".number_format($rs_venda_row["delay"], 2, '.', '.').", ".$icolor.", #".$GradientsTri[$icolor]."<br>\n";

//					$stitle = (($rs_venda_row["vg_pagto_tipo"]==5 || $rs_venda_row["vg_pagto_tipo"]==6 || $rs_venda_row["vg_pagto_tipo"]==9 || $rs_venda_row["vg_pagto_tipo"]==10 )?"Pagto Online":"");
					$total_valor_this = $rs_venda_row["valor"]*(($tipo=="M")?$rs_venda_row["qtde_itens"]:1);
					$total_valor_cum += $total_valor_this;
					$delay = $rs_venda_row["delay"];
					$total_por_min = (($rs_venda_row["delay"])?(60*$total_valor_cum / $delay):0);

					$scolor_iforma = ( ($rs_venda_row["vg_pagto_tipo"]==$GLOBALS['PAGAMENTO_PIN_EPREPAG_NUMERIC']) ? "color:red" : 
						((b_IsPagtoCielo($rs_venda_row["vg_pagto_tipo"])) ? "color:cyan" : 
							($rs_venda_row["vg_pagto_tipo"]==$GLOBALS['PAGAMENTO_MCOIN_NUMERIC']) ? "color:yellow" : "" 
						) );

					$sret .= "<tr bgcolor='#".$GradientsTri[$icolor]."' onclick='abre_venda('".$tipo."', ".$rs_venda_row["vg_id"].")' title='vg_id: ".$rs_venda_row["vg_id"]."".((isset($rs_venda_row["vg_integracao_parceiro_origem_id"]) && $rs_venda_row["vg_integracao_parceiro_origem_id"])?" (Venda Integração - store_id: ".$rs_venda_row["vg_integracao_parceiro_origem_id"].")":""). (($vg_drupal_order_id>0)?"\ndrupal _order_id: $vg_drupal_order_id":"") ."'>\n";
						$sret .= "<td align='center'><span title='".$stitle."'><font color='#00008C' size='1' face='Arial, Helvetica, sans-serif'> ".$rs_venda_row["vg_canal"].(($rs_venda_row["vg_canal"]=="M")?( ($b_is_vg_pagto_tipo_online) ? "&nbsp;(<span style='".$scolor_iforma."'>".$rs_venda_row["vg_pagto_tipo"]."</span>)":""):"") .(($vg_drupal_order_id>0)?"&nbsp;[<span style='color:red'>D</span>]":"") . "</font></span></td>\n";
						$sret .= "<td align='center'><font color='#00008C' size='1' face='Arial, Helvetica, sans-serif'> ".substr($rs_venda_row["data_venda"],11,8)." </font></td>\n";
						
						$sret .= "<td align='center'><font color='#00008C' size='1' face='Arial, Helvetica, sans-serif'> ".$rs_venda_row["qtde_itens"].(($tipo=="L")?" (".$rs_venda_row["qtde_produtos"].")":"")." </font></td>\n";
						
						$sret .= "<td align='right' title='&#8721;=R$".number_format($total_valor_cum, 2, '.', '.')."";
						if($_SESSION['userlogin_bko']=='WAGNER') {
							$sret .= "\nR$".number_format($total_por_min, 2, '.', ',')."/min";
//							$sret .= " (".number_format($delay, 2, '.', ',')." min)";
						}
						$sret .= "'><font color='#00008C' size='1' face='Arial, Helvetica, sans-serif'> ".number_format($total_valor_this, 2, '.', ',')." </font></td>\n";

						$sret .= "<td align='right'><font color='#00008C' size='1' face='Arial, Helvetica, sans-serif'> ".convert_secs_to_string($delay)." </font></td>\n";
						
						$sret .= "<td align='center'><font color='".((isset($rs_venda_row["vg_integracao_parceiro_origem_id"]) && $rs_venda_row["vg_integracao_parceiro_origem_id"])?"red":(($rs_venda_row["opr_nome"]=="E-Prepag Cash")?"#33CC00":"#00008C"))."' size='1' face='Arial, Helvetica, sans-serif'><nobr>".$rs_venda_row["opr_nome"]."</nobr></font></td>\n";
					$sret .= "</tr>\n";	
									// &nbsp;(".$icolor.",&nbsp;#".$GradientsTri[$icolor].")
									// title='(icolor= ".($icolor+1)." de ".(2*$nGradientsTri).",&nbsp;#".$GradientsTri[$icolor].")'

					$total_qtde += $rs_venda_row["qtde_itens"];
					$total_valor += $total_valor_this;
/*
					if($tipo=="M") {
						$total_valor += $rs_venda_row["valor"]*$rs_venda_row["qtde_itens"];
//						$total_delay_val += $rs_venda_row["delay"]*($rs_venda_row["valor"]*$rs_venda_row["qtde_itens"]);
					} else if($tipo=="L") {
						$total_valor += $rs_venda_row["valor"]; //*$rs_venda_row["qtde_itens"];
//						$total_delay_val += $rs_venda_row["delay"]*$rs_venda_row["valor"];
					}
*/
					$total_delay = (($total_delay<$rs_venda_row["delay"])?$rs_venda_row["delay"]:$total_delay);
				}

//				$indice_delay = ($total_delay*$total_valor-$total_delay_val)/$nitens;

				$sret .= "<tr>\n";
				$sret .= "<td align='left' colspan='5'><font color='#00008C' size='1' face='Arial, Helvetica, sans-serif'> Totais nos últimos ".$n_total." registros. </font></td>\n";
				$sret .= "<td><font color='#00008C' size='1' face='Arial, Helvetica, sans-serif'>&nbsp;</font></td>\n";
				$sret .= "</tr>\n";
				$sret .= "<tr>\n";
				$sret .= "<td align='right' colspan='2'><font color='#00008C' size='1' face='Arial, Helvetica, sans-serif'>Totais: &nbsp;</font></td>\n";
				$sret .= "<td align='center'><font color='#00008C' size='1' face='Arial, Helvetica, sans-serif'> ".$total_qtde." </font></td>\n";
				$sret .= "<td align='right'><font color='#00008C' size='1' face='Arial, Helvetica, sans-serif'> ".number_format($total_valor, 2, '.', '.')." </font></td>\n";
				$sret .= "<td align='right'><font color='#00008C' size='1' face='Arial, Helvetica, sans-serif'> ".convert_secs_to_string($total_delay)." </font></td>\n";
				$sret .= "<td align='right'><font color='#00008C' size='1' face='Arial, Helvetica, sans-serif'>&nbsp;";
				if($_SESSION['userlogin_bko']=='WAGNER') {
					$sret .= "R$".number_format($total_por_min, 2, '.', ',')."/min";
//							$sret .= " (".number_format($delay, 2, '.', ',')." min)";
				}
				$sret .= "</font></td>\n";
				$sret .= "</tr>\n";

//				$sret .= "<tr><td align='right' colspan='3'><font color='#00008C' size='1' face='Arial, Helvetica, sans-serif'>Índice: &nbsp;</font></td><td align='right'><font color='#00008C' size='1' face='Arial, Helvetica, sans-serif'> ".number_format($indice_delay, 2, '.', '.')." </font></td><td align='right'><font color='#00008C' size='1' face='Arial, Helvetica, sans-serif'> &nbsp;</font></td></tr>\n";

				$sret .= "</table>\n";

			} else {
				$sret = "<font color='#FF0000'>Registros não encontrados</font><br>\n";
			}
		} else {
			$sret = "<font color='#FF0000'>sql_query não definido</font><br>\n";
		}
//		echo "(".$tipo.") ".substr($date_now, 11);	//."(".$tipo.")";
		echo $sret."<br>\n";
		echo "Query time: " . number_format(getmicrotime() - $time_start, 2, '.', '.') . "s";
		if($tipo=="M") {
			echo " ($n_integracao venda".(($n_integracao==1)?"":"s")." de integração - " . @number_format((100*$n_integracao/$n_total), 2, '.', '.') . "%)";
//			if($_SESSION['userlogin_bko']=='WAGNER') {
//				echo "<br>".$s_ids."<br>";
//			}
		}
//echo $stmp;
/*
echo "<table width='20%' border='1'>\n";
//foreach($GradientsTri as $Gradient) {
for($j=0;$j<count($GradientsTri);$j++) {
//	echo "<tr bgcolor='#".$Gradient."'><td>&nbsp;".($i++)."&nbsp;</td><td>&nbsp; &nbsp;</td><td>&nbsp;".$Gradient."&nbsp;</td></tr>\n";
	echo "<tr bgcolor='#".$GradientsTri[$j]."'><td>&nbsp;".($j+1)."&nbsp;</td><td>&nbsp; &nbsp;</td><td>&nbsp;".$GradientsTri[$j]."&nbsp;</td></tr>\n";
}
echo "</table>\n";
*/
	} else {
//		echo "<font color='#0000FF'>DON´T RELOAD</font><br>";
	}

	// send data back to caller
//	echo $date_now;

//Fechando Conexão
pg_close($connid);


function get_sql_monitor($tipo, $date_threshold, $limit) {
	$sql = "";
	if($tipo=="M") {
		$sql  = "
                    select 
                        case when vg.vg_ug_id = 7909 then 'E' else 'M' end as vg_canal, 
                        vg.vg_ex_email, 
                        vgm.vgm_qtde, 
                        (case when vgm_opr_codigo = 78 then 0 else vgm.vgm_valor end ) as valor, 
                        vgm.vgm_qtde as qtde_itens, 
                        COALESCE(vg.vg_data_concilia, vg.vg_pagto_data_inclusao, vg.vg_data_inclusao) as data_venda, 
                        EXTRACT(EPOCH FROM (CURRENT_TIMESTAMP-COALESCE(vg.vg_data_concilia, vg.vg_pagto_data_inclusao, vg.vg_data_inclusao))) as delay, 
                        (select opr_nome from operadoras o where o.opr_codigo=vgm.vgm_opr_codigo) as opr_nome, 
                        vg_pagto_tipo, 
                        vg_id, 
                        vg_integracao_parceiro_origem_id, 
                        vg_drupal_order_id 
                    from tb_venda_games vg 
                        inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id  
                    where vg.vg_ultimo_status=5 
                        and vg.vg_data_concilia>='".$date_threshold."'  
                    order by vg.vg_data_concilia desc
                    ";
	} else if($tipo=="L") {
		$sql = "
                    select 
                        'L' as vg_canal, 
                        ug.ug_id, 
                        ug.ug_email, 
                        ug.ug_responsavel, 
                        ug.ug_nome_fantasia, 
                        ug.ug_nome, 
                        ug.ug_tipo_cadastro, 
                        vg.vg_id, 
                        vg.vg_data_inclusao as data_venda, 
                        vg.vg_pagto_tipo, 
                        vg.vg_ultimo_status, 
                        vg.vg_concilia, 
                        sum(vgm.vgm_valor * vgm.vgm_qtde) as valor, 
                        sum(vgm.vgm_qtde) as qtde_itens, 
                        count(*) as qtde_produtos, 
                        sum(vgm.vgm_valor * vgm.vgm_qtde - vgm.vgm_valor * vgm.vgm_qtde * vgm_perc_desconto / 100) as repasse, 
                        EXTRACT(EPOCH FROM (CURRENT_TIMESTAMP-vg.vg_data_inclusao)) as delay, 
                        (select opr_nome from operadoras o where o.opr_codigo=vgm.vgm_opr_codigo) as opr_nome, 
                        vg_id, 
                        vg_drupal_order_id 
                    from tb_dist_venda_games vg 
                        inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                        inner join dist_usuarios_games ug on ug.ug_id = vg.vg_ug_id 
                    where vg.vg_ultimo_status = 5 
                        and vg.vg_data_inclusao>='".$date_threshold."' 
                    group by ug.ug_id, 
                            ug.ug_email, 
                            ug.ug_responsavel, 
                            ug.ug_nome_fantasia, 
                            ug.ug_nome, 
                            ug.ug_tipo_cadastro, 
                            vg.vg_id, 
                            vg.vg_data_inclusao, 
                            vg.vg_pagto_tipo, 
                            vg.vg_ultimo_status, 
                            vg.vg_concilia, 
                            vgm.vgm_opr_codigo,
                            vg.vg_drupal_order_id
                    order by vg.vg_data_inclusao desc";
	}
	if($limit>0) {
		$sql .= " limit ".$limit." ";
	}
	return $sql;
}

	function convert_secs_to_string($n) {
		$sout = "";
		$ndays = 0;
		$nhours = 0;
		$nmins = 0;
		$nsecs = 0;

		$ndays = intval($n/(60*60*24));
		$nhours = str_pad(intval(($n-$ndays*60*60*24)/(60*60)), 2, "0", STR_PAD_LEFT);
		$nmins = str_pad(intval(($n-$ndays*60*60*24-$nhours*60*60)/(60)), 2, "0", STR_PAD_LEFT);
		$nsecs = str_pad(intval(($n-$ndays*60*60*24-$nhours*60*60-$nmins*60)), 2, "0", STR_PAD_LEFT);
		
		
		$sout .= "<font size='1'>";
		$sout .= (($ndays>0)?$ndays."<font color='#FF0000'>d</font>":"");
		$sout .= (($ndays>0 || $nhours>0)?$nhours."<font color='#FF0000'>h</font>":"");
		$sout .= (($ndays>0 || $nhours>0 || $nmins>0)?$nmins."<font color='#FF0000'>m</font>":"");
		$sout .= (($ndays>0 || $nhours>0 || $nmins>0 || $nsecs>0)?$nsecs."<font color='#FF0000'>s</font>":"");
		$sout .= "</font>";

		return $sout;
	}


	function Gradient($HexFrom, $HexTo, $ColorSteps0, $bSkipFirst) {
		$FromRGB['r'] = hexdec(substr($HexFrom, 0, 2));
		$FromRGB['g'] = hexdec(substr($HexFrom, 2, 2));
		$FromRGB['b'] = hexdec(substr($HexFrom, 4, 2));

		$ToRGB['r'] = hexdec(substr($HexTo, 0, 2));
		$ToRGB['g'] = hexdec(substr($HexTo, 2, 2));
		$ToRGB['b'] = hexdec(substr($HexTo, 4, 2));

		$ColorSteps = ($bSkipFirst)?($ColorSteps0+1):$ColorSteps0;


		$StepRGB['r'] = ($FromRGB['r'] - $ToRGB['r']) / ($ColorSteps - 1);
		$StepRGB['g'] = ($FromRGB['g'] - $ToRGB['g']) / ($ColorSteps - 1);
		$StepRGB['b'] = ($FromRGB['b'] - $ToRGB['b']) / ($ColorSteps - 1);

		$GradientColors = array();

		for($i = 0; $i < $ColorSteps; $i++) {
			$RGB['r'] = floor($FromRGB['r'] - ($StepRGB['r'] * $i));
			$RGB['g'] = floor($FromRGB['g'] - ($StepRGB['g'] * $i));
			$RGB['b'] = floor($FromRGB['b'] - ($StepRGB['b'] * $i));

			$HexRGB['r'] = sprintf('%02x', ($RGB['r']));
			$HexRGB['g'] = sprintf('%02x', ($RGB['g']));
			$HexRGB['b'] = sprintf('%02x', ($RGB['b']));

			if(!($bSkipFirst && $i==0)) { 
				$GradientColors[] = strtoupper(implode(NULL, $HexRGB));
			}
		}
		return $GradientColors;
	}

	function GradientTri($HexFrom, $HexTo1, $HexTo2, $ColorStepsEach) {
		$GradientsTri1 = Gradient($HexFrom, $HexTo1, $ColorStepsEach, false);
		$GradientsTri2 = Gradient($HexTo1, $HexTo2, $ColorStepsEach, true);
		$GradientColorsTri = array_merge($GradientsTri1, $GradientsTri2);

		return $GradientColorsTri;
	}


?>

