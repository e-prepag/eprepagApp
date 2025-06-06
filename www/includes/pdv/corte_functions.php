<?php require_once __DIR__ . '/../constantes_url.php'; ?>
<?php
function geraRemessaBradesco() {

    $titulo = "Geração Arquivo Remessa Bradesco - " . date('d/m/Y - H:i:s');

    //cabecalho
    $cabecalho = PHP_EOL."------------------------------------------------------------------------".PHP_EOL. $titulo . PHP_EOL;
    $msg = "";

    //Recupera boletos
    if ($msg == "") {
        $sql = "select *, ug.ug_tipo_cadastro, ug.ug_cnpj, ug.ug_rg, ug.ug_cpf 
					 from boleto_bancario_cortes bbc
					 inner join dist_usuarios_games ug on ug.ug_id = bbc.bbc_ug_id
					 where bbc_bco_codigo = 237
						and bbc.bbc_status = " . $GLOBALS['CORTE_BOLETO_STATUS']['ABERTO'];
        $rs_boletos = SQLexecuteQuery($sql);
        if(!$rs_boletos) $msg = "Erro ao obter boletos.".PHP_EOL;
        elseif(pg_num_rows($rs_boletos) == 0) $msg = "Nenhum boleto encontrado.".PHP_EOL;
    }

    //Recupera sequencial
    if ($msg == "") {
        $sql = "select vg_valor from tb_variaveis_globais
					 where vg_nome = 'SEQUENCIAL_ARQUIVO_REMESSA_BRADESCO'";
        $rs_bol_seq = SQLexecuteQuery($sql);
			if(!$rs_bol_seq) $msg = "Erro ao obter sequencial boleto.".PHP_EOL;
        else {
            $rs_bol_seq_row = pg_fetch_array($rs_bol_seq);
            $bol_seq = $rs_bol_seq_row['vg_valor'];
        }
        //ajusta
        if(!$bol_seq || trim($bol_seq) == "") $bol_seq = 0;
        $bol_seq++;
    }

    if ($msg == "") {

        //Nome Arquivo
        $nomeArq = "CB" . date('d') . date('m') . "01.REM"; //Extensao REM para arquivo oficial
        //$nomeArq = "CB" . date('d') . date('m') . "01.TST";	//Extensao TST para arquivo de teste
		
        //Abre arquivo
        $folder = $GLOBALS['raiz_do_projeto'] . "arquivos_gerados/corte/remessaBradesco/";
        if(!$handle = fopen($folder . $nomeArq, 'w+')) $msg = "Não foi possivel criar o arquivo: " . $folder . $nomeArq . PHP_EOL;
    }

    //Inicia transacao
    if ($msg == "") {
        $sql = "BEGIN TRANSACTION ";
        $ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao iniciar transação.".PHP_EOL;
    }

    if ($msg == "") {

        //header
        $header_IdentReg = "0";
        $header_IdentArqRemessa = str_pad("1", 1, "0", STR_PAD_LEFT);
        $header_LiteralRemessa = str_pad("REMESSA", 7, " ", STR_PAD_RIGHT);
        $header_CodServico = str_pad("1", 2, "0", STR_PAD_LEFT);
        $header_LiteralServico = str_pad("COBRANCA", 15, " ", STR_PAD_RIGHT);
        $header_CodEmpresa = str_pad("4039921", 20, "0", STR_PAD_LEFT);
        $header_NomeEmpresa = str_pad(substr("E-PREPAG PAGAMENTOS ELETRONICO", 0, 30), 30, " ", STR_PAD_RIGHT);
        $header_NumeroBradesco = str_pad("237", 3, "0", STR_PAD_LEFT);
        $header_NomeBanco = str_pad("BRADESCO", 15, " ", STR_PAD_RIGHT);
        $header_DataGravacaoArq = str_pad(date('dmy'), 6, "0", STR_PAD_LEFT);
        $header_Branco1 = str_pad("", 8, " ", STR_PAD_RIGHT);
        $header_IdentSistema = str_pad("MX", 2, " ", STR_PAD_RIGHT);
        $header_SeqArq = str_pad($bol_seq, 7, "0", STR_PAD_LEFT);
        $header_Branco2 = str_pad("", 277, " ", STR_PAD_RIGHT);
        $header_SeqReg = str_pad("1", 6, "0", STR_PAD_LEFT);
        $header = $header_IdentReg . $header_IdentArqRemessa . $header_LiteralRemessa . $header_CodServico;
        $header .= $header_LiteralServico . $header_CodEmpresa . $header_NomeEmpresa . $header_NumeroBradesco;
        $header .= $header_NomeBanco . $header_DataGravacaoArq . $header_Branco1 . $header_IdentSistema;
        $header .= $header_SeqArq . $header_Branco2 . $header_SeqReg;
        fwrite($handle, $header . "\r".PHP_EOL);

        //detalhe
        $sequencial = 1;
        while ($rs_boletos_row = pg_fetch_array($rs_boletos)) {

            $detalhe_IdentReg = "1";
            $detalhe_AgDeb = str_pad("", 5, "0", STR_PAD_LEFT);
            $detalhe_AgDebDV = str_pad("", 1, "0", STR_PAD_LEFT);
            $detalhe_RazaoCCDeb = str_pad("", 5, "0", STR_PAD_LEFT);
            $detalhe_CCDeb = str_pad("", 7, "0", STR_PAD_LEFT);
            $detalhe_CCDebDV = str_pad("", 1, "0", STR_PAD_LEFT);
            $detalhe_IdentCed0 = str_pad("0", 1, "0", STR_PAD_LEFT);
            $detalhe_IdentCedCart = str_pad(substr($GLOBALS['BOLETO_CARTEIRA'], 0, 3), 3, "0", STR_PAD_LEFT);
            $detalhe_IdentCedAg = str_pad(substr($GLOBALS['BOLETO_CEDENTE_AGENCIA'], 0, 5), 5, "0", STR_PAD_LEFT);
            $detalhe_IdentCedCC = str_pad(substr($GLOBALS['BOLETO_CEDENTE_CONTA'], 0, 7), 7, "0", STR_PAD_LEFT);
            $detalhe_IdentCedCCDV = str_pad(substr($GLOBALS['BOLETO_CEDENTE_CONTA_DV'], 0, 1), 1, "0", STR_PAD_LEFT);
            $detalhe_ContrPart = str_pad($rs_boletos_row['bbc_boleto_codigo'] . ";" . $rs_boletos_row['bbc_cor_codigo'] . ";" . $rs_boletos_row['bbc_ug_id'], 25, " ", STR_PAD_RIGHT);
            $detalhe_CodBancoDeb = str_pad("", 3, "0", STR_PAD_LEFT);
            $detalhe_Zeros = str_pad("", 5, "0", STR_PAD_LEFT);
            $resto = modulo_11(substr("00" . $GLOBALS['BOLETO_CARTEIRA'], -2) . $rs_boletos_row['bbc_documento'], 7, 1);
            if($resto == 1) $dv = "P";
            elseif($resto == 0) $dv = "0";
            else $dv = 11 - $resto;
            $detalhe_IdentTitulo = str_pad($rs_boletos_row['bbc_documento'] . $dv, 12, " ", STR_PAD_RIGHT);
            $detalhe_Desconto = str_pad("", 10, "0", STR_PAD_LEFT);
            $detalhe_CondEmissao = str_pad("2", 1, "0", STR_PAD_LEFT);
            $detalhe_IdentDebAutom = str_pad("N", 1, " ", STR_PAD_RIGHT);
            $detalhe_IdentOpera = str_pad("", 10, " ", STR_PAD_RIGHT);
            $detalhe_IndicRateio = str_pad("", 1, " ", STR_PAD_RIGHT);
            $detalhe_EndAvDebAutom = str_pad("2", 1, "0", STR_PAD_LEFT);
            $detalhe_Branco1 = str_pad("", 2, " ", STR_PAD_RIGHT);
            $detalhe_IdentOcorr = str_pad("01", 1, "0", STR_PAD_LEFT);
            $detalhe_NroDocum = str_pad(substr($rs_boletos_row['bbc_documento'], -10), 10, " ", STR_PAD_RIGHT);
            $detalhe_DataVencTitulo = str_pad(formata_data($rs_boletos_row['bbc_data_venc'], 2), 6, "0", STR_PAD_LEFT);
            $detalhe_ValorTitulo = str_pad(number_format($rs_boletos_row['bbc_valor'], 2, '', ''), 13, "0", STR_PAD_LEFT);
            $detalhe_BancoCobr = str_pad("237", 3, "0", STR_PAD_LEFT);
            $detalhe_AgDep = str_pad(substr($GLOBALS['BOLETO_CEDENTE_AGENCIA'], 0, 5), 5, "0", STR_PAD_LEFT);
            $detalhe_EspecieTitulo = str_pad("01", 2, "0", STR_PAD_LEFT);
            $detalhe_Identificacao = str_pad("N", 1, " ", STR_PAD_RIGHT);
            $detalhe_DataEmissTitulo = str_pad(formata_data($rs_boletos_row['bbc_data_inclusao'], 2), 6, "0", STR_PAD_LEFT);
            $detalhe_1aInstr = str_pad("06", 2, "0", STR_PAD_LEFT);
            $detalhe_2aInstr = str_pad("14", 2, "0", STR_PAD_LEFT);
            $ValorDiaAtraso = $rs_boletos_row['bbc_valor'] * ($GLOBALS['BOLETO_JUROS_AO_MES_PRCT'] / 30) / 100;
            $detalhe_ValorDiaAtraso = str_pad(number_format($ValorDiaAtraso, 2, "", ""), 13, "0", STR_PAD_LEFT);
            $detalhe_DataLimDesc = str_pad("", 6, "0", STR_PAD_LEFT);
            $detalhe_ValorDesc = str_pad("", 13, "0", STR_PAD_LEFT);
            $detalhe_ValorIOF = str_pad("", 13, "0", STR_PAD_LEFT);
            $detalhe_ValorAbat = str_pad("", 13, "0", STR_PAD_LEFT);
            if ($rs_boletos_row['ug_tipo_cadastro'] == "PF") {
                if ($rs_boletos_row['ug_cpf'] && trim($rs_boletos_row['ug_cpf']) != "") {
                    $detalhe_IdentTipoSac = str_pad("01", 2, "0", STR_PAD_LEFT);
                    $detalhe_InscrSacado = str_pad($rs_boletos_row['ug_cpf'], 14, "0", STR_PAD_LEFT);
                } else {
                    $detalhe_IdentTipoSac = str_pad("99", 2, "0", STR_PAD_LEFT);
                    $detalhe_InscrSacado = str_pad($rs_boletos_row['ug_rg'], 14, "0", STR_PAD_LEFT);
                }
            } else {
                $detalhe_IdentTipoSac = str_pad("02", 2, "0", STR_PAD_LEFT);
                $detalhe_InscrSacado = str_pad($rs_boletos_row['ug_cnpj'], 14, "0", STR_PAD_LEFT);
            }
            $detalhe_NomeSacado = str_pad("", 40, " ", STR_PAD_RIGHT);
            $detalhe_EnderecoSacado = str_pad("", 40, " ", STR_PAD_RIGHT);
            $detalhe_1aMensagem = str_pad("", 12, " ", STR_PAD_RIGHT);
            $detalhe_CEP = str_pad("", 5, "0", STR_PAD_LEFT);
            $detalhe_CEPSulfixo = str_pad("", 3, "0", STR_PAD_LEFT);
            $detalhe_2aMensagem = str_pad("", 60, " ", STR_PAD_RIGHT);
            $detalhe_Sequencial = str_pad($sequencial++, 6, "0", STR_PAD_LEFT);
            $detalhe = $detalhe_IdentReg . $detalhe_AgDeb . $detalhe_AgDebDV . $detalhe_RazaoCCDeb;
            $detalhe .= $detalhe_CCDeb . $detalhe_CCDebDV . $detalhe_IdentCed0 . $detalhe_IdentCedCart;
            $detalhe .= $detalhe_IdentCedAg . $detalhe_IdentCedCC . $detalhe_IdentCedCCDV . $detalhe_ContrPart;
            $detalhe .= $detalhe_CodBancoDeb . $detalhe_Zeros . $detalhe_IdentTitulo . $detalhe_Desconto;
            $detalhe .= $detalhe_CondEmissao . $detalhe_IdentDebAutom . $detalhe_IdentOpera . $detalhe_IndicRateio;
            $detalhe .= $detalhe_EndAvDebAutom . $detalhe_Branco1 . $detalhe_IdentOcorr . $detalhe_NroDocum;
            $detalhe .= $detalhe_DataVencTitulo . $detalhe_ValorTitulo . $detalhe_BancoCobr . $detalhe_AgDep;
            $detalhe .= $detalhe_EspecieTitulo . $detalhe_Identificacao . $detalhe_DataEmissTitulo;
            $detalhe .= $detalhe_1aInstr . $detalhe_2aInstr . $detalhe_ValorDiaAtraso . $detalhe_DataLimDesc;
            $detalhe .= $detalhe_ValorDesc . $detalhe_ValorIOF . $detalhe_ValorAbat . $detalhe_IdentTipoSac;
            $detalhe .= $detalhe_InscrSacado . $detalhe_NomeSacado . $detalhe_EnderecoSacado . $detalhe_1aMensagem;
            $detalhe .= $detalhe_CEP . $detalhe_CEPSulfixo . $detalhe_2aMensagem . $detalhe_Sequencial;
            fwrite($handle, $detalhe . "\r".PHP_EOL);

            //Atualiza boleto
            if ($msg == "") {
                $sql = "update boleto_bancario_cortes set ";
                $sql .= " bbc_status = " . SQLaddFields($GLOBALS['CORTE_BOLETO_STATUS']['ENVIADO'], "s") . ", ";
                $sql .= " bbc_arq_remessa = " . SQLaddFields($nomeArq, "s");
                $sql .= " where bbc_boleto_codigo = " . SQLaddFields($rs_boletos_row['bbc_boleto_codigo'], "");
                $ret = SQLexecuteQuery($sql);
                if (!$ret) {
                    $msg = "Erro ao atualizar boleto.".PHP_EOL;
                    break;
                }
            }

        }//end while

        //trailler
        $trailler_IdentReg = "9";
        $trailler_Branco = str_pad("", 393, " ", STR_PAD_RIGHT);
        $trailler_Sequencial = str_pad( --$sequencial, 6, "0", STR_PAD_LEFT);
        $trailler = $trailler_IdentReg . $trailler_Branco . $trailler_Sequencial;
        fwrite($handle, $trailler . "\r".PHP_EOL);
    }

    //atualiza sequencial
    if ($msg == "") {
        $sql = "delete from tb_variaveis_globais where vg_nome = 'SEQUENCIAL_ARQUIVO_REMESSA_BRADESCO'";
        $ret = SQLexecuteQuery($sql);
        if(!$ret) $msg = "Erro ao excluir sequencial boleto.".PHP_EOL;
        else {
            $sql = "insert into tb_variaveis_globais (vg_nome, vg_valor) values ('SEQUENCIAL_ARQUIVO_REMESSA_BRADESCO','$bol_seq')";
            $ret = SQLexecuteQuery($sql);
            if(!$ret) $msg = "Erro ao inserir sequencial boleto.".PHP_EOL;
        }
    }

    //Finaliza transacao
    if ($msg == "") {
        $sql = "COMMIT TRANSACTION ";
        $ret = SQLexecuteQuery($sql);
        //if(!$ret) $msg = "Erro ao comitar transação.".PHP_EOL;
    } else {
        $sql = "ROLLBACK TRANSACTION ";
        $ret = SQLexecuteQuery($sql);
        //if(!$ret) $msg = "Erro ao dar rollback na transação.".PHP_EOL;
    }


    if ($msg == "") {
        $msg = "Arquivo Remessa Bradesco gerado com sucesso em $nomeArq.".PHP_EOL;
			if($handle) fclose($handle);
        chmod($folder . $nomeArq, 0777);
    } else {
        if ($handle) {
            fclose($handle);
            unlink($folder . $nomeArq);
        }
    }

    return $msg;
}//end function geraRemessaBradesco()


