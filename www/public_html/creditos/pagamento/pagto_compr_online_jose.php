<?php 



require_once "../../../includes/constantes.php";
require_once DIR_INCS . "configIP.php";
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "pdv/main.php";
require_once DIR_CLASS . "pdv/classOperadorGamesUsuario.php";
require_once DIR_INCS . "inc_register_globals.php";

require_once DIR_CLASS . "pdv/controller/ProdutosController.class.php";

$qtdFeedsIndex = 5;
$controller = new ProdutosController;

require_once DIR_INCS . "pdv/venda_e_modelos_logica.php";

if(!isset($_SESSION['pagamento.numorder'])){
    header("Location: /creditos/index.php");
    exit;
}

//Recupera usuario
if(isset($_SESSION['dist_usuarioGames_ser']) && !is_null($_SESSION['dist_usuarioGames_ser'])){
        $dist_usuarioGames = unserialize($_SESSION['dist_usuarioGames_ser']);
        $dist_usuarioId = $dist_usuarioGames->getId();
}

$rs_venda_row = pg_fetch_array($rs_venda);
$pagto_tipo	 = $rs_venda_row['vg_pagto_tipo'];
$iforma = $pagto_tipo; //$_SESSION['pagamento.pagto'];
$ultimo_status	= $rs_venda_row['vg_ultimo_status'];

$sql  = "select * from tb_pag_compras " .
                "where idvenda = " . $venda_id . " and idcliente=" . $usuarioId;
$rs_pagto = SQLexecuteQuery($sql);
if(!$rs_pagto || pg_num_rows($rs_pagto) == 0) {
        $msg = "Não foi encontrado o pagamento para a venda ".$venda_id.".\n";
} else {
        $rs_pagto_row = pg_fetch_array($rs_pagto);
        $banco = $rs_pagto_row['banco'];
        $assinatura = $rs_pagto_row['assinatura'];
        $total_pag =  $rs_pagto_row['total']/100;
}
                
if(!b_IsPagtoOnline($pagto_tipo)) {
        $strRedirect = "/creditos/pedidos.php";
        die("redirect");
        //redirect($strRedirect);
}

// recupera numorder
$OrderId = $_SESSION['pagamento.numorder'];
$total_carrinho = $_SESSION['dist_pagamento.total'];
$taxas = $_SESSION['dist_pagamento.taxa'];
$total_pag = $total_carrinho+$taxas;
$total_geral = $total_pag;

unset($_SESSION['debug_pagto_online']);

// Insere os arquivos de URL de cada banco
if(($pagto_tipo == $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) || ($pagto_tipo == $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO']) || ($pagto_tipo == $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_CREDITO'])  ) {
        require_once RAIZ_DO_PROJETO . "banco/bradesco/inc_functions.php";
        require_once RAIZ_DO_PROJETO . "banco/bradesco/inc_urls_bradesco.php";
        require_once RAIZ_DO_PROJETO . "banco/bradesco/config.inc.bradesco_transf.php";
} else if($pagto_tipo == $FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']) {
        require_once RAIZ_DO_PROJETO . "banco/bancodobrasil/inc_urls_bancodobrasil.php";
} else if($pagto_tipo == $PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC) { //$FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE']

        // Recupera Itau ID
        $sql  = "select * from tb_pag_compras where idvenda = " . $venda_id . " and idcliente=" . $usuarioId;
        $rs_pagto_id = SQLexecuteQuery($sql);
        if(!$rs_pagto_id || pg_num_rows($rs_pagto_id) == 0) {
                $msg = "Não foi encontrado o pagamento para a venda ".$venda_id.".\n";
        } else {
                $rs_pagto_id_row = pg_fetch_array($rs_pagto_id);
                $id_transacao_itau = $rs_pagto_id_row['id_transacao_itau'];
        }

        require_once RAIZ_DO_PROJETO . "banco/itau/inc_config.php"; 
        require_once RAIZ_DO_PROJETO . "banco/itau/inc_urls_bancoitau.php";
}

