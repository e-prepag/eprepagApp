<?php require_once __DIR__ . '/../../../../includes/constantes_url.php'; ?>
<?php
ob_start();
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
require_once "../../../../includes/constantes.php";
require_once $raiz_do_projeto . "public_html/sys/includes/topo_sys.php";
require_once $raiz_do_projeto . "public_html/sys/includes/gamer/inc_pub_access.php";
require_once $raiz_do_projeto . "class/classIntegracaoPinCard.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";
require_once $raiz_do_projeto."includes/gamer/chave.php";
require_once $raiz_do_projeto."includes/gamer/AES.class.php";

set_time_limit ( 3000 ) ;

$operacao_array_tmp	= VetorDistribuidorasCard();


?>
<link rel="stylesheet" href="/sys/css/css.css" type="text/css">
<script language="JavaScript" type="text/JavaScript">
<!--
function validade() {
    if(document.form1.fpin.value == '') {
        alert('Por favor, informa o PIN desejado.');
        return false;
    } 
    else {
        return true;
    }
}//end function validade() 

//-->
</script>
<body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<!-- INICIO CODIGO NOVO -->
<link href="/includes/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/js/jquery.js"></script>
<script type="text/javascript" src="/includes/bootstrap/js/bootstrap.min.js"></script>
<div class="container-fluid">
    <div class="container txt-azul-claro bg-branco">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12 ">
                        <strong><?php echo LANG_PINS_PAGE_TITLE; ?></strong>
                    </div>
                </div>
                <div class="row txt-cinza espacamento">
                    <div class="col-md-6">
                        <span class="pull-left"><strong><?php echo LANG_PINS_SEARCH_1; ?></strong> (<?php echo LANG_PINS_AVARAGE_OF.' '.$days_for_mean.' '.LANG_PINS_DAYS; ?>)</span>
                    </div>
                    <div class="col-md-6">
                        <span class="pull-right"><a href="/sys/admin/commerce/index.php" class="btn btn-primary"><strong><i><?php echo LANG_BACK; ?></i></strong></a></span>
                    </div>
                </div>
                <form name="form1" id="form1" method="post" action="" onSubmit="return validade()">
                <div class="row txt-cinza espacamento">
                    <div class="col-md-2">
                        <span class="pull-right"><?php echo LANG_PINS_PIN_NUMBER; ?></span>
                    </div>
                    <div class="col-md-3">
                        <input name="fpin" type="text" class="form-control" id="fpin" value="<?php  echo (isset($_GET["pin"]) && !$BtnSearch)? $_GET["pin"]: $fpin; ?>" size="30" maxlength="40">
                    </div>
                    <div class="col-md-2">
                        <input type="submit" name="BtnSearch" value="<?php echo LANG_PINS_SEARCH_2; ?>" class="btn pull-left btn-success">
                    </div>
                </div>
                </form>
            </div>
        </div>
    
