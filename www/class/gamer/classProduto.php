<?php
class Produto
{

	var $p_id;
	var $p_sNome;
	var $p_sDescricao;
	var $p_blAtivo;
	var $p_sNomeImagem;
	var $p_dDataInclusao;
	var $p_dDataAtualizacao;
	var $p_iOprCodigo;
	var $p_ogp_mostra_integracao;
	var $p_ogp_iof;
	var $op_nomeOperadora;
	var $ogp_pin_request;
	var $ogp_detalhes_utilizacao;
	var $ogp_termos_condicoes;
	var $ogp_valor_minimo;
	var $ogp_valor_maximo;

	public $ogp_idade_minima;

	function __construct(
		$p_id 				= null,
		$p_sNome 			= null,
		$p_sDescricao			= null,
		$p_blAtivo 			= null,
		$p_sNomeImagem 			= null,
		$p_dDataInclusao		= null,
		$p_iOprCodigo			= null,
		$p_ogp_mostra_integracao	= null,
		$p_ogp_iof			= null,
		$ogp_pin_request		= null,
		$ogp_detalhes_utilizacao	= null,
		$ogp_termos_condicoes		= null,
		$ogp_valor_minimo		= null,
		$ogp_valor_maximo		= null,
		$ogp_idade_minima               = null
	) {

		$this->setId($p_id);
		$this->setNome($p_sNome);
		$this->setDescricao($p_sDescricao);
		$this->setAtivo($p_blAtivo);
		$this->setNomeImagem($p_sNomeImagem);
		$this->setDataInclusao($p_dDataInclusao);
		$this->setOprCodigo($p_iOprCodigo);
		$this->setMostraIntegracao($p_ogp_mostra_integracao);
		$this->setIOF($p_ogp_iof);
		$this->setPinRequest($ogp_pin_request);
		$this->seDetalhesUtilizacao($ogp_detalhes_utilizacao);
		$this->setTermosCondicoes($ogp_termos_condicoes);
		$this->setValorMinimo($ogp_valor_minimo);
		$this->setValorMaximo($ogp_valor_maximo);
		$this->setIdadeMinima($ogp_idade_minima);
	} //end function Produto

	public function getValorMinimo()
	{
		return $this->ogp_valor_minimo;
	}

	public function setValorMinimo($ogp_valor_minimo)
	{
		$this->ogp_valor_minimo = $ogp_valor_minimo;
	}

	public function getValorMaximo()
	{
		return $this->ogp_valor_maximo;
	}

	public function getIdadeMinima()
	{
		return $this->ogp_idade_minima;
	}

	public function setIdadeMinima($ogp_idade_minima)
	{
		$this->ogp_idade_minima = ($ogp_idade_minima) ? $ogp_idade_minima : 0;
	}

	public function setValorMaximo($ogp_valor_maximo)
	{
		$this->ogp_valor_maximo = $ogp_valor_maximo;
	}

	public function getPinRequest()
	{
		return $this->ogp_pin_request;
	}

	public function setPinRequest($ogp_pin_request)
	{
		$this->ogp_pin_request = $ogp_pin_request;
	}

	public function getDetalhesUtilizacao()
	{
		return $this->ogp_detalhes_utilizacao;
	}

	public function seDetalhesUtilizacao($ogp_detalhes_utilizacao)
	{
		$this->ogp_detalhes_utilizacao = $ogp_detalhes_utilizacao;
	}

	public function getTermosCondicoes()
	{
		return $this->ogp_termos_condicoes;
	}

	public function setTermosCondicoes($ogp_termos_condicoes)
	{
		$this->ogp_termos_condicoes = $ogp_termos_condicoes;
	}

	function setNomeOperadora($nomeOperadora = "")
	{
		$this->op_nomeOperadora = $nomeOperadora;
	}

	function getNomeOperadora()
	{
		return $this->op_nomeOperadora;
	}

	function getId()
	{
		return $this->p_id;
	}
	function setId($p_id)
	{
		$this->p_id = $p_id;
	}

