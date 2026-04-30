<?php
class Produto {
    
    var $p_id;
    var $p_sNome;
    var $p_sDescricao;
	var $p_sDescricao_api;
    var $p_blAtivo;
    var $p_sNomeImagem;
    var $p_dDataInclusao;
    var $p_iOprCodigo;
    var $p_ogp_mostra_integracao_gamer;
    var $p_ogp_iof;
    var $ogp_inibi_lojas_online;
    var $op_nomeOperadora;
    var $modelo;
    var $ogp_pin_request;
    var $ogp_comunicacao_cupom;
    var $ogp_valor_minimo;
    var $ogp_valor_maximo;
	public $ogp_idade_minima;

    function Produto(	$p_id 				= null,
                        $p_sNome 			= null,
                        $p_sDescricao			= null,
						$p_sDescricao_api 	= null,
                        $p_blAtivo 			= null,
                        $p_sNomeImagem 			= null,
                        $p_dDataInclusao		= null,
                        $p_iOprCodigo			= null,
                        $p_ogp_mostra_integracao_gamer	= null,
                        $p_ogp_iof			= null,
                        $ogp_inibi_lojas_online = null,
                        $modelo				= null,
                        $ogp_pin_request		= null,
                        $ogp_comunicacao_cupom          = null,
                        $ogp_valor_minimo		= null,
			$ogp_valor_maximo		= null,
                        $ogp_idade_minima               = null
                    ) {

    	$this->setId($p_id);
    	$this->setNome($p_sNome);
    	$this->setDescricao($p_sDescricao);
		$this->setDescricaoApi($p_sDescricao_api);
    	$this->setAtivo($p_blAtivo);
    	$this->setNomeImagem($p_sNomeImagem);
    	$this->setDataInclusao($p_dDataInclusao);
    	$this->setOprCodigo($p_iOprCodigo);
        $this->setMostraIntegracao($p_ogp_mostra_integracao_gamer);
        $this->setIOF($p_ogp_iof);
        $this->setInibiLojasOnline($ogp_inibi_lojas_online);
        $this->setModelo($modelo);
        $this->setPinRequest($ogp_pin_request);
        $this->setComunicacaoCupom($ogp_comunicacao_cupom);
        $this->setValorMinimo($ogp_valor_minimo);
        $this->setValorMaximo($ogp_valor_maximo);
        $this->setIdadeMinima($ogp_idade_minima);
		
    } //end function Produto
    
    public function getValorMinimo() {
         return $this->ogp_valor_minimo;
    }

    public function setValorMinimo($ogp_valor_minimo) {
         $this->ogp_valor_minimo = $ogp_valor_minimo;
    }

    public function getValorMaximo() {
         return $this->ogp_valor_maximo;
    }

    public function setValorMaximo($ogp_valor_maximo) {
         $this->ogp_valor_maximo = $ogp_valor_maximo;
    }
    
    public function getIdadeMinima(){
        return $this->ogp_idade_minima;
    }
    
    public function setIdadeMinima($ogp_idade_minima){
        $this->ogp_idade_minima = ($ogp_idade_minima) ? $ogp_idade_minima : 0;
    }
    
    public function getComunicacaoCupom() {
        return $this->ogp_comunicacao_cupom;
     }

     public function setComunicacaoCupom($ogp_comunicacao_cupom) {
        $this->ogp_comunicacao_cupom = $ogp_comunicacao_cupom;
     }
    
     public function getPinRequest() {
         return $this->ogp_pin_request;
     }

     public function setPinRequest($ogp_pin_request) {
         $this->ogp_pin_request = $ogp_pin_request;
     }

     public function getModelo() {
         return $this->modelo;
     }

     public function setModelo($modelo) {
         $this->modelo = $modelo;
         return $this;
     }

    function setNomeOperadora($nomeOperadora = ""){
        $this->op_nomeOperadora = $nomeOperadora;
    }
    
    function getNomeOperadora(){
        return $this->op_nomeOperadora;
    }
    
    function getId(){
    	return $this->p_id;
    }
    function setId($p_id){
    	$this->p_id = $p_id;
    }
    
    function getNome(){
    	return $this->p_sNome;
    }
    function setNome($p_sNome){
    	$this->p_sNome = $p_sNome;
    }
    
    function getDescricao(){
    	return $this->p_sDescricao;
    }
	
	public function getDescricaoApi() {
		return $this->p_sDescricao_api;
	}
	
