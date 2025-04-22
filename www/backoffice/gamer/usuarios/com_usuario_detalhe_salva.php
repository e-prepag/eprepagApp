<?php 
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/gamer/main.php";
require_once "/www/includes/bourls.php";

$grupos = unserialize($_SESSION["arrIdGrupos"]);
?>
<link href="<?php echo $url; ?>:<?php echo $server_port ;?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port ;?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port ;?>/js/jquery.mask.min.js"></script>
<script src="<?php echo $url; ?>:<?php echo $server_port ;?>/js/formataNome.js"></script>
<script language="javascript">

//função para verificar se o objeto DOM do javascript está pronto.
$(document).ready(function(){
        

    $("#novo_ug_data_nascimento").datepicker({
        dateFormat: "dd/mm/yy",
        maxDate: "dateToday"
    });
        
    $("#cpf").mask("999.999.999-99");
    
	var searching = false;
	//Função para impedir que o usuário digite além de números no campo CEP.
	$("#novo_ug_cep").keypress(function(event) {

		var varTecla = event.charCode || event.keyCode;

<?php
	// http://www.aspdotnetfaq.com/Faq/What-is-the-list-of-KeyCodes-for-JavaScript-KeyDown-KeyPress-and-KeyUp-events.aspx
	// Permite números, Backspace, Tab, Enter, End, Home, Left, Right, Del
?>			
		if (((varTecla>47) && (varTecla<58)) || (varTecla==8) || (varTecla==9) || (varTecla==13) || (varTecla==35) || (varTecla==36) || (varTecla==37) || (varTecla==39) || (varTecla==46)) {
			return true;
		} else {
			return false;
		}
	});
	
	//Função para buscar o endereço.
	$("#novo_ug_cep").keyup(function() {
		cep = this.value;
		if (cep.length == 8 && !searching) {
			$.ajax({
				type: "POST",
				url: "/includes/cep.php",
				data: "cep=" + cep,
				beforeSend: function() {
					searching = true;
					$("#info_cep").html("<b>Aguarde... Procurando CEP.</b>");
				},
				success: function(txt) {
					searching = false;
					$("#info_cep").html("");

                    if (txt.indexOf("NO_ACCESS") < 0){
                        var msg = 'Você gostaria de trocar o endereço abaixo:\n';
						msg += 'Endereço: ' + document.getElementById("novo_ug_tipo_end").value + ' ' + document.getElementById("novo_ug_endereco").value + '\n';
						msg += document.getElementById("novo_ug_bairro").value + ' - ' + document.getElementById("novo_ug_cidade").value + ' - ' + document.getElementById("novo_ug_estado").value + '\n\n';
						msg += 'por este novo endereço?\n';
                                
                        if (txt.indexOf("ERRO") < 0){
                            txt = txt.split("&");

                                msg += 'Endereço: ' + txt[0].trim() + ' ' + txt[1] + '\n';
                                msg += txt[2] + ' - ' + txt[3] + ' - ' + txt[4];

                                if(confirm(msg)){

                                    document.getElementById("novo_ug_tipo_end").value = "";
                                    document.getElementById("novo_ug_endereco").value = txt[0].trim()+' '+txt[1].trim();
                                    document.getElementById("novo_ug_bairro").value = txt[2].trim();
                                    document.getElementById("novo_ug_cidade").value = txt[3].trim();
                                    document.getElementById("novo_ug_estado").value = txt[4].trim();
                                    document.getElementById("novo_ug_numero").focus();
                                    
                                } else{
                                    document.getElementById("novo_ug_cep").value = "";
                                }
                        }
                        else{
                            funcZerar();
                            document.getElementById("novo_ug_cep").value = "";
                            alert("CEP Inexistente!");
                        }
                    }
                    else{
                        funcZerar();
                        document.getElementById("novo_ug_cep").value = "";
                        alert("[ERRO 404] - Consulta de CEP indisponível no momento. Tente novamente mais tarde.");
                    }
				},
				error: function() {
					$("#info_cep").html("");
					funcZerar();
					alert("[ERRO 400] - Erro no servidor, por favor tente mais tarde.");
				}
			});
		} 
	});
    
	function funcZerar() {
		document.getElementById("novo_ug_tipo_end").value = "Tipo";
		document.getElementById("novo_ug_endereco").value = "";
		document.getElementById("novo_ug_bairro").value = "";
		document.getElementById("novo_ug_cidade").value = "";
		document.getElementById("novo_ug_estado").value = "Estado";
		document.getElementById("novo_ug_cep").focus();
	}
});

