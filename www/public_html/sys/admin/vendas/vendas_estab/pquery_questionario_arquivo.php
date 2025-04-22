<?php header("Content-Type: text/html; charset=ISO-8859-1",true) ?>
<?php 

    require_once "../../../../../includes/constantes.php";
    require_once $raiz_do_projeto . "includes/main.php";
    require_once $raiz_do_projeto . "includes/gamer/main.php";
    require_once $raiz_do_projeto."db/connect.php";
    require_once $raiz_do_projeto."db/ConnectionPDO.php";

?>
<link href="/css/styles.css" rel="stylesheet" type="text/css" />
<?php
	require_once $raiz_do_projeto."includes/gamer/AES.class.php";
        require_once $raiz_do_projeto."includes/gamer/chave256_tmp.php"; 
	require_once $raiz_do_projeto."includes/inc_register_globals.php";
        set_time_limit(3600);
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

$sql_estat0 = "";

/*
	// Dummy
	$estat = "TESTES $%¨% dssd32";
	$aes = new AES($chave256bits);
	$sql_estat0 = base64url_encode($aes->encrypt($estat));
	if(!$sql_estat) $sql_estat = $sql_estat0;
//echo "<pre>".print_r($_REQUEST)."</pre>";
//die("Stop");
*/
//echo "sql_estat: '$sql_estat'<br>";
//die("Stop");

		//instanciando a classe de cryptografia
		$aes = new AES($chave256bits);
		$estat_decrypted = $aes->decrypt(base64url_decode($sql_estat));

//echo "estat_decrypted: <div style='background-color:#CCFF99'>".str_replace("\n", "<br>\n", $estat_decrypted)."</div><br>";
if($estat_decrypted) {
	// Recupera dados do pagamento
	$sql = $estat_decrypted;
	//echo "sql: $sql<br>"; 
	$ret = SQLexecuteQuery($sql);
	if(!$ret) {
		echo "Erro ao recuperar dados de questionario (ajax)\n";
		die("Stop");
	}

	$mensagem = "";

	//echo "<table>";
	$mensagem .= "Data\tvg_id\tstatus venda\tCanal\tPrice\tCurrency\tItem type\tPayment method\tGame type\tSex\tAge\tCountry\tCity/State\n\n";

	$i = 1;
	while($ret_row = pg_fetch_array($ret)) {
//		echo "<tr><td><font size='2' face='Arial, Helvetica, sans-serif'>".($i++)."</font></td>";
/*
		foreach($ret_row as $key => $val) {
			echo "<td><font size='2' face='Arial, Helvetica, sans-serif'>$val</font></td>";
		}
*/
/*
	?>
		<td><font size="1" face="Arial, Helvetica, sans-serif"><?php  echo formata_data($ret_row['trn_data'], 0) ?></font></td>
		<td><font size="1" face="Arial, Helvetica, sans-serif"><?php  echo "<a href='".$slink."'>".$ret_row['vg_id']."</a>" ?></font></td>
		<td><font size="1" face="Arial, Helvetica, sans-serif"><?php  echo $ret_row['status'] ?></font></td>

		<td><font size="1" face="Arial, Helvetica, sans-serif"><?php  echo $ret_row['canal'] ?></font></td>
		<td><font size="1" face="Arial, Helvetica, sans-serif"><?php  echo number_format(($ret_row['total_face']), 2, ',', '.') ?></font></td>

		<td><font size="1" face="Arial, Helvetica, sans-serif"><?php  echo $ret_row['currency'] ?></font></td>
		<td><font size="1" face="Arial, Helvetica, sans-serif"><?php  echo $ret_row['item_type'] ?></font></td>
		<td><font size="1" face="Arial, Helvetica, sans-serif"><?php  echo $ret_row['payment_method'] ?></font></td>

		<td><font size="1" face="Arial, Helvetica, sans-serif"><?php  echo $ret_row['game_type'] ?></font></td>

		<td><font size="1" face="Arial, Helvetica, sans-serif"><?php  echo $ret_row['ug_sexo'] ?></font></td>
		<td><font size="1" face="Arial, Helvetica, sans-serif"><?php  echo $ret_row['ug_age'] ?></font></td>
		<td><font size="1" face="Arial, Helvetica, sans-serif"><?php  echo $ret_row['ug_country'] ?></font></td>
		<td><font size="1" face="Arial, Helvetica, sans-serif"><nobr><?php  echo $ret_row['ug_zip_code'] ?></nobr></font></td>
	<?php
*/

		$mensagem .= formata_data($ret_row['trn_data'], 0) ."\t". 
			$ret_row['vg_id'] ."\t". 
			$ret_row['status'] ."\t". 
			$ret_row['canal'] ."\t". 
			number_format(($ret_row['total_face']), 2, ',', '.') ."\t". 
			$ret_row['currency'] ."\t". 
			$ret_row['item_type'] ."\t". 
			$ret_row['payment_method'] ."\t". 
			$ret_row['game_type'] ."\t". 
			$ret_row['ug_sexo'] ."\t". 
			$ret_row['ug_age'] ."\t". 
			$ret_row['ug_country'] ."\t". 
			$ret_row['ug_zip_code'] ."\n";
/*
formata_data($ret_row['trn_data'], 0) 
$ret_row['vg_id']
$ret_row['status'] 
$ret_row['canal'] 
number_format(($ret_row['total_face']), 2, ',', '.') 
$ret_row['currency'] 
$ret_row['item_type'] 
$ret_row['payment_method'] 
$ret_row['game_type'] 
$ret_row['ug_sexo'] 
$ret_row['ug_age'] 
$ret_row['ug_country'] 
$ret_row['ug_zip_code']

		
*/
//		echo "</tr>";
		$mensagem .= "\n";
	}
	$mensagem .= "\n";
} else {
	echo "Sem comando no retorno<br>";
	die("Stop 4334");
}

$file_ret = grava_arquivo_emails_user($mensagem); 
$tf_tipo = "q";
?>
<a href="/includes/dld.php?f=<?php echo $file_ret; ?>&fc=<?php echo $tf_tipo."_".date("YmdHis").".txt"; ?>">Arquivo TXT com Todos os Registros da Sele&ccedil;&atilde;o</a>
</html>
<?php 
// Tomado de CodeIgniter (ver POS lista_transacoes_gr.php)
function grava_arquivo_emails_user($mensagem) {

		$file_path = RAIZ_DO_PROJETO . "public_html/tmp/txt/";
		$web_path = "/tmp/txt/";
		$expiration = 200;

		// -----------------------------------
		// Remove old files	
		// -----------------------------------
				
		list($usec, $sec) = explode(" ", microtime());
		$now = ((float)$usec + (float)$sec);
				
		$current_dir = @opendir($file_path);
		
		while($filename = @readdir($current_dir)) {
			if ($filename != "." and $filename != ".." and $filename != "index.html") {
				$name = str_replace(".txt", "", $filename);
				if (($name + $expiration) < $now) {
					@unlink($file_path.$filename);
				}
			}
		}
		@closedir($current_dir);

		//Arquivo
		$file = $file_path.$now.".txt";
	
		//Grava mensagem no arquivo
		if ($handle = fopen($file, 'a+')) {
			fwrite($handle, $mensagem);
			fclose($handle);
		} 
		
//		$file_return = 'http://'.$_SERVER['HTTP_HOST'].$web_path.$now.".txt";
		$file_return = $now.".txt";

		return $file_return;
}
?>