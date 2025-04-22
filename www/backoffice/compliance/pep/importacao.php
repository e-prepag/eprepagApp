<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

set_time_limit(0);
ini_set('max_execution_time', 0);
ini_set('memory_limit', '1024M');

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "class/util/Validate.class.php";
require_once $raiz_do_projeto . "class/util/Util.class.php";
require_once $raiz_do_projeto . "includes/complice/functions.php";

//Variavel de verificação de sucesso
$success = false;

//Extensoes de arquivos do PEP permitidos 
$PEP_EXTENSOES = array("csv", "zip");
//PArte do Nome do arquivo PEP zipado
$PEP_FILE_NAME = "PEP";
//Diretório destino do arquivo do PEP
$DIR_PEP_ARQ_RETORNO = $raiz_do_projeto . "/backoffice/compliance/pep/";
//Indices a serem considerados
$IDX_VETOR_ARQ_EXPLODE = array(
                               'cpf'  => 0,
                               'nome'  => 1,
                               'descricao_funcao'   => 3
                               );
							   
?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><?php echo $sistema->item->getDescricao(); ?></li>
    </ol>
	
	<?php //echo phpinfo(); ?>
</div>
<div class="clearfix"></div>    
<?php
//ob_end_flush();
//flush();

//Processa acoes
//----------------------------------------------------------------------------------------------------------
//$BtnConcluir;

