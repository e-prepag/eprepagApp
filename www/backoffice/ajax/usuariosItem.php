<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php"; 
require_once $raiz_do_projeto."class/util/Util.class.php";

$_POST['nivel_id'] = 1;
if(Util::isAjaxRequest() && isset($_POST['reqType'])){

    $con = ConnectionPDO::getConnection();
    if ( !$con->isConnected() ) {
        // retornar os erros: $con->getErrors();
        die('Erro#2');
    }

    $pdo = $con->getLink();

    if($_POST['reqType'] == "vincula"){
        if(isset($_POST['item'])){
        
            $sql = "delete from nivel_acesso_item_grupo where item_id = ?";

            $stmt = $pdo->prepare($sql);
            $stmt->execute(
                            array(
                                $_POST['item']
                            )
            );

            $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $erro = 0;

            if(isset($_POST['usuariosGrupo']) && !empty($_POST['usuariosGrupo'])){

                foreach($_POST['usuariosGrupo'] as $usuario){

                    $sql = "insert into nivel_acesso_item_grupo (grupos_id, item_id, nivel_id) values (?, ?, ?)";
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(
                                    array(
                                        $usuario,
                                        $_POST['item'],
                                        $_POST['nivel_id']
                                    )
                    );

                    if($stmt->rowCount() <= 0){
                        $erro++;
                    }  

                }
            }

            if($erro == 0){
                print RETURN_SUCCESS;
            }else{
                print RETURN_WRONG;
            }  

        }else{
            print RETURN_EMPTY;
        }
    }
    else if($_POST['reqType'] == "montaHtml" && isset($_POST['item'])){
        
        $sqlGrupos = "SELECT 
                            grupos_id,grupos_descricao 
                        FROM 
                            grupos_usuarios 
                        WHERE
                            grupos_id NOT IN (
                                                SELECT 
                                                    grupos_id
                                                FROM 
                                                    nivel_acesso_item_grupo 
                                                where 
                                                    item_id = ".$_POST['item']."
                                            )";
        $stmt = $pdo->prepare($sqlGrupos);
        $stmt->execute();
        $resultGrupos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sqlGruposItens = "SELECT 
                                grupos_id,grupos_descricao 
                            FROM 
                                grupos_usuarios 
                            WHERE
                                grupos_id IN (
                                                    SELECT 
                                                        grupos_id 
                                                    FROM 
                                                        nivel_acesso_item_grupo 
                                                    where 
                                                        item_id = ".$_POST['item']."
                                                )";
        
        $stmt = $pdo->prepare($sqlGruposItens);
        $stmt->execute();
        $resultGruposItem = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if(count($resultGrupos) > 0 || count($resultGruposItem) > 0){
        
            $html = '<div id="modal-grupo-usuario" class="modal fade" role="dialog">
                        <div class="modal-dialog modal-lg">
                            <!-- Modal content-->
                            <div class="modal-content">
                                <div class="modal-header">
                                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                                  <h4 class="modal-title">Alteração de login</h4>
                                </div>
                                <div class="modal-bodyespacamento">
                                    <form role="form" id="formLogin" name="formLogin" method="POST">
                                        <div class="espacamento  bg-cinza-claro ">
                                            <div class="col-md-6 borda-fina">
                                                <h5>Grupos</h5>
                                                <ul id="sort1" class="list-group sortable espacamento bg-branco">';

                                                for($i = 0; $i < count($resultGrupos); $i++)
                                                {
                                                    $html .= '<li id="'.$resultGrupos[$i]["grupos_id"].'" class="list-group-item">'.$resultGrupos[$i]['grupos_descricao'].'</li>';
                                                }

            $html .=                            '</ul>
                                            </div>
                                            <div class="col-md-6 borda-fina">
                                                <h5 id="tituloGrupo">Grupo</h5>
                                                <ul id="sort2" class="list-group sortable espacamento bg-branco">';

                                                for($i = 0; $i < count($resultGruposItem); $i++)
                                                {
                                                    $html .= '<li id="'.$resultGruposItem[$i]["grupos_id"].'" class="list-group-item">'.$resultGruposItem[$i]['grupos_descricao'].'</li>';
                                                }
            $html .=
                                                '</ul>
                                            </div>
                                            <div class="clearfix"></div>
                                        </div>
                                    </form>
                                    <div class="col-md-12 bg-cinza-claro">
                                        <div class="alert alert-danger" id="erro" role="alert">
                                            <span class="glyphicon t0 glyphicon-exclamation-sign" aria-hidden="true"></span>
                                            <span class="sr-only">Error:</span>
                                            Erro: favor entrar em contato com o suporte.
                                        </div>
                                        <div class="alert alert-success" id="sucesso" role="alert">
                                            <span class="glyphicon t0 glyphicon-ok" aria-hidden="true"></span>
                                            <span class="sr-only">Error:</span>
                                            Usuários vinculados com sucesso.
                                        </div>
                                        <a href="#" class="btn btn-success bottom10" id="alterar">Alterar</a>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                  <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
                                </div>
                            </div>
                        </div>
                    </div>';

            $html .= '<script>
                $(function() {
                
                    $("#tituloGrupo").text($("#nomeConst").val());

                    $("#sort1").sortable({
                        connectWith: ".sortable",
                        appendTo: "body"
                    }).disableSelection();

                    $("#sort2").sortable({
                        connectWith: ".sortable",
                        appendTo: "body"
                    }).disableSelection();

                    $("#sucesso").fadeOut();
                    $("#erro").fadeOut();

                    $("#modal-grupo-usuario").on("hidden.bs.modal", function () { $("#erro").fadeOut(); $("#sucesso").fadeOut(); });

                    $("#alterar").click(function(){

                        $("#erro").fadeOut(); $("#sucesso").fadeOut();
                        var usuarios = [];

                        $("#sort2").children().each(function(){
                            usuarios.push($(this).attr("id"));
                        });

                        $.ajax({
                            type: "POST",
                            data: {usuariosGrupo: usuarios, item: $("#id").val(), reqType: "vincula"},
                            url: "/ajax/usuariosItem.php",
                            success: function(ret){
                                if(ret == 1){

                                    $("#sucesso").fadeIn();
                                }else if(ret == 3 || ret == 2){

                                    $("#erro").fadeIn();
                                }
                            }
                         });
                    });

                    $("#modal-grupo-usuario").modal("show"); 
                });
                </script>';

            print $html;
        
        }else{
            
            print "<span class='txt-vermelho'>Grupos não encontrados. Favor entrar em contato com o suporte.</span>";
        }
    }
    

}else{
    print "Chamada não permitida.";
}
?>