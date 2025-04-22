<?php
	
	class UsuarioVip
	{
		private $ug_id = null;
		private $ug_vip_status = null;
		private $ug_data_inclusao = null;
		private $op_nome = null;
		
		public function getStatusVip($ug_id) {
			$sql = "select ug_vip_status from tb_gamers_vip where ug_id = {$ug_id};";
			$rs = SQLexecuteQuery($sql);
			$status = pg_fetch_array($rs);
			
			
			if ($status['ug_vip_status'] == 1) {
				$status = 1;
			} else {
				$status = 0;
			}
			
			return $status;
		}
		
		public function getDataInclusao($ug_id) {
			$sql = "select ug_data_inclusao from tb_gamers_vip where ug_id = {$ug_id};";
			$rs = SQLexecuteQuery($sql);
			$data_inclusao = pg_fetch_array($rs);
			
			return $data_inclusao['ug_data_inclusao'];
		}
		
		public function getNomeOperador($ug_id) {
			$sql = "select op_nome from tb_gamers_vip where ug_id = {$ug_id};";
			$rs = SQLexecuteQuery($sql);
			$nome_operador = pg_fetch_array($rs);
			
			return $nome_operador['op_nome'];
		}
		
		public function setGamerVip($ug_id, $op_id, $op_nome) {
			
			if (!empty($ug_id)) {
				
				$ug_id = $ug_id;
				$op_id = $op_id;
				$op_nome = $op_nome;
				
				$sqlPesquisa = "select * from usuarios_games where ug_id = {$ug_id}";
				$rsPesquisa = SQLexecuteQuery($sqlPesquisa);
				$dadosPesquisa = pg_fetch_array($rsPesquisa);

				$sqlVerificaCadastroVIP = "select ug_vip_status from tb_gamers_vip where ug_id = {$ug_id};";

				$rsPesquisaCadastroVIP = SQLexecuteQuery($sqlVerificaCadastroVIP);
				$dadosPesquisaCadastroVIP = pg_fetch_array($rsPesquisaCadastroVIP);
					
				if (!empty($dadosPesquisa) && $dadosPesquisaCadastroVIP['ug_vip_status'] == 1) {
					
					return 'O usuário já é VIP';
					
				} elseif (!empty($dadosPesquisa) && empty($dadosPesquisaCadastroVIP)) {
					
					$ug_vip_status = 1;
					$ug_data_inclusao = date('Y-m-d H:i:s');
					
					$sqlAdicao = "insert into tb_gamers_vip (ug_id, ug_vip_status, ug_data_inclusao, op_id, op_nome) values ($1, $2, $3, $4, $5)";
					$rsAdicao = pg_query_params($sqlAdicao, [$ug_id, $ug_vip_status, $ug_data_inclusao, $op_id, $op_nome]);
					$dadosAdicao = SQLexecuteQuery($rsAdicao);
					/*
					$sqlConsultaBloqueio = "select * from usuarios_games_pagamento_bloqueio_log where ugpbl_ug_id = {$ug_id};";
					$consultaBloqueio = SQLexecuteQuery($sqlConsultaBloqueio);
					$dadosConsultaBloqueio = pg_fetch_array($consultaBloqueio);
					
					if (!empty($dadosConsultaBloqueio) && $dadosConsultaBloqueio != null) {
						$sqlDeletaBloqueio = "delete from usuarios_games_pagamento_bloqueio_log where ugpbl_ug_id = {$ug_id};";
						$deletaBloqueio = SQLexecuteQuery($sqlDeletaBloqueio);
					}
					*/
					if ($dadosAdicao != false && $dadosAdicao->rowCount() > 0) {
						return 'Usuário VIP adicionado com sucesso';
					} else {
						return 'Erro ao adicionar o usuário à categoria VIP - #888';
					}
					
				} else {
					return 'Erro ao adicionar o usuário à categoria VIP - #999';
				}
				
			} else {
				return 'ID não foi informado - #1010';
			}
		}
	}