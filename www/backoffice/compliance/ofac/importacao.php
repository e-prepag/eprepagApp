<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
set_time_limit(1200);
ini_set('max_execution_time', 1200); 

//Variavel de verificação de sucesso
$success = false;

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "class/util/Validate.class.php";
require_once $raiz_do_projeto . "class/util/Util.class.php";
require_once $raiz_do_projeto . "includes/complice/functions.php";

//Extensoes de arquivos do OFAC permitidos 
$OFAC_EXTENSOES = array("zip");
//Nome do arquivo OFAC zipado
$OFAC_FILE_NAME = "SDN_XML.ZIP";
//Diretório destino do arquivo do OFAC
$DIR_OFAC_ARQ_RETORNO = $raiz_do_projeto . "/backoffice/compliance/ofac/";
//Número de arquivos contidos no ZIP
$QTDE_ARQUIVOS = 1;
//Lista de arquivos a serem importados
//$LISTA_ARQUIVOS_IMPORTAR = array('cons_prim.del','cons_alt.del');
$LISTA_ARQUIVOS_IMPORTAR = array('SDN.XML');
//Lista de arquivos a serem importados
/*
$IDX_VETOR_ARQ_EXPLODE = array(
                               'cons_prim.del'  => 1,
                               'cons_alt.del'   => 3
                               );
*/
//Tipo que devem ser considerados os campos FIRST NAME e LAST NAME
$tipos_com_firstName = array("INDIVIDUAL");

?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><?php echo $sistema->item->getDescricao(); ?></li>
    </ol>
</div>
<div class="clearfix"></div>    
<?php
ob_end_flush();
flush();

