<?php require_once __DIR__ . '/../includes/constantes_url.php'; ?>
<?php

// include do arquivo contendo IPs DEV
require_once DIR_INCS . 'configIP.php';

class AtivacaoPinTemplate {
	
	public	$url_ativacao;
	private	$jquery_core_include;
	private	$usuarioLogado; 
	private $exibe_botao_pagar;
	private $saldo;
	private $valor_pedido;
	private $saldo_final;
	private $email;
	private $captcha_valor;
	private $lista_pins = array();
	private $box_carga_saldo;
	
	/**
	 * Construtor
	 * 	 
	 * @param Array $paramList => URL dos recursos da página, como Imagens, scripts JS, etc..
	 * @param Boolean usuarioLogado => Diz ao método se existe um usuário Logado 
	 * @param Boolean exibe_botao_pagar => Diz ao método se exibe ou não o botão pagar 
	 * @param Decimal saldo => Contem o valor do Saldo do usuário 
	 * @param Decimal valor_pedido => Contem o valor do Pedido do usuário 
	 * @param Decimal saldo_final => Contem o valor do Saldo Final do usuário 
	 * @param String email => Contem o email do usuário 
	 * @param String captcha_valor => Contem o valor digitado referente ao captcha 
	 * @param Array $lista_pins => Contem a lista de PINs 
	 * @param Boolean box_carga_saldo => Diz ao método se é um box de carga no saldo ou de pagamento, onde: true = box de carga no saldo e false = box de pagamento 
	*/
	public function __construct($paramList,$lista_pins=null) { 		
		$this->jquery_core_include = $paramList['jquery_core_include'] ? $paramList['jquery_core_include'] : false;
		$this->url_ativacao = $this->generateUrl($paramList['url_resources']);
		$this->setUsuarioLogado($paramList['usuarioLogado']);
		$this->setSaldo($paramList['saldo']);
		$this->setValorPedido($paramList['valor_pedido']);
		$this->setSaldoFinal($paramList['saldo_final']);
		@$this->setCaptchaValor($paramList['captcha_valor']);
		if($this->getSaldoFinal() >= 0) {
			$this->setExibeBotaoPagar(true);
		}
		else {
			$this->setExibeBotaoPagar(false);
		}
		$this->setEmail($paramList['email']);
		$this->setListaPINs($lista_pins);
		@$this->setBoxCargaSaldo($paramList['box_carga_saldo'] ? true : false);
		
		/*
		if($this->getBoxCargaSaldo() && count($this->getListaPINs())>0)  {
			$this->setExibeBotaoPagar(false);
		}
		*/
	}
	
    function getUsuarioLogado(){
    	return $this->usuarioLogado;
    }
    function setUsuarioLogado($usuarioLogado){
    	$this->usuarioLogado = $usuarioLogado;
    }

    function getExibeBotaoPagar(){
    	return $this->exibe_botao_pagar;
    }
    function setExibeBotaoPagar($exibe_botao_pagar){
    	$this->exibe_botao_pagar = $exibe_botao_pagar;
    }

    function getListaPINs(){
    	return $this->lista_pins;
    }
    function setListaPINs($lista_pins){
    	$this->lista_pins = $lista_pins;
    }

    function getSaldo(){
    	return $this->saldo;
    }
    function setSaldo($saldo){
    	$this->saldo = $saldo;
    }

    function getValorPedido(){
    	return $this->valor_pedido;
    }
    function setValorPedido($valor_pedido){
    	$this->valor_pedido = $valor_pedido;
    }

    function getSaldoFinal(){
    	return $this->saldo_final;
    }
    function setSaldoFinal($saldo_final){
    	$this->saldo_final = $saldo_final;
    }

    function getEmail(){
    	return $this->email;
    }
    function setEmail($email){
    	$this->email = $email;
    }

    function getCaptchaValor(){
    	return $this->captcha_valor;
    }
    function setCaptchaValor($captcha_valor){
    	$this->captcha_valor = $captcha_valor;
    }

    function getBoxCargaSaldo(){
    	return $this->box_carga_saldo;
    }
    function setBoxCargaSaldo($box_carga_saldo){
    	$this->box_carga_saldo = $box_carga_saldo;
    }

	public function jsInclude() {
		
		$jsInclude = '';
		if($this->jquery_core_include) 
			$jsInclude .= "<script type='text/javascript' src='{$this->url_ativacao}js/jqueryui/js/jquery-1.6.2.min.js'></script>\n";		
		
		$jsInclude .=  "<script type='text/javascript' src='{$this->url_ativacao}js/jquery-ativacao-pin.js'></script>\n";
		
		return $jsInclude;
	}

