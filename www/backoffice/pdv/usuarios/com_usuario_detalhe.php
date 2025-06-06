<?php

//require_once("C:/Sites/E-Prepag/www/web/incs/inc_register_globals.php");	

set_time_limit(3000);

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/main.php";
require_once $raiz_do_projeto . "includes/pdv/main.php";
require_once $raiz_do_projeto . "includes/pdv/corte_constantes.php";
require_once $raiz_do_projeto . "class/util/Util.class.php";
require_once __DIR__ . '../../../../public_html/creditos/includes/funcoes_login.php';
//$varBlDebug = true;

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
function corTextoContraste($hexCor)
{
	$hex = ltrim($hexCor, '#');

	$r = hexdec(substr($hex, 0, 2));
	$g = hexdec(substr($hex, 2, 2));
	$b = hexdec(substr($hex, 4, 2));

	$yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;

	return ($yiq >= 128) ? '#000000' : '#FFFFFF';
}

//var_dump($_SESSION);
$grupos = unserialize($_SESSION["arrIdGrupos"]);

$varsel = "&usuario_id=$usuario_id";

$max = 200; //$qtde_reg_tela;
$default_add = nome_arquivo($PHP_SELF);
$img_anterior = "https://" . $_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'] . "/images/anterior.gif";
$img_proxima = "https://" . $_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'] . "/images/proxima.gif";
if (!isset($range) || !$range)
	$range = 1;
$range_qtde = $qtde_range_tela;

$msg = "";
$msgAcao = "";

if (!$usuario_id)
	$msg = "Código do usuário não fornecido.\n";
elseif (!is_numeric($usuario_id))
	$msg = "Código do usuário inválido.\n";


//Processa Acoes
if ($msg == "") {

	//Alterar Dados do Estabelecimento
	if (isset($acao) && $acao == "a") {

		if (!$v_campo || trim($v_campo) == '') {
			$msgAcao = "Item a ser alterado não especificado.\n";
		}

		if ($msgAcao == "") {
			$cad_usuarioGames = new UsuarioGames($usuario_id);
			//echo "cad_usuarioGames->getLogin(): '".$cad_usuarioGames->getLogin()."'<br>";
//echo "cad_usuarioGames->getAbertura: ".$cad_usuarioGames->getAberturaMes()."/".$cad_usuarioGames->getAberturaAno()."<br>";

			if ($v_campo == 'email')
				$cad_usuarioGames->setEmail($v_valor_new);

			if ($v_campo == 'ativo')
				$cad_usuarioGames->setAtivo($v_valor_new);

			if ($v_campo == 'status_busca')
				$cad_usuarioGames->setStatusBusca($v_valor_new);

			if ($v_campo == 'substatus') {
				$cad_usuarioGames->setSubstatus($v_valor_new);
				$cad_usuarioGames->setDataAprovacao(urldecode($novo_ug_data_aprovacao));
			}//end if($v_campo == 'substatus')

			if ($v_campo == 'riscoclassif') {
				if (array_key_exists(utf8_decode($v_valor_new), $RISCO_CLASSIFICACAO)) {
					$cad_usuarioGames->setRiscoClassif($RISCO_CLASSIFICACAO[utf8_decode($v_valor_new)]);
				}
			}
			if ($v_campo == 'ug_perfil_senha_reimpressao')
				$cad_usuarioGames->setPerfilSenhaReimpressao($v_valor_new);
			if ($v_campo == 'ug_perfil_forma_pagto')
				$cad_usuarioGames->setPerfilFormaPagto($v_valor_new);
			if ($v_campo == 'ug_perfil_limite')
				$cad_usuarioGames->setPerfilLimite($v_valor_new);
			if ($v_campo == 'ug_perfil_limite_referencia')
				$cad_usuarioGames->setPerfilLimiteRef($v_valor_new);
			if ($v_campo == 'ug_perfil_corte_dia_semana')
				$cad_usuarioGames->setPerfilCorteDiaSemana($v_valor_new);
			if ($v_campo == 'ug_tipo_cadastro')
				$cad_usuarioGames->setTipoCadastro($v_valor_new);

			//Pre atualizacao
			if ($msgAcao == "") {
				//inibir erro pois a funcao getUsuarioGamesById nao seta todos os filtros, gerando varios notices na hora de pegar o usuario
				error_reporting(E_ALL ^ E_NOTICE);
				$instUsuarioGames = new UsuarioGames();
				$objUsuarioGames = $instUsuarioGames->getUsuarioGamesById($usuario_id);
				if ($objUsuarioGames == null)
					$msgAcao = "Usuário não encontrado.\n";
			}
			if ($msgAcao == "") {
				if ($v_campo == 'ativo' && $v_valor_new == "1") {

					if (!$objUsuarioGames->getPerfilFormaPagto() || trim($objUsuarioGames->getPerfilFormaPagto()) == "") {
						$msgAcao = "Não é possivel ativar este usuário, Forma de Pagamento ainda não definida.\n";
					}

				}
			}

			if ($msgAcao == "") {
				$instUsuarioGames = new UsuarioGames();
				$msgAcao = $instUsuarioGames->atualizar($cad_usuarioGames);
			}

			//Pos atualizacao
			if ($msgAcao == "") {
				if ($v_campo == 'ativo' && $v_valor_new == "1") {
					//$msgAcao = UsuarioGames::enviaEmailAtivacao($usuario_id);
				}
			}
		}
	}

	//excluir desconto
	if (isset($acao) && $acao == "e") {

		//Validacao
		if (!$des_id || !is_numeric($des_id))
			$msg = "Código do desconto inválido.\n";

		//exclui
		if ($msg == "") {
			$sql = "delete from tb_dist_descontos where des_id = $des_id ";
			SQLexecuteQuery($sql);
		}
	}

}

//Recupera dados do usuario
if ($msg == "") {
	$instUsuarioGames = new UsuarioGames();
	@$objUsuarioGames = $instUsuarioGames->getUsuarioGamesById($usuario_id);




	if ($objUsuarioGames == null)
		$msg = "Nenhum usuário encontrado.\n";
	else {

		//echo "objUsuarioGames->getSubstatus(): ".$objUsuarioGames->getSubstatus()."<br>";

		// Valida dados
		$instUsuarioGames = new UsuarioGames();
		@$msgRetValida = $instUsuarioGames->apenas_validar($objUsuarioGames);

		//RA
		if (is_null($objUsuarioGames->getRACodigo()) || trim($objUsuarioGames->getRACodigo()) == "") {
			$cad_RA = $objUsuarioGames->getRAOutros();
		} else {
			$resatv = SQLexecuteQuery("select ra_codigo, ra_desc from ramo_atividade where ra_codigo = '" . $objUsuarioGames->getRACodigo() . "'");
			if ($resatv)
				$pgatv = pg_fetch_array($resatv);
			$cad_RA = $pgatv['ra_desc'];
		}
	}

}
$msg .= $msgAcao; //.$msgRetValida;


$cor1 = "#F5F5FB";
$cor2 = "#F5F5FB";
$cor3 = "#FFFFFF";

ob_end_flush();
?>
<script language='javascript' src='/js/popcalendar.js'></script>

<script language="javascript">
	function GP_popupAlertMsg(msg) { //v1.0
		document.MM_returnValue = alert(msg);
	}
	function GP_popupConfirmMsg(msg) { //v1.0
		document.MM_returnValue = confirm(msg);
	}
</script>

<script language="javascript">

	function fcnEditarCadastro(cod) {
		form1.action = 'com_usuario_detalhe_salva.php?acao=edt&usuario_id=' + cod;
		form1.submit();
	}

	function fcnAlterar(v_campo, v_valor_old, v_texto_adicional) {

		var msg = '';
		var v_valor_new_aux = '';

		var v_valor_new = prompt('Entre com o novo valor:\n ' + v_texto_adicional, v_valor_old);

		if (v_valor_new == null) return;
		//		msg = 'Alteração cancelada.';

		if (trimAll(v_valor_new) == '')
			msg = 'Valor inválido.';
		else if (trimAll(v_valor_new) == trimAll(v_valor_old))
			msg = 'Novo valor igual ao valor antigo.';

		if (msg != '') {
			alert(msg);
		} else {
			form1.v_campo.value = v_campo;
			form1.v_valor_old.value = v_valor_old;
			form1.v_valor_new.value = v_valor_new;
			form1.action = '?acao=a<?php echo $varsel ?>';
			form1.submit();
		}
	}
	function trimAll(sString) {
		while (sString.substring(0, 1) == ' ')
			sString = sString.substring(1, sString.length);
		while (sString.substring(sString.length - 1, sString.length) == ' ')
			sString = sString.substring(0, sString.length - 1);

		return sString;
	}

</script>
<form name="form1" method="post" action="est_perfil_informa_estab.php">
	<input type="hidden" name="v_campo" value="">
	<input type="hidden" name="v_valor_old" value="">
	<input type="hidden" name="v_valor_new" value="">
</form>
<div class="col-md-12">
	<ol class="breadcrumb top10">
		<li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice -
				<?php echo $currentAba->getDescricao(); ?></a></li>
		<li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
		<li class="active"><a
				href="<?php echo $sistema->item->getLink(); ?>"><?php echo $sistema->item->getDescricao(); ?></a></li>
	</ol>
</div>

