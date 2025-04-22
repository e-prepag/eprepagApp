<?php
require_once "../../../includes/constantes.php";
require_once DIR_CLASS ."pdv/controller/FuncionarioController.class.php";

$controller = new FuncionarioController;

if($_POST['sel_id'] && $_POST['sel_id'] > 0)
{    
    if($_POST["btSubmit"])
    {
        $controller->edita($_POST['sel_id']);
    }
    
    if($_POST["btRemoveAuth"]){
        $conexao = ConnectionPDO::getConnection()->getLink();

        $query = $conexao->prepare("UPDATE dist_usuarios_games_operador SET ugo_chave_autenticador = '' WHERE ugo_id = :ID;");
        $query->bindValue(":ID", $_POST['sel_id']);
        $query->execute();
    }

    $funcionario = $controller->pega($_POST['sel_id']);
}
else
{
    die("ERRO");
}

$banner = $controller->getBanner();

require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/header.php";

?>
<div class="container txt-azul-claro bg-branco">
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-12 espacamento">
                    <strong>GERENCIAR FUNCIONÁRIO</strong>
                </div>
            </div>
<?php
            if($controller->msg != "")
            {
?>
            <div class="row">
                <div class="col-md-12">
                    <p class="txt-vermelho"><?php echo $controller->msg; ?></p>
                </div>
            </div>
<?php
            }
?>
            <div class="row txt-cinza">
                <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12 espacamento txt-azul-claro">
                    <strong>Novo Funcionário</strong>
                </div>
                <div class="col-md-6 col-lg-6 col-sm-12 col-xs-12 top20">
                    <form method="post" id="edita">
                        <div class="col-md-4 col-lg-4 col-sm-4 col-xs-4">
                            <span class="pull-right">Nome:</span>
                        </div>
                        <div class="col-md-8 col-lg-8 col-sm-8 col-xs-8">
                            <input class="input-sm form-control" <?php if(isset($funcionario['ugo_nome'])) echo "value='".$funcionario['ugo_nome']."'";?> type="text" name="ugo_nome" id="ugo_nome">
                        </div>
                        <div class="col-md-4 col-lg-4 col-sm-4 col-xs-4 top10">
                            <span class="pull-right">Login:</span>
                        </div>
                        <div class="col-md-8 col-lg-8 col-sm-8 col-xs-8 top10">
                            <input class="input-sm form-control" <?php if(isset($funcionario['ugo_login'])) echo "value='".$funcionario['ugo_login']."'";?> type="text" name="ugo_login" id="ugo_login">
                        </div>
                        <div class="col-md-4 col-lg-4 col-sm-4 col-xs-4 top10">
                            <span class="pull-right">E-mail:</span>
                        </div>
                        <div class="col-md-8 col-lg-8 col-sm-8 col-xs-8 top10">
                            <input class="input-sm form-control" <?php if(isset($funcionario['ugo_email'])) echo "value='".$funcionario['ugo_email']."'";?> type="text" name="ugo_email" id="ugo_email">
                        </div>
                        <div class="col-md-4 col-lg-4 col-sm-4 col-xs-4 top10">
                            <span class="pull-right">Senha:</span>
                        </div>
                        <div class="col-md-8 col-lg-8 col-sm-8 col-xs-8 top10">
                            <span class="pull-left">******** <span class="c-pointer txt-azul-claro" id="alterarSenha">alterar</span></span>
                        </div>
                        <div class="col-md-4 col-lg-4 col-sm-4 col-xs-4 top10">
                            <span class="pull-right">Acesso:</span></div>
                        <div class="col-md-8 col-lg-8 col-sm-8 col-xs-8 top10">
                            <select name="ugo_tipo" id="ugo_tipo" class="form-control w-auto">
                                <option value="1" <?php if(isset($funcionario['ugo_tipo']) && $funcionario['ugo_tipo'] == 1) echo "selected"; ?> >Comprar e emitir</option>
                                <option value="0" <?php if(isset($funcionario['ugo_tipo']) && $funcionario['ugo_tipo'] == 0) echo "selected"; ?>>Emitir</option>
                            </select>
                        </div>
                        <div class="col-md-4 col-lg-4 col-sm-4 col-xs-4 top10">
                            <span class="pull-right">Ativo:</span>
                        </div>
                        <div class="col-md-8 col-lg-8 col-sm-8 col-xs-8 top10">
                            <select name="ugo_ativo" id="ugo_ativo" class="form-control w-auto">
                                <option value="0" <?php if(isset($funcionario['ugo_ativo']) && $funcionario['ugo_ativo'] == 0) echo "selected"; ?>>Não</option>
                                <option value="1" <?php if(isset($funcionario['ugo_ativo']) && $funcionario['ugo_ativo'] == 1) echo "selected"; ?>>Sim</option>
                            </select>
                        </div>
                        <div class="col-md-4 col-lg-4 col-sm-4 col-xs-4 top10">
                            <a href="/creditos/funcionarios.php" class="btn btn-primary pull-right">Voltar</a>
                        </div>
                        <div class="col-md-8 col-lg-8 col-sm-8 col-xs-8 top10">
                            <input type="hidden" name="sel_id" id="sel_id" class="btn btn-info" value='<?php echo $_POST['sel_id']; ?>'>
                            <input type="submit" name="btSubmit" id="btSubmit" class="btn btn-info" value='Editar'>
                        </div>
                    </form>
                    <?php if(!empty($funcionario['ugo_chave_autenticador']))
                    {
                    ?>
                    <div class="col-md-8 col-lg-8 col-sm-8 col-xs-8 top10">
                        <form id="removeAuth" method="post">
                                <input type="hidden" name="sel_id" id="sel_id" class="btn btn-info" value='<?php echo $_POST['sel_id']; ?>'>
                                <input type="submit" name="btRemoveAuth" id="btRemoveAuth" class="btn btn-danger" value='Remover autenticador'>
                        </form>
                    </div>
                    <?php
                    }
                    ?>
                </div>
                <div class="col-md-6 col-lg-6 col-sm-12 top20 col-xs-12 div-historico">
