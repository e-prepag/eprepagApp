<?php require_once __DIR__ . '/../../includes/constantes_url.php'; ?>
<?php

require_once "/www/class/classSecureEncryption.php";
class UsuarioGamesOperador {
    
    var $ugo_id;
    var $ugo_ug_id;
    var $ugo_sLogin;
    var $ugo_sSenha;
    var $ugo_blAtivo;
    var $ugo_dDataInclusao;
	var $ugo_dDataUltimoAcesso;
	var $ugo_iQtdeAcessos;    

    var $ugo_sNome;
    var $ugo_sEmail;

	public $ugo_tipo;


/*
    function UsuarioGamesOperador() {
    }
*/
    function UsuarioGamesOperador(	$ugo_id 		= null,		
							$ugo_ug_id 				= null,		
						    $ugo_sLogin 			= null,		
						    $ugo_sSenha 			= null,		
						    $ugo_blAtivo 			= null,		
						    $ugo_dDataInclusao 		= null,		
							$ugo_dDataUltimoAcesso	= null,	
							$ugo_iQtdeAcessos		= 0,		
						    $ugo_tipo	 			= 0,		
						
						    $ugo_sNome 				= null,		
						    $ugo_sEmail 			= null		
							){
    
	    $this->setId($ugo_id);
	    $this->setUgId($ugo_ug_id);
	    $this->setLogin($ugo_sLogin);
	    $this->setSenha($ugo_sSenha);
	    $this->setAtivo($ugo_blAtivo);
	    $this->setDataInclusao($ugo_dDataInclusao); 
		$this->setDataUltimoAcesso($ugo_dDataUltimoAcesso); 
		$this->setQtdeAcessos($ugo_iQtdeAcessos);
	    $this->setTipo($ugo_tipo);
	
	    $this->setNome($ugo_sNome);
	    $this->setEmail($ugo_sEmail);
//echo "ugo_dDataUltimoAcesso: $ugo_dDataUltimoAcesso<br>";	    
//echo "ugo_dDataInclusao: $ugo_dDataInclusao<br>";	    
    }
    
    
    function getId(){
    	return $this->ugo_id;
    }
    function setId($ugo_id){
    	$this->ugo_id = $ugo_id;
    }

    function getUgId(){
    	return $this->ugo_ug_id;
    }
    function setUgId($ugo_ug_id){
    	$this->ugo_ug_id = $ugo_ug_id;
    }

    function getLogin(){
    	return $this->ugo_sLogin;
    }
    function setLogin($ugo_sLogin){
    	$this->ugo_sLogin = $ugo_sLogin;
    }
    
    function getSenha(){
    	return $this->ugo_sSenha;
    }
    function setSenha($ugo_sSenha){
    	$this->ugo_sSenha = $ugo_sSenha;
    }
    
    function getAtivo(){
    	return $this->ugo_blAtivo;
    }
    function setAtivo($ugo_blAtivo){
		if(!is_null($ugo_blAtivo))
			if($ugo_blAtivo == 1 || $ugo_blAtivo == "1" || $ugo_blAtivo === "true") $ugo_blAtivo = 1;
			else $ugo_blAtivo = 0;
    	$this->ugo_blAtivo = $ugo_blAtivo;
    }
    
    function getDataInclusao(){
    	return $this->ugo_dDataInclusao;
    }
    function setDataInclusao($ugo_dDataInclusao){
    	$this->ugo_dDataInclusao = $ugo_dDataInclusao;
    }
    
    function getDataUltimoAcesso(){
    	return $this->ugo_dDataUltimoAcesso;
    }
    function setDataUltimoAcesso($ugo_dDataUltimoAcesso){
    	$this->ugo_dDataUltimoAcesso = $ugo_dDataUltimoAcesso;
    }
    
    function getQtdeAcessos(){
    	return $this->ugo_iQtdeAcessos;
    }
    function setQtdeAcessos($ugo_iQtdeAcessos){
    	$this->ugo_iQtdeAcessos = $ugo_iQtdeAcessos;
    }
    function getTipo(){
    	return $this->ugo_tipo;
    }
    function setTipo($ugo_tipo){
		if(!is_null($ugo_tipo))
			if($ugo_tipo != 1) $ugo_tipo = 0;
    	$this->ugo_tipo = $ugo_tipo;
    }

    
    function getNome(){
    	return $this->ugo_sNome;
    }
    function setNome($ugo_sNome){
    	$this->ugo_sNome = $ugo_sNome;
    }

	function getEmail(){
    	return $this->ugo_sEmail;
    }
    function setEmail($ugo_sEmail){
    	$this->ugo_sEmail = $ugo_sEmail;
    }
    
    

	private static function addParam(&$params, $value)
	{
		$params[] = $value;
		return '$' . count($params);
	}

