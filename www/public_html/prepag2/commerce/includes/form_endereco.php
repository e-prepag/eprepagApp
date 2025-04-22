<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
ob_clean();
set_time_limit(120);
header("Content-Type: text/html; charset=ISO-8859-1",true);
$usuarioGames = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']);
$usuarioId = $usuarioGames->getId();

if(isset($_POST['btSubmit_EPP_8593'])){
    $btSubmit_EPP_8593 = $_POST['btSubmit_EPP_8593'];
    $iforma = $_POST['iforma'];
    $idu = $_POST['idu'];
    $sno = $_POST['sno'];
    if(isset($_POST['pagto']))
        $pagto = $_POST['pagto'];
    if(isset($_POST['tipo']))
        $tipo = $_POST['tipo'];
    if(isset($_POST['produtos_valor'])){
        $produtos_valor = $_POST['produtos_valor'];
    }
}

if(isset($_POST['formsubmitEnd'])){ 
    if( isset($_POST['skip']) ){
        $GLOBALS['_SESSION']['skip'] = true;
        header('Location: ' . $GLOBALS['_SERVER']['PHP_SELF']);
    }
 
    ob_clean();
    
    if(!empty($usuarioId)){
        $usuarioGames->setEndereco(trim($GLOBALS['_POST']['endereco']));
        $usuarioGames->setNumero(trim($GLOBALS['_POST']['numero']));
        $usuarioGames->setBairro(trim($GLOBALS['_POST']['bairro']));
        $usuarioGames->setComplemento(trim($GLOBALS['_POST']['complemento']));
        $usuarioGames->setCidade(trim($GLOBALS['_POST']['cidade']));
        $usuarioGames->setEstado(trim($GLOBALS['_POST']['uf']));
        $usuarioGames->setCEP(preg_replace('/[^0-9]/', '', $GLOBALS['_POST']['cep']));
        $erro = array();
        
        if($usuarioGames->ug_sCEP == "" || strlen($usuarioGames->ug_sCEP) != 8){
            $errors[] = "Problema com o campo CEP.\n";
        }
        if($usuarioGames->ug_sEndereco == ""){
            $errors[] = "Problema com o campo Logradouro.\n";
        }
        if($usuarioGames->ug_sNumero == ""){
            $errors[] = "Problema com o campo Numero.\n";
        }
        if($usuarioGames->ug_sBairro == ""){
            $errors[] = "Problema com o campo Bairro.\n";
        }
        if($usuarioGames->ug_sCidade == ""){
            $errors[] = "Problema com o campo Cidade.\n";
        }  
        if($usuarioGames->ug_sEstado == ""){
            $errors[] = "Problema com o campo Estado.\n";
        }  
        
        if(count($errors)==0){
            $atualiza = (new UsuarioGames)->atualizar_dados_endereco($usuarioGames, $erro);

            if($atualiza){
                if($tipo == "integracao_gamer"){
                    $strRedirect = "/prepag2/commerce/finaliza_venda_int.php";
                    redirect($strRedirect);
                } else{
                    header('Location: ' . $GLOBALS['_SERVER']['PHP_SELF']);
                }
                
            }
        }
        
    }
    elseif(empty($usuarioId)){
        $errors[] = "Sua sessão expirou. Por favor, faça login no sistema novamente. Obrigado!";
    }
 
}

?>
<html>
        <head>
        <script type="text/javascript" src="/js/scripts.js"></script>
        <link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
        <link href="/css/creditos.css" rel="stylesheet" type="text/css" />
        <!-- includes js -->
        <script type="text/javascript" src="/js/jquery.js"></script>
        <script type="text/javascript" src="/includes/bootstrap/js/bootstrap.min.js"></script>
        <link href="/js/jqueryui/css/custom-theme/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
        <script type="text/javascript" src="/js/modalwaitingfor.js"></script>
        </head>
        <body class="bg-cinza txt-preto">
<?php echo integracao_layout('css'); ?>
<?php echo modal_includes(); ?>
<?php 
$url = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=="on") ? "s" : "") . '://' . $_SERVER['SERVER_NAME'];
echo '<script src="'.$url.'/js/jquery.mask.min.js"></script>';
?>

<body>

<?php 

$form_cep = isset($_POST['cep']) ? $_POST['cep'] : $usuarioGames->ug_sCEP;
$form_endereco = isset($_POST['endereco']) ? $_POST['endereco'] : $usuarioGames->ug_sEndereco;
$form_numero = isset($_POST['numero']) ? $_POST['numero'] : $usuarioGames->ug_sNumero;
$form_complemento = isset($_POST['complemento']) ? $_POST['complemento'] : $usuarioGames->ug_sComplemento;
$form_bairro = isset($_POST['bairro']) ? $_POST['bairro'] : $usuarioGames->ug_sBairro;
$form_cidade = isset($_POST['cidade']) ? $_POST['cidade'] : $usuarioGames->ug_sCidade;
$form_uf = isset($_POST['uf']) ? $_POST['uf'] : $usuarioGames->ug_sEstado;

