<?php

class ProdutoModelo
{

	var $pm_id;
	var $pm_p_id;
	var $pm_sNome;
	var $pm_sDescricao;
	var $pm_fValor;
	var $pm_fPercDesconto;
	var $pm_blAtivo;
	var $pm_sNomeImagem;
	var $pm_dDataInclusao;
	var $pm_iPinValor;
	var $nomeProduto;
	var $codOperador;
	var $ogpm_pin_resquest_id;
	var $ogpm_pin_valor_markup;


	function __construct(
		$pm_id 				= null,
		$pm_p_id 			= null,
		$pm_sNome 			= null,
		$pm_sDescricao 		= null,
		$pm_fValor 			= null,
		$pm_fPercDesconto	= null,
		$pm_blAtivo 		= null,
		$pm_sNomeImagem 	= null,
		$pm_dDataInclusao 	= null,
		$pm_iPinValor 		= null,
		$nomeProduto            = null,
		$codOperador            = null,
		$ogpm_pin_resquest_id	= null,
		$ogpm_pin_valor_markup	= null
	) {
		$this->ProdutoModelo(
			$pm_id,
			$pm_p_id,
			$pm_sNome,
			$pm_sDescricao,
			$pm_fValor,
			$pm_fPercDesconto,
			$pm_blAtivo,
			$pm_sNomeImagem,
			$pm_dDataInclusao,
			$pm_iPinValor,
			$nomeProduto,
			$codOperador,
			$ogpm_pin_resquest_id,
			$ogpm_pin_valor_markup
		);
	}


	function ProdutoModelo(
		$pm_id 				= null,
		$pm_p_id 			= null,
		$pm_sNome 			= null,
		$pm_sDescricao 		= null,
		$pm_fValor 			= null,
		$pm_fPercDesconto	= null,
		$pm_blAtivo 		= null,
		$pm_sNomeImagem 	= null,
		$pm_dDataInclusao 	= null,
		$pm_iPinValor 		= null,
		$nomeProduto            = null,
		$codOperador            = null,
		$ogpm_pin_resquest_id	= null,
		$ogpm_pin_valor_markup	= null
	) {

		$this->setId($pm_id);
		$this->setProdutoId($pm_p_id);
		$this->setNome($pm_sNome);
		$this->setDescricao($pm_sDescricao);
		$this->setValor($pm_fValor);
		$this->setPercDesconto($pm_fPercDesconto);
		$this->setAtivo($pm_blAtivo);
		$this->setNomeImagem($pm_sNomeImagem);
		$this->setDataInclusao($pm_dDataInclusao);
		$this->setPinValor($pm_iPinValor);
		$this->setNomeProduto($nomeProduto);
		$this->setCodOperador($codOperador);
		$this->setPinRequestId($ogpm_pin_resquest_id);
		$this->setValorMarkup($ogpm_pin_valor_markup);
	}

	public function getPinRequestId()
	{
		return $this->ogpm_pin_resquest_id;
	}

	public function setPinRequestId($ogpm_pin_resquest_id)
	{
		$this->ogpm_pin_resquest_id = $ogpm_pin_resquest_id;
	}

	public function getCodOperador()
	{
		return $this->codOperador;
	}

	public function setCodOperador($codOperador)
	{
		$this->codOperador = $codOperador;
		return $this;
	}

	public function getNomeProduto()
	{
		return $this->nomeProduto;
	}

	public function setNomeProduto($nomeProduto)
	{
		$this->nomeProduto = $nomeProduto;
		return $this;
	}

	function getId()
	{
		return $this->pm_id;
	}
	function setId($pm_id)
	{
		$this->pm_id = $pm_id;
	}

	function getProdutoId()
	{
		return $this->pm_p_id;
	}
	function setProdutoId($pm_p_id)
	{
		$this->pm_p_id = $pm_p_id;
	}

	function getNome()
	{
		return $this->pm_sNome;
	}
	function setNome($pm_sNome)
	{
		$this->pm_sNome = $pm_sNome;
	}

	function getDescricao()
	{
		return $this->pm_sDescricao;
	}
	function setDescricao($pm_sDescricao)
	{
		$this->pm_sDescricao = $pm_sDescricao;
	}

	function getValor()
	{
		return $this->pm_fValor;
	}
	function setValor($pm_fValor)
	{
		$this->pm_fValor = $pm_fValor;
	}

