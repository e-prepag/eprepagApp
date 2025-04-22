<?php
    ob_start();
    require_once '../../../includes/constantes.php';
    require_once RAIZ_DO_PROJETO . "includes/main.php";
    require_once RAIZ_DO_PROJETO . "includes/gamer/main.php";
    require_once RAIZ_DO_PROJETO . "banco/boletos/include/funcoes_bradesco.php";
    header('Content-Type: text/html; charset=charset=iso-8859-1');
    
	//Controle de acesso de usuario
	//O boleto pode ser visualizado pelo usuario que esta fazendo a compra no site e pelo operador do backoffice.
	//Como o backoffice eh um site diferente do site de venda, eh passado um token para validar.
	//----------------------------------------------------------------------------------------------------------------
	//Recupera token
	if(!$token) $token = $_REQUEST['token'];
	if($token && $token != ""){
		$objEncryption = new Encryption();
		$token_decript = $objEncryption->decrypt($token);
//echo "token_decript: $token_decript<br>";
//exit();
		$tokenAr = preg_split("/,/", $token_decript);
		if(count($tokenAr) == 3){
			$data_gerado = $tokenAr[0];
			$venda_id = $tokenAr[1];
			$usuario_id = $tokenAr[2];
			if(date('YmdHis') - $data_gerado > 5 * 60){ //segundos
				$msg = "Token expirado.";                                
				$strRedirect = "/creditos/mensagem.php?msg=" . urlencode($msg) . "&pt=" . urlencode("Boleto Bancário");
				redirect($strRedirect);
			}
		}

	//Recupera o usuario do session
	} else {
		$usuarioGames = unserialize($_SESSION['dist_usuarioGames_ser']);
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
		$strRedirect = "/creditos/mensagem.php?msg=" . urlencode($msg) . "&pt=" . urlencode("Boleto Bancário");
		redirect($strRedirect);
	}


	// Gera boleto
	//----------------------------------------------------------------------------------------------------------------
	//Obtem dados do boleto
	$sql  = "select * from dist_boleto_bancario_games bbg " .
			"where (bbg_pago = 0 or bbg_pago is null) and bbg.bbg_vg_id = " . $venda_id . " and bbg.bbg_ug_id=" . $usuario_id;