var searching = false;
function pegaNomeRF(){
        
    if($("#cpf").val().trim().length === 14 && $("#novo_ug_data_nascimento").val().trim().length === 10 && !searching){
        $.ajax({
            type: "POST",
            url: "/ajax/ajaxCpf.php",
            dataType : "json",
            data: { cpf : $("#cpf").val(), dataNascimento : $("#novo_ug_data_nascimento").val()},
            beforeSend: function(){
                searching = true;
                $(".loading").html("<img src='https://www.e-prepag.com.br/imagens/ajax-loader.gif' width='30' height='30' title='Consultando...'>");
            },
            success: function(txt){
                searching = false;
                if(txt.erros.length > 0){
                    alert(txt.erros);
                    $(".loading").attr("style", "vertical-align:8px");
                    $(".loading").addClass("glyphicon glyphicon-remove txt-vermelho");
                } else{
                    $("#consultou_rf").val("1");
                    $("#cpf").attr("readonly", "readonly");
                    $("#novo_ug_data_nascimento").datepicker("destroy");
                    $("#novo_ug_data_nascimento").attr("readonly", "readonly");
                    
                    $("#consulta_rf").attr("disabled", "disabled");
                    var nome_cpf = fix_name_js(txt.nome.substr(0, 480));
                    $("#ug_nome_cpf").val(nome_cpf);
                    
                    $(".loading").attr("style", "vertical-align:8px");
                    $(".loading").addClass("glyphicon glyphicon-ok txt-verde");
                }
                $(".loading").html("");
                
            },
            error: function(x,y){
                $(".loading").addClass("hidden");
                return false;
            }
        });
    } else{
        alert("Antes de clicar em 'Consultar CPF', preencha corretamente os campos CPF e Data de Nascimento");
        return false;
    }
}

function fcnSalvarCadastro(cod) {
		
    form1.action = '?acao=sto&usuario_id='+cod;
    form1.submit();	
}

function fcnVoltar(cod) {
    form1.action = 'com_usuario_detalhe.php?usuario_id='+cod;
    form1.submit();	
}

function mudarSelect() {
	var x = document.getElementById('novo_ug_substatus');
	var y = '';
	var ativos = new Array(2);
	ativos[0] = 'Selecione o Substatus'; ativos[1] = 'Ainda não fez 1º compra';
	var inativos = new Array(2);
	inativos[0] = 'Selecione o Substatus'; inativos[1] = 'Pendente de Contato e Análise'; inativos[2] = 'Retornar Contato'; inativos[3] = 'Dados Insuficientes'; inativos[4] = 'Cadastro não Aprovado'; inativos[5] = 'Sem Interesse'; inativos[6] = 'Não quer mais vender'; inativos[7] = 'Bloqueado por fraude'; inativos[8] = 'Pré-Cadastro/Prospecção';
	
	for(var i=x.length-1;i>=0;i--)
		x.remove(x[i]);
	
	if (document.getElementById('novo_ug_ativo').selectedIndex == 2) {
		y = document.createElement('option'); y.text = ativos[0]; y.value = '';
		try { x.add(y,null); } catch(ex) { x.add(y); }
		y = document.createElement('option'); y.text = ativos[1]; y.value = '9';
		try { x.add(y,null); } catch(ex) { x.add(y); }
	} else if (document.getElementById('novo_ug_ativo').selectedIndex == 1) {
		for(i=0;i<inativos.length;i++) {
			y = document.createElement('option'); y.text = inativos[i]; y.value = i;
			try { x.add(y,null); } catch(ex) { x.add(y); }
		}
	} else {
		for(i=0;i<inativos.length;i++) {
			y = document.createElement('option'); y.text = inativos[i]; y.value = i;
			try { x.add(y,null); } catch(ex) { x.add(y); }
		}
		var y = document.createElement('option'); y.text = ativos[1]; y.value = '9';
		try { x.add(y,null); } catch(ex) { x.add(y); }
	}
}
</script>

