<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."class/util/Validate.class.php";
require_once $raiz_do_projeto."class/util/Util.class.php";
require_once $raiz_do_projeto."includes/gamer/chave.php";
require_once $raiz_do_projeto."includes/gamer/AES.class.php";
require_once $raiz_do_projeto."class/util/Login.class.php";

if(!b_IsBKOUsuarioAdminBKO())
{
    Util::redirect("/");
}elseif(!isset($_POST['id']) || !is_numeric($_POST['id']))
{
    Util::redirect("/admin/usuarios/lista.php");
}

$con = ConnectionPDO::getConnection();
if ( !$con->isConnected() ) {
    // retornar os erros: $con->getErrors();
    die('Erro#2');
}

    
$grupos_id = unserialize($_SESSION["arrIdGrupos"]);
	
$msg = array();
$minCaracPass = 6;
$maxCaracPass = 12;  

$pdo = $con->getLink();
$sql = "SELECT id,shn_login,shn_nome,shn_mail, visualiza_dados FROM usuarios where id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute(array($_POST['id']));
$fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

if(isset($fetch[0]['id']) && isset($fetch[0]['shn_login']))
{
    $login = $fetch[0]['shn_login'];
    $id = $fetch[0]['id'];
    $nome = $fetch[0]['shn_nome'];
    $email = $fetch[0]['shn_mail'];
	$visualiza_dados = $fetch[0]['visualiza_dados'];
}

if(isset($_POST['nome']) && isset($_POST['email']) && isset($_POST['id']))
{

    $validate = new Validate();
    
    if(isset($_POST['nova_senha']) && $_POST['nova_senha'] != ""){
        
        $clsLogin = new Login($_POST['nova_senha']);
        $clsLogin->setLimiteCaracteres($minCaracPass, $maxCaracPass);

        if($clsLogin->valida() > 0){
            $msg[] = "Senha não atinge os níveis de segurança desejados.";
        }
        elseif($_POST['nova_senha'] !== $_POST['pass_confirm'])
        {
            $msg[] = "A confirmação de senha está diferente.";
        }
        else
        {
            //Instanciando Objetos para Descriptografia
            $chave256bits = new Chave();
            $aes = new AES($chave256bits->retornaChavePub());
            $passw = base64_encode($aes->encrypt(addslashes($_POST['nova_senha'])));
        }
    }
	
	if(isset($_POST["visualiza"]) && in_array(1, $grupos_id)){
        $visualiza_dados = $_POST["visualiza"];		
	}else{
		$visualiza_dados = "N";
	}
        
    if($validate->qtdCaracteres($_POST['id'],1,20))
        $msg[] = "Usuário não encontrado.";
    
    if($validate->qtdCaracteres($_POST['nome'],2,50))
        $msg[] = "Nome inválido.";
    
    if($validate->email($_POST['email']))
        $msg[] = "E-mail inválido";

    if(empty($msg))
    {
        $pdo = $con->getLink();
        $sql = "SELECT * FROM usuarios WHERE id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($_POST['id']));

        $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($fetch) == 1) 
        {
            if(isset($passw))
            {
                $update = "UPDATE usuarios set shn_password = ?, shn_nome = ?, shn_mail = ?, visualiza_dados = ? where id = ?";
                $params = array(
                                $passw,
                                $_POST['nome'],
                                $_POST['email'],
								$visualiza_dados,
                                $_POST['id']
                            );
            }else{
                $update = "UPDATE usuarios set shn_nome = ?, shn_mail = ?, visualiza_dados = ? where id = ?";
                $params = array(
                                $_POST['nome'],
                                $_POST['email'],
								$visualiza_dados,
                                $_POST['id']
                            );
            }
            
            
            $stmt = $pdo->prepare($update);
            $stmt->execute(
                            $params
                        );
            if($stmt->rowCount() == 1){
                $msg[] = "Dados alterados com sucesso. Clique <a href='lista.php'>aqui</a> para voltar.";
                $color = "txt-verde";
            }else{
                var_dump($stmt->rowCount());
                $msg[] = "Erro ao alterar senha. Entre em contato com o suporte.";
                $color = "txt-vermelho";
            }        
        }else{
            $msg[] = "Usuário para alteração não encontrado.";
            $color = "txt-vermelho";
        }
    }else{
        $color = "txt-vermelho";
    }
    
}

$sql = "SELECT grupos_descricao, g.grupos_id from grupos_usuarios g inner join grupos_acesso_usuarios u on g.grupos_id = u.grupos_id and u.id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute(array($id));
$fetchGrupos = $stmt->fetchAll(PDO::FETCH_OBJ);
?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="0">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><a href="lista.php">Listagem</a></li>
        <li class="active">Edição</li>
    </ol>
</div>    
<?php

