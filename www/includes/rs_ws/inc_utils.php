<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

require_once $raiz_do_projeto . "db/connect.php";

$versao = "1.2.3";
$ident_rede = "8888";

$varBlDebug = 0;
$_config = array(
  'db_type'		=> 'PostgreSQL',
  'db_host'		=> DB_HOST,
  'db_port'		=> DB_PORT,
  'db_name'		=> DB_BANCO,
  'db_user'		=> DB_USER,	// epp_pr
  'db_passw'	=> DB_PASS,	// p4ssw0rd1354
  'db_connid'	=> null,

);

$a_tipos = array(
        "EG" => "Echo Get",
        "ES" => "Echo Set (retorno)",
        "PG" => "Produto Get",
        "PS" => "Produto Set (retorno)",
        "VG" => "Valores Get",
        "VS" => "Valores Set (retorno)",
        "CG" => "Compra Get",
        "CS" => "Compra Set (retorno)",
        "LG" => "Cancela Compra Get",
        "LS" => "Cancela Compra Set (retorno)",
        "RG" => "Confirma Compra Get",
        "RS" => "Confirma Compra Set (retorno)",
);

//foreach($a_tipos as $key => $val) {
//	echo "'$key' =&gt; '$val'<br>";
//}


$a_tipos_funcao = array(
        "E" => "Echo",
        "P" => "Produto",
        "V" => "Valores",
        "C" => "Compra",
        "L" => "Cancela Compra",
        "R" => "Confirma Compra",
);

//foreach($a_tipos_funcao as $key => $val) {
//	echo "'$key' =&gt; '$val'<br>";
//}

$a_tipos_direcao = array(
        "G" => "Get",
        "S" => "Set",
);



_doConnect();

/**
*
* _doConnect
*
* It connects to the database using confguration parameters
*
* @access private
* @param void
*/
function _doConnect() {
        global $_config;

//print_r2($_config);
        if($_config['db_type']=='PostgreSQL') {
                $_config['db_connid'] = pg_connect("host=".$_config['db_host']." port=".$_config['db_port']." dbname=".$_config['db_name']." user=".$_config['db_user']." password=".$_config['db_passw']."");
        }

        if(!$_config['db_connid']) {
          echo "<font color='#FF0000'>No conection</font><br>";
          die("Stop<br>");
        } 
//		else {echo "<font color='#0000FF'>DB Conected</font><br>&nbsp;";}
}

if (!function_exists('getmicrotime')) {
	function getmicrotime() {
	   list($usec, $sec) = explode(" ", microtime());
	   return ((float)$usec + (float)$sec);
	}
}

if (!function_exists('SQLexecuteQuery')) {
	function SQLexecuteQuery($sql){
		global $_config, $varBlDebug;

		//echo "<font color='red'>SQLexecuteQuery() desativada</font><br>\n";
		//return true;

		$lev = error_reporting (8); //NO WARRING!!

		if($varBlDebug){
			echo "<br>" . $sql . "<br>";
			if(substr($sql, 0, 6) == "select")	$ret = pg_query ($_config['db_connid'], $sql);
			else $ret = 1;
		} else {
			$ret = pg_query ($_config['db_connid'], $sql);
		}

		error_reporting ($lev); //DEFAULT!!

		if (strlen ($erro = pg_last_error($_config['db_connid']))) {
    		$message  = date("Y-m-d H:i:s") . " ";
    		$message .= "Erro: " . $erro . "<br>\n";
    		$message .= "Query: " . $sql . "<br>\n";
    		gravaLog_SQLexecuteQuery_epp_pos($message);
    		//die($message);
	    }

		return $ret;		
	}
}

function gravaLog_SQLexecuteQuery_epp_pos($mensagem){

        //Arquivo
        //$file = $GLOBALS['ARQUIVO_LOG_SQL_EXECUTE_QUERY'];
        $file = DIR_LOG . "log_epprede_sql_execute_query.txt";

        //Mensagem
        $mensagem = date('Y-m-d H:i:s') . " " . $GLOBALS['_SERVER']["SCRIPT_FILENAME"] . "\n" . $mensagem . "\n";

        //Grava mensagem no arquivo
        if ($handle = fopen($file, 'a+')) {
                fwrite($handle, $mensagem);
                fclose($handle);
        }	
}

