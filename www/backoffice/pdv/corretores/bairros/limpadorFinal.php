<?php

die("Stop limpador Final<br>");
require_once '../../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";

echo "Start: ".date("Y-M-d H:i:s")."<br>";
	$time_start_stats = getmicrotime();


$translation = array (
			'Km 18' => 'Quilômetro Dezoito',
			' De ' => ' de ',
			' Da ' => ' da ',
			' Do ' => ' do ',
			' Dos ' => ' dos ',
			' Das ' => ' das ',
			'são ' => 'São ',
			'Sao ' => 'São ',
			'Sto' => 'Santo ',
			'S.' => 'São',
			' v ' => ' V ',
			' vi ' => ' VI ',
			' Iii' => ' III ',
			' Ii' => ' II ',
			' Xv' => ' XV ',
			' xi' => ' XI ',
			' Xi ' => ' XI ',
			' Xii ' => ' XII ',
			' xii ' => ' XII ',
			' Vi ' => ' VI',
			' i ' => ' I ',
			' Jardin ' => 'Jardim ',
			'13' => 'Treze',
			'14' => 'Quatorze',
			'31' => 'Trinta e Um',
			'jd' => 'Jardim',
			'Jd.' => 'Jardim',
			'Jd ' => 'Jardim ',
			'J.' => 'Jardim ',
			'J ' => 'Jardim ',
			'Jdm ' => 'Jardim ',
			'centro' => 'Centro',
			'Cenro' => 'Centro',
			'Cemtro' => 'Centro',
			'Cenrtro' => 'Centro',
			'Alt ' => 'Alto ',
			'Altoo ' => 'Alto ',
			'Vl.' => 'Vila',
			'Vl ' => 'Vila',
			'V.' => 'Vila',
			'Pq' => 'Parque',
			'P.' => 'Parque',
			'Baln.' => 'Balneário',
			'Ã“' => 'Ó',
			'D. ' => 'Don ',
			' 2' => ' II',
			' 1' => ' I',
			' 10 ' => ' Dez ',
			'18 ' => 'Dezoito ',
			'Jardim Paulo Vi' => 'Jardim Paulo VI',
			'Olho D\'Ã¡gua' => 'Olho D\'Água',
			'V Erna' => 'Vila Erna',
			'Dix-sept Rosado' => 'Dix-Sept Rosado',
			'Grajau' => 'Grajaú',
			' Iv ' => ' IV ',
			' Petropoles' => ' Petrópolis',
			'Jundiai' => 'Jundiaí',
			'Jn' => 'Júnior',
			'N. Sra' => 'Nossa Senhora',
			'Sra.' => 'Senhora',
			'Sr ' => 'Senhor ',
			'Sra ' => 'Senhora ',
			'Betania' => 'Betânia',
			'Betânea' => 'Betânia',
			'Petropolis' => 'Petrópolis',
			'Pacoencia' => 'Paciência',
			'Padremiguel' => 'Padre Miguel',
			'Parque.' => 'Parque',
			' I0' => ' Dez',
			'Pau Miudo' => 'Pau Miúdo',
			'Res Aquario' => 'Residêncial Aquário',
			'Samanau' => 'Samanaú',
			'Eulalia' => 'Eulália',
			'Ifigênia' => 'Efigênia',
			'Eulália - Jacarati' => 'Eulália',
			'Viola' => 'Vila',
			'Joao' => 'João',
			'Jose' => 'José',
			'Slvador' => 'Salvador',
			'V São Cristovao' => 'Vila São Cristóvão',
			'Vila Brasilia' => 'Vila Brasília',
			'Carrao' => 'Carrão',
			'Vila Iorio - Sousas' => 'Vila Iorio',
			'Vila Iara - Centro' => 'Vila Iara',
			'Jacui' => 'Jacuí',
			'Operaria' => 'Operária',
			'Pompeia' => 'Pompéia',
			'Quitauna' => 'Quitaúna',
			'Luiz' => 'Luíz',
			'Vila Sônia - Butantã' => 'Vila Sônia',
			'Virginia' => 'Virgínia',
			'Araujo' => 'Araújo',
			'Ã¡' => 'Á'
			);

//foreach($translation as $key => $val) {
//	echo "'".$key."' => '".$val."',<br>";
//}
foreach($translation as $key => $val) {
	$errado[] = $key;
	$certo[] = $val;
}

//die("Stop Limpador Final<br>");

$ps_query = "SELECT distinct ug_bairro FROM dist_usuarios_games ";
//pg_send_query($conex,$ps_query);
//$res0 = pg_get_result($conex);
$res0 = SQLexecuteQuery($ps_query);
$total = pg_num_rows($res0);

//* comentado para o update
/*$ps_query = "SELECT distinct ug_bairro,ug_cidade, count (ug_bairro) as total FROM dist_usuarios_games where sem_acentos(ug_bairro) ~ sem_acentos('[A-z] +') group by ug_bairro,ug_cidade order by ug_bairro"; */

$ps_query = "SELECT distinct ug_bairro FROM dist_usuarios_games ";

echo $ps_query."<br>";
/// todas as lan que estiverem nesse bairro

//pg_send_query($conex,$ps_query);
//$res1 = pg_get_result($conex);
$res1 = SQLexecuteQuery($ps_query);

$ok = pg_num_rows($res1);

echo "Total: $ok - $total <br>";
$iloop = 1;
while ($info = pg_fetch_array($res1)) {

	$upped = ucwords($info['ug_bairro']);
	$mudar = str_replace($errado,$certo,$upped);

	echo ($iloop++)." - Original : <font color='#FF0000'>".$info['ug_bairro']." </font> Corrigido : <font color='#66CC00'>".$mudar."</font>".((in_array($info['ug_bairro'], $errado))?" <font color='#000099'>ENCONTRADO</font>":"")."<br>";

	if($info['ug_bairro'] && in_array($info['ug_bairro'],$errado)) {
		$query = "update dist_usuarios_games set ug_bairro = '".str_replace("'","''", $mudar)."' where ug_bairro = '".str_replace("'","''",$info['ug_bairro'])."' ;";
		echo "<font color='#000099'>".$query."</font><br>";

		//pg_send_query($conex,$query);
//		$res = SQLexecuteQuery($query);
	} else {
		echo "<font color='#000099'>".$info['ug_bairro']." está certo</font><br>";
	}
}

echo "End: ".date("Y-M-d H:i:s")."<br>";
echo "Processamento em ". number_format(getmicrotime() - $time_start_stats, 2, '.', '.') ." s.<br>";

?>
