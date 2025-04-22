<?php

$raiz_do_projeto2 = $raiz_do_projeto;

require_once  "Log.class.php";
require_once  "Util.class.php";
require_once  "Json.class.php";

class Busca extends Json{
    public $strBusca;
    public $arrProduto;
    public $filtro;
    private $_jsonPath;
    private $_categoria = "Lan House";
    
    public function getCategoria() {
        return $this->_categoria;
    }

    public function setCategoria($categoria) {
        $this->_categoria = $categoria;
        return $this;
    }
        
    public function __construct($filtro = array(), $strBusca = "", $arrProduto = array(), $_jsonPath = "")
    {
        $this->strBusca = $strBusca;
        $this->arrProduto = $arrProduto;
        $this->_jsonPath = $_jsonPath;
    }

    public function setJsonPath($path)
    {
        $this->_jsonPath = $path;
    }
    
    public function setProduto($arrProdutos)
    {
        $this->arrProduto[] = $arrProdutos;
    }
    
    public function setFiltro($filtro)
    {
        $this->filtro = $filtro;
    }
    
    public function geraJson()
    {
        try {
            if(is_array($this->arrProduto))
            {
                if($this->refresh($this->arrProduto))
                    print "Json atualizado com sucesso.";
                else
                    print "Erro ao atualizar Json.";
            }else{
                throw new Exception("ERRO NA GERAÇÃO DO ARRAY DE PRODUTOS. ARRAY INVÁLIDO ".PHP_EOL);
            }
        } catch (Exception $ex) {
            echo $ex->getMessage();
            $geraLog = new Log("PRODUTOS",array($ex->getMessage(),
                                          "FILE: ".$ex->getFile(),
                                          "LINE ".$ex->getLine()));
            return false;
        }
    }
    
    public function getJson($term)
    {
        try{
            $obj = $this->getJsonRecursive();
            
            if($obj && $term != "")
            {
                $this->getArrObj($obj, $term);
                
                return json_encode($this->arrProduto);
            }else{
                throw new Exception("ERROR: JSON INVÁLIDO ".PHP_EOL);
            }
        } catch (Exception $ex) {
            $geraLog = new Log("LHBUSCA",array($ex->getMessage(),
                                          "FILE: ".$ex->getFile(),
                                          "LINE ".$ex->getLine()));
            return array();
        }
    }
    
    public function getArrObj($obj, $part)
    {
        $produtos = array();
        if($this->filtro['origem'] == "busca"){
            $produtos['games'] = $this->setObj($obj[0]->games,$part);
            //$produtos['servicos'] = $this->setObj($obj[1]->servico,$part);
            //$produtos['jogos'] = $this->setObj($obj[2]->jogos,$part);
            //para voltar com os produtos b2c, descomente as duas linhas acima
            
        }elseif($this->filtro['origem'] == "autocomplete")
        {
            /*
             * $objProdutos = (isset($this->filtro['b2c']) && $this->filtro['b2c']) ?
               array_merge($obj[0]->games,$obj[1]->servico,$obj[2]->jogos) : $obj[0]->games;
             */
            
            $objProdutos = $obj[0]->games;//para voltar com os produtos b2c, descomente o bloco acima e remova essa linha
            

            $produtos = $this->setObj($objProdutos, $part);
        }
        
        $this->arrProduto = $produtos;
    }
    
    private function setObj($objProdutos, $term)
    {
        $parts = explode(" ",$term);
        $link = "";
        $arr = array();
        
        foreach($parts as $part)
        {
            if(empty($part))
                continue;
            
            foreach($objProdutos as $produto)
            {
                if(strpos(Util::cleanStr2(strtoupper($produto->busca)), Util::cleanStr2(strtoupper($part))) !== false && strlen($part) >= 2)
                {
                    if($this->validaPermissaoProduto($produto))
                    {
                        /*
                        $link = ($produto->tipo == "games") ? "/creditos/produto/produto_detalhe.php?prod=" : "/creditos/servico/servico_detalhe.php?product=";
                         */
                        if($this->getCategoria() == "Lan House")
                            $link = "/creditos/produto/produto_detalhe.php?prod=";//para voltar com os produtos b2c, descomente o bloco acima e remova essa linha
                        else{
                            $link = "/game/produto/detalhe.php?prod=";
                        }
                        
                        $arrProduto["id"]    = $link.$produto->id;
                        $arrProduto["value"] = $produto->nome;
                        $arrProduto["label"] = $produto->nome;
                        $arrProduto["object"]= $produto;
                        $arr[$produto->nome] = $arrProduto;
                    }
                }
            }
        }
        
        return $arr;
    }
    
