<?php
require_once "../../../includes/constantes.php";
set_time_limit(3000);
$time_start = getmicrotime();

//Conectando com PDO para execu��o da QUERY
$con = ConnectionPDO::getConnection();
$pdo = $con->getLink();

$Cidade = filter_var(str_replace("'", "", $Cidade),FILTER_SANITIZE_STRING);
$Bairro = filter_var(str_replace("'", "", $Bairro),FILTER_SANITIZE_STRING);
$Estado = filter_var(trim($Estado),FILTER_SANITIZE_STRING);
// aqui vamos carregar os dados das lans necessarios para serem apresentados no mapa
$sqlLans = "			
SELECT 
	tipo,
	ve_nome, 
	ve_cidade, 
	ve_estado, 
	ug_coord_lat, 
	ug_coord_lng, 
	ug_tipo_cadastro, 
	ug_ativo, 
	ve_id, 
	ug_numero, 
	ug_endereco,
        ug_complemento,
	ug_tipo_end, 
	ug_bairro
FROM (

	(SELECT 
		'L' as tipo,
		(CASE WHEN (ug.ug_tipo_cadastro='PJ') THEN ug.ug_nome_fantasia WHEN (ug.ug_tipo_cadastro='PF') THEN ug.ug_nome END) AS ve_nome, 
		ug.ug_cidade AS ve_cidade, 
		ug.ug_estado AS ve_estado, 
		ug.ug_coord_lat, 
		ug.ug_coord_lng, 
		ug.ug_tipo_cadastro, 
		ug.ug_ativo, 
		ug.ug_id AS ve_id, 
		ug.ug_numero, 
                ug.ug_complemento, 
		ug.ug_endereco, 
		ug.ug_tipo_end, 
		ug.ug_bairro
	FROM dist_usuarios_games ug 
	WHERE ug.ug_ativo = 1 
		AND replace(ug_cidade, '\'', '') = :ug_cidade
		AND replace(ug_bairro, '\'', '') = :ug_bairro
		AND ug_estado = :ug_estado
		AND ug.ug_coord_lat != 0
		AND ug.ug_coord_lng != 0
		AND ug.ug_status = 1
	)
) as locais
ORDER BY ve_nome, ve_estado";

$mensagem = date('Y-m-d H:i:s') . " $sqlLans\n";
grava_log_mapa_lhs($mensagem);


$stmt = $pdo->prepare($sqlLans);
$stmt->bindParam(':ug_cidade', $Cidade, PDO::PARAM_STR);
$stmt->bindParam(':ug_estado', $Estado, PDO::PARAM_STR);
$stmt->bindParam(':ug_bairro', $Bairro, PDO::PARAM_STR);
$stmt->execute();
$rssLans = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totLans = count($rssLans);
	
	if($totLans > 0) {
		$contador = 0;
		foreach($rssLans as $vlrLans) {
			
			// aqui pegamos os dados para serem apresentados no mapa
			$ve_id[]  			= $vlrLans['ve_id'];
			$ve_nome[]  	    = $vlrLans['ve_nome'];
			$ve_cidade		  	= $vlrLans['ve_cidade'];
			$ve_estado		   	= $vlrLans['ve_estado'];
			$ativo[]			= $vlrLans['ug_ativo'];
			$tipo[]  			= $vlrLans['tipo'];
			if ($vlrLans['ug_tipo_end'] == ""){
				$ug_dados_endereco[]= $vlrLans['ug_endereco'].',&nbsp;'.$vlrLans['ug_numero'].'&nbsp;'.$vlrLans['ug_complemento']." - ".$vlrLans['ug_bairro']." - ".$ve_cidade.', '.$ve_estado;
			}else{
				$ug_dados_endereco[]= $vlrLans['ug_tipo_end'].'&nbsp;'.$vlrLans['ug_endereco'].',&nbsp;'.$vlrLans['ug_numero'].'&nbsp;'.$vlrLans['ug_complemento']." - ".$vlrLans['ug_bairro']." - ".$ve_cidade.', '.$ve_estado;
			}
			$places[] 			= "places.push(new google.maps.LatLng(".$vlrLans[ug_coord_lat].", ".$vlrLans[ug_coord_lng]."));\n";
			
			// fim
			$contador++;
			$n_total += $vlrLans['n'];

		}
	}