if(isset($_POST["BtnConcluir"]) && !empty($_POST["BtnConcluir"])) {
    
        //Validacao
        $msg = "";

        $caminho = "/www/backoffice/compliance/pep";
        $zip = new ZipArchive;
		$zip->open( $_FILES['arquivo']['tmp_name']);
		if($zip->extractTo($caminho) != true){
	        $msg = "Não possivel finalizar o processo de leitura de dados";
		}
		$arquivos = scandir($caminho);
		foreach($arquivos as $key => $arquivo){
			if(pathinfo($arquivo, PATHINFO_EXTENSION) == "CSV" || pathinfo($arquivo, PATHINFO_EXTENSION) == "csv"){
				$arquivoDescompactado = $arquivo;
			}
		}
		$zip->close();

        //Valida arquivo
		//$fileSource = $arquivoDescompactado;
        $fileSource = $_FILES['arquivo']['tmp_name']; 
        if (($fileSource == 'none') || ($fileSource == '' )) $msg = "Nenhum arquivo fornecido.".PHP_EOL;

        //Valida extensao
        if($msg == ""){
                $fileExtensao = strtolower(substr($_FILES['arquivo']['name'], -3)); 
                if (!in_array($fileExtensao, $GLOBALS['PEP_EXTENSOES'])) $msg = "Extensão de arquivo inválida. Aceitos somente tipo ".$GLOBALS['PEP_EXTENSOES'][0]." (".$GLOBALS['PEP_EXTENSOES'][0].").".PHP_EOL;
        }

        //Valida extensao
        if($msg == ""){
                if (!is_numeric(strpos(strtoupper($_FILES['arquivo']['name']),$PEP_FILE_NAME))) $msg = "Parte do nome do Arquivo deveria ser $PEP_FILE_NAME.".PHP_EOL;
        }

        //Salva arquivo
        if($msg == ""){

                $fileDest_nome = $_FILES['arquivo']['name']; 
				//$fileDest_nome = $arquivoDescompactado;
                $fileDest = $GLOBALS['DIR_PEP_ARQ_RETORNO'] ."tmp/". $fileDest_nome; 

                //echo "Arquivo carregado [".$fileSource."]<br>Arquivo destino [".$fileDest."]<br>";
                if (!move_uploaded_file($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.".PHP_EOL; 
                else if((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo no destino esta vazio ou inválido.".PHP_EOL;
                if(file_exists($fileSource)) unlink($fileSource);
        }
        
        //Verificando se o arquivo já foi importado
        if($msg == ""){
            
            //capturando a data (YYYYMMDD) do arquivo gerado pelo PEP
            $data_arquivo_pep = date("Ymd", filemtime($fileDest));
            //Data para o banco de dados
            $data_dados_considerados = date("Y-m-d H:i:s", filemtime($fileDest));
            //testando se diretório para gravação do arquivo existe
            if(!file_exists($GLOBALS['DIR_PEP_ARQ_RETORNO']."/".$data_arquivo_pep)) {
                mkdir($GLOBALS['DIR_PEP_ARQ_RETORNO']."/".$data_arquivo_pep, 0700);
            }//end if
            else {
                $msg = "Arquivo já importado anteriormente".PHP_EOL;
            }//end else 
        }//end 
        
        //Validações de arquivo por período, estrutura e conteudo
        if($msg == ""){
                    /*
                    //Abrindo arquivo do PEP
                    $xml=simplexml_load_file($fileDest);
                    $data_dados_considerados = substr($xml->publshInformation->Publish_Date,6,4)."-".substr($xml->publshInformation->Publish_Date,0,2)."-".substr($xml->publshInformation->Publish_Date,3,2)." 00:00:00";
                    $total_de_registros = $xml->publshInformation->Record_Count;
		    $msg_aux = "Data da geração dos Dados Considerados: ".$data_dados_considerados."<br> Total de Registros: ".$total_de_registros."<br>"; //."<pre>".print_r($xml,true)."</pre>";
                    $i = 1;
                    foreach($xml->sdnEntry as $indice => $registro) {
                            $nome_aux = strtoupper(trim($registro->lastName));
                        $nome_aux = str_replace("'", "\'", $nome_aux);
                        $sql = "INSERT INTO pep (nome, tipo_dado, data) VALUES('".$nome_aux."','".strtoupper($registro->sdnType)."','".$data_dados_considerados."');";
                        //echo $i.": ".$sql."<br>";
                        $ret = SQLexecuteQuery($sql);
                        if(!$ret) $msg = "Erro ao Inserir registro no Banco de Dados ($sql).".PHP_EOL;
                        $i++;
                    }//end foreach

                    //Verificando a quantidade de registros no arquivo
                    if($total_de_registros != ($i-1)) {
                        $msg = "Quantidade de registros diferente do total informado no cabeçalho do arquivo".PHP_EOL;
                    }
                    */
                    
            //Abrindo arquivo do PEP
            $filePEP = fopen($arquivoDescompactado, 'r'); //$fileDest
            if($filePEP) {
                while (!feof($filePEP)) {
                    $members[] = fgets($filePEP);
                }
                fclose($filePEP);

                $contador = 0;
                foreach ($members as $line){

                    $vetor_aux = explode(';', $line);
					
                    //removendo aspas duplas (")
                    @$vetor_aux[$IDX_VETOR_ARQ_EXPLODE['nome']] = str_replace('"', '', $vetor_aux[$IDX_VETOR_ARQ_EXPLODE['nome']]);
                    //Preparando nome para banco de dados
                    $nome_aux = strtoupper(trim($vetor_aux[$IDX_VETOR_ARQ_EXPLODE['nome']]));
                    $nome_aux = str_replace("'", "\'", $nome_aux);
                    //Testando se não é cabeçalho
                    if(($nome_aux != "NOME_PEP" && $nome_aux != "Nome_PEP") && !empty($nome_aux)) {
                        //echo "<pre>".print_r($vetor_aux, true)."</pre>";
                        //echo "Nome aux: [".$nome_aux."] CPF: [".$vetor_aux[$IDX_VETOR_ARQ_EXPLODE['cpf']]."] Função: [".$vetor_aux[$IDX_VETOR_ARQ_EXPLODE['descricao_funcao']]."] Data: [$data_dados_considerados]<br>";
                        $sql = "INSERT INTO pep (nome, descricao_funcao, data, cpf) VALUES('".$nome_aux."','".strtoupper($vetor_aux[$IDX_VETOR_ARQ_EXPLODE['descricao_funcao']])."','".$data_dados_considerados."',".$vetor_aux[$IDX_VETOR_ARQ_EXPLODE['cpf']].");";
                        //echo $i.": ".$sql."<br>";
                        $ret = SQLexecuteQuery($sql);
                        if(!$ret) $msg = "Erro ao Inserir registro no Banco de Dados ($sql).".PHP_EOL;
                        else $contador++;
                    }//end if($nome_aux != "NOME_PEP" && !empty($nome_aux))
                }//end foreach
                unset($members);
                //echo $msg;
            }//end if($filePEP) 
            else $msg = "Ocorreu um erro ao tentar abrir o Arquivo do PEP".PHP_EOL;


            //verificando se ocorreu algum erro, se sim deleta arquivos
            if($msg != ""){

                //Removendo o arquivo
                unlink($fileDest);
                
            }//end if($msg != "")
            
        }//end if($msg == "")
        
        //Movendo arquivos para o destino final
        if($msg == ""){
                
                //Alterando destinos
                $fileSource = $fileDest;
                $fileDest = $GLOBALS['DIR_PEP_ARQ_RETORNO']."/".$data_arquivo_pep."/".$fileDest_nome;

                //echo "Arquivo carregado [".$fileSource."]<br>Arquivo destino [".$fileDest."]<br>";
                if (!rename($fileSource, $fileDest)) $msg = "Não foi possivel copiar para o diretório destino.".PHP_EOL; 
                else if((!file_exists($fileDest)) || (filesize($fileDest)) == 0) $msg = "Arquivo no destino esta vazio ou inválido.".PHP_EOL;
                if(file_exists($fileSource)) unlink($fileSource);
                
                //verificando se ocorreu algum erro, se sim deleta arquivos
                if($msg != "") unlink($fileSource);

        }

        //limpar base
        if($msg == ""){

                //Limpar dados antigos do Banco de Dados 
                $sql = "DELETE FROM pep WHERE data <  '".$data_dados_considerados."';";
                //echo $sql;
                $ret = SQLexecuteQuery($sql);
                if(!$ret) $msg = "Erro ao remover registros antigos ($sql).".PHP_EOL;
                
        }

        //fecha janela
        if($msg == ""){
                $success = true;
                $msg = "Operação efetuada com sucesso!".PHP_EOL."Arquivos validados e registros salvos no Banco de Dados.".PHP_EOL."Totalizando [$contador] registros importados.".PHP_EOL;
                unlink($arquivoDescompactado);				
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
	
	<div class="alert alert-info" role="alert"> 
	     <h5>O arquivo PEP deve ser no formato <b>.CSV</b> e deve ser comprimido no formato <b>.ZIP</b> para ser adicionado no sistema.</h5>
    </div>
    <div class="col-md-12 txt-preto">
        <p>Selecionar o arquivo <?php echo $PEP_FILE_NAME; ?> para importar a lista a ser considerada nos alertas e controles de Compliance.</p>
        <form name="form1" id="form1" action="importacao.php" enctype="multipart/form-data" method="post" onsubmit="return fcnOnSubmit();">
            <p>
                <input type="file" name="arquivo" size="30" class="btn btn-sm btn-info pull-left"> 
                <input type="submit" name="BtnConcluir" value="Enviar" class="btn btn-sm btn-info pull-left left5" />
            </p>
        </form>
    </div>
<?php 
if(isset($msg) && $msg != ""){ 
?>
    <div class="top20 col-md-12 msg <?php echo (!$success)?'txt-vermelho':'txt-azul-claro';?>"><?php echo ((!empty($msg)&&!$success)?'ERRO: ':'SUCESSO:<br><br>').nl2br($msg);?></div>
<?php 
} 
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</body>
</html>
