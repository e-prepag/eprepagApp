<?php
$_GET['endereco'] = utf8_decode($_GET['endereco']);
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";

$us_id = $_GET['us_id']; 
$us_endereco = $_GET['endereco']; 
$us_cep = $_GET['us_cep'];

if(!empty($us_endereco)) {
	
	$sql = "select * from dist_usuarios_stores_qiwi where us_id=$us_id;";

	$rs = SQLexecuteQuery($sql);
	$rs_row = pg_fetch_array($rs);

	$us_endereco = $rs_row['us_endereco'];
	$us_numero = $rs_row['us_numero'];
	$us_complemento = $rs_row['us_complemento'];
	$us_bairro = $rs_row['us_bairro'];
	$us_cidade = $rs_row['us_cidade'];
	$us_estado = $rs_row['us_estado'];
	$us_cep = $rs_row['us_cep'];

	$us_coord_lat = $rs_row['us_coord_lat'];
	$us_coord_lng  = $rs_row['us_coord_lng'];
	$us_google_maps_status = $rs_row['us_google_maps_status'];

}
$need_key_maps = (checkIP()?"sensor=false":"key=AIzaSyA25PAcZMc6toew3UDW1HwG8wve00r8hb4");
?>
<!--Insira o número da sua chave após a variável "key" na querystring abaixo -->
<script type="text/javascript" src="//maps.google.com/maps/api/js?<?php echo $need_key_maps; ?>"></script>
</script>
<!--ABQIAAAAeJzMJ3dLhaZ7pRKXsvNdaBR4eZj9Hf0QXLR-3E4___JDomdNNBRIBB0cA5ODor9xh7WB8Smc1txpyQ   chave meu ip-->
<!--ABQIAAAAeJzMJ3dLhaZ7pRKXsvNdaBT_nVl5JcFRxrUznZXV_B8X28sPKBRglwnxLGOgu8HLthjGDWHgvNS4sw   chave mdxnoip-->
<script type="text/javascript">
// variaveis vindas do backoffice
	var endereco = '<?php echo $_GET[endereco]; ?>';
	var us_id	 = eval(<?php echo $_GET[us_id]; ?>);
	
// Variável irá referenciar o objeto que representa o mapa
	var meuPrimeiroMapa = "";

// Referência para a instância de GMap2
	var mapaobj;

// Referência para a instância de GClientGeocoder
	var geocoder; 

// Coordenadas para o centro do mapa mundi
	var centroDoMundo = new google.maps.LatLng(0,0);

    nivelZoom = 2;
    
    $(function(){
        inicializa();
    })
</script>
<!-- Criamos o mapa no evento onLoad da página -->
<!-- Botão para reiniciar o mapa -->
<!--<input type="button" name="Reinicia" value="Reiniciar" onClick="reiniciar()" />-->
<div class="col-md-12 txt-preto fontsize-p">
<h2> 
Modelo de geolocalização endereços com resolução de ambigüidades e atualizacao de base de dados
</h2>

<!-- Div para a listagem dos endereços -->  
<div id="locais"></div>
<?php
$_GET['us_cep'] = str_replace('-','',$_GET['us_cep']);
$us_cep1 = substr($_GET['us_cep'],0,5);
$us_cep2 = substr($_GET['us_cep'],5,3);

$_GET['us_cep'] = $us_cep1.'-'.$us_cep2;
?>
<!-- Formulário para o envio de consultas. Note que a função -->
<!-- realizaConsulta é invocada no evento onSubmit da tag <form> -->        
<form action="" onsubmit="realizaConsulta(); return false;">
<p>
  ID:&nbsp;