	public function setDescricaoApi($p_sDescricao_api) {
		$this->p_sDescricao_api = $p_sDescricao_api;
	}
	
    function setDescricao($p_sDescricao){
    	$this->p_sDescricao = $p_sDescricao;
    }
    
    function getAtivo(){
    	return $this->p_blAtivo;
    }
    function setAtivo($p_blAtivo){
		if($p_blAtivo == 1 || $p_blAtivo == "1" || $p_blAtivo === "true") $p_blAtivo = "1";
		else $p_blAtivo = "0";
    	$this->p_blAtivo = $p_blAtivo;
    }
    
	function getMostraIntegracao(){
    	return $this->p_ogp_mostra_integracao_gamer;
    }
    function setMostraIntegracao($p_ogp_mostra_integracao_gamer){
		if($p_ogp_mostra_integracao_gamer == 1 || $p_ogp_mostra_integracao_gamer == "1" || $p_ogp_mostra_integracao_gamer === "true") $p_ogp_mostra_integracao_gamer = "1";
		else $p_ogp_mostra_integracao_gamer = "0";
    	$this->p_ogp_mostra_integracao_gamer = $p_ogp_mostra_integracao_gamer;
    }
    
    function getNomeImagem(){
    	return $this->p_sNomeImagem;
    }
    function setNomeImagem($p_sNomeImagem){
    	$this->p_sNomeImagem = $p_sNomeImagem;
    }
    
    function getDataInclusao(){
    	return $this->p_dDataInclusao;
    }
    function setDataInclusao($p_dDataInclusao){
    	$this->p_dDataInclusao = $p_dDataInclusao;
    }
    
    function getOprCodigo(){
    	return $this->p_iOprCodigo;
    }
    function setOprCodigo($p_iOprCodigo){
    	$this->p_iOprCodigo = $p_iOprCodigo;
    }
    
    function getIOF(){
    	return $this->p_ogp_iof;
    }
    function setIOF($p_ogp_iof){
    	$this->p_ogp_iof = $p_ogp_iof;
    }
    
    function getInibiLojasOnline(){
    	return $this->ogp_inibi_lojas_online;
    }
    function setInibiLojasOnline($ogp_inibi_lojas_online){
    	$this->ogp_inibi_lojas_online = $ogp_inibi_lojas_online;
    }
    

	private function addParam(&$params, $value)
	{
		$params[] = $value;
		return '$' . count($params);
	}

	private function addIdList(&$params, $list)
	{
		$ids = array_filter(array_map('trim', explode(',', (string) $list)), 'ctype_digit');
		$placeholders = array();
		foreach ($ids as $id) {
			$placeholders[] = $this->addParam($params, (int) $id);
		}
		return $placeholders;
	}

