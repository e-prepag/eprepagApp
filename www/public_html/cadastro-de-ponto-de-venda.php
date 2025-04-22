<?php

$msg = '';
if ( array_key_exists('msg', $_GET) ) {
    if ( !empty($_GET['msg']) ) {
        $msg = nl2br(htmlentities($_GET['msg']));
    }
}
require_once "../includes/constantes.php";
// arquivo que � utilizado para salvar os PDVs que s� preencheram a primeira aba do cadastro
//require_once "../includes/pdv/inc_pdv_pendente.php";
require_once DIR_WEB . 'creditos/layout/bootstrap.php';

$uf = array("AC" => "Acre", "AL" => "Alagoas", "AM" => "Amazonas", "AP" => "Amap�", "BA" => "Bahia", "CE" => "Cear�", 
            "DF" => "Distrito Federal", "ES" => "Esp�rito Santo", "GO" => "Goi�s", "MA" => "Maranh�o", "MG" => "Minas Gerais", 
            "MS" => "Mato Grosso do Sul", "MT" => "Mato Grosso", "PA" => "Par�", "PB" => "Para�ba", "PE" => "Pernambuco", 
            "PI" => "Piaui", "PR" => "Paran�", "RJ" => "Rio de Janeiro", "RN" => "Rio Grande do Norte", "RO" => "Rond�nia", 
            "RR" => "Roraima", "RS" => "Rio Grande do Sul", "SC" => "Santa Catarina", "SE" => "Sergipe", "SP" => "S�o Paulo", "TO" => "Tocantins");

// <link rel="stylesheet" href="js/jquery/plugins/formvalidation/dist/css/formValidation.min.css">
$_css_add = array(
    '/js/formvalidation/dist/css/formValidation.min.css',
    '/js/step/jquery.steps.css',
);
//    <script src="js/jquery/plugins/formvalidation/dist/js/formValidation.min.js"></script>
$_js_add = array(
    '/js/step/jquery.steps.js',
    '/js/jquery.mask.min.js',
    '/js/formvalidation/dist/js/formValidation.min.js',
    '/js/formvalidation/dist/js/framework/bootstrap.min.js',
);
require_once DIR_WEB . 'creditos/includes/header-offline.php';
require_once DIR_CLASS . 'pdv/controller/OffLineController.class.php';
$controller = new OfflineController;
?>
<!-- CSS's -->
    <link href="/css/jquery-ui.css" rel="stylesheet" type="text/css" />
    <link href="/js/fancybox/jquery.fancybox.css" rel="stylesheet" type="text/css" />

    <?php
    if ( isset($_css_add) ) {
        if ( is_array($_css_add) && count($_css_add) > 0 ) {
            foreach ($_css_add as $css) {
                echo "<link href=\"$css\" rel=\"stylesheet\" type=\"text/css\" />".PHP_EOL;
            }
        }
    }
    ?>
