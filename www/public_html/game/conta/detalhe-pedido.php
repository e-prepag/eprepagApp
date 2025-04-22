<?php
require_once "../../../includes/constantes.php";
require_once "../../../includes/functions.php";
require_once DIR_CLASS . "gamer/controller/HeaderController.class.php";

$posicao = "Inferior Internas";
$controller = new HeaderController;
$banners = $controller->getBanner($posicao);

$controller->setHeader();


//Recupera usuario
if(isset($_SESSION['usuarioGames_ser']) && !is_null($_SESSION['usuarioGames_ser'])){
        $usuarioGames = unserialize($_SESSION['usuarioGames_ser']);
        $usuarioId = $usuarioGames->getId();
}
require_once DIR_INCS . "inc_register_globals.php";

if(!isset($venda_id)){
    $msg = "ID da venda não foi informado.";
}
//Recupera modelos
if($msg == ""){
    //Inicializando conexao PDO
    $con = ConnectionPDO::getConnection();
    $pdo = $con->getLink();
    
        $sql  = "select * from tb_venda_games vg " .
                        "inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id where vg.vg_id = " . $venda_id . " and vg_ug_id = ".$controller->usuario->getId();
     
        //Tentando executar a Query de Insert
        $stmt = $pdo->prepare($sql);
		
		$key = getEnvVariable('ENCRYPT_KEY');
		$plaintext = $venda_id;
		$ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
		$iv = openssl_random_pseudo_bytes($ivlen);
        // file deepcode ignore HardcodedNonCryptoSecret: <Precisa implementar um sistema de variáveis de ambiente no servidor da e-prepag>
		$ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);	
        // file deepcode ignore HardcodedNonCryptoSecret: <Precisa implementar um sistema de variáveis de ambiente no servidor da e-prepag>
        $hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
		$idVendCalc = base64_encode( $iv.$hmac.$ciphertext_raw );
				
		//$idVendCalc = (($venda_id * 5)/3)-90;

        if($stmt->execute()){
            $num_rows = $stmt->rowCount();
            
            if($num_rows > 0){
                $rs_venda_modelos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $total_geral = 0; $qtde_itens = 0; $qtde_produtos = 0;
                
                foreach($rs_venda_modelos as $ind => $rs_venda_modelos_row){
//                while ($rs_venda_modelos_row  = $rs_venda_modelos->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)){
                    
                        $vg_concilia = $rs_venda_modelos_row['vg_concilia'];
                        $vg_ultimo_status = $rs_venda_modelos_row['vg_ultimo_status'];
                        $vg_pagto_banco = $rs_venda_modelos_row['vg_pagto_banco'];
                        $vg_pagto_num_docto = $rs_venda_modelos_row['vg_pagto_num_docto'];
                        $vg_pagto_tipo = $rs_venda_modelos_row['vg_pagto_tipo'];
                        $vg_user_id_concilia = trim($rs_venda_modelos_row['vg_user_id_concilia']);
                        $qtde = $rs_venda_modelos_row['vgm_qtde'];
                        $valor = $rs_venda_modelos_row['vgm_valor'];
                        $total_geral += $valor*$qtde;
                        $qtde_itens += $qtde;
                        $qtde_produtos += 1;
                        
                        $pagto_num_docto 	 = explode("\|", $vg_pagto_num_docto);
                }

            }else{
                $msg = "Nenhum produto encontrado (1w3).\n";
            }
        }else{
            $msg = "Nenhum produto encontrado (1w32).";
        }
}

