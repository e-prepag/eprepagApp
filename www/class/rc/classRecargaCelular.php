<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

require_once $raiz_do_projeto . "includes/inc_register_globals.php";	
require_once $raiz_do_projeto . "db/connect.php";

$ARQUIVO_RC_VECTOR_OPERADORAS = $raiz_do_projeto . "/includes/rc/inc_vector.php";
$ARQUIVO_RC_MONITOR = $raiz_do_projeto . "/includes/rc/inc_monitor.txt";

include $ARQUIVO_RC_VECTOR_OPERADORAS;

if(php_sapi_name()=="isapi") {
	$cReturn = "<br>\n";
} else {
	$cReturn = "\n";
}

//$a_lista_ddds = array('11', '12', '13', '14', '15', '16', '17', '18', '19', '21', '22', '24', '27', '28', '31', '32', '33', '34', '35', '37', '38', '41', '42', '43', '44', '45', '46', '47', '48', '49', '51', '53', '54', '55', '61', '62', '63', '64', '65', '66', '67', '68', '69', '71', '73', '74', '75', '77', '79', '81', '82', '83', '84', '85', '86', '87', '88', '89', '91', '92', '93', '94', '95', '96', '97', '98', '99');
$a_lista_ddds = array('11', '12', '13', '14', '15', '16', '17', '18', '19', '21', '22', '24', '27', '28', '31', '32', '33', '34', '35', '37', '38', '41', '42', '43', '44', '45', '46', '47', '48', '49', '51', '53', '54', '55', '61', '62', '63', '64', '65', '66', '67', '68', '69', '71', '73', '74', '75', '77', '79', '81', '82', '83', '84', '85', '86', '87', '88', '89', '91', '92', '93', '94', '95', '96', '97', '98', '99');
/*
$a_lista_ddds = array();
for($i=1;$i<100;$i++) {	$a_lista_ddds[] = (($i<10)?"0":"")."$i";}
//echo "<hr><pre style='background-color:#CCCCFF;color:darkgreen'>".print_r($a_lista_ddds, true)."</pre><hr>";
*/
/*
echo "<hr>Lista de DDDs<br>";
for($i=0;$i<count($a_lista_ddds);$i++) {	echo $a_lista_ddds[$i].", ";}
echo "<hr>";
*/
class RecargaCelular {

	// Parámetros de configuracao
	var $rc_configuracao = array(
			'identificador' => '58285',
			'senha'	=> 'teste',
			'url_pontocerto' => 'http://10.22.1.1:8081/jscom/', 
/*	Antes de 2012/04/26
			'identificador' => '54627',
			'senha'	=> 'teste',
			'url_pontocerto' => 'http://10.22.1.1:8081/jscom/', //'http://www.e-prepag.com.br/prepag2/rc/pc.php',
*/
/*
			'identificador' => '58285',
			'senha'	=> 'teste',
			'url_pontocerto' => 'http://200.230.208.21:8080/jscom', 
*/
		);

	// 
	var $bSimul;


	// Variáveis gerais
	var $op_id;
	var $codigoOperadora;
	var $codigoRede;
	var $ddd;
	var $codigoProduto;
	var $numeroCelular;
	var $numeroCelularConf;
	var $valor;
	var $versaoFilial;
	var $versaoOperadora;

	var $vg_id;
	var $ug_id;
	var $data_inclusao;
	var $rp_status;
	var $rp_statustransacao;
	var $recibo;
	var $params_atualiza;

	// Variáveis da API Ponto Certo

	// URLs
	var $URL_ConsultaOperadoras_producao;
	var $URL_ConsultaOperadoras_homologacao;
	var $URL_ConsultaValores_producao;
	var $URL_ConsultaValores_homologacao;
	var $URL_SolicitacaoRecarga_producao;
	var $URL_SolicitacaoRecarga_homologacao;

	// Parametros válidos
	var $parameters = array(
			"AtualizaOperadorasValores" => array(),
			"ConsultaOperadoras" => array("identificador", "senha"),
			"ConsultaValores" => array("identificador", "senha", "codigoOperadora", "codigoRede", "ddd"),
			"SolicitacaoRecarga" => array("identificador", "senha", "codigoOperadora", "codigoRede", "codigoProduto", "numeroCelular", "numeroCelularConf", "valor", "versaoFilial", "versaoOperadora"),
		);
	var $retorno = array( 
			"AtualizaOperadorasValores" => array(),
			"ConsultaOperadoras" => array("sequencial", "descricaoStatus", "versaoOperadora", "operadoras", "statusTransacao", "codigoMensagem", "checksum"),
			"ConsultaValores" => array("codigoMensagem", "sequencial", "descricaoStatus", "tamanhoDV", "valoresFixos", "valorMinimo", "valorMaximo", "versaoFilial", "statusTransacao", "checksum"),
			"SolicitacaoRecarga" => array("codigoMensagem", "sequencial", "statusTransacao", "descricaoStatus", "recibo"),
		);

	var $retorno_ConsultaOperadoras_operadoras = array("codigoProduto", "codigoOperadora", "codigoRede", "nomeOperadora", "urlLogo");

	var $retorno_ConsultaValores_valoresFixos = array("valor", "valorBonus");

	var $retorno_Erro_de_negocio  = array("codigoMensagem", "sequencial", "statusTransacao", "descricaoStatus");

	// Metodos 
	function RecargaCelular() {
//		echo "Loading ".$GLOBALS['cReturn']."";

		$this->bSimul = false;
//echo "<p><font ".(($this->bSimul)?"color='red'":"").">Conexão com Ponto Certo: ".(($this->bSimul)?"simulação":"direta")." (".$this->rc_configuracao['url_pontocerto'].")</font></p>";

		// Le do POST de formulario
		$this->getFormData();
		$this->URL_ConsultaOperadoras_producao = $this->rc_configuracao['url_pontocerto'].(($this->bSimul)?"":"ConsultaOperadorasServlet");
		$this->URL_ConsultaOperadoras_homologacao = $this->rc_configuracao['url_pontocerto'].(($this->bSimul)?"":"ConsultaOperadorasServlet");
		$this->URL_ConsultaValores_producao = $this->rc_configuracao['url_pontocerto'].(($this->bSimul)?"":"ConsultaValoresOperadorasServlet");
		$this->URL_ConsultaValores_homologacao = $this->rc_configuracao['url_pontocerto'].(($this->bSimul)?"":"ConsultaValoresOperadorasServlet");
		$this->URL_SolicitacaoRecarga_producao = $this->rc_configuracao['url_pontocerto'].(($this->bSimul)?"":"RecargaCelularTransactionServlet");
		$this->URL_SolicitacaoRecarga_homologacao = $this->rc_configuracao['url_pontocerto'].(($this->bSimul)?"":"RecargaCelularTransactionServlet");

		_doConnect();

	}

	// Processa dados do REQUEST 
	function getFormData() {
//echo "_POST['tf_op_id']: '".$GLOBALS['_POST']['tf_op_id']."'".$GLOBALS['cReturn']."";
		if(isset($GLOBALS['_POST']['tf_op_id']))
             $this->set_op_id($GLOBALS['_POST']['tf_op_id']);
 //echo "_POST['tf_op_id']: '".$GLOBALS['_POST']['tf_op_id']."'".$GLOBALS['cReturn']."";
 		if(!$this->op_id) $this->op_id = "ConsultaOperadoras";
         
        if(isset($GLOBALS['_POST']['codigoOperadora']))
             $this->codigoOperadora = $GLOBALS['_POST']['codigoOperadora'];
        
        if(isset($GLOBALS['_POST']['codigoRede']))
             $this->codigoRede = $GLOBALS['_POST']['codigoRede'];
 //echo "this->codigoRede (ABCD): '".$this->codigoRede."'".$GLOBALS['cReturn']."";
        if(isset($GLOBALS['_POST']['DDD']))
             $this->ddd = $GLOBALS['_POST']['DDD'];
 //echo "this->DDD (ABCD): '".$this->ddd."'".$GLOBALS['cReturn']."";
        if(isset($GLOBALS['_POST']['codigoProduto']))
             $this->codigoProduto = $GLOBALS['_POST']['codigoProduto'];
        if(isset($GLOBALS['_POST']['numeroCelular']))
             $this->numeroCelular = $GLOBALS['_POST']['numeroCelular'];
        if(isset($GLOBALS['_POST']['numeroCelularConf']))
             $this->numeroCelularConf = $GLOBALS['_POST']['numeroCelularConf'];
        if(isset($GLOBALS['_POST']['valor']))
             $this->valor = $GLOBALS['_POST']['valor'];
        if(isset($GLOBALS['_POST']['versaoFilial']))
             $this->versaoFilial = $GLOBALS['_POST']['versaoFilial'];
        if(isset($GLOBALS['_POST']['versaoOperadora']))
             $this->versaoOperadora = $GLOBALS['_POST']['versaoOperadora'];

		// Debug - Form
//		echo "op_id: '".$this->op_id."'".$GLOBALS['cReturn']."";

	}
	// Carrega ddos de Consulta com id_venda vg_id
	function get_new_idvenda() {

		$b_unique = false;
		$iloop = 1;
		do{
			$vg_id = rand(1, 1e7-1);

			$sql = "select * from tb_recarga_pedidos where rp_vg_id = $vg_id order by rp_data_inclusao desc limit 1";
//echo "[$iloop] $sql<br>";
			$rs = SQLexecuteQuery($sql);
			if(!$rs || pg_num_rows($rs) == 0) {
				$b_unique = true;
				break;
			} else {
				$b_unique = false;
			}
		} while((!$b_unique) && (($iloop++)<10));
//echo "Encontrou vg_id = $vg_id em $iloop tentativa(s)<br>";
		return $vg_id;
	}

