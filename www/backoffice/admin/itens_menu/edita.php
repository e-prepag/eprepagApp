<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."class/util/Validate.class.php";
require_once $raiz_do_projeto."class/util/Util.class.php";
require_once $raiz_do_projeto."public_html/sys/includes/language/eprepag_lang_pt.inc.php";

$_POST['nivel_id'] = 1;
$con = ConnectionPDO::getConnection();
if ( !$con->isConnected() ) {
    // retornar os erros: $con->getErrors();
    die('Erro#2');
}
$pdo = $con->getLink();

$msg = array();
$color = "txt-verde";


if(isset($_POST['nome']) && isset($_POST['id'])) { //novo e edita

    $validate = new Validate();
    
    if($validate->qtdCaracteres($_POST['nome'],2,256))
        $msg[] = "Nome inválido.";
    
    if($validate->numeros($_POST['item_aparece_menu']))
        $msg[] = "É necessário informar se o item aparecerá no menu.";
    
    if($validate->numeros($_POST['idMenu']))
        $msg[] = "É necessário informar o menu.";
    
    if(empty($msg)) {
        
        $texto = null;
        if(empty($_POST["link_linux"])){
            $_POST["link_linux"] = NULL;
        }else{
            $_POST["link_linux"] = filter_var($_POST['link_linux'],FILTER_SANITIZE_URL);
        }
        if(empty($_POST['id'])) { //novo
            $texto = "inseridos";
            $sql = "INSERT INTO bo_item (item_descricao, item_link, item_link_linux, item_monitor, menu_id, item_aparece_menu) VALUES (?, ?, ?, ?, ?, ?);";
            
            $params = array(
                            filter_var($_POST['nome'],FILTER_SANITIZE_STRING),
                            filter_var($_POST['link_windows'],FILTER_SANITIZE_URL),
                            $_POST["link_linux"],
                            filter_var($_POST['item_monitor'],FILTER_SANITIZE_STRING),
                            filter_var($_POST['idMenu'],FILTER_SANITIZE_NUMBER_INT),
                            filter_var($_POST['item_aparece_menu'],FILTER_SANITIZE_NUMBER_INT)
                        );
            
        }
        elseif(isset($_POST['id']) && is_numeric($_POST['id']) ){ //edita
            $texto = "atualizados";
            $sql = "UPDATE bo_item set item_descricao = ?, item_link  = ?, item_link_linux = ?, item_monitor = ?, menu_id = ?, item_aparece_menu = ? where item_id = ?;";
            $params = array(
                            filter_var($_POST['nome'],FILTER_SANITIZE_STRING),
                            filter_var($_POST['link_windows'],FILTER_SANITIZE_URL),
                            $_POST["link_linux"],
                            filter_var($_POST['item_monitor'],FILTER_SANITIZE_STRING),
                            filter_var($_POST['idMenu'],FILTER_SANITIZE_NUMBER_INT),
                            filter_var($_POST['item_aparece_menu'],FILTER_SANITIZE_NUMBER_INT),
                            filter_var($_POST['id'],FILTER_SANITIZE_NUMBER_INT)
                        );
        }
            
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        if($stmt->rowCount() == 1){
            
            if(isset($_POST["fixo"])){
                $sql = "SELECT menu_descricao FROM bo_menu WHERE menu_id = " . $_POST["idMenu"] . "LIMIT 1";
                $rs_menu = SQLexecuteQuery($sql);
                $menu = pg_fetch_array($rs_menu);
                $nome_antigo = Util::cleanStr($_POST["nome_antigo"]);
                $diretorio = Util::cleanStr($menu["menu_descricao"]);
                $caminho = RAIZ_DO_PROJETO . "backoffice/manuais/" . $diretorio . "/";
                if(file_exists($caminho . $nome_antigo . ".php")){
                    $arquivos = scandir($caminho);
                    foreach($arquivos as $arquivo){
                        if($arquivo != '.' && $arquivo != ".." && strpos($arquivo, $nome_antigo) !== false){
                            $nome_novo = str_replace($nome_antigo, Util::cleanStr($_POST["nome"]), $arquivo);
                            rename($caminho . $arquivo, $caminho . $nome_novo);
                        }                    
                    }
                    $link = "/manuais/" . $diretorio . "/" . Util::cleanStr($_POST["nome"]) . ".php";
                    $sql = "UPDATE bo_item SET item_link = '" . $link . "', item_link_linux = '" . $link . "' WHERE item_id = " . $_POST['id'];
                    $rs_item = SQLexecuteQuery($sql);
                }
                
                $msg[] = "Dados ".$texto." com sucesso. Clique <a href='lista.php'>aqui</a> para voltar.";
                $color = "txt-verde";
                unset($_SESSION["arrAbasVo"]);
                unset($_SESSION["arrMenu"]);

            }else{
                $msg[] = "Dados ".$texto." com sucesso. Clique <a href='lista.php'>aqui</a> para voltar.";
                $color = "txt-verde";
                unset($_SESSION["arrAbasVo"]);
                unset($_SESSION["arrMenu"]);
            }
        }else{
            $msg[] = "Erro ao executar a query. Entre em contato com o Administrador do Sistema.";
            $color = "txt-vermelho";
        }
        
    }else{
        $color = "txt-vermelho";
    }
    
}
else if(isset($_POST['id']) && is_numeric($_POST['id']) && empty($_POST['nome'])) { //editar
        
        $sql = "SELECT * FROM bo_item inner join bo_menu on bo_menu.menu_id = bo_item.menu_id and item_id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($_POST['id']));
        $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if(count($fetch) > 0){

            $id = $fetch[0]['item_id'];
            $nome = $fetch[0]['item_descricao'];
            $link_windows = $fetch[0]['item_link'];
            $link_linux = $fetch[0]['item_link_linux'];
            $monitor = $fetch[0]['item_monitor'];
            $idMenu = $fetch[0]['menu_id'];
            $fixo = $fetch[0]['menu_fixo'];
            $listagem = $fetch[0]['item_aparece_menu'];
            $_POST['aba'] = isset($_POST['aba']) ? $_POST['aba'] : $fetch[0]['aba_id'];
        }
}
else {
    $id = "";
    $nome = "";
    $link_windows = "";
    $link_linux = "";
    $idMenu = "";
    $monitor = "";
    $listagem = "";
}

