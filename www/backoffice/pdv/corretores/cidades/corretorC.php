<?php
require_once '../../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";

$ps_query = "SELECT distinct ug_estado FROM dist_usuarios_games where 1=1 ;";
//echo $ps_query;
/// todas as lan que estiverem nesse bairro
//pg_send_query($conex,$ps_query);
//$res1 = pg_get_result($conex);
$res1 = SQLexecuteQuery($ps_query);


?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<style type="text/css">
<!--
td, p, b, a, li {font-size: 12px; }
.style3 {color: #000000; font-family: Arial, Helvetica, sans-serif; }
.style5 {color: #000000; font-family: Arial, Helvetica, sans-serif; font-weight: bold; }
.teste {background-color: #C5E9FA; }
-->
</style>
<form name="form" method="POST" action="corretorC.php">
<table width="100%" height="144">
  <tr>
    <td width="286" height="42" align="left" valign="top" class="style3"><strong>Estado</strong>:<br />
    Escolha um Estado</td>
    <td colspan="2" align="left" valign="top" class="style3"><strong>Cidade</strong>:<br />
    Lista de Cidades que o estado escolhido possui, clique em um nome para escolher a modifica&ccedil;&atilde;o</td>
    <td width="340" align="left" valign="top" bgcolor="#FFFFFF" class="style3"><strong>Modificar Para: </strong><br />
    Escolha como deseja corrigir o nome da Cidade</td>
    <td width="210" align="left" valign="top" bgcolor="#C5E9FA"><p class="style3"><strong>Varia&ccedil;&otilde;es Encontradas</strong>:<br />
    Selecione os nomes que ir&atilde;o ser corrigidos:</p>    </td>
  </tr>
  <tr valign="top">
  <td class="style3">
  <?php
  $estado_sel	= isset($_REQUEST['estado'])			? $_REQUEST['estado']			: null;
  ?>
  <select id='estado' name='estado'>
  <option value=''>Estados</option>
  <?php
  while ($info = pg_fetch_array($res1)) {
	?><option value='<?php echo $info[0]?>'<?php if ($info[0]==$estado_sel) echo " selected"?>><?php echo $info[0]?></option>;
<?php } ?>
  </select>
  <input type="submit" value="Atualizar">
  </td>
  <td colspan="2" class="style3" id='cidade'>&nbsp;</td>
  <td bgcolor="#FFFFFF" class="style3" id='corretor'>&nbsp;</td>
  <td bgcolor="#C5E9FA" class="style3" id='variacao'>&nbsp;</td>
  </tr>
  <tr>
    <td colspan="5" class="style5" id='resultado'>Resultados:</td>
  </tr>
  <tr>
    <td colspan="3" bgcolor="#FFE3AA" class="style5">Nomes de cidades que est&atilde;o fora do Padr&atilde;o: </td>
    <td colspan="3" bgcolor="#C5E9FA" class="style5">Nomes de cidades que est&atilde;o Padronizados:</td>
  </tr>
  <tr>
    <td valign="top" bgcolor="#FFE3AA" class="style3" id='list_bad'>&nbsp;</td>
    <td width="177" valign="top" bgcolor="#FFE3AA" class="style3" id='list_de'>&nbsp;</td>
    <td width="184" valign="top" bgcolor="#FFE3AA" class="style3" id='list_se'>&nbsp;</td>
    <td colspan="3" valign="top" bgcolor="#C5E9FA" class="style3" id='list_ok'>&nbsp;</td>
  </tr>
</table>
</form>
<script  type="text/javascript" language="javascript"> 
////////////////////////////////////////////ESTADO QUE CARREGA CIDADES //////////
$("#estado").change(function () {

	var ValorSelecionadoEstado = document.getElementById("estado").value;
	
	$("#cidade").load("selectcidades.php","estado="+ValorSelecionadoEstado);
	
		 });

///////////////////////////CARREGA AS SIMILIARIDADES DAS PALAVRAS //////////////		 
$(document).on( 'change', "#lista_cidades" ,function() {
	
		 
	var ValorSelecionadoCidade = document.getElementById("lista_cidades").value;
	var ValorSelecionadoEstado = document.getElementById("estado").value;

	$("#variacao").load("variacao.php","estado="+ValorSelecionadoEstado+"&cidade="+ValorSelecionadoCidade);
	$("#corretor").load("corretor.php","estado="+ValorSelecionadoEstado+"&cidade="+ValorSelecionadoCidade);
			
	 });

/////////////////////////MANDA PARAMETROS PARA CORREÇÃO ///////////////////////
$(document).on( 'click', "#corrigir" ,function() {
		 
	
	var ValorSelecionadoCidade = document.getElementById("lista_cidades").value;
	$("#variacao").load("variacao.php","cidade="+ValorSelecionadoCidade);
	$("#corretor").load("corretor.php","cidade="+ValorSelecionadoCidade);
			
	 });
/////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////

////////QUANDO DIGITAR O VALOR IRÁ PARA A CAIXA DE MODELO ///////////////////////
$(document).on( 'keyup', "#palavra" ,function() {
	var ValorPalavra = document.getElementById('palavra').value;
	$("#word").attr('value',ValorPalavra);
	
	if ($("input:checked").length > 1 && ValorPalavra !== ""  ) {
		
			$("#carregar").removeAttr('disabled');
		
		} else {
		
			$("#carregar").attr('disabled','disabled');
			
		}
});
//////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////
//CARREGA O CHANGER A PALAVRA QUE VAI CORRIGIR NA TELA FINAL E O BOTÃO SUBMIT//
$(document).on( 'change', "#lista_corretor" ,function() {

var ValorSelecionadoListaCorretor = document.getElementById("lista_corretor").value;
var ValorSelecionadoEstado = document.getElementById('estado').value;
	$("#word").attr('value',ValorSelecionadoListaCorretor);
	$("#c_estado").attr('value',ValorSelecionadoEstado);
	$("#carregar").removeAttr('disabled');
			
	 });
/////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////

/// CONTROLE DE OPÇÔES PARA MODIFICAÇÃO PREFERENCIAL DO NOME/////////////////////
$(document).on( 'click', "#r1" ,function() {
	 $("#palavra").attr('disabled','disabled');
	 $("#lista_corretor").removeAttr('disabled');
 });

$(document).on( 'click', "#r2" ,function() {
	$("#palavra").removeAttr('disabled');
	$("#lista_corretor").attr('disabled','disabled');
 });

 ////////////////////////////////////////////////////////////////////////////////
 //////////////////////// Carregando Relatórios de listas ///////////////////////

$("#list_bad").load("listaCidadeBad.php");
$("#list_de").load("listaCidadeDe.php");
$("#list_se").load("listaCidadeSe.php");
$("#list_ok").load("listaCidadeOk.php");
 ////////////////////////////////////////////////////////////////////////////////

 ////////////////////////////////////////////////////////
/////////////////Submete o formulário via POST ////////
$(document).on( 'click', "#carregar" ,function() {
	  if (confirm('Tem certeza que quer realizar esta alteração ?')) {
		  var ValuesForm = $('#formfinal').serialize();
//		  alert('Entrou: '+ValuesForm);
		  $.ajax({	url: 'corrigeCidade.php', 
			type: "POST",
			data: ValuesForm,
			success: function(msg){
					jQuery("#resultado").html(msg)},
			error: function (xhr, ajaxOptions, thrownError){
                    alert('Status: '+xhr.status);
                    alert('Error:' +thrownError);
                }    
			});
		  }
	//$('#resultado').load('corrigeBairro.php')
 });
 ////////////////////////////////////////////////////////
 ////////////////////////////////////////////////////////
</script>
<?php require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php"; ?>
</body> 
</html>