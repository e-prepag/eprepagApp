<?php

class ProdutoModelo {
    
    var $pm_id;
    var $pm_p_id;
    var $pm_sNome;
    var $pm_sDescricao;
    var $pm_fValor;
    var $pm_blAtivo;
    var $pm_sNomeImagem;
    var $pm_dDataInclusao;
    var $pm_iPinValor;
    var $pm_iValorEPPCash;
    var $ogpm_pin_resquest_id;
    var $ogpm_pin_valor_markup;

/*
    function ProdutoModelo() {
    }
*/
    function ProdutoModelo(	$pm_id 				= null,
    						$pm_p_id 			= null,
    						$pm_sNome 			= null,
    						$pm_sDescricao 		= null,
    						$pm_fValor 			= null,
    						$pm_blAtivo 		= null,
    						$pm_sNomeImagem 	= null,
    						$pm_dDataInclusao 	= null,
    						$pm_iPinValor 		= null,
						$pm_iValorEPPCash	= null,
                                                $ogpm_pin_resquest_id	= null,
                                                $ogpm_pin_valor_markup	= null) {

    	$this->setId($pm_id);
    	$this->setProdutoId($pm_p_id);
    	$this->setNome($pm_sNome);
    	$this->setDescricao($pm_sDescricao);
    	$this->setValor($pm_fValor);
    	$this->setAtivo($pm_blAtivo);
    	$this->setNomeImagem($pm_sNomeImagem);
    	$this->setDataInclusao($pm_dDataInclusao);
    	$this->setPinValor($pm_iPinValor);
    	$this->setValorEPPCash($pm_iValorEPPCash);
        $this->setPinRequestId($ogpm_pin_resquest_id);
        $this->setValorMarkup($ogpm_pin_valor_markup);

    }
    
    public function getPinRequestId() {
         return $this->ogpm_pin_resquest_id;
    }

