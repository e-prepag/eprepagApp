<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."includes/security.php";

//inclue a classe para a listagem
include $raiz_do_projeto."class/util/classGeralLista.inc.php";

// valores padroes para o inicio e limite da listagem, ordenacao e direcao da ordenacao
$inicio		= isset($_POST['inicio'])		? htmlentities($_POST['inicio'])		: 0;
$limite		= isset($_POST['limite'])		? htmlentities($_POST['limite'])		: 100;
$sort		= isset($_POST['sort'])			? htmlentities($_POST['sort'])			: 'bds_texto';
$dir		= isset($_POST['dir'])			? htmlentities($_POST['dir'])			: '';
$bds_id	= isset($_REQUEST['bds_id'])	? htmlentities($_REQUEST['bds_id'])	: '';
//echo "quest ID: [".$bds_id."]<br>";
$bds_nome = isset($_REQUEST['bds_nome'])? htmlentities($_REQUEST['bds_nome']) : '';
//echo "quest NOME: [".$bds_nome."]<br>";
?>
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
    <a href="index_banner.php?acao=novo" class="btn btn-sm btn-info">Novo</a>
</div>
<table class="table txt-preto fontsize-pp top10">
    <tr>
        <td valign="top">
            <table width="100%" border="0" align="center">
                <tr>
                    <td valign="top">
                        <form id="form1" name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                            <table class="table txt-preto fontsize-pp">
                                <tr>
                                    <td colspan="4" bgcolor="#DDDDDD">&nbsp;&nbsp;Filtro de Pesquisa</td>
                                </tr>
                                <tr>
                                    <td align="right"><nobr>C&oacute;digo do Banner Drop Shadow: </nobr></td>
                                    <td><input name="bds_id" type="text" id="bds_id" size="20" maxlength="20" value="<?php echo $bds_id;?>"/></td>
                                    <td><div align="right"><nobr>Nome do Banner Drop Shadow: </nobr></div></td>
                                    <td><input name="bds_nome" type="text" id="bds_nome" size="30" maxlength="50" value="<?php echo $bds_nome;?>"/></td>
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
                'acao'     => 'index_banner.php?acao=editar&bds_id={bds_id_banner}',
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
				'bds_id'	=> $bds_id,
				'bds_nome'=> $bds_nome

);

$sql = "SELECT
		bds_id_banner,
		bds_texto,
		SUBSTRING(bds_data_inicio::varchar,0, 11) as bds_data_inicio,
		SUBSTRING(bds_data_fim::varchar,0, 11) as bds_data_fim,
		CASE WHEN bds_tipo_usuario = 'L' THEN 'LAN HOUSE' WHEN bds_tipo_usuario = 'G' THEN 'Gamers' END as bds_tipo_usuario,
		CASE WHEN (bds_ativo = '1' AND bds_data_inicio <= NOW() AND (bds_data_fim + interval '1 day')   >= NOW()) THEN '<div style=\'background-color:orange;color:blue;text-align:center\'>Sim</div>' WHEN bds_ativo = '1' THEN 'Sim' ELSE 'N&atilde;o' END as bds_ativo
	FROM tb_banner_drop_shadow ";
if (!empty($bds_nome))
	$sbds_aux[] = "upper(bds_texto) LIKE '%" . strtoupper($bds_nome) . "%'";
if (!empty($bds_id))
	$sbds_aux[] = "bds_id_banner = ". $bds_id ;
if (isset($sbds_aux) && is_array($sbds_aux)) {
	$sql .= ' WHERE ' . implode(' AND ', $sbds_aux);
}
//echo "SQL: ".$sql."<br>";
// instancia classe para listagem
$lista = new tabelaLista($sql, $_SERVER['PHP_SELF'], isset($paginacao) ? $paginacao : null, $inicio, $limite, $sort, $dir, $objBotoes);

// campos a serem listados => headers
$camposTabela = array(
    'bds_id_banner'=> '&nbsp;ID&nbsp;',
    'bds_texto'			=> '&nbsp;Banner Drop Shadow&nbsp;',
    'bds_data_inicio'	=> 'In&iacute;cio da Vig&ecirc;ncia',
    'bds_data_fim'	=> 'Fim da Vig&ecirc;ncia',
    'bds_tipo_usuario'	=> 'Tipo de Usu&aacute;rios',
	'bds_ativo'		=> 'Ativo'
);
$lista->camposTabela = $camposTabela;

$lista->geraLista();

$strLista = $lista->getLista();
$f = array("linhaNormal(this)","linhaSelecionada(this,'#F8F8F8')","lista-table");
$t = array("","","table txt-preto fontsize-pp lista-table");
echo str_replace($f, $t,$strLista);
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
					<td>&nbsp;&nbsp;Banner Drop Shadow vig&ecirc;nte e ativo.</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<script src="/js/tabelaLista.inc.js"></script>
</body>
</html>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>