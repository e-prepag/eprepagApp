<?php
//Caminho antigo no windows: C:/Sites/E-Prepag/incs/configISSCidade.php
//Caminho novo no linux: /www/includes/configISSCidade.php

//error_reporting(E_ALL); 
//ini_set('display_errors', 1);


require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";

require_once $raiz_do_projeto."includes/configISSCidade.php";
require_once $raiz_do_projeto.'sftp/connect.php';
require_once $raiz_do_projeto.'sftp/classSFTPconnection.php';

/* 
    CONTROLLER
 */
$vetorISSCidade = array('SIM_ISS_CIDADE' => 1, 'NAO_ISS_CIDADE' => 0);

$vetorISSCidade_Legenda = array(1 => 'ISS por Cidade Ativo', 0 => 'ISS por Cidade INATIVO');

//Verificando se executou o click no botão atualizar
if(!isset($environment)) $environment = ISS_CIDADE;


$array_content_page = file($raiz_do_projeto."includes/configISSCidade.php");


//Verificando se executou o click no botão atualizar
if(isset($BtnSearch) && $BtnSearch) {
        //Variavel de OUTPUT
        $msg = '';
        //Montando Arquivo com a Configuração ATUAL 
        $conteudoArquivo = '<?php
// '.date('Y-m-d H:i:s').'
// Constante que define se deve ser considerado ISS por Cidade ou NÃO. Onde (SIM deve ser considerado ISS por Cidade = 1) ou (NÃO deve ser considerado ISS por Cidade = 0)
define("ISS_CIDADE",'.$environment.'); 
?>';
        $newfile = fopen($raiz_do_projeto."includes/configISSCidade.php", 'w');
        if(fwrite($newfile, $conteudoArquivo)) {
                $msg = "<span class='txt-verde'>Sucesso na atualização das configurações!<br>A nova forma de tributação é ".$vetorISSCidade_Legenda[$environment]."!</span>";
                fclose($newfile);
                $nome_arquivo = "configISSCidade.php";
                $arquivo = $raiz_do_projeto."includes/".$nome_arquivo;
                if(SFTP_TRANSFER && file_exists($arquivo)){
                    $arq = trim(str_replace('/', '\\', $arquivo));

                    //enviar para os servidores via sFTP
                    $sftp = new SFTPConnection($server, $port);
                    $sftp->login($user, $pass);
                    $sftp->uploadFile($raiz_do_projeto."includes/".$nome_arquivo, "E-Prepag/incs/".$nome_arquivo);

                    $msg .= "<br><br>Arquivo de configuração enviado ao servidor Windows 2003";

                }
        }
        else $msg = "<span class='txt-vermelho'>Erro ao salvar as configurações contacte o Administrador imediatamente!</span>";
} // end if(isset($BtnSearch) && $BtnSearch) 
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
</div>
<div class="col-md-12 txt-preto espacamento">
    <form id="buscaBanner" name="buscaBanner" method="post">
        <span class="col-md-4">
            <label for="environment">ISS por Cidade:</label>
        </span>
        <span class="col-md-4">
            <select name="environment" id="environment" class="form-control right">
                <option value="">Selecione o tipo de tributação ISS</option>
<?php 
            foreach ($vetorISSCidade as $key => $value) {
?>
                
                <option value="<?php echo $value;?>" <?php  if($value == $environment) echo "selected" ?>><?php echo $vetorISSCidade_Legenda[$value];?></option>
<?php
            }//end foreach
?>
            </select>
        </span>
        <div class="col-md-4">
            <button type="submit" name="BtnSearch" value="Alterar" class="btn pull-left btn-success " onClick='return confirm("Deseja realmente alterar a forma de considerar o ISS?");'>Alterar</button>
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