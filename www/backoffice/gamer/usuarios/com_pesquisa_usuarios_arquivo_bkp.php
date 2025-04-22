<?php 
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";

include $raiz_do_projeto."includes/main.php";

if(isset($tf_u_cidade)) $tf_u_cidade = utf8_decode($tf_u_cidade);

include $raiz_do_projeto."includes/gamer/inc_pesquisa_usuarios_sql.php";

$mensagem = "";

if($tf_u_com_totais_vendas ) {	// && $dd_opr_codigo
	$mensagem .= "ID\tNome\tLogin\tEMail\tData de Cadastro\tCPF\tSexo\tEndereco\tBairro\tCidade\tEstado\tCEP\tVendas R$\tn Vendas\tTicket medio\tData ultima venda\tStatus\tData de Nascimento\tSaldo Atual\n\n";
}
else {	
	$mensagem .= "ID\tNome\tLogin\tEMail\tData de Cadastro\tCPF\tSexo\tEndereco\tBairro\tCidade\tEstado\tCEP\tData de Nascimento\tSaldo Atual\n\n";
}

while($rs_usuario_row = pg_fetch_array($rs_usuario)){
	$cor1 = ($cor1 == $cor2)?$cor3:$cor2;
	$ug_id		=	$rs_usuario_row['ug_id'];
    $ug_nome	=	$rs_usuario_row['ug_nome'];
    $ug_email	=	$rs_usuario_row['ug_email'];
    $ug_data_inclusao = substr($rs_usuario_row['ug_data_inclusao'], 0,10);
    $ug_cpf		=	$rs_usuario_row['ug_cpf'];
    $ug_login	=	$rs_usuario_row['ug_login'];
    $ug_sexo	=	$rs_usuario_row['ug_sexo'];
    $ug_endereco=	$rs_usuario_row['ug_endereco'].",".$rs_usuario_row['ug_numero']." ".$rs_usuario_row['ug_complemento'];
    $ug_bairro	=	$rs_usuario_row['ug_bairro'];
    $ug_cidade	=	$rs_usuario_row['ug_cidade'];
    $ug_estado	=	$rs_usuario_row['ug_estado'];
    $ug_cep		=	$rs_usuario_row['ug_cep'];
    $ug_data_nascimento	=	substr($rs_usuario_row['ug_data_nascimento'], 0, 10);
    $ug_perfil_saldo	=	$rs_usuario_row['ug_perfil_saldo'];
	if($tf_u_com_totais_vendas ) {	// && $dd_opr_codigo
			$vg_valor				=	number_format($rs_usuario_row['vg_valor'], 2, '.', '.');
			$vg_qtde_itens			=	(($rs_usuario_row['vg_qtde_itens']>0)?$rs_usuario_row['vg_qtde_itens']:1);
			$ticket_medio			=	number_format($rs_usuario_row['vg_valor']/$vg_qtde_itens, 2, '.', '.');
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
			$mensagem .= 	$ug_id."\t".$ug_nome."\t".$ug_login."\t".$ug_email."\t".$ug_data_inclusao. "\t".$ug_cpf."\t".$ug_login."\t".$ug_sexo."\t".$ug_endereco."\t".$ug_bairro."\t".$ug_cidade."\t".$ug_estado."\t".$ug_cep."\t".$vg_valor."\t".$vg_qtde_itens."\t".$ticket_medio."\t".$vg_data_ultima_venda."\t".$status_label."\t".$ug_data_nascimento."\t".$ug_perfil_saldo."\n";
	}
	else {
		$mensagem .= 	$ug_id."\t".$ug_nome."\t".$ug_login."\t".$ug_email."\t".$ug_data_inclusao. "\t".$ug_cpf."\t".$ug_sexo."\t".$ug_endereco."\t".$ug_bairro."\t".$ug_cidade."\t".$ug_estado."\t".$ug_cep."\t".$ug_data_nascimento."\t".$ug_perfil_saldo."\n";
	}
	
}
          
$file_ret = grava_arquivo_emails_user($mensagem); 
$tf_tipo = "u";
?>
<a href="/includes/download/download.php?f=<?php echo $file_ret; ?>&fc=<?php echo $tf_tipo."_".date("YmdHis").".txt"; ?>">Arquivo TXT com Todos os Registros da Sele&ccedil;&atilde;o</a>
</html>
<?php 
// Tomado de CodeIgniter (ver POS lista_transacoes_gr.php)
function grava_arquivo_emails_user($mensagem) {

        global $raiz_do_projeto;
    
		$file_path = $raiz_do_projeto . "backoffice/includes/download/tmp/";
		$web_path = "/includes/download/tmp/";
		$expiration = 200;

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