	// Carrega ddos de Consulta com id_venda vg_id
	function carregaPedido($vg_id) {

		if(!$vg_id) {
			return -1;
		}
		$sql = "select * from tb_recarga_pedidos where rp_vg_id = $vg_id order by rp_data_inclusao desc limit 1";
//echo "carregaPedido($sql)<br>\n";
		$rs = SQLexecuteQuery($sql);
		if(!$rs || pg_num_rows($rs) == 0) {
			echo "Nenhum produto encontrado ($sql).\n";
			return -2;
		} else {
			$rs_row = pg_fetch_array($rs);

//echo "Carregou consulta $vg_id  (valor: ".$rs_row['rp_valor'].")\n";
			if(isset($rs_row['tf_op_id']))
                 $this->set_op_id($this->translate_op_id($rs_row['tf_op_id']));
 //			if(!$this->op_id) $this->op_id = "ConsultaOperadoras";
 
            if(isset($rs_row['rp_codigooperadora']))
                 $this->codigoOperadora = $rs_row['rp_codigooperadora'];
            
            if(isset($rs_row['rp_versaooperadora']))
                 $this->versaoOperadora = $rs_row['rp_versaooperadora'];
            
            if(isset($rs_row['rp_codigorede']))
                 $this->codigoRede = $rs_row['rp_codigorede'];
            
            if(isset($rs_row['rp_ddd']))
                 $this->ddd = $rs_row['rp_ddd'];
            
            if(isset($rs_row['rp_codigoproduto']))
                 $this->codigoProduto = $rs_row['rp_codigoproduto'];
            
            if(isset($rs_row['rp_numerocelular']))
                 $this->numeroCelular = $rs_row['rp_numerocelular'];
            
            if(isset($rs_row['rp_numerocelularconf']))
                 $this->numeroCelularConf = $rs_row['rp_numerocelularconf'];
            
            if(isset($rs_row['rp_valor']))
                 $this->valor = $rs_row['rp_valor'];
            
            if(isset($rs_row['rp_versaofilial']))
                 $this->versaoFilial = $rs_row['rp_versaofilial'];
            
            if(isset($rs_row['rp_versaooperadora']))
                 $this->versaoOperadora = $rs_row['rp_versaooperadora'];
            
            if(isset($rs_row['rp_data_inclusao']))
                 $this->data_inclusao = $rs_row['rp_data_inclusao'];
             
            if(isset($rs_row['rp_vg_id']))
                 $this->vg_id = $rs_row['rp_vg_id'];
            
            if(isset($rs_row['rp_ug_id']))
                 $this->ug_id = $rs_row['rp_ug_id'];
            
            if(isset($rs_row['rp_status']))
                 $this->rp_status = $rs_row['rp_status'];
            
            if(isset($rs_row['rp_statustransacao']))
                 $this->rp_statustransacao = $rs_row['rp_statustransacao'];
            
            if(isset($rs_row['recibo']))
                 $this->recibo = $rs_row['recibo'];

			// Debug - Form
//echo "op_id: '".$this->op_id."'".$GLOBALS['cReturn']."";
			return 0;
		}
		return -3;
	}

	function get_Select_Operacao($op_id) {

		$sret = "";

		$sret = "<select name='tf_op_id' id='tf_op_id'>\n";
		$sret .= "<option value='AtualizaOperadorasValores'".(($op_id=="AtualizaOperadorasValores")?" selected":"").">AtualizaOperadorasValores</option>\n";
		$sret .= "<option value='ConsultaOperadoras'".(($op_id=="ConsultaOperadoras")?" selected":"").">ConsultaOperadoras</option>\n";
		$sret .= "<option value='ConsultaValores'".(($op_id=="ConsultaValores")?" selected":"").">ConsultaValores</option>\n";
		$sret .= "<option value='SolicitacaoRecarga'".(($op_id=="SolicitacaoRecarga")?" selected":"").">SolicitacaoRecarga</option>\n";
		$sret .= "</select>\n";

		return $sret;

	}

	function is_valid_op_id($op_id) { 

		$bret = ($op_id=="AtualizaOperadorasValores" || $op_id=="ConsultaOperadoras" || $op_id=="ConsultaValores" || $op_id=="SolicitacaoRecarga"); 
//echo "op_id: '".$op_id."'  (is valid? ".(($bret)?"YES":"Nope").")".$GLOBALS['cReturn']."";
		return($bret); 
	}

	// 'A'/'a' -> 'AtualizaOperadorasValores'
	// 'O'/'o' -> 'ConsultaOperadoras'
	// 'V'/'v' -> 'ConsultaValores'
	// 'R'/'r' -> 'SolicitacaoRecarga'
	function translate_op_id($op_id) { 

		$sret = "";
		switch($op_id) {
			case 'A':
			case 'a':
				$sret = "AtualizaOperadorasValores"; break;
			case 'O':
			case 'o':
				$sret = "ConsultaOperadoras"; break;
			case 'V':
			case 'v':
				$sret = "ConsultaValores"; break;
			case 'R':
			case 'r':
				$sret = "SolicitacaoRecarga"; break;
		}
		return($sret); 
	}

	// Métodos Get/Set 
	function get_op_id() { return $this->op_id;	}
	function set_op_id($op_id) { if($this->is_valid_op_id($op_id)) $this->op_id = $op_id; }

