<?php
/**
 * verificaCPF
 * 
 * Classe que vai tratar os eventos de Request/Response da ActionName CPF_XML_REQUISICAO da Integracao com CPF 
 * 
 * @author Wagner de Miranda
 *
*/
class verificaCPF {
	public $acesso;		// string
	public $consulta;	// string
	
	public function getRequestData($requestData) {		
		$this->acesso	= new dadosAcesso();
		$this->consulta	= new dadosConsulta($requestData);

		return array($this);		 
	}
	
	public function getResponseData($soapResponseData) {
                //necessario para CREDIFY
                $soapResponseData = simplexml_load_string(utf8_decode($soapResponseData));
                //echo "----[<pre>".print_r($soapResponseData,true)."</pre>]----";
		$serialsaleRecord = array();
		$serialsaleRecord['consulta']['codigoresposta']			= (int) $soapResponseData->CONSULTA->CODIGORESPOSTA;
		$serialsaleRecord['consulta']['datahora']			= (string) $soapResponseData->CONSULTA->DATAHORA;
		$serialsaleRecord['consulta']['logon']				= (string) $soapResponseData->CONSULTA->LOGON;
		$serialsaleRecord['consulta']['idconsulta']			= (int) $soapResponseData->CONSULTA->IDCONSULTA;
		$serialsaleRecord['resposta']['codigo']				= (int) $soapResponseData->RESPOSTA->CODIGO;
                if($serialsaleRecord['resposta']['codigo'] == 0)	{
			$serialsaleRecord['resposta']['cpf']['cpf']			= (string) $soapResponseData->RESPOSTA->CPF->CPF;
			$serialsaleRecord['resposta']['cpf']['nome']			= (string) $soapResponseData->RESPOSTA->CPF->NOME;
			$serialsaleRecord['resposta']['cpf']['situacao']		= (string) $soapResponseData->RESPOSTA->CPF->SITUACAO;
			$serialsaleRecord['resposta']['cpf']['data']			= (string) $soapResponseData->RESPOSTA->CPF->DATA;
			$serialsaleRecord['resposta']['cpf']['hora']			= (string) $soapResponseData->RESPOSTA->CPF->HORA;
			$serialsaleRecord['resposta']['cpf']['chave']			= (string) $soapResponseData->RESPOSTA->CPF->CHAVE;
			$serialsaleRecord['resposta']['cpf']['digito_verificador']	= (string) $soapResponseData->RESPOSTA->CPF->DIGITO_VERIFICADOR;
                } //end if(!empty($soapResponseData->resposta->codigo))	
                
                else{
			$serialsaleRecord['resposta']['cpf']['msg']			= (string) $soapResponseData->RESPOSTA->CPF->MSG;
                } //end else do if(!empty($soapResponseData->resposta->codigo))	
                
		return $serialsaleRecord;
	}
	
}//end class verificaCPF

class verificaCPF_OMNIDATA {
	public $fonte;		// string
	public $parametros;	// string
        public $timeout;        //int
        public $validade;       //int
	
	public function getRequestData($requestData) {		
		$this->fonte		= CPF_ID_CONSULT;
                if(is_null($requestData['data_nascimento'])) {
                    $this->parametros	= array(
                                                new dadosParametros(CPF_NOME_CAMPO      ,($requestData['cpfcnpj']?$requestData['cpfcnpj']:'null'))
                                                );
                } //end if(empty($requestData['data_nascimento']))
                else {
                    $this->parametros	= array(
                                                new dadosParametros(CPF_NOME_CAMPO      ,($requestData['cpfcnpj']?$requestData['cpfcnpj']:'null')),
                                                new dadosParametros(DATA_NASC_NOME_CAMPO,($requestData['data_nascimento']?$requestData['data_nascimento']:'null'))
                                                );
                }//end else do if(empty($requestData['data_nascimento']))
		
		$this->timeout		= CPF_TIMEOUT;
		$this->validade		= CPF_VALIDADE;

		return array($this);		 
	}
	