	function getPercDesconto()
	{
		return $this->pm_fPercDesconto;
	}
	function setPercDesconto($pm_fPercDesconto)
	{
		$this->pm_fPercDesconto = $pm_fPercDesconto;
	}

	function getAtivo()
	{
		return $this->pm_blAtivo;
	}
	function setAtivo($pm_blAtivo)
	{
		if ($pm_blAtivo == 1 || $pm_blAtivo == "1" || $pm_blAtivo === "true") $pm_blAtivo = "1";
		else $pm_blAtivo = "0";
		$this->pm_blAtivo = $pm_blAtivo;
	}

	function getNomeImagem()
	{
		return $this->pm_sNomeImagem;
	}
	function setNomeImagem($pm_sNomeImagem)
	{
		$this->pm_sNomeImagem = $pm_sNomeImagem;
	}

	function getDataInclusao()
	{
		return $this->pm_dDataInclusao;
	}
	function setDataInclusao($pm_dDataInclusao)
	{
		$this->pm_dDataInclusao = $pm_dDataInclusao;
	}

	function getPinValor()
	{
		return $this->pm_iPinValor;
	}
	function setPinValor($pm_iPinValor)
	{
		$this->pm_iPinValor = $pm_iPinValor;
	}

	function getValorMarkup()
	{
		return $this->ogpm_pin_valor_markup;
	}
	function setValorMarkup($ogpm_pin_valor_markup)
	{
		$this->ogpm_pin_valor_markup = $ogpm_pin_valor_markup;
	}


	private function addParam(&$params, $value)
	{
		$params[] = $value;
		return '$' . count($params);
	}

	private function orderByModelo($orderBy)
	{
		if (is_null($orderBy) || trim((string) $orderBy) == '') return null;
		$permitidos = array(
			'ogpm_id' => 'ogpm.ogpm_id', 'ogpm.ogpm_id' => 'ogpm.ogpm_id',
			'ogpm_ogp_id' => 'ogpm.ogpm_ogp_id', 'ogpm.ogpm_ogp_id' => 'ogpm.ogpm_ogp_id',
			'ogpm_nome' => 'ogpm.ogpm_nome', 'ogpm.ogpm_nome' => 'ogpm.ogpm_nome',
			'ogpm_descricao' => 'ogpm.ogpm_descricao', 'ogpm.ogpm_descricao' => 'ogpm.ogpm_descricao',
			'ogpm_valor' => 'ogpm.ogpm_valor', 'ogpm.ogpm_valor' => 'ogpm.ogpm_valor',
			'ogpm_perc_desconto' => 'ogpm.ogpm_perc_desconto', 'ogpm.ogpm_perc_desconto' => 'ogpm.ogpm_perc_desconto',
			'ogpm_ativo' => 'ogpm.ogpm_ativo', 'ogpm.ogpm_ativo' => 'ogpm.ogpm_ativo',
			'ogpm_nome_imagem' => 'ogpm.ogpm_nome_imagem', 'ogpm.ogpm_nome_imagem' => 'ogpm.ogpm_nome_imagem',
			'ogpm_data_inclusao' => 'ogpm.ogpm_data_inclusao', 'ogpm.ogpm_data_inclusao' => 'ogpm.ogpm_data_inclusao',
			'ogpm_pin_valor' => 'ogpm.ogpm_pin_valor', 'ogpm.ogpm_pin_valor' => 'ogpm.ogpm_pin_valor',
			'ogpm_pin_resquest_id' => 'ogpm.ogpm_pin_resquest_id', 'ogpm.ogpm_pin_resquest_id' => 'ogpm.ogpm_pin_resquest_id',
			'ogpm_pin_valor_markup' => 'ogpm.ogpm_pin_valor_markup', 'ogpm.ogpm_pin_valor_markup' => 'ogpm.ogpm_pin_valor_markup',
			'ogp_nome' => 'ogp.ogp_nome', 'ogp.ogp_nome' => 'ogp.ogp_nome'
		);
		$partes = array();
		foreach (explode(',', $orderBy) as $parte) {
			$tokens = preg_split('/\s+/', trim($parte));
			$coluna = strtolower($tokens[0] ?? '');
			$direcao = strtoupper($tokens[1] ?? 'ASC');
			if (!isset($permitidos[$coluna]) || !in_array($direcao, array('ASC', 'DESC'))) return null;
			$partes[] = $permitidos[$coluna] . ' ' . $direcao;
		}
		return count($partes) ? implode(', ', $partes) : null;
	}

