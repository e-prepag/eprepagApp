<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" /> 
<meta http-equiv="Content-Language" content="pt-br" /> 
<title> Gerenciamento de Arquivos e Senhas </title>
<link href="/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="/js/global.js"></script>
</head>
<body>
<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";
require_once $raiz_do_projeto . "class/classPinsCard.php";

$time_start_stats = getmicrotime();

//Recupera as vendas
if(isset($tf_v_nome_arq)){
	$sql  = "SELECT *,to_char(pcra_dataentrada,'DD/MM/YYYY HH24:MI:SS') as pcra_data_aux from pins_card_rel_arquivos WHERE pcra_nome='".trim($tf_v_nome_arq)."'"; 
//echo $sql ."<br>\n";
	$rs_pins = SQLexecuteQuery($sql);
}
?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><?php echo $sistema->item->getDescricao(); ?></li>
    </ol>
</div>
<div class="col-md-12 table txt-preto fontsize-pp fontsize-pp">            
<form name="form1" method="post" action="pins_card_ger_arq_user.php">
    <table class="table txt-preto fontsize-pp" align="center">
    	        <tr>
    	          <td>&nbsp;</td>
    	          <td align="center"><b>Nome do Arquivo:</b></td>
    	          <td>&nbsp;</td>
    	        </tr>
    	        <tr>
    	          <td>&nbsp;</td>
    	          <td align="center"><nobr>&nbsp;
                        <input name="tf_v_nome_arq" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php echo $tf_v_nome_arq ?>" size="80" maxlength="80">&nbsp;</nobr>
                  </td>
    	          <td>&nbsp;</td>
    	        </tr>
    	        <tr>
    	          <td>&nbsp;</td>
                    <td align="center">
                      <input type="submit" name="btPesquisar" value="Pesquisar" class="btn btn-sm btn-info">
		    </td>
    	          <td>&nbsp;</td>
    	        </tr>
    </table>
    <table class="table txt-preto fontsize-pp">
		<?php
			$i=0;
			if (isset($tf_v_nome_arq)) {
				if(pg_num_rows($rs_pins) <> 0) {
			?>
				<tr bgcolor="F0F0F0">
				  <td width="40%"><b>Nome do Arquivo</b>&nbsp;</td>
				  <td width="20%"><b>Senha</b>&nbsp;</td>
				  <td width="15%"><b>Dia e Hora</b></td>
				</tr>
			<?php	
					while($rs_pins_row = pg_fetch_array($rs_pins)){ 
						$bgcolor = (($i++) % 2)?" bgcolor=\"F5F5FB\"":"";
				?>
					<tr<?php echo $bgcolor?> valign="top">
					  <td>&nbsp;<?php echo $rs_pins_row['pcra_nome']?></td>
					  <td>&nbsp;<?php echo $rs_pins_row['pcra_senha']?></td>
					  <td>&nbsp;<?php echo $rs_pins_row['pcra_data_aux']?>&nbsp;</td>
				   </tr>
			<?php	
					}
				} else {
			?>
					<tr>
					  <td colspan="13">&nbsp;<font color='#FF0000'>N&atilde;o foram encontradas arquivos para os valores escolhidos</font></td>
					</tr>
			<?php
				} 
		}
		?>
        </table>
	<br>&nbsp;
	<table class="table txt-preto fontsize-pp">	
	  <tr> 
		<td align="center">Processamento em <?php echo number_format(getmicrotime() - $time_start_stats, 2, '.', '.') ?> s.</td>
	  </tr>
	</table>

	</form>
	<!--A T E N &Ccedil; &Atilde; O : Definido quem receber&aacute; os arquivos ser&aacute; exibido um combo com a listagem de poss&iacute;veis receptores e um bot&atilde;o para vincular os arquivos a estes.-->
</div>
</body>
</html>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>