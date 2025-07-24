<?php require_once __DIR__ . '/../../../includes/constantes_url.php'; ?>
<style>
	.galeria {
		display: flex;
		flex-wrap: wrap;
		gap: 1px;
		/* sem espaço entre imagens */
		align-items: stretch;
	}

	.galeria img {
		/* 4 por linha */
		/* preenche mantendo proporção */
		height: 30px;
        width: auto;
		/* altura fixa para manter o alinhamento */
		box-sizing: border-box;
	}
</style>
</div>

<?php if ($_SERVER["SCRIPT_FILENAME"] != "/www/public_html/cadastro-de-ponto-de-venda.php") { ?>
	<div class="container-fluid bg-cinza-claro h-footer">
		<div class="container top20">
			<div class="col-md-9">
				<div class="row">
					<a href="http<?php if ($_SERVER['HTTPS'] == "on") {
						echo "s";
					} ?>://<?php echo $_SERVER['SERVER_NAME'] ?>"
						class="txt-cinza fontsize-p" alt="Portal E-prepag" title="Portal E-prepag" target="_blank">Portal
						E-prepag</a><span class="espacamento-laterais txt-cinza">|</span>
					<a href="/creditos/termos-de-uso.php" alt="Termos de uso - Lojas" title="Termos de uso - Lojas"
						target="_blank" class="txt-cinza fontsize-p">Termos de uso - Lojas</a><span
						class="espacamento-laterais txt-cinza">|</span>
					<a href="http://blog.e-prepag.com/compra-segura/" alt="Políticas de segurança"
						title="Políticas de segurança" target="_blank" class="txt-cinza fontsize-p">Políticas de
						segurança</a><span class="espacamento-laterais txt-cinza">|</span>
					<a href="/game/politica-de-privacidade.php" alt="Política de Privacidade"
						title="Política de Privacidade" target="_blank" class="txt-cinza fontsize-p">Política de
						Privacidade</a><span class="espacamento-laterais txt-cinza">|</span>
					<a href="/game/suporte.php" alt="Suporte" title="Suporte" target="_blank"
						class="txt-cinza fontsize-p">Suporte</a>
				</div>
			</div>
			<div class="col-md-3">
				<!-- Begin DigiCert/ClickID site seal HTML and JavaScript -->
				<div id="DigiCertClickID_l47UPnR6" data-language="en_US">
					<div id="DigiCertClickID_l47UPnR6Seal" class="div-divcert">
						<img src="//seal.digicert.com/seals/cascade/?s=l47UPnR6,13,s,<?= EPREPAG_URL_COM ?>"
							alt="DigiCert Seal" class="img-digicert">
					</div>
				</div>
				<script type="text/javascript">
					var __dcid = __dcid || [];
					__dcid.push(["DigiCertClickID_l47UPnR6", "13", "s", "black", "l47UPnR6"]);
					(function () {
						var cid = document.createElement("script");
						cid.async = true; cid.src = "//seal.digicert.com/seals/cascade/seal.min.js";
						var s = document.getElementsByTagName("script");
						var ls = s[(s.length - 1)];
						ls.parentNode.insertBefore(cid, ls.nextSibling);
					}());
				</script>
				<script async="" src="//seal.digicert.com/seals/cascade/seal.min.js"></script>
				<!-- End DigiCert/ClickID site seal HTML and JavaScript -->
				<script type="text/javascript">
					(function (a, e, c, f, g, h, b, d) { var k = { ak: "1052651518", cl: "HjvTCPXqhnIQ_t_49QM", autoreplace: "3030 9101" }; a[c] = a[c] || function () { (a[c].q = a[c].q || []).push(arguments) }; a[g] || (a[g] = k.ak); b = e.createElement(h); b.async = 1; b.src = "//www.gstatic.com/wcm/loader.js"; d = e.getElementsByTagName(h)[0]; d.parentNode.insertBefore(b, d); a[f] = function (b, d, e) { a[c](2, b, k, d, null, new Date, e) }; a[f]() })(window, document, "_googWcmImpl", "_googWcmGet", "_googWcmAk", "script");
				</script>
			</div>
		</div>
	</div>
