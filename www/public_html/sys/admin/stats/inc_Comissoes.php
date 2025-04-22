<?php

error_reporting(E_ALL ^ E_NOTICE); 
//ini_set("display_errors", 1); 

// Alíquota Imposto Publishers internacionais
$TAX = array(
		1	=>	10,
		2	=>	15,
		);

// Vinculando Imposto aos Publishers internacionais
$TAX_PUBLISHERS_INTERNATIONAL = array(
								'Axeso5'		=> 2,
								'Bilagames'		=> 1,
								'Ignitedgames'	=> 2,  
								'NDoors'		=> 2,  
								'GAMEIS'		=> 1, 
								'OGPlanet'		=> 2,
								'PayByCash'		=> 2,
								'Stardoll'		=> 2,
								'Webzen'		=> 1,
								'BigPoint'		=> 2,
								'Onnet'			=> 2,
								'PaymentWall'	=> 2,
								'SGInteractive'	=> 2,
								'Playwith'=> 2,
								'FHLGames'		=> 1,
								'CYBERSTEP'		=> 2,
								'Ankama'		=> 2,
								'Elextech'		=> 2,
								'G4Box'			=> 2,
                                                                'NTTGame'               => 2,
								);	

// Publishers com Detalhamento (POS - LAN - SITE)
$DETAILS = array(
		'GPotato',  
		'Vostu',
		'Kaizen', 
		'ONGAME',
		'HABBO HOTEL',
//		'Softnyx',
		'Skillab',
		);

// Comissão rede POS Prepag
$COMISSAO_POS = 12;

// Comissão Lans (min)
$COMISSAO_LANS_MIN = 10;

// Comissão Lans cartões (min)
$COMISSAO_LANS_CARTOES_MIN = 10;

// Comissão rede POS Ponto Certo
$COMISSAO_REDE_PONTO_CERTO = 11;

