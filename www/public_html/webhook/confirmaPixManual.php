<?php

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

require_once "/www/includes/constantes.php";
require_once "/www/includes/constantesPagamento.php";
require_once "/www/includes/gamer/constantes.php";
require_once "/www/includes/main.php";
require_once "/www/includes/inc_Pagamentos.php";
require_once "/www/includes/functions.php";
// Se for gamer, precisa colocar 10 na frente. Se for PDV, precisa colocar 20 no id od pagamento
$teste = '{
		"http_status_code": 200,
		"http_status_message": "OK",
		"date": "2024-09-11 17:14:40",
		"response": {
			"message": {
				"status": "TRANSACAO_RECEBIDA",
				"id": "2020240910180901840"
			}
		}
	}';

# FUNÇÃO DE CONCILIAÇÃO PARA PDV ( conciliacaoAutomaticaPagtoOnlineExpressMoneyLH / conciliacaoAutomaticaPagtoPIXemPDV )
# FUNÇÃO DE CONCILIAÇÃO PARA USUARIO FINAL ( conciliacaoAutomaticaPagtoPIXemGAMER )
$dados = file_get_contents('php://input');
$infomacoesRecebidas = json_decode($teste); //$teste

       //$file = fopen("/www/log/log_webhook.txt", "a+");
	   //fwrite($file, str_repeat("*", 50)."\n");
	   //fwrite($file, "DATA: ".date("d-m-Y H:i:s")."\n");
	   //fwrite($file, "ID VENDA: ".$dados."\n");
	   //fwrite($file, str_repeat("*", 50)."\n");
	   //fclose($file);

# VERIFICAÇÃO DE QUAL AMBIENTE VAI SER TRABALHADO
# 20 = PDV
# 10 = USUARIO FINAL
if($infomacoesRecebidas != "" && $infomacoesRecebidas != null && isset($infomacoesRecebidas->response->message->id)){
	if(substr($infomacoesRecebidas->response->message->id, 0, 2) == "20"){
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
            $data_venda = substr($data_venda, 0, 19);
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
			$data_venda = substr($venda["vg_data_inclusao"], 0, 19);
	        $valor = substr($venda["total"], 0 , -2).".".substr($venda["total"], -2);
			$atualizaVenda = "update ".$tableName." set vg_ultimo_status_obs ='',vg_usuario_obs = '', vg_pagto_data_inclusao = '".$data_venda."',
			vg_ultimo_status = 5,vg_pagto_data = '".$data_venda.".097958',vg_pagto_banco = '400',vg_pagto_num_docto = 'PIXR_".date("YmdHis")."591',
			vg_concilia = 1,vg_data_concilia = '".$data_venda.".431227',vg_user_id_concilia = '0401121156014',vg_pagto_valor_pago = ".$valor." where vg_id = ".$idVenda;
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
	
	public function conciliaPix($informacoes){
		
		$this->status = $informacoes->response->message->status;
		$this->idConciliador = $informacoes->response->message->id;
		
		if($this->ambiente == "PDV"){
			$venda = $this->verificaPagamento(substr($this->idConciliador, 2));
			if(($venda["status"] == "1" || $venda["status"] == "-1")){ 
			    $this->atulizaPagamento(substr($this->idConciliador, 2), $venda["vg_data_inclusao"]);
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
			    $this->atulizaPagamento(substr($this->idConciliador, 2), $venda["vg_data_inclusao"], "USUARIO");
				//$this->atualizaVenda($venda["idvenda"]);
                if($venda["tipo_deposito"] == 0){
                    conciliacaoAutomaticaPagtoPIXemGAMER(true, $venda["idvenda"]);
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

# 20000000020221109121112256 | 10000000020221109120309450
$pix = new RecebePix($tipoUsuario);
$pix->conciliaPix($infomacoesRecebidas);
http_response_code(200);

?>