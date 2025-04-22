<?php
require_once '../../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."backoffice/includes/constantes.php";
require_once $raiz_do_projeto."includes/gamer/constantes.php";

define('STR_SALDO_EXCLUDE', 'S A L D O');
define('STR_TED_EXCLUDE', 'TED');
define('COD_TRANSFERENCIA_ONLINE', 470);
define('COD_TRANSFERENCIA_AGENDADA', 474);
define('COD_DEPOSITO_BLOQUEADO', 911);

define('INDICE_CONTA_BRADESCO_DEP_OFFLINE_ANTIGA', 2);
define('INDICE_CONTA_BRADESCO_DEP_OFFLINE_NOVA', 9);

$BANCO_DEP    = array(
                    '1' => array($BOLETO_MONEY_BRADESCO_COD_BANCO,'Banco Bradesco S.A.','2062-1','1689-6'),
                    '2' => array($BOLETO_MONEY_BRADESCO_COD_BANCO,'Banco Bradesco S.A.','2062-1','4707-4'),
                    '3' => array($BOLETO_MONEY_BRADESCO_COD_BANCO,'Banco Bradesco S.A.','2062-1','20.459-5'),
                    '9' => array($BOLETO_MONEY_BRADESCO_COD_BANCO,'Banco Bradesco S.A.','2062-1','0030265-1'),
                    '4' => array($BOLETO_MONEY_BANCO_DO_BRASIL_COD_BANCO,'Banco do Brasil','4328-1','14.498-3'),
                    '5' => array($BOLETO_MONEY_BANCO_DO_BRASIL_COD_BANCO,'Banco do Brasil','4328-1','2978-5'),
//                    '4' => array($BOLETO_MONEY_BANCO_DO_BRASIL_COD_BANCO,'Banco do Brasil','4055-X','5.811-4'),
                    '6' => array($BOLETO_MONEY_CAIXA_COD_BANCO,'Caixa Econômica Federal','0263','003.2812-1'),
                    '7' => array($BOLETO_MONEY_BANCO_ITAU_COD_BANCO,'Banco Itaú','0444','77567-0'),
                    '8' => array($BOLETO_MONEY_BANCO_ITAU_COD_BANCO, 'Banco Itaú', '0444', '35570-5')
                );

$field = array(
    MOV => array('field' => 'rfcb_valor_extrato'),
    TAR => array('field' => 'rfcb_taxa_extrato'),
);

