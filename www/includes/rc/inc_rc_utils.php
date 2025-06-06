<?php require_once __DIR__ . '/../constantes_url.php'; ?>
<?php
function get_id_in_vector_form_codigoOperadora($id) {
	$id_operadoras_current = -1;
	foreach($GLOBALS['operadoras_current'] as $key => $val) {
//echo "KEY $key => ('".$val['codigoOperadora']."' == '$id')<br>";
		if($val['codigoOperadora'] == $id) {
			$id_operadoras_current = $key;
			break;
		}
	}
//echo "RETURN $id_operadoras_current<br>";
	return $id_operadoras_current;
}

function get_lista_logos_Operadoras() {
	$sret = "<table border='0' cellpadding='0' cellspacing='0'><tr>";
	foreach($GLOBALS['operadoras_current'] as $key => $val) {
//		$sret .= "".$val['codigoOperadora'].", ".$val['nomeOperadora'].", ".$val['urlLogo']." ";
	
		$sret .= "<td align='center' valign='middle' onMouseOver=\"this.className='linkover'\" onMouseOut=\"this.className='linkout'\"><a href='#priceDiv' onclick=\"document.getElementById('provider').value = ".$val['codigoOperadora']."; carga_valor();\"><img width='50' src='".$val['urlLogo'];

//		src='EPREPAG_URL_HTTP/prepag2/rc/inc/imgs/
//		$img_src = $val['urlLogo'];
//		$file_name = basename(parse_url($img_src, PHP_URL_PATH)); 
//		$auxOprNome3 = $file_name;
//		$sret .= $auxOprNome3;

		$sret .= "' border='0' alt=\"". $val['codigoOperadora']." - '".$val['nomeOperadora']."'\" title=\"".$val['codigoOperadora']." - '".$val['nomeOperadora']." (".$val['urlLogo'].")'\" ></a></td>\n"; //($auxOprNome3)
		
//		$sret .= " ==  ['$img_src' -> '$file_name']\n";
//		$sret .= " ['$auxOprNome1' -> '$auxOprNome2' -> '$auxOprNome3']";

//		$sret .= " (".$img_src.")";
//		$sret .= " {".$auxOprNome3."} ";
//		$sret .= "<br>";
	}
	$sret .= "</tr></table>";

	return $sret;
}

function get_select_Operadoras($id_selected = null) {
	$sret = "";

	$aValores = $GLOBALS['operadoras_current'];

	$sret .= "<select id='provider' name='provider' class='form-xl' onchange='carga_valor();'>\n"; 
	$sret .= "<option value=''>Selecione a Operadora</option>\n";
	foreach($aValores as $key => $val) {
		$sret .= "<option value='".$val['codigoOperadora']."'>".$val['codigoOperadora']." - ".$val['nomeOperadora']."</option>\n";
	}
	$sret .= "</select> \n";
	$sret .= "<br>\n";

	return $sret;
}

