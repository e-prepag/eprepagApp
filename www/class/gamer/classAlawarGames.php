<?php

class AlawarGames {

	
	public function __construct() { }
	
	/* Monta o campo <select> com a lista de jogos validos */
	public function createComboBox($id_jogo) {
	
		$comboBoxGames  = '<select name="gamesAlawar" id="gamesAlawar">'; 
		$comboBoxGames .= '	<option value="-1"'.(($id_jogo==-1 || $id_jogo==0 || !$id_jogo)?" selected":"").'> Escolha o ID do Jogo </option>';
	
		$filtro['pag_status'] = 1;
		$orderBy = "pag_name";
		$listAllGames = $this->getGamesBy($filtro, $orderBy);
			
		foreach ($listAllGames as $gamesID => $gamesItems) {			
			$comboBoxGames .= '<option value="'.$gamesID.'"'.(($id_jogo==$gamesID)?" selected":"").'>'.$gamesID.' - '.$gamesItems['pag_name'].'</option>'."\n";
		}	
		$comboBoxGames .= '</select>';
	
		return $comboBoxGames;
	}	
		
	public function loadListFromAlawar() {
		
		try {
		
			/* Cria Mensagem para Registrar o Tempo de Execucao do Script */
			$msgImportTask  = "";
			$msgImportTask .= "Inicio -> ".date("d/m/Y - H:i:s")."\n";
				
			$dom = new DOMDocument;
					
			/* Carrega o arquivo XML com lista de Games e seus IDs. No caso de erro, dispara uma Exception que serao gravada em arquivo Texto */
			$gameIdByXML = @file_get_contents(URL_LOAD_GAME_LIST_ALAWAR);
gravaLog_AlawarXML($gameIdByXML);

			if (!$gameIdByXML) {
				throw new Exception("Erro ao tentar conectar na Alawar para obter arquivo XML!");
			}
		
			/* Carrega arquivo XML com os Games, no caso de erro, dispara uma Exception que serao gravada em arquivo Texto */
			if(!@$dom->loadXML($gameIdByXML)) {
				throw new Exception("Erro ao tentar carregar arquivo XML!");
			}
		
			@$gameContentXml = simplexml_import_dom($dom);
		
			$alawarItems = $gameContentXml->xpath("//Catalog[@Code='casualpcgames']//Items/Item");
					
			$listOfCasualGamesFromXML = array();
			
			foreach ($alawarItems as $itAl) {
		
				$jogoCasualProperties = array();
				
				/* Pega o ID do Jogo do node @attributes */
				$gameID = each($itAl);
				$jogoCasualProperties['game_id'] = $gameID['value']['ID'];
				
				/* O dados recebidos chegam em UTF-8, e precisamos grava-los em ISO-8859-1 (PgSQL) */
				$jogoCasualProperties['game_name'] = iconv("UTF-8", "ISO-8859-1", trim($itAl->Name));	
				$jogoCasualProperties['symbol_code'] = (string)$itAl->Properties->Property[0];
				$jogoCasualProperties['game_icon'] = (string)$itAl->Images->Image[2];
				$jogoCasualProperties['game_description'] = iconv("UTF-8", "ISO-8859-1", trim((string)$itAl->Properties->Property[6]));

				/* Carrega a lista de IDs dos Games no Array */
				$listOfCasualGamesFromXML[$jogoCasualProperties['game_id']] = $jogoCasualProperties;
								
				$filtroCasual['pag_id'] = $jogoCasualProperties['game_id'];				
				$listOfGamesAlawar = $this->getGamesBy($filtroCasual, $orderBy);				
											
				/* Verifica se o Game ja foi inserido na base de dados, e qual o seu status */
				if(array_key_exists($jogoCasualProperties['game_id'], $listOfGamesAlawar)) {
echo "VERIFY UPDATE ".$jogoCasualProperties['game_id']."\n";
					/* Se Game existe e estao desativado, eh ativado novamente, pois veio no XML */
					if ($listOfGamesAlawar[$jogoCasualProperties['game_id']]['pag_status'] == 0) {
						$listOfCasualGamesFromXML[$jogoCasualProperties['game_id']]['game_status'] = 1;
						$this->update($listOfCasualGamesFromXML[$jogoCasualProperties['game_id']]);
					}
				}
				else {
echo "VERIFY INSERT ".$jogoCasualProperties['game_id']."\n";
					$listOfCasualGamesFromXML[$jogoCasualProperties['game_id']]['game_status'] = 1;
					$this->save($listOfCasualGamesFromXML[$jogoCasualProperties['game_id']]);
				}				
			}						

			/** Verifica se existe algum jogo da base de dados que nao existe no XML, e que precisara ser desativado **/
			 
			$filtroCasualGame['pag_online_game'] = ALAWAR_GAME_DISABLE;
			$listAllGames = $this->getGamesBy($filtroCasualGame, $orderBy);
				
			foreach ($listAllGames as $gidBD => $alGame) {				
				if(!array_key_exists($gidBD, $listOfCasualGamesFromXML)) {					
					$this->disable($gidBD);											
					$this->logEvents("GAME_DISABLE_BY_ALAWAR", "", "", "Jogo Offline Desativado na lista da Alawar : (ID=".$gidBD.",NAME='".$alGame['pag_name']."',STATUS=0)");
				}
			}			
				
			/* Processa os Jogos OnLine */
						
			$alawarOnlineItems = $gameContentXml->xpath("//Catalog[@Code='onlinegames']//Items/Item");

			$listOfOnlineGamesFromXML = array();
				
			foreach ($alawarOnlineItems as $indice => $jogosOnline) { 
				
				$jogoOnlineProperties = array();
								
				/* Pega o ID do Jogo do node @attributes */
				$gameOnlineID = each($jogosOnline);
				$jogoOnlineProperties['game_id_online'] = $gameOnlineID['value']['ID'];  
				
				/* Pega do node Properties as propriedades especificas dos jogos Online */
				$gidGameRelated = $jogosOnline->xpath("RelatedItems//RelatedItemCatalog[@Code='casualpcgames']//RelatedItem");

				/* Pega o ID do Jogo Offline Relacionado ao Jogo Online */
				$gameOnlineID = each($gidGameRelated);
				$jogoOnlineProperties['game_id_related'] = (string)$gameOnlineID['value']['ID'];				
				$jogoOnlineProperties['game_name'] = iconv("UTF-8", "ISO-8859-1", trim($jogosOnline->Name));				
				
				foreach ($jogosOnline->Properties as $prop) {									
					$jogoOnlineProperties['symbol_code'] = (string)$prop->Property[0];
					$jogoOnlineProperties['page_url'] = (string)$prop->Property[9];					
					$jogoOnlineProperties['width'] = (string)$prop->Property[5];
					$jogoOnlineProperties['height'] = (string)$prop->Property[6];
					$jogoOnlineProperties['embed'] = (string)$prop->Property[10];
					$jogoOnlineProperties['swf_width'] = (string)$prop->Property[7];
					$jogoOnlineProperties['swf_height'] = (string)$prop->Property[8];						
					$jogoOnlineProperties['game_icon'] = (string)$jogosOnline->Images->Image[2];
					$jogoOnlineProperties['game_description'] = iconv("UTF-8", "ISO-8859-1", trim((string)$prop->Property[3]));
				}								
				
				/* Carrega a lista de IDs dos Games no Array */
				$listOfOnlineGamesFromXML[$jogoOnlineProperties['game_id_online']] = $jogoOnlineProperties;
				
				$filtroOnLine['pag_id'] = $jogoOnlineProperties['game_id_online'];
				$listOfGamesOnlineAlawar = $this->getGamesBy($filtroOnLine, $orderBy);
				
				/* Verifica se o Game Online ja foi inserido na base de dados, e qual o seu status */
				if(array_key_exists($jogoOnlineProperties['game_id_online'], $listOfGamesOnlineAlawar)) {
				
					/* Se Game Online existe e esta desativado, eh ativado novamente, pois veio no XML */
					if ($listOfGamesOnlineAlawar[$jogoOnlineProperties['game_id_online']]['pag_status'] == 0) {
						$listOfOnlineGamesFromXML[$jogoOnlineProperties['game_id_online']]['game_status'] = 1;
						$this->update($listOfOnlineGamesFromXML[$jogoOnlineProperties['game_id_online']]);
					}
				}
				else {
					$listOfOnlineGamesFromXML[$jogoOnlineProperties['game_id_online']]['game_status'] = 1;
					$this->save($listOfOnlineGamesFromXML[$jogoOnlineProperties['game_id_online']]);
				}															
			}							
			
			/** Verifica se existe algum jogo online da base de dados que nao existe no XML, e que precisara ser desativado **/			 
			$filtroOnlineGame['pag_online_game'] = ALAWAR_GAME_ENABLE;
			$listAllGames = $this->getGamesBy($filtroOnlineGame, $orderBy);
			
			foreach ($listAllGames as $gidBD => $alGame) {				
				if(!array_key_exists($gidBD, $listOfOnlineGamesFromXML)) {					
					$this->disable($gidBD);											
					$this->logEvents("GAME_DISABLE_BY_ALAWAR", "", "", "Jogo Online Desativado na lista da Alawar : (ID=".$gidBD.",NAME='".$alGame['pag_name']."',STATUS=0)");
				}
			}			
			
			/* Registra Tempo de Execucao do Script */
			$msgImportTask .= "Fim.. -> ".date("d/m/Y - H:i:s");
			$this->logEvents("GAME_IMPORT_TASK", "", "", $msgImportTask);				
				
		} catch (Exception $e) {
					
			$log   = "TIPO LOG -> *ERROR* \n";
			$log  .= "SCRIPT   -> ".$e->getFile()."\n";
			$log  .= "LINHA    -> ".$e->getLine()."\n";
			$log  .= "MENSAGEM -> ".$e->getMessage()."\n";		
			$this->logEvents("ERROR", $e->getFile(), $e->getLine(), $e->getMessage());
		}	
	}
	
