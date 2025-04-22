<?php
//Include comumm de integração de PINs
require_once $raiz_do_projeto . "includes/inc_functions_card.php";
require_once $raiz_do_projeto . "consulta_cpf/config.inc.cpf.php";


function retorna_id_pin_card($pin,$id) {
	//instanciando a classe de cryptografia
	$chave256bits = new Chave();
	$aes = new AES($chave256bits->retornaChave());
	$sql = "select pin_codinterno from pins_card where pin_codigo = '".base64_encode($aes->encrypt(addslashes($pin)))."' and opr_codigo = ".addslashes($id).";";
	//echo $sql."<br>";
	$rs_log = SQLexecuteQuery($sql);
	if(!$rs_log) {
	}
	else { 
		$rs_log_row = pg_fetch_array($rs_log);
		if ($rs_log_row['pin_codinterno'] != '')
			return $rs_log_row['pin_codinterno'];
		// informa zero (0) quando nao foi encontrado -- ATENCAUN
		else return '0';
	}
}

function retorna_id_pin_card_para_adm_bo($pin) {
	//instanciando a classe de cryptografia
	$chave256bits = new Chave();
	$aes = new AES($chave256bits->retornaChave());
	$sql = "select pin_codinterno from pins_card where pin_codigo = '".base64_encode($aes->encrypt(addslashes($pin)))."';";
	//echo $sql."<br>";
	$rs_log = SQLexecuteQuery($sql);
	if(!$rs_log) {
	}
	else { 
		$rs_log_row = pg_fetch_array($rs_log);
		if ($rs_log_row['pin_codinterno'] != '')
			return $rs_log_row['pin_codinterno'];
		// informa zero (0) quando nao foi encontrado -- ATENCAUN
		else return '0';
	}
}

function retorna_status_card($pin,$id) {
	//instanciando a classe de cryptografia
	$chave256bits = new Chave();
	$aes = new AES($chave256bits->retornaChave());
	$sql = "select pin_status from pins_card where pin_codigo = '".base64_encode($aes->encrypt(addslashes($pin)))."' and opr_codigo = ".addslashes($id).";";
	//echo $sql."<br>";
	$rs_log = SQLexecuteQuery($sql);
	if(!$rs_log) {
	}
	else { 
		$rs_log_row = pg_fetch_array($rs_log);
		if ($rs_log_row['pin_status'] != '')
			return $rs_log_row['pin_status'];
		// informa zero (0) quando nao foi encontrado -- ATENCAUN
		else return '0';
	}
}

function retorna_pin_valor_card($pin,$id) {
	//instanciando a classe de cryptografia
	$chave256bits = new Chave();
	$aes = new AES($chave256bits->retornaChave());
	$sql = "select pin_valor from pins_card where pin_codigo = '".base64_encode($aes->encrypt(addslashes($pin)))."' and opr_codigo = ".addslashes($id).";";
	//echo $sql."<br>";
	$rs_log = SQLexecuteQuery($sql);
	if(!$rs_log) {
	}
	else { 
		$rs_log_row = pg_fetch_array($rs_log);
		if ($rs_log_row['pin_valor'] != '')
			return $rs_log_row['pin_valor'];
		// informa zero (0) quando nao foi encontrado -- ATENCAUN
		else return '0';
	}
}

function log_pin_card($codretepp,$pin,$id,$parametros,$valor) {
        $tmpTeste = retorna_id_pin_card(addslashes($pin),addslashes($id));
        if ($tmpTeste=='') 
                $aux_id_pin = '0';
        else $aux_id_pin = $tmpTeste;
	$sql = "INSERT INTO pins_integracao_card_historico VALUES (NOW(),'".retorna_ip_acesso()."',".$aux_id_pin.",".addslashes($id).",'".addslashes($codretepp)."',".retorna_status_card(addslashes($pin),addslashes($id)).",'".$parametros."',$valor)";
	//echo $sql."<br>";
	$rs_log = SQLexecuteQuery($sql);
	//var_dump($rs_log);
	if(!$rs_log) {
		 echo "<font color='#FF0000'><b>Erro na geração de LOG.</b></font><br>";
	}
}

function verifica_valor_pin_card($cod_pin,$valor,$id) {
	global $PINS_STORE_STATUS_VALUES,$PINS_STORE_MSG_LOG_STATUS;
	//instanciando a classe de cryptografia
	$chave256bits = new Chave();
	$aes = new AES($chave256bits->retornaChave());
	$sql = "select pin_codigo,pin_valor from pins_card where pin_codigo='".base64_encode($aes->encrypt(addslashes($cod_pin)))."' and pin_status='".intval($PINS_STORE_STATUS_VALUES['A'])."' and opr_codigo = ".addslashes($id).";";
	//echo $sql."<br>";
	$rs_oper = SQLexecuteQuery($sql);
	//sleep(1);
	if(!$rs_oper || pg_num_rows($rs_oper) == 0) {
		return false;
	} else {
		$rs_oper_row = pg_fetch_array($rs_oper);
		if ($rs_oper_row['pin_valor']==$valor) {
			return true;
		}
		else {
			return false;
		}
	}
}

