<?php
class Banner 
{   
    var $b_id;
    var $b_nome;
    var $b_texto_conteudo;
	var $b_conteudo;
	var $b_tipo;
    var $b_ativo;
    var $b_img_banner;
	var $b_img_conteudo;
    var $b_data_inicio;
	var $b_data_expira;
    var $b_titulo;
	var $b_click;
	var $b_url;
	var $b_contador;

    function Banner(	$b_id 				= null,
   						$b_nome 			= null,
   						$b_texto_conteudo	= null,
						$b_conteudo			= null,
   						$b_tipo 			= null,
   						$b_ativo 			= null,
   						$b_img_banner		= null,
   						$b_img_conteudo		= null,
						$b_data_expira		= null,
						$b_data_inicio		= null,
						$b_titulo			= null,
						$b_url				= null,
						$b_contador			= null)
	{
    	$this->setId($b_id);
    	$this->setNome($b_nome);
		$this->setTextoConteudo($b_texto_conteudo);
		$this->setConteudo($b_conteudo);
    	$this->setTipo($b_tipo);
    	$this->setAtivo($b_ativo);
    	$this->setImgBanner($b_img_banner);
    	$this->setImgConteudo($b_img_conteudo);
		$this->setDataExpira($b_data_expira);
		$this->setDataInicio($b_data_inicio);
		$this->setTitulo($b_titulo);
		$this->setUrl($b_url);
		$this->setContador($b_contador);
    }
    
    
    function getId()
	{
    	return $this->b_id;
    }
    function setId($b_id)
	{
    	$this->b_id = $b_id;
    }
    
    function getNome()
	{
    	return $this->b_nome;
    }
    function setNome($b_nome)
	{
    	$this->b_nome = $b_nome;
    }
    
    function getTextoConteudo()
	{
    	return $this->b_texto_conteudo;
    }
    function setTextoConteudo($b_texto_conteudo)
	{
    	$this->b_texto_conteudo = $b_texto_conteudo;
    }
    
	function getConteudo()
	{
    	return $this->b_conteudo;
    }
    function setConteudo($b_conteudo)
	{
    	$this->b_conteudo = $b_conteudo;
    }
	
    function getAtivo()
	{
    	return $this->b_ativo;
    }
    function setAtivo($b_ativo)
	{
		if($b_ativo == 1 || $b_ativo == "1" || $b_ativo === "true") 
			$b_ativo = "1";
		else 
			$b_ativo = "0";
    	$this->b_ativo = $b_ativo;
    }
    
    function getTipo()
	{
    	return $this->b_tipo;
    }
    function setTipo($b_tipo)
	{
    	$this->b_tipo = $b_tipo;
    }
    
    function getImgBanner()
	{
    	return $this->b_img_banner;
    }
    function setImgBanner($b_img_banner)
	{
    	$this->b_img_banner = $b_img_banner;
    }
    
    function getImgConteudo()
	{
    	return $this->b_img_conteudo;
    }
    function setImgConteudo($b_img_conteudo)
	{
    	$this->b_img_conteudo = $b_img_conteudo;
    }
	
	function getDataExpira()
	{
    	return $this->b_data_expira;
    }
    function setDataExpira($b_data_expira)
	{
    	$this->b_data_expira = $b_data_expira;
    }
	
	function getDataInicio()
	{
    	return $this->b_data_inicio;
    }
    function setDataInicio($b_data_inicio)
	{
    	$this->b_data_inicio = $b_data_inicio;
    }
	
	function getTitulo()
	{
    	return $this->b_titulo;
    }
    function setTitulo($b_titulo)
	{
    	$this->b_titulo = $b_titulo;
    }
	
	function getClick()
	{
    	return $this->b_click;
    }
	
	function getUrl()
	{
    	return $this->b_url;
    }
    function setUrl($b_url)
	{
    	$this->b_url = $b_url;
    }
	
	function getContador()
	{
    	return $this->b_contador;
    }
    function setContador($b_contador)
	{
    	$this->b_contador = $b_contador;
    }
	

	private function addParam(&$params, $value)
	{
		$params[] = $value;
		return '$' . count($params);
	}

