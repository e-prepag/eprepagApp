<?php
die("Stop");
require_once '../../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";

echo "Start: ".date("Y-M-d H:i:s")."<br>";
	$time_start_stats = getmicrotime();

/*
$errado = array("Km 18"," De "," Da "," Do "," Dos "," Das ","s�o ", "Sao " ,"Sto","S." , " v "," vi "," Iii"," Ii"," Xv"," xi"," Xi "," Xii "," xii "," Vi "," i "," Jardin","13","14","31","jd","Jd.","Jd ","J.","J ","Jdm ","centro","Cenro","Cemtro","Cenrtro","Alt ","Altoo","Alto","Vl","Vl.","V.","Pq","P.","Baln.","Ó","D."," 2"," 1"," 10 ","18","Jardim Paulo Vi","Olho D'água","V Erna","Dix-sept Rosado","Grajau"," Iv "," Petropoles","Jundiai","Jn","N. Sra","Sra.","Sr","Sra","Betania","Bet�nea","Petropolis","Pacoencia","Padremiguel","Parque.","I0","Pau Miudo","Res Aquario","Aquario","Samanau","Eulalia","Ifig�nia","Eul�lia - Jacarati","Viola","Joao","Jose","Slvador","V S�o Cristovao","Vila Brasilia","Carrao","Vila Iorio - Sousas","Vila Iara - Centro","Jacui","Operaria","Pompeia","Quitauna","Luiz","Vila S�nia - Butant�","Virginia","Araujo","Osorio","Paraiso","Paranagua","Petropoles","Pelotas","Caiua","á");

$certo = array("Quil�metro Dezoito"," de "," da "," do "," dos "," das ", "S�o ","S�o ","Santo ","S�o"," V "," VI "," III "," II "," XV "," XI "," XI "," XII "," XII ", " VI"," I ", "Jardim ","Treze","Quatorze","Trinta e Um","Jardim ", "Jardim ","Jardim ","Jardim ","Jardim ","Jardim ","Centro","Centro", "Centro","Centro","Alto","Alto","Alto","Vila","Vila","Vila", "Parque","Parque","Balne�rio","�","Don "," II","I"," Dez ","Dezoito"," Jardim Paulo VI", "Olho D'�gua", "Vila Erna","Dix-Sept Rosado","Graja�"," IV ", " Petr�polis","Jundia�","J�nior","Nossa Senhora ","Senhora","Senhor", "Senhora","Bet�nia", "Bet�nia", "Petr�polis","Paci�ncia", "Padre Miguel","Parque"," Dez","Pau Mi�do","Resid�ncial Aqu�rio","Aquario", "Samana�","Eul�lia", "Efig�nia", "Eul�lia", "Vila","Jo�o", "Jos�", "Salvador","Vila S�o Crist�v�o","Vila Bras�lia","Carr�o","Vila Iorio","Vila Iara","Jacu�", "Oper�ria", "Pomp�ia","Quita�na","Lu�z","Vila S�nia","Virg�nia", "Ara�jo","Os�rio","Para�so","Paranagu�","Petr�polis","Pel�tas","Caiu�","�");
*/
//for($i=0;$i<count($errado);$i++) {
//	echo "'".$errado[$i]."' => '".$certo[$i]."',<br>";
//}

$translation = array (
			'Km 18' => 'Quil�metro Dezoito',
			' De ' => ' de ',
			' Da ' => ' da ',
			' Do ' => ' do ',
			' Dos ' => ' dos ',
			' Das ' => ' das ',
			's�o ' => 'S�o ',
			'Sao ' => 'S�o ',
			'Sto' => 'Santo ',
			'S.' => 'S�o',
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
			'Baln.' => 'Balne�rio',
			'Ó' => '�',
			'D.' => 'Don ',
			' 2' => ' II',
			' 1' => 'I',
			' 10 ' => ' Dez ',
			'18' => 'Dezoito',
			'Jardim Paulo Vi' => ' Jardim Paulo VI',
			'Olho D\'água' => 'Olho D\'�gua',
			'V Erna' => 'Vila Erna',
			'Dix-sept Rosado' => 'Dix-Sept Rosado',
			'Grajau' => 'Graja�',
			' Iv ' => ' IV ',
			' Petropoles' => ' Petr�polis',
			'Jundiai' => 'Jundia�',
			'Jn' => 'J�nior',
			'N. Sra' => 'Nossa Senhora ',
			'Sra.' => 'Senhora',
			'Sr' => 'Senhor',
			'Sra' => 'Senhora',
			'Betania' => 'Bet�nia',
			'Bet�nea' => 'Bet�nia',
			'Petropolis' => 'Petr�polis',
			'Pacoencia' => 'Paci�ncia',
			'Padremiguel' => 'Padre Miguel',
			'Parque.' => 'Parque',
			'I0' => ' Dez',
			'Pau Miudo' => 'Pau Mi�do',
			'Res Aquario' => 'Resid�ncial Aqu�rio',
			'Aquario' => 'Aquario',
			'Samanau' => 'Samana�',
			'Eulalia' => 'Eul�lia',
			'Ifig�nia' => 'Efig�nia',
			'Eul�lia - Jacarati' => 'Eul�lia',
			'Viola' => 'Vila',
			'Joao' => 'Jo�o',
			'Jose' => 'Jos�',
			'Slvador' => 'Salvador',
			'V S�o Cristovao' => 'Vila S�o Crist�v�o',
			'Vila Brasilia' => 'Vila Bras�lia',
			'Carrao' => 'Carr�o',
			'Vila Iorio - Sousas' => 'Vila Iorio',
			'Vila Iara - Centro' => 'Vila Iara',
			'Jacui' => 'Jacu�',
			'Operaria' => 'Oper�ria',
			'Pompeia' => 'Pomp�ia',
			'Quitauna' => 'Quita�na',
			'Luiz' => 'Lu�z',
			'Vila S�nia - Butant�' => 'Vila S�nia',
			'Virginia' => 'Virg�nia',
			'Araujo' => 'Ara�jo',
			'Osorio' => 'Os�rio',
			'Paraiso' => 'Para�so',
			'Paranagua' => 'Paranagu�',
			'Petropoles' => 'Petr�polis',
			'Pelotas' => 'Pel�tas',
			'Caiua' => 'Caiu�',
			'á' => '�'
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
