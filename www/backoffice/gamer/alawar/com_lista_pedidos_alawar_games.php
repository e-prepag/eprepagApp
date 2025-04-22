<?php 
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/gamer/alawar/config.inc.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."class/gamer/classAlawarGames.php";
require_once $raiz_do_projeto."class/gamer/classAlawar.php";
require_once $raiz_do_projeto."class/gamer/classSrvUtils.php";
require_once "/www/includes/bourls.php";
$varsel = "";
$filtro = array();			
$orderBy = "";

if(isset($_GET['filtroNomeJogo'])) { 
	$filtro['pag_name'] = $_GET['filtroNomeJogo'];
	$varsel .= "&filtroNomeJogo=".$_GET['filtroNomeJogo'];
}

if(isset($_GET['filtroEmailUser'])) { 
	$filtro['ug_email'] = $_GET['filtroEmailUser'];
	$varsel .= "&filtroEmailUser=".$_GET['filtroEmailUser'];
}

if(isset($_GET['filtroCertificado'])) { 
	$filtro['pa_certificate_id'] = $_GET['filtroCertificado']; 
	$varsel .= "&filtroCertificado=".$_GET['filtroCertificado'];
}

if(isset($_GET['filtroChaveAtivacao'])) { 
	$filtro['pa_activation_key'] = $_GET['filtroChaveAtivacao']; 
	$varsel .= "&filtroChaveAtivacao=".$_GET['filtroChaveAtivacao'];
}


$errorDate = "";

if(isset($_GET['filtroDataTransacaoIni']) && $_GET['filtroDataTransacaoIni'] != '') { 
	$filtro['pa_data_transacao_ini'] = $_GET['filtroDataTransacaoIni']; 
	$varsel .= "&filtroDataTransacaoIni=".$_GET['filtroDataTransacaoIni'];
	
	$dtTransIni = explode('/', $_GET['filtroDataTransacaoIni']);
	if(!checkdate($dtTransIni[1], $dtTransIni[0], $dtTransIni[2])) { $errorDate .= '<li><span style="color: #FC021F;">Data de Transação Inicial e Inválida! Entre com Outra Data.</span></li>'; }
}

if(isset($_GET['filtroDataTransacaoFim']) && $_GET['filtroDataTransacaoFim'] != '') { 
	$filtro['pa_data_transacao_fim'] = $_GET['filtroDataTransacaoFim']; 
	$varsel .= "&filtroDataTransacaoFim=".$_GET['filtroDataTransacaoFim'];

	$dtTransFim = explode('/', $_GET['filtroDataTransacaoFim']);
	if(!checkdate($dtTransFim[1], $dtTransFim[0], $dtTransFim[2])) { $errorDate .= '<li><span style="color: #FC021F;">Data de Transação Final e Inválida! Entre com Outra Data.</span></li>'; }
}


/* ORDER BY */

/** Coluna E-Mail USUARIO **/

if(isset($_GET['filtroOrderBy']) && $_GET['filtroOrderBy']=='USUARIO') {
	$orderBy .= " ug_email ";

	if(isset($_GET['filtroOrderByTipoUsuario']) && $_GET['filtroOrderByTipoUsuario']=='ASC') {
		$orderBy .= " ASC ";
		$imgOrderTipo_USUARIO = '<img src="/images/seta_up.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoUsuario'] = "DESC";
		$varsel .= "&filtroOrderBy=USUARIO&filtroOrderByTipoUsuario=ASC";
	}
	else {
		$orderBy .= " DESC ";
		$imgOrderTipo_USUARIO = '<img src="/images/seta_down.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoUsuario'] = "ASC";
		$varsel .= "&filtroOrderBy=USUARIO&filtroOrderByTipoUsuario=DESC";
	}
}

/** Coluna ID USUARIO **/

