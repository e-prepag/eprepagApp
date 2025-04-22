<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";

$ug_id = $_GET['ug_id']; 
$ug_endereco = $_GET['endereco']; 
$ug_cep = $_GET['ug_cep'];

if(!empty($ug_endereco)) {
	
	$sql = "select * from dist_usuarios_games where ug_id=$ug_id;";

	$rs = SQLexecuteQuery($sql);
	$rs_row = pg_fetch_array($rs);

	$ug_endereco = $rs_row['ug_endereco'];
	$ug_numero = $rs_row['ug_numero'];
	$ug_complemento = $rs_row['ug_complemento'];
	$ug_bairro = $rs_row['ug_bairro'];
	$ug_cidade = $rs_row['ug_cidade'];
	$ug_estado = $rs_row['ug_estado'];
	$ug_cep = $rs_row['ug_cep'];

	$ug_coord_lat = $rs_row['ug_coord_lat'];
	$ug_coord_lng  = $rs_row['ug_coord_lng'];
	$ug_google_maps_status = $rs_row['ug_google_maps_status'];

	//$ug_endereco = "";
	if($ug_numero != '') {
		if($ug_tipo_end == '') {
			$ug_endereco .= ', '.$ug_numero.', '.$ug_cidade.', '.$ug_estado;
		} else {
			$ug_endereco .= $ug_tipo_end.', '.$ug_endereco.', '.$ug_numero.', '.$ug_cidade.', '.$ug_estado;
		}
	} else {
		if($ug_tipo_end == '') {
			$ug_endereco .= ', '.$ug_cidade.', '.$ug_estado;
		} else {
			$ug_endereco .= $ug_tipo_end.', '.$ug_endereco.', '.$ug_cidade.', '.$ug_estado;
		}		
	}
}

$need_key_maps = (checkIP()?"sensor=false":"key=AIzaSyA25PAcZMc6toew3UDW1HwG8wve00r8hb4");
?>
<script type="text/javascript" src="//maps.google.com/maps/api/js?<?php echo $need_key_maps; ?>"></script>
<script type="text/javascript">

	var endereco = '<?php echo $_GET['endereco']; ?>';
	var ug_id	 = eval(<?php echo $_GET['ug_id']; ?>);

    // Referência para a instância de google.maps.Map
	var mapaobj;

    // Referência para a instância de GClientGeocoder
	var geocoder; 

    // Coordenadas para o centro do mapa mundi
	var centroDoMundo = new google.maps.LatLng(0,0);

    nivelZoom = 2;
    
    $(function(){
        inicializa();
    });
</script>

<!-- Botão para reiniciar o mapa -->
<!--<input type="button" name="Reinicia" value="Reiniciar" onClick="reiniciar()" />-->
<div class="col-md-12 txt-preto fontsize-p">
<h2> 
Modelo de geolocalização endereços com resolução de ambiguidades e atualização de base de dados
</h2>

<!-- Div para a listagem dos endereços -->  
<div id="locais"></div>
<?php
$_GET['ug_cep'] = str_replace('-','',$_GET['ug_cep']);
$ug_cep1 = substr($_GET['ug_cep'],0,5);
$ug_cep2 = substr($_GET['ug_cep'],5,3);

$_GET['ug_cep'] = $ug_cep1.'-'.$ug_cep2;
?>
<!-- Formulário para o envio de consultas.-->      
<form action="" onsubmit="realizaConsulta(); return false;">
<p>
  ID:&nbsp;
    <input name="idcadastro" type="text" id="idcadastro" size="20" maxlength="11" value="<?php echo $_GET['ug_id']; ?>" readonly="readonly"/>
    (<span id="ug_coord_lat"><?php echo $ug_coord_lat ?></span>; <span id="ug_coord_lng"><?php echo $ug_coord_lng ?></span>)
    <input type="button" name="buscar" class="btn btn-info btn-sm" value="Atualizar geolocalização com estes valores" id="buscar" onClick="atualizaGeo(document.getElementById('ug_coord_lat').innerHTML, document.getElementById('ug_coord_lng').innerHTML);"/>
  <hr />
  <?php echo utf8_decode($_GET['endereco']); ?>,<?php echo $_GET['ug_cep']; ?>, Brasil
  &nbsp;&nbsp;<input type="button" name="resetar" value="Renovar Endereço" id="buscar" class="btn btn-info btn-sm" onClick="document.getElementById('consulta').value = document.getElementById('consultaoculta').value ;"/>
  <br />
  ENDERE&Ccedil;O:&nbsp;
    <input type="text" name="consulta" id="consulta" size = "100"  value="<?php echo utf8_decode($_GET['endereco']); ?>,<?php echo $_GET['ug_cep']; ?>, Brasil"/> 
    <input type="hidden" name="consultaoculta" id="consultaoculta" size = "100"  value="<?php echo utf8_decode($_GET['endereco']); ?><?php echo $_GET['ug_cep']; ?>, Brasil"/> 
    <input type="submit" class="btn btn-info btn-sm" name="Ok" value="Consultar" />
</p>
</form>

<!-- Div onde o mapa será renderizado -->
<div id="mapa" class="col-md-12" style="height: 1000px"></div> 