// Define Array de comissoes
$COMISSOES_BRUTAS = array(
		'M' => array(
			'ONGAME' => 16, 
			'HABBO HOTEL' => 18, 
			'MU ONLINE' => 15, //16, 
			'Kaizen' => 10, 
			'GAMEGOL' => 18, 
			'ACCLAIM' => 25, 
//			'NeoAct' => 22, 
			'HIVE' => 18, 
			'KOL' => 15, 
			'GAMEIS' => 22, 
			'Entwell' => 25,
			'Ticket-Surf' => 20,
			'Brancaleone' => 22,  
			'Escola 24hs' => 50,  
			'GPotato' => 14,  
			'NDoors' => 25,  
			'Ignitedgames' => 25,  
			'Webzen' => 23,
			'Vostu' => 18,
			'Stardoll' => 25,
			'Softnyx' => 25,
			'Cosmopax' => 19,
			'Onnet' => 25,
			'OGPlanet' => 23,
			'Axeso5' => 23,
			'Bilagames'	=> 23,
			'BigPoint'	=> 20,
			'PayByCash'		=> 23,
			'2Mundos'		=> 23,
			'Jolt'		=> 20,
			'Mindset'		=> 23,
			'E-Prepag Cash'	=> 100,
			'PaymentWall'	=> 25,
			'SGInteractive'	=> 15,
			'Playwith'=> 22,
			'FHLGames'		=> 23,
			'CYBERSTEP'		=> 18,
			'Ankama'		=> 20,
                        'Coolnex'               => 12,
			'Alawar'		=> 50,
			'77PB Entertain.'		=> 22,
			'Global Games'		=> 18,
			'Global Games 2'	=> 18,
			'Elextech'		=> 20,
			'G4Box'			=> 15,
                        'NTTGame'               => 23,
                        'Skillab'               => 18,
			),  
		'E' => array(
			'ONGAME' => 16, 
			'HABBO HOTEL' => 18, 
			'MU ONLINE' => 15, //16, 
			'Kaizen' => 10, 
			'GAMEGOL' => 18, 
			'ACCLAIM' => 25, 
//			'NeoAct' => 22, 
			'HIVE' => 18, 
			'KOL' => 15, 
			'GAMEIS' => 22, 
			'Entwell' => 25,
			'Ticket-Surf' => 20,
			'Brancaleone' => 22,  
			'Escola 24hs' => 50,  
			'GPotato' => 14,  
			'NDoors' => 25,  
			'Ignitedgames' => 25,  
			'Webzen' => 23,
			'Vostu' => 18,
			'Stardoll' => 25,
			'Softnyx' => 25,
			'Cosmopax' => 19,
			'Onnet' => 25,
			'OGPlanet' => 23,
			'Axeso5' => 23,
			'Bilagames'	=> 23,
			'BigPoint'	=> 20,
			'PayByCash' => 23,
			'2Mundos'		=> 23,
			'Jolt'		=> 20,
			'Mindset'		=> 23,
			'E-Prepag Cash'	=> 100,
			'PaymentWall'	=> 25,
			'SGInteractive'	=> 15,
			'Playwith'=> 22,
			'FHLGames'		=> 23,
			'CYBERSTEP'		=> 18,
			'Ankama'		=> 20,
                        'Coolnex'               => 12,
			'Alawar'		=> 50,
			'77PB Entertain.'		=> 22,
			'Global Games'		=> 18,
			'Global Games 2'	=> 18,
			'Elextech'		=> 20,
			'G4Box'			=> 15,
                        'NTTGame'               => 23,
                        'Skillab'               => 18,
			),  
		'L' => array(
			'ONGAME' => 16, 
			'HABBO HOTEL' => 18, 
			'MU ONLINE' => 18, 
			'Kaizen' => 16, 
			'GAMEGOL' => 18, 
			'ACCLAIM' => 25, 
//			'NeoAct' => 22, 
			'HIVE' => 18, 
			'KOL' => 0, 
			'GAMEIS' => 22, 
			'Entwell' => 25,
			'Ticket-Surf' => 20,
			'Brancaleone' => 22,  
			'Escola 24hs' => 50,  
			'GPotato' => 20,  
			'NDoors' => 25,  
			'Ignitedgames' => 25,  
			'Webzen' => 23,
			'Vostu' => 20,
			'Stardoll' => 25,
			'Softnyx' => 25,
			'Cosmopax' => 19,
			'Onnet' => 25,
			'OGPlanet' => 23,
			'Axeso5' => 23,
			'Bilagames'	=> 23,
			'BigPoint'	=> 20,
			'PayByCash' => 23,
			'2Mundos'		=> 23,
			'Jolt'		=> 20,
			'Mindset'		=> 23,
			'E-Prepag Cash'	=> 100,
			'PaymentWall'	=> 25,
			'SGInteractive'	=> 15,
			'Playwith'=> 22,
			'FHLGames'		=> 23,
			'CYBERSTEP'		=> 18,
			'Ankama'		=> 20,
                        'Coolnex'               => 12,
			'Alawar'		=> 50,
			'77PB Entertain.'		=> 22,
			'Global Games'		=> 18,
			'Global Games 2'	=> 18,
			'Elextech'		=> 20,
			'G4Box'			=> 15,
                        'NTTGame'               => 23,
                        'Skillab'               => 18,
			),  
		'C' => array(
			'ONGAME' => 30, 
			'HABBO HOTEL' => 20, 
			'MU ONLINE' => 23,	//25, 
			'Kaizen' => 16, 
			'GAMEGOL' => 23, 
			'ACCLAIM' => 0, 
//			'NeoAct' => 0, 
			'HIVE' => 18, 
			'KOL' => 0, 
			'GAMEIS' => 22, 
			'Entwell' => 25,
			'Ticket-Surf' => 0,
			'Brancaleone' => 22,  
			'Escola 24hs' => 50,  
			'GPotato' => 20,  
			'NDoors' => 25,  
			'Ignitedgames' => 25,  
			'Webzen' => 23,
			'Vostu' => 20,
			'Stardoll' => 30,
			'Softnyx' => 25,
			'Cosmopax' => 19,
			'Onnet' => 25,
			'OGPlanet' => 23,
			'Axeso5' => 23,
			'Bilagames'	=> 30,
			'BigPoint'	=> 30,
			'PayByCash' => 23,
			'2Mundos'		=> 23,
			'Jolt'		=> 20,
			'Mindset'		=> 23,
			'E-Prepag Cash'	=> 100,
			'PaymentWall'	=> 25,
			'SGInteractive'	=> 15,
			'Playwith'=> 22,
			'FHLGames'		=> 27,
			'CYBERSTEP'		=> 18,
			'Ankama'		=> 20,
                        'Coolnex'               => 12,
			'Alawar'		=> 50,
			'77PB Entertain.'		=> 22,
			'Global Games'		=> 18,
			'Global Games 2'	=> 18,
			'Elextech'		=> 20,
			'G4Box'			=> 20,
                        'NTTGame'               => 23,
                        'Skillab'               => 18,
			),  
		'P' => array(
			'ONGAME' => 16,
			'HABBO HOTEL' => 18,
			'MU ONLINE' => 17,	//19,
			'Kaizen' => 16,
			'GAMEGOL' => 18,
			'ACCLAIM' => 25,
//			'NeoAct' => 22,
			'HIVE' => 18,
			'KOL' => 15,
			'GAMEIS' => 22,
			'Entwell' => 25,
			'Ticket-Surf' => 25,
			'Brancaleone' => 22,  
			'Escola 24hs' => 50,  
			'GPotato' => 20,  
			'NDoors' => 25,  
			'Ignitedgames' => 25,  
			'Webzen' => 23,
			'Vostu' => 20,
			'Stardoll' => 25,
			'Softnyx' => 25,
			'Cosmopax' => 19,
			'Onnet' => 25,
			'OGPlanet' => 23,
			'Axeso5' => 23,
			'Bilagames'	=> 23,
			'BigPoint'	=> 20,
			'PayByCash' => 23,
			'2Mundos'		=> 23,
			'Jolt'		=> 20,
			'Mindset'		=> 23,
			'E-Prepag Cash'	=> 100,
			'PaymentWall'	=> 25,
			'SGInteractive'	=> 15,
			'Playwith'=> 22,
			'FHLGames'		=> 23,
			'CYBERSTEP'		=> 18,
			'Ankama'		=> 20,
                        'Coolnex'               => 12,
			'Alawar'		=> 50,
			'77PB Entertain.'		=> 22,
			'Global Games'		=> 18,
			'Global Games 2'	=> 18,
			'Elextech'		=> 20,
			'G4Box'			=> 15,
                        'NTTGame'               => 23,
                        'Skillab'               => 18, 
			)
					);

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

