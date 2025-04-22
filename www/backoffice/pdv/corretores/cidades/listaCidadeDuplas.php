<?php
die("Stop");

set_time_limit(100);
require_once '../../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";

echo "Start: ".date("Y-M-d H:i:s")."<br>";

// Esta lista guarda os acentos da Língua portuguesa Brasileira
$lista_acento = array('á','é','í','ó','ú','à','è','ì','ò','ù','ã','õ','â','ê','î','ô','ô','ä','ë','ï','ö','ü','ç','Á','É','Í','Ó','Ú','À','È','Ì','Ò','Ù','Ã','Õ','Â','Ê','Î','Ô','Û','Ä','Ë','Ï','Ö','Ü');


// seleção de cidades para contagem do total de cidades
$ps_query = "SELECT distinct ug_cidade FROM dist_usuarios_games ";
//pg_send_query($conex,$ps_query);
//$res0 = pg_get_result($conex);
$res0 = SQLexecuteQuery($ps_query);



$total_de_cidades = pg_num_rows($res0);

// seleção de cidades ordenada em ordem decrescente para acelerar 
$ps_query = "SELECT distinct ug_cidade, ug_estado FROM dist_usuarios_games order by ug_cidade desc";

//echo $ps_query;
/// todas as lan que estiverem nesse bairro

//pg_send_query($conex,$ps_query);
//$res1 = pg_get_result($conex);
$res1 = SQLexecuteQuery($ps_query);

$total_de_cidades_com_estado = pg_num_rows($res1);


// conferencia 
echo "total_de_cidades: $total_de_cidades - total_de_cidades_com_estado: $total_de_cidades_com_estado <br>";
$iloop = 1;
while ($info = pg_fetch_array($res1)) {


	// esta query seleciona cidades e remove a acentuação delas e compara se a palavra ficar igual a antiga com acentos removidos significa que são
	//cidades com nomes semelhantes e acentuação errada.
		$queryB = "SELECT distinct ug_cidade from dist_usuarios_games where sem_acentos(ug_cidade) like sem_acentos('".$info['ug_cidade']."') and ug_cidade != '".$info['ug_cidade']."' and ug_estado = '".$info['ug_estado']."' ";

//		echo "queryB: ".$queryB."<br>";
		//pg_send_query($conex,$queryB);
		//$res2 = pg_get_result($conex);
		$res2 = SQLexecuteQuery($queryB);

			echo "&nbsp;&nbsp;&nbsp;Total duplos encontrados: ".pg_num_rows($res2)."<br>";
			if(pg_num_rows($res2)>0) {
				echo "queryB: ".$queryB."<br>";
			}

			//Extrai os registros para o vetor info 2 : informações 2 
			while ($info2 = pg_fetch_array($res2)) {
			
				// armazenando em uma variavel para tratamento
				$checar_acento_antigo = $info['ug_cidade'];
				$checar_acento_novo = $info2['ug_cidade'];
					

				// verifica se checar_acento tem algum acento da lista de acentos
				// strpbrk retorna a letra que foi encontrada.
				$tem_acento_antigo = strpbrk($checar_acento_antigo,$lista_acento);
				$tem_acento_novo = strpbrk($checar_acento_novo,$lista_acento);


				//só haverá mudanças se a primeira palavra tiver acento e a segunda não tiver acento, pois quando a varredura chegar na segunda
				// ela irá novamente verificar cada registro se tem acento da palavra semelhante e então o primeiro irá ter e o segundo não e ai será 
				// corrigido de maneira segura.

				// se tiver ou seja se for diferente de falso
				if ($tem_acento_antigo !== false ) {
					// e se o acento no
					if ($tem_acento_novo === false) {

						echo "palavras -> A:".$info2['ug_cidade']." B: ".$info['ug_cidade']."<br>";
						// queryJ constrói a queri que vai corrigir 
						$query_atualizar = "update dist_usuarios_games set ug_cidade = '".$info2['ug_cidade']."' where ug_cidade = '".$info['ug_cidade']."' and ug_estado = '".$info['ug_estado']."' ";

						echo $query_atualizar.'<br>';
						echo 'UPDATE Bloqueado!!! <br>';
				//		pg_send_query($conex,$query_atualizar);
						$res = SQLexecuteQuery($query_atualizar);


					} else {

						echo "palavras -> A:".$info2['ug_cidade']." B: ".$info['ug_cidade']."<br>";
						echo "Não pode escolher acento<br>";

					}// fim do if 2
				} else {
						echo "Não é ele mesmo<br>";

				}//fim do if 1

				echo "Ciclo Interno (".($iloop++)."): ".date("Y-M-d H:i:s")."<br>";
		

			}//fim do while 2

}// fim do while 1

echo "End: ".date("Y-M-d H:i:s")."<br>";

echo "<a href='/index.php'>voltar</a>"
?>