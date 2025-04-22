<?php
	//require_once '../includes/constantes.php';
    //require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
	//Mater esta variavel com este valor
	//Coloca-la com true nas paginas que for debugar apos a chamada deste include
	$varBlDebug = false; 
//	$varBlDebug = true; 
	// ===============================================================
	function displayEstoque_POS($distributor_codigo, $pin_valor) {
		global $PINS_STORE_STATUS, $DISTRIBUIDORAS;
		$msg = "";
		$vgm_opr_codigo = $distributor_codigo;
		$vgm_pin_valor = $pin_valor;
		$opr_nome = '';
		//PINS
		//---------------------------------------------------------------------------------------------------
		// Lista opr_nome+opr_codigo_pin_valor+pin_qtde_1+pin_qtde_7 por linha
		$sql = "select distributor_codigo,pin_valor";
		foreach($PINS_STORE_STATUS as $key => $val) {
			$sql .=	",count(case when pin_status='".intval($key)."' then 1 end) as pins_qtde_".strtolower(substr(addslashes($val),0,4));
		}
		$sql .=	" from pins_store p
				  where pin_canal = 'w' ";
		if (!empty($distributor_codigo))
			$sql .=" and distributor_codigo = ".intval($distributor_codigo);
		if (!empty($pin_valor))
			$sql .=" and pin_valor = ".intval($pin_valor);
		$sql .=	" group by distributor_codigo, pin_valor 
				  order by distributor_codigo, pin_valor;";
		//echo $sql; 
		$rs_pins = SQLexecuteQuery($sql);
		if($rs_pins) {
			$msg .= "<table cellpadding='0' cellspacing='2' border='2' bordercolor='#cccccc' style='border-collapse:collapse;'><tr><td align='center'>\n";
			$msg .= "<table width='100%'>\n";
			$msg .= "<tr align='center' bgcolor='#FFFFCC'><td>Distribuidora</td><td>cod</td><td>Valor<br>(R$)</td>";
			foreach($PINS_STORE_STATUS as $key => $val) {
				$msg .=	"<td title='".$val."' colspan='2'><b>Qtde.<br>".$val."(".$key.")</b></td>";
			}
			$msg .=	"<td title='Totais' colspan='2'><b>Totais</b></td>";
			$msg .="</tr>\n";
			$new_opr = false;
			$distributor_codigo_last = "";
//			$n_pins_limit = 10;
			$totais = array();
			$totais_parcial = array();
			$totais_pin_valor = array();
			$totais_pin_valor_qtde = array();
			$totais_geral_qtde = 0;
			$totais_geral_valor = 0;
			while ($rs_pins_row = pg_fetch_array($rs_pins)){
				if($distributor_codigo_last!=$rs_pins_row['distributor_codigo']) {
					if(!empty($distributor_codigo_last)){
						$msg .= "<tr bgcolor='#CCCCCC'><td align='right' colspan='3'><b>Totais Parciais</b></td>\n";
						$total_aux_parcial = 0;
						foreach($totais_parcial as $key => $value) {
							if($value>0) {
								$msg .= "<td colspan='2' align='right'><b>".number_format($value, 2, ',', '.')."</b></td>\n";
							}
							else {
								$msg .= "<td colspan='2'></td>\n";
							}
							$total_aux_parcial +=  $value;
						}//end foreach
						$msg .= "<td colspan='2' align='right'><b>".number_format($total_aux_parcial, 2, ',', '.')."</b></td>\n";
						$msg .= "</tr>\n";
						unset($totais_parcial);
						$totais_parcial = array();
					}
					$msg .= "<tr bgcolor='#cccccc'><td colspan='19' height='1'></td></tr>\n";
					$msg .= "<tr><td><nobr>".$DISTRIBUIDORAS[$rs_pins_row['distributor_codigo']]['distributor_name']."</nobr></td><td>".$rs_pins_row['distributor_codigo']."</td>";
				} else {
					$msg .= "<tr><td>&nbsp;</td><td>&nbsp;</td>";
				}
				$msg .= "<td align='right'>".number_format($rs_pins_row['pin_valor'], 2, ',', '.')."</td>";
				$totais_pin_valor[$rs_pins_row['pin_valor']] = 0;
				$totais_pin_valor_qtde[$rs_pins_row['pin_valor']] = 0;
				foreach($PINS_STORE_STATUS as $key => $val) {
					$indice_aux ="pins_qtde_".strtolower(substr($val,0,4));
//					$msg .=	"<td align='center'".(($rs_pins_row[$indice_aux]>$n_pins_limit)?"":" style='background-color:#FFFF00'")."><font color='".(($rs_pins_row[$indice_aux]>$n_pins_limit)?"":"#FF0000")."'>".$rs_pins_row[$indice_aux]."</font></td>";
					$msg .=	"<td align='center' ".(($rs_pins_row[$indice_aux]==0)?"colspan='2'":"")."><font color='".(($rs_pins_row[$indice_aux]>0)?"#000000":"#CCCCFF")."'>".$rs_pins_row[$indice_aux]."</font></td>";
					if($rs_pins_row[$indice_aux]>0) {
						$msg .=	"<td align='right'><font color='".(($rs_pins_row[$indice_aux]>0)?"#000000":"#CCCCFF")."' size='1'>".number_format($rs_pins_row[$indice_aux]*$rs_pins_row['pin_valor'], 2, ',', '.')."</font></td>";
					}
					$totais[$indice_aux] += $rs_pins_row[$indice_aux]*$rs_pins_row['pin_valor']; 
					$totais_parcial[$indice_aux] += $rs_pins_row[$indice_aux]*$rs_pins_row['pin_valor'];
					$totais_pin_valor[$rs_pins_row['pin_valor']] += $rs_pins_row[$indice_aux];
					$totais_pin_valor_qtde[$rs_pins_row['pin_valor']] += $rs_pins_row[$indice_aux]*$rs_pins_row['pin_valor'];
				}
				$totais_geral_qtde += $totais_pin_valor[$rs_pins_row['pin_valor']];
				$totais_geral_valor += $totais_pin_valor_qtde[$rs_pins_row['pin_valor']];
				$msg .=	"<td align='right'><font color='".(($totais_pin_valor[$rs_pins_row['pin_valor']]>0)?"#000000":"#CCCCFF")."' size='1'>".number_format($totais_pin_valor[$rs_pins_row['pin_valor']], 0, ',', '.')."</font></td>";
				$msg .=	"<td align='right'><font color='".(($totais_pin_valor_qtde[$rs_pins_row['pin_valor']]>0)?"#000000":"#CCCCFF")."' size='1'>".number_format($totais_pin_valor_qtde[$rs_pins_row['pin_valor']], 2, ',', '.')."</font></td>";
				$distributor_codigo_last = $rs_pins_row['distributor_codigo'];
			}
			$msg .= "<tr bgcolor='#CCCCCC'><td align='right' colspan='3'><b>Totais Parciais</b></td>\n";
			$total_aux_parcial = 0;
			foreach($totais_parcial as $key => $value) {
				if($value>0) {
					$msg .= "<td colspan='2' align='right'><b>".number_format($value, 2, ',', '.')."</b></td>\n";
				}
				else {
					$msg .= "<td colspan='2'></td>\n";
				}
				$total_aux_parcial +=  $value;
			}//end foreach
			$msg .= "<td colspan='2' align='right'><b>".number_format($total_aux_parcial, 2, ',', '.')."</b></td>\n";
			$msg .= "</tr>\n";
			$msg .= "<tr bgcolor='#CCCCCC'><td align='right' colspan='3'><b>TOTAIS</b></td>\n";
			foreach($totais as $key => $value) {
				if($value>0) {
					$msg .= "<td colspan='2' align='right'><b>".number_format($value, 2, ',', '.')."</b></td>\n";
				}
				else {
					$msg .= "<td colspan='2'></td>\n";
				}
			}//end foreach
			$msg .= "<td align='right'><b>".number_format($totais_geral_qtde, 0, ',', '.')."</b></td>\n";
			$msg .= "<td align='right'><b>".number_format($totais_geral_valor, 2, ',', '.')."</b></td>\n";
			$msg .= "</tr>\n";
			$msg .= "</table>\n";
			$msg .= "</td></tr></table>\n";
		} else {
			$msg .= "<p><font color='#FF0000'>Não foram encontrados pins para canal 'w'</font></p><br>\n";
		}
		return $msg;
	}

	
function VetorDistribuidoras() {
	global $DISTRIBUIDORAS;
	foreach($DISTRIBUIDORAS as $key => $val) {
				$operacao_array[$key]=$DISTRIBUIDORAS[$key]['distributor_name'].' - Formato ('.$DISTRIBUIDORAS[$key]['distributor_format'].')';
	}
	return $operacao_array;
}

function VetorOperadoras() {
	$sql_opr = "select opr_pin_epp_formato,opr_nome,opr_codigo from operadoras where opr_pin_epp_formato is not null order by opr_nome";
	$rs_oper = SQLexecuteQuery($sql_opr);
	if($rs_oper) {
		while ($rs_oper_row = pg_fetch_array($rs_oper)) {
				$operacao_array[$rs_oper_row['opr_codigo']]=$rs_oper_row['opr_nome'].' - Formato ('.$rs_oper_row['opr_pin_epp_formato'].')';
			}
	}
	return $operacao_array;
}

?>