$COMISSOES_BRUTAS_PUBLISHER_M_E = Array(
	'9Dragons' => 'ACCLAIM',
	'Bots' => 'ACCLAIM',
	'Dance' => 'ACCLAIM',
	'Ponystars' => 'ACCLAIM',
	'My Diva Doll' => 'ACCLAIM',
	'Spellborn' => 'ACCLAIM',
	'Trackmania Nations' => 'ACCLAIM',
	'Tribal Nations' => 'ACCLAIM',
	'Muniz Online' => 'ACCLAIM',
	'Prize Potato' => 'ACCLAIM',
	'Age of Lore' => 'ACCLAIM',
	'TrackMania - United' => 'ACCLAIM', 
	'2Moons' => 'ACCLAIM',

	'AstroN' => 'GAMEIS',
	'Carom 3D' => 'GAMEIS',
	'Fantasy Masters' => 'GAMEIS',

	'GameGol' => 'GAMEGOL',
	
	'GetAmped' => 'HIVE',
	'Tako Online' => 'HIVE',
	
	'Gunbound' => 'ONGAME',
	'WYD' => 'ONGAME',
	'Hero Online' => 'ONGAME',
	'Metin2' => 'ONGAME',
	'Asda Story' => 'ONGAME',
	'Kart N\' Crazy' => 'ONGAME',
	'Aika' => 'ONGAME',
	'Point Blank' => 'ONGAME',
	'Mercury Red' => 'ONGAME',
	
	'Habbo Hotel' => 'HABBO HOTEL',
	
	'MU Online' => 'MU ONLINE',
	'GamersFirst' => 'MU ONLINE',
	
	'Nostale' => 'Entwell',
	
	'Priston Tale' => 'Kaizen',
	
	'Knight Online' => 'KOL',
	
	'Urban-Rivals' => 'Ticket-Surf',
	'Ticket-Surf' => 'Ticket-Surf',
	
	'Migux' => 'Brancaleone', 

	'Ultimate Game Card' => 'PayByCash', 

	'Flyff' => 'GPotato', 
	'Rappelz' => 'GPotato', 

	'Atlantica' => 'NDoors', 
	'Luminary: Rise of the GoonZu' => 'NDoors', 
	'WonderKing' => 'NDoors', 

	'Mu Online' => 'Webzen',	
	'Archlord' => 'Webzen',	
	'S.U.N' => 'Webzen',
	'C9 - Continent of Ninth' => 'Webzen',
	'Arctic Combat' => 'Webzen',

	'Stardoll' => 'Stardoll',

	'Cosmopax' => 'Cosmopax',

	'Wonderking' => 'Ignitedgames',
	'Atlantica Brazil' => 'Ignitedgames',
	'DarkEden' => 'Ignitedgames',
	'Rosh Online' => 'Ignitedgames',
	'Atlantica Espanhol' => 'Ignitedgames',

	'Legend of Edda' => 'Onnet',
	'GamesCampus' => 'Onnet',
	'Scarlet Legacy' => 'Onnet',
	'Heroes in the Sky' => 'Onnet',
	'9 Dragons' => 'Onnet',
	'Asda 2' => 'Onnet',
	'MLB Dugout Heroes' => 'Onnet',
	'Drift City' => 'Onnet',
	'ShotOnline' => 'Onnet',

	'Gunbound_1' => 'Softnyx',
	'Wolfteam' => 'Softnyx',
	'Rakion' => 'Softnyx',
	'Love Ritmo' => 'Softnyx',

	'Joga Craque' => 'Vostu',	
	'MiniFazenda' => 'Vostu',	
	'CaféMania' => 'Vostu',	
	'Poker' => 'Vostu',	
	'PetMania' => 'Vostu',
	'Rede do Crime' => 'Vostu',
	'MegaCity' => 'Vostu',
	'MegaCity - Orkut' => 'Vostu',
	'Golmania' => 'Vostu',

	'Karos Online' => 'Axeso5',
	'Axesocash' => 'Axeso5',
	'Luna Plus' => 'Axeso5',
	'HoN' => 'Axeso5',

	'Championship Manager: Rivals' => 'Jolt', 

	'2Mundos' => '2Mundos', 

	'Race Town - Orkut' => 'Mindset', 

	'Red Stone' => 'OGPlanet',
	'La Tale' => 'OGPlanet',
	'Zone 4' => 'OGPlanet',
	'OGPlanet' => 'OGPlanet',
	'Lost Saga' => 'OGPlanet',
	'Rumble Fighter' => 'OGPlanet',
	'SDGO' => 'OGPlanet',

	'E-PREPAG Cash' => 'E-Prepag Cash',

	'Apoio Escolar 24Horas' => 'Escola 24hs',

	'Alawar' => 'Alawar',

	'R.O.H.A.N.: BLOOD FEUD' => 'Playwith',
	'SEAL ONLINE' => 'Playwith',
	'K.O.S. - SECRET OPERATIONS' => 'Playwith',

	'Mix Master' => '77PB Entertain.',

	'Kaybo' => 'FHLGames',

	'Conquest' => 'Global Games',

	'Dofus' => 'Ankama',
	'Wakfu' => 'Ankama',

	'DDTank' => 'Elextech',
	'IK' => 'Elextech',
	'Odisseia' => 'Elextech',
	'Plantas Loucas' => 'Elextech',
	'Sociedade Poquer' => 'Elextech',
	'César' => 'Elextech',
	'Rei e Conquistador' => 'Elextech',
	'NAMO' => 'Elextech',
	'Magnata de Negócios' => 'Elextech',
	'Nindou' => 'Elextech',
	'337 Cash' => 'Elextech',

	);

