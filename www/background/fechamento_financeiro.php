<?php
header("Content-Type: text/html; charset=ISO-8859-1",true);
// Pré-Processamento para fechamento financeiro
// financial_processing.php 
// - Processa totais por publisher, dia e canal

error_reporting(E_ALL); 
ini_set("display_errors", 1); 
function logProcessamento($mensagem) {
    $data = date("Y-m-d H:i:s");
    $pastaLogs = "logs"; // Pasta onde os logs serão armazenados
    $arquivo = "$pastaLogs/log_" . date("Y-m-d") . ".txt";

    // Cria a pasta 'logs' se não existir
    if (!is_dir($pastaLogs)) {
        mkdir($pastaLogs, 0777, true);
    }

    // Formata a mensagem com data e quebra de linha
    $mensagemFormatada = "[$data] $mensagem" . PHP_EOL;

    // Escreve no arquivo (cria se não existir)
    file_put_contents($arquivo, $mensagemFormatada, FILE_APPEND);
}

$raiz_do_projeto = "/www/";
require_once "/www/includes/main.php";
require_once $raiz_do_projeto . "includes/gamer/main.php"; 

//Esse ID é concatenado no inicio de cada id da operação('id_venda' => 'id_op') para diferenciar o tipo de venda que foi feito
$ARRAY_CONCATENA_ID_VENDA = array
                                    (
                                        'gamer'          => '10',
                                        'pdv'            => '20',
                                        'cards'          => '30',
                                        'boleto_express' => '40'
                                    );

$time_start_stats = getmicrotime();

//Buscando Publisher que possuem totalização por utilização
//$vetorPublisherPorUtilizacao = levantamentoPublisherComFechamentoUtilizacao();
// { [13]=> string(22) "2015-08-16 00:00:00-03" [124]=> string(22) "2018-08-28 00:00:00-03" [137]=> string(22) "2018-10-16 00:00:00-03" [143]=> string(22) "2020-01-07 00:00:00-03" [147]=> string(22) "2019-11-30 00:00:00-03" [148]=> string(22) "2020-07-31 00:00:00-03" }

$oar[0]['124'] = "2018-08-28 00:00:00-03";
// $oar[1]['137'] = "2018-10-16 00:00:00-03";
// $oar[2]['143'] = "2020-01-07 00:00:00-03";
// $oar[3]['147'] = "2019-11-30 00:00:00-03";
// $oar[4]['148'] = "2020-07-31 00:00:00-03";
// $oar[5]['13'] = "2015-08-16 00:00:00-03";

$escolhe = $_GET['i'];
$vetorPublisherPorUtilizacao = $oar[$escolhe]; // 
$opr_id = 0;
// $vetorPublisherPorUtilizacao ;
if(count($vetorPublisherPorUtilizacao)>0) {
    $where_opr_venda_lan = " AND ( CASE ";
    $where_opr_venda_lan_negativa = " AND ( CASE ";
    $where_opr_utilizacao_lan = " AND ( CASE ";
    foreach ($vetorPublisherPorUtilizacao as $opr_codigo => $opr_data_inicio_contabilizacao_utilizacao){ 
        //echo "ID: ".$opr_codigo." => DATA: [".substr($opr_data_inicio_contabilizacao_utilizacao,0,19)."]<br>";
		$opr_id = $opr_codigo;
        $where_opr_venda_lan .= " WHEN vgm.vgm_opr_codigo = $opr_codigo THEN vg.vg_data_inclusao < '".substr($opr_data_inicio_contabilizacao_utilizacao,0,19)."' ";
        $where_opr_venda_lan_negativa .= " WHEN vgm.vgm_opr_codigo = $opr_codigo THEN vg.vg_data_inclusao >= '".substr($opr_data_inicio_contabilizacao_utilizacao,0,19)."' ";
        $where_opr_utilizacao_lan .= "  WHEN pih_id = $opr_codigo THEN pih_data >= '".substr($opr_data_inicio_contabilizacao_utilizacao,0,19)."' ";
    }//end foreach
    $where_opr_venda_lan .= " ELSE vg.vg_data_inclusao > '2008-01-01 00:00:00' END )";
    $where_opr_venda_lan_negativa .= " ELSE FALSE END )";
    $where_opr_utilizacao_lan .= "  ELSE FALSE END ) ";
} //end if(count($vetorPublisherPorUtilizacao)>0)
else {
    $where_opr_venda_lan = "";
    $where_opr_venda_lan_negativa = "";
    $where_opr_utilizacao_lan = "";
}//end else do if(count($vetorPublisherPorUtilizacao)>0)

