<?php
require_once '../../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
// f = numero  de cidades que foram selecionadas para modificar
//$f = str_replace("'","\'",utf8_decode( $_POST['f']));
$f = utf8_decode( $_POST['f']);
// novo_nome é o nome que irá atualizar os marcados
//$novo_nome = str_replace("'","\'",utf8_decode( $_POST['word']));
$novo_nome = utf8_decode( $_POST['word']);

// contador da quantidade de cidades diferente
$j = 0;
// contador que numera o vetor para devido alguna problemas de contar apartir de 0
$r = 1;

// captura o estado para segurança
//$estado = str_replace("'","\'",utf8_decode( $_POST['c_estado']));
$estado = utf8_decode( $_POST['c_estado']);


// montando a query
$query = "update dist_usuarios_games set ug_cidade = '".str_replace("'", "''", $novo_nome)."' where ";
$msg = " As Cidades :";

//echo "query: $query<br>";
//die("Stop1222");


// testa se chegou ou não alguma para modificar
while ( $j <= $f ) {

	///captura o cidade selecionado 
	if ( $_POST['varia'.$j] != "") {

		//atribui ao vetor//
//		$varia[$r] = str_replace("'","\'",utf8_decode($_POST['varia'.$j]));
		$varia[$r] = utf8_decode($_POST['varia'.$j]);
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
$size = count ($varia);


for ($varredor = 1; $varredor <= $size ; $varredor++) {

	if ($varredor < $size ) {

			$query .= " ug_cidade = '".str_replace("'", "''", $varia[$varredor])."' OR";

	} else { /// caso contrário adiciona o AND para concatenar com a cidade

			$query .= " ug_cidade = '".str_replace("'", "''", $varia[$varredor])."' AND";

	}//fim if

}// fim for
$query .= " ug_estado = '$estado' ;";
//echo "QUERY: ".$query."<br>";


////////---------- ATIVAR AQUI PARA FUNCIONAR ------------/////////
$res = SQLexecuteQuery($query);

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