<?php
//Função que verifica se o publisher exige CPF do cliente de LANHouse
function checkingNeedCPF_LH(mixed $opr_codigo): mixed
{
    $sql_function = "
    SELECT opr_need_cpf_lh
    FROM operadoras
    WHERE opr_codigo = :opr_codigo
";

    $params = [
        'opr_codigo' => intval($opr_codigo),
    ];

    $rs_function = SQLexecuteQueryParams($sql_function, $params);
    $opr_need_cpf_lh = null;
    if ($rs_function && $rs_function_row = pg_fetch_array($rs_function)) {
        $opr_need_cpf_lh = $rs_function_row['opr_need_cpf_lh'];
    }
    return $opr_need_cpf_lh;
} //end function checkingNeedCPF_LH

//Função que chama a página para inserir o CPF
function cpf_page(): void
{

    $is_data_valid = verificaNome($GLOBALS['_SESSION']['NOME_CPF'] ?? '') && verificaCPF_LH($GLOBALS['_SESSION']['CPF_LH'] ?? '');

    if (!$is_data_valid) {
        $file = RAIZ_DO_PROJETO . 'includes/pdv/form_cpf.php';

        require_once $file;
        die();
    }
} //end function cpf_page

////Valida estrutura de CPF
function verificaCPF_LH(mixed $cpf): bool|int
{
    $cpf = preg_replace('/[^0-9]/', '', (string)$cpf);

    $RecebeCPF = $cpf;

    if (strlen((string)$RecebeCPF) != 11) {
        return 0;
    } else
		if ($RecebeCPF == "00000000000" || $RecebeCPF == "11111111111") {
        return 0;
    } else {
        $Numero[1] = intval(substr((string)$RecebeCPF, 1 - 1, 1));
        $Numero[2] = intval(substr((string)$RecebeCPF, 2 - 1, 1));
        $Numero[3] = intval(substr((string)$RecebeCPF, 3 - 1, 1));
        $Numero[4] = intval(substr((string)$RecebeCPF, 4 - 1, 1));
        $Numero[5] = intval(substr((string)$RecebeCPF, 5 - 1, 1));
        $Numero[6] = intval(substr((string)$RecebeCPF, 6 - 1, 1));
        $Numero[7] = intval(substr((string)$RecebeCPF, 7 - 1, 1));
        $Numero[8] = intval(substr((string)$RecebeCPF, 8 - 1, 1));
        $Numero[9] = intval(substr((string)$RecebeCPF, 9 - 1, 1));
        $Numero[10] = intval(substr((string)$RecebeCPF, 10 - 1, 1));
        $Numero[11] = intval(substr((string)$RecebeCPF, 11 - 1, 1));

        $soma = 10 * $Numero[1] + 9 * $Numero[2] + 8 * $Numero[3] + 7 * $Numero[4] + 6 * $Numero[5] + 5 *
            $Numero[6] + 4 * $Numero[7] + 3 * $Numero[8] + 2 * $Numero[9];
        $soma = $soma - (11 * (intval($soma / 11)));

        if ($soma == 0 || $soma == 1) {
            $resultado1 = 0;
        } else {
            $resultado1 = 11 - $soma;
        }

        if ($resultado1 == $Numero[10]) {
            $soma = $Numero[1] * 11 + $Numero[2] * 10 + $Numero[3] * 9 + $Numero[4] * 8 + $Numero[5] * 7 + $Numero[6] * 6 + $Numero[7] * 5 +
                $Numero[8] * 4 + $Numero[9] * 3 + $Numero[10] * 2;
            $soma = $soma - (11 * (intval($soma / 11)));

            if ($soma == 0 || $soma == 1) {
                $resultado2 = 0;
            } else {
                $resultado2 = 11 - $soma;
            }
            if ($resultado2 == $Numero[11]) {
                return TRUE;
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }
} //end function verificaCPF_LH


//Valida estrutura de Nome
function verificaNome(mixed $nome): bool
{

    $reg = '/^\\s*[a-zA-Zá-úÁ-Ú\']{1,}(\\s+[a-zA-Zá-úÁ-Ú\']{1,}\\s*)+$/';

    if (preg_match($reg, (string)$nome) && strpos((string)$nome, "  ") === false) {
        return TRUE;
    }
    return FALSE;
} //end function verificaNome

function integracao_layout(mixed $type, mixed $data = false): ?string
{
    global $GLOBALS;

    if ($type == "css" || $type == "includes") {
        $url = "https://" . ($_SERVER['SERVER_NAME'] ?? 'localhost');
        $html = "";
        $html .= '<link rel="stylesheet" href="' . $url . '/css/form_cpf.css" type="text/css" />';

        if (!isset($GLOBALS['jquery-1.11.3']) || $GLOBALS['jquery-1.11.3'] != 'on')
            $html .= PHP_EOL . '<script src="' . $url . '/prepag2/js/jquery-1.11.3.min.js"></script>';

        $html .= PHP_EOL . '<script src="' . $url . '/js/form_cpf_valida.js"></script>';
        return $html;
    }
    return null;
} //end function integracao_layout

function mask(mixed $val, mixed $mask): string
{
    $maskared = '';
    $k = 0;
    $val = (string)$val;
    $mask = (string)$mask;
    for ($i = 0; $i <= strlen($mask) - 1; $i++) {
        if ($mask[$i] == '#') {
            if (isset($val[$k]))
                $maskared .= $val[$k++];
        } else {
            if (isset($mask[$i]))
                $maskared .= $mask[$i];
        }
    }
    return $maskared;
} //end function mask

//Função que verifica se o publisher exige CPF do cliente de LANHouse
function checkingIsCompletedData(mixed $url_preview): void
{
    //Variavel retorna necessidade de solicitação de CPF de cliente por parte da LAN House
    $test_opr_need_cpf_lh = false;
    if (!empty($GLOBALS['_SESSION']['dist_carrinho'])) {
        //Recupera carrinho do session
        $carrinho = $GLOBALS['_SESSION']['dist_carrinho'];
        foreach ($carrinho as $modeloId => $qtde) {
            $rs = null;
            $opr_codigo = 0;
            if (!empty($modeloId)) {
                if ($modeloId != ($GLOBALS["NO_HAVE"] ?? '')) {
                    $filtro['ogpm_ativo'] = 1;
                    $filtro['ogpm_id'] = $modeloId;
                    $filtro['com_produto'] = true;
                    if (class_exists('ProdutoModelo')) {
                        $instProdutoModelo = new ProdutoModelo;
                        $ret = $instProdutoModelo->obter($filtro, null, $rs);
                        if ($rs && pg_num_rows($rs) != 0) {
                            $rs_row = pg_fetch_array($rs);
                            if (is_array($rs_row)) {
                                $opr_codigo = $rs_row['ogp_opr_codigo'] ?? 0;
                            }
                        }
                    }
                } else {
                    foreach ($qtde as $codeProd => $vetor_valor) {
                        foreach ($vetor_valor as $valor => $quantidade) {
                            $filtro['ogp_ativo'] = 1;
                            $filtro['ogp_id'] = $codeProd;
                            $filtro['opr'] = 1;
                            if (class_exists('Produto')) {
                                $ret = (new Produto)->obtermelhorado($filtro, null, $rs);

                                if ($rs && pg_num_rows($rs) > 0) {
                                    $rs_row = pg_fetch_array($rs);
                                    if (is_array($rs_row)) {
                                        $opr_codigo = $rs_row["ogp_opr_codigo"] ?? 0;
                                    }
                                }
                            }
                        }
                    }
                }
                //Verificando se exige CPF de cliente
                if ($opr_codigo > 0 && !$test_opr_need_cpf_lh) {
                    $test_opr_need_cpf_lh = (bool)checkingNeedCPF_LH($opr_codigo);
                } //end if(!$test_opr_need_cpf_lh)
            } //end if(!empty($modeloId))
        } //end foreach

    }

    $is_data_valid = verificaNome($GLOBALS['_SESSION']['NOME_CPF'] ?? '') && verificaCPF_LH($GLOBALS['_SESSION']['CPF_LH'] ?? '');
    if ($test_opr_need_cpf_lh && !$is_data_valid) {
        header('Location: ' . $url_preview);
        die();
    } //end if($test_opr_need_cpf_lh && !$is_data_valid)

} //end function checkingIsCompletedData

//Função que verifica se o publisher exige CPF do cliente de LANHouse e exibe a página
function checkingIsCallFormCPF(): void
{
    //Recupera carrinho do session
    $carrinho = $GLOBALS['_SESSION']['dist_carrinho'] ?? [];
    //Variavel retorna necessidade de solicitação de CPF de cliente por parte da LAN House
    $test_opr_need_cpf_lh = false;
    foreach ($carrinho as $modeloId => $qtde) {
        $rs = null;
        $opr_codigo = 0;
        if (!empty($modeloId)) {
            $filtro['ogpm_ativo'] = 1;
            $filtro['ogpm_id'] = $modeloId;
            $filtro['com_produto'] = true;
            if (class_exists('ProdutoModelo')) {
                $ret = ProdutoModelo::obter($filtro, null, $rs);
                if ($rs && pg_num_rows($rs) > 0) {
                    $rs_row = pg_fetch_array($rs);
                    if (is_array($rs_row)) {
                        $opr_codigo = (int)($rs_row['ogp_opr_codigo'] ?? 0);
                    }
                }
            }
            //Verificando se exige CPF de cliente
            if ($opr_codigo > 0 && !$test_opr_need_cpf_lh) {
                $test_opr_need_cpf_lh = (bool)checkingNeedCPF_LH($opr_codigo);
            } //end if(!$test_opr_need_cpf_lh)
        } //end if(!empty($modeloId)) 
    } //end foreach
    $is_data_valid = verificaNome($GLOBALS['_SESSION']['NOME_CPF'] ?? '') && verificaCPF_LH($GLOBALS['_SESSION']['CPF_LH'] ?? '');
    if ($test_opr_need_cpf_lh && !$is_data_valid) {
        include RAIZ_DO_PROJETO . 'includes/pdv/form_cpf.php';
        die();
    } //end if($test_opr_need_cpf_lh && !$is_data_valid)

} //end function checkingIsCallFormCPF
