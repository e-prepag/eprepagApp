<script src="/js/valida.js"></script>
<?php

// Correcao bug sessao Internet Explorer 6,7,8
header('P3P: CP="CAO PSA OUR"');

require_once "../../../includes/constantes.php";
require_once DIR_CLASS . 'gamer/controller/HeaderController.class.php';
require_once $raiz_do_projeto."includes/complice/functions.php";

$controller = new HeaderController;
$controller->setHeader();

require_once DIR_INCS . "gamer/inc_Campeonatos.php";
require_once DIR_CLASS . "gamer/classLimite.php";

require_once DIR_INCS . "gamer/functions_endereco.php";

require_once DIR_INCS . "inc_register_globals.php";
require_once DIR_INCS . "config.MeiosPagamentos.php";


// Livrodjx

require_once "/www/includes/main.php";
require_once "/www/includes/gamer/main.php"; 
require_once "/www/class/util/Util.class.php";
include_once "/www/includes/complice/functions.php";
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/



//Definindo valor Default no caso do include estar conrrompido
if(!defined('PAGAMENTO_BRADESCO')) {
    //Definindo como ativado
    define('PAGAMENTO_BRADESCO',1);
}// end if
if(!defined('PAGAMENTO_BANCO_BRASIL')) {
    //Definindo como ativado
    define('PAGAMENTO_BANCO_BRASIL',1);
}// end if
if(!defined('PAGAMENTO_ITAU')) {
    //Definindo como ativado
    define('PAGAMENTO_ITAU',1);
}// end if
if(!defined('PAGAMENTO_BOLETO')) {
    //Definindo como ativado
    define('PAGAMENTO_BOLETO',1);
}// end if
if(!defined('PAGAMENTO_EPREPAG_CASH')) {
    //Definindo como ativado
    define('PAGAMENTO_EPREPAG_CASH',1);
}// end if
if(!defined('PAGAMENTO_CIELO')) {
    //Definindo como ativado
    define('PAGAMENTO_CIELO',1);
}// end if
if(!defined('PAGAMENTO_PIX')) {
    //Definindo como ativado
    define('PAGAMENTO_PIX',1);
}// end if

$btSubmit = $_POST['btSubmit'];

//Recupra carrinho do session
$carrinho = $GLOBALS['_SESSION']['carrinho'];

//Marcando que não é compra de integração
$GLOBALS['_SESSION']['is_integration'] = false; 

if($controller->usuario->getId() == 1286357){

   //ini_set('display_errors', 1);
  // ini_set('display_startup_errors', 1);
   //error_reporting(E_ALL);
  
  /* $garenaTrue = 0;
   $ogp_garena = 0;
   foreach($carrinho as $modelo => $val){
	   
	    $sql = "select ogpm_ogp_id from tb_operadora_games_produto_modelo where ogpm_id =" . $modelo;
		$ret = SQLexecuteQuery($sql);
		$result = pg_fetch_assoc($ret);
		if($result["ogpm_ogp_id"] == 454 || $result["ogpm_ogp_id"] == 433){
			$garenaTrue = 1;
			$ogp_garena = $result["ogpm_ogp_id"];
		}		
	   
   }
   
    require_once "/www/class/classIntegracaoGarena.php";
    $classGarena = new Garena(["validacao", $ogp_garena], $_POST["useridgarena"], "usuario"); 
   
    $auth = $classGarena->chamaGarena("GET"); // Para produção passar o segundo parametro 'producao'
	if($auth !== true){
		$_SESSION["erroGarena"] = $auth;
		header("location: https://www.e-prepag.com.br/game/pedido/passo-1.php");
		exit;
	}
   */
 
}


//Definindo valor máximo
if($controller->usuario->b_IsLogin_pagamento_free()) {
        $total_diario_const = $RISCO_GAMERS_FREE_TOTAL_DIARIO;
        $pagamentos_diario_const = $RISCO_GAMERS_FREE_PAGAMENTOS_DIARIO;
//	Gamers VIP- Pagamento Online = no max R$1000,00 por día por usuário (ver getVendasMoneyTotalDiarioOnline()) em até 20 vezes
} elseif($controller->usuario->b_IsLogin_pagamento_vip()) {
        $total_diario_const = $RISCO_GAMERS_VIP_TOTAL_DIARIO;
        $pagamentos_diario_const = $RISCO_GAMERS_VIP_PAGAMENTOS_DIARIO;
//	Gamers - Pagamento Online = no max R$450,00 por día por usuário (ver getVendasMoneyTotalDiarioOnline()) em até 10 vezes
} else {
        $total_diario_const = $RISCO_GAMERS_TOTAL_DIARIO;
        $pagamentos_diario_const = $RISCO_GAMERS_PAGAMENTOS_DIARIO;
	
		$prodmod = new ProdutoModelo;
		$dds = "";
		$exige = [];
		$idadeproduto = [];
		$exigeCPF = levantamentoPublisherObrigatorioCPF($dds);
			
		foreach($carrinho as $modeloId => $qtde){
		
		        if($modeloId !== $NO_HAVE) {
			      
						$publishers = $prodmod->verificarPublisher($modeloId, "");
						$linharesult = pg_fetch_array($publishers);
							
						if(in_array($linharesult["ogp_opr_codigo"], $exigeCPF)){
							  $exige[$linharesult["ogp_nome"]] = $linharesult["ogp_opr_codigo"];
						}
						
						if((int)$linharesult["ogp_idade_minima"] > 0){
						
						   $idadeproduto[] = $linharesult["ogp_idade_minima"];
						
						}
				}else{
				
				        foreach($qtde as $prod => $qtdeprod){
						
						   $publishers = $prodmod->verificarPublisher($prod,"NO HAVE");
						   $linharesult = pg_fetch_array($publishers);
						  
						    if(in_array($linharesult["ogp_opr_codigo"], $exigeCPF)){
						       $exige[$linharesult["ogp_nome"]] = $linharesult["ogp_opr_codigo"];
						    }
							if((int)$linharesult["ogp_idade_minima"] > 0){
						
						       $idadeproduto[] = $linharesult["ogp_idade_minima"];
						
						    }
						   
						}
				       
				}
					
					
		}
		
		if(	
			    $controller->usuario->ug_nome_da_mae != "" &&
				$controller->usuario->ug_sEndereco != "" &&
				$controller->usuario->ug_sNumero != "" &&
				$controller->usuario->ug_sBairro != "" &&
				$controller->usuario->ug_sCidade != "" &&
				$controller->usuario->ug_sCEP != "" &&
				$controller->usuario->ug_sEstado != "" &&
				$controller->usuario->ug_sCel != ""
		){
				
			$travaendereco = false;	
				
		}else{
			$travaendereco = true;
		}
		
		if(count($exige) == 0){
			$travapublisher = false;  
		}else{
			$travapublisher = true;
	  	}
		if($controller->usuario->ug_dDataNascimento != "" || $controller->usuario->ug_dDataNascimento != null){
			$datausu = str_replace(" 00:00:00", "", $controller->usuario->ug_dDataNascimento);
			$ano = substr($datausu, 6);
			$anonow = date("Y");
			$idade = (int)$anonow - (int)$ano;
		}else{
		    $travaidade = true;
			$idade = 0;
		}
		
		// VERICA IDADE USUARIO
		if(count($idadeproduto) != 0){
			$travaidade = false;
			for($num=0;$num < count($idadeproduto);$num++){
			    if((int)$idade < (int)$idadeproduto[$num]){
				 
				    $travaidade = true;
				    break;
				}
			} 
		}else{
			$travaidade = false;
		}
		/// VERIFICA LISTA BLACK E WHITE
		if($controller->usuario->ug_sCPF != "" || $controller->usuario->ug_sCPF != null){
			$cpf =  str_replace("-", "", str_replace(".", "", $controller->usuario->ug_sCPF));
			$travalistas = $controller->verifica_cpf_usuario($cpf);
		}else{
		    $cpfvazio = true;
		}
		
		
		/// valida as possibilidades
		if($travaidade === false){

			    if($travalistas["black"] === true || $cpfvazio === true){
			        $trintaemtrinta=true;
			    }else{
     
                    if(($travapublisher === true || $travapublisher === false) && $travaendereco === true){
						$trintaemtrinta=true;
					}					
				
				}		
			 
		}else{
			
			  $bloqueia = true;
              /// trava a compra total nao deixa o usuario prosseguir 		
		}
}

$naocompra = false;
$bloqueioPercentual = false;


/*if($cpfvazio) { ?>

	<script>
	manipulaModal(1,"<div style='background-color: #fff; padding: 30px; display: flex; flex-direction: column'><label for='data_nascimento'>Data de Nascimento</label><input type='date' name='data_nascimento' placeholder='00/00/0000' value='03-03-2002'/><br /><label for='data_nascimento'>CPF</label><input style='padding: 10px' type='text' name='cpf' placeholder='000.000.000-00'/><br/><button id='teste' style='width: 200px; margin: 0px auto; padding: 10px;'>Salvar</button></div>","Preencha as informações restantes abaixo <?php echo str_replace('00:00:00', '', $controller->usuario->ug_dDataNascimento); ?>"); 
			
			$('#teste').click(function() {
				console.log("aa");
			})
			$('#modal-load').on('hidden.bs.modal', function () {  
				window.alert("a");
			});
			</script>
<?php } ?>*/

if ($trintaemtrinta){ //flavio

        $qtde30em30 = getNVendasMoneySEG($controller->usuario->getId());
		$percentual = 80 / 100;
        $resultadoPercentual = ceil(30 * $percentual);
        
		//if($controller->usuario->getId() == 1286357){
			if($qtde30em30 >= $resultadoPercentual && $qtde30em30 <= 30){ //
				
			    $bloqueioPercentual = true;
			}		
		//}
		
        if ($qtde30em30>30){
				
			     $naocompra = true;
        }

}
	
if(!$carrinho || count($carrinho) == 0){
        $msg = "<p>Carrinho vazio.<br>Por favor, selecione algum produto.<p>";
}//end  if(!$carrinho || count($carrinho) == 0)          

$prod_camp = $_POST['prod_camp'];

//Recupera dados do session
$pagto = $_SESSION['pagamento.pagto'];
$pagto_ja_fiz = $_SESSION['pagamento.pagto_ja_fiz'];
$parcelas_REDECARD_MASTERCARD = $_SESSION['pagamento.parcelas.REDECARD_MASTERCARD'];
$parcelas_REDECARD_DINERS = $_SESSION['pagamento.parcelas.REDECARD_DINERS'];

 // Reset dados de Campeonato
$_SESSION['campeonato.prod_id'] = "";
unset($_SESSION['campeonato.prod_id']);

if($btSubmit || $iforma){
    
        //Variaveis do formulario
        if($_POST['iforma']) {
                $iforma = $_POST['iforma'];
        }
        $pagto = $_POST['pagto'];
        $pagto_ja_fiz = $_POST['pagto_ja_fiz'];
        $parcelas_REDECARD_MASTERCARD = $_POST['parcelas_REDECARD_MASTERCARD'];
        $parcelas_REDECARD_DINERS = $_POST['parcelas_REDECARD_DINERS'];

        //Validacao
        $msg = "";
        // Quando tem bloqueio Ongame pagto retorna vazio mas iforma retorna com o valor correto
        if($iforma && (!$pagto) ) {
                $pagto = $iforma;
        }

        //Valida opcao de pagamento
        if($msg == ""){
                if(!$pagto || $pagto == "" || (strlen($pagto)!=1)) $msg = "Selecione a forma de pagamento.";
        }

        //Validacao formas de pagamento		
        if($msg == ""){
                if(!in_array($pagto, $FORMAS_PAGAMENTO)) $msg = "Forma de pagamento inválida.";
        }
        //Adiciona dados no session
        if($msg == ""){

                $_SESSION['pagamento.pagto'] = $pagto;
                $_SESSION['pagamento.pagto_ja_fiz'] = $pagto_ja_fiz;
                $_SESSION['pagamento.parcelas.REDECARD_MASTERCARD'] = $parcelas_REDECARD_MASTERCARD;
                $_SESSION['pagamento.parcelas.REDECARD_DINERS'] = $parcelas_REDECARD_DINERS;

                $_SESSION['campeonato.prod_id'] = $prod_camp;
                
                if(($pagto == $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) && 
                   (trim($controller->usuario->getCEP()) == "" || 
                    trim($controller->usuario->getEndereco()) == "" ||  
                    trim($controller->usuario->getNumero()) == "" || 
                    trim($controller->usuario->getBairro()) == "" || 
                    trim($controller->usuario->getCidade()) == "" || 
                    trim($controller->usuario->getEstado()) == "") )
                {
                    $completar_endereco = true;
                }
                
                if($completar_endereco){
?>
                    <div class="container txt-azul-claro bg-branco">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-12 txt-azul-claro top10">
                                        <span class="glyphicon glyphicon-triangle-right graphycon-big" aria-hidden="true"></span><strong>Atualização Dados de Endereço</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 espacamento">
<?php
                            endereco_page_transf($completar_endereco);
                }
                else{
                    //redireciona
					
					/// garena nova integração
					if(isset($_POST["useridgarena"]) && !empty($_POST["useridgarena"])){
						$_SESSION["contaGarena"] = $_POST["useridgarena"];
					}
					
                    $strRedirect = "/game/pagamento/finaliza_venda.php";
                    redirect($strRedirect);
                }
        } 

}//end if($btSubmit || $iforma)