	function inserir(&$objProdutoModelo)
	{

		$ret = ProdutoModelo::validarCampos($objProdutoModelo);

		if ($ret == "") {
			$sql = "insert into tb_dist_operadora_games_produto_modelo(ogpm_ogp_id, ogpm_nome, ogpm_descricao, ogpm_valor, ogpm_perc_desconto, ogpm_ativo, ogpm_nome_imagem, ogpm_pin_valor, ogpm_data_inclusao, ogpm_pin_resquest_id, ogpm_pin_valor_markup) values ($1, $2, $3, $4, $5, $6, $7, $8, CURRENT_TIMESTAMP, $9, $10);";
			$params = array($objProdutoModelo->getProdutoId(), $objProdutoModelo->getNome(), $objProdutoModelo->getDescricao(), moeda2numeric($objProdutoModelo->getValor()), moeda2numeric($objProdutoModelo->getPercDesconto()), $objProdutoModelo->getAtivo(), $objProdutoModelo->getNomeImagem(), $objProdutoModelo->getPinValor(), ($objProdutoModelo->getPinRequestId() == "" ? null : $objProdutoModelo->getPinRequestId()), $objProdutoModelo->getValorMarkup());

			$ret = SQLexecuteQueryParams($sql, $params);
			if (!$ret) $ret = "Erro ao inserir modelo.\n";
			else {
				$ret = "";
				$rs_id = SQLexecuteQuery("select currval('dist_operadora_games_produto_modelo_id_seq') as last_id");
				if ($rs_id && pg_num_rows($rs_id) > 0) {
					$rs_id_row = pg_fetch_array($rs_id);
					$objProdutoModelo->setId($rs_id_row['last_id']);
				}
			}
		}

		return $ret;
	}

	function atualizar($objProdutoModelo)
	{

		$ret = ProdutoModelo::validarCampos($objProdutoModelo);

		if ($ret == "") {
			$params = array();
			$sets = array();
			if (!is_null($objProdutoModelo->getProdutoId())) $sets[] = "ogpm_ogp_id = " . $this->addParam($params, $objProdutoModelo->getProdutoId());
			if (!is_null($objProdutoModelo->getNome())) $sets[] = "ogpm_nome = " . $this->addParam($params, $objProdutoModelo->getNome());
			if (!is_null($objProdutoModelo->getDescricao())) $sets[] = "ogpm_descricao = " . $this->addParam($params, $objProdutoModelo->getDescricao());
			if (!is_null($objProdutoModelo->getValor())) $sets[] = "ogpm_valor = " . $this->addParam($params, moeda2numeric($objProdutoModelo->getValor()));
			if (!is_null($objProdutoModelo->getPercDesconto())) $sets[] = "ogpm_perc_desconto = " . $this->addParam($params, moeda2numeric($objProdutoModelo->getPercDesconto()));
			if (!is_null($objProdutoModelo->getAtivo())) $sets[] = "ogpm_ativo = " . $this->addParam($params, $objProdutoModelo->getAtivo());
			if (!is_null($objProdutoModelo->getNomeImagem())) $sets[] = "ogpm_nome_imagem = " . $this->addParam($params, $objProdutoModelo->getNomeImagem());
			if (!is_null($objProdutoModelo->getPinValor())) $sets[] = "ogpm_pin_valor = " . $this->addParam($params, $objProdutoModelo->getPinValor());
			if (!is_null($objProdutoModelo->getPinRequestId())) $sets[] = ($objProdutoModelo->getPinRequestId() == "" ? "ogpm_pin_resquest_id = NULL" : "ogpm_pin_resquest_id = " . $this->addParam($params, $objProdutoModelo->getPinRequestId()));
			if (!is_null($objProdutoModelo->getValorMarkup())) $sets[] = "ogpm_pin_valor_markup = " . $this->addParam($params, $objProdutoModelo->getValorMarkup());
			$sql = "update tb_dist_operadora_games_produto_modelo set " . implode(", ", $sets);
			$sql .= " where ogpm_id = " . $this->addParam($params, $objProdutoModelo->getId());

			$ret = SQLexecuteQueryParams($sql, $params);
			if (!$ret) $ret = "Erro ao atualizar modelo.\n";
			else $ret = "";
		}

		return $ret;
	}