	private static function orderByOperador($orderBy)
	{
		if (is_null($orderBy) || trim((string) $orderBy) == '') return null;
		$permitidos = array('ugo_id', 'ugo_ug_id', 'ugo_login', 'ugo_senha', 'ugo_ativo', 'ugo_data_inclusao', 'ugo_data_ultimo_acesso', 'ugo_qtde_acessos', 'ugo_tipo', 'ugo_nome', 'ugo_email');
		$partes = array();
		foreach (explode(',', $orderBy) as $parte) {
			$tokens = preg_split('/\s+/', trim($parte));
			$coluna = strtolower($tokens[0] ?? '');
			$direcao = strtoupper($tokens[1] ?? 'ASC');
			if (!in_array($coluna, $permitidos) || !in_array($direcao, array('ASC', 'DESC'))) return null;
			$partes[] = $coluna . ' ' . $direcao;
		}
		return count($partes) ? implode(', ', $partes) : null;
	}

    function inserir(&$objOperadorGamesUsuario){
        $server_url = "" . EPREPAG_URL . "";
            if(checkIP()) {
                $server_url = $_SERVER['SERVER_NAME'];
                }
 
 		$ret = UsuarioGamesOperador::validarCampos($objOperadorGamesUsuario, true);
 
 		if($ret == ""){
	 		if(UsuarioGamesOperador::existeLogin($objOperadorGamesUsuario->getLogin(), null, null )) {
	 			$ret = "Login ja cadastrado.";
	 		}
 		}
 		
 		if($ret == ""){
 			$objEncryption = new SecureEncryption();
 			$senha = $objEncryption->hashPassword(trim($objOperadorGamesUsuario->getSenha()));
			$qtdeAcessos = 0;
			$sql = "insert into dist_usuarios_games_operador(ugo_ug_id, ugo_login, ugo_senha, ugo_ativo, ugo_data_inclusao, ugo_data_ultimo_acesso, ugo_qtde_acessos, ugo_tipo, ugo_nome, ugo_email) values ($1, $2, $3, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, $4, $5, $6, $7)";
			$params = array($objOperadorGamesUsuario->getUgId(), trim(strtoupper($objOperadorGamesUsuario->getLogin())), $senha, $qtdeAcessos, $objOperadorGamesUsuario->getTipo(), trim(strtoupper($objOperadorGamesUsuario->getNome())), trim(strtoupper($objOperadorGamesUsuario->getEmail())));

			$ret = SQLexecuteQueryParams($sql, $params);
			if(!$ret) $ret = "Erro ao inserir operador.\n";
			else{
				$ret = "";				
				$rs_id = SQLexecuteQuery("select currval('dist_usuarios_games_operador_id_seq') as last_id");
				if($rs_id && pg_num_rows($rs_id) > 0){
					$rs_id_row = pg_fetch_array($rs_id);
					$objOperadorGamesUsuario->setId($rs_id_row['last_id']);
					usuarios_games_operador_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['CADASTRA_OPERADOR'], $objOperadorGamesUsuario->getUgId(), null);
					$parametros['prepag_dominio'] = "http://" . $server_url;
					$parametros['nome'] = $objOperadorGamesUsuario->getNome();

					$msgEmail  = email_cabecalho($parametros);
					$msgEmail .= "  <br><br>
									<table border='0' cellspacing='0'>
	            				<tr><td>&nbsp;</td></tr>
	            				<tr valign='middle' bgcolor='#FFFFFF'>
	            					<td align='left' class='texto'>
											Confirmamos a recepcao do seu cadastro de operador junto ao E-Prepag LanHouses. <br>
											Voce pode comecar a usar esse cadastro imediatamente.<br>
	            					</td>
	            				</tr>
	            				<tr><td>&nbsp;</td></tr>
	        					</table>
	        				";
					$msgEmail .= email_rodape($parametros);
					enviaEmail($objOperadorGamesUsuario->getEmail(), null, null, "E-Prepag - Cadastro de Operador de LanHouse", $msgEmail);
				}					
			}			
 		}
 		
