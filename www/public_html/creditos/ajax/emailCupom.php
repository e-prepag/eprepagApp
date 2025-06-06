<?php require_once __DIR__ . '/../../../includes/constantes_url.php'; ?>
<?php 
//script equivalente a "/www/web/prepag2/dist_commerce/conta/ajax_cupom_email_dr.php"
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
header("Content-Type: text/html; charset=ISO-8859-1",true);
function isAjax() {return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));}
function block_direct_calling() {
    if(!isAjax()) {
           echo "Chamada não permitida<br>";
           die("Stop");
    }
}
block_direct_calling();
require_once "../../../includes/constantes.php";
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "pdv/main.php";
require_once DIR_CLASS."pdv/classOperadorGamesUsuario.php";

// include do arquivo contendo IPs DEV
require_once DIR_INCS. "configIP.php";

$a = session_id();
if(!$a)
    session_start();

/*
vgpe_pin_codinterno	=	'Campo contendo o ID do modelo do produto. Correspondente ao campo pin_codinterno da tabela pins_dist.';
vgpe_ug_id			=	'Campo contendo o ID da LAN House. Correspondente ao campo ug_id da tabela dist_usuarios_games.';
vgpe_email			=	'Campo contendo o email para qual o PIN foi enviado.';
*/

//validaSessao(); 

$server_url = "" . EPREPAG_URL . "";
if(checkIP()) {
    $server_url = $_SERVER['SERVER_NAME'];
}

//Recupera usuario
if(isset($_SESSION['dist_usuarioGames_ser']) && !is_null($_SESSION['dist_usuarioGames_ser'])){
	$usuarioGames = unserialize($_SESSION['dist_usuarioGames_ser']);
	$usuarioId = $usuarioGames->getId();
        
        //echo "<pre>".print_r($_POST,true)."</pre>";

        if(empty($_POST['listaPINs'])) {
            $listaPINs = "";
            foreach($_POST as $key => $val) {
                if($key!=str_replace('emitir', '', $key)) { 
                    if(empty($listaPINs)) {
                        $listaPINs = $val;
                    }//end if(empty($listaPINs))
                    else {
                        $listaPINs .= ",".$val;
                    }//end else do if(empty($listaPINs))
                }//end if($key!=str_replace('emitir', '', $key))
            }//end foreach($_POST as $key => $val)
        }//end if(empty($_POST['listaPINs']))
        else {
            $listaPINs = $_POST['listaPINs'];
        }//end else do if(empty($_POST['listaPINs']))
        
        if(empty($listaPINs)) {
            die("<p class='text-red'>Nenhum PIN selecionado.</p>");
        }//end if(empty($listaPINs))
?>
    <script language="javascript">
    function validaALL() {
            if(validateEmail(document.form3.email.value)) { 
                    if(document.form3.email.value == document.form3.email2.value) { 
                            document.form3.envia_email.value=1;
                            return true;
                    } //end if(document.form3.email.value == document.form3.email2.value)
                    else { 
                            alert('Email de Confirmação digitado diferente.');
                            document.form3.email.focus();
                            document.form3.email.select();
                            return false;
                    } 
            } //end if(validateEmail(document.form3.email.value))
            else {
                    alert('Informe um email válido.');
                    document.form3.email.focus();
                    document.form3.email.select();
                    return false;
            }
    }//end function validaALL()

    function validateEmail(email) { 
        var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(email);
    }

    function verifica(evt)
    {
            if (evt == 17)
            {
                    return false;
            }
    }

    document.event;

    //Parte nova do DropShadow
    function Trim(str){
        return str.replace(/^\\s+|\\s+$/g,'');
    }
    
    </script>
    <div id='popup_envio_email' class="col-md-12" align='left' title='Enviar PIN via E-Mail'>
    <form method='POST' name='form3' id='form3' onsubmit='return validaALL();'>
        <input type='hidden' name='listaPINs' id='listaPINs' value='<?php echo $listaPINs; ?>' />
        <input type="hidden" name="tf_v_codigo_detalhe" id="tf_v_codigo_detalhe" value="<?php echo $_POST['tf_v_codigo_detalhe']; ?>">
        <input type="hidden" name="nao_emitidos" id="tf_v_codigo_detalhe" value="<?php echo $_POST['nao_emitidos']; ?>">
        <input type="checkbox" name="enviandoEmailH" class="hidden">
        <!-- novo lay -->
        <div class="row txt-azul-claro">
            <p class="top20 margin004"><strong>Digite aqui o e-mail de seu CLIENTE:</strong></p>
            <p class="margin004"><input class="input-sm form-control input-medium" type="text"  name="email" type="text" id="email" value="<?php echo $email; ?>" size="32" maxlength="100"></p>
            <p class="fontsize-pp txt-preto margin004"><em>Campo obrigatório</em></p>
            <p class="top20 margin004"><strong>Confirme o e-mail de seu CLIENTE:</strong></p>
            <p class="margin004"><input class="input-sm form-control input-medium" type="text"  name="email2" id="email2" value="<?php echo $email2; ?>" size="32" maxlength="100" autocomplete="off" onKeyUp="verifica(event)" onpaste="return false;"></p>
            <p class="fontsize-pp txt-preto margin004"><em>Campo obrigatório</em></p>
        </div>
        <div class="row top10 txt-vermelho fontsize-p">
            <div class="col-md-6 top20">
                <p>Confirme novamente com seu cliente o e-mail que receberá o PIN. Sugerimos soletrar letra a letra para que ele confirme.</p>
            </div>
            <div class="col-md-12">
                <input type="hidden" name="envia_email" id="envia_email" value="">
                <input type="submit" class="btn btn-success" value="Enviar" name="bt_enviar" id="bt_enviar" title="Enviar PINs por E-Mail">
            </div>
        </div>
        <!-- /novo lay -->
    </form>
    </div>
    <?php
} //end if(isset($_SESSION['dist_usuarioGames_ser']) && !is_null($_SESSION['dist_usuarioGames_ser']))
else {
?>
    <p>Seu login expirou. Por favor, faça novamente o login para concluir esta venda.</p>
<?php
}//end else do if(isset($_SESSION['dist_usuarioGames_ser']) && !is_null($_SESSION['dist_usuarioGames_ser']))
?>
