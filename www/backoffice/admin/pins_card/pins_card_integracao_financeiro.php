<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";
require_once $raiz_do_projeto . "class/classPinsCard.php";
require_once $raiz_do_projeto . "class/classIntegracaoPinCard.php";
require_once $raiz_do_projeto . "class/classIntegracaoPin.php";

$publisher_array	= VetorOperadorasCard();
$operacao_array		= VetorDistribuidorasCard();
$opr_codigo 		= isset($_POST['pin_operacao'])     ? (int) $_POST['pin_operacao']		: null;
$time_start_stats = getmicrotime();
//Instanciando Objetos para Descriptografia
$chave256bits = new Chave();
$pc = new AES($chave256bits->retornaChave());

set_time_limit ( 30000 ) ;

$registros_total = 0;
//rotinas de inicializacao se click em botao gerar arquivo

//Vericações e Update
$msg = "";

//Recupera as vendas
if(!empty($btPesquisar)){ 
        $sql  = "SELECT *,
					to_char(pih_data,'DD/MM/YYYY HH24:MI:SS') as pih_data_aux 
                                        from pins_integracao_card_historico pich
                                        	inner join pins_card ON pin_codinterno=pih_pin_id
                WHERE pih_codretepp='".$notify_list_values['SU']."'
                    and pich.pin_status =4
                    and (CASE WHEN opr_codigo = 90 THEN pin_lote_codigo > 6 ELSE pin_lote_codigo > 0 END) -- Codigo de lotes menor e igual a 6 foram utilizados para testes SOMENTE RIOT
                    "; 
	if(!empty($opr_codigo))
				$sql .= " and pih_id = ".addslashes($opr_codigo);
	if(strlen($tf_v_data_inclusao_ini))
				$sql .= " and pih_data >= to_timestamp('".addslashes($tf_v_data_inclusao_ini)." 00:00:00', 'DD/MM/YYYY HH24:MI:SS')";
	if(strlen($tf_v_data_inclusao_fim))
				$sql .= " and pih_data <= to_timestamp('".addslashes($tf_v_data_inclusao_fim)." 23:59:59', 'DD/MM/YYYY HH24:MI:SS')";
	$sql .= " ORDER BY pih_data DESC";	
	//echo $sql ."<br>\n";
	$rs_pins = SQLexecuteQuery($sql);
        $registros_total = pg_num_rows($rs_pins);
	if(!$rs_pins || pg_num_rows($rs_pins) == 0) $msg = "Nenhum pin encontrado.\n";
}
?>
<link href="/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="/js/global.js"></script>
<script>
$(function(){
    var optDate = new Object();
        optDate.interval = 1000;

    setDateInterval('tf_v_data_inclusao_ini','tf_v_data_inclusao_fim',optDate);
});

<!--
	function algumCheckBoxSel()
	{
		frm = document.form1;
		for(i=0; i < frm.length; i++)
		{
			if (frm.elements[i].type == "checkbox")
			{
				if(frm.elements[i].checked)
				{
					return true;
				}
			}
		}
		return false;
	}
	function marcar_desmarcar() {
		frm = document.form1;
		for ( i=1; i < frm.elements.length; i++ ) {
			if ( frm.elements[i].type == "checkbox" ) {
				if ( frm.elements[i].checked == 1 ) {
				   frm.elements[i].checked = 0;
				} else {
				   frm.elements[i].checked = 1;
				}
			}
		}
	}
--> 
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><?php echo $sistema->item->getDescricao(); ?></li>
    </ol>
 </div>
