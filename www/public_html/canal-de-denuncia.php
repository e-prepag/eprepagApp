<?php
// Obtém a URL acessada sem o domínio
$request_uri = $_SERVER['REQUEST_URI'];
// Obtém o script principal chamado
$script_name = $_SERVER['SCRIPT_NAME'];
// Se a URI acessada não for exatamente igual ao script chamado, bloqueia o acesso
if ($request_uri !== $script_name) {
    http_response_code(403);
    die("Acesso negado.");
}
require_once "../includes/constantes.php";
require_once DIR_CLASS . "gamer/controller/HeaderController.class.php";
require_once DIR_CLASS . "util/CanalDenuncia.php";
$controller = new HeaderController;
 
$pagina_titulo = "E-prepag - Créditos para Games";
 
$controller->setHeader();

$url = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=="on") ? "s" : "") . '://' . $server_url;

define("DESTINATARIO_EMAIL_PROD", "canal.denuncias@e-prepag.com");  //rc@e-prepag.com.br,rc1@e-prepag.com.br

define("DESTINATARIO_EMAIL_DEV", "luis.gustavo@e-prepag.com.br");

?>
<link rel="stylesheet" href="/js/fancybox/jquery.fancybox.css" type="text/css" />
<link rel="stylesheet" href="/css/modal.css" type="text/css" />
<script src="/js/fancybox/jquery.fancybox.js"></script>
<script src="/js/modal.js"></script>
<script src="/js/jquery.mask.min.js"></script>
<script src="/js/valida.js"></script>
<script>
    $(function(){
        $('.celular').mask('(00) 90000-0000');
        $('.cpf').mask('999.999.999-99');
        
        $("#enviar").click(function(e){
            get_action_recaptcha();
        });
       
        $('input[name="identificacao"]').change(function () {
            if ($('input[name="identificacao"]:checked').val() === "1") {
                $('input[name="identificacao"]').val("0");
                $(".anonimo").hide();
            } else {
                $('input[name="identificacao"]').val("1");
                $('input[name="identificacao"]').removeAttr('checked');
                $(".anonimo").show();
            }
        });
    });
   
    function get_action_recaptcha(){
        var response = grecaptcha.getResponse();
        var msg_erro = '';
        if(response.length == 0){
            msg_erro += "- Você deve fazer a verificação do reCAPTCHA para continuar!";
            $("#modal-validacao").modal('show');
            $("#msg_modal_erro").html(msg_erro);
            return false;
        } else{
            validaDados();
        }
    }
   
    var onloadCallback = function(){
        grecaptcha.render(  'html_recap', {
                            'sitekey' : '6Lc4XtkkAAAAAJrfsAKc99enqDlxXz4uq86FI9_T',
                            'lang' : 'pt'
                        });
    };
    
    function validaEmail(email){
        var re = /\S+@\S+\.\S+/;
        return re.test(email);
    }
   
    function validaDados(){
        var msg_erro = '';
        if($('input[name="identificacao"]').val() == "1"){
            if($("#nome").val().trim() == ""){
                msg_erro += "- Campo Nome é obrigatório!<br>";
                $("#nome").css('border-color', 'red');
            } else{
                $("#nome").css('border-color', '');
            }
 
            if(!validaCpf($("#cpf").val().trim())){
                msg_erro += "- CPF inválido! Digite um CPF válido<br>";
                $("#cpf").css('border-color', 'red');
            } else{
                $("#cpf").css('border-color', '');
            }
 
            if($("#email").val().trim() != $("#confirma_email").val().trim() || $("#email").val().trim() == ""){
                msg_erro += "- Verifique o e-mail inserido!<br>";
                $("#email").css('border-color', 'red');
                $("#confirma_email").css('border-color', 'red');
            } else{
                if(!validaEmail($("#email").val().trim())){
                    msg_erro += "- E-mail inválido! Insira um e-mail válido<br>";
                    $("#email").css('border-color', 'red');
                    $("#confirma_email").css('border-color', 'red');
                } else{
                    $("#email").css('border-color', '');
                    $("#confirma_email").css('border-color', '');
                }
            }
 
            if($("#celular").val().trim() == ""){
                msg_erro += "- Campo Celular é obrigatório!<br>";
                $("#celular").css('border-color', 'red');
            } else{
                $("#celular").css('border-color', '');
            }
        }
 
        if($("#motivo").val().trim() == ""){
            msg_erro += "- Campo Motivo da Denúncia é obrigatório! Selecione um motivo para sua denúncia<br>";
            $("#motivo").css('border-color', 'red');
        } else{
            $("#motivo").css('border-color', '');
        }
 
        if($("#mensagem").val().trim() == ""){
            msg_erro += "- Campo Sua Denúncia é obrigatório! Escreva a sua denúncia<br>";
            $("#mensagem").css('border-color', 'red');
        } else{
            $("#mensagem").css('border-color', '');
        }
 
        if(msg_erro != ''){
            $("#msg_modal_erro").html(msg_erro);
            $("#modal-validacao").modal('show');
            return false;
        }
        $( "form" ).submit();
    } 
   
