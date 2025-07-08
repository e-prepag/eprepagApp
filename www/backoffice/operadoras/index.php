<?php

require_once '../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/constantes_opr.php";
require_once $raiz_do_projeto . "includes/gamer/constantes.php";
require_once $raiz_do_projeto . 'class/util/Util.class.php';
require_once $raiz_do_projeto . 'class/util/Json.class.php';

$acao = isset($_REQUEST['acao']) ? $_REQUEST['acao'] : 'listar';
$msg = "";

function formatar_valor($valor)
{
    $valor = trim($valor);

    // Se tiver mais de uma vírgula ou ambos vírgula e ponto ? erro
    if ((substr_count($valor, ',') > 1) || (strpos($valor, ',') !== false && strpos($valor, '.') !== false)) {
        return null;
    }

    // Se tiver vírgula, troca por ponto (decimal)
    if (strpos($valor, ',') !== false) {
        $valor = str_replace(',', '.', $valor);
    }

    return number_format(floatval($valor), 2, '.', '');
}


function geraJsonOperadoraPagamentosBloqueados()
{

	global $FORMAS_PAGAMENTO_DESCRICAO;
	global $FORMAS_PAGAMENTO_INATIVAS;

	$sql = "select opr_codigo,opr_tipo_pagto_bloqueados from operadoras where opr_status = '1' and opr_flag_possui_restricao_pagto = 1";
	$rs_opr = SQLexecuteQuery($sql);
	$arrJson = array();

	if ($rs_opr && pg_num_rows($rs_opr) > 0) {

		while ($rs_opr_row = pg_fetch_array($rs_opr)) {

			$sql = "select ogp_id from tb_operadora_games_produto where ogp_opr_codigo = " . $rs_opr_row['opr_codigo'];
			$rs_opr_prod = SQLexecuteQuery($sql);
			$formasBloqueadas = explode(",", $rs_opr_row['opr_tipo_pagto_bloqueados']);

			if ($rs_opr_prod && pg_num_rows($rs_opr_prod) > 0) {

				while ($rs_opr_prod_row = pg_fetch_array($rs_opr_prod)) {

					$sql = "select ogpm_nome, ogpm_id from tb_operadora_games_produto_modelo where ogpm_ogp_id = " . $rs_opr_prod_row['ogp_id'];
					$rs_opr_prod_mod = SQLexecuteQuery($sql);

					if ($rs_opr_prod_mod && pg_num_rows($rs_opr_prod_mod) > 0) {
						while ($rs_opr_prod_mod_row = pg_fetch_array($rs_opr_prod_mod)) {

							$arrFormasPagto = array();
							$obj = new stdClass();
							$obj->produtoModeloId = $rs_opr_prod_mod_row['ogpm_id'];
							$obj->produtoId = $rs_opr_prod_row['ogp_id'];
							$obj->operadora = $rs_opr_row['opr_codigo'];

							foreach ($FORMAS_PAGAMENTO_DESCRICAO as $idForma => $icone) {

								if (in_array($idForma, $FORMAS_PAGAMENTO_INATIVAS))
									continue;

								//$arrFormasPagto[$idForma] = (in_array($idForma, $formasBloqueadas)) ? true : false;
								$arrFormasPagto[$idForma] = (array_search($idForma, $formasBloqueadas) != false) ? true : false;


							}

							$obj->formasPagamento = $arrFormasPagto;
							$arrJson[$rs_opr_prod_mod_row['ogpm_id']] = $obj;

							if ($_SERVER["REMOTE_ADDR"] == "201.93.162.169" && $idForma == "S" && $rs_opr_row['opr_codigo'] == 45) {
								echo "<script>console.log(" . json_encode($arrFormasPagto) . ")</script>";
							}

							unset($obj);
						}

					}
				}
			}

		}


		if (isset($arrJson)) {

			$json = new Json;
			$json->setFullPath(DIR_JSON);
			$json->setArrJsonFiles(unserialize(ARR_JSON_PRODUTOS_MEIOS_DE_PAGAMENTOS_BLOQUEADOS_GAMER));
			$json->refresh($arrJson);
		}

		if ($_SERVER["REMOTE_ADDR"] == "201.93.162.169") {
			echo "<script> console.log(" . json_encode($arrJson) . ") </script>";
			//exit;
		}

	}



}

