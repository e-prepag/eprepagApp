<?php
require_once $raiz_do_projeto."class/dao/PosicaoBannerDAO.class.php";
require_once $raiz_do_projeto."class/view/PosicaoBannerVO.class.php";

class PosicaoBannerBO extends PosicaoBannerDAO{
    
    public function pegaPosicao($where = ""){
        try{
            $obj = $this->get($where);
            
            if(!empty($this->erros))
                throw new Exception;
            
            return $obj;
            
        } catch (Exception $ex) {
            //faz o que com o erro
            Util::showArrError($this->erros);
            $geraLog = new Log("BANNERSBOpegaPosicao",array("ERROR: ".  implode(" / ", $this->erros),
                                              "FILE: ".$ex->getFile(),
                                              "LINE ".$ex->getLine()));
            return false;
        }
    }
    
    public function inserePosicao($post){
        $this->erros = array();
        
        try{
            
            if(!$this->validaPosicaoBanner($post))
            {
                $this->erros[] = "Dados inválidos, confira-os e tente novamente.";
                throw new Exception;
            }
            
            $posicao = new PosicaoBannerVO;
            $posicao->setDataCadastro("CURRENT_DATE");
            $posicao->setDescricao(Util::cleanStr2($post["bsp_descricao"]));
            $posicao->setTamanho($post["bsp_tamanho"]);
            $posicao->setStatus($post["bsp_status"]);
            
            if($this->insert($posicao))
                return true;
            else
                throw new Exception;
            
        } catch (Exception $ex) {
            Util::showArrError($this->erros);
            $geraLog = new Log("BANNERSBOinserePosicao",array("ERROR: ".  implode(" / ", $this->erros),
                                              "FILE: ".$ex->getFile(),
                                              "LINE ".$ex->getLine()));
            $this->erros = array();
            return false;
        }
    }
    
    public function editaPosicao($post){
        $this->erros = array();
        
        try{
            
            if(!isset($post["idbp"]) || 
                $post["idbp"] == "" || 
                !$this->validaPosicaoBanner($post))
            {
                $this->erros[] = "Dados inválidos, confira-os e tente novamente.";
                throw new Exception;
            }
            
            $posicao = new PosicaoBannerVO;
            $posicao->setId($post["idbp"]);
            $posicao->setDescricao($post["bsp_descricao"]);
            $posicao->setTamanho($post["bsp_tamanho"]);
            $posicao->setStatus($post["bsp_status"]);
            
            $this->update($posicao);
            unset($posicao);
            
            if(empty($this->erros))
                return true;
            else
                throw new Exception;
            
        } catch (Exception $ex) {
            Util::showArrError($this->erros);
            $geraLog = new Log("BANNERSBOinserePosicao",array("ERROR: ".  implode(" / ", $this->erros),
                                              "FILE: ".$ex->getFile(),
                                              "LINE ".$ex->getLine()));
            $this->erros = array();
            return false;
        }
    }
    
    public function validaPosicaoBanner($post){
        return (!$post['bsp_descricao'] ||
                $post['bsp_status'] === "" ||
                Util::cleanStr2($post["bsp_descricao"]) !== $post["bsp_descricao"]) ?
                    false : true;
            
    }
    
} //end class

?>
