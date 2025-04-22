<?php

/* 
    Classe com os métodos para a entidade json
 */
require_once DIR_CLASS . "util/Util.class.php"; 
require_once DIR_CLASS . "util/Log.class.php"; 

class Json{
    private $_fullPath;
    private $_file;
    private $_arrJsonFiles;
    private $_html;

    
    function __construct(){

    }
    
    public function setFullPath($fullPath) {
        $this->_fullPath = $fullPath;
        return $this;
    }
    
    public function getFullPath() {
        return $this->_fullPath;
    }

    public function setArrJsonFiles($arrJsonFiles) {
        $this->_arrJsonFiles = $arrJsonFiles;
        return $this;
    }
    
    public function refresh($arrJson = array()){
        try{
			//$dd = json_encode($arrJson, JSON_FORCE_OBJECT);
			//var_dump($arrJson);
			//var_dump(json_last_error());
            if(!empty(json_encode($arrJson))){ //TESTANDO ERRO - URL JSON ERRADA
                
                if($this->isJson($this->_fullPath.$this->_arrJsonFiles[0]))
                    $this->moveRecursive();
                
                if($handle = fopen($this->_fullPath.$this->_arrJsonFiles[0],"w+")){
                        //faz o rodizio dos arquivos para inserir o novo conteudo no arquivo 1
                    if(fwrite($handle, json_encode($arrJson))){
                        unset($this->_file);
                        fclose($handle);
                        
                        $arquivo = $this->_fullPath.$this->_arrJsonFiles[0];
                        $destino = "E-Prepag/www/web/cache/jsonblog/" . $this->_arrJsonFiles[0];
                        if(SFTP_TRANSFER && file_exists($arquivo)){
                            global $raiz_do_projeto;
                            include $raiz_do_projeto.'sftp/connect.php';
                            require_once $raiz_do_projeto.'sftp/classSFTPconnection.php';
                            $arq = trim(str_replace('/', '\\', $arquivo));
                            //enviar para os servidores via sFTP
                            $sftp = new SFTPConnection($server, $port);
                            $sftp->login($user, $pass);
                            $sftp->uploadFile($arquivo, $destino);
                            if(isset($msg)) $msg .= "<br><br>Arquivo de configuração enviado ao servidor Windows 2003";
                            else $msg = "<br><br>Arquivo de configuração enviado ao servidor Windows 2003";

                        }
                        
                        return true;
                    }else{
                        fclose($handle);
                        throw new Exception("ERRO AO ESCREVER ARQUIVO - Path: [". $this->_fullPath.$this->_arrJsonFiles[0]."] - ".json_encode($arrJson));
                    }
                }else{
                    throw new Exception("ERRO AO ABRIR ARQUIVO - Path: [". $this->_fullPath.$this->_arrJsonFiles[0]."]");
                }
            }
            else
            {
                echo "Estava VAZIO o JSON_ENCODE (".json_encode($arrJson).")".PHP_EOL."Pode ser problema de UFT8_DECODE.".PHP_EOL."Arquivo: ".$this->_fullPath.$this->_arrJsonFiles[0].PHP_EOL;
                return true;
                //throw new Exception("ERRO PEGANDO ARQUIVO JSON ".$this->_jsonUrl);
            }    
        } catch (Exception $ex) {
                $geraLog = new Log("JSON",array("ERROR: ".$ex->getMessage(),
                                              "FILE: ".$ex->getFile(),
                                              "LINE ".$ex->getLine()));
                return false;
        }
    }
    
    /*
        Método que gera 2 cópias de um arquivo para cache
        @var $arrFiles: array de 0 a 2, com o nome dos 3 arquivos 
            - o índice '0', é o arquivo que será copiado, 
              o índice '0' que será copiado para o índice '1'
              o indice '1', que será copiado para o índice '2'
              
    */
    public function moveRecursive(){
        $this->copyJson($this->_fullPath.$this->_arrJsonFiles[1],$this->_fullPath.$this->_arrJsonFiles[2]);
        $this->copyJson($this->_fullPath.$this->_arrJsonFiles[0],$this->_fullPath.$this->_arrJsonFiles[1]);
    }
    
    public function copyJson($oldfile,$newfile){
        if(file_exists($newfile)){
            if(!unlink($newfile))
                throw new Exception("ERRO AO APAGAR ARQUIVO {$newfile}");
        }

        if($this->isJson($oldfile)){
            if(rename($oldfile, $newfile)){
                $json = Util::jsonVerify($newfile);
                return true;
            }else{
                //die("ERRO AO GRAVAR ARQUIVO {$newfile} ;");
                throw new Exception("ERRO AO RENOMEAR ARQUIVO ARQUIVO {$newfile} ;");
            }
        }
    }
    
    public static function isJson($jsonFile){
        $ret = false;
        
        if(file_exists($jsonFile)){
            if($json = file_get_contents($jsonFile)){
                if(is_object(json_decode($json)) || is_array(json_decode($json))){
                    $ret = true;
                }
            }
        }
        return $ret;
        
    }
    
