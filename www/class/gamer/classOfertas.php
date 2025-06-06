<?php

class Ofertas {
		
	public function __construct() { }	
	
	/*
	 * Retorna as informacoes de todos os canais de ofertas disponiveis na Loja
	 *
	 * @return Array
	*/
	public function getOfferChannels() {
	
		$sql = "SELECT * FROM usuarios_games_ofertas_canal WHERE ugoc_ativo=1 ORDER BY ugoc_descricao ASC";
		$rs = SQLexecuteQuery($sql);
		$listOfferChannels = array();
	
		while ($result = pg_fetch_assoc($rs)) {
			array_push($listOfferChannels, $result);
		}
	
		return $listOfferChannels;
	}
		
	/*
	 * Monta o Menu de Navegacao na Pagina que Exibe Todos os Murais de Ofertas
	 *
	 * @return String
	*/
	public function getNavChannelsOfferWall() {
		
		global $canaisOfertas;
		
		$arrayOffersChannel = $this->getOfferChannels();
		$canaisOfertasStr = '';
		
		foreach ($arrayOffersChannel as $offerChannel) {			
			$channelNickName = array_search($offerChannel["ugoc_id"], $canaisOfertas);
			$canaisOfertasStr .= '<a href="index.php?ch='.$channelNickName.'"><img src="'.URL_LOGO_IMAGE.$offerChannel["ugoc_imagem"].'" border="0" style="float: left; display: block; margin-right: 30px;" /></a>';
		}
				
		return $canaisOfertasStr;		
	}
	
	
	/*
	 * Retorna o codigo HTML do Mural de Ofertas do Canal Selecionado 
	 *
	 * @return String
	*/
	public function getIframeByOfferChannel($idOfferChannel, $emailUser) {
		
		$sql = "SELECT ugoc_canal_url, ugoc_app_id, ugoc_descricao FROM usuarios_games_ofertas_canal WHERE ugoc_id=".$idOfferChannel;
		$rs = SQLexecuteQuery($sql);		
		$result = pg_fetch_assoc($rs);
				
		$urlPainelOferta = str_replace("[APP_ID]", $result["ugoc_app_id"], $result["ugoc_canal_url"]);
		$urlPainelOferta = str_replace("[USER_ID]", $emailUser, $urlPainelOferta);
		$urlPainelOferta = str_replace("[TRANSACTION_ID]", md5(microtime().uniqid(rand(), true)), $urlPainelOferta);
		
		$painelOfertas = '	<!-- <Painel Ofertas - '.$result["ugoc_descricao"].'> -->
							<div class="painelOfertas">
								<iframe id="iframeOfferChannell" src="'.$urlPainelOferta.'" frameborder="0" width="890" height="1400"></iframe>
							</div>
							<!-- </Painel Ofertas - '.$result["ugoc_descricao"].'> -->';										
				
		return $painelOfertas;				
	}
	
	
	/*
	 * Obtem a lista de todos as ofertas aderidas de acordo com o filtro aplicado
	 *
	 * @return String
	*/	
	public function getOffersBy($filtro = "", $orderBy = "", $limitQuery = 0, $offSetQuery = 0) {
	
		$sql = "SELECT
					ug_ofertas.*,
					to_char(ugo_data_adesao_oferta, 'dd/mm/yyyy - HH24:MI:SS') as ugo_data_adesao_oferta, 
					ug_ofertas_canal.*, 
					ug_ofertas_status.descricao as descricao 
				FROM 
					usuarios_games_ofertas as ug_ofertas,
					usuarios_games_ofertas_canal as ug_ofertas_canal,
					usuarios_games_ofertas_status as ug_ofertas_status  
				WHERE 
					 1=1 
					 AND ug_ofertas.ugo_ugoc_id=ug_ofertas_canal.ugoc_id 
					 AND ug_ofertas.ugo_status=ug_ofertas_status.ugo_status_id ";
					
		if(!is_null($filtro['ugo_id']) && $filtro['ugo_id'] != "") {
			$sql .= " AND (" . (is_null($filtro['ugo_id'])?1:0);
			$sql .= "=1 OR ug_ofertas.ugo_id = " . SQLaddFields($filtro['ugo_id'], "") . ")";
		}
	
		if(!is_null($filtro['ugo_oferta_id']) && $filtro['ugo_oferta_id'] != "") {
			$sql .= " AND (" . (is_null($filtro['ugo_oferta_id'])?1:0);
			$sql .= "=1 OR ug_ofertas.ugo_oferta_id LIKE '%" . SQLaddFields($filtro['ugo_oferta_id'], "r") . "%')";
		}
		
		if(!is_null($filtro['ugoc_descricao']) && $filtro['ugoc_descricao'] != "") {
			$sql .= " AND (" . (is_null($filtro['ugoc_descricao'])?1:0);
			$sql .= "=1 OR UPPER(ug_ofertas_canal.ugoc_descricao) LIKE '%" . SQLaddFields(strtoupper($filtro['ugoc_descricao']), "r") . "%')";
		}
		
		if(!is_null($filtro['ugo_transaction_id']) && $filtro['ugo_transaction_id'] != "") {
			$sql .= " AND (" . (is_null($filtro['ugo_transaction_id'])?1:0);
			$sql .= "=1 OR ug_ofertas.ugo_transaction_id LIKE '%" . SQLaddFields($filtro['ugo_transaction_id'], "r") . "%')";
		}
				
		if(!is_null($filtro['ugo_valor_credito']) && $filtro['ugo_valor_credito'] != "") {
			$sql .= " AND (" . (is_null($filtro['ugo_valor_credito'])?1:0);
			$sql .= "=1 OR ug_ofertas.ugo_valor_credito = " . SQLaddFields($filtro['ugo_valor_credito']?$filtro['ugo_valor_credito']:0, "") . ")";
		}
		
		if(!is_null($filtro['ugo_ug_email']) && $filtro['ugo_ug_email'] != "") {
			$sql .= " AND (" . (is_null($filtro['ugo_ug_email'])?1:0);
			$sql .= "=1 OR UPPER(ug_ofertas.ugo_ug_email) LIKE '%" . SQLaddFields(strtoupper($filtro['ugo_ug_email']), "r") . "%')";
		}
		
		if(!is_null($filtro['ugo_status']) && $filtro['ugo_status'] != "") {
			$sql .= " AND (" . (is_null($filtro['ugo_status'])?1:0);
			$sql .= "=1 OR ug_ofertas.ugo_status = " . SQLaddFields($filtro['ugo_status'], "") . ")";
		}
		
		if ($filtro['ugo_data_adesao_oferta_ini'] && $filtro['ugo_data_adesao_oferta_fim']) {
			$filtro['ugo_data_adesao_oferta_ini'] = formata_data_ts($filtro['ugo_data_adesao_oferta_ini'] . " 00:00:00", 2, true, true);
			$filtro['ugo_data_adesao_oferta_fim'] = formata_data_ts($filtro['ugo_data_adesao_oferta_fim'] . " 23:59:59", 2, true, true);
				
			$sql .= " AND (ug_ofertas.ugo_data_adesao_oferta between " . SQLaddFields($filtro['ugo_data_adesao_oferta_ini'], "s") . " and " . SQLaddFields($filtro['ugo_data_adesao_oferta_fim'], "s") . ")";
		}
		else if (!is_null($filtro['ugo_data_adesao_oferta_ini']) && is_null($filtro['ugo_data_adesao_oferta_fim'])) {
			$filtro['ugo_data_adesao_oferta_ini'] = formata_data_ts($filtro['ugo_data_adesao_oferta_ini'] . " 00:00:00", 2, true, true);
			$sql .= " AND (ug_ofertas.ugo_data_adesao_oferta >= " . SQLaddFields($filtro['ugo_data_adesao_oferta_ini'], "s"). ")";
		}	
		
		if(!is_null($orderBy) && $orderBy != "") $sql .= " ORDER BY " . $orderBy;
		if(!is_null($limitQuery) && $limitQuery != 0) $sql .= " LIMIT " . $limitQuery;
		if(!is_null($offSetQuery) && $offSetQuery != 0) $sql .= " OFFSET " . $offSetQuery;			
		$rs = SQLexecuteQuery($sql);
		$listOfOffers = array();
	
		if ($rs) {
			while ($result = pg_fetch_assoc($rs)) {
				array_push($listOfOffers, $result);
			}
		}

		return $listOfOffers;
	}