// =========================================================
function gravaLog_WS_processing($mensagem){

        //Arquivo
        //$file = $GLOBALS['ARQUIVO_LOG_SQL_EXECUTE_QUERY'];
        $file = DIR_LOG . "log_redesim_ws_processing.txt";

        //Mensagem
        $mensagem = str_repeat("=",80)."\n".date('Y-m-d H:i:s') . " " . $GLOBALS['_SERVER']["SCRIPT_FILENAME"] . "\n" . $mensagem . "\n";

        //Grava mensagem no arquivo
        if ($handle = fopen($file, 'a+')) {
                fwrite($handle, $mensagem);
                fclose($handle);
        } 

}

// =========================================================
function gravaLog_WS_canceling($mensagem){

        //Arquivo
        //$file = $GLOBALS['ARQUIVO_LOG_SQL_EXECUTE_QUERY'];
        $file = DIR_LOG . "log_redesim_ws_canceling.txt";

        //Mensagem
        $mensagem = str_repeat("=",80)."\n".date('Y-m-d H:i:s') . " " . $GLOBALS['_SERVER']["SCRIPT_FILENAME"] . "\n" . $mensagem . "\n";

        //Grava mensagem no arquivo
        if ($handle = fopen($file, 'a+')) {
                fwrite($handle, $mensagem);
                fclose($handle);
        } 

}

// =========================================================
function gravaLog_WS_origem($mensagem){

        //Arquivo
        //$file = $GLOBALS['ARQUIVO_LOG_SQL_EXECUTE_QUERY'];
        $file = DIR_LOG . "log_redesim_ws_origem.txt";


//		$remote_server_ip_address = gethostbyname(get_server_DNS_by_URL($GLOBALS['_SERVER']['HTTP_REFERER']));
        $s_server_vars = "  SERVER Information in \$_SERVER\n";
//		$s_server_vars .= "  SERVER gethostbyname: ".$remote_server_ip_address."\n";
        $s_server_vars .= "  SERVER_ADDR:          ".$GLOBALS['_SERVER']['SERVER_ADDR']."\n";
        $s_server_vars .= "  LOCAL_ADDR:           ".$GLOBALS['_SERVER']['LOCAL_ADDR']."\n";

//		$s_server_vars .= "  SERVER_ADDR:          ".$GLOBALS['_SERVER']['SERVER_ADDR']."\n";
        $s_server_vars .= "  SERVER_NAME:          ".$GLOBALS['_SERVER']['SERVER_NAME']."\n";
        $s_server_vars .= "  SERVER_SOFTWARE:      ".$GLOBALS['_SERVER']['SERVER_SOFTWARE']."\n";
        $s_server_vars .= "  SERVER_PROTOCOL:      ".$GLOBALS['_SERVER']['SERVER_PROTOCOL']."\n";

        $s_server_vars .= "  REMOTE_ADDR:          ".$GLOBALS['_SERVER']['REMOTE_ADDR']."\n";
        $s_server_vars .= "  HTTP_REFERER:         ".$GLOBALS['_SERVER']['HTTP_REFERER']."\n";
        $s_server_vars .= "  HTTP_CLIENT_IP:       ".$GLOBALS['_SERVER']['HTTP_CLIENT_IP']."\n";
        $s_server_vars .= "  HTTP_X_FORWARDED_FOR: ".$GLOBALS['_SERVER']['HTTP_X_FORWARDED_FOR']."\n";

        $s_server_vars .= "  HTTPS:                ".$GLOBALS['_SERVER']['HTTPS']."\n";
        $s_server_vars .= "  SERVER_PORT:          ".$GLOBALS['_SERVER']['SERVER_PORT']."\n";

        $s_server_vars .= "  REMOTE_HOST:          ".$GLOBALS['_SERVER']['REMOTE_HOST']."\n";
        $s_server_vars .= "  HTTP_HOST:            ".$GLOBALS['_SERVER']['HTTP_HOST']."\n";

        $ret_code1 = "--";
        $s_server_vars .= "  is_REMOTE_HOST_OK:    ".((is_REMOTE_HOST_OK($ret_code1, "123.test"))?"YES":"no")."\n";

        //Mensagem
        $mensagem = str_repeat("=",80)."\n".date('Y-m-d H:i:s') . " " . $GLOBALS['_SERVER']["SCRIPT_FILENAME"] . "\n" . $mensagem . "\n" . $s_server_vars . "\n";

        //Grava mensagem no arquivo
        if ($handle = fopen($file, 'a+')) {
                fwrite($handle, $mensagem);
                fclose($handle);
        } 

}

