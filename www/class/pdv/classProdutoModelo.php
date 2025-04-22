<?php

class ProdutoModelo {
    
    var $pm_id;
    var $pm_p_id;
    var $pm_sNome;
    var $pm_sDescricao;
    var $pm_fValor;
    var $pm_fPercDesconto;
    var $pm_blAtivo;
    var $pm_sNomeImagem;
    var $pm_dDataInclusao;
    var $pm_iPinValor;
    var $nomeProduto;
    var $codOperador;
    var $ogpm_pin_resquest_id;
    var $ogpm_pin_valor_markup;


    function ProdutoModelo(	$pm_id 				= null,
    						$pm_p_id 			= null,
    						$pm_sNome 			= null,
    						$pm_sDescricao 		= null,
    						$pm_fValor 			= null,
    						$pm_fPercDesconto	= null,
    						$pm_blAtivo 		= null,
    						$pm_sNomeImagem 	= null,
    						$pm_dDataInclusao 	= null,
    						$pm_iPinValor 		= null,
                                                $nomeProduto            = null,
                                                $codOperador            = null,
                                                $ogpm_pin_resquest_id	= null,
                                                $ogpm_pin_valor_markup	= null) {

    	$this->setId($pm_id);
    	$this->setProdutoId($pm_p_id);
    	$this->setNome($pm_sNome);
    	$this->setDescricao($pm_sDescricao);
    	$this->setValor($pm_fValor);
    	$this->setPercDesconto($pm_fPercDesconto);
    	$this->setAtivo($pm_blAtivo);
    	$this->setNomeImagem($pm_sNomeImagem);
    	$this->setDataInclusao($pm_dDataInclusao);
    	$this->setPinValor($pm_iPinValor);
        $this->setNomeProduto($nomeProduto);
        $this->setCodOperador($codOperador);
        $this->setPinRequestId($ogpm_pin_resquest_id);
        $this->setValorMarkup($ogpm_pin_valor_markup);
    }
    
    public function getPinRequestId() {
         return $this->ogpm_pin_resquest_id;
    }

    public function setPinRequestId($ogpm_pin_resquest_id) {
         $this->ogpm_pin_resquest_id = $ogpm_pin_resquest_id;
    }
    
    public function getCodOperador() {
        return $this->codOperador;
    }

    public function setCodOperador($codOperador) {
        $this->codOperador = $codOperador;
        return $this;
    }

        public function getNomeProduto() {
        return $this->nomeProduto;
    }

    public function setNomeProduto($nomeProduto) {
        $this->nomeProduto = $nomeProduto;
        return $this;
    }
    
    function getId(){
    	return $this->pm_id;
    }
    function setId($pm_id){
    	$this->pm_id = $pm_id;
    }
    
    function getProdutoId(){
    	return $this->pm_p_id;
    }
    function setProdutoId($pm_p_id){
    	$this->pm_p_id = $pm_p_id;
    }
    
    function getNome(){
    	return $this->pm_sNome;
    }
    function setNome($pm_sNome){
    	$this->pm_sNome = $pm_sNome;
    }
    
    function getDescricao(){
    	return $this->pm_sDescricao;
    }
    function setDescricao($pm_sDescricao){
    	$this->pm_sDescricao = $pm_sDescricao;
    }
    
    function getValor(){
    	return $this->pm_fValor;
    }
    function setValor($pm_fValor){
    	$this->pm_fValor = $pm_fValor;
    }
    
    function getPercDesconto(){
    	return $this->pm_fPercDesconto;
    }
    function setPercDesconto($pm_fPercDesconto){
    	$this->pm_fPercDesconto = $pm_fPercDesconto;
    }
    
    function getAtivo(){
    	return $this->pm_blAtivo;
    }
    function setAtivo($pm_blAtivo){
		if($pm_blAtivo == 1 || $pm_blAtivo == "1" || $pm_blAtivo === "true") $pm_blAtivo = "1";
		else $pm_blAtivo = "0";
    	$this->pm_blAtivo = $pm_blAtivo;
    }
    
    function getNomeImagem(){
    	return $this->pm_sNomeImagem;
    }
    function setNomeImagem($pm_sNomeImagem){
    	$this->pm_sNomeImagem = $pm_sNomeImagem;
    }
    
    function getDataInclusao(){
    	return $this->pm_dDataInclusao;
    }
    function setDataInclusao($pm_dDataInclusao){
    	$this->pm_dDataInclusao = $pm_dDataInclusao;
    }
    
    function getPinValor(){
    	return $this->pm_iPinValor;
    }
    function setPinValor($pm_iPinValor){
    	$this->pm_iPinValor = $pm_iPinValor;
    }
    