function processaCorte() {

    //header
    $header = PHP_EOL."------------------------------------------------------------------------".PHP_EOL;
    $header .= "Execucao de Corte - " . date('d/m/Y - H:i:s') . PHP_EOL;
    $msg = "";

    //Recupera usuarios
    if ($msg == "") {
        $sql = "select ug.ug_id from dist_usuarios_games ug 
                where ug.ug_ativo = 1 and ug_risco_classif = 1 and ug.ug_perfil_corte_dia_semana = ". date('w'); //. date('w');
        echo "CORTE TMP: $sql".PHP_EOL;
        $rs_estab = SQLexecuteQuery($sql);
        if(!$rs_estab || pg_num_rows($rs_estab) == 0) $msg = "Nenhum usuário encontrado.".PHP_EOL;
    }

    //Executa cortes
    if ($msg == "") {
        while ($rs_estab_row = pg_fetch_array($rs_estab)) {
            $ug_id = $rs_estab_row['ug_id'];

            //Gera corte
            $msgCorte = geraCorte($ug_id);
            if($msgCorte == "") $msgCorteUsuario = "Corte: Corte efetuado com sucesso.".PHP_EOL;
            else $msgCorteUsuario = "Corte: " . $msgCorte;

            $msgOut .= "Usuário " . $ug_id . ":".PHP_EOL;
            $msgOut .= $msgCorteUsuario;

            //atualiza usuario
            if ($msgCorte == "") {
                $sql = "update dist_usuarios_games set 
                                ug_perfil_corte_ultimo_corte = CURRENT_DATE
                        where ug_id = " . $ug_id;
                $ret = SQLexecuteQuery($sql);
					if(!$ret) $msgOut .= "Erro ao atualizar usuário.".PHP_EOL;
            }
        }
    }

    //Gera arquivo remessa Bradesco
    $msgOut .= "Geração Arquivo Remessa Bradesco: " . geraRemessaBradesco();

    $msg = $header . $msg . $msgOut;

    //envia email
    $ret = enviaEmail("comercial@e-prepag.com.br,wagner@e-prepag.com.br", null, null, "Processamento de Corte - " . date('d/m/Y - H:i:s'), str_replace(PHP_EOL, "<br>", $msg));
    if($ret) $msg .= "Email enviado.".PHP_EOL;
    else  $msg .= "Email não foi enviado.".PHP_EOL;

    return $msg;
}//end function processaCorte()

