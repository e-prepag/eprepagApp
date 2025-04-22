<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
header("Content-Type: text/html; charset=ISO-8859-1",true);
function isAjax() {return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));}
function block_direct_calling() {
    if(!isAjax()) {
           echo "Chamada não permitida<br>";
           die("Stop");
    }
}
block_direct_calling();

//error_reporting(E_ALL);
ini_set("display_errors", 1);

if(empty(session_id())){
    //session não está inicada
    session_start();
}

$pagina_titulo = "E-prepag - Créditos para Games";
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."includes/inc_register_globals.php";	

$url = $_SERVER['HTTPS']=="on" ? "https://" : "http://";
$url .= $_SERVER['SERVER_NAME'];

$webstring = "https://".$_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'];
require_once $raiz_do_projeto."includes/access_functions.php";
require_once $raiz_do_projeto.'includes/configIP.php';
require_once $raiz_do_projeto.'includes/configuracaoBO.php';
require_once $raiz_do_projeto."db/connect.php";
require_once $raiz_do_projeto."db/ConnectionPDO.php";
require_once $raiz_do_projeto."includes/header.php";
require_once $raiz_do_projeto."includes/security.php";
require_once $raiz_do_projeto."includes/functions.php";


$qlpr_descricao	= isset($_POST['qlpr_descricao'])	? $_POST['qlpr_descricao']		: NULL;
$qlp_id			= isset($_POST['qlp_id'])			? $_POST['qlp_id']				: NULL;
$qlpr_ativo		= isset($_POST['qlpr_ativo'])		? $_POST['qlpr_ativo']			: NULL;

//echo "qlpr_descricao	= ".utf8_decode($qlpr_descricao[$qlp_id])." - ".$qlpr_descricao[$qlp_id]."<br>";
//echo "qlp_id			= ".$qlp_id."<br>";
//echo "qlpr_descricao	= <pre>".print_r($qlpr_descricao,true)."</pre><br>";
//echo "qlpr_ativo		= <pre>".print_r($qlpr_ativo,true)."</pre><br>";
//die();
?>
<script type="text/javascript" src="/js/jquery-1.5.2.min.js"></script>
<script type="text/javascript" src="/js/jquery-ui-1.8.11.custom.min.js"></script>
<script type="text/javascript" src="/js/jquery.ui.nestedSortable.js"></script>

<script type="text/javascript">
(function(){
	if (!/*@cc_on!@*/0) return;
	var e = ("abbr article aside audio canvas command datalist details figure figcaption footer "+
		"header hgroup mark meter nav output progress section summary time video").split(' '),
	i=e.length;
	while (i--) {
	document.createElement(e[i])
	}
})(document.documentElement,'className');


$(document).ready(function(){

	$('ol.sortable').nestedSortable({
		disableNesting: 'no-nest',
		forcePlaceholderSize: true,
		handle: 'div',
		helper:	'clone',
		items: 'li',
		maxLevels: 3,
		opacity: .6,
		placeholder: 'placeholder',
		revert: 250,
		tabSize: 25,
		tolerance: 'pointer',
		toleranceElement: '> div'
	});
});

function showValues2() {
  var str = $("form").serialize();
  return str;
}

//funcao que adiciona linha de Pergunta
function MM_reload_resp(ID){
        //alert("TESTE AJAX Resposta:"+ID);
		var aux;
		$(document).ready(function(){
			$.ajax({
				type: "POST",
				url: "/ajax/ajaxRespostas.php",
				data: showValues2()+"&qlp_id="+ID,
				success: function(html){
					aux = '#mostraRespostas'+ID;
					$(aux).html(html);
				},
				error: function(){
					alert('Erro Valor');
				}
			});
		});
}

//funcao que exclui linha de pergunta
function MM_Dreload_resp(caixa_selecao){
        $(document).ready(function(){
			$.ajax({
				type: "POST",
				url: "/ajax/ajaxRespostas.php",
				data: showValues2()+"&prg_excluir="+caixa_selecao,
				success: function(html){
					$('#mostraRespostas').html(html);
				},
				error: function(){
					alert('Erro Valor');
				}
			});
		});
}
</script>
<section id="demo<?php echo $qlp_id;?>">
<table width="100%" border="0" align="center" cellpadding="1" cellspacing="0" style="font-size:10px">
   	<?php
