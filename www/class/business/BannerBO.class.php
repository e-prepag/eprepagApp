<?php
require_once $raiz_do_projeto . "class/dao/BannerDAO.class.php";
require_once $raiz_do_projeto . "class/view/BannerVO.class.php";
require_once $raiz_do_projeto . "class/business/PosicaoBannerBO.class.php";
require_once $raiz_do_projeto . "class/business/CategoriaBannerBO.class.php";
require_once $raiz_do_projeto . "class/util/Json.class.php";
require_once $raiz_do_projeto . "class/util/Log.class.php";
require_once $raiz_do_projeto . "class/util/Util.class.php";
require_once $raiz_do_projeto . "db/connect.php";
require_once $raiz_do_projeto . 'db/ConnectionPDO.php';

class BannerBO extends BannerDAO
{

    private $formatos = array('jpg', 'jpeg', 'gif', 'png');
    private $pasta = "arquivos_gerados/imagens/banners/";
    public $urlLink = "/imagens/banners/";
    private $categoria;
    private $posicao;
    private $_json;

    public function __construct()
    {
        $fullPath = RAIZ_DO_PROJETO . "json/";
        $this->_json = new Json;
    }

    public function getCategoria()
    {
        return $this->categoria;
    }

    public function getPosicao()
    {
        return $this->posicao;
    }

    public function setCategoria($categoria = "")
    {
        $this->categoria = $this->getCategoriaBanner($categoria);
        return $this;
    }

    public function setPosicao($posicao)
    {
        $this->posicao = $this->getPosicaoBanner($posicao);
        return $this;
    }

    public function pegaBanner($where = array(), $limit = null, $order = "")
    {
        $this->erros = array();

        try {
            if ($this->getCategoria())
                $where[] = ["bsc_id", "=", $this->getCategoria()];

            if ($this->getPosicao())
                $where[] = ["bsp_id",  "=", $this->getPosicao()];

            $obj = $this->get($where, $limit, $order);

            if (!empty($this->erros))
                throw new Exception;

            return $obj;
        } catch (Exception $ex) {
            //faz o que com o erro
            $this->erros[] = $ex->getMessage();
            Util::showArrError($this->erros);
            $geraLog = new Log("BANNERSBOpegaBanner", array(
                "ERROR: " .  implode(" / ", $this->erros),
                "FILE: " . $ex->getFile(),
                "LINE " . $ex->getLine()
            ));
            return false;
        }
    }

    public function insereBanner($post, $file)
    {
        $this->erros = array();

        try {
            if (!$this->validaBanner($post)) {
                $this->erros[] = "Dados inválidos";
                throw new Exception;
            }

            if (!$this->validaImagem($file["bs_imagem"]) || !$this->moveImagem($file["bs_imagem"])) {
                throw new Exception;
            }

            $banner = new BannerVO;
            $banner->setTitulo($post["bs_titulo"]);
            $banner->setImagem($file["bs_imagem"]["name"]);
            $banner->setLink($post["bs_link"]);
            $banner->setDataInicio($post["bs_data_inicio"]);
            $banner->setDataFim($post["bs_data_fim"]);
            $banner->setStatus($post["bs_status"]);
            $banner->setStatus($post["bs_status"]);
            $banner->setCategoria($post["bsc_id"]);
            $banner->setPosicao($post["bsp_id"]);

            if ($this->insert($banner)) {
                $this->jsonBanners();
                return true;
            } else
                throw new Exception;
        } catch (Exception $ex) {
            $this->erros[] = $ex->getMessage();
            Util::showArrError($this->erros);
            $geraLog = new Log("BANNERSBOinsereBanner", array(
                "ERROR: " .  implode(" / ", $this->erros),
                "FILE: " . $ex->getFile(),
                "LINE " . $ex->getLine()
            ));
            return false;
        }
    }