function geraCorte($usuario_id, $periodo_ini = null, $periodo_fim = null, $force = null) {

    set_time_limit(0);
    ob_end_flush();
    $msg = "";

    //Flag se foi definido periodo
    $isPeriodoPersonalizado = ($periodo_ini || $periodo_fim);

    //Valida periodo
    if ($msg == "") {
        if ($isPeriodoPersonalizado) {
            if(!is_DateTimeEx($periodo_ini . " 00:00", 1)) $msg = "Data de inicio do período é inválida.".PHP_EOL;
            if(!is_DateTimeEx($periodo_fim . " 00:00", 1)) $msg = "Data final do período é inválida.".PHP_EOL;

            //Converte de dd/mm/yyyy para yyyy-mm-dd
            if ($msg == "") {
                $periodo_ini = substr($periodo_ini, 6, 4) . "-" . substr($periodo_ini, 3, 2) . "-" . substr($periodo_ini, 0, 2);
                $periodo_fim = substr($periodo_fim, 6, 4) . "-" . substr($periodo_fim, 3, 2) . "-" . substr($periodo_fim, 0, 2);
            }
        }
    }

    //Valida usuario
    if ($msg == "") {
        if(!$usuario_id || trim($usuario_id) == "" || !is_numeric($usuario_id)) $msg = "Código do usuário é inválido.".PHP_EOL;
    }

    //Obtem usuario
    if ($msg == "") {
        $sql = "select * from dist_usuarios_games ug where ug.ug_id = " . $usuario_id;
        $rs_estab = SQLexecuteQuery($sql);
        if(!$rs_estab || pg_num_rows($rs_estab) == 0) $msg = "Nenhum usuário encontrado.".PHP_EOL;
        else {
            $rs_estab_row = pg_fetch_array($rs_estab);
            $ug_perfil_corte_dia_semana = $rs_estab_row['ug_perfil_corte_dia_semana'];
            $ug_perfil_corte_ultimo_corte = $rs_estab_row['ug_perfil_corte_ultimo_corte'];
            //$est_corte_tipo_pagto 		= $rs_estab_row['est_corte_tipo_pagto'];
            $ug_tipo_cadastro = trim($rs_estab_row['ug_tipo_cadastro']);
            $ug_nome_fantasia = trim($rs_estab_row['ug_nome_fantasia']);
            $ug_nome = trim($rs_estab_row['ug_nome']);
            $ug_sexo = trim($rs_estab_row['ug_sexo']);
            $ug_email = trim($rs_estab_row['ug_email']);
            $ug_credito_pendente = $rs_estab_row['ug_credito_pendente'];
            $ug_perfil_corte_tipo_pagto = $GLOBALS['CORTE_FORMAS_PAGAMENTO']['BOLETO_BANCARIO'];


            //Defaults
            //-----------------------------------------------------------------------------------------------------
            //corte_dia_semana
            if (is_null($ug_perfil_corte_dia_semana) || trim($ug_perfil_corte_dia_semana) == "" || !is_numeric($ug_perfil_corte_dia_semana))
                $ug_perfil_corte_dia_semana = $GLOBALS['CORTE_DIAS_DA_SEMANA']['SEGUNDA'];

            //ultimo_corte
            if (!$ug_perfil_corte_ultimo_corte || trim($ug_perfil_corte_ultimo_corte) == "")
                $ug_perfil_corte_ultimo_corte = date('Y-m-d', strtotime("-7 day"));

            //validacoes -  Se nao foi passado periodo
            //-----------------------------------------------------------------------------------------------------
            //Validar se data de corte eh menor que data atual
            if(!dateDiff('d', $ug_perfil_corte_ultimo_corte, date('Y-m-d')) > 0) $msg = "Corte já efetuado anteriormente.".PHP_EOL;

			
            if (!$isPeriodoPersonalizado) {

			    $force = true;
                //Validar dia de corte
                if (!$force) {
                    if($ug_perfil_corte_dia_semana != date('w')) $msg = "Hoje não é o dia de corte do usuário.".PHP_EOL;
                }
            }
        }
    }

    //Obtem periodo
    if ($msg == "") {

        //Se nao foi passado, obtem o periodo
        if (!$isPeriodoPersonalizado) {

            $periodo_ini = date('Y-m-d', strtotime("-7 day"));
            $periodo_fim = date('Y-m-d', strtotime("-1 day"));

            //Pega o que for mais recente entre periodo_ini e est_corte_ultimo_corte
            $ug_perfil_corte_ultimo_corte = date('Y-m-d', strtotime($ug_perfil_corte_ultimo_corte));
            if(dateDiff('d', $periodo_ini, $ug_perfil_corte_ultimo_corte) > 0 ) $periodo_ini = $ug_perfil_corte_ultimo_corte;
        }

        //validacoes
        if ($msg == "") {
            if(!$periodo_ini || trim($periodo_ini) == "") $msg = "Data de inicio do período é inválida.".PHP_EOL;
            if(!$periodo_fim || trim($periodo_fim) == "") $msg = "Data final do período é inválida.".PHP_EOL;
        }
    }


    //verifica se ja existe corte no periodo
    if ($msg == "") {
        $sql = "select * from cortes
                where cor_ug_id = $usuario_id
                and (
                        (cor_periodo_ini between '$periodo_ini' and '$periodo_fim' or cor_periodo_fim between '$periodo_ini' and '$periodo_fim') or
                        (cor_periodo_ini <= '$periodo_fim' and '$periodo_fim' between cor_periodo_ini and cor_periodo_fim) or
                        (cor_periodo_fim >= '$periodo_ini' and '$periodo_ini' between cor_periodo_ini and cor_periodo_fim)
                    )";
        $rs_cortes = SQLexecuteQuery($sql);
        if(!$rs_cortes) $msg = "Erro ao obter cortes já existentes.".PHP_EOL;
        elseif(pg_num_rows($rs_cortes) != 0)  $msg = "Já existe corte no intervalo do período.".PHP_EOL;
    }

    //Inicia transacao
    if ($msg == "") {
        $sql = "BEGIN TRANSACTION ";
        $ret = SQLexecuteQuery($sql);
        if(!$ret) $msg = "Erro ao iniciar transação.".PHP_EOL;
    }

    //cria corte
    if ($msg == "") {

        $sql = "insert into cortes(cor_status, cor_ug_id, cor_periodo_ini, cor_periodo_fim, cor_tipo_pagto) values (";
        $sql .= SQLaddFields($GLOBALS['CORTE_STATUS']['ABERTO'], "") . ",";
        $sql .= SQLaddFields($usuario_id, "") . ",";
        $sql .= SQLaddFields($periodo_ini, "s") . ",";
        $sql .= SQLaddFields($periodo_fim, "s") . ",";
        $sql .= SQLaddFields($ug_perfil_corte_tipo_pagto, "") . ")";
        $ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao criar corte.".PHP_EOL;
        else {
            $rs_id = SQLexecuteQuery("select currval('cortes_cor_codigo_seq') as last_id");
				if(!$rs_id || pg_num_rows($rs_id) != 1) $msg = "Erro ao obter ID do corte.".PHP_EOL;
            else {
                $rs_id_row = pg_fetch_array($rs_id);
                $cor_codigo = $rs_id_row['last_id'];
            }
        }

        //validacoes
        if ($msg == "") {
				if(!$cor_codigo || trim($cor_codigo) == "" || !is_numeric($cor_codigo)) $msg = "Código de corte criado é inválido.".PHP_EOL;
        }
    }

    //obtem e insere vendas
    if ($msg == "") {
        $sql = "update tb_dist_venda_games
                set vg_cor_codigo = $cor_codigo
                where vg_id in (
                        select vg.vg_id
                        from tb_dist_venda_games vg 
                        where vg.vg_cor_codigo is null and vg.vg_ug_id = $usuario_id
                                and (vg.vg_data_inclusao >= '" . $periodo_ini . " 00:00:00' and vg.vg_data_inclusao <= '" . $periodo_fim . " 23:59:59')
                                and (vg_ultimo_status = " . $GLOBALS['STATUS_VENDA']['PROCESSAMENTO_REALIZADO'] . " or vg_ultimo_status = " . $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'] . "))";
        $ret = SQLexecuteQuery($sql);
        if(!$ret) $msg = "Erro ao obter e inserir vendas.".PHP_EOL;
    }

    //Obtem valor total do corte
    if ($msg == "") {
        $sql = "select count(*) as venda_qtde, 
                        sum(vgm.vgm_valor * vgm.vgm_qtde) as venda_bruta, 
                        sum(vgm.vgm_valor * vgm.vgm_qtde * vgm_perc_desconto / 100) as venda_comissao,
                        sum(vgm.vgm_valor * vgm.vgm_qtde - vgm.vgm_valor * vgm.vgm_qtde * vgm_perc_desconto / 100) as venda_liquida
                from tb_dist_venda_games vg 
                inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                where vgm.vgm_opr_codigo <> 78 and vg.vg_cor_codigo = $cor_codigo";
        $rs_cv = SQLexecuteQuery($sql);
        if(!$rs_cv || pg_num_rows($rs_cv) == 0) $msg = "Erro ao obter valor total do corte.".PHP_EOL;
        else {
            $rs_cv_row = pg_fetch_array($rs_cv);
            $venda_qtde = $rs_cv_row['venda_qtde'];
            $venda_bruta = $rs_cv_row['venda_bruta'];
            $venda_comissao = $rs_cv_row['venda_comissao'];
            $venda_liquida = $rs_cv_row['venda_liquida'];

            if(!$venda_qtde) 	$venda_qtde = 0;
            if(!$venda_bruta) 	$venda_bruta = 0;
            if(!$venda_comissao)$venda_comissao = 0;
            if(!$venda_liquida) $venda_liquida = 0;
        }
    }

    //Atualiza valor total do corte
    if ($msg == "") {
        $sql = "update cortes set ";
 			if(!$venda_liquida || $venda_liquida == 0) $sql .= " cor_status = " . SQLaddFields($GLOBALS['CORTE_STATUS']['CONCILIADO'], "") . ",";
        $sql .= " cor_venda_qtde = " . SQLaddFields($venda_qtde, "") . ",";
        $sql .= " cor_venda_bruta = " . SQLaddFields($venda_bruta, "") . ",";
        $sql .= " cor_venda_comissao = " . SQLaddFields($venda_comissao, "") . ",";
        $sql .= " cor_venda_liquida = " . SQLaddFields($venda_liquida, "");
        $sql .= " where cor_codigo = " . SQLaddFields($cor_codigo, "");
        $ret = SQLexecuteQuery($sql);
        if(!$ret) $msg = "Erro ao atualizar corte.".PHP_EOL;
    }

    //atualiza usuario
    if ($msg == "") {
        $sql = "update dist_usuarios_games set 
                ug_perfil_corte_ultimo_corte = CURRENT_DATE
                where ug_id = " . $usuario_id;
        $ret = SQLexecuteQuery($sql);
        if(!$ret) $msg .= "Erro ao atualizar data do último corte do usuário.".PHP_EOL;
    }


    //Credito pendente
    if ($msg == "") {
        //Se houver credito pendente e for menor que a venda liquida, atualiza corte
        if ($ug_credito_pendente > 0 && $ug_credito_pendente < $venda_liquida) {
            $sql = "update cortes set ";
            $sql .= " cor_credito_pendente = " . SQLaddFields($ug_credito_pendente, "");
            $sql .= " where cor_codigo = " . SQLaddFields($cor_codigo, "");
            $ret = SQLexecuteQuery($sql);
				if(!$ret) $msg = "Erro ao atualizar crédito pendente no corte.".PHP_EOL;
            else {
                $sql = "update dist_usuarios_games set ";
                $sql .= " ug_credito_pendente = 0 ";
                $sql .= " where ug_id = " . SQLaddFields($usuario_id, "");
                $ret = SQLexecuteQuery($sql);
					if(!$ret) $msg = "Erro ao atualizar crédito pendente no usuário.".PHP_EOL;
            }

            // ===============================================================
            // Testes para determinar se está cadastrando credito pendente
            $parametros['prepag_dominio'] = $GLOBALS['SITE_URL'];
            $parametros['nome_fantasia'] = $ug_nome_fantasia;
            $parametros['tipo_cadastro'] = $ug_tipo_cadastro;
            $parametros['sexo'] = $ug_sexo;
            $parametros['nome'] = $ug_nome;
            $msgEmail = email_cabecalho($parametros);

            //Mensagem
            $msgEmail .= "	<br>
                                <table border='0' cellspacing='0' width='90%'>
                                <tr>
                                        <td class='texto'>Foi gerado um valor de crédito pendente para <b>" . $ug_nome_fantasia . "</b> (ID: " . $usuario_id . ") de " . number_format($ug_credito_pendente, 2, ',', '.') . "
                                        </td>
                                </tr>
                                </table>";

            $msgEmail .= email_rodape($parametros);

            //envia email
            $subjectEmail = "E-Prepag - Crédito Pendente gerado " . formata_data($periodo_ini, 0) . " a " . formata_data($periodo_fim, 0);
            $ret = enviaEmail($ug_email, null, "comercial@e-prepag.com.br,wagner@e-prepag.com.br,glaucia@e-prepag.com.br", $subjectEmail, $msgEmail);
            // ===============================================================
        }
    }

    //Gera forma de pagamento
    if ($msg == "") {
        //Gera pagamento somente se ha repasse a ser feito
        if ($venda_liquida > 0) {
            //boleto
            if ($ug_perfil_corte_tipo_pagto == $GLOBALS['CORTE_FORMAS_PAGAMENTO']['BOLETO_BANCARIO']) {
                $msgFormaPagto = geraBoleto($cor_codigo);
                if($msgFormaPagto != "") $msg = "Boleto: " . $msgFormaPagto;
            }
        }
    }

    //Envia email
    if ($msg == "") {
        if ($ug_email != "") {

            //Envia Email somente se ha repasse a ser feito
            if ($venda_liquida > 0) {
                $parametros['prepag_dominio'] = $GLOBALS['SITE_URL'];
                $parametros['nome_fantasia'] = $ug_nome_fantasia;
                $parametros['tipo_cadastro'] = $ug_tipo_cadastro;
                $parametros['sexo'] = $ug_sexo;
                $parametros['nome'] = $ug_nome;
                $msgEmail = email_cabecalho($parametros);

                //Mensagem
                $msgEmail .= "	<br>
                                <table border='0' cellspacing='0' width='90%'>
                                <tr>
                                        <td class='texto'>
                                                Informamos que o Boleto para o pagamento das vendas referentes 
                                                ao período " . formata_data($periodo_ini, 0) . " a " . formata_data($periodo_fim, 0) . " está disponível para impressão no E-Prepag LanHouse.<br><br>
                                                Imprima e pague agora mesmo!!!<br>
                                        </td>
                                </tr>
                                <tr>
                                        <td class='texto'>
                                                <blockquote>
                                                1. Acesse o E-Prepag LanHouse através do " . EPREPAG_URL . "<br>
                                                2. Clique em Revendedores e faça Login<br>
                                                3. Na opção “Serviços” escolha 'BOLETOS'<br>
                                                4. Identifique a semana correspondente e clique 'Emitir Boleto'<br>
                                                </blockquote>
                                        </td>
                                </tr>
                                <tr>
                                        <td class='texto'>
                                                <br>
                                                Não interrompa as suas vendas por falta de crédito. Mantenha em dia os seus pagamentos. Bom para você, bom para o seu cliente!
                                        </td>
                                </tr>
                                </table>";

                $msgEmail .= email_rodape($parametros);

                //envia email
                $subjectEmail = "E-Prepag - Boleto disponível para impressão - Semana " . formata_data($periodo_ini, 0) . " a " . formata_data($periodo_fim, 0);
                $ret = enviaEmail($ug_email, null, "comercial@e-prepag.com.br,wagner@e-prepag.com.br", $subjectEmail, $msgEmail);
            }
        }
    }

    //Finaliza transacao
    if ($msg == "") {
        $sql = "COMMIT TRANSACTION ";
        $ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.".PHP_EOL;
    } else {
        $sql = "ROLLBACK TRANSACTION ";
        $ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.".PHP_EOL;
    }

    return $msg;
} //end function geraCorte

	
function geraBoleto($cor_codigo, $force = null) {

    set_time_limit(0);
    ob_end_flush();
    $msg = "";

    //Valida corte
    if ($msg == "") {
        if(!$cor_codigo || trim($cor_codigo) == "" || !is_numeric($cor_codigo)) $msg = "Código do corte é inválido.".PHP_EOL;
    }

    //Obtem corte
    if ($msg == "") {
        $sql = "select * from cortes c where c.cor_codigo = " . $cor_codigo;
        $rs_corte = SQLexecuteQuery($sql);
			if(!$rs_corte || pg_num_rows($rs_corte) == 0) $msg = "Nenhum corte encontrado.".PHP_EOL;
        else {
            $rs_corte_row = pg_fetch_array($rs_corte);
            $cor_status = $rs_corte_row['cor_status'];
            $cor_tipo_pagto = $rs_corte_row['cor_tipo_pagto'];
            $cor_venda_liquida = $rs_corte_row['cor_venda_liquida'];
            $cor_bbc_boleto_codigo = $rs_corte_row['cor_bbc_boleto_codigo'];
            $cor_ug_id = $rs_corte_row['cor_ug_id'];
            $cor_credito_pendente = $rs_corte_row['cor_credito_pendente'];

            //debita credito pendente
            $cor_venda_liquida -= $cor_credito_pendente;

            //Validacoes
            //-----------------------------------------------------------------------------------------------------
            //Corte status
            if($cor_status != $GLOBALS['CORTE_STATUS']['ABERTO']) $msg = "Corte não esta em Aberto, pode estar conciliado ou cancelado.".PHP_EOL;

            //Corte tipo pagto
            if($cor_tipo_pagto != $GLOBALS['CORTE_FORMAS_PAGAMENTO']['BOLETO_BANCARIO']) $msg = "O tipo de pagto do Corte não é boleto.".PHP_EOL;

            //Corte venda liquida
            if(is_null($cor_venda_liquida) || trim($cor_venda_liquida) == "" || !is_numeric($cor_venda_liquida)) $msg = "Valor liquido é inválido.".PHP_EOL;
            elseif($cor_venda_liquida <= 0) $msg = "Valor líquido igual a zero.".PHP_EOL;

            //Corte com boleto gerado
            if (!$force) {
                if(trim($cor_bbc_boleto_codigo) != "") $msg = "Já existe um boleto para este corte.".PHP_EOL;
            }
        }
    }


    if ($msg == "") {

        //Formato do Nosso Numero e Numero do documento
        //----------------------------------------------------
        //1EEEEECCCCC
        //Onde: 
        //1 – identifica CORTE
        //EEEEE – código do usuario (composto com zeros a esquerda)
        //CCCCC – codigo do corte (composto com zeros a esquerda)
        //			$num_doc = "1" . substr("00000" . $cor_ug_id, -5) . substr("00000" . $cor_codigo, -5);
        $num_doc = "1" . "00" . str_pad($cor_codigo, 8, "0", STR_PAD_LEFT);

        if($cor_venda_liquida>=$BOLETO_LIMITE_PARA_TAXA_ADICIONAL_BRADESCO) $Boleto_taxa_adicional = 0;
        else $Boleto_taxa_adicional = $GLOBALS['BOLETO_TAXA_ADICIONAL_BRADESCO'];

        // Obtem linha digitavel
        //----------------------------------------------------			
        $linha_digitavel = obtemLinhaDigitavelBradesco($num_doc, $cor_venda_liquida);


        //Insere boleto na base
        //----------------------------------------------------
        $sql = "insert into boleto_bancario_cortes (
                bbc_data_inclusao, bbc_bco_codigo, bbc_documento, bbc_valor, bbc_valor_taxa,
                bbc_data_venc, bbc_status, bbc_ug_id, bbc_cor_codigo, bbc_linha_digitavel) values (";
        $sql .= SQLaddFields("CURRENT_TIMESTAMP", "") . ",";
        $sql .= SQLaddFields($GLOBALS['BOLETO_COD_BANCO_BRADESCO'], "") . ",";
        $sql .= SQLaddFields($num_doc, "s") . ",";
        $sql .= SQLaddFields($cor_venda_liquida + $Boleto_taxa_adicional, "") . ",";
        $sql .= SQLaddFields($Boleto_taxa_adicional, "") . ",";
        // data de vencimento de boleto de corte - processamento normal
        $sql .= SQLaddFields("CURRENT_DATE + interval '" . $GLOBALS['BOLETO_QTDE_DIAS_VENCIMENTO'] . " day'", "") . ",";
        $sql .= SQLaddFields($GLOBALS['CORTE_BOLETO_STATUS']['ABERTO'], "") . ",";
        $sql .= SQLaddFields($cor_ug_id, "") . ",";
        $sql .= SQLaddFields($cor_codigo, "") . ",";
        $sql .= SQLaddFields($linha_digitavel, "s") . ")";
        $ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao criar boleto.".PHP_EOL;
        else {
            $rs_id = SQLexecuteQuery("select currval('boleto_bancario_cortes_bbc_boleto_codigo_seq') as last_id");
				if(!$rs_id || pg_num_rows($rs_id) != 1) $msg = "Erro ao obter ID do boleto.".PHP_EOL;
            else {
                $rs_id_row = pg_fetch_array($rs_id);
                $bbc_boleto_codigo = $rs_id_row['last_id'];
            }
        }
    }

    //validacoes
    if ($msg == "") {
			if(!$bbc_boleto_codigo || trim($bbc_boleto_codigo) == "" || !is_numeric($bbc_boleto_codigo)) $msg = "Código do boleto criado é inválido.".PHP_EOL;
    }

    //Atualiza codigo do boleto no corte
    if ($msg == "") {
        $sql = "update cortes set ";
        $sql .= " cor_bbc_boleto_codigo = " . SQLaddFields($bbc_boleto_codigo, "");
        $sql .= " where cor_codigo = " . SQLaddFields($cor_codigo, "");
        $ret = SQLexecuteQuery($sql);
        if(!$ret) $msg = "Erro ao atualizar boleto no corte.".PHP_EOL;
    }

    //Finaliza transacao
    if ($msg == "") {
        $sql = "COMMIT TRANSACTION ";
        $ret = SQLexecuteQuery($sql);
				if(!$ret) $msg = "Erro ao comitar transação.".PHP_EOL;
    } else {
        $sql = "ROLLBACK TRANSACTION ";
        $ret = SQLexecuteQuery($sql);
				if(!$ret) $msg = "Erro ao dar rollback na transação.".PHP_EOL;
    }

    return $msg;
}//end function geraBoleto