 		return $ret;   	
    }
    
    function atualizar($objOperadorGamesUsuario){
                global $raiz_do_projeto;
 
 		$ret = UsuarioGamesOperador::validarCampos($objOperadorGamesUsuario, false);

 		if($ret == ""){
			$params = array();
			$sets = array();
			if(!is_null($objOperadorGamesUsuario->getAtivo())) $sets[] = "ugo_ativo = " . self::addParam($params, trim($objOperadorGamesUsuario->getAtivo()));
			if(!is_null($objOperadorGamesUsuario->getLogin())) $sets[] = "ugo_login = " . self::addParam($params, trim(strtoupper($objOperadorGamesUsuario->getLogin())));

 			if(!is_null($objOperadorGamesUsuario->getSenha())) 	{
	 			$objEncryption = new SecureEncryption();
 				$senha = $objEncryption->hashPassword(trim($objOperadorGamesUsuario->getSenha()));
				$sets[] = "ugo_senha = " . self::addParam($params, $senha);
			}

			if(!is_null($objOperadorGamesUsuario->getTipo())) $sets[] = "ugo_tipo = " . self::addParam($params, trim(strtoupper($objOperadorGamesUsuario->getTipo())));
			if(!is_null($objOperadorGamesUsuario->getNome())) $sets[] = "ugo_nome = " . self::addParam($params, trim(strtoupper($objOperadorGamesUsuario->getNome())));
			if(!is_null($objOperadorGamesUsuario->getEmail())) $sets[] = "ugo_email = " . self::addParam($params, trim(strtoupper($objOperadorGamesUsuario->getEmail())));

			$sql = "update dist_usuarios_games_operador set " . implode(", ", $sets);
			$sql .= " where ugo_id = " . self::addParam($params, $objOperadorGamesUsuario->getId());
			$sql .= " AND ugo_ug_id = " . self::addParam($params, $objOperadorGamesUsuario->getUgId());
			
			$ret = SQLexecuteQueryParams($sql, $params);
			if(!$ret) $ret = "Erro ao atualizar operador.\n";
			else {
                                $cmdtuples = pg_affected_rows($ret);
                                if($cmdtuples > 0) {
                                        $nome_tmp = $raiz_do_projeto."arquivos_gerados/logs/idsOpPDVs.txt";
                                        if ($handle = fopen($nome_tmp, 'a+')) {
                                                fwrite($handle, $objOperadorGamesUsuario->getId().PHP_EOL);
                                                fclose($handle);
                                        }
        				$ret = "";
                                } else {
                                        $ret = "Operador ou PDV nao cadastrados. Por favor, entre em contato com nossa Central de Atendimento atraves do e-mail suporte@e-prepag.com.br. Obrigado.";	
                                }
			}				

 		}
 		
 		return $ret;   	
    }
    
	function validarCamposLogin($senha, $senhaConf, $login){
//echo "validarCamposLogin('$senha', '$senhaConf', '$login')<br>"; 
		$ret = "";
		
		$senha = trim($senha);
		$senhaConf = trim($senhaConf);
		$login = trim($login);
		
		//Senha
		if(is_null($senha) || $senha == "") 				$ret .= "A Senha deve ser preenchida.\n";
		elseif(strlen($senha) < 10 || strlen($senha) > 35) 	$ret .= "A Senha deve ter entre 10 e 35 caracteres.\n";
 		
		//SenhaConf 		
		if($senha != $senhaConf) 							$ret .= "A confirmação da senha deve ser igual a senha.";
 		
		//login
 		if(is_null($login) || $login == "") 				$ret .= "O Login deve ser preenchido.\n";
		elseif(strlen($login) < 6 || strlen($login) > 100) $ret .= "O Login deve ter entre 6 e 100 caracteres.\n";

 		
 		return $ret;
	}
	
	function validarCampos($objOperadorGamesUsuario, $blCompleto){
	
		$tipoCadastro = '';

		$ret = "";
		
		//Dados do login
		if($blCompleto)
			$ret .= UsuarioGamesOperador::validarCamposLogin($objOperadorGamesUsuario->getSenha(), $objOperadorGamesUsuario->getSenha(), $objOperadorGamesUsuario->getLogin());
		
		//login
 		$login = $objOperadorGamesUsuario->getLogin();
 		if(!is_null($login) || $blCompleto){
	 		$login = trim($objOperadorGamesUsuario->getLogin());
	 		if(is_null($login) || $login == "") 				$ret .= "O Login deve ser preenchido.\n";
			elseif(strlen($login) < 6 || strlen($login) > 100) 	$ret .= "O Login deve ter entre 6 e 100 caracteres.\n";
		}
		
		
		//Nome
 		$nome = $objOperadorGamesUsuario->getNome();
 		if(!is_null($nome) || ($blCompleto && $tipoCadastro == 'PF')){
	 		$nome = trim($objOperadorGamesUsuario->getNome());
 			if($tipoCadastro == 'PF' && (is_null($nome) || $nome == "")) 	$ret .= "O Nome deve ser preenchido.\n";
 			elseif(strlen($nome) > 100) 		$ret .= "O Nome deve ter até 100 caracteres.\n";
 		}

		//Email
 		$email = $objOperadorGamesUsuario->getEmail();
 		if(!is_null($email) || $blCompleto){
	 		$email = trim($objOperadorGamesUsuario->getEmail());
	 		if(is_null($email) || $email == "") $ret .= "O Email deve ser preenchido.\n";
			elseif(strlen($email) > 100) 		$ret .= "O Email deve ter até 100 caracteres.\n";
			elseif(!verifica_email($email)) 	$ret .= "O Email é inválido.\n";
		}

 		return $ret;
	}


	function obter($filtro, $orderBy, &$rs){

		$ret = "";
		$params = array();
		$filtro = is_array($filtro) ? array_map("strtoupper", $filtro) : array();
		$filtro += array('ugo_id' => null, 'ugo_ug_id' => null, 'ugo_ativo' => null, 'ugo_data_inclusaoMin' => null, 'ugo_data_inclusaoMax' => null, 'ugo_data_ultimo_acessoMin' => null, 'ugo_data_ultimo_acessoMax' => null, 'ugo_qtde_acessosMin' => null, 'ugo_qtde_acessosMax' => null, 'ugo_login' => null, 'ugo_loginLike' => null, 'ugo_tipo' => null, 'ugo_nome' => null, 'ugo_nome_Like' => null, 'ugo_email' => null);
	
		$sql = "select * from dist_usuarios_games_operador ";

		if(!empty($filtro)){

			if(!is_null($filtro['ugo_data_inclusaoMin']) && !is_null($filtro['ugo_data_inclusaoMax'])){
				$filtro['ugo_data_inclusaoMin'] = formata_data_ts($filtro['ugo_data_inclusaoMin'] . " 00:00:00", 1, true, true);
				$filtro['ugo_data_inclusaoMax'] = formata_data_ts($filtro['ugo_data_inclusaoMax'] . " 23:59:59", 1, true, true);
			}			

			if(!is_null($filtro['ugo_data_ultimo_acessoMin']) && !is_null($filtro['ugo_data_ultimo_acessoMax'])){
				$filtro['ugo_data_ultimo_acessoMin'] = formata_data_ts($filtro['ugo_data_ultimo_acessoMin'] . " 00:00:00", 1, true, true);
				$filtro['ugo_data_ultimo_acessoMax'] = formata_data_ts($filtro['ugo_data_ultimo_acessoMax'] . " 23:59:59", 1, true, true);
			}

			$where = array();
			if(!is_null($filtro['ugo_id'])) $where[] = "ugo_id = " . self::addParam($params, $filtro['ugo_id']);
			if(!is_null($filtro['ugo_ug_id'])) $where[] = "ugo_ug_id = " . self::addParam($params, $filtro['ugo_ug_id']);
			if(!is_null($filtro['ugo_ativo'])) $where[] = "ugo_ativo = " . self::addParam($params, $filtro['ugo_ativo']);
			if(!is_null($filtro['ugo_data_inclusaoMin']) && !is_null($filtro['ugo_data_inclusaoMax'])) $where[] = "ugo_data_inclusao between " . self::addParam($params, $filtro['ugo_data_inclusaoMin']) . " and " . self::addParam($params, $filtro['ugo_data_inclusaoMax']);
			if(!is_null($filtro['ugo_data_ultimo_acessoMin']) && !is_null($filtro['ugo_data_ultimo_acessoMax'])) $where[] = "ugo_data_ultimo_acesso between " . self::addParam($params, $filtro['ugo_data_ultimo_acessoMin']) . " and " . self::addParam($params, $filtro['ugo_data_ultimo_acessoMax']);
			if(!is_null($filtro['ugo_qtde_acessosMin']) && !is_null($filtro['ugo_qtde_acessosMax'])) $where[] = "ugo_qtde_acessos between " . self::addParam($params, $filtro['ugo_qtde_acessosMin']) . " and " . self::addParam($params, $filtro['ugo_qtde_acessosMax']);
			if(!is_null($filtro['ugo_login'])) $where[] = "ugo_login = " . self::addParam($params, $filtro['ugo_login']);
			if(!is_null($filtro['ugo_loginLike'])) $where[] = "ugo_login like " . self::addParam($params, '%' . $filtro['ugo_loginLike'] . '%');
			if(!is_null($filtro['ugo_tipo'])) $where[] = "ugo_tipo = " . self::addParam($params, $filtro['ugo_tipo']);
			if(!is_null($filtro['ugo_nome'])) $where[] = "ugo_nome = " . self::addParam($params, $filtro['ugo_nome']);
			if(!is_null($filtro['ugo_nome_Like'])) $where[] = "ugo_nome like " . self::addParam($params, '%' . $filtro['ugo_nome_Like'] . '%');
			if(!is_null($filtro['ugo_email'])) {
				$where[] = "ugo_email = " . self::addParam($params, $filtro['ugo_email']);
				$where[] = "ugo_email like " . self::addParam($params, '%' . $filtro['ugo_email'] . '%');
			}
			if (count($where)) $sql .= " where " . implode(" and ", $where);
		}
		
		$order = self::orderByOperador($orderBy);
		if($order) $sql .= " order by " . $order;

		$rs = count($params) ? SQLexecuteQueryParams($sql, $params) : SQLexecuteQuery($sql);
		if(!$rs) $ret = "Erro ao obter operador(s).\n";

		return $ret;

	}

	// O login e unico nas tabelas dist_usuarios_games_operador e dist_usuarios_games (leva em conta ate cadastros que nao estao mais ativos)
	// os campos $usuario_id_excessao, $usuario_id_lanhouse nao sao usados mais
    function existeLogin($login, $usuario_id_excessao, $usuario_id_lanhouse){

		$ret = true;
		$qtde_01 = 0;
		$qtde_02 = 0;
		$login = strtoupper(trim($login));

		$sql = "select count(*) as qtde from dist_usuarios_games_operador where ugo_login = $1";
		$rs = SQLexecuteQueryParams($sql, array($login));
		if($rs && pg_num_rows($rs) > 0){
			$rs_row = pg_fetch_array($rs);
			$qtde_01 = $rs_row['qtde'];
		}			

		if($qtde_01==0) {
			$sql = "select count(*) as qtde from dist_usuarios_games where ug_login = $1";
			$rs = SQLexecuteQueryParams($sql, array($login));
			if($rs && pg_num_rows($rs) > 0){
				$rs_row = pg_fetch_array($rs);
				$qtde_02 = $rs_row['qtde'];
			}			
		}

		if (($qtde_01 + $qtde_02)==0) $ret = false;
		return $ret;   	
    }
    
    function autenticarLogin($login, $senha, $aut = false) { //Autentica usuario

		$ret = false;
		$login = strtoupper(trim($login));
		$senhaOriginal = trim($senha);

		// Carrega as classes de criptografia
		$secureEncryption = new SecureEncryption();

		$con = ConnectionPDO::getConnection();
		$pdo = $con->getLink();

		// Busca o operador e sua senha atual
		$sqlUser = "SELECT ugo_id, ugo_ug_id, ugo_senha, ugo_senha_migrated FROM dist_usuarios_games_operador 
					WHERE ugo_ativo = 1 AND ugo_login = ?";
		$stmtUser = $pdo->prepare($sqlUser);
		$stmtUser->execute(array($login));
		$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

		if (!$user) {
			return false; // Operador não encontrado
		}

		$senhaHash = $user['ugo_senha'];
		$isMigrated = $user['ugo_senha_migrated'] ?: false;
		$ugo_ug_id = $user['ugo_ug_id'];

		// Verifica a senha usando o método apropriado
		if ($isMigrated) {
			// Senha já migrada para bcrypt
			$ret = $secureEncryption->verifyPassword($senhaOriginal, $senhaHash);
			
			// Verifica se precisa re-hash (upgrade de custo)
			if ($ret && $secureEncryption->needsRehash($senhaHash)) {
				$this->upgradeOperatorPasswordHash($user['ugo_id'], $senhaOriginal);
			}
		} else {
			// Senha ainda no formato antigo, tenta verificar
			$ret = $secureEncryption->verifyPassword($senhaOriginal, $senhaHash);
			
			// Se a verificação passou, migra automaticamente para bcrypt
			if ($ret) {
				$this->migrateOperatorPassword($user['ugo_id'], $senhaOriginal);
			}
		}

                $instUsuarioGames = new UsuarioGames;
		$objGamesUsuario = $instUsuarioGames->getUsuarioGamesById($ugo_ug_id);
		if($objGamesUsuario != null) {
			$ug_ativo = $objGamesUsuario->getAtivo();
                        $ug_substatus = $objGamesUsuario->getSubstatus();
		}
		
//echo "ug_ativo: ".$ug_ativo."<br>";
                
		if($ug_ativo==1 && $ug_substatus==11 || $ug_ativo==1 && $ug_substatus==9) {
			$sql = "select count(*) as qtde from dist_usuarios_games_operador where ugo_ativo = 1 and ugo_login = $1";
			$rs = SQLexecuteQueryParams($sql, array($login));
			if($rs && pg_num_rows($rs) > 0){
				$rs_row = pg_fetch_array($rs);
				if($rs_row['qtde'] > 0) $ret = true;
//echo "rs_row['qtde']: ".$rs_row['qtde']."<br>";
//echo "ret1: ".(($ret)?"ret OK":"Not ret")."<br>";
			}			

			//Adiciona objeto usuario no session
			if($ret){
                                $ret = UsuarioGamesOperador::adicionarLoginSession($login, $ugo_ug_id); 
			}
//echo "ret2: ".(($ret)?"ret OK":"Not ret")."<br>";
//die("Para 3223");

			//Atualiza ultimo acesso
			//------------------------------------------------------------------
			if($ret){
				UsuarioGamesOperador::atualiza_ultimo_acesso($login);
				//Log na base
				$obs = "";
            	if($aut == true){
            	    $obs = "Login com autenticador";
            	} else{
            	    $obs = "Login sem autenticador";
				usuarios_games_operador_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['LOGIN_OPERADOR'], null, null, $obs);
				}
				
			}
		} 