if(isset($_GET['filtroOrderBy']) && $_GET['filtroOrderBy']=='ID_USUARIO') {
	$orderBy .= " pa_ug_id ";

	if(isset($_GET['filtroOrderByTipoIdUsuario']) && $_GET['filtroOrderByTipoIdUsuario']=='ASC') {
		$orderBy .= " ASC ";
		$imgOrderTipo_ID_USUARIO = '<img src="/images/seta_up.gif" style="margin-left: 2px; border:none;" />';		
		$_GET['filtroOrderByTipoIdUsuario'] = "DESC";
		$varsel .= "&filtroOrderBy=ID_USUARIO&filtroOrderByTipoIdUsuario=ASC";
	}
	else {
		$orderBy .= " DESC ";
		$imgOrderTipo_ID_USUARIO = '<img src="/images/seta_down.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoIdUsuario'] = "ASC";
		$varsel .= "&filtroOrderBy=ID_USUARIO&filtroOrderByTipoIdUsuario=DESC";
	}
}


/** Coluna Nome Jogo **/

if(isset($_GET['filtroOrderBy']) && $_GET['filtroOrderBy']=='NOME_JOGO') {
	$orderBy .= " nome_jogo ";

	if(isset($_GET['filtroOrderByTipoNomeJogo']) && $_GET['filtroOrderByTipoNomeJogo']=='ASC') {
		$orderBy .= " ASC ";
		$imgOrderTipo_NOME_JOGO = '<img src="/images/seta_up.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoNomeJogo'] = "DESC";
		$varsel .= "&filtroOrderBy=NOME_JOGO&filtroOrderByTipoNomeJogo=ASC";
	}
	else {
		$orderBy .= " DESC ";
		$imgOrderTipo_NOME_JOGO = '<img src="/images/seta_down.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoNomeJogo'] = "ASC";
		$varsel .= "&filtroOrderBy=NOME_JOGO&filtroOrderByTipoNomeJogo=DESC";
	}
}


/** Coluna Certificado **/

if(isset($_GET['filtroOrderBy']) && $_GET['filtroOrderBy']=='CERTIFICADO') {
	$orderBy .= " pa_certificate_id ";

	if(isset($_GET['filtroOrderByTipoCertificado']) && $_GET['filtroOrderByTipoCertificado']=='ASC') {
		$orderBy .= " ASC ";
		$imgOrderTipo_CERTIFICADO = '<img src="/images/seta_up.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoCertificado'] = "DESC";
		$varsel .= "&filtroOrderBy=CERTIFICADO&filtroOrderByTipoCertificado=ASC";
	}
	else {
		$orderBy .= " DESC ";
		$imgOrderTipo_CERTIFICADO = '<img src="/images/seta_down.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoCertificado'] = "ASC";
		$varsel .= "&filtroOrderBy=CERTIFICADO&filtroOrderByTipoCertificado=DESC";
	}
}


/** Coluna Chave de Ativacao **/

if(isset($_GET['filtroOrderBy']) && $_GET['filtroOrderBy']=='GAME_KEY') {
	$orderBy .= " pa_activation_key ";

	if(isset($_GET['filtroOrderByTipoGameKey']) && $_GET['filtroOrderByTipoGameKey']=='ASC') {
		$orderBy .= " ASC ";
		$imgOrderTipo_GAME_KEY = '<img src="/images/seta_up.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoGameKey'] = "DESC";
		$varsel .= "&filtroOrderBy=GAME_KEY&filtroOrderByTipoGameKey=ASC";
	}
	else {
		$orderBy .= " DESC ";
		$imgOrderTipo_GAME_KEY = '<img src="/images/seta_down.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoGameKey'] = "ASC";
		$varsel .= "&filtroOrderBy=GAME_KEY&filtroOrderByTipoGameKey=DESC";
	}
}


/** Coluna Data Transacao **/

if(isset($_GET['filtroOrderBy']) && $_GET['filtroOrderBy']=='DATA_TRANSACAO') {
	$orderBy .= " pa_data_transacao ";

	if(isset($_GET['filtroOrderByTipoDataTransacao']) && $_GET['filtroOrderByTipoDataTransacao']=='ASC') {
		$orderBy .= " ASC ";
		$imgOrderTipo_DATA_TRANSACAO = '<img src="/images/seta_up.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoDataTransacao'] = "DESC";
		$varsel .= "&filtroOrderBy=DATA_TRANSACAO&filtroOrderByTipoDataTransacao=ASC";
	}
	else {
		$orderBy .= " DESC ";
		$imgOrderTipo_DATA_TRANSACAO = '<img src="/images/seta_down.gif" style="margin-left: 2px; border:none;" />';
		$_GET['filtroOrderByTipoDataTransacao'] = "ASC";
		$varsel .= "&filtroOrderBy=DATA_TRANSACAO&filtroOrderByTipoDataTransacao=DESC";
	}
}


