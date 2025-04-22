<?php
set_time_limit(3000); 
$time_start = getmicrotime();
$datai = explode('/',$dataini);
$dataini = $datai[2].'-'.$datai[1].'-'.$datai[0].' 00:00:00';

$dataf = explode('/',$datafim);
$datafim = $dataf[2].'-'.$dataf[1].'-'.$dataf[0].' 23:59:59';

// aqui vamos carregar os dados das lans necessarios para serem apresentados no mapa
	$sqlLans = 
			   "
				SELECT (CASE WHEN (ug.ug_tipo_cadastro='PJ') THEN ug.ug_nome_fantasia||' ('||ug.ug_tipo_cadastro||')' 
				WHEN (ug.ug_tipo_cadastro='PF') THEN ug.ug_nome||' ('||ug.ug_tipo_cadastro||')' END) AS ve_nome, 
				ug.ug_cidade AS ve_cidade, ug.ug_estado AS ve_estado, ug.ug_coord_lat, ug.ug_coord_lng, ug.ug_tipo_cadastro, ug.ug_ativo, ug.ug_id AS ve_id, ug.ug_numero, ug.ug_endereco, ug.ug_tipo_end, ug.ug_bairro";
	if ($dd_faturamento =='1'){
		$sqlLans .=  ",sum(vgm.vgm_qtde) AS n, SUM(vgm.vgm_valor * vgm.vgm_qtde) AS vendas , 
				MIN(vg.vg_data_inclusao) AS primeira_venda , MAX(vg.vg_data_inclusao) AS ultima_venda";
	}
	$sqlLans .=  " FROM dist_usuarios_games ug ";
	if ($dd_faturamento =='1'){
		$sqlLans .=  "INNER JOIN tb_dist_venda_games vg ON vg.vg_ug_id = ug.ug_id  
					  INNER JOIN tb_dist_venda_games_modelo vgm ON vgm.vgm_vg_id = vg.vg_id
					WHERE vg.vg_ultimo_status='5' ";
		if(!empty($dataini) && !empty($datafim) && $dataini != '-- 00:00:00' && $datafim != '-- 00:00:00') {
			$sqlLans .=  "AND vg.vg_data_inclusao>='$dataini'  AND vg.vg_data_inclusao<='$datafim'  ";
		}
					
	} else {
		$sqlLans .=  "	  WHERE 1=1 ";
	
	}
	
	if(!empty($estado)) {
		$sqlLans .=  "	AND ug_estado = '".$estado."' ";
	}
	
	if(!empty($cidade)) {
		$sqlLans .=  "	AND ug_cidade = '".$cidade."' ";
	}

	if(!empty($bairro)) {
		$sqlLans .=  "	AND ug_bairro = '".$bairro."' ";
	}

	if ($dd_ongame =='1'){
		$sqlLans .=	"AND lower(ug.ug_ongame) = 's'  ";
	}

	if($ug_ativo >0) {
		$sqlLans .=	"AND ug.ug_ativo = $ug_ativo  ";
	}
	
	if($tipo =='PF' || $tipo == 'PJ') {
		$sqlLans .= "AND ug.ug_tipo_cadastro='$tipo'";
	}
	
	$sqlLans .=	"AND ug.ug_coord_lat != 0
				AND ug.ug_coord_lng != 0
				GROUP BY ug.ug_id, ug.ug_nome_fantasia, ug.ug_nome, ug.ug_cidade, ug.ug_estado, ug.ug_tipo_cadastro, ug.ug_coord_lat, ug.ug_coord_lng, ug.ug_ativo, ug.ug_numero, ug.ug_endereco, ug.ug_tipo_end, ug.ug_bairro
			   ";
	if ($dd_faturamento =='1'){
		$sqlLans .=	"ORDER BY vendas, ve_nome, ve_estado";
	}
	else {
		$sqlLans .=	"ORDER BY ve_nome, ve_estado";
	}
