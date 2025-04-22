<?php 
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";

$ug_ativo	= $_POST['ug_ativo'];
$dataini	= $_POST['dataini'];
$datafim	= $_POST['datafim'];
$tipo		= $_POST['tipo'];
$centro	 	= $_POST['centro'];
$zoom	 	= $_POST['zoom'];
$chk_ug_id	= $_POST['chk_ug_id'];

$https = 'http' . (($_SERVER['HTTPS']=='on') ? 's' : '');
$need_key_maps = (checkIP())?"sensor=false":"key=AIzaSyA25PAcZMc6toew3UDW1HwG8wve00r8hb4";

if(count($chk_ug_id)>0) {
	$lista_ug_id = implode(",", $chk_ug_id);
	$sqlUpdate = "update dist_usuarios_games set ug_coord_lat=0, ug_coord_lng=0 where ug_id IN ($lista_ug_id)";
	//echo $sqlUpdate."<br>";
	$ret = SQLexecuteQuery($sqlUpdate);
	if(!$ret){
		echo "<fonr style='color:red'>Erro ao limpar as coordenadas (ug_ids: $lista_ug_id).</font>";
	} else {
		echo "<fonr style='color:blue'>Coordenadas limpas com sucesso (ug_ids: $lista_ug_id).</font>";
	}
}

if ($_SERVER['PHP_SELF']!=str_replace("http://".$_SERVER['HTTP_HOST'],'',$_SERVER['HTTP_REFERER'])) {
	$dd_faturamento = '1';
}

if(empty($dataini)) {
	$dataini = date('d/m/Y');
}

if(empty($datafim)) {
	$datafim = date('d/m/Y');
}

if(empty($ug_ativo)) {
	$ug_ativo = 0;
}

// Deixa o drop down nos valores que estavam selecionados antes do reload.
$valorRequestCidade = $_POST['cidade'];
if ((isset($_REQUEST['cidade'])) and (isset($_REQUEST['bairro']))){
	$SQLBairro = "SELECT distinct(ug_bairro) as ug_bairro
				FROM dist_usuarios_games
				WHERE ug_cidade = '".$valorRequestCidade."'
				ORDER BY ug_bairro";
	$ResultadoBairro = SQLexecuteQuery($SQLBairro);
}

// Deixa o drop down nos valores que estavam selecionados antes do reload.
$valorRequestEstado = $_POST['estado'];
if ((isset($_REQUEST['estado'])) and (isset($_REQUEST['cidade']))){
	$SQLCidade = "SELECT ug_cidade, ug_estado
						FROM dist_usuarios_games
						WHERE ug_estado = '".$valorRequestEstado."'
						GROUP BY ug_cidade, ug_estado 
						ORDER BY ug_cidade";
	//echo "SQLCidade: $SQLCidade<br>";
	//die("Stop");
	$ResultadoCidade = SQLexecuteQuery($SQLCidade);
}

// Query que cria o drop drown dos Estados
$SQLEstado = "SELECT ug_estado
					FROM dist_usuarios_games
					GROUP BY ug_estado 
					ORDER BY ug_estado";
//echo "SQLCidade: $SQLEstado<br>";
//die("Stop");
$ResultadoEstado = SQLexecuteQuery($SQLEstado);


