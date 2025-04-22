<?php

require_once "../../includes/constantes.php";
require_once RAIZ_DO_PROJETO . "class/pdv/controller/OffLineController.class.php";

$controller = new OfflineController;

require_once "includes/header-offline.php";

$https = 'http' . (($_SERVER['HTTPS']=='on') ? 's' : '');
$server_url = $https . '://' . (checkIP() ? $_SERVER['SERVER_NAME'] : 'www.e-prepag.com.br');
session_start();

//Id do GoCASH
$id_gocash = 1;

// Vetor que cria o drop drown dos estados
$Resultadoestado = $SIGLA_ESTADOS;

function generateRandomCode() {
    $numbersAllowedInCode = false; //	Set to FALSE for a 'Letters Only' Code
    $numberOfLetters = 4;   //	Allow Minimum 20 Pixels Per Letter - See codeBoxWidth Property Above)

//echo "numbersAllowedInCode: '".($numbersAllowedInCode?"YES":"Nope")."', numberOfLetters: '".$numberOfLetters."'<br>";

    $GLOBALS['_SESSION']['verificationCode'] = "";
    $ret = "";
    for ($placebo = 1; $placebo <= $numberOfLetters; $placebo++) {
        if ((rand() > 0.49) || ($numbersAllowedInCode == false)) {
            $number = 97 + rand(0, 25); //rand(97,122);
            $char = chr($number);
            $ret .= $char;
//	        $ret .= chr(rand(97,122));
        } else {
            $number = 48 + rand(0, 10); //rand(48, 57);
            $char = chr($number);
            $ret .= $char;
//			$ret .= chr(rand(48, 57));
        }
//echo "<nobr>char: '$char', number: '$number', ret: '".$ret."'</nobr><br>";
    }
    $GLOBALS['_SESSION']['verificationCode'] = $ret;
    $GLOBALS['_SESSION']['palavraCodigo'] = $ret;
//echo "verificationCode: '".$GLOBALS['_SESSION']['verificationCode']."'<br>";
//die("Stop");
    return $ret;
}

// echo "<hr>translateCode(generateRandomCode()) :" . translateCode(generateRandomCode()) . "<hr>";

Function translateCode($scode) {
    $numbersAllowedInCode = false; //	Set to FALSE for a 'Letters Only' Code
    $numberOfLetters = 4;   //	Allow Minimum 20 Pixels Per Letter - See codeBoxWidth Property Above)

//echo "<nobr>scode: '".$scode."'</nobr><br>";
    $stmp = "";

    for ($placebo = 0; $placebo < $numberOfLetters; $placebo++) {
        /*
          $schar = ord(substr($scode, $placebo, 1))+$placebo;
          if(strlen($schar)==0) $schar = "000";
          if(strlen($schar)==1) $schar = "00".$schar;
          if(strlen($schar)==2) $schar = "0".$schar;
          $stmp .= $schar;
         */
        $schar = ord(substr($scode, $placebo, 1)) + $placebo;
        $stmp.= str_pad($schar, 3, '0', STR_PAD_LEFT);

//echo "<nobr>$placebo: '".$schar."' ('".ord(substr($scode, $placebo, 1))."') -> '".$stmp ."'</nobr><br>";
    }
    return $stmp;
}

if(isset($_SESSION["error_login"])){
	foreach($_SESSION["error_login"] as $key => $value) {
		$msg .= utf8_decode($value)."<br>";
	}
	unset($_SESSION["error_login"]);
}

?>
<div class="container txt-cinza bg-branco  p-bottom40">
<?php
    if(isset($msg) && $msg != ""){
?>
    <div class="col-md-12 top20">
        <div class="alert alert-danger" role="alert">
            <span class="glyphicon glyphicon-exclamation-sign t0" aria-hidden="true"></span>
            <span class="sr-only">Erro:</span>
            <?php echo $msg; ?>
        </div>
    </div>
<?php
    }
