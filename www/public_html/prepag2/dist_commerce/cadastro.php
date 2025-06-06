<?php require_once __DIR__ . '/../../../includes/constantes_url.php'; ?>
<?php
header("Location: /cadastro-de-ponto-de-venda.php");
die;

//ini_set('display_errors', 1);
//error_reporting(E_ALL);

require_once dirname(__FILE__) . '/layout/bootstrap.php';

$uf = array("AC" => "Acre", "AL" => "Alagoas", "AM" => "Amazonas", "AP" => "Amapá", "BA" => "Bahia", "CE" => "Ceará", 
            "DF" => "Distrito Federal", "ES" => "Espírito Santo", "GO" => "Goiás", "MA" => "Maranhão", "MG" => "Minas Gerais", 
            "MS" => "Mato Grosso do Sul", "MT" => "Mato Grosso", "PA" => "Pará", "PB" => "Paraíba", "PE" => "Pernambuco", 
            "PI" => "Piaui", "PR" => "Paraná", "RJ" => "Rio de Janeiro", "RN" => "Rio Grande do Norte", "RO" => "Rondônia", 
            "RR" => "Roraima", "RS" => "Rio Grande do Sul", "SC" => "Santa Catarina", "SE" => "Sergipe", "SP" => "São Paulo", "TO" => "Tocantins");