    public function getAllJsonByFilter(){
        try{
            $json = $this->getJsonRecursive();
            $link = "";
            if($json){
                foreach($json[0]->games as $produto)
                {
                    if($this->validaPermissaoProduto($produto))
                    {
                        /*
                        $link = ($produto->tipo == "games") ? "/creditos/produto/produto_detalhe.php?prod=" : "/creditos/servico/servico_detalhe.php?product=";
                         */

                        if($this->getCategoria() == "Lan House")
                            $link = "/creditos/produto/produto_detalhe.php?prod=";//para voltar com os produtos b2c, descomente o bloco acima e remova essa linha

                        $arrProduto["id"]    = $link.$produto->id;
                        $arrProduto["value"] = $produto->nome;
                        $arrProduto["label"] = $produto->nome;
                        $arrProduto["object"]= $produto;
                        $arr[strtoupper($produto->nome)] = $arrProduto;
                    }
                }
            }else{
                throw new Exception("ERRO NA TENTATIVA DE OBTER OS 3 JSON.");
            }
            
            return $arr;
        } catch (Exception $ex) {
             $geraLog = new Log("LHBUSCA",array($ex->getMessage(),
                                          "FILE: ".$ex->getFile(),
                                          "LINE ".$ex->getLine()));
            return array();
        }
    }
    
    private function validaPermissaoProduto($produto)
    {
        $ret = true;
        //Retirando bloqueio do jogo novo apenas para o pdv teste

        if(!isset($this->filtro['id_user']))
            $this->filtro['id_user'] = false;
        //regra testada e está ok;
        if(isset($this->filtro['_ug_possui_restricao_produtos']) && $this->filtro['_ug_possui_restricao_produtos'] == 1 && isset($produto->filtro))
        {
            if ($produto->filtro->ogp_inibi_lojas_online == 1)
                $ret = false;
//              if($filtro['_ug_possui_restricao_produtos'] == 1)
//                  $filtro['ogp_inibi_lojas_online'] = '1';
        }    

        // LH Mousebox de Campinas não pode vender Habbo (ogp_id=5) nem Flyff (ogp_id=41)
        //regra testada e está ok;
        if ($this->filtro['id_user'] == 43) 
        {
            if ($produto->id == "5" || $produto->id == "41")
                $ret = false;
            //$filtro['ogp_codigo_negado_2'] = "5, 41"; //ogp.ogp_id not in
        }

        // 2010-12-06 - LH ZEUSPORTELLA só compra "Point Blank" da Ongame = cod: 63 //a partir de 2011-09-15 também compra "Ultimate Game Card" da PayByCash = cod: 36
        // reinaldolh -  || $this->usuarioId == 389
        // reinaldolh2 -  || $this->usuarioId == 468 
        //regra testada e está ok;
        if ($this->filtro['id_user'] == 4207)
        {
            if ($produto->id != "63" && $produto->id != "36")
                $ret = false;
            //$filtro['ogp_id_lista'] = "63, 36";//ogp.ogp_id in 
        }

        if ($this->filtro['id_user'] == 6161 || $this->filtro['id_user'] == 7323)
        {
            if ($produto->id != "93")
                $ret = false;
            //$filtro['ogp_id_lista'] = "93";//ogp.ogp_id in 
        }
        
        if(isset($this->filtro['gamer']) && $this->filtro['gamer']){
            if($produto->filtro->ogp_opr_codigo != $this->filtro['ogp_opr_codigo'])
                $ret = false;
        }
        
        return $ret;
    }
    
}