<!-- Facebook Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window,document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '564270170805767'); 
fbq('track', 'PageView');
</script>
<noscript>
<img height="1" width="1" 
src="https://www.facebook.com/tr?id=564270170805767&ev=PageView
&noscript=1"/>
</noscript>
<!-- End Facebook Pixel Code -->
    <!-- Javascripts -->
    <!--[if lt IE 9]>
    <script src="<?php echo JS_DIR;?>js/html5shiv-respond.js"></script>
    <![endif]-->
    <script type="text/javascript" src="/js/Modernizr.js"></script>
    <script type="text/javascript" src="/js/fancybox/jquery.fancybox.js"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <?php
    if ( isset($_js_add) ) {
        if ( is_array($_js_add) && count($_js_add) > 0 ) {
            foreach ($_js_add as $js) {
                echo "<script type=\"text/javascript\" src=\"$js\"></script>".PHP_EOL;
            }
        }
    }
    if ( !empty($msg) ) {?>
        <script>
            $(document).ready(function(){
                $('.msgbox').fancybox().trigger('click');
            });
        </script>
    <?php } ?>
    <style type="text/css">
        .btnLogin{width: 70px !important; color: #fff;    background-color: #009b4a;    border-color: #4cae4c;    font-weight: bold;    font-style: italic;    display: inline-block;    margin-bottom: 0;    font-size: 16px;    text-align: center;    white-space: nowrap;    vertical-align: middle;    -ms-touch-action: manipulation;    touch-action: manipulation;    cursor: pointer;    -webkit-user-select: none;    -moz-user-select: none;    -ms-user-select: none;    user-select: none;    background-image: none;    border: 1px solid transparent;    border-radius: 4px;}
        
        .fancybox-wrap {
            top: 200px !important;
        }
        .fancybox-inner {
            overflow: hidden !important;
        }
        .msgbox{ width: 400px; height: auto !important; text-align: left; line-height: 19px; color: rgba(0, 0, 0, 0.8) }
        .msgbox h2{ font-size: 22px; color: #094E78; display: block; }
        
        /* Adjust the height of section */
        #profileForm .content {min-height: 100px;}

        #profileForm .content > .body {width: 100%;height: auto;position: relative;}
        
        .wizard > .steps > ul > li { width: 20% !important;}

        .preenchimento {color: #0c8224; font-weight: bold;}

        .form-control {display: inline !important;}

        .lista_preenchimento ul {list-style-type: disc;color: #0c8224;font-style: italic;}

        #info {position: relative;float: right;margin-top: -115px;color: #797576;font-weight: bold;font-style: italic;font-size: 13px;line-height: 22px;}
        #info2 > h3 {
            color: #0F64CF;
            font-weight: 500;
            font-size: 14px;
        }
        #info2 {
            border: 1px solid #0F64CF;
            position: relative;
            float: right;
            margin-top: -115px;
            padding: 10px;
            font-size: 12px;
            color: #5f5b5c;
            font-weight: 600;
            line-height: 22px;
            margin-bottom: 25px;
        }
        /*#info2 {position: relative;float: right;margin-top: -115px;color: #797576;font-weight: bold;font-style: italic;font-size: 13px;line-height: 22px;}*/
        .msgboxForm h2 {font-size: 22px;padding-bottom: 5px;margin-bottom: 15px;border-bottom: 1px solid darkgray;}
        .form-group {margin-bottom: 2px;}
        .cpf, .cnpj, .rg, .cep  {padding-left: 5px;width: 190px;}

        .errorsValidation {border-left: 5px solid #a94442;padding-left: 15px;}
        .errorsValidation li {list-style-type: none;}
        .errorsValidation li:before {content: '\b7\a0';}

        /*Verificar uso!*/

        .labels {float: left;color: gray;text-align: right;margin-right: 6pt;padding-right: 6pt;line-height: 15px;}
        .input_form label {
            margin-right: 10px;
        }
        td > input,
        td > select {
            margin-right: 70px;
            height: 20px;
        }
        .form_obs {
            color: gray;
            font-size: 13px;
            letter-spacing: -1px;
            margin-left: 5px;
        }
        .confirmacao {
            margin-left: 12%;
            width: 820px;
        }
        
        .font-p{
            font-size: 11px;
        }
        
        #profileForm .selectContainer .form-control-feedback {
            top: 0;
            right: -15px;
        }
		.new-titulo,.new-subtitulo,.new-texto{
			color: white;
		}
		.new-texto{
			text-align: justify;
		}
		
		ul li:first-child a {
			display: none !important;
		}

    </style>
    <div class="container p0 bg-branco p-bottom40 txt-cinza box-principal">
        <div style="background-color: #276e8d;" class="col-lg-9 col-md-9 col-sm-12 col-xs-12 lista_preenchimento">
		    <h1 class="new-titulo">SEJA UM PONTO DE VENDA E-PREPAG</h1>
		    <h4 class="new-subtitulo">BOM PARA SEUS CLIENTES. BOM PARA VOC�!</h4>
		    <p class="new-texto">
			  O PDV E-Prepag tem acesso aos produtos mais exclusivos do mercado digital. Venda na sua loja os cr�ditos dos games e plataforma digitais mais populares e aumente a renda do seu neg�cio,
			  voc� pode ter um lucro adicional em sua loja de R$5.000,00!		  
		    </p>	  
            <!--<h1 class="top20 txt-azul-claro text20"><strong>Cadastro de Ponto de Venda - Venda de cr�ditos para games</strong></h1>
            <h2 class="top20 txt-verde text17"><strong>Voc� pode ter um lucro adicional em sua loja de R$ 5.000,00!</strong></h2>-->
            <ul class="top10 new-lista" style="list-style-type: disc;padding-left: 70px;color: white;">
                <li>Sem taxa de inscri��o ou mensalidades</li>
                <li>Games como League of Legends*, Valorant*, Point Blank*, Garena Free Fire e CrossFire<br> *Produtos sujeitos a an�lise.</li>
                <li>Aumente o fluxo de clientes em at� 800 pessoas/m�s</li>
                <li>Comiss�o de at� 10%</li>
            </ul>
            <form id="profileForm" method="post" class="form-horizontal top20">
                <h2 class="hidden-xs hidden-sm">Cadastro</h2>
                <section data-step="0" style="padding: 15px 15px !important;">
				
				    <div class="form-group">
                        <label class="col-lg-4 col-md-4 col-sm-12 col-xs-12 control-label left10" for="name">Nome:<span class="required txt-vermelho">*</span></label>
                        <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12 right10">
                            <input type="text" class="form-control" name="name" maxlength="100" id="name" autocomplete="off"/>
                        </div>
                    </div>
					
                    <div class="form-group">
                        <label class="col-lg-4 col-md-4 col-sm-12 col-xs-12 control-label left10" for="username">Login<span class="required txt-vermelho">*</span></label>
                        <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12 right10">
                            <input type="text" class="form-control" name="username" maxlength="100" id="username" autocomplete="off"/>
                        </div>
                    </div>
					
					<div class="form-group">
                        <label class="col-lg-4 col-md-4 col-sm-12 col-xs-12 control-label" for="telefone_contato">WhatsApp <span class="required txt-vermelho">*</span></label>
                        <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12 p-left15i">
                            <div class="input-group">
                                <span class="input-group-addon">Brasil (+55)</span>
                                <input type="text" name="telefone_contato" id="telefone_contato" class="form-control telefone p-right0i"
                                                placeholder="(99) 99999-9999" maxlength="15" size="15"/>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 col-md-4 col-sm-12 col-xs-12 control-label" for="email">E-mail<span class="required txt-vermelho">*</span></label>

                        <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12">
                            <input type="text" class="form-control" name="email" id="email" autocomplete="off"/>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 col-md-4 col-sm-12 col-xs-12 control-label" for="email_confirmacao">Confirma��o de E-mail<span class="required txt-vermelho">*</span></label>

                        <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12">
                            <input type="text" class="form-control" maxlength="100" name="email_confirmacao" id="email_confirmacao" autocomplete="off" onpaste="return false" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 col-md-4 col-sm-12 col-xs-12 control-label" for="password">Senha <span class="required txt-vermelho">*</span></label>

                        <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12">
                            <input type="password" class="form-control novaSenha" minlength="6" maxlength="15" autocomplete="off" name="password" id="password"/>
                        </div>
                        <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12 col-md-offset-4 col-lg-offset-4 ">
                            <div class="progress w-auto">
                                <div class="progress-bar hidden progress-bar-danger" style="width: 33.33%">
                                  <span class="sr-only">33.33% Complete (danger)</span>
                                </div>
                                <div class="progress-bar hidden progress-bar-warning" style="width: 33.33%">
                                  <span class="sr-only">33.33% Complete (warning)</span>
                                </div>
                                <div class="progress-bar hidden progress-bar-success" style="width: 33.33%">
                                  <span class="sr-only">33.33% Complete (success)</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12 col-md-offset-4 col-lg-offset-4 text-danger font-p">
                            *Sua senha deve ter: de 6 a 12 caracteres, letras, n�meros, caracteres especiais (|,!,?,*,$,%, etc)
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-4 col-md-4 col-sm-12 col-xs-12 control-label" for="password_confirmacao">Confirma��o de senha<span class="required txt-vermelho">*</span></label>

                        <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12">
                            <input type="password" class="form-control confirmacaoSenha" name="password_confirmacao" id="password_confirmacao" autocomplete="off" onpaste="return false" />
                        </div>
                    </div>
					<div class="form-group">
                        <label class="col-lg-4 col-md-4 col-sm-12 col-xs-12 control-label" for="cnpj">CNPJ <span class="required txt-vermelho">*</span></label>
                        <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12">
                            <input type="text" id="cnpj" name="cnpj_empresa" size="20" class="form-control cnpj" required /><br />
                        </div>
                        <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12 col-md-offset-4 col-lg-offset-4">
                            <span class="form_obs">
                                (Sem pontos barras ou espa�os.)
                            </span>
                        </div>
                    </div>
					<div class="form-group">
                        <label class="col-lg-4 col-md-4 col-sm-12 col-xs-12 control-label" for="como_conheceu_eprepag">Como conheceu a E-Prepag? <span class="required txt-vermelho">*</span></label>
                        <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12">
							<select id="como_conheceu_eprepag" name="como_conheceu_eprepag" class="form-control" required>
								<option value="">Escolha uma op��o</option>
								<option value="facebook">Facebook;</option>
								<option value="instagram">Instagram;</option>
								<option value="youtube">YouTube;</option>
								<option value="google">Pesquisa no Google;</option>
								<option value="indicacao">Indica��o de um amigo;</option>
								<option value="outro">Outro.</option>
							</select>
							<div id="campo_outro" style="display: none;">
								<input type="text" id="campo_outro_input" name="campo_outro_input" class="form-control" placeholder="Informe aqui...">
							</div>
                        </div>
                    </div>
					<script>
						$('#como_conheceu_eprepag').on('change', function(){
							if ($(this).val() === 'outro') {
								
								// Exibe o campo adicional caso a op��o "outro" seja selecionada
								
								$('#campo_outro').slideDown(700);
								$('#campo_outro_input').prop('required', true);
								
							} else {
								
								// Esconde o campo adicional caso outra op��o seja selecionada
								
								$('#campo_outro').slideUp(700);
								$('#campo_outro_input').prop('required', false);
							}
						});
					</script>
					<!--<div class="form-group">
                        <label class="col-lg-4 col-md-4 col-sm-12 col-xs-12 control-label" for="tipo_estabelecimento">Tipo do estabelecimento <span class="required txt-vermelho">*</span></label>
                        <div class="col-lg-5 col-md-5 col-sm-11 col-xs-11 selectContainer" >
                            <select name="tipo_estabelecimento_empresa" id="tipo_estabelecimento" class="form-control select_input" required>
                                <option value=""> Selecione </option> -->
<?php
                               /* $sql = "select te_id,te_descricao from tb_tipo_estabelecimento where te_ativo = 1 order by te_descricao;";
                                $pdo = $con = ConnectionPDO::getConnection();
                                $pdo = $con->getLink();
                                $rs = $pdo->prepare($sql);
                                $rs->execute();
                                while ($rs_tipo_estabalecimento = $rs->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='".$rs_tipo_estabalecimento['te_id']."'> ".utf8_decode($rs_tipo_estabalecimento['te_descricao'])." </option>";
                                
                                }      */                      
?>
                <!--            </select>
                        </div>
                    </div> -->
					<div style="display: block; padding: 15px 15px; margin-top: 10px">
                        <?php echo "<textarea class='contrato' rows='12' readonly style='width: 100%; font-size: 13px'>" .  file_get_contents(DIR_WEB . "creditos/layout/contrato.php"). "</textarea>";?>
                        <div class="titulo_cinza form-group" style="font-size: 17px">
                            <label class="control-label" style="margin-left: 15px;">Termos de Uso <span class="required txt-vermelho">*</span></label>
							<span class="texto_cinza">
								<input type="checkbox" name="termos" id="termos" class="step2" />
								<label for="termos" style="font-size: 14px">Eu concordo com os Termos de Uso do sistema E-Prepag</label>
							</span>
                        </div>
					</div>
					
                    <div class="form-group">
						<div style="padding: 15px 0 15px 60px;">
							<div class="g-recaptcha" data-sitekey="6Lc4XtkkAAAAAJrfsAKc99enqDlxXz4uq86FI9_T"></div>
						</div>
                    </div>
                    <div class="form-group">
                        <div class="col-xs-9 col-xs-offset-3">
                            <ul id="errors0"></ul>
                        </div>
                    </div>
                </section>
				
                <div class="hidden ajax-load" style="position: absolute; left: 50%; bottom: 5px; transform: translate(-50%, 0px);">
                    <img src="/imagens/ajax-loader.gif" title="carregando..." alt=" carregando...">
                </div>

            </form>
            <section class="confirmacao hidden" id="confirmation">
                <div class="titulo">
                    <div class="col-md-3">
                        <img src="<?php echo IMG_LAN_URL;?>confirmation.png" title="confirma��o" alt="confirma��o" style="width: 125px;margin-top: 5px;">
                    </div>
                    <div class="col-md-6" style="padding-bottom: 40px">
                        <h1>Formul�rio de cadastro completo</h1>
                        <span class="texto_cinza">
                            Agora nossa equipe de neg�cios ir� verificar seus dados.<br />
                            Este processo de an�lise leva at� 3 dias �teis. Se for aprovado voc� receber� um e-mail com as instru��es do servi�o.<br />
                            <br />
                            Caso tenha alguma d�vida por favor entre em contato com nosso <a href="<?php echo $https;?>://www.e-prepag.com/support" target="_blank">suporte</a>.
                        </span>
                    </div>
                </div>
            </section>
            <div class="modal fade" id="welcomeModal" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">Welcome</h4>
                        </div>
                        <div class="modal-body">
                            <p class="text-center">Thanks for signing up</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-xs-12 col-sm-12 col-lg-3 txt-cinza bottom20">
            <h2 class="top20 txt-azul-claro text17"><strong>Quem pode se cadastrar:</strong></h2>
            <ul class="top10" style="list-style-type: disc;padding-left: 20px;">
                <li>Estabelecimentos comerciais com CNPJ</li>
            </ul>
            <h2 class="top20 txt-azul-claro  text17"><strong>Como funciona a venda de cr�ditos/PINs:</strong></h2>
            <p class="top10">Ap�s ser aprovado como parceiro, voc� efetuar� login com o seu CNPJ e senha cadastrada e j� estar� habilitado para iniciar as vendas de forma simples,
 			f�cil e r�pida e 100% Online.<br> Al�m disso disponibilizamos nossa API de Integra��o onde voc� pode automatizar as vendas do seu site.</p>
            
            <p class="txt-azul-claro top10"><strong>Valida��o cadastral:</strong></p>
			<ul class="top10" style="list-style-type: disc;padding-left: 20px;">
                <li>Ap�s o preenchimento do cadastro voc� receber� via e-mail e WhatsApp o link para realizar o nosso onboarding, nesse ser� necess�rio enviar o seu Documento oficial com foto;</li>
				<li>selfie para validarmos sua identidade;</li>
				<li>comprovante de endere�o de no m�ximo 90 dias (conforme registrado na Receita Federal) e a identifica��o da loja 
				(link da loja online, foto da fachada, print do google maps ou print das redes sociais), tais informa��es s�o importantes para garantirmos sua seguran�a.</li>
            </ul>
        </div>
    </div>
    <style>
        #ourError > label {font-size: 22px;margin-bottom: 10px;}
        #ourError > ul > li:before {content: ' - ';}
        #ourError > ul > li {margin-left: 10px;}
    </style>
    <div id="ourError" class="error-msg hidden">
        <label>Erro(s) encontrado(s):</label>
        <ul class="list-errors"></ul>
    </div>
    <div id="sociosError" class="error-msg-socios hidden">
        <label>Erro(s) encontrado(s):</label>
        <ul class="list-errors-socios"></ul>
    </div>
<script>	
        
    $(document).ready(function () {
		
        function adjustIframeHeight() {
            var $body = $('body'),
                $iframe = $body.data('iframe.fv');
            if ($iframe) {
                // Adjust the height of iframe
                $iframe.height($body.height());
            }
        }
        
        $(window).keydown(function(event){
            if(event.keyCode == 13 || event.keywich == 13) {
              event.preventDefault();
              return false;
            }
        });
        
        $(".form-control")
                .attr("autofocus","true");
           // http://formvalidation.io/examples/adding-dynamic-field/
            // IMPORTANT: You must call .steps() before calling .()
            var largura = $(window).width();
            
            var tituloTmpl = (largura < 540) ? "#title#" : "<span class=\"number\">#index#.</span> #title#";
            
            $('#profileForm')
                .steps({
                    headerTag: 'h2',
                    bodyTag: 'section',
                    transitionEffect: "slideLeft",
                    titleTemplate: tituloTmpl,
                    startIndex: 0,
                    container: 'tooltip',
                    /* Labels */
                    labels: {
                        finish: "Finalizar Cadastro",
                        next: "Pr�ximo",
                        previous: "Anterior",
                        loading: "Carregando ..."
                    },
                    onInit: function (event, currentIndex) {
                        $('.cnpj').mask('99.999.999/9999-99', {placeholder: '__.___.___/____-__'});
                        $('.cpf').mask('999.999.999-99', {placeholder: '___.___.___-__'});
                        //$('.rg').mask('##.###.###-?');
                        $('.cep').mask('99999-999', {placeholder: '_____-___'});


						var SPMaskBehavior = function (val) {
						  return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
						},
						spOptions = {
						  onKeyPress: function(val, e, field, options) {
							  field.mask(SPMaskBehavior.apply({}, arguments), options);
							}
						};

						//$('.telefone').mask(SPMaskBehavior, spOptions);
                        $('.telefone').mask('(99) 99999-9999'); //
                        //$('.celular').mask('(00) 90000-0000');
                        $(".data").mask("99/99/9999");
                    },
                    onStepChanged: function (e, currentIndex, priorIndex) {
                        //adjustIframeHeight();
                    },
                    // Triggered when clicking the Previous/Next buttons
                    onStepChanging: function (e, currentIndex, newIndex) {

                        var fv = $('#profileForm').data('formValidation'),
                            $container = $('#profileForm').find('section[data-step="' + currentIndex + '"]');

                        if ( newIndex < currentIndex ) {
                            return true;
                        }
                        
                        //Validando a aba S�cios (por conter campos din�micos n�o foi poss�vel utilizar da valida��o do framework formValidation.io, utilizado nas outras abas)
                        if (currentIndex === 3) {
                            return validaCamposSocios();
                        }

                        // Validate the container
                        fv.validateContainer($container);

                        var isValidStep = fv.isValidContainer($container);
                        
                        if(isValidStep === null){
                            return false;
                        }else if (isValidStep === false) {
                            $('#ourError').removeClass('hidden').fancybox().trigger('click');
                            return false;
                        } 
                        if ( currentIndex === 0 ) {
                           $.post('/creditos/layout/pdv_pendente.php',{email: $('#email').val(), username: $("#username").val()},function(data){});
                        }
                        
                        if (newIndex > 0) {
                            $('#info2').hide('slow');
                            $('#explicacao').hide('slow');
                        } else {
                            $('#info2').show('slow');
                            $('#explicacao').show('slow');
                        }
                        
                        return true;
                    },
                    // Triggered when clicking the Finish button
                    onFinishing: function (e, currentIndex) {

                        if(grecaptcha.getResponse() == "" || grecaptcha.getResponse().length == 0){
							manipulaModal(1,"Voc� deve fazer a verifica��o do RECAPTCHA para finalizar seu cadastro.",'Erro');
							return false;
						}

                        var fv = $('#profileForm').data('formValidation'), // FormValidation instance
                        // The current step container
                            $container = $('#profileForm').find('section[data-step="' + currentIndex + '"]');
                        // Validate the container
                        fv.validateContainer($container);

                        var isValidStep = fv.isValidContainer($container);
                        if (isValidStep === false || isValidStep === null) {
                            $('#ourError').removeClass('hidden').fancybox().trigger('click');
                            // Do not jump to the next step
                            return false;
                        }
						     
                        $.ajax({
                            type: "POST",
                            url: "/ajax/pdv/ajaxRegistraUsuario.php",
                            data: $("#profileForm").serialize(),
                            beforeSend: function(){
                                $("#profileForm-t-0").addClass("hidden");
                                $("#profileForm-t-1").addClass("hidden");
                                $("#profileForm-t-2").addClass("hidden");
                                $("#profileForm-t-3").addClass("hidden");
                                $("#profileForm-t-4").addClass("hidden");
                                $(".actions").addClass("hidden");
                                //$('#profileForm').find('section[data-step="4"]').addClass("hidden");
                                $(".ajax-load").removeClass("hidden");
                            },
                            success: function(txt){
                                
                                txt = txt.trim();
                                if(txt == ""){ //!txt 
								
   								   // Perform an ajax request to validate if the username and e-mail has bem already taken 
									
									document.cookie = "meuCookie=" + JSON.stringify({
										nome: $("#username").val(),
										email: $("#email").val()
									});
								
									
                                   location.href = "/cadastro_finalizado.php";
                                }else{
                                    if(txt.indexOf("Email") !== -1){
                                        if(txt.indexOf("cadastrado") !== -1){
                                            txt = '<ul class="list-errors"><li data-field="email"><span class="error-msg-info">E-mail j� cadastrado. Escolha outro.</span></li></ul>';
                                        }else if(txt.indexOf("preenchido")){
                                            txt = '<ul class="list-errors"><li data-field="email"><span class="error-msg-info">E-mail n�o pode ficar em branco</span></li></ul></div>'
                                        }    
                                        $("#email").val("");
                                        $("#email_confirmacao").val("");
                                        $("#profileForm-t-0").trigger("click");
                                    }
									else if(txt.indexOf("Login") !== -1){
                                        if(txt.indexOf("cadastrado") !== -1){
                                            txt = '<ul class="list-errors"><li data-field="username"><span class="error-msg-info">Login j� cadastrado. Escolha outro.</span></li></ul>';
                                        }else if(txt.indexOf("preenchido")){
                                            txt = '<ul class="list-errors"><li data-field="username"><span class="error-msg-info">Login n�o pode ficar em branco</span></li></ul></div>'
                                        }    
                                        $("#username").val("");
                                        $("#profileForm-t-0").trigger("click");
                                    }else if(txt.indexOf("CNPJ") !== -1){
                                        if(txt.indexOf("cadastrado") !== -1){
                                            txt = '<ul class="list-errors"><li data-field="cnpj"><span class="error-msg-info">O CNPJ n�o atende aos requisitos para realizar o cadastro.</span></li></ul>';
                                        }else if(txt.indexOf("preenchido")){
                                            txt = '<ul class="list-errors"><li data-field="cnpj"><span class="error-msg-info">CNPJ n�o pode ficar em branco</span></li></ul></div>'
                                        }    
                                        $("#cnpj").val("");
                                        $("#profileForm-t-2").trigger("click");
                                    }
                                    


                                    $("#profileForm-t-0").removeClass("hidden");
                                    $("#profileForm-t-1").removeClass("hidden");
                                    $("#profileForm-t-2").removeClass("hidden");
                                    $("#profileForm-t-3").removeClass("hidden");
                                    $("#profileForm-t-4").removeClass("hidden");
                                    $(".actions").removeClass("hidden");
                                    //$('#profileForm').find('section[data-step="4"]').removeClass("hidden");
                                    $(".ajax-load").addClass("hidden");

                                    $('#ourError').html("<label>Erro(s) encontrado(s):</label><br>"+txt);
                                    $('#ourError').removeClass('hidden').fancybox().trigger('click');
                                    $("#email").trigger("blur");
                                    $("#cnpj").trigger("blur");
                                    $("#username").trigger("blur");
                                }
                            }
                        });

                    }
                })
                .formValidation({
                    framework: 'bootstrap',
                    icon: {
                        valid: 'glyphicon glyphicon-ok',
                        invalid: 'glyphicon glyphicon-remove',
                        validating: 'glyphicon glyphicon-refresh'
                    },
                    // This option will not ignore invisible fields which belong to inactive panels
                    //exclude: ':disabled',
                    fields: {
                        // Conta
						name: {
                            validators: {
                                stringLength: {min:5, max:100, message: 'Nome deve ter o m�nimo de 5 caracteres'},
                                notEmpty: {
                                    message: 'Campo "Nome" n�o pode ficar em branco'
                                }
                            }
                        },
                        username: {
                            threshold: 6,
                            trigger: 'blur',
                            validators: {
                                stringLength: {min:6,max:100,message: 'O nome de usu�rio deve ter mais que 6 e menos que 100 caracteres de tamanho'},
                                remote: {
                                    message:'Nome de usu�rio indispon�vel. Escolha outro.',
                                    url: '/creditos/layout/ajaxCadastro.php',
                                    type: 'POST',
                                    data: {field: 'username'}
                                },
                                notEmpty: {message: 'Nome de usu�rio n�o pode ficar em branco.'},
                                regexp: {  
                                    regexp: /^[a-zA-Z0-9_\.]+$/,
                                    message: 'O nome de usu�rio deve ser somente alfanum�rico, pontos "." e underline "_"'
                                }
                                
                            }
                        }, 
                        email: {
                            threshold: 6,
                            trigger: 'blur',
                            validators: {
                                stringLength: {min:5, max: 100, message: 'O e-mail deve ter o m�nimo de 5 caracteres'},
                                remote: {
                                    message: 'E-mail j� cadastrado. Escolha outro.',
                                    url: '/creditos/layout/ajaxVerificaEmail.php',
                                    type: 'POST',
                                    data: { field: 'email' },
                                    delay: 1000, // opcional: adiciona um atraso antes de enviar a requisi��o
                                    // Fun��o chamada ap�s a requisi��o AJAX
                                    callback: function (value, validator, $field) {
                                        // Aqui voc� pode realizar uma a��o caso a requisi��o falhe
                                        return true; // Retorna true se a requisi��o foi bem-sucedida
                                    },
                                    // Fun��o chamada em caso de erro na requisi��o
                                    error: function () {
                                        // Aqui voc� pode exibir uma mensagem de erro personalizada
                                        alert("Erro ao verificar o e-mail. Tente novamente.");
                                        return false; // Retorna false para que a valida��o falhe
                                    }
                                },
                                notEmpty: {message: 'E-mail n�o pode ficar em branco'},
                                //emailAddress: {message: 'O e-mail digitado n�o � v�lido'},
                                regexp: {
                                    regexp: '^[^@\\s]+@([^@\\s]+\\.)+[^@\\s]+$',
                                    message: 'O e-mail digitado n�o � v�lido'
                                }
                            }
                        },
                        email_confirmacao: {
                            validators: {
                                stringLength: {min:5, message: 'O e-mail deve ter o m�nimo de 5 caracteres'},
                                notEmpty: {message: "E-mail de confirma��o n�o pode ficar em branco"},
                                identical: {field: 'email',message: 'O e-mail de confirma��o � diferente do e-mail original'}
                            }
                        },
                        password: {
                            validators: {
                                stringLength: {min:6, max:15, message: 'A senha deve ter entre 6 e 12 caracteres'},
                                notEmpty: {message: 'Campo senha n�o pode ficar em branco'},
                                different: {field: 'username',message: 'A senha n�o pode ser igual ao nome de usu�rio'}
                            }
                        },
                        password_confirmacao: {
                            validators: {
                                stringLength: {min:6, max:15, message: 'A senha de confirma��o deve ter entre 6 e 12 caracteres'},
                                notEmpty: {message: 'Campo confirma��o de senha n�o pode ficar em branco'},
                                callback: {
                                    message: 'Senha inv�lida',
                                    callback: function(value, validator, $field) {
                                            var erro = validaFormSenha(); //funcao esta em /js/validaSenha.js
                                            if (erro.length > 0) {
                                                return {
                                                    valid: false,
                                                    message: erro.join("\n")
                                                };
                                            }else{
                                                return true;
                                                
                                            }
                                    }
                                }
                            }
                        },
                        // Fim Conta
                        // Contato
                        telefone_contato: {
                           validators: {
                                notEmpty: {message: 'Campo telefone n�o pode ficar em branco'},
                                regexp: {
                                    regexp: /\(\d{2}\)\s\d{4,5}-\d{4}$/,
                                    'message': 'N�mero de telefone inv�lido'
                                }
                            }
                        },
                     //   celular_contato: {
                        //    validators: {
                        //        notEmpty: {message: 'Campo celular n�o pode ficar em branco'},
                          //      regexp: {
                            //        regexp: /\(\d{2}\)\s\d{4,5}-\d{3,4}$/,
                            //        'message': 'N�mero de celular inv�lido'
                          //      }
                          //  }
                      //  },
                        //TIRANDO PARTE DO REPRESENTANTE
//                        data_nascimento: {
//                            validators: {
//                                stringLength: {min:10, max: 10, message: 'Data de nascimento inv�lida.'},
//                                notEmpty: {message: 'Data de nascimento n�o pode ficar em branco'}
//                            }
//                        },
//                        cpf_representante: {
//                            threshold: 12,
//                            trigger: 'blur',
//                            validators: {
//                                notEmpty: {
//                                    message: 'Campo CPF n�o pode ficar em branco'
//                                },
//                                id: {
//                                    country: 'BR',
//                                    message: 'CPF inv�lido'
//                                }
    //                                ,
    //                                remote: {
    //                                    message:'CPF j� cadastrado.',
    //                                    url: '/prepag2/dist_commerce/layout/ajaxCadastro.php',
    //                                    type: 'POST',
    //                                    data: {field: 'cpf_representante'}
    //                                }
//                                }
//                        },
//                        rg_representante: {
//                            validators: {
//                                stringLength: {min:4,message: 'O RG deve ter no m�nimo 4 digitos.'},
//                                notEmpty: {
//                                    message: 'Campo RG n�o pode ficar em branco'
//                                }
//                            }
//                        },
                        //Fim Contato
                        // Empresa
                       // fantasia_empresa: {
                         //   validators: {
                           //     stringLength: {min:5, max:100, message: 'Nome fantasia deve ter o m�nimo de 5 caracteres'},
                           //     notEmpty: {
                      //              message: 'Campo "Nome Fantasia" n�o pode ficar em branco'
                          //      }
                     //       }
                      //  },

                      //  razao_social_empresa: {
                          //  validators: {
                              //  stringLength: {min:5, message: 'Raz�o social deve ter o m�nimo de 5 caracteres'},
                              //  notEmpty: {
                                 //   message: 'Campo "Raz�o Social" n�o pode ficar em branco'
                              //  }
                          //  }
                      //  },
						
                        cnpj_empresa: {
                            threshold: 16,
                            trigger: 'blur',
                            validators: {
                                notEmpty: {
                                    message: 'Campo "CNJP" n�o pode ficar em branco'
                                },
                                vat: {
                                    country: 'BR',
                                    message: 'CNPJ inv�lido'
                                },
                                remote: {
                                    message:'CNPJ j� cadastrado.',
                                    url: '/creditos/layout/ajaxCadastro.php',
                                    type: 'POST',
                                   data: {field: 'cnpj_empresa'}
                                }, 
								remote: {
                                    message:'CNPJ invalido na receita federal.',
                                    url: '/creditos/layout/ajaxCadastro.php',
                                    type: 'POST',
                                   data: {field: 'cnpj_empresa', rf: true}
                                }
                            }
                        },
						
						como_conheceu_eprepag: {
                            validators: {
                                notEmpty: {
                                    message: 'Escolha uma op��o que diga como conheceu a E-prepag'
                                }
                            }
                        },
						
						campo_outro_input: {
                            validators: {
                                notEmpty: {
                                    message: 'Informe como conheceu a E-prepag'
                                }
                            }
						},
						
 
                        tipo_estabelecimento_empresa: {
                            validators: {
                                notEmpty: {
                                    message: 'Campo "Tipo de Estabelecimento" n�o pode ficar em branco'
                                }
                            }
                        },

                        outro_estabelecimento: {
                            validators: {
                                notEmpty: {
                                    message: 'Especifique o outro tipo'
                                }
                            }
                        },

                       // faturamento_medio: {
                          //  validators: {
                            //    notEmpty: {
                                  //  message: 'Campo "Faturamento M�dio" n�o pode ficar em branco'
                              //  }
                          //  }
                      ///  },

                      //  cep_empresa: {
                          //  validators: {
                            //    stringLength: {min:8, message: 'CEP inv�lido'},
                            //    notEmpty: {
                           //         message: 'Campo "CEP" n�o pode ficar em branco'
                            //    }
                           // }
                       // },

                       // estado_empresa: {
                         //   validators: {
                             //   notEmpty: {
                               //     message: 'Escolha o Estado'
                              //  }
                       //     }
                       // },

                      //  cidade_empresa: {
                           // validators: {
                            //    stringLength: {max:100, message: 'Cidade deve ter no m�ximo 100 caracteres'},
                             //   notEmpty: {
                              //      message: 'Campo "Cidade" n�o pode ficar em branco'
                              //  }
                           // }
                       // },

                      //  bairro_empresa: {
                          //  validators: {
                             //   stringLength: {max:100, message: 'Bairro deve ter no m�ximo 100 caracteres'},
                              //  notEmpty: {
                              //      message: 'Campo "Bairro" n�o pode ficar em branco'
                             //   }
                          //  }
                      //  },

                      //  endereco_empresa: {
                          //  validators: {
                              //  stringLength: {max:100, message: 'Endere�o deve ter no m�ximo 100 caracteres'},
                               // notEmpty: {
                                //    message: 'Campo "Endere�o" n�o pode ficar em branco'
                             //   }
                         //   }
                      //  },

                      //  numero_empresa: {
                          //  validators: {
                              //  stringLength: {max:10, message: 'N�mero deve ter no m�ximo 10 caracteres'},
                            //    notEmpty: {
                            //        message: 'Campo "N�mero" n�o pode ficar em branco'
                            //    }
                         //   }
                     //   },
                        /// Fim Empresa
                        
                        // Confirma��o
                        termos: {
                            validators: {
                                notEmpty: {
                                    message: '� necess�rio concordar com os termos'
                                }
                            }
                        }
                        // Fim Confirma��o
                    }
                }).on('err.field.fv', function(e, data) {
                    var id = $('li.current').children('a').attr('id');
                    //var step = id.replace(/[^\d]/g, '');
                    var messages = data.fv.getMessages(data.element);
                    // Remove the field messages if they're already available
                    $('#ourError').find('li[data-field="' + data.field + '"]').remove();

                    // Loop over the messages
                    for (var i in messages) {
                        // Create new 'li' element to show the message
                        $('<li/>')
                            .attr('data-field', data.field)
                            .wrapInner(
                            $('<span/>')
                                .addClass('error-msg-info')
                                .html(messages[i])
                                .on('click', function(e) {
                                    // Focus on the invalid field
                                    //data.element.focus();
                                    parent.$.fancybox.close();
                                    $.fancybox.close();
                                })
                        ).appendTo('.list-errors');
                    }
                    data.element
                        .data('fv.messages')
                        .find('.help-block[data-fv-for="' + data.field + '"]')
                        .hide();
                })
                .on('success.field.fv', function(e, data) {
                    var id = $('li.current').children('a').attr('id');
                    var step = id.replace(/[^\d]/g, '');
                    $('#ourError').find('li[data-field="' + data.field + '"]').remove();
            });
        $(".help-block").addClass("hidden");
       /* $('#tipo_estabelecimento').change(function(){
            var fv = $('#profileForm').data('formValidation');
            if ( $(this).val() === 'Outros' ) {
                fv.enableFieldValidators('outro_estabelecimento', true).revalidateField('outro_estabelecimento');
                $('#outro_estabelecimento_div').removeClass('hidden');
            } else {
                $('#outro_estabelecimento_div').addClass('hidden');
                fv.enableFieldValidators('outro_estabelecimento', false).revalidateField('outro_estabelecimento');
            }
        }); */
            
        $("#password_confirmacao").blur(function(){
            $('#profileForm').formValidation('revalidateField', 'password_confirmacao');
        });
        
        $("#password").blur(function(){
            $('#profileForm').formValidation('revalidateField', 'password_confirmacao');
        });

        //Fun��o para buscar o endere�o.
        $("#cep").blur(function(){
            setCep($(this).val());
        });
        
//        $("#cpf_representante").keyup(function(){
//            pegaNomeRF();
//        }).keydown(function(){
//            pegaNomeRF();
//        }).blur(function(){
//            pegaNomeRF();
//        });
//        
//        $("#data_nascimento").keyup(function(){
//            pegaNomeRF();
//        }).keydown(function(){
//            pegaNomeRF();
//        }).blur(function(){
//            pegaNomeRF();
//        });
        
        $("#enviaNewsletter").click(function(){
            $.ajax({
                url: '/ajax/newsletter.php',
                type: "POST",
                data: { email: $("#newsletter").val() },
                dataType : "JSON",
                beforeSend: function(){
                    waitingDialog.show('Por favor, aguarde...',{dialogSize: 'sm'});
                },
                success: function(ret){
                    waitingDialog.hide();
                    
                    if(ret){
                        
                        var titulo = (ret.tipo == 1) ? "Erro" : "Sucesso";
                        
                        manipulaModal(ret.tipo, ret.msg,titulo);
                    }else{
                        manipulaModal(1, "Erro ao salvar e-mail, por favor tente novamente.",'Erro');
                    }
                    
                },
                error: function(x,y){
                    waitingDialog.hide();
                    return false;
                }
            });
        });
        
        $("input[name='porcentagem_socios[0]']").val('100,00%');
        
        $(".validar_campos_cpf , .validar_campos_data").on('click keyup keydown', function(){
            
            if($(this).closest('.form-group').hasClass('has-success') === true || $(this).closest('.form-group').hasClass('has-error') === true){
                ($(this).closest('.form-group').hasClass('has-success') === true) ? 
                $(this).closest('.form-group').removeClass('has-feedback has-success') : $(this).closest('.form-group').removeClass('has-feedback has-error');
                
                $(this).next('.form-control-feedback').remove();
            }
        });
        
        var indice_socios = 0;
        
        $(document).on('click','.remDiv, .addDiv', function(e) {
            
            thisClass = e.target.className;
            var get_action = thisClass.split(" ");
            
            //div correspondente do bot�o 'remover' em que o usu�rio clicou
            var current_div = get_action[1];
            
            //Recuperando a classe 'remDiv' ou 'addDiv'
            thisClass = get_action[0];
            
            if(thisClass == 'remDiv'){
                
                if(current_div < indice_socios){
                    // remove a �ltima div adicionada, independente de qual bot�o 'Remover' o usu�rio clicar
                    var ind = indice_socios;
                    $('.' + ind).closest('.remover-div').prev().add($('.' + ind).closest('.remover-div')).remove();
                    
                } else{
                    $(this).closest('.remover-div').prev().add($(this).closest('.remover-div')).remove();
                }
                
                indice_socios--;
                    
            } else{
                (indice_socios === 0 && $("input[name='porcentagem_socios[0]']").val() === '100,00%') ? $("input[name='porcentagem_socios[0]']").val("") : '0';
                
                indice_socios++;
                
                //bloco adicionado no click do bot�o 'Add S�cio'
                $('#p_inputs').append(
                    '<div class="remover-div"><legend class="txt-cinza top10 text17">S�cio '+(indice_socios+1)+'</legend><div class="form-group"><label class="col-lg-4 col-md-4 col-sm-12 col-xs-12 control-label" for="cpf_socios">CPF <span class="required txt-vermelho">*</span></label><div class="col-lg-5 col-md-5 col-sm-12 col-xs-12 marcador_cpf"><input type="text" class="form-control cpf validar_campos_cpf" name="cpf_socios['+indice_socios+']" onblur="pegaNomeSocio('+indice_socios+');" size="15" /></div></div><div class="form-group"><label class="col-lg-4 col-md-4 col-sm-12 col-xs-12 control-label" for="data_nascimento_socios">Data Nascimento <span class="required txt-vermelho">*</span></label><div class="col-lg-5 col-md-5 col-sm-12 col-xs-12 marcador_data"><input type="text" class="form-control data validar_campos_data" name="data_nascimento_socios['+indice_socios+']" onblur="pegaNomeSocio('+indice_socios+');" size="15" /></div></div><div class="form-group"><label class="col-lg-4 col-md-4 col-sm-12 col-xs-12 control-label" for="nome_socios">Nome</label><div class="col-lg-5 col-md-5 col-sm-12 col-xs-12"><input type="text" class="form-control validar_campos_nome" readonly="readonly" title="Campo Nome ser� preenchido de acordo com o CPF e Data de Nascimento informados" name="nome_socios['+indice_socios+']" size="15" /></div></div><div class="form-group"><label class="col-lg-4 col-md-4 col-sm-12 col-xs-12 control-label" for="porcentagem_socios">Porcentagem na Empresa <span class="required txt-vermelho">*</span></label><div class="col-lg-5 col-md-5 col-sm-12 col-xs-12 marcador_porcentagem"><input type="text" class="form-control validar_campos_porcentagem" onblur="formata_porcentagem('+indice_socios+');" name="porcentagem_socios['+indice_socios+']" maxlength="7" placeholder="(Apenas n�meros)" size="15" /></div></div></div>'
                );
                //bot�o 'Remover' o bloco adicionado
                $('#p_inputs').append('<div class="remover-div"><div class="form-group"><div class="col-lg-12 col-md-12 col-sm-12 col-xs-12"><button type="button" class="remDiv '+indice_socios+' btn btn-danger pull-right">Remover</button></div></div></div>');
                
            }
        });
        
    });
    
    var searching = false;
    //funcao que busca o nome do representante a partir do cpf e da data de nascimento
//    function pegaNomeRF(){
//        
//        if($("#cpf_representante").val().length == 14 && $("#data_nascimento").val().length == 10 && !searching)
//        {
//            $.ajax({
//                type: "POST",
//                url: "/ajax/ajaxCpf.php",
//                dataType : "json",
//                data: { cpf : $("#cpf_representante").val(), dataNascimento : $("#data_nascimento").val()},
//                beforeSend: function(){
//                    searching = true;
//                    $(".actions").addClass("hidden");
//                    waitingDialog.show('Por favor, aguarde...',{dialogSize: 'sm'});
//                },success: function(txt){
//                    searching = false;
//                    $(".actions").removeClass("hidden");
//                    waitingDialog.hide();
//                    var fv = $('#profileForm').data('formValidation');
//                    if(txt.erros.length > 0)
//                    {
//                        $("#cpf_representante").val("");
//                        $("#data_nascimento").val("");
//                        fv.enableFieldValidators('cpf_representante', true).revalidateField('cpf_representante');
//                        fv.enableFieldValidators('data_nascimento', true).revalidateField('data_nascimento');
//                        alert(txt.erros);
//                    }
//                    else
//                    {
//                        $("#nome_representante").val(txt.nome.substr(0, 480)); //20 pq insere em mais campos e tem um limite de 20 caracteres que se o nome for maior, da problema
//                    }
//                },
//                error: function(x,y){
//                    waitingDialog.hide();
//                    return false;
//                }
//            });
//        }
//    }
    
    function formata_porcentagem(ind){
        var aux_val = $("input[name='porcentagem_socios["+ind+"]']").val();
        if(aux_val.length <= 3 && aux_val.trim() !== "" && $.isNumeric(aux_val.replace(',' , '.'))){
            (aux_val.indexOf(',') === -1 && aux_val.indexOf('.') === -1) ? $("input[name='porcentagem_socios["+ind+"]']").val(aux_val+',00%') : ((aux_val.length === (aux_val.indexOf(',') + 1)) || (aux_val.length === (aux_val.indexOf('.') + 1))) ? $("input[name='porcentagem_socios["+ind+"]']").val(aux_val+'00%') : 0;
        } else{
            if((aux_val.length === 5 && (aux_val.indexOf(',') === 2 || aux_val.indexOf('.') === 2)) || (aux_val.length === 6 && (aux_val.indexOf(',') === 3 || aux_val.indexOf('.') === 3))){
                $("input[name='porcentagem_socios["+ind+"]']").val(aux_val+'%');
            }
        }
    }
       
    function pegaNomeSocio(index){
        
        if($("input[name='cpf_socios["+index+"]']").val().length == 14 && $("input[name='data_nascimento_socios["+index+"]']").val().length == 10 && !searching){
            $.ajax({
                type: "POST",
                url: "/ajax/ajaxCpf.php",
                dataType : "json",
                data: { cpf : $("input[name='cpf_socios["+index+"]']").val(), dataNascimento : $("input[name='data_nascimento_socios["+index+"]']").val()},
                beforeSend: function(){
                    searching = true;
                    $(".actions").addClass("hidden");
                    waitingDialog.show('Por favor, aguarde...',{dialogSize: 'sm'});
                },success: function(txt){
                    searching = false;
                    $(".actions").removeClass("hidden");
                    waitingDialog.hide();
                    if(txt.erros.length > 0){
                        $("input[name='cpf_socios["+index+"]']").val("");
                        $("input[name='data_nascimento_socios["+index+"]']").val("");
                        
                        if($("input[name='cpf_socios["+index+"]']").closest('.form-group').hasClass('has-success') === true){
                            $("input[name='cpf_socios["+index+"]']").closest('.form-group').removeClass('has-success');
                            $("input[name='cpf_socios["+index+"]']").closest('.form-group').addClass('has-error');
                            
                            $("input[name='cpf_socios["+index+"]']").next('.form-control-feedback').removeClass('glyphicon-ok');
                            $("input[name='cpf_socios["+index+"]']").next('.form-control-feedback').addClass('glyphicon-remove');
                            
                        } else{
                            $("input[name='cpf_socios["+index+"]']").closest('.form-group').addClass('has-feedback has-error');
                            $("input[name='cpf_socios["+index+"]']").closest('.marcador_cpf').append('<i class="form-control-feedback glyphicon glyphicon-remove" aria-hidden="true"></i> ');
                        }
                        
                        if($("input[name='data_nascimento_socios["+index+"]']").closest('.form-group').hasClass('has-success') === true){
                            $("input[name='data_nascimento_socios["+index+"]']").closest('.form-group').removeClass('has-success');
                            $("input[name='data_nascimento_socios["+index+"]']").closest('.form-group').addClass('has-error');
                            
                            $("input[name='data_nascimento_socios["+index+"]']").next('.form-control-feedback').removeClass('glyphicon-ok');
                            $("input[name='data_nascimento_socios["+index+"]']").next('.form-control-feedback').addClass('glyphicon-remove');
                            
                        } else{
                            $("input[name='data_nascimento_socios["+index+"]']").closest('.form-group').addClass('has-feedback has-error');
                            $("input[name='data_nascimento_socios["+index+"]']").closest('.marcador_data').append('<i class="form-control-feedback glyphicon glyphicon-remove" aria-hidden="true"></i> ');
                        }
                        
                        $("input[name='nome_socios["+index+"]']").val("");
                        
                        alert(txt.erros);
                    }
                    else {
                        $("input[name='nome_socios["+index+"]']").val(txt.nome.substr(0, 480));
                        
                        if($("input[name='cpf_socios["+index+"]']").closest('.form-group').hasClass('has-error') === true){
                            $("input[name='cpf_socios["+index+"]']").closest('.form-group').removeClass('has-error');
                            $("input[name='cpf_socios["+index+"]']").closest('.form-group').addClass('has-success');
                            
                            $("input[name='cpf_socios["+index+"]']").next('.form-control-feedback').removeClass('glyphicon-remove');
                            $("input[name='cpf_socios["+index+"]']").next('.form-control-feedback').addClass('glyphicon-ok');
                        } else{
                            
                            if($("input[name='cpf_socios["+index+"]']").closest('.form-group').hasClass('has-success') === false){
                                $("input[name='cpf_socios["+index+"]']").closest('.form-group').addClass('has-feedback has-success');
                                $("input[name='cpf_socios["+index+"]']").closest('.marcador_cpf').append('<i class="form-control-feedback glyphicon glyphicon-ok" aria-hidden="true"></i> ');
                            } 
                        }
                        
                        if($("input[name='data_nascimento_socios["+index+"]']").closest('.form-group').hasClass('has-error') === true){
                            $("input[name='data_nascimento_socios["+index+"]']").closest('.form-group').removeClass('has-error');
                            $("input[name='data_nascimento_socios["+index+"]']").closest('.form-group').addClass('has-success');
                            
                            $("input[name='data_nascimento_socios["+index+"]']").next('.form-control-feedback').removeClass('glyphicon-remove');
                            $("input[name='data_nascimento_socios["+index+"]']").next('.form-control-feedback').addClass('glyphicon-ok');
                        } else{
                            
                            if($("input[name='data_nascimento_socios["+index+"]']").closest('.form-group').hasClass('has-success') === false){
                                $("input[name='data_nascimento_socios["+index+"]']").closest('.form-group').addClass('has-feedback has-success');
                                $("input[name='data_nascimento_socios["+index+"]']").closest('.marcador_data').append('<i class="form-control-feedback glyphicon glyphicon-ok" aria-hidden="true"></i> ');
                            }
                        }
                    }
                },
                error: function(x,y){
                    waitingDialog.hide();
                    return false;
                }
            });
        }
    }
    
    function validaPorcentagemSocios(){
        var total = 0;
        var coeficiente = 0.1;
        var values = $('input[name^="porcentagem_socios"]').map(function(){ return $(this).val();}).get();
        
        var vetor_valores = Object.values(values);
        
        for(i=0; i < vetor_valores.length; i++){
            total += parseFloat(vetor_valores[i].replace(",", "."));
        }
        
        var diferenca = Math.abs(100-total);
        
        if(diferenca >= coeficiente) { return false;}
        return true;
    }
    
    function temSocioDuplicado(){
        var values = $('input[name^="cpf_socios"]').map(function(){ return $(this).val();}).get();
        var vetor_valores = Object.values(values);
        return (new Set(vetor_valores)).size !== vetor_valores.length;
        
    }
    
    function validaCamposSocios(){
        
        var msg_error = '';
        var ind = 0;
        var tem_socio_duplicado = temSocioDuplicado();
        var marcador = true;
        var mensagem_soma_porcentagens = false;
        
        $('.validar_campos_cpf').each(function() {
            
            if($(this).val().trim().length != 14){
                
                marcador = false;

                if($(this).closest('.form-group').hasClass('has-feedback') === false){
                    $(this).closest('.form-group').addClass('has-feedback has-error');
                    $(this).closest('.marcador_cpf').append('<i class="form-control-feedback glyphicon glyphicon-remove" aria-hidden="true"></i> ');

                } else{
                    if($(this).closest('.form-group').hasClass('has-success')){
                        $(this).closest('.form-group').removeClass('has-success');
                        $(this).closest('.form-group').addClass('has-error');

                        $(this).next('.form-control-feedback').removeClass('glyphicon-ok');
                        $(this).next('.form-control-feedback').addClass('glyphicon-remove');
                    }
                }

                if($(this).val().trim() === ""){
                    msg_error += '- Campo CPF do S�cio '+(ind+1)+' n�o pode ficar em branco.\n';

                } else{
                    msg_error += '- Campo CPF do S�cio '+(ind+1)+' inv�lido.\n';
                }
                $(this).val("");
                $("input[name='nome_socios["+ind+"]']").val("");
            } else{

                if(tem_socio_duplicado){

                    if($(this).closest('.form-group').hasClass('has-feedback') === false){
                        $(this).closest('.form-group').addClass('has-feedback has-error');
                        $(this).closest('.marcador_cpf').append('<i class="form-control-feedback glyphicon glyphicon-remove" aria-hidden="true"></i> ');
                    } else{
                        if($(this).closest('.form-group').hasClass('has-success')){
                            $(this).closest('.form-group').removeClass('has-success');
                            $(this).closest('.form-group').addClass('has-error');

                            $(this).next('.form-control-feedback').removeClass('glyphicon-ok');
                            $(this).next('.form-control-feedback').addClass('glyphicon-remove');
                        }
                    }
                }
            }
            ind++;
            
        });
        
        ind = 0;
        
        $('.validar_campos_data').each(function() {
            
            if($(this).val().trim().length != 10){
                
                marcador = false;

                if($(this).closest('.form-group').hasClass('has-feedback') === false){
                    $(this).closest('.form-group').addClass('has-feedback has-error');
                    $(this).closest('.marcador_data').append('<i class="form-control-feedback glyphicon glyphicon-remove" aria-hidden="true"></i> ');

                } else{
                    if($(this).closest('.form-group').hasClass('has-success')){
                        $(this).closest('.form-group').removeClass('has-success');
                        $(this).closest('.form-group').addClass('has-error');

                        $(this).next('.form-control-feedback').removeClass('glyphicon-ok');
                        $(this).next('.form-control-feedback').addClass('glyphicon-remove');
                    }
                }

                if($(this).val().trim() == ""){

                    msg_error += '- Campo Data Nascimento do S�cio '+(ind+1)+' n�o pode ficar em branco.\n';
                } else{
                    msg_error += '- Campo Data Nascimento do S�cio '+(ind+1)+' inv�lido.\n';
                }
                $(this).val("");
                $("input[name='nome_socios["+ind+"]']").val("");

            } else{
            
                if(tem_socio_duplicado){
                    if($(this).closest('.form-group').hasClass('has-feedback') === false){
                        $(this).closest('.form-group').addClass('has-feedback has-error');
                        $(this).closest('.marcador_data').append('<i class="form-control-feedback glyphicon glyphicon-remove" aria-hidden="true"></i> ');
                    } else{
                        if($(this).closest('.form-group').hasClass('has-success')){
                            $(this).closest('.form-group').removeClass('has-success');
                            $(this).closest('.form-group').addClass('has-error');

                            $(this).next('.form-control-feedback').removeClass('glyphicon-ok');
                            $(this).next('.form-control-feedback').addClass('glyphicon-remove');
                        }
                    }
                }
            }
            ind++;
            
        });
        
        var valida_porc = validaPorcentagemSocios();
        ind = 0;
        
        $('.validar_campos_porcentagem').each(function() {
        
            if($(this).val().trim() === ""){
                
                msg_error += '- Campo Porcentagem na Empresa do S�cio '+(ind+1)+' n�o pode ficar em branco.\n';
                
                if($(this).closest('.form-group').hasClass('has-feedback') === false){
                    $(this).closest('.form-group').addClass('has-feedback has-error');
                    $(this).closest('.marcador_porcentagem').append('<i class="form-control-feedback glyphicon glyphicon-remove" aria-hidden="true"></i> ');
                } else{
                    
                    if($(this).closest('.form-group').hasClass('has-success') || $(this).closest('.form-group').hasClass('has-error') === false){
                        $(this).closest('.form-group').removeClass('has-success');
                        $(this).closest('.form-group').addClass('has-error');

                        $(this).next('.form-control-feedback').removeClass('glyphicon-ok');
                        $(this).next('.form-control-feedback').addClass('glyphicon-remove');
                    }
                }
                
            } else{
                
                if(valida_porc === false){
                    
                    mensagem_soma_porcentagens = true;
                    
                    if($(this).closest('.form-group').hasClass('has-feedback') === false){
                        $(this).closest('.form-group').addClass('has-feedback has-error');
                        $(this).closest('.marcador_porcentagem').append('<i class="form-control-feedback glyphicon glyphicon-remove" aria-hidden="true"></i> ');
                    } else{
                        if($(this).closest('.form-group').hasClass('has-success')){
                            $(this).closest('.form-group').removeClass('has-success');
                            $(this).closest('.form-group').addClass('has-error');

                            $(this).next('.form-control-feedback').removeClass('glyphicon-ok');
                            $(this).next('.form-control-feedback').addClass('glyphicon-remove');
                        } else{
                            $(this).closest('.form-group').addClass('has-error');
                            $(this).next('.form-control-feedback').addClass('glyphicon glyphicon-remove');
                        }
                    }
                } else{
                    
                    if($(this).closest('.form-group').hasClass('has-feedback') === false){
                        $(this).closest('.form-group').addClass('has-feedback has-success');
                        $(this).closest('.marcador_porcentagem').append('<i class="form-control-feedback glyphicon glyphicon-ok" aria-hidden="true"></i> ');
                    } else{
                        
                        if($(this).closest('.form-group').hasClass('has-error')){
                            $(this).closest('.form-group').removeClass('has-error');
                            $(this).closest('.form-group').addClass('has-success');
                            
                            $(this).next('.form-control-feedback').removeClass('glyphicon-remove');
                            $(this).next('.form-control-feedback').addClass('glyphicon-ok');
                            
                        }
                    }
                }
            }
            ind++;
        });

        if(mensagem_soma_porcentagens === true){
            msg_error += '- A soma dos campos Porcentagem na Empresa devem totalizar 100%.\n';
            $('.validar_campos_porcentagem').val("");
        }
            
        
        if(tem_socio_duplicado && marcador){
            $('.validar_campos_nome').val("");
            $('.validar_campos_cpf').val("");
            $('.validar_campos_data').val("");
            
            msg_error += '- Problema com dois ou mais s�cios iguais.\n';
        }

        if(msg_error !== ''){
        
            $('#sociosError').find('li[data-field="socios_errors"]').remove();
            
            var mensagens = msg_error.split('\n');
            
            for (var i in mensagens) {
                // Create new 'li' element to show the message
                $('<li/>')
                    .attr('data-field', 'socios_errors')
                    .wrapInner(
                    $('<span/>')
                        .addClass('error-msg-info')
                        .html(mensagens[i])
                        .on('click', function(e) {
                            parent.$.fancybox.close();
                            $.fancybox.close();
                        })
                ).appendTo('.list-errors-socios');
            }
            //removendo o �ltimo da lista (vazio)
            $('li[data-field="socios_errors"]').last().remove();
            
            $('#sociosError').removeClass('hidden').fancybox().trigger('click');
            return false;
        }
        
        return true;
    }
    
    function setCep(cep){
            if (cep.length == 9 && !searching){
                $.ajax({
                    type: "POST",
                    url: "/ajax/cep.php",
                    data: "cep=" + cep,
                    beforeSend: function(){
                        searching = true;
                        waitingDialog.show('Por favor, aguarde...',{dialogSize: 'sm'});
                    },
                    success: function(txt){
                        var fv = $('#profileForm').data('formValidation');
                        searching = false;
                        waitingDialog.hide();

                            if (txt.search("NO_ACCESS") == -1){
                                
                                if (txt.search("ERRO") == -1){
                                    txt = txt.split("&");
                                    
                                    
                                    document.getElementById("tipo_endereco").value = txt[0].trim();
                                    document.getElementById("endereco").value = txt[1].trim();
                                    document.getElementById("bairro").value = txt[2].trim();
                                    document.getElementById("cidade").value = txt[3].trim();
                                    document.getElementById("estado").value = txt[4].trim();
                                    document.getElementById("estado_empresa_display").value = txt[4].trim();

                                    if(txt[1].trim() != "" || txt[2].trim() != ""){
                                        if(txt[1].trim() != "")
                                            $("#endereco").attr("readonly","readonly");

                                        if(txt[2].trim() != "")
                                            $("#bairro").attr("readonly","readonly");

                                        document.getElementById("numero").focus();
                                    }else{
                                            $("#bairro").removeAttr("readonly");
                                            $("#endereco").removeAttr("readonly");
                                            document.getElementById("bairro").focus();
                                    }
                                }
                                else{
                                    document.getElementById("tipo_endereco").value = "";
                                    document.getElementById("endereco").value = "";
                                    document.getElementById("bairro").value = "";
                                    document.getElementById("cidade").value = "";
                                    document.getElementById("estado").value = "";
                                    document.getElementById("estado_empresa_display").value = "";
                                    manipulaModal(1,"CEP Inexistente!","Aten��o");
                                }
                                fv.enableFieldValidators('endereco_empresa', true).revalidateField('endereco_empresa');
                                fv.enableFieldValidators('bairro_empresa', true).revalidateField('bairro_empresa');
                                fv.enableFieldValidators('cidade_empresa', true).revalidateField('cidade_empresa');
                                fv.enableFieldValidators('estado_empresa', true).revalidateField('estado_empresa');
                                fv.enableFieldValidators('numero_empresa', true).revalidateField('numero_empresa');
                            }
                            else{
                                document.getElementById("endereco").value = "";
                                document.getElementById("bairro").value = "";
                                document.getElementById("cidade").value = "";
                                document.getElementById("estado").value = "";                                
                                manipulaModal(1,"<strong>[ERRO 404]</strong> - N�o foi poss�vel consultar o CEP, tente novamente mais tarde.<br>Por favor, relate o problema ao <a href='/support' alt='Suporte' title='Suporte'  target='_blank'>Suporte</a><br>Obrigado!","Consulta de CEP indispon�vel no momento");
                            }

                    },
                    error: function(jqXHR, textStatus){
                        searching = false;
                        $("#info_cep").html("");
                        document.getElementById("tipo_endereco").value = "";
                        document.getElementById("bairro").value = "";
                        document.getElementById("cidade").value = "";
                        document.getElementById("estado").value = "";
                        document.getElementById("estado_empresa_display").value = "";
                        
                        if(textStatus === 'timeout'){     
                            waitingDialog.hide();
                            manipulaModal(1,"<strong>[ERRO 404]</strong> - N�o foi poss�vel consultar o CEP, tente novamente mais tarde.<br>Por favor, relate o problema ao <a href='/support' alt='Suporte' title='Suporte'  target='_blank'>Suporte</a><br>Obrigado!","Erro no servidor");
                        } else{
                            waitingDialog.hide();
                            manipulaModal(1,"<strong>[ERRO 400]</strong> - Erro no servidor.<br>Por favor, relate o problema ao <a href='/support' alt='Suporte' title='Suporte'  target='_blank'>Suporte</a><br>Obrigado!","Consulta de CEP indispon�vel no momento");
                        }
                    },
                    timeout: 30000
                });
                
            } else{
                manipulaModal(1,"CEP Inv�lido!","Aten��o");
                document.getElementById("endereco").value = "";
                document.getElementById("bairro").value = "";
                document.getElementById("cidade").value = "";
                document.getElementById("estado").value = "";
            }
    }
</script>

<script src="/js/validaSenha.js"></script>
<script src="http<?php echo (($_SERVER['HTTPS']=="on")?"s":""); ?>://www.google-analytics.com/urchin.js" type="text/javascript">
</script>
<script type="text/javascript">
_uacct = "UA-1903237-3";
urchinTracker();
</script>
<?php
require_once DIR_WEB . 'creditos/includes/footer.php';
?>