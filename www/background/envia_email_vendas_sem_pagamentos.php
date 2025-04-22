<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

set_time_limit(6000);
ini_set('max_execution_time', 6000); 

require_once "../includes/main.php";
require_once $raiz_do_projeto . "class/classManipulacaoArquivosLog.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";

$arquivoLog = new ManipulacaoArquivosLog($argv);
if(!$arquivoLog->haveFile()) {
    
    $arquivoLog->createLockedFile();
    $nome_arquivo = $arquivoLog->getNomeArquivo();
    ob_start('callbackLog');
    
    //Processa Email de Aviso	
    echo str_repeat("=", 80).PHP_EOL."Processando Envio de Email de Aviso ".date("Y-m-d H:i:s").PHP_EOL.str_repeat("=", 80).PHP_EOL;

    //Definindo a data a ser considerada no levantamento
    $currentmonth  = mktime(0, 0, 0, date("m"), date("d")-1, date("Y"));
    $data_pesquisa = date("Y-m-d",$currentmonth);

    $sql  = "select vg.vg_ug_id, ug.ug_email, ug.ug_nome, vi.n_vendas_incompletas, vi.v_data_min, vi.v_data_max, coalesce(pag.n_pags_completos, 0) as n_pags_completos
            from tb_venda_games vg 
                    inner join usuarios_games ug on ug.ug_id = vg.vg_ug_id
                    inner join 
                            (select vg1.vg_ug_id, count(*) as n_vendas_incompletas, min(vg1.vg_data_inclusao) as v_data_min, max(vg1.vg_data_inclusao) as v_data_max  
                            from tb_venda_games vg1 
                            where (vg1.vg_data_inclusao between '".$data_pesquisa." 00:00:00' and '".$data_pesquisa." 23:59:59' ) 
                                    and not (vg1.vg_ultimo_status=5)
                                    and vg1.vg_pagto_tipo>3 
                            group by vg1.vg_ug_id
                            ) vi on vi.vg_ug_id = vg.vg_ug_id
                    left outer join 
                            (select pg.idcliente, count(*) as n_pags_completos
                            from tb_pag_compras pg
                            where  pg.status=3
                            group by pg.idcliente
                            ) pag on pag.idcliente = vg.vg_ug_id
            where (vg_data_inclusao between '$data_pesquisa 00:00:00' and '$data_pesquisa 23:59:59' )
                    and not (vg_ultimo_status=5)
                    and vg_pagto_tipo>3
                    and coalesce(pag.n_pags_completos, 0) = 0
            group by vg.vg_ug_id, ug.ug_email, ug.ug_nome, vi.n_vendas_incompletas, vi.v_data_min, vi.v_data_max, pag.n_pags_completos
            order by ug_email";

    echo $sql.PHP_EOL;
    $rs_venda_identifica = SQLexecuteQuery($sql);

    echo "total: ".pg_num_rows($rs_venda_identifica).PHP_EOL;


    while ($rs_venda_identifica_row = pg_fetch_array($rs_venda_identifica)){

            $ug_id			= $rs_venda_identifica_row['vg_ug_id'];
            $ug_email			= $rs_venda_identifica_row['ug_email'];
            $ug_nome			= $rs_venda_identifica_row['ug_nome'];
            $n_vendas_incompletas	= $rs_venda_identifica_row['n_vendas_incompletas'];
            $v_data_min			= $rs_venda_identifica_row['v_data_min'];
            $v_data_max			= $rs_venda_identifica_row['v_data_max'];
            $n_pags_completos		= $rs_venda_identifica_row['n_pags_completos'];


            echo "$ug_id: [$n_vendas_incompletas] '$ug_email' , '$ug_nome'".PHP_EOL;

            if($ug_email) {

                    $objEnvioEmailAutomatico = new EnvioEmailAutomatico('G','ComprasNaoConcluidas');
                    $objEnvioEmailAutomatico->setUgID($ug_id);
                    $objEnvioEmailAutomatico->MontaEmailEspecifico();

                    // Log
                    echo $ug_email." [ID: $ug_id] (n_pags_completos: ".$n_pags_completos.",nvendas_incompletas: ".$n_vendas_incompletas.", data_min: '". $v_data_min."', data_max: '". $v_data_max."')".PHP_EOL;

            } else {
                    echo "  => Email vazio (ug_id: $ug_id), não enviado".PHP_EOL;
            }

    } //end while

    echo str_repeat("=", 80).PHP_EOL." Fim - ".date('Y-m-d H:i:s').PHP_EOL.str_repeat("=", 80).PHP_EOL;
        
    $arquivoLog->deleteLockedFile();
}
else {
    $arquivoLog->showBusy();
}

//Fechando Conexão
pg_close($connid);

?>
