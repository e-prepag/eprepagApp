<?php 
require_once '../../../includes/constantes.php';
require_once RAIZ_DO_PROJETO . "includes/main.php";
require_once RAIZ_DO_PROJETO . "includes/gamer/main.php";
require_once RAIZ_DO_PROJETO . "banco/boletos/include/funcoes_bradesco.php";
header("Content-Type: text/html; charset=ISO-8859-1",true);
$https = 'http' . (($_SERVER['HTTPS']=='on') ? 's' : '');
require_once RAIZ_DO_PROJETO . "includes/inc_register_globals.php";	

//Controle de acesso de usuario
//O boleto pode ser visualizado pelo usuario que esta fazendo a compra no site e pelo operador do backoffice.
//Como o backoffice eh um site diferente do site de venda, eh passado um token para validar.
//----------------------------------------------------------------------------------------------------------------
//Recupera token
if(!$token) $token = $_REQUEST['token'];
if($token && $token != ""){
        $objEncryption = new Encryption();
        $token_decript = $objEncryption->decrypt($token);
        $tokenAr = preg_split("/,/", $token_decript);
        if(count($tokenAr) == 3){
                $data_gerado = $tokenAr[0];
                $venda_id = $tokenAr[1];
                $usuario_id = $tokenAr[2];
                if(date('YmdHis') - $data_gerado > 5 * 60){ //segundos
                        $msg = "Token expirado.";
                        $strRedirect = "/game/mensagem.php?msg=" . urlencode($msg) . "&pt=" . urlencode("Boleto Bancário");
                        redirect($strRedirect);
                }
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
        $msg = "Dados insuficientes para gerar o Boleto.";
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
        }elseif($bbg_bco_codigo == "341"){
                if($token) $strRedirect = "/SICOB/BoletoWebItauCommerce.php?token=" . urlencode($token);
                else $strRedirect = "/SICOB/BoletoWebItauCommerce.php?venda=" . urlencode($venda_id);
                redirect($strRedirect);
        }elseif($bbg_bco_codigo == "033"){
                if($token) $strRedirect = "/SICOB/BoletoWebBanespaCommerce.php?token=" . urlencode($token);
                else $strRedirect = "/SICOB/BoletoWebBanespaCommerce.php?venda=" . urlencode($venda_id);
                redirect($strRedirect);
        }elseif($bbg_bco_codigo != "237"){
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
				$ug_nome_registro = $rs_usuario_row['ug_nome_cpf'];
                $ug_data_nascimento = $rs_usuario_row['ug_data_nascimento'];
                $ug_sexo = $rs_usuario_row['ug_sexo'];
                $ug_endereco = $rs_usuario_row['ug_endereco'];
                $ug_numero = $rs_usuario_row['ug_numero'];
                $ug_complemento = (empty($rs_usuario_row['ug_complemento'])) ? 'Vazio' : $rs_usuario_row['ug_complemento'];
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
    $ug_nome_registro = $vendaCpf['vgcbe_nome_cpf'];
    $ug_cpf = $vendaCpf['vgcbe_cpf'];

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

require_once RAIZ_DO_PROJETO . "banco/boletos/include/funcoes_bradesco_fixo_money.php";

// ----- DADOS DO CLIENTE - P/ REGISTRO DO BOLETO -----

$dadosboleto["nome_pagador"] = $ug_nome_registro;
$dadosboleto["documento_pagador"] = $ug_cpf;
$dadosboleto["tipo_documento"] = "1";
$dadosboleto["cep_pagador"] = preg_replace('/[^0-9]/', '', $ug_cep) ;
$dadosboleto["logradouro_pagador"] = $ug_endereco;
$dadosboleto["numero_pagador"] = $ug_numero;
$dadosboleto["complemento_pagador"] = $ug_complemento;
$dadosboleto["bairro_pagador"] = $ug_bairro;
$dadosboleto["cidade_pagador"] = $ug_cidade;
$dadosboleto["uf_pagador"] = $ug_estado;
$dadosboleto["cpfcnpj"] = preg_replace('/[^0-9]/', '', $dadosboleto["documento_pagador"]);

//Aplicando date() as datas de vencimento e emissao para fazer a comparação [(strtotime($date_vencimento) < strtotime($date_emissao))]
$date_vencimento = date('Y-m-d', strtotime(str_replace('/', '-', $dadosboleto["data_vencimento"])));

$date_emissao = date('Y-m-d', strtotime(str_replace('/', '-', $dadosboleto["data_documento"])));

//Validando campos preenchidos
if(empty($dadosboleto["cep_pagador"]) || 
   empty($dadosboleto["logradouro_pagador"]) || 
   (!isset($dadosboleto["numero_pagador"]) || $dadosboleto["numero_pagador"] =="" || $dadosboleto["numero_pagador"] ==" ") || 
   empty($dadosboleto["bairro_pagador"]) || 
   empty($dadosboleto["cidade_pagador"]) || 
   empty($dadosboleto["uf_pagador"]) || 
   empty($dadosboleto["cpfcnpj"]))
{
    $msg = "Por favor preencha seus dados de Endereço antes de gerar o boleto!";
    ?>
        <form name="pagamento" id="pagamento" method="POST" action="/game/mensagem.php">
            <input type='hidden' name='msg' id='msg' value='<?php echo $msg; ?>'>
            <input type='hidden' name='titulo' id='titulo' value='Preencha os Dados de Endereço'>
            <input type='hidden' name='link' id='link' value='/game/conta/meus-dados.php'>
        </form>
        <script language='javascript'>
            document.getElementById("pagamento").submit();
        </script>       
<?php
        die();
}//end emptys
// Validando a data de vencimento (nao pode ser menor que a data de emissao)
elseif(strtotime($date_vencimento) < strtotime($date_emissao) && (strtotime($date_vencimento) != FALSE && strtotime($date_emissao) != FALSE)){
    $msg = "O boleto que você está tentando emitir possui Data de Vencimento anterior a Data Atual. Por favor, gere outro boleto com a opção desejada. Obrigado!";
    if(!isset($GLOBALS['_SESSION']['integracao_is_parceiro']) || empty($GLOBALS['_SESSION']['integracao_is_parceiro'])) {
?>    
    <form name="pagamento" id="pagamento" method="POST" action="/game/mensagem.php">
        <input type='hidden' name='msg' id='msg' value='<?php echo $msg; ?>'>
        <input type='hidden' name='titulo' id='titulo' value='Boleto Expirado'>
        <input type='hidden' name='link' id='link' value='/game/conta/pedidos.php'>
    </form>
    <script language='javascript'>
        document.getElementById("pagamento").submit();
    </script>
<?php
    }//end if(!isset($GLOBALS['_SESSION']['integracao_is_parceiro']) || empty($GLOBALS['_SESSION']['integracao_is_parceiro']))
    else {
        echo $msg;
?>
        <script>
            alert('<?php echo $msg; ?>');
        </script>    
<?php        
    }//end else do if(!isset($GLOBALS['_SESSION']['integracao_is_parceiro']) || empty($GLOBALS['_SESSION']['integracao_is_parceiro']))
    die();
}//end elseif(strtotime($dadosboleto["data_vencimento"]) < strtotime($dadosboleto["data_documento"]))

require_once RAIZ_DO_PROJETO.'banco/boletos/boleto_regitrado/bradesco/config.inc.bradesco.php';

//********************************************************************************************************************************
//------BLOCO PARA TRATAR PROBLEMA DO HORÁRIO DE VERÃO ADIADO PELO GOVERNO EM 2018------------------------------------------------
$aux_data = date("d-m-Y");
//((SE O MÊS FOR OUTUBRO(10)) OU (SE O DIA É ANTES DO DIA 4 E O MÊS FOR NOVEMBRO(11)) E (O ANO FOR 2018))
if(((substr($aux_data, 3,2) == 10) || (substr($aux_data,0,2) < 4 && substr($aux_data, 3,2) == 11)) && substr($aux_data, 6,4) == 2018){
    $aux_data_hora = date("d/m/Y H:i:s");
    $hora_verao = trim(substr($aux_data_hora, 10));

    if(substr($hora_verao, 0, 2) == '00'){
        if(substr($hora_verao, 3, 2) <= 59) $dadosboleto["data_documento"] = date('d/m/Y', strtotime('-1 days', strtotime($aux_data)));
    }
}
//------FIM BLOCO PARA TRATAR PROBLEMA DO HORÁRIO DE VERÃO ADIADO PELO GOVERNO EM 2018--------------------------------------------
//********************************************************************************************************************************

$boleto =  array( 
                 'nosso_numero' => $dadosboleto["numero_documento"],
                 'numero_documento' => $dadosboleto['nosso_numero'], 
                 'data_emissao' => formata_data($dadosboleto["data_documento"],"1"), 
                 'data_vencimento' => formata_data($dadosboleto["data_vencimento"], "1"), 
                 'valor_titulo' => preg_replace('/[^0-9]/', '', $dadosboleto["valor_boleto"]) ,
                 'pagador' => array(
                                    'nome' => substr($dadosboleto["nome_pagador"],0,150), 
                                    'documento' => preg_replace('/[^0-9]/', '', $dadosboleto["cpfcnpj"]), 
                                    'tipo_documento' => $dadosboleto["tipo_documento"], 
                                    'endereco' => array(
                                                        'id' => $usuario_id,
                                                        'cep' => $dadosboleto["cep_pagador"] , 
                                                        'logradouro' => substr($dadosboleto["logradouro_pagador"], 0, 70) , 
                                                        'numero' => substr($dadosboleto["numero_pagador"], 0, 10) , 
                                                        'complemento' => substr($dadosboleto["complemento_pagador"], 0, 20) ,
                                                        'bairro' => substr($dadosboleto["bairro_pagador"], 0, 50) , 
                                                        'cidade' => substr($dadosboleto["cidade_pagador"], 0, 100) , 
                                                        'uf' => $dadosboleto["uf_pagador"]
                                                        )
                                    )
                );

array_walk_recursive(
        $boleto,
        function (&$entry) {
            $entry = utf8_decode(
                $entry
            );
        }
);

/*
		$meu_ip_1 = '201.93.162.169';
		$meu_ip_2 = '189.62.151.212';

		if ($_SERVER['REMOTE_ADDR'] == $meu_ip_1 || $_SERVER['REMOTE_ADDR'] == $meu_ip_2) {
			echo '<pre>';
			var_dump($boleto);
			echo '</pre>';
			die();
		}
*/

$t = new classBradesco();
$lista_resposta = NULL;
$codigo = $t->Req_EfetuaConsultaRegistro($boleto, $lista_resposta);

if(!in_array($codigo, $BRADESCO_CODE_SUCESS)){
    $assunto1 = (checkIP()?"[DEV] ":"[PROD] ")."E-Prepag - Problema ao Registrar Boleto Bradesco - GAMER";
    enviaEmail("estagiario1@e-prepag.com,wagner@e-prepag.com.br, suporte@e-prepag.com.br", null, null, $assunto1, "Na tentativa do registro tivemos o seguinte retorno:<br>" .(!is_null($codigo)?$BRADESCO_CODE_ERRORS_REGISTRO[$codigo]:"NULL"). "<br><br>ID Usuário: ".$usuario_id. "<br>" . "<pre>".print_r($boleto, true)."</pre>");
    $msg = "Tivemos problema de comunicação com o Banco!<br>Aguarde alguns instantes e tente novamente.<br>Obrigado!";
    if((!isset($GLOBALS['_SESSION']['integracao_is_parceiro']) || empty($GLOBALS['_SESSION']['integracao_is_parceiro'])) && ($ug_id != $GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY'])) {
?>
        <form name="pagamento" id="pagamento" method="POST" action="/game/mensagem.php">
            <input type='hidden' name='msg' id='msg' value='<?php echo $msg; ?>'>
            <input type='hidden' name='titulo' id='titulo' value='Problema de comunicação com o Banco'>
        </form>
        <script language='javascript'>
            document.getElementById("pagamento").submit();
        </script>       
<?php
    }//end if((!isset($GLOBALS['_SESSION']['integracao_is_parceiro']) || empty($GLOBALS['_SESSION']['integracao_is_parceiro'])) && ($ug_id != $GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']))
    //elseif($ug_id == $GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']){
    else{
        $msg = str_replace("<br>", " ", $msg);
?>
        <div class="col-md-12 top10 col-sm-12 col-xs-12">
            <p class="txt-vermelho"><?php echo $msg;?></p>
        </div>    
        
        <link href="/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
        <link href="/css/creditos.css" rel="stylesheet" type="text/css" />
        <script type="text/javascript" src="/js/jquery/jquery.js"></script>
        <script src="/prepag2/js/jqueryui/js/jquery-ui-1.9.2.custom.min.js"></script>
        <script type="text/javascript" src="/bootstrap/js/bootstrap.min.js"></script>
        <!-- Modal -->
        <div id="modal-problema-comunicacao" class="modal fade" data-backdrop="static" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                      <h4 class="modal-title txt-vermelho">Problema na comunicação com o Banco</h4>
                    </div>
                    <div class="modal-body alert alert-danger">
                        <div class="form-group top10">
                            <p><?php echo $msg;?></p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal" onclick="window.close();">Fechar</button>
                    </div>
                </div>
            </div>
        </div>
        <script>$("#modal-problema-comunicacao").modal();</script>
<?php        
    }//end else do if((!isset($GLOBALS['_SESSION']['integracao_is_parceiro']) || empty($GLOBALS['_SESSION']['integracao_is_parceiro'])) && ($ug_id != $GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']))

    die();
} 

ob_clean();
require_once RAIZ_DO_PROJETO . "banco/boletos/include/boleto_to_image/boleto_imagem.php";
}

echo str_replace("\n", "<br>", $msg);
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
<script language="JavaScript" src="<?php echo $https; ?>://www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<img height="1" width="1" border="0" src="<?php echo $https; ?>://www.googleadservices.com/pagead/conversion/1052651518/?value=<?php echo str_replace(",", ".", $valor_boleto)?>&label=VieMCNqaZRD-3_j1Aw&guid=ON&script=0"/>
</noscript>
<!-- Google Code for Analytics Page -->
<script src="<?php echo $https; ?>://www.google-analytics.com/urchin.js" type="text/javascript">
</script>
<script type="text/javascript">
_uacct = "UA-1903237-3";
urchinTracker();
</script>