<?php
	$msg = "";
	$msgAcao = "";

	if(!$usuario_id) $msg = "Código do usuário não fornecido.\n";
	elseif(!is_numeric($usuario_id)) $msg = "Código do usuário inválido.\n";

//echo "op: '$op'<br>";
    $usuariogames = new UsuarioGames();
	//Processa Acoes
	if($msg == ""){
		//Alterar Dados do Estabelecimento
		if($op && $op == "sto"){
		
			if($msgAcao == ""){
                if(!empty($GLOBALS['_SESSION']['userlogin_bko'])) {
                    $cad_usuarioGames = new UsuarioGames($usuario_id);
                    
                    $cad_usuarioGames->setAtivo($novo_ug_ativo);
                    $cad_usuarioGames->setEmail($novo_ug_email);
                    $cad_usuarioGames->setEndereco($novo_ug_endereco);
                    $cad_usuarioGames->setTipoEnd($novo_ug_tipo_end);
                    $cad_usuarioGames->setNumero($novo_ug_numero);
                    $cad_usuarioGames->setComplemento($novo_ug_complemento);
                    $cad_usuarioGames->setBairro($novo_ug_bairro);
                    $cad_usuarioGames->setCidade($novo_ug_cidade);
                    $cad_usuarioGames->setEstado($novo_ug_estado);
                    $cad_usuarioGames->setCEP($novo_ug_cep);
                    $cad_usuarioGames->setTelDDI($novo_ug_tel_ddi);
                    $cad_usuarioGames->setTelDDD($novo_ug_tel_ddd);
                    $cad_usuarioGames->setTel($novo_ug_tel);
                    $cad_usuarioGames->setCelDDI($novo_ug_cel_ddi);
                    $cad_usuarioGames->setCelDDD($novo_ug_cel_ddd);
                    $cad_usuarioGames->setCel($novo_ug_cel);
                    $cad_usuarioGames->setNome($novo_ug_nome);
                    $cad_usuarioGames->setRG($novo_ug_rg);
                    $cad_usuarioGames->setCPF($novo_ug_cpf);
                    if($novo_ug_login)
                        $cad_usuarioGames->setLogin($novo_ug_login);
                    $cad_usuarioGames->setDataNascimento($novo_ug_data_nascimento);
                    $cad_usuarioGames->setSexo($novo_ug_sexo);
                    $cad_usuarioGames->setNewsLetter($novo_ug_news);
                    $cad_usuarioGames->setUseCielo($novo_ug_use_cielo);
                    $cad_usuarioGames->setHabboId($novo_ug_habbo_id);
                    $cad_usuarioGames->setNomeCPF($ug_nome_cpf);
                    $cad_usuarioGames->setOBS($novo_ug_obs);
                    $cad_usuarioGames->setNomedaMae($novo_ug_nome_da_mae);
                    
                    if($consultou_rf == '1'){
                        $sql_atualiza_data_cpf_informado = "UPDATE usuarios_games set ug_data_cpf_informado = NOW() where ug_id = ".SQLaddFields($usuario_id, "").";";
                        $result = SQLexecuteQuery($sql_atualiza_data_cpf_informado);
                        if(!$result){
                            $msgAcao = "Erro ao atualizar usuário. Problema ao atualizar a data em que o CPF foi consultado na Receita.";
                        }
                    } else{
                        $sql_verifica = "SELECT ug_cpf, ug_data_nascimento from usuarios_games WHERE ug_id = ".SQLaddFields($usuario_id, "").";";
                        $result_verifica = SQLexecuteQuery($sql_verifica);
                        if(!$result_verifica){
                            $msgAcao = "Erro ao atualizar usuário. Problema com CPF e a data de nascimento do usuário.";
                        } else{
                            $retorno = pg_fetch_array($result_verifica);
                        }
                        
                        if(isset($retorno)){
                            if((preg_replace('/[^0-9]/', '', $retorno['ug_cpf']) !== preg_replace('/[^0-9]/', '', $cad_usuarioGames->getCPF())) || 
                            (substr($retorno['ug_data_nascimento'], 0, 10) !== formata_data($cad_usuarioGames->getDataNascimento(),1)))
                            {
                                $sql_atualiza_data_cpf_informado = "UPDATE usuarios_games set ug_data_cpf_informado = NULL where ug_id = ".SQLaddFields($usuario_id, "").";";
                                $result = SQLexecuteQuery($sql_atualiza_data_cpf_informado);
                                if(!$result){
                                    $msgAcao = "Erro ao atualizar usuário. Problema ao anular a data em que o CPF foi consultado na Receita.";
                                }
                            }
                        }
                    }
                                
                    if($msgAcao == ""){
                        $msgAcao = $usuariogames->atualizar_sem_validar($cad_usuarioGames, true, ((in_array(44, $grupos))? true: false));
                        usuarios_games_log($GLOBALS['USUARIO_GAMES_LOG_TIPOS']['ALTERACAO_DO_CADASTRO'], $usuario_id, null, "Mod. por usuario bko id: " . $_SESSION['iduser_bko']);
                    }//end if($msgAcao == "")
                }//end if(!empty($GLOBALS['_SESSION']['userlogin_bko']))
                else {
                    $msgAcao = "É necessário estar Logado no BackOffice para executar alteração.";
                }//end else do if(!empty($GLOBALS['_SESSION']['userlogin_bko']))
			}//end if($msgAcao == ""
		}//end if($op && $op == "sto")
	}//end if($msg == "")

	//Recupera dados do usuario
	if($msg == ""){
		$objUsuarioGames = $usuariogames->getUsuarioGamesById($usuario_id);
		if($objUsuarioGames == null) $msg = "Nenhum usuário encontrado.\n";
	}//end if($msg == "")

	$msg .= $msgAcao;


	$cor1 = "#F5F5FB";
	$cor2 = "#F5F5FB";
	$cor3 = "#FFFFFF"; 	

