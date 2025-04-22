<?php
die("Stop");
require_once '../../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";

echo "Start: ".date("Y-M-d H:i:s")."<br>";
	$time_start_stats = getmicrotime();

/*
$errado = array("Km 18"," De "," Da "," Do "," Dos "," Das ","são ", "Sao " ,"Sto","S." , " v "," vi "," Iii"," Ii"," Xv"," xi"," Xi "," Xii "," xii "," Vi "," i "," Jardin","13","14","31","jd","Jd.","Jd ","J.","J ","Jdm ","centro","Cenro","Cemtro","Cenrtro","Alt ","Altoo","Alto","Vl","Vl.","V.","Pq","P.","Baln.","Ã“","D."," 2"," 1"," 10 ","18","Jardim Paulo Vi","Olho D'Ã¡gua","V Erna","Dix-sept Rosado","Grajau"," Iv "," Petropoles","Jundiai","Jn","N. Sra","Sra.","Sr","Sra","Betania","Betânea","Petropolis","Pacoencia","Padremiguel","Parque.","I0","Pau Miudo","Res Aquario","Aquario","Samanau","Eulalia","Ifigênia","Eulália - Jacarati","Viola","Joao","Jose","Slvador","V São Cristovao","Vila Brasilia","Carrao","Vila Iorio - Sousas","Vila Iara - Centro","Jacui","Operaria","Pompeia","Quitauna","Luiz","Vila Sônia - Butantã","Virginia","Araujo","Osorio","Paraiso","Paranagua","Petropoles","Pelotas","Caiua","Ã¡");

$certo = array("Quilômetro Dezoito"," de "," da "," do "," dos "," das ", "São ","São ","Santo ","São"," V "," VI "," III "," II "," XV "," XI "," XI "," XII "," XII ", " VI"," I ", "Jardim ","Treze","Quatorze","Trinta e Um","Jardim ", "Jardim ","Jardim ","Jardim ","Jardim ","Jardim ","Centro","Centro", "Centro","Centro","Alto","Alto","Alto","Vila","Vila","Vila", "Parque","Parque","Balneário","Ó","Don "," II","I"," Dez ","Dezoito"," Jardim Paulo VI", "Olho D'Água", "Vila Erna","Dix-Sept Rosado","Grajaú"," IV ", " Petrópolis","Jundiaí","Júnior","Nossa Senhora ","Senhora","Senhor", "Senhora","Betânia", "Betânia", "Petrópolis","Paciência", "Padre Miguel","Parque"," Dez","Pau Miúdo","Residêncial Aquário","Aquario", "Samanaú","Eulália", "Efigênia", "Eulália", "Vila","João", "José", "Salvador","Vila São Cristóvão","Vila Brasília","Carrão","Vila Iorio","Vila Iara","Jacuí", "Operária", "Pompéia","Quitaúna","Luíz","Vila Sônia","Virgínia", "Araújo","Osório","Paraíso","Paranaguá","Petrópolis","Pelótas","Caiuá","Á");
*/
//for($i=0;$i<count($errado);$i++) {
//	echo "'".$errado[$i]."' => '".$certo[$i]."',<br>";
//}

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
			' Jardin' => 'Jardim ',
			'13' => 'Treze',
			'14' => 'Quatorze',
			'31' => 'Trinta e Um',
			'jd' => 'Jardim ',
			'Jd.' => 'Jardim ',
			'Jd ' => 'Jardim ',
			'J.' => 'Jardim ',
			'J ' => 'Jardim ',
			'Jdm ' => 'Jardim ',
			'centro' => 'Centro',
			'Cenro' => 'Centro',
			'Cemtro' => 'Centro',
			'Cenrtro' => 'Centro',
			'Alt ' => 'Alto',
			'Altoo' => 'Alto',
			'Alto' => 'Alto',
			'Vl' => 'Vila',
			'Vl.' => 'Vila',
			'V.' => 'Vila',
			'Pq' => 'Parque',
			'P.' => 'Parque',
			'Baln.' => 'Balneário',
			'Ã“' => 'Ó',
			'D.' => 'Don ',
			' 2' => ' II',
			' 1' => 'I',
			' 10 ' => ' Dez ',
			'18' => 'Dezoito',
			'Jardim Paulo Vi' => ' Jardim Paulo VI',
			'Olho D\'Ã¡gua' => 'Olho D\'Água',
			'V Erna' => 'Vila Erna',
			'Dix-sept Rosado' => 'Dix-Sept Rosado',
			'Grajau' => 'Grajaú',
			' Iv ' => ' IV ',
			' Petropoles' => ' Petrópolis',
			'Jundiai' => 'Jundiaí',
			'Jn' => 'Júnior',
			'N. Sra' => 'Nossa Senhora ',
			'Sra.' => 'Senhora',
			'Sr' => 'Senhor',
			'Sra' => 'Senhora',
			'Betania' => 'Betânia',
			'Betânea' => 'Betânia',
			'Petropolis' => 'Petrópolis',
			'Pacoencia' => 'Paciência',
			'Padremiguel' => 'Padre Miguel',
			'Parque.' => 'Parque',
			'I0' => ' Dez',
			'Pau Miudo' => 'Pau Miúdo',
			'Res Aquario' => 'Residêncial Aquário',
			'Aquario' => 'Aquario',
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
			'Osorio' => 'Osório',
			'Paraiso' => 'Paraíso',
			'Paranagua' => 'Paranaguá',
			'Petropoles' => 'Petrópolis',
			'Pelotas' => 'Pelótas',
			'Caiua' => 'Caiuá',
			'Ã¡' => 'Á'
			);