	public function cssInclude() {
		$cssInclude = "<link type='text/css' href='{$this->url_ativacao}css/estilo_ativacao_pin.css' rel='stylesheet' />\n";
		return $cssInclude;
	}
	
	
	/**
	 * Gera a URL que será usada na página de Ativação de PINs EPP Cash
	 * 
	 * @param String $urlParam
	*/
	public function generateUrl($urlParam) {
		$server_url = "" . EPREPAG_URL . "";
                if(checkIP()) {
                    $server_url = $_SERVER['SERVER_NAME'];
                }
//		$urlAtivacao = "http".(($_SERVER['HTTPS']=="on")?"s":"")."://" . $server_url;
		$urlAtivacao = "";
                
		if($urlParam == '') {
			$urlTmp = explode("/", $_SERVER['PHP_SELF']);
			array_shift($urlTmp);
			array_pop($urlTmp);
				
			$urlAtivacao .= "/";
			foreach ($urlTmp as $urlPart) {
				$urlAtivacao .= $urlPart."/";
			}							
		} else {
			$urlAtivacao .= $urlParam;
		}
//		if($_SERVER['HTTPS']=="on") {
//			$urlAtivacao = str_replace("http:", "https:", $urlAtivacao);
//		}
		
		return $urlAtivacao;
	}
	
	
	/**
	 * Exibe o HTML que contém o Box de Ativação dos PINs EPP Cash
	 * 
	*/
	public function boxAtivacaoPin() {
                $server_url = "" . EPREPAG_URL . "";
                if(checkIP()) {
                    $server_url = $_SERVER['SERVER_NAME'];
                    }
				
		@$paginaAtivacaoPin .= $this->cssInclude();
		$paginaAtivacaoPin .= $this->jsInclude();						
//		$paginaAtivacaoPin .= "<div id='box-principal' name='box-principal'>";
        $disblock = (!b_isIntegracao() || $this->getUsuarioLogado()) ? "style='display:block;'" : "";
		$paginaAtivacaoPin .= "	<table width='578' cellpadding='0' cellspacing='0' align='center' border='0'>
								  	<tr>
										<td align='center'>
															
											<div class='box-ativacao-page'>				
												<div class='box-ativacao'>
													<img class='box-ativacao-logo-eppcash hidden-xs' src='/ativacao_pin/images/logo_eprepag_cash.png' />	
							
													<div class='box-sessao-usuario-login-sucesso hidden-xs' $disblock>";
		if(!$this->getUsuarioLogado()) {
			$paginaAtivacaoPin .= "													Login Efetuado com Sucesso!
													";
		}
		$paginaAtivacaoPin .= "						</div>			
													<div class='box-ativacao-pin'>		
														";
		if(!$this->getUsuarioLogado()) {
			$paginaAtivacaoPin .= $this->boxSessaoUsuario();
		}
		$paginaAtivacaoPin .= "									
													".$this->boxAdicionaPIN()."	
													</div>	
																	
													<div class='box-msg-utilizacao' id='box-msg-utilizacao' name='box-msg-utilizacao'></div>
												</div>
												
												<div class='box-resumo-pedido' id='box-resumo-pedido'>
													<div class='box-resumo-pedido-titulo'><label>";
		if(!$this->getExibeBotaoPagar()||!$this->getBoxCargaSaldo()) {
			$paginaAtivacaoPin .= "Resumo do Pedido";
		}
		else {
			$paginaAtivacaoPin .= "Resumo do Depósito";
		}
		$paginaAtivacaoPin .= "</label> (EPPCash)</div>
													
													".$this->boxSaldo()."	

													";
		if(!$this->getExibeBotaoPagar()||!$this->getBoxCargaSaldo()) {
			$paginaAtivacaoPin .= $this->boxPedido();
		}
		$paginaAtivacaoPin .= "

													<form name='formListaPIN' id='formListaPIN' method='post'>
														<input type='hidden' name='pagto' id='pagto'>
													</form>
								
													<table class='box-resumo-datagrid-pin' width='100%' border='0' cellspacing='0'>
													
													".$this->boxPIN_EPP()."
							
													</table>
													
													".$this->boxExibeResumoPINs()."

													";
		if(!$this->getExibeBotaoPagar()||!$this->getBoxCargaSaldo()) {
			$paginaAtivacaoPin .= $this->boxSaldoFinal();
		}
		elseif($this->getBoxCargaSaldo() && count($this->getListaPINs())>0)  {
				$paginaAtivacaoPin .= $this->boxSaldoFinal();
		}
		$paginaAtivacaoPin .= "
													
													".$this->boxBotaoPagar()."
													
													".$this->boxConfirmaPagamento()."

												</div> 			
												
												<div class='box-ativacao-pin-right'></div>
												
												<div class='box-ativacao-mensagem-rodape margin_top_xs_20' > 
												";
		if(!$this->getExibeBotaoPagar()) {
			$paginaAtivacaoPin .= "			<div class='margin_top_xs_20'> Você pode acrescentar até 5 PIN Cash.";
		}
		$paginaAtivacaoPin .= "<br>
											Veja <a href='/regra-de-uso-eppcash.php' class='link_azul' alt='Regras de Uso' title='Regras de Uso' style='cursor:pointer;cursor:help;' target='_blank'>aqui as regras de uso</a> do E-Prepag CASH </div>
												</div>
                                                
											</div>";
        
        if(b_isIntegracao()){
		
                        $paginaAtivacaoPin .= "<div class=\"mapa-epp\" style='    display: flex; align-items: flex-start;justify-content: flex-start; margin-top: 50px;'>
                                                <a href='/busca-pdv.php' target='_blank' title='busca de pontos de venda'>
                                                <img class='box-ativacao-logo-eppcash hidden-xs' src='/imagens/gamer/mapa_brasil_icone.gif' />
                                                <span style='display: inline-block; font-size: 13px !important; color: #258DC8; text-align: left; margin-top: 35px; width: 100px;'>
                                                    <strong>Encontre aqui</strong> <br>
                                                    Um ponto de venda de E-prepag Cash
                                                </span>
                                                </a>
                                            </div>";
        }
        
		$paginaAtivacaoPin .= "</td>
								  	 </tr>
								  </table>
								  <div id='box-script' name='box-script'></div>
								  <!--Div Box que exibe Regra de Uso PINs EPP -->
								  <div id='boxPopUpRegradeUso'></div>
								  ";
			$paginaAtivacaoPin .= $this->MontaJavaScript();
/*
	$paginaAtivacaoPin .= "
								</div>";
*/
			return $paginaAtivacaoPin;
	}//end public function boxAtivacaoPin


