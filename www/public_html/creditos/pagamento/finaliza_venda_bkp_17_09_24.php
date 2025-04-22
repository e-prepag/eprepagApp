<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

@session_start();
require_once "../../../includes/constantes.php";
require_once DIR_INCS . "configIP.php";
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "pdv/main.php";
require_once DIR_CLASS . "pdv/classOperadorGamesUsuario.php";
require_once DIR_INCS . "funcoes_cpf.php";


checkingIsCompletedData('/creditos/carrinho/');///prepag2/dist_commerce/finaliza_venda_preview

$_PaginaOperador2Permitido = 54; 
validaSessao(); 

//Recupera o usuario do session
$usuarioGames = unserialize($_SESSION['dist_usuarioGames_ser']);

$pagto = $_SESSION['dist_pagamento.pagto'];
$produtos = $_SESSION['dist_pagamento.total'];
$iforma = $_SESSION['dist_pagamento.pagto'];

 if(!empty($_POST["g-recaptcha-response"])){
	 
/*
   if($_SERVER["REMOTE_ADDR"] == "201.93.162.169"){
	    //ini_set('display_errors', 1);
		//ini_set('display_startup_errors', 1);
		//error_reporting(E_ALL);
   }
*/			
	// s recaptcha: 6LcajMgoAAAAAJnVzc2UdimN2eW7Tll7228KIrAb		
   $tokenInfo = ["secret" => "ES_c5e93fbd48b146578b65fc84f8717a95", "response" => $_POST["g-recaptcha-response"]];  //, "remoteip" => $_SERVER["REMOTE_ADDR"]

	$recaptcha = curl_init();
	curl_setopt_array($recaptcha, [
		CURLOPT_URL => 'https://api.hcaptcha.com/siteverify', // https://www.google.com/recaptcha/api/siteverify
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS => http_build_query($tokenInfo),
	    CURLOPT_HTTPHEADER => array(
		   'Content-Type: application/x-www-form-urlencoded'
	    )
	]);
	
	curl_setopt($recaptcha, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($recaptcha, CURLOPT_SSL_VERIFYPEER, 0);
	
	$jsonC = curl_exec($recaptcha);
	$retorno = json_decode($jsonC, true);
	$info = curl_getinfo($recaptcha);
	$err = curl_error($recaptcha);
	curl_close($recaptcha);
	
	if($retorno["success"] != true || (isset($retorno["error-codes"]) && !empty($retorno["error-codes"]))){
		$erro = true;
		header("location: https://www.e-prepag.com.br/creditos/");
        exit;
	}
   
}
else{
  header("location: https://www.e-prepag.com.br/creditos/");
  exit;
}

if($usuarioGames->b_IsLogin_pagamento())  {

		// atualiza cesta e total (em LH-Pre pagamos online apenas boletos, não tem lista de produtos)
//		$total_carrinho = mostraCarrinho_pag(false);
		$total_carrinho = $_SESSION['dist_pagamento.total'];

		// Taxa do Banco Itau é acrescentada em inc_urls_bancoitau.php, aqui não está fucnionando
		$taxas = (($pagto==$FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) ? $BRADESCO_TRANSFERENCIA_ENTRE_CONTAS_TAXA_ADICIONAL : (($pagto==$FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']) ? $BANCO_DO_BRASIL_TAXA_DE_SERVICO : (($pagto==$FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE']) ? $BANCO_ITAU_TAXA_DE_SERVICO : 0)) );


		// ==========================================================================================
		// Faz validação de vendas totais, copia de pagamento.php

		// Testa que só tem produtos Habbo e GPotato no carrinho
		//$b_IsProdutoOK = bCarrinho_ApenasProdutosOK();	// não usa mais

		// Testa que usuário comprou no máximo 10 vezes nas últimas 24 horas
		$qtde_last_dayOK = getNVendasLH($usuarioGames->getId());

		// Calcula o total diario para pagamentos Online Bradesco
		$total_diario = getVendasLHTotalDiarioOnline($usuarioGames->getId()); 

		$b_TentativasDiariasOK = ($qtde_last_dayOK<=$RISCO_LANS_PRE_PAGAMENTOS_DIARIO);
		$b_LimiteDiarioOK = (($total_carrinho+$total_diario)<=$RISCO_LANS_PRE_TOTAL_DIARIO); // ((($total_carrinho+$total_diario)<=$RISCO_LANS_PRE_TOTAL_DIARIO) && ($qtde_last_dayOK<=$RISCO_LANS_PRE_PAGAMENTOS_DIARIO))

                // Libera pagamento Online Banco do Brasil
		$b_libera_BancodoBrasil = $b_LimiteDiarioOK && $b_TentativasDiariasOK;// && $usuarioGames->b_IsLogin_pagamento_bancodobrasil();

		// Libera Bradesco apenas se limite diario não ultrapassado e tem até 10 compras nas últimas 24 horas	//produtos (Habbo e GPotato) 
		$b_libera_Bradesco = $b_LimiteDiarioOK && $b_TentativasDiariasOK;	//$b_IsProdutoOK && 

		// Libera pagamento Online Banco Itaú
		$b_libera_BancoItau = $b_LimiteDiarioOK && $b_TentativasDiariasOK && $usuarioGames->b_IsLogin_pagamento_bancoitau();

		$msg_bloqueia_Bradesco = (!$b_libera_Bradesco)?((!$b_LimiteDiarioOK)?" \\n \\n Sua compra de ".number_format($total_carrinho, 2, ',', '.')." ultrapassa o limite diário de compras on-line.":((!$b_TentativasDiariasOK)?" \\n \\n Número de pagamentos online (".$qtde_last_dayOK.") ultrapassa o limite diário.":"")):"";

		$msg_bloqueia_BancodoBrasil = (!$b_libera_BancodoBrasil)?((!$b_LimiteDiarioOK)?" \\n \\n Sua compra de ".number_format($total_carrinho, 2, ',', '.')." ultrapassa o limite diário de compras on-line.":((!$b_TentativasDiariasOK)?" \\n \\n Número de pagamentos online (".$qtde_last_dayOK.") ultrapassa o limite diário.":"")):"";

		$msg_bloqueia_BancoItau = (!$b_libera_BancoItau)?((!$b_LimiteDiarioOK)?" \\n \\n Sua compra de ".number_format($total_carrinho, 2, ',', '.')." ultrapassa o limite diário de compras on-line.":((!$b_TentativasDiariasOK)?" \\n \\n Número de pagamentos online (".$qtde_last_dayOK.") ultrapassa o limite diário.":"")):"";

		// finaliza validações
		// ==========================================================================================
		// Pega o id_usuario do login para salvar ao inserir em tb_pag_compras, deposi de ter a venda cadastrada o ID será atualizado novamente.
		$id_usuario_prev = $usuarioGames->getId();
		$cliente_nome_prev = $usuarioGames->getNome();

		$pagto_venda = $pagto;
		// tipo_cliente   character varying(2),	-- 'M' - Money, 'E' - Money Express, 'LR' - Lanhouse Pré, 'LO' - Lanhouse Pos, 
		$tipo_cliente = "LR";

		if(($pagto==$FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) || ($pagto==$FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO'])) {
			// gera nova ordem em tb_pag_compras
			require_once RAIZ_DO_PROJETO . "banco/bradesco/inc_gen_order.php"; // 
			$numOrder = $orderId;
		}

		if($pagto==$FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']) {
			// gera nova ordem em tb_pag_compras
			require_once RAIZ_DO_PROJETO . "banco/bancodobrasil/inc_gen_order_bbr.php"; // 
			$numOrder = $orderId;
		
		}

		if($pagto==$FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE']) {

			// gera nova ordem em tb_pag_compras
			require_once RAIZ_DO_PROJETO . "banco/itau/inc_config.php"; 
			require_once RAIZ_DO_PROJETO . "banco/itau/inc_gen_order_bit.php"; // 
			$numOrder = $orderId;
			$pagto_venda = $PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC;	// convert to numeric value to allow storing in tb_dist_venda_games
		
		}

		// Recupera cesta
		$sql = "SELECT * FROM tb_pag_compras WHERE numCompra='".$numOrder."'";
		$ret = SQLexecuteQuery($sql);
		if(!$ret) {
			echo "Erro ao recuperar transação de pagamento (1).\n";
			die("Stop");
		}


		$sql = "UPDATE tb_pag_compras SET cliente_nome='".str_replace("'", "''", $usuarioGames->getNome())."', idcliente=".$usuarioGames->getId().", status=1, cesta='".str_replace("'", "''", montaCesta_pag())."', total=".(100*($total_carrinho+$taxas))." WHERE numcompra='".$numOrder."'";		// "iforma='".$_SESSION['pagamento.pagto']."', "
		$ret = SQLexecuteQuery($sql);
		if(!$ret) {
			echo "Erro ao atualizar transação de pagamento (2).\n";
			die("Stop");
		}

	}


