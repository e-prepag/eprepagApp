<?php require_once __DIR__ . '/constantes_url.php'; ?>
<?php
//Mater esta variavel com este valor
//Coloca-la com true nas paginas que for debugar apos a chamada deste include
$varBlDebug = false; 

// ===============================================================
function displayEstoque_CARDS($opr_codigo, $distributor_codigo, $pin_valor) {
		global $PINS_STORE_STATUS;
                $publisher_array	= VetorOperadorasCard();
                $operacao_array		= VetorDistribuidorasCard();

		$msg = "";
		$vgm_opr_codigo = $distributor_codigo;
		$vgm_pin_valor = $pin_valor;
		$opr_nome = '';
		//PINS
		//---------------------------------------------------------------------------------------------------
		// Lista opr_nome+opr_codigo_pin_valor+pin_qtde_1+pin_qtde_7 por linha
		$sql = "select distributor_codigo, opr_codigo, pin_valor";
		foreach($PINS_STORE_STATUS as $key => $val) {
			$sql .=	",count(case when pin_status='".intval($key)."' then 1 end) as pins_qtde_".strtolower(substr(addslashes($val),0,4));
		}
		$sql .=	" from pins_card p
				  where 1=1 ";
		if (!empty($opr_codigo))
			$sql .=" and opr_codigo = ".intval($opr_codigo);
		if (!empty($distributor_codigo))
			$sql .=" and distributor_codigo = ".intval($distributor_codigo);
		if (!empty($pin_valor))
			$sql .=" and pin_valor = ".intval($pin_valor);
		$sql .=	" group by opr_codigo, distributor_codigo, opr_codigo, pin_valor 
				  order by opr_codigo, distributor_codigo, pin_valor;";
		//echo $sql;  die();
		$rs_pins = SQLexecuteQuery($sql);
		if($rs_pins) {
			$msg .= "<table cellpadding='0' cellspacing='2' border='2' bordercolor='#cccccc' style='border-collapse:collapse;'><tr><td align='center'>".PHP_EOL;
			$msg .= "<table width='100%'>".PHP_EOL;
			$msg .= "<tr align='center' bgcolor='#FFFFCC'><td>Publisher</td><td>cod</td><td>Distribuidora</td><td>cod</td><td>Valor<br>(R$)</td>";
			foreach($PINS_STORE_STATUS as $key => $val) {
				$msg .=	"<td title='".$val."' colspan='2'><b>Qtde.<br>".$val."(".$key.")</b></td>";
			}
			$msg .=	"<td title='Totais' colspan='2'><b>Totais</b></td>";
			$msg .="</tr>".PHP_EOL;
			$new_opr = false;
			$distributor_codigo_last = "";
			$opr_codigo_last = "";
//			$n_pins_limit = 10;
			$totais = array();
			$totais_parcial = array();
			$totais_pin_valor = array();
			$totais_pin_valor_qtde = array();
			$totais_geral_qtde = 0;
			$totais_geral_valor = 0;
			while ($rs_pins_row = pg_fetch_array($rs_pins)){
				if($opr_codigo_last!=$rs_pins_row['opr_codigo']) {
					if(!empty($opr_codigo_last)){
						$msg .= "<tr bgcolor='#CCCCCC'><td align='right' colspan='3'></td>".PHP_EOL;
						$msg .= "<td colspan='2'></td>".PHP_EOL;
						$msg .= "<td colspan='17' align='right'></td>".PHP_EOL;
						$msg .= "</tr>".PHP_EOL."<tr>".PHP_EOL."<td align='right' colspan='5'><b>Totais Parciais</b></td>".PHP_EOL;
						$total_aux_parcial = 0;
						foreach($totais_parcial as $key => $value) {
							if($value>0) {
								$msg .= "<td colspan='2' align='right'><b>".number_format($value, 2, ',', '.')."</b></td>".PHP_EOL;
							}
							else {
								$msg .= "<td colspan='2'></td>".PHP_EOL;
							}
							$total_aux_parcial +=  $value;
						}//end foreach
						$msg .= "<td colspan='2' align='right'><b>".number_format($total_aux_parcial, 2, ',', '.')."</b></td>".PHP_EOL;
						$msg .= "</tr>".PHP_EOL;
						unset($totais_parcial);
						$totais_parcial = array();
					}
					$msg .= "<tr><td><nobr>".$publisher_array[$rs_pins_row['opr_codigo']]."</nobr></td><td>".$rs_pins_row['opr_codigo']."</td>";
				} else {
					$msg .= "<tr><td>&nbsp;</td><td>&nbsp;</td>";
				}
				if($distributor_codigo_last!=$rs_pins_row['distributor_codigo']) {
					if(!empty($distributor_codigo_last)){
                                            /*
						$msg .= "<td align='right' colspan='3'><b>Totais Parciais</b></td>".PHP_EOL;
						$total_aux_parcial = 0;
						foreach($totais_parcial as $key => $value) {
							if($value>0) {
								$msg .= "<td colspan='2' align='right'><b>".number_format($value, 2, ',', '.')."</b></td>".PHP_EOL;
							}
							else {
								$msg .= "<td colspan='2'></td>".PHP_EOL;
							}
							$total_aux_parcial +=  $value;
						}//end foreach
						$msg .= "<td colspan='2' align='right'><b>".number_format($total_aux_parcial, 2, ',', '.')."</b></td>".PHP_EOL;
						$msg .= "</tr>".PHP_EOL;
						unset($totais_parcial);
						$totais_parcial = array();
                                             */
					}
					$msg .= "<td><nobr>".$operacao_array[$rs_pins_row['distributor_codigo']]."</nobr></td><td>".$rs_pins_row['distributor_codigo']."</td>";
				} else {
					$msg .= "<td>&nbsp;</td><td>&nbsp;</td>";
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
				$opr_codigo_last = $rs_pins_row['opr_codigo'];
			}
			$msg .= "<tr bgcolor='#CCCCCC'><td align='right' colspan='5'><b>Totais Parciais</b></td>".PHP_EOL;
			$total_aux_parcial = 0;
			foreach($totais_parcial as $key => $value) {
				if($value>0) {
					$msg .= "<td colspan='2' align='right'><b>".number_format($value, 2, ',', '.')."</b></td>".PHP_EOL;
				}
				else {
					$msg .= "<td colspan='2'></td>".PHP_EOL;
				}
				$total_aux_parcial +=  $value;
			}//end foreach
			$msg .= "<td colspan='2' align='right'><b>".number_format($total_aux_parcial, 2, ',', '.')."</b></td>".PHP_EOL;
			$msg .= "</tr>".PHP_EOL;
			$msg .= "<tr bgcolor='#CCCCCC'><td align='right' colspan='5'><b>TOTAIS</b></td>".PHP_EOL;
			foreach($totais as $key => $value) {
				if($value>0) {
					$msg .= "<td colspan='2' align='right'><b>".number_format($value, 2, ',', '.')."</b></td>".PHP_EOL;
				}
				else {
					$msg .= "<td colspan='2'></td>".PHP_EOL;
				}
			}//end foreach
			$msg .= "<td align='right'><b>".number_format($totais_geral_qtde, 0, ',', '.')."</b></td>".PHP_EOL;
			$msg .= "<td align='right'><b>".number_format($totais_geral_valor, 2, ',', '.')."</b></td>".PHP_EOL;
			$msg .= "</tr>".PHP_EOL;
			$msg .= "</table>".PHP_EOL;
			$msg .= "</td></tr></table>".PHP_EOL;
		} else {
			$msg .= "<p><font color='#FF0000'>Não foram encontrados pins para canal 'w'</font></p><br>".PHP_EOL;
		}
		return $msg;
}

	
function VetorDistribuidorasCard() {
	foreach($GLOBALS['DISTRIBUIDORAS_CARTOES'] as $key => $val) {
		$operacao_array[$key]=$val;
	}
	return $operacao_array;
}

function VetorOperadorasCard() {
	$sql = "select opr_codigo, opr_nome from operadoras where opr_emite_cartao_conosco=1 order by opr_nome;";
        $rs_operadoras = SQLexecuteQuery($sql);
        while ($rs_operadoras_row = pg_fetch_array($rs_operadoras)) {
        	$operacao_array[$rs_operadoras_row['opr_codigo']]=$rs_operadoras_row['opr_nome'];
	} //end while
        return $operacao_array;
}

function VerificaIncomm($post_parameters, $action) {
    
        //Concatenando posição no vetor para a consulta
        $post_parameters['action'] = $action;
        
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, "" . EPREPAG_URL_HTTPS . "/prepag2/commerce/epp_incomm.php");
        // verify the digital certificate
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, 0);
        //  verify digital certificate’s name
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl_handle, CURLOPT_USERAGENT,"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)");
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_handle, CURLOPT_POST, true);
        curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $post_parameters);
        // The number of seconds to wait while trying to connect. 
        // Use 0 to wait indefinitely.
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 0);
        // The maximum number of seconds to allow cURL functions to execute.
        curl_setopt($curl_handle, CURLOPT_TIMEOUT, 90);
        // send the request and get the response
        $buffer = curl_exec($curl_handle);
        //Verificando erro
        $erros_curl = curl_error($curl_handle);

        curl_close($curl_handle);
        if(empty($erros_curl)) {
            return json_decode($buffer);
        }
        else return FALSE;

        
} //end function VerificaIncomm($post_parameters, $action) 

?>