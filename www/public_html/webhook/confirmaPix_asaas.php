<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "/www/includes/constantes.php";
require_once "/www/includes/constantesPagamento.php";
require_once "/www/includes/gamer/constantes.php";
require_once "/www/includes/main.php";
require_once "/www/includes/inc_Pagamentos.php";
require_once "/www/includes/functions.php";
require_once "/www/includes/load_dotenv.php";

define("ASAAS_SECRET_TOKEN", getenv('ASAAS_SECRET_TOKEN'));

// Pegue o token enviado pelo Asaas no header

$receivedToken = isset($_SERVER['HTTP_ASAAS_ACCESS_TOKEN']) ? $_SERVER['HTTP_ASAAS_ACCESS_TOKEN'] : "nao existe";

if ($receivedToken !== ASAAS_SECRET_TOKEN) {
    // Se o token estiver errado, rejeita a requisi��o
    
	http_response_code(403);
	
    exit("Acesso negado. Token inv�lido: $receivedToken");
}

$webhook = file_get_contents('php://input');
$webhookData = json_decode($webhook, true);

// Extrai o ID do pagamento do webhook
$eventType = $webhookData['event'];

if(!($eventType && $eventType == "PAYMENT_RECEIVED")){
	http_response_code(200);
	exit;
}

$paymentStatus = $webhookData['payment']['status'];
$paymentReference = $webhookData['payment']['externalReference'];
$confirmDate = $webhookData['payment']['confirmedDate'];

# VERIFICAÇÃO DE QUAL AMBIENTE VAI SER TRABALHADO
# 20 = PDV
# 10 = USUARIO FINAL
if($paymentReference != "" && $paymentReference != null ){

	// if(!isset($infomacoesRecebidas->response->message->password) || !password_verify("9X!d7#AqB4z&K1wF", $infomacoesRecebidas->response->message->password)){
	// 	exit;
	// }
	if(substr($paymentReference, 0, 2) == "GM" || substr($paymentReference, 0, 2) == "PD"){
		require_once "./confirmaBoleto_asaas.php";
		exit();
	}
	else if(substr($paymentReference, 0, 2) == "20"){
		require_once "/www/includes/pdv/functions_vendaGames.php";
		require_once "/www/includes/pdv/functions.php";
		$tipoUsuario = "PDV";
	}else{
		require_once "/www/includes/gamer/functions_vendaGames.php";
		require_once "/www/includes/gamer/functions.php";
		require_once "/www/class/gamer/classIntegracao.php";
		require_once "/www/class/gamer/classPromocoes.php";
		require_once "/www/includes/gamer/inc_instrucoes.php";
        require_once "/www/includes/gamer/functions_pagto.php";
		$tipoUsuario = "USUARIO";
	}
}

class RecebePix {
	
	private $conexao;
	private $idConciliador;
	private $status;
	private $ambiente;
	
	public function __construct($ambiente){
		$this->conexao = ConnectionPDO::getConnection()->getLink();
	    $this->ambiente = $ambiente;
	}
	
	private function verificaPagamento($idVenda){
		
		$tableName = ($this->ambiente == "PDV")? "tb_dist_venda_games": "tb_venda_games";
		$selecaoVenda = "select idvenda,status,status_processed, vg_data_inclusao,tipo_deposito,vg_ultimo_status from tb_pag_compras inner join ".$tableName." on idvenda = vg_id where numcompra = '".$idVenda."';";
		$informacaoVenda = SQLexecuteQuery($selecaoVenda);
		$venda = pg_fetch_array($informacaoVenda);
		
		if(pg_num_rows($informacaoVenda) > 0){
			return $venda;
		}
		return false;
	}
	
	private function atulizaPagamento($idVenda, $data_venda, $tipo = "PDV"){
		
		if($tipo == "PDV"){
            //$data_venda = substr($data_venda, 0, 19);
			$atualizacaoVenda = "update tb_pag_compras set status_processed = 1, status = 3,datacompra = '".$data_venda."', dataconfirma = '".$data_venda."' where numcompra = '".$idVenda."';";
			$informacaoVenda = SQLexecuteQuery($atualizacaoVenda);
			if(pg_num_rows($informacaoVenda) > 0){
				return true;
			}
			return false;
        }else{
            $sqlVerificaData = "select datainicio from tb_pag_compras where numcompra = '".$idVenda."' and (datainicio > (now() -'1440 minutes'::interval));";
            $informacaoData = SQLexecuteQuery($sqlVerificaData);
			if(pg_num_rows($informacaoData) > 0){
                $dadosPagamento = pg_fetch_array($informacaoData);
				$dataInicio = "'".$dadosPagamento["datainicio"]."'";
			}else{
                $dataInicio = "CURRENT_TIMESTAMP";
            }
        
            $atualizacaoVenda = "update tb_pag_compras set status_processed = 0, status = 1, datainicio = ".$dataInicio." where numcompra = '".$idVenda."';";
			$informacaoVenda = SQLexecuteQuery($atualizacaoVenda);
			if(pg_num_rows($informacaoVenda) > 0){
				return true;
			}
			return false;
        }
		
	}
	
