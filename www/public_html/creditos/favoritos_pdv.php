<?php require_once __DIR__ . '/../../includes/constantes_url.php'; ?>
<?php	
	header("Content-Type: text/html; charset=ISO-8859-1",true);
	
	require_once "../../includes/constantes.php";
	
	require_once "../../db/connect.php";
	require_once "../../db/ConnectionPDO.php";

	require_once RAIZ_DO_PROJETO."class/pdv/controller/IndexController.class.php";

	$controller = new IndexController;
	require_once "includes/header.php";
	
?>

<div class="container txt-azul-claro bg-branco">
    <div class="row">
        <div class="col-md-10">
            <div class="espacamento">
                <strong>FAVORITOS</strong>
            </div>
        </div>
        <div class="col-md-2 hidden-sm hidden-xs p-top10">
<?php 		
			// Pega e exibe banner dos aplicativos
			$banner = $controller->getBanner();
            if($banner){
                foreach($banner as $b):
?>
					<div class="row pull-right">
						<a href="<?php echo $b->link; ?>" class="banner" id="<?php echo $b->id; ?>" target="_blank"><img src="<?php echo $controller->objBanner->urlLink.$b->imagem; ?>" width="186" class="p-3" title="<?php echo $b->titulo; ?>"></a>
					</div>
<?php 
                endforeach;
            }
?>
        </div>
    </div>
	<div class="row top20">
        <div class="col-md-12">
            <?php
				
				// Filtra os produtos vendidos pelo usuário / PDV
				$id_usuario = $controller->usuarios->getId();
						
				$conexao_pdo = ConnectionPDO::getConnection();
				$conexao_bd_pdo = $conexao_pdo->getLink();
						
				try{
					
					$query_favoritos = "SELECT COUNT(vgm_ogp_id) as quantidade,vgm_ogp_id, ogp_nome as nome 
										FROM tb_dist_venda_games 
										INNER JOIN tb_dist_venda_games_modelo ON vg_id = vgm_vg_id
										INNER JOIN tb_dist_operadora_games_produto ON vgm_ogp_id = ogp_id
										WHERE vg_ug_id = :id_usuario 
										AND vg_deposito_em_saldo = :sem_deposito 
										AND vg_ultimo_status = :estado_da_venda
										AND ogp_ativo = :produto_ativo
										GROUP BY vgm_ogp_id, ogp_nome
										ORDER BY quantidade DESC;";
							
					$stmt = $conexao_bd_pdo->prepare($query_favoritos);
					$stmt->bindValue(":id_usuario", $id_usuario, PDO::PARAM_INT);
					$stmt->bindValue(":sem_deposito", 0, PDO::PARAM_INT);
					$stmt->bindValue(":estado_da_venda", 5, PDO::PARAM_INT);
					$stmt->bindValue(":produto_ativo", 1, PDO::PARAM_INT);
					$stmt->execute();
							
					$resultado_listagem = $stmt->fetchAll(PDO::FETCH_ASSOC);
					
					$qtd_favoritos = count($resultado_listagem);
					
					$lista_favoritos = [];
							
					for($i = 0; $i < $qtd_favoritos; $i++) {
						$lista_favoritos[$resultado_listagem[$i]["vgm_ogp_id"]] = $resultado_listagem[$i]["nome"];
					}
							
				}catch(PDOException $erro) {
					
					$dataHoraAtual = new dateTime();
					$dataHoraFormatada = $dataHoraAtual->format('Y-m-d H:i:s');
					
					$compila_erro = "# {$dataHoraFormatada} Erro em listar produtos favoritos {$erro->getMessage()}";
					
					$arquivo_de_log = "/www/log/log-favoritos.txt";

					$abre_arquivo = fopen($arquivo_de_log, 'a');
					fwrite($abre_arquivo, $compila_erro);											
					fclose($abre_arquivo);
				}
				if(!empty($lista_favoritos)) {
					foreach ($lista_favoritos as $id => $nome):
						$caminho_imagem_jpg = "" . EPREPAG_URL_HTTPS . "/imagens/pdv/produtos/p_".$id.".jpg";
						$caminho_imagem_png = "" . EPREPAG_URL_HTTPS . "/imagens/pdv/produtos/p_".$id.".png";
						
						$headers_jpg = get_headers($caminho_imagem_jpg);
						$headers_png = get_headers($caminho_imagem_png);

						// Se a IMG em JPG for encontrada, insere o caminho dela na SRC. Se não, insere o PNG
						$src = ($headers_jpg && strpos($headers_jpg[0], '200') !== false) ? $caminho_imagem_jpg : $caminho_imagem_png;
					
			?>
				<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3 txt-azul-claro text-center top20 c-pointer" onclick="postProduct(<?php echo $id; ?>)">
					<div class="thumbnail">
						<div class="box-image" style="height: 250px;">
							<img alt="<?php echo $nome; ?>" border="0" class="img-produto" style="margin-top: 0px" src="<?php echo $src; ?>">
						</div>
						<div class="caption align-center thumbail-body" style="margin-top: 30px; padding: 15px 0">      
							<h4 class="color-blue">
								<strong><?php echo $nome; ?></strong>
							</h4>
							<input type="hidden" id="prod" name="prod">
						</div>
					</div>
				</div>
			<?php 
				endforeach; 
				} else {
					echo "<div class='text-center bottom20'>
							<h3>Você ainda não tem produtos favoritos.</h3>
							<br>
							<h4>Acesse o catálogo para ver os itens disponíveis:</h4>
							<br>
							<a href='/creditos/produtos.php' alt='Listar Games' title='Games' type='button' class='btn btn-lg btn-large btn-success'><strong>Ver todos os games</strong></a>
						</div>";
				}
			?>
        </div>
    </div>
</div>
		

<link href="/js/jqueryui/css/custom-theme/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/jqueryui/js/jquery-ui-1.9.2.custom.min.js"></script>
<script>
	function postProduct(id){
        $("#prod").val(id);
        $("#detalhe").submit();
    }
</script>
<?php
	require_once RAIZ_DO_PROJETO . "public_html/creditos/includes/footer.php";
?>
	