	/**
	 * Exibe o HTML que contém o box-sessao-usuario
	*/
	private function boxSessaoUsuario() {
                $server_url = "" . EPREPAG_URL . "";
                if(checkIP()) {
                    $server_url = $_SERVER['SERVER_NAME'];
                    }
			$paginaAtivacaoPin = "<div class='box-sessao-usuario text-left fontsize-pp' id='box-sessao-usuario'>
                                        <label>Utilizar Saldo</label>								
                                        <div class='box-sessao-usuario-msg'>Caso tenha saldo EPP Cash, faça aqui seu <a class='exibeLogin' href='javascript:exibeLogin();'>login</a> na E-prepag <img src='/ativacao_pin/images/botao_login.gif' onclick='javascript:exibeLogin();' style='cursor:pointer;cursor:hand;' alt='Login' title='Login'/></div>
                                        <div class='box-sessao-usuario-login text-left'>					
                                            <form name='formLogin' id='formLogin' method='post'>
                                            
                                                <span class='box-sessao-usuario-login-email' style='margin-bottom: 10px; position: relative; z-index: 999'><p>".$this->getEmail()."</p></span>
                                            
                                            <p>
                                                <input type='password' name='senhaLogin' id='senhaLogin'/>
                                                <input type='button' name='btnLogin' id='btnLogin' onclick='javascript:login_integracao();' style='height: 24px; cursor:hand;' alt='Executar Login' title='Executar Login' value='OK'/>
                                            </p>
                                            <p>
                                                <i><a href='#' onclick='esqueciSenha();' class='fontsize-pp'>Esqueci minha senha</a></i>
                                            </p>
                        </form>
                    </div>																	
                </div>";
                $paginaAtivacaoPin .= '
                                <script type="text/javascript" src="/js/modalwaitingfor.js"></script>
                                <script>
                                function esqueciSenha(){
                                    waitingDialog.show("Por favor, aguarde...",{dialogSize: "sm"});
                                    $.ajax({
                                         type: "POST",
                                         dataType: "JSON",
                                         url: "/game/ajax/dados-acesso.php",
                                         data: {type: "esqueciMinhaSenha", email: "'.$this->getEmail().'", senha: true },
                                         success: function(obj){

                                             waitingDialog.hide();

                                             if(obj.erro.length > 0){
                                                 var msgRetorno = "<div class=\"alert alert-danger\" role=\"alert\">"+obj.erro+"</div>";
                                                 $("#retEnvEmail").html(msgRetorno);
                                                 $("#modal-esqueci-senha").modal();
                                                 return false;
                                             }else if(obj.sucesso == true){
                                                var msgRetorno = "<div class=\"alert alert-success\" role=\"alert\">Um e-mail foi enviado para '.$this->getEmail().' com a sua senha.</div>";
                                                    $("#retEnvEmail").html(msgRetorno);
                                                    $("#modal-esqueci-senha").modal();
                                                 return false;
                                             }else{
                                                 var msgRetorno = "<div class=\"alert alert-danger\" role=\"alert\">Erro desconhecido, por favor, entre em contato com nosso suporte.</div>";
                                                 $("#retEnvEmail").html(msgRetorno);
                                                 $("#modal-esqueci-senha").modal();
                                                 return false;
                                             }
                                         },
                                         error: function(){
                                            waitingDialog.hide();
                                            var msgRetorno = "<div class=\"alert alert-danger\" role=\"alert\">Erro desconhecido, por favor, entre em contato com nosso suporte.</div>";
                                            $("#retEnvEmail").html(msgRetorno);
                                            $("#modal-esqueci-senha").modal();
                                            return false;
                                         }
                                     });

                                }
                                
                                $("#senhaLogin").keypress(function(e){
                                    var key = e.keyCode || e.which;
                                    if(key == "13"){
                                        login_integracao();
                                        return false;
                                }
                            });</script>';
                $paginaAtivacaoPin .=  '<div id="modal-esqueci-senha" class="modal fade" role="dialog">
                                        <div class="modal-dialog modal-lg">
                                            <!-- Modal content-->
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                  <h4 class="modal-title">Esqueci minha senha</h4>
                                                </div>
                                                <div class="modal-body espacamento text-center">
                                                    <div class="top10" id="retEnvEmail">

                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                  <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>';
			return $paginaAtivacaoPin;
	}//end public function boxSessaoUsuario()

