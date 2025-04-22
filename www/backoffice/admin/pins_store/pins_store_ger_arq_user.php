<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/main.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";
require_once $raiz_do_projeto . "class/classIntegracaoPin.php";
require_once $raiz_do_projeto . "class/classIntegracaoPinCash.php";
require_once $raiz_do_projeto . "includes/inc_functions.php";
require_once $raiz_do_projeto . "class/classPinsStore.php";       

?>
<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" /> 
<meta http-equiv="Content-Language" content="pt-br" /> 
<title> Gerenciamento de Arquivos e Senhas </title>
<link href="/js/jQCTC/_assets/css/Style.css" rel="stylesheet" type="text/css" />
</head>
<body>
<?php
$time_start_stats = getmicrotime();

//Recupera as vendas
if(isset($tf_v_nome_arq)){
	$sql  = "SELECT *,to_char(psra_dataentrada,'DD/MM/YYYY HH24:MI:SS') as psra_data_aux from pins_store_rel_arquivos WHERE psra_nome='".trim($tf_v_nome_arq)."'"; 
//echo $sql ."<br>\n";
	$rs_pins = SQLexecuteQuery($sql);
}
?>
	<center>
<form name="form1" method="post" action="pins_store_ger_arq.php">
	<table  border="0" cellspacing="0" bgcolor="#FFFFFF" width="100%">
    <tr><td>&nbsp;</td></tr>
	<tr>
		<td height="22,5" valign="center" align="center" bgcolor="#00008C"><font face="Arial, Helvetica, sans-serif" size="3" color="#FFFFFF"><b>Gerenciamento de Arquivos contendo os PINs</b></font></td>
	</tr>
    <tr><td align="right"><font face="Arial, Helvetica, sans-serif" size="2" color="#00008C"><a href="" class="menu"><img src="../../../images/voltar.gif" width="47" height="15" border="0"></a></font></td></tr>
	<tr><td>&nbsp;</td></tr>
    <tr valign="top" align="center">
      <td align="center">
			<table border="0" cellspacing="01" width="90%" align="center">
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center">&nbsp;</td>
    	          <td class="texto" align="center"><b>Nome do Arquivo:</b></td>
    	          <td class="texto" align="center">&nbsp;</td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center"><nobr>&nbsp;</td>
    	          <td class="texto" align="center"><nobr>&nbsp;
					  <input name="tf_v_nome_arq" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php echo $tf_v_nome_arq ?>" size="80" maxlength="80">&nbsp;</nobr>
				  </td>
				  <td class="texto" align="center">&nbsp;</td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center">&nbsp</td>
    	          <td class="texto" align="center"><input type="submit" name="btPesquisar" value="Pesquisar" class="botao_simples">
				  <td class="texto" align="center">&nbsp;</td>
				  </td>
    	        </tr>
			</table>
			<table border="0" cellspacing="01" align="center" width="100%">
		<?php
			$i=0;
			if (isset($tf_v_nome_arq)) {
				if(pg_num_rows($rs_pins) <> 0) {
			?>
				<tr bgcolor='#000000'><td colspan='5' height='1'></td></tr>
				<tr bgcolor="F0F0F0">
				  <td class="texto" align="center" width="40%"><b>Nome do Arquivo</b>&nbsp;</td>
				  <td class="texto" align="center" width="20%"><b>Senha</b>&nbsp;</td>
				  <td class="texto" align="center" width="15%"><b>Dia e Hora</b></td>
				</tr>
				<tr bgcolor='#000000'><td colspan='5' height='1'></td></tr>
			<?php	
					while($rs_pins_row = pg_fetch_array($rs_pins)){ 
						$bgcolor = (($i++) % 2)?" bgcolor=\"F5F5FB\"":"";
				?>
					<tr<?php echo $bgcolor?> valign="top">
					  <td class="texto">&nbsp;<?php echo $rs_pins_row['psra_nome']?></td>
					  <td class="texto">&nbsp;<?php echo $rs_pins_row['psra_senha']?></td>
					  <td class="texto" align="center">&nbsp;<?php echo $rs_pins_row['psra_data_aux']?>&nbsp;</td>
				   </tr>
			<?php	
					}
				} else {
			?>
					<tr>
					  <td class="texto" align="center" colspan="13">&nbsp;<font color='#FF0000'>N&atilde;o foram encontradas arquivos para os valores escolhidos</font></td>
					</tr>
			<?php
				} 
		}
		?>
			</table>
      </td>
    </tr>
	</table>

	<br>&nbsp;
	<table border="0" cellspacing="0" bgcolor="#FFFFFF" width="100%">
    <tr>
      	<td align="center" class="texto"><nobr>
      		<input type="button" name="btOK" value="Voltar" OnClick="window.location='';" class="botao_simples">
      		</nobr>
      	</td>
    </tr>
	</table>
	<br>&nbsp;

	<table border='0' cellpadding='0' cellspacing='1' bordercolor='#cccccc' style='border-collapse:collapse;'>	
	  <tr align="center"> 
		<td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td><td bgcolor="#FFFFFF" class="texto">Processamento em <?php echo number_format(getmicrotime() - $time_start_stats, 2, '.', '.') ?> s.</font></td><td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td>
	  </tr>
	</table>

	</form>
	<!--A T E N &Ccedil; &Atilde; O : Definido quem receber&aacute; os arquivos ser&aacute; exibido um combo com a listagem de poss&iacute;veis receptores e um bot&atilde;o para vincular os arquivos a estes.-->
	</center>

</body>
</html>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>