if(!isset($_POST['formsubmitEnd'])){
    $form_cep = "";
    $form_endereco = "";
    $form_numero = "";
    $form_complemento = "";
    $form_bairro = "";
    $form_cidade = "";
    $form_uf = "";
}

echo integracao_layout('header'); 

echo integracao_layout('order'); 

if($atualiza === false){
    echo "<div class='txt-vermelho text-center top50'><p>".implode("<br>",$erro)."</p></div>";
    include "rodape.php"; 
    die();
} 

?>
<div class="wrapper txt-preto int-box">
    <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12">
        <h4 class="c1 txt-azul">Por favor, complete os campos abaixo com o seu Endereço <a href="#" class="btn-question glyphicon glyphicon-question-sign txt-vermelho c-pointer t0" data-msg="<h2>O que é isso?</h2>Agora os tipos de pagamento Boleto e Transferência entre contas Bradesco necessitam ser registrados junto ao Banco, e para isso precisamos dos seus dados endereço. Na E-Prepag estas informações serão solicitadas apenas uma vez, e os dados informados ficarão automaticamente armazenados em seu perfil." style="position: relative;"></a></h4>
        <p><i>Esta solicitação será feita apenas uma vez.</i></p>
        <div class="int-form1" style="position: relative;">
        <form action="" id="enderecoForm" method="POST">
            <input type="hidden" name="formsubmitEnd" value="OK" style="display: none;" />
            <input type="hidden" name="btSubmit_EPP_8593" value="<?php echo htmlspecialchars($btSubmit_EPP_8593, ENT_QUOTES, 'UTF-8'); ?>" style="display: none;" />
            <input type="hidden" name="iforma" value="<?php echo htmlspecialchars($iforma, ENT_QUOTES, 'UTF-8'); ?>" style="display: none;" />
            <input type="hidden" name="idu" value="<?php echo htmlspecialchars($idu, ENT_QUOTES, 'UTF-8'); ?>" style="display: none;" />
            <input type="hidden" name="sno" value="<?php echo htmlspecialchars($sno, ENT_QUOTES, 'UTF-8'); ?>" style="display: none;" />
            <input type="hidden" name="tipo" value="<?php echo htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8'); ?>" style="display: none;" />
            <input type="hidden" name="pagto" value="<?php echo htmlspecialchars($pagto, ENT_QUOTES, 'UTF-8'); ?>" style="display: none;" />
            
            <div class="col-md-5">
                <div class="form-group">
                    <input type="text" class="form-control w110" id="cep" name="cep" maxlength="9" value="<?php echo htmlspecialchars($form_cep, ENT_QUOTES, 'UTF-8'); ?>" placeholder="CEP">
                </div>
                <div class="form-group">
                    <input type="text" class="form-control w320" id="endereco" name="endereco" maxlength="500" readonly="" value="<?php echo htmlspecialchars($form_endereco, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Logradouro">
                </div>
                <div class="form-group">
                    <input type="text" class="form-control w110" id="numero" name="numero" maxlength="10" value="<?php echo htmlspecialchars($form_numero, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Número">
                </div>
                <div class="form-group">
                    <input type="text" class="form-control w320" id="complemento" name="complemento" maxlength="500" value="<?php echo htmlspecialchars($form_complemento, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Complemento">
                </div>
                <div class="form-group">
                    <input type="text" class="form-control w320" id="bairro" readonly="" name="bairro" maxlength="500" value="<?php echo htmlspecialchars($form_bairro, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Bairro">
                </div>
                <div class="form-group">
                    <input type="text" class="form-control w320" id="cidade" readonly="" name="cidade" maxlength="500" value="<?php echo htmlspecialchars($form_cidade, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Cidade">
                </div>
                <div class="form-group">
                    <input type="text" class="form-control w110" id="uf" readonly="" name="uf" maxlength="2" value="<?php echo htmlspecialchars($form_uf, ENT_QUOTES, 'UTF-8'); ?>" placeholder="UF">
                </div>
                <div class="form-group">
                    <input type="button" class="grad1 btn btn-sm btn-success pull-left" id="btn_submit" value="Confirmar" />
                </div>
            </div>
        </form>

            
            <?php 
            if(count($errors) > 0){
                foreach($errors as $key => $error){ ?>
                    <script>$(function(){ showMessage('<?php echo str_replace("\n"," ",  $error); ?>'); });</script>
                    <?php break; ?>

          <?php }
            
            } ?>

        </div>
    </div>
</div>