	private function orderByBanner($orderBy)
	{
		if (is_null($orderBy) || trim((string) $orderBy) == '') return null;
		$permitidos = array('b_id', 'b_nome', 'b_texto_conteudo', 'b_conteudo', 'b_tipo', 'b_ativo', 'b_img_banner', 'b_img_conteudo', 'b_data_expira', 'b_data_inicio', 'b_titulo', 'b_url', 'b_contador');
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

    function inserir(&$objBanner)
	{
 		$ret = $this->validarCampos($objBanner);
 
 		if($ret == "")
		{
			$sql_contador = "select max(b_contador) as contador from tb_promocoes";
			$ret_contador = SQLexecuteQuery($sql_contador);
			if (!$ret_contador)
				$ret = "Erro ao inserir banner.\n";
			else
			{
				$sql = "insert into tb_promocoes(b_nome, b_texto_conteudo, b_conteudo, b_tipo, b_ativo, b_img_banner, b_img_conteudo, b_data_expira, b_data_inicio, b_titulo, b_url, b_contador) values ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12)";
				$params = array($objBanner->getNome(), $objBanner->getTextoConteudo(), $objBanner->getConteudo(), $objBanner->getTipo(), $objBanner->getAtivo(), $objBanner->getImgBanner(), $objBanner->getImgConteudo(), formata_data($objBanner->getDataExpira(),1), formata_data($objBanner->getDataInicio(),1), $objBanner->getTitulo(), $objBanner->getUrl(), pg_fetch_result($ret_contador,0,0));
				$ret = SQLexecuteQueryParams($sql, $params);
				
				if(!$ret) 
					$ret = "Erro ao inserir banner.\n";
				else
				{	
					$ret = "";
					$rs_id = SQLexecuteQuery("select currval('sq_promocoes') as last_id");
					if($rs_id && pg_num_rows($rs_id) > 0)
					{
						$rs_id_row = pg_fetch_array($rs_id);
						$objBanner->setId($rs_id_row['last_id']);
					}
				}
			}		
 		}
 		
 		return $ret;
    }
    
    function atualizar($objBanner)
	{
 		$ret = Banner::validarCampos($objBanner);
 
 		if($ret == "")
		{
			$params = array();
			$sets = array();
			if(!is_null($objBanner->getNome())) $sets[] = "b_nome = " . $this->addParam($params, $objBanner->getNome());
			if(!is_null($objBanner->getTextoConteudo())) $sets[] = "b_texto_conteudo = " . $this->addParam($params, $objBanner->getTextoConteudo());
			if(!is_null($objBanner->getConteudo())) $sets[] = "b_conteudo = " . $this->addParam($params, $objBanner->getConteudo());
			if(!is_null($objBanner->getTipo())) $sets[] = "b_tipo = " . $this->addParam($params, $objBanner->getTipo());
			if(!is_null($objBanner->getAtivo())) $sets[] = "b_ativo = " . $this->addParam($params, $objBanner->getAtivo());
			if(!is_null($objBanner->getImgBanner())) $sets[] = "b_img_banner = " . $this->addParam($params, $objBanner->getImgBanner());
			if(!is_null($objBanner->getImgConteudo())) $sets[] = "b_img_conteudo = " . $this->addParam($params, $objBanner->getImgConteudo());
			if(!is_null($objBanner->getDataExpira())) $sets[] = "b_data_expira = " . $this->addParam($params, formata_data($objBanner->getDataExpira(),1));
			if(!is_null($objBanner->getDataInicio())) $sets[] = "b_data_inicio = " . $this->addParam($params, formata_data($objBanner->getDataInicio(),1));
			if(!is_null($objBanner->getTitulo())) $sets[] = "b_titulo = " . $this->addParam($params, $objBanner->getTitulo());
			if(!is_null($objBanner->getUrl())) $sets[] = "b_url = " . $this->addParam($params, $objBanner->getUrl());
			$sql = "update tb_promocoes set " . implode(", ", $sets);
 			$sql .= " where b_id = " . $this->addParam($params, $objBanner->getId());
			
			$ret = SQLexecuteQueryParams($sql, $params);
			if(!$ret) $ret = "Erro ao atualizar banner.\n";
			else $ret = "";
 		}
 		
 		return $ret;   	
    }
    
	function validarCampos($objBanner)
	{	
		$ret = "";
		
		//Nome
 		$nome = $objBanner->getNome();
 		if(is_null($nome) || $nome == "") 			$ret .= "O Nome deve ser preenchido.\n";
 		elseif(strlen($nome) > 200) 				$ret .= "O nome deve ter até 200 caracteres.\n";
 		
		//Imagem - Banner
 		$imagem_banner = $objBanner->getImgBanner();
 		if(!is_null($imagem_banner))
 			if(strlen($imagem_banner) > 200) 		$ret .= "O nome da imagem de banner deve ter até 200 caracteres.\n";
			
 		//Imagem - Conteúdo
 		$imagem_conteudo = $objBanner->getImgConteudo();
 		if(!is_null($imagem_conteudo))
 			if(strlen($imagem_conteudo) > 200) 		$ret .= "O nome da imagem de conteúdo deve ter até 200 caracteres.\n";
		
		//Texto - Conteúdo
 		$texto_conteudo = $objBanner->getTextoConteudo();
		if ($objBanner->getConteudo == 2 && $texto_conteudo == "")
			$ret .= "Você escolheu o texto como forma de conteúdo. É preciso digitar texto de conteúdo ou trocar o tipo de conteúdo.\n";
 		
 		return $ret;
	}

	function obter($filtro, $orderBy, &$rs) {
		$ret = "";
		$params = array();
		$filtro = is_array($filtro) ? array_map("strtoupper", $filtro) : array();
		$filtro += array('b_id' => null, 'b_nome' => null, 'b_nomeLike' => null, 'b_conteudo' => null, 'b_tipo' => null, 'b_status' => null, 'b_titulo' => null, 'b_tituloLike' => null, 'b_data_hoje' => null, 'b_data_expiraMin' => null, 'b_data_expiraMax' => null, 'b_data_inicioMin' => null, 'b_data_inicioMax' => null, 'b_url' => null, 'b_urlLike' => null);
			
		$sql = "select * from tb_promocoes ";

		if(!empty($filtro))
		{
			if(!is_null($filtro['b_data_expiraMin']) && !is_null($filtro['b_data_expiraMax']))
			{
				$filtro['b_data_expiraMin'] = str_replace("00:00:00","",formata_data_ts($filtro['b_data_expiraMin'],2,false,false));
				$filtro['b_data_expiraMax'] = str_replace("00:00:00","",formata_data_ts($filtro['b_data_expiraMax'],2,false,false));
			}
			
			if(!is_null($filtro['b_data_inicioMin']) && !is_null($filtro['b_data_inicioMax']))
			{
				$filtro['b_data_inicioMin'] = str_replace("00:00:00","",formata_data_ts($filtro['b_data_inicioMin'],2,false,false));
				$filtro['b_data_inicioMax'] = str_replace("00:00:00","",formata_data_ts($filtro['b_data_inicioMax'],2,false,false));
			}
			
			$where = array();
			if(!is_null($filtro['b_id'])) $where[] = "b_id = " . $this->addParam($params, $filtro['b_id']);
			if(!is_null($filtro['b_nome'])) $where[] = "upper(b_nome) = " . $this->addParam($params, $filtro['b_nome']);
			if(!is_null($filtro['b_nomeLike'])) $where[] = "upper(b_nome) like " . $this->addParam($params, '%' . $filtro['b_nomeLike'] . '%');
			if(!is_null($filtro['b_conteudo'])) $where[] = "b_conteudo = " . $this->addParam($params, $filtro['b_conteudo']);
			if(!is_null($filtro['b_tipo'])) {
				if ($filtro['b_tipo'] == 3) $where[] = "b_tipo in (1, 2)";
				else if ($filtro['b_tipo'] == 4) $where[] = "b_tipo in (0, 2)";
				else $where[] = "b_tipo = " . $this->addParam($params, $filtro['b_tipo']);
			}
			if(!is_null($filtro['b_status'])) $where[] = "b_ativo = " . $this->addParam($params, (($filtro['b_status']==1)?1:0));
			if(!is_null($filtro['b_titulo'])) $where[] = "b_titulo = " . $this->addParam($params, $filtro['b_titulo']);
			if(!is_null($filtro['b_tituloLike'])) $where[] = "upper(b_titulo) like " . $this->addParam($params, '%' . $filtro['b_tituloLike'] . '%');
			if(!is_null($filtro['b_data_hoje'])) $where[] = "(b_data_expira >= " . $this->addParam($params, $filtro['b_data_hoje']) . " and b_data_inicio <= " . $this->addParam($params, $filtro['b_data_hoje']) . ")";
			if(!is_null($filtro['b_data_expiraMin']) && !is_null($filtro['b_data_expiraMax'])) $where[] = "b_data_expira between " . $this->addParam($params, $filtro['b_data_expiraMin']) . " and " . $this->addParam($params, $filtro['b_data_expiraMax']);
			if(!is_null($filtro['b_data_inicioMin']) && !is_null($filtro['b_data_inicioMax'])) $where[] = "b_data_inicio between " . $this->addParam($params, $filtro['b_data_inicioMin']) . " and " . $this->addParam($params, $filtro['b_data_inicioMax']);
			if(!is_null($filtro['b_url'])) $where[] = "b_url = " . $this->addParam($params, $filtro['b_url']);
			if(!is_null($filtro['b_urlLike'])) $where[] = "upper(b_url) like " . $this->addParam($params, '%' . $filtro['b_urlLike'] . '%');
			if (count($where)) $sql .= " where " . implode(" and ", $where);
		}
		
		$order = $this->orderByBanner($orderBy);
		if($order) $sql .= " order by " . $order;
		$rs = count($params) ? SQLexecuteQueryParams($sql, $params) : SQLexecuteQuery($sql);
		if(!$rs) $ret = "Erro ao obter banner(s).\n";

		return $ret;
	}
	
 
	function showBanners($codigo_usuario,$tipo_usuario,$rs_banners, $subdir,$PREPAG_DOMINIO,$URL_DIR_IMAGES_BANNER = null) {
	  echo "
	   <br>
	   <center><table>
			  <tr>
	  ";

	  //Imprime os banners disponíveis na tela
	  while($rs_banners_row = pg_fetch_array($rs_banners)) {
	   echo "<form name=\"frmEnviar" . $rs_banners_row["b_id"] . "\" id=\"frmEnviar" . $rs_banners_row["b_id"] . "\" method=\"post\" action=\"".$rs_banners_row["b_url"]."\" target=\"_blank\">";
	   echo "<input type=\"hidden\" name=\"id\" id=\"id\" value=\"" . $rs_banners_row["b_id"] . "\">";
	   echo "<input type=\"hidden\" name=\"tipo_usuario\" id=\"tipo_usuario\" value=\"" . $tipo_usuario . "\">";
	   echo "<input type=\"hidden\" name=\"codigo_usuario\" id=\"codigo_usuario\" value=\"" . $codigo_usuario . "\">";
	   echo "</form>";
	   
	   echo "<td align=\"center\" width=\"33%\">";
		if($rs_banners_row["b_img_banner"] && $rs_banners_row["b_img_banner"] != "" && file_exists($GLOBALS['FIS_DIR_IMAGES_BANNER'] . $rs_banners_row["b_img_banner"])) {
		echo "
					 &nbsp;
					 <a href=\"#\" onclick=\"javascript:document.getElementById('frmEnviar" . $rs_banners_row["b_id"] . "').submit();\"><img src=\"" . $PREPAG_DOMINIO . $URL_DIR_IMAGES_BANNER . $rs_banners_row["b_img_banner"] . "\" alt=\"" . $rs_banners_row["b_titulo"] . "\" border=\"0\" width=\"192\" height=\"46\"/></a>
					   &nbsp;
		";
	   } else {
		echo "
					 &nbsp;
					 <b><a style=\"text-decoration:none;\" href=\"#\" onclick=\"javascript:document.getElementById('frmEnviar" . $rs_banners_row["b_id"] . "').submit();\">" . formatar($rs_banners_row["b_titulo"]) . "</a></b>
					&nbsp;
		";
	   }
				echo "</td>";
		   }
	  
	  echo "
		</tr>
			</table></center>
	  ";
	 }
}
?>