?>
    <link type="text/css" href="/css/mapas/style.css" rel="stylesheet" media="all" />
    <script type="text/javascript" src="<?php echo $https; ?>://maps.google.com/maps/api/js?<?php echo $need_key_maps; ?>"></script>
    <link href="/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
    <script src="/js/jquery-ui-1.9.2.custom.min.js"></script>
    <script src="/js/global.js"></script>
    <script language="javascript">
        $(function(){
           var optDate = new Object();
                optDate.interval = 10000;

            setDateInterval('dataini','datafim',optDate);

        });
    
		function MostraBairro() {
			if ( document.formFat.cidade.value != "" ){
				cidade = document.formFat.cidade.value;
				estado = document.formFat.estado.value;
				$.ajax({
					type: "POST",
					url: "/ajax/pdv/mapas/lan_house_select_bairro.php",
					data: "cidade=" + cidade + "&estado=" + estado,
					beforeSend: function(){
						$("#SelBairro").html("<select class='form-control input-sm' DISABLED><option>Buscando Bairro</option></select>");
					},
					success: function(txt){
						$("#SelBairro").html(txt);
					},
					error: function(){
					$("#SelBairro").html("ERRO");
					}
				});
			}
		}

		function MostraCidade() {
			if ( document.formFat.estado.value != "" ){
				estado = document.formFat.estado.value;
				$.ajax({
					type: "POST",
					url: "/ajax/pdv/mapas/lan_house_select_cidade.php",
					data: "estado=" + estado,
					beforeSend: function(){
						$("#SelCidade").html("<select class='form-control input-sm' DISABLED><option>Buscando Cidade</option></select>");
					},
					success: function(txt){
						$("#SelCidade").html(txt);
					},
					error: function(){
					$("#SelCidade").html("ERRO");
					}
				});
			}
		}

		function marcar_desmarcar() {
			frm = document.formFat;
			for ( i=1; i < frm.elements.length; i++ ) {
				if ( frm.elements[i].type == "checkbox" ) {
					if ( frm.elements[i].checked == 1 ) {
					   frm.elements[i].checked = 0;
					} else {
					   frm.elements[i].checked = 1;
					}
				}
			}
		}
        
        $(function(){
           inicializa(); 
        });
	</script>
    <div class="col-md-12">
        <ol class="breadcrumb top10">
            <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
            <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
            <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
        </ol>
    </div>
    <div id="filtros" class="col-md-12 txt-preto fontsize-p">
        <h4>Filtros:</h4>
    <form id="formFat" name="formFat" method="post" action="mapafaturamento.php">
        <div class="form-group col-md-3">
            <label for="pin">Data Inicial:</label>
            <input type="text" name="dataini" id="dataini" class='form-control input-sm' value="<?php echo $dataini; ?>"/>
        </div>
        <div class="form-group col-md-3">
            <label for="pin">Data Final:</label>
            <input type="text" name="datafim" id="datafim" class='form-control input-sm'  value="<?php echo $datafim; ?>"/>
        </div>
        <div class="form-group col-md-3">
            <label for="pin">Status:</label>
            <select name="ug_ativo" class='form-control input-sm' id="ug_ativo" >
                <option value="0" selected="selected">TODOS</option>
                <option value="1" <?php if($ug_ativo == 1) { ?> selected <?php } ?>>Ativo</option>
                <option value="2" <?php if($ug_ativo == 2) { ?> selected <?php } ?>>Inativo</option>
            </select>
        </div>
          
        <div class="form-group col-md-3">
            <label for="pin">Tipo:</label>
            <select name="tipo" class='form-control input-sm' id="tipo" >
                <option value="0" selected="selected">TODOS</option>
                <option value="PF" <?php if($tipo == 'PF') { ?> selected <?php } ?>>Pessoa Física</option>
                <option value="PJ" <?php if($tipo == 'PJ') { ?> selected <?php } ?>>Pessoa Jurídica</option>
            </select>
        </div>
        <div class="form-group col-md-3">
            <label for="pin">Faturamento:</label>
            <input type="checkbox" name="dd_faturamento" id="dd_faturamento" <?php if ($dd_faturamento=='1') echo "checked";?> value='1'>
        </div>
        
        <div class="form-group col-md-9">
            <label for="pin">Habilitada OnGame:</label>
            <input type="checkbox" name="dd_ongame" id="dd_ongame" <?php if ($dd_ongame=='1') echo "checked";?> value='1'>
        </div>
        
		<div class="form-group col-md-3">
            <label for="pin">Estado:</label>
            <div id="SelEstado">
                
                <select name="estado" id="estado" class='form-control input-sm' onChange='MostraCidade();'>
                    <option value="">Selecione um estado</option>
                    <?php
                    // Gera os dados do drop down estado
                    while ($RowEstado = pg_fetch_array($ResultadoEstado)){
                        if (!empty($RowEstado['ug_estado'])) {
                            echo '<option value="'.$RowEstado['ug_estado'].'"';
                            if ($valorRequestEstado == $RowEstado['ug_estado']){
                                echo " SELECTED ";
                            }
                            echo '>'.$RowEstado['ug_estado'].'</option>';
                        }
                    }
                ?>
                </select>
             </div>
        </div>
        
        <div class="form-group col-md-3">
            <label for="pin">Cidade:</label>
            <div id="SelCidade">
                <?php
                if ($ResultadoCidade){
                    echo '<select name="cidade" id="cidade" class="form-control input-sm" onChange="MostraBairro();">';
                    echo '<option value="">Todos as Cidades</option>';
                    while ($RowCidade = pg_fetch_array($ResultadoCidade)){
                        echo '<option value="'.$RowCidade['ug_cidade'].'"';
                        if ($_REQUEST['cidade'] == $RowCidade['ug_cidade'] && !empty($RowCidade['ug_cidade'])){
                            echo " SELECTED ";
                        }
                        echo '>'.$RowCidade['ug_cidade'].'</option>';
                    }
                    echo  '</select>';
                }else{
                ?>
                <select name="cidade" class='form-control input-sm' id="cidade" DISABLED>
                        <option>Selecione uma Cidade</option>		
                </select>
                <?php
                }
                ?>
             </div>
        </div>
        
        <div class="form-group col-md-3">
            <label for="pin">Bairro:</label>
            <div id="SelBairro">
			<?php
			if ($ResultadoBairro){
				echo '<select name="bairro" id="bairro" class="form-control input-sm">';
				echo '<option value="">Todos os Bairros</option>';
				while ($RowBairro = pg_fetch_array($ResultadoBairro)){
					echo '<option value="'.$RowBairro['ug_bairro'].'"';
					if ($_REQUEST['bairro'] == $RowBairro['ug_bairro'] && !empty($RowBairro['ug_bairro'])){
						echo " SELECTED ";
					}
					echo '>'.$RowBairro['ug_bairro'].'</option>';
				}
				echo  '</select>';
			}else{
			?>
			<select name="bairro" id="bairro" class='form-control input-sm' DISABLED>
					<option>Selecione um Bairro</option>		
			</select>
			<?php
			}
			?>
            </div>
        </div>
        <div class="form-group col-md-3">
            <input type="button" name="buscar" class="btn btn-info top20" id="buscar" value="Buscar Dados" />
            <input type="hidden" name="zoom" id="zoom" value="<?php echo $zoom; ?>">
            <input type="hidden" name="centro" id="centro" value="<?php echo $centro; ?>">
        </div>
        <div class="col-md-12">
            <div class="col-md-9">
                <div id="marcado"></div>
            </div>
			<div class="col-md-9">
                <div id="botaolimpar"></div>
            </div>    
        </div>
		</form>
	</div>
    <div class="col-md-9" style="max-width: 700px;" id="map">
    </div>
    <div class="col-md-3 txt-preto">
        <p>Legenda:</p>
        <div id="legendas"></div>
<?php
        include('mapfat.php');
?>

    </div>
</body>
</html>
