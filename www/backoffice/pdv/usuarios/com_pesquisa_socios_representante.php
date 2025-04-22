<?php

header("Content-Type: text/html; charset=ISO-8859-1", true);
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/main.php";
require_once $raiz_do_projeto . "includes/pdv/main.php";



$data_inclusao_inicio	= isset($_REQUEST['data_inclusao_inicio'])	? htmlentities($_REQUEST['data_inclusao_inicio'])	: '';
$data_inclusao_fim      = isset($_REQUEST['data_inclusao_fim'])? strtoupper(htmlentities($_REQUEST['data_inclusao_fim'])) : '';
$substatus              = isset($_REQUEST['substatus'])? htmlentities($_REQUEST['substatus']) : null;
$status                 = isset($_REQUEST['status'])? htmlentities($_REQUEST['status']) : null;
$cpf                    = isset($_REQUEST['cpf'])? htmlentities($_REQUEST['cpf']) : null;

//paginacao
if(isset($_POST['p'])){
    $p = $_POST['p'];
}else{
    $p = 1;
}

$registros = 10;
$registros_total = 0;

if(isset($_POST["btn_pesquisar"])){
    $where = " WHERE 1=1";

    if(!empty($data_inclusao_inicio)){
        $where .= " AND ug.ug_data_inclusao >= '" . $data_inclusao_inicio . "'";
    }

    if(!empty($data_inclusao_fim)){
        $where .= " AND ug.ug_data_inclusao <= '" . $data_inclusao_fim . "'";
    }

    if(!empty($substatus)){
        $where .= " AND ug.ug_substatus = " . $substatus;
    }

    if(!empty($status)){
        $where .= " AND ug.ug_status = " . $status;
    }

    if(!empty($cpf)){
        $where .= " AND (ug.ug_repr_legal_cpf = '" . $cpf . "' OR ugs.ugs_cpf = '" . $cpf . "')";
    }

    $sql = "SELECT count(*) as total FROM dist_usuarios_games ug LEFT JOIN dist_usuarios_games_socios ugs ON ug.ug_id = ugs.ug_id " . $where;
    $rs_total = SQLexecuteQuery($sql);
    $row = pg_fetch_assoc($rs_total);
    if($row) $registros_total = $row["total"];

    $sql = "SELECT ug.ug_id as id_pdv,ug.ug_cnpj, ug.ug_repr_legal_nome as nome_repr, ug_repr_legal_cpf as cpf_repr, ugs.ugs_nome as nome_socio, ugs.ugs_cpf as cpf_socio FROM dist_usuarios_games ug LEFT JOIN dist_usuarios_games_socios ugs ON ug.ug_id = ugs.ug_id " . $where;
    $sql .= " ORDER BY ug.ug_id DESC";	
    $sql .= " offset " . intval(($p - 1) * $registros) . " limit " . intval($registros);
    $rs_pdvs = SQLexecuteQuery($sql);
}
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
                <div class="col-md-3 text-left">
                    <div class="form-group">
                        <label for="data_inclusao_inicio">Data inicial: </label>
                        <input class="form-control" name="data_inclusao_inicio" type="date" id="data_inclusao_inicio" value="<?php echo ($data_inclusao_inicio) ? $data_inclusao_inicio : date("Y-m-d");?>"/>
                    </div>
                </div>
                <div class="col-md-3 text-left">
                    <div class="form-group">
                        <label for="data_inclusao_fim">Data final: </label>
                        <input class="form-control" name="data_inclusao_fim" type="date" id="data_inclusao_fim" value="<?php echo ($data_inclusao_fim) ? $data_inclusao_fim : date("Y-m-d");?>"/>
                    </div>
                </div>
                <div class="col-md-2 text-left">
                    <div class="form-group">
                        <label for="">Status: </label>
                        <select class="form-control" name="status">
                            <option value="">Todos</option>
                            <option value="1" <?php if($status == 1) echo "selected"; ?>>Ativo</option>
                            <option value="2" <?php if($status == 2) echo "selected"; ?>>Inativo</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4 text-left">
                    <div class="form-group">
                        <label for="">Tipo: </label>
                        <select class="form-control" name="substatus">
                            <option value="" <?php  if($substatus == "") echo "selected" ?>>Selecione</option>
<?php
                            foreach($SUBSTATUS_LH as $indice=>$dado) {
                                echo "<option value=\"".$indice."\""; if(strcmp($substatus,$indice)==0) echo "selected"; echo " >".$dado." (".$indice.")</option>\n";
                            }
?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 text-left">
                    <div class="form-group">
                        <label for="cpf">CPF: </label>
                        <input class="form-control" name="cpf" type="text" id="data_inclusao_fim" value="<?php echo ($cpf) ? $cpf : "";?>"/>
                    </div>
                </div>
                <div class="col-md-2 text-left">
                    <div class="form-group">
                        <input name="btn_pesquisar" class="btn btn-sm btn-info" style="margin-top: 25px;" type="submit" id="btn_pesquisar" value="Pesquisar" />
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>
<div class="panel panel-default top20">
    <div class="panel-heading" style="height: 40px;">
        <div class="col-md-6">
            <h3  class="panel-title"></h3>
        </div>
        <div class="col-md-6 text-right">
            <h3  class="panel-title">Página <?php echo $p ?> de <?php echo ceil($registros_total/$registros) ?></h3>
        </div>
    </div>
    <div class="panel-body">