$msg = "";

$verificaOpr = (count($vetorPublisherPorUtilizacao)>0)?" and vgm_opr_codigo = $opr_id":" and vgm_opr_codigo not in(124,137,143,147,148,13)";

echo PHP_EOL.str_repeat("=", 80).PHP_EOL."Pre-Processamento para fechamento financeiro (".date("Y-m-d H:i:s").")".PHP_EOL.PHP_EOL;
logProcessamento("Pre-Processamento para fechamento financeiro (".date("Y-m-d H:i:s").")");
$garena = "SELECT 
    pin_datavenda AS dia,
    canal,
    operadora AS publisher,
    COUNT(DISTINCT case_codigo) AS total_order,
    COUNT(pin_codinterno) AS n, 
    SUM(pin_valor) AS total
FROM (
    (SELECT 
        to_char(pih_data, 'YYYY-MM-DD')::timestamp AS pin_datavenda,
        'L' AS canal,
        t0.pin_codinterno, 
        t0.pin_valor, 
        vg.vg_id::TEXT AS case_codigo, 
        t0.opr_codigo AS operadora
     FROM pins t0 
     INNER JOIN operadoras t1 ON (t0.opr_codigo = t1.opr_codigo) 
     INNER JOIN pins_status t3 ON (t0.pin_status = t3.stat_codigo) 
     INNER JOIN pins_integracao_historico pih ON pih_pin_id = pin_codinterno 
     INNER JOIN tb_dist_venda_games_modelo_pins vgmp ON vgmp_pin_codinterno = pih_pin_id 
     INNER JOIN tb_dist_venda_games_modelo vgm ON vgm.vgm_id = vgmp_vgm_id 
     INNER JOIN tb_dist_venda_games vg ON vg.vg_id = vgm_vg_id 
     WHERE t0.pin_status = '8' 
     AND pih_codretepp = '2' 
     AND vg_ultimo_status = '5' 
     AND (CASE WHEN t1.opr_contabiliza_utilizacao <> 0 THEN vg_data_inclusao >= t1.opr_data_inicio_contabilizacao_utilizacao ELSE TRUE END) 
     AND (pih_data BETWEEN '2025-02-01 00:00:00' AND '2025-02-12 23:59:59')
    -- AND (pih_data BETWEEN CURRENT_DATE - INTERVAL '1 day' AND CURRENT_DATE - INTERVAL '1 second')

     AND t0.opr_codigo in (124,166))

    UNION ALL 
   
    (SELECT 
        to_char(vg_data_concilia, 'YYYY-MM-DD')::timestamp AS pin_datavenda,
        CASE 
            WHEN vg.vg_ug_id = 7909 THEN 'E' 
            ELSE 'M' 
        END AS canal,
        t0.pin_codinterno, 
        t0.pin_valor, 
        vg.vg_id::TEXT AS case_codigo, 
        t0.opr_codigo AS operadora
     FROM pins t0 
     INNER JOIN operadoras t1 ON (t0.opr_codigo = t1.opr_codigo) 
     INNER JOIN pins_status t3 ON (t0.pin_status = t3.stat_codigo) 
     INNER JOIN tb_venda_games_modelo_pins vgmp ON vgmp_pin_codinterno = pin_codinterno 
     INNER JOIN tb_venda_games_modelo vgm ON vgm.vgm_id = vgmp_vgm_id 
     INNER JOIN tb_venda_games vg ON vg.vg_id = vgm_vg_id 
     WHERE vg_ultimo_status = '5' 
     AND (CASE WHEN t1.opr_contabiliza_utilizacao <> 0 THEN vg_data_inclusao >= t1.opr_data_inicio_contabilizacao_utilizacao ELSE TRUE END) 
     AND (vg_data_concilia BETWEEN '2025-02-01 00:00:00' AND '2025-02-12 23:59:59'))
     --AND (vg_data_concilia BETWEEN CURRENT_DATE - INTERVAL '1 day' AND CURRENT_DATE - INTERVAL '1 second'))
    UNION ALL 
    
    (SELECT 
        to_char(pih_data, 'YYYY-MM-DD')::timestamp AS pin_datavenda, 
        'C' AS canal,
        pih_pin_id AS pin_codinterno, 
        pin_valor, 
        pih_pin_id::TEXT AS case_codigo, 
        o.opr_codigo AS operadora
     FROM pins_integracao_card_historico pich 
     INNER JOIN pins_card pc ON pin_codinterno = pih_pin_id 
     INNER JOIN operadoras o ON pc.opr_codigo = o.opr_codigo 
     WHERE pih_codretepp = '2' 
     AND pich.pin_status = 4 
     AND CASE WHEN pih_id = 90 THEN pin_lote_codigo > 6 ELSE pin_lote_codigo > 0 END 
     --AND (pih_data BETWEEN '2025-02-01 00:00:00' AND '2025-02-15 23:59:59'))
     AND (pih_data BETWEEN CURRENT_DATE - INTERVAL '1 day' AND CURRENT_DATE - INTERVAL '1 second'))
    UNION ALL 
    
    (SELECT 
        to_char(pgc_pin_response_date, 'YYYY-MM-DD')::timestamp AS pin_datavenda, 
        'C' AS canal,
        pgc_id AS pin_codinterno,
        CASE 
            WHEN opr_product_type = 5 THEN pgc_real_amount 
            WHEN opr_product_type IN (4, 7) THEN pgc_face_amount 
            ELSE pgc_face_amount 
        END AS pin_valor, 
        pgc_id::TEXT AS case_codigo, 
        pgc_opr_codigo AS operadora
     FROM pins_gocash 
     INNER JOIN operadoras ON opr_codigo = pgc_opr_codigo 
     WHERE pgc_opr_codigo != 0 
     AND (pgc_pin_response_date BETWEEN '2025-02-01 00:00:00' AND '2025-02-12 23:59:59'))
    --  AND (pgc_pin_response_date BETWEEN CURRENT_DATE - INTERVAL '1 day' AND CURRENT_DATE - INTERVAL '1 second'))
) AS selection
GROUP BY pin_datavenda, operadora, canal
ORDER BY pin_datavenda;";
//echo "SQL Executado: " . $sql . "<br>";
$rs = SQLexecuteQuery($garena);
$n_updates = pg_num_rows($rs);
//echo "Encontrado".(($n_updates>1)?"s":"")." : ".$n_updates." Registro".(($n_updates>1)?"s":"")." para serem verifidos e atualizados".PHP_EOL.PHP_EOL;

