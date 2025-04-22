<?php
/* Redirect browser */

// include do arquivo contendo IPs DEV
require_once "../../../includes/constantes.php";
require_once DIR_INCS . 'configIP.php';

header("Location: https://".$_SERVER["SERVER_NAME"]."/creditos/");

/* Make sure that code below does not get executed when we redirect. */
exit;
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<?php
require_once DIR_INCS . "inc_register_globals.php";	

include DIR_INCS . "meta.php";
include DIR_INCS . "pdv/captura_inc.php"; 
?>

<title>E-Prepag - Créditos para games online<?php echo ((isset($pagina_titulo))?" - ".$pagina_titulo:""); ?></title>
<link href="/css/styles.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/js/scripts.js"></script>

	<link href="/css/css.css" rel="stylesheet" type="text/css"/>
	<style type="text/css">
		<!--
		.texto_vermelho {
			font-family: Arial, Helvetica, sans-serif;
			font-size: 11px;
			color: #FF0000;
		}
		.espaco_tr {
			font-size: 1px;
		}
		.novo_menu_l {
			font-family: Arial, Helvetica, sans-serif;
			font-size: 13px;
			font-weight:bold;
			font-style:italic;
			color:#FF8907;
		}
		.novo_menu_c {
			font-family: Arial, Helvetica, sans-serif;
			font-size: 13px;
			font-weight:bold;
			font-style:italic;
			color:#838383;
		}
		.novo_menu_az {
			font-family: Arial, Helvetica, sans-serif;
			font-size: 13px;
			font-weight:bold;
			font-style:italic;
			color:#2977AE;
		}
		.novo_menu_am {
			font-family: Arial, Helvetica, sans-serif;
			font-size: 13px;
			font-weight:bold;
			font-style:italic;
			color:#A58620;
		}
		.novo_menu_r {
			font-family: Arial, Helvetica, sans-serif;
			font-size: 13px;
			font-weight:bold;
			font-style:italic;
			color:#593479;
		}
		.novo_menu_s {
			font-family: Arial, Helvetica, sans-serif;
			font-size: 13px;
			font-weight:bold;
			font-style:italic;
			color:#82A041;
		}
		.novo_menu_v {
			font-family: Arial, Helvetica, sans-serif;
			font-size: 13px;
			font-weight:bold;
			font-style:italic;
			color:#DA0101;
		}
		.novo_servicos {
			font-family: Arial, Helvetica, sans-serif;
			font-size: 20px;
			font-weight:bold;
			color:#698C18;
		}
		.novo_servicos_texto {
			font-family: Arial, Helvetica, sans-serif;
			font-size: 13px;
			font-style:italic;
			color:#A2A2A2;
		}
		.materiais_promocionais {
			font-family: Arial, Helvetica, sans-serif;
			font-size: 20px;
			font-weight:bold;
			color:#1765A3;
		}
		.materiais_promocionais_texto {
			font-family: Arial, Helvetica, sans-serif;
			font-size: 14px;
			font-style:italic;
                        vertical-align:middle;
			color:#434343;
		}
		.materiais_promocionais_linha {
			background-color:#A2A2A2;
		}
		.box_servico {
			-moz-border-radius:4px;
			-webkit-border-radius:4px;
			-border-radius:4px;
			background:#f4f4f4;
			border:1px solid #c6c6c6;
			padding:18px;
		}
		.novo_servicos_title {
			font-family: Arial, Helvetica, sans-serif;
			font-size: 14px;
			font-weight:bold;
			color:#797979;
		}
		.novo_servicos_produto {
			font-family: Arial, Helvetica, sans-serif;
			font-size: 14px;
			font-weight:bold;
			color:#698C18;
		}
		.novo_servicos_veja {
			font-family: Arial, Helvetica, sans-serif;
			font-size: 13px;
			font-weight:bold;
			color:#548BB2;
			text-decoration: none;
		}
		.novo_servicos_comissao {
			font-family: Arial, Helvetica, sans-serif;
			font-size: 14px;
			font-weight:bold;
			color:#095C8E;
		}
		-->
	</style>
