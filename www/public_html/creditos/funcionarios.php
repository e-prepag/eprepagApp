<?php

require_once "../../includes/constantes.php";
require_once DIR_CLASS ."pdv/controller/FuncionarioController.class.php";

$controller = new FuncionarioController;

$banner = $controller->getBanner();

require_once "includes/header.php";

$max = 200; 
$inicial = 0;

$sql = "select * from dist_usuarios_games_operador ugo where ugo.ugo_ug_id = ".$controller->usuarios->getId()." order by ugo.ugo_nome";
$res_count = SQLexecuteQuery($sql);
$total_table = pg_num_rows($res_count);
//echo "sql: $sql<br>";
//echo "total_table: $total_table<br>";

$sql .= " limit ".$max; 
$sql .= " offset ".$inicial;
$rs_operadores = SQLexecuteQuery($sql);
//echo "sql: $sql<br>";

if($max + $inicial > $total_table) $reg_ate = $total_table;
else $reg_ate = $max + $inicial;

$pagina_titulo = "Lista Funcionários";

$arr_rs_operadores = pg_fetch_all($rs_operadores);
?>
<div class="container txt-azul-claro bg-branco">
    <div class="row">
        <div class="col-md-10">
            <div class="row">
                <div class="col-md-12 espacamento">
                    <strong>GERENCIAR FUNCIONÁRIOS</strong>
                </div>
            </div>
            <div class="row txt-cinza espacamento">
                <div class="col-md-12 bg-cinza-claro">
                    <form method="post" id="main_form" action="/creditos/funcionario/edita.php">
<?php
        if($rs_operadores && $total_table > 0) 
        {
            foreach($arr_rs_operadores as $ind => $rs_operadores_row)
            {
?>
                    <div class="col-xs-12 col-sm-12 bg-branco hidden-lg hidden-md espacamento borda-fina">
                        <div class="row">
                            <div class="col-xs-5 col-sm-5">
                                Login:
                            </div>
                            <div class="col-xs-5 col-sm-5 hidden-lg hidden-md">
                                <strong><?php echo $rs_operadores_row['ugo_login'] ?></strong>
                            </div>
                            <div class="col-xs-2 col-sm-2 hidden-lg hidden-md">
                                <strong><span op="<?php echo $rs_operadores_row['ugo_id'] ?>" class="glyphicon txt-azul-claro c-pointer detalhe glyphicon-zoom-in"></span></strong>
                            </div>
                            <div class="col-xs-5 col-sm-5">
                                Nome:
                            </div>
                            <div class="col-xs-7 col-sm-7 hidden-lg hidden-md">
                                <strong><?php echo $rs_operadores_row['ugo_nome'] ?></strong>
                            </div>
                            <div class="col-xs-5 col-sm-5">
                                Ativo:
                            </div>
                            <div class="col-xs-7 col-sm-7 hidden-lg hidden-md">
                                <strong><?php echo ($rs_operadores_row['ugo_ativo']==1)?"Sim":"<font color='#FF0000'>Não</font>"; ?></strong>
                            </div>
                            <div class="col-xs-5 col-sm-5">
                                Acesso:
                            </div>
                            <div class="col-xs-7 col-sm-7 hidden-lg hidden-md">
                                <strong><?php echo $rs_operadores_row['ugo_tipo'] == 1 ? "Comprar e emitir" : "Emitir"; ?></strong>
                            </div>
                            <div class="col-xs-5 col-sm-5">
                                Inclusão:
                            </div>
                            <div class="col-xs-7 col-sm-7 hidden-lg hidden-md">
                                <strong><?php echo formata_data($rs_operadores_row['ugo_data_inclusao'], 0) ?></strong>
                            </div>
                            <div class="col-xs-5 nowrap col-sm-5">
                                Último acesso:
                            </div>
                            <div class="col-xs-7 col-sm-7 hidden-lg hidden-md">
                                <strong><?php echo formata_data($rs_operadores_row['ugo_data_ultimo_acesso'], 0) ?></strong>
                            </div>
                            <div class="col-xs-5 col-sm-5">
                                Acessos:
                            </div>
                            <div class="col-xs-7 col-sm-7 hidden-lg hidden-md">
                                <strong><?php echo $rs_operadores_row['ugo_qtde_acessos'] ?></strong>
                            </div>
                            <div class="col-xs-5 col-sm-5">
                                Possui autenticador:
                            </div>
                            <div class="col-xs-7 col-sm-7 hidden-lg hidden-md">
                                <strong><?php echo empty($rs_operadores_row['ugo_chave_autenticador']) ? "Não" : "Sim" ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="clearfix"></div>
<?php               	
            }
        }
        else 
        {
?>
                    <tr>
                        <td colspan='8'><p class="txt-vermelho">Não foram encontrados funcionários</p></td>
                    </tr>
<?php
        }
