<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/main.php";
require_once $raiz_do_projeto . "includes/gamer/constantes.php";
require_once $raiz_do_projeto . "includes/complice/functions.php";

//Variavel de verificação de sucesso
$success = false;

//Extensoes de arquivos do MUNICIPAL permitidos 
$MUNICIPAL_EXTENSOES = array("jpg");
//Diretório destino do arquivo do MUNICIPAL
$DIR_MUNICIPAL_ARQ_RETORNO = $raiz_do_projeto . "backoffice/bacen/";


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
                if (!in_array($fileExtensao, $GLOBALS['MUNICIPAL_EXTENSOES'])) $msg = "Extensão de arquivo inválida.".PHP_EOL;
        }

        //Salva arquivo
        if($msg == ""){

                $fileDest_nome = $_FILES['arquivo']['name']; 

                $fileDest = $GLOBALS['DIR_MUNICIPAL_ARQ_RETORNO'] ."tmp/". $fileDest_nome; 

                //echo "Arquivo carregado [".$fileSource."]<br>Arquivo destino [".$fileDest."]<br>";
                if (!move_uploaded_file($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.".PHP_EOL; 
                else if((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo no destino esta vazio ou inválido.".PHP_EOL;
                else @unlink($fileSource);
        }

        //Validações de arquivo por período, estrutura e conteudo
        if($msg == ""){
            
            //capturando a data (YYYYMM) do arquivo gerado pelo MUNICIPAL
            $data_arquivo_bacen = date("Ym", filemtime($fileDest));
            
            //teste se o arquivo percente ao mês vigente
            if(date("Ym") == $data_arquivo_bacen) {
                
                //echo "O arquivo corresponde ao Mês vigente<br>";

            }//end if(date("Ym") == date("Ym", $nome_arquivos[$POSICAO_ARQUIVO]['mtime']))

            else $msg = "O arquivo pertence a um período anterior ao atual".PHP_EOL;
                
            
            //verificando se ocorreu algum erro, se sim deleta arquivos
            if($msg != ""){

                //Removendo o arquivo zipado
                @unlink($fileDest);
                
            }//end if($msg != "")
            
        }//end if($msg == "")
        

        //Movendo arquivos para o destino final
        if($msg == ""){
                
                //Alterando destinos
                $fileSource = $fileDest;
                $fileDest = $GLOBALS['DIR_MUNICIPAL_ARQ_RETORNO']."/".$data_arquivo_bacen."_PM/";
                mkdir($fileDest, 0700);
                $fileDest .= $fileDest_nome;

                //echo "Arquivo carregado [".$fileSource."]<br>Arquivo destino [".$fileDest."]<br>";
                if (!rename($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.".PHP_EOL; 
                else if((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo no destino esta vazio ou inválido.".PHP_EOL;
                else @unlink($fileSource);
                
                //verificando se ocorreu algum erro, se sim deleta arquivos
                if($msg != "") @unlink($fileSource);

        }

        
        //atualiza base
        if($msg == ""){

                //atualiza publisher de já em arquivos para 
                $sql = "update operadoras set opr_ja_contabilizou = ".$GLOBALS['STATUS_ARQUIVO_BACEN']['CONTABILIZOU']." 
                        where opr_ja_contabilizou = " . $GLOBALS['STATUS_ARQUIVO_BACEN']['AGUARDANDO_RETORNO_BACEN']. "  
                        and opr_data_inicio_operacoes is not null
                        and opr_internacional_alicota = 0
                        and UPPER(opr_estado) = 'SP'
                        and UPPER(TRIM(opr_cidade)) = UPPER('São Paulo')
                        and opr_vinculo_empresa = ".$GLOBALS['IDENTIFICACAO_EMPRESA_ADMINISTRADORA_CARTAO']."; ";
                //echo $sql;
                //die("<br>POr favor, informe o Wagner da mensagema cima urgentemente!!");
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao atualizar Publisher.".PHP_EOL;

        }

        //fecha janela
        if($msg == ""){
                $success = true;
                $msg = "Operação efetuada com sucesso!".PHP_EOL."Arquivos validados e salvos nos respectivos diretórios".PHP_EOL; 
        }

}
?>
<html>
<head>
    <title>E-Prepag - Upload</title>
    <META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
    <META HTTP-EQUIV="EXPIRES" CONTENT="0">
    <META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
    <link href="http://<?php echo $_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'] ?>/incs/css.css" rel="stylesheet" type="text/css">
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
            height: 200px;
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
</head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><?php echo $sistema->item->getDescricao(); ?></li>
    </ol>
</div>
<fieldset>
    <legend>Altera o Status de Publishers (Compliance Municipal)</legend>
    <br>
    Selecionar o arquivo de retorno do MUNICIPAL (imagem scanneada - Formato JPG) para ser processado e alterar o status dos Publishers para já considerados anteriormente em arquivo para o compliance MUNICIPAL. 
    <form action="" enctype="multipart/form-data" method="post">
        <input type="file" name="arquivo" size="30"> <br> <input type="submit" name="BtnConcluir" value="Enviar" />
    </form>
    <br>
    <?php if(isset($msg) && $msg != ""){ ?>
        <div class="msg <?php echo (!$success)?'error':'success';?>"><?php echo nl2br($msg);?></div>
    <?php } ?>
</fieldset>
</body>
</html>