    public function setPinRequestId($ogpm_pin_resquest_id) {
         $this->ogpm_pin_resquest_id = $ogpm_pin_resquest_id;
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
    
    function getValorEPPCash(){
    	return $this->pm_iValorEPPCash;
    }
    function setValorEPPCash($pm_iValorEPPCash){
    	$this->pm_iValorEPPCash = $pm_iValorEPPCash;
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
 			$sql = "insert into tb_operadora_games_produto_modelo(" .
 					"ogpm_ogp_id, ogpm_nome, ogpm_descricao, ogpm_valor, ogpm_ativo, " .
 					"ogpm_nome_imagem, ogpm_pin_valor, ogpm_valor_eppcash, ogpm_data_inclusao, ogpm_pin_resquest_id, ogpm_pin_valor_markup) values (";
 			$sql .= SQLaddFields($objProdutoModelo->getProdutoId(), "") . ",";
 			$sql .= SQLaddFields($objProdutoModelo->getNome(), "s") . ",";
 			$sql .= SQLaddFields($objProdutoModelo->getDescricao(), "s") . ",";
 			$sql .= SQLaddFields(str_replace(',','.',moeda2numeric($objProdutoModelo->getValor())), "") . ",";
 			$sql .= SQLaddFields($objProdutoModelo->getAtivo(), "") . ",";
 			$sql .= SQLaddFields($objProdutoModelo->getNomeImagem(), "s") . ",";
 			$sql .= SQLaddFields($objProdutoModelo->getPinValor(), "") . ",";
 			$sql .= SQLaddFields($objProdutoModelo->getValorEPPCash(), "") . ",";
 			$sql .= SQLaddFields("CURRENT_TIMESTAMP", "") . ",";
			$sql .= ($objProdutoModelo->getPinRequestId() == ""? "NULL": SQLaddFields($objProdutoModelo->getPinRequestId(), "s")) . ",";
 			$sql .= SQLaddFields($objProdutoModelo->getValorMarkup(), "") . ");";
//echo "objProdutoModelo->getValor(): ".$objProdutoModelo->getValor()."<br>";
//echo "moeda2numeric(objProdutoModelo->getValor()): ".moeda2numeric($objProdutoModelo->getValor())."<br>";
//	echo "$sql<br>";
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $ret = "Erro ao inserir modelo.\n";
			else{
				$ret = "";				
				$rs_id = SQLexecuteQuery("select currval('operadora_games_produto_modelo_id_seq') as last_id");
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
 			$sql = "update tb_operadora_games_produto_modelo set ";
 			if(!is_null($objProdutoModelo->getProdutoId())) $sql .= " ogpm_ogp_id = " 	  .	SQLaddFields($objProdutoModelo->getProdutoId(), "") . ",";
 			if(!is_null($objProdutoModelo->getNome())) 		$sql .= " ogpm_nome = " 	  . SQLaddFields($objProdutoModelo->getNome(), "s") . ",";
 			if(!is_null($objProdutoModelo->getDescricao())) $sql .= " ogpm_descricao = "  . SQLaddFields($objProdutoModelo->getDescricao(), "s") . ",";
 			if(!is_null($objProdutoModelo->getValor())) 	$sql .= " ogpm_valor = " 	  .	SQLaddFields(moeda2numeric($objProdutoModelo->getValor()), "") . ",";
 			if(!is_null($objProdutoModelo->getAtivo())) 	$sql .= " ogpm_ativo = " 	  . SQLaddFields($objProdutoModelo->getAtivo(), "") . ",";
 			if(!is_null($objProdutoModelo->getNomeImagem()))$sql .= " ogpm_nome_imagem = ". SQLaddFields($objProdutoModelo->getNomeImagem(), "s") . ", ";
 			if(!is_null($objProdutoModelo->getPinValor()))	$sql .= " ogpm_pin_valor = "  . SQLaddFields($objProdutoModelo->getPinValor(), "") . ", ";
 			if(!is_null($objProdutoModelo->getValorEPPCash()))	$sql .= " ogpm_valor_eppcash = "  . SQLaddFields($objProdutoModelo->getValorEPPCash(), "") . ", ";
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
 		if(is_null($produtoId) || $produtoId == 0) 	$ret .= "Código do produto inválido.\n";
 		elseif(!is_numeric($produtoId)) 			$ret .= "Código do produto deve ser numérico.\n";
 		
		//Nome
 		$nome = $objProdutoModelo->getNome();
 		if(!is_null($nome)){
 			if(strlen($nome) > 100) 		$ret .= "O nome deve ter até 100 caracteres.\n";
 		}
				
		//Descricao
 		$descricao = $objProdutoModelo->getDescricao();
 		if(is_null($descricao) || $descricao == "") $ret .= "A Descrição deve ser preenchida.\n";
 		elseif(strlen($descricao) > 1024) 			$ret .= "A Descrição deve ter até 1024 caracteres.\n";
 		
		//Valor
 		$valor = $objProdutoModelo->getValor();
//echo "".($valor+1)."<br>";
 		if(is_null($valor)) 		$ret .= "Valor deve ser preenchido.\n";
// 		elseif(!is_moeda($valor)) 	$ret .= "Valor inválido.\n";
 		
		//Valor
 		$valorepp = $objProdutoModelo->getValorEPPCash();
// 		if(is_null($valorepp)) 		$ret .= "Valor PIN EPPCash deve ser preenchido.\n";
 //		else
			if(!is_numeric($valorepp)) 	$ret .= "Valor PIN EPPCash inválido ('$valorepp' não é numérico).\n";
 		
		//ativo
 		$ativo = $objProdutoModelo->getAtivo();
 		if(is_null($ativo) || $ativo == "") $ret .= "O status deve ser selecionado.\n";
		else if(!is_numeric($ativo)) $ret .= "O status deve ser numérico.\n";

		//NomeImagem
 		$nomeImagem = $objProdutoModelo->getNomeImagem();
 		if(!is_null($nomeImagem)){
 			if(strlen($nomeImagem) > 100) $ret .= "O Nome da Imagem deve ter até 100 caracteres.\n";
 		}
 		
		//PinValor
 		$pinValor = $objProdutoModelo->getPinValor();
//echo "$pinValor<br>";
//die("");
 		if(is_null($pinValor)) 			$ret .= "Valor do PIN inválido.\n";
 		elseif(!is_numeric($pinValor)) 	$ret .= "Valor do PIN deve ser numérico.\n";
 		elseif($pinValor < 0) 			$ret .= "Valor do PIN deve ser maior que zero.\n";

 		return $ret;
	}

	function obter($filtro, $orderBy, &$rs){

		$ret = "";
		$filtro = array_map("strtoupper", $filtro);
	
		$sql = "select * from tb_operadora_games_produto_modelo ogpm  \n";
		if(!is_null($filtro['com_produto']) || b_isIntegracao() ) $sql .= "inner join tb_operadora_games_produto ogp on ogp.ogp_id = ogpm.ogpm_ogp_id \n";

		if(!is_null($filtro) && $filtro != ""){
			
			if(!is_null($filtro['ogpm_data_inclusaoMin']) && !is_null($filtro['ogpm_data_inclusaoMax'])){
				$filtro['ogpm_data_inclusaoMin'] = formata_data_ts($filtro['ogpm_data_inclusaoMin'] . " 00:00:00", 1, true, true);
				$filtro['ogpm_data_inclusaoMax'] = formata_data_ts($filtro['ogpm_data_inclusaoMax'] . " 23:59:59", 1, true, true);
			}			
			
			$sql .= " where 1=1";
			
			$sql .= " and (" . (is_null($filtro['ogpm_id'])?1:0);
			$sql .= "=1 or ogpm.ogpm_id = " . SQLaddFields($filtro['ogpm_id'], "") . ") \n";
			
			$sql .= " and (" . (is_null($filtro['ogpm_ogp_id'])?1:0);
			$sql .= "=1 or ogpm.ogpm_ogp_id = " . SQLaddFields($filtro['ogpm_ogp_id'], "") . ") \n";

			$sql .= " and (" . (is_null($filtro['ogpm_nome'])?1:0);
			$sql .= "=1 or upper(ogpm.ogpm_nome) = '" . SQLaddFields($filtro['ogpm_nome'], "r") . "') \n";
			
			$sql .= " and (" . (is_null($filtro['ogpm_nomeLike'])?1:0);
			$sql .= "=1 or upper(ogpm.ogpm_nome) like '%" . SQLaddFields($filtro['ogpm_nomeLike'], "r") . "%') \n";
			
			$sql .= " and (" . (is_null($filtro['ogpm_descricao'])?1:0);
			$sql .= "=1 or upper(ogpm.ogpm_descricao) = '" . SQLaddFields($filtro['ogpm_descricao'], "r") . "') \n";
			
			$sql .= " and (" . (is_null($filtro['ogpm_descricaoLike'])?1:0);
			$sql .= "=1 or upper(ogpm.ogpm_descricao) like '%" . SQLaddFields($filtro['ogpm_descricaoLike'], "r") . "%') \n";
			
			$sql .= " and (" . (is_null($filtro['ogpm_valorMin']) || is_null($filtro['ogpm_valorMax'])?1:0);
			$sql .= "=1 or ogpm.ogpm_valor between " . SQLaddFields($filtro['ogpm_valorMin'], "") . " and " . SQLaddFields($filtro['ogpm_valorMax'], "") . ") \n";

			// Customize - Start
			$sql_debug = "";

			// Trata casos de integração: produtos de integração estão inativos (para não aparecer na loja) mas aparecem aqui para contar no carrinho
			$filtro['ogp_integracao'] = 0;
			if(b_isIntegracao()) {
				$filtro['ogp_integracao'] = 1;
				$sql_debug .= " or (ogpm.ogpm_id in (".(($filtro['ogpm_id'])?$filtro['ogpm_id']:(-1)).")) \n";
			}
			if($filtro['ogp_integracao']==1) {
//				$sql .= " and (" . (is_null($filtro['ogp_integracao'])?1:0);
//				$sql .= "=1 or (ogp.ogp_integracao = " . SQLaddFields($filtro['ogp_integracao'], "") .")) \n";
				$sql_debug .= " or (ogp_integracao>0) or (ogpm_integracao>0) ";
			}

			// Debug reinaldops
			if($filtro['show_treinamento']==1 && ($filtro['ogpm_id']==282 || $filtro['ogpm_id']==283 || $filtro['ogpm_id']==284 || $filtro['ogpm_id']==285 || $filtro['ogpm_id']==286)) {
				$sql_debug .= " or (ogpm.ogpm_id in (282, 283, 284, 285, 286)) ";
			}
			// Customize - Stop

			if($filtro['ogp_drupal']==1 && isset($filtro['ogpm_id_list'])) {
				$sql .= " and (ogpm.ogpm_id in (".(($filtro['ogpm_id_list'])?$filtro['ogpm_id_list']:(-1)).")) \n";
			}

			$sql .= " and (" . (is_null($filtro['ogpm_ativo'])?1:0);
			$sql .= "=1 or (ogpm.ogpm_ativo = " . SQLaddFields($filtro['ogpm_ativo'], "") . " ".$sql_debug.")) \n";
			
			$sql .= " and (" . (is_null($filtro['ogpm_nome_imagem'])?1:0);
			$sql .= "=1 or upper(ogpm.ogpm_nome_imagem) = '" . SQLaddFields($filtro['ogpm_nome_imagem'], "r") . "') \n";
			
			$sql .= " and (" . (is_null($filtro['ogpm_nome_imagemLike'])?1:0);
			$sql .= "=1 or upper(ogpm.ogpm_nome_imagem) like '%" . SQLaddFields($filtro['ogpm_nome_imagemLike'], "r") . "%') \n";
			
			$sql .= " and (" . (is_null($filtro['ogpm_data_inclusaoMin']) || is_null($filtro['ogpm_data_inclusaoMax'])?1:0);
			$sql .= "=1 or ogpm.ogpm_data_inclusao between " . SQLaddFields($filtro['ogpm_data_inclusaoMin'], "") . " and " . SQLaddFields($filtro['ogpm_data_inclusaoMax'], "") . ") \n";
			
			$sql .= " and (" . (is_null($filtro['ogpm_pin_valor'])?1:0);
			$sql .= "=1 or ogpm.ogpm_pin_valor = " . SQLaddFields($filtro['ogpm_pin_valor'], "") . ") \n";

			$sql .= " and (" . (is_null($filtro['ogpm_valor_eppcash'])?1:0);
			$sql .= "=1 or ogpm.ogpm_valor_eppcash = " . SQLaddFields($filtro['ogpm_valor_eppcash'], "") . ") \n";

			// Debug reinaldops
			if($filtro['show_treinamento']==1 && $filtro['ogpm_ogp_id']==63) {
				$sql .= "or (ogpm.ogpm_ogp_id = ".SQLaddFields($filtro['ogpm_ogp_id']).") \n";
			}
		}		
		if(!is_null($orderBy)) $sql .= " order by " . $orderBy . " \n";

		$rs = SQLexecuteQuery($sql);
		if(!$rs) $ret = "Erro ao obter modelo(s).\n";

		return $ret;

	}
	function verificarPublisher($idprod, $filtro){
 
        $ret = "";
		if(!$idprod) $ret = "Código do modelo não fornecido.\n";
		elseif(!is_numeric($idprod)) $ret = "Código do modelo inválido.\n";

 		if($ret == ""){
 			
			if($filtro == ""){
			
				$sql = "select * from tb_operadora_games_produto pr inner join tb_operadora_games_produto_modelo prm on pr.ogp_id = prm.ogpm_ogp_id where prm.ogpm_id = ".$idprod.";";
			    $ret = SQLexecuteQuery($sql);
			
			}else{
			
			    $sql = "select * from tb_operadora_games_produto where ogp_id = ".$idprod.";";
			    $ret = SQLexecuteQuery($sql);
			
			}
			
 		}
 		
 		return $ret;   	
    }

    function excluir($produto_modelo_id){
 
 		$ret = "";
		
		if(!$produto_modelo_id) $ret = "Código do modelo não fornecido.\n";
		elseif(!is_numeric($produto_modelo_id)) $ret = "Código do modelo inválido.\n";
 
 		if($ret == ""){
 			$sql = "delete from tb_operadora_games_produto_modelo ";
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
//echo "<!-- sql=" . $sql . "--><br>";
			
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
    function getOperadora(){
 
 		$ret = "0";
 
 		//if($ret == "")
		{
			$sql = "select ogp_opr_codigo ";
			$sql .= "from tb_operadora_games_produto_modelo ogpm  ";
			$sql .= "	inner join tb_operadora_games_produto ogp on ogp.ogp_id = ogpm.ogpm_ogp_id ";
			$sql .= "where (ogpm.ogpm_id = ".$this->getId().")"; 
//echo "<!-- sql=" . $sql . "--><br>";
//echo "" . $sql . "<br>";
			
			$rs_opr = SQLexecuteQuery($sql);
			if($rs_opr && pg_num_rows($rs_opr) > 0){
				$rs_opr_value = pg_fetch_array($rs_opr);
				$ret = $rs_opr_value['ogp_opr_codigo'];
			}
			else {
				$ret = "0";
			}
 		}
 		
 		return $ret;   	
    }

}

?>