?>     
                    
                    <table class="table bg-branco txt-preto text-center hidden-sm hidden-xs">
                        <thead>
                            <tr class="bg-cinza-claro text-center">
                                <th>Login</th>
                                <th>Nome</th>
                                <th>Ativo</th>
                                <th>Acesso</th>
                                <th>Inclusão</th>
                                <th>Último acesso</th>
                                <th>Acessos</th>
                                <th>Possui autenticador</th>
                                <th>&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
<!-- -->
<?php
        if($rs_operadores && $total_table > 0) 
        {
?>
                    <input type="hidden" name="sel_id" id="sel_id" value="">
<?php
            foreach($arr_rs_operadores as $ind => $rs_operadores_row)
            {
?>
                    <tr class="trListagem">
                        <td><?php echo $rs_operadores_row['ugo_login'] ?></td>
                        <td><?php echo $rs_operadores_row['ugo_nome'] ?></td>
                        <td><?php echo ($rs_operadores_row['ugo_ativo']==1)?"Sim":"<font color='#FF0000'>Não</font>"; ?></td>
                        <td><?php echo $rs_operadores_row['ugo_tipo'] == 1 ? "Comprar e emitir" : "Emitir"; ?></td>
                        <td><?php echo formata_data($rs_operadores_row['ugo_data_inclusao'], 0) ?></td>
                        <td><?php echo formata_data($rs_operadores_row['ugo_data_ultimo_acesso'], 0) ?></td>
                        <td><?php echo $rs_operadores_row['ugo_qtde_acessos'] ?></td>
                        <td><?php echo empty($rs_operadores_row['ugo_chave_autenticador']) ? "Não" : "Sim" ?></td>
                        <td><span op="<?php echo $rs_operadores_row['ugo_id'] ?>" class="glyphicon txt-azul-claro c-pointer detalhe glyphicon-zoom-in"></span></td>
                    </tr>
<?php               	
            }
        }
        else 
        {
?>
                    <tr>
                        <td colspan='8'><p class="txt-vermelho">Não foram encontrados funcionários</p></td>
                    </tr>
<?php
        }
?>                
                        </tbody>
                    </table>
                </form>


                </div>
                <div class="col-md-12">
                    <div class="row txt-cinza">
                            <div class="col-md-12 espacamento">
                                <a href="/creditos/funcionario/novo.php" target="_self"><button type="button" class="btn btn-info"><em>Adicionar</em></button></a>
                            </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2 hidden-sm hidden-xs p-top10">
<?php 
            if($banner){
                foreach($banner as $b){
?>
                <div class="row pull-right">
                    <a href="<?php echo $b->link; ?>" class="banner" id="<?php echo $b->id; ?>" target="_blank"><img src="<?php echo $controller->objBanner->urlLink.$b->imagem; ?>" width="186" class="p-3" title="<?php echo $b->titulo; ?>"></a>
                </div>
<?php 
                }
            }
?>
            <div class="row pull-right facebook">
            </div>
        </div>
        
    </div>
</div>
<script>
$(function(){
   $(".detalhe").click(function(){
      $("#sel_id").val($(this).attr("op"));
      $("#main_form").submit();
      
   });
});
</script>
<link href="/js/jqueryui/css/custom-theme/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/jqueryui/js/jquery-ui-1.9.2.custom.min.js"></script>
<?php
require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/footer.php";
?>