//echo '<br>'.$sqlLans.'<br>';
			//   die;
	$rssLans = SQLexecuteQuery($sqlLans);
	$totLans = pg_num_rows($rssLans);
	$valorMax = -1;

	if($totLans > 0) {
		$contador = 1;
		$vendas_total = 0;
		$n_total = 0;
		while($vlrLans = pg_fetch_array($rssLans)) {
			$pri_venda	     	= date('Y-m-d',strtotime($vlrLans['primeira_venda']));
			$ult_venda	     	= date('Y-m-d',strtotime($vlrLans['ultima_venda']));
			
			// aqui pegamos os dados para serem apresentados no mapa
			$ve_id[]  			= $vlrLans['ve_id'];
			$ve_nome[]  	    = $vlrLans['ve_nome'];
			$ve_cidade		  	= $vlrLans['ve_cidade'];
			$ve_estado		   	= $vlrLans['ve_estado'];
			$ativo[]			= $vlrLans['ug_ativo'];
			if ($vlrLans['ug_tipo_end'] == ""){
				$ug_dados_endereco[]= $vlrLans['ug_endereco'].',&nbsp;'.$vlrLans['ug_numero'].'&nbsp;'.$vlrLans['ug_complemento']." - ".$vlrLans['ug_bairro']." - ".$ve_cidade.', '.$ve_estado;
			}else{
				$ug_dados_endereco[]= $vlrLans['ug_tipo_end'].'&nbsp;'.$vlrLans['ug_endereco'].',&nbsp;'.$vlrLans['ug_numero'].'&nbsp;'.$vlrLans['ug_complemento']." - ".$vlrLans['ug_bairro']." - ".$ve_cidade.', '.$ve_estado;
			}
			//$ug_dados_endereco[]= $ve_cidade.', '.$ve_estado;
			if ($dd_faturamento =='1'){
				$vendas[]	     	= $vlrLans['vendas'];
				$infos[] 			= 'Primeira Venda: '.date('d/m/Y',strtotime($pri_venda)).'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Ultima &nbsp;&nbsp;&nbsp;Venda: '.date('d/m/Y',strtotime($ult_venda));
				if($valorMax <$vlrLans['vendas']) {
					$valorMax = $vlrLans['vendas'];
				}
				$vendas_total += $vlrLans['vendas'];
			}				
			$places[] 			= 'places.push(new google.maps.LatLng('.$vlrLans[ug_coord_lat].', '.$vlrLans[ug_coord_lng].'));';				
			
//				if($contador === 1) {
//					$valorTopo = $vlrLans['vendas'];
//				}
			
			// fim
			
			$contador++;
			$n_total += $vlrLans['n'];

		}
		$valorTopo = $valorMax;
	}
//
$conta = $contador;
if($conta > 0) {
	if($conta == 1) {
		$conta = 2;
	}
	$s_sub = (($conta-1)>1?"s":"");
	echo "<hr>";
	echo "<nobr>Encontrada$s_sub: <b>".($conta-1)."</b> LH$s_sub</nobr><br>";
	echo "<nobr>Vendas: <b>R$".number_format($vendas_total, 2, '.', '.')."</b></nobr><br>";
	echo "<nobr>Total: <b>".$n_total."</b> vendas.</nobr><br>";
}