    public function editaBanner($post, $file)
    {
        $this->erros = array();

        try {

            if (
                !isset($post["idb"]) ||
                $post["idb"] == "" ||
                !$this->validaBanner($post)
            ) {
                $this->erros[] = "Dados inválidos";
                throw new Exception;
            }

            $filtro[] = ["bs_id", "=", $post["idb"]];
            $bannerAntigo = $this->pegaBanner($filtro);

            if (empty($bannerAntigo)) {
                $this->erros[] = "Banner nao encontrado";
                throw new Exception;
            }

            if (!isset($file["bs_imagem"]["name"]) || $file["bs_imagem"]["name"] == "") {
                $file["bs_imagem"]["name"] = $bannerAntigo[0]->getImagem();
            } else {
                if (!$this->validaImagem($file["bs_imagem"])) {
                    throw new Exception;
                }

                $this->removeImagemAntiga($bannerAntigo[0]->getImagem());

                if (!$this->moveImagem($file["bs_imagem"])) {
                    throw new Exception;
                }
            }

            $banner = new BannerVO;
            $banner->setId($post["idb"]);
            $banner->setTitulo(utf8_decode($post["bs_titulo"]));
            $banner->setImagem(utf8_decode($file["bs_imagem"]["name"]));
            $banner->setLink($post["bs_link"]);
            $banner->setDataInicio($post["bs_data_inicio"]);
            $banner->setDataFim($post["bs_data_fim"]);
            $banner->setStatus($post["bs_status"]);
            $banner->setCategoria($post["bsc_id"]);
            $banner->setPosicao($post["bsp_id"]);

            if (isset($post["bs_ordenacao"]))
                $banner->setOrdenacao($post["bs_ordenacao"]);

            $this->update($banner);
            unset($banner);

            if (empty($this->erros)) {
                $this->jsonBanners();
                return true;
            } else
                throw new Exception;
        } catch (Exception $ex) {
            $this->erros[] = $ex->getMessage();
            Util::showArrError($this->erros);
            $geraLog = new Log("BANNERSBOeditaBanner", array(
                "ERROR: " .  implode(" / ", $this->erros),
                "FILE: " . $ex->getFile(),
                "LINE " . $ex->getLine()
            ));
            return false;
        }
    }

    private function removeImagemAntiga($file)
    {
        if (file_exists(RAIZ_DO_PROJETO . $this->pasta . $file))
            unlink(RAIZ_DO_PROJETO . $this->pasta . $file);
    }

    private function moveImagem($file)
    {
        global $raiz_do_projeto;
        // Garante que o nome não contenha caminhos (../)
        $safe_filename = basename($file["name"]);

        $image_info = @getimagesize($file["tmp_name"]);

        if ($image_info === false) {
            $this->erros[] = "Arquivo inválido. O arquivo não é uma imagem suportada.";
            return false;
        }

        // Usamos a informação de MIME que o getimagesize() nos deu.
        $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (!isset($image_info['mime']) || !in_array($image_info['mime'], $allowed_mimes)) {
            $this->erros[] = "Tipo de imagem não permitido. Apenas JPG, PNG e GIF são aceitos.";
            return false;
        }

        $destino_local = RAIZ_DO_PROJETO . $this->pasta . $safe_filename;
        // 6. Mover o arquivo
        if (!move_uploaded_file($file["tmp_name"], $destino_local)) {
            $this->erros[] = "Erro ao gravar imagem localmente. $destino_local";
        }
        return (!empty($this->erros))
            ? false : true;
    }

    private function validaBanner($post)
    {
        $urlErro = false;
        $ch = curl_init($post["bs_link"]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);    // true - verifica certificado
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);    // 1 - então, também verifica nome no certificado