<table class="table txt-preto fontsize-p">
	<tr>
		<td>
			<table width="894" border="0" cellpadding="0" cellspacing="2">
				<tr>
					<td colspan="5">
						<table border="0" cellpadding="0" cellspacing="0" dwcopytype="CopyTableCell">
							<tr>
								<td width="894">
									<b>Money Distribuidor - Usuário</b>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			<?php if ($msg != "") { ?>
				<table width="894" border="0" cellpadding="0" cellspacing="2">
					<tr>
						<td align="center" class="texto">
							<font color="#FF0000"><?php echo str_replace("\n", "<br>", $msg) ?></font>
						</td>
					</tr>
				</table>
			<?php } ?>
			<table class="table top20">
				<tr bgcolor="#FFFFFF" class="texto">
					<td colspan="4" bgcolor="#ECE9D8">Perfil
						(<b><?php echo (($objUsuarioGames->getTipoCadastro() == "PF") ? "PF" : (($objUsuarioGames->getTipoCadastro() == "PJ") ? "PJ" : "<font color='#FF0000'>Sem Tipo cadastro definido</font>")) ?></b>)
						</font>
					</td>
				</tr>
				<tr bgcolor="#F5F5FB" class="texto">
					<td width="140"><b>Forma de Pagamento</b></td>
					<td width="307"><?php echo $FORMAS_PAGAMENTO_DESCRICAO[$objUsuarioGames->getPerfilFormaPagto()] ?>
					</td>
					<td width="140"><a class="link_azul" href="#"
							Onclick="if(confirm('Deseja alterar a Senha de Reimpressão deste usuário ?')) fcnAlterar('ug_perfil_senha_reimpressao','<?php echo $objUsuarioGames->getPerfilSenhaReimpressao() ?>','');return false;"><b>Senha
								de Reimpressão</b></a></td>
					<td width="307"><?php echo $objUsuarioGames->getPerfilSenhaReimpressao() ?></td>
				</tr>
				<tr bgcolor="#F5F5FB" class="texto">
					<td><a class="link_azul" href="#"
							Onclick="if(confirm('Deseja alterar o Limite deste usuário ?')) fcnAlterar('ug_perfil_limite','<?php echo number_format($objUsuarioGames->getPerfilLimite(), 2, ',', '.') ?>','(formato x,xx)');return false;"><b>Limite</b></a>
					</td>
					<td><?php echo number_format($objUsuarioGames->getPerfilLimite(), 2, ',', '.') ?></td>
					<td><b>Saldo atual</b></td>
					<td><?php echo number_format($objUsuarioGames->getPerfilSaldo(), 2, ',', '.') ?>
						<?php if ($objUsuarioGames->getCreditoPendente() > 0 && false) { ?>
							<font color="#FF0000">(<b>Crédito Pendente:
									<?php echo number_format($objUsuarioGames->getCreditoPendente(), 2, ',', '.') ?></b>)
							</font><?php } ?>
					</td>
				</tr>
				<tr bgcolor="#F5F5FB" class="texto">
					<td><b>Limite Sugerido</b></td>
					<td><?php echo number_format($objUsuarioGames->getPerfilLimiteSugerido(), 2, ',', '.') ?></td>
					<td><a class="link_azul" href="#"
							Onclick="if(confirm('Deseja alterar o Dia de Corte deste usuário ?')) window.open('com_usuario_detalhe_selecao.php?v_campo=ug_perfil_corte_dia_semana&ug_perfil_corte_dia_semana=<?php echo $objUsuarioGames->getPerfilCorteDiaSemana() ?><?php echo $varsel ?>','selecao', 'status=0,width=500,height=200,top=0,left=0');return false;"><b>Dia
								de Corte</b></a></td>
					<td><?php echo $GLOBALS['CORTE_DIAS_DA_SEMANA_DESCRICAO'][$objUsuarioGames->getPerfilCorteDiaSemana()] ?>
					</td>
				</tr>
				<tr bgcolor="#F5F5FB" class="texto">
					<td><a class="link_azul" href="#"
							Onclick="if(confirm('Deseja alterar o Limite de Referência deste usuário ?')) fcnAlterar('ug_perfil_limite_referencia','<?php echo number_format($objUsuarioGames->getPerfilLimiteRef(), 2, ',', '.') ?>','(formato x,xx)');return false;"><b>Limite
								de Referência</b></a></td>
					<td><?php echo number_format($objUsuarioGames->getPerfilLimiteRef(), 2, ',', '.') ?></td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
				<tr bgcolor="#F5F5FB" class="texto">
					<td><b>Usuário VIP ou Master?</b></td>
					<td><?php
					$bret = $objUsuarioGames->b_IsLogin_pagamento_vip();
					$bret2 = $objUsuarioGames->b_IsLogin_pagamento_master();
					$bret3 = $objUsuarioGames->b_IsLogin_pagamento_black();
					$bret4 = $objUsuarioGames->b_IsLogin_pagamento_gold();
					echo (($bret) ? "<font color='blue'>VIP</font>" : (($bret2) ? "MASTER" : (($bret3) ? "BLACK" : (($bret4) ? "GOLD" : "não"))));
					?></td>
					<td width="140">Possui Restrição de Vendas de Produtos?</td>
					<td width="307">
						<?php echo (($objUsuarioGames->getPossuiRestricaoProdutos() == 1) ? "SIM" : "Não"); ?>
					</td>
				</tr>
				<tr bgcolor="#FFFFFF" class="texto">
					<td colspan="4" bgcolor="#ECE9D8">Dados Administrativos</font>
					</td>
				</tr>
				<tr bgcolor="#F5F5FB" class="texto">
					<td><b>Código</b></td>
					<td id="codigo-pdv"><?php echo $objUsuarioGames->getId() ?></td>
					<td><a class="link_azul" href="#"
							Onclick="if(confirm('Deseja alterar o Status deste usuário ?')) window.open('com_usuario_detalhe_selecao.php?v_campo=ativo&ativo=<?php echo $objUsuarioGames->getAtivo() ?><?php echo $varsel ?>','selecao', 'status=0,width=500,height=200,top=0,left=0');return false;"><b>Status</b></a>
					</td>
					<td><?php echo ($objUsuarioGames->getAtivo() == 1) ? "Ativo" : "Inativo" ?></td>
				</tr>
				<?php if ($_SESSION["visualiza_dados"] == "S") { ?>
					<tr bgcolor="#F5F5FB" class="texto">
						<td><b>Login</b></td>
						<td><?php echo $objUsuarioGames->getLogin() ?></td>
						<td>
							<?php if (in_array(44, $grupos)) { ?>
								<a class="link_azul" href="#"
									Onclick="if(confirm('Deseja alterar o Email deste usuário ?')) fcnAlterar('email','<?php echo $objUsuarioGames->getEmail() ?>','');return false;"><b>Email</b></a>
							<?php } else { ?>
								<b>Email</b>
							<?php } ?>
						</td>
						<td><?php echo $objUsuarioGames->getEmail() ?></td>
					</tr>
				<?php } ?>
				<tr bgcolor="#F5F5FB" class="texto">
					<td><b>Data de Cadastro</b></td>
					<td><?php echo $objUsuarioGames->getDataInclusao() ?></td>
					<td><a class="link_azul" href="#"
							Onclick="if(confirm('Deseja alterar o Status de busca deste usuário ?')) window.open('com_usuario_detalhe_selecao.php?v_campo=status_busca&status_busca=<?php echo $objUsuarioGames->getStatusBusca() ?><?php echo $varsel ?>','selecao', 'status=0,width=500,height=200,top=0,left=0');return false;"><b>Status
								- Busca</b></a></td>
					<td><?php echo ($objUsuarioGames->getStatusBusca() == 1) ? "Ativo" : "Inativo" ?></td>
				</tr>
				<tr bgcolor="#F5F5FB" class="texto">
					<td><b>Qtde de Acessos</b></td>
					<td><?php echo $objUsuarioGames->getQtdeAcessos() ?></td>
					<td><b>Data Último Acesso</b></td>
					<td><?php

					// Pega data e horário do último acesso registrado nas tabelas dist_usuarios_games_log				
					
					// Verifica a data de acesso mais recente do cliente na tabela de usuários ESTÁ NO FORMATO d-m-Y H:i
					$data_tab_usuario = $objUsuarioGames->getDataUltimoAcesso();

					// Query para consultar a data mais recente de acesso registrada na tabela de hitórico do cliente
					$sql_acesso_recente_historico = "SELECT * FROM dist_usuarios_games_log WHERE ugl_ug_id = {$usuario_id} and ugl_uglt_id = '2' ORDER BY ugl_data_inclusao DESC LIMIT 1";

					// Faz a pesquisa no histórico do cliente
					$executa_pesquisa_no_historico = SQLexecuteQuery($sql_acesso_recente_historico);

					// Guarda a resposta da pesquisa no histórico do cliente
					$resposta_pesquisa_no_historico = pg_fetch_array($executa_pesquisa_no_historico);

					// Guarda a data de acesso mais recente no histórico do cliente ESTÁ NO FORMATO Y-m-d H:i
					$data_no_historico = $resposta_pesquisa_no_historico['ugl_data_inclusao'];

					// Para fazer a comparação corretamente, transforma $data_recente_tab_usuario para o modelo ano/mês/dia hora:minuto
					$data_tab_usuario_formato_comparacao = date('Y-m-d H:i', strtotime($data_tab_usuario));

					$data_no_historico_formatado = date('d-m-Y H:i', strtotime($data_no_historico));

					if (empty($data_tab_usuario_formato_comparacao) && empty($data_no_historico)) {
						echo "Sem registro";
					} else if (!strtotime($data_tab_usuario_formato_comparacao) && !strtotime($data_no_historico)) {
						echo "Sem registro";
					} else {
						if ($data_tab_usuario_formato_comparacao >= $data_no_historico) {
							echo $data_tab_usuario;
						} else if ($data_tab_usuario_formato_comparacao < $data_no_historico) {
							echo $data_no_historico_formatado;
						} else {
							echo "Sem registro";
						}
					}

					?></td>
				</tr>
				<tr bgcolor="#F5F5FB" class="texto">
					<td><b>Data de expiração de senha</b></td>
					<td><?php echo ($objUsuarioGames->getDataExpiraSenha()) ? $objUsuarioGames->getDataExpiraSenha() : "indefinido"; ?>
					</td>
					<td><b>Reenvio de chave mestra</b></td>
					<td><button type="button" class="btn btn-success" id="btn-send-key">Reenviar chave
							&#128273;</button></td>
				</tr>
				<tr bgcolor="#F5F5FB" class="texto">
					<td><b>Possui um autenticador</b></td>
					<td id="txt-auth"><?php
					$sql = "SELECT ug_chave_autenticador FROM dist_usuarios_games WHERE ug_id = " . $objUsuarioGames->getId();
					$res_te = SQLexecuteQuery($sql);
					if ($res_te_row = pg_fetch_array($res_te)) {
						$autenticador = !empty($res_te_row['ug_chave_autenticador']);
						echo $autenticador ? "Sim" : "Não";
					} else {
						echo "Não encontrado";
					}

					?></td>
					<td></td>
					<td><button type="button" class="btn btn-danger <?php echo $autenticador ? "" : "d-none" ?>"
							id="btn-remove-auth">Remover autenticador
							&#128274;</button></td>
				</tr>
				<tr bgcolor="#F5F5FB" class="texto">
					<td><b>Usuário bloqueado por fraude</b></td>
					<td id="txt-block"><?php
					$usuario_bloqueio = obterUsuarioBloqueado(0+$objUsuarioGames->getId());
					if ($usuario_bloqueio) {
						echo "Sim. Motivo: " . utf8_decode($usuario_bloqueio['motivo']) . " - Data: " . $usuario_bloqueio['data_bloqueio'];
					} else {
						echo "Não possui bloqueio por fraude";
					}
					?></td>
					<td></td>
					<td><button type="button"
							class="btn <?php echo $usuario_bloqueio ? "btn-success act-remove" : "btn-danger act-add" ?>"
							style="font-weight: bold;" id="btn-bloqueio"><?php echo $usuario_bloqueio
								? "Desbloquear usuário &#128275;"
								: "Bloquear usuário &#9888;&#65039;" ?>
						</button></td>
				</tr>

				<tr bgcolor="#FFFFFF" class="texto">
					<td colspan="4" bgcolor="#ECE9D8">
						<table border="0" cellspacing="0" cellpadding="0" width="0">
							<tr>
								<td align="left" class="texto">Habilitado para ONGAME (PB): </td>
								<td align="left" class="texto">
									&nbsp;&nbsp;&nbsp;&nbsp;<b><?php if (strtolower($objUsuarioGames->getUgOngame()) == 's')
										echo "Sim";
									else
										echo "N&atilde;o"; ?></b>
								</td>
							</tr>
						</table>
					</td>
				</tr>

				<tr bgcolor="#FFFFFF" class="texto">
					<td colspan="4" bgcolor="#ECE9D8">
						<table border="0" cellspacing="0" cellpadding="0" width="0">
							<tr>
								<td align="left" class="texto">Cadastro</td>
								<td width="100%">&nbsp;</td>
								<td align="right"><input type="button" value="Editar cadastro"
										Onclick="fcnEditarCadastro(<?php echo $objUsuarioGames->getId() ?>)"
										class="btn btn-info"> </td>
							</tr>
						</table>
					</td>
				</tr>

				<tr bgcolor="#F5F5FB" class="texto">
					<td><b>Tipo Estabelecimento</b></td>
					<td>
						<?php
						if (is_null($objUsuarioGames->getTipoEstabelecimento())) {
							echo "N&atilde;o Informado";
						} //end if(is_null($objUsuarioGames->getTipoEstabelecimento()))
						else {
							//selecionando a descrição do tipo de estabelecimento
							$sql = "select * from tb_tipo_estabelecimento where te_id=" . $objUsuarioGames->getTipoEstabelecimento();
							$res_te = SQLexecuteQuery($sql);
							if ($res_te_row = pg_fetch_array($res_te)) {
								echo utf8_decode($res_te_row['te_descricao']);
							}//end if($res_te_row = pg_fetch_array($res_te))
							else {
								echo "ID n&atilde;o encontrado";
							}
						}// else if(is_null($objUsuarioGames->getTipoEstabelecimento()))
						?>
					</td>
					<td><a class="link_azul" href="#"
							Onclick="if(confirm('Deseja alterar o Tipo de Cadastro?')) window.open('com_usuario_detalhe_selecao.php?v_campo=ug_tipo_cadastro&ug_tipo_cadastro=<?php echo $objUsuarioGames->getTipoCadastro() ?>&substatus_status=<?php echo $objUsuarioGames->getAtivo() ?><?php echo $varsel ?>','selecao', 'status=0,width=500,height=200,top=0,left=0');return false;"><b>Tipo
								de Cadastro</b></a></td>
					<td><?php echo $objUsuarioGames->getTipoCadastro(); ?></td>
				</tr>

				<?php if ($objUsuarioGames->getTipoCadastro() == "PJ") { ?>
					<tr bgcolor="#F5F5FB" class="texto">
						<td><b>Nome Fantasia</b></td>
						<td><?php echo $objUsuarioGames->getNomeFantasia() ?></td>
						<td><b>Razão Social</b></td>
						<td><?php echo $objUsuarioGames->getRazaoSocial() ?></td>
					</tr>
					<tr bgcolor="#F5F5FB" class="texto">
						<?php if ($_SESSION["visualiza_dados"] == "S") { ?>
							<td><b>CNPJ</b></td>
							<td><?php echo mascara_cnpj_cpf($objUsuarioGames->getCNPJ(), 'cnpj') ?></td>
						<?php } ?>
						<td <?php echo ($_SESSION["visualiza_dados"] == "S") ? '' : 'colspan="3"'; ?>><b>Inscrição
								Estadual</b>
						</td>
						<td><?php echo $objUsuarioGames->getInscrEstadual() ?></td>
					</tr>
					<tr bgcolor="#F5F5FB" class="texto">
						<td><b>Responsável</b></td>
						<td><?php echo $objUsuarioGames->getResponsavel() ?></td>
						<td><b>Abertura da Empresa</b></td>
						<td><?php echo substr("00" . $objUsuarioGames->getAberturaMes(), -2) ?>/<?php echo $objUsuarioGames->getAberturaAno() ?>
						</td>
					</tr>
					<tr bgcolor="#F5F5FB" class="texto">
						<td><b>Site</b></td>
						<td><?php echo $objUsuarioGames->getSite() ?></td>
						<td><a class="link_azul" href="#"
								Onclick="if(confirm('Deseja alterar o Substatus deste usuário ?')) window.open('com_usuario_detalhe_selecao.php?v_campo=substatus&substatus=<?php echo $objUsuarioGames->getSubstatus() ?>&substatus_status=<?php echo $objUsuarioGames->getAtivo() ?>&data_aprovacao=<?php echo urlencode($objUsuarioGames->getDataAprovacao()); ?><?php echo $varsel ?>','selecao', 'status=0,width=500,height=200,top=0,left=0');return false;"><b>Substatus</b></a>
						</td>
						<td>
							<?php
							echo $objUsuarioGames->getSubstatusDescription();
							if ($objUsuarioGames->getDataAprovacao() != "") {
								echo "<br>Data de Aprovação do PDV em: <b>" . $objUsuarioGames->getDataAprovacao() . "</b>";
							}//end if(!empty($objUsuarioGames->getDataAprovacao()))
							?>
						</td>
					</tr>
					<!--tr bgcolor="#F5F5FB" class="texto"> 
			<td><b>Ramo de Atividade</b></td>
			<td><?php echo $cad_RA ?></td>
			<td colspan="2"></td>
		  </tr-->
					<tr bgcolor="#F5F5FB" class="texto">
						<td><b>Fat. Médio Mensal</b></td>
						<td><?php if (isset($GLOBALS['CADASTRO_FATURAMENTO'][$objUsuarioGames->getFaturaMediaMensal()]))
							echo $GLOBALS['CADASTRO_FATURAMENTO'][$objUsuarioGames->getFaturaMediaMensal()]; ?>
						</td>
						<td><b>Qtde Computadores</b></td>
						<td><?php if (isset($GLOBALS['CADASTRO_COMPUTADORES'][$objUsuarioGames->getComputadoresQtde()]))
							echo $GLOBALS['CADASTRO_COMPUTADORES'][$objUsuarioGames->getComputadoresQtde()] ?>
							</td>
						</tr>
						<tr bgcolor="#F5F5FB" class="texto">
							<td><b>Cartões</b></td>
							<td>
							<?php $cad_Cartoes = preg_split("/;/", $objUsuarioGames->getCartoes()); ?>
							<?php if (isset($cad_Cartoes)) { ?>
								<?php foreach ($cad_Cartoes as $CartaoId) { ?>
									<?php if (isset($GLOBALS['CADASTRO_CARTOES'][$CartaoId]))
										echo $GLOBALS['CADASTRO_CARTOES'][$CartaoId] ?>,
								<?php } ?>
							<?php } ?>
						</td>
						<td><b>Comunicação Visual</b></td>
						<td>
							<?php $cad_ComunicacaoVisual = preg_split("/;/", $objUsuarioGames->getComunicacaoVisual()); ?>
							<?php if (isset($cad_ComunicacaoVisual)) { ?>
								<?php foreach ($cad_ComunicacaoVisual as $ComunicacaoId) { ?>
									<?php if (isset($GLOBALS['CADASTRO_COMUNICACAO'][$ComunicacaoId]))
										echo $GLOBALS['CADASTRO_COMUNICACAO'][$ComunicacaoId] ?>,
								<?php } ?>
							<?php } ?>
						</td>
					</tr>
					<tr bgcolor="#F5F5FB" class="texto">
						<td><strong>Canais de venda</strong></td>
						<td><?php echo $objUsuarioGames->getCanaisVenda(); ?></td>
						<td><strong>Tipo de Venda</strong></td>
						<td><?php echo ($objUsuarioGames->getTipoVenda() == '1' ? 'On' : ($objUsuarioGames->getTipoVenda() == '3' ? 'Online e Off' : 'Off')); ?>line
						</td>
					</tr>

					<tr bgcolor="#FFFFFF" class="texto">
						<td colspan="4" bgcolor="#ECE9D8">
							<table border="0" cellspacing="0" cellpadding="0" width="0">
								<tr>
									<td align="left" class="texto">Estilização</td>
									<td width="100%">&nbsp;</td>
								</tr>
							</table>
						</td>
					</tr>

					<?php
					function getEstilosUsuarioPDO($userId, PDO $pdo)
					{
						// Configurações recomendadas
						$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
						$pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);

						$sql = "SELECT ug_estilo, ug_logo FROM dist_usuarios_games WHERE ug_id = :userId";
						$stmt = $pdo->prepare($sql);
						$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
						$stmt->execute();

						$resultado = $stmt->fetch(PDO::FETCH_ASSOC);

						if ($resultado && isset($resultado['ug_estilo'])) {
							$dados = json_decode($resultado['ug_estilo'], true);

							if (!empty($resultado['ug_logo'])) {
								// Se for um stream, precisamos ler o conteúdo
								$logoRaw = is_resource($resultado['ug_logo'])
									? stream_get_contents($resultado['ug_logo'])
									: $resultado['ug_logo'];

								// Detectar extensão do logo (opcional)
								$ext = isset($dados['logo_extensao']) ? strtolower($dados['logo_extensao']) : 'png';
								$mime = ($ext === 'jpg') ? 'jpeg' : $ext;

								// Gerar base64 da imagem
								$dados['logo_base64'] = 'data:image/' . $mime . ';base64,' . base64_encode($logoRaw);
							}

							return is_array($dados) ? $dados : array();
						}

						return array();
					}
					$con = ConnectionPDO::getConnection();
					$pdo = $con->getLink();
					$estilos = getEstilosUsuarioPDO($objUsuarioGames->getId(), $pdo);
					if (!filter_var($estilos['email_suporte'], FILTER_VALIDATE_EMAIL)) {
						$estilos['email_suporte'] = "";
					}
					if (!filter_var($estilos['link_canal'], FILTER_VALIDATE_URL)) {
						$estilos['link_canal'] = "";
					}
					?>

					<tr bgcolor="#F5F5FB" class="texto">
						<td><b>Cor primária</b></td>
						<td>
							<?php
							echo '<div style="
									width: 90px;
									height: 30px;
									border-radius: 10px;
									background-color: ' . htmlspecialchars($estilos['cor_primaria']) . ';
									color: ' . corTextoContraste(htmlspecialchars($estilos['cor_primaria'])) . ';
									display: flex;
									align-items: center;
									justify-content: center;
									font-family: Arial, sans-serif;
									font-weight: bold;
									font-size: 14px;
									box-shadow: 2px 3px 6px rgba(0, 0, 0, 0.3);
									">
									' . htmlspecialchars($estilos['cor_primaria']) . '
									</div>';
							?>
						</td>
						<td><b>Cor secundária</b></td>
						<td><?php
						echo '<div style="
									width: 90px;
									height: 30px;
									border-radius: 10px;
									background-color: ' . htmlspecialchars($estilos['cor_secundaria']) . ';
									color: ' . corTextoContraste(htmlspecialchars($estilos['cor_secundaria'])) . ';
									display: flex;
									align-items: center;
									justify-content: center;
									font-family: Arial, sans-serif;
									font-weight: bold;
									font-size: 14px;
									box-shadow: 2px 3px 6px rgba(0, 0, 0, 0.3);
									">
									' . htmlspecialchars($estilos['cor_secundaria']) . '
									</div>';
						?></td>
					</tr>
					<tr bgcolor="#F5F5FB" class="texto">
						<td><b>E-mail de suporte</b></td>
						<td>
							<?php
							echo htmlspecialchars($estilos['email_suporte']);
							?>
						</td>
						<td><b>Canal de atendimento</b></td>
						<td><?php echo htmlspecialchars($estilos['link_canal']); ?></td>
					</tr>
					<tr bgcolor="#F5F5FB" class="texto">
						<td><b>Mensagem</b></td>
						<td>
							<?php
							echo htmlspecialchars($estilos['mensagem']);
							?>
						</td>
						<td><b>Logo</b></td>
						<td><?php echo "<img src='" . $estilos['logo_base64'] . "' title='logo' alt='Sem logo' border='0' class='imagem_epp' style='max-width: 100px; max-height: 25px;'>"; ?>
						</td>
					</tr>

					<tr bgcolor="#FFFFFF" class="texto">
						<td colspan="4" bgcolor="#ECE9D8">Representante Legal da Empresa</font>
						</td>
					</tr>
					<tr bgcolor="#F5F5FB" class="texto">
						<td><b>Nome</b></td>
						<td><?php echo $objUsuarioGames->getReprLegalNome() ?></td>
						<td><b>Data Nascimento</b></td>
						<td><?php echo substr(formata_data($objUsuarioGames->getReprLegalDataNascimento(), 0), 0, 10) ?>
						</td>
					</tr>
					<tr bgcolor="#F5F5FB" class="texto">
						<td><b>CPF</b></td>
						<td><?php echo $objUsuarioGames->getReprLegalCPF() ?></td>
						<td><b>RG</b></td>
						<td><?php echo $objUsuarioGames->getReprLegalRG() ?></td>
					</tr>
					<tr bgcolor="#F5F5FB" class="texto">
						<td><b>Telefone</b></td>
						<td>(<?php echo $objUsuarioGames->getReprLegalTelDDI() ?>)
							(<?php echo $objUsuarioGames->getReprLegalTelDDD() ?>)
							<?php echo $objUsuarioGames->getReprLegalTel() ?>
						</td>
						<td><b>Celular</b></td>
						<td>(<?php echo $objUsuarioGames->getReprLegalCelDDI() ?>)
							(<?php echo $objUsuarioGames->getReprLegalCelDDD() ?>)
							<?php echo $objUsuarioGames->getReprLegalCel() ?>
						</td>
					</tr>
					<?php if ($_SESSION["visualiza_dados"] == "S") { ?>
						<tr bgcolor="#F5F5FB" class="texto">
							<td><b>Email</b></td>
							<td colspan="4"><?php echo $objUsuarioGames->getReprLegalEmail() ?></td>
						</tr>
					<?php } ?>
					<tr bgcolor="#F5F5FB" class="texto">
						<td><b>Skype / Estilo resgate</b></td>
						<td colspan="4" style="text-align: center; letter-spacing: 1.5px;"><?php
						if ($objUsuarioGames->getReprLegalMSN() != "" && is_object(json_decode($objUsuarioGames->getReprLegalMSN()))) {
							$dadosJson = json_decode($objUsuarioGames->getReprLegalMSN());
							echo "<ul style='list-style: none;display: flex;justify-content: space-around;margin-bottom: 0;padding: 0;'>";
							foreach ($dadosJson as $key => $value) {
								if ($key != "EMAILMARK") {
									echo "<li style='padding: 8px;'><span style='border: 1px solid #bbb;padding: 6px;'>" . ucfirst(strtolower($key)) . "</span><b style='padding: 6px;border: 1px solid #bbb;background-color: #dddddd;'>" . $value . "</b></li>";
								}
								//echo "<li>E-MAIL MARKETING : </b>".$value."<b style='color: red;'> | </li>";
							}
							echo "</ul>";
						} else {
							echo $objUsuarioGames->getReprLegalMSN();
						}
						?></td>
					</tr>
					<tr bgcolor="#F5F5FB" class="texto">
						<td><b>Informe</b></td>
						<td colspan="4" style="text-align: center; letter-spacing: 1.5px;"><?php
						if ($objUsuarioGames->getReprLegalMSN() != "" && is_object(json_decode($objUsuarioGames->getReprLegalMSN()))) {
							$dadosJson = json_decode($objUsuarioGames->getReprLegalMSN());
							echo "<ul style='list-style: none;display: flex;justify-content: space-around;margin-bottom: 0;padding: 0;'>";
							foreach ($dadosJson as $key => $value) {
								if ($key == "EMAILMARK") {
									echo "<li style='padding: 8px;display: flex;'><span style='border: 1px solid #bbb;padding: 6px;'>E-mail Marketing</span><b style='padding: 6px;border: 1px solid #bbb;background-color: #dddddd;'>" . $value . "</b></li>";
								}
							}
							echo "<li style='padding: 8px;display: flex;'><span style='border: 1px solid #bbb;padding: 6px;'>Representate Legal</span><b style='padding: 6px;border: 1px solid #bbb;background-color: #dddddd;'>" . $objUsuarioGames->getReprLegalNome() . "</b></li>";
							echo "<li style='padding: 8px;display: flex;'><span style='border: 1px solid #bbb;padding: 6px;'>Nome Fantasia</span><b style='padding: 6px;border: 1px solid #bbb;background-color: #dddddd;'>" . $objUsuarioGames->getNomeFantasia() . "</b></li>";
							echo "</ul>";
						} else {
							echo $objUsuarioGames->getReprLegalMSN();
						}
						?></td>
					</tr>
					<tr bgcolor="#FFFFFF" class="texto">
						<td colspan="4" bgcolor="#ECE9D8">Principal Contato para assuntos relacionados à venda de crédito
							digitáis e cartões pré-pagos para games online</font>
						</td>
					</tr>
					<?php if ($objUsuarioGames->getReprVendaIgualReprLegal() == "1") { ?>
						<tr bgcolor="#F5F5FB" class="texto">
							<td colspan="4">Representante Legal da Empresa também é o Principal Contato</td>
						</tr>
					<?php } else { ?>
						<tr bgcolor="#F5F5FB" class="texto">
							<td><b>Nome</b></td>
							<td colspan="3"><?php echo $objUsuarioGames->getReprVendaNome() ?></td>
						</tr>
						<tr bgcolor="#F5F5FB" class="texto">
							<td><b>CPF</b></td>
							<td><?php echo $objUsuarioGames->getReprVendaCPF() ?></td>
							<td><b>RG</b></td>
							<td><?php echo $objUsuarioGames->getReprVendaRG() ?></td>
						</tr>
						<tr bgcolor="#F5F5FB" class="texto">
							<td><b>Telefone</b></td>
							<td>(<?php echo $objUsuarioGames->getReprVendaTelDDI() ?>)
								(<?php echo $objUsuarioGames->getReprVendaTelDDD() ?>)
								<?php echo $objUsuarioGames->getReprVendaTel() ?>
							</td>
							<td><b>Celular</b></td>
							<td>(<?php echo $objUsuarioGames->getReprVendaCelDDI() ?>)
								(<?php echo $objUsuarioGames->getReprVendaCelDDD() ?>)
								<?php echo $objUsuarioGames->getReprVendaCel() ?>
							</td>
						</tr>
						<tr bgcolor="#F5F5FB" class="texto">
							<td><b>Email</b></td>
							<td><?php echo $objUsuarioGames->getReprVendaEmail() ?></td>
							<td><b>MSN</b></td>
							<td><?php echo $objUsuarioGames->getReprVendaMSN() ?></td>
						</tr>
					<?php } ?>

					<tr bgcolor="#FFFFFF" class="texto">
						<td colspan="4" bgcolor="#ECE9D8">Sócios</font>
						</td>
					</tr>
					<?php $sql_socios = "SELECT * FROM dist_usuarios_games_socios WHERE ug_id = " . $objUsuarioGames->getId() . " order by ugs_percentagem DESC;";
					$res_socios = SQLexecuteQuery($sql_socios);

					if ($res_socios && pg_num_rows($res_socios) > 0) {
						$i = 0;
						while ($res_row = pg_fetch_array($res_socios)) {
							?>
							<tr bgcolor="#F5F5FB" class="texto">
								<td colspan="4"><b>Sócio <?php echo ($i + 1); ?></b></td>
							</tr>
							<tr bgcolor="#F5F5FB" class="texto">
								<td><b>Nome</b></td>
								<td><?php echo $res_row['ugs_nome']; ?></td>
								<td><b>Porcentagem na Empresa</b></td>
								<td><?php echo $res_row['ugs_percentagem'] . "%"; ?></td>
							</tr>
							<tr bgcolor="#F5F5FB" class="texto">
								<td><b>CPF</b></td>
								<td class="cpf"> <?php echo mascara_cnpj_cpf($res_row['ugs_cpf'], 'cpf'); ?></td>
								<td><b>Data Nascimento</b></td>
								<td><?php echo formata_data($res_row['ugs_data_nascimento'], 0); ?></td>
								<td colspan="2"></td>
							</tr>
							<?php
							$i++;
						}
					} else {
						?>
						<tr bgcolor="#F5F5FB" class="texto">
							<td colspan="4"><b>*Sócios ainda não informados</b></font>
							</td>
						</tr>
						<?php
					}
					?>
					<tr bgcolor="#FFFFFF" class="texto">
						<td colspan="4" bgcolor="#ECE9D8">Dados Bancários</font>
						</td>
					</tr>
					<tr>
						<td colspan="4" width="100%">
							<table border="0" cellspacing="0" cellpadding="0" width="100%">
								<tr bgcolor="#F5F5FB" class="texto">
									<td align="center"><b>#</b></td>
									<td align="center"><b>Banco</b></td>
									<td align="center"><b>Agência</b></td>
									<td align="center"><b>Conta</b></td>
									<td align="center"><b>Data Abertura</b></td>
								</tr>
								<tr bgcolor="#F5F5FB" class="texto">
									<td align="center">&nbsp;1&nbsp;</td>
									<td align="center"><?php echo $objUsuarioGames->getDadosBancarios01Banco() ?></td>
									<td align="center"><?php echo $objUsuarioGames->getDadosBancarios01Agencia() ?></td>
									<td align="center"><?php echo $objUsuarioGames->getDadosBancarios01Conta() ?></td>
									<td align="center"><?php echo $objUsuarioGames->getDadosBancarios01Abertura() ?></td>
								</tr>
								<tr bgcolor="#F5F5FB" class="texto">
									<td align="center">&nbsp;&nbsp;2&nbsp;</td>
									<td align="center"><?php echo $objUsuarioGames->getDadosBancarios02Banco() ?></td>
									<td align="center"><?php echo $objUsuarioGames->getDadosBancarios02Agencia() ?></td>
									<td align="center"><?php echo $objUsuarioGames->getDadosBancarios02Conta() ?></td>
									<td align="center"><?php echo $objUsuarioGames->getDadosBancarios02Abertura() ?></td>
								</tr>

							</table>
						</td>
					</tr>

					<tr bgcolor="#FFFFFF" class="texto">
						<td colspan="4" bgcolor="#ECE9D8">Contato Técnico</font>
						</td>
					</tr>
					<tr bgcolor="#F5F5FB" class="texto">
						<td><b>Nome</b></td>
						<td><?php echo $objUsuarioGames->getContato01Nome() ?></td>
						<td><b>Cargo</b></td>
						<td><?php echo $objUsuarioGames->getContato01Cargo() ?></td>
					</tr>
					<tr bgcolor="#F5F5FB" class="texto">
						<td><b>Telefone</b></td>
						<td>(<?php echo $objUsuarioGames->getContato01TelDDI() ?>)
							(<?php echo $objUsuarioGames->getContato01TelDDD() ?>)
							<?php echo $objUsuarioGames->getContato01Tel() ?>
						</td>
						<td colspan="2"></td>
					</tr>
				<?php } ?>

				<?php if ($objUsuarioGames->getTipoCadastro() == "PF") { ?>
					<tr bgcolor="#F5F5FB" class="texto">
						<td><b>Nome</b></td>
						<td><?php echo $objUsuarioGames->getNome() ?></td>
						<td><a class="link_azul" href="#"
								Onclick="if(confirm('Deseja alterar o Substatus deste usuário ?')) window.open('com_usuario_detalhe_selecao.php?v_campo=substatus&substatus=<?php echo $objUsuarioGames->getSubstatus() ?>&substatus_status=<?php echo $objUsuarioGames->getAtivo() ?><?php echo $varsel ?>','selecao', 'status=0,width=500,height=200,top=0,left=0');return false;"><b>Substatus</b></a>
						</td>
						<td><?php echo $objUsuarioGames->getSubstatusDescription() . " (" . $objUsuarioGames->getSubstatus() . ")"; ?>
						</td>
					</tr>
					<tr bgcolor="#F5F5FB" class="texto">
						<td><b>CPF</b></td>
						<td><?php echo $objUsuarioGames->getCPF() ?></td>
						<td><b>RG</b></td>
						<td><?php echo $objUsuarioGames->getRG() ?></td>
					</tr>
					<tr bgcolor="#F5F5FB" class="texto">
						<td><b>Data de Nascimento</b></td>
						<td><?php echo substr($objUsuarioGames->getDataNascimento(), 0, 10) ?></td>
						<td><b>Sexo</b></td>
						<td><?php echo $objUsuarioGames->getSexo() ?></td>
					</tr>
					<tr bgcolor="#F5F5FB" class="texto">
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td><b>Tipo de Venda</b></td>
						<td><?php echo ($objUsuarioGames->getTipoVenda() == '1' ? 'On' : ($objUsuarioGames->getTipoVenda() == '3' ? 'Online e Off' : 'Off')); ?>line
						</td>
					</tr>
				<?php } ?>

				<?php if (($objUsuarioGames->getTipoCadastro() != "PF") && ($objUsuarioGames->getTipoCadastro() != "PJ")) { ?>
					<tr bgcolor="#F5F5FB" class="texto">
						<td><b>Tipo cadastro&nbsp;</b></td>
						<td>
							<font color='#FF0000'>Sem Tipo cadastro definido</font>
						</td>
						<td><a class="link_azul" href="#"
								Onclick="if(confirm('Deseja alterar o Substatus deste usuário ?')) window.open('com_usuario_detalhe_selecao.php?v_campo=substatus&substatus=<?php echo $objUsuarioGames->getSubstatus() ?>&substatus_status=<?php echo $objUsuarioGames->getAtivo() ?><?php echo $varsel ?>','selecao', 'status=0,width=500,height=200,top=0,left=0');return false;"><b>Substatus</b></a>
						</td>
						<td><?php echo $objUsuarioGames->getSubstatusDescription(); ?></td>
					</tr>
				<?php } ?>

				<tr bgcolor="#FFFFFF" class="texto">
					<td colspan="4" bgcolor="#ECE9D8">Endereço</font>
					</td>
				</tr>
				<tr bgcolor="#F5F5FB" class="texto">
					<td><b>Tipo de Endereço</b></td>
					<td><?php echo $objUsuarioGames->getTipoEnd() ?></td>
					<td><b>Endereço</b></td>
					<td><?php echo $objUsuarioGames->getEndereco() ?></td>
				</tr>
				<tr bgcolor="#F5F5FB" class="texto">
					<td><b>Número</b></td>
					<td><?php echo $objUsuarioGames->getNumero() ?></td>
					<td><b>Complemento</b></td>
					<td><?php echo $objUsuarioGames->getComplemento() ?></td>
				</tr>
				<tr bgcolor="#F5F5FB" class="texto">
					<td><b>Bairro</b></td>
					<td><?php echo $objUsuarioGames->getBairro() ?></td>
					<td><b>Cidade</b></td>
					<td><?php echo $objUsuarioGames->getCidade() ?></td>
				</tr>
				<tr bgcolor="#F5F5FB" class="texto">
					<td><b>CEP</b></td>
					<td><?php echo $objUsuarioGames->getCEP() ?></td>
					<td><b>Estado</b></td>
					<td><?php echo $objUsuarioGames->getEstado() ?></td>
				</tr>
				<tr bgcolor="#F5F5FB" class="texto">
					<td><b>Telefone</b></td>
					<td>(<?php echo $objUsuarioGames->getTelDDI() ?>) (<?php echo $objUsuarioGames->getTelDDD() ?>)
						<?php echo $objUsuarioGames->getTel() ?>
					</td>
					<td><b>Celular</b></td>
					<td>(<?php echo $objUsuarioGames->getCelDDI() ?>) (<?php echo $objUsuarioGames->getCelDDD() ?>)
						<?php echo $objUsuarioGames->getCel() ?>
					</td>
				</tr>
				<tr bgcolor="#F5F5FB" class="texto">
					<td><b>Fax</b></td>
					<td>(<?php echo $objUsuarioGames->getFaxDDI() ?>) (<?php echo $objUsuarioGames->getFaxDDD() ?>)
						<?php echo $objUsuarioGames->getFax() ?>
					</td>
					<td colspan="2"></td>
				</tr>

				<!-- [NEXCAFE] -->
				<tr bgcolor="#FFFFFF" class="texto">
					<td colspan="4" bgcolor="#ECE9D8">NexCafé</font>
					</td>
				</tr>
				<tr bgcolor="#F5F5FB" class="texto">
					<td><b>Login NexCafé Plus+</b></td>
					<td colspan="2"><?php echo $objUsuarioGames->getUgIdNexCafe(); ?></td>
					<td colspan="2"><b>Data Adesão NexCafé Plus+</b>
						&nbsp;<?php echo (($objUsuarioGames->getUgDataInclusaoNexCafe()) ? formata_data_ts($objUsuarioGames->getUgDataInclusaoNexCafe() . "", 0, true, true) : "-"); ?>
					</td>
				</tr>
				<tr bgcolor="#F5F5FB" class="texto">
					<td colspan="2">
						<b>Login automático no NexCafé está habilitado?</b>
						<?php echo (($objUsuarioGames->getUgLoginNexCafeAuto() == 1) ? "Sim" : "Não"); ?>
					</td>
				</tr>

				<tr bgcolor="#FFFFFF" class="texto">
					<td colspan="4" bgcolor="#ECE9D8">Observaçôes</font>
					</td>
				</tr>
				<tr bgcolor="#F5F5FB" class="texto">
					<td><b>Observações</b></td>
					<td colspan="3"><?php echo str_replace("\n", "<br>", $objUsuarioGames->getObservacoes()) ?></td>
				</tr>

				<tr bgcolor="#FFFFFF" class="texto">
					<td colspan="4" bgcolor="#ECE9D8">Gestão de Risco</font>
					</td>
				</tr>
				<tr bgcolor="#F5F5FB" class="texto">
					<td><a class="link_azul" href="#"
							Onclick="if(confirm('Deseja alterar a Classificação de Risco deste usuário ?')) window.open('com_usuario_detalhe_selecao.php?v_campo=riscoclassif&riscoclassif=<?php echo $objUsuarioGames->getRiscoClassif() ?><?php echo $varsel ?>','selecao', 'status=0,width=500,height=200,top=0,left=0');return false;"><b>Classificação</b></a>
					</td>
					<td colspan="3"><?php echo $RISCO_CLASSIFICACAO_NOMES[$objUsuarioGames->getRiscoClassif()] ?>:
						Descrição
						'<b><?php echo $RISCO_CLASSIFICACAO_DESCRICAO[$objUsuarioGames->getRiscoClassif()] ?></b>'
						<?php //&nbsp;&nbsp;&nbsp;[<_? if(bBloqueiaSePrepag($objUsuarioGames->getLogin())) { echo "<b><font color='#FF0000'>Esta LH pode ser habilitada para Pré-pago</font></b>"; } else { echo "Esta LH não pode ser habilitada para Pré-pago";  } ?_>] ?>
					</td>
				</tr>
				<tr bgcolor="#F5F5FB" class="texto">
					<td><b>Como Conheceu a E-prepag?</b></td>
					<td colspan="3">
						<?php echo (($objUsuarioGames->getFicouSabendo()) ? $objUsuarioGames->getFicouSabendo() : "--"); ?>
					</td>
				</tr>

				<tr bgcolor="#FFFFFF" class="texto">
					<td colspan="4" bgcolor="#ECE9D8">Cadastro de Funcionários</font>
					</td>
				</tr>
				<tr bgcolor="#F5F5FB" class="texto">
					<td colspan="4">
						<div id="Layer1" class=""
							style="position:static; width:100%; height:150px; z-index:1; overflow: auto;">

							<?php
							$sql = "select * from dist_usuarios_games_operador ugo where ugo.ugo_ug_id = " . $usuario_id . "";
							$res_count = SQLexecuteQuery($sql);
							$total_table = pg_num_rows($res_count);
							//echo "sql: $sql<br>";
