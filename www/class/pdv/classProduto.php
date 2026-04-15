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
    
	function reordenar($cont) {
		if(is_null($cont))
		{
			//Ativos
			$sql = "select ogp_id from tb_dist_operadora_games_produto where ogp_ativo = 1 order by ogp_nome ASC";
			$sql_ordenar = pg_query($sql);
			$cont = 0;
		
			if (pg_num_rows($sql_ordenar) != 0)
				while($codigo = pg_fetch_array($sql_ordenar))
				{
					pg_query("update tb_dist_operadora_games_produto set ogp_ordem = " . $cont . " where ogp_id = " . $codigo["ogp_id"] . "");
					$cont++;
				}
		}
		
		//Inativos
		$sql = "select ogp_id from tb_dist_operadora_games_produto where ogp_ativo <> 1 order by ogp_nome ASC";
//echo "sql: $sql<br>";
		$sql_ordenar_inat = pg_query($sql);
		$cont = is_null($cont) ? pg_num_rows($sql_ordenar) : $cont;
		
		if (pg_num_rows($sql_ordenar_inat) != 0)
			while($codigo = pg_fetch_array($sql_ordenar_inat))
			{
				pg_query("update tb_dist_operadora_games_produto set ogp_ordem = " . $cont . " where ogp_id = " . $codigo["ogp_id"] . "");
				$cont++;
			}
	}
    
    function inserir(&$objProduto){
 
 		$ret = Produto::validarCampos($objProduto);
 
 		if($ret == ""){
 			$sql = "insert into tb_dist_operadora_games_produto(" .
 					"ogp_nome, ogp_descricao, ogp_ativo, " .
 					"ogp_nome_imagem, ogp_data_inclusao, ogp_opr_codigo, " .
 				 	"ogp_mostra_integracao_gamer, ogp_ordem, ogp_iof, ogp_inibi_lojas_online, " .
 				 	"ogp_pin_request,ogp_valor_minimo, ogp_valor_maximo, ogp_idade_minima".
 				 	") values (";
 			$sql .= SQLaddFields($objProduto->getNome(), "s") . ",";
 			$sql .= SQLaddFields($objProduto->getDescricao(), "s") . ",";
 			$sql .= SQLaddFields($objProduto->getAtivo(), "") . ",";
 			$sql .= SQLaddFields($objProduto->getNomeImagem(), "s") . ",";
 			$sql .= SQLaddFields("CURRENT_TIMESTAMP", "") . ",";
 			$sql .= SQLaddFields($objProduto->getOprCodigo(), "") . ",";
			$sql .= SQLaddFields($objProduto->getMostraIntegracao(), "") . ",";

			// Adiciona numero total de registros na tabela (diferencia ativo/inativo)
			if ($objProduto->getAtivo() != 1)
				$sql_ordem = pg_query("select count(*) as total from tb_dist_operadora_games_produto");
			else
				$sql_ordem = pg_query("select count(ogp_id) as total from tb_dist_operadora_games_produto where ogp_ativo = 1");
			$total_ordem = pg_fetch_result($sql_ordem,0,0);
			$sql .= SQLaddFields($total_ordem, "") . ",";
			$sql .= SQLaddFields($objProduto->getIOF(), "") . ",";
			$sql .= SQLaddFields($objProduto->getInibiLojasOnline(), "") . ",";
			$sql .= SQLaddFields($objProduto->getPinRequest(), "") . ",";
                        $sql .= SQLaddFields($objProduto->getValorMinimo(), "") . ",";
                        $sql .= SQLaddFields($objProduto->getValorMaximo(), "") . ",";
			$sql .= SQLaddFields($objProduto->getIdadeMinima(), "") . ");";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $ret = "Erro ao inserir produto.\n";
			else{
				$ret = "";
				
				$rs_id = SQLexecuteQuery("select currval('dist_operadora_games_produto_id_seq') as last_id");
				if($rs_id && pg_num_rows($rs_id) > 0){
					$rs_id_row = pg_fetch_array($rs_id);
					$objProduto->setId($rs_id_row['last_id']);
				}					

				// Se for ativo -> reordena
				if ($objProduto->getAtivo() == 1) $objProduto->reordenar($total_ordem + 1);

			}			
 		}
		
 		return $ret;   	
    }
    
    function atualizar($objProduto){
 
 		$ret = $this->validarCampos($objProduto);
 
 		if($ret == ""){
 			$sql = "update tb_dist_operadora_games_produto set ";
 			if(!is_null($objProduto->getNome())) 			$sql .= " ogp_nome = " 			. SQLaddFields($objProduto->getNome(), "s")		. ",";
 			if(!is_null($objProduto->getDescricao())) 		$sql .= " ogp_descricao = "		. SQLaddFields($objProduto->getDescricao(), "s")	. ",";
			if(!is_null($objProduto->getDescricaoApi()))	$sql .= "ogp_descricao_api = " . SQLaddFields($objProduto->getDescricaoApi(), "s")	. ",";
 			if(!is_null($objProduto->getAtivo())) 			$sql .= " ogp_ativo = " 		. SQLaddFields($objProduto->getAtivo(), "")		. ",";
 			if(!is_null($objProduto->getNomeImagem())) 		$sql .= " ogp_nome_imagem = "		. SQLaddFields($objProduto->getNomeImagem(), "s")	. ",";
 			if(!is_null($objProduto->getMostraIntegracao()))        $sql .= " ogp_mostra_integracao_gamer = ".SQLaddFields($objProduto->getMostraIntegracao(), "")	. ",";
			if(!is_null($objProduto->getOprCodigo())) 		$sql .= " ogp_opr_codigo = "		. SQLaddFields($objProduto->getOprCodigo(), "")		. ", ";
			if(!is_null($objProduto->getIOF())) 			$sql .= " ogp_iof = "			. SQLaddFields($objProduto->getIOF(), "")               . ",";
			if(!is_null($objProduto->getInibiLojasOnline())) 	$sql .= " ogp_inibi_lojas_online = "	. SQLaddFields($objProduto->getInibiLojasOnline(), "")  . ",";
			if(!is_null($objProduto->getPinRequest())) 		$sql .= " ogp_pin_request = "		. SQLaddFields($objProduto->getPinRequest(), "")  	. ", ";
                        if(!is_null($objProduto->getComunicacaoCupom())) 	$sql .= " ogp_comunicacao_cupom = "	. SQLaddFields(trim($objProduto->getComunicacaoCupom()), "s")  	. ", ";
			$sql .= " ogp_valor_minimo = "		. SQLaddFields($objProduto->getValorMinimo(), "")  	. ",";
			$sql .= " ogp_valor_maximo = "		. SQLaddFields($objProduto->getValorMaximo(), "")  	. ",";
                        if(!is_null($objProduto->getIdadeMinima()))             $sql .= " ogp_idade_minima = "		. SQLaddFields($objProduto->getIdadeMinima(), "")  	. "";
                        $sql .= " where ogp_id = " . SQLaddFields($objProduto->getId(), "");
			$ret = SQLexecuteQuery($sql);
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
		$filtro = array_map("strtoupper", $filtro);
	
		$sql = "select * from tb_dist_operadora_games_produto ogp ";

		if(!is_null($filtro) && $filtro != ""){
		
			if(!is_null($filtro['opr']))
				$sql .= " inner join operadoras ope on ope.opr_codigo = ogp.ogp_opr_codigo";


			if(!is_null($filtro['ogp_data_inclusaoMin']) && !is_null($filtro['ogp_data_inclusaoMax'])){
				$filtro['ogp_data_inclusaoMin'] = formata_data_ts($filtro['ogp_data_inclusaoMin'] . " 00:00:00", 1, true, true);
				$filtro['ogp_data_inclusaoMax'] = formata_data_ts($filtro['ogp_data_inclusaoMax'] . " 23:59:59", 1, true, true);
			}			

			$sql .= " where 1=1";
			
			if(!is_null($filtro['opr_status'])) $sql .= " and ope.opr_status = '" . $filtro['opr_status'] . "' ";

			$sql .= " and (" . (is_null($filtro['ogp_id'])?1:0);
			$sql .= "=1 or ogp.ogp_id = " . SQLaddFields($filtro['ogp_id'], "") . ")";
			
			$sql .= " and (" . (is_null($filtro['ogp_id_lista'])?1:0);
			$sql .= "=1 or ogp.ogp_id in (" . SQLaddFields($filtro['ogp_id_lista'], "") . "))";
			
			$sql .= " and (" . (is_null($filtro['ogp_nome'])?1:0);
			$sql .= "=1 or upper(ogp.ogp_nome) = '" . SQLaddFields($filtro['ogp_nome'], "r") . "')";
			
			$sql .= " and (" . (is_null($filtro['ogp_nomeLike'])?1:0);
			$sql .= "=1 or upper(ogp.ogp_nome) like '%" . SQLaddFields($filtro['ogp_nomeLike'], "r") . "%')";
			
			$sql .= " and (" . (is_null($filtro['ogp_descricao'])?1:0);
			$sql .= "=1 or upper(ogp.ogp_descricao) = '" . SQLaddFields($filtro['ogp_descricao'], "r") . "')";
			
			$sql .= " and (" . (is_null($filtro['ogp_descricaoLike'])?1:0);
			$sql .= "=1 or upper(ogp.ogp_descricao) like '%" . SQLaddFields($filtro['ogp_descricaoLike'], "r") . "%')";
			

			if($filtro['ogp_mostra_integracao_gamer_com_loja']) {
				$sql .= " and (0";
				$sql .= "=1 or ((ogp.ogp_ativo = 1) or (ogp_mostra_integracao_gamer = ".$filtro['ogp_mostra_integracao_gamer_com_loja']." and ogp.ogp_ativo = 0) )) \n";
			} else {
			$sql .= " and (" . (is_null($filtro['ogp_ativo'])?1:0);
				$sql .= "=1 or ogp.ogp_ativo = " . SQLaddFields($filtro['ogp_ativo'], "") . ") \n";
			
				$sql .= " and (" . (is_null($filtro['ogp_mostra_integracao_gamer'])?1:0);
				$sql .= "=1 or ogp.ogp_mostra_integracao_gamer = " . SQLaddFields($filtro['ogp_mostra_integracao_gamer'], "") . ") \n";
			}


			
			$sql .= " and (" . (is_null($filtro['ogp_nome_imagem'])?1:0);
			$sql .= "=1 or upper(ogp.ogp_nome_imagem) = '" . SQLaddFields($filtro['ogp_nome_imagem'], "r") . "')";

			$sql .= " and (" . (is_null($filtro['ogp_nome_imagemLike'])?1:0);
			$sql .= "=1 or upper(ogp.ogp_nome_imagem) like '%" . SQLaddFields($filtro['ogp_nome_imagemLike'], "r") . "%')";

			$sql .= " and (" . (is_null($filtro['ogp_data_inclusaoMin']) || is_null($filtro['ogp_data_inclusaoMax'])?1:0);
			$sql .= "=1 or ogp.ogp_data_inclusao between " . SQLaddFields($filtro['ogp_data_inclusaoMin'], "") . " and " . SQLaddFields($filtro['ogp_data_inclusaoMax'], "") . ")";

			$sql .= " and (" . (is_null($filtro['ogp_opr_codigo'])?1:0);
			$sql .= "=1 or ogp.ogp_opr_codigo = " . SQLaddFields($filtro['ogp_opr_codigo'], "") . ")";

			$sql .= " and (" . (is_null($filtro['ogp_codigo_negado'])?1:0);
			$sql .= "=1 or ogp.ogp_id <> " . SQLaddFields($filtro['ogp_codigo_negado'], "") . ")";

			$sql .= " and (" . (is_null($filtro['ogp_codigo_negado_2'])?1:0);
			$sql .= "=1 or ogp.ogp_id not in (" . SQLaddFields($filtro['ogp_codigo_negado_2'], "") . ") )";
		}
		
		if(!is_null($orderBy)) 
			$sql .= " order by " . $orderBy;
		else
			$sql .= " order by ogp_ordem ASC";
		
//echo "<!-- $sql\n -->";

//echo $sql."<br>\n";
//die();

		$rs = SQLexecuteQuery($sql);
		if(!$rs) $ret = "Erro ao obter produto(s).\n";

		return $ret;

	}

	function obterMelhorado($filtro, $orderBy, &$rs){

		$ret = "";
		$filtro = array_map("strtoupper", $filtro);
		
                $sql = "select ogp_id, ogp_nome,ogp_descricao, ogp_descricao_api ,ogp_ativo,ogp_nome_imagem,ogp_data_inclusao,ogp_opr_codigo, ogp_mostra_integracao_gamer, ogp_iof, ogp_inibi_lojas_online, ogp_pin_request, ogp_comunicacao_cupom, ogp_valor_minimo, ogp_valor_maximo, ogp_idade_minima ";
                
                if(!is_null($filtro['opr']))
                {
                    $sql .= ", ope.opr_nome_loja  "; //isso mudará
                }
                                    
                $sql .= "from tb_dist_operadora_games_produto ogp ";
                
		if(!is_null($filtro) && $filtro != ""){
		
			if(!is_null($filtro['opr']))
				$sql .= " inner join operadoras ope on ope.opr_codigo = ogp.ogp_opr_codigo";


			if(!is_null($filtro['ogp_data_inclusaoMin']) && !is_null($filtro['ogp_data_inclusaoMax'])){
				$filtro['ogp_data_inclusaoMin'] = formata_data_ts($filtro['ogp_data_inclusaoMin'] . " 00:00:00", 2, true, true);
				$filtro['ogp_data_inclusaoMax'] = formata_data_ts($filtro['ogp_data_inclusaoMax'] . " 23:59:59", 2, true, true);
			}			

			$sql .= " where 1=1";
			
			if(!is_null($filtro['opr_status'])) $sql .= " and ope.opr_status = '" . $filtro['opr_status'] . "' ";

			if(!is_null($filtro['ogp_id'])) $sql .= " and ogp.ogp_id = " . SQLaddFields($filtro['ogp_id'], "") . " ";

			if(!is_null($filtro['ogp_id_lista'])) $sql .= " and ogp.ogp_id in (" . SQLaddFields($filtro['ogp_id_lista'], "") . ") ";

			if(!is_null($filtro['ogp_nome'])) $sql .= " and upper(ogp.ogp_nome) = '" . SQLaddFields($filtro['ogp_nome'], "r") . "' ";

			if(!is_null($filtro['ogp_nomeLike'])) $sql .= " and upper(ogp.ogp_nome) like '%" . SQLaddFields($filtro['ogp_nomeLike'], "r") . "%' ";

			if(!is_null($filtro['ogp_nome'])) $sql .= " and upper(ogp.ogp_nome) = '" . SQLaddFields($filtro['ogp_nome'], "r") . "' ";

			if(!is_null($filtro['ogp_descricao'])) $sql .= " and upper(ogp.ogp_descricao) = '" . SQLaddFields($filtro['ogp_descricao'], "r") . "' ";

			if(!is_null($filtro['ogp_descricaoLike'])) $sql .= " and upper(ogp.ogp_descricao) like '%" . SQLaddFields($filtro['ogp_descricaoLike'], "r") . "%' ";

			if($filtro['ogp_mostra_integracao_gamer_com_loja']) {
				$sql .= " and ((ogp.ogp_ativo = 1) or (ogp_mostra_integracao_gamer = ".$filtro['ogp_mostra_integracao_gamer_com_loja']." and ogp.ogp_ativo = 0) ) \n";
			} else {
                                $sql .= " and (" . (is_null($filtro['ogp_ativo'])?1:0);
				$sql .= "=1 or ogp.ogp_ativo = " . SQLaddFields($filtro['ogp_ativo'], "") . ") \n";
			
				$sql .= " and (" . (is_null($filtro['ogp_mostra_integracao_gamer'])?1:0);
				$sql .= "=1 or ogp.ogp_mostra_integracao_gamer = " . SQLaddFields($filtro['ogp_mostra_integracao_gamer'], "") . ") \n";
			}

			if(!is_null($filtro['ogp_nome_imagem'])) $sql .= " and upper(ogp.ogp_nome_imagem) = '" . SQLaddFields($filtro['ogp_nome_imagem'], "r") . "' ";

			if(!is_null($filtro['ogp_nome_imagemLike'])) $sql .= " and upper(ogp.ogp_nome_imagem) like '%" . SQLaddFields($filtro['ogp_nome_imagemLike'], "r") . "%' ";

			if(!is_null($filtro['ogp_data_inclusaoMin'])) $sql .= " and ogp.ogp_data_inclusao between " . SQLaddFields($filtro['ogp_data_inclusaoMin'], "s") . " and " . SQLaddFields($filtro['ogp_data_inclusaoMax'], "s") . " ";

			if(!is_null($filtro['ogp_opr_codigo'])) $sql .= " and ogp.ogp_opr_codigo = " . SQLaddFields($filtro['ogp_opr_codigo'], "") . " ";

			if(!is_null($filtro['ogp_codigo_negado'])) $sql .= " and ogp.ogp_id <> " . SQLaddFields($filtro['ogp_codigo_negado'], "") . " ";

			if(!is_null($filtro['ogp_codigo_negado_2'])) $sql .= " and ogp.ogp_id not in (" . SQLaddFields($filtro['ogp_codigo_negado_2'], "") . ") ";

			if($filtro['ogp_inibi_lojas_online'] == 1) $sql .= " and ogp.ogp_inibi_lojas_online != 1 ";
                        
		}
		
		if(!is_null($orderBy)) 
			$sql .= " order by " . $orderBy;
		else
			$sql .= " order by ogp_ordem ASC";
		                
		$rs = SQLexecuteQuery($sql);              
		if(!$rs) $ret = "Erro ao obter produto(s).\n";

		return $ret;

	}
        
}
?>
