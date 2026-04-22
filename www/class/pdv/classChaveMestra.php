<?php

require_once "/www/db/connect.php";
require_once "/www/db/ConnectionPDO.php";

require_once "/www/class/classSecureEncryption.php";
require_once "/www/includes/writeIfPossible.php";

class ChaveMestra
{

	private $conexao;

	public function __construct()
	{

		$conexao = ConnectionPDO::getConnection();
		$this->conexao = $conexao->getLink();
	}

	public function verificaSenha($usuario, $senha)
	{

		// Busca a chave armazenada para o usuário
		$sql = "select chave, chave_migrated from dist_usuarios_games_chave where usuario = :USUARIO;";
		$query = $this->conexao->prepare($sql);
		$query->bindParam(":USUARIO", $usuario);
		$query->execute();
		$rowChave = $query->fetch(PDO::FETCH_ASSOC);

		$quantidade = 0;

		$invalid = false;

		if ($rowChave) {
			$chaveArmazenada = $rowChave['chave'];
			// $isMigrated = $rowChave['chave_migrated'] ?: false;
			$isMigrated = (int)$rowChave['chave_migrated'] === 1;


			$secureEncryption = new SecureEncryption();

			// Verifica a senha usando o método apropriado
			if ($isMigrated) {
				// Chave já migrada para bcrypt
				if ($secureEncryption->verifyPassword($senha, $chaveArmazenada)) {
					$quantidade = 1;

					// Verifica se precisa re-hash (upgrade de custo)
					if ($secureEncryption->needsRehash($chaveArmazenada)) {
						$this->upgradeChaveMestraHash($usuario, $senha);
					}
				} else {
					$invalid = true;
				}
			} else {
				// Chave ainda no formato antigo
				if (trim($senha) == $chaveArmazenada) {
					$quantidade = 1;
					// Migra automaticamente para bcrypt
					$this->migrateChaveMestra($usuario, $senha);
				}
			}
		}

		$logLines = array(
			"data: " . date("d-m-Y H:s:s"),
			"usuario: " . $usuario,
			"quantidade: " . $quantidade,
		);
		if ($invalid) {
			$logLines[] = "resultado: SENHA_INVALIDA";
		}
		$logLines[] = str_repeat("*", 60);
		writeLinesIfPossible("/www/arquivos_gerados/logs/chave_mestra.txt", $logLines);

		return $quantidade;
	}