	private function orderByProduto($orderBy)
	{
		if (is_null($orderBy) || trim((string) $orderBy) == '') return null;
		$permitidos = array(
			'ogp_id' => 'ogp_id', 'ogp.ogp_id' => 'ogp.ogp_id',
			'ogp_nome' => 'ogp_nome', 'ogp.ogp_nome' => 'ogp.ogp_nome',
			'ogp_descricao' => 'ogp_descricao', 'ogp.ogp_descricao' => 'ogp.ogp_descricao',
			'ogp_descricao_api' => 'ogp_descricao_api', 'ogp.ogp_descricao_api' => 'ogp.ogp_descricao_api',
			'ogp_ativo' => 'ogp_ativo', 'ogp.ogp_ativo' => 'ogp.ogp_ativo',
			'ogp_nome_imagem' => 'ogp_nome_imagem', 'ogp.ogp_nome_imagem' => 'ogp.ogp_nome_imagem',
			'ogp_data_inclusao' => 'ogp_data_inclusao', 'ogp.ogp_data_inclusao' => 'ogp.ogp_data_inclusao',
			'ogp_opr_codigo' => 'ogp_opr_codigo', 'ogp.ogp_opr_codigo' => 'ogp.ogp_opr_codigo',
			'ogp_mostra_integracao_gamer' => 'ogp_mostra_integracao_gamer', 'ogp.ogp_mostra_integracao_gamer' => 'ogp.ogp_mostra_integracao_gamer',
			'ogp_ordem' => 'ogp_ordem', 'ogp.ogp_ordem' => 'ogp.ogp_ordem',
			'ogp_iof' => 'ogp_iof', 'ogp.ogp_iof' => 'ogp.ogp_iof',
			'ogp_inibi_lojas_online' => 'ogp_inibi_lojas_online', 'ogp.ogp_inibi_lojas_online' => 'ogp.ogp_inibi_lojas_online',
			'ogp_pin_request' => 'ogp_pin_request', 'ogp.ogp_pin_request' => 'ogp.ogp_pin_request',
			'ogp_comunicacao_cupom' => 'ogp_comunicacao_cupom', 'ogp.ogp_comunicacao_cupom' => 'ogp.ogp_comunicacao_cupom',
			'ogp_valor_minimo' => 'ogp_valor_minimo', 'ogp.ogp_valor_minimo' => 'ogp.ogp_valor_minimo',
			'ogp_valor_maximo' => 'ogp_valor_maximo', 'ogp.ogp_valor_maximo' => 'ogp.ogp_valor_maximo',
			'ogp_idade_minima' => 'ogp_idade_minima', 'ogp.ogp_idade_minima' => 'ogp.ogp_idade_minima',
			'opr_nome_loja' => 'opr_nome_loja', 'ope.opr_nome_loja' => 'ope.opr_nome_loja'
		);
		$partes = array();
		foreach (explode(',', $orderBy) as $parte) {
			$tokens = preg_split('/\s+/', trim($parte));
			$coluna = strtolower($tokens[0] ?? '');
			$direcao = strtoupper($tokens[1] ?? 'ASC');
			if (!isset($permitidos[$coluna]) || !in_array($direcao, array('ASC', 'DESC'))) return null;
			$partes[] = $permitidos[$coluna] . ' ' . $direcao;
		}
		return count($partes) ? implode(', ', $partes) : null;
	}

	function reordenar($cont) {
		if(is_null($cont))
		{
			$sql = "select ogp_id from tb_dist_operadora_games_produto where ogp_ativo = 1 order by ogp_nome ASC";
			$sql_ordenar = SQLexecuteQuery($sql);
			$cont = 0;
		
			if ((($sql_ordenar) ? pg_num_rows($sql_ordenar) : 0) != 0)
				while($codigo = pg_fetch_array($sql_ordenar))
				{
					SQLexecuteQueryParams("update tb_dist_operadora_games_produto set ogp_ordem = $1 where ogp_id = $2", array($cont, $codigo["ogp_id"]));
					$cont++;
				}
		}
		
		$sql = "select ogp_id from tb_dist_operadora_games_produto where ogp_ativo <> 1 order by ogp_nome ASC";
		$sql_ordenar_inat = SQLexecuteQuery($sql);
		$cont = is_null($cont) ? pg_num_rows($sql_ordenar) : $cont;
		
		if ((($sql_ordenar_inat) ? pg_num_rows($sql_ordenar_inat) : 0) != 0)
			while($codigo = pg_fetch_array($sql_ordenar_inat))
			{
				SQLexecuteQueryParams("update tb_dist_operadora_games_produto set ogp_ordem = $1 where ogp_id = $2", array($cont, $codigo["ogp_id"]));
				$cont++;
			}
	}
    
    function inserir(&$objProduto){
 
 		$ret = Produto::validarCampos($objProduto);
 
 		if($ret == ""){
			if ($objProduto->getAtivo() != 1)
				$sql_ordem = SQLexecuteQuery("select count(*) as total from tb_dist_operadora_games_produto");
			else
				$sql_ordem = SQLexecuteQuery("select count(ogp_id) as total from tb_dist_operadora_games_produto where ogp_ativo = 1");
			$total_ordem = pg_fetch_result($sql_ordem,0,0);

 			$sql = "insert into tb_dist_operadora_games_produto(ogp_nome, ogp_descricao, ogp_ativo, ogp_nome_imagem, ogp_data_inclusao, ogp_opr_codigo, ogp_mostra_integracao_gamer, ogp_ordem, ogp_iof, ogp_inibi_lojas_online, ogp_pin_request, ogp_valor_minimo, ogp_valor_maximo, ogp_idade_minima) values ($1, $2, $3, $4, CURRENT_TIMESTAMP, $5, $6, $7, $8, $9, $10, $11, $12, $13);";
			$params = array($objProduto->getNome(), $objProduto->getDescricao(), $objProduto->getAtivo(), $objProduto->getNomeImagem(), $objProduto->getOprCodigo(), $objProduto->getMostraIntegracao(), $total_ordem, $objProduto->getIOF(), $objProduto->getInibiLojasOnline(), $objProduto->getPinRequest(), $objProduto->getValorMinimo(), $objProduto->getValorMaximo(), $objProduto->getIdadeMinima());
			$ret = SQLexecuteQueryParams($sql, $params);
			if(!$ret) $ret = "Erro ao inserir produto.\n";
			else{
				$ret = "";
				
				$rs_id = SQLexecuteQuery("select currval('dist_operadora_games_produto_id_seq') as last_id");
				if($rs_id && pg_num_rows($rs_id) > 0){
					$rs_id_row = pg_fetch_array($rs_id);
					$objProduto->setId($rs_id_row['last_id']);
				}

				if ($objProduto->getAtivo() == 1) $objProduto->reordenar($total_ordem + 1);
			}
 		}
		
 		return $ret;   	
    }
    
