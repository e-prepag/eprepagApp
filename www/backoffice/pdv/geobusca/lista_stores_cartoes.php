<?php 
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";

// Atualizando dados
if(isset($Submit) && $Submit=="RESPONDER") {
    
    $sql = "UPDATE dist_usuarios_stores_cartoes SET
                us_endereco		= '".str_replace("'",'"',$us_endereco)."',
                us_bairro		= '".str_replace("'",'"',$us_bairro)."',
                us_cidade		= '".str_replace("'",'"',$us_cidade)."',
                us_estado		= '".str_replace("'",'"',$us_estado)."',
                us_cep			= '".str_replace("'",'"',$us_cep)."'
            WHERE	us_id	= $us_id";
    //echo $sql."<br>:SQL<br>";
    $rs_stores_cartoes = SQLexecuteQuery($sql);
    if(!$rs_stores_cartoes) 
    {
            $msg .= "Erro ao atualizar informa&ccedil;&otilde;es da loja de cartão. ($sql)<br>";
    }
    else 
    {
            $msg .= "Sucesso ao atualizar informa&ccedil;&otilde;es da loja de cartão ID:($us_id).<br>";
    }
}//end if($Submit=="RESPONDER") 

$sql = "SELECT * FROM dist_usuarios_stores_cartoes ORDER BY us_id ;";
$sqlCM = "SELECT * FROM classificacao_mapas where cm_status = 1";
$sqlRelacao = "select * from classificacao_mapas_pdv";

//echo "$sql<br>";
$rss = SQLexecuteQuery($sql);
$tot = pg_num_rows($rss);

$rssCM = SQLexecuteQuery($sqlCM);

$rssRelacao = SQLexecuteQuery($sqlRelacao);

$publishers = array();

while($cm = pg_fetch_array($rssCM)) {
    $publishers[$cm['cm_id']] = $cm['cm_nome'];
}


$relacoes = array();

while($relacao = pg_fetch_array($rssRelacao)) {
    $arr = array();
    $arr['us_id'] = $relacao['us_id'];
    $arr['cm_id'] = $relacao['cm_id'];
    
    array_push($relacoes, $arr);
    unset($arr);
}

$a_fields = array(
	'us_id', 
	'us_nome_loja', 
	'us_endereco', 
	'us_numero', 
	'us_complemento', 
	'us_bairro', 
	'us_cidade', 
	'us_estado', 
	'us_cep', 
	'us_tel', 
	'us_tipo_store', 
	'us_regiao', 
	'us_classif_store', 
	'us_coord_lat', 
	'us_coord_lng', 
	'us_google_maps_string', 
	'us_google_maps_status'
);

$rows = array();

while($valores = pg_fetch_array($rss)) {
    $table = array();
    
    foreach($a_fields as $column){
        $table[$column] = $valores[$column];
    }
    
    foreach($publishers as $id => $column){
        $table[$column] = 0;
    }
    
    foreach($relacoes as $relacao){
        if($relacao['us_id'] == $valores['us_id']){
            if(isset($publishers[$relacao["cm_id"]]))
                $table[$publishers[$relacao["cm_id"]]] = 1;
        }
    }
    
    $rows[$valores['us_id']] = $table;
    
    unset($table);
}
?>
<style>
    .ui-widget-overlay .ui-front{z-index:-1;}
<!--
.style1 {font-size: 12px; vertical-align: top}
-->

.tb-store-cards{
    top: 10px;
}

@media (min-width: 600px) {
    .tb-store-cards {
        left: 0px;
        position: absolute;
    }
}
@media (min-width: 908px) {
    .tb-store-cards {
        left: -100px;
        position: absolute;
    }
}
@media (min-width: 991px) {
    .tb-store-cards {
        left: 0px;
        position: absolute;
    }
}
@media (min-width: 1408px) {
    .tb-store-cards {
        left: -200px;
        position: absolute;
    }
}
@media (min-width: 1732px) {
    .tb-store-cards {
        left: -358px;
        position: absolute;
    }
}


