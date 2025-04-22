<?php
class PosicaoBannerDAO {
    
    public $posicoes;
    
    public function get($filtro){
        $this->posicoes = array();
        $sql = "select 
                    *
                from 
                        tb_banner_store_posicao";
        
        if($filtro != "")
            $sql .= " where ".$filtro;
        
        $sql .= " order by bsp_data_cadastro desc";
        
        try{
            if($posicoes = SQLexecuteQuery($sql)){
                $totalLinhas = pg_num_rows($posicoes);
                if($totalLinhas > 0){
                    while($lineRow = pg_fetch_array($posicoes)){
                        $posicao = new PosicaoBannerVO(
                                                            $lineRow["bsp_id"],
                                                            utf8_decode($lineRow["bsp_descricao"]),
                                                            utf8_decode($lineRow["bsp_tamanho"]),
                                                            $lineRow["bsp_status"],
                                                            $lineRow["bsp_data_cadastro"]
                                                        );
                        $this->posicoes[] = $posicao;
                        unset($posicao);
                    }

                    return $this->posicoes;
                }
            }else{
                throw new Exception("FALHA NA OBTENCAO DAS POSICOES DOS BANNERS");
            }
        } catch (Exception $ex) {
            $this->erros[] = $ex->getMessage();
            return false;
        }
	
    }
    
    public function insert(PosicaoBannerVO $posicao){
        
        try {
            $sql = vsprintf("insert into "
                    . "         tb_banner_store_posicao "
                    . "      (bsp_descricao, bsp_tamanho, bsp_data_cadastro, bsp_status) values ('%s', '%s', CURRENT_DATE, %s)",
                            array(
                                    $posicao->getDescricao(),
                                    $posicao->getTamanho(),
                                    $posicao->getStatus() 
                                )
                    );
            $retorno = SQLexecuteQuery($sql);
            if($retorno) {
                return true;
            }else{
                throw new Exception("ERRO AO INSERIR NOVA POSICAO. Query: $sql ".PHP_EOL);
            }
        } catch (Exception $ex) {
            $this->erros[] = $ex->getMessage();
            return false;
        }
    }
    
    public function update(PosicaoBannerVO $posicao){
        try {
            $sql = vsprintf(
                    "update 
                        tb_banner_store_posicao 
                    set
                        bsp_descricao = '%s',
                        bsp_tamanho = '%s',
                        bsp_status = %s
                    where 
                        bsp_id = %s;",array(
                                        $posicao->getDescricao(),
                                        $posicao->getTamanho(),
                                        $posicao->getStatus(),
                                        $posicao->getId()
                                    )
                    );

            $retorno = SQLexecuteQuery($sql);
            if($retorno) {
                return true;
            }else{
                throw new Exception("ERRO AO EDITAR POSICAO. Query: $sql ".PHP_EOL);
            }
        } catch (Exception $ex) {
            $this->erros[] = $ex->getMessage();
            return false;
        }
    }
    
}

?>