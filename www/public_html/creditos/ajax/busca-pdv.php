<?php
header("Content-Type: text/html; charset=ISO-8859-1",true);
function isAjax() {return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));}
function block_direct_calling() {
    if(!isAjax()) {
           echo "Chamada não permitida<br>";
           die("Stop");
    }
}
block_direct_calling();
require_once "../../../includes/constantes.php";
require_once DIR_CLASS."gamer/controller/HeaderController.class.php";
$controller = new HeaderController;
// Adiciona os valos recebidos por post em uma variavel
$Cidade = isset($_POST['cidade']) ? filter_var(utf8_decode(trim(str_replace("'", "",$_POST['cidade']))),FILTER_SANITIZE_STRING) : '';
$Estado = isset($_POST['estado']) ? filter_var(utf8_decode($_POST['estado']), FILTER_SANITIZE_STRING) : "";
$Bairro = isset($_POST['bairro']) ? filter_var(utf8_decode(trim(str_replace("'", "",$_POST['bairro']))), FILTER_SANITIZE_STRING) : "";
//Conectando com PDO para execução da QUERY
$con = ConnectionPDO::getConnection();
$pdo = $con->getLink();

if(!isset($_POST['box']) || $_POST['box'] != true){

    function grava_log_mapa_lhs($mensagem){
            $ARQUIVO_LOG_HTTP_REFERER = RAIZ_DO_PROJETO . "log/log_mapa_lista_lhs.txt";	

            //Arquivo
            $file = $ARQUIVO_LOG_HTTP_REFERER;

            //Mensagem
    //	$mensagem = date('Y-m-d H:i:s') . " " . (($GLOBALS['_SERVER']['HTTP_REFERER'])?$GLOBALS['_SERVER']['HTTP_REFERER']:'Empty') . " - " . $GLOBALS['_SERVER']["REMOTE_ADDR"] . "\n";
    //echo 	$mensagem;
            //Grava mensagem no arquivo
            if ($handle = fopen($file, 'a+')) {
                    fwrite($handle, $mensagem);
                    fclose($handle);
            } 	
    }
    //Id do GoCASH
    $id_gocash = 1;

    if(empty($ug_ativo)) {
            $ug_ativo = 0;
    }
    //echo $_SERVER['HTTP_REFERER'];
    //echo substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?")?strpos($_SERVER['HTTP_REFERER'],"?"):strlen($_SERVER['HTTP_REFERER']));
    ?>
    <form name="form_lanHouses" id="form_lanHouses" method="post">
        <div id="main">
    <?php		
		
        // Verifica se o codigo foi digitado ou se o codigo digitado é igual ao da imagem
        if ((strtolower($_POST['verificationCode']) == strtolower($_SESSION['palavraCodigo'])) and ($_POST['verificationCode'] != "")){
    ?>
            <div id="legendas"></div>
            <div id="map" style="height: 500px"></div>
    <?php
                set_time_limit(3000);
                $time_start = getmicrotime();

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
                        ug_complemento,
                        ug_endereco, 
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
                                AND trim(both ' ' from replace(ug_cidade, '\'', '')) = :ug_cidade
                                AND trim(both ' ' from replace(ug_bairro, '\'', '')) = :ug_bairro
                                AND ug_estado = :ug_estado
                                AND ug.ug_coord_lat != 0
                                AND ug.ug_coord_lng != 0
                                AND ug.ug_status = 1
                        )
                UNION ALL
                        (SELECT 
                                us_tipo_store as tipo,
                                us_nome_loja AS ve_nome, 
                                us_cidade AS ve_cidade, 
                                us_estado AS ve_estado, 
                                us_coord_lat AS ug_coord_lat, 
                                us_coord_lng AS ug_coord_lng, 
                                us_tipo_store AS ug_tipo_cadastro, 
                                '1' AS ug_ativo, 
                                us_id AS ve_id, 
                                us_numero AS ug_numero, 
                                us_complemento AS ug_complemento,
                                us_endereco AS ug_endereco, 
                                '' AS ug_tipo_end, 
                                us_bairro AS ug_bairro
                        FROM dist_usuarios_stores_cartoes
                        WHERE trim(both ' ' from replace(us_cidade, '\'', '')) = :us_cidade
                                AND trim(both ' ' from replace(us_bairro, '\'', '')) = :us_bairro 
                                AND us_estado = :us_estado
                                AND us_coord_lat != 0
                                AND us_coord_lng != 0
                                AND us_id IN (
                                    select us_id from classificacao_mapas cm
                                            INNER JOIN classificacao_mapas_pdv cmp ON cm.cm_id = cmp.cm_id
                                    WHERE cm.cm_id = :idgocash
                                            AND cm_status = 1
                                )
                        )
                UNION ALL
                        (SELECT 
                                'Q' as tipo,
                                us_nome_loja AS ve_nome, 
                                us_cidade AS ve_cidade, 
                                us_estado AS ve_estado, 
                                us_coord_lat AS ug_coord_lat, 
                                us_coord_lng AS ug_coord_lng, 
                                '' AS ug_tipo_cadastro, 
                                '1' AS ug_ativo, 
                                us_id AS ve_id, 
                                us_numero AS ug_numero, 
                                us_complemento AS ug_complemento,
                                us_endereco AS ug_endereco, 
                                '' AS ug_tipo_end, 
                                us_bairro AS ug_bairro
                        FROM dist_usuarios_stores_qiwi
                        WHERE trim(both ' ' from replace(us_cidade, '\'', '')) = :cidade
                                AND trim(both ' ' from replace(us_bairro, '\'', '')) = :bairro 
                                AND us_estado = :estado
                                AND us_coord_lat != 0
                                AND us_coord_lng != 0
                        )
                ) as locais
                ORDER BY ve_nome, ve_estado
                                        ";
                        
                $stmt = $pdo->prepare($sqlLans);
                $auxEstado=trim($Estado);
                $auxCidade=trim($Cidade);
                $auxBairro= trim($Bairro);
                $stmt->bindParam(':ug_cidade', $auxCidade, PDO::PARAM_STR);
                $stmt->bindParam(':ug_bairro', $auxBairro, PDO::PARAM_STR);
                $stmt->bindParam(':ug_estado', $auxEstado, PDO::PARAM_STR);
                $stmt->bindParam(':us_cidade', $auxCidade, PDO::PARAM_STR);
                $stmt->bindParam(':us_bairro', $auxBairro, PDO::PARAM_STR);
                $stmt->bindParam(':us_estado', $auxEstado, PDO::PARAM_STR);
                $stmt->bindParam(':cidade', $auxCidade, PDO::PARAM_STR);
                $stmt->bindParam(':bairro', $auxBairro, PDO::PARAM_STR);
                $stmt->bindParam(':estado', $auxEstado, PDO::PARAM_STR);
                $stmt->bindParam(':idgocash', $id_gocash, PDO::PARAM_INT);
                
                $stmt->execute();
                $rssLans = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $mensagem = date('Y-m-d H:i:s') . " $sqlLans\n";
                grava_log_mapa_lhs($mensagem);

                // ||' ('||ug.ug_tipo_cadastro||')' concatenando no select
                $totLans = count($rssLans);

                if($totLans > 0) {
                    $contador = 0;
                    foreach($rssLans as $vlrLans){
                        
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
                    //echo "<hr>";
                    echo "<span class='txt-preto col-md-12'>Encontrado$s_sub: ".($contador)." Ponto$s_sub de Venda</span>";
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

                        //Evento Clique no Balão
                        function exibir() { 
                                $.blockUI.defaults.message = 'Não é permitido selecionar!';
                                $.blockUI(); 
                                setTimeout($.unblockUI, 2000); 
                                $.blockUI.defaults.message = '<img src="imagens/loading1.gif" alt=""/>';
                        } 

                        function inicializa() {
                                // Creating a reference to the mapDiv
                                var mapDiv      = document.getElementById('map');
                                //var centroform  = document.form_lanHouses.centro.value;
                                //var zoomform    = document.form_lanHouses.zoom.value;
                                //alert(nome.length);
                                centroform = places[0];
                                if (places.length>1)
                                        zoomform = 14;
                                else zoomform = 16;

                                // Creating a latLng for the center of the map
                                //var latlng = new google.maps.LatLng(-13.00, -51.30);

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

                                //alert(places.length);
                                for (var i = 0; i < places.length; i++) {
                                        // Creating a new marker

                                        if(tipo[i] == 'L') {
                                                cor = '/imagens/icone_googlemaps_3.png';
                                        }
                                        else if(tipo[i] == 'Q') {
                                                cor = '/imagensicone_qiwi.png';
                                        }
                                        else {
                                                cor = '/imagens/icone_googlemaps_card.png';
                                        }
                                        index = 1;
                                        //var vendatratada = 'Teste';

                                        //alert(places[i]);

                                        var marker = new google.maps.Marker({
                                                position: places[i],
                                                map: map,
                                                title: dadoslogin[i],
                                                icon: cor, 
                                                zIndex: index
                                        });

                                        //marker.setClickable(false);
                                        cor = '';

                                        //alert(marker);

                                        // Wrapping the event listener inside an anonymous function
                                        // that we immediately invoke and passes the variable i to.
                                        (function(i, marker) {
                                                //alert('Dentro da função!!');
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

                                                var content = '<div id="info" class="txt-preto" onmousedown="javascript:exibir();">' +
                                                '<strong>NOME</strong>: '+nome[i]+'<br>' +
                                                '<strong>ENDEREÇO</strong>: '+dadosendereco[i]+'<br>' +
                                                '</div>';

                                                infowindow.setContent(content);

                                                // Tying the InfoWindow to the marker
                                                infowindow.open(map, marker);
                                        });
                                        })(i, marker);
                                }	
                                zoomint = parseInt(zoomform, 10); 


                                centrostr = (centroform).toString();
                                centrostr = centrostr.replace("(", "");
                                centrostr = centrostr.replace(")", "");

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
                echo '<br><span class="txt-preto col-md-12">Clique no &iacute;cone para ver o endere&ccedil;o</span>'; 


    ?>
                
        <script type="text/javascript">
                inicializa();
        </script>
                            <?php 
                    }elseif($_POST['verificationCode'] == ""){
                            echo utf8_encode ('<div class="col-md-12"><span class="col-md-offset-2 col-md-4 text-left txt-vermelho">Preencha o campo de verificação.</span></div>');
                    }elseif(strtolower($_POST['verificationCode']) != strtolower($_SESSION['palavraCodigo'])){
                            echo '<span class="col-md-offset-2 col-md-4 text-left txt-vermelho">Preencha o campo de verificação corretamente.</span>';
                    }
                    ?>
                            </form>
                            <!-- fim :: conteudo principal //-->
            </div>
                    <!-- fim :: centro //-->
