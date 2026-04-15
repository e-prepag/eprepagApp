<?php
require_once $raiz_do_projeto."class/dao/CategoriaBannerDAO.class.php";
require_once $raiz_do_projeto."class/view/CategoriaBannerVO.class.php";

class CategoriaBannerBO extends CategoriaBannerDAO{
    
    public function pegaCategoria($where = ""){
        try{
            $obj = $this->get($where);
            
            if(!empty($this->erros))
                throw new Exception;
            
            return $obj;
            
        } catch (Exception $ex) {
            //faz o que com o erro
            Util::showArrError($this->erros);
            $geraLog = new Log("BANNERSBOpegaCategoria",array("ERROR: ".  implode(" / ", $this->erros),
                                              "FILE: ".$ex->getFile(),
                                              "LINE ".$ex->getLine()));
            return false;
        }
        
        
        
    }
    
    public function insereCategoria($post){
        $this->erros = array();
        
        try{
            
            if(!$this->validaCategoriaBanner($post))
            {
                $this->erros[] = "Dados inválidos, confira-os e tente novamente.";
                throw new Exception;
            }
            
            $categoria = new CategoriaBannerVO;
            $categoria->setDataCadastro("CURRENT_DATE");
            $categoria->setDescricao(Util::cleanStr2($post["bsc_descricao"]));
            $categoria->setStatus($post["bsc_status"]);
            
            if($this->insert($categoria))
                return true;
            else
                throw new Exception;
            
        } catch (Exception $ex) {
            //faz o que com o erro
            Util::showArrError($this->erros);
            $geraLog = new Log("BANNERSBOinsereCategoria",array("ERROR: ".  implode(" / ", $this->erros),
                                              "FILE: ".$ex->getFile(),
                                              "LINE ".$ex->getLine()));
            $this->erros = array();
            return false;
        }
    }
    
    public function editaCategoria($post){
        $this->erros = array();
        
        try{
            
            if(!isset($post["idbc"]) || 
                $post["idbc"] == "" || 
                !$this->validaCategoriaBanner($post))
            {
                $this->erros[] = "Dados inválidos, confira-os e tente novamente.";
                throw new Exception;
            }
            
            $categoria = new CategoriaBannerVO;
            $categoria->setId($post["idbc"]);
            $categoria->setDescricao($post["bsc_descricao"]);
            $categoria->setStatus($post["bsc_status"]);
            
            $this->update($categoria);
            unset($categoria);
            
            if(empty($this->erros))
                return true;
            else
                throw new Exception;
            
        } catch (Exception $ex) {
            //faz o que com o erro
            Util::showArrError($this->erros);
            $geraLog = new Log("BANNERSBOeditaCategoria",array("ERROR: ".  implode(" / ", $this->erros),
                                              "FILE: ".$ex->getFile(),
                                              "LINE ".$ex->getLine()));
            
            $this->erros = array();
            return false;
        }
    }
    
    public function validaCategoriaBanner($post){
        return (!$post['bsc_descricao'] ||
                $post['bsc_status'] === "" ||
                Util::cleanStr2($post["bsc_descricao"]) !== $post["bsc_descricao"]) ?
                    false : true;
            
    }
    
} //end class
?>