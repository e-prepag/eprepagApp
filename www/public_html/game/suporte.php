<?php
require_once "../../includes/constantes.php";
require_once DIR_CLASS."gamer/controller/HeaderController.class.php";
require_once DIR_CLASS."util/Validate.class.php";

$controller = new HeaderController;
$controller->setHeader();

if(!empty($_POST)){
    
    foreach($_POST as $ind => $val){
        $_POST[$ind] = stripslashes(strip_tags($val));
    }
    
    $valida = new Validate;
    
    if($valida->email($_POST['email'])){
        $erro[] = "Email inválido.";
    }
    
    if(!empty($_FILES['anexo']["tmp_name"])){
        if($retImg = $valida->imagem($_FILES['anexo'])){
            $erro = $retImg; //retorna array
        }else{
            $attach = "";
            $file = $_FILES['anexo'];
            if(!move_uploaded_file(basename($file["tmp_name"]),DIR_CACHE . basename($file["name"])))
                $erro[] = "Erro ao gravar imngem";
            else
                $attach = DIR_CACHE.$file["name"];
        
        }
    }
    
    if($valida->letras($_POST['nome'])){
        $erro[] = "Nome inválido.";
    }
        
    if($valida->qtdCaracteres($_POST['tel'],14,15)){
        $erro[] = "Telefone inválido.";
    }
    
    if(empty($_POST['msg'])){
        $erro[] = "Mensagem precisa ser preenchida.";
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


	 if(!empty($_POST["g-recaptcha-response"])){
			
		   $tokenInfo = ["secret" => getenv("RECAPTCHA_SECRET_KEY"), "response" => $_POST["g-recaptcha-response"], "remoteip" => $_SERVER["REMOTE_ADDR"]];             

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
				  $erro[] = "<p>Processo invalidado por RECAPTCHA.</p>";
			}
		   
	  }else{
		   $erro[] = "<p>Você deve realizar a verificação do RECAPTCHA para prosseguir.</p>";
	  }
    
	if(!verificaPOST("", $_POST)){
	    $titulo = "Ops...";
	    $modalMsg = "Problemas no envio do Email.";
		$modalTipo = 1;
	}else{
		if(empty($erro)){
	//        require_once DIR_CLASS."util/Email.class.php";
			require_once DIR_INCS."gamer/functions.php";
			// Dados do Email
	//        $email  = "diegogandradex@gmail.com";
			$cc     = getenv("email_suporte");
			$subject= "Contato - Suporte";
			$corpoMsg = "<html><head></head><body>
						Olá<br>
						<br>
						O seu formulário de contato com a E-Prepag foi enviado.<br>
						<br>
						Data de envio: ".date("d-m-Y H:i:s").".<br>
						<br>
						Nome: ".$_POST['nome']."<br>
						Email: ".$_POST['email']."<br>
						Telefone: ".$_POST['tel']."<br>
						Dúvida, mensagem ou problema apresentado: ".$_POST['msg']."<br>
						<br>
						<br>
						Fique tranquilo, enviaremos uma resposta em até 1 dia útil!
						<br><br>
						Atenciosamente,
						<br><br>
						Equipe E-Prepag</body></html>";
			
			if (enviaEmail4($_POST['email'], $cc, $bcc, $subject, $corpoMsg, null, $attach)) {
				if($attach != "")
					unlink($attach);
				
				$titulo = "E-Prepag - Créditos";
				$modalMsg = "Email enviado com sucesso.";
				$modalTipo = 2;
			}
			else {
				$titulo = "Ops...";
				$modalMsg = "Problemas no envio do Email.";
				$modalTipo = 1;
			}

		}else{
			$titulo = "Ops...";
			
			if(is_array($erro))
				$modalMsg = implode("<br>",$erro);
			else
				$modalMsg = "Erro desconhecido";
			
			$modalTipo = 1;
		}
	
	}
}

?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script src="/js/valida.js"></script>
<div class="container txt-azul-claro bg-branco p-bottom40">
<?php
    if(isset($modalTipo) && isset($modalMsg) && isset($titulo)){
        print "<script>manipulaModal($modalTipo,'$modalMsg','$titulo');</script>";
    }
