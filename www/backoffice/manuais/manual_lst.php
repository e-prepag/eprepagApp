<?php

require_once '../../includes/constantes.php';
require_once RAIZ_DO_PROJETO . "includes/security.php";

//inclue a classe para a listagem
include RAIZ_DO_PROJETO . "class/util/classGeralLista.inc.php";

// valores padroes para o inicio e limite da listagem, ordenacao e direcao da ordenacao
$inicio		= isset($_POST['inicio'])		? htmlentities($_POST['inicio'])		: 0;
$limite		= isset($_POST['limite'])		? htmlentities($_POST['limite'])		: 100;
$sort		= isset($_POST['sort'])			? htmlentities($_POST['sort'])			: 'item_descricao';
$dir		= isset($_POST['dir'])			? htmlentities($_POST['dir'])			: '';

$manual_id	= isset($_REQUEST['manual_id'])	? htmlentities($_REQUEST['manual_id'])	: '';
$manual_nome = isset($_REQUEST['manual_nome'])? htmlentities($_REQUEST['manual_nome']) : '';

if(!empty($manual_id) && !is_int($manual_id)){
    $erro_lst = "O código do manual deve ser um número";
}


$sql = "SELECT
                item.item_id,
                item.item_descricao,
                item.item_link_linux
        FROM    
                bo_item as item
        INNER JOIN 
                bo_menu as menu ON item.menu_id = menu.menu_id
        WHERE   
                item.item_id != " . $sistema->item->getId() . "  
                AND menu.aba_id = " . $currentAba->getId();
if(!isset($erro_lst)){
    if (!empty($manual_nome))
            $sbds_aux[] = "upper(item.item_descricao) LIKE '%" . strtoupper($manual_nome) . "%'";
    if (!empty($manual_id))
            $sbds_aux[] = "item.item_id = ". $manual_id ;
    if (isset($sbds_aux) && is_array($sbds_aux)) {
            $sql .= ' AND ' . implode(' AND ', $sbds_aux);
    }
}
$rs = SQLexecuteQuery($sql);

?>

<!--Cabeçalho-->
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><?php echo $sistema->item->getDescricao() ; ?></li>
    </ol>
</div>

<div class='row'>
    <div id="msg" name="msg" class="lstDado">
        <?php

            if(!empty($msg)){
                echo "<div class='alert alert-danger'>";
                foreach($msg as $m){
                    echo $m . "<br>";
                }
                echo "</div>";
            }

        ?>
    </div>
</div>

<!--Botão para novo manual-->
<div class="row p-left15">
    <div class="col-md-12 ">
        <a href="index.php?acao=novo" class="btn btn-sm btn-info">Novo</a>
    </div>
</div>
<div class="row top10">
    <div class="col-md-12">
        <?php
            if(!empty($erro_lst)){
                echo "<div class='alert alert-danger'>";
                echo $erro_lst;
                echo "</div>";
            }
        ?>
    </div>
</div>
<div class="row" style="padding: 10px;">
    <div class="col-md-12 p-left25">
        <h4 class="txt-azul-claro">Filtro de pesquisa</h4>
    </div>
</div>
<div class="row">
    <div class="col-md-12 top10 txt-preto">
        <form id="form1" name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <div class="row">
                <div class="col-md-5 text-center">
                    <label for="">Código do Manual: </label>
                    <input name="manual_id" type="text" id="manual_id" size="20" maxlength="20" value="<?php echo ($manual_id) ? $manual_id : "";?>"/>
                </div>
                <div class="col-md-5 text-center">
                    <label for="">Nome do Manual: </label>
                    <input name="manual_nome" type="text" id="manual_nome" size="30" maxlength="50" value="<?php echo ($manual_nome) ? $manual_nome : "";?>"/>
                </div>
                <div class="col-md-2 text-center">
                    <input name="btn_pesquisar" class="btn btn-sm btn-info" type="submit" id="btn_pesquisar" value="Pesquisar" />
                </div>
            </div>

        </form>
    </div>
</div>
<div class="col-md-12 bg-cinza-claro top10">
    <table id="table" class="table bg-branco txt-preto fontsize-p">
        <thead>
            <tr class="bg-cinza-claro">
                <th>ID</th>
                <th>Nome do Manual</th>
                <th>Link</th>
                <th>
            </tr>
        </thead>
        <tbody>
            <?php
                while ($rsRow = pg_fetch_array($rs)) {
            ?>
                    <tr class="trListagem"> 
                        <td><?php echo $rsRow['item_id']; ?></td>
                        <td><?php echo $rsRow['item_descricao']; ?></td>
                        <td><?php echo $rsRow['item_link_linux']; ?></td>
                        <td><a href="index.php?acao=editar&manual_id=<?php echo $rsRow['item_id'] ?>"><img src="/images/pencil.png"></a></td>
                    </tr>
            <?php        
                }//foreach
            ?>
        </tbody>
    </table>
</div>
</table>
<script>
    
</script>