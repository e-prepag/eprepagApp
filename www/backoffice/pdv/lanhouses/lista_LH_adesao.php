<?php 
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
require_once $raiz_do_projeto."includes/pdv/inc_campeonato.php";

	$time_start_stats = getmicrotime();

//echo "<pre>".print_r($_POST,true)."</pre>";

	if(!$vg_id_selected) $vg_id_selected = $_POST['vg_id_selected'];
	if($vg_id_selected) {

		$sql  = "select ug_email from dist_usuarios_games where ug_id = $vg_id_selected";
//echo str_replace("\n", "<br>\n", $sql);	
		$rs_emails = SQLexecuteQuery($sql);
		$rs_emails_row = pg_fetch_array($rs_emails);
		$ug_email = $rs_emails_row['ug_email'];

//echo "Manda email aviso para $vg_id_selected - $ug_email<br>";
//if(!($ug_email=="reinaldo@e-prepag.com.br" || $ug_email == "joao.trevisan@e-prepag.com.br")) {
//$ug_email = "reinaldo@e-prepag.com.br";
//$ug_email_bcc = "joao.trevisan@e-prepag.com.br";
//} else {
//$ug_email = "reinaldo@e-prepag.com.br";
//$ug_email_bcc = "reinaldo@e-prepag.com.br, joao.trevisan@e-prepag.com.br";
//}

echo "Manda email aviso para $vg_id_selected - $ug_email<br>";

		manda_email_campeonato($vg_id_selected, $ug_email, $ug_email_bcc);

	}

	$sql  = "select * from dist_usuarios_games ";
	$sql  .= "where upper(ug_compet_participa)='S' ";
	$sql  .= "order by ug_data_inclusao desc";
//echo str_replace("\n", "<br>\n", $sql);	

	$rs_logins = SQLexecuteQuery($sql);
	$n_lans = pg_num_rows($rs_logins);
