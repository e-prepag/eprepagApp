<?php
require_once "../../../includes/constantes.php";
require_once DIR_CLASS . "gamer/controller/HeaderController.class.php";
require_once RAIZ_DO_PROJETO . 'consulta_cpf/config.inc.cpf.php';
$usuarios = new UsuarioGames;
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

if(isset($_POST['login']) && !empty($_POST['login'])){
	
	$fileLog = fopen("/www/log/cadastro_games.txt", "a+");
	fwrite($fileLog, "Dta requis�o: ". date("d-m-Y H:i:s") ."\n");
	fwrite($fileLog, "Dados recebidos: ". json_encode($_POST) ."\n");
	fwrite($fileLog, str_repeat("*", 50)."\n\r");
	fclose($fileLog);

    //if($_SERVER["REMOTE_ADDR"] == "201.93.162.169"){

	  if(!empty($_POST["g-recaptcha-response"])){
			
		   $tokenInfo = ["secret" => "6Lc4XtkkAAAAAJYRV2wnZk_PrI7FFNaNR24h7koQ", "response" => $_POST["g-recaptcha-response"], "remoteip" => $_SERVER["REMOTE_ADDR"]];             

			$recaptcha = curl_init();
			curl_setopt_array($recaptcha, [
				CURLOPT_URL => "https://www.google.com/recaptcha/api/siteverify",
				CURLOPT_CUSTOMREQUEST => "POST",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POSTFIELDS => http_build_query($tokenInfo)

			]);
			$retorno = json_decode(curl_exec($recaptcha), true);
			curl_close($recaptcha);

			if($retorno["success"] != true || (isset($retorno["error-codes"]) && !empty($retorno["error-codes"]))){
				  $erros[] = "<p>Processo invalidado por RECAPTCHA.</p>";
			}
		   
	  }else{
		   $erros[] = "<p>Voc� deve realizar a verifica��o do RECAPTCHA para prosseguir.</p>";
	  }

    //}
	
	function verificaPOST($referer,$POST){
            
		//if (strpos($referer,"bronzato.com.br")===false && strpos($referer,"contause.digital")===false){return false;}
		$flag=true;
		foreach($_POST as $xa=>$xb){
			$xb = serialize($xb);
			if (strpos($xb,"dbms_pipe.receive_message")!==false || strpos($xb,"DBMS_PIPE.RECEIVE_MESSAGE")!==false || strpos($xb,"delete")!==false || strpos($xb,"delete")!==false || strpos($xb,"update")!==false || strpos($xb,"select")!==false ){
					return false;
			}
			
			if (strpos($xb,"dbms_pipe.receive_message")!==false || strpos($xb,"DBMS_PIPE.RECEIVE_MESSAGE")!==false ||strpos(hexToStr($xb),"delete")!==false || strpos(hexToStr($xb),"update")!==false || strpos(hexToStr($xb),"select")!==false ){
					return false;
			}
		}
		
		if ($flag){return true;}else{return false;}
	}
	
	function strToHex($string){
		$hex = '';
		for ($i=0; $i<strlen($string); $i++){
			$ord = ord($string[$i]);
			$hexCode = dechex($ord);
			$hex .= substr('0'.$hexCode, -2);
		}
		return strToUpper($hex);
	}
	
	function hexToStr($hex){
		$string='';
		for ($i=0; $i < strlen($hex)-1; $i+=2){
			$string .= chr(hexdec($hex[$i].$hex[$i+1]));
		}
		return $string;
	}
    
    require_once DIR_CLASS . "util/Validate.class.php";
    require_once DIR_CLASS . "util/Login.class.php";
    
    $erros = array();
    $validate = new Validate;
    $clsLogin = new Login($_POST['senha']);
    
    if($validate->qtdCaracteres($_POST['login'], 5, 100) > 0)
        $erros[] = "<p>O Login deve ter mais de 5 caracteres.</p>";
    
    if($validate->email($_POST['email']) > 0 || $_POST['email'] != $_POST['conf_mail'])
        $errros[] = "<p>A confirma��o de e-mail est� incorreta. Verifique os dados inseridos.</p>";
    
    if($clsLogin->valida() > 0 || $_POST['senha'] != $_POST['conf_senha']){
        $erros[] = "<p>Senha n�o atinge os n�veis de seguran�a desejados.</p>";
    }
    
	$cpf = str_replace([".", "-"], "", $_POST['cpf']);
	$dataNascimento = $_POST['dtNasc'];
	
	if(!verificaPOST("", $_POST)){
		$erros[] = "<p>N�o foi possivel continuar seu processo.</p>";
	}else{
	
		if(strpos($dataNascimento, "/") !== false){
			$dataNascimento = str_replace(" ", "", $dataNascimento); 
			$dataQuebrada = explode("/", $dataNascimento);
			$verificaNumeroDePartes = (count($dataQuebrada) == 3)? true: false;
			$verificaParteVazia = (array_search("", $dataQuebrada) === false)? true: false;
			if($verificaNumeroDePartes === true && $verificaParteVazia === true && strlen($dataNascimento) == 10){
				
				$onminidata = true;
				if($onminidata){
					require "/www/consulta_cpf/Onminidata.php";
					$onminidata = new Onminidata();
					$onminidata->query($cpf, $dataNascimento);
					$result = $onminidata->collects_data();
					$id_search = $onminidata->take_property($result, "id_search");
					///sleep(5);
					$tempoRetorno = 0;
					$retorno = $onminidata->result_status_search($id_search);
					
					while($retorno["pesquisas"]["camposResposta"]["status"] != "DadoDisponivel"){
					 
						 if($tempoRetorno >= 7){
							 break;
						 }
					 
						$retorno = $onminidata->result_status_search($id_search);
						$tempoRetorno++;
						sleep(10 * $tempoRetorno);
					}
					
					$file = fopen("/www/log/retorno_cpf.txt", "a+");
					fwrite($file, "conteudo ".json_encode($retorno)."\n");
					fwrite($file, "d1 ".$retorno["pesquisas"]["camposResposta"]["data_nascimento"]."\n");
					fwrite($file, "d2 ".$dataNascimento."\n");
					fwrite($file, str_repeat("*", 50)."\n");
					fclose($file);
					curl_close($curl);
					
				}else{
					$curl = curl_init();
					curl_setopt_array($curl, [
						CURLOPT_URL => "https://ws.hubdodesenvolvedor.com.br/v2/cpf/?cpf=$cpf&data=$dataNascimento&token=104048520UeLqsXgHvd187856448&ignore_db", //https://ws.hubdodesenvolvedor.com.br/v2/cpf/?cpf=$cpf&data=$dataNascimento&token=104048520UeLqsXgHvd187856448
						CURLOPT_CUSTOMREQUEST => "POST",
						CURLOPT_RETURNTRANSFER => true
					]);
					$retorno = json_decode(curl_exec($curl), true);
				}
				
				if(
					(isset($retorno["pesquisas"]["camposResposta"]["situacao"]) && $retorno["pesquisas"]["camposResposta"]["situacao"] == "REGULAR") ||
					(($retorno["result"]["situacao_cadastral"] == "REGULAR" || $retorno["result"]["situacao_cadastral"] == "PENDENTE DE REGULARIZA��O") && $retorno["status"] == true)
				){

					if((isset($retorno["pesquisas"]["camposResposta"]["data_nascimento"]) && $retorno["pesquisas"]["camposResposta"]["data_nascimento"] != $dataNascimento) || (isset($retorno["result"]["data_nascimento"]) && $retorno["result"]["data_nascimento"] != $dataNascimento)){
						$erros[] = "<p>O cpf n�o foi validado na receita federal.</p>";
					}else{
						$dd = isset($retorno["result"]["data_nascimento"])? $retorno["result"]["data_nascimento"]: $retorno["pesquisas"]["camposResposta"]["data_nascimento"];
						list($ano, $mes, $dia) = explode('-',DateTime::createFromFormat('d/m/Y', $dd)->format("Y-m-d")); 
						$hoje = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
						$nascimento = mktime( 0, 0, 0, $mes, $dia, $ano);
						$idade = floor((((($hoje - $nascimento) / 60) / 60) / 24) / 365.25);
						
						//echo "<script>console.log(".$ano.")</script>";
						if($idade >= 12){
							
							$nome = isset($retorno["result"]["nome_da_pf"])? $retorno["result"]["nome_da_pf"]: $retorno["pesquisas"]["camposResposta"]["nome"];
									
							$usuarios = new UsuarioGames;
							$usuarios->setLogin($_POST['login']);
							$usuarios->setNome($nome);
							$usuarios->setNomeCPF($nome);
							$usuarios->setCPF($_POST['cpf']);
							$usuarios->setDataNascimento($_POST['dtNasc']);
							$usuarios->setEmail($_POST['email']);
							$usuarios->setSenha($_POST['senha']);
							
						}else{
							$erros[] = "<p>A idade m�nima para o cadastro � de 12 anos.</p>";
						}
					
					}
				}else{
					$erros[] = "<p>O cpf n�o foi validado na receita federal.</p>";
				}
				
			}else{
				$erros[] = "<p>A data de nascimento digitada est� invalida</p>";
			}
		}else{
			$erros[] = "<p>A data de nascimento digitada est� invalida</p>";
		}
		
    }
	
	
	/*$cpf = new classCPF();
	$parametros = array(
		'cpfcnpj'		=> str_replace([".", "-"], "", $_POST['cpf']),
		'data_nascimento'	=> str_replace("/", "-", $_POST['dtNasc'])
	);
	$resposta = null;
	
	$cpf->Req_EfetuaConsulta($parametros,$resposta);
	if($resposta["pesquisas"]["camposResposta"]["situacao"] == "REGULAR" && $resposta["pesquisas"]["camposResposta"]["data_nascimento"] == $_POST['dtNasc']){
		$usuarios = new UsuarioGames;
        $usuarios->setLogin($_POST['login']);
		$usuarios->setCPF($_POST['cpf']);
		$usuarios->setDataNascimento($_POST['dtNasc']);
        $usuarios->setEmail($_POST['email']);
        $usuarios->setSenha($_POST['senha']);
    }else{
		$erros[] = "<p>O cpf n�o foi validado na receita federal.</p>";
	}
	
	*/
    if(empty($erros)){	
        $insere = $usuarios->inserirMelhorado();
        if(is_array($insere)){
            $erros = $insere;
        }else{
            Util::redirect("/game/");
        }
    } 
}

