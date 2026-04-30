<?php
class PosicaoBannerDAO {
    
    public $posicoes;
    protected $erros = array();
    
    public function get($filtro){
        $this->posicoes = array();
        $sql = "select 
                    *
                from 
                        tb_banner_store_posicao";
        
        $params = array();
        if($filtro != "") {
            if(preg_match('/^\s*(bsp_id|bsp_status)\s*=\s*(\d+)\s*$/', $filtro, $matches)) {
                $sql .= " where " . $matches[1] . " = $1";
                $params[] = (int) $matches[2];
            } else {
                $this->erros[] = "Filtro invalido";
                return false;
            }
        }
        
        $sql .= " order by bsp_data_cadastro desc";
        
        try{
            if($posicoes = (count($params) ? SQLexecuteQueryParams($sql, $params) : SQLexecuteQuery($sql))){
                $totalLinhas = (($posicoes) ? pg_num_rows($posicoes) : 0);
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
            $sql = "insert into tb_banner_store_posicao
                         (bsp_descricao, bsp_tamanho, bsp_data_cadastro, bsp_status) values ($1, $2, CURRENT_DATE, $3)";
            $retorno = SQLexecuteQueryParams($sql, [$posicao->getDescricao(), $posicao->getTamanho(), $posicao->getStatus()]);
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
            $sql = "update 
                        tb_banner_store_posicao 
                    set
                        bsp_descricao = $1,
                        bsp_tamanho = $2,
                        bsp_status = $3
                    where 
                        bsp_id = $4;";

            $retorno = SQLexecuteQueryParams($sql, [$posicao->getDescricao(), $posicao->getTamanho(), $posicao->getStatus(), $posicao->getId()]);
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
