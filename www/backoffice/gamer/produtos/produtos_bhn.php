<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
header("Content-Type: text/html; charset=UTF-8",true);

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto.'bhn/egift/config.inc.bhn.egift.php';
require_once $raiz_do_projeto.'class/util/Util.class.php';

//Alterando o limite do tempod e processamento
set_time_limit(360);
ini_set('max_execution_time', 360);
ini_set('default_socket_timeout', 360);

//Lento lista de catalogos
$allCatalogs = new classAllCatalogs();
$allCatalogs -> Req_EfetuaConsultaRegistro($lista_resposta);

$vetorProdutos = array();

$contador = 1;
    
foreach($lista_resposta->results as $key => $value) {
    
    $vars = $allCatalogs -> readVarsRestful($value->entityId);
    //echo "<pre>".print_r($vars, true)."</pre>";

    //Lento lista de produtos
    $readProductsCatalogs = new classReadProductsCatalogs();
    $readProductsCatalogs -> Req_EfetuaConsultaRegistro($lista_resposta_produtos, $vars[BHN_EGIFT_PRODUCTCATALOGS]);
    
    foreach($lista_resposta_produtos->details->productIds as $chave => $valor) {
        $products = $readProductsCatalogs -> readVarsRestful($valor);
        //echo "<pre>".print_r($products, true)."</pre>";
        
        //Lendo detalhes do Produto
        $datailsProduct = new classReadProduct();
        $datailsProduct -> Req_EfetuaConsultaRegistro($lista_resposta_produtos_detalhes, $products[BHN_EGIFT_PRODUCT]);
        
        echo "<hr>"."Produto ".$contador."<hr>";
        echo $lista_resposta_produtos_detalhes->summary->productName. "<br>";
        //echo "<pre>".print_r($lista_resposta_produtos_detalhes, true)."</pre>";
        $teste_decode = json_encode($lista_resposta_produtos_detalhes,JSON_UNESCAPED_UNICODE);
        $teste_decode = utf8_decode($teste_decode);
        echo "<pre>".prettyPrint($teste_decode)."</pre>";
        $contador++;
        
    }//end foreach
    
}//end foreach

echo PHP_EOL.str_repeat("_", 80) . PHP_EOL. "<br>Final de execucao em: ". date('Y-m-d H:i:s'). PHP_EOL. "<br>Elapsed time : ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.').PHP_EOL;

require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";

function prettyPrint( $json ) {
    $result = '';
    $level = 0;
    $in_quotes = false;
    $in_escape = false;
    $ends_line_level = NULL;
    $json_length = strlen( $json );

    for( $i = 0; $i < $json_length; $i++ ) {
        $char = $json[$i];
        $new_line_level = NULL;
        $post = "";
        if( $ends_line_level !== NULL ) {
            $new_line_level = $ends_line_level;
            $ends_line_level = NULL;
        }
        if ( $in_escape ) {
            $in_escape = false;
        } else if( $char === '"' ) {
            $in_quotes = !$in_quotes;
        } else if( ! $in_quotes ) {
            switch( $char ) {
                case '}': case ']':
                    $level--;
                    $ends_line_level = NULL;
                    $new_line_level = $level;
                    break;

                case '{': case '[':
                    $level++;
                case ',':
                    $ends_line_level = $level;
                    break;

                case ':':
                    $post = " ";
                    break;

                case " ": case "\t": case "\n": case "\r":
                    $char = "";
                    $ends_line_level = $new_line_level;
                    $new_line_level = NULL;
                    break;
            }
        } else if ( $char === '\\' ) {
            $in_escape = true;
        }
        if( $new_line_level !== NULL ) {
            $result .= "\n".str_repeat( "\t", $new_line_level );
        }
        $result .= $char.$post;
    }

    return str_replace("\\n","<br>",$result);
}

?>