function formata_data_ts_pos($data, $gravar, $blComHora, $blComSegundos){

        $mask = $data;

        //Entra: yyyy-mm-dd hh:mm:ss.uuu
        //Sai: dd/mm/yyyy hh:mm:ss.uuu
        if($gravar == 0){
                $dia = substr($mask, 8, 2);
                $mes = substr($mask, 5, 2);
                $ano = substr($mask, 0, 4);
                $doc = $dia."/".$mes."/".$ano;

                if($blComHora){
                        $hora = substr($mask, 11, 2);
                        $minuto = substr($mask, 14, 2);
                        $segundo = substr($mask, 17, 2);
                        $milliseg = substr($mask, 20, 3);
                        $doc = $doc . " " . $hora . ":" . $minuto;
                        if($blComSegundos) $doc = $doc . ":" . $segundo;
//				if($milliseg) $doc = $doc . "." . $milliseg;
                }
                $doc = str_replace(" ","<br>\n",$doc);
        }

        //Entra: dd/mm/yyyy hh:mm:ss
        //Sai: yyyymmddhhmmss
        if($gravar == 1){
                $dia = substr($mask, 0, 2);
                $mes = substr($mask, 3, 2);
                $ano = substr($mask, 6, 4);
                $doc = $ano . $mes . $dia;
                if($blComHora){
                        $hora = substr($mask, 11, 2);
                        $minuto = substr($mask, 14, 2);
                        $segundo = substr($mask, 17, 2);
                        $milliseg = substr($mask, 20, 3);
                        $doc .= " " . $hora . $minuto;
                        if($blComSegundos) $doc .= $segundo;
                        else $doc .= "00";
                        if($milliseg) $doc = $doc . "." . $milliseg;

                } else {
                        $doc .= "000000";
                }
        }

        //Entra: dd/mm/yyyy hh:mm:ss
        //Sai: yyyy-mm-dd hh:mm:ss
        if($gravar == 2){
                $dia = substr($mask, 0, 2);
                $mes = substr($mask, 3, 2);
                $ano = substr($mask, 6, 4);
                $doc = $ano . "-" . $mes . "-" . $dia;
                if($blComHora){
                        $hora = substr($mask, 11, 2);
                        $minuto = substr($mask, 14, 2);
                        $segundo = substr($mask, 17, 2);
                        $milliseg = substr($mask, 20, 3);
                        $doc = $doc . " " . $hora . ":" . $minuto;
                        if($blComSegundos) $doc = $doc . ":" . $segundo;
                        if($milliseg) $doc = $doc . "." . $milliseg;

                } else {
                        $doc .= "00:00:00";
                }
        }
        return $doc;
}

function print_r2($obj) {
        echo "<pre>";
        print_r($obj);
        echo "</pre>";
}

$epp_erros = array(
        array('code' => 'R0', 'description' => 'Sistema EPP inativo'),
        array('code' => 'R1', 'description' => 'Rede não reconhecida'),

        array('code' => 'NA', 'description' => 'Endereço IP do parceiro não reconhecido'),

        array('code' => 'V1', 'description' => 'Valores NÃO encontrados'),
        array('code' => 'V2', 'description' => 'Produtos NÃO encontrados'),

        array('code' => 'N0', 'description' => 'Recarga não permitida'),
        array('code' => 'N1', 'description' => 'Operadora NÃO existe'),
        array('code' => 'N3', 'description' => 'Valor de PIN não existe'),
        array('code' => 'N5', 'description' => 'Estabelecimento bloqueado'),
        array('code' => 'N6', 'description' => 'Venda não encontrada'),

        array('code' => 'C1', 'description' => 'Compra não encontrada'),
        array('code' => 'C2', 'description' => 'Erro ao liberar PIN'),
        array('code' => 'C3', 'description' => 'Erro ao cancelar compra'),
        array('code' => 'C4', 'description' => 'Erro ao confirmar compra'),

        array('code' => 'Z1', 'description' => 'Usuário não valido'),
        array('code' => 'Z2', 'description' => 'Erro no estoque'),
        array('code' => 'Z3', 'description' => 'Erro ao iniciar transação'),
        array('code' => 'Z4', 'description' => 'Nenhum pin encontrado ou estoque insuficiente'),
        array('code' => 'Z5', 'description' => 'Erro ao atualizar tabela de pins'),
        array('code' => 'Z6', 'description' => 'Erro ao inserir venda buffer'),
        array('code' => 'Z7', 'description' => 'Erro ao completar transação'),

        array('code' => 'VI', 'description' => 'Erro de validação de valores'),
        array('code' => 'VS', 'description' => 'Erro de validação de valores'),
        array('code' => 'VD', 'description' => 'Erro de validação de valores'),
        array('code' => 'VP', 'description' => 'Erro de validação de valores'),
        array('code' => 'VE', 'description' => 'Erro de validação de valores'),

);

