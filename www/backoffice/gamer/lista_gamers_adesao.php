<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/main.php";

	$time_start_stats = getmicrotime();

	if(!$tf_ug_id_lh_selected) $tf_ug_id_lh_selected = $_POST['tf_ug_id_lh_selected'];
	if(!$tf_ug_id_gamer) $tf_ug_id_gamer = $_POST['tf_ug_id_gamer'];
	if(!$tf_aceito_participar) $tf_aceito_participar = $_POST['tf_aceito_participar'];

	if($tf_ug_id_gamer) {

	echo "Atualiza Gamer ID: $tf_ug_id_gamer ";
	if($tf_aceito_participar) {
		echo "para ".(($tf_aceito_participar=="S")?"SIM":"NÃO")." participar do Campeonato ";
	}
	if($tf_ug_id_lh_selected) {
		echo "no PDV ID: $tf_ug_id_lh_selected<br>";
	} else {
		echo "SEM PDV<br>";
	}

		$sql  = "update usuarios_games set ug_compet_lh_ug_id = ".(($tf_ug_id_lh_selected>0)?$tf_ug_id_lh_selected:0).", ug_compet_aceito_regulamento='".(($tf_aceito_participar=="S")?"S":"N")."' where ug_id = $tf_ug_id_gamer";
//echo str_replace("\n", "<br>\n", $sql);	
		$rs_emails = SQLexecuteQuery($sql);
		$rs_emails_row = pg_fetch_array($rs_emails);
//echo "<br>Bloqueado SQL <br>";
		echo "<br><font color='blue'>Usuário gamer ID: $tf_ug_id_gamer atualizado para ";
		if($tf_aceito_participar) {
			echo "".(($tf_aceito_participar=="S")?"SIM":"NÃO")." participar do Campeonato ";
		}
		if($tf_ug_id_lh_selected) {
			echo " usar PDV ID: $tf_ug_id_lh_selected";
		} else {
			echo " Sem PDV";
		}
		echo "</font><br>";
	}

	// Aceito ug_compet_aceito_regulamento
	// Com LH ug_compet_lh_ug_id
/*	Pago 
		$prod_id = $GLOBALS['CAMPEONATO_PROD_ID'];
		$pagtos_valor = 0;

		$pagtos_n = get_Campeonato_Pagto_Completo($this->ug_id , $prod_id, &$pagtos_valor);

//gravaLog_Temporario("SQL em b_IsGamer_Competicao_Pago($ogpm_id, &$vg_id)".$sql);

		return ($pagtos_n>0);

	$prod_id	= $GLOBALS['CAMPEONATO_PROD_ID'];
	$opr_codigo = $GLOBALS['CAMPEONATO_OPR_ID'];
	
	$pagtos_valor = 0;
//echo "prod_id: ".$prod_id."<br>";

	$sql = "select sum(vgm_qtde) as n_vendas, sum(vgm_valor) as valor_total
			from tb_venda_games vg 
				inner join tb_venda_games_modelo vgm on vg.vg_id = vgm.vgm_vg_id 
			where 1=1
				and vgm_opr_codigo = $opr_codigo
				and vgm_ogp_id = $prod_id 
				and vg_ug_id = $ug_id
				and vg_ultimo_status = 5
			group by vgm_opr_codigo, vg_ug_id, vgm_ogp_id, vgm_valor";


*/

	$prod_id	= $GLOBALS['CAMPEONATO_PROD_ID'];
	$opr_codigo = $GLOBALS['CAMPEONATO_OPR_ID'];

	$sql   = "select ug.*, ";
	$sql   .= "coalesce((select sum(vgm_qtde) as n_vendas
			from tb_venda_games vg 
				inner join tb_venda_games_modelo vgm on vg.vg_id = vgm.vgm_vg_id 
			where 1=1
				and vgm_opr_codigo = $opr_codigo
				and vgm_ogp_id = $prod_id 
				and vg_ug_id = ug.ug_id
				and vg_ultimo_status = 5
			group by vgm_opr_codigo, vg_ug_id, vgm_ogp_id), 0) as pagtos_n ";
	$sql  .= "from usuarios_games ug ";
	$sql  .= "where upper(ug_compet_aceito_regulamento)='S' ";
	$sql  .= "order by ug_data_inclusao desc";
//echo str_replace("\n", "<br>\n", $sql);	
//die("<br>Stop");

	$rs_logins = SQLexecuteQuery($sql);
	$n_gamers = pg_num_rows($rs_logins);
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
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<center>
<table class="table txt-preto fontsize-pp">
    <tr> 
        <td> 
          <form name="form1" method=post action="lista_gamers_adesao.php">
              Modificar o Gamer código: 
              <input type="text" id="tf_ug_id_gamer" name="tf_ug_id_gamer" value="<?php echo $tf_ug_id_gamer ?>"> 
              para (aceito 
              <select name="tf_aceito_participar" id="tf_aceito_participar">
                  <option value="S"<?php echo (($tf_aceito_participar=="S")?" selected":"")?>>Sim</option>
                  <option value="N"<?php echo (($tf_aceito_participar!="S")?" selected":"")?>>Não</option>
              </select> 
              participar) usando a Lan house código: 
              <input type="text" id="tf_ug_id_lh_selected" name="tf_ug_id_lh_selected" value="<?php echo $tf_ug_id_lh_selected ?>">
              <input type="submit" class="btn btn-sm btn-info" value="Modifica cadastro do Gamer">
           </form>
        </td>
    </tr>
    <tr> 
        <td> 
            <form name="form1" method=post action="lista_gamers_adesao.php">
                <input type="submit" class="btn btn-sm btn-info" value="Atualiza Lista">
             </form>
        </td>
    </tr>
    <tr> 

    <td> 
        <div align="left">
