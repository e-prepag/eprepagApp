<?php
/**
 * Classe para as regras de negocio das vendas
 *
 * @author Diego
 * @email diego.gomes@e-prepag.com.br
 * @date 16-07-2015
 */

require_once $raiz_do_projeto.'class/pdv/controller/HeaderController.class.php';

$_PaginaOperador2Permitido = 54; 

$pagina_titulo = "Produtos Disponíveis";

class ProdutosController extends HeaderController{
    public $raiz_do_projeto;
    
//    public $usuarioGames;
    
    public $usuarioId;
    
    public $usuarioSerialize;
    
    public $arrProduto;
    
    public $arrProdutoOrdemAlfabetica;
    
    private $_ug_possui_restricao_produtos;
    
    private $vetorAux;
    
    public function __construct(){
        $this->objBanner = new BannerBO;
        
        parent::__construct();
        
        if($this->operadorTipo === $GLOBALS['USUARIO_GAMES_OPERADOR_TIPOS'][FUNCIONARIO_2])
            $this->accessDenied ();
        
        //Recupera o usuario do session
        if (!$this->usuarioSerialize = $this->getUserGamesSession())
            $this->logout(get_class($this), "\n ERRO DE SESSAO: construct::getUserGamesSession() - Produtos \n");

        if (!$this->usuarioId = $this->usuarioSerialize->getId()) {
            $this->logout(get_class($this), "\n ERRO DE SESSAO: construct::getId() - Produtos \n");
        }
        $this->_ug_possui_restricao_produtos = $this->usuarioSerialize->getPossuiRestricaoProdutos();

        if ($this->_ug_possui_restricao_produtos === null) {
            $this->logout(get_class($this), "\n ERRO DE SESSAO: construct::getPossuiRestricaoProdutos() - Produtos \n");
        }
        //recupera usuário caso tenha sido mudado para pre-pago
        //$this->usuarioGames = UsuarioGames::getUsuarioGamesById($this->usuarioId); //comentado pois nao sera utilizado ate o sprite atual
    }
    
    public function getUserGamesSession(){
        try{
            $ret = unserialize($_SESSION['dist_usuarioGames_ser']);
            
        } catch (Exception $ex) {
            $this->logout(get_class($this),"\n ERRO DE SESSAO: getUserGamesSession()::unserialize - Produtos \n");
        }
        
        return $ret;
    }
    
    public function getProdutos($idProduto = null){
            
        $rs = null;
        $filtro['opr'] = 1;
        $filtro['opr_status'] = '1';
        $filtro['ogp_codigo_negado'] = 39;
        // Wagner
        $filtro['ogp_mostra_integracao_gamer_com_loja'] = '1';
        
        if($idProduto)
            $filtro['ogp_id'] = $idProduto;

        //Restringindo produtos que não podem ser vendidos online para LANs devidamente inibidas
        if($this->_ug_possui_restricao_produtos == 1) {
            $filtro['ogp_inibi_lojas_online'] = '1';
        }

        // LH Mousebox de Campinas não pode vender Habbo (ogp_id=5) nem Flyff (ogp_id=41)
        if ($this->usuarioId == 43) {
                $filtro['ogp_codigo_negado_2'] = "5, 41";
        }

        // 2010-12-06 - LH ZEUSPORTELLA só compra "Point Blank" da Ongame = cod: 63
        //	a partir de 2011-09-15 também compra "Ultimate Game Card" da PayByCash = cod: 36
        // reinaldolh -  || $this->usuarioId == 389
        // reinaldolh2 -  || $this->usuarioId == 468 
        if ($this->usuarioId == 4207) {
                $filtro['ogp_id'] = null;
                unset($filtro['ogp_id']);
                $filtro['ogp_id_lista'] = "63, 36";
        }

        if ($this->usuarioId == 6161 || $this->usuarioId == 7323) {
                $filtro['ogp_id'] = null;
                unset($filtro['ogp_id']);
                $filtro['ogp_id_lista'] = "93";
        }
        
        $filtro['ogp_ativo'] = 1;
        $instProduto = new Produto;
        $ret = $instProduto->obterMelhorado($filtro, null, $rs);

        if(!$rs || pg_num_rows($rs) == 0){

            try{
                $params = "";
                
                if(isset($_POST))
                    $params .= json_encode($_POST);
                
                if(isset($_GET))
                    $params .= json_encode($_GET);
                
                $error  = "BUSCA DE PRODUTOS RETORNOU VAZIO. <br>";
                $error .= "Usuário: ".$this->usuarioId." <br>";
                $error .= "Parâmetros: ".$params;
                
                $this->emailReport($_SERVER["URL"], $error);
                throw new Exception($error);
            } catch (Exception $ex) {
                $this->setError("PRODUTOS_DIST_COMMERCE_CONTROLLER", $ex);
            }

            $this->arrProduto = array();

        } else {
            $this->arrProduto = array();

            for($i=0; $rs_row = pg_fetch_array($rs); $i++){

                $objProduto = new Produto;
                $objProduto->setId($rs_row['ogp_id']);
                $objProduto->setNome($rs_row['ogp_nome']);
                $objProduto->setDescricao($rs_row['ogp_descricao']);
                $objProduto->setAtivo($rs_row['ogp_ativo']);
                $objProduto->setNomeImagem($rs_row['ogp_nome_imagem']);
                $objProduto->setDataInclusao($rs_row['ogp_data_inclusao']);
                $objProduto->setNomeOperadora($rs_row['opr_nome_loja']);
                $objProduto->setOprCodigo($rs_row["ogp_opr_codigo"]);
                $objProduto->setMostraIntegracao($rs_row['ogp_mostra_integracao_gamer']);
                $objProduto->setPinRequest($rs_row['ogp_pin_request']);
                $objProduto->setValorMinimo($rs_row['ogp_valor_minimo']);
                $objProduto->setValorMaximo($rs_row['ogp_valor_maximo']);
                $this->arrProduto[$rs_row['ogp_nome']] = $objProduto;
                
                unset($objProduto);
            }
        }

        return $this->arrProduto;
    }
        