//echo "sql: $sql<br>";
//exit();
	$rs_boleto = SQLexecuteQuery($sql);
	if(!$rs_boleto || pg_num_rows($rs_boleto) == 0){
		$msg = "Boleto não encontrado ou já pago.";
		$strRedirect = "/creditos/mensagem.php?msg=" . urlencode($msg) . "&pt=" . urlencode("Boleto Bancário");
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
			if($token) $strRedirect = "/SICOB/BoletoWebCaixaDistCommerce.php?token=" . urlencode($token);
			else $strRedirect = "/SICOB/BoletoWebCaixaDistCommerce.php?venda=" . urlencode($venda_id);
			redirect($strRedirect);
		}elseif($bbg_bco_codigo == "341"){
                        if($token) $strRedirect = "/SICOB/BoletoWebItauCommerceLH.php?token=" . urlencode($token);
                        else $strRedirect = "/SICOB/BoletoWebItauCommerceLH.php?venda=" . urlencode($venda_id);
                        redirect($strRedirect);
		}elseif($bbg_bco_codigo == "033"){
                        if($token) $strRedirect = "/SICOB/BoletoWebBanespaCommerceLH.php?token=" . urlencode($token);
                        else $strRedirect = "/SICOB/BoletoWebBanespaCommerceLH.php?venda=" . urlencode($venda_id);
                        redirect($strRedirect);
		}elseif($bbg_bco_codigo != "237"){
			$msg = "Boleto deste banco não existente.";
			$strRedirect = "/creditos/mensagem.php?msg=" . urlencode($msg) . "&pt=" . urlencode("Boleto Bancário");
			redirect($strRedirect);
		}
	}

	//Recupera dados do usuario
	if($msg == ""){
		$sql  = "select * from dist_usuarios_games ug " .
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
			$ug_nome = $rs_usuario_row['ug_nome'];
            $ug_responsavel = $rs_usuario_row['ug_responsavel'];
            $ug_nome_fantasia = $rs_usuario_row['ug_nome_fantasia'];
			$ug_razao_social = $rs_usuario_row['ug_razao_social'];
			$ug_cpf = $rs_usuario_row['ug_cpf'];
			$ug_cnpj = $rs_usuario_row['ug_cnpj'];
			$ug_tipo_cadastro = $rs_usuario_row['ug_tipo_cadastro'];
			$ug_data_nascimento = $rs_usuario_row['ug_data_nascimento'];
			$ug_sexo = $rs_usuario_row['ug_sexo'];
            $ug_tipo_end = $rs_usuario_row['ug_tipo_end'];
			$ug_endereco = $ug_tipo_end.": ".$rs_usuario_row['ug_endereco'];
            $ug_endereco_logradouro = $rs_usuario_row['ug_endereco'];
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
            
//          o bloco abaixo serve para se adequar a nova norma da febraban de boleto registrado
            if($rs_usuario_row['ug_tipo_cadastro'] == "PF"){
                $rs_usuario_row['ug_cpf'] = str_replace(array(".", "-"), "",$rs_usuario_row['ug_cpf']);
                if(!empty($ug_nome)){
                    $dadosboleto["nome_pagador"] = $ug_nome;
                    $dadosboleto["sacado"] = $ug_nome;
                } else{
                    $dadosboleto["nome_pagador"] = $ug_responsavel;
                    $dadosboleto["sacado"] = $ug_responsavel;
                }
                $dadosBoletoRegistrado = $dadosboleto["sacado"] ." - CPF: ".mascara_cnpj_cpf($rs_usuario_row['ug_cpf'],"cpf");
                $dadosboleto["tipo_documento"] = "1";
                
            } else{
                
                $rs_usuario_row['ug_cnpj'] = str_replace(array(".", "/", "-"), "", $rs_usuario_row['ug_cnpj']);
                if(!empty($ug_razao_social)){
                    $dadosboleto["nome_pagador"] = $ug_razao_social;
                    $nomeBoletoRegistrado = $ug_razao_social;
                } else{
                    if(!empty($ug_nome_fantasia)){
                        $dadosboleto["nome_pagador"] = $ug_nome_fantasia;
                        $nomeBoletoRegistrado = $ug_nome_fantasia;
                    } else {
                        if(!empty($ug_nome)){
                            $dadosboleto["nome_pagador"] = $ug_nome;
                            $nomeBoletoRegistrado = $ug_nome;
                        } else{
                            $dadosboleto["nome_pagador"] = $ug_responsavel;
                            $nomeBoletoRegistrado = $ug_responsavel;
                        }
                    }
                }
                $dadosBoletoRegistrado = $nomeBoletoRegistrado . " - CNPJ: ".mascara_cnpj_cpf($rs_usuario_row['ug_cnpj'],"cnpj");
                $dadosboleto["tipo_documento"] = "2";
            }
            
            $ug_cep 			= str_replace("-","",$ug_cep);
            $mask = $ug_cep;
            $var1 = substr("$mask", 0,5);
            $var2 = substr("$mask", 5,8);  
            $ug_cep = $var1."-".$var2;
		}
	}
    
	//Recupera dados da venda
	if($msg == ""){
		$sql  = "select * from tb_dist_venda_games vg where vg.vg_id = " . $venda_id;
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
//		if($ug_tipo_cadastro=="PJ" && $ug_razao_social) {
		if($ug_id==4707 || $ug_id==468) {
//			$sacado			= $ug_razao_social; sacado esta sendo inserido logo abaixo do fim deste if, para se adequar a nova norma da febraban de boleto registrado

			$linha2 = "".$ug_razao_social." (CNPJ: ".$ug_cnpj.")";

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

		} else {
//			$sacado			= $ug_razao_social; sacado esta sendo inserido logo abaixo do fim deste if, para se adequar a nova norma da febraban de boleto registrado

			$linha2 = "";

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

        $sacado = $dadosBoletoRegistrado;

		// NÃO ALTERAR!
		require_once RAIZ_DO_PROJETO . "banco/boletos/include/funcoes_bradesco_fixo_money.php";
		//include $GLOBALS['raiz_do_projeto'] . "/www/web/SICOB2/include/layout_bradesco.php";
        
        /// ----- DADOS DO CLIENTE - P/ REGISTRO DO BOLETO ----- ///

        
        if($dadosboleto["tipo_documento"] == "1"){
            $dadosboleto["documento_pagador"] = $ug_cpf;
        } else{
            $dadosboleto["documento_pagador"] = $ug_cnpj;
        }
        
        $dadosboleto["cep_pagador"] = preg_replace('/[^0-9]/', '', $ug_cep);
        $dadosboleto["logradouro_pagador"] = $ug_endereco_logradouro;
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
           empty($dadosboleto["cpfcnpj"])){
           
            $msg = "Por favor preencha seus dados de Cadastro antes de gerar o boleto!<br>Entre em contato com o suporte da E-Prepag e atualize seu cadastro.";
            ?>
                <form name="pagamento" id="pagamento" method="POST" action="/creditos/mensagem.php">
                    <input type='hidden' name='msg' id='msg' value='<?php echo $msg; ?>'>
                    <input type='hidden' name='titulo' id='titulo' value='Preencha os Dados de Endereço'>
                    <input type='hidden' name='link' id='link' value='/creditos/meu_cadastro.php'>
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
        ?>    
            <form name="pagamento" id="pagamento" method="POST" action="/creditos/mensagem.php">
                <input type='hidden' name='msg' id='msg' value='<?php echo $msg; ?>'>
                <input type='hidden' name='titulo' id='titulo' value='Boleto Expirado'>
                <input type='hidden' name='link' id='link' value='/creditos/add_saldo.php'>
            </form>
            <script language='javascript'>
                document.getElementById("pagamento").submit();
            </script>
        <?php
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
        //------FIM BLOCO PARA TRATAR PROBLEMA DO HORÁRIO DE VERÃO ADIADO PELO GOVERNO em 2018--------------------------------------------
        //********************************************************************************************************************************

        $boleto =  array(
                         'nosso_numero' => $dadosboleto["numero_documento"],
                         'numero_documento' => $dadosboleto['nosso_numero'], 
                         'data_emissao' => formata_data($dadosboleto["data_documento"],"1"), 
                         'data_vencimento' => formata_data($dadosboleto["data_vencimento"],"1"), 
                         'valor_titulo' => preg_replace('/[^0-9]/', '', $dadosboleto["valor_boleto"]) ,
                         'pagador' => array(
                                            'id' => $usuario_id,
                                            'nome' => substr($dadosboleto["nome_pagador"],0,150), 
                                            'documento' => $dadosboleto["cpfcnpj"], 
                                            'tipo_documento' => $dadosboleto["tipo_documento"], 
                                            'endereco' => array(
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

        $t = new classBradesco();
        $lista_resposta = NULL;
        $codigo = $t->Req_EfetuaConsultaRegistro($boleto, $lista_resposta);
 
        if(!in_array($codigo, $BRADESCO_CODE_SUCESS)){
            $assunto1 = (checkIP()?"[DEV] ":"[PROD] ") . "E-Prepag - Problema ao Registrar Boleto Bradesco - PDV";
            
                enviaEmail("estagiario1@e-prepag.com,wagner@e-prepag.com.br, suporte@e-prepag.com.br", null, null, $assunto1, "Na tentativa do registro tivemos o seguinte retorno:<br>" .(!is_null($codigo)?$BRADESCO_CODE_ERRORS_REGISTRO[$codigo]:"NULL"). "<br><br>ID Usuário: ".$usuario_id. "<br>" . "<pre>".print_r($boleto, true)."</pre>");
           $msg = "Tivemos problema de comunicação com o Banco!<br>Aguarde alguns instantes e tente novamente.<br>Obrigado!";
           ?>
                <form name="pagamento" id="pagamento" method="POST" action="/creditos/mensagem.php">
                    <input type='hidden' name='msg' id='msg' value='<?php echo $msg; ?>'>
                    <input type='hidden' name='titulo' id='titulo' value='Problema de comunicação com o Banco'>
                </form>
                <script language='javascript'>
                    document.getElementById("pagamento").submit();
                </script>       
        <?php
                die();
            }
            
        ob_clean();
        require_once RAIZ_DO_PROJETO . "banco/boletos/include/boleto_to_image/boleto_imagem.php";
	}

?>

