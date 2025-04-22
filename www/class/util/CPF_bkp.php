<?php
require_once $raiz_do_projeto."consulta_cpf/config.inc.cpf.php";
require_once $raiz_do_projeto.'includes/main.php';
require_once $raiz_do_projeto.'includes/pdv/main.php';
/**
 * CLASSE PARA VALIDACOES GERAIS DE CPF
 *
 * @author diego andrade
 * @email diego.gomes@e-prepag.com.br
 * @date 19/06/2015
 */
class CPF extends classCPF{
    //put your code here
    
    public function verificaCpfRF($cpf,$dataNascimento){
        
        $errors = array();

            if( !$this->verificaCPF($cpf,$dataNascimento) )
                $errors[] = "CPF inválido, por favor revise o número digitado.";

            //ob_clean();
            $cpf = preg_replace('/[^0-9]/', '', $cpf);

            //Novo modelo de Consulta
//            $rs_api = new classCPF();
            $resposta = null;
            $parametros = array(
                                'cpfcnpj' => $cpf,
                                'data_nascimento' => (!empty($dataNascimento)?$dataNascimento:null)
                                );
            $testeCPF = $this->Req_EfetuaConsulta($parametros,$resposta);

            //var_dump($testeCPF); die;
            //var_dump($resposta); die;
            
            //Verificação de idade mínima 
            if($testeCPF == 112){
                $errors[] = "Venda não autorizada para menores de ". $GLOBALS["IDADE_MINIMA"] ." anos.";
            }

            //Testando se o CPF consta na BlackList
            elseif($testeCPF == 299) {
                $errors[] = "Existem pendências de documentos relacionadas ao seu CPF. Por gentileza entre em contato com suporte@e-prepag.com.br para desbloqueio. Como empresa de serviços financeiros, a E-prepag trabalha para manter um ambiente seguro para todos, e conta com a sua colaboração.";
            }
		        
            //Testando se ultrapassou o limite de utilização do mesmo CPF
            elseif ($testeCPF != 171) {

                    if (CPF_PARTNER_ENVIRONMET == CPF_PARTNER_CREDIFY) { 

                            if($testeCPF == 2){
                                $errors[] = "Este número de CPF parece não constar na Receita Federal. Por favor, verifique o número digitado e tente novamente.";
                            }

                            elseif($testeCPF == 1){
                                $errors[] = "Não foi possível realizar consulta. Erro(9191). Por favor, entre em contato com suporte@e-prepag.com.br reportando o código do problema. Obrigado.";
                            }

                            elseif(is_null($testeCPF)){
                                $errors[] = "Erro no sistema (0034). Por favor, entre em contato com suporte@e-prepag.com.br reportando o código do problema. Obrigado.";
                            }

                            elseif($testeCPF == 0 && $resposta['resposta']['cpf']['situacao'] != CPF_SITUCAO_REGULAR) {
                                $errors[] = "CPF não está regular junto a Receita Federal. Por favor, verifique o número digitado e tente novamente.";
                            }

                            elseif(!isset($resposta['resposta']['cpf']['nome'])){
                                $errors[] = "Este número de CPF parece não constar na Receita Federal. Por favor, verifique o número digitado e tente novamente.";
                            }

                            elseif($testeCPF == 0 && $resposta['resposta']['cpf']['situacao'] == CPF_SITUCAO_REGULAR){
                                $retorno["nome"] = $resposta['resposta']['cpf']['nome'];
                            }

                            else {
                                $errors[] = "Erro no sistema (0407). Por favor, entre em contato com suporte@e-prepag.com.br reportando o código do problema. Obrigado.";
                            }

                    } // end if (CPF_PARTNER_ENVIRONMET == CPF_PARTNER_CREDIFY)
						
					elseif(CPF_PARTNER_ENVIRONMET == CPF_CONSULTA_HUB){
						
						    $file = fopen("/www/log/retorno_cpf.txt", "a+");
							fwrite($file, "hud do desenvolvedor \n");
							fwrite($file, "resultado code ".$testeCPF."\n");
							fwrite($file, "resultado json ".json_encode($resposta)."\n");
							fwrite($file, str_repeat("*", 50)); 
							fclose($file);
						
						    if($testeCPF == 2){
                                $errors[] = "Este número de CPF parece não constar na Receita Federal. Por favor, verifique o número digitado e tente novamente.";
                            }

                            elseif($testeCPF == 1){
                                $errors[] = "Não foi possível realizar consulta. Erro(9191). Por favor, entre em contato com suporte@e-prepag.com.br reportando o código do problema. Obrigado.";
                            }

                            elseif(is_null($testeCPF)){
                                $errors[] = "Erro no sistema (0034). Por favor, entre em contato com suporte@e-prepag.com.br reportando o código do problema. Obrigado.";
                            }

                            elseif($testeCPF == 0 && $resposta['result']['situacao_cadastral'] != CPF_SITUCAO_REGULAR) {
                                $errors[] = "CPF não está regular junto a Receita Federal. Por favor, verifique o número digitado e tente novamente.";
                            }

                            elseif(!isset($resposta['result']['nome_da_pf'])){
                                $errors[] = "Este número de CPF parece não constar na Receita Federal. Por favor, verifique o número digitado e tente novamente.";
                            }

                            elseif($testeCPF == 0 && $resposta['result']['situacao_cadastral'] == CPF_SITUCAO_REGULAR){
                                $retorno["nome"] = $resposta['result']['nome_da_pf'];
								$retorno["data_nascimento"] = ['result']['data_nascimento'];
                            }

                            else {
                                $errors[] = "Erro no sistema (0407). Por favor, entre em contato com suporte@e-prepag.com.br reportando o código do problema. Obrigado.";
                            }
						
					}
                    elseif (CPF_PARTNER_ENVIRONMET == CPF_PARTNER_OMNIDATA) {
						
						    $file = fopen("/www/log/retorno_cpf.txt", "a+");
							fwrite($file, "resultado ".$testeCPF."\n");
							fwrite($file, str_repeat("*", 50)); 
							fclose($file);
							
                            if($testeCPF == 2 || $testeCPF == 8){
                                $errors[] = "Este número de CPF parece não constar na Receita Federal. Por favor, verifique o número digitado e tente novamente.";
                            }

                            elseif($testeCPF == 1){
                                $errors[] = "Não foi possível realizar consulta. Erro(9191). Por favor, entre em contato com suporte@e-prepag.com.br reportando o código do problema. Obrigado.";
                            }

                            elseif($testeCPF == 12){
                                $errors[] = "A Data de Nascimento informada é diferente do que consta nos dados da Receita. Por favor, insira a data de nascimento do CPF informado.";
                            }

                            elseif(is_null($testeCPF)){
                                $errors[] = "Erro no sistema (0034). Por favor, entre em contato com suporte@e-prepag.com.br reportando o código do problema. Obrigado.";
                            }

                            elseif($testeCPF == 3 && $resposta['pesquisas']['camposResposta']['situacao'] != CPF_SITUCAO_REGULAR) {
                                $errors[] = "CPF não está regular junto a Receita Federal. Por favor, verifique o número digitado e tente novamente.";
                            }

                            elseif(!isset($resposta['pesquisas']['camposResposta']['nome'])){
                                $errors[] = "Este número de CPF parece não constar na Receita Federal. Por favor, verifique o número digitado e tente novamente.";
                            }

                            elseif($testeCPF == 3 && $resposta['pesquisas']['camposResposta']['situacao'] == CPF_SITUCAO_REGULAR){
                                $retorno["nome"] = $resposta['pesquisas']['camposResposta']['nome'];
                                $retorno["data_nascimento"] = $resposta['pesquisas']['camposResposta']['data_nascimento'];
                            }

                            else {
                                $errors[] = "Erro no sistema [".$resposta['pesquisas']['msg']."] (0407). Por favor, entre em contato com suporte@e-prepag.com.br reportando o código do problema. Obrigado.";
                            }


                    }//end elseif (CPF_PARTNER_ENVIRONMET == CPF_PARTNER_OMNIDATA)   

                    elseif (CPF_PARTNER_ENVIRONMET == CPF_CONSULTA_CACHE) {

                            if($testeCPF == 2){
                                $errors[] = "Estamos momentaneamente com falha na comunição para verificação do CPF informado. Por favor, aguarde alguns minutos e tente novamente.";
                            }

                            elseif($testeCPF == 1){
                                $retorno["nome"] = $resposta['pesquisas']['camposResposta']['nome'];
                                $retorno["data_nascimento"] = $resposta['pesquisas']['camposResposta']['data_nascimento'];
                            }

                            else {
                                $errors[] = "Erro no sistema [".$resposta['pesquisas']['msg']."] (0485). Por favor, entre em contato com suporte@e-prepag.com.br reportando o código do problema. Obrigado.";
                            }

                    } //end  elseif (CPF_PARTNER_ENVIRONMET == CPF_CONSULTA_CACHE) 

            }//end elseif ($testeCPF != 171)

            // Atingiu o limite máximo de utilização do mesmo CPF
            else {

                    $errors[] = "Para utilizar seu CPF precisamos confirmar alguns dados pessoais. Por favor entre em contato com a E-Prepag. https://www.e-prepag.com/support";
                    //$errors[] = "Para utilizar seu CPF precisamos confirmar alguns dados pessoais. Por favor entre em contato com a E-Prepag.";

            }//end else do elseif ($testeCPF != 171)

            $erro = (is_array($errors) && count($errors) >= 1) ? implode("\n",$errors) : null;
            $retorno["erros"] = utf8_encode($erro);
//            $ret = array_map(utf8_encode, $retorno);
            return $retorno;
            
        }
        