ob_end_flush();
?>
<script language="javascript">
function GP_popupAlertMsg(msg) { //v1.0
  document.MM_returnValue = alert(msg);
}
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
</script>

<script language="javascript">

function trimAll(sString) {
	while (sString.substring(0,1) == ' ')
		sString = sString.substring(1, sString.length);
	while (sString.substring(sString.length-1, sString.length) == ' ')
		sString = sString.substring(0,sString.length-1);

	return sString;
}

</script>

<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>

<form name="form1" method="post" action="com_usuario_detalhe_salva.php">
 	<input type="hidden" name="v_campo" value="">
 	<input type="hidden" name="v_valor_old" value="">
 	<input type="hidden" name="v_valor_new" value="">
 	<input type="hidden" name="op" value="sto">
    <input type="hidden" name="consultou_rf" id="consultou_rf" value="0">

<table class="table">
  <tr> 
    <td valign="top">
    <?php if($msg != ""){?>
        <table>
          <tr><td align="center" class="texto"><font color="#FF0000"><?php echo str_replace("\n", "<br>", $msg) ?></font></td></tr>
		</table>
	<?php } elseif($msg == "" && $op == "sto") {?>
        <table>
          <tr><td align="center" class="texto"><font color="blue">Atualizado com sucesso!!!</font></td></tr>
		</table>
	<?php }?>
		
        <table class="table txt-preto fontsize-pp">
          <tr bgcolor="#FFFFFF" class="texto"> 
            <td align="left"><input type="button" value="Voltar" Onclick="fcnVoltar(<?php echo $usuario_id ?>)" class="btn btn-sm btn-info"></font></td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td align="right"><input type="button" value="Salvar Cadastro" Onclick="fcnSalvarCadastro(<?php echo $usuario_id ?>)" class="btn btn-sm btn-info"></font></td>
          </tr>
          <tr bgcolor="#FFFFFF" class="texto"> 
            <td colspan="4" bgcolor="#ECE9D8">Perfil</font></td>
          </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td><b>Saldo atual</b></td>
            <td><?php echo number_format($objUsuarioGames->getPerfilSaldo(), 2, ',','.') ?></td>
			<td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          <tr bgcolor="#FFFFFF" class="texto"> 
            <td colspan="4" bgcolor="#ECE9D8">Dados Administrativos</font></td>
          </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td><b>C&oacute;digo</b></td>
            <td><?php echo $objUsuarioGames->getId() ?></td>
            <td><b>Status</b></td>
            <td>
				<select name="novo_ug_ativo" class="texto"> <?php // onChange="javascript:mudarSelect();"> ?>
					<option value="">Selecione o Status</option>
	                                <?php
                                        foreach($GLOBALS['STATUS_USUARIO'] as $key => $val) {
                                        ?>
                                                <option value='<?php echo $val ?>'<?php echo (($objUsuarioGames->getAtivo()==$val)?" selected":"") ?>><?php echo $GLOBALS['STATUS_USUARIO_LEGENDA'][$val]?></option>
                                        <?php
                                        } //end foreach
                                        ?>
				</select>
			</td>
          </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
		    <?php if(in_array(44, $grupos)){ ?>
				<td><b>Email</b></td>
				<td><?php //echo $objUsuarioGames->getEmail() ?><input type="text" name="novo_ug_email" value="<?php echo $objUsuarioGames->getEmail() ?>"></td>
			<?php }else{ ?>
			    <td></td>
                <td></td>
            <?php } ?>
            <td><b>Newsletter</b></td>
            <td>
				<select name="novo_ug_news" id="novo_ug_news" class="texto">
					<option value="h" <?php if ($objUsuarioGames->getNewsLetter() == 'h') echo "selected";?>>Sim - HTML</option>
					<option value="t" <?php if ($objUsuarioGames->getNewsLetter() == 't') echo "selected";?>>Sim - Texto</option>
					<option value="n" <?php if ($objUsuarioGames->getNewsLetter() == 'n') echo "selected";?>>N&atilde;o</option>
				</select>
			</td>
		  </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td><b>Data de Cadastro</b></td>
            <td><?php echo $objUsuarioGames->getDataInclusao() ?></td>
			<td><b>Cielo</b></td>
            <td>
				<select name="novo_ug_use_cielo" id="novo_ug_use_cielo" class="texto">
					<option value="1" <?php if ($objUsuarioGames->getUseCielo() == '1') echo "selected";?>>Ativo</option>
					<option value="0" <?php if ($objUsuarioGames->getUseCielo() == '0') echo "selected";?>>Inativo</option>
				</select>
			</td>
          </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td><b>Qtde de Acessos</b></td>
            <td><?php echo $objUsuarioGames->getQtdeAcessos() ?></td>
            <td><b>Data Último Acesso</b></td>
            <td><?php echo $objUsuarioGames->getDataUltimoAcesso() ?></td>
          </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td><b>Login</b></td>
            <td><input type="text" name="novo_ug_login" value="<?php echo $objUsuarioGames->getLogin() ?>"></td>
            <td colspan="2"></td>
          </tr>

		  <tr bgcolor="#FFFFFF" class="texto"> 
            <td colspan="2" bgcolor="#ECE9D8">Cadastro</font></td>
            <td colspan="2" bgcolor="#ECE9D8" class="txt-vermelho"><strong>CUIDADO!! Campo de Compliance</strong></td>
          </tr>
	
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td><b>Nome</b></td>
            <td><input type="text" name="novo_ug_nome" value="<?php echo $objUsuarioGames->getNome() ?>" maxlength="50" size="50" class="texto"></td>
            <td class="txt-vermelho"><b>Nome CPF</b></td>
            <td><input type="text" name="ug_nome_cpf" id="ug_nome_cpf" readonly="readonly" value="<?php echo $objUsuarioGames->getNomeCPF(); ?>" title="Para editar o nome, consulte o CPF na Receita" maxlength="100" size="50" class="texto bloqueio"></td></td>
          </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td><b>CPF</b></td>
            <td><input type="text" name="novo_ug_cpf" id="cpf" value="<?php echo $objUsuarioGames->getCPF() ?>" maxlength="14" size="20" class="texto bloqueio"></td>
            <td><b>Data de Nascimento</b></td>
            <td><input name="novo_ug_data_nascimento" type="text" class="form bloqueio" id="novo_ug_data_nascimento" value="<?php echo substr($objUsuarioGames->getDataNascimento(), 0, 10) ?>" size="9" maxlength="10"><button type="button" id="consulta_rf" class="btn btn-info btn-xs" onclick="pegaNomeRF();" title="Clique aqui para verificar o CPF junto a RF">Consultar CPF</button>&nbsp;&nbsp;&nbsp;<span class="loading"></span>
			</td>
		  </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td><b>RG</b></td>
            <td><input type="text" name="novo_ug_rg" value="<?php echo $objUsuarioGames->getRG() ?>" maxlength="20" size="20" class="texto"></td>
            <td><b>Sexo</b></td>
            <td>
				<select name="novo_ug_sexo" class="texto">
					<option value="">Selecione o Sexo</option>
					<option value="F" <?php if ($objUsuarioGames->getSexo() == "F") echo "selected";?>>Feminino</option>
					<option value="M" <?php if ($objUsuarioGames->getSexo() == "M") echo "selected";?>>Masculino</option>
				</select>
			</td>
          </tr>
        <tr bgcolor="#F5F5FB" class="texto"> 
                <td><b>Nome da Mãe do Usuário</b></td>
                <td><input type="text" name="novo_ug_nome_da_mae" value="<?php echo $objUsuarioGames->getNomedaMae() ?>" maxlength="600" size="50" class="texto"></td>
                <td></td>
                <td></td>
        </tr>
