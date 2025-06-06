<?php require_once __DIR__ . '/../../../includes/constantes_url.php'; ?>
<?php
session_start();

header("Content-Type: text/html; charset=ISO-8859-1",true);
function isAjax() {return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));}
function block_direct_calling() {
    if(!isAjax()) {
           echo "Chamada não permitida<br>";
           die("Stop");
    }
}
block_direct_calling();

require_once "../../../includes/configIP.php";
require_once "../../../includes/functions_captcha.php";

$https = 'http' . (($_SERVER['HTTPS']=='on') ? 's' : '');
$server_url = $https . '://' . (checkIP() ? $_SERVER['SERVER_NAME'] : '' . EPREPAG_URL . '');

$randomcode = generateRandomCode();	
$randomcode_translated = translateCode($randomcode);  


?>

<img width="110px" height="60px" class="pull-right" src="../../includes/captcha/CaptchaImage.php?uid=<?php echo $randomcode_translated; ?>&codigoRecaptcha=<?php echo $randomcode; ?>" title="Verify Code" vspace="2" />
