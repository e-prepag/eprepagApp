<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once "/www/includes/bourls.php";
$controladoMensagem = false;

// processando Bloqueio/Desbloqueio de PDvs para Vendas Online
if(!empty($executar)) {
    if(!empty($lista_ids_pdvs)) {
        //Proteção contra usuário displicente
        $lista_ids_pdvs = str_replace(PHP_EOL, ',', $lista_ids_pdvs);
        $lista_ids_pdvs = str_replace(';', ',', $lista_ids_pdvs);
        $lista_ids_pdvs = str_replace(',,', ',', $lista_ids_pdvs);
        $lista_ids_pdvs = str_replace(',,', ',', $lista_ids_pdvs);
        $lista_ids_pdvs = str_replace(',,', ',', $lista_ids_pdvs);
        $lista_ids_pdvs = str_replace(',,', ',', $lista_ids_pdvs);
        $lista_ids_pdvs = str_replace(',,', ',', $lista_ids_pdvs);
        $lista_ids_pdvs = str_replace(',,', ',', $lista_ids_pdvs);
        $lista_ids_pdvs = str_replace(',,', ',', $lista_ids_pdvs);
        $lista_ids_pdvs = str_replace(',,', ',', $lista_ids_pdvs);
        $lista_ids_pdvs = str_replace(',,', ',', $lista_ids_pdvs);
        if(substr($lista_ids_pdvs,-1) == ',') $lista_ids_pdvs = substr($lista_ids_pdvs,0,strlen($lista_ids_pdvs)-1);
        if(substr($lista_ids_pdvs,0,1) == ',') $lista_ids_pdvs = substr($lista_ids_pdvs,1,strlen($lista_ids_pdvs));
        //Fim da proteção contra usuário displicente
        $sql = "UPDATE dist_usuarios_games SET ug_possui_restricao_produtos = ".(($acao == "Bloquear")?"1":"0")." WHERE ug_id IN (".$lista_ids_pdvs.");";
        $rs_bloqueios = SQLexecuteQuery($sql);
        $cmdtuples = pg_affected_rows($rs_bloqueios);
        if($cmdtuples > 0) {
            $controladoMensagem = $cmdtuples." PDV".(($cmdtuples > 1)?"s":"")." ".(($cmdtuples > 1)?"foram":"foi")." alterado".(($cmdtuples > 1)?"s":"")." para ".(($acao == "Bloquear")?"BLOQUEADO":"DESBLOQUEADO").(($cmdtuples > 1)?"S":"").", confira a lista atualizada abaixo.";
        }
        else {
            $controladoMensagem = "Nenhum PDV foi alterado para ".(($acao == "Bloquear")?"BLOQUEADO":"DESBLOQUEADO")."!";
        }
    }//end if(!empty($lista_ids_pdvs))
    else {
        $controladoMensagem = "Necessário Informar a Lista de IDs de PDVs para Executar a Ação Selecionada!";
    }//end else do if(!empty($lista_ids_pdvs)) 
}//end if(!empty($executar))
?>
<link href="<?php echo $url; ?>:<?php echo $server_port ;?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port ;?>/js/jquery-ui.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port ;?>/js/global.js"></script>
<script>
    $(document).ready(function(){
       $("#formulario").submit (
               function(){
                   var teste = $("#lista_ids_pdvs").val();
                   if(teste != "") {
                        //teste = teste.replace(/[^\d]+/g,'')
                        var reg = /(?:[^,\d])/;
                        if(reg.test(teste)) {
                            $("#erro").html("Por favor, utilize separação por vírgulas(,) entre os IDs de PDVs.");
                            $('#modal-load').modal('show');
                            return false;
                        }
                        else true;
                   }
                   else {
                       $("#erro").html("Por favor, informe a lista de IDs de PDVs a serem processados separados por vírgula(,).");
                       $('#modal-load').modal('show');
                       return false;
                   }
               }); 
    });
       
</script>    
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<div id="modal-load" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title txt-vermelho" id="modal-title"><b>ATENÇÃO</b></h4>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger" id="tipo-modal" role="alert"> 
                    <h5><span id="error-text"><div class="row"><div class="col-md-12"><span id="erro" name="erro"></span></div></span></h5>
              </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    
    <div class="col-md-12">
        
        <form class="form-inline row" id="formulario" name="formulario" method="POST">
            <div class="form-group col-md-8">
                <label for="exampleInputName2">Lista de IDs de PDVs</label>
                <input type="text" class="form-control" placeholder="Informar a lista de IDs" id="lista_ids_pdvs" name="lista_ids_pdvs" style="width:77%!important">
            </div>
            <div class="form-group col-md-3">
                <label for="exampleInputName3">Ação:</label>
                <select class="form-control" name="acao" id="acao" required="required">
                    <option value="Bloquear"<?php (empty($acao)||$acao == "Bloquear")?" selected='selected'":""?>>Bloquear</option>
                    <option value="Desbloquear"<?php (isset($acao) && $acao == "Desbloquear")?" selected='selected'":""?>>Desbloquear</option>
                </select>
            </div>
            <div class="form-group col-md-1">
                <button type="submit" class="btn btn-info left20" id="executar" name="executar" value="Submit">Executar</button>
                <span class="loading left5"></span>
            </div>
        </form>
        
    </div>
    
</div>

<div class="panel panel-default top20">
    <div class="panel-heading">
        <h3 accesskey=""class="panel-title">Lista de PDVs Bloqueados para Vendas Online</h3>
    </div>
    <div class="panel-body">
    <?php
        $sql = "SELECT ug_id, ug_login FROM dist_usuarios_games WHERE ug_possui_restricao_produtos = 1 ORDER BY ug_login;";
        $rs = SQLexecuteQuery($sql); 
        if($rs && pg_num_rows($rs) > 0 ) {
            echo '<div class="row borda-baixo-basica p-8"><div class="col-md-2"><b>ID do PDV</b></div><div class="col-md-10"><b>Login do PDV</b></div></div><br>';
            while($rs_row = pg_fetch_array($rs)) {
                echo '<div class="row"><div class="col-md-2">'.$rs_row['ug_id'].'</div><div class="col-md-10">'.$rs_row['ug_login'].'</div></div>';
            } //end while   
        }//end if($rs)
        else echo "Nenhum PDV possui restrição de vendas Online!";
    ?>
    </div>
</div>
<?php
if($controladoMensagem) {
     echo "<script>  $('#erro').html('".$controladoMensagem."'); $('#modal-load').modal('show');</script>";
}
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</div>    
</body>
</html>