if ($acao == 'inserir') {
	if (!in_array($opr_codigo, $OPR_CODIGO_BLOCK)) {
		$sql = "select MAX(opr_codigo) as max_opr_codigo from operadoras where opr_codigo NOT IN (" . implode(",", $OPR_CODIGO_BLOCK) . ");";
		$rs_opr_codigo = SQLexecuteQuery($sql);
		if ($rs_opr_codigo_row = pg_fetch_array($rs_opr_codigo)) {
			$opr_codigo = $rs_opr_codigo_row['max_opr_codigo'] + 1;
			while (in_array($opr_codigo, $OPR_CODIGO_BLOCK)) {
				$opr_codigo++;
			}
			$sql = "select MAX(opr_codigo) as max_opr_codigo from operadoras;";
			$rs_opr_ordem = SQLexecuteQuery($sql);
			$rs_opr_ordem_row = pg_fetch_array($rs_opr_ordem);
			$opr_faturamento_ordem = $rs_opr_ordem_row['max_opr_codigo'];

			if (isset($_POST['formasPagamento']) && !empty($_POST['formasPagamento'])) {

				$strFormasPagamento = implode(",", $_POST['formasPagamento']);
				$opr_flag_possui_restricao_pagto = 1;
				$opr_tipo_pagto_bloqueados = "'$strFormasPagamento'";

			} else {
				$opr_flag_possui_restricao_pagto = "0";
				$opr_tipo_pagto_bloqueados = "''";
			}

			// Retirando os espaços em Branco da variável abaixo
			$opr_data_inicio_operacoes = trim($opr_data_inicio_operacoes);

			$sql = "INSERT INTO operadoras (
                        opr_flag_possui_restricao_pagto,
                        opr_tipo_pagto_bloqueados,
						opr_cnpj,
						opr_razao,
						opr_im,
						opr_endereco,
						opr_numero,
						opr_complemento,
						opr_bairro,
						opr_cep,
						opr_cidade,
						opr_estado,
						opr_pais,
						opr_codigo, 
						opr_nome,";
			if (!empty($opr_nome_loja))
				$sql .= "opr_nome_loja,";
			if (!empty($opr_internacional)) {
				$sql .= "opr_internacional,";
			}
			$sql .= "	opr_numero_conta,
						opr_tipo_conta,
						opr_numero_roteamento,
						opr_banco_nome,
						opr_banco_endereco,
						opr_banco_cidade,
						opr_banco_telefone,
						opr_moeda_corrente,
						opr_iban,
						opr_bic_code,
						opr_numero_contrato,
						opr_tipo, 
						cupom_nomeopr,
						cupom_linhasenha,
						id,
						opr_contato, 
						opr_status,
                        opr_contato_epp,
                        opr_min_repasse,
                        opr_internacional_alicota,
						opr_possui_detalhe,
                        opr_site, 
						opr_cont_fone,
                        opr_email_dimp,
                        opr_cotacao_dolar,
						opr_cont_mail, 
						opr_ddd_string,
						opr_uf, 
						opr_ban_pos, 
						opr_ddd, 
						opr_importa,
						opr_prestacao_lote, 
						opr_ordem, 
						opr_mostra_remuneracao, 
						opr_pin_online, 
						opr_seq, 
						opr_master_codigo,
						opr_pedido_estoque_prazo, 
						opr_pedido_estoque_prazo_comentario, 
						opr_campo_1, 
						opr_campo_2,
						opr_faturamento_ordem,
                        opr_need_cpf_lh,
                        opr_vinculo_empresa,
                        opr_desmembra_cartao,
                        opr_emite_cartao_conosco,
                        opr_distribui_ponto_certo,
                        opr_prefixo_ponto_certo,
                        opr_contabiliza_utilizacao,";
			if (!empty($opr_banco_intermediario)) {
				$sql .= "opr_banco_intermediario,";
			}
			if (!empty($opr_bslan)) {
				$sql .= "opr_bslan,";
			}
			if (!empty($opr_bslan_rule) && !empty($opr_bslan)) {
				$sql .= "opr_bslan_rule,";
			}
			if (!empty($opr_data_inicio_operacoes)) {
				$sql .= "opr_data_inicio_operacoes,";
			}
			if (!empty($opr_data_inicio_contabilizacao_utilizacao)) {
				$sql .= "opr_data_inicio_contabilizacao_utilizacao,";
			}
			$sql .= "	 opr_pin_epp_formato,
                         opr_markup,
                         opr_facilitadora,
                         merchant_id_bexs) 
						VALUES (
                                    $opr_flag_possui_restricao_pagto,
                                    $opr_tipo_pagto_bloqueados,
									'$opr_cnpj',
									'$opr_razao',
									'$opr_im',
									'$opr_endereco',
									'$opr_numero',
									'$opr_complemento',
									'$opr_bairro',
									'$opr_cep',
									'$opr_cidade',
									'$opr_estado',
									'$opr_pais',
									$opr_codigo, 
									'$opr_nome',";
			if (!empty($opr_nome_loja)) {
				$sql .= "			'$opr_nome_loja',";
			}
			if (!empty($opr_internacional)) {
				$sql .= "			$opr_internacional,";
			}
			$sql .= "				'$opr_numero_conta',
									'$opr_tipo_conta',
									'$opr_numero_roteamento',
									'$opr_banco_nome',
									'$opr_banco_endereco',
									'$opr_banco_cidade',
									'$opr_banco_telefone',
									'$opr_moeda_corrente',
									'$opr_iban',
									'$opr_bic_code',
									'$opr_numero_contrato',
									1, 
									'$opr_nome', 
									'NUMERO DO PIN',
									'131313131313131', 
									'$opr_contato',
									'$opr_status',
                                    '$opr_contato_epp',
                                    " . (str_replace(",", ".", $opr_min_repasse) * 1) . ",
                                    $opr_internacional_alicota,
									$opr_possui_detalhe,
                                    '$opr_site', 
									'$opr_cont_fone',
                                    '$opr_email_dimp',
                                    $opr_cotacao_dolar,
									'$opr_cont_mail', 
									'11-19', 
									'SP', 
									'$opr_nome',
									'11-19;21;22;24;27;28;31-35;37;38;41-49;51;53-55;61-69;71;73;74;75;77;79;81-89;91-99',
									1, 
									0, 
									$opr_codigo, 
									0, 
									0, 
									0, 
									$opr_codigo, 
									$opr_pedido_estoque_prazo, 
									'$opr_pedido_estoque_prazo_comentario', 
									'codigo', 
									'serial',
									$opr_faturamento_ordem,
                                    $opr_need_cpf_lh,
                                    $opr_vinculo_empresa,
                                    $opr_desmembra_cartao, 
                                    $opr_emite_cartao_conosco,
                                    $opr_distribui_ponto_certo,
                                    '$opr_prefixo_ponto_certo', 
                                    $opr_contabiliza_utilizacao,";
			if (!empty($opr_banco_intermediario)) {
				$sql .= "			$opr_banco_intermediario,";
			}
			if (!empty($opr_bslan)) {
				$sql .= "			$opr_bslan,";
			}
			if (!empty($opr_bslan_rule) && !empty($opr_bslan)) {
				$sql .= "			'" . str_replace("'", '"', $opr_bslan_rule) . "',";
			}
			if (!empty($opr_data_inicio_operacoes)) {
				$sql .= "			to_date('" . $opr_data_inicio_operacoes . "','DD/MM/YYYY'),";
			}
			if (!empty($opr_data_inicio_contabilizacao_utilizacao)) {
				$sql .= "			to_date('" . $opr_data_inicio_contabilizacao_utilizacao . "','DD/MM/YYYY'),";
			}
			if ($opr_pin_epp_formato == '')
				$sql .= "NULL,";
			else
				$sql .= $opr_pin_epp_formato . ",";
			$sql .= $opr_markup . ", " .
				$opr_facilitadora . ", " .
				$merchant_id_bexs . ");";
			//echo $sql."<br>";
			$rs_operadoras = SQLexecuteQuery($sql);
			if (!$rs_operadoras) {
				$msg .= "Erro ao salvar informa&ccedil;&otilde;es da Operadora. ($sql)<br>";
			} else {
				if ($opr_valores && count($opr_valores) > 0) {
					$values = [];
					foreach ($opr_valores as $valor) {
						$valor = floatval($valor);
						$values[] = "($opr_codigo, $valor)";
					}

					$sqlValores = "INSERT INTO operadoras_valores (opr_codigo, valor) VALUES " . implode(", ", $values) . ";";
					$rs_valores = SQLexecuteQuery($sqlValores);
					if (!$rs_valores) {
						$msg .= "Erro ao salvar os valores da Operadora. ($sqlValores)<br>";
					}
				}


				if (!empty($opr_banco_intermediario)) {
					$sql = "insert into operadoras_banco_intermediario (
								opr_codigo, 
								obi_banco_nome, 
								obi_bic_code, 
								obi_numero_conta
								) 
							values  (
								$opr_codigo,
								'$obi_banco_nome',
								'$obi_bic_code',
								'$obi_numero_conta');";

					$rs_operadoras = SQLexecuteQuery($sql);
					if (!$rs_operadoras) {
						$msg .= "Erro ao salvar as informacoes de Banco Intermediario da Operadora. ($sql)<br>";
					}
				}
			}

			geraJsonOperadoraPagamentosBloqueados();
		} else
			$msg .= "Erro ao selecionar o NOVO opr_codigo.<br>";
	} else
		$msg .= "Opr_Codigo da Operadora n&atilde;o permitido.<br>";
	$acao = 'listar';
}

