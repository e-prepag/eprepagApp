<?php
require_once '../../../includes/constantes.php';
include_once $raiz_do_projeto."includes/security.php";
// inclue a classe para a listagem
include $raiz_do_projeto."class/util/classGeralLista.inc.php";

// valores padroes para o inicio e limite da listagem, ordenacao e direcao da ordenacao
$inicio		= isset($_POST['inicio'])		? htmlentities($_POST['inicio'])		: 0;
$limite		= isset($_POST['limite'])		? htmlentities($_POST['limite'])		: 100;
$sort		= isset($_POST['sort'])			? htmlentities($_POST['sort'])			: 'promo_nome';
$dir		= isset($_POST['dir'])			? htmlentities($_POST['dir'])			: '';
$promo_id	= isset($_REQUEST['promo_id'])	? htmlentities($_REQUEST['promo_id'])	: '';
//echo "PROMO ID: [".$promo_id."]<br>";
$promo_nome = isset($_REQUEST['promo_nome'])? htmlentities($_REQUEST['promo_nome']) : '';
//echo "PROMO NOME: [".$promo_nome."]<br>";
?>
<script type="text/javascript" src="/js/tabelaLista.inc.js"></script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<input type="hidden" name="alert" id="alert" value="Arquivo selecionado n&atilde;o &eacute; uma Imagem V&aacute;lida." />
<div id="msg" name="msg" class="lstDado">
</div>
<table class="table">
    <tr>
        <td valign="top">
            <table class="table txt-preto table-bordered">
                <tr>
                    <td><a href="index_promocoes.php?acao=novo" class="btn btn-info btn-sm">Cadastro de Promoção</span></td>
                </tr>
                <tr>
                    <td valign="top">
                        <form id="form1" name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                            <table class="table txt-preto">
                                <tr>
                                    <td colspan="4" bgcolor="#DDDDDD">Filtro de Pesquisa</td>
                                </tr>
                                <tr>
                                    <td align="right">Código da Promoção: </td>
                                    <td><input name="promo_id" type="text" id="promo_id" size="20" maxlength="20" value="<?php echo $promo_id;?>"/></td>
                                    <td><div align="right">Nome da Promoção: </div></td>
                                    <td><input name="promo_nome" type="text" id="promo_nome" size="30" maxlength="50" value="<?php echo $promo_nome;?>"/></td>
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
$permissoes = array(
                'EDITAR' => 'true'
);

$botoes = array(
            array(
                'acao'     => 'index_promocoes.php?acao=editar&promo_id={promo_id}',
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
				'promo_id'	=> $promo_id,
				'promo_nome'=> $promo_nome

);

$sql = "SELECT
		promo_id,
		promo_nome,
		opr_nome,
		to_char(promo_data_inicio,'DD/MM/YYYY') as promo_data_inicio,
		to_char(promo_data_fim,'DD/MM/YYYY') as promo_data_fim,
		promo_banner,
		promo_valor,
		CASE WHEN (promo_ativo = '1' AND promo_data_inicio <= NOW() AND (promo_data_fim + interval '1 day')   >= NOW()) THEN '<div style=\'background-color:orange;color:blue;text-align:center\'>Sim</div>' WHEN promo_ativo = '1' THEN 'Sim' ELSE 'N&atilde;o' END as promo_ativo
	FROM promocoes p
		LEFT JOIN operadoras o ON (p.opr_codigo = o.opr_codigo)";
if (!empty($promo_nome))
	$sql_aux[] = "upper(promo_nome) LIKE '%" . strtoupper($promo_nome) . "%'";
if (!empty($promo_id))
	$sql_aux[] = "promo_id = ". $promo_id ;
if (isset($sql_aux) && is_array($sql_aux)) {
	$sql .= ' WHERE ' . implode(' AND ', $sql_aux);
}
//echo "SQL: ".$sql."<br>";
// instancia classe para listagem
$lista = new tabelaLista($sql, $_SERVER['PHP_SELF'], isset($paginacao) ? $paginacao : null, $inicio, $limite, $sort, $dir, $objBotoes);

// campos a serem listados => headers
$camposTabela = array(
    'promo_id'			=> '&nbsp;ID&nbsp;',
    'promo_nome'		=> '&nbsp;Promo&ccedil;&atilde;o&nbsp;',
    'opr_nome'			=> '&nbsp;Publisher',
    'promo_data_inicio'	=> 'In&iacute;cio da Vig&ecirc;ncia',
    'promo_data_fim'	=> 'Fim da Vig&ecirc;ncia',
    'promo_banner'		=> 'Arquivo do Banner',
    'promo_valor'		=> '<nobr>Valor da</nobr> Regra',
	'promo_ativo'		=> 'Ativo'
);
$lista->camposTabela = $camposTabela;

$lista->geraLista();

$strLista = $lista->getLista();
echo str_replace("lista-table","table txt-preto fontsize-pp lista-table",$strLista);
?>
</p>
               </td>
                </tr>
            </table>
        </td>
    </tr>
	<tr>
		<td>
			<table width="60%" border="0" align="center" style="font-family:verdana, arial;font-size:10px;">
				<tr>
					<td bgcolor="orange" width="16px">
					</td>
					<td>&nbsp;&nbsp;Promo&ccedil;&atilde;o vig&ecirc;nte e ativa.</td>
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