//foreach($translation as $key => $val) {
//	echo "'".$key."' => '".$val."',<br>";
//}
foreach($translation as $key => $val) {
	$errado[] = $key;
	$certo[] = $val;
}
//die("Stop<br>");

$ps_query = "SELECT distinct ug_cidade FROM dist_usuarios_games ";
//pg_send_query($conex,$ps_query);
//$res0 = pg_get_result($conex);
$res0 = SQLexecuteQuery($ps_query);

$total = pg_num_rows($res0);

//* comentado para o update
/*$ps_query = "SELECT distinct ug_bairro,ug_cidade, count (ug_bairro) as total FROM dist_usuarios_games where sem_acentos(ug_bairro) ~ sem_acentos('[A-z] +') group by ug_bairro,ug_cidade order by ug_bairro"; */

$ps_query = "SELECT distinct ug_cidade FROM dist_usuarios_games ";

//echo $ps_query."<br>";
/// todas as lan que estiverem nesse bairro

//pg_send_query($conex,$ps_query);
//$res1 = pg_get_result($conex);
$res1 = SQLexecuteQuery($ps_query);

$ok = pg_num_rows($res1);

echo "Total: $ok - $total <br>";
$iloop = 1;
while ($info = pg_fetch_array($res1)) {

	$upped = ucwords($info['ug_cidade']);
	$mudar = str_replace($errado,$certo,$upped);

	echo ($iloop++)." - Original : <font color='#FF0000'>".$info['ug_cidade']." </font> Corrigido : <font color='#66CC00'>".$mudar."</font>".((in_array($info['ug_cidade'],$errado))?" <font color='#000099'>ENCONTRADO</font>":"")."<br>";

	if(in_array($info['ug_cidade'],$errado)) {
		$query = "update dist_usuarios_games set ug_cidade = '$mudar' where ug_cidade = '".$info['ug_cidade']."' ;";
		echo "<font color='#000099'>".$query."</font><br>";

//		pg_send_query($conex,$query);
//		$res = SQLexecuteQuery($query);

	}

}

echo "End: ".date("Y-M-d H:i:s")."<br>";
echo "Processamento em ". number_format(getmicrotime() - $time_start_stats, 2, '.', '.') ." s.<br>";

?>
