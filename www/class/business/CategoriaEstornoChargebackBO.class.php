<?php
/**
 * Classe Business Object das categorias de Estorno e Chargeback
 *
 * @author Wagner de Miranda
 * @email wagner.mbis@gmail.com
 * @date 06-10-2015
 */

if(!isset($raiz_do_projeto))
    $raiz_do_projeto = "/www/";

require_once $raiz_do_projeto."/class/dao/CategoriaEstornoChargebackDAO.class.php";
require_once $raiz_do_projeto."/class/util/Log.class.php";
require_once $raiz_do_projeto."/class/util/Util.class.php";
require_once $raiz_do_projeto."/class/view/CategoriaEstornoChargebackVO.class.php";

class CategoriaEstornoChargebackBO extends CategoriaEstornoChargebackDAO{
    
    public function pegaCategoria($where = ""){
        try{
            $obj = $this->get($where);
            
            if(!empty($this->erros))
                throw new Exception;
            
            return $obj;
            
        } catch (Exception $ex) {
            //faz o que com o erro
            Util::showArrError($this->erros);
            $geraLog = new Log("CategoriaEstornoChargebackBOpegaCategoria",array("ERROR: ".  implode(" / ", $this->erros),
                                              "FILE: ".$ex->getFile(),
                                              "LINE ".$ex->getLine()));
            return false;
        }
        
        
        
    }
    
    public function insereCategoria($post){
        $this->erros = array();
        
        try{
            
            if(!$this->validaCategoriaEstornoChargeback($post))
            {
                $this->erros[] = "Dados inválidos";
                throw new Exception;
            }
            
            $categoria = new CategoriaEstornoChargebackVO;
            $categoria->setDescricao($post["cec_descricao"]);
            $categoria->setStatus($post["cec_status"]);
            
            if($this->insert($categoria))
                return true;
            else
                throw new Exception;
            
        } catch (Exception $ex) {
            //faz o que com o erro
            Util::showArrError($this->erros);
            $geraLog = new Log("CategoriaEstornoChargebackBOinsereCategoria",array("ERROR: ".  implode(" / ", $this->erros),
                                              "FILE: ".$ex->getFile(),
                                              "LINE ".$ex->getLine()));
            return false;
        }
    }
    
    public function editaCategoria($post){
        $this->erros = array();
        
        try{
            
            if(!isset($post["id"]) || 
                $post["id"] == "" || 
                !$this->validaCategoriaEstornoChargeback($post))
            {
                $this->erros[] = "Dados inválidos";
                throw new Exception;
            }
            
            $categoria = new CategoriaEstornoChargebackVO;
            $categoria->setId($post["id"]);
            $categoria->setDescricao($post["cec_descricao"]);
            $categoria->setStatus($post["cec_status"]);
            
            $this->update($categoria);
            unset($categoria);
            
            if(empty($this->erros))
                return true;
            else
                throw new Exception;
            
        } catch (Exception $ex) {
            //faz o que com o erro
            Util::showArrError($this->erros);
            $geraLog = new Log("CategoriaEstornoChargebackBOeditaCategoria",array("ERROR: ".  implode(" / ", $this->erros),
                                              "FILE: ".$ex->getFile(),
                                              "LINE ".$ex->getLine()));
            return false;
        }
    }
    
    public function validaCategoriaEstornoChargeback($post){
        return (!$post['cec_descricao'] ||
                  $post['cec_status'] === "") ?
                    false : true;
            
    }
    
} //end class CategoriaEstornoChargebackBO
