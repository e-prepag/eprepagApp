<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";

$ug_id	= isset($_REQUEST['ug_id'])	? htmlentities($_REQUEST['ug_id'])	: '';
$di_ip	= isset($_REQUEST['di_ip'])	? htmlentities($_REQUEST['di_ip'])	: '';

?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<input type="hidden" name="alert" id="alert" value="Arquivo selecionado n&atilde;o &eacute; uma Imagem V&aacute;lida." />
<table class="table txt-preto fontsize-pp">
    <tr>
        <td valign="top">
            <table class="table txt-preto fontsize-pp">
                <tr>
                    <td valign="top">
                        <form id="form1" name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                        <input type="hidden" name="ug_id" value="<?php //echo $ug_id?>" />
                            <table width="80%" border="0" align="center" style="font-family:verdana, arial;font-size:12px;">
                                <tr>
                                    <td colspan="4" bgcolor="#DDDDDD">&nbsp;&nbsp;Filtro de Pesquisa</td>
                                </tr>
                                <tr>
                                    <td align="right">C&oacute;digo da LAN House: </td>
                                    <td>
										<input name="ug_id" type="text" id="ug_id" size="40" maxlength="40" value="<?php echo $ug_id;?>"/>
									 <td><div align="right">IP: </div></td>
                                    <td><input name="di_ip" type="text" id="di_ip" size="40" maxlength="40" value="<?php echo $di_ip;?>"/></td>
                                </tr>
                                <tr>
                                    <td colspan="6" align="center"><input name="btn_pesquisar" type="submit" class="top10 btn btn-sm btn-info" id="btn_pesquisar" value="Pesquisar" /></td>
                                </tr>
                            </table>
                        </form>
                    </td>
                </tr>
                <tr align="center">
                <td>
<p align="center">
<?php
$sql = "SELECT
		(CASE WHEN (ug.ug_tipo_cadastro='PJ') THEN upper(ug.ug_nome_fantasia)||' ('||ug.ug_tipo_cadastro||')' WHEN (ug.ug_tipo_cadastro='PF') THEN upper(ug.ug_nome)||' ('||ug.ug_tipo_cadastro||')' END) as ug_nome,
		CASE WHEN di_ativo = '1' THEN '<div style=\'background-color:orange;color:blue;text-align:center\'>Sim</div>' ELSE 'N&atilde;o' END as di_ativo,
		di.ug_id,
		to_char(di_data_ativacao,'DD/MM/YYYY HH24:MI:SS') as di_data_ativacao_format, 
		to_char(di_data_desativacao,'DD/MM/YYYY HH24:MI:SS') as di_data_desativacao, 
		di_ip,
		di_remote_addr,
		di_http_client_ip,
		di_http_x_forwarded_for
	FROM dist_ip di
		INNER JOIN dist_usuarios_games ug ON (di.ug_id=ug.ug_id) ";
if (!empty($di_ip))
	$sql_aux[] = "UPPER(di_ip) LIKE '%" . strtoupper($di_ip) . "%' ";
if (!empty($ug_id))
	$sql_aux[] = "ug.ug_id = ". $ug_id . " ";
if (isset($sql_aux) && is_array($sql_aux)) {
	$sql .= ' WHERE ' . implode(' AND ', $sql_aux);
}
$sql .= ' ORDER BY ug.ug_id, di_data_ativacao desc';
//$sql .= ' ORDER BY di_id desc';
//echo str_replace("\n", "<br>\n", $sql)."<br>";
//die($sql);
$rsResposta = SQLexecuteQuery($sql);
?>
<table width="100%" border="0" align="center" class="texto">
<?php
if((pg_num_rows($rsResposta) != 0) && ($rsResposta)) {
?>
	<tr>
        <td align="center">&nbsp;</td>
        <td align="left" colspan="4"><?php echo "Encontrado".((pg_num_rows($rsResposta)>0)?"s":"")." ".pg_num_rows($rsResposta)." registro".((pg_num_rows($rsResposta)>0)?"s":"")."";?></td>
        <td align="center">&nbsp;</td>
    </tr>
	<tr>
        <td align="center">&nbsp;</td>
        <td align="left" colspan="4"><div id="txt_n_lans"></div></td>
        <td align="center">&nbsp;</td>
    </tr>
	<tr>
        <td bgcolor="#DDDDDD" align="center">N</td>
        <td bgcolor="#DDDDDD" align="center">Nome usu&aacute;rio</td>
        <td bgcolor="#DDDDDD" align="center">ID usu&aacute;rio</td>
        <td bgcolor="#DDDDDD" align="center">IP</td>
        <td bgcolor="#DDDDDD" align="center">Ativo</td>
        <td bgcolor="#DDDDDD" align="center">Data Ativa&ccedil;&atilde;o</td>
        <td bgcolor="#DDDDDD" align="center">Data Desativa&ccedil;&atilde;o</td>
        <td bgcolor="#DDDDDD" align="center">REMOTE_ADDR</td>
        <td bgcolor="#DDDDDD" align="center">HTTP_CLIENT_IP</td>
        <td bgcolor="#DDDDDD" align="center">HTTP_X_FORWARDED_FOR</td>
    </tr>
<?php
}
$contador=1;
$backcolor1 = "#ccffff";
$backcolor2 = "#ffffff";
$bck = $backcolor1;
$ug_id_prev = 0;
$n_ug = 0;
while ($pgResposta = pg_fetch_array ($rsResposta)) {

	if($ug_id_prev!=$pgResposta['ug_id']) {
		$ug_id_prev = $pgResposta['ug_id'];
		$bck = (($bck == $backcolor1)?$backcolor2:$backcolor1);
		$n_ug ++;
	}

?>
	<tr<?php echo " bgcolor='".$bck."'" ?>>
        <td align="center"><?php echo $contador;?></td>
        <td align="left"><nobr><?php echo $pgResposta['ug_nome'];?></nobr></td>
        <td align="center"><?php echo (($pgResposta['ug_id']>0 && $pgResposta['ug_id']!=7909) ? "<a href='/gamer/usuarios/com_usuario_detalhe.php?usuario_id=" . $pgResposta['ug_id']."' target='_blank'>" : "").$pgResposta['ug_id'].(($pgResposta['ug_id']>0 && $pgResposta['ug_id']!=7909) ? "</a>" : "");?></td>
        <td align="center"><nobr><?php echo $pgResposta['di_ip'];?></nobr></td>
        <td align="center"><?php echo $pgResposta['di_ativo'];?></td>
        <td align="center"><nobr><?php echo $pgResposta['di_data_ativacao_format'];?></nobr></td>
        <td align="center"><nobr><?php echo $pgResposta['di_data_desativacao'];?></nobr></td>
        <td align="center"><nobr><?php echo $pgResposta['di_remote_addr'];?></nobr></td>
        <td align="center"><nobr><?php echo $pgResposta['di_http_client_ip'];?></nobr></td>
        <td align="center"><nobr><?php echo $pgResposta['di_http_x_forwarded_for'];?></nobr></td>
    </tr>
<?php
	$contador++;
}


?>
</table>
</p>
               </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<script language="JavaScript" type="text/JavaScript">
	$("#txt_n_lans").html('<?php echo "Encontrada".(($n_ug!=1)?"s":"")." ".$n_ug. " lan".(($n_ug!=1)?"s":"") ?>');
</script>
</body>
</html>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>