<?php 
	
		$trava = true;
	
		if ($trava) {
			
			require_once "../../../class/gamer/classUsuarioVip.php";
			
			$iduser_bko = $_SESSION['iduser_bko'];
			$userlogin_bko = $_SESSION['userlogin_bko'];
			
			$ug_id = $objUsuarioGames->getId();
			
			$dadosStatusUsuario = new UsuarioVip();
			$status = $dadosStatusUsuario->getStatusVip($ug_id);
			$dataInclusao = $dadosStatusUsuario->getDataInclusao($ug_id);
			$nomeOperador = $dadosStatusUsuario->getNomeOperador($ug_id);
			
?>
			<tr bgcolor="#F5F5FB" class="texto"> 
			<?php
				if ($status) {
			?>
					<td class="txt-vermelho"><b>O usuário está cadastrado como VIP</b></td>
					<td><button id="btnSetGamerVip" type="submit" class="btn btn-sm btn-info" name="ug_id" disabled>--</button></td>
					<td>Cadastrado por: <?php echo $nomeOperador; ?></td>
					<td>Na data: <?php echo $dataInclusao; ?></td>
			<?php
				} else {
			?>
					<td class="txt-vermelho"><b>Usuário não está cadastrado como VIP</b></td>
					<td>
						<form method="post">
							<input type="hidden" id="op_id" name="op_id" value="<?php echo $iduser_bko; ?>">
							<input type="hidden" id="op_nome" name="op_nome" value="<?php echo $userlogin_bko; ?>">
						
							<input type="hidden" id="ug_id" name="ug_id" value="<?php echo $ug_id; ?>">
							<button id="btnSetGamerVip" type="submit" class="btn btn-sm btn-info" name="ug_id">Cadastrar na categoria VIP</button>
						</form>
					</td>
					<td></td>
					<td></td>
						<script>
							$('#btnSetGamerVip').click(()=> {
									
								var ug_id = $('#ug_id').val();
								var op_id = $('#op_id').val();
								var op_nome = $('#op_nome').val();
									
								$.ajax({
									url: '../../ajax/gamer/ajaxUsuarioVip.php',
									type: 'POST',
									data: {
											ug_id: ug_id,
											op_id: op_id,
											op_nome: op_nome
										},
									success: () => {
										alert('O usuário VIP foi adicionado com sucesso');
										window.location.href = "https://<?php echo $server_url_complete ;?>/gamer/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $ug_id; ?>";
									},
									error: (jqXHR, textStatus, errorThrown) => {
										alert('Ocorreu um erro ao processar a solicitação: ' + textStatus + ' ' + errorThrown);
										window.location.reload();
									}
								});
							});
						</script>
			<?php
				}
			?>
			</tr>
<?php
		}
