<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

set_time_limit(6000);
ini_set('max_execution_time', 6000); 

require_once "../includes/main.php";
require_once "../includes/functions.php";
require_once $raiz_do_projeto . "class/classManipulacaoArquivosLog.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";

$arquivoLog = new ManipulacaoArquivosLog($argv);

if(!$arquivoLog->haveFile()) {
    $arquivoLog->createLockedFile();
    $nome_arquivo = $arquivoLog->getNomeArquivo();

    ob_start('callbackLog');
    
    echo PHP_EOL.str_repeat("=",80).PHP_EOL."Data execução : ".date('Y-m-d H:i:s').PHP_EOL.PHP_EOL;

    //Declarando valor IOF 6.38 ou 0.38
    $iof = array(6.38,0.38);

    $time_start = getmicrotime();

    $b_debug = false;

    $dd_opr_codigo = 90;
    
    /* ----- Para forçar uma data especifica ----------
    $data = mktime(0, 0, 0, 8, 23, 2018);
    $date = date ('Y-m-j G:i:s',$data);
    */
    
    //Definindo datas
    $date = date ('Y-m-j G:i:s');
    $tf_data_final = date('d/m/Y', strtotime('-1 day'.$date));
    $tf_data_inicial = date('d/m/Y', strtotime('-1 day'.$date));

    //Definindo nome do arquivo
    $arq_csv = "E_Prepag_".date('Y_m_d', strtotime('-1 day'.$date)).".csv";
    $nome_do_arquivo = $raiz_do_projeto."arquivos_gerados/riot/".$arq_csv;
    
    //Dados de conexão SFTP na RIOT
    $host = getEnvVariable('SFTP_HOST'); 
    $port = getEnvVariable('SFTP_PORT'); 
    $username = getEnvVariable('CLIENT_ID');
    $password = getEnvVariable('SFTP_PASSWORD');
    $remoteDir = '/filesDir/';
    $localDir = '/data/';

    // Levantando as comissões por canal
    $sql = "
            select co_opr_codigo, co_canal, co_data_inclusao, co_volume_tipo, co_volume_min, co_comissao, opr_internacional_alicota
            from operadoras o 
                left outer join tb_comissoes c on o.opr_codigo=c.co_opr_codigo 
            where to_char(co_data_inclusao,'YYYYDDMMHH24MISS') = (select to_char(max(co_data_inclusao),'YYYYDDMMHH24MISS') from tb_comissoes where co_opr_codigo=opr_codigo) 
                and opr_codigo = ".$dd_opr_codigo."
            group by co_opr_codigo, co_canal, co_data_inclusao, co_volume_tipo, co_volume_min, co_comissao, opr_internacional_alicota 
            order by co_opr_codigo, co_canal, co_data_inclusao desc, co_volume_tipo, co_volume_min  
           ";
    echo $sql.PHP_EOL;
    $rescommiss = pg_exec($connid,$sql);
    $vetorComissao = "";
    $vetorComissaoIOF = "";
    $vetorAlicotaIOF = "";
    while ($rescommiss_row = pg_fetch_array($rescommiss)) {
        $rescommiss_row['co_canal'] = trim($rescommiss_row['co_canal']);
        $vetorAlicotaIOF[$rescommiss_row['co_opr_codigo']] = $rescommiss_row['opr_internacional_alicota'];
        $vetorComissaoIOF[$rescommiss_row['co_opr_codigo']][$rescommiss_row['co_canal']] = false;
        if(empty($rescommiss_row['co_canal'])) {
            $rescommiss_row['co_volume_tipo'] = trim($rescommiss_row['co_volume_tipo']);
            if(empty($rescommiss_row['co_volume_tipo'])) {
                $vetorComissao[$rescommiss_row['co_opr_codigo']][$rescommiss_row['co_volume_min']] = $rescommiss_row['co_comissao'];
            } //end if(empty($rescommiss_row['co_volume_tipo']))
            else {
                $vetorComissao[$rescommiss_row['co_opr_codigo']][$rescommiss_row['co_volume_tipo']][$rescommiss_row['co_volume_min']] = $rescommiss_row['co_comissao'];
            }//end else do if(empty($rescommiss_row['co_volume_tipo']))
        }//end if(empty($rescommiss_row['co_canal']))
        else {
            if(in_array($rescommiss_row['opr_internacional_alicota'], $iof) && !empty($rescommiss_row['co_comissao'])) {
                $vetorComissao[$rescommiss_row['co_opr_codigo']][$rescommiss_row['co_canal']] = $rescommiss_row['co_comissao'];
                $vetorComissaoIOF[$rescommiss_row['co_opr_codigo']][$rescommiss_row['co_canal']] = true;
            }//end if(in_array($rescommiss_row['opr_internacional_alicota'], $iof) && !empty($rescommiss_row['co_comissao']))
            else {
                $vetorComissao[$rescommiss_row['co_opr_codigo']][$rescommiss_row['co_canal']] = $rescommiss_row['co_comissao'];
            }//end else
        }//end else do if(empty($rescommiss_row['co_canal']))
    }//end while
    echo print_r($vetorComissao,true);

    //Instanciando Objetos para Descriptografia
    $chave256bits = new Chave();
    $pc = new AES($chave256bits->retornaChave());

    //Buscando PINs na banco de dados
    $sql = "
            select 
                pin_codinterno, 
                case_codigo, 
                opr_nome, 
                pin_valor, 
                opr_codigo,
                pin_datavenda,
                pin_horavenda,
                riot_order_id,
                vg_canal
            FROM (
               (select 
                    t0.pin_codinterno, 
                    pin_codigo as case_codigo, 
                    t1.opr_nome, 
                    t0.pin_valor, 
                    t0.opr_codigo,
                    to_char(pih_data,'YYYY-MM-DD')::timestamp as pin_datavenda,
                    to_char(pih_data,'HH24:MI:SS') as pin_horavenda,
                    t4.riot_order_id,
                    case 
                        when t0.pin_status = '3' then 'G'
                        when t0.pin_status = '6' then 'L'
                        -- TODO => Alterar a linha abaixo para verificar se a venda foi para gamer ou LAN
                        when t0.pin_status = '8' then 'L'
                    end as vg_canal
                from pins t0
                    INNER JOIN operadoras t1 ON (t0.opr_codigo=t1.opr_codigo)
                    INNER JOIN pins_status t3 ON (t0.pin_status=t3.stat_codigo) 
                    INNER JOIN pins_riot_id t4 ON (t0.pin_codinterno=t4.pin_codinterno)
                    INNER JOIN pins_integracao_historico pih ON pih_pin_id = t0.pin_codinterno
                where 
                    (t4.pin_channel='L' OR t4.pin_channel IS NULL)
                    and t0.pin_status = '8'
                    and pih_codretepp='2'
                    ";
        if($tf_data_inicial && $tf_data_final) {
                    $data_inic = formata_data(trim($tf_data_inicial), 1);
                    $data_fim = formata_data(trim($tf_data_final), 1); 
                    $sql .= " 
                    and pih_data >= '".trim($data_inic)." 00:00:00' and  pih_data <= '".trim($data_fim)." 23:59:59'  ".PHP_EOL; 
        }
        $sql .= " 
                   and (t0.opr_codigo=".$dd_opr_codigo.")  
                        
                 ) 
                 
                UNION ALL
                   
               ( SELECT 
                    pih_pin_id as pin_codinterno, 
                    pin_codigo as case_codigo, 
                    o.opr_nome, 
                    pin_valor, 
                    pih_id as opr_codigo,
                    to_char(pih_data,'YYYY-MM-DD')::timestamp as pin_datavenda,
                    to_char(pih_data,'HH24:MI:SS') as pin_horavenda,
                    r.riot_order_id,
                    'C' as vg_canal
                FROM pins_integracao_card_historico pich
                    INNER JOIN pins_card pc ON pin_codinterno=pih_pin_id
                    INNER JOIN operadoras o ON pc.opr_codigo=o.opr_codigo
                    INNER JOIN pins_riot_id r ON (pc.pin_codinterno=r.pin_codinterno)
                WHERE pih_codretepp='2'
                    and pich.pin_status =4
                    and pin_lote_codigo > 6 -- Codigo de lotes menor e igual a 6 foram utilizados para testes 
                    and (r.pin_channel='C' OR r.pin_channel IS NULL)
                    and pih_id = ".$dd_opr_codigo.PHP_EOL;
        if($tf_data_inicial && $tf_data_final) {
                    $data_inic = formata_data(trim($tf_data_inicial), 1);
                    $data_fim = formata_data(trim($tf_data_final), 1); 
                    $sql .= " 
                    and pih_data >= '".trim($data_inic)." 00:00:00' and  pih_data <= '".trim($data_fim)." 23:59:59'  ".PHP_EOL; 
        }
        $sql .= " 
                )
            ) as selection
            order by pin_datavenda desc, pin_horavenda desc";
    echo $sql.PHP_EOL;
    $resid = pg_exec($connid, $sql);
    $total_table = pg_num_rows($resid);

    if($total_table > 0) {
        $conteudo = '"PSPCode";"RIOT ORDER ID";"PIN";"TransactionType";"Region";"ID";"Transaction";"Purchase";"E-Prepag + Tax";"Net Amount";"Country Code"'.PHP_EOL;
        while ($pgrow = pg_fetch_array($resid)) {
                $valor = 1;

                //Calculando a Comissão
                if($vetorComissaoIOF[$pgrow['opr_codigo']][$pgrow['vg_canal']]) {
                    $aux_comiss = ($pgrow['pin_valor']*$vetorComissao[$pgrow['opr_codigo']][$pgrow['vg_canal']]/100);
                    // Calculando IOF da nova maneira
                    $total_sem_iof = $pgrow['pin_valor']/(1+$vetorAlicotaIOF[$pgrow['opr_codigo']]/100);
                    $aux_comiss += $total_sem_iof*$vetorAlicotaIOF[$pgrow['opr_codigo']]/100;                                
                }
                else {
                    $aux_comiss = ($pgrow['pin_valor']*$vetorComissao[$pgrow['opr_codigo']][$pgrow['vg_canal']]/100);
                }
                //Fim Calculando a Comissão

                $valor_geral += $pgrow['pin_valor'];
                $valor_comiss += $aux_comiss;
                $valor_liquido += ($pgrow['pin_valor']-$aux_comiss);

                if(strtoupper($pgrow['vg_canal']) == 'C') {
                    $pin_serial	= $pc->decrypt(base64_decode($pgrow['case_codigo']));
                }//end if(strtoupper($pgrow['vg_canal']) == 'C')
                else {
                    $pin_serial	= $pgrow['case_codigo'];
                }//end else do if(strtoupper($pgrow['vg_canal']) == 'C') 

                $opr_codigo	= $pgrow['opr_codigo'];

                $conteudo .= '"E-Prepag";"'.$pgrow['riot_order_id'].'";\''.$pin_serial.'\';"'.($pgrow['vg_canal']=='C'?"PIN Card":"PIN Virtual").'";"BR";"'. $pgrow['pin_codinterno'].'";"'.(($pgrow['pin_datavenda'])?monta_data($pgrow['pin_datavenda']).' - '.$pgrow['pin_horavenda']:"--").'";"R$ '.number_format($pgrow['pin_valor'], 2, '.', '').'";"R$ '.number_format($aux_comiss, 2, '.', '').'";"R$ '.number_format(($pgrow['pin_valor']-$aux_comiss), 2, '.', '').'";"BR"'.PHP_EOL;
        }
        if(!$valor) {
            $conteudo .= "Empty File";
        }

        //Gerando o arquivo 
        $fp = fopen($nome_do_arquivo,"w+");
        fwrite($fp, $conteudo);
        fclose($fp);

        if(file_exists($nome_do_arquivo)) echo PHP_EOL."Arquivo gerado com Sucesso!".PHP_EOL;

        //Desalocando a memoria
        flush();
        ob_flush();
        //ob_end_flush();
        
        //Transferindo arquivo por SFTP
        $connection = ssh2_connect($host, $port);
        if($connection === FALSE) {
            echo 'Failed to connect'.PHP_EOL;
        }
        else {
            if(ssh2_auth_password($connection, $username, $password) === FALSE) {
                echo 'Failed to authenticate'.PHP_EOL;
            }
            else {
                //Linha abaixo não deu certo
                //ssh2_scp_send($connection, '/local/file with space.txt', '"/remote/file with space.txt"', 0777);
                //var_dump( $nome_do_arquivo, $localDir.$arq_csv);
                
                $resSFTP = ssh2_sftp($connection);
                $resFile = fopen("ssh2.sftp://$username:$password@$host:$port".$localDir.$arq_csv, 'w');
                $srcFile = fopen($nome_do_arquivo, 'r');
                $writtenBytes = stream_copy_to_stream($srcFile, $resFile);
                if($writtenBytes > 0) {
                    echo "Total de ".$writtenBytes." Bytes Transferidos!".PHP_EOL;
                    if($writtenBytes == filesize($nome_do_arquivo))
                        echo "Arquivo transferido com Sucesso!".PHP_EOL;
                    else echo "Arquivo não foi completamente transferido!".PHP_EOL;
                }//end if($writtenBytes > 0) 
                else echo "Falha na transferencia do arquivo".PHP_EOL;
                fclose($resFile);
                fclose($srcFile);
            }//end else do if(ssh2_auth_password() === FALSE)
        }//end else do if($connection === FALSE)

    } //end if($total_table > 0)

    $arquivoLog->deleteLockedFile();
}
else {
    $arquivoLog->showBusy();
}

//Fechando Conexão
pg_close($connid);

?>