</script>
<div id="modal-validacao" class="modal fade" data-backdrop="static" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title txt-vermelho">Erro(s) Encontrado(s)</h4>
            </div>
            <div class="modal-body alert alert-danger">
                <div class="form-group top10">
                    <p id="msg_modal_erro"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
 
<div class="container txt-azul-claro bg-branco p-bottom40">
    <div class="col-md-12 txt-azul-claro top10">
        <span class="glyphicon glyphicon-triangle-right graphycon-big pull-left" aria-hidden="true"></span><strong class="pull-left"><h4 class="top20">Canal de Denúncias</h4></strong>
    </div>
<?php
if(isset($GLOBALS['_SESSION']['usuarioGames_ser'])){
    $usuario_logado = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']); 
}

	//if($_SERVER["REMOTE_ADDR"] == "191.181.57.158"){
	    //ini_set('display_errors', 1);
        //ini_set('display_startup_errors', 1);
        //error_reporting(E_ALL);
	//}

$ug_id_user_logado = (isset($usuario_logado->ug_id)?$usuario_logado->ug_id:"0");
$name_user_logado = (isset($usuario_logado->ug_sNome)?$usuario_logado->ug_sNome:"");
$cpf_user_logado = (isset($usuario_logado->ug_sCPF)?$usuario_logado->ug_sCPF:"");
$email_user_logado = (isset($usuario_logado->ug_sEmail)?strtolower($usuario_logado->ug_sEmail):"");
$celular_user_logado = (isset($usuario_logado->ug_sCelDDD) && isset($usuario_logado->ug_sCel)?$usuario_logado->ug_sCelDDD . $usuario_logado->ug_sCel:"");

