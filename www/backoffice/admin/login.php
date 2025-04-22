<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";

$file = $raiz_do_projeto."includes/attrLogin.php";

if(isset($_POST['tempoBloqueioLan']) && $_POST['tempoBloqueioLan'] != "") 
{
    foreach($_POST as $ind => $val)
    {
        if(!is_numeric($val))
        {
            $msg = "Os valores devem ser somente números inteiros.";
            $color = "txt-vermelho";
        }
    }
    
    if(!isset($msg)){
        //Montando Arquivo com a Configuração ATUAL 
        $conteudoArquivo = '<?php
        //configucações de segurança para o login de lan houses
        $cfgLoginLan = new stdClass();
        $cfgLoginLan->tempoMaxBloqueio = '.$_POST['tempoBloqueioLan'].';
        $cfgLoginLan->maxTentativas = '.$_POST['maxTentativasLan'].';

        //configucações de segurança para o login de gamers
        $cfgLoginGamer = new stdClass();
        $cfgLoginGamer->tempoMaxBloqueio = '.$_POST['tempoBloqueioGamer'].';
        $cfgLoginGamer->maxTentativas = '.$_POST['maxTentativasGamer'].';
?>';
        $newfile = fopen($file, 'w');
        if(fwrite($newfile, $conteudoArquivo)) 
        {
            $color = "txt-verde";
            $msg = "Sucesso na atualização das configurações!";
            fclose($newfile);
        }
        else
        {
            $msg = "Erro ao salvar as configurações contacte o Administrador imediatamente!";
            $color = "txt-vermelho";
        }
    }
    
} // end if($BtnSearch)

if(file_exists($file))
    require_once $file;

?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><?php echo $sistema->item->getDescricao() ; ?></li>
    </ol>
</div>
<?php
if(isset($msg))
{
?>
    <div class="col-md-12 espacamento <?php echo $color;?>">
        <strong><?php echo $msg?></strong>
    </div>
<?php
}
?>
<form method="POST" id="listaPedido">
    <div class="col-md-7 espacamento">
        <table class="table table-bordered">
            <thead class="">
                <tr>
                    <th>&nbsp;</th>
                    <th>Máximo de tentativas</th>
                    <th>Tempo de espera após bloqueio</th>
                </tr>
            </thead>
            <tbody title="Clique para editar">
                <tr class="bannersOpt" id="22">
                    <td class="text-right">Lan House</td>
                    <td class="text-center">
                        <input type="text" name="maxTentativasLan" maxlength="2" id="maxTentativasLan" value="<?php if(isset($cfgLoginLan->maxTentativas)) echo $cfgLoginLan->maxTentativas; ?>" class="form-control widthInputMoeda">
                    </td>
                    <td class="text-center">
                        <input type="text" name="tempoBloqueioLan" maxlength="3" id="tempoBloqueioLan" value="<?php if(isset($cfgLoginLan->tempoMaxBloqueio)) echo $cfgLoginLan->tempoMaxBloqueio; ?>" class="form-control widthInputMoeda pull-left"> <span class="pull-left">segundos</span>
                    </td>
                </tr>
                <tr class="bannersOpt" id="21">
                    <td class="text-right">Gamer</td>
                    <td class="text-center">
                        <input type="text" name="maxTentativasGamer" maxlength="2" id="maxTentativasGamer" value="<?php if(isset($cfgLoginGamer->maxTentativas)) echo $cfgLoginGamer->maxTentativas; ?>" class="form-control widthInputMoeda pull-left">
                    </td>
                    <td class="text-center">
                        <input type="text" name="tempoBloqueioGamer" maxlength="3" id="tempoBloqueioGamer" value="<?php if(isset($cfgLoginGamer->tempoMaxBloqueio)) echo $cfgLoginGamer->tempoMaxBloqueio; ?>" class="form-control widthInputMoeda pull-left"> <span class="pull-left">segundos</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="col-md-5 espacamento">
        <button type="submit" class="btn btn-success">Editar</button>
    </div>
</form>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>