<?php
	session_start();

	if(!empty($_SESSION["iduser_bko_pub"])) {
		// Unset all of the session variables.
		$_SESSION = array();

		// If it's desired to kill the session, also delete the session cookie.
		// Note: This will destroy the session, and not just the session data!
		if (isset($_COOKIE[session_name()])) {
			setcookie(session_name(), '', time()-42000, '/');
		}

		// Finally, destroy the session.
		session_destroy();

		// Just in case anything was left...
		// http://br2.php.net/manual/en/function.session-destroy.php
		unset($_SESSION["iduser_bko_pub"]);
		unset($_SESSION["tipo_acesso_pub"]);	
		unset($_SESSION["opr_codigo_pub"]);	
		unset($_SESSION["nome_bko"]);
		unset($_SESSION["opr_nome"]);	
		unset($_SESSION["datalog_bko"]);
		unset($_SESSION["horalog_bko"]);
	}
	
	header("Location: /sys/admin/index.php");
?>
