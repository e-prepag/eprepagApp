<?php
require_once __DIR__ . "/../../../../includes/pdv_encoding.php";
require_once '../../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
// f = numero  de cidades que foram selecionadas para modificar
//$f = str_replace("'","\'",pdv_utf8_to_iso( $_POST['f']));
$f = (int)pdv_utf8_to_iso(isset($_POST['f']) ? $_POST['f'] : '');
// novo_nome é o nome que irá atualizar os marcados
//$novo_nome = str_replace("'","\'",pdv_utf8_to_iso( $_POST['word']));
$novo_nome = pdv_utf8_to_iso(isset($_POST['word']) ? $_POST['word'] : '');

// contador da quantidade de cidades diferente
$j = 0;
// contador que numera o vetor para devido alguna problemas de contar apartir de 0
$r = 1;
$varia = array();

// captura o estado para segurança
//$estado = str_replace("'","\'",pdv_utf8_to_iso( $_POST['c_estado']));
$estado = pdv_utf8_to_iso(isset($_POST['c_estado']) ? $_POST['c_estado'] : '');


// montando a query
$query = "update dist_usuarios_games set ug_cidade = $1 where ";
$params = array($novo_nome);
$msg = " As Cidades :";

//echo "query: $query<br>";
//die("Stop1222");


// testa se chegou ou não alguma para modificar
while ( $j <= $f ) {

	///captura o cidade selecionado 
	if (isset($_POST['varia' . $j]) && $_POST['varia' . $j] != "") {

		//atribui ao vetor//
//		$varia[$r] = str_replace("'","\'",pdv_utf8_to_iso($_POST['varia'.$j]));
		$varia[$r] = pdv_utf8_to_iso($_POST['varia'.$j]);
echo "&nbsp;&nbsp; = 'varia".$j . "' -> '".$_POST['varia'.$j]."' -> '". $varia[$r]."'<br>";
		$msg .="<br> *".$varia[$r]."<br>";
		$r++;
			//// montagem da query 
			/// se não for a ultima variação do nome selecionado então adiciona or
	}
$j++;
}

$msg .= "Foram modificados para ".$novo_nome." com sucesso<br>";
echo $msg;
/// size coleta o tamanho do vetor e o menos 1 é para dizer que contou do 0 ou seja 0 é 1 ;
$size = (is_countable($varia) ? count($varia) : 0);


for ($varredor = 1; $varredor <= $size ; $varredor++) {

	if ($varredor < $size ) {

			$params[] = $varia[$varredor];
			$placeholder = "$" . count($params);
			$query .= " ug_cidade = " . $placeholder . " OR";

	} else { /// caso contrário adiciona o AND para concatenar com a cidade

			$params[] = $varia[$varredor];
			$placeholder = "$" . count($params);
			$query .= " ug_cidade = " . $placeholder . " AND";

	}//fim if

}// fim for
$params[] = $estado;
$estadoPlaceholder = "$" . count($params);
$query .= " ug_estado = " . $estadoPlaceholder . " ;";
//echo "QUERY: ".$query."<br>";


////////---------- ATIVAR AQUI PARA FUNCIONAR ------------/////////
$res = SQLexecuteQueryParams($query, $params);

//////////------------------------------------------------//////////
//echo $query;





			/*
	
			}*/




//for (
//update dist_usuarios_games set ug_cidade = '$cidade_escolhida' where ug_cidade = '$val1' or ug_cidade = '$val2' or ug_cidade = '$val3'


?>
<script>
var ValorSelecionadoEstado = '<?=$estado?>';
	
	$("#cidade").load("selectcidades.php","estado="+ValorSelecionadoEstado);
</script>