if ($acao == 'atualizar') {

	if (isset($_POST['formasPagamento']) && !empty($_POST['formasPagamento'])) {

		$strFormasPagamento = implode(",", $_POST['formasPagamento']);
		$sqlOprFormasPagto = "opr_flag_possui_restricao_pagto = 1, ";
		$sqlOprFormasPagto .= "opr_tipo_pagto_bloqueados = '$strFormasPagamento', ";

	} else {
		$sqlOprFormasPagto = "opr_flag_possui_restricao_pagto = 0, ";
		$sqlOprFormasPagto .= "opr_tipo_pagto_bloqueados = '', ";
	}

	$sql = "UPDATE operadoras SET
                    $sqlOprFormasPagto
					opr_cnpj		= '" . $opr_cnpj . "',
					opr_razao		= '" . $opr_razao . "',
					opr_im			= '" . $opr_im . "',
					opr_endereco	= '" . $opr_endereco . "',
					opr_numero		= '" . $opr_numero . "',
					opr_complemento	= '" . $opr_complemento . "',
					opr_bairro		= '" . $opr_bairro . "',
					opr_cep			= '" . $opr_cep . "',
					opr_cidade		= '" . $opr_cidade . "',
					opr_estado		= '" . $opr_estado . "',
					opr_pais		= '" . $opr_pais . "',
					opr_status		= '" . $opr_status . "',
					opr_contato_epp		= '" . $opr_contato_epp . "',
					opr_min_repasse		= " . (str_replace(",", ".", $opr_min_repasse) * 1) . ",
                    opr_internacional_alicota = " . $opr_internacional_alicota . ",
					opr_possui_detalhe      = " . $opr_possui_detalhe . ",
					opr_nome		= '" . $opr_nome . "', 
                    opr_troca_nacional_internacional =  '" . ($opr_troca_nacional_internacional * 1) . "',
                    opr_nome_loja           = '" . $opr_nome_loja . "',";
	if (!empty($opr_internacional)) {
		$sql .= "	opr_internacional	= " . $opr_internacional . ",
					opr_moeda_corrente	='" . $opr_moeda_corrente . "',
					opr_numero_contrato	='" . $opr_numero_contrato . "',";
	} else {
		$sql .= "	opr_internacional	= 0,
					opr_moeda_corrente	='Real',
					opr_numero_contrato	='',";
	}
	if (!empty($opr_banco_intermediario)) {
		$sql .= "	opr_banco_intermediario=" . $opr_banco_intermediario . ",";
	} else {
		$sql .= "	opr_banco_intermediario = 0,";
	}
	if (!empty($opr_bslan)) {
		$sql .= "	opr_bslan=" . $opr_bslan . ",";
	} else {
		$sql .= "	opr_bslan = 0,";
	}
	if (!empty($opr_bslan_rule) && !empty($opr_bslan)) {
		$sql .= "	opr_bslan_rule='" . str_replace("'", '"', $opr_bslan_rule) . "',";
	} else {
		$sql .= "	opr_bslan_rule = '',";
	}
	if (!empty($opr_comissao_por_volume)) {
		$sql .= "	opr_comissao_por_volume = 1,";
	} else {
		$sql .= "	opr_comissao_por_volume = 0,";
	}
	$sql .= "		opr_numero_conta	='" . $opr_numero_conta . "',
					opr_tipo_conta		='" . $opr_tipo_conta . "',
					opr_numero_roteamento ='" . $opr_numero_roteamento . "',
					opr_banco_nome		='" . $opr_banco_nome . "',
					opr_banco_endereco	='" . $opr_banco_endereco . "',
					opr_banco_cidade	='" . $opr_banco_cidade . "',
					opr_banco_telefone	='" . $opr_banco_telefone . "',
					opr_iban			='" . $opr_iban . "',
					opr_bic_code		='" . $opr_bic_code . "',
					opr_contato			='" . $opr_contato . "',
					opr_site			='" . $opr_site . "',           
					opr_cont_fone		='" . $opr_cont_fone . "',
					opr_cont_mail		='" . $opr_cont_mail . "',
                    opr_email_dimp		='" . $opr_email_dimp . "',
                    opr_cotacao_dolar	= " . $opr_cotacao_dolar . ",
					opr_pedido_estoque_prazo = " . $opr_pedido_estoque_prazo . ",
					opr_pedido_estoque_prazo_comentario = '" . $opr_pedido_estoque_prazo_comentario . "',
                    opr_need_cpf_lh = " . ($opr_need_cpf_lh * 1) . ",
                    opr_vinculo_empresa = " . ($opr_vinculo_empresa * 1) . ",
                    opr_desmembra_cartao = " . ($opr_desmembra_cartao * 1) . ",
                    opr_emite_cartao_conosco= " . $opr_emite_cartao_conosco . ",
                    opr_distribui_ponto_certo   =" . $opr_distribui_ponto_certo . ",
                    opr_prefixo_ponto_certo     ='" . $opr_prefixo_ponto_certo . "', 
                    opr_contabiliza_utilizacao = " . $opr_contabiliza_utilizacao . ",";
	$opr_data_inicio_operacoes = trim($opr_data_inicio_operacoes);
	if (!empty($opr_data_inicio_operacoes)) {
		$sql .= " opr_data_inicio_operacoes = to_date('" . $opr_data_inicio_operacoes . "','DD/MM/YYYY'),";
	} else {
		$sql .= " opr_data_inicio_operacoes = null,";
	}
	if (!empty($opr_data_inicio_contabilizacao_utilizacao)) {
		$sql .= " opr_data_inicio_contabilizacao_utilizacao = to_date('" . $opr_data_inicio_contabilizacao_utilizacao . "','DD/MM/YYYY'),";
	} else {
		$sql .= " opr_data_inicio_contabilizacao_utilizacao = null,";
	}
	if ($opr_pin_epp_formato == '') {
		$sql .= "opr_pin_epp_formato = NULL,";
	} else {
		$sql .= "opr_pin_epp_formato =" . $opr_pin_epp_formato . ",";
	}
	$sql .= " opr_markup =" . $opr_markup . ", 
                  opr_facilitadora = " . $opr_facilitadora . ", 
                  merchant_id_bexs = " . $merchant_id_bexs;
	$sql .= "	WHERE opr_codigo = $opr_codigo";

	//echo $sql."<br>";
	$rs_operadoras = SQLexecuteQuery($sql);
	//Limpando a tabela de cotações caso seja alterado para apenas uma cotação
	if ($opr_cotacao_dolar == 0) {
		$data = mktime(0, 0, 0, date("n") - 1, 1, date('Y'));
		$mesAno = date('m/Y', $data);
		list($mes, $ano) = explode("/", $mesAno);
		$sql = "DELETE FROM cotacao_dolar where cd_data >= '" . $ano . "-" . $mes . "-01 00:00:00' AND to_char(cd_data,'DD') <> '01' AND opr_codigo = " . $opr_codigo . " AND cd_freeze = 0;";
		$rs_delete_cotacao = SQLexecuteQuery($sql);
		if (!$rs_delete_cotacao) {
			$msg .= "Erro ao deletar as cotações de dólar para o operador $opr_codigo! <br>";
		}
	}
	if (!$rs_operadoras) {
		$msg .= "Erro ao atualizar informa&ccedil;&otilde;es da Operadora. ($sql)<br>";
	} else {
		//Atualizar valores
		$sql = "SELECT valor FROM operadoras_valores WHERE opr_codigo = $opr_codigo;";
		$rs = SQLexecuteQuery($sql);
		$valores_atuais = [];
		while ($row = pg_fetch_array($rs)) {
			$valores_atuais[] = formatar_valor($row['valor']);
		}

		$valores_novos = array_map(function ($v) {
			return formatar_valor($v);
		}, $opr_valores);

		$valores_para_inserir = array_diff($valores_novos, $valores_atuais);
		$valores_para_excluir = array_diff($valores_atuais, $valores_novos);

		if (!empty($valores_para_inserir)) {
			$values = [];
			foreach ($valores_para_inserir as $valor) {
				$values[] = "($opr_codigo, $valor)";
			}
			$sql = "INSERT INTO operadoras_valores (opr_codigo, valor) VALUES " . implode(", ", $values) . ";";
			SQLexecuteQuery($sql);
		}

		if (!empty($valores_para_excluir)) {
			$valores_str = implode(", ", $valores_para_excluir);
			$sql = "DELETE FROM operadoras_valores WHERE opr_codigo = $opr_codigo AND valor IN ($valores_str);";
			SQLexecuteQuery($sql);
		}

		// Banco Intermediario
		if (!empty($opr_banco_intermediario)) {
			$sql = "SELECT * FROM operadoras_banco_intermediario WHERE opr_codigo = $opr_codigo";
			$rs_operadoras = SQLexecuteQuery($sql);
			$existe_registro = pg_num_rows($rs_operadoras);
			if (empty($existe_registro)) {
				$sql = "insert into operadoras_banco_intermediario (
									opr_codigo, 
									obi_banco_nome, 
									obi_bic_code, 
									obi_numero_conta
									) 
								values  (
									$opr_codigo,
									'$obi_banco_nome',
									'$obi_bic_code',
									'$obi_numero_conta');";
				$rs_operadoras = SQLexecuteQuery($sql);
				if (!$rs_operadoras) {
					$msg .= "Erro ao salvar as informacoes de Banco Intermediario da Operadora. ($sql)<br>";
				}
			} else {
				$sql = "UPDATE operadoras_banco_intermediario SET
								obi_banco_nome		= '" . $obi_banco_nome . "',
								obi_bic_code		= '" . $obi_bic_code . "',
								obi_numero_conta	= '" . $obi_numero_conta . "'
						WHERE opr_codigo = " . $opr_codigo;
				$rs_operadoras = SQLexecuteQuery($sql);
				if (!$rs_operadoras) {
					$msg .= "Erro ao atualizar as informacoes de Banco Intermediario da Operadora. ($sql)<br>";
				}
			}
		}

		geraJsonOperadoraPagamentosBloqueados();

	}
	$acao = 'listar';
}

