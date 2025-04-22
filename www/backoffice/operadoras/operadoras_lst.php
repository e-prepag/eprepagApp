<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."includes/security.php";
// inclue a classe para a listagem
require_once $raiz_do_projeto."class/util/classGeralLista.inc.php";
// valores padroes para o inicio e limite da listagem, ordenacao e direcao da ordenacao
$inicio		= isset($_POST['inicio'])		? htmlentities($_POST['inicio'])		: 0;
$limite		= isset($_POST['limite'])		? htmlentities($_POST['limite'])		: 100;
$sort		= isset($_POST['sort'])			? htmlentities($_POST['sort'])			: 'opr_codigo';
$dir		= isset($_POST['dir'])			? htmlentities($_POST['dir'])			: 'DESC';
$opr_codigo	= isset($_REQUEST['opr_codigo'])? htmlentities($_REQUEST['opr_codigo']) : '';
$opr_nome   = isset($_REQUEST['opr_nome'])  ? htmlentities($_REQUEST['opr_nome'])   : '';
?>
<script type="text/javascript" src="/js/tabelaLista.inc.js"></script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><?php echo $sistema->item->getDescricao() ; ?></li>
    </ol>
</div>
<input type="hidden" name="alert" id="alert" value="Arquivo selecionado n&atilde;o &eacute; um TXT." />
<div class="col-md-12">
    <a href="index.php?acao=novo" class="btn btn-sm btn-info pull-right">
        Novo
    </a>
</div>
</div></div>
<table class="table bg-branco txt-preto fontsize-pp">
    <tr>
        <td valign="top">
            <table width="100%" border="0" align="center">
                <tr>
                    <td valign="top">
                        <form id="form1" name="form1" method="post">
                        <input type="hidden" name="opr_codigo" value="<?php //echo $opr_codigo?>" />
						<table class="table txt-preto fontsize-pp">
                            <tr>
                                <td colspan="4" bgcolor="#DDDDDD">Filtro de Pesquisa</td>
                            </tr>
                            <tr>
                                <td align="right">C&oacute;digo da Operadora: </td>
                                <td><input name="opr_codigo" type="text" id="opr_codigo" size="20" maxlength="20" /></td>
                                <td><div align="right">Nome da Operadora: </div></td>
                                <td><input name="opr_nome" type="text" id="opr_nome" size="30" maxlength="50" /></td>
                            </tr>
                            <tr>
                                <td colspan="4" align="center"><input name="btn_pesquisar" class="btn btn-sm btn-info" type="submit" id="btn_pesquisar" value="Pesquisar" /></td>
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
		'<nobr>'||opr_nome||'</nobr>' as opr_nome,
		'<nobr>'||SUBSTR(opr_contato,1,25)||'</nobr>' as opr_contato,
		opr_site,
		opr_cont_fone,
		opr_cont_mail,
		opr_pedido_estoque_prazo,
		opr_comissao_por_volume, 
		(100*obtem_comissao(opr_codigo, 'M', null, 0)) as comiss_m, 
		(100*obtem_comissao(opr_codigo, 'E', null, 0)) as comiss_e, 
		(100*obtem_comissao(opr_codigo, 'L', null, 0)) as comiss_l, 
		(100*obtem_comissao(opr_codigo, 'C', null, 0)) as comiss_c, 
		(100*obtem_comissao(opr_codigo, 'P', null, 0)) as comiss_p    
	FROM operadoras";
if (!empty($opr_nome))
	$sql_aux[] = "UPPER(opr_nome) LIKE '%" .strtoupper ($opr_nome) . "%' ";
if (!empty($opr_codigo))
	$sql_aux[] = "opr_codigo = ". $opr_codigo ;
if (isset($sql_aux) && is_array($sql_aux)) {
	$sql .= ' WHERE ' . implode(' AND ', $sql_aux);
}
//echo "$sql<br>";
// instancia classe para listagem
$lista = new tabelaLista($sql, $_SERVER['PHP_SELF'], isset($paginacao) ? $paginacao : null, $inicio, $limite, $sort, $dir, $objBotoes);

// campos a serem listados => headers
$camposTabela = array(
    'opr_codigo'				=> 'ID',
    'opr_nome'					=> 'Publisher',
    'opr_contato'				=> 'Contato',
//    'opr_site'					=> 'Site',
    'opr_cont_fone'				=> 'Telefone',
//    'opr_cont_mail'				=> 'Email',
    'opr_pedido_estoque_prazo'	=> 'Prazo Estoque',

	'opr_comissao_por_volume'	=> 'V',

	'comiss_m'					=> 'M',
	'comiss_e'					=> 'E',
	'comiss_l'					=> 'L',
	'comiss_c'					=> 'C',
	'comiss_p'					=> 'P',
);
$lista->camposTabela = $camposTabela;

$lista->geraLista();

$strLista = $lista->getLista();
echo str_replace('width="16px" height="16px" hspace="5" vspace="5"',"",str_replace("lista-table","table txt-preto fontsize-pp lista-table",$strLista));

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