//
$conta = $contador;
if($conta > 0) {
	if($conta == 1) {
		$conta = 2;
	}
	$s_sub = (($contador)>1?"s":"");
	echo "<nobr style='font-family:Arial, Helvetica, sans-serif;font-size:12px;color:#0146A3;font-weight:bold'>Encontrado$s_sub: ".($contador)." Ponto$s_sub de Venda</nobr><br>";
}

?>
<script language="javascript" src="/js/jquery.blockUI.New.js"></script>
<script language="javascript">
	
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
	var tipo = [];
	
	<?php
		for ($i = 0; $i <= count($places); $i++) {
		echo $places[$i];
		
		echo 'dados['.$i.'] = "'.$infos[$i].'";';
		echo 'dadoslogin['.$i.'] = "'.$ve_nome[$i].'";';
		echo 'dadosendereco['.$i.'] = "'.$ug_dados_endereco[$i].'";';
		echo 'nome['.$i.'] = "'.$ve_nome[$i].'";';
		echo 'ativos['.$i.'] = "'.$ativo[$i].'";';
		echo 'tipo['.$i.'] = "'.$tipo[$i].'";';
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
	
	function processa() {
		alert('Current Zoom level is ' + map.getZoom());
		alert('Current center is ' + map.getCenter());
		alert('The current mapType is ' + map.getMapTypeId());
	}

	//Evento Clique no Bal�o
	function exibir() { 
		$.blockUI.defaults.message = 'N�o � permitido selecionar!';
		$.blockUI(); 
		setTimeout($.unblockUI, 2000); 
		$.blockUI.defaults.message = '<img src="../../../prepag2/dist_commerce/images/loading1.gif" alt=""/>';
	} 
	
	function inicializa() {
		// Creating a reference to the mapDiv
		var mapDiv      = document.getElementById('map');
        
		centroform = places[0];
		if (places.length>1)
			zoomform = 14;
		else zoomform = 16;
		
		// Creating an object literal containing the properties 
		// we want to pass to the map  
		var options = {
		  center: centroform,//latlng,
		  zoom: zoomform,
		  backgroundColor: '#F4F3F0',
		  mapTypeId: google.maps.MapTypeId.ROADMAP,
		  streetViewControl: true
		};
		
		// Creating the map
		var map = new google.maps.Map(mapDiv, options);
		
		// Looping through the places array
		
		// aqui definimos o valor topo e o separamos em 7 partes para referencia
		var cor = '';
		var index = 0;
		var icon = '';

		for (var i = 0; i < places.length; i++) {
			// Creating a new marker
			
			if(tipo[i] == 'L') {
				cor = '/imagens/icone_googlemaps_3.png';
			}
			else {
				cor = 'imagens/icone_googlemaps_card.png';
			}
			index = 1;
			
			var marker = new google.maps.Marker({
				position: places[i],
				map: map,
				title: dadoslogin[i],
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

                    var status = ativos[i];

                    if(status == 1) {
                        status = 'ATIVO';
                    }

                    if(status == 2) {
                        status = 'INATIVO';
                    }								

                    var content = '<div id="info" onmousedown="javascript:exibir();">' +
                    'NOME: '+nome[i]+'<br>' +
                    'ENDERE�O: '+dadosendereco[i]+'<br>' +
                    '</div>';

                    infowindow.setContent(content);

                    // Tying the InfoWindow to the marker
                    infowindow.open(map, marker);
                });
			})(i, marker);
		}	
		zoomint = parseInt(zoomform, 10);
		
		centrostr = (centroform).toString();
		centrostr = centrostr.replace("(", "")
		centrostr = centrostr.replace(")", "")
		
		mapCenF= centrostr.split(", ");
		//alert(mapCenF);
		latint = eval(mapCenF[0], 10); 
		lngint = eval(mapCenF[1], 10); 
		
		map.setOptions({
        center: new google.maps.LatLng(latint, lngint),
        zoom: zoomint
      });		
		
	}

	var legendaT = '';

	document.getElementById("legendas").innerHTML = legendaT;
</script>
<?php  

echo '<span style="font-family:Arial, Helvetica, sans-serif;font-size:11px;">Clique no &iacute;cone para ver o endere&ccedil;o</span>'; 

function grava_log_mapa_lhs($mensagem){
	$ARQUIVO_LOG_HTTP_REFERER = RAIZ_DO_PROJETO . "log/log_mapa_lista_lhs.txt";	

	//Arquivo
	$file = $ARQUIVO_LOG_HTTP_REFERER;

	//Grava mensagem no arquivo
	if ($handle = fopen($file, 'a+')) {
		fwrite($handle, $mensagem);
		fclose($handle);
	} 	
}

?>