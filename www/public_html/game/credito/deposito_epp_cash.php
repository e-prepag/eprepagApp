<?php  
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') )
{
        $teste = substr($_SERVER['HTTP_USER_AGENT'],strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')+4,4)*1;
        echo "<meta http-equiv=\"X-UA-Compatible\" content=\"IE=".htmlspecialchars($teste)."\" />";
}

require_once "../../../includes/constantes.php";
require_once DIR_CLASS . 'gamer/controller/HeaderController.class.php';
$controller = new HeaderController;
$controller->setHeader();
$controller->atualizaSessaoUsuario();
require_once DIR_INCS . "inc_register_globals.php";    

//codigo para deposito em EPP CASH
$pagto_tipo = 13;

?>
<script language="JavaScript" type="text/JavaScript">
        function abrePaginaBanco() {
                $("#vaipagamento").click();
        }

        function GP_popupConfirmMsg(msg) { //v1.0
          document.MM_returnValue = confirm(msg);
        }

        function IrAoBanco() {
                $("#btnIrAoBanco").click();
        }
        function ShowPopupWindowXY(fileName) {
                myFloater = window.open(fileName,'myWindow','scrollbars=yes,status=no,width=' +(0.8*screen.width) +',height='+(0.8*screen.height) +',top='+(0.1*screen.height) +',left='+(0.1*screen.width)+'');
        }
</script>
<div class="container txt-azul-claro bg-branco">
    <div class="row top20">
        <div class="col-md-12">
            <div class="txt-azul-claro top10 row">
                <span class="glyphicon glyphicon-triangle-right graphycon-big pull-left" aria-hidden="true"></span><strong class="pull-left"><h4 class="top20">E-Prepag CASH</h4></strong>
            </div>
            <center>
<?php
            //inicio do bloco de pagamento com PINS EPREPAG
            unset($_SESSION['PINEPP']);
            unset($_SESSION['PIN_NOMINAL']);
            echo "
            <div id='box-principal' name='box-principal'>
            ";

            $aux_saldo = unserialize($_SESSION['usuarioGames_ser']);

            include_once(DIR_CLASS."classAtivacaoPinTemplate.class.php");
            if (b_isIntegracao() && b_isIntegracao_with_nonvalidated_email() && (!b_isIntegracao_logged_in())) {
                    $user_logado_aux	= false;
                    $saldo_aux			= 0;
                    $saldo_final_aux	= number_format(0,2,'.','');
            }
            else {
                    $user_logado_aux	= true;
                    $saldo_aux			= $aux_saldo->ug_fPerfilSaldo;
                    $saldo_final_aux	= number_format($aux_saldo->ug_fPerfilSaldo,2,'.','');
                            }


            //echo "[".$aux_saldo->ug_fPerfilSaldo."]";
            $paramList	= array(
                                                    'jquery_core_include'	=>	false,
                                                    'url_resources'			=>	'/ativacao_pin/',
                                                    'usuarioLogado'			=>	$user_logado_aux,
                                                    'saldo'					=>	$saldo_aux,
                                                    'valor_pedido'			=>	0,
                                                    'saldo_final'			=>	$saldo_final_aux,
                                                    'email'					=>	$_SESSION['integracao_client_email'],
                                                    'box_carga_saldo'		=>	true,
                                                    );
            $ativacaoPinTemplate2 = new AtivacaoPinTemplate($paramList);
            echo $ativacaoPinTemplate2->boxAtivacaoPin();
            echo "
            </div>";

// bloco pagamento efetuado inicio 
?>       
        </center>
        <div id="pagamento_ok">	
                                <div class="textoProdutoAux"><br>
                                Dep&oacute;sito realizado com sucesso.<br>
                                <br>
                                <a href="/game/conta/extrato.php" name="btVoltar" class="decoration-none btn btn-info">Visualizar extrato</a>
                                <br><br>
                                </div>
        </div>
<?php 
// bloco pagamento efetuado fim  
// bloco pagamento cancelado inicio
?>
        <div id="pagamento_cancela">
                <center>
                Dep&oacute;sito <font color="#FF0000">cancelado</font>.<br>
                Por favor, tente novamente.<br>
                <br>
                </center>
        </div>			
<?php 
// bloco pagamento cancelado fim  
?>
        <script language="JavaScript" type="text/JavaScript">
                $("#pagamento_ok").hide();
                $("#pagamento_cancela").hide();
        </script>
        </div>
    </div>
</div>
</div>
<?php 
require_once RAIZ_DO_PROJETO . "public_html/game/includes/footer.php";

?>
