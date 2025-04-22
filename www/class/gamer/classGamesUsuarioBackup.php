<?php
class UsuarioGames {

    var $ug_id;
    var $ug_sSenha;
    var $ug_blAtivo;
    var $ug_dDataInclusao;
    var $ug_dDataUltimoAcesso;
    var $ug_iQtdeAcessos;

    var $ug_sEmail;
    var $ug_sNome;
    var $ug_sCPF;
    var $ug_sRG;
    var $ug_dDataNascimento;
    var $ug_cSexo;
    var $ug_sTipoEnd;
    var $ug_sEndereco;
    var $ug_sNumero;
    var $ug_sComplemento;
    var $ug_sBairro;
    var $ug_sCidade;
    var $ug_sEstado;
    var $ug_sCEP;
    var $ug_sTelDDI;
    var $ug_sTelDDD;
    var $ug_sTel;
    var $ug_sCelDDI;
    var $ug_sCelDDD;
    var $ug_sCel;
    var $ug_sHabboId;
    var $ugNewsLetter;
    var $ug_integracao_origem;

    var $ug_fPerfilSaldo;

    var $ug_compet_lh_ug_id;
    var $ug_compet_jogo;
    var $ug_compet_aceito_regulamento;
    var $ug_compet_aceito_data_aceito;

    var $ug_use_cielo;

    // Saldo de Fidelização
    var $ug_saldo_fidelizacao;

    // Categoria de Fidelização
    var $ug_categoria_fidelizacao;

    // Observações
    var $ug_obs;

    // Nome da mãe do usuário
    var $ug_nome_da_mae;

    //login
    var $login;
    
    var $ug_nome_cpf;
    
    /*
      function UsuarioGames() {
      }
     */
    function UsuarioGames(	$ug_id 				= null,
						    $ug_sSenha 			= null,
						    $ug_blAtivo 		= null,
						    $ug_dDataInclusao 	= null,
							$ug_dDataUltimoAcesso= null,
							$ug_iQtdeAcessos	= null,
						
						    $ug_sEmail 			= null,
						    $ug_sNome 			= null,
						    $ug_sCPF 			= null,
						    $ug_sRG 			= null,
						    $ug_dDataNascimento = null,
						    $ug_cSexo 			= null,
						    $ug_sTipoEnd 		= null,
						    $ug_sEndereco 		= null,
						    $ug_sNumero 		= null,
						    $ug_sComplemento 	= null,
						    $ug_sBairro 		= null,
						    $ug_sCidade 		= null,
						    $ug_sEstado 		= null,
						    $ug_sCEP 			= null,
						    $ug_sTelDDI 		= null,
						    $ug_sTelDDD 		= null,
						    $ug_sTel 			= null,
						    $ug_sCelDDI 		= null,
						    $ug_sCelDDD 		= null,
						    $ug_sCel 			= null,
						    $ug_sHabboId		= null,
                                $ug_NewsLetter		= null, 

                                $ug_compet_lh_ug_id	= null, 
                                $ug_compet_jogo		= null, 
                                $ug_compet_aceito_regulamento	= null, 
                                $ug_compet_aceito_data_aceito	= null,
                                $ug_use_cielo					= null,

                                $ug_saldo_fidelizacao			= null,
                                $ug_categoria_fidelizacao		= null,
                                $login = null
							) {

        $this->setId($ug_id);
        $this->setSenha($ug_sSenha);
        $this->setAtivo($ug_blAtivo);
        $this->setDataInclusao($ug_dDataInclusao);
        $this->setDataUltimoAcesso($ug_dDataUltimoAcesso);
        $this->setQtdeAcessos($ug_iQtdeAcessos);

        $this->setEmail($ug_sEmail);
        $this->setNome($ug_sNome);
        $this->setCPF($ug_sCPF);
        $this->setRG($ug_sRG);
        $this->setDataNascimento($ug_dDataNascimento);
        $this->setSexo($ug_cSexo);
        $this->setTipoEnd($ug_sTipoEnd);
        $this->setEndereco($ug_sEndereco);
        $this->setNumero($ug_sNumero);
        $this->setComplemento($ug_sComplemento);
        $this->setBairro($ug_sBairro);
        $this->setCidade($ug_sCidade);
        $this->setEstado($ug_sEstado);
        $this->setCEP($ug_sCEP);
        $this->setTelDDI($ug_sTelDDI);
        $this->setTelDDD($ug_sTelDDD);
        $this->setTel($ug_sTel);
        $this->setCelDDI($ug_sCelDDI);
        $this->setCelDDD($ug_sCelDDD);
        $this->setCel($ug_sCel);
        $this->setHabboId($ug_sHabboId);
        $this->setNewsLetter($ug_NewsLetter);

        $this->setCompet_lh_ug_id($ug_compet_lh_ug_id);
        $this->setCompet_jogo($ug_compet_jogo);
        $this->setCompet_aceito_regulamento($ug_compet_aceito_regulamento);
        $this->setCompet_aceito_data_aceito($ug_compet_aceito_data_aceito);
        $this->setUseCielo($ug_use_cielo);

        $this->setSaldoFidelizacao($ug_saldo_fidelizacao);
        $this->setCategoriaFidelizacao($ug_categoria_fidelizacao);
        $this->setLogin($login);
    }
    
    public function getLogin() {
        return $this->login;
    }

    public function setLogin($login) {
        $this->login = $login;
        return $this;
    }

    function getId() {
        return $this->ug_id;
    }
    function setId($ug_id) {
        $this->ug_id = $ug_id;
    }

    function getSenha() {
        return $this->ug_sSenha;
    }
    function setSenha($ug_sSenha) {
        $this->ug_sSenha = $ug_sSenha;
    }

    function getAtivo() {
        return $this->ug_blAtivo;
    }
    function setAtivo($ug_blAtivo) {
        if (!is_null($ug_blAtivo)) {
            if ($ug_blAtivo == 1 || $ug_blAtivo == "1" || $ug_blAtivo === "true") $ug_blAtivo = 1;
            elseif($ug_blAtivo == '' || $ug_blAtivo == '0' || $ug_blAtivo == 0 || $ug_blAtivo == "false") $ug_blAtivo = 2;
            else $ug_blAtivo = $ug_blAtivo;
        } //end if (!is_null($ug_blAtivo))
        else $ug_blAtivo = 2;
        $this->ug_blAtivo = $ug_blAtivo;
    }

    function getDataInclusao() {
        return $this->ug_dDataInclusao;
    }
    function setDataInclusao($ug_dDataInclusao) {
        $this->ug_dDataInclusao = $ug_dDataInclusao;
    }

    function getDataUltimoAcesso() {
        return $this->ug_dDataUltimoAcesso;
    }
    function setDataUltimoAcesso($ug_dDataUltimoAcesso) {
        $this->ug_dDataUltimoAcesso = $ug_dDataUltimoAcesso;
    }

    function getQtdeAcessos() {
        return $this->ug_iQtdeAcessos;
    }
    function setQtdeAcessos($ug_iQtdeAcessos) {
        $this->ug_iQtdeAcessos = $ug_iQtdeAcessos;
    }

    function getEmail() {
        return $this->ug_sEmail;
    }
    function setEmail($ug_sEmail) {
        $this->ug_sEmail = $ug_sEmail;
    }

    function getNome() {
        return $this->ug_sNome;
    }
    function setNome($ug_sNome) {
        $this->ug_sNome = $ug_sNome;
    }

    function getCPF() {
        return $this->ug_sCPF;
    }
    function setCPF($ug_sCPF) {
        $this->ug_sCPF = $ug_sCPF;
    }

    function getRG() {
        return $this->ug_sRG;
    }
    function setRG($ug_sRG) {
        if (!is_null($ug_sRG)) $ug_sRG = preg_replace("/[\.-]/", "", $ug_sRG);
        $this->ug_sRG = $ug_sRG;
    }

    function getDataNascimento() {
        return $this->ug_dDataNascimento;
    }
    function setDataNascimento($ug_dDataNascimento) {
        $this->ug_dDataNascimento = $ug_dDataNascimento;
    }

    function getSexo() {
        return $this->ug_cSexo;
    }
    function setSexo($ug_cSexo) {
        $this->ug_cSexo = $ug_cSexo;
    }

    
    function getEndereco() {
        return $this->ug_sEndereco;
    }
    function setEndereco($ug_sEndereco) {
        $this->ug_sEndereco = $ug_sEndereco;
    }

    function getTipoEnd() {
        return $this->ug_sTipoEnd;
    }
    function setTipoEnd($ug_sTipoEnd) {
        $this->ug_sTipoEnd = $ug_sTipoEnd;
    }

    function getNumero() {
        return $this->ug_sNumero;
    }
    function setNumero($ug_sNumero) {
        $this->ug_sNumero = $ug_sNumero;
    }

    function getComplemento() {
        return $this->ug_sComplemento;
    }
    function setComplemento($ug_sComplemento) {
        $this->ug_sComplemento = $ug_sComplemento;
    }

    function getBairro() {
        return $this->ug_sBairro;
    }
    function setBairro($ug_sBairro) {
        $this->ug_sBairro = $ug_sBairro;
    }

    function getCidade() {
        return $this->ug_sCidade;
    }
    function setCidade($ug_sCidade) {
        $this->ug_sCidade = $ug_sCidade;
    }

    function getEstado() {
        return $this->ug_sEstado;
    }
    function setEstado($ug_sEstado) {
        $this->ug_sEstado = $ug_sEstado;
    }

    function getCEP() {
        return $this->ug_sCEP;
    }
    function setCEP($ug_sCEP) {
        $this->ug_sCEP = $ug_sCEP;
    }

    function getTelDDI() {
        return $this->ug_sTelDDI;
    }
    function setTelDDI($ug_sTelDDI) {
        $this->ug_sTelDDI = $ug_sTelDDI;
    }

    function getTelDDD() {
        return $this->ug_sTelDDD;
    }
    function setTelDDD($ug_sTelDDD) {
        $this->ug_sTelDDD = $ug_sTelDDD;
    }

    function getTel() {
        return $this->ug_sTel;
    }
    function setTel($ug_sTel) {
        $this->ug_sTel = $ug_sTel;
    }

    function getCelDDI() {
        return $this->ug_sCelDDI;
    }
    function setCelDDI($ug_sCelDDI) {
        $this->ug_sCelDDI = $ug_sCelDDI;
    }

    function getCelDDD() {
        return $this->ug_sCelDDD;
    }
    function setCelDDD($ug_sCelDDD) {
        $this->ug_sCelDDD = $ug_sCelDDD;
    }

    function getCel() {
        return $this->ug_sCel;
    }
    function setCel($ug_sCel) {
        $this->ug_sCel = $ug_sCel;
    }

    function getHabboId() {
        return $this->ug_sHabboId;
    }
    function setHabboId($ug_sHabboId) {
        $this->ug_sHabboId = $ug_sHabboId;
    }

    function getNewsLetter() {
        return $this->ugNewsLetter;
    }
    function setNewsLetter($ugNewsLetter) {
        // Se não for H - HTML ou N - Não então cadastra T - Text
        if (strtoupper($ugNewsLetter) != 'H' && strtoupper($ugNewsLetter) != 'N') $ugNewsLetter = 't';
        $this->ugNewsLetter = $ugNewsLetter;
    }

    function getUseCielo() {
        return $this->ug_use_cielo;
    }
    function setUseCielo($ug_use_cielo) {
        $this->ug_use_cielo = $ug_use_cielo;
    }

    function getOBS() {
        return $this->ug_obs;
    }
    function setOBS($ug_obs) {
        $this->ug_obs = $ug_obs;
    }

    function getNomedaMae() {
        return $this->ug_nome_da_mae;
    }
    function setNomedaMae($ug_nome_da_mae) {
        $this->ug_nome_da_mae = $ug_nome_da_mae;
    }
    
    public function getNomeCPF() {
        return $this->ug_nome_cpf;
    }

    public function setNomeCPF($ug_nome_cpf) {
        $this->ug_nome_cpf = $ug_nome_cpf;
        return $this;
    }

    function getCompet_lh_ug_id(){ return $this->ug_compet_lh_ug_id; }
	function setCompet_lh_ug_id($ug_compet_lh_ug_id){ $this->ug_compet_lh_ug_id = $ug_compet_lh_ug_id; }

	function getCompet_jogo(){ return $this->ug_compet_jogo; }
	function setCompet_jogo($ug_compet_jogo){ $this->ug_compet_jogo = $ug_compet_jogo; }

	function getCompet_aceito_regulamento(){ return $this->ug_compet_aceito_regulamento; }
	function setCompet_aceito_regulamento($ug_compet_aceito_regulamento){ $this->ug_compet_aceito_regulamento = $ug_compet_aceito_regulamento; }

	function getCompet_aceito_data_aceito(){ return $this->ug_compet_aceito_data_aceito; }
	function setCompet_aceito_data_aceito($ug_compet_aceito_data_aceito){ $this->ug_compet_aceito_data_aceito = $ug_compet_aceito_data_aceito; }

	function getPerfilSaldo(){ return $this->ug_fPerfilSaldo;}
	function setPerfilSaldo($ug_fPerfilSaldo){ $this->ug_fPerfilSaldo = $ug_fPerfilSaldo;}

    //Metodos de SET e GET para o Campo ug_saldo_fidelizacao - Contem o Saldo de Fidelização
	function getSaldoFidelizacao(){ return $this->ug_saldo_fidelizacao; }
	function setSaldoFidelizacao($ug_saldo_fidelizacao){	$this->ug_saldo_fidelizacao = $ug_saldo_fidelizacao; }

    //Metodos de SET e GET para o Campo ug_categoria_fidelizacao - Contem a Categoria de Fidelização
	function getCategoriaFidelizacao(){ return $this->ug_categoria_fidelizacao; }
	function setCategoriaFidelizacao($ug_categoria_fidelizacao){	$this->ug_categoria_fidelizacao = $ug_categoria_fidelizacao; }

    function getIdUsuarioGamerByEmail($ug_email) {
        $sql = "select ug_id from usuarios_games where ug_ativo = 1 and ug_email = '" . trim(strtoupper($ug_email)) . "'";
        $rs = SQLexecuteQuery($sql);
        $dataUser = pg_fetch_array($rs);

        return $dataUser['ug_id'];
    }
	
	function getEmailUsuarioGamerById($ug_id) {
		$sql = "select ug_email from usuarios_games where ug_ativo = 1 and ug_id = " . $ug_id . "";
        $con = ConnectionPDO::getConnection();
        $pdo = $con->getLink();
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		
		$dataUser = $stmt->fetch(PDO::FETCH_ASSOC);
        return $dataUser['ug_email'];
	}
    
