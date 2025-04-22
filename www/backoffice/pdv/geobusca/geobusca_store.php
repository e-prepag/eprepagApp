<?php
$_GET['endereco'] = utf8_decode($_GET['endereco']);
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";

$us_id = $_GET['us_id']; 
$us_endereco = $_GET['us_endereco']; 
$us_cep = $_GET['us_cep'];

if(!empty($us_endereco)) {
	
	$sql = "select * from dist_usuarios_stores_cartoes where us_id=$us_id;";
//echo $sql."<br>";
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

//	$us_endereco .= $us_endereco.', '.$us_numero.', '.$us_cidade.', '.$us_estado;
}

//echo $us_coord_lat.":us_coord_lat<br>";
//echo $us_coord_lng.":us_coord_lat<br>";

$need_key_maps = (checkIP()?"sensor=false":"key=AIzaSyA25PAcZMc6toew3UDW1HwG8wve00r8hb4");
?>
<!--Insira o número da sua chave após a variável "key" na querystring abaixo -->
<script type="text/javascript" src="//maps.google.com/maps/api/js?<?php echo $need_key_maps; ?>"></script>
<!--ABQIAAAAeJzMJ3dLhaZ7pRKXsvNdaBR4eZj9Hf0QXLR-3E4___JDomdNNBRIBB0cA5ODor9xh7WB8Smc1txpyQ   chave meu ip-->
<!--ABQIAAAAeJzMJ3dLhaZ7pRKXsvNdaBT_nVl5JcFRxrUznZXV_B8X28sPKBRglwnxLGOgu8HLthjGDWHgvNS4sw   chave mdxnoip-->
<script type="text/javascript">
// variaveis vindas do backoffice
	var endereco = '<?php echo $_GET['endereco']; ?>';
	var us_id	 = eval(<?php echo $_GET['us_id']; ?>);
	
	//alert(endereco);
	//alert(us_id);


// Variável irá referenciar o objeto que representa o mapa
	var meuPrimeiroMapa = "";

// Referência para a instância de GMap2
	var mapaobj;

// Referência para a instância de GClientGeocoder
	var geocoder; 

// Coordenadas para o centro do mapa mundi
	var centroDoMundo = new google.maps.LatLng(0,0);(0,0);

// Array para mapear níveis de Zoom com a precisão do resultado
// Sinta-se livre para realizar o mapeamento achar mais conveniente.
// Note que quanto maior o número, maior o nível de zoom.
//var nivelZoom = [];
//    nivelZoom[0] = 2;
//    nivelZoom[1] = 8;
//    nivelZoom[2] = 9;
//    nivelZoom[3] = 10;
//    nivelZoom[4] = 12;
//    nivelZoom[5] = 13;
//    nivelZoom[6] = 14;
//    nivelZoom[7] = 15;
//    nivelZoom[8] = 16;

nivelZoom = 2;
    
// Exibe o mapa em modo normal. As outras opções são
// G_SATELLITE_MAP e G_HYBRID_MAP 
	var tipoMapa = google.maps.MapTypeId.ROADMAP; 
    
    $(function(){
        inicializa();
    });
</script>

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
<form action="" onsubmit="event.preventDefault(); realizaConsulta(); return false;">
<p>
  ID:&nbsp;
<input name="idcadastro" type="text" id="idcadastro" size="20" maxlength="11" value="<?php echo $_GET['us_id']; ?>" readonly="readonly"/>
(<span id="us_coord_lat" name="us_coord_lat"><?php echo $us_coord_lat ?></span>; <span id="us_coord_lng" name="us_coord_lng"><?php echo $us_coord_lng ?></span>)
  <input type="button" class='btn btn-sm btn-info' name="buscar" value="Atualizar geolocalização com estes valores" id="buscar" onClick="atualizaGeo(document.getElementById('us_coord_lat').innerHTML, document.getElementById('us_coord_lng').innerHTML);"/>
  <!--<input type="button" name="buscar" value="Localizar Endere&ccedil;o" id="buscar" onClick="localizaEndereco();"/>-->
  <hr />
  <?php echo utf8_decode($_GET['endereco']); ?>,<?php echo $_GET['us_cep']; ?>, Brasil
  &nbsp;&nbsp;<input type="button" class='btn btn-sm btn-info' name="resetar" value="Renovar Endereço" id="buscar" onClick="document.getElementById('consulta').value = document.getElementById('consultaoculta').value ;"/>
  <br />
  ENDERE&Ccedil;O:&nbsp;