?>
<style>
.botao_avisa_pendente {
	font-family: Arial, Helvetica, sans-serif; 
	font-size: 12px; 
	color: red; 
	background-color: #A6A6A6; 
	border: none; 
	text-transform: none; 
	font-weight: bold; 
}
.botao_avisa_cadastrado {
	font-family: Arial, Helvetica, sans-serif; 
	font-size: 12px; 
	color: gray; 
	background-color: #A6A6A6; 
	border: none; 
	text-transform: none; 
	font-weight: bold; 
}
</style>
<script language="javascript">
function avisa_lh(vg_id, b_aceito) {
//alert("Avisa "+vg_id");  
	var answer = 0;
	if(b_aceito) {
		answer = confirm("Este usuário já confirmou, \nquer enviar email de confirmação novamente?");
	} else {
		answer = 1;
	}
	if(answer) {
		document.getElementById('vg_id_selected').value = vg_id;
		document.form1.submit();
	}
}
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<table class="txt-preto top10 bg-branco fontsize-p">
  <tr> 
      <td class="pull-right"> 
	<form name="form1" method=post action="lista_LH_adesao.php">
		<input type="hidden" id="vg_id_selected" name="vg_id_selected" value="">
        <input type="submit" class="btn btn-info" value="Atualiza">
	 </form>
  </td>
  </tr>
  <tr> 

  <td width="100%" valign="top">
	<?php
	
	if($rs_logins && pg_num_rows($rs_logins) > 0){
		echo "<p>Lista de Lans cadastradas para participar no Campeonato</p>";
		echo "<p>Na coluna da direita os botões aparecem em vermelho para as Lans que ainda não aceitaram os Termos e a coluna 'Aceito?' indica isso.</p>";
		echo "<p>Total de PDVs encontrados: $n_lans</p>\n";
		echo "<table border='0' cellpadding='2' cellspacing='2' bordercolor='#cccccc'>\n";
		$scol1 = "#CCFFCC";
		$scol2 = "#FFFFFF";
		$scol = $scol1;
		$i_row = 1;

		echo "<tr bgcolor='#99CCFF' align='center'>
				<td width='5%'><font color='#000000'><b>i</b></font></td>
				<td align='center' width='5%'><font color='#00000'><b>ug_id</b></font></td>
				<td align='center' width='5%'><font color='#00000'><b>login</b></font></td>
				<td align='center' width='5%'><font color='#00000'><b>nome</b></font></td>
				<td align='center' width='5%'><font color='#00000'><b>email</b></font></td>

				<td align='center' width='5%'><font color='#00000'><b>participa?</b></font></td>
				<td align='center' width='5%'><font color='#00000'><b>promoveu?</b></font></td>
				<td align='center' width='5%'><font color='#00000'><b>participantesfifa</b></font></td>
				<td align='center' width='5%'><font color='#00000'><b>dataaceito</b></font></td>

				<td align='center' width='20%'><font color='#00000'><b>endereço</b></font></td>

				<td align='center' width='20%'><font color='#00000'><b>Aceito?</b></font></td>
				<td align='center' width='20%'><font color='#00000'><b></b></font></td>
				</tr>\n";

		while($rs_logins_row = pg_fetch_array($rs_logins)) {

		//	ug_id, ug_login, ug_responsavel, ug_email, 
		//	ug_compet_participa, ug_compet_promoveu, ug_compet_participantes_fifa, ug_compet_aceito_data_aceito, 
		//	ug_bairro, ug_cidade, ug_estado, ug_cep, 
		echo "<tr bgcolor='".$scol."' align='center'>
				<td><font color='#000000'><b>".($i_row++)."</b></font></td>
				<td align='center'><font color='#00000'>".$rs_logins_row['ug_id']."</font></td>
				<td align='center'><font color='#00000'>".$rs_logins_row['ug_login']."</font></td>
				<td align='center'><font color='#00000'>".$rs_logins_row['ug_responsavel']."</font></td>
				<td align='center'><font color='#00000'>".$rs_logins_row['ug_email']."</font></td>

				<td align='center'><font color='#00000'>". ((strtoupper($rs_logins_row['ug_compet_participa'])=='S')?"Sim":"não"). "</font></td>
				<td align='center'><font color='#00000'>". ((strtoupper($rs_logins_row['ug_compet_promoveu'])=='S')?"Sim":"não"). "</font></td>
				<td align='center'><font color='#00000'>".$rs_logins_row['ug_compet_participantes_fifa']."</font></td>
				<td align='center'><font color='#00000'>".substr($rs_logins_row['ug_compet_aceito_data_aceito'], 0, 19)."</font></td>

				<td align='center' width='20%'><font color='#00000'><div width='200pt'>".$rs_logins_row['ug_endereco'].", ".$rs_logins_row['ug_numero'].", ".$rs_logins_row['ug_complemento'].", ".$rs_logins_row['ug_bairro'].", ".$rs_logins_row['ug_cidade']." - ".$rs_logins_row['ug_estado']." (CEP: ".$rs_logins_row['ug_cep'].")</div></font></td>

				<td align='center' title='Data aceito ".substr($rs_logins_row['ug_compet_aceito_data_aceito'], 0, 19) ."'><font color='#00000'>". ((strlen($rs_logins_row['ug_compet_aceito_data_aceito'])>0)?"Sim":"não"). "</font></td>
				<td align='center'><input type='button' name='btn_Avisa_".$rs_logins_row['ug_id']."' value='Avisa id: ".$rs_logins_row['ug_id']."' onclick='avisa_lh(".$rs_logins_row['ug_id'].", ". ((strlen($rs_logins_row['ug_compet_aceito_data_aceito'])>0)?1:0). ")' class='". ((strlen($rs_logins_row['ug_compet_aceito_data_aceito'])>0) ? "botao_avisa_cadastrado" : "botao_avisa_pendente"). "'></td>

				</tr>\n";

			$scol = ($scol==$scol1)?$scol2:$scol1;

		}

		echo "</table>\n";

	} else {
		echo "<p><font color='#FF0000'><b>Erro ao procurar PDVs cadastrados</b></font></p>\n";
	}

	?>
		</td>
      </tr>
    </table>
<?php 
	echo "<p><font color='#000000'>Elapsed time: ".number_format(getmicrotime() - $time_start_stats, 2, ',', '.')."s</font></p>";
?>
<?php require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php"; ?>
</body></html>