	private function getClientIP()
	{
		if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
			return $_SERVER['HTTP_CF_CONNECTING_IP']; // Cloudflare
		}

		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			// Pode ter múltiplos IPs: pega o primeiro
			$ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
			return trim($ips[0]);
		}

		if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
			return $_SERVER['HTTP_X_REAL_IP'];
		}

		return $_SERVER['REMOTE_ADDR']; // fallback
	}

	public function inserirSeguro($liberado, $usuario)
	{

		$sql = "select usuario from dist_usuarios_games_chave_seguro where usuario = :USUARIO and ip = :IP;";
		$query = $this->conexao->prepare($sql);
		$query->bindValue(":USUARIO", $usuario);
		$query->bindValue(":IP", $this->getClientIP());
		$query->execute();
		$rowSeguro = $query->fetch(PDO::FETCH_ASSOC);

		if ($rowSeguro == false) {

			$sql = "insert into dist_usuarios_games_chave_seguro(ip,liberado,usuario)values(:IP,:LIBERADO,:USUARIO);";
			$query = $this->conexao->prepare($sql);
			$query->bindValue(":IP", $this->getClientIP());
			$query->bindValue(":LIBERADO", $liberado);
			$query->bindValue(":USUARIO", $usuario);
			$query->execute();

			if ($query->rowCount() > 0) {
				return true;
			}

			return false;
		} else {
			return true;
		}
	}

	public function inserirChaveMestra($usuario)
	{

		$chave = $this->gerarSenha();
		$retorno = $this->verificaSenha($usuario, $chave);

		if ($retorno == 0) {

			$sql = "select usuario from dist_usuarios_games_chave where usuario = :USUARIO;";
			$query = $this->conexao->prepare($sql);
			$query->bindValue(":USUARIO", $usuario);
			$query->execute();
			$rowUsuario = $query->fetch(PDO::FETCH_ASSOC);

			if ($rowUsuario != false) {
				return false;
			} else {

				$sql = "insert into dist_usuarios_games_chave(usuario,chave)values(:USUARIO,:CHAVE);";
				$query = $this->conexao->prepare($sql);
				$query->bindValue(":USUARIO", $usuario);
				$query->bindValue(":CHAVE", $chave);
				$query->execute();

				if ($query->rowCount() > 0) {

					$sql = "select chave from dist_usuarios_games_chave where usuario = :USUARIO;";
					$query = $this->conexao->prepare($sql);
					$query->bindValue(":USUARIO", $usuario);
					$query->execute();
					$rowChave = $query->fetch(PDO::FETCH_ASSOC);

					return $rowChave["chave"];
				}

				return false;
			}
		}
	}

	private function verificaSeguro()
	{

		$sql = "SELECT liberado FROM dist_usuarios_games_chave_seguro where ip = :IP;";
		$query = $this->conexao->prepare($sql);
		$query->bindValue(":IP", $this->getClientIP());
		$query->execute();
		$rowIP = $query->fetch(PDO::FETCH_ASSOC);

		if ($rowIP != false) {
			if ($rowIP["liberado"] == "S") {
				return true;
			}
			return false;
		}

		return false;
	}

	private function gerarSenha()
	{

		$tamanho = 15;
		$posibilidades = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz@*{}";
		$chaveFinal = "";

		for ($num = 0; $num < $tamanho; $num++) {

			$letra = $posibilidades[rand(0, (strlen($posibilidades) - 1))];
			$chaveFinal .= $letra;
		}

		return $chaveFinal;
	}

	public function verificarIPUtilizado($usuario)
	{
		// Leva em consideraÃ§Ã£o a quatidade de utilizaÃ§Ã£o nos ultimos 7 dias ordernando pela maior utilizaÃ§Ã£o que tenha pedido vinculado
		$sql = "select count(*) as qtde, ugl_ip from dist_usuarios_games_log where ugl_ug_id = :USUARIO and ugl_data_inclusao >= (CURRENT_TIMESTAMP - INTERVAL '7 day') and ugl_uglt_id = 5 group by ugl_ip order by qtde desc limit 1;";
		$query = $this->conexao->prepare($sql);
		$query->bindValue(":USUARIO", $usuario);
		$query->execute();
		$rowIP = $query->fetch(PDO::FETCH_ASSOC);

		if ($rowIP != false) {
			if ($this->getClientIP() == $rowIP["ugl_ip"]) {

				return true;
			} else {
				return $this->verificaSeguro();
			}
		} else {
			return false;
		}
	}

	/**
	 * Migra a chave mestra do formato antigo para bcrypt
	 */
	private function migrateChaveMestra($usuario, $senhaPlaintext)
	{
		$secureEncryption = new SecureEncryption();
		$novoHash = $secureEncryption->hashPassword($senhaPlaintext);

		$sql = "UPDATE dist_usuarios_games_chave SET chave = :NOVO_HASH, chave_migrated = 1 WHERE usuario = :USUARIO";
		$query = $this->conexao->prepare($sql);
		$query->bindParam(":NOVO_HASH", $novoHash);
		$query->bindParam(":USUARIO", $usuario);
		$query->execute();

		// Log da migracao
		writeLinesIfPossible("/www/arquivos_gerados/logs/chave_mestra_migration.txt", array(
			"data: " . date("d-m-Y H:i:s"),
			"usuario: " . $usuario,
			"acao: migracao para bcrypt",
			str_repeat("*", 60),
		));
	}

	/**
	 * Atualiza o hash da chave mestra se necessário (upgrade de custo)
	 */
	private function upgradeChaveMestraHash($usuario, $senhaPlaintext)
	{
		$secureEncryption = new SecureEncryption();
		$novoHash = $secureEncryption->hashPassword($senhaPlaintext);

		$sql = "UPDATE dist_usuarios_games_chave SET chave = :NOVO_HASH WHERE usuario = :USUARIO";
		$query = $this->conexao->prepare($sql);
		$query->bindParam(":NOVO_HASH", $novoHash);
		$query->bindParam(":USUARIO", $usuario);
		$query->execute();

		// Log do upgrade
		writeLinesIfPossible("/www/log/chave_mestra_migration.txt", array(
			"data: " . date("d-m-Y H:i:s"),
			"usuario: " . $usuario,
			"acao: upgrade hash bcrypt",
			str_repeat("*", 60),
		));
	}
}
