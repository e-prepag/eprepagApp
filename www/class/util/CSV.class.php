<?php
/**
 * Script que gera relatório geral das vendas em csv
 */

class CSV {
    private $dados = array();
    
    public $cabecalho = "";
    
    private $subCabecalho = "";
    
    private $espacamento = "";
    
    private $csv = "";
    
    private $endLine = PHP_EOL;
    
    private $nomeCsv = "";
    
    private $dir = "";

    public function __construct($cabecalho, $nomeCsv, $dir ,$subCabecalho = "", $espacamento = ""){
        $this->cabecalho = $cabecalho;
        $this->nomeCsv = $nomeCsv.".csv";
        $this->dir = $dir;
        $this->subCabecalho = $subCabecalho;
        $this->espacamento = $espacamento;
        
    }
    
    public function setLine($str){
        $this->csv .= $str.$this->endLine;
    }
    
    public function setCabecalho(){
        $this->csv = $this->cabecalho.$this->endLine;
    }
    
    public function addEspacamento(){
        $this->csv .= $this->espacamento;
    }
    
    public function addSubCabecalho(){
        $this->csv .= $this->subCabecalho.$this->endLine;
    }
    
    public function quebraLinha(){
        $this->csv .= $this->endLine;
    }
    
    public function getCsv(){
        return $this->csv;
    }


    public function export(){

        try{
            if($this->csv != ""){
                $f = fopen($this->dir.$this->nomeCsv,"w+");
                if($f){
                    
                    if(!fwrite($f, $this->csv)){ //html_entity_decode($string, ENT_QUOTES, "utf-8");
                        throw new Exception('FALHA AO GERAR ARQUIVO.');
                    }else{
                        fclose($f);
                        return $this->nomeCsv;
                    }
                }
            }
        } catch (Exception $ex) {
            fclose($f);
//            $geraLog = new Log("CSV",array("ERROR: ".$ex->getMessage(),
//                                              "FILE: ".$ex->getFile(),
//                                              "LINE ".$ex->getLine()));
            return false;
        }

    }
    
    private function limpaCsvCache(){
        $dh  = opendir($this->dir);
        try{
            if (is_dir($this->dir)) {
                while (false !== ($filename = readdir($dh))) 
                {
                    if(substr($filename,-4,4) != ".csv")
                        continue;

                    $filename = $this->dir.$filename;
                    if(file_exists($filename) && date("Y-m-d", filemtime($filename)) < date("Y-m-d")){
                        chmod($filename, 0777);
                        if(unlink ($filename) ===false)
                            throw new Exception($filename);
                    }
                }
            }else{
                echo "<h4 style='color: red;'> O diretório $this->dir não existe. Falha na geração do csv.</h4>";
            }
        } catch (Exception $ex) {
//            $geraLog = new Log("CSV",array("ERROR: ".$ex->getMessage(),
//                                              "FILE: ".$ex->getFile(),
//                                              "LINE ".$ex->getLine()));
            return false;
        }
    }
    
    public function __destruct(){
        $this->limpaCsvCache();
    }
    
}