<input type="text" name="consulta" id="consulta" size = "100"  value="<?php echo utf8_decode($_GET['endereco']); ?>,<?php echo $_GET['us_cep']; ?>, Brasil"/> 
<input type="hidden" name="consultaoculta" id="consultaoculta" size = "100"  value="<?php echo utf8_decode($_GET['endereco']); ?>,<?php echo $_GET['us_cep']; ?>, Brasil"/> 
  <input type="submit" name="Ok" class='btn btn-sm btn-info' value="Consultar" />
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
    // Centraliza o mapa na coordenada (34, 0) com nível de zoom 3
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
	var us_id	 = $("#idcadastro").val();
	var endereco = $("#consulta").val();

//	lat = document.getElementById("us_coord_lat").innerHTML;
//	lon = document.getElementById("us_coord_lng").innerHTML;

	//Invertido por Wagner em 6/2/2013
	document.getElementById("us_coord_lat").innerHTML = lat;
	document.getElementById("us_coord_lng").innerHTML = lon;

	//alert('us_id:'+us_id+' lat:'+lat+' lon:'+lon);
	//alert('Endereço: '+endereco);
	
	window.open ("geobuscagrava_store.php?us_google_maps_string="+endereco+'&us_id='+us_id+'&us_coord_lat='+lat+"&us_coord_lng="+lon,"_self");
}

// Função localiza endereço, retorna o endereco recuperado na base de dados conforme o id digitado
function localizaEndereco() {
	// Recebe o id digitado no campo 'idcadastro' do form
	var id = document.forms[0].idcadastro.value;
	
	alert('id: '+id);
}

