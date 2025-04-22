<?php
/**
 * Classe Business Object de Estorno e Chargeback
 *
 * @author Wagner de Miranda
 * @email wagner.mbis@gmail.com
 * @date 23-10-2015
 */

if(!isset($raiz_do_projeto)) 
    $raiz_do_projeto = "/www/";
if(!defined(RAIZ_DO_PROJETO))
    define(RAIZ_DO_PROJETO,"/www/");

require_once $raiz_do_projeto."/class/dao/EstornoChargebackDAO.class.php";
require_once $raiz_do_projeto."/class/util/Log.class.php";
require_once $raiz_do_projeto."/class/util/Util.class.php";
require_once $raiz_do_projeto."/class/view/EstornoChargebackVO.class.php";
require_once $raiz_do_projeto."/class/view/EstornoDadosBancariosVO.class.php";
require_once $raiz_do_projeto."/class/view/CategoriaEstornoChargebackVO.class.php";

class EstornoChargeBackBO extends EstornoChargebackDAO {
    
    public function pegaEstornoChargeBack($filtro = null, $limit = null){
        $this->erros = array();
        
        try{
            $obj = $this->get($filtro, $limit);
            
            if(!empty($this->erros))
                throw new Exception;
            
            return $obj;
            
        } catch (Exception $ex) {
            
            Util::showArrError($this->erros);
            $geraLog = new Log("BOpegaEstornoChargeBack",array("ERROR: ".  implode(" / ", $this->erros),
                                              "FILE: ".$ex->getFile(),
                                              "LINE ".$ex->getLine()));
            return false;
        }
    }
    
    public function insereEstornoChargeBack($post){
        $this->erros = array();
        
        try{
            if(!$this->validaEstornoChargeBack($post)){
                $this->erros[] = "Dados inválidos";
                throw new Exception;
            }
            
            $EstornoChargeBack = new EstornoChargeBackVO($post);
            if($post['ec_forma_devolucao'] == '2' && $post['ec_tipo'] == '2') {
                $EstornoDadosBancarios = new EstornoDadosBancariosVO($post);
            }
            else {
                $EstornoDadosBancarios = new EstornoDadosBancariosVO();
            }
            
            if($this->insert($EstornoChargeBack, $EstornoDadosBancarios))
                return true;
            else
                throw new Exception;
            
        } catch (Exception $ex) {
            Util::showArrError($this->erros);
            $geraLog = new Log("BOinsereEstornoChargeBack",array("ERROR: ".  implode(" / ", $this->erros),
                                              "FILE: ".$ex->getFile(),
                                              "LINE ".$ex->getLine()));
            return false;
        }
        
    } //end function insereEstornoChargeBack
    
    public function editaEstornoChargeBack($post){
        $this->erros = array();
        
        try{
            
            if(!isset($post["ec_id"]) || 
                $post["ec_id"] == "" || 
                !$this->validaEstornoChargeBack($post))
            {
                $this->erros[] = "Dados inválidos";
                throw new Exception;
            }
            
            $filtro["ec_id"] =  "ec.ec_id = ".$post["ec_id"];
            $EstornoChargeBackAntigo = $this->pegaEstornoChargeBack($filtro);
            if(empty($EstornoChargeBackAntigo))
            {
                $this->erros[] = "EstornoChargeBack nao encontrado";
                throw new Exception;
            }

            $EstornoChargeBack = new EstornoChargeBackVO($post);
            if($post['ec_forma_devolucao'] == '2' && $post['ec_tipo'] == '2') {
                $EstornoDadosBancarios = new EstornoDadosBancariosVO($post);
            }
            else {
                $EstornoDadosBancarios = new EstornoDadosBancariosVO();
            }
            $this->update($EstornoChargeBack,$post["ec_id"],$EstornoDadosBancarios);
            unset($EstornoChargeBack);
            unset($EstornoDadosBancarios);
            
            if(empty($this->erros))
                return true;
            else
                throw new Exception;
            
        } catch (Exception $ex) {
            Util::showArrError($this->erros);
            $geraLog = new Log("BOeditaEstornoChargeBack",array("ERROR: ".  implode(" / ", $this->erros),
                                              "FILE: ".$ex->getFile(),
                                              "LINE ".$ex->getLine()));
            return false;
        }
        
    }//end function editaEstornoChargeBack
    
    private function validaEstornoChargeBack($post){
        return 
        ( (substr_count($post["ec_data_devolucao"], '/') !=  2) ||
          !is_int($post["ec_pin_bloqueado"]*1) ||
          !is_int($post["cec_id"]*1) ||
          (strlen($post["ec_tipo_usuario"]) != 1) ||
          !(Util::getNumero($post["ec_valor"],true)*1 > 0) ||
          !is_int($post["ug_id"]*1) ||
          !is_int($post["ec_tipo"]*1) ||
          !(strlen($post["ec_nome"]) > 5) ||
          !is_int($post["vg_id"]*1) ||
          !is_int($post["opr_codigo"]*1)
        ) ? false : true;

    }//end function validaEstornoChargeBack
    
} //end class