//echo "total_table: $total_table<br>";
							
							//		$sql .= " limit ".$max; 
//		$sql .= " offset ".$inicial;
							$rs_operadores = SQLexecuteQuery($sql);
							//echo "sql: $sql<br>";
							
							?>
							<table class="table">
								<tr>
									<td>
										<font color="#666666" size="2" face="Arial, Helvetica, sans-serif">
											<?php if (!isset($inicial))
												$inicial = null;
											if (!isset($reg_ate))
												$reg_ate = null;
											if ($total_table > 0) { ?>
												Exibindo resultados <strong><?php echo $inicial + 1 ?></strong>
												a <strong><?php echo $reg_ate ?></strong> de
												<strong><?php echo $total_table ?></strong>
											</font>
										<?php } ?>
									</td>
									<td>
										<div align="right"></div>
									</td>
								</tr>
							</table>
							<table class="table">
								<tr class="texto" bgcolor="#ECE9D8">
									<td align="center"><strong>
											<font color="#666666">ID</font>
										</strong></td>
									<td align="center"><strong>
											<font color="#666666">Nome</font>
										</strong></td>
									<td align="center"><strong>
											<font color="#666666">Login</font>
										</strong></td>
									<td align="center"><strong>
											<font color="#666666">Ativo</font>
										</strong></td>
									<td align="center"><strong>
											<font color="#666666">Tipo</font>
										</strong></td>
									<td align="center"><strong>
											<font color="#666666">Inclusão</font>
										</strong></td>
									<td align="center"><strong>
											<font color="#666666">Último Acesso</font>
										</strong></td>
									<td align="center"><strong>
											<font color="#666666">Qtde. Acessos</font>
										</strong></td>
									<td align="center"><strong>
											<font color="#666666"></font>
										</strong></td>
								</tr>

								<?php
								$cor1 = "#f5f5fb";
								$cor2 = "#f5f5fb";
								$cor3 = "#E5E5Eb";

								if ($rs_operadores) {
									?>
									<?php
									while ($rs_operadores_row = pg_fetch_array($rs_operadores)) {
										if ($cor1 == $cor2) {
											$cor1 = $cor3;
										} else {
											$cor1 = $cor2;
										}
										?>
										<tr class="texto" bgcolor="<?php echo $cor1 ?>">
											<td align="left"><?php echo $rs_operadores_row['ugo_id'] ?></td>
											<td align="left"><?php echo $rs_operadores_row['ugo_nome'] ?> </td>
											<td align="center"><?php echo $rs_operadores_row['ugo_login'] ?> </td>
											<td align="center">
												<?php echo ($rs_operadores_row['ugo_ativo'] == 1) ? "Sim" : "<font color='#FF0000'>Não</font>"; ?>
											</td>
											<td align="center">
												<?php echo $GLOBALS['USUARIO_GAMES_OPERADOR_TIPOS_NOME'][$rs_operadores_row['ugo_tipo']]; ?>
											</td>
											<td align="center">
												<?php echo formata_data($rs_operadores_row['ugo_data_inclusao'], 0) ?>
											</td>
											<td align="center">
												<?php echo formata_data($rs_operadores_row['ugo_data_ultimo_acesso'], 0) ?>
											</td>
											<td align="center"><?php echo $rs_operadores_row['ugo_qtde_acessos'] ?> </td>

							</td>
							<td align="center">&nbsp;</td>
						</tr>
						<?php
									}
									?>
					</form>
					<?php
								} else {
									?>
					<tr class="texto" bgcolor="<?php echo $cor1 ?>">
						<td align="center" colspan="8">
							<font color="#FF0000">Não foram encontrados funcionários</font>
						</td>
					</tr>
					<?php
								}
								?>
			</table>

			</div>
		</td>
	</tr>