//Recupera dados da forma de pagamento
if($msg == ""){

        if($vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO']){
                $sql  = "select * from boleto_bancario_games bbg " .
                                "where bbg.bbg_vg_id = " . $venda_id;
                
                $stmt = $pdo->prepare($sql);

                if($stmt->execute()){
                    if($stmt->rowCount() > 0){
                        $rs_boleto_row = $stmt->fetch(PDO::FETCH_ASSOC);
                        $bbg_boleto_codigo = $rs_boleto_row['bbg_boleto_codigo'];
                        $bbg_data_inclusao = $rs_boleto_row['bbg_data_inclusao'];
                        $bbg_bco_codigo = $rs_boleto_row['bbg_bco_codigo'];
                        $bbg_documento = $rs_boleto_row['bbg_documento'];
                        $bbg_valor = $rs_boleto_row['bbg_valor'];
                        $bbg_valor_taxa = $rs_boleto_row['bbg_valor_taxa'];
                        $bbg_data_venc = $rs_boleto_row['bbg_data_venc'];
                        $bbg_data_pago = $rs_boleto_row['bbg_data_pago'];
                        $bbg_pago = $rs_boleto_row['bbg_pago'];
                    }else{
                        $msg = "Nenhum redecard encontrado.\n";
                    }
                }else{
                    $msg = "Nenhum redecard encontrado.\n";
                }
        } elseif($vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['REDECARD_MASTERCARD'] || $vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['REDECARD_DINERS']){
                $sql  = "select * from tb_venda_games_redecard vgrc " .
                                "where vgrc.vgrc_vg_id = " . $venda_id;
                
                $stmt = $pdo->prepare($sql);

                if($stmt->execute()){
                    if($stmt->rowCount() > 0){
                        $rs_redecard_row = $stmt->fetch(PDO::FETCH_ASSOC);
                        $vgrc_id = $rs_redecard_row['vgrc_id'];
                        $vgrc_vg_id = $rs_redecard_row['vgrc_vg_id'];
                        $vgrc_ug_id = $rs_redecard_row['vgrc_ug_id'];
                        $vgrc_parcelas = $rs_redecard_row['vgrc_parcelas'];
                        $vgrc_data_inclusao = $rs_redecard_row['vgrc_data_inclusao'];
                        $vgrc_total = $rs_redecard_row['vgrc_total'];
                        $vgrc_transacao = $rs_redecard_row['vgrc_transacao'];
                        $vgrc_bandeira = $rs_redecard_row['vgrc_bandeira'];
                        $vgrc_codver = $rs_redecard_row['vgrc_codver'];
                        $vgrc_data_envio1 = $rs_redecard_row['vgrc_data_envio1'];
                        $vgrc_ret2_data = $rs_redecard_row['vgrc_ret2_data'];
                        $vgrc_ret2_nr_cartao = $rs_redecard_row['vgrc_ret2_nr_cartao'];
                        $vgrc_ret2_origem_bin = $rs_redecard_row['vgrc_ret2_origem_bin'];
                        $vgrc_ret2_numautor = $rs_redecard_row['vgrc_ret2_numautor'];
                        $vgrc_ret2_numcv = $rs_redecard_row['vgrc_ret2_numcv'];
                        $vgrc_ret2_numautent = $rs_redecard_row['vgrc_ret2_numautent'];
                        $vgrc_ret2_numsqn = $rs_redecard_row['vgrc_ret2_numsqn'];
                        $vgrc_ret2_codret = $rs_redecard_row['vgrc_ret2_codret'];
                        $vgrc_ret2_msgret = $rs_redecard_row['vgrc_ret2_msgret'];
                        $vgrc_ret4_ret = $rs_redecard_row['vgrc_ret4_ret'];
                        $vgrc_ret4_codret = $rs_redecard_row['vgrc_ret4_codret'];
                        $vgrc_ret4_msgret = $rs_redecard_row['vgrc_ret4_msgret'];
                        $vgrc_usuario_ip = $rs_redecard_row['vgrc_usuario_ip'];
                        $vgrc_ret2_endereco = $rs_redecard_row['vgrc_ret2_endereco'];
                        $vgrc_ret2_numero = $rs_redecard_row['vgrc_ret2_numero'];
                        $vgrc_ret2_complemento = $rs_redecard_row['vgrc_ret2_complemento'];
                        $vgrc_ret2_cep = $rs_redecard_row['vgrc_ret2_cep'];
                        $vgrc_ret2_respavs = $rs_redecard_row['vgrc_ret2_respavs'];
                        $vgrc_ret2_msgavs = $rs_redecard_row['vgrc_ret2_msgavs'];

                        $vgrc_ret2_numprg = $rs_redecard_row['vgrc_ret2_numprg'];
                        $vgrc_ret2_nr_hash_cartao = $rs_redecard_row['vgrc_ret2_nr_hash_cartao'];
                        $vgrc_ret2_cod_banco = $rs_redecard_row['vgrc_ret2_cod_banco'];
                    }
                }

        }
}


