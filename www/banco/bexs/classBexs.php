<?php

class classBexs{
    
    private $data_ini;
    private $data_fim;
    private $data_me;
    private $data_mn;
    private $data_lq;
    private $data_op;
    private $id_operadora;
    private $perfil_op;
    private $valor_moeda_nacional;
    private $valor_moeda_estrangeira;
    private $cotacao_dolar;
    private $status;
    private $merchant_id_bexs;

    public $msg_remessa_env_ou_conc;
    public $optionsArraySoapClient;
    public $hash;
    public $nome_arquivo_zip;
    public $controle_insert_update;
    public $msg_erro;
    public $aviso_background;
    public $is_report;

    public function __construct($id_publisher = null, $arr = array(), $is_background = FALSE, $is_report = FALSE) {
        
        $this->setStatus(TRUE);
        $this->setIsReport($is_report);
        
        $errors = array();
        
        //Validação dos dados
        if($this->validation_ws($arr, $errors)){
            $this->setDataIni($arr['data_ini']);
            $this->setDataFim($arr['data_fim']);
            $this->setDataMoedaEstrangeira($arr['data_me']);
            $this->setDataMoedaNacional($arr['data_mn']);
            $this->setDataLiquidacao($arr['data_lq']);
            $this->setDataOperacao($arr['data_op']);
            $this->setIdOperadora($arr['dd_operadora']);
            $this->setPerfilOperacional($arr['perfil_op']);
            $this->setValorMoedaNacional($arr['valor_moeda_nacional']);
            $this->setNomeMerchant($arr['nome_merchant']);
            $this->setMerchantIdBexs($arr['merchant_id_bexs']);
            
            $remessa_enviada_ou_concluida = $this->verifica_remessa_enviada_ou_concluida();
            
            if($remessa_enviada_ou_concluida){
                //verifica se houve resposta do processamento do arquivo de operações que deve acontecer em até 30 minutos, ou seja, caso status seja $GLOBALS['ARRAY_STATUS']['SUCESSO_SFTP']
                $this->setMsgRemessaEnvOuConc($remessa_enviada_ou_concluida);
                $this->setStatus(FALSE);
            }
            else {
                $ausencia_resposta_processamento = $this->ausencia_resposta_processamento();
                
                if($ausencia_resposta_processamento){
                    
                    $this->setMsgRemessaEnvOuConc("3");
                    
                    $this->setNomeArquivoZip(null, $ausencia_resposta_processamento);
                    
                    $this->setStatus(FALSE);
                } else{
                    
                    $cot_dolar = $this->recupera_cotacao_dolar();

                    if($cot_dolar || $is_background){ 
                        
                        if(!$is_background){
                            $this->setCotacaoDolar($cot_dolar);

                            $this->setValorMoedaEstrangeira($this->getValorMoedaNacional()/$this->getCotacaoDolar());
                        }

                        $existe_id_arquivo = $this->verifica_existe_id_arquivo();

                        if($existe_id_arquivo){

                            $existe_com_infos_iguais = $this->verifica_dados_remessa($existe_id_arquivo);

                            if($existe_com_infos_iguais){
                                
                                if($is_background){
                                    $this->setAvisoBackground(TRUE);
                                    
                                } else{

                                    switch ($existe_com_infos_iguais){
                                        case $GLOBALS['ARRAY_STATUS']['REGISTRO_CRIADO']:
                                            $novo_status = $GLOBALS['ARRAY_STATUS']['REGISTRO_CRIADO'];
                                            break;

                                        case $GLOBALS['ARRAY_STATUS']['SUCESSO_WS']:
                                            $novo_status = $GLOBALS['ARRAY_STATUS']['SUCESSO_WS'];

                                            break;

                                        case $GLOBALS['ARRAY_STATUS']['ERRO_CAMPO_OBRIGATORIO']:
                                            $novo_status = $GLOBALS['ARRAY_STATUS']['SUCESSO_WS'];
                                            break;

                                        case $GLOBALS['ARRAY_STATUS']['ERRO_SFTP']:
                                            $novo_status = $GLOBALS['ARRAY_STATUS']['SUCESSO_WS'];
                                            break;

                                        case $GLOBALS['ARRAY_STATUS']['ERRO_ACESSO_WS']:
                                            $novo_status = $GLOBALS['ARRAY_STATUS']['REGISTRO_CRIADO'];
                                            break;

                                        case $GLOBALS['ARRAY_STATUS']['ERRO_WS']:
                                            $novo_status = $GLOBALS['ARRAY_STATUS']['REGISTRO_CRIADO'];
                                            break;

                                        case $GLOBALS['ARRAY_STATUS']['ERRO_PROCESSAMENTO']:
                                            $novo_status = $GLOBALS['ARRAY_STATUS']['SUCESSO_WS'];
                                            break;

                                    }
                                    
                                    $this->setNomeArquivoZip(null, $existe_id_arquivo);
                                    $this->setControleInsertUpdate("update", $novo_status);
                                }
                                
                            } else{
                                $existe_com_infos_diferentes = $this->status_infos_diferentes($existe_id_arquivo);
                                
                                if($existe_com_infos_diferentes){
                                    if($existe_com_infos_diferentes == $GLOBALS['ARRAY_STATUS']['SUCESSO_WS'] || 
                                       $existe_com_infos_diferentes == $GLOBALS['ARRAY_STATUS']['ERRO_WS'] || 
                                       $existe_com_infos_diferentes == $GLOBALS['ARRAY_STATUS']['ERRO_CAMPO_OBRIGATORIO'] || 
                                       $existe_com_infos_diferentes == $GLOBALS['ARRAY_STATUS']['ERRO_SFTP'] || 
                                       $existe_com_infos_diferentes == $GLOBALS['ARRAY_STATUS']['ERRO_PROCESSAMENTO'])
                                    {
                                        $sql_infos = "SELECT * FROM remessa_bexs WHERE id_arquivo = '".$existe_id_arquivo."' ;";
                                        
                                        $rs_i = SQLexecuteQuery($sql_infos);

                                        if($rs_i && pg_num_rows($rs_i) == 1){
                                            $rs_row = pg_fetch_array($rs_i);
                                            $comparativo = "<strong>Dados enviados:</strong><br>".
                                                "Anteriormente | Agora:<br><br>".
                                                "Data Operação: ".substr($rs_row['data_operacao'], 0, 10)." | ".$this->getDataOperacao().
                                                "<br>Data Moeda Estrangeira: ".substr($rs_row['data_moeda'], 0, 10)." | ".$this->getDataMoedaEstrangeira().
                                                "<br>Data Moeda Nacional: ".substr($rs_row['data_moeda_nacional'], 0, 10)." | ".$this->getDataMoedaNacional().
                                                "<br>Data Liquidação: ".substr($rs_row['data_liquidacao'], 0, 10)." | ".$this->getDataLiquidacao().
                                                "<br>Valor Moeda Nacional: ".$rs_row['valor_moeda_nacional']." | ".$this->getValorMoedaNacional().
                                                "<br>Valor Moeda Estrangeira: ".$rs_row['valor_moeda_estrangeira']." | ".$this->getValorMoedaEstrangeira();
                                        } else{
                                            $comparativo = "<strong>Problema ao recuperar dados inconsistentes para comparação. Contacte o setor de T.I</strong>";
                                        }
                                    }
                                    
                                    $msg_user = "<strong>ERRO 501</strong>: As informações da remessa de ID <strong>".$existe_id_arquivo."</strong> já foram enviadas ao BEXS e na tentativa atual há uma diferença nos dados.<br> Um e-mail contendo os detalhes e as instruções a seguir está sendo enviado para <i>financeiro@e-prepag.com.br</i>";
                                    
                                    $msg_prob = "A remessa de ID <strong>".$existe_id_arquivo."</strong> apresentou problemas de inconsistência de dados<br><br>Verifique o comparativo abaixo e veja se os dados enviados agora seriam os dados corretos. <br><br>Se sim, entre em contato com o suporte BEXS para verificar se existe a necessidade de cancelamento do lado deles.<br><br>Em seguida, no Relatório de Envio de Remessas BEXS, localizado no Backoffice, clique para Cancelar a remessa com o ID citado acima<br><br>".$comparativo;

                                    switch ($existe_com_infos_diferentes){
                                        case $GLOBALS['ARRAY_STATUS']['REGISTRO_CRIADO']:
                                            $novo_status = $GLOBALS['ARRAY_STATUS']['REGISTRO_CRIADO'];

                                            $this->setNomeArquivoZip(null, $existe_id_arquivo);
                                            $this->setControleInsertUpdate("update", $novo_status);
                                            break;

                                        case $GLOBALS['ARRAY_STATUS']['SUCESSO_WS']:

                                            $this->setMsgErro($msg_user);
                                            $this->setStatus(FALSE);

                                            $assunto = (checkIP()?"[DEV] ":"[PROD] ")."Informações inconsistentes de remessa enviada ao BEXS";
                                            break;

                                        case $GLOBALS['ARRAY_STATUS']['ERRO_CAMPO_OBRIGATORIO']:
                                            
                                            $this->setMsgErro($msg_user);
                                            $this->setStatus(FALSE);

                                            $assunto = (checkIP()?"[DEV] ":"[PROD] ")."Informações inconsistentes de remessa enviada ao BEXS";
                                            break;

                                        case $GLOBALS['ARRAY_STATUS']['ERRO_SFTP']:
                                            
                                            $this->setMsgErro($msg_user);
                                            $this->setStatus(FALSE);

                                            $assunto = (checkIP()?"[DEV] ":"[PROD] ")."Informações inconsistentes de remessa enviada ao BEXS";
                                            break;

                                        case $GLOBALS['ARRAY_STATUS']['ERRO_ACESSO_WS']:

                                            $novo_status = $GLOBALS['ARRAY_STATUS']['REGISTRO_CRIADO'];

                                            $this->setNomeArquivoZip(null, $existe_id_arquivo);
                                            $this->setControleInsertUpdate("update", $novo_status);
                                            break;

                                        case $GLOBALS['ARRAY_STATUS']['ERRO_WS']:

                                            $this->setMsgErro($msg_user);
                                            $this->setStatus(FALSE);

                                            $assunto = (checkIP()?"[DEV] ":"[PROD] ")."Informações inconsistentes de remessa enviada ao BEXS";
                                            break;
                                        
                                        case $GLOBALS['ARRAY_STATUS']['ERRO_PROCESSAMENTO']:
                                            
                                            $this->setMsgErro($msg_user);
                                            $this->setStatus(FALSE);

                                            $assunto = (checkIP()?"[DEV] ":"[PROD] ")."Informações inconsistentes de remessa enviada ao BEXS";
                                            break;

                                    }

                                    if(isset($assunto) && $assunto != ""){
                                        $destino = (checkIP()) ? EMAIL_DEV : EMAILS_PROD;
                                        
                                        enviaEmail($destino, null, null, $assunto, $msg_prob);
                                    }
                                } else{
                                    $this->setMsgErro("<strong>ERRO 910</strong>: Problema inesperado no servidor de BD");
                                    $this->setStatus(FALSE);
                                }
                                
                            }

                        } else{
                            $this->setNomeArquivoZip($id_publisher);
                            $this->setControleInsertUpdate("insert");
                        }

                    } //end if($cot_dolar)
                    else{
                        $this->setMsgErro("<strong>ERRO 300</strong>: Cotação do dólar não cadastrada no BackOffice");
                        $this->setStatus(FALSE);
                    }
                }

            }//end else do if($remessa_enviada_ou_concluida)
            
        } //end if($this->validation_ws($arr, $errors))
        elseif($this->getIsReport()) {
            $this->setDataIni($arr['data_ini']);
            $this->setDataFim($arr['data_fim']);
            $this->setIdOperadora($arr['dd_operadora']);
        }
        else {
            $this->setStatus(FALSE);
            $this->setMsgErro($this->getErrors($errors));
        }
   
    }
    
