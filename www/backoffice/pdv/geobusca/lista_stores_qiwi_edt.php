<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
function isAjax() {return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));}
function block_direct_calling() {
    if(!isAjax()) {
           echo "Chamada não permitida<br>";
           die("Stop");
    }
}
block_direct_calling();

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";

$us_id = $_POST['us_id'];
?>
<div id='popup_questionario' name='popup_questionario' align='left' title='Edição de Dados'>
	<form method='post' action='lista_stores_qiwi.php' name='frmPreCadastro' id='frmPreCadastro' onsubmit='return true;'>
		<input type='hidden' name='us_id' id='us_id' value='<?php echo $us_id;?>' />
		<img src='/images/epp_logo.png' width='130' height='30' border='0' alt='E-Prepag'>
			<br>
		<div style='color:#1f5b89;font-size:15px;font-weight: bold;'>
			<br>
			<br>
		</div>
		<div style='background-color:#e7eef8;font-size:15px;font-weight: bold;'>
			<br>
		</div> 
		<div style='background-color:#e7eef8;font-size:10px;'>
			<table>
		<?php
				$sql = "SELECT * 
						FROM dist_usuarios_stores_qiwi 
						WHERE us_id = $us_id ";
				//echo $sql.":sql<br>";
				$rs_perguntas = SQLexecuteQuery($sql);
				while ($rs_perguntas_row = pg_fetch_array($rs_perguntas)) {
					echo "<tr><td class='texto'> ID </td><td class='texto'> ".$rs_perguntas_row["us_id"]."<td>"; 
					echo "<tr><td class='texto'> Endereço </td><td> <input name='us_endereco' type='text' id='us_endereco' value='".$rs_perguntas_row["us_endereco"]."' class='form'/><td></tr>"; 
					echo "<tr><td class='texto'> Bairro </td><td> <input name='us_bairro' type='text' id='us_bairro' value='".$rs_perguntas_row["us_bairro"]."' class='form'/><td></tr>"; 
					echo "<tr><td class='texto'> Cidade </td><td> <input name='us_cidade' type='text' id='us_cidade' value='".$rs_perguntas_row["us_cidade"]."' class='form'/><td></tr>"; 
					echo "<tr><td class='texto'> Estado </td><td> <input name='us_estado' type='text' id='us_estado' value='".$rs_perguntas_row["us_estado"]."' class='form'/><td></tr>"; 
					echo "<tr><td class='texto'> CEP </td><td>  <input name='us_cep' type='text' id='us_cep' value='".$rs_perguntas_row["us_cep"]."' class='form'/><td></tr>"; 
				}//end while
		?>
			</table>
		</div>
			<br>
		<center>
			<input type='hidden' name='Submit' id='Submit' value='RESPONDER'>
			<input type='submit' name='resp' id='resp' value='' style="background:url('/images/alterar.gif');background-repeat:no-repeat;width:79px;height=24px;" />
		</center>
</div>
<script type='text/javascript' src='/js/jqueryui/js/jquery-1.7.1.js'></script>
<script type='text/javascript' src='/js/jqueryui/js/jquery-ui-1.8.16.custom.min.js'></script>
<style type='text/css'><!-- @import '/js/jqueryui/css/eprepag/jquery-ui-1.8.16.custom.css'; --></style>
<script>
$(document).ready(
	function(){
		$('#popup_questionario').dialog({
						autoOpen:true,
						height: 400,
						width: 460,
						modal:true,
						closeText: 'hide',
						closeOnEscape: true,
						close: function(event, ui) { 
                    //top.location.href = "index.php";
						}
 	});
	$('.ui-widget-overlay').click(
		function() { 
			$("#popup_questionario").dialog("close"); 
                    }).css("z-index", "-1");
});


</script>

