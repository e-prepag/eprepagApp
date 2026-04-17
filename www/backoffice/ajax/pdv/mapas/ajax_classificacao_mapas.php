<?php

require_once '../../../../includes/constantes.php';
require_once $raiz_do_projeto."class/util/Log.class.php";
require_once $raiz_do_projeto."class/util/Util.class.php";

if(!Util::isAjaxRequest() || empty($_POST['str']))
{
    die("Chamada não permitida.");
}

require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";

$sqlCM = "SELECT cm_id FROM classificacao_mapas where cm_status = 1";
$ret = SQLexecuteQuery($sqlCM);

try{
    
    if(!$ret)
    {
        throw new Exception("QUERY DE CLASSIFICACOES ATIVAS RETORNOU VAZIA.");
    }
    
    $publishers = array();

    while($cm = pg_fetch_array($ret)) 
    {
        $publishers[] = $cm['cm_id'];
    }

    if(count($publishers) == 0){
        throw new Exception("ARRAY COM CLASSIFICACOES DE MAPA ATIVAS, ESTÁ VAZIO");
    }

    $lojas = explode("#",$_POST['str']); //separando os dados de cada loja em um array

    $arrLojas = array();

    $deleteParams = array_values($publishers);
    $deletePlaceholders = array();
    foreach ($deleteParams as $deleteIndex => $deleteParam) {
        $deletePlaceholders[] = "$" . ($deleteIndex + 1);
    }
    $delete = "delete from classificacao_mapas_pdv where cm_id in (" . implode(",", $deletePlaceholders) . ")";

    $limpa_tabela = SQLexecuteQueryParams($delete, $deleteParams);

    $insert = "insert into classificacao_mapas_pdv (us_id, cm_id) values ($1, $2)";


    foreach($lojas as $loja){
        $tmpLoja = explode("||",$loja); //separando o id da loja dos ids dos publishers

        if(isset($tmpLoja[1]) && trim($tmpLoja[1]) != "")
            $arrLojas[$tmpLoja[0]] = $tmpLoja[1]; //agrupando as lojas em array
    }

    $ret = true;
    foreach($arrLojas as $ind => $cMapas){
        $tmpcMapas = explode("|",$cMapas); 

            foreach($tmpcMapas as $cMapa){
                if(trim($cMapa) != ""){
                    $ret = SQLexecuteQueryParams($insert, array($ind, $cMapa));
                    if(!$ret) break 2;
                }
            }
        }

    if($ret){
        echo true;
    }else{
        throw new Exception("ERRO AO INSERIR CAMPOS");
    }    
    
} catch (Exception $ex) {
    $geraLog = new Log("BO-CLASSIFICACAOMAPAS",array(   "ERROR: ".  $ex->getMessage(),
                                                        "FILE: ".$ex->getFile(),
                                                        "LINE ".$ex->getLine()));
    
    echo false;
}