// ==============================================
function get_error_description_by_code($err_code) {
        global $epp_erros;
        if($err_code=="00") 
                return "Sucesso";
        if($err_code=="") 
                return "Empty";
        foreach($epp_erros as $key => $val) {
                if($val['code']==$err_code) {
                        return $val['description'];
                }
        }
        return 'Erro desconhecido (Cod: \''.$err_code.'\')';
}

// ==============================================
function list_errors() {
        global $epp_erros;
        echo "<table cellpadding='0' cellspacing='1' border='1' bordercolor='#cccccc' style='border-collapse:collapse;'><tr><td align='center'>\n";
        echo "<tr align='center'><td colspan='2'><b>Lista Erros</b></td></tr>\n";
        echo "<tr align='center'><td>&nbsp;<b>code</b>&nbsp;</td><td>&nbsp;<b>description</b>&nbsp;</td></tr>\n";
        foreach($epp_erros as $key => $val) {
                echo "<tr><td align='center'>".$val['code']."</td><td>".$val['description']."</td></tr>\n";
        }
        echo "</table>\n";
}

// ==============================================
function get_next_transacao_id() {
	$next_transacao_id = date("YmdHis").str_pad(mt_rand(0,10000), 4, "0", STR_PAD_LEFT);
/*
	$sql = "select coalesce(max(ptr_transacao_id),0)+1 as next_transacao_id from dist_vendas_rede_transacao;";
	$rs_max = SQLexecuteQuery($sql);
	if(!$rs_max || pg_num_rows($rs_max) == 0) {
		die("Não foi possivel obter next_transacao_id.<br>\nStop.");
	} else {
		$rs_max_row = pg_fetch_array($rs_max);
		$next_transacao_id = $rs_max_row['next_transacao_id'];
	}
*/
	return $next_transacao_id;
}

// ==============================================
function get_first_compra(&$vp_transacao_id, &$vp_data_inclusao) {

	// Obtem a primeira compra em aberto (ou seja a mais antiga em aberto)
	$sql = "select vp_transacao_id, vp_data_inclusao from dist_vendas_rede_buffer where vp_status_confirmado=0 and vp_transacao_id>0 order by vp_data_inclusao limit 1;";
//echo "$sql<br>";
	$rs_last = SQLexecuteQuery($sql);
	if(!$rs_last || pg_num_rows($rs_last) == 0) {
//		die("Não foi possivel obter dados última compra.<br>\nStop.");
		$vp_transacao_id = 0;
		$vp_data_inclusao = null;
		return false;
	} else {
		$rs_last_row = pg_fetch_array($rs_last);
		$vp_transacao_id = $rs_last_row['vp_transacao_id'];
		$vp_data_inclusao = $rs_last_row['vp_data_inclusao'];
	}

echo "SQL: vp_transacao_id: $vp_transacao_id, vp_data_inclusao: $vp_data_inclusao<br>";
	return true;
}

// ==============================================
function get_venda_id_by_transacao_id($trans_id) {

	$vp_venda_id = 0;
	// Obtem a primeira compra em aberto (ou seja a mais antiga em aberto)
	$sql = "select vp_venda_id from dist_vendas_rede_buffer where vp_transacao_id=$trans_id;";
//echo "$sql<br>";
	$rs_last = SQLexecuteQuery($sql);
	if(!$rs_last || pg_num_rows($rs_last) == 0) {
//		die("Não foi possivel obter dados última compra.<br>\nStop.");
		$vp_transacao_id = 0;
		$vp_data_inclusao = null;
		return false;
	} else {
		$rs_last_row = pg_fetch_array($rs_last);
		$vp_venda_id = $rs_last_row['vp_venda_id'];
	}

	return $vp_venda_id;
}

// ==============================================
function get_next_venda_id() {
	$venda_id_next = 0;
	$sql = "select nextval('vr_vg_id_seq')";
	$rs_next = SQLexecuteQuery($sql);
	if(!$rs_next || pg_num_rows($rs_next) == 0) {
		die("Não foi possivel obter next id venda.<br>\nStop.");
	} else {
		$rs_next_row = pg_fetch_array($rs_next);
		$venda_id_next = $rs_next_row['nextval'];
	}
	return $venda_id_next;
}