</style>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<script language="javascript">
	// Edição de Dados 
	function edit_data(id){
			$.ajax({
				type:'POST',
				data:"us_id="+id,
				url:'lista_stores_cartoes_edt.php',
				beforeSend: function(){
					$('#box-edit').html("<table><tr class='box-principal-login-class'><td><img src='/images/pdv/loading1.gif' border='0' title='Aguardando pagamento...'/></td></tr><tr class='box-principal-login-class'><td><font size='1'><b>Aguarde... Verificando.</b></font></td></tr></table>"); 
				},
				success: function(txt){
					$('#box-edit').html(txt);
				},
				error: function(){
					$('#box-edit').html('ERRO');
				}
			});
	}

	function validaGeo(us_id, us_endereco, us_bairro, us_cidade, us_estado, us_pais, us_cep) {
		var us_endereco = us_endereco;
		var us_bairro   = us_bairro;
		var us_cidade   = us_cidade;
		var us_estado	= us_estado;
		var us_cep		= us_cep;
		var us_numero	= us_numero;
		
		var us_id		= us_id;
		
		var endereco	= us_endereco+', '+us_bairro+', '+us_cidade+', '+us_estado+', '+us_pais;
	
		
		window.open ("geobusca_store.php?endereco="+endereco+'&us_id='+us_id+'&us_cep='+us_cep,"geobusca_store");
	}
</script>
<?php
if(isset($msg))
    echo $msg;
?>
<div class='bg-branco col-md-12 bottom10'>
    <input type="button" class="btn btn-info pull-right" id="salvar" value="Alterar classificação">
    <span id="aguarde" style="display:none; float:right; margin: 10px 20px 0 20px;"><img width="20px;" src='/images/pdv/loading1.gif' border='0' title='Aguardando pagamento...'/></span>
</div>
<div class="col-md-12 col-lg-12 col-sm-12 col-xs-12">
    <table class="table table-bordered txt-preto bg-branco fontsize-pp tb-store-cards">
    <tr align="center">
        <td class='style1'>id</td> 
        <td class='style1'>nome_loja</td> 
        <td class='style1'>endereco</td> 
        <td class='style1'>numero</td> 
        <td class='style1'>complemento</td> 
        <td class='style1'>bairro</td> 
        <td class='style1'>cidade</td> 
        <td class='style1'>estado</td> 
        <td class='style1'>cep</td> 
        <td class='style1'>tel</td> 
        <td class='style1'>tipo_store</td> 
        <td class='style1'>regiao</td> 
        <td class='style1'>classif_store</td> 
        <td class='style1'>coord_lat</td> 
        <td class='style1'>coord_lng</td> 
        <td class='style1'>google_maps_string</td> 
        <td class='style1'>google_maps_status</td>
<?php
    foreach($publishers as $id => $publisher)
    {
        echo "<td class='style1'>$publisher <input type='checkbox' class='cAllBox' box='$id'></td>\n";
    }
?>    
  </tr>
