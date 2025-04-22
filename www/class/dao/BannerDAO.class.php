<?php
require_once RAIZ_DO_PROJETO."class/business/ClickBannerBO.class.php";

class BannerDAO {
    
    public $objPosicoes = array();
    public $objCategorias = array();
    public $banners = array();
    public $erros = array();
    
    public function get($filtro,$limit = null, $order = ""){
        
        $this->banners = array();
        $this->objPosicoes = new PosicaoBannerBO;
        $this->objCategorias = new CategoriaBannerBO;
        
        $sql = "select 
                    *
                from 
                    tb_banner_store"; 
                //as b
                //inner join tb_banner_store_posicao as p on b.bsp_id = p.bsp_id
                //inner join tb_banner_store_categorias as c on b.bsc_id = c.bsc_id

        if($filtro != "")
            $sql .= " where ".$filtro;
        
        $sql .= ($order !== "") ? " order by $order" : " order by bsc_id,bsp_id,bs_ordenacao,bs_data_cadastro desc ";

        if($limit)
            $sql .= " limit ".$limit;
        
        try{
            if($banners = SQLexecuteQuery($sql)){
                $totalLinhas = pg_num_rows($banners);
                if($totalLinhas > 0){
                    
                    while($lineRow = pg_fetch_array($banners)){
                        $banner = new BannerVO;
                        $banner->setId($lineRow["bs_id"]);
                        $banner->setTitulo($lineRow["bs_titulo"]);
                        $banner->setImagem($lineRow["bs_imagem"]);
                        $banner->setLink($lineRow["bs_link"]);
                        $banner->setDataInicio($lineRow["bs_data_inicio"]);
                        $banner->setDataFim($lineRow["bs_data_fim"]);
                        $banner->setDataCadastro($lineRow["bs_data_cadastro"]);
                        $banner->setStatus($lineRow["bs_status"]);
                        $banner->setOrdenacao($lineRow["bs_ordenacao"]);
                        
                        $filtroCategoria = "bsc_id = ".$lineRow["bsc_id"];
                        $categoria = $this->objCategorias->pegaCategoria($filtroCategoria);
                        
                        if(isset($categoria[0]))
                            $banner->setCategoria($categoria[0]);

                        $lineRow["bsp_altura"] = 100;
                        $lineRow["bsp_largura"] = 100;

                        $filtroPosicao = "bsp_id = ".$lineRow["bsp_id"];
                        $posicao = $this->objPosicoes->pegaPosicao($filtroPosicao);

                        if(isset($posicao[0]))
                            $banner->setPosicao($posicao[0]);

                        $objClicks = new ClickBannerBO;
                        $clicks = $objClicks->pegaClicksBanner($lineRow["bs_id"]);
                        $banner->setClicks($clicks[0]->getClicks());

                        $this->banners[] = $banner;
                        unset($banner);
                        unset($categoria);
                        unset($posicao);
                    }

                    return $this->banners;
                }
            }
        } catch (Exception $ex) {
            $this->erros[] = $ex->getMessage();
            return false;
        }
	
    }
    
    public function insert(BannerVO $banner){
        
        try {
            $sql = vsprintf(
                    "insert into tb_banner_store 
                        (bs_titulo, bs_imagem, bs_link, bs_data_inicio, bs_data_fim, bs_data_cadastro, bs_status, bsc_id, bsp_id)
                    values
                        ('%s', '%s', '%s', to_date('%s','DD/MM/YYYY'), to_date('%s','DD/MM/YYYY'), CURRENT_DATE, %s, %s, %s)",array(
                                                                            $banner->getTitulo(),
                                                                            $banner->getImagem(),
                                                                            $banner->getLink(),
                                                                            $banner->getDataInicio(),
                                                                            $banner->getDataFim(),
                                                                            $banner->getStatus(),
                                                                            $banner->getCategoria(),
                                                                            $banner->getPosicao()
                                                                        )
                    );

            $retorno = SQLexecuteQuery($sql);
            if($retorno) {
                return true;
            }else{
                throw new Exception("FALHA AO INSERIR NOVO BANNER.".PHP_EOL);
            }
        } catch (Exception $ex) {
            $this->erros[] = $ex->getMessage();
            return false;
        }
    }
    
    public function update(BannerVO $banner){
        try {
            $sql = vsprintf(
                    "update  
                        tb_banner_store 
                     set 
                        bs_titulo = '%s', 
                        bs_imagem = '%s', 
                        bs_link = '%s', 
                        bs_data_inicio = to_date('%s','DD/MM/YYYY'), 
                        bs_data_fim = to_date('%s','DD/MM/YYYY'), 
                        bs_status = '%s', 
                        bsc_id = '%s', 
                        bsp_id = '%s',
                        bs_ordenacao = '%s'
                     where
                        bs_id = %s;",array(
                                        $banner->getTitulo(),
                                        $banner->getImagem(),
                                        $banner->getLink(),
                                        $banner->getDataInicio(),
                                        $banner->getDataFim(),
                                        $banner->getStatus(),
                                        $banner->getCategoria(),
                                        $banner->getPosicao(),
                                        $banner->getOrdenacao(),
                                        $banner->getId(),
                                    )
                    );
            $retorno = SQLexecuteQuery($sql);
            if($retorno) {
                return true;
            }else{
                $this->erros[] = "ERRO AO INSERIR NOVO BANNER. Query: $sql ".PHP_EOL;
            }
        } catch (Exception $ex) {
            $this->erros[] = $ex->getMessage();
            return false;
        }
    }
    
    public function getPosicaoBanner($posicao){
        
        $sql = "select bsp_id from tb_banner_store_posicao where UPPER(bsp_descricao) = '".  strtoupper($posicao)."'";

        try{
            if($posicao = SQLexecuteQuery($sql)){
                if(pg_num_rows($posicao) > 0){
                    
                    $lineRow = pg_fetch_array($posicao);
                    return $lineRow['bsp_id'];
                }else{
                    throw new Exception("POSICAO INVALIDA");
                }
            }
        } catch (Exception $ex) {
            $this->erros[] = $ex->getMessage();
            return false;
        }
	
    }
    
    public function getCategoriaBanner($categoria){
        
        $sql = "select bsc_id from tb_banner_store_categorias where UPPER(bsc_descricao) = '".  strtoupper($categoria)."'";

        try{
            if($categoria = SQLexecuteQuery($sql)){
                if(pg_num_rows($categoria) > 0){
                    
                    $lineRow = pg_fetch_array($categoria);
                    return $lineRow['bsc_id'];
                }else{
                    throw new Exception("CATEGORIA INVALIDA");
                }
            }
        } catch (Exception $ex) {
            $this->erros[] = $ex->getMessage();
            return false;
        }
	
    }
    
}
?>