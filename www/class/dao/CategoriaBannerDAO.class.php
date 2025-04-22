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
        
        if($filtro != "")
            $sql .= " where ".$filtro;
        
        $sql .= " order by bsc_data_cadastro desc";
        
        try{
            $categorias = SQLexecuteQuery($sql);
            $totalLinhas = pg_num_rows($categorias);
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
            $sql = vsprintf(
                    "insert into tb_banner_store_categorias
                    (bsc_descricao, bsc_data_cadastro, bsc_status) values ('%s', CURRENT_DATE, %s)",array(
                                                                            $categoria->getDescricao(),
                                                                            $categoria->getStatus() 
                                                                        )
                    );
            
            $retorno = SQLexecuteQuery($sql);
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
            $sql = vsprintf(
                    "update 
                        tb_banner_store_categorias 
                    set
                        bsc_descricao = '%s',
                        bsc_status = %s
                    where 
                        bsc_id = %s;",array(
                                        $categoria->getDescricao(),
                                        $categoria->getStatus(),
                                        $categoria->getId()
                                    )
                    );
            $retorno = SQLexecuteQuery($sql);
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