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
        $newfile = fopen($GLOBALS['raiz_do_projeto']."log/".$this->getNomeArquivo().'.locked', 'w');
        fwrite($newfile, getmypid());
        fclose($newfile);
    }//end function createLockedFile

    public function deleteLockedFile() {
        unlink($GLOBALS['raiz_do_projeto']."log/".$this->getNomeArquivo().'.locked');
    }//end function deleteLockedFile

    public function haveFile() {
        if(file_exists($GLOBALS['raiz_do_projeto']."log/".$this->getNomeArquivo().'.locked')) {
                if($this->isProcess(trim($this->readFile()))) {
                    return true;
                } //end if($this->isProcess(trim($this->readFile())))
                else {
                    $this->deleteLockedFile();
                    return false;
                }//end else do if($this->isProcess(trim($this->readFile())))
        } //end if(file_exists($GLOBALS['raiz_do_projeto']."log/".$this->getNomeArquivo().'.locked'))
        else return false;
    }//end function haveFile

    public function showBusy(){
        $fp = fopen($GLOBALS['raiz_do_projeto']."log/".$this->getNomeArquivo().".busy","a");
        fwrite($fp, date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] ." ==> Programa em uso.".PHP_EOL);
        fclose($fp);
    }//end function showBusy
    
    private function isProcess($pid){
        $plist = explode(PHP_EOL, shell_exec('ps -eo pid'));
        //echo "Lista de Processos".print_r($plist,true)." ID no arquivo: ".$pid.PHP_EOL;
        if(in_array($pid, $plist)) return true;
        else return false;
    }//end function isProcess

    public function killProcess($pid){
        shell_exec("kill -9 " . $pid);
    }//end function killProcess
    
    private function readFile() {
        $fp = fopen($GLOBALS['raiz_do_projeto']."log/".$this->getNomeArquivo().'.locked', "r");
        $data = fgets($fp, 1024);
        fclose($fp);
        return $data;
    }//end readFile()
    
    
}//end class ManipulacaoArquivosLog


//função para gravar o buffer do echo  através do ob_start
function callbackLog($buffer){
    global $nome_arquivo;
    $fp = fopen($GLOBALS['raiz_do_projeto']."log/".$nome_arquivo.".log","a");
    fwrite($fp, $buffer);
    fclose($fp);
}//end function callbackLog

?>