    function getValorMarkup(){
    	return $this->ogpm_pin_valor_markup;
    }
    function setValorMarkup($ogpm_pin_valor_markup){
    	$this->ogpm_pin_valor_markup = $ogpm_pin_valor_markup;
    }
        
    function inserir(&$objProdutoModelo){
 
 		$ret = ProdutoModelo::validarCampos($objProdutoModelo);
 
 		if($ret == ""){
 			$sql = "insert into tb_dist_operadora_games_produto_modelo(" .
 					"ogpm_ogp_id, ogpm_nome, ogpm_descricao, ogpm_valor, ogpm_perc_desconto, ogpm_ativo, " .
 					"ogpm_nome_imagem, ogpm_pin_valor, ogpm_data_inclusao, ogpm_pin_resquest_id, ogpm_pin_valor_markup) values (";
 			$sql .= SQLaddFields($objProdutoModelo->getProdutoId(), "") . ",";
 			$sql .= SQLaddFields($objProdutoModelo->getNome(), "s") . ",";
 			$sql .= SQLaddFields($objProdutoModelo->getDescricao(), "s") . ",";
 			$sql .= SQLaddFields(moeda2numeric($objProdutoModelo->getValor()), "") . ",";
 			$sql .= SQLaddFields(moeda2numeric($objProdutoModelo->getPercDesconto()), "") . ",";
 			$sql .= SQLaddFields($objProdutoModelo->getAtivo(), "") . ",";
 			$sql .= SQLaddFields($objProdutoModelo->getNomeImagem(), "s") . ",";
 			$sql .= SQLaddFields($objProdutoModelo->getPinValor(), "") . ",";
 			$sql .= SQLaddFields("CURRENT_TIMESTAMP", "") . ",";
			$sql .= ($objProdutoModelo->getPinRequestId() == ""? "NULL": SQLaddFields($objProdutoModelo->getPinRequestId(), "s")) . ",";
 			$sql .= SQLaddFields($objProdutoModelo->getValorMarkup(), "") . ");";
		
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $ret = "Erro ao inserir modelo.\n";
			else{
				$ret = "";				
				$rs_id = SQLexecuteQuery("select currval('dist_operadora_games_produto_modelo_id_seq') as last_id");
				if($rs_id && pg_num_rows($rs_id) > 0){
					$rs_id_row = pg_fetch_array($rs_id);
					$objProdutoModelo->setId($rs_id_row['last_id']);
				}					
			}			
 		}
 		
 		return $ret;   	
    }
    
    function atualizar($objProdutoModelo){
 
 		$ret = ProdutoModelo::validarCampos($objProdutoModelo);
 
 		if($ret == ""){
 			$sql = "update tb_dist_operadora_games_produto_modelo set ";
 			if(!is_null($objProdutoModelo->getProdutoId())) 	$sql .= " ogpm_ogp_id = " 	  .	SQLaddFields($objProdutoModelo->getProdutoId(), "") . ",";
 			if(!is_null($objProdutoModelo->getNome())) 			$sql .= " ogpm_nome = " 	  . SQLaddFields($objProdutoModelo->getNome(), "s") . ",";
 			if(!is_null($objProdutoModelo->getDescricao())) 	$sql .= " ogpm_descricao = "  . SQLaddFields($objProdutoModelo->getDescricao(), "s") . ",";
 			if(!is_null($objProdutoModelo->getValor())) 		$sql .= " ogpm_valor = " 	  .	SQLaddFields(moeda2numeric($objProdutoModelo->getValor()), "") . ",";
 			if(!is_null($objProdutoModelo->getPercDesconto()))	$sql .= " ogpm_perc_desconto=".	SQLaddFields(moeda2numeric($objProdutoModelo->getPercDesconto()), "") . ",";
 			if(!is_null($objProdutoModelo->getAtivo())) 		$sql .= " ogpm_ativo = " 	  . SQLaddFields($objProdutoModelo->getAtivo(), "") . ",";
 			if(!is_null($objProdutoModelo->getNomeImagem()))	$sql .= " ogpm_nome_imagem = ". SQLaddFields($objProdutoModelo->getNomeImagem(), "s") . ", ";
 			if(!is_null($objProdutoModelo->getPinValor()))		$sql .= " ogpm_pin_valor = "  . SQLaddFields($objProdutoModelo->getPinValor(), "") . ", ";
			if(!is_null($objProdutoModelo->getPinRequestId()))	$sql .= " ogpm_pin_resquest_id = ". ($objProdutoModelo->getPinRequestId() == ""? "NULL": SQLaddFields($objProdutoModelo->getPinRequestId(), "s")) . ", ";
 			if(!is_null($objProdutoModelo->getValorMarkup()))	$sql .= " ogpm_pin_valor_markup = "  . SQLaddFields($objProdutoModelo->getValorMarkup(), "") . " ";
			$sql .= " where ogpm_id = " . SQLaddFields($objProdutoModelo->getId(), "");
			
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $ret = "Erro ao atualizar modelo.\n";
			else $ret = "";

 		}
 		
 		return $ret;   	
    }
    
