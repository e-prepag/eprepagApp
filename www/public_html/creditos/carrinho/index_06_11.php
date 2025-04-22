<?php
//error_reporting(E_ALL);
require_once "../../../includes/constantes.php";
require_once DIR_CLASS . "pdv/controller/CarrinhoController.class.php";

$produto_idade_minima = "";
$controller = new CarrinhoController;

$carrinho = $controller->confirmarCompra();
$modelos = $controller->getCarrinho($_SESSION['dist_carrinho']);
$mostra_modal_erros = 0;
$travaQtdeProduto = false;

/*

	PARA ELIMINAR TRAVA / LIMITE
	
	Substituir o ID da e-prepagTESTES pelo ID do cliente
	
	14549 -> Rei dos Coins
	17371 -> e-prepagTESTES

*/

if(!isset($_SESSION["seg_ip"]) || $_SESSION["seg_ip"] === false){
	header("location: /creditos/chave.php");
	exit;
}

if(!isset($_SESSION['dist_usuarioGamesOperador_ser'])){
	$pdvInfo = unserialize($_SESSION["dist_usuarioGames_ser"]);
	$usuario = $pdvInfo->ug_id;
}else{
	$pdvInfo = unserialize($_SESSION['dist_usuarioGamesOperador_ser']);
	$usuario = $pdvInfo->ugo_ug_id; //    ugo_id
}

$sql = 'select ug_vip from dist_usuarios_games where ug_id = '.$usuario;
$rs_vip = SQLexecuteQuery($sql);
$retornoVip = pg_fetch_assoc($rs_vip);

switch($retornoVip["ug_vip"]){
	case "0":
		$quantideProdutos = $GLOBALS['RISCO_LANS_PRE_QTDE_PRODUTO'];
		$quantideModelos = $GLOBALS['RISCO_LANS_PRE_QTDE_MODELO'];   
	break;
	case "1":
		$quantideProdutos = $GLOBALS['RISCO_LANS_PRE_VIP_QTDE_PRODUTO'];
		$quantideModelos = $GLOBALS['RISCO_LANS_PRE_VIP_QTDE_MODELO'];
	break;
	case "2":
		$quantideProdutos = $GLOBALS['RISCO_LANS_PRE_MASTER_QTDE_PRODUTO'];
		$quantideModelos = $GLOBALS['RISCO_LANS_PRE_MASTER_QTDE_MODELO'];
	break;
	case "3":
		$quantideProdutos = $GLOBALS['RISCO_LANS_PRE_BLACK_QTDE_PRODUTO'];
		$quantideModelos = $GLOBALS['RISCO_LANS_PRE_BLACK_QTDE_MODELO'];
	break;
	case "4":
		$quantideProdutos = $GLOBALS['RISCO_LANS_PRE_GOLD_QTDE_PRODUTO'];
		$quantideModelos = $GLOBALS['RISCO_LANS_PRE_GOLD_QTDE_MODELO'];
	break;
	case "5":
		$quantideProdutos = true;
		$quantideModelos = true;
	break;
	default:
		$quantideProdutos = 2;
		$quantideModelos = 10;
	break;
}

// VALIDA QUANTIDADE DE PRODUTO NO CARRINHO
if ($controller->usuarios->getId() != 14549) {
	if($modelos["qtde_total_produtos"] > $quantideProdutos && $retornoVip["ug_vip"] != 5) {
		$mostra_modal_erros++;
		$travaQtdeProduto = true;
	}
}

require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/header.php";
require_once '/www/includes/pdv/functions.php';
require_once "/www/db/connect.php"; 
require_once "/www/db/ConnectionPDO.php"; 
$connection = ConnectionPDO::getConnection()->getLink();	

?>
<!-- script src="https://www.google.com/recaptcha/api.js" async="" defer=""></script> -->
<script src="https://js.hcaptcha.com/1/api.js" async defer></script>
<div id="modal-error" class="modal fade" role="dialog">
    <div class="modal-dialog modal-md" role="document">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h4 class="modal-title txt-vermelho"><strong>Quantidade não permitida.</strong></h4>
            </div>
            <div class="modal-body alert alert-danger txt-vermelho">
			    <?php if($travaQtdeProduto != true){ ?>
                    <p>Quantidade de produtos ultrapassou o limite máximo.<br>A quantidade máxima permitida por produto é de <?php echo $quantideModelos; ?> unidades.</p>
				<?php }else{ ?>
				    <p>Quantidade de produtos ultrapassou o limite máximo.<br>A quantidade máxima permitida de produtos é <?php echo $quantideProdutos; ?>.</p>
				<?php } ?>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