//Se conciliado, Recupera dados do usuario que conciliou
if($msg == ""){

        if($vg_concilia == 1){

                if($vg_user_id_concilia == ""){
                        $shn_nome = "Anonymous";
                } else {
                        $sql  = "select * from usuarios urpp " .
                                        "where urpp.id = '" . $vg_user_id_concilia . "'";
                        
                        
                        $stmt = $pdo->prepare($sql);

                        if($stmt->execute()){
                            if($stmt->rowCount() > 0){
                                
                                $rs_urpp_row = $stmt->fetch(PDO::FETCH_ASSOC);
                                $shn_nome = $rs_urpp_row['shn_nome'];
                            }else{
                                $shn_nome = "Anonymous";
                            }
                        
                        }else{
                            $shn_nome = "Anonymous";
                        }
                        
                }
        }
} 

$msg = $msgConciliaUsuario . $msg;

$cor1 = "#F5F5FB";
$cor2 = "#F5F5FB";
$cor3 = "#FFFFFF"; 	


ob_end_flush();

if($vg_ultimo_status == 1){
    $color = "txt-amarelo";
}else if($vg_ultimo_status == 2){
    $color = "txt-amarelo";
}else if($vg_ultimo_status == 3){
    $color = "txt-verde";
}else if($vg_ultimo_status == 4){
    $color = "txt-verde";
}else if($vg_ultimo_status == 5){
    $color = "txt-verde";
}else if($vg_ultimo_status == 6){
    $color = "txt-vermelho";
}



?>
<div class="container txt-cinza bg-branco  p-bottom40">
    <div class="row top20">
            <div class="col-md-3 txt-azul-claro">
                <div class="row">
                    <span class="glyphicon glyphicon-triangle-right graphycon-big pull-left" aria-hidden="true"></span><strong class="pull-left"><h4 class="top20">Minha Conta</h4></strong>
                </div>
                <div class="row">
                   <?php require_once RAIZ_DO_PROJETO . "public_html/game/includes/menu-carteira.php"?>
                </div>
            </div>
            <div class="col-md-9 txt-azul-claro">
                <div class="row">
                    <strong class="pull-left p-left15 top20"><strong>PEDIDOS / DEPOSITOS</strong></strong>
                </div>
            <div class="row txt-cinza">
                <div class="col-md-12 top20">
                    <h4 class="margin004"><strong>Detalhe de pedido</strong></h4>
                    <p class="margin004 txt-azul-claro"><strong>Pedido: <?php echo $venda_id ?></strong></p>
                    <p class="margin004 txt-azul-claro"><strong>Status: <span class="<?=$color?>"><?php echo $GLOBALS['STATUS_VENDA_DESCRICAO'][$vg_ultimo_status] ?></span></strong></p>
					<?php
					
					     $sqlGarena = "select count(*) as tot from tb_venda_games_modelo where vgm_opr_codigo = 124 and vgm_vg_id =" . $venda_id;
						 $dadosGarena = $pdo->prepare($sqlGarena);
						 $dadosGarena->execute();
						 $totalGarena = $dadosGarena->fetch(PDO::FETCH_ASSOC);
						 //$dadosGarena = SQLexecuteQuery($sqlGarena);
						 //$totalGarena = pg_fetch_array($dadosGarena); 
						
						 if($totalGarena["tot"] > 0){
							 echo '<div style="margin-top:5px;"><a target="_blank" href="https://www.e-prepag.com.br/resgate/garena/creditos.php" style="display:block;width: 200px;" class="btn btn-success copiar">link para resgate Garena</a></div>';
						 }
					 
					?>
                </div>
            </div>
			<div class="col-md-10">
                <div class="alert alert-notify" role="alert"></div>
            </div>
            <div class="row txt-cinza espacamento">
                <div class="col-md-12 bg-cinza-claro"> 
                    <table class="table bg-branco txt-preto text-center">
                        <thead>
                          <tr class="bg-cinza-claro">
                            <th class="text-left">Produto</th>
                            <th>Valor unitário</th>
                            <th>Qtde.</th>
                            <th>Total</th>
                          </tr>
                        </thead>
                        <tbody>
