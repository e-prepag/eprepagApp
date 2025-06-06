<?php require_once __DIR__ . '/../../../includes/constantes_url.php'; ?>
<?php

	function chaveEspecial() {
		
		$chaveEspecial = 'cH@V3$3cR&t4';
		return $chaveEspecial;
		
	}
	
	function capturaTimeStamp() {
		
		$timestamp = time();
		return $timestamp;
		
	}
	
	function capturaDataHoraAtual() {
		
		$dataHoraAtual = date("Y-m-d H:i:s");
		return $dataHoraAtual;
		
	}
	
	function capturaIp() {
		
		$ip = $_SERVER['REMOTE_ADDR'];
		return $ip;
	}
	
	// Faz um LOG de todas as novas solicita��es
	function geraLogNovaSolicitacao($arquivoLog, $mensagemLog) {
		
		$handle = fopen($arquivoLog, 'a+');
		$dataHoraAtual = capturaDataHoraAtual();
		$divisor = str_repeat('#',100);
		
		$conteudo = "\n{$divisor}\n\n{$dataHoraAtual} - Nova Solicita��o\n{$mensagemLog}\n";
		
		fwrite($handle, $conteudo);
		fclose($handle);
		
	}
	
	function geraLogEnvioEmail($arquivoLog, $mensagemLog) {
		
		$handle = fopen($arquivoLog, 'a+');
		$dataHoraAtual = capturaDataHoraAtual();
		$divisor = str_repeat('#',100);
		
		$conteudo = "\n{$divisor}\n\n{$dataHoraAtual} - Nova Solicita��o de\n{$mensagemLog}\n";
		
		fwrite($handle, $conteudo);
		fclose($handle);
		
	}
	
	function geraLogAtualizaSolicitacao($arquivoLog, $mensagemLog) {
		
		$handle = fopen($arquivoLog, 'a+');
		$dataHoraAtual = capturaDataHoraAtual();
		$divisor = str_repeat('#',100);
		
		$conteudo = "\n{$divisor}\n\n{$dataHoraAtual} - {$mensagemLog}\n";
		
		fwrite($handle, $conteudo);
		fclose($handle);
		
	}
	
	// Fun��o para gerar um c�digo de valida��o �nico
    function geraCodigoValidacao($idUsuario, $emailUsuario, $timestamp) {

		$chaveEspecial = chaveEspecial();

        $codigoHash = hash('sha256', $idUsuario . $emailUsuario . $chaveEspecial . $timestamp); // Gera um hash �nico usando o ID do usu�rio e o timestamp
		
		$codigoValidacao = substr($codigoHash, 0, 12);
		
        return $codigoValidacao;
		
    }
	
	// Pesquisa no BD pelo ID do usu�rio usando o Login e o E-mail como refer�ncias
	function encontraIdUsuario($loginUsuario, $emailUsuario) {
		
		$tabela = 'dist_usuarios_games';
		$loginUsuario = strtoupper($loginUsuario);
		$emailUsuario = strtoupper($emailUsuario);
		
		$conexao = ConnectionPDO::getConnection()->getLink();
		
        $sql = "select ug_id from {$tabela} where ug_login = :LOGIN and ug_email = :EMAIL;";
        $query = $conexao->prepare($sql);
        $query->bindValue(':LOGIN', $loginUsuario);
        $query->bindValue(':EMAIL', $emailUsuario);
        $query->execute();
        $resultadoQuery = $query->fetchAll(PDO::FETCH_ASSOC);
		
		empty($resultadoQuery) ? $retorno = 'Usuario n�o encontrado' : $retorno = $resultadoQuery[0]['ug_id'];
		
		return $retorno;
		
    }
	
	// Pesquisa no BD pelo Nome do Usu�rio usando o Login e o E-mail como refer�ncias
	function encontraNomeUsuario($loginUsuario, $emailUsuario) {
		
		$tabela = 'dist_usuarios_games';
		
		$conexao = ConnectionPDO::getConnection()->getLink();
		
        $sql = "select ug_nome from {$tabela} where ug_login = :LOGIN and ug_email = :EMAIL;";
        $query = $conexao->prepare($sql);
        $query->bindValue(':LOGIN', strtoupper($loginUsuario));
        $query->bindValue(':EMAIL', strtoupper($emailUsuario));
        $query->execute();
        $resultadoQuery = $query->fetchAll(PDO::FETCH_ASSOC);
		
		$nomeCompleto = trim($resultadoQuery[0]['ug_nome']);
		
		return $nomeCompleto;
		
    }
	
	// Registra no BD a nova solicita��o e as informa��es de quem solicitou
	function registraNovaSolicitacao($dadosUsuario, $codigoValidacao, $timestamp) {
		
		$tabela = 'tb_esqueci_minha_senha_pdv';
		
		$conexao = ConnectionPDO::getConnection()->getLink();
					
		$idUsuario = $dadosUsuario['idUsuario'];
		$loginUsuario = $dadosUsuario['loginUsuario'];
		$emailUsuario = $dadosUsuario['emailUsuario'];
		$nomeCompletoUsuario = $dadosUsuario['nomeCompletoUsuario'];
		$primeiroNomeUsuario = $dadosUsuario['primeiroNomeUsuario'];
		$ipUsuario = $dadosUsuario['ipUsuario'];
		$dataHoraAtual = capturaDataHoraAtual();
		$status = 'Aguardando valida��o';
					
		$sql = "insert into {$tabela}(ug_id, ug_login, ug_nome_completo, ug_nome, ug_email, ip, data_solicitacao, codigo_verificacao, codigo_timestamp, status) 
				values (:ID, :LOGIN, :NOME_COMPLETO, :NOME, :EMAIL, :IP, :DATA, :CODIGO, :TIME, :STATUS);";
		$query = $conexao->prepare($sql);
		$query->bindValue(':ID', $idUsuario);
		$query->bindValue(':LOGIN', $loginUsuario);
		$query->bindValue(':NOME_COMPLETO', $nomeCompletoUsuario);
		$query->bindValue(':NOME', $primeiroNomeUsuario);
		$query->bindValue(':EMAIL', $emailUsuario);
		$query->bindValue(':IP', $ipUsuario);
		$query->bindValue(':DATA', $dataHoraAtual);
		$query->bindValue(':CODIGO', $codigoValidacao);
		$query->bindValue(':TIME', $timestamp);
		$query->bindValue(':STATUS', utf8_encode($status));
		$query->execute();
	
		$arquivoLog = 'logEsqueciMinhaSenha.log';
					
		$mensagemLog = "ID: {$idUsuario}\nIP: {$ipUsuario}\nLOGIN: {$loginUsuario}\nE-MAIL: {$emailUsuario}\nC�DIGO: {$codigoValidacao}";

		geraLogNovaSolicitacao($arquivoLog, $mensagemLog);
		
	}
	
	// Recolhe as informa��es e dispara o e-mail
	function enviaEmailParaValidarSolicitacao($dadosUsuario, $codigoValidacao) {

		require_once 'configuracoes-email.php';
		
		$to = $dadosUsuario['emailUsuario'];
		$cc = null;
		$bcc = null;
		$subject = 'Solicita��o de Troca de Senha';
		$body_html = "<html><head><title>Esqueci Minha Senha</title></head><body><h1>Ol�, {$dadosUsuario['primeiroNomeUsuario']}</h1><p>Voc� poder� cadastrar uma nova senha clicando no link abaixo:</p><a target='_blank' href='" . EPREPAG_URL_HTTPS . "/creditos/esqueci-minha-senha/verifica-solicitacao.php?redirected=true&codigoValidacao={$codigoValidacao}&origemUsuario={$dadosUsuario['origemUsuario']}'>Mudar a Senha</a><p>Caso voc� n�o tenha feito essa solicita��o, ignore o link acima e nos avise: <a href='" . EPREPAG_URL_HTTPS . "/game/suporte.php' target='_blank' title='Entre em contato conosco'>aqui</a>.</p></body></html>";
		//$body_html = file_get_contents('/www/includes/templates/testeEmailMudaSenha.html');
		$body_html = html_entity_decode($body_html, ENT_QUOTES, 'ISO8859-1');
		$body_plain = '';

			
		return disparaEmail($to, $cc, $bcc, $subject, $body_html, $body_plain, $codigoValidacao);
			
	}
	
	// Fun��o para verificar se o c�digo � v�lido ou se expirou
    function verificaCodigoValidacao($codigoValidacao) {

		$conexao = ConnectionPDO::getConnection()->getLink();
		
        $sql = "select * from tb_esqueci_minha_senha_pdv where codigo_verificacao = :CODE ;";
        $query = $conexao->prepare($sql);
        $query->bindValue(':CODE', $codigoValidacao);
        $query->execute();
        $resultadoQuery = $query->fetchAll(PDO::FETCH_ASSOC);

		if (empty($resultadoQuery)) {
			
			$retorno = 'Solicita��o n�o encontrada';
			
		} else {
			
			$timestampAtual = capturaTimeStamp();
			$idUsuario = $resultadoQuery[0]['ug_id'];
			$ipUsuario = $resultadoQuery[0]['ip'];
			$loginUsuario = $resultadoQuery[0]['ug_login'];
			$dataSolicitacao = $resultadoQuery[0]['data_solicitacao'];
			$dataHoraAtual = capturaDataHoraAtual();
			$codigo_timestamp = $resultadoQuery[0]['codigo_timestamp'];
			$codigo_verificacao = $resultadoQuery[0]['codigo_verificacao'];
			$statusSolicitacao = $resultadoQuery[0]['status'];
			
			
			if (($codigoValidacao === $codigo_verificacao) && $statusSolicitacao !== 'Expirado' && $statusSolicitacao !== 'Validado') {

				if ($timestampAtual - $codigo_timestamp <= 3600 ) {
					
					$retorno = 'Validado';
					
				} else {
					
					$retorno = 'Expirado';
					
				}
				
			} else {
				
				$retorno = $statusSolicitacao;
				
			}
		
		}
		
		$arquivoLog = 'logEsqueciMinhaSenha.log';
					
		$mensagemLog = "O c�digo {$codigoValidacao} est� {$retorno}\nData da atualiza��o {$dataHoraAtual}";
		
		geraLogAtualizaSolicitacao($arquivoLog, $mensagemLog);
		
		return $retorno;
		
    }
	
	function expiraSolicitacao($codigoValidacao) {
		
		$statusSolicitacao = 'Expirou';
		
		$conexao = ConnectionPDO::getConnection()->getLink();
		
		$sql = "update tb_esqueci_minha_senha_pdv set status = :STATUS where codigo_verificacao = :CODE ;";
		$query = $conexao->prepare($sql);
		$query->bindValue(':CODE', $codigoValidacao);
		$query->bindValue(':STATUS', $statusSolicitacao);
		$query->execute();

	}
	
	// Fun��o para registrar no BD que a solicita��o foi validada
	function validaSolicitacao($codigoValidacao) {
		
		$statusSolicitacao = 'Validado';
		
		$conexao = ConnectionPDO::getConnection()->getLink();
		
		$sql = "update tb_esqueci_minha_senha_pdv set status = :STATUS where codigo_verificacao = :CODE ;";
		$query = $conexao->prepare($sql);
		$query->bindValue(':CODE', $codigoValidacao);
		$query->bindValue(':STATUS', $statusSolicitacao);
		$query->execute();

	}
	
	// Fun��o para pegar os dados do usu�rio registrados em tb_esqueci_minha_senha_pdv
	function capturaDadosSolicitacao($codigoValidacao, $origemUsuario) {
		
		$tabela = 'tb_esqueci_minha_senha_pdv';
		$conexao = ConnectionPDO::getConnection()->getLink();
		
		$sql = "select * from {$tabela} where codigo_verificacao = :CODE ;";
		$query = $conexao->prepare($sql);
		$query->bindValue(':CODE', $codigoValidacao);
		$query->execute();
		$respostaQuery = $query->fetchAll(PDO::FETCH_ASSOC);
		
		return $respostaQuery;
		
	}
	
	// Fun��o para defirnir o status da requisi��o como 'Alterando senha'
	function defineStatusAlterandoSenha($codigoValidacao) {
		
		$statusSolicitacao = 'Mudando Senha';
		
		$conexao = ConnectionPDO::getConnection()->getLink();
		
		$sql = "update tb_esqueci_minha_senha_pdv set status = :STATUS where codigo_verificacao = :CODE ;";
		$query = $conexao->prepare($sql);
		$query->bindValue(':CODE', $codigoValidacao);
		$query->bindValue(':STATUS', $statusSolicitacao);
		$query->execute();

	}
	
	// Fun��o para defirnir o status da requisi��o como 'Senha atualizada'
	function defineStatusSenhaAtualizada($codigoValidacao) {
		
		$statusSolicitacao = 'Senha Atualizada';
		
		$conexao = ConnectionPDO::getConnection()->getLink();
		
		$sql = "update tb_esqueci_minha_senha_pdv set status = :STATUS where codigo_verificacao = :CODE ;";
		$query = $conexao->prepare($sql);
		$query->bindValue(':CODE', $codigoValidacao);
		$query->bindValue(':STATUS', $statusSolicitacao);
		$query->execute();

	}
	
	function defineStatusErro($codigoValidacao) {
		
		$statusSolicitacao = 'Erro na Atualiza��o';
		
		$conexao = ConnectionPDO::getConnection()->getLink();
		
		$sql = "update tb_esqueci_minha_senha_pdv set status = :STATUS where codigo_verificacao = :CODE ;";
		$query = $conexao->prepare($sql);
		$query->bindValue(':CODE', $codigoValidacao);
		$query->bindValue(':STATUS', utf8_encode($statusSolicitacao));
		$query->execute();

	}
	
	// Fun��o para fazer a criptografia da senha
	function criptografaSenha($confirmacaoNovaSenha) {
		
		require_once "/www/class/classEncryption.php"; 
		
		$criptografia = new Encryption();
		$senhaCriptografada = $criptografia->encrypt($confirmacaoNovaSenha);
		
		return $senhaCriptografada;
		
	}
	
	function registraNovaSenha($novaSenhaCriptografada, $idUsuario) {
		
		$conexao = ConnectionPDO::getConnection()->getLink();
		
		$sql = "update dist_usuarios_games set ug_senha = :NOVA_SENHA where ug_id = :ID ;";
		$query = $conexao->prepare($sql);
		$query->bindValue(':NOVA_SENHA', $novaSenhaCriptografada);
		$query->bindValue(':ID', $idUsuario);
		$query->execute();
	}
	
	function atualizaHistoricoCliente($idUsuario, $idIp, $idDataTrocaSenha) {
		
		$status = 4;
		
		$conexao = ConnectionPDO::getConnection()->getLink();
		
		$sql = "insert into dist_usuarios_games_log(ugl_data_inclusao, ugl_ip, ugl_uglt_id, ugl_ug_id) values(:DATA, :IP, :STATUS, :ID) ;";
		$query = $conexao->prepare($sql);
		$query->bindValue(':ID', $idUsuario);
		$query->bindValue(':IP', $idIp);
		$query->bindValue(':DATA', $idDataTrocaSenha);
		$query->bindValue(':STATUS', $status);
		$query->execute();
		
	}
	
	function validarSenha($confirmacaoNovaSenha) {
		
		// Verificar comprimento m�nimo da senha e presen�a de caracteres obrigat�rios
		return strlen($confirmacaoNovaSenha) >= 12
			&& preg_match('/[A-Z]/', $confirmacaoNovaSenha)   // Pelo menos uma letra mai�scula
			&& preg_match('/[0-9]/', $confirmacaoNovaSenha)   // Pelo menos um n�mero
			&& preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $confirmacaoNovaSenha);  // Pelo menos um caractere especial
			
	}
	
	// Fun��o para finalizar a opera��o com feedback positivo
	function redirecionaSucesso() {
		
		header('Location: sucesso.php');
		die();
		
	}
	
	
	// Fun��o para impedir o acesso n�o autorizado com feedback negativo
	function redirecionaAcessoNaoAutorizado() {
		
		header('Location: erro.php');
		die();
		
	}