<?php require_once __DIR__ . '/../constantes_url.php'; ?>
<?php
// Functions processaExpressMoneyLH() e processaEmailExpressMoneyLH(), 
//	similares àquelas em \bkov2_prepag\dist_commerce\includes\functions_vendaGames.php"
// Mas que permite fazer include() em \prepag2\dist_commerce\includes\functions_vendaGames.php"
function processaExpressMoneyLH_pag_online($venda_id, $usuario_id, $parametros){

    $blDebugMT = false;
    $blShowProgress = false;
    if($parametros['showProgress']) $blShowProgress = true;
    $blShowProgress = false;

    //set_time_limit(0);
    //ob_end_flush();

    if($blShowProgress) echo "</td></tr></table>";

    $msg = "";
    if($blDebugMT) echo "Ponto B1;" . getmicrotime() . PHP_EOL;
    //Recupera a venda
    if($msg == ""){
            $sql  = "select * from tb_dist_venda_games vg where vg.vg_id = " . $venda_id;
            $rs_venda = SQLexecuteQuery($sql);
            if(!$rs_venda || pg_num_rows($rs_venda) == 0) $msg = "Nenhuma venda encontrada.".PHP_EOL;
            else {
                    $rs_venda_row = pg_fetch_array($rs_venda);
                    $vg_ug_id 			= $rs_venda_row['vg_ug_id'];
                    $vg_ultimo_status 	= $rs_venda_row['vg_ultimo_status'];
                    $vg_pagto_tipo 		= $rs_venda_row['vg_pagto_tipo'];
                    //valida status
                    if($vg_ultimo_status != $GLOBALS['STATUS_VENDA']['PEDIDO_EFETUADO'] && $vg_ultimo_status != $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO'] && $vg_ultimo_status != $GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO'] ) $msg = "Venda não esta no seu status inicial.".PHP_EOL;
            }
    }
    if($blDebugMT) echo "Ponto B4;" . getmicrotime() . PHP_EOL;
    //Recupera dados do usuario
    if($msg == ""){
            $sql  = "select * from dist_usuarios_games ug where ug.ug_id = " . $vg_ug_id;
            $rs_usuario = SQLexecuteQuery($sql);
            if(!$rs_usuario || pg_num_rows($rs_usuario) == 0) $msg = "Nenhum cliente encontrado.".PHP_EOL;
            else {
                    $rs_usuario_row = pg_fetch_array($rs_usuario);
                    $ug_cel_ddd = $rs_usuario_row['ug_cel_ddd'];
                    $ug_cel 	= $rs_usuario_row['ug_cel'];
                    if(!is_numeric($ug_cel_ddd)) $ug_cel_ddd = null;
                    if(!is_numeric(str_replace("-", "", $ug_cel))) $ug_cel = null;
            }
    }
    if($blDebugMT) echo "Ponto B5;" . getmicrotime() . PHP_EOL;

    //Enquanto nao tem deposito e boleto
    $vg_pagto_banco 	= $rs_venda_row['vg_pagto_banco'];
    $vg_pagto_num_docto = $rs_venda_row['vg_pagto_num_docto'];
    $ped_cod_doc_equiv = "";
    $ped_dep_codigo = null;
    $ped_bol_codigo = null;


    $data_corrente = date ("Y/m/d");
    $hora_corrente = date ("H:i:s");

    //Inicia transacao
    if($msg == ""){
                    $sql = "BEGIN TRANSACTION ";
                    $ret = SQLexecuteQuery($sql);
                    if(!$ret) $msg = "Erro ao iniciar transação.".PHP_EOL;
    }
    if($blDebugMT) echo "Ponto B6;" . getmicrotime() . PHP_EOL;

    //VENDA GAMES
    //---------------------------------------------------------------------------------------------------
    //atualiza status
    if($msg == ""){
			$sql = "update tb_dist_venda_games set 
						vg_ultimo_status_obs = " . SQLaddFields($parametros['ultimo_status_obs'], "s") . ",
						vg_ultimo_status = " . SQLaddFields($GLOBALS['STATUS_VENDA']['PROCESSAMENTO_REALIZADO'], "") . "
					where vg_id = " . $venda_id;
			$ret = SQLexecuteQuery($sql);
			if(!$ret) $msg = "Erro ao conciliar venda.".PHP_EOL;
    }
    if($blDebugMT) echo "Ponto B20;" . getmicrotime() . PHP_EOL;

    //Finaliza transacao
    if($msg == ""){
                    $sql = "COMMIT TRANSACTION ";
                    $ret = SQLexecuteQuery($sql);
                    if(!$ret) $msg = "Erro ao comitar transação.".PHP_EOL;
    } else {
                    $sql = "ROLLBACK TRANSACTION ";
                    $ret = SQLexecuteQuery($sql);
                    if(!$ret) $msg = "Erro ao dar rollback na transação.".PHP_EOL;
    }
    if($blDebugMT) echo "Ponto B21;" . getmicrotime() . PHP_EOL;

    return $msg;
}