	function getNome()
	{
		return $this->p_sNome;
	}
	function setNome($p_sNome)
	{
		$this->p_sNome = $p_sNome;
	}

	function getDescricao()
	{
		return str_replace("target=\"produto\"", "", $this->p_sDescricao);
		//    	return $this->p_sDescricao;
	}
	function setDescricao($p_sDescricao)
	{
		$this->p_sDescricao = $p_sDescricao;
	}

	function getAtivo()
	{
		return $this->p_blAtivo;
	}
	function setAtivo($p_blAtivo)
	{
		if ($p_blAtivo == 1 || $p_blAtivo == "1" || $p_blAtivo === "true") $p_blAtivo = "1";
		else $p_blAtivo = "0";
		$this->p_blAtivo = $p_blAtivo;
	}

	function getMostraIntegracao()
	{
		return $this->p_ogp_mostra_integracao;
	}
	function setMostraIntegracao($p_ogp_mostra_integracao)
	{
		if ($p_ogp_mostra_integracao == 1 || $p_ogp_mostra_integracao == "1" || $p_ogp_mostra_integracao === "true") $p_ogp_mostra_integracao = "1";
		else $p_ogp_mostra_integracao = "0";
		$this->p_ogp_mostra_integracao = $p_ogp_mostra_integracao;
	}

	function getNomeImagem()
	{
		return $this->p_sNomeImagem;
	}
	function setNomeImagem($p_sNomeImagem)
	{
		$this->p_sNomeImagem = $p_sNomeImagem;
	}

	function getDataInclusao()
	{
		return $this->p_dDataInclusao;
	}
	function setDataInclusao($p_dDataInclusao)
	{
		$this->p_dDataInclusao = $p_dDataInclusao;
	}

	function getDataAtualizacao()
	{
		return $this->p_dDataAtualizacao;
	}
	function setDataAtualizacao($p_dDataAtualizacao)
	{
		$this->p_dDataAtualizacao = $p_dDataAtualizacao;
	}

	function getOprCodigo()
	{
		return $this->p_iOprCodigo;
	}
	function setOprCodigo($p_iOprCodigo)
	{
		$this->p_iOprCodigo = $p_iOprCodigo;
	}

	function getIOF()
	{
		return $this->p_ogp_iof;
	}
	function setIOF($p_ogp_iof)
	{
		$this->p_ogp_iof = $p_ogp_iof;
	}


	private function addParam(&$params, $value)
	{
		$params[] = $value;
		return '$' . count($params);
	}

	private function addIdList(&$params, $list)
	{
		$ids = array_filter(array_map('trim', explode(',', (string) $list)), 'ctype_digit');
		$placeholders = array();
		foreach ($ids as $id) {
			$placeholders[] = $this->addParam($params, (int) $id);
		}
		return $placeholders;
	}

	private function orderByProduto($orderBy)
	{
		if (is_null($orderBy) || trim((string) $orderBy) == '') return null;
		$permitidos = array('ogp_id', 'ogp_nome', 'ogp_descricao', 'ogp_ativo', 'ogp_nome_imagem', 'ogp_data_inclusao', 'ogp_opr_codigo', 'ogp_data_atualizacao', 'ogp_mostra_integracao', 'ogp_ordem', 'opr_nome_loja');
		$partes = array();
		foreach (explode(',', $orderBy) as $parte) {
			$tokens = preg_split('/\s+/', trim($parte));
			$coluna = strtolower($tokens[0] ?? '');
			$direcao = strtoupper($tokens[1] ?? 'ASC');
			if (!in_array($coluna, $permitidos) || !in_array($direcao, array('ASC', 'DESC'))) return null;
			$partes[] = $coluna . ' ' . $direcao;
		}
		return count($partes) ? implode(', ', $partes) : null;
	}