<?php
                            $total_geral = 0;
                        if(isset($num_rows) && $num_rows > 0){
                            foreach($rs_venda_modelos as $ind => $rs_venda_modelos_row){

                                $qtde = $rs_venda_modelos_row['vgm_qtde'];
                                $valor = $rs_venda_modelos_row['vgm_valor'];
                                $total_geral += $valor*$qtde;
?>
                            <tr class="trListagem">
                              <td class="text-left">
                                <?php echo $rs_venda_modelos_row['vgm_nome_produto'];
                                if($rs_venda_modelos_row['vgm_nome_modelo']!=""){ echo " - ".$rs_venda_modelos_row['vgm_nome_modelo']; }?>
                              </td>
                              <td class="text-center"><?php echo number_format($valor, 2, ',', '.')?></td>
                              <td class="text-center"><?php echo $qtde?></td>
                              <td class="text-center"><?php echo number_format($valor*$qtde, 2, ',', '.')?></td>
                            </tr>
<?php	
                            }
?>
                            <tr class="bg-cinza-claro">
                              <td colspan="2"></td>
                              <td class="text-center"><b>Total</b></td>
                              <td class="text-center"><b><?php echo number_format($total_geral, 2, ',', '.')?></b></td>
                            </tr>
<?php
                        }else{
?>
                            <tr class="bg-cinza-claro">
                              <td colspan="4">Venda não encontrada.</td>
                            </tr>
<?php
                        }
?>
                        </tbody>
                    </table>
