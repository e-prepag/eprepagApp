<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."includes/security.php";
// inclue a classe para a listagem
require_once $raiz_do_projeto."class/util/classGeralLista.inc.php";
// valores padroes para o inicio e limite da listagem, ordenacao e direcao da ordenacao
$inicio		= isset($_POST['inicio'])		? htmlentities($_POST['inicio'])		: 0;
$limite		= isset($_POST['limite'])		? htmlentities($_POST['limite'])		: 100;
$sort		= isset($_POST['sort'])			? htmlentities($_POST['sort'])			: 'mp_descricao';
$dir		= isset($_POST['dir'])			? htmlentities($_POST['dir'])			: '';
$mat_promo_id	= isset($_REQUEST['mat_promo_id'])	? htmlentities($_REQUEST['mat_promo_id'])	: '';
//echo "quest ID: [".$mat_promo_id."]<br>";
$mat_promo_nome = isset($_REQUEST['mat_promo_nome'])? htmlentities($_REQUEST['mat_promo_nome']) : '';
//echo "quest NOME: [".$mat_promo_nome."]<br>";
?><script type="text/javascript" src="/js/tabelaLista.inc.js"></script>
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
<div class="col-md-12">
    <a href="index.php?acao=novo" class="btn btn-info btn-sm pull-right">Novo</a>
</div>
<div class="col-md-12">

    <table class="table">
        <tr>
            <td valign="top">
                <form id="form1" name="form1" method="post">
                    <table class="table">
                        <tr>
                            <td colspan="4" bgcolor="#DDDDDD">&nbsp;&nbsp;Filtro de Pesquisa</td>
                        </tr>
                        <tr>
                            <td align="right"><nobr>C&oacute;digo do Material Promocional: </nobr></td>
                            <td><input name="mat_promo_id" type="text" id="mat_promo_id" size="20" maxlength="20" value="<?php echo $mat_promo_id;?>"/></td>
                            <td><div align="right"><nobr>Nome do Material Promocional: </nobr></div></td>
                            <td><input name="mat_promo_nome" type="text" id="mat_promo_nome" size="30" maxlength="50" value="<?php echo $mat_promo_nome;?>"/></td>
                        </tr>
                        <tr>
                            <td colspan="6" align="center"><input name="btn_pesquisar" class="btn btn-sm btn-info" type="submit" id="btn_pesquisar" value="Pesquisar" /></td>
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
                'acao'     => 'index.php?acao=editar&mat_promo_id={mp_id}',
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
				'mat_promo_id'	=> $mat_promo_id,
				'mat_promo_nome'=> $mat_promo_nome
);

$smp_aux = array();

$sql = "SELECT
		mp_id,
		mp_descricao,
		to_char(mp_data_inclusao,'DD/MM/YYYY') as mat_promo_data_inclusao,
		to_char(mp_data_alteracao,'DD/MM/YYYY') as mat_promo_data_alteracao,
		CASE WHEN (mp_ativo = '1') THEN '<div style=\'background-color:orange;color:blue;text-align:center\'>Sim</div>' ELSE 'N&atilde;o' END as mat_promo_ativo
	FROM dist_materiais_promocionais ";
if (!empty($mat_promo_nome))
	$smp_aux[] = "upper(mp_descricao) LIKE '%" . strtoupper($mat_promo_nome) . "%'";
if (!empty($mat_promo_id))
	$smp_aux[] = "mp_id = ". $mat_promo_id ;
if (count($smp_aux)>0) {
	$sql .= ' WHERE ' . implode(' AND ', $smp_aux);
}
//echo "SQL: ".$sql."<br>";
// instancia classe para listagem
$lista = new tabelaLista($sql, $_SERVER['PHP_SELF'], isset($paginacao) ? $paginacao : null, $inicio, $limite, $sort, $dir, $objBotoes);

// campos a serem listados => headers
$camposTabela = array(
    'mp_id'                     => '&nbsp;ID&nbsp;',
    'mp_descricao'		=> '&nbsp;Material Promocional&nbsp;',
    'mat_promo_data_inclusao'	=> 'Data Inclus&atilde;o',
    'mat_promo_data_alteracao'	=> 'Data Altera&ccedil;&atilde;o',
    'mat_promo_ativo'		=> 'Ativo'
);
$lista->camposTabela = $camposTabela;

$lista->geraLista();

$lista->imprimeLista();
?>
                </p>
            </td>
        </tr>
    </table>
    <table width="60%" border="0" align="center" style="font-family:verdana, arial;font-size:10px;">
        <tr>
            <td bgcolor="orange" width="16px">
            </td>
            <td>&nbsp;&nbsp;Material Promocional Ativo.</td>
        </tr>
    </table>
</div>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>