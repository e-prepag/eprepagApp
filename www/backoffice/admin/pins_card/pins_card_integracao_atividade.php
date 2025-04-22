<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";
require_once $raiz_do_projeto . "class/classPinsCard.php";
require_once $raiz_do_projeto . "class/classIntegracaoPinCard.php";
require_once $raiz_do_projeto . "class/classIntegracaoPin.php";

$publisher_array	= VetorOperadorasCard();
$operacao_array		= VetorDistribuidorasCard();
$opr_codigo		= isset($_POST['pin_operacao'])	? (int) $_POST['pin_operacao']	: null;
$pin_codigo		= isset($_POST['pin_codigo'])	? $_POST['pin_codigo']		: null;
$time_start_stats = getmicrotime();
//paginacao
$p = $_GET['p'];
if(!$p) $p = 1;
$registros = 500;
$registros_total = 0;
//rotinas de inicializacao se click em botao gerar arquivo

//VericaÁıes e Update
$msg = "";

//Recupera as vendas
if($msg == "" && $btPesquisar){
	$sql_filters = array();
	$sql  = "SELECT *,to_char(pih_data,'DD/MM/YYYY HH24:MI:SS') as pih_data_aux from pins_integracao_card_historico "; 
	if(strlen($tf_v_data_inclusao_ini))
				$sql_filters[] = "pih_data >= to_timestamp('".addslashes($tf_v_data_inclusao_ini)." 00:00:00', 'DD/MM/YYYY HH24:MI:SS')";
	if(strlen($tf_v_data_inclusao_fim))
				$sql_filters[] = "pih_data <= to_timestamp('".addslashes($tf_v_data_inclusao_fim)." 23:59:59', 'DD/MM/YYYY HH24:MI:SS')";
	if(!empty($opr_codigo))
				$sql_filters[] = "pih_id = ".addslashes($opr_codigo);
	if(!empty($pin_codigo) && !empty($opr_codigo))
				$sql_filters[] = "pih_pin_id = ".retorna_id_pin_card($pin_codigo,$opr_codigo);
        elseif(!empty($pin_codigo)) die("… obrigatÛrio a seleÁ„o do Publisher para a Pesquisa de PIN!");
	if(!empty($pin_valor))
				$sql_filters[] = "pih_pin_valor = ".addslashes($pin_valor);
	if (count($sql_filters) > 0) {
		$sql_aux = implode(" and ", $sql_filters);
		$sql  .= "WHERE ".$sql_aux;
	}
	//echo $sql."<br>";
	$rs_total = SQLexecuteQuery($sql);
	if($rs_total) $registros_total = pg_num_rows($rs_total);
	$sql .= " ORDER BY pih_data DESC";	
	$sql .= " offset " . intval(($p - 1) * $registros) . " limit " . intval($registros);
        //echo $sql ."<br>\n";
	$rs_pins = SQLexecuteQuery($sql);
	if(!$rs_pins || pg_num_rows($rs_pins) == 0) $msg = "Nenhum pin encontrado.\n";
}

//funcoes
function implode_with_key($assoc, $inglue = '>', $outglue = ',') {
    $return = '';
 
    foreach ($assoc as $tk => $tv) {
        $return .= $outglue . $tk . $inglue . $tv;
    }
 
    return substr($return, strlen($outglue));
}//end function implode_with_key

function gera_epp_card_integracao() {
	$vetor_aux = array();
	$sql  = "SELECT count(*) as total,pih_id from pins_integracao_card_historico group by pih_id"; 
	$rs_pins = SQLexecuteQuery($sql);
	if($rs_pins) {
		while($rs_pins_row = pg_fetch_array($rs_pins)){ 
			$vetor_aux[$rs_pins_row['pih_id']]['Qtde_Solicitacoes']=$rs_pins_row['total'];
		}//end while
	}//end if($rs_pins)
	else return 0;
	$sql  = "SELECT count(*) as total,pih_id from pins_integracao_card_historico where pih_codretepp='1' group by pih_id"; 
	$rs_pins = SQLexecuteQuery($sql);
	if($rs_pins) {
		while($rs_pins_row = pg_fetch_array($rs_pins)){ 
			$vetor_aux[$rs_pins_row['pih_id']]['Qtde_Consultas_com_Sucesso']=$rs_pins_row['total'];
		}//end while
	}//end if($rs_pins)
	else return 0;
	$sql  = "SELECT count(*) as total,pih_id from pins_integracao_card_historico where pih_codretepp='2' group by pih_id"; 
	$rs_pins = SQLexecuteQuery($sql);
	if($rs_pins) {
		while($rs_pins_row = pg_fetch_array($rs_pins)){ 
			$vetor_aux[$rs_pins_row['pih_id']]['Qtde_Utilizacoes_com_Sucesso']=$rs_pins_row['total'];
		}//end while
	}//end if($rs_pins)
	else return 0;
	$sql  = "SELECT to_char(max(pih_data),'DD/MM/YYYY HH24:MI:SS') as data,pih_id from pins_integracao_card_historico group by pih_id"; 
	$rs_pins = SQLexecuteQuery($sql);
	if($rs_pins) {
		while($rs_pins_row = pg_fetch_array($rs_pins)){ 
			$vetor_aux[$rs_pins_row['pih_id']]['Ultima_Data_de_Utilizacao']=$rs_pins_row['data'];
		}//end while
	}//end if($rs_pins)
	else return 0;
	$retorno_aux = '<table border="1" cellspacing="0" bgcolor="#FFFFFF" width="100%">';
	foreach ($vetor_aux as $key => $value) {
		$retorno_aux .='<tr class="texto"><td colspan="3">'.$GLOBALS['publisher_array'][$key].'</td></tr>';
		foreach ($value as $campo => $valor) {
			$retorno_aux .='<tr class="texto"><td width="15%"></td><td width="25%">'.str_replace("_","&nbsp;",$campo).'</td><td width="60%">'.$valor.'</td></tr>';
		}//end foreach ($value as $campo => $valor)
	}//end foreach ($vetor_aux as $key => $value)
	$retorno_aux .='</table>';
	return $retorno_aux;
}//end function gera_epp_card_integracao()

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
function timedRefresh(timeoutPeriod) {
		setTimeout("location.reload(true);",timeoutPeriod);
	}