//echo '<pre>';
//print_r($vendas);
//echo '</pre><hr>';
//echo '<pre>';
//print_r($infos);
//echo '</pre><hr>';
//echo '<pre>';
//print_r($ve_nome);
//echo '</pre><hr>';
//die('stop');
?>
<script language="javascript">
	var ug_ativo = '<?php echo $ug_ativo; ?>';
	var valorTopo = '<?php echo $valorTopo; ?>';
	
	//alert ('ug_ativo: '+ug_ativo);
	//alert ('valorTopo: '+valorTopo);
	 
	// Attaching click events to the buttons
	// Creating a variable that will hold the InfoWindow object
	var infowindow;
	
	// Adding a LatLng object for each city
	var places = [];
	var dados = [];
	var dadoslogin = [];
	var dadosendereco = [];
	var vendas = [];
	var nome = []
	var infos = [];
	var ativos = [];
	var dadosid = [];
	var dadossel = [];
	
	<?php
	for ($i = 0; $i <= count($places); $i++) {
		echo $places[$i]."\n";
		
		echo "dados[".$i."] = \"".$infos[$i]."\";\n";
		echo "dadoslogin[".$i."] = \"".str_replace("'","",$ve_nome[$i])."\";\n";
		echo "dadosendereco[".$i."] = \"".$ug_dados_endereco[$i]."\";\n";
		echo "nome[".$i."] = \"".str_replace("'","",$ve_nome[$i])."\";\n";
		if ($dd_faturamento =='1'){
			echo "vendas[".$i."] = \"".$vendas[$i]."\";\n";
			echo "infos[".$i."] = \"".$infos[$i]."\";\n";
		}
		echo "ativos[".$i."] = \"".$ativo[$i]."\";\n";
		echo "dadosid[".$i."] = \"".$ve_id[$i]."\";\n";
		echo "dadossel['".$ve_id[$i]."'] = 1;\n";
	}
	?>

	function converte(nStr) {
		nStr += '';
		x = nStr.split('.');
		x1 = x[0];
		x2 = x.length > 1 ? '.' + x[1] : '';
		var rgx = /(\d+)(\d{3})/;
		while (rgx.test(x1)) {
			x1 = x1.replace(rgx, '$1' + '.' + '$2');
		}
		return x1 + x2;	
	}	
	
	function selecionarLAN(id,lan) {
		if (dadossel[id] == 1) {
			var auxHTML = new String(document.getElementById("marcado").innerHTML);
			document.getElementById("marcado").innerHTML = auxHTML + "<br><input name=\"chk_ug_id[]\" id=\"chk_ug_id[]\" type=\"checkbox\"  value=\"" + id + "\" > " +id + " - " + lan;
			if (document.getElementById("marcado").innerHTML.length > 10) {
				document.getElementById("botaolimpar").innerHTML = "<input type='submit' name='limpar' id='limpar' value='Limpar Coords'><br><input type='button' name='marcar' id='marcar' value='Marcar/Desmarcar' onclick='javascript:marcar_desmarcar();'>"; 
			}
			dadossel[id] = 2;
		}
	}

	function processa() {
		alert('Current Zoom level is ' + map.getZoom());
		alert('Current center is ' + map.getCenter());
		alert('The current mapType is ' + map.getMapTypeId());
	}
	
	function inicializa() {
		// Creating a reference to the mapDiv
		var mapDiv      = document.getElementById('map');
		var centroform  = document.formFat.centro.value;
		var zoomform    = document.formFat.zoom.value;
		
		if(centroform == '') {
			centroform = '(-13.00, -51.30)';
		}

		if(zoomform == '') {
			zoomform = 5;
		}
		
		function processa(zoomLev, mapCen) {
		  var mapCen = mapCen;
		  var zoomLev = eval(zoomLev);

		  document.formFat.centro.value = mapCen;
		  document.formFat.zoom.value = zoomLev;
		  document.formFat.submit();
		}
		
		document.getElementById('tipo').onchange = function() {
		  var zoomLev =  map.getZoom();
		  var mapCen = map.getCenter();

		  processa(zoomLev,mapCen);		  
		}

		document.getElementById('ug_ativo').onchange = function() {
		  var zoomLev =  map.getZoom();
		  var mapCen = map.getCenter();

		  processa(zoomLev,mapCen);		  
		}

		document.getElementById('buscar').onclick = function() {
		  var zoomLev =  map.getZoom();
		  var mapCen = map.getCenter();
		  
		  processa(zoomLev,mapCen);
		}
		
	
		// Creating a latLng for the center of the map
		var latlng = new google.maps.LatLng(0, -90);
		
		// Creating an object literal containing the properties 
		// we want to pass to the map  
		var options = {
		  center: latlng,
		  zoom: 5,
		  mapTypeId: google.maps.MapTypeId.ROADMAP,
		  streetViewControl: false
		};
		
		// Creating the map
		var map = new google.maps.Map(mapDiv, options);
		
		// Looping through the places array
		
		// aqui definimos o valor topo e o separamos em 7 partes para referencia
		var toposete = Math.ceil((valorTopo/7));	
		var cor = '';
		var index = 0;
		var icon = '';

		//alert(places.length);
		for (var i = 0; i < places.length; i++) {
			// Creating a new marker
			
			if(ativos[i] == '1') {
				icon = 'trianguloA';
			}
			if(ativos[i] == '2') {
				icon = 'markerL';
			}

<?php
	if ($dd_faturamento =='1'){
?>
			if(vendas[i] >= toposete*6 && vendas[i] <= toposete*7) {
				cor = '/images/mapas/'+icon+'1.png';
				index = 7;
			}
			
			if(vendas[i] >= toposete*5 && vendas[i] <= toposete*6) {
				cor = '/images/mapas/'+icon+'2.png';
				index = 6;
			}
			
			if(vendas[i] >= toposete*4 && vendas[i] <= toposete*5) {
				cor = '/images/mapas/'+icon+'3.png';
				index = 5;
			}
			
			if(vendas[i] >= toposete*3 && vendas[i] <= toposete*4) {
				cor = '/images/mapas/'+icon+'4.png';
				index = 4;
			}
			
			if(vendas[i] >= toposete*2 && vendas[i] <= toposete*3) {
				cor = '/images/mapas/'+icon+'5.png';
				index = 3;
			}
			
			if(vendas[i] >= toposete*1 && vendas[i] <= toposete*2) {
				cor = '/images/mapas/'+icon+'6.png';
				index = 2;
			}
			
			if(vendas[i] >= 0 && vendas[i] <= toposete*1) {
				cor = '/images/mapas/'+icon+'7.png';
				index = 1;
			}

			var vendatratada = 'R$ '+converte(vendas[i])+',00';
<?php
	}
	else {
?>
			var i_vendas = 7*Math.ceil(vendas[i]/(toposete*7));
			if(i_vendas>7) i_vendas = 7;
			var i_icone =  i_vendas;
			cor = '/images/mapas/'+icon+i_icone+'.png';
			index = i_vendas;
			var vendatratada = dadoslogin[i];
<?php
	}
?>
			
			var marker = new google.maps.Marker({
				position: places[i],
				map: map,
				title: vendatratada,
				icon: cor, 
				zIndex: index
			});
			
			cor = '';

				
			// Wrapping the event listener inside an anonymous function
			// that we immediately invoke and passes the variable i to.
			(function(i, marker) {
				// Creating the event listener. It now has access to the values of
				// i and marker as they were during its creation
				google.maps.event.addListener(marker, 'click', function() {
				
				if (!infowindow) {
					infowindow = new google.maps.InfoWindow();
				}			
	
				// Setting the content of the InfoWindow
				var info = '';
<?php
	if ($dd_faturamento =='1'){
?>
				var vendatratada = 'R$ '+converte(vendas[i])+',00';
<?php
	}
?>
				
				var status = ativos[i];
				
				if(status == 1) {
					status = 'ATIVO';
				}

				if(status == 2) {
					status = 'INATIVO';
				}								
				
				var content = '<div id="info" onclick=\'javascript:selecionarLAN("' + dadosid[i] + '","' +  nome[i] + '");\'>' +
				'ID: '+dadosid[i]+' LOGIN: '+nome[i]+'<br>' +
				'ENDEREÇO: '+dadosendereco[i]+'<br>' +
<?php
	if ($dd_faturamento =='1'){
?>
				'INFOS: '+infos[i]+'<br>' +
				'VALOR FATURADO: '+vendatratada+'<br>' +
<?php
	}
?>
				'STATUS: '+status +
				'</div>';
	
				infowindow.setContent(content);
				
				// Tying the InfoWindow to the marker
				infowindow.open(map, marker);
			});
			})(i, marker);
		}	
		zoomint = parseInt(zoomform, 10); 
		
		//alert(centroform);
		
		centrostr = (centroform).toString();
		centrostr = centrostr.replace("(", "")
		centrostr = centrostr.replace(")", "")
		
		mapCenF= centrostr.split(", ");
		
		latint = eval(mapCenF[0], 10); 
		lngint = eval(mapCenF[1], 10); 
		
		map.setOptions({
        center: new google.maps.LatLng(latint, lngint),
        zoom: zoomint
      });

/*
		google.maps.event.addDomListener(map, 'click', function(event) {
			var myLatLng = event.latLng;
			var lat = myLatLng.lat();
			var lng = myLatLng.lng();
			alert( 'lat '+ lat + ' lng ' + lng ); 
			}
		);
*/
	}

	var legendaT = '';
	