//Processa acoes
//----------------------------------------------------------------------------------------------------------
$msg_aux="";
if(isset($BtnConcluir) && $BtnConcluir) {
    
        //Validacao
        $msg = "";

        //Valida arquivo
        $fileSource = $_FILES['arquivo']['tmp_name']; 
        if (($fileSource == 'none') || ($fileSource == '' )) $msg = "Nenhum arquivo fornecido.".PHP_EOL;

        //Valida extensao
        if($msg == ""){
                $fileExtensao = strtolower(substr($_FILES['arquivo']['name'], -3)); 
                if (!in_array($fileExtensao, $GLOBALS['OFAC_EXTENSOES'])) $msg = "Extensão de arquivo inválida.".PHP_EOL;
        }

        //Valida extensao
        if($msg == ""){
                if (strtoupper($_FILES['arquivo']['name']) != $OFAC_FILE_NAME) $msg = "O nome do Arquivo deveria ser $OFAC_FILE_NAME.".PHP_EOL;
        }

        //Salva arquivo
        if($msg == ""){

                $fileDest_nome = $_FILES['arquivo']['name']; 

                $fileDest = $GLOBALS['DIR_OFAC_ARQ_RETORNO'] ."tmp/". $fileDest_nome; 

                //echo "Arquivo carregado [".$fileSource."]<br>Arquivo destino [".$fileDest."]<br>";
                if (!move_uploaded_file($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.".PHP_EOL; 
                else if((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo no destino esta vazio ou inválido.".PHP_EOL;
                if(file_exists($fileSource)) unlink($fileSource);
        }
        
        //Descompactando o arquivo e verificando a data
        if($msg == ""){
                //Diretório Destino
                $dirDest = $GLOBALS['DIR_OFAC_ARQ_RETORNO'] ."tmp/";
                
                $zip = new ZipArchive; 
                if ($zip->open($fileDest)){ 
                    //verificando se contem a qtde de arquivos correta
                    $numeroArquivos = $zip->numFiles;
                    if($numeroArquivos >= $QTDE_ARQUIVOS) {
                        for($i = 0; $i < $numeroArquivos; $i++) {
                            
                            $nome_arquivos[$i] = $zip->statName($zip->getNameIndex($i));
                            $zip->extractTo($dirDest, array($zip->getNameIndex($i)));
                            //echo "<pre>".print_r($nome_arquivos[$i], true)."</pre>";

                        }//end for
                    }//end if($zip->numFiles == $QTDE_ARQUIVOS)
                    else {
                        $msg = "Arquivo compactado contem quantidade incorreta de Arquivos".PHP_EOL; 
                    }
                    $zip->close();
                } //end if ($zip->open($fileDest))
                else { 
                    $msg = "Erro ao tentar abrir o arquivo compactado".PHP_EOL; 
                } 
        }

        
        //Verificando se o arquivo já foi importado
        if($msg == ""){
            
            //capturando a data (YYYYMMDD) do arquivo gerado pelo OFAC
            $data_arquivo_ofac = date("Ymd", $nome_arquivos[0]['mtime']);
            
            //testando se diretório para gravação do arquivo existe
            if(!file_exists($GLOBALS['DIR_OFAC_ARQ_RETORNO']."/".$data_arquivo_ofac)) {
                mkdir($GLOBALS['DIR_OFAC_ARQ_RETORNO']."/".$data_arquivo_ofac, 0700);
            }//end if
            else {
                $msg = "Arquivo já importado anteriormente".PHP_EOL;
            }//end else 
        }//end 
            
        //Validações de arquivo por período, estrutura e conteudo
        if($msg == ""){
            //Buscando todos os arquivos 
            for($i = 0; $i < $numeroArquivos; $i++) {
                if(in_array(strtoupper($nome_arquivos[$i]['name']), $LISTA_ARQUIVOS_IMPORTAR)) {
                    //echo $dirDest.$nome_arquivos[$i]['name']."<br>";
                    //Abrindo arquivo do OFAC
                    $xml=simplexml_load_file($dirDest.$nome_arquivos[$i]['name']);
                    $data_dados_considerados = substr($xml->publshInformation->Publish_Date,6,4)."-".substr($xml->publshInformation->Publish_Date,0,2)."-".substr($xml->publshInformation->Publish_Date,3,2)." 00:00:00";
                    $total_de_registros = $xml->publshInformation->Record_Count;
		    $msg_aux = "Data da geração dos Dados Considerados: ".$data_dados_considerados."<br> Total de Registros: ".$total_de_registros."<br>"; //."<pre>".print_r($xml,true)."</pre>";
                    $i = 1;
                    foreach($xml->sdnEntry as $indice => $registro) {
                        if(in_array(strtoupper($registro->sdnType), $tipos_com_firstName)) {
                            $nome_aux = strtoupper(trim($registro->firstName)." ".trim($registro->lastName));
                        }
                        else {
                            $nome_aux = strtoupper(trim($registro->lastName));
                        }
                        $nome_aux = str_replace("'", "\'", $nome_aux);
                        $sql = "INSERT INTO ofac (nome, tipo_dado, data) VALUES('".$nome_aux."','".strtoupper($registro->sdnType)."','".$data_dados_considerados."');";
                        //echo $i.": ".$sql."<br>";
                        $ret = SQLexecuteQuery($sql);
                        if(!$ret) $msg = "Erro ao Inserir registro no Banco de Dados ($sql).".PHP_EOL;
                        $i++;
                    }//end foreach

                    //Verificando a quantidade de registros no arquivo
                    if($total_de_registros != ($i-1)) {
                        $msg = "Quantidade de registros diferente do total informado no cabeçalho do arquivo".PHP_EOL;
                    }
                    
                    /*
                    $fileOFAC = fopen($dirDest.$nome_arquivos[$i]['name'], 'r');
                    if($fileOFAC) {

                        while (!feof($fileOFAC)) {
                            $members[] = fgets($fileOFAC);
                        }
                        fclose($fileOFAC);
                        
                        foreach ($members as $line){
                            
                            $vetor_aux = explode('@', $line);
                            //removendo aspas duplas (")
                            $vetor_aux[$IDX_VETOR_ARQ_EXPLODE[$nome_arquivos[$i]['name']]] = str_replace('"', '', $vetor_aux[$IDX_VETOR_ARQ_EXPLODE[$nome_arquivos[$i]['name']]]);
                            echo $vetor_aux[$IDX_VETOR_ARQ_EXPLODE[$nome_arquivos[$i]['name']]]."<pre>".print_r($vetor_aux, true)."</pre>";
                            if(strstr($vetor_aux[$IDX_VETOR_ARQ_EXPLODE[$nome_arquivos[$i]['name']]], ",")) {
                                $vetor_nome_aux = explode(',',$vetor_aux[$IDX_VETOR_ARQ_EXPLODE[$nome_arquivos[$i]['name']]]);
                                $nome_aux = strtoupper(trim($vetor_nome_aux[1]." ".$vetor_nome_aux[0]));
                            echo "Nome aux: [".$nome_aux."]<br>";
                                break;
                            }
                            else $nome_aux = strtoupper(trim($vetor_aux[$IDX_VETOR_ARQ_EXPLODE[$nome_arquivos[$i]['name']]]));
                            echo "Nome aux: [".$nome_aux."]<br>";

                        }//end foreach
                        unset($members);
                        //echo $msg;
                    }//end if($fileOFAC) 
                    else $msg = "Ocorreu um erro ao tentar abrir o Arquivo do OFAC".PHP_EOL;
                    */
                }//end if(in_array($nome_arquivos[$i]['name'], $LISTA_ARQUIVOS_IMPORTAR))
            }//end for($i = 0; $i < $numeroArquivos; $i++)

            //verificando se ocorreu algum erro, se sim deleta arquivos
            if($msg != ""){

                //Removendo o arquivo zipado
                unlink($fileDest);
                //Removendo arquivos descompactados
                foreach($nome_arquivos as $key => $value){
                    unlink($dirDest.$value['name']);
                }//end foreach
                
            }//end if($msg != "")
            
        }//end if($msg == "")
        
        //Movendo arquivos para o destino final
        if($msg == ""){
                
                //Alterando destinos
                $fileSource = $fileDest;
                $fileDest = $GLOBALS['DIR_OFAC_ARQ_RETORNO']."/".$data_arquivo_ofac."/".$fileDest_nome;

                //echo "Arquivo carregado [".$fileSource."]<br>Arquivo destino [".$fileDest."]<br>";
                if (!rename($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.".PHP_EOL; 
                else if((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo no destino esta vazio ou inválido.".PHP_EOL;
                if(file_exists($fileSource)) unlink($fileSource);
                
                //verificando se ocorreu algum erro, se sim deleta arquivos
                if($msg != "") unlink($fileSource);

                //Alterando destinos
                for($i = 0; $i < $numeroArquivos; $i++) {
                    $fileDest_nome = $nome_arquivos[$i]['name']; 
                    $fileDest = $GLOBALS['DIR_OFAC_ARQ_RETORNO']."/".$data_arquivo_ofac."/". $fileDest_nome; 
                    $fileSource = $dirDest.$nome_arquivos[$i]['name'];
                
                    //echo "Arquivo carregado [".$fileSource."]<br>Arquivo destino [".$fileDest."]<br>";
                    if (!rename($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.".PHP_EOL; 
                    else if(!file_exists($fileDest)) $msg = "Arquivo no destino esta vazio ou inválido.".PHP_EOL;
                    if(file_exists($fileSource)) unlink($fileSource);
                }//end for
                
                //verificando se ocorreu algum erro, se sim deleta arquivos
                if($msg != "") unlink($fileSource);
        }

        //limpar base
        if($msg == ""){

                //Limpar dados antigos do Banco de Dados 
                $sql = "DELETE FROM ofac WHERE data < '".$data_dados_considerados."';";
                //echo $sql;
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao remover registros antigos ($sql).".PHP_EOL;
                
        }

        //fecha janela
        if($msg == ""){
                $success = true;
                $msg = "Operação efetuada com sucesso!".PHP_EOL."Arquivos validados e registros salvos no Banco de Dados.".PHP_EOL; 
        }

//die($msg." -$i-  ".$_FILES['arquivo']['name']."--".$numeroArquivos);

}//end if(isset($BtnConcluir) && $BtnConcluir)
?>
    <script>
        function fcnOnSubmit(){
            if(form1.arquivo.value==''){
                alert('Arquivo não especificado');
                return false;
            }
            else {
                return confirm('O arquivo demorará a ser processado.\nPor favor, NÃO FECHE a tela acreditando que o programa travou!');
            }
        }
    </script>
    <div class="col-md-12 txt-preto">
        <p>Selecionar o arquivo <?php echo $OFAC_FILE_NAME; ?> para importar a lista OFAC a ser considerada nos alertas e controles de Compliance.</p>
        <form name="form1" id="form1" action="" enctype="multipart/form-data" method="post" onsubmit="return fcnOnSubmit();">
            <p>
                <input type="file" name="arquivo" size="30" class="btn btn-sm btn-info pull-left"> 
                <input type="submit" name="BtnConcluir" value="Enviar" class="btn btn-sm btn-info pull-left left5" />
            </p>
        </form>
    </div>
    <div class="col-md-12 txt-vermelho">
        <p>Efetuar download do arquivo em: ftp://ofacftp.treas.gov/fac_sdn/</p>
    </div>
<?php 
if(isset($msg) && $msg != ""){ 
?>
    <div class="top20 col-md-12 msg <?php echo (!$success)?'txt-vermelho':'txt-azul-claro';?>"><?php echo ((!empty($msg)&&!$success)?'ERRO: ':'SUCESSO:<br><br>'.$msg_aux).nl2br($msg);?></div>
<?php 
} 
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</body>
</html>
