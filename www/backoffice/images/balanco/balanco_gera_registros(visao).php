<?php 

include "../includes/classPrincipal.php"; ?>
<?php  include "../../../incs/topo_bko.php"; ?>
<?php  include "../../financeiro/corte/corte_constantes.php"; 

include "inc_balanco.php"; 

// Calcula total de lans ativas/inativas
$total_query = "select ug_id from dist_usuarios_games order by ug_id" ;
$total_rs_query = SQLexecuteQUERY($total_query);
$total_row = pg_num_rows($total_rs_query);

// Calcula total balanços cadastrados
$balancos_query = "select count(*) as n, min(db_data_balanco) as dmin, max(db_data_balanco) as dmax from dist_balancos; " ;
$balancos_rs_query = SQLexecuteQUERY($balancos_query);
$balanco_total = -1;
$balanco_data_min = "";
$balanco_data_max = "";

if($balanco_row = pg_fetch_array($balancos_rs_query)) {
	$balanco_total = $balanco_row['n'];
	$balanco_data_min = substr($balanco_row['dmin'],0,10);
	$balanco_data_max = substr($balanco_row['dmax'],0,10);
}
?>

<script src="../includes/jquery.js" language="javascript"></script>
<script>
function roda_registros_total_antiga() {

var id_lan = $("#id_lan").val();
var total_lan = eval($("#limite").val());

		if (id_lan < total_lan ) {

				$.ajax({
								
							type: "POST",
							url: "balanco_gera_registros.php",
							data: {n:id_lan,d:total_lan},
							success: function(html){
									$("#conteudo").html(html);
									id_lan ++;
									
									$("#id_lan").attr('value',id_lan) ; 
									roda_registros_total();
							 } // fim function success
				}); //fim ajax

		}// fim if
}// fim function



function roda_registros_individual() {

	var id_lan = $("#cod_lan").val();

	$.ajax({								
		type: "POST",
		url: "balanco_gera_registros_unitario.php",
		data: {id:id_lan},
		beforeSend: function(){
//alert("id_lan: "+id_lan);
			$("#conteudo").html("Waiting...");	
		},
		success: function(html){
			$("#conteudo").html(html);
		}, 
		error:function(x,e){
			if(x.status==0){
				$("#conteudo").html('You are offline!!\n Please Check Your Network.');
			}else if(x.status==404){
				$("#conteudo").html('Requested URL not found.');
			}else if(x.status==500){
				$("#conteudo").html('Internal Server Error.');
			}else if(e=='parsererror'){
				$("#conteudo").html('Error.\nParsing JSON Request failed.');
			}else if(e=='timeout'){
				$("#conteudo").html('Request Time out.');
			}else {
				$("#conteudo").html('Unknow Error.\n'+x.responseText);
			}
		}

	}); //fim ajax
	
}// fim function

</script>
<script>
$(document).ready( function () { 

	$('#res_total').html( $('#total_entrada').val());		

	$('#res_saida').html( $('#total_saida').val());	

	$('#res_comissao').html( $('#total_comissao').val());	

	//roda_registros();
});

