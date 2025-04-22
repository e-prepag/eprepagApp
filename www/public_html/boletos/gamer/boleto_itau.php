<?php 

        require_once '../../../includes/constantes.php';
        require_once RAIZ_DO_PROJETO . "includes/main.php";
        require_once RAIZ_DO_PROJETO . "includes/gamer/main.php";
	require_once RAIZ_DO_PROJETO . "includes/inc_register_globals.php";	

//error_reporting(E_ALL);
//ini_set("display_errors", 1);

	//Controle de acesso de usuario
	//O boleto pode ser visualizado pelo usuario que esta fazendo a compra no site e pelo operador do backoffice.
	//Como o backoffice eh um site diferente do site de venda, eh passado um token para validar.
	//----------------------------------------------------------------------------------------------------------------
	//Recupera token
	if(!$token) $token = $_REQUEST['token'];
	// Não deve usar mais este parâmetro
//	if(!$venda) $venda = $_REQUEST['venda'];

//gravaLog_DRUPAL_TMP("Em SICOB/BoletoWebItauCommerce.php: {venda: '$venda', token = '$token'\n");

	// Dummy
//	$token = "cVhQRlUZfFheEn1TSFBedFpWQFwUf01YGX1c";

	if($token && $token != ""){
		$objEncryption = new Encryption();
		$token_decript = $objEncryption->decrypt($token);
		$tokenAr = split(",", $token_decript);
		if(count($tokenAr) == 3){
			$data_gerado = $tokenAr[0];
			$venda_id = $tokenAr[1];
			$usuario_id = $tokenAr[2];
/*
	Dummy
*/
			if(date('YmdHis') - $data_gerado > 5 * 60){ //segundos
				$msg = "Token expirado.";
				$strRedirect = "/game/mensagem.php?msg=" . urlencode($msg) . "&pt=" . urlencode("Boleto Bancário");
				redirect($strRedirect);
			}
/*  */
		}

	//Recupera o usuario do session
	} else {
		$usuarioGames = unserialize($_SESSION['usuarioGames_ser']);
		if($usuarioGames){
			//Codigo do usuario
			$usuario_id = $usuarioGames->getId();
			//Codigo da Venda
			if(!$venda_id) $venda_id = $_REQUEST['venda'];
		}
	}

	//Validacao
	//----------------------------------------------------------------------------------------------------------------
	$msg = "";

	//Valida dados
	if(!$venda_id || $venda_id == "" || !is_numeric($venda_id)) $msg = "Código da venda inválido.";
	if(!$usuario_id || $usuario_id == "" || !is_numeric($usuario_id)) $msg = "Código do usuário inválido.";

	//Redireciona
	if($msg != ""){
		$msg = "Dados insuficientes para gerar o Boleto ($msg, [$venda_id], [$usuario_id]).";
		$strRedirect = "/game/mensagem.php?msg=" . urlencode($msg) . "&pt=" . urlencode("Boleto Bancário");
		redirect($strRedirect);
	}


	// Gera boleto
	//----------------------------------------------------------------------------------------------------------------
	//Obtem dados do boleto
	$sql  = "select * from boleto_bancario_games bbg " .
			"where (bbg_pago = 0 or bbg_pago is null) and bbg.bbg_vg_id = " . $venda_id . " and bbg.bbg_ug_id=" . $usuario_id;
	$rs_boleto = SQLexecuteQuery($sql);
	if(!$rs_boleto || pg_num_rows($rs_boleto) == 0){
		$msg = "Boleto não encontrado ou já pago.";
		$strRedirect = "/game/mensagem.php?msg=" . urlencode($msg) . "&pt=" . urlencode("Boleto Bancário");
		redirect($strRedirect);

	} else {
		$rs_boleto_row = pg_fetch_array($rs_boleto);
		$bbg_ug_id 		= $rs_boleto_row['bbg_ug_id'];
		$bbg_bco_codigo 		= $rs_boleto_row['bbg_bco_codigo'];
		$data_inclusao 	= $rs_boleto_row['bbg_data_inclusao'];
		$num_doc 		= $rs_boleto_row['bbg_documento'];
		$valor 			= $rs_boleto_row['bbg_valor'];
		$valor_taxa		= $rs_boleto_row['bbg_valor_taxa'];
		$data_venc 		= $rs_boleto_row['bbg_data_venc'];
	}

	//Checa boleto
	if($msg == ""){
		if($bbg_bco_codigo == "104"){
			if($token) $strRedirect = "/SICOB/BoletoWebCaixaCommerce.php?token=" . urlencode($token);
			else $strRedirect = "/SICOB/BoletoWebCaixaCommerce.php?venda=" . urlencode($venda_id);
			redirect($strRedirect);
		}elseif($bbg_bco_codigo == "237"){
			if($token) $strRedirect = "/boletos/gamer/boleto_bradesco.php?token=" . urlencode($token);
			else $strRedirect = "/boletos/gamer/boleto_bradesco.php?venda=" . urlencode($venda_id);
			redirect($strRedirect);
		}elseif($bbg_bco_codigo == "033"){
                        if($token) $strRedirect = "/SICOB/BoletoWebBanespaCommerce.php?token=" . urlencode($token);
                        else $strRedirect = "/SICOB/BoletoWebBanespaCommerce.php?venda=" . urlencode($venda_id);
                        redirect($strRedirect);
		}elseif($bbg_bco_codigo != "341"){
			$msg = "Boleto deste banco não existente.";
			$strRedirect = "/game/mensagem.php?msg=" . urlencode($msg) . "&pt=" . urlencode("Boleto Bancário");
			redirect($strRedirect);
		}
	}


	//Recupera dados do usuario
	if($msg == ""){
		$sql  = "select * from usuarios_games ug " .
				"where ug.ug_id = " . $bbg_ug_id;
		$rs_usuario = SQLexecuteQuery($sql);
		if(!$rs_usuario || pg_num_rows($rs_usuario) == 0) $msg = "Nenhum cliente encontrado.\n";
		else {
			$rs_usuario_row = pg_fetch_array($rs_usuario);
			$ug_id = $rs_usuario_row['ug_id'];
			$ug_ativo = $rs_usuario_row['ug_ativo'];
			$ug_data_inclusao = $rs_usuario_row['ug_data_inclusao'];
			$ug_data_ultimo_acesso = $rs_usuario_row['ug_data_ultimo_acesso'];
			$ug_qtde_acessos = $rs_usuario_row['ug_qtde_acessos'];
			$ug_email = $rs_usuario_row['ug_email'];
			$ug_cpf = $rs_usuario_row['ug_cpf'];
			$ug_nome = $rs_usuario_row['ug_nome_cpf'].". CPF: ".$ug_cpf;
			$ug_data_nascimento = $rs_usuario_row['ug_data_nascimento'];
			$ug_sexo = $rs_usuario_row['ug_sexo'];
			$ug_endereco = $rs_usuario_row['ug_endereco'];
			$ug_numero = $rs_usuario_row['ug_numero'];
			$ug_complemento = $rs_usuario_row['ug_complemento'];
			$ug_bairro = $rs_usuario_row['ug_bairro'];
			$ug_cidade = $rs_usuario_row['ug_cidade'];
			$ug_estado = $rs_usuario_row['ug_estado'];
			$ug_cep = $rs_usuario_row['ug_cep'];
			$ug_tel_ddi = $rs_usuario_row['ug_tel_ddi'];
			$ug_tel_ddd = $rs_usuario_row['ug_tel_ddd'];
			$ug_tel = $rs_usuario_row['ug_tel'];
			$ug_cel_ddi = $rs_usuario_row['ug_cel_ddi'];
			$ug_cel_ddd = $rs_usuario_row['ug_cel_ddd'];
			$ug_cel = $rs_usuario_row['ug_cel'];
		}
	}

	//Recupera dados da venda
	if($msg == ""){
		$sql  = "select * from tb_venda_games vg where vg.vg_id = " . $venda_id;
		$rs_venda = SQLexecuteQuery($sql);
		if(!$rs_venda || pg_num_rows($rs_venda) == 0) $msg = "Nenhuma venda encontrada.\n";
		else {
			$rs_venda_row = pg_fetch_array($rs_venda);
			$vg_ex_email = $rs_venda_row['vg_ex_email'];
		}
	}

