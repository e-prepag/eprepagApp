<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
//ob_clean();
set_time_limit(120);
require_once $raiz_do_projeto . "consulta_cpf/config.inc.cpf.php";

//Include do modelo antigo
//include 'C:\Sites\E-Prepag\www\web\prepag2\incs\rf_cpf\funcoes.php';

require_once $raiz_do_projeto . 'includes/functions.php';
require_once $raiz_do_projeto . "consulta_cpf/trocaAutomatica.php";              
require_once '/www/includes/pdv/functions.php';	
					
$errors = array();
            
if( isset($_REQUEST['formsubmit']) ){
    
    if( !verificaCPF_LH($_REQUEST['cpf']) )
        $errors[] = "CPF inválido, por favor revise o número digitado.";
    
    //ob_clean();
    $_REQUEST['cpf'] = preg_replace('/[^0-9]/', '', $_REQUEST['cpf']);
	
	$ff = fopen("/www/log/ttcpf.txt", "a+");
	fwrite($ff, $_REQUEST['cpf']."\r");
	fclose($ff);

    //Novo modelo de Consulta
	
	$contagemErroDia = verificaContagem();
	if($contagemErroDia["contagem"] != false && $contagemErroDia["contagem"] >= 5){
		 trocaOrigemAutomatica(3);
	}
	
    $rs_api = new classCPF();
    $resposta = null;
    $parametros = array(
                        'cpfcnpj' => $_REQUEST['cpf'],
                        'data_nascimento' => (!empty($_REQUEST['data_nascimento'])?$_REQUEST['data_nascimento']:null)
                        );
    $testeCPF = $rs_api->Req_EfetuaConsulta($parametros,$resposta);
    
    //var_dump($testeCPF); die;
    //var_dump($resposta); die;
    
    //Verificação de idade mínima 
    if($testeCPF == 112){
        $errors[] = "O produto " . $GLOBALS["produto_idade_minima"] . " é destinado para maiores de " . $GLOBALS["IDADE_MINIMA"] . " anos. Esta venda só poderá ser concluída caso o(a) usuário(a) informe o CPF e a data de nascimento dos pais ou responsável.";
    }
        
    //Testando se o CPF consta na BlackList
    elseif($testeCPF == 299) {
        $errors[] = "Existem pendências de documentos relacionadas ao seu CPF. Por gentileza entre em contato com suporte@e-prepag.com.br para desbloqueio.<br> Como empresa de serviços financeiros, a E-prepag trabalha para manter um ambiente seguro para todos, e conta com a sua colaboração. Erro 299";
    }

    //Testando se ultrapassou o limite de utilização do mesmo CPF
    elseif ($testeCPF != 171) {
        
            if (CPF_PARTNER_ENVIRONMET == CPF_PARTNER_CREDIFY) { 

                    if($testeCPF == 2){
                        $errors[] = "Este número de CPF parece não constar na Receita Federal. Por favor, verifique o número digitado e tente novamente.";
                    }

                    elseif($testeCPF == 1){
                        $errors[] = "Não foi possível realizar consulta. Erro(9191). Por favor, entre em contato com suporte@e-prepag.com.br reportando o código do problema. Obrigado.";
						qtdeTrocaAutomatica();
                    }

                    elseif(is_null($testeCPF)){
                        $errors[] = "Erro no sistema (0034). Por favor, entre em contato com suporte@e-prepag.com.br reportando o código do problema. Obrigado.";
					    qtdeTrocaAutomatica();
                    }

                    elseif($testeCPF == 0 && $resposta['resposta']['cpf']['situacao'] != CPF_SITUCAO_REGULAR) {
                        $errors[] = "CPF não está regular junto a Receita Federal. Por favor, verifique o número digitado e tente novamente.";
                    }

                    elseif(!isset($resposta['resposta']['cpf']['nome'])){
                        $errors[] = "Este número de CPF parece não constar na Receita Federal. Por favor, verifique o número digitado e tente novamente.";
                    }

                    elseif($testeCPF == 0 && $resposta['resposta']['cpf']['situacao'] == CPF_SITUCAO_REGULAR){
                        $name = $resposta['resposta']['cpf']['nome'];
                    }

                    else {
                        $errors[] = "Erro no sistema (0407). Por favor, entre em contato com suporte@e-prepag.com.br reportando o código do problema. Obrigado.";
						qtdeTrocaAutomatica();
                    }

            } // end if (CPF_PARTNER_ENVIRONMET == CPF_PARTNER_CREDIFY)
				
			elseif(CPF_PARTNER_ENVIRONMET == CPF_CONSULTA_HUB){
					
				$file = fopen("/www/log/retorno_cpf.txt", "a+");
				fwrite($file, "DATA ".date("d-m-Y H:i:s")."\n");
				fwrite($file, "codigo cpf: ".$testeCPF."\n");
				fwrite($file, "resultado: ".json_encode($resposta)."\n");
				fwrite($file, str_repeat("*", 50)."\n"); 
				fclose($file);	
					
				if($testeCPF == 2){
					$errors[] = "Este número de CPF parece não constar na Receita Federal. Por favor, verifique o número digitado e tente novamente.";
				}

				elseif($testeCPF == 1){
					$errors[] = "Não foi possível realizar consulta. Erro(9191). Por favor, entre em contato com suporte@e-prepag.com.br reportando o código do problema. Obrigado.";
					qtdeTrocaAutomatica();
				}

				elseif(is_null($testeCPF)){
					$errors[] = "Erro no sistema (0034). Por favor, entre em contato com suporte@e-prepag.com.br reportando o código do problema. Obrigado.";
					qtdeTrocaAutomatica();
				}

				elseif($testeCPF == 0 && $resposta['result']['situacao_cadastral'] != CPF_SITUCAO_REGULAR) {
					$errors[] = "CPF não está regular junto a Receita Federal. Por favor, verifique o número digitado e tente novamente.";
				}

				elseif(!isset($resposta['result']['nome_da_pf'])){
					$errors[] = "Este número de CPF parece não constar na Receita Federal. Por favor, verifique o número digitado e tente novamente.";
				}

				elseif($testeCPF == 0 && $resposta['result']['situacao_cadastral'] == CPF_SITUCAO_REGULAR){
					$retorno["nome"] = $resposta['result']['nome_da_pf'];
					$retorno["data_nascimento"] = $resposta['result']['data_nascimento'];
					$name = $retorno["nome"];
					$data_nascimento = $retorno["data_nascimento"];
					
				}

				else {
					$errors[] = "Erro no sistema (0407). Por favor, entre em contato com suporte@e-prepag.com.br reportando o código do problema. Obrigado.";
					qtdeTrocaAutomatica();
				}
					
			}
            elseif (CPF_PARTNER_ENVIRONMET == CPF_PARTNER_OMNIDATA) {
				
				    $file = fopen("/www/log/retorno_cpf.txt", "a+");
					fwrite($file, "DATA ".date("d-m-Y H:i:s")."\n");
					fwrite($file, "parametros ".json_encode($parametros)."\n");
					fwrite($file, "resultado PASSO 4 ".$testeCPF."\n");
					fwrite($file, str_repeat("*", 50)."\n"); 
					fclose($file);
					
					//var_dump($testeCPF);
					//var_dump($resposta);
                    if($testeCPF == 2 || $testeCPF == 8){
                        $errors[] = "Este número de CPF parece não constar na Receita Federal. Por favor, verifique o número digitado e tente novamente.";
                    }

                    elseif($testeCPF == 1){
                        $errors[] = "Não foi possível realizar consulta. Erro(9191). Por favor, entre em contato com suporte@e-prepag.com.br reportando o código do problema. Obrigado.";
						qtdeTrocaAutomatica();
                    }

                    elseif($testeCPF == 9){
                        $errors[] = "Não foi possível realizar consulta. Erro(9355). Por favor, tente novamente. Se o problema persistir entre em contato com suporte@e-prepag.com.br reportando o código do problema. Obrigado.";
						qtdeTrocaAutomatica();
                    }

                    elseif($testeCPF == 12){
                        $errors[] = "A Data de Nascimento informada é diferente do que consta nos dados da Receita. Por favor, insira a data de nascimento do CPF informado.";
                    }

                    elseif(is_null($testeCPF)){
                        $errors[] = "Erro no sistema (0034). Por favor, entre em contato com suporte@e-prepag.com.br reportando o código do problema. Obrigado.";
						qtdeTrocaAutomatica();
                    }

                    elseif($testeCPF == 3 && $resposta['pesquisas']['camposResposta']['situacao'] != CPF_SITUCAO_REGULAR) {
                        $errors[] = "CPF não está regular junto a Receita Federal. Por favor, verifique o número digitado e tente novamente.";
                    }

                    elseif(!isset($resposta['pesquisas']['camposResposta']['nome'])){
                        $errors[] = "Este número de CPF parece não constar na Receita Federal. Por favor, verifique o número digitado e tente novamente.";
                    }

                    elseif($testeCPF == 3 && $resposta['pesquisas']['camposResposta']['situacao'] == CPF_SITUCAO_REGULAR){
                        $name = $resposta['pesquisas']['camposResposta']['nome'];
                        $data_nascimento = $resposta['pesquisas']['camposResposta']['data_nascimento'];
                    }

                    else {
                        $errors[] = "Erro no sistema [".$resposta['pesquisas']['msg']."] (0407). Por favor, entre em contato com suporte@e-prepag.com.br reportando o código do problema. Obrigado.";
						qtdeTrocaAutomatica();
                    }


            }//end elseif (CPF_PARTNER_ENVIRONMET == CPF_PARTNER_OMNIDATA)   

            elseif (CPF_PARTNER_ENVIRONMET == CPF_CONSULTA_CACHE) {

                    if($testeCPF == 2){
                        $errors[] = "Estamos momentaneamente com falha na comunição para verificação do CPF informado. Por favor, aguarde alguns minutos e tente novamente.";
                    }

                    elseif($testeCPF == 1){
                        $name = $resposta['pesquisas']['camposResposta']['nome'];
                        $data_nascimento = $resposta['pesquisas']['camposResposta']['data_nascimento'];
                    }

                    else {
                        $errors[] = "Erro no sistema [".$resposta['pesquisas']['msg']."] (0485). Por favor, entre em contato com suporte@e-prepag.com.br reportando o código do problema. Obrigado.";
                    }

            } //end  elseif (CPF_PARTNER_ENVIRONMET == CPF_CONSULTA_CACHE) 
			
			$file = fopen("/www/log/retorno_cpf.txt", "a+");
			fwrite($file, "hud do desenvolvedor \n");
			fwrite($file, "resultado code ".$testeCPF."\n");
			fwrite($file, "resultado json ".json_encode($resposta)."\n");
			fwrite($file, "retorno json ".json_encode($retorno)."\n");
			fwrite($file, str_repeat("*", 50)); 
			fclose($file);
            
    }//end elseif ($testeCPF != 171)
    
    // Atingiu o limite máximo de utilização do mesmo CPF
    else {

            $errors[] = "Este CPF está temporariamente desabilitado para compras pois atingiu o limite máximo de utilização. Por favor, confirme o número do CPF com seu cliente. Erro 171";
        
    }//end else do elseif ($testeCPF != 171)

    if( count($errors)==0 ){
        
        // Vamos certificar que extraimos apenas os numeros do CPF, para depois aplicarmos a mascara
        $matches = array();
        preg_match_all('!\d+!', $_REQUEST['cpf'], $matches);
        
        $cpf = implode('', $matches[0]);
        $GLOBALS['_SESSION']['CPF_LH'] = mask($cpf,'###.###.###-##');
        $GLOBALS['_SESSION']['NOME_CPF'] = fix_name($name);
        $GLOBALS['_SESSION']['DATA_NASCIMENTO'] = $data_nascimento;

        
		
		header('Location: ' . $GLOBALS['_SERVER']['PHP_SELF']);
		
    }
       
}