$numOrder = $OrderId;

// Redireciona para a página final no site do Banco
if($iforma==$FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) {
        $simg_bank = "<img src='/imagens/pag/bradesco_horiz_peq.jpg' width='128' height='35' border='0' title='Bradesco'>";
} else if($iforma==$FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO']) {
        $location = $link_PagtoFacil;
        $simg_bank = "<img src='/imagens/pag/bradesco_horiz_peq.jpg' width='128' height='35' border='0' title='Bradesco'>";
} else if($iforma==$FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']) {
        $location = $link_BBDebito;
        $simg_bank = "<img src='/imagens/pag/bb_logo_dr.gif' border='0' title='Banco do Brasil'>";
} else if($iforma==$PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC) { //$FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE']
        $location = $link_BItauShopline;
        $simg_bank = "<img src='/imagens/pag/Itau_logo_loja.jpg' width='116' height='43' border='0' title='Banco Itaú Shopline'>";
} else if($iforma==$PAGAMENTO_PIX_NUMERIC) {
        $simg_bank = "<img src='/imagens/pag/iconePIX.png' width='116' border='0' title='Pagamento PIX' class='top40'>";
} else {
        $location = $link_error;
        $simg_bank = "";
}


$pagina_titulo = "Comprovante";
//include "../includes/cabecalho.php";
require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/header.php";

if($ultimo_status == $GLOBALS['STATUS_VENDA']['PEDIDO_EFETUADO']) 
{ 
?>

<!--Link para a biblioteca jquery-->
<script language="JavaScript">
function abrePaginaBanco() {
        $("#vaipagamento").click();
}

function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}

function IrAoBanco() {
        $("#btnIrAoBanco").click();
}
function ShowPopupWindowXY(fileName) {
        myFloater = window.open(fileName,'myWindow','scrollbars=yes,status=no,width=' +(0.8*screen.width) +',height='+(0.8*screen.height) +',top='+(0.1*screen.height) +',left='+(0.1*screen.width)+'');
}

var refresh_snipet = 1;
// Mostra status da compra
function refresh_status(){
    $.ajax({
            type: "POST",
            url: "/ajax/pdv/ajax_info_pagamento.php",	
            data: "numcompra=<?php echo $numOrder; ?>",
            success: function(txt){
                $("#info_pagamento").html(txt);

                if(refresh_snipet==0) {
                        clearInterval(refreshIntervalId);
                }
            },
            error: function(){
                    $("#info_pagamento").html("");
                    //alert("Erro no servidor, por favor tente mais tarde.");
            }
    });
}		

var refreshIntervalId = setInterval(refresh_status, 5000);
</script>