</table>

<table class="table">
	<tr bgcolor="#FFFFFF" class="texto">
		<td bgcolor="#ECE9D8">Pedidos</td>
	</tr>
	<tr bgcolor="#FFFFFF" class="texto">
		<td>
			<?php
			$varsel = "&usuario_id=" . $usuario_id;
			if (!isset($ncamp_v) || !$ncamp_v)
				$ncamp_v = "vg_data_inclusao";
			if (!isset($ordem_v) || !$ordem_v)
				$ordem_v = 1;
			if (!isset($inicial_v) || !$inicial_v)
				$inicial_v = 0;
			//if(b_IsUsuarioReinaldo()) { 
//echo "<font color='#FF0000'>inicial_v: $inicial_v</font><br>";
//echo "varsel: $varsel<br>";
//}
/*
			$sql  = "select vg.vg_id, vg.vg_data_inclusao, vg.vg_pagto_tipo, vg.vg_ultimo_status, vg.vg_concilia, 
							sum(vgm.vgm_valor * vgm.vgm_qtde) as valor, sum(vgm.vgm_qtde) as qtde_itens, count(*) as qtde_produtos, 
							sum(vgm.vgm_valor * vgm.vgm_qtde - vgm.vgm_valor * vgm.vgm_qtde * vgm_perc_desconto / 100) as repasse
					 from tb_dist_venda_games vg 
					 inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
 					 where vg.vg_ug_id = " . $usuario_id . "
					 group by vg.vg_id, vg.vg_data_inclusao, vg.vg_pagto_tipo, vg.vg_ultimo_status, vg.vg_concilia ";
*/
			$sql = "select * from (
						select 'p' as pedido_tipo, vg.vg_id, vg.vg_data_inclusao as vg_data_inclusao, vg.vg_pagto_tipo, vg.vg_ultimo_status, vg.vg_concilia, sum(vgm.vgm_valor * vgm.vgm_qtde) as valor, 
							sum(vgm.vgm_qtde) as qtde_itens, count(*) as qtde_produtos, 
							sum(vgm.vgm_valor * vgm.vgm_qtde - vgm.vgm_valor * vgm.vgm_qtde * vgm_perc_desconto / 100) as repasse 
						from tb_dist_venda_games vg 
							inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
						where vg.vg_ug_id = " . $usuario_id . " 
						group by vg.vg_id, vg.vg_data_inclusao, vg.vg_pagto_tipo, vg.vg_ultimo_status, vg.vg_concilia 
						union all 
						select 'x' as tipo_pedido, bbg_vg_id as vg_id, bbg_data_inclusao as vg_data_inclusao, 2 as vg_pagto_tipo, 5 as vg_ultimo_status, 0 as vg_concilia, bbg_valor as valor, 1 as qtde_itens, 1 as qtde_produtos, bbg_valor_taxa as repasse
						from  dist_boleto_bancario_games 
						where bbg_ug_id= " . $usuario_id . "
						) v ";
			//$rs_venda = SQLexecuteQuery($sql);
			//$total_table = pg_num_rows($rs_venda);
			$sql .= " order by " . $ncamp_v . " " . ($ordem_v == 1 ? "desc" : "asc");
			//$sql .= " limit " . $max . " offset " . $inicial_v;
			$sql .= " limit 50; ";


			//echo "$sql<br>";
			$rs_venda = SQLexecuteQuery($sql);
			$total_table = pg_num_rows($rs_venda);
			?>
			<div id="Layer1" class="" style="position:static; width:100%; height:150px; z-index:1; overflow: auto;">

				<table class="table">
					<?php $ordem_v = ($ordem_v == 1) ? 2 : 1; ?>
					<tr bgcolor="#ECE9D8">
						<td align="center"><a style="text-decoration:none"
								href="<?php echo $default_add . "?ordem_v=" . $ordem_v . "&ncamp_v=vg_id&inicial_v=" . $inicial_v . $varsel ?>"
								class="link_branco">Pedido</a></td>
						<td align="center">Tipo</td>
						<td align="center"><a style="text-decoration:none"
								href="<?php echo $default_add . "?ordem_v=" . $ordem_v . "&ncamp_v=vg_data_inclusao&inicial_v=" . $inicial_v . $varsel ?>"
								class="link_branco">Data</a></td>
						<td align="center"><a style="text-decoration:none"
								href="<?php echo $default_add . "?ordem_v=" . $ordem_v . "&ncamp_v=vg_pagto_tipo&inicial_v=" . $inicial_v . $varsel ?>"
								class="link_branco">Forma de Pagamento</a><br>(ord. pelo código)</td>
						<td align="center"><a style="text-decoration:none"
								href="<?php echo $default_add . "?ordem_v=" . $ordem_v . "&ncamp_v=vg_ultimo_status&inicial_v=" . $inicial_v . $varsel ?>"
								class="link_branco">Status</a><br>(ord. pelo código)</td>
						<td align="center"><a style="text-decoration:none"
								href="<?php echo $default_add . "?ordem_v=" . $ordem_v . "&ncamp_v=valor&inicial_v=" . $inicial_v . $varsel ?>"
								class="link_branco">Valor</a></td>
						<td align="center"><a style="text-decoration:none"
								href="<?php echo $default_add . "?ordem_v=" . $ordem_v . "&ncamp_v=repasse&inicial_v=" . $inicial_v . $varsel ?>"
								class="link_branco">Repasse</a></td>
						<td align="center"><a style="text-decoration:none"
								href="<?php echo $default_add . "?ordem_v=" . $ordem_v . "&ncamp_v=qtde_itens&inicial_v=" . $inicial_v . $varsel ?>"
								class="link_branco">Qtde Itens</a></td>
						<td align="center"><a style="text-decoration:none"
								href="<?php echo $default_add . "?ordem_v=" . $ordem_v . "&ncamp_v=qtde_produtos&inicial_v=" . $inicial_v . $varsel ?>"
								class="link_branco">Qtde Produtos</a></td>
						<td align="center"><a style="text-decoration:none"
								href="<?php echo $default_add . "?ordem_v=" . $ordem_v . "&ncamp_v=vg_concilia&inicial_v=" . $inicial_v . $varsel ?>"
								class="link_branco">Conciliação</a></td>
					</tr>
					<?php if (!$rs_venda || pg_num_rows($rs_venda) == 0) { ?>
						<tr>
							<td align="center" colspan="9">Nenhum pedido encontrado</td>
						</tr>
					<?php } else { ?>
						<?php while ($rs_venda_row = pg_fetch_array($rs_venda)) {
							if ($cor1 == $cor2) {
								$cor1 = $cor3;
							} else {
								$cor1 = $cor2;
							}
							$status = $rs_venda_row['vg_ultimo_status'];
							$pagto_tipo = $rs_venda_row['vg_pagto_tipo'];
							if ($pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['DEP_DOC_TRANSF'])
								$pagto_tipo = "Transf, DOC, Dep";
							elseif ($pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO'])
								$pagto_tipo = "Boleto";
							else
								$pagto_tipo = $GLOBALS['FORMAS_PAGAMENTO_DESCRICAO'][$pagto_tipo];
							?>
							<tr bgcolor="<?php echo $cor1 ?>">
								<td class="texto" align="center"><a style="text-decoration:none"
										href="/pdv/vendas/com_venda_detalhe.php?venda_id=<?php echo $rs_venda_row['vg_id'] ?>"><?php echo $rs_venda_row['vg_id'] ?></a>
								</td>
								<td class="texto">
									<?php echo (($rs_venda_row['pedido_tipo'] == 'p') ? "LH Money" : (($rs_venda_row['pedido_tipo'] == 'x') ? "LH Money Express" : "???")); ?>
								</td>
								<td class="texto" align="center">
									<?php echo formata_data_ts($rs_venda_row['vg_data_inclusao'], 0, true, true) ?>
								</td>
								<td class="texto"><?php echo $pagto_tipo ?></td>
								<td class="texto">
									<?php echo substr($GLOBALS['STATUS_VENDA_DESCRICAO'][$status], 0, strpos($GLOBALS['STATUS_VENDA_DESCRICAO'][$status], '.')) ?>
								</td>
								<td class="texto" align="right"><?php echo number_format($rs_venda_row['valor'], 2, ',', '.') ?>
								</td>
								<td class="texto" align="right">
									<?php echo number_format($rs_venda_row['repasse'], 2, ',', '.') ?>
								</td>
								<td class="texto" align="center"><?php echo $rs_venda_row['qtde_itens'] ?></td>
								<td class="texto" align="center"><?php echo $rs_venda_row['qtde_produtos'] ?></td>
								<?php if (
									$status == $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO'] ||
									$status == $GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO'] ||
									$status == $GLOBALS['STATUS_VENDA']['PROCESSAMENTO_REALIZADO'] ||
									$status == $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']
								) { ?>
									<td class="texto" align="center"><a style="text-decoration:none"
											href="/pdv/vendas/com_venda_detalhe.php?venda_id=<?php echo $rs_venda_row['vg_id'] ?>"><?php echo (($rs_venda_row['vg_concilia'] == 0) ? "Conciliar" : "Conciliado") ?></a>
									</td>
								<?php } else { ?>
									<td>&nbsp;</td>
								<?php } ?>
							</tr>
						<?php } ?>
						<?php //paginacao_query($inicial_v, $total_table, $max, 9, $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel."&inicial=$inicial", "inicial_v"); ?>
					<?php } ?>
				</table>
			</div>

		</td>
	</tr>
</table>

<table class="table">
	<tr bgcolor="#FFFFFF" class="texto">
		<td bgcolor="#ECE9D8">Histórico</td>
	</tr>
	<tr bgcolor="#FFFFFF" class="texto">
		<td>
			<?php
			$varsel = "&usuario_id=" . $usuario_id;
			if (!isset($ncamp) || !$ncamp)
				$ncamp = "ugl_data_inclusao";
			if (!isset($ordem) || !$ordem)
				$ordem = 1;
			if (!isset($inicial) || !$inicial)
				$inicial = 0;
			//if(b_IsUsuarioReinaldo()) { 
//echo "<font color='#FF0000'>inicial: $inicial</font><br>";
//echo "varsel: $varsel<br>";
//}
			$sql = "select * from dist_usuarios_games_log ugl " .
				"where ugl.ugl_ug_id = " . $usuario_id;
			//$rs_usuario_log = SQLexecuteQuery($sql);
			//$total_table = pg_num_rows($rs_usuario_log);
			$sql .= " order by " . $ncamp . " " . ($ordem == 1 ? "desc" : "asc");
			//$sql .= " limit " . $max . " offset " . $inicial;
			if ($usuario_id != 16467 && $usuario_id != 10480 && $usuario_id != 5764 && $usuario_id != 6623) {
				$sql .= " limit 500;";
			}
			//echo "$sql<br>";
			$rs_usuario_log = SQLexecuteQuery($sql);
			$total_table = pg_num_rows($rs_usuario_log);
			//echo "varsel: $varsel<br>";
			?>
			<div id="Layer1" class="" style="position:static; width:100%; height:200px; z-index:1; overflow: auto;">
				<table border='0' width="100%" cellpadding="0" cellspacing="01" class="texto" bgcolor="ffffff">
					<?php $ordem = ($ordem == 1) ? 2 : 1; ?>
					<tr bgcolor="#ECE9D8">
						<td align="center"><a style="text-decoration:none"
								href="<?php echo $default_add . "?ordem=" . $ordem . "&ncamp=ugl_data_inclusao&inicial=" . $inicial . $varsel ?>"
								class="link_branco">Data</a></td>
						<td align="left"><a style="text-decoration:none"
								href="<?php echo $default_add . "?ordem=" . $ordem . "&ncamp=ugl_uglt_id&inicial=" . $inicial . $varsel ?>"
								class="link_branco">Tipo</a> (ord. pelo código)</td>
						<td align="center"><a style="text-decoration:none"
								href="<?php echo $default_add . "?ordem=" . $ordem . "&ncamp=ugl_vg_id&inicial=" . $inicial . $varsel ?>"
								class="link_branco">Pedido</a></td>
						<td align="center"><a style="text-decoration:none"
								href="<?php echo $default_add . "?ordem=" . $ordem . "&ncamp=ugl_ip&inicial=" . $inicial . $varsel ?>"
								class="link_branco">IP</a></td>
						<td align="center"><a style="text-decoration:none" class="link_branco">Obs</a></td>
					</tr>
					<?php if (!$rs_usuario_log || pg_num_rows($rs_usuario_log) == 0) { ?>
						<tr>
							<td align="center" colspan="4">Nenhum histórico encontrado</td>
						</tr>
					<?php } else { ?>
						<?php while ($rs_usuario_log_row = pg_fetch_array($rs_usuario_log)) {
							if ($cor1 == $cor2) {
								$cor1 = $cor3;
							} else {
								$cor1 = $cor2;
							} ?>
							<tr bgcolor="<?php echo $cor1 ?>">
								<td align="center">
									<?php echo formata_data_ts($rs_usuario_log_row['ugl_data_inclusao'], 0, true, true) ?>
								</td>
								<?php $ugl_uglt_id = $rs_usuario_log_row['ugl_uglt_id']; ?>
								<td align="left"><?php echo $GLOBALS['USUARIO_GAMES_LOG_TIPOS_DESCRICAO'][$ugl_uglt_id] ?></td>
								<td align="center"><a style="text-decoration:none"
										href="/pdv/vendas/com_venda_detalhe.php?venda_id=<?php echo $rs_usuario_log_row['ugl_vg_id'] ?>"><?php echo $rs_usuario_log_row['ugl_vg_id'] ?></a>
								</td>
								<td align="center"><?php echo $rs_usuario_log_row['ugl_ip'] ?></td>
								<td align="center"><?php echo $rs_usuario_log_row['ugl_obs'] ?></td>
							</tr>
						<?php } ?>
						<?php //paginacao_query($inicial, $total_table, $max, 4, $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel."&inicial_v=".$inicial_v); ?>
					<?php } ?>
				</table>
			</div>

		</td>
	</tr>
</table>

<br>
</td>
</tr>

</div>
</div>
<table class="txt-preto table fontsize-pp">
	<tr>
		<td>
			<table width="100%" border="0" cellpadding="0" cellspacing="0" class="table">
				<tr bgcolor="#FFFFFF">
					<td width="100%" bgcolor="#ECE9D8" align="center"><b>Desconto Default por Forma de Pagamento</b>
					</td>
				</tr>
				<tr bgcolor="#FFFFFF">
					<td valign="top">

						<table width="100%" border="0" cellpadding="0" cellspacing="0" class="table">
							<tr>
								<?php
								//Recupera desconto por forma de pagamento
								foreach ($GLOBALS['FORMAS_PAGAMENTO_DESCRICAO'] as $formaId => $formaNome) {
									$formaId_num = (($formaId == $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_BANCO_ITAU_ONLINE']) ? $GLOBALS['PAGAMENTO_BANCO_ITAU_ONLINE_NUMERIC'] : (($formaId == $GLOBALS['FORMAS_PAGAMENTO']['PAGAMENTO_PIX']) ? $GLOBALS['PAGAMENTO_PIX_NUMERIC'] : $formaId));
									$sql = "select opr.opr_codigo, opr.opr_nome, des.*
									 from operadoras opr
									 left join tb_dist_descontos des on opr.opr_codigo = des.des_opr_codigo
									 	and des.des_opr_codigo <> 0 and des.des_vg_pagto_tipo = $formaId_num and des.des_ug_id = $usuario_id
									 where opr.opr_status = '1'
									 order by opr.opr_nome";
									//echo $sql."<br>";
//echo "formaId: ".$formaId." - formaId_num: ".$formaId_num."<br>";
								
									$rs_porFormaPagto = SQLexecuteQuery($sql);
									?>
									<td valign="top">
										<table class="table">
											<tr bgcolor="#ECE9D8" class="texto">
												<td colspan="3" align="center"><b><?php echo $formaNome ?></b></td>
											</tr>
											<tr bgcolor="#F5F5FB" class="texto">
												<td align="center"><b>Operadora</b></td>
												<td align="center"><b>Desconto</b></td>
												<td align="center"><img src="../../images/deletar.gif"></td>
											</tr>
											<?php
											if ($rs_porFormaPagto)
												while ($rs_porFormaPagto_row = pg_fetch_array($rs_porFormaPagto)) {
													$des_perc_desconto = $rs_porFormaPagto_row['des_perc_desconto'];
													?>
													<tr class="texto" bgcolor="#F5F5FB">
														<td>&nbsp;<nobr><?php echo $rs_porFormaPagto_row['opr_nome'] ?></nobr>
														</td>
														<td align="right">
															<?php if (!is_null($des_perc_desconto)) { ?>
																<a class="link_azul" href="#"
																	Onclick="window.open('com_desconto_selecao.php?opr_nome=<?php echo urlencode($rs_porFormaPagto_row['opr_nome']) ?>&des_opr_codigo=<?php echo $rs_porFormaPagto_row['opr_codigo'] ?>&perc_desconto=<?php echo urlencode(number_format($des_perc_desconto, 2, ',', '.')) ?>&des_vg_pagto_tipo=<?php echo $formaId ?>&des_id=<?php echo $rs_porFormaPagto_row['des_id'] ?>&usuario_id=<?php echo $usuario_id ?>','selecao', 'status=0,width=500,height=200,top=0,left=0');return false;"><?php echo number_format($des_perc_desconto, 2, ',', '.') ?>%</a>&nbsp;
															<?php } else { ?>
																<a class="link_azul" href="#"
																	Onclick="window.open('com_desconto_selecao.php?opr_nome=<?php echo urlencode($rs_porFormaPagto_row['opr_nome']) ?>&des_opr_codigo=<?php echo $rs_porFormaPagto_row['opr_codigo'] ?>&perc_desconto=<?php echo urlencode("0,00") ?>&des_vg_pagto_tipo=<?php echo $formaId ?>&des_id=0&usuario_id=<?php echo $usuario_id ?>','selecao', 'status=0,width=500,height=200,top=0,left=0');return false;">Inserir</a>&nbsp;
															<?php } ?>
														</td>
														<td align="center">
															<?php if (!is_null($des_perc_desconto)) { ?>
																<a class="link_azul"
																	Onclick="return confirm('Deseja excluir este desconto ?');"
																	href="?acao=e&des_id=<?php echo $rs_porFormaPagto_row['des_id'] ?><?php echo $varsel ?>"
																	title="Excluir desconto"><img src="../../images/deletar.gif"
																		border="0"></a>
															<?php } else { ?>
																&nbsp;
															<?php } ?>
														</td>
													</tr>
												<?php } ?>
										</table>
									</td>
								<?php } ?>

							</tr>
						</table>

					</td>
				</tr>
			</table>

		</td>
	</tr>
</table>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
	$("#btn-send-key").click(function () {
		//alert("Em desenvolvimento");
		$.ajax({
			url: "/pdv/usuarios/ajaxReenvioChave.php",
			method: "POST",
			data: { id: $("#codigo-pdv").html() },
			beforeSend: function () {
				Swal.fire({
					didOpen: () => {
						Swal.showLoading()
					}
				})
			},
			complete: function (request, data) {
				Swal.close();
				if (data == "success") {
					let response = JSON.parse(request.responseText);
					Swal.fire(
						'Processo finalizado',
						response.msg,
						'success'
					)
				} else {
					let response = JSON.parse(request.responseText);
					Swal.fire(
						'Processo finalizado',
						response.msg,
						'error'
					)
				}
			}
		});
	});
	$("#btn-remove-auth").click(function () {
		Swal.fire({
			title: "Tem certeza?",
			text: "Esta ação removerá o autenticador do usuário!",
			icon: "warning",
			showCancelButton: true,
			confirmButtonText: "Sim, remover",
			cancelButtonText: "Cancelar"
		}).then((result) => {
			if (result.isConfirmed) {
				$.ajax({
					url: "/pdv/usuarios/ajaxRemoverAutenticador.php",
					method: "POST",
					data: { id: $("#codigo-pdv").html(), codigo: 'Gz8#kV2!mP$Xr9@tQw' },
					beforeSend: function () {
						Swal.fire({
							didOpen: () => {
								Swal.showLoading()
							}
						});
					},
					complete: function (request, status) {
						Swal.close();
						let response = JSON.parse(request.responseText);

						if (response.situacao == 'success') {
							Swal.fire(
								"Sucesso!",
								response.msg,
								"success"
							).then(() => {
								$("#btn-remove-auth").hide();
								$("#txt-auth").html("Não");
							});
						} else {
							Swal.fire(
								"Erro!",
								response.msg,
								"error"
							);
						}
					}
				});
			}
		});
	});
	$("#btn-bloqueio").click(function () {
		let acao = $("#btn-bloqueio").hasClass("act-add") ? "add" : "rm";

		let msg = acao == "add" ? "Esta ação bloqueará o usuário!" : "Esta ação desbloqueará o usuário!";
		Swal.fire({
			title: "Tem certeza?",
			text: msg,
			icon: "warning",
			showCancelButton: true,
			confirmButtonText: "Sim! Confirmar",
			cancelButtonText: "Cancelar"
		}).then((result) => {
			if (result.isConfirmed) {
				$.ajax({
					url: "/pdv/usuarios/ajax_usuario_fraude.php",
					method: "POST",
					data: { ug_id: $("#codigo-pdv").html(), codigo: 'Gz8#kV2!mP$Xr9@tQw', acao: acao },
					beforeSend: function () {
						Swal.fire({
							didOpen: () => {
								Swal.showLoading()
							}
						});
					},
					complete: function (request, status) {
						Swal.close();
						let response = JSON.parse(request.responseText);

						if (response.status == 'success') {
							Swal.fire(
								"Sucesso!",
								response.message,
								"success"
							).then(() => {
								window.location.reload();
							});
						} else {
							Swal.fire(
								"Erro!",
								response.message,
								"error"
							);
						}
					}
				});
			}
		});
	});

</script>
<?php
require_once $raiz_do_projeto . "backoffice/includes/rodape_bko.php";
?>

<style>
	html {
		overflow-x: scroll !important;
	}
</style>

</html>