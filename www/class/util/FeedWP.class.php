<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once  "Log.class.php";
require_once  "Util.class.php";
require_once  "Json.class.php";

class FeedWP extends Json{
    
    private $_imagesDir = "imagens/blog/";
    
    public function generate($urlJsonBlog){
        
        try{
            $rss = new DOMDocument();
            $xml = file_get_contents($urlJsonBlog);
            if(!@$rss->loadHTML(utf8_decode($xml))){
                die(date('Y-m-d H:i:s').PHP_EOL."Não foi possível acessar o LINK: ".$urlJsonBlog.PHP_EOL);
                $this->generate($urlJsonBlog);
            }
       
            $feed = array();

            foreach ($rss->getElementsByTagName('item') as $ind => $node) {
                
                $img[0] = $this->trataImg($this->getImg(utf8_decode($node->getElementsByTagName('description')->item(0)->nodeValue)));
                
                $item = array ( 
                            'title' => htmlentities(utf8_decode($node->getElementsByTagName('title')->item(0)->nodeValue)),
                            'src' => $img,
                            'link' => $node->getElementsByTagName('link')->item(0)->nodeValue,
                            'date' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue,
                            'content' => strip_tags($node->getElementsByTagName('encoded')->item(0)->nodeValue)
                            );
                array_push($feed, $item);
                
                if($ind == MAX_FEEDS_JSON)
                    break;
            }

            if(!$this->refresh($feed)){
                $msg = PHP_EOL."<br> Erro ao atualizar json rss-blog (gamer) - ".date("Y-m-d H:i:s");
                throw new Exception($msg);
            }else{
                return true;
            }
            
        } catch (Exception $ex) {
            $geraLog = new Log("FEEDRSSWP",array("ERROR: ".$ex->getMessage(),
                                              "FILE: ".$ex->getFile(),
                                              "LINE ".$ex->getLine()));
                return false;
        }
    }
    
    public function trataImg($imagem, $cont = 0){
        $img = "";
        $erro = false;
        
        if(isset($imagem[0]) && $imagem[0] != ""){
            
            $img = explode("/",$imagem[0]);
            $img = end($img);
            $dir = DIR_WEB.$this->_imagesDir;
            $destino = $dir.$img;
            //copiando arquivo do diretorio do wordpress para nosso servidor
            if(!file_exists($dir)){
                $erro = true;
                $subject   = "ERRO AO ABRIR DIRETÓRIO $dir.";
                $body_html = "<p>ERRO AO ABRIR DIRETÓRIO.</p>
                              <p>PROVAVELMENTE ELE NÃO EXISTE, FAVOR VERIFICAR O CAMINHO $dir</p>";
                
            }else if(!@copy($imagem[0],$destino)){
                
                if($cont <= 10){
                    $cont++;
                    $this->trataImg($imagem,$cont);
                    
                }else{
                    $erro = true;
                    $subject   = "ERRO AO GRAVAR ARQUIVOS NO DIRETÓRIO $dir.";
                    $body_html = "<p>Excedido o número de tentativas para gravar o arquivo ".$imagem[0].". </p>";
                    
                }
                
            }
            
            if($erro === true && function_exists("enviaEmail4")){
                $to = "tamy@e-prepag.com.br, wagner@e-prepag.com.br";
                $cc = null;
                $bcc = false;
                $body_plain = null;
                $body_html .= "<p>" .date("d-m-Y H:i"). "</p>";
                enviaEmail4($to, $cc, $bcc, $subject, $body_html, $body_plain);

                throw new Exception($body_html);

            }
            
        }
        
        return "/".$this->_imagesDir.$img;
        
    }

    public function getImg($img){
        $doc = new DOMDocument();
		//var_dump($img);
		if($img != ""){
	        $doc->loadHTML($img);
			$xml=simplexml_import_dom($doc); // just to make xpath more simple
			$images=$xml->xpath('//img');
			unset($doc);
			unset($xml);
			foreach ($images as $img) {
				return Util::object_to_array($img['src'][0]);
			}
		}
       
    }
    
    public function cleanDir($files){
        $path = $this->getFullPath();
        try{
            
            foreach($files as $file){
                $content = Util::jsonVerify($path.$file);
                if($content){
                    foreach($content as $ind => $val){
                        $fullPathImg = $val->src[0];
                        $arrPathImg = explode("/",$fullPathImg);
                        $img = end($arrPathImg);
                        $arrImg[$img] = $img;
                    }
                }
            }
            
            if(!file_exists(DIR_WEB.$this->_imagesDir))
                throw new Exception ("DIRETÓRIO '".DIR_WEB.$this->_imagesDir."' NÃO EXISTE.");
            
            $diretorio = dir(DIR_WEB.$this->_imagesDir);
            $logArquivos = "";
            while(($arquivo = $diretorio->read()) !== false){
                
                if(!in_array($arquivo,$arrImg) && $arquivo != "." && $arquivo != ".."){
                    
                    if(unlink(DIR_WEB.$this->_imagesDir.$arquivo)){
                        
                        $t = true;
                        $logArquivos .= "Arquivo ".DIR_WEB.$this->_imagesDir.$arquivo." removido com sucesso.<br>".PHP_EOL;
                    }else{
                        
                        $t = true;
                        $logArquivos .= "Erro ao remover arquivo ".DIR_WEB.$this->_imagesDir.$arquivo.".<br>".PHP_EOL;
                    }
                }
            }
            
            if(isset($t)){
                echo $logArquivos;
            }else{
                echo "Nenhum arquivo para ser apagado. <br>".PHP_EOL;
            }

            $diretorio->close();
            return true;
            
        } catch (Exception $ex) {
            echo "Erro: ".$ex->getMessage()."<br>".PHP_EOL;
            return false;
        }
        
        
    }
}