//Atualizando as repostas ativas
if (count($qlpr_ativo)>0) {
	if ($qlpr_ativo['0']<>0) {
		//removendo todos os ativos
		$sql = "update tb_questionarios_perguntas_respostas set qlpr_ativo=0 where qlp_id=".$qlp_id;
		$rs_questionario_perguntas = SQLexecuteQuery($sql);
		if(!$rs_questionario_perguntas) {
			echo "Erro ao remover informa&ccedil;&otilde;es de ativo. ($sql)<br>";
		}
		else {
			//ativando somente os selecionados
			$aux_qlpr_id = "";
			foreach ($qlpr_ativo as $key => $value) {
				if (empty($aux_qlpr_id)) {
					$aux_qlpr_id .= $value;
				}
				else {
					$aux_qlpr_id .= ",".$value;
				}
			}//end foreach
			//echo $aux_qlpr_id." :IDS R<br>";
			$sql = "update tb_questionarios_perguntas_respostas set qlpr_ativo=1 where qlpr_id IN (".$aux_qlpr_id.") and qlp_id=".$qlp_id;
			$rs_questionario_perguntas = SQLexecuteQuery($sql);
			if(!$rs_questionario_perguntas) {
				echo "Erro ao ativar informa&ccedil;&otilde;es de ativo. ($sql)<br>";
			}
		}//end else if(!$rs_questionario_perguntas) removendo os ativos
	}//end if ($qlpr_ativo[0]<>0) 
}//end if (count($qlpr_ativo)>0)

//Adicionado a nova Pergunta
if(!empty($qlpr_descricao[$qlp_id])&&!empty($qlp_id)) {
	//colocar insert
	if (in_array(0,$qlpr_ativo)) {
		$aux_qlp_ordem = count($qlpr_ativo);
		$aux_qlpr_ativo = 1;
	}
	else {
		$aux_qlp_ordem = "NULL";
		$aux_qlpr_ativo = 0;
	}
	$sql ="insert into tb_questionarios_perguntas_respostas (qlp_id,qlpr_descricao,qlpr_ativo,qlpr_ordem) values ($qlp_id,'".utf8_decode($qlpr_descricao[$qlp_id])."',$aux_qlpr_ativo,$aux_qlp_ordem) ";
	/*
	echo $sql."<br>";
	if (function_exists('SQLexecuteQuery')) {
		echo "SQLexecuteQuery functions are available.<br />\n";
	} else {
		echo "SQLexecuteQuery functions are NOT available.<br />\n";
	}
	*/
	$rs_questionario_perguntas = SQLexecuteQuery($sql);
	if(!$rs_questionario_perguntas) {
		echo "Erro ao salvar informa&ccedil;&otilde;es da pergunta. ($sql)<br>";
	}
}

//buscar pelo id da pergunta todas as respostas 
$sql = "select * from tb_questionarios_perguntas_respostas where qlp_id=".$qlp_id." order by qlpr_ativo DESC,qlpr_ordem";
//echo $sql."<br>";
$rs_perguntas = SQLexecuteQuery($sql);
$i = 1;
?>
<tr>
	<td>
		<ol class="sortable">
<?php
while($rs_perguntas_row = pg_fetch_array($rs_perguntas)) {
?>
		&nbsp;
		<li id="list_<?php echo $i;?>">
		<div>Resposta (<?php echo $i;?>): <?php echo $rs_perguntas_row["qlpr_descricao"];?>
	&nbsp;<input name="qlpr_ativo[]" type="checkbox" id="qlpr_ativo[]" value="<?php echo $rs_perguntas_row["qlpr_id"]; ?>" <?php if($rs_perguntas_row["qlpr_ativo"]==1) echo "checked";?>/> Ativo </div>
<?php
	$i++;
}
?>
		</ol>
		<br>
	</td>
</tr>
<tr>
	<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nova Resposta: <input name="qlpr_descricao[<?php echo $qlp_id;?>]" type="text" id="qlpr_descricao[<?php echo $qlp_id;?>]" size="60" maxlength="256" value=""/></td>
	<td>&nbsp;<input name="qlpr_ativo[]" type="checkbox" id="qlpr_ativo[]" value="0"/> Ativo &nbsp;&nbsp;&nbsp;</td>
	<td>&nbsp;<img src="/images/add_resposta.gif" width="67" height="22" border="0" alt="Adicionar Resposta" title="Adicionar Resposta" onclick="javascript:MM_reload_resp(<?php echo $qlp_id;?>);" style="cursor:pointer;cursor:hand;">
	</td>
</tr>
</table>
</section> <!-- END #demo -->