    public function verificaCPF($cpf) {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

	$RecebeCPF=$cpf;

		if (strlen($RecebeCPF)!=11)
		{ return 0; }
		else
		if ($RecebeCPF=="00000000000" || $RecebeCPF=="11111111111")
		{ return 0; }
		else
		{
			$Numero[1]=intval(substr($RecebeCPF,1-1,1));
			$Numero[2]=intval(substr($RecebeCPF,2-1,1));
			$Numero[3]=intval(substr($RecebeCPF,3-1,1));
			$Numero[4]=intval(substr($RecebeCPF,4-1,1));
			$Numero[5]=intval(substr($RecebeCPF,5-1,1));
			$Numero[6]=intval(substr($RecebeCPF,6-1,1));
			$Numero[7]=intval(substr($RecebeCPF,7-1,1));
			$Numero[8]=intval(substr($RecebeCPF,8-1,1));
			$Numero[9]=intval(substr($RecebeCPF,9-1,1));
			$Numero[10]=intval(substr($RecebeCPF,10-1,1));
			$Numero[11]=intval(substr($RecebeCPF,11-1,1));

			$soma=10*$Numero[1]+9*$Numero[2]+8*$Numero[3]+7*$Numero[4]+6*$Numero[5]+5*
			$Numero[6]+4*$Numero[7]+3*$Numero[8]+2*$Numero[9];
			$soma=$soma-(11*(intval($soma/11)));

			if ($soma==0 || $soma==1)
			{ $resultado1=0; }
			else
			{ $resultado1=11-$soma; }

			if ($resultado1==$Numero[10])
			{
				$soma=$Numero[1]*11+$Numero[2]*10+$Numero[3]*9+$Numero[4]*8+$Numero[5]*7+$Numero[6]*6+$Numero[7]*5+
				$Numero[8]*4+$Numero[9]*3+$Numero[10]*2;
				$soma=$soma-(11*(intval($soma/11)));

				if ($soma==0 || $soma==1)
				{ $resultado2=0; }
				else
				{ $resultado2=11-$soma; }
				if ($resultado2==$Numero[11])
				{ return TRUE;}
				else
				{ return 0; }
			}
			else
			{ return 0; }
	 }
    }
}