if(!$rs || pg_num_rows($rs) == 0) {
        $msg = "Nenhum usuários selecionado
";
} else {
	while($rs_row = pg_fetch_array($rs)) {
            $sql = "
                select * 
                from financial_processing 
                where fp_channel = '".$rs_row['canal']."'
                    and fp_publisher = ".$rs_row['publisher']."
                    and fp_date = '".$rs_row['dia']."' ;";
            echo(" ========= <br> ".$rs_row['dia']);
            $rs_existe = SQLexecuteQuery($sql);
            logProcessamento("Dia: ".$rs_row['dia']);
            logProcessamento($sql);
            $n_existente  = pg_num_rows($rs_existe);
            echo(" ========= <br>".$n_existente);
            //Verificando se existe o registrologProcessamento
            logProcessamento("qtd registros: ". $n_existente);
            if($n_existente == 1) {
					
                $sql = "
                    update financial_processing 
                    set fp_number = ".$rs_row['n'].",
                        fp_total = ".$rs_row['total'].",
                        fp_total_order = ".$rs_row['total_order']."
                    where fp_channel = '".$rs_row['canal']."'
                        and fp_publisher = ".$rs_row['publisher']."
                        and fp_date::DATE = '".$rs_row['dia']."' 
                        and fp_freeze = 0;";
               // echo " ========= <br>SQL do Update: <br>".$sql.PHP_EOL;
                $ret = SQLexecuteQuery($sql);
                logProcessamento("Update: ".$sql);
                if(!$ret) echo "";
            }//end if($n_existente > 0) 
            //Verificando se existe erro nos dados
            elseif($n_existente > 1) {
                echo "ERRO: EXISTEM MAIS DE UM REGISTRO PARA O PERIODO, CANAL E PUBLISHER **********************************************************************************".PHP_EOL;
            }//end elseif($n_existente > 1)
            //Inserindo por não existir o registro
            else {
                $sql = "
                   INSERT INTO financial_processing (fp_channel, fp_publisher, fp_date, fp_number, fp_total, fp_total_order)
VALUES ('".$rs_row['canal']."', ".$rs_row['publisher'].", '".$rs_row['dia']."'::DATE, ".$rs_row['n'].", ".$rs_row['total'].", ".$rs_row['total_order'].");
                    ";
                echo " ========= <br> SQL do Insert: <br> ".$sql.PHP_EOL;
                $ret = SQLexecuteQuery($sql);
                logProcessamento("insert: ".$sql);
                if(!$ret) echo "";
            }//end else do elseif($n_existente > 1)
	}//end while
}//end else do if(!$rs || pg_num_rows($rs) == 0)

