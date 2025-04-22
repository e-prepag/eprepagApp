<?php
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
    
    
    function inserir(&$objOperadorGamesUsuario){
        $server_url = "www.e-prepag.com.br";
            if(checkIP()) {
                $server_url = $_SERVER['SERVER_NAME'];
                }
 
 		$ret = UsuarioGamesOperador::validarCampos($objOperadorGamesUsuario, true);
 
 		if($ret == ""){
	 		if(UsuarioGamesOperador::existeLogin($objOperadorGamesUsuario->getLogin(), null, null )) {	// $objOperadorGamesUsuario->getUgId()
	 			$ret = "Login já cadastrado.";
	 		}
 		}
 		
 		if($ret == ""){
 			
 			//Formata
 			$objEncryption = new Encryption();
 			$senha = $objEncryption->encrypt(trim($objOperadorGamesUsuario->getSenha()));
			$dataInclusao = "CURRENT_TIMESTAMP";
			$dataUltimoAcesso = "CURRENT_TIMESTAMP";
 			$qtdeAcessos = 0;
			
			//SQL
 			$sql = "insert into dist_usuarios_games_operador(ugo_ug_id, ugo_login, ugo_senha, ugo_ativo, ugo_data_inclusao, 
 						ugo_data_ultimo_acesso, ugo_qtde_acessos, ugo_tipo, ugo_nome, ugo_email
					) values (";

 			$sql .= SQLaddFields($objOperadorGamesUsuario->getUgId(), "") . ",";
 			$sql .= SQLaddFields(trim(strtoupper($objOperadorGamesUsuario->getLogin())), "s") . ",";
 			$sql .= SQLaddFields($senha, "s") . ",";
 			$sql .= "1,";
 			$sql .= SQLaddFields($dataInclusao, "") . ",";
 			$sql .= SQLaddFields($dataUltimoAcesso, "") . ",";
 			$sql .= SQLaddFields($qtdeAcessos, "") . ",";
 			$sql .= SQLaddFields($objOperadorGamesUsuario->getTipo(), "s") . ",";

 			$sql .= SQLaddFields(trim(strtoupper($objOperadorGamesUsuario->getNome())), "s") . ",";
 			$sql .= SQLaddFields(trim(strtoupper($objOperadorGamesUsuario->getEmail())), "s") . ")";
//echo "sql: $sql<br>";

			$ret = SQLexecuteQuery($sql);
			if(!$ret) $ret = "Erro ao inserir operador.\n<!--\n".$sql."\n-->";
			else{
				$ret = "";				
				$rs_id = SQLexecuteQuery("select currval('dist_usuarios_games_operador_id_seq') as last_id");
				if($rs_id && pg_num_rows($rs_id) > 0){
					$rs_id_row = pg_fetch_array($rs_id);
					$objOperadorGamesUsuario->setId($rs_id_row['last_id']);
					
					//Log na base
					usuarios_games_operador_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['CADASTRA_OPERADOR'], $objOperadorGamesUsuario->getUgId(), null);
					
					//Envia email
					//--------------------------------------------------------------------------------
					$parametros['prepag_dominio'] = "http://" . $server_url;
					$parametros['nome'] = $objOperadorGamesUsuario->getNome();

					$msgEmail  = email_cabecalho($parametros);
					$msgEmail .= "  <br><br>
									<table border='0' cellspacing='0'>
		            				<tr><td>&nbsp;</td></tr>
		            				<tr valign='middle' bgcolor='#FFFFFF'>
		            					<td align='left' class='texto'>
											Confirmamos a recepção do seu cadastro de operador junto ao E-Prepag LanHouses. <br>
											Você pode começar a usar esse cadastro imediatamente.<br>
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
			//SQL
 			$sql = "update dist_usuarios_games_operador set ";
 			if(!is_null($objOperadorGamesUsuario->getAtivo())) 			$sql .= " ugo_ativo = " 				. SQLaddFields(trim($objOperadorGamesUsuario->getAtivo()), "") . ",";
 			if(!is_null($objOperadorGamesUsuario->getLogin())) 			$sql .= " ugo_login = " 				. SQLaddFields(trim(strtoupper($objOperadorGamesUsuario->getLogin())), "s") . ",";

 			if(!is_null($objOperadorGamesUsuario->getSenha())) 	{
	 			$objEncryption = new Encryption();
 				$senha = $objEncryption->encrypt(trim($objOperadorGamesUsuario->getSenha()));
				$sql .= " ugo_senha = '". $senha . "',";
			}

			if(!is_null($objOperadorGamesUsuario->getTipo())) 			$sql .= " ugo_tipo = " 				. SQLaddFields(trim(strtoupper($objOperadorGamesUsuario->getTipo())), "s") . ",";
			if(!is_null($objOperadorGamesUsuario->getNome())) 			$sql .= " ugo_nome = " 				. SQLaddFields(trim(strtoupper($objOperadorGamesUsuario->getNome())), "s") . ",";
			if(!is_null($objOperadorGamesUsuario->getEmail())) 			$sql .= " ugo_email = " 				. SQLaddFields(trim(strtoupper($objOperadorGamesUsuario->getEmail())), "s") . ",";

			if(substr($sql, -1) == ",") $sql = substr($sql, 0, strlen($sql) - 1);
			$sql .= " where ugo_id = " . SQLaddFields($objOperadorGamesUsuario->getId(), "");
			$sql .= " AND ugo_ug_id = " . SQLaddFields($objOperadorGamesUsuario->getUgId(), "");
			
			$ret = SQLexecuteQuery($sql);
//echo "sql: $sql<br>";
//die("");
			if(!$ret) $ret = "Erro ao atualizar operador.\n";
			else {
                                $cmdtuples = pg_affected_rows($ret);
                                //echo $cmdtuples . " tuples are affected.<br>\n";
                                if($cmdtuples > 0) {
                                    
                                        //Grava no arquivo o ID do PDV para Exclusão de todas as Sessões abertas
                                        $nome_tmp = $raiz_do_projeto."log/idsOpPDVs.txt";
                                        if ($handle = fopen($nome_tmp, 'a+')) {
                                                fwrite($handle, $objOperadorGamesUsuario->getId().PHP_EOL);
                                                fclose($handle);
                                        }//end if ($handle = fopen($nome_tmp, 'a+'))
                                        
        				$ret = "";
                                } else {
                                        $ret = "Operador ou PDV não cadastrados. Por favor, entre em contato com nossa Central de Atendimento através do e-mail suporte@e-prepag.com.br. Obrigado.";	
                                }
/*
				//Log na base
				usuarios_games_operador_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['ALTERACAO_DO_CADASTRO'], null, null);

				$objOperadorGamesUsuario = unserialize($_SESSION['dist_usuarioGamesOperador_ser']);
				if(is_object($objOperadorGamesUsuario))	{
					//Envia email
					//--------------------------------------------------------------------------------
					$parametros['prepag_dominio'] = "http://www.e-prepag.com.br";
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
											Você acessou nosso site e alterou seu cadastro.<br><br>
											Utilize seu login " . $objOperadorGamesUsuario->getLogin() . " para acessar sua conta e realizar compras em nosso site.
										</td>
									</tr>
									<tr><td>&nbsp;</td></tr>
									</table>
								";
					$msgEmail .= email_rodape($parametros);
					if(!is_null($objOperadorGamesUsuario->getEmail())) enviaEmail($objOperadorGamesUsuario->getEmail(), null, null, "E-Prepag - Atualização de Cadastro", $msgEmail);
				}
*/
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
		elseif(strlen($senha) < 6 || strlen($senha) > 15) 	$ret .= "A Senha deve ter entre 6 e 15 caracteres.\n";
 		
		//SenhaConf 		
		if($senha != $senhaConf) 							$ret .= "A confirmação da senha deve ser igual a senha.";
 		
		//login
 		if(is_null($login) || $login == "") 				$ret .= "O Login deve ser preenchido.\n";
		elseif(strlen($login) < 6 || strlen($login) > 100) $ret .= "O Login deve ter entre 6 e 100 caracteres.\n";

 		
 		return $ret;
	}
	
	function validarCampos($objOperadorGamesUsuario, $blCompleto){
		
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
		$filtro = array_map("strtoupper", $filtro);
	
		$sql = "select * from dist_usuarios_games_operador ";

		if(!is_null($filtro) && $filtro != ""){

			if(!is_null($filtro['ugo_data_inclusaoMin']) && !is_null($filtro['ugo_data_inclusaoMax'])){
				$filtro['ugo_data_inclusaoMin'] = formata_data_ts($filtro['ugo_data_inclusaoMin'] . " 00:00:00", 1, true, true);
				$filtro['ugo_data_inclusaoMax'] = formata_data_ts($filtro['ugo_data_inclusaoMax'] . " 23:59:59", 1, true, true);
			}			

			if(!is_null($filtro['ugo_data_ultimo_acessoMin']) && !is_null($filtro['ugo_data_ultimo_acessoMax'])){
				$filtro['ugo_data_ultimo_acessoMin'] = formata_data_ts($filtro['ugo_data_ultimo_acessoMin'] . " 00:00:00", 1, true, true);
				$filtro['ugo_data_ultimo_acessoMax'] = formata_data_ts($filtro['ugo_data_ultimo_acessoMax'] . " 23:59:59", 1, true, true);
			}			


			$sql .= " where 1=1";
			
			$sql .= " and (" . (is_null($filtro['ugo_id'])?1:0);
			$sql .= "=1 or ugo_id = " . SQLaddFields($filtro['ugo_id'], "") . ")";

                        $sql .= " and (" . (is_null($filtro['ugo_ug_id'])?1:0);
                        $sql .= "=1 or ugo_ug_id = " . SQLaddFields($filtro['ugo_ug_id'], "") . ")";

			$sql .= " and (" . (is_null($filtro['ugo_ativo'])?1:0);
			$sql .= "=1 or ugo_ativo = " . SQLaddFields($filtro['ugo_ativo'], "") . ")";

			$sql .= " and (" . (is_null($filtro['ugo_data_inclusaoMin']) || is_null($filtro['ugo_data_inclusaoMax'])?1:0);
			$sql .= "=1 or ugo_data_inclusao between " . SQLaddFields($filtro['ugo_data_inclusaoMin'], "") . " and " . SQLaddFields($filtro['ugo_data_inclusaoMax'], "") . ")";

			$sql .= " and (" . (is_null($filtro['ugo_data_ultimo_acessoMin']) || is_null($filtro['ugo_data_ultimo_acessoMax'])?1:0);
			$sql .= "=1 or ugo_data_ultimo_acesso between " . SQLaddFields($filtro['ugo_data_ultimo_acessoMin'], "") . " and " . SQLaddFields($filtro['ugo_data_ultimo_acessoMax'], "") . ")";

			$sql .= " and (" . (is_null($filtro['ugo_qtde_acessosMin']) || is_null($filtro['ugo_qtde_acessosMax'])?1:0);
			$sql .= "=1 or ugo_qtde_acessos between " . SQLaddFields($filtro['ugo_qtde_acessosMin'], "") . " and " . SQLaddFields($filtro['ugo_qtde_acessosMax'], "") . ")";



			$sql .= " and (" . (is_null($filtro['ugo_login'])?1:0);
			$sql .= "=1 or ugo_login = '" . SQLaddFields($filtro['ugo_login'], "r") . "')";
			$sql .= " and (" . (is_null($filtro['ugo_loginLike'])?1:0);
			$sql .= "=1 or ugo_login like '%" . SQLaddFields($filtro['ugo_loginLike'], "r") . "%')";
			

			$sql .= " and (" . (is_null($filtro['ugo_tipo'])?1:0);
			$sql .= "=1 or ugo_tipo = " . SQLaddFields($filtro['ugo_tipo'], "") . ")";

			$sql .= " and (" . (is_null($filtro['ugo_nome'])?1:0);
			$sql .= "=1 or ugo_nome = '" . SQLaddFields($filtro['ugo_nome'], "r") . "')";
			$sql .= " and (" . (is_null($filtro['ugo_nome_Like'])?1:0);
			$sql .= "=1 or ugo_nome like '%" . SQLaddFields($filtro['ugo_nome_Like'], "r") . "%')";

			$sql .= " and (" . (is_null($filtro['ugo_email'])?1:0);
			$sql .= "=1 or ugo_email = '" . SQLaddFields($filtro['ugo_email'], "r") . "')";
			$sql .= " and (" . (is_null($filtro['ugo_email'])?1:0);
			$sql .= "=1 or ugo_email like '%" . SQLaddFields($filtro['ugo_email'], "r") . "%')";

		}
		
		if(!is_null($orderBy)) $sql .= " order by " . $orderBy;

//echo "sql: $sql<br>";

		$rs = SQLexecuteQuery($sql);
		if(!$rs) $ret = "Erro ao obter operador(s).\n";

		return $ret;

	}

	// O login é unico nas tabelas dist_usuarios_games_operador e dist_usuarios_games (leva em conta até cadastros que não estão mais ativos)
	// os campos $usuario_id_excessao, $usuario_id_lanhouse não são usados mais
    function existeLogin($login, $usuario_id_excessao, $usuario_id_lanhouse){

		$ret = true;
		$qtde_01 = 0;
		$qtde_02 = 0;
		$login = strtoupper(trim($login));

		//SQL
		$sql = "select count(*) as qtde from dist_usuarios_games_operador ";
		$sql .= " where ugo_login = " . SQLaddFields($login, "s");
//		$sql .= " and ugo_ug_id = " . SQLaddFields(trim($usuario_id_lanhouse), "");
//		if($usuario_id_excessao && !is_null($usuario_id_excessao) && is_numeric($usuario_id_excessao))
//			$sql .= " and ugo_id = " . SQLaddFields(trim($usuario_id_excessao), "");

//echo "ret: ".(($ret)?"True":"False")."<br>";
//echo "sql1: $sql<br>";
		$rs = SQLexecuteQuery($sql);
		if($rs && pg_num_rows($rs) > 0){
			$rs_row = pg_fetch_array($rs);
			$qtde_01 = $rs_row['qtde'];
		}			

//echo "ret: ".(($ret)?"True":"False")."<br>";
		if($qtde_01==0) {
			//SQL
			$sql = "select count(*) as qtde from dist_usuarios_games ";
			$sql .= " where ug_login = " . SQLaddFields($login, "s");
//			if($usuario_id_excessao && !is_null($usuario_id_excessao) && is_numeric($usuario_id_excessao))
//				$sql .= " and ug_id = " . SQLaddFields(trim($usuario_id_excessao), "");

//echo "sql2: $sql<br>";
			$rs = SQLexecuteQuery($sql);
			if($rs && pg_num_rows($rs) > 0){
				$rs_row = pg_fetch_array($rs);
				$qtde_02 = $rs_row['qtde'];
			}			
		}

//echo "qtde_01: $qtde_01, qtde_02: $qtde_02, (".($qtde_01 + $qtde_02).")<br>";
		if (($qtde_01 + $qtde_02)==0) $ret = false;
//echo "ret: ".(($ret)?"True":"False")."<br>";
//die("Em existeLogin<br>");
		return $ret;   	
    }
    
    function autenticarLogin($login, $senha){

		$ret = false;
//echo "ret0: ".(($ret)?"ret OK":"Not ret")."<br>";
		
		//Autentica usuario
		//------------------------------------------------------------------
		$objEncryption = new Encryption();
		$senha = $objEncryption->encrypt(trim($senha));
		$login = strtoupper(trim($login));
/*
		//SQL
		$sql = "select ugo_ug_id from dist_usuarios_games_operador ";
		$sql .= " where ugo_ativo = 1 ";
		$sql .= " and ugo_login = " . SQLaddFields($login, "s");
//		$sql .= " and ugo_ug_id = " . SQLaddFields($this->ugo_ug_id, "s");
		$sql .= " and ugo_senha = " . SQLaddFields($senha, "s");
        */

        $sql = "select ugo_ug_id from dist_usuarios_games_operador where ugo_ativo = 1 and ugo_login = ? and ugo_senha = ? ";

        $con = ConnectionPDO::getConnection();
        $pdo = $con->getLink();

        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($login, $senha));
        $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ( count($fetch) > 0 ) {
            $ugo_ug_id = $fetch[0]['ugo_ug_id'];
        }

//echo "<!-- sql: $sql<br>\n -->";
//die("Para 3223");
//echo "reta: $ret<br>";
		/*
        $rs_id = SQLexecuteQuery($sql);
		if($rs_id && pg_num_rows($rs_id) > 0){
			$rs_id_row = pg_fetch_array($rs_id);
			$ugo_ug_id = $rs_id_row['ugo_ug_id'];
		}
		*/
//echo "ugo_ug_id: ".$ugo_ug_id."<br>";
                $instUsuarioGames = new UsuarioGames;
		$objGamesUsuario = $instUsuarioGames->getUsuarioGamesById($ugo_ug_id);
		if($objGamesUsuario != null) {
			$ug_ativo = $objGamesUsuario->getAtivo();
                        $ug_substatus = $objGamesUsuario->getSubstatus();
		}
		
//echo "ug_ativo: ".$ug_ativo."<br>";
                
		if($ug_ativo==1 && $ug_substatus==11 || $ug_ativo==1 && $ug_substatus==9) {
			//SQL
			$sql = "select count(*) as qtde from dist_usuarios_games_operador ";
			$sql .= " where ugo_ativo = 1 ";
			$sql .= " and ugo_login = " . SQLaddFields($login, "s");
	//		$sql .= " and ugo_ug_id = " . SQLaddFields($this->ugo_ug_id, "s");
			$sql .= " and ugo_senha = " . SQLaddFields($senha, "s");
//echo "sql: $sql<br>";
//die("Para 3223");
//echo "reta: $ret<br>";
			$rs = SQLexecuteQuery($sql);
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
				usuarios_games_operador_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['LOGIN_OPERADOR'], null, null);
				
			}
		} 

//echo "ret3: ".(($ret)?"ret OK":"Not ret")."<br>";
 		return $ret;   	
    }
    
    function LoginAutomatico($ugo_id,$login){

		$ret = false;
		//SQL
		$sql = "select ugo_ug_id from dist_usuarios_games_operador ";
		$sql .= " where ugo_ativo = 1 ";
		$sql .= " and ugo_id = " . SQLaddFields($ugo_id, "");

                $rs_id = SQLexecuteQuery($sql);
		if($rs_id && pg_num_rows($rs_id) > 0){
			$rs_id_row = pg_fetch_array($rs_id);
			$ugo_ug_id = $rs_id_row['ugo_ug_id'];
		}			

		$objGamesUsuario = UsuarioGames::getUsuarioGamesById($ugo_ug_id);
		if($objGamesUsuario != null) {
			$ug_ativo = $objGamesUsuario->getAtivo();
                        $ug_substatus = $objGamesUsuario->getSubstatus();
		}

		if($ug_ativo==1 && $ug_substatus==11 || $ug_ativo==1 && $ug_substatus==9) {
			//SQL
			$sql = "select count(*) as qtde from dist_usuarios_games_operador ";
			$sql .= " where ugo_ativo = 1 ";
        		$sql .= " and ugo_id = " . SQLaddFields($ugo_id, "");

                        $rs = SQLexecuteQuery($sql);
			if($rs && pg_num_rows($rs) > 0){
				$rs_row = pg_fetch_array($rs);
				if($rs_row['qtde'] > 0) $ret = true;
			}			

			//Adiciona objeto usuario no session
			if($ret){
		                $ret = UsuarioGamesOperador::adicionarLoginSession($login, $ugo_ug_id); 
     			}

			//Atualiza ultimo acesso
			//------------------------------------------------------------------
			if($ret){
				UsuarioGamesOperador::atualiza_ultimo_acesso($login);
				//Log na base
				usuarios_games_operador_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['LOGIN_OPERADOR'], null, null);
				
			}
		} 

 		return $ret;   	
    }
    
    function atualiza_ultimo_acesso($login) {

            //Atualiza ultimo acesso
            //------------------------------------------------------------------
            if($login){
                    //SQL
                    $sql = "update dist_usuarios_games_operador set ";
                    $sql .= " ugo_data_ultimo_acesso = CURRENT_TIMESTAMP,";
                    $sql .= " ugo_qtde_acessos = ugo_qtde_acessos + 1 ";
                    $sql .= " where ugo_login = " . SQLaddFields($login, "s");
                    $rs = SQLexecuteQuery($sql);			
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
        $server_url = "www.e-prepag.com.br";
            if(checkIP()) {
                $server_url = $_SERVER['SERVER_NAME'];
                }
		$ret = false;
		
		//Autentica usuario
		//------------------------------------------------------------------
		$objEncryption = new Encryption();
		$senha = $objEncryption->encrypt(trim($senha));
		$senhaAtual = $objEncryption->encrypt(trim($senhaAtual));
		$login = strtoupper(trim($login));

		//SQL
		$sql = "select count(*) as qtde from dist_usuarios_games_operador ";
		$sql .= " where ugo_login = " . SQLaddFields($login, "s");
		$sql .= " and ugo_senha = " . SQLaddFields($senhaAtual, "s");
			
		$rs = SQLexecuteQuery($sql);
		if($rs && pg_num_rows($rs) > 0){
			$rs_row = pg_fetch_array($rs);
			if($rs_row['qtde'] > 0) $ret = true;
		}			

		//Atualiza ultimo acesso
		//------------------------------------------------------------------
		if($ret){
			//SQL
			$sql = "update dist_usuarios_games_operador set ";
			$sql .= " ugo_senha = " . SQLaddFields($senha, "s");
			$sql .= " where ugo_login = " . SQLaddFields($login, "s");
			$sql .= " and ugo_senha = " . SQLaddFields($senhaAtual, "s");
			$ret = SQLexecuteQuery($sql);
			
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
        $server_url = "www.e-prepag.com.br";
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
}

?>