	/**
	 * Exibe o Captcha no box-adicionar-pin-form-captcha
	*/
	private function boxCaptcha() {
                $server_url = "" . EPREPAG_URL . "";
                if(checkIP()) {
                    $server_url = $_SERVER['SERVER_NAME'];
                    }
			$paginaAtivacaoPin = "	
																	<div class='box-adicionar-pin-form-captcha-height'>
																		<div class='box-adicionar-pin-form-captcha'>
																			<label>Digite o código abaixo</label>																		
																			<input name='verificationCode' type='text' id='verificationCode' size='3' maxlength='3' />
																			<img src='/game/conta/C03/CaptchaImage3.php' width='50' height='25' alt='Verify Code' title='Verify Code' vspace='2' class='captcha-img'/>
																		</div>																											
																	</div>																											
																						
															";
			return $paginaAtivacaoPin;
	}//end private function boxCaptcha()

	/**
	 * Exibe o Adicionar PIN no box-adicionar-pin
	*/
	private function boxAdicionaPIN() {
            $server_url = "" . EPREPAG_URL . "";
                if(checkIP()) {
                    $server_url = $_SERVER['SERVER_NAME'];
                    }
			$paginaAtivacaoPin = "	
														<div class='box-adicionar-pin' id='box-adicionar-pin'>
								";
			$lista = $this->getListaPINs();
			if(!$this->getExibeBotaoPagar() || $this->getBoxCargaSaldo()) {
				if(count($lista)<5) {
					$paginaAtivacaoPin .= "						<label>Adicionar PIN</label>
										";
				}
				else {
					$paginaAtivacaoPin .= "						<span class='text'>Você pode acrescentar até 5 PIN Cash.</span>
										";
				}
				$paginaAtivacaoPin .= "						<div class='box-adicionar-pin-form'>
																<form name='formAddPIN' id='formAddPIN' method='post'>
																";
				if(count($lista)<5) {
					$paginaAtivacaoPin .= "						<input class='text' type='text' name='pin_number' id='pin_number' value=''>
										";
				}
				else {
					$paginaAtivacaoPin .= "						<input type='hidden' name='pin_number' id='pin_number' value=''>
										";
				}
				if(count($lista)==0) {
					$paginaAtivacaoPin .=  $this->boxCaptcha();
				}
				else {
					$paginaAtivacaoPin .= "							<input type='hidden' name='verificationCode' id='verificationCode' value='".$this->getCaptchaValor()."' />
																	";
				}
				$paginaAtivacaoPin .= "	
																</form>
															</div>
															";
				if(count($lista)<5) {
					$paginaAtivacaoPin .= "					<img id='btnAdicionar' src='/ativacao_pin/images/botao_adicionar.gif' onclick='javascript: clickAdicionar(0);' style='cursor:pointer;cursor:hand;     z-index: 999999;
    position: relative;' alt='Adicionar E-Prepag CASH' title='Adicionar E-Prepag CASH' />	
                                                                        <script>$('#verificationCode').keypress(function(e){
                                                                                            var key = e.keyCode || e.which;
                                                                                            if(key == '13'){
                                                                                                clickAdicionar(0);
                                                                                                return false;
                                                                                            }
                                                                        });
                                                                        $('#pin_number').keypress(function(e){
                                                                            var key = e.keyCode || e.which;
                                                                            if(key == '13'){
                                                                                clickAdicionar(0);
                                                                                return false;
                                                                            }
                                                                        });
                                                                        </script>
										";
				}
			}
			else {
			$paginaAtivacaoPin .= "							<span>Você já possui saldo suficiente para concluir o pagamento!</span>
									";
			}
			$paginaAtivacaoPin .= "						</div>

														";
			return $paginaAtivacaoPin;
	}//end private function boxAdicionaPIN()