	function validarCampos($objProdutoModelo){
		
		$ret = "";
		
		//ProdutoId
 		$produtoId = $objProdutoModelo->getProdutoId();
 		if(is_null($produtoId) || $produtoId == 0) 	$ret .= "C�digo do produto inv�lido.\n";
 		elseif(!is_numeric($produtoId)) 			$ret .= "C�digo do produto deve ser num�rico.\n";
 		
		//Nome
 		$nome = $objProdutoModelo->getNome();
 		if(!is_null($nome)){
 			if(strlen($nome) > 100) 		$ret .= "O nome deve ter at� 100 caracteres.\n";
 		}
				
		//Descricao
 		$descricao = $objProdutoModelo->getDescricao();
 		if(is_null($descricao) || $descricao == "") $ret .= "A Descri��o deve ser preenchida.\n";
 		elseif(strlen($descricao) > 1024) 			$ret .= "A Descri��o deve ter at� 1024 caracteres.\n";
 		
		//Valor
 		$valor = $objProdutoModelo->getValor();
 		if(is_null($valor)) 		$ret .= "Valor deve ser preenchido.\n";
 		elseif(!is_moeda($valor)) 	$ret .= "Valor inv�lido.1\n";

		//PercDesconto
 		$percDesconto = $objProdutoModelo->getPercDesconto();
 		if(is_null($percDesconto)) 		$ret .= "Percentual de desconto deve ser preenchido.\n";
 		elseif(!is_moeda($percDesconto))$ret .= "Percentual de desconto inv�lido.2\n";

		//ativo
 		$ativo = $objProdutoModelo->getAtivo();
 		if(is_null($ativo) || $ativo == "") $ret .= "O status deve ser selecionado.\n";
		else if(!is_numeric($ativo)) $ret .= "O status deve ser n�merico.\n";

		//NomeImagem
 		$nomeImagem = $objProdutoModelo->getNomeImagem();
 		if(!is_null($nomeImagem)){
 			if(strlen($nomeImagem) > 100) $ret .= "O Nome da Imagem deve ter at� 100 caracteres.\n";
 		}
 		
		//PinValor
		
 		$pinValor = $objProdutoModelo->getPinValor();
 		if(is_null($pinValor)) 			$ret .= "Valor do PIN inv�lido.\n";
 		elseif(!is_numeric($pinValor)) 	$ret .= "Valor do PIN deve ser num�rico.\n";
 		elseif($pinValor < 0) 			$ret .= "Valor do PIN deve ser maior que zero.\n";

 		return $ret;
	}

