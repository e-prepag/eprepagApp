<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto.'sftp/connect.php';
require_once $raiz_do_projeto.'sftp/classSFTPconnection.php';
require_once $raiz_do_projeto."class/util/EmailEnvironment.class.php";
if(b_IsBKOUsuarioAdminServidorEmails()) {
/* 
    CONTROLLER
 */
//Settando o $vetorLegenda
$vetorLegenda = EmailEnvironment::serverList();
//Settando inicialmente a variável $environment
if(!isset($environment)) $environment = EmailEnvironment::serverId();
//Verificando se executou o click no botão atualizar
if(isset($BtnSearch) && $BtnSearch) {
        //Variavel de OUTPUT
        $msg = '';
        //Montando Arquivo com a Configuração ATUAL 
        $conteudoArquivo = '<?php
// '.date('Y-m-d H:i:s').'
// Constante que define o Servidor de E-Mail. Onde (Emails.COM = 1) ou (Emails.COM.BR = 2)
define("EMAIL_SERVER",'.$environment.'); 
?>';
        $newfile = fopen($raiz_do_projeto."includes/configEmail.php", 'w');
        if(fwrite($newfile, $conteudoArquivo)) {
                $msg = "<span class='txt-verde'>Sucesso na atualização das configurações!<br>O novo Servidor de Envio de Emails é ".$vetorLegenda[$environment]."!</span>";
                fclose($newfile);
                $nome_arquivo = "configEmail.php";
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
<div class="col-md-12 txt-preto">
    <form id="buscaBanner" name="buscaBanner" method="post">
        <span class="col-md-4">
            <label for="environment">Ambiente de Servidor de Envio de Emails:</label>
        </span>
        <span class="col-md-4">
            <select name="environment" id="environment" class="form-control right">
            <?php 
            foreach ($vetorLegenda as $key => $value) {
            ?>
                <option value="<?php echo $key;?>" <?php  if($environment == $key) echo "selected" ?>><?php echo $value;?></option>
            <?php
            }//end foreach
            ?>
            </select>
        </span>
        <div class="col-md-4">
            <button type="submit" name="BtnSearch" value="Alterar" class="btn pull-left btn-success " onClick='return confirm("Deseja realmente alterar as configurações do Servidor de Envio de Emails?");'>Alterar</button>
        </div>
    </form>
</div>
<?php
if(isset($msg)) echo '<div class="col-md-12 top20 txt-preto"><p>'.$msg.'</p></div>';
?>
<div class="bloco row">
<?php
} //end if(b_IsBKOUsuarioAdminServidorEmails())
else {
    echo "Seu usuário não possui acesso a este programa";
}//end else do if(b_IsBKOUsuarioAdminConsultaCPF())
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</div>    
</body>
</html>