<?php
                    if(	$vg_pagto_tipo == $FORMAS_PAGAMENTO['BOLETO_BANCARIO'] &&
                                    ($vg_ultimo_status == $STATUS_VENDA['PEDIDO_EFETUADO'] ||
                                    $vg_ultimo_status == $STATUS_VENDA['DADOS_PAGTO_RECEBIDO'])) {

                        switch ($vg_pagto_banco) {
                            case $BOLETO_MONEY_BANCO_ITAU_COD_BANCO:
                                $sboletoURL = "/SICOB/BoletoWebItauCommerce.php";
                                break;
                            case $BOLETO_MONEY_CAIXA_COD_BANCO:
                                $sboletoURL = "/SICOB/BoletoWebCaixaCommerce.php";
                                break;
                            case $BOLETO_MONEY_BANCO_BANESPA_COD_BANCO:
                                $sboletoURL = '/SICOB/BoletoWebBanespaCommerce.php';
                                break;
                            case $BOLETO_MONEY_BRADESCO_COD_BANCO:
                                $sboletoURL = "/boletos/gamer/boletos_bradesco.php";
                                break;
                            default:
                                $sboletoURL = "";
                                break;
                        }
?>
<?php    
                        if ($sboletoURL) {
                            
                            $token = date('YmdHis', strtotime("+20 day")) . "," . $_POST['venda_id'] . "," . $controller->usuario->getId();
                            $objEncryption = new Encryption();
                            $token = $objEncryption->encrypt($token);
?>
                            <div class="espacamento text-center">
                                <a class="btn btn-success" href="<?php echo htmlspecialchars($sboletoURL, ENT_QUOTES, 'UTF-8'); ?>?token=<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>" class="link_azul" target="_blank"><i>Gerar Boleto</i></a>
                            </div>
<?php 
                        } else { 
                            echo "Sem boleto";
                        } 
                    } 
                    
                    if($vg_ultimo_status == $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']) {

                        foreach($rs_venda_modelos as $ind => $rs_venda_modelos_row){
                            $vgm_pin_codinterno = $rs_venda_modelos_row['vgm_pin_codinterno'];
                            $vgm_opr_codigo = $rs_venda_modelos_row['vgm_opr_codigo'];
                            $vgm_valor = $rs_venda_modelos_row['vgm_valor'];
							
                            //Formatação					
                            $labSenha = "Senha";
                            if($vgm_opr_codigo == 16) $labSenha = "Habbo Crédito";

                            //elimina ultima virgula
                            if(substr($vgm_pin_codinterno, -1) == ",") $vgm_pin_codinterno = substr($vgm_pin_codinterno, 0, strlen($vgm_pin_codinterno) - 1);

                            //separa os ids dos pins
                            $vgm_pin_codinternoAr = explode(",", $vgm_pin_codinterno);

                            //verifica se o(s) pin(s) foram associados ao modelo
                            if(count($vgm_pin_codinternoAr) > 0){

                          

                                //Realiza n qtde de venda de pins
                                for($i=0; $i < count($vgm_pin_codinternoAr); $i++){

                                    // Executa uma verificação se o a senha do pin é zerada, se for exibe o campo pin_caracter	
                                    $sql = "select *, 
                                                CASE WHEN pin_codigo = '0000000000000000' THEN pin_caracter
                                                ELSE pin_codigo
                                                END as case_serial
                                        from pins
                                        where pin_codinterno = " . (($vgm_pin_codinternoAr[$i])?$vgm_pin_codinternoAr[$i]:0) . "";
										
									if($rs_venda_modelos_row["vgm_opr_codigo"] == 124 ){
										$garena = true;
									}
									
                                    $rs_pin = SQLexecuteQuery($sql);
                                    if(!$rs_pin || pg_num_rows($rs_pin) == 0) 
                                        $msg = "PIN não encontrado.\n";
                                    else {
                                        $pgpin = pg_fetch_array($rs_pin);
                                        $pin_codinterno = $pgpin['pin_codinterno'];
                                        $pin_serial = $pgpin['pin_serial'];
										$opr_codigo = $pgpin['opr_codigo'];
										$case_serial = $pgpin['case_serial'];
										$contaGARENA = $pgpin['pin_game_id'];
										$pin_status_g = $pgpin['pin_status'];
										$guidEPP = $pgpin['pin_guid_epp'];

                                        $sqlDataUtiliza = "select pih_data from pins_integracao_historico where pih_pin_id = $pin_codinterno";
										$resData = SQLexecuteQuery($sqlDataUtiliza);
										$dataUtil = pg_fetch_array($resData);
									
                                        if($opr_codigo==$GLOBALS['opr_codigo_Alawar']) {
                                            //	select pa_data_transacao, pa_activation_key, pa_pag_id, * from pins_alawar where pa_certificate_id = '1256704180550'
                                            $sql2 = "select * from pins_alawar where pa_certificate_id = '$case_serial';";
                                            $rs_pin_alawar = SQLexecuteQuery($sql2);
                                            if(!$rs_pin_alawar || pg_num_rows($rs_pin_alawar) == 0) $msg = "Activation key Alawar não encontrado.\n";
                                            else {
                                                $pgpin_alawar = pg_fetch_array($rs_pin_alawar);
                                                $case_serial = $pgpin_alawar['pa_activation_key'];
                                            }
                                        }

                                        //Formatacao							
                                        if($vgm_opr_codigo == 13) $case_serial = wordwrap($case_serial, 4, " ", true);
                                    }
                                    if(empty($msg)) {
?>
                            <div class="espacamento container-g">
                                <div class="row">
                                    <div class="col-md-12">
                                        <strong><?php echo $rs_venda_modelos_row['vgm_nome_produto']?> 
                                        <?php if($rs_venda_modelos_row['vgm_nome_modelo']!=""){?> - <?php echo $rs_venda_modelos_row['vgm_nome_modelo']?><?php } ?></strong>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">PIN:</div>
                                    <div class="col-md-3"><?php echo $case_serial; ?></div>
									<div style="display: none;" class="col-md-3 pin"><?php echo $pin_codinterno; ?></div>
									<?php
										if($contaGARENA == "" && $pin_status_g == 3 && $contaGARENA == null && $guidEPP == "" && $guidEPP == null && isset($garena) && $garena === true){ //$controller->usuario->getId() == 1286357 && 
								    ?> 
									        <div class="col-md-3"><button type="button" class="btn btn-success depositarPin" id="depositarPin">Resgatar</button></div>
									<?php	
									        unset($garena);
								
										}
								    ?>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">Nº de série:</div>
                                    <div class="col-md-5"><?php echo $pin_serial ?></div>
                                </div>
								<?php
									  // if($controller->usuario->getId() == 1286357){
										   if($contaGARENA != "" && $contaGARENA != null && $dataUtil != "" && $dataUtil != null){
											   
								 ?> 
									<div class="row">
										<div class="col-md-3">Conta usada:</div>
										<div class="col-md-5 txt-verde"><b><?php echo $contaGARENA;?></b></div>
									</div>
									<div class="row">
										<div class="col-md-3">Data de resgate:</div>
										<div class="col-md-5 txt-verde"><b><?php echo substr($dataUtil["pih_data"], 8, 2)."/".substr($dataUtil["pih_data"], 5, 2)."/".substr($dataUtil["pih_data"], 0, 4)."-".substr($dataUtil["pih_data"], 11, 8);?></b></div>
									</div>
								<?php
								            }
									 //  }
								 ?> 
								
								
                            </div>
<?php	
                                    }//end if(!empty($msg))
                                    $msg = NULL;
                                }						
                            }
                        } 
?>
                <?php
					//	if($controller->usuario->getId() == 1286357){
				?>
					<div class="row">
						<div style="display: none;" class="col-md-12 col-conta">  
							<h4><b>Digite a sua conta garena</b></h4>
							<input style="color: black;" type="number" name="useridgarena" id="UserGarena">
							<span style="display: none;" id="msgUser">Preencha o campo com o ID da conta do garena que irá receber os créditos</span>
							<button style="display: block;margin:10px 0;" class="btn btn-success btn-confirma" type="button">Confirmar</button>
						</div> 
					</div>  
				<?php	
					//	}
				?>
				</table>
			</td>
		  </tr>
		</table>

<?php	
                }  