// ==============================================
function is_valid_sale_open($transacao_id, $cod_rede, $cod_terminal) {
	// tem vendas sem confirmar/cancelar com o transacao_id?
	$sql = "select vp_id from dist_vendas_rede_buffer where vp_transacao_id=$transacao_id and vp_cod_rede = '$cod_rede' and vp_cod_terminal = '$cod_terminal' and vp_status_confirmado=0;";
//gravaLog_WS_processing("Em is_valid_sale_open (".date("Y-m-d H:i:s")."): \n$sql\n");

	$rs = SQLexecuteQuery($sql);
	if(!$rs || pg_num_rows($rs) == 0) {
		return false;
	} else {
		return true;
	}
	return false;
}


// ==============================================
function verifica_data_pos($data)
{
	$aux = $data;
	$tam = strlen($aux);
		if($tam < 10)
		{ return 0; }
		else
		{
				$bar1 = substr($aux,2,1);
				$bar2 = substr($aux,5,1);
					if(ord($bar1) != 47 || ord($bar2) != 47)
					{ return 0; }
					else
					{
						$dia = substr($aux,0,2); 
						for ($x = 1 ; $x <= strlen($dia) ; $x++)
						{
							$pos = substr($dia,$x-1,1);
							if(ord($pos) >= 48 && ord($pos) <= 57)
							{ $alerta = 0; }
							else
							{ $alerta = 1; break;}
						}							
								if($alerta == 1) 
								{ return 0; }
								else
								{
									$mes = substr($aux,3,2); 
									for ($x = 1 ; $x <= strlen($mes) ; $x++)
									{
										$pos = substr($mes,$x-1,1);
										if(ord($pos) >= 48 && ord($pos) <= 57)
										{ $alerta = 0; }
										else
										{ $alerta = 1; break;}							
									}
										
										if($alerta == 1) 
										{ return  0; }
										else
										{
											$ano = substr($aux,6,4); 
											for ($x = 1 ; $x <= strlen($ano) ; $x++)
											{
												$pos = substr($ano,$x-1,1);
												if(ord($pos) >= 48 && ord($pos) <= 57)
												{ $alerta = 0; }
												else
												{ $alerta = 1; break;}							
											}
											
											if($alerta == 1) 
											{ return  0; }
											else	
											{ 
												if($mes > 12 || $dia > 31)
												{ return 0; }
												else
												{									
													if ((($ano % 4) == 0 and ($ano % 100) != 0) or ($ano % 400) == 0)
														{ $bissexto = 1; }
													else 
														{ $bissexto = 0; }
													
													if($bissexto == 0)
													{
														if(($mes == 4 || $mes == 6 || $mes == 9 || $mes == 11) && ($dia > 30))
														{ return 0; }
														else
														{
															if($mes == 2 && $dia > 28) 
															{ return 0; }
															else
															{ return 1; }														
														}
													}
													if($bissexto == 1)
													{
														if(($mes == 4 || $mes == 6 || $mes == 9 || $mes == 11) && ($dia > 30))
														{ return 0; }
														else
														{
															if($mes == 2 && $dia > 29) 
															{ return 0; }
															else
															{ return 1; }
														}
													}													
												}
											}											
										}															
								}																				
					}																		
		}			
}

// ==============================================
function formata_data_pos($data,$gravar)
{
	$mask = $data;
	if($gravar == 0)
	{
		$dia = substr($mask,8,2);
		$mes = substr($mask,5,2);
		$ano = substr($mask,0,4);
		$doc = $dia."/".$mes."/".$ano;
	}
	
	if($gravar == 1)
	{
		$dia = substr($mask,0,2);
		$mes = substr($mask,3,2);
		$ano = substr($mask,6,4);
		$doc = $ano."-".$mes."-".$dia;
	}

	//entra AAAA-MM-DD
	//retorna DDMMAA
	if($gravar == 2)
	{
		$dia = substr($mask,8,2);
		$mes = substr($mask,5,2);
		$ano = substr($mask,2,2);
		$doc = $dia.$mes.$ano;
	}

	return $doc;
}

