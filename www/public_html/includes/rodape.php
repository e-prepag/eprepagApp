<?php require_once __DIR__ . '/../../includes/constantes_url.php'; ?>
<?php
function b_isIntegracao_geral() {
    
	// Algumas páginas não trabalham em integração, para evitar que ProdutoModelo:obter() retorne modelos inativos
	if(strpos(strtolower($GLOBALS['_SERVER']['SCRIPT_NAME']), "/prepag2/commerce/modelosEx.php")===false) {
		if($GLOBALS['_SESSION']['integracao_is_parceiro']=="OK" && isset($GLOBALS['_SESSION']['integracao_origem_id']) && isset($GLOBALS['_SESSION']['integracao_order_id'])) {
			return true;
		}
	}
	return false;
}


if (!b_isIntegracao_geral()) {
?>
<center>

<div id="rodape" align="center">

<script language='javascript'>function vopenw() {	tbar='location=no,status=yes,resizable=yes,scrollbars=yes,width=560,height=535';	sw =  window.open('https://www.certisign.com.br/seal/splashcerti.htm','CRSN_Splash',tbar);	sw.focus();}</script>

<table border='0' cellpadding='0' cellspacing='0' align='center'>
<tr>
	<td width='135' align='center' valign='middle'>
		<a href='javascript:vopenw()'><img src='/imagens/certisign/100x46_transparente.gif' border=0 align=center title='Um site validado pela Certisign indica que nossa empresa concluiu satisfatoriamente todos os procedimentos para determinar que o domínio validado é de propriedade ou se encontra registrado por uma empresa ou organização autorizada a negociar por ela ou exercer qualquer atividade lícita em seu nome.'></a>
	</td>
	<td width=135 align=center valign=center>
		<script src="https://seal.verisign.com/getseal?host_name=<?= EPREPAG_URL ?>&size=S&use_flash=NO&use_transparent=getsealjs.js&lang=pt"></script>
	</td>
	<?php 
        if(strpos($_SERVER['SCRIPT_NAME'],"dist_")>0) { 
        ?>
        <td>&nbsp;
	</td>
	<td align=center valign=center>
                <font size="1" color="#626262" face="verdana, arial, sans serif"><a href="<?= EPREPAG_URL_HTTP ?>/prepag2/dist_commerce/conta/ajuda_seguranca.php">Dicas de Segurança</a></font>
	</td>
        <?php
        }//end if(strpos($_SERVER['SCRIPT_NAME'],"dist_")>0)
        ?>
</tr>
<tr>
	<td colspan="7" align=center valign=center>
		<font size="1" color="#626262" face="verdana, arial, sans serif"><div id="copyright">Copyright E-PREPAG 2007-<?php echo date('Y');?> - Todos os direitos reservados</div></font>
	</td>
</tr>
</table>
</div>
</center>
<?php 
}

//Fechando Conexão
//pg_close($connid);
?>