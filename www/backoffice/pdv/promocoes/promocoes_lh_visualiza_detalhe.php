<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
?>
<?php
$msg	= "";
exibe_ranking();

// função que monta o ranking
function exibe_ranking() {
	$cReturn = "<br>\n";
	
	// busca as promoções vigentes
	$query = "select promolh_id,
					promolh_titulo_tabela,
					promolh_regulamento,
					promolh_banner,
					promolh_link_download
				from promocoes_lanhouses 
				where promolh_data_inicio <= NOW() 
					  and (promolh_data_fim + interval '1 day') >= NOW()
				order by promolh_id";
	//echo "query: ".$query."\n";

	$rs_query = SQLexecuteQUERY($query);

	// Obter apenas a lista de promoções vigentes
	$a_promolh_id = array();
	$i = 0;
	while ($promocoes_info = pg_fetch_array($rs_query)) {
		$a_promolh_id[$i]['ID']						= $promocoes_info['promolh_id'];
		$a_promolh_id[$i]['titulo_tabela']			= $promocoes_info['promolh_titulo_tabela'];
		$a_promolh_id[$i]['promolh_regulamento']	= $promocoes_info['promolh_regulamento'];
		$a_promolh_id[$i]['promolh_banner']			= $promocoes_info['promolh_banner'];
		$a_promolh_id[$i]['promolh_link_download']	= $promocoes_info['promolh_link_download'];
		$i++;
	}
        
        if($i == 0){
            echo '<div class="alert alert-warning text-center" role="alert">Nenhuma promoção vigente no momento.</div>';
        }

	//echo "count(a_promolh_id): ".count($a_promolh_id)." Promoções cadastradas vigentes"."\n";
	//echo "<pre>".print_r($a_promolh_id,true)."</pre>";

	for($i=0;$i<count($a_promolh_id);$i++) {

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
				WHERE promolh_id = ".$a_promolh_id[$i]['ID']."
					and to_char(promolh_r_data_processamento,'YYYYMMDDHH24') = (
													select max(to_char(promolh_r_data_processamento,'YYYYMMDDHH24'))
													from promocoes_lanhouses_rank
													)
				order by promolh_r_rank";
		//echo "query: ".$query."\n";

		$rs_query = SQLexecuteQuery($query);
		echo "<br><table class='table'>";
		echo "<tr style='color:blue;font-size:15px;font-weight: bold;'><td colspan='2' align='left'>".$a_promolh_id[$i]['titulo_tabela']."</td></tr>";
		echo "<tr><td valign='top'><table style='font-family:arial;'>";
		echo "<tr style='font-size:11px;font-weight: bold;'><td colspan='4'>Acompanhe os primeiros colocados</td></tr>";
		echo "<tr style='font-size:11px;font-weight: bold;'><td>Rank</td><td>UF</td><td>LAN</td><td>Valor</td><td><nobr>Dif. entre pos.</nobr></td><td><nobr>&Uacute;lt. Processamento</nobr></td></tr>";
		while ($promocoes_info = pg_fetch_array($rs_query)) {
			//echo " dentro while"."\n";
			if(($promocoes_info['promolh_r_rank'] % 2) == 1) {
				$aux_bgcolor='#E3F0FF';
			}
			else {
				$aux_bgcolor='#FFFFFF';
			}
			echo "<tr style='font-size:10px;background-color:".$aux_bgcolor.";'><td align='center'><nobr>".$promocoes_info['promolh_r_rank'].chr(170)."&nbsp;&nbsp;</nobr>";
			echo "</td><td align='center'><nobr>".$promocoes_info['ug_estado']."&nbsp;&nbsp;</nobr>";
			echo "</td><td><nobr>".substr($promocoes_info['ug_nome'],0,30)."</nobr>";
			echo "</td><td align='right'><nobr>R$ ".number_format($promocoes_info['promolh_r_valor'], 2, ',', '.')."&nbsp;</nobr>";
				
			//echo "</td><td>".($promocoes_info['promolh_r_rank'] % 2);
			if(!empty($vlr_anterior)) {
				if (($vlr_anterior-$promocoes_info['promolh_r_valor'])==0){
						echo "</td><td align='center'><nobr>Empatada com ".($promocoes_info['promolh_r_rank']-1).chr(170)."&nbsp;</nobr>";
					}
					else {
						echo "</td><td align='center'><nobr>R$ ".number_format(($vlr_anterior-$promocoes_info['promolh_r_valor']), 2, ',', '.')." da ".($promocoes_info['promolh_r_rank']-1).chr(170)."&nbsp;</nobr>";
					}
			}
			else {
				echo "</td><td align='center'><nobr>A 1".chr(170)." Colocada&nbsp;</nobr>";
			}
			
			$vlr_anterior = $promocoes_info['promolh_r_valor'];
			echo "</td><td align='center'><nobr>".$promocoes_info['promolh_r_data_processamento'].":00&nbsp;&nbsp;</nobr>";
			echo "</td></tr>";
			//echo "<pre>".print_r($promocoes_info,true)."</pre>";
		}
		echo "<tr style='font-size:11px;color:red;font-family:verdana;'><td colspan='4' align='left'>Se voc&ecirc; n&atilde;o est&aacute; na lista acima &eacute; porque sua coloca&ccedil;&atilde;o est&aacute; abaixo da 20".chr(170).".</td></tr>";
		echo "</table></td>";
		$pasta = "https://".$_SERVER['SERVER_NAME']."/imagens/pdv/promocoes/";
		$pastadwl = "https://".$_SERVER['SERVER_NAME']."/imagens/pdv/";
		echo "<td style='font-size:10px;font-family:arial;'><center><img src='".$pasta.$a_promolh_id[$i]['promolh_banner']."' alt='Banner desta Promo&ccedil;&atilde;o' border='0' align='absmiddle' /><br>";
		echo "<div style='width: 92%;' align='left'>".$a_promolh_id[$i]['promolh_regulamento']."<hr></div><br>";
		echo "<a href='".$a_promolh_id[$i]['promolh_link_download']."' target='_blank'><img src='".$pastadwl."bt_download.jpg' alt='Banner desta Promo&ccedil;&atilde;o' border='0' align='absmiddle' /></a></center></td>";
		echo "</tr></table><br>";
		$vlr_anterior = 0;
	}
}// end function

require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</body>
</html>