	// retorna os query string
	function get_params($op){
		$sret = "";
		if($op=="AtualizaOperadorasValores") {

		} elseif($op=="ConsultaOperadoras") {
			if($this->bSimul) {
				$sret .= "op=ConsultaOperadorasServlet&";
			}
			$sret .= "identificador=".$this->rc_configuracao['identificador']."&senha=".$this->rc_configuracao['senha'];

		} elseif($op=="ConsultaValores") {
			if($this->bSimul) {
				$sret .= "op=ConsultaValoresOperadorasServlet&";
			}
			$sret .= "identificador=".$this->rc_configuracao['identificador']."&senha=" . $this->rc_configuracao['senha'] . "&codigoOperadora=" . $this->codigoOperadora . "&codigoRede=" . $this->codigoRede . "&ddd=" . $this->ddd."";

		} elseif($op=="SolicitacaoRecarga") {
			if($this->bSimul) {
				$sret .= "op=RecargaCelularTransactionServlet&";
			}
			$sret .= "identificador=".$this->rc_configuracao['identificador'] . "&senha=" . $this->rc_configuracao['senha'] . "&codigoOperadora=" . $this->codigoOperadora . "&codigoRede=" . $this->codigoRede . "&codigoProduto=" . $this->codigoProduto . "&numeroCelular=" . $this->ddd . $this->numeroCelular . "&numeroCelularConf=" . $this->ddd . $this->numeroCelular . "&valor=" . $this->valor*100 . "&versaoFilial=" . $this->versaoFilial . "&versaoOperadora=" . $this->versaoOperadora . "";

		} else {
			echo "Operação desconhecida ABC('".$op."')".$GLOBALS['cReturn']."";
		}
		return $sret;		
	}
	// Métodos Processa
	// =================================================
	function process_AtualizaOperadorasValores() {

		set_time_limit ( 3000 ) ;
		$time_start = getmicrotime();

		echo "Processa AtualizaOperadorasValores (".date("Y-m-d H:i:s").")".$GLOBALS['cReturn']."";
		$url = $this->URL_ConsultaOperadoras_producao;
		$querystring = $this->get_params("ConsultaOperadoras");
//echo "URL: '$url'".$GLOBALS['cReturn']."";
//echo "querystring: '$querystring'".$GLOBALS['cReturn']."";

		// registra consulta
		$params = array();
			$params['smsg'] = $querystring;
			$params['result'] = '0';
		$this->salvaConsulta('a', $params);
		$this->salvaConsulta('o', $params);

		$sret = $this->getCURL($url, $querystring);

		if($this->bSimul) {
			$sret_json = substr($sret, strpos($sret, "Content-type: text/html")+23);
		} else {
			$ipos_first = strpos($sret, "{") - 1;
			$ipos_last = strpos($sret, "}") + 1;
			$sret_json = trim(substr($sret, $ipos_first));
		}
echo "<hr><div style='background-color:#CCFFCC'>".$sret_json."</div>";
		$aret = json_decode($sret_json, true);
//echo "aret: <pre>".print_r($aret, true)."</pre>";
//echo "aret: <pre>".print_r($aret['operadoras'], true)."</pre>";
//echo "statusTransacao: ".$aret['statusTransacao']."<be>";

		// registra retorno 
		$params = array();
			if($aret['statusTransacao']!="0") {
				$params['smsg'] = "descricaoStatus: '".$aret['descricaoStatus']."', statusTransacao: '".$aret['statusTransacao']."', codigoMensagem: '".$aret['codigoMensagem']."', ";
				$params['result'] = $aret['statusTransacao'];
				$operadoras = array();
			} else {
//				$operadoras = $aret['operadoras'];
				$versaoOperadora = $aret['versaoOperadora'];
				$operadoras = array();
				foreach($aret['operadoras'] as $key => $val) {
					$codigoOperadora = $val['codigoOperadora'];
					$operadoras[$codigoOperadora] = $val;
					$operadoras[$codigoOperadora]['versaoOperadora'] = $versaoOperadora;
				}
				$params['smsg'] = json_encode($operadoras);
				$params['result'] = $aret['statusTransacao'];
			}
//echo "OPERADORAS <pre>".print_r($operadoras, true)."</pre>";
//echo "<pre>".print_r($params, true)."</pre>";
		$this->salvaConsulta('O', $params);

		// consulta valores para cada operadora
		if(is_array($operadoras) && ! is_null($operadoras)) {
			$n_operadoras = 0;
// ===================
/*
			// Debug
			echo "<pre style='background-color:#CCFFCC;color:darkgreen'>".print_r($aret, true)."</pre>";
			foreach($operadoras as $key => $val) {
				$codigoOperadora = $val['codigoOperadora'];
				$codigoRede = $val['codigoRede'];
				$codigoProduto = $val['codigoProduto'];
				echo "** '$codigoOperadora' - '$codigoRede' - '$codigoProduto' **<br>";
			}
echo "<font color='red'>SUSPENDE EXECUÇÃO DE process_AtualizaOperadorasValores()</font><br>\n";
			return 0;
*/
// ===================

			$smsg_ddd_invalido = "";
//echo "Cadastro operadoras acabado de obter: <pre>".print_r($val, true)."</pre>";
//echo "Cadastro operadoras acabado de obter: <pre>".print_r($operadoras, true)."</pre>";
echo "Cadastro operadoras acabado de obter: nOperadoras: ".count($operadoras)."<br>";

			foreach($operadoras as $key => $val) {
				$n_ddds = 0;
				$versaoOperadora = $val['versaoOperadora'];
				$codigoOperadora = $val['codigoOperadora'];
				$codigoRede = $val['codigoRede'];

				foreach($GLOBALS['a_lista_ddds'] as $key_ddd => $val_ddd) {
echo "<hr color='red'>Processa codigoOperadora (elapsed time: ".number_format(getmicrotime() - $time_start, 2, '.', '.')."s): ".$val['codigoOperadora']." (prod: ".$val['codigoProduto'].") - '".$val['nomeOperadora']."' [rede: ".$val['codigoRede']."], DDD: '$val_ddd'".$GLOBALS['cReturn']."\n";

echo "Operadora ### [$key] -> versaoOperadora: '$versaoOperadora', codigoOperadora: '$codigoOperadora', codigoRede: '$codigoRede'<br>";
				
					$this->codigoOperadora = $codigoOperadora;
					$this->codigoRede = $codigoRede;
					$this->ddd = $val_ddd;	//"11";
				
					$url = $this->URL_ConsultaValores_producao;
					$querystring = $this->get_params("ConsultaValores");
	//echo "URL: '$url'".$GLOBALS['cReturn']."\n";
//echo "params: '$params'".$GLOBALS['cReturn']."\n";
					// registra consulta
					$params = array();
						$params['smsg'] = $querystring;
						$params['result'] = '0';
						$params['operadora'] = $codigoOperadora;
						$params['rede'] = $codigoRede;
					$this->salvaConsulta('v', $params);
	
echo "getCURL[<span style='background-color:yellow'>$url, $querystring</span>]<br>";
					$sret = $this->getCURL($url, $querystring);

					if($this->bSimul) {
						$sret_json = substr($sret, strpos($sret, "Content-type: text/html")+23);
					} else {
						$ipos_first = strpos($sret, "{") - 1;
						$ipos_last = strpos($sret, "}") + 1;
						$sret_json = trim(substr($sret, $ipos_first));
					}
//echo "<hr>$$$$$<div style='background-color:#CCFFCC'>".$sret_json."</div>";
					$aret = json_decode($sret_json, true);
echo "****<pre>".print_r($aret, true)."</pre><hr style='color:blue'>";

					// registra retorno 
					$params = array();
						if($aret['statusTransacao']!="0") {
							$params['smsg'] = "descricaoStatus: '".$aret['descricaoStatus']."', statusTransacao: '".$aret['statusTransacao']."', codigoMensagem: 	'".$aret['codigoMensagem']."', ";
							$params['result'] = $aret['statusTransacao'];
$smsg_ddd_invalido .= "<div style='background-color:yellow; color:red'>codigoOperadora: '".$codigoOperadora."', codigoRede: '".$codigoRede."', DDD: '".$val_ddd."'</div>\n";

						} else {
							$valoresFixos = $aret['valoresFixos'];
//echo "**** <pre>".print_r($valoresFixos, true)."</pre>";
							$operadoras[$key]['valoresPorDDD'][$val_ddd] = array(
								'valoresFixos' => $valoresFixos,
								'valorMinimo' => $aret['valorMinimo'],
								'valorMaximo' => $aret['valorMaximo'],
								'versaoFilial' => $aret['versaoFilial'],
								'versaoOperadora' => $versaoOperadora,
								);
echo "<span style='background-color:yellow; color:blue'>valorMinimo: '".$aret['valorMinimo']."', valorMaximo: '".$aret['valorMaximo']."'</span><br>";
							$params['smsg'] = json_encode($valoresFixos);
							$params['result'] = $aret['statusTransacao'];
							$params['operadora'] = $aret['codigoOperadora'];
							$params['rede'] = $aret['codigoRede'];
							$params['urlLogo'] = $aret['codigoRede'];
							$params['ddd'] = $val_ddd;
						}
					$this->salvaConsulta('V', $params);
		
//					// Dummy
//					if((++$n_ddds)>10) {
//						echo "DUMMY: Apenas $n_ddds DDDs para cada operadora<br>";
//						break;
//					}
				}
//				// Dummy
//				if((++$n_operadoras)>200) {
//					echo "DUMMY: Apenas $n_operadoras Operadoras<br>";
//					break;
//				}
			}
		} else {
			echo "<p style='color:red'>Não retornou um array URL: '$url' - params: '$params'</p>";
		}
echo "Cadastro operadoras Terminou: nOperadoras: ".count($operadoras)."<br>";
echo "<pre>".print_r($operadoras, true)."</pre>";
//echo "<hr>Resultado: <div style='background-color:#FFCC66'>".json_encode($operadoras)."</div>";

		// registra retorno da atualização
		$params = array();
			if($aret['statusTransacao']!="0") {
				$params['smsg'] = "descricaoStatus: '".$aret['descricaoStatus']."', statusTransacao: '".$aret['statusTransacao']."', codigoMensagem: '".$aret['codigoMensagem']."', ";
				$params['result'] = $aret['statusTransacao'];
			} else {
				$params['smsg'] = json_encode($operadoras);
				$params['result'] = $aret['statusTransacao'];
			}

		$this->salvaConsulta('A', $params);
		$s_elapsed_time = number_format(getmicrotime() - $time_start, 2, '.', '.');
		$this->salvaVetorOperadoras($operadoras, $s_elapsed_time);

//echo "<hr>MSGs 'DDD invalido (RPC0036)'<br>".$smsg_ddd_invalido."<hr>";

echo "<hr><span style='color:darkgreen'>Elapsed time: ".number_format(getmicrotime() - $time_start, 2, '.', '.')."s</span><br>";
gravaLog_RC_processing("AtualizaOperadorasValores: \n".print_r(json_encode($operadoras), true)."\n".print_r($this, true));

	}
	// =================================================
	function process_ConsultaOperadoras() {
		echo "Processa ConsultaOperadoras (".date("Y-m-d H:i:s").")".$GLOBALS['cReturn']."";
		$url = $this->URL_ConsultaOperadoras_producao;
		$querystring = $this->get_params("ConsultaOperadoras");
//echo "URL: '$url'".$GLOBALS['cReturn']."";
//echo "querystring: '$querystring'".$GLOBALS['cReturn']."";
//die("Stop SSS");
		// registra consulta
		$params = array();
			$params['smsg'] = $querystring;
			$params['result'] = '0';
//echo "<pre>".print_r($params, true)."</pre>";
		$this->salvaConsulta('o', $params);

		$sret = $this->getCURL($url, $querystring);

echo "<hr><div style='background-color:#CCFFCC'><pre>".$sret."</pre></div>";

		if($this->bSimul) {
			$sret_json = substr($sret, strpos($sret, "Content-type: text/html")+23);
		} else {
			$ipos_first = strpos($sret, "{") - 1;
			$ipos_last = strpos($sret, "}") + 1;
			$sret_json = trim(substr($sret, $ipos_first));
		}
//echo "<hr><div style='background-color:#CCFFCC'>".$sret_json."</div>";
		$aret = json_decode($sret_json, true);
//echo "<pre>".print_r($aret, true)."</pre>";
//echo "<pre>".print_r($operadoras, true)."</pre>";

		// registra retorno 
		$params = array();
			if($aret['statusTransacao']!="0") {
/*
	Em caso de erro
	"descricaoStatus": "For input string: \"EPP\"",
   "statusTransacao": 99,
   "codigoMensagem": -99
 */
				$params['smsg'] = "descricaoStatus: '".$aret['descricaoStatus']."', statusTransacao: '".$aret['statusTransacao']."', codigoMensagem: '".$aret['codigoMensagem']."', ";
				$params['result'] = $aret['statusTransacao'];
				$operadoras = array();
			} else {
				$operadoras = $aret['operadoras'];
				$params['smsg'] = json_encode($operadoras);
				$params['result'] = $aret['statusTransacao'];
			}
//echo "<pre>".print_r($params, true)."</pre>";
		$this->salvaConsulta('O', $params);

//		echo '<p>Last error: ', $GLOBALS['json_errors'][json_last_error()], "</p>";

gravaLog_RC_processing("ConsultaOperadoras: \n".print_r(json_encode($operadoras), true));
	}
	// =================================================
	function process_ConsultaValores() {
		echo "Processa ConsultaValores (".date("Y-m-d H:i:s").")".$GLOBALS['cReturn']."";
		$url = $this->URL_ConsultaValores_producao;
		$querystring = $this->get_params("ConsultaValores");
//echo "URL: '$url'".$GLOBALS['cReturn']."";
//echo "querystring: '$querystring'".$GLOBALS['cReturn']."";
//echo "<pre>".print_r($this, true)."</pre>";

		// registra consulta
		$params = array();
			$params['smsg'] = $querystring;
			$params['result'] = '0';
			$params['operadora'] = $this->codigoOperadora;
			$params['rede'] = $this->codigoRede;
			$params['ddd'] = $this->ddd;
		$this->salvaConsulta('v', $params);

		$sret = $this->getCURL($url, $querystring);

		if($this->bSimul) {
			$sret_json = substr($sret, strpos($sret, "Content-type: text/html")+23);
		} else {
			$ipos_first = strpos($sret, "{") - 1;
			$ipos_last = strpos($sret, "}") + 1;
			$sret_json = trim(substr($sret, $ipos_first));
		}
//echo "<hr><div style='background-color:#CCFFCC'>".$sret_json."</div>";
		$aret = json_decode($sret_json, true);
//echo "<pre>".print_r($aret, true)."</pre>";

		// registra retorno 
		$params = array();

			if($aret['statusTransacao']!="0") {
				$params['smsg'] = "descricaoStatus: '".$aret['descricaoStatus']."', statusTransacao: '".$aret['statusTransacao']."', codigoMensagem: '".$aret['codigoMensagem']."', ";
				$params['result'] = $aret['statusTransacao'];
				$valoresFixos = "";
			} else {
				$valoresFixos = $aret['valoresFixos'];
//echo "<pre>".print_r($aret, true)."</pre>";
				$params['smsg'] = json_encode($valoresFixos);
				$params['result'] = $aret['statusTransacao'];
				$params['operadora'] = $aret['codigoOperadora'];
				$params['rede'] = $aret['codigoRede'];
			}

//echo "<pre>".print_r($params, true)."</pre>";
		$this->salvaConsulta('V', $params);


gravaLog_RC_processing("ConsultaValores: \n".print_r(json_encode($valoresFixos), true));
	}
/*
	// =================================================
	function process_SolicitacaoRecarga - Completa - com pedido e registro em BD($vg_id) {
		echo "Processa SolicitacaoRecarga (".date("Y-m-d H:i:s").")".$GLOBALS['cReturn']."";

		$this->vg_id = $vg_id;

		$url = $this->URL_SolicitacaoRecarga_producao;
		$querystring = $this->get_params("SolicitacaoRecarga");
//echo "URL: '$url'".$GLOBALS['cReturn']."";
//echo "querystring: '$querystring'".$GLOBALS['cReturn']."";

		// registra consulta
		$params = array();
			$params['smsg'] = $querystring;
			$params['result'] = '0';
			$params['operadora'] = $this->codigoOperadora;
			$params['rede'] = $this->codigoRede;
		$this->salvaConsulta('r', $params);

		$sret = $this->getCURL($url, $querystring);

		$sret_json = substr($sret, strpos($sret, "Content-type: text/html")+23);
echo "<hr><div style='background-color:#CCFFCC'>".$sret_json."</div>";
		$aret = json_decode($sret_json, true);
//echo "<pre>".print_r($aret, true)."</pre>";

		// registra retorno 
		$params = array();
			$params['smsg'] = json_encode($aret);
			$params['result'] = $aret['statusTransacao'];
			$params['operadora'] = $this->codigoOperadora;
			$params['rede'] = $this->codigoRede;
		$this->salvaConsulta('R', $params);
			$params['params'] = $querystring;
			$params['vg_id'] = $this->vg_id;
			$params['produto'] = $this->codigoProduto;
			$params['celular'] = $this->numeroCelular;
			$params['valor'] = $this->valor;
		$this->salvaPedido('R', $params);

gravaLog_RC_processing("SolicitacaoRecarga: \n".print_r(json_encode($aret), true));

	}
*/
	// =================================================
	function process_SalvaPedidoRecarga($vg_id) {
//		echo "Processa SalvaPedidoRecarga (".date("Y-m-d H:i:s").")".$GLOBALS['cReturn']."";

		$this->vg_id = $vg_id;

		// registra consulta
		$params = array();
			$params['smsg'] = '';	// não fazemos o pedido aqui, então não tem querystring
			$params['result'] = '0';
			$params['operadora'] = $this->codigoOperadora;
			$params['versaooperadora'] = $this->versaoOperadora;
			$params['versaofilial'] = $this->versaoFilial;
			$params['rede'] = $this->codigoRede;
			$params['ddd'] = $this->ddd;
		$this->salvaConsulta('r', $params);
			$params['params'] = '';	// não fazemos o pedido aqui, então não tem querystring
			$params['vg_id'] = $this->vg_id;
			$params['ug_id'] = $this->ug_id;
			$params['produto'] = $this->codigoProduto;
			$params['celular'] = $this->numeroCelular;
			$params['valor'] = $this->valor;
			$params['recibo'] = $this->recibo;
		$this->salvaPedido('R', $params);

//		$this->atualizaMonitor(	str_pad($this->vg_id,7,"0",STR_PAD_LEFT)." - ".date("Y-m-d H:i:s")."\n");
//		$this->atualizaMonitor(	getmicrotime().";".date("Y-m-d H:i:s")."\n");

gravaLog_RC_processing("PedidoRecarga - Pedido: \n".print_r(json_encode($params), true));

	}

