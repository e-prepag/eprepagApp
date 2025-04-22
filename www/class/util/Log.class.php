<?php
/**
 * Classe que gera logs de erros
 *
 * @author Diego
 */
class Log {
    private $dir;
    private $fileName;
    private $message;
    
    public function __construct($fileName, $message){
        global $raiz_do_projeto;
        $this->fileName = $fileName.".txt";
        $this->message = date("Y-m-d H:i:s")." - ".implode(PHP_EOL,$message).PHP_EOL;
        
        $this->dir = (isset($raiz_do_projeto)) ?
                                        $raiz_do_projeto.'log/' :
                                        '/www/log/';
        $this->generate();
    }
    
    private function generate(){
        if($f = fopen($this->dir.$this->fileName,"a")){
            if(!fwrite($f,$this->message))
            fclose($f);
        }else{
            return false;
        }
            
        
    }
}