    function atualizar($objProduto){
 
 		$ret = $this->validarCampos($objProduto);
 
 		if($ret == ""){
			$params = array();
			$sets = array();
			if(!is_null($objProduto->getNome())) $sets[] = "ogp_nome = " . $this->addParam($params, $objProduto->getNome());
			if(!is_null($objProduto->getDescricao())) $sets[] = "ogp_descricao = " . $this->addParam($params, $objProduto->getDescricao());
			if(!is_null($objProduto->getDescricaoApi())) $sets[] = "ogp_descricao_api = " . $this->addParam($params, $objProduto->getDescricaoApi());
			if(!is_null($objProduto->getAtivo())) $sets[] = "ogp_ativo = " . $this->addParam($params, $objProduto->getAtivo());
			if(!is_null($objProduto->getNomeImagem())) $sets[] = "ogp_nome_imagem = " . $this->addParam($params, $objProduto->getNomeImagem());
			if(!is_null($objProduto->getMostraIntegracao())) $sets[] = "ogp_mostra_integracao_gamer = " . $this->addParam($params, $objProduto->getMostraIntegracao());
			if(!is_null($objProduto->getOprCodigo())) $sets[] = "ogp_opr_codigo = " . $this->addParam($params, $objProduto->getOprCodigo());
			if(!is_null($objProduto->getIOF())) $sets[] = "ogp_iof = " . $this->addParam($params, $objProduto->getIOF());
			if(!is_null($objProduto->getInibiLojasOnline())) $sets[] = "ogp_inibi_lojas_online = " . $this->addParam($params, $objProduto->getInibiLojasOnline());
			if(!is_null($objProduto->getPinRequest())) $sets[] = "ogp_pin_request = " . $this->addParam($params, $objProduto->getPinRequest());
			if(!is_null($objProduto->getComunicacaoCupom())) $sets[] = "ogp_comunicacao_cupom = " . $this->addParam($params, trim($objProduto->getComunicacaoCupom()));
			$sets[] = "ogp_valor_minimo = " . $this->addParam($params, $objProduto->getValorMinimo());
			$sets[] = "ogp_valor_maximo = " . $this->addParam($params, $objProduto->getValorMaximo());
			if(!is_null($objProduto->getIdadeMinima())) $sets[] = "ogp_idade_minima = " . $this->addParam($params, $objProduto->getIdadeMinima());
			$sql = "update tb_dist_operadora_games_produto set " . implode(", ", $sets);
			$sql .= " where ogp_id = " . $this->addParam($params, $objProduto->getId());
			$ret = SQLexecuteQueryParams($sql, $params);
			if(!$ret) $ret = "Erro ao atualizar produto.\n";
			else $ret = "";
 		}
 		
 		return $ret;   	
    }
    