	// =================================================
	function process_SolicitacaoRecarga($vg_id, &$params = null) {
//		echo "Processa SolicitacaoRecarga (A) (".date("Y-m-d H:i:s").")".$GLOBALS['cReturn']."";

//echo "<pre>".print_r($GLOBALS['_POST'], true)."</pre>";
//echo "Em process_SolicitacaoRecarga(): <pre>".print_r($params, true)."</pre>";
/*
    [ddd] => 27
    [mobilenumber] => 99999999
    [mobilenumberConf] => 99999999
    [provider] => 174
    [planId] => -1
    [planIdFlex] => 1234
    [versaoFilial] => 1
    [versaoOperadora] => 243
    [codigoRede] => 0
    [codigoProduto] => 63
*/
		if($params!=null) {
			$this->codigoOperadora = ((isset($params['provider']))?$params['provider']:"");
//			$this->codigoRede = $GLOBALS['_POST']['codigoRede'];
	//echo "this->codigoRede (ABCD): '".$this->codigoRede."'".$GLOBALS['cReturn']."";
			$this->ddd = ((isset($params['ddd']))?$params['ddd']:"");
	//echo "this->DDD (ABCD): '".$this->ddd."'".$GLOBALS['cReturn']."";
//			$this->codigoProduto = $GLOBALS['_POST']['codigoProduto'];
			$this->numeroCelular = ((isset($params['mobilenumber']))?$params['mobilenumber']:"");
			$this->numeroCelularConf = ((isset($params['mobilenumberConf']))?$params['mobilenumberConf']:"");
			/*
			if($params['planId']!="" && $params['planId']>=0)	{
				$valor = get_ValorFixo($params['provider'], $params['ddd'], $params['planId']);
			} else {
				$valor = $params['planIdFlex'];
			}
echo "valor solicitado: '".$valor."' <br>";
			$this->valor = $valor;
			*/
			$this->valor = $params['valor'];
			$this->versaoFilial = ((isset($params['versaoFilial']))?$params['versaoFilial']:"");
			$this->versaoOperadora = ((isset($params['versaoOperadora']))?$params['versaoOperadora']:"");
			$this->codigoOperadora = ((isset($params['codigoOperadora']))?$params['codigoOperadora']:"");
			$this->codigoProduto = ((isset($params['codigoProduto']))?$params['codigoProduto']:"");
			$this->codigoRede = ((isset($params['codigoRede']))?$params['codigoRede']:"");
		}
//echo "#-%-#<pre>".print_r($this, true)."</pre>";

		$this->vg_id = $vg_id;

		$url = $this->URL_SolicitacaoRecarga_producao;
		$querystring = $this->get_params("SolicitacaoRecarga");
//echo "URL: '$url'".$GLOBALS['cReturn']."";
//echo "querystring: '$querystring'".$GLOBALS['cReturn']."";

//die("Stop dssd");
		if($iret = $this->carregaPedido($vg_id)) {
			echo " **** Pedido não encontrado ao carregar (vg_id = $vg_id)\n";
			return -1;
		}

echo "\n  URL vai pedir \n    $url\n    $querystring\n";
		$sret = $this->getCURL($url, $querystring);
//echo "<br>\$this->getCURL<pre style='color:blue;background-color:#CCFF99'>".print_r($sret, true)."</pre>";
//echo "getCURL\n".print_r($sret, true)."\n";

		if($this->bSimul) {
			$sret_json = substr($sret, strpos($sret, "Content-type: text/html")+23);
		} else {
			$ipos_first = strpos($sret, "{") - 1;
			$ipos_last = strpos($sret, "}") + 1;
			$sret_json = trim(substr($sret, $ipos_first));
		}
//echo "<hr>sret_json: <br><div style='background-color:#CCFFCC'>".$sret_json."</div>";
		$aret = json_decode($sret_json, true);
echo "\n  aret: ".print_r($aret, true)."\n";

		// atualiza pedido com retorno 
		$params = array();
			$params['params'] = $querystring;
			$params['smsg'] = json_encode($aret);
//echo "<hr> aret['recibo']: <pre>".print_r($aret['recibo'], true)."</pre>";
if(isset($aret['recibo']) && ($aret['recibo']!="")) {
//	echo "  +++ RECIBO ".print_r($aret['recibo'], true)."\n";
} else {
	echo "  --- SEM RECIBO\n";
}
			$params['vg_id'] = $this->vg_id;
			$params['ug_id'] = $this->ug_id;
			$params['celular'] = $this->numeroCelular;
			$params['statusTransacao'] = $aret['statusTransacao'];
			$params['descricaoStatus'] = $aret['descricaoStatus'];
			$params['codigoMensagem'] = $aret['codigoMensagem'];
			$params['recibo'] = $aret['recibo'];

		if($aret['statusTransacao']=='0') {
			$params['status'] = '1';
			$params['data_recarga'] = date("Y-m-d H:i:s");
			$ret = true;
		} else {
			$params['status'] = 'N';
			$params['data_recarga'] = 'null';
			$ret = false;
		}

			$params['result'] = $aret['statusTransacao'];
		$this->atualizaPedido($vg_id, $params);


gravaLog_RC_processing("SolicitacaoRecarga: \n".print_r(json_encode($aret), true));

		return $ret;

	}

