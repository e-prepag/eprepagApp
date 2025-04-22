<?php
// include do arquivo contendo IPs DEV
// include do arquivo contendo IPs DEV
require_once "../../includes/constantes.php";
require_once  DIR_INCS . "configIP.php";

// Constante que define o ambiente de conexão Produção (Live = 1) ou Homologação (Test = 2)
if(checkIP()) {
    $server_url = $_SERVER['SERVER_NAME'];
    }
else {
    $server_url = "www.e-prepag.com.br";
}
?>
<title>E-Prepag - Créditos para games online</title>
<link href="http<?php echo (($_SERVER['HTTPS']=="on")?"s":""); ?>://<?php echo $server_url; ?>/eprepag/incs/landingpage.css" rel="stylesheet" type="text/css" />
<body>
<div class="principal">
    <div class="faixa">
    </div>
    <div class="links">
            <a href="https://www.e-prepag.com/" target="_blank" class="corlink">E-Prepag</a> | <a href="https://www.e-prepag.com/support" target="_blank" class="corlink">Suporte</a> | <a href="http://blog.e-prepag.com/seja-um-ponto-de-venda/" target="_blank" class="corlink">Seja um ponto autorizado</a>
    </div>
    <div class="dadosparceiro">
        <img src="http<?php echo (($_SERVER['HTTPS']=="on")?"s":""); ?>://<?php echo $server_url; ?>/imagens/logo_ongame.jpg" class="goodgames" width="150"/>
        <span class="textocentral">
            Encontre aqui um Ponto de Venda autorizado
        </span>
        <img src="http<?php echo (($_SERVER['HTTPS']=="on")?"s":""); ?>://<?php echo $server_url; ?>/imagens/logo_mapa.gif" class="imglogo" />
    </div>
    <br>
    <center>
    <div>
        <iframe src="http<?php echo (($_SERVER['HTTPS']=="on")?"s":""); ?>://<?php echo $server_url; ?>/eprepag/moedavirtual/lan_houses_ongame.php" width="790" style="margin:0 auto; overflow: hidden;" height="730" frameborder="0" scrolling="no">
        </iframe>
    </div>
    </center>
    <div class="seja">
        <a href="https://e-prepagpdv.com.br/" target="_blank" class="corlink2">
            Seja um  ponto autorizado
        </a>
    </div>
</div>    
<script src="http<?php echo (($_SERVER['HTTPS']=="on")?"s":""); ?>://www.google-analytics.com/urchin.js" type="text/javascript"></script>
<script type="text/javascript">
    _uacct = "UA-1903237-3";
    urchinTracker();
</script>
</body>
