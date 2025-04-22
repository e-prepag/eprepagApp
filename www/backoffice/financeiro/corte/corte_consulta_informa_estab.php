<?php 
    require_once '../../../includes/constantes.php';
    require_once $raiz_do_projeto . "backoffice/includes/topo.php";
    require_once $raiz_do_projeto."includes/main.php";
    require_once $raiz_do_projeto."includes/pdv/main.php";
    require_once $raiz_do_projeto . "includes/pdv/corte_classPrincipal.php"; 
    require_once "/www/includes/bourls.php";
    
    $time_start = getmicrotime();
	
	if(isset($ncamp) && !$ncamp)    $ncamp       = 'ug_id';
	if(!isset($ncamp) || !$inicial)  $inicial     = 0;
	if(!isset($ncamp) || !$range)    $range       = 1;
	if(!isset($ncamp) || !$ordem)    $ordem       = 0;
//	if($BtnSearch) $inicial     = 0;
	if(isset($ncamp) && $BtnSearch) $range       = 1;
	if(isset($ncamp) && $BtnSearch) $total_table = 0;

	$default_add  = nome_arquivo($PHP_SELF);
	$img_proxima  = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
	$img_anterior = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
	$max          = $qtde_reg_tela;
	$range_qtde   = $qtde_range_tela;

    if(!isset($tf_u_codigo))
        $tf_u_codigo = null;
    
    if(!isset($tf_u_status))
        $tf_u_status = null;
    
    if(!isset($tf_u_data_ultimo_acesso_ini))
        $tf_u_data_ultimo_acesso_ini = null;
    
    if(!isset($tf_u_qtde_acessos_ini))
        $tf_u_qtde_acessos_ini = null;
    
    if(!isset($tf_u_qtde_acessos_fim))
        $tf_u_qtde_acessos_fim = null;
    
    if(!isset($tf_u_data_ultimo_acesso_ini))
        $tf_u_data_ultimo_acesso_ini = null;
    
    if(!isset($tf_u_data_ultimo_acesso_fim))
        $tf_u_data_ultimo_acesso_fim = null;
    
    if(!isset($tf_u_data_inclusao_ini))
        $tf_u_data_inclusao_ini = null;
    
    if(!isset($tf_u_data_inclusao_fim))
        $tf_u_data_inclusao_fim = null;
    
    if(!isset($tf_u_login))
        $tf_u_login = null;
    
    if(!isset($tf_u_nome_fantasia))
        $tf_u_nome_fantasia = null;
    
    if(!isset($tf_u_razao_social))
        $tf_u_razao_social = null;
    
    if(!isset($tf_u_cnpj))
        $tf_u_cnpj = null;
    
    if(!isset($tf_u_responsavel))
        $tf_u_responsavel = null;
    
    if(!isset($tf_u_email))
        $tf_u_email = null;
    
    if(!isset($tf_u_tipo_cadastro))
        $tf_u_tipo_cadastro = null;
    
    if(!isset($tf_u_razao_social))
        $tf_u_razao_social = null;
    
    if(!isset($tf_u_nome))
        $tf_u_nome = null;
    
    if(!isset($tf_u_cpf))
        $tf_u_cpf = null;
    
    if(!isset($tf_u_rg))
        $tf_u_rg = null;
    
    if(!isset($tf_u_sexo))
        $tf_u_sexo = null;
    
    if(!isset($tf_u_data_nascimento_ini))
        $tf_u_data_nascimento_ini = null;
    
    if(!isset($tf_u_data_nascimento_fim))
        $tf_u_data_nascimento_fim = null;
    
    if(!isset($tf_u_endereco))
        $tf_u_endereco = null;
    
    if(!isset($tf_u_bairro))
        $tf_u_bairro = null;
    
    if(!isset($tf_u_cidade))
        $tf_u_cidade = null;
    
    if(!isset($tf_u_cep))
        $tf_u_cep = null;
    
    if(!isset($tf_u_estado))
        $tf_u_estado = null;
    
    if(!isset($tf_u_tel_ddi))
        $tf_u_tel_ddi = null;
    
    if(!isset($tf_u_tel_ddd))
        $tf_u_tel_ddd = null;
    
    if(!isset($tf_u_tel))
        $tf_u_tel = null;
    
    if(!isset($tf_u_cel_ddi))
        $tf_u_cel_ddi = null;
    
    if(!isset($tf_u_cel_ddd))
        $tf_u_cel_ddd = null;
    
    if(!isset($tf_u_cel))
        $tf_u_cel = null;
    
    if(!isset($tf_u_fax_ddi))
        $tf_u_fax_ddi = null;
    
    if(!isset($tf_u_fax_ddd))
        $tf_u_fax_ddd = null;
    
    if(!isset($tf_u_fax))
        $tf_u_fax = null;
    
    if(!isset($tf_u_ra_codigo))
        $tf_u_ra_codigo = null;
    
    if(!isset($tf_u_ra_outros))
        $tf_u_ra_outros = null;
    
    if(!isset($tf_u_contato01_nome))
        $tf_u_contato01_nome = null;
    
    if(!isset($tf_u_contato01_cargo))
        $tf_u_contato01_cargo = null;
    
    if(!isset($tf_u_contato01_tel_ddi))
        $tf_u_contato01_tel_ddi = null;
    
    if(!isset($tf_u_contato01_tel_ddd))
        $tf_u_contato01_tel_ddd = null;
    
    if(!isset($tf_u_contato01_tel))
        $tf_u_contato01_tel = null;
    
    if(!isset($tf_u_observacoes))
        $tf_u_observacoes = null;
    
    if(!isset($tf_u_observacoes))
        $tf_u_observacoes = null;
    
    if(!isset($ncamp))
        $ncamp = null;
    
	$varsel = "&BtnSearch=1";
	$varsel .= "&tf_u_codigo=$tf_u_codigo&tf_u_status=$tf_u_status";
	$varsel .= "&tf_u_qtde_acessos_ini=$tf_u_qtde_acessos_ini&tf_u_qtde_acessos_fim=$tf_u_qtde_acessos_fim";
	$varsel .= "&tf_u_data_ultimo_acesso_ini=$tf_u_data_ultimo_acesso_ini&tf_u_data_ultimo_acesso_fim=$tf_u_data_ultimo_acesso_fim";
	$varsel .= "&tf_u_data_inclusao_ini=$tf_u_data_inclusao_ini&tf_u_data_inclusao_fim=$tf_u_data_inclusao_fim";
	$varsel .= "&tf_u_login=$tf_u_login";
	$varsel .= "&tf_u_nome_fantasia=$tf_u_nome_fantasia&tf_u_razao_social=$tf_u_razao_social";
	$varsel .= "&tf_u_cnpj=$tf_u_cnpj";
	$varsel .= "&tf_u_responsavel=$tf_u_responsavel&tf_u_email=$tf_u_email";
	$varsel .= "&tf_u_tipo_cadastro=$tf_u_tipo_cadastro&tf_u_nome=$tf_u_nome&tf_u_cpf=$tf_u_cpf&tf_u_rg=$tf_u_rg&tf_u_sexo=$tf_u_sexo";
	$varsel .= "&tf_u_data_nascimento_ini=$tf_u_data_nascimento_ini&tf_u_data_nascimento_fim=$tf_u_data_nascimento_fim";
	$varsel .= "&tf_u_endereco=$tf_u_endereco&tf_u_bairro=$tf_u_bairro&tf_u_cidade=$tf_u_cidade&tf_u_cep=$tf_u_cep&tf_u_estado=$tf_u_estado";
	$varsel .= "&tf_u_tel_ddi=$tf_u_tel_ddi&tf_u_tel_ddd=$tf_u_tel_ddd&tf_u_tel=$tf_u_tel";
	$varsel .= "&tf_u_cel_ddi=$tf_u_cel_ddi&tf_u_cel_ddd=$tf_u_cel_ddd&tf_u_cel=$tf_u_cel";
	$varsel .= "&tf_u_fax_ddi=$tf_u_fax_ddi&tf_u_fax_ddd=$tf_u_fax_ddd&tf_u_fax=$tf_u_fax";
	$varsel .= "&tf_u_ra_codigo=$tf_u_ra_codigo&tf_u_ra_outros=$tf_u_ra_outros";
	$varsel .= "&tf_u_contato01_nome=$tf_u_contato01_nome&tf_u_contato01__cargo=$tf_u_contato01_cargo";
	$varsel .= "&tf_u_contato01_tel_ddi=$tf_u_contato01_tel_ddi&tf_u_contato01_tel_ddd=$tf_u_contato01_tel_ddd&tf_u_contato01_tel=$tf_u_contato01_tel";
	$varsel .= "&tf_u_observacoes=$tf_u_observacoes&tf_u_observacoes=$tf_u_observacoes";

	$msg = "";


	if(isset($BtnSearch)){
	
		//Validacao
		//------------------------------------------------------------------------------------------------------------------
		$msg = "";

		//Dados administrativos
		//------------------------------------------------------------------
		//codigo
		if($msg == "")
			if($tf_u_codigo){
				if(!is_numeric($tf_u_codigo)) $msg = "Código deve ser numérico.\n";
			}
		//Qtde acessos
		if($msg == "")
			if($tf_u_qtde_acessos_ini || $tf_u_qtde_acessos_fim){
				if(!is_numeric($tf_u_qtde_acessos_ini))	$msg = "Qtde de Acessos inicial deve ser numérico.\n";
				if(!is_numeric($tf_u_qtde_acessos_fim))	$msg = "Qtde de Acessos final deve ser numérico.\n";
			}
		//Data_ultimo_acesso
		if($msg == "")
			if($tf_u_data_ultimo_acesso_ini || $tf_u_data_ultimo_acesso_fim){
				if(verifica_data($tf_u_data_ultimo_acesso_ini) == 0)	$msg = "A Data Último Acesso inicial é inválida.\n";
				if(verifica_data($tf_u_data_ultimo_acesso_fim) == 0)	$msg = "A Data Último Acesso final é inválida.\n";
			}
		//Data de Cadastro
		if($msg == "")
			if($tf_u_data_inclusao_ini || $tf_u_data_inclusao_fim){
				if(verifica_data($tf_u_data_inclusao_ini) == 0)	$msg = "A Data de Cadastro inicial é inválida.\n";
				if(verifica_data($tf_u_data_inclusao_fim) == 0)	$msg = "A Data de Cadastro fina é inválida.\n";
			}

		//Dados
		//------------------------------------------------------------------
		//tf_u_cnpj
		if($msg == "")
			if($tf_u_cnpj){
				if(!is_numeric($tf_u_cnpj)) $msg = "O CNPJ deve ter somente números.\n";
			}

		//Data de Nascimento
		if($msg == "")
			if($tf_u_data_nascimento_ini || $tf_u_data_nascimento_fim){
				if(verifica_data($tf_u_data_nascimento_ini) == 0)	$msg = "A Data de Nascimento inicialé inválida.\n";
				if(verifica_data($tf_u_data_nascimento_fim) == 0)	$msg = "A Data de Nascimento final é inválida.\n";
			}

		//Endereco
		//------------------------------------------------------------------
		//tf_u_cep
		if($msg == "")
			if($tf_u_cep){
				if(!is_numeric(str_replace("-","",$tf_u_cep))) $msg = "CEP deve ser numérico.\n";
			}

		//tf_u_tel_ddi
		if($msg == "")
			if($tf_u_tel_ddi){
				if(!is_numeric($tf_u_tel_ddi)) $msg = "O Código do País do Telefone deve ser numérico.\n";
			}
		//tf_u_tel_ddd
		if($msg == "")
			if($tf_u_tel_ddd){
				if(!is_numeric($tf_u_tel_ddd)) $msg = "DDD do Telefone deve ser numérico.\n";
			}
		//tf_u_tel
		if($msg == "")
			if($tf_u_tel){
				if(!is_numeric(str_replace("-","",$tf_u_tel))) $msg = "Telefone deve ser numérico.\n";
			}

		//tf_u_cel_ddi
		if($msg == "")
			if($tf_u_cel_ddi){
				if(!is_numeric($tf_u_cel_ddi)) $msg = "O Código do País do Celular deve ser numérico.\n";
			}
		//tf_u_cel_ddd
		if($msg == "")
			if($tf_u_cel_ddd){
				if(!is_numeric($tf_u_cel_ddd)) $msg = "DDD do Celular deve ser numérico.\n";
			}
		//tf_u_cel
		if($msg == "")
			if($tf_u_cel){
				if(!is_numeric(str_replace("-","",$tf_u_cel))) $msg = "Celular deve ser numérico.\n";
			}

		//tf_u_fax_ddi
		if($msg == "")
			if($tf_u_fax_ddi){
				if(!is_numeric($tf_u_fax_ddi)) $msg = "O Código do País do Fax deve ser numérico.\n";
			}
		//tf_u_fax_ddd
		if($msg == "")
			if($tf_u_fax_ddd){
				if(!is_numeric($tf_u_fax_ddd)) $msg = "DDD do Fax deve ser numérico.\n";
			}
		//tf_u_fax
		if($msg == "")
			if($tf_u_fax){
				if(!is_numeric(str_replace("-","",$tf_u_fax))) $msg = "Fax deve ser numérico.\n";
			}

	
		//Busca vendas
		//------------------------------------------------------------------------------------------------------------------
		if($msg == ""){
			$sql  = "select * 
					 from dist_usuarios_games ug
 					 where 1=1 ";
			if($tf_u_codigo)		$sql .= " and ug.ug_id = '" . $tf_u_codigo . "' ";
			if($tf_u_status)		$sql .= " and ug.ug_ativo = " . $tf_u_status . " ";
			if($tf_u_qtde_acessos_ini && $tf_u_qtde_acessos_fim) 			$sql .= " and ug.ug_qtde_acessos between " . ($tf_u_qtde_acessos_ini==-1?0:$tf_u_qtde_acessos_ini) ." and " . ($tf_u_qtde_acessos_fim==-1?0:$tf_u_qtde_acessos_fim);
			if($tf_u_data_ultimo_acesso_ini && $tf_u_data_ultimo_acesso_fim)$sql .= " and ug.ug_data_ultimo_acesso between '".formata_data($tf_u_data_ultimo_acesso_ini,1)."' and '".formata_data($tf_u_data_ultimo_acesso_fim,1)."'";
			if($tf_u_data_inclusao_ini && $tf_u_data_inclusao_fim) 			$sql .= " and ug.ug_data_inclusao between '".formata_data($tf_u_data_inclusao_ini,1)."' and '".formata_data($tf_u_data_inclusao_fim,1)."'";
			if($tf_u_login) 		$sql .= " and upper(ug.ug_login) like '%" . strtoupper($tf_u_login) . "%' ";
			if(!is_null($tf_u_corte_dia_semana) && trim($tf_u_corte_dia_semana) != "") $sqlFiltro = " and ug.ug_perfil_corte_dia_semana = $tf_u_corte_dia_semana ";

			if($tf_u_nome_fantasia) $sql .= " and upper(ug.ug_nome_fantasia) like '%" . strtoupper($tf_u_nome_fantasia) . "%' ";
			if($tf_u_razao_social) 	$sql .= " and upper(ug.ug_razao_social) like '%" . strtoupper($tf_u_razao_social) . "%' ";
			if($tf_u_cnpj) 			$sql .= " and ug.ug_cnpj like '%" . $tf_u_cnpj . "%' ";
			if($tf_u_responsavel) 	$sql .= " and upper(ug.ug_responsavel) like '%" . strtoupper($tf_u_responsavel) . "%' ";
			if($tf_u_email)			$sql .= " and upper(ug.ug_email) like '%" . strtoupper($tf_u_email) . "%' ";
			
			if($tf_u_tipo_cadastro) $sql .= " and upper(ug.ug_tipo_cadastro) = '" . strtoupper($tf_u_tipo_cadastro) . "' ";
			if($tf_u_nome) 		$sql .= " and upper(ug.ug_nome) like '%" . strtoupper($tf_u_nome) . "%' ";
			if($tf_u_cpf) 		$sql .= " and ug.ug_cpf like '%" . $tf_u_cpf . "%' ";
			if($tf_u_rg) 		$sql .= " and ug.ug_rg like '%" . $tf_u_rg . "%' ";
			if($tf_u_sexo) 		$sql .= " and upper(ug.ug_sexo) = '" . strtoupper($tf_u_sexo) . "' ";
			if($tf_u_data_nascimento_ini && $tf_u_data_nascimento_fim) 			$sql .= " and ug.ug_data_nascimento between '".formata_data($tf_u_data_nascimento_ini,1)."' and '".formata_data($tf_u_data_nascimento_fim,1)."'";
			
			if($tf_u_endereco) 	$sql .= " and upper(ug.ug_endereco) like '%" . strtoupper($tf_u_endereco) . "%' ";
			if($tf_u_bairro)	$sql .= " and upper(ug.ug_bairro) like '%" . strtoupper($tf_u_bairro) . "%' ";
			if($tf_u_cidade)	$sql .= " and upper(ug.ug_cidade) like '%" . strtoupper($tf_u_cidade) . "%' ";
			if($tf_u_cep)		$sql .= " and ug.ug_cep like '%" . $tf_u_cep . "%' ";
			if($tf_u_estado)	$sql .= " and upper(ug.ug_estado) = '" . strtoupper($tf_u_estado) . "' ";

			if($tf_u_tel_ddi) 	$sql .= " and ug.ug_tel_ddi = '" . $tf_u_tel_ddi . "' ";
			if($tf_u_tel_ddd) 	$sql .= " and ug.ug_tel_ddd = '" . $tf_u_tel_ddd . "' ";
			if($tf_u_tel) 		$sql .= " and ug.ug_tel like '%" . $tf_u_tel . "%' ";
			if($tf_u_cel_ddi) 	$sql .= " and ug.ug_cel_ddi = '" . $tf_u_cel_ddi . "' ";
			if($tf_u_cel_ddd) 	$sql .= " and ug.ug_cel_ddd = '" . $tf_u_cel_ddd . "' ";
			if($tf_u_cel) 		$sql .= " and ug.ug_cel like '%" . $tf_u_cel . "%' ";
			if($tf_u_fax_ddi) 	$sql .= " and ug.ug_fax_ddi = '" . $tf_u_fax_ddi . "' ";
			if($tf_u_fax_ddd) 	$sql .= " and ug.ug_fax_ddd = '" . $tf_u_fax_ddd . "' ";
			if($tf_u_fax) 		$sql .= " and ug.ug_fax like '%" . $tf_u_fax . "%' ";

			if($tf_u_ra_codigo)	$sql .= " and upper(ug.ug_ra_codigo) = '" . strtoupper($tf_u_ra_codigo) . "' ";
			if($tf_u_ra_outros)	$sql .= " and upper(ug.ug_ra_outros) like '%" . strtoupper($tf_u_ra_outros) . "%' ";

			if($tf_u_contato01_nome) 	$sql .= " and upper(ug.ug_contato01_nome) like '%" . strtoupper($tf_u_contato01_nome) . "%' ";
			if($tf_u_contato01_cargo) 	$sql .= " and upper(ug.ug_contato01_cargo) like '%" . strtoupper($tf_u_contato01_cargo) . "%' ";
			if($tf_u_contato01_tel_ddi) $sql .= " and ug.ug_contato01_tel_ddi = '" . $tf_u_contato01_tel_ddi . "' ";
			if($tf_u_contato01_tel_ddd) $sql .= " and ug.ug_contato01_tel_ddd = '" . $tf_u_contato01_tel_ddd . "' ";
			if($tf_u_contato01_tel) 	$sql .= " and ug.ug_contato01_tel like '%" . $tf_u_contato01_tel . "%' ";

			if($tf_u_observacoes) 	$sql .= " and ug.ug_observacoes like '%" . $tf_u_observacoes . "%' ";


			$rs_usuario = SQLexecuteQuery($sql);
			$total_table = pg_num_rows($rs_usuario);

			//Ordem
			if($ncamp){
                $sql .= " order by ".$ncamp;
                if($ordem == 1){
                    $sql .= " desc ";
                    $img_seta = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/seta_down.gif";
                } else {
                    $sql .= " asc ";
                    $img_seta = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/seta_up.gif";
                }
            }
			
			$sql .= " limit ".$max; 
			$sql .= " offset ".$inicial;
//echo $sql;

			if($total_table == 0) {
				$msg = "Nenhum usuário encontrado.\n";
			} else {
				$rs_usuario = SQLexecuteQuery($sql);
				
				if($max + $inicial > $total_table)
					$reg_ate = $total_table;
				else
					$reg_ate = $max + $inicial;
			}
				
		}
	}
	
	//RA
	$resatv = SQLexecuteQuery("select ra_codigo, ra_desc from ramo_atividade order by ra_desc");
	