function getCodigoHistorico($banco, $historico){
    if ($banco == '001') { // banco do brasil
        $v = array(170=>TAR, 617=>MOV);
        return (array_key_exists((int) $historico, $v) ? $v[$historico] : 0);
    } else { // banco itau
        if (strtolower(substr(trim($historico), 0, 3)) == 'mov') {
            return MOV;
        } elseif (strtolower(substr(trim($historico), 0, 3)) == 'tar') {
            return TAR;
        }
    }

    return 0;
}

    $pos_pagina = (isset($seg_auxilar)) ? $seg_auxilar : "";

    if(isset($BtnRegistrar) && $BtnRegistrar)
    {
        require $raiz_do_projeto . "includes/Feriados.php";
        $feriados = new Feriados();
        $FrmEnviar = 1;
        $msg = "";
        
        if(trim($ta_depositos) == "") {
            $FrmEnviar = 0;
            $area_vazio = true;
        }
        
        if(isset($FrmEnviar) && $FrmEnviar == 1) {
            $depositos = explode("\n", $ta_depositos);

            if(!$dd_banco_index || trim($dd_banco_index) == "" || !is_numeric($dd_banco_index)) $msg .= "Um banco deve ser selecionado.\n";

            if($msg == ""){

                $linha = array();

                $dd_banco = $BANCO_DEP[$dd_banco_index][0];
                $dd_agencia = $BANCO_DEP[$dd_banco_index][2];
                $dd_conta = $BANCO_DEP[$dd_banco_index][3];

                //Inicia transacao
                pg_query($connid, "BEGIN TRANSACTION ");
                
                //Banco do Brasil
                if ( $BOLETO_MONEY_BANCO_DO_BRASIL_COD_BANCO == $dd_banco ) { // Banco do Brasil - 001
                        $tipo_pagamento = '9'; // rfcb_tipo_pagamento
                        $linhas = explode(PHP_EOL, $ta_depositos);
                        $inicioEncontrado = false;
                        $fimEncontrado = false;

                        $extratoBB = array();
                        foreach ($linhas as $n_linha => $linha) {
                            if( preg_match('/Dt\. movimento/i', $linha) ){
                                $inicioEncontrado = $n_linha+1;
                                continue;
                            } elseif ( $inicioEncontrado && preg_match('/\-\-\-\-\-\-/i', $linha) ) {
                                $fimEncontrado = $n_linha+1;
                                break;
                            }
                            if ( $inicioEncontrado ) {
                                $extratoBB[] = $linha;
                            }
                        }
                        if ( in_array($extratoBB[1][90], array('C', 'D')) ) {
                            // Continue
                            setlocale(LC_MONETARY, 'pt_BR');
                            foreach ( $extratoBB as $n => $linha ) {
                                if ( $n > 0 ) {
                                    $data_movimento = trim(substr($linha, 3, 10));
    //                                                $data_balancete = trim(substr($linha, 18, 10)); //comentado pois com essa validacao nao importava os campos de taxa_extrato e valor_extrato de acordo com o registro importado
                                    $cod_historico = trim(substr($linha, 30, 3));
                                    $historico = strtoupper(trim(substr($linha, 34, 21)));
                                    $documento = str_replace('.','', trim(substr($linha, 55, 19)));
                                    $valor = str_replace('.', '', trim(substr($linha, 75, 15)));
                                    $valor = str_replace(',', '.', $valor);
                                    $tipo = trim(substr($linha, 90, 1));
                                    $saldo = trim(substr($linha, 93, 15));
                                    $tipo_saldo =    trim(substr($linha, 107, 1));
                                    $data_formatada = formata_data($data_movimento,1);
                                    if ( strtoupper($historico) === STR_SALDO_EXCLUDE || strtoupper($historico) === STR_TED_EXCLUDE || in_array($cod_historico, array(COD_TRANSFERENCIA_ONLINE, COD_TRANSFERENCIA_AGENDADA, COD_DEPOSITO_BLOQUEADO))) continue;

                                    // Verificando dado já existe:
                                    $sqlVerDep = "SELECT * FROM depositos_pendentes
                                            WHERE dep_banco='{$dd_banco}'
                                            AND dep_documento = '{$documento}'
                                            AND upper(dep_cod_documento)='{$historico}'
                                            AND dep_agencia='{$dd_agencia}'
                                            AND dep_data='{$data_formatada}'
                                            AND dep_valor={$valor};";

                                    $rsVerDep = SQLexecuteQuery($sqlVerDep);

                                    if (pg_num_rows($rsVerDep) > 0) {
                                        $msg .= "Erro ao inserir depósito: Valor=\"$valor\" Data=\"$data_movimento\" Cod Doc=\"$cod_historico\" Hist=\"$historico\"  Doc=\"$documento\" - Dado Duplicado\n\n";
                                        //echo "<br>SQL: {$sqlVerDep}<br><br>";
                                    } else {
                                        // Primeiro tratamos sobre o relatorio de conciliacao bancaria
                                        $eprepagCode = getCodigoHistorico($dd_banco, $cod_historico);
                                        $fieldCode = '';
                                        $valueCode = '';
                                        // Caso não seja nem Tarifa ou Movimentação, a função retorna 0
    //                                                    if ( in_array($eprepagCode, array(MOV, TAR)) && !empty($data_balancete) ) {
                                        if ( in_array($eprepagCode, array(MOV, TAR))) {
                                            // Temos que encontrar a data correta do movimento (não data do extrato)
                                            // Banco do Brasil = D+2
                                            list($ano, $mes, $dia) = explode('-', $data_formatada);
                                            $dia = substr($dia, 0, 2);
                                            $data = "$dia/$mes/$ano";
                                            $stimestamp = $feriados->subDiaUtil($data, 2);

                                            $ndate = date('Y-m-d', $stimestamp);
                                            
                                            
                                            // Agora verificamos se essa data existe no relatorio de conciliacao bancaria
                                            // se sim, vamos atualizar os dados
                                            $sqlVerRelFin = "SELECT rfcb_id FROM relfin_conciliacao_bancaria WHERE rfcb_data_registro = '{$ndate}' AND rfcb_tipo_pagamento='{$tipo_pagamento}'";
                                            
                                            $rsVerRelFin = SQLexecuteQuery($sqlVerRelFin);

                                            if ( pg_num_rows($rsVerRelFin) > 0 ) {
                                                // Agora que sabemos que existe a linha, vamos atualiza-la
                                                $dataVerRelFin = pg_fetch_assoc($rsVerRelFin);

                                                $idRelFin = $dataVerRelFin['rfcb_id'];
                                                $fieldCode = ', dep_cod_documento2';
                                                $valueCode = ", '{$eprepagCode}'";

                                                

                                                $sqlUpdateRelFin = "UPDATE relfin_conciliacao_bancaria SET {$field[$eprepagCode]['field']}={$valor} "
                                                    . "WHERE rfcb_data_registro='{$ndate}' AND rfcb_tipo_pagamento='{$tipo_pagamento}'";

                                                pg_query($sqlUpdateRelFin);
                                            }
                                        }

                                        $sql  = "insert into depositos_pendentes (dep_valor, dep_data, dep_banco, dep_agencia, dep_conta, dep_cod_documento, dep_documento {$fieldCode}) ";
                                        $sql .= "values ({$valor}, '{$data_formatada}', '{$dd_banco}', '{$dd_agencia}', '{$dd_conta}', '{$historico}', '{$documento}' {$valueCode}) ";
                                        $result = pg_query($connid, $sql);
                                        if(!$result) {
                                            $msg .= "Erro ao inserir depósito: Valor=\"$valor\" Data=\"$data_movimento\" Cod Doc=\"$cod\" Doc=\"$doc\"\n$sql\n\n";
                                        }
                                    }
                                }
                            }
                        } else {
                            die('Formato incompatível com o sistema.<br />Formato correto é o Extrato Detalhado.');
                        }
                } // Fim Banco do Brasil

                //Caixa Economica Federal
                if($dd_banco == $BOLETO_MONEY_CAIXA_COD_BANCO) { // 104
                    for($t = 0; $t < count($depositos); $t++)
                        {
                            $data=trim(substr($depositos[$t],0,12));
                            $doc=trim(substr($depositos[$t],12,8));
                            $cod=trim(substr($depositos[$t],20,10));
                            $valor_tmp=substr($depositos[$t],30,13);
                            $valor=str_replace(",",".",str_replace(".","",trim(substr($valor_tmp,0,strpos($valor_tmp,'C')))));

                            if(trim($doc)!= "" && !is_numeric($doc)) $msg .= "Documento ($doc) deve ser númerico.\n";
                            if(trim($valor)!= "" && !is_numeric($valor)) $msg .= "Valor ($valor) deve ser númerico.\n";

                            if($msg == ""){
                                if(is_numeric(substr($data,0,2)) && is_numeric(substr($data,3,2)) && trim($cod) && trim($doc) && trim($valor)) {
                                    $sql  = "insert into depositos_pendentes (dep_valor, dep_data, dep_banco, dep_agencia, dep_conta, dep_cod_documento, dep_documento) ";
                                    $sql .= "values ('".$valor."', '".formata_data($data,1)."', '".$dd_banco."', '".$dd_agencia."', '".$dd_conta."', '".$cod."', '".$doc."') ";

                                     $result = pg_query($connid, $sql);
                                     if(!$result) $msg .= "Erro ao inserir depósito: Valor=\"$valor\" Data=\"$data\" Cod Doc=\"$cod\" Doc=\"$doc\"\n$sql\n\n";
                                }
                            }
                        }
                }

                //Bradesco
                if($dd_banco == $BOLETO_MONEY_BRADESCO_COD_BANCO){ //Bradesco - 237
                    
                    $tipo_pagamento = '5'; // rfcb_tipo_pagamento
                    $linha = $depositos;
                    $data_anterior = null;
                    for($x = 0 ; $x < count($linha) ; $x++){

                        $data = substr($linha[$x], 164, 8);
                        $cod = strtoupper(trim(substr($linha[$x], 49, 25)));
                        $cod2 = strtoupper(trim(substr($linha[$x], 105, 44)));
                        $doc = substr($linha[$x], 149, 8);
                        $valor = substr($linha[$x], 95, 9);

                        $identReg = substr($linha[$x], 0, 1);
                        $lanc = substr($linha[$x], 41, 1);
                        $tipoLanc = strtoupper(substr($linha[$x], 104, 1));
                        $agencia = substr($linha[$x], 17, 4);
                        $conta = substr($linha[$x], 33, 8);


                        if($identReg == '1' && $lanc == '1' && $tipoLanc == 'C'){
                            if(substr($cod, 0, 3) == 'DEP' || substr($cod, 0, 6) == 'TRANSF' || substr($cod, 0, 3) == 'DOC' || substr($cod, 0, 3) == 'TED'){

                                //Validacao
                                $dd_agencia_aux = $dd_agencia;
                                if(strpos($dd_agencia, "-") !== false) $dd_agencia_aux = substr($dd_agencia, 0, strpos($dd_agencia, "-"));
                                $dd_agencia_aux = str_pad($dd_agencia_aux, 4, "0", STR_PAD_LEFT);
                                $agencia_aux = str_pad($agencia, 4, "0", STR_PAD_LEFT);
                                $dd_conta_aux = str_replace(".", "", str_replace("-", "", $dd_conta));
                                $dd_conta_aux = str_pad($dd_conta_aux, 8, "0", STR_PAD_LEFT);
                                $conta_aux = str_pad($conta, 8, "0", STR_PAD_LEFT);
                                if($agencia_aux != $dd_agencia_aux) $msg .= "Agência no arquivo ($agencia) difere da agência selecionada ($dd_agencia).\n";
                                if($conta_aux != $dd_conta_aux) $msg .= "Conta no arquivo ($conta) difere da conta selecionada ($dd_conta).\n";

                                if(verifica_data(substr($data, 0, 2) . "/" . substr($data, 2, 2) . "/" . substr($data, 4, 4)) == 0) $msg .= "Data ($data) inválida.\n";
                                if(trim($doc)!= "" && !is_numeric($doc)) $msg .= "Documento ($doc) deve ser númerico.\n";
                                if(trim($valor)!= "" && !is_numeric($valor)) $msg .= "Valor ($valor) deve ser númerico.\n";

                                if($msg != "") break;
                                else {
                                    $data = substr($data, 4, 4) . "-" . substr($data, 2, 2) . "-" . substr($data, 0, 2);
                                    $valor = intval($valor) / 100;

                                    $sql  = "select dep_valor from depositos_pendentes where ";
                                    $sql .= "   dep_valor='$valor' and dep_data='$data' and dep_cod_documento='$cod' and dep_documento='$doc'";
                                    $sql .= "   and dep_banco='$dd_banco' and dep_agencia='$dd_agencia' and dep_conta='$dd_conta' ";
                                    $result = pg_exec($connid, $sql);
                                    if(pg_num_rows($result) > 0) {
                                        $msgInfo .= "Depósito duplicado: $data, $dd_banco, $agencia, $conta, $doc, $cod, $valor, $cod2\n";
                                    } else {
                                        $sql  = "insert into depositos_pendentes (dep_valor, dep_data, dep_banco, dep_agencia, dep_conta, dep_cod_documento, dep_cod_documento2, dep_documento) ";
                                        $sql .= "values (".$valor.", '".$data."', '".$dd_banco."', '".$dd_agencia."', '".$dd_conta."', '".$cod."', '".$cod2."', '".$doc."') ";
                                        $result = pg_exec($connid, $sql);
                                        if(!$result) $msg .= "Erro ao inserir depósito: Valor='$valor' Data='$data' Cod Doc='$cod' Doc='$doc' \n$sql\n\n";
                                        //Excluindo a conta para depósito OffLine
                                        elseif ($dd_conta != $BANCO_DEP[INDICE_CONTA_BRADESCO_DEP_OFFLINE_ANTIGA][3] && $dd_conta != $BANCO_DEP[INDICE_CONTA_BRADESCO_DEP_OFFLINE_NOVA][3]) {
                                            
                                                // Agora verificamos se essa data existe no relatorio de conciliacao bancaria
                                                // se sim, vamos atualizar os dados
                                                $data_formatada = $data;
                                                $valor = str_replace(',', '.', trim($valor));
                                                $sqlFreeze = "SELECT rfcb_id,rfcb_data_registro FROM relfin_conciliacao_bancaria WHERE rfcb_data_registro = '{$data_formatada}' AND rfcb_tipo_pagamento='{$tipo_pagamento}' and rfcb_freeze_bradesco=1; ";
                                                $rsFreeze = SQLexecuteQuery($sqlFreeze);
                                                //echo "<br><br> - $sqlFreeze<br>";
                                                if ( pg_num_rows($rsFreeze) == 0 ) {
                                                    
                                                        // Agora que sabemos que existe a linha, vamos atualiza-la
                                                        $sqlVerRelFin = "SELECT rfcb_id,rfcb_data_registro FROM relfin_conciliacao_bancaria WHERE rfcb_data_registro = '{$data_formatada}' AND rfcb_tipo_pagamento='{$tipo_pagamento}';";
                                                        $rsVerRelFin = SQLexecuteQuery($sqlVerRelFin);
                                                        //echo "<br><br> - $sqlVerRelFin<br>";
                                                        if ( pg_num_rows($rsVerRelFin) > 0 ) {
                                                            // Agora que sabemos que existe a linha, vamos atualiza-la
                                                            $dataVerRelFin = pg_fetch_assoc($rsVerRelFin);

                                                            $idRelFin = $dataVerRelFin['rfcb_id'];

                                                            // Temos que encontrar a data correta do movimento (não data do extrato)
                                                            // Banco Bradesco = D+0 => não precisa de tratamento de datas

                                                            $sqlUpdateRelFin = "
                                                                     UPDATE relfin_conciliacao_bancaria SET rfcb_valor_extrato = rfcb_valor_extrato + {$valor}
                                                                     WHERE rfcb_id={$idRelFin}; ";

                                                            //echo $sqlUpdateRelFin."<br>";
                                                            SQLexecuteQuery($sqlUpdateRelFin);
                                                            //Verificando data para congelamento
                                                            if($data_formatada != $data_anterior) {
                                                                if(!empty($data_anterior)) {
                                                                    $sqlUpdateRelFinEnd = "
                                                                            UPDATE relfin_conciliacao_bancaria SET rfcb_freeze_bradesco = 1
                                                                            WHERE rfcb_id={$idRelFin}; ";
                                                                    //echo "Dentro repetição:".$sqlUpdateRelFinEnd."<br>";
                                                                    SQLexecuteQuery($sqlUpdateRelFinEnd);
                                                                } //end if(!empty($data_anterior))
                                                                $data_anterior = $data_formatada;
                                                            }//end if(!empty($data_anterior) && $data_formatada != $data_anterior)

                                                        } //end if ( pg_num_rows($rsVerRelFin) > 0 )

                                                } //end if ( pg_num_rows($rsFreeze) == 0 )
                                            
                                            
                                        } //end elseif ($dd_conta != $BANCO_DEP[INDICE_CONTA_BRADESCO_DEP_OFFLINE_ANTIGA][3] && $dd_conta != $BANCO_DEP[INDICE_CONTA_BRADESCO_DEP_OFFLINE_NOVA][3])
                                    } //end else do if(pg_num_rows($result) > 0)
                                } //end else do if($msg != "")
                            } //end if(substr($cod, 0, 3) == 'DEP' || substr($cod, 0, 6) == 'TRANSF' || substr($cod, 0, 3) == 'DOC')
                        } //end if($identReg == '1' && $lanc == '1' && $tipoLanc == 'C')
                    } //end for
                    if(!empty($idRelFin)){
                            $sqlUpdateRelFinEnd = "
                                    UPDATE relfin_conciliacao_bancaria SET rfcb_freeze_bradesco = 1
                                    WHERE rfcb_id={$idRelFin}; ";
                            //echo "FORA repetição:".$sqlUpdateRelFinEnd."<br>";
                            SQLexecuteQuery($sqlUpdateRelFinEnd);
                    } //end if(!empty($idRelFin))
                    if(isset($msgInfo) && $msgInfo != "")$msgInfo .= "Demais registros inseridos.\n";
                } //end if($dd_banco == $BOLETO_MONEY_BRADESCO_COD_BANCO)
                
                
                // Banco Itaú
                if ( $BOLETO_MONEY_BANCO_ITAU_COD_BANCO == $dd_banco ) { // Itaú - 341
                        $tipo_pagamento = 'A'; // rfcb_tipo_pagamento
                        $linhas = explode(PHP_EOL, $ta_depositos);
                        if ( isset($linhas[1]) ) {
                            foreach ($linhas as $linha) {
                                $linha = trim($linha);
                                if ( !empty($linha) ) {
                                    // pegar TAR/CUSTAS COBRANCA e MOV TIT COB DISP para ir pra tabela de conciliacao
                                    list($data, $lancamento, $valor) = explode(';', $linha);

                                    if(strpos(trim(strtoupper($lancamento)), "TAR/CUSTAS COBRANCA") === false && strpos(trim(strtoupper($lancamento)), "MOV TIT COB DISP") === false){
                                        continue;
                                    }

                                    $valor = str_replace(',', '.', trim($valor));
                                    $data_formatada = formata_data($data,1);

                                    // Verificando dado já existe:
                                    $sqlVerDep = "SELECT * FROM depositos_pendentes
                                                    WHERE dep_banco='{$dd_banco}'
                                                    AND dep_agencia='{$dd_agencia}'
                                                    AND dep_data='{$data_formatada}'
                                                    AND dep_valor={$valor};";

                                    $rsVerDep = SQLexecuteQuery($sqlVerDep);
                                    if ( pg_num_rows($rsVerDep) > 0 ) {
                                        $msg .= "Erro ao inserir depósito: Valor=\"$valor\" Data=\"$data_formatada\" Lancamento=\"$lancamento\" - Dado Duplicado\n\n";
                                    } else {
                                        // Primeiro tratamos sobre o relatorio de conciliacao bancaria
                                        $eprepagCode = getCodigoHistorico($dd_banco, $lancamento);
                                        $fieldCode = '';
                                        $valueCode = '';
                                        if ( preg_match('/(mov\stit|tar\/custas)/', strtolower($lancamento)) ) {
                                        //if ( in_array($eprepagCode, array(MOV, TAR)) ) {
                                            // Temos que encontrar a data correta do movimento (não data do extrato)
                                            // Banco Itaú = D+0 (passou a ser D+0 em Novembro de 2018)
                                            list($ano, $mes, $dia) = explode('-', $data_formatada);
                                            $dia = substr($dia, 0, 2);
                                            $data = "$dia/$mes/$ano";
                                            $stimestamp = mktime(0, 0, 0, $mes, $dia, $ano);

                                            $ndate = date('Y-m-d', $stimestamp);
                                            // Agora verificamos se essa data existe no relatorio de conciliacao bancaria
                                            // se sim, vamos atualizar os dados
                                            $sqlVerRelFin = "SELECT rfcb_id,rfcb_data_registro FROM relfin_conciliacao_bancaria WHERE rfcb_data_registro = '{$ndate}' AND rfcb_tipo_pagamento='{$tipo_pagamento}'";
                                            $rsVerRelFin = SQLexecuteQuery($sqlVerRelFin);
                                            //echo "<br><br> - $sqlVerRelFin<br>";
                                            if ( pg_num_rows($rsVerRelFin) > 0 ) {
                                                // Agora que sabemos que existe a linha, vamos atualiza-la
                                                $dataVerRelFin = pg_fetch_assoc($rsVerRelFin);

                                                $idRelFin = $dataVerRelFin['rfcb_id'];
                                                $fieldCode = ', dep_cod_documento2';
                                                $valueCode = ", '{$eprepagCode}'";

                                                $sqlUpdateRelFin = "UPDATE relfin_conciliacao_bancaria SET {$field[$eprepagCode]['field']}={$valor} "
                                                    . "WHERE rfcb_data_registro='{$ndate}' AND rfcb_tipo_pagamento='{$tipo_pagamento}'";

                                                //$sqlUpdateRelFin = "UPDATE relfin_conciliacao_bancaria SET {$field[$eprepagCode]['field']}={$valor} WHERE rfcb_id={$idRelFin};";
                                                SQLexecuteQuery($sqlUpdateRelFin);
                                            } //end if ( pg_num_rows($rsVerRelFin) > 0 )
                                        } //end if ( preg_match('/(mov\stit|tar\/custas)/', strtolower($lancamento)) )
                                        $sql = "insert into depositos_pendentes (dep_valor, dep_data, dep_banco, dep_agencia, dep_conta, dep_cod_documento {$fieldCode}) ";
                                        $sql .= "values ({$valor}, to_date('{$data}', 'DD-MM-YYYY'), '{$dd_banco}', '{$dd_agencia}', '{$dd_conta}', '{$lancamento}' {$valueCode}) ";
                                        $resultQry = pg_exec($connid, $sql);
                                        if(!$resultQry) $msg .= "Erro ao inserir depósito: Valor='$valor' Data='$data' Banco='$dd_banco\' Conta='$dd_conta\' Lançamento:'$lancamento\' \n$sql\n\n";
                                    } //end else do if ( pg_num_rows($rsVerDep) > 0 )
                                } //end if ( !empty($linha) )
                            } //end foreach
                        } //end if ( isset($linhas[1]) )
                } //end if ( $BOLETO_MONEY_BANCO_ITAU_COD_BANCO == $dd_banco )

                //Finaliza transacao
                if($msg == "") {
                    pg_query($connid, "COMMIT TRANSACTION;");
                }
                else {
                    pg_query($connid, "ROLLBACK TRANSACTION;");
                }
            }
        }
        if((!isset($msg) || $msg == "") && (!isset($msgInfo) ||$msgInfo == "")) $VoltarPagina = 2;
        else $msg .= $msgInfo;
//exit;
        echo "Processado!<br>";
    }
