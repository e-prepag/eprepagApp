<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 



$tipos_perguntas = array(
				'U' => "Resposta &Uacute;nica",
				'M' => "Resposta com Multiplas Op&ccedil;&otilde;es",
				);

header("Content-Type: text/html; charset=ISO-8859-1",true);
function isAjax() {return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));}
function block_direct_calling() {
    if(!isAjax()) {
           echo "Chamada não permitida<br>";
           die("Stop");
    }
}
block_direct_calling();

//desenvolvimento	
if(false) $raiz_do_projeto = "D:\\Projetos\\Outros\\E-Prepag\\Sites\\Producao";
//
//error_reporting(E_ALL);
ini_set("display_errors", 1);

if(empty(session_id())){
    //session não está inicada
    session_start();
}

$pagina_titulo = "E-prepag - Créditos para Games";
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."includes/inc_register_globals.php";	
require_once "/www/includes/bourls.php";
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


$qlp_texto		= isset($_POST['qlp_texto'])		? utf8_decode($_POST['qlp_texto'])	: NULL;
$quest_id_update= isset($_POST['quest_id_update'])	? $_POST['quest_id_update']			: NULL;
$qlp_ativo		= isset($_POST['qlp_ativo'])		? $_POST['qlp_ativo']				: NULL;
$qlp_tipo		= isset($_POST['qlp_tipo'])			? $_POST['qlp_tipo']				: NULL;
$qlp_outros		= isset($_POST['qlp_outros'])		? $_POST['qlp_outros']				: NULL;

//echo "qlp_texto			= ".$qlp_texto."<br>";
//echo "quest_id_update	= ".$quest_id_update."<br>";
//echo "qlp_ativo			= ".print_r($qlp_ativo,true)."<br>";
//die();
?>
<style type="text/css">
<!--
#teste {
			z-index: 2;
			height: 100%;
			width: 100%;
			color: #000000;
			font-size: 14px;
			background-color: #CCCCCC;
			border: 1px solid #444;
			padding: 5px;
			position: fixed;
			*position: absolute;
			top: 1px;
			left: 1px;
			text-align: left;
			display: none;
			overflow: auto;
			*overflow: hidden;
			-moz-opacity: 0.65;
			opacity: 0.65;
			filter: alpha(opacity=65);
}

#boxPopUpAlterar {
			z-index: 2;
			height: 240px;
			width: 560px;
			color: #000000;
			font-size: 14px;
			background-color: #FFFFFF;
			border: 1px solid #444;
			padding: 5px;
			position: fixed;
			*position: absolute;
			top: 40%;
			*top: auto;
			left: 30%;
			text-align: left;
			display: none;
			overflow: auto;
			*overflow: hidden;
			}
-->
</style>

<script type="text/javascript" src="/js/jquery-1.5.2.min.js"></script>
<script type="text/javascript" src="/js/jquery-ui-1.8.11.custom.min.js"></script>
<script type="text/javascript" src="/js/jquery.ui.nestedSortable.js"></script>

<script type="text/javascript">

function load_alterar(id) {
	$('#teste').show();
	$('#boxPopUpAlterar').load("ajaxAlterarPerguntas.php?qlp_id="+id).show();
}

function fecha() {
	$('#boxPopUpAlterar').hide();
}

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

	$('#Submit').click(function(e){
		hiered = $('ol.sortable').nestedSortable('toHierarchy', {startDepthCount: 0});
		hiered = dump(hiered);
		document.frmPreCadastro.vetor_ordem.value = hiered;
		//alert(document.frmPreCadastro.vetor_ordem.value);
	});
});

function dump(arr,level) {
	var dumped_text = "";
	if(!level) level = 0;

	//The padding given at the beginning of the line.
	var level_padding = "";
	
	if(typeof(arr) == 'object') { //Array/Hashes/Objects
		for(var item in arr) {
			var value = arr[item];
			if(dumped_text.length > 0) {
				dumped_text += ";";
			}
			if(typeof(value) == 'object') { //If it is an array,
				dumped_text += level_padding + "" + item + ":";
				dumped_text += dump(value,level+1);
			} else {
				dumped_text += level_padding + "" + value;
			}
		}
	} else { //Strings/Chars/Numbers etc.
		dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
	}
	return dumped_text;
}

