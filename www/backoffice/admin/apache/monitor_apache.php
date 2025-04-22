<?php
$pagina_titulo = "E-prepag - Créditos para Games";

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
?>
<link rel="stylesheet" href="/css/css_frame.css" type="text/css">
<script language="javascript" src="/js/jquery.js"></script>
<script type="text/javascript">
function abre_venda(vg_canal, vg_id){
	alert('Canal: '+vg_canal+', vg_id:'+vg_id);
}
</script>

<script language="JavaScript" type="text/JavaScript">
	//Função para buscar o endereço.
	function refresh_status(tipo){
		//função para verificar se o objeto DOM do javascript está pronto.
		$(document).ready(function(){
			$.ajax({
				type: "POST",
				url: "monitor_ajax.php",
				data: "tipo="+tipo,
				success: function(txt){
					if (txt != "ERRO") {
						var txt0="????";
						if(tipo=='M') {
							if(txt.length>0) {
								$("#monitor_agora").html(txt);	// " ("+txt.length+")<br>"+  // +'<br>\n'+trans1

							}
						} else if(tipo=='L') {
							if(txt.length>0) {
								$("#numero_requisicoes").html(txt);	// " ("+txt.length+")<br>"+ // +'<br>\n'+trans1
							}
						} else {
							$("#monitor_agora").html("ERROR, tipo="+tipo);
							$("#numero_requisicoes").html("ERROR, tipo="+tipo);
						}
					} else {
						alert("ERROR");
					}					
				},
				error: function(){
					$("#monitor_agora").html("???");
					$("#numero_requisicoes").html("???");
				}
			});
		});
	}

	// Hide both marks
	$(document).ready(function(){
		$("#monitor_agora_mark").hide();
		$("#numero_requisicoes_mark").hide();
		var i = refresh_status('M');
		i = refresh_status('L');

	});

	setInterval(function(){refresh_status('M');}, 20000);
	setInterval(function(){refresh_status('L');}, 20000);

	function fade_out_mark(mark_name) {
		$(document).ready(function(){
			$("#"+mark_name).fadeOut("slow");
		});
	}

	function limit_to_nlines(txt, n) {
		var trans = txt.split("<br>\n");
		var trans1 = "";
		var nmax = n;
		if(nmax>trans.length) nmax = trans.length;
		for(var i = 0; i < nmax; i++){
			trans1 += trans[i]+"<br>\n"; 
		}
		// Limit to 2K maximum length
		return trans1.substr(0,1024);
	}

</SCRIPT>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><?php echo $sistema->item->getDescricao(); ?></li>
    </ol>
</div>
<table class="table txt-preto fontsize-pp top10">
  <tr> 
      <td colspan="5" class="txt-azul-claro"><h4>Monitor</h4></td>
    </tr>
    <tr> 
      <td width="22" align="center" bgcolor="#ECE9D8"> <div align="center"></div></td>
      <td width="356" align="center" bgcolor="#ECE9D8"><div align="left"><nobr><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Tempo Real</font><font color="#FF0000" size="2" face="Arial, Helvetica, sans-serif">&nbsp;<span id="monitor_agora_mark">Nova&nbsp;transação <span id="monitor_agora_mark_length">*</span></span></font></nobr></div></td>
      <td width="22" align="center" bgcolor="#ECE9D8"><div align="center"></div></td>
      <td width="356" align="center" bgcolor="#ECE9D8"><div align="left"><nobr><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Acumulados</font>&nbsp;<span id="numero_requisicoes_mark"><font color="#FF0000" size="2" face="Arial, Helvetica, sans-serif">Nova&nbsp;transação</font></span></nobr></div></td>
      <td width="22" align="center" bgcolor="#ECE9D8"></td>
    </tr>
    <tr height="300">
        <td align="center"></td>
        <td valign="top">
            <font size="1" face="Arial, Helvetica, sans-serif">
            <div id="monitor_agora"></div>
            </font>
        </td>
      <td align="center"></td>
        <td valign="top">
            <font size="1" face="Arial, Helvetica, sans-serif">
            <div id="numero_requisicoes"></div>
            </font>
        </td>
      <td align="center"></td>
    </tr>
    <tr> 
      <td><div align="right"></div></td>
      <td colspan="3" align="center">&nbsp;<font size="1" face="Arial, Helvetica, sans-serif"><div id="info_send"></div></font></td>
      <td  align="center"></td>
    </tr>
</table>
<?php 
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</body></html>
