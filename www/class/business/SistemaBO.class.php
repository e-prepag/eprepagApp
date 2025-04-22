<?php
require_once DIR_CLASS."dao/SistemaDAO.class.php";

class SistemaBO extends SistemaDAO{
    
    public $sistema;
    public $aba;
    public $erro = array();
    public $item = array();
    public $menu = array();

    public function __construct($sistema, $aba, $paginaInicial) {
        
        $this->setSistema($sistema);
        $this->setAba($aba);
        $this->setPaginaInicial($paginaInicial);
                
        parent::__construct();

    }
    
    public function getIndex($session = true){
                
        if($itensGrupos = $this->getArrItensByGrupos()){
			//echo "<script>console.log(".json_encode($itensGrupos).")</script>"; 
			//echo "<script>console.log(".json_encode($this->getAba()).")</script>";
            $this->setItensUsuario($itensGrupos);
            $abasMenuItensMenu = $this->getAbasMenuItensMenu($this->getSistema(), $this->getAba(), $session);
        }else{
            
            $abasMenuItensMenu = false;
            $this->setErro("Não existem grupos cadastrados para seu usuário. Entre em contato com o suporte.");
        }
        
        return $abasMenuItensMenu;
    }
    
    public function getAllItensByGrupo($idGrupo, $session = true){
        $this->setArrIdGrupos(
                                array($idGrupo)
                            );
        
        //zerando a variável aba para nao limitar a busca
        $this->setAba("");

        //pegando as abas / menu / itens menu do sistema backoffice
        $abasMenuItensMenu['backoffice'] = $this->getIndex($session);
        
        $this->setArrAbasVO("");
        //pegando as abas / menu / itens do menu do sistema sys/admin
        $this->setSistema("sysadmin");
        $abasMenuItensMenu['sysadmin'] = $this->getIndex($session);
        
        return $abasMenuItensMenu;
    }
    
    public function getAllAbas(){
     
        return $this->getAbas($this->getSistema(), "", false);
    }
    
    //Returna os menus e seus respectivos itens pertencentes a aba atual
    //Caso não tenha itens para essa aba ou o usuário não pertença a nenhum grupo
    //uma mensagem de erro é retornada
    public function getMenusAba($idAba, $withItens = false){
        $itensGrupos = $this->getArrItensByGrupos();
        if(empty($itensGrupos)){
            $this->setErro("Não existem itens disponíveis para este usuário nessa aba.");
            return FALSE;
        }
        else if($itensGrupos){
            $this->setItensUsuario($itensGrupos);
            return $this->getMenus($idAba, $withItens);
        }else{
            $this->setErro("Não existem grupos cadastrados para seu usuário. Entre em contato com o suporte.");
            return FALSE;
        }
    }
    
    public function getMenuByItem(){        
        if($this->item){
            $menuId = $this->item->getIdMenu();
            $this->menu = $this->getMenuById($menuId);
        }
    }
    
    public function validaAcessoItem($script = ""){
        
        $acesso = false;
        
        if($script)
            $_SERVER['SCRIPT_NAME'] = $script;
        
        if($_SERVER['SCRIPT_NAME'] == $this->getPaginaInicial()){
            $acesso = true;
        }else{
            
            $itens = $this->getItemByLink($_SERVER['SCRIPT_NAME']);
            
            if(!empty($itens)){
                foreach($itens as $item){
                    if($itensGrupos = $this->getArrItensByGrupos($item->getId())){
                        $acesso = true;
                        $this->item = $item;
                    }
                }
            }
        }
        
        return $acesso;
    }
    
    public function getSistema() {
        return $this->sistema;
    }

    public function getAba() {
        return $this->aba;
    }

    public function setSistema($sistema) {
        $this->sistema = $sistema;
    }

    public function setAba($aba) {
        $this->aba = $aba;
    }
    
    public function getErro() {
        return $this->erro;
    }

    public function setErro($erro) {
        $this->erro[] = $erro;
    }

}
?>