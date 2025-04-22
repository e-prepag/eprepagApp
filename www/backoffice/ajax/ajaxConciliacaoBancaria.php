<?php

date_default_timezone_set ( 'America/Fortaleza' ); 
(strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') or die('Chamada n�o permitida<br>Stop');
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto . "includes/gamer/constantes.php";


$_SERVER['DOCUMENT_ROOT'] = $raiz_do_projeto . "backoffice";


$return = array(
    'status' => '',
    'message' => '',
);
function verifyHttpVar($var_name){
    if ( filter_has_var(INPUT_GET, $var_name) ) {
        if ( !empty($_GET[$var_name]) ) {
            return true;
        }
    }
    return false;
}
if ( filter_has_var(INPUT_GET, 'ids') ) {
    if ( !empty($_GET['ids']) ) {
        try {
            $tipo_pagamento = (verifyHttpVar('tipo_pagamento') ? $_GET['tipo_pagamento'] : null);
            $id = $_GET['ids'];

            require_once $raiz_do_projeto . "class/classConciliacaoBancaria.php";
            $cb = new ConciliacaoBancaria();
            // se n�o importou extrato, n�o agrupa
            if ( $cb->canAgrupar($tipo_pagamento, $id) ) {
                $return['status'] = 'ok';
                echo json_encode($return);
            } else {
                $return['status'] = 'error';
                $return['message'] = utf8_encode('N�o foi poss�vel agrupar as datas. Extrato n�o importado.');
                echo json_encode($return);
            }
        } catch (Exception $e) {
            $return['status'] = 'error';
            $return['message'] = utf8_encode($e->getMessage());
            echo json_encode($return);
        }

    } else {
        $return['status'] = 'error';
        $return['message'] = utf8_encode('Valor dos Ids n�o informado.');
        echo json_encode($return);
    }
} else {
    $return['status'] = 'error';
    $return['message'] = utf8_encode('Campo ids n�o informado.');
    echo json_encode($return);
}