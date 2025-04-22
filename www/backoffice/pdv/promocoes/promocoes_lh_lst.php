<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."includes/security.php";
// inclue a classe para a listagem
require_once $raiz_do_projeto."class/util/classGeralLista.inc.php";
// valores padroes para o inicio e limite da listagem, ordenacao e direcao da ordenacao
$inicio		= isset($_POST['inicio'])		? htmlentities($_POST['inicio'])		: 0;
$limite		= isset($_POST['limite'])		? htmlentities($_POST['limite'])		: 10;
$sort		= isset($_POST['sort'])			? htmlentities($_POST['sort'])			: 'promolh_descricao';
$dir		= isset($_POST['dir'])			? htmlentities($_POST['dir'])			: '';
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
<table width="99%" border="0" align="center">
    <tr>
        <td valign="top">
            <table width="100%" border="0" align="center">
                <tr>
                    <td valign="top">
                        <form id="form1" name="form1" method="post" action="">
                            <a href="index_promocoes_lh.php?acao=novo" class="btn-info btn btn-sm">Novo</a>
                            <table class="table top10">
                                <tr>
                                    <td colspan="4" bgcolor="#DDDDDD">&nbsp;&nbsp;Filtro de Pesquisa</td>
                                </tr>
                                <tr>
                                    <td align="right">Código da Promoção: </td>
                                    <td><input name="promolh_id" type="text" id="promolh_id" size="20" maxlength="20" value="<?php echo $promolh_id;?>"/></td>
                                    <td><div align="right">Nome da Promoção: </div></td>
                                    <td><input name="promolh_descricao" type="text" id="promolh_descricao" size="30" maxlength="50" value="<?php echo $promolh_descricao;?>"/></td>
                                </tr>
                                <tr>
                                    <td colspan="6" align="center"><input name="btn_pesquisar" type="submit" id="btn_pesquisar" class="btn btn-sm btn-info" value="Pesquisar" /></td>
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
                'acao'     => 'index_promocoes_lh.php?acao=editar&promolh_id={promolh_id}',
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
				'promolh_id'	=> $promolh_id,
				'promolh_descricao'=> $promolh_descricao

);

$sql = "SELECT
		promolh_id,
		promolh_descricao,
		to_char(promolh_data_inicio,'DD/MM/YYYY') as promolh_data_inicio,
		to_char(promolh_data_fim,'DD/MM/YYYY') as promolh_data_fim,
		opr_nome
	FROM promocoes_lanhouses p
		LEFT JOIN operadoras o ON (p.opr_codigo = o.opr_codigo)";
if (!empty($promolh_descricao))
	$sql_aux[] = "UPPER(promolh_descricao) LIKE '%" . strtoupper($promolh_descricao) . "%'";
if (!empty($promolh_id))
	$sql_aux[] = "promolh_id = ". $promolh_id ;
if (isset($sql_aux) && is_array($sql_aux)) {
	$sql .= ' WHERE ' . implode(' AND ', $sql_aux);
}
//echo $sql."<br>";
// instancia classe para listagem
$lista = new tabelaLista($sql, $_SERVER['PHP_SELF'], isset($paginacao) ? $paginacao : null, $inicio, $limite, $sort, $dir, $objBotoes);

// campos a serem listados => headers
$camposTabela = array(
    'promolh_id'			=> '&nbsp;ID&nbsp;',
    'promolh_descricao'		=> '&nbsp;Promo&ccedil;&atilde;o&nbsp;',
    'opr_nome'				=> '&nbsp;Publisher',
    'promolh_data_inicio'	=> 'In&iacute;cio da Vig&ecirc;ncia',
    'promolh_data_fim'		=> 'Fim da Vig&ecirc;ncia'
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