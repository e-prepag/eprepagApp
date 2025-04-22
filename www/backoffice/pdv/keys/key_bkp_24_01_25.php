<?php
 
 session_start();
 require_once '../../../includes/constantes.php';
 require_once "/www/includes/main.php";
 require_once $raiz_do_projeto."backoffice/includes/topo.php";
 require_once "/www/includes/bourls.php";
 
 $inicial = isset($_GET["ini"])? $_GET["ini"]:0;
 $limiteporpagina = 6;
 $msg = "";

 $conexao_new_epp = function(){
	//Conectando ao Banco de dados
	try{
		$username = 'eprepaga_pagorama';
		$password = 'U3yARhv6HcJN';
		$pdo = new PDO('mysql:host=177.11.54.107;port=3306;dbname=eprepaga_pag', $username, $password);
	}catch(PDOEXCEPTION $e){ //5433 
		http_response_code(500);
		return false;
	}
	return $pdo;
 };
 
 if(isset($_POST["cadastrar"])){
	 
	if((isset($_POST["idPdv"]) && !empty($_POST["idPdv"])) && (isset($_POST["acesso"]) && !empty($_POST["acesso"]))){

		$conexao = ConnectionPDO::getConnection()->getLink();
		# recupera os dados do id de PDV digitado
		$sql = "select ug_cnpj,ug_cpf,ug_nome_fantasia from dist_usuarios_games where ug_id = :USU;";
		$procura = $conexao->prepare($sql);
		$procura->bindValue(":USU", $_POST["idPdv"]);
		$procura->execute();
		$resultado = $procura->fetch(PDO::FETCH_ASSOC);
		
		# verifica se já não temos uma chave para o PDV digitado
		$verificacao = $conexao_new_epp()->prepare("select id_eprepag from user where id_eprepag = :ID;");
		$verificacao->bindValue(":ID", $_POST["idPdv"]);
		$verificacao->execute();
		$retorno = $verificacao->fetch(PDO::FETCH_ASSOC);
		
		if($retorno != false){
			$msg = ["mensagem" => "O PDV j&#225; possui um chave cadastrada", "tipo" => "erro"];
		}else{
			
			$verificacaoUsuarioEPP = $conexao->prepare("select * from dist_usuarios_games where ug_id = :USU and ug_ativo = 1;");
			$verificacaoUsuarioEPP->bindValue(":USU", $_POST["idPdv"]);
			$verificacaoUsuarioEPP->execute();
			$retornoVerifica = $verificacaoUsuarioEPP->fetch(PDO::FETCH_ASSOC);
			
			if($retornoVerifica != false){
				$con = $conexao_new_epp();
				$con->beginTransaction();
				$inserirUsuario = $con->prepare("insert into user(documentId,cpf,preferredName,fullName,id_eprepag)values(:DOC,:CPF,:NOME,:NOME,:ID);");
				$cnpjFormatado = substr($resultado["ug_cnpj"], 0, 2).".".substr($resultado["ug_cnpj"], 2, 3).".".substr($resultado["ug_cnpj"], 5, 3)."/".substr($resultado["ug_cnpj"], 8, 4)."-".substr($resultado["ug_cnpj"], 12, 2);
				$inserirUsuario->bindValue(":DOC", $cnpjFormatado);
				$inserirUsuario->bindValue(":CPF", (!empty($resultado["ug_cpf"])? $resultado["ug_cpf"]: "000.000.000-00"));
				$inserirUsuario->bindValue(":NOME", $resultado["ug_nome_fantasia"]);
				$inserirUsuario->bindValue(":ID", $_POST["idPdv"]);
				$inserirUsuario->execute();
				
				if($inserirUsuario->rowCount() > 0){
					$idUsuarioNovoBanco = $con->lastInsertId();
					$pass = '';
					$confirmaSenha = false;
					for($max = 0;$max < 5;$max++){
						for($num = 0; $num < 12; $num++){
							$possibilidades = "abcdefghijklmniopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@$%&!";
							$pass .= $possibilidades[rand(0, strlen($possibilidades) - 1)];
						}
						
						$verificaSenha = $con->prepare("select count(*) as total from oauth_clients where client_secret = :VPASS;");
						$verificaSenha->bindValue(":VPASS", $pass);
						$verificaSenha->execute();
						$qtdeSenha = $verificaSenha->fetch(PDO::FETCH_ASSOC);
						
						if($qtdeSenha["total"] == 0){
							$confirmaSenha = true;
							break;
						}
						
					}

					if($confirmaSenha === true){
						
						$inserirChave = $con->prepare("insert into oauth_clients(client_id,client_secret,grant_types,scope,user_id)values(:ACESSO,:PASS,:TYPE,:SCOPE,:USU);");
						$inserirChave->bindValue(":ACESSO", $_POST["acesso"]);
						$inserirChave->bindValue(":PASS", $pass); 
						$inserirChave->bindValue(":TYPE", 'client_credentials');
						$inserirChave->bindValue(":SCOPE", 'geral gift');
						$inserirChave->bindValue(":USU", $idUsuarioNovoBanco);
						$inserirChave->execute();
						
						if($inserirChave->rowCount() > 0){
							
							$inserirSituacaoChave = $con->prepare("insert into situacao_chave_api(cod_situacao,cod_usuario,criado)values(:SIT,:USU,:CRI);");
							$inserirSituacaoChave->bindValue(":SIT", 1);
							$inserirSituacaoChave->bindValue(":USU", $idUsuarioNovoBanco);
							$inserirSituacaoChave->bindValue(":CRI", $_SESSION["userlogin_bko"]);
							$inserirSituacaoChave->execute();
							
							if($inserirChave->rowCount() > 0){
								$con->commit();
								$msg = ["mensagem" => "A chave foi gerada com sucesso", "tipo" => "exito"];
								$_POST["Pesquisar"] = true;
								$_POST["selecaoPdv"] = $_POST["idPdv"];
								
							}else{
								$msg = ["mensagem" => "N&#227;o foi possivel cadastrar a chave para o PDV", "tipo" => "erro"];
								$con->rollback();
							}
						}else{
							$msg = ["mensagem" => "N&#227;o foi possivel cadastrar a chave para o PDV 2", "tipo" => "erro"];
							$con->rollback();
						}	
					}else{
						$msg = ["mensagem" => "O sistema excedeu a quantidade de senhas possiveis", "tipo" => "erro"];
						$con->rollback();
					}
				}else{
					$msg = ["mensagem" => "N&#227;o foi possivel cadastrar a chave para o PDV 1", "tipo" => "erro"];
					$con->rollback();
				}
			}else{
				$msg = ["mensagem" => "N&#227;o foi encotrado o PDV digitado ou o status n&#227;o est&#225; ativo", "tipo" => "erro"];
			}
		}
	}else{
		$msg = ["mensagem" => "Preencha o campo que esta vazio", "tipo" => "erro"];
	}
 }
 if(isset($_GET["cod"]) && !empty($_GET["cod"])){
    if($_GET["action"] == 1){
		
		$con = $conexao_new_epp();
		$con->beginTransaction();
		$atulizarChave = $con->prepare("update oauth_clients set scope = '' where user_id = :USU;");
		$atulizarChave->bindValue(":USU", $_GET["cod"]);
		$atulizarChave->execute();	
		if($atulizarChave->rowCount() > 0){
			$atulizarSituacao = $con->prepare("update situacao_chave_api set cod_situacao = 2 where cod_usuario = :USU;");
			$atulizarSituacao->bindValue(":USU", $_GET["cod"]);
			$atulizarSituacao->execute();
			if($atulizarSituacao->rowCount() > 0){
				$msg = ["mensagem" => "Chave atualizada com sucesso", "tipo" => "exito"];
		        $con->commit();
				$_POST["Pesquisar"] = true;
				
			}else{
				$msg = ["mensagem" => "Falha ao tentar atualizar a chave", "tipo" => "erro"];
		        $con->rollback();
			}
		}else{
			$msg = ["mensagem" => "Falha ao tentar atualizar a chave", "tipo" => "erro"];
		    $con->rollback();
		}
		
	}else{
		
		$con = $conexao_new_epp();
		$con->beginTransaction();
		$atulizarChave = $con->prepare("update oauth_clients set scope = 'geral gift' where user_id = :USU;");
		$atulizarChave->bindValue(":USU", $_GET["cod"]);
		$atulizarChave->execute();
		if($atulizarChave->rowCount() > 0){
			$atulizarSituacao = $con->prepare("update situacao_chave_api set cod_situacao = 1 where cod_usuario = :USU;");
			$atulizarSituacao->bindValue(":USU", $_GET["cod"]);
			$atulizarSituacao->execute();
			if($atulizarSituacao->rowCount() > 0){
				$msg = ["mensagem" => "Chave atualizada com sucesso", "tipo" => "exito"];
		        $con->commit();
				$_POST["Pesquisar"] = true;
	
			}else{
				$msg = ["mensagem" => "Falha ao tentar atualizar a chave", "tipo" => "erro"];
		        $con->rollback();
			}
		}else{
			$msg = ["mensagem" => "Falha ao tentar atualizar a chave", "tipo" => "erro"];
		    $con->rollback();
		}
		
	}
 }
 if(isset($_POST["Pesquisar"]) || isset($_GET["ini"])){
	
	$id = isset($_POST["selecaoPdv"])? $_POST["selecaoPdv"]: "";
	$inicial = !empty($id)? 0: $inicial;
	$filtro = !empty($id)? "id_eprepag = :ID": "id_eprepag is not null";
	if(empty($id)){
		$queryTotal = $conexao_new_epp()->prepare("select count(*) as numtotal from oauth_clients where user_id <> 5;"); 
		$queryTotal->execute();
		$resultadoTotal = $queryTotal->fetch(PDO::FETCH_ASSOC);
		// não deixa passar do limite de registros no banco
		if($inicial >= $resultadoTotal["numtotal"] && isset($_GET["action"]) && $_GET["action"] == "pro"){
			$inicial = $inicial - $limiteporpagina;
		}
	}else{
		$resultadoTotal["numtotal"] = 1;
	}
	$query = $conexao_new_epp()->prepare("select cod_situacao,client_id,preferredName,client_secret,user_id,id_eprepag from
	user inner join oauth_clients on user_id = id_new inner join situacao_chave_api on cod_usuario = id_new where $filtro and id_eprepag <> 17371 limit $limiteporpagina offset $inicial;"); 
	if($id != ""){
		$query->bindValue(":ID", $id);
	}
    $query->execute();
    $resultadoSelecao = $query->fetchAll(PDO::FETCH_ASSOC);
    if(count($resultadoSelecao) == 0){
		$resultadoTotal["numtotal"] = 0;
	}
   
 }
 
?>
<style>
 
    .container-titulo{
		padding: 15px;
		outline: 1px solid #ccc;
		margin: 20px 0 0 0;
    }
    .titulo{
        margin: 20px 0;
        color: black;
    }
    .inpId{
        margin-right: 10px;
		margin-bottom: 3px;
        padding: 5px;
        border-radius: 0;
        border: solid 1px #ccc;
        outline: 0;
    }
    .inpId:focus{
        outline: 1px solid #000; 
    }
    .btenvia{
        color: white;
        background-color: #157347;
        border: none;
        border-radius: 5px;
        padding: 7px 10px;
    }
	.alerta{
		padding: 5px;
		background: ghostwhite;
	}
	.alerta > span{
		font-weight: bold;
	}
	.borda{
		background-color: black;
		height: 5px;
	}
    .tabela-selecao{
        width: 100%;
    }
    thead > tr > th, tbody > tr > .colt{
        border: 1px solid #ccc;
        padding: 10px;
        text-align: center;
    }
	thead > tr > th{
		color: black;
	}
	.dados{
		margin-bottom: 15px;
	}
	.info{
		display: inline-block;
		max-width: auto;
		margin: 10px 0 0 0;
		background-color: #bbb;
		color: #000;
		font-size: 1em;
		width: fit-content;
		border-top: 1px solid #000;
		border-left: 1px solid #000;
		border-right: 1px solid #000;
		padding: 5px 10px;
	}
    .rodape{
		display: inline-block;
		max-width: auto;
		margin: 25px 0 0 0;
		background-color: #bbb;
		color: #000;
		width: fit-content;
		border: 1px solid #000;
		padding: 5px 10px;
	}
	.total {
		margin-right: 10px;
	}
	.btn-rodape{
		padding: 0;
		margin-right: 0;
	}
	.btn-rodape a{
		display: block;
		padding: 5px 10px;
		color: black;
	}
	.btn-rodape a:hover{
		background-color: #7f8c8d;
	}
	.container-nome{
		display: inline-block;
        text-align: center;
	}
	.container-rodape{
		display: flex;
        justify-content: space-between;
	}
	.container-botoes-paginacao{
		text-align: end;
	}
	.aviso{
		color: red;
	}
	.icone-chave{
		font-size: 1.3em;
	}
	.container-tabela{
		overflow: auto;
	}
	
</style>
<div class="container-titulo">
    <?php if($msg != ""){ ?>
        <div style="color: <?php echo (isset($msg) && $msg["tipo"] == "exito")? "green": "red";?>;" class="alerta"><span>&#9888;</span> <?php echo ($msg != "")? $msg["mensagem"]: ""; ?></div>
	<?php } ?>
	<div class="borda"></div>
    <h3 class="titulo">Cria&ccedil;&atilde;o de chave em produ&ccedil;&atilde;o</h3>
    <form class="dados" action="https://<?php echo $server_url_complete ;?>/pdv/keys/key.php" method="POST">
	    <h5 class="aviso">* N&atilde;o utilize espa&ccedil;o, troque por '_'</h5>
		<h5 class="aviso">* N&atilde;o utilize acentos ou caracteres especiais</h5>
        <input type="number" name="idPdv" value="<?php echo isset($_POST["idPdv"])? $_POST["idPdv"]: "";?>" class="inpId" placeholder="Digite c&#243;digo do pdv">
		<input type="text" name="acesso" value="<?php echo isset($_POST["acesso"])? $_POST["acesso"]: "";?>" class="inpId" placeholder="Digite o acesso do pdv">
        <button name="cadastrar" class="btenvia"> 
            Criar chave
        </button>
    </form>
</div>

<div class="container-titulo">
    <div class="borda"></div>
    <h3 id="busca" class="titulo">Busca de chave em produ&ccedil;&atilde;o</h3>
    <form action="https://<?php echo $server_url_complete ;?>/pdv/keys/key.php" class="dados" method="POST">
        <input type="number" value="<?php echo isset($_POST["selecaoPdv"])? $_POST["selecaoPdv"]: "";?>" name="selecaoPdv" class="inpId" placeholder="Digite c&#243;digo do pdv">
        <button name="Pesquisar" class="btenvia"> 
            Pesquisar
        </button>
    </form>
	<span>Nome: </span>
    <?php if(isset($resultadoSelecao)){ ?>
	    <div class="container-nome">
	    <?php for($num = 0; $num < 6;$num++){ 
		        $nome = isset($resultadoSelecao[$num]["preferredName"])? $resultadoSelecao[$num]["preferredName"]: "N&atilde;o encotrado";
			    if(!isset($resultadoSelecao[$num])){
					break;
				}
		?>
		    <span class="info"><?php echo $nome; ?></span>
		<?php } ?>
		</div>
		<div class="container-tabela">
		<table id="tabela-chave" class="tabela-selecao">
			<thead>
				<tr>
				    <th>#</th>
					<th>PDV</th>
					<th>Login</th>
					<th>Senha</th>
					<th>Situa&ccedil;&atilde;o</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
                <?php 
				    if($resultadoSelecao != false){
						$qdte = count($resultadoSelecao);
                        foreach($resultadoSelecao as $key => $value){
							$situacao = isset($value["cod_situacao"])? $value["cod_situacao"]: "N&atilde;o encotrado";	
				?>  
					<tr>
					    <td class="colt icone-chave">&#128272;</td>
						<td class="colt"><?php echo $value["id_eprepag"]; ?></td>
						<td class="colt"><?php echo $value["client_id"]; ?></td>
						<td class="colt"><?php echo $value["client_secret"]; ?></td>
						<td class="colt"><?php echo (!is_numeric($situacao))? $situacao:(($situacao == 1)? "Ativo": "Inativo"); ?></td>
						<td class="colt"><?php echo (!is_numeric($situacao))? $situacao:(($situacao == 1)? '<a href="https://'.$server_url_complete.'/pdv/keys/key.php?cod='.$value["user_id"].'&action=1">&#10060;</a>'
						: '<a href="https://'.$server_url_complete.'/pdv/keys/key.php?cod='.$value["user_id"].'&action=2">&#9989;</a>'); ?></td>
					</tr>
                <?php 
				        }
				    }else{ 
				?>
                    <tr>
						<td class="colt" colspan="6">Nenhum registro encontrado</td>
					</tr>
                <?php } ?> 
			</tbody>
		</table>
		</div>
		<div class="container-rodape">
		    <div>
				<span class="rodape">Chaves na pagina: <?php echo isset($qdte)? $qdte: 0; ?></span>
				<span class="rodape total">Total de chaves: <?php echo $resultadoTotal["numtotal"]; ?></span>
			</div>
			<div class="container-botoes-paginacao">
				<?php if($resultadoTotal["numtotal"] > $limiteporpagina){ ?>
					<span class="rodape btn-rodape"><a href="https://<?php echo $server_url_complete ;?>/pdv/keys/key.php?action=ante&ini=<?php echo ($inicial <= 0)? 0: $inicial - $limiteporpagina; ?>">anterior</a></span>
					<span class="rodape btn-rodape"><a href="https://<?php echo $server_url_complete ;?>/pdv/keys/key.php?action=pro&ini=<?php echo ($inicial >= $resultadoTotal["numtotal"])? $inicial: $inicial + $limiteporpagina; ?>">proxima</a></span>		
				<?php } ?>
			</div>
		</div>
    <?php } ?>
</div>
<script>
    $(document).ready(() => {
		let params = new URLSearchParams(window.location.href);
        if(params.has("ini")){
			window.scrollTo({ top: $("#tabela-chave").offset().top, left: 0, behavior: 'smooth' });
		}else{
			if($("#tabela-chave").length){
				window.scrollTo({ top: $("#tabela-chave").offset().top, left: 0, behavior: 'smooth' });
			}
		}
	});
</script>
<?php
 require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>