?>

        <tr bgcolor="#FFFFFF" class="texto"> 
            <td colspan="4" bgcolor="#ECE9D8">Endereço</font></td>
          </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
          	<td><b>CEP</b></td>
            <td><input type="text" id="novo_ug_cep" name="novo_ug_cep" value="<?php  echo $objUsuarioGames->getCEP() ?>" maxlength="8" size="8" class="texto">&nbsp;<div id="info_cep"></div></td>
            <td><b>Tipo de Endereço</b></td>
            <td><input type="text" id="novo_ug_tipo_end" name="novo_ug_tipo_end" value="<?php  echo $objUsuarioGames->getTipoEnd() ?>" maxlength="30" size="15" class="texto"></td>
          </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td><b>Endereço</b></td>
            <td colspan="3"><input type="text" id="novo_ug_endereco" name="novo_ug_endereco" value="<?php  echo $objUsuarioGames->getEndereco() ?>" maxlength="100" size="50" class="texto"></td>
          </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td><b>Número</b></td>
            <td><input type="text" id="novo_ug_numero" name="novo_ug_numero" value="<?php  echo $objUsuarioGames->getNumero() ?>" maxlength="10" size="10" class="texto"></td>
            <td><b>Complemento</b></td>
            <td><input type="text" id="novo_ug_complemento" name="novo_ug_complemento" value="<?php  echo $objUsuarioGames->getComplemento() ?>" maxlength="100" size="50" class="texto"></td>
		  </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td><b>Bairro</b></td>
            <td><input type="text" id="novo_ug_bairro" name="novo_ug_bairro" value="<?php  echo $objUsuarioGames->getBairro() ?>" maxlength="100" size="50" class="texto"></td>
            <td><b>Cidade</b></td>
            <td><input type="text" id="novo_ug_cidade" name="novo_ug_cidade" value="<?php  echo $objUsuarioGames->getCidade() ?>" maxlength="100" size="50" class="texto"></td>
		  </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td><b>Estado</b></td>
            <td><input type="text" id="novo_ug_estado" name="novo_ug_estado" value="<?php  echo $objUsuarioGames->getEstado() ?>" maxlength="2" size="2" class="texto"></td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
		  </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td><b>Telefone</b></td>
            <td>(<input type="text" name="novo_ug_tel_ddi" value="<?php echo $objUsuarioGames->getTelDDI() ?>" maxlength="2" size="2" class="texto">) (<input type="text" name="novo_ug_tel_ddd" value="<?php echo $objUsuarioGames->getTelDDD() ?>" maxlength="2" size="2" class="texto">) <input type="text" name="novo_ug_tel" value="<?php echo $objUsuarioGames->getTel() ?>" maxlength="9" size="9" class="texto"></td>
            <td><b>Celular</b></td>
            <td>(<input type="text" name="novo_ug_cel_ddi" value="<?php echo $objUsuarioGames->getCelDDI() ?>" maxlength="2" size="2" class="texto">) (<input type="text" name="novo_ug_cel_ddd" value="<?php echo $objUsuarioGames->getCelDDD() ?>" maxlength="2" size="2" class="texto">) <input type="text" name="novo_ug_cel" value="<?php echo $objUsuarioGames->getCel() ?>" maxlength="9" size="9" class="texto"></td>
		  </tr>

          <tr bgcolor="#FFFFFF" class="texto"> 
            <td colspan="4" bgcolor="#ECE9D8">Gestão de Risco</font></td>
          </tr>
	  <tr bgcolor="#FFFFFF" class="texto"> 
                <td bgcolor="#ECE9D8" colspan="2">Observações</td>
          </tr>
          <tr bgcolor="#FFFFFF" class="texto"> 
                <td valign="top" colspan="2">
                        <?php  echo str_replace(PHP_EOL, "<BR>", $objUsuarioGames->getOBS()); ?>
                        <textarea cols="40" rows="8" name="novo_ug_obs"></textarea>
                </td>
          </tr>
		</table>


    </td>
  </tr>
</table>
</form>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</html>