$sql = "select * from bo_aba order by aba_order";
$stmt = $pdo->prepare($sql);
$stmt->execute();

$abas = $stmt->fetchAll(PDO::FETCH_OBJ);

$menus = array();

if(isset($_POST['aba'])){
    $sql = "SELECT * FROM bo_menu where aba_id = ".$_POST['aba']." order by menu_order";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $menus = $stmt->fetchAll(PDO::FETCH_OBJ);
}


?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="0">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li><a href="lista.php">Listagem</a></li>
        <li class="active">Gerenciamento de Itens do Menu</li>
    </ol>
</div>
<div class="col-md-12">
    <form method="post" action="lista.php">
        <input type="hidden" name="menu" value="<?php echo (isset($_POST['menu_item'])) ? $_POST['menu_item'] : "" ;?>">
        <input type="hidden" name="aba" value="<?php echo (isset($_POST['aba'])) ? $_POST['aba'] : "" ;?>">
        <input type="submit" class="btn btn-info" value="Voltar">
    </form>
</div>
<?php

if(!empty($msg))
{
?>
    
<div class="col-md-12 top10">
    <a href="edita.php" class="btn btn-success">Nova</a>
    <a href="reordena.php" class="btn btn-success">Reordenar</a>
</div>
<?php
    foreach($msg as $txt)
    {
?>
    <div class="col-md-12 top10 <?php echo $color;?>">
        <strong><?php echo $txt?></strong>
    </div>
<?php
    }
    die();
}
?>
<div class='row'>
        <div class='col-md-7 text-center top10'>
            <?php if(isset($fixo) && $fixo) { ?><span style="color: red;">* Este item pertence a um menu fixo e não pode ter sua aba ou menu alterados</span><?php } ?>
        </div>
    </div>
<div class="col-md-7 top20">
    <form method="post" id="filtro">
        <div class="form-group has-feedback txt-preto">
            <label class="control-label txt-preto text-right col-md-6" for="menu">
                Escolha a aba desejada:
            </label>
            <div class="col-md-6">
    <?php
            if(!empty($abas)){
    ?>
                <input type="hidden" name="id" value="<?php echo (isset($_POST['id'])) ? $_POST['id'] : "";?>">
                <select name="aba" id="aba" onchange="this.form.submit();" class="form-control" <?php if(isset($fixo) && $fixo) echo 'disabled'; ?>>
                    <option value="">Selecione a aba</option>
    <?php 
                foreach($abas as $aba){ 
    ?>
                    <option value="<?php echo $aba->aba_id; ?>" <?php if(isset($_POST['aba']) != "" && $aba->aba_id == $_POST['aba']) echo "selected"; ?>><?php echo $aba->aba_descricao; ?></option>
    <?php 
                } 
    ?>
                </select>
<?php
            }else{
?>
                Nenhuma aba foi encontrada
<?php

            }
?>
            </div>
        </div>
    </form>
