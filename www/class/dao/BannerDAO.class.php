<?php
require_once RAIZ_DO_PROJETO . "class/business/ClickBannerBO.class.php";

class BannerDAO
{

    public $objPosicoes = array();
    public $objCategorias = array();
    public $banners = array();
    public $erros = array();

    public function get($where, $limit = null, $order = "")
    {

        $this->banners = array();
        $this->objPosicoes = new PosicaoBannerBO;
        $this->objCategorias = new CategoriaBannerBO;

        $sql = "select 
                *
            from 
                tb_banner_store";

        $params = [];
        $filtro_inseguro = "";

        if (!empty($where)) {

            $primeiro_elemento = reset($where);

            // Se o primeiro elemento for um array, assumimos o NOVO formato seguro
            // Ex: $where = [ ['bsc_id', '=', 1], ['bs_status', '=', 1] ]
            if (is_array($primeiro_elemento)) {

                $where_strings = [];

                $colunasPermitidas = [
                    'bs_data_inicio',
                    'bs_data_fim',
                    'bs_status',
                    'bsc_id',
                    'bsp_id',
                    'bs_ordenacao',
                    'bs_data_cadastro',
                    'bs_id',
                    'bs_titulo',
                    'bs_link',
                    'bs_imagem'
                ];
                $operadoresPermitidos = ['=', '<>', '>', '<', '>=', '<=', 'LIKE', 'ILIKE'];

                foreach ($where as $cond) {
                    // Valida o formato [coluna, operador, valor]
                    if (is_array($cond) && count($cond) === 3) {
                        list($coluna, $operador, $valor) = $cond;

                        // Validação rigorosa contra as whitelists
                        if (in_array(strtolower($coluna), $colunasPermitidas) && in_array(strtoupper($operador), $operadoresPermitidos)) {
                            $where_strings[] = "$coluna $operador $" . (count($params) + 1);
                            $params[] = $valor;
                        }
                    }
                }
                if (!empty($where_strings)) {
                    $sql .= " WHERE " . implode(" AND ", $where_strings);
                }
            } else {
                // --- MODO LEGADO (INSEGURO) ---
                $filtro_inseguro = implode(" AND ", $where);
                if ($filtro_inseguro != "") {
                    $sql .= " WHERE " . $filtro_inseguro;
                }
            }
        }

        $colunasOrderPermitidas = [
            'bs_data_inicio',
            'bs_data_fim',
            'bs_status',
            'bsc_id',
            'bsp_id',
            'bs_ordenacao',
            'bs_data_cadastro',
            'bs_id',
            'bs_titulo',
            'bs_link',
            'bs_imagem'
        ];
        $ordemPadrao = " ORDER BY bsc_id,bsp_id,bs_ordenacao,bs_data_cadastro desc ";

        if (!empty($order)) {
            $orderLimpa = trim(preg_replace('/\s+/', ' ', $order));

            $partes = explode(' ', $orderLimpa);
            $coluna = strtolower(trim($partes[0]));
            $direcao = 'ASC'; // Padrão

            if (isset($partes[1])) {
                $direcao = strtoupper(trim($partes[1]));
            }

            $colunaValida = in_array($coluna, $colunasOrderPermitidas);
            $direcaoValida = in_array($direcao, ['ASC', 'DESC']);

            if ($colunaValida && $direcaoValida) {
                $sql .= " ORDER BY $coluna $direcao";
            } else {
                $sql .= $ordemPadrao;
            }
        } else {
            $sql .= $ordemPadrao;
        }

        if ($limit) {
            $sql .= " LIMIT " . (int)$limit; // Força $limit a ser um número inteiro
        }

        try {
            if ($banners = SQLexecuteQueryParams($sql, $params)) {
                $totalLinhas = pg_num_rows($banners);
                if ($totalLinhas > 0) {

                    while ($lineRow = pg_fetch_array($banners)) {
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

                        $filtroCategoria = "bsc_id = " . $lineRow["bsc_id"];
                        $categoria = $this->objCategorias->pegaCategoria($filtroCategoria);

                        if (isset($categoria[0]))
                            $banner->setCategoria($categoria[0]);

                        $lineRow["bsp_altura"] = 100;
                        $lineRow["bsp_largura"] = 100;

                        $filtroPosicao = "bsp_id = " . $lineRow["bsp_id"];
                        $posicao = $this->objPosicoes->pegaPosicao($filtroPosicao);

                        if (isset($posicao[0]))
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

    public function insert(BannerVO $banner)
    {

        try {
            $sql = "INSERT INTO tb_banner_store 
            (bs_titulo, bs_imagem, bs_link, bs_data_inicio, bs_data_fim, bs_data_cadastro, bs_status, bsc_id, bsp_id)
        VALUES
            ($1, $2, $3, to_date($4, 'DD/MM/YYYY'), to_date($5, 'DD/MM/YYYY'), CURRENT_DATE, $6, $7, $8)";

            $params = [
                $banner->getTitulo(),
                $banner->getImagem(),
                $banner->getLink(),
                $banner->getDataInicio(),
                $banner->getDataFim(),
                $banner->getStatus(),
                $banner->getCategoria(),
                $banner->getPosicao()
            ];

            $retorno = SQLexecuteQueryParams($sql, $params);
            if ($retorno) {
                return true;
            } else {
                throw new Exception("FALHA AO INSERIR NOVO BANNER." . PHP_EOL);
            }
        } catch (Exception $ex) {
            $this->erros[] = $ex->getMessage();
            return false;
        }
    }

    public function update(BannerVO $banner)
    {
        try {
            $sql = "UPDATE  
            tb_banner_store 
         SET 
            bs_titulo = $1, 
            bs_imagem = $2, 
            bs_link = $3, 
            bs_data_inicio = to_date($4, 'DD/MM/YYYY'), 
            bs_data_fim = to_date($5, 'DD/MM/YYYY'), 
            bs_status = $6, 
            bsc_id = $7, 
            bsp_id = $8,
            bs_ordenacao = $9
         WHERE
            bs_id = $10";

            $params = [
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
            ];

            $retorno = SQLexecuteQueryParams($sql, $params);
            if ($retorno) {
                return true;
            } else {
                $this->erros[] = "ERRO AO INSERIR NOVO BANNER. Query: $sql " . PHP_EOL;
            }
        } catch (Exception $ex) {
            $this->erros[] = $ex->getMessage();
            return false;
        }
    }

    public function getPosicaoBanner($posicao)
    {

        $sql = "select bsp_id from tb_banner_store_posicao where UPPER(bsp_descricao) = $1";

        $posicao_upper = strtoupper($posicao);
        try {
            if ($posicao = SQLexecuteQueryParams($sql, [$posicao_upper])) {
                if (pg_num_rows($posicao) > 0) {

                    $lineRow = pg_fetch_array($posicao);
                    return $lineRow['bsp_id'];
                } else {
                    throw new Exception("POSICAO INVALIDA");
                }
            }
        } catch (Exception $ex) {
            $this->erros[] = $ex->getMessage();
            return false;
        }
    }

    public function getCategoriaBanner($categoria)
    {

        $sql = "select bsc_id from tb_banner_store_categorias where UPPER(bsc_descricao) = $1";

        $categoria_upper = strtoupper($categoria);

        try {
            if ($categoria = SQLexecuteQueryParams($sql, [$categoria_upper])) {
                if (pg_num_rows($categoria) > 0) {

                    $lineRow = pg_fetch_array($categoria);
                    return $lineRow['bsc_id'];
                } else {
                    throw new Exception("CATEGORIA INVALIDA");
                }
            }
        } catch (Exception $ex) {
            $this->erros[] = $ex->getMessage();
            return false;
        }
    }
}
