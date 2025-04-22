<?php
/*
 * Classe FilePipe
 *
 * Classe contém os metodos para geração de arquivos com campos
 * separado por Pipe (ex.: Nome|Endereco|Bairro|Cidade||Estado)
 *
 */
class FilePipe {

	private $fileName;
	private $line;
	private $file;
	private $dir;
        private $new_dir;               // variavel de controle para a criação de um subdiretório para salvo o arquivo no formato date('Ymd')
        private $vetorLines = array();  // cada posição do vetor deve conter a subestrutura [key][name] e [key][size]
        private $diretorio_temporario_windows = "/www/tmp/";

	function __construct($fileName, $dir = null) {
		$this->setFileName(strtoupper($fileName));
                $this->setDir($dir);
	}
        
	function setFileName($fileName) {
 		$this->fileName = $fileName;
	}
	function getFileName(){
            return $this->fileName;
        }

	function setDir($dir) {
 		$this->dir = $dir;
	}
	function getDir(){
            return $this->dir;
        }

	function setNewDir($new_dir) {
 		$this->new_dir = $new_dir;
	}
	function getNewDir(){
            return $this->new_dir;
        }

	function setLine($line) {
                $this->line = $line;
                $this->setFile();
	}
	function getLine(){
            return $this->line;
        }

	function cleanFile() {
 		$this->file = "";
	}
	function setFile() {
 		$this->file .= $this->getLine()."|".PHP_EOL;
	}
	function getFile(){
            return $this->file;
        }

	function setVetorLines($vetorLines) {
 		$tmp = "";                   // variável contendo dados temporários
 		$this->vetorLines = $vetorLines;
                // cada posição do vetor deve conter a subestrutura [key][name] e [key][size]
                foreach ($this->vetorLines as $key => $dados) {
                       //para não converter automaticamente a string para maiusculo deve ser informado o parametro 'upper' no vetor dados como 'nao'
                       $tmp .= ($key == 0?"|":"").$this->format_string($dados['name'], $dados['size'],((isset($dados['upper']))?FALSE:TRUE),(((count($this->vetorLines)-1) > $key)?FALSE:TRUE));
                }//end foreach
                $this->setLine($tmp);
	}
	function getVetorLines(){
            return $this->vetorLines;
        }

	//Função para preencher com espaços as strings que não completam o valor exigido.
	function format_string($name, $size, $toUpper, $endLine = false) {
                if($toUpper) {
                    $name = strtoupper($name);
                }
                else {
                    $name = strtolower($name);
                }
		$ret = substr(trim($name),0,$size).($endLine?"":"|");
		return $ret;
	}

        function saveFile($new_dir = false, $modeloWindows = null){
                $this->setNewDir($new_dir);
		//Arquivo
                if(is_null($this->getDir())) {
                    $file = $GLOBALS['raiz_do_projeto'] . 'backoffice/dimp/';
                }
                else {
                    $file = $this->getDir();
                }
                if($this->getNewDir()) {
                    $file .= date('Ymd').'/';
                    if(!is_dir($file)) {
                        mkdir($file, 0700);
                    }
                }
                $file .= strtoupper($this->getFileName());
		//Grava o arquivo
                if ($handle = fopen($file, 'w')) {
                        $tempFile = $this->getFile();
                        if(!is_null($modeloWindows)) {
                            $tempFile = str_replace(PHP_EOL, "\r\n", $tempFile);
                        }//end if(!is_null($modeloWindows))
                        fwrite($handle, $tempFile);
                        fclose($handle);
                        unset($tempFile);
		}
	}//end function saveFile

        function checkFile(){
		//Arquivo
                if(is_null($this->getDir())) {
                    $file = $GLOBALS['raiz_do_projeto'] . 'backoffice/dimp/';
                }
                else {
                    $file = $this->getDir();
                }
                if($this->getNewDir()) {
                    $file .= date('Ymd').'/';
                }
                $file .= strtolower($this->getFileName());
		//Teste se existe
		if (file_exists($file)) {
                    return true;
		}
                else {
                    return false;
                }
	}//end function saveFile

        function checkNumber($number){
                $reg = '/(^[0-9.,]+$)/';
                if (preg_match($reg, $number)) {
                    return TRUE;
                }
                else {
                    return FALSE;
                }
        }//end function checkNumber

