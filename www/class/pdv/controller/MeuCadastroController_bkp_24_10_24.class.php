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


$pagina_titulo = "Meu Cadastro";


class MeuCadastroController extends HeaderController{
    public $raiz_do_projeto;
    public $erros = array();
        
    public function __construct(){
        $this->objBanner = new BannerBO;
        
        parent::__construct();
    }
    
    public function atualizaCadastro($post, $file = "", $name_file_old = "")
    {
        if($this->validaCamposEdicao($post))
        {
            $telefone = $this->splitTelphoneNumber($post['telefone_contato']); //$telefone_contato);
            $celular = $this->splitTelphoneNumber($post['celular_contato'], true); //$celular_contato, true);
            
            $objUsuarios = new UsuarioGames;
            $objUsuarios->setId($this->usuarios->getId());
            $objUsuarios->setTelDDD($telefone['ddd']);
            $objUsuarios->setTel($telefone['number']);
            $objUsuarios->setCelDDD($celular['ddd']);
            $objUsuarios->setCel($celular['number']);
            $objUsuarios->setNomeFantasia($post['fantasia_empresa']);
            $objUsuarios->setTipoEstabelecimento($post['tipo_estabelecimento_empresa']);
            $objUsuarios->setFaturaMediaMensal($post['faturamento_medio']);
            $objUsuarios->setReprVendaMSN($post['skype']);
            $objUsuarios->setSite($post['site']);
			
			if(isset($post["corCaixa"]) && isset($post["corBotao"]) && isset($post["corFundo"]) && isset($post["corTexto"])){
				$nomeArquivo = ($file["logo"]["error"] == 0 && $file["logo"]["size"] > 0)?$this->usuarios->getId().".".pathinfo($file["logo"]["name"], PATHINFO_EXTENSION): $name_file_old;
				$extensoesPermitidas = array("png", "jpg", "");
			    if(!in_array(strtolower(pathinfo($nomeArquivo, PATHINFO_EXTENSION)), $extensoesPermitidas)){
					$this->erros = "Esses tipo de arquivo não é permitido";
					return false;
				}
				if(!empty($post['emailMark'])){
					if(filter_var($post['emailMark'], FILTER_VALIDATE_EMAIL)){
						$emailMarketing = $post['emailMark']; 
					}else{
						$this->erros = "O e-mail marketing digitado está invalido";
					    return false;
					}
				}else{
					$emailMarketing = "";
				}
				// todas as cores não podem ser pretas #000000
				if($post["corCaixa"] != "#000000" || $post["corBotao"] != "#000000" || $post["corFundo"] != "#000000"){ 
					$objUsuarios->setReprLegalMSN(json_encode(["caixa"=> $post["corCaixa"], "botao"=> $post["corBotao"], "fundo"=> $post["corFundo"], "logo"=> $nomeArquivo, "texto"=> $post["corTexto"], "emailMark" => $emailMarketing]));
					if(!empty($file["logo"]["name"])){	
						 $files = scandir("/www/public_html/imagens/pdv/logos");
						 foreach($files as $key => $value){
							 if($value != "." && $value != ".."){
								 $nomeNoDir = pathinfo($value, PATHINFO_FILENAME);
								 if($nomeNoDir == $this->usuarios->getId()){
									 unlink("/www/public_html/imagens/pdv/logos/".$value);
								 }
							 }
						 }
						 $caminho = "/www/public_html/imagens/pdv/logos/".$nomeArquivo;
						 if(!move_uploaded_file($file["logo"]["tmp_name"], $caminho)){
							 $this->erros = "Não foi possivel cadastrar seu logo";
							 return false;
						 }	
				    }
				}else{
					 $this->erros = "Todas as cores não podem ser iguais";
					 return false;
				}
			}

            if ($objUsuarios->getTipoEstabelecimento() == "Outros") 
            {
                $temp_tipo_estabelecimento_empresa = $post['outro_estabelecimento'];
                $sql = "select te_id from tb_tipo_estabelecimento where UPPER(te_descricao)='" . strtoupper(str_replace("'", '"', $temp_tipo_estabelecimento_empresa)) . "'";
                $rs_select_tipo_estabelecimento = SQLexecuteQuery($sql);
                
                if ($rs_select_tipo_estabelecimento_row = pg_fetch_array($rs_select_tipo_estabelecimento))
                {
                    //echo  "rs_select_tipo_estabelecimento_row [te_id]: ".$rs_select_tipo_estabelecimento_row['te_id']."<br>";
                    $objUsuarios->setTipoEstabelecimento($rs_select_tipo_estabelecimento_row['te_id']);
                }//end if ($rs_select_tipo_estabelecimento_row = pg_fetch_array($rs_select_tipo_estabelecimento))
                else 
                {
                    $post['outro_estabelecimento'] = utf8_encode(str_replace("'", '"', $post['outro_estabelecimento']));
                    $sql = "INSERT INTO tb_tipo_estabelecimento (te_ativo,te_descricao) VALUES (0,'" . $post['outro_estabelecimento'] . "');"; //".utf8_decode($resposta)."

                    $rs_tipo_estabelecimento = SQLexecuteQuery($sql);
                    
                    if (!$rs_tipo_estabelecimento)
                    {
                        //echo "Erro ao salvar informa&ccedil;&otilde;es do tipo de estabelecimento.<br>";
                    } 
                    else 
                    {
                        $sql = "select te_id from tb_tipo_estabelecimento where UPPER(te_descricao)='" . strtoupper(str_replace("'", '"', $post['outro_estabelecimento'])) . "'";
                        $rs_select_tipo_estabelecimento_inserido = SQLexecuteQuery($sql);
                        $rs_select_tipo_estabelecimento_inserido_row = pg_fetch_array($rs_select_tipo_estabelecimento_inserido);
                        $objUsuarios->setTipoEstabelecimento($rs_select_tipo_estabelecimento_inserido_row['te_id']);
                    }
                }
            }
            
            $retorno = $this->usuarios->atualizar($objUsuarios);
            if($retorno != "")
            {
                $this->erros = $retorno;
                return false;
            }
            else
            {
                $instUsuarioGames =  new UsuarioGames();
                $this->usuarios = $instUsuarioGames->getUsuarioGamesById($objUsuarios->getId());
                return true;
            }
        }
        else
        {
            return false;
        }
    }
    
