<?php


function con(){

//Conectando ao Banco de dados

$con = pg_connect("host=".DB_HOST." port=".DB_PORT." dbname=".DB_BANCO." user=".DB_USER." password=".DB_PASS);

return $con;

}

if (!function_exists('checkIP')) {

    function checkIP() {

        $aux_return = false;

        $sComputerName = 'SERVER_WITHOUT_COMPUTERNAME';

        $pComputerName = 'ENV_WITHOUT_COMPUTERNAME';

        foreach ($GLOBALS['ENV_LIST'] as $IP => $parametros) {

            if ( array_key_exists('COMPUTERNAME', $GLOBALS['_SERVER']) ) {

                $sComputerName = $GLOBALS['_SERVER']['COMPUTERNAME'];

            }

            else $sComputerName = php_uname('n');

            if ( array_key_exists('COMPUTERNAME', $parametros) ) {

                $pComputerName = $parametros['COMPUTERNAME'];

            }

            if(@$GLOBALS['_SERVER']['SERVER_NAME'] == $IP or $sComputerName == $pComputerName) {

                $aux_return = $parametros;

            }//end if($_SERVER['SERVER_NAME'] == $IP or $_SERVER['COMPUTERNAME'] == $parametros['COMPUTERNAME'])

        }//end foreach

        return $aux_return;

    }//end function checkIP()

}//end if (!function_exists('checkIP'))

else {

    //Arquivo

    $file = $raiz_do_projeto."log/log_Debug_Dupla_Funcao.txt";

    //Mensagem

    $mensagem = date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . "\nExistiu tentativa de duplica��o de FUN��O neste ponto. (FUN��O: checkIP)\n";

    //Grava mensagem no arquivo

    if ($handle = fopen($file, 'a+')) {

            fwrite($handle, $mensagem);

            fclose($handle);

    } 

}//end else do if (!function_exists('checkIP'))

// Fun��o de execu��o de Instru��o no DB

function SQLexecuteQueryTWO($con, $sql) {



    $ret = pg_query ($con, $sql);

    if (strlen ($erro = pg_last_error($con))) {

    		$message  = date("Y-m-d H:i:s") . " ";

    		$message .= "Erro: " . $erro . "<br>\n";

    		$message .= "Query: " . $sql . "<br>\n";

    		gravaLog_SQLexecuteQuery($message);

    }



    return $ret;		

}//end function SQLexecuteQuery($sql)


?>