$form_name = isset($_REQUEST['name']) ? $_REQUEST['name'] : $GLOBALS['_SESSION']['NOME_CPF'];
$form_cpf = isset($_REQUEST['cpf']) ? $_REQUEST['cpf'] : $GLOBALS['_SESSION']['CPF_LH'];
$form_data_nascimento = isset($_REQUEST['data_nascimento']) ? $_REQUEST['data_nascimento'] : $GLOBALS['_SESSION']['DATA_NASCIMENTO'];
//echo $form_name ."--".$form_cpf;
if(!isset($_REQUEST['formsubmit'])){
    $form_name = "";
    $form_cpf = "";
    $form_data_nascimento = "";
}

//$name_valid = verificaNome($form_name);
//$cpf_valid  = verificaCPF_LH($form_cpf);

$server_url = $_SERVER['SERVER_NAME'];
    
$retorno = "<div id='popup_cpf' align='left' title=''>
                            <script type='text/javascript'>
                                function Trim(str){
                                    return str.replace(/^\\s+|\\s+$/g,'');
                                }
                                function validaform() {
                                        var strDtNasc = document.frmPreCadastro.data_nascimento.value;
                                        if(strDtNasc.length == '10'){
                                            var dtNasc = strDtNasc.split('/');
                                            var objDtNasc = new Date(parseInt(dtNasc[2]),parseInt(dtNasc[1])-1,parseInt(dtNasc[0]));
                                            if(objDtNasc.getTime() > currentDate.getTime()){
                                                document.frmPreCadastro.data_nascimento.focus();
                                                document.frmPreCadastro.data_nascimento.select();
                                                return false;
                                            }
                                        }

                      			if (document.frmPreCadastro.ug_cpf.value == '') {
                                                alert('Informe o CPF');
                                                document.frmPreCadastro.ug_cpf.focus();
                                                document.frmPreCadastro.ug_cpf.select();
                                                return false;
                                        }
                                        else if(!validaRespostaCPF(document.frmPreCadastro.ug_cpf.value)) {
                                                alert('CPF inválido, por favor revise o número digitado.');
                                                document.frmPreCadastro.ug_cpf.focus();
                                                document.frmPreCadastro.ug_cpf.select();
                                                return false;
                                        }
                                        else return true;
                                }//end function validaform()

                                function validaRespostaCPF(cpf) {
                                    cpf = cpf.replace(/[^\d]+/g,'');
                                    if(cpf == '') return false;

                                    // Elimina CPFs invalidos conhecidos
                                    if (cpf.length != 11 ||
                                            cpf == '00000000000' ||
                                            cpf == '11111111111' ||
                                            cpf == '22222222222' ||
                                            cpf == '33333333333' ||
                                            cpf == '44444444444' ||
                                            cpf == '55555555555' ||
                                            cpf == '66666666666' ||
                                            cpf == '77777777777' ||
                                            cpf == '88888888888' ||
                                            cpf == '99999999999')
                                            return false;

                                    // Valida 1o digito
                                    add = 0;
                                    for (i=0; i < 9; i ++)
                                            add += parseInt(cpf.charAt(i)) * (10 - i);
                                    rev = 11 - (add % 11);
                                    if (rev == 10 || rev == 11)
                                            rev = 0;
                                    if (rev != parseInt(cpf.charAt(9)))
                                            return false;

                                    // Valida 2o digito
                                    add = 0;
                                    for (i = 0; i < 10; i ++)
                                            add += parseInt(cpf.charAt(i)) * (11 - i);
                                    rev = 11 - (add % 11);
                                    if (rev == 10 || rev == 11)
                                            rev = 0;
                                    if (rev != parseInt(cpf.charAt(10)))
                                            return false;

                                    return true;
                              }//end validaRespostaCPF()

                             </script>
                        </div>
                ";
