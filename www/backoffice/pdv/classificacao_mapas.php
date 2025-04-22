<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
    
if(isset($_POST)){
    if(empty($_POST['opr_codigo']))
        $_POST['opr_codigo'] = "null";

    if(isset($_POST['novaPosicao'])){

        $select = "select * from classificacao_mapas where cm_nome = '".$_POST['cm_nome']."'";
        if($pegaRegDuplicado = SQLexecuteQuery($select)){
            if(pg_num_rows($pegaRegDuplicado) == 0){
                $insert = "insert into classificacao_mapas (cm_nome, cm_status, cm_data_cadastro, opr_codigo) values('%s', '%s', current_date, %s)"; //cm_id, cm_nome, cm_status, cm_data_cadastro, opr_codigo"
                $sql = vsprintf($insert, array($_POST['cm_nome'],$_POST['cm_status'], $_POST['opr_codigo'])).";";

                if($ret = SQLexecuteQuery($sql)){
                    echo "<script>alert('Registro inserido com sucesso!');</script>";
                    unset($_POST);
                }else{
                    echo "<script>alert('Erro ao inserir registro!');</script>";
                }
            }else{
                echo "<script>alert('Já existe um publisher cadastrado com esse nome!');</script>";
            }
        }
    }elseif(isset($_POST['editaPosicao'])){
        if(isset($_POST["cmid"]))
        {
            $update = "update classificacao_mapas 
                        set cm_nome = '".$_POST["cm_nome"]."', cm_status = '".$_POST["cm_status"]."', opr_codigo = ".$_POST['opr_codigo']."
                        where cm_id = ".$_POST["cmid"];

            if($teste = SQLexecuteQuery($update)){
                echo "<script>alert('Registro editado com sucesso.');</script>";    
            }else{
                echo "<script>alert('Erro ao editar registro.');</script>";    
            }
        }
        else
            echo "<script>alert('Problema ao obter classificação.'); location.href = '/dist_commerce/classificacao_mapas.php';</script>";
    }
}

/*
    FIM CONTROLLER
 */

include_once (isset($_GET["acao"]) && ($_GET["acao"] == "novo" || $_GET["acao"] == "edita")) ? 'classificacao_mapas_novo_edita.php' : 'classificacao_mapas_lista.php';

require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</body>
</html>