	// ================================================
	// Utils
	function getCURL($url, $post_parameters) {

		$buffer = "";
//echo "<hr color='blue'>Parameters em CURL: '<span style='color:blue;bakcground-color:yellow'>".$url."</span>', '<span style='color:darkgreen;bakcground-color:yellow'>".$post_parameters."</span>".$GLOBALS['cReturn']."";
		// http://blog.unitedheroes.net/curl/
		$curl_handle = curl_init();
		curl_setopt($curl_handle, CURLOPT_URL,$url);

		// Some sites may protect themselves from remote logins by checking which site you came from.
		// http://php.net/manual/en/function.curl-setopt.php
		$ref_url = "http://www.e-prepag.com.br";
		curl_setopt($curl_handle, CURLOPT_REFERER, $ref_url);
		
		// http://www.weberdev.com/get_example-4136.html
		// http://www.php.net/manual/en/function.curl-setopt.php
		curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, 0);	// true - verifica certificado
		curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, 0);	// 1 - então, também verifica nome no certificado

		curl_setopt($curl_handle, CURLOPT_HEADER, 1); 
		curl_setopt($curl_handle, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)"); 

		curl_setopt($curl_handle, CURLOPT_POST, 1);
		curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $post_parameters);

		// The number of seconds to wait while trying to connect. Use 0 to wait indefinitely.
		curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 30);		
		// The maximum number of seconds to allow cURL functions to execute.
		curl_setopt($curl_handle, CURLOPT_TIMEOUT, 30);		
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);

		$buffer = curl_exec($curl_handle);
/*
		// Em caso de erro - libera aqui
		$info = curl_getinfo($curl_handle);

		if ($output === false || $info['http_code'] != 200) {
		  $output = "No cURL data returned for URL [". $info['http_code']. "]";
		  if (curl_error($curl_handle)) {
			$output .= "\n". curl_error($curl_handle);
		  }
		  echo "CRL Error: ".$output."".$GLOBALS['cReturn']."Buffer: ".$buffer."\n";	  
	//echo "<pre>";
	//print_r($info);
	//echo "</pre>";
		} else {
		  // 'OK' status; format $output data if necessary here:
		  echo "CRL OK".$GLOBALS['cReturn']."\n";	  
		}
		// Em caso de erro - Até aqui
*/	
		curl_close($curl_handle);