$controller = new HeaderController;
$controller->setHeader();

require_once RAIZ_DO_PROJETO . "public_html/game/includes/termos-de-uso.php";
$termosDeUso = strip_tags($termosDeUso);
?>
<script src="/js/valida.js"></script>
<script src="/js/validaSenha.js"></script>
<script>
<?php
    if(!empty($erros)){
        print "manipulaModal(1,\"".implode($erros)."\",'Aten��o');";
    }
?>
$(function(){
    $("#cadastro").submit(function(){
        
        if(grecaptcha.getResponse() == "" || grecaptcha.getResponse().length == 0){
            manipulaModal(1,"Voc� deve fazer a verifica��o do RECAPTCHA para finalizar seu cadastro.",'Erro');
            return false;
        }
	
        
        if($("#termos_uso").is(":checked") && $("#termos_responsaveis_uso").is(":checked")){
            if(!valida()){
                return false;
            }

            var erro = validaFormSenha();
            if(erro.length > 0)
            {
                manipulaModal(1,erro.join("<br>"),'Erro');
                return false;
            }
        }else{
            manipulaModal(1,"Voc� deve concordar com os termos de uso e termos dos respons�veis.",'Erro');
            return false;
        }
        
    });
});
</script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script src="https://kit.fontawesome.com/e045fafe2e.js" crossorigin="anonymous"></script>
<div class="container txt-cinza bg-branco  p-bottom40">
    <div class="row top10">
		<!-- <div class="alert alert-danger" style="margin: 5px 10px;" role="alert">
		  <i class="fas fa-exclamation-triangle"></i>Devido � instabilidade na Receita Federal, seu cadastro na E-prepag n�o poder� ser finalizado neste momento, mas voc� pode se cadastrar no Atimopay e comprar cr�ditos para seus games : <a href="https://linktr.ee/atimopay">https://linktr.ee/atimopay</a>
		</div> -->
		
		<div class="alert alert-success" style="margin: 5px 10px;" role="alert">
		  <i class="fas fa-gamepad"></i> Prefere comprar seus giftcards pelo celular ? Baixe agora mesmo o Atimopay <a href="https://linktr.ee/atimopay">clicando aqui</a>.
		</div> 
        <div class="col-md-12 txt-verde top10">
            <span class="glyphicon glyphicon-triangle-right graphycon-big pull-left" aria-hidden="true"></span><strong class="pull-left"><h4 class="top20">novo usu�rio?<span class="hidden-md hidden-lg"><br></span> fa�a aqui um r�pido cadastro!</h4></strong>
        </div>
        <div class="clearfix"></div>
        <div class="col-md-6 col-lg-6 col-sm-12 col-xs-12 borda-colunas-formas-pagamento">
            <form id="cadastro" method="post" class="text-right-lg text-rightmd text-left-sm text-left-xs">
                <div class="row top10">
                    <div class="col-md-6">
                        <label for="login">Login:</label>
                    </div>
                    <div class="col-md-6">
                        <input class="form-control" id="login" char="5" name="login" type="text" value="<?php if(isset($_POST['login'])) print $_POST['login'];?>">
                    </div>
                </div>
                <div class="row top20">
                    <div class="col-md-6">
                        <label for="email">Digite seu e-mail:</label>
                    </div>
                    <div class="col-md-6">
                        <input class="form-control" id="email" char="5" maxlength="100" name="email" type="text" value="<?php if(isset($_POST['email'])) print $_POST['email'];?>">
                    </div>
                </div>
                <div class="row top10">
                    <div class="col-md-6">
                        <label for="conf_mail">Confirma��o de e-mail:</label>
                    </div>
                    <div class="col-md-6">
                        <input class="form-control" id="conf_mail" onpaste="return false;" char="5" maxlength="100" name="conf_mail" type="text" value="<?php if(isset($_POST['conf_mail'])) print $_POST['conf_mail'];?>">
                    </div>
                </div>
                
				<div class="row top10">
                    <div class="col-md-6">
                        <label for="cpf">CPF:</label>
                    </div>
                    <div class="col-md-6">
                        <input class="form-control" id="cpf" onpaste="return false;" char="14" maxlength="14" name="cpf" type="text" value="<?php if(isset($_POST['cpf'])) print $_POST['cpf'];?>">
                    </div>
                </div>
				<div class="row top10">
                    <div class="col-md-6">
                        <label for="dtNasc">Data de nascimento:</label>
                    </div>
                    <div class="col-md-6">
                        <input class="form-control" id="dtNasc" onpaste="return false;" char="10" maxlength="10" name="dtNasc" type="text" value="<?php if(isset($_POST['dtNasc'])) print $_POST['dtNasc'];?>">
                    </div>
                </div>
				
                <div class="row top10">
                    <div class="col-md-6">
                        <label for="senha">Senha:</label>
                    </div>
                    <div class="col-md-6">
                        <input class="form-control novaSenha" maxlength="12" autocomplete="new-password" onpaste="return false;" char="6" id="senha"  name="senha" char="3" type="password" value="">
                    </div>
                </div>
                <div class="row top10">
                    <div class="col-md-6">
                        <label for="conf_senha">Confirma��o de senha:</label>
                    </div>
                    <div class="col-md-6">
                        <input class="form-control confirmacaoSenha" maxlength="12" onpaste="return false;" autocomplete="new-password" onpaste="return false;" char="6" id="conf_senha" char="3" name="conf_senha" type="password" value="">
                    </div>
					
					<div class="col-md-6 col-md-offset-6 col-sm-12 col-xs-12 txt-preto text-left">
						<span>Sua senha deve ter:</span>
						<ul>
							<li>De 6 a 12 caracteres</li>
							<li>Letras</li>
							<li>N�meros</li>
							<li>Caracteres especiais (!,?,*,$,%)</li>
						</ul>
					</div>

                </div>
                <div class="row top10">
                    <div class="col-md-6 col-md-offset-6">
                        <div class="progress w-auto">
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
                </div>
                <div class="row top10">
                    <div class="col-md-6">
                        <label for="comment" >Termos de uso:</label>
                    </div>
                    <div class="col-md-6">
                        <textarea class="form-control" rows="5" readonly="readonly"><?php echo $termosDeUso;?></textarea>
                    </div>
                </div>
                <div class="row top10 ">
                    <div class="col-md-6 col-lg-6 col-sm-10 col-xs-10">
                        <label for="termos_uso" >Li e aceito os termos de uso:</label>
                    </div>
                    <div class="col-md-6 col-lg-6 col-sm-2 col-xs-2 text-left">
                        <input id="termos_uso" type="checkbox" char="1" class="" name="termos_uso" value="">
                    </div>
                </div>
                <div class="row top10 ">
