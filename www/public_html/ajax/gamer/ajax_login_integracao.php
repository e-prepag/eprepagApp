<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
header("Content-Type: text/html; charset=ISO-8859-1",true);
require_once "../../../includes/constantes.php";
require_once DIR_INCS . "gamer/inc_ajax.php"; 

block_direct_calling();

require_once RAIZ_DO_PROJETO . "db/connect.php";
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "gamer/main.php"; 
require_once DIR_CLASS . "gamer/classIntegracao.php"; 

validaSessao(1);

$usuarioGames = unserialize($_SESSION['usuarioGames_ser']);
$instUsuario = new UsuarioGames();
if($_POST['login_integracao']=="OK") {
	if($instUsuario->autenticarLoginIntegracao($usuarioGames->ug_sEmail, $_POST['senha'])) {
	?>
		<script language="JavaScript" type="text/JavaScript">
			window.location.reload(true);
		</script>
	<?php
		die();
	}
	else {
		echo "<nobr class='txt-vermelho'>Senha incorreta. Tente novamente.</nobr>";
	}
} //end if($_POST['login_integracao']=="OK")
?>
<label>Utilizar Saldo</label>								
<div class='box-sessao-usuario-msg'>Caso tenha saldo EPP Cash, faça aqui seu <a class='exibeLogin' href='javascript:exibeLogin();'>login</a> na E-prepag <img src='/ativacao_pin/images/botao_login.gif' onclick='javascript:exibeLogin();' style='cursor:pointer;cursor:hand;' alt='Login' title='Login'/></div>
<div class='box-sessao-usuario-login text-left'>
	<form name='formLogin' id='formLogin' method='post'>			
        <span class='box-sessao-usuario-login-email'><p><?php echo $usuarioGames->ug_sEmail; ?></p></span>
        <p>
            <input type='password' name='senhaLogin' id='senhaLogin'/>
            <input type='button' name='btnLogin' id='btnLogin' onclick='javascript:login_integracao();' style='height: 24px; cursor:hand;' alt='Executar Login' title='Executar Login' value='OK'/>
        </p>
        <p>
            <i><a href='#'  onclick="esqueciSenha();" class='fontsize-pp'>Esqueci minha senha</a></i>
        </p>
	</form>
</div>
<?php

//Fechando Conexão
pg_close($connid);

?>
