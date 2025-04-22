<?php
header("Content-Type: text/html; charset=ISO-8859-1",true);
// Cruzamento de Dados de Usuários com Compras no Último Mês que são Pessoas Politicamente Expostas
// pep_cruzamento.php 

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

set_time_limit(1200);
ini_set('max_execution_time', 1200); 

//Data considerada
$data_inicio = mktime(0, 0, 0, date('n')-1,  date('d'), date('Y'));
$data_fim = mktime(0, 0, 0, date('n'),  date('d'), date('Y'));
//Teste 55 (Homologação)
//$data_inicio = mktime(0, 0, 0, 8,  date('d'), 2012);
//$data_fim = mktime(0, 0, 0, 11,  date('d'), 2012);
$dataClickIni = date('Y-m-d',$data_inicio);
$dataClickFim = date('Y-m-d',$data_fim);

// Dados do Email
$email  = "rc@e-prepag.com.br,rc1@e-prepag.com.br";
$cc     = "glaucia@e-prepag.com.br";
$bcc    = "wagner@e-prepag.com.br";
$subject= "Cruzamento de Dados PEP";
$msg    = "";

require_once "../includes/main.php";
require_once $raiz_do_projeto . "includes/gamer/main.php"; 
include_once $raiz_do_projeto . "includes/complice/functions.php";

$time_start_stats = getmicrotime();

echo PHP_EOL.str_repeat("=", 80).PHP_EOL."Cruzamento de Dados de Usuários com Compras no Último Mês que são Pessoas Politicamente Expostas (".date("Y-m-d H:i:s").")".PHP_EOL.PHP_EOL;

//Publishers Exigem CPFs como Obrigatórios
$vetorPublisher = levantamentoPublisherObrigatorioCPF($vetorPublisherLegenda);

//Buscando CPF e nomes envolvido em transações de vendas
$sql = "select ug_nome,tipo,ug_cpf from ( 
            (select 
                    'Gamer' as tipo,
                    ug_cpf,
                    UPPER(ug_nome_cpf) as ug_nome
            from tb_venda_games vg 
                    inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                    inner join usuarios_games ug on (ug.ug_id = vg.vg_ug_id)
            where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                    and vg.vg_data_concilia >= '".$dataClickIni." 00:00:00'
                    and vg.vg_data_concilia <= '".$dataClickFim." 23:59:59'
                    and vg.vg_ug_id != '".$GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']."'
                    and vgm_opr_codigo IN (".implode(",", $vetorPublisher).")
            group by tipo, ug_nome_cpf, ug_cpf)

        union all

            (select 
                    'CPF na Venda do PDV' as tipo,
                    vgm_cpf as ug_cpf, 
                    UPPER(vgm_nome_cpf) as ug_nome
            from tb_dist_venda_games vg 
                    inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
            where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                    and vg.vg_data_inclusao >= '".$dataClickIni." 00:00:00'
                    and vg.vg_data_inclusao <= '".$dataClickFim." 23:59:59'
                    and vgm_opr_codigo IN (".implode(",", $vetorPublisher).") 
            group by tipo, vgm_nome_cpf, vgm_cpf) 

        union all

            (select 
                    'PDV - Representante Legal' as tipo,
                    ug_repr_legal_cpf as ug_cpf, 
                    UPPER(ug_repr_legal_nome) as ug_nome
            from tb_dist_venda_games vg 
                    inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                    inner join dist_usuarios_games ug on ug.ug_id = vg.vg_ug_id
            where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                    and vg.vg_data_inclusao >= '".$dataClickIni." 00:00:00'
                    and vg.vg_data_inclusao <= '".$dataClickFim." 23:59:59'
                    and vgm_opr_codigo IN (".implode(",", $vetorPublisher).") 
            group by tipo, ug_repr_legal_nome, ug_repr_legal_cpf) 

        union all

            (select 
                    'PDV - Sócio' as tipo,
                    ugs_cpf as ug_cpf,
                    UPPER(ugs_nome) as ug_nome
            from tb_dist_venda_games vg 
                    inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                    inner join dist_usuarios_games_socios ugc on ugc.ug_id = vg.vg_ug_id
            where vg.vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                    and vg.vg_data_inclusao >= '".$dataClickIni." 00:00:00'
                    and vg.vg_data_inclusao <= '".$dataClickFim." 23:59:59'
                    and vgm_opr_codigo IN (".implode(",", $vetorPublisher).") 
            group by tipo, ugs_nome, ugs_cpf) 

        union all

            (select 
                    'CPF no Gift Card' as tipo,
                    picc_cpf as ug_cpf, 
                    UPPER(picc_nome) as ug_nome
            from pins_integracao_card_historico
                left outer join pins_integracao_card_cpf ON (pin_codinterno=pih_pin_id)
            where pin_status = '4' 
                    and pih_codretepp = '2'
                    and pih_data >= '".$dataClickIni." 00:00:00'
                    and pih_data <= '".$dataClickFim." 23:59:59'
                    and pih_id IN (".implode(",", $vetorPublisher).") 
            group by tipo, picc_nome, picc_cpf)

        union all

            (select 
                    'CPF no Boleto Express' as tipo,
                    vgcbe_cpf as ug_cpf, 
                    UPPER(vgcbe_nome_cpf) as ug_nome
            from tb_venda_games_cpf_boleto_express
                inner join tb_venda_games ON (vg_id = vgcbe_vg_id)
                inner join tb_venda_games_modelo ON (vgm_vg_id = vg_id)
            where vg_ultimo_status='".$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']."' 
                    and vgcbe_data_inclusao >= '".$dataClickIni." 00:00:00'
                    and vgcbe_data_inclusao <= '".$dataClickFim." 23:59:59'
                    and vgm_opr_codigo IN (".implode(",", $vetorPublisher).") 
            group by tipo, vgcbe_nome_cpf, vgcbe_cpf)
) tabelaUnion 
            group by ug_nome, tipo, ug_cpf  
            order by ug_nome;
    ";