if ($acao == 'editar') {
	$sql = "SELECT	*,to_char(opr_data_inicio_operacoes, 'DD/MM/YYYY') as opr_data_inicio_operacoes_formatada, to_char(opr_data_inicio_contabilizacao_utilizacao, 'DD/MM/YYYY') as opr_data_inicio_contabilizacao_utilizacao_formatada,
					(100*obtem_comissao(opr_codigo, 'M', null, 0)) as comiss_m, 
					(100*obtem_comissao(opr_codigo, 'E', null, 0)) as comiss_e, 
					(100*obtem_comissao(opr_codigo, 'L', null, 0)) as comiss_l, 
					(100*obtem_comissao(opr_codigo, 'C', null, 0)) as comiss_c, 
					(100*obtem_comissao(opr_codigo, 'P', null, 0)) as comiss_p    
		FROM operadoras WHERE opr_codigo = $opr_codigo";
	$rs_operadoras = SQLexecuteQuery($sql);
	if (!($rs_operadoras_row = pg_fetch_array($rs_operadoras))) {
		$msg .= "Erro ao consultar informa&ccedil;&otilde;es da Operadora. ($sql)<br>";
	} else {
		$opr_codigo = $rs_operadoras_row['opr_codigo'];
		$opr_nome = $rs_operadoras_row['opr_nome'];

		$sql = "SELECT valor FROM operadoras_valores WHERE opr_codigo = $opr_codigo;";
		$rs = SQLexecuteQuery($sql);
		$opr_valores = [];
		while ($row = pg_fetch_array($rs)) {
			$opr_valores[] = formatar_valor($row['valor']);
		}

		$opr_nome_loja = $rs_operadoras_row['opr_nome_loja'];
		$opr_contato = $rs_operadoras_row['opr_contato'];
		$opr_site = $rs_operadoras_row['opr_site'];
		$opr_cont_fone = $rs_operadoras_row['opr_cont_fone'];
		$opr_cont_mail = $rs_operadoras_row['opr_cont_mail'];
		$opr_email_dimp = $rs_operadoras_row['opr_email_dimp'];
		$opr_cotacao_dolar = $rs_operadoras_row['opr_cotacao_dolar'];
		$opr_pedido_estoque_prazo = $rs_operadoras_row['opr_pedido_estoque_prazo'];
		$opr_pedido_estoque_prazo_comentario = $rs_operadoras_row['opr_pedido_estoque_prazo_comentario'];
		$opr_pin_epp_formato = $rs_operadoras_row['opr_pin_epp_formato'];
		$opr_product_type = $rs_operadoras_row['opr_product_type'];
		$opr_cnpj = $rs_operadoras_row['opr_cnpj'];
		$opr_razao = $rs_operadoras_row['opr_razao'];
		$opr_im = $rs_operadoras_row['opr_im'];
		$opr_endereco = $rs_operadoras_row['opr_endereco'];
		$opr_numero = $rs_operadoras_row['opr_numero'];
		$opr_complemento = $rs_operadoras_row['opr_complemento'];
		$opr_bairro = $rs_operadoras_row['opr_bairro'];
		$opr_cep = $rs_operadoras_row['opr_cep'];
		$opr_cidade = $rs_operadoras_row['opr_cidade'];
		$opr_estado = $rs_operadoras_row['opr_estado'];
		$opr_pais = $rs_operadoras_row['opr_pais'];
		$opr_internacional = $rs_operadoras_row['opr_internacional'];
		$opr_numero_conta = $rs_operadoras_row['opr_numero_conta'];
		$opr_tipo_conta = $rs_operadoras_row['opr_tipo_conta'];
		$opr_numero_roteamento = $rs_operadoras_row['opr_numero_roteamento'];
		$opr_banco_nome = $rs_operadoras_row['opr_banco_nome'];
		$opr_banco_endereco = $rs_operadoras_row['opr_banco_endereco'];
		$opr_banco_cidade = $rs_operadoras_row['opr_banco_cidade'];
		$opr_banco_telefone = $rs_operadoras_row['opr_banco_telefone'];
		$opr_moeda_corrente = $rs_operadoras_row['opr_moeda_corrente'];
		$opr_iban = $rs_operadoras_row['opr_iban'];
		$opr_bic_code = $rs_operadoras_row['opr_bic_code'];
		$opr_numero_contrato = $rs_operadoras_row['opr_numero_contrato'];
		$opr_banco_intermediario = $rs_operadoras_row['opr_banco_intermediario'];
		$opr_bslan = $rs_operadoras_row['opr_bslan'];
		$opr_bslan_rule = $rs_operadoras_row['opr_bslan_rule'];
		$opr_comissao_por_volume = $rs_operadoras_row['opr_comissao_por_volume'];
		$comiss_m = $rs_operadoras_row['comiss_m'];
		$comiss_e = $rs_operadoras_row['comiss_e'];
		$comiss_l = $rs_operadoras_row['comiss_l'];
		$comiss_c = $rs_operadoras_row['comiss_c'];
		$comiss_p = $rs_operadoras_row['comiss_p'];
		$opr_status = $rs_operadoras_row['opr_status'];
		$opr_contato_epp = $rs_operadoras_row['opr_contato_epp'];
		$opr_min_repasse = $rs_operadoras_row['opr_min_repasse'];
		$opr_internacional_alicota = $rs_operadoras_row['opr_internacional_alicota'];
		$opr_possui_detalhe = $rs_operadoras_row['opr_possui_detalhe'];
		$opr_need_cpf_lh = $rs_operadoras_row['opr_need_cpf_lh'];
		$opr_vinculo_empresa = $rs_operadoras_row['opr_vinculo_empresa'];
		$opr_data_inicio_operacoes = $rs_operadoras_row['opr_data_inicio_operacoes_formatada'];
		$opr_desmembra_cartao = $rs_operadoras_row['opr_desmembra_cartao'];
		$opr_emite_cartao_conosco = $rs_operadoras_row['opr_emite_cartao_conosco'];
		$opr_distribui_ponto_certo = $rs_operadoras_row['opr_distribui_ponto_certo'];
		$opr_prefixo_ponto_certo = $rs_operadoras_row['opr_prefixo_ponto_certo'];
		$opr_contabiliza_utilizacao = $rs_operadoras_row['opr_contabiliza_utilizacao'];
		$opr_data_inicio_contabilizacao_utilizacao = $rs_operadoras_row['opr_data_inicio_contabilizacao_utilizacao_formatada'];
		$opr_troca_nacional_internacional = $rs_operadoras_row['opr_troca_nacional_internacional'];
		$opr_flag_possui_restricao_pagto = $rs_operadoras_row['opr_flag_possui_restricao_pagto'];
		$opr_tipo_pagto_bloqueados = $rs_operadoras_row['opr_tipo_pagto_bloqueados'];
		$opr_markup = $rs_operadoras_row['opr_markup'];
		$opr_facilitadora = $rs_operadoras_row['opr_facilitadora'];
		$merchant_id_bexs = $rs_operadoras_row['merchant_id_bexs'];

		if ($opr_tipo_pagto_bloqueados != '') {
			$arrTipoPagtoBloqueado = explode(",", $opr_tipo_pagto_bloqueados);
		} else {
			$arrTipoPagtoBloqueado = array();
		}

		//                $opr_codigo
		$sqlTrocaNacionalInternacional = "select * from operadoras_troca_nacional_internacional where opr_codigo = $opr_codigo  order by otni_id desc";
		$rs_TrocaNacionalInternacional = SQLexecuteQuery($sqlTrocaNacionalInternacional);


		if (pg_num_rows($rs_operadoras) > 0)
			include 'operadoras_edt.php';
		else
			$acao = 'listar';
	}
}

if ($acao == 'novo') {
	include 'operadoras_edt.php';
}

if ($acao == 'listar') {
	include 'operadoras_lst.php';
}
echo $msg;
?>
</body>

</html>