require_once DIR_WEB . 'game/includes/cabecalho.php';
?>

<script language="Javascript">
	function load_saibamais() {
		$('#boxPopUpSaibaMais').load("/game/instr_pcerto.php").show();
	}
	function fecha() {
		$('#boxPopUpSaibaMais').hide();
	}

	function load_saibamais_cielo() {
		$('#boxPopUpSaibaMaisCielo').load("/game/saiba_mais_cielo.php").show();
	}
	function fecha_cielo() {
		$('#boxPopUpSaibaMaisCielo').hide();
	}
        
<?php
     
	if($bloqueioPercentual == true){
		echo ' manipulaModal(1,"Seu <b>limite de compras</b> neste CPF consta em 80% para prosseguir com a compra de PINS, por gentileza efetue contato com o suporte@e-prepag.com.br para aumento do limite de vendas.","Atenção");'; 
		// $("#modal-load").on("hidden.bs.modal", function () { location.href="/game/pedido/passo-1.php" });
	}
    

	if($controller->logado) {
		
		if($controller->usuario->b_IsLogin_pagamento() && !$naocompra && !$bloqueia)  {

?>
	function save_shipping(iforma, id, sno) {
            
		document.form1.iforma.value = iforma;
		document.form1.idu.value = id;
		document.form1.prod_camp.value = <?php echo ((isset($prod_camp))?$prod_camp:0); ?>;
		document.form1.sno.value = sno;
		document.form1.btSubmit.value = "Continuar";
		for(var i=0; i < document.form1.pagto.length; i++){
			if(document.form1.pagto[i].value==iforma) {
				document.form1.pagto[i].checked = true;
			} else {
				document.form1.pagto[i].checked = false;
			}
		}
		document.form1.submit();
	}
        function check_deposito(checkbox){
                if(checkbox.checked == true){

                    save_shipping(<?php echo $FORMAS_PAGAMENTO['DEP_DOC_TRANSF'] ?>, <?php echo (($controller->usuario->getId()>0)?$controller->usuario->getId():"0") ?>, '<?php echo $controller->usuario->getNome() ?>');
                }
        }
<?php
        //end if($controller->usuario->b_IsLogin_pagamento())
		}elseif($naocompra){
?>
		            manipulaModal(1,"O <b>limite máximo</b> de compras por mês foi <b>excedido</b>","Erro"); 
                    $('#modal-load').on('hidden.bs.modal', function () { location.href='/game/pedido/passo-1.php' });
                    </script>
<?php	
		
		
         }elseif($cpfvazio){
			 
	?>	 
			
					
			manipulaModal(1,"<div style='background-color: #fff; padding: 30px; display: flex; flex-direction: column'><label for='data_nascimento'>Data de Nascimento</label><input type='date' name='data_nascimento' placeholder='00/00/0000' value=''/><br /><label for='data_nascimento'>CPF</label><input style='padding: 10px' type='text' name='cpf' placeholder='000.000.000-00'/><br/><button id='teste' style='width: 200px; margin: 0px auto; padding: 10px;'>Salvar</button></div>","Preencha as informações restantes abaixo"); 
			
			$('#teste').click(function() {
				console.log("aa");
			})
			$('#modal-load').on('hidden.bs.modal', function () {  
				window.alert("a");
			});
			</script>
				
	<?php				
		 }else {
?>
                    manipulaModal(1,"Usuários Bloqueados Momentaneamente para Compra","Erro"); 
                    $('#modal-load').on('hidden.bs.modal', function () { location.href='/game/' });
                    </script>
<?php
                    die();
                }
	} //end if($controller->usuario->logado) 
        else $msg .= "Usuário com TimeOut.\nLogar novamente.";
?>
</script>
<!--Div Box que exibe Saiba Mais PINs EPP -->
<div id="boxPopUpSaibaMais"></div>
<!--Div Box que exibe Saiba Mais CIELO -->
<div id="boxPopUpSaibaMaisCielo"></div>
<div class="container txt-azul-claro bg-branco">
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-12 txt-azul-claro top10">
                    <span class="glyphicon glyphicon-triangle-right graphycon-big" aria-hidden="true"></span><strong>Escolha forma de pagamento</strong>
                </div>
            </div>