?>
    <div class="row top10">
        <div class="col-md-6 top10 col-sm-12 col-xs-12">
            <span class="glyphicon txt-azul-claro glyphicon-triangle-right graphycon-big pull-left" aria-hidden="true"></span>
            <strong class="pull-left"><h4 class="top20 txt-azul-claro">acesso para pontos de venda</h4></strong>
			<div class="alert-login">
				O acesso para conta principal deverá ser feito somente com o login cadastrado.
			</div>
			<?php if(!isset($_SESSION["sendToken"]) && !$_SESSION["sendToken"]) { ?>
				<form action="loginEf-sms.php" method="post">
					<div class="form-group top20 col-md-8 col-md-offset-4 col-sm-12 col-xs-12">
						<div class="col-md-3 col-md-offset-1">
							<label for="login">Login:</label>
						</div>
						<div class="col-md-8">
							<input class="form-control input-sm" id="login" onpaste="return false;" name="login" autocomplete="off" type="text" value="">
						</div>
					</div>
					<div class="col-md-8 col-md-offset-4 form-group col-sm-12 col-xs-12">
						<div class="col-md-3 col-md-offset-1">
							<label for="senha">Senha:</label>
						</div>
						<div class="col-md-8">
							<input class="form-control input-sm" id="senha" name="senha" type="password" value="">
						</div>
						<div class="col-md-12 fontsize-p" style="text-align: end;">
							<div style="padding: 10px 0 15px 0;">
								<div class="g-recaptcha" data-sitekey="6Lc4XtkkAAAAAJrfsAKc99enqDlxXz4uq86FI9_T"></div>
							</div>
							<a class="decoration-none txt-cinza" id="esqueci-senha" href="#"><em>Esqueci minha senha</em></a>
						</div>
						<div class="col-md-8 col-md-offset-6 fontsize-p">
							<a class="decoration-none txt-cinza" id="faca-cadastro" target="_blank" href="/cadastro-de-ponto-de-venda.php"><em>Faça aqui seu cadastro</em></a>
						</div>
						<!-- Modal -->
						<div id="modal-esqueci-senha" class="modal fade" role="dialog">
							<div class="modal-dialog">
								<!-- Modal content-->
								<div class="modal-content">
									<div class="modal-header">
									  <button type="button" class="close" data-dismiss="modal">&times;</button>
									  <h4 class="modal-title">Esqueci minha senha.</h4>
									</div>
									<div class="modal-body">
										<div class="form-group top10">
											<label for="senha">Digite seu login:</label>
											<input type="text" class="form-control" id="emailEsqueciSenha" autocomplete="new-password" name="emailEsqueciSenha" placeholder="Digite seu login">
											<a href="#" class="btn btn-info top10" id="reenviarSenha">Enviar senha</a>
										</div>
									</div>
									<div class="modal-footer">
									  <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-12 top10 form-group col-sm-12 col-xs-12">
						<div class="col-md-6 col-md-offset-6 dislineblock">
							<input id="" type="submit" class="pull-right btn btn-success" value="Login" /><br />
						</div>
					</div>
				</form>
			<?php 
				}else{ 
				    if($_SESSION["dist_usuarioGamesOperador_ser"]){
						$_SESSION["AuXdist_usuarioGamesOperador_ser"] = $_SESSION["dist_usuarioGamesOperador_ser"];
						unset($_SESSION["dist_usuarioGamesOperador_ser"]);
					}
					
					if($_SESSION["dist_usuarioGames_ser"]){
						$_SESSION["AuXdist_usuarioGames_ser"] = $_SESSION["dist_usuarioGames_ser"];
						unset($_SESSION["dist_usuarioGames_ser"]);
					}
			?>
				<form action="loginToken.php" method="post">
					<div class="col-md-8 col-md-offset-4 form-group col-sm-12 col-xs-12">
						<div class="col-md-3 top20 col-md-offset-1">
							<label for="senha">Token:</label>
						</div>
						<div class="col-md-8 top20">
							<input class="form-control input-sm" id="token" name="token" type="text">
						</div>
					</div>
					<div class="col-md-12 top10 form-group col-sm-12 col-xs-12">
						<div class="col-md-6 col-md-offset-6 dislineblock">
							<input id="" type="submit" class="pull-right btn btn-success" value="Login" /><br />
						</div>
					</div>
					<div 
					style="
						font-size: 15px;
						text-align: justify;
						border: 1px solid #ddd;
						border-radius: 10px;
						padding: 10px;
						box-shadow: 10px 10px 30px #ddd;
					" 
					class="col-md-12 top10 form-group col-sm-12 col-xs-12">
						<p>&#128679; Foi enviado um SMS para o seu represente legal, onde será gerado o token de acesso.</p>
					</div>
				</form>
			<?php } ?>
        </div>
        <div class="col-md-6 borda-cinza top10 col-sm-12 col-xs-12">
            <div class="col-md-8 pull-right col-md-offset-2 borda-fina bg-cinza-claro">
                <form id="form_lanHouses_filtros" name="form_lanHouses_filtros" method="post" action="/busca-pdv.php">
                <div class="form-group col-md-12 col-sm-12 col-xs-12">
                    <strong class="pull-left"><h4 class="top20 txt-azul-claro"><strong>Encontre um ponto de venda</strong></h4></strong>
                </div>
                <div class="form-group col-md-12 col-sm-12 col-xs-12">
                    <div class="col-md-3">
                        <label for="estado">Estado:</label>
                    </div>
                    <div class="col-md-9">
                        <select name="estado" class="form-control input-sm" id="estado" onChange='MostraCidade();'>
                            <option value="">&nbsp;UF&nbsp;</option>
                            <?php
                            // Gera os dados do drop down estado
                            foreach ($Resultadoestado as $value) {
                                echo '<option value="' . $value . '"';
                                if ($_POST['estado'] == $value) {
                                    echo " SELECTED ";
                                }
                                echo ">" . $value . "</option>\n";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-group col-md-12 col-sm-12 col-xs-12">
                    <div class="col-md-3">
                        <label for="cidade">Cidade:</label>
                    </div>
                    <div class="col-md-9" id="SelCidade">
                        <select name="cidade" class="form-control input-sm" id="cidade" DISABLED>
                            <option value="">Selecione um Estado</option>		
                        </select>
                    </div>
                </div>
                <div class="form-group col-md-12 col-sm-12 col-xs-12">
                    <div class="col-md-3">
                        <label for="bairro">Bairro:</label>
                    </div>
                    <div class="col-md-9" id="SelBairro">
                        <select class="form-control input-sm" name="bairro" id="bairro" DISABLED>
                            <option value="">Selecione uma Cidade</option>		
                        </select>
                    </div>
                </div>
                <div class="form-group col-md-12 col-sm-12 col-xs-12">
                    <div class="col-md-5  col-sm-6 col-xs-6">
    <?php
                        $randomcode = (isset($_POST['rc'])) ? $_POST['rc'] : generateRandomCode();
                        $randomcode_translated = translateCode($randomcode);
    ?>
                        <img width="110px" height="35px" class="pull-right" src="/includes/captcha/CaptchaImage.php?uid=<?php echo $randomcode_translated; ?>" title="Verify Code" vspace="2" />
                        <input type="hidden" name="rc" value="<?php echo $randomcode;?>">
                    </div>
                    <div class="col-md-5  col-sm-4 col-xs-4">
                        <input name="verificationCode" class="form-control input-sm" type="text" id="verificationCode"  size="5" />
                    </div>
                    <div class="col-md-2  col-sm-2 col-xs-2">
                        <a onClick="procuraLan()" href="#" class=" btn btn-sm pull-right btn-success">Ok</a>
                    </div>
                </div>
                <div class="clearfix"></div>
                </div>
            </form>
        </div>
    </div>
<?php
require_once RAIZ_DO_PROJETO . "class/business/BannerBO.class.php";

$categoria = "OffLine";
$posicao = "Login";

$objBanner = new BannerBO();
$banner = $objBanner->getBannersFromJson($posicao,$categoria);
?>
    <div id="background_banner" class="top20 hidden-sm hidden-xs">
<?php 
if($banner){
    foreach($banner as $b){
?>
        <a href="<?php echo $b->link; ?>" class="banner p-8" id="<?php echo $b->id; ?>" target="_blank"><img src="<?php echo $objBanner->urlLink.$b->imagem; ?>" title="<?php echo $b->titulo; ?>"></a>
<?php 
    }
?>
    <script>
    $(function(){
       $(function(){
            $(".banner").click(function(){
                $.get( "/ajax/pdv/clickBanner.php", { id: $(this).attr("id") } );
            });
        }); 
    });
    </script>
<?php
}
?>
    </div>
</div>
</div>
<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 bg-info align-center borda-direita-basica p-bottom10">
        <h3>
            Quer ser um ponto de venda ?  
            <a href="/cadastro-de-ponto-de-venda.php" class="txt-branco" target="_blank"><b><span class="link-destaque">Cadastre-se</span></b></a>
            ou 
            <a href="https://e-prepagpdv.com.br/" class="txt-branco" target="_blank"><b><span class="link-destaque">saiba mais</span></b></a>.            
        </h3>
    </div>
</div>
<script src="https://www.google.com/recaptcha/api.js" async="" defer=""></script>
<script type="text/javascript" src="/js/buscalans.js"></script>
<script src="/js/valida.js"></script>
<script>
    $(function(){
       $('#avisenos').on('hidden.bs.modal', function () { $("#input").focus(); location.href=''; });
       
       $("#modal-esqueci-senha").on('shown.bs.modal', function () {
            var eml = $("#login").val();
            if(checkEmail(eml)){
                $("#emailEsqueciSenha").val("");
            } else{
                $("#emailEsqueciSenha").val(eml);
            }
            
        });
       
       $("#esqueci-senha").click(function(){
            $("#modal-esqueci-senha").modal();
        });
        
        $("#reenviarSenha").click(function(){
            
           waitingDialog.show('Por favor, aguarde...',{dialogSize: 'sm'});
           var erro = "";
           if($("#emailEsqueciSenha").val() != ""){
               $.ajax({
                    type: "POST",
                    dataType: "JSON",
                    url: "/creditos/ajax/dados-acesso.php",
                    data: {type: "esqueciMinhaSenha", login: $("#emailEsqueciSenha").val(), senha: true },
                    success: function(obj){

                        waitingDialog.hide();

                        if(obj.erro.length > 0){
                            $("#modal-esqueci-senha").modal('hide');
                            manipulaModal(1,obj.erro,"Erro");
                            return false;
                        }else if(obj.sucesso == true){
                            $("#modal-esqueci-senha").modal('hide');
                            manipulaModal(2,"A senha foi enviada para seu e-mail de cadastro","Senha enviada");
                            return false;
                        }else{
                            manipulaModal(1,"Login de usuário inválido ou não encontrado","Erro");  
                        }
                    },
                    error: function(){
                        waitingDialog.hide();
                        manipulaModal(1,"Erro desconhecido, favor entrar em contato com o nosso suporte.","Erro");
                        return false;
                    }
                });
           }else{
                waitingDialog.hide();
                erro = "Login inválido. Verifique os dados inseridos.";
                manipulaModal(1,erro,"Atenção");
                return false;
           }
       });
       
        $("a").mouseover(function() {
            $(this).css("text-decoration","underline");
        }).mouseout(function() {
            $(this).css("text-decoration","none");
        });

    });
</script>
<?php
require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/footer.php";