	function validarCampos($objProdutoModelo)
	{

		$ret = "";

		//ProdutoId
		$produtoId = $objProdutoModelo->getProdutoId();
		if (is_null($produtoId) || $produtoId == 0) 	$ret .= "Código do produto inválido.\n";
		elseif (!is_numeric($produtoId)) 			$ret .= "Código do produto deve ser numérico.\n";

		//Nome
		$nome = $objProdutoModelo->getNome();
		if (!is_null($nome)) {
			if (strlen($nome) > 100) 		$ret .= "O nome deve ter até 100 caracteres.\n";
		}

		//Descricao
		$descricao = $objProdutoModelo->getDescricao();
		if (is_null($descricao) || $descricao == "") $ret .= "A Descrição deve ser preenchida.\n";
		elseif (strlen($descricao) > 1024) 			$ret .= "A Descrição deve ter até 1024 caracteres.\n";

		//Valor
		$valor = $objProdutoModelo->getValor();
		if (is_null($valor)) 		$ret .= "Valor deve ser preenchido.\n";
		elseif (!is_moeda($valor)) 	$ret .= "Valor inválido.1\n";

		//PercDesconto
		$percDesconto = $objProdutoModelo->getPercDesconto();
		if (is_null($percDesconto)) 		$ret .= "Percentual de desconto deve ser preenchido.\n";
		elseif (!is_moeda($percDesconto)) $ret .= "Percentual de desconto inválido.2\n";

		//ativo
		$ativo = $objProdutoModelo->getAtivo();
		if (is_null($ativo) || $ativo == "") $ret .= "O status deve ser selecionado.\n";
		else if (!is_numeric($ativo)) $ret .= "O status deve ser númerico.\n";

		//NomeImagem
		$nomeImagem = $objProdutoModelo->getNomeImagem();
		if (!is_null($nomeImagem)) {
			if (strlen($nomeImagem) > 100) $ret .= "O Nome da Imagem deve ter até 100 caracteres.\n";
		}

		//PinValor

		$pinValor = $objProdutoModelo->getPinValor();
		if (is_null($pinValor)) 			$ret .= "Valor do PIN inválido.\n";
		elseif (!is_numeric($pinValor)) 	$ret .= "Valor do PIN deve ser numérico.\n";
		elseif ($pinValor < 0) 			$ret .= "Valor do PIN deve ser maior que zero.\n";

		return $ret;
	}

