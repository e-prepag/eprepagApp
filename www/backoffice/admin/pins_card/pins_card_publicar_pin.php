<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";
require_once $raiz_do_projeto . "class/classPinsCard.php";

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" /> 
<meta http-equiv="Content-Language" content="pt-br" /> 
<title> pins_card Publicar </title>
<link href="/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="/js/global.js"></script>
<script language="JavaScript">
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
	function reload() {
		document.form1.action = "pins_card_publicar_pin.php";
		document.form1.submit();
	}
	function reload_tot() {
		if (document.form1.pin_tot_lote.value=="S")
			document.form1.action = "pins_card_publicar.php";
		else document.form1.action = "pins_card_publicar_pin.php";
		document.form1.submit();
	}
	function aciona() {
        document.form1.action = "pins_card_publicar_pin.php";
		document.form1.submit();
	}
	function verifica()
	{
		if ((event.keyCode<47)||(event.keyCode>58)){
			  alert("Somente numeros sao permitidos");
			  event.returnValue = false;
		}
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
	function ValidateForm() {
		if (algumCheckBoxSel())
		{
			if (confirm("Realmente deseja Publicar este PIN?"))
			{
				document.form1.action = "pins_card_publicar_pin.php";
				document.form1.submit();
			}
		}
		else  {
			alert("Deve ser selecionado ao menos um PIN\n para publicar!");
			return false;
		}
	}
-->
</script>
</head>

<body>
<?php

$publisher_array	= VetorOperadorasCard();
$operacao_array     	= VetorDistribuidorasCard();
$tot_lote 		= isset($_POST['pin_tot_lote'])		? $_POST['pin_tot_lote']	: 'S';
$opr_codigo		= isset($_POST['opr_codigo'])		? $_POST['opr_codigo']		: null;
$distributor_codigo 	= isset($_POST['pin_operacao'])		? $_POST['pin_operacao']	: null;
$lote           	= isset($_POST['pin_lote'])		? $_POST['pin_lote']		: null;
$valor          	= isset($_POST['pin_valor'])		? $_POST['pin_valor']		: null;
$ids_temp       	= isset($_POST['chkPIN'])		? $_POST['chkPIN']		: null;
$testeSubmit    	= isset($_POST['Publicar'])		? $_POST['Publicar']		: null;
$time_start_stats 	= getmicrotime();

//paginacao
$p = $_GET['p'];
if(!$p) $p = 1;
$registros = 50;
$registros_total = 0;

//Verica��es e Update
$msg = "";
$msg_pin = "";

if($testeSubmit=="Publicar") {
        //Inicia transacao
        if($msg == ""){
                $sql = "BEGIN TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "<font color='#FF0000'><b>Erro ao iniciar transa&cceil;&atilde;o.\n</b></font><br>";
        }
        // testa status do pin antes de mudar
        $ids="";
        for ($i=0; $i<count($ids_temp);$i++) {
                $ids_temp[$i] = intval($ids_temp[$i]);
                $sql = "select pin_status from pins_card where pin_codinterno=".$ids_temp[$i].";";
                //echo $sql."<br>";
                $rs_pins = SQLexecuteQuery($sql);
                if(pg_num_rows($rs_pins) <> 0) {
                        $rs_pin_row = pg_fetch_array($rs_pins);
                        $pin_status = $rs_pin_row['pin_status'];
                        if (($pin_status == '0')||($pin_status == $PINS_STORE_STATUS_VALUES['P'])||($pin_status == $PINS_STORE_STATUS_VALUES['A'])||($pin_status == $PINS_STORE_STATUS_VALUES['U'])||($pin_status == $PINS_STORE_STATUS_VALUES['C'])) {
                                $msg_pin .= "O PIN ".$ids_temp[$i]." n&atilde;o pode ter seu Status alterado pois est&aacute; com Status de <font color='".$PINS_STORE_STATUS_COLORS[$pin_status]."'>".strtoupper($PINS_STORE_STATUS[$pin_status])." (".$pin_status.")</font>. <br>";
                        }
                        else if (strlen($ids)==0) {
                                                $ids = $ids_temp[$i];
                                }
                                else $ids .= ','.$ids_temp[$i];
                }
        }
        if(strlen($ids)>0) {
                if($msg == ""){
                        // Passa para o novo estado
                        $sql  = "update pins_card set pin_status='".intval($PINS_STORE_STATUS_VALUES['P'])."' where pin_codinterno IN (".$ids.") and pin_status='".intval($PINS_STORE_STATUS_VALUES['D'])."';";
                        //echo $sql."<br>";
                        $rs_pins_save = SQLexecuteQuery($sql);
                        if($rs_pins_save ) {
                                        $msg_pin .= "PINs atualizados com sucesso ($ids)<br>";
                        } else {
                                        $msg = "Erro ao atualizar os PINs ($ids)<br>$sql<br>";
                        }
                }//end if($msg == "")
        } else {
                $msg_pin .= "<font color='#FF0000'><b>N&atilde;o foi selecionado nenhum PIN v&aacute;lido para publica&ccedil;&atilde;o!</b></font><br>";
        }
        //Finaliza transacao
        if($msg == ""){
                $sql = "COMMIT TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "<font color='#FF0000'><b>Erro ao comitar transa&ccedil;&atilde;o.\n<br></b></font><br>";
        } else {
                $sql = "ROLLBACK TRANSACTION ";
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "<font color='#FF0000'><b>Erro ao dar rollback na transa&ccedil;&atilde;o.\n<br></b></font><br>";
        }
}

//Recupera as vendas
if($msg == ""){

        $sql  = "select * from pins_card ps where pin_status='".intval($PINS_STORE_STATUS_VALUES['D'])."' ";
        if(strlen($opr_codigo))
            $sql .= " and opr_codigo=".intval($opr_codigo);
        if(strlen($distributor_codigo))
            $sql .= " and distributor_codigo=".intval($distributor_codigo);
        if(strlen($lote))
            $sql .= " and pin_lote_codigo=".intval($lote);
        if(strlen($valor))
            $sql .= " and pin_valor=".intval($valor);
        if(strlen($tf_v_data_inclusao_ini))
            $sql .= " and pin_dataentrada >= to_timestamp('".addslashes($tf_v_data_inclusao_ini)." 00:00:00', 'DD/MM/YYYY HH24:MI:SS')";
        if(strlen($tf_v_data_inclusao_fim))
            $sql .= " and pin_dataentrada <= to_timestamp('".addslashes($tf_v_data_inclusao_fim)." 23:59:59', 'DD/MM/YYYY HH24:MI:SS')";
        $rs_total = SQLexecuteQuery($sql);
        if($rs_total) $registros_total = pg_num_rows($rs_total);
        $sql .= " order by pin_codinterno desc ";	
        $sql .= " offset " . intval(($p - 1) * $registros) . " limit " . intval($registros);
        //echo $sql ."<br>\n";
        $rs_pins = SQLexecuteQuery($sql);
        if(!$rs_pins || pg_num_rows($rs_pins) == 0) $msg = "Nenhum pin encontrado.\n";
}

?>
<div class="col-md-12">
     <ol class="breadcrumb top10">
         <li><a href="#" class="muda-aba" ordem="1">BackOffice - Money</a></li>
         <li class="active">Publicar PINs Cart�es PIN por PIN</li>
     </ol>
 </div>
 <div class="col-md-12 txt-preto fontsize-pp">
<?php
include "pins_card_menu.php";
?>
<form name="form1" method="post" action="pins_card_publicar_pin.php" onsubmit="javascript:return reload();">
	<table class="table txt-preto fontsize-pp">
    <tr><td align="right"><font face="Arial, Helvetica, sans-serif" size="2" color="#00008C"><a href="index.php" class="menu"><img src="../../../images/voltar.gif" width="47" height="15" border="0"></a></font></td></tr>
	<?php
	echo "<tr><td>".$msg_pin."</td></tr>";
	?>
    <tr valign="top" align="center">
      <td>
			<input type="hidden" name="p" value="<?php echo $p; ?>">
			<table border="0" cellspacing="01" width="90%" align="center">
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center" colspan="3"><b>Lista de <?php echo ((($p - 1) * $registros)+1); ?> a <?php echo (($p*$registros)); ?> <?php echo " (Total: ".$registros_total." registro"?><?php if($registros_total>1) echo "s"; ?><?php echo ")"?></b></td>
    	        </tr>
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center" width="400"><b>Publisher</b></td>
    	          <td class="texto" align="center"><b>Per&iacute;odo de cadastro</b></td>
    	          <td width="200">&nbsp;</td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center"><nobr>&nbsp;
                            <select name="opr_codigo" id="opr_codigo" class="combo_normal">
                                    <option value=''<?php if(!$opr_codigo) echo "selected"?>>Selecione o Publisher</option>
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
                <td class="texto" align="center">&nbsp;Valor
                  <input name="pin_valor" id="pin_valor" type="text" value="<?php echo $valor; ?>" size="10" maxlength="10" onKeypress="return verifica();"></td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
    	          <td class="texto" align="center">
                                    <select name="pin_operacao" id="pin_operacao" onChange='reload()'>
                                        <option value='' <?php echo (($distributor_codigo=="")?" selected":"") ?>>Todas as Distribuidoras</option>
									<?php foreach ($operacao_array as $key => $value) { ?>
                                        <option value="<?php echo $key ?>" <?php if($key == $distributor_codigo) echo "selected"; ?>><?php echo $value; ?></option>
                                        <?php } ?>
                                     </select>
									Totaliza por Lotes
									 <select name="pin_tot_lote" id="pin_tot_lote" onChange='reload_tot()'>
                                        <option value='S' <?php echo (($tot_lote=="S")?" selected":"") ?>>SIM</option>
									    <option value="N" <?php echo (($tot_lote=="N")?" selected":"") ?>>N&Atilde;O</option>
                                     </select>
		  </td>
    	          <td class="texto" align="center">&nbsp;Lote
                    <input name="pin_lote" id="pin_lote" type="text" value="<?php echo $lote; ?>" size="10" maxlength="10" onKeypress="return verifica();">
                  </td>
                  <td class="texto" align="center">&nbsp;<input type="submit" name="btPesquisar" value="Pesquisar" class="botao_simples"></td>
    	        </tr>
			</table>
			
			<table border="0" cellspacing="01" align="center" width="100%">
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center" width="10%"><a href="javascript:marcar_desmarcar();">Marcar<br>Desmarcar</a></td>
    	          <td class="texto" align="center" width="5%"><b>ID</b>&nbsp;</td>
    	          <td class="texto" align="center" width="10%"><b>Publisher</b></td>
    	          <td class="texto" align="center" width="10%"><b>Distribuidora</b></td>
    	          <td class="texto" align="center" width="10%"><b>Lote</b></td>
    	          <td class="texto" align="center" width="10%"><b>Valor</b></td>
    	          <td class="texto" align="center" width="10%"><b>Status</b></td>
    	          <td class="texto" align="center" width="40%"><b>Serial Number</b></td>
            </tr>
		<?php	

			$i=0;
			$irows=0;
			if($rs_pins) {

				while($rs_pins_row = pg_fetch_array($rs_pins)){ 
					$bgcolor = ((++$i) % 2)?" bgcolor=\"F5F5FB\"":"";
					$irows++;
										
			?>
    	        <tr<?php echo $bgcolor?> valign="top">
    	          <td class="texto" align="center">&nbsp;<input name="chkPIN[]" id="chkPIN" type="checkbox" value="<?php echo $rs_pins_row['pin_codinterno'];?>" />&nbsp;</td>
                  <td class="texto" align="center">&nbsp;<?php echo $rs_pins_row['pin_codinterno']?></td>
    	          <td class="texto" align="center"><nobr>&nbsp;<?php echo $publisher_array[$rs_pins_row['opr_codigo']]." (".$rs_pins_row['opr_codigo'].")"; ?>&nbsp;</nobr></td>
    	          <td class="texto" align="center"><nobr>&nbsp;<?php echo $operacao_array[$rs_pins_row['distributor_codigo']]." (".$rs_pins_row['distributor_codigo'].")" ?>&nbsp;</nobr></td>
    	          <td class="texto" align="center"><nobr>&nbsp;<?php echo str_replace("-", "", substr($rs_pins_row['pin_dataentrada'],0,10))."_".$rs_pins_row['pin_lote_codigo']?>&nbsp;</nobr></td>
    	          <td class="texto" align="center">&nbsp;<?php echo $rs_pins_row['pin_valor']?>&nbsp;</td>
    	          <td class="texto" align="center"><nobr>&nbsp;<font color="<?php echo $PINS_STORE_STATUS_COLORS[$rs_pins_row['pin_status']]?>"><?php echo $PINS_STORE_STATUS[$rs_pins_row['pin_status']]." (".$rs_pins_row['pin_status'].")"?></font>&nbsp;</nobr></td>
    	          
    	          <td class="texto" align="center"><nobr>&nbsp;<?php echo $rs_pins_row['pin_serial']?>&nbsp;</nobr></td>
    	        </tr>
		<?php
				}

				if($irows==0) {
			?>
					<tr>
					  <td class="texto" align="center" colspan="7">&nbsp;<font color='#FF0000'>N&atilde;o foram encontrados pins para os valores escolhidos</font></td>
					</tr>
			<?php
				}

			} else {
		?>
    	        <tr>
    	          <td class="texto" align="center" colspan="7">&nbsp;<font color='#FF0000'>N&atilde;o foram encontrados pins para os valores escolhidos</font></td>
    	        </tr>
		<?php
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
      		<?php if($p > 1){ ?>
         	<input type="button" name="btAnterior" value=" < " OnClick="window.location='?p=<?php echo $p-1?><?php echo $varsel?>';" class="botao_simples">
         	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<?php } ?>
         	<input type="button" name="btOK" value="Voltar" OnClick="window.location='index.php';" class="botao_simples">
      		<input type="submit" name="Publicar" value="Publicar" class="botao_simples">
      		<?php if($p < ($registros_total/$registros)){ ?>
         	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
         	<input type="button" name="btProximo" value=" > " OnClick="window.location='?p=<?php echo $p+1?><?php echo $varsel?>';" class="botao_simples">
			<?php } ?></nobr>
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
	</center>

</body>
</html>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