function maskCard($val, $mask)
{
    $maskared = '';
    $k = 0;
    for($i = 0; $i<=strlen($mask)-1; $i++)
    {
        if($mask[$i] == '#')
        {
            if(isset($val[$k]))
                $maskared .= $val[$k++];
        }
        else
        {
            if(isset($mask[$i]))
                $maskared .= $mask[$i];
        }
    }
    return $maskared;
}//end function maskCard

function verificaCPF_Card($cpf) {
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
}//end function verificaCPF_Card


function fix_name_Card($str){
    $name = explode(' ', strtolower($str));
    foreach( $name as $k=>$n ){
        if(strlen($n)<=2)
            continue;
        
       $name[$k] = ucfirst($n);
    }
    return implode(' ', $name);
}//end function fix_name_Card($str)


function verificaNomeCard($nome) {

    $reg = '/^\\s*[a-zA-ZÀ-ú\']{1,}(\\s+[a-zA-ZÀ-ú\']{1,}\\s*)+$/';

    if (preg_match($reg, $nome) && strpos($nome, "  ") === false) {
        return TRUE;
    }
    return FALSE;

}//end function verificaNomeCard($nome)


function verificaCPFnaReceitaFederal($cpf, $pin, $id, $data_nascimento = null) {

    /*
     *  A T E N Ç Ã O:  Foi implementado RETURN 2 para verificação se o serviço está disponível
    */
    
    $name = null;
    
    if( !verificaCPF_Card($cpf) ) {
        return false;
    }//if( !verificaCPF_Card($cpf) )
    //ob_clean();

    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    //Novo modelo de Consulta
    $rs_api = new classCPF();

    //Testando se o serviço está disponível
    if(!$rs_api->get_service_status()) {
        return 2;
    }//end if($rs_api->get_service_status())

    else {
        
        $resposta = null;
        $parametros = array(
                            'cpfcnpj' => $cpf,
                            'data_nascimento' => $data_nascimento,
                            );
        $testeCPF = $rs_api->Req_EfetuaConsulta($parametros,$resposta);

        //Testando menor de idade minima
        if($testeCPF == 112) {
            
            return $testeCPF;
            
        }
        //Testando se o CPF consta na BlackList
        elseif($testeCPF == 299) {
            
            return 171;
            
        }//end if($testeCPF == 299)

        //Testando se ultrapassou o limite de utilização do mesmo CPF
        elseif ($testeCPF != 171) {
        
                if (CPF_PARTNER_ENVIRONMET == CPF_PARTNER_CREDIFY) { 

                        if($testeCPF == 0 && $resposta['resposta']['cpf']['situacao'] == CPF_SITUCAO_REGULAR){
                            $name = $resposta['resposta']['cpf']['nome'];
                        }

                        elseif($testeCPF == 1){
                            return 2;
                        }

                        elseif(is_null($testeCPF)){
                            return 2;
                        }

                        else {
                            return false;
                        }

                } // end if (CPF_PARTNER_ENVIRONMET == CPF_PARTNER_CREDIFY)
                elseif (CPF_PARTNER_ENVIRONMET == CPF_CONSULTA_CACHE) {

                        if($testeCPF == 2){
                            return 2;
                        }

                        elseif($testeCPF == 1){
                            $name = $resposta['pesquisas']['camposResposta']['nome'];
                            $data_nascimento = $resposta['pesquisas']['camposResposta']['data_nascimento'];
                        }

                        else {
                            return 2;
                        }

                } else {

                    if($testeCPF == 3 && $resposta['pesquisas']['camposResposta']['situacao'] == CPF_SITUCAO_REGULAR){
                        $name = $resposta['pesquisas']['camposResposta']['nome'];
                        $data_nascimento = $resposta['pesquisas']['camposResposta']['data_nascimento'];
                    }

                    elseif($testeCPF == 1){
                        return 2;
                    }

                    elseif($testeCPF == 9){
                        return 2;
                    }

                    elseif($testeCPF == 12){
                        return $testeCPF;
                    }

                    elseif(is_null($testeCPF)){
                        return 2;
                    }

                    else {
                        return false;
                    }


            }

        }//end elseif ($testeCPF != 171)

        // Atingiu o limite máximo de utilização do mesmo CPF
        else {

            return 171;

        }//end else do elseif ($testeCPF != 171)

        if(!is_null($name)){

            // Vamos certificar que extraimos apenas os numeros do CPF, para depois aplicarmos a mascara
            $matches = array();
            preg_match_all('!\d+!', $cpf, $matches);

            $cpf = implode('', $matches[0]);

            //Verificar tabela a ser salvo
            $sql = "INSERT INTO pins_integracao_card_cpf VALUES (".retorna_id_pin_card($pin,$id).",'". maskCard($cpf,'###.###.###-##')."', '".fix_name_Card($name)."', NOW(),to_date('".$data_nascimento."','DD/MM/YYYY'));";
            //die($sql);
            $res = SQLexecuteQuery($sql);

            $cmdtuples = pg_affected_rows($res);

            if($cmdtuples===1) {
                    return true;
            } else {
                    return false;
            }

        }//end if(!is_null($name))
        else {
            //retorna false se a estrutura do nome não está OK.
            return false;
        }
        
    }//end else do if(!$rs_api->get_service_status())

}//end function verificaCPFnaReceitaFederal()

