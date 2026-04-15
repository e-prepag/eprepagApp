<?php  
header("Content-Type: text/html; charset=ISO-8859-1; P3P: CP='CAO PSA OUR'",true);
require_once "../../../../includes/constantes.php";
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "gamer/main.php";
require_once DIR_CLASS . "gamer/classIntegracao.php";
validaSessao(); 

require_once DIR_INCS . "gamer/venda_e_modelos_logica_epp.php";

require_once DIR_INCS . "gamer/pagto_informa_dep_doc_transf_verificacoes.php";


//Variaveis do Formulario
$pagto_banco 		= $_SESSION['pagto.pagto_banco'];
$pagto_local 		= $_SESSION['pagto.pagto_local'];
$pagto_num_docto 	= $_SESSION['pagto.pagto_num_docto'];
$pagto_valor_pago 	= $_SESSION['pagto.pagto_valor_pago'];
//	$pagto_data_Dia 	= $_SESSION['pagto.pagto_data_Dia'];
//	$pagto_data_Mes 	= $_SESSION['pagto.pagto_data_Mes'];
//	$pagto_data_Ano 	= $_SESSION['pagto.pagto_data_Ano'];
$pagto_data_data 	= $_SESSION['pagto.pagto_data_data'];
$pagto_data_horas 	= $_SESSION['pagto.pagto_data_horas'];
$pagto_data_minutos	= $_SESSION['pagto.pagto_data_minutos'];

$pagto_data_data_full = $pagto_data_data ." ".$pagto_data_horas .":". $pagto_data_minutos;

require_once DIR_INCS . "gamer/pagto_informa_dep_doc_transf_validacoes.php";

if($msg != ""){

    //redireciona
    $strRedirect = "/prepag2/commerce/conta/pagto_informa_dep_doc_transf.php";

    //Fechando Conexão
    pg_close($connid);

    redirect($strRedirect);
}


?>

<?php 
$pagina_titulo = "Informa Pagamento";

$cabecalho_file = isset($GLOBALS['_SESSION']['is_integration']) && $GLOBALS['_SESSION']['is_integration'] == true ? "../includes/cabecalho_int.php" : "../includes/cabecalho.php";
include $cabecalho_file;
?>
			<table border="0" cellspacing="0" align="center" width="100%" class="wrapper int-box" style="border-top: 0px;">
            <tr valign="middle" bgcolor="#FFFFFF">
              	<td align="left" class="texto_vermelho"><?php echo str_replace("\n", "<br>", $msg)?></td>
            </tr>
            <tr><td>&nbsp;</td></tr>
            <tr>
            	<td>
					<?php include DIR_INCS . "gamer/venda_e_modelos_view_epp.php"; ?>
				</td>
			</tr>
			<tr>
				<td>
			                
                    <table class="table fontsize-p" <?php if($GLOBALS['_SESSION']['is_integration']==true){ echo "style='margin: auto;'"; } ?>>
                        <tr valign="middle" bgcolor="#FFFFFF">
                            <td colspan="2" align="left" class="texto text13">Por favor, confira os dados atentamente:<br>
                                <font color="FF0000">(Para sua segurança, uma vez informados os dados não será possível fazer alteração. Se as informações forem fornecidas incorretamente o seu pedido ficará pendente)</font>
                            </td>
                        </tr>

                    <tr bgcolor="#F0F0F0">
                        <td class="texto" align="right">&nbsp;&nbsp;<b>Banco:</b></td>
                        <td class="texto"><?php echo $PAGTO_BANCOS[$pagto_banco] ?></td>
                    </tr>
                    <tr bgcolor="#F0F0F0">
                        <td class="texto" align="right">&nbsp;&nbsp;<b>Local:</b></td>
                        <td class="texto"><?php echo $PAGTO_LOCAIS[$pagto_banco][$pagto_local] ?></td>
                    </tr>
                    <tr bgcolor="#F0F0F0">
                        <td class="texto" align="right">&nbsp;&nbsp;<b>Data do Pagamento:</b></td>
                        <td class="texto"><?php echo $pagto_data_data_full?></td>
                    </tr>
                    <?php
                    $pagto_nome_docto_Ar = split(";", $PAGTO_NOME_DOCTO[$pagto_banco][$pagto_local]);
                    for($i=0; $i<count($pagto_nome_docto_Ar); $i++){
                    ?>
                    <tr bgcolor="#F0F0F0">
                        <td class="texto" align="right">&nbsp;&nbsp;<b><?php echo $pagto_nome_docto_Ar[$i]; ?>:</b></td>
                        <td class="texto"><?php echo $pagto_num_docto[$i]?></td>
                    </tr>
                    <?php } ?>

                    <?php if( 	($pagto_banco == "001" && $pagto_local == "06") ||
                            ($pagto_banco == "237" && $pagto_local == "06") ||
                            ($pagto_banco == "104" && $pagto_local == "06") ){?>
                    <?php		$arquivos = buscaArquivosIniciaCom($FOLDER_COMMERCE_UPLOAD_TMP, 'nome', 'asc', "money_comprovante_" . $venda_id . "_");
                            if(count($arquivos) > 0){ ?>
                    <tr bgcolor="#F0F0F0">
                        <td class="texto" align="right">&nbsp;&nbsp;<b>Comprovante:</b></td>
                        <td class="texto"><?php for($j = 0; $j < count($arquivos); $j++){ ?><a target="_blank" href="pagto_compr_down.php?venda=<?php echo $venda_id?>&arquivo=<?php echo $arquivos[$j]?>">Comprovante <?php echo ($j+1)?></a><br><?php } ?></td>
                    </tr>
                        <?php 	} ?>
                    <?php } ?>

                    <tr bgcolor="#F0F0F0">
                        <td class="texto" align="right">&nbsp;&nbsp;<b>Valor Pago:</b></td>
                        <td class="texto"><?php echo $pagto_valor_pago ?></td>
                    </tr>
                    <tr><td colspan="2">&nbsp;</td></tr>
                    <tr>
                        <td colspan="2" align="center">
                            <input type="button" name="btVoltar" value="Voltar" OnClick="window.location='/prepag2/commerce/conta/pagto_informa_dep_doc_transf.php';" class="btn btn-sm btn-default <?php if($GLOBALS['_SESSION']['is_integration']==true){ echo "int-btn1 grad1"; } ?>">
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <input type="button" name="btContinuar" value="Confirmar" OnClick="window.location='/prepag2/commerce/conta/pagto_informa_dep_doc_transfEf.php';" class="btn btn-sm btn-success <?php if($GLOBALS['_SESSION']['is_integration']==true){ echo "int-btn1 grad1"; } ?>">
                        </td>
                    </tr>
                    </table>
              	</td>
            </tr>
        	</table>
<br>&nbsp;
<br>&nbsp;
<br>&nbsp;
<?php 
require_once RAIZ_DO_PROJETO . "public_html/prepag2/commerce/includes/rodape.php"; 

//Fechando Conexão
//pg_close($connid);

?>
