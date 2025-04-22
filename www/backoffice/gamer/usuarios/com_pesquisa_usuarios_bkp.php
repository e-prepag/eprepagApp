<?php 
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
include $raiz_do_projeto."includes/main.php";
include $raiz_do_projeto."includes/gamer/main.php";

set_time_limit ( 6000 ) ;
$time_start = getmicrotime();

if(!$ncamp)    $ncamp       = 'ug_id';
if(!$inicial)  $inicial     = 0;
if(!$range)    $range       = 1;
if(!$ordem)    $ordem       = 0;
//	if($BtnSearch) $inicial     = 0;
//	if($BtnSearch) $range       = 1;
//	if($BtnSearch) $total_table = 0;
if($BtnSearch=="Buscar") {
        $inicial     = 0;
        $range       = 1;
        $total_table = 0;
}

$default_add  = nome_arquivo($PHP_SELF);
$img_proxima  = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
$img_anterior = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
$max          = 100; //$qtde_reg_tela;
$range_qtde   = $qtde_range_tela;

$varsel  = "&BtnSearch=1";
$varsel .= "&tf_u_codigo=$tf_u_codigo&tf_u_codigo_include=$tf_u_codigo_include&tf_u_status=$tf_u_status";
$varsel .= "&tf_u_qtde_acessos_ini=$tf_u_qtde_acessos_ini&tf_u_qtde_acessos_fim=$tf_u_qtde_acessos_fim";
$varsel .= "&tf_u_data_ultimo_acesso_ini=$tf_u_data_ultimo_acesso_ini&tf_u_data_ultimo_acesso_fim=$tf_u_data_ultimo_acesso_fim";
$varsel .= "&tf_u_data_inclusao_ini=$tf_u_data_inclusao_ini&tf_u_data_inclusao_fim=$tf_u_data_inclusao_fim";
$varsel .= "&tf_u_email=$tf_u_email&tf_u_nome=$tf_u_nome&tf_u_cpf=$tf_u_cpf&tf_u_sexo=$tf_u_sexo&ug_login=$ug_login";
$varsel .= "&tf_u_data_nascimento_ini=$tf_u_data_nascimento_ini&tf_u_data_nascimento_fim=$tf_u_data_nascimento_fim";
$varsel .= "&tf_u_tel_ddi=$tf_u_tel_ddi&tf_u_tel_ddd=$tf_u_tel_ddd&tf_u_tel=$tf_u_tel&tf_u_endereco_ip=$tf_u_endereco_ip&tf_u_observacoes=$tf_u_observacoes";
$varsel .= "&tf_u_cel_ddi=$tf_u_cel_ddi&tf_u_cel_ddd=$tf_u_cel_ddd&tf_u_cel=$tf_u_cel&tf_u_news=$tf_u_news";
$varsel .= "&tf_u_endereco=$tf_u_endereco&tf_u_bairro=$tf_u_bairro&tf_u_cidade=$tf_u_cidade&tf_u_cep=$tf_u_cep&tf_u_estado=$tf_u_estado";
$varsel .= "&tf_u_compet_aceito_regulamento=$tf_u_compet_aceito_regulamento&tf_u_compet_jogo=$tf_u_compet_jogo";
$varsel .= "&tf_u_integracao_origem=$tf_u_integracao_origem";
$varsel .= "&tf_u_com_totais_vendas=$tf_u_com_totais_vendas&tf_u_habilitado_cielo=$tf_u_habilitado_cielo";
$varsel .= "&dd_opr_codigo=$dd_opr_codigo&tf_u_usuario_vip=$tf_u_usuario_vip";
$varsel .= "&ug_cadastro_completo=$ug_cadastro_completo";
$varsel .= "&tf_v_data_inclusao_ini=$tf_v_data_inclusao_ini&tf_v_data_inclusao_fim=$tf_v_data_inclusao_fim&ug_flag_usando_saldo=$ug_flag_usando_saldo";

$a_lista_usuarios_VIP = get_lista_usuarios_VIP();

