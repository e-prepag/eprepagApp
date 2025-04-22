<?php
require_once DIR_CLASS."view/SistemaVO.class.php";

class SistemaDAO{
    
    private $pdo = false;
    
    public $qtdItens = 0;
    
    public $idUsuario;
    
    protected $itensUsuario = array();
    
    public $arrIdGrupos = array();
    
    public $paginaInicial;
    
    public $arrAbasVO;
    
    public function __construct(){
        
        $con = ConnectionPDO::getConnection();
        
        if (!$con->isConnected()){
            throw new Exception("Erro de conexão.");
        }else{
            $this->pdo = $con->getLink();
        }
    }
    
    public function getArrIdGrupos() {
        return $this->arrIdGrupos;
    }

    public function setArrIdGrupos($arrIdGrupos) {
        $this->arrIdGrupos = $arrIdGrupos;
    }
    
    public function getPaginaInicial() {
        return $this->paginaInicial;
    }

    public function setPaginaInicial($paginaInicial) {
        $this->paginaInicial = $paginaInicial;
    }
    
    protected function getAbasMenuItensMenu($sistema, $aba = "", $session = true){
        

        $arrAbas = $this->getAbas($sistema,$aba, true, $session);
        
        $objSistema = new SistemaVO();
        $objSistema->setTipo($sistema);
        $objSistema->setAbas($arrAbas);
        
        return $objSistema;
    }
    
    protected function getAbas($sistema, $strAba = "", $withMenus = false, $session = true){
        
        //Recebe vazio caso seja o primeiro acesso.
        //Do contrário ele recebe as abas carregadas na sessão
        $arrAbas = $this->getArrAbasVO($session);
                
        if(empty($arrAbas)){
            
//            if(!isset($_SESSION["sistemas"])){
//                $_SESSION["sistemas"] = array();
//            }
//            array_push($_SESSION["sistemas"], $sistema);
                        				
            $where = "where aba_sistema = '$sistema'";
        
            if($strAba != ""){
                $where .= " and aba_order = $strAba";
            }

            $sql = "SELECT * FROM bo_aba $where order by aba_order asc";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();

            $fetchAbas = $stmt->fetchAll(PDO::FETCH_OBJ);

            if(count($fetchAbas) > 0){
                foreach($fetchAbas as $aba){

                    $objAba = new AbaVO();
                    $objAba->setDescricao($aba->aba_descricao);
                    $objAba->setId($aba->aba_id);
                    $objAba->setOrdem($aba->aba_order);
                    $objAba->setLink($aba->aba_link);
                    $objAba->setSistema($sistema);

                    $arrAbas[] = $objAba;
                    unset($aba);
                }

                $this->setArrAbasVO($arrAbas);
            }
            
        }
        
        if(!empty($arrAbas) && $withMenus){
            foreach($arrAbas as $aba){
                if($strAba !== "" && $aba->getOrdem() != $strAba)
                    continue;
                
                $id = $aba->getId();
                
                $menus = $this->getMenus($id, $withMenus, $session);
                $aba->setMenus($menus);
                
                //if($strAba !== "" && $aba->getOrdem() == $strAba){
                    //unset($arrAbas);
                    //$arrAbas[] = $aba;
                    //break;

                //}
                $arrAuxAbas[] = $aba;
                
            }
        }
        
        if(isset($arrAuxAbas)){
            $arrAbas = $arrAuxAbas;
        }
        
        if($session){
            $_SESSION[SISTEMA]["arrAbasVo"] = serialize($arrAbas);
        }
        
        return $arrAbas;
        
    }
    
