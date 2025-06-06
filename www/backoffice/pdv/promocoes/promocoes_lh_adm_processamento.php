<?php require_once __DIR__ . '/../../../includes/constantes_url.php'; ?>
<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
?>
<!--trecho necessário para o calendario com data hora-->
<link rel="stylesheet" type="text/css" href="/css/anytime512.css" />
<!--link rel="stylesheet" type="text/css" href="<?= EPREPAG_URL_HTTP ?>/prepag2/js/jqueryui/css/eprepag/jquery-ui-1.8.16.custom.css" /-->
<script language="JavaScript" src="/js/anytime512.js"></script>
<script language="JavaScript" src="/js/anytimetz.js"></script>
<script language="JavaScript" src="/js/anytimeBR.js"></script>
<!--fim do trecho necessário para o calendario com data hora-->
<link href="/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="/js/global.js"></script>
<script>
    $(function(){
        $("#promolh_r_data_processamento").AnyTime_noPicker().AnyTime_picker({baseYear: 2008,
            earliest: rangeDemoConv.format(new Date(2008,1,1,0,0,0)),
            format: "%d/%m/%Y %H",
            latest: rangeDemoConv.format(new Date(2022,11,31,23,59,59)),
            dayAbbreviations: ['DOM','SEG','TER','QUA','QUI','SEX','SAB'],
            labelDayOfMonth: 'Dia do Mês',
            labelHour: 'Hora',
            labelMinute: 'Minuto',
            labelMonth: 'Mês',
            labelTitle: 'Selecione a Data e Hora',
            labelYear: 'Ano',
            monthAbbreviations: ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez']
        });
    })
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<form id="form1" name="form1" method="post">
    <table class="table txt-preto fontsize-pp">
			<tr>
				<td colspan="4" bgcolor="#DDDDDD">&nbsp;&nbsp;Filtro de Pesquisa</td>
			</tr>
			<tr>
				<td align="right">ID da Promo&ccedil;&atilde;o: </td>
				<td><input name="promolh_id" type="text" id="promolh_id" size="20" maxlength="20" value="<?php if(isset($promolh_id)) echo $promolh_id;?>"/></td>
				<td><div align="right">Nome da LAN House: </div></td>
				<td><input name="ug_nome" type="text" id="ug_nome" size="30" maxlength="50" value="<?php if(isset($promolh_id)) echo $ug_nome;?>"/></td>
			</tr>
			<tr>
				<td align="right">Posi&ccedil;&atilde;o Ranking: </td>
				<td><input name="promolh_r_rank" type="text" id="promolh_r_rank" size="2" maxlength="2" value="<?php if(isset($promolh_r_rank)) echo $promolh_r_rank;?>"/></td>
				<td><div align="right">Estado: </div></td>
				<td><input name="ug_estado" type="text" id="ug_estado" size="2" maxlength="2" value="<?php if(isset($ug_estado)) echo $ug_estado;?>"/></td>
			</tr>
			<tr>
				<td colspan="2"><div align="right">Data Processamento: </div></td>
				<td colspan="2"><input name="promolh_r_data_processamento" type="text" id="promolh_r_data_processamento" size="16" maxlength="16" value="<?php if(isset($promolh_r_data_processamento)) echo $promolh_r_data_processamento;?>"/> DD/MM/YYYY HH24</td>
			</tr>
			<tr>
				<td colspan="6" align="center"><input name="btn_pesquisar" type="submit" id="btn_pesquisar" value="Pesquisar" /></td>
			</tr>
		</table>
</form>
<?php
$msg	= "";

$cReturn = "<br>\n";

$query = "SELECT 
				promolh_id,
				to_char(promolh_r_data_processamento,'DD/MM/YYYY HH24') as promolh_r_data_processamento,
				promolh_r_rank, 
				plr.ug_id,
				(CASE WHEN (ug.ug_tipo_cadastro='PJ') THEN ug.ug_nome_fantasia WHEN (ug.ug_tipo_cadastro='PF') THEN ug.ug_nome END) as ug_nome,
				ug_estado,
				promolh_r_valor
		FROM promocoes_lanhouses_rank plr
			INNER JOIN dist_usuarios_games ug ON (plr.ug_id = ug.ug_id)
		WHERE ";
if(empty($promolh_r_data_processamento)) {
								$query .="to_char(promolh_r_data_processamento,'YYYYMMDDHH24') = (
											select max(to_char(promolh_r_data_processamento,'YYYYMMDDHH24'))
											from promocoes_lanhouses_rank
											)";
}
else {
	$query .= "to_char(promolh_r_data_processamento,'DD/MM/YYYY HH24') = '$promolh_r_data_processamento'";
}
if(!empty($promolh_r_rank)) {
	$query .="	and promolh_r_rank = ".$promolh_r_rank;
}
if(!empty($ug_estado)) {
	$query .="	and ug_estado = '".$ug_estado."'";
}
if(!empty($ug_nome)) {
	$query .="	and (ug_nome_fantasia like '%".strtoupper($ug_nome)."%' or ug_nome like '%".strtoupper($ug_nome)."%')";
}
if(!empty($promolh_id)) {
	$query .="	and promolh_id = ".$promolh_id;
}
$query .="	order by promolh_id,promolh_r_rank";
//echo "query: ".$query."\n";

$rs_query = SQLexecuteQuery($query);
echo "<table class=\"table txt-preto fontsize-pp\">";
//echo "<tr style='font-size:11px;font-weight: bold;'><td colspan='4'>Acompanhe os primeiros colocados</td></tr>";
echo "<tr style='font-size:11px;font-weight: bold;'><td>ID Promo</td><td>Rank</td><td>UF</td><td>LAN</td><td>Valor</td><td><nobr>&Uacute;lt. Processamento</nobr></td></tr>";
while ($promocoes_info = pg_fetch_array($rs_query)) {
	//echo " dentro while"."\n";
	if(($promocoes_info['promolh_r_rank'] % 2) == 1) {
		$aux_bgcolor='#E3F0FF';
	}
	else {
		$aux_bgcolor='#FFFFFF';
	}
	echo "<tr style='font-size:10px;background-color:".$aux_bgcolor.";'>";
	echo "<td align='center'><nobr>".$promocoes_info['promolh_id']."&nbsp;&nbsp;</nobr>";
	echo "</td><td align='center'><nobr>".$promocoes_info['promolh_r_rank'].chr(170)."&nbsp;&nbsp;</nobr>";
	echo "</td><td align='center'><nobr>".$promocoes_info['ug_estado']."&nbsp;&nbsp;</nobr>";
	echo "</td><td><nobr><a href='/pdv/usuarios/com_usuario_detalhe.php?usuario_id=".$promocoes_info['ug_id']."' target='_blank'>".substr($promocoes_info['ug_nome'],0,30)."</a></nobr>";
	echo "</td><td align='right'><nobr>R$ ".number_format($promocoes_info['promolh_r_valor'], 2, ',', '.')."&nbsp;</nobr>";
		
	//echo "</td><td>".($promocoes_info['promolh_r_rank'] % 2);
	
	echo "</td><td align='center'><nobr>".$promocoes_info['promolh_r_data_processamento'].":00&nbsp;&nbsp;</nobr>";
	echo "</td></tr>";
	//echo "<pre>".print_r($promocoes_info,true)."</pre>";
}
echo "</table><br>";

require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</center>
</body>
</html>