<?php
	if ($dd_faturamento =='1'){
?>
	var valorTopo = '<?php echo $valorTopo; ?>';
	var toposete = Math.ceil((valorTopo/7));
	
	var valor1 = (converte((toposete*1)));
	var valor2 = (converte((toposete*2)));
	var valor3 = (converte((toposete*3)));
	var valor4 = (converte((toposete*4)));
	var valor5 = (converte((toposete*5)));
	var valor6 = (converte((toposete*6)));
	var valor7 = (converte((toposete*7)));
	var topo   = (converte((valorTopo)));

	var aCores = new Array();
	aCores[1] = "#fe0000";
	aCores[2] = "#f9109f";
	aCores[3] = "#922eff";
	aCores[4] = "#335cf2";
	aCores[5] = "#41be02";
	aCores[6] = "#479001";
	aCores[7] = "#005f01";

	legendaT += '	<table width="100%" border="0" align="center" cellpadding="2" cellspacing="2" style="font-family:Arial, Helvetica, sans-serif;font-size:11px;">';
	legendaT += '            <tr>';
	legendaT += '              <td width="6%" align="center" valign="middle">&nbsp;</td>';
	legendaT += '              <td width="17%" align="center" valign="middle" bgcolor="' + aCores[7] + '">&nbsp;</td>';
	legendaT += '              <td width="39%"><nobr>R$ '+valor1+',00</nobr></td>';
	legendaT += '            </tr>';
	legendaT += '            <tr>';
	legendaT += '              <td align="center" valign="middle">&nbsp;</td>';
	legendaT += '              <td align="center" valign="middle" bgcolor="' + aCores[6] + '">&nbsp;</td>';
	legendaT += '              <td><nobr>R$ '+valor2+',00</nobr></td>';
	legendaT += '            </tr>';
	legendaT += '            <tr>';
	legendaT += '              <td align="center" valign="middle">&nbsp;</td>';
	legendaT += '              <td align="center" valign="middle" bgcolor="' + aCores[5] + '">&nbsp;</td>';
	legendaT += '              <td><nobr>R$ '+valor3+',00</nobr></td>';
	legendaT += '            </tr>';
	legendaT += '            <tr>';
	legendaT += '              <td align="center" valign="middle">&nbsp;</td>';
	legendaT += '              <td align="center" valign="middle" bgcolor="' + aCores[4] + '">&nbsp;</td>';
	legendaT += '              <td><nobr>R$ '+valor4+',00</nobr></td>';
	legendaT += '            </tr>';
	legendaT += '            <tr>';
	legendaT += '              <td align="center" valign="middle">&nbsp;</td>';
	legendaT += '              <td align="center" valign="middle" bgcolor="' + aCores[3] + '">&nbsp;</td>';
	legendaT += '              <td><nobr>R$ '+valor5+',00</nobr></td>';
	legendaT += '            </tr>';
	legendaT += '            <tr>';
	legendaT += '              <td align="center" valign="middle">&nbsp;</td>';
	legendaT += '              <td align="center" valign="middle" bgcolor="' + aCores[2] + '">&nbsp;</td>';
	legendaT += '              <td><nobr>R$ '+valor6+',00</nobr></td>';
	legendaT += '            </tr>';
	legendaT += '            <tr>';
	legendaT += '              <td align="center" valign="middle">&nbsp;</td>';
	legendaT += '              <td align="center" valign="middle" bgcolor="' + aCores[1] + '">&nbsp;</td>';
	legendaT += '              <td><nobr>R$ '+topo+',00</nobr></td>';
	legendaT += '            </tr>';
	legendaT += '            <tr>';
	legendaT += '              <td align="center" valign="middle">&nbsp;</td>';
	legendaT += '              <td align="center" valign="middle" ><img src="/images/mapas/triangulo7.png" /></td>';
	legendaT += '              <td><nobr>ATIVO</nobr></td>';
	legendaT += '            </tr>';
	legendaT += '            <tr>';
	legendaT += '              <td align="center" valign="middle">&nbsp;</td>';
	legendaT += '              <td align="center" valign="middle"><img src="/images/mapas/markerL2.png" /></td>';
	legendaT += '              <td><nobr>INATIVO</nobr></td>';
	legendaT += '            </tr>';		
	legendaT += '          </table></td>';
	legendaT += '      </tr>';
	legendaT += '    </table>';
	
	if(valorTopo > 0) {
		document.getElementById("legendas").innerHTML = legendaT;
	} else {
		document.getElementById("legendas").innerHTML = 'Sem dados solicitados';
	}
<?php
	}
	else {
?>
	legendaT += '	<table width="100%" border="0" align="center" cellpadding="2" cellspacing="2" style="font-family:Arial, Helvetica, sans-serif;font-size:11px;">';
	legendaT += '            <tr>';
	legendaT += '              <td align="center" valign="middle">&nbsp;</td>';
	legendaT += '              <td align="center" valign="middle" ><img src="/images/mapas/triangulo7.png" /></td>';
	legendaT += '              <td><nobr>ATIVO</nobr></td>';
	legendaT += '            </tr>';
	legendaT += '            <tr>';
	legendaT += '              <td align="center" valign="middle">&nbsp;</td>';
	legendaT += '              <td align="center" valign="middle"><img src="/images/mapas/markerL2.png" /></td>';
	legendaT += '              <td><nobr>INATIVO</nobr></td>';
	legendaT += '            </tr>';		
	legendaT += '          </table></td>';
	legendaT += '      </tr>';
	legendaT += '    </table>';
	document.getElementById("legendas").innerHTML = legendaT;
<?php
	}
?>
	
</script>
<?php  echo '<br>Processado: '. number_format(getmicrotime() - $time_start, 2, '.', '.') . ' sec.' ?>