function get_select_Valores($id, $ddd, $id_selected) {
	$id_operadoras_current = get_id_in_vector_form_codigoOperadora($id);

	$sret = "";

//	$sret .= "Em get_select_Valores (id_operadoras_current: ".$id_operadoras_current.", ddd: '$ddd')<br>";
//echo "<pre>".print_r($GLOBALS['operadoras_current'][$id_operadoras_current], true)."</pre>";
//echo "<pre>".print_r($GLOBALS['operadoras_current'][$id_operadoras_current]['valoresPorDDD'], true)."</pre>";
//echo "<pre>".print_r($GLOBALS['operadoras_current'][$id_operadoras_current]['valoresPorDDD'][$ddd], true)."</pre>";

	if ($id_operadoras_current >= 0 && $ddd!=""){
		$aValores = $GLOBALS['operadoras_current'][$id_operadoras_current]['valoresPorDDD'][$ddd];

		$sret .= "<select class='form-xl' id='planId' name='planId' onChange='do_change_value();'>\n"; 
		$sret .= "<option value='-1'>";
		$sret .= ((count($aValores['valoresFixos'])>0)?"Selecione o Valor":"Sem valores fixos");
		$sret .= "</option>\n";
		$i = 0;
		foreach($aValores['valoresFixos'] as $key => $val) {
			$sret .= "<option value='".($i++)."'>R$ ".($val['valor']/100)." (Bonus: R$ ".($val['valorBonus']/100).")</option>\n";
		}
		$sret .= "</select> \n";
		$sret .= "<br>\n";
//		$sret .= "[".number_format(($aValores['valorMinimo']/100), 2, ',', '.').", ".number_format(($aValores['valorMaximo']/100), 2, ',', '.')."]<br>\n";
//		$sret .= "<div id=\"bonus\">***</div>\n";
		if(!empty($aValores['valorMinimo'])&&!empty($aValores['valorMaximo'])) {
			$sret .= "<script type=\"text/javascript\">\n";
//			$sret .= "document.getElementById(\"labellivre\").innerHTML = \"Valor Livre\";\n";
//			$sret .= "document.getElementById(\"inputlivre\").innerHTML = \"R\$ <input id='planIdFlex' name='planIdFlex' class='form-xl w35' type='text' maxlength='2' value='' onclick='this.value=\'\';' />\";\n";	// onBlur='valida_valores(" . $aValores['valorMinimo'] . "," . $aValores['valorMaximo'].");'
		
//			$sret .= "alert('Go show');\n";
			$sret .= "\$('#labellivre').show();\n";
			$sret .= "\$('#inputlivre').show();\n";

			$sret .= "</script>\n";
		}
		else {
			$sret .= "<script type=\"text/javascript\">\n";
//			$sret .= "alert('Go hide 2');\n";
			$sret .= "\$('#labellivre').hide();\n";
			$sret .= "\$('#inputlivre').hide();\n";
			$sret .= "</script>\n";
		}
	} else {
		$sret .= "Consulta a valores retornou vazio<br>\n";
	}

	return $sret;

}

function get_Valor_Minimo($id, $ddd) {
	$valorMinimo = ((isset($GLOBALS['operadoras_current'][$id]['valoresPorDDD'][$ddd]['valorMinimo']) && ($GLOBALS['operadoras_current'][$id]['valoresPorDDD'][$ddd]['valorMinimo']>0))?$GLOBALS['operadoras_current'][$id]['valoresPorDDD'][$ddd]['valorMinimo']:0);
	return $valorMinimo;
}
function get_Valor_Maximo($id, $ddd) {
	$valorMaximo = ((isset($GLOBALS['operadoras_current'][$id]['valoresPorDDD'][$ddd]['valorMaximo']) && ($GLOBALS['operadoras_current'][$id]['valoresPorDDD'][$ddd]['valorMaximo']>0))?$GLOBALS['operadoras_current'][$id]['valoresPorDDD'][$ddd]['valorMaximo']:0);
	return $valorMaximo;
}
function get_ValoresFixos_array($id, $ddd) {
	$aValoresFixos = array();
	if(isset($GLOBALS['operadoras_current'][$id]['valoresPorDDD'][$ddd]['valoresFixos'])) {
		$aValoresFixos = $GLOBALS['operadoras_current'][$id]['valoresPorDDD'][$ddd]['valoresFixos'];
	}
	return $aValoresFixos;
}
function get_ValorFixo($id, $ddd, $idvalor) {
	$aValorFixo = 0;
	if(isset($GLOBALS['operadoras_current'][$id]['valoresPorDDD'][$ddd]['valoresFixos'])) {
		if(isset($GLOBALS['operadoras_current'][$id]['valoresPorDDD'][$ddd]['valoresFixos'][$idvalor])) {
			$aValorFixo = $GLOBALS['operadoras_current'][$id]['valoresPorDDD'][$ddd]['valoresFixos'][$idvalor]['valor'];
		}
	}
	return $aValorFixo;
}
function get_ValorFixoBonus($id, $ddd, $idvalor) {
	$aValorFixoBonus = 0;
	if(isset($GLOBALS['operadoras_current'][$id]['valoresPorDDD'][$ddd]['valoresFixos'])) {
		if(isset($GLOBALS['operadoras_current'][$id]['valoresPorDDD'][$ddd]['valoresFixos'][$idvalor])) {
			$aValorFixoBonus = $GLOBALS['operadoras_current'][$id]['valoresPorDDD'][$ddd]['valoresFixos'][$idvalor]['valorBonus'];
		}
	}
	return $aValorFixoBonus;
}