    protected function getMenus($idAba, $withItens = false, $session = true){
        
        
        $auxSession = array();
        
        if($session && isset($_SESSION[SISTEMA]["arrMenu"][$idAba]["menus"])){
            $auxSession = $_SESSION[SISTEMA]["arrMenu"][$idAba];
        }
        //Caso o menu já tenha sido carregado nessa session
        //o programa não realiza a pesquisa no banco novamente
        if(!isset($auxSession["menus"])){
            $auxSession["qtdItens"] = 0;
            $sql = "SELECT * FROM bo_menu where aba_id = ? order by menu_order asc";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(
                    array(
                        $idAba
                    )
                );

            $fetchMenus = $stmt->fetchAll(PDO::FETCH_OBJ);
            $arrMenus = array();

            foreach($fetchMenus as $menu){
                $objMenu = new MenuVO();
                $objMenu->setDescricao($menu->menu_descricao);
                $objMenu->setId($menu->menu_id);
                $objMenu->setOrdem($menu->menu_order);
                $objMenu->setIdAba($idAba);

                if($withItens){
                    $itensMenu = $this->getItensMenu($menu->menu_id);
                    $auxSession["qtdItens"] += count($itensMenu);
                    
                    $objMenu->setItens($itensMenu);
                }

                $arrMenus[] = $objMenu;
                unset($objMenu);
            }
            $auxSession["menus"] = serialize($arrMenus);
        }
        
        if($session){
            $_SESSION[SISTEMA]["arrMenu"][$idAba] = $auxSession;
        }
        
        $arrMenus = unserialize($auxSession["menus"]);
        return $arrMenus;
    }
    
    protected function getItensMenu($id){
        
        $where = " AND item_link_linux IS NOT NULL";
        $itensUsuario = $this->getItensUsuario();
        //echo "<script>console.log(".json_encode($itensUsuario).")</script>";
        if(!empty($itensUsuario)){
            
            $in = implode(",",$itensUsuario);
            $where .= " AND item_id in($in)";
        }
        
        if($this->getPaginaInicial() == $_SERVER['SCRIPT_NAME']){
            $where .= " AND item_aparece_menu = 1";
        }
        
        $sql = "SELECT * FROM bo_item WHERE menu_id = ? $where ORDER BY item_order asc";
                
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(
                array(
                    $id
                )
            );

        $fetchItensMenu = $stmt->fetchAll(PDO::FETCH_OBJ);
        $arrItensMenu = array();
        
		//echo "<script>console.log(".json_encode($fetchItensMenu).")</script>";
		
        if(count($fetchItensMenu) > 0){
            foreach($fetchItensMenu as $item){

                $objItem = new ItemVO();
                $objItem->setDescricao($item->item_descricao);
                $objItem->setId($item->item_id);
                $objItem->setOrdem($item->item_order);
                $objItem->setLink($item->item_link_linux);
                $objItem->setChaveMonitor($item->item_monitor);
                $objItem->setApareceNaListagem($item->item_aparece_menu);
                $objItem->setIdMenu($id);

                $arrItensMenu[] = $objItem;
                $this->qtdItens++;
                unset($objItem);
            }
        }
        
        return $arrItensMenu;
                
    }
    
    public function getItemByLink($link){
        
        $sql = "select * from bo_item WHERE item_link_linux = '$link' AND item_link_linux IS NOT NULL";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        $fetchItensMenu = $stmt->fetchAll(PDO::FETCH_OBJ);
        $arrItensMenu = array();

        if(count($fetchItensMenu) > 0){
            foreach($fetchItensMenu as $item){

                $objItem = new ItemVO();
                $objItem->setDescricao($item->item_descricao);
                $objItem->setId($item->item_id);
                $objItem->setOrdem($item->item_order);
                $objItem->setLink($item->item_link_linux);
                $objItem->setChaveMonitor($item->item_monitor);
                $objItem->setIdMenu($item->menu_id);

                $arrItensMenu[] = $objItem;
                $this->qtdItens++;
                unset($objItem);
            }
        }
        
        return $arrItensMenu;
        
    }
    
    public function setArrIdsGruposUsuario(){
        
        $sql = "SELECT * FROM grupos_acesso_usuarios where id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(
                array(
                    $this->idUsuario
                )
            );

