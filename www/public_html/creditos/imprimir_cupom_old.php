<?php 
$_PaginaOperador1Permitido = 53; // o número magico
$_PaginaOperador2Permitido = 54;

require_once "../../includes/constantes.php";
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "pdv/main.php";
require_once DIR_CLASS."pdv/classOperadorGamesUsuario.php";

// include do arquivo contendo IPs DEV
require_once DIR_INCS. "configIP.php";

require_once DIR_CLASS . "pdv/classPesquisaEY.php";

$_PaginaOperador1Permitido = 53; // o número magico
$_PaginaOperador2Permitido = 54; 
validaSessao(); 
header("Content-Type: text/html; charset=ISO-8859-1",true);
//Recupera usuario
if(isset($_SESSION['dist_usuarioGames_ser']) && !is_null($_SESSION['dist_usuarioGames_ser'])){
	$usuarioGames = unserialize($_SESSION['dist_usuarioGames_ser']);
	$usuarioId = $usuarioGames->getId();

    //Variavel para mensagens
    $msg = "";

    //Captura os PINs selecionados
    $listaPINs = "";
    foreach($_POST as $key => $val) {
        if($key!=str_replace('emitir', '', $key)) { 
            if(empty($listaPINs)) {
                $listaPINs = $val;
            }//end if(empty($listaPINs))
            else {
                $listaPINs .= ",".$val;
            }//end else do if(empty($listaPINs))
        }//end if($key!=str_replace('emitir', '', $key))
    }//end foreach($_POST as $key => $val)

    //CSS vindo do Drupal
    //echo $_SESSION['drupal_render_css']."\n";

    //Testando se teve algum PIN selecionado
    if(empty($listaPINs)) {
        die("<p class='text-red'>Nenhum PIN selecionado.</p>");
    }//end if(empty($listaPINs))

    //Capturando os IDs dos PINs
    $lp_ids = $listaPINs;

    //capturando a variável server
    $server_url = "www.e-prepag.com.br";
    if(checkIP()) {
        $server_url = $_SERVER['SERVER_NAME'];
    }

    if($msg == ""){
            $sql  = "select p.pin_vencimento, p.pin_codigo, p.pin_valor, p.pin_lote_codigo, p.pin_serial, p.pin_codinterno,
                                    vgm.vgm_nome_produto, vgm.vgm_nome_modelo, opr.opr_codigo, opr.opr_nome, opr.opr_ban_pos, ogp.ogp_nome_imagem, 
                                    CASE WHEN pin_codigo = '0000000000000000' THEN pin_caracter
                                            ELSE pin_codigo
                                    END as case_serial,
                                    vgm_pin_request, ogp_comunicacao_cupom 
                     from pins_dist p
                            inner join tb_dist_venda_games_modelo_pins vgmp on p.pin_codinterno = vgmp.vgmp_pin_codinterno 
                            inner join tb_dist_venda_games_modelo vgm on vgm.vgm_id = vgmp.vgmp_vgm_id 
                            inner join tb_dist_venda_games vg on vg.vg_id = vgm.vgm_vg_id
                            left join operadoras opr on opr.opr_codigo = vgm.vgm_opr_codigo
                            left join tb_dist_operadora_games_produto ogp on ogp.ogp_id = vgm.vgm_ogp_id
                     where vg.vg_ug_id = $usuarioId 
                           and vg.vg_id = ".$_POST['tf_v_codigo_detalhe']." 
                           and vgmp.vgmp_pin_codinterno in ($lp_ids)
                     order by vgmp.vgmp_impressao_ult_data desc, vgmp.vgmp_impressao_qtde ";
    //echo "$sql<br>";
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
?>
    <html>
    <head>
            <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
            <title>Rede E-Prepag Meios de Pagamentos / Prepag Money</title>
            <link href="/incs/css.css" rel="stylesheet" type="text/css">
            <style type="text/css">
            <!--
                @media print {
                  .noprint { display: none; }
                }
                .texto_vermelho {
                        font-family: Arial, Helvetica, sans-serif;
                        font-size: 11px;
                        color: #FF0000;
                }
                .label_pin {
                        font-family: Arial, Helvetica, sans-serif;
                        font-size: 20px;
                        font-weight:bold;
                        color:#000000;
                }
                .novo_label {
                        font-family: Arial, Helvetica, sans-serif;
                        font-size: 20px;
                        color:#000000;
                }
                .dados_pin_texto {
                        font-family: Arial, Helvetica, sans-serif;
                        font-size: 15px;
                        vertical-align:middle;
                        color:#58585A;
                }
                .dados_pin_linha {
                        background-color:#BCBDC1;
                }
                .label_pin_veja {
                        font-family: Arial, Helvetica, sans-serif;
                        font-size: 14px;
                        font-style:italic;
                        color:#58585A;
                        text-decoration: none;
                }
                .imagem_epp { float: right; display: block; overflow: hidden; position:relative;
                }
                -->
              </style>
    <head>
    <body marginleft="0" marginright="0" margintop="0" marginbottom="0" >
<?php
    if($_POST['imprimir_ou_csv'] == 'imprimir'){
?>        
    
    <table border="0" cellspacing="0" bgcolor="#F0F0F0" width="100%" class='noprint'>
        <tr>
            <td colspan="2">&nbsp;</td>
        </tr>
        <tr>
            <td align="center" class="texto">
                    <input type="button" name="btImprimir" value="Imprimir" OnClick="window.print();" class="botao_simples">
            </td>
            <td align="center" class="texto">
                    <input type="button" name="btFechar" value="Fechar" OnClick="window.close();" class="botao_simples">
            </td>
        </tr>
        <tr>
            <td colspan="2">&nbsp;</td>
        </tr>
    </table>

<?php 
    }//end if($_POST['imprimir_ou_csv'] == 'imprimir')
    if($msg != "") {
?>
    <table border="0" cellspacing="0" align="center" class='noprint'>
    <tr><td>&nbsp;</td></tr>
    <tr valign="middle" bgcolor="#FFFFFF">
            <td align="left" class="texto_vermelho"><?php echo str_replace("\n", "<br>", $msg)?></td>
    </tr>
    </table>
<?php 	
    }//end if($msg != "")

    $cuponsPorLinha = 2;
if($_POST['imprimir_ou_csv'] == 'imprimir'){
    if($rs_modelos) {
        echo "<center><table cellspacing='0' width='960px'>";
        $rs_modelos_row_total = pg_num_rows($rs_modelos);
        for($i=0;$i<$rs_modelos_row_total;$i++) {
            $rs_modelos_row = pg_fetch_array($rs_modelos);
            if((($i) % $cuponsPorLinha) === 0 ) {
                 //echo "QUebra linha Inicio<br>";
                 echo "<tr class='texto' valign='middle'>
                                     <td width='480px' style='padding: 5px 5px;' align='center'>";
            }
            else {
                 echo "       <td width='480px' style='padding: 5px 5px;' align='center'>";
            }
            echo "<table style='border: solid 1px; padding: 5px 15px;'> 
                  ";
            if($rs_modelos_row['vgm_pin_request'] != 1) {
                echo " 
                             <tr style='height: 80px;'>
                                 <td colspan='2' align='left'>";
                if($rs_modelos_row['ogp_nome_imagem'] && $rs_modelos_row['ogp_nome_imagem'] != "" && file_exists($GLOBALS['FIS_DIR_IMAGES_PRODUTO'] . $rs_modelos_row['ogp_nome_imagem'])) {
                     echo "              <img src='".$GLOBALS['URL_DIR_IMAGES_PRODUTO'].$rs_modelos_row['ogp_nome_imagem']."' title='".$aux_matriz[$i]['descricao']."' alt='".$aux_matriz[$i]['descricao']."' style='max-width: 145px; max-height: 80px;' border='0'>
                         ";
                }//end if($rs_modelos_row['ogp_nome_imagem'] && $rs_modelos_row['ogp_nome_imagem'] != "" && file_exists($GLOBALS['FIS_DIR_IMAGES_PRODUTO'] . $rs_modelos_row['ogp_nome_imagem']))
                echo "               
                                        <img src='/imagens/pdv/logo_eprepag.gif' title='E-Prepag' alt='E-Prepag' border='0' class='imagem_epp'>";
                echo "
                                 </td>
                             </tr>";
            } //end if($rs_modelos_row['vgm_pin_request'] != 1) 
            $pin_serial = $rs_modelos_row['case_serial'];
            $case_serial = $rs_modelos_row['pin_serial'];

            $opr_codigo     = $rs_modelos_row['opr_codigo'];
            $pin_codinterno = $rs_modelos_row['pin_codinterno'];

            $usuarioGames = unserialize($_SESSION['dist_usuarioGames_ser']);

            //	opr_codigo = 44 -> 'Axeso5' , 28 -> 'PayByCash', 34 -> 'Webzen', 100 -> 'Facebook BHN'
            if($opr_codigo == 28 || $opr_codigo == 44 || $opr_codigo == 34 || $opr_codigo == 100 || $opr_codigo == 106) {
                     // o carregaemnto no estoque para Axeso5 está trocado -> então troca de novo aqui
                     // o carregamento no estoque para Facebook BHN foi baseado no Axeso5 que está trocado -> então troca de novo aqui
                     $pin_serial = $rs_modelos_row['pin_serial'];
                     $case_serial = $rs_modelos_row['case_serial'];
            }
            //	opr_codigo = 100 -> 'Facebook BHN' 
            if($opr_codigo == 100) {
                     $conteudo_instrucoes = "<br><br>Para resgatar este cartão virtual:<br>1. Acesse www.facebook.com/giftcards/redeem.<br>2. Siga as instruções para resgate de cartões.<br>3. Quando solicitado, insira o código PIN.";
                     $label_numero_serie =  "Cartão";
            }
            else {
                $label_numero_serie = "Nº de série";
                $conteudo_instrucoes = "";
            }
            
            //If para capturar instruções enviadas pelo integração de requisição de PINs
            if($rs_modelos_row['vgm_pin_request']) {
                $sql_instrucoes = "SELECT bhn_xml_retorno FROM pedidos_bhn WHERE bhn_pin = '".$rs_modelos_row['pin_codigo']."';";
                $rs_instrucoes = SQLexecuteQuery($sql_instrucoes);
                if($rs_instrucoes) {
                    $rs_instrucoes_row = pg_fetch_array($rs_instrucoes);
                    $rs_instrucoes_row['bhn_xml_retorno'] = str_replace("\n", "", $rs_instrucoes_row['bhn_xml_retorno']);
                    $instrucoes = json_decode($rs_instrucoes_row['bhn_xml_retorno']);
                    $total = count($instrucoes->transaction->receiptsFields->line);
                    $conteudo_instrucoes = "";
                    if($total > 0) {
                        $conteudo_instrucoes = "<br><br>Instruções para resgatar:<br>";
                        for($contador=0;$contador < $total; $contador++){
                            if(!is_object($instrucoes->transaction->receiptsFields->line[$contador])) {
                                if(!empty($instrucoes->transaction->receiptsFields->line[$contador]))
                                    $conteudo_instrucoes .= $instrucoes->transaction->receiptsFields->line[$contador]."<br>";
                            }//end if(!is_object($instrucoes->transaction->receiptsFields->line[$contador])) 
                        }//end foreach
                    }//end if($total > 0)
                    if(isset($instrucoes->transaction->termsAndConditions)) {
                        $conteudo_instrucoes .= "<br>Termos de Serviço:<br>".$instrucoes->transaction->termsAndConditions."<br><br>"; 
                    }
                    $label_numero_serie = "Numero de serie";
                    $case_serial = $instrucoes->transaction->additionalTxnFields->activationAccountNumber;
                }//end if($rs_instrucoes)
            }//end if($rs_modelos_row['vgm_pin_request'])
            
            //Teste para impressão do link da pesquisa
            /*Trecho de pesquisa da Ernst Young
            $teste = new classPesquisaEY($pin_serial);
            if(count($teste->getErro()) == 0) { 
                echo "      <tr>
                                 <td colspan='2' height='10px'><strong><i>Ganhe ".(($teste->getPublisher() == 13)?"10.000 Cash Ongame":"1255 Riot Points")." respondendo uma pesquisa!<br>Saiba mais em www.e-prepag.com.br/pesquisa</i></strong></td>
                             </tr>
                             <tr>
                                 <td colspan='2' height='10px'></td>
                             </tr>";
            }//end if(count($teste->getErro()) == 0)
            */
            if($rs_modelos_row['vgm_pin_request'] != 1) {
                echo "<tr class='dados_pin_texto'>
                                 <td>
                                    Publisher 
                                 </td>
                                 <td>
                                     &nbsp;".$rs_modelos_row['opr_nome']."
                                 </td>
                             </tr>
                             <tr class='dados_pin_texto'>
                                 <td>
                                     Produto
                                 </td>
                                 <td>
                                     &nbsp;".$rs_modelos_row['vgm_nome_produto'].(($rs_modelos_row['vgm_nome_modelo']!="")?" - ".$rs_modelos_row['vgm_nome_modelo']:"")."
                                 </td>
                              </tr>";
            }//end if($rs_modelos_row['vgm_pin_request'] != 1) 
            else {
                echo " 
                             <tr class='dados_pin_texto'>
                                 <td>
                                     Produto
                                 </td>
                                 <td>
                                     &nbsp;".$rs_modelos_row['vgm_nome_produto']."
                                 </td>
                              </tr>
                    ";
            }//end else
            echo " 
                             <tr class='dados_pin_texto'>
                                 <td>
                                      Valor
                                 </td>
                                 <td>
                                     &nbsp;R$ ".number_format($rs_modelos_row['pin_valor'], 2, ',', '.')."
                                 </td>
                              </tr>
                             ";
            if($rs_modelos_row['pin_vencimento']) {
            echo "<tr class='dados_pin_texto'>
                                 <td>
                                     Validade
                                 </td>
                                 <td>
                                     &nbsp;".$rs_modelos_row['pin_vencimento']."&nbsp; Dias
                                 </td>
                              </tr>
                              ";
            }
            echo "<tr>
                                 <td colspan='2' height='1px' class='dados_pin_linha' width='450px'></td>
                             </tr>
                             <tr>
                                 <td colspan='2' height='1px'></td>
                             </tr>
                             <tr>
                                 <td class='novo_label'>
                                      PIN
                                 </td>
                                 <td class='label_pin'>
                                     &nbsp;".$pin_serial."
                                 </td>
                             </tr>";
            if(!$rs_modelos_row['vgm_pin_request']) {
                echo " 
                             <tr>
                                 <td class='novo_label'>
                                     ".$label_numero_serie."
                                 </td>
                                 <td class='label_pin'>
                                     &nbsp;".$case_serial."
                                 </td>
                             </tr>
                 ";
                
                if(!empty($rs_modelos_row['ogp_comunicacao_cupom'])){
                    echo "
                                <tr>
                                    <td>
                                    </td>
                                    <td class='label_pin_veja' style='padding: 15px 0px 15px 0px;' height='35px'>
                                        ".$rs_modelos_row['ogp_comunicacao_cupom']."
                                    </td>
                                </tr>";
                }
            }//end if(!$rs_modelos_row['vgm_pin_request']) 
            echo " 
                             <tr>
                                 <td colspan='2' height='1px'></td>
                             </tr>
                             <tr>
                                 <td>
                                 </td>
                                 <td class='label_pin_veja'>
                                 O código deve ser utilizado em até 6 meses.".$conteudo_instrucoes."
                                 </td>
                              </tr>";
            if($rs_modelos_row['vgm_pin_request'] && isset($instrucoes->transaction->additionalTxnFields->activationAccountNumber)) {
                echo " 
                             <tr>
                                 <td colspan='2' class='label_pin_veja'  height='35px'>
                                     ".$label_numero_serie." : &nbsp;".$case_serial."
                                 </td>
                             </tr>
                 ";
            }//end if($rs_modelos_row['vgm_pin_request'])
            echo "
                              <tr>
                                 <td>
                                 </td>
                                 <td class='label_pin_veja'>
                                 Suporte: www.e-prepag.com.br
                                 </td>
                              </tr>
                 </table>";
            if((($i+1) % $cuponsPorLinha) === 0 ) { //(($i+2) % $cuponsPorLinha) {
                  //echo "QUEBRA linha FIM<br>";
                  $retorno .= "       </td>
                               </tr>
                               <tr>
                                  <td colspan='".$cuponsPorLinha."' height='5px'>
                                        &nbsp;
                                  </td>
                                </tr>
                                ";
            }
            else {
                  $retorno .= "       </td>";
            }
       } //end for($i=0;$i<$rs_modelos_row_total;$i++)
       echo "</table>
           </center>";
    } //end if($rs_modelos)
} 
if($_POST['imprimir_ou_csv'] == 'csv'){
    if($rs_modelos) {
        $rs_modelos_row_total = pg_num_rows($rs_modelos);
        for($i=0;$i<$rs_modelos_row_total;$i++) {
            $rs_modelos_row = pg_fetch_array($rs_modelos);

            $pin_serial = $rs_modelos_row['case_serial'];
            $case_serial = $rs_modelos_row['pin_serial'];

            $opr_codigo     = $rs_modelos_row['opr_codigo'];
            $pin_codinterno = $rs_modelos_row['pin_codinterno'];

            $usuarioGames = unserialize($_SESSION['dist_usuarioGames_ser']);
            //	opr_codigo = 44 -> 'Axeso5' , 28 -> 'PayByCash', 34 -> 'Webzen', 100 -> 'Facebook BHN', 100 -> 'IMVU BHN'
            if($opr_codigo == 28 || $opr_codigo == 44 || $opr_codigo == 34 || $opr_codigo == 100 || $opr_codigo == 101 || $opr_codigo == 101 || $opr_codigo == 106) {
                // o carregaemnto no estoque para Axeso5 está trocado -> então troca de novo aqui
                // o carregamento no estoque para Facebook BHN foi baseado no Axeso5 que está trocado -> então troca de novo aqui
                $pin_serial = $rs_modelos_row['pin_serial'];
                $case_serial = $rs_modelos_row['case_serial'];
            }

            $label_numero_serie = "Nº de série";

            if($rs_modelos_row['vgm_pin_request'] != 1) {
                if($i == 0){
                    $mensagem .= "Publisher;";
                    $mensagem .= "Produto;";
                }
                $pub = $rs_modelos_row['opr_nome'] . ";";
                
            }//end if($rs_modelos_row['vgm_pin_request'] != 1) 
            else {
                if($i == 0){
                    $mensagem .= "Produto;";
                }
            }//end else
            if($i == 0){
                $mensagem .= "Valor;";
                $mensagem .= "PIN;";
            }
                                 
            if(!$rs_modelos_row['vgm_pin_request']) {
                if($i == 0){
                    $mensagem .= $label_numero_serie.";";
                }
            }
            $pin = ($pin_serial && $pin_serial != "") ? $pin_serial : "-";
            $c_serial = ($case_serial && $case_serial != "") ? $case_serial : "-";
            
            $mensagem .= "\n". $pub . $rs_modelos_row['vgm_nome_produto'].(($rs_modelos_row['vgm_nome_modelo']!="")?" - ".$rs_modelos_row['vgm_nome_modelo']:"").";" ."R$". number_format($rs_modelos_row['pin_valor'], 2, ',', '.').";". '="'.$pin. '"' .";" . '="'.$c_serial.'"' ;
        
        } //end for($i=0;$i<$rs_modelos_row_total;$i++)

    } //end if($rs_modelos)
    $file_ret = grava_arquivo_pin($mensagem); 
 ?> 

<script>
    $( document ).ready(function() {
        location.href= '/creditos/dld.php?f=<?php echo $file_ret; ?>&fc=<?php echo "eprepag_pin_".date("YmdHis").".csv"; ?>';
    }); 
</script>
        
<?php
}

} //end if(isset($_SESSION['dist_usuarioGames_ser']) && !is_null($_SESSION['dist_usuarioGames_ser']))
else {
?>
    <p>Seu login expirou. Por favor, faça novamente o login para imprimir seu cupom.</p>
<?php
}//end else do if(isset($_SESSION['dist_usuarioGames_ser']) && !is_null($_SESSION['dist_usuarioGames_ser']))

function grava_arquivo_pin($mensagem) {

		$file_path = RAIZ_DO_PROJETO . 'public_html/tmp/txt/';
		$web_path = "temp/txt/";
		$expiration = 20;
		// -----------------------------------
		// Remove old files	
		// -----------------------------------
		list($usec, $sec) = explode(" ", microtime());
		$now = ((float)$usec + (float)$sec);
				
		$current_dir = @opendir($file_path);
		
		while($filename = @readdir($current_dir)) {
			if ($filename != "." and $filename != ".." and $filename != "index.html") {
				$name = str_replace(".csv", "", $filename);
				if (($name + $expiration) < $now) {
					@unlink($file_path.$filename);
				}
			}
		}
		@closedir($current_dir);
           
		//Arquivo
		$file = $file_path.$now.".csv";
            
		//Grava mensagem no arquivo
		if ($handle = fopen($file, 'a+')) {
			fwrite($handle, $mensagem);
			fclose($handle);
		}
		$file_return = $now.".csv";

		return $file_return;
}
?>
</body>
</html>