	private function atualizaVenda($idVenda){
		
		$tableName = ($this->ambiente == "PDV")? "tb_dist_venda_games": "tb_venda_games";
		$tableNameJoin = ($this->ambiente == "PDV")? "tb_dist_venda_games_modelo": "tb_venda_games_modelo";
		$selecaoVenda = "select * from ".$tableName." inner join tb_pag_compras on idvenda = vg_id left join ".$tableNameJoin." on vgm_vg_id = vg_id where vg_id = ". $idVenda;
		$informacaoVenda = SQLexecuteQuery($selecaoVenda);
		if(pg_num_rows($informacaoVenda) > 0){
			$venda = pg_fetch_array($informacaoVenda);
			$data_venda = date('Y-m-d H:i:s') . '.' . sprintf('%05d', round(microtime(true) * 1000) % 1000);
	        $valor = substr($venda["total"], 0 , -2).".".substr($venda["total"], -2);
			$atualizaVenda = "update ".$tableName." set vg_ultimo_status_obs ='',vg_usuario_obs = 'Pagamento Online PIX POR WEBHOOK em " . date('Y-m-d H:i:s') . "', vg_pagto_data_inclusao = '".$data_venda."',
			vg_ultimo_status = 5,vg_pagto_data = '".$data_venda."',vg_pagto_banco = '400',
			vg_concilia = 1,vg_data_concilia = '".$data_venda."',vg_pagto_valor_pago = ".$valor." where vg_id = ".$idVenda;
			$informacaoAtualizacao = SQLexecuteQuery($atualizaVenda);
			if(pg_affected_rows($informacaoAtualizacao) > 0){
				if($this->ambiente == "PDV"){
					$informacaoSaldo = "update dist_usuarios_games set ug_perfil_saldo = ug_perfil_saldo + ".$valor." where ug_id =". $venda["vg_ug_id"];
					$atualizacaoSaldo = SQLexecuteQuery($informacaoSaldo);
					if(pg_affected_rows($informacaoAtualizacao) > 0){
						return true;
					}else{
						return false;
					}
				}
			}else{
				return false;
			}
		}
		return false;
	}
	
	private function gravaLog($idVenda, $novoStatus, $antigoStatus, $statusFinalVenda){
		
		$confirmaConciliacao = ($novoStatus != $antigoStatus)? "CONCILIADO COM SUCESSO": "PEDIDO JÝ CONCILIADO";
		$file = fopen("/www/log/log_webhook.txt", "a+");
		fwrite($file, str_repeat("*", 50)."\n");
		fwrite($file, "DATA: ".date("d-m-Y H:i:s")."\n");
		fwrite($file, "ID VENDA: ". $idVenda."\n");
		fwrite($file, "NOVO STATUS PAGAMENTO: ". $novoStatus."\n");
		fwrite($file, "ANTIGO STATUS PAGAMENTO: ". $antigoStatus."\n");
		fwrite($file, "CODIGO PEDIDO RECEBIDO CDC: ". $this->idConciliador."\n");
		fwrite($file, "STATUS RECEBIDO CDC: ". $this->status."\n");
		fwrite($file, "SITUAÇÃO DA CONCILIAÇÃO: ". $confirmaConciliacao."\n");
        fwrite($file, "STATUS FINAL VENDA: ". $statusFinalVenda."\n");
        fwrite($file, "AMBIENTE VENDA: ". $this->ambiente."\n");
		fwrite($file, str_repeat("*", 50)."\n");
		fclose($file);
		
	}

	public function dadosPagador($idUsuario, $idpedido, $resposta_json)
    {

        $curl = curl_init();

		$token = getenv('ASAAS_ACCESS_TOKEN');

        curl_setopt_array($curl, array(
            CURLOPT_URL => getenv('ASAAS_API_URL') . "customers/$idUsuario",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'access_token: ' . $token,
            ),
        ));

        $response = curl_exec($curl);

        $data = json_decode($response, true);

        $cpf = $data['cpfCnpj'] ? preg_replace('/\D/', '', $data['cpfCnpj']) : 'N ret CpfCnpj';
        $name = $data['name'] ? $data['name'] : 'Nao retornou nome';