// Função chamada quando o usuário envia a consulta
function realizaConsulta(e) {
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
<?php

/*
https://developers.google.com/maps/documentation/javascript/v2/reference#GGeoStatusCode

enum GGeoStatusCode

Numeric equivalents for each symbolic constant are specified next to the equal sign.

Constants

Constant	Description
G_GEO_SUCCESS
= 200	 No errors occurred; the address was successfully parsed and its geocode has been returned.
G_GEO_BAD_REQUEST
= 400	 A directions request could not be successfully parsed. For example, the request may have been rejected if it contained more than the maximum number of waypoints allowed.
G_GEO_SERVER_ERROR
= 500	 A geocoding, directions or maximum zoom level request could not be successfully processed, yet the exact reason for the failure is not known.
G_GEO_MISSING_QUERY
= 601	 The HTTP q parameter was either missing or had no value. For geocoding requests, this means that an empty address was specified as input. For directions requests, this means that no query was specified in the input.
G_GEO_MISSING_ADDRESS
= 601	 Synonym for G_GEO_MISSING_QUERY.
G_GEO_UNKNOWN_ADDRESS
= 602	 No corresponding geographic location could be found for the specified address. This may be due to the fact that the address is relatively new, or it may be incorrect.
G_GEO_UNAVAILABLE_ADDRESS
= 603	 The geocode for the given address or the route for the given directions query cannot be returned due to legal or contractual reasons.
G_GEO_UNKNOWN_DIRECTIONS
= 604	 The GDirections object could not compute directions between the points mentioned in the query. This is usually because there is no route available between the two points, or because we do not have data for routing in that region.
G_GEO_BAD_KEY
= 610	 The given key is either invalid or does not match the domain for which it was given.
G_GEO_TOO_MANY_QUERIES
= 620	 The given key has gone over the requests limit in the 24 hour period or has submitted too many requests in too short a period of time. If you're sending multiple requests in parallel or in a tight loop, use a timer or pause in your code to make sure you don't send the requests too quickly.

*/
?>
// Callback para tratar o retorno de uma chamada ao método
// getLocations() do objeto geocoder. O parâmetro resposta será
// usado para acessar os dados retornados. resolverEnderecos também faz 
// uso da função listarLocais que será explicada adiante.
function resolverEnderecos(resposta) {

    // Retira todos os marcadores existentes no mapa.
    mapaobj.clearOverlays(); 
        
    // Verifica o status da resposta
    if (!resposta || resposta.Status.code != G_GEO_SUCCESS) {

        // Caso a resposta seja inválida, exibe o motivo.
        alert("Nao foi possivel localizar o endereco solicitado");
        // Os códigos de erro são úteis para procurer o motivo
        // exato da falha na consulta de endereços na documentação
        // do GoogleMaps API
        alert("Código de erro: " +  resposta.Status.code + "\n" + objToString(resposta));

    } else {

        // Caso o status da resposta seja G_GEO_SUCCESS,
        // iremos navegar em todos os resultados retornados,
        // que podem ser vários em caso de uma consulta ambígüa

        // Extrai o número de resultados retornados. O atributo
        // Placemark matém toda a informação de que precisamos
        // acerca das localidades encontradas.
        var num_resultados = resposta.Placemark.length;
        // Obtemos a referência DOM à div na qual os locais encontrados
        // serão listados através do Javascript
        var alvo = document.getElementById("locais");

        // Invoca a função listarLocais, explicada posteriormente
        listarLocais(alvo, resposta.Placemark);           

        // Caso haja múltiplos resultados, informa o fato ao usuário
        if (num_resultados > 1) {
              
              alert('A sua consulta retornou resultados ambígüos.' +
                    '\nEscolha a localidade mais adequada à consulta.');

        } else {

          // Caso haja um único resultado, 

          // Obtém uma referência ao endereço retornado
          var local = resposta.Placemark[0];

          // Extrai o um objeto GLatLng representando as coordenadas
          // do endereço solicitado
          var ponto = local.Point.coordinates;

          // Extrai a precisão do endereço. Accuracy é um número que
          // indica se o endereço retornado corresponde a um país, 
          // provincial, estado, cidade, bairro, rua, etc. Depende da
          // consulta que foi realizada. Com essa informação em mãos,
          // podemos decidir qual o nível de zoom mais adequado
          var acc = resposta.Placemark[0].AddressDetails.Accuracy;

          // Chama a função centralizaMapa passando como argumento
          // as coordenadas do ponto (posição 1 para latitude, 0 para
          // longitude); O endereço completo do ponto encontrado; a 
          // precisão do endereço para controlar o nível de zoom
          centralizaMapa(ponto[1],ponto[0],resposta.Placemark[0].address, acc);
         
       }
    }
}


// Função lista as localizações encontradas na consulta.
// Os parâmetros são: alvo --> a referência DOM da div que receberá os 
// endereços; placemark --> referência a um array de objetos representando as
// localidades encontradas
function listarLocais(alvo, placemark) {

    //Começa a escrita na div informando a quantidade de resultados
    // encontrados. O máximo retornado pelo geocoder são seis endereços
    alvo.innerHTML = "<p> A pesquisa retornou " +
                         placemark.length + " resultado(s): </p>";	// + "<br>" + objToString(placemark);

    // Loop para escrever informações de cada endereço
    for (var i=0; i<placemark.length; ++i) {

        // Obtém a informação da Unidade Federativa, no caso de um 
        // endereço no Brasil
        var uf = placemark[i].AddressDetails.Country
                            .AdministrativeArea.AdministrativeAreaName;	

        // Obtém a informação sobre a precisão do endereço  
		var acc = placemark[i].AddressDetails.Accuracy;

        // Obtém um ponto GLatLng com as coordenadas da localidade i
        var p = placemark[i].Point.coordinates;
      
        // Obtém o endereço textual completo
        var info = placemark[i].address;
    
        // Começa a escrita de um link para a função centralizaMapa do
        // endereço i encontrado
        alvo.innerHTML +="<a href='javascript:centralizaMapa(" + p[1] + 
                         "," + p[0] + ",\"" + info + "\", " + acc +
                         ")'>" + placemark[i].address +"</a>";
		
		var lat = p[1];
		var lon = p[0];	
				
		alvo.innerHTML +="&nbsp;&nbsp;&nbsp;<input type='button' name='atualizargeo' value='Atualizar Geolocalização' id='atualizargeo' style='margin-bottom: 10px !important;' class='btn btn-sm btn-info' onClick='atualizaGeo(" +lat+ "," + lon +");'/>";						 

		alvo.innerHTML +="<br />";
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