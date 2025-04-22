<?php 
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
require_once $raiz_do_projeto."includes/pdv/point_in_polygon.php"; ?>
<script language="javascript">
	function validaGeo(ug_tipo_end, ug_endereco, ug_bairro, ug_cidade, ug_id, ug_pais, ug_cep, ug_estado, ug_numero) {
		var ug_tipo_end = ug_tipo_end;
		var ug_endereco = ug_endereco;
		var ug_bairro   = ug_bairro;
		var ug_cidade   = ug_cidade;
		var ug_estado	= ug_estado;
		var ug_cep		= ug_cep;
		var ug_numero	= ug_numero;
		ug_cep			= ug_cep.replace("-", "");
		
		var ug_id		= eval(ug_id);
		//var endereco	= ug_endereco+', '+ug_cidade+', '+ug_bairro;
		
		if(ug_numero != '') {
			if(ug_tipo_end == '') {
				var endereco	= ug_endereco+', '+ug_numero+', '+ug_cidade+', '+ug_estado;
			} else {
				var endereco	= ug_tipo_end+', '+ug_endereco+', '+ug_numero+', '+ug_cidade+', '+ug_estado;
			}
		} else {
			if(ug_tipo_end == '') {
				var endereco	= ug_endereco+', '+ug_cidade+', '+ug_estado;
			} else {
				var endereco	= ug_tipo_end+', '+ug_endereco+', '+ug_cidade+', '+ug_estado;
			}		
		}
		
		window.open ("geobusca.php?endereco="+endereco+'&ug_id='+ug_id+'&ug_cep='+ug_cep,"geobusca");
	}
</script>
<?php
	$sql = "SELECT * FROM dist_usuarios_games WHERE ug_coord_lat != 0 AND ug_coord_lng != 0";
//echo $sql."<br>";
	$rss = SQLexecuteQuery($sql);
	$tot = pg_num_rows($rss);
	
	if($tot > 0) {
		$conta = 0;
		while($vlr = pg_fetch_array($rss)) {
			$ug_coord_lat = $vlr['ug_coord_lat'];
			$ug_coord_lng = $vlr['ug_coord_lng'];
			$ug_id		  = $vlr['ug_id'];	
			$ug_login	  = $vlr['ug_login'];	

			$ug_tipo_end = $vlr['ug_tipo_end'];
			$ug_endereco = $vlr['ug_endereco'];
			$ug_bairro   = $vlr['ug_bairro'];
			$ug_cidade   = $vlr['ug_cidade'];
			$ug_estado	 = $vlr['ug_estado'];
			$ug_cep		 = $vlr['ug_cep'];
			$ug_numero	 = $vlr['ug_numero'];
			$ug_cep		 = str_replace('-','',$ug_cep);
			
			$p_in = new Point($ug_coord_lat,$ug_coord_lng); 
			
			//echo "Point ".$p_in->print_coords()." is ";

			if($ug_numero != '') {
				if($ug_tipo_end == '') {
					$endereco	= $ug_endereco.', '.$ug_numero.', '.$ug_cidade.', '.$ug_estado;
				} else {
					$endereco	= $ug_tipo_end.', '.$ug_endereco.', '.$ug_numero.', '.$ug_cidade.', '.$ug_estado;
				}
			} else {
				if($ug_tipo_end == '') {
					$endereco	= $ug_endereco.', '.$ug_cidade.', '.$ug_estado;
				} else {
					$endereco	= $ug_tipo_end.', '.$ug_endereco.', '.$ug_cidade.', '.$ug_estado;
				}		
			}

			if (pointInside($p_in,$polygon)) {
//				echo "$ug_login ($ug_id) INSIDE<br>";
			} else {
//				echo "OUTSIDE<br>";

//				$sqlupd = "UPDATE dist_usuarios_games SET ug_google_maps_status = '2' WHERE ug_id = $ug_id";
//				$rssupd = SQLexecuteQuery($sqlupd);
				$conta++;
				echo $ug_id.' - '.$ug_login.'&nbsp;&nbsp;';
				?>
				<a href="javascript:void(0);" onclick="validaGeo('<?php echo $ug_tipo_end; ?>','<?php echo $ug_endereco; ?>','<?php echo $ug_bairro; ?>','<?php echo $ug_cidade; ?>',<?php echo $ug_id; ?>,'Brasil','<?php echo $ug_cep; ?>','<?php echo $ug_estado; ?>','<?php  echo $ug_numero ?>');"><img src="images/global-search-icon_peq.jpg" width="28" height="21" border="0" alt="Clique aqui para validar no mapa" title="Clique aqui para validar (<?php echo $ug_login; ?>), no mapa"></a><br>
                <?php
			}
		}
		$retorno = 'Existem, '.$conta.', PDV(s) com a localização fora dos limites, favor utilizar o filtro Pesquisa de Usuários/Google Maps: Fora do Mapa, para localiza-los e providenciar a regularização dos dados.';
	} else {
		$retorno = 'Sem dados para serem validados.';
	}
?>
<div class="col-md-12 top20 txt-preto">
    
                <?php echo $retorno;?>
 
</div>
<?php require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php"; ?>
</body>
</html>