function load_dados(dados) {
	//vari√°veis
	var i,html, array_produtos;
	//transforma esta string em um array pr√≥prio do Javascript
	array_produtos = dados.split('|');
	html = '<div align="right" onClick="fecha();" class="link_azul" style="cursor:pointer;cursor:hand;"><font size="1">Fechar [X]</font></div><br><br>';
	//varre o array s√≥ pra mostrar que t√° tudo ok
	for (i in array_produtos)
		html = html + array_produtos[i] + '<BR>';
	html += '<br><center><img src="http://www.e-prepag.com.br/prepag2/commerce/images/voltar.gif" width="88" height="31" border="0" alt="Voltar" OnClick="fecha();" style="cursor:pointer;cursor:hand;"/></center>';
	$('#boxPopUpDadosRecebidos').html(html); 
	$('#boxPopUpDadosRecebidos').show();
}

function fecha() {
	$('#boxPopUpDadosRecebidos').html('');
	$('#boxPopUpDadosRecebidos').hide();
}

$(function(){
	$('body tr')
		.mouseover(function(){
			$(this).addClass('over');
		})
		.mouseout(function(){
			$(this).removeClass('over');
		});
});

-->
</script>
<style type="text/css">
<!--
#boxPopUpDadosRecebidos {
			z-index: 2;
			height: 250px;
			width: 250px;
			color: #000000;
			font-size: 14px;
			background-color: #FFFFFF;
			border: 1px solid #444;
			padding: 5px;
			position: fixed;
			top: 4%;
			left: 80%;
			text-align: left;
			display: none;
			overflow: auto;
			}
#boxPopUpResumo {
			z-index: 2;
			height: 300px;
			width: 300px;
			color: #000000;
			font-size: 14px;
			background-color: #FFFFFF;
			border: 1px solid #444;
			padding: 5px;
			position: fixed;
			top: 4%;
			left: 5%;
			text-align: left;
			display: auto;
			overflow: auto;
			}
