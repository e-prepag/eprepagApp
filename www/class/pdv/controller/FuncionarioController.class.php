<?php
/**
 * Classe para as regras de negocio das vendas
 *
 * @author Diego
 * @email diego.gomes@e-prepag.com.br
 * @date 16-07-2015
 */
require_once DIR_CLASS . 'util/Util.class.php';
require_once DIR_CLASS . 'pdv/controller/HeaderController.class.php';
require_once DIR_CLASS . 'util/Login.class.php';

$pagina_titulo = "Funcionários";


class FuncionarioController extends HeaderController{
    public $raiz_do_projeto;
    public $msg;
        
    public function __construct(){
        $this->objBanner = new BannerBO;
        
        parent::__construct();
    }
         
    public function salva()
    {
                
        $cad_ug_id = $this->usuarios->getId();
        $cad_senha = $_POST['cad_senha'];
        $cad_senhaConf = $_POST['cad_senhaConf'];
        $cad_login = $_POST['cad_login'];
        $cad_nome = $_POST['cad_nome'];
        $cad_email = $_POST['cad_email'];
        $cad_ativo = 1;
        $sel_id = $_POST['sel_id'];
        $cad_tipo = $_POST['cad_tipo'];
        //Validacoes
        $msg = "";	

        //Valida Login
        if($msg == "")
        {
            $instUsuarioGamesOperador = new UsuarioGamesOperador;
            $msg = $instUsuarioGamesOperador->validarCamposLogin(
                                                                $cad_senha, $cad_senhaConf, $cad_login
                                                            );
        }

        //Valida Cadastro
        if($msg == "")
        {
            //cria objeto usuario
            $cad_usuariooperadorGames = new UsuarioGamesOperador(
                                                                    null, $cad_ug_id, $cad_login, $cad_senha, $cad_ativo, 
                                                                    null, null, 0, $cad_tipo, $cad_nome, $cad_email
                                                                );
            $instUsuarioGamesOperador = new UsuarioGamesOperador;
            $msg = $instUsuarioGamesOperador->validarCampos(
                                                        $cad_usuariooperadorGames, 1
                                                    );
        }

        //Valida Login se ja esta cadastrado
        if($msg == "")
        {
            $instUsuarioGamesOperador = new UsuarioGamesOperador;
            $ret = $instUsuarioGamesOperador->existeLogin($cad_login, null, $cad_ug_id);
            if($ret) 
                $msg = "Login já cadastrado. Escolha outro  login, por favor";
        }
        
        $clsLogin = new Login($cad_senha);
        
        if($msg == "" && $clsLogin->valida() > 0){
            $msg = "Senha não atinge os níveis de segurança desejados.";
        }

        if($msg == "")
        {
//            echo "<pre>";
//            print_r($cad_usuariooperadorGames);
//            echo "</pre>";
//            die($cad_ativo);
            $instUsuarioGamesOperador = new UsuarioGamesOperador;
            $msg = $instUsuarioGamesOperador->inserir($cad_usuariooperadorGames);
        }

        if($msg == "")
        {
            $msg = "Cadastro realizado com sucesso!";
            
            $msg = "<p class='txt-verde'>$msg</p>";
            //Log na base
            usuarios_games_operador_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['CADASTRA_OPERADOR'], $ug_id, null);
//            $strRedirect = "/prepag2/dist_commerce/conta/operador_consulta.php?msg=" . urlencode($msg);
//            redirect($strRedirect);
            $this->msg = $msg;
            return true;
        }
        else
        {
            $msg = "<p class='txt-vermelho'>$msg</p>";
            $this->msg = $msg;
            return false;
        }
    }
    
    public function alteraSenha($op){
        $operadorCadastrado = $this->usuarios->operadorCadastrado($this->usuarios->getId(), $op);
        
        if(!$operadorCadastrado){
            $this->accessDenied();
        }else{
            
            $cad_senha  = $_POST['novaSenha'];
            $cad_senhaConf = $_POST['novaSenhaConf'];
            $cad_login = $_POST['ugo_login'];
            $sel_id = $op;
            
            $instUsuarioGamesOperador = new UsuarioGamesOperador;
            $msg = $instUsuarioGamesOperador->validarCamposLogin($cad_senha, $cad_senhaConf, $cad_login);
            
            if($msg == ""){
                $clsLogin = new Login($cad_senha);
                if($clsLogin->valida() > 0){
                    $msg = "Senha não atinge os níveis de segurança desejados.";
                }
            }
            //Pre atualizacao
            if($msg == "")
            {
                $instUsuarioGamesOperador = new UsuarioGamesOperador;
                $cad_usuariooperadorGames = $instUsuarioGamesOperador->getUsuarioGamesOperadorById($sel_id);
                
                if($cad_usuariooperadorGames == null){
                    $msg = "Funcionário não encontrado.\n";
                }else{
                    $instUsuarioGamesOperador = new UsuarioGamesOperador;
                    $cad_usuariooperadorGames->setUgId($this->usuarios->getId());
                    $cad_usuariooperadorGames->setSenha($cad_senhaConf);
                    
                    $msg = $instUsuarioGamesOperador->atualizar($cad_usuariooperadorGames);
                }
            }

            if($msg == "")
            {
                $msg = "Cadastro atualizado com sucesso!";
                $msg = "<p class='txt-verde'>$msg</p>";
                $this->msg = $msg;
                return true;
            }  else 
            {
                $msg = "<p class='txt-vermelho'>$msg</p>";
                $this->msg = $msg;
                return false;
            }
        }
    }
    
    public function edita($op)
    {
        $operadorCadastrado = $this->usuarios->operadorCadastrado($this->usuarios->getId(), $op);
        
        if(!$operadorCadastrado){
            $this->accessDenied();
        }else{
        
            $btSubmit = $_POST['btSubmit'];

            $cad_senha = null;
            $cad_login = $_POST['ugo_login'];
            $cad_ug_id = $_POST['ugo_ug_id'];
            $cad_nome = $_POST['ugo_nome'];
            $cad_email = $_POST['ugo_email'];
            $cad_ativo = $_POST['ugo_ativo'];
            $sel_id = $op;
            $cad_tipo = $_POST['ugo_tipo'];

            //Validacoes
            $msg = "";	
            
            //Pre atualizacao
            if($msg == "")
            {
                $instUsuarioGamesOperador = new UsuarioGamesOperador;
                $cad_usuariooperadorGames = $instUsuarioGamesOperador->getUsuarioGamesOperadorById($sel_id);
                if($cad_usuariooperadorGames == null) $msg = "Funcionário não encontrado.\n";
            }
            //Valida Cadastro
            if($msg == "")
            {
                // $cad_usuariooperadorGames = new UsuarioGamesOperador($usuario_id);
                $cad_usuariooperadorGames->setSenha($cad_senha); //corrigindo problema de senha na hora de alterar usuário
                if(strlen($cad_nome)>0) $cad_usuariooperadorGames->setNome($cad_nome);
                if(strlen($cad_email)>0) $cad_usuariooperadorGames->setEmail($cad_email);
                if($cad_ativo==0 ||$cad_ativo==1 ) $cad_usuariooperadorGames->setAtivo($cad_ativo);
                if($cad_ug_id>0) $cad_usuariooperadorGames->setUgId($cad_ug_id);
                if($cad_login) $cad_usuariooperadorGames->setLogin($cad_login);
                if($cad_tipo==0 ||$cad_tipo==1 ) $cad_usuariooperadorGames->setTipo($cad_tipo);
                $cad_usuariooperadorGames->setUgId($this->usuarios->getId());
                $cad_data_inclusao = $cad_usuariooperadorGames->getDataInclusao();
                $cad_data_ultimo_acesso = $cad_usuariooperadorGames->getDataUltimoAcesso();
                $instUsuarioGamesOperador = new UsuarioGamesOperador;
                $msg = $instUsuarioGamesOperador->atualizar($cad_usuariooperadorGames);

            }

            if($msg == "")
            {
                $msg = "Cadastro atualizado com sucesso!";
                $msg = "<p class='txt-verde'>$msg</p>";
    //                    redireciona
    //                    $strRedirect = "/prepag2/dist_commerce/mensagem.php?msg=" . urlencode($msg) . "&pt=" . urlencode("Atualiza cadastro de Funcionário")  . "&link=" . urlencode("/prepag2/dist_commerce/conta/index.php");
               // $strRedirect = "/prepag2/dist_commerce/conta/operador_consulta.php?msg=" . urlencode($msg);
                //redirect($strRedirect);
                $this->msg = $msg;
                return true;
            }  else 
            {
                $msg = "<p class='txt-vermelho'>$msg</p>";
                $this->msg = $msg;
                return false;
            }
        }
    }
    
    public function pega($sel_id)
    {
        $operadorCadastrado = $this->usuarios->operadorCadastrado($this->usuarios->getId(), $sel_id);
        
        if(!$operadorCadastrado){
            $this->accessDenied();
        }else{
            $rs = null;
            $filtro['ugo_id'] = $sel_id;
    //echo "sel_id: $sel_id<br>";
            $instUsuarioGamesOperador = new UsuarioGamesOperador;
            $ret = $instUsuarioGamesOperador->obter($filtro, null, $rs);
            if(!$rs || pg_num_rows($rs) == 0)
            {
                $this->msg .= "Login não encontrado." . "\n<br>" . $ENTRE_CONTATO_CENTRAL;	
                return false;
            } else 
            {
                return pg_fetch_array($rs);
            }
        }
    }
}
