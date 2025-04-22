<?php

header("Content-Type: text/html; charset=ISO-8859-1",true);
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
require_once "/www/includes/bourls.php";

set_time_limit(3600);
$time_start = getmicrotime();

if(!$ncamp)    $ncamp       = 'ug_id';
if(!$inicial)  $inicial     = 0;
if(!$range)    $range       = 1;
if(!$ordem)    $ordem       = 0;
if($BtnSearch=="Buscar") {
        $inicial     = 0;
        $range       = 1;
        $total_table = 0; 
}

$default_add  = nome_arquivo($PHP_SELF);
$img_proxima  = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/proxima.gif";
$img_anterior = "https://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/images/anterior.gif";
$max          = 100;    //$qtde_reg_tela;
$range_qtde   = $qtde_range_tela;

$tf_u_endereco = str_replace("_", " ", $tf_u_endereco);
$tf_u_bairro = str_replace("_", " ", $tf_u_bairro);
$tf_u_cidade = str_replace("_", " ", $tf_u_cidade);

$tf_u_endereco_mod = str_replace(" ", "_", $tf_u_endereco);
$tf_u_bairro_mod = str_replace(" ", "_", $tf_u_bairro);
$tf_u_cidade_mod = str_replace(" ", "_", $tf_u_cidade);

$tf_u_status_busca = (($tf_u_status_busca==1)?1:(($tf_u_status_busca==2)?2:0));

$varsel = "&BtnSearch=1";
$varsel .= "&tf_u_codigo=$tf_u_codigo&tf_u_status=$tf_u_status&tf_u_status_busca=$tf_u_status_busca";
$varsel .= "&tf_u_qtde_acessos_ini=$tf_u_qtde_acessos_ini&tf_u_qtde_acessos_fim=$tf_u_qtde_acessos_fim";
$varsel .= "&tf_u_data_ultimo_acesso_ini=$tf_u_data_ultimo_acesso_ini&tf_u_data_ultimo_acesso_fim=$tf_u_data_ultimo_acesso_fim";
$varsel .= "&tf_u_data_inclusao_ini=$tf_u_data_inclusao_ini&tf_u_data_inclusao_fim=$tf_u_data_inclusao_fim";
$varsel .= "&tf_u_data_aprovacao_ini=$tf_u_data_aprovacao_ini&tf_u_data_aprovacao_fim=$tf_u_data_aprovacao_fim";
$varsel .= "&ug_data_expiracao_senha_ini=$ug_data_expiracao_senha_ini&ug_data_expiracao_senha_fim=$ug_data_expiracao_senha_fim";
$varsel .= "&tf_u_login=$tf_u_login";
$varsel .= "&tf_u_nome_fantasia=$tf_u_nome_fantasia&tf_u_razao_social=$tf_u_razao_social";
$varsel .= "&tf_u_cnpj=$tf_u_cnpj&tf_ug_te_id=$tf_ug_te_id&tf_ug_te_id_ativo=$tf_ug_te_id_ativo";
$varsel .= "&tf_u_responsavel=$tf_u_responsavel&tf_u_email=$tf_u_email";
$varsel .= "&tf_u_tipo_cadastro=$tf_u_tipo_cadastro&tf_u_nome=$tf_u_nome&tf_u_cpf=$tf_u_cpf&tf_u_rg=$tf_u_rg&tf_u_sexo=$tf_u_sexo";
$varsel .= "&tf_u_data_nascimento_ini=$tf_u_data_nascimento_ini&tf_u_data_nascimento_fim=$tf_u_data_nascimento_fim";
$varsel .= "&tf_u_tel_ddi=$tf_u_tel_ddi&tf_u_tel_ddd=$tf_u_tel_ddd&tf_u_tel=$tf_u_tel";
$varsel .= "&tf_u_cel_ddi=$tf_u_cel_ddi&tf_u_cel_ddd=$tf_u_cel_ddd&tf_u_cel=$tf_u_cel";
$varsel .= "&tf_u_fax_ddi=$tf_u_fax_ddi&tf_u_fax_ddd=$tf_u_fax_ddd&tf_u_fax=$tf_u_fax";
$varsel .= "&tf_u_ra_codigo=$tf_u_ra_codigo&tf_u_ra_outros=$tf_u_ra_outros";
$varsel .= "&tf_u_contato01_nome=$tf_u_contato01_nome&tf_u_contato01__cargo=$tf_u_contato01_cargo";
$varsel .= "&tf_u_contato01_tel_ddi=$tf_u_contato01_tel_ddi&tf_u_contato01_tel_ddd=$tf_u_contato01_tel_ddd&tf_u_contato01_tel=$tf_u_contato01_tel";
$varsel .= "&tf_u_risco_classif=$tf_u_risco_classif";
$varsel .= "&tf_u_usuarios_cartao=$tf_u_usuarios_cartao";
$varsel .= "&tf_u_usuarios_novos=$tf_u_usuarios_novos";
	
$varsel .= "&tf_u_substatus=$tf_u_substatus";
$varsel .= "&tf_u_endereco=$tf_u_endereco&tf_u_bairro=$tf_u_bairro&tf_u_cidade=$tf_u_cidade_mod&tf_u_cep=$tf_u_cep&tf_u_estado=$tf_u_estado";
$varsel .= "&tf_u_computadores_qtde=$tf_u_computadores_qtde&tf_u_fatura_media_mensal=$tf_u_fatura_media_mensal";
$varsel .= "&tf_u_saldo_positivo=$tf_u_saldo_positivo&tf_u_compet_participa=$tf_u_compet_participa&tf_u_sem_dados_cadastro=$tf_u_sem_dados_cadastro";
$varsel .= "&tf_gmaps=$tf_gmaps";
$varsel .= "&tf_u_com_totais_vendas=$tf_u_com_totais_vendas&dd_opr_codigo=$dd_opr_codigo";
$varsel .= "&tf_v_data_inclusao_ini=$tf_v_data_inclusao_ini&tf_v_data_inclusao_fim=$tf_v_data_inclusao_fim";
// NexCafe
$varsel .= "&tf_u_login_nexcafe=$tf_u_login_nexcafe&tf_u_login_automatico_nexcafe=$tf_u_login_automatico_nexcafe";
$varsel .= "&tf_u_data_adesao_nexcafe_ini=$tf_u_data_adesao_nexcafe_ini&tf_u_data_adesao_nexcafe_fim=$tf_u_data_adesao_nexcafe_fim";
//VIP
$varsel .= "&tf_u_vip=$tf_u_vip";
//Possui Restrição de Vendas de Produtos
$varsel .= "&tf_ug_possui_restricao_produtos=$tf_ug_possui_restricao_produtos";
//echo "varsel: ".$varsel."<br>";
//
// Tipo Venda
$varsel .= "&tf_u_tipo_venda=$tf_u_tipo_venda";

$sqlorigem = "select ug_ficou_sabendo, count(*) as n from dist_usuarios_games where not ug_ficou_sabendo='' group by ug_ficou_sabendo order by ug_ficou_sabendo";
//echo $sqlorigem."<br>";
$resorigem = SQLexecuteQuery($sqlorigem);

$msg = "";

    //Processa Acoes
if($msg == ""){

        //Excluir usuario
        if($acao && $acao == "e"){

                $msgAcao = "";

                if(!$usuario_id || !is_numeric($usuario_id)) $msgAcao = "Código do usuário inválido.\n";

                //Inicia transacao
                if($msgAcao == ""){  
                        $sql = "BEGIN TRANSACTION ";
                        $ret = SQLexecuteQuery($sql);
                        if(!$ret) $msgAcao = "Erro ao iniciar transação.\n";
                }

                if($msgAcao == ""){
                        $sql = "select count(*) as qtde from tb_dist_venda_games where vg_ug_id = $usuario_id";
                        $rs = SQLexecuteQuery($sql);
                        if(!$rs || pg_num_rows($rs) == 0) $msgAcao = "Erro ao pesquisar pedidos do usuário.\n";
                        else {
                                $rs_row = pg_fetch_array($rs);
                                $qtde = $rs_row['qtde'];
                                if($qtde > 0) $msgAcao = "Usuário possui pedidos, não pode ser excluído.\n";
                        }
                }

                if($msgAcao == ""){
                        $sql = "select count(*) as qtde from cortes where cor_ug_id = $usuario_id";
                        $rs = SQLexecuteQuery($sql);
                        if(!$rs || pg_num_rows($rs) == 0) $msgAcao = "Erro ao pesquisar cortes do usuário.\n";
                        else {
                                $rs_row = pg_fetch_array($rs);
                                $qtdecortes = $rs_row['qtde'];  
                                if($qtdecortes > 0) {  
                                        if($qtde == 0) {    // Possui cortes mas não possui pedidos => exclui
                                                $sql = "delete from cortes where cor_ug_id = $usuario_id";
                                                $ret = SQLexecuteQuery($sql);
                                                if(!$ret) $msgAcao = "Erro ao deletar cortes do usuário.\n";
                                        }
                                }
                        }
                }

                if($msgAcao == ""){
                        $sql = "delete from dist_usuarios_games_log where ugl_ug_id = $usuario_id";
                        $ret = SQLexecuteQuery($sql);
                        if(!$ret) $msgAcao = "Erro ao deletar log do usuário.\n";
                }
                if($msgAcao == ""){
                        $sql = "delete from dist_usuarios_games where ug_id = $usuario_id";
//echo "sql: $sql<br>";
                        $ret = SQLexecuteQuery($sql);
                        if(!$ret) $msgAcao = "Erro ao deletar usuário.\n";
                }
                //Finaliza transacao
                if($msgAcao == ""){
                                $sql = "COMMIT TRANSACTION ";
                                $ret = SQLexecuteQuery($sql);
                                if(!$ret) $msgAcao = "Erro ao comitar transação.\n";
                } else {
                                $sql = "ROLLBACK TRANSACTION ";
                                $ret = SQLexecuteQuery($sql);
                                if(!$ret) $msgAcao = "Erro ao dar rollback na transação.\n";
                }

        }
}

