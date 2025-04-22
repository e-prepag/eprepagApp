<?php
//Função que verifica se o publisher exige CPF do cliente de LANHouse
function checkingNeedCPF_LH($opr_codigo) {
    $sql_function ="SELECT opr_need_cpf_lh from operadoras where opr_codigo=".intval($opr_codigo).";";
    $rs_function = SQLexecuteQuery($sql_function);
    if($rs_function_row = pg_fetch_array($rs_function)) {
            $opr_need_cpf_lh = $rs_function_row['opr_need_cpf_lh'];
    }
    return $opr_need_cpf_lh;
}//end function checkingNeedCPF_LH

//Função que chama a página para inserir o CPF
function cpf_page(){

    $is_data_valid = verificaNome($GLOBALS['_SESSION']['NOME_CPF']) && verificaCPF_LH($GLOBALS['_SESSION']['CPF_LH']);

    if(!$is_data_valid ) {
        include($raiz_do_projeto . "includes/pdv/form_cpf");
        die();           
    }

}//end function cpf_page

////Valida estrutura de CPF
function verificaCPF_LH($cpf) {
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
}//end function verificaCPF_LH


//Valida estrutura de Nome
function verificaNome($nome) {

    $reg = '/^\\s*[a-zA-ZÀ-ú\']{1,}(\\s+[a-zA-ZÀ-ú\']{1,}\\s*)+$/';

    if (preg_match($reg, $nome) && strpos($nome, "  ") === false) {
        return TRUE;
    }
    return FALSE;

}//end function verificaNome

function integracao_layout($type, $data=false){
    global $GLOBALS;

    if( $type=="css" || $type=="includes" ){
        $url = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=="on") ? "s" : "") . '://' . $_SERVER['SERVER_NAME'];
        $html = "";
        $html .= '<link rel="stylesheet" href="'.$url.'/css/form_cpf.css" type="text/css" />';

        if(!isset($GLOBALS['jquery-1.11.3']) || $GLOBALS['jquery-1.11.3'] != 'on')
            $html .= PHP_EOL . '<script src="'.$url.'/prepag2/js/jquery-1.11.3.min.js"></script>';
        
        $html .= PHP_EOL . '<script src="'.$url.'/js/form_cpf_valida.js"></script>';
        return $html;
    }

}//end function integracao_layout

function mask($val, $mask)
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
}//end function mask

//Função que verifica se o publisher exige CPF do cliente de LANHouse
function checkingIsCompletedData($url_preview) {
    //Variavel retorna necessidade de solicitação de CPF de cliente por parte da LAN House
    $test_opr_need_cpf_lh = false;
    if(!empty($GLOBALS['_SESSION']['dist_carrinho'])){
        //Recupera carrinho do session
        $carrinho = $GLOBALS['_SESSION']['dist_carrinho'];
        foreach ($carrinho as $modeloId => $qtde){
            $rs = null;
            $opr_codigo = 0;
            if(!empty($modeloId)) { 
                if($modeloId != $GLOBALS["NO_HAVE"]){
                    $filtro['ogpm_ativo'] = 1;
                    $filtro['ogpm_id'] = $modeloId;
                    $filtro['com_produto'] = true;
                    $instProdutoModelo = new ProdutoModelo;
                    $ret = $instProdutoModelo->obter($filtro, null, $rs);
                    if($rs && pg_num_rows($rs) != 0) {
                            $rs_row = pg_fetch_array($rs);
                            $opr_codigo = $rs_row['ogp_opr_codigo'];
                    }
                }else{
                    foreach ($qtde as $codeProd => $vetor_valor) {
                        foreach ($vetor_valor as $valor => $quantidade) {
                            $filtro['ogp_ativo'] = 1;
                            $filtro['ogp_id'] = $codeProd;
                            $filtro['opr'] = 1;
                            $ret = (new Produto)->obtermelhorado($filtro, null, $rs);

                            if(!$rs || pg_num_rows($rs) == 0) $msg = "Nenhum produto disponível no momento.";
                            else $rs_row = pg_fetch_array($rs);

                            $opr_codigo = $rs_row["ogp_opr_codigo"];
                        }
                    }
                }
                //Verificando se exige CPF de cliente
                if(!$test_opr_need_cpf_lh) {
                    $test_opr_need_cpf_lh = checkingNeedCPF_LH($opr_codigo);
                }//end if(!$test_opr_need_cpf_lh)
            } //end if(!empty($modeloId))
        }//end foreach

    }
    
    $is_data_valid = verificaNome($GLOBALS['_SESSION']['NOME_CPF']) && verificaCPF_LH($GLOBALS['_SESSION']['CPF_LH']);
    if($test_opr_need_cpf_lh && !$is_data_valid) {
        header('Location: '.$url_preview);
        die();
    }//end if($test_opr_need_cpf_lh && !$is_data_valid)
    
}//end function checkingNeedCPF_LH

//Função que verifica se o publisher exige CPF do cliente de LANHouse e exibe a página
function checkingIsCallFormCPF() {
    //Recupera carrinho do session
    $carrinho = $GLOBALS['_SESSION']['dist_carrinho'];
    //Variavel retorna necessidade de solicitação de CPF de cliente por parte da LAN House
    $test_opr_need_cpf_lh = false;
    foreach ($carrinho as $modeloId => $qtde){
        $rs = null;
        $opr_codigo = 0;
        if(!empty($modeloId)) { 
            $filtro['ogpm_ativo'] = 1;
            $filtro['ogpm_id'] = $modeloId;
            $filtro['com_produto'] = true;
            $ret = ProdutoModelo::obter($filtro, null, $rs);
            if($rs && pg_num_rows($rs) != 0) {
                    $rs_row = pg_fetch_array($rs);
                    $opr_codigo = $rs_row['ogp_opr_codigo'];
            }
            //Verificando se exige CPF de cliente
            if(!$test_opr_need_cpf_lh) {
                $test_opr_need_cpf_lh = checkingNeedCPF_LH($opr_codigo);
            }//end if(!$test_opr_need_cpf_lh)
        } //end if(!empty($modeloId)) 
    }//end foreach
    $is_data_valid = verificaNome($GLOBALS['_SESSION']['NOME_CPF']) && verificaCPF_LH($GLOBALS['_SESSION']['CPF_LH']);
    if($test_opr_need_cpf_lh && !$is_data_valid) {
        include $raiz_do_projeto . 'includes/pdv/form_cpf.php';
        die();
    }//end if($test_opr_need_cpf_lh && !$is_data_valid)
    
}//end function checkingIsCallFormCPF

		
?>