<div class="container txt-azul-claro bg-branco">
<?php
    if($carrinho)
    {
?>
    <div class="row">
   
</div>
        <div class="col-md-10">
            <div class="row">
                <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12 espacamento">
                    <strong>Produtos selecionados</strong>
                </div>
            </div>
            <div class="txt-cinza">
                <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12 bg-cinza-claro">
<?php                                
            if(is_array($modelos) && !empty($modelos)) {
                $teste_facilitadora = false;
                foreach($modelos as $modelo){
                    if(is_array($modelo) && $modelo['modelo'] instanceof ProdutoModelo)
                    {
                        $sql = "select 
                                    opr_codigo
                                from operadoras
                                where 
                                opr_vinculo_empresa = ".$GLOBALS['IDENTIFICACAO_EMPRESA_PAGAMENTOS']." 
                                and opr_data_inicio_operacoes is not null
                                and opr_data_inicio_operacoes <= NOW()
                                and opr_internacional_alicota = 0.38
                                and opr_status != '0'
                                and opr_codigo  = ".$modelo['modelo']->getCodOperador();
                        
                        $rs_verifica = SQLexecuteQuery($sql);
                        
                        if($rs_verifica){
                            if(pg_num_rows($rs_verifica) > 0){
                                $teste_facilitadora = true;
                            }
                        }
                        
                        // Capturando produto
						if ($controller->usuarios->getId() != 14549) {
							if($modelo['qtd'] > $quantideModelos && $retornoVip["ug_vip"] != 5){ //LIMITE_QUANTIDADE_PINS
								$mostra_modal_erros++;
							}
						}
                        $filtro['ogp_id'] = $modelo['modelo']->getProdutoId();
                        $instProduto = new Produto;
                        $produto = $instProduto->obter($filtro,"ogp_id", $resposta);
                        $resposta_row = pg_fetch_array($resposta);
?>
                        <div class="col-xs-12 col-sm-12 bg-branco hidden-lg hidden-md espacamento borda-fina">
                            <div class="row">
                                <div class="col-xs-3 col-sm-5">
                                    Produto:
                                </div>
                                <div class="col-xs-9 col-sm-7">
                                    <strong><?php echo ($modelo['modelo']->getNome()!="") ? $modelo['modelo']->getNomeProduto(). " - ".$modelo['modelo']->getNome() : $modelo['modelo']->getNomeProduto(); ?></strong>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-5 col-sm-5">
                                    IOF.:
                                </div>
                                <div class="col-xs-7 col-sm-7">
                                    <?php echo (($resposta_row["ogp_iof"] == 1)?"Incluso":"");?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-5 col-sm-5">
                                    Valor unitário:
                                </div>
                                <div class="col-xs-7 col-sm-7">
                                   R$ <?php echo number_format($modelo['modelo']->getValor(), 2, ',', '.')?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-5 col-sm-5">
                                    Qtde.:
                                </div>
                                <div class="col-xs-7 col-sm-7">
                                    <?php echo $modelo['qtd'];?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-5 col-sm-5">
                                    Preço Total:
                                </div>
                                <div class="col-xs-7 col-sm-7">
                                   R$ <?php echo number_format($modelo['geral'], 2, ',', '.');?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-5 col-sm-5">
                                    Comissão:
                                </div>
                                <div class="col-xs-7 col-sm-7">
                                   R$ <?php echo number_format($modelo['comissao'], 2, ',', '.');?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-5 col-sm-5">
                                    Valor Líquido:
                                </div>
                                <div class="col-xs-7 col-sm-7">
                                   R$ <?php echo number_format($valorLiquido, 2, ',', '.');?>
                                </div>
                            </div>
                        </div>
<?php
                        }elseif(is_array($modelo)){
                            foreach($modelo as $valor){
                                foreach($valor as $prod){
                                    $sql = "select 
                                        opr_codigo
                                        from operadoras
                                        where 
                                        opr_vinculo_empresa = ".$GLOBALS['IDENTIFICACAO_EMPRESA_PAGAMENTOS']." 
                                        and opr_data_inicio_operacoes is not null
                                        and opr_data_inicio_operacoes <= NOW()
                                        and opr_internacional_alicota = 0.38
                                        and opr_status != '0'
                                        and opr_codigo  = ".$prod["produto"]["ogp_opr_codigo"];

                                    $rs_verifica = SQLexecuteQuery($sql);

                                    if($rs_verifica){
                                        if(pg_num_rows($rs_verifica) > 0){
                                            $teste_facilitadora = true;
                                        }
                                    }

                                    $valorLiquido = $prod['geral']-$prod['comissao'];
?>
                                    <div class="col-xs-12 col-sm-12 bg-branco hidden-lg hidden-md espacamento borda-fina">
                                        <div class="row">
                                            <div class="col-xs-3 col-sm-5">
                                                Produto:
                                            </div>
                                            <div class="col-xs-9 col-sm-7">
                                                <strong><?php echo $prod['produto']["ogp_nome"]. " - ".$prod['valor']; ?></strong>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-5 col-sm-5">
                                                IOF.:
                                            </div>
                                            <div class="col-xs-7 col-sm-7">
                                                <?php echo (($prod["produto"]["ogp_iof"] == 1)?"Incluso":"");?>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-5 col-sm-5">
                                                Valor unitário:
                                            </div>
                                            <div class="col-xs-7 col-sm-7">
                                               R$ <?php echo number_format($prod["valor"], 2, ',', '.')?>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-5 col-sm-5">
                                                Qtde.:
                                            </div>
                                            <div class="col-xs-7 col-sm-7">
                                                <?php echo $prod['qtd'];?>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-5 col-sm-5">
                                                Preço Total:
                                            </div>
                                            <div class="col-xs-7 col-sm-7">
                                               R$ <?php echo number_format($prod['geral'], 2, ',', '.');?>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-5 col-sm-5">
                                                Comissão:
                                            </div>
                                            <div class="col-xs-7 col-sm-7">
                                               R$ <?php echo number_format($prod['comissao'], 2, ',', '.');?>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-5 col-sm-5">
                                                Valor Líquido:
                                            </div>
                                            <div class="col-xs-7 col-sm-7">
                                               R$ <?php echo number_format($valorLiquido, 2, ',', '.');?>
                                            </div>
                                        </div>
                                    </div>
<?php
                                }
                            }
                        }
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
                                    R$ <?php echo number_format($modelos['total_geral'], 2, ',', '.'); ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-5 col-sm-5">
                                    Comissão 
                                </div>
                                <div class="col-xs-7 col-sm-7">
                                   R$ <?php echo number_format($modelos['total_desconto'], 2, ',', '.'); ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-5 col-sm-5">
                                    Valor Líquido
                                </div>
                                <div class="col-xs-7 col-sm-7">
                                   R$ <?php echo number_format($modelos['total_repasse'], 2, ',', '.'); ?>
                                </div>
                            </div>
                        </div>
<?php
                }
                
                if($controller->usuarios->getId() != 14549 && $teste_facilitadora && $modelos['total_geral'] > $LIMITE_VALOR_PUBLISHERS_COM_FACILITADORA) {
?>                    
                    <form name="pagamento" id="pagamento" method="POST" action="/creditos/mensagem.php">
                        <input type='hidden' name='msg' id='msg' value='O valor permitido por pedido para estes produtos é de R$<?php echo number_format($LIMITE_VALOR_PUBLISHERS_COM_FACILITADORA, 2, ',', '.') ?>.'>
                        <input type='hidden' name='titulo' id='titulo' value='Valor limite ultrapassado'>
                        <input type='hidden' name='link' id='link' value='/creditos/'>
                    </form>
                    <script language='javascript'>
                        document.getElementById("pagamento").submit();
                    </script>
<?php
                    die("Limite de valor ultrapassado para esta venda");
                }
?>
                    <table class="table bg-branco hidden-sm hidden-xs txt-preto fontsize-pp">
                    <thead>
                      <tr class="bg-cinza-claro text-center">
                        <th class="txt-left">Produto</th>
                        <th>I.O.F.</th>
                        <th>Valor unitário</th>
                        <th>Qtde.</th>
                        <th>Total</th>
                        <th>Comissão</th>
                        <th>Líquido</th>
                      </tr>
                    </thead>
                    <tbody>
<?php 
            if(is_array($modelos) && !empty($modelos))
            {
                foreach($modelos as $modelo)
                {
                    if(is_array($modelo) && $modelo['modelo'] instanceof ProdutoModelo)
                    {
                        // Capturando produto
                        $filtro['ogp_id'] = $modelo['modelo']->getProdutoId();
                        $instProduto = new Produto;
                        $produto = $instProduto->obter($filtro,"ogp_id", $resposta);
                        $resposta_row = pg_fetch_array($resposta);
                        // Fim Captura Produto
?>                        
                      <tr class="text-center">
                        <td class="txt-left"><?php echo ($modelo['modelo']->getNome()!="") ? $modelo['modelo']->getNomeProduto(). " - ".$modelo['modelo']->getNome() : $modelo['modelo']->getNomeProduto(); ?></td>
                        <td><?php echo (($resposta_row["ogp_iof"] == 1)?"Incluso":"");?></td>
                        <td>R$ <?php echo number_format($modelo['modelo']->getValor(), 2, ',', '.')?></td>
                        <td><?php echo $modelo['qtd'];?></td>
                        <td>R$ <?php echo number_format($modelo['geral'], 2, ',', '.');?></td>
                        <td>R$ <?php echo number_format($modelo['comissao'], 2, ',', '.');?></td>
                        <td>R$ <?php echo number_format($modelo['repasse'], 2, ',', '.');?></td>
                      </tr>
<?php
                    }elseif(is_array($modelo)){
                        foreach($modelo as $valor){     
                            foreach($valor as $prod){
?>
                                <tr class="text-center">
                                    <td class="txt-left"><?php echo $prod['produto']["ogp_nome"]. " - ".$prod['valor']; ?></td>
                                    <td><?php echo (($prod["produto"]["ogp_iof"] == 1)?"Incluso":"");?></td>
                                    <td>R$ <?php echo number_format($prod["valor"], 2, ',', '.')?></td>
                                    <td><?php echo $prod['qtd'];?></td>
                                    <td>R$ <?php echo number_format($prod['geral'], 2, ',', '.');?></td>
                                    <td>R$ <?php echo number_format($prod['comissao'], 2, ',', '.');?></td>
                                    <td>R$ <?php echo number_format($prod['repasse'], 2, ',', '.');?></td>
                                </tr>
<?php
                            }
                        }
                    }
                }
            }
?>
                      <tr class="bg-cinza-claro text-center">
                        <td colspan="3">&nbsp;</td>
                        <td><strong>Total:</strong></td>
                        <td>R$ <?php echo number_format($modelos['total_geral'], 2, ',', '.'); ?></td>
                        <td>R$ <?php echo number_format($modelos['total_desconto'], 2, ',', '.'); ?></td>
                        <td>R$ <?php echo number_format($modelos['total_repasse'], 2, ',', '.'); ?></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
            </div>
        <div class="row espacamento">
<!-- INICIO TRATAMENTO -->            
            
<?php
        $bBloqueado = false;
        $qtde_ultrapassada = false;
        // usuario Risco Classif = "PRE-PAGO" com saldo negativo
        if($mostra_modal_erros > 0){
           $qtde_ultrapassada = true;

			echo '<div class="txt-vermelho">'
				. '<p><strong>A quantidade permitida por produto foi ultrapassada.</strong></p>'
				. '<p><strong>Clique <a href="/creditos/">aqui</a> para voltar a página inicial.</strong></p>'
			. '</div>';
		   
           $bBloqueado = true;
        }
        
        if($controller->usuarios->getRiscoClassif()==2) 
        {
            if(((float)$controller->usuarios->getPerfilSaldo() - (float)$modelos['total_repasse']) < 0) 
            {
                
				$file = fopen("/www/log/c.txt", "a+");
				fwrite($file, "saldo perfil ".$controller->usuarios->getPerfilSaldo()."\n");
				fwrite($file, "repasse ".$modelos['total_repasse']."\n");
				fwrite($file, "const ".(($controller->usuarios->getPerfilSaldo() - $modelos['total_repasse']) < 0)."\n");
				fwrite($file, str_repeat("*", 50)."\n");
				fclose($file);
				
                $strSaldo = number_format($controller->usuarios->getPerfilSaldo(), 2, ",", ".");
                $strTotalRepasse = number_format($modelos['total_repasse'], 2, ",", ".");
                // Notificação EPP
                $pagina = "carrinho de compras (lan pré paga)";
                $erro = "O usuário id {$controller->usuarios->getId()} tentou fazer uma compra de R$ {$strTotalRepasse}, mas tem somente o saldo de R$ {$strSaldo}";
                //$controller->emailReport($pagina,$erro);
                
                // Bloqueado por tipo Risco Classif
                echo '<div class="txt-vermelho">'
                        . '<p><strong>Seu estabelecimento não possui saldo disponível para completar esta compra.</strong></p>'
                        . '<p><strong>Clique <a href="/creditos/add_saldo.php">aqui</a> para adicionar saldo.</strong></p>'
                    . '</div>';
                $bBloqueado = true;
            }
        } 
        else 
        {
            if(($controller->usuarios->getPerfilLimite()+$controller->usuarios->getPerfilSaldo()-$modelos['total_repasse'])<0) 
            {
                // Notificação EPP
                $pagina = "carrinho de compras (lan pós paga)";
                $erro = "O usuário id {$controller->usuarios->getId()} tentou fazer uma compra de R$ ".number_format($modelos['total_repasse'], 2, ',', '.').". Sua empresa não possui saldo suficiente para efetuar esta compra</p></strong><br>
                (Limite: R$".number_format($controller->usuarios->getPerfilLimite(), 2, ',', '.').", saldo disponível: R$".number_format($controller->usuarios->getPerfilSaldo(), 2, ',', '.');
                //$controller->emailReport($pagina,$erro);
                
                echo '<div class="txt-vermelho">'
                    . '<p><strong>Sua empresa não possui saldo suficiente para efetuar esta compra</p></strong>'
                    . '<p><strong>(Limite: R$'.number_format($controller->usuarios->getPerfilLimite(), 2, ',', '.').', saldo disponível: R$'.number_format($controller->usuarios->getPerfilSaldo(), 2, ',', '.').' valor da compra: R$'.number_format($modelos['total_repasse'], 2, ',', '.').')</p></strong>'
                .    '</div>';
    //echo "<br>Para utilizar o sistema de pagamento por Express Money <br>e adquirir créditos pré-pagos <a href=\"BoletoExpressLH.php\">clique aquí</a></font></b>"

                $iaberto = checaBoletoEmAberto();
                $mensagemBoletoEmAberto = MesagemBoletoEmAberto($iaberto);

                if($mensagemBoletoEmAberto != "")
                {
                    echo '<div class="txt-vermelho">';
                    echo $mensagemBoletoEmAberto;
                    echo '</div>';
                }


                // Bloqueado por Limite Zerado
                $bBloqueado = true;
            } 

        } // Usuário Pré-pago
		
        $is_data_valid = isset($GLOBALS['_SESSION']['NOME_CPF']) && verificaNome($GLOBALS['_SESSION']['NOME_CPF']) && verificaCPF_LH($GLOBALS['_SESSION']['CPF_LH']);
		
        if(!$bBloqueado && (!$modelos['require_cpf'] || $is_data_valid))
        {
            if($controller->usuarios->getRiscoClassif()==2) 
            {
                $creditodisponivel = $controller->usuarios->getPerfilSaldo();
            } 
            else 
            {
                $creditodisponivel = $controller->usuarios->getPerfilSaldo()+$controller->usuarios->getPerfilLimite();
            }
?>      
        <div class="row">
            <div class="top20 col-md-4 col-lg-4 col-sm-12 col-xs-12">
                <div class="col-md-12">    
                    <strong>Saldo disponível:</strong><span class="pull-right">R$ <?php echo number_format($creditodisponivel, 2, ',', '.')?></span>
                </div>
                <div class="col-md-12">
                    <strong>Esta compra:</strong><span class="pull-right">R$ <?php echo number_format($modelos['total_repasse'], 2, ',', '.')?></span>
                </div>
                <div class="col-md-12">
                    <strong>Saldo:</strong><span class="pull-right">R$ <?php echo number_format($creditodisponivel-$modelos['total_repasse'], 2, ',', '.')?></span>
                </div>
            </div>
            <div class="top20 col-md-4 col-lg-4 col-sm-12 col-xs-12">
                <div class="col-md-12"> 
                    <strong>CPF do cliente:</strong>
                    <span class="pull-right">
                        <?php echo $GLOBALS['_SESSION']['CPF_LH'] ? $GLOBALS['_SESSION']['CPF_LH'] : '--'?>  
                    </span>
                    <button id="btn_edit_cpf" class="btn btn-success" style="
                        font-size: 12px;
                        font-weight: 300;
                        padding: 4px 7px;
                        font-style: normal;
                    ">Editar CPF</button>
                </div>
            </div>
        </div>

        
        <div class="row">
            <div class="top20 col-md-12 col-lg-12 col-sm-12 col-xs-12">
                <span class="txt-vermelho col-md-12"><strong><em>Atenção: Após esta etapa não será possível cancelar seu PIN.</em></strong></span>
            </div>
        </div>
         <!-- Div onde o formulário de CPF será carregado -->
    <div id="cpf_form_container" style="display: none;">
        <?php $GLOBALS['jquery-1.11.3'] = "on";
            require_once DIR_INCS . "pdv/form_cpf.php";?>
    </div>
		<form action="/creditos/pagamento/finaliza_venda.php" id="step" method="POST">
			<div style="padding: 0 0 15px 10px; display: flex; justify-content: end;">
			    <!-- <div class="g-recaptcha" data-sitekey="6LcajMgoAAAAADfMoqDlJVP90GcztNfrQIjDIwk8"></div> -->
				<div class="h-captcha" data-sitekey="cf431eba-155c-4d7f-a313-bf9d69cdf7e2"></div>
			</div>

			<div class="col-md-1 col-md-offset-9 col-sm-6 col-xs-6 top10">
				<a href="/creditos/produto/produtos_selecionados.php" class="btn btn-primary">Voltar</a>
			</div>
			<div class="col-md-2 col-sm-6 col-xs-6 top10">
				<a type="button" class="btn btn-success btn-envia">Confirmar</a>
			</div>
		</form>


<?php
        }
        else if(!$bBloqueado)
        {
			
            if(!isset($GLOBALS['_SESSION']['NOME_CPF']))
                $GLOBALS['_SESSION']['NOME_CPF'] = "";
            if(!isset($GLOBALS['_SESSION']['CPF_LH']))
                $GLOBALS['_SESSION']['CPF_LH'] = "";
            if(!isset($GLOBALS['_SESSION']['DATA_NASCIMENTO']))
                $GLOBALS['_SESSION']['DATA_NASCIMENTO'] = "";
            
            echo "<div class='top20 clearfix'></div>";
            $GLOBALS['jquery-1.11.3'] = "on";
            require_once DIR_INCS . "pdv/form_cpf.php";
        }
		
		if(isset($GLOBALS['_SESSION']['NOME_CPF']) && !empty($GLOBALS['_SESSION']['NOME_CPF'])) {
			$matches = array();
			preg_match_all('!\d+!', $GLOBALS['_SESSION']['CPF_LH'], $matches);
			
			$cpf_djx = implode('', $matches[0]);
			
			$sql_djx = "SELECT * FROM pep WHERE cpf = $cpf_djx and enviado_email = 0; ";
			$rs_djx = SQLexecuteQuery($sql_djx);
			$dadosTotais_djx = pg_fetch_all($rs_djx);
			$count_registers = pg_num_rows($rs_djx);
			
			
			if($count_registers > 0) {
				
				$cabecalho = "
					<h2 style='text-align:center;border:solid 1px black;width:90%;margin:0 auto;display:block;margin-top:40px;'>PEP (".$dadosTotais_djx[0]['nome']." - " .$dadosTotais_djx[0]['cpf']." - ".$dadosTotais_djx[0]['descricao_funcao'].") Tentando Realizar Compra via PDV (ID: ".$controller->usuarios->getId().")</h2>
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
		
				if(is_array($modelos) && !empty($modelos))
				{
					foreach($modelos as $modelo ) {
						
						//var_dump($modelo);
						
						
						if(is_array($modelo) && $modelo['modelo'] instanceof ProdutoModelo)
						{
							$sql = "select 
										opr_codigo
									from operadoras
									where 
									opr_vinculo_empresa = ".$GLOBALS['IDENTIFICACAO_EMPRESA_PAGAMENTOS']." 
									and opr_data_inicio_operacoes is not null
									and opr_data_inicio_operacoes <= NOW()
									and opr_internacional_alicota = 0.38
									and opr_status != '0'
									and opr_codigo  = ".$modelo['modelo']->getCodOperador();
							
							$rs_verifica = SQLexecuteQuery($sql);
							
							if($rs_verifica){
								if(pg_num_rows($rs_verifica) > 0){
									$teste_facilitadora = true;
								}
							}
							
							// Capturando produto
							if($modelo['qtd'] > $quantideModelos && $retornoVip["ug_vip"] != 5){
								$mostra_modal_erros++;
							}
							$filtro['ogp_id'] = $modelo['modelo']->getProdutoId();
							$instProduto = new Produto;
							$produto = $instProduto->obter($filtro,"ogp_id", $resposta);
							$resposta_row = pg_fetch_array($resposta);
							
							$tem_iof = $resposta_row["ogp_iof"] == 1 ? "Incluso" : "";
							
							$total_geral += $modelo['modelo']->getValor() * $modelo['qtd'];
							
							$exibicaoDadosProblemas .= "
								<tr>
									<td>
										".$modelo['modelo']->getNomeProduto(). " - ".$modelo['modelo']->getNome() ."
									</td>
							   
							   
									<td >
										".$tem_iof. "
									</td>
								
									<td>
									   ".number_format($modelo['modelo']->getValor(), 2, ',', '.') ."
									</td>
								
									<td > 
										". $modelo['qtd'] ."
									</td>
								
									<td >
									   ". number_format($modelo['geral'], 2, ',', '.') ."
									</td>
								
					
								</tr>";
						}
					$exibicaoDadosProblemas .= "
						<tr>
							<td colspan='2'></td>
							<td colspan='2'></td>
							<td style='border-top: 1px solid #222'> R$ ".number_format($total_geral,2, ',', '.')."</td>
						</tr>
					";
					$email  = "rc@e-prepag.com.br,rc1@e-prepag.com.br";
					$cc     = "glaucia@e-prepag.com.br";
					$subject= "Tentativa de Compra PEP";

					$msg = $cabecalho.$exibicaoDadosProblemas."</table>";
					 
					if(enviaEmail($email, $cc, $bcc, $subject, $msg)) { 
						//echo "Email enviado com sucesso".PHP_EOL;
					}
					return ;		
				}
			
		}
	}
?>

    <!-- FIM TRATAMENTO -->            
                </div>
            </div>
        </div>
<?php
	}
    else
    {
?>
    <div class="row top10 espacamento">
        <p class="txt-vermelho "><strong><em><?php echo $controller->erro; ?>.</em></strong></p>
        <p><a href="/creditos/produto/produtos_selecionados.php" class="btn btn-info"><em>Voltar</em></a></p>
    </div>
<?php
}
?>    
</div>
<script>
$(document).ready(function() {
    // Ao clicar no botão "Editar CPF", carrega o formulário via Ajax
    $('#btn_edit_cpf').click(function() {
        $('#cpf_form_container').css('display', 'block'); // Exibe a div
          
    });

    // Máscaras para CPF e Data de Nascimento
    $('#cpf_input').mask('999.999.999-99');
    $('#data_nascimento').mask('99/99/9999');

    // Validação de data de nascimento
    var currentDate = new Date();
    $('#data_nascimento').blur(function() {
        var dtNasc = $(this).val().split('/');
        var objDtNasc = new Date(parseInt(dtNasc[2]), parseInt(dtNasc[1]) - 1, parseInt(dtNasc[0]));
        if (objDtNasc.getTime() > currentDate.getTime()) {
            $(this).val('');
            alert('Data inválida');
        }
    });
});
</script>
<script>
    $(document).ready(function(){
		
		var erro = false;
		$(".btn-envia").on("click", function(){
			
			if(grecaptcha.getResponse() == "" || grecaptcha.getResponse().length == 0){
				erro = true;
			}
			
			if(erro == false){
				$("#step").submit(); 
			}else{
				$(".modal-header").html("Verificação RECAPTCHA");
				$(".modal-body").html("Você deve fazer a verificação do RECAPTCHA para finalizar a venda.");
				$("#modal-error").modal();
			}
		
		});
		
        if(verifica_qtde(<?php echo $qtde_ultrapassada; ?>))
            $("#modal-error").modal();
		          
        $("#modal-error").on("hidden.bs.modal", function () {
           if(erro == undefined || erro == false){
			   window.location = "/creditos";
		   }           
        });    
    });
    
    function verifica_qtde(mostra_modal){
        return mostra_modal;
    }
</script>
<?php
require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/footer.php";
	}
?> 