<?php
        if(isset($rs_pdvs) && pg_num_rows($rs_pdvs) > 0){
?>
            <table class="table">
                <thead>
                    <tr>
                        <th>CNPJ</th>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>Tipo</th>
                    </tr>
                </thead>
                <tbody>
<?php
                    $id_atual = -1;
                    while($pdv = pg_fetch_assoc($rs_pdvs)){
                        if($id_atual != $pdv["id_pdv"]){
                            $id_atual = $pdv["id_pdv"];
?>
        
                            <tr onmouseover="bgColor='#CFDAD7'" onmouseout="bgColor='#F5F5FB'" bgcolor="#F5F5FB">
                                <td><?php echo $pdv["ug_cnpj"] ?></td>
                                <td><?php echo $pdv["nome_repr"] ?></td>
                                <td><?php echo $pdv["cpf_repr"] ?></td>
                                <td>Representante Legal</td>
                            </tr>

<?php
                            if(!empty($pdv["cpf_socio"])){
                                
?>
                                <tr>
                                    <td><?php echo $pdv["ug_cnpj"] ?></td>
                                    <td><?php echo $pdv["nome_socio"] ?></td>
                                    <td><?php echo $pdv["cpf_socio"] ?></td>
                                    <td>Sócio</td>
                                </tr>
<?php
                            }
                        }else{
                            
?>
                            <tr>
                                <td><?php echo $pdv["id_pdv"] ?></td>
                                <td><?php echo $pdv["nome_socio"] ?></td>
                                <td><?php echo $pdv["cpf_socio"] ?></td>
                                <td>Sócio</td>
                            </tr>
<?php
                        }
                    }
?>
                </tbody>
            </table>
            <div class="row">
                <div class="col-md-6 text-right">
                    
                    <?php if($p > 1){ ?>
                            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                                <input type="hidden" name="p" value="<?php echo $p - 1; ?>"/>
                                <input name="data_inclusao_inicio" type="hidden" value="<?php echo $data_inclusao_inicio;?>"/>
                                <input name="data_inclusao_fim" type="hidden" value="<?php echo $data_inclusao_fim;?>"/>
                                <input name="status" type="hidden" value="<?php echo $status;?>"/>
                                <input name="substatus" type="hidden" value="<?php echo $substatus;?>"/>
                                <input name="cpf" type="hidden" value="<?php echo $cpf;?>"/>
                                <input name="btn_pesquisar" type="hidden" value="Pesquisar" />
                                <input type="submit" name="btAnterior" value=" < " class="btn btn-sm btn-info">
                            </form>
                    <?php } ?>
                </div>
                <div class="col-md-6 text-left">
                    <?php if($p < ($registros_total/$registros)){ ?>
                            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                                <input type="hidden" name="p" value="<?php echo $p + 1; ?>"/>
                                <input name="data_inclusao_inicio" type="hidden" value="<?php echo $data_inclusao_inicio;?>"/>
                                <input name="data_inclusao_fim" type="hidden" value="<?php echo $data_inclusao_fim;?>"/>
                                <input name="status" type="hidden" value="<?php echo $status;?>"/>
                                <input name="substatus" type="hidden" value="<?php echo $substatus;?>"/>
                                <input name="cpf" type="hidden" value="<?php echo $cpf;?>"/>
                                <input name="btn_pesquisar" type="hidden" value="Pesquisar" />
                                <input type="submit" name="btAnterior" value=" > " class="btn btn-sm btn-info">
                            </form>
                    <?php } ?></nobr>
                </div>
            </div>
<?php
        }else{
?>
            Nenhum pdv encontrado
<?php
        }
?>      
    </div>
</div>
<div class="row">
    <div class="col-md-12 text-right">
        <form action="com_pesquisa_socios_representante_download.php" method="POST">
            <input type="hidden" name="p" value="<?php echo $p;?>"/>
            <input name="data_inclusao_inicio" type="hidden" value="<?php echo $data_inclusao_inicio;?>"/>
            <input name="data_inclusao_fim" type="hidden" value="<?php echo $data_inclusao_fim;?>"/>
            <input name="status" type="hidden" value="<?php echo $status;?>"/>
            <input name="substatus" type="hidden" value="<?php echo $substatus;?>"/>
            <input name="cpf" type="hidden" value="<?php echo $cpf;?>"/>
            <input name="btn_pesquisar" type="hidden" value="Pesquisar" />
            <input name="btnDownload" type="hidden" value="Download" />
            <input type="submit" name="btAnterior" value="Download" class="btn btn-sm btn-info">
        </form>
    </div>
</div>

<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</div>