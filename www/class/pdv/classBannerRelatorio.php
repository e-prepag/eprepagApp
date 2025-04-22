<?php
class BannerRelatorio 
{    
    var $br_id;
    var $br_data;
    var $br_hora;
	var $br_ug_dist_codigo;
    var $br_ug_codigo;
    var $br_tipo_usuario;
    var $br_b_codigo;

    function BannerRelatorio(	$br_id 				= null,
    							$br_data 			= null,
    							$br_hora 			= null,
								$br_ug_dist_codigo 	= null,
    							$br_ug_codigo 		= null,
								$br_tipo_usuario 	= null,
								$br_b_codigo 		= null)
	{
    	$this->setId($br_id);
    	$this->setData($br_data);
    	$this->setHora($br_hora);
    	$this->setCodigoUsuario($br_ug_codigo);
		$this->setCodigoUsuarioDist($br_ug_dist_codigo);
    	$this->setTipoUsuario($br_tipo_usuario);
    	$this->setCodigoBanner($br_b_codigo);
    }
    
    
    function getId()
	{
    	return $this->br_id;
    }
    function setId($br_id)
	{
    	$this->br_id = $br_id;
    }
    
    function getData()
	{
    	return $this->br_data;
    }
    function setData($br_data)
	{
    	$this->br_data = $br_data;
    }
    
    function getHora()
	{
    	return $this->br_hora;
    }
    function setHora($br_hora)
	{
    	$this->br_hora = $br_hora;
    }
    
    function getCodigoUsuario()
	{
    	return $this->br_ug_codigo;
    }
    function setCodigoUsuario($br_ug_codigo)
	{
    	$this->br_ug_codigo = $br_ug_codigo;
    }
	
	function getCodigoUsuarioDist()
	{
    	return $this->br_ug_dist_codigo;
    }
    function setCodigoUsuarioDist($br_ug_dist_codigo)
	{
    	$this->br_ug_dist_codigo = $br_ug_dist_codigo;
    }
    
    function getTipoUsuario()
	{
    	return $this->br_tipo_usuario;
    }
    function setTipoUsuario($br_tipo_usuario)
	{
    	$this->br_tipo_usuario = $br_tipo_usuario;
    }
    
    function getCodigoBanner()
	{
    	return $this->br_b_codigo;
    }
    function setCodigoBanner($br_b_codigo)
	{
    	$this->br_b_codigo = $br_b_codigo;
    }
    
    function inserir(&$objBannerRelatorio)
	{ 		
		$sql = "insert into tb_promocoes_relatorios(" .
				"br_data, br_hora, br_tipo_usuario, br_ug_dist_codigo, br_ug_codigo, " .
				"br_b_codigo) values (";
		$sql .= SQLaddFields($objBannerRelatorio->getData(), "s") . ",";
		$sql .= SQLaddFields($objBannerRelatorio->getHora(), "s") . ",";
		$sql .= SQLaddFields($objBannerRelatorio->getTipoUsuario(), "") . ",";
		$sql .= SQLaddFields($objBannerRelatorio->getCodigoUsuarioDist(), "") . ",";
		$sql .= SQLaddFields($objBannerRelatorio->getCodigoUsuario(), "") . ",";
		$sql .= SQLaddFields($objBannerRelatorio->getCodigoBanner(), "") . ")";
	
		$ret = SQLexecuteQuery($sql);
		
 		return $ret;   	
    }    
    
	function obter($filtro, $orderBy, &$rs)
	{
		$ret = "";
		$filtro = array_map("strtoupper", $filtro);
	
		$sql = "select br.br_data,br.br_hora,br.br_tipo_usuario,br.br_ug_dist_codigo as br_codigo_dist,dist.ug_nome as br_nome_dist,dist.ug_nome_fantasia as br_nome_fantasia_dist,br_ug_codigo as br_codigo,ug.ug_nome as br_nome 
				from tb_promocoes_relatorios br 
				left join usuarios_games ug on br.br_ug_codigo = ug.ug_id 
				left join dist_usuarios_games dist on br.br_ug_dist_codigo = dist.ug_id";
		
		if(!is_null($filtro) && $filtro != "")
			if (!is_null($filtro["b_id"]))
				$sql .= " where br.br_b_codigo = " . SQLaddFields($filtro['b_id'], "") . "";						
		
		if(!is_null($orderBy)) 
			$sql .= " order by " . $orderBy;
		
		$rs = SQLexecuteQuery($sql);
		if(!$rs) 
			$ret = "Erro ao obter relatório(s).\n";

		return $ret;
	}
}
?>