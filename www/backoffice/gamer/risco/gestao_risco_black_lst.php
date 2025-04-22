<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."includes/security.php";
if(b_IsBKOUsuarioAdminGestaodeRisco()){
// inclue a classe para a listagem
require_once $raiz_do_projeto."class/util/classGeralLista.inc.php";
// valores padroes para o inicio e limite da listagem, ordenacao e direcao da ordenacao
$inicio		= isset($_POST['inicio'])		? htmlentities($_POST['inicio'])		: 0;
$limite		= isset($_POST['limite'])		? htmlentities($_POST['limite'])		: 10;
$sort		= isset($_POST['sort'])			? htmlentities($_POST['sort'])			: 'ugbl_status';
$dir		= isset($_POST['dir'])			? htmlentities($_POST['dir'])			: '';
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
<table width="99%" border="0" align="center">
    <tr>
        <td valign="top">
            <table width="100%" border="0" align="center">
                <tr>
                    <td><a href="index_gestao_risco_black.php?acao=novo" class="btn btn-info btn-sm">Novo</a></td>
                </tr>
                <tr>
                    <td valign="top">
                        <form id="form1" name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                            <table class="table top10">
                                <tr>
                                    <td colspan="4" bgcolor="#DDDDDD">Filtro de Pesquisa</td>
                                </tr>
                                <tr>
                                    <td align="right">C&oacute;digo do Usu&aacute;rio: </td>
                                    <td><input name="ug_id" type="text" id="ug_id" size="20" maxlength="20" value="<?php echo $ug_id;?>"/></td>
                                    <td><div align="right">Status do Usu&aacute;rio: </div></td>
                                    <td>
										<select name="ugbl_status" id="ugbl_status" class="combo_normal">
											<option value="" <?php if($ugbl_status==='') echo "selected" ?>>Selecione</option>
											<?php foreach ($ativacao as $key => $value) { ?>
											<option value="<?php echo $key ?>" <?php if($key === $ugbl_status) echo "selected" ?>><?php echo "(".$key.") ".$value ?></option>
											<?php } ?>
										</select>
									</td>
                                </tr>
                                <tr>
                                    <td colspan="6" align="center"><input name="btn_pesquisar" type="submit" id="btn_pesquisar" class="btn btn-info btn-sm" value="Pesquisar" /></td>
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
                'acao'     => 'index_gestao_risco_black.php?acao=editar&ug_id={ug_id}',
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
				'ug_id'	=> $ug_id,
				'ugbl_status'=> $ugbl_status

);

$sql = "SELECT
		(CASE WHEN(ug_id>0 AND ug_id!=7909) THEN '<a href=\'/gamer/usuarios/com_usuario_detalhe.php?usuario_id='||ug_id||'\' target=\'_blank\'>' || ug_id ||'</a>' END) as ug_id_aux,
		ug_id,
		(CASE WHEN cast(ugbl_status as char)='0' THEN cast('Está na Lista' as TEXT) WHEN cast(ugbl_status as char)='1' THEN cast('Retirado da Lista' as TEXT) ELSE cast(ugbl_status as char) END) as ugbl_status, 
		shn_login,
		to_char(ugbl_data_ultima_alteracao,'DD/MM/YYYY') as ugbl_data_ultima_alteracao
	FROM usuarios_games_black_list";
if (!empty($ugbl_status))
	$sql_aux[] = "ugbl_status = ".$ugbl_status;
if (!empty($ug_id))
	$sql_aux[] = "ug_id = ". $ug_id ;
if (is_array($sql_aux)) {
	$sql .= ' WHERE ' . implode(' AND ', $sql_aux);
}
//echo $sql."<br>";
// instancia classe para listagem
$lista = new tabelaLista($sql, $_SERVER['PHP_SELF'], isset($paginacao) ? $paginacao : null, $inicio, $limite, $sort, $dir, $objBotoes);

// campos a serem listados => headers
$camposTabela = array(
    'ug_id_aux'						=> 'ID do Usu&aacute;rio',
    'ugbl_status'				=> 'Status',
    'shn_login'					=> 'BackOffice',
    'ugbl_data_ultima_alteracao'=> 'Data Altera&ccedil;&atilde;o'
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
}//end if(b_IsBKOUsuarioAdminGestaodeRisco())
?>