//echo "<table border='0'><tr><td width='10%'>&nbsp;</td><td style='color:blue;background-color:#CCFF99'><pre>".$buffer."</pre></td></tr></table>".$GLOBALS['cReturn']."<hr color='blue'>";

		return $buffer;
	}

	// ================================================
	function salvaConsulta($tipo, $params) {

		 $smsg = $params['smsg'];

		// Sanitize 
		if((strpos(strtoupper($smsg), "DROP")===false) && (strpos(strtoupper($smsg), "DELETE")===false) && (strpos(strtoupper($smsg), "INSERT")===false) && (strpos(strtoupper($smsg), "CREATE")===false) && (strpos(strtoupper($smsg), "ALTER")===false)) {
//			$smsg = str_replace("drop", " ", str_replace("'", "''", $smsg));

//echo "Em salvaConsulta('$tipo'): <pre>".print_r($params, true)."</pre><br>\n";

			$sfield = ((ctype_lower($tipo))?"rc_parametros":"rc_retorno");
			$sql = "INSERT INTO tb_recarga_consultas (rc_tipo, $sfield, rc_StatusTransacao, rc_CodigoOperadora, rc_CodigoRede, rc_urlLogo, rc_ddd) ";
			$sql .= "VALUES ('$tipo', '".str_replace("'", "''", $params['smsg'])."', '".$params['result']."', '".$params['operadora']."', '".$params['rede']."', '".substr($params['urlLogo'], 0, 512)."', '".substr($params['ddd'], 0, 2)."');";
//echo "Salvando '$sql' ".$GLOBALS['cReturn']."";
			$rs = SQLexecuteQuery($sql);
			if(!$rs) {
echo "Erro ao Salvar Consulta '$sql' (1) ".$GLOBALS['cReturn']."";
			} else {
//echo "Consulta salva (A) ".$GLOBALS['cReturn']."";
			}
		}
	}
	// ================================================
	function salvaPedido($tipo, $params) {

		 $smsg = $params['smsg'];
//echo "VAI Salvar".$GLOBALS['cReturn']."";

		// Sanitize 
		if((strpos(strtoupper($smsg), "DROP")===false) && (strpos(strtoupper($smsg), "DELETE")===false) && (strpos(strtoupper($smsg), "INSERT")===false) && (strpos(strtoupper($smsg), "CREATE")===false) && (strpos(strtoupper($smsg), "ALTER")===false)) {
//			$smsg = str_replace("drop", " ", str_replace("'", "''", $smsg));

			$sfield = ((ctype_lower($tipo))?"rp_parametros":"rp_retorno");
			$sql = "INSERT INTO tb_recarga_pedidos (rp_tipo, $sfield, rp_statustransacao, rp_codigooperadora, rp_codigorede, rp_VersaoOperadora, rp_VersaoFilial, rp_codigoproduto, rp_numerocelular, rp_valor, rp_vg_id, rp_ug_id, rp_ddd, rp_parametros) ";
			$sql .= "VALUES ('$tipo', '".$params['smsg']."', '".$params['result']."', '".$params['operadora']."', '".$params['rede']."', ".$params['versaooperadora'].", ".$params['versaofilial'].", '".$params['produto']."', '".$params['celular']."', ".$params['valor'].", ".$params['vg_id'].", ".$params['ug_id'].", '".$params['ddd']."', '".$params['params']."');";
//echo "Salvando '$sql' ".$GLOBALS['cReturn']."";
			$rs = SQLexecuteQuery($sql);
			if(!$rs) {
echo "Erro ao Salvar '$sql' (2) ".$GLOBALS['cReturn']."";
			} else {
//echo "Pedido salvo(vg_id: ".$params['vg_id'].") ".$GLOBALS['cReturn']."";
			}
		} else {
echo "Erro ao Salvar '$sql' (2) ".$GLOBALS['cReturn']."";
		}
	}
	// ================================================
	function atualizaPedido($vg_id, $params) {
		// Salva para os casos onde precisa chamar atualizaPedido() depois de ROLLBACK
		$this->params_atualiza = $params;
		if($vg_id) {
			$data_recarga = (($params['data_recarga']=="null" || is_null($params['data_recarga']) || ($params['data_recarga']=="")) ? "null" : ("'".$params['data_recarga']."'"));
			$sql = "UPDATE tb_recarga_pedidos set rp_statustransacao = '".$params['statusTransacao']."', rp_status = '".$params['status']."', rp_parametros = '".$params['params']."', rp_data_recarga = ".$data_recarga." ";
			if(isset($params['recibo'])) {
				$sql .= ", rp_recibo = '".$params['recibo']."' ";
			}
			$sql .= " WHERE rp_vg_id = ".$params['vg_id']." and rp_numerocelular = '".$params['celular']."' and rp_status = '0'; ";

			$rs = SQLexecuteQuery($sql);
			if(!$rs) {
echo "Em atualizaPedido() - Erro ao atualizar pedido '$sql' (2) ".$GLOBALS['cReturn']."";
			} else {
//echo "Em atualizaPedido() - Pedido atualizado ('$sql')".$GLOBALS['cReturn']."";
			}
		} else {
echo "Em atualizaPedido() - NÃO Atualizou pedido por vg_id vazio: '$vg_id'".$GLOBALS['cReturn']."";
		}
	}

	
	// ================================================
	function salvaVetorOperadoras($a_operadoras, $s_elapsed_time) {
//echo "<hr><pre style='background-color:#CCCCFF;color:blue'>".print_r($a_operadoras, true)."</pre><hr>";
		if(count($a_operadoras)==0) {
			echo "<font color='red'>a_operadoras() vazio -> VetorOperadoras não foi salvo.</font><br>\n";
			return false;
		}
		$msg = "";
//die("Stop");
//$msg .= "//".str_repeat("=",80)."\n"; 
//		$time_start0 = getmicrotime();
		$msg .= "<?php \n";
		$msg .= "// created: ".date("Y-m-d H:i:s")." \n";
		$msg .= "// total found: ".count($a_operadoras)." oprs\n";
		$msg .= "// elapsed time: ".$s_elapsed_time."s\n";	
		$msg .= "\$operadoras_current_date_created = '".date("Y-m-d H:i:s")."'; \n";

		$msg .= "\$operadoras_current = array(\n";
		foreach($a_operadoras as $key => $val) {
			$msg .= "\t'$key' => array('codigoOperadora' => '".$val['codigoOperadora']."', 'codigoRede' => '".$val['codigoRede']."', 'nomeOperadora' => '".$val['nomeOperadora']."', 'urlLogo' => '".$val['urlLogo']."', 'codigoProduto' => '".$val['codigoProduto']."', ";
			$msg .= "\t'valoresPorDDD' => array(\n";
			foreach($val['valoresPorDDD'] as $key1 => $val1) {
				$msg .= "\t\t'$key1' => array(\n";
				$msg .= "\t\t\t'valoresFixos' => array(\n";
	//echo "<hr>a_operadoras['valoresFixos']: <pre>".print_r($val['valoresFixos'], true)."</pre><hr>";
				foreach($val1['valoresFixos'] as $key2 => $val2) {
					$msg .= "\t\t\t\tarray('valor' => '".$val2['valor']."', 'valorBonus' => '".$val2['valorBonus']."'),\n";
				}
				$msg .= "\t\t\t\t), \n";	// Termina valoresFixos
				$msg .= "\t\t\t'valorMinimo' => '".$val1['valorMinimo']."', 'valorMaximo' => '".$val1['valorMaximo']."', 'versaoFilial' => '".$val1['versaoFilial']."', 'versaoOperadora' => '".$val1['versaoOperadora']."', ";
				$msg .= "\t\t\t),\n";	
			}
			$msg .= "\t\t),\n";	// Termina valoresPorDDD
			$msg .= "\t),\n";	// Termina cada operadora
		}
		$msg .= ");\n";	// Termina $operadoras_current
//		$msg .= "// elapsed time: ".number_format((getmicrotime() - $time_start0), 2, '.', '.')."s\n";	
		$msg .= "\n?>\n";

//echo str_replace("\n", "".$GLOBALS['cReturn']."\n", ("\n// created: ".date("Y-m-d H:i:s")." \n".$msg));
		grava_inc_vector_file($msg);
//echo "<hr>".count($a_operadoras)."<pre style='background-color:#CCCCFF;color:blue'>".print_r($a_operadoras, true)."</pre><hr>";

	}

	// ================================================
	function getExtraFields_SolicitacaoRecarga_Config($vg_id) {
		$this->vg_id = $vg_id;

		// $codigoOperadora, $codigoRede, $codigoProduto, $numeroCelular, $numeroCelularConf, $valor, $versaoFilial, $versaoOperadora
/*
		if(!$codigoRede) $codigoRede = 2;
		if(!$codigoProduto) $codigoProduto = 321;
		if(!$numeroCelular) $numeroCelular = '98765432';
		if(!$numeroCelularConf) $numeroCelularConf = $numeroCelular;
		if(!$valor) $valor = 102;
		if(!$versaoFilial) $versaoFilial = '1.0';
		if(!$versaoOperadora) $versaoOperadora = '1.02';
*/
		$sret = "<hr>";
		$sret .= "vg_id : ".$this->vg_id."&nbsp;<input type='hidden' id='vg_id' name='vg_id' value='".$this->vg_id."'>".$GLOBALS['cReturn']."\n";
		$sret .= "codigoOperadora&nbsp;<select id='codigoOperadora' name='codigoOperadora' onChange='document.form1.submit();'>\n";
		if($val['codigoOperadora']=="") {
			$s_select = " selected";
		}
		$sret .= "<option value=''".$s_select.">Selecione uma operadora</option>\n";
		foreach($GLOBALS['operadoras_current'] as $key => $val) {
			$s_select = "";
			if($val['codigoOperadora']==$this->codigoOperadora) {
				$s_select = " selected";
				$this->codigoRede = $val['codigoRede'];
				$this->codigoProduto = $val['codigoProduto'];
			}
			$sret .= "<option value='".$val['codigoOperadora']."'".$s_select.">".$val['codigoOperadora']." - ".$val['nomeOperadora']."</option>\n";
		}
		$sret .= "</select>".$GLOBALS['cReturn']."\n";
		$sret .= "codigoRede : ".$this->codigoRede."&nbsp;<input type='hidden' id='codigoRede' name='codigoRede' value='".$this->codigoRede."'>".$GLOBALS['cReturn']."\n";

		$sret .= "codigoProduto : ".$this->codigoProduto."&nbsp;<input type='hidden' id='codigoProduto' name='codigoProduto' value='".$this->codigoProduto."'>".$GLOBALS['cReturn']."\n";
		$sret .= "numeroCelular&nbsp;<input type='text' name='numeroCelular' value='".$this->numeroCelular."'>".$GLOBALS['cReturn']."\n";
		$sret .= "numeroCelularConf&nbsp;<input type='text' name='numeroCelularConf' value='".$this->numeroCelularConf."'>".$GLOBALS['cReturn']."\n";
//		$sret .= "valor&nbsp;<input type='text' name='valor' value='".$this->valor."'>".$GLOBALS['cReturn']."\n";
		foreach($GLOBALS['operadoras_current'] as $key => $val) {
//			$sret .= "<option value='".$val['codigoOperadora']."'".(($val['codigoOperadora']==$this->codigoOperadora)?" selected":"").">".$val['codigoOperadora']." - ".$val['nomeOperadora']."</option>\n";
//echo "".$GLOBALS['cReturn']."codigoOperadora: ".$val['codigoOperadora']."".$GLOBALS['cReturn']."";
//echo "".$GLOBALS['cReturn']."this->codigoOperadora: ".$this->codigoOperadora."".$GLOBALS['cReturn']."";
			if($val['codigoOperadora']==$this->codigoOperadora) {
//echo "".$GLOBALS['cReturn']."codigoOperadora: ".$val['codigoOperadora']." (val: ".print_r($val, true)."".$GLOBALS['cReturn']."";
				if(is_array($val['valoresFixos'])) {
echo "COM valoresFixos: ";
					$sret .= "valor&nbsp;<select name='valor' onChange='document.form1.submit();'>\n";
					foreach($val['valoresFixos'] as $key1 => $val1) {
						//echo $val1['valor']." (".$val1['valorBonus']."), ";
						$sret .= "<option value='".$val1['valor']."'".(($this->valor==$val1['valor'])?" selected":"").">".$val1['valor']."</option>\n";
					}
					$sret .= "</select>\n";
				} else {
echo "SEM valoresFixos: ";
				}
			}
		}

//		$sret .= "versaoFilial&nbsp;<input type='text' name='versaoFilial' value='".$this->versaoFilial."'>".$GLOBALS['cReturn']."\n";
//		$sret .= "versaoOperadora&nbsp;<input type='text' name='versaoOperadora' value='".$this->versaoOperadora."'>".$GLOBALS['cReturn']."\n";
		return $sret;
	}

	function atualizaMonitor($msg) {
		//Arquivo
		$file = $GLOBALS['ARQUIVO_RC_MONITOR'];

		if (is_writable($file)) {
			//Grava mensagem no arquivo
			if ($handle = fopen($file, 'w')) {
				fwrite($handle, $msg);
				fclose($handle);
				echo "<p style='color:blue'>Salvou arquivo MONITOR</p>";
			}	
		} else {
			echo "<p style='color:red'>NÃO salvou arquivo MONITOR (arquivo protegido para escrita)</p>";
		}

	}

	function lista_config() {
		return $this->rc_configuracao['url_pontocerto']." (".$this->rc_configuracao['identificador'].")"; 
	}

}	// Class End
// =====================================
/*
// Define the errors.
$constants = get_defined_constants(true);
$json_errors = array();
if($constants) {
	foreach ($constants["json"] as $name => $value) {
		if (!strncmp($name, "JSON_ERROR_", 11)) {
			$json_errors[$value] = $name;
		}
	}
}
*/
// =========================================================
function gravaLog_RC_processing($mensagem){

	//Arquivo
	//$file = $GLOBALS['ARQUIVO_LOG_SQL_EXECUTE_QUERY'];
	$file = DIR_LOG . "log_recargaCelular.txt";

	//Mensagem
	$mensagem = str_repeat("=",80)."\n".date('Y-m-d H:i:s') . " " . $GLOBALS['_SERVER']["SCRIPT_FILENAME"] . "\n" . $mensagem . "\n";

	//Grava mensagem no arquivo
	if ($handle = fopen($file, 'a+')) {
		fwrite($handle, $mensagem);
		fclose($handle);
	} 

}

