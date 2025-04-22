<?php 
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";

// Constante que define o ambiente de conexão Produção (Live = 1) ou Homologação (Test = 2)
if(checkIP()) {
    $server_url = $_SERVER['SERVER_NAME'];
    }
else {
    $server_url = $server_url_bo;
}
if(!isset($GLOBALS['_GET']['ncamp'])) $ncamp = 'us_cidade';
if(!isset($GLOBALS['_GET']['inicial'])) $inicial = 0;
if(!isset($GLOBALS['_GET']['range'])) $range = 1;
if(!isset($GLOBALS['_GET']['ordem'])) $ordem = 0;
$varsel = "";

$tot = 0;

$default_add  = nome_arquivo($PHP_SELF);
$img_proxima  = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
$img_anterior = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
$max          = 100;    //$qtde_reg_tela;
$range_qtde   = $qtde_range_tela;

// Atualizando dados
if(isset($Submit) && $Submit=="RESPONDER") {

	$sql = "UPDATE dist_usuarios_stores_qiwi SET
						us_endereco		= '".str_replace("'",'"',$us_endereco)."',
						us_bairro		= '".str_replace("'",'"',$us_bairro)."',
						us_cidade		= '".str_replace("'",'"',$us_cidade)."',
						us_estado		= '".str_replace("'",'"',$us_estado)."',
						us_cep			= '".str_replace("'",'"',$us_cep)."'
			WHERE	us_id	= $us_id";
	//echo $sql."<br>:SQL<br>";
	$rs_stores_qiwi = SQLexecuteQuery($sql);
	if(!$rs_stores_qiwi) {
		$msg .= "Erro ao atualizar informa&ccedil;&otilde;es da loja de cartão. ($sql)<br>";
	}
	else {
		$msg .= "Sucesso ao atualizar informa&ccedil;&otilde;es da loja de cartão ID:($us_id).<br>";
	}
}//end if($Submit=="RESPONDER") 

$sql = "SELECT * FROM dist_usuarios_stores_qiwi";
$result_aux = SQLexecuteQuery($sql);
$total_table = pg_num_rows($result_aux);
//Ordem
$sql .= " order by ".$ncamp;
if($ordem == 1){
    $sql .= " desc ";
    $img_seta = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/seta_down.gif";
} else {
    $sql .= " asc ";
    $img_seta = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/seta_up.gif";
}

$sql .= " limit ".$max;
$sql .= " offset ".$inicial;

$rss = SQLexecuteQuery($sql);
if($max + $inicial > $total_table)
    $reg_ate = $total_table;
else
    $reg_ate = $max + $inicial;
$tot = pg_num_rows($rss);

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
	'us_regiao', 
	'us_coord_lat', 
	'us_coord_lng', 
	'us_google_maps_string', 
	'us_google_maps_status', 
);

?>
<script language="javascript">
	// Edição de Dados 
	function edit_data(id){
		$(document).ready(function(){
			$.ajax({
				type:'POST',
				data:"us_id="+id,
				url:'lista_stores_qiwi_edt.php',
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
		});
	}

	function validaGeo(us_id, us_endereco, us_numero, us_bairro, us_cidade, us_estado, us_pais, us_cep) {
		var us_endereco = us_endereco;
		var us_bairro   = us_bairro;
		var us_cidade   = us_cidade;
		var us_estado	= us_estado;
		var us_cep		= us_cep;
		var us_numero	= us_numero;
		
		var us_id		= us_id;
		
		var endereco	= us_endereco+', '+us_numero+', '+us_bairro+', '+us_cidade+', '+us_estado+', '+us_pais;
	
		
		window.open ("geobusca_store_qiwi.php?endereco="+endereco+'&us_id='+us_id+'&us_cep='+us_cep,"geobusca_store");
	}
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<?php
if(isset($msg))
    echo $msg;
?>
<div class="col-md-12">
    <h4>Lista Stores Qiwi</h4>
    <p>Exibindo resultados <strong><?php echo $inicial + 1 ?></strong> a <strong><?php echo $reg_ate ?></strong> de <strong><?php echo $total_table ?></strong></p>
</div>
<table class="table table-bordered txt-preto bg-branco fontsize-pp">
  <tr>
	<?php
		foreach($a_fields as $key => $val) {
			if($key == 13) {	// "us_google_maps_string"
				echo "<td class='style1'><strong>GMaps</strong></td>\n";
			} elseif($key == 14) {		//"us_google_maps_status"
				echo "<td class='style1'><strong>VMaps</strong></td>\n";
			} else {
				echo "<td class='style1'><strong>".substr($val, 3)."</strong></td>\n";
			}
		}
        
        //td para coluna de edição
        echo "<td>&nbsp;</td>";
	?>    
  </tr>
  
  <?php
	if($tot >0) {
		$i_row = 1; 
		while($valores = pg_fetch_array($rss)) {
        ?> 
          <tr onmouseover="bgColor='#CFDAD7'" onmouseout="bgColor='#D0EFF0'" onClick="bgColor='#CFDAD7'">
			<?php
				foreach($a_fields as $key => $val) {
					if($key == 13) {	// "us_google_maps_string"
						$us_endereco = "".$valores['us_id'].", '".$valores['us_endereco']."', '".$valores['us_numero']."', '".$valores['us_bairro']."', '".$valores['us_cidade']."', '".$valores['us_estado']."', 'Brasil', '".$valores['us_cep']."'";

						echo "<td class='style1' align='center'>";
						echo "<a href=\"javascript:void(0);\" onClick=\"validaGeo(".$us_endereco.");\"> <img src=\"/images/pdv/global-search-icon_peq.jpg\" width=\"28\" height=\"21\" border=\"0\" title=\"Lat/Lng: [".$valores['us_coord_lat']." , ".$valores['us_coord_lng']."]\n".$us_endereco ."\"> </a>";
						echo "</td>\n";
					} elseif($key == 14) {		//"us_google_maps_status"
						$statusMaps = $valores[$val];

						$statusMaps_descr = "";
						switch($statusMaps) {
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
						if($valores['us_coord_lat']==0 && $valores['us_coord_lng']==0) {
							if($statusMaps_descr!="") $statusMaps_descr.= "\n";
							$statusMaps_descr .= "Sem Geolocalização";
						} else {
							$statusMaps_descr .= "\n[".number_format($valores['us_coord_lat'], 2, '.', '.').", ".number_format($valores['us_coord_lng'], 2, '.', '.')."]";
						}
						if(trim($statusMaps)=="") {
							if($valores['us_coord_lat']==0 && $valores['us_coord_lng']==0) {
								$statusMaps = "<font color='red'>Coords=0</font>";
							} else {
								$statusMaps = "<font color='blue'>Com_Coords</font>";
							}
						}

						echo "<td class='style1' title='$statusMaps_descr'>".$statusMaps."</td>\n";
					} else { 
						echo "<td class='style1'>".$valores[$val]."</td>\n";
					}
				}
				echo "<td class='style1'><img src='/images/pencil.png' width='16' height='16' border='0' alt='Editar' title='Editar' onclick='javascript:edit_data(".$valores['us_id'].");' style='cursor:pointer;cursor:hand;'></td>\n";
			?>    
          </tr>    
        <?php  
		}
        paginacao_query($inicial, $total_table, $max, 20, $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel);
	}
  ?>

</table>
<div id='box-edit' name='box-edit' align='center'></div>
<p>&nbsp;</p>
<p>
  <?php require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php"; ?>
</p>