function obtemLinhaDigitavelBradesco($num_doc, $cor_venda_liquida) {

    // Obtem linha digitavel
    //----------------------------------------------------
    //Data vencimento
    $dias_de_prazo_para_pagamento = $GLOBALS['BOLETO_QTDE_DIAS_VENCIMENTO'];
    $data_venc = date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006"; 
			
    //Valor boleto
    if($cor_venda_liquida>=$BOLETO_LIMITE_PARA_TAXA_ADICIONAL_BRADESCO) $taxa_boleto = 0;
    else $taxa_boleto = $GLOBALS['BOLETO_TAXA_ADICIONAL_BRADESCO'];

    $valor_cobrado = $cor_venda_liquida; // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
    $valor_cobrado = str_replace(",", ".", $valor_cobrado);
    $valor_boleto = number_format($valor_cobrado + $taxa_boleto, 2, ',', '');

    //calcula linha digitavel
    $linha_digitavel = "";
    require_once $GLOBALS['raiz_do_projeto'] . "banco/bradesco/funcoes_bradesco_fixo_corte.php";

    return $linha_digitavel;
		
} //end function obtemLinhaDigitavelBradesco

function processaLimiteSugerido() {

    //header
    $header = PHP_EOL."------------------------------------------------------------------------".PHP_EOL;
    $header .= "Execucao de LimiteSugerido - " . date('d/m/Y - H:i:s') . PHP_EOL;
    $msg = "";

    //Recupera usuarios
    if ($msg == "") {
        $sql = "select ug.ug_id from dist_usuarios_games ug 
					where ug.ug_ativo = 1 and ug.ug_perfil_corte_dia_semana = " . date('w');
        $rs_estab = SQLexecuteQuery($sql);
			if(!$rs_estab || pg_num_rows($rs_estab) == 0) $msg = "Nenhum usuario encontrado.".PHP_EOL;
    }

    //Obtem venda media diaria
    if ($msg == "") {
        $sql = "select vg_valor from tb_variaveis_globais where vg_nome = 'POSPAGO_VENDA_MEDIA_DIARIA_DEFAULT'";
        $rs_global = SQLexecuteQuery($sql);
			if(!$rs_global || pg_num_rows($rs_global) == 0) $venda_media_diaria_default = 0;
        else {
            $rs_global_row = pg_fetch_array($rs_global);
            $venda_media_diaria_default = $rs_global_row['vg_valor'];
        }
    }

    //Executa cortes
    if ($msg == "") {
        while ($rs_estab_row = pg_fetch_array($rs_estab)) {

            $ug_id = $rs_estab_row['ug_id'];

            //Gera corte
            $msgCorte = atualizaLimiteSugerido($ug_id, null, null, $venda_media_diaria_default);
				if($msgCorte == "") $msgCorteUsuario = "LimiteSugerido: LimiteSugerido efetuado com sucesso.".PHP_EOL;
				else $msgCorteUsuario = "LimiteSugerido: " . $msgCorte;

            $msgOut .= PHP_EOL."Usuário " . $ug_id . ":".PHP_EOL;
            $msgOut .= $msgCorteUsuario;
        }
    }

    $msg = $header . $msg . $msgOut;

    //envia email
    $ret = enviaEmail("comercial@e-prepag.com.br,wagner@e-prepag.com.br", null, null, "Limite Sugerido - " . date('d/m/Y - H:i:s'), str_replace(PHP_EOL, "<br>",$msg));		if($ret) $msg .= "Email enviado.".PHP_EOL;
    else  $msg .= "Email não foi enviado.".PHP_EOL;

    return $msg;
} //end function processaLimiteSugerido()