//Junta objetos de uma venda
//----------------------------------------------------------------------------------------------------------------------------
$strRedirect = "";

	// Carrinho (em LH pagamos online apenas boletos, não tem lista de produtos) 
	//		mas continua a lista de carrinho para vendas normais: com corte semanal (LH pos) ou pagamento com o saldo (LH pre)
		if($usuarioGames->b_IsLogin_pagamento()) {
			if (isFormaPagtoOnline($iforma)) {
				
				// Para pagamento online de lans pre temos apenas 1 produto: 1 boleto de valor $total_carrinho = $_SESSION['dist_pagamento.total'];
				$qtde = 1;
				$total_carrinho = $_SESSION['dist_pagamento.total'];
			} else {
				
				// Idêntico ao pagamento normal - mais embaixo
				//----------------------------------------------------
				//Recupera carrinho do session
				$carrinho = $_SESSION['dist_carrinho'];
				//Valida se existe carrinho
				if($strRedirect == ""){
					if(!$carrinho || count($carrinho) == 0){
						$strRedirect = "/creditos/erro.php?err=31";
					}
				}
			
				//Valida produtos
				if($strRedirect == ""){
			
					//Remove produtos invalidos
					foreach ($carrinho as $modeloId => $qtde){
                                            
                                            if($modeloId !== $GLOBALS['NO_HAVE']) {
			
						$qtde = intval($qtde);
						//Se qtde do modelo invalida, remove modelo
						if($qtde <= 0) unset($carrinho[$modeloId]);
                                                
                                            }
                                            else {
                                                foreach ($qtde as $codeProd => $vetor_valor) {
                                                    foreach ($vetor_valor as $valor => $quantidade) {
                                                        //Se qtde do modelo invalida, remove modelo
                                                        if($quantidade <= 0) unset($carrinho[$modeloId][$codeProd][$valor]);
                                                        if(count($carrinho[$modeloId][$codeProd]) == 0) unset($carrinho[$mod][$codeProd]);
                                                        if(count($carrinho[$modeloId]) == 0) unset($carrinho[$mod]);
                                                    }//end foreach 
                                                }//end foreach
                                            }//end else do if($modeloId !== $GLOBALS['NO_HAVE'])
			
					}
					
					//Se nao restou produto, retorna para o carrinho
					if(!$carrinho || count($carrinho) == 0){
						$strRedirect = "/creditos/erro.php?err=32";
					}
				}
			}
		} else {
			//----------------------------------------------------
			//Recupera carrinho do session
			$carrinho = $_SESSION['dist_carrinho'];
		
			//Valida se existe carrinho
			if($strRedirect == ""){
				if(!$carrinho || count($carrinho) == 0){
					$strRedirect = "/creditos/erro.php?err=31";
				}
			}
                        
			//Valida produtos
			if($strRedirect == ""){
		
				//Remove produtos invalidos
                                foreach ($carrinho as $modeloId => $qtde){

                                    if($modeloId !== $GLOBALS['NO_HAVE']) {

                                        $qtde = intval($qtde);
                                        //Se qtde do modelo invalida, remove modelo
                                        if($qtde <= 0) unset($carrinho[$modeloId]);

                                    }
                                    else {
                                        foreach ($qtde as $codeProd => $vetor_valor) {
                                            foreach ($vetor_valor as $valor => $quantidade) {
                                                //Se qtde do modelo invalida, remove modelo
                                                if($quantidade <= 0) unset($carrinho[$modeloId][$codeProd][$valor]);
                                                if(count($carrinho[$modeloId][$codeProd]) == 0) unset($carrinho[$mod][$codeProd]);
                                                if(count($carrinho[$modeloId]) == 0) unset($carrinho[$mod]);
                                            }//end foreach 
                                        }//end foreach
                                    }//end else do if($modeloId !== $GLOBALS['NO_HAVE'])

                                }
				
				//Se nao restou produto, retorna para o carrinho
				if(!$carrinho || count($carrinho) == 0){
					$strRedirect = "/creditos/erro.php?err=32";
				}
			}
		}
	//Pagamento
	//----------------------------------------------------