	public function getResponseData($soapResponseData) {
                //echo "----[<pre>".print_r($soapResponseData,true)."</pre>]----";
		$serialsaleRecord = array();
		$serialsaleRecord['id']			= (int) $soapResponseData->return->id;
		$serialsaleRecord['timeout']		= (int) $soapResponseData->return->timeout;
		$serialsaleRecord['tempoVida']		= (int) $soapResponseData->return->tempoVida;
		$serialsaleRecord['status']		= (int) $soapResponseData->return->status;
		$serialsaleRecord['percentual']		= (int) $soapResponseData->return->percentual;
		$serialsaleRecord['prioridade']		= (int) $soapResponseData->return->prioridade;
		$serialsaleRecord['tempoConclusao']	= (int) $soapResponseData->return->tempoConclusao;
		$serialsaleRecord['dataHora']		= (string) $soapResponseData->return->dataHora;
		$serialsaleRecord['login']		= (string) $soapResponseData->return->login;
		$serialsaleRecord['tipo']		= (string) $soapResponseData->return->tipo;
                $serialsaleRecord['pesquisas']['status']= (int) $soapResponseData->return->pesquisas->status;
		if($serialsaleRecord['pesquisas']['status'] == 3)	{
			$serialsaleRecord['pesquisas']['id']				= (string) $soapResponseData->return->pesquisas->id;
			$serialsaleRecord['pesquisas']['nome']				= (string) $soapResponseData->return->pesquisas->nome;
			$serialsaleRecord['pesquisas']['fonte']				= (string) $soapResponseData->return->pesquisas->fonte;
			$serialsaleRecord['pesquisas']['validade']			= (string) $soapResponseData->return->pesquisas->validade;
			$serialsaleRecord['pesquisas']['mensagemStatus']		= (string) $soapResponseData->return->pesquisas->mensagemStatus;
			$serialsaleRecord['pesquisas']['dataHora']			= (string) $soapResponseData->return->pesquisas->dataHora;
			$serialsaleRecord['pesquisas']['foiUtilizadoCache']		= (string) $soapResponseData->return->pesquisas->foiUtilizadoCache;
			$serialsaleRecord['pesquisas']['permiteWIMDB']			= (string) $soapResponseData->return->pesquisas->permiteWIMDB;
			$serialsaleRecord['pesquisas']['tempoPesquisa']			= (string) $soapResponseData->return->pesquisas->tempoPesquisa;
                        // Válido somente quando existe somente um campo de entrada
			$serialsaleRecord['pesquisas']['camposEntrada']['nomeCampo']	= (string)$soapResponseData->return->pesquisas->camposEntrada[0]->nomeCampo;
			$serialsaleRecord['pesquisas']['camposEntrada']['valorCampo']	= (string) $soapResponseData->return->pesquisas->camposEntrada[0]->valorCampo;
                        foreach($soapResponseData as $retorno => $xmlRetorno) {
                                // Válido somente quando existe mais de um campo de entrada
                                //foreach($xmlRetorno->pesquisas->camposEntrada as $camposEntrada => $xmlSolicitacao) {
                                //        $serialsaleRecord['pesquisas']['camposEntrada'][(string) $xmlSolicitacao->nomeCampo] = (string) $xmlSolicitacao->valorCampo;
                                //} //end foreach($xmlRetorno->pesquisas->camposEntrada as $camposEntrada => $xmlSolicitacao)
                                
                                //Capturando os campos de Resposta
                                foreach($xmlRetorno->pesquisas->camposResposta as $camposResposta => $xmlResposta) {
                                        $serialsaleRecord['pesquisas']['camposResposta'][(string) $xmlResposta->nomeCampo] = (string) $xmlResposta->valorCampo;
                                } //end foreach($xmlRetorno->pesquisas->camposResposta as $camposResposta => $xmlResposta)
                        } //end foreach($soapResponseData as $retorno => $xmlRetorno)
                        
                } //end if(!empty($soapResponseData->resposta->codigo))	
                
                else{
			$serialsaleRecord['pesquisas']['msg']			= (string) $soapResponseData->return->pesquisas->mensagemStatus;
                } //end else do if(!empty($soapResponseData->resposta->codigo))	

                //echo "serialsaleRecord[<pre>".print_r($serialsaleRecord,true)."</pre>]";
				
			$file = fopen("/www/log/retorno_cpf.txt", "a+");
			fwrite($file, "DATA ".date("d-m-Y H:i:s")."\n");
			fwrite($file, "retorno class verificaCPF = PASSO 2 ".json_encode($serialsaleRecord)."\n");
			fwrite($file, str_repeat("*", 50)."\n"); 
			fclose($file);
                
		return $serialsaleRecord;
	}
	
}//end class verificaCPF

?>