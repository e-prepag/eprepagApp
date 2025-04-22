<?php
/**
 * Classe Data Access Object das categorias de Estorno e Chargeback
 *
 * @author Wagner de Miranda
 * @email wagner.mbis@gmail.com
 * @date 06-10-2015
 
cec_id serial NOT NULL, -- ID de identificação da categoria nesta tabela.
cec_descricao character varying(256) NOT NULL, -- Campo contendo a descrição da categoria de Estorno e ChargeBack de pedidos.
cec_data_cadastro timestamp with time zone NOT NULL, -- Campo contendo a data de cadastro da categoria de Estorno e ChargeBack de pedidos
cec_status smallint NOT NULL DEFAULT 0, -- Campo contendo a ativação da categoria de Estorno e ChargeBack de pedidos. Onde 0 = Desativado e 1 = Ativado.

 */

class CategoriaEstornoChargebackDAO {
    
    public $categorias;
    protected $erros = array();
    
    public function get($filtro){
       
        $sql = "select 
                    *
                from 
                        categoria_estorno_chargeback";
        $this->categorias = array();
        
        if($filtro != "")
            $sql .= " where ".$filtro;
        
        $sql .= " order by cec_data_cadastro desc";
        
        try{
            $categorias = SQLexecuteQuery($sql);
            $totalLinhas = pg_num_rows($categorias);
            if($totalLinhas > 0){
                
                while($lineRow = pg_fetch_array($categorias)){
                    $categoria = new CategoriaEstornoChargebackVO(
                                                        $lineRow["cec_id"],
                                                        $lineRow["cec_descricao"],
                                                        $lineRow["cec_status"],
                                                        $lineRow["cec_data_cadastro"]
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
    
    public function insert(CategoriaEstornoChargebackVO $categoria){
        
        try {
            $sql = vsprintf(
                    "insert into categoria_estorno_chargeback
                    (cec_descricao, cec_data_cadastro, cec_status) values ('%s', CURRENT_DATE, %s)",array(
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
    
    public function update(CategoriaEstornoChargebackVO $categoria){
        try {
            $sql = vsprintf(
                    "update 
                        categoria_estorno_chargeback 
                    set
                        cec_descricao = '%s',
                        cec_status = %s
                    where 
                        cec_id = %s;",array(
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
    
    
    
} //end class CategoriaEstornoChargebackDAO
