<?php
// Ponto Certo - Recarga de Celular
// inc_Simul.php - Simula as respostas da rede Ponto Certo

/*
, 'urlLogo' => 'http://www.redepontocerto.com.br/rpc/imagens/operadoras/telefonica_logo.jpg'
, 'urlLogo' => 'http://www.redepontocerto.com.br/rpc/imagens/operadoras/claro_logo.jpg'
, 'urlLogo' => 'http://www.redepontocerto.com.br/rpc/imagens/operadoras/nextel_logo.jpg'

*/

$operadoras_simul = array(
	array('codigoOperadora' => 132, 'nomeOperadora' => 'Nextel', 'codigoRede' => 2, 'urlLogo' => 'http://www.redepontocerto.com.br/rpc/imagens/operadoras/nextel_logo.jpg' ),
	array('codigoOperadora' => 147, 'nomeOperadora' => 'Oi', 'codigoRede' => 2, 'urlLogo' => 'http://www.redepontocerto.com.br/rpc/imagens/operadoras/Oi_LOGO.jpg' ),
	array('codigoOperadora' => 156, 'nomeOperadora' => 'L E Familia', 'codigoRede' => 2, 'urlLogo' => 'http://www.redepontocerto.com.br/rpc/imagens/operadoras/vivo_logo.jpg' ),
	array('codigoOperadora' => 155, 'nomeOperadora' => 'L Economia', 'codigoRede' => 2, 'urlLogo' => 'http://www.redepontocerto.com.br/rpc/imagens/operadoras/vivo_logo.jpg' ),
	array('codigoOperadora' => 146, 'nomeOperadora' => 'Claro', 'codigoRede' => 2, 'urlLogo' => 'http://www.redepontocerto.com.br/rpc/imagens/operadoras/claro_logo.jpg' ),
	array('codigoOperadora' => 145, 'nomeOperadora' => 'Vivo', 'codigoRede' => 2, 'urlLogo' => 'http://www.redepontocerto.com.br/rpc/imagens/operadoras/vivo_logo.jpg' ),
);


	function getRetorno_ConsultaOperadoras() {

		$operadoras = array();
		foreach($GLOBALS['operadoras_simul'] as $key => $val) {
			$operadoras[] = array(
					'codigoProduto' => rand(100, 400), 
					'codigoOperadora' => $val['codigoOperadora'], 
					'nomeOperadora' => "[".$val['nomeOperadora']."]", 
					'codigoRede' => $val['codigoRede'], 
					'urlLogo' => $val['urlLogo']
				);
		}
//			"ConsultaOperadoras" => array("sequencial", "descricaoStatus", "versaoOperadora", "operadoras", "statusTransacao", "codigoMensagem", "checksum"),

		$aret = array(
			'sequencial' => 'OSEQ_'.rand(1000, 9999),
			'descricaoStatus' => 'Status Descricao',
			'versaoOperadora' => 'v 2.3',
			'operadoras' => $operadoras,
			'statusTransacao' => '0',
			'codigoMensagem' => '3223',
			'checksum' => 'FD4537EDC8H',
			);
		return $aret;

	}

	function getRetorno_ConsultaValores() {

		$rc = $GLOBALS['rc'];
		$codigoOperadora = $rc->codigoOperadora;
		
		$valor_venda_0 = rand(1,10); $valor_bonus_0 = rand(1,10);

		$valores = array(); 
			$valores[] = array('valor' => $valor_venda_0, 'valorBonus' => $valor_bonus_0); 
				$valor_venda_0 += rand(2,10); $valor_bonus_0 += rand(2,10);
			$valores[] = array('valor' => $valor_venda_0, 'valorBonus' => $valor_bonus_0); 
				$valor_venda_0 += rand(2,10); $valor_bonus_0 += rand(2,10);
			$valores[] = array('valor' => $valor_venda_0, 'valorBonus' => $valor_bonus_0); 
				$valor_venda_0 += rand(2,10); $valor_bonus_0 += rand(2,10);
			$valores[] = array('valor' => $valor_venda_0, 'valorBonus' => $valor_bonus_0); 
				$valor_venda_0 += rand(2,10); $valor_bonus_0 += rand(2,10);
			$valores[] = array('valor' => $valor_venda_0, 'valorBonus' => $valor_bonus_0); 
				$valor_venda_0 += rand(2,10); $valor_bonus_0 += rand(2,10);
			$valores[] = array('valor' => $valor_venda_0, 'valorBonus' => $valor_bonus_0); 
				$valor_venda_0 += rand(2,10); $valor_bonus_0 += rand(2,10);

			$val_min = 100000; $val_max = -100000;
			foreach($valores as $key => $val) {
				if($val['valor']<$val_min) $val_min = $val['valor']; 
				if($val['valor']>$val_max) $val_max = $val['valor']; 
			}

//			"ConsultaValores" => array("codigoMensagem", "sequencial", "descricaoStatus", "tamanhoDV", "valoresFixos", "valorMinimo", "valorMaximo", "versaoFilial", "statusTransacao", "checksum"),

		$aret = array(
			'codigoMensagem' => 4321,
			'sequencial' => 'VSEQ_'.rand(1000, 9999),
			'descricaoStatus' => 'Status Descricao',
			'tamanhoDV' => '0',
			'valoresFixos' => $valores,

			'valorMinimo' => $val_min,
			'valorMaximo' => $val_max,
			'versaoFilial' => '0',

			'statusTransacao' => '0',
			'checksum' => 'FD4537EDC8H',

			);
		return $aret;
	}

	function getRetorno_SolicitacaoRecarga() {

//			"SolicitacaoRecarga" => array("codigoMensagem", "sequencial", "statusTransacao", "descricaoStatus", "recibo"),

		$aret = array(
			'codigoMensagem' => 4321,
			'sequencial' => 'RSEQ_'.rand(1000, 9999),
			'statusTransacao' => '0',
			'descricaoStatus' => 'Status Descricao',
			'recibo' => 'COMPROVANTE',

			);
		return $aret;
	}



	function getExtraFields_AtualizaOperadorasValores() {
		$sret = "<hr>";
		return $sret;
	}
	function getExtraFields_ConsultaOperadoras() {
		$sret = "<hr>";
		return $sret;
	}
	function getExtraFields_ConsultaValores($codigoOperadora, $codigoRede, $DDD) {
		if(!$codigoRede) $codigoRede = 2;
		if(!$DDD) $DDD = 11;

		$sret = "<hr>";
		$sret .= "codigoOperadora&nbsp;<select name='codigoOperadora'>\n";
		foreach($GLOBALS['operadoras_simul'] as $key => $val) {
			$sret .= "<option value='".$val['codigoOperadora']."'".(($val['codigoOperadora']==$codigoOperadora)?" selected":"").">".$val['codigoOperadora']." - ".$val['nomeOperadora']."</option>\n";
		}
		$sret .= "</select><br>\n";
		$sret .= "codigoRede&nbsp;<input type='text' name='codigoRede' value='$codigoRede'><br>\n";
//		$sret .= "DDD&nbsp;<input type='text' name='DDD' value='$DDD'><br>\n";
		$sret .= "DDD&nbsp;".get_select_ddds($DDD)."<br>\n";
		return $sret;
	}
	function getExtraFields_SolicitacaoRecarga($codigoOperadora, $codigoRede, $codigoProduto, $numeroCelular, $numeroCelularConf, $valor, $versaoFilial, $versaoOperadora) {
		
		if(!$codigoRede) $codigoRede = 2;
		if(!$codigoProduto) $codigoProduto = 321;
		if(!$numeroCelular) $numeroCelular = '98765432';
		if(!$numeroCelularConf) $numeroCelularConf = $numeroCelular;
		if(!$valor) $valor = 102;
		if(!$versaoFilial) $versaoFilial = '1.0';
		if(!$versaoOperadora) $versaoOperadora = '1.02';

		$sret = "<hr>";
		$sret .= "codigoOperadora&nbsp;<select name='codigoOperadora'>\n";
		foreach($GLOBALS['operadoras_simul'] as $key => $val) {
			$sret .= "<option value='".$val['codigoOperadora']."'".(($val['codigoOperadora']==$codigoOperadora)?" selected":"").">".$val['codigoOperadora']." - ".$val['nomeOperadora']."</option>\n";
		}
		$sret .= "</select><br>\n";
		$sret .= "codigoRede&nbsp;<input type='text' name='codigoRede' value='$codigoRede'><br>\n";

		$sret .= "codigoProduto&nbsp;<input type='text' name='codigoProduto' value='$codigoProduto'><br>\n";
		$sret .= "numeroCelular&nbsp;<input type='text' name='numeroCelular' value='$numeroCelular'><br>\n";
		$sret .= "numeroCelularConf&nbsp;<input type='text' name='numeroCelularConf' value='$numeroCelularConf'><br>\n";
		$sret .= "valor&nbsp;<input type='text' name='valor' value='$valor'><br>\n";
		$sret .= "versaoFilial&nbsp;<input type='text' name='versaoFilial' value='$versaoFilial'><br>\n";
		$sret .= "versaoOperadora&nbsp;<input type='text' name='versaoOperadora' value='$versaoOperadora'><br>\n";
		return $sret;
	}

?>