// tempo em segundos desde a última mensagem de echo trocada com a rede '$ptr_identificacao'
function get_delay($ptr_identificacao) {
        $delay = -1;

        $sql = "select ptr_identificacao, 
                                (	select EXTRACT ('epoch' FROM (CURRENT_TIMESTAMP - ptr1.ptr_data_trans)) as delay 
                                        from dist_vendas_rede_transacao ptr1 
                                        where ptr1.ptr_identificacao = ptr.ptr_identificacao and substr(ptr1.ptr_operacao, 1, 1) = 'E' 
                                        order by ptr_id desc 
                                        limit 1		
                                ) as delay
                        from dist_vendas_rede_transacao ptr 
                        where 1=1 and substr(ptr_operacao, 1, 1) = 'E' and not ptr_identificacao = '' and ptr_identificacao = '$ptr_identificacao'
                        group by ptr_identificacao
                        order by delay ";
//echo "delay :$sql<br>";
        $rs_trans = SQLexecuteQuery($sql);
        if($rs_trans && pg_num_rows($rs_trans)>0) {
                $rs_trans_row = pg_fetch_array($rs_trans);
                $delay = $rs_trans_row['delay'];
        }
        return (int)$delay;
}


function gravaLog_MonitorPOS($mensagem){
        global $raiz_do_projeto;
        if($bDebug) echo "  SALVA FILE MONITOR (" . date('d/m/Y - H:i:s') . ")\n";
        // Salva o file monitor para mostrar no Backoffice
        try {
                if ($handle = fopen($raiz_do_projeto . 'log/monitortransacoespos.txt', 'w')) { 
                        fwrite($handle, $mensagem."<br>");

                        fclose($handle);
                } else {
                        echo "\nError: Couldn't open Monitor File for writing\n";
                }
        } catch (Exception $e) {
                echo "Error(6) writing monitor file [".date("Y-m-d H:i:s")."]: ".$e->getMessage()."\n";
        }

}

function get_rede_color($srede) {
//		(($rs_trans_row['ptr_identificacao_origem']=="8888")?"blue":(($rs_trans_row['ptr_identificacao_origem']=="8887")?"red":"green"))
        $scolor = "#ff0000";	// -> wrong
        switch($srede) {
                case "8888":
                        $scolor = "#0000ff";
                        break;
                case "8887":
                        $scolor = "#006600";
                        break;
                case "8000":
                        $scolor = "#ff9900";
                        break;
        }
        return $scolor;
}

function convert_secs_to_string_global($n) {
        $sout = "";
        $ndays = 0;
        $nhours = 0;
        $nmins = 0;
        $nsecs = 0;

        $ndays = intval($n/(60*60*24));
        $nhours = intval(($n-$ndays*60*60*24)/(60*60));
        $nmins = intval(($n-$ndays*60*60*24-$nhours*60*60)/(60));
        $nsecs = intval(($n-$ndays*60*60*24-$nhours*60*60-$nmins*60));


        $sout .= "<font size='1'>";
        $sout .= (($ndays>0)?$ndays."<font color='#FF0000'>d</font>":"");
        $sout .= (($ndays>0 || $nhours>0)?$nhours."<font color='#FF0000'>h</font>":"");
        $sout .= (($ndays>0 || $nhours>0 || $nmins>0)?$nmins."<font color='#FF0000'>m</font>":"");
        $sout .= (($ndays>0 || $nhours>0 || $nmins>0 || $nsecs>0)?$nsecs."<font color='#FF0000'>s</font>":"");
        $sout .= "</font>";

        return $sout;
}

function dummy_log_in_reinaldo() {
	$_SESSION['iduser_bko'] = "0401121116743";
	$_SESSION['userlogin_bko'] = "REINALDO";
	$_SESSION['datalog_bko'] = "2013-02-27";
	$_SESSION['horalog_bko'] = "14:44:43";
	echo "FORCED TO LOGIN<br>";
}

function valida_operadora($id) {
	$id_operadoras_current = $GLOBALS['RS_PRODUCT'];
	foreach($id_operadoras_current as $key => $val) {
		if($id == $key) {
			return true;
		}
	}
	return false;
}//end function valida_operadora($id) 

function get_ValorFixo($id, $idvalor) {
	$aValorFixo = 0;
	if(isset($GLOBALS['operadoras_redesim_current'])) {
		$aValoresAux = $GLOBALS['operadoras_redesim_current'];
		foreach($aValoresAux as $key => $val) {
			if ($val['opr_id'] == $id) {
				$aValores = $val['opr_valores']; 
			}//end if ($val['opr_id'] == $id)
		}//end foreach
		foreach($aValores as $key => $val) {
			if(key($val) == $idvalor) {
				$aValorFixo = str_replace(",", ".",str_replace("R$ ", "", $val[key($val)]));
			}
		}
	}//end if(isset($GLOBALS['operadoras_redesim_current']))

	return $aValorFixo;
}