<?php
if(($BtnSearch && !empty($fpin)) || (isset($_GET["pin"]) && !empty($_GET["pin"]))) {  
        
		if(isset($_GET["pin"]) && !empty($_GET["pin"]) && !$BtnSearch){
			$fpin = $_GET["pin"];
		}
        //Variavel de verificação de PIN Cartão
        $isPINCARD = RetonaTamanhoPINEPPCARD(trim($fpin));
        if($isPINCARD) {
            if(empty($GLOBALS['_SESSION']['opr_codigo_pub'])) {
                if($_SESSION["tipo_acesso_pub"]=='AT') {
                    //Variavel contendo o ID do PIN GiftCard
                    $idGiftCard = retorna_id_pin_card_para_adm_bo(trim($fpin));
                }//end if($_SESSION["tipo_acesso_pub"]=='AT')
                else {
                    //Variavel contendo o ID do PIN GiftCard
                    $idGiftCard = 0;
                }//end else do if($_SESSION["tipo_acesso_pub"]=='AT')
            }//end if(!empty($GLOBALS['_SESSION']['opr_codigo_pub']))
            else {
                //Variavel contendo o ID do PIN GiftCard
                $idGiftCard = retorna_id_pin_card(trim($fpin),$GLOBALS['_SESSION']['opr_codigo_pub']);
            }//end else do if(empty($GLOBALS['_SESSION']['opr_codigo_pub']))
            
            $sql = "select 
                    opr_codigo,
                    ".trim($fpin)." as pin_codigo,
                    pin_dataentrada,
                    pin_valor,
                    pin_status,
                    'GIFT CARD -'||pin_formato as opr_pin_epp_formato
                from pins_card 
                where pin_codinterno = ".$idGiftCard.";";
            
            //Carregando includes conforme Distribuidor
            //Variavel contendo o Código do Distribuidor
            $cod_distrib = retornaID_Distibuidora(trim($fpin));
            
            //Arquivo contendo o Include Dinâmico
            $tmp_arq = $raiz_do_projeto . "partners_cards/".$operacao_array_tmp[$cod_distrib]."/config.inc.".$operacao_array_tmp[$cod_distrib].".php";
            
            //Testando se o PIN pertence a algum distribuidor integrado
            if(array_key_exists($cod_distrib, $operacao_array_tmp) && file_exists($tmp_arq)) {
                
                    //incluindo a classe dinamicamente de acordo com o PIN informado
                    require_once ($tmp_arq);
                    
            } //end if(array_key_exists($cod_distrib, $operacao_array_tmp) && file_exists($tmp_arq))
            
        }//end if($isPINCARD)
        else {
            $sql = "select *  
                    from pins left join trava_qtde_pin on pin_codigo = pin 
                    where pin_codigo = '".trim($fpin)."' ";

            if(!empty($GLOBALS['_SESSION']['opr_codigo_pub'])) {
                $sql .= " and opr_codigo = ".$GLOBALS['_SESSION']['opr_codigo_pub']." ";
            }//end if(!empty($GLOBALS['_SESSION']['opr_codigo_pub']))
            $sql .= "order by data_inclusao asc limit 1";
        } //end else do if
        
        //echo "(R) ".str_replace("\n","<br>\n",$sql)."<br>\n<hr>";
        
        $rs_pin = SQLexecuteQuery($sql);
        
        if($rs_pin && pg_num_rows($rs_pin) > 0) {
       ?>
        <div class="row txt-cinza espacamento">
            <div class="col-md-12 bg-cinza-claro">
			    <div class="alert" id="alert-message" style="display: none;margin-top: 10px;" role="alert"></div>
                <table class="table bg-branco txt-preto fontsize-p" id="ReportTable">
                <thead>
                <tr class="bg-cinza-claro">

<?php
            $colspan = 11;
            if($_SESSION["tipo_acesso_pub"]=='AT') 
            {
                $colspan++;
?>
                    <th>Destravar</th>
                    <th class="text-center"><strong>PUBLISHER</strong></th>
					<th class="text-center"><strong>Display txn_id</strong></th>
					<th class="text-center"><strong>Conta Utilizada</strong></th>
					<th class="text-center"><strong>Tentativas de resgate</strong></th>
					<th class="text-center"><strong>Último status integração</strong></th> 
<?php
                $sql_opr = "select opr_pin_epp_formato,opr_nome,opr_codigo from operadoras where opr_pin_epp_formato is not null order by opr_nome";
                $rs_oper = SQLexecuteQuery($sql_opr);
                if($rs_oper) 
                {
                    while ($rs_oper_row = pg_fetch_array($rs_oper)) 
                    {
                        $operacao_array[$rs_oper_row['opr_codigo']]=$rs_oper_row['opr_nome'].' - Formato ('.$rs_oper_row['opr_pin_epp_formato'].')';
                    }					
                }
            } //end if($_SESSION["tipo_acesso_pub"]=='AT')
             ?>
                    <th class="text-center"><strong><?php echo LANG_PINS_PIN_NUMBER; ?></strong></th>
                    <th class="text-center"><strong><?php echo LANG_PINS_CREATE_DATE; ?></strong></th>
                    <th class="text-center"><strong><?php echo LANG_PINS_SALES_DATE; ?></strong></th>
                    <th class="text-center"><strong><?php echo LANG_PINS_USED_DATE; ?></strong></th>
                    <th class="text-center"><strong><?php echo LANG_PINS_VALUE; ?></strong></th>
                    <th class="text-center"><strong><?php echo LANG_PINS_LAST_STATUS; ?></strong></th>
                </thead> 
                </tr>
              <?php 
                    
                    while($rs_pin_row = pg_fetch_array($rs_pin)) {
                            $valor = 1;
                           			
                            if($isPINCARD) {
                                    if($rs_pin_row['pin_status'] == intval($PINS_STORE_STATUS_VALUES['U'])) {
                                        //Capturando a data da Utilização
                                        $sql = "select MIN(pih_data) as pih_data from pins_integracao_card_historico where pin_status = '".intval($PINS_STORE_STATUS_VALUES['U'])."'  and pih_codretepp = '2' and pih_pin_id =".$idGiftCard." ;";
                                        //echo "<!-- $sql -->";
                                        $rs_pin_utilizado = SQLexecuteQuery($sql);
                                        $rs_pin_utilizado_row = pg_fetch_array($rs_pin_utilizado);
                                        $data_utilizado_aux = substr($rs_pin_utilizado_row['pih_data'],0,19);
                                        
                                        //Capturando a Data da Venda
                                        $teste = new $operacao_array_tmp[$cod_distrib];
                                        $params_distributor = array(
                                                        'pin'		=> trim($fpin),
                                                        );
                                        $teste->Req_EfetuaConsulta($params_distributor,$resposta, INQUIRY);
                                        //echo "<div align='left'><pre>".print_r($resposta,true)."</pre></div>";
                                        
                                        $data_vendido_aux = (is_null($teste->RetornaDataAtivacaoPINnoCaixa($resposta))?"--":$teste->RetornaDataAtivacaoPINnoCaixa($resposta));

                                    }//end if($rs_pin_row['pin_status'] == intval($PINS_STORE_STATUS_VALUES['U'])) 
                                    elseif($rs_pin_row['pin_status'] == intval($PINS_STORE_STATUS_VALUES['A'])) {
                                        //Capturando a Data da Venda
                                        $teste = new $operacao_array_tmp[$cod_distrib];
                                        $params_distributor = array(
                                                        'pin'		=> trim($fpin),
                                                        );
                                        $teste->Req_EfetuaConsulta($params_distributor,$resposta, INQUIRY);
                                        //echo "<div align='left'><pre>".print_r($resposta,true)."</pre></div>";
                                        
                                        $data_vendido_aux = (is_null($teste->RetornaDataAtivacaoPINnoCaixa($resposta))?"--":$teste->RetornaDataAtivacaoPINnoCaixa($resposta));
                                        //echo "<div align='left'><pre>".print_r($data_vendido_aux,true)."</pre></div>";
                                        //
                                        //Tratando a resposta de não ativo no caixa
                                        if(is_array($data_vendido_aux)) {
                                            $data_vendido_aux = "--";
                                        }//end if(is_array($data_vendido_aux))
                                        
                                        $data_utilizado_aux = "--";
                                    }//end elseif($rs_pin_row['pin_status'] == intval($PINS_STORE_STATUS_VALUES['A']))
                                    else {
                                        $data_vendido_aux = "--";
                                        $data_utilizado_aux = "--";
                                    } //end else
                            } //end  if($isPINCARD)
                            else { 
						
						        $chave256bits = new Chave();
							    $aes = new AES($chave256bits->retornaChave());
							    $pinEnc = base64_encode($aes->encrypt(trim($fpin)));
						        $sqlPinStore = "select pin_status from pins_store where pin_codigo = '".$pinEnc."';";
						        $rs_pin_utilizado_store = SQLexecuteQuery($sqlPinStore);
                                $rs_pin_utilizado_row_store = pg_fetch_array($rs_pin_utilizado_store);
									
								//echo $rs_pin_utilizado_row_store["pin_status"];	
                                if($rs_pin_row['pin_status'] == '8') {
                                    $sql = "select MIN(pih_data) as pih_data from pins_integracao_historico where pih_pin_id = ".$rs_pin_row['pin_codinterno']." and pin_status = 8;";
                                    $rs_pin_utilizado = SQLexecuteQuery($sql);
                                    $rs_pin_utilizado_row = pg_fetch_array($rs_pin_utilizado);
                                    $data_utilizado_aux = substr($rs_pin_utilizado_row['pih_data'],0,19);
                                }//end if($rs_pin_row['pin_status'] == '8')
                                else {
                                    $data_utilizado_aux = "--";
                                } //end else
                            }//end else do  if($isPINCARD)

             ?>
              <tr class="trListagem text-center">
             <?php
             if($_SESSION["tipo_acesso_pub"]=='AT') {
				if($rs_pin_row['opr_codigo'] == "124" && ($rs_pin_row['pin_status'] != "8"|| $rs_pin_row['pin_status'] != "9") && $rs_pin_row['qtde'] > 2){
					
             ?>
			        <td><button type="submit" id="desbloqueio" class="btn btn-success">Confirmar</button></td>
				<?php }else{ ?>	
					<td>Não possui</td>
				<?php } ?>
                <td><?php echo $operacao_array[$rs_pin_row['opr_codigo']]; ?></td>
				<td><?php echo !empty($rs_pin_row['pin_guid_parceiro'])?$rs_pin_row['pin_guid_parceiro']:"Não possui";?></td>
				<td><?php echo !empty($rs_pin_row['pin_game_id'])?$rs_pin_row['pin_game_id']:"Não possui";?></td>
				<td><?php echo !empty($rs_pin_row['qtde'])?$rs_pin_row['qtde']:"Não possui";?></td>
				<td><?php echo !empty($PINS_STORE_STATUS[$rs_pin_utilizado_row_store["pin_status"]])?$PINS_STORE_STATUS[$rs_pin_utilizado_row_store["pin_status"]]:"Não possui";?></td>
             <?php
             } //end if($_SESSION["tipo_acesso_pub"]=='AT')
             ?>
                <td><?php echo "[".$rs_pin_row['pin_codigo']."]"; ?></td>
                <td><?php if($rs_pin_row['pin_dataentrada']) { ?><?php  echo monta_data($rs_pin_row['pin_dataentrada']); ?> - <?php  echo ($isPINCARD?substr($rs_pin_row['pin_dataentrada'],11,8):substr($rs_pin_row['pin_horaentrada'],0,8)); } else echo "--"; ?></td>
                <td><?php echo ($isPINCARD?$data_vendido_aux:($rs_pin_row['pin_datavenda']?monta_data($rs_pin_row['pin_datavenda'])." - ".$rs_pin_row['pin_horavenda']: "--")); ?></td>
                <td><?php echo ($isPINCARD?$data_utilizado_aux:($rs_pin_utilizado_row['pih_data']?monta_data($rs_pin_utilizado_row['pih_data'])." - ".substr($rs_pin_utilizado_row['pih_data'], 11, 8): "--")); ?></td>
                <td><?php  echo "R$ ".number_format($rs_pin_row['pin_valor'], 2, ',', '.'); ?></td>
                <td><?php echo ($isPINCARD?($data_vendido_aux=="--"&&$rs_pin_row['pin_status'] == intval($PINS_STORE_STATUS_VALUES['A'])?"PIN Não Ativado no PDV":$PINS_STORE_STATUS[$rs_pin_row['pin_status']]):constant("LANG_PINS_STATUS_MSG_".$rs_pin_row['pin_status'])); ?></td>
              </tr>
              <?php  
                            $cor1 = (($cor1==$cor2)?$cor3:$cor2);
                    } //end while 
              ?>
              <tr class="bg-cinza-claro"> 
                    <td colspan="<?php echo $colspan;?>"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">&nbsp;</font>
                    </td>
              </tr>
               </table>
            </div>
<?php
            if(!$isPINCARD)
            {
?>
            <div class="col-md-2 p-top10 fontsize-p text-left">
                <div class="row txt-azul-claro trListagem">
                    <strong>LEGENDA <?php echo strtoupper(LANG_PINS_LAST_STATUS); ?></strong>
                </div>
                <div class="row top10 txt-cinza trListagem">
                        <?php echo LANG_PINS_STATUS_MSG_0; ?>
                </div>
                <div class="top10 row txt-cinza trListagem">
                        <?php echo LANG_PINS_STATUS_MSG_1; ?>
                </div>
                <div class="top10 bottom10 row txt-cinza trListagem">
                        <?php echo LANG_PINS_STATUS_MSG_2; ?>
                </div>
                <div class="top10 bottom10 row txt-cinza trListagem">
                        <?php echo LANG_PINS_STATUS_MSG_3; ?>
                </div>
                <div class="top10 bottom10 row txt-cinza trListagem">
                        <?php echo LANG_PINS_STATUS_MSG_6; ?>
                </div>
                <div class="top10 bottom10 row txt-cinza trListagem">
                        <?php echo LANG_PINS_STATUS_MSG_7; ?>
                </div>
                <div class="top10 bottom10 row txt-cinza trListagem">
                      <?php echo LANG_PINS_STATUS_MSG_8; ?>
                </div>
                <div class="top10 bottom10 row txt-cinza trListagem">
                      <?php echo LANG_PINS_STATUS_MSG_9; ?>
                </div>
            </div>
    <?php
              }//end else do if($isPINCARD)
              
    } //end if(pg_num_rows($rs_pin) > 0)
    else {
    ?>
    <font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Este PIN não foi encontrado em nosso banco de dados.<br>Por favor, verifique a sequência de caracteres digitados.</font>
    <?php
    } //end else do if(pg_num_rows($rs_pin) > 0)
}//end if($BtnSearch && !empty($fpin))    
?>
        
    
</div>
<script>

    $(document).ready(function(){
		
		$("#desbloqueio").on("click", function(){
			
			$.ajax({
				url: "ajaxDesbloqueioPinGarega.php",
				method: "POST",
				data: {codPin: $("#fpin").val()}
			}).done(function(message){
					$("#alert-message").css("display", "block");
				if(message.type == "erro"){
					$("#alert-message").removeClass("alert-success");
					$("#alert-message").addClass("alert-danger");
				}else{
					$("#alert-message").removeClass("alert-danger");
					$("#alert-message").addClass("alert-success");
					setTimeout(function(){
						//$("#form1").submit();
						window.location.href = "<?= EPREPAG_URL_HTTPS ?>/sys/admin/pins/situacao_pin.php?pin="+$("#fpin").val();
					}, 5000);
				}
					$("#alert-message").html(message.mensagem);
					
			});
			
		});
		
	});

</script>
</html>
<?php  
require_once $raiz_do_projeto . "public_html/sys/includes/rodape_sys.php";
?>						