echo "SQL :".$sql.PHP_EOL;
$rs = SQLexecuteQuery($sql);
$exibicaoDadosProblemas = NULL;
if($rs) {
    $total_de_registros = pg_num_rows($rs);
    if($total_de_registros > 0) {
        $cabecalho = "
        <table class='table table-bordered top20' >
        <thead class=''>
            <tr>
                <th colspan='5'>Total Nomes Levantados: ".$total_de_registros."</th>
            </tr>
            <tr>
                <th>Tipo EPP</th>
                <th>CPF</th>
                <th>Nome - Nosso Banco de Dados</th>
                <th>Nome Encontrado - Lista PEP</th>
                <th>Tipo PEP</th>
            </tr>
        </thead>
         ";
                    while ($rsRow = pg_fetch_array($rs)) {
                        
                       // Eliminando espaços em branco inicio e fim da String
                       $rsRow['ug_nome'] = trim($rsRow['ug_nome']);

                       // Eleiminado mascara e convertendo para inteiro
                       $cpf_aux = str_replace("-","",str_replace(".", "", $rsRow['ug_cpf']))*1;
                       
                       // Verificando conteudo válido oara pesquisa
                       if($cpf_aux > 0) {
                        
                            //Buscando insidencia na lista da PEP
                            $sql = "SELECT * FROM pep WHERE cpf = ". str_replace("-","",str_replace(".", "", $rsRow['ug_cpf']))*1 .";";
                            //echo $sql.PHP_EOL;
                            $rsEncontrado = SQLexecuteQuery($sql);
                            // Verificando se foi encontrado algum nome
                            if(isset($rsEncontrado) && pg_num_rows($rsEncontrado) > 0) {
                                // Exibindo todas incidencias de nomes
                                while ($rsEncontradoRow = pg_fetch_array($rsEncontrado)) {
                                    
                                    $exibicaoDadosProblemas .= " 
                                            <tbody title='Nome Encontrado'>
                                                <tr class='trListagem'>
                                                    <td>".$rsRow['tipo']."</td>
                                                    <td>".$rsRow['ug_cpf']."</td>
                                                    <td>".$rsRow['ug_nome']."</td>
                                                    <td>".$rsEncontradoRow['nome']."</td>
                                                    <td>".$rsEncontradoRow['descricao_funcao']."</td>
                                                </tr>
                                    ";
                                }//end while ($rsEncontradoRow = pg_fetch_array($rsEncontrado))
                            }//end if(pg_num_rows($rsEncontrado) > 0) 
                        }//end if($cpf_aux > 0)
                    }//end while 
                }//end if(pg_num_rows($rs) > 0)
                else {
                        echo "Nenhum registro encontrado.".PHP_EOL;
                }//end else do if(pg_num_rows($rs) > 0) 
}//end if($rs) 
else echo "ERRO na query acima.".PHP_EOL;

if(!empty($exibicaoDadosProblemas)) {
        $msg .= "Período Considerado $dataClickIni até $dataClickFim.<br>".PHP_EOL;
        $msg .= $cabecalho.$exibicaoDadosProblemas."</table>";
        echo strip_tags($msg).PHP_EOL;

        if(!empty($msg)) {
            if(enviaEmail($email, $cc, $bcc, $subject, $msg)) {
                echo "Email enviado com sucesso".PHP_EOL;
            }
            else {
                echo "Problemas no envio do Email".PHP_EOL." TO: ".$email."".PHP_EOL." CC: ".$cc."".PHP_EOL." BCC: ".$bcc."".PHP_EOL." SUBJECT: ".$subject."".PHP_EOL;
            }
        }//end if(!empty($msg))
}//end if(!empty($exibicaoDadosProblemas))

echo str_repeat("_", 80) .PHP_EOL."Elapsed time (total: ".count($vetor_ug_id)."): ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.').PHP_EOL.str_repeat("=", 80).PHP_EOL;

//Fechando Conexão
pg_close($connid);

?>