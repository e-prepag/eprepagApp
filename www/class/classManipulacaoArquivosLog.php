<?php
class ManipulacaoArquivosLog {
    
    private $nome_arquivo;

    function __construct($argumentos) { 
        $shellcommand = implode(" ", $argumentos);
        $output_to = "log";

        $pattern = '/--log=([^ ]+)/';
        $match = array();
        if( preg_match($pattern, $shellcommand, $match) ){
            $output_to = $match[1];
        }
        $this->setNomeArquivo($output_to);

    }//end function __construct   

    private function setNomeArquivo($nome_arquivo) {
            $this->nome_arquivo = $nome_arquivo;
    }

    public function getNomeArquivo() {
            return $this->nome_arquivo;
    }

    public function createLockedFile() {
        $newfile = fopen($GLOBALS['raiz_do_projeto']."arquivos_gerados/logs/".$this->getNomeArquivo().'.locked', 'w');
        if ($newfile) {
        fwrite($newfile, getmypid());
        fclose($newfile);
        }
    }//end function createLockedFile

    public function deleteLockedFile() {
        unlink($GLOBALS['raiz_do_projeto']."arquivos_gerados/logs/".$this->getNomeArquivo().'.locked');
    }//end function deleteLockedFile

    public function haveFile() {
        if(file_exists($GLOBALS['raiz_do_projeto']."arquivos_gerados/logs/".$this->getNomeArquivo().'.locked')) {
                if($this->isProcess(trim($this->readFile()))) {
                    return true;
                } //end if($this->isProcess(trim($this->readFile())))
                else {
                    $this->deleteLockedFile();
                    return false;
                }//end else do if($this->isProcess(trim($this->readFile())))
        } //end if(file_exists($GLOBALS['raiz_do_projeto']."arquivos_gerados/logs/".$this->getNomeArquivo().'.locked'))
        else return false;
    }//end function haveFile

    public function showBusy(){
        $fp = fopen($GLOBALS['raiz_do_projeto']."arquivos_gerados/logs/".$this->getNomeArquivo().".busy","a");
        if ($fp) {
        fwrite($fp, date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] ." ==> Programa em uso.".PHP_EOL);
        fclose($fp);
        }
    }//end function showBusy
    
    private function isProcess($pid){
        $pid = (int)$pid;
        if($pid <= 0) {
            return false;
        }

        if(function_exists('posix_kill')) {
            return @posix_kill($pid, 0);
        }

        return is_dir('/proc/'.$pid);
    }//end function isProcess

    public function killProcess($pid){
        $pid = (int)$pid;
        if($pid <= 0) {
            return false;
        }

        if(function_exists('posix_kill')) {
            return @posix_kill($pid, 9);
        }

        return false;
    }//end function killProcess
    
    private function readFile() {
        $fp = fopen($GLOBALS['raiz_do_projeto']."arquivos_gerados/logs/".$this->getNomeArquivo().'.locked', "r");
        $data = fgets($fp, 1024);
        if ($fp) {
        fclose($fp);
        }
        return $data;
    }//end readFile()
    
    
}//end class ManipulacaoArquivosLog


//função para gravar o buffer do echo  através do ob_start
function callbackLog($buffer){
    global $nome_arquivo;
    $fp = fopen($GLOBALS['raiz_do_projeto']."arquivos_gerados/logs/".$nome_arquivo.".log","a");
    if ($fp) {
    fwrite($fp, $buffer);
    fclose($fp);
    }
}//end function callbackLog

?>
