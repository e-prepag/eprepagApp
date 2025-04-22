<?php
//error_reporting(E_ALL); 
//ini_set('display_errors', 1);


require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto.'sftp/connect.php';
require_once $raiz_do_projeto.'sftp/classSFTPconnection.php';
require $raiz_do_projeto.'includes/configCEP.php';

/* 
    CONTROLLER
 */

//Verificando se executou o click no botão atualizar
if(!isset($environment)) $environment = CONSULTA_CEP;


$array_content_page = file($raiz_do_projeto.'includes/configCEP.php');


if(isset($BtnSearch) && $BtnSearch) {
        //Variavel de OUTPUT
        $msg = '';

        $conteudo_novo = "";
        $pattern = '/CONSULTA_CEP/';
        foreach ($array_content_page as $i => $linha){
            
            if(preg_match($pattern, $linha)){
                $conteudo_novo .= 'define("CONSULTA_CEP", $vetorCEP ['."'".$vetorCEP_Legenda[$vetorCEP[$_POST['site_cons']]]."'".']);';
            } else{
                $conteudo_novo .= $linha;
            }
            
        }
        
        $newfile = fopen($raiz_do_projeto.'includes/configCEP.php', 'w');
        
        if(!$newfile){
            $msg = "<span class='txt-vermelho'>Problema ao alterar arquivo de configuração de consulta de CEP! Contacte o setor de T.I</span>";
        }else{
            if(fwrite($newfile, $conteudo_novo)) {
                    $msg = "<span class='txt-verde'>Sucesso na atualização do site de Consulta de CEP!</span>";
                    fclose($newfile);
                    if($vetorCEP_Legenda[$vetorCEP[$_POST['site_cons']]] == 'REPUBLICA_VIRTUAL'){
                        $site_atual = "REPUBLICA VIRTUAL - http://cep.republicavirtual.com.br";
                    } elseif($vetorCEP_Legenda[$vetorCEP[$_POST['site_cons']]] == 'VIACEP'){
                        $site_atual = "VIACEP - https://viacep.com.br";
                    }
                    $msg .= "<br>O site utilizado no momento é ".$site_atual;
                    $nome_arquivo = "configCEP.php";
                    $arquivo = $raiz_do_projeto."includes/".$nome_arquivo;
                    if(SFTP_TRANSFER && file_exists($arquivo)){
                        $arq = trim(str_replace('/', '\\', $arquivo));

                        //enviar para os servidores via sFTP
                        $sftp = new SFTPConnection($server, $port);
                        $sftp->login($user, $pass);
                        $sftp->uploadFile($arquivo, "E-Prepag/incs/".$nome_arquivo);

                        $msg .= "<br><br>Arquivo de configuração enviado ao servidor Windows 2003";

                    }
            }
            else $msg = "<span class='txt-vermelho'>Erro ao salvar as configurações contacte o Administrador imediatamente!</span>";
        }
        
            
} // end if($BtnSearch)
/*
    FIM CONTROLLER
 */
?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>

<div class="col-md-12 txt-preto espacamento">
    <form id="buscaBanner" name="buscaBanner" method="post">
        <span class="col-md-4">
            <label for="environment">Consulta CEP via site:</label>
        </span>
        <span class="col-md-4">
            <select name="site_cons" id="site_cons" class="form-control right">
                <option value="">Selecione um Site</option>
<?php 
            $atual = (isset($_POST['site_cons'])) ? $vetorCEP[$vetorCEP_Legenda[$vetorCEP[$_POST['site_cons']]]] : $environment;
            
            foreach ($vetorCEP as $key => $value) {
?>
                
                <option value="<?php echo $key;?>" <?php  if($value == $atual) echo "selected" ?>><?php echo $vetorCEP_Legenda[$value];?></option>
<?php
            }//end foreach
?>
            </select>
        </span>
        <div class="col-md-4">
            <button type="submit" name="BtnSearch" value="Alterar" class="btn pull-left btn-success " onClick='return confirm("Deseja realmente alterar o site de Consulta de CEP?");'>Alterar</button>
        </div>
    </form>
</div>
<?php
if(isset($msg)) echo '<div class="col-md-12 top20 txt-preto"><p>'.$msg.'</p></div>';
?>
<div class="bloco row">
<?php

require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</div>    
</body>
</html>