<?php require_once __DIR__ . '/../../../includes/constantes_url.php'; ?>
<?php
require_once "../../../includes/constantes.php";
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "gamer/main.php";

require_once DIR_CLASS . "util/Util.class.php";
require_once DIR_CLASS . "gamer/classInibeAtaque.php";
header("Content-Type: text/html; charset=ISO-8859-1", true);

if (Util::isAjaxRequest()) {
    //validacao
    $msg = "";

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) die('');
    if (!filter_var($emailConf, FILTER_VALIDATE_EMAIL)) die('');
    //habboId
    if ($msg == "") {
        if (strlen($habboId) > 50) $msg = "O Nome Habbo deve ter até 50 caracteres.\n";
    }

    $testeAtaque = new InibeAtaque($email, $_SESSION['epp_remote_addr']);

    if ($testeAtaque->verificaAtaque()) {
        die("Stop");
    }
    // Teste de Carrinho OK
    $carrinho = $_SESSION['carrinho'];
    if (!$carrinho || count($carrinho) == 0) {
        die(utf8_encode("Carrinho vazio no momento."));
    }

    // Teste de CPF => Inicio
    $flagCpf = false;
    foreach ($carrinho as $modeloId => $qtde) {
        $opr_codigo = get_opr_codigo_by_modelo_id($modeloId);
        if (checkingNeedCPFGamer($opr_codigo)) {
            $flagCpf = true;
            break;
        }
    } //end foreach
    if ($flagCpf) {
        if (!(is_array($_SESSION['cpf_boleto_ex']) &&
            !empty($_SESSION['cpf_boleto_ex']['cpf']) &&
            !empty($_SESSION['cpf_boleto_ex']['data_nascimento']) &&
            !empty($_SESSION['cpf_boleto_ex']['nome']))) {
            die(utf8_encode("CPF não informado!"));
        }
    } //end if($flagCpf)
    // Teste de CPF => Fim

    // Escolhe boleto
    $opr_codigo_money_express = $_SESSION['opr_codigo_money_express'];
    //echo "opr_codigo_money_express: $opr_codigo_money_express<br>";
    if (b_Is_Boleto_Express_Bradesco()) {
        $url_boleto = "/boletos/gamer/boleto_bradesco.php";
    } elseif (b_Is_Boleto_Express_Santander()) {
        $url_boleto = "/SICOB/BoletoWebBanespaCommerce.php";
    } elseif (b_Is_Boleto_Express_Itau($email, $opr_codigo_money_express)) {
        $url_boleto = "/SICOB/BoletoWebItauCommerce.php";
    } else {
        die("Problema ao selecionar Boleto. Por favor, entre em contato com o suporte e informe o ERRO 87658. Obrigado.");
    }

    //Produtos
    if ($msg == "") {
        if (!$produtos)
            $msg = "Nenhum produto selecionado.\n";
        else if (!is_array($produtos) && (trim($produtos) == "" || !is_numeric($produtos)))
            $msg = "Produto inválido.\n";
        else if (is_array($produtos)) {
            if (count($produtos) == 0)
                $msg = "Nenhum produto selecionado.\n";
            else {
                for ($i = 0; $i < count($produtos); $i++) {
                    if (!$produtos[$i] || trim($produtos[$i]) == "" || !is_numeric($produtos[$i]))
                        $msg = "Produto inválido.\n";

                    break;
                }
            }
        }
    }

    //Quantidades
    if ($msg == "") {
        $blAchou = false;
        for ($i = 0; $i < count($produtos); $i++) {
            $qtde = $_POST['q' . $produtos[$i]];
            if ($qtde && trim($qtde) != "" && is_numeric($qtde) && $qtde > 0)
                $blAchou = true;
        }

        if (!$blAchou)
            $msg = "Nenhuma quantidade selecionada.\n";
    }

    //Total
    if ($msg == "") {
        $total150 = 0;
        for ($i = 0; $i < count($produtos); $i++) {
            $qtde = $_POST['q' . $produtos[$i]];
            $valortmp = $_POST['v' . $produtos[$i]];

            if (($qtde && trim($qtde) != "" && is_numeric($qtde) && $qtde > 0) && ($valortmp && trim($valortmp) != "" && is_numeric($valortmp) && $valortmp > 0)) {
                $total150 += $valortmp * $qtde;
            }
        }
        if ($total150 > $GLOBALS['RISCO_GAMERS_VALOR_MAX'])
            $msg = "O valor máximo por boleto é de R$" . number_format($GLOBALS['RISCO_GAMERS_VALOR_MAX'], 2, ",", ".") . "\n\nPor favor, preencha novamente o pedido.\n";
    }

    if ($msg != "") {
        $msg = "<script>manipulaModal(1,'" . str_replace("\n", "\\n", $msg) . "','Erro') ; $('#prosseguir').removeAttr('disabled');</script>";
        echo $msg;
        exit;
    }

    //Usuario
    $usuarioId = $GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY'];

    //Inicia transacao
    if ($msg == "") {
        $sql = "BEGIN TRANSACTION ";
        $ret = SQLexecuteQuery($sql);
        if (!$ret) $msg = "Erro ao iniciar transação.\n";
    }

    //Gera a venda
    if ($msg == "") {
        $venda_id = obterIdVendaValido();

        $sql = "
                    INSERT INTO tb_venda_games (
                        vg_id,
                        vg_ug_id,
                        vg_data_inclusao,
                        vg_pagto_tipo,
                        vg_ultimo_status,
                        vg_ultimo_status_obs,
                        vg_ex_email,
                        vg_http_referer_origem,
                        vg_http_referer,
                        vg_http_referer_ip
                    ) VALUES (
                        $1, $2, CURRENT_TIMESTAMP, $3, $4, $5, $6, $7, $8, $9
                    )
                ";

        $params = array(
            $venda_id,                                   // $1
            $usuarioId,                                  // $2
            $GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO'], // $3
            $GLOBALS['STATUS_VENDA']['PEDIDO_EFETUADO'],     // $4
            "",                                          // $5 vg_ultimo_status_obs
            $email,                                      // $6 vg_ex_email
            $_SESSION['epp_origem'],                     // $7 vg_http_referer_origem
            $_SESSION['epp_origem_referer'],            // $8 vg_http_referer
            $_SESSION['epp_remote_addr']                 // $9 vg_http_referer_ip
        );

        $ret = SQLexecuteQueryParams($sql, $params);
        if (!$ret) $msg = "Erro ao inserir venda.\n";
    }

    //Insere os modelos na tabela de venda modelos
    //Este eh para guardar dados do modelo, valor e qtde do momento da venda
    if ($msg == "") {
        for ($i = 0; $i < count($produtos); $i++) {
            $qtde = $_POST['q' . $produtos[$i]];
            if ($qtde && trim($qtde) != "" && is_numeric($qtde) && $qtde > 0) {
                $sql = "
                        INSERT INTO tb_venda_games_modelo (
                            vgm_vg_id,
                            vgm_ogp_id,
                            vgm_nome_produto,
                            vgm_ogpm_id,
                            vgm_nome_modelo,
                            vgm_valor,
                            vgm_qtde,
                            vgm_opr_codigo,
                            vgm_pin_valor,
                            vgm_game_id,
                            vgm_valor_eppcash
                        )
                        SELECT 
                            $1, 
                            ogp.ogp_id, 
                            ogp.ogp_nome, 
                            ogpm.ogpm_id, 
                            ogpm.ogpm_nome, 
                            ogpm.ogpm_valor, 
                            $2, 
                            ogp.ogp_opr_codigo, 
                            ogpm.ogpm_pin_valor, 
                            CASE ogp.ogp_id WHEN 5 THEN $3 ELSE NULL END, 
                            ogpm.ogpm_valor_eppcash
                        FROM tb_operadora_games_produto_modelo ogpm
                        INNER JOIN tb_operadora_games_produto ogp ON ogp.ogp_id = ogpm.ogpm_ogp_id
                        WHERE ogpm.ogpm_id = $4
                    ";

                $params = array(
                    $venda_id,       // $1 vgm_vg_id
                    $qtde,           // $2 vgm_qtde
                    $habboId,        // $3 vgm_game_id quando ogp_id = 5
                    $produtos[$i]    // $4 filtro do WHERE
                );

                $ret = SQLexecuteQueryParams($sql, $params);

                if (!$ret) {

                    //Se deu erro ao inserir um modelo, deleta toda a venda
                    // Deleta modelos relacionados à venda
                    $sql = "DELETE FROM tb_venda_games_modelo WHERE vgm_vg_id = $1";
                    SQLexecuteQueryParams($sql, array($venda_id));

                    // Deleta a venda
                    $sql = "DELETE FROM tb_venda_games WHERE vg_id = $1";
                    SQLexecuteQueryParams($sql, array($venda_id));

                    // Deleta histórico relacionado à venda
                    $sql = "DELETE FROM tb_venda_games_historico WHERE vgh_vg_id = $1";
                    SQLexecuteQueryParams($sql, array($venda_id));


                    $msg = "Erro ao inserir modelo(s) na venda.\n";
                    break;
                }
            }
        }
    }

    if (
        $msg == "" && is_array($_SESSION['cpf_boleto_ex']) &&
        !empty($_SESSION['cpf_boleto_ex']['cpf']) &&
        !empty($_SESSION['cpf_boleto_ex']['data_nascimento']) &&
        !empty($_SESSION['cpf_boleto_ex']['nome'])
    ) {
        $sql = 'INSERT INTO tb_venda_games_cpf_boleto_express (
            vgcbe_vg_id, 
            vgcbe_data_inclusao, 
            vgcbe_ex_email, 
            vgcbe_cpf, 
            vgcbe_data_nascimento, 
            vgcbe_nome_cpf
        ) VALUES (
            $1, 
            CURRENT_TIMESTAMP, 
            $2, 
            $3, 
            to_date($4, \'DD/MM/YYYY\'), 
            $5
        )';

        $params = array(
            $venda_id,
            $email,
            $_SESSION['cpf_boleto_ex']['cpf'],
            $_SESSION['cpf_boleto_ex']['data_nascimento'],
            fix_name($_SESSION['cpf_boleto_ex']['nome'])
        );

        $ret = SQLexecuteQueryParams($sql, $params);

        if (!$ret) $msg = "Erro ao inserir dados na tabela tb_venda_games_cpf_boleto_express .\n" . $sql;
    }

    //Log na base
    if ($msg == "") {
        usuarios_games_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['VENDA'], $usuarioId, $venda_id);
    }

    //Boleto
    if ($msg == "") {

        //obtem o valor total da venda
        //----------------------------------------------------
        $sql = "
                SELECT * 
                FROM tb_venda_games vg
                INNER JOIN tb_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id
                WHERE vg.vg_id = $1 AND vg.vg_ug_id = $2
            ";

        $params = array(
            $venda_id,
            $usuarioId
        );

        $rs_venda_modelos = SQLexecuteQueryParams($sql, $params);

        if ($rs_venda_modelos && pg_num_rows($rs_venda_modelos) > 0) {
            $total_geral = 0;
            while ($rs_venda_modelos_row = pg_fetch_array($rs_venda_modelos)) {
                $qtde = $rs_venda_modelos_row['vgm_qtde'];
                $valor = $rs_venda_modelos_row['vgm_valor'];
                $total_geral += $valor * $qtde;
            }
        }

        //Boleto Bradesco
        //Formato do Nosso Numero e Numero do documento
        //----------------------------------------------------
        //3EEEEECCCCC Onde: 
        //3 – identifica MONEY EXPRESS
        //CCCCC – código do cliente MONEY (composto com zeros a esquerda)
        //VVVVV – codigo da venda (composto com zeros a esquerda)
        $num_doc = "3" . "00" . str_pad($venda_id, 8, "0", STR_PAD_LEFT);

        $opr_codigo_money_express = $_SESSION['opr_codigo_money_express'];

        if (b_Is_Boleto_Express_Bradesco()) {
            if ($total_geral < $GLOBALS['RISCO_GAMERS_VALOR_MIN_PARA_TAXA'])
                $taxa_adicional = $GLOBALS['BOLETO_MONEY_BRADESCO_TAXA_ADICIONAL'];
            else
                $taxa_adicional = 0;

            $qtde_dias_venc = $GLOBALS['BOLETO_MONEY_BRADESCO_QTDE_DIAS_VENCIMENTO'];
            $bco_codigo = $GLOBALS['BOLETO_MONEY_BRADESCO_COD_BANCO'];
        } elseif (b_Is_Boleto_Express_Santander()) {
            $qtde_dias_venc = $GLOBALS['BOLETO_MONEY_BANESPA_QTDE_DIAS_VENCIMENTO'];
            $bco_codigo = $GLOBALS['BOLETO_MONEY_BANCO_BANESPA_COD_BANCO'];

            if ($total_geral < $GLOBALS['RISCO_GAMERS_VALOR_MIN_PARA_TAXA'])
                $taxa_adicional = $GLOBALS['BOLETO_MONEY_BANESPA_TAXA_ADICIONAL'];
            else
                $taxa_adicional = 0;

            $num_doc = "3" . "000" . str_pad($venda_id, 8, "0", STR_PAD_LEFT);
        } elseif (b_Is_Boleto_Express_Itau($email, $opr_codigo_money_express)) {
            if ($total_geral < $GLOBALS['RISCO_GAMERS_VALOR_MIN_PARA_TAXA'])
                $taxa_adicional = $GLOBALS['BOLETO_MONEY_ITAU_TAXA_ADICIONAL'];
            else
                $taxa_adicional = 0;

            $qtde_dias_venc = $GLOBALS['BOLETO_MONEY_ITAU_QTDE_DIAS_VENCIMENTO'];
            $bco_codigo = $GLOBALS['BOLETO_MONEY_ITAU_COD_BANCO'];
        } else {
            die("Problema ao selecionar Boleto. Por favor, entre em contato com o suporte e informe o ERRO 87645. Obrigado.");
        }

        // Testa novo prazo de vencimento do boleto
        if (b_IsLogin_boleto_novo_prazo_vencimento($email)) {
            $qtde_dias_venc = get_dias_uteis_para_vencimento_boleto($cod_banco, strtotime(date('Y/m/d')));
        }

        //Insere boleto na base
        //----------------------------------------------------
        $sql = "
                INSERT INTO boleto_bancario_games (
                    bbg_ug_id, 
                    bbg_vg_id, 
                    bbg_data_inclusao, 
                    bbg_valor, 
                    bbg_valor_taxa, 
                    bbg_bco_codigo, 
                    bbg_documento, 
                    bbg_data_venc
                ) VALUES (
                    $1, $2, CURRENT_TIMESTAMP, $3, $4, $5, $6, CURRENT_DATE + interval '$7 day'
                )
            ";

        $params = array(
            $usuarioId,          // $1
            $venda_id,           // $2
            $total_geral + $taxa_adicional, // $3
            $taxa_adicional,     // $4
            $bco_codigo,         // $5
            $num_doc,            // $6
            $qtde_dias_venc      // $7, usado na interpolação do interval
        );

        $ret = SQLexecuteQueryParams($sql, $params);


        //atualiza dados do pagamento e status da venda
        if ($ret) {
            $sql = "
                        UPDATE tb_venda_games SET
                            vg_pagto_data_inclusao = CURRENT_TIMESTAMP,
                            vg_pagto_banco = $1,
                            vg_pagto_num_docto = $2,
                            vg_ultimo_status = $3
                        WHERE vg_id = $4
                    ";

            $params = array(
                $bco_codigo,                                  // $1
                $num_doc,                                     // $2
                $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO'], // $3
                $venda_id                                     // $4
            );

            $ret = SQLexecuteQueryParams($sql, $params);

            if (!$ret)
                $msg = "Erro ao atualizar status da venda.\n";
        }
    }

    //Finaliza transacao
    if ($msg == "") {
        $sql = "COMMIT TRANSACTION ";
        $ret = SQLexecuteQuery($sql);
        //if(!$ret) $msg = "Erro ao comitar transação.\n";
    } else {
        $sql = "ROLLBACK TRANSACTION ";
        $ret = SQLexecuteQuery($sql);
        //if(!$ret) $msg = "Erro ao dar rollback na transação.\n";
    }

    //token
    if ($msg == "") {
        //$token = date('YmdHis') . "," . $venda_id . "," . $usuarioId;
        $token = date('YmdHis', strtotime("+20 day")) . "," . $venda_id . "," . $usuarioId;
        $objEncryption = new Encryption();
        $token = $objEncryption->encrypt($token);
    }

    //Envia email
    //--------------------------------------------------------------------------------
    if ($msg == "") {
        $sql = "
            SELECT *
            FROM tb_venda_games vg
            INNER JOIN tb_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id
            WHERE vg.vg_id = $1
              AND vg.vg_ug_id = $2
            ORDER BY vgm.vgm_opr_codigo, vgm.vgm_valor
        ";

        $params = array(
            $venda_id,   // $1
            $usuarioId   // $2
        );

        $rs_venda_modelos = SQLexecuteQueryParams($sql, $params);


        $parametros['prepag_dominio'] = "" . EPREPAG_URL_HTTP . "";

        /* ---Wagner - variavel $aux_lista */
        $aux_lista = "<table cellspacing='0' cellpadding='5' width='100%' style='font: normal 13px arial, sans-serif;'>
                        <tr bgcolor='#CCCCCC'>
                                <td width='3'>&nbsp;</td>
                                <td align='left'><b>Jogo</b></td>
                                <td align='center'><b>Produto</b></td>
                                <td align='center'><b>Unit.&nbsp;(R$)</b></td>
                                <td align='center'><b>Qtde</b></td>
                                <td align='right'><b>Total&nbsp;(R$)</b></td>
                                <td width='5'>&nbsp;</td>
                        </tr>";

        $qtde_total = 0;
        $total_geral = 0;
        /* ---Fim Wagner - variavel $aux_lista */
        while ($rs_venda_modelos_row = pg_fetch_array($rs_venda_modelos)) {

            $pagto_tipo = $rs_venda_modelos_row['vg_pagto_tipo'];
            $vg_ex_email = $rs_venda_modelos_row['vg_ex_email'];
            $codigo     = $rs_venda_modelos_row['vgm_id'];
            $qtde         = $rs_venda_modelos_row['vgm_qtde'];
            $valor         = $rs_venda_modelos_row['vgm_valor'];
            $qtde_total += $qtde;
            $total_geral += $valor * $qtde;

            /* ---Wagner - variavel $aux_lista */
            $aux_lista .= "<tr bgcolor='#E6E6E6'>
                            <td width='3'>&nbsp;</td>
                            <td align='left'><nobr>" . str_replace(" ", "&nbsp;", $rs_venda_modelos_row['vgm_nome_produto']) . "</nobr></td>
                            <td align='center'>" . $rs_venda_modelos_row['vgm_nome_modelo'] . "</td>
                            <td align='center'>" . number_format($valor, 2, ',', '.') . "</td>
                            <td align='center'>" . $qtde . "</td>
                            <td align='right'><nobr><b>" . number_format($valor * $qtde, 2, ',', '.') . "</b></nobr></td>
                            <td width='5'>&nbsp;</td>
                    </tr>";
            /* ---Fim Wagner - variavel $aux_lista */
        }

        /* ---Wagner - variavel $aux_lista */
        $aux_lista .= "<tr  bgcolor='#CCCCCC'>
                        <td colspan='3'>&nbsp;</td>
                        <td colspan='2' align='center'><b>Total&nbsp;Geral&nbsp;(R$)</b></td>
                        <td align='right'><b>" . number_format($total_geral, 2, ',', '.') . "</b></td>
                        <td width='5'>&nbsp;</td>
                        </tr></table>";
        /* ---Fim Wagner - variavel $aux_lista */

        /* ---Wagner */
        $GLOBALS['_SESSION']['boleto_imagem'] = 'PedidoRegistradoEx';
        $GLOBALS['_SESSION']['EmailTO'] = $vg_ex_email;
        $GLOBALS['_SESSION']['EmailOfertas'] = $aux_lista;
        $GLOBALS['_SESSION']['EmailToken'] = $parametros['prepag_dominio'] . $url_boleto . "?token=" . $token;
    }

    // se usuário não existe como Gamer -> cadastra novo usuário e avisa por email (tamplate "SenhaIntegracao")
    if ($msg == "") {
        // Testa se email vg_ex_email está cadastrado 
        $instUsuarioGames = new UsuarioGames;
        $idcliente = $instUsuarioGames->existeEmail_get_ID($email);
        if ($idcliente == 0) {
            gravaLog_CadastraUsuariosExpressMoney("Cadastra Boleto - email: '$email', idcliente: $idcliente \n    " . (($idcliente == 0) ? "CADASTRA NOVO USUÁRIO '$email'" : "Não cadastra novo usuário") . "");

            // Registra novo usuário com o email
            $id_novo = UsuarioGames::inserir_simple("", $email);

            // Envia email com instruções
            if ($id_novo > 0) {
                $promo_msg = "";
                $objEnvioEmailAutomatico = new EnvioEmailAutomatico(TIPO_USUARIO_GAMER, 'SenhaExMoney');
                $objEnvioEmailAutomatico->setPromocoes($promo_msg);
                $objEnvioEmailAutomatico->setUgID($id_novo);
                echo $objEnvioEmailAutomatico->MontaEmailEspecifico();
            }
        }
    }

    //Retorno
    if ($msg != "") {
        $msg = "<script>$('#geraBoleto').html(''); manipulaModal(1,'" . str_replace("\n", "\\n", $msg) . "','Erro');</script>";
        echo $msg;
        exit;
    } else {
        $msg .= "<p class='text-center'><strong>Pedido efetuado.</strong></p>";
        $msg .= "<p class='text-center'><strong>Número do pedido: " . str_pad($venda_id, 8, "0", STR_PAD_LEFT) . "</strong></p>";
        $msg .= "<p class='text-center'><strong><a href='" . $url_boleto . "?token=$token' target='_blank' class='btn btn-success'>Visualizar boleto bancário</a></strong></p>";
        unset($_SESSION['carrinho']);
        unset($_SESSION['cpf_boleto_ex']);
        // Este script funciona chamdo por AJAX desde modelosEx.php
        echo utf8_encode($msg);
        exit;
    }
} else {
    require_once DIR_CLASS . 'gamer/controller/HeaderController.class.php';
    $controller = new HeaderController();
    $controller->setHeader();
    echo "<script src='/js/valida.js'></script><script>manipulaModal(1,'Chamada não permitida.','Erro'); $('#modal-load').on('hidden.bs.modal', function () { location.href='/' });</script>";
    require_once DIR_WEB . 'game/includes/footer.php';
}