	function reordenar($cont)
	{
		if (is_null($cont)) {
			//Ativos
			$sql = "select ogp_id from tb_operadora_games_produto where ogp_ativo = 1 order by ogp_nome ASC";
			$sql_ordenar = SQLexecuteQuery($sql);
			$cont = 0;

			if ((($sql_ordenar) ? pg_num_rows($sql_ordenar) : 0) != 0)
				while ($codigo = pg_fetch_array($sql_ordenar)) {
					SQLexecuteQueryParams("update tb_operadora_games_produto set ogp_ordem = $1 where ogp_id = $2", [$cont, $codigo["ogp_id"]]);
					$cont++;
				}
		}

		//Inativos
		$sql = "select ogp_id from tb_operadora_games_produto where ogp_ativo <> 1 order by ogp_nome ASC";
		//echo "sql: $sql<br>";
		$sql_ordenar_inat = SQLexecuteQuery($sql);
		$cont = is_null($cont) ? pg_num_rows($sql_ordenar) : $cont;

		if ((($sql_ordenar_inat) ? pg_num_rows($sql_ordenar_inat) : 0) != 0)
			while ($codigo = pg_fetch_array($sql_ordenar_inat)) {
				SQLexecuteQueryParams("update tb_operadora_games_produto set ogp_ordem = $1 where ogp_id = $2", [$cont, $codigo["ogp_id"]]);
				$cont++;
			}
	}


	function inserir(&$objProduto)
	{

		$ret = $this->validarCampos($objProduto);

		if ($ret == "") {
			if ($objProduto->getAtivo() != 1)
				$sql_ordem = SQLexecuteQuery("select count(*) as total from tb_operadora_games_produto");
			else
				$sql_ordem = SQLexecuteQuery("select count(ogp_id) as total from tb_operadora_games_produto where ogp_ativo = 1");
			$total_ordem = pg_fetch_result($sql_ordem, 0, 0);

			$sql = "insert into tb_operadora_games_produto(ogp_nome, ogp_descricao, ogp_ativo, ogp_nome_imagem, ogp_data_inclusao, ogp_opr_codigo, ogp_mostra_integracao, ogp_ordem, ogp_iof, ogp_pin_request, ogp_detalhes_utilizacao, ogp_termos_condicoes, ogp_valor_minimo, ogp_valor_maximo, ogp_idade_minima) values ($1, $2, $3, $4, CURRENT_TIMESTAMP, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14);";
			$params = array($objProduto->getNome(), $objProduto->getDescricao(), $objProduto->getAtivo(), $objProduto->getNomeImagem(), $objProduto->getOprCodigo(), $objProduto->getMostraIntegracao(), $total_ordem, $objProduto->getIOF(), $objProduto->getPinRequest(), $objProduto->getDetalhesUtilizacao(), $objProduto->getTermosCondicoes(), $objProduto->getValorMinimo(), $objProduto->getValorMaximo(), $objProduto->getIdadeMinima());
			$ret = SQLexecuteQueryParams($sql, $params);
			if (!$ret) $ret = "Erro ao inserir produto." . PHP_EOL;
			else {
				$ret = "";
				$rs_id = SQLexecuteQuery("select currval('operadora_games_produto_id_seq') as last_id");
				if ($rs_id && pg_num_rows($rs_id) > 0) {
					$rs_id_row = pg_fetch_array($rs_id);
					$objProduto->setId($rs_id_row['last_id']);
				}
				if ($objProduto->getAtivo() == 1) $objProduto->reordenar($total_ordem + 1);
			}
		}

		return $ret;
	}

