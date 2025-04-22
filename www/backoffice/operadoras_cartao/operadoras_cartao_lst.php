<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."includes/security.php";
// inclue a classe para a listagem
require_once $raiz_do_projeto."class/util/classGeralLista.inc.php";
// valores padroes para o inicio e limite da listagem, ordenacao e direcao da ordenacao
$inicio		= isset($_POST['inicio'])		? htmlentities($_POST['inicio'])		: 0;
$limite		= isset($_POST['limite'])		? htmlentities($_POST['limite'])		: 100;
$sort		= isset($_POST['sort'])			? htmlentities($_POST['sort'])			: 'opr_nome';
$dir		= isset($_POST['dir'])			? htmlentities($_POST['dir'])			: 'ASC';
$opr_codigo	= isset($_REQUEST['opr_codigo'])	? htmlentities($_REQUEST['opr_codigo']) 	: '';
$opr_nome       = isset($_REQUEST['pcd_id_distribuidor'])  ? htmlentities($_REQUEST['pcd_id_distribuidor'])   : '';
if(!isset($pcd_id_distribuidor)){
    $pcd_id_distribuidor = null;
}
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
<div class="col-md-12">
    <a href="index.php?acao=novo" class="btn btn-info btn-sm pull-right">Novo</a>
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
                                    <td align="right">C&oacute;digo da Operadora: </td>
                                    <td><input name="opr_codigo" type="text" id="opr_codigo" size="20" maxlength="20" value="<?php echo $opr_codigo;?>" /></td>
                                    <td><div align="right">C&oacute;digo da Distribuidora: </div></td>
                                    <td><input name="pcd_id_distribuidor" type="text" id="pcd_id_distribuidor" size="20" maxlength="20" value="<?php echo $pcd_id_distribuidor;?>" /></td>
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
                'acao'     => 'index.php?acao=editar&opr_codigo={opr_codigo}&pcd_id_distribuidor={pcd_id_distribuidor}',
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
				'opr_codigo'            => $opr_codigo,
				'pcd_id_distribuidor'   => $pcd_id_distribuidor

);

$sql = "SELECT opr_nome,
                o.opr_codigo,
                pcd_id_distribuidor,
                pcd_formato,
                pcd_comissao,
                ( case ";
                foreach ($GLOBALS['DISTRIBUIDORAS_CARTOES'] as $id => $nome){ 
                        $sql .= " when pcd_id_distribuidor = ".$id." then '".$nome."' ";
                }
                $sql .= " end ) as nome_distribuidora
	FROM pins_card_distribuidoras pcd 
        INNER JOIN operadoras o ON (pcd.opr_codigo = o.opr_codigo)
";
if (!empty($opr_codigo))
	$sql_aux[] = "o.opr_codigo = ". $opr_codigo ;
if (!empty($opr_nome))
	$sql_aux[] = "pcd_id_distribuidor = " . $pcd_id_distribuidor . "";
if (isset($sql_aux)) {
	$sql .= ' WHERE ' . implode(' AND ', $sql_aux);
}
//echo "$sql<br>"; //die();
// instancia classe para listagem
$lista = new tabelaLista($sql, $_SERVER['PHP_SELF'], isset($paginacao) ? $paginacao : null, $inicio, $limite, $sort, $dir, $objBotoes);

// campos a serem listados => headers
$camposTabela = array(
    'opr_nome'                  => 'Publisher',
    'nome_distribuidora'	=> 'Distribuidora',
    'pcd_formato'		=> 'Formato',
    'pcd_comissao'		=> 'Comissão',
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