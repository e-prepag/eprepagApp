<?php
/**
 * Classe para as regras de negocio das vendas
 *
 * @author Diego
 * @email diego.gomes@e-prepag.com.br
 * @date 16-07-2015
 */
$pagina_titulo = "Inicio";
$_PaginaOperador1Permitido = 53; // o número magico
$_PaginaOperador2Permitido = 54;

require_once RAIZ_DO_PROJETO . 'class/util/Util.class.php';
require_once RAIZ_DO_PROJETO . 'class/pdv/controller/HeaderController.class.php';
require_once RAIZ_DO_PROJETO . 'class/util/Json.class.php';

class IndexController extends HeaderController{
    public $raiz_do_projeto;
    public $objFeed;
    
    public function __construct(){
        
        $this->objBanner = new BannerBO;
        
        parent::__construct();
        
        $this->objFeed = new Json();
    }
    
    public function getFeedBlog($qtd = 5){
        

        $this->objFeed->setFullPath(DIR_JSON);
        $arrJsonFiles = unserialize(ARR_JSON_FEED_CREDITOS);
        $this->objFeed->setArrJsonFiles($arrJsonFiles);
        return $this->objFeed->getJsonRecursive();
        
    }
     
    public function getLimiteDisponivel(){
        return ($this->usuarios->getRiscoClassif()==2) ? 0  : $this->usuarios->getPerfilLimite();
    }
}