	/*
	* Obtem a lista de todos as ofertas aderidas de acordo com o filtro aplicado
	*
	* @return String
	*/
	public function getTotalsBy($filtro = "") {
	
		$sql = "SELECT
					SUM(ug_ofertas.ugo_valor_credito) as total_valor_credito, 
					COUNT(ug_ofertas.ugo_id) as total_registros  					
				FROM 
					usuarios_games_ofertas as ug_ofertas,
					usuarios_games_ofertas_canal as ug_ofertas_canal,
					usuarios_games_ofertas_status as ug_ofertas_status  
				WHERE 
					 1=1 
					 AND ug_ofertas.ugo_ugoc_id=ug_ofertas_canal.ugoc_id 
					 AND ug_ofertas.ugo_status=ug_ofertas_status.ugo_status_id ";
		
		if(!is_null($filtro['ugo_id']) && $filtro['ugo_id'] != "") {
			$sql .= " AND (" . (is_null($filtro['ugo_id'])?1:0);
			$sql .= "=1 OR ug_ofertas.ugo_id = " . SQLaddFields($filtro['ugo_id'], "") . ")";
		}
	
		if(!is_null($filtro['ugo_oferta_id']) && $filtro['ugo_oferta_id'] != "") {
			$sql .= " AND (" . (is_null($filtro['ugo_oferta_id'])?1:0);
			$sql .= "=1 OR ug_ofertas.ugo_oferta_id LIKE '%" . SQLaddFields($filtro['ugo_oferta_id'], "r") . "%')";
		}
	
		if(!is_null($filtro['ugoc_descricao']) && $filtro['ugoc_descricao'] != "") {
			$sql .= " AND (" . (is_null($filtro['ugoc_descricao'])?1:0);
			$sql .= "=1 OR UPPER(ug_ofertas_canal.ugoc_descricao) LIKE '%" . SQLaddFields(strtoupper($filtro['ugoc_descricao']), "r") . "%')";
		}
	
		if(!is_null($filtro['ugo_transaction_id']) && $filtro['ugo_transaction_id'] != "") {
			$sql .= " AND (" . (is_null($filtro['ugo_transaction_id'])?1:0);
			$sql .= "=1 OR ug_ofertas.ugo_transaction_id LIKE '%" . SQLaddFields($filtro['ugo_transaction_id'], "r") . "%')";
		}
	
		if(!is_null($filtro['ugo_valor_credito']) && $filtro['ugo_valor_credito'] != "") {
			$sql .= " AND (" . (is_null($filtro['ugo_valor_credito'])?1:0);
			$sql .= "=1 OR ug_ofertas.ugo_valor_credito = " . SQLaddFields($filtro['ugo_valor_credito']?$filtro['ugo_valor_credito']:0, "") . ")";
		}
	
		if(!is_null($filtro['ugo_ug_email']) && $filtro['ugo_ug_email'] != "") {
			$sql .= " AND (" . (is_null($filtro['ugo_ug_email'])?1:0);
			$sql .= "=1 OR UPPER(ug_ofertas.ugo_ug_email) LIKE '%" . SQLaddFields(strtoupper($filtro['ugo_ug_email']), "r") . "%')";
		}
	
		if(!is_null($filtro['ugo_status']) && $filtro['ugo_status'] != "") {
			$sql .= " AND (" . (is_null($filtro['ugo_status'])?1:0);
			$sql .= "=1 OR ug_ofertas.ugo_status = " . SQLaddFields($filtro['ugo_status'], "") . ")";
		}
	
		if ($filtro['ugo_data_adesao_oferta_ini'] && $filtro['ugo_data_adesao_oferta_fim']) {
			$filtro['ugo_data_adesao_oferta_ini'] = formata_data_ts($filtro['ugo_data_adesao_oferta_ini'] . " 00:00:00", 2, true, true);
			$filtro['ugo_data_adesao_oferta_fim'] = formata_data_ts($filtro['ugo_data_adesao_oferta_fim'] . " 23:59:59", 2, true, true);
	
			$sql .= " AND (ug_ofertas.ugo_data_adesao_oferta between " . SQLaddFields($filtro['ugo_data_adesao_oferta_ini'], "s") . " and " . SQLaddFields($filtro['ugo_data_adesao_oferta_fim'], "s") . ")";
		}
		else if (!is_null($filtro['ugo_data_adesao_oferta_ini']) && is_null($filtro['ugo_data_adesao_oferta_fim'])) {
			$filtro['ugo_data_adesao_oferta_ini'] = formata_data_ts($filtro['ugo_data_adesao_oferta_ini'] . " 00:00:00", 2, true, true);
			$sql .= " AND (ug_ofertas.ugo_data_adesao_oferta >= " . SQLaddFields($filtro['ugo_data_adesao_oferta_ini'], "s"). ")";
		}
	
		$rs = SQLexecuteQuery($sql);
		$resultTotal = pg_fetch_assoc($rs);	
		return $resultTotal;
	}	
	
	/*
	 * Retorna a lista de Status das Ofertas
	 *
	 * @return Array
	*/
	public function getOfferStatus() {
	
		$sql = "SELECT * FROM usuarios_games_ofertas_status ORDER BY ugo_status_id ASC";
		$rs = SQLexecuteQuery($sql);
		$listOfferStatus = array();
	
		while ($result = pg_fetch_assoc($rs)) {
			array_push($listOfferStatus, $result);
		}
	
		return $listOfferStatus;
	}
	
}

?>