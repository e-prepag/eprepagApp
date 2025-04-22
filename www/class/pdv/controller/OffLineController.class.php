<?php
/**
 * Classe para as regras de negocio das vendas
 *
 * @author Diego
 * @email diego.gomes@e-prepag.com.br
 * @date 16-07-2015
 */
require_once RAIZ_DO_PROJETO . 'class/util/Util.class.php';
require_once RAIZ_DO_PROJETO . 'class/pdv/controller/HeaderController.class.php';
require_once RAIZ_DO_PROJETO . 'db/ConnectionPDO.php';
$pagina_titulo = "Meu Cadastro";


class OfflineController extends HeaderController{
    public $raiz_do_projeto;
    public $erros = array();

    public function __construct() {

    }
    
    public function relembraSenha($post)
    {
        try{
            if(isset($post['login']) && $post['login'] != "" && strlen($post['login']) <= 100)
            {
                $sql = "select ug_id from dist_usuarios_games where upper(ug_login) = :ugLogin and ug_ativo = 1 and (ug_substatus = 11 or ug_substatus = 9)";
                $arrParam = array(':ugLogin'    => strtoupper($post['login']));
                
                $pdo = $con = ConnectionPDO::getConnection();
                $pdo = $con->getLink();
                $rs = $pdo->prepare($sql);
                $rs->execute($arrParam);
                $rs_row = $rs->fetch(PDO::FETCH_ASSOC);
                if($rs_row['ug_id'] > 0) 
                {
                    $envioEmail = new EnvioEmailAutomatico(TIPO_USUARIO_LAN, 'EsqueciSenhaLan');
                    $envioEmail->setUgID($rs_row['ug_id']);
                    $envioEmail->MontaEmailEspecifico();
                    return true;
                }else{
                    throw new Exception("Login não encontrado ou desabilitado.");
                }
            }else{
                throw new Exception("Login inválido");
            }
        } catch (Exception $ex) {
            $geraLog = new Log("ESQUECIMINHASENHA-LH",array("ERROR: ".  implode(" / ", $this->erros),
                                              "FILE: ".$ex->getFile(),
                                              "LINE ".$ex->getLine()));
            return false;
        }
    }
}
