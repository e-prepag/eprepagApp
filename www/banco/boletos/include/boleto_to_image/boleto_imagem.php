<?php
    $cBarra = $dadosboleto["codigo_barras"];
    $codigo = str_split($cBarra);

//    echo $cBarra;
//    die;

    switch ($bbg_bco_codigo) {
        case "341":
                // ITAU
                $logo_banco = $raiz_do_projeto.'public_html/imagens/boletos/logoitau.jpg';
                break;
        case "033":
                // SANTANDER
                $logo_banco = $raiz_do_projeto.'public_html/imagens/boletos/logobanespa.jpg';
                break;
        case "237":
                // BRADESCO
                $logo_banco = $raiz_do_projeto.'public_html/imagens/boletos/logobradesco.jpg';
                break;

        default:
            break;
    }


    header('Content-type: image/gif');
    
    $largura = 550;
    $altura = 720;
    $espessuraE = 1;// espessura Fina
    $espessuraL = 3;// espessura Larga
    $i=100;
    $altura_tela = 770;
    $larguraInicio= 25;
    $larguraAtual = 25;

//    $imagem1 = imagecreatefrompng( $_SERVER['DOCUMENT_ROOT']."/boletos/include/boleto_to_image/boleto_BG.png" );
    $imagem1 = imagecreatefrompng( $raiz_do_projeto . "banco/boletos/include/boleto_to_image/boleto_BG.png" );


    //Fontes
    $arial_bold =  $raiz_do_projeto . "banco/boletos/include/boleto_to_image/arialbd.ttf";
    $arial =  $raiz_do_projeto . "banco/boletos/include/boleto_to_image/arial.ttf";
    $arial_black =  $raiz_do_projeto . "banco/boletos/include/boleto_to_image/ariblk.ttf";

    // fonte colors
    $preto = imagecolorallocate($imagem1, 0, 0, 0);
    $branco = imagecolorallocate($imagem1, 255, 255, 255);
    $cinza = imagecolorallocate($imagem1, 128, 128, 128);
    $azul = imagecolorallocate($imagem1, 0, 0, 250);

    $font_size = 9;


    imagettftext( $imagem1, 9, 0, 125, 49, $preto, $arial,  $dadosboleto["identificacao"]);
    imagettftext( $imagem1, 9, 0, 125, 62, $preto, $arial,  $dadosboleto["cpf_cnpj"]);



    $logo_banco = imagecreatefromjpeg($logo_banco);
