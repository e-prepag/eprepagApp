
<?php include "../includes/classPrincipal.php";
$raiz_do_projeto = "C:\\Sites\\E-Prepag";
	
//desenvolvimento	
if(false) $raiz_do_projeto = "D:\\Projetos\\Outros\\E-Prepag\\Sites\\Producao";
$_SERVER['DOCUMENT_ROOT'] = $raiz_do_projeto . "\\backoffice\\web";

$webstring = "http://".$_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'];
require $_SERVER['DOCUMENT_ROOT']."/incs/configuracao.inc";
require $_SERVER['DOCUMENT_ROOT']."/connections/connect.php";
include $_SERVER['DOCUMENT_ROOT']."/incs/header.php";
include $_SERVER['DOCUMENT_ROOT']."/incs/security.php";
require $_SERVER['DOCUMENT_ROOT']."/incs/functions.php";

include "../includes/function_time.php"; 
include "inc_balanco.php"; 
?>

<?php

$id_lan = $_REQUEST['n'];

$d = $_REQUEST['d'];

$t_ini = microtime(true);

echo "<h1> Simulando Registros de Balanco </h1><br><br>";


// quando publicar lembre-se de comentar essa linha pois aqui no dev os saldos e limits estão encriptados
$objEncryption = new Encryption();

// Seleciona cada lanhouse :
$query = "select ug_id,ug_perfil_saldo,ug_perfil_limite,ug_risco_classif from dist_usuarios_games where ug_ativo = '1' and ug_id = '$id_lan' ";


//// configura o intervalo de verificação de venda no caso 5 , signica que vai verificar se houve venda dentro dos ultimos 5 dias 
// $n_dias = 7;

//////////////////////////////////////////////////////////////////////

$rs_query = SQLexecuteQUERY($query);


while (pg_num_rows($rs_query) < 1) {

	$id_lan ++;

	$query = "select ug_id,ug_perfil_saldo,ug_perfil_limite,ug_risco_classif from dist_usuarios_games where ug_ativo = '1' and ug_id = '$id_lan' ";

	$rs_query = SQLexecuteQUERY($query);

}



$data_de_hoje= date("d-m-Y H:i:s");

while ($movimento_info = pg_fetch_array($rs_query)) {


	$id_lan = $movimento_info['ug_id'];

	$data_inicio_periodo = date('d-m-Y');

	$data_leitura = data_menos_n($data_de_hoje,0); // Exibição da data para leitura humana

	
	///////////////////////////////////////////////////////////////////////////////////////////////

	$ultimo_balanco_query = "select max(coalesce (db_data_balanco,'2001-01-01'::timestamp without time zone) )as data_balanco from dist_balancos where db_ug_id = '$id_lan' ";

	$rs_ultimo_balanco_query = SQLexecuteQuery($ultimo_balanco_query);
	$dados_ultimo_balanco_query = pg_fetch_array($rs_ultimo_balanco_query);
	$data_ultimo_balanco = $dados_ultimo_balanco_query['data_balanco'];

	////////////////////////////////////////////////////////////////////////////////////////////////
	/////// CARREGA BOLETO /////
	$ultimo_boleto_query = "select max (coalesce (vg_data_inclusao,'2001-01-01'::timestamp without time zone) ) from boletos_pendentes, bancos_financeiros, tb_dist_venda_games, dist_boleto_bancario_games where (bol_banco = bco_codigo) and (bol_venda_games_id=vg_id) and (bco_rpp = 1) and vg_ug_id = '$id_lan' and bol_documento LIKE '4%' and bbg_ug_id = '$id_lan' and bbg_vg_id = vg_id ";

	//echo  $ultimo_boleto_query."<br>";
	
	$rs_ultimo_boleto_query = SQLexecuteQuery($ultimo_boleto_query);
	$dados_ultimo_boleto_query = pg_fetch_array($rs_ultimo_boleto_query);
	$data_ultimo_boleto = $dados_ultimo_boleto_query['max'];

	///////////////////////////
	////////////////////////////////////////////////////////////////////////////////////////////////
	/// movimento query - indica se houve movimentanção entre pagamento de boleto ou venda de pin ///	
	$movimento_query = "select max(vg_data_inclusao) from tb_dist_venda_games vg inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
where '$data_ultimo_boleto' <= '$data_ultimo_balanco' or (vg_ug_id = '$id_lan' and vg_data_inclusao  >= '$data_ultimo_balanco' and vg.vg_ultimo_status = '5' )" ;

	//echo $movimento_query;
	

	$rs_movimento = SQLexecuteQuery($movimento_query);
	//$res_movimento = pg_num_rows($rs_movimento);
	$dados_movimento = pg_fetch_array($rs_movimento);

	
	
	$data_movimento = $dados_movimento['max'];
	//////////////////////////////////////////////////////////////////////////
	$risco = $movimento_info['ug_risco_classif'];
		
	// pos
	if ($movimento_info['ug_risco_classif'] == 1) {

		// Corrigir os decrypts tambem 
		///----------> ATENÇÂO AQUI <--------------/// 
		/// No dev os limites e saldos estão encriptados 
		$total = $objEncryption->decrypt($movimento_info['ug_perfil_limite']) + $objEncryption->decrypt( $movimento_info['ug_perfil_saldo']);
		
	}

	// pre
	if  ($movimento_info['ug_risco_classif'] == 2) {

		// Aqui tambem
		///----------> ATENÇÂO AQUI <--------------/// 
		/// No dev os limites e saldos estão encriptados 
		$total = $objEncryption->decrypt($movimento_info['ug_perfil_saldo']);


	}
				
						/////Aqui
		///----------> ATENÇÂO AQUI <--------------/// 
	$saldo  = $objEncryption->decrypt($movimento_info['ug_perfil_saldo']);
	// $saldo  =$movimento_info['ug_perfil_saldo']; // usar aqui para o produção

							/////Aqui
		///----------> ATENÇÂO AQUI <--------------/// 
	$limite = $objEncryption->decrypt($movimento_info['ug_perfil_limite']);
	// $limite = $movimento_info['ug_perfil_limite']); // usar aqui para o produção
	

	if ($data_movimento != '' ) {

		echo "Foi encontrado uma venda, será inserido o balanco";
	
		echo "<br><br>".$total."total"."<br>".$b_total.": btotal"."<br>";

		$query_insert = "insert into dist_balancos (db_ug_id,db_saldo,db_limite,db_tipo_lan,db_data_balanco,db_valor_balanco) values ( ";
		$query_insert .= "'".$id_lan."',";
		$query_insert .= "'".$saldo."',";
		$query_insert .= "'".$limite."',";
		$query_insert .= "'".$risco."',";
		$query_insert .= "'".$data_de_hoje."',";
		$query_insert .= "'".$total."')";

echo $query_insert;
	//*	SQLexecuteQUERY($query_insert);
	
		echo "Id do PDV : ".$id_lan. " <br> ";
		echo "* saldo : ".$saldo. " <br> ";
		echo "* limite : ".$limite. " <br> ";
		echo "* data : ".$data_de_hoje. " <br>";
		echo "Saldo Total : ".$total." <br><br> ";

	} else {

	echo "Nenhuma venda recente foi realizada para o PDV : ".$id_lan." N&atilde;o ser&aacute; cadastrado Balanco para ela.<br>";
		//die();
	}

}// fim do while
	







?>
