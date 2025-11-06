<?php
class ClickBannerDAO {
    
    public $clicks = array();
    protected $erros = array();
    
    public function get($sql, $params = array()){
        
        $this->clicks = array();
        $clicks = SQLexecuteQueryParams($sql, $params);
        $totalLinhas = pg_num_rows($clicks);
        if($totalLinhas > 0){
            
            while($lineRow = pg_fetch_array($clicks)){
                $objClicks = new ClickBannerVO;
                $objClicks->setClicks($lineRow["clicks"]);
                if(isset($lineRow["bs_titulo"])) $objClicks->setTitulo($lineRow["bs_titulo"]);
                
                $this->clicks[] = $objClicks;
                unset($objClicks);
            }
        }
        
        return $this->clicks;
    }
    
    public function insert($id){
        $sql = "insert into tb_banner_store_clicks (bs_click_data_cadastro, bs_id) values (CURRENT_DATE, $1)";
        
        $clicks = SQLexecuteQueryParams($sql, [$id]);
        if($clicks) {
            return true;
        }else{
            $this->erros[] = "ERRO AO INSERIR CLICK NO BANNER. Query: $sql ".PHP_EOL;
            return false;
        }
    }
}
?>