</script>
<table width="894" border="0" cellspacing="0" cellpadding="0">
  <tr> 
    <td width="891" height="102" valign="top">
    <table width="894" border="0" cellpadding="0" cellspacing="2">
          <tr> 
            <td colspan="5">
				<table width="894" border="0" cellpadding="0" cellspacing="0" dwcopytype="CopyTableCell">
					<tr> 
					    <td width="894" height="21" bgcolor="00008C">
							<font face="Arial, Helvetica, sans-serif" size="2" color="#FFFFFF"><b>Registrador de Balan&ccedil;os hist&oacute;ricos</b></font></td>
				  </tr>
				</table>
		      <table border='0' width="100%" cellpadding="2" cellspacing="0">
        			<tr bgcolor=""> 
			          <td><img src="Logotipo-Engrenagens.png"/>&nbsp;</td>
			          <td>	<input name="id_lan" type="hidden" id="id_lan" value="0">
							<input name="limite" type="hidden" id="limite" value="<?php echo $total_row?>">
							<br>
							Total de usuários LH: <?php echo $total_row?><br>
							Total de balanços cadastrados: <?php echo $balanco_total?> (Datas entre '<?php echo $balanco_data_min ?>' e '<?php echo $balanco_data_max ?>')
					 </td>
		        </tr>
        			<tr bgcolor="">
        			  <td colspan="2"><p>Esta ferramenta cria um hist&oacute;rico de balanco desde a data de hoje at&eacute; a abertura da Lan House, utiliza como base, o calculo das vendas e pagamentos de boleto.</p>
     			     <p>Ela oferece duas formas de gerar balan&ccedil;o, individual ou para todas.</p>
     			     <p><strong>Gerar balan&ccedil;o individual</strong>: deve se inserir o c&oacute;digo da Lanhouse no campo de texto e clicar no bot&atilde;o &quot; gerar balanco individual &quot; o programa ir&aacute; construir todos os balancos anteriores &agrave; data de hoje at&eacute; a data de cria&ccedil;&atilde;o apenas para aLanhouse que foi fornecido o c&oacute;digo.</p>
     			     <p><strong>Gerar balan&ccedil;o para todos os PDV's</strong>: Clicar no bot&atilde;o &quot;Gerar para todas as Lan Houses&quot; o programa ir&aacute; gerar todos os balan&ccedil;os de uma lan house de cada vez automaticamente. Nesta op&ccedil;&atilde;o tambem ser&aacute; exibido o tempo estimado que resta para o termino da opera&ccedil;&atilde;o, a opera&ccedil;&atilde;o poder&aacute; ser cancelada a qualquer momento, porem se for executado novamente, os balancos ser&atilde;o duplicados e nas telas de extratos ir&atilde;o aparecer duas vezes esses registros.</p>
     			     <p><strong>Mais informa&ccedil;&otilde;es:</strong></p>
     			     <p>&Eacute; possivel alterar o intervalo de dias do balan&ccedil;o nas paginas <em>balanco_gera_registros.php</em> e <em>balanco_gera_registros_unitario.php</em> , na variavel $n_dias, por padr&atilde;o esta marcado de <?php echo $n_dias ?> dias. Por&eacute;m s&oacute; ser&aacute; gravado se houver alguma movimenta&ccedil;&atilde;o de venda ou pagamento no intervalo de <?php echo $n_dias ?> dias.</p>
     			     <p>&nbsp;</p>     			     </td>
     			  </tr>
        			<tr bgcolor="">
        			  <td colspan="2">Codigo da Lan
                   <input type="text" name="textfield" id="cod_lan">
                   <input type="submit" name="gera_balanco" id="gera_balanco" value="gerar balanco individual">
                 </td>
     			  </tr>
        			<tr bgcolor="">
        			  <td colspan="2"><?php //<input type="submit" name="gera_total" id="gera_total" value="Gerar para todas Lan Houses"> ?>
					  <a href="balanco_gera_registros.php">balanco_gera_registros</a> de <?php echo $total_row?> registros.
					  </td>
     			  </tr>
        			<tr bgcolor="">
        			  <td colspan="2">
					  <a href="balanco_gera_registros_um_por_lan.php">balanco_gera_registros_um_por_lan</a> de <?php echo $total_row?> registros.
					  </td>
     			  </tr>
 
     			  </tr>
        			<tr bgcolor="">
        			  <td colspan="2">
					  <a href="..\com_pesquisa_extrato_geral_rapida.php" target=~_blank>com_pesquisa_extrato_geral_rapida.php</a>
					  </td>
     			  </tr>
		      </table>            </td>
     	  </tr>
            <tr>
            <td colspan='2' id ='conteudo'>  </td>
            </tr>
		</table>
      
        <script>
		$('#gera_balanco').click(function() {
		roda_registros_individual();
		});
		
//		$('#gera_total').click(function() {
//		roda_registros_total();
//		});
		             </script>
    </td>
      </tr>

      </table>
      </body></html>