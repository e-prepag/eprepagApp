<?php
//ini_set('display_errors', 1);
//error_reporting(E_ALL);
require_once "../../../includes/constantes.php";
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	
    if (array_key_exists('type', $_GET) ) {
		
        $type = filter_input(INPUT_GET, 'type');

        if ( $type === 'email' ) {
            
            $sent = false;
            $email = filter_input(INPUT_GET, 'email');
            $username = filter_input(INPUT_GET, 'username');
            $password = filter_input(INPUT_GET, 'password');
            if($password != 'P@8v#Xz4!Tm9'){
                die("Acesso negado");
            }

            require_once dirname(__FILE__) . '/bootstrap.php';
//            require_once('C:/Sites/E-Prepag/www/web/incs/ConnectionPDO.php');
//            $p = ConnectionPDO::getConnection();
//            $pdo = $p->getLink();

//            $stmt = $pdo->prepare('SELECT * FROM lead_cadastro WHERE email = ?');
//            $stmt->execute(array(strtoupper($email)));
//            if ( count($stmt->fetchAll()) > 0 ) {
//                $sent = true;
//            }

            if ( !$sent ) {
                lead($email,$username);
                //$stmt2 = $pdo->prepare('INSERT INTO lead_cadastro (email) VALUES (?)');
                //$stmt2->execute(array(strtoupper($email)));
            }

            echo json_encode(array('sent'=>$sent));
        }
    }
}

// Monta o corpo do e-mail de controle e dispara
function lead($email, $username){
    $html = <<<EMAIL
<h3>Novo Ponto de Venda Cadastrado</h3>

<div>
    Um novo PDV realizou seu cadastro.<br />
    Dados:<br />
    E-mail: $email <br />
    Login: $username <br />
</div>
EMAIL;
	enviaEmail('rc1@e-prepag.com.br,help@e-prepag.com.br,jose.carlos@easygroupit.com', 'rc@e-prepag.com.br', null, 'PDV registrado', $html);
}