if(isset($BtnSearch)){

        $produtos_query = "";
        //Validacao
        //------------------------------------------------------------------------------------------------------------------
        $msg = "";

        //Dados administrativos
        //------------------------------------------------------------------
        //codigo
        if($msg == "")
            if($tf_u_codigo){
//                if(!is_numeric($tf_u_codigo)) $msg = "Código deve ser numérico.\n";
                if(!is_csv_numeric_global($tf_u_codigo, 1)) {
                    $msg = "Código da venda deve ser numérico ou lista de números separada por vírgulas.\n";
                }
            }
        //Data inclusao de venda
        if($msg == "")
            if($tf_v_data_inclusao_ini || $tf_v_data_inclusao_fim){
                if(verifica_data($tf_v_data_inclusao_ini) == 0)    {
                    $msg = "A data de inclusão inicial da venda é inválida.\n";
                } else {
                    $produtos_query .= " and vg_data_inclusao >= '".formata_data($tf_v_data_inclusao_ini, 1)." 00:00:00' ";
                }
                if(verifica_data($tf_v_data_inclusao_fim) == 0)    {
                    $msg = "A data de inclusão final da venda é inválida.\n";
                } else {
                    $produtos_query .= " and vg_data_inclusao <= '".formata_data($tf_v_data_inclusao_fim, 1)." 23:59:59' ";
                }
            }

        //Qtde acessos
        if($msg == "")
            if($tf_u_qtde_acessos_ini || $tf_u_qtde_acessos_fim){
                if(!is_numeric($tf_u_qtde_acessos_ini))    $msg = "Qtde de Acessos inicial deve ser numérico.\n";
                if(!is_numeric($tf_u_qtde_acessos_fim))    $msg = "Qtde de Acessos final deve ser numérico.\n";
            }
        //Data_ultimo_acesso
        if($msg == "")
            if($tf_u_data_ultimo_acesso_ini || $tf_u_data_ultimo_acesso_fim){
                if(verifica_data($tf_u_data_ultimo_acesso_ini) == 0)    $msg = "A Data Último Acesso inicial é inválida.\n";
                if(verifica_data($tf_u_data_ultimo_acesso_fim) == 0)    $msg = "A Data Último Acesso final é inválida.\n";
            }
        //Data de Cadastro
        if($msg == "")
            if($tf_u_data_inclusao_ini || $tf_u_data_inclusao_fim){
                if(verifica_data($tf_u_data_inclusao_ini) == 0)    $msg = "A Data de Cadastro inicial é inválida.\n";
                if(verifica_data($tf_u_data_inclusao_fim) == 0)    $msg = "A Data de Cadastro fina é inválida.\n";
            }
            if($tf_u_data_aprovacao_ini || $tf_u_data_aprovacao_fim){
                if(verifica_data($tf_u_data_aprovacao_ini) == 0)    $msg = "A Data de Aprovação inicial é inválida.\n";
                if(verifica_data($tf_u_data_aprovacao_fim) == 0)    $msg = "A Data de Cadastro fina é inválida.\n";
            }
            
        //Data de Cadastro
        if($msg == "")
            if($ug_data_expiracao_senha_ini || $ug_data_expiracao_senha_fim){
                if(verifica_data($ug_data_expiracao_senha_ini) == 0)    $msg = "A Data de expiração de senha inicial é inválida.\n";
                if(verifica_data($ug_data_expiracao_senha_fim) == 0)    $msg = "A Data de expiração de senha final é inválida.\n";
            }
            if($ug_data_expiracao_senha_ini || $ug_data_expiracao_senha_fim){
                if(verifica_data($ug_data_expiracao_senha_ini) == 0)    $msg = "A Data de expiração de senha inicial é inválida.\n";
                if(verifica_data($ug_data_expiracao_senha_fim) == 0)    $msg = "A Data de expiração de senha final é inválida.\n";
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
                if(verifica_data($tf_u_data_nascimento_ini) == 0)    $msg = "A Data de Nascimento inicialé inválida.\n";
                if(verifica_data($tf_u_data_nascimento_fim) == 0)    $msg = "A Data de Nascimento final é inválida.\n";
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

        // NexCafe
        // Data de Adesao ao NexCafe
        if($msg == "")
            if($tf_u_data_adesao_nexcafe_ini || $tf_u_data_adesao_nexcafe_fim){
                if(verifica_data($tf_u_data_adesao_nexcafe_ini) == 0)    $msg .= "A Data Inicial de Adesão Ao NexCafé é inválida.\n";
                if(verifica_data($tf_u_data_adesao_nexcafe_fim) == 0)    $msg .= "A Data Final de Adesão Ao NexCafé é inválida.\n";
        }

        if(($msg == "") && $tf_u_com_totais_vendas) {
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
                    $produtos_query .= " upper(vgm_nome_produto) = '".str_replace("'", "''", strtoupper($filtro['produto'.$s]))."' ";
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

          
//echo "tf_u_substatus: '".$tf_u_substatus."'<br>";
        //Busca vendas
        //------------------------------------------------------------------------------------------------------------------
        if($msg == ""){
			
			$tf_decode = false;
            require_once $raiz_do_projeto . "includes/pdv/inc_pesquisa_usuarios_sql.php";

            $total_table = pg_num_rows($rs_usuario);

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

    //RA
    $resatv = SQLexecuteQuery("select ra_codigo, ra_desc from ramo_atividade order by ra_desc");

ob_end_flush();
?>
<link href="<?php echo $url; ?>:<?php echo $server_port ;?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port ;?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url;?>:<?php echo $server_port ;?>/js/global.js"></script>
<script language="javascript">
    function toggleTotaisVendas(){
        if($("#tf_u_com_totais_vendas").is(":checked")){
            $(".contotaisvendas").fadeIn("slow");
        }else{
            $("#tf_v_data_inclusao_ini").val("");
            $("#tf_v_data_inclusao_fim").val("");
            $("#dd_opr_codigo").val("");
            $(".contotaisvendas").fadeOut("slow");
        }
    }
    
    $(function(){
        
        toggleTotaisVendas();
        
       var optDate = new Object();
            optDate.interval = 10000;

        setDateInterval('tf_u_data_nascimento_ini','tf_u_data_nascimento_fim',optDate);
        setDateInterval('tf_u_data_inclusao_ini','tf_u_data_inclusao_fim',optDate);
        setDateInterval('tf_u_data_aprovacao_ini','tf_u_data_aprovacao_fim',optDate);
        setDateInterval('ug_data_expiracao_senha_ini','ug_data_expiracao_senha_fim',optDate);
        setDateInterval('tf_u_data_ultimo_acesso_ini','tf_u_data_ultimo_acesso_fim',optDate);
        setDateInterval('tf_v_data_inclusao_ini','tf_v_data_inclusao_fim', optDate);
        
        $("#tf_u_com_totais_vendas").click(function(){
            
            toggleTotaisVendas();
        });
        
<?php
    if($tf_u_com_totais_vendas) 
    {
?>
        setDateInterval('tf_v_data_inclusao_ini','tf_v_data_inclusao_fim',optDate);
<?php
    }
?>        
        //setDateInterval('tf_u_data_adesao_nexcafe_ini','tf_u_data_adesao_nexcafe_fim',optDate);
    });
    
    function validaGeo(ug_tipo_end, ug_endereco, ug_bairro, ug_cidade, ug_id, ug_pais, ug_cep, ug_estado, ug_numero) {
        var ug_tipo_end = ug_tipo_end;
        var ug_endereco = ug_endereco;
        var ug_bairro   = ug_bairro;
        var ug_cidade   = ug_cidade;
        var ug_estado    = ug_estado;
        var ug_cep        = ug_cep;
        var ug_numero    = ug_numero;
        ug_cep            = ug_cep.replace("-", "");

        var ug_id        = eval(ug_id);
        //var endereco    = ug_endereco+', '+ug_cidade+', '+ug_bairro;

        if(ug_numero != '') {
            if(ug_tipo_end == '') {
                var endereco    = ug_endereco+', '+ug_numero+', '+ug_cidade+', '+ug_estado;
            } else {
                var endereco    = ug_tipo_end+' '+ug_endereco+', '+ug_numero+', '+ug_cidade+', '+ug_estado;
            }
        } else {
            if(ug_tipo_end == '') {
                var endereco    = ug_endereco+', '+ug_cidade+', '+ug_estado;
            } else {
                var endereco    = ug_tipo_end+' '+ug_endereco+', '+ug_cidade+', '+ug_estado;
            }
        }

//        alert(ug_numero);
//        alert('ug_endereco: '+ug_endereco);
//        alert('ug_bairro: '+ug_bairro);
//        alert('ug_cidade: '+ug_cidade);
//        alert('ug_estado: '+ug_estado);
//        alert('ug_id: '+ug_id);
//        alert('ug_cep: '+ug_cep);
//        alert('Endereço montado: '+endereco);

        window.open ("/pdv/geobusca/geobusca.php?endereco="+endereco+'&ug_id='+ug_id+'&ug_cep='+ug_cep,"geobusca");
    }

function load_caixas(){


    ResetCheckedValue();

    <?php     $i = 0;

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
                data: {id:+((opr_codigo>0)?opr_codigo:-1)<?php echo $parametros; ?>},
                beforeSend: function(){
                    $('#mostraValores').html("Aguarde...");
                },
                success: function(html){
                    //alert('valor');
                    $('#mostraValores').html(html);
                },
                error: function(){
                    alert('erro valor');
                }
            });// fim ajax
        }    // fim function

    function v_precos() {



    ResetCheckedValuePin();

                <?php     $i = 0;

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
                    alert('erro ao carregar valores');
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

function GP_popupAlertMsg(msg) { //v1.0
  document.MM_returnValue = alert(msg);
}

function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}

function mudarSelect() {
    var x = document.getElementById('tf_u_substatus');
    var y = '';
    var ativos = new Array(2);
    ativos[0] = 'Selecione'; ativos[1] = 'Ainda não fez 1º compra';
    var inativos = new Array(2);
    inativos[0] = 'Selecione'; inativos[1] = 'Pendente de Contato e Análise'; inativos[2] = 'Loja não Localizada'; inativos[3] = 'Representante Divergente'; inativos[4] = 'Cadastro não Aprovado'; inativos[5] = 'Sem Interesse'; inativos[6] = 'Não quer mais vender'; inativos[7] = 'Bloqueado por fraude'; inativos[8] = 'Pré-Cadastro/Prospecção';

    for(var i=x.length-1;i>=0;i--)
        x.remove(x[i]);

    if (document.getElementById('tf_u_status').selectedIndex == 1) {
        y = document.createElement('option'); y.text = ativos[0]; y.value = '';
        try { x.add(y,null); } catch(ex) { x.add(y); }
        y = document.createElement('option'); y.text = ativos[1]; y.value = '9';
        try { x.add(y,null); } catch(ex) { x.add(y); }
    } else if (document.getElementById('tf_u_status').selectedIndex == 2) {
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
    var ug_data_expiracao_senha_ini = '<?php echo $ug_data_expiracao_senha_ini?>';
    var ug_data_expiracao_senha_fim = '<?php echo $ug_data_expiracao_senha_fim?>';
    var tf_u_data_aprovacao_ini = '<?php echo $tf_u_data_aprovacao_ini?>';
    var tf_u_data_aprovacao_fim = '<?php echo $tf_u_data_aprovacao_fim?>';
    var tf_u_nome = '<?php echo $tf_u_nome?>';
    var tf_u_email = '<?php echo $tf_u_email?>';
    var tf_u_cpf = '<?php echo $tf_u_cpf?>';
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
    var tf_u_cidade = '<?php echo $tf_u_cidade?>';
    var tf_u_cep = '<?php echo $tf_u_cep?>';
    var tf_u_estado = '<?php echo $tf_u_estado?>';
    var tf_u_news = '<?php echo $tf_u_news?>';
    var tf_u_compet_aceito_regulamento = '<?php echo $tf_u_compet_aceito_regulamento?>';
    var tf_u_compet_jogo = '<?php echo $tf_u_compet_jogo?>';
    var tf_u_integracao_origem = '<?php echo $tf_u_integracao_origem?>';
    var tf_u_status_busca = '<?php echo (($tf_u_status_busca==1)?1:(($tf_u_status_busca==2)?2:0))?>';
    var tf_u_substatus = '<?php echo $tf_u_substatus?>';
    var tf_u_login = '<?php echo $tf_u_login?>';
    var tf_u_nome_fantasia = '<?php echo $tf_u_nome_fantasia?>';
    var tf_u_razao_social = '<?php echo $tf_u_razao_social?>';
    var tf_u_cnpj = '<?php echo $tf_u_cnpj?>';
    var tf_ug_te_id = '<?php echo $tf_ug_te_id?>';
    var tf_ug_te_id_ativo = '<?php echo $tf_ug_te_id_ativo?>';
    var tf_u_responsavel = '<?php echo $tf_u_responsavel?>';
    var tf_u_site = '<?php echo $tf_u_site?>';
    var tf_u_tipo_cadastro = '<?php echo $tf_u_tipo_cadastro?>';
    var tf_u_rg = '<?php echo $tf_u_rg?>';
    var tf_u_fax_ddi = '<?php echo $tf_u_fax_ddi?>';
    var tf_u_fax_ddd = '<?php echo $tf_u_fax_ddd?>';
    var tf_u_fax = '<?php echo $tf_u_fax?>';
    var tf_u_ra_codigo = '<?php echo $tf_u_ra_codigo?>';
    var tf_u_ra_outros = '<?php echo $tf_u_ra_outros?>';
    var tf_u_contato01_nome = '<?php echo $tf_u_contato01_nome?>';
    var tf_u_contato01_cargo = '<?php echo $tf_u_contato01_cargo?>';
    var tf_u_contato01_tel_ddi = '<?php echo $tf_u_contato01_tel_ddi?>';
    var tf_u_contato01_tel_ddd = '<?php echo $tf_u_contato01_tel_ddd?>';
    var tf_u_contato01_tel = '<?php echo $tf_u_contato01_tel?>';
    var tf_u_risco_classif = '<?php echo $tf_u_risco_classif?>';
    var tf_u_saldo_positivo = '<?php echo $tf_u_saldo_positivo?>';
    var tf_u_usuarios_cartao = '<?php echo $tf_u_usuarios_cartao?>';
    var tf_u_usuarios_novos = '<?php echo $tf_u_usuarios_novos?>';
    var tf_u_origem_cadastro = '<?php echo $tf_u_origem_cadastro?>';
	var ug_ficou_sabendo = '<?php echo $ug_ficou_sabendo; ?>';
    var tf_u_computadores_qtde = '<?php echo $tf_u_computadores_qtde?>';
    var tf_u_fatura_media_mensal = '<?php echo $tf_u_fatura_media_mensal?>';
    var tf_u_compet_participa = '<?php echo $tf_u_compet_participa?>';
    var ug_ongame = '<?php echo $ug_ongame?>';
    var tf_gmaps = '<?php echo $tf_gmaps?>';
    var tf_u_vip = '<?php echo $tf_u_vip?>';
    var tf_u_tipo_venda = '<?php echo $tf_u_tipo_venda?>';
    var tf_ug_possui_restricao_produtos = '<?php echo $tf_ug_possui_restricao_produtos?>';
    var tf_tipo = 'UsuariosPDVs';
    var isAjax = 1;
    $.ajax({
            type: "POST",
            url: "com_pesquisa_usuarios_arquivo.php",
            data: {
                tf_u_com_totais_vendas:tf_u_com_totais_vendas,
                dd_opr_codigo:dd_opr_codigo,
                produtos_query:produtos_query,
                tf_u_codigo:tf_u_codigo,
                tf_u_status:tf_u_status,
                tf_u_data_inclusao_ini:tf_u_data_inclusao_ini,
                tf_u_data_inclusao_fim:tf_u_data_inclusao_fim,
                tf_u_data_aprovacao_ini:tf_u_data_aprovacao_ini,
                tf_u_data_aprovacao_fim:tf_u_data_aprovacao_fim,
                tf_u_qtde_acessos_ini:tf_u_qtde_acessos_ini,
                tf_u_qtde_acessos_fim:tf_u_qtde_acessos_fim,
                tf_u_data_ultimo_acesso_ini:tf_u_data_ultimo_acesso_ini,
                tf_u_data_ultimo_acesso_fim:tf_u_data_ultimo_acesso_fim,
                tf_u_nome:tf_u_nome,
                tf_u_email:tf_u_email,
                tf_u_cpf:tf_u_cpf,
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
                tf_u_cidade:tf_u_cidade,
                tf_u_cep:tf_u_cep,
                tf_u_estado:tf_u_estado,
                tf_u_news:tf_u_news,
                tf_u_compet_aceito_regulamento:tf_u_compet_aceito_regulamento,
                tf_u_compet_jogo:tf_u_compet_jogo,
                tf_u_integracao_origem:tf_u_integracao_origem,
                tf_u_status_busca:tf_u_status_busca,
                tf_u_substatus:tf_u_substatus,
                tf_u_login:tf_u_login,
                tf_u_nome_fantasia:tf_u_nome_fantasia,
                tf_u_razao_social:tf_u_razao_social,
                tf_u_cnpj:tf_u_cnpj,
                tf_ug_te_id:tf_ug_te_id,
                tf_ug_te_id_ativo:tf_ug_te_id_ativo,
                tf_u_responsavel:tf_u_responsavel,
                tf_u_site:tf_u_site,
                tf_u_tipo_cadastro:tf_u_tipo_cadastro,
                tf_u_rg:tf_u_rg,
                tf_u_fax_ddi:tf_u_fax_ddi,
                tf_u_fax_ddd:tf_u_fax_ddd,
                tf_u_fax:tf_u_fax,
                tf_u_ra_codigo:tf_u_ra_codigo,
                tf_u_ra_outros:tf_u_ra_outros,
                tf_u_contato01_nome:tf_u_contato01_nome,
                tf_u_contato01_cargo:tf_u_contato01_cargo,
                tf_u_contato01_tel_ddi:tf_u_contato01_tel_ddi,
                tf_u_contato01_tel_ddd:tf_u_contato01_tel_ddd,
                tf_u_contato01_tel:tf_u_contato01_tel,
                tf_u_risco_classif:tf_u_risco_classif,
                tf_u_saldo_positivo:tf_u_saldo_positivo,
                tf_u_usuarios_cartao:tf_u_usuarios_cartao,
                tf_u_usuarios_novos:tf_u_usuarios_novos,
                tf_u_origem_cadastro:tf_u_origem_cadastro,
				ug_ficou_sabendo:ug_ficou_sabendo,
                tf_u_computadores_qtde:tf_u_computadores_qtde,
                tf_u_fatura_media_mensal:tf_u_fatura_media_mensal,
                tf_u_compet_participa:tf_u_compet_participa,
                ug_ongame:ug_ongame,
                tf_gmaps:tf_gmaps,
                tf_u_vip:tf_u_vip,
                tf_ug_possui_restricao_produtos:tf_ug_possui_restricao_produtos,
                tf_u_tipo_venda:tf_u_tipo_venda,
                tf_tipo:tf_tipo,
                isAjax:isAjax
                },
            beforeSend: function(){
                $("#area").html("<img src='/images/ajax-loader.gif' />");
            },
            success: function(html){
				//console.log(html);
                $("#area").html(html);
                //alert(html);
            },
            error: function(){
                alert('erro ao carregar valores');
            }
        });
}


function gerarArquivoConheceu() {
    
	var resposta_como_conheceu = $('#resposta_como_conheceu').val();
	
    $.ajax({
            type: "POST",
            url: "com_pesquisa_usuarios_arquivo_como_conheceu.php",
            data: { resposta_como_conheceu: resposta_como_conheceu },
            beforeSend: () => { 
                $("#area_conheceu").html("<img src='/images/ajax-loader.gif' />");
            },
            success: (mensagem) => {
                // Convertendo os dados em Blob
                let blob = new Blob([mensagem], { type: 'text/csv' });

                // Criando um objeto URL para o Blob
                let url = window.URL.createObjectURL(blob);

                // Criando um link de download
				const dataAtual = new Date();
				
                let a = document.createElement('a');
                a.href = url;
                a.download = `cadastro-usuarios-como-conheceu-${dataAtual.getDate().toString().padStart(2, '0')}-${(dataAtual.getMonth() + 1).toString().padStart(2, '0')}-${dataAtual.getFullYear()}-${dataAtual.getHours().toString().padStart(2, '0')}-${dataAtual.getMinutes().toString().padStart(2, '0')}-${dataAtual.getSeconds().toString().padStart(2, '0')}.txt`;

                // Adicionando o link ao corpo do documento
                document.body.appendChild(a);

                // Acionando o clique automaticamente
                a.click();

                // Removendo o link após o download
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url)
            },
			complete: () => {
				$("#area_conheceu").html("Download concluído");
			},
            error: () => {
                alert('erro ao carregar valores');
            }
        });
}


function gerarArquivoCorreios() {
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
    var ug_data_expiracao_senha_ini = '<?php echo $ug_data_expiracao_senha_ini?>';
    var ug_data_expiracao_senha_fim = '<?php echo $ug_data_expiracao_senha_fim?>';
    var tf_u_data_aprovacao_ini = '<?php echo $tf_u_data_aprovacao_ini?>';
    var tf_u_data_aprovacao_fim = '<?php echo $tf_u_data_aprovacao_fim?>';
    var tf_u_nome = '<?php echo $tf_u_nome?>';
    var tf_u_email = '<?php echo $tf_u_email?>';
    var tf_u_cpf = '<?php echo $tf_u_cpf?>';
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
    var tf_u_cidade = '<?php echo $tf_u_cidade?>';
    var tf_u_cep = '<?php echo $tf_u_cep?>';
    var tf_u_estado = '<?php echo $tf_u_estado?>';
    var tf_u_news = '<?php echo $tf_u_news?>';
    var tf_u_compet_aceito_regulamento = '<?php echo $tf_u_compet_aceito_regulamento?>';
    var tf_u_compet_jogo = '<?php echo $tf_u_compet_jogo?>';
    var tf_u_integracao_origem = '<?php echo $tf_u_integracao_origem?>';
    var tf_u_status_busca = '<?php echo (($tf_u_status_busca==1)?1:(($tf_u_status_busca==2)?2:0))?>';
    var tf_u_substatus = '<?php echo $tf_u_substatus?>';
    var tf_u_login = '<?php echo $tf_u_login?>';
    var tf_u_nome_fantasia = '<?php echo $tf_u_nome_fantasia?>';
    var tf_u_razao_social = '<?php echo $tf_u_razao_social?>';
    var tf_u_cnpj = '<?php echo $tf_u_cnpj?>';
    var tf_ug_te_id = '<?php echo $tf_ug_te_id?>';
    var tf_ug_te_id_ativo = '<?php echo $tf_ug_te_id_ativo?>';
    var tf_u_responsavel = '<?php echo $tf_u_responsavel?>';
    var tf_u_site = '<?php echo $tf_u_site?>';
    var tf_u_tipo_cadastro = '<?php echo $tf_u_tipo_cadastro?>';
    var tf_u_rg = '<?php echo $tf_u_rg?>';
    var tf_u_fax_ddi = '<?php echo $tf_u_fax_ddi?>';
    var tf_u_fax_ddd = '<?php echo $tf_u_fax_ddd?>';
    var tf_u_fax = '<?php echo $tf_u_fax?>';
    var tf_u_ra_codigo = '<?php echo $tf_u_ra_codigo?>';
    var tf_u_ra_outros = '<?php echo $tf_u_ra_outros?>';
    var tf_u_contato01_nome = '<?php echo $tf_u_contato01_nome?>';
    var tf_u_contato01_cargo = '<?php echo $tf_u_contato01_cargo?>';
    var tf_u_contato01_tel_ddi = '<?php echo $tf_u_contato01_tel_ddi?>';
    var tf_u_contato01_tel_ddd = '<?php echo $tf_u_contato01_tel_ddd?>';
    var tf_u_contato01_tel = '<?php echo $tf_u_contato01_tel?>';
    var tf_u_risco_classif = '<?php echo $tf_u_risco_classif?>';
    var tf_u_saldo_positivo = '<?php echo $tf_u_saldo_positivo?>';
    var tf_u_usuarios_cartao = '<?php echo $tf_u_usuarios_cartao?>';
    var tf_u_usuarios_novos = '<?php echo $tf_u_usuarios_novos?>';
    var tf_u_origem_cadastro = '<?php echo $tf_u_origem_cadastro?>';
    var tf_u_computadores_qtde = '<?php echo $tf_u_computadores_qtde?>';
    var tf_u_fatura_media_mensal = '<?php echo $tf_u_fatura_media_mensal?>';
    var tf_u_compet_participa = '<?php echo $tf_u_compet_participa?>';
    var ug_ongame = '<?php echo $ug_ongame?>';
    var tf_gmaps = '<?php echo $tf_gmaps?>';
    var tf_u_vip = '<?php echo $tf_u_vip?>';
    var tf_u_tipo_venda = '<?php echo $tf_u_tipo_venda?>';
    var tf_ug_possui_restricao_produtos = '<?php echo $tf_ug_possui_restricao_produtos?>';
    var isAjax = 1;
    $.ajax({
            type: "POST",
            url: "com_pesquisa_usuarios_arquivo_correios.php",
            data: {
                tf_u_com_totais_vendas:tf_u_com_totais_vendas,
                dd_opr_codigo:dd_opr_codigo,
                produtos_query:produtos_query,
                tf_u_codigo:tf_u_codigo,
                tf_u_status:tf_u_status,
                tf_u_data_inclusao_ini:tf_u_data_inclusao_ini,
                tf_u_data_inclusao_fim:tf_u_data_inclusao_fim,
                tf_u_data_aprovacao_ini:tf_u_data_aprovacao_ini,
                tf_u_data_aprovacao_fim:tf_u_data_aprovacao_fim,
                tf_u_qtde_acessos_ini:tf_u_qtde_acessos_ini,
                tf_u_qtde_acessos_fim:tf_u_qtde_acessos_fim,
                tf_u_data_ultimo_acesso_ini:tf_u_data_ultimo_acesso_ini,
                tf_u_data_ultimo_acesso_fim:tf_u_data_ultimo_acesso_fim,
                tf_u_nome:tf_u_nome,
                tf_u_email:tf_u_email,
                tf_u_cpf:tf_u_cpf,
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
                tf_u_cidade:tf_u_cidade,
                tf_u_cep:tf_u_cep,
                tf_u_estado:tf_u_estado,
                tf_u_news:tf_u_news,
                tf_u_compet_aceito_regulamento:tf_u_compet_aceito_regulamento,
                tf_u_compet_jogo:tf_u_compet_jogo,
                tf_u_integracao_origem:tf_u_integracao_origem,
                tf_u_status_busca:tf_u_status_busca,
                tf_u_substatus:tf_u_substatus,
                tf_u_login:tf_u_login,
                tf_u_nome_fantasia:tf_u_nome_fantasia,
                tf_u_razao_social:tf_u_razao_social,
                tf_u_cnpj:tf_u_cnpj,
                tf_ug_te_id:tf_ug_te_id,
                tf_ug_te_id_ativo:tf_ug_te_id_ativo,
                tf_u_responsavel:tf_u_responsavel,
                tf_u_site:tf_u_site,
                tf_u_tipo_cadastro:tf_u_tipo_cadastro,
                tf_u_rg:tf_u_rg,
                tf_u_fax_ddi:tf_u_fax_ddi,
                tf_u_fax_ddd:tf_u_fax_ddd,
                tf_u_fax:tf_u_fax,
                tf_u_ra_codigo:tf_u_ra_codigo,
                tf_u_ra_outros:tf_u_ra_outros,
                tf_u_contato01_nome:tf_u_contato01_nome,
                tf_u_contato01_cargo:tf_u_contato01_cargo,
                tf_u_contato01_tel_ddi:tf_u_contato01_tel_ddi,
                tf_u_contato01_tel_ddd:tf_u_contato01_tel_ddd,
                tf_u_contato01_tel:tf_u_contato01_tel,
                tf_u_risco_classif:tf_u_risco_classif,
                tf_u_saldo_positivo:tf_u_saldo_positivo,
                tf_u_usuarios_cartao:tf_u_usuarios_cartao,
                tf_u_usuarios_novos:tf_u_usuarios_novos,
                tf_u_origem_cadastro:tf_u_origem_cadastro,
                tf_u_computadores_qtde:tf_u_computadores_qtde,
                tf_u_fatura_media_mensal:tf_u_fatura_media_mensal,
                tf_u_compet_participa:tf_u_compet_participa,
                ug_ongame:ug_ongame,
                tf_gmaps:tf_gmaps,
                tf_u_vip:tf_u_vip,
                tf_ug_possui_restricao_produtos:tf_ug_possui_restricao_produtos,
                tf_u_tipo_venda:tf_u_tipo_venda,
                isAjax:isAjax
                },
            beforeSend: function(){
                $("#area_correios").html("<img src='/images/ajax-loader.gif' />");
            },
            success: function(html){
                $("#area_correios").html(html);
                //alert(html);
            },
            error: function(){
                alert('erro ao carregar valores');
            }
        });
}
</script>

<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<!--<div class="col-md-12">
    <blockquote>
        <p>Instru&ccedil;&atilde;o para Captura de Coordenadas:</p>
        <footer>Ao ativar uma nova LAN House deve clicar no icone <img src="images/global-search-icon_peq.jpg" width="28" height="21" border="0" title="icone de captura de coordenadas"> para capturar as coordenadas.<br>
                 Carregado a nova p&aacute;gina deve ser clicado no bot&atilde;o consultar e verificar o retorno se confere com o endere&ccedil;o cadastrado.<br>
                 Se tudo estiver correto deve ser clicado no bot&atilde;o Atualizar Geolocaliza&ccedil;&atilde;o.</footer>
    </blockquote>
</div>-->
<?php 
    if($msg != ""){
?>
    <div class="col-md-12">
        <div class="alert alert-info" role="alert"><?php echo str_replace("\n", "<br>", $msg) ?></div>
    </div>
<?php 
    } elseif($msgAcao != "")
    {
?>
    <div class="col-md-12">
        <div class="alert alert-info" role="alert"><?php echo str_replace("\n", "<br>", $msgAcao) ?></div>
    </div>
<?php 
    } 
?>
<div class="col-md-12 fontsize-pp">
    <form name="form1" method="post" action="com_pesquisa_usuarios.php">
    <div class="col-md-12">
        <input type="submit" name="BtnSearch" value="Buscar" class="btn btn-success btn-sm pull-right">
    </div>
    <div class="clearfix"></div>
    <div class="top10 panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Dados Administrativos</h3>
        </div>
        <div class="panel-body">
            <div class="col-md-6">
                <div class="form-group">
                  <label for="tf_u_codigo">Código</label>
				  <?php //echo $_SESSION["visualiza_dados"];?>
                  <input type="text" class="input-sm form-control" id="tf_u_codigo" name="tf_u_codigo" placeholder="Código" value="<?php echo trim($tf_u_codigo) ?>">
                </div>
                <div class="form-group">
                    <label for="tf_u_qtde_acessos_ini" class="w100">Qtde de Acessos</label>
                    <input name="tf_u_qtde_acessos_ini" id="tf_u_qtde_acessos_ini" type="text" class="input-sm form-control w150  dislineblock" value="<?php echo $tf_u_qtde_acessos_ini ?>" size="7" maxlength="7" placeholder="de">
                    <span class="text-info p-3  dislineblock">a</span>
                    <input name="tf_u_qtde_acessos_fim" id="tf_u_qtde_acessos_fim" type="text" class="input-sm form-control w150 dislineblock left5" value="<?php echo $tf_u_qtde_acessos_fim ?>" size="7" maxlength="7" placeholder="até">
                    <span class="text-info p-3">para 0 usar -1</span>
                </div>
                <div class="form-group">
                    <label for="tf_u_data_inclusao_ini"  class="w100">Data de Cadastro</label>
                    <input name="tf_u_data_inclusao_ini" type="text" class="input-sm form-control  w150  dislineblock" id="tf_u_data_inclusao_ini" value="<?php echo $tf_u_data_inclusao_ini ?>" size="9" maxlength="10">
                    <span class="text-info p-3  dislineblock">a</span>
                    <input name="tf_u_data_inclusao_fim" type="text" class="input-sm form-control w150 left5 dislineblock" id="tf_u_data_inclusao_fim" value="<?php echo $tf_u_data_inclusao_fim ?>" size="9" maxlength="10">
                    <span class="text-info p-3  dislineblock">(99/99/9999)</span>
                </div>
                <div class="form-group">
                    <label for="tf_u_login" class="w100">Login</label>
                    <input name="tf_u_login" id="tf_u_login" type="text" class="input-sm form-control" value="<?php echo trim($tf_u_login) ?>" size="25" maxlength="100">
                </div>
                <div class="form-group">
                <label for="tf_u_tipo_cadastro" class="w100">Tipo de Cadastro</label>
                    <select name="tf_u_tipo_cadastro" id="tf_u_tipo_cadastro" class="input-sm form-control w-auto">
                        <option value="" <?php if($tf_u_tipo_cadastro == "") echo "selected" ?>>Selecione</option>
                        <option value="PJ" <?php if ($tf_u_tipo_cadastro == "PJ") echo "selected";?>>Pessoa Jurídica</option>
                        <option value="PF" <?php if ($tf_u_tipo_cadastro == "PF") echo "selected";?>>Pessoa Física</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="tf_u_data_aprovacao_ini" class="w100">Data de Aprovação</label>
                    <input name="tf_u_data_aprovacao_ini" type="text" class="input-sm form-control w150  dislineblock" id="tf_u_data_aprovacao_ini" value="<?php echo $tf_u_data_aprovacao_ini ?>" size="9" maxlength="10">
                    <span class="text-info p-3  dislineblock">a</span>
                    <input name="tf_u_data_aprovacao_fim" type="text" class="input-sm form-control w150  dislineblock left5" id="tf_u_data_aprovacao_fim" value="<?php echo $tf_u_data_aprovacao_fim ?>" size="9" maxlength="10">
                    <span class="text-info p-3  dislineblock">(99/99/9999)</span>
                </div>
                <div class="form-group">
                    <label for="ug_data_expiracao_senha_ini" class="w100">Data de expiração da senha</label>
                    <input name="ug_data_expiracao_senha_ini" type="text" class="input-sm form-control w150  dislineblock" id="ug_data_expiracao_senha_ini" value="<?php echo $ug_data_expiracao_senha_ini ?>" size="9" maxlength="10">
                    <span class="text-info p-3  dislineblock">a</span>
                    <input name="ug_data_expiracao_senha_fim" type="text" class="input-sm form-control w150  dislineblock left5" id="ug_data_expiracao_senha_fim" value="<?php echo $ug_data_expiracao_senha_fim; ?>" size="9" maxlength="10">
                    <span class="text-info p-3  dislineblock">(99/99/9999)</span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="tf_u_status" class="w100">Status</label>
                    <select name="tf_u_status" id="tf_u_status" class="input-sm form-control w-auto" >    <?php // onChange="javascript:mudarSelect();" ?>
                        <option value="" <?php if($tf_u_status == "") echo "selected" ?>>Selecione</option>
                        <option value="1" <?php if ($tf_u_status == "1") echo "selected";?>>Ativo</option>
                        <option value="2" <?php if ($tf_u_status == "2") echo "selected";?>>Inativo</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="tf_u_data_ultimo_acesso_ini" class="w100">Data Último Acesso</label>
                    <input name="tf_u_data_ultimo_acesso_ini" type="text" class="input-sm form-control w150  dislineblock" id="tf_u_data_ultimo_acesso_ini" value="<?php echo $tf_u_data_ultimo_acesso_ini ?>" size="9" maxlength="10">
                    <span class="text-info p-3  dislineblock">a</span>
                    <input name="tf_u_data_ultimo_acesso_fim" type="text" class="input-sm form-control w150  dislineblock left5" id="tf_u_data_ultimo_acesso_fim" value="<?php echo $tf_u_data_ultimo_acesso_fim ?>" size="9" maxlength="10">
                    <span class="text-info p-3  dislineblock">(99/99/9999)</span>
                </div>
                <div class="form-group">
                    <label for="tf_u_status_busca" class="w100">Status Busca</label>
                    <select name="tf_u_status_busca" id="tf_u_status_busca" class="input-sm form-control w-auto">
                        <option value="" <?php  if($tf_u_status_busca == "" || $tf_u_status_busca == "0") echo "selected" ?>>Selecione</option>
                        <option value="1" <?php  if ($tf_u_status_busca == "1") echo "selected";?>>Ativo</option>
                        <option value="2" <?php  if ($tf_u_status_busca == "2") echo "selected";?>>Inativo</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="tf_u_substatus" class="w100">Substatus</label>
                    <select name="tf_u_substatus" id="tf_u_substatus" class="input-sm form-control w-auto">
                        <option value="" <?php  if($tf_u_substatus == "") echo "selected" ?>>Selecione</option>
                        <?php
                            foreach($SUBSTATUS_LH as $indice=>$dado) {
                                echo "<option value=\"".$indice."\""; if(strcmp($tf_u_substatus,$indice)==0) echo "selected"; echo " >".$dado." (".$indice.")</option>\n";
                            }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="tf_u_vip" class="w100">Categoria</label>
                    <select name="tf_u_vip" id="tf_u_vip" class="input-sm form-control w-auto">
                        <option value="" <?php if($tf_u_vip == "") echo "selected" ?>>Selecione</option>
                        <option value="0" <?php if (is_numeric($tf_u_vip) && $tf_u_vip == 0) echo "selected";?>>Normal</option>
                        <option value="1" <?php if ($tf_u_vip == 1) echo "selected";?>>VIP</option>
                        <option value="2" <?php if ($tf_u_vip == 2) echo "selected";?>>Master</option>
                        <option value="3" <?php if ($tf_u_vip == 3) echo "selected";?>>Black</option>
                        <option value="4" <?php if ($tf_u_vip == 4) echo "selected";?>>Gold</option>
						<option value="5" <?php if ($tf_u_vip == 5) echo "selected";?>>Platinum</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="tf_ug_possui_restricao_produtos" class="w100">Possui Restrição de Vendas de Produtos</label>
                    <select name="tf_ug_possui_restricao_produtos" id="tf_ug_possui_restricao_produtos" class="input-sm form-control w-auto">
                        <option value="" <?php if($tf_ug_possui_restricao_produtos == "") echo "selected" ?>>Selecione</option>
                        <option value="0" <?php if (is_numeric($tf_ug_possui_restricao_produtos) && $tf_ug_possui_restricao_produtos == 0) echo "selected";?>>Não</option>
                        <option value="1" <?php if ($tf_ug_possui_restricao_produtos == 1) echo "selected";?>>Sim</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="tf_u_risco_classif" class="w100">Classificação</label>
                    <select name="tf_u_risco_classif" id="tf_u_risco_classif" class="input-sm form-control w-auto">
                        <option value="" <?php if($tf_u_risco_classif == "") echo "selected" ?>>Selecione</option>
                        <?php for($i=1; $i < count($RISCO_CLASSIFICACAO_NOMES)+1; $i++){ ?>
                            <option value="<?php echo $RISCO_CLASSIFICACAO_NOMES[$i] ?>" <?php if($tf_u_risco_classif == $RISCO_CLASSIFICACAO_NOMES[$i]) echo "selected"; ?>><?php echo $RISCO_CLASSIFICACAO_NOMES[$i] ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
      </div>
    </div>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title">Dados da Empresa</h3>
      </div>
        <div class="panel-body">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="tf_u_nome_fantasia" class="w100">Nome Fantasia</label>
                    <input name="tf_u_nome_fantasia" id="tf_u_nome_fantasia" type="text" class="input-sm form-control" value="<?php echo trim($tf_u_nome_fantasia) ?>" size="25" maxlength="100">
                </div>
                <div class="form-group">
                    <label for="tf_u_cnpj" class="w100">CNPJ</label>
                    <input name="tf_u_cnpj" id="" type="text" class="input-sm form-control" value="<?php echo trim($tf_u_cnpj) ?>" size="25" maxlength="14">
                </div>    
                <div class="form-group">
                    <label for="tf_u_responsavel" class="w100">Responsável</label>
                    <input name="tf_u_responsavel" id="tf_u_responsavel" type="text" class="input-sm form-control" value="<?php echo trim($tf_u_responsavel) ?>" size="25" maxlength="100">
                </div>
                <div class="form-group">
                    <label for="tf_u_site" class="w100">Site Web</label>
                    <input name="tf_u_site" id="tf_u_site" type="text" class="input-sm form-control" value="<?php echo trim($tf_u_site) ?>" size="50" maxlength="100">
                </div>
                <div class="form-group">
                    <label for="tf_u_fatura_media_mensal" class="w100">Fat. médio mensal:</label>
                    <select name="tf_u_fatura_media_mensal" id="tf_u_fatura_media_mensal" class="input-sm form-control">
                        <option value="Todos"<?php if(!$tf_u_fatura_media_mensal || $tf_u_fatura_media_mensal=="Todos") echo " selected"; ?>>Qualquer valor</option>
                            <?php foreach($CADASTRO_FATURAMENTO as $key => $val) { ?>
                            <option value="<?php echo $key; ?>"<?php if($tf_u_fatura_media_mensal==$key) echo " selected";  ?>><?php echo $val; ?></option>
                            <?php } ?>
                        </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="tf_u_razao_social" class="w100">Razão Social</label>
                    <input name="tf_u_razao_social" id="tf_u_razao_social" type="text" class="input-sm form-control" value="<?php echo trim($tf_u_razao_social) ?>" size="25" maxlength="100">
                </div>
                <div class="form-group">
                    <label for="tf_ug_te_id" class="w100">Tipo Estabelecimento</label>
                    <select name="tf_ug_te_id" id="tf_ug_te_id" class="input-sm form-control w150 dislineblock">
                        <option value="" <?php  if($tf_ug_te_id == "") echo "selected" ?>>Selecione</option>
<?php
                            //colocar rotina para criação do combo de seleção do tipo de estabelecimento
                            $sql = "select * from tb_tipo_estabelecimento order by te_ativo DESC,te_descricao";
                            $res_te = SQLexecuteQuery($sql);
                            while ($res_te_row = pg_fetch_array($res_te)) {
                                $te_codigo    = $res_te_row['te_id'];
                                if ($tf_ug_te_id == $te_codigo ) {
                                    $select    = "selected = 'selected' ";
                                } else {
                                    $select    = '';
                                }
                                ?>
                                <option value='<?php echo $te_codigo?>' <?php echo $select?>><?php echo utf8_decode($res_te_row['te_descricao']);?> (<?php if($res_te_row['te_ativo']) echo "Ativo"; else echo "Inativo";?>)</option>
<?php
                                }//end while
?>
                    </select>
                    <select name="tf_ug_te_id_ativo" class="input-sm form-control w150 dislineblock left5">
                        <option value="" <?php  if($tf_ug_te_id_ativo === '') echo "selected" ?>>Selecione</option>
                        <option value="1" <?php  if($tf_ug_te_id_ativo === '1') echo "selected" ?>>Ativo</option>
                        <option value="0" <?php  if($tf_ug_te_id_ativo === '0') echo "selected" ?>>Inativo</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="tf_u_email" class="w100">Email</label>
                    <input name="tf_u_email" id="tf_u_email" type="text" class="input-sm form-control" value="<?php echo $tf_u_email ?>" size="25" maxlength="100">
                </div>
                <div class="form-group">
                    <label for="tf_u_computadores_qtde" class="w100">No. de computadores:</label>
                    <select name="tf_u_computadores_qtde" id="tf_u_computadores_qtde" class="input-sm form-control">
                        <option value="Todos"<?php if(!$tf_u_computadores_qtde || $tf_u_computadores_qtde=="Todos") echo " selected"; ?>>Qualquer número</option>
                        <?php foreach($CADASTRO_COMPUTADORES as $key => $val) { ?>
                        <option value="<?php echo $key; ?>"<?php if($tf_u_computadores_qtde==$key) echo " selected";  ?>><?php echo $val; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="tf_u_tipo_venda" class="w100">Tipo Venda</label>
                        <select name="tf_u_tipo_venda" id="ug_tipo_venda" class="input-sm form-control">
                            <option value="0" <?php echo ($tf_u_tipo_venda=='0') ? ' selected="selected" ' : '' ?>>-- Selecione --</option>
                            <option value="1" <?php echo ($tf_u_tipo_venda=='1') ? ' selected="selected" ' : '' ?>>Online</option>
                            <option value="2" <?php echo ($tf_u_tipo_venda=='2') ? ' selected="selected" ' : '' ?>>Offline</option>
                            <option value="3" <?php echo ($tf_u_tipo_venda=='3') ? ' selected="selected" ' : '' ?>>Offline e Online</option>
                        </select>
                </div>
            </div>
        </div>
    </div>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title">Dados Pessoais</h3>
      </div>
        <div class="panel-body">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="tf_u_nome" class="w100">Nome</label>
                    <input name="tf_u_nome" id="tf_u_nome" type="text" class="input-sm form-control" value="<?php echo trim($tf_u_nome) ?>" size="25" maxlength="100">
                </div>
                <div class="form-group">
                    <label for="tf_u_cpf" class="w100">CPF</label>
                    <input name="tf_u_cpf" id="tf_u_cpf" type="text" class="input-sm form-control" value="<?php echo trim($tf_u_cpf) ?>" size="25" maxlength="14">
                </div>                    
                <div class="form-group">
                    <label for="tf_u_data_nascimento_ini" class="w100">Data de Nascimento</label>
                    <input name="tf_u_data_nascimento_ini" type="text" class="input-sm form-control dislineblock w150" id="tf_u_data_nascimento_ini" value="<?php echo $tf_u_data_nascimento_ini ?>" size="9" maxlength="10">
                    <span class="text-info p-3  dislineblock">a</span>
                    <input name="tf_u_data_nascimento_fim" type="text" class="input-sm form-control dislineblock left5 w150" id="tf_u_data_nascimento_fim" value="<?php echo $tf_u_data_nascimento_fim ?>" size="9" maxlength="10">
                    <span class="text-info p-3  dislineblock">(99/99/9999)</span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="tf_u_sexo" class="w100">Sexo</label>
                    <select name="tf_u_sexo" id="tf_u_sexo" class="input-sm form-control">
                        <option value="" <?php if($tf_u_sexo == "") echo "selected" ?>>Selecione</option>
                        <option value="M" <?php if ($tf_u_sexo == "M") echo "selected";?>>Masculino</option>
                        <option value="F" <?php if ($tf_u_sexo == "F") echo "selected";?>>Feminino</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="tf_u_rg" class="w100">RG</label>
                    <input name="tf_u_rg" id="tf_u_rg" type="text" class="input-sm form-control" value="<?php echo $tf_u_rg ?>" size="25" maxlength="14">
                </div>
            </div>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title">Endereço</h3>
        </div>
        <div class="panel-body">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="tf_u_endereco" class="w100">Endereço</label>
                    <input name="tf_u_endereco" id="tf_u_endereco" type="text" class="input-sm form-control" value="<?php echo trim($tf_u_endereco) ?>" size="50" maxlength="100">
                </div>
                <div class="form-group">
                    <label for="tf_u_bairro" class="w100">Bairro</label>
                    <input name="tf_u_bairro" id="tf_u_bairro" type="text" class="input-sm form-control" value="<?php echo trim($tf_u_bairro) ?>" size="25" maxlength="100">
                </div>
                <div class="form-group">
                    <label for="tf_u_cep" class="w100">CEP</label>
                    <input name="tf_u_cep" id="tf_u_cep" type="text" class="input-sm form-control" value="<?php echo trim($tf_u_cep) ?>" size="7" maxlength="8">
                </div>
                <div class="form-group">
                    <label for="tf_u_tel_ddi" class="w100">Telefone</label>
                    (<input name="tf_u_tel_ddi" id="tf_u_tel_ddi" type="text" class="input-sm form-control widthInputMoeda dislineblock" value="<?php echo $tf_u_tel_ddi ?>" size="2" maxlength="2">)
                    (<input name="tf_u_tel_ddd" type="text" class="input-sm form-control widthInputMoeda dislineblock" value="<?php echo $tf_u_tel_ddd ?>" size="2" maxlength="2">)
                    <input name="tf_u_tel" type="text" class="input-sm form-control w100p dislineblock" value="<?php echo $tf_u_tel ?>" size="7" maxlength="9">
                </div>
<!--                <div class="form-group">
                    <label for="tf_u_fax_ddi" class="w100">Fax</label>
                    (<input name="tf_u_fax_ddi" id="tf_u_fax_ddi" type="text" class="input-sm form-control widthInputMoeda dislineblock" value="<?php echo $tf_u_fax_ddi ?>" size="2" maxlength="2">)
                    (<input name="tf_u_fax_ddd" type="text" class="input-sm form-control widthInputMoeda dislineblock" value="<?php echo $tf_u_fax_ddd ?>" size="2" maxlength="2">)
                    <input name="tf_u_fax" type="text" class="input-sm form-control w100p dislineblock" value="<?php echo $tf_u_fax ?>" size="7" maxlength="9">
                </div>
                <div class="form-group">
                    <label for="tf_u_ra_codigo" class="w100">Ramo de atividade</label>
                    <select name="tf_u_ra_codigo" id="tf_u_ra_codigo" class="input-sm form-control">
                        <option value="">Selecione</option>
                        <?php while ($pgatv = pg_fetch_array($resatv)) { ?>
                            <option value="<?php echo $pgatv['ra_codigo'] ?>" <?php if($pgatv['ra_codigo'] == $tf_u_ra_codigo) echo "selected" ?>><?php echo $pgatv['ra_desc'] ?></option>
                        <?php } ?>
                    </select>
                </div>-->
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="tf_u_cidade" class="w100 top74">Cidade</label>
                    <input name="tf_u_cidade" id="tf_u_cidade" type="text" class="input-sm form-control" value="<?php echo trim($tf_u_cidade) ?>" size="25" maxlength="100">
                </div>
                <div class="form-group">
                    <label for="tf_u_estado" class="w100">Estado</label>
                    <select name="tf_u_estado" id="tf_u_estado" class="input-sm form-control">
                        <option value="" <?php if($tf_u_estado == "") echo "selected" ?>>Selecione</option>
                    <?php for($i=0; $i < count($SIGLA_ESTADOS); $i++){ ?>
                        <option value="<?php echo $SIGLA_ESTADOS[$i] ?>" <?php if($tf_u_estado == $SIGLA_ESTADOS[$i]) echo "selected"; ?>><?php echo $SIGLA_ESTADOS[$i] ?></option>
                    <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="tf_u_cel_ddi" class="w100">Celular</label>
                    (<input name="tf_u_cel_ddi" id="tf_u_cel_ddi" type="text" class="input-sm form-control  widthInputMoeda dislineblock" value="<?php echo $tf_u_cel_ddi ?>" size="2" maxlength="2">)
                    (<input name="tf_u_cel_ddd" type="text" class="input-sm form-control  widthInputMoeda dislineblock" value="<?php echo $tf_u_cel_ddd ?>" size="2" maxlength="2">)
                    <input name="tf_u_cel" type="text" class="input-sm form-control w100p dislineblock" value="<?php echo $tf_u_cel ?>" size="7" maxlength="9">
                </div>
<!--                <div class="form-group">
                    <label for="tf_u_ra_outros" class="w100 top74">Ramo de atividade - Outros</label>
                    <input name="tf_u_ra_outros" id="tf_u_ra_outros" type="text" class="input-sm form-control" value="<?php echo $tf_u_ra_outros ?>" size="25" maxlength="100">
                </div>-->
            </div>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Geolocalização</h3>
        </div>
        <div class="panel-body">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="tf_gmaps" class="w100">Google Maps</label>
                    <select name="tf_gmaps" class="input-sm form-control" id="tf_gmaps">
                        <option value="0" <?php  if($tf_gmaps == 0) echo "selected"; ?>>Todos os Registros</option>
                        <option value="L" <?php  if($tf_gmaps == 'L') echo "selected"; ?>>Apenas 'Localizados'</option>
                        <option value="1" <?php  if($tf_gmaps == 1) echo "selected"; ?>>Apenas 'Não localizados'</option>
                        <option value="2" <?php  if($tf_gmaps == 2) echo "selected"; ?>>Apenas 'Fora do Mapa'</option>
                        <option value="-1" <?php  if($tf_gmaps == -1) echo "selected"; ?>>Não Geolocalizadas (lat=0,lng=0).</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
<!--    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title">Contato Técnico</h3>
      </div>
        <div class="panel-body">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="tf_u_contato01_nome" class="w100">Nome</label>
                    <input name="tf_u_contato01_nome" id="tf_u_contato01_nome" type="text"class="input-sm form-control" value="<?php echo $tf_u_contato01_nome ?>" size="25" maxlength="100">
                </div>
                <div class="form-group">
                    <label for="tf_u_contato01_tel_ddi" class="w100">Telefone</label>
                    (<input name="tf_u_contato01_tel_ddi" id="tf_u_contato01_tel_ddi" type="text"class="input-sm form-control widthInputMoeda dislineblock" value="<?php echo $tf_u_contato01_tel_ddi ?>" size="2" maxlength="2">)
                    (<input name="tf_u_contato01_tel_ddd" type="text"class="input-sm form-control widthInputMoeda dislineblock" value="<?php echo $tf_u_contato01_tel_ddd ?>" size="2" maxlength="2">)
                    <input name="tf_u_contato01_tel" type="text"class="input-sm form-control w100p dislineblock" value="<?php echo $tf_u_contato01_tel ?>" size="7" maxlength="9">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="tf_u_contato01_cargo" class="w100">Cargo</label>
                    <input name="tf_u_contato01_cargo" id="tf_u_contato01_cargo" type="text"class="input-sm form-control" value="<?php echo $tf_u_contato01_cargo ?>" size="25" maxlength="100">
                </div>
            </div>
        </div>
    </div>-->
<!--    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title">Gestão de Risco</h3>
      </div>
        <div class="panel-body">
            <div class="col-md-6">
                
            </div>
            <div class="col-md-6"></div>
        </div>
    </div>-->
    <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title">Campeonato</h3>
        </div>
        <div class="panel-body">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="tf_u_compet_participa" class="w100">Cadastrado no campeonato</label>
                    <select name="tf_u_compet_participa" id="tf_u_compet_participa" class="input-sm form-control">
                        <option value=""<?php if($tf_u_compet_participa=="") echo " selected" ?>>Todos os usuários</option>
                        <option value="s"<?php if(strtolower($tf_u_compet_participa) == "s") echo " selected" ?>>Sim - apenas os cadastrados</option>
                        <option value="n"<?php if(strtolower($tf_u_compet_participa) == "n") echo " selected" ?>>Não - apenas os não cadastrados</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="ug_ongame" class="w100">Habilita OnGame (PB)</label>
                    <select name="ug_ongame" id="ug_ongame" class="input-sm form-control">
                        <option value=""<?php if($ug_ongame=="") echo " selected" ?>>Todos os usuários</option>
                        <option value="s"<?php if(strtolower($ug_ongame) == "s") echo " selected" ?>>Sim - apenas os habilitados</option>
                        <option value="n"<?php if(strtolower($ug_ongame) == "n") echo " selected" ?>>Não - apenas os não habilitados</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title">Outros</h3>
      </div>
        <div class="panel-body">
            <div class="col-md-6">
<!--                <div class="form-group">
                    <label for="tf_u_usuarios_cartao" class="w100">Usuários de Cartões</label>
                    <select name="tf_u_usuarios_cartao" id="tf_u_usuarios_cartao" class="input-sm form-control">
                        <option value="0" <?php if($tf_u_usuarios_cartao != 1) echo "selected"; ?>>Não, ver todos os usuários</option>
                        <option value="1" <?php if($tf_u_usuarios_cartao == 1) echo "selected"; ?>>Sim, apenas os usuários de cartões</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="tf_u_origem_cadastro" class="w100">Origem do cadastro</label>
                    <select name="tf_u_origem_cadastro" id="tf_u_origem_cadastro" class="input-sm form-control" id="tf_u_origem_cadastro">
                        <option value="">Todas as Origens</option>
                        <?php while ($pgorigem = pg_fetch_array ($resorigem)) { ?>
                        <option value="<?php echo $pgorigem['ug_ficou_sabendo'] ?>" <?php if($pgorigem['ug_ficou_sabendo'] == $tf_u_origem_cadastro) echo "selected" ?>><?php echo $pgorigem['ug_ficou_sabendo'] ?> (<?php echo $pgorigem['n']?>)</option>
                        <?php } ?>
                    </select>
                </div>-->
<?php
                $sbkg1 = "";
                if($tf_u_sem_dados_cadastro) $sbkg1 = " style='background-color:#FFCC66;'";
?>
                <div class="form-group"<?php echo $sbkg1;?>>
                    <label for="tf_u_sem_dados_cadastro" class="w100">Oculta dados de cadastro</label>
                    <input type="checkbox" id="tf_u_sem_dados_cadastro" name="tf_u_sem_dados_cadastro"<?php if($tf_u_sem_dados_cadastro) echo " CHECKED"; ?>>
<?php 
                    if($tf_u_sem_dados_cadastro) echo " (Sem dados de cadastro)"; 
                    $sbkg2 = "";
                    if(strtoupper($tf_u_saldo_positivo)=="P") $sbkg2 = " style='background-color:#CCFFFF;'";
                    elseif(strtoupper($tf_u_saldo_positivo)=="N") $sbkg2 = " style='background-color:#FFCC66;'";
?>                    
                </div>
<?php
                $sbkg1 = "";
                if($tf_u_com_totais_vendas) $sbkg1 = " style='background-color:#FFCC66;'";
?>
                <div class="form-group">
                    <label for="tf_u_com_totais_vendas" class="w100">Com totais de vendas</label>
                    <input type="checkbox" id="tf_u_com_totais_vendas" name="tf_u_com_totais_vendas"<?php if($tf_u_com_totais_vendas) echo " CHECKED"; ?>>
<?php 
                if($tf_u_com_totais_vendas) echo " (Com totais de vendas)"; 
?>
                </div>
<?php
//                if($tf_u_com_totais_vendas) 
//                {
?>
                    <div class="form-group contotaisvendas">
                        <label for="tf_v_data_inclusao_ini" class="w100">Data de Inclusão das Vendas</label>
                        <input name="tf_v_data_inclusao_ini" type="text" class="input-sm form-control dislineblock w150" id="tf_v_data_inclusao_ini" value="<?php echo $tf_v_data_inclusao_ini ?>" size="9" maxlength="10">
                        <span class="text-info p-3  dislineblock">a</span>
                        <input name="tf_v_data_inclusao_fim" type="text" class="input-sm form-control dislineblock w150" id="tf_v_data_inclusao_fim" value="<?php echo $tf_v_data_inclusao_fim ?>" size="9" maxlength="10">
                        <span class="text-info p-3  dislineblock">(99/99/9999)</span>
                    </div>
                    <div class="form-group contotaisvendas">
                        <label for="dd_opr_codigo" class="w100">Operadora</label>
                        <select name="dd_opr_codigo" id="dd_opr_codigo" class="input-sm form-control">
                            <option value="">Selecione a Operadora</option>
<?php
                            $sql  = "select opr_codigo, opr_nome from operadoras where opr_status='1' and opr_importa=1 order by opr_nome";
                            $resopr = SQLexecuteQuery($sql);
                            $a_opr = array();
                            while ($pgopr = pg_fetch_array($resopr)) 
                            {
                                $operadora_nome = $pgopr['opr_nome'];
                                $operadora_codigo = $pgopr['opr_codigo'];
                                if ($dd_opr_codigo == $operadora_codigo ) 
                                {
                                    $select = "selected = 'selected' ";
                                } else {
                                    $select = '';
                                }
?>
                                <option value='<?php echo $operadora_codigo?>' <?php echo $select?>><?php echo $operadora_nome?>(<?php echo $operadora_codigo?>)</option>
<?php
                            }
?>
                        </select>
                    </div>
<?php
//                }
?>           
            </div>
            <div class="col-md-6">
<!--                <div class="form-group">
                    <label for="tf_u_usuarios_novos" class="w100">Usuários novos</label>
                    <select name="tf_u_usuarios_novos" id="tf_u_usuarios_novos" class="input-sm form-control">
                        <option value="0" <?php if($tf_u_usuarios_novos != 1) echo "selected"; ?>>Não, ver todos os usuários</option>
                        <option value="1" <?php if($tf_u_usuarios_novos == 1) echo "selected"; ?>>Sim, apenas os usuários novos cadastro direto</option>
                        <option value="2" <?php if($tf_u_usuarios_novos == 2) echo "selected"; ?>>Sim, apenas os usuários novos cadastrados pelo SITE</option>
                        <option value="3" <?php if($tf_u_usuarios_novos == 3) echo "selected"; ?>>Sim, usuários novos sem os de cartão</option>
                        <option value="4" <?php if($tf_u_usuarios_novos == 4) echo "selected"; ?>>Não, sem usuários novos</option>
                    </select>
                </div>-->
                <div class="form-group"<?php echo $sbkg2;?>>
                    <label for="tf_u_saldo_positivo" class="w100">Saldo positivo</label>
                    <select name="tf_u_saldo_positivo" class="input-sm form-control" id="tf_u_saldo_positivo">
                        <option value="" <?php if(strtoupper($tf_u_saldo_positivo)!="P" && strtoupper($tf_u_saldo_positivo)!="N") echo "selected" ?>>LHs com qualquer saldo</option>
                        <option value="P" <?php if(strtoupper($tf_u_saldo_positivo)=="P") echo "selected" ?>>Apenas LHs com saldo POSITIVO</option>
                        <option value="N" <?php if(strtoupper($tf_u_saldo_positivo)=="N") echo "selected" ?>>Apenas LHs com saldo NEGATIVO</option>
                    </select>
                </div>
				<div class="form-group">
					<label for="como_conheceu" class="w100">Resposta: "Como conheceu a E-prepag?"</label>
					<select name="como_conheceu" id="como_conheceu" class="input-sm form-control">
						<option value="">Todos</option>
						<option value="facebook" <?php if($como_conheceu == "facebook") echo "selected"; ?>>Facebook</option>
						<option value="instagram" <?php if($como_conheceu == "instagram") echo "selected"; ?>>Instagram</option>
						<option value="youtube" <?php if($como_conheceu == "youtube") echo "selected"; ?>>YouTube</option>
						<option value="google" <?php if($como_conheceu == "google") echo "selected"; ?>>Pesquisa no Google</option>
						<option value="indicacao" <?php if($como_conheceu == "indicacao") echo "selected"; ?>>Indicação de um amigo</option>
						<option value="outro" <?php if($como_conheceu == "outro") echo "selected"; ?>>Outro</option>
					</select>
				</div>
<?php
                if($tf_u_com_totais_vendas)
                {
?>
                <div class="form-group top74">
                        <label class="w100">Produtos</label>
                        <div id='mostraValores'>
<?php
                    $i = 0;
                    while ($i < $num_col ) 
                    {
?>
                            <input type="checkbox" id="tf_produto[]" name="tf_produto[]" value="<?php echo $tf_produto[$i]?>" checked><?php echo $tf_produto[$i]?>
<?php
                        $i++;
                    }

                    if($resvalue) 
                    {
                        foreach($a_valores as $key => $val) 
                        {
?>
                            <input type="checkbox" id="tf_produto[]" name="tf_produto[]" value="<?php echo $key; ?>"
<?php
                            if ($tf_produto && is_array($tf_produto))
                                if (in_array($key, $tf_produto))
                                    echo " checked";
                                else
                                    if ($key == $tf_produto)
                                        echo " checked";
                            ?>><span title="<?php echo "n: ".$val; ?>"><?php echo $key . ",00"; ?></span>
<?php 
                        }
                    }
?>
                        </div>
                </div>
                <div class="form-group">
                    <label class="w100">Valor:</label>
                    <div id='mostraValores2'>
<?php
                    if($resvalue) 
                    {
                        foreach($a_valores as $key => $val) 
                        {
?>
                        <input type="checkbox" id="tf_pins[]" name="tf_pins[]" value="<?php echo $key; ?>"
<?php
                            if ($tf_valor && is_array($tf_pins))
                                if (in_array($key, $tf_pins))
                                    echo " checked";
                            else
                                if ($key == $tf_pins)
                                    echo " checked";
                        ?>>
                        <span title="<?php echo "n: ".$val; ?>"><?php echo $key . ",00"; ?></span></nobr>
    <?php 
                        }
                    }
?>
                    </div>
                </div>
<?php
                }
?>
            </div>
        </div>
    </div>
<!--    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title">NextCafé</h3>
      </div>
        <div class="panel-body">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="tf_u_login_nexcafe" class="w100">Login NexCafé Plus+</label>
                    <input name="tf_u_login_nexcafe" id="tf_u_login_nexcafe" type="text" class="input-sm form-control" value="<?php echo $tf_u_login_nexcafe; ?>" size="25" maxlength="100">
                </div>
                <div class="form-group">
                    <label for="tf_u_data_adesao_nexcafe_ini" class="w100">Data de Adesão NexCafé</label>
                        <input name="tf_u_data_adesao_nexcafe_ini" type="text" class="input-sm form-control" id="tf_u_data_adesao_nexcafe_ini" value="<?php echo $tf_u_data_adesao_nexcafe_ini ?>" size="9" maxlength="10">
                        <span class="text-info p-3  dislineblock">a</span>
                        <input name="tf_u_data_adesao_nexcafe_fim" type="text" class="input-sm form-control" id="tf_u_data_adesao_nexcafe_fim" value="<?php echo $tf_u_data_adesao_nexcafe_fim ?>" size="9" maxlength="10">
                        <span class="text-info p-3  dislineblock">(99/99/9999)</span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="tf_u_login_automatico_nexcafe" class="w100">Login Automático?</label>
                    <input type="checkbox" name="tf_u_login_automatico_nexcafe" id="tf_u_login_automatico_nexcafe" value="1" <?php if ($tf_u_login_automatico_nexcafe) { echo 'checked="checked"'; } ?>  style="float: left; display: block; text-align: left; width: auto; margin-top:0px;" />
                    <span class="text-info p-3  dislineblock">Deseja realizar o login autom&aacute;tico para venda de PINs via NexCaf&eacute;?</span>
                </div>
            </div>
        </div>
    </div>-->
    <div class="col-md-12">
        <input type="submit" name="BtnSearch" value="Buscar" class="btn btn-success btn-sm pull-right">
    </div>
<?php 
    if($msg != "")
    {
?>
    <div class="col-md-12 top10 alert alert-danger" role="alert">
        <strong><?php echo $msg;?></strong>
    </div>
<?php
    }
?>
    </form>
<?php
    if($total_table > 0) 
    {
        $ordem = ($ordem == 1)?2:1; 
?>
    <div id="focusAfterSubmited"></div>
    <script>
        $('html,body').animate({
        scrollTop: $("#focusAfterSubmited").offset().top},
        'slow');
    </script>
    <div class="col-md-12">
        <blockquote>
            <p>Exibindo resultados <strong><?php echo $inicial + 1 ?></strong> a <strong><?php echo $reg_ate ?></strong> de <strong><?php echo $total_table ?></strong></p>
        </blockquote>
    </div>
    <div class="col-md-6 espacamento">
        <div  class="btn btn-success btn-sm pull-left" id="area_correios">
            <span id="area_correios" onclick="gerarArquivoCorreios();" class="txt-branco">Gerar Arquivo de Destinatários dos Correios</span>
        </div>
    </div>
    <div class="col-md-6 espacamento">
        <div class="btn btn-success btn-sm pull-right" id="area">
            <span onclick="gerarArquivo();" class="txt-branco">Exportar Relatório</span>
        </div>
    </div>
<?php
	if(!empty($como_conheceu)) {
?>
	<div class="col-md-6 espacamento">
		<div class="btn btn-success btn-sm pull-left" id="area_conheceu">
			<span id="area_conheceu" onclick="gerarArquivoConheceu();" class="txt-branco">Gerar Arquivo Com Filtro "Como Conheceu E-prepag"</span>
		</div>
	</div>
<?php } ?>

</div>
</div></div>
    <div class="bg-branco">
	<div style="overflow: scroll;">
    <table class="table bg-branco table-bordered txt-preto text-center fontsize-pp">
        <thead>
            <tr class="bg-cinza-claro text-center">	    
				<td align="center">
					<strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_id&inicial=".$inicial.$varsel ?>">Cód.</a>
					<?php if($ncamp == 'ug_id') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?></strong>
				</td>
                <td align="center">
                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_ativo&inicial=".$inicial.$varsel ?>">Status</a>
                    <?php if($ncamp == 'ug_ativo') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    </strong>
                </td>
                <td align="center">
                    <strong><a href="<?php  echo $default_add."?ordem=".$ordem."&ncamp=ug_status&inicial=".$inicial.$varsel ?>">Status Busca</a>
                    <?php  if($ncamp == 'ug_status') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    </strong>
                </td>
				<?php if($_SESSION["visualiza_dados"] == "S"){ ?>
					<td align="center">
						<strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_login&inicial=".$inicial.$varsel ?>">Login</a>
						<?php if($ncamp == 'ug_login') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
						</strong>
					</td>
				<?php } ?>
                <td align="center">
                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_nome_fantasia&inicial=".$inicial.$varsel ?>">Nome Fantasia</a>
                    <?php if($ncamp == 'ug_nome_fantasia') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    </strong>
                </td>
<!--                <td align="center">
                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_nome&inicial=".$inicial.$varsel ?>">Nome</a>
                    <?php if($ncamp == 'ug_nome') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    </strong>
                </td>-->
				<?php if($_SESSION["visualiza_dados"] == "S"){ ?>
					<td align="center">
						<strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_cnpj&inicial=".$inicial.$varsel ?>">CNPJ</a>
						<?php if($ncamp == 'ug_cnpj') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
						</strong>
					</td>
				<?php } ?>
<?php 
            if(!$tf_u_sem_dados_cadastro) 
            {
?>
<!--                <td align="center">
                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=te_descricao&inicial=".$inicial.$varsel ?>">Tipo Estabelecimento</a>
                    <?php if($ncamp == 'te_descricao') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    </strong>
                </td>
                <td align="center">
                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_cpf&inicial=".$inicial.$varsel ?>">CPF</a>
                    <?php if($ncamp == 'ug_cpf') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    </strong>
                </td>
                <td align="center">
                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_rg&inicial=".$inicial.$varsel ?>">RG</a>
                    <?php if($ncamp == 'ug_rg') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    </strong>
                </td>
                <td align="center">
                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_responsavel&inicial=".$inicial.$varsel ?>">Responsável</a>
                    <?php if($ncamp == 'ug_responsavel') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    </strong>
                </td>-->
				<?php if($_SESSION["visualiza_dados"] == "S"){ ?>
					<td align="center">
						<strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_email&inicial=".$inicial.$varsel ?>">Email</a>
						<?php if($ncamp == 'ug_email') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
						</strong>
					</td>
				<?php } ?>
				<td align="center">
                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_tel_ddd&inicial=".$inicial.$varsel ?>">Número</a>
                    <?php if($ncamp == 'ug_tel_ddd') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    </strong>
                </td>
				
				<td align="center">
					<strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_ficou_sabendo&inicial=".$inicial.$varsel ?>">Como conheceu a E-prepag?</a>
                    <?php if($ncamp == 'ug_ficou_sabendo') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    </strong>
                </td>
                <td align="center">
                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_data_inclusao&inicial=".$inicial.$varsel ?>">Data de Cadastro</a>
                    <?php if($ncamp == 'ug_email') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    </strong>
                </td>
                <td align="center">
                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_endereco&inicial=".$inicial.$varsel ?>">Endereço</a>
                    <?php if($ncamp == 'ug_endereco') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    </strong>
                </td>
                <td align="center">
                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_bairro&inicial=".$inicial.$varsel ?>">Bairro</a>
                    <?php if($ncamp == 'ug_bairro') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    </strong>
                </td>
                <td align="center">
                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_cidade&inicial=".$inicial.$varsel ?>">Cidade</a>
                    <?php if($ncamp == 'ug_cidade') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    </strong>
                </td>
                <td align="center">
                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_estado&inicial=".$inicial.$varsel ?>">Estado</a>
                    <?php if($ncamp == 'ug_estado') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    </strong>
                </td>
                <td align="center">
                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_cep&inicial=".$inicial.$varsel ?>">CEP</a>
                    <?php if($ncamp == 'ug_cep') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    </strong>
                </td>
<!--                <td align="center">
                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_tel&inicial=".$inicial.$varsel ?>">Telefone</a>
                    <?php if($ncamp == 'ug_tel') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    </strong>
                </td>
                <td align="center">
                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_cel&inicial=".$inicial.$varsel ?>">Celular</a>
                    <?php if($ncamp == 'ug_cel') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    </strong>
                </td>-->
<?php 
            } 
    
            if($tf_u_com_totais_vendas ) 
            {
?>
                <td align="center">
                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=vg_valor&inicial=".$inicial.$varsel ?>">Vendas R$</a>
                    <?php if($ncamp == 'vg_valor') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    </strong>
                </td>
                <td align="center">
                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=vg_qtde_itens&inicial=".$inicial.$varsel ?>">n Vendas</a>
                    <?php if($ncamp == 'vg_qtde_itens') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    </strong>
                </td>
                <td align="center">
                    <strong>Ticket médio</strong>
                </td>
                <td align="center">
                    <strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=vg_data_ultima_venda&inicial=".$inicial.$varsel ?>">Data última venda</a>
                    <?php if($ncamp == 'vg_data_ultima_venda') echo "<img src=".$img_seta." width='10' height='7' align='absmiddle'>"; ?>
                    </strong>
                </td>
                <td align="center">
                    <strong>Status</strong>
                </td>
<?php
            }

            if($tf_u_compet_participa!="") 
            { 
?>
                <td align="center"><strong>No&nbsp;Fifa</strong></td>
                <td align="center"><strong>No&nbsp;WC3</strong></td>
<?php 
            } 
?>
                <!--<td align="center"><strong>Perfil&nbsp;Limite</strong></td>-->
                <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_perfil_saldo&inicial=".$inicial.$varsel ?>">Perfil Saldo</a></strong></td>

                <td align="center"><strong><a href="<?php echo $default_add."?ordem=".$ordem."&ncamp=ug_coord_lng&inicial=".$inicial.$varsel ?>">GMaps</a></strong></td>
                <td align="center"><strong>VMaps</strong></td>
            </tr>
        </thead>
        <tbody>
<?php
        $i = 0;
		$como_conheceu = strtoupper($_REQUEST['como_conheceu']);
		
		echo "<input type='hidden' id='resposta_como_conheceu' name='resposta_como_conheceu' value='{$como_conheceu}'>";
		
		if (!empty($como_conheceu)) {
			
			$sql_como_conheceu = "select * from dist_usuarios_games where ug_ficou_sabendo LIKE '%{$como_conheceu}%';";
			$resultado_como_conheceu = SQLexecuteQuery($sql_como_conheceu);			
			
			while($rs_usuario_row = pg_fetch_array($resultado_como_conheceu)):
				
				if ($rs_usuario_row['ug_ativo'] == 1) {
					$ativo = "Ativo";
				} elseif ($rs_usuario_row['ug_ativo'] == 2) {
					$ativo = "Inativo";
				} else {
					$ativo = "Indefinido";
				}
				
				if ($rs_usuario_row['ug_status'] == 1) {
					$status_busca = "Ativo";
				} elseif ($rs_usuario_row['ug_status'] == 2) {
					$status_busca = "Inativo";
				} else {
					$status_busca = "Indefinido";
				}
			?>
				<tr class="trListagem">
					<td align="center"><a style="text-decoration:none" href="com_usuario_detalhe.php?usuario_id=<?php echo $rs_usuario_row['ug_id'] ?>"><?php echo $rs_usuario_row['ug_id'] ?></a></td>
					<td align="center"><?php echo (($rs_usuario_row['ug_ativo']!="1")?"<font color='#FF0000'>":"").$ativo.(($rs_usuario_row['ug_ativo']!="1")?"":"") ?></td>
					<td align="center"><?php  echo (($rs_usuario_row['ug_status']!=1)?"<font color='#FF0000'>":"").$status_busca.(($rs_usuario_row['ug_status']!=1)?"":"") ?></td>
		<?php
				
				if($_SESSION["visualiza_dados"] == "S"){ ?>
                    <td><?php echo $rs_usuario_row['ug_login'] ?></td>
		<?php 
				}
		?>
					<td><a style="text-decoration:none" href="com_usuario_detalhe.php?usuario_id=<?php echo $rs_usuario_row['ug_id'] ?>"><?php echo $rs_usuario_row['ug_nome_fantasia']; ?></a></td>
		<?php
				if($_SESSION["visualiza_dados"] == "S"){ ?>
					<td><?php echo $rs_usuario_row['ug_cnpj']; ?></td>
		<?php 
				}
				
				if(!$tf_u_sem_dados_cadastro) {
					if($_SESSION["visualiza_dados"] == "S") {
		?>
						<td><?php echo $rs_usuario_row['ug_email'] ?></td>

		<?php 
					} 
		?>
					<td>
		<?php 
						if(strcmp($rs_usuario_row['ug_cel_ddi']." (".$rs_usuario_row['ug_cel_ddd'].") ".$rs_usuario_row['ug_cel'], "55 ()") != 1){
							echo $rs_usuario_row['ug_cel_ddi']." (".$rs_usuario_row['ug_cel_ddd'].") ".$rs_usuario_row['ug_cel'];
								
						} else {
							echo $rs_usuario_row['ug_tel_ddi']." (".$rs_usuario_row['ug_tel_ddd'].") ".$rs_usuario_row['ug_tel'];
						}
		?>
					</td>

					<td><?php echo $rs_usuario_row['ug_ficou_sabendo']; ?></td>
					<td><?php echo substr($rs_usuario_row['ug_data_inclusao'], 0, 10) ?></td>
					<td><?php echo $rs_usuario_row['ug_tipo_end'] ?> <?php echo $rs_usuario_row['ug_endereco'] ?>, <?php echo $rs_usuario_row['ug_numero'] ?> <?php echo $rs_usuario_row['ug_complemento'] ?> </td>
					<td><?php echo $rs_usuario_row['ug_bairro'] ?></td>
					<td><?php echo $rs_usuario_row['ug_cidade'] ?></td>
					<td><?php echo $rs_usuario_row['ug_estado'] ?></td>
					<td ><?php echo $rs_usuario_row['ug_cep'] ?></td>
		<?php
				}
				
				if($tf_u_com_totais_vendas) {
					$vg_qtde_itens = (($rs_usuario_row['vg_qtde_itens']>0)?$rs_usuario_row['vg_qtde_itens']:1);
		?>
					<td align="right"><?php echo number_format($rs_usuario_row['vg_valor'], 2, '.', '.') ?></td>
					<td align="right"><?php echo $vg_qtde_itens ?></td>
					<td align="right"><?php echo number_format($rs_usuario_row['vg_valor']/$vg_qtde_itens, 2, '.', '.') ?></td>
					<td align="right" title="Primeira venda: '<?php echo substr($rs_usuario_row['vg_data_primeira_venda'], 0, 19) ?>'"><?php echo substr($rs_usuario_row['vg_data_ultima_venda'], 0, 19) ?></td>
		<?php
					$status = qtde_dias(substr($rs_usuario_row['vg_data_ultima_venda'], 8, 2)."-".substr($rs_usuario_row['vg_data_ultima_venda'], 5, 2)."-".substr($rs_usuario_row['vg_data_ultima_venda'], 0, 4),date('d-m-Y'));

					if ($status <= 15) {
						$status_label    =    "Frequente";
					} elseif ($status > 15 && $status <= 30) {
						$status_label    =    "Abandonou";
					} elseif($status > 15) {
						$status_label    =    "Atrasado";
					}
		?>
					<td align="right"><?php echo $status_label ?></td>
		<?php
				}
				
				if($tf_u_compet_participa!="") {
		?>
					<td align="center"><?php echo $rs_usuario_row['ug_compet_participantes_fifa'] ?></td>
					<td align="center"><?php echo $rs_usuario_row['ug_compet_participantes_wc3'] ?></td>
		<?php
				}
		?>
					<td><font color='<?php echo (($rs_usuario_row['ug_perfil_saldo']<0)?"red":(($rs_usuario_row['ug_perfil_saldo']>0)?"blue":"black")); ?>'>R$ <?php echo number_format($rs_usuario_row['ug_perfil_saldo'], 2, '.', '.') ?></td>
		
		<?php
		
				$statusMaps = $rs_usuario_row['ug_google_maps_status'];

				$statusMaps_descr = "";
				
				switch($statusMaps) {
					case 1:
						$statusMaps_descr = "Não Localizada";
                        
						break;
					case 2:
						$statusMaps_descr = "Fora do Brasil";
						break;
					default:
						$statusMaps_descr = "Tipo Desconhecido";
						if(strlen(trim($statusMaps))==0) $statusMaps_descr .= " (Empty)";
						else $statusMaps_descr .= " ('$statusMaps')";
						break;
				}
				
				if($rs_usuario_row['ug_coord_lat']==0 && $rs_usuario_row['ug_coord_lng']==0) {
					if($statusMaps_descr!="") $statusMaps_descr.= "\n";
					$statusMaps_descr .= "Sem Geolocalização";
				} else {
					$statusMaps_descr .= "\n[".number_format($rs_usuario_row['ug_coord_lat'], 2, '.', '.').", ".number_format($rs_usuario_row['ug_coord_lng'], 2, '.', '.')."]";
				}
				if(trim($statusMaps)=="") {
					if($rs_usuario_row['ug_coord_lat']==0 && $rs_usuario_row['ug_coord_lng']==0) {
						$statusMaps = "<font color='red'>Coords=0";
					} else {
						$statusMaps = "<font color='blue'>Com_Coords";
					}
				}
		?>
					<td title="<?php echo $statusMaps_descr ?>" align="center"><?php echo $statusMaps ?></td>
					<td align="center">
		<?php
						$ug_endereco = $rs_usuario_row['ug_tipo_end'] . "','" . str_replace("'", "\'", $rs_usuario_row['ug_endereco']) . "','" . str_replace("'", "\'", $rs_usuario_row['ug_bairro']) . "','" . str_replace("'", "\'", $rs_usuario_row['ug_cidade']) . "','" . $rs_usuario_row['ug_id'] . "','Brasil','" . $rs_usuario_row['ug_cep'] . "','" . $rs_usuario_row['ug_estado'] . "','" . $rs_usuario_row['ug_numero'] . "";
				
		?>
						<a href="javascript:void(0);" onClick="validaGeo('<?php echo $ug_endereco ?>');"><img src="/images/pdv/global-search-icon_peq.jpg" width="28" height="21" border="0" title="<?php echo "Lat/Lng: [".$rs_usuario_row['ug_coord_lat']." , ".$rs_usuario_row['ug_coord_lng']."]\n '".$ug_endereco ."'" ?>"></a>
					</td>
				</tr>
		<?php
			endwhile;

		} else {
			
		
			while($rs_usuario_row = pg_fetch_array($rs_usuario)) {			
				if($rs_usuario_row['ug_ativo'] == 1)
					$ativo = "Ativo";
				elseif($rs_usuario_row['ug_ativo'] == 2)
					$ativo = "Inativo";
				else
					$ativo = "Indefinido";
				
				if($rs_usuario_row['ug_status'] == 1)
					$status_busca = "Ativo";
				elseif($rs_usuario_row['ug_status'] == 2)
					$status_busca = "Inativo";
				else
					$status_busca = "Indefinido";
		?>



				<tr class="trListagem">
					<td align="center"><a style="text-decoration:none" href="com_usuario_detalhe.php?usuario_id=<?php echo $rs_usuario_row['ug_id'] ?>"><?php echo $rs_usuario_row['ug_id'] ?></a></td>
					<td align="center"><?php echo (($rs_usuario_row['ug_ativo']!="1")?"<font color='#FF0000'>":"").$ativo.(($rs_usuario_row['ug_ativo']!="1")?"":"") ?></td>
					<td align="center"><?php  echo (($rs_usuario_row['ug_status']!=1)?"<font color='#FF0000'>":"").$status_busca.(($rs_usuario_row['ug_status']!=1)?"":"") ?></td>
					<?php if($_SESSION["visualiza_dados"] == "S"){ ?>
						 <td><?php echo $rs_usuario_row['ug_login'] ?></td>
					<?php } ?>
					<td><a style="text-decoration:none" href="com_usuario_detalhe.php?usuario_id=<?php echo $rs_usuario_row['ug_id'] ?>"><?php echo $rs_usuario_row['ug_nome_fantasia'];/*strlen($rs_usuario_row['ug_nome_fantasia']) > 40 ? substr($rs_usuario_row['ug_nome_fantasia'],0,37)."..." :*/ ?></a></td>
					<!--<td><a style="text-decoration:none" href="com_usuario_detalhe.php?usuario_id=<?php echo $rs_usuario_row['ug_id'] ?>"><?php echo $rs_usuario_row['ug_nome'] ?></a></td>-->
					<?php if($_SESSION["visualiza_dados"] == "S"){ ?>
						 <td ><?php echo $rs_usuario_row['ug_cnpj']; ?></td>
					<?php } ?>
<?php 
				if(!$tf_u_sem_dados_cadastro) 
				{
?>
					<!--<td ><?php echo $rs_usuario_row['te_descricao'] ?></td>-->
					<!--<td ><?php echo $rs_usuario_row['ug_cpf'] ?></td>-->
					<!--<td ><?php echo $rs_usuario_row['ug_rg'] ?></td>-->
					<!--<td><?php echo $rs_usuario_row['ug_responsavel'] ?></td>-->
					<?php if($_SESSION["visualiza_dados"] == "S"){ ?>
						<td><?php echo $rs_usuario_row['ug_email'] ?></td>
					<?php } ?>
					<td>
						<?php 
							if(strcmp($rs_usuario_row['ug_cel_ddi']." (".$rs_usuario_row['ug_cel_ddd'].") ".$rs_usuario_row['ug_cel'], "55 ()") != 1){
								echo $rs_usuario_row['ug_cel_ddi']." (".$rs_usuario_row['ug_cel_ddd'].") ".$rs_usuario_row['ug_cel'];
								
							}
							else {
								echo $rs_usuario_row['ug_tel_ddi']." (".$rs_usuario_row['ug_tel_ddd'].") ".$rs_usuario_row['ug_tel'];
							}
						?>
					</td>

					<td><?php echo $rs_usuario_row['ug_ficou_sabendo']; ?></td>
					<td><?php echo substr($rs_usuario_row['ug_data_inclusao'], 0, 10) ?></td>
					<td><?php echo $rs_usuario_row['ug_tipo_end'] ?> <?php echo $rs_usuario_row['ug_endereco'] ?>, <?php echo $rs_usuario_row['ug_numero'] ?> <?php echo $rs_usuario_row['ug_complemento'] ?> </td>
					<td><?php echo $rs_usuario_row['ug_bairro'] ?></td>
					<td><?php echo $rs_usuario_row['ug_cidade'] ?></td>
					<td><?php echo $rs_usuario_row['ug_estado'] ?></td>
					<td ><?php echo $rs_usuario_row['ug_cep'] ?></td>
	<!--                <td  align="center" onMouseOver="this.className='linkover3'" onMouseOut="this.className='<?php echo $sstyle; ?>'">
						<?php echo $rs_usuario_row['ug_tel_ddi']." (".$rs_usuario_row['ug_tel_ddd'].") ".$rs_usuario_row['ug_tel']; ?>
					</td>
					<td  align="center" onMouseOver="this.className='linkover3'" onMouseOut="this.className='<?php echo $sstyle; ?>'">
						<?php echo $rs_usuario_row['ug_cel_ddi']." (".$rs_usuario_row['ug_cel_ddd'].") ".$rs_usuario_row['ug_cel']; ?>
					</td>-->
<?php 
				} 

				if($tf_u_com_totais_vendas ) 
				{
					$vg_qtde_itens = (($rs_usuario_row['vg_qtde_itens']>0)?$rs_usuario_row['vg_qtde_itens']:1);
?>
					<td align="right"><?php echo number_format($rs_usuario_row['vg_valor'], 2, '.', '.') ?></td>
					<td align="right"><?php echo $vg_qtde_itens ?></td>
					<td align="right"><?php echo number_format($rs_usuario_row['vg_valor']/$vg_qtde_itens, 2, '.', '.') ?></td>
					<td align="right" title="Primeira venda: '<?php echo substr($rs_usuario_row['vg_data_primeira_venda'], 0, 19) ?>'"><?php echo substr($rs_usuario_row['vg_data_ultima_venda'], 0, 19) ?></td>
<?php
					$status                    =    qtde_dias(substr($rs_usuario_row['vg_data_ultima_venda'], 8, 2)."-".substr($rs_usuario_row['vg_data_ultima_venda'], 5, 2)."-".substr($rs_usuario_row['vg_data_ultima_venda'], 0, 4),date('d-m-Y'));

					if ($status <= 15) 
					{
						$status_label    =    "Frequente";
					}
					elseif($status > 15 && $status <= 30)
					{
						$status_label    =    "Abandonou";
					}
					elseif($status > 15)
					{
						$status_label    =    "Atrasado";
					}
?>
						<td align="right"><?php echo $status_label ?></td>
<?php
				}
            
				if($tf_u_compet_participa!="") 
				{
?>
					<td align="center"><?php echo $rs_usuario_row['ug_compet_participantes_fifa'] ?></td>
					<td align="center"><?php echo $rs_usuario_row['ug_compet_participantes_wc3'] ?></td>
<?php 
				} 
?>
					<!--<td>R$ <?php echo number_format($rs_usuario_row['ug_perfil_limite'], 2, '.', '.') ?></td>-->
					<td><font color='<?php echo (($rs_usuario_row['ug_perfil_saldo']<0)?"red":(($rs_usuario_row['ug_perfil_saldo']>0)?"blue":"black")); ?>'>R$ <?php echo number_format($rs_usuario_row['ug_perfil_saldo'], 2, '.', '.') ?></td>
<?php
				$statusMaps = $rs_usuario_row['ug_google_maps_status'];

				$statusMaps_descr = "";
				switch($statusMaps) {
					case 1:
						$statusMaps_descr = "Não Localizada";
						break;
					case 2:
						$statusMaps_descr = "Fora do Brasil";
						break;
					default:
						$statusMaps_descr = "Tipo Desconhecido";
						if(strlen(trim($statusMaps))==0) $statusMaps_descr .= " (Empty)";
						else $statusMaps_descr .= " ('$statusMaps')";
						break;
				}
				if($rs_usuario_row['ug_coord_lat']==0 && $rs_usuario_row['ug_coord_lng']==0) {
					if($statusMaps_descr!="") $statusMaps_descr.= "\n";
					$statusMaps_descr .= "Sem Geolocalização";
				} else {
					$statusMaps_descr .= "\n[".number_format($rs_usuario_row['ug_coord_lat'], 2, '.', '.').", ".number_format($rs_usuario_row['ug_coord_lng'], 2, '.', '.')."]";
				}
				if(trim($statusMaps)=="") {
					if($rs_usuario_row['ug_coord_lat']==0 && $rs_usuario_row['ug_coord_lng']==0) {
						$statusMaps = "<font color='red'>Coords=0";
					} else {
						$statusMaps = "<font color='blue'>Com_Coords";
					}
				}
?>
					<td title="<?php echo $statusMaps_descr ?>" align="center"><?php echo $statusMaps ?></td>
					<td align="center">
                <?php
						$ug_endereco = $rs_usuario_row['ug_tipo_end'] . "','" . str_replace("'", "\'", $rs_usuario_row['ug_endereco']) . "','" . str_replace("'", "\'", $rs_usuario_row['ug_bairro']) . "','" . str_replace("'", "\'", $rs_usuario_row['ug_cidade']) . "','" . $rs_usuario_row['ug_id'] . "','Brasil','" . $rs_usuario_row['ug_cep'] . "','" . $rs_usuario_row['ug_estado'] . "','" . $rs_usuario_row['ug_numero'] . "";
                ?>
						<a href="javascript:void(0);" onClick="validaGeo('<?php echo $ug_endereco ?>');"><img src="/images/pdv/global-search-icon_peq.jpg" width="28" height="21" border="0" title="<?php echo "Lat/Lng: [".$rs_usuario_row['ug_coord_lat']." , ".$rs_usuario_row['ug_coord_lng']."]\n '".$ug_endereco ."'" ?>"></a>
					</td>
				</tr>
<?php     
			}
		}
		
?>

            <tr>
                <td colspan="20" bgcolor="#FFFFFF" class="texto"><?php echo $search_msg . number_format(getmicrotime() - $time_start, 2, '.', '.') . $search_unit ?></td>
            </tr>
<?php
        paginacao_query($inicial, $total_table, $max, 20, $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel);
?>
        </tbody>
        </table>
		</div>
    </div>
<div><div>
<?php
    }

    require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
<script>

<?php
    if($tf_u_com_totais_vendas ) {    //&& $dd_opr_codigo
?>
    load_caixas();
    v_precos();
<?php
    }
?>

$('#dd_opr_codigo').change( function() {

    load_caixas();
    v_precos();


});
</script>
</html>