// =========================================================
function gravaLog_RC_processing_loop($mensagem){

	//Arquivo
	//$file = $GLOBALS['ARQUIVO_LOG_SQL_EXECUTE_QUERY'];
	$file = DIR_LOG . "log_recargaCelular_LOOP.txt";

	//Mensagem
	$mensagem = str_repeat("=",80)."\n".date('Y-m-d H:i:s') . " " . $GLOBALS['_SERVER']["SCRIPT_FILENAME"] . "\n" . $mensagem . "\n";

	//Grava mensagem no arquivo
	if ($handle = fopen($file, 'a+')) {
		fwrite($handle, $mensagem);
		fclose($handle);
	} 

}

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

// =========================================================
function _doConnect() {
	global $_config;

//echo "<pre>".print_r($_config, true)."<pre>";
	if($_config['db_type']=='PostgreSQL') {
		$_config['db_connid'] = pg_connect("host=".$_config['db_host']." port=".$_config['db_port']." dbname=".$_config['db_name']." user=".$_config['db_user']." password=".$_config['db_passw']."");
	}

	if(!$_config['db_connid']) {
	  echo "<font color='#FF0000'>No conection</font>".$GLOBALS['cReturn']."";
	  die("Stop".$GLOBALS['cReturn']."");
	} 
//	else {echo "<font color='#0000FF'>DB Conected</font>".$GLOBALS['cReturn']."&nbsp;";}
}


// =========================================================
if (!function_exists('getmicrotime')) {
	function getmicrotime() {
	   list($usec, $sec) = explode(" ", microtime());
	   return ((float)$usec + (float)$sec);
	}
}//end if (!function_exists('getmicrotime')) 

