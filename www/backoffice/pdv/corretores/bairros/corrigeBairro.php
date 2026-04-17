<?php
require_once __DIR__ . "/../../../../includes/pdv_encoding.php";
require_once '../../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";

$f =  (int)pdv_utf8_to_iso(isset($_REQUEST['f']) ? $_REQUEST['f'] : '');
$word =  pdv_utf8_to_iso(isset($_REQUEST['word']) ? $_REQUEST['word'] : '');
$j = 0;
$r = 1;
$varia = array();
$estado =  isset($_REQUEST['c_estado']) ? $_REQUEST['c_estado'] : '';
$cidade =  pdv_utf8_to_iso(isset($_REQUEST['c_cidade']) ? $_REQUEST['c_cidade'] : '');

$query = "update dist_usuarios_games set ug_bairro = '$word' where ";
$msg = " Os Bairros :";

while ( $j <= $f ) {

	///captura o bairro selecionado 
	if (isset($_REQUEST['varia' . $j]) && $_REQUEST['varia' . $j] != "") {

		//atribui ao vetor//
	$varia[$r] =  pdv_utf8_to_iso($_REQUEST['varia'.$j]);
echo "&nbsp;&nbsp; = 'varia".$j . "' -> '".$_REQUEST['varia'.$j]."' -> '". $varia[$r]."'<br>";
	$msg .="<br> *".$varia[$r]."<br>";
		$r++;
			
	
	}

$j++;

}

$msg .= "Foram modificados para ".$word." com sucesso";
echo $msg;
/// size coleta o tamanho do vetor e o menos 1 é para dizer que contou do 0 ou seja 0 é 1 ;
$size = (is_countable($varia) ? count($varia) : 0);


for ($varredor = 1; $varredor <= $size ; $varredor++) {

	if ($varredor < $size ) {

			$query .= " ug_bairro = '".$varia[$varredor]."' OR";

	} else { /// caso contrário adiciona o AND para concatenar com a cidade

			$query .= " ug_bairro = '".$varia[$varredor]."' AND";

	}//fim if

}// fim for
$query .= " ug_cidade = '$cidade' and ug_estado = '$estado' ;";
echo "<br>".$query."<br>";
//echo "<br><br>".$query."<br><br> ATIVAR A QUERY";


////////---------- ATIVAR AQUI PARA FUNCIONAR ------------/////////
//if (!pg_query($conex,$query)) {

	if (!($res = SQLexecuteQuery($query))) {
		echo " falhou (213a)";	
	} else {
		echo " ok (213)"; 
	}

//////////////////////////////////////////////////////////////////////

//for (
//update dist_usuarios_games set ug_cidade = '$cidade_escolhida' where ug_cidade = '$val1' or ug_cidade = '$val2' or ug_cidade = '$val3'

?>
<script>
/// função recomecar recarrega os nome dos bairros novamente
ValorSelecionadoCidade = '<?=$cidade?>';
ValorSelecionadoEstado = '<?=$estado?>';
	$("#bairro").load("selectbairros.php","cidade="+ValorSelecionadoCidade+"&estado="+ValorSelecionadoEstado);
////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////
</script>