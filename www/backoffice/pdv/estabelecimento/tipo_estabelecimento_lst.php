<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."includes/security.php";
// inclue a classe para a listagem
include $raiz_do_projeto."class/util/classGeralLista.inc.php";
// valores padroes para o inicio e limite da listagem, ordenacao e direcao da ordenacao
$inicio		= isset($_POST['inicio'])		? htmlentities($_POST['inicio'])		: 0;
$limite		= isset($_POST['limite'])		? htmlentities($_POST['limite'])		: 30;
$sort		= isset($_POST['sort'])			? htmlentities($_POST['sort'])			: 'te_descricao';
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

<input type="hidden" name="alert" id="alert" value="Arquivo selecionado n&atilde;o &eacute; uma Imagem V&aacute;lida." />
<div id="msg" name="msg" class="lstDado">
</div>
<table class="table">
    <tr>
        <td valign="top">
            <table class="table">
                <tr>
                    <td valign="top">
                        <form id="form1" name="form1" method="post" action="">
                            <table id="toolbar" >
								<tr height="60" valign="middle" align="center">
									<td>
                                        <a href="index_tipo_estabelecimento.php?acao=novo" class="btn btn-sm btn-info">Novo</a>
									</td>
								</tr>
							</table>
                            <table class="table">
                                <tr>
                                    <td colspan="4" bgcolor="#DDDDDD">&nbsp;&nbsp;Filtro de Pesquisa</td>
                                </tr>
                                <tr>
                                    <td align="right">C&oacute;digo da Tipo de Estabelecimento: </td>
                                    <td><input name="te_id" type="text" id="te_id" size="20" maxlength="20" value="<?php if(isset($te_id)) echo $te_id;?>"/></td>
                                    <td><div align="right">Nome da Tipo de Estabelecimento: </div></td>
                                    <td><input name="te_descricao" type="text" id="te_descricao" size="30" maxlength="50" value="<?php if(isset($te_descricao)) echo $te_descricao;?>"/></td>
                                </tr>
                                <tr>
                                    <td colspan="6" align="center"><input name="btn_pesquisar" type="submit" class="btn btn-info btn-sm" id="btn_pesquisar" value="Pesquisar" /></td>
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
                'acao'     => 'index_tipo_estabelecimento.php?acao=editar&te_id={te_id}',
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
if(!isset($te_id))
    $te_id = null;

if(!isset($te_descricao))
    $te_descricao = null;
$pesquisa = array (
				'te_id'	=> $te_id,
				'te_descricao'=> $te_descricao

);

$sql = "SELECT
		te_id,
		te_descricao,
		CASE WHEN te_ativo = '1' THEN '<div style=\'background-color:orange;color:blue;text-align:center\'>Sim</div>' ELSE 'N&atilde;o' END as te_ativo
	FROM tb_tipo_estabelecimento te ";
if (!empty($te_descricao))
	$sql_aux[] = "UPPER(te_descricao) LIKE '%" . strtoupper($te_descricao) . "%'";
if (!empty($te_id))
	$sql_aux[] = "te_id = ". $te_id ;
if (isset($sql_aux) && is_array($sql_aux)) {
	$sql .= ' WHERE ' . implode(' AND ', $sql_aux);
}
//echo $sql."<br>";
// instancia classe para listagem
$lista = new tabelaLista($sql, $_SERVER['PHP_SELF'], isset($paginacao) ? $paginacao : null, $inicio, $limite, $sort, $dir, $objBotoes);

// campos a serem listados => headers
$camposTabela = array(
    'te_id'				=> '&nbsp;ID&nbsp;',
    'te_descricao'		=> '&nbsp;Tipo de Estabelecimento&nbsp;',
    'te_ativo'			=> '&nbsp;Ativo'
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
					<td>&nbsp;&nbsp;Tipo de Estabelecimento Ativo.</td>
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