<?php
                $sql  = "select * from dist_usuarios_games_operador_log ugl " .
                                "where ugl.ugol_ugo_id = " . $funcionario['ugo_id'];

                if(!isset($ncamp)) 	$ncamp = " ugol_data_inclusao ";
                if(!isset($ordem)) 	$ordem = 1;
                if(!isset($inicial)) 	$inicial = 0;
                $rs_usuario_log = SQLexecuteQuery($sql);
                if($rs_usuario_log) 
                {
                    $sql .= " order by " . $ncamp . " " . ($ordem == 1?"desc":"asc");
                    $rs_usuario_log = SQLexecuteQuery($sql);            
?>
                    <form method="POST" id="iteracoesFuncionario" action="/creditos/pedido/detalhe.php">
                        <p>Histórico</p>
                        <table class="table bg-branco txt-preto text-center">
                            <thead>
                                <tr class="bg-cinza-claro text-center">
                                    <th>Data</th>
                                    <th>Tipo</th>
                                    <th>Pedido</th>
                                    <th>IP</th>
                                </tr>
                            </thead>
                            <tbody>
<!-- -->
<?php
                        if(!$rs_usuario_log || pg_num_rows($rs_usuario_log) == 0)
                        {
?>
                            <tr> 
                                <td align="center" colspan="4">Nenhum histórico encontrado</td>
                            </tr>
<?php 
                        }
                        else 
                        {
                            while ($rs_usuario_log_row = pg_fetch_array($rs_usuario_log)) 
                            {
?>
                            <tr class="trListagem"> 
                                <td><?php echo formata_data_ts($rs_usuario_log_row['ugol_data_inclusao'], 0, true, true) ?></td>
                                <?php $ugol_uglt_id = $rs_usuario_log_row['ugol_uglt_id'];?>
                                <td><?php echo $GLOBALS['USUARIO_GAMES_LOG_TIPOS_DESCRICAO'][$ugol_uglt_id] ?></td>
                                <td align="center"><span class="class1"><?php if($rs_usuario_log_row['ugol_vg_id']) { ?><a href="#" class="detalhePedido" pedido="<?php echo $rs_usuario_log_row['ugol_vg_id']; ?>"><?php echo $rs_usuario_log_row['ugol_vg_id'] ?></a><?php } else echo "-"; ?></span></td>
                                <td align="center"><?php echo $rs_usuario_log_row['ugol_ip'] ?></td>
                            </tr>
<?php
                            }
                        }
?>                
                            </tbody>
                        </table>
                        <input type="hidden" name="tf_v_codigo_detalhe" id="tf_v_codigo_detalhe" value="">
                    </form>
                    <script>
                        $(function(){
                            $(".detalhePedido").click(function(){
                                $("#tf_v_codigo_detalhe").val($(this).attr("pedido"));
                                $("#iteracoesFuncionario").submit();
                            });
                            
                            $("#alterarSenha").click(function(){
                                $("#edita").attr("action","alterar_senha.php").submit();
                            });
                        });
                    </script>
<?php
                }
?>
                </div>
            </div>
        </div>
    </div>
</div>
<link href="/js/jqueryui/css/custom-theme/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/jqueryui/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="/js/validaSenha.js"></script>
<?php
require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/footer.php";
?>