<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

$https = 'http' . (($_SERVER['HTTPS']=='on') ? 's' : '');
require_once "../../../includes/constantes.php";
require_once DIR_CLASS . 'gamer/controller/HeaderController.class.php';
$controller = new HeaderController;
$controller->setHeader();

require_once DIR_CLASS . "gamer/classIntegracao.php";

require_once DIR_INCS . "gamer/venda_e_modelos_logica.php"; 

$rs_venda_row = pg_fetch_array($rs_venda);
$pagto_tipo	 = $rs_venda_row['vg_pagto_tipo'];
$ultimo_status	= $rs_venda_row['vg_ultimo_status'];


if($pagto_tipo != $FORMAS_PAGAMENTO['BOLETO_BANCARIO']){
        $strRedirect = "/game/conta/pedidos.php";
                                                    
        //Fechando Conexão
        pg_close($connid);

        redirect($strRedirect);
}

// Obtem o valor total deste pedido
$libera_pagamento = array(
    'Boleto' => true,
);

$pagtoInvalido = false;

pg_result_seek($rs_venda_modelos, 0);
$arr_venda_modelos = pg_fetch_all($rs_venda_modelos);

$produto_idade_minima = "";
foreach($arr_venda_modelos as $modelo){
    if(isset($modelo["vgm_ogp_id"])){
        $sql = "SELECT ogp_idade_minima FROM tb_operadora_games_produto WHERE ogp_id = " . $modelo["vgm_ogp_id"];
        $rs_operadora = SQLexecuteQuery($sql);
        $rs_idade_minima = pg_fetch_all($rs_operadora)[0]["ogp_idade_minima"];
        if($rs_idade_minima > $GLOBALS["IDADE_MINIMA"]){
            $GLOBALS["IDADE_MINIMA"] = $rs_idade_minima;
            $produto_idade_minima = $modelo["vgm_nome_produto"];
        }
    }
}

foreach($arr_venda_modelos as $ind => $rs_venda_modelos_row){
    
    $tipoId['operadora'] = $rs_venda_modelos_row['vgm_opr_codigo'];
    $arrPagtosBloqueados[] = getMeiosPagamentosBloqueados($tipoId, $libera_pagamento);
}

foreach($arrPagtosBloqueados as $ind => $val){
    if(!$arrPagtosBloqueados[$ind]['Boleto']){
        $pagtoInvalido = true;
        break;
    }
}

//Recupera usuario
if(isset($_SESSION['usuarioGames_ser']) && !is_null($_SESSION['usuarioGames_ser'])){
        $usuarioGames = unserialize($_SESSION['usuarioGames_ser']);
        $usuarioId = $usuarioGames->getId();
}


$pagina_titulo = "Comprovante";
?>
<script>
	function fcnJanelaBoleto(){
		<?php 

			if($usuarioGames)
			{
				//Codigo do usuario
				$usuario_id = $usuarioGames->getId();

				$token = date('YmdHis', strtotime("+20 day")) . "," . $venda_id . "," . $usuario_id;
				$objEncryption = new Encryption();
				$token = $objEncryption->encrypt($token);
                                $server_url = "www.e-prepag.com.br";
                                if(checkIP()) {
                                    $server_url = $_SERVER['SERVER_NAME'];
                                }
                                
                                $parametros['prepag_dominio'] = "http".(($_SERVER['HTTPS']=="on")?"s":"")."://" . $server_url;
                                        
				if($usuarioGames->b_Is_Boleto_Bradesco()) {
		?>
		window.open('/boletos/gamer/boleto_bradesco.php?token=<?php echo $token ?>','boleto','');
		<?php
				} elseif($usuarioGames->b_Is_Boleto_Banespa()) {
		?>
		window.open('/SICOB/BoletoWebBanespaCommerce.php?token=<?php echo $token ?>','boleto','');
		<?php
				} elseif($usuarioGames->b_Is_Boleto_Itau()) {
		?>
		window.open('/SICOB/BoletoWebItauCommerce.php?token=<?php echo $token ?>','boleto','');
		<?php
				} else {
		?>
		window.open('/SICOB/BoletoWebCaixaCommerce.php?venda=<?php echo $venda_id?>','boleto','');
		<?php
				}
			} 
		?>
	}
</script>
<div class="container txt-azul-claro bg-branco">
    <div class="row top20">
        <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12">
            <div class="txt-azul-claro top10">
                <span class="glyphicon glyphicon-triangle-right graphycon-big pull-left" aria-hidden="true"></span><strong class="pull-left"><h4 class="top20">boleto</h4></strong>
            </div>
            <div class="hidden-md hidden-lg top20"></div>
        </div>
        <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12">
<?php
if($pagtoInvalido){
?>
    <div class="alert alert-danger top20" id="erro" role="alert">
        <span class="glyphicon t0 glyphicon-exclamation-sign" aria-hidden="true"></span>
        <span class="sr-only">Error:</span>
        Erro: forma de pagamento inválida no momento.
    </div>
<?php
}
else{
require_once DIR_INCS . "gamer/venda_e_modelos_view.php";

//Necessidade de solicitação de CPF para Gamer quando Boleto
cpf_page_gamer();

require_once DIR_INCS."gamer/pagto_compr_usuario_dados.php";

if($ultimo_status == $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO'] || $ultimo_status == $GLOBALS['STATUS_VENDA']['PEDIDO_EFETUADO']) { 
?>
            <div class="col-md-9 texto espacamento bottom20">
                <div class="text-left txt-preto">
                <?php
                if($total_geral < $GLOBALS['RISCO_GAMERS_VALOR_MIN_PARA_TAXA']) { 
                ?>
                <strong>Obs: Boleto Banc&aacute;rio</strong> - Acr&eacute;scimo de R$ <?php 
                echo number_format($GLOBALS['BOLETO_MONEY_ITAU_TAXA_ADICIONAL'], 2, ',', '.') ;
                ?> referente a taxa de servi&ccedil;o banc&aacute;rio
                <?php
                }//end if($total_geral < $RISCO_GAMERS_VALOR_MIN_PARA_TAXA)
                ?>
                </div>
                <div class="top10">
                    <strong><i>Atenção: Após o pagamento aguarde até 2 dias úteis para confirmação bancária.</i></strong>
                </div>
            </div>
            <div class="col-md-3 espacamento">
                <div class="top10 pull-right">
                    <a href="javascript:void(0);" class="btn btn-success" onclick="fcnJanelaBoleto();">Clique aqui para emitir o boleto</a>
                </div>
                <div class="clearfix"></div>
            </div>
<?php
} //end  if($ultimo_status == $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO'])
}
?>
        </div>
    </div>
</div>
</div>
<?php 
require_once DIR_WEB . "game/includes/footer.php";
?>