<?php } else { ?>
	<div class="container-fluid bg-cinza">
		<div class="container espacamento top50">
			<div class="row">
				<div class="col-md-12 top20">
					<p>Atendimento:</p>
				</div>
			</div>
			<div class="row">
				<div class="col-md-3 col-sm-6 bg-cinza-claro-2 espacamento" style="min-height: 110px;">
					<p class="p-left10"><strong>E-mail</strong></p>
					<p class="p-left10"><a class="txt-branco"
							href="mailto:suporte@e-prepag.com.br">suporte@e-prepag.com.br</a></p>
				</div>
				<div class="col-md-3 col-sm-6 bg-cinza-claro-2 espacamento" style="min-height: 110px;">
					<p><strong>Dúvidas Frequentes:</strong></p>
					<p><a class="txt-branco" target="_blank"
							href="<?php echo PROTOCOL; ?>://e-prepag.zendesk.com/hc/pt-br">Consulte aqui suas dúvidas</a></p>
				</div>
				<div class="col-md-3 col-sm-6 bg-cinza-claro-2 espacamento">
					<a class="txt-branco" href="/game/suporte.php" target="_blank">
						<p class="p-left10"><strong>Chat</strong></p>
					</a>
					<p class="p-left10"><a class="txt-branco" href="/game/suporte.php" target="_blank">Segunda a sexta, das
							09:00 as 13:00 e das 14:00 as 17:00h</a></p>
				</div>
				<!--                    <div style="visibility: hidden;" class="col-md-3 col-sm-6 bg-cinza-claro-2 espacamento">
							<p><strong>Telefone:</strong></p>
							<p>11 3030 9101</p>
						</div>-->
				<div class="col-md-3 col-sm-6 espacamento">
					<p><strong>Siga-nos:</strong></p>
					<p>
						<a href="https://www.facebook.com/eprepagpdv/" target="_blank" title="Facebook"><img
								src="/imagens/facebook_eprepag.png"></a>
						<a href="https://www.instagram.com/eprepagpdv/" target="_blank" title="Instagram"><img
								src="/imagens/instagram_eprepag.png"></a>
						<a href="https://www.youtube.com/user/EPrepagVideos" target="_blank" title="Youtube"><img
								src="/imagens/youtube_eprepag.png"></a>
						<a href="https://www.linkedin.com/company/511036" target="_blank" title="LinkedIN"><img
								src="/imagens/LinkedIN_eprepag.png"></a>
					</p>
				</div>
			</div>
			<div class="row top20 txt-cinza-claro2">
				<div class="col-md-3 col-sm-6">
					<p><a href="http:<?= SOLUCOES_URL ?>/" class="txt-cinza-claro2 decoration-none">Soluções em Pagamentos</a>
					</p>
					<p><a class="txt-cinza-claro2" href="<?= QUEMSOMOS_URL ?>">Quem somos</a></p>
					<p><a href="<?= CARTAO_URL ?>" class="txt-cinza-claro2 decoration-none">Cartão E-Prepag</a></p>
					<p><a href="/canal-de-denuncia.php/" class="txt-cinza-claro2 decoration-none">Canal de Denúncia</a></p>
				</div>
				<div class="col-md-3 col-sm-6">
					<p><a href="/game/termos-de-uso.php" class="txt-cinza-claro2 decoration-none" target="_blank">Termos de
							uso</a></p>
					<p><a href="/creditos/termos-de-uso.php" class="txt-cinza-claro2 decoration-none" target="_blank">Termos
							de uso - PDV</a></p>
					<p><a href="<?= COMPRASEG_URL ?>" class="txt-cinza-claro2 decoration-none">Compra segura</a></p>
					<p><a href="/game/politica-de-privacidade.php" class="txt-cinza-claro2 decoration-none"
							target="_blank">Política de Privacidade</a></p>
				</div>
				<div class="col-md-3 col-sm-6">
					<p><a href="<?= FORMASPAG_URL ?>" class="txt-cinza-claro2 decoration-none" target="_blank">Veja aqui
							prazos e condições</a></p>
					<div class="galeria">
						<img src="/imagens/pag/iconePIX.png" alt="">
						<img src="/imagens/icones_boleto_cinza.jpg" alt="">
						<img src="/imagens/logo_epp_cash.png" alt="">
					</div>
				</div>
				<div class="col-md-3 col-sm-6">
					<p>Certificado de segurança</p>
					<p>
						<!-- Begin DigiCert/ClickID site seal HTML and JavaScript -->
					<div id="DigiCertClickID_l47UPnR6" data-language="en_US">
						<div id="DigiCertClickID_l47UPnR6Seal" class="div-divcert">
							<img src="//seal.digicert.com/seals/cascade/?s=l47UPnR6,13,s,<?= EPREPAG_URL_COM ?>"
								alt="DigiCert Seal" class="img-digicert">
						</div>
					</div>
					<script type="text/javascript">
						var __dcid = __dcid || [];
						__dcid.push(["DigiCertClickID_l47UPnR6", "13", "s", "black", "l47UPnR6"]);
						(function () {
							var cid = document.createElement("script");
							cid.async = true; cid.src = "//seal.digicert.com/seals/cascade/seal.min.js";
							var s = document.getElementsByTagName("script");
							var ls = s[(s.length - 1)];
							ls.parentNode.insertBefore(cid, ls.nextSibling);
						}());
					</script>
					<script async="" src="//seal.digicert.com/seals/cascade/seal.min.js"></script>
					<!-- End DigiCert/ClickID site seal HTML and JavaScript -->
					<script type="text/javascript">
						(function (a, e, c, f, g, h, b, d) { var k = { ak: "1052651518", cl: "HjvTCPXqhnIQ_t_49QM", autoreplace: "3030 9101" }; a[c] = a[c] || function () { (a[c].q = a[c].q || []).push(arguments) }; a[g] || (a[g] = k.ak); b = e.createElement(h); b.async = 1; b.src = "//www.gstatic.com/wcm/loader.js"; d = e.getElementsByTagName(h)[0]; d.parentNode.insertBefore(b, d); a[f] = function (b, d, e) { a[c](2, b, k, d, null, new Date, e) }; a[f]() })(window, document, "_googWcmImpl", "_googWcmGet", "_googWcmAk", "script");
					</script>
					</p>
				</div>
			</div>
			<div class="row top20 txt-cinza-claro2">
				<div class="col-md-2 col-sm-3 fontsize-p">
					<!-- <p><strong>App para Gamer:</strong></p>
							<p>
								<a href="https://play.google.com/store/apps/details?id=app.atimopay" target="_blank" title="App Gamer"><img src="/imagens/badge_googleplay.gif"></a>   
							</p> -->
				</div>
				<div class="col-md-2 col-sm-3 fontsize-p">
					<!-- <p><strong>App Ponto de Venda:</strong></p>
							<p>
								<a href="https://play.google.com/store/apps/details?id=com.app.eprepagapppdv&hl=pt-BR" target="_blank" title="App PDV"><img src="/imagens/badge_googleplay.gif"></a> 
							</p> -->
				</div>
				<div class="col-md-8 col-sm-6 text-right fontsize-p espacamento">
					<p>E-prepag Administradora de Cartões Ltda | 11 30309101 | 19.037.276/0001-72 | suporte@e-prepag.com.br
					</p>
					<p>Rua Deputado Lacerda Franco, 300 - conjuntos 26, 27 e 28, Pinheiros, CEP 05418-000</p>
				</div>
			</div>
		</div>
	</div>
<?php } ?>
<!-- Google Analytics -->
<?php
if ($controller->mktzap()) {
	?>
	<!-- Start of e-prepag Zendesk Widget script -->

	<script>/*<![CDATA[*/window.zEmbed || function (e, t) { var n, o, d, i, s, a = [], r = document.createElement("iframe"); window.zEmbed = function () { a.push(arguments) }, window.zE = window.zE || window.zEmbed, r.src = "javascript:false", r.title = "", r.role = "presentation", (r.frameElement || r).style.cssText = "display: none", d = document.getElementsByTagName("script"), d = d[d.length - 1], d.parentNode.insertBefore(r, d), i = r.contentWindow, s = i.document; try { o = s } catch (e) { n = document.domain, r.src = 'javascript:var d=document.open();d.domain="' + n + '";void(0);', o = s } o.open()._l = function () { var o = this.createElement("script"); n && (this.domain = n), o.id = "js-iframe-async", o.src = e, this.t = +new Date, this.zendeskHost = t, this.zEQueue = a, this.body.appendChild(o) }, o.write('<body onload="document._l();">'), o.close() }("//assets.zendesk.com/embeddable_framework/main.js", "e-prepag.zendesk.com");

		/*]]>*/</script>

	<!-- End of e-prepag Zendesk Widget script -->
<?php
}
?>
<script src="http<?php if ($_SERVER['HTTPS'] == "on") {
	echo "s";
} ?>://www.google-analytics.com/urchin.js"
	type="text/javascript"></script>
