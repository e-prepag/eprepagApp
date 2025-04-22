<html>
    <head>
        <script type="text/javascript" src="/js/jquery.js"></script>
        <?php
        header("Content-Type: text/html; charset=ISO-8859-1",true);
        require_once "../../../includes/constantes.php";
        require_once DIR_INCS."main.php";
        require_once DIR_INCS."pdv/main.php";
        $https = 'http' . (($_SERVER['HTTPS']=='on') ? 's' : '');
        require_once DIR_INCS."configIP.php";

        $server_url = $https . '://' . (checkIP() ? $_SERVER['SERVER_NAME'] : 'www.e-prepag.com.br');
        session_start();

//        error_reporting(E_ALL); 
//        ini_set("display_errors", 1); 

        function generateRandomCode() {
            $numbersAllowedInCode = false; //	Set to FALSE for a 'Letters Only' Code
            $numberOfLetters = 4;   //	Allow Minimum 20 Pixels Per Letter - See codeBoxWidth Property Above)

            $GLOBALS['_SESSION']['verificationCodeP'] = "";
            $ret = "";
            for ($placebo = 1; $placebo <= $numberOfLetters; $placebo++) {
                if ((rand() > 0.49) || ($numbersAllowedInCode == false)) {
                    $number = 97 + rand(0, 25); //rand(97,122);
                    $char = chr($number);
                    $ret .= $char;
                } else {
                    $number = 48 + rand(0, 10); //rand(48, 57);
                    $char = chr($number);
                    $ret .= $char;
                }
            }
            $GLOBALS['_SESSION']['verificationCodeP'] = $ret;
            $GLOBALS['_SESSION']['palavraCodigoP'] = $ret;

            return $ret;
        }

        function translateCode($scode) {
            $numbersAllowedInCode = false; //	Set to FALSE for a 'Letters Only' Code
            $numberOfLetters = 4;   //	Allow Minimum 20 Pixels Per Letter - See codeBoxWidth Property Above)

            $stmp = "";

            for ($placebo = 0; $placebo < $numberOfLetters; $placebo++) {

                $schar = ord(substr($scode, $placebo, 1)) + $placebo;
                $stmp.= str_pad($schar, 3, '0', STR_PAD_LEFT);
                
            }
            return $stmp;
        }
        
        if(isset($_POST["email"]) && $_POST["email"] != ""){

            if($_POST['verificationCodeP'] == $_SESSION['palavraCodigoP'] && $_POST['verificationCodeP'] != ""){
                $to = "suporte@e-prepag.com.br";
                $subject = "PDV Não Encontrado no Mapa";
                $body_html = "<b>Data</b>: ".date("d/m/Y H:i:s").". <br> ";
                foreach($_POST as $ind => $val){
                    if($val != ""){
                    $body_html .= "<b>".ucfirst($ind) . "</b>: ".nl2br($val)."<br>";
                    }
                }

                echo (enviaEmail($to, null, null, $subject, $body_html, null)) ? 
                          "<span  style='color: #0F64CF;font-family: \"Verdana\", Arial, Serif;'>"
                        . " <p>Vamos verificar a ocorrência.</p>"
                        . " <p>Muito obrigado por seu aviso!</p>"
                        . "</span>" : 
                              "<span  style='color: #0F64CF;font-family: \"Verdana\", Arial, Serif;'>"  
                            . " <p>Erro no envio do e-mail. </p>"
                            . " <p>Por favor tente novamente mais tarde.</p>"
                            . "</span>";
                die;
            
            }
            elseif($_POST['verificationCodeP'] == ""){
                echo '<div class="col-md-12"><span class="col-md-offset-2 col-md-4 text-left txt-vermelho">Preencha o campo de verificação.</span></div>';
            }elseif($_POST['verificationCodeP'] != $_SESSION['palavraCodigoP']){
                echo '<span class="col-md-offset-2 col-md-4 text-left txt-vermelho top10 espacamento">Preencha o campo de verificação corretamente.</span><span class="top10"></span>';
            }
        }
        ?>

        <script type="text/javascript">

            $(function(){
                if(parent.document.form_lanHouses_filtros.estado.value !== ""){
                   document.form_lanHouses_filtros.estado.value = parent.document.form_lanHouses_filtros.estado.value;
                   MostraCidade();
                }
                
            });

            function validaForm() {
                $("#enviarForm").attr("disabled","disabled");
                $("#msg_validacao").html("");
                var erro = false;
                
                $("#msg_validacao").text("");
                
                if($("#estado").val() == ""){
                   $("#msg_validacao").append("<p>Estado deve ser selecionado.</p>");
                   erro = true;
                }
                
                if($("#cidade").val() == ""){
                   $("#msg_validacao").append("<p>Cidade deve ser selecionada.</p>");
                   erro = true;
                }
                
                if($("#bairro").val() == ""){
                   $("#msg_validacao").append("<p>Bairro deve ser selecionado.</p>"); 
                   erro = true;
                }
                
                if (document.form_lanHouses_filtros.nome_do_Ponto_de_Venda.value == ""){
                    $("#msg_validacao").append('<p>O nome do ponto de venda não foi preenchido corretamente.</p>');
                    erro = true;
                }
                
                if (document.form_lanHouses_filtros.email.value == "" || !validaEmail(document.form_lanHouses_filtros.email.value)){
                    $("#msg_validacao").append('<p>O e-mail não foi preenchido corretamente.</p>');
                    erro = true;
                }
                
                if ( document.form_lanHouses_filtros.motivo.value == "" || (document.form_lanHouses_filtros.motivo.value == "outro" && document.form_lanHouses_filtros.outro_motivo.value == "")){
                    $("#msg_validacao").append('<p>O motivo deve ser informado.</p>');
                    erro = true;
                }
                
                if(erro == true){
                    $("#enviarForm").removeAttr("disabled");
                    return false;
                }else{
                    $("#form_lanHouses_filtros").submit();                    
                }
            }

            function validaEmail(email){
                var re = /\S+@\S+\.\S+/;
                return re.test(email);
            }

            function MostraCidade() {
                    if ( document.form_lanHouses_filtros.estado.value != "" ){
                            estado = document.form_lanHouses_filtros.estado.value;
                            if(typeof parent.document.form_lanHouses_filtros.estado != "undefined" && parent.document.form_lanHouses_filtros.estado.value != "")
                                parentEstado = parent.document.form_lanHouses_filtros.estado.value;
                            else
                                parentEstado = "";

                            $.ajax({
                                    type: "POST",
                                    url: "busca-pdv-cidade.php",
                                    data: "estado=" + estado,
                                    beforeSend: function(){
                                            $("#SelCidade").html("Buscando...");
                                    },
                                    success: function(html){
                                        $("#SelCidade").html(html);
                                        if(parentEstado !== "" && estado == parentEstado){
                                            document.form_lanHouses_filtros.cidade.value = parent.document.form_lanHouses_filtros.cidade.value;
                                            MostraBairro();
                                        }else{
                                            document.form_lanHouses_filtros.cidade.value = "";
                                            $("#SelBairro").html('<select name="bairro" class="form-control input-sm" id="bairro" DISABLED><option>Selecione uma Cidade</option></select>');
                                        }    
                                    },
                                    error: function(e){
                                        console.log(e);
                                    $("#SelCidade").html("ERRO");
                                    }
                            });
                    }
            }

            function MostraBairro() {
                if ( document.form_lanHouses_filtros.cidade.value != "" ){
                    estado = document.form_lanHouses_filtros.estado.value;
                    cidade = document.form_lanHouses_filtros.cidade.value;
                    $.ajax({
                            type: "POST",
                            url: "busca-pdv-bairro.php",
                            data: "cidade=" + cidade + "&estado=" + estado,
                            beforeSend: function(){
                                    $("#SelBairro").html('<select class="form-control input-sm" DISABLED><option>Buscando Bairro</option></select>');
                            },
                            success: function(html){
                                    $("#SelBairro").html(html);
                                    if(parent.document.form_lanHouses_filtros.bairro.value !== "" && cidade == parent.document.form_lanHouses_filtros.cidade.value){
                                       document.form_lanHouses_filtros.bairro.value = parent.document.form_lanHouses_filtros.bairro.value;
                                    }
                            },
                            error: function(){
                            $("#SelBairro").html("ERRO");
                            }
                    });
                }
            }

        $(function(){
            $("#motivo").change(function(){
                if($(this).val() == "outro"){
                    $("#outro_motivo").fadeIn("fast");
                }else{
                    $("#outro_motivo").fadeOut("fast");
                }
            })
        });
        
        </script>
        <link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
        <link href="/css/creditos.css" rel="stylesheet" type="text/css" />
        <link href="/css/game.css" rel="stylesheet" type="text/css" />
        <!-- includes js -->
        <script type="text/javascript" src="/js/jquery/jquery.js"></script>
        <script type="text/javascript" src="/includes/bootstrap/js/bootstrap.min.js"></script>
    </head>
    <body style="background-color:#d9edf7 !important; ">
        <form id="form_lanHouses_filtros" name="form_lanHouses_filtros" style="background-color:#d9edf7 !important;" class="txt-preto" method="post">
            <div class="col-md-3">O que aconteceu?</div>
            <div class="col-md-9"> 
                <select class="form-control input-sm" name="motivo" id="motivo">
                    <option value="O Ponto de venda não existe">O Ponto de venda não existe</option>
                    <option value="O endereço está errado">O endereço está errado</option>
                    <option value="O Ponto de venda não tem saldo">O Ponto de venda não tem saldo</option>
                    <option value="Não souberam como vender o crédito">Não souberam como vender o crédito</option>
                    <option value="outro">Outro</option>
                </select>
                <textarea rows="3" id="outro_motivo" name="Outro_Motivo" style="display:none; margin-top: 5px;" placeholder="Qual?"  cols="24"></textarea>
            </div>
            <div class="col-md-3 top10">
                Estado:
            </div>
            <div class="col-md-9">
                <select name="estado" id="estado" class="form-control input-sm" onChange='MostraCidade();'>
                    <option value="">&nbsp;UF&nbsp;</option>
                    <?php
                    // Gera os dados do drop down estado
                    foreach ($SIGLA_ESTADOS as $value) {
                        echo '<option value="' . $value . '"';
                        if (isset($_REQUEST['estado']) && $_REQUEST['estado'] == $value) {
                            echo " SELECTED ";
                        }
                        echo ">" . $value . "</option>\n";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3 top10">
                Cidade:
            </div>
            <div class="col-md-9" id="SelCidade">
            <?php
            if (isset($ResultadoCidade)) {
                echo '<select name="cidade" class="form-control input-sm" id="cidade"  onChange="MostraBairro();">';
                while ($RowCidade = pg_fetch_array($pgResultadoCidade)) {
                    echo '<option value="' . $RowCidade['ug_cidade'] . '"';
                    if ($_REQUEST['cidade'] == $RowCidade['ug_cidade'] && !empty($RowCidade['ug_cidade'])) {
                        echo " SELECTED ";
                    }
                    echo '>' . $RowCidade['ug_cidade'] . '</option>';
                }
                echo '</select>';
            } else {
                ?>
                <select name="bairro" class="form-control input-sm" id="bairro" DISABLED>
                    <option>Selecione um Estado</option>		
                </select>
                <?php
            }
            ?>
            </div>
            <div class="col-md-3 top10 ">
                Bairro:
            </div>
            <div class="col-md-9 " id="SelBairro">
            <?php
            if (isset($ResultadoBairro)) {
                echo '<select name="bairro" class="form-control input-sm" id="bairro">';
                while ($RowBairro = pg_fetch_array($pgResultadoBairro)) {
                    echo '<option value="' . $RowBairro['ug_bairro'] . '"';
                    if ($_REQUEST['bairro'] == $RowBairro['ug_bairro'] && !empty($RowBairro['ug_bairro'])) {
                        echo " SELECTED ";
                    }
                    echo '>' . $RowBairro['ug_bairro'] . '</option>';
                }
                echo '</select>';
            } else {
                ?>
                <select name="bairro" class="form-control" id="bairro" DISABLED>
                    <option>Selecione uma Cidade</option>		
                </select>
                <?php
            }
            ?>
            </div>
            <div class="col-md-12 top10">
                <input type="text" style=width:190px;" placeholder="Nome do ponto de venda" class="form-control input-sm" id="nome_do_Ponto_de_Venda" name="nome_do_Ponto_de_Venda">
            </div>
            <div class="col-md-12 top10">
                <input type="text" style=width:190px;" id="email" name="email" class="form-control input-sm" placeholder="Seu e-mail">
            </div>
            
            
            <div class="col-md-4 top10">
<?php
            $randomcode = (isset($_POST['rc'])) ? $_POST['rc'] : generateRandomCode();
            $randomcode_translated = translateCode($randomcode);
?>
                <img class="img-responsive pull-left" src="/includes/captcha/CaptchaImage.php?uid=<?php echo $randomcode_translated; ?>" title="Verify Code" vspace="2" />
            </div>
            
            <div class="clearfix"></div>
            <div class="col-md-4 top10">
                <input name="verificationCodeP" class="form-control" type="text" id="verificationCodeP" placeholder="Digite o captcha" style="width: 130px;" value="<?php if(isset($_POST['verificationCodeP'])) echo $_POST['verificationCodeP'];?>" size="5" />
            </div>
            <div class="clearfix"></div>
            <div class="col-md-4">
                <a class="pull-left" href="javascript:form_lanHouses_filtros.submit();">Gerar outro código</a>
            </div>
            
            
            <div class="clearfix"></div>
            
            
            <div clas="col-md-12 txt-vermelho top10" id="msg_validacao"></div>
            <div class="col-md-12">
                <a href="#" class="btn top10 btn-success" onclick="validaForm();"><i>Enviar</i></a>
            </div>
    </form>

    </body>
</html>

