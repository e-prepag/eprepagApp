<?php
ob_start();
require_once "../../../includes/constantes.php";   
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "pdv/main.php";
require_once DIR_INCS . "pdv/corte_classPrincipal.php";
require_once DIR_CLASS . "pdv/classOperadorGamesUsuario.php";
header("Content-Type: text/html; charset=ISO-8859-1",true);
$_PaginaOperador1Permitido = 53; // o número magico 
$_PaginaOperador2Permitido = 54; 

validaSessao(); 

//login
$usuarioGames = unserialize($_SESSION['dist_usuarioGames_ser']);
$usuario_id = $usuarioGames->getId();

//Validacao
//------------------------------------------------------------------------------------------------------------------
$msg = "";
$msgFatal = "";
$str_redirect = "";

//Valida estabelecimento
if($msg == "" && $msgFatal == "")
        if(!$usuario_id || !is_numeric($usuario_id) || trim($usuario_id) == "") $msgFatal = "Código do usuário inválido.\n";

//Valida codigo do boleto
if($msg == ""){
        if(!$bbc_boleto_codigo || trim($bbc_boleto_codigo) == "" || !is_numeric($bbc_boleto_codigo)) $msg = "Código do boleto inválido.\n";
}


//Busca dados do boleto
if($msg == ""){
        $sql = "select * from boleto_bancario_cortes bbc
                        inner join cortes c on c.cor_codigo = bbc.bbc_cor_codigo
                        where bbc.bbc_boleto_codigo = $bbc_boleto_codigo
                                and bbc.bbc_ug_id = $usuario_id";
        $rs_boleto = SQLexecuteQuery($sql);
        if(!$rs_boleto || pg_num_rows($rs_boleto) == 0) $msg = "Erro ao buscar boleto.\n";
        else {
                $rs_boleto_row = pg_fetch_array($rs_boleto);
                $bbc_bco_codigo = $rs_boleto_row['bbc_bco_codigo'];
                
                //Cambiarra para aparecer o logo do banco no boleto imagem
                $bbg_bco_codigo = $bbc_bco_codigo;
                
                $bbc_documento 	= $rs_boleto_row['bbc_documento'];
                $bbc_valor 		= $rs_boleto_row['bbc_valor'];
                $bbc_valor_taxa = $rs_boleto_row['bbc_valor_taxa'];
                $bbc_data_venc 	= $rs_boleto_row['bbc_data_venc'];
                $bbc_ug_id 		= $rs_boleto_row['bbc_ug_id'];
                $cor_periodo_ini = $rs_boleto_row['cor_periodo_ini'];
                $cor_periodo_fim = $rs_boleto_row['cor_periodo_fim'];

                //Validacoes
                //-----------------------------------------------------------------------------------------------------
                //Banco
                if($bbc_bco_codigo != $GLOBALS['BOLETO_COD_BANCO_BRADESCO']) $msg = "Boleto não é do Bradesco.\n";

                //usuario
                if(!$bbc_ug_id || trim($bbc_ug_id) == "" || !is_numeric($bbc_ug_id)) $msg = "Código do usuário inválido.\n";
        }
}