	/**
	 * Exibe o Saldo no box-resumo-pedido-seu-saldo
	*/
	private function boxSaldo() {
			if($this->getUsuarioLogado()) {
				$paginaAtivacaoPin = "	
													<div class='box-resumo-pedido-seu-saldo'>Seu Saldo<span class='box-resumo-pedido-seu-saldo-valor'>".number_format(intval($this->getSaldo()*100),0,',','.')."</span></div>								

															";
			}
			else {
				$paginaAtivacaoPin = "	
													<div class='box-resumo-pedido-seu-saldo'><span class='box-resumo-pedido-seu-saldo-valor'></span></div>								

															";
			}
			return $paginaAtivacaoPin;
	}//end private function boxSaldo()

	/**
	 * Exibe o PIN no box-resumo-item-pin-label
	*/
	private function boxPIN_EPP() {
            $server_url = "" . EPREPAG_URL . "";
                if(checkIP()) {
                    $server_url = $_SERVER['SERVER_NAME'];
                    }
			$lista = $this->getListaPINs();
			$paginaAtivacaoPin = "";
                        if(!isset($lista)) $lista = array();
			foreach($lista as $key => $values)  {
				$paginaAtivacaoPin .= "	
														<tr class='box-resumo-item-pin bg_color_odd'>
															<td class='box-resumo-item-pin-label'>
																<img src='/ativacao_pin/images/botao_excluir.png' width='12' height='12' border='0' alt='Excluir PIN da Lista' title='Excluir PIN da Lista'  OnClick=\"javascript: if (confirm('Deseja realmente remover este PIN?\\nSe você remover poderá utiliza-lo posteriormente.')) { clickAdicionar('".substr($key,-4)."'); } \"  style='cursor:pointer;cursor:hand;'> PIN ****".substr($key,-4)." <span class='box-resumo-pedido-seu-saldo-valor'>".number_format(($values['VALOR']*100),0,',','.')."</span>
															</td>
															<td class='box-resumo-item-pin-excluir'></td>
														</tr>
														
															";
/************************ Incluir este trecho abaixo no codigo acima quando dispo nibilizar o bonus ************************************************************
														<tr class='box-resumo-item-pin bg_color_odd'>
															<td class='box-resumo-item-pin-bonus'>+ Bônus<img src='http<_?php echo (($_SERVER['HTTPS']=="on")?"s":"") ?_>://EPREPAG_URL/prepag2/commerce/ativacao_pin/images/ajuda_pin.gif' /></td>
															<td align='right'><span class='box-resumo-item-pin-bonus-valor'>".number_format(($values['BONUS']*100),0,',','.')."</span></td>
															<td class='box-resumo-item-pin-excluir'></td>
														</tr>						
************************************************************************************/

			}//end foreach
			return $paginaAtivacaoPin;
	}//end private function boxPIN_EPP()