if (isset($VoltarPagina) && $VoltarPagina) {?>
<meta http-equiv="refresh" content="0;URL=pendentes.php">
<?php } ?>

<script language="javascript">
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<table class="txt-preto fontsize-pp">
  <tr> 
    <td>
        <form name="form1" method="post">
        <table class="table">
          <tr> 
            <td width="8%" bgcolor="#268fbd"><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif">Banco</font></td>
            <td width="92%" colspan="2" bgcolor="#f5f5fb"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif"> 
              <!--select name="dd_banco" id="dd_banco" class="combo_normal">
                <?php if(isset($resbco)){ ?>
                <?php while($pgbco = pg_fetch_array($resbco)) { ?>
                <option value="<?php echo $pgbco['bco_codigo'] ?>" <?php if($pgbco['bco_codigo'] == $dd_banco) echo "selected" ?>><?php echo $pgbco['bco_nome'] ?></option>
                <?php }} ?>
              </select-->

              <select name="dd_banco_index" id="dd_banco" class="combo_normal">
                <?php foreach ($BANCO_DEP as $key => $value) { ?>
                <option value="<?php echo $key ?>" <?php if(isset($dd_banco_index) && $key == $dd_banco_index) echo "selected" ?>><?php echo $value[0] . " - " . $value[1] . " - Ag: " . $value[2] . " C/C: " . $value[3] ?></option>
                <?php } ?>
              </select>

              </font></td>
          </tr>
          <tr bgcolor="#FFFFFF"> 
            <td>&nbsp;</td>
            <td colspan="2">&nbsp;</td>
          </tr>
          <?php if(isset($msg) && $msg != ""){ ?>
          <tr bgcolor="#FFFFFF"> 
            <td colspan="3">
                <font color="red" size="2"><?php
                    if ( empty($msg) ) {
                        echo 'msg em branco';
                    } else {
                        echo nl2br($msg);
                    }
                    ?>
                </font>
            </td>
          </tr>
          <?php } ?>
          <tr bgcolor="#f5f5fb"> 
            <td colspan="3"> <table width="100%" border="0" cellspacing="1" cellpadding="2" align="center">
                <tr> 
                  <td colspan="2"><font face="Arial, Helvetica, sans-serif" size="2" color="#268fbd"><b>
                    Dep&oacute;sitos</b> </font></td>
                </tr>
                <tr bgcolor="#F5F5FB"> 
                  <td width="99%"> <font face="Arial, Helvetica, sans-serif" size="2" color="#FFFFFF"> 
                    <textarea name="ta_depositos" rows="20" cols="120" class="ta_registra_deposito txt-preto" scroll="NO"><?php if(isset($ta_depositos)) echo $ta_depositos ?></textarea>
                    <?php
                        if(isset($area_vazio) && $area_vazio == true)
                            echo "<br><font color='#FF0000'><b>Obs: Preencha a área</b></font>";
                        elseif(isset($formatacao_invalida) && $formatacao_invalida == true)
                            echo "<br><font color='#FF0000'><b>Obs: Formatacao Invalida</b></font>";
                    ?>
                    </font></td>
                </tr>
              </table></td>
          </tr>
          <tr> 
            <td colspan="3"><input name="BtnRegistrar" type="submit" id="BtnRegistrar" value="Registrar" class="btn btn-sm btn-info" onClick="GP_popupConfirmMsg('Deseja registrar esses depositos?');return document.MM_returnValue"> 
            </td>
          </tr>
          <tr><td colspan="3"> <p>&nbsp;</p><p>&nbsp;</p><?php require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php"; ?></td></tr>
        </table> 
      </form></td>
  </tr>
</table>
</html>
