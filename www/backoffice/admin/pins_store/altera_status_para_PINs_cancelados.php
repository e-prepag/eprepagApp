<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/main.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";
require_once $raiz_do_projeto . "class/classIntegracaoPin.php";
require_once $raiz_do_projeto . "class/classIntegracaoPinCash.php";
require_once $raiz_do_projeto . "class/classPinsStore.php";       

//Variavel de verificação de sucesso
$success = false;

//Extensoes de arquivos do permitidos 
$EXTENSOES = array("csv");
//Diretório destino do arquivo
$DIR_ARQ_RETORNO = $raiz_do_projeto . "arquivos_gerados/ponto_certo/cancelados/";
//Prefixo da linha identificando EPP CASH
$prefixo = "E-PREPAG CASH";
//Tamanho do EPP CASH Considerado
$tamanhoEPPCash = 16;

//Processa acoes
//----------------------------------------------------------------------------------------------------------
if(isset($BtnConcluir) && $BtnConcluir) {
    
        //Setando o timeout
        set_time_limit(3000);
    
        //Validacao
        $msg = "";

        //Valida arquivo
        $fileSource = $_FILES['arquivo']['tmp_name']; 
        if (($fileSource == 'none') || ($fileSource == '' )) $msg = "Nenhum arquivo fornecido.\n";

        //Valida extensao
        if($msg == ""){
                $fileExtensao = strtolower(substr($_FILES['arquivo']['name'], -3)); 
                if (!in_array($fileExtensao, $GLOBALS['EXTENSOES'])) $msg = "Extensão de arquivo inválida.\n";
        }

        //Testando se o diretório destino existe
        if($msg == ""){
            if(!is_dir($GLOBALS['DIR_ARQ_RETORNO'])) {
                mkdir($GLOBALS['DIR_ARQ_RETORNO'], 0700);
            }
        }
        
        //Salva arquivo
        if($msg == ""){

                $fileDest_nome = $_FILES['arquivo']['name']; 

                $fileDest = $GLOBALS['DIR_ARQ_RETORNO'] . $fileDest_nome; 

                //echo "Arquivo carregado [".$fileSource."]<br>Arquivo destino [".$fileDest."]<br>";
                if (!move_uploaded_file($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
                else if((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo no destino esta vazio ou inválido.\n";
                else unlink($fileSource);
        }

        //Validações de arquivo por período, estrutura e conteudo
        if($msg == ""){
            
            //testando se foi gerado arquivos para o prríodo informado
            if(file_exists($fileDest)) {
                //echo "Diretório existe.<br>";
                    
                //Abrindo arquivo
                $file = fopen($fileDest, 'r');
                if($file) {

                    while (!feof($file)) {
                        $members[] = fgets($file);
                    }
                    fclose($file);

                    //Varrendo linha por linha do arquivo
                    $i = 1;
                    $contadorSucesso = 0;
                    $contadorPINjaUtilizado = 0;
                    $contadorPINnaoEncontrado = 0;
                    $contadorOutroStatus = 0;
                    foreach ($members as $line){
                        
                        //Verificando se possui o prefixo
                        if(strstr(strtoupper($line), $prefixo)) {

                            //Limpando aspas
                            $line = str_replace('"', '', $line);
                            $line = str_replace("'", '', $line);

                            $vetorAux = array();
                            $vetorAux = explode(",",$line);

                            if(strlen($vetorAux[2]) == $tamanhoEPPCash) {
                                
                                //Verificando se o PIN existe no DB
                                $idPIN = retorna_id_pin_cash($vetorAux[2]);
                                //echo "PIN [".$vetorAux[2]."] ID: ".$idPIN."<br>";
                                if($idPIN <> 0) {
                                    
                                        //Verificando o Status do PIN
                                        $statusPIN = retorna_status_cash($vetorAux[2]);
                                        if($statusPIN == $PINS_STORE_STATUS_VALUES['A']) {
                                                
                                                //atualiza PIN para Cancelado
                                                $sql = "update pins_store set pin_status=".$PINS_STORE_STATUS_VALUES['C']." where pin_status= ".$statusPIN."  AND pin_codinterno=".$idPIN.";";
                                                //echo $sql;
                                                $ret = SQLexecuteQuery($sql);
                                                $cmdtuples = pg_affected_rows($ret);
                                                //echo $cmdtuples . " registros afetados.<br>\n";
                                                if($cmdtuples===1) {
                                                    $contadorSucesso++;
                                                }//end if($cmdtuples===1)
                                                else {
                                                    $msg .= "Erro ao atualizar PIN [".$vetorAux[2]."].\n";
                                                }//end else do if($cmdtuples===1)

                                        }//end if($statusPIN == $PINS_STORE_STATUS_VALUES['A'])
                                        elseif($statusPIN == $PINS_STORE_STATUS_VALUES['U']) {

                                                $contadorPINjaUtilizado++;

                                        }//end elseif($statusPIN == $PINS_STORE_STATUS_VALUES['U'])
                                        else {

                                                $contadorOutroStatus++;

                                        }//end else $contadorOutroStatus
                                    
                                }//end if($idPIN <> 0) 
                                else {
                                    $contadorPINnaoEncontrado++;
                                }//end else do if($idPIN <> 0) 
                                
                            } //end if(strlen($vetorAux[2]) == $tamanhoEPPCash) 
                            
                            
                        }//end if(strpos($prefixo, $line))
                        
                        $i++;
                    }//end foreach

                }//end if($file) 

                else $msg = "Ocorreu um erro ao tentar abrir o Arquivo\n";
                    
                
            } //end if(file_exists($fileDest))
            
            else $msg = "O arquivo enviado não corresponde a nenhum período de geração de arquivos\n"; 
            
            //verificando se ocorreu algum erro, se sim deleta arquivos
            if($msg != ""){

                //Removendo o arquivo zipado
                unlink($fileDest);
                
            }//end if($msg != "")
            
        }//end if($msg == "")
        
        //fecha janela
        if($msg == ""){
                $success = true;
                if($contadorSucesso > 0) $msg .="Processamento efetuado com sucesso para :".$contadorSucesso." PINs\n";
                if($contadorPINjaUtilizado > 0) $msg .="PINs já Utilizados:".$contadorPINjaUtilizado." e não alterados\n";
                if($contadorPINnaoEncontrado > 0) $msg .="PINs Não Encontrados:".$contadorPINnaoEncontrado."\n";
                if($contadorOutroStatus > 0) $msg .="PINs com Outros Status:".$contadorOutroStatus." diferente de Ativo e Utilizado\n"; 
        }

}//end if($BtnConcluir)
?>
    <script>
        function fcnOnSubmit(){

            if(form1.arquivo.value==''){
                alert('Arquivo não especificado');
                return false;
            }

        }
    </script>
    <style>
        fieldset {
            width: 75%;
            margin-left: 12%;
            margin-top: 35px;
            font-family: Verdana;
            font-size: 13px;
            border-radius: 10px;
            -webkit-border-radius: 10px;
            -moz-border-radius: 10px;
            -o-border-radius: 10px;
        }
        fieldset input {
            font-family: Verdana;
        }
        fieldset input[type="submit"] {
            margin-left: 80px;
            font-size: 13px;
            color: #FFFFFF;
            background-color: #A6A6A6;
            border: none;
            text-transform: none;
            font-weight: bold;
            padding: 5px 15px 5px 15px;
            border-radius: 10px;
            -webkit-border-radius: 10px;
        }
        fieldset input[type="file"]::-webkit-file-upload-button {
            font-size: 13px;
            color: #FFFFFF;
            background-color: #A6A6A6;
            border: none;
            text-transform: none;
            font-weight: bold;
            padding: 5px 15px 5px 15px;
            border-radius: 10px;
            -webkit-border-radius: 10px;
            -moz-border-radius: 10px;
            -o-border-radius: 10px;
        }
        fieldset form {
            margin-left: 13%;
            margin-top: 35px;
        }
        fieldset input[type="submit"] {
            margin-left: 80px;
        }
        .msg {
            font-weight: 500;
            font-size: 17px;
            text-align: center;
        }
        .error {
            color: #ff423e;
        }
        .success {
            color: #29981b;
        }
    </style>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="1">BackOffice - Money</a></li>
        <li class="active">Altera o Status dos PINs EPP CASH contidos em Arquivo CSV para cancelados</li>
    </ol>
</div>  
<div class="col-md-12 txt-preto fontsize-pp">
<?php
include "pins_store_menu.php";
?>
<fieldset>
    Selecionar o arquivo contendo a relação de PINs EPP CASH (CSV - No formato correto) para serem processados e alterar o status para Cancelados.
    <form action="" enctype="multipart/form-data" method="post">
        <input type="file" name="arquivo" size="30"> <input type="submit" name="BtnConcluir" value="Enviar" />
    </form>
    <br>
    <?php if($msg != ""){ ?>
        <div class="msg <?php echo (!$success)?'error':'success';?>"><?php echo nl2br($msg);?></div>
    <?php } ?>
</fieldset>
<fieldset>
    <legend>Lista de Arquivos já Processados</legend>
<?php
if(is_dir($DIR_ARQ_RETORNO)){
    $diretorio = dir($DIR_ARQ_RETORNO); 
    
    while($arquivo = $diretorio -> read()){ 
    if($arquivo != "." && $arquivo != "..") {
        echo "<a href='../../../ponto_certo/cancelados/".$arquivo."'>".$arquivo."</a><br />"; 
    } //end if($arquivo != "." && $arquivo != "..")
} //end while
$diretorio -> close();
}

?>
</fieldset>
</div>
</body>
</html>