function valida_seguro($id) {
	$id_operadoras_current = $GLOBALS['seguros_redesim_current'];
	foreach($id_operadoras_current as $key => $val) {
		if($id == $val['Item']) {
			return true;
		}
	}
	return false;
}//end function valida_operadora($id) 

function get_ValorFixoSeguro($id, $idvalor) {
	$aValorFixo = 0;
	if(isset($GLOBALS['seguros_redesim_current'])) {
		$aValoresAux = $GLOBALS['seguros_redesim_current'];
		foreach($aValoresAux as $key => $val) {
			if ($val['Item'] == $id) {
				$aValorFixo = str_replace(",", ".",str_replace("R$ ", "", $val['ItemValor']));
			}//end if ($val['opr_id'] == $id)
		}//end foreach
	}//end if(isset($GLOBALS['operadoras_redesim_current']))

	return $aValorFixo;
}

function get_operadora_nome_by_codigo($codigo_operadora) {

	$snome = "";
	if($codigo_operadora) {
		$aValoresAux = $GLOBALS['operadoras_redesim_current'];
		foreach($aValoresAux as $key => $val) {
			if ($val['opr_id'] == $codigo_operadora) {
				$snome = $val['opr_nome'];
				break;
			}
		}
	}
	return $snome;
}

function formata_data_rc($data,$gravar)
{
	$mask = $data;
	if($gravar == 0)
	{
		$dia = substr($mask,8,2);
		$mes = substr($mask,5,2);
		$ano = substr($mask,0,4);
		$doc = $dia."/".$mes."/".$ano;
	}
	
	if($gravar == 1)
	{
		$dia = substr($mask,0,2);
		$mes = substr($mask,3,2);
		$ano = substr($mask,6,4);
		$doc = $ano."-".$mes."-".$dia;
	}
	return $doc;
}

function verifica_data_rc($data) {
	$aux = $data;
	$tam = strlen($aux);
		if($tam < 10)
		{ return 0; }
		else
		{
				$bar1 = substr($aux,2,1);
				$bar2 = substr($aux,5,1);
					if(ord($bar1) != 47 || ord($bar2) != 47)
					{ return 0; }
					else
					{
						$dia = substr($aux,0,2); 
						for ($x = 1 ; $x <= strlen($dia) ; $x++)
						{
							$pos = substr($dia,$x-1,1);
							if(ord($pos) >= 48 && ord($pos) <= 57)
							{ $alerta = 0; }
							else
							{ $alerta = 1; break;}
						}							
								if($alerta == 1) 
								{ return 0; }
								else
								{
									$mes = substr($aux,3,2); 
									for ($x = 1 ; $x <= strlen($mes) ; $x++)
									{
										$pos = substr($mes,$x-1,1);
										if(ord($pos) >= 48 && ord($pos) <= 57)
										{ $alerta = 0; }
										else
										{ $alerta = 1; break;}							
									}
										
										if($alerta == 1) 
										{ return  0; }
										else
										{
											$ano = substr($aux,6,4); 
											for ($x = 1 ; $x <= strlen($ano) ; $x++)
											{
												$pos = substr($ano,$x-1,1);
												if(ord($pos) >= 48 && ord($pos) <= 57)
												{ $alerta = 0; }
												else
												{ $alerta = 1; break;}							
											}
											
											if($alerta == 1) 
											{ return  0; }
											else	
											{ 
												if($mes > 12 || $dia > 31)
												{ return 0; }
												else
												{									
													if ((($ano % 4) == 0 and ($ano % 100) != 0) or ($ano % 400) == 0)
														{ $bissexto = 1; }
													else 
														{ $bissexto = 0; }
													
													if($bissexto == 0)
													{
														if(($mes == 4 || $mes == 6 || $mes == 9 || $mes == 11) && ($dia > 30))
														{ return 0; }
														else
														{
															if($mes == 2 && $dia > 28) 
															{ return 0; }
															else
															{ return 1; }														
														}
													}
													if($bissexto == 1)
													{
														if(($mes == 4 || $mes == 6 || $mes == 9 || $mes == 11) && ($dia > 30))
														{ return 0; }
														else
														{
															if($mes == 2 && $dia > 29) 
															{ return 0; }
															else
															{ return 1; }
														}
													}													
												}
											}											
										}															
								}																				
					}																		
		}			
}

