<?php require_once __DIR__ . '/../../includes/constantes_url.php'; ?>
<?php
	// "1LG8j9ofhEp6DAedOzZ1V3WAbKeIGY1"
	$chave = "1rWe5Px/mdDJ8okXCFBrTMEgyF3O1A0";
    
    require_once '../../includes/constantes.php';

    include RAIZ_DO_PROJETO  . 'includes/configCEP.php';

	$gmtDate = gmdate("D, d M Y H:i:s"); 
	header("Expires: {$gmtDate} GMT"); 
	header("Last-Modified: {$gmtDate} GMT"); 
	header("Cache-Control: no-cache, must-revalidate"); 
	header("Pragma: no-cache");
	header("Content-Type: text/html; charset=ISO-8859-1",true);

	$cep = isset($_REQUEST["cep"]) ? $_REQUEST["cep"] : false;
	$slog = "LOC: BACKOFFICE - COMMERCE\n";
	$slog .= "CEP: $cep\n";
	
	if($cep) {

        if(CONSULTA_CEP === $vetorCEP['VIACEP']){
            $cep_so_numero = trim(str_replace('-', '', $cep));
            $url = "https://viacep.com.br/ws/".$cep_so_numero."/json/";
            
            $content = file_get_contents($url);
            
            if($content == FALSE){
                echo "NO_ACCESS";
                die();
            } else{
                $retorno = json_decode($content);
            }
            
            $slog .= "URL: $url\n";

            if (!is_object($retorno))
                echo "ERRO1";
            else{
                if(trim($retorno->localidade) != ""){
                    if(trim($retorno->logradouro) != ""){
                        
                        $array_logradouro = explode(" ", $retorno->logradouro);
                        $tip_end = $array_logradouro[0];
                        $end = "";

                        foreach ($array_logradouro as $ind => $value){
                            if($ind != 0){
                                $end .= $value." ";
                            }
                        }
                        $end .= $retorno->complemento;
                        
                    } else{
                        $tip_end = "";
                        $end = "";
                    }
                    echo utf8_decode ($tip_end. "&" . $end . "&" . $retorno->bairro . "&" . $retorno->localidade . "&" . $retorno->uf);
                } else{
                    echo "ERRO2";
                }
                
            }
        } elseif(CONSULTA_CEP === $vetorCEP['REPUBLICA_VIRTUAL']){
            $url = "http://cep.republicavirtual.com.br/web_cep.php?formato=json&cep=".$cep;
            $content = file_get_contents($url);
            
            if($content == FALSE){
                echo "NO_ACCESS";
                die();
            } else{
                $retorno = json_decode($content);
            }
            
            $slog .= "URL: $url\n";

            if (!is_object($retorno))
                echo "ERRO1";
            else{
                if ($retorno->resultado == "1" || $retorno->resultado == "2"){
                    echo utf8_decode ($retorno->tipo_logradouro. "&" . $retorno->logradouro . "&" . $retorno->bairro . "&" . $retorno->cidade . "&" . $retorno->uf);
                } else{
                   echo "ERRO2";
                }
            }
        } else{
            echo "NO_ACCESS";
        }
      
	}
	else{
		echo "ERRO3";
	}

$slog = str_repeat("=", 80)."\n". $slog ;
gravaLog_TMP1($slog);
	
	function gravaLog_TMP1($mensagem){
	
		//Arquivo
		$file = RAIZ_DO_PROJETO  . "log/log_TMP1.txt";
		
		//Mensagem
		$mensagem = date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . "\n" . $mensagem . "\n";

		//Grava mensagem no arquivo
		if ($handle = fopen($file, 'a+')) {
			fwrite($handle, $mensagem);
			fclose($handle);
		} 
	}

function getFileByCURL($url, $post_parameters) {

	$buffer = "";
//echo "SONDA Parameters: ".$url.", ".$post_parameters."<br>";
	// http://blog.unitedheroes.net/curl/
	$curl_handle = curl_init();
	curl_setopt($curl_handle, CURLOPT_URL,$url);

	// Some sites may protect themselves from remote logins by checking which site you came from.
	// http://php.net/manual/en/function.curl-setopt.php
	$ref_url = "" . EPREPAG_URL_HTTP . "";
	curl_setopt($curl_handle, CURLOPT_REFERER, $ref_url);
	
	// http://www.weberdev.com/get_example-4136.html
	// http://www.php.net/manual/en/function.curl-setopt.php
	curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, 0);	// true - verifica certificado
	curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, 0);	// 1 - então, também verifica nome no certificado

	curl_setopt($curl_handle, CURLOPT_HEADER, 1); 
	curl_setopt($curl_handle, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)"); 

	curl_setopt($curl_handle, CURLOPT_POST, 1);
	curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $post_parameters);

	// The number of seconds to wait while trying to connect. Use 0 to wait indefinitely.
	curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 10);		
	// The maximum number of seconds to allow cURL functions to execute.
	curl_setopt($curl_handle, CURLOPT_TIMEOUT, 10);		
	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);

	$buffer = curl_exec($curl_handle);

/*	// Em caso de erro libera aqui
	$info = curl_getinfo($curl_handle);

	if ($output === false || $info['http_code'] != 200) {
	  $output = "No cURL data returned for URL [". $info['http_code']. "]";
	  if (curl_error($curl_handle)) {
		$output .= "\n". curl_error($curl_handle);
	  }
	  echo "CRL Error: ".$output."<br>Buffer: ".$buffer."\n";	  
//echo "<pre>";
//print_r($info);
//echo "</pre>";
	} else {
	  // 'OK' status; format $output data if necessary here:
	  echo "CRL OK<br>\n";	  
	}
	// Até aqui
*/
	curl_close($curl_handle);
	if($buffer) {
		$ipos = strpos($buffer, "&cep");
		$buffer = substr($buffer, $ipos);
		gravaLog_TMP1("ipos: $ipos\n  buffer1: '".$buffer."'\n");
	}

	return $buffer;
}

?>