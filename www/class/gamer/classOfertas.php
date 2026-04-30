<?php

class Ofertas {
		
	public function __construct() { }	

	private function addParam(&$params, $value) {
		$params[] = $value;
		return '$' . count($params);
	}

	private function filtroValue($filtro, $key) {
		return is_array($filtro) && array_key_exists($key, $filtro) ? $filtro[$key] : null;
	}

	private function addOffersWhere(&$sql, &$params, &$filtro) {
		if (!is_array($filtro)) $filtro = array();

		if ($this->filtroValue($filtro, 'ugo_id') !== null && $this->filtroValue($filtro, 'ugo_id') !== '') {
			$sql .= " AND ug_ofertas.ugo_id = " . $this->addParam($params, $this->filtroValue($filtro, 'ugo_id'));
		}

		if ($this->filtroValue($filtro, 'ugo_oferta_id') !== null && $this->filtroValue($filtro, 'ugo_oferta_id') !== '') {
			$sql .= " AND ug_ofertas.ugo_oferta_id LIKE " . $this->addParam($params, '%' . $this->filtroValue($filtro, 'ugo_oferta_id') . '%');
		}

		if ($this->filtroValue($filtro, 'ugoc_descricao') !== null && $this->filtroValue($filtro, 'ugoc_descricao') !== '') {
			$sql .= " AND UPPER(ug_ofertas_canal.ugoc_descricao) LIKE " . $this->addParam($params, '%' . strtoupper($this->filtroValue($filtro, 'ugoc_descricao')) . '%');
		}

		if ($this->filtroValue($filtro, 'ugo_transaction_id') !== null && $this->filtroValue($filtro, 'ugo_transaction_id') !== '') {
			$sql .= " AND ug_ofertas.ugo_transaction_id LIKE " . $this->addParam($params, '%' . $this->filtroValue($filtro, 'ugo_transaction_id') . '%');
		}

		if ($this->filtroValue($filtro, 'ugo_valor_credito') !== null && $this->filtroValue($filtro, 'ugo_valor_credito') !== '') {
			$sql .= " AND ug_ofertas.ugo_valor_credito = " . $this->addParam($params, $this->filtroValue($filtro, 'ugo_valor_credito') ? $this->filtroValue($filtro, 'ugo_valor_credito') : 0);
		}

		if ($this->filtroValue($filtro, 'ugo_ug_email') !== null && $this->filtroValue($filtro, 'ugo_ug_email') !== '') {
			$sql .= " AND UPPER(ug_ofertas.ugo_ug_email) LIKE " . $this->addParam($params, '%' . strtoupper($this->filtroValue($filtro, 'ugo_ug_email')) . '%');
		}

		if ($this->filtroValue($filtro, 'ugo_status') !== null && $this->filtroValue($filtro, 'ugo_status') !== '') {
			$sql .= " AND ug_ofertas.ugo_status = " . $this->addParam($params, $this->filtroValue($filtro, 'ugo_status'));
		}

		if ($this->filtroValue($filtro, 'ugo_data_adesao_oferta_ini') && $this->filtroValue($filtro, 'ugo_data_adesao_oferta_fim')) {
			$filtro['ugo_data_adesao_oferta_ini'] = formata_data_ts($this->filtroValue($filtro, 'ugo_data_adesao_oferta_ini') . " 00:00:00", 2, true, true);
			$filtro['ugo_data_adesao_oferta_fim'] = formata_data_ts($this->filtroValue($filtro, 'ugo_data_adesao_oferta_fim') . " 23:59:59", 2, true, true);
			$sql .= " AND ug_ofertas.ugo_data_adesao_oferta between " . $this->addParam($params, $filtro['ugo_data_adesao_oferta_ini']) . " and " . $this->addParam($params, $filtro['ugo_data_adesao_oferta_fim']);
		}
		else if ($this->filtroValue($filtro, 'ugo_data_adesao_oferta_ini') !== null && $this->filtroValue($filtro, 'ugo_data_adesao_oferta_fim') === null) {
			$filtro['ugo_data_adesao_oferta_ini'] = formata_data_ts($this->filtroValue($filtro, 'ugo_data_adesao_oferta_ini') . " 00:00:00", 2, true, true);
			$sql .= " AND ug_ofertas.ugo_data_adesao_oferta >= " . $this->addParam($params, $filtro['ugo_data_adesao_oferta_ini']);
		}
	}

	private function getOffersOrderBy($orderBy) {
		$allow = array(
			'ugo_id' => 'ug_ofertas.ugo_id',
			'ugo_oferta_id' => 'ug_ofertas.ugo_oferta_id',
			'ugoc_descricao' => 'ug_ofertas_canal.ugoc_descricao',
			'ugo_transaction_id' => 'ug_ofertas.ugo_transaction_id',
			'ugo_valor_credito' => 'ug_ofertas.ugo_valor_credito',
			'ugo_ug_email' => 'ug_ofertas.ugo_ug_email',
			'ugo_status' => 'ug_ofertas.ugo_status',
			'ugo_data_adesao_oferta' => 'ug_ofertas.ugo_data_adesao_oferta',
			'descricao' => 'ug_ofertas_status.descricao'
		);
		$parts = array();
		foreach (explode(',', (string)$orderBy) as $part) {
			$bits = preg_split('/\s+/', trim($part));
			$field = $bits[0];
			$dir = isset($bits[1]) && strtolower($bits[1]) == 'desc' ? 'DESC' : 'ASC';
			if (isset($allow[$field])) $parts[] = $allow[$field] . ' ' . $dir;
		}
		return implode(', ', $parts);
	}
	
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
		
		$sql = "SELECT ugoc_canal_url, ugoc_app_id, ugoc_descricao FROM usuarios_games_ofertas_canal WHERE ugoc_id=$1";
		$rs = SQLexecuteQueryParams($sql, array($idOfferChannel));		
		$result = pg_fetch_assoc($rs);
				
		$urlPainelOferta = str_replace("[APP_ID]", $result["ugoc_app_id"], $result["ugoc_canal_url"]);
		$urlPainelOferta = str_replace("[USER_ID]", $emailUser, $urlPainelOferta);
		$urlPainelOferta = str_replace("[TRANSACTION_ID]", md5(microtime().uniqid((string)rand(), true)), $urlPainelOferta);
		
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
		$params = array();
		$this->addOffersWhere($sql, $params, $filtro);
					
		$orderSql = $this->getOffersOrderBy($orderBy);
		if($orderSql != "") $sql .= " ORDER BY " . $orderSql;
		if(!is_null($limitQuery) && (int)$limitQuery != 0) $sql .= " LIMIT " . (int)$limitQuery;
		if(!is_null($offSetQuery) && (int)$offSetQuery != 0) $sql .= " OFFSET " . (int)$offSetQuery;			
		$rs = $params ? SQLexecuteQueryParams($sql, $params) : SQLexecuteQuery($sql);
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
		$params = array();
		$this->addOffersWhere($sql, $params, $filtro);
	
		$rs = $params ? SQLexecuteQueryParams($sql, $params) : SQLexecuteQuery($sql);
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