<div class="col-md-12 txt-preto fontsize-pp">
<?php
include "pins_card_menu.php";
?>
<form name="form1" method="post" action="pins_card_integracao_financeiro.php">
<table class="table txt-preto fontsize-pp">
    <tr valign="top" align="center">
      <td align="center">
			<input type="hidden" name="p" value="<?php echo $p; ?>">
			<table class="table txt-preto fontsize-pp">
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center" colspan="3"><b><?php echo " (Total: ".$registros_total." registro"?><?php if($registros_total>1) echo "s"; ?><?php echo ")"?></b></td>
    	        </tr>
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center"><b>Operadora</b></td>
    	          <td class="texto" align="center"><b>Per&iacute;odo de cadastro</b></td>
    	          <td class="texto" align="center">&nbsp;</td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center"><nobr>
				  <select name="pin_operacao" id="pin_operacao" class="combo_normal">
					<option value=''<?php if(!$pin_operacao) echo "selected"?>>Selecione a operadora</option>
			        <?php foreach ($publisher_array as $key => $value) { ?>
				    <option value=<?php echo "\"".$key.(($opr_codigo==$key)?"\" selected":"\""); ?>><?php echo $value; ?></option>
					<?php } ?>
					</select>
				  </td>
    	          <td class="texto" align="center"><nobr>&nbsp;
					  <input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php echo $tf_v_data_inclusao_ini ?>" size="9" maxlength="10">
					  a 
					  <input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php echo $tf_v_data_inclusao_fim ?>" size="9" maxlength="10">
				  </td>
				  <td class="texto" align="center">&nbsp;</td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center" colspan="3"><input type="submit" name="btPesquisar" value="Pesquisar" class="btn btn-sm btn-info">
		  </td>
    	        </tr>
        </table>
<br>                        
        <table class="table txt-preto fontsize-pp">
                    <tr bgcolor='#000000'><td colspan='6' height='1'></td></tr>
                    <tr bgcolor="F0F0F0">
			  <td class="texto" align="center"><b>ID do PIN</b>&nbsp;</td>
			  <td class="texto" align="center"><b>Integrador</b>&nbsp;</td>
			  <td class="texto" align="center"><b>Dia e Hora Utilização</b>&nbsp;</td>
			  <td class="texto" align="center"><b>VAZIO</b>&nbsp;</td>
			  <td class="texto" align="center"><b>PIN</b>&nbsp;</td>
			  <td class="texto" align="center"><b>Valor do PIN</b>&nbsp;</td>
			</tr>
			<tr bgcolor='#000000'><td colspan='6' height='1'></td></tr>
    	<?php	

			$i=0;
			$irows=0;
                        $valorTotalGeral=0;
			if($rs_pins) {
				while($rs_pins_row = pg_fetch_array($rs_pins)){ 
					$bgcolor = (($i++) % 2)?" bgcolor=\"F5F5FB\"":"";
					$irows++;
                                        $valorTotalGeral += $rs_pins_row['pin_valor'];
			?>
    	        <tr<?php echo $bgcolor?> valign="top">
		  <td class="texto" align="right">&nbsp;<?php echo $rs_pins_row['pih_pin_id']; ?></td>
    	          <td class="texto" align="center">&nbsp;<?php echo $publisher_array[$rs_pins_row['pih_id']]." (".$rs_pins_row['pih_id'].")"; ?></td>
    	          <td class="texto" align="center">&nbsp;<?php echo $rs_pins_row['pih_data_aux'];?>&nbsp;</td>
    	          <td class="texto" align="center">&nbsp;</td>
                  <td class="texto" align="center">&nbsp;<?php echo $pc->decrypt(base64_decode($rs_pins_row['pin_codigo'])); ?>&nbsp;</td>
    	          <td class="texto" align="right">&nbsp;<?php echo number_format($rs_pins_row['pin_valor'],2,',','.'); ?>&nbsp;</td>
    	        		</tr>
    	<?php	
				}
				if($irows==0) {
			?>
					<tr>
					  <td class="texto" align="center" colspan="13">&nbsp;<font color='#FF0000'>N&atilde;o foram encontradas arquivos para os valores escolhidos</font></td>
					</tr>
			<?php
				}
				else {
			?>
                                        <tr bgcolor='#000000'><td colspan='6' height='1'></td></tr>
                                        <tr bgcolor="F0F0F0">
					<tr>
					  <td class="texto" align="center" colspan="13">&nbsp;<font color='#FF0000'>Valor Total Geral no Per&iacute;odo: <?php echo number_format($valorTotalGeral,2,',','.')?></font></td>
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
		?>
      </td>
    </tr>
	</table>
	<br>&nbsp;
	<table class="table txt-preto fontsize-pp">
	  <tr align="center"> 
		<td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td><td bgcolor="#FFFFFF" class="texto">Processamento em <?php echo number_format(getmicrotime() - $time_start_stats, 2, '.', '.') ?> s.</font></td><td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td>
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