function atualizaLimiteSugerido($usuario_id, $periodo_ini = null, $periodo_fim = null, $venda_media_diaria_default = null) {

    set_time_limit(0);
    ob_end_flush();
    $msg = "";

    //Valida usuario
    if ($msg == "") {
			if(!$usuario_id || trim($usuario_id) == "" || !is_numeric($usuario_id)) $msg = "Código do usuário é inválido.".PHP_EOL;
    }

    //Flag se foi definido periodo
    $isPeriodoPersonalizado = ($periodo_ini || $periodo_fim);

    //Valida periodo
    if ($msg == "") {
        if ($isPeriodoPersonalizado) {
				if(!is_DateTimeEx($periodo_ini . " 00:00", 1)) $msg = "Data de inicio do período é inválida.".PHP_EOL;
				if(!is_DateTimeEx($periodo_fim . " 00:00", 1)) $msg = "Data final do período é inválida.".PHP_EOL;

            //Converte de dd/mm/yyyy para yyyy-mm-dd
            if ($msg == "") {
                $periodo_ini = substr($periodo_ini, 6, 4) . "-" . substr($periodo_ini, 3, 2) . "-" . substr($periodo_ini, 0, 2);
                $periodo_fim = substr($periodo_fim, 6, 4) . "-" . substr($periodo_fim, 3, 2) . "-" . substr($periodo_fim, 0, 2);
            }
        } else {
            $periodo_ini = date('Y-m-d', strtotime("-28 day"));
            $periodo_fim = date('Y-m-d', strtotime("-1 day"));
        }
        if ($msg == "") {
				if(dateDiff('d', $periodo_ini, $periodo_fim) < 0)  $msg = "Data final deve ser maior que Data inicial.".PHP_EOL;
        }
    }

    //Calcula diferenca de dias
    if ($msg == "") {
        $diasDiff = 1 + dateDiff('d', $periodo_ini, $periodo_fim);
    }


    //Obtem venda media diaria
    if ($msg == "") {
        $sql = "select count(*)/$diasDiff as venda_qtde, 
                        sum(vgm.vgm_valor * vgm.vgm_qtde)/$diasDiff as venda_bruta, 
                        sum(vgm.vgm_valor * vgm.vgm_qtde * vgm_perc_desconto / 100)/$diasDiff as venda_comissao,
                        sum(vgm.vgm_valor * vgm.vgm_qtde - vgm.vgm_valor * vgm.vgm_qtde * vgm_perc_desconto / 100)/$diasDiff as venda_liquida
                from tb_dist_venda_games vg 
                inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                where vg.vg_ug_id = $usuario_id
                        and (vg.vg_data_inclusao >= '" . $periodo_ini . " 00:00:00' and vg.vg_data_inclusao <= '" . $periodo_fim . " 23:59:59')
                        and (vg_ultimo_status = " . $GLOBALS['STATUS_VENDA']['PROCESSAMENTO_REALIZADO'] . " or vg_ultimo_status = " . $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'] . ")";
        $rs_vendas = SQLexecuteQuery($sql);
			if(!$rs_vendas) $msg = "Erro ao obter a média diária.".PHP_EOL;
        elseif (pg_num_rows($rs_vendas) != 0) {
            $rs_vendas_row = pg_fetch_array($rs_vendas);
            $venda_media_diaria = $rs_vendas_row['venda_liquida'];
        }
    }

    //Se nao tem venda media diaria, usa venda media diaria default
    if ($msg == "") {
        if (!$venda_media_diaria) {
				if($venda_media_diaria_default) $venda_media_diaria = $venda_media_diaria_default;
            else {
                $sql = "select vg_valor from tb_variaveis_globais where vg_nome = 'POSPAGO_VENDA_MEDIA_DIARIA_DEFAULT'";
                $rs_global = SQLexecuteQuery($sql);
					if(!$rs_global || pg_num_rows($rs_global) == 0) $venda_media_diaria = 0;
                else {
                    $rs_global_row = pg_fetch_array($rs_global);
                    $venda_media_diaria = $rs_global_row['vg_valor'];
                }
            }
        }
    }

    //Recupera limite do usuario
    if ($msg == "") {
        $sql = "select ug_perfil_limite from dist_usuarios_games where ug_id = $usuario_id";
        $rs_estab = SQLexecuteQuery($sql);
			if(!$rs_estab || pg_num_rows($rs_estab) == 0) $msg = "Nenhum usuário encontrado.".PHP_EOL;
        else {
            $rs_estab_row = pg_fetch_array($rs_estab);
            $limite_valor_vendas = $rs_estab_row['ug_perfil_limite'];
				if(!$limite_valor_vendas) $limite_valor_vendas = 0;
        }
    }

    //calcula novo limite sugerido
    if ($msg == "") {
        $limite_valor_vendas_sugerido_novo = $venda_media_diaria * 7;
    }

    //calcula novo limite
    if ($msg == "") {
        $limite_valor_vendas_novo = $limite_valor_vendas;
        //Se o saldo atual for zero, mantem zero, devido a bloqueio de nao pagamento
			if($limite_valor_vendas == 0) $limite_valor_vendas_novo = 0;
    }

    //atualiza usuario
    if ($msg == "") {
        $sql = "update dist_usuarios_games set 
						ug_perfil_limite_sugerido = " . $limite_valor_vendas_sugerido_novo . ",
						ug_perfil_limite = " . $limite_valor_vendas_novo . "
					where ug_id = $usuario_id ".PHP_EOL;
        $ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao atualizar usuário.".PHP_EOL;
    }

    return $msg;
} //end function atualizaLimiteSugerido


