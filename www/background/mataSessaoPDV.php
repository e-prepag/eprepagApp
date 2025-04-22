<?php
//die();
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

require_once "../includes/constantes.php";

//Definindo nome do arquivo contendo os IDs de PDV
$arrArquivoIDs = array("idsPDVs.txt","idsOpPDVs.txt");

//Definindo nome do arquivo temporário contendo os IDs de PDV enquanto processando
$arrArquivoIDsTMP = array("idsPDVs.tmp","idsOpPDVs.tmp");

include DIR_CLASS . "classManipulacaoArquivosLog.php";

$arquivoLog = new ManipulacaoArquivosLog($argv);
if(!$arquivoLog->haveFile()) 
{
    
    $arquivoLog->createLockedFile();
    $nome_arquivo = $arquivoLog->getNomeArquivo();
    ob_start('callbackLog');

    echo str_repeat("=", 80).PHP_EOL.date('Y-m-d H:i:s').PHP_EOL.str_repeat("=", 80).PHP_EOL;
    foreach($arrArquivoIDs as $ind => $arquivoIDs)
    {
        $arquivoIDsTMP = $arrArquivoIDsTMP[$ind];
        //Verificando se existe arquivo com IDs para ser processado
        $fullNameArquivoIDs = RAIZ_DO_PROJETO . "log/".$arquivoIDs;
        $fullNameArquivoIDsTMP = RAIZ_DO_PROJETO . "log/".$arquivoIDsTMP;
        
        if(file_exists($fullNameArquivoIDs)) 
        {

            echo "Encontrou o arquivo de IDs: ".$fullNameArquivoIDs.PHP_EOL;
            if(copy($fullNameArquivoIDs, $fullNameArquivoIDsTMP)) 
            {

                echo "Moveu o arquivo de IDs para arquivo temporário: ".$fullNameArquivoIDsTMP.PHP_EOL;
                if(unlink($fullNameArquivoIDs))
                {
                    if(filesize($fullNameArquivoIDsTMP) > 0) 
                    {
                        require_once DIR_CLASS . 'class.Session.php';
                        require_once DIR_CLASS . 'pdv/classGamesUsuario.php';
                        require_once DIR_CLASS . 'pdv/classOperadorGamesUsuario.php';
                        $dir_session = ini_get('session.save_path');

                        //Capturando a lista de arquivos de Sessões
                        $vetorSessoes = preg_grep('/sess*/', scandir($dir_session));;

                        //Capturando conteudo do arquivo com contendo IDs
                        $conteudoArquivoIDs = file_get_contents($fullNameArquivoIDsTMP);
                        $vetorIDs = explode(PHP_EOL,$conteudoArquivoIDs);
                        echo "Vetor de IDs: ".print_r($vetorIDs,true).PHP_EOL;

                        foreach ($vetorIDs as $chave => $valor)
                        {

                            //Limpando quebra de linha
                            $valor = $valor*1;

                            //Testando se possui valor válido
                            if(!empty($valor) && filter_var($valor, FILTER_VALIDATE_INT))
                            {

                                echo "Verificando Sessões para ID: ".$valor.PHP_EOL;
                                reset($vetorSessoes);

                                //Rastreando sessões do ID em questão
                                foreach($vetorSessoes as $key => $value) 
                                {

                                    $nome_tmp = $dir_session."/".$vetorSessoes[$key];

                                    //Verificando se é um arquivo de sessão válido
                                    if(@filesize($nome_tmp) > 0 && strpos($nome_tmp, "sess_"))
                                    {

                                        $conteudo_tmp = file_get_contents($nome_tmp);
                                        $vetor_tmp = Session::unserialize($conteudo_tmp);
                                        
                                        //verifica se está matando a sessão certa para o id correspondente (pdv / operador)
                                        if(isset($vetor_tmp['dist_usuarioGamesOperador_ser']) && $arquivoIDs == "idsPDVs.txt")
                                            continue;
                                        else if(!isset($vetor_tmp['dist_usuarioGamesOperador_ser']) && $arquivoIDs == "idsOpPDVs.txt")
                                            continue;
                                            
                                        if(!empty($vetor_tmp['dist_usuarioGames_ser']) && isset($vetor_tmp['dist_usuarioGames_ser']))
                                        {
                                            if($arquivoIDs == 'idsOpPDVs.txt')
                                            {
                                                $sub_objeto = unserialize((string)$vetor_tmp['dist_usuarioGamesOperador_ser']);
                                                $id = $sub_objeto->getId();
                                            }else{
                                                $sub_objeto = unserialize((string)$vetor_tmp['dist_usuarioGames_ser']);
                                                $id = $sub_objeto->ug_id;
                                            }
                                            
                                            if($id == $valor){

                                                echo $nome_tmp.PHP_EOL;
                                                echo "Conteudo: [".$conteudo_tmp."]".PHP_EOL;
                                                echo "UNSERIALIZE ".print_r($vetor_tmp, true);
                                                echo "Destruindo arquivo com a Sessão para o usuário: ".$id.PHP_EOL.str_repeat("-", 80).PHP_EOL;
                                                
                                                //Grava o arquivo vazio
                                                if ($handle = fopen($nome_tmp, 'w+')) {
                                                        fwrite($handle, '');
                                                        fclose($handle);
                                                }//end if ($handle = fopen($nome_tmp, 'w+'))

                                            }//end if($sub_objeto->ug_id == $valor)
                                        }//end if(!empty($vetor_tmp['dist_usuarioGames_ser']))
                                    }//end if(filesize($nome_tmp) > 0 && strpos($nome_tmp, "sess_"))
                                }//end foreach($vetorSessoes as $key => $value)
                            }//end if(!empty($valor) && filter_var($valor, FILTER_VALIDATE_INT))
                        }//end foreach ($vetorIDs as $chave => $valor)
                    }//end if(filesize($fullNameArquivoIDsTMP) > 0) 
                    else {
                        echo "Arquivo ".$fullNameArquivoIDsTMP." VAZIO.".PHP_EOL;
                    }
                }//end if(unlink($fullNameArquivoIDs))
                else {
                    echo "Não foi possível deletar o arquivo ".$fullNameArquivoIDs." após este ter sido moovido para ".$fullNameArquivoIDsTMP.PHP_EOL;
                }

                //deletando o arquivo temporário
                unlink($fullNameArquivoIDsTMP);

            }//end if(copy($fullNameArquivoIDs, $fullNameArquivoIDsTMP))
            else {
                echo "Não foi possível mover o arquivo para o temporário: ".$fullNameArquivoIDsTMP.PHP_EOL;
            }
        } //end if(file_exists($fullNameArquivoIDs))
        else {
            echo "Nenhum ID de PDV para ser processado.".PHP_EOL;
        }
    }
    $arquivoLog->deleteLockedFile();
}
else {
    $arquivoLog->showBusy();
}