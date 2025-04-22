<?php
require_once "../../../includes/constantes.php";
require_once DIR_CLASS . "pdv/controller/CarrinhoController.class.php";

if(isset($pagto_tipo)){
	$pagina_titulo = "Comprovante " . $GLOBALS['FORMAS_PAGAMENTO_DESCRICAO'][$pagto_tipo];
}else{
	$pagina_titulo = "Comprovante";
}

//Recupera carrinho do session
//$carrinho = $_SESSION['dist_carrinho'];
$controller = new CarrinhoController;

require_once DIR_INCS . "pdv/venda_e_modelos_logica.php";

pg_result_seek($rs_venda, 0);
$rs_venda_row = pg_fetch_array($rs_venda);
$pagto_tipo	 = $rs_venda_row['vg_pagto_tipo'];
$vg_ultimo_status = $rs_venda_row['vg_ultimo_status'];

if($pagto_tipo != $FORMAS_PAGAMENTO['BOLETO_BANCARIO']){
    $strRedirect = "/ajax/pdv/pedidos.php";
    redirect($strRedirect);
}

if($_POST['envia_email'] == 1) {
    session_start();

    //echo "<pre>".print_r($_POST,true)."</pre>";
    
    //Capturando os IDs dos PINs
    $lp_ids = $_POST['listaPINs'];
    
    //Capturando o E-Mail
    $email ="";
    //validando email
    $varRegExp = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i'; 
    if(preg_match($varRegExp,$_POST['email'])) { 
        $email = $_POST['email'];
    }
    else {
        $msg .= "Email inválido"; 
    }

    //Setando a variável
    $msg = "";
    
    //Verifica reimpressao
    if($msg == ""){
            if($lp_ids == "") $msg = "Nenhum PIN selecionado.\n";
    }

    if($msg == ""){
            $sql  = "select p.pin_vencimento, p.pin_codigo, p.pin_valor, p.pin_lote_codigo, p.pin_serial, p.pin_codinterno,
                                    vgm.vgm_nome_produto, vgm.vgm_nome_modelo, opr.opr_codigo, opr.opr_nome, opr.opr_ban_pos, ogp.ogp_nome_imagem,vgm.vgm_id,vg.vg_ug_id,  
                                    CASE WHEN pin_codigo = '0000000000000000' THEN pin_caracter
                                            ELSE pin_codigo
                                    END as case_serial
                      from pins_dist p
                            inner join tb_dist_venda_games_modelo_pins vgmp on p.pin_codinterno = vgmp.vgmp_pin_codinterno 
                            inner join tb_dist_venda_games_modelo vgm on vgm.vgm_id = vgmp.vgmp_vgm_id 
                            inner join tb_dist_venda_games vg on vg.vg_id = vgm.vgm_vg_id
                            left join operadoras opr on opr.opr_codigo = vgm.vgm_opr_codigo
                            left join tb_dist_operadora_games_produto ogp on ogp.ogp_id = vgm.vgm_ogp_id
                      where vg.vg_ug_id = ".$usuarioGames->getId()."
                          and vg.vg_id = ".$GLOBALS['_SESSION']['venda']."
                          and vgmp.vgmp_pin_codinterno in (".$lp_ids.")
                            order by vgmp.vgmp_impressao_ult_data desc, vgmp.vgmp_impressao_qtde ";
            $rs_modelos = SQLexecuteQuery($sql);
            if(!$rs_modelos || pg_num_rows($rs_modelos) == 0) $msg = "Nenhum cupom encontrado.\n";
            else{
                    $sql  = "update tb_dist_venda_games_modelo_pins set
                                            vgmp_impressao_ult_data = CURRENT_TIMESTAMP,
                                            vgmp_impressao_qtde = case when vgmp_impressao_qtde is NULL then 1 else vgmp_impressao_qtde + 1 end
                                    where vgmp_pin_codinterno in ($lp_ids)";
                    $ret = SQLexecuteQuery($sql);
                    if(!$ret) $msg = "Erro ao atualizar quantidade de impressões dos cupons.\n";
            } //end else do if(!$rs_modelos || pg_num_rows($rs_modelos) == 0)

    } //end if($msg == "")

    if($msg != ""){ 
        echo "<p class='text-red'>$msg</p>";
    } //end if($msg != "")

    if($rs_modelos && $msg == ""){

            //inicializando a variavel
            $aux_lista_prods = "";

            while($rs_modelos_row = pg_fetch_array($rs_modelos)){

                    $pin_serial = $rs_modelos_row['case_serial'];
                    $case_serial = $rs_modelos_row['pin_serial'];

                    $opr_codigo     = $rs_modelos_row['opr_codigo'];
                    $pin_codinterno = $rs_modelos_row['pin_codinterno'];

                    if($opr_codigo == 28 || $opr_codigo == 44 || $opr_codigo == 34 || $opr_codigo == 100 || $opr_codigo == 101 || $opr_codigo == 106) {
                            // o carregaemnto no estoque para Axeso5 está trocado -> então troca de novo aqui
                            // o carregamento no estoque para Facebook BHN foi baseado no Axeso5 que está trocado -> então troca de novo aqui
                            $pin_serial = $rs_modelos_row['pin_serial'];
                            $case_serial = $rs_modelos_row['case_serial'];
                    }
                    if($opr_codigo == 100 || $opr_codigo == 101 ){
                            $label_numero_serie =  "Cartão";
                    }
                    else {
                            $label_numero_serie = "Nº de série";
                    }
                    //echo "VGM_ID: ".$rs_modelos_row['vgm_id']." -  UG_ID: ".$rs_modelos_row['vg_ug_id']."<br>";
                    $aux_lista_prods .= "
                    <table cellspacing='0' cellpadding='0' width='100%' style='font: normal 14px arial, sans-serif;'>
                            <tr>
                                    <td><font face='arial' color='#304D77'>Operadora</font></td>
                                    <td width='15'><font face='arial' color='#304D77'>:</font></td>
                                    <td><font face='arial' color='#304D77'>". $rs_modelos_row['opr_nome'] ."</font></td>
                            </tr>
                            <tr>
                                    <td><font face='arial' color='#304D77'>Produto</font></td>
                                    <td width='15'><font face='arial' color='#304D77'>:</font></td>
                                    <td><font face='arial' color='#304D77'>". htmlspecialchars($rs_modelos_row['vgm_nome_produto'], ENT_QUOTES) . (trim($rs_modelos_row['vgm_nome_modelo']) == ""?"":" - " . htmlspecialchars($rs_modelos_row['vgm_nome_modelo'], ENT_QUOTES)) ."</font></td>
                            </tr>
                    ";
                    if($rs_modelos_row['pin_vencimento']){
                            $aux_lista_prods .= "
                            <tr>
                                    <td><font face='arial' color='#304D77'>Validade</font></td>
                                    <td width='15'><font face='arial' color='#304D77'>:</font></td>
                                    <td><font face='arial' color='#304D77'>". $rs_modelos_row['pin_vencimento'] ." Dias</font></td>
                            </tr>
                            ";
                    }
                    $aux_lista_prods .= "
                            <tr>
                                    <td><font face='arial' color='#304D77'>Preço</font></td>
                                    <td width='15'><font face='arial' color='#304D77'>:</font></td>
                                    <td><font face='arial' color='#304D77'>". number_format($rs_modelos_row['pin_valor'], 2, ',', '.') ." </font></td>
                            </tr>
                            <tr>
                                    <td><font face='arial' color='#304D77'><b>".$label_numero_serie."</b></font></td>
                                    <td width='15'><font face='arial' color='#304D77'>:</font></td>
                                    <td><font face='arial' color='#304D77'><b>". $case_serial ." </b></font></td>
                            </tr>
                            <tr>
                                    <td><font face='arial' color='#304D77' size='4'><b>PIN</b></font></td>
                                    <td width='15'><font face='arial' color='#304D77'>:</font></td>
                                    <td><font face='arial' color='#304D77' size='4'><b>". $pin_serial ."</b></font></td>
                            </tr>
                    </table><br><br>
                    ";
                    //	opr_codigo = 100 -> 'Facebook BHN' 
                    if($opr_codigo == 100) {
                        $aux_lista_prods .= " <div style='font: normal 14px arial, sans-serif;color:#304D77;'>Para resgatar este cartão:<br>
                                                1. Acesse <a href='https://www.facebook.com/giftcards/redeem'>www.facebook.com/giftcards/redeem</a>.<br>
                                                2. Siga as instruções para resgate de cartões.<br>
                                                3. Quando solicitado, insira o código PIN.<br><br>
                                                <span style='font: normal 10px arial, sans-serif;' align='justify'><b>Termos e Condições</b><br><br>
                                                O uso deste cartão representa a aceitação dos termos e condições a seguir. 
                                                Este cartão pode ser resgatado no Facebook somente por pessoas maiores de 13 anos. 
                                                Para resgatá-lo, acesse <a href='https://www.facebook.com/giftcards/redeem'>www.facebook.com/giftcards/redeem</a>. 
                                                É necessário acesso à Internet (sujeito a tarifas cobradas pelas empresas provedoras deste acesso). 
                                                O valor total deste cartão é deduzido mediante uso do seu código PIN. 
                                                Não é permitida nenhuma dedução adicional. 
                                                Este cartão não pode ser resgatado por dinheiro, devolvido para reembolso, trocado ou revendido (exceto quando exigido por lei). 
                                                A data de emissão deste cartão é a data de compra exibida no seu recibo de vendas. 
                                                Não será fornecido nenhum reembolso ou crédito para cartões perdidos, roubados ou destruídos, ou para cartões utilizados sem permissão. 
                                                O uso do cartão-presente do Facebook é regido pelos Termos de Pagamento do Facebook, disponíveis em <a href='https://www.facebook.com/payments_terms'>www.facebook.com/payments_terms</a> e sujeitos a eventuais alterações sem aviso prévio conforme permitido por lei. 
                                                Proteja este cartão como se fosse dinheiro. Facebook Payments Inc. é o emissor deste cartão.
                                                </span>
                                              </div>
                                ";
                    }
                    //	opr_codigo = 101 -> 'IMVU BHN' 
                    if($opr_codigo == 101) {
                        $aux_lista_prods .= " <div style='font: normal 14px arial, sans-serif;color:#304D77;'>O IMVU é uma rede social de entretenimento online onde os membros usam avatares 3D para conhecer novas pessoas.<br>
                                                O cartão pré-pago IMVU pode ser resgatado no endereço <a href='http://pt.imvu.com/prepaidcard'>http://pt.imvu.com/prepaidcard</a>.<br><br>
                                                <span style='font: normal 10px arial, sans-serif;' align='justify'><b>Termos e Condições</b><br><br>
                                                O IMVU é uma rede social de entretenimento online onde os membros usam avatares 3D para conhecer novas pessoas, conversar, criar e jogar com seus amigos. 
                                                O IMVU tem mais de 100 milhões de usuários registrados e tem o maior catálogo de artigos virtuais do mundo com mais de 10 milhões de itens, quase todos criados pelos membros do site. 
                                                O cartão é válido apenas para a compra de créditos IMVU ou para associação VIP. 
                                                Para uso dos créditos IMVU, será necessário ter acesso à internet (cujo serviço pode ser cobrado) e software e hardware compatíveis com o sistema. 
                                                Para resgatar esse cartão, você deve ter uma conta avatar IMVU válida. 
                                                Menores de 18 anos precisam obter consentimento dos pais para comprar este cartão pré-pago. 
                                                Não serão permitidos reembolsos ou trocas. A partir da ativação, o risco pela perda e a titularidade do cartão passam a ser do comprador. 
                                                O IMVU não se responsabiliza por qualquer perda ou dano resultante de cartões perdidos, roubados ou utilizados sem permissão. 
                                                O uso do cartão implica a aceitação de todos os termos e condições descritos no endereço http://pt.imvu.com/catalog/web_info.php?section=info&topic=terms_of_service <a href='http://pt.imvu.com/catalog/web_info.php?section=info&topic=terms_of_service'>http://pt.imvu.com/catalog/web_info.php?section=info&topic=terms_of_service</a>.
                                                </span>
                                              </div>
                                ";
                    }

                    if(!empty($email)) {
                            $sql = "INSERT INTO tb_dist_venda_games_produto_email (
                                                                            vgpe_pin_codinterno, 
                                                                            vgpe_ug_id,
                                                                            vgpe_email,
                                                                            vgpe_data
                                                                            ) 
                                                            VALUES (
                                                                            ".$pin_codinterno.",
                                                                            ".$rs_modelos_row['vg_ug_id'].",
                                                                            '".$email."',
                                                                            NOW());";
                            //echo $sql."<br>";
                            $rs_banner = SQLexecuteQuery($sql);
                            if(!$rs_banner) {
                                    $mensagem = "Erro ao salvar informa&ccedil;&otilde;es denvio do e-mail. (ERRO: SIE-01)<br>";
                            }

                    }//end if(!empty($email))

            }//end while 

            if(!empty($email)) {

                            $objEnvioEmailAutomatico = new EnvioEmailAutomatico(TIPO_USUARIO_LAN,'CompraPontoVenda');			
                            $objEnvioEmailAutomatico->setListaProduto($aux_lista_prods);
                            $objEnvioEmailAutomatico->setUgID($usuarioGames->getId());
                            $objEnvioEmailAutomatico->setUgEmail($email);
                            $objEnvioEmailAutomatico->setUgNome($usuarioGames->getNomeFantasia());
                            echo $objEnvioEmailAutomatico->MontaEmailEspecifico();

                            $mensagem = "<p class='txt-verde text-center'>E-mail enviado com sucesso para: ".$email."</p>";

            }//end if(!empty($email)) 

    } //end if($rs_modelos)
    $mensagem .= $msg;
}//end if($envia_email == 1)