<?php
            if($msg != ""){
?>			
            <div class="row">
                <div class="col-md-12 espacamento text-center txt-vermelho">
                    <?php echo $msg;?>
                </div>
                <div class="col-md-3 col-md-offset-7 espacamento">
                    <a href="<?php echo (isset($link) && $link != "") ? $link : "/game/";?>" class="btn btn-primary">Voltar</a>
                </div>
            </div>
            <script>
                manipulaModal(1,"<?php echo $msg; ?>","Erro");
            </script>
<?php
            } //end if($msg != "")
            else {
?>
            <div class="row txt-cinza espacamento top20">
                <div class="col-md-12 bg-cinza-claro">
<?php
                $total_geral_pin_epp_cash = 0;
                $total_geral = 0;
                
                foreach ($carrinho as $modeloId => $qtde){
                    if($modeloId !== $NO_HAVE) {
                        $qtde = intval($qtde);
                        $rs = null;
                        $filtro['ogpm_ativo'] = 1;
                        $filtro['ogpm_id'] = $modeloId;
                        $filtro['com_produto'] = true;
                        // Debug reinaldops
                        if(isset($GLOBALS['_SESSION']['usuarioGames_ser'])) {
                            if($controller->usuario->b_IsLogin_pagamento_usa_produto_treinamento()) {
                                $filtro['show_treinamento'] = 1;
                            }
                        }
                        $instProdutoModelo = new ProdutoModelo;
                        $ret = $instProdutoModelo->obter($filtro, null, $rs);
                        if($rs && pg_num_rows($rs) != 0){
                            $rs_row = pg_fetch_array($rs);
                            $total_geral += $rs_row['ogpm_valor'] * $qtde;
                            $total_geral_pin_epp_cash  += $rs_row['ogpm_valor_eppcash'] * $qtde;
                            $instProduto = new Produto;
                            $iof = $instProduto->buscaIOF($modeloId) ? "Incluso" : "";
    ?>
                        <div class="col-xs-12 col-sm-12 bg-branco hidden-lg hidden-md espacamento borda-fina">
                            <div class="row">
                                <div class="col-xs-3 col-sm-5">
                                    Produto:
                                </div>
                                <div class="col-xs-9 col-sm-7">
                                    <strong><?php echo $rs_row['ogp_nome']?>
                                    <?php if($rs_row['ogpm_nome']!=""){ ?> - <?php echo $rs_row['ogpm_nome']?><?php }?></strong>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-5 col-sm-5">
                                    IOF.:
                                </div>
                                <div class="col-xs-7 col-sm-7">
                                    <?php echo $iof;?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-5 col-sm-5">
                                    Valor unit.:
                                </div>
                                <div class="col-xs-7 col-sm-7">
                                   R$ <?php echo number_format($rs_row['ogpm_valor'], 2, ',', '.')?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-5 col-sm-5">
                                    Qtde.:
                                </div>
                                <div class="col-xs-7 col-sm-7">
                                    <?php echo htmlspecialchars($qtde, ENT_QUOTES);?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-5 col-sm-5">
                                    Preço Total:
                                </div>
                                <div class="col-xs-7 col-sm-7">
                                   R$ <?php	echo number_format($rs_row['ogpm_valor']*$qtde, 2, ',', '.');?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-5 col-sm-5 nowrap">
                                    Preço em:
                                </div>
                                <div class="col-xs-7 col-sm-7">
                                   <?php echo get_info_EPPCash_NO_Table($rs_row['ogpm_valor_eppcash']*$qtde);?>
                                </div>
                            </div>
                        </div>
<?php
                        }
                    }//end if($modeloId !== $NO_HAVE) 
                    else {
                        foreach ($qtde as $codeProd => $vetor_valor) {
                            foreach ($vetor_valor as $valor => $quantidade) {
                                    $rs = null;
                                    $filtro['ogp_ativo'] = 1;
                                    $filtro['ogp_id'] = $codeProd;
                                    $filtro['ogp_mostra_integracao_com_loja'] = '1';
                                    $filtro['opr'] = 1;
                                    $ret = (new Produto)->obterMelhorado($filtro, null, $rs);
                                    if(!$rs || pg_num_rows($rs) == 0) $msg = "Nenhum produto disponível no momento.";
                                    else $rs_row = pg_fetch_array($rs);
                                    $total_geral += $valor * $quantidade;
                                    $total_geral_pin_epp_cash  += (new ConversionPINsEPP)->get_ValorEPPCash('E',$valor)*$quantidade;
?>
                        <div class="col-xs-12 col-sm-12 bg-branco hidden-lg hidden-md espacamento borda-fina">
                            <div class="row">
                                <div class="col-xs-3 col-sm-5">
                                    Produto:
                                </div>
                                <div class="col-xs-9 col-sm-7">
                                    <strong><?php echo $rs_row['ogp_nome']?></strong>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-5 col-sm-5">
                                    IOF.:
                                </div>
                                <div class="col-xs-7 col-sm-7">
                                    <?php echo $rs_row['ogp_iof'] ? "Incluso" : "";?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-5 col-sm-5">
                                    Valor unit.:
                                </div>
                                <div class="col-xs-7 col-sm-7">
                                   R$ <?php echo number_format($valor, 2, ',', '.')?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-5 col-sm-5">
                                    Qtde.:
                                </div>
                                <div class="col-xs-7 col-sm-7">
                                    <?php echo htmlspecialchars($quantidade, ENT_QUOTES);?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-5 col-sm-5">
                                    Preço Total:
                                </div>
                                <div class="col-xs-7 col-sm-7">
                                   R$ <?php	echo number_format($valor*$quantidade, 2, ',', '.');?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-5 col-sm-5 nowrap">
                                    Preço em:
                                </div>
                                <div class="col-xs-7 col-sm-7">
                                   <?php echo get_info_EPPCash_NO_Table((new ConversionPINsEPP)->get_ValorEPPCash('E',$valor)*$quantidade);?>
                                </div>
                            </div>
                        </div>
<?php
                            }//end foreach 
                        }//end foreach
                    }//end else do if($modeloId !== $NO_HAVE)
                }
                
                if($total_geral>$total_diario_const) {
                    $msg = "O valor m\u00E1ximo por Pedido \u00E9 de R$".number_format($total_diario_const,2,",",".");
                    echo "<script>manipulaModal(1,'" . str_replace("\n", "\\n", $msg) . "','Erro') ; $('#modal-load').on('hidden.bs.modal', function () { location.href='/game/pedido/passo-1.php' });</script>";
                    
                    die;
                }
?>
                    <div class="col-xs-12 col-sm-12 hidden-lg hidden-md bg-cinza-claro espacamento borda-fina">
                        <div class="row">
                            <div class="col-xs-5 col-sm-5">
                                <strong>Total:</strong>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-5 col-sm-5">
                                Preço 
                            </div>
                            <div class="col-xs-7 col-sm-7">
                                R$ <?php echo number_format($total_geral, 2, ',', '.') ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-5 col-sm-5 nowrap">
                                Preço em
                            </div>
                            <div class="col-xs-7 col-sm-7">
                               <?php echo get_info_EPPCash_NO_Table($total_geral_pin_epp_cash); ?>
                            </div>
                        </div>
                    </div>
                    <table class="table bg-branco hidden-sm hidden-xs txt-preto">
                    <thead>
                        <tr class="bg-cinza-claro text-center">
                            <th class="txt-left">Produto</th>
                            <th>I.O.F.</th>
                            <th>Valor unitário</th>
                            <th>Qtde.</th>
                            <th>Total</th>
                            <th>Preço em</th>
                        </tr>
                    </thead>
                    <tbody>
            
<?php
                $total_geral_pin_epp_cash = 0;
                $total_geral = 0;
                
                foreach ($carrinho as $modeloId => $qtde){
                    if($modeloId !== $NO_HAVE) {
                        $qtde = intval($qtde);
                        $rs = null;
                        $filtro['ogpm_ativo'] = 1;
                        $filtro['ogpm_id'] = $modeloId;
                        $filtro['com_produto'] = true;
                        // Debug reinaldops
                        if(isset($GLOBALS['_SESSION']['usuarioGames_ser'])) {
                            if($controller->usuario->b_IsLogin_pagamento_usa_produto_treinamento()) {
                                $filtro['show_treinamento'] = 1;
                            }
                        }
                        $instProdutoModelo = new ProdutoModelo;
                        $ret = $instProdutoModelo->obter($filtro, null, $rs);
                        if($rs && pg_num_rows($rs) != 0){
                            $rs_row = pg_fetch_array($rs);
                            $total_geral += $rs_row['ogpm_valor'] * $qtde;
                            $total_geral_pin_epp_cash  += $rs_row['ogpm_valor_eppcash'] * $qtde;
                            $instProduto = new Produto;
                            $iof = $instProduto->buscaIOF($modeloId) ? "Incluso" : "";
    ?>

                              <tr class="text-center trListagem">
                                <td class="text-left">
                                    <input name="produtos[]" id="produtos" type="hidden" value="<?php echo $rs_row['ogpm_id'];?>" />
                                    <input name="v<?php echo $rs_row['ogpm_id'];?>" id="v<?php echo $rs_row['ogpm_id'];?>" type="hidden" value="<?php echo $rs_row['ogpm_valor'];?>" />
                                    <input name="e<?php echo $rs_row['ogpm_id'];?>" id="e<?php echo $rs_row['ogpm_id'];?>" type="hidden" value="<?php echo $rs_row['ogpm_valor_eppcash'];?>" />
                                    <input name="q<?php echo $rs_row['ogpm_id'];?>" id="q<?php echo $rs_row['ogpm_id'];?>" type="hidden" value="<?php echo $qtde;?>" />
                                    <?php echo $rs_row['ogp_nome']?>
                                    <?php if($rs_row['ogpm_nome']!=""){ ?> - <?php echo $rs_row['ogpm_nome']?><?php }?>
                                </td>
                                <td><?php echo $iof;?></td>
                                <td>R$ <?php echo number_format($rs_row['ogpm_valor'], 2, ',', '.')?></td>
                                <td><?php echo htmlspecialchars($qtde, ENT_QUOTES);?></td>
                                <td>R$ <?php	echo number_format($rs_row['ogpm_valor']*$qtde, 2, ',', '.');?></td>
                                <td><?php echo get_info_EPPCash_NO_Table($rs_row['ogpm_valor_eppcash']*$qtde);?></td>
                              </tr>

<?php
                        }
                    }//end if($modeloId !== $NO_HAVE)
                    else {
                        foreach ($qtde as $codeProd => $vetor_valor) {
                            foreach ($vetor_valor as $valor => $quantidade) {
                                    $total_geral += $valor * $quantidade;
                                    $total_geral_pin_epp_cash  += (new ConversionPINsEPP)->get_ValorEPPCash('E',$valor)*$quantidade;
                                    $rs = null;
                                    $filtro['ogp_ativo'] = 1;
                                    $filtro['ogp_id'] = $codeProd;
                                    $filtro['ogp_mostra_integracao_com_loja'] = '1';
                                    $filtro['opr'] = 1;
                                    $ret = (new Produto)->obtermelhorado($filtro, null, $rs);
                                    if(!$rs || pg_num_rows($rs) == 0) $msg = "Nenhum produto disponível no momento.";
                                    else $rs_row = pg_fetch_array($rs);
?>

                              <tr class="text-center trListagem">
                                <td class="text-left"><?php echo $rs_row['ogp_nome']?></td>
                                <td><?php echo $iof;?></td>
                                <td>R$ <?php echo number_format($valor, 2, ',', '.')?></td>
                                <td><?php echo htmlspecialchars($quantidade, ENT_QUOTES);?></td>
                                <td>R$ <?php echo number_format($valor*$quantidade, 2, ',', '.');?></td>
                                <td><?php echo get_info_EPPCash_NO_Table((new ConversionPINsEPP)->get_ValorEPPCash('E',$valor)*$quantidade);?></td>
                              </tr>

<?php
                            }//end foreach 
                        }//end foreach
                    }//end else do if($modeloId !== $NO_HAVE)
                }
                
                if($total_geral>$total_diario_const) {
                    $msg = "O valor m\u00E1ximo por Pedido \u00E9 de R$".number_format($total_diario_const,2,",",".");
                    echo "<script>manipulaModal(1,'" . str_replace("\n", "\\n", $msg) . "','Erro') ; $('#modal-load').on('hidden.bs.modal', function () { location.href='/game/pedido/passo-1.php' });</script>";
                    
                    die;
                }
?>
                        <tr class="bg-cinza-claro text-center">
                            <td colspan="3">&nbsp;</td>
                            <td><strong>Total:</strong></td>
                            <td><?php echo number_format($total_geral, 2, ',', '.') ?></td>
                            <td><?php echo get_info_EPPCash_NO_Table($total_geral_pin_epp_cash); ?></td>
                        </tr>
                    </tbody>
                    </table>
                </div>
            </div>
            <form id="form1" name="form1" method="post">
            <input type="hidden" name="prod_camp" id="prod_camp" value="<?php echo ((isset($prod_camp))?$prod_camp:0); ?>">
            <input type="hidden" name="tipo" value="venda_gamer">
            <input type="hidden" name="btSubmit" value="">
<?php
            // REGRAS DE DISPONIBILIZAÇÂO DE MEIOS DE PAGAMENTOS
            $b_bloqueia_Ongame = false;
            
            // Se carrinho contem algum produto da Ongame -> bloqueia
            $b_bloqueia_Ongame = get_carrinho_com_produtos_ongame();

            //Recupera carrinho do session
            if($controller->usuario->b_IsLogin_pagamento()) {

                    // Obtem o valor total deste pedido
                    $libera_pagamento = array(
                                    'BancodoBrasil'         => true,
                                    'BancoItau'             => true,
                                    'Bradesco'              => true,
                                    'Hipay'                 => true,
                                    'Paypal'                => true,
                                    'Boleto'                => true,
                                    'Deposito'              => true,
                                    'EppCash'               => true,
                                    'Cielo'                 => true,
                                    'Cielo_Visa_DEB'        => true,
                                    'Cielo_Visa_CRED'       => true,
                                    'Cielo_Master_DEB'      => true,
                                    'Cielo_Master_CRED'     => true,
                                    'Cielo_Elo_DEB'         => true,
                                    'Cielo_Elo_CRED'        => true,
                                    'Cielo_Diners_CRED'     => true,
                                    'Cielo_Discover_CRED'   => true,
                                    'Pix'                   => true
                                    );
                    
                    $total_carrinho = mostraCarrinho_pag(false, 1, $libera_pagamento);
                    
                    //Variavel utilizada para isenção de taxas
                    $valor_tmp = $total_geral;

                    // Testa que usuário comprou no máximo 10 vezes nas últimas 24 horas
                    $qtde_last_dayOK = getNVendasMoney($controller->usuario->getId());

                    // Calcula o total nas últimas 24 horas para pagamentos Online 
                    $total_diario = getVendasMoneyTotalDiarioOnline($controller->usuario->getId());

                    if($controller->usuario->b_IsLogin_pagamento_free()) {
                            $total_diario_const = $RISCO_GAMERS_FREE_TOTAL_DIARIO;
                            $pagamentos_diario_const = $RISCO_GAMERS_FREE_PAGAMENTOS_DIARIO;
                    } elseif($controller->usuario->b_IsLogin_pagamento_vip()) {
                            $total_diario_const = $RISCO_GAMERS_VIP_TOTAL_DIARIO;
                            $pagamentos_diario_const = $RISCO_GAMERS_VIP_PAGAMENTOS_DIARIO;
                    } else {
                            $total_diario_const = $RISCO_GAMERS_TOTAL_DIARIO;
                            $pagamentos_diario_const = $RISCO_GAMERS_PAGAMENTOS_DIARIO;
                    }

                    $b_TentativasDiariasOK = ($qtde_last_dayOK<=$pagamentos_diario_const);
                    $b_LimiteDiarioOK = (($total_carrinho+$total_diario)<=$total_diario_const);
                    $b_ValorBoletoOK = ($total_carrinho<=$RISCO_GAMERS_BOLETOS_TOTAL_DIARIO);
                    $b_ValorDepositoOK = ($total_carrinho<=$RISCO_GAMERS_DEPOSITOS_TOTAL_DIARIO);

                    // Libera pagamento Online Banco do Brasil
                    $b_libera_BancodoBrasil = $b_LimiteDiarioOK && $b_TentativasDiariasOK && $libera_pagamento['BancodoBrasil'];// && $controller->usuario->b_IsLogin_pagamento_bancodobrasil();

                    // Libera pagamento Online Banco Itaú
                    $b_libera_BancoItau = $b_LimiteDiarioOK && $b_TentativasDiariasOK && $controller->usuario->b_IsLogin_pagamento_bancoitau() && $libera_pagamento['BancoItau'];

                    // Libera Bradesco apenas se limite diario não ultrapassado //produtos (Habbo e GPotato) e tem até 5 compras nas últimas 24 horas
                    $b_libera_Bradesco = $b_LimiteDiarioOK && $b_TentativasDiariasOK && $libera_pagamento['Bradesco'];	//$b_IsProdutoOK && 

                    // Libera pagamento Online Hipay
                    $b_libera_Hipay = $b_LimiteDiarioOK && $b_TentativasDiariasOK && $controller->usuario->b_IsLogin_pagamento_hipay() && $libera_pagamento['Hipay'];

                    // Libera pagamento Online Paypal
                    $b_libera_Paypal = $b_LimiteDiarioOK && $b_TentativasDiariasOK && $controller->usuario->b_IsLogin_pagamento_paypal() && $libera_pagamento['Paypal'];

                    // Libera Boleto apenas se o valor da venda não ultrapassa o limite por venda
                    $b_libera_Boleto = $b_ValorBoletoOK && $libera_pagamento['Boleto'];	

                    // Libera Depósito apenas se o valor da venda não ultrapassa o limite por venda
                    $b_libera_Deposito =  false; //$b_ValorDepositoOK && $libera_pagamento['Deposito'];	

                    // Libera Epp CASH
                    $b_libera_EppCash = $libera_pagamento['EppCash'];	

                    // Libera PIX
                    $b_libera_Pix = $b_LimiteDiarioOK && $b_TentativasDiariasOK && $libera_pagamento["Pix"];
                    
                    $msg_bloqueia_Bradesco = (!$b_libera_Bradesco) ? ((!$b_LimiteDiarioOK)?"<p class='txt-azul fontsize-pp'>Sua compra de ".number_format($total_carrinho, 2, ',', '.')." ultrapassa o limite diário de compras on-line.</p>":((!$b_TentativasDiariasOK)?"<p class='txt-azul fontsize-pp'>Número de pagamentos online (".$qtde_last_dayOK.") ultrapassa o limite diário.</p>":"")) :"";

                    $msg_bloqueia_BancodoBrasil = (!$b_libera_BancodoBrasil)?((!$b_LimiteDiarioOK)?"<p class='txt-azul fontsize-pp'>Sua compra de ".number_format($total_carrinho, 2, ',', '.')." ultrapassa o limite diário de compras on-line.</p>":((!$b_TentativasDiariasOK)?"<p class='txt-azul fontsize-pp'>Número de pagamentos online (".$qtde_last_dayOK.") ultrapassa o limite diário.</p>":"")):"";

                    $msg_bloqueia_BancoItau = (!$b_libera_BancoItau)?((!$b_LimiteDiarioOK)?"<p class='txt-azul fontsize-pp'>Sua compra de ".number_format($total_carrinho, 2, ',', '.')." ultrapassa o limite diário de compras on-line.</p>":((!$b_TentativasDiariasOK)?"<p class='txt-azul fontsize-pp'>Número de pagamentos online (".$qtde_last_dayOK.") ultrapassa o limite diário.</p>":"")):"";

                    $msg_bloqueia_Hipay = (!$b_libera_Hipay)?((!$b_LimiteDiarioOK)?"<p class='txt-azul fontsize-pp'>Sua compra de ".number_format($total_carrinho, 2, ',', '.')." ultrapassa o limite diário de compras on-line.</p>":((!$b_TentativasDiariasOK)?"<p class='txt-azul fontsize-pp'>Número de pagamentos online (".$qtde_last_dayOK.") ultrapassa o limite diário.</p>":"")):"";

                    $msg_bloqueia_Paypal = (!$b_libera_Paypal)?((!$b_LimiteDiarioOK)?"<p class='txt-azul fontsize-pp'>Sua compra de ".number_format($total_carrinho, 2, ',', '.')." ultrapassa o limite diário de compras on-line.</p>":((!$b_TentativasDiariasOK)?"<p class='txt-azul fontsize-pp'>Número de pagamentos online (".$qtde_last_dayOK.") ultrapassa o limite diário.</p>":"")):"";

                    $msg_bloqueia_Boleto = (!$b_libera_Boleto)? ((!$b_LimiteDiarioOK)?"<p class='txt-azul fontsize-pp'>Sua compra de ".number_format($total_carrinho, 2, ',', '.')." ultrapassa o limite de compras por boleto.</p>":((!$b_TentativasDiariasOK)?"<p class='txt-azul fontsize-pp'>Número de pagamentos (".$qtde_last_dayOK.") ultrapassa o limite diário.</p>":"")):"";

                    $msg_bloqueia_Deposito = (!$b_libera_Deposito)? "<p class='txt-azul fontsize-pp'>Sua compra de ".number_format($total_carrinho, 2, ',', '.')." ultrapassa o limite de compras por depósito.</p>":"";
                    
                    $msg_bloqueia_Pix = (!$b_libera_Pix) ? "<p class='txt-azul fontsize-pp'>Sua compra de ".number_format($total_carrinho, 2, ',', '.')." ultrapassa o limite de compras por pix.</p>":"";
                    
                    // Começa Gestão de Risco CIELO
                    $carrinho_tmp = $GLOBALS['_SESSION']['carrinho'];
                    $params = array();
                    // $pagto = 'G' (Visa Crédito), quando passa aqi aonda não foi escolhido o $pagto -> uma forma para todos 
                    $limite = new Limite('G', $controller->usuario->getId(), $total_carrinho, $carrinho_tmp, "week"); 
                    $mensagem = "";
                    $ret_regras_cielo = $limite->aplicaRegrasCieloNovas($mensagem, $params);
                    if($ret_regras_cielo && $libera_pagamento['Cielo']) {
                            $b_libera_Cielo = true;	
                    } else {
                            $b_libera_Cielo = false;
                            gravaLog_BloqueioPagtoOnline("Pagamento Cielo Bloqueado\n    pagto: $pagto, usuarioGames->getId(): ".$controller->usuario->getId().", total_carrinho: $total_carrinho, qtde_last_dayOK: ".$qtde_last_dayOK. ", total_diario: ".$total_diario."\n    ".$mensagem);
                    }
                    $msg_bloqueia_Cielo = (!$b_libera_Cielo)? "<p class='txt-azul fontsize-pp'>Pagamento indisponível</p>":"";
                    // Termina Gestão de Risco CIELO


                    if(!$b_TentativasDiariasOK || !$b_LimiteDiarioOK ) {	
                            $msg_block = "Pagamento Online BLOQUEADO ******  ";

                            $smsg_bloqueio = 
                                    "	Usuário: ID: ".$controller->usuario->getId().", Nome: ".$controller->usuario->getNome().", Email: ".$controller->usuario->getEmail().",\n".
                                    "	Regras bloqueio: b_TentativasDiariasOK: ".(($b_TentativasDiariasOK)?"SIM":"não")." (n: $qtde_last_dayOK), ".
                                            "b_LimiteDiarioOK: ".(($b_LimiteDiarioOK)?"SIM":"não").", ".
                                            "b_libera_Cielo: ".(($b_libera_Cielo)?"SIM":"não")." \n".
                                    "	total_carrinho: ".number_format($total_carrinho, 2, ',', '.').", total_diario: ".number_format($total_diario, 2, ',', '.')."\n".
                                    "	solicitado: ".number_format(($total_carrinho+$total_diario), 2, ',', '.')." de ".number_format($total_diario_const, 2, ',', '.')."\n".
                                    "";
                            if(($total_carrinho+$total_diario)<=(2*$total_diario_const)) {
                                    $smsg_bloqueio .= "	Safe (<=2*LIMITE_MAX)\n";
                            } else {
                                    $smsg_bloqueio .= "	NotSafe (>2*LIMITE_MAX)\n";
                            }
                            gravaLog_PagtoOnlineUsuariosBloqueadosParaVIP($smsg_bloqueio);

                            PagtoOnlineUsuariosBloqueadosParaVIP($pagto, $controller->usuario->getId(), $total_carrinho, $total_diario, $total_diario_const, $qtde_last_dayOK, $pagamentos_diario_const);

                    } else {
                            $msg_block = "Pagamento Online PERMITIDO ++++++  ";
                    }
                    $mensagem = "=====================================================================================\n".
                            "$msg_block (".date("Y-m-d H:i:s").")\n".
                            "  Usuário: ID: ".$controller->usuario->getId().", Nome: ".$controller->usuario->getNome().", Email: ".$controller->usuario->getEmail().",\n".
                            "  qtde_last_dayOK: ".$qtde_last_dayOK."\n".
                            "  total_diario: ".number_format($total_diario, 2, ',', '.')."\n".
                            "  total_diario_const: ".number_format($total_diario_const, 2, ',', '.')."\n".
                            "  total_carrinho+total_diario: ".number_format(($total_carrinho+$total_diario), 2, ',', '.')."\n".
                            "  b_TentativasDiariasOK: ".($b_TentativasDiariasOK?"OK":"nope")."\n".
                            "  b_LimiteDiarioOK: ".($b_LimiteDiarioOK?"OK":"nope")."\n".
                            "  \n".
                            "  b_libera_BancodoBrasil: ".($b_libera_BancodoBrasil?"OK":"nope")."\n".
                            "  b_libera_Bradesco: ".($b_libera_Bradesco?"OK":"nope")."\n".
                            "  b_libera_Cielo: ".($b_libera_cielo?"OK":"nope")."\n".
                            "  b_libera_PayPal: ".($b_libera_Paypal?"OK":"nope")."\n".
                            "  b_libera_Hipay: ".($b_libera_Hipay?"OK":"nope")."\n".
                            "\n";
                    gravaLog_BloqueioPagtoOnline($mensagem);
                    
                    //Verifica se o pagamento com EPP CASH está habilitado
                    $have_eppcash = false;
                    
                    //Verifica se apenas o pagamento EPP CASH está habilitado
                    $only_eppcash = false;
                    
                    //Conta a quantidade de pagamentos habilitados
                    $cont_pagamentos = 0;
                    
                    if($b_libera_Bradesco && PAGAMENTO_BRADESCO){
                        $cont_pagamentos++;
                        $div_bradesco = true;
                    }
                    
                    if($b_libera_Deposito){
                        $cont_pagamentos++;
                        $div_deposito = true;
                    }

                    if($controller->usuario->b_IsLogin_pagamento_bancodobrasil() && PAGAMENTO_BANCO_BRASIL && $b_libera_BancodoBrasil) {
                        $cont_pagamentos++;
                        $div_brasil = true;
                    }

                    if(PAGAMENTO_ITAU && $controller->usuario->b_IsLogin_pagamento_bancoitau() && $b_libera_BancoItau) {
                        $cont_pagamentos++;
                        $div_itau = true;
                    }

                    if(PAGAMENTO_BOLETO && $b_libera_Boleto){
                        $cont_pagamentos++;
                        $div_boleto = true;
                    }

                    if($controller->usuario->b_IsLogin_pagamento_pin_EPP_Cash() && b_pin_forma_pagamento() && $b_libera_EppCash && PAGAMENTO_EPREPAG_CASH){
                        $cont_pagamentos++;
                        $have_eppcash = true;
                        $div_eppcash = true;
                    }    
                            
                    if(($controller->usuario->b_IsLogin_pagamento_Cielo() && b_cielo_forma_pagamento() && PAGAMENTO_CIELO) && (!$b_bloqueia_Ongame) && ($libera_pagamento['Cielo_Visa_DEB'] || $libera_pagamento['Cielo_Visa_CRED'] || $libera_pagamento['Cielo_Master_CRED'] || $libera_pagamento['Cielo_Elo_CRED'] || $libera_pagamento['Cielo_Diners_CRED'] || $libera_pagamento['Cielo_Discover_CRED']) && $b_libera_Cielo ) {
                        $cont_pagamentos++;
                        $div_cielo = true;
                    }
                    
                    if($b_libera_Pix && PAGAMENTO_PIX){
                        $cont_pagamentos++;
                        $div_pix = true;
                    }

                    if($cont_pagamentos == 1 && $have_eppcash){
                        $only_eppcash = true;
                    }
?>
            <input type="hidden" name="iforma" value="0">
            <input type="hidden" name="idu" value="0">
            <input type="hidden" name="sno" value="0">
<?php
    //Variável para identificar quando é necessário pular linha
    $cont_colunas = 0;
?>
            <div class="row espacamento">
                
                <div class="<?php if(isset($div_pix)) echo 'col-xs-12 col-md-4 mt-sm-15'; else echo "dnone"; ?>">
                    <div class="row">
<?php                        
                        // Bloqueio Pagamento Ongame - inicio
                        if(!$b_bloqueia_Ongame && (isset($div_pix))) {
                            $cont_colunas++;
                            
                            if($b_libera_Pix && PAGAMENTO_PIX){
                                $onclick = 'save_shipping(\''. $FORMAS_PAGAMENTO['PAGAMENTO_PIX'] . '\',' . (($controller->usuario->getId()>0)?$controller->usuario->getId():'0') . ',\'' . $controller->usuario->getNome() . '\');';
                            }else{
                                $onclick = '';
                            }
?>           
                            <div class="col-xs-4">
                               <img 
                                    src="/imagens/pag/iconePIX.png" 
                                    class="c-pointer btnPgto" 
                                    style="width:100%; max-width: 100px;"
                                    name="btn_5"
                                    title="<?php echo $FORMAS_PAGAMENTO_DESCRICAO[$FORMAS_PAGAMENTO['PAGAMENTO_PIX']] ?>"
                                    onClick="<?php echo $onclick; ?>"
                                > 
                            </div> 
                            <div class="col-xs-8">
<?php
                                if($b_libera_Pix && PAGAMENTO_PIX){
?>
                                   <p class="fontsize-pp bottom0 top20">PIX</p> 
<?php
                                    if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA && $PAGAMENTO_PIX_TAXA != 0) {
                                           echo "<p class='txt-cinza fontsize-pp bottom0'>Taxa de serviço: R$ ".number_format($PAGAMENTO_PIX_TAXA, 2, ',', '.')."</p>";
                                    }//end if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                                    else {
                                         echo "<p class='txt-vermelho fontsize-pp bottom0'>Sem taxa de serviço</p>";
										//echo "<p class='txt-vermelho fontsize-pp bottom0'>ATENÇAO pix itaú indisponível<br> <strong>faça o pagamento pix por outro banco</strong></p>";
                                    }//end else do if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
?>
                                    <p class="txt-verde fontsize-pp">Entrega Imediata</p> 
                                    <span style='display:none'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_PIX']?>" <?php if($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_PIX']) echo " checked"; ?>></span> 
