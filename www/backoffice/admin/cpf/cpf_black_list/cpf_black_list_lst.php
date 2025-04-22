<?php
require_once '../../../../includes/constantes.php';
require_once $raiz_do_projeto."includes/security.php";
require_once "/www/includes/bourls.php";
// inclue a classe para a listagem
require_once $raiz_do_projeto."class/util/classGeralLista.inc.php";

// valores padroes para o inicio e limite da listagem, ordenacao e direcao da ordenacao
$inicio		= isset($_POST['inicio'])		? htmlentities($_POST['inicio'])		: 0;
$limite		= isset($_POST['limite'])		? htmlentities($_POST['limite'])		: 20;
$sort		= isset($_POST['sort'])			? htmlentities($_POST['sort'])			: 'cpf';
$dir		= isset($_POST['dir'])			? htmlentities($_POST['dir'])			: '';
?>
<script type="text/javascript" src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery.mask.min.js"></script>
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui.min.js"></script>
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/tabelaLista.inc.js"></script>
<script type="text/javascript">
$("#cpf").mask("999.999.999-99");
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<div id="msg" name="msg" class="lstDado">
</div>
<table width="99%" border="0" align="center">
    <tr>
        <td valign="top">
            <table width="100%" border="0" align="center">
                <tr>
                    <td><a href="index.php?acao=novo" class="btn btn-info btn-sm">Novo</a></td>
                </tr>
                <tr>
                    <td valign="top">
                        <form id="form1" name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                            <table class="table top10">
                                <tr>
                                    <td colspan="4" bgcolor="#DDDDDD">Filtro de Pesquisa</td>
                                </tr>
                                <tr>
                                    <td align="right">CPF: </td>
                                    <td><input name="cpf" type="text" id="cpf" size="20" maxlength="14" value="<?php echo $cpf;?>" placeholder="CPF"/></td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td colspan="6" align="center"><input name="btn_pesquisar" type="submit" id="btn_pesquisar" class="btn btn-info btn-sm" value="Pesquisar" /></td>
                                </tr>
                            </table>
                        </form>
                    </td>
                </tr>
                <tr align="center">
                <td>
<p align="center">
<?php
$permissoes = array(
                'EXCLUIR' => 'true'
);

$botoes = array(
            array(
                'acao'     => 'index.php?acao=excluir&cpf={cpf}',
                'coluna'   => '0',
                'imagem'   => '../../../images/excluir.gif',
                'width'    => '16px',
                'height'   => '16px',
                'alt'      => 'Excluir',
                'condicao' => 'if({EXCLUIR}) { return true; } else { return false; }'
            )
);

$objBotoes = new acoesLista(1, $botoes, $permissoes);

$paginacao = array(
				'primeiro' 	=> '../../../images/resultset_first.png',
				'anterior' 	=> '../../../images/resultset_previous.png',
				'proximo' 	=> '../../../images/resultset_next.png',
				'ultimo'	=> '../../../images/resultset_last.png'
); 

$pesquisa = array (
				'cpf'	=> $cpf

);

$sql = "SELECT
		to_char(cpf,'000\".\"000\".\"000\"-\"99') as cpf,
		shn_login,
		to_char(cbl_data,'YYYY-MM-DD HH24:MI') as cbl_data
	FROM cpf_black_list";
if (!empty($cpf))
	$sql_aux[] = "cpf = ". $cpf ;
if (isset($sql_aux) && is_array($sql_aux)) {
	$sql .= ' WHERE ' . implode(' AND ', $sql_aux);
}
//echo $sql."<br>";
// instancia classe para listagem
$lista = new tabelaLista($sql, $_SERVER['PHP_SELF'], isset($paginacao) ? $paginacao : null, $inicio, $limite, $sort, $dir, $objBotoes);

// campos a serem listados => headers
$camposTabela = array(
    'cpf'					=> 'CPF',
    'shn_login'					=> 'BackOffice',
    'cbl_data'					=> 'Data'
);

$lista->camposTabela = $camposTabela;

$lista->geraLista();

$lista->imprimeLista();
?>
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