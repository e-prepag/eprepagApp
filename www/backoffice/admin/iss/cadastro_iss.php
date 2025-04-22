<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/gamer/constantes.php";

/* 
    CONTROLLER
 */
//Verificando se executou o click no botão Consultar
if(isset($BtnSearch) && $BtnSearch) {
        //Variavel de OUTPUT
        $msg = '';
        //Consultar Cidades no Banco de Dados
        $sql = "
                SELECT ug_cidade, iss_aliquota, count(*) AS total 
                FROM usuarios_games 
                    LEFT OUTER JOIN iss_cidade ON (ug_cidade = iss_cidade AND iss_estado = '".$ug_estado."')
                WHERE ug_estado = '".$ug_estado."'
                GROUP BY ug_cidade, iss_aliquota
                ORDER BY ug_cidade; 
                ";
        //echo $sql."<br>";
        $rs = SQLexecuteQuery($sql);
        if(!$rs) {
                $msg .= "ERRO: Problema na seleção das Cidades.<br>";
        }//end do if($rs)
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
    <form id="buscaCidades" name="buscaCidades" method="post">
        <span class="col-md-2">
            <label for="ug_estado">Estado:</label>
        </span>
        <span class="col-md-2">
            <select name="ug_estado" id="ug_estado" class="form-control right">
            <?php 
            foreach ($SIGLA_ESTADOS as $key => $value) {
            ?>
                <option value="<?php echo $value;?>" <?php  if(isset($ug_estado) && $ug_estado == $value) echo "selected" ?>><?php echo $value;?></option>
            <?php
            }//end foreach
            ?>
            </select>
        </span>
        <div class="col-md-2">
            <button type="submit" name="BtnSearch" value="Consultar" class="btn pull-left btn-success ">Consultar</button>
        </div>
        <div class="col-md-6">
        </div>
    </form>
</div>
<?php
if(isset($rs) && $rs) {
    $contadorLinhas = 0;
?>
<div class="col-md-12 top20">
    <div class="alert alert-info" role="alert">Para alterar/cadastrar alguma aliquota de ISS, clique sobre a cidade.</div>
</div>
<div class="col-md-12">
    <table class="text-center table table-bordered table-hover" >
        <thead class="">
            <tr>
                <th class="text-center">Cidade</th>
                <th class="text-center">Alíquota ISS</th>
                <th class="text-center">Qtde. Registros</th>
            </tr>
        </thead>
        <tbody title="Clique para editar">      
<?php
    while ($rsRow = pg_fetch_array($rs)) {
        $contadorLinhas++;
?>
        <tr class="opt trListagem" nome="<?php echo $rsRow['ug_cidade']; ?>">
           <td><?php echo $rsRow['ug_cidade']; ?></td>
           <td><?php echo (!is_null($rsRow['iss_aliquota'])?number_format($rsRow['iss_aliquota'], 2, ",", "."):" --"); ?></td>
           <td><?php echo $rsRow['total']; ?></td>
       </tr>        
<?php        
    } //end while
?>
        </tbody>
    </table>
</div>
<?php
    if($contadorLinhas > 0) {
?>
<div class="col-md-12">
    <div class="col-md-12 alert alert-info" role="alert">
        <div class="col-md-6">
            Total de cidade<?php echo($contadorLinhas>1?"s":""); ?>: <?php echo $contadorLinhas;?>
        </div>
        <div class="col-md-6 text-right">
            Legenda: (--) Alícota ISS não cadastrada.
        </div>
    </div>
</div>
<?php
    }//end if($contadorLinhas > 0)
?>
<form method="post" action="edita_iss.php" id="frmItensAba">
    <input type="hidden" id="nome" name="nome" value="">
    <input type="hidden" id="ug_estado" name="ug_estado" value="<?php echo $ug_estado; ?>">
</form>
<script>
    $(function(){
        $(".opt").click(function(){
            $("#nome").val($(this).attr("nome"));
            $("#frmItensAba").submit();
        });
        
        $("#aba").change(function(){
            $("#menu").val(false);
            $("#filtro").submit();
        });
        
    });
</script>  
<?php    
} //end if(isset($rs) && $rs)
if(isset($msg)) echo '<div class="col-md-12 top20 txt-preto"><p>'.$msg.'</p></div>';
?>
<div class="bloco row">
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</div>    
</body>
</html>