function showValues() {
  var str = $("form").serialize();
  return str;
}

//funcao que adiciona linha de Pergunta
function MM_reload(){
        //alert("TESTE AJAX");
		$(document).ready(function(){
			$.ajax({
				type: "POST",
				url: "/ajax/ajaxPerguntas.php",
				data: showValues(),
				success: function(html){
                    html = html.replace("<script type=\"text/javascript\" src=\"/js/jquery-1.5.2.min.js\"><\/script>","");
                    html = html.replace("<script type=\"text/javascript\" src=\"/js/jquery-ui-1.8.11.custom.min.js\"><\/script>","");
					$('#mostraPerguntas').html(html);
				},
				error: function(){
					alert('Erro Valor 1');
				}
			});
		});
}

//funcao que exclui linha de pergunta
function MM_Dreload(caixa_selecao){
        $(document).ready(function(){
			$.ajax({
				type: "POST",
				url: "/ajax/ajaxPerguntas.php",
				data: showValues()+"&prg_excluir="+caixa_selecao,
				success: function(html){
                    html = html.replace("<script type=\"text/javascript\" src=\"/js/jquery-1.5.2.min.js\"><\/script>","");
                    html = html.replace("<script type=\"text/javascript\" src=\"/js/jquery-ui-1.8.11.custom.min.js\"><\/script>","");
					$('#mostraPerguntas').html(html);
				},
				error: function(){
					alert('Erro Valor 2');
				}
			});
		});
}