<script>

    $(document).ready(function(){

        var searching = false;

        $("#cep").mask("99999-999");

        $("#cep").blur(function(){
            var cep = $(this).val();

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
                        searching = false;
                        waitingDialog.hide();

                        if (txt.search("NO_ACCESS") == -1){

                            if (txt.search("ERRO") == -1){
                                txt = txt.split("&");

                                    document.getElementById("endereco").value = txt[0].trim()+' '+txt[1].trim();
                                    document.getElementById("bairro").value = txt[2].trim();
                                    document.getElementById("cidade").value = txt[3].trim();
                                    document.getElementById("uf").value = txt[4].trim();                             

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
                                document.getElementById("endereco").value = "";
                                document.getElementById("bairro").value = "";
                                document.getElementById("cidade").value = "";
                                document.getElementById("uf").value = "";
                                alert("CEP Inexistente!");
                                return false;
                            }
                        }
                        else{
                            document.getElementById("endereco").value = "";
                            document.getElementById("bairro").value = "";
                            document.getElementById("cidade").value = "";
                            document.getElementById("uf").value = "";
                            alert("[ERRO 404]- Não foi possível consultar o CEP, tente novamente mais tarde. Por favor, relate o problema ao Suporte. Obrigado!");
                        }

                    },
                    error: function(jqXHR, textStatus){
                        if(textStatus === 'timeout'){     
                            waitingDialog.hide();
                            alert("[ERRO 404] - Não foi possível consultar o CEP, tente novamente mais tarde. Por favor, relate o problema ao Suporte. Obrigado!");
                        } else{
                            waitingDialog.hide();
                            alert("[ERRO 400] - Erro no servidor. Por favor, relate o problema ao Suporte.Obrigado!");
                        }
                    },
                    timeout: 60000
                });
                
            } else{
                alert("CEP Inválido!");
                document.getElementById("endereco").value = "";
                document.getElementById("bairro").value = "";
                document.getElementById("cidade").value = "";
                document.getElementById("estado").value = "";
                return false;
            }
        });               
    });

    $('input#btn_submit').click(function(){

    if($('input#btn_submit').hasClass("grad1") && $('input#btn_submit').val() == "Confirmar"){
        $('input#btn_submit').val("Aguarde...");
        $('input#btn_submit').removeClass("grad1");
        $('input#btn_submit').attr("disabled", "disabled");
    }

    var strError = "";

    if($("#cep").val().trim() == "" || $("#cep").val().length != 9){
        if($("#cep").val() == ""){
            strError += (strError == "") ? "Por favor, preencha o campo CEP." : "<br> Por favor, preencha o campo CEP.";
        }
        if($("#cep").val().length != 9 && $("#cep").val() != ""){
            strError += (strError == "") ? "Campo CEP inválido, por favor digite um CEP válido." : "<br> Campo CEP inválido, por favor digite um CEP válido.";
        }
        $('input#cep').css("border-color", "#EE0000");
    }
    else{
        $('input#cep').css("border-color", "");
    }

    if($("#endereco").val().trim() == ""){
        strError += (strError == "") ? "Por favor, preencha o campo Logradouro." : "<br> Por favor, preencha o campo Logradouro.";
        $('input#endereco').css("border-color", "#EE0000");
    }
    else{
        $('input#endereco').css("border-color", "");
    }

    if($("#numero").val().trim() == ""){
        strError += (strError == "") ? "Por favor, preencha o campo Número." : "<br> Por favor, preencha o campo Número.";
        $('input#numero').css("border-color", "#EE0000");
    } 
    else{
        $('input#numero').css("border-color", "");
    }

    if($("#bairro").val().trim() == ""){
        strError += (strError == "") ? "Por favor, preencha o campo Bairro." : "<br> Por favor, preencha o campo Bairro.";
        $('input#bairro').css("border-color", "#EE0000");
    } 
    else{
        $('input#bairro').css("border-color", "");
    }

    if($("#cidade").val().trim() == ""){
        strError += (strError == "") ? "Por favor, preencha o campo Cidade." : "<br> Por favor, preencha o campo Cidade.";
        $('input#cidade').css("border-color", "#EE0000");
    } 
    else{
        $('input#cidade').css("border-color", "");
    }

    if($("#uf").val().trim() == ""){
        strError += (strError == "") ? "Por favor, preencha o campo UF." : "<br> Por favor, preencha o campo UF.";
        $('input#uf').css("border-color", "#EE0000");
    } 
    else{
        $('input#uf').css("border-color", "");
    }    

    if(strError != ""){
        strError += "<br> Os campos em destaque devem ser preenchidos corretamente.";
        showMessage(strError);
        if($('input#btn_submit').hasClass("grad1") === false && $('input#btn_submit').val() == "Aguarde..."){
            $('input#btn_submit').val("Confirmar");
            $('input#btn_submit').addClass("grad1");
            $('input#btn_submit').removeAttr("disabled");
        }
        $('input#btn_submit').attr();
        return;
    }

    $('form#enderecoForm').submit();

});
    
</script>
</body>
</html>