?>
<?php
	//gera boleto
	if($msg == ""){
		// DADOS DO BOLETO PARA O SEU CLIENTE
		$data_venc 		= formata_data($data_venc, 0);
		$taxa_boleto 	= $valor_taxa;
		$valor_boleto 	= number_format($valor, 2, ',', '');
		$num_doc 		= $num_doc;
		$venda_id		= $venda_id;

		//Dados do sacado
		$sacado			= $ug_nome;

		if($ug_id == $GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']){
			$endereco 		= $vg_ex_email;
            $sqlVenda = "select * from tb_venda_games_cpf_boleto_express where vgcbe_vg_id = ".$rs_venda_row["vg_id"];
            $rs_venda_cpf = SQLexecuteQuery($sqlVenda);
            $vendaCpf = pg_fetch_array($rs_venda_cpf);
			$sacado = $vendaCpf['vgcbe_nome_cpf'].". CPF: ".$vendaCpf['vgcbe_cpf'];
            
		} else {
			$endereco 		= $ug_endereco;
			$numero 		= $ug_numero;
			if(trim($numero) != "") $endereco .= ", " . trim($numero);
			$complemento	= $ug_complemento;
			if(trim($complemento) != "") $endereco .= " - " . trim($complemento);
			$bairro 		= $ug_bairro;
			$municipio 		= $ug_cidade;
			if(trim($bairro) != "") $municipio = trim($bairro) . " - " . trim($municipio);
			$uf 			= $ug_estado;
			$cep 			= $ug_cep;
		}

		// NÃO ALTERAR!
		
		include $GLOBALS['raiz_do_projeto'] . "/www/web/SICOB2/include/funcoes_itau.php";
		include $GLOBALS['raiz_do_projeto'] . "/www/web/SICOB2/include/funcoes_itau_fixo.php";
		//include $GLOBALS['raiz_do_projeto'] . "/www/web/SICOB2/include/layout_itau.php";
        ob_clean();
        include $GLOBALS['raiz_do_projeto'] . "/www/web/SICOB2/include/boleto_to_image/boleto_imagem.php";
	}

	echo str_replace("\n", "<br>", $msg);
