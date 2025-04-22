<?php 
// Atualizando dados
$sqlCM = "SELECT * FROM classificacao_mapas order by cm_id desc"; //cm_id, cm_nome, cm_status, cm_data_cadastro, opr_codigo

$rss = SQLexecuteQuery($sqlCM);
$tot = 0;
if($rss) {
    $tot = pg_num_rows($rss);

    $arrClassMapas = array();

    while($publishers = pg_fetch_array($rss)) 
    {
        $publisher = new stdClass;
        $publisher->id = $publishers['cm_id'];
        $publisher->nome = $publishers['cm_nome'];
        $publisher->status = ($publishers['cm_status'] == 1) ? "Ativo" : "Inativo";
        $publisher->dataCadastro = substr($publishers['cm_data_cadastro'],0,10);
        $publisher->oprCodigo = ($publishers['opr_codigo'] ? $publishers['opr_codigo'] : "Não Possui");

        array_push($arrClassMapas,$publisher);
    }
}

require_once DIR_CLASS."util/Util.class.php";
?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><?php echo $sistema->item->getDescricao() ; ?></li>
    </ol>
</div>
<div class="col-md-12">
    <a href="classificacao_mapas.php?acao=novo" class="btn btn-sm btn-info">Nova Classificação</a>
</div>
<div class="col-md-12">
    <table class="table txt-preto top20 table-bordered text-center" >
        <thead class="">
            <tr>
                <th>ID</th>
                <th>Publisher</th>
                <th>Status</th>
                <th>Data Cadastro</th>
                <th>Código do Publisher</th>
            </tr>
        </thead>
        <tbody title="Clique para editar">
<?php 
    if($tot >0) 
    {
        foreach($arrClassMapas as $classMapas)
        {
            $data = Util::getData($classMapas->dataCadastro." 00:00:00");
?>
            <tr class="bannersOpt trListagem c-pointer" id="<?php echo $classMapas->id; ?>">
                <td class='style1'><?php echo $classMapas->id; ?></td> 
                <td class='style1'><?php echo $classMapas->nome; ?></td> 
                <td class='style1'><?php echo $classMapas->status; ?></td> 
                <td class='style1'><?php echo $data; ?></td> 
                <td class='style1'><?php echo $classMapas->oprCodigo; ?></td> 
            </tr>
<?php
        }
    }else
    {
?>
            <tr>
                <td colspan="5">Nenhum resultado foi encontrado.</td>
            </tr>
<?php
    }
?>
        </tbody>
    </table>
</div>

<script>
    $(function(){
        $(".bannersOpt").click(function(){
            window.location = "/dist_commerce/classificacao_mapas.php?acao=edita&id="+$(this).attr("id");
        });
        
        $("#buscar").click(function(){
            var erro = [];
            
            $(".form-control").each(function(){
                 if($(this).val().length < $(this).attr("char"))
                     erro.push($(this).attr("label"));
            });
            
            if(erro.length > 4)
            {
                var msgErro = "Nenhum campo foi preenchido";
                alert(msgErro);
            }
            else
               $("#"+$(this).get(0).form.id).submit();

       });
    });
</script>