<?php
                                }else{
                                    echo $msg_bloqueia_Pix;
                                }
?>
                            </div>
<?php
                        }
?>
                    </div>
                </div>
<?php
                if($cont_colunas == 3){
                    echo "</div><div class='row espacamento'>";
                    $cont_colunas = 0;
                }
?>
                <div class="<?php if(isset($div_bradesco)) echo 'col-xs-12 col-md-4 mt-sm-15'; else echo "dnone"; ?>">
                    <div class="row">

<?php
                        // Bloqueio Pagamento Ongame - inicio
                        if(!$b_bloqueia_Ongame && (isset($div_bradesco))) {
                            $cont_colunas++;

                            // Linha Bradesco - inicio
                            if($b_libera_Bradesco && PAGAMENTO_BRADESCO && false) { 
?>
<!--                                <p>
                                    <img class="c-pointer btnPgto" 
                                         src="/imagens/pag/pagto_forma_debito_visa1.gif" 
                                         name="btn_5" 
                                         onMouseOver="document.btn_5.src='/imagens/pag/pagto_forma_debito_visa2.gif'" 
                                         onMouseOut="document.btn_5.src='/imagens/pag/pagto_forma_debito_visa1.gif'" 
                                         title="Bradesco pagamento (Débito em conta)" 
                                         onClick="save_shipping(<?php // echo $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO'] ?>, <?php // echo (($controller->usuario->getId()>0)?$controller->usuario->getId():"0") ?>, '<?php // echo $controller->usuario->getNome() ?>');">
                                </p>-->
<!--                                <p class="txt-vermelho fontsize-pp bottom0">Sem taxa de serviço</p>
                                <p class="txt-verde fontsize-pp">Entrega em até 90 minutos</p>
                                <span style='display:none'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO']?>" <?php if($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO']) echo " checked"; ?>></span>-->
<?php
                            } //end if($b_libera_Bradesco && PAGAMENTO_BRADESCO && false)
                            else { 
                                    echo $msg_bloqueia_Bradesco;
                            }
                            if($b_libera_Bradesco && PAGAMENTO_BRADESCO) { 
                                $onclick = 'save_shipping('. $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO'] . ',' . (($controller->usuario->getId()>0)?$controller->usuario->getId():'0') . ',\'' . $controller->usuario->getNome() . '\');';
                            }else{
                                $onclick = '';
                            }