//	'Pang OnLine' ,    ??
/*
	foreach ($COMISSOES_BRUTAS_PUBLISHER_M_E as $nome_produto => $opr_nome){ 
		echo "'".$nome_produto."' => '".$opr_nome."'<br>";
	}
*/
	// Cria array com códigos de operadoras
	$OPR_CODIGOS = Array();		// opr_codigo => opr_nome
	$OPR_NOMES = Array();		// opr_nome => opr_codigo 
	$sql  = "select * from operadoras ope order by opr_codigo "; //"where not opr_codigo=78;";
//echo "$sql<br>";
	$rs_operadoras = SQLexecuteQuery($sql);
	if($rs_operadoras) {
		while($rs_operadoras_row = pg_fetch_array($rs_operadoras)){ 
			$OPR_CODIGOS[$rs_operadoras_row['opr_codigo']] = $rs_operadoras_row['opr_nome']; 
			$OPR_NOMES[$rs_operadoras_row['opr_nome']] = $rs_operadoras_row['opr_codigo']; 
//echo $rs_operadoras_row['opr_codigo']." - '".$rs_operadoras_row['opr_nome']."'<br>";
		} 
	}
//echo "<hr>";

	// Cria array com comissões por códigos de operadora

	$COMISSOES_BRUTAS_BY_OPR_CODIGO = Array();		// opr_codigo => comissão
	foreach ($COMISSOES_BRUTAS as $canal => $arr){ 
//echo "'$canal' <br>";
//echo "<pre>";
//print_r($arr);
//echo "</pre>";
//		$COMISSOES_BRUTAS_BY_OPR_CODIGO[$canal] = $arr; 
		foreach ($arr as $key => $val){ 
//echo "opr_codigo: '".$OPR_NOMES[$key]."', ID: ".$key." = ";
//echo "Canal = ".$canal." opr_codigo: '".$OPR_NOMES[$key]."', valor: ".$val." , key = ".$key."<br>";
			$COMISSOES_BRUTAS_BY_OPR_CODIGO[$canal][$OPR_NOMES[$key]] = $val; 
/*
if($canal='M') {
	if(!$OPR_NOMES[$key]) echo "*** - ";
echo "canal: '".$canal."', opr: $key, val: ".$val." (".$COMISSOES_BRUTAS_BY_OPR_CODIGO[$canal][$OPR_NOMES[$key]].")<br>";
}
*/
		}
//echo "<hr>";
	}

