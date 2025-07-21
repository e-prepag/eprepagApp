<?php require_once __DIR__ . '/../../../includes/constantes_url.php'; ?>
<script type="text/javascript" src="/js/jquery.js"></script>
<?php
header("Content-Type: text/html; charset=ISO-8859-1",true);
require_once "../../../includes/constantes.php";
require_once  DIR_INCS . "main.php";
require_once  DIR_INCS . "pdv/main.php";
$https = 'http' . (($_SERVER['HTTPS']=='on') ? 's' : '');
require_once DIR_INCS . "configIP.php";

$server_url = $https . '://' . (checkIP() ? $_SERVER['SERVER_NAME'] : '' . EPREPAG_URL . '');
session_start();

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

if(isset($_POST["email"]) && $_POST["email"] != ""){

    $to = getenv("email_suporte");
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

?>

<script type="text/javascript">

    $(function(){
        if(parent.document.form_lanHouses_filtros.estado.value !== ""){
           document.form_lanHouses_filtros.estado.value = parent.document.form_lanHouses_filtros.estado.value;
           MostraCidade();
        }
        
        
    });

    function validaForm() {
        $("#msg_validacao").html("");

        if ( document.form_lanHouses_filtros.estado.value == "" ){
            $("#msg_validacao").append('<br><font color="#FF1F00"><b>Selecione um estado.</b></font>');

        }else if ( document.form_lanHouses_filtros.cidade.value == "" ){
            $("#msg_validacao").append('<br><font color="#FF1F00"><b>Selecione uma cidade.</b></font>');
        
        }
        else if (document.form_lanHouses_filtros.nome_do_Ponto_de_Venda.value == ""){
            $("#msg_validacao").append('<br><font color="#FF1F00"><b>O nome do ponto de venda não foi preenchido corretamente.</b></font>');
        
        }else if (document.form_lanHouses_filtros.email.value == "" || !validaEmail(document.form_lanHouses_filtros.email.value)){
            $("#msg_validacao").append('<br><font color="#FF1F00"><b>O e-mail não foi preenchido corretamente.</b></font>');
        
        }else if ( document.form_lanHouses_filtros.bairro.value == "" ){
            $("#msg_validacao").append('<br><font color="#FF1F00"><b>Selecione um bairro.</b></font>');
        
        }else if ( document.form_lanHouses_filtros.motivo.value == "" || (document.form_lanHouses_filtros.motivo.value == "outro" && document.form_lanHouses_filtros.outro_motivo.value == "")){
            $("#msg_validacao").append('<br><font color="#FF1F00"><b>O motivo deve ser informado.</b></font>');
        
        }else{
            $("#form_lanHouses_filtros").submit();
            //submete e envia e-mail
        }




            return false;
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
                            url: "lan_house_select_cidade.php",
                            data: "estado=" + estado,
                            beforeSend: function(){
                                    $("#SelCidade").html("&nbsp;&nbsp;&nbsp;Cidade: <select DISABLED><option>Buscando Cidade</option></select>");
                            },
                            success: function(html){
                                $("#SelCidade").html(html);
                                if(parentEstado !== "" && estado == parentEstado){
                                    document.form_lanHouses_filtros.cidade.value = parent.document.form_lanHouses_filtros.cidade.value;
                                    MostraBairro();
                                }else{
                                    document.form_lanHouses_filtros.cidade.value = "";
                                    $("#SelBairro").html('Bairro:&nbsp;&nbsp;&nbsp;<select name="bairro" id="bairro" DISABLED><option>Selecione uma Cidade</option></select>');
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
                            url: "lan_house_select_bairro_only.php",
                            data: "cidade=" + cidade + "&estado=" + estado,
                            beforeSend: function(){
                                    $("#SelBairro").html("&nbsp;&nbsp;&nbsp;Bairro: <select DISABLED><option>Buscando Bairro</option></select>");
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

<style>
    body {
        font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
        font-size: small;
        background: #fff;
    }
    
    select {
        border: solid 1px #CDDAE3;
        font: 11px arial;
        max-width: 180px;        
        color: #000000;
        height: 16px;
    }
    
    .textocentral{color: #0F64CF;font-family: "Verdana", Arial, Serif}
</style>
<form id="form_lanHouses_filtros" name="form_lanHouses_filtros" style="width: 250px;" method="post">
    <p class="textocentral ">O que aconteceu?</p>
    <p> 
        <select name="motivo" id="motivo">
            <option value="O Ponto de venda não existe">O Ponto de venda não existe</option>
            <option value="O endereço está errado">O endereço está errado</option>
            <option value="O Ponto de venda não tem saldo">O Ponto de venda não tem saldo</option>
            <option value="Não souberam como vender o crédito">Não souberam como vender o crédito</option>
            <option value="outro">Outro</option>
        </select>
        <textarea rows="3" id="outro_motivo" name="Outro_Motivo" style="display:none; margin-top: 5px;" placeholder="Qual?"  cols="24"></textarea>
    </p>
    <div>
        Estado:&nbsp;&nbsp;
            <select name="estado" id="estado" onChange='MostraCidade();'>
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
    <div id="SelCidade">
        Cidade:&nbsp;
        <?php
        if (isset($ResultadoCidade)) {
            echo '<select name="cidade" id="cidade"  onChange="MostraBairro();">';
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
            <select name="bairro" id="bairro" DISABLED>
                <option>Selecione um Estado</option>		
            </select>
            <?php
        }
        ?>
    </div>
    <div id="SelBairro">
    Bairro:&nbsp;&nbsp;&nbsp;
    <?php
    if (isset($ResultadoBairro)) {
        echo '<select name="bairro" id="bairro">';
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
        <select name="bairro" id="bairro" DISABLED>
            <option>Selecione uma Cidade</option>		
        </select>
        <?php
    }
    ?>
    </div>
    <p><input type="text" style=width:190px;" placeholder="Nome do ponto de venda" id="nome_do_Ponto_de_Venda" name="nome_do_Ponto_de_Venda"></p>
    <p><input type="text" style=width:190px;" id="email" name="email" placeholder="Seu e-mail"></p>
    <p id="msg_validacao"></p>
    <p><img src="/imagens/botao_enviar.gif" onclick="validaForm()"></p> 
    </form>