?>
                            <div class="col-xs-4">
                                <img 
                                    src="/imagens/pag/iconeBradesco.png" 
                                    class="c-pointer btnPgto" 
                                    style="width:100%; max-width: 100px;"
                                    name="btn_5"
                                    title="Bradesco pagamento (Transferência entre contas)"
                                    onClick="<?php echo $onclick; ?>"
                                >
                            </div>
                            <div class="col-xs-8">
<?php
                                if($b_libera_Bradesco && PAGAMENTO_BRADESCO){
?>
                                    <p class="fontsize-pp bottom0 top20">TRANSFERÊNCIA ENTRE CONTAS</p>
<?php
                                    if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA && $BRADESCO_TRANSFERENCIA_ENTRE_CONTAS_TAXA_ADICIONAL != 0) {
                                            echo "<p class='txt-cinza fontsize-pp bottom0'>Taxa de serviço: R$ ".number_format($BRADESCO_TRANSFERENCIA_ENTRE_CONTAS_TAXA_ADICIONAL, 2, ',', '.')."</p>";
                                    }//end if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                                    else {
                                        echo "<p class='txt-vermelho fontsize-pp bottom0'>Sem taxa de serviço</p>";
                                    }//end else do if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
?>
                                    <p class="txt-verde fontsize-pp">Entrega em até 90 minutos</p>
                                    <span style='display:none'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']?>" <?php if($pagto == $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) echo " checked"; ?>></span>
<?php
                                }else{
                                    echo $msg_bloqueia_Bradesco;
                                }
?>
                            </div>
<?php
//                                if($b_libera_Deposito) {
?>
<!--                                <p>
                                    <img class="c-pointer" 
                                        src="/imagens/pag/pagto_forma_deposito1.gif" 
                                        name="btn_22br" 
                                        onmouseover="document.btn_22br.src='/imagens/pag/pagto_forma_deposito2.gif'" 
                                        onmouseout="document.btn_22br.src='/imagens/pag/pagto_forma_deposito1.gif'" 
                                        width="110" height="35" border="0" title="Bradesco pagamento depósito"
                                        onClick="save_shipping(<?php // echo $FORMAS_PAGAMENTO['DEP_DOC_TRANSF'] ?>, <?php // echo (($controller->usuario->getId()>0)?$controller->usuario->getId():"0") ?>, '<?php // echo $controller->usuario->getNome() ?>');">
                                </p>
                                <p class="txt-vermelho fontsize-pp bottom0">Sem taxa de serviço</p>
                                <p class="txt-verde fontsize-pp">Entrega em até 1 dia útil</p>
                                <p class="fontsize-pp txt-preto"><strong>Depósito, DOC
                                    Transferência offline
                                    Ag.2062-1<br>
                                    Cc.0030265-1</strong>
                                </p>
                                <p class="txt-preto fontsize-pp"><input type="checkbox" name="pagto_ja_fiz" id="pagto_ja_fiz" value="1" class="" <?php if($pagto_ja_fiz == "1") echo "checked"; ?>>  Fiz o depósito e quero informar os dados.</p>
                                <span style='display:none'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['DEP_DOC_TRANSF']?>" <?php if($pagto == $FORMAS_PAGAMENTO['DEP_DOC_TRANSF']) echo " checked"; ?>></span>-->
<?php 
//                                } //end if($b_libera_Deposito)
//                                elseif($libera_pagamento['Deposito']){ 
                                    //echo $msg_bloqueia_Deposito;
//                                } //end elseif($libera_pagamento['Deposito'])
                                // Linha Bradesco - fim
                        }// if(!$b_bloqueia_Ongame)
                        // Bloqueio Pagamento Ongame - fim
?>
                    
                    </div>
                </div>
<?php
                if($cont_colunas == 3){
                    echo "</div><div class='row espacamento'>";
                    $cont_colunas = 0;
                }
?>
                         <div class="<?php if(isset($div_itau)) echo 'col-xs-12 col-md-4 mt-sm-15'; else echo "dnone"; ?>" style="float: left;">
                    <div class="row">
<?php
                        // Bloqueio Pagamento Ongame - inicio
                        if(!$b_bloqueia_Ongame && isset($div_itau)) {  
                            $cont_colunas++;
                            // Linha Banco itau - inicio

                            //Constante do configurador de meios de pagamentos
                            if(PAGAMENTO_ITAU && $controller->usuario->b_IsLogin_pagamento_bancoitau()) {

                                if($b_libera_BancoItau) {
                                    $onclick = 'save_shipping(\''. $FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE'] . '\',' . (($controller->usuario->getId()>0)?$controller->usuario->getId():'0') . ',\'' . $controller->usuario->getNome() . '\');';
                                } //end if($b_libera_BancoItau)
                                else {
                                    $onclick = '';
                                } 
?>
                                <div class="col-xs-4">
                                    <img 
                                        src="/imagens/pag/iconeShoplineItau.png" 
                                        class="c-pointer btnPgto" 
                                        style="width:100%; max-width: 100px;"
                                        name="btn_10"
                                        title="<?php echo $FORMAS_PAGAMENTO_DESCRICAO[$FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE']] ?>"
                                        onClick="<?php echo $onclick; ?>"
                                    >
                                </div>
                                <div class="col-xs-8">
<?php
                                    if($b_libera_BancoItau) {
?>
                                        <p class="fontsize-pp bottom0 top20">TRANSFERÊNCIA ENTRE CONTAS</p>
<?php
                                        if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA && $BANCO_ITAU_TAXA_DE_SERVICO != 0) {
                                                echo "<p class='txt-cinza fontsize-pp bottom0'>Taxa de serviço: R$ ".number_format($BANCO_ITAU_TAXA_DE_SERVICO, 2, ',', '.')."</p>";
                                        }//end if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                                        else {
                                            echo "<p class='txt-vermelho fontsize-pp bottom0'>Sem taxa de serviço</p>";
                                        }//end else do if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
?>
                                        <p class="txt-verde fontsize-pp">Entrega em até 30 minutos</p>
                                        <span style='display:none'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE']?>" <?php if($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE']) echo " checked"; ?>></span>
<?php
                                    }else{
                                        echo $msg_bloqueia_BancoItau;
                                    }
?>
                                </div>
<?php
                            }//end if(PAGAMENTO_ITAU && $controller->usuario->b_IsLogin_pagamento_bancoitau())
                            else {
                                echo "<p class='txt-azul fontsize-pp'>Serviço indisponível no momento</p>";
                            }
                    // Linha Banco Itau - fim
                    
                        }// if(!$b_bloqueia_Ongame)
                    // Bloqueio Pagamento Ongame - fim 
?>
                    </div>
                </div>       

<?php
                if($cont_colunas == 3){
                    echo "</div><div class='row espacamento'>";
                    $cont_colunas = 0;
                }
?>
                <div class="<?php if(isset($div_brasil)) echo 'col-xs-12 col-md-4 mt-sm-15'; else echo "dnone";?>">
                    <div class="row">
<?php
                    
                    // Bloqueio Pagamento Ongame - inicio
                    if(!$b_bloqueia_Ongame && (isset($div_brasil))) {
                        $cont_colunas++;
                        // Linha BB - inicio
?>
<?php                
                        if($controller->usuario->b_IsLogin_pagamento_bancodobrasil() && PAGAMENTO_BANCO_BRASIL) {	
                            if($b_libera_BancodoBrasil) {
                                $onclick = 'save_shipping('. $FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA'] . ',' . (($controller->usuario->getId()>0)?$controller->usuario->getId():'0') . ',\'' . $controller->usuario->getNome() . '\');';
                            } //end if($b_libera_BancodoBrasil) 
                            else {
                                $onclick = '';
                                
                            } 
?>
                            <div class="col-xs-4">
                                <img 
                                    src="/imagens/pag/iconeBrancodoBrasil.png" 
                                    class="c-pointer btnPgto" 
                                    style="width:100%; max-width: 100px;"
                                    name="btn_9"
                                    title="<?php echo $FORMAS_PAGAMENTO_DESCRICAO[$FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']] ?>"
                                    onClick="<?php echo $onclick; ?>"
                                >
                            </div>
<!--                                <p>
                                <img name="btn_9" class="c-pointer btnPgto" 
                                onMouseOver="document.btn_9.src='/imagens/pag/pagto_forma_debito_2.gif'" 
                                onMouseOut="document.btn_9.src='/imagens/pag/pagto_forma_debito_1.gif'" 
                                src="/imagens/pag/pagto_forma_debito_1.gif"  
                                title="<?php // echo $FORMAS_PAGAMENTO_DESCRICAO[$FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']] ?>"
                                onClick="save_shipping(<?php // echo $FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA'] ?>, <?php // echo (($controller->usuario->getId()>0)?$controller->usuario->getId():"0") ?>, '<?php // echo $controller->usuario->getNome() ?>');">
                            </p>-->
                            <div class="col-xs-8">
<?php
                            if($b_libera_BancodoBrasil) {
?>
                                <p class="fontsize-pp bottom0 top20">DÉBITO EM CONTA</p>
<?php
                                if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA && $BANCO_DO_BRASIL_TAXA_DE_SERVICO != 0) {
                                        echo "<p class='txt-cinza fontsize-pp bottom0'>Taxa de serviço: R$ ".number_format($BANCO_DO_BRASIL_TAXA_DE_SERVICO, 2, ',', '.')."</p>";
                                }//end if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                                else {
                                    echo "<p class='txt-vermelho fontsize-pp bottom0'>Sem taxa de serviço</p>";
                                }//end else do if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
?>
                                <p class="txt-verde fontsize-pp">Entrega em até 30 minutos</p>
                                <span style='display:none'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']?>" <?php if($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']) echo " checked"; ?>></span>
<?php
                            }else{
                                echo $msg_bloqueia_BancodoBrasil;
                            }
?>

                            </div>
<?php	
                            
                        }//end if($controller->usuario->b_IsLogin_pagamento_bancodobrasil() && PAGAMENTO_BANCO_BRASIL) 
                        
                    }// if(!$b_bloqueia_Ongame)
                    // Bloqueio Pagamento Ongame - fim
?>
                    </div>
                </div>
<?php
                if($cont_colunas == 3){
                    echo "</div><div class='row espacamento'>";
                    $cont_colunas = 0;
                }
?>

<?php

                    // Linha HIPAY - inicio
/* Comentado por nao usar                    
                    if($controller->usuario->b_IsLogin_pagamento_hipay() && false) {		// DUMMY
?>
                            <td valign="top" width="20%">
                                    <TABLE cellspacing="0" cellpadding="0" width="100%">
                                    <tr height="<?php echo $title_height ?>" align="center" valign="middle">
                                            <td style="BORDER-BOTTOM: #cccccc 1px solid;" >&nbsp;<b>HIPAY</b>&nbsp;</td>
                                    </tr>
                                    <tr>
                                            <td>
                                                    <TABLE border="0" cellspacing="0" cellpadding="0" width="100%">
                                                            <tr align="center" valign="top">
                                                                    <td width="33%" align="center"
                                                                    <?php 
                                                                            if($controller->usuario->b_IsLogin_pagamento_hipay()) {
                                                                                    if($b_libera_Hipay) { ?>>&nbsp;<br>
                                                                      <img src="images/botao_hipay.gif" 
                                                                            name="btn_11" 
                                                                            onMouseOver="document.btn_11.src='../pag/images/pagto_forma_debito_visa2.gif'" 
                                                                            onMouseOut="document.btn_11.src='../pag/images/pagto_forma_debito_visa1.gif'" 
                                                                            width="110" height="35" border="0" title="<?php echo $FORMAS_PAGAMENTO_DESCRICAO[$FORMAS_PAGAMENTO['PAGAMENTO_HIPAY_ONLINE']] ?>" onClick="save_shipping('<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_HIPAY_ONLINE'] ?>', <?php echo (($controller->usuario->getId()>0)?$controller->usuario->getId():"0") ?>, '<?php echo $controller->usuario->getNome() ?>')"><br>
                                                                            <span class="style20">Pagamento Online - HIPAY</span><br>
                                                                            <span style='display:none'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_HIPAY_ONLINE']?>" <?php if($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_HIPAY_ONLINE']) echo " checked"; ?>></span>

                                                                    <?php	} else {
                                                                                            echo ">".$msg_bloqueia_Hipay."<br>&nbsp;";
                                                                                    } 
                                                                            } else {
                                                                                    echo ">&nbsp;";
                                                                            }
                                                                    ?>
                                                                    </td>
                                                            </tr>
                                                    </table>
                                            </td>
                                    </tr>
                                    </table>
                            </td>
                            <td valign="top" align="center" width="10%"><img src="../pag/images/linhadeseparacao.gif" width="1" height="324" border="0">
                            </td>
<?php
                    }
                    // Linha HIPAY - fim

                    // Linha PAYPAL - inicio
                    if($controller->usuario->b_IsLogin_pagamento_paypal() && false) {		// DUMMY
?>
                            <td valign="top" width="20%">
                                    <TABLE cellspacing="0" cellpadding="0" width="100%">
                                    <tr height="<?php echo $title_height ?>" align="center" valign="middle">
                                            <td style="BORDER-BOTTOM: #cccccc 1px solid;" >&nbsp;<b>PAYPAL</b>&nbsp;</td>
                                    </tr>
                                    <tr>
                                            <td>
                                                    <TABLE border="0" cellspacing="0" cellpadding="0" width="100%">
                                                            <tr align="center" valign="top">
                                                                    <td width="33%" align="center" 
                                                                    <?php 
                                                                            if($controller->usuario->b_IsLogin_pagamento_paypal()) {
                                                                                    if($b_libera_Paypal) { ?>>&nbsp;<br>
                                                                      <img src="images/botao_paypal.gif" 
                                                                            name="btn_12" 
                                                                            onMouseOver="document.btn_12.src='../pag/images/pagto_forma_debito_visa2.gif'" 
                                                                            onMouseOut="document.btn_12.src='../pag/images/pagto_forma_debito_visa1.gif'" 
                                                                            width="110" height="35" border="0" title="<?php echo $FORMAS_PAGAMENTO_DESCRICAO[$FORMAS_PAGAMENTO['PAGAMENTO_PAYPAL_ONLINE']] ?>" onClick="save_shipping('<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_PAYPAL_ONLINE'] ?>', <?php echo (($controller->usuario->getId()>0)?$controller->usuario->getId():"0") ?>, '<?php echo $controller->usuario->getNome() ?>')"><br>
                                                                            <span class="style20">Pagamento Online - PAYPAL</span><br>
                                                                            <span style='display:none'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_PAYPAL_ONLINE']?>" <?php if($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_PAYPAL_ONLINE']) echo " checked"; ?>></span>

                                                                    <?php	} else {
                                                                                            echo ">".$msg_bloqueia_Paypal."<br>&nbsp;";
                                                                                    } 
                                                                            } else {
                                                                                    echo ">&nbsp;";
                                                                            }
                                                                    ?>
                                                                    </td>
                                                            </tr>
                                                    </table>
                                            </td>
                                    </tr>
                                    </table>
                            </td>
                            <td valign="top" align="center" width="10%"><img src="../pag/images/linhadeseparacao.gif" width="1" height="324" border="0">
                            </td>
<?php
                    }
                    // Linha PAYPAL - fim
Comentado por nao usar
*/  
                    // Linha Boleto - inicio
