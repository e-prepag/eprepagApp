<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/gamer/constantes.php";
require_once $raiz_do_projeto . "includes/complice/functions.php";

//Variavel de verificação de sucesso
$success = false;

//Extensoes de arquivos do BACEN permitidos 
$BACEN_EXTENSOES = array("zip");
//Diretório destino do arquivo do BACEN
$DIR_BACEN_ARQ_RETORNO = $raiz_do_projeto . "backoffice/bacen/";
//Número de arquivos contidos no ZIP
$QTDE_ARQUIVOS = 1;
//Posição no arquivo dentro do ZIP File
$POSICAO_ARQUIVO = 0;
//Código do movimento gravado
$COD_MOV_SAVED = "PMTF110C";
//Identificador de Qtde de Faturas Gravadas
$TOTAL_FATURAS = "TOTAL DE FATURAS GRAVADAS";

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

//Processa acoes
//----------------------------------------------------------------------------------------------------------
if(isset($BtnConcluir) && $BtnConcluir) {
    
        //Validacao
        $msg = "";

        //Valida arquivo
        $fileSource = $_FILES['arquivo']['tmp_name']; 
        if (($fileSource == 'none') || ($fileSource == '' )) $msg = "Nenhum arquivo fornecido.".PHP_EOL;

        //Valida extensao
        if($msg == ""){
                $fileExtensao = strtolower(substr($_FILES['arquivo']['name'], -3)); 
                if (!in_array($fileExtensao, $GLOBALS['BACEN_EXTENSOES'])) $msg = "Extensão de arquivo inválida.".PHP_EOL;
        }

        //Salva arquivo
        if($msg == ""){

                $fileDest_nome = $_FILES['arquivo']['name']; 

                $fileDest = $GLOBALS['DIR_BACEN_ARQ_RETORNO'] ."tmp/". $fileDest_nome; 

                //echo "Arquivo carregado [".$fileSource."]<br>Arquivo destino [".$fileDest."]<br>";
                if (!move_uploaded_file($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.".PHP_EOL; 
                else if((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo no destino esta vazio ou inválido.".PHP_EOL;
                else @unlink($fileSource);
        }

        //Descompactando o arquivo e verificando a data
        if($msg == ""){
                //Diretório Destino
                $dirDest = $GLOBALS['DIR_BACEN_ARQ_RETORNO'] ."tmp/";
                
                $zip = new ZipArchive; 
                if ($zip->open($fileDest)){ 
                    //verificando se contem a qtde de arquivos correta
                    if($zip->numFiles == $QTDE_ARQUIVOS) {
                        for($i = 0; $i < $zip->numFiles; $i++) {
                            
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
        
        //Validações de arquivo por período, estrutura e conteudo
        if($msg == ""){
            
            //capturando a data (YYYYMMDD) do arquivo gerado pelo BACEN
            $data_arquivo_bacen = date("Ymd", $nome_arquivos[$POSICAO_ARQUIVO]['mtime']);
            
            //testando se diretório para gravação do arquivo existe
            if(!file_exists($GLOBALS['DIR_BACEN_ARQ_RETORNO']."/".$data_arquivo_bacen)) {
                mkdir($GLOBALS['DIR_BACEN_ARQ_RETORNO']."/".$data_arquivo_bacen, 0700);
            }//end if(!file_exists($GLOBALS['DIR_BACEN_ARQ_RETORNO']."/".$data_arquivo_bacen))
                
            //teste se o arquivo percente ao mês vigente
            if(date("Ym") == date("Ym", $nome_arquivos[$POSICAO_ARQUIVO]['mtime'])) {
                //echo "O arquivo corresponde ao Mês vigente<br>";

                //Abrindo arquivo do BACEN
                $fileBACEN = fopen($dirDest.$nome_arquivos[$POSICAO_ARQUIVO]['name'], 'r');
                if($fileBACEN) {

                    while (!feof($fileBACEN)) {
                        $members[] = fgets($fileBACEN);
                    }
                    fclose($fileBACEN);

                    //Varrendo linha por linha do arquivo
                    $i = 1;
                    $linhaCOD_MOV_SAVED = false;
                    $linhaTOTAL_FATURAS = false;
                    foreach ($members as $line){
                        if(strstr(strtoupper($line), $COD_MOV_SAVED)) {
                            $linhaCOD_MOV_SAVED = $i;
                        }

                        if(strstr(strtoupper($line), $TOTAL_FATURAS)) {
                            $linhaTOTAL_FATURAS = $i;
                            $auxNumero = preg_replace('/[^0-9]/','',$line);
                            //echo "[$auxNumero]<br>";
                            if(!($auxNumero > 0)) $msg = "O Total de Faturas Gravadas no Arquivo do BACEN é 0(ZERO)".PHP_EOL;
                        } //end if(strstr(strtoupper($line), $TOTAL_FATURAS))

                        //echo $line.'<br/>'; // do something with each line from text file here
                        $i++;
                    }//end foreach

                    if(!$linhaCOD_MOV_SAVED) $msg = "Não encontrou o Código de Movimento Gravado no Arquivo do BACEN".PHP_EOL;

                    if(!$linhaTOTAL_FATURAS) $msg = "Não encontrou o Total de Faturas Gravadas no Arquivo do BACEN".PHP_EOL;

                    if($linhaTOTAL_FATURAS <= $linhaCOD_MOV_SAVED) $msg = "Informações de Código de Movimento Gravados e Total de Faturas Gravadas Incoerentes".PHP_EOL;

                    //echo $msg;
                }//end if($fileBACEN) 

                else $msg = "Ocorreu um erro ao tentar abrir o Arquivo do BACEN".PHP_EOL;

            }//end if(date("Ym") == date("Ym", $nome_arquivos[$POSICAO_ARQUIVO]['mtime']))

            else $msg = "O arquivo pertence a um período anterior ao atual".PHP_EOL;

            //verificando se ocorreu algum erro, se sim deleta arquivos
            if($msg != ""){

                //Removendo o arquivo zipado
                @unlink($fileDest);
                //Removendo arquivos descompactados
                foreach($nome_arquivos as $key => $value){
                    @unlink($dirDest.$value['name']);
                }//end foreach
                
            }//end if($msg != "")
            
        }//end if($msg == "")
        

        //Movendo arquivos para o destino final
        if($msg == ""){
                
                //Alterando destinos
                $fileSource = $fileDest;
                $fileDest = $GLOBALS['DIR_BACEN_ARQ_RETORNO']."/".$data_arquivo_bacen."/".$fileDest_nome;

                //echo "Arquivo carregado [".$fileSource."]<br>Arquivo destino [".$fileDest."]<br>";
                if (!rename($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.".PHP_EOL; 
                else if((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo no destino esta vazio ou inválido.".PHP_EOL;
                else @unlink($fileSource);
                
                //verificando se ocorreu algum erro, se sim deleta arquivos
                if($msg != "") @unlink($fileSource);

                //Alterando destinos
                $fileDest_nome = $nome_arquivos[$POSICAO_ARQUIVO]['name']; 
                $fileDest = $GLOBALS['DIR_BACEN_ARQ_RETORNO']."/".$data_arquivo_bacen."/". $fileDest_nome; 
                $fileSource = $dirDest.$nome_arquivos[$POSICAO_ARQUIVO]['name'];
                
                //echo "Arquivo carregado [".$fileSource."]<br>Arquivo destino [".$fileDest."]<br>";
                if (!rename($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.".PHP_EOL; 
                else if((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo no destino esta vazio ou inválido.".PHP_EOL;
                else @unlink($fileSource);
                
                //verificando se ocorreu algum erro, se sim deleta arquivos
                if($msg != "") @unlink($fileSource);
        }

        
        //atualiza base
        if($msg == ""){
                //Capturando a data do arquivo
                $mesAno = date('m/Y', $nome_arquivos[$POSICAO_ARQUIVO]['mtime']);
                list($mes, $ano) = explode("/", $mesAno);   
                //Trazendo o mês anterior que foi o mês que o BACEN processou
                $currentmonth = mktime(0, 0, 0, $mes-1, 1, $ano);
                $mesAno = date('m/Y',$currentmonth);
                list($mes, $ano) = explode("/", $mesAno);

                //Publishers Já em Operação constantes em arquivos BACEN anteriores INTERNacionais
                $vetorPublisher = levantamentoPublisherOperantes($ano,$mes);

                //Publishers novos nunca antes contou nos arquivos BACEN INTERNacionais
                $vetorPublisherNovos = levantamentoPublisherNovosOperantes($ano,$mes);

                // Instanciando a variavel para verificação de novos Publishers
                $verificadorPublishersNovos = implode(",", $vetorPublisherNovos);

                //atualiza publisher de já em arquivos para 
                $sql = "update operadoras set opr_ja_contabilizou = ".$GLOBALS['STATUS_ARQUIVO_BACEN']['CONTABILIZOU']." where opr_ja_contabilizou = " . $GLOBALS['STATUS_ARQUIVO_BACEN']['AGUARDANDO_RETORNO_BACEN']. " and opr_vinculo_empresa = 1 and opr_codigo IN (".implode(",", $vetorPublisher).(!empty($verificadorPublishersNovos)?",".$verificadorPublishersNovos:"").");";
                //echo $sql;
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao atualizar Publisher.".PHP_EOL;
                else {
                    //Capturando o período    
                    $currentmonth = mktime(0, 0, 0, date('n')-1, 1, date('Y'));
                    $sql = "update cotacao_dolar set cd_freeze = 1 where cd_data = '".date('Y-m-d',$currentmonth)." 00:00:00' and opr_codigo IN (".implode(",", $vetorPublisher).(!empty($verificadorPublishersNovos)?",".$verificadorPublishersNovos:"").");";
                    //echo $sql;
                    $retCotacao = SQLexecuteQuery($sql);
                    if(!$retCotacao) $msg = "Erro ao atualizar congelamento de cotação do dólar.".PHP_EOL;
                }//end else if(!$ret)

        }

        //fecha janela
        if($msg == ""){
                $success = true;
                $msg = "Operação efetuada com sucesso!\nArquivos validados e salvos nos respectivos diretórios".PHP_EOL; 
        }

}
?>
    <script>
        function fcnOnSubmit(){

            if(form1.arquivo.value==''){
                alert('Arquivo não especificado');
                return false;
            }

        }
    </script>
    <div class="col-md-8 txt-preto">
        <p>Selecionar o arquivo de retorno do BACEN (compactado) para ser processado e alterar o status dos Publishers para já considerados anteriormente em arquivo para o BACEN. </p>
        <form action="" enctype="multipart/form-data" method="post">
            <p>
                <input type="file" name="arquivo" size="30" class="btn btn-sm btn-info pull-left"> 
                <input type="submit" name="BtnConcluir" value="Enviar" class="btn btn-sm btn-info pull-left left5" />
            </p>
        </form>
        <?php if(isset($msg) && $msg != ""){ ?>
            <div class="col-md-12 msg <?php echo (!$success)?'error':'success';?>"><?php echo nl2br($msg);?></div>
        <?php } ?>
    </div>
<?php 
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</body>
</html>