        $fp_err = fopen(RAIZ_DO_PROJETO . 'arquivos_gerados/logs/curl_err.log', 'ab+');
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_STDERR, $fp_err);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_exec($ch);
        $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // $retcode >= 400 -> not found, $retcode = 200, found.
        curl_close($ch);

        if ($retcode >= 400) {
            $this->erros[] = "Url inválida.";
            $urlErro = true;
        }

        return (strlen($post["bs_titulo"]) < 4 ||
            strlen($post["bs_link"]) < 8 ||
            strlen($post["bs_data_inicio"]) < 10 ||
            strlen($post["bs_data_fim"]) < 10 ||
            $post["bsc_id"] <= 0 ||
            $post["bsp_id"] <= 0 ||
            ($post["bs_status"] != 0 && $post["bs_status"] != 1) ||
            $urlErro)
            ? false : true;
    }

    private function validaImagem($file)
    {
        $ext = explode('/', $file['type']);
        $ext = array_reverse($ext);

        if (empty($file["name"]))
            $this->erros[] = "A imagem precisa ter um nome.";

        if (!in_array($ext[0], $this->formatos))
            $this->erros[] = "Formato de imagem inválido.";

        if (file_exists(RAIZ_DO_PROJETO . $this->pasta . $file["name"]))
            $this->erros[] = "Já existe uma imagem com esse nome, por favor, renomeie-a e tente novamente.";

        return (!empty($this->erros)) ? false : true;
    }

    public function jsonBanners()
    {
        $where[] = ["bs_status", "=", 1];
        $where[] = ["bs_data_inicio", "<=", date('Y-m-d 00:00:00')];
        $where[] = ["bs_data_fim", ">=", date('Y-m-d 00:00:00')];
        $empty = array("Vazio");

        $objCategorias = new CategoriaBannerBO;
        $categorias = $objCategorias->pegaCategoria();

        $where_p = "bsp_status = 1";
        $objPosicoes = new PosicaoBannerBO();
        $posicoes = $objPosicoes->pegaPosicao($where_p);

        if (!empty($categorias)) {

            foreach ($categorias as $categoria) {
                $prefix = $categoria->getDescricao();
                $arrJsonFiles = array($prefix . "-banners-1.json", $prefix . "-banners-2.json", $prefix . "-banners-3.json");
                $this->_json->setArrJsonFiles($arrJsonFiles);

                if ($categoria->getStatus() != 1) {
                    $this->_json->refresh($empty);
                    continue;
                }

                $index_bsc_id = 3;
                if (!isset($where[$index_bsc_id])) {
                    $where[] = ["bsc_id", "=", $categoria->getId()];
                } else {
                    $where[$index_bsc_id] = ["bsc_id", "=", $categoria->getId()];
                }
                $banners = $this->pegaBanner($where);
                if (!empty($banners)) {
                    foreach ($banners as $banner) {
                        foreach ($posicoes as $posicao) {
                            if ($posicao->getDescricao() == $banner->getPosicao()->getDescricao()) {
                                $titulo = htmlentities($banner->getTitulo());
                                $banner->setTitulo($titulo);
                                $arrBanners[$banner->getPosicao()->getDescricao()][] = $banner;
                            }
                        }
                    }

                    if (!isset($arrBanners)) {
                        $arrBanners = array("Vazio");
                        echo "Não está setado arrBanners" . PHP_EOL;
                    }

                    try {
                        if (!$this->_json->refresh($arrBanners))
                            throw new Exception("ERRO AO ATUALIZAR JSON DOS BANNERS");

                        unset($arrBanners);
                    } catch (Exception $ex) {
                        $this->erros[] = $ex->getMessage();
                        Util::showArrError($this->erros);
                        $geraLog = new Log("BANNERSBOrefresh", array(
                            "ERROR: " .  implode(" / ", $this->erros),
                            "FILE: " . $ex->getFile(),
                            "LINE " . $ex->getLine()
                        ));
                        return false;
                    }
                } else {
                    try {
                        if (!$this->_json->refresh($empty))
                            throw new Exception("ERRO AO ATUALIZAR JSON DOS BANNERS - NENHUMA BANNER ENCONTRADO");
                    } catch (Exception $ex) {
                        $this->erros[] = $ex->getMessage();
                        Util::showArrError($this->erros);
                        $geraLog = new Log("BANNERSBOrefresh", array(
                            "ERROR: " .  implode(" / ", $this->erros),
                            "FILE: " . $ex->getFile(),
                            "LINE " . $ex->getLine()
                        ));
                        return false;
                    }
                }
            }
        } else {
            try {
                if (!$this->_json->refresh($empty))
                    throw new Exception("ERRO AO ATUALIZAR JSON DOS BANNERS - NENHUMA CATEGORIA ATIVA");
            } catch (Exception $ex) {
                $this->erros[] = $ex->getMessage();
                Util::showArrError($this->erros);
                $geraLog = new Log("BANNERSBOrefresh", array(
                    "ERROR: " .  implode(" / ", $this->erros),
                    "FILE: " . $ex->getFile(),
                    "LINE " . $ex->getLine()
                ));
                return false;
            }
        }
    }

    public function getBannersFromJson($posicao, $categoria, $currJsonFile = 1)
    {
        try {
            // 1. Definir o NOME do JSON a ser buscado no banco
            $nomeJson = $categoria . "-banners-" . $currJsonFile . ".json";

            // 2. Usar a classe Json refatorada para buscar do DB
            $json = $this->_json
                ->setArrJsonFiles([$nomeJson]) // Define o 'nome' a ser buscado
                ->getJsonRecursive();         // Executa o SELECT no banco

            // 3. Verificar o resultado
            if ($json) {
                // getJsonRecursive() já retorna o objeto/array decodificado
                $posicao = html_entity_decode($posicao);
                return isset($json->$posicao) ? $json->$posicao : false;
            } else {
                // Se $json for false, o DB não encontrou.
                // Lançamos uma exceção para acionar a lógica de fallback (tentar -2.json, -3.json)
                throw new Exception("JSON '$nomeJson' não encontrado no banco.");
            }
        } catch (Exception $ex) {
            $this->erros[] = $ex->getMessage();
            $geraLog = new Log("FEEDRSS_DB", array( // (Opcional) Mudei o nome do Log
                "ERROR: " . $ex->getMessage(),
                "FILE: " . $ex->getFile(),
                "LINE " . $ex->getLine()
            ));

            // setando qual arquivo json será usado em caso de erro
            $currJsonFile++;

            // A lógica de fallback é mantida
            if ($currJsonFile <= 3) {
                return $this->getBannersFromJson($posicao, $categoria, $currJsonFile);
            } else {
                return false;
            }
        }
    }
} //end class