//	$pagto = $FORMAS_PAGAMENTO['REPASSE'];
 
		//Recupera tipo do pagamento do session
		$pagto = $_SESSION['dist_pagamento.pagto'];
		$total_carrinho = $_SESSION['dist_pagamento.total'];

		//Valida formas de pagamento
		if($strRedirect == ""){
			if(!$pagto || trim($pagto) == "" || !is_numeric($pagto)){
				$strRedirect = "/creditos/carrinho/2";
                                //var_dump($_SESSION['dist_pagamento.pagto']);
			}else if(!in_array($pagto, $FORMAS_PAGAMENTO)){
				$strRedirect = "/creditos/carrinho/1";
			}
		}

		if($usuarioGames->b_IsLogin_pagamento()) {
			
			if($usuarioGames->bIsLanPre()) {
				if(!$b_libera_Bradesco && (($pagto==$FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) || ($pagto==$FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO']))) {
					?>
					<script language="Javascript">
						alert("Erro: <?php echo $msg_bloqueia_Bradesco;?>");
					</script>
					<?php				
					$strRedirect = "/creditos/erro.php?err=41";
				} else if(!$b_libera_BancodoBrasil && ($pagto==$FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA'])) {
					if($usuarioGames->b_IsLogin_pagamento_bancodobrasil()) {	
					?>
					<script language="Javascript">
						alert("Erro: <?php echo $msg_bloqueia_BancodoBrasil; ?>");
					</script>
					<?php			
					$strRedirect = "/creditos/erro.php?err=42";
					}
				} else if(!$b_libera_BancoItau && ($pagto==$FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE'])) {
					if($usuarioGames->b_IsLogin_pagamento_bancoitau()) {	
					?>
					<script language="Javascript">
						alert("Erro: <?php echo $msg_bloqueia_BancoItau; ?>");
					</script>
					<?php					
					$strRedirect = "/creditos/erro.php?err=43";
                                        }
                                }
			}

		}
		// Finaliza pagamentos online de forma diferente -> está em tmp/finaliza_venda_pagto_online_LHPre.php
    
	//Redireciona se ha algum dado invalido
	//----------------------------------------------------
	if($strRedirect != ""){
//            die("dado inválido ".$strRedirect);
		redirect($strRedirect);
	}


//Cria venda
//----------------------------------------------------------------------------------------------------------------------------
$ret = "";
$msg = "";

//Inicia transacao
if($msg == ""){
	$sql = "BEGIN TRANSACTION ";
	$ret = SQLexecuteQuery($sql);
	if(!$ret) $msg = "Erro ao iniciar transação.\n";
}

	//Recupera o usuario do session
	if($msg == ""){
		$usuarioGames = unserialize($_SESSION['dist_usuarioGames_ser']);
	}
	//Insere na tabela de venda
	if($msg == ""){

		$venda_id = obterIdVendaValido();
                if(isset($_SESSION['dist_usuarioGamesOperador_ser'])){
                    $operador_id = unserialize($_SESSION['dist_usuarioGamesOperador_ser'])->getId();
                }
		//Recupera o usuario do session
		$usuario_RiscoClassif = $usuarioGames->getRiscoClassif();
		$sql = "insert into tb_dist_venda_games (" .
				"vg_id, vg_ug_id, vg_data_inclusao, vg_pagto_tipo, " .
				"vg_ultimo_status, vg_ultimo_status_obs";
		// Usuários Pré-pago cadastram vendas que não estarão no próximo corte
		if($usuario_RiscoClassif==2) 
			$sql .= ", vg_cor_codigo";
		$sql .= ") values (";

		$sql .= SQLaddFields($venda_id, "") . ",";
		$sql .= SQLaddFields($usuarioGames->getId(), "") . ",";
		$sql .= SQLaddFields("CURRENT_TIMESTAMP", "") . ",";
		$sql .= SQLaddFields($pagto, "") . ",";
		$sql .= SQLaddFields($STATUS_VENDA['AGUARDANDO_PROCESSAMENTO'], "") . ",";
		$sql .= SQLaddFields("", "s") . "";
		// Usuários Pré-pago cadastram vendas que não estarão no próximo corte
		if($usuario_RiscoClassif==2) 
			$sql .= ", 0";
		$sql .= ")";

		$ret = SQLexecuteQuery($sql);
		if(!$ret) $msg = "Erro ao inserir venda.\n";
		else{
			$ret = ""; //limpa resourceId
                        if(isset($operador_id)){
                            $sql = "INSERT INTO tb_dist_venda_games_operador (vg_id, ugo_id) VALUES (" . $venda_id . ", " . $operador_id . ")";
                            $ret = SQLexecuteQuery($sql);
                            if(!$ret) $msg = "Erro ao ligar operador a venda.\n";
                            else $ret = "";
                        }
		}
		if(strlen($numOrder)==0) $numOrder = $OrderId;

		// Apenas para pagamentos online Lans Pos - salva o IDVenda no registro do pagto online
		if($usuarioGames->b_IsLogin_pagamento())  {

			if (($iforma==$FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) || ($iforma==$FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO']) || ($iforma==$FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']) || ($iforma==$PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC)) { //$FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE']
				if($usuarioGames->bIsLanPos()) {
					// atualiza $venda_id
					$sql = "UPDATE tb_pag_compras SET idvenda=".$venda_id." WHERE numcompra='".$numOrder."'";
					$ret1 = SQLexecuteQuery($sql);
					if(!$ret1) {
						echo "Erro ao atualizar transação de pagamento (3).\n";
						gravaLog_TMP("Erro ao atualizar transação de pagamento (3).\n".$sql."\n");
					}
				}
			} 
		}

	}

	//Insere os modelos na tabela de venda modelos
	//Este é para guardar dados do modelo, valor e qtde do momento da venda
	if($msg == ""){

		//Recupera o usuario do session
		$usuarioId = $usuarioGames->getId();

		$total_geral = 0;
		foreach ($carrinho as $modeloId => $qtde){
                    if($modeloId !== $GLOBALS['NO_HAVE']) {
                        $qtde = intval($qtde);
                        $rs = null;
                        if(!empty($modeloId)) { 
                            $filtro['ogpm_ativo'] = 1;
                            $filtro['ogpm_id'] = $modeloId;
                            $filtro['com_produto'] = true;
                            $instProdutoModelo = new ProdutoModelo;
                            $ret = $instProdutoModelo->obter($filtro, null, $rs);
                            if($rs && pg_num_rows($rs) != 0){
                                    $rs_row = pg_fetch_array($rs);
                                    $valor = $rs_row['ogpm_valor'];
                                    $geral = $valor*$qtde;
                                    $total_geral += $geral;
                            } //end if($rs && pg_num_rows($rs) != 0)
                        } //end if(!empty($modeloId))
                    }else{
                        foreach ($qtde as $codeProd => $vetor_valor) {
                            foreach ($vetor_valor as $p_valor => $quantidade) {
                                $valor = $p_valor;
								$valor = ($valor < 0)? $valor * (-1): $valor;
                                $geral = $valor*$quantidade;
                                $total_geral += $geral;
                            }
                        }
                    }
		} //end foreach
		$total_geral_aux = $total_geral;
//		include "includes/perc_desconto.php";

		foreach ($carrinho as $modeloId => $qtde){
                    
                    $rs = null;
                    $opr_codigo = 0;
                    if(!empty($modeloId)) { 
                        if($modeloId != $NO_HAVE){
                            $filtro['ogpm_ativo'] = 1;
                            $filtro['ogpm_id'] = $modeloId;
                            $filtro['com_produto'] = true;
                            $instProdutoModelo = new ProdutoModelo;
                            $ret = $instProdutoModelo->obter($filtro, null, $rs);
                            if($rs && pg_num_rows($rs) != 0) {
                                    $rs_row = pg_fetch_array($rs);
                                    $opr_codigo = $rs_row['ogp_opr_codigo'];
                            } //end if($rs && pg_num_rows($rs) != 0)
                            $perc_desconto = obtemDesconto($opr_codigo, $pagto, $usuarioId, $total_geral_aux);

                            //Verificando se exige CPF de cliente
                            $test_opr_need_cpf_lh = checkingNeedCPF_LH($opr_codigo);

                            $sql  = "insert into tb_dist_venda_games_modelo( ";
                            $sql .=	"		vgm_vg_id, vgm_ogp_id, vgm_nome_produto, vgm_ogpm_id, vgm_nome_modelo, ";
                            $sql .=	"		vgm_valor, vgm_qtde, vgm_opr_codigo, vgm_pin_valor, vgm_perc_desconto, vgm_pin_request";
                            if($test_opr_need_cpf_lh) {
                                $sql .= ", vgm_nome_cpf, vgm_cpf,vgm_cpf_data_nascimento";
                            }//end if($test_opr_need_cpf_lh)
                            $sql .=	" ) select " . $venda_id . ", ogp.ogp_id, ogp.ogp_nome, ogpm.ogpm_id, ogpm.ogpm_nome, ";
                            $sql .=	"		ogpm.ogpm_valor, " . $qtde . ", ogp.ogp_opr_codigo, ogpm.ogpm_pin_valor, $perc_desconto, ogp_pin_request ";
                            if($test_opr_need_cpf_lh) {
                                $sql .= ", '".$GLOBALS['_SESSION']['NOME_CPF']."', '".$GLOBALS['_SESSION']['CPF_LH']."',to_date('".$GLOBALS['_SESSION']['DATA_NASCIMENTO']."','DD/MM/YYYY')";
                            }//end if($test_opr_need_cpf_lh)
                            $sql .=	"from tb_dist_operadora_games_produto_modelo ogpm ";
                            $sql .=	"inner join tb_dist_operadora_games_produto ogp on ogp.ogp_id = ogpm.ogpm_ogp_id ";
                            $sql .=	"where ogpm.ogpm_id = " . $modeloId;
                            $ret = SQLexecuteQuery($sql);
                            if(!$ret) {
                                    $msg = "Erro ao inserir modelo(s) na venda.\n";
                                    break;
                            } //end if(!$ret)
                        }else{
                            foreach ($qtde as $codeProd => $vetor_valor) {
                                foreach ($vetor_valor as $valor => $quantidade) {
                                    $rs = null;
                                    $filtro['ogp_ativo'] = 1;
                                    $filtro['ogp_id'] = $codeProd;
                                    $filtro['opr'] = 1;
                                    $ret = (new Produto)->obtermelhorado($filtro, null, $rs);
									$valor = ($valor < 0)? $valor * (-1): $valor;
                                    
                                    if(!$rs || pg_num_rows($rs) == 0) $msg = "Nenhum produto disponível no momento.";
                                    else $rs_row = pg_fetch_array($rs);                                 
                                    
                                    $opr_codigo = $rs_row["ogp_opr_codigo"];
                                    
                                    $perc_desconto = obtemDesconto($opr_codigo, $pagto, $usuarioId, $total_geral_aux);

                                    //Verificando se exige CPF de cliente
                                    $test_opr_need_cpf_lh = checkingNeedCPF_LH($opr_codigo);
                                    
                                    $sql  = "insert into tb_dist_venda_games_modelo( ";
                                    $sql .=	"		vgm_vg_id, vgm_ogp_id, vgm_nome_produto, vgm_ogpm_id, vgm_nome_modelo, ";
                                    $sql .=	"		vgm_valor, vgm_qtde, vgm_opr_codigo, vgm_pin_valor, vgm_perc_desconto, vgm_pin_request";
                                    
                                    if($test_opr_need_cpf_lh) {
                                        $sql .= ", vgm_nome_cpf, vgm_cpf,vgm_cpf_data_nascimento";
                                    }//end if($test_opr_need_cpf_lh)
                                    $sql .=	") VALUES (". $venda_id . ", ".$codeProd.", '".$rs_row['ogp_nome']."', (SELECT ogpm_id FROM tb_dist_operadora_games_produto_modelo WHERE ogpm_ogp_id = ".$codeProd." AND ogpm_ativo = 1 LIMIT 1), (SELECT ogpm_nome FROM tb_dist_operadora_games_produto_modelo WHERE ogpm_ogp_id = ".$codeProd." AND ogpm_ativo = 1 LIMIT 1), ";
                                    $sql .=	"               ".$valor.", " . $quantidade . ", ".$rs_row['ogp_opr_codigo'].", ".$valor.", " . $perc_desconto . ", ".$rs_row['ogp_pin_request']." ";
                                    if($test_opr_need_cpf_lh) {
                                        $sql .= ", '".$GLOBALS['_SESSION']['NOME_CPF']."', '".$GLOBALS['_SESSION']['CPF_LH']."',to_date('".$GLOBALS['_SESSION']['DATA_NASCIMENTO']."','DD/MM/YYYY')";
                                    }//end if($test_opr_need_cpf_lh)
                                    $sql .= ");";
                                    //die($sql);
                                    
                                    
                                    
                                    $ret = SQLexecuteQuery($sql);
                                    
                                    if(!$ret) {
                                            $msg = "Erro ao inserir modelo(s) na venda.\n";
                                            break;
                                    } //end if(!$ret)
                                }
                            }
                            
                        }
                    }//end if(!empty($modeloId))
 		} //end foreach
	}

        //Valida pre processamento
	if($msg == ""){
		$msg = processaVendaGamesValidacao($venda_id, $usuarioGames->getId());
	}

	//Adiciona agendamento
	if($msg == ""){
		$sql = "insert into tb_dist_agendamento_execucao (
					ae_data_inclusao, ae_status, ae_tipo, ae_vg_ultimo_status_obs, ae_vg_id 
				) values (CURRENT_TIMESTAMP, 1, 1, '$ultimo_status_obs', $venda_id); ";
		$ret = SQLexecuteQuery($sql);
		if(!$ret) $msg = "Erro ao inserir agendamento.\n"; 
	}


//Finaliza transacao
if($msg == ""){
	$sql = "COMMIT TRANSACTION ";
	$ret = SQLexecuteQuery($sql);
	if(!$ret) $msg = "Erro ao comitar transação.\n";
} else {
    echo "<br> < ROLLBACK > <br>";
	$sql = "ROLLBACK TRANSACTION ";
	$ret = SQLexecuteQuery($sql);
	if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
}
	

//Redireciona se ha algum dado invalido
//----------------------------------------------------
if($msg != ""){
        $strRedirect = "/creditos/mensagem.php?msg=" . urlencode($msg) . "&pt=" . urlencode("Erro") . "&link=" . urlencode("/creditos/produto/produtos_selecionados.php?err=33");
        redirect($strRedirect);
        die();
}
	
//Registra venda
//----------------------------------------------------------------------------------------------------------------------------
$ret = "";
	
//Limpa objetos de uma venda
//----------------------------------------------------
unset($GLOBALS['_SESSION']['dist_carrinho']); 
unset($GLOBALS['_SESSION']['CPF_LH']);
unset($GLOBALS['_SESSION']['NOME_CPF']);
unset($GLOBALS['_SESSION']['DATA_NASCIMENTO']);
unset($GLOBALS['_SESSION']['dist_pagamento.pagto']);

//Log na base
//----------------------------------------------------
usuarios_games_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['VENDA'], null, $venda_id); 

//Redireciona
//----------------------------------------------------
$strRedirect = "/creditos/pagamento/pagto_compr_redirect.php?venda=" . $venda_id;
                                    
//Fechando Conexão
pg_close($connid);
//die($strRedirect);
redirect($strRedirect);
?>
        