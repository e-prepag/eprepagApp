<?php 
header("Content-Type: text/html; charset=ISO-8859-1; P3P: CP='CAO PSA OUR'",true);
require_once "../../../../includes/constantes.php";   
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "gamer/main.php";
require_once DIR_CLASS . "gamer/classIntegracao.php";

validaSessao(); 
require_once DIR_INCS . "gamer/venda_e_modelos_logica_epp.php";
$https = 'http' . (($_SERVER['HTTPS']=='on') ? 's' : '');

$pagina_titulo = "Comprovante " . $GLOBALS['FORMAS_PAGAMENTO_DESCRICAO']['1'];
$cabecalho_file = isset($GLOBALS['_SESSION']['is_integration']) && $GLOBALS['_SESSION']['is_integration'] == true ? "../includes/cabecalho_int.php" : "../includes/cabecalho.php";
include $cabecalho_file;

require_once DIR_INCS . "gamer/venda_e_modelos_view_epp.php";
require_once DIR_INCS . "gamer/pagto_compr_usuario_dados.php";

$rs_venda_row = pg_fetch_array($rs_venda);
$venda_status 		 = $rs_venda_row['vg_ultimo_status'];
$pagto_data_inclusao = $rs_venda_row['vg_pagto_data_inclusao'];
$pagto_banco 		 = $rs_venda_row['vg_pagto_banco'];
$pagto_local 		 = $rs_venda_row['vg_pagto_local'];
$pagto_num_docto 	 = $rs_venda_row['vg_pagto_num_docto'];
$pagto_valor_pago 	 = $rs_venda_row['vg_pagto_valor_pago'];
$pagto_data 		 = $rs_venda_row['vg_pagto_data'];

$pagto_num_docto 	 = preg_split("/\|/", $pagto_num_docto);

    if($venda_status == $STATUS_VENDA['PEDIDO_EFETUADO']){

        if( $GLOBALS['_SESSION']['is_integration'] == true )
                    include DIR_WEB . "prepag2/commerce/includes/pagto_compr_dep_doc_transf_int.php";
                else
                    include DIR_WEB . "prepag2/commerce/includes/pagto_compr_dep_doc_transf.php";
        
        } elseif($venda_status == $STATUS_VENDA['DADOS_PAGTO_RECEBIDO'] ||
					 $venda_status == $STATUS_VENDA['PAGTO_CONFIRMADO'] 	||
					 $venda_status == $STATUS_VENDA['VENDA_REALIZADA']) {
?>
			
        <table class="table fontsize-p">
            <tr bgcolor="#F0F0F0" height="25">
            	<td class="texto" align="right" width="40%">&nbsp;&nbsp;<b>Banco:</b></td>
                <td class="texto" width="60%"><?php echo $PAGTO_BANCOS[$pagto_banco] ?></td>
            </tr>
            <tr bgcolor="#F0F0F0" height="25">
            	<td class="texto" align="right">&nbsp;&nbsp;<b>Local:</b></td>
                <td class="texto"><?php echo $PAGTO_LOCAIS[$pagto_banco][$pagto_local] ?></td>
            </tr>
            <tr bgcolor="#F0F0F0" height="25">
            	<td class="texto" align="right">&nbsp;&nbsp;<b>Data do Pagamento:</b></td>
                <td class="texto"><?php echo formata_data_ts($pagto_data, 0, false, false) ?></td>
            </tr>
			<?php
			$pagto_nome_docto_Ar = preg_split("/;/", $PAGTO_NOME_DOCTO[$pagto_banco][$pagto_local]);
			for($i=0; $i<count($pagto_nome_docto_Ar); $i++){
			?>
			<tr bgcolor="#F0F0F0" height="25">
				<td class="texto" align="right">&nbsp;&nbsp;<b><?php echo $pagto_nome_docto_Ar[$i]; ?>:</b></td>
				<td class="texto"><?php echo $pagto_num_docto[$i]?></td>
			</tr>
			<?php } ?>
			
			<?php if( 	($pagto_banco == "001" && $pagto_local == "06") ||
					($pagto_banco == "237" && $pagto_local == "06") ||
					($pagto_banco == "104" && $pagto_local == "06") ){?>
			<?php 	$arquivos = buscaArquivosIniciaCom($FOLDER_COMMERCE_UPLOAD, 'nome', 'asc', "money_comprovante_" . $venda_id . "_");
					if(count($arquivos) > 0){ ?>
			<tr bgcolor="#F0F0F0" height="25">
				<td class="texto" align="right">&nbsp;&nbsp;<b>Comprovante:</b></td>
				<td class="texto"><?php for($j = 0; $j < count($arquivos); $j++){ ?><a target="_blank" href="pagto_compr_down.php?venda=<?php echo $venda_id?>&arquivo=<?php echo $arquivos[$j]?>">Comprovante <?php echo ($j+1)?></a><br><?php } ?></td>
			</tr>
				<?php 	} ?>
			<?php } ?>
			
            <tr bgcolor="#F0F0F0" height="25">
            	<td class="texto" align="right">&nbsp;&nbsp;<b>Valor Pago:</b></td>
                <td class="texto"><?php echo number_format($pagto_valor_pago, 2, ',','.') ?></td>
            </tr>
			</table>
			
			<br>
			<table border="0" cellspacing="0" width="90%" align="center">
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center" height="25"><b>Status</b></td>
    	        </tr>
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center" height="25"><?php echo $STATUS_VENDA_DESCRICAO[$venda_status]?></td>
    	        </tr>
			</table>
			
		<?php } ?>
</div>
<?php 
require_once RAIZ_DO_PROJETO . "public_html/prepag2/commerce/includes/rodape.php"; 

//Fechando Conexão
//pg_close($connid);

?>
<!-- Google Code for P&aacute;gina de pagamento Conversion Page -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = 1052651518;
var google_conversion_language = "pt";
var google_conversion_format = "1";
var google_conversion_color = "ffffff";
var google_conversion_label = "WS5VCIKYswIQ_t_49QM";
var google_conversion_value = 0;
/* ]]> */
</script>
<script type="text/javascript" src="<?php echo $https; ?>://www.googleadservices.com/pagead/conversion.js">
</script>
<!-- Facebook Pixel Code -->
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '228069144336893'); // Insert your pixel ID here.
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=228069144336893&ev=PageView&noscript=1"/></noscript>
<!-- End Facebook Pixel Code -->
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="<?php echo $https; ?>://www.googleadservices.com/pagead/conversion/1052651518/?label=WS5VCIKYswIQ_t_49QM&guid=ON&script=0"/>
</div>
</noscript>
        