<?php } ?>
<div class="container txt-azul-claro bg-branco">
    <div class="col-md-10">
        <div class="row">
            <div class="col-md-12 espacamento">
                <strong>ADICIONAR SALDO</strong>
            </div>
        </div>
        <div class="row txt-cinza">
            <div class="col-md-12 espacamento">
                <strong>Número do depósito: <span class="txt-verde"><?php echo $venda_id;?></span><br>
                Valor selecionado: <span class="txt-verde">R$ <?php echo number_format($_SESSION['dist_pagamento.total'], 2, ',','.');?></span></strong>
            </div>
        </div>
    </div>
    <div class="borda-top-azul col-md-12 espacamento"></div>
    <div class="row col-md-offset-2 col-md-8">
    <?php 
    if(($ultimo_status == $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO']) || ($ultimo_status == $GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO']) || ($ultimo_status == $GLOBALS['STATUS_VENDA']['PROCESSAMENTO_REALIZADO']))
    { 
    ?>
        <table  border="0" cellspacing="0" width="100%">
            <tr>
                <td class="texto" align="center" height="25">
                    <br>
                    Sua recarga de saldo está quase finalizada.<br><br>
                    Após o pagamento o saldo será atualizado automaticamente.
                </td>
            </tr>
        </table>

        <table border="0" cellspacing="0" width="100%">
            <tr><td>&nbsp;</td></tr>
            <tr>
                <td align="center" class="texto">
                    <input type="button" name="btOK" value="Clique aqui para emitir o Boleto Bancário" OnClick="fcnJanelaBoleto();" class="botao_simples">
                </td>
            </tr>
            <tr><td>&nbsp;</td></tr>
            <tr>
                <td align="center" class="texto">&nbsp;</td>
            </tr>
        </table>
    <?php 
    } else if($ultimo_status == $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA'])
    { 
    ?>
        <table  border="0" cellspacing="0" bgcolor="#FFFFFF" width="100%">
            <tr>
              <td class="texto" align="center" height="25">
                    <br>
                    Sua transação de depósito foi concluída com sucesso.<br><br>
                    Seu saldo em conta será atualizado automaticamente.</td>
            </tr>
        </table>
    <?php 
        if (($banco=="237") && ($assinatura)) 
        { 
    ?>
        <center>
            <table border="0" cellspacing="0" align="center"> 
                <tr bgcolor="F0F0F0"> 
                    <td class="texto" align="center" height="25"><b>Autenticação</b></td> 
                </tr> 
                <tr bgcolor="F0F0F0"> 
                    <td class="texto" align="center"><?php formataAssinatura($assinatura) ?></td> 
                </tr> 
            </table>
        </center>

    <?php 
        }
    } else if($ultimo_status == $GLOBALS['STATUS_VENDA']['VENDA_CANCELADA'])
    { 
    ?>
        <table  border="0" cellspacing="0" bgcolor="#FFFFFF" width="100%">
            <tr>
              <td class="texto" align="center" height="25">
                <br>
                Seu pedido de depósito foi cancelado pelo sistema.<br><br>
                Por favor tente um novo pedido de depósito ou então entre em contato com nosso suporte.</td>
            </tr>
        </table>
    <?php 
    } else 
    { //	apenas $GLOBALS['STATUS_VENDA']['PEDIDO_EFETUADO']?>
        <center>
        <table cellspacing="0" cellpadding="0" width="90%">
            <tr>
                <td align="center">
                    <div id="link_bank">		<?php // bloco pagamento inicio ?>
                        <table border="0" cellspacing="0" width="90%">
                            <tr>
                              <td class="texto" height="10"></td>
                            </tr>
                            <tr>
                                <td class="texto" height="25" align="center">
                                    <table border="0" cellspacing="0" width="90%">
                                        <tr>
                                            <td class="texto" align="left" width="30%"><?php echo $simg_bank; ?></td>
                                            <td align="center" class="texto" align="center" height="100">
                                                    <div id="info_pagamento">&nbsp;</div>
<?php
                                        if($pagto_tipo == $PAGAMENTO_PIX_NUMERIC){ 
										        $sql  = "select nome from provedor_pix where ativo = 'A';";
												$ativo = SQLexecuteQuery($sql);
												$ativoNome = pg_fetch_assoc($ativo);
												/*																				
												if($_SERVER["REMOTE_ADDR"] == "201.93.162.169"){	
													//ini_set('display_errors', 1);
													//ini_set('display_startup_errors', 1);
													//error_reporting(E_ALL);
												}
												*/
												function tirarAcentos($string){//Declara a função e recebe o parâmetro $string.
													//Abaixo é usado str_replace em cada vogal ou consuante com acento que será retirado o acento.
													//Além de retirar o acento, retorna a informação na mesma variável $string.
													$string = str_replace('ã', 'a', $string);
													$string = str_replace('á', 'a', $string);
													$string = str_replace('Ã', 'A', $string);
													$string = str_replace('Á', 'A', $string);
													$string = str_replace('ç', 'c', $string);
													$string = str_replace('Ç', 'C', $string);
													$string = str_replace('?', 'e', $string);
													$string = str_replace('é', 'e', $string);
													$string = str_replace('?', 'E', $string);
													$string = str_replace('É', 'E', $string);
													$string = str_replace('í', 'i', $string);
													$string = str_replace('Í', 'I', $string);
													$string = str_replace('ó', 'o', $string);
													$string = str_replace('õ', 'O', $string);
													$string = str_replace('Õ', 'O', $string);
													$string = str_replace('Ó', 'O', $string);
													$string = str_replace('Ú', 'U', $string);
													$string = str_replace('ú', 'u', $string);
													//No final retorna a variável com o texto sem acento.
													return $string;
												}
												
												if(!defined("PAGAMENTO_PIX_CHAVEAMENTO")) {
													include "/www/includes/config.MeiosPagamentos.php"; 
												}
																		
												if(true) {
												   if(number_format(($total_carrinho+$taxas),2,'.','') > 0.00){
										                $ativoNome["nome"] = "asaas";
													}else{	
														$ativoNome["nome"] = "mercadopago";
													}
                                                    require_once RAIZ_DO_PROJETO.'banco/pix/'.$ativoNome["nome"].'/config.inc.pix.php'; 
													//classe para pagamento em pix usada atualmente
													if($ativoNome["nome"] == "asaas"){
														$pix = new classPIX(); 
                                                        $params = array (
															'metodo'    => PIX_REGISTER,
															'cpf_cnpj'  => str_replace('-', '', str_replace('.', '', $usuarioGames->ug_sCNPJ)),
															'nome'      => tirarAcentos($usuarioGames->ug_sRazaoSocial),
															'valor'     => number_format(($total_carrinho+$taxas),2,'.',''),
															'descricao' => "E-PREPAG",
															'idpedido'  => $ARRAY_CONCATENA_ID_VENDA['pdv'].$_SESSION['pagamento.numorder'],
															'venda_id' => $venda_id,
                                                            'email'    => $usuarioGames->ug_sEmail
														); 
                                                        echo $pix->callService($params);
                                                        echo "asaas";
													}else if($ativoNome["nome"] == "mercadopago")
                                                    {
                                                        $pix = new classPIX(); 
                                                        $params = array (
															'metodo'    => PIX_REGISTER,
															'cpf_cnpj'  => str_replace('-', '', str_replace('.', '', $usuarioGames->ug_sCNPJ)),
															'nome'      => tirarAcentos($usuarioGames->ug_sRazaoSocial),
															'valor'     => number_format(($total_carrinho+$taxas),2,'.',''),
															'descricao' => "E-PREPAG",
															'idpedido'  => $ARRAY_CONCATENA_ID_VENDA['pdv'].$_SESSION['pagamento.numorder'],
															'venda_id' => $venda_id,
                                                            'email'    => $usuarioGames->ug_sEmail
														); 
                                                        echo $pix->callService($params);
                                                        echo "mercado livre";
                                                    }
                                                    else{
														echo "Pix não disponível no momento.";
													}
												}else{
													require_once RAIZ_DO_PROJETO.'banco/pix/asaas/config.inc.pix.php'; 
													//classe para pagamento em pix usada atualmente
													if($ativoNome["nome"] == "blupay"){
														$pix = new classPIX(); 
														//Bloco que vai para página de checkout
														$params = array (
															'metodo'    => PIX_REGISTER,
															'cpf_cnpj'  => str_replace('-', '', str_replace('.', '', $usuarioGames->ug_sCNPJ)),
															'nome'      => tirarAcentos($usuarioGames->ug_sRazaoSocial),
															'valor'     => number_format(($total_carrinho+$taxas),2,'.',''),
															'descricao' => "E-PREPAG",
															'idpedido'  => $ARRAY_CONCATENA_ID_VENDA['pdv'].$_SESSION['pagamento.numorder'],
															'venda_id' => $venda_id
														); 
														echo $pix->callService($params);
													}else if($ativoNome["nome"] == "mercadopago")
                                                    {
                                                        $pix = new classPIX(); 
                                                        $params = array (
															'metodo'    => PIX_REGISTER,
															'cpf_cnpj'  => str_replace('-', '', str_replace('.', '', $usuarioGames->ug_sCNPJ)),
															'nome'      => tirarAcentos($usuarioGames->ug_sRazaoSocial),
															'valor'     => number_format(($total_carrinho+$taxas),2,'.',''),
															'descricao' => "E-PREPAG",
															'idpedido'  => $ARRAY_CONCATENA_ID_VENDA['pdv'].$_SESSION['pagamento.numorder'],
															'venda_id' => $venda_id,
                                                            'email'    => $usuarioGames->ug_sEmail
														); 
                                                        echo $pix->callService($params);
                                                    }
                                                    else{
														echo "Pix não disponível no momento.";
													}
												}
																						
												/*$ff = fopen("/www/log/disparo.txt","a+");
												fwrite($ff, PIX_REGISTER."\r\n");
												fwrite($ff, str_replace('-', '', str_replace('.', '', $usuarioGames->ug_sCNPJ))."\r\n");
												fwrite($ff, $usuarioGames->ug_sRazaoSocial."\r\n");
												fwrite($ff, number_format(($total_carrinho+$taxas),2,'.','')."\r\n");
												fwrite($ff, RAZAO_EMPRESA."\r\n");
												fwrite($ff, $ARRAY_CONCATENA_ID_VENDA['pdv'].'0000000'.$_SESSION['pagamento.numorder']."\r\n");
												fwrite($ff, "***************************************************\r\n");
												
												fclose($ff);*/
                                                //var_dump($params); die();
?>
                                                <script language='javascript'>
                                                    $("#info_pagamento").hide();
                                                </script>                                                
<?php                                                
                                        } //end else if($pagto_tipo == $PAGAMENTO_PIX_NUMERIC)

                                        
?>                                                    
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
    <?php
            // "Clique no botão "Ir ao Banco" para efetuar o pagamento.<br>"	  
    ?>
                            <tr align="center" class="texto">
                                <table border="0" cellspacing="0" width="90%">
                                    <tr>
                                        <td colspan="3" align="center" width="33%">
    <?php 
                                        if(($pagto_tipo == $FORMAS_PAGAMENTO['TRANSFERENCIA_ENTRE_CONTAS_BRADESCO']) || ($pagto_tipo == $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_DEBITO']) || ($pagto_tipo == $FORMAS_PAGAMENTO['PAGAMENTO_FACIL_BRADESCO_CREDITO']) ) 
                                        {
                                            $cesta = $rs_pagto_row['cesta'];
                                            $numcompra = $rs_pagto_row['numcompra'];

                                            if(strpos($cesta, "\n")){
                                                $cesta_desc = explode("\n", $cesta);

                                                $cesta_descricao = "";
                                                $pattern = "/item:/";
                                                foreach ($cesta_desc as $i => $prod){
                                                    if(preg_match($pattern, $prod)){
                                                        $cesta_descricao .= $prod;
                                                    }
                                                }
                                            } else{
                                                $cesta_descricao = $cesta;
                                            }
                                            
                                            $obj_pagamento = new classBradescoTransferencia();
    
                                            $array_infos_ws = $obj_pagamento->montaVetorInformacoes($dist_usuarioGames, $total_carrinho, $numcompra, trim($cesta_descricao), TRUE);

                                            if(is_null($array_infos_ws)){
                                                $titulo = "Sessão expirada";
                                                $msg_problem = "Sua sessão expirou!";
                                            } else{                       
                                                $comunica = $obj_pagamento->Req_EfetuaConsultaURL($array_infos_ws, $lista_resposta);

                                                if(is_null($comunica)){
                                                    $titulo = "ERRO - Problema na validação de seus dados";
                                                    $msg_problem = "Problema ao validar seus dados cadastrados! Por favor, relate o problema ao Suporte";
                                                } else{
                                                    if(is_array($comunica)){
                                                        $titulo = "ERRO - Problema de comunicação com o Bradesco";
                                                        $msg_problem = "Houve um problema de comunicação com o Bradesco! Tente novamente mais tarde. Obrigado!";
                                                    } else{
                                                        $location = $comunica;
                                                    }
                                                }
                                            }

                                            if(isset($msg_problem)){
                                                
?>
                                                <div class="col-md-12 top10 col-sm-12 col-xs-12">
                                                    <p class="txt-vermelho"><?php echo $msg_problem;?></p>
                                                </div>    

                                                <link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
                                                <link href="/css/creditos.css" rel="stylesheet" type="text/css" />
                                                <script type="text/javascript" src="/js/jquery.js"></script>
                                                <script src="/js/jqueryui/js/jquery-ui-1.9.2.custom.min.js"></script>
                                                <script type="text/javascript" src="/includes/bootstrap/js/bootstrap.min.js"></script>
                                                <!-- Modal -->
                                                <div id="modal-problema-comunicacao" class="modal fade text-left" data-backdrop="static" role="dialog">
                                                    <div class="modal-dialog">
                                                        <!-- Modal content-->
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                              <h4 class="modal-title txt-vermelho"><?php echo $titulo;?></h4>
                                                            </div>
                                                            <div class="modal-body alert alert-danger">
                                                                <div class="form-group top10">
                                                                    <p><?php echo $msg_problem;?></p>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <script language='javascript'>
                                                    $("#info_pagamento").hide();
                                                    $("#modal-problema-comunicacao").modal();
                                                </script>
<?php 
                                                die();
                                            } else{
?>
                                                <form action="" method="post" target="_blank">
                                                    <input class="btn btn-success top50" type="button" name="btnIrAoBanco" value="Clique aqui para pagar" onclick="window.open('<?php echo $location; ?>')" class="int-btn1 grad1 int-pagamento-compr-online-btn1">
                                                </form>
<?php
                                            }
                                        } 
                                        else if($pagto_tipo == $FORMAS_PAGAMENTO['PAGAMENTO_BB_DEBITO_SUA_CONTA']) 
                                        { 
    ?>
                                            <form action="<?php echo $location; // "../debug.php" ?>" method="post" target="_blank">
                                                <input type="submit" name="btnIrAoBanco" value="Clique aqui para pagar" class="btn btn-success">
                                                <input type="hidden" name="idConv" value="<?php echo $bbr_idConv ; ?>">
                                                <input type="hidden" name="refTran" value="<?php echo $bbr_refTran ; ?>">
                                                <input type="hidden" name="valor" value="<?php echo $bbr_valor ; ?>">
                                                <input type="hidden" name="qtdPontos" value="<?php echo $bbr_qtdPontos ; ?>">
                                                <input type="hidden" name="dtVenc" value="<?php echo $bbr_dtVenc ; ?>">
                                                <input type="hidden" name="tpPagamento" value="<?php echo $bbr_tpPagamento ; ?>">
                                                <input type="hidden" name="urlRetorno" value="<?php echo $bbr_urlRetorno ; ?>">
                                                <input type="hidden" name="urlInforma" value="<?php echo $bbr_urlInforma ; ?>">
                                                <input type="hidden" name="nome" value="<?php echo $bbr_nome ; ?>">
                                                <input type="hidden" name="endereco" value="<?php echo $bbr_endereco ; ?>">
                                                <input type="hidden" name="cidade" value="<?php echo $bbr_cidade ; ?>">
                                                <input type="hidden" name="uf" value="<?php echo $bbr_uf ; ?>">
                                                <input type="hidden" name="cep" value="<?php echo $bbr_cep ; ?>">
                                                <input type="hidden" name="msgLoja" value="<?php echo $bbr_msgLoja ; ?>">
                                            </form>
    <?php 
                                        } 
                                        else if($pagto_tipo == $PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC)
                                        { // $FORMAS_PAGAMENTO['PAGAMENTO_BANCO_ITAU_ONLINE'] 
                                            $cripto = new Itaucripto();
            
                                            $dados_cripto = $cripto->geraDados($codEmp,$pedido,$valorAux,$observacao,$chave,$nomeSacado,$codigoInscricao,$numeroInscricao,$enderecoSacado,$bairroSacado,$cepSacado,$cidadeSacado,$estadoSacado,$dataVencimento,$urlRetorna,$ObsAdicional1,$ObsAdicional2,$ObsAdicional3);
//-----------------------------------------------------------------------------------------------------------------------------------------------------
//MODO UTILIZANDO ASP - DESCOMENTAR CASO UTILIZAR ASP                                        
//                                            $dados = getItauCrypto($form_fields, "pagto");
//                                            $aretorno = explode("\n", $dados);
//                                            $dados_cripto = $aretorno[9];
//-----------------------------------------------------------------------------------------------------------------------------------------------------
    ?>
                                            <form action="<?php echo $location; ?>" method="post" target="_blank">
                                                <input type="hidden" name="DC" value="<?php echo $dados_cripto ?>">
                                                <input type="submit" name="btnIrAoBanco" value="Clique aqui para pagar" class="btn btn-success">
                                            </form>
    <?php 
                                        }
    ?>
                                        </td>
                                    </tr>
                                    <tr><td colspan="3" align="center" width="33%">&nbsp;</td></tr>
                                    <tr bgcolor="ffffff">
                                        <td align="center" colspan="3" class="texto"><?php echo ($pagto_tipo == $PAGAMENTO_PIX_NUMERIC)?"<b>ATENÇÃO: Não efetue o Pix fora do nosso site. Para cada pagamento será necessário gerar um novo pedido.</b>":"Os seus dados serão fornecidos com toda segurança apenas ao seu banco."; ?></td>
                                    </tr>
                                </table>
                            </tr>
                            <tr bgcolor="ffffff">
                                <td align="center">&nbsp;</td>
                            </tr>
                            
                        </table>
                        <br>
                    </div>	<?php // bloco pagamento fim  ?>
                    <div id="pagamento_ok" class="col-md-12 h-footer">	<?php // bloco pagamento efetuado inicio ?>
                        <center>
                        Sua transação de depósito foi concluída com sucesso.<br>
                        Seu saldo em conta será atualizado automaticamente.<br>
                        <br>
                        <!--//voltar-->
                        </center>
                    </div>							<?php // bloco pagamento efetuado fim  ?>
                    <div id="pagamento_cancela" class="col-md-12 h-footer">	<?php // bloco pagamento cancelado inicio ?>
                        <center>
                        Seu pedido de depósito foi cancelado pelo sistema.<br>
                        Por favor tente um novo pedido de depósito ou então entre em contato com nosso suporte.<br>
                        <br>
                        <!--//voltar-->
                        </center>
                    </div>							<?php // bloco pagamento cancelado fim  ?>
                </td>
            </tr>
        </table>
        </center>
    </div>
    <script language="JavaScript" type="text/JavaScript">
    $("#pagamento_ok").hide();
    $("#pagamento_cancela").hide();
	
	$(".col-pix").removeClass("col-md-7");
	$(".col-pix").addClass("col-md-12");
	$("#img-pix").css("float","none");
	
    </script>
<?php } ?>
<br>
<br>
</div>
<?php 
require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/footer.php";
unset($_SESSION['pagamento.numorder']);
?>