    private function getIsReport() {
        return $this->is_report;
    }
    
    private function setIsReport($data){
        $this->is_report = $data;
    }
    
    private function getDataIni() {
        return $this->data_ini;
    }
    
    private function setDataIni($data){
        $this->data_ini = $data;
    }
    
    private function getDataFim() {
        return $this->data_fim;
    }
    
    private function setDataFim($data){
        $this->data_fim = $data;
    }
    
    private function getDataMoedaEstrangeira() {
        return $this->data_me;
    }
    
    private function setDataMoedaEstrangeira($data){
        $this->data_me = $data;
    }
    
    private function getDataLiquidacao() {
        return $this->data_lq;
    }
    
    private function setDataLiquidacao($data){
        $this->data_lq = $data;
    }
    
    private function getDataOperacao() {
        return $this->data_op;
    }
    
    private function setDataOperacao($data){
        $this->data_op = $data;
    }
    
    private function getDataMoedaNacional() {
        return $this->data_mn;
    }
    
    private function setDataMoedaNacional($data){
        $this->data_mn = $data;
    }
    
    private function getIdOperadora() {
        return $this->id_operadora;
    }
    
    private function setIdOperadora($id){
        $this->id_operadora = $id;
    }
    
    private function getPerfilOperacional() {
        return $this->perfil_op;
    }
    
    private function setPerfilOperacional($data){
        $this->perfil_op = $data;
    }
    
    private function getValorMoedaNacional() {
        return $this->valor_moeda_nacional;
    }
    
    private function setValorMoedaNacional($data){
        $this->valor_moeda_nacional = number_format($data, 2, ".","");
    }
    
    private function getValorMoedaEstrangeira() {
        return $this->valor_moeda_estrangeira;
    }
    
    private function setValorMoedaEstrangeira($data){
        $this->valor_moeda_estrangeira = number_format($data, 2, ".","");
    }
    
    private function getNomeMerchant() {
        return $this->nome_merchant;
    }
    
    private function setNomeMerchant($data){
        $this->nome_merchant = $data;
    }
    
    private function getCotacaoDolar() {
        return $this->cotacao_dolar;
    }
    
    private function setCotacaoDolar($data){
        $this->cotacao_dolar = $data;
    }
    
    public function getStatus() {
        return $this->status;
    }
    
    private function setStatus($data){
        $this->status = $data;
    }
    
    public function getMsgErro() {
        return $this->msg_erro;
    }
    
    private function setMsgErro($data){
        $this->msg_erro = $data;
    }
    
    public function getAvisoBackground() {
        return $this->aviso_background;
    }
    
    private function setAvisoBackground($data){
        $this->aviso_background = $data;
    }
    
    public function getnomeArquivoZip() {
        return $this->nome_arquivo_zip;
    }
    
    private function getMerchantIdBexs(){
        return $this->merchant_id_bexs;
    }
    
    private function setMerchantIdBexs($data){
        $this->merchant_id_bexs = $data;
    }

    private function setNomeArquivoZip($id_pub, $arquivo = NULL) {
        if(is_null($id_pub)){
            $this->nome_arquivo_zip = $arquivo.".zip";
        } else{
            $this->nome_arquivo_zip = $id_pub . "_xml_eprepag_".date("YmdHis").".zip";
        }
    }
    
    public function getMsgRemessaEnvOuConc() {
        return $this->msg_remessa_env_ou_conc;
    }
    
    private function setMsgRemessaEnvOuConc($msg){
        $this->msg_remessa_env_ou_conc = $msg;
    }
    
    private function processaPorDataUtilizacao(){
        $sql = "select opr_contabiliza_utilizacao from operadoras where opr_codigo = ".$this->getIdOperadora().";";
        $rs = SQLexecuteQuery($sql);
        
        if($rs && pg_num_rows($rs) > 0){
            $valor_aux = pg_fetch_array($rs);
            $id = $valor_aux['opr_contabiliza_utilizacao'];
            return $id;
        } else{
            return 0;
        }
    }
    
    private function ausencia_resposta_processamento(){
        
        $sql = "SELECT id_arquivo FROM remessa_bexs WHERE 
                        data_ini = '".$this->getDataIni()."' 
                        AND data_fim = '".$this->getDataFim()."' AND perfil_op = '".$this->getPerfilOperacional()."' 
                	AND SUBSTRING(id_arquivo FROM 1 FOR (POSITION ('_xml' IN id_arquivo) -1)) = '".$this->getIdOperadora()."' 
                        AND status = ".$GLOBALS['ARRAY_STATUS']['SUCESSO_SFTP']." AND (NOW() - data_atualizacao > '".BEXS_TEMPO_MAX_RESPOSTA." minutes'::interval);";
        
        $rs = SQLexecuteQuery($sql);
        
        if($rs && pg_num_rows($rs) > 0){
            $valor_aux = pg_fetch_array($rs);
            $id = $valor_aux['id_arquivo'];
            return $id;
        } else{
            return FALSE;
        }
        
    }

    private function verifica_remessa_enviada_ou_concluida() {
    
        $sql = "SELECT status FROM remessa_bexs WHERE 
                        data_ini = '".$this->getDataIni()."' 
                        AND data_fim = '".$this->getDataFim()."' AND perfil_op = '".$this->getPerfilOperacional()."' 
                	AND SUBSTRING(id_arquivo FROM 1 FOR (POSITION ('_xml' IN id_arquivo) -1)) = '".$this->getIdOperadora()."' 
                        AND ((status = ".$GLOBALS['ARRAY_STATUS']['SUCESSO_SFTP']." AND (NOW() - data_atualizacao < '".BEXS_TEMPO_MAX_RESPOSTA." minutes'::interval))
                        OR status = ".$GLOBALS['ARRAY_STATUS']['SUCESSO_PROCESSAMENTO']." )";
        $rs = SQLexecuteQuery($sql);
        
        $msg_modal_bexs = "";
        
        if($rs && pg_num_rows($rs) == 1) {
            $valor_aux = pg_fetch_array($rs);
            $st = $valor_aux['status'];

            if($st == $GLOBALS['ARRAY_STATUS']['SUCESSO_SFTP']){
                $msg_modal_bexs = "1";
            } elseif($st == $GLOBALS['ARRAY_STATUS']['SUCESSO_PROCESSAMENTO']){
                $msg_modal_bexs = "2";
            }
        }
        
        if($msg_modal_bexs != ""){
            return $msg_modal_bexs;
        } else{
            return FALSE;
        }
    }
    
    private function verifica_existe_id_arquivo() {
        $sql = "SELECT id_arquivo from remessa_bexs WHERE data_ini = date('".$this->getDataIni()."') 
                        AND data_fim = date('".$this->getDataFim()."') AND perfil_op = '".$this->getPerfilOperacional()."' 
                	AND SUBSTRING(id_arquivo FROM 1 FOR (POSITION ('_xml' IN id_arquivo) -1)) = '".$this->getIdOperadora()."' 
                        AND status <> ".$GLOBALS['ARRAY_STATUS']['CANCELADA']." ;";
        $rs = SQLexecuteQuery($sql);
        
        if($rs && pg_num_rows($rs) > 0){
            $rs_row = pg_fetch_array($rs);
            $id_arquivo = $rs_row['id_arquivo'];
            return $id_arquivo;
        } else{
            return FALSE;
        }
    }  
    
    private function status_infos_diferentes($id_arquivo) {
        $sql = "SELECT status from remessa_bexs WHERE id_arquivo = '".$id_arquivo."'";
        $rs = SQLexecuteQuery($sql);
        
        if($rs && pg_num_rows($rs) > 0){
            $rs_row = pg_fetch_array($rs);
            $status = $rs_row['status'];
            return $status;
        } else{
            return FALSE;
        }
    }
    
    private function verifica_dados_remessa($id_arq) {
        $sql = "SELECT status from remessa_bexs WHERE id_arquivo = '".$id_arq."' AND data_ini = '".$this->getDataIni()."' 
                        AND data_fim = '".$this->getDataFim()."' AND perfil_op = '".$this->getPerfilOperacional()."' 
                        AND data_operacao = '".$this->getDataOperacao()."' AND data_moeda = '".$this->getDataMoedaEstrangeira()."' 
                        AND data_moeda_nacional = '".$this->getDataMoedaNacional()."' AND data_liquidacao = '".$this->getDataLiquidacao()."' 
                        AND valor_moeda_nacional = ".$this->getValorMoedaNacional()." AND valor_moeda_estrangeira = ".$this->getValorMoedaEstrangeira();
        $rs = SQLexecuteQuery($sql);
        
        if($rs && pg_num_rows($rs) > 0){
            $valor_aux = pg_fetch_array($rs);
            $status = $valor_aux['status'];
            return $status;
        } else{
            return FALSE;
        }
    }
    
