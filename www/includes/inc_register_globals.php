<?php
register_globals();

function register_globals($order = 'gp')
{
    // Log de entradas
    $caller = $_SERVER['SCRIPT_FILENAME'];
    file_put_contents(
        '/www/arquivos_gerados/logs/php_register_globals.log',
        date('[Y-m-d H:i:s]') . " $caller GET=" . json_encode($_GET) . ' POST=' . json_encode($_POST) . "\n",
        FILE_APPEND
    );

    // Subfunчуo
    if (!function_exists('register_global_array'))
    {
        function register_global_array(array $superglobal)
        {
            foreach ($superglobal as $varname => $value)
            {
                // Nуo sobrescreve variсveis jс existentes
                if (!array_key_exists($varname, $GLOBALS))
                {
                    $GLOBALS[$varname] = $value;
                }
            }
        }
    }

    // Ordem g p
    $order = explode("\r\n", trim(chunk_split($order, 1)));
    foreach ($order as $k)
    {
        switch(strtolower($k))
        {
            case 'g': register_global_array($_GET);  break;
            case 'p': register_global_array($_POST); break;
        }
    }
}


/**
 * Undo register_globals
 * @author Ruquay K Calloway
 * @link hxxp://www.php.net/manual/en/security.globals.php#82213
 */
function unregister_globals() {
    if (ini_get(register_globals)) {
        $array = array('_REQUEST', '_SESSION', '_SERVER', '_ENV', '_FILES');
        foreach ($array as $value) {
            foreach ($GLOBALS[$value] as $key => $var) {
                if ($var === $GLOBALS[$key]) {
                    unset($GLOBALS[$key]);
                }
            }
        }
    }
}
?>