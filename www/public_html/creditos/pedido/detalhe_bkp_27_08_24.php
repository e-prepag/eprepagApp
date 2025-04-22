<?php
//página equivalente a /prepag2/dist_commerce/conta/pagto_compr_boleto.php

$_PaginaOperador1Permitido = 53; // o número magico
$_PaginaOperador2Permitido = 54;

$_REQUEST['nao_emitidos'] = (isset($_REQUEST['nao_emitidos'])) ? $_REQUEST['nao_emitidos'] : false;

$pagina_titulo = "Detalhe do pedido";

require_once "../../../includes/constantes.php";
require_once DIR_CLASS."pdv/controller/PedidosController.class.php";
$controller = new PedidosController;

if(isset($_POST['tf_v_codigo_detalhe']))
    $_POST['tf_v_codigo'] = $_POST['tf_v_codigo_detalhe'];

$GLOBALS['_SESSION']['venda'] = $_POST['tf_v_codigo'];

require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/header.php";

if(isset($_REQUEST['envia_email']) && $_REQUEST['envia_email'] == 1) {
   $msg = $controller->enviaEmail();
}

	
if(!isset($_POST['tf_v_codigo']) || $_POST['tf_v_codigo'] == ""){
    $str = '<div class="container txt-azul-claro bg-branco">
                <div class="row">
                    <div class="col-md-12 espacamento txt-verde text-center">
                        <strong>Nenhum pedido foi informado</strong>
                    </div>
                </div>
            </div>';
    die($str);
}

//Recupera carrinho do session
$pedido = $controller->getVenda($GLOBALS['_SESSION']['venda']);
// hash venda
$key = 'epp@2022@pin@23453';
$plaintext = $GLOBALS['_SESSION']['venda'];
$ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
$iv = openssl_random_pseudo_bytes($ivlen);
$ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
$hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
$idVendCalc = base64_encode( $iv.$hmac.$ciphertext_raw );

// antigo hash usuario
/*
$key = 'epp@2022@pin@23453';
$plaintext = $controller->usuarios->getId();
$ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
$iv = openssl_random_pseudo_bytes($ivlen);
$ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
$hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
$usuarioEnc = base64_encode( $iv.$hmac.$ciphertext_raw );
*/

// novo hash usuario
$usuarioEnc = urlencode(base64_encode(base_convert(base_convert($controller->usuarios->getId(), 10, 8), 8, 16)));

// TESTE DA NOVA INTEGRAÇÃO PDV
$linkPageGarena = "https://www.e-prepag.com.br/resgate/garena/creditos-novo.php?partner=".urlencode($usuarioEnc);

if(empty($pedido['produtos'])){
    Util::redirect("/creditos/depositos.php");
}

