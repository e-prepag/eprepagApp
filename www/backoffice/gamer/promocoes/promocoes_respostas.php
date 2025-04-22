<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
$promo_id			= isset($_REQUEST['promo_id'])			? htmlentities($_REQUEST['promo_id'])			: '';
$promo_r_resposta	= isset($_REQUEST['promo_r_resposta'])	? htmlentities($_REQUEST['promo_r_resposta'])	: '';

$sqlpromo = "select promo_nome, promo_id from promocoes order by promo_nome"; 
$respromo = SQLexecuteQuery($sqlpromo);

//echo utf8_decode("murillo_du?Œó×Xßpe_Wã1Ìbr");
?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><?php echo $sistema->item->getDescricao(); ?></li>
    </ol>
</div>
<link rel="stylesheet" type="text/css" href="/css/gamer/cssClassLista.css" />
<input type="hidden" name="alert" id="alert" value="Arquivo selecionado n&atilde;o &eacute; uma Imagem V&aacute;lida." />
<table class="table txt-preto fontsize-pp">
    <tr>
        <td valign="top">
            <table class="table">
                <tr>
                    <td valign="top">
                        <form id="form1" name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                        <input type="hidden" name="promo_id" value="<?php //echo $promo_id?>" />
                        <table class="table txt-preto fontsize-pp">
                                <tr>
                                    <td colspan="4" bgcolor="#DDDDDD">Filtro de Pesquisa</td>
                                </tr>
                                <tr>
                                    <td align="right">Promo&ccedil;&atilde;o: </td>
                                    <td>
										<select name="promo_id" id="promo_id" class="combo_normal">
										  <option value="">Selecione uma Promo&ccedil;&atilde;o</option>
										  <?php while ($pgpromo = pg_fetch_array ($respromo)) { ?>
										  <option value="<?php echo $pgpromo['promo_id'] ?>" <?php if($pgpromo['promo_id'] == $promo_id) echo "selected" ?>><?php echo $pgpromo['promo_nome']." (ID: ".$pgpromo['promo_id'].")" ?></option>
										  <?php } ?>
										</select>
									 <td><div align="right">Resposta: </div></td>
                                    <td><input name="promo_r_resposta" type="text" id="promo_r_resposta" size="40" maxlength="40" value="<?php echo $promo_r_resposta;?>"/></td>
                                </tr>
                                <tr>
                                    <td colspan="6" align="center"><input name="btn_pesquisar" class="btn btn-info btn-sm" type="submit" id="btn_pesquisar" value="Pesquisar" /></td>
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
		promo_nome,
		promo_r_id, 
		promo_r_email, 
		ug_id,
		pr.promo_id,
		to_char(promo_r_data,'DD/MM/YYYY HH24:MI:SS') as promo_r_data, 
		promo_r_resposta
	FROM promocoes_resposta pr
		LEFT JOIN promocoes p ON (pr.promo_id=p.promo_id)";
if (!empty($promo_r_resposta))
	$sql_aux[] = "UPPER(promo_r_resposta) LIKE '%" . strtoupper($promo_r_resposta) . "%'";
if (!empty($promo_id))
	$sql_aux[] = "pr.promo_id = ". $promo_id ;
if (isset($sql_aux) && is_array($sql_aux)) {
	$sql .= ' WHERE ' . implode(' AND ', $sql_aux);
}
$sql .= ' ORDER BY pr.promo_r_data desc';
//echo str_replace("\n", "<br>\n", $sql)."<br>";
//die($sql);
$rsResposta = SQLexecuteQuery($sql);
?>
<table class="txt-preto table fontsize-pp table-bordered">
<?php
if((pg_num_rows($rsResposta) != 0) && ($rsResposta)) {
?>
	<tr>
        <td align="center"></td>
        <td align="left" colspan="5"><?php echo "Encontrado".((pg_num_rows($rsResposta)>0)?"s":"")." ".pg_num_rows($rsResposta)." registro".((pg_num_rows($rsResposta)>0)?"s":"")."";?></td>
        <td align="center"></td>
    </tr>
	<tr>
        <td bgcolor="#DDDDDD" align="center">N</td>
        <td bgcolor="#DDDDDD" align="center">Promo&ccedil;&atilde;o</td>
        <td bgcolor="#DDDDDD" align="center">Promo&ccedil;&atilde;oID</td>
        <td bgcolor="#DDDDDD" align="center">Email</td>
        <td bgcolor="#DDDDDD" align="center">IDUsu&aacute;rio</td>
        <td bgcolor="#DDDDDD" align="center">DatadaResposta</td>
        <td bgcolor="#DDDDDD" align="center">Resposta</td>
    </tr>
<?php
}
$contador=1;
while ($pgResposta = pg_fetch_array ($rsResposta)) {
?>
	<tr>
        <td align="center"><?php echo $contador;?></td>
        <td align="center"><nobr><?php echo $pgResposta['promo_nome'];?></nobr></td>
        <td align="center"><?php echo $pgResposta['promo_id'];?></td>
        <td align="center"><?php echo $pgResposta['promo_r_email'];?></td>
        <td align="center"><?php echo (($pgResposta['ug_id']>0 && $pgResposta['ug_id']!=7909) ? "<a href='/gamer/usuarios/com_usuario_detalhe.php?usuario_id=" . $pgResposta['ug_id']."' target='_blank'>" : "").$pgResposta['ug_id'].(($pgResposta['ug_id']>0 && $pgResposta['ug_id']!=7909) ? "</a>" : "");?></td>
        <td align="center"><nobr><?php echo $pgResposta['promo_r_data'];?></nobr></td>
        <td align="center"><?php echo $pgResposta['promo_r_resposta'];?></td>
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
</body>
</html>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>