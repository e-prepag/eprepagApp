<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
include_once $raiz_do_projeto."includes/gamer/constantes.php";
include_once $raiz_do_projeto."includes/complice/functions.php";

//Variavel de verificação de sucesso
$success = false;

//Extensoes de arquivos da Ponto Certo permitidos 
$PONTO_CERTO_EXTENSOES = array("csv");
//Diretório destino do arquivo do Ponto Certo Importado
$DIR_PONTO_CERTO_ARQ_RETORNO = $raiz_do_projeto . "/arquivos_gerados/ponto_certo/";
//Número de Coluna do Arquivo CSV
$NUMERO_COLUNAS = 4;

//Processa acoes
//----------------------------------------------------------------------------------------------------------
if(isset($BtnConcluir) && $BtnConcluir) {
    
        //Geraldo o vetor contendo os prefixos previstos
        $sql = "select opr_codigo,opr_prefixo_ponto_certo from operadoras where opr_distribui_ponto_certo = 1;";
        $ret_vetor = SQLexecuteQuery($sql);
        if(!$ret_vetor) die("Erro na instrução que seleciona Lista de Publisher que distribuem PINs pela rede Potno Certo.\n");
        else {
            while($ret_vetor_row = pg_fetch_array($ret_vetor)){
                $vetorPublishers[$ret_vetor_row['opr_prefixo_ponto_certo']] = $ret_vetor_row['opr_codigo'];
            }//end while
        }//end else do if(!$ret_vetor)
        //echo "<pre>".print_r($vetorPublishers,true)."</pre>";
    
        //Validacao
        $msg = "";

        //Valida arquivo
        $fileSource = $_FILES['arquivo']['tmp_name']; 
        if (($fileSource == 'none') || ($fileSource == '' )) $msg = "Nenhum arquivo fornecido.\n";

        //Valida extensao
        if($msg == ""){
                $fileExtensao = strtolower(substr($_FILES['arquivo']['name'], -3)); 
                if (!in_array($fileExtensao, $GLOBALS['PONTO_CERTO_EXTENSOES'])) $msg = "Extensão de arquivo inválida.\n";
        }

        //Salva arquivo
        if($msg == ""){

                $fileDest_nome = $_FILES['arquivo']['name']; 

                $fileDest = $GLOBALS['DIR_PONTO_CERTO_ARQ_RETORNO'] . date('Ymd_') . $fileDest_nome; 

                //echo "Arquivo carregado [".$fileSource."]<br>Arquivo destino [".$fileDest."]<br>";
                if (!move_uploaded_file($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.\n"; 
                else if((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo no destino esta vazio ou inválido.\n";
                else unlink($fileSource);
        }

        //Importando Dados
        if($msg == ""){
                
                //Abrindo arquivo com as transações
                $filePontoCerto = fopen($fileDest, 'r');
                if($filePontoCerto) {
                        
                        //Contador de Linhas
                        $contador = 1;
                        
                        //Lendo o arquivo
                        while (!feof($filePontoCerto)) {
                            $linha = explode(";",fgets($filePontoCerto));
                            if(count($linha) === $NUMERO_COLUNAS) {
                                list($id_transacao, $descricao, $data, $valor) = $linha;
                                $valor = str_replace(",",".",str_replace(".", "", $valor));
                                if(filter_var($id_transacao, FILTER_VALIDATE_INT) && filter_var($valor, FILTER_VALIDATE_FLOAT)){ 
                                    //Verificando se a transação já foi importada anteriomente
                                    $sql = "select * from pos_transacoes_ponto_certo where id_transacao = ".trim($id_transacao).";";
                                    //echo $sql;
                                    $ret = SQLexecuteQuery($sql);
                                    if(!$ret) $msg .= "Erro ao selecionar transação.\n";
                                    else {
                                        if(pg_num_rows($ret)==0) {
                                                
                                                //Capturando o campo com o ID do Publisher
                                                $opr_codigo = "NULL";
                                                foreach ($vetorPublishers as $key => $value) {
                                                    if(strstr(strtoupper($descricao),  strtoupper($key))) {
                                                        $opr_codigo = $value;
                                                        break;
                                                    }//end if(strpos(strtoupper($descricao),  strtoupper($key)))
                                                }//end foreach
                                                
                                                
                                                //inserir registro da transação da rede ponto certo 
                                                $sql = "insert into pos_transacoes_ponto_certo values (".$id_transacao.",'".$descricao."',to_timestamp('".$data."','DD/MM/YYYY HH24:MI:SS'),".trim($valor).",".$opr_codigo.");";
                                                //echo $sql;
                                                $ret_insert = SQLexecuteQuery($sql);
                                                if(!$ret_insert) echo "Erro ao Inserir o Registro da Transação ID [".$id_transacao."].<br>";
                                        }//end if(count($ret)==0)
                                        else {
                                            echo "Transação de ID[".$id_transacao."] já importada anteriormente.<br>";
                                        }//end else do if(count($ret)==0)
                                    }//end else do if(!$ret)
                                }//end if(filter_var($id_transacao, FILTER_VALIDATE_INT) && filter_var($valor, FILTER_VALIDATE_FLOAT))
                                else {
                                    $msg .= "\n<font color='#FF0000'>Error: ID da Transação Tipo Inválido (".$id_transacao.") ou Valor da Transação Tipo Inválido (".$valor.")</font><br>\n";
                                }//end else do if(filter_var($id_transacao, FILTER_VALIDATE_INT) && filter_var($valor, FILTER_VALIDATE_FLOAT))
                            }//end if(count($linha) === $NUMERO_COLUNAS)
                            else {
                                echo "\n<font color='#FF0000'>Atenção: Linha [".$contador."] contem somente (".count($linha).") coluna".(count($linha)>1?"s":"")."!</font><br>\n";
                            }//end else do if(count($linha) === $NUMERO_COLUNAS)
                            
                            $contador++;
                        }//end while
                        fclose($filePontoCerto);
                } else {
                        $msg .= "\n<font color='#FF0000'>Error: Couldn't open Monitor File for reading</font><br>\n";
                }
        }//end if($msg == "")

        //fecha janela
        if($msg == ""){
                $success = true;
                $msg = "Operação efetuada com sucesso!\n"; 
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
    <div class="col-md-12">
        <ol class="breadcrumb top10">
            <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
            <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
            <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
        </ol>
    </div>
    <div class="col-md-12 txt-preto">
        
        <div class="row top10 form-group">
            <form action="" enctype="multipart/form-data" method="post">
                <div class="col-md-12">
                    <label for="arquivo">Selecionar o arquivo contendo as transações da Rede Ponto Certo(formato CSV) para ser importado pelo sistema:</label>
                </div>
                <div class="col-md-3">
                    <input type="file" name="arquivo" id="arquivo" class="btn btn-info btn-sm" size="30"> 
                </div>
                <div class="col-md-2">
                    <input type="submit" class="btn pull-right btn-sm btn-info" name="BtnConcluir" value="Enviar" />
                </div>
            </form>
        </div>
        <div class="row">
            <?php if(isset($msg) && $msg != ""){ ?>
                <div class="msg <?php echo (!$success)?'error':'success';?>"><?php echo nl2br($msg);?></div>
            <?php } ?>
        </div>
    </div>
</body>
</html>