<!-- Funções utilizadas pelo sistema -->
<script type="text/javascript">

function inicializa() {
    
    var mapOptions = {
        zoom: nivelZoom ,
        center: centroDoMundo,
        mapTypeControl: true,
        scaleControl: true,
        overviewMapControl: true,
        overviewMapControlOptions:{opened:true},
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    // Cria o objeto principal referenciando a div 'mapa'
    mapaobj = new google.maps.Map(document.getElementById("mapa"),mapOptions);
	
<?php
	if(!empty($ug_coord_lat)&&!empty($ug_coord_lng)) {
?>
        // Chama a função centralizaMapa passando como argumento as coordenadas, 1)latitude e depois 2)latitude, o 3)endereço completo e a 4)precisão para controlar o nível do zoom
        centralizaMapa(<?php echo $ug_coord_lat;?>,<?php echo $ug_coord_lng;?>,"<?php echo $ug_endereco;?>", 16);
<?php
	}
	else {
?>
        // Centraliza o mapa na coordenada passada com nível de zoom 3
        mapaobj.setCenter(new google.maps.LatLng(-23.5489433, -46.6388182), 3);
        
        marcador = new google.maps.Marker({
        draggable: true,
        map: mapaobj
        });
        
        google.maps.event.addListener(marcador, 'dragend', function(evt){
        var lat = evt.latLng.lat();
        var lng = evt.latLng.lng();
        document.getElementById("ug_coord_lat").innerHTML = lat;
		document.getElementById("ug_coord_lng").innerHTML = lng;
    });
<?php
	}
?>
    // Cria o objeto que resolverá as consultas de endereço
    geocoder = new google.maps.Geocoder();
}

function atualizaGeo(lat0, lon0) {
	var lat 	 = lat0;
	var lon 	 = lon0;
	var ug_id	 = $("#idcadastro").val();
	var endereco = $("#consulta").val();
	
	window.open ("geobuscagrava.php?ug_google_maps_string="+endereco+'&ug_id='+ug_id+'&ug_coord_lat='+lat+"&ug_coord_lng="+lon,"_self");
}

// Função chamada quando o usuário envia a consulta
function realizaConsulta() {
    //Limpando a div 'locais'
    $("#locais").empty();
    // Recebe o endereço digitado no campo 'consulta' do form
    var endereco 	= $("#consulta").val();
	var idcadastro 	= $("#idcadastro").val();
	
    geocoder.geocode({address:endereco}, geocode_result_handler);
}

function geocode_result_handler(result, status) {
    if (status != google.maps.GeocoderStatus.OK) {
        alert('Geocoding failed. ' + status);
    } else {
        var alvo = document.getElementById("locais");
        mapaobj.fitBounds(result[0].geometry.viewport);
        var marker_title = result[0].formatted_address + " " +result[0].geometry.location;
        alvo.innerHTML += "<span class='txt-azul-claro'>" + marker_title + "</span>";                                          //***MUDAR A MENSAGEMMM*********
        alvo.innerHTML +="&nbsp;&nbsp;&nbsp;<input type='button' name='atualizargeo' value='Atualizar Geolocalização' id='atualizargeo' style='margin-bottom: 10px !important;' class='btn btn-sm btn-info' onClick='atualizaGeo(" +result[0].geometry.location.lat()+ "," + result[0].geometry.location.lng() +");'/>";

        mapaobj.setCenter(new google.maps.LatLng(result[0].geometry.location.lat(), result[0].geometry.location.lng()), 3);

        if (marcador) {
            marcador.setPosition(result[0].geometry.location);
            marcador.setTitle(marker_title);
        } else {
            marcador = new google.maps.Marker({
                position: result[0].geometry.location,
                title: marker_title,
                map: mapaobj
            });
        }
    }
}

// Função para centralizar o mapa no ponto solicitado (x - latitude, y - longitude, info - informações do endereço, acc - precisão do níevl do zoom)
function centralizaMapa(x, y, info, acc) {
   
    // Cria um ponto google.maps.LatLng
    var p = new google.maps.LatLng(x,y);
    
    // Obtém o nível de zoom conforme a precisão do endereço
	var zoom = acc;

    // Define o novo centro do mapa e o seu novo nível de zoom
    mapaobj.setCenter(p,zoom);

    // Cria um novo marcador que sera exibido no ponto p solicitado
    marcador = new google.maps.Marker({
        position: p, 
        draggable: true,
        title: info,
        map: mapaobj
    });

	marcador.addListener("dragstart", function() {
		infowindow.close();
	});

    google.maps.event.addListener(marcador, 'dragend', function(evt){
        var lat = evt.latLng.lat();
        var lng = evt.latLng.lng();
        document.getElementById("ug_coord_lat").innerHTML = lat;
		document.getElementById("ug_coord_lng").innerHTML = lng;
    });

    // Adiciona o marcador ao mapa
    marcador.setMap(mapaobj);

    // Exibe uma caixa de informação com o texto informado
    var infowindow = new google.maps.InfoWindow();
    google.maps.event.addListener(marcador, 'click', function() {
        infowindow.setContent("<b> " + info + "</b>");
        infowindow.open(mapaobj, marcador);
    });   
}

</script>
</div>
<?php 
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>