        function createZip ($files = array(), $new_dir = false, $deleleOriginal = false, $debug = false) {
            
                //Define o caminho e nome do arquivo
                $this->setNewDir($new_dir);
		if(is_null($this->getDir())) {
                    $file = $GLOBALS['raiz_do_projeto'] . 'backoffice/dimp/';
                }
                else {
                    $file = $this->getDir();
                }
                if($this->getNewDir()) {
                    $file .= date('Ymd').'/';
                    if(!is_dir($file)) {
                        mkdir($file, 0700);
                    }
                }
                $directory = $file;
                $file = strtolower($this->getFileName());

                // Removendo arquivo anterior existente com mesmo nome
                if(file_exists($directory.$file)) {
                    unlink($directory.$file);
                }

                // Cria o arquivo .zip
                $zip = new ZipArchive;
                echo (($debug)?"declarando[".$this->escreveStatus($zip->status)."]<br>".PHP_EOL:"");
                echo (($debug)?"Name File[".$this->diretorio_temporario_windows.$file."]<br>".PHP_EOL:"");
                $res = $zip->open($this->diretorio_temporario_windows.$file, ZipArchive::CREATE);
                if ($res === TRUE) {
                    echo (($debug)?"SUCESSO ao Abrir o Arquivo [".$this->diretorio_temporario_windows.$file."]<br>".PHP_EOL:"");
                }
                else {
                    echo (($debug)?"ERRO ao Abrir o Arquivo [".$this->diretorio_temporario_windows.$file."]<br>".PHP_EOL:"");
                }

                // Checa se o array não está vazio e adiciona os arquivos
                if ( count($files) > 0 ) {
                        // Loop do(s) arquivo(s) enviado(s) 
                        foreach ( $files as $key => $value ) {
                            
                                // Adiciona os arquivos ao zip criado
                                if(!$zip->addFile($directory.strtolower($value), $value)) {
                                    echo (($debug)?"ERRO ao Adicionar Arquivo [".$directory.$value."]<br>".PHP_EOL:"");
                                }
                                else echo (($debug)?"SUCESSO ao Adicionar Arquivo [".$directory.$value."]<br>".PHP_EOL:"");
                
                                // Verifica se $deleleOriginal está setada como true, se sim, apaga os arquivos
                                if ( $deleleOriginal === true ) {
                                        // Apaga o arquivo
                                        unlink($directory.$value);
                                }
                        }//end foreach

                } //end  if ( count( $files ) > 0 )

                //Fecha o arquivo zip
                $zip->close();
                echo (($debug)?"close [".$this->escreveStatus($zip->status)."]<br>".PHP_EOL:"");
                echo (($debug)? var_dump($this->diretorio_temporario_windows.$file, $directory,$file,get_current_user(),ZipArchive::CREATE):"");
                if(file_exists($this->diretorio_temporario_windows.$file)) echo ($debug)?"SUcesso no Arquivo Gerado [".$this->diretorio_temporario_windows.$file."]!<br>":"";
                else echo ($debug)?"NO FILE [".$this->diretorio_temporario_windows.$file."]!!!!!!<br>":"";
                rename($this->diretorio_temporario_windows.$file, $directory.$file);
                @unlink($this->diretorio_temporario_windows.$file);
                
        } //end function createZip 

        function escreveStatus($status) {
                switch((int) $status)  {
                   case ZipArchive::ER_OK           : return 'N No error';
                   case ZipArchive::ER_MULTIDISK    : return 'N Multi-disk zip archives not supported';
                   case ZipArchive::ER_RENAME       : return 'S Renaming temporary file failed';
                   case ZipArchive::ER_CLOSE        : return 'S Closing zip archive failed';
                   case ZipArchive::ER_SEEK         : return 'S Seek error';
                   case ZipArchive::ER_READ         : return 'S Read error';
                   case ZipArchive::ER_WRITE        : return 'S Write error';
                   case ZipArchive::ER_CRC          : return 'N CRC error';
                   case ZipArchive::ER_ZIPCLOSED    : return 'N Containing zip archive was closed';
                   case ZipArchive::ER_NOENT        : return 'N No such file';
                   case ZipArchive::ER_EXISTS       : return 'N File already exists';
                   case ZipArchive::ER_OPEN         : return 'S Can\'t open file';
                   case ZipArchive::ER_TMPOPEN      : return 'S Failure to create temporary file';
                   case ZipArchive::ER_ZLIB         : return 'Z Zlib error';
                   case ZipArchive::ER_MEMORY       : return 'N Malloc failure';
                   case ZipArchive::ER_CHANGED      : return 'N Entry has been changed';
                   case ZipArchive::ER_COMPNOTSUPP  : return 'N Compression method not supported';
                   case ZipArchive::ER_EOF          : return 'N Premature EOF';
                   case ZipArchive::ER_INVAL        : return 'N Invalid argument';
                   case ZipArchive::ER_NOZIP        : return 'N Not a zip archive';
                   case ZipArchive::ER_INTERNAL     : return 'N Internal error';
                   case ZipArchive::ER_INCONS       : return 'N Zip archive inconsistent';
                   case ZipArchive::ER_REMOVE       : return 'S Can\'t remove file';
                   case ZipArchive::ER_DELETED      : return 'N Entry has been deleted';

                   default: return sprintf('Unknown status %s', $status );
               }//end switch
           }//end function escreveStatus($status)
           
} //end class
?>
