<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/gamer/main.php";
require_once "/www/includes/bourls.php";
ini_set('memory_limit','512M');

if (b_IsBKOUsuarioHistorico()) {

//Explicativo
require_once $raiz_do_projeto."class/classDescriptionReport.php";
$descricao = new DescriptionReport('historico_usuario');
$descricao = $descricao->MontaAreaDescricao();
echo str_replace("<script language='JavaScript' src='http://www.e-prepag.com.br/prepag2/dist_commerce/includes/jquery.js'></script>","", $descricao);

?>
<script language="JavaScript">
function VerificaMotivo() {
    var teste = true;
    if((document.form1.tf_v_data_inclusao_ini.value=="") && (document.form1.tf_v_data_inclusao_fim.value=="") && (document.form1.ug_id.value=="") && (document.form1.vg_id.value=="")) { 
        teste = false; 
    } 
    if(teste) return true;
    else {
        alert('Você deve informar ao menos um parametro de filtro!');
        return false;
    }
}//end function VerificaMotivo()
</script>
<link href="<?php echo $url; ?>:<?php echo $server_port ;?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port ;?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port ;?>/js/global.js"></script>
<script language="javascript">
    $(function(){
       var optDate = new Object();
            optDate.interval = 10000;

        setDateInterval('tf_v_data_inclusao_ini','tf_v_data_inclusao_fim',optDate);
        
    });
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>

<form id="form1" name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onsubmit="return VerificaMotivo();">
            <table class="table">
                <tr>
                    <td valign="top">
                        <table class="table">
                                <tr>
                                    <td colspan="4" bgcolor="#DDDDDD">&nbsp;&nbsp;Dados do Usu&aacute;rio</td>
                                </tr>
                                <tr>
									<td align="right">Data da Ação: </font></td>
									<td align="left">
										<input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php if(isset($tf_v_data_inclusao_ini)) echo $tf_v_data_inclusao_ini ?>" size="11" maxlength="10">
										&nbsp;&agrave;&nbsp; 
										<input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php if(isset($tf_v_data_inclusao_fim)) echo $tf_v_data_inclusao_fim ?>" size="11" maxlength="10">
									</td>
                                    <td align="right">Tipo: </td>
                                    <td>
										<select name="ugl_uglt_id" id="ugl_uglt_id" class="form2">
											<option value="">Selecione</option>
											<?php foreach ($GLOBALS['USUARIO_GAMES_LOG_TIPOS_DESCRICAO'] as $formaId => $formaNome){ ?>
												<option value="<?php echo $formaId; ?>" <?php if (isset($ugl_uglt_id) && isset($formaId) && $ugl_uglt_id == $formaId) echo "selected";?>><?php echo $formaId . " - " . $formaNome; ?></option>
											<?php } ?>
										</select>
									</td>
                                </tr>
								<tr>
                                    <td align="right">ID do Usu&aacute;rio: </td>
                                    <td>
										<input name="ug_id" type="text" id="ug_id" size="20" maxlength="10" value="<?php if(isset($ug_id)) echo $ug_id;?>"/>
									</td>
                                    <td align="right">ID da Venda: </td>
                                    <td>
										<input name="vg_id" type="text" id="vg_id" size="20" maxlength="10" value="<?php if(isset($vg_id)) echo $vg_id;?>"/>
									</td>
                                </tr>
                                <tr>
                                    <td colspan="4" align="center"><input name="btn_pesquisar" class="btn btn-sm btn-info" type="submit" id="btn_pesquisar" value="Pesquisar" /></td>
                                </tr>
                            </table>
                    </td>
                </tr>
                <tr align="center">
                <td>
<p align="center">
<?php


if (isset($btn_pesquisar) && $btn_pesquisar=="Pesquisar") {
	$sql  = "SELECT ugl.*, ug_nome	";	//",to_char(ugl_data_inclusao,'DD/MM/YYYY HH:MI:SS') as data_deposito
	$sql .= "FROM usuarios_games_log ugl 
			INNER JOIN usuarios_games ug ON (ugl.ugl_ug_id=ug.ug_id) ";
	if (!empty($ug_id))
		$sql_aux[] = "ugl.ugl_ug_id = ". $ug_id . " ";
	if (!empty($vg_id))
		$sql_aux[] = "ugl.ugl_vg_id = ". $vg_id . " ";
	if (!empty($ugl_uglt_id))
		$sql_aux[] = "ugl.ugl_uglt_id = ". $ugl_uglt_id . " ";
	if($tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim) {
//		$sql_aux[] = "ugl.ugl_data_inclusao >= to_timestamp('".addslashes($tf_v_data_inclusao_ini)." 00:00:00', 'DD/MM/YYYY HH24:MI:SS') ";
//		$sql_aux[] = "ugl.ugl_data_inclusao <= to_timestamp('".addslashes($tf_v_data_inclusao_fim)." 23:59:59', 'DD/MM/YYYY HH24:MI:SS') ";

		$data_ini = substr( $tf_v_data_inclusao_ini,6,4) . "/" . substr($tf_v_data_inclusao_ini,3,2) . "/" . substr($tf_v_data_inclusao_ini,0,2)." 00:00:00";
		$data_fim = substr( $tf_v_data_inclusao_fim,6,4) . "/" . substr($tf_v_data_inclusao_fim,3,2) . "/" . substr($tf_v_data_inclusao_fim,0,2)." 23:59:59";

//echo "tf_v_data_inclusao_ini: '$tf_v_data_inclusao_ini'<br>";
//echo "tf_v_data_inclusao_fim: '$tf_v_data_inclusao_fim'<br>";
//echo "data_ini: '$data_ini'<br>";
//echo "data_fim: '$data_fim'<br>";

		$sql_aux[] = "(ugl.ugl_data_inclusao between '".$data_ini."' and '".$data_fim."') ";

	}
	if (is_array($sql_aux)) {
		$sql .= ' WHERE ' . implode(' AND ', $sql_aux) . ' ';
	}
//	$sql .= ' GROUP BY ugl_id,ugl_data_inclusao,ugl_ip,ugl_uglt_id,ugl_ug_id,ugl_vg_id,ugl_is_drupal,data_deposito,ug_nome ';
	$sql .= ' ORDER BY ugl_data_inclusao DESC';
//if(b_IsUsuarioReinaldo()) {
//echo str_replace("\n", "<br>\n", $sql)."<br>";
//}
	//die($sql);
	$rsResposta = SQLexecuteQuery($sql);
}//end if ($btn_pesquisar=="Pesquisar")
?>
<table width="100%" border="0" align="center" class="texto">
<?php
if(isset($rsResposta) && (pg_num_rows($rsResposta) != 0) && ($rsResposta)) {
?>
	<tr>
        <td align="center">&nbsp;</td>
        <td align="left" colspan="4"><?php echo "Encontrado".((pg_num_rows($rsResposta)>0)?"s":"")." ".pg_num_rows($rsResposta)." registro".((pg_num_rows($rsResposta)>0)?"s":"")."";?></td>
        <td align="center">&nbsp;</td>
    </tr>
	<tr>
        <td bgcolor="#DDDDDD" align="center">&nbsp;</td>
        <td bgcolor="#DDDDDD" align="center">Nome usu&aacute;rio</td>
        <td bgcolor="#DDDDDD" align="center">ID</td>
        <td bgcolor="#DDDDDD" align="center">Data</td>
        <td bgcolor="#DDDDDD" align="center">Tipo</td>
        <td bgcolor="#DDDDDD" align="center">IP</td>
        <td bgcolor="#DDDDDD" align="center">ID da Venda</td>
    </tr>
<?php
} //end if((pg_num_rows($rsResposta) != 0) && ($rsResposta))
$backcolor1 = "#ccffff";
$backcolor2 = "#ffffff";
$bck = $backcolor1;
if(isset($rsResposta)){
    while ($pgResposta = pg_fetch_array ($rsResposta)) {
    ?>
        <tr<?php echo " bgcolor='".$bck."'" ?>>
            <td align="center">&nbsp;</td>
            <td align="left"><?php if($pgResposta['ugl_ug_id']==7909) { echo "<span style='color:blue;font-weight:bold'>".$pgResposta['ug_nome']."</span>"; } else { ?><a href="/gamer/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $pgResposta['ugl_ug_id'];?>"><?php echo $pgResposta['ug_nome'];?></a><?php } ?></td>
             <td align="center"><?php echo $pgResposta['ugl_ug_id'];?></td>

            <td align="center"><nobr><?php echo substr($pgResposta['ugl_data_inclusao'], 0, 19);?></nobr></td>
            <td align="right"><nobr><?php if(isset($GLOBALS['USUARIO_GAMES_LOG_TIPOS_DESCRICAO'])) echo $GLOBALS['USUARIO_GAMES_LOG_TIPOS_DESCRICAO'][$pgResposta['ugl_uglt_id']];?></nobr></td>
            <td align="right"><nobr><?php echo $pgResposta['ugl_ip'];?></nobr></td>
            <td align="center"><nobr><a href="/gamer/vendas/com_venda_detalhe.php?venda_id=<?php echo $pgResposta['ugl_vg_id'];?>"><?php echo $pgResposta['ugl_vg_id'];?></a></nobr></td>
        </tr>
    <?php
        if ($bck == $backcolor1)
            $bck = $backcolor2;
        else $bck = $backcolor1;
    } //end while ($pgResposta = pg_fetch_array ($rsResposta)) 
}
?>
</table>
</p>
               </td>
                </tr>
            </table>
</form>
</body>
</html>
<?php
} // end if (b_IsBKOUsuarioComposicaoFifo())
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>