        $fetchGAcesso = $stmt->fetchAll(PDO::FETCH_OBJ);
        $arrIdGrupos = array();

        if(count($fetchGAcesso) > 0){
            foreach($fetchGAcesso as $gAcesso){
                
                $arrIdGrupos[] = $gAcesso->grupos_id;
                
            }
        }
        
        //Compara os grupos a que o usuário pertence no momento
        //com os grupos que ele pertencia no acesso anterior
        //Caso haja diferença, os menus e abas devem ser recarregados
        //a fim de que exiba ou esconda os menus de acordo com as 
        //permissões do novo conjunto de grupos
        if(isset($_SESSION["arrIdGrupos"])){
            $count_vetor1 = count(unserialize($_SESSION["arrIdGrupos"]));
            $count_vetor2 = count($arrIdGrupos);
            if($count_vetor1 != $count_vetor2){
                unset($_SESSION[SISTEMA]["arrAbasVo"]);
                unset($_SESSION[SISTEMA]["arrMenu"]);
            }
            else{
                $diff = array_diff(unserialize($_SESSION["arrIdGrupos"]), $arrIdGrupos);
                if(!empty($diff)){
                    unset($_SESSION[SISTEMA]["arrAbasVo"]);
                    unset($_SESSION[SISTEMA]["arrMenu"]);
                }
            }
        }
        $_SESSION["arrIdGrupos"] = serialize($arrIdGrupos);
        $this->setArrIdGrupos($arrIdGrupos);
        
    }
    
    protected function getArrItensByGrupos($idItem = null){
        
        $arrIdGrupos = $this->getArrIdGrupos();
        
        if(empty($arrIdGrupos))
            $this->setArrIdsGruposUsuario();
        
        $where = "";
        
        if($idItem !== null){
            $where = " and item_id = $idItem ";
        }
        
        $stridsGrupos = implode(",",$this->arrIdGrupos);
        $arrIdItens = array();
        
        if($stridsGrupos != ''){
            $sql = "SELECT item_id FROM nivel_acesso_item_grupo where grupos_id in($stridsGrupos) $where group by item_id";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();

            $fetchitensAcessoGrupos = $stmt->fetchAll(PDO::FETCH_OBJ);

            if(count($fetchitensAcessoGrupos) > 0){
                foreach($fetchitensAcessoGrupos as $gAcesso){

                    $arrIdItens[] = $gAcesso->item_id;

                }
            }
        }else{
            $arrIdItens = false;
        }
        
        return $arrIdItens;
    }
    
    protected function getMenuById($id){
        
        $sql = "SELECT * FROM bo_menu where menu_id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(
                array(
                    $id
                )
            );

        $fetchMenus = $stmt->fetchAll(PDO::FETCH_OBJ);
        $arrMenus = array();
        
        foreach($fetchMenus as $menu){

            $objMenu = new MenuVO();
            $objMenu->setDescricao($menu->menu_descricao);
            $objMenu->setId($menu->menu_id);
            $objMenu->setOrdem($menu->menu_order);
            $objMenu->setIdAba($menu->aba_id);

            $arrMenus[] = $objMenu;
            unset($objMenu);
        }

        return $arrMenus;
    }
    
    public function getItensUsuario() {
        return $this->itensUsuario;
    }

    public function setItensUsuario($itensUsuario) {
        $this->itensUsuario = $itensUsuario;
    }
    
    public function getIdUsuario() {
        return $this->idUsuario;
    }

    public function setIdUsuario($idUsuario) {
        $this->idUsuario = $idUsuario;
    }
    
    public function getArrAbasVO($session = true) {
        if($session && isset($_SESSION[SISTEMA]["arrAbasVo"])){
            $this->setArrAbasVO(unserialize($_SESSION[SISTEMA]["arrAbasVo"]));
        }
        return $this->arrAbasVO;
    }

    public function setArrAbasVO($arrAbasVo) {
        $this->arrAbasVO = $arrAbasVo;
    }

}
?>