	/**
	 * Exibe mensagem de confirmação do pagamento no box-resumo-pedido-msg-confirma-pagamento
	*/
	private function boxConfirmaPagamento() {
            $server_url = "" . EPREPAG_URL . "";
                if(checkIP()) {
                    $server_url = $_SERVER['SERVER_NAME'];
                    }
			$paginaAtivacaoPin = "	
													<div class='box-resumo-pedido-msg-confirma-pagamento'>";
			if($this->getExibeBotaoPagar() && !$this->getBoxCargaSaldo()) {
                $saldo = intval(number_format(intval($this->getSaldo()*100),0,',',''));
                $lista = $this->getListaPINs();
                
                if(count($lista)>0) {
                    $auxValor = 0;
                    foreach($lista as $key => $values)  {
                        $auxValor += $values['VALOR'];
                        $auxValor += $values['BONUS'];
                    }//end foreach
                    $auxValor = intval($auxValor*100);
                    $saldo+=intval($auxValor);
                }
                
                $pedido = number_format(intval($this->getValorPedido()*100),0,',','');
                $excedido = $pedido + ($pedido * PERCENT_SALDO_MAIOR_QUE_COMPRA); //calculando se o valor de saldo é 30% maior que o valor da compra para exibir mensagem de alerta
                if($saldo >= $excedido){                    
                    $paginaAtivacaoPin .= "<script>manipulaModal(1, '<p class=\"txt-preto\">Atenção: o valor do PIN ou Saldo é maior que o valor do pedido. A diferença ficará armazenada no saldo em sua conta na E-Prepag.</p>', 'Atenção');</script>";
                }
				
                $paginaAtivacaoPin .= "	 
														<label>Deseja Confirmar esta compra?</label>
														<img class='btn-nao' src='/ativacao_pin/images/botao_nao.gif' onclick='javascript:";
				if(!$this->getUsuarioLogado()) {
					$paginaAtivacaoPin .= "executaPagamentoRollBackNaoLogado";
				}
				else {
					$paginaAtivacaoPin .= "executaPagamentoRollBack";
				}
				$paginaAtivacaoPin .= "();' style='cursor:pointer;cursor:hand;' alt='Retorna para informar/excluir PINs CASH' title='Retorna para informar/excluir PINs CASH'/>
														<img class='btn-sim' src='/ativacao_pin/images/botao_sim.gif' onclick='javascript:usar_pin();' style='cursor:pointer;cursor:hand;' alt='Confirma o Pagamento da Compra' title='Confirma o Pagamento da Compra'/>";
			}
			elseif($this->getBoxCargaSaldo()) {
				$paginaAtivacaoPin .= "	
														<label>Deseja Confirmar o depósito?</label>
														<img class='btn-nao' src='/ativacao_pin/images/botao_nao.gif' onclick='javascript:";
				if(!$this->getUsuarioLogado()) {
					$paginaAtivacaoPin .= "executaPagamentoRollBackNaoLogado";
				}
				else {
					$paginaAtivacaoPin .= "executaPagamentoRollBack";
				}
				$paginaAtivacaoPin .= "();' style='cursor:pointer;cursor:hand;' alt='Retorna para informar/excluir PINs CASH' title='Retorna para informar/excluir PINs CASH'/>
														<img class='btn-sim' src='/ativacao_pin/images/botao_sim.gif' onclick='javascript:usar_pin();' style='cursor:pointer;cursor:hand;' alt='Confirma o Depósito' title='Confirma o Depósito'/>";
			}
			$paginaAtivacaoPin .= "
													</div>
													
															";
			return $paginaAtivacaoPin;
	}//end private function boxConfirmaPagamento()

	/**
	 * Exibe resumo de PINs no box-resumo-pins-utilizado
	*/
	private function boxExibeResumoPINs() {
			$lista = $this->getListaPINs();
			if(count($lista)>0) {
				$auxValor = 0;
				foreach($lista as $key => $values)  {
					$auxValor += $values['VALOR'];
					$auxValor += $values['BONUS'];
				}//end foreach
				$paginaAtivacaoPin = "	
														<div class='box-resumo-pins-utilizado'>PINs Adicionados<span class='box-resumo-pins-utilizado-valor'>".number_format(intval($auxValor*100),0,',','.')."</span></div>

																";
			}//end if(count($lista)==0)
			return isset($paginaAtivacaoPin) ? $paginaAtivacaoPin : null;
	}//end private function boxConfirmaPagamento()

