<?php 
header("Content-Type: text/html; charset=ISO-8859-1",true);

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
require_once $raiz_do_projeto."includes/pdv/inc_pesquisa_usuarios_sql.php";

set_time_limit ( 30000 ) ;

$run_silently = "OK";

$mensagem = "";

if($tf_u_com_totais_vendas ) {	// && $dd_opr_codigo
	$mensagem .= "Id\tLogin\tFantasia\tNome\tCNPJ\tEMail\tDDD+Celular\tDDD+Telefone\tComo conheceu a E-prepag ?\tData de Cadastro\tCategoria\tSaldo\tEndereco\tBairro\tCidade\tEstado\tCEP\tVendas R$\tn Vendas\tTicket medio\tData ultima venda\tStatus\tTipo de Estabelecimento\n\n";
}
else {	
	$mensagem .= "Id\tLogin\tFantasia\tNome\tCNPJ\tEMail\tDDD+Celular\tDDD+Telefone\tComo conheceu a E-prepag ?\tData de Cadastro\tCategoria\tSaldo\tEndereco\tBairro\tCidade\tEstado\tCEP\tTipo de Estabelecimento\n\n";
}

//echo $rs_usuario;
//exit;
while($rs_usuario_row = pg_fetch_array($rs_usuario)){
	$cor1 = ($cor1 == $cor2)?$cor3:$cor2;
	$ug_id			=	$rs_usuario_row['ug_id'];
	$ug_login		=	$rs_usuario_row['ug_login'];
	$ug_nome_fantasia=	$rs_usuario_row['ug_nome_fantasia'];
    $ug_data_inclusao = substr($rs_usuario_row['ug_data_inclusao'], 0,10);
	$ug_nome		=	$rs_usuario_row['ug_nome'];
	$ug_cnpj		=	$rs_usuario_row['ug_cnpj'];
	$ug_email		=	$rs_usuario_row['ug_email'];
	$ug_endereco	=	$rs_usuario_row['ug_endereco'].",".$rs_usuario_row['ug_numero']." ".$rs_usuario_row['ug_complemento'];
	$ug_bairro		=	$rs_usuario_row['ug_bairro'];
	$ug_cidade		=	$rs_usuario_row['ug_cidade'];
	$ug_estado		=	$rs_usuario_row['ug_estado'];
	$ug_cep			=	$rs_usuario_row['ug_cep'];
        $ug_tel                 =       $rs_usuario_row['ug_tel'];
        $ug_tel_ddd             =       $rs_usuario_row['ug_tel_ddd'];
        $ug_cel_ddd		=	$rs_usuario_row['ug_cel_ddd'];
        $ug_cel			=	$rs_usuario_row['ug_cel'];
		$ug_ficou_sabendo	= $rs_usuario_row['ug_ficou_sabendo'];
        $ug_tipo_end		=	$rs_usuario_row['ug_tipo_end'];
        $te_descricao   	=	utf8_decode($rs_usuario_row['te_descricao']);
        //$ug_perfil_saldo	=	number_format($rs_usuario_row['ug_perfil_saldo'], 2, ',', '.'); Pontuação quebrou em nova coluna
        $ug_perfil_saldo	=	$rs_usuario_row['ug_perfil_saldo'];
        switch($rs_usuario_row['ug_vip']){
            case 0: 
                $ug_vip = "Normal";
                break;
            case 1: 
                $ug_vip = "VIP";
                break;
            case 2: 
                $ug_vip = "Master";
                break;
            case 3: 
                $ug_vip = "Black";
                break;
            case 4: 
                $ug_vip = "Gold";
                break;
            default:
                $ug_vip = "Categoria NÃO Registrada";
                break;
        } //end switch
        
	if($tf_u_com_totais_vendas ) {	// && $dd_opr_codigo
			$vg_valor				=	number_format($rs_usuario_row['vg_valor'], 2, ',', '.');
			$vg_qtde_itens			=	(($rs_usuario_row['vg_qtde_itens']>0)?$rs_usuario_row['vg_qtde_itens']:1);
			$ticket_medio			=	number_format($rs_usuario_row['vg_valor']/$vg_qtde_itens, 2, ',', '.');
			$vg_data_ultima_venda	=	substr($rs_usuario_row['vg_data_ultima_venda'], 0, 19);
			//2011-10-14 
			$status					=	qtde_dias(substr($rs_usuario_row['vg_data_ultima_venda'], 8, 2)."-".substr($rs_usuario_row['vg_data_ultima_venda'], 5, 2)."-".substr($rs_usuario_row['vg_data_ultima_venda'], 0, 4),date('d-m-Y'));
			if ($status <= 15) {
				$status_label	=	"Frequente";
			}
			elseif($status > 15 && $status <= 30){
				$status_label	=	"Abandonou";
			}
			elseif($status > 15){
				$status_label	=	"Atrasado";
			}
			$mensagem .= 	$ug_id."\t".$ug_login."\t".$ug_nome_fantasia."\t".$ug_nome."\t".$ug_cnpj."\t".$ug_email."\t".$ug_cel_ddd." ".$ug_cel."\t".$ug_tel_ddd." ".$ug_tel."\t".$ug_ficou_sabendo."\t".$ug_data_inclusao."\t".$ug_vip."\t".$ug_perfil_saldo."\t".$ug_tipo_end.": ".$ug_endereco."\t".$ug_bairro."\t".$ug_cidade."\t".$ug_estado."\t".$ug_cep."\t".$vg_valor."\t".$vg_qtde_itens."\t".$ticket_medio."\t".$vg_data_ultima_venda."\t".$status_label."\t".$te_descricao."\n";
	}
	else {
		$mensagem .= 	$ug_id."\t".$ug_login."\t".$ug_nome_fantasia."\t".$ug_nome."\t".$ug_cnpj."\t".$ug_email."\t".$ug_cel_ddd." ".$ug_cel."\t".$ug_tel_ddd." ".$ug_tel."\t".$ug_ficou_sabendo."\t".$ug_data_inclusao."\t".$ug_vip."\t".$ug_perfil_saldo."\t".$ug_tipo_end.": ".$ug_endereco."\t".$ug_bairro."\t".$ug_cidade."\t".$ug_estado."\t".$ug_cep."\t".$te_descricao."\n";
	}
}
if(b_IsUsuarioReinaldo()||b_IsUsuarioWagner()) { 
//	echo "mensagem: ".$mensagem."<br>";
//	die("Stop");
}         
$file_ret = grava_arquivo_emails_user($mensagem); 