function valida_operadora($id) {
	$id_operadoras_current = get_id_in_vector_form_codigoOperadora($id);

//echo "Em valida_operadora(id: $id, id_operadoras_current: ".$id_operadoras_current."<br>";
//echo "<pre>".print_r($GLOBALS['operadoras_current'][$id_operadoras_current], true)."</pre>";

	if ($id_operadoras_current >= 0){
		$aValores = $GLOBALS['operadoras_current'][$id_operadoras_current];
//echo "<pre>".print_r($aValores, true)."</pre>";

		return true;
	} else {
		return false;
	}
}

function get_nome_operadora($id) {
	$id_operadoras_current = get_id_in_vector_form_codigoOperadora($id);
	$nome = "";
	if ($id_operadoras_current >= 0){
		$nome = $GLOBALS['operadoras_current'][$id_operadoras_current]['nomeOperadora'];
	}
	return $nome;
}

function get_versaoFilial_operadora($id, $ddd) {
	$id_operadoras_current = get_id_in_vector_form_codigoOperadora($id);
//echo "In get_versaoFilial_operadora($id, $ddd) -> $id_operadoras_current<br>";
	$versaoFilial = "";
	if ($id_operadoras_current >= 0){
		$versaoFilial = $GLOBALS['operadoras_current'][$id_operadoras_current]['valoresPorDDD'][$ddd]['versaoFilial'];
//echo "DUMP 1 <pre style='background-color:#FFFF99;color:blue'>".print_r($GLOBALS['operadoras_current'][$id_operadoras_current]['valoresPorDDD'][$ddd], true)."</pre>";
//echo "In get_versaoFilial_operadora($id, $ddd) -> $versaoFilial<br>";

	}
	return $versaoFilial;
}

function get_versaoOperadora_operadora($id, $ddd) {
	$id_operadoras_current = get_id_in_vector_form_codigoOperadora($id);
//echo "In get_versaoOperadora_operadora($id, $ddd) -> $id_operadoras_current<br>";
	$versaoOperadora = "";
	if ($id_operadoras_current >= 0){
		$versaoOperadora = $GLOBALS['operadoras_current'][$id_operadoras_current]['valoresPorDDD'][$ddd]['versaoOperadora'];

//echo "DUMP 2 <pre style='background-color:#FFFF99;color:blue'>".print_r($GLOBALS['operadoras_current'][$id_operadoras_current]['valoresPorDDD'][$ddd], true)."</pre>";
//echo "In get_versaoOperadora_operadora($id, $ddd) -> $versaoOperadora<br>";
	}
	return $versaoOperadora;
}


function get_codigoRede_operadora($id) {
	$id_operadoras_current = get_id_in_vector_form_codigoOperadora($id);
	$codigoRede = "";
	if ($id_operadoras_current >= 0){
		$codigoRede = $GLOBALS['operadoras_current'][$id_operadoras_current]['codigoRede'];
	}
	return $codigoRede;
}

function get_codigoProduto_operadora($id) {
	$id_operadoras_current = get_id_in_vector_form_codigoOperadora($id);
	$codigoProduto = "";
	if ($id_operadoras_current >= 0){
		$codigoProduto = $GLOBALS['operadoras_current'][$id_operadoras_current]['codigoProduto'];
	}
	return $codigoProduto;
}

function get_status_pedido($vg_id, &$recibo = null) {

	if(!$vg_id) {
		return -1;
	}
	$sql = "select * from tb_recarga_pedidos where rp_vg_id = $vg_id order by rp_data_inclusao desc limit 1";
//echo "get_status_pedido: $vg_id <br>$sql<br>\n";
	$rs = SQLexecuteQuery($sql);
	if(!$rs || pg_num_rows($rs) == 0) {
//echo "Nope<br>";
		echo "Nenhum produto encontrado ($sql).\n";
		return -2;
	} else {
//echo "Yeah<br>";
		$rs_row = pg_fetch_array($rs);
		$status = $rs_row['rp_status'];
///echo "status: '$status'<br>";
		if($status=="1") {
			// Pedido processado
			$recibo = $rs_row['rp_recibo'];
			return "1";
		} elseif($status=="N") {
			// Pedido recusado
			return "N";
		} elseif($status=="0") {
			// Pedido pendente de procesamento
			return "0";
		} else {
			// Status desconhecido
			return $status;
		}
	}
}