//Obtem estabelecimento
if($msg == ""){
        $sql  = "select * from dist_usuarios_games ug where ug.ug_id = " . $bbc_ug_id;
        $rs_estab = SQLexecuteQuery($sql);
        if(!$rs_estab || pg_num_rows($rs_estab) == 0) $msg = "Nenhum usuário encontrado.\n";
        else {
                $rs_estab_row = pg_fetch_array($rs_estab);

                $ug_tipo_cadastro 	= $rs_estab_row['ug_tipo_cadastro'];
                $ug_razao_social 	= $rs_estab_row['ug_razao_social'];
                $ug_cpf 		= $rs_estab_row['ug_cpf'];
                $ug_cnpj		= $rs_estab_row['ug_cnpj'];
                $ug_nome 		= $rs_estab_row['ug_nome'];
                $ug_tipo_end = $rs_estab_row['ug_tipo_end'];
                $ug_endereco = $ug_tipo_end.": ".$rs_estab_row['ug_endereco'];
                $ug_endereco_logradouro = $ug_endereco;
                $numero 		= $rs_estab_row['ug_numero'];
                $ug_numero 		= $numero;
                if(trim($numero) != "") $ug_endereco .= ", " . trim($numero);
                $complemento	= $rs_estab_row['ug_complemento'];
                $ug_complemento		= $complemento;
                if(trim($complemento) != "") $ug_endereco .= " - " . trim($complemento);
                $bairro 		= $rs_estab_row['ug_bairro'];
                $ug_bairro 		= $bairro;
                $municipio 		= $rs_estab_row['ug_cidade'];
                $ug_cidade 		= $municipio;
                if(trim($bairro) != "") $bairro .= " - " . trim($municipio);
                $uf 			= $rs_estab_row['ug_estado'];
                $ug_estado		= $uf;
                $cep 			= str_replace("-","",$rs_estab_row['ug_cep']);
                $ug_cep = $cep;
                $mask = $cep;
                $var1 = substr("$mask", 0,5);
                $var2 = substr("$mask", 5,8);  
                $cep = $var1."-".$var2;
                
//                 o bloco abaixo serve para se adequar a nova norma da febraban de boleto registrado
                if($rs_estab_row['ug_tipo_cadastro'] == "PF")
                {
                    $rs_estab_row['ug_cpf'] = str_replace(array(".", ","), "",$rs_estab_row['ug_cpf']);
                    $sacado = $rs_estab_row['ug_nome']." - CPF: ".mascara_cnpj_cpf($rs_estab_row['ug_cpf'],"cpf");
                    $dadosboleto["tipo_documento"] = "1";
                    $dadosboleto["nome_pagador"] = $ug_nome;
                    $dadosboleto["sacado"] = $ug_nome;
                }
                else
                {
                    $rs_estab_row['ug_cnpj'] = str_replace(array(".", ","), "",$rs_estab_row['ug_cnpj']);
                    if(!empty($ug_razao_social)){
                        $dadosboleto["nome_pagador"] = $ug_razao_social;
                        $sacado = $rs_estab_row['ug_razao_social']." - CNPJ: ".mascara_cnpj_cpf($rs_estab_row['ug_cnpj'],"cnpj");
                    } else{
                        if(!empty($ug_nome_fantasia)){
                            $dadosboleto["nome_pagador"] = $ug_nome_fantasia;
                            $sacado = $rs_estab_row['ug_nome_fantasia']." - CNPJ: ".mascara_cnpj_cpf($rs_estab_row['ug_cnpj'],"cnpj");
                        } else {
                            $dadosboleto["nome_pagador"] = $ug_nome;
                            $sacado = $rs_estab_row['ug_nome']." - CNPJ: ".mascara_cnpj_cpf($rs_estab_row['ug_cnpj'],"cnpj");
                        }

                    }

                    $dadosboleto["tipo_documento"] = "2";

                }
        }
}

//loga acesso
if($msg == ""){
        $sql = "insert into boleto_bancario_cortes_acessos(bbca_data_inclusao, bbca_ip,	bbca_ug_id, bbca_bbc_boleto_codigo) values (";
        $sql .= "CURRENT_TIMESTAMP,'" . $_SERVER["REMOTE_ADDR"] . "', $usuario_id, $bbc_boleto_codigo)";
        $ret = SQLexecuteQuery($sql);
        if(!$ret) $msg = "";
}