function processaEmailExpressMoneyLH_pag_online($venda_id, $parametros){
    $blDebugMT = false;

    $msg = "";
    if($blDebugMT) echo "Ponto C1;" . getmicrotime() . PHP_EOL;

    //Recupera a venda
    if($msg == ""){
            $sql  = "select * from tb_dist_venda_games vg " .
                            "where vg.vg_id = " . $venda_id;
            $rs_venda = SQLexecuteQuery($sql);
            if(!$rs_venda || pg_num_rows($rs_venda) == 0) $msg = "Nenhuma venda encontrada.".PHP_EOL;
            else {
                    $rs_venda_row = pg_fetch_array($rs_venda);
                    $vg_ug_id = $rs_venda_row['vg_ug_id'];
                    $vg_ultimo_status = $rs_venda_row['vg_ultimo_status'];

                    //valida status
                    if($vg_ultimo_status != $GLOBALS['STATUS_VENDA']['PROCESSAMENTO_REALIZADO'] && $vg_ultimo_status != $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'] && $vg_ultimo_status != $GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO'] ) $msg = "Processamento ainda não realizado.".PHP_EOL;
            }
    }
    if($blDebugMT) echo "Ponto C2;" . getmicrotime() . PHP_EOL;
		
    //Recupera dados do usuario
    if($msg == ""){
            $sql  = "select * from dist_usuarios_games ug " .
                            "where ug.ug_id = " . $vg_ug_id;
            $rs_usuario = SQLexecuteQuery($sql);
            if(!$rs_usuario || pg_num_rows($rs_usuario) == 0) $msg = "Nenhum cliente encontrado.".PHP_EOL;
            else {
                    $rs_usuario_row = pg_fetch_array($rs_usuario);
                    $ug_email = $rs_usuario_row['ug_email'];
                    $ug_tipo_cadastro = $rs_usuario_row['ug_tipo_cadastro'];
                    $ug_sexo = $rs_usuario_row['ug_sexo'];
                    $ug_nome = $rs_usuario_row['ug_nome'];
                    $ug_cpf = $rs_usuario_row['ug_cpf'];
                    $ug_rg = $rs_usuario_row['ug_rg'];
                    $ug_nome_fantasia = $rs_usuario_row['ug_nome_fantasia'];
                    $ug_cnpj = $rs_usuario_row['ug_cnpj'];
                    $ug_endereco = $rs_usuario_row['ug_endereco'];
                    $ug_numero = $rs_usuario_row['ug_numero'];
                    $ug_complemento = $rs_usuario_row['ug_complemento'];
                    $ug_bairro = $rs_usuario_row['ug_bairro'];
                    $ug_cidade = $rs_usuario_row['ug_cidade'];
                    $ug_estado = $rs_usuario_row['ug_estado'];
                    $ug_cep = $rs_usuario_row['ug_cep'];
            }
    }
    if($blDebugMT) echo "Ponto C4;" . getmicrotime() . PHP_EOL;

    //USUARIO
    //---------------------------------------------------------------------------------------------------
    //envia email
    if($msg == ""){

            $parametros['prepag_dominio'] = "" . EPREPAG_URL_HTTP . "";
            $parametros['nome_fantasia'] = $ug_nome_fantasia;
            $parametros['tipo_cadastro'] = $ug_tipo_cadastro;
            $parametros['sexo'] = $ug_sexo;
            $parametros['nome'] = $ug_nome;
            $msgEmail = email_cabecalho($parametros);

            //Dados do comprador
            $msgEmail .= "	<br>
                                            <table border='0' cellspacing='0' width='90%'>
                                            <tr>
                                                    <td class='texto' colspan='2'><b>DADOS DE CADASTRO</b></td>
                                            </tr>
                                            <tr>
                                                    <td class='texto'> " .
                                                            ($ug_tipo_cadastro == 'PF'?($ug_nome . "<br>CPF: " . $ug_cpf . "<br>RG: " . $ug_rg . "<br>"):($ug_nome_fantasia . "<br>CNPJ: " . $ug_cnpj . "<br>")) . "	
                                                            " . $ug_endereco . (trim($ug_complemento) == ""?"":" - " .$ug_complemento) . "<br>
                                                            " . $ug_bairro . ", " . $ug_cidade . " - " . $ug_estado . "<br>
                                                            " . $ug_cep . "<br>
                                                    </td>
                                            </tr>
                                            </table>";

            //Mensagem
            $msgEmail .= "	<br>
                                            <table border='0' cellspacing='0' width='90%'>
                                            <tr>
                                                    <td class='texto'> 
                                                            Seu pagamento de créditos de número <b>" . formata_codigo_venda($venda_id) . "</b> foi processado com sucesso!<br>
                                                            No seu PDV com a E-Prepag foi creditado o valor de <b>R$".number_format($parametros['valor'],2,',','.')."</b> que já pode ser usado para comprar os produtos disponíveis. 
                                                    </td>
                                            </tr>
                                            </table>";
            if($blDebugMT) echo "Ponto C5;" . getmicrotime() . PHP_EOL;

            $msgEmail .= "	<br>";

            $msgEmail .= email_rodape($parametros);
            if($blDebugMT) echo "Ponto C6;" . getmicrotime() . PHP_EOL;

            $subjectEmail = "E-Prepag - Pagamento online Processado";
            if($vg_ultimo_status == $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']) $subjectEmail .= " (Reenvio)";
            enviaEmail($ug_email, null, null, $subjectEmail, $msgEmail);
            if($blDebugMT) echo "Ponto C7;" . getmicrotime() . PHP_EOL;
    }

    //VENDA GAMES
    //---------------------------------------------------------------------------------------------------
    //atualiza status
    if($msg == ""){
            $sql = "update tb_dist_venda_games set 
                                    vg_ultimo_status_obs = " . SQLaddFields($parametros['ultimo_status_obs'], "s") . ",
                                    vg_ultimo_status = " . SQLaddFields($GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'], "") . ",
                                    vg_concilia = 1, vg_data_concilia = CURRENT_TIMESTAMP
                            where vg_id = " . $venda_id;
            $ret = SQLexecuteQuery($sql);
            if(!$ret) $msg = "Erro ao atualizar venda.".PHP_EOL;
    }
    if($blDebugMT) echo "Ponto C8;" . getmicrotime() . PHP_EOL;

    return $msg;
}
?>