<?php
ob_start();
require_once '../../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
$ps_query = "SELECT distinct ug_estado FROM dist_usuarios_games where 1=1 ;";	// where ug_ativo = '1' 
//echo $ps_query;
/// todas as lan que estiverem nesse bairro
$res1 = SQLexecuteQuery($ps_query);
//pg_send_query($conex,$ps_query);
//$res1 = pg_get_result($conex);

?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<style>
    td, p, b, a{font-size: 12px; color: #000000; background-color: #ffffff;}
.teste {background-color: #C5E9FA !important; }
.style3 {color: #000000; font-family: Arial, Helvetica, sans-serif; }
.style4 {
	font-family: Arial, Helvetica, sans-serif;
	font-weight: bold;
}
.style6 {
	font-family: Arial, Helvetica, sans-serif;
	font-weight: bold;
	color: #000000;
}
.style7 {font-family: Arial, Helvetica, sans-serif}
.style8 {color: #000000}
</style>
<table width="100%" height="144" border="1">
  <tr>
    <td width="286" height="42" align="center" valign="top"><span class="style4">Estado</span><br />
    <span class="style3">Escolha um Estado</span></td>
    <td width="143" align="center" valign="top"><span class="style4">Cidade</span><br />
    <span class="style3">Lista de Cidades que o estado escolhido possui, clique em um nome para escolher a modifica&ccedil;&atilde;o</span></td>
    <td width="144" align="center" valign="top"><span class="style4">Bairro</span><br />
      <span class="style3">Lista de Bairros que a cidade escolhida possui, clique em um nome para escolher a modifica&ccedil;&atilde;o</span><br /></td>
    <td width="72" align="center" valign="top"><span class="style4">Modificar Para: </span><br />
    <span class="style3">Escolha como deseja corrigir o nome do Bairro</span></td>
    <td width="210" align="center" valign="top" bgcolor="#6699CC"><span class="style6">Varia&ccedil;&otilde;es Encontradas:</span><span class="style8"><br />
    <span class="style7">Selecione os nomes que ir&atilde;o ser corrigidos:</span></span></td>
  </tr>
  <tr valign="top">
  <td><select id='estado' name='estado'>
  <option value=''>Estados</option>
  <?php
  while ($info = pg_fetch_array($res1)) {
	?><option value='<?php echo $info[0]?>'><?php echo $info[0]?></option>
<?php } ?>
  </select></td>
  <td id='cidade'>&nbsp;</td>
  <td id='bairro'>&nbsp;</td>
  <td id='corretor'>&nbsp;</td>
  <td bgcolor="#6699CC" class="style8" id='variacao'>&nbsp;</td>
  </tr>
  <tr>
      <td colspan="5" style="background-color: #999999;" id='resultado'>Resultado:</td>
  </tr>
  <tr>
    <td colspan="2" style="background-color: #FFCCFF;">Nomes de bairros que est&atilde;o fora do Padr&atilde;o: </td>
    <td colspan="3" style="background-color: #6699FF;">Nomes de bairros que est&atilde;o Padronizados:</td>
  </tr>
  <tr>
    <td valign="top" style="background-color: #FFCCFF;" id='list_bad'>&nbsp;</td>
    <td valign="top" style="background-color: #FFCCFF;" id='list_de'>&nbsp;</td>
    <td colspan="3" valign="top" style="background-color: #6699FF;"  id='list_ok'>&nbsp;</td>
  </tr>
</table>
<script  type="text/javascript" language="javascript"> 
////////////////////////////////////////////ESTADO QUE CARREGA CIDADES //////////

$("#estado").change(function carregacidade() {

	var ValorSelecionadoEstado = document.getElementById("estado").value;
	
	$("#cidade").load("selectcidades.php","estado="+ValorSelecionadoEstado);
	
		 });
		 
/////////////////////////MANDA PARAMETROS PARA CORREÇÃO ///////////////////////
$(document).on( 'click', "#corrigir" ,function() {
		 	
	var ValorSelecionadoEstado = document.getElementById("estado").value;
	var ValorSelecionadoCidade = document.getElementById("cidade").value;
	var ValorSelecionadoBairros = document.getElementById("lista_bairros").value;
	$("#variacao").load("variacao.php","bairro="+ValorSelecionadoBairros+"&estado="+ValorSelecionadoEstado);
	$("#corretor").load("corretor.php","bairro="+ValorSelecionadoBairros+"&estado="+ValorSelecionadoEstado);
			
 });



//QUANDO DIGITAR O VALOR IRÁ PARA A CAIXA DE MODELO ///////////////////////////
$(document).on( 'keyup', "#palavra" ,function() {
	var ValorPalavra = document.getElementById('palavra').value;
	$("#word").attr('value',ValorPalavra);
	
	if ($("input:checked").length > 1 && ValorPalavra !== ''  ) {
		
			$("#carregar").removeAttr('disabled');
		
		} else {
		
			$("#carregar").attr('disabled','disabled');
			
		}
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
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////
/////////////////Submete o formulário via POST ////////
$(document).on( 'click', "#carregar" ,function() {
        if (confirm('Tem certeza que quer realizar esta alteração ?')) {
		  var ValuesForm = $('#formfinal').serialize();
		  $.ajax({	url: 'corrigeBairro.php', 
			type: "POST",
			data: ValuesForm,
			success: function(msg){
                $("#resultado").html(msg)}//fim function msg
            })
        }
 });
 ////////////////////////////////////////////////////////
 ////////////////////////////////////////////////////////
 ////////////////////////////////////////////////////////////////////////////////
 //////////////////////// Carregando Relatórios de listas ///////////////////////

$("#list_bad").load("listaBairroBad.php");
$("#list_de").load("listaBairroDe.php");
$("#list_ok").load("listaBairroOk.php");
 ////////////////////////////////////////////////////////////////////////////////
 //////////////////////////////							/////////////////////////
 ////////////////////////////////////////////////////////////////////////////////

</script>
<?php require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php"; ?>
</body> 
</html>