    public function getJsonRecursive($currJsonFile = 0){
        try{
			
			if(filesize($this->getFullPath().$this->_arrJsonFiles[$currJsonFile]) < 5){
				return false;
			}
			
            $this->file = Util::jsonVerify($this->getFullPath().$this->_arrJsonFiles[$currJsonFile]);
            if($this->file){
                return $this->file;
            }else{
                throw new Exception;
            }

        } catch (Exception $ex) {
            $geraLog = new Log("JSON",array("ERROR: problema ao obter o arquivo ($currJsonFile): ".$this->getFullPath().$this->_arrJsonFiles[$currJsonFile],
                                              "FILE: ".$ex->getFile(),
                                              "LINE ".$ex->getLine()));
            
            //setando qual arquivo json será usado em caso de erro
            $currJsonFile++;

            if($currJsonFile <= 2){
                return $this->getJsonRecursive($currJsonFile);
            }else{
                
                if(function_exists("enviaEmail4")){
                    
                    $server_url = "www.e-prepag.com.br";
                    $to = "nathany.andrade@e-prepag.com.br, estagiario1@e-prepag.com, wagner@e-prepag.com.br";

                    if(checkIP()) {
                        $server_url = $_SERVER['SERVER_NAME'];
                        $to = "estagiario1@e-prepag.com";
                    }
                    
                    $cc = null;
                    $bcc = false;
                    $subject = "ERRO NA OBTENÇÃO DE JSON. (".$this->_arrJsonFiles[0]." / url: $server_url)";
                    $body_html = "<p>ERRO GRAVE ao obter todos os JSONs do array ".$this->_arrJsonFiles[0]. " - " .date("d/m/Y H:i"). "</p>";
                    $body_html .= "<p>Server url: $server_url</p>";
                    $body_plain = null;
                    
                    enviaEmail4($to, $cc, $bcc, $subject, $body_html, $body_plain);
                }
                    
                return false;    
            }
            
        }
    }
    
    /*
     *  Método que embeleza o Json de forma que vc consiga vê-lo na tela e navegar dentro dele tendo uma melhor visibilidade
     *  @var $content - é o conteúdo a ser visualizado (nome de arquivo ou string)
     *  @libs - false por padrão, indica que as bibliotecas necessárias para funcionamento do programa NÃO estão inclusas, então, as incluirá.
     *          se passado TRUE, ele não as incluirá;
     */
    
    public function jsonBeautifier($content, $libs = false){
        
        global $url;
        
        if(!$url)
            $url = "https://www.e-prepag.com.br";
        
        if(file_exists($content))
            $json = Util::jsonVerify($content);
        else if(is_string($content))
            $json = json_decode($content);
        
        if(!empty($json)){
            
            if(!$libs){
                $this->_html = '<script type="text/javascript" src="'.$url.'/js/jquery/jquery.js"></script>
                                <script type="text/javascript" src="'.$url.'/bootstrap/js/bootstrap.min.js"></script>
                                <link href="'.$url.'/bootstrap/css/bootstrap.min_new.css" rel="stylesheet" type="text/css" />
                                <link href="'.$url.'/css/creditos.css" rel="stylesheet" type="text/css" />';
            }
            
            $this->_html .= '<script>
                                $(function(){
                                    $(".collapse").collapse("show");
                                });
                            </script>
                            <link rel="stylesheet" type="text/css" href="'.$url.'/css/jsonbeautifier.css">
                            <div id="json">';

            $this->jsonBeautifierMain($json);
            $this->_html .= "</div>";
            
        }
        else{
            $this->_html = "";
        }
        
        return $this->_html;
        
    }
    
    private function jsonBeautifierMain($json){

        foreach($json as $ind => $arr){

            if(empty($arr))
                continue;

            if(is_string($arr)){
                $this->jsonBeautifierStr($ind, $arr);
            }

            if(is_array($arr)){
                $this->jsonBeautifierArr($ind, $arr);
            }

            if(is_object($arr)){
                $this->jsonBeautifierObj($ind, $arr);
            }

        }
    }
    
    private function jsonBeautifierArr($title, $arr){

        $id = rand();

        $this->_html .= '<li>
                <div class="beautifier-hoverable">
                <a href="#arr'.$id.'" class="glyphicon glyphicon-minus t0" data-toggle="collapse"></a>';

        if(is_string($title))
            $this->_html .=    '<span class="beautifier-property">'.$title.'</span>:';

        $this->_html .=   '<div class="beautifier-collapser"></div>
                [<span class="beautifier-ellipsis"></span>
                <ul id="arr'.$id.'" class="collapse array beautifier-collapsible">';
                        //beautifier-selected ----------- jquery hover / focus / active
        $this->jsonBeautifierMain($arr, $id);

        $this->_html .= '      </ul>
                ],';
    }
    
    private function jsonBeautifierObj($title, $arr){

        $id = rand();

        $this->_html .= '<li>
                <div class="beautifier-hoverable">
                <a href="#ob'.$id.'" class="glyphicon glyphicon-minus t0" data-toggle="collapse"></a>';

        if(is_string($title))
            $this->_html .=    '<span class="beautifier-property">'.$title.'</span>:';

        $this->_html .=   '<div class="beautifier-collapser"></div>
                {<span class="beautifier-ellipsis"></span>
                    <ul id="ob'.$id.'" class="obj collapse beautifier-collapsible t0">';

        $this->jsonBeautifierMain($arr, $id);

        $this->_html .= '      </ul>
                },';
    }
    
    private function jsonBeautifierStr($title, $arr){

        $this->_html .= "<li class=\"beautifier-li\">
                <div class=\"beautifier-hoverable\">
                    <span class=\"beautifier-property\">$title</span>: 
                    <span class=\"beautifier-type-string\">\"$arr\"</span>,
                </div>
             </li>";
    }
}