if(isset($GLOBALS['_POST']['enviado']) && $GLOBALS['_POST']['enviado']){
    $ug_id = $GLOBALS['_POST']['usuario_id'];
    
    //Número randomico para concatenar ao número de protocolo gerado
    $rand = rand(100,999);
    $protocolo = date('YmdHi').$rand;
    
    $protocolo_cliente = "Anote seu número de protocolo <strong>[".$protocolo."]</strong><br>";
   
    if((isset($GLOBALS['_POST']['identificacao']) && $GLOBALS['_POST']['identificacao']) || (!isset($GLOBALS['_POST']['identificacao']))){
        $nome = $GLOBALS['_POST']['nome'];
        $cpf = $GLOBALS['_POST']['cpf'];
        $email = strtolower($GLOBALS['_POST']['email']);
        $celular = $GLOBALS['_POST']['celular'];
        $anonima = "0";
    } else{
        $anonima = "1";
    }
	
	function verificaPOST($referer,$POST){
            
		//if (strpos($referer,"bronzato.com.br")===false && strpos($referer,"contause.digital")===false){return false;}
		$flag=true;
		foreach($_POST as $xa=>$xb){
			$xb = serialize($xb);
			if (strpos($xb,"dbms_pipe.receive_message")!==false || strpos($xb,"DBMS_PIPE.RECEIVE_MESSAGE")!==false || strpos($xb,"delete")!==false || strpos($xb,"delete")!==false || strpos($xb,"update")!==false || strpos($xb,"select")!==false ){
					return false;
			}
			
			if (strpos($xb,"dbms_pipe.receive_message")!==false || strpos($xb,"DBMS_PIPE.RECEIVE_MESSAGE")!==false ||strpos(hexToStr($xb),"delete")!==false || strpos(hexToStr($xb),"update")!==false || strpos(hexToStr($xb),"select")!==false ){
					return false;
			}
		}
		
		if ($flag){return true;}else{return false;}
	}
	
	function strToHex($string){
		$hex = '';
		for ($i=0; $i<strlen($string); $i++){
			$ord = ord($string[$i]);
			$hexCode = dechex($ord);
			$hex .= substr('0'.$hexCode, -2);
		}
		return strToUpper($hex);
	}
	
	function hexToStr($hex){
		$string='';
		for ($i=0; $i < strlen($hex)-1; $i+=2){
			$string .= chr(hexdec($hex[$i].$hex[$i+1]));
		}
		return $string;
	}
    
	if(!verificaPOST("", $_POST)){
		$denuncia_enviada = "Problema ao enviar denúncia!";
        $denuncia_enviada_mensagem = "Houve um problema ao enviar sua denúncia! Tente novamente mais tarde.";
        $image = "cancel.gif";
	}else{
		
		 if(!empty($_POST["g-recaptcha-response"])){
			
		    $tokenInfo = ["secret" => "6Lc4XtkkAAAAAJYRV2wnZk_PrI7FFNaNR24h7koQ", "response" => $_POST["g-recaptcha-response"], "remoteip" => $_SERVER["REMOTE_ADDR"]];             
			$recaptcha = curl_init();
			curl_setopt_array($recaptcha, [
				CURLOPT_URL => "https://www.google.com/recaptcha/api/siteverify",
				CURLOPT_CUSTOMREQUEST => "POST",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POSTFIELDS => http_build_query($tokenInfo)

			]);
			$retorno = json_decode(curl_exec($recaptcha), true);
			curl_close($recaptcha);

			if($retorno["success"] != true || (isset($retorno["error-codes"]) && !empty($retorno["error-codes"]))){
				$denuncia_enviada = "Problema ao enviar denúncia!";
				$denuncia_enviada_mensagem = "Houve um problema ao enviar sua denúncia! Tente novamente mais tarde.";
				$image = "cancel.gif";
			}else{
				
				$motivo_denuncia = $GLOBALS['_POST']['motivo'];
				$mensagem = $GLOBALS['_POST']['mensagem'];
				
				$array_construtor = array(
										  'ug_id'           => $ug_id,
										  'protocolo'       => $protocolo,
										  'nome'            => (isset($nome)?$nome:NULL),
										  'cpf'             => (isset($cpf)?$cpf:NULL),
										  'email'           => (isset($email)?$email:NULL),
										  'celular'         => (isset($celular)?$celular:NULL),
										  'motivo_denuncia' => $motivo_denuncia,
										  'mensagem_denuncia' => $mensagem,
										  'denuncia_anonima' => $anonima
				);
				
				$obj_denuncia = new CanalDenuncia($array_construtor);
				$salvou = $obj_denuncia->save();
				
			}
		   
		}
		else{	
		   	$denuncia_enviada = "Problema ao enviar denúncia!";
			$denuncia_enviada_mensagem = "Houve um problema ao enviar sua denúncia! Tente novamente mais tarde.";
			$image = "cancel.gif";
		}
		
    }
    if(isset($salvou) && $salvou){
        $msg_email = "Nova denúncia recebida em <strong>".date("d/m/Y H:i")."</strong><br><br>";
        if(isset($nome) && isset($email) && isset($celular)){
            $msg_email .= "<strong>Nome</strong>: ".$nome."<br>".
                          "<strong>E-mail</strong>: ".$email."<br>".
                          "<strong>CPF</strong>: ".$cpf."<br>".
                          "<strong>Celular</strong>: ".$celular."<br>";

            $msg_usuario = "Sua denúncia foi enviada com sucesso para a E-Prepag. Em até 7 dias você receberá uma resposta no e-mail <i>".$email."</i>";

        } else{
            $msg_email .= "<strong>Denúncia Anônima</strong><br>";

            $msg_usuario = "Sua denúncia anônima foi enviada com sucesso para a E-Prepag.";

        }

        $msg_email .= "<strong>Motivo da denúncia</strong>: ".$obj_denuncia->retorna_motivo_denuncia($motivo_denuncia)."<br>".
                      "<strong>Denúncia</strong>: ".$mensagem."<br>";
        
        if($ug_id != "0") $msg_email .= "<strong>Id Cliente GAMER: </strong>: ".$ug_id."<br>";

        $assunto = "E-Prepag - Nova Denúncia [".$protocolo."]";
        
        $denuncia_enviada = "Denúncia enviada com sucesso!";
        $denuncia_enviada_mensagem = $protocolo_cliente."Agora nossa diretoria irá verificar sua denúncia.<br>
                        Este processo de análise pode levar até 7 dias úteis.<br>
                        Caso não seja uma denúncia anônima, você receberá um e-mail com a apuração da sua denúncia em breve.";
        $image = "confirmation.png";

        $destinatario = (checkIP()?DESTINATARIO_EMAIL_DEV : DESTINATARIO_EMAIL_PROD);

        enviaEmail($destinatario, $cc, $bcc, $assunto, $msg_email);
        
        
        
    } else{
        $denuncia_enviada = "Problema ao enviar denúncia!";
        $denuncia_enviada_mensagem = "Houve um problema ao enviar sua denúncia! Tente novamente mais tarde.";
        $image = "cancel.gif";
    }
       
?>
    <div class="col-md-12 top10 espacamento">
        <div class="row">
            <div class="titulo top50">
                <div class="col-md-2 col-lg-2 col-xs-12 col-sm-12">
                    <img src="/imagens/pdv/<?php echo $image; ?>" style="width: 100px;margin-top: 5px;">
                </div>
                <div class="col-md-10 col-lg-10 col-xs-12 col-sm-12" style="padding-bottom: 40px">
                    <p class="text18"><strong><?php echo $denuncia_enviada; ?></strong></p>
                    <span class="texto_cinza">
                        <?php echo $denuncia_enviada_mensagem; ?><br>
                        Caso tenha alguma dúvida, por favor contate nosso <a href="/game/suporte.php" target="_blank">Suporte</a>.
                    </span>
                </div>
            </div>
        </div>
    </div>
<?php    
} else{
 
?>
    <div class="col-md-12 col-sm-12 col-xs-12 top10 txt-cinza">
        <h3>O que é Canal de Denúncias <a href="#" class="btn-question glyphicon glyphicon-question-sign txt-vermelho c-pointer t0" data-fancybox data-msg="<h2>O que é isso?</h2>A Resolução nº 4.567, de 27/4/2017, do Banco Central, pede a disponibilização de um dispositivo denominado 'Canal de Denúncia', sendo este  um instrumento democrático, que rompe barreiras hierárquicas e oferece aos colaboradores, parceiros e clientes, um meio de comunicação direta com a liderança da empresa para tratar de indícios de ilicitude relacionados às atividades da instituição.<br>Você pode optar por uma denúncia anônima e não obter um retorno com uma conclusão, ou se identificar e receber um feedback dentro de 7 dias úteis." style="position: relative;"></a></h3>
    </div>
    <div class="col-md-12 col-sm-12 col-xs-12 top20 txt-cinza">
        <form name="denuncia" id="denuncia" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method="post">
            <input type="hidden" name="enviado" id="enviado" value="1">
            <input type="hidden" name="usuario_id" id="usuario_id" value="<?php echo $ug_id_user_logado; ?>">
            <div class="col-md-6">
                <div class="row form-group">
                    <label class="col-lg-8 col-md-8 col-sm-8 col-xs-8 control-label left10" for="identificacao">Quero fazer uma denúncia anônima</label>
                    <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 right10">
                        <input type="checkbox" name="identificacao" id="identificacao" value="1">
                    </div>
                </div>
               
                <div class="row form-group anonimo">
                    <label class="col-lg-4 col-md-4 col-sm-12 col-xs-12 control-label left10" for="nome">Nome Completo<span class="required txt-vermelho">*</span></label>
                    <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12 right10">
                        <input class="form-control" type="text" name="nome" id="nome" maxlength="512" value="<?php echo $name_user_logado; ?>">
                    </div>
                </div>
 
                <div class="row form-group anonimo">
                    <label class="col-lg-4 col-md-4 col-sm-12 col-xs-12 control-label left10" for="cpf">CPF<span class="required txt-vermelho">*</span></label>
                    <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12 right10">
                        <input class="form-control cpf" type="text" name="cpf" id="cpf" maxlength="14" value="<?php echo $cpf_user_logado; ?>">
                    </div>
                </div>
 
                <div class="row form-group anonimo">
                    <label class="col-lg-4 col-md-4 col-sm-12 col-xs-12 control-label left10" for="email">Email<span class="required txt-vermelho">*</span></label>
                    <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12 right10">
                        <input class="form-control" type="text" name="email" id="email" maxlength="256" value="<?php echo $email_user_logado; ?>">
                    </div>
                </div>
                <div class="row form-group anonimo">
                    <label class="col-lg-4 col-md-4 col-sm-12 col-xs-12 control-label left10" for="confirma_email">Confirmação de Email<span class="required txt-vermelho">*</span></label>
                    <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12 right10">
                        <input class="form-control" type="text" name="confirma_email" maxlength="256" id="confirma_email" value="">
                    </div>
                </div>
                <div class="row form-group anonimo">
                    <label class="col-lg-4 col-md-4 col-sm-12 col-xs-12 control-label left10" for="celular">Celular<span class="required txt-vermelho">*</span></label>
                    <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12 right10">
                        <input class="form-control celular p-right0i" placeholder="(99) 9999-9999" maxlength="20" type="text" name="celular" id="celular" value="<?php echo $celular_user_logado; ?>">
                    </div>
                </div>
                <div class="row form-group">
                    <label class="col-lg-4 col-md-4 col-sm-12 col-xs-12 control-label left10" for="motivo">Motivo da denúncia<span class="required txt-vermelho">*</span></label>
                    <div class="col-lg-5 col-md-5 col-sm-11 col-xs-11 selectContainer" >
                        <select name="motivo" id="motivo" class="form-control select_input" required>
                            <option value=""> Selecione </option>
                            <option value="1"> Relacionamento Interpessoal </option>
                            <option value="2"> Normas e Políticas </option>
                            <option value="3"> Má intenção/Ilícitos </option>
                            <option value="4"> Ética </option>
                            <option value="5"> Sustentabilidade </option>
                            <option value="6"> Outros </option>
                        </select>
                    </div>
                </div>
               
                <div class="row form-group">
                    <label class="col-lg-6 col-md-6 col-sm-12 col-xs-12 control-label left10" for="mensagem">Sua denúncia<span class="required txt-vermelho">*</span></label>
                    <textarea style="resize:none" class="form-control textarea row pull-right" placeholder="Escreva aqui a sua denúncia" id="mensagem" name="mensagem" rows="6"></textarea>
                </div>
               
                <div class="row form-group">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 right10 espacamento">
                        <div id="html_recap" class="pull-left "></div>
                    </div>
                    <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 right10">
                        <button class="form-control btn btn-success" type="button" name="enviar" id="enviar" value="1">Enviar</button>
                    </div>
                </div>
            </div>
        </form>
        <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit&hl=pt" async defer></script>
    </div>
<?php
}
?>  
</div>
</div>
 
<?php
require_once "game/includes/footer.php";
?>