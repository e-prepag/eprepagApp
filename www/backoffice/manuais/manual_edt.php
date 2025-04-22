<?php

//INCLUDES INCIAIS

require_once '../../includes/constantes.php';
require_once RAIZ_DO_PROJETO . "includes/security.php";

//VERIFICANDO AÇÃO REQUISITADA

if($acao == 'novo')
{
    $acao = 'inserir';
}
else
{
    $acao = 'atualizar';
}

//CARREGAR OS PROJETOS JÁ EXISTENTES
$sql = "SELECT
            menu_id, menu_descricao
        FROM    
            bo_menu
        WHERE   
            aba_id = " . $currentAba->getId();
$rs_menus = SQLexecuteQuery($sql);

?>

<!--JAVASCRIPTS NECESSÁRIOS-->

<link href="<?php echo $url; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url; ?>/js/global.js"></script>

<!--CABEÇALHO-->

<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><?php if($acao == 'atualizar') echo "Edi&ccedil;&atilde;o"; else echo"Cadastro"; ?></li>
    </ol>
</div>

<!--DIV DE EXIBIÇÃO DE MENSAGEM-->

<div class="row">
    <div class="col-md-12">
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

<div class="row" style="padding: 10px;">
    <div class="col-md-12 p-left25">
        <h4 class="txt-azul-claro bottom50">Criação de Manuais</h4>
    </div>
</div>
<!--DIV COM FORMULÁRIO DE CADASTRO-->
<div class='row txt-preto'>
    <div class='col-md-offset-1 col-md-10'>
        <form class="form-horizontal" method="POST" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']; ?>" name="frmPreCadastro" id="frmPreCadastro">
            <!--CAMPOS VERIFICANDO SE VAI SER UMA INSERÇÃO OU UM UPDATE-->
            <input type="hidden" name="acao" id="acao" value="<?php if(isset($acao)) echo $acao; ?>" />
            <input type="hidden" name="manual_id_update" id="manual_id_update" value="<?php if(isset($manual_id)) echo $manual_id; ?>" />
            <input type="hidden" name="menu_id_antigo" id="menu_id_antigo" value="<?php if(isset($menu_id)) echo $menu_id; ?>" />
            <div class="form-group">
                <div class="row">
                    <div class="col-md-3 text-right">
                        <label for="">* Nome do manual:</label>
                    </div>
                    <div class="col-md-9">
                        <input class="form-control" name="manual_nome" type="text" id="manual_nome" size="100" maxlength="256" <?php if($acao == 'atualizar' && isset($manual_nome)) echo "readonly"; ?> value="<?php if(isset($manual_nome)) echo $manual_nome; ?>" required/>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <div class="col-md-3 text-right">
                        <label for="">Criar novo projeto: </label>
                    </div>
                    <div class="col-md-9">
                        <div class="row">
                            <div class="col-md-1 text-left">
                                <input type="checkbox"  name="novo_projeto" id="novo_projeto" value="1">
                            </div>
                            <div id="tr_lista_projetos">
                                <div class="col-md-3 text-right">
                                    <label for="">Projetos</label>
                                </div>
                                <div class="col-md-8 text-left">
                                    <select class="form-control" name="menu_id" id="menu_id">
                                        <!--LISTANDO PROJETOS EXISTENTES-->
                                        <?php 
                                            while($menu = pg_fetch_array($rs_menus)){
                                        ?>
                                                <option value="<?php echo $menu["menu_id"] ?>" <?php if(isset($menu_id) && $menu["menu_id"] == $menu_id) echo "selected"; ?>> <?php echo $menu["menu_descricao"] ?> </option>
                                        <?php
                                            }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div id="tr_novo_projeto" class="dnone">
                                <div class="col-md-3 text-right">
                                    <label for="">* Novo projeto:</label>
                                </div>
                                <div class="col-md-8">
                                    <input class="form-control" name="novo_projeto_nome" type="text" id="novo_projeto_nome"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <div class="row">
                    <div class="col-md-3 text-right">
                        <label for=""><?php if(!empty($manual_pdf)) { echo "Carregar novo pdf"; } else { echo "Carregar PDF"; } ?>: </label>
                    </div>
                    <div class="col-md-9 text-left">
                        <input type="file" class="btn btn-sm btn-info" name="manual_pdf" id="manual_pdf" <?php if(empty($manual_pdf)) { echo "required"; } ?>/>&nbsp; Formatos Permitidos (PDF)
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <div class="col-md-12 text-right">
                        <button type="submit" class="btn btn-info">Salvar</button>
                        <a href="/manuais/index.php" class="btn btn-info">Voltar</a>
                    </div>
                </div>
            </div>
            
        </form>
    </div>
</div>

<script>
    $(document).ready(function(){
        $("#novo_projeto").change(function(){
           if($(this).is(":checked")){
               $("#tr_lista_projetos").addClass("dnone");
               $("#tr_novo_projeto").removeClass("dnone");
           }else{
               $("#tr_lista_projetos").removeClass("dnone");
               $("#tr_novo_projeto").addClass("dnone");
           } 
        });
    });
</script>

<?php

    require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";

?>