	function obter($filtro, $orderBy, &$rs)
	{

		$ret = "";
		$params = array();
		$filtro = is_array($filtro) ? array_map("strtoupper", $filtro) : array();
		$filtro += array(
			"com_produto" => null,
			"ogpm_data_inclusaoMin" => null,
			"ogpm_data_inclusaoMax" => null,
			"ogpm_id" => null,
			"ogpm_ogp_id" => null,
			"ogpm_nome" => null,
			"ogpm_nomeLike" => null,
			"ogpm_descricao" => null,
			"ogpm_descricaoLike" => null,
			"ogpm_valorMin" => null,
			"ogpm_valorMax" => null,
			"ogpm_perc_descontoMin" => null,
			"ogpm_perc_descontoMax" => null,
			"ogpm_ativo" => null,
			"ogpm_nome_imagem" => null,
			"ogpm_nome_imagemLike" => null,
			"ogpm_pin_valor" => null,
		);

		$sql = "select * from tb_dist_operadora_games_produto_modelo ogpm ";
		if (!is_null($filtro['com_produto'])) $sql .= "inner join tb_dist_operadora_games_produto ogp on ogp.ogp_id = ogpm.ogpm_ogp_id";

		if (!empty($filtro)) {

			if (!is_null($filtro['ogpm_data_inclusaoMin']) && !is_null($filtro['ogpm_data_inclusaoMax'])) {
				$filtro['ogpm_data_inclusaoMin'] = formata_data_ts($filtro['ogpm_data_inclusaoMin'] . " 00:00:00", 1, true, true);
				$filtro['ogpm_data_inclusaoMax'] = formata_data_ts($filtro['ogpm_data_inclusaoMax'] . " 23:59:59", 1, true, true);
			}

			$where = array();
			if (!is_null($filtro['ogpm_id'])) $where[] = "ogpm.ogpm_id = " . $this->addParam($params, $filtro['ogpm_id']);
			if (!is_null($filtro['ogpm_ogp_id'])) $where[] = "ogpm.ogpm_ogp_id = " . $this->addParam($params, $filtro['ogpm_ogp_id']);
			if (!is_null($filtro['ogpm_nome'])) $where[] = "upper(ogpm.ogpm_nome) = " . $this->addParam($params, $filtro['ogpm_nome']);
			if (!is_null($filtro['ogpm_nomeLike'])) $where[] = "upper(ogpm.ogpm_nome) like " . $this->addParam($params, '%' . $filtro['ogpm_nomeLike'] . '%');
			if (!is_null($filtro['ogpm_descricao'])) $where[] = "upper(ogpm.ogpm_descricao) = " . $this->addParam($params, $filtro['ogpm_descricao']);
			if (!is_null($filtro['ogpm_descricaoLike'])) $where[] = "upper(ogpm.ogpm_descricao) like " . $this->addParam($params, '%' . $filtro['ogpm_descricaoLike'] . '%');
			if (!is_null($filtro['ogpm_valorMin']) && !is_null($filtro['ogpm_valorMax'])) $where[] = "ogpm.ogpm_valor between " . $this->addParam($params, $filtro['ogpm_valorMin']) . " and " . $this->addParam($params, $filtro['ogpm_valorMax']);
			if (!is_null($filtro['ogpm_perc_descontoMin']) && !is_null($filtro['ogpm_perc_descontoMax'])) $where[] = "ogpm.ogpm_perc_desconto between " . $this->addParam($params, $filtro['ogpm_perc_descontoMin']) . " and " . $this->addParam($params, $filtro['ogpm_perc_descontoMax']);
			if (!is_null($filtro['ogpm_ativo'])) $where[] = "ogpm.ogpm_ativo = " . $this->addParam($params, $filtro['ogpm_ativo']);
			if (!is_null($filtro['ogpm_nome_imagem'])) $where[] = "upper(ogpm.ogpm_nome_imagem) = " . $this->addParam($params, $filtro['ogpm_nome_imagem']);
			if (!is_null($filtro['ogpm_nome_imagemLike'])) $where[] = "upper(ogpm.ogpm_nome_imagem) like " . $this->addParam($params, '%' . $filtro['ogpm_nome_imagemLike'] . '%');
			if (!is_null($filtro['ogpm_data_inclusaoMin']) && !is_null($filtro['ogpm_data_inclusaoMax'])) $where[] = "ogpm.ogpm_data_inclusao between " . $this->addParam($params, $filtro['ogpm_data_inclusaoMin']) . " and " . $this->addParam($params, $filtro['ogpm_data_inclusaoMax']);
			if (!is_null($filtro['ogpm_pin_valor'])) $where[] = "ogpm.ogpm_pin_valor = " . $this->addParam($params, $filtro['ogpm_pin_valor']);

			if (count($where)) $sql .= " where " . implode(" and ", $where);
		}

		$order = $this->orderByModelo($orderBy);
		if ($order) $sql .= " order by " . $order;

		$rs = count($params) ? SQLexecuteQueryParams($sql, $params) : SQLexecuteQuery($sql);
		if (!$rs) $ret = "Erro ao obter modelo(s).\n";

		return $ret;
	}

	function excluir($produto_modelo_id)
	{

		$ret = "";

		if (!$produto_modelo_id) $ret = "Codigo do modelo nao fornecido.\n";
		elseif (!is_numeric($produto_modelo_id)) $ret = "Codigo do modelo invalido.\n";

		if ($ret == "") {
			$sql = "delete from tb_dist_operadora_games_produto_modelo where ogpm_id = $1";

			$ret = SQLexecuteQueryParams($sql, array($produto_modelo_id));
			if (!$ret) $ret = "Erro ao excluir modelo.\n";
			else $ret = "";
		}

		return $ret;
	}

	function contar($opr_codigo, $pin_valor)
	{

		$ret = "";

		if (!$opr_codigo) $ret = "0";
		elseif (!is_numeric($opr_codigo)) $ret = "0";

		if ($ret == "") {
			$sql = "select count(*) as quantidade from pins ";
			$sql .= "where opr_codigo = $1 ";
			$sql .= "and pin_valor = $2 ";
			$sql .= "and pin_canal = 's' ";
			$sql .= "and pin_status = '1' ";
			$sql .= "group by opr_codigo, pin_valor, pin_status";
			$rs_count = SQLexecuteQueryParams($sql, array($opr_codigo, $pin_valor));
			if ($rs_count && pg_num_rows($rs_count) > 0) {
				$rs_count_value = pg_fetch_array($rs_count);
				$ret = $rs_count_value['quantidade'];
			} else {
				$ret = "0";
			}
		}

		return $ret;
	}
}