/* Se ORDER BY vazio, usa o default */
if(!isset($_GET['filtroOrderBy'])) {
	$orderBy = "pa_data_transacao DESC";
}

$listAllPurchaseOrders = array();

if (!$errorDate)
	$listAllPurchaseOrders = AlawarAPI::listAllTransactions($filtro,"",0,0);

$totalOrders = count($listAllPurchaseOrders);
$paginaOrders = $_GET['inicial'];
$limitPaginaOrders = 20;

$listPaginatePurchaseOrders = array();

if (!$errorDate)
	$listPaginatePurchaseOrders = AlawarAPI::listAllTransactions($filtro, $orderBy, $limitPaginaOrders, $paginaOrders);
				
$img_proxima  = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
$img_anterior = "http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";		

?>
    <link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
    <script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
    <script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>
    <script language="javascript">
    $(function(){
        var optDate = new Object();
        optDate.interval = 10000;

        setDateInterval('filtroDataTransacaoIni','filtroDataTransacaoFim',optDate);

    });
    </script>
	<script type="text/javascript" language="javascript" src="/js/appFunctions.js"></script>	
	<style type="text/css">
	
		.dataGridAlawar {
			font: 11px Arial, Helvetica, sans-serif;
			color: #333;
			background-color: #fff;
			width: auto;
			border: 1px solid #ccc;
			margin-top: 5px;	
			clear:  both;
			overflow: hidden;
		}
		
		#filterDataGridAlawar {
			font: 11px Arial, Helvetica, sans-serif;
			margin: 0 auto; 
			display: block; 
			border: 1px solid #ccc; 
			width: 100%; 
			height: auto;
			overflow: hidden;
			background: #eeeeee;			
			clear:  both;
		}
				
		.dataGridPaginationInfo {
			float: left;
			display: block;
			width: 350px;
			color: #5E6972;
			font: 11px Arial, Helvetica, sans-serif;
			margin: 10px 5px 5px 5px;	
		}		
		
		#dataGridGames { 
			border-collapse: collapse;
			width: 100%;	
			overflow: hidden;
			margin: 0 auto;
			display: block;
			font: 11px Arial, Helvetica, sans-serif;
		}
		
		#dataGridGames td, #dataGridGames th { 
			font: 10px "Lucida Grande", Verdana, Arial, Helvetica, sans-serif;
			border: 1px solid #cccccc;
			text-align: center; 
			padding: 2px;
		}
		
		#dataGridGames th { background: #91AEBC; color: #FFFFFF; font-weight: bold;} 	
		#dataGridGames th a { background: #91AEBC; color: #FFFFFF; font-weight: bold;}
		#dataGridGames tbody > tr:hover { background: #C9DCE5; }
	
	</style>
    
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>