<?php
  
    if($tot >0) 
    {
        foreach($rows as $key => $valores)
        {
            $us_endereco = "".$valores['us_id'].", '".$valores['us_endereco']."', '".$valores['us_bairro']."', '".$valores['us_cidade']."', '".$valores['us_estado']."', 'Brasil', '".$valores['us_cep']."'";
            $statusMaps = $valores['us_google_maps_status'];
            $statusMaps_descr = "";
            
            switch($statusMaps) 
            {
                case 1: 
                    $statusMaps_descr = "Não Localizada";
                    break;
                case 2: 
                    $statusMaps_descr = "Fora do Brasil";
                    break;
                default: 
                    $statusMaps_descr = "Tipo Desconhecido";
                    if(strlen(trim($statusMaps))==0) $statusMaps_descr .= " (Empty)";
                    else $statusMaps_descr .= " ('$statusMaps')";
                    break;
            }
            
            if($valores['us_coord_lat']==0 && $valores['us_coord_lng']==0) 
            {
                if($statusMaps_descr!="") $statusMaps_descr.= "\n";
                $statusMaps_descr .= "Sem Geolocalização";
            } else 
            {
                $statusMaps_descr .= "\n[".number_format($valores['us_coord_lat'], 2, '.', '.').", ".number_format($valores['us_coord_lng'], 2, '.', '.')."]";
            }
            
            if(trim($statusMaps)=="") 
            {
                if($valores['us_coord_lat']==0 && $valores['us_coord_lng']==0) 
                {
                    $statusMaps = "<font color='red'>Coords=0</font>";
                } else 
                {
                    $statusMaps = "<font color='blue'>Com_Coords</font>";
                }
            }
?>
            <tr bgcolor="#D0EFF0" style="font-family:Arial, Helvetica, sans-serif;font-size:10px;" onmouseover="bgColor='#CFDAD7'" onmouseout="bgColor='#D0EFF0'" onClick="bgColor='#CFDAD7'">
                <td class='style1'><?php echo $valores['us_id']; ?></td> 
                <td class='style1'><?php echo $valores['us_nome_loja']; ?></td> 
                <td class='style1'><?php echo $valores['us_endereco']; ?></td> 
                <td class='style1'><?php echo $valores['us_numero']; ?></td> 
                <td class='style1'><?php echo $valores['us_complemento']; ?></td> 
                <td class='style1'><?php echo $valores['us_bairro']; ?></td> 
                <td class='style1'><?php echo $valores['us_cidade']; ?></td> 
                <td class='style1'><?php echo $valores['us_estado']; ?></td> 
                <td class='style1'><?php echo $valores['us_cep']; ?></td> 
                <td class='style1'><?php echo $valores['us_tel']; ?></td> 
                <td class='style1'><?php echo $valores['us_tipo_store']; ?></td> 
                <td class='style1'><?php echo $valores['us_regiao']; ?></td> 
                <td class='style1'><?php echo $valores['us_classif_store']; ?></td> 
                <td class='style1'><?php echo $valores['us_coord_lat']; ?></td> 
                <td class='style1'><?php echo $valores['us_coord_lng']; ?></td> 
                <td class='style1'><a href="javascript:void(0);" onClick="validaGeo(<?php echo $us_endereco; ?>);"><img src="/images/pdv/global-search-icon_peq.jpg" width="28" height="21" border="0" title="Lat/Lng: [<?php echo $valores['us_coord_lat'];?> , <?php echo $valores['us_coord_lng'];?>]\n<?php echo $us_endereco; ?>"> </a></td> 
                <td class='style1' title='<?php echo $statusMaps_descr?>'><?php echo $statusMaps;?></td>
<?php
            foreach($publishers as $id => $publisher)
            {
?>
                <td class='style1' align='center'><input type='checkbox' <?php if($valores[$publisher]) echo "checked"; ?> class='cbox<?php echo $id; ?>' loja="<?php echo $valores['us_id']; ?>" name='classificacao_mapa' value='<?php echo $id; ?>'></td>
<?php
            }
?>
                <td class='style1'><img src='/images/pencil.png' width='16' height='16' border='0' alt='Editar' title='Editar' onclick='javascript:edit_data(<?php echo $valores['us_id'];?>);' style='cursor:pointer;cursor:hand;'></td>
            </tr>
<?php
        }
    }
?>
</table>
</div>
<div id='box-edit' name='box-edit' align='center'></div>
<p>&nbsp;</p>
<p>
  <?php require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php"; ?>
</p>
<script>
$(function(){
   $(".cAllBox").click(function(){
       
       var res = this.checked;
        
        $(".cbox"+$(this).attr("box")).each(function() {
            this.checked = res;
        });
   });
   
    $("#salvar").click(function(){
        var str = "";
        var loja = "";
        
        $.each($("input[name='classificacao_mapa']:checked"), function(){ //gerando string para enviar para o ajax
            if($(this).attr("loja") != loja){
                loja = $(this).attr("loja");
                str += "#"+loja+"||"; //delimitando os dados para cada loja com # e separando o id da loja do id dos publishers com ||
            }

            str += $(this).val()+"|"; //separando os publishers da respectiva loja por |
        });
        
        
        $.ajax({
            type: 'POST',
            url: '/ajax/pdv/mapas/ajax_classificacao_mapas.php',
//            dataType: "JSON",
            data: {str : str},
            beforeSend: function(){
                $("#salvar").toggle();
                $("#aguarde").toggle();
            },
            success: function(ret){
                if(ret == 1){
                    alert("Dados alterados com sucesso.");
                }else{
                    alert("ERRO.");
                }
                
                $("#salvar").toggle();
                $("#aguarde").toggle();
            },
            error: function(){
                $("#salvar").toggle();
                $("#aguarde").toggle();
                alert('Erro Valor');
            }
        });
    });
});
</script>