/*
echo "<hr>";
// Lista todos os elementos do Array
foreach ($COMISSOES_BRUTAS_BY_OPR_CODIGO as $ComissaoID => $ComissaoArray){ 
	echo "<hr>'$ComissaoID'<br>";
	foreach ($ComissaoArray as $ComissaoOperadoraID => $ComissaoValor){ 
		echo "&nbsp;&nbsp;".$ComissaoOperadoraID." -> ".$ComissaoValor."%<br>"; 
	} 
}

	echo "<hr>'M'<br>";
	foreach ($COMISSOES_BRUTAS_BY_OPR_CODIGO['M'] as $ComissaoOperadoraID => $ComissaoValor){ 
		echo "&nbsp;&nbsp;".$ComissaoOperadoraID." -> ".$ComissaoValor."%<br>"; 
	} 
*/
/*
echo "<hr>";
foreach ($OPR_CODIGOS as $key => $val){ 
	echo "&nbsp;&nbsp;'".$key." '-> '".$val."'<br>"; 
}
echo "<hr>";
foreach ($OPR_NOMES as $key => $val){ 
	echo "&nbsp;&nbsp;'".$key." '-> '".$val."'<br>"; 
}
echo "<hr>";
*/	
/*
	// Lista todos os elementos do Array
	foreach ($COMISSOES_BRUTAS as $ComissaoID => $ComissaoArray){ 
		echo "<hr>'$ComissaoID'<br>";
		foreach ($ComissaoArray as $ComissaoOperadora => $ComissaoValor){ 
			echo "&nbsp;&nbsp;".$ComissaoOperadora." -> ".$ComissaoValor."%<br>"; 
		} 
	}
*/
/*
	// Lista cada canal com todas as Operadoras
	foreach ($COMISSOES_BRUTAS as $ComissaoID => $ComissaoArray){ 
		echo "<hr>'$ComissaoID' =>";
		foreach ($ComissaoArray as $ComissaoOperadora => $ComissaoValor){ 
			echo "'$ComissaoOperadora' (".getComissaoValue($ComissaoID, $ComissaoOperadora)."%), ";
		}
	}
*/
//	echo "<hr>'E', 'ONGAME' (".getComissaoValue("E", "ONGAME")."%)<br>";
//	echo "<hr>IsArray(".is_array($COMISSOES_BRUTAS["E"]).")<br>";
//	echo "<hr>Value: ".$COMISSOES_BRUTAS['E']['KOL']."<br>";
	
	function getComissaoValue($canal, $operadoraNome) {
		global $COMISSOES_BRUTAS;
//		echo "<hr>(Values: '".$canal."' => '".$operadoraNome."' = {".$COMISSOES_BRUTAS[$canal][$operadoraNome]."%})<br>";
		return($COMISSOES_BRUTAS[$canal][$operadoraNome]);
	}