if($pedido['venda'])
{
?>
<div class="container txt-azul-claro bg-branco">
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-12 espacamento">
                    <strong>Pedidos</strong>
                </div>
            </div>
            <div class="row txt-cinza espacamento">
                <div class="col-md-12 col-lg-12 col-xs-12 col-sm-12 bg-cinza-claro espacamento">
                    <div class="col-xs-12 col-sm-12 hidden-lg hidden-md">
                        <div class="col-sm-8 col-xs-8 bg-cinza-claro txt-azul-claro">
                            <p><strong>Pedido: <?php echo $GLOBALS['_SESSION']['venda']; ?></strong></p>
                            <p><strong>Status: <?php echo $pedido['venda'][0]->getStatus();?></strong></p>
                        </div>
                        <div class="col-xs-4 col-sm-4 bg-cinza-claro">
						<?php
						    	
						    // funcao temporaria para o bloqueio do botao para PDVs que excederam quantidade de pins 
						    function buscaQtdeExcedida(){
								
								global $controller;
								global $pedido;
								
								// $controller->usuarios->getId() 
								$sqlQtde = "select vg_data_inclusao,vg_id,vgm_id,vg_ug_id,vgm_ogp_id,vgm_qtde as qtde_original,(select count(*) from tb_dist_venda_games_modelo_pins where vgmp_vgm_id = vgm_id) as qtde_recebida from tb_dist_venda_games_modelo inner join tb_dist_venda_games on vgm_vg_id = vg_id where vgm_qtde <> (select count(*) from tb_dist_venda_games_modelo_pins where vgmp_vgm_id = vgm_id) and vg_id not in(14827296,33147950,82290948,96353986,58057989) and vg_ug_id = ".$controller->usuarios->getId()." and vg_ultimo_status = '5' and vg_data_inclusao >= '2022-01-01' and (select count(*) from tb_dist_venda_games_modelo_pins where vgmp_vgm_id = vgm_id) <> 0 order by qtde_recebida desc;";
								$rs = SQLexecuteQuery($sqlQtde);
								$dadosQtde = pg_fetch_assoc($rs);
								
								if(!empty($dadosQtde) && $dadosQtde != false){
									$sqlIdProd = "select ogp_id from tb_dist_venda_games inner join tb_dist_venda_games_modelo on vg_id = vgm_vg_id inner join tb_dist_operadora_games_produto on vgm_ogp_id = ogp_id where vg_id =". $GLOBALS['_SESSION']['venda'];
									$proid = SQLexecuteQuery($sqlIdProd);
									$idprodepp = pg_fetch_assoc($proid);
									if($dadosQtde["vgm_ogp_id"] == $idprodepp["ogp_id"]){
										return true;
									}
									return false;
								}else{
									return false;
								}
									
							} 
							
							//if($controller->usuarios->getId() == 17371){
								//buscaQtdeExcedida();
								    
								//col-xs-4 col-sm-4 bg-cinza-claro
							//}
							
							//17371 nosso id andre
							
							    //$idBloqueados = [7045,12667,12881,17944,12881,7950,15025,10701,5944,15434,17148,13333,17589,5944,8074,17396,14350,18686,9952,12404];  
							    //if(!in_array($controller->usuarios->getId(), $idBloqueados)){
								//if(!buscaQtdeExcedida()){
						?>
						    <p><a href="#reemitepins" class="btn btn-success pull-right reemite" title="">Emitir pin</a></p>
						<?php
								//}
						?>
                        </div>
                    </div>
<?php
                $qtde_total = 0;
                $total_geral = 0;
                $total_desconto = 0;
                $total_repasse = 0;
                    foreach($pedido['produtos'] as $ind => $produto)
                    {

                        $qtde = $produto->getQtd();
                        $valor = $produto->getValorUnitario();
                        $perc_desconto = $produto->getDesconto();
                        $geral = $valor*$qtde;
                        $desconto = $geral*$perc_desconto/100;
                        $repasse = $geral - $desconto;

                        $qtde_total += $qtde;
                        $total_geral += $geral;
                        $total_desconto += $desconto;
                        $total_repasse += $repasse;
?>
                        <div class="col-xs-12 col-sm-12 bg-branco hidden-lg hidden-md espacamento borda-fina">
                            <div class="row">
                                <div class="col-xs-3 col-sm-5">
                                    Produto:
                                </div>
                                <div class="col-xs-9 col-sm-7">
                                    <strong><?php echo $produto->getNomeProduto(). " - ".$produto->getModelo();?></strong>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-5 col-sm-5">
                                    IOF.:
                                </div>
                                <div class="col-xs-7 col-sm-7">
                                    <?php echo (($produto->getIOF() == 1)?"Incluso":"");?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-5 col-sm-5">
                                    Qtde.:
                                </div>
                                <div class="col-xs-7 col-sm-7">
                                   <?php echo $produto->getQtd();?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-5 col-sm-5">
                                    Preço Unitário:
                                </div>
                                <div class="col-xs-7 col-sm-7">
                                   <?php echo number_format($produto->getValorUnitario(), 2, ',', '.');?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-5 col-sm-5">
                                    Preço Total:
                                </div>
                                <div class="col-xs-7 col-sm-7">
                                   <?php echo number_format($geral, 2, ',', '.')?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-5 col-sm-5">
                                    Desconto:
                                </div>
                                <div class="col-xs-7 col-sm-7">
                                   <?php echo number_format($desconto, 2, ',', '.'); ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-5 col-sm-5">
                                    Valor Líquido:
                                </div>
                                <div class="col-xs-7 col-sm-7">
                                   <?php echo number_format($repasse, 2, ',', '.')?>
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
                               <?php echo number_format($total_geral, 2, ',', '.'); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-5 col-sm-5">
                                Desconto 
                            </div>
                            <div class="col-xs-7 col-sm-7">
                               <?php echo number_format($total_desconto, 2, ',', '.'); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-5 col-sm-5">
                                Valor Líquido
                            </div>
                            <div class="col-xs-7 col-sm-7">
                               <?php echo number_format($total_repasse, 2, ',', '.'); ?>
                            </div>
                        </div>
                    </div>
                    <table class="table hidden-sm hidden-xs bg-branco txt-preto fontsize-p">
                    <thead>
					
					   <tr class="bg-cinza-claro text-left">
					    <?php
							 if($controller->usuarios->getId() == 17371){
									//echo '<th colspan="12"><button type="button" class="btn btn-success copiar">link para resgate</button><span class="d-none link">'.$linkPageGarena.'</span><span class="d-none mensagem left5"></span></th>';
							 }
						?>
						</tr>
                        <tr class="bg-cinza-claro text-left">
                            <th colspan="6" class="txt-azul-claro">
                                Pedido: <?php echo $GLOBALS['_SESSION']['venda']; ?>
                            </th>
						
							<th rowspan="2" colspan="1"><button type="button" class="btn btn-success pull-right reemite" title="">Emitir pin</button></th>
							
                        </tr>
                        <tr class="bg-cinza-claro text-left">
                            <th colspan="6" class="txt-azul-claro">
                                Status: <?php echo $pedido['venda'][0]->getStatus();?>
                            </th>
                        </tr>
                        <tr class="bg-cinza-claro text-center">
                            <th>Produto</th>
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
                foreach($pedido['produtos'] as $ind => $produto)
                {
                    
                    $qtde = $produto->getQtd();
                    $valor = $produto->getValorUnitario();
                    $perc_desconto = $produto->getDesconto();
                    $geral = $valor*$qtde;
                    $desconto = $geral*$perc_desconto/100;
                    $repasse = $geral - $desconto;

                    $qtde_total += $qtde;
                    $total_geral += $geral;
                    $total_desconto += $desconto;
                    $total_repasse += $repasse;
?>
                    <tr class="text-center">
                        <td><?php echo $produto->getNomeProduto(). " - ".$produto->getModelo();?></td>
                        <td><?php echo (($produto->getIOF() == 1)?"Incluso":"");?></td>
                        <td><?php echo $produto->getQtd();?></td>
                        <td><?php echo number_format($produto->getValorUnitario(), 2, ',', '.');?></td>
                        <td><?php echo number_format($geral, 2, ',', '.')?></td>
                        <td><?php echo number_format($desconto, 2, ',', '.'); ?></td>
                        <td><?php echo number_format($repasse, 2, ',', '.')?></td>
                    </tr>
<?php
                }
?>
                    <tr class="bg-cinza-claro text-center">
                        <td colspan="4" class="text-right">Total:</td>
                        <td><b><?php echo number_format($total_geral, 2, ',', '.'); ?> </b></td>
                        <td><b><?php echo number_format($total_desconto, 2, ',', '.'); ?></b></td>
                        <td><b><?php echo number_format($total_repasse, 2, ',', '.'); ?></b></td>
                    </tr>
                    </tbody>
                  </table>
                </div>
            </div>
            <?php
            if(isset($msg) && $msg != "")
            {
?> 
            <div class="row">
                <div class="col-md-12 espacamento txt-verde text-center">
                    <strong><?php echo $msg;?></strong>
                </div>
            </div>
<?php
            }
?>
            <div class="row espacamento content">
                <div class="col-md-12 text-center" <?php if(!isset($_REQUEST['nao_emitidos']) || $_REQUEST['nao_emitidos'] != 1) echo 'style="display:none;"'; ?> id="box-lan-hope">
    <!-- FIM NOVO BLOCO dsds -->
<?php
$sql = "select vgm.vgm_ogp_id,pg.pin_status,pg.pin_game_id,pg.pin_status_trava,pg.pin_guid_epp,pg.pin_guid_parceiro,p.pin_valor,vgm.vgm_nome_produto,vgm.vgm_ogp_id,vgm.vgm_opr_codigo,vgm.vgm_nome_modelo,p.pin_codinterno, p.pin_vencimento, p.pin_codigo, p.pin_lote_codigo, p.pin_serial, vgmp.vgmp_impressao_qtde, vgmp.vgmp_impressao_ult_data, vgm.vgm_id, vgm_pin_request 
from pins_dist p 
    inner join pins pg on pg.pin_codinterno = p.pin_codinterno
    inner join tb_dist_venda_games_modelo_pins vgmp on p.pin_codinterno = vgmp.vgmp_pin_codinterno 
    inner join tb_dist_venda_games_modelo vgm on vgm.vgm_id = vgmp.vgmp_vgm_id 
    inner join tb_dist_venda_games vg on vg.vg_id = vgm.vgm_vg_id 
where vg.vg_id = ".$GLOBALS['_SESSION']['venda']."
    and vg.vg_ug_id = ".$controller->usuarios->getId()." and vg.vg_ultimo_status = '5'
order by vgmp.vgmp_impressao_ult_data desc, vgmp.vgmp_impressao_qtde, p.pin_serial;";

$rs = SQLexecuteQuery($sql);
if(!$rs) {
?>
    <p class="text-red">Nenhum produto encontrado (ERRO: WM390).</p>
<?php
    die();
} //end if(!$rs)
    
//Calculando se a quantidade de PINs é igual a quantidade de registros 
$totalRegistros = pg_num_rows($rs);
$sql = "select sum(vgm_qtde) as total from tb_dist_venda_games_modelo where vgm_vg_id = ".$GLOBALS['_SESSION']['venda'];
$rsTotal = SQLexecuteQuery($sql);
$rsTotalRow = pg_fetch_array($rsTotal);
$totalPins = $rsTotalRow['total'];

?>
<div class="col-md-12 espacamento">
    <form name="form1" id="form1" target="_blank" action="/creditos/imprimir_cupom.php" method="post">
        <input type="hidden" name="tf_v_codigo_detalhe" id="tf_v_codigo_detalhe" value="<?php echo $GLOBALS['_SESSION']['venda']; ?>">
        <input type="hidden" name="nao_emitidos" id="nao_emitidos" value="<?php echo $_REQUEST['nao_emitidos']; ?>">
        <input type="hidden" id="imprimir_ou_csv" name="imprimir_ou_csv" value="">
		
    <?php
    if($totalPins > 0) {
        $checkbox = true;
    ?>
    <!-- NOVO BLOCO -->
    <div class="row text-center espacamento content">
<?php
    if($totalPins > 1){
?>
        <div class="txt-preto pull-left col-md-12 text-left">
            <input type="checkbox" id="checkall">
            <label for="checkall" class="fontweightnormal">Selecionar Todos</label>
        </div>
<?php
    }
	     $sqlGarena = "select count(*) as tot from tb_dist_venda_games_modelo where (vgm_ogp_id = 355 or vgm_ogp_id = 374) and vgm_vg_id =" . $GLOBALS['_SESSION']['venda'];
		 $dadosGarena = SQLexecuteQuery($sqlGarena);
		 $totalGarena = pg_fetch_array($dadosGarena); 
		
		 if($totalGarena["tot"] > 0){ //&&($controller->usuarios->getId() == 17371 || $controller->usuarios->getId() == 7503 || $controller->usuarios->getId() == 7630 || $controller->usuarios->getId() == 13885)
			 if($controller->usuarios->getReprLegalMSN() == "" || $controller->usuarios->getReprLegalMSN() == null){
				 echo '<div style="margin-top:25px;"><a target="_blank" href="https://www.e-prepag.com.br/resgate/garena/creditos.php" style="display:block;margin-bottom: 5px;width: 200px;" class="btn btn-success copiar">link para resgate Garena</a></div>';
			 }else{
				 echo '<div style="margin-top:25px;"><a target="_blank" href="'.$linkPageGarena.'" style="display:block;margin-bottom: 5px;width: 200px;" class="btn btn-success copiar">link para resgate Garena</a></div>';
			 }
		 }

?>
	 
	    <!-- AVISO DO METODO GARENA -->
		<div class="alert alert-notify" role="alert"></div>
			
        <table class="table bg-branco borda-fina txt-cinza" id="reemitepins">
            <tbody>
    <!-- FIM NOVO BLOCO -->
<?php   
        //Variavel para habilitar botão de envio de email
        $podeEnviarEmail = true;
        
        // contador para incrementar o nome do checkbox e funcionar o novo layout
        // para verificar o checkbox repetir até a quantidade total de PINs do pedido (pg_num_rows($rs))
        $contador = 1;
        while($rs_row = pg_fetch_array($rs)) {
            
			
            if($rs_row['vgm_pin_request'] > 0)
                $podeEnviarEmail = false;
			
			// verificação se o pin e do garena ( * verificar depois se já foi depositado o valor na conta do cliente )
			if(
			    $rs_row['vgm_opr_codigo'] == 124 && 
				($rs_row['pin_status'] == 6 || $rs_row['pin_status'] == 3) &&
			    ($rs_row['vgm_ogp_id'] == 355 || $rs_row['vgm_ogp_id'] == 374 || $rs_row['vgm_ogp_id'] == 493) && 
				$rs_row['pin_game_id'] == null && ($rs_row['pin_status_trava'] == null || $rs_row['pin_status_trava'] == 'LIBERADO') && $rs_row['pin_guid_epp'] == null && $rs_row['pin_guid_parceiro'] == null
			){
				$liberaDepositoGarena = true;
				//$classbloqueia = "bloque";
			}
?>    
            <tr>
                <td id="td2emitir<?php echo $contador;?>">
<?php 
            if($rs_row['vgmp_impressao_qtde'] > 0) 
            { 
?>
                <span class="txt-verde glyphicon glyphicon-ok t0"></span>
<?php 
    } 
?>
                </td>
				<td class="d-none" id="td3id<?php echo $contador;?>"><?php echo $rs_row["vgm_ogp_id"];?></td> <!-- G -->
                <td>
                    <input type="checkbox"  value="<?php echo $rs_row['pin_codinterno']; ?>" id="emitir<?php echo $contador;?>" name="emitir<?php echo $contador;?>">
                    <label for="emitir<?php echo $contador;?>"></label>
                </td>
                
                <td id="tdemitir<?php echo $contador;?>">
<?php //Verificando se já foi impresso
                if($rs_row['vgmp_impressao_qtde'] > 0) {
                    echo "<span class='pull-left'>Emitido&nbsp;</span> ";
                    $sql = "select * from tb_dist_venda_games_produto_email where vgpe_pin_codinterno = ".$rs_row['pin_codinterno'].";";
                    $rs_forma = SQLexecuteQuery($sql);
                    if($rs_forma) {
                            $total_email = 0;
                            $lista_emails = "";
                            while($rs_forma_row = pg_fetch_array($rs_forma)) {
                                    $total_email++;
                                    if(empty($lista_emails))
                                            $lista_emails .= $rs_forma_row['vgpe_email'];
                                    else $lista_emails .= ",\n ".$rs_forma_row['vgpe_email'];
                            } //end while
                            //echo "[$total_email]";
                            if($total_email >= $rs_row['vgmp_impressao_qtde'] ) {
    ?>
                                <span class="glyphicon glyphicon-envelope t0 left" alt='Email para: <?php echo $lista_emails; ?>' title='Email para: <?php echo "\n ".$lista_emails; ?>'></span>
                                <div style="display:none"><?php echo $lista_emails;?></div>
    <?php
                            }
                            elseif($total_email == 0){
    ?>
                                <span class="glyphicon glyphicon-print t0 left" alt='Impresso' title='Impresso'></span>
    <?php
                            }
                            else {
    ?>
                                <span class="glyphicon glyphicon-envelope t0 left"alt='Email para: <?php echo $lista_emails; ?>' title='Email para: <?php echo "\n ".$lista_emails; ?>'></span>
                                <span class="glyphicon glyphicon-print t0 left" alt='Impresso' title='Impresso'></span>
    <?php                                
                            }
                    }//end if($rs_forma)

            }//end if($rs_row['vgmp_impressao_qtde'] > 0)
				
			// pega data de utilizacao 
			$sqlUtilizacao = "select pih_data from pins_integracao_historico where pih_pin_id =". $rs_row["pin_codinterno"];
			$rs_util = SQLexecuteQuery($sqlUtilizacao);
			$dataUtil = pg_fetch_assoc($rs_util);
			
?>
                </td>
                <td><?php echo $rs_row['vgm_nome_produto'].": ".$rs_row['vgm_nome_modelo']; ?></td>
                <td class="txt-verde">R$ <?php echo number_format($rs_row['pin_valor'], 2, ',', '.'); ?></td>
				
				<?php
                    if((isset($liberaDepositoGarena) && $liberaDepositoGarena === true)){ //($controller->usuarios->getId() == 17371 || $controller->usuarios->getId() == 273) &&
                ?>	
				    <td></td>
				    <td> 
					    <?php 
						   if($controller->usuarios->getId() != null){ //17371
						?>
					         <button type="button" class="btn btn-success depositarPin" id="depositarPin">Resgatar</button>
						<?php
						   }
						?>
					</td>
				<?php //<a href="#" class="link-conta"> 
				    unset($liberaDepositoGarena);
                    }else if($rs_row['pin_game_id'] != null && $rs_row['pin_game_id'] != "" && $dataUtil["pih_data"] != "" && $dataUtil["pih_data"] != null){
						echo '<td>Data de resgate: <b class="txt-verde">'.substr($dataUtil["pih_data"], 8, 2)."/".substr($dataUtil["pih_data"], 5, 2)."/".substr($dataUtil["pih_data"], 0, 4)."-".substr($dataUtil["pih_data"], 11, 8).'</b></td>';
				        echo '<td>Conta usada: <b class="txt-verde txt-conta border-verify-user">'.$rs_row['pin_game_id'].'</b><div id="user" data-prod="'.$rs_row['vgm_ogp_id'].'"></div></td>';
					}else{
						echo "<td></td><td></td>";
					}
					
                ?>
				
        </tr>
<?php 
            $contador++;
        }//end while($rs_row = pg_fetch_array($rs))
        if($totalPins > $totalRegistros) {
            $subTotal = $totalPins - $totalRegistros;
            for($i = $subTotal; $i>0; $i-- ) {
?>    
            <tr>
                <td colspan="5">
                    PIN ainda em processamento. Aguarde um instante.
                </td>
        </tr>
<?php                
            }//end for
        }//end if($totalPins > $totalRegistros)

?>
    </table>
    <?php
    }//end if(pg_num_rows($rs) > 1)
    else {
        $rs_row = pg_fetch_array($rs);
    ?>
        <input type="checkbox"  value="<?php echo $rs_row['pin_codinterno']; ?>" id="emitir" name="emitir" checked="checked" style="display:none">
    <?php
        //Colocar aqui o checkbox invisivel com o unico PIN da venda
    }//end else do if(pg_num_rows($rs) > 1)

    ?>

    </form>
</div>
<script language="javascript">
function showValues() {
  var str = $('form').serialize();
  return str;
}

let vd = "<?php echo $idVendCalc; ?>"; 
var iptCheckBox = <?php echo (isset($checkbox)) ? "true" : "false";?>;
//funcao de Envio de Email
$(function(){
    
    $("#checkall").click(function(){
        
        var res = this.checked;
        $(':checkbox').each(function() {
                 this.checked = res;	
        });
		
    });
	
	$(document).on("click", ".txt-conta", function(event){
		   
		   let ele = $(event.target);
		   ele.css("position", "relative");
		   let div = $(event.target.parentElement).find("#user");
		   $.ajax({
			   url: "https://www.e-prepag.com.br/ajax/garena/verificaProduto.php",
			   method: "POST",
			   data: {vde: <?php echo $GLOBALS['_SESSION']['venda'];?>, codigo: $($(event.target.parentElement.parentElement).children()[2]).children()[0].value, garena: event.target.innerText, prod: div.data().prod, user: true, type: "pdv"},
			   beforeSend: function(){
				    div.html("Aguarde ....");
					div.css({
					   padding :"7px",
					   display: "block",
					   backgroundColor: "#eeeeee",
					   borderRadius: "5px",
					   zIndex: "1",
					   right: "0",
					   position:"absolute"
					});			
			   }
		   }).done(function(res){
				let dados = JSON.parse(res);
                div.html("Nome de usuário: <b>" + dados[0].nome+ "</b>");
                div.delay(4000).fadeOut(800);				
		   }); 
		   
	});
	
	/*$(".copiar").on("click", function(event){
		
		 let content =  $($(event.target.parentElement).children()[1]).html(); //$('.link').html();
         let mensagem = $(event.target.parentElement).children().last(); //$('.mensagem') 
         navigator.clipboard.writeText(content).then(() => {
            
			mensagem.addClass("text-success");
			$(mensagem).html("Link copiado com sucesso!");
			$(mensagem).fadeIn("slow");
			$(mensagem).delay(4000).fadeOut(800);

        });

	});*/
    
   $(".reemite").click(function(){
       $('#box-lan-hope').toggle();
   });
    
   $("#emailPin").click(function(){
       
	    let usr = <?php echo $controller->usuarios->getId();?>;
	
        if($(":checkbox:checked").length > 0 || iptCheckBox == false)
        {
			let blocks = 0;
			$("input:checkbox:checked").each(function(){
				let id = $(this).attr("id").replace("emitir","");
				let vl = $(this).parent().parent().find("#td3id"+id);
			    if(vl.html() == 355 || vl.html() == 374 || vl.html() == 493){
					blocks++;
				}
			});
			
			/*if(blocks != 0 && ( usr != 7503 && usr != 7630 && usr != 13885 && usr != 17371)){
				
				$(".alert-notify").css({display:"block"});
				$(".alert-notify").html("Essa opção de resgate não está disponivel para os produtos garena");
				if($(".alert-notify").hasClass("alert-success")){
					$(".alert-notify").removeClass("alert-success");
				}
				$(".alert-notify").addClass("alert-danger");
				$(".alert-notify").delay(4000).fadeOut(100);
				
			}else{ */
				
				$.ajax({
				  type: 'POST',
				  url: 'http<?php if($_SERVER['HTTPS']=="on") { echo "s"; } ?>://<?php echo $server_url; ?>/creditos/ajax/emailCupom.php',
				  data: showValues(),
				  beforeSend: function(){
					  $('#box-lan-hope').html("<img src='/imagens/loading1.gif' border='0' title='Pedido aguardando processamento....'/><p class='text-red'>Pedido aguardando processamento.</p>");
				  },
				  success: function(html){
					  //$('#box-lan-hope').html(html);
					  $('#box-lan-hope').html(html);
					  //console.log(html);
				  },
				  error: function(){
						  alert('Erro Valor');
				  }
				});
				
			//}
			
        }else
        {
            $(".errorBox").html("Selecione uma opção.");
        }
   });
   
   $("#imprimirPin").click(function(){
	   
        var id = false;
		let blocks = 0;
		let usr = <?php echo $controller->usuarios->getId();?>;
		 
		$("input:checkbox:checked").each(function(){
			let id = $(this).attr("id").replace("emitir","");
			let vl = $(this).parent().parent().find("#td3id"+id);
			if(vl.html() == 355 || vl.html() == 374 || vl.html() == 493){
				blocks++;
			}
		});
		
		/* if(blocks != 0 && ( usr != 7503 && usr != 7630 && usr != 13885 && usr != 17371)){
			
			$(".alert-notify").css({display:"block"});
			$(".alert-notify").html("Essa opção de resgate não está disponivel para os produtos garena");
			if($(".alert-notify").hasClass("alert-success")){
				$(".alert-notify").removeClass("alert-success");
			}
			$(".alert-notify").addClass("alert-danger");
			$(".alert-notify").delay(4000).fadeOut(100);
			
		}else{ */
			 
			$("input:checkbox").each(function()
			{
				if($(this).is(":checked"))
				{
					id = $(this).attr("id");
					var td = $("#td"+id);
					var td2 = $("#td2"+id);
					var txt = td.html();
					if(typeof txt != "undefined")
					{
						
						if(txt.indexOf("glyphicon-print") < 0)
						{
							if(txt.indexOf("Emitido") < 0)
							{
								td.append("<span class='pull-left'>Emitido</span> ");
							}
							td.append("<span class='glyphicon glyphicon-print t0 left' alt='Impresso' title='Impresso'></span>");
						}

						if(td2.html().indexOf("glyphicon-ok") < 0)
						{
							td2.html("<span class='txt-verde glyphicon glyphicon-ok t0'></span>")
						}
					}
				}
			});
			
			$('#imprimir_ou_csv').val('imprimir');
	   
			if(id !== false || iptCheckBox == false)
			{
				document.form1.submit();
			}else
			{
				$(".errorBox").html("Selecione uma opção.");
			}     
			 
		//}
        
   });
   
   $("#downloadPin").click(function(){
	   
		let blocks = 0;
		let usr = <?php echo $controller->usuarios->getId();?>;
		 
		$("input:checkbox:checked").each(function(){
			let id = $(this).attr("id").replace("emitir","");
			let vl = $(this).parent().parent().find("#td3id"+id);
			if(vl.html() == 355 || vl.html() == 374 || vl.html() == 493){
				blocks++; 
				//$(this).parent().css({backgroundColor:"#f2dede"});
				//$(this).parent().delay(2000);
				//$(this).parent().css({backgroundColor:"white"});
			}
		});
	   
		/*if(blocks != 0 && ( usr != 7503 && usr != 7630 && usr != 13885 && usr != 17371)){ 
		
			$(".alert-notify").css({display:"block"});
			$(".alert-notify").html("Essa opção de resgate não está disponivel para os produtos garena");
			if($(".alert-notify").hasClass("alert-success")){
				$(".alert-notify").removeClass("alert-success");
			}
			$(".alert-notify").addClass("alert-danger");
			$(".alert-notify").delay(4000).fadeOut(100);
		
		}else{ */
			
			$('#imprimir_ou_csv').val('csv');
			
			if($(":checkbox:checked").length > 0 || iptCheckBox === false)
			{
					
				$.ajax({
						type: 'POST',
						url: '/creditos/imprimir_cupom.php',
						data: showValues(),
						beforeSend: function(){
							$('#downloadPin').html("<span><i>Iniciando download..</i></span>");
						},
						  
						success: function(html){
							$('#downloadPin').html(html);
							$('#downloadPin').html("<span>Download</span>");
						},
						error: function(){
							alert('Problema no download do PIN');
						}
					  });
			}else
			{
				$(".errorBox").html("Selecione uma opção.");
			}
			
		//}
    });
    
	$(document).on("click", ".depositarPin", function(event){
		
		let check = 0;
		$("input:checkbox").each(function()
        {
			
            if($(this).is(":checked"))
            {
				  if(check == 0){
					    
						if($(this).attr("id") != "checkall"){
							if($("div").hasClass("modal-garena")){
							$(".modal-garena").remove();
							}else{
								let tr = $(this.parentElement.parentElement); 
								let div = $('<div class="modal-garena"><input type="number" id="id_garena" name="id_garena"><span id="span_id_garena">Preencha o campo com o ID da conta do garena que irá receber os créditos</span><button type="button" class="btn btn-success" id="confirmaContaGarena">Confirmar</button></div>');
								tr.css({position:"relative"});
								div.css({position:"absolute", left:"0", zIndex: "1", top:"52px", width: "100%", padding:"15px", backgroundColor:"#eeeeee"});
								tr.append(div);
								//$("#close").css({width:"20px", float:"right"}); 
							}
								
							check = 1;
							$(this.parentElement.parentElement).find(".depositarPin").addClass("btn-click");
							$("#form1").attr("action", "");
							$("#id_garena").css({display:"block"});
							$("#span_id_garena").css({color:"#16a3dc"}); 
							$("#span_id_garena").fadeIn(800);
							$("#confirmaContaGarena").fadeIn(900);	
					  }else{
						    $(".alert-notify").css({display:"block"});
							$(".alert-notify").html("Não é possivel seleciona mais de um pin para o resgate Garena");
							if($(".alert-notify").hasClass("alert-success")){
								$(".alert-notify").removeClass("alert-success");
							}
							$(".alert-notify").addClass("alert-danger");
							$(".alert-notify").delay(4000).fadeOut(800);
							$(this).prop("checked", false);
					  }
					
				}else{
					
					$(this).prop("checked", false);
					
				}
				
            }
			
        });
		
		if(check == 0){
			
			$(".alert-notify").css({display:"block"});
			$(".alert-notify").html("Selecione um pin para o resgate");
			if($(".alert-notify").hasClass("alert-success")){
				$(".alert-notify").removeClass("alert-success");
			}
			$(".alert-notify").addClass("alert-danger");
			$(".alert-notify").delay(4000).fadeOut(800);
			
			if($("div").hasClass("modal-garena")){
				$(".modal-garena").remove();
				$("#id_garena").css({display:"none"});
				$("#span_id_garena").css({display:"none"});
				$("#confirmaContaGarena").css({display:"none"});
			}
			
		}
		
	});
	
	$(document).on("click", "#confirmaContaGarena", function(event){
		
		event.preventDefault();
		idP = new Array();
		let trg = '';
		$("input:checkbox:checked").each(function()
        {
			idP.push($(this).attr("value"));
			trg = $(this.parentElement.parentElement); 
        });
		
		if($("#id_garena").val() != null && $("#id_garena").val() != "" && $("#id_garena").val() != undefined){
				
            if(idP.length == 1){
				
				$.ajax({

					url: "https://www.e-prepag.com.br/ajax/garena/verificaProduto.php",
					method: "POST",
                    data: { codigo: idP, garena: $("#id_garena").val(), type: "pdv", valid: true, vde: vd},
					beforeSend: function(){
						
						$("#confirmaContaGarena").html("Processando...");
						$("#span_id_garena").css({color:"#16a3dc"}); 
						$( "#confirmaContaGarena" ).prop( "disabled", true );
						
					}
					
				}).done(function(result){
					
					let dados = JSON.parse(result);					
					if(dados.hasOwnProperty('Erro')){
						
						$(".alert-notify").css({display:"block"});
						$(".alert-notify").html(dados.Erro);
						if($(".alert-notify").hasClass("alert-success")){
							$(".alert-notify").removeClass("alert-success");
						}
						$(".alert-notify").addClass("alert-danger");
						$(".alert-notify").delay(4000).fadeOut(100);
						
						$("#confirmaContaGarena").html("Confirmar");
						$( "#confirmaContaGarena" ).prop( "disabled", false );
									
				    }else{
					
						Swal.fire({
						  title: 'Confirmação de usuário',
						  html: 'Você é o usuário <b>'+dados[0].nome+'</b> ?',
						  icon: 'question',
						  showDenyButton: true,
						  allowOutsideClick: false,
						  allowEscapeKey: false,
						  confirmButtonColor: '#28a745',
						  denyButtonColor: '#d33',
						  confirmButtonText: 'Confirmar',
						  denyButtonText: 'Cancelar'
						}).then((result) => {
						 
						  if (result.isConfirmed) {
							   
								  $.ajax({

									url: "https://www.e-prepag.com.br/ajax/garena/verificaProduto.php",
									method: "POST",
									data: { codigo: idP, garena: $("#id_garena").val(), type: "pdv", vde: vd},
									beforeSend: function(){
										
										$("#confirmaContaGarena").html("Processando...");
										$( "#confirmaContaGarena" ).prop( "disabled", true );
										
									}
									
								}).done(function(result){
									
									let retorno = JSON.parse(result);
									if(retorno.hasOwnProperty('Erro')){
							
										$(".alert-notify").css({display:"block"});
										$(".alert-notify").html(retorno.Erro);
										if($(".alert-notify").hasClass("alert-success")){
											$(".alert-notify").removeClass("alert-success");
										}
										$(".alert-notify").addClass("alert-danger");
										$(".alert-notify").delay(4000).fadeOut(100);
										
										$("#confirmaContaGarena").html("Confirmar");
										$( "#confirmaContaGarena" ).prop( "disabled", false );
										
									}else{
										
										
										
										$(".alert-notify").css({display:"block"});
										$(".alert-notify").html(retorno.Sucesso);
										if(trg != ''){
											trg.find(".depositarPin").css({display:"none"});
											$(trg.find(".depositarPin").parent().parent().children()[0]).html("<span class='txt-verde glyphicon glyphicon-ok t0'></span>");
											$(trg.find(".depositarPin").parent().parent().children()[6]).html('Data de resgate: <b class="txt-verde">'+retorno.dataUtilizacao+'</b>');
											trg.find(".depositarPin").parent().html("Conta usada: <b class='txt-verde'>" + $("#id_garena").val() +"</b>");
										}
										
										if($(".alert-notify").hasClass("alert-danger")){
											$(".alert-notify").removeClass("alert-danger");
										}
										$(".alert-notify").addClass("alert-success");
										$(".alert-notify").delay(4000).fadeOut(100);
										
										
										if($("div").hasClass("modal-garena")){
											$(".modal-garena").remove();
											$("#confirmaContaGarena").fadeOut();
											$("#id_garena").fadeOut();
											$("#span_id_garena").fadeOut();
											$("#id_garena").val("");
										}
										
										$("#confirmaContaGarena").html("Confirmar");
										$( "#confirmaContaGarena" ).prop( "disabled", false );
										
										
									}
									
								});
							
						  } else if (result.isDenied) {
								
								Swal.fire({
								  title: 'O resgate para o usuário foi cancelado!',
								  allowOutsideClick: false,
						          allowEscapeKey: false,
								  icon: 'info',
								  confirmButtonColor: '#28a745',
								  confirmButtonText: 'Fechar'
								});
								$("#confirmaContaGarena").html("Confirmar");
								$( "#confirmaContaGarena" ).prop( "disabled", false );
									
						  }
						  
					   });
				   }
					
				}).fail(function(result){
					
					$(".alert-notify").css({display:"block"});
					$(".alert-notify").html("Não foi possivel fazer o resgate dos creditos");
					if($(".alert-notify").hasClass("alert-success")){
						$(".alert-notify").removeClass("alert-success");
					}
					$(".alert-notify").addClass("alert-danger");
					$(".alert-notify").delay(4000).fadeOut(100);
					
					$("#confirmaContaGarena").html("Confirmar");
					$( "#confirmaContaGarena" ).prop( "disabled", false );
					
				}); 
				
			}else if(idP.length > 1){
				
				$(".alert-notify").css({display:"block"});
				$(".alert-notify").html("Por favor selecione um pin por vez");
				if($(".alert-notify").hasClass("alert-success")){
					$(".alert-notify").removeClass("alert-success");
				}
				$(".alert-notify").addClass("alert-danger");
				$(".alert-notify").delay(4000).fadeOut(800);
				
			}else{
				
				$(".alert-notify").css({display:"block"});
				$(".alert-notify").html("Nenhum pin foi selecionado");
				if($(".alert-notify").hasClass("alert-success")){
					$(".alert-notify").removeClass("alert-success");
				}
				$(".alert-notify").addClass("alert-danger");
				$(".alert-notify").delay(4000).fadeOut(800);
				
			}
				
		}else{
			$("#span_id_garena").css({color:"red"});
			$("#id_garena").focus();
		}
		
	});
   
});
</script>
<?php
    if($totalPins == $totalRegistros || $totalRegistros > 0) {
?>
                    <p class="txt-vermelho errorBox"></p>
                    <p>
<?php
       if($podeEnviarEmail) {
?>        
                        <button type="button" class="btn btn-success" id="emailPin" title="Clique aqui para enviar o PIN por email">Enviar por e-mail</button>
                        <button type="button" class="btn btn-success"  id="downloadPin" title="Clique aqui para baixar as informações do PIN em formato CSV/Excel" value="">Download</button>
<?php
       } //if($podeEnviarEmail)
		   
    
?>        
             
                        <button type="button" class="btn btn-success" id="imprimirPin">Imprimir</button>
                                  
                    </p>
<?php
    }//end if($totalPins == $totalRegistros || $totalRegistros > 0)
?>
<!-- -->                    
                </div>
            </div>
        </div>
        <div class="col-md-12 row espacamento text-center">
            <a href="/creditos/pedidos.php" class="btn btn-primary">Voltar</a>
        </div>
    </div>
</div>

<?php
}else{
?>
    <div class="container txt-azul-claro bg-branco espacamento">
        <p class="txt-vermelho">Nenhum produto encontrado (ERRO: WM111).</p>
    </div>
<?php
}
require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/footer.php";