<?php
}else{
    
    //Id do GoCASH
    $id_gocash = 1;

    // Deixa o drop down nos valores que estavam selecionados antes do reload.
    if (!empty($_POST['cidade']) && !empty($_POST['estado'])) {
        
        $SQLBairro = "
                                    SELECT 
                                            ug_bairro
                                    FROM (

                                            (SELECT ug_bairro
                                            FROM dist_usuarios_games
                                            WHERE ug_cidade = :ug_cidade
                                                    AND ug_estado = :ug_estado
                                                    AND ug_ativo = 1
                                                    AND ug_status = 1
                                                    AND ug_coord_lat != 0
                                                    AND ug_coord_lng != 0
                                            )
                                    UNION ALL

                                            (SELECT us_bairro AS ug_bairro
                                            FROM dist_usuarios_stores_cartoes
                                            WHERE us_cidade = :us_cidade
                                                    AND us_estado = :us_estado
                                                    AND us_coord_lat != 0
                                                    AND us_coord_lng != 0
                                                    AND us_id IN (
                                                        select us_id from classificacao_mapas cm
                                                                INNER JOIN classificacao_mapas_pdv cmp ON cm.cm_id = cmp.cm_id
                                                        WHERE cm.cm_id = :id_gocash
                                                                AND cm_status = 1
                                                    )
                                            )
                                    UNION ALL

                                            (SELECT trim(both ' ' from us_bairro) AS ug_bairro
                                            FROM dist_usuarios_stores_qiwi
                                            WHERE us_cidade = :cidade
                                                    AND us_estado = :estado
                                                    AND us_coord_lat != 0
                                                    AND us_coord_lng != 0
                                            )
                                    ) as locais
                                    GROUP BY ug_bairro 
                                    ORDER BY ug_bairro
                                    ";
        
        $stmt = $pdo->prepare($SQLBairro);
        $auxEstado=trim($Estado);
        $auxCidade=trim($Cidade);
        $stmt->bindParam(':ug_cidade', $auxCidade, PDO::PARAM_STR);
        $stmt->bindParam(':ug_estado', $auxEstado, PDO::PARAM_STR);
        $stmt->bindParam(':us_cidade', $auxCidade, PDO::PARAM_STR);
        $stmt->bindParam(':us_estado', $auxEstado, PDO::PARAM_STR);
        $stmt->bindParam(':id_gocash', $id_gocash, PDO::PARAM_INT);
        $stmt->bindParam(':cidade', $auxCidade, PDO::PARAM_STR);
        $stmt->bindParam(':estado', $auxEstado, PDO::PARAM_STR);
        $stmt->execute();
        $ResultadoBairro = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Query que cria o drop drown das cidades
    if (!empty($_POST['estado'])) {
        $SQLCidade = "
            SELECT 
                    ug_cidade
            FROM (

                    (SELECT 
                            ug_cidade
                    FROM dist_usuarios_games
                    WHERE ug_ativo = 1
                            AND ug_status = 1
                            AND ug_estado = :ug_estado
                            AND ug_coord_lat != 0
                            AND ug_coord_lng != 0
                    )
            UNION ALL
                    (SELECT 
                            us_cidade AS ug_cidade
                    FROM dist_usuarios_stores_cartoes
                    WHERE us_coord_lat != 0
                            AND us_coord_lng != 0
                            AND us_estado = :us_estado
                            AND us_id IN (
                                select us_id from classificacao_mapas cm
                                        INNER JOIN classificacao_mapas_pdv cmp ON cm.cm_id = cmp.cm_id
                                WHERE cm.cm_id = :id_gocash
                                        AND cm_status = 1
                            )
                    )
            UNION ALL
                    (SELECT 
                            trim(both ' ' from us_cidade) AS ug_cidade
                    FROM dist_usuarios_stores_qiwi
                    WHERE us_coord_lat != 0
                            AND us_coord_lng != 0
                            AND us_estado = :estado
                    )
            ) as locais
            GROUP BY ug_cidade
            ORDER BY ug_cidade
            ";
        //echo "SQLCidade: $SQLCidade<br>";
        //die("Stop");
        $stmt = $pdo->prepare($SQLCidade);
        $auxEstado=trim($Estado);
        $stmt->bindParam(':ug_estado', $auxEstado, PDO::PARAM_STR);
        $stmt->bindParam(':us_estado', $auxEstado, PDO::PARAM_STR);
        $stmt->bindParam(':id_gocash', $id_gocash, PDO::PARAM_INT);
        $stmt->bindParam(':estado', $auxEstado, PDO::PARAM_STR);
        $stmt->execute();
        $ResultadoCidade = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    }//end if ((isset($_POST['estado'])) and (isset($_POST['cidade'])))

    /*    
            Inicio captcha
     */
    function generateRandomCode() {
    $numbersAllowedInCode = false; //	Set to FALSE for a 'Letters Only' Code
    $numberOfLetters = 4;   //	Allow Minimum 20 Pixels Per Letter - See codeBoxWidth Property Above)

    //echo "numbersAllowedInCode: '".($numbersAllowedInCode?"YES":"Nope")."', numberOfLetters: '".$numberOfLetters."'<br>";

    $GLOBALS['_SESSION']['verificationCode'] = "";
    $ret = "";
    for ($placebo = 1; $placebo <= $numberOfLetters; $placebo++) {
        if ((rand() > 0.49) || ($numbersAllowedInCode == false)) {
            $number = 97 + rand(0, 25); //rand(97,122);
            $char = chr($number);
            $ret .= $char;
    //	        $ret .= chr(rand(97,122));
        } else {
            $number = 48 + rand(0, 10); //rand(48, 57);
            $char = chr($number);
            $ret .= $char;
    //			$ret .= chr(rand(48, 57));
        }
    //echo "<nobr>char: '$char', number: '$number', ret: '".$ret."'</nobr><br>";
    }
    $GLOBALS['_SESSION']['verificationCode'] = $ret;
    $GLOBALS['_SESSION']['palavraCodigo'] = $ret;
    //echo "verificationCode: '".$GLOBALS['_SESSION']['verificationCode']."'<br>";
    //die("Stop");
    return $ret;
    }

    // echo "<hr>translateCode(generateRandomCode()) :" . translateCode(generateRandomCode()) . "<hr>";

    function translateCode($scode) {
    $numbersAllowedInCode = false; //	Set to FALSE for a 'Letters Only' Code
    $numberOfLetters = 4;   //	Allow Minimum 20 Pixels Per Letter - See codeBoxWidth Property Above)

    //echo "<nobr>scode: '".$scode."'</nobr><br>";
    $stmp = "";

    for ($placebo = 0; $placebo < $numberOfLetters; $placebo++) {
        /*
          $schar = ord(substr($scode, $placebo, 1))+$placebo;
          if(strlen($schar)==0) $schar = "000";
          if(strlen($schar)==1) $schar = "00".$schar;
          if(strlen($schar)==2) $schar = "0".$schar;
          $stmp .= $schar;
         */
        $schar = ord(substr($scode, $placebo, 1)) + $placebo;
        $stmp.= str_pad($schar, 3, '0', STR_PAD_LEFT);

    //echo "<nobr>$placebo: '".$schar."' ('".ord(substr($scode, $placebo, 1))."') -> '".$stmp ."'</nobr><br>";
    }
    return $stmp;
    }
    
    /*
        Fim captcha
    */
    
?>
    <a class="txt-branco hover-verde-personalizado" href="/creditos/">
        <div class="row box-verde-home-sup">
            <div class="col-md-3 top10">
                <span class="glyphicon text22 glyphicon-lock pull-right"></span>
            </div>
            <div class="col-md-9 top10">
                <h4 class="bottom0"><strong>Pontos de venda</strong></h4>
                <p class="">Acesse aqui</p>
            </div>
        </div>     
    </a>
    <div class=" row box-verde-home-inf">
        <form id="form_lanHouses_filtros" name="form_lanHouses_filtros" method="post" action="busca-pdv.php">
        <div class="col-md-2 top5 txt-branco">
            <span class="glyphicon text22 glyphicon-search " style="float: left;"></span>
        </div>
        <div class="col-md-10 top5 txt-branco">
            <!--<p class="bottom0"><strong>Encontre</strong></p>-->
            <p class="">Encontre o ponto de venda mais próximo de você </p>
        </div>
        <div class="col-md-12 txt-cinza">
            <select name="estado" class="form-control input-sm" id="estado" onChange='MostraCidade();'>
                <option value="">Estado</option>
                <?php
                // Gera os dados do drop down estado
                foreach ($SIGLA_ESTADOS as $value) {
                    echo '<option value="' . $value . '"';
                    if ($_POST['estado'] == $value) {
                        echo " SELECTED ";
                    }
                    echo ">" . $value . "</option>\n";
                }
                ?>
            </select>
        </div>
        <div class="col-md-12 txt-cinza top5" id="SelCidade">
            <?php
            if (isset($ResultadoCidade) && $ResultadoCidade) {
                echo '<select name="cidade" id="cidade"  class="form-control input-sm" onChange="MostraBairro();"><option value="">Selecione a Cidade</option>';
                foreach($ResultadoCidade as $RowCidade){
                    echo '<option value="' . $RowCidade['ug_cidade'] . '"';
                    if (utf8_decode($_POST['cidade']) == $RowCidade['ug_cidade'] && !empty($RowCidade['ug_cidade'])) {
                        echo " SELECTED ";
                    }
                    echo '>' . $RowCidade['ug_cidade'] . '</option>';
                }
                echo '</select>';
            } else {
                ?>
                <select name="cidade" class="form-control input-sm" id="cidade" DISABLED>
                    <option value="">Selecione um Estado</option>		
                </select>
                <?php
            }
            ?>
        </div>
        <div class="col-md-12 txt-cinza top5" id="SelBairro">
            <?php
            if (isset($ResultadoBairro) && $ResultadoBairro) {
                if(!empty($_POST['bairro']))
                    $_POST['bairro'] = utf8_decode ($_POST['bairro']);
                echo '<select class="form-control input-sm" name="bairro" id="bairro"><option value="">Selecione o Bairro</option>';
                foreach($ResultadoBairro as $RowBairro){
                    echo '<option value="' . $RowBairro['ug_bairro'] . '"';
                    if ($_POST['bairro'] == $RowBairro['ug_bairro'] && !empty($RowBairro['ug_bairro'])) {
                        echo " SELECTED ";
                    }
                    echo '>' . $RowBairro['ug_bairro'] . '</option>';
                }
                echo '</select>';
            } else {
                ?>
                <select class="form-control input-sm" name="bairro" id="bairro" DISABLED>
                    <option value="">Selecione uma Cidade</option>		
                </select>
                <?php
            }
            ?>
        </div>
        <div class="col-md-12 txt-cinza top5">
<?php
        $randomcode = generateRandomCode();
        $randomcode_translated = translateCode($randomcode);
?>
            <img height="30px" width="90px" class="pull-left" src="/includes/captcha/CaptchaImage.php?uid=<?php echo $randomcode_translated; ?>" title="Verify Code" vspace="2" />
            <a class="glyphicon glyphicon-refresh pull-left espacamento-laterais-pequeno" alt="alterar imagem" title="alterar imagem" onclick="montaBoxPdv();" href="javascript:void(0);"></a>
            <input name="verificationCode" style="width: 50px;" class="pull-left form-control input-sm espacamento-laterais-pequeno" type="text" id="verificationCode" />
            <input type="hidden" name="rc" value="<?php echo $randomcode;?>">
            <a href="javascript:void(0);" onClick="procuraLan()" class="pull-left btn btn-success btn-sm left5">Ok</a>
        </div>
    </form>
    </div>
    <div class="row">            
        <a href="http://blog.e-prepag.com/pdvs-online-oficiais-e-prepag/" target="_blank" class="c-pointer botao-parceiros-online-home" style="z-index: 999; text-align: left !important;">
            <div class="col-md-2">
                <span class="glyphicon glyphicon-search" style="top: 5px !important; left: -5px !important; font-size: 30px;" aria-hidden="true"></span> 
            </div>
            <div class="col-md-10"> 
               <div class ="row text14 p-left15">Ponto de Venda Online</div> 
               <div class="row fontsize-pp p-left15">Encontre um parceiro oficial</div>          
            </div>
        </a>
    </div>
<?php
}