    public function getEstabelecimentos(){
        
        $sql = "select te_id,te_descricao from tb_tipo_estabelecimento where te_ativo = 1 order by te_descricao;";
        $pdo = $con = ConnectionPDO::getConnection();
        $pdo = $con->getLink();
        $rs = $pdo->prepare($sql);
        $rs->execute();
        while ($rs_tipo_estabalecimento = $rs->fetch(PDO::FETCH_ASSOC)) {
            $arrEstabelecimento[$rs_tipo_estabalecimento['te_id']] = $rs_tipo_estabalecimento['te_descricao'];

        } 
        
//        MÉTODO ANTIGO DE CARREGAMENTO
//        
//        $arrEstabelecimento['1'] = "Lan House";
//        $arrEstabelecimento['3'] = "Loja de Games";
//        $arrEstabelecimento['2'] = "Loja de Informática e afins";
//        
//        if( $this->usuarios->getTipoEstabelecimento() != 1 &&
//            $this->usuarios->getTipoEstabelecimento() != 2 &&
//            $this->usuarios->getTipoEstabelecimento() != 3)
//        {
//            if($this->usuarios->getTipoEstabelecimento() != null)
//            {
//                $sql = "select te_id, te_descricao from tb_tipo_estabelecimento where te_id = ".$this->usuarios->getTipoEstabelecimento();
//                $rs_select_tipo_estabelecimento = SQLexecuteQuery($sql);
//
//                if ($rs_select_tipo_estabelecimento_row = pg_fetch_array($rs_select_tipo_estabelecimento))
//                {
//                    //echo  "rs_select_tipo_estabelecimento_row [te_id]: ".$rs_select_tipo_estabelecimento_row['te_id']."<br>";
//                    $arrEstabelecimento[$rs_select_tipo_estabelecimento_row['te_id']] = $rs_select_tipo_estabelecimento_row['te_descricao'];
//                }
//            }
//            
//        }
        
        
        return $arrEstabelecimento;
    }
    
    public function splitTelphoneNumber($number, $isMobile = false) {
        $digitMobile = ($isMobile) ? '9,10' : '9';
        $regex = '/\(([0-9]{2})\)\s([0-9\-]{' . $digitMobile . '})/';

        preg_match_all($regex, $number, $matches);

        if (is_null($matches) || count($matches) === 0) {
            return false;
        }

        //    var_dump($regex);exit;

        return array('ddd' => $matches[1][0], 'number' => str_replace('-', '', $matches[2][0]));
    }
    
    
    
    public function validaCamposEdicao($post){
        $this->erros = array();
        
        if(strlen($post['telefone_contato']) < 14){
            $this->erros['telefone_contato'];
        }
        
        if(strlen($post['celular_contato']) < 14){
            $this->erros['celular_contato'];
        }
        
        if(strlen($post['fantasia_empresa']) < 5){
            $this->erros['fantasia_empresa'];
        }
        
        if(strlen($post['tipo_estabelecimento_empresa']) < 1 || ($post['tipo_estabelecimento_empresa'] == "Outros" && strlen($post['outro_estabelecimento']) < 5)){
            $this->erros['tipo_estabelecimento_empresa'];
        }
        
        if(strlen($post['faturamento_medio']) < 1){
            $this->erros['faturamento_medio'];
        }
        
        return (empty($this->erros)) ? true : false;

    }
}