if(isset($BtnSearch)){

        //Validacao
        //------------------------------------------------------------------------------------------------------------------
        $msg = "";
        $produtos_query = "";

        //Dados administrativos
        //------------------------------------------------------------------
        if(!isset($tf_u_codigo_include)) $tf_u_codigo_include = "1";

        //codigo
        if($msg == "")
                if($tf_u_codigo){
                        if(!is_csv_numeric_global($tf_u_codigo, 1)) {
                                $msg = "Código da venda deve ser numérico ou lista de números separada por vírgulas.\n";
                        }
                }
        //Data inclusao de venda
        if($msg == "")
                if($tf_v_data_inclusao_ini || $tf_v_data_inclusao_fim){
                        if(verifica_data($tf_v_data_inclusao_ini) == 0)	{
                                $msg = "A data de inclusão inicial da venda é inválida.\n";
                        } else {
                                $produtos_query .= " and vg_data_concilia >= '".formata_data($tf_v_data_inclusao_ini, 1)." 00:00:00' ";
                        }
                        if(verifica_data($tf_v_data_inclusao_fim) == 0)	{
                                $msg = "A data de inclusão final da venda é inválida.\n";
                        } else {
                                $produtos_query .= " and vg_data_concilia <= '".formata_data($tf_v_data_inclusao_fim, 1)." 23:59:59' ";
                        }
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

        //Dados Pessoais
        //------------------------------------------------------------------
        //Data de Nascimento
        if($msg == "")
                if($tf_u_data_nascimento_ini || $tf_u_data_nascimento_fim){
                        if(verifica_data($tf_u_data_nascimento_ini) == 0)	$msg = "A Data de Nascimento inicial é inválida.\n";
                        if(verifica_data($tf_u_data_nascimento_fim) == 0)	$msg = "A Data de Nascimento final é inválida.\n";
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

        //tf_u_habilitado_cielo
        if($msg == "")
                if(!(($tf_u_habilitado_cielo==1) || ($tf_u_habilitado_cielo==-1))) { 
                        $tf_u_habilitado_cielo = 0;
                }

        //tf_u_usuario_vip
        if($msg == "")
                if(!(($tf_u_usuario_vip==1) || ($tf_u_usuario_vip==-1))) { 
                        $tf_u_usuario_vip = 0;
                }

        if($tf_u_com_totais_vendas) {
                // Adiciona opr_codigo ao query
                if($dd_opr_codigo) {
                        $produtos_query .= " and vgm_opr_codigo= ".$dd_opr_codigo." ";
                } else {
                        $tf_produto = null;
                        $tf_pins = null;
                }

                // Processa a seleção de produtos no POST
                if ($tf_produto && is_array($tf_produto)) {
                                if (count($tf_produto) == 1) {
                                        $tf_produto = $tf_produto[0];
                                } else {
                                        $tf_produto = implode("|",$tf_produto);
                                }	
                        }
                if ($tf_produto && $tf_produto != "") {
                        $tf_produto = explode("|",$tf_produto);	
                }

                $i = 0;
                $num_col = count($tf_produto);
                while ($i <= $num_col) {				
                        $filtro['produto'.$i] = $tf_produto[$i];
                        $palavra = urlencode($filtro['produto'.$i]);
                        $varsel .= "&tf_produto[]=".$palavra;
                        $i++;
                }

                // Processa a seleção de valores no POST
                if ($tf_pins && is_array($tf_pins)) {
                                if (count($tf_pins) == 1) {
                                        $tf_pins = $tf_pins[0];
                                } else {
                                        $tf_pins = implode("|",$tf_pins);
                                }	
                        }
                if ($tf_pins && $tf_pins != "") {
                        $tf_pins = explode("|",$tf_pins);	
                }

                $i = 0;
                $num_col_pin = count($tf_pins);
                while ($i <= $num_col_pin) {				
                        $filtro['pin'.$i] = $tf_pins[$i];
                        $palavra = urlencode($filtro['pin'.$i]);
                        $varsel .= "&tf_pins[]=".$palavra;
                        $i++;
                }

                // Adiciona lista de produtos ao query
                if ($filtro['produto0'] != '') {
                        $s = 0;
                        $produtos_query .= " and  ( ";
                        while ($filtro['produto'.$s] != '') {
                                $com .= ", '".$filtro['produto'.$s]."' as produto".$s." ";
                                $produtos_query .= " upper(vgm_nome_produto) = '".strtoupper(str_replace("'", "''", $filtro['produto'.$s]))."' ";
                                $s++;
                                if ($filtro['produto'.$s] != '') $produtos_query .= " or ";
                        }
                        $produtos_query .= ") ";	
                } 
                // Adiciona lista de valores ao query
                if ($filtro['pin0'] != '') {
                        $s = 0;
                        $produtos_query .= " and  ( ";
                        while ($filtro['pin'.$s] != '') {
                                $com .= ", '".$filtro['pin'.$s]."' as pin".$s." ";
                                $produtos_query .= " vgm_valor = '".$filtro['pin'.$s]."' ";
                                $s++;
                                if ($filtro['pin'.$s] != '') $produtos_query .= " or ";
                        }
                        $produtos_query .= ") ";		
                }

        }

        //Endereco
        //------------------------------------------------------------------
        //tf_u_cep
        if($msg == "")
                if($tf_u_cep){
                        if(!is_numeric(str_replace("-","",$tf_u_cep))) $msg = "CEP deve ser numérico.\n";
                }


        //Busca vendas
        //------------------------------------------------------------------------------------------------------------------
        if($msg == ""){

                // 1 => não executar a querie e montar o count(*)
                $somenteContar = 1;
                include $raiz_do_projeto . "includes/gamer/inc_pesquisa_usuarios_sql.php";
                $rs_usuario = SQLexecuteQuery($sql);                        
                $rs_usuario_total = pg_fetch_array($rs_usuario);
                $total_table = $rs_usuario_total['total'];
                // 2 => não executar a querie e montar o select completo
                $somenteContar = 2;
                include $raiz_do_projeto . "includes/gamer/inc_pesquisa_usuarios_sql.php";

                //Ordem
                $sql .= " order by ".$ncamp;
                if($ordem == 1){
                        $sql .= " desc ";
                        $img_seta = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/seta_down.gif";
                } else {
                        $sql .= " asc ";
                        $img_seta = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/seta_up.gif";
                }

                $sql .= " limit ".$max; 
                $sql .= " offset ".$inicial;

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
ob_end_flush();
require_once "/www/includes/bourls.php";
?>
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/global.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port; ?>/js/jquery.mask.min.js"></script>
<script language="javascript">
    $(function(){
       var optDate = new Object();
            optDate.interval = 10000;

        setDateInterval('tf_u_data_inclusao_ini','tf_u_data_inclusao_fim',optDate);
        setDateInterval('tf_u_data_ultimo_acesso_ini','tf_u_data_ultimo_acesso_fim',optDate);
        setDateInterval('tf_u_data_nascimento_ini','tf_u_data_nascimento_fim',optDate);
<?php
        if($tf_u_com_totais_vendas) {
            print "setDateInterval('tf_v_data_inclusao_ini','tf_v_data_inclusao_fim',optDate);";
        }
?>
        
    });
    
function GP_popupAlertMsg(msg) { //v1.0
  document.MM_returnValue = alert(msg);
}
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}

function load_caixas(){

	
	ResetCheckedValue();

	<?php 	$i = 0;
	
	$parametros = ",'tf_produto[]': [";

	while ($i <= $num_col ) {
	?>	
	var tf_produto<?php echo $i?> = "<?php echo $tf_produto[$i]?>" ;	
	<?php	
	$parametros .= "\"$tf_produto[$i]\"";
		$i++;
		if ( $i <= $num_col) {
			$parametros .= ",";
		}
	}

	$parametros .= "]"; ?>
	
	var opr_codigo = 0;
	if(document.getElementById('dd_opr_codigo')) {
		opr_codigo = document.getElementById('dd_opr_codigo').value;
	}
			// values in dd_pin_status start with 'st' to avoid geting null when status = 0
			$.ajax({
				type: "POST",
				url: "/ajax/gamer/ajaxProdutoComPesquisaVendas.php",
				data: {id:+((opr_codigo>0)?opr_codigo:-1)<?php echo $parametros?>},
				beforeSend: function(){
					$('#mostraValores').html("Aguarde...");
				},
				success: function(html){
					//alert('valor');
					$('#mostraValores').html(html);
				},
				error: function(){
					alert('erro valor 1');
				}
			});// fim ajax
}	// fim function 

	function v_precos() {
	

	
	ResetCheckedValuePin();

				<?php 	$i = 0;
	
	$parametros = ",'tf_pins[]': [";

	while ($i < $num_col_pin ) {
		?>	

	var tf_pins<?php echo $i?> = "<?php echo $tf_pins[$i]?>" ;	
				
	<?php	
		
	$parametros .= "'$tf_pins[$i]'";
			$i++;
			if ( $i < $num_col_pin) {
				$parametros .= ",";
			}
	}

	$parametros .= "]"; ?>

	var selectedItems = new Array();

	var opr_codigo = 0;
	if(document.getElementById('dd_opr_codigo')) {
		opr_codigo = document.getElementById('dd_opr_codigo').value;
	}
	
	$.ajax({
				
			type: "POST",
			url: "/ajax/gamer/ajaxTipoComPesquisaVendas.php",
		    data: 
				
				{id:+((opr_codigo>0)?opr_codigo:-1)<?php echo $parametros?>},
					beforeSend: function(){
					$('#mostraValores2').html("Aguarde...");
				},
				success: function(html){
					
					$('#mostraValores2').html(html);
				},
				error: function(){
					alert('erro ao carregar valores 2');
				}

				}); //fim ajax

					
	
		}// fim function reload precos

	function ResetCheckedValue() {
		// reset the $varsel var 'tf_pins'
		if(document.form1) {
			if(document.form1.tf_produto) {
				document.form1.tf_produto.value = '';
			}

			// reset the checkboxes with values 'tf_pins[]'
			var chkObj = document.form1.elements.length;
			var chkLength = chkObj.length;
			for(var i = 0; i < chkLength; i++) {
				var type = document.form1.elements[i].type;
				if(type=="checkbox" && document.form1.elements[i].checked) {
					chkObj[i].checked = false;
				}
			}
		}
	}

	function ResetCheckedValuePin() {
		// reset the $varsel var 'tf_pins'
		if(document.form1) {
			if(document.form1.tf_pins) {
				document.form1.tf_pins.value = '';
			}

			// reset the checkboxes with values 'tf_pins[]'
			var chkObj = document.form1.elements.length;
			var chkLength = chkObj.length;
			for(var i = 0; i < chkLength; i++) {
				var type = document.form1.elements[i].type;
				if(type=="checkbox" && document.form1.elements[i].checked) {
					chkObj[i].checked = false;
				}
			}
		}
	}

	function gerarArquivo() {
		var tf_u_com_totais_vendas = '<?php echo $tf_u_com_totais_vendas?>';
		var dd_opr_codigo = '<?php echo $dd_opr_codigo?>';
		var produtos_query = '<?php echo str_replace("'", "\'", $produtos_query) ?>';
		var tf_u_codigo = '<?php echo $tf_u_codigo?>';
		var tf_u_status = '<?php echo $tf_u_status?>';
		var tf_u_qtde_acessos_ini = '<?php echo $tf_u_qtde_acessos_ini?>';
		var tf_u_qtde_acessos_fim = '<?php echo $tf_u_qtde_acessos_fim?>';
		var tf_u_data_ultimo_acesso_ini = '<?php echo $tf_u_data_ultimo_acesso_ini?>';
		var tf_u_data_ultimo_acesso_fim = '<?php echo $tf_u_data_ultimo_acesso_fim?>';
		var tf_u_data_inclusao_ini = '<?php echo $tf_u_data_inclusao_ini?>';
		var tf_u_data_inclusao_fim = '<?php echo $tf_u_data_inclusao_fim?>';
		var tf_u_nome = '<?php echo $tf_u_nome?>';
		var tf_u_email = '<?php echo $tf_u_email?>';
		var tf_u_cpf = '<?php echo $tf_u_cpf?>';
        var ug_login = '<?php echo $ug_login?>';
		var tf_u_sexo = '<?php echo $tf_u_sexo?>';
		var tf_u_data_nascimento_ini = '<?php echo $tf_u_data_nascimento_ini?>';
		var tf_u_data_nascimento_fim = '<?php echo $tf_u_data_nascimento_fim?>';
		var tf_u_tel_ddi = '<?php echo $tf_u_tel_ddi?>';
		var tf_u_tel_ddd = '<?php echo $tf_u_tel_ddd?>';
		var tf_u_tel = '<?php echo $tf_u_tel?>';
		var tf_u_cel_ddi = '<?php echo $tf_u_cel_ddi?>';
		var tf_u_cel_ddd = '<?php echo $tf_u_cel_ddd?>';
		var tf_u_cel = '<?php echo $tf_u_cel?>';
		var tf_u_endereco = '<?php echo $tf_u_endereco?>';
		var tf_u_bairro = '<?php echo $tf_u_bairro?>';
                var tf_u_observacoes = '<?php echo $tf_u_observacoes?>';
                var tf_u_endereco_ip = '<?php echo $tf_u_endereco_ip?>';
		var tf_u_cidade = '<?php echo $tf_u_cidade?>';
		var tf_u_cep = '<?php echo $tf_u_cep?>';
		var tf_u_estado = '<?php echo $tf_u_estado?>';
		var tf_u_news = '<?php echo $tf_u_news?>';
		var tf_u_compet_aceito_regulamento = '<?php echo $tf_u_compet_aceito_regulamento ?>';
		var tf_u_compet_jogo = '<?php echo $tf_u_compet_jogo?>';
		var tf_u_integracao_origem = '<?php echo $tf_u_integracao_origem?>';
		var ug_flag_usando_saldo = '<?php echo $ug_flag_usando_saldo?>';
                var tf_u_habilitado_cielo = '<?php echo $tf_u_habilitado_cielo?>'; 
                var ug_cadastro_completo = '<?php echo $ug_cadastro_completo ?>';
		$.ajax({
				type: "POST",
				url: "/gamer/usuarios/com_pesquisa_usuarios_arquivo.php",
				data: {
					tf_u_com_totais_vendas:tf_u_com_totais_vendas,
					dd_opr_codigo:dd_opr_codigo,
					produtos_query:produtos_query,
					tf_u_codigo:tf_u_codigo,
					tf_u_status:tf_u_status,
					tf_u_qtde_acessos_ini:tf_u_qtde_acessos_ini,
					tf_u_qtde_acessos_fim:tf_u_qtde_acessos_fim,
					tf_u_data_ultimo_acesso_ini:tf_u_data_ultimo_acesso_ini,
					tf_u_data_ultimo_acesso_fim:tf_u_data_ultimo_acesso_fim,
					tf_u_data_inclusao_ini:tf_u_data_inclusao_ini,
					tf_u_data_inclusao_fim:tf_u_data_inclusao_fim,
					tf_u_nome:tf_u_nome,
					tf_u_email:tf_u_email,
					tf_u_cpf:tf_u_cpf,
                    ug_login: ug_login,
					tf_u_sexo:tf_u_sexo,
					tf_u_data_nascimento_ini:tf_u_data_nascimento_ini,
					tf_u_data_nascimento_fim:tf_u_data_nascimento_fim,
					tf_u_tel_ddi:tf_u_tel_ddi,
					tf_u_tel_ddd:tf_u_tel_ddd,
					tf_u_tel:tf_u_tel,
					tf_u_cel_ddi:tf_u_cel_ddi,
					tf_u_cel_ddd:tf_u_cel_ddd,
					tf_u_cel:tf_u_cel,
					tf_u_endereco:tf_u_endereco,
					tf_u_bairro:tf_u_bairro,
                                        tf_u_endereco_ip:tf_u_endereco_ip,
                                        tf_u_observacoes:tf_u_observacoes,
					tf_u_cidade:tf_u_cidade,
					tf_u_cep:tf_u_cep,
					tf_u_estado:tf_u_estado,
					tf_u_news:tf_u_news,
					tf_u_compet_aceito_regulamento:tf_u_compet_aceito_regulamento,
					tf_u_compet_jogo:tf_u_compet_jogo,
					tf_u_integracao_origem:tf_u_integracao_origem,
					ug_flag_usando_saldo: ug_flag_usando_saldo,
                                        ug_cadastro_completo: ug_cadastro_completo
					},	
				beforeSend: function(){
					$("#area").html("<img src='/images/ajax-loader.gif' />");
				},
				success: function(html){
					$("#area").html(html);
					//alert(html);
				},
				error: function(){
					alert('erro ao carregar valores');
				}				
			});
	}
    
    $(function(){
        $("#cpf").mask("999.999.999-99");
    });
</script>
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
        <form name="form1" method="post" action="com_pesquisa_usuarios.php">
        <table border="0" cellpadding="0" cellspacing="2">
          <tr bgcolor="#F5F5FB"> 
            <td align="right"><input type="submit" name="BtnSearch" value="Buscar" class="btn btn-sm btn-info"></td>
          </tr>
		</table>
        <table class="table top10 txt-preto fontsize-p">
		
          <tr bgcolor="#FFFFFF" class="texto"> 
            <td colspan="6" bgcolor="#ECE9D8">Dados Administrativos</font></td>
          </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td width="100">C&oacute;digo</font></td>
            <td>
              	<input name="tf_u_codigo" type="text" class="form2" value="<?php echo str_replace("'", "", $tf_u_codigo) ?>" size="20">
				<select name="tf_u_codigo_include">
					<option value="1"<?php if ($tf_u_codigo_include=="1") echo " selected"?>>Incluir lista</option>
					<option value="-1"<?php if ($tf_u_codigo_include=="-1") echo " selected"?>>EXCLUIR lista</option>
				</select>

			</td>
            <td>Status</td>
			<td>
                            <select name="tf_u_status" class="form2" width="100" style="width: 100px !important">
					<option value="" <?php if($tf_u_status == "") echo "selected" ?>>Selecione</option>
                                <?php
                                foreach($GLOBALS['STATUS_USUARIO'] as $key => $val) {
                                ?>
                                        <option value='<?php echo $val ?>'<?php echo (($tf_u_status==$val)?" selected":"") ?>><?php echo $GLOBALS['STATUS_USUARIO_LEGENDA'][$val]?></option>
                                <?php
                                } //end foreach
                                ?>
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
            <td>Newsletter</td>
			<td>
				<select name="tf_u_news" class="form2">
					<option value="" <?php if($tf_u_news == "") echo "selected" ?>>Selecione</option>
					<option value="h" <?php if ($tf_u_news == "h") echo "selected";?>>Sim - HTML</option>
					<option value="s" <?php if ($tf_u_news == "s") echo "selected";?>>Sim - Texto</option>
					<option value="n" <?php if ($tf_u_news == "n") echo "selected";?>>Não</option>
				</select>
			</td>
          </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td>Saldo Bloqueado</td>
            <td>
                <input type="checkbox" name="ug_flag_usando_saldo" id="ug_flag_usando_saldo"<?php if($ug_flag_usando_saldo) echo " CHECKED"; ?> value="1">
            </td>
            <td>Situação de cadastro</td>
            <td>
                <select name="ug_cadastro_completo" class="form2" id="ug_cadastro_completo">
                    <option value="0" <?php if ($ug_cadastro_completo == "0" || !isset($ug_cadastro_completo)) echo "selected" ?>>Todos</option>
                    <option value="1" <?php if ($ug_cadastro_completo == "1") echo "selected";?>>Completo</option>
                    <option value="2" <?php if ($ug_cadastro_completo == "2") echo "selected";?>>Incompleto</option>
                </select>
            </td>
          </tr>
		  
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td>Endereço IP</td>
            <td>
                <input name="tf_u_endereco_ip" type="text" class="form2" id="tf_u_endereco_ip" value="<?php echo $tf_u_endereco_ip ?>" size="25" maxlength="100">
            </td>
            <td>Observações</td>
            <td>
                <input name="tf_u_observacoes" type="text" class="form2" id="tf_u_observacoes" value="<?php echo $tf_u_observacoes ?>" size="25" maxlength="100">
            </td>
          </tr>
		  
          <tr bgcolor="#FFFFFF" class="texto"> 
            <td colspan="4" bgcolor="#ECE9D8">Dados Pessoais</font></td>
          </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td>Email</font></td>
            <td>
              	<input name="tf_u_email" type="text" class="form2" value="<?php echo $tf_u_email ?>" size="25" maxlength="100">
			</td>
            <td>Nome</font></td>
            <td>
              	<input name="tf_u_nome" type="text" class="form2" value="<?php echo $tf_u_nome ?>" size="25" maxlength="100">
			</td>
		  </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td>CPF</font></td>
            <td>
                <input name="tf_u_cpf" id="cpf" type="text" class="form2" value="<?php echo $tf_u_cpf ?>" size="25" maxlength="14">
			</td>
            <td>Login</td>
			<td>
				<input name="ug_login" type="text" class="form2" value="<?php echo $ug_login ?>" size="25" maxlength="14">
			</td>
		  </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td>Data de Nascimento</td>
            <td>
              <input name="tf_u_data_nascimento_ini" type="text" class="form" id="tf_u_data_nascimento_ini" value="<?php echo $tf_u_data_nascimento_ini ?>" size="9" maxlength="10">
              a 
              <input name="tf_u_data_nascimento_fim" type="text" class="form" id="tf_u_data_nascimento_fim" value="<?php echo $tf_u_data_nascimento_fim ?>" size="9" maxlength="10">
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
				<select name="tf_u_estado" class="form2">
					<option value="" <?php if($tf_u_estado == "") echo "selected" ?>>Selecione</option>
				<?php for($i=0; $i < count($SIGLA_ESTADOS); $i++){ ?>
					<option value="<?php echo $SIGLA_ESTADOS[$i] ?>" <?php if($tf_u_estado == $SIGLA_ESTADOS[$i]) echo "selected"; ?>><?php echo $SIGLA_ESTADOS[$i] ?></option>
				<?php } ?>
				</select>
			</td>
		  </tr>
          <tr bgcolor="#FFFFFF" class="texto"> 
            <td colspan="4" bgcolor="#ECE9D8">Campeonato</font></td>
          </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td>Cadastrado no campeonato</font></td>
            <td>
				<select name="tf_u_compet_aceito_regulamento" class="form2">
					<option value=""<?php if($tf_u_compet_aceito_regulamento=="") echo " selected" ?>>Todos os usuários</option>
					<option value="s"<?php if(strtolower($tf_u_compet_aceito_regulamento) == "s") echo " selected" ?>>Sim - apenas os cadastrados</option>
					<option value="n"<?php if(strtolower($tf_u_compet_aceito_regulamento) == "n") echo " selected" ?>>Não - apenas os não cadastrados</option>
				</select>
			</td>
			<?php if(strtolower($tf_u_compet_aceito_regulamento) == "s") { ?>
            <td>Jogo escolhido: </td>
            <td>
				<select name="tf_u_compet_jogo" class="form2">
					<option value=""<?php if($tf_u_compet_jogo!=1 && $tf_u_compet_jogo!=2) echo " selected" ?>>Todos os jogos</option>
					<option value="1"<?php if($tf_u_compet_jogo == 1) echo " selected" ?>>Jogo - Fifa</option>
					<option value="2"<?php if($tf_u_compet_jogo == 2) echo " selected" ?>>Jogo - WC3</option>
				</select>
			</td>
			<?php } else { ?>
            <td>&nbsp;</td>
            <td>&nbsp;
			</td>
			<?php } ?>
		  </tr>          

          <tr bgcolor="#FFFFFF" class="texto"> 
            <td colspan="4" bgcolor="#ECE9D8">Integração</font></td>
          </tr>
		  <?php
			// ug_integracao_origem
		  ?>
          <tr bgcolor="#F5F5FB" class="texto"> 
            <td>Integração</font></td>
            <td>
				<select name="tf_u_integracao_origem" class="form2">
					<option value=""<?php if(strtolower($tf_u_integracao_origem) == "") echo " selected" ?>>Todos os Usuários</option>
					<option value="-2"<?php if($tf_u_integracao_origem=="-2") echo " selected" ?>>Apenas Usuários do site</option>
					<option value="-1"<?php if($tf_u_integracao_origem=="-1") echo " selected" ?>>Usuários de Todos os Parceiros de Integração</option>
					<?php
					foreach($partner_list as $key => $val) {
					?>
					<option value='<?php echo $val['partner_id'] ?>'<?php echo (($tf_u_integracao_origem == $val['partner_id'])?" selected":"") ?>> <?php echo $key." (ID: ".$val['partner_id'].") "; ?> </option>
					<?php
					}	
					?>
				</select>
			</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
		  </tr> 
          <tr bgcolor="#FFFFFF" class="texto"> 
            <td colspan="4" bgcolor="#ECE9D8">Gestão de risco</font></td>
          </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
			<?php 
				$sbkg1 = ""; 
				if($tf_u_com_totais_vendas==1 || $tf_u_com_totais_vendas==-1 ) $sbkg1 = " bgcolor='#FFCC66'"; 
			?>
			<td class="texto"<?php echo $sbkg1; ?>>Pagamento Cielo</td>
			<td class="texto"<?php echo $sbkg1; ?>>
				<select name="tf_u_habilitado_cielo" id="tf_u_habilitado_cielo" class="form2">
					<option value="0" <?php if(! (($tf_u_habilitado_cielo == 1) || ($tf_u_habilitado_cielo==-1)))  echo "selected" ?>>Todos os usuários</option>
					<option value="1" <?php if ($tf_u_habilitado_cielo == 1) echo "selected";?>>Apenas os que usam Cielo</option>
					<option value="-1" <?php if ($tf_u_habilitado_cielo == -1) echo "selected";?>>Apenas os que NÃO usam Cielo</option>
				</select>			 
			</td>
			<td class="texto">&nbsp;</td>
			<td class="texto">&nbsp;</td>
		  </tr>
          <tr bgcolor="#FFFFFF" class="texto"> 
            <td colspan="4" bgcolor="#ECE9D8">Vendas</font></td>
          </tr>
          <tr bgcolor="#F5F5FB" class="texto"> 
			<?php 
				$sbkg1 = ""; 
				if($tf_u_com_totais_vendas) $sbkg1 = " bgcolor='#FFCC66'"; 
			?>
			<td class="texto"<?php echo $sbkg1; ?>>Com totais de vendas</td>
			<td class="texto"<?php echo $sbkg1; ?>><input type="checkbox" name="tf_u_com_totais_vendas"<?php if($tf_u_com_totais_vendas) echo " CHECKED"; ?>>
			 <?php if($tf_u_com_totais_vendas) echo " (Com totais de vendas)"; ?>
			</td>
			<td class="texto">Usuários VIP</td>
			<td class="texto">
				<select name="tf_u_usuario_vip" id="tf_u_usuario_vip" class="form2">
					<option value="0" <?php if(! (($tf_u_usuario_vip == 1) || ($tf_u_usuario_vip==-1)))  echo "selected" ?>>Todos os usuários</option>
					<option value="1" <?php if ($tf_u_usuario_vip == 1) echo "selected";?>>Apenas os VIP</option>
					<option value="-1" <?php if ($tf_u_usuario_vip == -1) echo "selected";?>>Apenas os que NÃO são VIP</option>
				</select>			 
				&nbsp;</td>
		  </tr>

			<?php 
				if($tf_u_com_totais_vendas) {
			?>

          <tr bgcolor="#F5F5FB" class="texto"> 
            <td>Data de Conciliação das Vendas</td>
            <td>
              <input name="tf_v_data_inclusao_ini" type="text" class="form" id="tf_v_data_inclusao_ini" value="<?php echo $tf_v_data_inclusao_ini ?>" size="9" maxlength="10">
              a 
              <input name="tf_v_data_inclusao_fim" type="text" class="form" id="tf_v_data_inclusao_fim" value="<?php echo $tf_v_data_inclusao_fim ?>" size="9" maxlength="10">
			</td>
            <td colspan="2"></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td height="40" class="texto">Operadora</td>
            <td> <select name="dd_opr_codigo" id="dd_opr_codigo" class="combo_normal">
                <option value="">Selecione a Operadora</option>
				<?php
				$sql  = "select opr_codigo, opr_nome from operadoras where opr_status='1' and opr_importa=1 order by opr_nome";
				$resopr = SQLexecuteQuery($sql);
				$a_opr = array();
				while ($pgopr = pg_fetch_array($resopr)) { 
					$operadora_nome = $pgopr['opr_nome'];
					$operadora_codigo = $pgopr['opr_codigo'];
					if ($dd_opr_codigo == $operadora_codigo ) {
						$select = "selected = 'selected' ";
					} else {
						$select = '';
					}
				?>
				<option value='<?php echo $operadora_codigo?>' <?php echo $select?>><?php echo $operadora_nome?>(<?php echo $operadora_codigo?>)</option>
				
				<?php }
				//ksort($a_opr); 
				 //foreach($a_opr as $key => $val) { ?>
               
                <?php //} ?>
            </select>&nbsp;</td>
            <td class="texto">Produtos</td>
            <td class="texto"><div id='mostraValores'>*</div></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td height="40" class="texto">&nbsp;</td>
            <td>&nbsp;</td>
            <td class="texto">Valor:</td>
            <td class="texto"><div id='mostraValores2'>*</div></td>
          </tr>
			<?php 
			}
			?>
		  
		</table>

        <table border="0" cellpadding="0" cellspacing="2">
          <tr bgcolor="#F5F5FB"> 
            <td align="right"><input type="submit" name="BtnSearch" value="Buscar" class="btn btn-sm btn-info"></td>
          </tr>
          <?php if($msg != ""){?><tr class="texto"><td align="center"><br><br><font color="#FF0000"><?php echo $msg?></font></td></tr><?php }?>
		</table>
		</form>
    </td></tr></table>
    <table class="table fontsize-pp txt-preto"><tr><td>
		<?php if($total_table > 0) { ?>
        <table border="0" cellpadding="0" cellspacing="2">
                <tr bgcolor="#00008C"> 
                  <td colspan="3" bgcolor="#FFFFFF"> 
                      <table class="table">
				  	  <tr> 
						<td colspan="20" class="texto"> 
                          Exibindo resultados <strong><?php echo $inicial + 1 ?></strong> 
                          a <strong><?php echo $reg_ate ?></strong> de <strong><?php echo $total_table ?></strong></font> 
                        </td>
					  </tr>
					  <tr>
						<td colspan="20" bgcolor="#CCCCCC" id="area" class='texto' align="center"><div id="download" onClick="gerarArquivo();" onMouseOver="this.style.backgroundColor='#CCFF99'" onMouseOut="this.style.backgroundColor='#CCCCCC'"><strong>Gerar Arquivo</strong></div></td>
					  </tr>
					  <?php $ordem = ($ordem == 1)?2:1; ?>
                      <tr  bgcolor="#ECE9D8"> 
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_id&inicial=".$inicial.$varsel ?>" class="link_branco" ><font class="texto">C&oacute;d.</font></a> 
                          <?php if($ncamp == 'ug_id') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_ativo&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Status</font></a>
                          <?php if($ncamp == 'ug_ativo') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_nome&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Nome</font></a>
                          <?php if($ncamp == 'ug_nome') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_email&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Email</font></a>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_data_inclusao&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Data de Cadastro</font></a>
                          <?php if($ncamp == 'ug_email') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_cpf&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">CPF</font></a>
                          <?php if($ncamp == 'ug_cpf') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                          </strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_login&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto">Login</font></a><?php if($ncamp == 'ug_login') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
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
                        <td align="center"><strong><font class="texto">Vendas</font></a>                          
                          </strong></td>
                        <td align="center"><strong><font class="texto">Saldo</font></a>                          
                          </strong></td>
						<?php if(strtolower($tf_u_compet_aceito_regulamento) != "") { ?>
                        <td align="center"><strong><font class="texto">Jogo</font></a>
                          </strong></td>
						<?php } ?>
						<?php if(strtolower($tf_u_integracao_origem) != "" && $tf_u_integracao_origem!="-2") { ?>
                        <td align="center"><strong><font class="texto">Store_ID</font></a>                          
                          </strong></td>
						<?php } ?>
						<?php 
							if($tf_u_com_totais_vendas ) {	// && $dd_opr_codigo
						?>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=vg_valor&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto"><nobr>Vendas R$</nobr></font></a>
                          <?php if($ncamp == 'vg_valor') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
						</strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=vg_qtde_itens&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto"><nobr>n Vendas</nobr></font></a>
                          <?php if($ncamp == 'vg_qtde_itens') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
						</strong></td>
                        <td align="center"><strong><font class="texto"><nobr>Ticket médio</nobr>
						</strong></td>
                        <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=vg_data_ultima_venda&inicial=".$inicial.$varsel ?>" class="link_branco"><font class="texto"><nobr>Data última venda</nobr></font></a>
                          <?php if($ncamp == 'vg_data_ultima_venda') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
						</strong></td>
						<?php 
							}
						?>
                            <td align="center"><strong><font class="texto"><nobr>VIP?</nobr></td>
                            <td align="center"><strong><font class="texto"><nobr>Data de Nascimento</td>
                      </tr>
					<?php 
						$cor1 = $query_cor1;
						$cor2 = $query_cor1;
						$cor3 = $query_cor2;
						while($rs_usuario_row = pg_fetch_array($rs_usuario)){
							$cor1 = ($cor1 == $cor2)?$cor3:$cor2;
					?>
                      <tr bgcolor="<?php echo $cor1 ?>" class="texto" onmouseover="bgColor='#CFDAD7'" onmouseout="bgColor='<?php echo $cor1 ?>'"> 
                        <td align="center"><a style="text-decoration:none" href="com_usuario_detalhe.php?usuario_id=<?php echo $rs_usuario_row['ug_id'] ?>"><?php echo $rs_usuario_row['ug_id'] ?></a></td>
                        <td align="center"><?php echo  $GLOBALS['STATUS_USUARIO_LEGENDA'][$rs_usuario_row['ug_ativo']] ?></td>
                        <td><a style="text-decoration:none" href="com_usuario_detalhe.php?usuario_id=<?php echo $rs_usuario_row['ug_id'] ?>"><?php echo $rs_usuario_row['ug_nome'] ?></a></td>
                        <td><?php echo $rs_usuario_row['ug_email'] ?></td>
                        <td><?php echo substr($rs_usuario_row['ug_data_inclusao'], 0, 10) ?></td>
                        <td nowrap><?php echo $rs_usuario_row['ug_cpf'] ?></td>
                        <td><?php echo $rs_usuario_row['ug_login'] ?></td>
                        <td><?php echo $rs_usuario_row['ug_endereco'] ?>, <?php echo $rs_usuario_row['ug_numero'] ?> <?php echo $rs_usuario_row['ug_complemento'] ?> </td>
                        <td><?php echo $rs_usuario_row['ug_bairro'] ?></td>
                        <td><?php echo $rs_usuario_row['ug_cidade'] ?></td>
                        <td><?php echo $rs_usuario_row['ug_estado'] ?></td>
                        <td nowrap><?php echo $rs_usuario_row['ug_cep'] ?></td>
                        <td align="center"><a style="text-decoration:none" href="com_pesquisa_vendas.php?tf_u_codigo=<?php echo $rs_usuario_row['ug_id'] ?>&BtnSearch=1&tf_v_data_inclusao_fim=<?php echo date("d/m/Y");?>&tf_v_data_inclusao_ini=01/01/2008" target="_blank">Click</a></td>
                        <td align="right"><?php echo number_format($rs_usuario_row['ug_perfil_saldo'], 2, '.', '.') ?></td>

						<?php if(strtolower($tf_u_compet_aceito_regulamento) != "") { ?>
	                        <td><?php echo (($rs_usuario_row['ug_compet_jogo']==1)?"Fifa":(($rs_usuario_row['ug_compet_jogo']==2)?"WC3":"???")); ?></td>
						<?php } ?>
						<?php if(strtolower($tf_u_integracao_origem) != "" && $tf_u_integracao_origem!="-2") { ?>
	                        <td align='right' title='cadastrado em: <?php echo substr($rs_usuario_row['ug_data_inclusao'], 0,19); ?>'><?php echo getPartner_name_By_ID($rs_usuario_row['ug_integracao_origem'])."&nbsp;(".$rs_usuario_row['ug_integracao_origem'].")"; ?></td>
						<?php } ?>
						<?php 
							if($tf_u_com_totais_vendas ) {	// && $dd_opr_codigo
								$vg_qtde_itens = (($rs_usuario_row['vg_qtde_itens']>0)?$rs_usuario_row['vg_qtde_itens']:1);
						?>
                        <td align="right"><?php echo number_format($rs_usuario_row['vg_valor'], 2, '.', '.') ?></td>
                        <td align="right"><?php echo $vg_qtde_itens ?></td>
                        <td align="right"><?php echo number_format($rs_usuario_row['vg_valor']/$vg_qtde_itens, 2, '.', '.') ?></td>
                        <td align="right" title="Primeira venda: '<?php echo substr($rs_usuario_row['vg_data_primeira_venda'], 0, 19) ?>'"><nobr><?php echo substr($rs_usuario_row['vg_data_ultima_venda'], 0, 19) ?></nobr></td>
						<?php
								
							}
						?>
                        <td align="center"><?php echo ((in_array($rs_usuario_row['ug_id'], $a_lista_usuarios_VIP))?"<font color='blue'>VIP</font>":"<font color='#D3D3D3'>não</font>") ?></td>
                        <td align="center"><?php echo substr($rs_usuario_row['ug_data_nascimento'], 0, 10); ?></td>
                      </tr>
					<?php 	}	?>
                      <tr> 
                        <td colspan="20" bgcolor="#FFFFFF" class="texto"><?php echo $search_msg . number_format(getmicrotime() - $time_start, 2, '.', '.') . $search_unit ?></td>
                      </tr>
					<?php paginacao_query($inicial, $total_table, $max, 20, $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel); ?>
                    </table>
				  </td>
                </tr>
              </table>
          <?php }  ?>
    </td>
  </tr>
</table>
<?php
			$meu_ip = '189.62.151.212';
					
			if ($_SERVER['REMOTE_ADDR'] == $meu_ip) echo $sql; 		  
			
		  ?>
<?php 
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
<script>

<?php
	if($tf_u_com_totais_vendas ) {	//&& $dd_opr_codigo
?>
	$(document).ready(function () {
		load_caixas(); 
		v_precos();
	});
<?php
	}
?>

$(document).ready(function () {
	$('#dd_opr_codigo').change( function() { 
		load_caixas(); 
		v_precos();
	});
});
</script>
</html>