?>
<?php
//<script src="http://www.google-analytics.com/urchin.js" type="text/javascript">
//</script>
//<script type="text/javascript">
//_uacct = "UA-1903237-3";
//urchinTracker();
//</script>
?>
<!-- Google Code for Acao 1 Conversion Page -->
<script language="JavaScript" type="text/javascript">
<!--
var google_conversion_id = 1052651518;
var google_conversion_language = "pt_BR";
var google_conversion_format = "3";
var google_conversion_color = "ffffff";
if (<?php echo str_replace(",", ".", $valor_boleto) ?>) {
var google_conversion_value = <?php echo str_replace(",", ".", $valor_boleto)?>;
}
var google_conversion_label = "VieMCNqaZRD-3_j1Aw";
//-->
</script>
<script language="JavaScript" src="http<?php echo (($_SERVER['HTTPS']=="on")?"s":"") ?>://www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<img height="1" width="1" border="0" src="http<?php echo (($_SERVER['HTTPS']=="on")?"s":"") ?>://www.googleadservices.com/pagead/conversion/1052651518/?value=<?php echo str_replace(",", ".", $valor_boleto)?>&label=VieMCNqaZRD-3_j1Aw&guid=ON&script=0"/>
</noscript>
<script src="http<?php echo (($_SERVER['HTTPS']=="on")?"s":"") ?>://www.google-analytics.com/urchin.js" type="text/javascript">
</script>
<script type="text/javascript">
_uacct = "UA-1903237-3";
urchinTracker();
</script>