?>
            </div>
        </div>
    </div>
<?php
    if(!empty($banners)){
?>
    <div class="col-md-12 top10">
        <a href='<?php echo $banners[0]->link; ?>' target="_blank">
            <img title="<?php echo $banners[0]->titulo; ?>" alt="<?php echo $banners[0]->titulo; ?>" class="img-responsive" src="<?php echo $controller->objBanners->urlLink.$banners[0]->imagem; ?>">
        </a>
    </div>
<?php 
    } 
?>
</div>
</div>
</div>

<script>

    <?php
		//if($controller->usuario->getId() == 1286357){
		
	?>
	
	    let c = '';
		let b = '';
		let vd = "<?php echo $idVendCalc; ?>"; 
	    $(document).on("click", ".depositarPin", function(event){
			
			$(".col-conta").css({display:"block"});
			b = $(event.target);
			c = $(event.target).parent().parent().find(".pin").html();
			
		});
		
		$(document).on("click", ".btn-confirma", function(event){
			
            if($("#UserGarena").val() == "" || $("#UserGarena").val() == null){
				$("#msgUser").css({color:"red"});
				$("#msgUser").css({display:"inline-block"});
			    $("#UserGarena").focus();
			}else{
				
				$("#msgUser").css({color:"#268fbd"});
				$.ajax({

					url: "https://www.e-prepag.com.br/ajax/garena/verificaProduto.php",
					method: "POST",
                    data: { codigo: [c], garena: $("#UserGarena").val(), type: "usuario", valid: true, vde: vd},
					beforeSend: function(){
						
						$(".btn-confirma").html("Processando...");
						$( ".btn-confirma" ).prop( "disabled", true );
						
					}
					
				}).done(function(result){
					console.log(result);	
					let dados = JSON.parse(result);
					if(dados.hasOwnProperty('Erro')){
						
						$(".alert-notify").css({display:"block"});
						$(".alert-notify").html(dados.Erro);
						if($(".alert-notify").hasClass("alert-success")){
							$(".alert-notify").removeClass("alert-success");
						}
						$(".alert-notify").addClass("alert-danger");
						$(".alert-notify").delay(4000).fadeOut(100);
						
						$(".btn-confirma").html("Confirmar");
						$( ".btn-confirma" ).prop( "disabled", false );
						
					}else{
						
						Swal.fire({
						  title: 'Confirmação de usuário',
						  html: 'Você é o usuário <b>'+dados[0].nome+'</b> ?',
						  icon: 'question',
						  allowOutsideClick: false,
						  allowEscapeKey: false,
						  showDenyButton: true,
						  confirmButtonColor: '#28a745',
						  denyButtonColor: '#d33',
						  confirmButtonText: 'Confirmar',
						  denyButtonText: 'Cancelar'
						}).then((result) => {
						 
						  if (result.isConfirmed) {
							  
							  $.ajax({

								url: "https://www.e-prepag.com.br/ajax/garena/verificaProduto.php",
								method: "POST",
								data: { codigo: [c], garena: $("#UserGarena").val(), type: "usuario", vde: vd},
								beforeSend: function(){
									
									$(".btn-confirma").html("Processando...");
									$( ".btn-confirma" ).prop( "disabled", true );
									
								}
								
							}).done(function(dadosReturn){
								console.log(dadosReturn);
								let dadosRet = JSON.parse(dadosReturn);
								if(dadosRet.hasOwnProperty('Erro')){
						
									$(".alert-notify").css({display:"block"});
									$(".alert-notify").html(dadosRet.Erro);
									if($(".alert-notify").hasClass("alert-success")){
										$(".alert-notify").removeClass("alert-success");
									}
									$(".alert-notify").addClass("alert-danger");
									$(".alert-notify").delay(4000).fadeOut(100);
									
									$(".btn-confirma").html("Confirmar");
									$( ".btn-confirma" ).prop( "disabled", false );
									
								}else{
									
									$(".alert-notify").css({display:"block"});
									$(".alert-notify").html(dadosRet.Sucesso);
									if($(".alert-notify").hasClass("alert-danger")){
										$(".alert-notify").removeClass("alert-danger");
									}
									$(".alert-notify").addClass("alert-success");
									$(".alert-notify").delay(4000).fadeOut(100);
									$(b.parent().parent().parent()).append($('<div class="row"><div class="col-md-3">Conta usada:</div><div class="col-md-5 txt-verde"><b>'+$("#UserGarena").val()+'</b></div></div>'));
									$(b.parent().parent().parent()).append($('<div class="row"><div class="col-md-3">Data de resgate:</div><div class="col-md-5 txt-verde"><b>'+dadosRet.dataUtilizacao+'</b></div></div>'));
									$(b).css({display:"none"});
									$(".col-conta").fadeOut();
									$(".btn-confirma").fadeOut();
									$("#UserGarena").fadeOut();
									$("#UserGarena").val("");
									$("#msgUser").fadeOut();
									
								}
								
							});
							  
						  }else if (result.isDenied) {
								
								Swal.fire({
								  title: 'O resgate para o usuário foi cancelado!',
								  icon: 'info',
								  allowOutsideClick: false,
						          allowEscapeKey: false,
								  confirmButtonColor: '#28a745',
								  confirmButtonText: 'Fechar'
								});
								$(".btn-confirma").html("Confirmar");
					            $( ".btn-confirma" ).prop( "disabled", false );
								
								$(".col-conta").fadeIn();
								$(".btn-confirma").fadeIn();
								$("#UserGarena").fadeIn();
								
						 }
						  
					    });
						
					}
					
					
				}).fail(function(){
					$(".btn-confirma").html("Confirmar");
					$( ".btn-confirma" ).prop( "disabled", false );
				});
				
				
			}
			
		});
		
	   
	<?php	
	//	}
	?>

</script>
<?php
require_once RAIZ_DO_PROJETO . "public_html/game/includes/footer.php";