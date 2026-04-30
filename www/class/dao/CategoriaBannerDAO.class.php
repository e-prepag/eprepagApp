<?php
class CategoriaBannerDAO {
    
    public $categorias;
    protected $erros = array();
    
    public function get($filtro){
       
        $sql = "select 
                    *
                from 
                        tb_banner_store_categorias";
        $this->categorias = array();
        
        $params = array();
        if($filtro != "") {
            if(preg_match('/^\s*(bsc_id|bsc_status)\s*=\s*(\d+)\s*$/', $filtro, $matches)) {
                $sql .= " where " . $matches[1] . " = $1";
                $params[] = (int) $matches[2];
            } else {
                $this->erros[] = "Filtro invalido";
                return false;
            }
        }
        
        $sql .= " order by bsc_data_cadastro desc";
        
        try{
            $categorias = count($params) ? SQLexecuteQueryParams($sql, $params) : SQLexecuteQuery($sql);
            $totalLinhas = (($categorias) ? pg_num_rows($categorias) : 0);
            if($totalLinhas > 0){
                
                while($lineRow = pg_fetch_array($categorias)){
                    $categoria = new CategoriaBannerVO(
                                                        $lineRow["bsc_id"],
                                                        $lineRow["bsc_descricao"],
                                                        $lineRow["bsc_status"],
                                                        $lineRow["bsc_data_cadastro"]
                                                    );
                    $this->categorias[] = $categoria;
                    unset($categoria);
                }

                return $this->categorias;
            }else{
                throw new Exception("FALHA NA OBTENCAO DAS CATEGORIAS DOS BANNERS $sql");
            }
        } catch (Exception $ex) {
            $this->erros[] = $ex->getMessage();
            return false;
        }
	
    }
    
    public function insert(CategoriaBannerVO $categoria){
        
        try {
            $sql = "insert into tb_banner_store_categorias
                    (bsc_descricao, bsc_data_cadastro, bsc_status) values ($1, CURRENT_DATE, $2)";
            
            $retorno = SQLexecuteQueryParams($sql, [$categoria->getDescricao(), $categoria->getStatus()]);
            if($retorno) {
                return true;
            }else{
                throw new Exception("ERRO AO INSERIR NOVA CATEGORIA. Query: $sql \n ");
            }
        } catch (Exception $ex) {
            $this->erros[] = $ex->getMessage();
            return false;
        }
    }
    
    public function update(CategoriaBannerVO $categoria){
        try {
            $sql = "update 
                        tb_banner_store_categorias 
                    set
                        bsc_descricao = $1,
                        bsc_status = $2
                    where 
                        bsc_id = $3;";
            $retorno = SQLexecuteQueryParams($sql, [$categoria->getDescricao(), $categoria->getStatus(), $categoria->getId()]);
            if($retorno) {
                return true;
            }else{
                throw new Exception("ERRO AO EDITAR CATEGORIA. Query: $sql \n ");
            }
        } catch (Exception $ex) {
            $this->erros[] = $ex->getMessage();
            return false;
        }
    }
    
    
    
}
?>