<input name="idcadastro" type="text" id="idcadastro" size="20" maxlength="11" value="<?php echo $_GET['us_id']; ?>" readonly="readonly"/>
(<span id="us_coord_lat" name="us_coord_lat"><?php echo $us_coord_lat ?></span>; <span id="us_coord_lng" name="us_coord_lng"><?php echo $us_coord_lng ?></span>)
  <input type="button" name="buscar" class="btn btn-info btn-sm" value="Atualizar geolocalização com estes valores" id="buscar" onClick="atualizaGeo(document.getElementById('us_coord_lat').innerHTML, document.getElementById('us_coord_lng').innerHTML);"/>
  <!--<input type="button" name="buscar" value="Localizar Endere&ccedil;o" id="buscar" onClick="localizaEndereco();"/>-->
  <hr />
  <?php echo $_GET['endereco']; ?>,<?php echo $_GET['us_cep']; ?>, Brasil
  &nbsp;&nbsp;<input type="button" name="resetar" value="Renovar Endereço" id="buscar" class="btn btn-info btn-sm" onClick="document.getElementById('consulta').value = document.getElementById('consultaoculta').value ;"/>
  <br />
  ENDERE&Ccedil;O:&nbsp;
<input type="text" name="consulta" id="consulta" size = "100"  value="<?php echo $_GET['endereco']; ?>,<?php echo $_GET['us_cep']; ?>, Brasil"/> 
<input type="hidden" name="consultaoculta" id="consultaoculta" size = "100"  value="<?php echo $_GET['endereco']; ?>,<?php echo $_GET['us_cep']; ?>, Brasil"/> 
  <input type="submit" class="btn btn-info btn-sm" name="Ok" value="Consultar" />
</p>
</form>

<!-- Div onde o mapa será renderizado -->
<div id="mapa" class="col-md-12" style="height: 1000px"></div> 

<!-- Div onde o mapa sera renderizado. Note que o estilo -->
<!-- CSS define tamanho do mapa -->
<!--<center>
	<div id="meuMapa" style="width: 1000px; height: 600px"></div>
</center>-->

<!-- Funções utilizadas pelo sistema -->
<script type="text/javascript">


// Função chamada ao carregar a página HTML
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
	if(!empty($us_coord_lat)&&!empty($us_coord_lng)) {
?>
	// Chama a função centralizaMapa passando como argumento
	// as coordenadas do ponto (posição 1 para latitude, 0 para
	// longitude); O endereço completo do ponto encontrado; a 
	// precisão do endereço para controlar o nível de zoom
	centralizaMapa(<?php echo $us_coord_lat;?>,<?php echo $us_coord_lng;?>,"<?php echo $us_endereco;?>", 16);
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
            document.getElementById("us_coord_lat").innerHTML = lat;
            document.getElementById("us_coord_lng").innerHTML = lng;
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
	var us_id	 = $("#idcadastro").val();
	var endereco = $("#consulta").val();

    lat = document.getElementById("us_coord_lat").innerHTML;
	lon = document.getElementById("us_coord_lng").innerHTML;

	//alert('us_id:'+us_id+' lat:'+lat+' lon:'+lon);
	//alert('Endereço: '+endereco);
	window.opener.location.reload();
	window.open ("geobuscagrava_qiwi.php?us_google_maps_string="+endereco+'&us_id='+us_id+'&us_coord_lat='+lat0+"&us_coord_lng="+lon0,"_self");
}

// Função localiza endereço, retorna o endereco recuperado na base de dados conforme o id digitado
function localizaEndereco() {
	// Recebe o id digitado no campo 'idcadastro' do form
	var id = document.forms[0].idcadastro.value;
	
	alert('id: '+id);
}

// Função chamada quando o usuário envia a consulta
function realizaConsulta() {
    $("#locais").empty();

    // Recebe o endereço digitado no campo 'consulta' do form
    var endereco 	= $("#consulta").val();
	var idcadastro 	= $("#idcadastro").val();
	
    // Realiza a consulta. resolverEnderecos é a função callback
    // Javascript que será chamada quando o método getLocations do 
    // objeto geocoder retornar uma resposta.
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

// Função para centralizar o mapa no ponto solicitado
// Parâmetros: x à latitude; y à longitude; info à Um texto que será
// exibido em um quadro informativo que aponta para o endereço;
// acc à a precisão do endereço para utilizar o zoom adequado
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
        document.getElementById("us_coord_lat").innerHTML = lat;
		document.getElementById("us_coord_lng").innerHTML = lng;
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

// utils
function objToString(obj) {
    var str = '';
    for (var p in obj) {
        if (obj.hasOwnProperty(p)) {
            str += p + '::' + obj[p] + '\n';
        }
    }
    return str;
}
</script>
</div>
<?php 
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</html>