	/**
	 * Exibe saldo final no box-resumo-pedido-saldo-final
	*/
	private function boxSaldoFinal() {
			$paginaAtivacaoPin = "	
													<div class='". ((intval($this->getSaldoFinal()*100)>0) ? "box-resumo-pedido-saldo-final" : "box-resumo-pedido-saldo-final-neg"). "'>Saldo Final<span class='box-resumo-pedido-saldo-final-valor'>".number_format(intval($this->getSaldoFinal()*100),0,',','.')."</span></div>
													
															";
			return $paginaAtivacaoPin;
	}//end private function boxSaldoFinal()

	/**
	 * Exibe o valor do Pedido no box-resumo-pedido-seu-total-label
	*/
	private function boxPedido() {
			$paginaAtivacaoPin = "	
													<div class='box-resumo-pedido-seu-total-label'>Pedido<span class='box-resumo-pedido-seu-total-valor'>".number_format(intval($this->getValorPedido()*100),0,',','.')."</span></div>
													
															";
			return $paginaAtivacaoPin;
	}//end private function boxPedido()

	/**
	 * Exibe o botão pagar no box-resumo-pedido-pagar
	*/
	private function boxBotaoPagar() {
            $server_url = "" . EPREPAG_URL . "";
                if(checkIP()) {
                    $server_url = $_SERVER['SERVER_NAME'];
                    }
			$paginaAtivacaoPin = "	
													<div class='box-resumo-pedido-pagar'>";
            $is_integra = (b_isIntegracao()) ? "  
            <div class='box-btn-pagar-height-integra'></div>
            " 
            : 
            "
            <div class='box-btn-pagar-height'></div>
            "; 
            
			$lista = $this->getListaPINs();
			
			if($this->getExibeBotaoPagar()) {
				if(!$this->getBoxCargaSaldo()) {
						$paginaAtivacaoPin .= "				
															<form name='formAddPIN' id='formAddPIN' method='post'>
															";
						if(count($lista)==0) {
							$paginaAtivacaoPin .= $this->boxCaptcha(). $is_integra;
						}
						else {
							$paginaAtivacaoPin .= "			<input type='hidden' name='verificationCode' id='verificationCode' value='".$this->getCaptchaValor()."' />
															<input type='hidden' name='pin_number' name='pin_number' value=''>
												  ";
						}
						$paginaAtivacaoPin .= "				</form>
															<img class='btn-pagar' src='/ativacao_pin/images/botao_pagar.png' onclick='javascript: if(clickPagar()) { ";
						if(!$this->getUsuarioLogado()) {
							$paginaAtivacaoPin .= "executaPagamentoNaoLogado";
						}
						else {
							$paginaAtivacaoPin .= "executaPagamento";
						}
						$paginaAtivacaoPin .= "(); }' style='cursor:pointer;cursor:hand;' alt='Executar o Pagamento da Compra' title='Executar o Pagamento da Compra'/>
																"; 
				}//end if(!$this->getBoxCargaSaldo())
				else {
					
					if(count($lista)>0) {
					
						$paginaAtivacaoPin .= "				<img class='btn-pagar' src='/ativacao_pin/images/concluir.gif' onclick='javascript: if(clickPagar()) { ";
						if(!$this->getUsuarioLogado()) {
							$paginaAtivacaoPin .= "executaPagamentoNaoLogado";
						}
						else {
							$paginaAtivacaoPin .= "executaPagamento";
						}
						$paginaAtivacaoPin .= "(); }' style='cursor:pointer;cursor:hand;' alt='Executar o Depósito no Saldo' title='Executar o Depósito no Saldo'/>
																"; 
					}//end if(count($lista)==0) 

				}//end else do if(!$this->getBoxCargaSaldo())
				//'".$GLOBALS['PINS_STORE_BOTOES']['pagar']."' atualizar as imagens no vetor
			}
			$paginaAtivacaoPin .= "
													</div> 							
													
															";
			return $paginaAtivacaoPin;
	}//end private function boxBotaoPagar()

