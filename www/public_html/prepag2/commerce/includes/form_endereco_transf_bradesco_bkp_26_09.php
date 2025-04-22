<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

set_time_limit(120);

require_once RAIZ_DO_PROJETO . "/includes/functions.php";

header("Content-Type: text/html; charset=ISO-8859-1",true);

$usuarioGames = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']);
$usuarioId = $usuarioGames->getId();

if(isset($_POST['btSubmit'])){
    $btSubmit = $_POST['btSubmit'];
    $iforma = $_POST['iforma'];
    $idu = $_POST['idu'];
    $sno = $_POST['sno'];
    $pagto = $_POST['pagto'];
    $prod_camp = $_POST['prod_camp'];
    $tipo = $_POST['tipo'];
    if(isset($_POST['produtos_valor'])){
        $produtos_valor = $_POST['produtos_valor'];
    }
}

if(isset($_POST['formsubmitEnd'])){
    
    if(!empty($usuarioId)){
        $usuarioGames->setEndereco(trim($GLOBALS['_POST']['endereco']));
        $usuarioGames->setNumero(trim($GLOBALS['_POST']['numero']));
        $usuarioGames->setBairro(trim($GLOBALS['_POST']['bairro']));
        $usuarioGames->setComplemento(trim($GLOBALS['_POST']['complemento']));
        $usuarioGames->setCidade(trim($GLOBALS['_POST']['cidade']));
        $usuarioGames->setEstado(trim($GLOBALS['_POST']['uf']));
        $usuarioGames->setCEP(preg_replace('/[^0-9]/', '', $GLOBALS['_POST']['cep']));
        $erro = array();

        if($usuarioGames->getCEP() == "" || strlen($usuarioGames->getCEP()) != 8){
            $errors[] = "Problema com o campo CEP.\n";
        }
        if($usuarioGames->getEndereco() == ""){
            $errors[] = "Problema com o campo Logradouro.\n";
        }
        if($usuarioGames->getNumero() == ""){
            $errors[] = "Problema com o campo Numero.\n";
        }
        if($usuarioGames->getBairro() == ""){
            $errors[] = "Problema com o campo Bairro.\n";
        }
        if($usuarioGames->getCidade() == ""){
            $errors[] = "Problema com o campo Cidade.\n";
        }  
        if($usuarioGames->getEstado() == ""){
            $errors[] = "Problema com o campo Estado.\n";
        }  

        if(count($errors)==0){
            $atualiza = (new UsuarioGames)->atualizar_dados_endereco($usuarioGames, $erro);

            if($atualiza){
                if($tipo == "venda_gamer"){
                    $strRedirect = "/game/pagamento/finaliza_venda.php";
                    redirect($strRedirect);
                } elseif($tipo == "adicao_gamer"){
                    $strRedirect = "/game/pagamento/finaliza_deposito.php";
                    redirect($strRedirect);
                }

            } else{
                
                if(is_array($erro) && count($erro) > 0){
                    foreach ($erro as $msg_erro){
                        $msg .= $msg_erro;
                    }
                } else{
                    $msg = "ERRO 2030: Problema ao atualizar seus dados de Endereço! Por favor, relate o problema ao suporte!";
                }
?>
                <form name="problem" id="problem" method="POST" action="/prepag2/commerce/mensagem.php">
                    <input type='hidden' name='msg' id='msg' value='<?php echo $msg; ?>'>
                    <input type='hidden' name='titulo' id='titulo' value='Problema Atualização dos Dados de Endereço'>
                    <input type='hidden' name='link' id='link' value='/game/suporte.php'>
                </form>
                <script language='javascript'>
                    document.getElementById("problem").submit();
                </script>
<?php
                exit;
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
        <body class="bg-cinza">      
<?php 
if($is_int){
    echo integracao_layout('css');
}
echo modal_includes();
$url = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=="on") ? "s" : "") . '://' . $_SERVER['SERVER_NAME'];
echo '<script src="'.$url.'/js/jquery.mask.min.js"></script>';
?>

<body>

<?php 

$form_cep = isset($_POST['cep']) ? $_POST['cep'] : $usuarioGames->getCEP();
$form_endereco = isset($_POST['endereco']) ? $_POST['endereco'] : $usuarioGames->getEndereco();
$form_numero = isset($_POST['numero']) ? $_POST['numero'] : $usuarioGames->getNumero();
$form_complemento = isset($_POST['complemento']) ? $_POST['complemento'] : $usuarioGames->getComplemento();
$form_bairro = isset($_POST['bairro']) ? $_POST['bairro'] : $usuarioGames->getBairro();
$form_cidade = isset($_POST['cidade']) ? $_POST['cidade'] : $usuarioGames->getCidade();
$form_uf = isset($_POST['uf']) ? $_POST['uf'] : $usuarioGames->getEstado();

$readonly_cidade = (trim($usuarioGames->getCidade()))?"readonly='readonly'":"";
$readonly_estado = (trim($usuarioGames->getEstado()))?"readonly='readonly'":"";
$readonly_bairro = (trim($usuarioGames->getBairro()))?"readonly='readonly'":"";
$readonly_logradouro = (trim($usuarioGames->getEndereco()))?"readonly='readonly'":"";

if($is_int){
    echo integracao_layout('header'); 

    echo integracao_layout('order'); 
}

if($atualiza === false){
    echo "<div class='txt-vermelho text-center top50'><p>".implode("<br>",$erro)."</p></div>";
    include "rodape.php"; 
    die();
} 

?>
<div class="wrapper txt-preto int-box">
    <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12">
        <h4 class="c1 txt-azul">Por favor, complete os campos abaixo com o seu Endereço Completo <a href="#" class="btn-question glyphicon glyphicon-question-sign txt-vermelho c-pointer t0" data-msg="<h2>O que é isso?</h2>Agora todo pagamento via Transferência entre contas Bradesco necessita de seus dados de endereço. Na E-Prepag estas informações serão solicitadas apenas uma vez, e os dados informados ficarão automaticamente armazenados em seu perfil." style="position: relative;"></a></h4>
        <p><i>Esta solicitação será feita apenas uma vez.</i></p>
        <div class="int-form1" style="position: relative;">
            <form action="<?php echo $_SERVER['PHP_SELF'];?>" id="enderecoForm" method="POST">
                <input type="hidden" name="formsubmitEnd" value="OK" style="display: none;" />
                <input type="hidden" name="btSubmit" value="<?php echo $btSubmit;?>" style="display: none;" />
                <input type="hidden" name="tipo" value="<?php echo $tipo;?>" style="display: none;" />
                <input type="hidden" name="produtos_valor" value="<?php echo $produtos_valor;?>" style="display: none;" />
                <input type="hidden" name="iforma" value="<?php echo $iforma;?>" style="display: none;" />
                <input type="hidden" name="idu" value="<?php echo $idu;?>" style="display: none;" />
                <input type="hidden" name="sno" value="<?php echo $sno;?>" style="display: none;" />
                <input type="hidden" name="prod_camp" value="<?php echo $prod_camp;?>" style="display: none;" />
                <input type="hidden" name="pagto" value="<?php echo $pagto;?>" style="display: none;" />
                <div class="col-md-5">
                    <div class="form-group">
                                <input type="text" class="form-control w110" id="cep" name="cep" maxlength="9" value="<?php echo $form_cep; ?>" placeholder="CEP">
                    </div>
                    <div class="form-group">
                                <input type="text" class="form-control w320" id="endereco" <?php echo $readonly_logradouro;?> name="endereco" maxlength="500" value="<?php echo $form_endereco; ?>" placeholder="Logradouro">
                            </div>
                            <div class="form-group">
                                <input type="text" class="form-control w110" id="numero" name="numero" maxlength="10" value="<?php echo $form_numero; ?>" placeholder="Número">
                            </div>
                            <div class="form-group">
                                <input type="text" class="form-control w320" id="complemento" name="complemento" maxlength="500" value="<?php $form_complemento; ?>" placeholder="Complemento (opcional)">
                            </div>
                            <div class="form-group">
                                <input type="text" class="form-control w320" id="bairro" <?php echo $readonly_bairro;?> name="bairro" maxlength="500" value="<?php echo $form_bairro; ?>" placeholder="Bairro">
                            </div>
                            <div class="form-group">
                                <input type="text" class="form-control w320" id="cidade" <?php echo $readonly_cidade;?> name="cidade" maxlength="500" value="<?php echo $form_cidade; ?>" placeholder="Cidade">
                            </div>
                            <div class="form-group">
                                <input type="text" class="form-control w110" id="uf" <?php echo $readonly_estado;?> name="uf" maxlength="2" value="<?php echo $form_uf; ?>" placeholder="UF">
                            </div>
                    <div class="form-group">
                        
                        <input type="button" class="grad1 btn btn-sm btn-success pull-left" id="btn_submit" value="Confirmar" />
                    </div>
                </div>
            </form>
            
            <?php 
            if(count($errors) > 0){
                foreach($errors as $key => $error){
                    $er .= str_replace("\n"," ",  $error);
                }
                $er .= "Por favor, tente novamente! Se o problema persistir entre em contato com nosso Suporte";
?>
                <script>$(function(){ showMessage('<?php echo $er; ?>'); });</script>
<?php
            }
?>

        </div>
    </div>
</div>
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