function concilia_boleto($cor_codigo, $bbc_boleto_codigo, $parametros) {

    //Validacoes
    $msg = "";

    //Valida cor_codigo
		if(!$cor_codigo) $msg = "Código do corte não fornecido.".PHP_EOL;
		elseif(!is_numeric($cor_codigo)) $msg = "Código do corte inválido.".PHP_EOL;

    //Valida boleto
		if(!$bbc_boleto_codigo) $msg = "Código do boleto não fornecido.".PHP_EOL;
		elseif(!is_numeric($bbc_boleto_codigo)) $msg = "Código do boleto inválido.".PHP_EOL;

    //id operador			
    $iduser_bko = $_SESSION['iduser_bko'];
		if(!$iduser_bko || is_null($iduser_bko)) $iduser_bko = $GLOBALS['PROCESS_AUTOM_IDUSER_BKO'];

    //Recupera corte
    if ($msg == "") {
        $sql = "select * from cortes cor where cor.cor_codigo = " . $cor_codigo;
        $rs_corte = SQLexecuteQuery($sql);
        if(!$rs_corte || pg_num_rows($rs_corte) == 0) $msg = "Nenhum corte encontrado.".PHP_EOL;
        else {
            $rs_corte_row = pg_fetch_array($rs_corte);

            $cor_ug_id = $rs_corte_row['cor_ug_id'];
            $cor_status = $rs_corte_row['cor_status'];
            $cor_tipo_pagto = $rs_corte_row['cor_tipo_pagto'];
            $cor_bbc_boleto_codigo = $rs_corte_row['cor_bbc_boleto_codigo'];
            $cor_credito_pendente = $rs_corte_row['cor_credito_pendente'];

            //valida status
            if($cor_status != $GLOBALS['CORTE_STATUS']['ABERTO']) $msg = "Corte não esta em aberto (a1).".PHP_EOL;

            //valida tipo pagto
            if($cor_tipo_pagto != $GLOBALS['CORTE_FORMAS_PAGAMENTO']['BOLETO_BANCARIO']) $msg = "Forma de pagamento do corte não é por boleto.".PHP_EOL;

            //valida codigo do boleto
            if($cor_bbc_boleto_codigo != $bbc_boleto_codigo) $msg = "Código do boleto fornecido difere do código do boleto associado ao corte.".PHP_EOL;
        }
    }

    //Recupera o boleto
    if ($msg == "") {
        $sql = "select * from boleto_bancario_cortes bbc where bbc.bbc_boleto_codigo = " . $cor_bbc_boleto_codigo;
        $rs_boleto_bancario = SQLexecuteQuery($sql);
        if(!$rs_boleto_bancario || pg_num_rows($rs_boleto_bancario) == 0) $msg = "Nenhum boleto bancário encontrado.".PHP_EOL;
        else {
            $rs_boleto_bancario_row = pg_fetch_array($rs_boleto_bancario);
            $bbc_valor = $rs_boleto_bancario_row['bbc_valor'];
            $bbc_valor_taxa = $rs_boleto_bancario_row['bbc_valor_taxa'];
            $bbc_status = $rs_boleto_bancario_row['bbc_status'];

            $bol_valor_liquido = $bbc_valor - $bbc_valor_taxa;

            //valida status
            if($bbc_status != $GLOBALS['CORTE_BOLETO_STATUS']['CONCILIADO']) $msg = "Boleto não esta conciliado.".PHP_EOL;
        }
    }

    //Inicia transacao
    if ($msg == "") {
        $sql = "BEGIN TRANSACTION ";
        $ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao iniciar transação.".PHP_EOL;
    }

    //Concilia o corte e atualiza status
    if ($msg == "") {
        $sql = "update cortes set 
						cor_status = " . $GLOBALS['CORTE_STATUS']['CONCILIADO'] . ", 
						cor_data_concilia = CURRENT_TIMESTAMP, 
						cor_user_id_concilia = '" . $iduser_bko . "'
					where cor_codigo = " . $cor_codigo;

        $ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao conciliar corte.".PHP_EOL;
    }

    //Concilia na venda_games e atualiza status
    if ($msg == "") {
        $sql = "update tb_dist_venda_games set 
						vg_concilia = 1, vg_data_concilia = CURRENT_TIMESTAMP, vg_user_id_concilia = '" . $iduser_bko . "'
					where vg_cor_codigo = " . $cor_codigo;
        $ret = SQLexecuteQuery($sql);
        if(!$ret) $msg = "Erro ao conciliar venda.".PHP_EOL;
    }

    //Credita valor liquido do boleto no limite do usuario
    if ($msg == "") {
        $sql = "update dist_usuarios_games set 
						ug_perfil_saldo = ug_perfil_saldo + " . ($bol_valor_liquido + $cor_credito_pendente) . "
					where ug_id = " . $cor_ug_id;
        $ret = SQLexecuteQuery($sql);
        if(!$ret) $msg = "Erro ao creditar valor liquido do boleto no limite do usuário.".PHP_EOL;
        else {
            //Funcao para inserir a movimentacao (Extrato) de um usuario -----------------------------------------------
//				insere_EstabelecimentoMovimentacao($cor_ug_id, 'C', 9, 2, $cor_codigo, ($bol_valor_liquido + $cor_credito_pendente), '');
            //------------------------------------------------------------------------------------------------------------------
        }
    }

    //Finaliza transacao
    if ($msg == "") {
        $sql = "COMMIT TRANSACTION ";
        $ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.".PHP_EOL;
    } else {
        $sql = "ROLLBACK TRANSACTION ";
        $ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.".PHP_EOL;
    }

    return $msg;
} //end function concilia_boleto

