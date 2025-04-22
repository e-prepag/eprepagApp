<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."includes/security.php";
// inclue a classe para a listagem
require_once $raiz_do_projeto."class/util/classGeralLista.inc.php";
// valores padroes para o inicio e limite da listagem, ordenacao e direcao da ordenacao
$inicio		= isset($_POST['inicio'])		? htmlentities($_POST['inicio'])		: 0;
$limite		= isset($_POST['limite'])		? htmlentities($_POST['limite'])		: 10;
$sort		= isset($_POST['sort'])			? htmlentities($_POST['sort'])			: 'opr_nome';
$dir		= isset($_POST['dir'])			? htmlentities($_POST['dir'])			: '';
$opr_codigo	= isset($_REQUEST['opr_codigo'])? htmlentities($_REQUEST['opr_codigo']) : '';
$opr_nome   = isset($_REQUEST['opr_nome'])  ? htmlentities($_REQUEST['opr_nome'])   : '';
?>
<script type="text/javascript" src="/js/tabelaLista.inc.js"></script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<input type="hidden" name="alert" id="alert" value="Arquivo selecionado n&atilde;o &eacute; um TXT." />
<table class="table txt-preto fontsize-pp">
    <tr>
        <td valign="top">
            <table class="txt-preto table fontsize-pp">
                <tr>
                    <td valign="top">
                        <form id="form1" name="form1" method="post" action="">
                        <input type="hidden" name="opr_codigo" value="<?php //echo $opr_codigo?>" />
                        <table class=" table">
								<tr height="60" valign="middle" align="center">
									<td>
                                        <a href="index.php?acao=novo" class="btn btn-sm btn-info">Novo</a>
									</td>
								</tr>
							</table>
							<table class="table txt-preto fontsize-pp">
                                <tr>
                                    <td colspan="4" bgcolor="#DDDDDD">&nbsp;&nbsp;Filtro de Pesquisa</td>
                                </tr>
                                <tr>
                                    <td align="right">C&oacute;digo da Operadora: </td>
                                    <td><input name="opr_codigo" type="text" id="opr_codigo" size="20" maxlength="20" /></td>
                                    <td><div align="right">Nome da Operadora: </div></td>
                                    <td><input name="opr_nome" type="text" id="opr_nome" size="30" maxlength="50" /></td>
                                </tr>
                                <tr>
                                    <td colspan="6" align="center"><input name="btn_pesquisar" type="submit" class="btn btn-sm btn-info" id="btn_pesquisar" value="Pesquisar" /></td>
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
                'EDITAR' => 'true'
);

$botoes = array(
            array(
                'acao'     => 'index.php?acao=editar&opr_codigo={opr_codigo}',
                'coluna'   => '0',
                'imagem'   => '/images/pencil.png',
                'width'    => '16px',
                'height'   => '16px',
                'alt'      => 'Editar',
                'condicao' => 'if({EDITAR}) { return true; } else { return false; }'
            )
);

$objBotoes = new acoesLista(1, $botoes, $permissoes);

$paginacao = array(
				'primeiro' 	=> '/images/resultset_first.png',
				'anterior' 	=> '/images/resultset_previous.png',
				'proximo' 	=> '/images/resultset_next.png',
				'ultimo'	=> '/images/resultset_last.png'
); 

$pesquisa = array (
				'opr_codigo' => $opr_codigo,
				'opr_nome'   => $opr_nome

);

$sql = "SELECT
		opr_codigo,
		opr_nome,
		(case ";
foreach($PRODUCT_TYPE as $key => $val) {
	$sql .= "when (opr_product_type=".$key.") then ('<nobr>".substr($val, 0, 34)."</nobr>') ";
}
$sql .=" end) as opr_product_type,
		(case ";
foreach($USE_CHECK as $key => $val) {
	$sql .= "when (opr_use_check=".$key.") then ('".$val."') ";
}		
$sql .=" end ) as opr_use_check,
		opr_partner_check,
		replace(opr_ip, ';', '</nobr><br><nobr>') as opr_ip,
		opr_partner_email
	FROM operadoras"; 
$sql_aux[] = "opr_product_type!=0 ";
if (!empty($opr_nome))
	$sql_aux[] = "UPPER(opr_nome) LIKE '%" .strtoupper ($opr_nome) . "%' ";
if (!empty($opr_codigo))
	$sql_aux[] = "opr_codigo = ". $opr_codigo . " ";
if (is_array($sql_aux)) {
	$sql .= ' WHERE ' . implode(' AND ', $sql_aux) . " ";
}
//echo $sql."<br>";
// instancia classe para listagem
$lista = new tabelaLista($sql, $_SERVER['PHP_SELF'], isset($paginacao) ? $paginacao : null, $inicio, $limite, $sort, $dir, $objBotoes);

// campos a serem listados => headers
$camposTabela = array(
    'opr_codigo'			=> '&nbsp;ID&nbsp;',
	'opr_nome'				=> '&nbsp;Nome&nbsp;',
	'opr_product_type'		=> '&nbsp;Produto&nbsp;',
	'opr_use_check'			=> '&nbsp;Checagem&nbsp;',
	'opr_partner_check'		=> ' URL ',
	'opr_ip'				=> ' IPs dos Servidores ',
	'opr_partner_email'		=> ' Email Homologa&ccedil;&atilde;o '
);
$lista->camposTabela = $camposTabela;

$lista->geraLista();

$strLista = $lista->getLista();
echo str_replace("lista-table","table txt-preto fontsize-pp text-center lista-table",$strLista);
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