?>
                <?php
                if($b_libera_Deposito) {
                    $cont_colunas++;
?>
                    <div class="col-xs-12 col-md-4 mt-sm-15">
                        <div class="row">
                            <div class="col-xs-4">
                                <img 
                                    src="/imagens/pag/iconeDeposito.png" 
                                    class="c-pointer btnPgto" 
                                    style="width:100%; max-width: 100px;"
                                    name="btn_5"
                                    title="Pagamento por Depósito"
                                    onClick="save_shipping(<?php echo $FORMAS_PAGAMENTO['DEP_DOC_TRANSF'] ?>, <?php echo (($controller->usuario->getId()>0)?$controller->usuario->getId():"0") ?>, '<?php echo $controller->usuario->getNome() ?>');"
                                >
                            </div>
                            <div class="col-xs-8">
                                <p class="fontsize-pp bottom0">Depósito / DOC / TED</p>
                                <p class="txt-vermelho fontsize-pp bottom0">Sem taxa de serviço</p>
                                <p class="txt-verde fontsize-pp">Entrega em até 1 dia útil</p>
<!--                                <p class="fontsize-pp txt-preto"><strong>Depósito, DOC
                                    Transferência offline
                                    Ag.2062-1<br>
                                    Cc.0030265-1</strong>
                                </p>-->
                                <p class="txt-preto fontsize-pp"><input type="checkbox" name="pagto_ja_fiz" id="pagto_ja_fiz" value="1" onchange='check_deposito(this);' class="" <?php if($pagto_ja_fiz == "1") echo "checked"; ?>>  Após efetuar o pagamento, clique aqui para informar os dados.</p>
                                <span style='display:none'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['DEP_DOC_TRANSF']?>" <?php if($pagto == $FORMAS_PAGAMENTO['DEP_DOC_TRANSF']) echo " checked"; ?>></span>
                            </div>
                        </div>
                    </div>
                
<?php
                }
?>
<?php
                if($cont_colunas == 3){
                    echo "</div><div class='row espacamento'>";
                    $cont_colunas = 0;
                }
?>                
                <div class="<?php if(isset($div_boleto)) echo 'col-xs-12 col-md-4 mt-sm-15'; else echo "dnone";?>" style="float: left;">
                    <div class="row">
<?php                
                    // Bloqueio Pagamento Ongame - inicio
                        if(!$b_bloqueia_Ongame && isset($div_boleto)) {  
                            $cont_colunas++;
                            //Constante do configurador de meios de pagamentos
                            if(PAGAMENTO_BOLETO) {
                                if($b_libera_Boleto) {
                                    $onclick = 'save_shipping(\''. $FORMAS_PAGAMENTO['BOLETO_BANCARIO'] . '\',' . (($controller->usuario->getId()>0)?$controller->usuario->getId():'0') . ',\'' . $controller->usuario->getNome() . '\');';
                                }//end if($b_libera_Boleto)
                                else { 
                                    $onclick = '';
                                }
?>
                                <div class="col-xs-4">
                                    <img 
                                        src="/imagens/pag/iconeBoleto.png" 
                                        class="c-pointer btnPgto" 
                                        style="width:100%; max-width: 100px;"
                                        name="btn_2"
                                        title="Boleto Bancário"
                                        onClick="<?php echo $onclick; ?>"
                                    >
                                </div>
                                <div class="col-xs-8">
<?php
                                    if($b_libera_Boleto){
?>
                                        <p class="fontsize-pp bottom0 top20">BOLETO BANCÁRIO</p>
<?php
                                        if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA && $BOLETO_TAXA_ADICIONAL != 0) {
                                                echo "<p class='txt-cinza fontsize-pp bottom0'>Taxa de serviço: R$ ".number_format($BOLETO_TAXA_ADICIONAL, 2, ',', '.')."</p>";
                                        }//end if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                                        else {
                                            echo "<p class='txt-vermelho fontsize-pp bottom0'>Sem taxa de serviço</p>";
                                        }//end else do if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
?>
                                        <p class="txt-verde fontsize-pp">Entrega em até 2 dias úteis.</p>
                                        <span style='display:none'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['BOLETO_BANCARIO']?>" <?php if($pagto == $FORMAS_PAGAMENTO['BOLETO_BANCARIO']) echo " checked"; ?>></span>
<?php
                                    }else{
                                        echo $msg_bloqueia_Boleto;
                                    }
?>
                                </div>
<?php
                                
                            }//end if(PAGAMENTO_BOLETO)
                            else {
                                echo "<p class='txt-azul fontsize-pp'>Serviço indisponível no momento</p>";
                            }
                            // Linha Boleto - fim

                        }// if(!$b_bloqueia_Ongame)
                        // Bloqueio Pagamento Ongame - fim 

?>
                    </div>
                </div>
<?php
                if($cont_colunas == 3){
                    echo "</div><div class='row espacamento'>";
                    $cont_colunas = 0;
                }
?>                
                <div class="<?php if($only_eppcash) echo "col-xs-offset-1" ?> <?php if(isset($div_eppcash)) echo "col-xs-12 col-md-4 mt-sm-15"; else echo "dnone";?>">
                    <div class="row">   
<?php                
                        // Bloqueio Pagamento Ongame - inicio
                        if(!$b_bloqueia_Ongame && isset($div_eppcash)) {
                            $cont_colunas++;
?>                          
<?php
                            // Linha PIN E-PREPAG - inicio
                            if ($controller->usuario->b_IsLogin_pagamento_pin_EPP_Cash() && b_pin_forma_pagamento() && $b_libera_EppCash && PAGAMENTO_EPREPAG_CASH) {
?>
                                <div class="col-xs-4">
                                    <img 
                                        src="/imagens/pag/iconeeppcash.png" 
                                        class="c-pointer btnPgto" 
                                        style="width:100%; max-width: 100px;"
                                        name="btn_13"
                                        title="<?php echo $FORMAS_PAGAMENTO_DESCRICAO[$FORMAS_PAGAMENTO['PAGAMENTO_PIN_EPREPAG']] ?>"
                                        onClick="save_shipping('<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_PIN_EPREPAG'] ?>', <?php echo (($controller->usuario->getId()>0)?$controller->usuario->getId():"0") ?>, '<?php echo $controller->usuario->getNome() ?>');"
                                    >
                                </div>
                                <div class="col-xs-8">
                                    <p class="fontsize-pp bottom0">E-PREPAG CASH</p>
                                    <p class='txt-vermelho fontsize-pp bottom0'>Sem taxa de serviço</p>
                                    <p class="txt-verde fontsize-pp">Entrega Imediata</p>
<?php
                                    if(!$only_eppcash){
?>
                                        <p class="txt-azul fontsize-pp bottom0"><a href="https://www.e-prepag.com.br/game/produto/detalhe.php?token=IlJQTB5TdFZVAj0XFwEHNwdDTRYafVtNFXtVWl4P">Não tem um PIN?</a></p>
<?php
                                    }
?>
                                    <span style='display:none'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_PIN_EPREPAG']?>" <?php if($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_PIN_EPREPAG']) echo " checked"; ?>></span>
                                </div>
<?php
                            }//end if ($controller->usuario->b_IsLogin_pagamento_pin_EPP_Cash() && b_pin_forma_pagamento() && $b_libera_EppCash && PAGAMENTO_EPREPAG_CASH)
                            else {
                                echo "<p class='txt-azul fontsize-pp'>Serviço indisponível no momento</p>";
                            }
                        // Linha PIN E-PREPAG - fim

                        }// if(!$b_bloqueia_Ongame)
                    // Bloqueio Pagamento Ongame - fim 
?>
                    </div>
                </div>
<?php
                if($only_eppcash){
?>
                <div class="col-xs-12 col-md-6 mt-sm-20">
                    <span class="txt-cinza">
                        Para finalizar esta compra, você precisa de um <b><a href="https://www.e-prepag.com.br/game/produto/detalhe.php?token=IlJQTB5TdFZVAj0XFwEHNwdDTRYafFtNGHlHQxg">Cartão E-Prepag Cash</a></b> ou <b><a href="https://www.e-prepag.com.br/game/conta/add-saldo.php">saldo</a></b> em sua Conta E-Prepag.<br>
                        Caso já possua um <b>cartão</b> ou <b>saldo</b>, clique no botão <b>"E-PREPAG Cash"</b> e finalize a compra.<br><br>
                        Caso queira adquirir um Cartão E-Prepag Cash, <b><a href="https://www.e-prepag.com.br/game/produto/detalhe.php?token=IlJQTB5TdFZVAj0XFwEHNwdDTRYafFtNGHlHQxg">clique aqui</a></b><br>
                        <i>(Você pode pagar por boleto bancário, transferência, débito em conta, depósito bancário, DOC ou TED)</i>
                    </span>
                </div>
<?php
                }

                if($cont_colunas == 3){
                    echo "</div><div class='row espacamento'>";
                    $cont_colunas = 0;
                }
?>
                <!--<div class="col-md-2 text-center">-->
                        
<?php                
                // Bloqueio Pagamento Ongame - inicio
                if(!$b_bloqueia_Ongame && isset($div_cielo)) {
?>
<?php 
                    // Linha CIELO - inicio
                    if(($controller->usuario->b_IsLogin_pagamento_Cielo() && b_cielo_forma_pagamento() && PAGAMENTO_CIELO) && (!$b_bloqueia_Ongame) && $libera_pagamento['Cielo']) {
                        if($b_libera_Cielo) {
                            $liberado = true;
                        }//end if($b_libera_Cielo)
                        else { 
                            $liberado = false;        
                        }//end else do if($b_libera_Cielo)
                            

                        if($libera_pagamento['Cielo_Visa_DEB']){
                            $cont_colunas++;
                            if($liberado){
                                $onclick = 'save_shipping(\''. $FORMAS_PAGAMENTO['PAGAMENTO_VISA_DEBITO'] . '\',' . (($controller->usuario->getId()>0)?$controller->usuario->getId():'0') . ',\'' . $controller->usuario->getNome() . '\');';
                            }else{
                                $onclick = '';
                            }
?>
                            <div class="col-xs-12 col-md-4 mt-sm-15">
                                <div class="row">
                                     <div class="col-xs-4">
                                        <img 
                                            src="/imagens/pag/iconeVisa.png" 
                                            class="c-pointer btnPgto" 
                                            style="width:100%; max-width: 100px;"
                                            name="btn_14"
                                            title="<?php echo $FORMAS_PAGAMENTO_DESCRICAO[$FORMAS_PAGAMENTO['PAGAMENTO_VISA_DEBITO']] ?>"
                                            onClick="<?php echo $onclick; ?>"
                                        >
                                    </div>
                                    <div class="col-xs-8 top20">
<?php
                                        if($liberado){
?>
                                            <p class="fontsize-pp bottom0">VISA - DÉBITO</p>
<?php
                                            if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA && $PAGAMENTO_VISA_DEBITO_TAXA != 0) {
                                                echo "<p class='txt-cinza fontsize-pp bottom0'>Taxa de serviço: R$ ".number_format($PAGAMENTO_VISA_DEBITO_TAXA, 2, ',', '.')."</p>";
                                            }//end if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                                            else {
                                                echo "<p class='txt-vermelho fontsize-pp bottom0'>Sem taxa de serviço</p>";
                                            }//end else do if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
?>
                                            <p class="txt-verde fontsize-pp">Entrega em até 30 minutos</p>
                                            <span style='display:none'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_VISA_DEBITO']?>" <?php if($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_VISA_DEBITO']) echo " checked"; ?>></span>