function concilia_manualmente_temporario($cor_codigo, $dep_id, $parametros) {

    //Validacoes
    $msg = "";

    //Valida cor_codigo
    if(!$cor_codigo) $msg = "Código do corte não fornecido.".PHP_EOL;
    elseif(!is_numeric($cor_codigo)) $msg = "Código do corte inválido.".PHP_EOL;

    //Valida dep_id
    if(!$dep_id) $msg = "Código do depósito não fornecido.".PHP_EOL;
    elseif(!is_numeric($dep_id)) $msg = "Código do depósito inválido.".PHP_EOL;

    //id operador
    $iduser_bko = $_SESSION['iduser_bko'];
    if(!$iduser_bko || is_null($iduser_bko)) $iduser_bko = $GLOBALS['PROCESS_AUTOM_IDUSER_BKO'];

    //Recupera corte
    if ($msg == "") {
        $sql = "select * from cortes cor where cor.cor_codigo = " . $cor_codigo;
        $rs_corte = SQLexecuteQuery($sql);
			if(!$rs_corte || pg_num_rows($rs_corte) == 0) $msg = "Nenhum corte encontrado.".PHP_EOL;
        else {
            $rs_corte_row = pg_fetch_array($rs_corte);

            $cor_ug_id = $rs_corte_row['cor_ug_id'];
            $cor_status = $rs_corte_row['cor_status'];
            $cor_tipo_pagto = $rs_corte_row['cor_tipo_pagto'];
            $cor_bbc_boleto_codigo = $rs_corte_row['cor_bbc_boleto_codigo'];
            $cor_venda_liquida = $rs_corte_row['cor_venda_liquida'];
            $cor_credito_pendente = $rs_corte_row['cor_credito_pendente'];

            //valida status
            if($cor_status != $GLOBALS['CORTE_STATUS']['ABERTO']) $msg = "Corte não esta em aberto (a2).".PHP_EOL;

            //valida tipo pagto
            if($cor_tipo_pagto != $GLOBALS['CORTE_FORMAS_PAGAMENTO']['BOLETO_BANCARIO']) $msg = "Forma de pagamento do corte não é por boleto.".PHP_EOL;
        }
    }

    //Inicia transacao
    if ($msg == "") {
        $sql = "BEGIN TRANSACTION ";
        $ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao iniciar transação.".PHP_EOL;
    }

    //Concilia o corte e atualiza status
    if ($msg == "") {
        $sql = "update cortes set 
						cor_status = " . $GLOBALS['CORTE_STATUS']['CONCILIADO_MANUALMENTE'] . ", 
						cor_data_concilia = CURRENT_TIMESTAMP, 
						cor_user_id_concilia = '" . $iduser_bko . "',
						cor_dep_id = " . $dep_id . "
					where cor_codigo = " . $cor_codigo;
        $ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao conciliar corte.".PHP_EOL;
    }

    //Concilia na venda_games e atualiza status
    if ($msg == "") {
        $sql = "update tb_dist_venda_games set 
						vg_concilia = 1, vg_data_concilia = CURRENT_TIMESTAMP, vg_user_id_concilia = '" . $iduser_bko . "'
					where vg_cor_codigo = " . $cor_codigo;
        $ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao conciliar venda.".PHP_EOL;
    }

    //Credita valor liquido do boleto no limite do usuario
    if ($msg == "") {
        $sql = "update dist_usuarios_games set 
						ug_perfil_saldo = ug_perfil_saldo + " . ($cor_venda_liquida) . "
					where ug_id = " . $cor_ug_id;
        $ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao creditar valor liquido do boleto no limite do usuário.".PHP_EOL;
        else {
            //Funcao para inserir a movimentacao (Extrato) de um estabelecimento -----------------------------------------------
//				insere_EstabelecimentoMovimentacao($cor_ug_id, 'C', 9, 1, $cor_codigo, ($cor_venda_liquida), '');
            //------------------------------------------------------------------------------------------------------------------
        }
    }


    //Atualiza status do boleto para cancelado
    if ($msg == "") {
        if ($cor_bbc_boleto_codigo) {
            $sql = "update boleto_bancario_cortes set 
							bbc_status = " . $GLOBALS['CORTE_BOLETO_STATUS']['CANCELADO'] . ",
							bbc_data_cancelado = CURRENT_TIMESTAMP
						where bbc_boleto_codigo = $cor_bbc_boleto_codigo";
            $ret = SQLexecuteQuery($sql);
				if(!$ret) $msg = "Erro ao atualizar status CANCELADO do boleto.".PHP_EOL;
        }
    }


    //Finaliza transacao
    if ($msg == "") {
        $sql = "COMMIT TRANSACTION ";
        $ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao comitar transação.".PHP_EOL;
    } else {
        $sql = "ROLLBACK TRANSACTION ";
        $ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao dar rollback na transação.".PHP_EOL;
    }

    return $msg;
} //end function concilia_manualmente_temporario

