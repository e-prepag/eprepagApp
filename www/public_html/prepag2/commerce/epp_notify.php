<?php  

require_once "../../../includes/constantes.php";
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "gamer/main.php";
require_once DIR_CLASS . "gamer/classIntegracao.php";
 
?>
<?php 
// error_reporting(E_ALL); 
// ini_set("display_errors", 1); 

//	echo "_SERVER['HTTPS']: <b>".$_SERVER['HTTPS']."</b><br>\n";
//	echo "_SERVER['SERVER_PORT']: <b>".$_SERVER['SERVER_PORT']."</b><br>\n";

//echo "<pre>".print_r($_POST, true)."</pre>";

	// Default
	$codretepp = "3"; // Order not found

	// Grava info de log
	$log_codretepp		= "?";
	$log_store_id		= "";
	$log_cliente_email	= "";
	$log_cmd			= "";
	$log_currency_code	= "";
	$log_order_id		= "";
	$log_amount			= "";
	$vg_id				= 0;

	// Obtem a origem a partir do IP, do HTTP_Referer e outra informação que seja necessária
	$b_ret = is_Integracao();
	if(true) {	//if(is_Integracao()) {	//	

		// Obter parceiro_params a partir de $_POST
		$parceiro_params = get_Integracao_params_from_POST();

		// Valida dados de POST
		if(is_Integracao_params_valida($parceiro_params)) {
//echo "<font color='blue'>OK</font><br>";

			// Transfere dados de parceiro para nosso ambiente
			$integracao_store_id			= $parceiro_params["store_id"];

			$integracao_currency_code		= $parceiro_params["currency_code"];
			$integracao_order_id			= $parceiro_params["order_id"];
			$integracao_order_description	= $parceiro_params["order_description"];
			$integracao_amount				= $parceiro_params["amount"];
			$integracao_client_email		= $parceiro_params["client_email"];
			$integracao_cliente_id			= $parceiro_params["client_id"];
			$integracao_transaction_id		= $parceiro_params["transaction_id"];
			$integracao_cmd					= $parceiro_params["cmd"];		// must be 'processed'
			$integracao_parceiro_params		= serialize($parceiro_params);

			// Testa IP Address
			$ip_remote_address = $GLOBALS['_SERVER']['REMOTE_ADDR'];
			$b_ip_valid = b_is_address_valid($integracao_store_id, $ip_remote_address);
			grava_log_integracao_ip_notify("\nTESTA IP_ADDRESS em EPP_NOTIFY ".date("Y-m-d H:i:s")." ($integracao_store_id, IP: '$ip_remote_address') IP valid?: ".(($b_ip_valid)?"SIM":"não")."\n");

			$msg_0 = "EPP_NOTIFY - Integração entering notify em EPP_NOTIFY: ".date("Y-m-d H:i:s")."\n  ip_store_id = '".$integracao_store_id."', integracao_client_email: '$integracao_client_email', ip_order_id = '".$integracao_order_id."', ip_amount  = '".$integracao_amount."', ip_currency_code = '".$integracao_currency_code."', integracao_transaction_id: '$integracao_transaction_id', integracao_cmd: '$integracao_cmd'\n";
			grava_log_integracao($msg_0);
			grava_log_integracao("EPP_Notify - params: ".print_r($_POST, true)."\n");

			$b_testing_email = getPartner_param_By_ID('partner_testing_email', $integracao_store_id);
			$s_testing_email = getPartner_param_By_ID('partner_email', $integracao_store_id);

			// Grava info de log
			$log_store_id		= $integracao_store_id;
			$log_cliente_email	= $integracao_client_email;
			$log_cmd			= $integracao_cmd;
			$log_currency_code	= $integracao_currency_code;
			$log_order_id		= $integracao_order_id;
			$log_amount			= $integracao_amount;

			grava_log_integracao("Em EPP_NOTIFY (A): ".date("Y-m-d H:i:s")."\n  b_testing_email = '".(($b_testing_email)?"TRUE":"false")."', s_testing_email: '$s_testing_email'\n");

			// Modelo de produto
			$valor = $integracao_amount/100;
//echo "integracao_mod: $integracao_mod<br>";

			// Fazer login para o usuário ou cadastrar um novo usuário
			$idcliente = (new UsuarioGames)->existeEmail_get_ID($integracao_client_email);

//echo "integracao_client_email: '$integracao_client_email'<br>";
//echo "idcliente: $idcliente<br>";
//die("Stop");

			if(true || $idcliente>0) {

				// ======================== Inicio - Conteúdo

//echo "Usuário encontrado ($idcliente): Reportar dados da venda<br>";

				// ======================== Fim - Conteúdo

				grava_log_integracao("Integração 'LOGIN ACEITO' em EPP_NOTIFY: ".date("Y-m-d H:i:s")."\n  idcliente: $idcliente\n  integracao_client_email: $integracao_client_email, valor: $valor\n");

				// recupera os dados da compra efetuada.
				$sql = "SELECT * 
						FROM tb_integracao_pedido ip 
							left outer join tb_venda_games vg on ip.ip_vg_id = vg.vg_id 
						WHERE ip.ip_store_id = '".$integracao_store_id."' 
							and ip.ip_currency_code = '".$integracao_currency_code."' 
							and ip.ip_order_id = '".$integracao_order_id."' 
							and ip.ip_amount  = '".$integracao_amount."' 
							and ip.ip_client_email = '".$integracao_client_email."' 
							and vg_id>0
							and ip_vg_id>0
							order by ip_data_inclusao desc";
//echo "sql: $sql<br>"; 
grava_log_integracao("Integração SQL em EPP_NOTIFY: ".date("Y-m-d H:i:s")."\n$sql\n");

				$retCompra = SQLexecuteQuery($sql);
				if(!$retCompra) {
					$codretepp = "3"; // Order not found
					grava_log_integracao("Integração 'Order not found 0' (!retCompra) em EPP_NOTIFY: ".date("Y-m-d H:i:s")."\n  ip_store_id = '".$integracao_store_id."', integracao_client_email: $integracao_client_email, ip_order_id = '".$integracao_order_id."', ip_amount  = '".$integracao_amount."', ip_currency_code = '".$integracao_currency_code."', codretepp: '$codretepp'\n");
					//echo "Erro ao recuperar transação de pagamento ('$numOrder').<br>\n";
					//die("Stop");
				}

				// Recupera dados do pagamento
				if ($retCompra) {
					$nregs = pg_num_rows($retCompra);
					if($nregs==0) {
						$codretepp = "3"; // Order not found
						grava_log_integracao("Integração 'Order not found (Found $nregs registers)' em EPP_NOTIFY: ".date("Y-m-d H:i:s")."\n  ip_store_id = '".$integracao_store_id."', integracao_client_email: $integracao_client_email, ip_order_id = '".$integracao_order_id."', ip_amount  = '".$integracao_amount."', ip_currency_code = '".$integracao_currency_code."', codretepp: '$codretepp'\n");
						//echo "Erro ao recuperar transação de pagamento ('$numOrder').<br>\n";
						//die("Stop");
					} else {

						// Chegou aqui -> temos um registro encontrado
						$pgCompra = pg_fetch_array($retCompra);
	/*
		$STATUS_VENDA = array(	'PEDIDO_EFETUADO' 			=> '1',	
								'DADOS_PAGTO_RECEBIDO' 		=> '2',	
								'PAGTO_CONFIRMADO' 			=> '3',	OK
								'PROCESSAMENTO_REALIZADO'	=> '4',	
								'VENDA_REALIZADA' 			=> '5',	OK
								'VENDA_CANCELADA'			=> '6'	OK
								);	
	0 => 'Order successfully confirmed',
	1 => 'Order already confirmed',
	2 => 'Incorrect parameters values',
	3 => 'Order not found',
	4 => 'Postback is missing data',
	5 => 'Order not payed yet',
	6 => 'Order not processed yet',
	7 => 'Order canceled',
	8 => 'System not available',

	*/
						$ip_status_confirmed	= $pgCompra['ip_status_confirmed'];
						$vg_ultimo_status		= $pgCompra['vg_ultimo_status'];
						$vg_id					= $pgCompra['vg_id'];

						if($ip_status_confirmed==1 && $vg_ultimo_status==$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'] && $vg_id>0) {
							$codretepp = "1"; // Order already confirmed
							$msg = "EPP_NOTIFY - Integração 'Order already confirmed' em EPP_NOTIFY: ".date("Y-m-d H:i:s")."\n  ip_store_id = '".$integracao_store_id."', integracao_client_email: $integracao_client_email, ip_order_id = '".$integracao_order_id."', ip_amount  = '".$integracao_amount."', ip_currency_code = '".$integracao_currency_code."', vg_ultimo_status='$vg_ultimo_status', codretepp: '$codretepp'\n";
							grava_log_integracao($msg);
//echo "<font color='blue'>$msg</font><br>";
							if($vg_ultimo_status==$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']) {
								// OK, venda completa para uma integração também completa
							} else {
								$msg = "EPP_NOTIFY - ERROR - Integração Completa, mas venda incompleta ('$vg_ultimo_status') em EPP_NOTIFY: ".date("Y-m-d H:i:s")."\n  ip_store_id = '".$integracao_store_id."', integracao_client_email: $integracao_client_email, ip_order_id = '".$integracao_order_id."', ip_amount  = '".$integracao_amount."', ip_currency_code = '".$integracao_currency_code."', vg_ultimo_status='$vg_ultimo_status', codretepp: '$codretepp'\n";
								grava_log_integracao($msg);
							}

						} else {
							if($vg_ultimo_status==$GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']) {
								$codretepp = "0"; // Order successfully confirmed
								$msg = "EPP_NOTIFY - Integração Order successfully confirmed em EPP_NOTIFY: ".date("Y-m-d H:i:s")."\n  ip_store_id = '".$integracao_store_id."', integracao_client_email: $integracao_client_email, ip_order_id = '".$integracao_order_id."', ip_amount  = '".$integracao_amount."', ip_currency_code = '".$integracao_currency_code."', vg_ultimo_status='$vg_ultimo_status', codretepp: '$codretepp'\n";
								grava_log_integracao($msg);
//echo "<font color='blue'>$msg</font><br>";

								// Atualiza o status de Notify
								$sql = "UPDATE tb_integracao_pedido SET ip_status_confirmed = 1, ip_data_confirmed = CURRENT_TIMESTAMP 
										WHERE ip_store_id = '".$integracao_store_id."' 
											and ip_currency_code = '".$integracao_currency_code."' 
											and ip_order_id = '".$integracao_order_id."' 
											and ip_amount  = '".$integracao_amount."' 
											and ip_client_email = '".$integracao_client_email."' ";
							//echo "sql: $sql<br>"; 
								grava_log_integracao("\nAtualiza o status de Notify\n  ".$sql."\n");
								$retCompra = SQLexecuteQuery($sql);

							} elseif($vg_ultimo_status==$GLOBALS['STATUS_VENDA']['PEDIDO_EFETUADO'] || $vg_ultimo_status==$GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO'] || $vg_ultimo_status==$GLOBALS['STATUS_VENDA']['PROCESSAMENTO_REALIZADO'] || $vg_ultimo_status==$GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO']) {
								$codretepp = "6"; // Order not processed yet
								$msg = "EPP_NOTIFY - Integração 'Order not processed yet' em EPP_NOTIFY: ".date("Y-m-d H:i:s")."\n  ip_store_id = '".$integracao_store_id."', integracao_client_email: $integracao_client_email, ip_order_id = '".$integracao_order_id."', ip_amount  = '".$integracao_amount."', ip_currency_code = '".$integracao_currency_code."', vg_ultimo_status='$vg_ultimo_status', codretepp: '$codretepp'\n";
								grava_log_integracao($msg);
							} elseif($vg_ultimo_status==$GLOBALS['STATUS_VENDA']['VENDA_CANCELADA']) {
								$codretepp = "7"; // Order canceled
								$msg = "EPP_NOTIFY - Integração 'Order canceled' em EPP_NOTIFY: ".date("Y-m-d H:i:s")."\n  ip_store_id = '".$integracao_store_id."', integracao_client_email: $integracao_client_email, ip_order_id = '".$integracao_order_id."', ip_amount  = '".$integracao_amount."', ip_currency_code = '".$integracao_currency_code."', vg_ultimo_status='$vg_ultimo_status', codretepp: '$codretepp'\n";
								grava_log_integracao($msg);
							} else {
								// Não deve chegar aqui	
								$codretepp = "6"; // Order not processed yet
								$msg = "EPP_NOTIFY - Integração 'Order not found (A)' em EPP_NOTIFY: ".date("Y-m-d H:i:s")."\n  ip_store_id = '".$integracao_store_id."', integracao_client_email: $integracao_client_email, ip_order_id = '".$integracao_order_id."', ip_amount  = '".$integracao_amount."', ip_currency_code = '".$integracao_currency_code."', vg_ultimo_status='$vg_ultimo_status', codretepp: '$codretepp'\n";
								grava_log_integracao($msg);
							}
//							grava_log_integracao("EPP_NOTIFY - Integração 'Order not found (SQL)' em EPP_NOTIFY: ".date("Y-m-d H:i:s").$sql."\n");
						}
					}
				} else {
					$codretepp = "3"; // Order not found
					$msg = "EPP_NOTIFY - Integração 'Order not found (B)' (!retCompra 2) em EPP_NOTIFY: ".date("Y-m-d H:i:s")."\n  ip_store_id = '".$integracao_store_id."', integracao_client_email: $integracao_client_email, ip_order_id = '".$integracao_order_id."', ip_amount  = '".$integracao_amount."', ip_currency_code = '".$integracao_currency_code."', codretepp: '$codretepp'\n";
					grava_log_integracao($msg);
				}

			} else {

				$codretepp = "2"; // Incorrect parameters values
				$msg = "EPP_NOTIFY - Integração LOGIN NÃO ACEITO (USUÁRIO DESCONHECIDO) em EPP_NOTIFY: ".date("Y-m-d H:i:s")."\n  integracao_client_email: $integracao_client_email ($codretepp => '".$notify_list[$codretepp]."' ['Incorrect parameters values'])\n";
				grava_log_integracao($msg);
//echo "<font color='red'>$msg</font><br>";
			}

		} else {
			$codretepp = "2"; // Incorrect parameters values
			$msg = "EPP_NOTIFY - Integração recusada em EPP_NOTIFY - Parametros inválidos: ".date("Y-m-d H:i:s")."\n  store_id: ".$parceiro_params["store_id"]." ($codretepp => '".$notify_list[$codretepp]."')\n";
			grava_log_integracao($msg);
//echo "<font color='red'>$msg</font><br>";
		}
	} else {
		$codretepp = "8"; // System not available
		$msg = "EPP_NOTIFY - Origem de integração inválida em EPP_NOTIFY -> Sem login ($codretepp => '".$notify_list[$codretepp]."')\n";
		grava_log_integracao($msg);
//echo "<font color='red'>$msg</font><br>";
	}