//echo "ret3: ".(($ret)?"ret OK":"Not ret")."<br>";
 		return $ret;   	
    }
    

    function LoginAutomatico($ugo_id,$login, $aut = false) { //Autentica usuario

		$ret = false;
		$sql = "select ugo_ug_id from dist_usuarios_games_operador where ugo_ativo = 1 and ugo_id = $1";
                $rs_id = SQLexecuteQueryParams($sql, array($ugo_id));
		if($rs_id && pg_num_rows($rs_id) > 0){
			$rs_id_row = pg_fetch_array($rs_id);
			$ugo_ug_id = $rs_id_row['ugo_ug_id'];
		}			

		$instanceClassUsuario = new UsuarioGames();

		$objGamesUsuario = $instanceClassUsuario->getUsuarioGamesById($ugo_ug_id);
		if($objGamesUsuario != null) {
			$ug_ativo = $objGamesUsuario->getAtivo();
                        $ug_substatus = $objGamesUsuario->getSubstatus();
		}

		if($ug_ativo==1 && $ug_substatus==11 || $ug_ativo==1 && $ug_substatus==9) {
			$sql = "select count(*) as qtde from dist_usuarios_games_operador where ugo_ativo = 1 and ugo_id = $1";
                        $rs = SQLexecuteQueryParams($sql, array($ugo_id));
			if($rs && pg_num_rows($rs) > 0){
				$rs_row = pg_fetch_array($rs);
				if($rs_row['qtde'] > 0) $ret = true;
			}			

			if($ret){
		                $ret = UsuarioGamesOperador::adicionarLoginSession($login, $ugo_ug_id); 
     			}

			if($ret){
				UsuarioGamesOperador::atualiza_ultimo_acesso($login);
				$obs = "";
            	if($aut == true){
            	    $obs = "Login com autenticador";
            	} else{
            	    $obs = "Login sem autenticador";
            	}
				usuarios_games_operador_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['LOGIN_OPERADOR'], null, null, $obs);
				
			}
		} 

 		return $ret;   	
    }
    
    function atualiza_ultimo_acesso($login) {
            if($login){
                    $sql = "update dist_usuarios_games_operador set ugo_data_ultimo_acesso = CURRENT_TIMESTAMP, ugo_qtde_acessos = ugo_qtde_acessos + 1 where ugo_login = $1";
                    $rs = SQLexecuteQueryParams($sql, array($login));			
            }
    }//end function atualiza_ultimo_acesso

    function getUsuarioGamesOperadorById($usuario_id){

		if(!$usuario_id || $usuario_id == "" || !is_numeric($usuario_id)) return null;
		
		$rs = null;
		$filtro['ugo_id'] = $usuario_id;
		//$filtro['ugo_ativo'] = 1;
		$ret = UsuarioGamesOperador::obter($filtro, null, $rs);
		
		return UsuarioGamesOperador::create($rs);
		
    }

    function getUsuarioGamesOperadorByLogin($login){

		if(!$login || $login == "") return null;
		
		$rs = null;
		$filtro['ugo_login'] = $login;
		//$filtro['ugo_ativo'] = 1;
		$ret = UsuarioGamesOperador::obter($filtro, null, $rs);
		
		return UsuarioGamesOperador::create($rs);
		
    }


    function adicionarLoginSession($login, $ugo_ug_id){ 
     
		if(!$login || $login == "") return false;
		
		$rs = null;
		$filtro['ugo_login'] = $login;
                $filtro['ugo_ug_id'] = $ugo_ug_id; 
		$filtro['ugo_ativo'] = 1;
		$ret = UsuarioGamesOperador::obter($filtro, null, $rs);
		$UsuarioGamesOperador = UsuarioGamesOperador::create($rs);

//echo "UsuarioGamesOperador->getUgId():".$UsuarioGamesOperador->getUgId()."<br>";
//dumpclass("Em adicionarLoginSession()")."<br>";

		if($UsuarioGamesOperador != null){
			$ret = true;

			//Poe no session				
			$ug_id = $UsuarioGamesOperador->getUgId();
                        $instUsuarioGames = new UsuarioGames;
			$usuarioGames = $instUsuarioGames->getUsuarioGamesById($ug_id);
			$ug_tipo = $UsuarioGamesOperador->getTipo();

			$_SESSION['dist_usuarioGames_ser'] = serialize($usuarioGames);
			$_SESSION['dist_usuarioGames.horarioLogin'] = date("U");
			$_SESSION['dist_usuarioGames.horarioInatividade'] = date("U");
//echo "<pre>";
//print_r($UsuarioGamesOperador);
//echo "</pre>";

			$_SESSION['dist_usuarioGamesOperador_ser'] = serialize($UsuarioGamesOperador);
			$_SESSION['dist_usuarioGamesOperador.horarioLogin'] = date("U");
			$_SESSION['dist_usuarioGamesOperador.horarioInatividade'] = date("U");

			$_SESSION['dist_usuarioGamesOperadorTipo_ser'] = $ug_tipo;

		} else {
			$ret = false;
		}

		return $ret;
    }

    function create($rs){

		$UsuarioGamesOperador = null;

		if($rs && pg_num_rows($rs) > 0){
			$rs_row = pg_fetch_array($rs);

			$UsuarioGamesOperador = new UsuarioGamesOperador();
			$UsuarioGamesOperador->setId($rs_row['ugo_id']);
			$UsuarioGamesOperador->setUgId($rs_row['ugo_ug_id']);
			$UsuarioGamesOperador->setLogin($rs_row['ugo_login']);
			$UsuarioGamesOperador->setSenha($rs_row['ugo_senha']);
			$UsuarioGamesOperador->setAtivo($rs_row['ugo_ativo']);
			$UsuarioGamesOperador->setDataInclusao(formata_data_ts($rs_row['ugo_data_inclusao'], 0, true, false));
			$UsuarioGamesOperador->setDataUltimoAcesso(formata_data_ts($rs_row['ugo_data_ultimo_acesso'], 0, true, false));
			$UsuarioGamesOperador->setQtdeAcessos($rs_row['ugo_qtde_acessos']);

			$UsuarioGamesOperador->setTipo($rs_row['ugo_tipo']);
			$UsuarioGamesOperador->setNome($rs_row['ugo_nome']);
			$UsuarioGamesOperador->setEmail($rs_row['ugo_email']);

		}
		
		return $UsuarioGamesOperador;
    }
    
    
    function alterarSenha($senha, $senhaAtual, $login){
        $server_url = "" . EPREPAG_URL . "";
            if(checkIP()) {
                $server_url = $_SERVER['SERVER_NAME'];
                }
		$ret = false;
		$login = strtoupper(trim($login));
		$senhaAtualOriginal = trim($senhaAtual);
		$novaSenhaOriginal = trim($senha);

		// Carrega as classes de criptografia
		$secureEncryption = new SecureEncryption();

		$con = ConnectionPDO::getConnection();
		$pdo = $con->getLink();

		// Busca o operador e sua senha atual
		$sqlUser = "SELECT ugo_id, ugo_senha, ugo_senha_migrated FROM dist_usuarios_games_operador WHERE ugo_login = ?";
		$stmtUser = $pdo->prepare($sqlUser);
		$stmtUser->execute(array($login));
		$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

		if (!$user) {
			return false; // Operador não encontrado
		}

		$senhaHashAtual = $user['ugo_senha'];
		$isMigrated = $user['ugo_senha_migrated'] ?: false;

		// Verifica a senha atual usando o método apropriado
		if ($isMigrated) {
			// Senha já migrada para bcrypt
			$ret = $secureEncryption->verifyPassword($senhaAtualOriginal, $senhaHashAtual);
		} else {
			// Senha ainda no formato antigo
			$ret = $secureEncryption->verifyPassword($senhaAtualOriginal, $senhaHashAtual);
		}

		//Atualiza senha
		//------------------------------------------------------------------
		if($ret){
			// Gera o hash bcrypt da nova senha
			$novoHashSenha = $secureEncryption->hashPassword($novaSenhaOriginal);
			
			// Atualiza a senha usando PDO
			$sqlUpdate = "UPDATE dist_usuarios_games_operador SET 
						 ugo_senha = ?, 
						 ugo_senha_migrated = 1
						 WHERE ugo_id = ?";
			
			$stmtUpdate = $pdo->prepare($sqlUpdate);
			$ret = $stmtUpdate->execute(array($novoHashSenha, $user['ugo_id']));
			
			if($ret){
				
				//Log na base
				usuarios_games_operador_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['TROCA_DE_SENHA_OPERADOR'], null, null);
				
				//Envia email
				//--------------------------------------------------------------------------------
				$objOperadorGamesUsuario = unserialize($_SESSION['dist_usuarioGamesOperador_ser']);
				$parametros['prepag_dominio'] = "http://" . $server_url;
				$parametros['nome_fantasia'] = $objOperadorGamesUsuario->getNomefantasia();
				$parametros['tipo_cadastro'] = $objOperadorGamesUsuario->getTipoCadastro();
				$parametros['nome'] = $objOperadorGamesUsuario->getNome();
				$parametros['sexo'] = $objOperadorGamesUsuario->getSexo();
				
				$msgEmail  = email_cabecalho($parametros);
				$msgEmail .= "  <br><br>
								<table border='0' cellspacing='0'>
	            				<tr><td>&nbsp;</td></tr>
	            				<tr valign='middle' bgcolor='#FFFFFF'>
	            					<td align='left' class='texto'>
										Você acessou nosso site e alterou sua senha.<br><br>
										Utilize seu login " . $objOperadorGamesUsuario->getLogin() . " para acessar sua conta e realizar compras em nosso site.<br><br>
	            					</td>
	            				</tr>
	            				<tr><td>&nbsp;</td></tr>
	        					</table>
	        				";
				$msgEmail .= email_rodape($parametros);
				enviaEmail($objOperadorGamesUsuario->getEmail(), null, null, "E-Prepag - Alteração de Senha Operador", $msgEmail);
				
			}
		}

 		return $ret;   	
    }
 
    function enviaEmailAtivacao($usuario_id){
        $server_url = "" . EPREPAG_URL . "";
            if(checkIP()) {
                $server_url = $_SERVER['SERVER_NAME'];
                }
		$ret = "";
		
		$objEncryption = new Encryption();
		$objOperadorGamesUsuario = UsuarioGamesOperador::getUsuarioGamesOperadorById($usuario_id);

		if($objOperadorGamesUsuario == null){
			$ret = "Não foi possível enviar email de ativação de cadastro. Operador não encontrado.\n";
			return $ret;
		} 
		

		//Envia email
		//--------------------------------------------------------------------------------
		$parametros['prepag_dominio'] = "http://" . $server_url;
		$parametros['nome_fantasia'] = "";
		$parametros['tipo_cadastro'] = "";
		$parametros['nome'] = "";
		$parametros['sexo'] = "";
		
		$msgEmail  = email_cabecalho($parametros);
		$msgEmail .= "  <br><br>
						<table border='0' cellspacing='0'>
        				<tr><td>&nbsp;</td></tr>
        				<tr valign='middle' bgcolor='#FFFFFF'>
        					<td align='left' class='texto'>
								Informamos que o seu cadastro junto ao E-PREPAG LanHouses foi aprovado.<br><br>
								Para acessar a sua área de trabalho, utilize o login e senha que você criou no momento do cadastro.<br><br>
								Na sua área de trabalho, dentre outras coisas, é possível fazer compras, acompanhar a situação os seus pedidos e fazer a impressão dos cupons.<br><br>
								Acesse agora mesmo e já faça a sua primeira compra, é fácil e rápido!<br><br>
								<b>Login:</b> " . $objOperadorGamesUsuario->getLogin() . "<br>
        					</td>
        				</tr>
        				<tr><td>&nbsp;</td></tr>
    					</table>
    				";
		$msgEmail .= email_rodape($parametros);
		enviaEmail($objOperadorGamesUsuario->getEmail(), null, null, "E-Prepag - Cadastro Aprovado", $msgEmail);
				
 		return $ret;   	
    }

    function dumpclass($msg){
		$sret = "Msg: ".$msg."<br>";
		$sret .= $this->getUgId()."<br>";
/*		$sret .= $this->getugo_ug_id."<br>";
		$sret .= $ugo_sLogin."<br>";
		$sret .= $ugo_sSenha."<br>";
		$sret .= $ugo_blAtivo."<br>";
		$sret .= $ugo_dDataInclusao."<br>";
		$sret .= $ugo_dDataUltimoAcesso."<br>";
		$sret .= $ugo_iQtdeAcessos."<br>";
		$sret .= $ugo_tipo."<br>";

		$sret .= $ugo_sNome."<br>";
		$sret .= $ugo_sEmail."<br>";
*/
		return $sret;

	}

	/**
	 * Migra a senha de um operador para bcrypt
	 */
	private function migrateOperatorPassword($operatorId, $senhaOriginal) {
		$secureEncryption = new SecureEncryption();
		$novoHash = $secureEncryption->hashPassword($senhaOriginal);
		
		$con = ConnectionPDO::getConnection();
		$pdo = $con->getLink();
		
		$sql = "UPDATE dist_usuarios_games_operador SET ugo_senha = ?, ugo_senha_migrated = 1 WHERE ugo_id = ?";
		$stmt = $pdo->prepare($sql);
		$stmt->execute(array($novoHash, $operatorId));
	}

	/**
	 * Atualiza o hash da senha de um operador para um custo mais alto se necessário
	 */
	private function upgradeOperatorPasswordHash($operatorId, $senhaOriginal) {
		$secureEncryption = new SecureEncryption();
		$novoHash = $secureEncryption->hashPassword($senhaOriginal);
		
		$con = ConnectionPDO::getConnection();
		$pdo = $con->getLink();
		
		$sql = "UPDATE dist_usuarios_games_operador SET ugo_senha = ? WHERE ugo_id = ?";
		$stmt = $pdo->prepare($sql);
		$stmt->execute(array($novoHash, $operatorId));
	}
}

?>
