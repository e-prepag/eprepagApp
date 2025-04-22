<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";	

	$b_id = $_REQUEST['b_id'];
//	echo $b_id."<br>";
//echo $PREPAG_DOMINIO." - ".$URL_DIR_IMAGES_BANNER."<br>"; 
	if($b_id) {
		// Monta filtro para Banner
		$filtro = array();
		$filtro['b_id'] = $b_id;
		$usuarioID = 0;
//		$filtro['b_ativo'] = 1;
//		$filtro['b_tipo'] = 3;
//		$filtro['b_data_hoje'] = date("Y-m-d");
		
		// Procura banners
		$rs_banners = null;
		$order = "b_contador";
                $instBanner = new Banner();
		$ret = $instBanner->obter($filtro, $order, $rs_banners);

		//Imprime banners
		if($ret == "") {
				// showBanners($codigo_usuario,			$tipo_usuario,	$rs_banners,	$subdir,		$PREPAG_DOMINIO,		$URL_DIR_IMAGES_BANNER) 
//			Banner::showBanners($usuarioID,	0,				$rs_banners,	$PREPAG_DOMINIO,$URL_DIR_IMAGES_BANNER);
		}
	} else {
		echo "<font color='red'>Erro: Forneça um ID Banner</font><br>";
	}

		if ($rs_banners && pg_num_rows($rs_banners) > 0) {
			$rs_banners_row = pg_fetch_array($rs_banners);
//echo "<pre>".print_r($rs_banners_row, true)."</pre>";
?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="2">BackOffice - Lan Houses</a></li>
        <li><a href="com_banner_detalhe.php?banner_id=<?php echo $b_id;?>" >Voltar</a></li>
        <li class="active">Preview de Banner</li>
    </ol>
</div>
			<p>Mostra banner ID:<?php echo $rs_banners_row["b_id"] ." (conteúdo: ".$rs_banners_row["b_conteudo"].")"; ?></p>
			<hr>
				<table border="0" cellspacing="1" width="100%">
					<tr>
						<td align="center">
							<b><?php echo formatar($rs_banners_row["b_titulo"]); ?></b>
							<br />
							<br />
						</td>
					</tr>
					<tr>
						<td align="center" valign="middle">
							<?php if ($rs_banners_row["b_conteudo"] == 2) { ?>
								Apenas rediciona para "<?php echo "http://" . $rs_banners_row["b_url"] . "" ?>"
							<?php } elseif ($rs_banners_row["b_conteudo"] == 0) { ?>
								<?php echo $rs_banners_row["b_texto_conteudo"]; ?>
							<?php } elseif ($rs_banners_row["b_conteudo"] == 1){ ?>
								<?php if($rs_banners_row["b_img_conteudo"] && $rs_banners_row["b_img_conteudo"] != "" && file_exists($GLOBALS['FIS_DIR_IMAGES_BANNER'] . $rs_banners_row["b_img_conteudo"])) { ?>
									<img src="<?php echo $PREPAG_DOMINIO . $URL_DIR_IMAGES_BANNER . $rs_banners_row["b_img_conteudo"]; ?>" alt="<?php echo $rs_banners_row["b_titulo"]; ?>" border="0" width="500" height="400"/>
								<?php } ?>
							<?php } elseif ($rs_banners_row["b_conteudo"] == 3){ ?>
								<?php echo $rs_banners_row["b_texto_conteudo"]; ?>
								<br />
								<?php if($rs_banners_row["b_img_conteudo"] && $rs_banners_row["b_img_conteudo"] != "" && file_exists($GLOBALS['FIS_DIR_IMAGES_BANNER'] . $rs_banners_row["b_img_conteudo"])) { ?>
									<img src="<?php echo $PREPAG_DOMINIO . $URL_DIR_IMAGES_BANNER . $rs_banners_row["b_img_conteudo"]; ?>" alt="<?php echo $rs_banners_row["b_titulo"]; ?>" border="0" width="500" height="400"/>
								<?php } ?>
							<?php } elseif ($rs_banners_row["b_conteudo"] == 4){ ?>
								<?php if($rs_banners_row["b_img_conteudo"] && $rs_banners_row["b_img_conteudo"] != "" && file_exists($GLOBALS['FIS_DIR_IMAGES_BANNER'] . $rs_banners_row["b_img_conteudo"])) { ?>
									<img src="<?php echo $PREPAG_DOMINIO . $URL_DIR_IMAGES_BANNER . $rs_banners_row["b_img_conteudo"]; ?>" alt="<?php echo $rs_banners_row["b_titulo"]; ?>" border="0" width="500" height="400"/>
								<?php } ?>
								<br />
								<?php echo $rs_banners_row["b_texto_conteudo"]; ?>
							<?php } ?>
						</td>
					</tr>
				</table>
<?php
		} else {
			echo "Sem Banners para mostrar (b_id = $b_id)<br>";
		}
?>

<?php require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php"; ?>
</html>