function processaZeraLimiteBoletoVencido() {

    //header
    $header = PHP_EOL."------------------------------------------------------------------------".PHP_EOL;
    $header .= "Execucao de Zera Limite Boleto Vencido - " . date('d/m/Y - H:i:s') . PHP_EOL;
    $msg = "";

    //Busca boletos vencidos
    if ($msg == "") {
        $sql = "select bbc.bbc_boleto_codigo, bbc.bbc_data_inclusao, bbc.bbc_data_venc, bbc.bbc_cor_codigo, bbc.bbc_ug_id, ug.ug_email
                from boleto_bancario_cortes bbc 
                        inner join dist_usuarios_games ug on bbc.bbc_ug_id=ug.ug_id
                where (bbc.bbc_limite_zerado is null or bbc.bbc_limite_zerado = 0)
                        and bbc.bbc_status in (" . $GLOBALS['CORTE_BOLETO_STATUS']['ABERTO'] . ", " . $GLOBALS['CORTE_BOLETO_STATUS']['ENVIADO'] . ")
                        and ug_risco_classif = 1 and (bbc.bbc_data_venc + interval '" . $GLOBALS['PROCESS_AUTOM_ZERA_LIMITE_BOLETO_DIAS_VENCIDO'] . " day') < (CURRENT_DATE + CURRENT_TIME) ";
        $rs_boletos = SQLexecuteQuery($sql);
        if(!$rs_boletos || pg_num_rows($rs_boletos) == 0) $msg = $header."Nenhum boleto encontrado.".PHP_EOL;
    }

    //id operador			
    $iduser_bko = $_SESSION['iduser_bko'];
    if(!$iduser_bko || is_null($iduser_bko)) $iduser_bko = $GLOBALS['PROCESS_AUTOM_IDUSER_BKO'];

    //historico
    $historico = "Limite zerado automaticamente por não pagamento de boleto no prazo.";

    //Zera limites
    if ($msg == "") {
        $email_list = "";
        $email_n = 0;
        while ($rs_boletos_row = pg_fetch_array($rs_boletos)) {
            $bbc_boleto_codigo = $rs_boletos_row['bbc_boleto_codigo'];
            $bbc_data_inclusao = $rs_boletos_row['bbc_data_inclusao'];
            $bbc_data_venc = $rs_boletos_row['bbc_data_venc'];
            $bbc_cor_codigo = $rs_boletos_row['bbc_cor_codigo'];
            $bbc_ug_id = $rs_boletos_row['bbc_ug_id'];
				if(strcmp($email_list,"")!=0) $email_list .= ", ";
            $email_list .= $rs_boletos_row['ug_email'];
            $email_n += 1;

            //Inicia saida
            $msgOut .= PHP_EOL."Boleto $bbc_boleto_codigo: ".PHP_EOL;
            $msgOut .= "Corte: $bbc_cor_codigo ".PHP_EOL;
            $msgOut .= "Usuário: $bbc_ug_id ".PHP_EOL;
            $msgOut .= "Data de Inclusão: " . formata_data_ts($bbc_data_inclusao, 0, false, false) . PHP_EOL;
            $msgOut .= "Data de Vencimento: " . formata_data($bbc_data_venc, 0) . PHP_EOL;

            $msg = "";

            //Inicia transacao
            if ($msg == "") {
                $sql = "BEGIN TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
					if(!$ret) $msg = "Erro ao iniciar transação.".PHP_EOL;
            }

            //atualiza usuario
            if ($msg == "") {
                $sql = "update dist_usuarios_games set 
								ug_perfil_limite = 0
							where ug_id = " . $bbc_ug_id;
                $ret = SQLexecuteQuery($sql);
					if(!$ret) $msg = "Erro ao atualizar usuário.".PHP_EOL;
            }

            //atualiza boleto
            if ($msg == "") {
                $sql = "update boleto_bancario_cortes set 
								bbc_limite_zerado = 1
							where bbc_boleto_codigo = " . $bbc_boleto_codigo;
                $ret = SQLexecuteQuery($sql);
					if(!$ret) $msg = "Erro ao atualizar boleto.".PHP_EOL;
            }

            //Finaliza transacao
            if ($msg == "") {
                $sql = "COMMIT TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
					if(!$ret) $msg = "Erro ao comitar transação.".PHP_EOL;
            } else {
                $sql = "ROLLBACK TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
					if(!$ret) $msg = "Erro ao dar rollback na transação.".PHP_EOL;
            }

            //Finaliza saida
				if($msg == "") $msgOut .= "Resultado: Zerado limite com sucesso.".PHP_EOL;
				else $msgOut .= "Resultado: " . $msg;
        }

        $msg = $header . $msg . $msgOut;

        //envia email para administradores
        $ret = enviaEmail("comercial@e-prepag.com.br,wagner@e-prepag.com.br", null, null, "Zera Limite Boleto Vencido - " . date('d/m/Y - H:i:s'), str_replace(PHP_EOL, "<br>", $msg));
        if($ret) $msg .= "Email enviado.".PHP_EOL;
        else  $msg .= "Email não foi enviado.".PHP_EOL;

        //envia email para Admins
        $msga = "<p>Lista de PDVs com limite zerado por boleto de corte vencido (" . $email_n . " caso" . (($email_n == 1) ? "" : "s") . "): ".PHP_EOL."<br>" . $email_list . PHP_EOL."<br>Fim da lista.</p>";
        $msg2 = "<p>São Paulo,  " . Data_Atual_Por_Extenso() . "</p>
				<p>Prezado PDV,</p>
				<p>Cumprindo nosso compromisso de estabelecer um relacionamento de parceria com  um revendedor importante como você, gostaríamos de comunicá-lo que, até o  momento do envio deste e-mail, não registramos o pagamento do boleto vencido  esta semana. Por este motivo seu limite de créditos em nosso sistema foi  bloqueado. </p>
				<p>Para desbloquear seu crédito e continuar utilizando os benefícios de nossos  serviços, efetue o pagamento do boleto pendente. </p>
				<p><a href='" . EPREPAG_URL_HTTP . "/creditos/login.php'>Acesse já</a> sua conta, clique em \"BOLETO\" e depois em \"Emitir  boleto\". </p>
				<p>O pagamento do boleto pode ser feito em qualquer agência Bradesco, uma vez  que o título  já está vencido. Seu crédito será desbloqueado no dia útil seguinte ao pagamento.</p> 
				<p>Atenciosamente,</p> 
				<p>Equipe E-Prepag </p>";
        $ret = enviaEmail("glaucia@e-prepag.com.br,suporte@e-prepag.com.br", null, null, "Zera Limite Boleto Vencido - " . date('d/m/Y - H:i:s') . " - Lista de Lanhouses", str_replace(PHP_EOL, "<br>", $msga . $msg2));
        if($ret) $msg .= "Email (lista de PDVs com limite zerado por boleto de corte vencido) enviado.".PHP_EOL;
        else  $msg .= "Email (lista de PDVs com limite zerado por boleto de corte vencido) não foi enviado.".PHP_EOL;

        // Manda de fato para as Lans
        $ret = enviaEmail("comercial@e-prepag.com.br", null, $email_list, "Zera Limite Boleto Vencido - " . date('d/m/Y - H:i:s') . " ", $msg2);
        if($ret) $msg .= "Email (limite zerado por boleto de corte vencido) enviado.".PHP_EOL;
        else  $msg .= "Email (limite zerado por boleto de corte vencido) não foi enviado.".PHP_EOL;

    }

    return $msg;
} //end function processaZeraLimiteBoletoVencido()


//######################################################################################################################################	
//										Funcoes Auxiliares
//######################################################################################################################################	

function DateAdd($v, $d = null, $f = "Y-m-d") {
    $d = ($d ? $d : date("Y-m-d"));
    return date($f, strtotime($v . " days", strtotime($d)));
}//end function DateAdd

function dateDiff($interval, $dateTimeBegin, $dateTimeEnd) {
    //Parse about any English textual datetime
    //$dateTimeBegin, $dateTimeEnd

    $dateTimeBegin = strtotime($dateTimeBegin);
    if ($dateTimeBegin === -1) {
        return("..begin date Invalid");
    }

    $dateTimeEnd = strtotime($dateTimeEnd);
    if ($dateTimeEnd === -1) {
        return("..end date Invalid");
    }

    $dif = $dateTimeEnd - $dateTimeBegin;

    switch ($interval) {
        case "s"://seconds
            return($dif);

        case "n"://minutes
            return(floor($dif / 60)); //60s=1m

        case "h"://hours
            return(floor($dif / 3600)); //3600s=1h

        case "d"://days
            return(floor($dif / 86400)); //86400s=1d

        case "ww"://Week
            return(floor($dif / 604800)); //604800s=1week=1semana

        case "m": //similar result "m" dateDiff Microsoft
            $monthBegin = (date("Y", $dateTimeBegin) * 12) + date("n", $dateTimeBegin);
            $monthEnd = (date("Y", $dateTimeEnd) * 12) + date("n", $dateTimeEnd);
            $monthDiff = $monthEnd - $monthBegin;
            return($monthDiff);

        case "yyyy": //similar result "yyyy" dateDiff Microsoft
            return(date("Y", $dateTimeEnd) - date("Y", $dateTimeBegin));

        default:
            return(floor($dif / 86400)); //86400s=1d
    }

}//end function dateDiff
?>