<table class="table">
  <tr> 
    <td valign="top"> 
        <table>
          <tr> 
            <td>
				<table width="894"  border="0">
					<tr> 
					    <td>						
							<!-- Filtro DataTables -->
							<div id="filterDataGridAlawar" style="float: left; display: block; border:1px solid #ccc; width: 100%;background: #eeeeee;">
								<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="get" name="formFiltro" id="formFiltroPedidos">
									<div class="filterLine" style="margin: 0 auto; display: block; padding: 5px; width: 860px; background: #eeeeee; overflow: hidden; clear: both;">
										<div class="filterField" style="float: left; display: block; padding: 2px; width: 260px;">
											<label style="float: left; display: block; width: 80px; font-weight: bold; margin-top: 6px;">Nome Jogo</label>
											<input type="text" name="filtroNomeJogo" id="filtroNomeJogo" value="<?php echo $_GET['filtroNomeJogo']; ?>" style="float: left; display: block; width: 150px;" />
										</div>				
										<div class="filterField" style="float: left; display: block; padding: 2px; width: 260px;margin-left: 35px;">
											<label style="float: left; display: block; width: 90px; font-weight: bold; margin-top: 6px;">E-Mail Usuário</label>
											<input type="text" name="filtroEmailUser" id="filtroEmailUser" value="<?php echo $_GET['filtroEmailUser']; ?>" style="float: left; display: block; width: 150px;" />
										</div>				
										<div class="filterField" style="float: right; display: block; padding: 2px; width: 250px;">
											<label style="float: left; display: block; width: 90px; font-weight: bold; margin-top: 6px;">Certificado</label>
											<input type="text" name="filtroCertificado" id="filtroCertificado" value="<?php echo $_GET['filtroCertificado']; ?>" style="float: left; display: block; width: 150px;" />
										</div>				
							
										<div class="filterSepartor" style="float: left; display: block; padding: 2px; width: 855px; border-top: 1px solid #ccc; margin-top: 7px; margin-bottom: 4px;"></div>
							
										<div class="filterField" style="float: left; display: block; padding: 2px; width: 290px;">
											<label style="float: left; display: block; width: 120px; font-weight: bold; margin-top: 6px;">Chave de Ativação</label>
											<input type="text" name="filtroChaveAtivacao" id="filtroChaveAtivacao" value="<?php echo $_GET['filtroChaveAtivacao']; ?>" style="float: left; display: block; width: 150px;" />
										</div>				
										<div class="filterField" style="float: left; display: block; padding: 2px; width: 340px;margin-left: 35px;">
											<label style="float: left; display: block; width: 110px; font-weight: bold; margin-top: 6px;">Data Transação</label>
											<input type="text" name="filtroDataTransacaoIni" id="filtroDataTransacaoIni" class="text ui-widget-content ui-corner-all" value="<?php echo $_GET['filtroDataTransacaoIni']; ?>" style="float: left; display: block; width: 80px; color:#6F6F6F; border-color: #B2AEAB;" />										
											<label style="float: left; display: block; width: 25px; font-weight: bold; margin-top: 6px; text-align: center;"> a </label>
											<input type="text" name="filtroDataTransacaoFim" id="filtroDataTransacaoFim" class="text ui-widget-content ui-corner-all" value="<?php echo $_GET['filtroDataTransacaoFim']; ?>" style="float: left; display: block; width: 80px; color:#6F6F6F; border-color: #B2AEAB;" />
										</div>
																
										<div class="filterSepartor" style="float: left; display: block; padding: 2px; width: 855px; border-top:1px solid #ccc; margin-top: 7px; margin-bottom: 4px;"></div>
												
										<div class="filterField" style="float: right; display: block; width: auto; text-align: center;">
											<button type="submit" name="btnBuscar" id="btnBuscar" value="Buscar" class="btn btn-info btn-sm">Buscar</button>
											<button type="submit" onclick="javascript:top.location.href='<?php echo $_SERVER["PHP_SELF"]; ?>';" name="btnLimpar" id="btnLimpar" value="Limpar Campos" class="btn btn-info btn-sm">Limpar</button>
										</div>																																
									</div>		
								</form>
							</div>
							<!-- /Filtro DataTables -->
						</td>
					  </tr>
					  <tr>
						 <td>
							<div class="dataGridPaginationInfo">		
								<?php echo "Total de Pedidos Cadastrados: <strong>".$totalOrders."</strong>"; ?>		
							</div>							 
					  	 </td>
					  </tr>	 
					  <tr>
						 <td>
							<!-- DataTables -->
							<div class="">							
                                <table id="dataGridGames" class="table">	
									<thead>
										<tr>
											<th style="width:180px;"><a href="<?php echo $_SERVER["PHP_SELF"]."?".$_SERVER["QUERY_STRING"]."&filtroOrderBy=USUARIO&filtroOrderByTipoUsuario=".($_GET['filtroOrderByTipoUsuario']=='DESC' ? 'DESC' : 'ASC'); ?>">Usuário <?php echo $imgOrderTipo_USUARIO; ?></a></th>
											<th style="width:60px;"><a href="<?php echo $_SERVER["PHP_SELF"]."?".$_SERVER["QUERY_STRING"]."&filtroOrderBy=ID_USUARIO&filtroOrderByTipoIdUsuario=".($_GET['filtroOrderByTipoIdUsuario']=='DESC' ? 'DESC' : 'ASC'); ?>">ID Usuário <?php echo $imgOrderTipo_ID_USUARIO; ?></a></th>
											<th style="width:200px;"><a href="<?php echo $_SERVER["PHP_SELF"]."?".$_SERVER["QUERY_STRING"]."&filtroOrderBy=NOME_JOGO&filtroOrderByTipoNomeJogo=".($_GET['filtroOrderByTipoNomeJogo']=='DESC' ? 'DESC' : 'ASC'); ?>">Jogo <?php echo $imgOrderTipo_NOME_JOGO; ?></a></th>
											<th style="width:120px;"><a href="<?php echo $_SERVER["PHP_SELF"]."?".$_SERVER["QUERY_STRING"]."&filtroOrderBy=CERTIFICADO&filtroOrderByTipoCertificado=".($_GET['filtroOrderByTipoCertificado']=='DESC' ? 'DESC' : 'ASC'); ?>">Certificado <?php echo $imgOrderTipo_CERTIFICADO; ?></a></th>
											<th style="width:150px;"><a href="<?php echo $_SERVER["PHP_SELF"]."?".$_SERVER["QUERY_STRING"]."&filtroOrderBy=GAME_KEY&filtroOrderByTipoGameKey=".($_GET['filtroOrderByTipoGameKey']=='DESC' ? 'DESC' : 'ASC'); ?>">Chave Ativação <?php echo $imgOrderTipo_GAME_KEY; ?></a></th>
											<th style="width:80px;"><nobr><a href="<?php echo $_SERVER["PHP_SELF"]."?".$_SERVER["QUERY_STRING"]."&filtroOrderBy=DATA_TRANSACAO&filtroOrderByTipoDataTransacao=".($_GET['filtroOrderByTipoDataTransacao']=='DESC' ? 'DESC' : 'ASC'); ?>">Data Transação <?php echo $imgOrderTipo_DATA_TRANSACAO; ?></a></nobr></th>		
										</tr>			
									</thead>
									<tbody>
									<?php 
									
									foreach ($listPaginatePurchaseOrders as $alPurchaseOrders) {
										
									?>
										<tr>
											<td><?php echo $alPurchaseOrders['ug_email']; ?></td>
											<td><?php echo '<a href="/gamer/usuarios/com_usuario_detalhe.php?usuario_id='.$alPurchaseOrders['pa_ug_id'].'" target="_blank">'.$alPurchaseOrders['pa_ug_id']."</a>"; ?></td>
											<td align="center"><?php echo $alPurchaseOrders['nome_jogo']; ?></td>
											<td align="center"><?php echo $alPurchaseOrders['pa_certificate_id']; ?></td>		
											<td align="center"><?php echo $alPurchaseOrders['pa_activation_key']; ?></td>
											<td align="center"><nobr><?php echo $alPurchaseOrders['pa_data_transacao_format']; ?></nobr></td>
										</tr>
									<?php 
									
									}
									
									if (!$listPaginatePurchaseOrders) {
											
									?>						
										<tr>
											<td colspan="6">
												<?php 
													if($errorDate) {
														echo "<ul style=\"margin-top: 5px;\">".$errorDate."</ul>";
													}	
													else { 
														echo "Nenhuma Oferta Encontrada.";	
													} 
												?>												
											</td>	
										</tr>												
									<?php 							
						
									}				
									?>							
									</tbody>
								</table>
							</div>
							<!-- /DataTables -->
						 </td>				  
					  </tr>
					  <?php 															

						// functions.php no PATH -> /E-Prepag/www/web/incs/functions.php 
						paginacao_query($paginaOrders, $totalOrders, $limitPaginaOrders, 6, $img_anterior, $img_proxima, nome_arquivo($PHP_SELF), 1, 10, "", $varsel);
					  							
					  ?>							  
				</table>
			</td>
          </tr>
		</table>
    </td>
  </tr>
</table>
<?php require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php"; ?>		
</body>
</html>
