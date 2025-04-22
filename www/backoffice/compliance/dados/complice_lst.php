<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."includes/security.php";
// inclue a classe para a listagem
require_once $raiz_do_projeto."class/util/classGeralLista.inc.php";
// valores padroes para o inicio e limite da listagem, ordenacao e direcao da ordenacao
$inicio		= isset($_POST['inicio'])		? htmlentities($_POST['inicio'])		: 0;
$limite		= isset($_POST['limite'])		? htmlentities($_POST['limite'])		: 100;
$sort		= isset($_POST['sort'])			? htmlentities($_POST['sort'])			: 'ano';
$dir		= isset($_POST['dir'])			? htmlentities($_POST['dir'])			: 'DESC';
$ano            = isset($_REQUEST['ano'])               ? htmlentities($_REQUEST['ano'])                : '';
$mes            = isset($_REQUEST['mes'])               ? htmlentities($_REQUEST['mes'])                : '';
?>
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
            <table width="100%" border="0" align="center">
                <tr>
                    <td valign="top">
                        <form id="form1" name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                            <table class="table">
                                <tr>
                                    <td colspan="4">
                                        <a href="index.php?acao=novo" class="btn btn-sm btn-info">Novo</a>
									</td>
								</tr>
                                <tr>
                                    <td colspan="4" bgcolor="#DDDDDD">Filtro de Pesquisa</td>
                                </tr>
                                <tr>
                                    <td align="right">Ano dos Dados de Complice: </td>
                                    <td><select name="ano" id="ano" class="combo_normal">
                                                <option value="" >Todos os Anos</option>
                                        <?php  for($i =  date('Y'); $i >= $ANO_INICIO_OPERACAO ; $i--) { ?>
                                                <option value="<?php  echo $i ?>" <?php  if($ano == $i) echo "selected" ?>><?php  echo $i ?></option>
                                        <?php  } ?>
                                        </select>
                                    </td>
                                    <td><div align="right">Mês dos Dados de Complice: </div></td>
                                    <td><select name="mes" id="mes" class="combo_normal">
                                                <option value="" >Todos os Mêses</option>
                                        <?php
                                            for ($codigoMes=1; $codigoMes<=12; $codigoMes++){
                                                   if (strlen($codigoMes) == 1){
                                                           $codigoMes = '0'.$codigoMes;
                                                   }

                                                   echo '<option value="'.$codigoMes.'"';
                                                   if ($mes == $codigoMes){
                                                           echo ' SELECTED';
                                                   }
                                                   echo '>'.mesNome($codigoMes).'</option>';
                                            }
                                            ?>
                                    </select></td>
                                </tr>
                                <tr>
                                    <td colspan="4" align="center"><input name="btn_pesquisar" type="submit" id="btn_pesquisar" value="Pesquisar" class="btn btn-sm btn-info" /></td>
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
                'acao'     => 'index.php?acao=editar&ano={ano}&mes={mes}',
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
				'ano' => $ano,
				'mes' => $mes

);

$sql = "SELECT
		extract (year from c_ano_mes) as ano,
		extract (month from c_ano_mes) as mes,
                to_char(c_ano_mes, 'Month') as nome_mes,
		'<nobr>'||(case when (c_custo_mkt_credenciado > 0 AND c_custo_risco_credenciador > 0
                                        AND c_custo_outros_credenciador > 0 AND c_receita_mkt_emissor > 0
                                        AND c_receita_outras_emissor > 0 AND c_custo_risco_emissor > 0
                                        AND c_custo_processamento_emissor > 0 AND c_custo_mkt_emissor > 0
                                        AND c_custo_inadimplencia_emissor > 0 AND c_custos_outros_emissor > 0
                                        AND c_custo_impostos_emissor > 0 AND c_receita_credenciador > 0
                                        AND c_receita_outras_credenciador > 0 AND c_custo_processamento_front_end_back_end > 0) 
                            then 'Todos os Dados Preenchidos' else 'Faltam Dados à Preencher' end)||'</nobr>' as dados
	FROM complice ";
if(!isset($sql_aux))
    $sql_aux = null;

if (!empty($mes))
	$sql_aux[] = "(extract (month from c_ano_mes) = ".$mes.") ";
if (!empty($ano))
	$sql_aux[] = "(extract (year from c_ano_mes) = ".$ano.") ";
if (is_array($sql_aux)) {
	$sql .= ' WHERE ' . implode(' AND ', $sql_aux);
}
//echo "$sql<br>";
// instancia classe para listagem
$lista = new tabelaLista($sql, $_SERVER['PHP_SELF'], isset($paginacao) ? $paginacao : null, $inicio, $limite, $sort, $dir, $objBotoes);

// campos a serem listados => headers
$camposTabela = array(
    'ano'		=> 'Ano',
    'nome_mes'		=> 'Mes',
    'dados'		=> 'Detalhes de Dados',
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