//funcao que adiciona linha de Resposta
function MM_load_resp(ID){
        //alert('Resp :'+ID);
		$(document).ready(function(){
			$.ajax({
				type: "POST",
				url: "/ajax/ajaxRespostas.php",
				data: "qlp_id="+ID,
				success: function(html){
					var aux = '#mostraRespostas'+ID;
					//alert(aux);
                    html = html.replace("<script type=\"text/javascript\" src=\"/js/jquery-1.5.2.min.js\"><\/script>","");
                    html = html.replace("<script type=\"text/javascript\" src=\"/js/jquery-ui-1.8.11.custom.min.js\"><\/script>","");
					$(aux).html(html);
				},
				error: function(){
					alert('Erro Valor 3');
				}
			});
		});
}
</script>
<section id="demo">
<table width="100%" border="0" align="center" cellpadding="1" cellspacing="0" style="font-size:10px">
   	<?php
	//Atualizando as perguntas ativas
	if (isset($qlp_ativo) && count($qlp_ativo)>0) {
		//removendo todos os ativos
		$sql = "update tb_questionarios_perguntas set qlp_ativo=0 where ql_id_questionario=".$quest_id_update;
		$rs_questionario_perguntas = SQLexecuteQuery($sql);
		if(!$rs_questionario_perguntas) {
			echo "Erro ao remover informa&ccedil;&otilde;es de ativo. ($sql)<br>";
		}
		else {
			//ativando somente os selecionados
			$aux_qlp_id = "";
			foreach ($qlp_ativo as $key => $value) {
				if (empty($aux_qlp_id)) {
					$aux_qlp_id .= $value;
				}
				else {
					$aux_qlp_id .= ",".$value;
				}
			}//end foreach
			//echo $aux_qlp_id." :IDS<br>";
			$sql = "update tb_questionarios_perguntas set qlp_ativo=1 where qlp_id IN (".$aux_qlp_id.")";
			$rs_questionario_perguntas = SQLexecuteQuery($sql);
			if(!$rs_questionario_perguntas) {
				echo "Erro ao ativar informa&ccedil;&otilde;es de ativo. ($sql)<br>";
			}
		}//end else if(!$rs_questionario_perguntas) removendo os ativos
	}//end if (count($qlp_ativo)>0)

	//Adicionado a nova Pergunta
	if(!empty($qlp_texto)&&!empty($quest_id_update)) {
		//colocar insert
		if (isset($qlp_ativo) && in_array(0,$qlp_ativo)) {
			$aux_qlp_ordem = count($qlp_ativo);
			$aux_qlp_ativo = 1;
		}
		else {
			$aux_qlp_ordem = "NULL";
			$aux_qlp_ativo = 0;
		}
		$sql ="insert into tb_questionarios_perguntas (ql_id_questionario,qlp_texto,qlp_ativo,qlp_ordem,qlp_tipo,qlp_outros) values ($quest_id_update,'$qlp_texto',$aux_qlp_ativo,$aux_qlp_ordem,'$qlp_tipo',".intval($qlp_outros*1).") ";
		//echo $sql."<br>"."[$qlp_outros]<br>";
		/*
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

	//buscar pelo id do questionario todas as perguntas 
	$sql = "select * from tb_questionarios_perguntas where ql_id_questionario=".$quest_id_update." order by qlp_ativo DESC,qlp_ordem";
	//echo $sql."<br>";
	$rs_perguntas = SQLexecuteQuery($sql);
	$enumerador	= 1;
	?>
	<tr>
		<td colspan="4">
			<ol class="sortable">
	<?php
	while($rs_perguntas_row = pg_fetch_array($rs_perguntas)) {
	?>
		&nbsp;
		<li id="list_<?php echo $enumerador;?>">
		<div><?php echo $rs_perguntas_row["qlp_texto"]; ?>
		&nbsp;<span style="margin-left: 32"><?php echo "(".$rs_perguntas_row["qlp_tipo"].") ".$tipos_perguntas[$rs_perguntas_row["qlp_tipo"]]; ?></span>&nbsp;<span style="margin-left: 32"><input name="qlp_ativo[]" type="checkbox" id="qlp_ativo[]" value="<?php echo $rs_perguntas_row["qlp_id"]; ?>" <?php if($rs_perguntas_row["qlp_ativo"]==1) echo "checked";?>/> Ativo</span>&nbsp;<span style="margin-left: 32"><?php if($rs_perguntas_row["qlp_outros"]==1) echo "Com Op&ccedil;&atilde;o Outros"; else  echo "Sem Op&ccedil;&atilde;o Outros";?></span>&nbsp;<span style="margin-left: 32"><img src="/images/pencil.png" width="16" height="16" border="0" alt="Editar" title="Editar" style="cursor:pointer;cursor:hand;" onClick="javascript:load_alterar(<?php echo $rs_perguntas_row["qlp_id"]; ?>);"></span> </div>
		<br><br>
		<fieldset>
			<legend>Respostas da Pergunta</legend>
			<span id='mostraRespostas<?php echo $rs_perguntas_row["qlp_id"];?>'>
				<script type="text/javascript">
					MM_load_resp(<?php echo $rs_perguntas_row["qlp_id"];?>);
				</script>
			</span>
		</fieldset>
		
		<br>
	<?php
		$enumerador ++;
	}//end while
	?>
			</ol>
		</td>
	</tr>
	<tr>
		<td colspan="4">&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;Nova Pergunta: <input name="qlp_texto" type="text" id="qlp_texto" size="70" maxlength="512" value=""/></td>
		<td>&nbsp;Tipo de Resposta: 
			 <select name="qlp_tipo" id="qlp_tipo" class="combo_normal">
			  <?php foreach ($tipos_perguntas as $key => $value) { ?>
			  <option value="<?php echo $key ?>" <?php if($key == $qlp_tipo) echo "selected" ?>><?php echo "(".$key.") ".$value ?></option>
			  <?php } ?>
			</select>
		</td>
		<td align="center"><nobr>&nbsp;Ativo: <input name="qlp_ativo[]" type="checkbox" id="qlp_ativo[]" value="0"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Op&ccedil;&atilde;o Outros: <select name="qlp_outros" id="qlp_outros" class="combo_normal">
			  <option value="1" selected>Sim</option>
			  <option value="0">N&atilde;o</option>
			</select></nobr></td>
		<td>&nbsp;<img src="<?php echo $url . ":$server_port/images/add_pergunta.gif" ?>" width="67" height="22" border="0" alt="Adicionar Pergunta" title="Adicionar Pergunta" onclick="javascript:MM_reload();" style="cursor:pointer;cursor:hand;">
		<input type="hidden" name="vetor_ordem" id="vetor_ordem" />
		<!--input type="button" name="toHierarchy" id="toHierarchy" value="To hierarchy" />
		<pre id="toHierarchyOutput"></pre-->
		</td>
	</tr>
</table>
</section> <!-- END #demo -->