<?php
// ### Define banner superior
$varRoot = "/eprepag";

$sTiposup = " AND ((tiposup=0) OR (tiposup=1)) ";	// Banner dos tipos "Home" ou "Todos";
$sPath = "/eprepag/";	

include $raiz_do_projeto . "/www/web/prepag2/incs/inc_bannersuperior.php"; 

?>
</head>
<script language="javascript">
addLoadEvent(carregaBanner);
</script>

<body>  
<div id="main">
<center>

  <table width="779" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
      <td height="98" colspan="3" align="center" valign="bottom">
	<?php // ### Começa baner superior 	?>
	  	<span id="spnBannerSuperior"></span>
	<?php // ### Termina baner superior ?>
	</td>
    </tr>
    <tr>
      <td height="98" colspan="3" align="center" valign="bottom">
		<!-- inicio :: topo //-->
		<?php include $_SERVER['DOCUMENT_ROOT']. "/eprepag/incs/topoN.php" ?>
		<!-- fim :: topo //-->
	</td>
    </tr>
   </table>
<?php  //  Termina bloco para novo banner ?>

	<!-- inicio :: centro //-->
 	<div id="conteudo">

<?php include $_SERVER['DOCUMENT_ROOT']."/incs/configuracao.inc" ?>

<table width="90%" border="0" cellspacing="0" cellpadding="0" align="center" bgcolor="FFFFFF">
<tr>
	<td>
		<table width="100%" border="0" cellspacing="02" cellpadding="02" bgcolor="F0F0F0">
    	<tr>
      		<td><?php if(isValidaSessao()){ ?>
				<?php $usuarioGames = unserialize($_SESSION['dist_usuarioGames_ser']); 

				   $sTituloRiscoClassif = "";
//	Retirado em 2012-01-18 a pedido de Anna
//				   if($usuarioGames->getRiscoClassif()==2) $sTituloRiscoClassif = " <font color='#FF0000'>(pré)</font>";
				   $usuarioGamesOperador = null;
				   $sNomeOperador = "";
				   if(isSessionOperador()) {
						$usuarioGamesOperador = unserialize($_SESSION['dist_usuarioGamesOperador_ser']); 
						$sNomeOperador = $usuarioGamesOperador->getNome();
				   }

					$colTitulo = "FFCC00";
					$sTituloOperador = "";
					if(isSessionOperador()) {
						$colTitulo = "FF0000";
						$sTituloOperador = " (funcionário)";
					}
				?>
				<table width="100%" border="0" cellspacing="0" cellpadding="0" height="55">
				  <tr>
				    <td class="texto" align="center"><font face="Arial, Helvetia" color="<?php echo $colTitulo?>" size="5"><b><i>LanHouses<?php echo $sTituloRiscoClassif?><?php echo $sTituloOperador?></i></b></font></td>
				    <td width="420" align="right" bgcolor="#E0E0E0"><table width="100%" border="0" cellspacing="0" cellpadding="0">
				        <tr height="25">
				          <td class="texto" align="center">&nbsp;&nbsp;<?php echo BomDia()?>, <b><?php echo ($usuarioGames->getTipoCadastro()=='PF'?$usuarioGames->getNome():$usuarioGames->getNomeFantasia())?> 
						  <?php echo ((isSessionOperador())?"<font color='".$colTitulo."'>(".$sNomeOperador.")</font>":"")?></b> <?php //  echo "(".$usuarioGames->getLogin().")"?>
						  <?php if(isSessionOperador()) echo "<br><font color='".$colTitulo."'>".((isSessionOperadorTipo1())?$GLOBALS['USUARIO_GAMES_OPERADOR_TIPOS_DESCRICAO'][$GLOBALS['USUARIO_GAMES_OPERADOR_TIPOS'][FUNCIONARIO_1]]:((isSessionOperadorTipo2() )?$GLOBALS['USUARIO_GAMES_OPERADOR_TIPOS_DESCRICAO'][$GLOBALS['USUARIO_GAMES_OPERADOR_TIPOS'][FUNCIONARIO_2]]:"Sem tipo"))."</font>";  
							if($usuarioGames->bIsLanPre()){
								//echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Saldo: <b>R$".number_format($usuarioGames->getPerfilSaldo(), 2, ',', '.')."</b>&nbsp;"; 
							}
							?>
							<?php
								$usuarioId = $usuarioGames->getId();
								$usuarioLogin = $usuarioGames->getLogin();
				
								// variável abaixo necessária para verificação se é obrigatório a alteração de senha no próximo login
								$ug_alterar_senha = $usuarioGames->getAlteraSenha();
								
								// echo $usuarioId;
							?>
							</td>
				        </tr>
				    </table></td>
				  </tr>
				  <tr>
				    <td class="texto" align="center"><?php echo Data_Atual_Por_Extenso()?>&nbsp;<?php echo date("H:i")?><br>
					Acessos: <?php echo number_format($usuarioGames->getQtdeAcessos(),0,'','.')?>&nbsp;&nbsp;&nbsp;&nbsp;Último: <?php echo $usuarioGames->getDataUltimoAcesso()?>
				    </td>
				    <td align="center" bgcolor="#E0E0E0">&nbsp;&nbsp;&nbsp;<nobr><?php 

						$questionario = new Questionarios($usuarioId,'L');

						$aux_vetor = $questionario -> CapturarProximoQuestionario();


						$str_idsessaonex = "";
						if(!$questionario->getBloqueiaMenu()&&($ug_alterar_senha==0)) {
							$idsessaonex = $GLOBALS['_GET']["idsessaonex"];
							if($idsessaonex && (strlen($idsessaonex)<256)) {
								$str_idsessaonex = "idsessaonex=".$idsessaonex;
							}
					?><font class="texto"><?php if (isSessionLanHouse() || isSessionOperadorTipo1()) { ?><a href="/prepag2/dist_commerce/comprar.php" class="link_azul">Comprar</a></font><?php } else { ?>&nbsp;-&nbsp;<?php } ?> | <font class="texto"><?php if (isSessionLanHouse() || isSessionOperadorTipo1()) { ?><a href="/prepag2/dist_commerce/carrinho.php" class="link_azul">Meu Carrinho</a></font><?php } else { ?>&nbsp;-&nbsp;<?php } ?> | <font class="texto"><a href="/prepag2/dist_commerce/conta/index.php<?php if($str_idsessaonex) echo "?".$str_idsessaonex;?>" class="link_azul">Minha LanHouse</a></font> | <?php 
							// insere link "Adicionar Saldo"
							if($usuarioGames->getRiscoClassif()==2 && (!isSessionOperadorTipo2())) {
								$url_pagto_boleto = ($usuarioGames->b_IsLogin_pagamento())?"BoletoExpressLH_online.php":"BoletoExpressLH.php"; 

						?><font class="texto"><nobr><a href="/prepag2/dist_commerce/<?php echo $url_pagto_boleto ?>" class="link_azul">Adicionar Saldo</a></nobr></font> | <?php
							}
						}
					?> <font class="texto"><nobr><a href="/prepag2/dist_commerce/logout.php" class="link_azul">Encerrar Sessão</a></nobr></font> </nobr><br>
				    </td>
				  </tr>
				</table>
			<?php 
                            } else { 
                            $pag = $_REQUEST['pag']; 
                            if(!$pag || $pag == "") $pag = $_SERVER['SCRIPT_NAME'] . "?" . $_SERVER['QUERY_STRING']; 
                        ?> 

				<table width="100%" border="0" cellspacing="0" cellpadding="0" height="55">
				<form name="formLogin" action="https://www.e-prepag.com.br/prepag2/dist_commerce/loginEf.php" method="post">
				<input type="hidden" name="pag" value="<?php echo $pag?>">
				<!--input type="hidden" name="pag" value="<?php echo $_SERVER['SCRIPT_NAME'] . "?" . $_SERVER['QUERY_STRING']?>"-->
				  <tr>
				    <td class="texto" align="center">&nbsp;<font face="Arial, Helvetia" color="FFCC00" size="5"><b><i>LanHouses</i></b></font></td>
				    <td width="420" align="right" bgcolor="#E0E0E0"><table width="100%" border="0" cellspacing="0" cellpadding="0">
				        <tr height="25">
				          <td class="texto"><nobr>&nbsp;&nbsp;<?php /*Login:<input name="login" value="<?php echo htmlspecialchars($login, ENT_QUOTES)?>" type="text" size="25" class="field_dados"> */ ?></nobr></td>
				          <td class="texto"><nobr>&nbsp;<?php /*Senha:<input name="senha" type="password" size="8" class="field_dados">&nbsp;<input name="btSubmit" type="submit" value="OK" class="botao_simples">*/ ?></nobr></td>
				        </tr>
				    </table></td>
				  </tr>
				</form>
				  <tr>
				    <td class="texto" align="center"><?php echo Data_Atual_Por_Extenso(); ?>&nbsp;<?php echo date("H:i"); ?></td>
				    <td align="center" bgcolor="#E0E0E0">&nbsp;&nbsp;&nbsp;
				    	<font class="texto"><a href="/prepag2/dist_commerce/carrinho.php" class="link_azul">Meu Carrinho</a></font> | 
				    	<font class="texto"><a href="/prepag2/dist_commerce/conta/esqueci_senha.php" class="link_azul">Esqueci minha senha</a></font> | 
				    	<font class="texto"><a href="/cadastro-de-ponto-de-venda.php" class="link_azul">Ainda não sou cadastrado</a></font>
				    </td>
				  </tr>
				</table>
			<?php 
                            } 
                        ?>
	
			</td>
		</tr>
		</table>
	</td>
