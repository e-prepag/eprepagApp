<?php

            $sessao_curl = curl_init();

            curl_setopt($sessao_curl, CURLOPT_URL, $this->getLocation().$catalog);

            curl_setopt($sessao_curl, CURLOPT_FAILONERROR, false);

            //Setando o Agente do CURL

            curl_setopt($sessao_curl, CURLOPT_USERAGENT, 'curl 7.47.0 (x86_64-pc-linux-gnu) libcurl/7.47.0 OpenSSL/1.0.2g zlib/1.2.8');

            $headers = array();



            $headers[] = "Host: ".  str_replace("https://", "", "https://api.blackhawknetwork.com");



            $headers[] = "Accept: application/json; charset=UTF-8";



            $headers[] = "Content-Type: application/json; charset=UTF-8";



            $headers[] = "requestorId: ZHYMPVWF7DT5TC2TP3HH2DGFY0";



            if(!is_null($json)) {



                $auxJson = json_decode($json);



                if(isset($auxJson->recipientId) && $auxJson->recipientId != "") {



                   $headers[] = "requestId: ".$auxJson->recipientId;



                   unset($auxJson->recipientId);



                   $json = json_encode($auxJson);



                }//end if(isset($auxJson->recipientId) && $auxJson->recipientId != "")



            }//end if(!is_null($json) && isset($json->recipientId) && $json->recipientId != "")
            curl_setopt($sessao_curl, CURLOPT_HTTPHEADER, $headers); //Authorization: OAuth 

            //  CURLOPT_SSL_VERIFYPEER
            //  verifica a validade do certificado

            curl_setopt($sessao_curl, CURLOPT_SSL_VERIFYPEER, 0);

            //  CURLOPPT_SSL_VERIFYHOST
            //  verifica se a identidade do servidor bate com aquela informada no certificado

            curl_setopt($sessao_curl, CURLOPT_SSL_VERIFYHOST, 0);

            //  CERTIFICANDO COM AUTORIZAЧУO POR SENHA
            //  informa a localizaчуo do certificado para verificaчуo com o peer
            curl_setopt($sessao_curl, CURLOPT_SSLCERT, "lib/ssl/Eprepag-Digital-API-Production.crt.pem");

            curl_setopt($sessao_curl, CURLOPT_SSLKEY, "lib/ssl/Eprepag-Digital-API-Production.key.pem");

            curl_setopt($sessao_curl, CURLOPT_SSLCERTPASSWD, "8V39WP5F8358JL1GQZTLSCRF24");              

            curl_setopt($sessao_curl, CURLOPT_SSLKEYPASSWD, "8V39WP5F8358JL1GQZTLSCRF24"); 
			
            curl_setopt($sessao_curl, CURLOPT_SSLVERSION, 6);

            curl_setopt($sessao_curl, CURLOPT_SSL_CIPHER_LIST, 'ECDHE-RSA-AES128-GCM-SHA256,ECDHE-ECDSA-AES128-SHA');

            //  CURLOPT_CONNECTTIMEOUT
            //  o tempo em segundos de espera para obter uma conexуo
            curl_setopt($sessao_curl, CURLOPT_CONNECTTIMEOUT, 0);

            //  CURLOPT_TIMEOUT

            //  o tempo mсximo em segundos de espera para a execuчуo da requisiчуo (curl_exec)

            curl_setopt($sessao_curl, CURLOPT_TIMEOUT, ((90000/1000)+60));
            //  CURLOPT_RETURNTRANSFER
            //  TRUE para curl_exec retornar uma string de resultado em caso de sucesso, ao
            //  invщs de imprimir o resultado na tela. Retorna FALSE se hс problemas na requisiчуo
            curl_setopt($sessao_curl, CURLOPT_RETURNTRANSFER, true);

            if(!is_null($json)) {
               curl_setopt($sessao_curl, CURLOPT_CUSTOMREQUEST, 'POST');
               curl_setopt($sessao_curl, CURLOPT_POST, true);
               curl_setopt($sessao_curl, CURLOPT_POSTFIELDS, $json );                 

            }
            else {
               curl_setopt($sessao_curl, CURLOPT_CUSTOMREQUEST, 'GET');
            }

            $errorFileLog = fopen("lib/log/log_BHN_EGIFT_WS-Errors.log", "a+");

            curl_setopt($sessao_curl, CURLOPT_VERBOSE, true);

            curl_setopt($sessao_curl, CURLOPT_STDERR, $errorFileLog);

            curl_setopt($sessao_curl, CURLOPT_HEADER, 0);

            $resultado = curl_exec($sessao_curl);

            $auxResultado = json_decode($resultado);

            // Em caso de erro libera aqui

            $info = curl_getinfo($sessao_curl);

            curl_close($sessao_curl);
			
            //Setando Resposta por timeout 
            if($info['http_code'] != 200) $resultado = '{"total":"TIMEOUT"}'; 

            var_dump($resultado);

            exit;
            return json_decode($resultado);
?>