	function obter($filtro, $orderBy, &$rs){

		$ret = "";
		$filtro = array_map("strtoupper", $filtro);
	
		$sql = "select * from tb_dist_operadora_games_produto_modelo ogpm ";
		if(!is_null($filtro['com_produto'])) $sql .= "inner join tb_dist_operadora_games_produto ogp on ogp.ogp_id = ogpm.ogpm_ogp_id";

		if(!is_null($filtro) && $filtro != ""){
			
			if(!is_null($filtro['ogpm_data_inclusaoMin']) && !is_null($filtro['ogpm_data_inclusaoMax'])){
				$filtro['ogpm_data_inclusaoMin'] = formata_data_ts($filtro['ogpm_data_inclusaoMin'] . " 00:00:00", 1, true, true);
				$filtro['ogpm_data_inclusaoMax'] = formata_data_ts($filtro['ogpm_data_inclusaoMax'] . " 23:59:59", 1, true, true);
			}			
			
			$sql .= " where 1=1";
			
			$sql .= " and (" . (is_null($filtro['ogpm_id'])?1:0);
			$sql .= "=1 or ogpm.ogpm_id = " . SQLaddFields($filtro['ogpm_id'], "") . ")";
			
			$sql .= " and (" . (is_null($filtro['ogpm_ogp_id'])?1:0);
			$sql .= "=1 or ogpm.ogpm_ogp_id = " . SQLaddFields($filtro['ogpm_ogp_id'], "") . ")";

			$sql .= " and (" . (is_null($filtro['ogpm_nome'])?1:0);
			$sql .= "=1 or upper(ogpm.ogpm_nome) = '" . SQLaddFields($filtro['ogpm_nome'], "r") . "')";
			
			$sql .= " and (" . (is_null($filtro['ogpm_nomeLike'])?1:0);
			$sql .= "=1 or upper(ogpm.ogpm_nome) like '%" . SQLaddFields($filtro['ogpm_nomeLike'], "r") . "%')";
			
			$sql .= " and (" . (is_null($filtro['ogpm_descricao'])?1:0);
			$sql .= "=1 or upper(ogpm.ogpm_descricao) = '" . SQLaddFields($filtro['ogpm_descricao'], "r") . "')";
			
			$sql .= " and (" . (is_null($filtro['ogpm_descricaoLike'])?1:0);
			$sql .= "=1 or upper(ogpm.ogpm_descricao) like '%" . SQLaddFields($filtro['ogpm_descricaoLike'], "r") . "%')";
			
			$sql .= " and (" . (is_null($filtro['ogpm_valorMin']) || is_null($filtro['ogpm_valorMax'])?1:0);
			$sql .= "=1 or ogpm.ogpm_valor between " . SQLaddFields($filtro['ogpm_valorMin'], "") . " and " . SQLaddFields($filtro['ogpm_valorMax'], "") . ")";

			$sql .= " and (" . (is_null($filtro['ogpm_perc_descontoMin']) || is_null($filtro['ogpm_perc_descontoMax'])?1:0);
			$sql .= "=1 or ogpm.ogpm_perc_desconto between " . SQLaddFields($filtro['ogpm_perc_descontoMin'], "") . " and " . SQLaddFields($filtro['ogpm_perc_descontoMax'], "") . ")";

			$sql .= " and (" . (is_null($filtro['ogpm_ativo'])?1:0);
			$sql .= "=1 or ogpm.ogpm_ativo = " . SQLaddFields($filtro['ogpm_ativo'], "") . ")";
			
			$sql .= " and (" . (is_null($filtro['ogpm_nome_imagem'])?1:0);
			$sql .= "=1 or upper(ogpm.ogpm_nome_imagem) = '" . SQLaddFields($filtro['ogpm_nome_imagem'], "r") . "')";
			
			$sql .= " and (" . (is_null($filtro['ogpm_nome_imagemLike'])?1:0);
			$sql .= "=1 or upper(ogpm.ogpm_nome_imagem) like '%" . SQLaddFields($filtro['ogpm_nome_imagemLike'], "r") . "%')";
			
			$sql .= " and (" . (is_null($filtro['ogpm_data_inclusaoMin']) || is_null($filtro['ogpm_data_inclusaoMax'])?1:0);
			$sql .= "=1 or ogpm.ogpm_data_inclusao between " . SQLaddFields($filtro['ogpm_data_inclusaoMin'], "") . " and " . SQLaddFields($filtro['ogpm_data_inclusaoMax'], "") . ")";
			
			$sql .= " and (" . (is_null($filtro['ogpm_pin_valor'])?1:0);
			$sql .= "=1 or ogpm.ogpm_pin_valor = " . SQLaddFields($filtro['ogpm_pin_valor'], "") . ")";

		}
		
		if(!is_null($orderBy)) $sql .= " order by " . $orderBy;
                
		$rs = SQLexecuteQuery($sql);
		if(!$rs) $ret = "Erro ao obter modelo(s).\n";

		return $ret;

	}

    function excluir($produto_modelo_id){
 
 		$ret = "";
		
		if(!$produto_modelo_id) $ret = "C�digo do modelo n�o fornecido.\n";
		elseif(!is_numeric($produto_modelo_id)) $ret = "C�digo do modelo inv�lido.\n";
 
 		if($ret == ""){
 			$sql = "delete from tb_dist_operadora_games_produto_modelo ";
			$sql .= " where ogpm_id = " . SQLaddFields($produto_modelo_id, "");
			
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $ret = "Erro ao excluir modelo.\n";
			else $ret = "";

 		}
 		
 		return $ret;   	
    }
    
    function contar($opr_codigo, $pin_valor){
 
 		$ret = "";
		
		if(!$opr_codigo) $ret = "0";
		elseif(!is_numeric($opr_codigo)) $ret = "0";
 
 		if($ret == ""){
 			$sql = "select count(*) as quantidade from pins ";
			$sql .= "where opr_codigo = " . SQLaddFields($opr_codigo, "") . " "; 
			$sql .= "and pin_valor = " . SQLaddFields($pin_valor, "") . " "; 
			$sql .= "and pin_canal = 's' "; 
			$sql .= "and pin_status = '1' "; 
			$sql .= "group by opr_codigo, pin_valor, pin_status";
			$rs_count = SQLexecuteQuery($sql);
			if($rs_count && pg_num_rows($rs_count) > 0){
				$rs_count_value = pg_fetch_array($rs_count);
				$ret = $rs_count_value['quantidade'];
			}
			else {
				$ret = "0";
			}
 		}
 		
 		return $ret;   	
    }


}

?>
