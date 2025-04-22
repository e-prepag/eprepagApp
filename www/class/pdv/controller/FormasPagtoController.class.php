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

$pagina_titulo = "Saldo";
$_PaginaOperador2Permitido = 54;

class FormasPagtoController extends HeaderController{
    public $raiz_do_projeto;
    
    public function __construct(){
        $this->objBanner = new BannerBO;
        
        parent::__construct();
        
        if(!isset($_POST['produtos_valor']))
            redirect("/creditos/add_saldo.php");
    }
}
