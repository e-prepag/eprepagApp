<?php
require_once "../includes/main.php";
require_once $raiz_do_projeto . "includes/pdv/main.php"; 

set_time_limit(3600);
ini_set('max_execution_time', 3600);

/**
 * ug_substatus = 11 = Lan House Aprovada => Vari�vel no constante.php do prepag2/dist_commerce $SUBSTATUS_LH
 * ug_risco_classif = 2 = Lan House Pre Paga
 */

$time_start_stats = getmicrotime();

echo str_repeat("=", 80).PHP_EOL.date('Y-m-d H:i:s').PHP_EOL.PHP_EOL;

$sql = 'SELECT ug_id FROM dist_usuarios_games
WHERE ug_substatus = 11
  AND ug_risco_classif = '.$RISCO_CLASSIFICACAO['PR�-PAGO'].'
  AND ug_ativo = 1
  AND ug_perfil_saldo < '.$RISCO_LANS_PRE_VALOR_MIN.'
  AND ug_email <> \'\'
  AND (((EXTRACT(DAY FROM (now() - ug_data_envio_saldo_minimo))) > 30) OR ug_data_envio_saldo_minimo IS NULL)
  AND ((EXTRACT(DAY FROM (now() - ug_data_inclusao))) > 30);';

echo $sql.PHP_EOL.PHP_EOL;

$rs = SQLexecuteQuery($sql);

$qtde_registros = pg_num_rows($rs);
if ($qtde_registros > 0 ) {
    echo "Ser� enviado emails para ".$qtde_registros." LANs contendo saldo menor que o m�nimo".PHP_EOL;
    while ( $row = pg_fetch_array($rs) ) {
        $c = new EnvioEmailAutomatico('L', 'SaldoMinimoLH');
        $c->setUgID($row['ug_id']);
        $c->MontaEmailEspecifico();
        $sql = "UPDATE dist_usuarios_games SET ug_data_envio_saldo_minimo = NOW() WHERE ug_id=".$row['ug_id'].";";
        echo "SQL atualiza data do envio do email: ".$sql.PHP_EOL;
        $rs_update =SQLexecuteQuery($sql);
        if(!$rs_update) echo "Problema na execu��o da query de atualiza��o da data do envio.".PHP_EOL;
    }
}//end if ($qtde_registros > 0 )
else {
    echo "N�o existe nenhuma LAN com Saldo inferior ao saldo m�nimo.".PHP_EOL;
}

echo "".PHP_EOL.str_repeat("_", 80) .PHP_EOL."Elapsed time : ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.').PHP_EOL.str_repeat("=", 80) .PHP_EOL;