	function atualizar($objProduto)
	{

		$ret = Produto::validarCampos($objProduto);

		if ($ret == "") {
			$params = array();
			$sets = array();
			if (!is_null($objProduto->getNome())) $sets[] = "ogp_nome = " . $this->addParam($params, $objProduto->getNome());
			if (!is_null($objProduto->getDescricao())) $sets[] = "ogp_descricao = " . $this->addParam($params, $objProduto->getDescricao());
			if (!is_null($objProduto->getAtivo())) $sets[] = "ogp_ativo = " . $this->addParam($params, $objProduto->getAtivo());
			if (!is_null($objProduto->getNomeImagem())) $sets[] = "ogp_nome_imagem = " . $this->addParam($params, $objProduto->getNomeImagem());
			if (!is_null($objProduto->getOprCodigo())) $sets[] = "ogp_opr_codigo = " . $this->addParam($params, $objProduto->getOprCodigo());
			if (!is_null($objProduto->getMostraIntegracao())) $sets[] = "ogp_mostra_integracao = " . $this->addParam($params, $objProduto->getMostraIntegracao());
			if (!is_null($objProduto->getIOF())) $sets[] = "ogp_iof = " . $this->addParam($params, $objProduto->getIOF());
			if (!is_null($objProduto->getPinRequest())) $sets[] = "ogp_pin_request = " . $this->addParam($params, $objProduto->getPinRequest());
			if (!is_null($objProduto->getDetalhesUtilizacao())) $sets[] = "ogp_detalhes_utilizacao = " . $this->addParam($params, $objProduto->getDetalhesUtilizacao());
			if (!is_null($objProduto->getTermosCondicoes())) $sets[] = "ogp_termos_condicoes = " . $this->addParam($params, $objProduto->getTermosCondicoes());
			$sets[] = "ogp_valor_minimo = " . $this->addParam($params, $objProduto->getValorMinimo());
			$sets[] = "ogp_valor_maximo = " . $this->addParam($params, $objProduto->getValorMaximo());
			if (!is_null($objProduto->getIdadeMinima())) $sets[] = "ogp_idade_minima = " . $this->addParam($params, $objProduto->getIdadeMinima());
			$sets[] = "ogp_data_atualizacao = CURRENT_TIMESTAMP";
			$sql = "update tb_operadora_games_produto set " . implode(", ", $sets);
			$sql .= " where ogp_id = " . $this->addParam($params, $objProduto->getId());
			$ret = SQLexecuteQueryParams($sql, $params);
			if (!$ret) $ret = "Erro ao atualizar produto." . PHP_EOL;
			else $ret = "";
		}

		return $ret;
	}

	function validarCampos($objProduto)
	{

		$ret = "";

		//Nome
		$nome = $objProduto->getNome();
		if (is_null($nome) || $nome == "") 	$ret .= "O Nome deve ser preenchido." . PHP_EOL;
		elseif (strlen($nome) > 100) 		$ret .= "O nome deve ter até 100 caracteres." . PHP_EOL;

		//Descricao
		$descricao = $objProduto->getDescricao();
		if (is_null($descricao) || $descricao == "") $ret .= "A Descrição deve ser preenchida." . PHP_EOL;
		//elseif(strlen($descricao) > 1024) 			$ret .= "A Descrição deve ter até 1024 caracteres.".PHP_EOL;

		//NomeImagem
		$nomeImagem = $objProduto->getNomeImagem();
		if (!is_null($nomeImagem)) {
			if (strlen($nomeImagem) > 100) $ret .= "O Nome da Imagem deve ter até 100 caracteres." . PHP_EOL;
		}

		//ativo
		$ativo = $objProduto->getAtivo();
		if (is_null($ativo) || $ativo == "") $ret .= "O status deve ser selecionado." . PHP_EOL;
		else if (!is_numeric($ativo)) $ret .= "O status deve ser númerico." . PHP_EOL;

		//opr_codigo
		$opr_codigo = $objProduto->getOprCodigo();
		if (is_null($opr_codigo) || $opr_codigo == "") $ret .= "A Operadora deve ser selecionada." . PHP_EOL;
		else if (!is_numeric($opr_codigo)) $ret .= "O código da Operadora deve ser númerico." . PHP_EOL;

		//Valores mínimo e máximo
		$valor_minimo = $objProduto->getValorMinimo();
		$valor_maximo = $objProduto->getValorMaximo();
		if (!is_null($valor_minimo) && !is_null($valor_maximo)) {
			if ($valor_minimo > $valor_maximo) $ret .= "O valor mínimo não pode ser maior que o valor máximo" . PHP_EOL;
			if ($valor_minimo == 0 || $valor_maximo == 0) $ret .= "Os valores mínimo e máximo não podem ser iguais a zero" . PHP_EOL;
		}
		//Caso um valor esteja preenchido e o outro não, deve-se retornar um erro
		if ((is_null($valor_minimo) && !is_null($valor_maximo)) || (!is_null($valor_minimo) && is_null($valor_maximo))) {
			$ret .= "Os dois valores devem ser preenchidos, ou desabilite a opção de valor variável" . PHP_EOL;
		}

		return $ret;
	}