if(!empty($msg))
{
    foreach($msg as $txt)
    {
?>
    <div class="col-md-12 top10 <?php echo $color;?>">
        <strong><?php echo $txt?></strong>
    </div>
<?php
    }
}

    if(isset($login)){
?>
    <form method="POST" id="form">
        <div class="col-md-7 top20 txt-preto">
            <div class="form-group col-md-12 has-feedback">
                <label class="control-label col-md-6 text-right" for="usuarios">
                    Usuario
                </label>
                <div class="col-md-6">
                    <span class="txt-azul"><?php echo $login;?></span>
                    <input type="hidden" name="id" value="<?php echo $id;?>">
                </div>
            </div>
            <div class="form-group col-md-12 has-feedback">
                <label class="control-label col-md-6 text-right" for="nome">
                    Nome
                </label>
                <div class="col-md-6">
                    <input type="text" class="form-control" name="nome" maxlength="50" value="<?php echo (isset($_POST['nome'])) ? $_POST['nome'] : $nome;?>">
                </div>
            </div>
            <div class="form-group col-md-12 has-feedback">
                <label class="control-label col-md-6 text-right" for="email">
                    E-mail
                </label>
                <div class="col-md-6">
                    <input type="text" class="form-control" name="email" value="<?php echo (isset($_POST['email'])) ? $_POST['email'] : $email;?>">
                </div>
            </div>
            <div class="form-group col-md-12 has-feedback">
                <label class="control-label  text-right col-md-6" for="nova_senha">
                    Nova senha
                </label>
                <div class="col-md-6">
                    <input type="password" name="nova_senha" label="senha atual " char="6" class="novaSenha form-control" id="nova_senha">
                </div>
                <div class="col-md-offset-6 col-md-6 top5">
                    <div class="progress">
                        <div class="progress-bar hidden progress-bar-danger" style="width: 33.33%">
                            <span class="sr-only">33.33% Complete (danger)</span>
                        </div>
                        <div class="progress-bar hidden progress-bar-warning" style="width: 33.33%">
                            <span class="sr-only">33.33% Complete (warning)</span>
                        </div>
                        <div class="progress-bar hidden progress-bar-success" style="width: 33.33%">
                            <span class="sr-only">33.33% Complete (success)</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-offset-6 col-md-6 txt-vermelho fontsize-p">
                    <span class="text-right"><?php echo vsprintf('*Sua senha deve ter: de %s a %s caracteres, letras, números, caracteres especiais (|,!,?,*,$, etc)',array($minCaracPass,$maxCaracPass));?></span>
                </div>
            </div>
            <div class="form-group col-md-12 has-feedback">
                <label class="control-label  text-right col-md-6" for="pass_confirm">
                    Confirme a nova senha
                </label>
                <div class="col-md-6">
                    <input type="password" name="pass_confirm" label="senha atual " char="6" class="confirmacaoSenha form-control" id="pass_confirm">
                </div>
            </div>
			<div class="form-group col-md-12 has-feedback">
                <label class="control-label  text-right col-md-6" for="visualiza_dados">
                    Visualiza informações <br>(Login, id, e-mail) <br> na listagem
                </label>
                <div class="col-md-6">
                    <input type="checkbox" name="visualiza" value="S" class="" id="visualiza_dados" <?php echo (isset($visualiza_dados) && $visualiza_dados == "S")? "checked ": ""; echo (!in_array(1, $grupos_id))? "disabled ": "";?>>
					<?php// echo $visualiza_dados;?>
                </div>
            </div>
            <div class="col-md-offset-6 col-md-6">
                <button type="button" class="btn btn-success botao-salvar">Salvar</button>
            </div>
        </div>
        <div class="col-md-5 espacamento">
            <div class="panel panel-default">
                <!-- Default panel contents -->
                <div class="panel-heading">Grupos</div>
                <!-- List group -->
                <ul class="list-group">
<?php
                    if(!empty($fetchGrupos)){
                        foreach($fetchGrupos as $objGrupos){
                            echo "<li class=\"list-group-item c-pointer\" param='".$objGrupos->grupos_id."'>".$objGrupos->grupos_descricao."</li>";
                        }
                    }else{
?>
                        <div class="panel-body">
                            <p>Usuário sem grupos cadastrados</p>
                            <p><a href="/admin/grupos/lista.php" class="btn btn-info">Clique aqui para cadastrar</a></p>
                        </div>
<?php
                    }
?>
                </ul>
            </div>
        </div>
        <script>
        $(function(){
            $(".botao-salvar").click(function(){
				let senha = $("#nova_senha").val();
				
				if(senha != ""){
					erro = validaFormSenha(); //funcao esta em /js/validaSenha.js
					if(erro.length > 0) {
						alert(erro.join("\n"));
					}
					else{
						$(form).submit();    
					}
				}else{
					$(form).submit();  
				}
                
            });
            $(".list-group-item").click(function(){
                
                var url = '/admin/grupos/edita.php';
                var form = $('<form action="' + url + '" method="post">' +
                  '<input type="text" name="id" value="' + $(this).attr("param") + '" />' +
                  '</form>');
                $('body').append(form);
                form.submit(); 
                
            });
        });
        </script>
<?php
    }else
    {
?>
    <div class="col-md-12 top20">
        <div class="alert alert-danger" role="alert">
            <strong>Erro:</strong> usuário não encontrado
        </div>
    </div>
<?php
    }
?>    
<script src="<?php echo $server_url_ep;?>/js/validaSenha.js"></script>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</body>
</html>