        $sql = "SELECT * FROM tb_pag_pix WHERE numcompra = '" . substr($idpedido, 2, 17) . "'; ";
        $rs_teste_existencia = SQLexecuteQuery($sql);
        if (pg_num_rows($rs_teste_existencia) == 0) {
            $sql = "INSERT INTO tb_pag_pix( 
                                                numcompra, 
                                                cpf_cnpj_pagador, 
                                                nome_pagador, 
                                                json_resposta)
                                    VALUES (
                                            '" . substr($idpedido, 2, 17) . "', 
                                            '" . $cpf . "',
                                            '" . $name . "',
                                            '" . $resposta_json . "');";
            SQLexecuteQuery($sql);
        }
    }
	
	public function conciliaPix($id, $status){
		
		$this->status = $status;
		$this->idConciliador = $id;
		
		$data_atual = date('Y-m-d H:i:s') . '.' . sprintf('%05d', round(microtime(true) * 1000) % 1000);

		if($this->ambiente == "PDV"){
			$venda = $this->verificaPagamento(substr($this->idConciliador, 2));
			if(($venda["status"] == "1" || $venda["status"] == "-1")){ 
			    $this->atulizaPagamento(substr($this->idConciliador, 2), $data_atual);
				$this->atualizaVenda($venda["idvenda"]);
				///conciliacaoAutomaticaPagtoOnlineExpressMoneyLH($venda["idvenda"]);
				$novoRetorno = $this->verificaPagamento(substr($this->idConciliador, 2));
				$conciliado = ($novoRetorno["status"] == '3')? true: false;
			}else{
				$novoRetorno["status"] = $venda["status"];
                $novoRetorno["vg_ultimo_status"] = $venda["vg_ultimo_status"];
				$conciliado = false;
			}
			$this->gravaLog($venda["idvenda"], $novoRetorno["status"], $venda["status"], $novoRetorno["vg_ultimo_status"]);
		}else{
			$venda = $this->verificaPagamento(substr($this->idConciliador, 2));
			if(($venda["status"] == "1" || $venda["status"] == "-1")){ 
			    $this->atulizaPagamento(substr($this->idConciliador, 2), $data_atual, "USUARIO");
				//$this->atualizaVenda($venda["idvenda"]);
                if($venda["tipo_deposito"] == 0){
					$idvenda = htmlspecialchars($venda["idvenda"], ENT_QUOTES, 'UTF-8');
                    conciliacaoAutomaticaPagtoPIXemGAMER(true, $idvenda);
                }else if($venda["tipo_deposito"] == 2){
                    conciliaAutomaticaMoneyDepositoSaldocomPIX(true, $venda["idvenda"]); 
                }
				$novoRetorno = $this->verificaPagamento(substr($this->idConciliador, 2));
				$conciliado = ($novoRetorno["status"] == '3')? true: false;
			}else{
				$novoRetorno["status"] = $venda["status"];
                $novoRetorno["vg_ultimo_status"] = $venda["vg_ultimo_status"];
				$conciliado = false;
			}	
			$this->gravaLog($venda["idvenda"], $novoRetorno["status"], $venda["status"], $novoRetorno["vg_ultimo_status"]);
		}
		
		if($conciliado){
			$mensagem = utf8_decode('<b>O pagamento foi conciliado com sucesso!</b><br> 
			    Data da conciliação: '.date("d-m-Y H:i:s").'<br>
				ID pagamento E-Prepag: '.substr($this->idConciliador, 2).'<br>
				Status final venda: '.$novoRetorno["vg_ultimo_status"].'<br>
				Ambiente Venda: '.$this->ambiente.'<br>
				ID de venda E-Prepag: '.$venda["idvenda"]);
				$retornoEmail = enviaEmail("monitoramento@e-prepag.com.br", "", "", "WEB HOOK(".$venda["idvenda"].")", $mensagem);
			if($retornoEmail){ //
			
			    $status = ($retornoEmail == true)? "OK":"NOK";
			    $file = fopen("/www/log/emailwebhook.txt", "a+");
				fwrite($file, str_repeat("*", 50)."\n");
				fwrite($file, "DATA: ".date("d-m-Y H:i:s")."\n");
				fwrite($file, "RETORNO DISPARO: ".$status."\n");
				fwrite($file, "VENDA: ".$venda["idvenda"]."\n");
				fwrite($file, str_repeat("*", 50)."\n");
				fclose($file);
				echo "e-mail enviado com sucesso";
			}else{
				$status = ($retornoEmail == true)? "OK":"NOK";
			    $file = fopen("/www/log/emailwebhook.txt", "a+");
				fwrite($file, str_repeat("*", 50)."\n");
				fwrite($file, "DATA: ".date("d-m-Y H:i:s")."\n");
				fwrite($file, "RETORNO DISPARO: ".$status."\n");
				fwrite($file, "VENDA: ".$venda["idvenda"]."\n");
				fwrite($file, str_repeat("*", 50)."\n");
				fclose($file);
				echo "erro e-mail";
			}
		}else{
			echo "Pagamento já conciliado";
		}
	}
	
}

$pix = new RecebePix($tipoUsuario);
$pix->dadosPagador($webhookData['payment']['customer'] , $paymentReference, $webhook);
$pix->conciliaPix($paymentReference, $paymentStatus);
http_response_code(200);

?>