/*********************************************************************** 
 * *****   Marcando os períodos a quais empresas pertencem
 * *****   E se houve movimentação de nacional para internacional
 * *****   INICIO
 ***********************************************************************/
$sql = "SELECT opr_nome, opr_codigo, opr_vinculo_empresa, substring(opr_data_inicio_operacoes::varchar from 1 for 19) as data_inicio, opr_internacional_alicota FROM operadoras;";
echo " ========= <br> scrpt select movimentação: ";
echo " ========= <br>".$sql.PHP_EOL.PHP_EOL;
$rs_publishers = SQLexecuteQuery($sql);
$n_publishers = pg_num_rows($rs_publishers);
//echo PHP_EOL.$sql.PHP_EOL.PHP_EOL."Número total de Publisher Selecionados ".$n_publishers.PHP_EOL.PHP_EOL;



//Verificando se retornou ao menos 1 Publisher
if($n_publishers > 0) {
    while($rs_publishers_row = pg_fetch_array($rs_publishers)) {

        //Verificando se Publisher está vinculado à EPP Pagto
        if($rs_publishers_row['opr_vinculo_empresa'] == 0) {
            //echo "Publisher ".$rs_publishers_row['opr_nome']." está vinculado à E-Prepag Pagamentos".PHP_EOL;
            $sql = "UPDATE financial_processing SET fp_company = ".$rs_publishers_row['opr_vinculo_empresa']." WHERE fp_publisher = ".$rs_publishers_row['opr_codigo'].";";
            echo " ========= <br> UPDATE financial_processing: ";
            echo " ========= <br>".$sql.PHP_EOL;
            $rs_update = SQLexecuteQuery($sql);
            if(!$rs_update) echo "";
            //Publisher Internacional
            if($rs_publishers_row['opr_internacional_alicota'] > 0) {
                $sql = "UPDATE financial_processing SET fp_nationality = 1 WHERE fp_publisher = ".$rs_publishers_row['opr_codigo'].";";
                echo " ========= <br> Todos tempos Internacional: ".PHP_EOL.$sql.PHP_EOL;
                $rs_updateTrocaNacionalInternacional = SQLexecuteQuery($sql);
                if(!$rs_updateTrocaNacionalInternacional) echo "Erro ao Atualizar ao Marcar a Nacionalidade do Publisher [".$rs_publishers_row['opr_codigo']."] para Todos os Tempos".PHP_EOL;
            }//end if($rs_publishers_row['opr_internacional_alicota'] > 0)
            //Publisher Nacional
            else {
                $sql = "UPDATE financial_processing SET fp_nationality = 0 WHERE fp_publisher = ".$rs_publishers_row['opr_codigo'].";";
                echo " ========= <br> Todos tempos Nacional: ".PHP_EOL.$sql.PHP_EOL;
                $rs_updateTrocaNacionalInternacional = SQLexecuteQuery($sql);
                if(!$rs_updateTrocaNacionalInternacional) echo "Erro ao Atualizar ao Marcar a Nacionalidade do Publisher [".$rs_publishers_row['opr_codigo']."] para Todos os Tempos".PHP_EOL;
            }//end else do if($rs_publishers_row['opr_internacional_alicota'] > 0)
        }//end if($rs_publishers_row['opr_vinculo_empresa'] == 0)
        
        //Para o Publisher que está vinculado à EPP ADM
        else {
            //echo "Publisher ".$rs_publishers_row['opr_nome']." está vinculado à E-Prepag Administradora".(!empty($rs_publishers_row['data_inicio'])?" desde [".$rs_publishers_row['data_inicio']."]":"").PHP_EOL;
            if(!empty($rs_publishers_row['data_inicio'])) {
                $sql = "UPDATE financial_processing SET fp_company = ".$rs_publishers_row['opr_vinculo_empresa']." WHERE fp_publisher = ".$rs_publishers_row['opr_codigo']." AND fp_date >='".$rs_publishers_row['data_inicio']."';";
                echo " ========= <br>rs_publishers_row: ";
                echo " ========= <br>".$sql.PHP_EOL;
                $rs_update = SQLexecuteQuery($sql);
                if(!$rs_update) echo "";
                $sql = "UPDATE financial_processing SET fp_company = 0 WHERE fp_publisher = ".$rs_publishers_row['opr_codigo']." AND fp_date <'".$rs_publishers_row['data_inicio']."';";
                echo " ========= <br> rs_publishers_row rs_update: ";
                echo " ========= <br>".$sql.PHP_EOL;
                $rs_update = SQLexecuteQuery($sql);
                if(!$rs_update) echo "";
            }//end if(!empty($rs_publishers_row['data_inicio']))
            else {
                $sql = "UPDATE financial_processing SET fp_company = ".$rs_publishers_row['opr_vinculo_empresa']." WHERE fp_publisher = ".$rs_publishers_row['opr_codigo'].";";
                echo " ========= <br>sem publisher: ";
                echo " ========= <br>".$sql.PHP_EOL;
                $rs_update = SQLexecuteQuery($sql);
                if(!$rs_update) echo "";
            }//end else do if(!empty($rs_publishers_row['data_inicio']))
            $sql = "SELECT substring(otni_data::varchar from 1 for 19) as data_transferencia,otni_origem,otni_destino FROM operadoras_troca_nacional_internacional WHERE opr_codigo = ".$rs_publishers_row['opr_codigo']." ORDER BY otni_data;";
            echo "Verificando se teve movimentação de Internacional para nacional e vice-versa para o Publisher [".$rs_publishers_row['opr_codigo']."]:".PHP_EOL.$sql.PHP_EOL;
            $rs_TrocaNacionalInternacional = SQLexecuteQuery($sql);
            if(pg_num_rows($rs_TrocaNacionalInternacional) > 0) {
               // echo "** Publisher [".$rs_publishers_row['opr_codigo']."] POSSUI TROCA ".PHP_EOL;
                $data_anterior = null;
                while($rs_TrocaNacionalInternacional_row = pg_fetch_array($rs_TrocaNacionalInternacional)) {
                   // echo "*** Dados coletados: ".$rs_TrocaNacionalInternacional_row['data_transferencia']." Origem = ".$rs_TrocaNacionalInternacional_row['otni_origem']." Destino = ".$rs_TrocaNacionalInternacional_row['otni_destino'].PHP_EOL;
                    if($rs_TrocaNacionalInternacional_row['otni_origem'] == 0 && $rs_TrocaNacionalInternacional_row['otni_destino'] == 1) {
                       // echo "**** Mudou de Nacional para Internacional".PHP_EOL;
                        if(is_null($data_anterior)) {
                            $sql = "UPDATE financial_processing SET fp_nationality = 0 WHERE fp_publisher = ".$rs_publishers_row['opr_codigo']." AND fp_date <'".$rs_TrocaNacionalInternacional_row['data_transferencia']."';";
                            //echo "***** Parte 01 => Início dos tempo até a PRIMEIRA TROCA: ".PHP_EOL.$sql.PHP_EOL;
                            $rs_updateTrocaNacionalInternacional = SQLexecuteQuery($sql);
                            if(!$rs_updateTrocaNacionalInternacional) echo "Erro ao Atualizar a Troca de Nacionalidade do Publisher [".$rs_publishers_row['opr_codigo']."] para Início dos tempo até a PRIMEIRA TROCA".PHP_EOL;
                            $sql = "UPDATE financial_processing SET fp_nationality = 1 WHERE fp_publisher = ".$rs_publishers_row['opr_codigo']." AND fp_date >='".$rs_TrocaNacionalInternacional_row['data_transferencia']."';";
                            //echo "***** Parte 02 => De ".$rs_TrocaNacionalInternacional_row['data_transferencia']." até o Final dos tempos para a PRIMEIRA TROCA: ".PHP_EOL.$sql.PHP_EOL;
                            $rs_updateTrocaNacionalInternacional = SQLexecuteQuery($sql);
                            if(!$rs_updateTrocaNacionalInternacional) echo "Erro ao Atualizar a Troca de Nacionalidade do Publisher [".$rs_publishers_row['opr_codigo']."] para de ".$rs_TrocaNacionalInternacional_row['data_transferencia']." até o Final dos tempos para a PRIMEIRA TROCA".PHP_EOL;
                        }//end de if(is_null($data_anterior))
                        else {
                            $sql = "UPDATE financial_processing SET fp_nationality = 0 WHERE fp_publisher = ".$rs_publishers_row['opr_codigo']." AND fp_date <'".$rs_TrocaNacionalInternacional_row['data_transferencia']."' AND fp_date >= '".$data_anterior."';";
                            echo " ========= <br>***** Parte 01 => De ".$data_anterior." até a PRÓXIMA TROCA: ".PHP_EOL.$sql.PHP_EOL;
                            $rs_updateTrocaNacionalInternacional = SQLexecuteQuery($sql);
                            if(!$rs_updateTrocaNacionalInternacional) echo "Erro ao Atualizar a Troca de Nacionalidade do Publisher [".$rs_publishers_row['opr_codigo']."] para De ".$data_anterior." até a PRÓXIMA TROCA".PHP_EOL;
                            $sql = "UPDATE financial_processing SET fp_nationality = 1 WHERE fp_publisher = ".$rs_publishers_row['opr_codigo']." AND fp_date >='".$rs_TrocaNacionalInternacional_row['data_transferencia']."';";
                            echo " ========= <br> ***** Parte 02 => De ".$rs_TrocaNacionalInternacional_row['data_transferencia']." até o Final dos tempos para a PRÓXIMA TROCA: ".PHP_EOL.$sql.PHP_EOL;
                            $rs_updateTrocaNacionalInternacional = SQLexecuteQuery($sql);
                            if(!$rs_updateTrocaNacionalInternacional) echo "Erro ao Atualizar a Troca de Nacionalidade do Publisher [".$rs_publishers_row['opr_codigo']."] para De ".$rs_TrocaNacionalInternacional_row['data_transferencia']." até o Final dos tempos para a PRÓXIMA TROCA".PHP_EOL;
                        }//end else do if(is_null($data_anterior))
                    }//end if($rs_TrocaNacionalInternacional_row['otni_origem'] == 0 && $rs_TrocaNacionalInternacional_row['otni_destino'])
                    elseif($rs_TrocaNacionalInternacional_row['otni_origem'] == 1 && $rs_TrocaNacionalInternacional_row['otni_destino'] == 0) {
                        echo " ========= <br>**** Mudou de Internacional para Nacional".PHP_EOL;
                        if(is_null($data_anterior)) {
                            $sql = "UPDATE financial_processing SET fp_nationality = 1 WHERE fp_publisher = ".$rs_publishers_row['opr_codigo']." AND fp_date <'".$rs_TrocaNacionalInternacional_row['data_transferencia']."';";
                            echo " ========= <br>**** Parte 01 => Início dos tempo até a PRIMEIRA TROCA: ".PHP_EOL.$sql.PHP_EOL;
                            $rs_updateTrocaNacionalInternacional = SQLexecuteQuery($sql);
                            if(!$rs_updateTrocaNacionalInternacional) echo "Erro ao Atualizar a Troca de Nacionalidade do Publisher [".$rs_publishers_row['opr_codigo']."] para Início dos tempo até a PRIMEIRA TROCA".PHP_EOL;
                            $sql = "UPDATE financial_processing SET fp_nationality = 0 WHERE fp_publisher = ".$rs_publishers_row['opr_codigo']." AND fp_date >='".$rs_TrocaNacionalInternacional_row['data_transferencia']."';";
                            echo "***** Parte 02 => De ".$rs_TrocaNacionalInternacional_row['data_transferencia']." até o Final dos tempos para a PRIMEIRA TROCA: ".PHP_EOL.$sql.PHP_EOL;
                            $rs_updateTrocaNacionalInternacional = SQLexecuteQuery($sql);
                            if(!$rs_updateTrocaNacionalInternacional) echo "Erro ao Atualizar a Troca de Nacionalidade do Publisher [".$rs_publishers_row['opr_codigo']."] para de ".$rs_TrocaNacionalInternacional_row['data_transferencia']." até o Final dos tempos para a PRIMEIRA TROCA".PHP_EOL;
                        }//end de if(is_null($data_anterior))
                        else {
                            $sql = "UPDATE financial_processing SET fp_nationality = 1 WHERE fp_publisher = ".$rs_publishers_row['opr_codigo']." AND fp_date <'".$rs_TrocaNacionalInternacional_row['data_transferencia']."' AND fp_date >= '".$data_anterior."';";
                            echo "***** Parte 01 => De ".$data_anterior." até a PRÓXIMA TROCA: ".PHP_EOL.$sql.PHP_EOL;
                            $rs_updateTrocaNacionalInternacional = SQLexecuteQuery($sql);
                            if(!$rs_updateTrocaNacionalInternacional) echo "Erro ao Atualizar a Troca de Nacionalidade do Publisher [".$rs_publishers_row['opr_codigo']."] para De ".$data_anterior." até a PRÓXIMA TROCA".PHP_EOL;
                            $sql = "UPDATE financial_processing SET fp_nationality = 0 WHERE fp_publisher = ".$rs_publishers_row['opr_codigo']." AND fp_date >='".$rs_TrocaNacionalInternacional_row['data_transferencia']."';";
                            echo "***** Parte 02 => De ".$rs_TrocaNacionalInternacional_row['data_transferencia']." até o Final dos tempos para a PRÓXIMA TROCA: ".PHP_EOL.$sql.PHP_EOL;
                            $rs_updateTrocaNacionalInternacional = SQLexecuteQuery($sql);
                            if(!$rs_updateTrocaNacionalInternacional) echo "Erro ao Atualizar a Troca de Nacionalidade do Publisher [".$rs_publishers_row['opr_codigo']."] para De ".$rs_TrocaNacionalInternacional_row['data_transferencia']." até o Final dos tempos para a PRÓXIMA TROCA".PHP_EOL;
                        }//end else do if(is_null($data_anterior))
                    }//end do elseif($rs_TrocaNacionalInternacional_row['otni_origem'] == 0 && $rs_TrocaNacionalInternacional_row['otni_destino'])
                    else {
                        echo "**** Mudança com direção não Identificada".PHP_EOL;
                    }//end do else do do elseif($rs_TrocaNacionalInternacional_row['otni_origem'] == 0 && $rs_TrocaNacionalInternacional_row['otni_destino'])
                    $data_anterior = $rs_TrocaNacionalInternacional_row['data_transferencia'];
                }//end while
            }//end if(pg_num_rows($rs_TrocaNacionalInternacional) > 0)
            else {
              //  echo "** Publisher [".$rs_publishers_row['opr_codigo']."] NÃO Possui Troca ".PHP_EOL;
                //Publisher Internacional
                if($rs_publishers_row['opr_internacional_alicota'] > 0) {
                    $sql = "UPDATE financial_processing SET fp_nationality = 1 WHERE fp_publisher = ".$rs_publishers_row['opr_codigo'].";";
                    echo "*** Todos tempos Internacional: ".PHP_EOL.$sql.PHP_EOL;
                    $rs_updateTrocaNacionalInternacional = SQLexecuteQuery($sql);
                    if(!$rs_updateTrocaNacionalInternacional) echo "Erro ao Atualizar ao Marcar a Nacionalidade do Publisher [".$rs_publishers_row['opr_codigo']."] para Todos os Tempos".PHP_EOL;
                }//end if($rs_publishers_row['opr_internacional_alicota'] > 0)
                //Publisher Nacional
                else {
                    $sql = "UPDATE financial_processing SET fp_nationality = 0 WHERE fp_publisher = ".$rs_publishers_row['opr_codigo'].";";
                    echo "*** Todos tempos Nacional: ".PHP_EOL.$sql.PHP_EOL;
                    $rs_updateTrocaNacionalInternacional = SQLexecuteQuery($sql);
                    if(!$rs_updateTrocaNacionalInternacional) echo "Erro ao Atualizar ao Marcar a Nacionalidade do Publisher [".$rs_publishers_row['opr_codigo']."] para Todos os Tempos".PHP_EOL;
                }//end else do if($rs_publishers_row['opr_internacional_alicota'] > 0)
            }//end else do if(pg_num_rows($rs_TrocaNacionalInternacional) > 0)
        }//end else do if($rs_publishers_row['opr_vinculo_empresa'] == 0)
        //echo "=========".str_repeat('-',80).PHP_EOL;
    }//end while
}//end if($n_publishers > 0)
else {
    echo "ERRO: Nenhum Publisher foi selecionado para vínculo entre empresas".PHP_EOL.PHP_EOL;
}
//end else do if($n_publishers > 0)
/*********************************************************************** 
 * *****   Marcando os períodos a quais empresas pertencem
 * *****   FINAL
 ***********************************************************************/

echo str_repeat("_", 80) .PHP_EOL."Elapsed time: ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.').PHP_EOL.str_repeat("=", 80) .PHP_EOL;

logProcessamento("Finalizou (".date("Y-m-d H:i:s").")");
?>