ob_end_flush();
?>
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>
<script language="javascript">
function GP_popupAlertMsg(msg) { //v1.0
  document.MM_returnValue = alert(msg);
}
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}

$(function(){
       var optDate = new Object();
            optDate.interval = 10000;

        setDateInterval('tf_u_data_inclusao_ini','tf_u_data_inclusao_fim',optDate);
        setDateInterval('tf_u_data_ultimo_acesso_ini','tf_u_data_ultimo_acesso_fim',optDate);
        setDateInterval('tf_u_data_nascimento_ini','tf_u_data_nascimento_fim',optDate);
        
    });
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="2">BackOffice - Lan Houses</a></li>
        <li><a href="index.php">Corte Semanal</a></li>
        <li class="active">Consulta Corte Semanal</li>
    </ol> 
</div>
<table class="table txt-preto fontsize-p">
  <tr> 
    <td> 
        <form name="form1" method="post" action="corte_consulta_informa_estab.php">
		
        <table class="table">
          <tr bgcolor="#F5F5FB"> 
            <td align="right"><input type="submit" name="BtnSearch" value="Buscar" class="btn btn-info btn-sm"></td>
          </tr>
		</table>
    </td>
  </tr>
</table>
<table class="table txt-preto fontsize-p">
  <tr> 
    <td> 
        <table class="table">
		
          <tr bgcolor="#FFFFFF" class="texto"> 
            <td colspan="6" bgcolor="#ECE9D8">Dados Administrativos</font></td>
          </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td width="100">C&oacute;digo</font></td>
            <td>
              	<input name="tf_u_codigo" type="text" class="form2" value="<?php echo $tf_u_codigo ?>" size="7" maxlength="7">
			</td>
            <td>Status</td>
			<td>
				<select name="tf_u_status" class="form2">
					<option value="" <?php if($tf_u_status == "") echo "selected" ?>>Selecione</option>
					<option value="1" <?php if ($tf_u_status == "1") echo "selected";?>>Ativo</option>
					<option value="2" <?php if ($tf_u_status == "2") echo "selected";?>>Inativo</option>
				</select>
			</td>
          </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td>Qtde de Acessos</td>
            <td>
              	<input name="tf_u_qtde_acessos_ini" type="text" class="form2" value="<?php echo $tf_u_qtde_acessos_ini ?>" size="7" maxlength="7"> a
              	<input name="tf_u_qtde_acessos_fim" type="text" class="form2" value="<?php echo $tf_u_qtde_acessos_fim ?>" size="7" maxlength="7"> (para 0 usar -1)
			</td>
            <td>Data Último Acesso</td>
            <td>
              <input name="tf_u_data_ultimo_acesso_ini" type="text" class="form" id="tf_u_data_ultimo_acesso_ini" value="<?php echo $tf_u_data_ultimo_acesso_ini ?>" size="9" maxlength="10">
              a
              <input name="tf_u_data_ultimo_acesso_fim" type="text" class="form" id="tf_u_data_ultimo_acesso_fim" value="<?php echo $tf_u_data_ultimo_acesso_fim ?>" size="9" maxlength="10">
			</td>
          </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td>Data de Cadastro</td>
            <td>
              <input name="tf_u_data_inclusao_ini" type="text" class="form" id="tf_u_data_inclusao_ini" value="<?php echo $tf_u_data_inclusao_ini ?>" size="9" maxlength="10">
              a 
              <input name="tf_u_data_inclusao_fim" type="text" class="form" id="tf_u_data_inclusao_fim" value="<?php echo $tf_u_data_inclusao_fim ?>" size="9" maxlength="10">
			</td>
            <td colspan="2"></td>
          </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td>Login</td>
            <td>
              	<input name="tf_u_login" type="text" class="form2" value="<?php echo $tf_u_login ?>" size="25" maxlength="100">
			</td>
            <td colspan="2"></td>
          </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td>Tipo de Cadastro</td>
            <td>
				<select name="tf_u_tipo_cadastro" class="form2">
					<option value="" <?php if($tf_u_tipo_cadastro == "") echo "selected" ?>>Selecione</option>
					<option value="PJ" <?php if ($tf_u_tipo_cadastro == "PJ") echo "selected";?>>Pessoa Jurídica</option>
					<option value="PF" <?php if ($tf_u_tipo_cadastro == "PF") echo "selected";?>>Pessoa Física</option>
				</select>
			</td>
            <td>Dia de Corte</td>
            <td>
				<select name="tf_u_corte_dia_semana">
					<option value="">Selecione o dia do corte</option>
					<?php foreach ($GLOBALS['CORTE_DIAS_DA_SEMANA_DESCRICAO'] as $diasId => $diasNome){ ?>
					<option value="<?php echo $diasId; ?>" <?php if(isset($tf_u_corte_dia_semana) && trim($tf_u_corte_dia_semana) == trim($diasId)) echo "selected";?>><?php echo $diasNome; ?></option>
					<?php } ?>
				</select>
			</td>
          </tr>
		  
          <tr bgcolor="#FFFFFF" class="texto"> 
            <td colspan="4" bgcolor="#ECE9D8">Dados da Empresa</font></td>
          </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td>Nome Fantasia</font></td>
            <td>
              	<input name="tf_u_nome_fantasia" type="text" class="form2" value="<?php echo $tf_u_nome_fantasia ?>" size="25" maxlength="100">
			</td>
            <td>Razão Social</font></td>
            <td>
              	<input name="tf_u_razao_social" type="text" class="form2" value="<?php echo $tf_u_razao_social ?>" size="25" maxlength="100">
			</td>
		  </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td>CNPJ</font></td>
            <td>
              	<input name="tf_u_cnpj" type="text" class="form2" value="<?php echo $tf_u_cnpj ?>" size="25" maxlength="14">
			</td>
            <td colspan="2"></td>
		  </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td>Responsável</font></td>
            <td>
              	<input name="tf_u_responsavel" type="text" class="form2" value="<?php echo $tf_u_responsavel ?>" size="25" maxlength="100">
			</td>
            <td>Email</font></td>
            <td>
              	<input name="tf_u_email" type="text" class="form2" value="<?php echo $tf_u_email ?>" size="25" maxlength="100">
			</td>
		  </tr>

          <tr bgcolor="#FFFFFF" class="texto"> 
            <td colspan="4" bgcolor="#ECE9D8">Dados Pessoais</font></td>
          </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td>Nome</font></td>
            <td>
              	<input name="tf_u_nome" type="text" class="form2" value="<?php echo $tf_u_nome ?>" size="25" maxlength="100">
			</td>
            <td>Sexo</td>
			<td>
				<select name="tf_u_sexo" class="form2">
					<option value="" <?php if($tf_u_sexo == "") echo "selected" ?>>Selecione</option>
					<option value="M" <?php if ($tf_u_sexo == "M") echo "selected";?>>Masculino</option>
					<option value="F" <?php if ($tf_u_sexo == "F") echo "selected";?>>Feminino</option>
				</select>
			</td>
		  </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td>CPF</font></td>
            <td>
              	<input name="tf_u_cpf" type="text" class="form2" value="<?php echo $tf_u_cpf ?>" size="25" maxlength="14">
			</td>
            <td>RG</font></td>
            <td>
              	<input name="tf_u_rg" type="text" class="form2" value="<?php echo $tf_u_rg ?>" size="25" maxlength="14">
			</td>
		  </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td>Data de Nascimento</td>
            <td>
              <input name="tf_u_data_nascimento_ini" type="text" class="form" id="tf_u_data_nascimento_ini" value="<?php echo $tf_u_data_nascimento_ini ?>" size="9" maxlength="10">
              a 
              <input name="tf_u_data_nascimento_fim" type="text" class="form" id="tf_u_data_nascimento_fim" value="<?php echo $tf_u_data_nascimento_fim ?>" size="9" maxlength="10">
			</td>
            <td colspan="2"></td>
          </tr>

          <tr bgcolor="#FFFFFF" class="texto"> 
            <td colspan="4" bgcolor="#ECE9D8">Endereço</font></td>
          </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td>Endereço</font></td>
            <td colspan="3">
              	<input name="tf_u_endereco" type="text" class="form2" value="<?php echo $tf_u_endereco ?>" size="50" maxlength="100">
			</td>
		  </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td>Bairro</font></td>
            <td>
              	<input name="tf_u_bairro" type="text" class="form2" value="<?php echo $tf_u_bairro ?>" size="25" maxlength="100">
			</td>
            <td>Cidade</font></td>
            <td>
              	<input name="tf_u_cidade" type="text" class="form2" value="<?php echo $tf_u_cidade ?>" size="25" maxlength="100">
			</td>
		  </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td>CEP</font></td>
            <td>
              	<input name="tf_u_cep" type="text" class="form2" value="<?php echo $tf_u_cep ?>" size="7" maxlength="8">
			</td>
            <td>Estado</font></td>
            <td>
				<select name="tf_u_estado" class="field_dados">
					<option value="" <?php if($tf_u_estado == "") echo "selected" ?>>Selecione</option>
                                <?php   if(isset($SIGLA_ESTADOS)){?>
				<?php       for($i=0; $i < count($SIGLA_ESTADOS); $i++){ ?>
                                                <option value="<?php echo $SIGLA_ESTADOS[$i] ?>" <?php if($tf_u_estado == $SIGLA_ESTADOS[$i]) echo "selected"; ?>><?php echo $SIGLA_ESTADOS[$i] ?></option>
                                <?php 
                                            }
                                        } 
                                ?>
                                        
				</select>
			</td>
		  </tr>

          <tr bgcolor="#F5F5FB" class="texto"> 
            <td>Telefone</font></td>
            <td>
              	(<input name="tf_u_tel_ddi" type="text" class="form2" value="<?php echo $tf_u_tel_ddi ?>" size="2" maxlength="2">)
              	(<input name="tf_u_tel_ddd" type="text" class="form2" value="<?php echo $tf_u_tel_ddd ?>" size="2" maxlength="2">)
              	<input name="tf_u_tel" type="text" class="form2" value="<?php echo $tf_u_tel ?>" size="7" maxlength="9">
			</td>
            <td>Celular</font></td>
            <td>
              	(<input name="tf_u_cel_ddi" type="text" class="form2" value="<?php echo $tf_u_cel_ddi ?>" size="2" maxlength="2">)
              	(<input name="tf_u_cel_ddd" type="text" class="form2" value="<?php echo $tf_u_cel_ddd ?>" size="2" maxlength="2">)
              	<input name="tf_u_cel" type="text" class="form2" value="<?php echo $tf_u_cel ?>" size="7" maxlength="9">
			</td>
		  </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td>Fax</font></td>
            <td>
              	(<input name="tf_u_fax_ddi" type="text" class="form2" value="<?php echo $tf_u_fax_ddi ?>" size="2" maxlength="2">)
              	(<input name="tf_u_fax_ddd" type="text" class="form2" value="<?php echo $tf_u_fax_ddd ?>" size="2" maxlength="2">)
              	<input name="tf_u_fax" type="text" class="form2" value="<?php echo $tf_u_fax ?>" size="7" maxlength="9">
			</td>
            <td colspan="2"></td>
		  </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td>Ramo de atividade</font></td>
            <td>
				<select name="tf_u_ra_codigo" class="field_dados">
					<option value="">Selecione</option>
					<?php while ($pgatv = pg_fetch_array($resatv)) { ?>
						<option value="<?php echo $pgatv['ra_codigo'] ?>" <?php if($pgatv['ra_codigo'] == $tf_u_ra_codigo) echo "selected" ?>><?php echo $pgatv['ra_desc'] ?></option>
					<?php } ?>
				</select>
			</td>
            <td>Ramo de atividade - Outros</font></td>
            <td>
              	<input name="tf_u_ra_outros" type="text" class="form2" value="<?php echo $tf_u_ra_outros ?>" size="25" maxlength="100">
			</td>
		  </tr>

          <tr bgcolor="#FFFFFF" class="texto"> 
            <td colspan="4" bgcolor="#ECE9D8">Contato Técnico</font></td>
          </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td>Nome</font></td>
            <td>
              	<input name="tf_u_contato01_nome" type="text" class="form2" value="<?php echo $tf_u_contato01_nome ?>" size="25" maxlength="100">
			</td>
            <td>Cargo</font></td>
            <td>
              	<input name="tf_u_contato01_cargo" type="text" class="form2" value="<?php echo $tf_u_contato01_cargo ?>" size="25" maxlength="100">
			</td>
		  </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td>Telefone</font></td>
            <td>
              	(<input name="tf_u_contato01_tel_ddi" type="text" class="form2" value="<?php echo $tf_u_contato01_tel_ddi ?>" size="2" maxlength="2">)
              	(<input name="tf_u_contato01_tel_ddd" type="text" class="form2" value="<?php echo $tf_u_contato01_tel_ddd ?>" size="2" maxlength="2">)
              	<input name="tf_u_contato01_tel" type="text" class="form2" value="<?php echo $tf_u_contato01_tel ?>" size="7" maxlength="9">
			</td>
            <td colspan="2"></td>
		  </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td>Observações</font></td>
            <td>
              	<input name="tf_u_observacoes" type="text" class="form2" value="<?php echo $tf_u_observacoes ?>" size="25" maxlength="100">
			</td>
            <td colspan="2"></td>
		  </tr>

		</table>

        <table class="table">
          <tr bgcolor="#F5F5FB"> 
            <td align="right"><input type="submit" name="BtnSearch" value="Buscar" class="btn btn-info btn-sm"></td>
          </tr>
          <?php if($msg != ""){?><tr class="texto"><td align="center"><br><br><font color="#FF0000"><?php echo $msg?></font></td></tr><?php }?>
		</table>
		</form>

		<?php if(isset($total_table)  && $total_table > 0) { ?>
        <table  class="table">
                <tr bgcolor="#00008C"> 
                  <td height="11" colspan="3" bgcolor="#FFFFFF"> 
				  	<table width="100%" border='0' cellpadding="2" cellspacing="1">
				  	  <tr> 
						<td colspan="20" class="texto"> 
                          Exibindo resultados <strong><?php echo $inicial + 1 ?></strong> 
                          a <strong><?php echo $reg_ate ?></strong> de <strong><?php echo $total_table ?></strong></font> 
                        </td>
					  </tr>
					  <?php $ordem = ($ordem == 1)?2:1; ?>
                      <tr  bgcolor="#ECE9D8"> 
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_id&inicial=".$inicial.$varsel ?>" class="link_branco" ><font class="texto">C&oacute;d.</font></a> 
                          <?php if($ncamp == 'ug_id') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_ativo&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Status</font></a>
                          <?php if($ncamp == 'ug_ativo') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_login&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Login</font></a>
                          <?php if($ncamp == 'ug_login') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_nome_fantasia&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Nome Fantasia</font></a>
                          <?php if($ncamp == 'ug_nome_fantasia') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_nome&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Nome</font></a>
                          <?php if($ncamp == 'ug_nome') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_cnpj&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">CNPJ</font></a>
                          <?php if($ncamp == 'ug_cnpj') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_cpf&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">CPF</font></a>
                          <?php if($ncamp == 'ug_cpf') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_rg&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">RG</font></a>
                          <?php if($ncamp == 'ug_rg') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_responsavel&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Responsável</font></a>
                          <?php if($ncamp == 'ug_responsavel') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_email&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Email</font></a>
                          <?php if($ncamp == 'ug_email') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_endereco&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Endereço</font></a>
                          <?php if($ncamp == 'ug_endereco') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_bairro&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Bairro</font></a>
                          <?php if($ncamp == 'ug_bairro') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_cidade&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Cidade</font></a>
                          <?php if($ncamp == 'ug_cidade') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_estado&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Estado</font></a>
                          <?php if($ncamp == 'ug_estado') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_cep&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">CEP</font></a>
                          <?php if($ncamp == 'ug_cep') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                      </tr>
					<?php
						$cor1 = $query_cor1;
						$cor2 = $query_cor1;
						$cor3 = $query_cor2;
                    if(isset($rs_usuario) && $rs_usuario)
						while($rs_usuario_row = pg_fetch_array($rs_usuario)){
							$cor1 = ($cor1 == $cor2)?$cor3:$cor2;
							$ativo = ($rs_usuario_row['ug_ativo'] == 1)?"Ativo":"Inativo";
					?>
                      <tr bgcolor="<?php echo $cor1 ?>" class="texto"> 
                        <td align="center"><a style="text-decoration:none" href="corte_consulta.php?usuario_id=<?php echo $rs_usuario_row['ug_id'] ?>"><?php echo $rs_usuario_row['ug_id'] ?></a></td>
                        <td align="center"><?php echo $ativo ?></td>
                        <td><?php echo $rs_usuario_row['ug_login'] ?></td>
                        <td><?php echo $rs_usuario_row['ug_nome_fantasia'] ?></td>
                        <td><?php echo $rs_usuario_row['ug_nome'] ?></td>
                        <td nowrap><?php echo mascara_cnpj_cpf($rs_usuario_row['ug_cnpj'], 'cnpj') ?></td>
                        <td nowrap><?php echo $rs_usuario_row['ug_cpf'] ?></td>
                        <td nowrap><?php echo $rs_usuario_row['ug_rg'] ?></td>
                        <td><?php echo $rs_usuario_row['ug_responsavel'] ?></td>
                        <td><?php echo $rs_usuario_row['ug_email'] ?></td>
                        <td><?php echo $rs_usuario_row['ug_endereco'] ?>, <?php echo $rs_usuario_row['ug_numero'] ?> <?php echo $rs_usuario_row['ug_complemento'] ?> </td>
                        <td><?php echo $rs_usuario_row['ug_bairro'] ?></td>
                        <td><?php echo $rs_usuario_row['ug_cidade'] ?></td>
                        <td><?php echo $rs_usuario_row['ug_estado'] ?></td>
                        <td nowrap><?php echo $rs_usuario_row['ug_cep'] ?></td>
                      </tr>
					<?php 	}	?>
                      <tr> 
                        <td colspan="20" bgcolor="#FFFFFF" class="texto"><?php echo $search_msg . number_format(getmicrotime() - $time_start, 2, '.', '.') . $search_unit ?></font></td>
                      </tr>
					<?php paginacao_query($inicial, $total_table, $max, 20, $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel); ?>
                    </table>
				  </td>
                </tr>
              </table>
          <?php  }  ?>
    </td>
  </tr>
</table>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</html>
