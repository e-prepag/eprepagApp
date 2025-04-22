<?php
// error_reporting(E_ALL); 
// ini_set("display_errors", 1); 

@define('DELIMITER', '');	// '^#'
@define('DELIMITER2', '');	// ':~'


	//Variavel que armazena a mensagem de erro da função gera_rps.
	$varMsg = "";
	
	//Resumo das condições para gerar a linha de cabeçalho, rodapé e RPS.
	//==============================================================================
		//Registro Tipo 1 - Cabeçalho
		//Campos: TIPO DE REGISTRO(1) - VERSÃO DO LAYOUT(3) - INSCRIÇÃO MUNICIPAL(8) - DATA INICIAL(8) - DATA FINAL(8) - CARACTERE DE FIM DE LINHA
		//Posição Inicial: 1
		//Posição Final: 30
		
		//Registro Tipo 2 - Detalhe
		//Campos: TIPO DE REGISTRO(1) - TIPO DO RPS(5) - SÉRIE DO RPS(5) - NÚMERO DO RPS(12) - DATA DE EMISSÃO DO RPS(8) - SITUAÇÃO DO RPS(1) - VALOR DOS SERVIÇOS(15) - VALOR DAS DEDUÇÕES(15) - CÓDIGO DO SERVIÇO PRESTADO(5) - ALIQUOTA(4) - ISS RETIDO(1) - INDICADOR(1) - CPF/CNPJ(11/14) - IM(8) - IE(12) - NOME/RAZAO(75) - TIPO END(3) - ENDERECO(50) - NUMERO(10) - COMPLEMENTO(30) - BAIRRO(30) - CIDADE(50) - UF(2) - CEP(8) - EMAIL(75) - DISCRIMINAÇÃO - CARACTERE DE FIM DE LINHA
		//Posição Inicial: 1
		//Posição Final: 442
		
		//Registro Tipo 3 - Detalhe - Cupons
		//Campos: TIPO DE REGISTRO(1) - TIPO DO RPS(5) - SÉRIE DO RPS(5) - NÚMERO DO RPS(12) - DATA DE EMISSÃO DO RPS(8) - SITUAÇÃO DO RPS(1) - VALOR DOS SERVIÇOS(15) - VALOR DAS DEDUÇÕES(15) - CÓDIGO DO SERVIÇO PRESTADO(5) - ALIQUOTA(4) - ISS RETIDO(1) - INDICADOR(1) - CPF/CNPJ(11/14) - DISCRIMINAÇÃO - CARACTERE DE FIM DE LINHA
		//Posição Inicial: 1
		//Posição Final: 89
		
		//Registro Tipo 9 - Rodapé
		//Campos: TIPO DE REGISTROO(1) - LINHAS DE DETALHE(7) - VALOR TOTAL(15) - VALOR TOTAL DEDUÇÕES(15) - CARACTERE DE FIM DE LINHA
		//Posição Inicial: 1
		//Posição Final: 30
		
		//LEGENDAS
		//RPS - RECIBO PROVISÓRIO DE SERVIÇOS
		//RPS-M - RECIBO PROVISÓRIO DE SERVIÇOS PROVENIENTE DE NOTA FISCAL CONJUGADA MISTA
		//RPS-C - CUPONS
		
		//T - TRIBUTAÇÃO NORMAL
		//I - OPERAÇÃO ISENTA OU NÃO TRIBUTÁVEL
		//F - OPERAÇÃO ISENTA OU NÃO TRIBUTÁVEL MAIS EXECUTADA EM OUTRO MUNÍCIPIO
		//C - CANCELADO
		//E - EXTRAVIADO
		//J - ISS SUSPENSO POR DECISÃO JUDICIAL
		
		//1 - ISS RETIDO
		//2 - SEM ISS RETIDO
		
		//1 - CPF
		//2 - CNPJ
		//2 - CNPJ NÃO INFORMADO
	//==============================================================================
	
	
	
	//Função principal do arquivo.
	//==============================================================================
	function gera_lote($varTipoRPS, $varSerieRPS, $varNumeroRPS, $varNumLote, $varDataEmissaoRPS, $varSituacaoRPS, $varValorRPS, $varDeducaoRPS, $varCodigoServicoRPS, $varAliquotaRPS, $varISSRetido, $varIndicadorCPF, $varCPF, $varIM, $varIE, $varNome, $varTipoEndereco, $varEndereco, $varNumero, $varComplemento, $varBairro, $varCidade, $varUF, $varCEP, $varEmail, $varDiscriminacao, $nfse = null, $varTributacaoServico = null, $varCodigoSubitemLista = null, $varRegimeTributacao = null ,$varDataPagamentoServico = null, $varTipoNFTS = null) {
		
		$varDiretorio = "lotes/";
		$varCabecalho = "";
		$varConteudo = "";
		$LoteNum = $varNumLote;
		
		
//		$varNomeArquivo = "lotes/"."nfesp_lote_".date("YmdHis")."_".$LoteNum.".txt";
                $varNomeArquivo = "";
		if(empty($nfse)) {
			$s_rps = gera_rps($varNomeArquivo, $varTipoRPS, $varSerieRPS, $varNumeroRPS, $varDataEmissaoRPS, $varSituacaoRPS, $varValorRPS, $varDeducaoRPS, $varCodigoServicoRPS, $varAliquotaRPS, $varISSRetido, $varIndicadorCPF, $varCPF, $varIM, $varIE, $varNome, $varTipoEndereco, $varEndereco, $varNumero, $varComplemento, $varBairro, $varCidade, $varUF, $varCEP, $varEmail, $varDiscriminacao);
		}
		elseif($nfse == "NFS"){
			$s_rps = gera_nfse($varNomeArquivo, $varTipoRPS, $varSerieRPS, $varNumeroRPS, $varDataEmissaoRPS, $varSituacaoRPS, $varValorRPS, $varDeducaoRPS, $varCodigoServicoRPS, $varAliquotaRPS, $varISSRetido, $varIndicadorCPF, $varCPF, $varIM, $varIE, $varNome, $varTipoEndereco, $varEndereco, $varNumero, $varComplemento, $varBairro, $varCidade, $varUF, $varCEP, $varEmail, $varDiscriminacao, $varTributacaoServico, $varCodigoSubitemLista,$varRegimeTributacao,$varDataPagamentoServico, $varTipoNFTS);
		}
		
		return $s_rps;
		
	}
	//==============================================================================
	
	
	//Função para gerar a linha de cabeçalho de um novo arquivo lote.
	//==============================================================================
	function gera_cabecalho($opr_codigo, $data_inicial, $data_final) {

//echo "opr_codigo: $opr_codigo<br>";
		// Ongame "40013952"
		// Sulake "35061375" 
		// EPP "39324311"
		// Gala-net ( que é a empresa do Gpotato): CCM : 4.094.781-5 "40947815"


		$inscricao_municipal = ( ((int)($opr_codigo)==13 ||(int)($opr_codigo)==125) ? "40013952" : ( ((int)($opr_codigo)==16) ? "35061375" : ( ((int)($opr_codigo)==31) ? "40947815" : ( ($opr_codigo=="EPP") ? "39324311" : "99999999" ) ) ) );

		// date("t") - Number of days in the given month
		// echo "<hr>Calculate the date of the last day of the previous month: ".date("t/m/Y", strtotime("last month"))."<br>";
		if ($opr_codigo=="EPP") {
			$data_lote = $data_inicial;
		}
		else {
			$data_lote = date("Ymt", strtotime("last month"));
		}

		//Retorna o número 1001 que corresponde a linha de cabeçalho, a inscrição municipal mais a data de inicio do lote e a data final do lote.
		$ret = "1".DELIMITER.
				"001".DELIMITER.
				$inscricao_municipal.DELIMITER.	
				$data_lote.DELIMITER.			// $data_inicial
				$data_lote.DELIMITER.			// $data_final
				"\r\n";

		return $ret;

	}
	
	//Função para gerar a linha de cabeçalho de um novo arquivo lote para EPP ADMINISTRADORA
	//==============================================================================
	function gera_cabecalho_administradora($data_inicial) {

		$inscricao_municipal = "49211234"; //inscrição municipal - 4921123-4

		$data_lote = $data_inicial;
		
		//Retorna o número 1001 que corresponde a linha de cabeçalho, a inscrição municipal mais a data de inicio do lote e a data final do lote.
		$ret = "1".DELIMITER.
				"001".DELIMITER.
				$inscricao_municipal.DELIMITER.	
				$data_lote.DELIMITER.			// $data_inicial
				$data_lote.DELIMITER.			// $data_final
				"\r\n";

		return $ret;

	}//end function gera_cabecalho_administradora

        //Função para gerar o rodapé de um arquivo lote que esteja sendo finalizado.
	//==============================================================================
	function gera_rodape($n_linhas, $val_total) {
		
		$val_deducoes = 0;

		//Retorna o conteúdo do arquivo lote de texto mais a linha de rodapé que é composta pelo número 9 mais a soma de linhas de rps mais a soma dos valores das rps e mais a soma das deduções das rps.
		$ret = "9".DELIMITER.
				str_pad($n_linhas, 7, "0", STR_PAD_LEFT).DELIMITER.
				str_pad(100*$val_total, 15, "0", STR_PAD_LEFT).DELIMITER.
				str_pad(100*$val_deducoes, 15, "0", STR_PAD_LEFT).DELIMITER.
				"\r\n";

		return $ret;	

	}
	//==============================================================================
	
	
	
	//Função que gera a linha de rps.
	//==============================================================================
	function gera_rps($varNomeArquivo, $varTipoRPS, $varSerieRPS, $varNumeroRPS, $varDataEmissaoRPS, $varSituacaoRPS, $varValorRPS, $varDeducaoRPS, $varCodigoServicoRPS, $varAliquotaRPS, $varISSRetido, $varIndicadorCPF, $varCPF, $varIM, $varIE, $varNome, $varTipoEndereco, $varEndereco, $varNumero, $varComplemento, $varBairro, $varCidade, $varUF, $varCEP, $varEmail, $varDiscriminacao) {
	
		$LoteNum = $varSerieRPS;
		
		$varErro = "";
		$varCampos = "";
		
		//Removendo possíveis espaços em branco
		$varCPF	= trim($varCPF);

		//Condições
		if ($varTipoRPS <> "RPS" && $varTipoRPS <> "RPS-M" && $varTipoRPS <> "RPS-C") { 
			$varErro = "1" ;
			$varCampos .= "Tipo \n";
		}
		
		if ($varSerieRPS <> "") {
			if (strlen($varSerieRPS) > 5) { 
				$varErro = "1" ;
				$varCampos .= "Série \n";
			}
		}
		
		if ($varNumeroRPS == "" && strlen($varNumeroRPS) > 12) { 
			$varErro = "1";
			$varCampos .= "Número \n";
		}
		
		if (strlen($varDataEmissaoRPS) <> 8) { 
			$varErro = "1" ;
			$varCampos .= "Data \n";
		}
		
		if ($varSituacaoRPS <> "T" && $varSituacaoRPS <> "I" && $varSituacaoRPS <> "F" && $varSituacaoRPS <> "C" && $varSituacaoRPS <> "E" && $varSituacaoRPS <> "J") { 
			$varErro = "1";
			$varCampos .= "Situação \n";
		}
		
		if ($varValorRPS == "" && strlen($varValorRPS) > 15) { 
			$varErro = "1";
			$varCampos .= "Valor \n" ;
		} else { 
			$varValorRPS = str_replace(",", "", $varValorRPS);
			$varValorRPS = str_replace(".", "", $varValorRPS);
		}
		
		if ($varDeducaoRPS == "" && strlen($varDeducaoRPS) > 15) { 
			$varErro = "1";
			$varCampos .= "Dedução \n" ;
		} else { 
			$varDeducaoRPS = str_replace(",", "", $varDeducaoRPS);
			$varDeducaoRPS = str_replace(".", "", $varDeducaoRPS);
		}
		
		if ($varCodigoServicoRPS == "" && strlen($varCodigoServicoRPS) > 5) { 
			$varErro = "1" ;
			$varCampos .= "Código \n";
		}
		
		if ($varAliquotaRPS == "" && strlen($varAliquotaRPS) > 4) { 
			$varErro = "1" ;
			$varCampos .= "Alíquota \n";
		} else { 
			$varAliquotaRPS = str_replace(",", "", $varAliquotaRPS);
			$varAliquotaRPS = str_replace(".", "", $varAliquotaRPS);
		}
		
		if ($varISSRetido <> "1" && $varISSRetido <> "2") { 
			$varErro = "1";
			$varCampos .= "ISS Retido \n";
		}
		
		if ($varIndicadorCPF <> "1" && $varIndicadorCPF <> "2" && $varIndicadorCPF <> "3") { 
			$varErro = "1";
			$varCampos .= "Indicador \n";
		}
		
		if ($varIndicadorCPF == "1" && strlen($varCPF) <> 11) { 
			$varErro = "1";
			$varCampos .= "CPF \n";
//                        echo "PROBLEMA CPF:".var_dump($varNomeArquivo, $varTipoRPS, $varSerieRPS, $varNumeroRPS, $varDataEmissaoRPS, $varSituacaoRPS, $varValorRPS, $varDeducaoRPS, $varCodigoServicoRPS, $varAliquotaRPS, $varISSRetido, $varIndicadorCPF, $varCPF, $varIM, $varIE, $varNome, $varTipoEndereco, $varEndereco, $varNumero, $varComplemento, $varBairro, $varCidade, $varUF, $varCEP, $varEmail, $varDiscriminacao).  str_repeat("=", 80)."<br>";
		}
		
		if ($varIndicadorCPF == "2" && strlen($varCPF) <> 14) { 
			$varErro = "1";
			$varCampos .= "CNPJ \n";
		}
		/* Comentado por Wagner em 28/10/2011
		if ($varIndicadorCPF == "3" && strlen($varCPF) <> "") { 
			$varErro = "1";
			$varCampos .= "Indicador Tipo 3 \n";
		}
		*/
		if ($varIndicadorCPF == "2" && (strlen($varNome) == 0 || strlen($varNome) > 75)) { 
			$varErro = "1";
			$varCampos .= "Razão Social ";
		} elseif ($varIndicadorCPF == "2" && $varIndicadorCPF == "3") {
			if (strlen($varNome) > 75) { 
				$varErro = "1";
				$varCampos .= "Nome \n";
			}
		}
		
		if ($varIndicadorCPF == "2" && (strlen($varCidade) == 0 || strlen($varCidade) > 50)) { 
			$varErro = "1";
			$varCampos .= "Cidade \n";
		} elseif ($varIndicadorCPF == "2" && $varIndicadorCPF == "3") {
			if (strlen($varCidade) > 50) { 
				$varErro = "1";
				$varCampos .= "Cidade \n";
			}
		}
		
		if ($varIndicadorCPF == "2" && strlen($varUF) <> 2) { 
			$varErro = "1";
			$varCampos .= "Estado \n";
		}
		
		if ($varIndicadorCPF == "2" && strlen($varCEP) <> 8) { 
			$varErro = "1";
			$varCampos .= "CEP \n";
		}
		
		if ($varDiscriminacao <> "") { 
			$varDiscriminacao = str_replace("\n", chr(124), $varDiscriminacao);
		}
		
		if ($varTipoEndereco <> "") {
			if (strlen($varTipoEndereco) > 3) { 
				$varErro = "1";
				$varCampos .= "Tipo de Endereço \n";
			}
		}
		
		if ($varEndereco <> "") {
			if (strlen($varEndereco) > 50) { 
				$varErro = "1";
				$varCampos .= "Endereço \n";
			}
		}
		
		if ($varNumero <> "") {
			if (strlen($varNumero) > 10) { 
				$varErro = "1";
				$varCampos .= "Número \n";
			}
		}
		
		if ($varComplemento <> "") {
			if (strlen($varComplemento) > 30) { 
				$varErro = "1";
				$varCampos .= "Complemento \n";
//                        echo "PROBLEMA COMPLEMENTO:".var_dump($varNomeArquivo, $varTipoRPS, $varSerieRPS, $varNumeroRPS, $varDataEmissaoRPS, $varSituacaoRPS, $varValorRPS, $varDeducaoRPS, $varCodigoServicoRPS, $varAliquotaRPS, $varISSRetido, $varIndicadorCPF, $varCPF, $varIM, $varIE, $varNome, $varTipoEndereco, $varEndereco, $varNumero, $varComplemento, $varBairro, $varCidade, $varUF, $varCEP, $varEmail, $varDiscriminacao).  str_repeat("=", 80)."<br>";
			}
		}
		
		if ($varBairro <> "") {
			if (strlen($varBairro) > 30) { 
				$varErro = "1";
				$varCampos .= "Bairro \n";
			}
		}
		
		if ($varEmail <> "") {
			if (strlen($varEmail) > 75) { 
				$varErro = "1";
				$varCampos .= "Email \n";
			}
		}
		
		if ($varErro <> "") {
			$ret = "erro".$varCampos;
		} else {

		$varCPF		=	str_pad($varCPF, 14, "0", STR_PAD_LEFT);
		
		if(false) {
				$varTipoRPS_Nome			= "varTipoRPS".DELIMITER2;         
				$LoteNum_Nome				= "LoteNum".DELIMITER2;            
				$varNumeroRPS_Nome			= "varNumeroRPS".DELIMITER2;       
				$varSerieRPS_Nome			= "varSerieRPS".DELIMITER2;       
				$varDataEmissaoRPS_Nome		= "varDataEmissaoRPS".DELIMITER2;  
				$varSituacaoRPS_Nome		= "varSituacaoRPS".DELIMITER2;     
				$varValorRPS_Nome			= "varValorRPS".DELIMITER2;        
				$varDeducaoRPS_Nome			= "varDeducaoRPS".DELIMITER2;      
				$varCodigoServicoRPS_Nome	= "varCodigoServicoRPS".DELIMITER2;
				$varAliquotaRPS_Nome		= "varAliquotaRPS".DELIMITER2;     
				$varISSRetido_Nome			= "varISSRetido".DELIMITER2;       
				$varIndicadorCPF_Nome		= "varIndicadorCPF".DELIMITER2;    
				$varCPF_Nome				= "varCPF".DELIMITER2;             
				$varIM_Nome					= "varIM".DELIMITER2;              
				$varIE_Nome					= "varIE".DELIMITER2;              
				$varNome_Nome				= "varNome".DELIMITER2;            
				$varTipoEndereco_Nome		= "varTipoEndereco".DELIMITER2;    
				$varEndereco_Nome			= "varEndereco".DELIMITER2;        
				$varNumero_Nome				= "varNumero".DELIMITER2;          
				$varComplemento_Nome		= "varComplemento".DELIMITER2;     
				$varBairro_Nome				= "varBairro".DELIMITER2;          
				$varCidade_Nome				= "varCidade".DELIMITER2;          
				$varUF_Nome					= "varUF".DELIMITER2;              
				$varCEP_Nome				= "varCEP".DELIMITER2;             
				$varEmail_Nome				= "varEmail".DELIMITER2;
				$varDiscriminacao_Nome		= "varDiscriminacao".DELIMITER2;   
		} else {
				$varTipoRPS_Nome			= "";
				$LoteNum_Nome				= "";
				$varNumeroRPS_Nome			= "";
				$varSerieRPS_Nome			= "";
				$varDataEmissaoRPS_Nome		= "";
				$varSituacaoRPS_Nome		= "";
				$varValorRPS_Nome			= "";
				$varDeducaoRPS_Nome			= "";
				$varCodigoServicoRPS_Nome	= "";
				$varAliquotaRPS_Nome		= "";
				$varISSRetido_Nome			= "";
				$varIndicadorCPF_Nome		= "";
				$varCPF_Nome				= "";
				$varIM_Nome					= "";
				$varIE_Nome					= "";
				$varNome_Nome				= "";
				$varTipoEndereco_Nome		= "";
				$varEndereco_Nome			= "";
				$varNumero_Nome				= "";
				$varComplemento_Nome		= "";
				$varBairro_Nome				= "";
				$varCidade_Nome				= "";
				$varUF_Nome					= "";
				$varCEP_Nome				= "";
				$varEmail_Nome				= "";
				$varDiscriminacao_Nome		= "";
		}

//			$varArquivo = "lotes/"."nfesp_lote_".formatar_data(date("YmdHis"))."_".$LoteNum.".txt";

//			$handle = fopen($varArquivo, "r");
//			$varTexto = fread($handle, filesize($fileSource));
//			fclose($handle);

			$ret = "2".DELIMITER.
					$varTipoRPS_Nome.         strtoupper(formatar_string($varTipoRPS,5)).DELIMITER.
					$LoteNum_Nome.            strtoupper(formatar_string($LoteNum,5)).DELIMITER.
					$varNumeroRPS_Nome.       formatar_numero($varNumeroRPS,12).DELIMITER.
					$varDataEmissaoRPS_Nome.  $varDataEmissaoRPS.DELIMITER.
					$varSituacaoRPS_Nome.     strtoupper($varSituacaoRPS).DELIMITER.
					$varValorRPS_Nome.        formatar_numero($varValorRPS,15).DELIMITER.
					$varDeducaoRPS_Nome.      formatar_numero($varDeducaoRPS,15).DELIMITER.
					$varCodigoServicoRPS_Nome.formatar_numero($varCodigoServicoRPS,5).DELIMITER.
					$varAliquotaRPS_Nome.     formatar_numero($varAliquotaRPS,4).DELIMITER.
					$varISSRetido_Nome.       formatar_numero($varISSRetido,1).DELIMITER.
					$varIndicadorCPF_Nome.    formatar_numero($varIndicadorCPF,1).DELIMITER.
					$varCPF_Nome.             str_pad($varCPF, 14, "0", STR_PAD_LEFT).DELIMITER.
					$varIM_Nome.              formatar_numero($varIM,8).DELIMITER.
					$varIE_Nome.              formatar_numero($varIE,12).DELIMITER.
					$varNome_Nome.            formatar_string($varNome,75).DELIMITER.
					$varTipoEndereco_Nome.    formatar_string($varTipoEndereco,3).DELIMITER.
					$varEndereco_Nome.        formatar_string($varEndereco,50).DELIMITER.
					$varNumero_Nome.          formatar_string($varNumero,10).DELIMITER.
					$varComplemento_Nome.     formatar_string($varComplemento,30).DELIMITER.
					$varBairro_Nome.          formatar_string($varBairro,30).DELIMITER.
					$varCidade_Nome.          formatar_string($varCidade,50).DELIMITER.
					$varUF_Nome.              formatar_string($varUF,2).DELIMITER.
					$varCEP_Nome.             formatar_numero($varCEP,8).DELIMITER.
					$varEmail_Nome.           formatar_string($varEmail,75).DELIMITER.
					$varDiscriminacao_Nome.   $varDiscriminacao.DELIMITER
					."\r\n";
	}

		return $ret;
	}
	//==============================================================================

	//Função que gera a linha de NFSe do tipo 4.
	//==============================================================================
	function gera_nfse($varNomeArquivo, $varTipoNFSe, $varSerieNFSe, $varNumeroNFSe, $varDataEmissaoNFSe, $varSituacaoNFSe, $varValorNFSe, $varDeducaoNFSe, $varCodigoServicoNFSe, $varAliquotaNFSe, $varISSRetido, $varIndicadorCPF, $varCPF, $varIM, $varIE, $varNome, $varTipoEndereco, $varEndereco, $varNumero, $varComplemento, $varBairro, $varCidade, $varUF, $varCEP, $varEmail, $varDiscriminacao, $varTributacaoServico, $varCodigoSubitemLista,$varRegimeTributacao,$varDataPagamentoServico,$varTipoNFTS) {
	
		$LoteNum = $varSerieNFSe;
		
		$varErro = "";
		$varCampos = "";
		
		//Condições
		if ($varTipoNFSe <> "01" && $varTipoNFSe <> "02" && $varTipoNFSe <> "03") { 
			$varErro = "1" ;
			$varCampos .= "Tipo ";
		}
		
		if ($varSerieNFSe <> "") {
			if (strlen($varSerieNFSe) > 5) { 
				$varErro = "1" ;
				$varCampos .= "Série ";
			}
		}
		
		if ($varNumeroNFSe == "" && strlen($varNumeroNFSe) > 12) { 
			$varErro = "1";
			$varCampos .= "Número ";
		}
		
		if (strlen($varDataEmissaoNFSe) <> 8) { 
			$varErro = "1" ;
			$varCampos .= "Data ";
		}
				
		if ($varSituacaoNFSe <> "N" && $varSituacaoNFSe <> "C") { 
			$varErro = "1";
			$varCampos .= "Situação ";
		}
		
		if ($varTributacaoServico<>"T" && $varTributacaoServico<>"I" && $varTributacaoServico<>"J") { 
			$varErro = "1";
			$varCampos .= "Tributação ";
		}

		if ($varValorNFSe == "" && strlen($varValorNFSe) > 15) { 
			$varErro = "1";
			$varCampos .= "Valor " ;
		} else { 
			$varValorNFSe = str_replace(",", "", $varValorNFSe);
			$varValorNFSe = str_replace(".", "", $varValorNFSe);
		}
		
		if ($varDeducaoNFSe == "" && strlen($varDeducaoNFSe) > 15) { 
			$varErro = "1";
			$varCampos .= "Dedução " ;
		} else { 
			$varDeducaoNFSe = str_replace(",", "", $varDeducaoNFSe);
			$varDeducaoNFSe = str_replace(".", "", $varDeducaoNFSe);
		}
		
		if ($varCodigoServicoNFSe == "" && strlen($varCodigoServicoNFSe) > 5) { 
			$varErro = "1" ;
			$varCampos .= "Código ";
		}
		
		if ($varAliquotaNFSe == "" && strlen($varAliquotaNFSe) > 4) { 
			$varErro = "1" ;
			$varCampos .= "Alíquota ";
		} else { 
			$varAliquotaNFSe = str_replace(",", "", $varAliquotaNFSe);
			$varAliquotaNFSe = str_replace(".", "", $varAliquotaNFSe);
		}
		
		if ($varISSRetido <> "1" && $varISSRetido <> "2") { 
			$varErro = "1";
			$varCampos .= "ISS Retido ";
		}
		
		if ($varIndicadorCPF <> "1" && $varIndicadorCPF <> "2" && $varIndicadorCPF <> "3") { 
			$varErro = "1";
			$varCampos .= "Indicador ";
		}
		
		if ($varIndicadorCPF == "1" && strlen($varCPF) <> 11) { 
			$varErro = "1";
			$varCampos .= "CPF ";
		}
		
		if ($varIndicadorCPF == "2" && strlen($varCPF) <> 14) { 
			$varErro = "1";
			$varCampos .= "CNPJ ";
		}
		
		if ($varIndicadorCPF == "3" && strlen($varCPF) <> "") { 
			$varErro = "1";
			$varCampos .= "Indicador Tipo 3 ";
		}
		
		if ($varIndicadorCPF == "2" && (strlen($varNome) == 0 || strlen($varNome) > 75)) { 
			$varErro = "1";
			$varCampos .= "Razão Social ";
		} elseif ($varIndicadorCPF == "2" && $varIndicadorCPF == "3") {
			if (strlen($varNome) > 75) { 
				$varErro = "1";
				$varCampos .= "Nome ";
			}
		}
		
		if ($varIndicadorCPF == "2" && (strlen($varCidade) == 0 || strlen($varCidade) > 50)) { 
			$varErro = "1";
			$varCampos .= "Cidade ";
		} elseif ($varIndicadorCPF == "2" && $varIndicadorCPF == "3") {
			if (strlen($varCidade) > 50) { 
				$varErro = "1";
				$varCampos .= "Cidade ";
			}
		}
		
		if ($varIndicadorCPF == "2" && strlen($varUF) <> 2) { 
			$varErro = "1";
			$varCampos .= "Estado ";
		}
		
		if ($varIndicadorCPF == "2" && strlen($varCEP) <> 8) { 
			$varErro = "1";
			$varCampos .= "CEP ";
		}
		
		if ($varDiscriminacao <> "") { 
			$varDiscriminacao = str_replace("\n", chr(124), $varDiscriminacao);
		}
		
		if ($varTipoEndereco <> "") {
			if (strlen($varTipoEndereco) > 3) { 
				$varErro = "1";
				$varCampos .= "Tipo de Endereço ";
			}
		}
		
		if ($varEndereco <> "") {
			if (strlen($varEndereco) > 50) { 
				$varErro = "1";
				$varCampos .= "Endereço ";
			}
		}
		
		if ($varNumero <> "") {
			if (strlen($varNumero) > 10) { 
				$varErro = "1";
				$varCampos .= "Número ";
			}
		}
		
		if ($varComplemento <> "") {
			if (strlen($varComplemento) > 30) { 
				$varErro = "1";
				$varCampos .= "Complemento ";
			}
		}
		
		if ($varBairro <> "") {
			if (strlen($varBairro) > 30) { 
				$varErro = "1";
				$varCampos .= "Bairro ";
			}
		}
		
		if ($varEmail <> "") {
			if (strlen($varEmail) > 75) { 
				$varErro = "1";
				$varCampos .= "Email ";
			}
		}
		
		if ($varErro <> "") {
			$ret = "erro".$varCampos;
		} else {

		if(false) {
				$varTipoNFSe_Nome			= "varTipoNFSe".DELIMITER2;         
				$LoteNum_Nome				= "LoteNum".DELIMITER2;            
				$varNumeroNFSe_Nome			= "varNumeroNFSe".DELIMITER2;       
				$varSerieNFSe_Nome			= "varSerieNFSe".DELIMITER2;       
				$varDataEmissaoNFSe_Nome		= "varDataEmissaoNFSe".DELIMITER2;  
				$varSituacaoNFSe_Nome		= "varSituacaoNFSe".DELIMITER2;     
				$varValorNFSe_Nome			= "varValorNFSe".DELIMITER2;        
				$varDeducaoNFSe_Nome			= "varDeducaoNFSe".DELIMITER2;      
				$varCodigoServicoNFSe_Nome	= "varCodigoServicoNFSe".DELIMITER2;
				$varAliquotaNFSe_Nome		= "varAliquotaNFSe".DELIMITER2;     
				$varISSRetido_Nome			= "varISSRetido".DELIMITER2;       
				$varIndicadorCPF_Nome		= "varIndicadorCPF".DELIMITER2;    
				$varCPF_Nome				= "varCPF".DELIMITER2;             
				$varIM_Nome					= "varIM".DELIMITER2;              
				$varIE_Nome					= "varIE".DELIMITER2;              
				$varNome_Nome				= "varNome".DELIMITER2;            
				$varTipoEndereco_Nome		= "varTipoEndereco".DELIMITER2;    
				$varEndereco_Nome			= "varEndereco".DELIMITER2;        
				$varNumero_Nome				= "varNumero".DELIMITER2;          
				$varComplemento_Nome		= "varComplemento".DELIMITER2;     
				$varBairro_Nome				= "varBairro".DELIMITER2;          
				$varCidade_Nome				= "varCidade".DELIMITER2;          
				$varUF_Nome					= "varUF".DELIMITER2;              
				$varCEP_Nome				= "varCEP".DELIMITER2;             
				$varEmail_Nome				= "varEmail".DELIMITER2;
				$varDiscriminacao_Nome		= "varDiscriminacao".DELIMITER2;
				$varTributacaoServico_Nome	= "varTributacaoServico".DELIMITER2;
				$varCodigoSubitemLista_Nome	= "varCodigoSubitemLista".DELIMITER2;
				$varRegimeTributacao_Nome	= "varRegimeTributacao".DELIMITER2;
				$varDataPagamentoServico_Nome= "varDataPagamentoServico".DELIMITER2;
				$varTipoNFTS_Nome			= "varTipoNFTS".DELIMITER2;

		} else {
				$varTipoNFSe_Nome			= "";
				$LoteNum_Nome				= "";
				$varNumeroNFSe_Nome			= "";
				$varSerieNFSe_Nome			= "";
				$varDataEmissaoNFSe_Nome	= "";
				$varSituacaoNFSe_Nome		= "";
				$varValorNFSe_Nome			= "";
				$varDeducaoNFSe_Nome		= "";
				$varCodigoServicoNFSe_Nome	= "";
				$varAliquotaNFSe_Nome		= "";
				$varISSRetido_Nome			= "";
				$varIndicadorCPF_Nome		= "";
				$varCPF_Nome				= "";
				$varIM_Nome					= "";
				$varIE_Nome					= "";
				$varNome_Nome				= "";
				$varTipoEndereco_Nome		= "";
				$varEndereco_Nome			= "";
				$varNumero_Nome				= "";
				$varComplemento_Nome		= "";
				$varBairro_Nome				= "";
				$varCidade_Nome				= "";
				$varUF_Nome					= "";
				$varCEP_Nome				= "";
				$varEmail_Nome				= "";
				$varDiscriminacao_Nome		= "";
				$varTributacaoServico_Nome	= "";
				$varCodigoSubitemLista_Nome	= "";
				$varRegimeTributacao_Nome	= "";
				$varDataPagamentoServico_Nome= "";
				$varDiscriminacao_Nome		= "";
				$varTipoNFTS_Nome			= "";
		}

//			$varArquivo = "lotes/"."nfesp_lote_".formatar_data(date("YmdHis"))."_".$LoteNum.".txt";

//			$handle = fopen($varArquivo, "r");
//			$varTexto = fread($handle, filesize($fileSource));
//			fclose($handle);

			$ret = "4".DELIMITER.
					$varTipoNFSe_Nome.         formatar_numero($varTipoNFSe,2).DELIMITER.
					$LoteNum_Nome.             strtoupper(formatar_string($LoteNum,5)).DELIMITER.
					$varNumeroNFSe_Nome.       formatar_numero($varNumeroNFSe,12).DELIMITER.
					$varDataEmissaoNFSe_Nome.  $varDataEmissaoNFSe.DELIMITER.
					$varSituacaoNFSe_Nome.     strtoupper($varSituacaoNFSe).DELIMITER.
					$varTributacaoServico_Nome.strtoupper($varTributacaoServico).DELIMITER.	
					$varValorNFSe_Nome.        formatar_numero($varValorNFSe,15).DELIMITER.
					$varDeducaoNFSe_Nome.      formatar_numero($varDeducaoNFSe,15).DELIMITER.
					$varCodigoServicoNFSe_Nome.formatar_numero($varCodigoServicoNFSe,5).DELIMITER.
					$varCodigoSubitemLista_Nome.formatar_numero($varCodigoSubitemLista,4).DELIMITER.		
					$varAliquotaNFSe_Nome.     formatar_numero($varAliquotaNFSe,4).DELIMITER.
					$varISSRetido_Nome.       formatar_numero($varISSRetido,1).DELIMITER.
					$varIndicadorCPF_Nome.    formatar_numero($varIndicadorCPF,1).DELIMITER.
					$varCPF_Nome.             str_pad($varCPF, 14, "0", STR_PAD_LEFT).DELIMITER.
					$varIM_Nome.              formatar_numero($varIM,8).DELIMITER.
					$varNome_Nome.            formatar_string($varNome,75).DELIMITER.
					$varTipoEndereco_Nome.    formatar_string($varTipoEndereco,3).DELIMITER.
					$varEndereco_Nome.        formatar_string($varEndereco,50).DELIMITER.
					$varNumero_Nome.          formatar_string($varNumero,10).DELIMITER.
					$varComplemento_Nome.     formatar_string($varComplemento,30).DELIMITER.
					$varBairro_Nome.          formatar_string($varBairro,30).DELIMITER.
					$varCidade_Nome.          formatar_string($varCidade,50).DELIMITER.
					$varUF_Nome.              formatar_string($varUF,2).DELIMITER.
					$varCEP_Nome.             formatar_numero($varCEP,8).DELIMITER.
					$varEmail_Nome.           formatar_string($varEmail,75).DELIMITER.
					$varTipoNFTS_Nome.			  formatar_numero($varTipoNFTS,1).DELIMITER.
					$varRegimeTributacao_Nome.strtoupper($varRegimeTributacao).DELIMITER.
					$varDataPagamentoServico_Nome.formatar_string($varDataPagamentoServico,8).DELIMITER.
					$varDiscriminacao_Nome.   $varDiscriminacao.DELIMITER
					."\r\n";
	}

		return $ret;
	}
	//==============================================================================
	
	
	//Função para preencher com espaços as strings que não completam o valor exigido. É usada só nas linhas de RPS e NFSe.
	//==============================================================================
	function formatar_string($texto, $quant) {
		
		$ret = str_pad($texto, $quant, " ", STR_PAD_RIGHT);
		return $ret;
	}

	//==============================================================================
	function formatar_string_break($texto, $length) {
		$ret = "";
		for($i=0;$i<strlen($texto);$i++) {
			$ret .= $texto[$i];
			if(($i%$length)==0 && $i>0) {
				$ret .= "\n";
			}
		}
		return $ret;
	}
	
	
	
	//Função para preencher com zeros os números que não completam o valor exigido. É usada só nas linhas de RPS e NFSe.
	//==============================================================================
	function formatar_numero($numero, $quant) {
		$ret = str_pad($numero, $quant, "0", STR_PAD_RIGHT);
		return $ret;
	}
	//==============================================================================
	
	
	
	//Função para formatar a data no valor exigido.
	//==============================================================================
	function formatar_data($data) {
		
		$varDia = date("d");
		$varMes = date("m");
		
		if ($varDia < 10) { $varDia = "0".$varDia; }
		if ($varMes < 10) { $varMes = "0".$varMes; }
		
		return date("Y").$varMes.$varDia;
	}
	//==============================================================================
	
	
	
	//Função para formatar o número total de linhas de um arquivo lote com zeros e assim preencher o valor exigido.
	//==============================================================================
	function formatar_linha($quant, $numero) {
		$ret = str_pad($numero, $quant, "0", STR_PAD_LEFT);
		return $ret;
	}
	//==============================================================================
	
	
	
	//Função para formatar o valor de deduções e valor da rps de um arquivo lote com zeros e assim preencher o valor exigido.
	//==============================================================================
	function formatar_valores_deducoes($quant, $numero) {
		$ret = str_pad($numero, $quant, "0", STR_PAD_LEFT);
		return $ret;

	}
	//==============================================================================
	
	
	
	//Função para pegar o valor e a dedução de uma linha de rps.
	//==============================================================================
	function pegar_valores($texto, $x, $y) {
		
		if (strlen(trim($texto)) > $y) {
			$varAux = substr($texto, $x, $y);
		} else {
			$varAux = $texto;
		}
		
		return $varAux;
	}
	//==============================================================================


?>