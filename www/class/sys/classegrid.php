<?php
	require_once $raiz_do_projeto . "public_html/sys/includes/configuracao.php";
	require_once $raiz_do_projeto . "db/connect.php";	
	require_once $raiz_do_projeto . "public_html/sys/includes/functions.php";
	require_once $raiz_do_projeto . "public_html/sys/includes/languages.php";
	
class grid {
	public $conteudo	= '';		// variavel com dados para impressao no navegador (resposta de uma requisicao);
	public $colunas		= array();
	public $mes			= array();
	public $nvenda		= array();
	public $valorvendas	= array();
	public $percvalor	= array();
	public $i			= 0;
	
// Gera grid 
	public function gera_grid_publisher_mes($colunas,$jogo,$nvenda,$valorvendas,$crescente=null,$cols=null,$ordem=null) {
		$conteudo .='<thead>';
		$conteudo .='	<tr align="center">';
		
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			$conteudo .='		<th><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.($i+1).'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
			if ($ordem == ($i+1) && $crescente == 'ASC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens//seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
			elseif ($ordem == ($i+1) && $crescente == 'DESC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
			else $conteudo .= $colunas[$i];
			$conteudo .='</a></th>';
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</thead>';	

		$conteudo .='<tfoot>';
		$conteudo .='	<tr align="center">';
		
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			$conteudo .='		<th><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.($i+1).'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
			if ($ordem == ($i+1) && $crescente == 'ASC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
			elseif ($ordem == ($i+1) && $crescente == 'DESC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
			else $conteudo .= $colunas[$i];
			$conteudo .='</a></th>';
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</tfoot>';		

		$conteudo .='<tbody>';
		
		unset($i);

		for ($i = 0; $i <= count($jogo)-1; $i++) {
			$conteudo .='<tr>';
			$conteudo .='    <td><nobr>'.utf8_encode($jogo[$i]).'</nobr></td>';
			$conteudo .='    <td><nobr>'.$nvenda[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$valorvendas[$i].'</nobr></td>';
			$conteudo .=' </tr>';
		}
	
		$conteudo .='</tbody>';
        return $conteudo;
		unset($conteudo);
	}

	public function gera_grid_usuario($colunas,$usuario,$dtini,$dtfim,$aband,$priulve,$nven,$vven,$vper,$crescente=null,$cols=null,$ordem=null) {
		$conteudo .='<thead>';
		$conteudo .='	<tr align="center">';
		
		$i_aux = 1;
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			if ($i <> 4) {
				$conteudo .='		<th><nobr><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.$i_aux.'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
				if ($ordem == $i_aux && $crescente == 'ASC')
					$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
				elseif ($ordem == $i_aux && $crescente == 'DESC')
					$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
				else $conteudo .= $colunas[$i];
				$conteudo.='</a></nobr></th>';
				$i_aux++ ;
			}
			else {
				$conteudo .='		<th><nobr>'.$colunas[$i].'</nobr></th>';
			}
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</thead>';	

		$conteudo .='<tfoot>';
		$conteudo .='	<tr align="center">';
		
		$i_aux = 1;
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			if ($i <> 4) {
				$conteudo .='		<th><nobr><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.$i_aux.'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
				if ($ordem == $i_aux && $crescente == 'ASC')
					$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
				elseif ($ordem == $i_aux && $crescente == 'DESC')
					$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
				else $conteudo .= $colunas[$i];
				$conteudo.='</a></nobr></th>';
				$i_aux++ ;
			}
			else {
				$conteudo .='		<th><nobr>'.$colunas[$i].'</nobr></th>';
			}
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</tfoot>';		

		$conteudo .='<tbody>';
		
		unset($i);

		for ($i = 0; $i <= count($usuario)-1; $i++) {
			$conteudo .='<tr>';
			$conteudo .='    <td><nobr>'.utf8_encode($usuario[$i]).'</nobr></td>';
			if ($dtini[$i]!="")
   			 $conteudo .='    <td><nobr>'.$dtini[$i].'</nobr></td>';
			if ($dtfim[$i]!="")
   			 $conteudo .='    <td><nobr>'.$dtfim[$i].'</nobr></td>';
			if ($aband[$i]!="")
   			 $conteudo .='    <td><nobr>'.$aband[$i].'</nobr></td>';			
			$conteudo .='    <td><nobr>'.$priulve[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$nven[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$vven[$i].'</nobr></td>';
			//$conteudo .='    <td><nobr>'.$vper[$i].'</nobr></td>';
			$conteudo .=' </tr>';
		}
	
		$conteudo .='</tbody>';
        return $conteudo;
		unset($conteudo);
	}


	public function gera_grid_usuario_ultimo_mes($colunas,$usuario,$pos,$nven,$vven,$vper,$crescente=null,$cols=null,$ordem=null) {
		$conteudo .='<thead>';
		$conteudo .='	<tr align="center">';
		
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			$conteudo .='		<th><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.($i+1).'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
			if ($ordem == ($i+1) && $crescente == 'ASC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
			elseif ($ordem == ($i+1) && $crescente == 'DESC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
			else $conteudo .= $colunas[$i];
			$conteudo .='</a></th>';
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</thead>';	

		$conteudo .='<tfoot>';
		$conteudo .='	<tr align="center">';
		
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			$conteudo .='		<th><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.($i+1).'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
			if ($ordem == ($i+1) && $crescente == 'ASC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
			elseif ($ordem == ($i+1) && $crescente == 'DESC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
			else $conteudo .= $colunas[$i];
			$conteudo .='</a></th>';
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</tfoot>';		

		$conteudo .='<tbody>';
		
		unset($i);

		for ($i = 0; $i <= count($usuario)-1; $i++) {
			$conteudo .='<tr>';
			$conteudo .='    <td><nobr>'.utf8_encode($usuario[$i]).'</nobr></td>';
			$conteudo .='    <td><nobr>'.$pos[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$nven[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$vven[$i].'</nobr></td>';
			//$conteudo .='    <td><nobr>'.$vper[$i].'</nobr></td>';
			$conteudo .=' </tr>';
		}
	
		$conteudo .='</tbody>';
        return $conteudo;
		unset($conteudo);
	}



	
	public function gera_grid_publisher($colunas,$jogo,$nvenda,$valorvendas,$percvalor,$crescente=null,$cols=null,$ordem=null) {
		$conteudo .='<thead>';
		$conteudo .='	<tr align="center">';
		
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			$conteudo .='		<th><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.($i+1).'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
			if ($ordem == ($i+1) && $crescente == 'ASC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
			elseif ($ordem == ($i+1) && $crescente == 'DESC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
			else $conteudo .= $colunas[$i];
			$conteudo .='</a></th>';
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</thead>';	

		$conteudo .='<tfoot>';
		$conteudo .='	<tr align="center">';
		
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			$conteudo .='		<th><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.($i+1).'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
			if ($ordem == ($i+1) && $crescente == 'ASC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
			elseif ($ordem == ($i+1) && $crescente == 'DESC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
			else $conteudo .= $colunas[$i];
			$conteudo .='</a></th>';
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</tfoot>';		

		$conteudo .='<tbody>';
		
		unset($i);

		for ($i = 0; $i <= count($jogo)-1; $i++) {
			$conteudo .='<tr>';
			$conteudo .='    <td><nobr>'.utf8_encode($jogo[$i]).'</nobr></td>';
			$conteudo .='    <td><nobr>'.$nvenda[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$valorvendas[$i].'</nobr></td>';
			//$conteudo .='    <td><nobr>'.$percvalor[$i].'</nobr></td>';
			$conteudo .=' </tr>';
		}
	
		$conteudo .='</tbody>';
        
        return $conteudo;
		unset($conteudo);
	}

	public function gera_grid_cidade($colunas,$cidade,$estado,$nven,$vven,$vper,$crescente=null,$cols=null,$ordem=null) {
		$conteudo .='<thead>';
		$conteudo .='	<tr align="center">';
		
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			$conteudo .='		<th><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.($i+1).'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
			if ($ordem == ($i+1) && $crescente == 'ASC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
			elseif ($ordem == ($i+1) && $crescente == 'DESC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
			else $conteudo .= $colunas[$i];
			$conteudo .='</a></th>';
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</thead>';	

		$conteudo .='<tfoot>';
		$conteudo .='	<tr align="center">';
		
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			$conteudo .='		<th><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.($i+1).'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
			if ($ordem == ($i+1) && $crescente == 'ASC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
			elseif ($ordem == ($i+1) && $crescente == 'DESC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
			else $conteudo .= $colunas[$i];
			$conteudo.='</a></th>';
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</tfoot>';		

		$conteudo .='<tbody>';
		
		unset($i);

		for ($i = 0; $i <= count($cidade)-1; $i++) {
			$conteudo .='<tr>';
			$conteudo .='    <td><nobr>'.utf8_encode($cidade[$i]).'</nobr></td>';
			$conteudo .='    <td><nobr>'.$estado[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$nven[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$vven[$i].'</nobr></td>';
			//$conteudo .='    <td><nobr>'.$vper[$i].'</nobr></td>';
			$conteudo .=' </tr>';
		}
	
		$conteudo .='</tbody>';
		
		//$conteudo .= $this->gera_pager();
		        
        return $conteudo;
		unset($conteudo);
	}


	public function gera_grid_dia_semana($colunas,$diasemana,$nvenda,$valorvendas,$percvalor,$crescente=null,$cols=null,$ordem=null) {
		$conteudo .='<thead>';
		$conteudo .='	<tr align="center">';
		
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			$conteudo .='		<th><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.($i+1).'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
			if ($ordem == ($i+1) && $crescente == 'ASC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
			elseif ($ordem == ($i+1) && $crescente == 'DESC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
			else $conteudo .= $colunas[$i];
			$conteudo.='</a></th>';
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</thead>';	

		$conteudo .='<tfoot>';
		$conteudo .='	<tr align="center">';
		
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			$conteudo .='		<th><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.($i+1).'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
			if ($ordem == ($i+1) && $crescente == 'ASC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
			elseif ($ordem == ($i+1) && $crescente == 'DESC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
			else $conteudo .= $colunas[$i];
			$conteudo.='</a></th>';
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</tfoot>';		

		$conteudo .='<tbody>';
		
		unset($i);

		for ($i = 0; $i <= count($diasemana)-1; $i++) {
			$conteudo .='<tr>';
			$conteudo .='    <td><nobr>'.$diasemana[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$nvenda[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$valorvendas[$i].'</nobr></td>';
			//$conteudo .='    <td><nobr>'.$percvalor[$i].'</nobr></td>';
			$conteudo .=' </tr>';
		}
	
		$conteudo .='</tbody>';
        
        return $conteudo;
		unset($conteudo);
	}
	
	public function gera_grid_mes($colunas,$mes,$nvenda,$valorvendas,$percvalor,$crescente=null,$cols=null,$ordem=null) {
		//die("TEste: ".urlencode($cols));
		$conteudo .='<thead>';
		$conteudo .='	<tr align="center">';
		
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			$conteudo .='		<th><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.($i+1).'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
			if ($ordem == ($i+1) && $crescente == 'ASC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
			elseif ($ordem == ($i+1) && $crescente == 'DESC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
			else $conteudo .= $colunas[$i];
			$conteudo.='</a></th>';
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</thead>';	

		$conteudo .='<tfoot>';
		$conteudo .='	<tr align="center">';
		
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			$conteudo .='		<th><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.($i+1).'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
			if ($ordem == ($i+1) && $crescente == 'ASC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
			elseif ($ordem == ($i+1) && $crescente == 'DESC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
			else $conteudo .= $colunas[$i];
			$conteudo.='</a></th>';
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</tfoot>';		

		$conteudo .='<tbody>';
		
		unset($i);

		for ($i = 0; $i <= count($mes)-1; $i++) {
			$conteudo .='<tr>';
			$conteudo .='    <td><nobr>'.utf8_encode($mes[$i]).'</nobr></td>';
			$conteudo .='    <td><nobr>'.$nvenda[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$valorvendas[$i].'</nobr></td>';
			//$conteudo .='    <td><nobr>'.$percvalor[$i].'</nobr></td>';
			$conteudo .=' </tr>';
		}
	
		$conteudo .='</tbody>';
        return $conteudo;
		unset($conteudo);
	}


	public function gera_grid_dia($colunas,$dia,$dias,$nven,$vven,$vper,$crescente=null,$cols=null,$ordem=null) {
		$conteudo .='<thead>';
		$conteudo .='	<tr align="center">';
		
		$i_aux = 1;
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			if ($i <> 1) {
				$conteudo .='		<th><nobr><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.$i_aux.'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
				if ($ordem == $i_aux && $crescente == 'ASC')
					$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
				elseif ($ordem == $i_aux && $crescente == 'DESC')
					$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
				else $conteudo .= $colunas[$i];
				$conteudo.='</a></nobr></th>';
				$i_aux++ ;
			}
			else {
				$conteudo .='		<th><nobr>'.$colunas[$i].'</nobr></th>';
			}
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</thead>';	

		$conteudo .='<tfoot>';
		$conteudo .='	<tr align="center">';
		
		$i_aux = 1;
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			if ($i <> 1) {
				$conteudo .='		<th><nobr><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.($i+1).'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
				if ($ordem == $i_aux && $crescente == 'ASC')
					$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
				elseif ($ordem == $i_aux && $crescente == 'DESC')
					$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
				else $conteudo .= $colunas[$i];
				$conteudo.='</a></nobr></th>';
				$i_aux++ ;
			}
			else {
				$conteudo .='		<th><nobr>'.$colunas[$i].'</nobr></th>';
			}
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</tfoot>';		

		$conteudo .='<tbody>';
		
		unset($i);

		for ($i = 0; $i <= count($dia)-1; $i++) {
			$conteudo .='<tr>';
			$conteudo .='    <td><nobr>'.utf8_encode($dia[$i]).'</nobr></td>';
			$conteudo .='    <td><nobr>'.$dias[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$nven[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$vven[$i].'</nobr></td>';
			//$conteudo .='    <td><nobr>'.$vper[$i].'</nobr></td>';
			$conteudo .=' </tr>';
		}
	
		$conteudo .='</tbody>';
        
        return $conteudo;
		unset($conteudo);
	}


	public function gera_grid_jogo($colunas,$jogo,$item,$nvenda,$valorvendas,$percvalor,$crescente=null,$cols=null,$ordem=null) {
		$conteudo .='<thead>';
		$conteudo .='	<tr align="center">';
		
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			$conteudo .='		<th><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.($i+1).'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
			if ($ordem == ($i+1) && $crescente == 'ASC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
			elseif ($ordem == ($i+1) && $crescente == 'DESC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
			else $conteudo .= $colunas[$i];
			$conteudo.='</a></th>';
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</thead>';	

		$conteudo .='<tfoot>';
		$conteudo .='	<tr align="center">';
		
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			$conteudo .='		<th><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.($i+1).'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
			if ($ordem == ($i+1) && $crescente == 'ASC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
			elseif ($ordem == ($i+1) && $crescente == 'DESC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
			else $conteudo .= $colunas[$i];
			$conteudo.='</a></th>';
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</tfoot>';		

		$conteudo .='<tbody>';
		
		unset($i);

		for ($i = 0; $i <= count($jogo)-1; $i++) {
			$conteudo .='<tr>';
			$conteudo .='    <td><nobr>'.utf8_encode($jogo[$i]).'</nobr></td>';
			$conteudo .='    <td><nobr>'.$item[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$nvenda[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$valorvendas[$i].'</nobr></td>';
			//$conteudo .='    <td><nobr>'.$percvalor[$i].'</nobr></td>';
			$conteudo .=' </tr>';
		}
	
		$conteudo .='</tbody>';
        
        return $conteudo;
		unset($conteudo);
	}

	public function gera_grid_jogo_MONEY_STATS($colunas,$jogo,$nvenda,$valorvendas,$percvalor,$crescente=null,$cols=null,$ordem=null) {
		$conteudo .='<thead>';
		$conteudo .='	<tr align="center">';
		
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			$conteudo .='		<th><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.($i+1).'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
			if ($ordem == ($i+1) && $crescente == 'ASC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
			elseif ($ordem == ($i+1) && $crescente == 'DESC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
			else $conteudo .= $colunas[$i];
			$conteudo.='</a></th>';
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</thead>';	

		$conteudo .='<tfoot>';
		$conteudo .='	<tr align="center">';
		
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			$conteudo .='		<th><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.($i+1).'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
			if ($ordem == ($i+1) && $crescente == 'ASC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
			elseif ($ordem == ($i+1) && $crescente == 'DESC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
			else $conteudo .= $colunas[$i];
			$conteudo.='</a></th>';
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</tfoot>';		

		$conteudo .='<tbody>';
		
		unset($i);

		for ($i = 0; $i <= count($jogo)-1; $i++) {
			$conteudo .='<tr>';
			$conteudo .='    <td><nobr>'.utf8_encode($jogo[$i]).'</nobr></td>';
			$conteudo .='    <td><nobr>'.$nvenda[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$valorvendas[$i].'</nobr></td>';
			//$conteudo .='    <td><nobr>'.$percvalor[$i].'</nobr></td>';
			$conteudo .=' </tr>';
		}
	
		$conteudo .='</tbody>';
        
        return $conteudo;
		unset($conteudo);
	}

	public function gera_grid_jogo_mes_MONEY_STATS($colunas,$jogo,$nvenda,$valorvendas,$crescente=null,$cols=null,$ordem=null) {
		$conteudo .='<thead>';
		$conteudo .='	<tr align="center">';
		
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			$conteudo .='		<th><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.($i+1).'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
			if ($ordem == ($i+1) && $crescente == 'ASC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
			elseif ($ordem == ($i+1) && $crescente == 'DESC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
			else $conteudo .= $colunas[$i];
			$conteudo.='</a></th>';
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</thead>';	

		$conteudo .='<tfoot>';
		$conteudo .='	<tr align="center">';
		
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			$conteudo .='		<th><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.($i+1).'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
			if ($ordem == ($i+1) && $crescente == 'ASC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
			elseif ($ordem == ($i+1) && $crescente == 'DESC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
			else $conteudo .= $colunas[$i];
			$conteudo.='</a></th>';
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</tfoot>';		

		$conteudo .='<tbody>';
		
		unset($i);

		for ($i = 0; $i <= count($jogo)-1; $i++) {
			$conteudo .='<tr>';
			$conteudo .='    <td><nobr>'.utf8_encode($jogo[$i]).'</nobr></td>';
			$conteudo .='    <td><nobr>'.$nvenda[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$valorvendas[$i].'</nobr></td>';
			$conteudo .=' </tr>';
		}
	
		$conteudo .='</tbody>';
        
        return $conteudo;
		unset($conteudo);
	}

public function gera_grid_jogo_mes_MONEY_STATS2($colunas,$jogo,$nvenda,$valorvendas,$percvenda,$crescente=null,$cols=null,$ordem=null) {
		$conteudo .='<thead>';
		$conteudo .='	<tr align="center">';
		
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			$conteudo .='		<th><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.($i+1).'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
			if ($ordem == ($i+1) && $crescente == 'ASC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
			elseif ($ordem == ($i+1) && $crescente == 'DESC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
			else $conteudo .= $colunas[$i];
			$conteudo.='</a></th>';
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</thead>';	

		$conteudo .='<tfoot>';
		$conteudo .='	<tr align="center">';
		
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			$conteudo .='		<th><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.($i+1).'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
			if ($ordem == ($i+1) && $crescente == 'ASC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
			elseif ($ordem == ($i+1) && $crescente == 'DESC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
			else $conteudo .= $colunas[$i];
			$conteudo.='</a></th>';
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</tfoot>';		

		$conteudo .='<tbody>';
		
		unset($i);

		for ($i = 0; $i <= count($jogo)-1; $i++) {
			$conteudo .='<tr>';
			$conteudo .='    <td><nobr>'.utf8_encode($jogo[$i]).'</nobr></td>';
			$conteudo .='    <td><nobr>'.$nvenda[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$valorvendas[$i].'</nobr></td>';
			//$conteudo .='    <td><nobr>'.$percvenda[$i].'</nobr></td>';
			$conteudo .=' </tr>';
		}
	
		$conteudo .='</tbody>';
        
        return $conteudo;
		unset($conteudo);
	}
	
	public function gera_grid_estado($colunas,$estado,$nvenda,$valorvendas,$percvalor,$crescente=null,$cols=null,$ordem=null) {
		$conteudo .='<thead>';
		$conteudo .='	<tr align="center">';
		
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			$conteudo .='		<th><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.($i+1).'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
			if ($ordem == ($i+1) && $crescente == 'ASC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
			elseif ($ordem == ($i+1) && $crescente == 'DESC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
			else $conteudo .= $colunas[$i];
			$conteudo.='</a></th>';
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</thead>';	

		$conteudo .='<tfoot>';
		$conteudo .='	<tr align="center">';
		
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			$conteudo .='		<th><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.($i+1).'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
			if ($ordem == ($i+1) && $crescente == 'ASC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
			elseif ($ordem == ($i+1) && $crescente == 'DESC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
			else $conteudo .= $colunas[$i];
			$conteudo.='</a></th>';
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</tfoot>';		

		$conteudo .='<tbody>';
		
		unset($i);

		for ($i = 0; $i <= count($estado)-1; $i++) {
			$conteudo .='<tr>';
			$conteudo .='    <td><nobr>'.$estado[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$nvenda[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$valorvendas[$i].'</nobr></td>';
			//$conteudo .='    <td><nobr>'.$percvalor[$i].'</nobr></td>';
			$conteudo .=' </tr>';
		}
	
		$conteudo .='</tbody>';
        
        return $conteudo;
		unset($conteudo);
	}

	public function gera_grid_tipo_estabelecimento($colunas,$estabe,$nvenda,$valorvendas,$percvalor,$crescente=null,$cols=null,$ordem=null) {
		$conteudo .='<thead>';
		$conteudo .='	<tr align="center">';
		
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			$conteudo .='		<th><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.($i+1).'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
			if ($ordem == ($i+1) && $crescente == 'ASC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
			elseif ($ordem == ($i+1) && $crescente == 'DESC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
			else $conteudo .= $colunas[$i];
			$conteudo.='</a></th>';
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</thead>';	

		$conteudo .='<tfoot>';
		$conteudo .='	<tr align="center">';
		
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			$conteudo .='		<th><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.($i+1).'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
			if ($ordem == ($i+1) && $crescente == 'ASC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
			elseif ($ordem == ($i+1) && $crescente == 'DESC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
			else $conteudo .= $colunas[$i];
			$conteudo.='</a></th>';
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</tfoot>';		

		$conteudo .='<tbody>';
		
		unset($i);

		for ($i = 0; $i <= count($estabe)-1; $i++) {
			$conteudo .='<tr>';
			$conteudo .='    <td><nobr>'.utf8_encode($estabe[$i]).'</nobr></td>';
			$conteudo .='    <td><nobr>'.$nvenda[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$valorvendas[$i].'</nobr></td>';
			//$conteudo .='    <td><nobr>'.$percvalor[$i].'</nobr></td>';
			$conteudo .=' </tr>';
		}
	
		$conteudo .='</tbody>';

        return $conteudo;
		unset($conteudo);
	}	

	public function gera_grid_estabelecimento($colunas,$estabe,$tipo,$dtini,$dtfim,$aband,$ultvenda,$cidade,$uf,$nvenda,$valorvendas,$percvalor,$crescente=null,$cols=null,$ordem=null) {
		$conteudo .='<thead>';
		$conteudo .='	<tr align="center">';
		//Estabelecimento,Tipo,Data Inicio,Ultima Data,Abandonou,1a-Ultima Venda,Cidade,UF ,N. de Vendas,Valor das Vendas em (R$) 
		//ve_estabelecimento, ve_estabtipo, ve_cidade, ve_estado,  n, vendas, primeira_venda, ultima_venda
		$i_aux = 1;
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			if ($i <> 5) {
				$conteudo .='		<th><nobr><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.$i_aux.'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
				if ($ordem == $i_aux && $crescente == 'ASC')
					$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
				elseif ($ordem == $i_aux && $crescente == 'DESC')
					$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
				else $conteudo .= $colunas[$i];
				$conteudo .='</a></nobr></th>';
				$i_aux++ ;
			}
			else {
				$conteudo .='		<th><nobr>'.$colunas[$i].'</nobr></th>';
			}
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</thead>';	

		$conteudo .='<tfoot>';
		$conteudo .='	<tr align="center">';
		
		$i_aux = 1;
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			if ($i <> 5) {
				$conteudo .='		<th><nobr><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.$i_aux.'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
				if ($ordem == $i_aux && $crescente == 'ASC')
					$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
				elseif ($ordem == $i_aux && $crescente == 'DESC')
					$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
				else $conteudo .= $colunas[$i];
				$conteudo .='</a></nobr></th>';
				$i_aux++ ;
			}
			else {
				$conteudo .='		<th><nobr>'.$colunas[$i].'</nobr></th>';
			}
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</tfoot>';		

		$conteudo .='<tbody>';
		
		unset($i);

		for ($i = 0; $i <= count($estabe)-1; $i++) {
			$conteudo .='<tr>';
			$conteudo .='    <td><nobr>'.utf8_encode($estabe[$i]).'</nobr></td>';
			$conteudo .='    <td><nobr>'.utf8_encode($tipo[$i]).'</nobr></td>';
			$conteudo .='    <td><nobr>'.$dtini[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$dtfim[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$aband[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$ultvenda[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.utf8_encode($cidade[$i]).'</nobr></td>';
			$conteudo .='    <td><nobr>'.$uf[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$nvenda[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$valorvendas[$i].'</nobr></td>';
			//$conteudo .='    <td><nobr>'.$percvalor[$i].'</nobr></td>';
			$conteudo .='</tr>';
		}
		$conteudo .='</tbody>';
        
        return $conteudo;
		unset($conteudo);
	}	

	public function gera_grid_estabelecimento_mes($colunas,$estabe,$pos,$cidade,$uf,$nvenda,$valorvendas,$percvalor,$crescente=null,$cols=null,$ordem=null) {
		$conteudo .='<thead>';
		$conteudo .='	<tr align="center">';
		
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			$conteudo .='		<th><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.($i+1).'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
			if ($ordem == ($i+1) && $crescente == 'ASC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
			elseif ($ordem == ($i+1) && $crescente == 'DESC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
			else $conteudo .= $colunas[$i];
			$conteudo.='</a></th>';
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</thead>';	

		$conteudo .='<tfoot>';
		$conteudo .='	<tr align="center">';
		
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			$conteudo .='		<th><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.($i+1).'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
			if ($ordem == ($i+1) && $crescente == 'ASC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
			elseif ($ordem == ($i+1) && $crescente == 'DESC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
			else $conteudo .= $colunas[$i];
			$conteudo.='</a></th>';
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</tfoot>';		

		$conteudo .='<tbody>';
		
		unset($i);

		for ($i = 0; $i <= count($estabe)-1; $i++) {
			$conteudo .='<tr>';
			$conteudo .='    <td><nobr>'.utf8_encode($estabe[$i]).'</nobr></td>';
			$conteudo .='    <td><nobr>'.$pos[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.utf8_encode($cidade[$i]).'</nobr></td>';
			$conteudo .='    <td><nobr>'.$uf[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$nvenda[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$valorvendas[$i].'</nobr></td>';
			//$conteudo .='    <td><nobr>'.$percvalor[$i].'</nobr></td>';
			$conteudo .=' </tr>';
		}
	
		$conteudo .='</tbody>';
        
        return $conteudo;
		unset($conteudo);
	}	




	public function gera_grid_estabelecimento2($colunas,$estabe,$dtini,$dtfim,$aband,$ultvenda,$cidade,$uf,$nvenda,$valorvendas,$percvalor,$crescente=null,$cols=null,$ordem=null) {
		$conteudo .='<thead>';
		$conteudo .='	<tr align="center">';
		
		$i_aux = 1;
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			if ($i <> 4) {
				$conteudo .='		<th><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.$i_aux.'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
				if ($ordem == $i_aux && $crescente == 'ASC')
					$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
				elseif ($ordem == $i_aux && $crescente == 'DESC')
					$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
				else $conteudo .= $colunas[$i];
				$conteudo.='</a></th>';
				$i_aux++ ;
			}
			else {
				$conteudo .='		<th><nobr>'.$colunas[$i].'</nobr></th>';
			}
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</thead>';	

		$conteudo .='<tfoot>';
		$conteudo .='	<tr align="center">';
		
		$i_aux = 1;
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			if ($i <> 4) {
				$conteudo .='		<th><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.$i_aux.'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
				if ($ordem == $i_aux && $crescente == 'ASC')
					$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
				elseif ($ordem == $i_aux && $crescente == 'DESC')
					$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
				else $conteudo .= $colunas[$i];
				$conteudo.='</a></th>';
				$i_aux++ ;
			}
			else {
				$conteudo .='		<th><nobr>'.$colunas[$i].'</nobr></th>';
			}
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</tfoot>';		

		$conteudo .='<tbody>';
		
		unset($i);

		for ($i = 0; $i <= count($estabe)-1; $i++) {
			$conteudo .='<tr>';
			$conteudo .='    <td><nobr>'.utf8_encode($estabe[$i]).'</nobr></td>';
			$conteudo .='    <td><nobr>'.$dtini[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$dtfim[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$aband[$i].'</nobr></td>';			
			$conteudo .='    <td><nobr>'.$ultvenda[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.utf8_encode($cidade[$i]).'</nobr></td>';
			$conteudo .='    <td><nobr>'.$uf[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$nvenda[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$valorvendas[$i].'</nobr></td>';
			//$conteudo .='    <td><nobr>'.$percvalor[$i].'</nobr></td>';
			$conteudo .=' </tr>';
		}
	
		$conteudo .='</tbody>';
        
        return $conteudo;
		unset($conteudo);
	}	



	public function gera_grid_ultima_semana($colunas,$estabelecimento,$pos,$tipo,$cidade,$estado,$nvenda,$valvenda,$valorperc,$crescente=null,$cols=null,$ordem=null) {
		$conteudo .='<thead>';
		$conteudo .='	<tr align="center">';
		
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			$conteudo .='		<th><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.($i+1).'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
			if ($ordem == ($i+1) && $crescente == 'ASC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
			elseif ($ordem == ($i+1) && $crescente == 'DESC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
			else $conteudo .= $colunas[$i];
			$conteudo.='</a></th>';
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</thead>';	

		$conteudo .='<tfoot>';
		$conteudo .='	<tr align="center">';
		
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			$conteudo .='		<th><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.($i+1).'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
			if ($ordem == ($i+1) && $crescente == 'ASC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
			elseif ($ordem == ($i+1) && $crescente == 'DESC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
			else $conteudo .= $colunas[$i];
			$conteudo.='</a></th>';
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</tfoot>';		

		$conteudo .='<tbody>';
		
		unset($i);

		for ($i = 0; $i <= count($estabelecimento)-1; $i++) {
			$conteudo .='<tr>';
			$conteudo .='    <td><nobr>'.utf8_encode($estabelecimento[$i]).'</nobr></td>';
			$conteudo .='    <td><nobr>'.utf8_encode($tipo[$i]).'</nobr></td>';
			$conteudo .='    <td><nobr>'.utf8_encode($cidade[$i]).'</nobr></td>';
			$conteudo .='    <td><nobr>'.$estado[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$nvenda[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$valvenda[$i].'</nobr></td>';
			//$conteudo .='    <td><nobr>'.$valorperc[$i].'</nobr></td>';
			$conteudo .=' </tr>';
		}
	
		$conteudo .='</tbody>';
        
        return $conteudo;
		unset($conteudo);
	}	
	
	public function gera_grid_ultimo_mes($colunas,$estabelecimento,$pos,$tipo,$cidade,$estado,$nvenda,$valvenda,$valorperc,$crescente=null,$cols=null,$ordem=null) {
		$conteudo .='<thead>';
		$conteudo .='	<tr align="center">';
		
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			$conteudo .='		<th><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.($i+1).'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
			if ($ordem == ($i+1) && $crescente == 'ASC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
			elseif ($ordem == ($i+1) && $crescente == 'DESC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
			else $conteudo .= $colunas[$i];
			$conteudo.='</a></th>';
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</thead>';	

		$conteudo .='<tfoot>';
		$conteudo .='	<tr align="center">';
		
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			$conteudo .='		<th><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.($i+1).'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
			if ($ordem == ($i+1) && $crescente == 'ASC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
			elseif ($ordem == ($i+1) && $crescente == 'DESC')
				$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
			else $conteudo .= $colunas[$i];
			$conteudo.='</a></th>';
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</tfoot>';		

		$conteudo .='<tbody>';
		
		unset($i);

		for ($i = 0; $i <= count($estabelecimento)-1; $i++) {
			$conteudo .='<tr>';
			$conteudo .='    <td><nobr>'.utf8_encode($estabelecimento[$i]).'</nobr></td>';
			$conteudo .='    <td><nobr>'.utf8_encode($tipo[$i]).'</nobr></td>';
			$conteudo .='    <td><nobr>'.utf8_encode($cidade[$i]).'</nobr></td>';
			$conteudo .='    <td><nobr>'.$estado[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$nvenda[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$valvenda[$i].'</nobr></td>';
			//$conteudo .='    <td><nobr>'.$valorperc[$i].'</nobr></td>';
			$conteudo .=' </tr>';
		}
	
		$conteudo .='</tbody>';
        
        return $conteudo;
		unset($conteudo);
	}		

	public function gera_grid_Total_Usuario($colunas,$user,$dtini,$dtfim,$aband,$priultven,$cidade,$estado,$nven,$vven,$vper,$crescente=null,$cols=null,$ordem=null) {
		$conteudo .='<thead>';
		$conteudo .='	<tr align="center">';
		
		$i_aux = 1;
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			if ($i <> 4) {
				$conteudo .='		<th><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.$i_aux.'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
				if ($ordem == $i_aux && $crescente == 'ASC')
					$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
				elseif ($ordem == $i_aux && $crescente == 'DESC')
					$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
				else $conteudo .= $colunas[$i];
				$conteudo.='</a></th>';
				$i_aux++ ;
			}
			else {
				$conteudo .='		<th><nobr>'.$colunas[$i].'</nobr></th>';
			}
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</thead>';	

		$conteudo .='<tfoot>';
		$conteudo .='	<tr align="center">';
		
		$i_aux = 1;
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			if ($i <> 4) {
				$conteudo .='		<th><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.$i_aux.'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
				if ($ordem == $i_aux && $crescente == 'ASC')
					$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
				elseif ($ordem == $i_aux && $crescente == 'DESC')
					$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
				else $conteudo .= $colunas[$i];
				$conteudo.='</a></th>';
				$i_aux++ ;
			}
			else {
				$conteudo .='		<th><nobr>'.$colunas[$i].'</nobr></th>';
			}
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</tfoot>';		

		$conteudo .='<tbody>';
		
		unset($i);

		for ($i = 0; $i <= count($user)-1; $i++) {
			$conteudo .='<tr>';
			$conteudo .='    <td><nobr>'.utf8_encode($user[$i]).'</nobr></td>';
   			$conteudo .='    <td><nobr>'.$dtini[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$dtfim[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$aband[$i].'</nobr></td>';			
			$conteudo .='    <td><nobr>'.$priultven[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.utf8_encode($cidade[$i]).'</nobr></td>';
			$conteudo .='    <td><nobr>'.$estado[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$nven[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$vven[$i].'</nobr></td>';
			//$conteudo .='    <td><nobr>'.$vper[$i].'</nobr></td>';
			$conteudo .=' </tr>';
		}
	
		$conteudo .='</tbody>';
        
        return $conteudo;
		unset($conteudo);
	}

	public function gera_grid_lans($colunas,$lanhouse,$priulven,$cidade,$uf,$nven,$vven,$vper,$crescente=null,$cols=null,$ordem=null) {
		$conteudo .='<thead>';
		$conteudo .='	<tr align="center">';
		
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			if ($i <> 1) {
				$conteudo .='		<th><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.($i+1).'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
				if ($ordem == ($i+1) && $crescente == 'ASC')
					$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
				elseif ($ordem == ($i+1) && $crescente == 'DESC')
					$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
				else $conteudo .= $colunas[$i];
				$conteudo.='</a></th>';
			}
			else {
				$conteudo .='		<th><nobr>'.$colunas[$i].'</nobr></th>';
			}
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</thead>';	

		$conteudo .='<tfoot>';
		$conteudo .='	<tr align="center">';
		
		for ($i = 0; $i <= count($colunas)-1; $i++) {
			if ($i <> 1) {
				$conteudo .='		<th><a href="/sys/admin/stats/abas.php?script='.basename($_SERVER['PHP_SELF']).'&ordem='.($i+1).'&abanomeAux='.urlencode($cols).'&crescente='.$crescente.'">';
				if ($ordem == ($i+1) && $crescente == 'ASC')
					$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_down.gif" width="10" height="7" border="0" alt="Crescente"></div>';
				elseif ($ordem == ($i+1) && $crescente == 'DESC')
					$conteudo .= "<div style='color: red;font-size: 13px;font-weight: bold'>".$colunas[$i].' <img src="/sys/imagens/seta_up.gif" width="10" height="7" border="0" alt="Decrescente"></div>';
				else $conteudo .= $colunas[$i];
				$conteudo.='</a></th>';
			}
			else {
				$conteudo .='		<th><nobr>'.$colunas[$i].'</nobr></th>';
			}
    	}
		            
		$conteudo .='	</tr>';
		$conteudo .='</tfoot>';		

		$conteudo .='<tbody>';
		
		unset($i);

		for ($i = 0; $i <= count($lanhouse)-1; $i++) {
			$conteudo .='<tr>';
			$conteudo .='    <td><nobr>'.utf8_encode($lanhouse[$i]).'</nobr></td>';
			$conteudo .='    <td><nobr>'.$priulven[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.utf8_encode($cidade[$i]).'</nobr></td>';
			$conteudo .='    <td><nobr>'.$uf[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$nven[$i].'</nobr></td>';
			$conteudo .='    <td><nobr>'.$vven[$i].'</nobr></td>';
			//$conteudo .='    <td><nobr>'.$vper[$i].'</nobr></td>';
			$conteudo .=' </tr>';
		}
	
		$conteudo .='</tbody>';
        
        return $conteudo;
		unset($conteudo);
	}						
// Fim
}
?>