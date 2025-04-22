<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/gamer/constantes.php";
require_once "/www/includes/bourls.php";
if (b_IsBKOUsuarioComposicaoFifo()) {

//Explicativo
require_once $raiz_do_projeto."class/classDescriptionReport.php";
$descricao = new DescriptionReport('composicao_fifo');
echo str_replace("<script language='JavaScript' src='/js/jquery.js'></script>","",$descricao->MontaAreaDescricao());

?>
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>
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
<form id="form1" name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<table class="table txt-preto fontsize-pp">
    <tr>
        <td valign="top">
            <table class="table">
                <tr>
                    <td valign="top">
                            <table class="table">
                                <tr>
                                    <td colspan="4" bgcolor="#DDDDDD">&nbsp;&nbsp;Dados do Usu&aacute;rio</td>
                                </tr>
                                <tr>
									<td align="right">Data Inclusão: </font></td>
									<td align="left" colspan="3">
										<input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php if(isset($tf_v_data_inclusao_ini)) echo $tf_v_data_inclusao_ini ?>" size="9" maxlength="10"> 
										&nbsp;&agrave;&nbsp; 
										<input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php if(isset($tf_v_data_inclusao_fim)) echo $tf_v_data_inclusao_fim ?>" size="9" maxlength="10">
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


$sql = "SELECT scf.*,to_char(scf_data_deposito,'DD/MM/YYYY HH24:MI:SS') as data_deposito, ug_nome
	 FROM saldo_composicao_fifo scf 
		INNER JOIN tb_venda_games vg ON (scf.vg_id=vg.vg_id)
		INNER JOIN usuarios_games ug ON (scf.ug_id=ug.ug_id) \n";
if (!empty($ug_id))
	$sql_aux[] = "scf.ug_id = ". $ug_id . " ";
if (!empty($vg_id))
	$sql_aux[] = "scf.vg_id = ". $vg_id . " ";
if(strlen($tf_v_data_inclusao_ini))
	$sql_aux[] = "scf.scf_data_deposito >= to_timestamp('".addslashes($tf_v_data_inclusao_ini)." 00:00:00', 'DD/MM/YYYY HH24:MI:SS') \n";
if(strlen($tf_v_data_inclusao_fim))
	$sql_aux[] = "scf.scf_data_deposito <= to_timestamp('".addslashes($tf_v_data_inclusao_fim)." 23:59:59', 'DD/MM/YYYY HH24:MI:SS') \n";
if (isset($sql_aux) && is_array($sql_aux)) {
	$sql .= " WHERE " . implode(' AND ', $sql_aux) . " \n";
}
$sql .= " GROUP BY scf.scf_id, scf.ug_id, scf_data_deposito, scf_valor, scf_valor_disponivel, scf_status, scf_canal, scf_comissao, scf_id_pagamento, scf.vg_id, data_deposito, ug_nome\n";
$sql .= " ORDER BY scf_data_deposito DESC\n";

/*
if(b_IsUsuarioReinaldo()) {
echo str_replace("\n", "<br>\n", $sql)."<br>";
//die($sql);
}
*/
$rsResposta = SQLexecuteQuery($sql);
}//end if ($btn_pesquisar=="Pesquisar")
?>
<table width="100%" border="0" align="center" class="texto">
<?php
if(isset($rsResposta) && (pg_num_rows($rsResposta) != 0)) {
?>
	<tr>
        <td align="center">&nbsp;</td>
        <td align="left" colspan="4"><?php echo "Encontrado".((pg_num_rows($rsResposta)>0)?"s":"")." ".pg_num_rows($rsResposta)." registro".((pg_num_rows($rsResposta)>0)?"s":"")."";?></td>
        <td align="center">&nbsp;</td>
    </tr>
	<tr>
        <td bgcolor="#DDDDDD" align="center">&nbsp;</td>
        <td bgcolor="#DDDDDD" align="center">Nome usu&aacute;rio</td>
        <td bgcolor="#DDDDDD" align="center">Status</td>
        <td bgcolor="#DDDDDD" align="center">Data</td>
        <td bgcolor="#DDDDDD" align="center">Valor Depósito R$</td>
        <td bgcolor="#DDDDDD" align="center">Valor Disponível R$</td>
        <td bgcolor="#DDDDDD" align="center">Tipo Pagto.</td>
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
            <td align="left"><nobr><a href="/gamer/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $pgResposta['ug_id'];?>"><?php echo $pgResposta['ug_nome'];?></a></nobr></td>
            <td align="center"><nobr><?php if($pgResposta['scf_status']==1) echo "Disponível"; else echo "Utilizado";?></nobr></td>
            <td align="center"><nobr><?php echo $pgResposta['data_deposito'];?></nobr></td>
            <td align="right"><nobr><?php echo number_format($pgResposta['scf_valor'], 2, ',', '.');?></nobr></td>
            <td align="right"><nobr><?php echo number_format($pgResposta['scf_valor_disponivel'], 2, ',', '.');?></nobr></td>
            <td align="center"><nobr><?php echo $FORMAS_PAGAMENTO_DESCRICAO[$pgResposta['scf_id_pagamento']];?></nobr></td>
            <td align="center"><nobr><a href="/gamer/vendas/com_venda_detalhe.php?venda_id=<?php echo $pgResposta['vg_id'];?>"><?php echo $pgResposta['vg_id'];?></a></nobr></td>
        </tr>
    <?php
        if ($bck == $backcolor1)
            $bck = $backcolor2;
        else $bck = $backcolor1;
    } 
} //end while ($pgResposta = pg_fetch_array ($rsResposta)) 
?>
</table>
</p>
               </td>
                </tr>
            </table>
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