?>
<a class="txt-branco" target="_blank" href="/includes/download/dld.php?f=<?php echo $file_ret; ?>&fc=<?php echo $tf_tipo."_".date("YmdHis").".txt"; ?>">Arquivo TXT com Todos os Registros da Sele&ccedil;&atilde;o</a>
</html>
<?php 
// Tomado de CodeIgniter (ver POS lista_transacoes_gr.php)
function grava_arquivo_emails_user($mensagem) {

                global $raiz_do_projeto;
		$file_path = $raiz_do_projeto . "public_html/tmp/txt/";
		$web_path = "/tmp/txt/";
		$expiration = 20;

		// -----------------------------------
		// Remove old files	
		// -----------------------------------
				
		list($usec, $sec) = explode(" ", microtime());
		$now = ((float)$usec + (float)$sec);
		$current_dir = @opendir($file_path);
                if(is_dir($file_path)) {
                    while($filename = @readdir($current_dir)) {
                            if ($filename != "." and $filename != ".." and $filename != "index.html") {
                                    $name = str_replace(".txt", "", $filename);
                                    if (($name + $expiration) < $now) {
                                            @unlink($file_path.$filename);
                                    }
                            }
                    }
                    @closedir($current_dir);
                }

		//Arquivo
		$file = $file_path.$now.".txt";
	
		//Grava mensagem no arquivo
		if ($handle = fopen($file, 'a+')) {
			fwrite($handle, $mensagem);
			fclose($handle);
		} 
		
//		$file_return = 'http://'.$_SERVER['HTTP_HOST'].$web_path.$now.".txt";
		$file_return = $now.".txt";

		return $file_return;
}
?>