    public function need_req_Web_Service() {
        
        $sql = "SELECT * from remessa_bexs where perfil_op = '".$this->getPerfilOperacional()."' AND data_ini = '".$this->getDataIni()."' 
                        AND data_fim = '".$this->getDataFim()."' 
                	AND SUBSTRING(id_arquivo FROM 1 FOR (POSITION ('_xml' IN id_arquivo) -1)) = '".$this->getIdOperadora()."' 
                        AND (status = ".$GLOBALS['ARRAY_STATUS']['ERRO_ACESSO_WS']." OR status = ".$GLOBALS['ARRAY_STATUS']['ERRO_WS'].
                        " OR status = ".$GLOBALS['ARRAY_STATUS']['REGISTRO_CRIADO'].");";
        $rs = SQLexecuteQuery($sql);
        
        if($rs && pg_num_rows($rs) > 0){
            return TRUE;
        } else{
            return FALSE;
        }
    }
    
    public function req_Web_service(){
        
        $con = ConnectionPDO::getConnection();
        $pdo = $con->getLink();
        
        $err_sql = "";
        
        $req_ws = array
                        (
                            'id_arquivo' => str_replace(".zip", "", $this->getnomeArquivoZip()),
                            'perfil_op'  => $this->getPerfilOperacional(),
                            'tipoop'     => BEXS_TIPO_OP,
                            'moeda'      => BEXS_MOEDA,
                            'dataop'     => $this->getDataOperacao(), 
                            'formame'    => BEXS_FORMAME,
                            'datame'     => $this->getDataMoedaEstrangeira(),
                            'formamn'    => BEXS_FORMAMN,
                            'datamn'     => $this->getDataMoedaNacional(),
                            'datalq'     => $this->getDataLiquidacao(),
                            'valorme'    => $this->getValorMoedaEstrangeira(),
                            'valormn'    => $this->getValorMoedaNacional()
                        );
        
        $id_arquivo = str_replace(".zip", "", $this->getnomeArquivoZip());
        
        $response_ws = $this->Req_EfetuaTransmissao($req_ws, BEXS_XML_REQUISICAO_INFORMACOES_REMESSA, FALSE);
        
        if(($response_ws != FALSE && isset($response_ws))){

            if(isset($response_ws['numero']) && count($response_ws) > 1){
                
                $resultado = utf8_encode("<strong>ERRO 177</strong>: No envio das informações da remessa ao Web Service Bexs foi retornado o seguinte erro:<br>Cód.: ".$response_ws['numero']." - ".utf8_encode($response_ws['descricao'])."<br>Por favor, ajuste os erros e reenvie!<br><br>");
                        
                $assunto_falha = (checkIP()?"[DEV] ":"[PROD] ")."Requisição ao Web Service BEXS retornou resposta de ERRO";
                $msg_falha = "<u>A requisição enviada ao Web Service BEXS retornou um erro como resposta</u><br><br><u>Erro retornado</u>: ".$response_ws['descricao']."<br><br>Verifique o log de erros localizado em: <u>C:/Sites/E-Prepag/backoffice/offweb/tarefas/log/log_BEXS_WS-Errors.log</u>";
                
                $status_bexs = $GLOBALS['ARRAY_STATUS']['ERRO_WS'];

                $sql_update = "UPDATE remessa_bexs set status = ?, data_atualizacao = NOW() where id_arquivo = ?;";
                $params_update = array(
                                        filter_var($status_bexs, FILTER_SANITIZE_NUMBER_INT),
                                        filter_var($id_arquivo, FILTER_SANITIZE_STRING)
                                      );

                $stmt_update = $pdo->prepare($sql_update);
                $stmt_update->execute($params_update);

                if($stmt_update->rowCount() < 1){
                    $err_sql .= utf8_encode("<br><br><strong>ERRO 0102</strong>: Problema ao atualizar o status da transação no banco de dados!<br>Por favor, entre em contato com o setor de T.I.");
                }

            } //end if(isset($response_ws['numero']) && count($response_ws) > 1)
            else{

                $resultado = "<strong>SUCESSO</strong>: ". utf8_encode("A requisição enviada ao Web Service BEXS obteve resposta de sucesso<br>Taxa de nivelamento retornada após o envio: ") . "<strong>".$response_ws. "</strong><br><br>";

                $status_bexs = $GLOBALS['ARRAY_STATUS']['SUCESSO_WS'];

                $sql_update = "UPDATE remessa_bexs set status = ?, data_atualizacao = NOW() where id_arquivo = ?;";
                $params_update = array(
                                        filter_var($status_bexs, FILTER_SANITIZE_NUMBER_INT),
                                        filter_var($id_arquivo, FILTER_SANITIZE_STRING)
                                      );

                $stmt_update = $pdo->prepare($sql_update);
                $stmt_update->execute($params_update);

                if($stmt_update->rowCount() < 1){
                    $err_sql .= utf8_encode("<br><br><strong>ERRO 0106</strong>: Problema ao atualizar o status da transação no banco de dados!<br>Por favor, entre em contato com o setor de T.I.");
                }

            } // end else do if(isset($response_ws['numero']) && count($response_ws) > 1)

        } //end if($response_ws != FALSE && isset($response_ws)) 
        else {
            $assunto_falha = (checkIP()?"[DEV] ":"[PROD] ")."Falha na comunicação com Web Service BEXS";
            $msg_falha = "<u>Houve um problema na comunicação com o Web Service BEXS</u><br><br>O envio das informações da remessa via Web Service BEXS, referente a <strong>".$this->getNomeMerchant()."</strong>, <u>FALHOU</u>!<br><br>Verifique o log de erros localizado em: <u>C:/Sites/E-Prepag/backoffice/offweb/tarefas/log/log_BEXS_WS-Errors.log</u>";

            $resultado = utf8_encode("<strong>ERRO 170</strong>: Houve um problema de comunicação com o Web Service BEXS.<br>Por favor, entre em contato com o setor de T.I.<br><br>");
            $status_bexs = $GLOBALS['ARRAY_STATUS']['ERRO_ACESSO_WS'];

            $sql_update = "UPDATE remessa_bexs set status = ?, data_atualizacao = NOW() where id_arquivo = ?;";
            $params_update = array(
                                    filter_var($status_bexs, FILTER_SANITIZE_NUMBER_INT),
                                    filter_var($id_arquivo, FILTER_SANITIZE_STRING)
                                  );

            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute($params_update);

            if($stmt_update->rowCount() < 1){
                $err_sql .= utf8_encode("<br><br><strong>ERRO 0101</strong>: Problema ao atualizar o status da transação no banco de dados!<br>Por favor, entre em contato com o setor de T.I.");
            }

        } //end else if($response_ws != FALSE && isset($response_ws))
        
        if(isset($msg_falha) && isset($assunto_falha)){
            $destino = (checkIP()) ? EMAIL_DEV : EMAILS_PROD;
            enviaEmail($destino, null, null, $assunto_falha, $msg_falha.$err_sql);
        }
        
        $ret = ($err_sql == "") ? $resultado : $resultado.$err_sql;
        
        return $ret;
        
    }


    public function need_envio_sFTP() {
    
        $sql = "SELECT * FROM remessa_bexs WHERE 
                    data_ini = '".$this->getDataIni()."' AND data_fim = '".$this->getDataFim()."' 
                    AND SUBSTRING(id_arquivo FROM 1 FOR (POSITION ('_xml' IN id_arquivo) -1)) = '".$this->getIdOperadora()."' 
                    AND (status = ".$GLOBALS['ARRAY_STATUS']['SUCESSO_WS']." OR status = ".$GLOBALS['ARRAY_STATUS']['ERRO_PROCESSAMENTO'].
                    " OR status = ".$GLOBALS['ARRAY_STATUS']['ERRO_CAMPO_OBRIGATORIO']." OR status = ".$GLOBALS['ARRAY_STATUS']['ERRO_SFTP'].") 
                    AND perfil_op = '".$this->getPerfilOperacional()."'";
        $rs = SQLexecuteQuery($sql);
        
        if($rs && pg_num_rows($rs) > 0) {
            return TRUE;
        } else{
            
            return FALSE;
        }
    }
    
    public function envio_sFTP($reenvio_arq_op){
        
        $resultado = "";
        
        if(!$reenvio_arq_op){
            $resultado .= utf8_encode("**REENVIO APENAS DO ARQUIVO DE OPERAÇÕES**<br>");
        }
        
        $con = ConnectionPDO::getConnection();
        $pdo = $con->getLink();
        
        $err_sql = "";
        
        $array_operacoes = $this->array_operacoes_detalhadas($this->getCotacaoDolar());
        
        if(is_null($array_operacoes)){
            $resultado = utf8_encode("<strong>ERRO 0012</strong>: Problema ao recuperar dados para criação do arquivo de operações.<br>Por favor, entre em contato com o setor de T.I.");
        } else{
            
            $id_arquivo = str_replace(".zip", "", $this->getnomeArquivoZip());
            if($array_operacoes){

                $enviou_sftp = $this->Req_EfetuaTransmissao($array_operacoes, BEXS_XML_REQUISICAO_OPERACOES_REMESSA, $this->getnomeArquivoZip());

                if($enviou_sftp){   
                    if(is_array($enviou_sftp) && strpos($enviou_sftp['concluido'], "100")){

                        $resultado .= "<strong>SUCESSO</strong>: ". utf8_encode("O arquivo zipado contendo detalhes das operações foi enviado com sucesso!<br><strong>AGUARDE</strong> o processamento e resposta do BEXS que será enviada no e-mail <i>financeiro@e-prepag.com.br</i>") ."<br>Arquivo enviado: <strong>" . $enviou_sftp['arq_enviado'] ."</strong> - |". $enviou_sftp['tamanho']."|";

                        $status_bexs = $GLOBALS['ARRAY_STATUS']['SUCESSO_SFTP'];

                        $sql_update = "UPDATE remessa_bexs set status = ?, data_atualizacao = NOW() where id_arquivo = ?;";
                        $params_update = array(
                                                filter_var($status_bexs, FILTER_SANITIZE_NUMBER_INT),
                                                filter_var($id_arquivo, FILTER_SANITIZE_STRING)
                                              );

                        $stmt_update = $pdo->prepare($sql_update);
                        $stmt_update->execute($params_update);

                        if($stmt_update->rowCount() < 1){
                            $err_sql .= utf8_encode("<br><br><strong>ERRO 0110</strong>: Problema ao atualizar o status da transação no banco de dados!<br>Por favor, entre em contato com o setor de T.I.");
                        }

                    } //end if(is_array($enviou_sftp) && strpos($enviou_sftp['concluido'], "100") != FALSE)
                    else{
                        
                        $err_ret = $enviou_sftp;

                        $assunto_falha = (checkIP()?"[DEV] ":"[PROD] ")."Falha na transmissão de arquivo via sFTP ao BEXS";
                        $msg_falha = "<u>Houve um problema na transmissão do arquivo de operações via sFTP ao BEXS</u><br><br>O envio das operações da remessa via sFTP BEXS, referente a <strong>".$this->getNomeMerchant()."</strong>, <u>FALHOU</u>!<br><br><u>Erro Retornado</u>: ".$err_ret."<br><br>Verifique o log de erros localizado em: <u>C:/Sites/E-Prepag/backoffice/offweb/tarefas/log/log_BEXS_WS-Errors.log</u>";

                        $resultado .= utf8_encode("<strong>ERRO 171</strong>: Problema ao enviar o arquivo que contém as operações da remessa!<br>Por favor, entre em contato com o setor de T.I.<br><br>");

                        $status_bexs = $GLOBALS['ARRAY_STATUS']['ERRO_SFTP'];

                        $sql_update = "UPDATE remessa_bexs set status = ?, data_atualizacao = NOW() where id_arquivo = ?;";
                        $params_update = array(
                                                filter_var($status_bexs, FILTER_SANITIZE_NUMBER_INT),
                                                filter_var($id_arquivo, FILTER_SANITIZE_STRING)
                                              );

                        $stmt_update = $pdo->prepare($sql_update);
                        $stmt_update->execute($params_update);

                        if($stmt_update->rowCount() < 1){
                            $err_sql .= utf8_encode("<br><br><strong>ERRO 0107</strong>: Problema ao atualizar o status da transação no banco de dados!<br>Por favor, entre em contato com o setor de T.I.");
                        }

                        $destino = (checkIP()) ? EMAIL_DEV : EMAILS_PROD;
                        enviaEmail($destino, null, null, $assunto_falha, $msg_falha);

                    } //end else if(is_array($enviou_sftp) && strpos($enviou_sftp['concluido'], "100"))

                } //end if($enviou_sftp)
                else{
                    $resultado = utf8_encode("<strong>ERRO 173</strong>: Problema interno para geração e envio do arquivo de operações via sFTP!<br>Por favor, entre em contato com o setor de T.I.");
                }

            } //end if($array_operacoes)
            else{
                $status_bexs = $GLOBALS['ARRAY_STATUS']['ERRO_CAMPO_OBRIGATORIO'];

                $sql_update = "UPDATE remessa_bexs set status = ?, data_atualizacao = NOW() where id_arquivo = ?;";
                $params_update = array(
                                        filter_var($status_bexs, FILTER_SANITIZE_NUMBER_INT),
                                        filter_var($id_arquivo, FILTER_SANITIZE_STRING)
                                      );

                $stmt_update = $pdo->prepare($sql_update);
                $stmt_update->execute($params_update);

                if($stmt_update->rowCount() < 1){
                    $err_sql .= utf8_encode("<br><br><strong>ERRO 0111</strong>: Problema ao atualizar o status da transação no banco de dados!<br>Por favor, entre em contato com o setor de T.I.");
                }
                
                $resultado = utf8_encode($this->getMsgErro());
            }
        } 
        
        $ret = ($err_sql == "") ? $resultado : $resultado.$err_sql;
                
        return $ret;
        
    }
    
    public function array_operacoes_detalhadas($cotacao_dolar){
        
        if($this->processaPorDataUtilizacao()) {
                $sql = "
                select 
                    id_pdv,
                    ug_cpf, 
                    ug_nome,
                    to_char(data,'YYYY-MM-DD') as data,
                    to_char(nascimento,'YYYY-MM-DD') as nascimento,
                    to_char(verificacao,'YYYY-MM-DD') as verificacao,
                    trim(logradouro) as logradouro,
                    trim(bairro) as bairro,
                    trim(cidade) as cidade,
                    trim(estado) as estado,
                    trim(cep) as cep,
                    trim(telefone) as telefone,
                    trim(email) as email,
                    valor_total,
                    venda_id
                from ( 
                        ( select 
                                9999999 as id_pdv,
                                ug_cpf, 
                                ug_nome_cpf as ug_nome,
                                vg_data_concilia as data,
                                ug_data_nascimento as nascimento,
                                ug_data_cpf_informado as verificacao,
                                CASE WHEN (ug_tipo_end) != '' THEN ug_tipo_end || ': ' ||  ug_endereco || ', '|| ug_numero || ' ' || ug_complemento
                                ELSE ug_endereco || ', '|| ug_numero || ' ' || ug_complemento END as logradouro,
                                ug_bairro as bairro,
                                ug_cidade as cidade,
                                ug_estado as estado,
                                ug_cep as cep,
                                CASE WHEN TRIM(ug_cel)  != '' THEN '55' || ug_cel_ddd || ug_cel ELSE NULL END as telefone,
                                ug_email as email,
                                sum(vgm.vgm_valor * vgm.vgm_qtde) as valor_total,
                                cast(('".$GLOBALS['ARRAY_CONCATENA_ID_VENDA']['gamer']."' || vg.vg_id) as bigint) as venda_id
                        from tb_venda_games vg 
                                inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                                inner join usuarios_games ug on (ug.ug_id = vg.vg_ug_id)
                        where vg.vg_ultimo_status='5' 
                                and ug.ug_id != ".$GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']."
                                and vg.vg_data_concilia >= '".$this->getDataIni()." 00:00:00'
                                and vg.vg_data_concilia <= '".$this->getDataFim()." 23:59:59'
                                and (vgm_opr_codigo=".$this->getIdOperadora().") 
                        group by ug_cpf, ug_nome_cpf, vg_data_concilia, ug_data_nascimento, ug_data_cpf_informado, logradouro, bairro, cidade, estado, cep, telefone, telefone, ug_email, vg.vg_id )
   
                    union all

                        (select 
                                ug_id as id_pdv,
                                vgm_cpf as ug_cpf, 
                                vgm_nome_cpf as ug_nome, 
                                max(pih_data) as data,
                                vgm_cpf_data_nascimento as nascimento,
                                vg_data_inclusao as verificacao,
                                NULL as logradouro,
                                NULL as bairro,
                                NULL as cidade,
                                NULL as estado,
                                NULL as cep,
                                NULL as telefone,
                                NULL as email,
                                sum(vgm.vgm_valor) as valor_total ,
                                cast(('".$GLOBALS['ARRAY_CONCATENA_ID_VENDA']['pdv']."' || vg.vg_id) as bigint) as venda_id
                        from tb_dist_venda_games vg 
                                inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                                inner join dist_usuarios_games ug on (ug.ug_id = vg.vg_ug_id)
                                INNER JOIN tb_dist_venda_games_modelo_pins vgmp ON (vgmp_vgm_id = vgm.vgm_id)
                                INNER JOIN pins p ON (pin_codinterno = vgmp_pin_codinterno)
                                INNER JOIN pins_integracao_historico pih ON pih_pin_id = pin_codinterno
                        where  p.pin_status = '8' 
                                AND pih_codretepp='2' 
                                AND vg_ultimo_status='5'
                                and pih_data >= '".$this->getDataIni()." 00:00:00'
                                and pih_data <= '".$this->getDataFim()." 23:59:59'
                                and (vgm_opr_codigo=".$this->getIdOperadora().")  
                        group by vgm_cpf, vgm_nome_cpf, vgm_cpf_data_nascimento, vg_data_inclusao, logradouro, bairro, cidade, estado, cep, telefone, email, vg.vg_id, ug_id )  


                    union all

                        (select 
                                9999999 as id_pdv,
                                picc_cpf as ug_cpf, 
                                picc_nome as ug_nome,
                                pih_data as data,
                                picc_data_nascimento as nascimento,
                                picc_data_cpf_informado as verificacao,
                                NULL as logradouro,
                                NULL as bairro,
                                NULL as cidade,
                                NULL as estado,
                                NULL as cep,
                                NULL as telefone,
                                NULL as email,
                                sum(pih_pin_valor/100) as valor_total,
                                cast(('".$GLOBALS['ARRAY_CONCATENA_ID_VENDA']['cards']."' || pih_pin_id) as bigint) as venda_id
                        from pins_integracao_card_historico
                            left outer join pins_integracao_card_cpf ON (pin_codinterno=pih_pin_id)
                        where pin_status = '4' 
                                and pih_codretepp = '2'
                                and pih_data >= '".$this->getDataIni()." 00:00:00'
                                and pih_data <= '".$this->getDataFim()." 23:59:59'
                                and (pih_id=".$this->getIdOperadora().")  
                        group by picc_cpf, picc_nome, pih_data, picc_data_nascimento, picc_data_cpf_informado, logradouro, bairro, cidade, estado, cep, telefone, email, pih_pin_id )

                    union all

                        (select 
                                9999999 as id_pdv,
                                vgcbe_cpf as ug_cpf, 
                                vgcbe_nome_cpf as ug_nome, 
                                vg_data_concilia as data,
                                vgcbe_data_nascimento as nascimento,
                                vgcbe_data_inclusao as verificacao,
                                NULL as logradouro,
                                NULL as bairro,
                                NULL as cidade,
                                NULL as estado,
                                NULL as cep,
                                NULL as telefone,
                                vg_ex_email as email,
                                sum(vgm_valor * vgm_qtde) as valor_total,
                                cast(('".$GLOBALS['ARRAY_CONCATENA_ID_VENDA']['boleto_express']."' || vg_id) as bigint) as venda_id
                        from tb_venda_games_cpf_boleto_express
                            inner join tb_venda_games ON (vg_id = vgcbe_vg_id)
                            inner join tb_venda_games_modelo ON (vgm_vg_id = vg_id)
                        where vg_ultimo_status='5' 
                                and vg_data_concilia >= '".$this->getDataIni()." 00:00:00'
                                and vg_data_concilia <= '".$this->getDataFim()." 23:59:59'
                                and (vgm_opr_codigo=".$this->getIdOperadora().")  
                        group by vgcbe_cpf, vgcbe_nome_cpf, vg_data_concilia, vgcbe_data_nascimento, vgcbe_data_inclusao, logradouro, bairro, cidade, estado, cep, telefone, email, vg_id )


                ) tabelaUnion 
                order by data;  
                ";
            
        }//end if(processaPorDataUtilizacao())
        else {
                $sql = "
                select 
                    id_pdv,
                    ug_cpf, 
                    ug_nome,
                    to_char(data,'YYYY-MM-DD') as data,
                    to_char(nascimento,'YYYY-MM-DD') as nascimento,
                    to_char(verificacao,'YYYY-MM-DD') as verificacao,
                    trim(logradouro) as logradouro,
                    trim(bairro) as bairro,
                    trim(cidade) as cidade,
                    trim(estado) as estado,
                    trim(cep) as cep,
                    trim(telefone) as telefone,
                    trim(email) as email,
                    valor_total,
                    venda_id
                from ( 
                        ( select 
                                9999999 as id_pdv,
                                ug_cpf, 
                                ug_nome_cpf as ug_nome,
                                vg_data_concilia as data,
                                ug_data_nascimento as nascimento,
                                ug_data_cpf_informado as verificacao,
                                CASE WHEN (ug_tipo_end) != '' THEN ug_tipo_end || ': ' ||  ug_endereco || ', '|| ug_numero || ' ' || ug_complemento
                                ELSE ug_endereco || ', '|| ug_numero || ' ' || ug_complemento END as logradouro,
                                ug_bairro as bairro,
                                ug_cidade as cidade,
                                ug_estado as estado,
                                ug_cep as cep,
                                CASE WHEN TRIM(ug_cel)  != '' THEN '55' || ug_cel_ddd || ug_cel ELSE NULL END as telefone,
                                ug_email as email,
                                sum(vgm.vgm_valor * vgm.vgm_qtde) as valor_total,
                                cast(('".$GLOBALS['ARRAY_CONCATENA_ID_VENDA']['gamer']."' || vg.vg_id) as bigint) as venda_id
                        from tb_venda_games vg 
                                inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                                inner join usuarios_games ug on (ug.ug_id = vg.vg_ug_id)
                        where vg.vg_ultimo_status='5' 
                                and ug.ug_id != ".$GLOBALS['MONEY_EXPRESS_ID_USUARIO_MONEY']."
                                and vg.vg_data_concilia >= '".$this->getDataIni()." 00:00:00'
                                and vg.vg_data_concilia <= '".$this->getDataFim()." 23:59:59'
                                and (vgm_opr_codigo=".$this->getIdOperadora().") 
                        group by ug_cpf, ug_nome_cpf, vg_data_concilia, ug_data_nascimento, ug_data_cpf_informado, logradouro, bairro, cidade, estado, cep, telefone, telefone, ug_email, vg.vg_id )

                    union all

                        (select 
                                ug_id as id_pdv,
                                vgm_cpf as ug_cpf, 
                                vgm_nome_cpf as ug_nome, 
                                vg_data_inclusao as data,
                                vgm_cpf_data_nascimento as nascimento,
                                vg_data_inclusao as verificacao,
                                NULL as logradouro,
                                NULL as bairro,
                                NULL as cidade,
                                NULL as estado,
                                NULL as cep,
                                NULL as telefone,
                                NULL as email,
                                sum(vgm.vgm_valor * vgm.vgm_qtde) as valor_total ,
                                cast(('".$GLOBALS['ARRAY_CONCATENA_ID_VENDA']['pdv']."' || vg.vg_id) as bigint) as venda_id
                        from tb_dist_venda_games vg 
                                inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
                                inner join dist_usuarios_games u on vg.vg_ug_id = u.ug_id
                        where vg.vg_ultimo_status='5'  
                                and vg.vg_data_inclusao >= '".$this->getDataIni()." 00:00:00'
                                and vg.vg_data_inclusao <= '".$this->getDataFim()." 23:59:59'
                                and (vgm_opr_codigo=".$this->getIdOperadora().")  
                        group by vgm_cpf, vgm_nome_cpf, vg_data_inclusao, vgm_cpf_data_nascimento, vg_data_inclusao, logradouro, bairro, cidade, estado, cep, telefone, email, vg.vg_id, ug_id )  


                    union all

                        (select 
                                9999999 as id_pdv,
                                picc_cpf as ug_cpf, 
                                picc_nome as ug_nome,
                                pih_data as data,
                                picc_data_nascimento as nascimento,
                                picc_data_cpf_informado as verificacao,
                                NULL as logradouro,
                                NULL as bairro,
                                NULL as cidade,
                                NULL as estado,
                                NULL as cep,
                                NULL as telefone,
                                NULL as email,
                                sum(pih_pin_valor/100) as valor_total,
                                cast(('".$GLOBALS['ARRAY_CONCATENA_ID_VENDA']['cards']."' || pih_pin_id) as bigint) as venda_id
                        from pins_integracao_card_historico
                            left outer join pins_integracao_card_cpf ON (pin_codinterno=pih_pin_id)
                        where pin_status = '4' 
                                and pih_codretepp = '2'
                                and pih_data >= '".$this->getDataIni()." 00:00:00'
                                and pih_data <= '".$this->getDataFim()." 23:59:59'
                                and (pih_id=".$this->getIdOperadora().")  
                        group by picc_cpf, picc_nome, pih_data, picc_data_nascimento, picc_data_cpf_informado, logradouro, bairro, cidade, estado, cep, telefone, email, pih_pin_id )

                    union all

                        (select 
                                9999999 as id_pdv,
                                vgcbe_cpf as ug_cpf, 
                                vgcbe_nome_cpf as ug_nome, 
                                vg_data_concilia as data,
                                vgcbe_data_nascimento as nascimento,
                                vgcbe_data_inclusao as verificacao,
                                NULL as logradouro,
                                NULL as bairro,
                                NULL as cidade,
                                NULL as estado,
                                NULL as cep,
                                NULL as telefone,
                                vg_ex_email as email,
                                sum(vgm_valor * vgm_qtde) as valor_total,
                                cast(('".$GLOBALS['ARRAY_CONCATENA_ID_VENDA']['boleto_express']."' || vg_id) as bigint) as venda_id
                        from tb_venda_games_cpf_boleto_express
                            inner join tb_venda_games ON (vg_id = vgcbe_vg_id)
                            inner join tb_venda_games_modelo ON (vgm_vg_id = vg_id)
                        where vg_ultimo_status='5' 
                                and vg_data_concilia >= '".$this->getDataIni()." 00:00:00'
                                and vg_data_concilia <= '".$this->getDataFim()." 23:59:59'
                                and (vgm_opr_codigo=".$this->getIdOperadora().")  
                        group by vgcbe_cpf, vgcbe_nome_cpf, vg_data_concilia, vgcbe_data_nascimento, vgcbe_data_inclusao, logradouro, bairro, cidade, estado, cep, telefone, email, vg_id )


                ) tabelaUnion
                order by data;  
                ";
        }//end else do if(processaPorDataUtilizacao())
        //echo "<pre>".print_r($sql,true)."</pre>"; die();
        $rs = SQLexecuteQuery($sql);

        //Totalizados de vendas
        $valorTotalVendas = 0;
        $somaTotalEmDolares = 0;
        $valorPorDocumento = array();
        $listaIDPedidos = array();
        $listaNomes = array();
        $array_pedido_cpf = array();
        $msg_erro = "";
        if($rs && pg_num_rows($rs) > 0){
            $cpfs_black_list = $this->recupera_cpfs_black_list();
            if(!is_array($cpfs_black_list)){
                $this->setStatus(FALSE);
                //Houve algum problema ao executar a query que recupera os CPFs da Black List EPP
                $msg_erro = "<strong>ERRO 809</strong>: Houve um problema ao recuperar os CPFs contidos na <i>BLACK LIST</i> da E-Prepag<br>Entre em contato com o setor de T.I";
                $this->setMsgErro($msg_erro);
                
                return $this->getStatus();
                
            } else{
                
                $vetor_ids_venda = array();
                $vetor_contador = array();
                while($rs_row = pg_fetch_array($rs)){
                    $cpf = str_replace('.','',str_replace('-', '', $rs_row["ug_cpf"]));
                    $valorDolar = floatval($rs_row["valor_total"]/$cotacao_dolar);
                    
                    //MONTA O ARRAY COM AS OPERAÇÕES DA REMESSA (ENVIO VIA sFTP)
                    $req_sftp[] = array(
                                            "id_op"             => $rs_row["venda_id"].(in_array($rs_row["venda_id"], $vetor_ids_venda)?($vetor_contador[$rs_row["venda_id"]]+1):""), 
                                            "datacp"            => $rs_row["data"], 
                                            "valorme"           => $valorDolar, 
                                            "valormn"           => floatval($rs_row["valor_total"]), 
                                            "taxaop"            => $cotacao_dolar, 
                                            "payment_method"	=> BEXS_PAYMENT_METHOD, 
                                            "cliente"           => array(
                                                                            'natureza'      => (strlen($cpf)==11?BEXS_TIPO_NATUREZA_PF:BEXS_TIPO_NATUREZA_PJ), 
                                                                            'documento'     => $cpf,
                                                                            'datanasc'      => $rs_row["nascimento"],
                                                                            'nome'          => utf8_encode($rs_row["ug_nome"]),
                                                                            'logradouro'    => utf8_encode($rs_row["logradouro"]),
                                                                            'bairro'        => utf8_encode($rs_row["bairro"]),
                                                                            'cidade'        => utf8_encode($rs_row["cidade"]),
                                                                            'estado'        => $rs_row["estado"],
                                                                            'cep'           => str_replace('-','',$rs_row["cep"]),
                                                                            'email'         => $rs_row["email"],
                                                                            'telefone'      => $rs_row["telefone"],
                                                                            'verificacao'   => $rs_row["verificacao"]
                                                                        ),
                                            "merchant"          => array(
                                                                            'id'            => $this->getMerchantIdBexs()
                                                                        ),
                                            "pdv"               => array(
                                                                            'id'            => $rs_row['id_pdv']
                                                                        )                        
                                        );
                    $valorTotalVendas += floatval($rs_row['valor_total']); 

                    $valorPorDocumento[$cpf] +=  $valorDolar;
                    
                    $listaIDPedidos[$cpf] .= (isset($listaIDPedidos[$cpf])?", ":"").$GLOBALS['ARRAY_TIPO_VENDA_AUX'][substr($rs_row["venda_id"], 0, 2)] . ": ". substr($rs_row["venda_id"], 2);
                    
                    $listaNomes[$cpf] = $rs_row["ug_nome"];

                    $somaTotalEmDolares += $valorDolar;

                    $vetor_ids_venda[] = $rs_row["venda_id"];
                    
                    $vetor_contador[$rs_row["venda_id"]] = isset($vetor_contador[$rs_row["venda_id"]])?$vetor_contador[$rs_row["venda_id"]]+1:1; 

                    if(in_array(($cpf *1), $cpfs_black_list)){
                        $array_pedido_cpf[$rs_row["venda_id"]] = array(
                                                                        'CPF' => $cpf,
                                                                        'NOME' => utf8_encode($rs_row["ug_nome"])
                                                                      );
                    }

                }//end while($rs_row = pg_fetch_array($rs))
                
                $validate = $this->validate_fields_array($req_sftp);
                if($validate != ""){
                    $this->setStatus(FALSE);
                    $msg_erro .= $validate;
                }
                
                $verifiy_black_list = $this->verify_cpfs_black_list($array_pedido_cpf);
                if($verifiy_black_list != ""){
                    $this->setStatus(FALSE);
                    $msg_erro .= $verifiy_black_list;
                }

                //Aplicando porcentagem no coeficiente definido para o valor do dólar
                $coef_porcent = BEXS_COEFICIENTE_DOLAR/100;

                //Coeficiente máximo aceitável para a diferença entre o valor total em dólares($) enviado nas infos da remessa(web service) e a soma do valor total das operações(sFTP) 
                $coeficiente = $coef_porcent * $this->getValorMoedaEstrangeira();

                //Diferença em valor absoluto para casos em que o resultado é negativo
                $diferenca_valores_dolar = abs($this->getValorMoedaEstrangeira() - number_format($somaTotalEmDolares, 2, ".",""));

                if($diferenca_valores_dolar <= $coeficiente || $this->getIsReport()){
                    
                    $verifiy_value_cpf = $this->verify_value_each_cpf($valorPorDocumento, $listaNomes, $listaIDPedidos);
                    if($verifiy_value_cpf != ""){
                        if($msg_erro != "") $msg_erro .= "<br><br>";
                        $this->setStatus(FALSE);
                        $msg_erro .= $verifiy_value_cpf;
                    }

                } //end if($diferenca_valores_dolar <= $coeficiente || $this->getIsReport())
                else{
                    
                    $this->setStatus(FALSE);
                    
                    if($msg_erro != "") $msg_erro .= "<br><br>";
                    $msg_erro .= "<strong>ERRO 812</strong>: Diferença encontrada entre o valor total em dólar($) enviado nas informações da remessa e a soma total das operações!
                        <br><br>Valor total em dólar enviado nas Informações da Remessa: <strong>$".number_format($this->getValorMoedaEstrangeira(), 2, ",",".")."</strong><br>".
                        "Soma do valor total em dólar das Operações a serem enviadas: <strong>$". number_format($somaTotalEmDolares, 2, ",",".")."</strong><br>".
                        "Por favor, verifique!";

                }
                
                if(!$this->getStatus()){
                    $this->setMsgErro($msg_erro);
                    
                    if(!$this->getIsReport()){
                        $assunto = (checkIP()?"[DEV] ":"[PROD] ")."Problema(s) na geração do arquivo de operações BEXS";

                        $destino = (checkIP()) ? EMAIL_DEV : EMAILS_PROD;

                        enviaEmail($destino, null, null, $assunto, $this->getMsgErro());
                    }
                    
                    return FALSE;
                } else{
                    if(isset($req_sftp) && is_array($req_sftp)){
                        return $req_sftp;
                    }
                    return NULL;
                }
            }
                
        } //end if($rs && pg_num_rows($rs) > 0)
        else{
            $this->setStatus(FALSE);
            //Houve algum problema ao executar a query que recupera as transações no período especificado
            $msg_erro = "<strong>ERRO 813</strong>: Houve um problema ao recuperar as informações detalhadas das transações no período especificado<br>Entre em contato com o setor de T.I";
            $this->setMsgErro($msg_erro);

            return $this->getStatus();
        }
    }
    
    private function validate_fields_array($array){
        $cont_erros = 0;
        $errors_fields = array();
        $msg_erro = "";
        foreach ($array as $param){
            $this->validation($param, $cont_erros, $errors_fields);
        }

        if($cont_erros > 0){
            $msg_erro .= "<strong>ERRO 172</strong>: Alguns dados de clientes estão preenchidos de forma incorreta para envio do arquivo detalhado de operações.<br>Quantidade de campos com problemas: ".$cont_erros."<br><br>".$this->getErrors($errors_fields)."<br><br>";
        }
        return $msg_erro;
    }
    
    private function verify_cpfs_black_list($array_pedido_cpf){
        $msg_erro = "";
        if(count($array_pedido_cpf) > 0){
            $msg_erro .= "<strong>ERRO 810</strong>: Foram encontradas transações com CPFs que estão na <i>BLACK LIST</i> da E-Prepag.<br>Detalhes abaixo:<br>".
            "Quantidade de transações com problema: <strong>".count($array_pedido_cpf)."</strong><br><br>";
            foreach ($array_pedido_cpf as $pedido_black => $cpf_black){
                $identificador_tipo = substr($pedido_black, 0, 2);
                $identificador_venda = substr($pedido_black, 2);
                $msg_erro .= $GLOBALS['ARRAY_TIPO_VENDA_AUX'][$identificador_tipo]." Pedido ID [".$identificador_venda."] com o CPF [<strong>".mascara_cpf($cpf_black['CPF'])."</strong>] Nome: <strong>".$cpf_black['NOME']."</strong><br>";
            }
        }
        return $msg_erro;
    }
    
    private function verify_value_each_cpf($array_document, $array_nomes, $array_pedidos){
        $msg_erro = "";
        $arr_limite_dolar_ultrapassado = array();
        
        foreach ($array_document as $cpf => $valor){
            if($valor >= BEXS_LIMITE_DOLAR){
                $arr_limite_dolar_ultrapassado[] = "O CPF [<strong>".mascara_cpf($cpf)."</strong>] com o nome <strong>".$array_nomes[$cpf]."</strong> ultrapassou o limite de compras de $".BEXS_LIMITE_DOLAR. ".<br>Valor total em compras: $".number_format($valor, 2, ",",".")." no período.<br>Os IDs de Pedidos envolvidos são (".$array_pedidos[$cpf].").<br><br>";
            } 
        }
        
        if(count($arr_limite_dolar_ultrapassado) > 0){
            if($msg_erro != "") $msg_erro .= "<br><br>";
            $msg_erro .= "<strong>ERRO 811</strong>: O(s) CPF(s) a seguir ultrapassaram o limite de compras (em dólar) no período:<br>".
            "Quantidade de CPF(s) com problema: <strong>".count($arr_limite_dolar_ultrapassado)."</strong><br><br>";
            foreach ($arr_limite_dolar_ultrapassado as $msg){
                $msg_erro .= $msg;
            }

        } //end if(count($arr_limite_dolar_ultrapassado) > 0)
        
        return $msg_erro;
    }
    
    private function recupera_cpfs_black_list(){
        $array_cpfs = array();
        
        $sql = "SELECT cpf FROM cpf_black_list;";
        $rs = SQLexecuteQuery($sql);
        if($rs){
            if(pg_num_rows($rs) > 0){
                while($result = pg_fetch_array($rs)){
                    $array_cpfs[] = $result['cpf'];
                }
                return $array_cpfs;
            }
        }
        return false;
    }

    public function recupera_cotacao_dolar(){
        
        $currentmonthVerify = mktime(0, 0, 0, date('n')-1, 1, date('Y'));
        $sql = "select cd_cotacao from cotacao_dolar where opr_codigo = ".$this->getIdOperadora()." and cd_data = '".date('Y-m-d',$currentmonthVerify)." 00:00:00';";
        $rs_dolar = SQLexecuteQuery($sql);

        if($rs_dolar && pg_num_rows($rs_dolar) > 0) {
            $valor_dolar_aux = pg_fetch_array($rs_dolar);
            return floatval($valor_dolar_aux['cd_cotacao']);
        } else{
            return FALSE;
        }
    } 
    
    private function setControleInsertUpdate($acao, $status = NULL){
        
        $con = ConnectionPDO::getConnection();
        $pdo = $con->getLink();
        
        $id_arquivo = str_replace(".zip", "", $this->getnomeArquivoZip());
        
        if($acao == "insert"){
    
            $sql = "INSERT INTO remessa_bexs (id_arquivo, perfil_op, data_operacao, data_moeda, data_moeda_nacional, data_liquidacao, valor_moeda_nacional, status, data_ini, data_fim, valor_moeda_estrangeira,data_atualizacao) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW());";

            $params = array(
                            filter_var($id_arquivo,FILTER_SANITIZE_STRING),
                            filter_var($this->getPerfilOperacional(),FILTER_SANITIZE_STRING),
                            filter_var($this->getDataOperacao(),FILTER_SANITIZE_STRING),
                            filter_var($this->getDataMoedaEstrangeira(),FILTER_SANITIZE_STRING),
                            filter_var($this->getDataMoedaNacional(),FILTER_SANITIZE_STRING),
                            filter_var($this->getDataLiquidacao(),FILTER_SANITIZE_STRING),
                            filter_var($this->getValorMoedaNacional()),
                            filter_var($GLOBALS['ARRAY_STATUS']['REGISTRO_CRIADO'], FILTER_SANITIZE_NUMBER_INT),
                            filter_var($this->getDataIni(),FILTER_SANITIZE_STRING),
                            filter_var($this->getDataFim(),FILTER_SANITIZE_STRING),
                            filter_var($this->getValorMoedaEstrangeira())
                           );

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            if($stmt->rowCount() != 1){
                echo utf8_encode("<strong>ERRO 0104<strong>: Erro ao executar a <i>query</i> para inserção das informações da remessa no banco de dados.<br>Por favor, entre em contato com o setor de T.I.");
            }

        } elseif($acao == "update"){

            $sql_update = "UPDATE remessa_bexs set perfil_op = ?, data_operacao = ?, 
            data_moeda = ?, data_moeda_nacional = ?, data_liquidacao = ?, valor_moeda_nacional = ?, status = ?, 
            data_ini = ?, data_fim = ?, valor_moeda_estrangeira = ?, data_atualizacao = NOW() where id_arquivo = ? 
            AND status <> ".$GLOBALS['ARRAY_STATUS']['SUCESSO_WS']." AND status <> ".$GLOBALS['ARRAY_STATUS']['SUCESSO_SFTP']." 
            AND status <> ".$GLOBALS['ARRAY_STATUS']['SUCESSO_PROCESSAMENTO'].";";
            $params_update = array(
                                    filter_var($this->getPerfilOperacional(),FILTER_SANITIZE_STRING),
                                    filter_var($this->getDataOperacao(),FILTER_SANITIZE_STRING),
                                    filter_var($this->getDataMoedaEstrangeira(),FILTER_SANITIZE_STRING),
                                    filter_var($this->getDataMoedaNacional(),FILTER_SANITIZE_STRING),
                                    filter_var($this->getDataLiquidacao(),FILTER_SANITIZE_STRING),
                                    filter_var($this->getValorMoedaNacional()),
                                    filter_var($status, FILTER_SANITIZE_NUMBER_INT),
                                    filter_var($this->getDataIni(),FILTER_SANITIZE_STRING),
                                    filter_var($this->getDataFim(),FILTER_SANITIZE_STRING),
                                    filter_var($this->getValorMoedaEstrangeira()),
                                    filter_var($id_arquivo, FILTER_SANITIZE_STRING)
                                  );

            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute($params_update);

            if($stmt_update->rowCount() < 1){
                if($status != $GLOBALS['ARRAY_STATUS']['SUCESSO_WS'] && $status != $GLOBALS['ARRAY_STATUS']['SUCESSO_SFTP'] && $status != $GLOBALS['ARRAY_STATUS']['SUCESSO_PROCESSAMENTO']){
                    echo utf8_encode("<br><br><strong>ERRO 0112</strong>: Problema ao atualizar o status da transação no banco de dados!<br>Por favor, entre em contato com o setor de T.I.");
                }
            }
        }
    }
    
    private function callService($typeOfService = '', $requestParams = array(), $nomearq = NULL) {

		//Armazena na classe os dados do serviço informado
        $bexsRequestRecord = $this->getRequestObject($typeOfService, $requestParams);
        
        if($bexsRequestRecord){
            if($typeOfService == BEXS_XML_REQUISICAO_INFORMACOES_REMESSA){
                try{
                    
                    ini_set("soap.wsdl_cache_enabled", "0");
                    ini_set("soap.wsdl_cache_ttl", "0");
                    
                    $this->optionsArraySoapClient = array
                                                        (
                                                            'encoding'	=> 'UTF-8',
                                                            'trace'		=> 1,
                                                            'exceptions'	=> 1,
                                                           // 'connection_timeout' => 60,
                                                        );
                    
                    $this->soapClient = new SoapClient(BEXS_SERVICE_URL_WSDL, $this->optionsArraySoapClient);
                    $this->hash = $this->soapClient->geraHash();

                    $param = new SoapVar(array('token' => base64_encode(BEXS_WS_USER_EPREPAG).crypt(md5(BEXS_WS_PASSWD_EPREPAG),$this->hash)), SOAP_ENC_OBJECT);
                    
                    $header = new SoapHeader(BEXS_SERVICE_URL, 'AuthenticationInfo', $param, false);
                    $this->soapClient->__setSoapHeaders($header);

                } catch (SoapFault $e) {
                    $this->logEvents( "Caught exception 2A (".utf8_decode($e->faultcode)."): ". utf8_decode($e->getMessage()).PHP_EOL, BEXS_MSG_ERROR_LOG, 0);
                }

                if($this->soapClient) {
                    try {
                            //Convertendo Objeto em Array
                            $bexsRequestRecord = $this->object_to_array($bexsRequestRecord);

                            //Convertendo Array em XML e depois XML em String 
                            $bexsRequestRecord = str_replace('<?xml version="1.0"?>','',print_r($this->array_to_xml($bexsRequestRecord[0], new SimpleXMLElement('<xml/>'))->asXML(),true));
                            //Retirando as tag xml desnecessárias    
                            $bexsRequestRecord = preg_replace('/<\/?(?i)xml>/', '', $bexsRequestRecord);

                            //Resolvendo problema de estrutura de variável para passar como parametro em __soapCall
                            $bexsRequestRecord = array($bexsRequestRecord);

                            //Salvando no LOG variável antes de enviada
                            $this->logEvents("Antes do metodo __soapCall (Envio informacoes da remessa):".PHP_EOL.str_replace("><", ">".PHP_EOL."<", print_r($bexsRequestRecord,true)), BEXS_MSG_ERROR_LOG, 0);

                            //Retirando a tag remessa duplicada
                            $bexsRequestRecord = preg_replace('/<\/?(?i)remessa>/', '', $bexsRequestRecord);

                            $paramRemessa = new SoapParam($bexsRequestRecord[0], "remessa");

                            //Chamando o serviço            
                            $resultWS = $this->soapClient->__soapCall($typeOfService, array($paramRemessa));

                            $this->logEvents("<hr>SUCESSO".PHP_EOL."<pre>".htmlentities(str_replace("><", ">\n<", $this->getTransactionMessages()))."</pre>".PHP_EOL."<hr>", BEXS_MSG_ERROR_LOG, 0);

                            if ($resultWS instanceof SoapFault) {
                                $this->logEvents($this->getErrorMessages($resultWS), BEXS_MSG_ERROR_LOG, 0);	
                            } else {
                                //Capturando a resposta da consulta em vetor
                                $bexsResponseRecord = $this->getResponseObject($typeOfService, $resultWS);
                                return $bexsResponseRecord;
                            }

                    } catch (SoapFault $e) {
                        $this->logEvents("ERRO".PHP_EOL.htmlentities(str_replace("><", ">\n<", $this->getTransactionMessages())).PHP_EOL, BEXS_MSG_ERROR_LOG, 0);
                        $this->logEvents( "Caught exception 2B (".utf8_decode($e->faultcode)."): ". utf8_decode($e->getMessage()).PHP_EOL."MAX_EXECUTION_TIME : ".ini_get('max_execution_time').PHP_EOL."DEFAULT_SOCKET_TIMEOUT : ".ini_get('default_socket_timeout').PHP_EOL, BEXS_MSG_ERROR_LOG, 0);
                    }

                } else {
                    $this->logEvents( "Erro Interno 2C: soapClient não definido".PHP_EOL, BEXS_MSG_ERROR_LOG, 0);
                    return NULL;
                }
            }

            if($typeOfService == BEXS_XML_REQUISICAO_OPERACOES_REMESSA){
                
                foreach ($bexsRequestRecord as $val){
                    $arrayBexs[] = $this->object_to_array($val);
                }
                
                $sArq = "";
                
                $xml_data = new ExSimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><operacoes></operacoes>');

                for($i=0; $i < count($arrayBexs);$i++){
                    $this->array2xml($arrayBexs[$i][0],$xml_data);
                }
                
                // Tratando as quebras de linha do XML
                $caso_CDATA =  str_replace("><!", ">@", $xml_data->asXML());
                $caso_campo_vazio = str_replace("></", ">*", $caso_CDATA);
                $aux = str_replace("><", ">".PHP_EOL."<", $caso_campo_vazio).PHP_EOL;
                
                $xml_final = str_replace(">*", "></", $aux);
                
                $sArq .=  str_replace(">@", "><!", $xml_final);
                    
                $zip_file = $this->geraArquivoXML_Zip($sArq, $nomearq);
                
                $enviou = $this->sendXML_FTP($zip_file);

                return $enviou;

            }
            
        } else{
            $this->logEvents("Problema na formatacao dos dados para envio de informacoes da remessa - Detalhes enviados via e-mail".PHP_EOL, BEXS_MSG_ERROR_LOG, 0);
            return NULL;
        }     
	} //end function callService($typeOfService = '', $requestParams = array())
    
    private function Req_EfetuaTransmissao($requestParams, $typeOfService = '', $nomearq = NULL ) {
        
        $lista_resposta = null;
        if($typeOfService == BEXS_XML_REQUISICAO_INFORMACOES_REMESSA){
            $responseBEXS = $this->callService(BEXS_XML_REQUISICAO_INFORMACOES_REMESSA, $requestParams);
            if(is_null($responseBEXS)){
                return FALSE;
            } else{
                $this->logEvents("Resposta do envio das informações da remessa [".$requestParams['id_arquivo']."]:".PHP_EOL."Taxa de nivelamento: ".print_r($responseBEXS,true).PHP_EOL,BEXS_MSG_ERROR_LOG,0);

                return $responseBEXS;
            }
        }
        
        if($typeOfService == BEXS_XML_REQUISICAO_OPERACOES_REMESSA){
            $enviouBEXS = $this->callService(BEXS_XML_REQUISICAO_OPERACOES_REMESSA, $requestParams, $nomearq);
            if(is_null($enviouBEXS)){
                return FALSE;
            } else{
                
                if(is_array($enviouBEXS)){
                    if(isset($enviouBEXS['concluido']) && strpos($enviouBEXS['concluido'], "100") != FALSE){
                        $this->logEvents("O envio do arquivo de operações da remessa foi realizado com sucesso! Detalhes abaixo:".PHP_EOL.PHP_EOL.print_r($enviouBEXS,true).PHP_EOL,BEXS_MSG_ERROR_LOG,0);
                    }
                }
                
                return $enviouBEXS;
            }
        }
     
	}//end function Req_EfetuaTransmissao($requestParams,&$lista_resposta)
    
    // General methods request
	private function getRequestObject($typeOfService = '', $requestParams = array()) {
		
		if ($typeOfService == BEXS_XML_REQUISICAO_INFORMACOES_REMESSA) {
            $verifica = TRUE;
            $serialCheck = new informacoesRemessa();
            if(is_array($serialCheck->getRequestData($requestParams))){
                $serialCheckRequestObj = $serialCheck->getRequestData($requestParams);
            } else{
                $verifica = FALSE;
            }
            if($verifica){
                return $serialCheckRequestObj;
            } else{
                return FALSE;
            }
                      
		}//end if ($typeOfService == BEXS_XML_REQUISICAO_INFORMACOES_REMESSA) 
        
        if($typeOfService == BEXS_XML_REQUISICAO_OPERACOES_REMESSA){

            $serialCheck = new operacoesRemessa();
            $serialCheckRequestObj[] = $serialCheck->getRequestData($requestParams);

            return $serialCheckRequestObj;
            
        }//end if($typeOfService == BEXS_XML_REQUISICAO_OPERACOES_REMESSA)
		
	}//end 	function getRequestObject($typeOfService = '', $requestParams = array())

	// General method Response
	private function getResponseObject($typeOfService = '', $soapResponseData) {			

        if ($typeOfService == BEXS_XML_REQUISICAO_INFORMACOES_REMESSA) {
            $serialCheck = new informacoesRemessa();
            $serialCheckResponseObj = $serialCheck->getResponseData($soapResponseData);
            return $serialCheckResponseObj;
                        
        } //end if ($typeOfService == BEXS_XML_REQUISICAO_INFORMACOES_REMESSA)
        
	}//end function getResponseObject($typeOfService = '', $soapResponseData)
    
    public function sendXML_FTP($arquivo){
        
        $arq = trim(str_replace('/', '\\', $arquivo));

        //---------------------------------------------------------------------------------------------//
        //    Não esquecer de alterar a senha, usuário, url e (FTP -> sFTP) quando o BEXS nos passar   // 
        //    MODELO: ftp://usuario:senha@url                                                          //
        //---------------------------------------------------------------------------------------------//

        $hostkey = (checkIP()?"1024 b4:f4:86:20:8f:96:ce:df:38:2e:d4:57:1c:6f:92:48":"2048 9e:7b:b9:3d:52:91:48:55:4e:07:af:69:0b:88:f0:aa");
        $linhaExecucao = "\"C:\\Program Files\\WinSCP\\winSCP.com\" /log=\"log_winscp.log\" /ini=nul /command \"option confirm off\" \"option batch continue\" \"open sftp://".BEXS_SFTP_USER_EPREPAG.":".BEXS_SFTP_PASSWD_EPREPAG."@179.191.88.213/ -hostkey=\"\"ssh-rsa ".$hostkey."\"\" -timeout=60\" \"put ".$arq." /eprepag/ \" \"exit\"";
        
        $scriptBat = PATH_OPERACOES_BEXS.'script_bexs.bat';
        
        $script = fopen($scriptBat,"w+");
        fwrite($script, $linhaExecucao);
        fclose($script);

        exec('start /b '.$scriptBat, $output);

        $access_error = FALSE;
        if(is_array($output)){
            
            foreach ($output as $key => $value){
                if(strpos($value, "100%") !== FALSE){
                    $key_success = $key;
                }
                if(strpos($value, "Access denied.") !== FALSE){
                    $access_error = TRUE;
                }
            }

            if($access_error){
                
                $this->logEvents("Problema na autenticação de usuário para transferência de arquivo via sFTP! Host, usuário ou senha incorretos".PHP_EOL, BEXS_MSG_ERROR_LOG, 0);
                return "Problema na autenticação de usuário para transferência de arquivo via sFTP! Host, usuário ou senha incorretos, por favor, verifique!";
                
            } else{
                
                if(isset($key_success)){
                    
                    $array_success_send = explode('|', trim($output[$key_success]));

                    return array("arq_enviado" => $array_success_send[0], "tamanho" => $array_success_send[1], "concluido" => $array_success_send[4]);
                } else{
                    
                    $this->logEvents("Houve algum problema na tentativa de transferência do arquivo via sFTP! Detalhes retornados pelo WinSCP abaixo:".PHP_EOL.PHP_EOL.print_r($output,true).PHP_EOL,BEXS_MSG_ERROR_LOG,0);
                    return "Problema na tentativa de transferência do arquivo via sFTP! Por favor, verifique!";
                }
            }
            
        } //end if(is_array($output))
        else{
            
            $this->logEvents("Problema na execução do script de conexão WinSCP para envio de arquivo via sFTP!".PHP_EOL, BEXS_MSG_ERROR_LOG, 0);
            return "Problema na execução do script de conexão WinSCP para envio de arquivo via sFTP! Por favor, verifique!";
        }

    } //end function sendXML_FTP($arquivo)
    
    private function geraArquivoXML_Zip($content, $file){
        //Criando o nome do arquivo XML que será zipado
        $file = str_replace(".zip", ".xml", $file);
        
        if(is_dir(PATH_OPERACOES_BEXS)){
            $varArquivo = PATH_OPERACOES_BEXS.$file;
        } else{
            echo utf8_encode("<strong>ERRO 177</strong>: Diretório inexistente!<br>Problema ao abrir diretório que contém arquivo de operações.<br>Por favor, entre em contato com o setor de T.I.");
            die();
        }
        
        $zip_file = $this->getnomeArquivoZip();
        
        //Criação de arquivo xml para envio das operações da remessa
        $handle = fopen($varArquivo, "w+");
        //Escrevendo no arquivo
        fwrite($handle, $content);
        //Fechamento do arquivo
        fclose($handle);
        
        //Zipando o arquivo xml gerado anteriormente
        $zip = new ZipArchive();
        if($zip->open(PATH_OPERACOES_BEXS.$zip_file, ZIPARCHIVE::OVERWRITE) !== TRUE){
            
            return NULL;                   
            
        } else{
            //Adicionado arquivo xml ao arquivo .zip
            if (file_exists($varArquivo)) {
                $zip->addFile($varArquivo, $file);
            }
            $path_zip = PATH_OPERACOES_BEXS.$zip_file;
            $zip->close();
        }
        
        if(isset($path_zip)){
            return $path_zip;
        } else{
            return NULL;
        }

    } //end function geraArquivoXML_Zip($content)
  
    public function getTransactionMessages() {

		if($this->soapClient) {
			$requestMsg        = htmlspecialchars_decode($this->soapClient->__getLastRequest());
			$requestHeaderMsg  = htmlspecialchars_decode($this->soapClient->__getLastRequestHeaders());
			$responseMsg       = htmlspecialchars_decode($this->soapClient->__getLastResponse());
			$responseHeaderMsg = htmlspecialchars_decode($this->soapClient->__getLastResponseHeaders());
			
			$msg  = "";
			$msg .= "--------------------------".PHP_EOL;
			$msg .= "Request :".PHP_EOL.PHP_EOL.$requestMsg.PHP_EOL;
			$msg .= "--------------------------".PHP_EOL;
			$msg .= "RequestHeaders:".PHP_EOL.PHP_EOL.$requestHeaderMsg;
			$msg .= "--------------------------".PHP_EOL;
			$msg .= "Response:".PHP_EOL.PHP_EOL.$responseMsg.PHP_EOL.PHP_EOL;
			$msg .= "--------------------------".PHP_EOL;
			$msg .= "ResponseHeaders:".PHP_EOL.PHP_EOL.$responseHeaderMsg.PHP_EOL.PHP_EOL;
		} else {
			$msg = "Erro Interno A: soapClient não definido";
		}
		return $msg;		
	}//end function getTransactionMessages()
    
    public function getErrorMessages($resultWS, $isSoapFault = true) {
		
		if ($isSoapFault) {
			$msg .= "Message : ".$resultWS->getMessage().PHP_EOL;
			$msg .= "--------------------------".PHP_EOL;
			$msg .= "TraceString: ".$resultWS->getTraceAsString().PHP_EOL;
			$msg .= "--------------------------".PHP_EOL;
			$msg .= "Code: ".$resultWS->getCode().PHP_EOL;
			$msg .= "--------------------------".PHP_EOL;
			$msg .= "File: ".$resultWS->getFile().PHP_EOL;
			$msg .= "--------------------------".PHP_EOL;
			$msg .= "Line: ".$resultWS->getLine().PHP_EOL;
			$msg .= "--------------------------".PHP_EOL;
			$msg .= "FaultCode: ".$resultWS->faultcode.PHP_EOL;
			$msg .= "--------------------------".PHP_EOL;
			$msg .= "Detail: ".$resultWS->detail.PHP_EOL.PHP_EOL.PHP_EOL;
			$msg .= $this->getTransactionMessages();
		} else {
			$msg .= $this->getTransactionMessages();				
		}
		
		return $msg;
	} //end function getErrorMessages($resultWS, $isSoapFault = true)  
    
    public function logEvents($msg, $tipoLog = 'ERROR_LOG') {
			
		if($tipoLog == BEXS_MSG_ERROR_LOG) 
			$fileLog = LOG_FILE_BEXS_WS_ERRORS;		
		else if($tipoLog == BEXS_MSG_TRANSACTION_LOG) 
			$fileLog = LOG_FILE_BEXS_WS_TRANSACTIONS;
		
		$log  = "=================================================================================================".PHP_EOL;
		$log .= "DATA -> ".date("d/m/Y - H:i:s").PHP_EOL;
		$log .= "---------------------------------".PHP_EOL;
		$log .= htmlspecialchars_decode($msg);			
						
		$fp = fopen($fileLog, 'a+');
		fwrite($fp, $log);
		fclose($fp);		
	}//end function logEvents($msg, $tipoLog = 'ERROR_LOG')
    
    public function array_to_xml(array $arr, SimpleXMLElement $xml) {
        foreach ($arr as $k => $v) {
            if(is_array($v))
                $this->array_to_xml($v, $xml->addChild($k));
            else $xml->addChild($k, $v);
        }
        return $xml;
    }//end function array_to_xml(array $arr, SimpleXMLElement $xml)
    
    public function array2xml($data, &$xml_data) {
        foreach( $data as $key => $value ) {
            if( is_numeric($key) ){
                $key = 'item'.$key; //dealing with <0/>..<n/> issues
            }
            if( is_array($value) ) {
                $subnode = $xml_data->addChild($key);
                $this->array2xml($value, $subnode);
            } else {
                if($key == "nome" || $key == "pais" || $key == "email"){
                    $xml_data->addChildWithCDATA("$key",htmlspecialchars("$value"));
                } else{
                    $xml_data->addChild("$key",htmlspecialchars("$value"));
                }
                
            }
         }
    }//end function array2xml($data, &$xml_data)

    public function object_to_array($obj) {
        if(is_object($obj)) $obj = (array) $obj;
        if(is_array($obj)) {
            $new = array();
            foreach($obj as $key => $val) {
                $new[$key] = $this->object_to_array($val);
            }
        }
        else $new = $obj;
        return $new;       
    } //end function object_to_array($obj)
    
    public function xml2array($xmlObject, $out = array ()) {
        foreach ( (array)$xmlObject as $index => $node ) {
            $out[$index] = (is_object($node)) ? self::xml2array($node) : $node;
        }
        return $out;
    } //end function xml2array( $xmlObject, $out = array () )     
    
    public function checkTypeSize($var, $type, $min, $max) {
        
        switch (strtoupper($type)) {
            case "TEXTO":
                if(preg_match('/^[A-Za-zÀ-ú0-9\x21-\xBAü\s]+$/', $var)) {
                    if(strlen($var) >= $min && strlen($var) <= $max){
                        return true;
                    }
                    else return false;
                }
                else return false;
                break;
                
            case "NUMERO":
                if(preg_match('/^[0-9]+$/u', $var)){
                    if(strlen($var) >= $min && strlen($var) <= $max){
                        return true;
                    }
                    else return false;
                }
                else return false;
                break;
                
            case "DATA":
                if(preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', $var)){
                    if(strlen($var) >= $min && strlen($var) <= $max){
                        return true;
                    }
                    else return false;
                }
                else return false;
                break;
                
            case "TEXTO_ESP":
                if(preg_match('/^[A-Za-z0-9]+$/', $var)) {
                    if(strlen($var) >= $min && strlen($var) <= $max){
                        return true;
                    }
                    else return false;
                }
                else return false;
                break;
        }     
    } //end function checkTypeSize($var, $type, $min, $max)
    
    private function validation_ws($params, &$errors = array()){
        
        $validou = TRUE;
        
        if(isset($params['perfil_op'])){
            if (!$this->checkTypeSize($params['perfil_op'], "NUMERO", 1, 11)) {
                $errors[] = "Problema no perfil_op<br>Valor Inserido: [".$params['perfil_op'] . "]<br>Tamanho do campo: 11 caracteres(somente numeros)";
                $validou = FALSE;
            }
        } else{
            $errors[] = "Campo 'perfil_op' é OBRIGATÓRIO!";
            $validou = FALSE;
        }
        
        if(isset($params['tipoop'])){
            if (!$this->checkTypeSize($params['tipoop'], "NUMERO", 1, 1)) {
                $errors[] = "Problema no tipoop<br>Valor Inserido: [".$params['tipoop'] . "]<br>Tamanho do campo: 1 caractere(somente numeros)";
                $validou = FALSE;
            }
        } else{
            $errors[] = "Campo 'tipoop' é OBRIGATÓRIO!";
            $validou = FALSE;
        }
        
        if(isset($params['moeda'])){
            if (!$this->checkTypeSize($params['moeda'], "TEXTO", 1, 3)) {
                $errors[] = "Problema no moeda<br>Valor Inserido: [".$params['moeda'] . "]<br>Tamanho do campo: 3 caracteres(somente numeros)";
                $validou = FALSE;
            }
        } else{
            $errors[] = "Campo 'moeda' é OBRIGATÓRIO!";
            $validou = FALSE;
        }
        
        if(isset($params['data_me'])){
            if (!$this->checkTypeSize($params['data_me'], "DATA", 10, 10)) {
                $errors[] = "Problema na <strong>data da moeda estrangeira</strong><br>Valor Inserido: [".$params['data_me'] ."]<br>Tamanho do campo: 10 caracteres [AAAA-MM-DD]<br><br>";
                $validou = FALSE;
            }
        } else{
            $errors[] = "Campo 'data_me' é OBRIGATÓRIO!";
            $validou = FALSE;
        }
        
        if(isset($params['data_mn'])){
            if (!$this->checkTypeSize($params['data_me'], "DATA", 10, 10)) {
                $errors[] = "Problema na <strong>data da moeda nacional</strong><br>Valor Inserido: [".$params['data_mn'] ."]<br>Tamanho do campo: 10 caracteres [AAAA-MM-DD]<br><br>";
                $validou = FALSE;
            }
        } else{
            $errors[] = "Campo 'data_mn' é OBRIGATÓRIO!";
            $validou = FALSE;
        }
        
        if(isset($params['data_lq'])){
            if (!$this->checkTypeSize($params['data_lq'], "DATA", 10, 10)) {
                $errors[] = "Problema na <strong>data da liquidacao</strong><br>Valor Inserido: [".$params['data_lq'] ."]<br>Tamanho do campo: 10 caracteres [AAAA-MM-DD]<br><br>";
                $validou = FALSE;
            }
        } else{
            $errors[] = "Campo 'data_lq' é OBRIGATÓRIO!";
            $validou = FALSE;
        }
        
        if(isset($params['data_op'])){
            if (!$this->checkTypeSize($params['data_op'], "DATA", 10, 10)) {
                $errors[] = "Problema na <strong>data da operacao</strong><br>Valor Inserido: [".$params['data_op'] ."]<br>Tamanho do campo: 10 caracteres [AAAA-MM-DD]<br><br>";
                $validou = FALSE;
            }
        } else{
            $errors[] = "Campo 'data_operacao' é OBRIGATÓRIO!";
            $validou = FALSE;
        }
        
        return $validou;
    }
    
    private function validation($params, &$count_errors, &$errors = array()) {
        
        $informacoes_transacao = "Nome/Documento Cliente: <strong>" .(($params['cliente']['documento'] != "") ? $params['cliente']['documento']:$params['cliente']['nome'])." - ".(($params['pdv']['id'] != "9999999")?"PDV":"GAMER")."</strong><br>Id Venda: ".substr($params['id_op'], 2)."<br>";

        if(isset($params['payment_method'])){
            if (!$this->checkTypeSize($params['payment_method'], "TEXTO", 1, 3)) {
                $errors[] = "Problema no <strong>payment_method</strong><br>".$informacoes_transacao."Valor Inserido: [".$params['payment_method'] . "]<br>Tamanho do campo: 3 caracteres<br><br>";
                $count_errors++;
            }
        } else{
            $errors[] = "Campo 'payment_method' é OBRIGATÓRIO!";
            $count_errors++;
        }
        
        if(isset($params['id_op'])){
            if (!$this->checkTypeSize($params['id_op'], "NUMERO", 1, 40)) {
                $errors[] = "Problema no <strong>id_op</strong><br>".$informacoes_transacao."Valor Inserido: [".$params['id_op'] . "]<br>Tamanho do campo: 40 caracteres(somente numeros)<br><br>";
                $count_errors++;
            }
        } else{
            $errors[] = "Campo 'id_op' é OBRIGATÓRIO!";
            $count_errors++;
        }
        
        if(isset($params['cliente']['natureza'])){
            if (!$this->checkTypeSize($params['cliente']['natureza'], "TEXTO", 1, 2)) {
                $errors[] = "Problema no <strong>natureza</strong><br>".$informacoes_transacao."Valor Inserido: [".$params['cliente']['natureza'] . "]<br>Tamanho do campo: 1 a 2 caracteres<br><br>";
                $count_errors++;
            }
        } else{
            $errors[] = "Campo 'natureza' é OBRIGATÓRIO!";
            $count_errors++;
        }
        
        if(isset($params['cliente']['documento'])){
            if (!$this->checkTypeSize($params['cliente']['documento'], "TEXTO", 11, 14)) {
                $errors[] = "Problema no <strong>documento</strong><br>".$informacoes_transacao."Valor Inserido: [".$params['cliente']['documento'] . "]<br>Tamanho do campo: 11 a 14 caracteres<br><br>";
                $count_errors++;
            }
        } else{
            $errors[] = "Campo 'documento' é OBRIGATÓRIO!";
            $count_errors++;
        }
        
        if(isset($params['cliente']['datanasc']) || $params['cliente']['datanasc'] == NULL){
            if (!$this->checkTypeSize($params['cliente']['datanasc'], "DATA", 10, 10)) {
                $errors[] = "Problema na <strong>data de nascimento</strong><br>".$informacoes_transacao."Valor Inserido: [".$params['cliente']['datanasc'] . "]<br>Tamanho do campo: 10 caracteres<br><br>";
                $count_errors++;
            }
        } else{
            $errors[] = "Campo 'datanasc' é OBRIGATÓRIO!";
            $count_errors++;
        }
        
        if(isset($params['cliente']['nome'])){
            if (!$this->checkTypeSize($params['cliente']['nome'], "TEXTO", 1, 255)) {
                $errors[] = "Problema no <strong>nome</strong><br>".$informacoes_transacao."Valor Inserido: [".$params['cliente']['nome'] . "]<br>Tamanho do campo: 1 a 255 caracteres<br><br>";
                $count_errors++;
            }
        } else{
            $errors[] = "Campo 'nome' é OBRIGATÓRIO!";
            $count_errors++;
        }
        
        if(isset($params['cliente']['verificacao']) || ($params['cliente']['verificacao']) == NULL){
            if (!$this->checkTypeSize($params['cliente']['verificacao'], "DATA", 10, 10)) {
                $errors[] = "Problema na <strong>data de verificação do documento</strong><br>".$informacoes_transacao."Valor Inserido: [".$params['cliente']['verificacao'] . "]<br>Tamanho do campo: 10 caracteres<br><br>";
                $count_errors++;
            }
        } else{
            $errors[] = "Campo 'verificacao' é OBRIGATÓRIO!";
            $count_errors++;
        }
        
        if(isset($params['merchant']['id'])){
            if (!$this->checkTypeSize($params['merchant']['id'], "NUMERO", 1, 20)) {
                $errors[] = "Problema no id merchant<br>id_op: " .$params['id_op']."<br>Valor Inserido: [".$params['merchant']['id'] . "]<br>Tamanho do campo: 1 a 20 caracteres<br><br>";
                $count_errors++;
            }
        } else{
            $errors[] = "Campo 'id' de merchant é OBRIGATÓRIO!";
            $count_errors++;
        }
        
        unset($informacoes_transacao);
        
    } //end function validation($params, &$errors = array())
    
    public function getErrors($errors){
        $msg = "";
        foreach($errors as $er){
            $msg .=  "ERRO: " .$er . "<br>"; 
        }
        return $msg;
        
    } //end function getErrors($errors)
}