	function obter($filtro, $orderBy, &$rs)
	{
		$ret = "";
		$params = array();
		$filtro = is_array($filtro) ? array_map("strtoupper", $filtro) : array();
		$sql = "select * from tb_operadora_games_produto ogp " . PHP_EOL;

		if (!empty($filtro)) {
			if (!is_null($filtro['opr'] ?? null)) $sql .= " inner join operadoras ope on ope.opr_codigo = ogp.ogp_opr_codigo " . PHP_EOL;
			if (!is_null($filtro['ogp_data_inclusaoMin'] ?? null) && !is_null($filtro['ogp_data_inclusaoMax'] ?? null)) {
				$filtro['ogp_data_inclusaoMin'] = formata_data_ts($filtro['ogp_data_inclusaoMin'] . " 00:00:00", 1, true, true);
				$filtro['ogp_data_inclusaoMax'] = formata_data_ts($filtro['ogp_data_inclusaoMax'] . " 23:59:59", 1, true, true);
			}
			$sql .= " where 1=1 " . PHP_EOL;
			if (!is_null($filtro['opr_status'] ?? null)) $sql .= " and ope.opr_status = " . $this->addParam($params, $filtro['opr_status']);
			if (!is_null($filtro['ogp_id'] ?? null)) $sql .= " and ogp.ogp_id = " . $this->addParam($params, $filtro['ogp_id']) . PHP_EOL;
			if (!is_null($filtro['ogp_nome'] ?? null)) $sql .= " and upper(ogp.ogp_nome) = " . $this->addParam($params, $filtro['ogp_nome']) . PHP_EOL;
			if (!is_null($filtro['ogp_nomeLike'] ?? null)) $sql .= " and upper(ogp.ogp_nome) like " . $this->addParam($params, '%' . $filtro['ogp_nomeLike'] . '%') . PHP_EOL;
			if (!is_null($filtro['ogp_descricao'] ?? null)) $sql .= " and upper(ogp.ogp_descricao) = " . $this->addParam($params, $filtro['ogp_descricao']) . PHP_EOL;
			if (!is_null($filtro['ogp_descricaoLike'] ?? null)) $sql .= " and upper(ogp.ogp_descricao) like " . $this->addParam($params, '%' . $filtro['ogp_descricaoLike'] . '%') . PHP_EOL;
			if ($filtro['ogp_mostra_integracao_com_loja'] ?? null) {
				$sql .= " and ((ogp.ogp_ativo = 1) or (ogp_mostra_integracao = " . $this->addParam($params, $filtro['ogp_mostra_integracao_com_loja']) . " and ogp.ogp_ativo = 0)) " . PHP_EOL;
			} else {
				if (!is_null($filtro['ogp_ativo'] ?? null)) $sql .= " and ogp.ogp_ativo = " . $this->addParam($params, $filtro['ogp_ativo']) . PHP_EOL;
				if (!is_null($filtro['ogp_mostra_integracao'] ?? null)) $sql .= " and ogp.ogp_mostra_integracao = " . $this->addParam($params, $filtro['ogp_mostra_integracao']) . PHP_EOL;
			}
			if (!is_null($filtro['ogp_nome_imagem'] ?? null)) $sql .= " and upper(ogp.ogp_nome_imagem) = " . $this->addParam($params, $filtro['ogp_nome_imagem']) . PHP_EOL;
			if (!is_null($filtro['ogp_nome_imagemLike'] ?? null)) $sql .= " and upper(ogp.ogp_nome_imagem) like " . $this->addParam($params, '%' . $filtro['ogp_nome_imagemLike'] . '%') . PHP_EOL;
			if (!is_null($filtro['ogp_data_inclusaoMin'] ?? null) && !is_null($filtro['ogp_data_inclusaoMax'] ?? null)) $sql .= " and ogp.ogp_data_inclusao between " . $this->addParam($params, $filtro['ogp_data_inclusaoMin']) . " and " . $this->addParam($params, $filtro['ogp_data_inclusaoMax']) . PHP_EOL;
			if (!is_null($filtro['ogp_opr_codigo'] ?? null)) $sql .= " and ogp.ogp_opr_codigo = " . $this->addParam($params, $filtro['ogp_opr_codigo']) . PHP_EOL;
			if (!is_null($filtro['show_treinamento'] ?? null) && $filtro['show_treinamento'] == 1) $sql .= "or ogp.ogp_id = 63 " . PHP_EOL;
			if (!is_null($filtro['ogp_id_list'] ?? null)) {
				$ids = $this->addIdList($params, $filtro['ogp_id_list']);
				$sql .= count($ids) ? " and ( ogp.ogp_id IN (" . implode(', ', $ids) . ") ) " . PHP_EOL : " and 1=0 " . PHP_EOL;
				$sql .= " and not (ogp.ogp_id = 63) " . PHP_EOL;
			}
		}
		$order = $this->orderByProduto($orderBy);
		$sql .= $order ? " order by " . $order : " order by ogp_ordem ASC " . PHP_EOL;
		$rs = count($params) ? SQLexecuteQueryParams($sql, $params) : SQLexecuteQuery($sql);
		if (!$rs) $ret = "Erro ao obter produto(s)." . PHP_EOL;
		return $ret;
	}

