<?php
$BASE_IP = "192.168.200.";

$ENV_LIST = array(
                 $BASE_IP.'51' => array (
                                            'PREFIX_NAME' => 'martin',
                                            'COMPUTERNAME' => 'VM-DEV',
                                            'EMAIL' => 'admin@e-prepag.com',
                                            'URL' => '/MARTIN.E-PREPAG.COM/'
                                             ),
                 $BASE_IP.'52' => array (
                                            'PREFIX_NAME' => 'wagner',
                                            'COMPUTERNAME' => 'VM-DEV2',
                                            'EMAIL' => 'wagner@e-prepag.com.br',
                                            'URL' => '/WAGNER.E-PREPAG.COM.BR/'
                                             ),
                 $BASE_IP.'53' => array (
                                            'PREFIX_NAME' => 'dev1',
                                            'COMPUTERNAME' => 'VM-DEV3',
                                            'EMAIL' => 'caike@e-prepag.com.br',
                                            'URL' => '/WAGNER.E-PREPAG.COM.BR/'
                                             ),
                 $BASE_IP.'55' => array (
                                            'PREFIX_NAME' => 'test',
                                            'COMPUTERNAME' => 'VM-TEST',
                                            'EMAIL' => 'wagner@e-prepag.com.br',
                                            'URL' => '/TEST.E-PREPAG.COM/'
                                            ),
                 $BASE_IP.'61' => array (
                                            'PREFIX_NAME' => 'linuxdev',
                                            'COMPUTERNAME' => 'WWW2-DEV',
                                            'EMAIL' => 'admin@e-prepag.com',
                                            'URL' => '/LINUXDEV.E-PREPAG.COM/'
                                             ),
                 $BASE_IP.'65' => array (
                                            'PREFIX_NAME' => 'linuxtest',
                                            'COMPUTERNAME' => 'WWW2-HOMOLOGACAO',
                                            'EMAIL' => 'wagner@e-prepag.com.br',
                                            'URL' => '/LINUXTEST.E-PREPAG.COM/'
                                            ),
                 $BASE_IP.'91' => array (
                                            'PREFIX_NAME' => 'linuxestagiario',
                                            'COMPUTERNAME' => 'WWW2-ESTAGIARIO',
                                            'EMAIL' => 'wagner@e-prepag.com.br',
                                            'URL' => '/LINUXTEST.E-PREPAG.COM/'
                                            ),
                // Para conseguir acessar o servidor de homologação fora da rede E-prepag necessário o bloco abaixo 
                 'eprepag.ddns.net' => array (
                                            'PREFIX_NAME' => 'eprepag',
                                            'COMPUTERNAME' => 'VM-TEST',
                                            'EMAIL' => 'wagner@e-prepag.com.br',
                                            'URL' => '/eprepag.ddns.net/'
                                            ),
                //Para conseguir acessar o servidor de sandbox
                'sandbox.e-prepag.com.br' => array(
                                            'PREFIX_NAME' => 'eprepagsandbox',
                                            'COMPUTERNAME' => 'VM-SANDBOX',
                                            'EMAIL' => 'wagner@e-prepag.com.br',
                                            'URL' => '/sandbox.e-prepag.com.br/'
                                            ),
                );
                
// Bloco que bloqueia o acesso na homologação a partir de requisição externa
if(isset($GLOBALS['_SERVER']['SERVER_NAME']) && strtoupper(@$GLOBALS['_SERVER']['SERVER_NAME']) == 'EPREPAG.DDNS.NET') {
    die('Access Denied!');
}

$search_domain = array_key_exists(@$GLOBALS['_SERVER']['SERVER_NAME'], $ENV_LIST) ? $ENV_LIST[$GLOBALS['_SERVER']['SERVER_NAME']]['URL'] : null;

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
    $mensagem = date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . "\nExistiu tentativa de duplicação de FUNÇÃO neste ponto. (FUNÇÃO: checkIP)\n";
    //Grava mensagem no arquivo
    if ($handle = fopen($file, 'a+')) {
            fwrite($handle, $mensagem);
            fclose($handle);
    } 
}//end else do if (!function_exists('checkIP'))
?>