    function inserir(&$objGamesUsuario) {

        $ret = UsuarioGames::validarCampos($objGamesUsuario, true);

        if ($ret == "") {
            if (UsuarioGames::existeEmail($objGamesUsuario->getEmail(), null)) {
                $ret = "Email já cadastrado.";
            }
        }

        if ($ret == "") {
            if (UsuarioGames::existeCPF($objGamesUsuario->getCPF(), null)) {
                $ret = "CPF já cadastrado.";
            }
        }

        if ($ret == "") {
            if (UsuarioGames::existeRG($objGamesUsuario->getRG(), null)) {
                $ret = "RG já cadastrado.";
            }
        }

        if ($ret == "") {

            //Formata
            $objEncryption = new Encryption();
            $senha = $objEncryption->encrypt(trim($objGamesUsuario->getSenha()));
            $dataInclusao = "CURRENT_TIMESTAMP";
            $dataUltimoAcesso = "CURRENT_TIMESTAMP";
            $qtdeAcessos = 0;
            $dataNascimento = formata_data(trim($objGamesUsuario->getDataNascimento()), 1);
            $ug_news = $objGamesUsuario->getNewsLetter();
            if (strtoupper($ug_news) != "H" && strtoupper($ug_news) != "T") {
                $ug_news = "n";
                $objGamesUsuario->setNewsLetter($ug_news);
            }

            //SQL
            $sql = "insert into usuarios_games(ug_senha, ug_ativo, ug_data_inclusao, " .
                    "ug_data_ultimo_acesso, ug_qtde_acessos, ug_email, ug_nome, ug_cpf, ug_rg, " .
                    "ug_data_nascimento, ug_sexo, ug_tipo_end, ug_endereco, ug_numero, ug_complemento, " .
                    "ug_bairro, ug_cidade, ug_estado, ug_cep, ug_tel_ddi, ug_tel_ddd, ug_tel, " .
                    "ug_cel_ddi, ug_cel_ddd, ug_cel, ug_habbo_id, ug_news, " .
                    "ug_compet_lh_ug_id, ug_compet_jogo, ug_compet_aceito_regulamento, ug_compet_aceito_data_aceito ";

            $sql .= ") values (";

            $sql .= SQLaddFields($senha, "s") . ",";
            $sql .= SQLaddFields(trim($objGamesUsuario->getAtivo()), "") . ",";
            $sql .= SQLaddFields($dataInclusao, "") . ",";
            $sql .= SQLaddFields($dataUltimoAcesso, "") . ",";
            $sql .= SQLaddFields($qtdeAcessos, "") . ",";

            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getEmail())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getNome())), "s") . ",";
            $sql .= SQLaddFields(trim($objGamesUsuario->getCPF()), "s") . ",";
            $sql .= SQLaddFields(trim($objGamesUsuario->getRG()), "s") . ",";
            $sql .= SQLaddFields(trim($dataNascimento), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getSexo())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getTipoEnd())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getEndereco())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getNumero())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getComplemento())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getBairro())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getCidade())), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($objGamesUsuario->getEstado())), "s") . ",";
            $sql .= SQLaddFields(trim($objGamesUsuario->getCEP()), "s") . ",";
            $sql .= SQLaddFields(trim($objGamesUsuario->getTelDDI()), "s") . ",";
            $sql .= SQLaddFields(trim($objGamesUsuario->getTelDDD()), "s") . ",";
            $sql .= SQLaddFields(trim($objGamesUsuario->getTel()), "s") . ",";
            $sql .= SQLaddFields(trim($objGamesUsuario->getCelDDI()), "s") . ",";
            $sql .= SQLaddFields(trim($objGamesUsuario->getCelDDD()), "s") . ",";
            $sql .= SQLaddFields(trim($objGamesUsuario->getCel()), "s") . ",";
            $sql .= SQLaddFields(trim($objGamesUsuario->getHabboId()), "s") . ",";
            $sql .= SQLaddFields(trim($objGamesUsuario->getNewsLetter()), "s") . ",";


            $sql .= SQLaddFields((($objGamesUsuario->getCompet_lh_ug_id() > 0) ? $objGamesUsuario->getCompet_lh_ug_id() : 0), "") . ",";
            $sql .= SQLaddFields((($objGamesUsuario->getCompet_jogo() > 0) ? $objGamesUsuario->getCompet_jogo() : 0), "") . ",";
            $sql .= SQLaddFields(trim($objGamesUsuario->getCompet_aceito_regulamento()), "s") . ",";
            if (strtoupper($objGamesUsuario->getCompet_aceito_regulamento()) == "S")
                $sql .= SQLaddFields("CURRENT_TIMESTAMP", "") . " ";
            else
                $sql .= SQLaddFields("null", "") . " ";

            $sql .= ")";

            $ret = SQLexecuteQuery($sql);
            if (!$ret) $ret = "Erro ao inserir usuário.\n";
            else {
                $ret = "";
                $rs_id = SQLexecuteQuery("select currval('usuarios_games_id_seq') as last_id");
                if ($rs_id && pg_num_rows($rs_id) > 0) {
                    $rs_id_row = pg_fetch_array($rs_id);
                    $objGamesUsuario->setId($rs_id_row['last_id']);

                    //Log na base
                    usuarios_games_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['CRIACAO_DO_CADASTRO'], $objGamesUsuario->getId(), null);

                    //Envia email
                    //--------------------------------------------------------------------------------
                    /*
                      $parametros['prepag_dominio'] = "http://www.e-prepag.com.br";
                      $parametros['nome'] = $objGamesUsuario->getNome();
                      $parametros['sexo'] = $objGamesUsuario->getSexo();

                      $msgEmail  = email_cabecalho($parametros);
                      $msgEmail .= "  <br><br>
                      <table border='0' cellspacing='0'>
                      <tr><td>&nbsp;</td></tr>
                      <tr valign='middle' bgcolor='#FFFFFF'>
                      <td align='left' class='texto'>
                      Obrigado por se cadastrar conosco.<br><br>
                      Utilize seu email " . $objGamesUsuario->getEmail() . " para acessar sua conta e realizar compras em nosso site.
                      </td>
                      </tr>
                      <tr><td>&nbsp;</td></tr>
                      </table>
                      ";
                      $msgEmail .= email_rodape($parametros);
                      enviaEmail($objGamesUsuario->getEmail(), null, null, "E-Prepag - Cadastro", $msgEmail);
                     */
                    /* ---Wagner */
                    $objEnvioEmailAutomatico = new EnvioEmailAutomatico(TIPO_USUARIO_GAMER, 'CadastroGamer');
                    $objEnvioEmailAutomatico->setUgID($objGamesUsuario->getId());
                    $objEnvioEmailAutomatico->MontaEmailEspecifico();

                    }
            }

            }

        return $ret;
    }

    function inserir_simple($store_id_novo, $email_novo) {

        $id_novo = 0;
        if ($ret == "") {
            if (UsuarioGames::existeEmail($email_novo, null)) {
                $ret = "Email já cadastrado.";
            }
        }

        if ($ret == "") {

            //Formata
            $objEncryption = new Encryption();
            $senha = get_random_password(10);
            $senha = $objEncryption->encrypt($senha);

            $dataInclusao = "CURRENT_TIMESTAMP";
            $dataUltimoAcesso = "CURRENT_TIMESTAMP";
            $qtdeAcessos = 1;  // Este é o primeiro acesso
            $news_letter = "h";  // usuário recebe news
            
//SQL
            $sql = "insert into usuarios_games(ug_senha, ug_ativo, ug_data_inclusao, " .
                    "ug_data_ultimo_acesso, ug_qtde_acessos, ug_integracao_origem, ug_nome, ug_email, " .
                    "ug_origem_parceiro, " .
                    "ug_sexo, ug_endereco, ug_numero, ug_complemento, ug_bairro, ug_cidade, ug_estado, ug_cep, ug_tel_ddi, ug_tel_ddd, ug_tel, ug_cel_ddi, ug_cel_ddd, ug_cel, " .
                    "ug_news) values (";

            $sql .= SQLaddFields($senha, "s") . ",";
            $sql .= SQLaddFields(1, "") . ",";
            $sql .= SQLaddFields($dataInclusao, "") . ",";
            $sql .= SQLaddFields($dataUltimoAcesso, "") . ",";
            $sql .= SQLaddFields($qtdeAcessos, "") . ",";
            $sql .= SQLaddFields($store_id_novo, "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($email_novo)), "s") . ",";
            $sql .= SQLaddFields(trim(strtoupper($email_novo)), "s") . ", ";

            $sql .= SQLaddFields(trim(strtoupper($store_id_novo)), "s") . ", ";

            $sql .= "'', '', '', '', '', '', '', '', '', '', '', '', '', '', ";
            
            $sql .= SQLaddFields($news_letter, "s") . "";

            $sql .= ");";
//echo "$sql<br>";
//grava_log_integracao("Integração Debug 4: ".date("Y-m-d H:i:s")."\n  $sql \n");
            $ret = SQLexecuteQuery($sql);
            if (!$ret) $ret = "Erro ao inserir usuário.\n";
            else { 
                $ret = "";
                $rs_id = SQLexecuteQuery("select currval('usuarios_games_id_seq') as last_id");
                if ($rs_id && pg_num_rows($rs_id) > 0) {
                    $rs_id_row = pg_fetch_array($rs_id);
                    $id_novo = $rs_id_row['last_id'];

                    //Log na base
                    usuarios_games_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['CRIACAO_DO_CADASTRO'], $id_novo, null);

                    /* 					
                      //Envia email
                      //--------------------------------------------------------------------------------
                      $parametros['prepag_dominio'] = "http://www.e-prepag.com.br";
                      $parametros['nome'] = "";
                      $parametros['sexo'] = "";

                      $msgEmail  = email_cabecalho($parametros);
                      $msgEmail .= "  <br><br>
                      <table border='0' cellspacing='0'>
                      <tr><td>&nbsp;</td></tr>
                      <tr valign='middle' bgcolor='#FFFFFF'>
                      <td align='left' class='texto'>
                      Obrigado por se cadastrar conosco.<br><br>
                      Utilize seu email " . $email_novo . " para acessar sua conta e realizar compras em nosso site.
                      </td>
                      </tr>
                      <tr><td>&nbsp;</td></tr>
                      </table>
                      ";
                      $msgEmail .= email_rodape($parametros);
                      enviaEmail($email_novo, null, null, "E-Prepag - Cadastro (parceiro)", $msgEmail);
                     */
                }
            }

            }

        return $id_novo;
    }

    function atualizar($objGamesUsuario) {

        $ret = $this->validarCampos($objGamesUsuario, false);

        if ($ret == "") {
            if ($this->existeEmail($objGamesUsuario->getEmail(), $objGamesUsuario->getId())) {
                $ret = "Email já cadastrado.";
            }
        }

        if ($ret == "") {
            if ($this->existeCPF($objGamesUsuario->getCPF(), $objGamesUsuario->getId())) {
                $ret = "CPF já cadastrado.";
            }
        }

        if ($ret == "") {
            if ($this->existeRG($objGamesUsuario->getRG(), $objGamesUsuario->getId())) {
                $ret = "RG já cadastrado.";
            }
        }

        if ($ret == "") {

            //Formata
 			if(!is_null($objGamesUsuario->getDataNascimento())) $dataNascimento = formata_data($objGamesUsuario->getDataNascimento(), 1);

            //SQL
            $sql = "update usuarios_games set ";
 			if(!is_null($objGamesUsuario->getAtivo())) 			$sql .= " ug_ativo = " 			. SQLaddFields(trim($objGamesUsuario->getAtivo()), "") . ",";
 			if(!is_null($objGamesUsuario->getEmail())) 			$sql .= " ug_email = " 			. SQLaddFields(trim(strtoupper($objGamesUsuario->getEmail())), "s") . ",";
 			if(!is_null($objGamesUsuario->getNome())) 			$sql .= " ug_nome = " 			. SQLaddFields(trim(strtoupper($objGamesUsuario->getNome())), "s") . ",";
 			if(!is_null($objGamesUsuario->getCPF())) 			$sql .= " ug_cpf = " 			. SQLaddFields(trim($objGamesUsuario->getCPF()), "s") . ",";
 			if(!is_null($objGamesUsuario->getRG())) 			$sql .= " ug_rg = " 			. SQLaddFields(trim($objGamesUsuario->getRG()), "s") . ",";
            if (verifica_data($objGamesUsuario->getDataNascimento()) == 1) {
	 			if(!is_null($objGamesUsuario->getDataNascimento())) $sql .= " ug_data_nascimento = ". SQLaddFields(trim($dataNascimento), "s") . ",";
            }
 			if(!is_null($objGamesUsuario->getSexo())) 			$sql .= " ug_sexo = " 			. SQLaddFields(trim(strtoupper($objGamesUsuario->getSexo())), "s") . ",";
 			if(!is_null($objGamesUsuario->getTipoEnd())) 		$sql .= " ug_tipo_end = ". SQLaddFields(trim(strtoupper($objGamesUsuario->getTipoEnd())), "s") . ",";
 			if(!is_null($objGamesUsuario->getEndereco())) 		$sql .= " ug_endereco = " 		. SQLaddFields(trim(strtoupper($objGamesUsuario->getEndereco())), "s") . ",";
 			if(!is_null($objGamesUsuario->getNumero())) 		$sql .= " ug_numero = " 		. SQLaddFields(trim(strtoupper($objGamesUsuario->getNumero())), "s") . ",";
 			if(!is_null($objGamesUsuario->getComplemento())) 	$sql .= " ug_complemento = " 	. SQLaddFields(trim(strtoupper($objGamesUsuario->getComplemento())), "s") . ",";
 			if(!is_null($objGamesUsuario->getBairro())) 		$sql .= " ug_bairro = " 		. SQLaddFields(trim(strtoupper($objGamesUsuario->getBairro())), "s") . ",";
 			if(!is_null($objGamesUsuario->getCidade())) 		$sql .= " ug_cidade = " 		. SQLaddFields(trim(strtoupper($objGamesUsuario->getCidade())), "s") . ",";
 			if(!is_null($objGamesUsuario->getEstado())) 		$sql .= " ug_estado = " 		. SQLaddFields(trim(strtoupper($objGamesUsuario->getEstado())), "s") . ",";
 			if(!is_null($objGamesUsuario->getCEP())) 			$sql .= " ug_cep = " 			. SQLaddFields(trim($objGamesUsuario->getCEP()), "s") . ",";
 			if(!is_null($objGamesUsuario->getTelDDI())) 		$sql .= " ug_tel_ddi = " 		. SQLaddFields(trim($objGamesUsuario->getTelDDI()), "s") . ",";
 			if(!is_null($objGamesUsuario->getTelDDD())) 		$sql .= " ug_tel_ddd = " 		. SQLaddFields(trim($objGamesUsuario->getTelDDD()), "s") . ",";
 			if(!is_null($objGamesUsuario->getTel())) 			$sql .= " ug_tel = " 			. SQLaddFields(trim($objGamesUsuario->getTel()), "s") . ",";
 			if(!is_null($objGamesUsuario->getCelDDI())) 		$sql .= " ug_cel_ddi = " 		. SQLaddFields(trim($objGamesUsuario->getCelDDI()), "s") . ",";
 			if(!is_null($objGamesUsuario->getCelDDD())) 		$sql .= " ug_cel_ddd = " 		. SQLaddFields(trim($objGamesUsuario->getCelDDD()), "s") . ",";
 			if(!is_null($objGamesUsuario->getCelDDD())) 		$sql .= " ug_cel = " 			. SQLaddFields(trim($objGamesUsuario->getCel()), "s") . " ,";
			if(!is_null($objGamesUsuario->getHabboId())) 		$sql .= " ug_habbo_id = "		. SQLaddFields(trim($objGamesUsuario->getHabboId()), "s") . ",";
            if (!is_null($objGamesUsuario->getNewsLetter())) {
                $sql .= " ug_news = " . SQLaddFields(trim($objGamesUsuario->getNewsLetter()), "s") . ",";
            }
			if(!is_null($objGamesUsuario->getCompet_lh_ug_id())) 	$sql .= " ug_compet_lh_ug_id = "	. SQLaddFields(trim($objGamesUsuario->getCompet_lh_ug_id()), "") . ",";
			if(!is_null($objGamesUsuario->getCompet_jogo())) 		$sql .= " ug_compet_jogo = "		. SQLaddFields(trim($objGamesUsuario->getCompet_jogo()), "") . ",";
			if(!is_null($objGamesUsuario->getCompet_aceito_regulamento())) 		$sql .= " ug_compet_aceito_regulamento = "	. SQLaddFields(trim($objGamesUsuario->getCompet_aceito_regulamento()), "s") . ",";
			if(!is_null($objGamesUsuario->getCompet_aceito_data_aceito())) 		$sql .= " ug_compet_aceito_data_aceito = "	. SQLaddFields(trim($objGamesUsuario->getCompet_aceito_data_aceito()), "s") . ",";
			if(!is_null($objGamesUsuario->getUseCielo())) 						$sql .= " ug_use_cielo = " 					. SQLaddFields(trim($objGamesUsuario->getUseCielo()), "") . ",";
 			if(!is_null($objGamesUsuario->getSaldoFidelizacao())) 				$sql .= " ug_saldo_fidelizacao = " 			. SQLaddFields(trim($objGamesUsuario->getSaldoFidelizacao()), "") . ",";
 			if(!is_null($objGamesUsuario->getCategoriaFidelizacao())) 			$sql .= " ug_categoria_fidelizacao = " 		. SQLaddFields(trim($objGamesUsuario->getCategoriaFidelizacao()), "") . ",";

			if(substr($sql, -1) == ",") $sql = substr($sql, 0, strlen($sql) - 1);
            $sql .= " where ug_id = " . SQLaddFields($objGamesUsuario->getId(), "");

            $ret = SQLexecuteQuery($sql);
			if(!$ret) $ret = "Erro ao atualizar usuário.\n";
            else {
                $ret = "";

                //Log na base
                usuarios_games_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['ALTERACAO_DO_CADASTRO'], null, null);

                $usuarioGames = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']);
                if (is_object($usuarioGames)) {
                    //Envia email
                    /*
                      //--------------------------------------------------------------------------------
                      $parametros['prepag_dominio'] = "http://www.e-prepag.com.br";
                      $parametros['nome'] = $objGamesUsuario->getNome();
                      $parametros['sexo'] = $objGamesUsuario->getSexo();
                      $msgEmail  = email_cabecalho($parametros);
                      $msgEmail .= "  <br><br>
                      <table border='0' cellspacing='0'>
                      <tr><td>&nbsp;</td></tr>
                      <tr valign='middle' bgcolor='#FFFFFF'>
                      <td align='left' class='texto'>
                      Você acessou nosso site e alterou seu cadastro.<br><br>
                      Utilize seu email " . $objGamesUsuario->getEmail() . " para acessar sua conta e realizar compras em nosso site.
                      </td>
                      </tr>
                      <tr><td>&nbsp;</td></tr>
                      </table>
                      ";
                      $msgEmail .= email_rodape($parametros);
                     */
//					$usuarioGames = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']);
                    if (!is_null($usuarioGames->getEmail()) && $objGamesUsuario->getId() == $usuarioGames->getId()) {
//						enviaEmail($usuarioGames->getEmail(), null, null, "E-Prepag - Atualização de Cadastro", $msgEmail);


                        /* ---Wagner */
                        $objEnvioEmailAutomatico = new EnvioEmailAutomatico(TIPO_USUARIO_GAMER, 'AlteracaoCadastro');
                        $objEnvioEmailAutomatico->setUgID($usuarioGames->getId());
                        $objEnvioEmailAutomatico->MontaEmailEspecifico();

                        }//end if(!is_null($usuarioGames->getEmail()))
                }

                }

                }

        return $ret;
    }
    
    function atualizar_dados_endereco($objGamesUsuario, &$erro = array()) {

            if(is_numeric($objGamesUsuario->getId())) {
                
                try{
                        //Inicializando conexao PDO
                        $con = ConnectionPDO::getConnection();
                        $pdo = $con->getLink();
                        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                        if(!is_null($objGamesUsuario->getEndereco()))
                            $fields[":ug_endereco"] = (string) trim(strtoupper($objGamesUsuario->getEndereco()));

                        if(!is_null($objGamesUsuario->getNumero()))
                            $fields[":ug_numero"] = (string) trim(strtoupper($objGamesUsuario->getNumero()));

                        if(!is_null($objGamesUsuario->getBairro()))
                            $fields[":ug_bairro"] = (string) trim(strtoupper($objGamesUsuario->getBairro()));

                        if(!is_null($objGamesUsuario->getCidade()))
                            $fields[":ug_cidade"] = (string) trim(strtoupper($objGamesUsuario->getCidade()));

                        if(!is_null($objGamesUsuario->getComplemento()))
                            $fields[":ug_complemento"] = (string) trim(strtoupper($objGamesUsuario->getComplemento()));

                        if(!is_null($objGamesUsuario->getEstado()))
                            $fields[":ug_estado"] = (string) trim(strtoupper($objGamesUsuario->getEstado()));

                        if(!is_null($objGamesUsuario->getCEP()))
                            $fields[":ug_cep"] = (string) trim(strtoupper($objGamesUsuario->getCEP()));


                        if(!empty($fields)){                
                            $fields[":ug_id"] = (int) $objGamesUsuario->getId();

                            foreach($fields as $field => $value){
                                $strF[] = " ".str_replace(":","",$field)." = ".$field;
                            }

                            //SQL
                            $sql = "update 
                                        usuarios_games set 
                                        ".implode(",",$strF)."
                                    where 
                                        ug_id = :ug_id";
                            //Tentando executar a Query de Insert
                            $rs = $pdo->prepare($sql);

                            if($rs->execute($fields)){
                                //Log na base
                                usuarios_games_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['ALTERACAO_DO_CADASTRO'], null, null);

                                    /* ---Wagner */
                                $objEnvioEmailAutomatico = new EnvioEmailAutomatico(TIPO_USUARIO_GAMER, 'AlteracaoCadastro');
                                $objEnvioEmailAutomatico->setUgID($objGamesUsuario->getId());
                                $objEnvioEmailAutomatico->MontaEmailEspecifico();
                                $objGamesUsuario->adicionarLoginSession_ByID($objGamesUsuario->getId());
                                return true;

                            }
                            else{
                                $erro[] = "ERRO 215535. Tivemos um problema, favor entrar em contato com nosso suporte. Obrigado!";
                            }
                        }
                        else{
                            $erro[] = "ERRO 2155357. Houve algum erro no momento de informar os dados. Por favor, tente a operação novamente. Obrigado!";
                        }
 
                    
                } catch (Exception $ex) {
                    $erro[] = "ERRO 2155351. Tivemos um problema, favor se o erro persistir, entrar em contato com nosso suporte. Obrigado!";
                }

                UsuarioGames::logEvents("ERRO 2155351 ".implode(" / ",$erro));
                return false;
                
            
        } //end if(is_numeric($objGamesUsuario->getId()))
        else{
            $erro[] = "Sua sessão expirou. Volte no jogo e tente novamente. Obrigado!";
            UsuarioGames::logEvents("ERRO - Sem sucesso ao capturar o ID do usuário (objGamesUsuario->getId())");
            return false;
        }
    }//end atualizar_dados_endereco()

    function validarCamposLogin($senha, $senhaConf, $login) {

        $ret = "";

        $senha = trim($senha);
        $senhaConf = trim($senhaConf);
        $login = trim($login);

        //Senha
		if(is_null($senha) || $senha == "") 				$ret .= "A Senha deve ser preenchida.\n";
		elseif(strlen($senha) < 6 || strlen($senha) > 15) 	$ret .= "A Senha deve ter entre 6 e 15 caracteres.\n";

        //SenhaConf 		
        if ($senha != $senhaConf) {
            $ret .= "A confirmação da senha deve ser igual a senha.";
        }

        //login
 		if(is_null($login) || $login == "") $ret .= "O Email deve ser preenchido.\n";
		elseif(strlen($login) > 100) 		$ret .= "O Email deve ter até 100 caracteres.\n";
		elseif(!verifica_email($login)) 	$ret .= "O Email é inválido.\n";

        return $ret;
    }

    function validarCampos($objGamesUsuario, $blCompleto) {

        $ret = "";

        //Dados do login
        if ($blCompleto)
            $ret .= UsuarioGames::validarCamposLogin($objGamesUsuario->getSenha(), $objGamesUsuario->getSenha(), $objGamesUsuario->getEmail());

        //login
        $email = $objGamesUsuario->getEmail();
        if (!is_null($email) || $blCompleto) {
            $email = trim($objGamesUsuario->getEmail());
	 		if(is_null($email) || $email == "") $ret .= "O Email deve ser preenchido.\n";
			elseif(strlen($email) > 100) 		$ret .= "O Email deve ter até 100 caracteres.\n";
			elseif(!verifica_email($email)) 	$ret .= "O Email é inválido.\n";
        }

        //Nome
        $nome = $objGamesUsuario->getNome();
        if (!is_null($nome) || $blCompleto) {
            $nome = trim($objGamesUsuario->getNome());
 			if(is_null($nome) || $nome == "") 	$ret .= "O Nome deve ser preenchido.\n";
 			elseif(strlen($nome) > 100) 		$ret .= "O Nome deve ter até 100 caracteres.\n";
        }

        //CPF
        //$CPF = $objGamesUsuario->getCPF();
// 		if(!is_null($CPF) || $blCompleto){
//	 		$CPF = trim($objGamesUsuario->getCPF());
// 			if(is_null($CPF) || $CPF == "") 	$ret .= "O CPF deve ser preenchido.\n";
// 			elseif(verificaCPFEx($CPF) == 0) 		$ret .= "O CPF inválido. Utilize o formato xxx.xxx.xxx-xx\n";
// 		}
 		
        //RG
        //$RG = $objGamesUsuario->getRG();
// 		if(!is_null($RG) || $blCompleto){
//	 		$RG = trim($objGamesUsuario->getRG());
// 			if(is_null($RG) || $RG == "") 	$ret .= "O RG deve ser preenchido.\n";
// 			elseif(verificaRG($RG) == 0) 	$ret .= "O RG inválido.\n";
// 		}

        //CPF e RG
        //if(!is_null($CPF) || !is_null($RG) || $blCompleto){
        //	$CPF = trim($objGamesUsuario->getCPF());
        ///	$RG = trim($objGamesUsuario->getRG());
        //	if((is_null($CPF) || $CPF == "") && (is_null($RG) || $RG == "")) $ret .= "O CPF ou RG deve ser preenchido.\n";
        //	else{
        //		if(!is_null($CPF) && $CPF != ""){
        //			if(verificaCPFEx($CPF) == 0) $ret .= "O CPF é inválido. Utilize o formato xxx.xxx.xxx-xx\n";
        //		}
        //		if(!is_null($RG) && $RG != ""){
//					if(!eregi("^[A-Z0-9\.-/]{7,13}$", $RG)) $ret .= "O RG é inválido.\n";
        //			if(!eregi("^[A-Z0-9/.-]{7,13}$", $RG)) $ret .= "O RG é inválido.\n";
        //		}
        //	}
        //}

        //Data Nascimento
        $dataNascimento = $objGamesUsuario->getDataNascimento();
        if (!is_null($dataNascimento) || $blCompleto) {
            $dataNascimento = trim($objGamesUsuario->getDataNascimento());
 			if(is_null($dataNascimento) || $dataNascimento == "") 	$ret .= "A Data de Nascimento deve ser preenchida.\n";
 			elseif(verifica_data($dataNascimento) == 0)				$ret .= "A Data de Nascimento é inválida.\n";
        }

        //Sexo
        $sexo = $objGamesUsuario->getSexo();
        if (!is_null($sexo) || $blCompleto) {
            $sexo = trim($objGamesUsuario->getSexo());
 			if(is_null($sexo) || $sexo == "") 							$ret .= "O Sexo deve ser preenchida.\n";
 			elseif(strtoupper($sexo) != "M" && strtoupper($sexo) != "F")$ret .= "O Sexo é inválido.\n";
        }

        /*
          //Tipo Endereco
          $tipoEnd = $objGamesUsuario->getTipoEnd();
          if(!is_null($tipoEnd) || $blCompleto){
          $tipoEnd = trim($objGamesUsuario->getTipoEnd());
          }

          //Endereco
          $endereco = $objGamesUsuario->getEndereco();
          if(!is_null($endereco) || $blCompleto){
          $endereco = trim($objGamesUsuario->getEndereco());
          if(is_null($endereco) || $endereco == "") 	$ret .= "O Endereço deve ser preenchido.\n";
          elseif(strlen($endereco) > 100) 			$ret .= "O Endereço deve ter até 100 caracteres.\n";
          }

          //Numero
          $numero = $objGamesUsuario->getNumero();
          if(!is_null($numero) || $blCompleto){
          $numero = trim($objGamesUsuario->getNumero());
          if(is_null($numero) || $numero == "") 	$ret .= "O Número deve ser preenchido.\n";
          elseif(strlen($numero) > 10) 			$ret .= "O Número deve ter até 10 caracteres.\n";
          }

          //Complemento
          $complemento = $objGamesUsuario->getComplemento();
          if(!is_null($complemento) || $blCompleto){
          $complemento = trim($objGamesUsuario->getComplemento());
          if(strlen($complemento) > 100) 				$ret .= "O Complemento deve ter até 100 caracteres.\n";
          }

          //Bairro
          $bairro = $objGamesUsuario->getBairro();
          if(!is_null($bairro) || $blCompleto){
          $bairro = trim($objGamesUsuario->getBairro());
          if(is_null($bairro) || $bairro == "") 	$ret .= "O Bairro deve ser preenchido.\n";
          elseif(strlen($bairro) > 100) 			$ret .= "O Bairro deve ter até 100 caracteres.\n";
          }
         */
        //Cidade
        $cidade = $objGamesUsuario->getCidade();
        if (!is_null($cidade) || $blCompleto) {
            $cidade = trim($objGamesUsuario->getCidade());
 			if(is_null($cidade) || $cidade == "") 	$ret .= "O Cidade deve ser preenchido.\n";
 			elseif(strlen($cidade) > 100) 			$ret .= "O Cidade deve ter até 100 caracteres.\n";
        }

        //Estado
        $estado = $objGamesUsuario->getEstado();
        if (!is_null($estado) || $blCompleto) {
            $estado = trim($objGamesUsuario->getEstado());
 			if(is_null($estado) || $estado == "") 	$ret .= "O Estado deve ser preenchido.\n";
 			elseif(strlen($estado) <> 2) 			$ret .= "O Estado deve ter 2 caracteres.\n";
        }

        //CEP
        $CEP = $objGamesUsuario->getCEP();
        if (!is_null($CEP) || $blCompleto) {
            $CEP = trim($objGamesUsuario->getCEP());
	 		if(is_null($CEP) || $CEP == "")	$ret .= "O CEP deve ser preenchido.\n";
 			elseif(strlen($CEP) <> 8) 		$ret .= "O CEP deve ser no formato 00000000.\n";
 			elseif(!verifica_cepEx($CEP, false)) 		$ret .= "O CEP é inválido. Utilize o formato 00000000.\n";
        }

        //Tel DDI
        $TelDDI = $objGamesUsuario->getTelDDI();
        
        if ((!is_null($TelDDI) && $TelDDI != "") || $blCompleto) {
            $TelDDI = trim($objGamesUsuario->getTelDDI());
 			if(is_null($TelDDI) || $TelDDI == "")	$ret .= "O Código do País do Telefone deve ser preenchido.\n";
 			elseif(strlen($TelDDI) <> 2) 			$ret .= "O Código do País do Telefone deve ter 2 dígitos.\n";
 			elseif(!is_numeric($TelDDI)) 			$ret .= "O Código do País do Telefone deve ser númerico.\n";
        }

        //Tel DDD
        $TelDDD = $objGamesUsuario->getTelDDD();
        if ((!is_null($TelDDD) && $TelDDD != "") || $blCompleto) {
            $TelDDD = trim($objGamesUsuario->getTelDDD());
 			if(is_null($TelDDD) || $TelDDD == "")	$ret .= "O DDD do Telefone deve ser preenchido.\n";
 			elseif(strlen($TelDDD) <> 2) 			$ret .= "O DDD do Telefone deve ter 2 dígitos.\n";
 			elseif(!is_numeric($TelDDD)) 			$ret .= "O DDD do Telefone deve ser númerico.\n";
 			elseif($TelDDD <= 10 || ($TelDDD % 10 == 0)) $ret .= "O DDD do Telefone é inválido.\n";
        }

        //Tel 
        $Tel = $objGamesUsuario->getTel();
//echo $Tel
        if ((!is_null($Tel) &&  $Tel != "") || $blCompleto) {
            $Tel = trim($objGamesUsuario->getTel());
 			if(is_null($Tel) || $Tel == "")	$ret .= "O Telefone deve ser preenchido.\n";
            //			elseif(verifica_tel($Tel) == 0)		$ret .= "O Telefone é inválido.\n";
 			elseif(!is_numeric($Tel) || (strlen($Tel)!=8))		$ret .= "O Telefone é inválido.\n";
        }

        //Cel DDI
        $CelDDI = $objGamesUsuario->getCelDDI();
        if ((!is_null($CelDDI) && $CelDDI != "") || $blCompleto) {
            $CelDDI = trim($objGamesUsuario->getCelDDI());
 			if(is_null($CelDDI) || $CelDDI == "")	$ret .= "O Código do País do Celular deve ser preenchido.\n";
 			elseif(strlen($CelDDI) <> 2) 			$ret .= "O Código do País do Celular deve ter 2 dígitos.\n";
 			elseif(!is_numeric($CelDDI)) 			$ret .= "O Código do País do Celular deve ser númerico.\n";
        }

        //Cel DDD
        $CelDDD = $objGamesUsuario->getCelDDD();
        if (!is_null($CelDDD) || $blCompleto) {
            $CelDDD = trim($objGamesUsuario->getCelDDD());
 			if(is_null($CelDDD) || $CelDDD == "")	$ret .= "O DDD do Celular deve ser preenchido.\n";
 			elseif(strlen($CelDDD) <> 2) 			$ret .= "O DDD do Celular deve ter 2 dígitos.\n";
 			elseif(!is_numeric($CelDDD)) 			$ret .= "O DDD do Celular deve ser númerico.\n";
 			elseif($CelDDD <= 10 || ($CelDDD % 10 == 0)) $ret .= "O DDD do Celular é inválido.\n";
        }

        //Cel 
        $Cel = $objGamesUsuario->getCel();
        if (!is_null($Cel) || $blCompleto) {
            $Cel = trim($objGamesUsuario->getCel());
	 		if(is_null($Cel) || $Cel == "")	$ret .= "O Celular deve ser preenchido.\n";
// 			elseif(verifica_tel($Cel) == 0)		$ret .= "O Celular é inválido.\n";
 			elseif(!is_numeric($Cel) || ((strlen($Cel)!=8) && (strlen($Cel)!=9)))		$ret .= "O Celular é inválido.\n";
        }

        //Habbo Id
        $habboId = $objGamesUsuario->getHabboId();
        if (!is_null($habboId) || $blCompleto) {
            $habboId = trim($objGamesUsuario->getHabboId());
 			if(strlen($habboId) > 50) 				$ret .= "O Nome Habbo deve ter até 50 caracteres.\n";
        }

        return $ret;
    }

    function obter($filtro, $orderBy, &$rs) {

        $ret = "";
        $filtro = array_map("strtoupper", $filtro);

        $sql = "select * from usuarios_games ";

        if (!is_null($filtro) && $filtro != "") {

            if (isset($filtro['ug_data_inclusaoMin']) && !is_null($filtro['ug_data_inclusaoMin']) && !is_null($filtro['ug_data_inclusaoMax'])) {
                $filtro['ug_data_inclusaoMin'] = formata_data_ts($filtro['ug_data_inclusaoMin'] . " 00:00:00", 1, true, true);
                $filtro['ug_data_inclusaoMax'] = formata_data_ts($filtro['ug_data_inclusaoMax'] . " 23:59:59", 1, true, true);
            }

            if (isset($filtro['ug_data_ultimo_acessoMin']) && !is_null($filtro['ug_data_ultimo_acessoMin']) && !is_null($filtro['ug_data_ultimo_acessoMax'])) {
                $filtro['ug_data_ultimo_acessoMin'] = formata_data_ts($filtro['ug_data_ultimo_acessoMin'] . " 00:00:00", 1, true, true);
                $filtro['ug_data_ultimo_acessoMax'] = formata_data_ts($filtro['ug_data_ultimo_acessoMax'] . " 23:59:59", 1, true, true);
            }

            if (isset($filtro['ug_data_nascimentoMin']) && !is_null($filtro['ug_data_nascimentoMin']) && !is_null($filtro['ug_data_nascimentoMax'])) {
                $filtro['ug_data_nascimentoMin'] = formata_data_ts($filtro['ug_data_nascimentoMin'] . " 00:00:00", 1, true, true);
                $filtro['ug_data_nascimentoMax'] = formata_data_ts($filtro['ug_data_nascimentoMax'] . " 23:59:59", 1, true, true);
            }

            $sql .= " where 1=1";

            $sql .= " and (" . (is_null($filtro['ug_id']) ? 1 : 0);
            $sql .= "=1 or ug_id = " . SQLaddFields($filtro['ug_id'], "") . ")";

            $sql .= " and (" . (is_null($filtro['ug_ativo']) ? 1 : 0);
            $sql .= "=1 or ug_ativo = " . SQLaddFields($filtro['ug_ativo'], "") . ")";

            $sql .= " and (" . (isset($filtro['ug_data_inclusaoMin']) && is_null($filtro['ug_data_inclusaoMin']) || isset($filtro['ug_data_inclusaoMax']) && is_null($filtro['ug_data_inclusaoMax']) ? 1 : 0);
            $sql .= "=1 or ug_data_inclusao between " . SQLaddFields($filtro['ug_data_inclusaoMin'], "") . " and " . SQLaddFields($filtro['ug_data_inclusaoMax'], "") . ")";

            $sql .= " and (" . (isset($filtro['ug_data_ultimo_acessoMin']) && is_null($filtro['ug_data_ultimo_acessoMin']) || is_null($filtro['ug_data_ultimo_acessoMax']) ? 1 : 0);
            $sql .= "=1 or ug_data_ultimo_acesso between " . SQLaddFields($filtro['ug_data_ultimo_acessoMin'], "") . " and " . SQLaddFields($filtro['ug_data_ultimo_acessoMax'], "") . ")";

            $sql .= " and (" . (is_null($filtro['ug_qtde_acessosMin']) || is_null($filtro['ug_qtde_acessosMax']) ? 1 : 0);
            $sql .= "=1 or ug_qtde_acessos between " . SQLaddFields($filtro['ug_qtde_acessosMin'], "") . " and " . SQLaddFields($filtro['ug_qtde_acessosMax'], "") . ")";

            $sql .= " and (" . (is_null($filtro['ug_email']) ? 1 : 0);
            $sql .= "=1 or ug_email = '" . SQLaddFields($filtro['ug_email'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_emailLike']) ? 1 : 0);
            $sql .= "=1 or ug_email like '%" . SQLaddFields($filtro['ug_emailLike'], "r") . "%')";

            $sql .= " and (" . (is_null($filtro['ug_nome']) ? 1 : 0);
            $sql .= "=1 or ug_nome = '" . SQLaddFields($filtro['ug_nome'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_nomeLike']) ? 1 : 0);
            $sql .= "=1 or ug_nome like '%" . SQLaddFields($filtro['ug_nomeLike'], "r") . "%')";

            $sql .= " and (" . (is_null($filtro['ug_cpf']) ? 1 : 0);
            $sql .= "=1 or ug_cpf = '" . SQLaddFields($filtro['ug_cpf'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_rg']) ? 1 : 0);
            $sql .= "=1 or ug_rg = '" . SQLaddFields($filtro['ug_rg'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_data_nascimentoMin']) || is_null($filtro['ug_data_nascimentoMax']) ? 1 : 0);
            $sql .= "=1 or ug_data_nascimento between " . SQLaddFields($filtro['ug_data)nascimentoMin'], "") . " and " . SQLaddFields($filtro['ug_data_nascimentoMax'], "") . ")";

            $sql .= " and (" . (is_null($filtro['ug_sexo']) ? 1 : 0);
            $sql .= "=1 or ug_sexo = '" . SQLaddFields($filtro['ug_sexo'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_tipo_end']) ? 1 : 0);
            $sql .= "=1 or ug_tipo_end = '" . SQLaddFields($filtro['ug_tipo_end'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_endereco']) ? 1 : 0);
            $sql .= "=1 or ug_endereco = '" . SQLaddFields($filtro['ug_endereco'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_enderecoLike']) ? 1 : 0);
            $sql .= "=1 or ug_endereco like '%" . SQLaddFields($filtro['ug_enderecoLike'], "r") . "%')";

            $sql .= " and (" . (is_null($filtro['ug_numero']) ? 1 : 0);
            $sql .= "=1 or ug_numero = '" . SQLaddFields($filtro['ug_numero'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_complemento']) ? 1 : 0);
            $sql .= "=1 or ug_complemento = '" . SQLaddFields($filtro['ug_complemento'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_bairro']) ? 1 : 0);
            $sql .= "=1 or ug_bairro = '" . SQLaddFields($filtro['ug_bairro'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_bairroLike']) ? 1 : 0);
            $sql .= "=1 or ug_bairro like '%" . SQLaddFields($filtro['ug_bairroLike'], "r") . "%')";

            $sql .= " and (" . (is_null($filtro['ug_cidade']) ? 1 : 0);
            $sql .= "=1 or ug_cidade = '" . SQLaddFields($filtro['ug_cidade'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_cidadeLike']) ? 1 : 0);
            $sql .= "=1 or ug_cidade like '%" . SQLaddFields($filtro['ug_cidadeLike'], "r") . "%')";

            $sql .= " and (" . (is_null($filtro['ug_estado']) ? 1 : 0);
            $sql .= "=1 or ug_estado = '" . SQLaddFields($filtro['ug_estado'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_cep']) ? 1 : 0);
            $sql .= "=1 or ug_cep = '" . SQLaddFields($filtro['ug_cep'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_tel_ddi']) ? 1 : 0);
            $sql .= "=1 or ug_tel_ddi = '" . SQLaddFields($filtro['ug_tel_ddi'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_tel_ddd']) ? 1 : 0);
            $sql .= "=1 or ug_tel_ddd = '" . SQLaddFields($filtro['ug_tel_ddd'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_tel']) ? 1 : 0);
            $sql .= "=1 or ug_tel = '" . SQLaddFields($filtro['ug_tel'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_telLike']) ? 1 : 0);
            $sql .= "=1 or ug_tel like '%" . SQLaddFields($filtro['ug_telLike'], "r") . "%')";

            $sql .= " and (" . (is_null($filtro['ug_cel_ddi']) ? 1 : 0);
            $sql .= "=1 or ug_cel_ddi = '" . SQLaddFields($filtro['ug_cel_ddi'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_cel_ddd']) ? 1 : 0);
            $sql .= "=1 or ug_cel_ddd = '" . SQLaddFields($filtro['ug_cel_ddd'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_cel']) ? 1 : 0);
            $sql .= "=1 or ug_cel = '" . SQLaddFields($filtro['ug_cel'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_celLike']) ? 1 : 0);
            $sql .= "=1 or ug_cel like '%" . SQLaddFields($filtro['ug_celLike'], "r") . "%')";

            $sql .= " and (" . (is_null($filtro['ug_habbo_id']) ? 1 : 0);
            $sql .= "=1 or ug_habbo_id = '" . SQLaddFields($filtro['ug_habbo_id'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_habbo_idLike']) ? 1 : 0);
            $sql .= "=1 or ug_habbo_id like '%" . SQLaddFields($filtro['ug_habbo_idLike'], "r") . "%')";

            $sql .= " and (" . (is_null($filtro['ug_news_letter']) ? 1 : 0);
            $sql .= "=1 or ug_news = '" . SQLaddFields($filtro['ug_news_letter'], "r") . "')";

            $sql .= " and (" . (is_null($filtro['ug_news_letterLike']) ? 1 : 0);
            $sql .= "=1 or ug_news like '%" . SQLaddFields($filtro['ug_news_letterLike'], "r") . "%')";

            // Competicao
            // ug_compet_lh_ug_id, ug_compet_jogo, ug_compet_aceito_regulamento, ug_compet_aceito_data_aceito, 
            $sql .= " and (" . (is_null($filtro['ug_compet_lh_ug_id']) ? 1 : 0);
            $sql .= "=1 or ug_compet_lh_ug_id = " . SQLaddFields($filtro['ug_compet_lh_ug_id'], "") . ")";

            // Pagamento com Cielo
            $sql .= " and (" . (is_null($filtro['ug_use_cielo']) ? 1 : 0);
            $sql .= "=1 or ug_use_cielo = " . SQLaddFields($filtro['ug_use_cielo'], "") . ")";
            /*
              $sql .= " and (" . (is_null($filtro['ug_compet_jogo'])?1:0);
              $sql .= "=1 or ug_compet_jogo = " . SQLaddFields($filtro['ug_compet_jogo'], "") . ")";

              $sql .= " and (" . (is_null($filtro['ug_compet_aceito_regulamento'])?1:0);
              $sql .= "=1 or ug_compet_aceito_regulamento = '" . SQLaddFields($filtro['ug_compet_aceito_regulamento'], "") . "')";

              $sql .= " and (" . (is_null($filtro['ug_compet_aceito_data_aceito'])?1:0);
              $sql .= "=1 or ug_compet_aceito_data_aceito = '" . SQLaddFields($filtro['ug_compet_aceito_data_aceito'], "") . "')";
             */
        }

		if(!is_null($orderBy)) $sql .= " order by " . $orderBy;
//gravaLog_DebugTMP("Cria usuário : \n  $sql \n");

        $rs = SQLexecuteQuery($sql);
		if(!$rs) $ret = "Erro ao obter usuário(s).\n";

        return $ret;

    }

    function existeEmail($email0, $usuario_id_excessao) {

        $ret = true;
        $email = $email0;

        $err_cod = "";

        $params = array('email' => array('0' => $email,
                '1' => 'S',
                '2' => '1'
            )
        );
        $params = sanitize_input_data_array($params, $err_cod);
        extract($params, EXTR_OVERWRITE);

        $email = strtoupper(trim($email));

        //SQL
        $sql = "select count(*) as qtde from usuarios_games ";
        $sql .= " where ug_email = " . SQLaddFields($email, "s");
        if ($usuario_id_excessao && !is_null($usuario_id_excessao) && is_numeric($usuario_id_excessao))
            $sql .= " and ug_id <> " . SQLaddFields(trim($usuario_id_excessao), "");

        $rs = SQLexecuteQuery($sql);
        if ($rs && pg_num_rows($rs) > 0) {
            $rs_row = pg_fetch_array($rs);
			if($rs_row['qtde'] == 0) $ret = false;
        }
//gravaLog_Temporario("SQL UsuarioGames::existeEmail('$email0' -> '$email', ".(($usuario_id_excessao)?"TRUE":"FALSE").") => ".(($ret)?"TRUE":"FALSE")." (qtde: ".$rs_row['qtde'].")\n  ".$sql."\n");

        return $ret;
    }
    
    public static function existeLogin($login,$id_excessao = null) {
        
        $sql = "select count(ug_id) from usuarios_games where UPPER(ug_login) = UPPER(:ug_login)";

        if($id_excessao)
            $sql .= " and ug_id <> ".$id_excessao;
        //Inicializando conexao PDO
        $con = ConnectionPDO::getConnection();
        $pdo = $con->getLink();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        //passando a query de select
        $rs = $pdo->prepare($sql);
        
        //bindando os valores com a referencia, variavel e tipo
        $login = strtoupper($login);
        $param = ':ug_login';
        $rs->bindParam($param, $login, PDO::PARAM_STR);
        
        //executando query
        $rs->execute();
        
        //retornando quantidade de registros
        return ($rs->fetchColumn() > 0) ? true : false;
    }
	
	public static function existeCPFCadastro($cpf) {
        
        $sql = "select count(ug_id) as contas from usuarios_games where ug_cpf = :ug_cpf;";

        //Inicializando conexao PDO
        $con = ConnectionPDO::getConnection();
        $pdo = $con->getLink();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        //passando a query de select
        $rs = $pdo->prepare($sql);
        $param = ':ug_cpf';
        $rs->bindParam($param, $cpf, PDO::PARAM_STR);
        //executando query
        $rs->execute();
		
		$info = $rs->fetch(PDO::FETCH_ASSOC);
		
		if($info != false){
			if($info["contas"] >= 2){
				return true;
			}
			return false;
		}else{
			return false;
		}
        
        //retornando quantidade de registros
       // return ($rs->fetchColumn() > 0) ? true : false;
    }

    function existeEmail_get_ID($email) {

        $ret_id = 0;
        $email = strtoupper(trim($email));

        //SQL
        $sql = "select ug_id from usuarios_games ";
        $sql .= " where ug_email = " . SQLaddFields($email, "s");
//echo "$sql<br>";

        $rs = SQLexecuteQuery($sql);
        if ($rs && pg_num_rows($rs) > 0) {
            $rs_row = pg_fetch_array($rs);
            $ret_id = $rs_row['ug_id'];
        }

        return $ret_id;
    }
    

    function existeCPF($cpf, $usuario_id_excessao) {

        $ret = true;

        //SQL
        $sql = "select count(*) as qtde from usuarios_games ";
        $sql .= " where ug_cpf IS NOT NULL and ug_cpf <> '' and ug_cpf = " . SQLaddFields(trim($cpf), "s");
        if ($usuario_id_excessao && !is_null($usuario_id_excessao) && is_numeric($usuario_id_excessao))
            $sql .= " and ug_id <> " . SQLaddFields(trim($usuario_id_excessao), "");

        $rs = SQLexecuteQuery($sql);
        if ($rs && pg_num_rows($rs) > 0) {
            $rs_row = pg_fetch_array($rs);
			if($rs_row['qtde'] == 0) $ret = false;
        }

        return $ret;
    }

    function existeRG($rg, $usuario_id_excessao) {

        return false;

        $ret = true;

        //SQL
        $sql = "select count(*) as qtde from usuarios_games ";
        $sql .= " where ug_rg IS NOT NULL and ug_rg <> '' and ug_rg = " . SQLaddFields(trim($rg), "s");
        if ($usuario_id_excessao && !is_null($usuario_id_excessao) && is_numeric($usuario_id_excessao))
            $sql .= " and ug_id <> " . SQLaddFields(trim($usuario_id_excessao), "");
//echo $sql."<br>";
        $rs = SQLexecuteQuery($sql);
        if ($rs && pg_num_rows($rs) > 0) {
            $rs_row = pg_fetch_array($rs);
			if($rs_row['qtde'] == 0) $ret = false;
        }

        return $ret;
    }

    function autenticarLogin($login, $senha) {
        $senha0 = $senha;

        $ret = false;

        $err_cod = "";

        /*
        $params = array('login' => array('0' => $login,
                '1' => 'S',
                '2' => '1'
            )
        );
        $params = sanitize_input_data_array($params, $err_cod);
        extract($params, EXTR_OVERWRITE);
        */

        //Autentica usuario
        //------------------------------------------------------------------
        $objEncryption = new Encryption();
        $senha = $objEncryption->encrypt(trim($senha));
        $login = strtoupper(trim($login));
        /*
        //SQL
        $sql = "select count(*) as qtde from usuarios_games ";
        $sql .= " where ug_ativo = 1 ";
        $sql .= " and ug_email = " . SQLaddFields($login, "s");
        $sql .= " and ug_senha = " . SQLaddFields($senha, "s");
        */
        $sql = "SELECT count(*) as qtde FROM usuarios_games WHERE ug_ativo = 1 AND ug_email = ? AND ug_senha = ? ";

        $con = ConnectionPDO::getConnection();
        $pdo = $con->getLink();

        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($login, $senha));
        $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ( $fetch[0]['qtde'] > 0 ) {
            $ret = true;
        }

        /*$rs = SQLexecuteQuery($sql);
        if ($rs && pg_num_rows($rs) > 0) {
            $rs_row = pg_fetch_array($rs);
			if($rs_row['qtde'] > 0) $ret = true;
        }*/

        if ($ret) {

            //Adiciona objeto usuario no session
            $instUsuarioGames = new UsuarioGames();
            $ret = $instUsuarioGames->adicionarLoginSession($login);

            //Atualiza ultimo acesso
            //------------------------------------------------------------------
            //SQL
            $sql = "update usuarios_games set ";
            $sql .= " ug_data_ultimo_acesso = CURRENT_TIMESTAMP,";
            $sql .= " ug_qtde_acessos = ug_qtde_acessos + 1 ";
            $sql .= " where ug_email = " . SQLaddFields($login, "s");
            $rs = SQLexecuteQuery($sql);

            //Log na base
            usuarios_games_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['LOGIN'], null, null);
			
        } else {
            gravaLog_Login("Login de gamer falhou ($senha0): '$sql'.\n", true);
        }

        return $ret;
    }

    function autenticarUgLogin($login, $senha) {

        $ret = false;

        if (!filter_var($login, FILTER_VALIDATE_EMAIL)) {
            
            //Autentica usuario
            //------------------------------------------------------------------
            $objEncryption = new Encryption();
            $senha = $objEncryption->encrypt(trim($senha));
            $login = strtoupper(trim($login));
            //SQL
            $sql = "SELECT count(*) as qtde, ug_email, ug_id, ug_nome FROM usuarios_games WHERE ug_ativo = 1 AND UPPER(ug_login) = ? AND ug_senha = ? group by ug_email";

            $con = ConnectionPDO::getConnection();
            $pdo = $con->getLink();

            $stmt = $pdo->prepare($sql);
            $stmt->execute(array($login, $senha));
            $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($fetch[0]['qtde'] > 0) {
                
				if($_SERVER["REMOTE_ADDR"] == "201.93.162.169") {
					$two_factor = new TwoFactorAuthenticator('USER', $fetch[0]["ug_id"], $fetch[0]["ug_nome"], $fetch[0]["email"]);
					
					$two_factor->sendEmail();
					
				} 
				else {
					 //Adiciona objeto usuario no session
					$instUsuarioGames = new UsuarioGames();
					$ret = $instUsuarioGames->adicionarLoginSession($fetch[0]['ug_email']);

					//Atualiza ultimo acesso
					$sql = "update usuarios_games 
								set ug_data_ultimo_acesso = CURRENT_TIMESTAMP, 
									ug_qtde_acessos = ug_qtde_acessos + 1
							where UPPER(ug_login) = UPPER(?) ";
					$stmt2 = $pdo->prepare($sql);
					$stmt2->execute(array($login));
	 
					//Log na base
					usuarios_games_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['LOGIN'], null, null);
				}
            }
            else {
                gravaLog_Login("Login de gamer falhou ($senha): '$sql'.\n", true);
            }

        }//end if (!filter_var($login, FILTER_VALIDATE_EMAIL))
        
        return $ret;
        
    }//end function autenticarUgLogin

    /* Wagner -- Métodos novos login integração */
    function autenticarLoginIntegracao($login, $senha) {
        $ret = false;
        $err_cod = "";
        $params = array('login' => array('0' => $login,
                '1' => 'S',
                '2' => '1'
            )
        );
        $params = sanitize_input_data_array($params, $err_cod);
        extract($params, EXTR_OVERWRITE);

        //Autentica usuario
        //------------------------------------------------------------------
        $objEncryption = new Encryption();
        $senha = $objEncryption->encrypt(trim($senha));
        $login = strtoupper(trim($login));

        //SQL
        $sql = "select count(*) as qtde from usuarios_games ";
        $sql .= " where ug_ativo = 1 ";
        $sql .= " and ug_email = " . SQLaddFields($login, "s");
        $sql .= " and ug_senha = " . SQLaddFields($senha, "s");

        $rs = SQLexecuteQuery($sql);
        if ($rs && pg_num_rows($rs) > 0) {
            $rs_row = pg_fetch_array($rs);
			if($rs_row['qtde'] > 0) $ret = true;
        } //end if($rs && pg_num_rows($rs) > 0)			

        if ($ret) {
            //testar se é integracao retonando false caso negativo
            if (function_exists('get_Integracao_order_id_is_sessao_logged')) {
                if (get_Integracao_order_id_is_sessao_logged() <> '') {
                    //Adiciona sinalizador no session
                    $GLOBALS['_SESSION']['integracao_autenticado'] = '1';
                    //Atualiza ultimo acesso
                    //------------------------------------------------------------------
                    //SQL
                    $sql = "update usuarios_games set ";
                    $sql .= " ug_data_ultimo_acesso = CURRENT_TIMESTAMP ";
                    $sql .= " where ug_email = " . SQLaddFields($login, "s");
                    $rs = SQLexecuteQuery($sql);
                    //Log na base
                    usuarios_games_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['LOGIN_INTEGRACAO'], null, null);
                }//end if(get_Integracao_order_id_is_sessao_logged()<>'')
				else $ret = false;
            } //end if(function_exists('get_Integracao_order_id_is_sessao_logged'))
			else $ret = false;
        } //end if($ret)
        return $ret;
    }// function autenticarLoginIntegracao($login, $senha)

    /* Wagner -- fim bloco -- Métodos novos login integração */

    function getUsuarioGamesById($usuario_id) {

		if(!$usuario_id || $usuario_id == "" || !is_numeric($usuario_id)) return null;

        $rs = null;
        $filtro['ug_id'] = $usuario_id;
        //$filtro['ug_ativo'] = 1;
        $instUsuarioGames = new UsuarioGames();
        $ret = $instUsuarioGames->obter($filtro, null, $rs);
        return $instUsuarioGames->create($rs);
		
    }

    function getUsuarioGamesByLogin($login) {

		if(!$login || $login == "") return null;

        $rs = null;
        $filtro['ug_login'] = $login;
        //$filtro['ug_ativo'] = 1;
        $ret = UsuarioGames::obter($filtro, null, $rs);

        return UsuarioGames::create($rs);
		
    }

	
    function adicionarLoginSession($login) {

		if(!$login || $login == "") return false;

        $rs = null;
        $filtro['ug_email'] = $login;
        $filtro['ug_ativo'] = 1;
        
        $instUsuarioGames = new UsuarioGames();
        $ret = $instUsuarioGames->obter($filtro, null, $rs);
        
        $usuarioGames = $instUsuarioGames->create($rs);

        if ($usuarioGames != null) {
            $ret = true;

            //Poe no session				
            $GLOBALS['_SESSION']['usuarioGames_ser'] = serialize($usuarioGames);
            $GLOBALS['_SESSION']['usuarioGames.horarioLogin'] = date("U");
            $GLOBALS['_SESSION']['usuarioGames.horarioInatividade'] = date("U");

            $GLOBALS['_SESSION']['integracao_is_parceiro'] = "";
            $GLOBALS['_SESSION']['integracao_origem_id'] = "";
            $GLOBALS['_SESSION']['integracao_order_id'] = "";
            $GLOBALS['_SESSION']['integracao_autenticado'] = "";

        } else {
            $ret = false;
        }

        return $ret;
    }

    function create($rs) {

        $usuarioGames = null;

        if ($rs && pg_num_rows($rs) > 0) {
            $rs_row = pg_fetch_array($rs);
            $usuarioGames = new UsuarioGames();
            $usuarioGames->setId($rs_row['ug_id']);
            $usuarioGames->setLogin($rs_row['ug_login']);
            $usuarioGames->setSenha($rs_row['ug_senha']);
            $usuarioGames->setAtivo($rs_row['ug_ativo']);
            $usuarioGames->setDataInclusao(formata_data_ts($rs_row['ug_data_inclusao'], 0, true, false));
            $usuarioGames->setDataUltimoAcesso(formata_data_ts($rs_row['ug_data_ultimo_acesso'], 0, true, false));
            $usuarioGames->setQtdeAcessos($rs_row['ug_qtde_acessos']);
            $usuarioGames->setEmail($rs_row['ug_email']);
            $usuarioGames->setNome($rs_row['ug_nome']);
            $usuarioGames->setCPF($rs_row['ug_cpf']);
            $usuarioGames->setRG($rs_row['ug_rg']);
            if($rs_row['ug_data_nascimento'] != '') $usuarioGames->setDataNascimento(formata_data_ts($rs_row['ug_data_nascimento'], 0, true, true));
            $usuarioGames->setSexo($rs_row['ug_sexo']);
            $usuarioGames->setTipoEnd($rs_row['ug_tipo_end']);
            $usuarioGames->setEndereco($rs_row['ug_endereco']);
            $usuarioGames->setNumero($rs_row['ug_numero']);
            $usuarioGames->setComplemento($rs_row['ug_complemento']);
            $usuarioGames->setBairro($rs_row['ug_bairro']);
            $usuarioGames->setCidade($rs_row['ug_cidade']);
            $usuarioGames->setEstado($rs_row['ug_estado']);
            $usuarioGames->setCEP($rs_row['ug_cep']);
            $usuarioGames->setTelDDI($rs_row['ug_tel_ddi']);
            $usuarioGames->setTelDDD($rs_row['ug_tel_ddd']);
            $usuarioGames->setTel($rs_row['ug_tel']);
            $usuarioGames->setCelDDI($rs_row['ug_cel_ddi']);
            $usuarioGames->setCelDDD($rs_row['ug_cel_ddd']);
            $usuarioGames->setCel($rs_row['ug_cel']);
            $usuarioGames->setHabboId($rs_row['ug_habbo_id']);
            $usuarioGames->setNewsLetter($rs_row['ug_news_letter']);

//			$objEncryption = new Encryption();
//			$usuarioGames->setPerfilSaldo($objEncryption->decrypt(trim($rs_row['ug_perfil_saldo'])));
            $usuarioGames->setPerfilSaldo($rs_row['ug_perfil_saldo']);

            $usuarioGames->setCompet_lh_ug_id($rs_row['ug_compet_lh_ug_id']);
            $usuarioGames->setCompet_jogo($rs_row['ug_compet_jogo']);
            $usuarioGames->setCompet_aceito_regulamento($rs_row['ug_compet_aceito_regulamento']);
            $usuarioGames->setCompet_aceito_data_aceito($rs_row['ug_compet_aceito_data_aceito']);
            $usuarioGames->setUseCielo($rs_row['ug_use_cielo']);
            /*
              if($rs_row['ug_id']==9093) {
              gravaLog_DebugTMP("==== ug_use_cielo: \n    rs_row['ug_id']: ".$rs_row['ug_id']."\n   rs_row['ug_use_cielo']: ".$rs_row['ug_use_cielo']."\n    usuarioGames->getUseCielo(): ".$usuarioGames->getUseCielo()."\n   ".print_r($rs_row, true)." \n");
              } else {
              gravaLog_DebugTMP("==== ug_use_cielo: \n    rs_row['ug_id']: ".$rs_row['ug_id']."\n   rs_row['ug_use_cielo']: ".$rs_row['ug_use_cielo']."\n    usuarioGames->getUseCielo(): ".$usuarioGames->getUseCielo()."\n");
              }
             */

            $usuarioGames->setSaldoFidelizacao($rs_row['ug_saldo_fidelizacao']);
            $usuarioGames->setCategoriaFidelizacao($rs_row['ug_categoria_fidelizacao']);
            //Buscando Informações de observações
            $sql = "SELECT to_char(ugo_data,'DD/MM/YYYY HH24:MI:SS') as data,* FROM usuarios_games_obs WHERE ug_id = ".$rs_row['ug_id'].";";
            $rs_usuario_obs = SQLexecuteQuery($sql);
            $ug_obs = NULL;
            if(pg_num_rows($rs_usuario_obs) > 0) { 
                    while($rs_usuario_obs_row = pg_fetch_array($rs_usuario_obs)) {
                        $ug_obs .= "Em ".$rs_usuario_obs_row['data'].PHP_EOL."Autor: ".$rs_usuario_obs_row['ugo_user_insert'].PHP_EOL."Observação:".PHP_EOL.$rs_usuario_obs_row['ug_obs'].PHP_EOL.str_repeat("-",40).PHP_EOL;
                    }//end while
            } //end if(pg_num_rows($rs_usuario) > 0)
            $usuarioGames->setOBS($ug_obs);
            $usuarioGames->setNomedaMae($rs_row['ug_nome_da_mae']);
            $usuarioGames->setNomeCPF($rs_row['ug_nome_cpf']);
        }
        return $usuarioGames;
    }
	
	function adicionarLoginSessionDjx($ug_id) {
		
	}

    function adicionarLoginSession_ByID($ug_id) {

		if(!$ug_id || $ug_id == "") return false;

        $rs = null;
        $filtro['ug_id'] = $ug_id;
        $filtro['ug_ativo'] = 1;
        $ret = (new UsuarioGames)->obter($filtro, null, $rs);
        /*
          if($ug_id==9093) {
          echo "<pre>";
          print_r($ret);
          echo "</pre>";
          }
         */
        if ($rs && pg_num_rows($rs) > 0) {
            $ret = true;
            $rs_row = pg_fetch_array($rs);
            $usuarioGames = new UsuarioGames();
            $usuarioGames->setId($rs_row['ug_id']);
            $usuarioGames->setLogin($rs_row['ug_login']);
            $usuarioGames->setSenha($rs_row['ug_senha']);
            $usuarioGames->setAtivo($rs_row['ug_ativo']);
            $usuarioGames->setDataInclusao(formata_data_ts($rs_row['ug_data_inclusao'], 0, true, false));
            $usuarioGames->setDataUltimoAcesso(formata_data_ts($rs_row['ug_data_ultimo_acesso'], 0, true, false));
            $usuarioGames->setQtdeAcessos($rs_row['ug_qtde_acessos']);
            $usuarioGames->setEmail($rs_row['ug_email']);
            $usuarioGames->setNome($rs_row['ug_nome']);
            $usuarioGames->setCPF($rs_row['ug_cpf']);
            $usuarioGames->setRG($rs_row['ug_rg']);
            $usuarioGames->setDataNascimento(formata_data_ts($rs_row['ug_data_nascimento'], 0, true, true));
            $usuarioGames->setSexo($rs_row['ug_sexo']);
            $usuarioGames->setTipoEnd($rs_row['ug_tipo_end']);
            $usuarioGames->setEndereco($rs_row['ug_endereco']);
            $usuarioGames->setNumero($rs_row['ug_numero']);
            $usuarioGames->setComplemento($rs_row['ug_complemento']);
            $usuarioGames->setBairro($rs_row['ug_bairro']);
            $usuarioGames->setCidade($rs_row['ug_cidade']);
            $usuarioGames->setEstado($rs_row['ug_estado']);
            $usuarioGames->setCEP($rs_row['ug_cep']);
            $usuarioGames->setTelDDI($rs_row['ug_tel_ddi']);
            $usuarioGames->setTelDDD($rs_row['ug_tel_ddd']);
            $usuarioGames->setTel($rs_row['ug_tel']);
            $usuarioGames->setCelDDI($rs_row['ug_cel_ddi']);
            $usuarioGames->setCelDDD($rs_row['ug_cel_ddd']);
            $usuarioGames->setCel($rs_row['ug_cel']);
            $usuarioGames->setHabboId($rs_row['ug_habbo_id']);
            $usuarioGames->setNewsLetter($rs_row['ug_news']);

            $usuarioGames->setPerfilSaldo($rs_row['ug_perfil_saldo']);

            $usuarioGames->setCompet_lh_ug_id($rs_row['ug_compet_lh_ug_id']);
            $usuarioGames->setCompet_jogo($rs_row['ug_compet_jogo']);
            $usuarioGames->setCompet_aceito_regulamento($rs_row['ug_compet_aceito_regulamento']);
            $usuarioGames->setCompet_aceito_data_aceito($rs_row['ug_compet_aceito_data_aceito']);

            $usuarioGames->setUseCielo($rs_row['ug_use_cielo']);

            $usuarioGames->setSaldoFidelizacao($rs_row['ug_saldo_fidelizacao']);
            $usuarioGames->setCategoriaFidelizacao($rs_row['ug_categoria_fidelizacao']);

            //Salva no session				
            $GLOBALS['_SESSION']['usuarioGames_ser'] = serialize($usuarioGames);
            $GLOBALS['_SESSION']['usuarioGames.horarioLogin'] = date("U");
            $GLOBALS['_SESSION']['usuarioGames.horarioInatividade'] = date("U");
        } else {
            $ret = false;
        }

        return $ret;
    }


    function alterarSenha($senha, $senhaAtual, $login) {

        $ret = false;

        //Autentica usuario
        //------------------------------------------------------------------
        $objEncryption = new Encryption();
        $senha = $objEncryption->encrypt(trim($senha));
        $senhaAtual = $objEncryption->encrypt(trim($senhaAtual));
        $login = strtoupper(trim($login));

        //SQL
        $sql = "select count(*) as qtde from usuarios_games ";
        $sql .= " where ug_email = " . SQLaddFields($login, "s");
        $sql .= " and ug_senha = " . SQLaddFields($senhaAtual, "s");

        $rs = SQLexecuteQuery($sql);
        if ($rs && pg_num_rows($rs) > 0) {
            $rs_row = pg_fetch_array($rs);
			if($rs_row['qtde'] > 0) $ret = true;
        }

        //Atualiza ultimo acesso
        //------------------------------------------------------------------
        if ($ret) {
            //SQL
            $sql = "update usuarios_games set ";
            $sql .= " ug_senha = " . SQLaddFields($senha, "s");
            $sql .= " where ug_email = " . SQLaddFields($login, "s");
            $sql .= " and ug_senha = " . SQLaddFields($senhaAtual, "s");
            $ret = SQLexecuteQuery($sql);

            if ($ret) {

                //Log na base
                usuarios_games_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['TROCA_DE_SENHA'], null, null);

                //Envia email
                //--------------------------------------------------------------------------------
                $usuarioGames = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']);
                /*
                  $parametros['prepag_dominio'] = "http://www.e-prepag.com.br";
                  $parametros['nome'] = $usuarioGames->getNome();
                  $parametros['sexo'] = $usuarioGames->getSexo();

                  $msgEmail  = email_cabecalho($parametros);
                  $msgEmail .= "  <br><br>
                  <table border='0' cellspacing='0'>
                  <tr><td>&nbsp;</td></tr>
                  <tr valign='middle' bgcolor='#FFFFFF'>
                  <td align='left' class='texto'>
                  Você acessou nosso site e alterou sua senha.<br><br>
                  Utilize seu email " . $usuarioGames->getEmail() . " para acessar sua conta e realizar compras em nosso site.<br><br>
                  </td>
                  </tr>
                  <tr><td>&nbsp;</td></tr>
                  </table>
                  ";
                  $msgEmail .= email_rodape($parametros);
                  enviaEmail($usuarioGames->getEmail(), null, null, "E-Prepag - Alteração de Senha", $msgEmail);
                 */

                /* ---Wagner */
                if (is_object($usuarioGames)) {
                    $objEnvioEmailAutomatico = new EnvioEmailAutomatico(TIPO_USUARIO_GAMER, 'AlteracaoSenha');
                    $objEnvioEmailAutomatico->setUgID($usuarioGames->getId());
                    $objEnvioEmailAutomatico->MontaEmailEspecifico();
                }
				
            }
        }

        return $ret;
    }

    // Ver get_lista_usuarios_VIP() para usar a lista VIP sem criar instância da classe
    function b_IsLogin_pagamento_vip($op = null, &$aret = null) {

        // 19090 - "ELIANA AP.ANDREOTTI PETTA", 9845 - "REINALDO PÉREZ SÁNCHEZ KAIZEN", 16265 - "SILVIA CRISTINA PARISOTTO BALDIN" 
        // 55430 - "NIKOLAS VINICIUS DE OLIVEIRA"

        // adicionado em 2012-07-25 
        //	55124  - "MARCIO ALBERTO"
        //	34785  - "CLAUDIO AUGUSTO ROSA DAVID"
        //	112046 - "RAZAR MOTOROLA"
        //	48462  - "FRANCISCO DE ASSIS GALEPE"

        // adicionado 2013-01-23
        //	49589, 2182, 89860, 133265, 55031, 156625, 38978, 121029, 94568, 128815, 4030, 42175, 152869

        // 2013-04-01
        //	195456 - MRVFOTOGRAFIAS@OI.COM.BR 
        // adicionado 2014-01-09
        //      339998 
        // 2015-03-11     
        //      24092 - LEANDRO_SOKEM@HOTMAIL.COM
        // 2015-03-13
        //      46198 - JOAO.TREVISAN+066@E-PREPAG.COM
        //33105	JOAO BATISTA GONCALVES DA SILVA
        //647539	RODRIGO MOREIRA CESAR
        //568667	DOUGLAS BISCHOFF
        //626049	Jackson Leite Paulo
        //541858	JOANA FRANCISCA DE CARVALHO
        //197491	VITAL JOSE DA SILVA FILHO
        //324815	ARTHUR ARAUJO DA SILVA COELHO
        //52030		Priscila Sanches
        //599703	GUSTAVORODRIGO456@GMAIL.COM
        //629217	Diego Barbosa Marques
        //184723	ENOQUE AMORIM DA SILVA NETO
        //544704	J.J.SOARESDASILVAMODAS@HOTMAIL.COM
        //625467	CFLATINO@OUTLOOK.COM
        //622017	Tiago Marques
        //421717	HENRIQUE HATANO
        //38812		JOSIAS NETTO
        //472850	SARAIVA.CROSSFIRE@OUTLOOK.COM
        //527404	RUBAOCONTAS@HOTMAIL.COM
        //633080	XINJINWU@LIVE.CN
        //9016		TAMY@E-PREPAG.COM
        //515601
        //1135164       FELIPESDSPONTES@GMAIL.COM
        //1211443       Enviado somente ID por email rc@e-prepag.com.br qua 02/09/2020 13:33

        $usuarios_pagamento_online_vip_id = array(19090, 9845, 16265, 55430,
            55124, 34785, 112046, 48462,
            49589, 2182, 89860, 133265, 55031, 156625, 38978, 121029, 94568, 128815, 4030, 42175, 152869,
            195456, 339998, 24092, 46198,
            33105,647539,568667,626049,541858,197491,324815,52030,599703,629217,184723,544704,625467,622017,421717,38812,472850,527404,633080,
            9016, 515601, 829346, 1135164, 1211443,
        );
        if ($op == 1) {
            $aret = $usuarios_pagamento_online_vip_id;
        }

        if (in_array(strtoupper($this->getId()), $usuarios_pagamento_online_vip_id)) {
            return true;
        }
        return false;
    }

    function b_IsLogin_pagamento_free() {

        // 53916 - "WAGNER DE MIRANDA" 
        $usuarios_pagamento_online_free_id = array(53916);

        if (in_array(strtoupper($this->getId()), $usuarios_pagamento_online_free_id)) {
            return true;
        }
        return false;
    }

    function b_IsLogin_pagamento() {
        /*
          $usuarios_liberados = array("WAGNER@E-PREPAG.COM.BR", "GLAUCIA@E-PREPAG.COM.BR", "ODECIO@GREGIO.COM.BR");

          if(in_array(strtoupper($this->getEmail()), $usuarios_liberados)) {
          return true;
          }
          return false;
         */
        // Libera para todos os usuários
        return true;
    }

    function b_IsLogin_Reinaldo() {

        if (strtoupper($this->getEmail()) == "WAGNER@E-PREPAG.COM.BR") {
            return true;
        }
        return false;

    }

    function b_IsLogin_Wagner() {

        if (strtoupper($this->getEmail()) == "WAGNER@E-PREPAG.COM.BR") {
            return true;
        }
        return false;

    }

    function b_IsLogin_Tamy() {

        if (strtoupper($this->getEmail()) == "TAMY@E-PREPAG.COM.BR") {
            return true;
        }
        return false;

    }

    function b_IsLogin_Glaucia() {

        if (strtoupper($this->getEmail()) == "GLAUCIA@E-PREPAG.COM.BR") {
            return true;
        }
        return false;

    }

    function b_IsLogin_pagamento_minimo_1_real() {

        $aIsLogin_pagamento_mini_1_real = array("WAGNER@E-PREPAG.COM.BR", "GLAUCIA@E-PREPAG.COM.BR", "ODECIO@GREGIO.COM.BR", "TESTE_SCOPUS@TEST.COM");
        if (in_array(strtoupper($this->getEmail()), $aIsLogin_pagamento_mini_1_real)) {
            return true;
        }
        return false;
    }

    function b_IsLogin_pagamento_em_carteira() {
        return true;

        /*
          $usuarios_pagamento_em_carteira = array("GLAUCIA@E-PREPAG.COM.BR", "ODECIO@GREGIO.COM.BR", "WAGNER@E-PREPAG.COM.BR", "JOAO.TREVISAN@E-PREPAG.COM.BR", "TAMY@E-PREPAG.COM.BR");

          if(in_array(strtoupper($this->getEmail()), $usuarios_pagamento_em_carteira)) {
          return true;
          }
          return false;
         */
    }


    function b_isCampeonato() {
        // acabou em 2011-08-02
        return false;

//		return true;
        /*
          $usuarios_campeonatos = array("WAGNER@E-PREPAG.COM.BR", "GLAUCIA@E-PREPAG.COM.BR", "ODECIO@GREGIO.COM.BR");

          if(in_array(strtoupper($this->getEmail()), $usuarios_campeonatos)) {
          return true;
          }
          return false;
         */
    }

    // Aceitaram paraticipar do campeonato
    function b_IsGamer_Competicao_Aceito() {
        if (strtoupper($this->getCompet_aceito_regulamento()) == "S") {
            return true;
        }
        return false;
    }

    function b_IsGamer_Competicao_Com_LH() {
        if ($this->getCompet_lh_ug_id() > 0) {
            return true;
        }
        return false;
    }


    function b_IsGamer_Competicao_Pago($ogpm_id, &$vg_id) {
        $prod_id = $GLOBALS['CAMPEONATO_PROD_ID'];
        $pagtos_valor = 0;

        $pagtos_n = get_Campeonato_Pagto_Completo($this->ug_id, $prod_id, $pagtos_valor);

//gravaLog_Temporario("SQL em b_IsGamer_Competicao_Pago($ogpm_id, &$vg_id)".$sql);

        return ($pagtos_n > 0);
    }

    function atualizarCompet_participa($ug_id, $tf_ug_id, $ug_compet_jogo, $ug_compet_aceito_regulamento) {
        $sql = "update usuarios_games set ";
        $sql .= " ug_compet_lh_ug_id = " . $tf_ug_id . ", ";
        $sql .= " ug_compet_jogo = " . $ug_compet_jogo . ", ";
        $sql .= " ug_compet_aceito_regulamento = '" . $ug_compet_aceito_regulamento . "', ";
        $sql .= " ug_compet_aceito_data_aceito = CURRENT_TIMESTAMP ";
        $sql .= " where ug_id = " . $ug_id . "";
//echo $sql."<br>";
//die("");
        $ret = SQLexecuteQuery($sql);

        if ($ret) {
            return true;
        }
        return false;
    }

    function atualizarCompet_LH($ug_id, $tf_ug_id_lh) {
        $sql = "update usuarios_games set ";
        $sql .= " ug_compet_lh_ug_id = " . $tf_ug_id_lh . " ";
        $sql .= " where ug_id = " . $ug_id . "";
//echo $sql."<br>";
//die("");
        $ret = SQLexecuteQuery($sql);

        if ($ret) {
            return true;
        }
        return false;
    }

    function b_IsLogin_pagamento_bancodobrasil() {
        /*
          $usuarios_liberados_bancodobrasil = array("WAGNER@E-PREPAG.COM.BR", "GLAUCIA@E-PREPAG.COM.BR", "ODECIO@GREGIO.COM.BR");

          if(in_array(strtoupper($this->getEmail()), $usuarios_liberados_bancodobrasil)) {
          return true;
          }
          return false;
         */
        // Libera para todos os usuários
        return true;

    }

    function b_IsLogin_pagamento_bancoitau() {
        /*
          $usuarios_liberados_bancoitau = array("WAGNER@E-PREPAG.COM.BR", "GLAUCIA@E-PREPAG.COM.BR", "ODECIO@GREGIO.COM.BR");

          if(in_array(strtoupper($this->getEmail()), $usuarios_liberados_bancoitau)) {
          return true;
          }
          return false;
         */
        // Libera para todos os usuários
        return true;

    }

    function b_IsLogin_pagamento_bancoepp() {
        // Libera para todos os usuários
        return true;

    }

    function b_IsLogin_pagamento_usa_produto_treinamento() {

        $bret = ($this->b_IsLogin_pagamento_paypal() || $this->b_IsLogin_pagamento_hipay() || $this->b_IsLogin_pagamento_pin_eprepag());
        return $bret;

    }


    function b_IsLogin_pagamento_paypal() {

        $usuarios_liberados_paypal = array("WAGNER@E-PREPAG.COM.BR", "GLAUCIA@E-PREPAG.COM.BR", "ODECIO@GREGIO.COM.BR");

        if (in_array(strtoupper($this->getEmail()), $usuarios_liberados_paypal)) {
            return true;
        }
        return false;

        // Libera para todos os usuários
//		return true;

    }

    function b_IsLogin_pagamento_hipay() {

        $usuarios_liberados_hipay = array("WAGNER@E-PREPAG.COM.BR", "GLAUCIA@E-PREPAG.COM.BR", "ODECIO@GREGIO.COM.BR");

        if (in_array(strtoupper($this->getEmail()), $usuarios_liberados_hipay)) {
            return true;
        }
        return false;

        // Libera para todos os usuários
//		return true;

    }

    function b_Is_Boleto_Itau() {
        /*
          // Boleto Itau - Liberados todos em 2010-10-15
          $usuarios_usa_boleto_itau = array("WAGNER@E-PREPAG.COM.BR", "GLAUCIA-E-PREPAG@HOTMAIL.COM");

          if(in_array(strtoupper($this->getEmail()), $usuarios_usa_boleto_itau)) {
          return true;
          }
          return false;
         */
        // Libera para todos os usuários
        return true;

    }

    function b_Is_Boleto_Banespa() {
        // Boleto Santander
        /*
          $usuarios_usa_boleto_itau = array("GLAUCIA-E-PREPAG@HOTMAIL.COM");
          if(in_array(strtoupper($this->getEmail()), $usuarios_usa_boleto_itau)) {
          return true;
          }  //Abaixo teste que libera para todos menos o Drupal(exceto Boleto Express do Drupal)
          elseif($GLOBALS['_SERVER']['PHP_SELF']=="/prepag2/commerce/finaliza_vendaGamerDeposito.php"
          || $GLOBALS['_SERVER']['PHP_SELF']=="/prepag2/commerce/finaliza_venda.php"
          || $GLOBALS['_SERVER']['PHP_SELF']=="/prepag2/commerce/finaliza_venda_int.php"
          || $GLOBALS['_SERVER']['PHP_SELF']=="/prepag2/commerce/conta/pagto_compr_boleto.php"){
          return true;
          }
          else return false;
         */
        // Libera para todos os usuários
        return false;

    }

    function b_Is_Boleto_Bradesco() {
        /*
        // Boleto Bradesco - Liberados todos em 2016-07-07
        $usuarios_usa_boleto_itau = array("WAGNER.PLAYER@GMAIL.COM", "GLAUCIA-E-PREPAG@HOTMAIL.COM", "TAMY@E-PREPAG.COM");

        if(in_array(strtoupper($this->getEmail()), $usuarios_usa_boleto_itau)) {
                return true;
        }
        return false;
        */
        // Libera para todos os usuários
        return true;
    } //end function b_Is_Boleto_Bradesco

    function b_Is_Campeonato_Teste() {
        /*
          // Campeonato_Teste
          $usuarios_Campeonato_Teste = array("WAGNER@E-PREPAG.COM.BR", "GLAUCIA-E-PREPAG@HOTMAIL.COM", "ODECIO@GREGIO.COM.BR", "JPTREVISAN@GMAIL.COM", "JOAO123@MEUDOMINIO.COM");

          if(in_array(strtoupper($this->getEmail()), $usuarios_Campeonato_Teste)) {
          return true;
          }
          return false;
         */
        return true;
    }

    function b_IsLogin_reinaldopshotmail() {

        if (strtoupper($this->getEmail()) == "WAGNER@E-PREPAG.COM.BR") {
            return true;
        }
        return false;

    }

    function b_IsLogin_reinaldopsyahoo() {

        if (strtoupper($this->getEmail()) == "WAGNER@E-PREPAG.COM.BR") {
            return true;
        }
        return false;

    }

    function b_IsLogin_pagamento_pin_eprepag() {
        $usuarios_liberados_pin_eprepag = array("GLAUCIA@E-PREPAG.COM.BR", "ODECIO@GREGIO.COM.BR", "WAGNER@E-PREPAG.COM.BR");

        if (in_array(strtoupper($this->getEmail()), $usuarios_liberados_pin_eprepag)) {
            return true;
        }
        return false;
    }
	
function b_IsLogin_pagamento_pin_Personalizado() {
	
	return true;
	
}

    // Para bloquear o uso de PINs EPP Cash por usuários (usar jnto com b_pin_forma_pagamento())
    function b_IsLogin_pagamento_pin_EPP_Cash() {
        //return false; 
        return true;

        /*
          Liberado 2012-01-09
          $usuarios_liberados_pin_eprepag = array("GLAUCIA@E-PREPAG.COM.BR", "ODECIO@GREGIO.COM.BR","WAGNER@E-PREPAG.COM.BR", "JOAO.TREVISAN@E-PREPAG.COM.BR", "TAMY@E-PREPAG.COM.BR");

          if(in_array(strtoupper($this->getEmail()), $usuarios_liberados_pin_eprepag)) {
          return true;
          }
          return false;
         */
    }

    function b_IsLogin_pagamento_Cielo(&$lista_usuarios = null) {
        return $this->getUseCielo();
        /*
          $usuarios_liberados_cielo = array("WAGNER@E-PREPAG.COM.BR", "JOAO.TREVISAN@E-PREPAG.COM.BR", "GLAUCIA@E-PREPAG.COM.BR", "ODECIO@GREGIO.COM.BR");
          $lista_usuarios = $usuarios_liberados_cielo;

          if((in_array(strtoupper($this->getEmail()), $usuarios_liberados_cielo))||(!empty($this->ug_use_cielo))) {
          return true;
          }
          return false;
         */
    }

    function b_IsLogin_pagamento_Cielo_Integracao(&$lista_usuarios = null) {
//		return $this->getUseCielo();

        $usuarios_liberados_cielo_integracao = array("JOAO.TREVISAN@E-PREPAG.COM.BR", "GLAUCIA@E-PREPAG.COM.BR", "ODECIO@GREGIO.COM.BR", "WAGNER@E-PREPAG.COM.BR");
        $lista_usuarios = $usuarios_liberados_cielo_integracao;

//echo strtoupper($this->getEmail());

        if ((in_array(strtoupper($this->getEmail()), $usuarios_liberados_cielo_integracao))) {
            return true;
        }
        return false;
    }

    function b_IsLogin_pagamento_Cielo_debito(&$lista_usuarios = null) {
//		return $this->getUseCielo();

        $usuarios_liberados_cielo_debito = array("JOAO.TREVISAN@E-PREPAG.COM.BR", "GLAUCIA@E-PREPAG.COM.BR", "WAGNER@E-PREPAG.COM.BR");
        $lista_usuarios = $usuarios_liberados_cielo;
        if ((in_array(strtoupper($this->getEmail()), $usuarios_liberados_cielo_debito))) {
            return true;
        }
        return false;
    }

    // Liberado 2012-11-05
    function b_IsLogin_pagamento_Elex_nova_pagina(&$lista_usuarios = null) {
        return true;

//		return $this->getUseCielo();
        /*
          $usuarios_liberados_cielo_debito = array("JOAO.TREVISAN@E-PREPAG.COM.BR", "GLAUCIA@E-PREPAG.COM.BR", "WAGNER@E-PREPAG.COM.BR");
          $lista_usuarios = $usuarios_liberados_cielo;

          if((in_array(strtoupper($this->getEmail()), $usuarios_liberados_cielo_debito))) {
          return true;
          }
          return false;
         */
    }

    function atualiza_ultimo_acesso($semail0) {

//		$semail = ($semail0)?$semail0:($this->getEmail());
        $semail = $semail0;
        //Atualiza ultimo acesso
        //------------------------------------------------------------------
        if ($semail) {
            //SQL
            $sql = "update usuarios_games set ";
            $sql .= " ug_data_ultimo_acesso = CURRENT_TIMESTAMP, ";
            $sql .= " ug_qtde_acessos = ug_qtde_acessos + 1 ";
            $sql .= " where ug_email = " . SQLaddFields($semail, "s");
//grava_log_integracao("Integração Debug 5: ".date("Y-m-d H:i:s")."\n  $sql \n");
            $rs = SQLexecuteQuery($sql);
        }
    }

    function b_IsLogin_extrato_UG($op = null, &$aret = null) {
//		$usuarios_liberados_extrato_UG = array("GLAUCIA@E-PREPAG.COM.BR", "ODECIO@GREGIO.COM.BR", "WAGNER@E-PREPAG.COM.BR", "IGORTACE@GMAIL.COM", "TAMY@E-PREPAG.COM.BR", "JOAO.TREVISAN@E-PREPAG.COM.BR");

        $usuarios_liberados_extrato_id = array(16, 9943, 97619, 8972, 46198, 9093, 2745, 53916, 9016, 237209, 54276);

        if ($op == 1) {
            $aret = $usuarios_liberados_extrato_id;
        }

        if (in_array(strtoupper($this->getId()), $usuarios_liberados_extrato_id)) {
            return true;
        }
        return false;
    }

    function b_IsLogin_valorPINEPPCash() {

        //return false;		/// <<<<<<<<<<

        $usuarios_liberados_valorPINEPPCash = array("GLAUCIA@E-PREPAG.COM.BR", "WAGNER@E-PREPAG.COM.BR", "JOAO.TREVISAN@E-PREPAG.COM.BR");
        if (in_array(strtoupper($this->getEmail()), $usuarios_liberados_valorPINEPPCash)) {
            return true;
        }
        return false;
    }

    function b_extrato_UG($bloqueio = true) {
        if ($bloqueio) {
            return true;
        }
        return false;
    }

    function b_listaJogos_Alawar() {
        $usuarios_liberados_alawar = array("GLAUCIA@E-PREPAG.COM.BR", "ODECIO@GREGIO.COM.BR", "WAGNER@E-PREPAG.COM.BR", "FABIOSS@E-PREPAG.COM.BR");
        if (in_array(strtoupper($this->getEmail()), $usuarios_liberados_alawar)) {
            return true;
        }
        return false;
    }

    function b_lista_ofertas() {
        $usuarios_liberados_ofertas = array("GLAUCIA@E-PREPAG.COM.BR", "ODECIO@GREGIO.COM.BR", "WAGNER@E-PREPAG.COM.BR",  "JOAO.TREVISAN@E-PREPAG.COM.BR");
        if (in_array(strtoupper($this->getEmail()), $usuarios_liberados_ofertas)) {
            return true;
        }
        return false;
    }

    function atualizar_sem_validar($objGamesUsuario, $editaSemLogin = false) {


        //$ret = UsuarioGames::validarCampos($objGamesUsuario, false);

        if ($ret == "") {
            if (UsuarioGames::existeEmail($objGamesUsuario->getEmail(), $objGamesUsuario->getId())) {
                $ret = "Email já cadastrado.";
            }
        }
        
        if ($ret == "" && $editaSemLogin === false) {
            if (UsuarioGames::existeLogin($objGamesUsuario->getLogin(), $objGamesUsuario->getId())) {
                $ret = "Login já cadastrado.";
            }
        }
/*
        if ($ret == "") {
            if (UsuarioGames::existeCPF($objGamesUsuario->getCPF(), $objGamesUsuario->getId())) {
                $ret = "CPF já cadastrado.";
            }
        }

        if ($ret == "") {
            if (UsuarioGames::existeRG($objGamesUsuario->getRG(), $objGamesUsuario->getId())) {
                $ret = "RG já cadastrado.";
            }
        }
*/
        if ($ret == "") {

            //Formata
 			$dataNascimento = (!is_null($objGamesUsuario->getDataNascimento()) && $objGamesUsuario->getDataNascimento() != '') ? SQLaddFields(trim(formata_data($objGamesUsuario->getDataNascimento(), 1)), "s") : "null";
                
            //SQL
            $sql = "update usuarios_games set ";
 			if(!is_null($objGamesUsuario->getAtivo())) 			$sql .= " ug_ativo = " 			. SQLaddFields(trim($objGamesUsuario->getAtivo()), "") . ",";
 			if(!is_null($objGamesUsuario->getEmail())) 			$sql .= " ug_email = " 			. SQLaddFields(trim(strtoupper($objGamesUsuario->getEmail())), "s") . ",";
 			if(!is_null($objGamesUsuario->getNome())) 			$sql .= " ug_nome = " 			. SQLaddFields(trim(strtoupper($objGamesUsuario->getNome())), "s") . ",";
            if(!is_null($objGamesUsuario->getLogin())) 			$sql .= " ug_login = " 			. SQLaddFields(trim(strtoupper($objGamesUsuario->getLogin())), "s") . ",";
 			if(!is_null($objGamesUsuario->getCPF())) 			$sql .= " ug_cpf = " 			. SQLaddFields(trim($objGamesUsuario->getCPF()), "s") . ",";
 			if(!is_null($objGamesUsuario->getRG())) 			$sql .= " ug_rg = " 			. SQLaddFields(trim($objGamesUsuario->getRG()), "s") . ",";
 			$sql .= " ug_data_nascimento = ". trim($dataNascimento).",";
 			if(!is_null($objGamesUsuario->getSexo())) 			$sql .= " ug_sexo = " 			. SQLaddFields(trim(strtoupper($objGamesUsuario->getSexo())), "s") . ",";
 			if(!is_null($objGamesUsuario->getTipoEnd())) 		$sql .= " ug_tipo_end = ". SQLaddFields(trim(strtoupper($objGamesUsuario->getTipoEnd())), "s") . ",";
 			if(!is_null($objGamesUsuario->getEndereco())) 		$sql .= " ug_endereco = " 		. SQLaddFields(trim(strtoupper($objGamesUsuario->getEndereco())), "s") . ",";
 			if(!is_null($objGamesUsuario->getNumero())) 		$sql .= " ug_numero = " 		. SQLaddFields(trim(strtoupper($objGamesUsuario->getNumero())), "s") . ",";
 			if(!is_null($objGamesUsuario->getComplemento())) 	$sql .= " ug_complemento = " 	. SQLaddFields(trim(strtoupper($objGamesUsuario->getComplemento())), "s") . ",";
 			if(!is_null($objGamesUsuario->getBairro())) 		$sql .= " ug_bairro = " 		. SQLaddFields(trim(strtoupper($objGamesUsuario->getBairro())), "s") . ",";
 			if(!is_null($objGamesUsuario->getCidade())) 		$sql .= " ug_cidade = " 		. SQLaddFields(trim(strtoupper($objGamesUsuario->getCidade())), "s") . ",";
 			if(!is_null($objGamesUsuario->getEstado())) 		$sql .= " ug_estado = " 		. SQLaddFields(trim(strtoupper($objGamesUsuario->getEstado())), "s") . ",";
 			if(!is_null($objGamesUsuario->getCEP())) 			$sql .= " ug_cep = " 			. SQLaddFields(trim($objGamesUsuario->getCEP()), "s") . ",";
 			if(!is_null($objGamesUsuario->getTelDDI())) 		$sql .= " ug_tel_ddi = " 		. SQLaddFields(trim($objGamesUsuario->getTelDDI()), "s") . ",";
 			if(!is_null($objGamesUsuario->getTelDDD())) 		$sql .= " ug_tel_ddd = " 		. SQLaddFields(trim($objGamesUsuario->getTelDDD()), "s") . ",";
 			if(!is_null($objGamesUsuario->getTel())) 			$sql .= " ug_tel = " 			. SQLaddFields(trim($objGamesUsuario->getTel()), "s") . ",";
 			if(!is_null($objGamesUsuario->getCelDDI())) 		$sql .= " ug_cel_ddi = " 		. SQLaddFields(trim($objGamesUsuario->getCelDDI()), "s") . ",";
 			if(!is_null($objGamesUsuario->getCelDDD())) 		$sql .= " ug_cel_ddd = " 		. SQLaddFields(trim($objGamesUsuario->getCelDDD()), "s") . ",";
 			if(!is_null($objGamesUsuario->getCelDDD())) 		$sql .= " ug_cel = " 			. SQLaddFields(trim($objGamesUsuario->getCel()), "s") . " ,";
			if(!is_null($objGamesUsuario->getHabboId())) 		$sql .= " ug_habbo_id = "		. SQLaddFields(trim($objGamesUsuario->getHabboId()), "s") . ",";
                        if (!is_null($objGamesUsuario->getNewsLetter())) {
                            $sql .= " ug_news = " . SQLaddFields(trim($objGamesUsuario->getNewsLetter()), "s") . ",";
                        }
			if(!is_null($objGamesUsuario->getCompet_lh_ug_id()))            $sql .= " ug_compet_lh_ug_id = "            . SQLaddFields(trim($objGamesUsuario->getCompet_lh_ug_id()), "") . ",";
			if(!is_null($objGamesUsuario->getCompet_jogo())) 		$sql .= " ug_compet_jogo = "                . SQLaddFields(trim($objGamesUsuario->getCompet_jogo()), "") . ",";
			if(!is_null($objGamesUsuario->getCompet_aceito_regulamento())) 	$sql .= " ug_compet_aceito_regulamento = "  . SQLaddFields(trim($objGamesUsuario->getCompet_aceito_regulamento()), "s") . ",";
			if(!is_null($objGamesUsuario->getCompet_aceito_data_aceito())) 	$sql .= " ug_compet_aceito_data_aceito = "  . SQLaddFields(trim($objGamesUsuario->getCompet_aceito_data_aceito()), "s") . ",";
			if(!is_null($objGamesUsuario->getUseCielo())) 			$sql .= " ug_use_cielo = "                  . SQLaddFields(trim($objGamesUsuario->getUseCielo()), "") . ",";
 			if(!is_null($objGamesUsuario->getSaldoFidelizacao())) 		$sql .= " ug_saldo_fidelizacao = "          . SQLaddFields(trim($objGamesUsuario->getSaldoFidelizacao()), "") . ",";
 			if(!is_null($objGamesUsuario->getCategoriaFidelizacao())) 	$sql .= " ug_categoria_fidelizacao = "      . SQLaddFields(trim($objGamesUsuario->getCategoriaFidelizacao()), "") . ","; 			
 			if(!is_null($objGamesUsuario->getNomedaMae())) 			$sql .= " ug_nome_da_mae = "                . SQLaddFields(trim($objGamesUsuario->getNomedaMae()), "s") . ","; 			
            if(!is_null($objGamesUsuario->getNomeCPF())) 			$sql .= " ug_nome_cpf = "                . SQLaddFields(trim($objGamesUsuario->getNomeCPF()), "s") . ","; 		        
 			if(!is_null($objGamesUsuario->getOBS())) {
                            if(trim($objGamesUsuario->getOBS()) != "") {  
                                $sql_insert_obs = "INSERT INTO usuarios_games_obs VALUES (".$objGamesUsuario->getId().",". SQLaddFields(trim($objGamesUsuario->getOBS()), "s") . ",'".$GLOBALS['_SESSION']['userlogin_bko']."');";
                                //echo $sql_insert_obs;
                                $ret_insert_obs = SQLexecuteQuery($sql_insert_obs);
                                if(!$ret_insert_obs) echo "Erro ao atualizar Observação do Usuário.".PHP_EOL;
                            }//end if(trim($objGamesUsuario->getOBS()) != "")
                        } //end if(!is_null($objGamesUsuario->getOBS())) 
                        
            
			if(substr($sql, -1) == ",") $sql = substr($sql, 0, strlen($sql) - 1);
            $sql .= " where ug_id = " . SQLaddFields($objGamesUsuario->getId(), "");
            $ret = SQLexecuteQuery($sql);
			if(!$ret) $ret = "Erro ao atualizar usuário.\n";
            else {
                $ret = "";
            }

        }

        return $ret;
    }
    
    public function inserirMelhorado(){
        $erro = array();

        if (UsuarioGames::existeEmail($this->getEmail(), null)) {
            $erro[] = "<p>E-mail '".$this->getEmail(). "' já cadastrado.</p>";
        }

        if (UsuarioGames::existeLogin($this->getLogin())){
            $erro[] = "<p>Login '".$this->getLogin()."' já cadastrado.</p>";
        }
		
		if (UsuarioGames::existeCPFCadastro($this->getCPF())){
            $erro[] = "<p>O CPF '".$this->getCPF()."' já cadastrado.</p>";
        }
		
		

        if(empty($erro)){
            try {
                //Formata
                $objEncryption = new Encryption();
                $senha = $objEncryption->encrypt(trim($this->getSenha()));

                //Inicializando conexao PDO
                $con = ConnectionPDO::getConnection();
                $pdo = $con->getLink();
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $dataNascimento = (!is_null($this->getDataNascimento())) ? formata_data($this->getDataNascimento(), 1) : null;
                //Array para a Query de Insert
                $tmpArray = array(
                                    ':ug_login'                 => (string) trim(strtoupper($this->getLogin())),
                                    ':ug_senha'                 => (string) $senha,
                                    ':ug_ativo'                 => 1,//trim($this->getAtivo()),
                                    ':ug_data_inclusao'         => date("Y-m-d H:i:s"),
                                    ':ug_data_ultimo_acesso'    => date("Y-m-d H:i:s"),
                                    ':ug_qtde_acessos'          => 1,
                                    ':ug_email'                 => (string) trim(strtoupper($this->getEmail())),
                                    ':ug_endereco'              => (string) trim($this->getEndereco()),
                                    ':ug_tipo_end'              => (string) trim($this->getTipoEnd()),
                                    ':ug_numero'                => (string) trim(strtoupper($this->getNumero())),
                                    ':ug_complemento'           => (string) trim(strtoupper($this->getComplemento())),
                                    ':ug_bairro'                => (string) trim($this->getBairro()),
                                    ':ug_cidade'                => (string) trim($this->getCidade()),
                                    ':ug_estado'                => (string) trim(strtoupper($this->getEstado())),
                                    ':ug_cep'                   => (string) trim($this->getCEP()),
                                    ':ug_nome'                  => (string) trim(strtoupper($this->getNome())),
									':ug_nome_cpf'              => (string) trim(strtoupper($this->getNomeCPF())),
									':ug_data_cpf_informado'    => date("Y-m-d H:i:s").".8418-03",
                                    ':ug_cpf'                   => (string) trim($this->getCPF()),
                                    ':ug_rg'                    => (string) trim($this->getRG()),
                                    ':ug_data_nascimento'       => $dataNascimento,
                                    ':ug_sexo'                  => (string) trim(strtoupper($this->getSexo())),
                                    ':ug_tel_ddi'               => (string) trim(strtoupper($this->getTelDDI())),
                                    ':ug_tel_ddd'               => (string) trim(strtoupper($this->getTelDDD())),
                                    ':ug_tel'                   => (string) trim(strtoupper($this->getTel())),
                                    ':ug_cel_ddi'               => (string) trim(strtoupper($this->getCelDDI())),
                                    ':ug_cel_ddd'               => (string) trim(strtoupper($this->getCelDDD())),
                                    ':ug_cel'                   => (string) trim(strtoupper($this->getCel())),
                                    ':ug_news'                  => (string) trim(strtoupper($this->getNewsLetter()))
                                  );

                $sql = "insert into usuarios_games";

                foreach($tmpArray as $ind => $val){
                    if(isset($columns)){
                        $columns .= ", ".str_replace(":","",$ind);
                        $values  .= ", ".$ind;
                    }else{
                        $columns    = str_replace(":","",$ind);
                        $values     = $ind;
                    }

                } //end foreach

                $sql .= "(".$columns.") values (".$values.");";


                //Tentando executar a Query de Insert
                $rs = $pdo->prepare($sql);

                if($rs->execute($tmpArray)){
                    $sql = "select ug_id from usuarios_games where ug_email = '".strtoupper($this->getEmail())."'";
                    $getid = $pdo->prepare($sql);

                    if($getid->execute()){
                        $res = $getid->fetch(PDO::FETCH_ASSOC);
                        //Log na base
                        usuarios_games_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['CRIACAO_DO_CADASTRO'], $res['ug_id'], null);
                        $envioEmail = new EnvioEmailAutomatico(TIPO_USUARIO_GAMER,'CadastroGamer');
                        $envioEmail->setUgID($res['ug_id']);
                        $envioEmail->MontaEmailEspecifico();

                        $this->adicionarLoginSession_ByID($res['ug_id']);
                        return true;

                    }else{
                        $erro[] = "ERRO 215533. Tivemos um problema. Por favor, entre em contato com nosso suporte.";
                    }
                }else{
                    $erro[] = "ERRO 215534. Tivemos um problema, favor entrar em contato com nosso suporte.";
                }

            } catch(PDOException $e) {
                UsuarioGames::logEvents($e->getMessage());
                $erro[] = $e->getMessage();
            }
        }
        
        if(empty($erro)){
            $erro[] = "ERRO 215535. Tivemos um problema, favor entrar em contato com nosso suporte.";
        }
        
        return $erro;
    }
    
    function logEvents($msg) {
            global $raiz_do_projeto;
        
            $fileLog = $raiz_do_projeto."log/log_class_users_GAMERs_PDO-Errors.log";

            $log  = "=================================================================================================".PHP_EOL;
            $log .= "DATA -> ".date("d/m/Y - H:i:s")."".PHP_EOL;
            $log .= "---------------------------------".PHP_EOL;
            $log .= htmlspecialchars_decode($msg);			

            $fp = fopen($fileLog, 'a+');
            fwrite($fp, $log);
            fclose($fp);		
    }//end function logEvents
    
    public function atualizarMelhorado($editaSemLogin = false) {
        
        $ret = UsuarioGames::validarCampos($this, false);

        if ($ret == "") {
            if (UsuarioGames::existeEmail($this->getEmail(), $this->getId())) {
                $ret = "E-mail já cadastrado.";
            }
        }
        
        if ($ret == "" && $editaSemLogin === false) {
            if (UsuarioGames::existeLogin($this->getLogin(), $this->getId())) {
                $ret = "Login já cadastrado.";
            }
        }
        
        if($ret == ""){
            
            try{
                //Inicializando conexao PDO
                $con = ConnectionPDO::getConnection();
                $pdo = $con->getLink();
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                //Formata
                if(!is_null($this->getLogin()))
                    $fields[":ug_login"] = (string) strtoupper($this->getLogin());
                                
                if(!is_null($this->getAtivo()))
                    $fields[":ug_ativo"] = (int) $this->getAtivo();
                
                if(!is_null($this->getDataNascimento())){
                    
                    $dataNascimento = formata_data($this->getDataNascimento(), 1);
                    
                    if (verifica_data($this->getDataNascimento()) == 1)
                        $fields[":ug_data_nascimento"] = (string) trim($dataNascimento) ;
                }

                if(!is_null($this->getEmail()))
                    $fields[":ug_email"] = (string) trim(strtoupper($this->getEmail())) ;

                if(!is_null($this->getNome()))
                    $fields[":ug_nome"] = (string) trim($this->getNome());
                
                if(!is_null($this->getNomeCPF())){
                    $fields[":ug_nome_cpf"] = (string) trim($this->getNomeCPF());
                    $fields[":ug_data_cpf_informado"] = "NOW()";
                }
                if(!is_null($this->getCPF()))
                    $fields[":ug_cpf"] = (string) trim($this->getCPF()) ;

                if(!is_null($this->getSexo()))
                    $fields[":ug_sexo"] = (string) trim(strtoupper($this->getSexo())) ;

                if(!is_null($this->getEndereco()))
                    $fields[":ug_endereco"] = (string) trim(strtoupper($this->getEndereco()));

                if(!is_null($this->getNumero()))
                    $fields[":ug_numero"] = (string) trim(strtoupper($this->getNumero()));

                if($this->getComplemento())
                    $fields[":ug_complemento"] = (string) trim(strtoupper($this->getComplemento()));

                if(!is_null($this->getBairro()))
                    $fields[":ug_bairro"] = (string) trim(strtoupper($this->getBairro()));

                if(!is_null($this->getCidade()))
                    $fields[":ug_cidade"] = (string) trim(strtoupper($this->getCidade()));

                if(!is_null($this->getEstado()))
                    $fields[":ug_estado"] = (string) trim(strtoupper($this->getEstado()));

                if(!is_null($this->getCEP()))
                    $fields[":ug_cep"] = (string) trim($this->getCEP());

                if(!is_null($this->getCelDDI()))
                    $fields[":ug_cel_ddi"] = (string) trim($this->getCelDDI());

                if(!is_null($this->getCelDDD()))
                    $fields[":ug_cel_ddd"] = (string) trim($this->getCelDDD());

                if(!is_null($this->getCel()))
                    $fields[":ug_cel"] = (string) trim($this->getCel());

                if (!is_null($this->getNewsLetter())) 
                    $fields[":ug_news"] = (string) trim($this->getNewsLetter());

                if(!empty($fields)){
                    
                    $fields[":ug_id"] = (int) $this->getId();

                    foreach($fields as $field => $value){
                        $strF[] = " ".str_replace(":","",$field)." = ".$field;
                    }
                    
                    //SQL
                    $sql = "update 
                                usuarios_games set 
                                ".implode(",",$strF)."
                            where 
                                ug_id = :ug_id";

                    //Tentando executar a Query de Insert
                    $rs = $pdo->prepare($sql);

                    if($rs->execute($fields)){
                        //Log na base
                        usuarios_games_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['ALTERACAO_DO_CADASTRO'], null, null);

                            /* ---Wagner */
                        $objEnvioEmailAutomatico = new EnvioEmailAutomatico(TIPO_USUARIO_GAMER, 'AlteracaoCadastro');
                        $objEnvioEmailAutomatico->setUgID($this->getId());
                        $objEnvioEmailAutomatico->MontaEmailEspecifico();
                        $this->adicionarLoginSession_ByID($this->getId());
                        return true;

                    }else{
                        throw new PDOException("ERRO 215535. Tivemos um problema, favor entrar em contato com nosso suporte.");
                    }
                }else{
                    $erro[] = "Não há campos a serem atualizados.";
                }

            } catch(PDOException $e) {
                $erro[] = "ERRO 2155356. Tivemos um problema, favor se o erro persistir, entrar em contato com nosso suporte. Obrigado!";
            }
        }else{
            $erro[] = $ret;
        }
        
        UsuarioGames::logEvents("ERRO 2155356 ".implode(" / ",$erro));
        
        return $erro;

    }
    
    public function alteraDadoAcesso($campo, $valor, $senhaAtual) {

        $ret = false;
        
        //Inicializando conexao PDO
        $con = ConnectionPDO::getConnection();
        $pdo = $con->getLink();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        //Autentica usuario
        //------------------------------------------------------------------
        $objEncryption = new Encryption();
        $senhaAtual = $objEncryption->encrypt(trim($senhaAtual));
        $login = strtoupper(trim($login));

        //SQL
        $sql = "select 
                    count(*) as qtde 
                from 
                    usuarios_games 
                where 
                    ug_email = '".$this->getEmail()."' and ug_senha = '$senhaAtual'";
        
        if($campo == "ug_email"){
            $valor = strtoupper($valor);
            if($this->existeEmail($valor, $this->getId())){
                $ret = utf8_encode("E-mail já cadastrado em nossa base de dados.");
            }
        }else if($campo == "ug_login"){
            $valor = strtoupper($valor);
            if($this->existeLogin($valor, $this->getId())){
                $ret = utf8_encode("Login já cadastrado em nossa base de dados. Insira um novo Login.");
            }
        }else if($campo == "ug_senha"){
            //SQL
            $sqlValida = "select 
                        count(*) as qtde 
                    from 
                        usuarios_games 
                    where 
                        ug_email = '".$this->getEmail()."' and ug_senha = '".$objEncryption->encrypt(trim($valor))."'";
            
            //Tentando executar a Query de Insert
            $rs = $pdo->prepare($sqlValida);

            if($rs->execute()){

                if($rs->fetchColumn() > 0) {
                    $ret =utf8_encode( "A nova senha é identica a senha atual.");
                }
            }
        }

        if(!$ret){
            try{
            
                //Tentando executar a Query de Insert
                $rs = $pdo->prepare($sql);

                if($rs->execute()){

                    if($rs->fetchColumn() > 0) {

                        $sql = "update usuarios_games set $campo = :valor where ug_email = :ug_email and ug_senha = :senhaAtual";

                        if($campo == "ug_senha"){
                            $fields[':valor'] = $objEncryption->encrypt(trim($valor));
                        }else{
                            $fields[':valor'] = $valor;    
                        }

                        $fields[':ug_email'] = $this->getEmail();
                        $fields[':senhaAtual'] = (string) $senhaAtual;

                        //Tentando executar a Query de Insert
                        $rs = $pdo->prepare($sql);

                        if($rs->execute($fields)){
                            //Log na base
                            usuarios_games_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['TROCA_DE_SENHA'], null, null);

                            if($campo == "ug_email"){
                                $this->setEmail($valor);

                            }else if($campo == "ug_senha"){
                                $this->setSenha($senhaAtual);

                            }else if($campo == 'ug_login'){
                                $this->setLogin($valor);

                            }

                            $_SESSION['usuarioGames_ser'] = serialize($this);

                            //Envia email
                            //--------------------------------------------------------------------------------
                            if ($campo == "ug_senha") {
                                $objEnvioEmailAutomatico = new EnvioEmailAutomatico(TIPO_USUARIO_GAMER, 'AlteracaoSenha');
                                
                            }else if($campo == "ug_email"){
                                $objEnvioEmailAutomatico = new EnvioEmailAutomatico(TIPO_USUARIO_GAMER, 'AlteracaoCadastro');
                                
                            }else if($campo == "ug_login"){
                                $objEnvioEmailAutomatico = new EnvioEmailAutomatico(TIPO_USUARIO_GAMER, 'AlteracaoCadastro');
                                
                            }
                            
                            $objEnvioEmailAutomatico->setUgID($this->getId());
                            $objEnvioEmailAutomatico->MontaEmailEspecifico();

                            $ret = true;
                        }else{
                            $ret = utf8_encode("Erro desconhecido, favor entrar em contato com o nosso suporte.");
                        }

                    }else{
                        $ret = utf8_encode("A senha atual está incorreta.");
                    }
                }
            } catch(PDOException $e) {
                UsuarioGames::logEvents("ERRO 2155351 ".$e->getMessage());
                $ret = "ERRO 2155351. Tivemos um problema, favor se o erro persistir, entrar em contato com nosso suporte. Obrigado!";
            }
        }
        
        
        
        return $ret;
    }
	
	function verifica_situacao_cpf($cpf) {
	
	    $con = ConnectionPDO::getConnection();
        $pdo = $con->getLink();
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$retorno = ["black" => false, "white"=> false];
	   
		$tt = $pdo->prepare("select * from cpf_black_list where cpf =".$cpf.";");
		$tt->execute();
		$dados = $tt->fetch();
			
		if($dados != false && count($dados) > 0){
		  $retorno["black"] = true;
		}
		
		$rs = $pdo->prepare("select * from cpf_white_list where cpf =".$cpf.";");
		$rs->execute();
		$dados = $rs->fetch();
	
	    if($dados != false && count($dados) > 0){
		  $retorno["white"] = true;
		} 
	
      return $retorno;  
    }
}

// end of class


// retorna true -> Usa Itaú
// retorna false -> Usa Bradesco
function b_Is_Boleto_Express_Itau($email, $opr_codigo) {

    // Libera Itaú para todos os Express Money em 2011-11-11
    return false;
    /*
      // Se não for Habbo -> usa boleto Itau
      if($opr_codigo!=16) {
      return true;
      }
      return false;
     */
    /*
      // Boleto Express Money Itau - Testes em 2010-11-29
      $usuarios_usa_boleto_express_itau = array("GLAUCIA-E-PREPAG@HOTMAIL.COM");

      // Se não for da lista -> usa boleto Itau
      // Os da lista usam Bradesco
      if(!in_array(strtoupper($email), $usuarios_usa_boleto_express_itau)) {
      return true;
      }
      return false;
     */
    /*
      // Boleto Express Money Itau - Testes em 2011-10-31
      $usuarios_usa_boleto_express_itau = array("GLAUCIA-E-PREPAG@HOTMAIL.COM");

      // Se for da lista -> usa boleto Itau
      // O resto continua usando Bradesco
      if(in_array(strtoupper($email), $usuarios_usa_boleto_express_itau)) {
      return true;
      }
      return false;
     */
    // Libera para todos os usuários, again, em 2011-06-27
    //	return false;

}

// Função que Habilita/Desabilita Boleto SANTANDER
function b_Is_Boleto_Express_Santander() {

    // Libera Santander para todos os Express Money 
    return false;

}

// Função que Habilita/Desabilita Boleto Bradesco
function b_Is_Boleto_Express_Bradesco() {

    // Libera Bradesco para todos os Express Money 
    return true;

}

function b_IsLogin_boleto_novo_prazo_vencimento($email) {

    $b_IsLogin_boleto_novo_prazo_vencimento = array("WAGNER@E-PREPAG.COM.BR", "GLAUCIA@E-PREPAG.COM.BR", "ODECIO@GREGIO.COM.BR");
    if (in_array(strtoupper($email), $b_IsLogin_boleto_novo_prazo_vencimento)) {
        return true;
    }
    return false;
}


function get_random_password($length) {
//		srand(date("s")); 
//		$possible_characters = "abcdefghijklmnopqrstuvwxyz1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ"; 
    $possible_characters = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $string = "";
    while (strlen($string) < $length) {
        $string .= substr($possible_characters, rand() % (strlen($possible_characters)), 1);
    }
    return($string);
}

// Para bloquear o uso de PINs EPP Cash global - usar junto com UsuarioGames::b_IsLogin_pagamento_pin_EPP_Cash()
function b_pin_forma_pagamento($bloqueio = true) {

    /*
      // Para desabilitar a opção de pagamento EPP cash -> libera o retorno false
      return false;
     */

    /*
      // Para liberar a opção de pagamento EPP Cash apenas para estes usuários => libera este bloco
      $usuarioGames = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']);
      if(is_object($usuarioGames)) {
      if($usuarioGames->b_IsLogin_Wagner() || $usuarioGames->b_IsLogin_Reinaldo()) {
      return true;
      } else {
      return false;
      }
      }
     */
    if ($bloqueio) {
        return true;
    }
    return false;
}

function b_cielo_forma_pagamento($bloqueio = true) {
    if ($bloqueio) {
        return true;
    }
    return false;
}
function suspendeContaUsuario($usuario_id) {
    $msgAcao = "";
    if ($usuario_id) {
        $cad_usuarioGames = new UsuarioGames($usuario_id);
        $cad_usuarioGames->setAtivo('2');
        $msgAcao = UsuarioGames::atualizar($cad_usuarioGames);
        echo $msgAcao;
        if (empty($msgAcao)) {
            $sql = "INSERT INTO usuarios_games_cancelado (ug_id,ugc_data_cancelamento) VALUES (" . $usuario_id . ",NOW())";
            //echo $sql."<br>";
            $rs = SQLexecuteQuery($sql);
            if (!$rs) {
                return false;
			}
			else {
                unset($GLOBALS['_SESSION']['usuarioGames_ser']);
                return true;
            }
        }
    }
    return false;
}

function get_lista_usuarios_VIP() {
    //
    $aret = array();
    $class = new UsuarioGames();
    $usuarioGames = $class->getUsuarioGamesById(9093);
    $bret = $usuarioGames->b_IsLogin_pagamento_vip(1, $aret);
    return $aret;
}

?>