<!--                    <div class="col-md-6 col-lg-6 col-sm-10 col-xs-10">
                    </div>-->
                    <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12 text-left">
                        <ul>
                            <li>Esta conta deve ser utilizada para compras de cr�ditos para uso pessoal.</li>
                            <li>Limite de compras di�rio de R$<?php echo number_format($GLOBALS['RISCO_GAMERS_TOTAL_DIARIO'], 2) ?>, condicionado ao m�ximo de <?php echo CPF_QUANTIDADE_LIMITE ?> compras em 30 dias.</li>
                            <li>N�o � permitida a comercializa��o dos cr�ditos adquiridos. Quer ser um ponto de venda? Acesse: <a href="https://e-prepagpdv.com.br/" target="_blank">https://e-prepagpdv.com.br/</a></li>
                        </ul>
                    </div>
                </div>
				
				<div class="row top10">
					<div class="col-md-8 col-lg-10 col-sm-10 col-xs-10">
                        <label for="termos_uso" style="margin-left: 20px">Termo de Responsabilidade para pais e respons�veis:</label>
                    </div>
				</div>
				
				<div class="row top10">
					<div class="cold-md-12 col-lg-12 col-sm-12 col-xs-12 text-left">
						<ul>
							<li>Os usu�rios entre 12 e 18 anos devem certificar-se de ter lido o Termos e
							Condi��es de uso da Plataforma E-prepag, juntamente com seus pais ou
							respons�veis e que todo seu conte�do tenha sido entendido e aprovado.</li>
						</ul>
					</div>
				</div>
				
				<div class="row top10">
					<div class="col-md-12 col-lg-12 col-sm-12 col-xs-12 text-left" style="display: inline-flex;justify-content: flex-end">
                       <input id="termos_responsaveis_uso" type="checkbox" char="1" class="" name="termos_responsaveis_uso" value=""><span style="margin-top: 4px; margin-left: 4px;">Li e aceito os termos de responsabilidade para os pais e respons�veis.</span>
                    </div>
				</div>
				
                <div class="row top10">
					<div style="padding: 0 0 15px 40px;">
						<div class="g-recaptcha" data-sitekey="6Lc4XtkkAAAAAJrfsAKc99enqDlxXz4uq86FI9_T"></div>
					</div>
                    <div class="col-md-6 col-md-offset-6">
                        <input type="submit" class="pull-right btn btn-success" value="Prosseguir">
                    </div>
                </div>
              
            </form>
        </div>
        <div class="col-md-6 col-lg-6 col-sm-12 col-xs-12">
            <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12 txt-azul-claro">
                <h2>Seja um ponto de venda</h2>
            </div>
            <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12">
                <h4>Deseja cadastrar seu estabelecimento para vender cr�ditos de games e outros servi�os?</h4>
            </div>
            <div class="col-md-1 col-lg-1 col-sm-2 col-xs-2">
                <span class="glyphicon glyphicon-check txt-verde t0"></span>
            </div>
            <div class="col-md-11 col-lg-11 col-sm-10 col-xs-10">
                Mais de 1.000 games
            </div>
            <div class="col-md-1 col-lg-1 col-sm-2 col-xs-2">
                <span class="glyphicon glyphicon-check txt-verde t0"></span>
            </div>
            <div class="col-md-11 col-lg-11 col-sm-10 col-xs-10">
                Sistema 100% online
            </div>
            <div class="col-md-1 col-lg-1 col-sm-2 col-xs-2">
                <span class="glyphicon glyphicon-check txt-verde t0"></span>
            </div>
            <div class="col-md-11 col-lg-11 col-sm-10 col-xs-10">
                Sem custo de cadastro
            </div>
            <div class="col-md-6 col-lg-6 col-sm-12 col-xs-12 top10 align-center">
                <a href="/cadastro-de-ponto-de-venda.php" class="btn btn-info"><em>Fa�a agora seu cadastro</em></a>
            </div>
            <div class="col-md-6 col-lg-6 col-sm-12 col-xs-12 top10 bottom10 align-center">
                <a link="e-prepagpdv.com.br/" href="#" class="btn redirecionamento btn-info"><em>Veja aqui como funciona</em></a>
            </div>
        </div>
    </div>
</div>
</div>
<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 bg-info align-center borda-direita-basica p-bottom10">
        <h3>
            Quer ser um ponto de venda ?  
            <a href="/cadastro-de-ponto-de-venda.php" class="txt-branco" target="_blank"><b><span class="link-destaque">Cadastre-se</span></b></a>
            ou 
            <a href="https://e-prepagpdv.com.br/" class="txt-branco" target="_blank"><b><span class="link-destaque">saiba mais</span></b></a>.            
        </h3>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js" integrity="sha512-pHVGpX7F/27yZ0ISY+VVjyULApbDlD0/X0rgGbTqCE7WFW5MezNTWG/dnhtbBuICzsd0WQPgpE4REBLv+UqChw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
     $('#cpf').mask('000.000.000-00', {reverse: true});
	 $('#dtNasc').mask('00/00/0000', {placeholder: "__/__/____"});
</script>
<?php
require_once RAIZ_DO_PROJETO . "public_html/game/includes/footer.php";