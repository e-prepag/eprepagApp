<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
// inclue a classe para a listagem
require_once $raiz_do_projeto."/class/util/classGeralLista.inc.php";
// valores padroes para o inicio e limite da listagem, ordenacao e direcao da ordenacao
$inicio		= isset($_POST['inicio'])		? htmlentities($_POST['inicio'])		: 0;
$limite		= isset($_POST['limite'])		? htmlentities($_POST['limite'])		: 100;
$sort		= isset($_POST['sort'])			? htmlentities($_POST['sort'])			: 'ql_texto';
$dir		= isset($_POST['dir'])			? htmlentities($_POST['dir'])			: '';
$quest_id	= isset($_REQUEST['quest_id'])	? htmlentities($_REQUEST['quest_id'])	: '';
//echo "quest ID: [".$quest_id."]<br>";
$quest_nome = isset($_REQUEST['quest_nome'])? htmlentities($_REQUEST['quest_nome']) : '';
//echo "quest NOME: [".$quest_nome."]<br>";
?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<div class="col-md-12">
    <a href="index_questionario.php?acao=novo" class="btn btn-sm btn-info">Novo</a>
</div>
<input type="hidden" name="alert" id="alert" value="Arquivo selecionado n&atilde;o &eacute; uma Imagem V&aacute;lida." />
<div id="msg" name="msg" class="lstDado">
</div>
<table class="table txt-preto fontsize-pp">
    <tr>
        <td valign="top">
            <table class="table txt-preto fontsize-pp">
                <tr>
                    <td valign="top">
                        <form id="form1" name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                            <table class="table txt-preto fontsize-pp">
                                <tr>
                                    <td colspan="4" bgcolor="#DDDDDD">&nbsp;&nbsp;Filtro de Pesquisa</td>
                                </tr>
                                <tr>
                                    <td align="right">C&oacute;digo do Question&aacute;rio: </td>
                                    <td><input name="quest_id" type="text" id="quest_id" size="20" maxlength="20" value="<?php echo $quest_id;?>"/></td>
                                    <td><div align="right">Nome do Question&aacute;rio: </div></td>
                                    <td><input name="quest_nome" type="text" id="quest_nome" size="30" maxlength="50" value="<?php echo $quest_nome;?>"/></td>
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
                'acao'     => 'index_questionario.php?acao=editar&quest_id={ql_id_questionario}',
                'coluna'   => '0',
                'imagem'   => '../../images/pencil.png',
                'width'    => '16px',
                'height'   => '16px',
                'alt'      => 'Editar',
                'condicao' => 'if({EDITAR}) { return true; } else { return false; }'
            )
);

$objBotoes = new acoesLista(1, $botoes, $permissoes);

$paginacao = array(
				'primeiro' 	=> '../../images/resultset_first.png',
				'anterior' 	=> '../../images/resultset_previous.png',
				'proximo' 	=> '../../images/resultset_next.png',
				'ultimo'	=> '../../images/resultset_last.png'
); 

$pesquisa = array (
				'quest_id'	=> $quest_id,
				'quest_nome'=> $quest_nome

);

$sql = "SELECT
		ql_id_questionario,
		ql_texto,
		to_char(ql_data_inicio,'DD/MM/YYYY') as quest_data_inicio,
		to_char(ql_data_fim,'DD/MM/YYYY') as quest_data_fim,
		CASE WHEN ql_tipo_usuario = 'L' THEN 'LAN HOUSE' WHEN ql_tipo_usuario = 'G' THEN 'Gamers' END as ql_tipo_usuario,
		CASE WHEN (ql_ativo = '1' AND ql_data_inicio <= NOW() AND (ql_data_fim + interval '1 day')   >= NOW()) THEN '<div style=\'background-color:orange;color:blue;text-align:center\'>Sim</div>' WHEN ql_ativo = '1' THEN 'Sim' ELSE 'N&atilde;o' END as quest_ativo
	FROM tb_questionarios ";
if (!empty($quest_nome))
	$sql_aux[] = "upper(ql_texto) LIKE '%" . strtoupper($quest_nome) . "%'";
if (!empty($quest_id))
	$sql_aux[] = "ql_id_questionario = ". $quest_id ;
if (isset($sql_aux) && is_array($sql_aux)) {
	$sql .= ' WHERE ' . implode(' AND ', $sql_aux);
}
//echo "SQL: ".$sql."<br>";
// instancia classe para listagem
$lista = new tabelaLista($sql, $_SERVER['PHP_SELF'], isset($paginacao) ? $paginacao : null, $inicio, $limite, $sort, $dir, $objBotoes);

// campos a serem listados => headers
$camposTabela = array(
    'ql_id_questionario'=> '&nbsp;ID&nbsp;',
    'ql_texto'			=> '&nbsp;Question&aacute;rio&nbsp;',
    'quest_data_inicio'	=> 'In&iacute;cio da Vig&ecirc;ncia',
    'quest_data_fim'	=> 'Fim da Vig&ecirc;ncia',
    'ql_tipo_usuario'	=> 'Tipo de Usu&aacute;rios',
	'quest_ativo'		=> 'Ativo'
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
					<td>&nbsp;&nbsp;question&aacute;rio vig&ecirc;nte e ativo.</td>
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