// ==========================================================================
function get_sql_comissao($dd_operadora_nome, $smode) {
	global $COMISSOES_BRUTAS_PUBLISHER_M_E, $COMISSAO_POS, $COMISSAO_LANS_MIN, $COMISSAO_LANS_CARTOES_MIN, $COMISSOES_BRUTAS, $OPR_CODIGOS;

	$sql = "";

	switch($smode) {
		case "P":
			$sql .= " (case when ve_jogo='HB' then (".$COMISSOES_BRUTAS['P']['HABBO HOTEL']."./100) when ve_jogo='MU' then (".$COMISSOES_BRUTAS['P']['MU ONLINE']."./100) when ve_jogo='OG' then (".$COMISSOES_BRUTAS['P']['ONGAME']."./100) end) \n";
			break;
		case "M":		
		case "E":		
			$sql .= " (\n";
			$sql .= " case \n";
//			foreach ($COMISSOES_BRUTAS_PUBLISHER_M_E as $NomeProduto => $Publisher){ 
//				$sql .= " when vgm_nome_produto='$NomeProduto' then (".$COMISSOES_BRUTAS['E'][$Publisher]."./100) ";
//			}
			foreach ($OPR_CODIGOS as $opr_codigo => $opr_nome){ 
				$comiss = $COMISSOES_BRUTAS['M'][$opr_nome];			// "M" = "E" here (ok?)
	//echo $opr_nome." -> ".$comiss."<br>";
				if(!$comiss) $comiss = "0";
				$sql .= " when vgm.vgm_opr_codigo=$opr_codigo then (".$comiss."./100) \n";
			}
			$sql .= " end) \n";
			break;
		case "L":		
			$sql .= " (\n";
			$sql .= " case \n";
			foreach ($OPR_CODIGOS as $opr_codigo => $opr_nome){ 
				$comiss = $COMISSOES_BRUTAS['L'][$opr_nome];
	//echo $opr_nome." -> ".$comiss."<br>";
				if(!$comiss) $comiss = "0";
				$sql .= " when vgm.vgm_opr_codigo=$opr_codigo then (".$comiss."./100) \n";
			}
			$sql .= " end) \n";
			break;
		case "C":		
			if($dd_operadora_nome=="ONGAME") {
				$sql .= " (".$COMISSOES_BRUTAS['C']['ONGAME']."./100.) \n";
			} else {
				$sql .= " (0) \n";
			}
			break;
	}

	return $sql;
}

?>