	function validarCampos($objProduto){
		
		$ret = "";
		
		//Nome
 		$nome = $objProduto->getNome();
 		if(is_null($nome) || $nome == "") 	$ret .= "O Nome deve ser preenchido.\n";
 		elseif(strlen($nome) > 100) 		$ret .= "O nome deve ter até 100 caracteres.\n";
 		
		//Descricao
 		$descricao = $objProduto->getDescricao();
 		if(is_null($descricao) || $descricao == "") $ret .= "A Descrição deve ser preenchida.\n";
 		//elseif(strlen($descricao) > 1024) 			$ret .= "A Descrição deve ter até 1024 caracteres.\n";
 		
		//NomeImagem
 		$nomeImagem = $objProduto->getNomeImagem();
 		if(!is_null($nomeImagem)){
 			if(strlen($nomeImagem) > 100) $ret .= "O Nome da Imagem deve ter até 100 caracteres.\n";
 		}
 		
		//ativo
 		$ativo = $objProduto->getAtivo();
 		if(is_null($ativo) || $ativo == "") $ret .= "O status deve ser selecionado.\n";
		else if(!is_numeric($ativo)) $ret .= "O status deve ser númerico.\n";

		//opr_codigo
 		$opr_codigo = $objProduto->getOprCodigo();
 		if(is_null($opr_codigo) || $opr_codigo == "") $ret .= "A Operadora deve ser selecionada.\n";
		else if(!is_numeric($opr_codigo)) $ret .= "O código da Operadora deve ser númerico.\n";

 		
 		return $ret;
	}

	function obter($filtro, $orderBy, &$rs){

		$ret = "";
		$params = array();
		$filtro = is_array($filtro) ? array_map("strtoupper", $filtro) : array();
		$filtro += array(
			"opr" => null,
			"opr_status" => null,
			"ogp_id" => null,
			"ogp_id_lista" => null,
			"ogp_nome" => null,
			"ogp_nomeLike" => null,
			"ogp_descricao" => null,
			"ogp_descricaoLike" => null,
			"ogp_ativo" => null,
			"ogp_mostra_integracao_gamer" => null,
			"ogp_mostra_integracao_gamer_com_loja" => null,
			"ogp_nome_imagem" => null,
			"ogp_nome_imagemLike" => null,
			"ogp_data_inclusaoMin" => null,
			"ogp_data_inclusaoMax" => null,
			"ogp_opr_codigo" => null,
			"ogp_codigo_negado" => null,
			"ogp_codigo_negado_2" => null,
			"ogp_inibi_lojas_online" => null,
		);
	
		$sql = "select * from tb_dist_operadora_games_produto ogp ";

		if(!empty($filtro)){
			if(!is_null($filtro['opr'])) $sql .= " inner join operadoras ope on ope.opr_codigo = ogp.ogp_opr_codigo";

			if(!is_null($filtro['ogp_data_inclusaoMin']) && !is_null($filtro['ogp_data_inclusaoMax'])){
				$filtro['ogp_data_inclusaoMin'] = formata_data_ts($filtro['ogp_data_inclusaoMin'] . " 00:00:00", 1, true, true);
				$filtro['ogp_data_inclusaoMax'] = formata_data_ts($filtro['ogp_data_inclusaoMax'] . " 23:59:59", 1, true, true);
			}

			$sql .= " where 1=1";
			if(!is_null($filtro['opr_status'])) $sql .= " and ope.opr_status = " . $this->addParam($params, $filtro['opr_status']) . " ";
			if(!is_null($filtro['ogp_id'])) $sql .= " and ogp.ogp_id = " . $this->addParam($params, $filtro['ogp_id']);
			if(!is_null($filtro['ogp_id_lista'])) {
				$ids = $this->addIdList($params, $filtro['ogp_id_lista']);
				$sql .= count($ids) ? " and ogp.ogp_id in (" . implode(', ', $ids) . ")" : " and 1=0";
			}
			if(!is_null($filtro['ogp_nome'])) $sql .= " and upper(ogp.ogp_nome) = " . $this->addParam($params, $filtro['ogp_nome']);
			if(!is_null($filtro['ogp_nomeLike'])) $sql .= " and upper(ogp.ogp_nome) like " . $this->addParam($params, '%' . $filtro['ogp_nomeLike'] . '%');
			if(!is_null($filtro['ogp_descricao'])) $sql .= " and upper(ogp.ogp_descricao) = " . $this->addParam($params, $filtro['ogp_descricao']);
			if(!is_null($filtro['ogp_descricaoLike'])) $sql .= " and upper(ogp.ogp_descricao) like " . $this->addParam($params, '%' . $filtro['ogp_descricaoLike'] . '%');

			if($filtro['ogp_mostra_integracao_gamer_com_loja']) {
				$sql .= " and ((ogp.ogp_ativo = 1) or (ogp_mostra_integracao_gamer = " . $this->addParam($params, $filtro['ogp_mostra_integracao_gamer_com_loja']) . " and ogp.ogp_ativo = 0)) ";
			} else {
				if(!is_null($filtro['ogp_ativo'])) $sql .= " and ogp.ogp_ativo = " . $this->addParam($params, $filtro['ogp_ativo']);
				if(!is_null($filtro['ogp_mostra_integracao_gamer'])) $sql .= " and ogp.ogp_mostra_integracao_gamer = " . $this->addParam($params, $filtro['ogp_mostra_integracao_gamer']);
			}

			if(!is_null($filtro['ogp_nome_imagem'])) $sql .= " and upper(ogp.ogp_nome_imagem) = " . $this->addParam($params, $filtro['ogp_nome_imagem']);
			if(!is_null($filtro['ogp_nome_imagemLike'])) $sql .= " and upper(ogp.ogp_nome_imagem) like " . $this->addParam($params, '%' . $filtro['ogp_nome_imagemLike'] . '%');
			if(!is_null($filtro['ogp_data_inclusaoMin']) && !is_null($filtro['ogp_data_inclusaoMax'])) $sql .= " and ogp.ogp_data_inclusao between " . $this->addParam($params, $filtro['ogp_data_inclusaoMin']) . " and " . $this->addParam($params, $filtro['ogp_data_inclusaoMax']);
			if(!is_null($filtro['ogp_opr_codigo'])) $sql .= " and ogp.ogp_opr_codigo = " . $this->addParam($params, $filtro['ogp_opr_codigo']);
			if(!is_null($filtro['ogp_codigo_negado'])) $sql .= " and ogp.ogp_id <> " . $this->addParam($params, $filtro['ogp_codigo_negado']);
			if(!is_null($filtro['ogp_codigo_negado_2'])) {
				$ids = $this->addIdList($params, $filtro['ogp_codigo_negado_2']);
				if (count($ids)) $sql .= " and ogp.ogp_id not in (" . implode(', ', $ids) . ")";
			}
		}
		
		$order = $this->orderByProduto($orderBy);
		$sql .= $order ? " order by " . $order : " order by ogp_ordem ASC";

		$rs = count($params) ? SQLexecuteQueryParams($sql, $params) : SQLexecuteQuery($sql);
		if(!$rs) $ret = "Erro ao obter produto(s).\n";

		return $ret;

	}

