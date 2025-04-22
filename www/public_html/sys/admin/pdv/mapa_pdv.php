<?php
 
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
 
require_once "../../../../includes/constantes.php";
require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";
require_once $raiz_do_projeto . "public_html/sys/includes/gamer/inc_pub_access.php";
//include_once '../../../incs/ConnectionPDO.php';
 
$https = 'http' . (($_SERVER['HTTPS']=='on') ? 's' : '');
$need_key_maps = (checkIP())?"sensor=false":"key=AIzaSyA25PAcZMc6toew3UDW1HwG8wve00r8hb4";
?>
<html>
<head>
     <link rel="stylesheet" href="/sys/css/css.css" type="text/css">
     <title>E-Prepag</title>
     <script language='javascript' src='../stats/js/jquery-1.4.4.js'></script>
 
</head>
<link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/js/jquery.js"></script>
<script type="text/javascript" src="/includes/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="<?php echo $https; ?>://maps.google.com/maps/api/js?<?php echo $need_key_maps; ?>"></script>
<script type="text/javascript">
 
     function ValidaForm() {
 
         if ($("#id_pdv").val().trim() == ""){
             $("#msg_validacao").html('<p class="txt-vermelho alert alert-danger"><b>Preencha com um ID.</b></p>');
         }else if (isNaN($("#id_pdv").val().trim())){
             $("#msg_validacao").html('<p class="txt-vermelho alert alert-danger"><b>Preencha com um ID válido.</b></p>');
         }else{
            $("#msg_validacao").html('');
            MostraLANs();
         }
     }
 
     function MostraLANs() {
         if ($("#id_pdv").val().trim() != ""){
             id_pdv = $("#id_pdv").val().trim();
             $.ajax({
                 type: "POST",
                 url: "pdvs_result.php",
                 data: {id_pdv: id_pdv},
                 beforeSend: function(){
                    $("#resultado").html("<p class='txt-cinza'>Fazendo a consulta!</p>");
                 },
                 success: function(txt){
					 var result = txt;
					 if(result.indexOf("Nenhum") != -1){
						 $("#resultado").html("");
						 $("#msg_validacao").html('<p class="txt-vermelho alert alert-danger"><b>Nenhum Ponto de Venda Encontrado com este ID</b></p>');
					 } else{
						$("#resultado").html(txt);
					 }
                     
                 },
                 error: function(){
                    $("#resultado").html("<p class='txt-vermelho alert alert-danger'><b>ERRO NO SERVIDOR! TENTE NOVAMENTE MAIS TARDE</b></p>");
                 }
             });
         }
     }
 
     function get_action_recaptcha(){
        var response = grecaptcha.getResponse();
        if(response.length == 0){
            $("#msg_validacao").html('<p class="txt-vermelho alert alert-danger"><b>Por favor, efetue a verificação do reCAPTCHA.</b></p>');
            return false;
        } else{
            $("#msg_validacao").html('');
            ValidaForm();
            grecaptcha.reset();
            return true;
        }
     }
     
     var onloadCallback = function(){
        grecaptcha.render(  'html_recap', {
                            'sitekey' : '6LffiGIUAAAAAOVQQN4LMJhw1Zc4jWWS4UovyQLk',
                            'lang' : 'pt'
                        });
 
    };
 
</script>
<script>
    $(document).ready(function(){
        $("#BtnSearch").click(function(e){
            get_action_recaptcha();
        });
    });
</script>
 
<body>
    <div class="container-fluid bg-verde altura-verde"><br></div>
    <div class="container-fluid bg-azul txt-branco">
        <div class="row espacamento">
            <div class ="col-md-1"></div>
            <div class="col-md-6" >
                <h4><strong><?php echo LANG_MAP_PDV_TITLE; ?></strong></h4>
            </div>
        </div>
    </div>
    <div class="container-fluid bg-cinza-claro"><br></div>
    <div class="container-fluid bg-cinza-claro">
        <div class="container txt-azul-claro bg-branco">
            <div class="row">
                <div class="col-md-12">
                    <div class="row txt-cinza espacamento">
                        <div class="col-md-6">
                            <span class="pull-left txt-azul-claro"><h4><strong><?php echo LANG_PINS_SEARCH_1; ?></strong></h4></span>
                        </div>
                    </div>
                    <form name="form_id_pdv" method="post" action="">
                        <div class="row txt-cinza espacamento">
                            <div class="col-md-1 espacamento-menor-dir">
                                <span class="font-big pull-right"><strong>ID:</strong></span>
                            </div>
                            <div class="col-md-2 espacamento-menor-esp">
                                <input name="id_pdv" type="text" class="form-control w-ipt-medium pull-left" size="5" id="id_pdv" value="<?php echo $id_pdv ?>">
                            </div>
                            <div class="col-md-2 espacamento-menor-esp">
                                <button type="button" id="BtnSearch" name="BtnSearch" value="<?php echo LANG_MAP_PDV_SEARCH_2; ?>" class="btn btn-success"><?php echo LANG_MAP_PDV_SEARCH_2; ?></button>
                            </div>
                            
                        </div>
                        <div class="row espacamento-recaptcha">
                            <div class="col-md-5">
                               <div id="html_recap" class="pull-left "></div>
                            </div>
                        </div>
                    </form>
                    <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit&hl=pt" async defer></script>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div id="msg_validacao"></div>
                </div>
            </div>
            <table class="espacamento" width="785px" border="0" cellpadding="0" cellspacing="0" >
                <tr>
                    <td>
                        <!-- inicio :: resultado-->
                        <div name="resultado" id="resultado"></div>
                        <!-- fim :: resultado-->
                    </td>
                </tr>
            </table>
        </div>
        <br><br>
    </div>
 
</body>
</html>
 
<?php
 
require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php";