<?php
                                        }else{
                                            echo $msg_bloqueia_Cielo;
                                        }
?>
                                        
                                    </div>
                                </div>
                            </div>

<?php 
                            if($cont_colunas == 3){
                                echo "</div><div class='row espacamento'>";
                                $cont_colunas = 0;
                            }
                        /* 
?>
                        <?php
                        if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA && $PAGAMENTO_VISA_DEBITO_TAXA != 0) {
                            echo "<p class='txt-cinza fontsize-pp bottom0'>Taxa de serviço: R$ ".number_format($PAGAMENTO_VISA_DEBITO_TAXA, 2, ',', '.')."</p>";
                        }//end if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                        else {
                            echo "<p class='txt-vermelho fontsize-pp bottom0'>Sem taxa de serviço</p>";
                        }//end else do if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                        ?>
                        <p class="txt-verde fontsize-pp">Entrega em até 30 minutos</p>
<?php */ 
?>
<?php
                        }
                                
                        if($libera_pagamento['Cielo_Visa_CRED']){
                            $cont_colunas++;
                            if($liberado){
                                $onclick = 'save_shipping(\''. $FORMAS_PAGAMENTO['PAGAMENTO_VISA_CREDITO'] . '\',' . (($controller->usuario->getId()>0)?$controller->usuario->getId():'0') . ',\'' . $controller->usuario->getNome() . '\');';
                            }else{
                                $onclick = '';
                            }
?>
                            <div class="col-xs-12 col-md-4 mt-sm-15">
                                <div class="row">
                                     <div class="col-xs-4">
                                        <img 
                                            src="/imagens/pag/iconeVisa.png" 
                                            class="c-pointer btnPgto" 
                                            style="width:100%; max-width: 100px;"
                                            name="btn_15"
                                            title="<?php echo $FORMAS_PAGAMENTO_DESCRICAO[$FORMAS_PAGAMENTO['PAGAMENTO_VISA_CREDITO']] ?>"
                                            onClick="<?php echo $onclick; ?>"
                                        >
                                    </div>
                                    <div class="col-xs-8 top20">
<?php
                                        if($liberado){
?>
                                            <p class="fontsize-pp bottom0">VISA - CRÉDITO</p>
<?php
                                            if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA && $PAGAMENTO_VISA_CREDITO_TAXA != 0) {
                                                echo "<p class='txt-cinza fontsize-pp bottom0'>Taxa de serviço: R$ ".number_format($PAGAMENTO_VISA_CREDITO_TAXA, 2, ',', '.')."</p>";
                                            }//end if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                                            else {
                                                echo "<p class='txt-vermelho fontsize-pp bottom0'>Sem taxa de serviço</p>";
                                            }//end else do if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
?>
                                            <p class="txt-verde fontsize-pp">Entrega em até 30 minutos</p>
                                            <span style='display:none'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_VISA_CREDITO']?>" <?php if($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_VISA_CREDITO']) echo " checked"; ?>></span>

<?php
                                        }else{
                                            echo $msg_bloqueia_Cielo;
                                        }
?>
                                        
                                    </div>
                                </div>
                            </div>
<?php
                            if($cont_colunas == 3){
                                echo "</div><div class='row espacamento'>";
                                $cont_colunas = 0;
                            }
/*
?>
                        <?php
                        if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA && $PAGAMENTO_VISA_CREDITO_TAXA != 0) {
                            echo "<p class='txt-cinza fontsize-pp bottom0'>Taxa de serviço: R$ ".number_format($PAGAMENTO_VISA_CREDITO_TAXA, 2, ',', '.')."</p>";
                        }//end if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                        else {
                            echo "<p class='txt-vermelho fontsize-pp bottom0'>Sem taxa de serviço</p>";
                        }//end else do if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                        ?>
                        <p class="txt-verde fontsize-pp">Entrega em até 30 minutos</p>
<?php
*/
?>
<?php 
                        }
/* 
 ?>
                        <img src="../pag/images/pagto_maestro.gif" 
                        name="btn_16" 
                        onMouseOver="document.btn_16.src='../pag/images/pagto_maestro_over.gif'" 
                        onMouseOut="document.btn_16.src='../pag/images/pagto_maestro.gif'" 
                        width="110" height="35" border="0" title="<?php echo $FORMAS_PAGAMENTO_DESCRICAO[$FORMAS_PAGAMENTO['PAGAMENTO_MASTER_DEBITO']] ?>" onClick="save_shipping('<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_MASTER_DEBITO'] ?>', <?php echo (($controller->usuario->getId()>0)?$controller->usuario->getId():"0") ?>, '<?php echo $controller->usuario->getNome() ?>');">
                        <?php
                        if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA && $PAGAMENTO_MASTER_DEBITO_TAXA != 0) {
                            echo "<p class='txt-cinza fontsize-pp bottom0'>Taxa de serviço: R$ ".number_format($PAGAMENTO_MASTER_DEBITO_TAXA, 2, ',', '.')."</p>";
                        }//end if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                        else {
                            echo "<p class='txt-vermelho fontsize-pp bottom0'>Sem taxa de serviço</p>";
                        }//end else do if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                        ?>
                        <p class="txt-verde fontsize-pp">Entrega em até 30 minutos</p>
                        <span style='display:none'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_MASTER_DEBITO']?>" <?php if($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_MASTER_DEBITO']) echo " checked"; ?>></span>
<?php
*/ 
                        if($libera_pagamento['Cielo_Master_CRED']){
                            $cont_colunas++;
                            if($liberado){
                                $onclick = 'save_shipping(\''. $FORMAS_PAGAMENTO['PAGAMENTO_MASTER_CREDITO'] . '\',' . (($controller->usuario->getId()>0)?$controller->usuario->getId():'0') . ',\'' . $controller->usuario->getNome() . '\');';
                            }else{
                                $onclick = '';
                            }
?>
                            <div class="col-xs-12 col-md-4 mt-sm-15">
                                <div class="row">
                                     <div class="col-xs-4">
                                        <img 
                                            src="/imagens/pag/iconeMasterCard.png" 
                                            class="c-pointer btnPgto" 
                                            style="width:100%; max-width: 100px;"
                                            name="btn_17"
                                            title="<?php echo $FORMAS_PAGAMENTO_DESCRICAO[$FORMAS_PAGAMENTO['PAGAMENTO_MASTER_CREDITO']] ?>"
                                            onClick="<?php echo $onclick; ?>"
                                        >
                                    </div>
                                    <div class="col-xs-8 top20">
<?php
                                        if($liberado){
?>
                                            <p class="fontsize-pp bottom0">MASTERCARD - CRÉDITO</p>
<?php
                                            if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA && $PAGAMENTO_MASTER_CREDITO_TAXA != 0) {
                                                echo "<p class='txt-cinza fontsize-pp bottom0'>Taxa de serviço: R$ ".number_format($PAGAMENTO_MASTER_CREDITO_TAXA, 2, ',', '.')."</p>";
                                            }//end if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                                            else {
                                                echo "<p class='txt-vermelho fontsize-pp bottom0'>Sem taxa de serviço</p>";
                                            }//end else do if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
?>
                                            <p class="txt-verde fontsize-pp">Entrega em até 30 minutos</p>
                                            <span style='display:none'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_MASTER_CREDITO']?>" <?php if($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_MASTER_CREDITO']) echo " checked"; ?>></span>

<?php
                                        }else{
                                            echo $msg_bloqueia_Cielo;
                                        }
?>
                                        
                                    </div>
                                </div>
                            </div>
<?php
                            if($cont_colunas == 3){
                                echo "</div><div class='row espacamento'>";
                                $cont_colunas = 0;
                            }
/* 
?>
                        <?php
                        if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA && $PAGAMENTO_MASTER_CREDITO_TAXA != 0) {
                            echo "<p class='txt-cinza fontsize-pp bottom0'>Taxa de serviço: R$ ".number_format($PAGAMENTO_MASTER_CREDITO_TAXA, 2, ',', '.')."</p>";
                        }//end if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                        else {
                            echo "<p class='txt-vermelho fontsize-pp bottom0'>Sem taxa de serviço</p>";
                        }//end else do if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                        ?>
                        <p class="txt-verde fontsize-pp">Entrega em até 30 minutos</p>
<?php 
*/ 
?>
<?php 
                        }
/* 
?>
                        <img class="top5 c-pointer"
                        src="../pag/images/elo_debito.gif" 
                        name="btn_18" 
                        onMouseOver="document.btn_18.src='../pag/images/elo_debito_over.gif'" 
                        onMouseOut="document.btn_18.src='../pag/images/elo_debito.gif'" 
                        width="110" height="35" border="0" title="<?php echo $FORMAS_PAGAMENTO_DESCRICAO[$FORMAS_PAGAMENTO['PAGAMENTO_ELO_DEBITO']] ?>" onClick="save_shipping('<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_ELO_DEBITO'] ?>', <?php echo (($controller->usuario->getId()>0)?$controller->usuario->getId():"0") ?>, '<?php echo $controller->usuario->getNome() ?>')"><br>
                        <?php
                        if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA && $PAGAMENTO_ELO_DEBITO_TAXA != 0) {
                            echo "<p class='txt-cinza fontsize-pp bottom0'>Taxa de serviço: R$ ".number_format($PAGAMENTO_ELO_DEBITO_TAXA, 2, ',', '.')."</p>";
                        }//end if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                        else {
                            echo "<p class='txt-vermelho fontsize-pp bottom0'>Sem taxa de serviço</p>";
                        }//end else do if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                        ?>
                        <p class="txt-verde fontsize-pp">Entrega em até 30 minutos</p>
                        <span style='display:none'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_ELO_DEBITO']?>" <?php if($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_ELO_DEBITO']) echo " checked"; ?>></span>
<?php 
*/ 
                        if($libera_pagamento['Cielo_Elo_CRED']){
                            $cont_colunas++;
                            if($liberado){
                                $onclick = 'save_shipping(\''. $FORMAS_PAGAMENTO['PAGAMENTO_ELO_CREDITO'] . '\',' . (($controller->usuario->getId()>0)?$controller->usuario->getId():'0') . ',\'' . $controller->usuario->getNome() . '\');';
                            }else{
                                $onclick = '';
                            }
                            
?>
                            <div class="col-xs-12 col-md-4 mt-sm-15">
                                <div class="row">
                                     <div class="col-xs-4">
                                        <img 
                                            src="/imagens/pag/iconeElo.png" 
                                            class="c-pointer btnPgto" 
                                            style="width:100%; max-width: 100px;"
                                            name="btn_19"
                                            title="<?php echo $FORMAS_PAGAMENTO_DESCRICAO[$FORMAS_PAGAMENTO['PAGAMENTO_ELO_CREDITO']] ?>"
                                            onClick="<?php echo $onclick; ?>"
                                        >
                                    </div>
                                    <div class="col-xs-8 top20">
<?php
                                        if($liberado){
?>
                                            <p class="fontsize-pp bottom0">ELO - CRÉDITO</p>
<?php
                                            if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA && $PAGAMENTO_ELO_CREDITO_TAXA != 0) {
                                                echo "<p class='txt-cinza fontsize-pp bottom0'>Taxa de serviço: R$ ".number_format($PAGAMENTO_ELO_CREDITO_TAXA, 2, ',', '.')."</p>";
                                            }//end if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                                            else {
                                                echo "<p class='txt-vermelho fontsize-pp bottom0'>Sem taxa de serviço</p>";
                                            }//end else do if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
?>
                                            <p class="txt-verde fontsize-pp">Entrega em até 30 minutos</p>
                                            <span style='display:none'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_ELO_CREDITO']?>" <?php if($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_ELO_CREDITO']) echo " checked"; ?>></span>

<?php
                                        }else{
                                            echo $msg_bloqueia_Cielo;
                                        }
?>
                                        
                                    </div>
                                </div>
                            </div>

<?php 
                            if($cont_colunas == 3){
                                echo "</div><div class='row espacamento'>";
                                $cont_colunas = 0;
                            }
