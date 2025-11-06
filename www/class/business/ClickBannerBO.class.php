<?php
require_once $raiz_do_projeto . "class/dao/ClickBannerDAO.class.php";
require_once $raiz_do_projeto . "class/view/ClickBannerVO.class.php";

class ClickBannerBO extends ClickBannerDAO
{

    public function pegaClicksBanner($id)
    {

        try {
            $sql = "select count(*) as clicks 
                from tb_banner_store_clicks where bs_id = $1";
            $obj = $this->get($sql, [$id]);

            if (!empty($this->erros))
                throw new Exception;

            return $obj;
        } catch (Exception $ex) {
            //faz o que com o erro
            Util::showArrError($this->erros);
            $geraLog = new Log("CLICKBANNERSBOpegaClickBanner", array(
                "ERROR: " .  implode(" / ", $this->erros),
                "FILE: " . $ex->getFile(),
                "LINE " . $ex->getLine()
            ));
            return false;
        }
    }

    public function pegaClicksBannerBusca($post, BannerBO $objBanners)
    {

        $sql = "SELECT count(*) as clicks, bs_titulo FROM tb_banner_store_clicks as bsc 
        INNER JOIN tb_banner_store as bs ON bs.bs_id = bsc.bs_id";

        $where = [];
        $params = [];

        try {

            if (!empty($post["bs_id"])) {
                // Adiciona o placeholder (ex: $1)
                $where[] = "bsc.bs_id = $" . (count($params) + 1);
                $params[] = $post['bs_id'];
            }

            if ($post["dataClickIni"] != "") {
                $where[] = "bs_click_data_cadastro >= $" . (count($params) + 1); // Ex: $2
                $params[] = Util::getData($post['dataClickIni'], true); // O util formata o dado
            }

            if ($post["dataClickFim"] != "") {
                $where[] = "bs_click_data_cadastro <= $" . (count($params) + 1); // Ex: $3
                $params[] = Util::getData($post['dataClickFim'], true);
            }

            // Junta as partes do WHERE, se houver
            if (!empty($where)) {
                $sql .= ' WHERE ' . implode(' AND ', $where);
            }

            $sql .= " GROUP BY bs_titulo";

            $obj = $this->get($sql, $params);

            if (!empty($this->erros)) {
                throw new Exception;
            }

            return $obj;
        } catch (Exception $ex) {
            //faz o que com o erro
            Util::showArrError($this->erros);
            $geraLog = new Log("CLICKBANNERSBOpegaClickBanner", array(
                "ERROR: " .  implode(" / ", $this->erros),
                "FILE: " . $ex->getFile(),
                "LINE " . $ex->getLine()
            ));
            return false;
        }
    }

    public function insereClickBanner($id)
    {
        $this->erros = array();

        try {
            if ($this->insert($id))
                return true;
            else
                throw new Exception;
        } catch (Exception $ex) {
            Util::showArrError($this->erros);
            $geraLog = new Log("CLICKBANNERSBOinsereClickBanner", array(
                "ERROR: " .  implode(" / ", $this->erros),
                "FILE: " . $ex->getFile(),
                "LINE " . $ex->getLine()
            ));
            return false;
        }
    }
} //end class
