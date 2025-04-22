<?php 
	header("Content-Type: text/html; charset=ISO-8859-1",true);
include "../includes/classPrincipal.php";
include "../includes/function_time.php";
$run_silently = 1;
include "../../../incs/topo_bko.php";
include "../../financeiro/corte/corte_constantes.php";
include "inc_balanco.php";

	set_time_limit(30000) ;

die("Stop");
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

//$t_ini = microtime(true);
	$time_start_stats = getmicrotime();


echo "Now: ".date("Y-m-d H:i:s")."<br>";
// Calcula total de lans ativas/inativas
$total_query = "select ug_id from dist_usuarios_games order by ug_id";
$total_rs_query = SQLexecuteQuery($total_query);
$total_row = pg_num_rows($total_rs_query);

		$i_balanco = 1;
//		$n_balanco = 164;
//		echo "<hr>Max de $n_balanco registros<br>\n";
		echo "<br>Lidos do BD $total_row registros de LHs<br><hr>\n";


// Fabio### - 17
// Mandrake - 2644
// where ug_id=17 or ug_id = 2644 
$query = "select ug_id from dist_usuarios_games order by ug_id;";	// where ug_ativo = 1 // 

print "<hr>".$query."<hr>";

$rs_query = SQLexecuteQuery($query);

$lh_atual = 0;
while ($balanco_info = pg_fetch_array($rs_query)) {

	$msg = "";

	echo "<hr color='red'>".(++$lh_atual) ." de ".$total_row."<br>\n";

	$usuarioId = $balanco_info['ug_id'];
echo "usuarioId: $usuarioId<br>";
echo "MSG: $msg<br>";

	$usuario_id = $usuarioId;

	$objUsuarioGames = UsuarioGames::getUsuarioGamesById($usuario_id);

	//Recupera as vendas//////////////////////


			////// data do inicio do balanco 
	$data = $BALANCO_DATA_ABERTURA;
			
			////// 
	$fim_data = strtotime($BALANCO_DATA_ABERTURA);
			//// setei o intervalo para 10
	// $n_dias = 10;/////

			/////////////////// nada aqui //////////
	$data_balanco = data_mais_n($data, $n_dias);


	/// peguei a data de hoje
	$hoje = date('Y-m-d');
	
	//  0123456789
	// '2008-01-01' -> '01-01-2008'
	$data_br = substr($data,8,2)."-".substr($data,5,2)."-".substr($data,0,4);
	$hoje_br = date('d-m-Y');
	
	// medi a quantidade de dias entre a abertura de balanco até hoje
	$qtddias = qtde_dias($data_br,$hoje_br);
echo "qtde_dias('$data_br','$hoje_br') = $qtddias<br>";


	// removi a diferença das datas (quantos dias estão acima do balanco mais recente)
	$dif_dias = $qtddias % $n_dias;

	////////////////////////////////////////////
	////////////////////////////////////////////
	
	// puxo a quantidade de dias necessarios para achar ultimo balanco mais recente
	// $data_ajustada = $qtddias - $dif_dias;
	
	//// atribui a data de hoje para achar a data do ultimo balanco

	$hoje = formata_data_ts($hoje,0,true,false);

	$hoje = data_menos_n($hoje,$dif_dias);
	$data_bal = data_menos_n($hoje,$dif_dias);
	$hoje = formata_data($hoje,1);

	$data_balanco = strtotime($hoje);
	$data_limit = $objUsuarioGames->getDataInclusao();
		
	$data_limit = formata_data($data_limit,1);
	
	$data_limit = strtotime($data_limit);

	$t_lan = $risco = $objUsuarioGames->getRiscoClassif();
		
	$saldo = $objUsuarioGames->getPerfilSaldo();
	$limite = $objUsuarioGames->getPerfilLimite();

	$saldo_balanco = $saldo;

echo "&nbsp;&nbsp; = LH vi processar: ID: $usuario_id, Nome: '".(($t_lan==1)?$objUsuarioGames->getNome():$objUsuarioGames->getNomeFantasia())."', Saldo: $saldo, limite: $limite, ativa: ".(($objUsuarioGames->getAtivo()==1)?"<font color='blue'>ATIVA</font>":"<font color='red'>Inativa</font>")."<br>";


	$data_bal = $BALANCO_DATA_ABERTURA;	
// ********************* GRAVA REGISTROS ***********************************************************//

	$data_bal_grava = $data_bal;
	$data_bal_grava .= " 23:59:59";

	$query_insert = "insert into dist_balancos (db_ug_id, db_valor_balanco, db_tipo_lan, db_data_balanco, db_limite, db_saldo, db_balanco_historico) values (";

	$query_insert .= SQLaddFields($usuarioId,"").", ";

	if ( $t_lan == '1' ) {
		$query_insert .= SQLaddFields($limite,"").", ";
	} else {
		$query_insert .= SQLaddFields($saldo,"").", ";
	}

	$query_insert .= SQLaddFields($t_lan,"").", ";
	$query_insert .= "'".SQLaddFields($data_bal_grava,"")."', ";
	$query_insert .= SQLaddFields($limite,"").", ";
	$query_insert .= SQLaddFields($saldo,"").", ";
	$query_insert .= " 1) ";	// aqui todos são registros historicos
// print $t_lan;

	echo "<br>$i_balanco (".(( $t_lan == 1)?"Pos":"Pre").")<b>".$query_insert."</b><br>";
	
	SQLexecuteQuery($query_insert);
	
	$i_balanco++;
		
// ***************************************************************************************************//

echo " = = MSG $row<br>\n";

} // fim do while que varre todas as lans 


echo "Processamento primeira parte em ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.') ." s.<br>Média de ".number_format((getmicrotime() - $time_start_stats)/$lh_atual, 2, '.', '.') ." s/lh.";



?>

