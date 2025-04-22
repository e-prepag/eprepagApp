<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php"; 
require_once $raiz_do_projeto."class/util/Util.class.php";


if(Util::isAjaxRequest() && isset($_POST['reqType'])){

    $con = ConnectionPDO::getConnection();
    if ( !$con->isConnected() ) {
        // retornar os erros: $con->getErrors();
        die('Erro#2');
    }

    $pdo = $con->getLink();

    if($_POST['reqType'] == "vincula"){
        if(isset($_POST['grupo'])){
        
            $sql = "delete from grupos_acesso_usuarios where grupos_id = ?";

            $stmt = $pdo->prepare($sql);
            $stmt->execute(array($_POST['grupo']));

            $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $erro = 0;

            if(isset($_POST['usuariosGrupo']) && !empty($_POST['usuariosGrupo'])){

                foreach($_POST['usuariosGrupo'] as $usuario){

                    $sql = "insert into grupos_acesso_usuarios (grupos_id, id) values (?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(array($_POST['grupo'], $usuario));

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
    }else if($_POST['reqType'] == "montaHtml" && isset($_POST['grupo'])){
        
        $sqlUsuarios = "SELECT id,shn_login, shn_nome FROM usuarios where id NOT IN (SELECT id FROM grupos_acesso_usuarios where grupos_id = ".$_POST['grupo'].") order by shn_nome ASC";
        $stmt = $pdo->prepare($sqlUsuarios);
        $stmt->execute();
        $resultUsuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sqlUsuariosGrupos = "select g.id,shn_login ,u.shn_nome from grupos_acesso_usuarios g inner join usuarios u on g.id = u.id  and g.grupos_id = ".$_POST['grupo']."  order by u.shn_nome ASC";
        $stmt = $pdo->prepare($sqlUsuariosGrupos);
        $stmt->execute();
        $resultUsuariosGrupos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if(count($resultUsuarios) > 0 || count($resultUsuariosGrupos) > 0){
        
            $html = '<div id="modal-grupo-usuario" class="modal fade" role="dialog">
                        <div class="modal-dialog  modal-lg">
                            <!-- Modal content-->
                            <div class="modal-content">
                                <div class="modal-header">
                                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                                  <h4 class="modal-title">Vincular Usuário à Grupos</h4>
                                </div>
                                <div class="modal-bodyespacamento">
                                    <form role="form" id="formLogin" name="formLogin" method="POST">
                                        <div class="espacamento  bg-cinza-claro ">
                                            <div class="col-md-6 borda-fina">
                                                <h5>Usuários</h5>
                                                <ul id="sort1" class="list-group sortable espacamento bg-branco">';

                                                for($i = 0; $i < count($resultUsuarios); $i++)
                                                {
                                                    $html .= '<li id="'.$resultUsuarios[$i]["id"].'" class="list-group-item">'.$resultUsuarios[$i]['shn_nome'].' ('.$resultUsuarios[$i]['shn_login'].')</li>';
                                                }

            $html .=                            '</ul>
                                            </div>
                                            <div class="col-md-6 borda-fina">
                                                <h5 id="tituloGrupo">Grupo</h5>
                                                <ul id="sort2" class="list-group sortable espacamento bg-branco">';

                                                for($i = 0; $i < count($resultUsuariosGrupos); $i++)
                                                {
                                                    $html .= '<li id="'.$resultUsuariosGrupos[$i]["id"].'" class="list-group-item">'.$resultUsuariosGrupos[$i]['shn_nome'].' ('.$resultUsuariosGrupos[$i]['shn_login'].')</li>';
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
                
                    $("#tituloGrupo").text($("#nome").val());

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
                            data: {usuariosGrupo: usuarios, grupo: $("#id").val(), reqType: "vincula"},
                            url: "/ajax/usuariosGrupo.php",
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
            
            print "<span class='txt-vermelho'>Usuários não encontrados. Favor entrar em contato com o suporte.</span>";
        }
    }
    

}else{
    print "Chamada não permitida.";
}
?>