.over {	background: #FFFFFF; }
-->
</style>
<div class="col-md-12">
     <ol class="breadcrumb top10">
         <li><a href="#" class="muda-aba" ordem="1">BackOffice - Money</a></li>
         <li class="active">HistÛrico de PINs Cartıes</li>
     </ol>
 </div>
<div class="col-md-12 txt-preto fontsize-pp">
<?php
include "pins_card_menu.php";
?>
<form name="form1" method="post" action="pins_card_integracao_atividade.php">
	<!--Div Box Resumo Dados -->
	<div id='boxPopUpResumo'></div>
	<!--Div Box que exibe Dados -->
	<div id='boxPopUpDadosRecebidos'></div>
    <table class="table txt-preto fontsize-pp">
	<tr valign="top" align="center">
      <td align="center">
			<input type="hidden" name="p" value="<?php echo $p; ?>">
			<table class="table txt-preto fontsize-pp">
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center" colspan="4"><b>Lista de <?php echo ((($p - 1) * $registros)+1); ?> a <?php echo (($p*$registros)); ?> <?php echo " (Total: ".$registros_total." registro"?><?php if($registros_total>1) echo "s"; ?><?php echo ")"?></b></td>
    	        </tr>
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center"><b>Operadora</b></td>
    	          <td class="texto" align="center"><b>Per&iacute;odo de cadastro</b></td>
    	          <td class="texto" align="center"><b>PIN</b></td>
    	          <td class="texto" align="center"><b>Valor</b></td>
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
				  <td class="texto" align="center">&nbsp;<input name="pin_codigo" id="pin_codigo" type="text" value="<?php echo $pin_codigo; ?>" size="20" maxlength="18"></td>
				  <td class="texto" align="center">&nbsp;<input name="pin_valor" id="pin_valor" type="text" value="<?php echo $pin_valor; ?>" size="8" maxlength="5"></td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center" colspan="4"><input type="submit" name="btPesquisar" id="btPesquisar" value="Pesquisar" class="btn btn-info btn-sm">
				  </td>
    	        </tr>
			</table>
			<table class="table txt-preto fontsize-pp">
			<tr bgcolor="F0F0F0">
 			  <td class="texto" align="center" width="10%"><b>ID do PIN</b>&nbsp;</td>
			  <td class="texto" align="center" width="10%"><b>IP Utilizado</b>&nbsp;</td>
			  <td class="texto" align="center" width="10%"><b>Operadora</b>&nbsp;</td>
			  <td class="texto" align="center" width="20%"><b>Dia e Hora</b></td>
			  <td class="texto" align="center" width="20%"><b>Mensagem</b></td>
			  <td class="texto" align="center" width="15%"><b>Status</b>&nbsp;</td>
			  <td class="texto" align="center" width="15%"><b>Valor</b>&nbsp;</td>
			</tr>
    	<?php	
			$i=0;
			$irows=0;
			if($rs_pins) {
				while($rs_pins_row = pg_fetch_array($rs_pins)){ 
					$bgcolor = (($i++) % 2)?" bgcolor=\"F5F5FB\"":" bgcolor=\"CCFFFF\"";
					$irows++;
 			?>
  				<tr<?php echo $bgcolor?> valign="top" <?php if(!empty($rs_pins_row['pih_post'])) { ?>  style="cursor:pointer;cursor:hand;" onClick='javascript: load_dados("<?php $string_array = implode_with_key(unserialize($rs_pins_row['pih_post']),' => ','|');echo $string_array;?>");'<?php } ?>>
				  <td class="texto" align="center">&nbsp;<?php echo $rs_pins_row['pih_pin_id']?></td>
    	          <td class="texto" align="center">&nbsp;<?php echo $rs_pins_row['pih_ip_id']?></td>
    	          <td class="texto" align="center">&nbsp;<?php echo $publisher_array[$rs_pins_row['pih_id']]." (".$rs_pins_row['pih_id'].")"?></td>
    	          <td class="texto" align="center">&nbsp;<?php echo $rs_pins_row['pih_data_aux']?>&nbsp;</td>
    	          <td class="texto" align="center"<?php if(($rs_pins_row['pih_codretepp']==1)||($rs_pins_row['pih_codretepp']==2)) echo " style='color: #3B5998;font-weight: bold;'";?>>&nbsp;<?php echo $notify_list[$rs_pins_row['pih_codretepp']]?>&nbsp;</td>
				  <td class="texto" align="center">&nbsp;<?php echo $rs_pins_row['pin_status']?>&nbsp;</td>
    	          <td class="texto" align="center">&nbsp;<?php echo $rs_pins_row['pih_pin_valor']?>&nbsp;</td>
    	        </tr>
  
		<?php	
				}
				if($irows==0) {
			?>
					<tr>
					  <td class="texto" align="center" colspan="13">&nbsp;<font color='#FF0000'>N&atilde;o foram encontradas tentativas para os valores escolhidos</font></td>
					</tr>
			<?php
				}

			} else {
		?>
    	        <tr>
    	          <td class="texto" align="center" colspan="13">&nbsp;<font color='#FF0000'>N&atilde;o foram encontradas tentativas para os valores escolhidos</font></td>
    	        </tr>
		<?php
			}
		?>
			</table>
      </td>
    </tr>
	</table>

	<br>&nbsp;
	<table class="table txt-preto fontsize-pp">
    <tr>
      	<td align="center" class="texto"><nobr>
      		<?php if($p > 1){ ?>
         	<input type="button" name="btAnterior" value=" < " OnClick="window.location='?p=<?php echo $p-1?><?php echo $varsel?>';" class="btn btn-info btn-sm">
         	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<?php } ?>
         	<input type="button" name="btOK" value="Voltar" OnClick="window.location='index.php';" class="btn btn-info btn-sm">
      		<?php if($p < ($registros_total/$registros)){ ?>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
         	<input type="button" name="btProximo" value=" > " OnClick="window.location='?p=<?php echo $p+1?><?php echo $varsel?>';" class="btn btn-info btn-sm">
			<?php } ?></nobr>
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
	</center>
</div>
<script language="JavaScript">
$(document).ready(function(){
	$('#boxPopUpResumo').html('<?php $aux=gera_epp_card_integracao(); if($aux<>"0") echo $aux; else echo "Erro na gera&ccedil;&atilde;o do monitor de utilizaÁ„o";?>');
});
</script>
</body>
</html>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>