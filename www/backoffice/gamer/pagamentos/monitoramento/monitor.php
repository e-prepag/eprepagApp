<?php
require_once '../../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/main.php";
//if(b_IsUsuarioWagner())  {
?>
<link rel="stylesheet" href="/css/css.css" type="text/css">
<script language="javascript" src="/js/jquery.js"></script>
<script type="text/javascript">
function abre_venda(vg_canal, vg_id){
	alert('Canal: '+vg_canal+', vg_id:'+vg_id);
}
</script>

<script language="JavaScript" type="text/JavaScript">
<!--
		
	//Função para buscar o endereço.
	function refresh_status(tipo){
		//função para verificar se o objeto DOM do javascript está pronto.
		$(document).ready(function(){
			$.ajax({
				type: "POST",
				url: "/ajax/pagamento/monitor_ajax.php",
				data: "tipo="+tipo,
//				beforeSend: function(){
////					$("#info_send").html("Aguarde... Consultando ("+tipo+")");
//					if(tipo=='M') {
//						$("#monitor_money").html("Waiting...");	
//					} else if(tipo=='L') {
//						$("#monitor_lanhouses").html("Waiting...");	
//					} else {
//						$("#monitor_money").html("ERROR, tipo="+tipo);
//						$("#monitor_lanhouses").html("ERROR, tipo="+tipo);
//					}
//				},
				success: function(txt){
//					txt = txt.replace(/ /g, '*');
					if (txt != "ERRO") {
						var txt0="????";
						if(tipo=='M') {
							if(txt.length>0) {
//								txt0 = $("#monitor_money").html();
//								var trans1 = limit_to_nlines(txt0, 10);
								$("#monitor_money").html(txt);	// " ("+txt.length+")<br>"+  // +'<br>\n'+trans1

								// mark the new transaction
//								var slen = $("#monitor_money").html();
//								$("#monitor_money_mark_length").html(" ("+slen.length+")");
//								$("#monitor_money_mark").show();
//								setInterval(function(){fade_out_mark('monitor_money_mark');}, 500);
							}
						} else if(tipo=='L') {
							if(txt.length>0) {
//								txt0 = $("#monitor_lanhouses").html();
//								var trans1 = limit_to_nlines(txt0, 10);
								$("#monitor_lanhouses").html(txt);	// " ("+txt.length+")<br>"+ // +'<br>\n'+trans1

								// mark the new transaction
//								$("#monitor_lanhouses_mark").show();
//								setInterval(function(){fade_out_mark('monitor_lanhouses_mark');}, 500);
							}
						} else {
							$("#monitor_money").html("ERROR, tipo="+tipo);
							$("#monitor_lanhouses").html("ERROR, tipo="+tipo);
						}
					} else {
						//alert("ERROR");
					}					
				},
				error: function(){
					$("#monitor_money").html("???");
					$("#monitor_lanhouses").html("???");
				}
			});
		});
	}

	// Hide both marks
	$(document).ready(function(){
		$("#monitor_money_mark").hide();
		$("#monitor_lanhouses_mark").hide();
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

//--></SCRIPT>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<table class="table txt-preto fontsize-pp top10">
  <tr> 
      <td colspan="5" class="txt-azul-claro"><h4>Monitor</h4></td>
    </tr>
    <tr> 
      <td width="22" align="center" bgcolor="#ECE9D8"> <div align="center"></div></td>
      <td width="356" align="center" bgcolor="#ECE9D8"><div align="left"><nobr><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">Money</font><font color="#FF0000" size="2" face="Arial, Helvetica, sans-serif">&nbsp;<span id="monitor_money_mark">Nova&nbsp;transação <span id="monitor_money_mark_length">*</span></span></font></nobr></div></td>
      <td width="22" align="center" bgcolor="#ECE9D8"><div align="center"></div></td>
      <td width="356" align="center" bgcolor="#ECE9D8"><div align="left"><nobr><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">LanHouses</font>&nbsp;<span id="monitor_lanhouses_mark"><font color="#FF0000" size="2" face="Arial, Helvetica, sans-serif">Nova&nbsp;transação</font></span></nobr></div></td>
      <td width="22" align="center" bgcolor="#ECE9D8"></td>
    </tr>
    <tr height="300">
        <td align="center"></td>
        <td valign="top">
            <font size="1" face="Arial, Helvetica, sans-serif">
            <div id="monitor_money"></div>
            </font>
        </td>
      <td align="center"></td>
        <td valign="top">
            <font size="1" face="Arial, Helvetica, sans-serif">
            <div id="monitor_lanhouses"></div>
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
   <?php require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php"; ?>
    </body></html>