</div>
<?php 
if(!empty($menus)){
?>
<form method="POST" id="form">
    <input type="hidden" name="id" id="id" value="<?php echo $id;?>">
    <div class="col-md-7 top10 txt-preto">
        <div class="form-group has-feedback">
            <label class="control-label  col-md-6 text-right" for="idMenu">
                Menu
            </label>
            <div class="col-md-6 ">
                <?php if(isset($fixo) && $fixo){ ?>
                    <input type="hidden" id="idMenu" name="idMenu" value="<?php echo $idMenu;?>">
                    <input type="hidden" id="fixo" name="fixo" value="1">
                    <input type="hidden" id="nome_antigo" name="nome_antigo" value="<?php echo $nome;?>">
                <?php } ?>
                <select name="idMenu" id="idMenu" class="form-control" <?php if(isset($fixo) && $fixo) echo 'disabled'; ?>>
<?php 
                foreach($menus as $menu){ 
?>
                    <option value="<?php echo $menu->menu_id; ?>" <?php if($idMenu != "" && $menu->menu_id == $idMenu) echo "selected"; ?>><?php echo (@constant(trim($menu->menu_descricao)) === null) ? $menu->menu_descricao : constant(trim($menu->menu_descricao)); ?></option>
<?php 
                } 
?>
                </select>
            </div>
        </div>
        <div class="form-group has-feedback">
            <label class="control-label top10 col-md-6 text-right" for="nome">
                Nome
            </label>
            <div class="col-md-6">
                <input type="text" class="form-control top10" name="nome" maxlength="250" id="nome" value="<?php echo $nome;?>">
                <input type="hidden" id="nomeConst" value="<?php echo (@constant(trim($nome)) === null) ? $nome : constant(trim($nome));?>">
                
            </div>
        </div>
        <div class="form-group has-feedback">
            <label class="control-label top10 col-md-6 text-right" for="link_windows">
                Link no Windows
            </label>
            <div class="col-md-6">
                <input type="text" class="form-control top10" name="link_windows" maxlength="250" id="link" value="<?php echo $link_windows;?>" <?php if(isset($fixo) && $fixo) echo 'readonly'; ?>>
            </div>
        </div>
        <div class="form-group has-feedback">
            <label class="control-label top10 col-md-6 text-right" for="link_linux">
                Link no Linux
            </label>
            <div class="col-md-6">
                <input type="text" class="form-control top10" name="link_linux" maxlength="250" id="link" value="<?php echo $link_linux;?>" <?php if(isset($fixo) && $fixo) echo 'readonly'; ?>>
            </div>
        </div>
        <div class="form-group has-feedback">
            <label class="control-label top10 col-md-6 text-right" for="item_monitor">
                Monitor
            </label>
            <div class="col-md-6 top10">
                <input type="text" class="form-control" name="item_monitor" maxlength="50" id="item_monitor" value="<?php echo $monitor;?>">
            </div>
        </div>
        <div class="form-group has-feedback">
            <label class="control-label top10 col-md-6 text-right" for="item_aparece_menu">Aparece na listagem?</label>
            <div class="col-md-6 top10">
                <select class="form-control w150" name="item_aparece_menu" id="item_aparece_menu" required="required">
                    <option value="">--</option>
                    <option value="1" <?php if($listagem == "1") echo "selected";?>>Sim</option>
                    <option value="0" <?php if($listagem == "0") echo "selected";?>>Não</option>
                </select>
            </div>
        </div>
    </div>
    <div class="col-md-5 espacamento">
        <button type="submit" class="btn btn-success"><?php echo(empty($id)?"Criar":"Alterar");?></button>
        <input type="hidden" name="aba" value="<?php echo (isset($_POST['aba'])) ? $_POST['aba'] : "" ;?>">
        <input type="hidden" name="menu_item" value="<?php echo (isset($_POST['menu_item'])) ? $_POST['menu_item'] : "" ;?>">
        <div class="alert top5 alert-danger fontsize-pp" role="alert">Para cadastrar itens do sys admin, deve-se colocar a constante correspondente no campo nome, sempre que o item for marcado para aparecer na listagem.</div>
    </div>
</form>
<?php
}

    if(!empty($id)){
?>
<div class="col-md-12 top50">
    <a href="#" id="callModalUsuarioGrupo" class="btn btn-info" >Vincular a grupo</a>
</div>
<?php
    }
?>
<div class="col-md-12" id="divvincula"></div>
<link href="<?php echo $server_url_ep; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $server_url_ep; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script>
    $("#callModalUsuarioGrupo").click(function(){
        $.ajax({
            type: "POST",
            data: { reqType: "montaHtml", item: $("#id").val() },
            url: "/ajax/usuariosItem.php",
            success: function(ret){
                $("#divvincula").html(ret);
            }
         });
    });
</script> 
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</body>
</html>