echo integracao_layout('css'); 
echo modal_includes(); 

$url = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=="on") ? "s" : "") . '://' . $_SERVER['SERVER_NAME'];
//echo '<link href="'.$url.'/prepag2/js/jqueryui/css/custom-theme/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">';
//echo '<script src="'.$url.'/prepag2/js/jqueryui/js/jquery-ui-1.9.2.custom.min.js"></script>';
echo '<script src="'.$url.'/js/jquery.mask.min.js"></script>';

?>
    
    
<div class="wrapper int-box" style="border-top: 0px; border: 0px">
    <h3 class="c1">Por favor, complete o campo abaixo com o<br>CPF de seu cliente <a href="#" class="btn-question" data-msg="<h2>O que é isso?</h2>Agora as transações financeiras de alguns games estão condicionadas ao fornecimento de um CPF do cliente ou responsável. Esta solicitação é feita pelos orgão financeiros de regulamentação no Brasil. Qualquer dúvida entre em contato com o Suporte." style="position: relative; top: -4px;">?</a></h3>
        <p></p>
        
       
        <div class="int-form1" style="position: relative;">
            <form action="" id="cpfForm" method="POST">
            
                <input type="hidden" name="formsubmit" value="OK" style="display: none;" />
                 <input type="text" id="cpf" name="cpf" maxlength="14" value="<?php echo $form_cpf; ?>" placeholder="CPF" style="margin-bottom: 10px;" />
                 <input type="text" class="datepicker" style="width:140px;" value="<?php echo $form_data_nascimento; ?>" placeholder="Data de Nascimento" name="data_nascimento" id="data_nascimento">
                 <span style="font-style: italic; color: #444; float: left; font-size: 12px; margin-bottom: 10px;">(DD/MM/AAAA)</span><br>
        
                <?php 
                echo $retorno;  
                ?>
                
                 <div class="div-btn-cpf" style="position: absolute; right: -100px; bottom: 24px; width: 430px;">
                    <input type="button" class="int-btn1 grad1" id="btn_submit" value="Confirmar" />
                </div>
            </form>
            
            <?php foreach($errors as $error){ ?>
                <script>$(function(){ showMessage('<?php echo str_replace("\n"," ",$error); ?>'); });</script>
                <?php break; ?>
            <?php } ?>
        </div>