?>
    <div class="col-md-12 txt-azul-claro top10">
        <span class="glyphicon glyphicon-triangle-right graphycon-big pull-left" aria-hidden="true"></span><strong class="pull-left"><h4 class="top20">Suporte</h4></strong>
    </div>
    <div class="clearfix"></div>
    <div class="col-md-4 txt-cinza text-center">
        <p><img src="/imagens/icone_chat.gif"></p>
        <p class="text18 txt-azul-claro">Chat</p>
        <p class="leftright15">Segunda a sexta das 09:00 as 13:00 e das 14:00 as 17:00h.</p>
        <p class="leftright15">Acesse o atendimento online no rodapé desta página.</p>
    </div>
    <div class="col-md-12 hidden-md espacamento hidden-lg borda-top-azul top10">
    </div>
    <div class="col-md-4 txt-cinza hide text-center">
        <p><img src="/imagens/fone_ico.png"></p>
        <p class="text18 txt-azul-claro">Telefone</p>
        <p class="leftright15">11 3030-9101</p>
        <p>Segunda a sexta das 08h às 17h</p>
    </div>
    <div class="col-md-12 espacamento hidden-md hidden-lg borda-top-azul top10">
    </div>
    <div class="col-md-4 txt-cinza text-center">
        <a href="<?php echo PROTOCOL;?>://e-prepag.zendesk.com/hc/pt-br" class="txt-cinza" target="_blank">
            <p><img src="/imagens/duvidas_frequentes.gif"></p>
            <p class="text18 txt-azul-claro">Dúvidas frequentes</p>
            <p class="leftright15">Encontre rapidamente as</p>
            <p>respostas para sua dúvida</p>
        </a>
    </div>
    <div class="col-md-12 borda-top-azul top10">
    </div>
    <div class="col-md-4 txt-cinza text-center top20">
        <p><img src="/imagens/msg_ico.png"></p>
        <p class="text18 txt-azul-claro">Mensagem</p>
        <p>Resposta em no máximo 1 dia útil</p>
    </div>
    <div class="col-md-8 txt-cinza text-center top20">
        <form id="formContato" enctype="multipart/form-data" name="form" method="post">
            <div class="col-md-6 top20">
                <div class="col-md-12 top5">
                    <input type="text" placeholder="Nome *" id="nome" name="nome" class="form-control" char="2">
                </div>
                <div class="col-md-12 top5">
                    <input type="text" placeholder="E-mail *" id="email" name="email" class="form-control">
                </div>
                <div class="col-md-12 top5">
                    <input type="text" placeholder="Telefone *" id="tel" name="tel" class="form-control" char="14" maxlength="15">
                </div>
                <div class="col-md-12 top5">
                    <label for="anexo" class="text-left fontsize-p">Anexar
                        <input type="file" class="custom-file-input" name="anexo" id="anexo" value="">
                    </label>
                </div>
                <div class="col-md-12 text-right fontsize-p">
                    Se julgar necessário você pode anexar um arquivo ou comprovante.
                </div>
            </div>
            <div class="col-md-6">
                <div class="row form-group text-left">
                    <label for="comment">Mensagem:</label>
                    <textarea class="form-control" char="5" rows="5" name="msg" id="msg"></textarea>
                </div>
                <div style="padding: 0 0 15px 0;">
					<div class="g-recaptcha" data-sitekey="6Lc4XtkkAAAAAJrfsAKc99enqDlxXz4uq86FI9_T"></div>
			    </div>
                <div class="row text-right">
                    <button type="submit" class="btn btn-success">Enviar</button>
                </div>
            </div>
        </form>
    </div>
</div>
</div>
<script type="text/javascript" src="/js/jquery.mask.min.js"></script>
<script>
$(function(){
    $("#tel").mask('(00) 90000-0000');
    
    $("#formContato").submit(function(){
        if(grecaptcha.getResponse() == "" || grecaptcha.getResponse().length == 0){
            manipulaModal(1,"Você deve fazer a verificação do RECAPTCHA para finalizar seu cadastro.",'Erro');
            return false;
        }

        if(!valida())
            return false;
    });
       
   $("#btnanexo").click(function(){
      $("#anexo").trigger("click"); 
   });
});
</script>
<?php
require_once RAIZ_DO_PROJETO . "public_html/game/includes/footer.php";