//    $logo_banco = imagecreatefromjpeg($_SERVER['DOCUMENT_ROOT']."/boletos/imagens/logoitau.jpg");

    // linha 1 logo
    imagecopymerge($imagem1, $logo_banco,3,86,0,0,imagesx($logo_banco),imagesy($logo_banco),100);
    imagettftext( $imagem1, 15, 0, 160, 123, $preto, $arial_bold,  $dadosboleto["codigo_banco_com_dv"] );
    imagettftext( $imagem1, 12, 0, 218, 123, $preto, $arial_bold, $dadosboleto["linha_digitavel"] );

    //linha 2 cedente
    imagettftext( $imagem1, $font_size, 0, 10, 152, $preto, $arial_bold,  $dadosboleto["cedente"] );
    imagettftext( $imagem1, $font_size, 0, 315, 152, $preto, $arial_bold,  $dadosboleto["agencia_codigo"] );
    imagettftext( $imagem1, $font_size, 0, 450, 152, $preto, $arial_bold,  $dadosboleto["especie"] );
    imagettftext( $imagem1, $font_size, 0, 490, 152, $preto, $arial_bold,  $dadosboleto["quantidade"]);
    imagettftext( $imagem1, 8, 0, 550, 152, $preto, $arial_black,  $dadosboleto["nosso_numero"] );

    //linha 3 número documento
    imagettftext( $imagem1, $font_size, 0, 10, 178, $preto, $arial_bold,  $dadosboleto["numero_documento"] );
    imagettftext( $imagem1, $font_size, 0, 210, 178, $preto, $arial_black,  $dadosboleto["cpf_cnpj"] );
    imagettftext( $imagem1, 8, 0,350, 178, $preto, $arial_black,  $dadosboleto["data_vencimento"] );
    imagettftext( $imagem1, $font_size, 0, 550, 178, $preto, $arial_bold,  $dadosboleto["valor_boleto"] );

    //linha 4

    //linha 5 Sacado
    imagettftext( $imagem1, $font_size, 0, 10, 230, $preto, $arial_bold,  $dadosboleto["sacado"] );

    //linha 6 Demonstrativo
    imagettftext( $imagem1, $font_size, 0, 10, 265, $preto, $arial_bold,  $dadosboleto["demonstrativo1"] );
    imagettftext( $imagem1, $font_size, 0, 10, 280, $preto, $arial_bold,  $dadosboleto["demonstrativo2"] );
    imagettftext( $imagem1, $font_size, 0, 10, 295, $preto, $arial_bold,  $dadosboleto["demonstrativo3"] );

    //linha 07 logo
    imagecopymerge($imagem1, $logo_banco,3,368,0,0,imagesx($logo_banco),imagesy($logo_banco),100);
    imagettftext( $imagem1, 15, 0, 160, 405, $preto, $arial_bold,  $dadosboleto["codigo_banco_com_dv"] );
    imagettftext( $imagem1, 12, 0, 218, 405, $preto, $arial_bold, $dadosboleto["linha_digitavel"] );

    //linha 8 local de pagamento
    imagettftext( $imagem1, 8, 0, 10, 435, $preto, $arial_bold,  'Até o vencimento pague em qualquer banco.' );
    imagettftext( $imagem1, 8, 0, 540, 435, $preto, $arial_black,  $dadosboleto["data_vencimento"] );

    //linha 9 Cedente
    imagettftext( $imagem1, $font_size, 0, 10, 460, $preto, $arial_bold,  $dadosboleto["cedente"] );
    imagettftext( $imagem1, $font_size, 0, 540, 460, $preto, $arial_black,  $dadosboleto["agencia_codigo"] );

    //linha 10 Data do documento
    imagettftext( $imagem1, 8, 0, 15, 487, $preto, $arial_black,  $dadosboleto["data_documento"] );
    imagettftext( $imagem1, $font_size, 0, 135, 487, $preto, $arial_bold,  $dadosboleto["numero_documento"] );
    imagettftext( $imagem1, $font_size, 0, 295, 487, $preto, $arial_bold,  $dadosboleto["especie_doc"] );
    imagettftext( $imagem1, $font_size, 0, 365, 487, $preto, $arial_bold,  $dadosboleto["aceite"] );
    imagettftext( $imagem1, 8, 0, 400, 487, $preto, $arial_black,  $dadosboleto["data_processamento"] );
    imagettftext( $imagem1, $font_size, 0, 540, 487, $preto, $arial_black,  $dadosboleto["nosso_numero"] );

    //linha 11 Uso do banco
    imagettftext( $imagem1, $font_size, 0, 135, 513, $preto, $arial_bold,  $dadosboleto["carteira"] );
    imagettftext( $imagem1, $font_size, 0, 225, 513, $preto, $arial_bold,  $dadosboleto["especie"] );
    imagettftext( $imagem1, $font_size, 0, 280, 513, $preto, $arial_bold,  $dadosboleto["quantidade"] );
    imagettftext( $imagem1, $font_size, 0, 415, 513, $preto, $arial_bold,  $dadosboleto["valor_boleto"] );
    imagettftext( $imagem1, $font_size, 0, 540, 513, $preto, $arial_bold,  $dadosboleto["valor_boleto"] );

    //linha 12 Instruções
    imagettftext( $imagem1, $font_size, 0, 10, 560, $preto, $arial,  str_replace('<br>', "\n",$dadosboleto["instrucoes1"]));
    imagettftext( $imagem1, $font_size, 0, 10, 572, $preto, $arial,  str_replace('<br>', "\n",$dadosboleto["instrucoes2"]));
    imagettftext( $imagem1, $font_size, 0, 10, 584, $preto, $arial,  str_replace('<br>', "\n",$dadosboleto["instrucoes3"]));
    imagettftext( $imagem1, $font_size, 0, 10, 596, $preto, $arial,  str_replace('<br>', "\n", $dadosboleto["instrucoes4"]));

    //linha 13 Sacado
    imagettftext( $imagem1, 10, 0, 10, 670, $preto, $arial_bold,  $dadosboleto["sacado"] );
    imagettftext( $imagem1, 10, 0, 10, 682, $preto, $arial_bold,  $dadosboleto["endereco1"]);
    imagettftext( $imagem1, 10, 0, 10, 694, $preto, $arial_bold,  $dadosboleto["endereco2"]);


    //INICIO Boleto
    imagefilledrectangle($imagem1, 0, $altura_tela, $largura, $altura, $branco);
    ////////////////// (xinicial,yinicial,xfinal,yfinal,largura,altura) criado o espaço da imagem

    //imagefilledrectangle($imagem1, 0, 0, 1, $altura, $preto);/// PRETO Estreito
    $larguraFinal = $larguraAtual+$espessuraE;
    imagefilledrectangle($imagem1, $larguraInicio, $altura_tela, $larguraFinal, $altura, $preto);///preto estreito
    $larguraAtual = $larguraFinal;
    $larguraInicio = $larguraFinal;

    //imagefilledrectangle($imagem1, 1, 0, 2, $altura, $branco);// BRANCO Estreito
    $larguraFinal = $larguraAtual+$espessuraE;
    imagefilledrectangle($imagem1, $larguraInicio, $altura_tela, $larguraFinal, $altura, $branco);///Branco fino
    $larguraAtual = $larguraFinal;
    $larguraInicio = $larguraFinal;



    //imagefilledrectangle($imagem1, 2, 0, 3, $altura, $preto);/// PRETO Estreito
    $larguraFinal = $larguraAtual+$espessuraE;
    imagefilledrectangle($imagem1, $larguraInicio, $altura_tela, $larguraFinal, $altura, $preto);///preto estreito
    $larguraAtual = $larguraFinal;
    $larguraInicio = $larguraFinal;

    //imagefilledrectangle($imagem1, 3, 0, 4, $altura, $branco);// Branco Estreito
    $larguraFinal = $larguraAtual+$espessuraE;
    imagefilledrectangle($imagem1, $larguraInicio, $altura_tela, $larguraFinal, $altura, $branco);///Branco fino
    $larguraAtual = $larguraFinal;
    $larguraInicio = $larguraFinal;

    //FIM INICIO Boleto

    //// configurações de acordo com o numero do boleto
    ///////////////////////////////////////////////// setando as representações para cada numero do boleto

    foreach ($codigo as $i => $valor) {

        switch ($valor) {
            case 0:
                $codigoRepre[$i]="00110";
            break;
            case 1:
                $codigoRepre[$i]= "10001";
            break;
            case 2:
                $codigoRepre[$i]= "01001";
            break;
            case 3:
                $codigoRepre[$i]= "11000";
            break;
            case 4:
                $codigoRepre[$i]= "00101";
            break;
            case 5:
                $codigoRepre[$i]= "10100";
            break;
            case 6:
                $codigoRepre[$i]= "01100";
            break;
            case 7:
                $codigoRepre[$i]= "00011";
            break;
            case 8:
                $codigoRepre[$i]= "10010";
            break;
            case 9:
                $codigoRepre[$i]= "01010";
            break;
        }
    }

    $h=1; // pegar o valor do proximo codigoRepre para montar os pares
    $pular=false; // pegar de dois em dois montando os pares
    $cor = "branco";

    foreach ($codigoRepre as $j => $valorCodigo) {

    if($pular==true) {
        $pular = false;
        }else{
            $h=$j;
            $h++;
            $codigoRepreNext = $codigoRepre[$j].$codigoRepre[$h];// juntando os pares

            //echo "<br>Teste C?digos: $codigoRepreNext";
            $codigoPar = str_split($codigoRepre[$j]);
            $codigoImpar = str_split($codigoRepre[$h]);

            foreach($codigoPar as $l => $valorPar) {
            $pares = $codigoPar[$l].$codigoImpar[$l];

            switch($pares) {

            case '01':// EL

                if($cor=="branco") {
                    $corBarra = imagecolorallocate($imagem1, 0, 0, 0);//preto
                    $cor = "preto";
                }else {
                        $corBarra = imagecolorallocate($imagem1, 255, 255, 255);//branco
                        $cor ="branco";
                    }

                $larguraFinal = $larguraAtual+$espessuraE;
                imagefilledrectangle($imagem1, $larguraInicio, $altura_tela, $larguraFinal, $altura, $corBarra);
                $larguraAtual = $larguraFinal;
                $larguraInicio = $larguraFinal;

                if($cor=="branco") {
                    $corBarra = imagecolorallocate($imagem1, 0, 0, 0);//preto
                    $cor = "preto";
                }else {
                    $corBarra = imagecolorallocate($imagem1, 255, 255, 255);//branco
                    $cor ="branco";
                }

                $larguraFinal = $larguraAtual+$espessuraL;
                imagefilledrectangle($imagem1, $larguraInicio, $altura_tela, $larguraFinal, $altura, $corBarra);
                $larguraAtual = $larguraFinal;
                $larguraInicio = $larguraFinal;
                break;

            case '00': //EE

                if($cor=="branco") {
                    $corBarra = imagecolorallocate($imagem1, 0, 0, 0);//preto
                    $cor = "preto";
                }else {
                    $corBarra = imagecolorallocate($imagem1, 255, 255, 255);//branco
                    $cor ="branco";
                }
                $larguraFinal = $larguraAtual+$espessuraE;
                imagefilledrectangle($imagem1, $larguraInicio, $altura_tela, $larguraFinal, $altura, $corBarra);
                $larguraAtual = $larguraFinal;
                $larguraInicio = $larguraFinal;

                if($cor=="branco") {
                    $corBarra = imagecolorallocate($imagem1, 0, 0, 0);//preto
                    $cor = "preto";
                }else {
                    $corBarra = imagecolorallocate($imagem1, 255, 255, 255);//branco
                    $cor ="branco";
                }
                $larguraFinal = $larguraAtual+$espessuraE;
                imagefilledrectangle($imagem1, $larguraInicio, $altura_tela, $larguraFinal, $altura, $corBarra);
                $larguraAtual = $larguraFinal;
                $larguraInicio = $larguraFinal;
                break;

            case '10': //LE

                if($cor=="branco") {
                    $corBarra = imagecolorallocate($imagem1, 0, 0, 0);//preto
                    $cor = "preto";
                }else {
                    $corBarra = imagecolorallocate($imagem1, 255, 255, 255);//branco
                    $cor ="branco";
                }
                $larguraFinal = $larguraAtual+$espessuraL;
                imagefilledrectangle($imagem1, $larguraInicio, $altura_tela, $larguraFinal, $altura, $corBarra);
                $larguraAtual = $larguraFinal;
                $larguraInicio = $larguraFinal;

                if($cor=="branco") {
                    $corBarra = imagecolorallocate($imagem1, 0, 0, 0);//preto
                    $cor = "preto";
                }else {
                    $corBarra = imagecolorallocate($imagem1, 255, 255, 255);//branco
                    $cor ="branco";
                }
                $larguraFinal = $larguraAtual+$espessuraE;
                imagefilledrectangle($imagem1, $larguraInicio, $altura_tela, $larguraFinal, $altura, $corBarra);
                $larguraAtual = $larguraFinal;
                $larguraInicio = $larguraFinal;
                break;

            case '11': //LL

                if($cor=="branco") {
                    $corBarra = imagecolorallocate($imagem1, 0, 0, 0);//preto
                    $cor = "preto";
                }else {
                    $corBarra = imagecolorallocate($imagem1, 255, 255, 255);//branco
                    $cor ="branco";
                }
                $larguraFinal = $larguraAtual+$espessuraL;
                imagefilledrectangle($imagem1, $larguraInicio, $altura_tela, $larguraFinal, $altura, $corBarra);
                $larguraAtual = $larguraFinal;
                $larguraInicio = $larguraFinal;

                if($cor=="branco") {
                    $corBarra = imagecolorallocate($imagem1, 0, 0, 0);//preto
                    $cor = "preto";
                }else {
                    $corBarra = imagecolorallocate($imagem1, 255, 255, 255);//branco
                    $cor ="branco";
                }
                $larguraFinal = $larguraAtual+$espessuraL;
                imagefilledrectangle($imagem1, $larguraInicio, $altura_tela, $larguraFinal, $altura, $corBarra);
                $larguraAtual = $larguraFinal;
                $larguraInicio = $larguraFinal;
                break;
                }

                }
                $pular=true;
        }
    }

    //////////////////FINAL BOLETO

    $larguraFinal = $larguraAtual+$espessuraL;
    imagefilledrectangle($imagem1, $larguraInicio, $altura_tela, $larguraFinal, $altura, $preto);///preto Largo
    $larguraAtual = $larguraFinal;
    $larguraInicio = $larguraFinal;

    $larguraFinal = $larguraAtual+$espessuraE;
    imagefilledrectangle($imagem1, $larguraInicio, $altura_tela, $larguraFinal, $altura, $branco);///Branco fino
    $larguraAtual = $larguraFinal;
    $larguraInicio = $larguraFinal;

    $larguraFinal = $larguraAtual+$espessuraE;
    imagefilledrectangle($imagem1, $larguraInicio, $altura_tela, $larguraFinal, $altura, $preto);///preto fino
    $larguraAtual = $larguraFinal;
    $larguraInicio = $larguraFinal;

    $larguraFinal = $larguraAtual+$espessuraL;
    imagefilledrectangle($imagem1, $larguraInicio, $altura_tela, $larguraFinal, $altura, $branco);
    $larguraAtual = $larguraFinal;
    $larguraInicio = $larguraFinal;
    

     // caso queira salvar os arquivos
    //$folder_path = $_SERVER['DOCUMENT_ROOT'].'/prepag2/incs/rf_cpf/captcha/';
    //$file_name = 'barcode.png';
    //imagegif($imagem1,$folder_path.$file_name, NULL, 9);// criar
    
    if ( !function_exists('formata_codigo_venda') ) {
        function formata_codigo_venda($codigo) {
            return str_pad($codigo, 8, "0", STR_PAD_LEFT);
        }
    }
    
    function geraBoletoEnviaEmail($envioEmail) {
        global $imagem1;
        ob_clean();
        ob_start();
        imagegif($imagem1, NULL,9);// criar
        $gif = ob_get_clean();

        $envioEmail->MontaEmailEspecifico($gif, true, 'boleto.gif');
        
        echo $gif;
        imagedestroy($imagem1);
    }
   
    if (array_key_exists('boleto_imagem', $GLOBALS['_SESSION']) ) {
        switch ( $GLOBALS['_SESSION']['boleto_imagem'] ) {
            case 'AdicaoSaldoLan':
                $envioEmail = new EnvioEmailAutomatico(TIPO_USUARIO_LAN, $GLOBALS['_SESSION']['boleto_imagem']);
                $saldoAdicionado = number_format($total_geral, 2, ',', '.');
                $envioEmail->setUgID($ug_id);
                $envioEmail->setPedido(formata_codigo_venda($venda_id));
                $envioEmail->setSaldoAdicionado($GLOBALS['_SESSION']['saldoAdicionado']);
                $envioEmail->setFormaPagamento('Boleto Bancário');

                geraBoletoEnviaEmail($envioEmail);
                break;
            case 'AdicaoSaldoGamer':
                $usuarioGames = unserialize($GLOBALS['_SESSION']['usuarioGames_ser']);
                $envioEmail = new EnvioEmailAutomatico(TIPO_USUARIO_GAMER, $GLOBALS['_SESSION']['boleto_imagem']);
                $envioEmail->setUgID($usuarioGames->getId());
                $envioEmail->setSaldoAdicionado($GLOBALS['_SESSION']['valor_pedido_gamer']);
                $envioEmail->setFormaPagamento('Boleto');
                $envioEmail->setPedido(formata_codigo_venda($venda_id));
                geraBoletoEnviaEmail($envioEmail);
                unset($GLOBALS['_SESSION']['valor_pedido_gamer']);
                break;
            case 'PedidoRegistrado':
                if (array_key_exists('aux_lista_oferta', $GLOBALS['_SESSION']) ) {
                    $envioEmail = new EnvioEmailAutomatico(TIPO_USUARIO_GAMER, $GLOBALS['_SESSION']['boleto_imagem']);
                    $envioEmail->setUgID($ug_id);
                    $envioEmail->setListaCreditoOferta($GLOBALS['_SESSION']['aux_lista_oferta']);
                    $envioEmail->setPedido(formata_codigo_venda($venda_id));
                    //$objEnvioEmailAutomatico->MontaEmailEspecifico();
                    geraBoletoEnviaEmail($envioEmail);
                    unset($GLOBALS['_SESSION']['aux_lista_oferta']);
                } else {
                    imagegif($imagem1, NULL,9);// criar
                    imagedestroy($imagem1);
                }
                break;
            case 'PedidoRegistradoInt':
                if (array_key_exists('aux_lista_oferta', $GLOBALS['_SESSION']) ) {
                    $envioEmail = new EnvioEmailAutomatico(TIPO_USUARIO_GAMER, $GLOBALS['_SESSION']['boleto_imagem']);
                    $envioEmail->setUgID($ug_id);
                    $envioEmail->setUgEmail($usuarioGames->getEmail());                    
                    $envioEmail->setListaCreditoOferta($GLOBALS['_SESSION']['aux_lista_oferta']);
                    $envioEmail->setPedido(formata_codigo_venda($venda_id));
                    geraBoletoEnviaEmail($envioEmail);
                    unset($GLOBALS['_SESSION']['aux_lista_oferta']);
                } else {
                    imagegif($imagem1, NULL,9);// criar
                    imagedestroy($imagem1);
                }
                break;
            case 'PedidoRegistradoEx': // Boleto Express
                $envioEmail = new EnvioEmailAutomatico(TIPO_USUARIO_GAMER, $GLOBALS['_SESSION']['boleto_imagem']);
                $envioEmail->setUgEmail($GLOBALS['_SESSION']['EmailTO']);
                $envioEmail->setListaCreditoOferta($GLOBALS['_SESSION']['EmailOfertas']);
                $envioEmail->setToken($GLOBALS['_SESSION']['EmailToken']);
                $envioEmail->setPedido(formata_codigo_venda($venda_id));
                geraBoletoEnviaEmail($envioEmail);
                unset($GLOBALS['_SESSION']['EmailTO']);
                unset($GLOBALS['_SESSION']['EmailToken']);
                unset($GLOBALS['_SESSION']['EmailOfertas']);
                break;
            default:
                imagegif($imagem1, NULL,9);// criar
                imagedestroy($imagem1);
                break;
        }
        unset($GLOBALS['_SESSION']['boleto_imagem']);
    } else {
        imagegif($imagem1, NULL,9);// criar
        imagedestroy($imagem1);
    }
?>