</tr>
<tr><td height="15" align="right">
<?php // Monitor - Start ?>
	<table border="0" cellspacing="0" align="center" width="100%">
	   	<tr valign="middle" align="right">
			<td class="texto" align="left">
	<?php
		if(!is_object($usuarioGames)) {
			$usuarioGames = unserialize($_SESSION['dist_usuarioGames_ser']); 
		}
		if(is_object($usuarioGames)) {
			if($usuarioGames->b_IsLogin_lista_extrato()) {
				if($_SERVER['SCRIPT_NAME']=="/prepag2/dist_commerce/conta/lista_extratos_rapido.php") {
					//Mostra o icone de quadro de ajuda sobre o extrato.
					if (isset($_SESSION['dist_usuarioGames_ser'])){
						echo "<input type='image' id='bntAjudaExtrato' class='basic' style='width:25px; height:25px; cursor: hand' title='Ajuda sobre Informações de Extrato.' src='http://www.e-prepag.com.br/prepag2/dist_commerce/images/balanco_help.png'>";
					}
				}
			}
		}
	?>

			</td>
			<td>&nbsp;</td>
			<td class="texto" align="right">
<?php 
	if(!(strpos($_SESSION['dist_usuarioGames_ser'],"WAGNER")===false) ||
		  !(strpos($_SESSION['dist_usuarioGames_ser'],"GLAUCIAPJ")===false) ||
		  !(strpos($_SESSION['dist_usuarioGames_ser'],"ODECIO")===false) ||
		  !(strpos($_SESSION['dist_usuarioGames_ser'],"FABIO###")===false)
		)  {
?>
		<nobr><?php echo getLastOrders(); ?></nobr>
<?php	
	}
	?>
			</td>
		</tr>
	</table>
	
<?php // Monitor - End ?>
	</td>
</tr>
<tr>
	<td height="100%" width="100%" valign="top">
		<table border="0" cellspacing="0" align="center" width="100%" bgcolor="#1666a5">
                        <tr valign="middle">
                                <td width="50">&nbsp;</td><td align="left" class="texto"><font color="FFFFFF"><b><?php echo $pagina_titulo ?></b></font></td>
                        </tr>
		</table>