//gera boleto
if($msg == ""){
        // DADOS DO BOLETO PARA O SEU CLIENTE
        $data_venc 		= formata_data($bbc_data_venc, 0); 
        $taxa_boleto 	= $bbc_valor_taxa;
        $valor_boleto 	= number_format($bbc_valor, 2, ',', '');
        $num_doc 		= $bbc_documento;
        //$sacado 		= $razao_social;
        $periodo_ini	= $cor_periodo_ini;
        $periodo_fim	= $cor_periodo_fim;


        // NÃO ALTERAR!
        //ob_clean();
        include RAIZ_DO_PROJETO . "banco/boletos/include/funcoes_bradesco_fixo_corte.php";
        //include $GLOBALS['raiz_do_projeto'] . "/www/web/SICOB2/include/layout_bradesco.php";
        
        
        if($dadosboleto["tipo_documento"] == "1"){
            $dadosboleto["documento_pagador"] = $ug_cpf;
        } else{
            $dadosboleto["documento_pagador"] = $ug_cnpj;
        }

        $dadosboleto["cep_pagador"] = preg_replace('/[^0-9]/', '', $ug_cep);
        $dadosboleto["logradouro_pagador"] = $ug_endereco_logradouro;
        $dadosboleto["numero_pagador"] = $ug_numero;

        $dadosboleto["complemento_pagador"] = $ug_complemento;

        $dadosboleto["bairro_pagador"] = $ug_bairro;
        $dadosboleto["cidade_pagador"] = $ug_cidade;
        $dadosboleto["uf_pagador"] = $ug_estado;
        $dadosboleto["cpfcnpj"] = preg_replace('/[^0-9]/', '', $dadosboleto["documento_pagador"]);
        
        //Aplicando date() as datas de vencimento e emissao para fazer a comparação [(strtotime($date_vencimento) < strtotime($date_emissao))]
        $date_vencimento = date('Y-m-d', strtotime(str_replace('/', '-', $dadosboleto["data_vencimento"])));

        $date_emissao = date('Y-m-d', strtotime(str_replace('/', '-', $dadosboleto["data_documento"])));
        
        //Validando campos preenchidos
        if(empty($dadosboleto["cep_pagador"]) ||
           empty($dadosboleto["logradouro_pagador"]) ||
           (!isset($dadosboleto["numero_pagador"]) || $dadosboleto["numero_pagador"] =="" || $dadosboleto["numero_pagador"] ==" ") ||
           empty($dadosboleto["bairro_pagador"]) ||
           empty($dadosboleto["cidade_pagador"]) || 
           empty($dadosboleto["uf_pagador"]) || 
           empty($dadosboleto["cpfcnpj"])){
                
            $msg = "Por favor preencha seus dados de Endereço antes de gerar o boleto!<br>Entre em contato com o suporte da E-Prepag e atualize seu cadastro.";
            ?>
                <form name="pagamento" id="pagamento" method="POST" action="/creditos/mensagem.php">
                    <input type='hidden' name='msg' id='msg' value='<?php echo $msg; ?>'>
                    <input type='hidden' name='titulo' id='titulo' value='Preencha os Dados de Endereço'>
                    <input type='hidden' name='link' id='link' value='/creditos/meu_cadastro.php'>
                </form>
                <script language='javascript'>
                    document.getElementById("pagamento").submit();
                </script>
        <?php
                die();
        }//end emptys
        // Validando a data de vencimento (nao pode ser menor que a data de emissao)
        elseif(strtotime($date_vencimento) < strtotime($date_emissao) && (strtotime($date_vencimento) != FALSE && strtotime($date_emissao) != FALSE)){
            $msg = 'Você não pode visualizar este boleto, pois ele está vencido. Por favor, efetue um depósito ou transferência para uma das contas abaixo e envie uma cópia do seu comprovante para <a href="mailto:suporte@e-prepag.com.br" target="_blank">suporte@e-prepag.com.br</a>.<br><br>Bradesco<br>Agência: 2062<br>Conta Corrente: 1689-6<br>Titular: E-PREPAG ADMINISTRADORA DE CARTOES LTDA<br>Cnpj: 19.037.276/0001-72<br><br>Banco do Brasil<br>Agência: 4328-1<br>Conta Corrente: 2978-5<br>Titular: E-PREPAG ADMINISTRADORA DE CARTOES LTDA<br>Cnpj: 19.037.276/0001-72<br><br><strong>Atenção: Envie seu comprovante para nosso suporte para que seu limite seja liberado.</strong>';
            $assunt = (checkIP()?"[DEV] ":"[PROD] "). "E-Prepag - Problema com Boleto Expirado - PDV POS PAGO";
             enviaEmail("luis.gustavo@e-prepag.com.br, wagner@e-prepag.com.br, suporte@e-prepag.com.br", null, null, $assunt, "Usuário ID: ". $usuario_id . "<br>Email: " .$usuarioGames->getEmail() . "<br><br>Data Emissão: ". $dadosboleto["data_documento"] . "<br>Data Vencimento: <strong>". $dadosboleto["data_vencimento"]. "</strong><br>Valor Boleto: ". $dadosboleto["valor_boleto"]);
        ?>    
            <form name="pagamento" id="pagamento" method="POST" action="/creditos/mensagem.php">
                <input type='hidden' name='msg' id='msg' value='<?php echo $msg; ?>'>
                <input type='hidden' name='titulo' id='titulo' value='Boleto Expirado'>
                <input type='hidden' name='link' id='link' value='/creditos/index.php'>
            </form>
            <script language='javascript'>
                document.getElementById("pagamento").submit();
            </script>
        <?php
            die();
        }//end elseif(strtotime($dadosboleto["data_vencimento"]) < strtotime($dadosboleto["data_documento"]))
        
        require_once RAIZ_DO_PROJETO . "banco/boletos/boleto_regitrado/bradesco/config.inc.bradesco.php";

        $boleto =  array(
                         'nosso_numero' => $dadosboleto["numero_documento"],
                         'numero_documento' => $dadosboleto['nosso_numero'], 
                         'data_emissao' => formata_data($dadosboleto["data_documento"],"1"), 
                         'data_vencimento' => formata_data($dadosboleto["data_vencimento"],"1"), 
                         'valor_titulo' => preg_replace('/[^0-9]/', '', $dadosboleto["valor_boleto"]) ,
                         'pagador' => array(
                                            'id' => $usuario_id,
                                            'nome' => substr($dadosboleto["nome_pagador"],0,150), 
                                            'documento' => $dadosboleto["cpfcnpj"], 
                                            'tipo_documento' => $dadosboleto["tipo_documento"], 
                                            'endereco' => array(
                                                                'id' => $usuario_id,
                                                                'cep' => $dadosboleto["cep_pagador"] , 
                                                                'logradouro' => substr($dadosboleto["logradouro_pagador"], 0, 70) , 
                                                                'numero' => substr($dadosboleto["numero_pagador"], 0, 10) , 
                                                                'complemento' => substr($dadosboleto["complemento_pagador"], 0, 20) ,
                                                                'bairro' => substr($dadosboleto["bairro_pagador"], 0, 50) , 
                                                                'cidade' => substr($dadosboleto["cidade_pagador"], 0, 100) , 
                                                                'uf' => $dadosboleto["uf_pagador"]
                                                                )
                                            )
                        );

        $t = new classBradesco();
        $lista_resposta = NULL;
        array_walk_recursive(
            $boleto,
            function (&$entry) {
                $entry = utf8_decode(
                    $entry
                );
            }
        );
        $codigo = $t->Req_EfetuaConsultaRegistro($boleto, $lista_resposta);

        if(!in_array($codigo, $BRADESCO_CODE_SUCESS)){
            $assunto1 = (checkIP()?"[DEV] ":"[PROD] ")."E-Prepag - Problema ao Registrar Boleto Bradesco - PDV POS PAGO";
            
           enviaEmail("luis.gustavo@e-prepag.com.br,wagner@e-prepag.com.br, suporte@e-prepag.com.br", null, null, $assunto1, "Na tentativa do registro tivemos o seguinte retorno:<br>" .(!is_null($codigo)?$BRADESCO_CODE_ERRORS_REGISTRO[$codigo]:"NULL"). "<br><br>ID Usuário: ".$usuario_id. "<br>" . "<pre>".print_r($boleto, true)."</pre>");
           $msg = "Tivemos problema de comunicação com o Banco!<br>Aguarde alguns instantes e tente novamente.<br>Obrigado!";
            ?>
                <form name="pagamento" id="pagamento" method="POST" action="/creditos/mensagem.php">
                    <input type='hidden' name='msg' id='msg' value='<?php echo $msg; ?>'>
                    <input type='hidden' name='titulo' id='titulo' value='Problema de comunicação com o Banco'>
                </form>
                <script language='javascript'>
                    document.getElementById("pagamento").submit();
                </script>       
        <?php
                die();
        } 
        
        ob_clean();
        include RAIZ_DO_PROJETO . "banco/boletos/include/boleto_to_image/boleto_imagem.php";

}
?>
