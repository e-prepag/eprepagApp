<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."includes/security.php";
// inclue a classe para a listagem
require_once $raiz_do_projeto."class/util/classGeralLista.inc.php";
// valores padroes para o inicio e limite da listagem, ordenacao e direcao da ordenacao
$inicio		= isset($_POST['inicio'])		? htmlentities($_POST['inicio'])		: 0;
$limite		= isset($_POST['limite'])		? htmlentities($_POST['limite'])		: 10;
$sort		= isset($_POST['sort'])			? htmlentities($_POST['sort'])			: 'dv_descricao';
$dir		= isset($_POST['dir'])			? htmlentities($_POST['dir'])			: '';
?>
<script type="text/javascript" src="/js/tabelaLista.inc.js"></script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
		<li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
		<li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
		<li class="active"><?php echo $sistema->item->getDescricao(); ?></li>
	</ol>
</div>
<div class="lstDado"></div>
<input type="hidden" name="alert" id="alert" value="Arquivo selecionado n&atilde;o &eacute; uma Imagem V&aacute;lida." />
<div id="msg" name="msg" class="lstDado">
</div>
<div class="col-md-12">
    <a href="index_videos.php?acao=novo" class="btn btn-sm btn-info">Novo</a>
</div>
<div class="col-md-12 txt-preto top20">
    <form id="form1" name="form1" method="post">
        <h5><strong>Filtro de Pesquisa</strong></h5>
        <div class="col-md-4">
            <div class="form-group">
                <label for="te_descricao_update">Código:</label>
                <input name="dv_id" type="text" id="dv_id" size="20" maxlength="20" value="<?php if(isset($dv_id)) echo $dv_id;?>"/>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="te_descricao_update">Nome:</label>
                <input name="dv_descricao" type="text" id="dv_descricao" size="30" maxlength="50" value="<?php if(isset($dv_descricao)) echo $dv_descricao;?>"/>
            </div>
        </div>
        <input class="btn btn-info btn-sm" name="btn_pesquisar" type="submit" id="btn_pesquisar" value="Pesquisar" />
    </form>
</div>
<table class="table">
    <tr>
        <td valign="top">
            <table>
                <tr>
                <td>
<p align="center">
<?php
$permissoes = array(
                'EDITAR' => 'true'
);

$botoes = array(
            array(
                'acao'     => 'index_videos.php?acao=editar&dv_id={dv_id}',
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

if(!isset($dv_descricao))
    $dv_descricao = null;

if(!isset($dv_id))
    $dv_id = null;

$pesquisa = array (
				'dv_id'	=> $dv_id,
				'dv_descricao'=> $dv_descricao

);

$sql = "SELECT
		dv_id,
		dv_descricao,
		to_char(dv_data_inicio,'DD/MM/YYYY') as dv_data_inicio,
		to_char(dv_data_fim,'DD/MM/YYYY') as dv_data_fim,
		CASE WHEN (dv_ativo = '1' AND dv_data_inicio <= NOW() AND (dv_data_fim + interval '1 day')   >= NOW()) THEN '<div style=\'background-color:orange;color:blue;text-align:center\'>Sim</div>' WHEN dv_ativo = '1' THEN 'Sim' ELSE 'N&atilde;o' END as dv_ativo
	FROM dist_videos te ";
if (!empty($dv_descricao))
	$sql_aux[] = "UPPER(dv_descricao) LIKE '%" . strtoupper($dv_descricao) . "%'";
if (!empty($dv_id))
	$sql_aux[] = "dv_id = ". $dv_id ;
if (!empty($sql_aux)) {
	$sql .= ' WHERE ' . implode(' AND ', $sql_aux);
}
//echo $sql."<br>";
// instancia classe para listagem
$lista = new tabelaLista($sql, $_SERVER['PHP_SELF'], isset($paginacao) ? $paginacao : null, $inicio, $limite, $sort, $dir, $objBotoes);

// campos a serem listados => headers
$camposTabela = array(
    'dv_id'				=> '&nbsp;ID&nbsp;',
    'dv_descricao'		=> '&nbsp;V&iacute;deos&nbsp;',
    'dv_data_inicio'	=> '&nbsp;In&iacute;cio',
    'dv_data_fim'		=> '&nbsp;Final',
    'dv_ativo'			=> '&nbsp;Ativo'
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
	<tr>
		<td>
			<table width="60%" border="0" align="center" style="font-family:verdana, arial;font-size:10px;">
				<tr>
					<td bgcolor="orange" width="16px">
					</td>
					<td>Vídeos Ativos.</td>
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