function get_status_pedido($vg_id, &$recibo = null) {

	if(!$vg_id) {
		return -1;
	}
	$sql = "select * from tb_recarga_pedidos_rede_sim where rprs_vg_id = $vg_id order by rprs_data_inclusao desc limit 1";
	$rs = SQLexecuteQuery($sql);
	if(!$rs || pg_num_rows($rs) == 0) {
		echo "Nenhum produto encontrado ($sql).\n";
		return -2;
	} else {
		$rs_row = pg_fetch_array($rs);
		$status = $rs_row['rprs_status'];
		if($status=="1") {
			// Pedido processado
			$recibo = $rs_row['rprs_recibo'];
			return "1";
		} elseif($status=="N") {
			// Pedido recusado
			return "N";
		} elseif($status=="0") {
			// Pedido pendente de procesamento
			return "0";
		} else {
			// Status desconhecido
			return $status;
		}
	}
}

function get_status_pedido_seguro($vg_id, &$recibo = null) {

	if(!$vg_id) {
		return -1;
	}
	$sql = "select * from tb_seguro_pedidos_rede_sim where sprs_vg_id = $vg_id order by sprs_data_inclusao desc limit 1";
	$rs = SQLexecuteQuery($sql);
	if(!$rs || pg_num_rows($rs) == 0) {
		echo "Nenhum produto encontrado ($sql).\n";
		return -2;
	} else {
		$rs_row = pg_fetch_array($rs);
		$status = $rs_row['sprs_status'];
		if($status=="1") {
			// Pedido processado
			$recibo = $rs_row['sprs_recibo'];
			return "1";
		} elseif($status=="N") {
			// Pedido recusado
			return "N";
		} elseif($status=="0") {
			// Pedido pendente de procesamento
			return "0";
		} else {
			// Status desconhecido
			return $status;
		}
	}
}

function get_dados_da_Lan($ug_id, &$params) {
	$params = array();
	$sql  = "select ug_ativo, ug_tipo_cadastro, ug_perfil_limite, ug_perfil_saldo, ug_risco_classif from dist_usuarios_games where ug_id = " . $ug_id;
//echo "$sql\n";
	$rs_lan = SQLexecuteQuery($sql);
	if(!$rs_lan || pg_num_rows($rs_lan) == 0) {
	} else {
		$rs_lan_row = pg_fetch_array($rs_lan);
		$params['ug_ativo']			= $rs_lan_row['ug_ativo'];
		$params['ug_risco_classif']	= $rs_lan_row['ug_risco_classif'];
		$params['ug_perfil_limite']	= $rs_lan_row['ug_perfil_limite'];
		$params['ug_perfil_saldo']	= $rs_lan_row['ug_perfil_saldo'];
	}
}
/*
		dados a lan: ".$params['ug_id']."
		data/hora: ".$params['datahora']."
		celular: ".$params['celular']."
		valor: ".$params['valor']."
		email: ".$params['email']."
*/

function envia_email_recarga_redesim($params) {

	$stexto = "Você completou a recarga no seu cellar com sucesso!!!
		dados a lan: ".$params['ug_id']."
		data/hora: ".$params['datahora']."
		celular: ".$params['celular']."
		valor: ".number_format($params['valor'], 2, '.', '.')."
		email: ".$params['email']."
		comprovante: <pre>".$params['comprovante']."</pre>

	Dúvidas, sugestões?
	11 4063-0656, 11 3030-9101, msn: atendimento1@e-prepag.com.br ou suporte@e-prepag.com.br"; 

	$parametros = array();	
	$parametros['prepag_dominio'] = "https://www.e-prepag.com.br";


	$msg_data = str_replace("\r\n","\n",$stexto);
	$msg_data = str_replace("\r","\n",$msg_data);
	$msg_data = str_replace("\n","<br>\n",$msg_data);

	$msgEmail = $msg_data;
	$msgEmail .= email_rodape($parametros);
	$subjectEmail = "E-Prepag - Compra de recarga celular!!!";
	$bcc = null;

	if(trim($params['email'])) {
		enviaEmail($params['email'], null, $bcc, $subjectEmail, $msgEmail);
		echo " = Enviado Email (".date("Y-m-d H:i:s").") para ".$params['email']." (id da Lan: ".$params['ug_id'].")\n";
	}

}

?>