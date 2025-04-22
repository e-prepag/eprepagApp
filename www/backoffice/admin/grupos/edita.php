<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "class/util/Validate.class.php";
require_once $raiz_do_projeto . "class/util/Util.class.php";
require_once $raiz_do_projeto . "class/util/Login.class.php";
require_once $raiz_do_projeto . "public_html/sys/includes/language/eprepag_lang_pt.inc.php";

$con = ConnectionPDO::getConnection();
if (!$con->isConnected()) {
    // retornar os erros: $con->getErrors();
    die('Erro#2');
}

if (!b_IsBKOUsuarioAdminBKO()) {
    Util::redirect("/");
} elseif (isset($_POST['id']) && is_numeric($_POST['id']) && empty($_POST['nome'])) {

    $pdo = $con->getLink();
    $sql = "SELECT * FROM grupos_usuarios where grupos_id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(array($_POST['id']));
    $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (isset($fetch[0]['grupos_id']) && isset($fetch[0]['grupos_descricao'])) {
        $id = $fetch[0]['grupos_id'];
        $nome = $fetch[0]['grupos_descricao'];
    }
} else {
    $id = "";
    $nome = "";
}

$msg = array();

if (isset($_POST['nome']) && isset($_POST['id'])) {

    $validate = new Validate();

    if ($validate->qtdCaracteres($_POST['nome'], 2, 50))
        $msg[] = "Nome inválido.";

    if (empty($msg)) {

        $pdo = $con->getLink();

        $texto = null;
        if (empty($_POST['id'])) {
            $texto = "inserido";
            $sql = "INSERT INTO grupos_usuarios (grupos_descricao) VALUES (?);";
            $params = array(
                $_POST['nome']
            );
        } elseif (isset($_POST['id']) && is_numeric($_POST['id'])) {
            $texto = "atualizado";
            $sql = "UPDATE grupos_usuarios set grupos_descricao = ? where grupos_id = ?;";
            $params = array(
                $_POST['nome'],
                $_POST['id']
            );
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        if ($stmt->rowCount() == 1) {
            $msg[] = "Dados " . $texto . " com sucesso. Clique <a href='lista.php'>aqui</a> para voltar.";
            $color = "txt-verde";
        } else {
            $msg[] = "Erro ao executar a query. Entre em contato com o Administrador do Sistema.";
            $color = "txt-vermelho";
        }

    } else {
        $color = "txt-vermelho";
    }

}

$abasMenusItensMenu = $sistema->getAllItensByGrupo($id, false);
?>
<div class="col-md-12" id="divvincula"></div>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="0">BackOffice - <?php echo $currentAba->getDescricao(); ?></a></li>
        <li class="active"><a href="lista.php">Listagem</a></li>
        <li class="active">Edição</li>
    </ol>
</div>
<?php

if (!empty($msg)) {
    foreach ($msg as $txt) {
        ?>
        <div class="col-md-12 top10 <?php echo $color; ?>">
            <strong><?php echo $txt ?></strong>
        </div>
        <?php
    }
    die();
}
?>
<form method="POST" id="form">
    <input type="hidden" name="id" id="id" value="<?php echo $id; ?>">
    <div class="col-md-7 top20 txt-preto">
        <div class="form-group col-md-12 has-feedback">
            <label class="control-label col-md-3 text-right" for="nome">
                Nome
            </label>
            <div class="col-md-9">
                <input type="text" class="form-control" name="nome" maxlength="50" id="nome"
                    value="<?php echo $nome; ?>">
            </div>
        </div>
        <div class="col-md-offset-6 col-md-6">
            <button type="submit" class="btn btn-success"><?php echo (empty($id) ? "Criar" : "Alterar"); ?></button>
        </div>
    </div>
    <div class="col-md-5 espacamento">
        <?php
        if ($abasMenusItensMenu['backoffice'] || $abasMenusItensMenu['sysadmin']) {

            $abasAux = array();

            foreach ($abasMenusItensMenu as $sis => $sistemaMenus) {
                $continue = false;
                $abas = $sistemaMenus->getAbas();

                if (count($abas) > 0) {

                    foreach ($abas as $indAba => $aba) {

                        $menus = $aba->getMenus();

                        if (count($menus) <= 0)
                            continue;

                        foreach ($menus as $indMenu => $menu) {
                            $itensMenus = $menu->getItens();

                            if (count($itensMenus) > 0) {
                                $abasAux[$sis][$indAba] = $aba;
                            }
                        }
                    }
                }
            }

            foreach ($abasAux as $sis => $sistemaMenus) {
                $continue = false;
                $abas = $sistemaMenus;

                if (count($abas) > 0) {
                    ?>
                    <div class="top10 lista bg-primary txt-branco">
                        <a href="#<?= $sis; ?>" class="txt-branco" data-toggle="collapse"><strong>Sistema: <?= $sis; ?></strong></a>
                    </div>
                    <div id="<?= $sis; ?>" class="collapse">
                        <?php
                        foreach ($abas as $aba) {
                            $menus = $aba->getMenus();

                            if (count($menus) <= 0)
                                continue;
                            ?>
                            <div class="top10 lista bg-verde-claro txt-branco">
                                <a href="#aba<?= $aba->getId(); ?>" class="txt-branco" data-toggle="collapse">
                                    <strong><span class="glyphicon glyphicon-menu-right t0"></span>
                                        <?= $aba->getDescricao(); ?></strong>
                                </a>
                                <span class="pull-right">expandir todas <input id="<?= $aba->getId(); ?>" class="checkall"
                                        type="checkbox"></span>
                            </div>
                            <div id="aba<?= $aba->getId(); ?>" class="collapse">
                                <?php
                                foreach ($menus as $menu) {
                                    $itensMenus = $menu->getItens();

                                    if (count($itensMenus) <= 0)
                                        continue;
                                    ?>
                                    <div class="lista bg-info txt-branco">
                                        <a href="#menu<?= $menu->getId(); ?>" class="txt-branco" data-toggle="collapse">
                                            <strong><span class="glyphicon glyphicon-menu-right t0"></span><span
                                                    class="glyphicon glyphicon-menu-right t0"></span>
                                                <?php echo (@constant(trim($menu->getDescricao())) === null) ? $menu->getDescricao() : constant(trim($menu->getDescricao())); ?></strong>
                                        </a>
                                    </div>
                                    <div id="menu<?= $menu->getId(); ?>" class="collapse">
                                        <ul class="nav nav-pills nav-stacked nav-pills-stacked-example">
                                            <?php
                                            foreach ($itensMenus as $item) {
                                                ?>
                                                <li role="presentation">
                                                    <a href="<?php echo $item->getLink(); ?>">
                                                        <span class="glyphicon glyphicon-menu-right t0"></span><span
                                                            class="glyphicon glyphicon-menu-right t0"></span><span
                                                            class="glyphicon glyphicon-menu-right t0"></span>
                                                        <?php echo (@constant(trim($item->getDescricao())) === null) ? $item->getDescricao() : constant(trim($item->getDescricao())) ?>
                                                    </a>
                                                </li>
                                                <?php
                                            }
                                            ?>
                                        </ul>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    <?php
                }
            }
        }
        ?>
    </div>
</form>
<?php
if (!empty($id)) {
    ?>
    <div class="col-md-12 top50">
        <a href="#" id="callModalGrupoUsuario" class="btn btn-info">Vincular a usuário</a>
    </div>
    <?php
}
?>

<link href="<?php echo $server_url_ep; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $server_url_ep; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script>
    $(function () {

        $(".checkall").click(function () {

            var c = $(this).attr("id");

            if ($(this).is(":checked")) {
                $("#aba" + c).collapse('show');
                $("#aba" + c).children(".collapse").collapse('show');
            } else {
                $("#aba" + c).collapse('hide');
                $("#aba" + c).children(".collapse").collapse('hide');
            }

        });

        $(".list-group-item").click(function () {

            var url = '/admin/itens_menu/edita.php';
            var form = $('<form action="' + url + '" method="post">' +
                '<input type="text" name="id" value="' + $(this).attr("param") + '" />' +
                '</form>');
            $('body').append(form);
            form.submit();

        });

        $("#callModalGrupoUsuario").click(function () {
            $.ajax({
                type: "POST",
                data: { reqType: "montaHtml", grupo: $("#id").val() },
                url: "/ajax/usuariosGrupo.php",
                success: function (ret) {
                    $("#divvincula").html(ret);
                    $(".modal-backdrop").appendTo('#divvincula');
                }
            });
        });

    });
</script>
<?php
require_once $raiz_do_projeto . "backoffice/includes/rodape_bko.php";
?>
</body>

</html>