	function obterMelhorado($filtro, $orderBy, &$rs){

		$ret = "";
		$params = array();
		$filtro = is_array($filtro) ? array_map("strtoupper", $filtro) : array();
		$filtro += array(
			"opr" => null,
			"opr_status" => null,
			"ogp_id" => null,
			"ogp_id_lista" => null,
			"ogp_nome" => null,
			"ogp_nomeLike" => null,
			"ogp_descricao" => null,
			"ogp_descricaoLike" => null,
			"ogp_ativo" => null,
			"ogp_mostra_integracao_gamer" => null,
			"ogp_mostra_integracao_gamer_com_loja" => null,
			"ogp_nome_imagem" => null,
			"ogp_nome_imagemLike" => null,
			"ogp_data_inclusaoMin" => null,
			"ogp_data_inclusaoMax" => null,
			"ogp_opr_codigo" => null,
			"ogp_codigo_negado" => null,
			"ogp_codigo_negado_2" => null,
			"ogp_inibi_lojas_online" => null,
		);
		
		$sql = "select ogp_id, ogp_nome,ogp_descricao, ogp_descricao_api ,ogp_ativo,ogp_nome_imagem,ogp_data_inclusao,ogp_opr_codigo, ogp_mostra_integracao_gamer, ogp_iof, ogp_inibi_lojas_online, ogp_pin_request, ogp_comunicacao_cupom, ogp_valor_minimo, ogp_valor_maximo, ogp_idade_minima ";
		if(!is_null($filtro['opr'])) $sql .= ", ope.opr_nome_loja  ";
		$sql .= "from tb_dist_operadora_games_produto ogp ";
		
		if(!empty($filtro)){
			if(!is_null($filtro['opr'])) $sql .= " inner join operadoras ope on ope.opr_codigo = ogp.ogp_opr_codigo";

			if(!is_null($filtro['ogp_data_inclusaoMin']) && !is_null($filtro['ogp_data_inclusaoMax'])){
				$filtro['ogp_data_inclusaoMin'] = formata_data_ts($filtro['ogp_data_inclusaoMin'] . " 00:00:00", 2, true, true);
				$filtro['ogp_data_inclusaoMax'] = formata_data_ts($filtro['ogp_data_inclusaoMax'] . " 23:59:59", 2, true, true);
			}

			$sql .= " where 1=1";
			if(!is_null($filtro['opr_status'])) $sql .= " and ope.opr_status = " . $this->addParam($params, $filtro['opr_status']) . " ";
			if(!is_null($filtro['ogp_id'])) $sql .= " and ogp.ogp_id = " . $this->addParam($params, $filtro['ogp_id']) . " ";
			if(!is_null($filtro['ogp_id_lista'])) {
				$ids = $this->addIdList($params, $filtro['ogp_id_lista']);
				$sql .= count($ids) ? " and ogp.ogp_id in (" . implode(', ', $ids) . ") " : " and 1=0 ";
			}
			if(!is_null($filtro['ogp_nome'])) $sql .= " and upper(ogp.ogp_nome) = " . $this->addParam($params, $filtro['ogp_nome']) . " ";
			if(!is_null($filtro['ogp_nomeLike'])) $sql .= " and upper(ogp.ogp_nome) like " . $this->addParam($params, '%' . $filtro['ogp_nomeLike'] . '%') . " ";
			if(!is_null($filtro['ogp_nome'])) $sql .= " and upper(ogp.ogp_nome) = " . $this->addParam($params, $filtro['ogp_nome']) . " ";
			if(!is_null($filtro['ogp_descricao'])) $sql .= " and upper(ogp.ogp_descricao) = " . $this->addParam($params, $filtro['ogp_descricao']) . " ";
			if(!is_null($filtro['ogp_descricaoLike'])) $sql .= " and upper(ogp.ogp_descricao) like " . $this->addParam($params, '%' . $filtro['ogp_descricaoLike'] . '%') . " ";

			if($filtro['ogp_mostra_integracao_gamer_com_loja']) {
				$sql .= " and ((ogp.ogp_ativo = 1) or (ogp_mostra_integracao_gamer = " . $this->addParam($params, $filtro['ogp_mostra_integracao_gamer_com_loja']) . " and ogp.ogp_ativo = 0) ) ";
			} else {
				if(!is_null($filtro['ogp_ativo'])) $sql .= " and ogp.ogp_ativo = " . $this->addParam($params, $filtro['ogp_ativo']) . " ";
				if(!is_null($filtro['ogp_mostra_integracao_gamer'])) $sql .= " and ogp.ogp_mostra_integracao_gamer = " . $this->addParam($params, $filtro['ogp_mostra_integracao_gamer']) . " ";
			}

			if(!is_null($filtro['ogp_nome_imagem'])) $sql .= " and upper(ogp.ogp_nome_imagem) = " . $this->addParam($params, $filtro['ogp_nome_imagem']) . " ";
			if(!is_null($filtro['ogp_nome_imagemLike'])) $sql .= " and upper(ogp.ogp_nome_imagem) like " . $this->addParam($params, '%' . $filtro['ogp_nome_imagemLike'] . '%') . " ";
			if(!is_null($filtro['ogp_data_inclusaoMin'])) $sql .= " and ogp.ogp_data_inclusao between " . $this->addParam($params, $filtro['ogp_data_inclusaoMin']) . " and " . $this->addParam($params, $filtro['ogp_data_inclusaoMax']) . " ";
			if(!is_null($filtro['ogp_opr_codigo'])) $sql .= " and ogp.ogp_opr_codigo = " . $this->addParam($params, $filtro['ogp_opr_codigo']) . " ";
			if(!is_null($filtro['ogp_codigo_negado'])) $sql .= " and ogp.ogp_id <> " . $this->addParam($params, $filtro['ogp_codigo_negado']) . " ";
			if(!is_null($filtro['ogp_codigo_negado_2'])) {
				$ids = $this->addIdList($params, $filtro['ogp_codigo_negado_2']);
				if (count($ids)) $sql .= " and ogp.ogp_id not in (" . implode(', ', $ids) . ") ";
			}
			if($filtro['ogp_inibi_lojas_online'] == 1) $sql .= " and ogp.ogp_inibi_lojas_online != 1 ";
		}
		
		$order = $this->orderByProduto($orderBy);
		$sql .= $order ? " order by " . $order : " order by ogp_ordem ASC";
		                
		$rs = count($params) ? SQLexecuteQueryParams($sql, $params) : SQLexecuteQuery($sql);
		if(!$rs) $ret = "Erro ao obter produto(s).\n";

		return $ret;

	}
        
	}
	?>