<script type="text/javascript">
	_uacct = "UA-1903237-3";
	urchinTracker();

	$(function () {
		$(".banner").click(function () {
			$.get("/ajax/pdv/clickBanner.php", { id: $(this).attr("id") });
		});
	});
</script>
<link href="/js/jqueryui/css/custom-theme/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="/js/jqueryui/js/jquery-ui-1.9.2.custom.min.js"></script>
<script type="text/javascript" src="/js/autocomplete.js"></script>
<!-- /Google Analytics -->
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<script>
	(adsbygoogle = window.adsbygoogle || []).push({
		google_ad_client: "ca-pub-2905585494647123",
		enable_page_level_ads: true
	});
</script>
<script type="text/javascript">
	(function (a, e, c, f, g, h, b, d) { var k = { ak: "1052651518", cl: "HjvTCPXqhnIQ_t_49QM", autoreplace: "3030 9101" }; a[c] = a[c] || function () { (a[c].q = a[c].q || []).push(arguments) }; a[g] || (a[g] = k.ak); b = e.createElement(h); b.async = 1; b.src = "//www.gstatic.com/wcm/loader.js"; d = e.getElementsByTagName(h)[0]; d.parentNode.insertBefore(b, d); a[f] = function (b, d, e) { a[c](2, b, k, d, null, new Date, e) }; a[f]() })(window, document, "_googWcmImpl", "_googWcmGet", "_googWcmAk", "script");
</script>
<?php
if ($controller->usuarios instanceof UsuarioGames) {
	if (!class_exists('BannerDrawShadow')) {
		require_once DIR_CLASS . 'classBannerDrawShadow.php';
	}
	$banner = new BannerDrawShadow($controller->usuarios->getId(), 'L');
	$banner->CapturarProximoBanner();
	echo $banner->MontarBanner();

	//        $questionario = new Questionarios($usuarioId,'L');
//
//        $aux_vetor = $questionario -> CapturarProximoQuestionario();
//
//        if($questionario->getBanner()) {
//                echo $questionario->MontarQuestionario();
//        }
} //end if(is_object($controller))
?>
</body>

</html>