/* 
?>
                        <?php
                        if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA && $PAGAMENTO_ELO_CREDITO_TAXA != 0) {
                            echo "<p class='txt-cinza fontsize-pp bottom0'>Taxa de serviço: R$ ".number_format($PAGAMENTO_ELO_CREDITO_TAXA, 2, ',', '.')."</p>";
                        }//end if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                        else {
                            echo "<p class='txt-vermelho fontsize-pp bottom0'>Sem taxa de serviço</p>";
                        }//end else do if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                        ?>
                        <p class="txt-verde fontsize-pp">Entrega em até 30 minutos</p>
<?php
*/
?>
<?php
                        }

                        if($libera_pagamento['Cielo_Diners_CRED']){
                            $cont_colunas++;
                            if($liberado){
                                $onclick = 'save_shipping(\''. $FORMAS_PAGAMENTO['PAGAMENTO_DINERS_CREDITO'] . '\',' . (($controller->usuario->getId()>0)?$controller->usuario->getId():'0') . ',\'' . $controller->usuario->getNome() . '\');';
                            }else{
                                $onclick = '';
                            }
?>
                            <div class="col-xs-12 col-md-4 mt-sm-15">
                                <div class="row">
                                     <div class="col-xs-4">
                                        <img 
                                            src="/imagens/pag/iconeDiners.png" 
                                            class="c-pointer btnPgto" 
                                            style="width:100%; max-width: 100px;"
                                            name="btn_20"
                                            title="<?php echo $FORMAS_PAGAMENTO_DESCRICAO[$FORMAS_PAGAMENTO['PAGAMENTO_DINERS_CREDITO']] ?>"
                                            onClick="<?php echo $onclick; ?>"
                                        >
                                    </div>
                                    <div class="col-xs-8 top20">
<?php
                                        if($liberado){
?>
                                            <p class="fontsize-pp bottom0">DINERS - CRÉDITO</p>
<?php
                                            if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA && $PAGAMENTO_DINERS_CREDITO_TAXA != 0) {
                                                echo "<p class='txt-cinza fontsize-pp bottom0'>Taxa de serviço: R$ ".number_format($PAGAMENTO_DINERS_CREDITO_TAXA, 2, ',', '.')."</p>";
                                            }//end if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                                            else {
                                                echo "<p class='txt-vermelho fontsize-pp bottom0'>Sem taxa de serviço</p>";
                                            }//end else do if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
?>
                                            <p class="txt-verde fontsize-pp">Entrega em até 30 minutos</p>
                                            <span style='display:none'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_DINERS_CREDITO']?>" <?php if($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_DINERS_CREDITO']) echo " checked"; ?>></span>

<?php
                                        }else{
                                            echo $msg_bloqueia_Cielo;
                                        }
?>
                                        
                                    </div>
                                </div>
                            </div>

<?php 
                            if($cont_colunas == 3){
                                echo "</div><div class='row espacamento'>";
                                $cont_colunas = 0;
                            }
/*
?>
                        <?php
                        if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA && $PAGAMENTO_DINERS_CREDITO_TAXA != 0) {
                            echo "<p class='txt-cinza fontsize-pp bottom0'>Taxa de serviço: R$ ".number_format($PAGAMENTO_DINERS_CREDITO_TAXA, 2, ',', '.')."</p>";
                        }//end if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                        else {
                            echo "<p class='txt-vermelho fontsize-pp bottom0'>Sem taxa de serviço</p>";
                        }//end else do if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                        ?>
                        <p class="txt-verde fontsize-pp">Entrega em até 30 minutos</p>
<?php 
*/
?>
<?php
                        }

                        if($libera_pagamento['Cielo_Discover_CRED']){
                            $cont_colunas++;
                            if($liberado){
                                $onclick = 'save_shipping(\''. $FORMAS_PAGAMENTO['PAGAMENTO_DISCOVER_CREDITO'] . '\',' . (($controller->usuario->getId()>0)?$controller->usuario->getId():'0') . ',\'' . $controller->usuario->getNome() . '\');';
                            }else{
                                $onclick = '';
                            }
?>
                            <div class="col-xs-12 col-md-4 mt-sm-15">
                                <div class="row">
                                     <div class="col-xs-4">
                                        <img 
                                            src="/imagens/pag/iconeDiscover.png" 
                                            class="c-pointer btnPgto" 
                                            style="width:100%; max-width: 100px;"
                                            name="btn_21"
                                            title="<?php echo $FORMAS_PAGAMENTO_DESCRICAO[$FORMAS_PAGAMENTO['PAGAMENTO_DISCOVER_CREDITO']] ?>"
                                            onClick="<?php echo $onclick; ?>"
                                        >
                                    </div>
                                    <div class="col-xs-8 top20">
<?php
                                        if($liberado){
?>
                                            <p class="fontsize-pp bottom0">DISCOVER - CRÉDITO</p>
<?php
                                            if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA && $PAGAMENTO_DISCOVER_CREDITO_TAXA != 0) {
                                                echo "<p class='txt-cinza fontsize-pp bottom0'>Taxa de serviço: R$ ".number_format($PAGAMENTO_DISCOVER_CREDITO_TAXA, 2, ',', '.')."</p>";
                                            }//end if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                                            else {
											    //echo "<p class='txt-vermelho fontsize-pp bottom0'>ATENÇAO pix no itau está fora do ar</p>";
                                                echo "<p class='txt-vermelho fontsize-pp bottom0'>Sem taxa de serviço</p>";
                                            }//end else do if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
?>
                                            <p class="txt-verde fontsize-pp">Entrega em até 30 minutos</p>
                                            <span style='display:none'><input type="radio" name="pagto" value="<?php echo $FORMAS_PAGAMENTO['PAGAMENTO_DISCOVER_CREDITO']?>" <?php if($pagto == $FORMAS_PAGAMENTO['PAGAMENTO_DISCOVER_CREDITO']) echo " checked"; ?>></span>

<?php
                                        }else{
                                            echo $msg_bloqueia_Cielo;
                                        }
?>
                                        
                                    </div>
                                </div>
                            </div>

<?php
                            if($cont_colunas == 3){
                                echo "</div><div class='row espacamento'>";
                                $cont_colunas = 0;
                            }
                        }

//                        if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA && $PAGAMENTO_DISCOVER_CREDITO_TAXA != 0) {
//                            echo "<p class='txt-cinza fontsize-pp bottom0'>Taxa de serviço: R$ ".number_format($PAGAMENTO_DISCOVER_CREDITO_TAXA, 2, ',', '.')."</p>";
//                        }//end if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
//                        else {
//                            echo "<p class='txt-vermelho fontsize-pp bottom0'>Sem taxa de serviço</p>";
//                        }//end else do if($valor_tmp < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
?>
                        <!--<p class="txt-verde fontsize-pp">Entrega em até 30 minutos</p>-->
<?php 
                        
                    }//end if(($controller->usuario->b_IsLogin_pagamento_Cielo() && b_cielo_forma_pagamento() && PAGAMENTO_CIELO) && (!$b_bloqueia_Ongame) && $libera_pagamento['Cielo'] ) 
                    else {
                        echo "<p class='txt-azul fontsize-pp'>Serviço indisponível no momento</p>";
                    }
                    // Linha CIELO - fim
                    
                }// if(!$b_bloqueia_Ongame)
                    // Bloqueio Pagamento Ongame - fim 


            } //end if($controller->usuario->b_IsLogin_pagamento())
?>
                </div>

            </form>
<?php
            }//end else do if($msg != "")
			

	/*
		O código abaixo foi feito por Livrodjx, ele basicamente verifica se há um CPF e se esse CPF cruza com a tabela
		de pessoas politicamente expostas, se caso houver um registro ele envia um e-mail que houve uma tentativa de compra.
		
		Algumas variáveis tem a extensão _djx apenas para não haver conflito com o resto do código
	*/

if(isset($cpf)) {
	$sql_djx = "SELECT * FROM pep WHERE cpf = $cpf and enviado_email = 0;";
	$rs_djx = SQLexecuteQuery($sql_djx);
	$dadosTotais_djx = pg_fetch_all($rs_djx);
	$count_registers = pg_num_rows($rs_djx);
	
	if($count_registers > 0) {
		
		/*$sql_testa_pdv = "SELECT * FROM dist_usuario_games where ug_id = ".$controller->usuario->getId().";";
		$rs_djx2 = SQLexecuteQuery($sql_testa_pdv);
		$dados_pdv_djx = pg_fetch($rs_djx2);
		$count_pdv_registers = pg_num_rows($rs_djx2);
		
		$cabecalho_adicional_caso_pdv = "";
		
		$cabecalho_adicional_caso_pdv = " ATRAVÉS DE PDV (ID ".$dados_pdv_djx['ug_id']." )";
		
		if($count_pdv_registers > 0) {
		}*/
		
		$cabecalho = "
		<h2 style='text-align:center;border:solid 1px black;width:90%;margin:0 auto;display:block;margin-top:40px;'>PEP (".$dadosTotais_djx[0]['nome']." - " .$dadosTotais_djx[0]['cpf']." - ".$dadosTotais_djx[0]['descricao_funcao'].") Tentando Realizar Compra".$controller->usuario->getId()."</h2>
		<table style='padding:20px;background-color:#ddd;margin: 0 auto;width: 90%; color: #222' class='table table-bordered top20'><thead class=''>
		  
			<tr>
				<th style='padding:5px;'>Produto</th>
				<th style='padding:5px;'>I.O.F.</th>
				<th style='padding:5px;'>Valor unitário</th>
				<th style='padding:5px;'>Qtde</th>
				<th style='padding:5px;'>Total</th>
			</tr>
		</thead>
		<tbody title='Nome Encontrado' style='text-align: center'>
		";

		$exibicaoDadosProblemas = "";
		
		$total_geral = 0;
		
		foreach ($carrinho as $modeloId => $qtde){
			if($modeloId !== $NO_HAVE) {
				$qtde = intval($qtde);
				$rs = null;
				$filtro['ogpm_ativo'] = 1;
				$filtro['ogpm_id'] = $modeloId;
				$filtro['com_produto'] = true;
				
				if(isset($GLOBALS['_SESSION']['usuarioGames_ser'])) {
					if($controller->usuario->b_IsLogin_pagamento_usa_produto_treinamento()) {
						$filtro['show_treinamento'] = 1;
					}
				}
				$instProdutoModelo = new ProdutoModelo;
				$ret = $instProdutoModelo->obter($filtro, null, $rs);
				
				$rs_row_djx = pg_fetch_array($rs);
				$total_geral += $rs_row_djx['ogpm_valor'];
				$total_geral_pin_epp_cash  += $rs_row_djx['ogpm_valor_eppcash'] * $qtde;
				$instProduto = new Produto;
				$iof = $instProduto->buscaIOF($modeloId) ? "Incluso" : "";
					
				$exibicaoDadosProblemas .= "
				<tr class='trListagem'>
					
					<td style='padding: 5px'> ".$rs_row_djx['ogp_nome']." - " .$rs_row_djx['ogpm_nome'] ."</td>
					   
					<td style='padding: 5px'>$iof</td>
						
					<td style='padding: 5px'>R$ ".number_format($rs_row_djx['ogpm_valor'], 2, ',', '.')."</td>
					
					<td style='padding: 5px'> ".htmlspecialchars($qtde, ENT_QUOTES) ."</td>
					
					<td style='padding: 5px'> R$ ".number_format($rs_row_djx['ogpm_valor']*$qtde, 2, ',', '.') ."</td>
				</tr>";
					
				
			}
			
		}
		$exibicaoDadosProblemas .= "
			<tr>
				<td colspan='2'></td>
				<td colspan='2'></td>
				<td style='border-top: 1px solid #222'> R$ ".number_format($total_geral,2, ',', '.')."</td>
			</tr>
		";
		// Dados do Email
		$email  = "rc@e-prepag.com.br,rc1@e-prepag.com.br,lucas.alexandre@gokeitecnologia.com.br"; //andresilva@gokeitecnologia.com.br,rc@e-prepag.com.br,rc1@e-prepag.com.br,
		$cc     = "glaucia@e-prepag.com.br";
		$subject= "Tentativa de Compra PEP";

		$msg = $cabecalho.$exibicaoDadosProblemas."</table>";
		 
		if(enviaEmail($email, $cc, $bcc, $subject, $msg)) { 
			//echo "Email enviado com sucesso".PHP_EOL;
		}
		else {
			/*echo "Problemas no envio do Email".PHP_EOL." TO: ".$email."".PHP_EOL." CC: ".$cc."".PHP_EOL." BCC: ".$bcc."".PHP_EOL." SUBJECT: ".$subject."".PHP_EOL;*/
		}
	}
}
else {
	echo "CPF vazio";
}

?>            
        </div>
    </div>
</div>
</div>
                        
<?php
require_once DIR_WEB . "game/includes/footer.php";
?>