require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/header.php";

pg_result_seek($rs_venda_modelos, 0);
$arr_rs_venda_modelos_row = pg_fetch_all($rs_venda_modelos);

?>
<div class="container txt-azul-claro bg-branco">
    <div class="row">
        <div class="col-md-10">
            <div class="row">
                <div class="col-md-12 espacamento">
                    <strong>Número do pedido: <?php echo $GLOBALS['_SESSION']['venda']; ?></strong>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 espacamento">
                    <strong>Produtos selecionados</strong>
                </div>
            </div>
            <div class="row txt-cinza espacamento">
                <div class="col-md-12 bg-cinza-claro">
<?php                                
            if(is_array($arr_rs_venda_modelos_row) && !empty($arr_rs_venda_modelos_row))
            {
                $qtde_total = 0;
                $total_geral = 0;
                $total_desconto = 0;
                $total_repasse = 0;

                $total_geral = 0;

                foreach($arr_rs_venda_modelos_row as $ind => $rs_venda_modelos_row){

                    $qtde = $rs_venda_modelos_row['vgm_qtde'];
                    $valor = $rs_venda_modelos_row['vgm_valor'];
                    $perc_desconto = $rs_venda_modelos_row['vgm_perc_desconto'];
                    $geral = $valor*$qtde;
                    $desconto = $geral*$perc_desconto/100;
                    $repasse = $geral - $desconto;

                    $qtde_total += $qtde;
                    $total_geral += $geral;
                    $total_desconto += $desconto;
                    $total_repasse += $repasse;

                    // Capturando produto
                    $filtro['ogp_id'] = $rs_venda_modelos_row["vgm_ogp_id"];
                    $instProduto = new Produto;
                    $produto = $instProduto->obter($filtro,"ogp_id", $resposta);
                    $resposta_row = pg_fetch_array($resposta);
                    // Fim Captura Produto
?>
                        <div class="col-xs-12 col-sm-12 bg-branco hidden-lg hidden-md espacamento borda-fina">
                            <div class="row">
                                <div class="col-xs-3 col-sm-5">
                                    Produto:
                                </div>
                                <div class="col-xs-9 col-sm-7">
                                    <strong><?php echo $rs_venda_modelos_row['vgm_nome_produto'];
                                    if($rs_venda_modelos_row['vgm_nome_modelo']!=""){ echo " - ".$rs_venda_modelos_row['vgm_nome_modelo'];  }; ?></strong>
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
                                    Qtde.:
                                </div>
                                <div class="col-xs-7 col-sm-7">
                                   <?php echo $qtde; ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-5 col-sm-5">
                                    Preço unitário:
                                </div>
                                <div class="col-xs-7 col-sm-7">
                                    R$ <?php echo number_format($valor, 2, ',', '.'); ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-5 col-sm-5">
                                    Preço Total:
                                </div>
                                <div class="col-xs-7 col-sm-7">
                                   R$ <?php echo number_format($geral, 2, ',', '.'); ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-5 col-sm-5">
                                    Desconto:
                                </div>
                                <div class="col-xs-7 col-sm-7">
                                   R$ <?php echo number_format($desconto, 2, ',', '.'); ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-5 col-sm-5">
                                    Valor Líquido:
                                </div>
                                <div class="col-xs-7 col-sm-7">
                                   R$ <?php echo number_format($repasse, 2, ',', '.'); ?>
                                </div>
                            </div>
                        </div>
<?php
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
                               R$ <?php echo number_format($total_geral, 2, ',', '.'); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-5 col-sm-5">
                                Desconto 
                            </div>
                            <div class="col-xs-7 col-sm-7">
                               R$ <?php echo number_format($total_desconto, 2, ',', '.'); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-5 col-sm-5">
                                Valor Líquido
                            </div>
                            <div class="col-xs-7 col-sm-7">
                               R$ <?php echo number_format($total_repasse, 2, ',', '.'); ?>
                            </div>
                        </div>
                    </div>
<?php
            }   
?>
                <table class="table bg-branco hidden-sm hidden-xs txt-preto fontsize-p">
                    <thead>
                      <tr class="bg-cinza-claro text-center">
                        <th class="txt-left">Produto</th>
                        <th>I.O.F.</th>
                        <th>Qtde.</th>
                        <th>Preço Unitário</th>
                        <th>Preço Total</th>
                        <th>Desconto</th>
                        <th>Valor Líquido</th>
                      </tr>
                    </thead>
                    <tbody>
<?php 
            $qtde_total = 0;
            $total_geral = 0;
            $total_desconto = 0;
            $total_repasse = 0;

            $total_geral = 0;
            
            foreach($arr_rs_venda_modelos_row as $ind => $rs_venda_modelos_row){

                $qtde = $rs_venda_modelos_row['vgm_qtde'];
                $valor = $rs_venda_modelos_row['vgm_valor'];
                $perc_desconto = $rs_venda_modelos_row['vgm_perc_desconto'];
                $geral = $valor*$qtde;
                $desconto = $geral*$perc_desconto/100;
                $repasse = $geral - $desconto;

                $qtde_total += $qtde;
                $total_geral += $geral;
                $total_desconto += $desconto;
                $total_repasse += $repasse;

                // Capturando produto
                $filtro['ogp_id'] = $rs_venda_modelos_row["vgm_ogp_id"];
                $instProduto = new Produto;
                $produto = $instProduto->obter($filtro,"ogp_id", $resposta);
                $resposta_row = pg_fetch_array($resposta);
                // Fim Captura Produto

?>
                    <tr class="text-center">
                        <td class="txt-left">
                            <?php echo $rs_venda_modelos_row['vgm_nome_produto']; ?> 
                            <?php if($rs_venda_modelos_row['vgm_nome_modelo']!=""){ echo " - ".$rs_venda_modelos_row['vgm_nome_modelo'];  }; ?>
                        </td>
                        <td><?php echo (($resposta_row["ogp_iof"] == 1)?"Incluso":"");?></td>
                        <td><?php echo $qtde; ?></td>
                        <td>R$ <?php echo number_format($valor, 2, ',', '.'); ?></td>
                        <td>R$ <?php echo number_format($geral, 2, ',', '.'); ?></td>
                        <td>R$ <?php echo number_format($desconto, 2, ',', '.'); ?></td>
                        <td>R$ <?php echo number_format($repasse, 2, ',', '.'); ?></td>
                    </tr>
<?php   
            }
?>
                    <tr class="bg-cinza-claro text-center">
                        <td colspan="4" class="text-right">Total:</td>
                        <td><b>R$ <?php echo number_format($total_geral, 2, ',', '.'); ?></b></td>
                        <td><b>R$ <?php echo number_format($total_desconto, 2, ',', '.'); ?></b></td>
                        <td><b>R$ <?php echo number_format($total_repasse, 2, ',', '.'); ?></b></td>
                    </tr>
                    </tbody>
                  </table>
                </div>
            </div>
<?php
            if(isset($mensagem) && $mensagem != "")
            {
?>
            <div class="row espacamento">
                <p class="txt-vermelho"><?php echo $mensagem; ?></p>
            </div>
<?php
            }
?>
            <div class="row espacamento">
                <div class="col-md-12 text-center" id="box-lan-hope">
                    <p><img src="/imagens/loading1.gif" width="42" height="42" border="0" title="Carregando..."></p>
                    <p class="txt-vermelho"><em><?php echo $STATUS_VENDA_DESCRICAO[$vg_ultimo_status]; ?></em></p>
                </div>
            </div>
        </div>
    </div>
</div>
<script language="javascript">
    	var refresh_snipet = 1;

	// Carrega Icones coloridos 
	function atualiza_info_pedido(){
        
        if(refresh_snipet==0){
            clearInterval(refreshIntervalId);
        }else{
            if($(":checkbox:checked").length <= 0){
                $.ajax({
                        type: "POST",
                        url: "/ajax/pdv/pedido.php",
                        success: function(html){
                                if(refresh_snipet==0) {
                                    clearInterval(refreshIntervalId);
                                }
                                $('#box-lan-hope').html(html);
                        }
                });
            }
        }
	}
	var refreshIntervalId = setInterval('atualiza_info_pedido()',5000);
</script>
<?php
require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/footer.php";
?>