/*
		insert into tb_integracao_pedido_historico(vgh_ip_id, vgh_ip_vg_id, vgh_data_inclusao, vgh_ip_status_confirmed)
		values(NEW.ip_id, NEW.ip_vg_id, CURRENT_TIMESTAMP, NEW.ip_status_confirmed);

*/
/*
//echo "<!--\n";
echo "<pre>\n";
//echo "_POST\n";
//print_r($_POST);
//echo "_GET\n";
//print_r($_GET);
echo "\n_REQUEST\n";
print_r($_REQUEST);
echo "\n_SESSION\n";
print_r($_SESSION);
echo "</pre>\n";

echo "iforma: $iforma<br>";
//die("Stop");
//echo "-->\n";
*/

	// Grava Log
	$msg1 = "Terminou NOTIFY1: ".date("Y-m-d H:i:s")." (codretepp: '$codretepp')\n ";
	grava_log_integracao($msg1);

	// Grava info de log
	$log_codretepp		= $codretepp;
	grava_log_notify_db($log_store_id, $integracao_client_email, $log_cmd, $log_codretepp, $log_currency_code, $log_order_id, $log_amount, $vg_id);

	$msg_ret = "CODRETEPP=".$codretepp;

	$msg_debug = $msg ."(Msg Return from epp_notify: " . $msg_ret . ")"."\n".str_repeat("-", 80)."\n";

	// Debug
	send_debug_info_by_email("E-Prepag - Testing integration - EPP_Notify", $msg_debug, $parceiro_params["store_id"]);

	// Grava Log
	$msg1 = "Terminou NOTIFY2: ".date("Y-m-d H:i:s")." (store_id: ".$parceiro_params["store_id"].", order_id: ".$integracao_order_id.", codretepp: '$codretepp')\n   msg_ret: $msg_ret\n";
	grava_log_integracao($msg1);


echo $msg_ret;
?>