	/* Obtem a lista de todos os Games pelo seu ID */
	public function getGamesBy($filtro = "", $orderBy = "", $limitQuery = 0, $offSetQuery = 0) {

//echo "Em getGamesBy\n".print_r($filtro, true)."\n";
		$sql  = "SELECT 
						to_char(pag_data_inclusao, 'dd/mm/yyyy') as pag_data_inclusao_format, 
						to_char(pag_data_alteracao, 'dd/mm/yyyy') as pag_data_alteracao_format, 
						* 
				 FROM 
						pins_alawar_games 
				 WHERE 1=1 \n";
		
		if(!is_null($filtro) && $filtro != ""){
			$sql .= " AND (" . (is_null($filtro['pag_id'])?1:0);
			$sql .= "=1 OR pag_id = " . SQLaddFields($filtro['pag_id'], "") . ")\n";				
		
			$sql .= " AND (" . (is_null($filtro['pag_status'])?1:0);
			$sql .= "=1 OR pag_status = " . SQLaddFields(($filtro['pag_status']?1:0), "") . ")\n";

			$sql .= " AND (" . (is_null($filtro['pag_symbol_code'])?1:0);
			$sql .= "=1 OR pag_symbol_code LIKE '%" . SQLaddFields($filtro['pag_symbol_code'], "r") . "%')\n";
		
			$sql .= " AND (" . (is_null($filtro['pag_online_game'])?1:0);
			$sql .= "=1 OR pag_online_game = " . SQLaddFields($filtro['pag_online_game']?1:0, "") . ")\n";
		
			$sql .= " AND (" . (is_null($filtro['pag_ug_id_related'])?1:0);
			$sql .= "=1 OR pag_ug_id_related = " . SQLaddFields($filtro['pag_ug_id_related'], "") . ")\n";
		
			$sql .= " AND (" . (is_null($filtro['pag_name'])?1:0);
			$sql .= "=1 OR UPPER(pag_name) LIKE '%" . SQLaddFields(strtoupper($filtro['pag_name']), "r") . "%')\n";			
		
			/* Data de Inclusao */
			if ($filtro['pag_data_inclusao_ini'] && $filtro['pag_data_inclusao_fim']) {
				$filtro['pag_data_inclusao_ini'] = formata_data_ts($filtro['pag_data_inclusao_ini'] . " 00:00:00", 2, true, true);
				$filtro['pag_data_inclusao_fim'] = formata_data_ts($filtro['pag_data_inclusao_fim'] . " 23:59:59", 2, true, true);
				
				$sql .= " AND (pag_data_inclusao between " . SQLaddFields($filtro['pag_data_inclusao_ini'], "s") . " and " . SQLaddFields($filtro['pag_data_inclusao_fim'], "s") . ")\n";
			}
		
			/* Data de Alteracao */
			if($filtro['pag_data_alteracao_ini'] && $filtro['pag_data_alteracao_fim']) {
				$filtro['pag_data_alteracao_ini'] = formata_data_ts($filtro['pag_data_alteracao_ini'] . " 00:00:00", 2, true, true);
				$filtro['pag_data_alteracao_fim'] = formata_data_ts($filtro['pag_data_alteracao_fim'] . " 23:59:59", 2, true, true);
					
				$sql .= " AND (pag_data_alteracao between " . SQLaddFields($filtro['pag_data_alteracao_ini'], "s") . " and " . SQLaddFields($filtro['pag_data_alteracao_fim'], "s") . ")\n";
			}
		}
		if(!is_null($orderBy) && $orderBy != "") $sql .= " ORDER BY " . $orderBy;
		if(!is_null($limitQuery) && $limitQuery != 0) $sql .= " LIMIT " . $limitQuery;
		if(!is_null($offSetQuery) && $offSetQuery != 0) $sql .= " OFFSET " . $offSetQuery;
				
		//echo $sql;
//echo "Em getGamesBy\nSQL: $sql \n";
		$rs = SQLexecuteQuery($sql);
				
		$listOfGames = array();
		
		if ($rs) {
			while ($result = pg_fetch_assoc($rs)) {
				$listOfGames[$result['pag_id']] = array('pag_name' => $result['pag_name'], 
														'pag_symbol_code' => $result['pag_symbol_code'],
														'pag_status' => $result['pag_status'],
														'pag_online_game' => $result['pag_online_game'],
														'pag_ug_id_related' => $result['pag_ug_id_related'],
														'pag_icon' => $result['pag_icon'],
														'pag_data_inclusao' => $result['pag_data_inclusao_format'],
														'pag_data_alteracao' => $result['pag_data_alteracao_format'],
														'pag_online_game_page_url' => $result['pag_online_game_page_url'],
														'pag_online_game_width' => $result['pag_online_game_width'],
														'pag_online_game_height' => $result['pag_online_game_height'],
														'pag_online_embed' => $result['pag_online_embed'],
														'pag_online_game_swf_width' => $result['pag_online_game_swf_width'],
														'pag_online_game_swf_height' => $result['pag_online_game_swf_height'],
														'pag_description' => $result['pag_description']);				
			}		
		}

//echo "Em getGamesBy result\n".print_r($listOfGames, true)."\n";
		return $listOfGames;
	}
	
	
	/* Insere um Game da Base de Dados */
	public function save($gameData) {		
		
		$ret = false;
		
		try {			
			$gameID = $gameData['game_id'] ? $gameData['game_id'] : $gameData['game_id_online']; 
			$gameSymbolCode = $gameData['symbol_code'];
			$gameName = $gameData['game_name'];
			$gameDescription = $gameData['game_description'];
			$gameStatus = $gameData['game_status'] ? 1 : 0;
			$gameOnline = $gameData['game_id_online'] ? 1 : 0;
			$gameRelatedID = $gameData['game_id_related'] ? $gameData['game_id_related'] : "DEFAULT";
			$gameIcon = $gameData['game_icon'];
			$gameDataInclusao = "CURRENT_TIMESTAMP";
			$gameDataAlteracao = "CURRENT_TIMESTAMP";
			$gameOnlinePageURL = $gameData['page_url'];
			$gameWidth = $gameData['width'] ? $gameData['width'] : "DEFAULT";
			$gameHeight = $gameData['height'] ? $gameData['height'] : "DEFAULT";
			$gameOnlineEmbed = $gameData['embed'];
			$gameSwfWidth = $gameData['swf_width'] ? $gameData['swf_width'] : "DEFAULT";
			$gameSwfHeight = $gameData['swf_height'] ? $gameData['swf_height'] : "DEFAULT";
				
			$sql  = "INSERT INTO pins_alawar_games 
						(pag_id, pag_symbol_code, pag_name, pag_status, pag_online_game, pag_ug_id_related, pag_icon, 
						 pag_data_inclusao, pag_data_alteracao, pag_online_game_page_url, pag_online_game_width, pag_online_game_height,
						 pag_online_embed, pag_online_game_swf_width, pag_online_game_swf_height, pag_description) 
					VALUES (";
			$sql .= SQLaddFields($gameID, ""). ",";
			$sql .= SQLaddFields($gameSymbolCode, "s"). ",";
			$sql .= SQLaddFields($gameName, "s"). ",";
			$sql .= SQLaddFields($gameStatus, ""). ",";
			$sql .= SQLaddFields($gameOnline, ""). ",";
			$sql .= SQLaddFields($gameRelatedID, ""). ",";
			$sql .= SQLaddFields($gameIcon, "s"). ",";
			$sql .= SQLaddFields($gameDataInclusao, ""). ",";
			$sql .= SQLaddFields($gameDataAlteracao, ""). ",";
			$sql .= SQLaddFields($gameOnlinePageURL, "s"). ",";
			$sql .= SQLaddFields($gameWidth, ""). ",";
			$sql .= SQLaddFields($gameHeight, ""). ",";
			$sql .= SQLaddFields($gameOnlineEmbed, "s"). ",";
			$sql .= SQLaddFields($gameSwfWidth, ""). ",";
			$sql .= SQLaddFields($gameSwfHeight, ""). ",";
			$sql .= SQLaddFields($gameDescription, "s"). ")";
			$rs   = SQLexecuteQuery($sql);		
			
			if($rs) {
				$ret = true;
				$this->logEvents("OK", "", "", "Registro Inserido com Sucesso : (ID=".$gameID.",NAME='".$gameName."',STATUS=".$gameStatus.")", $sql);				
			}
			else {
				throw new Exception("Erro ao tentar inserir um jogo alawar na base de dados: (".pg_errormessage().")");
			}	
						
		} catch (Exception $e) {			
			$this->logEvents("ERROR", $e->getFile(), $e->getLine(), $e->getMessage(), $sql);
		}
		
		return $ret;
	}
	
	
	/* Atualiza um Game da Base de Dados */
	public function update($gameData) {
					
		$gameID = $gameData['game_id'] ? $gameData['game_id'] : $gameData['game_id_online'];
		$gameSymbolCode = $gameData['symbol_code'];
		$gameName = $gameData['game_name'];
		$gameDescription = $gameData['game_description'];
		$gameStatus = $gameData['game_status'] ? 1 : 0;
		$gameOnline = $gameData['game_id_online'] ? 1 : 0;
		$gameRelatedID = $gameData['game_id_related'] ? $gameData['game_id_related'] : "DEFAULT";
		$gameIcon = $gameData['game_icon'];
		$gameDataAlteracao = "CURRENT_TIMESTAMP";
		$gameOnlinePageURL = $gameData['page_url'];
		$gameWidth = $gameData['width'] ? $gameData['width'] : "DEFAULT";
		$gameHeight = $gameData['height'] ? $gameData['height'] : "DEFAULT";
		$gameOnlineEmbed = $gameData['embed'];
		$gameSwfWidth = $gameData['swf_width'] ? $gameData['swf_width'] : "DEFAULT";
		$gameSwfHeight = $gameData['swf_height'] ? $gameData['swf_height'] : "DEFAULT";

		$ret = false;
	
		try {
			$sql  = "UPDATE pins_alawar_games set ";						
			$sql .= "	pag_id=".SQLaddFields($gameID, "");
			
			if ($gameName)  { $sql .= ", pag_name=".SQLaddFields($gameName, "s"); }			
			if ($gameSymbolCode) { $sql .= ", pag_symbol_code=". SQLaddFields($gameSymbolCode, "s"); }
			if ($gameStatus) { $sql .= ", pag_status=". SQLaddFields($gameStatus, ""); }
			if ($gameOnline) { $sql .= ", pag_online_game=". SQLaddFields($gameOnline, ""); }
			if ($gameRelatedID)	{ $sql .= ", pag_ug_id_related=". SQLaddFields($gameRelatedID, ""); }
			if ($gameOnlinePageURL)	{ $sql .= ", pag_online_game_page_url=". SQLaddFields($gameOnlinePageURL, "s"); }
			if ($gameWidth)	{ $sql .= ", pag_online_game_width=". SQLaddFields($gameWidth, ""); } 
			if ($gameHeight) { $sql .= ", pag_online_game_height=". SQLaddFields($gameHeight, ""); }
			if ($gameIcon)	{ $sql .= ", pag_icon=". SQLaddFields($gameIcon, "s"); }
			if ($gameOnlineEmbed)	{ $sql .= ", pag_online_embed=". SQLaddFields($gameOnlineEmbed, "s"); }
			if ($gameSwfWidth)	{ $sql .= ", pag_online_game_swf_width=". SQLaddFields($gameSwfWidth, ""); } 
			if ($gameSwfHeight) { $sql .= ", pag_online_game_swf_height=". SQLaddFields($gameSwfHeight, ""); }
			if ($gameDataAlteracao) { $sql .= ", pag_data_alteracao=". SQLaddFields($gameDataAlteracao, ""); }
			if ($gameDescription) { $sql .= ", pag_description=". SQLaddFields($gameDescription, "s"); }
							
			$sql .= " WHERE pag_id=".SQLaddFields($gameID, ""). " ";						
			$rs   = SQLexecuteQuery($sql);
							
			if($rs) {
				$ret = true;
				$this->logEvents("OK", "", "", "Registro Atualizado com Sucesso! ", $sql);
			}
			else {
				throw new Exception("Erro ao tentar atualizar um jogo alawar na base de dados: (".pg_errormessage().")");
			}
		} catch (Exception $e) {							
			$this->logEvents("ERROR", $e->getFile(), $e->getLine(), $e->getMessage(), $sql);
		}
	
		return $ret;
	}

	
	/* Desativa um Game da Base de Dados */
	public function disable($gameID) {
			
		$ret = false;
	
		try {
			$sql  = "UPDATE pins_alawar_games set pag_status=0, pag_data_alteracao=CURRENT_TIMESTAMP WHERE pag_id=".SQLaddFields($gameID, "");				
			$rs   = SQLexecuteQuery($sql);
				
			if($rs) {
				$ret = true;
			}
			else {
				throw new Exception("Erro ao tentar Desativar um jogo alawar na base de dados: (".pg_errormessage().")");
			}
		} catch (Exception $e) {
			$this->logEvents("ERROR", $e->getFile(), $e->getLine(), $e->getMessage(), $sql);
		}
	
		return $ret;
	}
	
	
	/* Log de Eventos - ERROR ou OK */
	private function logEvents($typeLog = "", $fileName="", $lineError="", $message="", $queryStr="") {
	
		if($typeLog == "ERROR") {			
			$log   = "TIPO LOG -> *ERROR* \n";
			$log  .= "SCRIPT   -> ".$fileName."\n";
			$log  .= "LINHA    -> ".$lineError."\n";
			$log  .= "MENSAGEM -> ".$message."\n";
			$log  .= "SQL      -> ".$queryStr."\n";				
		}
		elseif ($typeLog == "OK") {
			$log   = "TIPO LOG -> OK \n";
			$log  .= "MENSAGEM -> ".$message."\n";
			$log  .= "SQL      -> ".$queryStr."\n";				
		}
		elseif ($typeLog == "GAME_DISABLE_BY_ALAWAR") {
			$log   = "TIPO LOG -> GAME_DISABLE_BY_ALAWAR \n";
			$log  .= "MENSAGEM -> ".$message."\n";
		}
		elseif ($typeLog == "GAME_IMPORT_TASK") {
			$log   = "TIPO LOG -> GAME_IMPORT_TASK \n";
			$log  .= "MENSAGEM -> ".$message."\n";
		}
		
		Utils::logEvent(LOG_FILE_AUTOMATIC_TASKS_ALAWAR, $log);			
	}
	
	
	public function loadImageGameList() {
			
		try {
				
			$dom = new DOMDocument;
				
			/* Carrega o arquivo XML com lista de Games e seus IDs. No caso de erro, dispara uma Exception que serao gravada em arquivo Texto */
			$gameIdByXML = @file_get_contents(URL_LOAD_GAME_LIST_ALAWAR);
	
			if (!$gameIdByXML) {
				throw new Exception("Erro ao tentar conectar na Alawar para obter arquivo XML!");
			}
	
			/* Carrega arquivo XML com os Games, no caso de erro, dispara uma Exception que serao gravada em arquivo Texto */
			if(!@$dom->loadXML($gameIdByXML)) {
				throw new Exception("Erro ao tentar carregar arquivo XML!");
			}
	
			@$gameContentXml = simplexml_import_dom($dom);	
//			$alawarItems = $gameContentXml->xpath("//Catalog[@Code='casualpcgames']//Items/Item");
			$alawarItems = $gameContentXml->xpath("//Catalog[@Code='onlinegames']//Items/Item");
				
			$recordSetGames = array();
			
			foreach ($alawarItems as $itAl) {
	
				$jogoCasualProperties = array();
	
				/* Pega o ID do Jogo do node @attributes */
				$gameID = each($itAl);
				$jogoCasualProperties['game_id'] = $gameID['value']['ID'];
	
				/* O dados recebidos chegam em UTF-8, e precisamos grava-los em ISO-8859-1 (PgSQL) */
				$jogoCasualProperties['game_name'] = iconv("UTF-8", "ISO-8859-1", trim($itAl->Name));
				$jogoCasualProperties['symbol_code'] = (string)$itAl->Properties->Property[0];
				$jogoCasualProperties['game_icon'] = (string)$itAl->Images->Image[2];
				$jogoCasualProperties['description'] = iconv("UTF-8", "ISO-8859-1", trim((string)$itAl->Properties->Property[6]));
	
				/*
				<Images>
					<Image Type="icon44x44" Width="44" Height="44" Timestamp="1329206978">http://eu.alawar.com/upload/iblock/52a/44x44.gif</Image>
					<Image Type="icon44x44bg" Width="44" Height="44" Timestamp="1329206978">http://eu.alawar.com/upload/iblock/b88/44x44full.gif</Image>
					<Image Type="icon100x100" Width="100" Height="100" Timestamp="1329206978">http://eu.alawar.com/upload/iblock/4a8/100x100.gif</Image>
					<Image Type="icon100x100bg" Width="100" Height="100" Timestamp="1329206978">http://eu.alawar.com/upload/iblock/7a6/100x100full.gif</Image>
					<Image Type="logo190x140" Width="190" Height="140" Timestamp="1329206978">http://eu.alawar.com/upload/iblock/a59/190x140.gif</Image>
					<Image Type="banner586x152" Width="586" Height="152" Timestamp="1329206978">http://eu.alawar.com/upload/iblock/28b/586x152eng.jpg</Image>
				</Images>
				*/
								
				$recordSetGames[$jogoCasualProperties['game_id']]['img'] = '<img src="'.(string)$itAl->Images->Image[2].'" alt="'.iconv("UTF-8", "ISO-8859-1", trim((string)$itAl->Properties->Property[4])).'" />';
				$recordSetGames[$jogoCasualProperties['game_id']]['description'] = iconv("UTF-8", "ISO-8859-1", trim((string)$itAl->Properties->Property[6]));
				$recordSetGames[$jogoCasualProperties['game_id']]['title'] = $jogoCasualProperties['game_name'];
				
				//echo '<img src="'.(string)$itAl->Images->Image[0].'">';
				//echo '<img src="'.(string)$itAl->Images->Image[1].'">';
				//echo '<img src="'.(string)$itAl->Images->Image[2].'">';
				//echo '<img src="'.(string)$itAl->Images->Image[3].'">';
				//echo '<img src="'.(string)$itAl->Images->Image[4].'">';
				//echo '<img src="'.(string)$itAl->Images->Image[5].'" alt="'.iconv("UTF-8", "ISO-8859-1", trim((string)$itAl->Properties->Property[4])).'">';
				//echo "<p>".iconv("UTF-8", "ISO-8859-1", trim((string)$itAl->Properties->Property[6]))."</p>";
				//echo "<hr>";

/*
				// para casualpcgames
				$sql = "update pins_alawar_games set pag_icon = '".(string)$itAl->Images->Image[2]."', pag_description = '".pg_escape_string(trim((string)$itAl->Properties->Property[6]))."' where pag_id = ".$jogoCasualProperties['game_id'].";";
				// para onlinegames
				$sql = "update pins_alawar_games set pag_icon = '".(string)$itAl->Images->Image[2]."', pag_description = '".pg_escape_string(trim((string)$itAl->Properties->Property[3]))."' where pag_id = ".$jogoCasualProperties['game_id'].";";
				echo $sql."<hr>";
*/
			}
			
			return $recordSetGames;
										
		} catch (Exception $e) {
				
			$log   = "TIPO LOG -> *ERROR* \n";
			$log  .= "SCRIPT   -> ".$e->getFile()."\n";
			$log  .= "LINHA    -> ".$e->getLine()."\n";
			$log  .= "MENSAGEM -> ".$e->getMessage()."\n";
			$this->logEvents("ERROR", $e->getFile(), $e->getLine(), $e->getMessage());
		}
	}
}
function gravaLog_AlawarXML($mensagem){
	//Arquivo
        global $raiz_do_projeto;
	$file = $raiz_do_projeto . "log/log_AlawarXML.txt";

	//Mensagem
	$mensagem = str_repeat("=", 80). "\n".date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . "\n" . $mensagem . "\n";

	//Grava mensagem no arquivo
	if ($handle = fopen($file, 'a+')) {
		fwrite($handle, $mensagem);
		fclose($handle);
	} 
}


?>

