<?php
$_POST['navAba'] = "0";
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
/* 
    CONTROLLER
 */
    
    if(isset($_POST)){
        if(empty($_POST['opr_codigo']))
            $_POST['opr_codigo'] = NULL;
        
        if(isset($_POST['novaPosicao'])){
            
            $select = "select * from classificacao_mapas where cm_nome = $1";
            if($pegaRegDuplicado = SQLexecuteQueryParams($select, array($_POST['cm_nome']))){
                if((($pegaRegDuplicado) ? pg_num_rows($pegaRegDuplicado) : 0) == 0){
                    $sql = "insert into classificacao_mapas (cm_nome, cm_status, cm_data_cadastro, opr_codigo) values($1, $2, current_date, $3)";
                                        if($ret = SQLexecuteQueryParams($sql, array($_POST['cm_nome'], $_POST['cm_status'], $_POST['opr_codigo']))){
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
                            set cm_nome = $1, cm_status = $2, opr_codigo = $3
                            where cm_id = $4";
                
                if($teste = SQLexecuteQueryParams($update, array($_POST["cm_nome"], $_POST["cm_status"], $_POST['opr_codigo'], $_POST["cmid"]))){
                    echo "<script>alert('Registro editado com sucesso.');</script>";    
                }else{
                    echo "<script>alert('Erro ao editar registro.');</script>";    
                }
            }
            else
                echo "<script>alert('Problema ao obter classificação.'); location.href = '/pdv/classificacao_mapas.php';</script>";
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