//Função que verifica se o tamanho do PIN pertence à um PIN EPP CARD
function RetonaTamanhoPINEPPCARD($pin) {
	$tamanho = strlen(trim($pin));
	if($tamanho == $GLOBALS['PIN_CARD_TAMANHO']) {
		return true;
	}
	else {
		return false;
	}
}//end function RetonaTamanhoPINEPPCARD($pin)


//Função que bloqueia e retorna 0 se bloqueou o PIN envolvido com sucesso, caso contrário retorna 1
function flag_pin_card_test($pin,$id) {

	//variavel auxiliar para o retorno da função
	$aux_retorno = 1;

        if(RetonaTamanhoPINEPPCARD($pin)) {
                $aux_pin_codinterno = retorna_id_pin_card(addslashes($pin), addslashes($id));
                if($aux_pin_codinterno != '0') {

                        //Inicia transacao
                        $sql = "BEGIN TRANSACTION ";
                        //echo $sql."<br>";
                        $ret = SQLexecuteQuery($sql);
                        if($ret) {
                                $sql = "update pins_card set pin_bloqueio = 1 where pin_codinterno = ".$aux_pin_codinterno." and pin_bloqueio = 0 and opr_codigo = ".addslashes($id).";";
                                $ret2 = SQLexecuteQuery($sql);
                                //echo $sql."<br>";
                        
                                $cmdtuples = pg_affected_rows($ret2);
                                gravaLog_EPPCARD("Em flag_pin_test: $cmdtuples registros afetados.$sql");

                                if($cmdtuples===1) {
                                        $sql = "COMMIT TRANSACTION ";
                                        //echo $sql."<br>";
                                        $ret3 = SQLexecuteQuery($sql);
                                        if($ret3) $aux_retorno = 0;
                                } else {
                                        $sql = "ROLLBACK TRANSACTION ";
                                        //echo $sql."<br>";
                                        $ret3 = SQLexecuteQuery($sql);
                                }
                        }//end if($ret)

                }//end if($aux_pin_codinterno > 0)

        }//end if(RetonaTamanhoPINEPPCARD($pin)) 

	return $aux_retorno;

}//end function flag_pin_card_test


//Função que desbloqueia todos o PIN envolvido, somente será executada se houve bloqueio com sucesso, ou seja, dentro do if de bloqueio com sucesso
function flag_pin_card_unblock($pin,$id) {
    
        if(RetonaTamanhoPINEPPCARD($pin)) {
                $aux_pin_codinterno = retorna_id_pin_card(addslashes($pin), addslashes($id));
                if($aux_pin_codinterno != '0') {

                        $sql = "update pins_card set pin_bloqueio = 0 where pin_codinterno = ".$aux_pin_codinterno." and opr_codigo = ".addslashes($id).";";
                        //echo $sql."<br>";
                        $ret2 = SQLexecuteQuery($sql);
                        //echo "DESBLOQUEIO de PIN<br>";
                        gravaLog_EPPCARD("DESBLOQUEIO EPP CASH: $sql");
                        
                }//end if($aux_pin_codinterno > 0)

        }//end if(RetonaTamanhoPINEPPCASH) 

}//end function flag_pin_card_unblock()


function gravaLog_EPPCARD($mensagem){
	
		//Arquivo
		$file = $GLOBALS['raiz_do_projeto'] . "log/log_integracao_PIN_Cartao.txt";
	
		//Mensagem
		$mensagem =  str_repeat("-", 80).PHP_EOL.date('Y-m-d H:i:s'). " " .$GLOBALS['_SERVER']['SCRIPT_FILENAME'] . PHP_EOL . $mensagem . PHP_EOL;
		//Grava mensagem no arquivo
		if ($handle = fopen($file, 'a+')) {
			fwrite($handle, $mensagem);
			fclose($handle);
		} 
	
}//end function gravaLog_EPPCARD


function retornaID_Distibuidora($pin){
	
        return substr($pin,0,2);
	
}//end function retornaID_Distibuidora


function getmicrotimePINCard()
{
   list($usec, $sec) = explode(" ", microtime());
   return ((float)$usec + (float)$sec);
}

?>