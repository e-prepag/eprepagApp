<?php
/**
 * Classe para as regras de negocio das vendas
 *
 * @author Diego
 * @email diego.gomes@e-prepag.com.br
 * @date 16-07-2015
 */
require_once DIR_CLASS . 'util/Util.class.php';
require_once DIR_CLASS . 'pdv/controller/HeaderController.class.php';
require_once DIR_CLASS . 'business/VendasLanHouseBO.class.php';

$_PaginaOperador2Permitido = 54;
$pagina_titulo = "Depósitos";


class DepositosController extends HeaderController{
    public $raiz_do_projeto;
        
    public function __construct(){
        $this->objBanner = new BannerBO;
        
        parent::__construct();
        
        if($this->usuarios->getRiscoClassif()==1 || $this->operadorTipo === $GLOBALS['USUARIO_GAMES_OPERADOR_TIPOS'][FUNCIONARIO_2])
            $this->accessDenied ();
        
        
    }
}