</div>
<script>
$('div#captcha_img, div#captcha_img + a').wrapAll('<div id="captcha-wrapper">');

$(document).ready(function(){
    //jQuery(function(e){e.datepicker.regional["pt-BR"]={closeText:"Fechar",prevText:"&#x3C;Anterior",nextText:"Próximo&#x3E;",currentText:"Hoje",monthNames:["Janeiro","Fevereiro","Março","Abril","Maio","Junho","Julho","Agosto","Setembro","Outubro","Novembro","Dezembro"],monthNamesShort:["Jan","Fev","Mar","Abr","Mai","Jun","Jul","Ago","Set","Out","Nov","Dez"],dayNames:["Domingo","Segunda-feira","Terça-feira","Quarta-feira","Quinta-feira","Sexta-feira","Sábado"],dayNamesShort:["Dom","Seg","Ter","Qua","Qui","Sex","Sáb"],dayNamesMin:["Dom","Seg","Ter","Qua","Qui","Sex","Sáb"],weekHeader:"Sm",dateFormat:"dd/mm/yy",firstDay:0,isRTL:!1,showMonthAfterYear:!1,yearSuffix:""},e.datepicker.setDefaults(e.datepicker.regional["pt-BR"])});
    var currentDate = new Date();
    
    $(document).keypress(function(e){
        if(e.which == 13 ) {
            $('#btn_submit').click();
            e.preventDefault();
            return false;
        }
    });
    
    $("#data_nascimento").mask("99/99/9999");
    $("#cpf").mask("999.999.999-99");
    $("#data_nascimento").blur(function(){
            if($(this).val().length == "10"){
                var dt_nasc = $(this).val().split("/");
                var objDtNasc = new Date(parseInt(dt_nasc[2]),parseInt(dt_nasc[1])-1,parseInt(dt_nasc[0]));
                if(objDtNasc.getTime() > currentDate.getTime()){
                    $(this).val("");
                    showMessage("Data inválida");
                }
            }
        });
    
    $("#data_nascimento").change(function(){
         if($(this).val().length == "10"){
                var dt_nasc = $(this).val().split("/");
                var objDtNasc = new Date(parseInt(dt_nasc[2]),parseInt(dt_nasc[1])-1,parseInt(dt_nasc[0]));
                if(objDtNasc.getTime() > currentDate.getTime()){
                    $(this).val("");
                    showMessage("Data inválida");
                }
            }
    });
    
//    $("#data_nascimento").datepicker({
//        maxDate: currentDate
//    });
});

</script>

</body>

</html>