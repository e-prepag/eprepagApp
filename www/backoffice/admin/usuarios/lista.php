<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";

$pos_pagina = false; //apenas para nao exibir erro/ resolver depois


/* 
    CONTROLLER
 */
include_once $raiz_do_projeto.'/class/util/Util.class.php';

if(!b_IsBKOUsuarioAdminBKO())
{
    Util::redirect("/");
}

$con = ConnectionPDO::getConnection();
if ( !$con->isConnected() ) {
    // retornar os erros: $con->getErrors();
    die('Erro#2');
}

$pdo = $con->getLink();
$sql = "SELECT * FROM usuarios order by shn_login asc";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

$usuarios = array();

if (count($fetch) > 0) 
{
    for($i=0;$i<=$stmt->rowCount();$i++){
        if(isset($fetch[$i]['id']) && isset($fetch[$i]['shn_login']))
        {
            $usuario = new stdClass();
            $usuario->id = $fetch[$i]['id'];
            $usuario->login = $fetch[$i]['shn_login'];
            $usuario->nome = $fetch[$i]['shn_nome'];
            $usuario->email = $fetch[$i]['shn_mail'];
            $usuario->tipoAcesso = $fetch[$i]['tipo_acesso'];
            
            array_push($usuarios, $usuario);
        }
    }
}else{
    $msg = "Usuarios não encontrados, entre em contato com o suporte.";
    $color = "txt-vermelho";
}
/*
    FIM CONTROLLER
 */

?>
<style>
    .opt{cursor:pointer;}
</style>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><?php echo $sistema->item->getDescricao() ; ?></li>
    </ol>
</div>
<?php 
    if(!isset($msg)) {
    
?>
<div class="col-md-12">
    <a href="/admin/usuarios/cadastro.php" style="float: right;" class="btn btn-info bottom10">Adicionar novo usuário</a>
    <div style="clear: both;" class="alert alert-info" role="alert">Para alterar a senha de algum usuário, clique sobre ele.</div>
</div>
<?php
    }
?>
<div class="col-md-12">
    <table class="text-center table table-bordered table-hover" >
        <thead class="">
            <tr>
                <th>ID</th>
                <th>Login</th>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Tipo de acesso</th>
            </tr>
        </thead>
        <tbody title="Clique para editar">
<?php 
            if(!empty($usuarios)){
                foreach($usuarios as $usuario){
?>
            <tr class="opt" id="<?php echo $usuario->id; ?>">
                <td><?php echo $usuario->id; ?></td>
                <td><?php echo $usuario->login; ?></td>
                <td><?php echo $usuario->nome; ?></td>
                <td><?php echo $usuario->email; ?></td>
                <td><?php echo $usuario->tipoAcesso; ?></td>
            </tr>
<?php
                }
            }else{
?>
            <tr>
                <td colspan="5"><?php echo isset($msg) ? $msg : "Nenhum resultado foi encontrado.";?></td>
            </tr>
<?php
            }
?>
        </tbody>
    </table>
</div>
<form method="post" action="edita.php" id="frmUsuarios">
    <input type="hidden" id="id" name="id" value="">
</form>
<script>
    $(function(){
        $(".opt").click(function(){
            $("#id").val($(this).attr("id"));
            $("#frmUsuarios").submit();
        });
    });
</script>  
<?php 
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</body>
</html>