	/**
	 * Monta javascript para execução
	*/
	private function MontaJavaScript() {
            $server_url = "" . EPREPAG_URL . "";
                if(checkIP()) {
                    $server_url = $_SERVER['SERVER_NAME'];
                    }
			$paginaAtivacaoPin = "	
						<script language='javascript' type='text/javascript'>";
			if($this->getUsuarioLogado()) {
			$paginaAtivacaoPin .= "	
						jaLogado();";
			}
			else {
			$paginaAtivacaoPin .= "	
						naoLogado();

						// Login Integração 
						function login_integracao(){
							//alert(document.formLogin.btnLogin.value);
							//alert(document.formLogin.senhaLogin.value);
								$.ajax({
									type:'POST',
									data: {\"login_integracao\": document.formLogin.btnLogin.value, \"senha\": document.formLogin.senhaLogin.value},
									url:'/ajax/gamer/ajax_login_integracao.php',
									beforeSend: function(){
										$('#box-sessao-usuario').html(\"<table><tr class='box-principal-login-class'><td><img src='/imagens/loading1.gif' border='0' title='Aguardando pagamento...'/></td></tr><tr class='box-principal-login-class'><td><font size='1'><b>Aguarde... Verificando.</b></font></td></tr></table>\"); 
									},
									success: function(txt){
										if (txt != 'ERRO') {
											$('#box-sessao-usuario').html(txt);
										} 
									},
									error: function(){
											$('#box-principal').html('');
										}
								});
						}
						";
			}//end else do if($this->getUsuarioLogado()) 

			//teste de transporte da variavel
			$trava	= unserialize($_SESSION['usuarioGames_ser']);
			if(isset($_POST['dr_par_general']) && !is_null($_POST['dr_par_general']) && ($trava->b_IsLogin_Wagner() || $trava->b_IsLogin_reinaldopshotmail())) {
				$paginaAtivacaoPin .= "alert('".$_POST['dr_par_general']."');";
			}
			
			if(!$this->getExibeBotaoPagar()||$this->getBoxCargaSaldo()) {

				$paginaAtivacaoPin .= "	
							// Validar o PIN 
							function verificar_pin(){
								var data = $('#formAddPIN').serialize() + '&' + $('#formListaPIN').serialize()";
				if(true) {
				$paginaAtivacaoPin .= " + '&dr_par_general=20'";
				}
				$paginaAtivacaoPin .= ";
								$(document).ready(function(){
									$.ajax({
										type: 'POST',
										url: '/ajax/gamer/";
				if(!$this->getBoxCargaSaldo()) {
					$paginaAtivacaoPin .="ajax_pin_pagamento";
				}
				else {
					$paginaAtivacaoPin .="ajax_pin_carga";
				}
				$paginaAtivacaoPin .=".php',
										dataType : 'html',
										data: data,
									 	beforeSend: function(){
											$('#box-adicionar-pin').html(\"<table><tr class='box-principal-class'><td><img src='/imagens/loading1.gif' border='0' title='Aguardando pagamento...'/></td><td><font size='1'> <b>Aguarde... Verificando.</b></font></td></tr></table>\"); 
										},
										success: function(html){
												$('#box-principal').html(html); 
										},
										error: function(x,e) {
											$('#box-principal').html(\"ERRO!!\");
										}
									});
								});
							}
								";
			} //end if(!$this->getExibeBotaoPagar()) 
			else {
				$paginaAtivacaoPin .= "	
							$(document).ready(function(){
								$.ajax({
									type: 'POST',
									url: '/ajax/gamer/ajax_pin_pagamento_data.php',";
				if(!is_null($_POST['dr_par_general'])) {
				$paginaAtivacaoPin .= "
									dataType : 'html',
									data: 'dr_par_general=".$_POST['dr_par_general']."',
									";
				}
				$paginaAtivacaoPin .= "
									success: function(txt){
										$('#box-script').html(txt);
									},
									error: function(){
										$('#box-script').html('ERRO ao carrega Ajax (234a)');
									}
								});
							});
															";
			}//end else do if(!$this->getExibeBotaoPagar())
			if($this->getBoxCargaSaldo()&& count($this->getListaPINs())>0) {
				$paginaAtivacaoPin .= "	
							$(document).ready(function(){
								$.ajax({
									type: 'POST',
									url: '/ajax/gamer/ajax_pin_carga_data.php',
									success: function(txt){
										$('#box-script').html(txt);
									},
									error: function(){
										$('#box-script').html('ERRO ao carrega Ajax (234b)');
									}
								});
							});
															";
			}
			$paginaAtivacaoPin .= "	
						</script>
								";
			return $paginaAtivacaoPin;
	}//end private function MontaJavaScript()


}//end class AtivacaoPinTemplate
?>