// =========================================================
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
/*
function grava_inc_vector_file($mensagem){

	//Arquivo
	$file = $GLOBALS['ARQUIVO_RC_VECTOR_OPERADORAS'];

	if (is_writable($file)) {
		//Grava mensagem no arquivo
		if ($handle = fopen($file, 'w')) {
			fwrite($handle, $mensagem);
			fclose($handle);
			echo "<p style='color:blue'>Salvou arquivo INC</p>";
		}	
	} else {
			echo "<p style='color:red'>NÃO salvou arquivo INC (arquivo protegido para escrita)</p>";
	}
}
*/
function grava_inc_vector_file($mensagem){

	if(php_sapi_name()=="isapi") {
		$cParagraphBlue_open = "<p style='color:blue'>";
		$cParagraphRed_open = "<p style='color:red'>";
		$cParagraph_close = "</p>";
		$cReturn = "<br>\n";
	} else {
		$cParagraphBlue_open = "";
		$cParagraphRed_open = "";
		$cParagraph_close = "\n";
		$cReturn = "\n";
	}

	$time_start = getmicrotime();
	//Arquivo
	$file = $GLOBALS['ARQUIVO_RC_VECTOR_OPERADORAS'];
	$file_name = basename($file); 
	$a_fname = explode(".", $file_name);
//echo "<pre>".print_r($a_fname, true)."</pre>";

	$srand =  str_pad(rand(1,999), 3, "0", STR_PAD_LEFT);
	$file_name_new = $a_fname[0]."_".date("YmdHis")."_".$srand.".".$a_fname[1];
//echo "file_name_new: '$file_name_new'<br>";
	$s_lmod = date("Y/m/d H:i:s", filemtime($file));
//echo "Last modified: " . $s_lmod."<br>";
	$file_name_old = $a_fname[0]."_".date("YmdHis", filemtime($file))."_".date("YmdHis").".".$a_fname[1];
//echo "file_name_old: '$file_name_old'<br>";

	$spath_new = str_replace($file_name, $file_name_new, $file);
	$file_old = str_replace($file_name, "bkp/".$file_name_old, $file);
//echo "<br>file: '$file'<br>spath_new: '$spath_new'<br>file_old: '$file_old'<br>";

//	if (is_writable($file)) {
		//Grava mensagem no arquivo
		if ($handle = fopen($spath_new, 'w')) {
			fwrite($handle, $mensagem);
			fclose($handle);
			echo $cParagraphBlue_open."Salvou novo arquivo INC ($spath_new)".$cParagraph_close;

			echo $cParagraphBlue_open."Rename ('$file' -> '$file_old')".$cParagraph_close;
			rename($file, $file_old);
			echo $cParagraphBlue_open."Rename ('$spath_new' -> '$file')".$cParagraph_close;
			rename($spath_new, $file);
		} else {
			echo $cParagraphRed_open."ERRO ao salvar novo arquivo INC ($spath_new)".$cParagraph_close;
		}	
//	} else {
//		echo $cParagraphRed_open."NÃO salvou arquivo INC (arquivo protegido para escrita)".$cParagraph_close;
//	}
	echo $cParagraphBlue_open."(Create and rename files - elapsed time: ".number_format(getmicrotime() - $time_start, 2, '.', '.')."s)".$cParagraph_close;
}


// =========================================================
if (!function_exists('SQLexecuteQuery')) {
	function SQLexecuteQuery($sql){
		global $_config, $varBlDebug;
		
		$lev = error_reporting (8); //NO WARRING!!

		if($varBlDebug){
			echo "".$GLOBALS['cReturn']."" . $sql . "".$GLOBALS['cReturn']."";
			if(substr($sql, 0, 6) == "select")	$ret = pg_query ($_config['db_connid'], $sql);
			else $ret = 1;
		} else {
			$ret = pg_query ($_config['db_connid'], $sql);
		}

		error_reporting ($lev); //DEFAULT!!

		if (strlen ($erro = pg_last_error($_config['db_connid']))) {
			$message  = date("Y-m-d H:i:s") . " ";
			$message .= "Erro: " . $erro . "".$GLOBALS['cReturn']."\n";
			$message .= "Query: " . $sql . "".$GLOBALS['cReturn']."\n";
			gravaLog_SQLexecuteQuery_epp_pos($message);
			//die($message);
		}

		return $ret;		
	}
}//end if (!function_exists('SQLexecuteQuery'))

	include $raiz_do_projeto . "class/rc/SimpleImage.php";

	$img_size_max = 50;
/*
	$img_src = "http://www.redepontocerto.com.br/rpc/imagens/operadoras/claro_logo.jpg";
	$img_dst = "claro_logo2.jpg";
	get_small_image($img_src, $img_dst, $img_size_max);

	$img_src = "http://www.redepontocerto.com.br/rpc/imagens/operadoras/nextel_logo.jpg";
	$img_dst = "nextel_logo2.jpg";
	get_small_image($img_src, $img_dst, $img_size_max);

	$img_src = "http://www.redepontocerto.com.br/rpc/imagens/operadoras/Oi_LOGO.jpg";
	$img_dst = "Oi_LOGO2.jpg";
	get_small_image($img_src, $img_dst, $img_size_max);
*/
function get_small_image($img_src, $img_dst, $img_size_max = 50, $bshow_src = true, $bshow_dst = true) {
	$sret = "";
//echo "<hr> '$img_src' =&gt; '$img_dst'<br>";

	$image = new SimpleImage();
	$image->load($img_src);
	
	$img_w = $image->getWidth();
	$img_h = $image->getHeight();
//echo "W: ".$img_w.", H: ".$img_h."<br>";
	if($img_w>=$img_h) {
		$img_w_new = (int)($img_w * $img_size_max /$img_w);
		$img_h_new = (int)($img_h * $img_size_max /$img_w);
	} else {
		$img_w_new = $img_w * $img_size_max /$img_h;
		$img_h_new = $img_h * $img_size_max /$img_h;
	}
//echo "New - W: ".$img_w_new.", H: ".$img_h_new."<br>";

	$image->resize($img_w_new, $img_h_new);
	$image->save('inc/imgs/'.$img_dst);

	if($bshow_src) {
		$sret .= "<img src='".$img_src."' width='".$img_w."' height='".$img_h."' border='0' title='".$img_src ."'>";
	}
	if($bshow_dst) {
		$sret .= "<br><img src='inc/imgs/".$img_dst."' width='".$img_w_new."' height='".$img_h_new."' border='0' title='".$img_dst."'>";
	}
	return $sret;
}	

function get_valid_ddds() {

	$aret = array();
	foreach($GLOBALS['operadoras_current'] as $key => $val) {
		foreach($val as $key1 => $val1) {
			foreach($val['valoresPorDDD'] as $key1 => $val1) {
//				echo "['".$val['nomeOperadora']."', DDD: $key1] =&gt; valorMinimo='".$val1['valorMinimo']."', valorMaximo=".$val1['valorMaximo']."'<br>";
//				foreach($val1['valoresFixos'] as $key2 => $val2) {
//					echo "&nbsp;&nbsp;&nbsp;valoresFixos$key2 = valor: '".$val2['valor']."' valorBonus: '".$val2['valorBonus']."'<br>";
//				}
				if(isset($val1['valoresFixos']) || ( isset($val1['valorMinimo']) && isset($val1['valorMaximo']) && $val1['valorMinimo']>0 && $val1['valorMaximo']>0) ) {
					if(!in_array($key1, $aret)) {
						$aret[] = $key1; 
					}
				}
			}
		}
	}
	sort($aret);
//echo "<hr>DDDs válidos<pre style='background-color:#CCCCFF;color:darkgreen'>".print_r($aret, true)."</pre><hr>";
	return $aret;
}

function get_select_ddds($ddd) {
	$sret = "";
	$sret .= "<select name='DDD' id='DDD'>\n";
	$sret .= "<option value=''".(($ddd=="")?" selected":"").">Escolha o DDD</option>\n";

	foreach($GLOBALS['a_lista_ddds'] as $key => $val) {
		$sret .= "<option value='$val'".(($ddd==$val)?" selected":"").">$val</option>\n";
	}
	$sret .= "</select>\n";

//echo "<hr>".htmlentities($sret)."<hr>";
	return $sret;
}

function get_operadora_nome_by_codigo($codigo_operadora) {

	$snome = "";
	if($codigo_operadora) {
		foreach($GLOBALS['operadoras_current'] as $key => $val) {
			if($key==$codigo_operadora) {
				$snome = $val['nomeOperadora'];
				break;
			}
		}
	}
	return $snome;
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

function get_select_operadora_nome($codigo_operadora) {
	$sret = "";
	$sret .= "<select name='rc_codigooperadora' id='rc_codigooperadora'>\n";
	$sret .= "<option value=''".(($codigo_operadora=="")?" selected":"").">Escolha a Operadora</option>\n";

	foreach($GLOBALS['operadoras_current'] as $key => $val) {
		$sret .= "<option value='$key'".(($codigo_operadora==$key)?" selected":"").">".$val['nomeOperadora']."</option>\n";
	}
	$sret .= "</select>\n";

//echo "<hr>".htmlentities($sret)."<hr>";
	return $sret;
}
 
$COMISSAO_DEFAULT = 10;
?>