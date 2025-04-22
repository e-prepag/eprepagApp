<?php
require_once "../includes/main.php";
require_once $raiz_do_projeto . "includes/pdv/main.php"; 

$time_start = getmicrotime();
echo "Processamento de Faixas (".date("Y-m-d H:i:s").")".PHP_EOL.PHP_EOL;

// Levanta as lans que devem ser informadas
$sql = "select ug_id, ug_email, ug_perfil_saldo, ug_data_email_saldo, ug_faixa_email_saldo,
        (case 	when (ug_perfil_saldo>=0 and ug_perfil_saldo<20) then 1   
                when (ug_perfil_saldo>=20 and ug_perfil_saldo<100) then 2 
                when (ug_perfil_saldo>=100) then 3 
                else -1 
        end) as faixa, (EXTRACT(DAY FROM (now() - ug_data_email_saldo))) as delay
from dist_usuarios_games 
where ug_risco_classif=2 and ug_ativo=1 and (EXTRACT(DAY FROM (now() - ug_data_email_saldo))) >=7   
order by ug_perfil_saldo";

echo "sql: $sql".PHP_EOL;

// Lista os registros encontrados:
$rs_lans_saldo_aviso = SQLexecuteQuery($sql);
$total_registros =  pg_num_rows($rs_lans_saldo_aviso);
echo "Total de Registros Levantados: ".$total_registros.PHP_EOL;
if($rs_lans_saldo_aviso && $total_registros > 0){
        while ($rs_lans_saldo_aviso_row = pg_fetch_array($rs_lans_saldo_aviso)){

                $ug_id = $rs_lans_saldo_aviso_row['ug_id'];
                $ug_email = $rs_lans_saldo_aviso_row['ug_email'];
                $faixa = $rs_lans_saldo_aviso_row['faixa'];
                $delay = $rs_lans_saldo_aviso_row['delay'];
                $ug_perfil_saldo = $rs_lans_saldo_aviso_row['ug_perfil_saldo'];
                $ug_faixa_email_saldo = $rs_lans_saldo_aviso_row['ug_faixa_email_saldo'];
                $ug_data_email_saldo = $rs_lans_saldo_aviso_row['ug_data_email_saldo'];
                $faixa_index = $faixa-1;

                if($faixa_index<0 || $faixa_index>1) $faixa_index = 0;

                // Se faz uma semana que não receve ou se a faixa mudou -> envia email
                // a partir de 2010-12-09 => manda só faixa=0 (lans perto de zero)
                if( ($delay>=7) || ($faixa!=$ug_faixa_email_saldo)) {
                    salva_faixa_timestamp($ug_id, $faixa); 
                    echo "Faixa do tipo $faixa_index para $ug_email (id: $ug_id) (delay: $delay) (faixa: $faixa) (saldo: R$".number_format($ug_perfil_saldo, 2, ',','.') .")".PHP_EOL;
                } 
                else echo "Não manda, ainda sem tempo..... (faixa: $faixa, delay: $delay, ug_faixa_email_saldo: $ug_faixa_email_saldo, ug_data_email_saldo: '$ug_data_email_saldo')".PHP_EOL;	
        }//end while
}//end if($rs_lans_saldo_aviso && $total_registros > 0)
else {
    echo "Nada encontrado ".PHP_EOL;
}
echo "Elapsed time : " . number_format(getmicrotime() - $time_start, 2, '.', '.') . " segundos.".PHP_EOL.str_repeat("=",80).PHP_EOL;

function salva_faixa_timestamp($id, $faixa) {
        global $connid;
        $sql = "update dist_usuarios_games set ug_faixa_email_saldo=".$faixa.", ug_data_email_saldo=now() where ug_id=".$id.";";
        pg_exec($connid,$sql);

}

//Fechando Conexão
pg_close($connid);

?>