<?php
	if($rs_logins && pg_num_rows($rs_logins) > 0){
		echo "<p>Lista de Gamers cadastrados para participar no Campeonato</p>";
		echo "<p>Total encontrado: $n_gamers</p>\n";
		echo "<table border='0' cellpadding='2' cellspacing='2' bordercolor='#cccccc' style='border-collapse:collapse;'>\n";
		$scol1 = "#CCFFCC";
		$scol2 = "#FFFFFF";
		$scol = $scol1;
		$i_row = 1;

		echo "<tr bgcolor='#99CCFF' align='center'>
				<td width='5%'><font color='#000000' size='1' face='Arial, Helvetica, sans-serif'>&nbsp;<b>i</b>&nbsp;</font></td>
				<td align='center' width='5%'><font color='#00000' size='1' face='Arial, Helvetica, sans-serif'>&nbsp;<b>ug_id</b>&nbsp;</font></td>
				<td align='center' width='5%'><font color='#00000' size='1' face='Arial, Helvetica, sans-serif'>&nbsp;<b>nome</b>&nbsp;</font></td>
				<td align='center' width='5%'><font color='#00000' size='1' face='Arial, Helvetica, sans-serif'>&nbsp;<b>email</b>&nbsp;</font></td>

				<td align='center' width='5%'><font color='#00000' size='1' face='Arial, Helvetica, sans-serif'>&nbsp;<b>participa?</b>&nbsp;</font></td>

				<td align='center' width='20%'><font color='#00000' size='1' face='Arial, Helvetica, sans-serif'>&nbsp;<b>endereço</b>&nbsp;</font></td>

				<td align='center' width='20%'><font color='#00000' size='1' face='Arial, Helvetica, sans-serif'>&nbsp;<b>Lan house escolhida</b>&nbsp;</font></td>
				<td align='center' width='5%'><font color='#00000' size='1' face='Arial, Helvetica, sans-serif'>&nbsp;<b>pagou?</b>&nbsp;</font></td>
				</tr>\n";

		while($rs_logins_row = pg_fetch_array($rs_logins)) {

		//	ug_id, ug_login, ug_responsavel, ug_email, 
		//	ug_compet_participa, ug_compet_promoveu, ug_compet_participantes_fifa, ug_compet_aceito_data_aceito, 
		//	ug_bairro, ug_cidade, ug_estado, ug_cep, 
		echo "<tr bgcolor='".$scol."' align='center'>
				<td><font color='#000000' size='1' face='Arial, Helvetica, sans-serif'>&nbsp;<b>".($i_row++)."</b>&nbsp;</font></td>
				<td align='center'><font color='#00000' size='1' face='Arial, Helvetica, sans-serif'><a href='/gamer/usuarios/com_usuario_detalhe.php?usuario_id=".$rs_logins_row['ug_id']."' target='_blank'>".$rs_logins_row['ug_id']."</a></font></td>
				<td align='center'><font color='#00000' size='1' face='Arial, Helvetica, sans-serif'>".$rs_logins_row['ug_nome']."</font></td>
				<td align='center'><font color='#00000' size='1' face='Arial, Helvetica, sans-serif'>".$rs_logins_row['ug_email']."</font></td>

				<td align='center'><font color='#00000' size='1' face='Arial, Helvetica, sans-serif'>". ((strtoupper($rs_logins_row['ug_compet_aceito_regulamento'])=='S')?"Sim":"não"). "</font></td>

				<td align='center' width='20%'><font color='#00000' size='1' face='Arial, Helvetica, sans-serif'><div width='200pt'>".$rs_logins_row['ug_endereco'].(($rs_logins_row['ug_endereco'])?", ":"").$rs_logins_row['ug_numero'].(($rs_logins_row['ug_numero'])?", ":"").$rs_logins_row['ug_complemento'].(($rs_logins_row['ug_complemento'])?", ":"").$rs_logins_row['ug_bairro'].(($rs_logins_row['ug_bairro'])?", ":"").$rs_logins_row['ug_cidade']." - ".$rs_logins_row['ug_estado']." <br>(CEP: ".$rs_logins_row['ug_cep'].") <br>(tel: ".$rs_logins_row['ug_tel_ddd']." - ".$rs_logins_row['ug_tel'].")</div></font></td>

				<td align='center'><font color='#00000' size='1' face='Arial, Helvetica, sans-serif'>".(($rs_logins_row['ug_compet_lh_ug_id']>0)?"<a href='/gamer/usuarios/com_usuario_detalhe.php?usuario_id=".$rs_logins_row['ug_compet_lh_ug_id']."' target='_blank'>":"").$rs_logins_row['ug_compet_lh_ug_id'].(($rs_logins_row['ug_compet_lh_ug_id']>0)?"</a>":"")."</font></td>

				<td align='center'><font color='#00000' size='1' face='Arial, Helvetica, sans-serif'>".(($rs_logins_row['pagtos_n']>0)?"SIM":"NÃO")."</font></td>
			</tr>\n";

			$scol = ($scol==$scol1)?$scol2:$scol1;

		}

		echo "</table>\n";

	} else {
		echo "<p><font color='#FF0000'><b>Erro ao procurar PDVs cadastrados</b></font></p>\n";
	}

	?>
            </div>
        </td>
    </tr>
</table>
</center>
<?php 
	echo "<p><font color='#000000' size='1' face='Arial, Helvetica, sans-serif'>Elapsed time: ".number_format(getmicrotime() - $time_start_stats, 2, ',', '.')."s</font></p>";
?>
<?php require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php"; ?>
</body></html>