    public function getProdutosOrdemAlfabetica(){
        if(empty($this->arrProduto))
            $this->getProdutos ();

        $this->arrProdutoOrdemAlfabetica = $this->arrProduto;
        ksort($this->arrProdutoOrdemAlfabetica);
        return $this->arrProdutoOrdemAlfabetica;

    }
    
    public function getProdutoValor($idProduto){
        $arrProduto = $this->getProdutos($idProduto);

        try {
            if(!empty($arrProduto)){
                $objProduto = current($arrProduto);

                $rs = null;
                $filtro['ogpm_ativo'] = 1;
                $filtro['ogpm_ogp_id'] = $objProduto->getId();
                $order = "ogpm_valor asc";

                $objProduto->setModelo($this->getModelo($filtro, $order));
            }else {
                throw new Exception("PRODUTO NÃO ENCONTRADO.");
            }
        } catch (Exception $ex) {
                $msg = "O produto que você está tentando acessar está indisponível no momento.<br>Aguarde alguns instantes ou entre em contato com nosso suporte.<br>Obrigado.";
?>
                <form name="pagamento" id="pagamento" method="POST" action="/creditos/mensagem.php">
                   <input type='hidden' name='msg' id='msg' value='<?php echo $msg; ?>'>
                   <input type='hidden' name='titulo' id='titulo' value='Produto Indisponível no Momento'>
                   <input type='hidden' name='link' id='link' value='/creditos/'>
               </form>
               <script language='javascript'>
                   document.getElementById("pagamento").submit();
               </script>       
<?php    
            die();
        }

        return $objProduto;
    }
    
    public function getModelo($filtro,$order){
        $rs = null;
        try{
            $instProdutoModelo = new ProdutoModelo();
            $ret = $instProdutoModelo->obter($filtro, $order, $rs);

            if(!$rs || pg_num_rows($rs) == 0){
                throw new Exception;

            } else {
                $arrModelos = array();

                for($i=0; $rs_row = pg_fetch_array($rs); $i++){
//                    echo "<br><hr><pre>";
//                    print_r($rs_row);
//                    echo "</pre><hr><br>";
                    
                    if(isset($filtro["com_produto"]))
                        $arrModelos[] = new ProdutoModelo(
                              $rs_row['ogpm_id'], 
                              $rs_row['ogpm_ogp_id'], 
                              $rs_row['ogpm_nome'], 
                              $rs_row['ogpm_descricao'], 
                              $rs_row['ogpm_valor'], 
                              $rs_row['ogpm_perc_desconto'], 
                              $rs_row['ogpm_ativo'], 
                              $rs_row['ogpm_nome_imagem'], 
                              $rs_row['ogpm_data_inclusao'], 
                              $rs_row['ogpm_pin_valor'],
                              $rs_row['ogp_nome'],
                              $rs_row['ogp_opr_codigo']
                            );
                    else
                      $arrModelos[] = new ProdutoModelo(
                              $rs_row['ogpm_id'], 
                              $rs_row['ogpm_ogp_id'], 
                              $rs_row['ogpm_nome'], 
                              $rs_row['ogpm_descricao'], 
                              $rs_row['ogpm_valor'], 
                              $rs_row['ogpm_perc_desconto'], 
                              $rs_row['ogpm_ativo'], 
                              $rs_row['ogpm_nome_imagem'], 
                              $rs_row['ogpm_data_inclusao'], 
                              $rs_row['ogpm_pin_valor']
                            );
                }
                if(!empty($arrModelos))
                    return $arrModelos;
                else
                    throw new Exception;
            }
        } catch (Exception $ex) {
            /*
             * gerar log de erros
                ("BUSCA DE PINS DO PRODUTO (".implode(" - ", $filtro).") RETORNOU VAZIO.")
             */
        }
        
    }
        
}
