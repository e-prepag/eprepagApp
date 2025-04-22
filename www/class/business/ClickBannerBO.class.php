<?php
require_once $raiz_do_projeto."class/dao/ClickBannerDAO.class.php";
require_once $raiz_do_projeto."class/view/ClickBannerVO.class.php";

class ClickBannerBO extends ClickBannerDAO{
       
    public function pegaClicksBanner($id){

        try{
            $sql = "select count(*) as clicks 
                from tb_banner_store_clicks where bs_id = $id";
            $obj = $this->get($sql);
            
            if(!empty($this->erros))
                throw new Exception;
            
            return $obj;
            
        } catch (Exception $ex) {
            //faz o que com o erro
            Util::showArrError($this->erros);
            $geraLog = new Log("CLICKBANNERSBOpegaClickBanner",array("ERROR: ".  implode(" / ", $this->erros),
                                              "FILE: ".$ex->getFile(),
                                              "LINE ".$ex->getLine()));
            return false;
        }
    }
    
    public function pegaClicksBannerBusca($post, BannerBO $objBanners){
        
        $sql = "select count(*) as clicks, bs_titulo from tb_banner_store_clicks as bsc inner join tb_banner_store as bs on bs.bs_id = bsc.bs_id ";

        try{
            if(!empty($post["bs_id"]))              $where[] = "bsc.bs_id = {$post['bs_id']}";
            if($post["dataClickIni"] != "")         $where[] = "bs_click_data_cadastro >= '".Util::getData($post['dataClickIni'],true)."'";
            if($post["dataClickFim"] != "")         $where[] = "bs_click_data_cadastro <= '".Util::getData($post['dataClickFim'],true)."'";
            if (isset($where) && is_array($where))  $sql .= ' WHERE ' . implode(' AND ', $where);
            $sql .= " group by bs_titulo";
            $obj = $this->get($sql);

            if(!empty($this->erros))
                throw new Exception;
            
            return $obj;
            
        } catch (Exception $ex) {
            //faz o que com o erro
            Util::showArrError($this->erros);
            $geraLog = new Log("CLICKBANNERSBOpegaClickBanner",array("ERROR: ".  implode(" / ", $this->erros),
                                              "FILE: ".$ex->getFile(),
                                              "LINE ".$ex->getLine()));
            return false;
        }
    }
    
    public function insereClickBanner($id){
        $this->erros = array();
        
        try{
            if($this->insert($id))
                return true;
            else
                throw new Exception;
            
        } catch (Exception $ex) {
            Util::showArrError($this->erros);
            $geraLog = new Log("CLICKBANNERSBOinsereClickBanner",array("ERROR: ".  implode(" / ", $this->erros),
                                              "FILE: ".$ex->getFile(),
                                              "LINE ".$ex->getLine()));
            return false;
        }
    }
} //end class
?>