// <link rel="stylesheet" href="js/jquery/plugins/formvalidation/dist/css/formValidation.min.css">
$_css_add = array(
    JS_DIR . 'jquery/plugins/formvalidation/dist/css/formValidation.min.css',
    JS_DIR . 'jquery/plugins/step/jquery.steps.css',
);
//    <script src="js/jquery/plugins/formvalidation/dist/js/formValidation.min.js"></script>
$_js_add = array(
    JS_DIR . 'jquery/plugins/step/jquery.steps.js',
    JS_DIR . 'jquery/plugins/mask/jquery.mask.min.js',
    JS_DIR . 'jquery/plugins/formvalidation/dist/js/formValidation.min.js',
    JS_DIR . 'jquery/plugins/formvalidation/dist/js/framework/bootstrap.min.js',
);
require_once dirname(__FILE__) . '/layout/topo_login.php';
?>
    <style type="text/css">
        /* Adjust the height of section */
        #profileForm .content {min-height: 100px;}

        #profileForm .content > .body {width: 100%;height: auto;position: relative;}

        .preenchimento {color: #0c8224; font-weight: bold;}

        .form-control {display: inline !important;}

        .lista_preenchimento {color: #0c8224;font-style: italic;}
        .lista_preenchimento ul {list-style-type: disc;padding-left: 20px;}

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
        .cpf, .cnpj, .rg, .telefone, .celular, .cep  {padding-left: 5px;width: 190px;}

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
        body {
            font-family: "Verdana", Arial, Serif !important;
        }
        .confirmacao {
            margin-left: 12%;
            width: 820px;
        }
        
        .font-p{
            font-size: 11px;
        }
    </style>
    <div class="principal">
        <div class="conteudo_principal">
            <div class="titulo">
                <h1>Cadastro de Ponto de Venda</h1>
                <br />
                <div id="explicacao" style="width: 490px;">
                    <div class="preenchimento">
                        Tenha um lucro adicional em sua loja de até R$ 2.000 <br />
                        sem investimento inicial!
                    </div>
                    <br />
                    <div class="lista_preenchimento">
                        <ul>
                            <li>Sem taxa de inscrição ou mensalidades</li>
                            <li>Games como League of Legends, Point Blank, Steam e CrossFire</li>
                            <li>Aumente o fluxo de clientes em até 800 pessoas/mês</li>
                            <li>Comissão de até 10%</li>
                        </ul>
                    </div>
                </div>

                <div id="info2">
                    <h3>Não tem CNPJ?</h3>
                    Você pode solicitar o seu cadastro utilizando<br />
                    seu CPF, comprovando ser proprietário de<br />
                    um estabelecimento comercial.<br />
                    <a href="http://blog.e-prepag.com/cadastro-de-ponto-de-venda-utilizando-o-cpf/" target="_blank">Veja as instruções</a>.
                </div>
            </div>
            <p>&nbsp;</p>
            <div class="container">
                <div class="row">
                    <div class="col-lg-9">
                        <form id="profileForm" method="post" class="form-horizontal">
                            <h2>Conta</h2>
                            <section data-step="0">
                                <div class="form-group">
                                    <label class="col-xs-4 control-label" for="username">Login<span class="required"></span></label>
                                    <div class="col-xs-5">
                                        <input type="text" class="form-control" name="username" maxlength="100" id="username" autocomplete="off"/>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-xs-4 control-label" for="email">E-mail<span class="required"></span></label>

                                    <div class="col-xs-5">
                                        <input type="text" class="form-control" name="email" id="email" autocomplete="off"/>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-xs-4 control-label" for="email_confirmacao">Confirmação de E-mail<span class="required"></span></label>

                                    <div class="col-xs-5">
                                        <input type="text" class="form-control" maxlength="100" name="email_confirmacao" id="email_confirmacao" autocomplete="off" onpaste="return false" />
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-xs-4 control-label" for="password">Senha <span class="required"></span></label>

                                    <div class="col-xs-5">
                                        <input type="password" class="form-control novaSenha" minlength="6" maxlength="15" autocomplete="off" name="password" id="password"/>
                                    </div>
                                    <div class="col-xs-5 col-xs-offset-4">
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
                                    <div class="col-xs-5 col-xs-offset-4 text-danger font-p">
                                        *Sua senha deve ter: de 6 a 12 caracteres, letras, números, caracteres especiais (|,!,?,*,$,%, etc)
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="col-xs-4 control-label" for="password_confirmacao">Confirmação de senha<span class="required"></span></label>

                                    <div class="col-xs-5">
                                        <input type="password" class="form-control confirmacaoSenha" name="password_confirmacao" id="password_confirmacao" autocomplete="off" onpaste="return false" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-xs-9 col-xs-offset-3">
                                        <ul id="errors0"></ul>
                                    </div>
                                </div>
                            </section>

                            <h2>Contato</h2>
                            <section data-step="1">

                                <div class="form-group">
                                    <label class="col-xs-2 control-label" for="telefone_contato">Telefone<span class="required"></span></label>
                                    <div class="col-xs-6">
                                        Brasil (+55) <input type="text" name="telefone_contato" id="telefone_contato" class="form-control telefone"
                                                            placeholder="(99) 9999-9999" maxlength="14" size="14" style="width: 230px;" />
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-xs-2 control-label" for="celular_contato">Celular<span class="required"></span></label>
                                    <div class="col-xs-6">
                                        Brasil (+55) <input type="text" name="celular_contato" id="celular_contato" class="form-control celular"
                                                            placeholder="(99) 9999-9999" maxlength="15" style="width: 230px;" />
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-xs-2 control-label" for="skype_contato">Skype</label>
                                    <div class="col-xs-7">
                                        <input type="text" name="skype_contato" id="skype_contato" size="55" class="form-control" />
                                    </div>
                                </div>
                                <br />
                                <div class="titulo"><h1>Representante da empresa</h1></div>
                                <br />
                                <input type="hidden" name="nome_representante" id="nome_representante" maxlength="480" size="55" class="form-control" />
                                <div class="form-group">
                                    <label class="col-xs-4 control-label" for="data_nascimento">Data de nascimento<span
                                            class="required"></span></label>
                                    <div class="col-xs-3">
                                        <input type="text" name="data_nascimento" id="data_nascimento" maxlength="20" size="55" class="form-control data" />
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-xs-4 control-label" for="cpf_representante">CPF<span
                                            class="required"></span></label>
                                    <div class="col-xs-4">
                                        <input type="text" name="cpf_representante" id="cpf_representante" size="15" style="width: 215px;" class="form-control cpf" />
                                    </div>
                                    <div id="dados_rf" class="form_obs col-xs-4"></div>
                                </div>

                                <div class="form-group">
                                    <label class="col-xs-4 control-label" for="rg_representante">RG<span
                                            class="required"></span></label>
                                    <div class="col-xs-4">
                                        <input type="text" name="rg_representante" id="rg_representante" size="15" style="width: 215px;" class="form-control rg" />
                                    </div>
                                </div>
                                <br />
                                <div class="form-group">
                                    <div class="col-xs-9 col-xs-offset-3">
                                        <ul id="errorsa1"></ul>
                                    </div>
                                </div>
                            </section>

                            <h2>Empresa</h2>
                            <section data-step="2">
                                <div class="form-group">
                                    <label class="col-xs-4 control-label" for="fantasia">Nome Fantasia <span
                                            class="required"></span></label>
                                    <div class="col-xs-7">
                                        <input type="text" class="form-control" maxlength="100" id="fantasia" name="fantasia_empresa" style="width: 400px;" required />
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-xs-4 control-label" for="razao_social">Razão Social <span
                                            class="required"></span></label>
                                    <div class="col-xs-7">
                                        <input type="text" class="form-control" id="razao_social" name="razao_social_empresa" style="width: 400px;" required />
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-xs-4 control-label" for="cnpj">CNPJ <span
                                            class="required"></span></label>
                                    <div class="col-xs-4">
                                        <input type="text" id="cnpj" style="width: 210px" name="cnpj_empresa" size="20" class="form-control cnpj" required /><br />
                                    </div>
                                    <div class="col-xs-4">
                                        <span class="form_obs" style="margin-left:  -30px !important;">
                                            (Sem pontos barras ou espaços. Não tem CNPJ? <a href="<?= EPREPAG_URL_HTTPS_COM ?>/solicita-o-de-cadastro-de-ponto-de-venda-por-cpf" target="_blank">Clique aqui</a>.)
                                        </span>
                                    </div>

                                </div>

                                <div class="form-group">
                                    <label class="col-xs-4 control-label" for="tipo_estabelecimento">Tipo do estabelecimento <span
                                            class="required"></span></label>
                                    <div class="col-xs-5">
                                        <select name="tipo_estabelecimento_empresa" id="tipo_estabelecimento" style="width: 245px !important;  padding-right: 0px !important;" class="form_control select_input" required>
                                            <option value=""> Selecione </option>
                                            <option value="1"> Lan House </option>
                                            <option value="3"> Loja de Games </option>
                                            <option value="2"> Loja de Informática e afins </option>
                                            <option value="Outros"> Outros </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group hidden" id="outro_estabelecimento_div">
                                    <label class="col-xs-4 control-label" for="outro_estabelecimento">Outros <span
                                            class="required"></span></label>
                                    <div class="col-xs-7">
                                        <input type="text" id="outro_estabelecimento" name="outro_estabelecimento" size="9" class="form-control" required /><span class="form_obs">(Sem hífen ou espaços)</span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-xs-4 control-label" for="faturamento_medio">Faturamento médio mensal <span
                                            class="required"></span></label>
                                    <div class="col-xs-5">
                                        <select name="faturamento_medio" id="faturamento_medio" style="width: 245px !important; padding-right: 0px !important; " class="form-control select_input" required>
                                            <option value=""> Selecione </option>
                                            <option value="1">Menor que R$ 5.000,00</option>
                                            <option value="2">R$ 5.000,01 - R$ 10.000,00</option>
                                            <option value="3">R$ 10.000,01 - R$ 20.000,00</option>
                                            <option value="4">Acima de R$ 20.000,00</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-xs-4 control-label" for="cep">CEP <span
                                            class="required"></span></label>
                                    <div id="info_cep" class="form_obs"></div>

                                    <div class="col-xs-7">
                                        <input type="text" id="cep" name="cep_empresa" size="9" class="form-control cep" maxlength="9" autocomplete="off" required /><span class="form_obs">(Sem hífen ou espaços)</span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-xs-4 control-label" for="estado">Estado <span
                                            class="required"></span></label>
                                    <div class="col-xs-4">
                                        <input type="hidden" name="estado_empresa" id="estado">
                                        <select id="estado_empresa_display" disabled="disabled" name="estado_empresa_display" style="width: 190px" class=" form-control select_input" required>
                                            <option value=""></option>
                                            <?php foreach($uf as $ind => $val){ ?>
                                            <option value="<?php echo $ind; ?>"><?php echo $val; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-xs-4 control-label" for="cidade">Cidade <span
                                            class="required"></span></label>
                                    <div class="col-xs-7">
                                        <input type="text" id="cidade" readonly name="cidade_empresa" maxlength="100" class="form-control" size="35" required />
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-xs-4 control-label" for="bairro">Bairro <span
                                            class="required"></span></label>
                                    <div class="col-xs-7">
                                        <input type="text" id="bairro" name="bairro_empresa" maxlength="100" class="form-control" size="35" required />
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="col-xs-4 control-label" for="endereco">Endereço <span
                                            class="required"></span></label>
                                    <div class="col-xs-7">
                                        <input type="text" id="endereco" class="form-control" maxlength="2040" name="endereco_empresa" size="55" required />
                                    </div>
                                    <input type="hidden" id="tipo_endereco" class="form-control" name="tipo_endereco" size="55" />
                                </div>
                                                                
                                <div class="form-group">
                                    <label class="col-xs-4 control-label" for="numero">Número <span
                                            class="required"></span></label>
                                    <div class="col-xs-7">
                                        <input type="text" id="numero" class="form-control" maxlength="10" name="numero_empresa" size="45" required />
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-xs-4 control-label" for="complemento">Complemento</label>
                                    <div class="col-xs-7">
                                        <input type="text" id="complemento" class="form-control" maxlength="100" name="complemento_empresa" size="55" />
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-xs-4 control-label" for="site">Site</label>
                                    <div class="col-xs-7">
                                        <input type="text" id="site" class="form-control" name="site_empresa" size="55" />
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-xs-4 control-label" for="inscricao_estadual">Inscrição Estadual</label>
                                    <div class="col-xs-7">
                                        <input type="text" class="form-control" id="inscricao_estadual" name="inscricao_estadual_empresa" size="55" />
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-xs-9 col-xs-offset-3">
                                        <ul id="errors2"></ul>
                                    </div>
                                </div>
                            </section>
                            <h2>Confirmação</h2>
                            <section data-step="3">
                                <div style="margin-left: 8%;">
                                    <?php echo '<textarea class="contrato" cols="80" rows="12" readonly style="font-size: 13px">' .file_get_contents('layout/contrato.php'). '</textarea>';?>
                                    <div class="titulo_cinza form-group" style="font-size: 17px">
                                        <label class="control-label" style="margin-left: 15px;">Termos de Uso <span class="required"></span></label>
                                    <span class="texto_cinza">
                                        <input type="checkbox" name="termos" id="termos" class="step2" />
                                        <label for="termos" style="font-size: 14px">Eu concordo com os Termos de Uso do sistema E-Prepag</label>
                                    </span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-xs-9 col-xs-offset-3">
                                        <ul id="errors3"></ul>
                                    </div>
                                </div>
                                
                            </section>
                            <div class="col-lg-2 hidden col-md-offset-5 ajax-load" style="position: absolute;float: right;padding-left: 20px;margin-top: 20px;">
                                    <img src="/images/ajax-loader.gif">
                                </div>

                        </form>
                        <div class="confirmacao hidden" id="confirmation">
                            <div class="titulo">
                                <div class="col-md-3">
                                    <img src="<?php echo IMG_LAN_URL;?>confirmation.png" style="  width: 125px;margin-top: 5px;">
                                </div>
                                <div class="col-md-6" style="padding-bottom: 40px">
                                    <h1>Formulário de cadastro completo</h1>
                                    <span class="texto_cinza">
                                        Agora nossa equipe de negócios irá verificar seus dados.<br />
                                        Este processo de análise leva até 3 dias úteis. Se for aprovado você receberá um e-mail com as<br />
                                        instruções do serviço.<br />
                                        <br />
                                        Caso tenha alguma dúvida por favor contacte nosso <a href="<?php echo $https;?>://<?= EPREPAG_URL_COM ?>/support" target="_blank">suporte</a>.
                                    </span>
                                </div>
                            </div>
                        </div>
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
                </div>
            </div>

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
                
        $(".form-control")
                .attr("autofocus","true")
                .keydown(function(e){
                    var tecla = e.with || e.keyCode;
                });
           // http://formvalidation.io/examples/adding-dynamic-field/
            // IMPORTANT: You must call .steps() before calling .()
            $('#profileForm')
                .steps({
                    headerTag: 'h2',
                    bodyTag: 'section',
                    transitionEffect: "slideLeft",
                    startIndex: 3,
                    container: 'tooltip',
                    /* Labels */
                    labels: {
                        finish: "Finalizar Cadastro",
                        next: "Próximo",
                        previous: "Anterior",
                        loading: "Carregando ..."
                    },
                    onInit: function (event, currentIndex) {
                        $('.cnpj').mask('99.999.999/9999-99', {placeholder: '__.___.___/____-__'});
                        $('.cpf').mask('999.999.999-99', {placeholder: '___.___.___-__'});
                        //$('.rg').mask('##.###.###-?');
                        $('.cep').mask('99999-999', {placeholder: '_____-___'});

                        $('.telefone').mask('(99) 9999-9999');
                        $('.celular').mask('(00) 90000-0000');
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
                            // Perform an ajax request to validate if the username and e-mail has bem already taken
                            $.get('/prepag2/dist_commerce/layout/ajaxEmail.php', {type:'email',email: $('#email').val(), username: $("#username").val()}, function(data){
                            });
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
                            url: "ajaxRegistraUsuario.php",
                            data: $("#profileForm").serialize(),
                            beforeSend: function(){
                                $("#profileForm-t-0").addClass("hidden");
                                $("#profileForm-t-1").addClass("hidden");
                                $("#profileForm-t-2").addClass("hidden");
                                $("#profileForm-t-3").addClass("hidden");
                                $(".actions").addClass("hidden");
                                $('#profileForm').find('section[data-step="3"]').addClass("hidden");
                                $(".ajax-load").removeClass("hidden");
                            },
                            success: function(txt){
                                console.log(txt);
                                txt = txt.trim();
                                if(!txt){
                                   window.location="/prepag2/dist_commerce/cadastro_finalizado.php"; 
                                }else{
                                    if(txt.indexOf("Email") !== -1){
                                        if(txt.indexOf("cadastrado") !== -1){
                                            txt = '<ul class="list-errors"><li data-field="email"><span class="error-msg-info">E-mail já cadastrado. Escolha outro.</span></li></ul>';
                                        }else if(txt.indexOf("preenchido")){
                                            txt = '<ul class="list-errors"><li data-field="email"><span class="error-msg-info">E-mail não pode ficar em branco</span></li></ul></div>'
                                        }    
                                        $("#email").val("");
                                        $("#email_confirmacao").val("");
                                        $("#profileForm-t-0").trigger("click");
                                    }else if(txt.indexOf("Login") !== -1){
                                        if(txt.indexOf("cadastrado") !== -1){
                                            txt = '<ul class="list-errors"><li data-field="username"><span class="error-msg-info">Login já cadastrado. Escolha outro.</span></li></ul>';
                                        }else if(txt.indexOf("preenchido")){
                                            txt = '<ul class="list-errors"><li data-field="username"><span class="error-msg-info">Login não pode ficar em branco</span></li></ul></div>'
                                        }    
                                        $("#username").val("");
                                        $("#profileForm-t-0").trigger("click");
                                    }else if(txt.indexOf("CNPJ") !== -1){
                                        if(txt.indexOf("cadastrado") !== -1){
                                            txt = '<ul class="list-errors"><li data-field="cnpj"><span class="error-msg-info">CNPJ já cadastrado. Escolha outro.</span></li></ul>';
                                        }else if(txt.indexOf("preenchido")){
                                            txt = '<ul class="list-errors"><li data-field="cnpj"><span class="error-msg-info">CNPJ não pode ficar em branco</span></li></ul></div>'
                                        }    
                                        $("#cnpj").val("");
                                        $("#profileForm-t-2").trigger("click");
                                    }
                                    


                                    $("#profileForm-t-0").removeClass("hidden");
                                    $("#profileForm-t-1").removeClass("hidden");
                                    $("#profileForm-t-2").removeClass("hidden");
                                    $("#profileForm-t-3").removeClass("hidden");
                                    $(".actions").removeClass("hidden");
                                    $('#profileForm').find('section[data-step="3"]').removeClass("hidden");
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
                        username: {
                            threshold: 6,
                            trigger: 'blur',
                            validators: {
                                stringLength: {min:6,max:100,message: 'O nome de usuário deve ter mais que 6 e menos que 100 caracteres de tamanho'},
                                remote: {
                                    message:'Nome de usuário indisponível. Escolha outro.',
                                    url: '/prepag2/dist_commerce/layout/ajaxCadastro.php',
                                    type: 'POST',
                                    data: {field: 'username'}
                                },
                                notEmpty: {message: 'Nome de usuário não pode ficar em branco.'},
                                regexp: {  
                                    regexp: /^[a-zA-Z0-9_\.]+$/,
                                    message: 'O nome de usuário deve ser somente alfanumérico, pontos "." e underline "_"'
                                }
                                
                            }
                        }, 
                        email: {
                            threshold: 6,
                            trigger: 'blur',
                            validators: {
                                stringLength: {min:5, max: 100, message: 'O e-mail deve ter o mínimo de 5 caracteres'},
                                remote: {
                                    message:'E-mail já cadastrado. Escolha outro.',
                                    url: '/prepag2/dist_commerce/layout/ajaxCadastro.php',
                                    type: 'POST',
                                    data: {field: 'email'}
                                },
                                notEmpty: {message: 'E-mail não pode ficar em branco'},
                                //emailAddress: {message: 'O e-mail digitado não é válido'},
                                regexp: {
                                    regexp: '^[^@\\s]+@([^@\\s]+\\.)+[^@\\s]+$',
                                    message: 'O e-mail digitado não é válido'
                                }
                            }
                        },
                        email_confirmacao: {
                            validators: {
                                stringLength: {min:5, message: 'O e-mail deve ter o mínimo de 5 caracteres'},
                                notEmpty: {message: "E-mail de confirmação não pode ficar em branco"},
                                identical: {field: 'email',message: 'O e-mail de confirmação é diferente do e-mail original'}
                            }
                        },
                        password: {
                            validators: {
                                stringLength: {min:6, max:15, message: 'A senha deve ter entre 6 e 15 caracteres'},
                                notEmpty: {message: 'Campo senha não pode ficar em branco'},
                                different: {field: 'username',message: 'A senha não pode ser igual ao nome de usuário'}
                            }
                        },
                        password_confirmacao: {
                            validators: {
                                stringLength: {min:6, max:15, message: 'A senha deve ter entre 6 e 15 caracteres'},
                                notEmpty: {message: 'Campo confirmação de senha não pode ficar em branco'},
                                identical: {
                                    field: 'password',
                                    message: 'Confirmação de senha não pode ser diferente da senha original'
                                },
                                callback: {
                                    message: 'Senha inválida',
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
                                notEmpty: {message: 'Campo telefone não pode ficar em branco'},
                                regexp: {
                                    regexp: /\(\d{2}\)\s\d{4}-\d{4}$/,
                                    'message': 'Número de telefone inválido'
                                }
                            }
                        },
                        celular_contato: {
                            validators: {
                                notEmpty: {message: 'Campo celular não pode ficar em branco'},
                                regexp: {
                                    regexp: /\(\d{2}\)\s\d{4,5}-\d{3,4}$/,
                                    'message': 'Número de celular inválido'
                                }
                            }
                        },
                        data_nascimento: {
                            validators: {
                                stringLength: {min:10, max: 10, message: 'MSG DATA NASCIMENTO.'},
                                notEmpty: {message: 'MSG DATA NASCIMENTO'}
                            }
                        },
                        cpf_representante: {
                            threshold: 12,
                            trigger: 'blur',
                            validators: {
                                notEmpty: {
                                    message: 'Campo CPF não pode ficar em branco'
                                },
                                id: {
                                    country: 'BR',
                                    message: 'CPF inválido'
                                }
    //                                ,
    //                                remote: {
    //                                    message:'CPF já cadastrado.',
    //                                    url: '/prepag2/dist_commerce/layout/ajaxCadastro.php',
    //                                    type: 'POST',
    //                                    data: {field: 'cpf_representante'}
    //                                }
                                }
                        },
                        rg_representante: {
                            validators: {
                                stringLength: {min:4,message: 'O RG deve ter no mínimo 4 digitos.'},
                                notEmpty: {
                                    message: 'Campo RG não pode ficar em branco'
                                }
                            }
                        },
                        //Fim Contato
                        // Empresa
                        fantasia_empresa: {
                            validators: {
                                stringLength: {min:5, max:100, message: 'Nome fantasia deve ter o mínimo de 5 caracteres'},
                                notEmpty: {
                                    message: 'Campo "Nome Fantasia" não pode ficar em branco'
                                }
                            }
                        },

                        razao_social_empresa: {
                            validators: {
                                stringLength: {min:5, message: 'Razão social deve ter o mínimo de 5 caracteres'},
                                notEmpty: {
                                    message: 'Campo "Razão Social" não pode ficar em branco'
                                }
                            }
                        },

                        cnpj_empresa: {
                            threshold: 16,
                            trigger: 'blur',
                            validators: {
                                notEmpty: {
                                    message: 'Campo "CNJP" não pode ficar em branco'
                                },
                                vat: {
                                    country: 'BR',
                                    message: 'CNPJ inválido'
                                },
                                remote: {
                                    message:'CNPJ já cadastrado.',
                                    url: '/prepag2/dist_commerce/layout/ajaxCadastro.php',
                                    type: 'POST',
                                   data: {field: 'cnpj_empresa'}
                                }
                            }
                        },

                        tipo_estabelecimento_empresa: {
                            validators: {
                                notEmpty: {
                                    message: 'Campo "Tipo de Estabelecimento" não pode ficar em branco'
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

                        faturamento_medio: {
                            validators: {
                                notEmpty: {
                                    message: 'Campo "Faturamento Médio" não pode ficar em branco'
                                }
                            }
                        },

                        cep_empresa: {
                            validators: {
                                stringLength: {min:8, message: 'CEP inválido'},
                                notEmpty: {
                                    message: 'Campo "CEP" não pode ficar em branco'
                                }
                            }
                        },

                        estado_empresa: {
                            validators: {
                                notEmpty: {
                                    message: 'Escolha o Estado'
                                }
                            }
                        },

                        cidade_empresa: {
                            validators: {
                                stringLength: {max:100, message: 'Cidade deve ter no máximo 100 caracteres'},
                                notEmpty: {
                                    message: 'Campo "Cidade" não pode ficar em branco'
                                }
                            }
                        },

                        bairro_empresa: {
                            validators: {
                                stringLength: {max:100, message: 'Bairro deve ter no máximo 100 caracteres'},
                                notEmpty: {
                                    message: 'Campo "Bairro" não pode ficar em branco'
                                }
                            }
                        },

                        endereco_empresa: {
                            validators: {
                                stringLength: {max:100, message: 'Endereço deve ter no máximo 100 caracteres'},
                                notEmpty: {
                                    message: 'Campo "Endereço" não pode ficar em branco'
                                }
                            }
                        },

                        numero_empresa: {
                            validators: {
                                stringLength: {max:10, message: 'Número deve ter no máximo 10 caracteres'},
                                notEmpty: {
                                    message: 'Campo "Número" não pode ficar em branco'
                                },
                                numeric: {
                                    message: 'Digite um número'
                                }
                            }
                        },
                        /// Fim Empresa

                        // Confirmação
                        termos: {
                            validators: {
                                notEmpty: {
                                    message: 'É necessário concordar com os termos'
                                }
                            }
                        }
                        // Fim Confirmação
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
                                    $('div[class="error-msg"]').fancybox().close();
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
        $('#tipo_estabelecimento').change(function(){
            var fv = $('#profileForm').data('formValidation');
            if ( $(this).val() === 'Outros' ) {
                fv.enableFieldValidators('outro_estabelecimento', true).revalidateField('outro_estabelecimento');
                $('#outro_estabelecimento_div').removeClass('hidden');
            } else {
                $('#outro_estabelecimento_div').addClass('hidden');
                fv.enableFieldValidators('outro_estabelecimento', false).revalidateField('outro_estabelecimento');
            }
        });
            
        $("#password_confirmacao").blur(function(){
            $('#profileForm').formValidation('revalidateField', 'password_confirmacao');
        });
        
        $("#password").blur(function(){
            $('#profileForm').formValidation('revalidateField', 'password_confirmacao');
        });
        
        

        //Função para buscar o endereço.
        $("#cep").blur(function(){
            setCep($(this).val());
        });
        
        
        
        $("#cpf_representante").keyup(function(){
            pegaNomeRF();
        }).keydown(function(){
            pegaNomeRF();
        }).blur(function(){
            pegaNomeRF();
        });
        
        $("#data_nascimento").keyup(function(){
            pegaNomeRF();
        }).keydown(function(){
            pegaNomeRF();
        }).blur(function(){
            pegaNomeRF();
        });

    });
    var searching = false;
        //funcao que busca o nome do representante a partir do cpf e da data de nascimento
    function pegaNomeRF(){
        
        if($("#cpf_representante").val().length == 14 && $("#data_nascimento").val().length == 10 && !searching)
        {
            $.ajax({
                type: "POST",
                url: "/prepag2/consulta_cpf/ajaxCpf.php",
                dataType : "json",
                data: { cpf : $("#cpf_representante").val(), dataNascimento : $("#data_nascimento").val()},
                beforeSend: function(){
                    searching = true;
                    $(".actions").addClass("hidden");
                    $("#dados_rf").html("Aguarde... Carregando informações.");
                },success: function(txt){
                    searching = false;
                    $(".actions").removeClass("hidden");
                    $("#dados_rf").html("");
                    var fv = $('#profileForm').data('formValidation');
                    if(txt.erros.length > 0)
                    {
                        $("#cpf_representante").val("");
                        $("#data_nascimento").val("");
                        fv.enableFieldValidators('cpf_representante', true).revalidateField('cpf_representante');
                        fv.enableFieldValidators('data_nascimento', true).revalidateField('data_nascimento');
                        alert(txt.erros);
                    }
                    else
                    {
                        $("#nome_representante").val(txt.nome.substr(0, 480)); //20 pq insere em mais campos e tem um limite de 20 caracteres que se o nome for maior, da problema
                    }
                },
                error: function(x,y){
                    return false;
                }
            });
        }
    }
    
    function setCep(cep){
            if (cep.length == 9 && !searching){
                $.ajax({
                    type: "POST",
                    url: "conta/cep.php",
                    data: "cep=" + cep,
                    beforeSend: function(){
                        searching = true;
                        $("#info_cep").html("Aguarde... Procurando CEP.");
                    },
                    success: function(txt){
                        var fv = $('#profileForm').data('formValidation');
                        searching = false;
                        $("#info_cep").html("");
                        
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
                                    alert("CEP Inexistente");
                                    return false;
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
                                alert("[ERRO 404] - Consulta de CEP indisponível no momento. Tente novamente mais tarde.");
                                return false;
                            }

                    },
                    error: function(){
                        searching = false;
                        $("#info_cep").html("");
                        document.getElementById("tipo_endereco").value = "";
                        document.getElementById("bairro").value = "";
                        document.getElementById("cidade").value = "";
                        document.getElementById("estado").value = "";
                        document.getElementById("estado_empresa_display").value = "";
                        
                        if(textStatus === 'timeout'){
                            alert("[ERRO 400] - Não foi possível consultar o CEP, tente novamente mais tarde.");
                        } else{
                            alert("Erro no servidor, tente novamente mais tarde.");
                        }
                        
                    },
                    timeout:60000
                });
                
            } else{
                alert("CEP Inválido!");
                return false;
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
require_once dirname(__FILE__) . '/layout/footer.php';
?>