	function obterMelhorado($filtro, $orderBy, &$rs)
	{
		$ret = "";
		$params = array();
		$filtro = is_array($filtro) ? array_map("strtoupper", $filtro) : array();
		$sql = "select ogp_id, ogp_nome,ogp_descricao,ogp_ativo,ogp_nome_imagem,ogp_data_inclusao,ogp_opr_codigo,ogp_data_atualizacao, ogp_mostra_integracao, ogp_iof, ogp_pin_request, ogp_detalhes_utilizacao, ogp_termos_condicoes, ogp_valor_minimo, ogp_valor_maximo, ogp_idade_minima";
		if (!is_null($filtro['opr'] ?? null)) $sql .= ", opr_nome_loja";
		$sql .= " from tb_operadora_games_produto ogp ";
		if (!empty($filtro)) {
			if (!is_null($filtro['opr'] ?? null)) $sql .= " inner join operadoras ope on ope.opr_codigo = ogp.ogp_opr_codigo";
			if (!is_null($filtro['ogp_data_inclusaoMin'] ?? null) && !is_null($filtro['ogp_data_inclusaoMax'] ?? null)) {
				$filtro['ogp_data_inclusaoMin'] = formata_data_ts($filtro['ogp_data_inclusaoMin'] . " 00:00:00", 2, true, true);
				$filtro['ogp_data_inclusaoMax'] = formata_data_ts($filtro['ogp_data_inclusaoMax'] . " 23:59:59", 2, true, true);
			}
			$sql .= " where 1=1";
			if (!is_null($filtro['opr_status'] ?? null)) $sql .= " and ope.opr_status = " . $this->addParam($params, $filtro['opr_status']) . " ";
			if (!is_null($filtro['ogp_id'] ?? null)) $sql .= " and ogp.ogp_id = " . $this->addParam($params, $filtro['ogp_id']) . " ";
			if (!is_null($filtro['ogp_id_list'] ?? null)) {
				$ids = $this->addIdList($params, $filtro['ogp_id_list']);
				$sql .= count($ids) ? " and (ogp.ogp_id in (" . implode(', ', $ids) . ") ) and not (ogp.ogp_id = 63) " : " and 1=0 ";
			}
			if (!is_null($filtro['ogp_nome'] ?? null)) $sql .= " and upper(ogp.ogp_nome) = " . $this->addParam($params, $filtro['ogp_nome']) . " ";
			if (!is_null($filtro['ogp_nomeLike'] ?? null)) $sql .= " and upper(ogp.ogp_nome) like " . $this->addParam($params, '%' . $filtro['ogp_nomeLike'] . '%') . " ";
			if (!is_null($filtro['ogp_descricao'] ?? null)) $sql .= " and upper(ogp.ogp_descricao) = " . $this->addParam($params, $filtro['ogp_descricao']) . " ";
			if (!is_null($filtro['ogp_descricaoLike'] ?? null)) $sql .= " and upper(ogp.ogp_descricao) like " . $this->addParam($params, '%' . $filtro['ogp_descricaoLike'] . '%') . " ";
			if ($filtro['ogp_mostra_integracao_com_loja'] ?? null) {
				$sql .= " and ((ogp.ogp_ativo = 1) or (ogp_mostra_integracao = " . $this->addParam($params, $filtro['ogp_mostra_integracao_com_loja']) . " and ogp.ogp_ativo = 0)) " . PHP_EOL;
			} else {
				if (!is_null($filtro['ogp_ativo'] ?? null)) $sql .= " and ogp.ogp_ativo = " . $this->addParam($params, $filtro['ogp_ativo']) . " ";
				if (!is_null($filtro['ogp_mostra_integracao'] ?? null)) $sql .= " and ogp.ogp_mostra_integracao = " . $this->addParam($params, $filtro['ogp_mostra_integracao']) . " ";
			}
			if (!is_null($filtro['ogp_nome_imagem'] ?? null)) $sql .= " and upper(ogp.ogp_nome_imagem) = " . $this->addParam($params, $filtro['ogp_nome_imagem']) . " ";
			if (!is_null($filtro['ogp_nome_imagemLike'] ?? null)) $sql .= " and upper(ogp.ogp_nome_imagem) like " . $this->addParam($params, '%' . $filtro['ogp_nome_imagemLike'] . '%') . " ";
			if (!is_null($filtro['ogp_data_inclusaoMin'] ?? null)) $sql .= " and ogp.ogp_data_inclusao between " . $this->addParam($params, $filtro['ogp_data_inclusaoMin']) . " and " . $this->addParam($params, $filtro['ogp_data_inclusaoMax']) . " ";
			if (!is_null($filtro['ogp_opr_codigo'] ?? null)) $sql .= " and ogp.ogp_opr_codigo = " . $this->addParam($params, $filtro['ogp_opr_codigo']) . " ";
			if (!is_null($filtro['ogp_codigo_negado'] ?? null)) $sql .= " and ogp.ogp_id <> " . $this->addParam($params, $filtro['ogp_codigo_negado']) . " ";
			if (!is_null($filtro['show_treinamento'] ?? null) && $filtro['show_treinamento'] == 1) $sql .= "or ogp.ogp_id = 63 " . PHP_EOL;
			if (($filtro['ogp_pin_request'] ?? 0) != 0) $sql .= " and ogp.ogp_pin_request = " . $this->addParam($params, $filtro['ogp_pin_request']) . " ";
		}
		$order = $this->orderByProduto($orderBy);
		$sql .= $order ? " order by " . $order : " order by ogp_ordem ASC";
		$rs = count($params) ? SQLexecuteQueryParams($sql, $params) : SQLexecuteQuery($sql);
		if (!$rs) $ret = "Erro ao obter produto(s)." . PHP_EOL;
		return $ret;
	}

	function buscaIOF($ogpm_id)
	{
		$aux_return = false;
		$sql = "
                select ogp_iof 
                from tb_operadora_games_produto
                    inner join tb_operadora_games_produto_modelo ON (ogp_id = ogpm_ogp_id)
                where ogpm_id = $1;
                ";
		//echo $sql."<br>";
		$rs = SQLexecuteQueryParams($sql, [$ogpm_id]);
		if (!$rs) return $aux_return;
		$rs_row = pg_fetch_array($rs);
		$aux_return = $rs_row['ogp_iof'];
		return $aux_return;
	} //end function buscaIOF

} // end class Produto