function verifica_data_rc($data) {
	$aux = $data;
	$tam = strlen($aux);
		if($tam < 10)
		{ return 0; }
		else
		{
				$bar1 = substr($aux,2,1);
				$bar2 = substr($aux,5,1);
					if(ord($bar1) != 47 || ord($bar2) != 47)
					{ return 0; }
					else
					{
						$dia = substr($aux,0,2); 
						for ($x = 1 ; $x <= strlen($dia) ; $x++)
						{
							$pos = substr($dia,$x-1,1);
							if(ord($pos) >= 48 && ord($pos) <= 57)
							{ $alerta = 0; }
							else
							{ $alerta = 1; break;}
						}							
								if($alerta == 1) 
								{ return 0; }
								else
								{
									$mes = substr($aux,3,2); 
									for ($x = 1 ; $x <= strlen($mes) ; $x++)
									{
										$pos = substr($mes,$x-1,1);
										if(ord($pos) >= 48 && ord($pos) <= 57)
										{ $alerta = 0; }
										else
										{ $alerta = 1; break;}							
									}
										
										if($alerta == 1) 
										{ return  0; }
										else
										{
											$ano = substr($aux,6,4); 
											for ($x = 1 ; $x <= strlen($ano) ; $x++)
											{
												$pos = substr($ano,$x-1,1);
												if(ord($pos) >= 48 && ord($pos) <= 57)
												{ $alerta = 0; }
												else
												{ $alerta = 1; break;}							
											}
											
											if($alerta == 1) 
											{ return  0; }
											else	
											{ 
												if($mes > 12 || $dia > 31)
												{ return 0; }
												else
												{									
													if ((($ano % 4) == 0 and ($ano % 100) != 0) or ($ano % 400) == 0)
														{ $bissexto = 1; }
													else 
														{ $bissexto = 0; }
													
													if($bissexto == 0)
													{
														if(($mes == 4 || $mes == 6 || $mes == 9 || $mes == 11) && ($dia > 30))
														{ return 0; }
														else
														{
															if($mes == 2 && $dia > 28) 
															{ return 0; }
															else
															{ return 1; }														
														}
													}
													if($bissexto == 1)
													{
														if(($mes == 4 || $mes == 6 || $mes == 9 || $mes == 11) && ($dia > 30))
														{ return 0; }
														else
														{
															if($mes == 2 && $dia > 29) 
															{ return 0; }
															else
															{ return 1; }
														}
													}													
												}
											}											
										}															
								}																				
					}																		
		}			
}

function formata_data_rc($data,$gravar)
{
	$mask = $data;
	if($gravar == 0)
	{
		$dia = substr($mask,8,2);
		$mes = substr($mask,5,2);
		$ano = substr($mask,0,4);
		$doc = $dia."/".$mes."/".$ano;
	}
	
	if($gravar == 1)
	{
		$dia = substr($mask,0,2);
		$mes = substr($mask,3,2);
		$ano = substr($mask,6,4);
		$doc = $ano."-".$mes."-".$dia;
	}
	return $doc;
}

function get_microtime_from_file() {
	$file = $GLOBALS['ARQUIVO_RC_MONITOR'];
	$sret = file_get_contents($file);

	$sret_from_file = substr($sret, 0, strpos($sret, ";")-1);
//echo "sret_from_file: ".$sret_from_file."\n";
	return $sret_from_file;
}

/*
usar: 
	wordwrap($str, $max_length, "\n", true);
function format_str_force_max_width($str, $cols) {
	$max_length = 40;
	$sret = "";
	$i = 0;
	$a_str = explode("\n", $str);
	foreach($a_str as $key => $val) {
		$str_processed = $val;
echo "<div style='background-color:#ffcc99'>".str_replace("\n", "<br>\n", $str_processed)."</div>";
		do {
			$i++;
			echo "($i) len: ".strlen($str_processed)." ($str_processed)<br>";
			$sret .= substr($str_processed, 0, $max_length-1)."\n";
			$str_processed = substr($str_processed, $max_length);
echo "<div style='background-color:#ffcc99'>".str_replace("\n", "<br>\n", $str_processed)."</div>";
		} while(strlen($str_processed)>0);
	}
	return $sret;
}
*/ 
?>