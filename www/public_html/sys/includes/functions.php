<?php
// include do arquivo contendo IPs DEV
require_once $raiz_do_projeto.'includes/configIP.php';

// include do classe de controle de servidor de envio de email
require_once $raiz_do_projeto.'class/util/EmailEnvironment.class.php';

	function is_moeda($val){

		if(strlen($val) < 4) return false;
		if(strrpos($val, ",") != strlen($val) - 3) return false;
		if(!is_numeric(substr($val, 0, 1))) return false;
//		if(substr($val, 0, 1) == "0") return false;

		$val = str_replace('.','',$val);
		$val = str_replace(',','.',$val);
		
		return is_numeric($val);
	}

	function is_hora($val){
	
		$pattern = "/([0-1][0-9]|2[0-3]):([0-5][0-9])/";
		return preg_match($pattern, $val);
	}

    function is_DateTime($dateTime) { 

        // Remove whitespace 
        $dateTime = trim($dateTime); 
         
        if(preg_match("'^(\d{2})[\-//](\d{2})[\-//](\d{4})\s(\d{2}):(\d{2})$'", $dateTime,  $matches)) { 
        	return checkdate($matches[2], $matches[1], $matches[3]) && is_hora($matches[4] . ":" . $matches[5]);

        } else { 
            return false; 
        } 
    } 

    function is_DateTimeEx($dateTime, $tipo) { 
		//Tipo 1: DDMMAAAAHH:MM
		//Tipo 2: AAAAMMDDHHMMSS
		//Tipo 3: AAAAMMDD

		if($tipo == 3){
			$dateTime .= "000000";
			$tipo = 2;
		}

		if($tipo == 1) $pattern = "'^(\d{2})[\-//](\d{2})[\-//](\d{4})\s(\d{2}):(\d{2})$'";
		else if($tipo == 2) $pattern = "'^(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})$'";
		else return false;
		
        // Remove whitespace 
        $dateTime = trim($dateTime); 
         
        if(preg_match($pattern, $dateTime,  $matches)) { 
        	if($tipo == 1) return checkdate($matches[2], $matches[1], $matches[3]) && is_hora($matches[4] . ":" . $matches[5]);
        	else if($tipo == 2) return checkdate($matches[2], $matches[3], $matches[1]) && is_hora($matches[4] . ":" . $matches[5]);

        } else { 
            return false; 
        } 
    } 

    function SQLaddFields($var, $tipo){
    	
    	if(is_null($var)) return "NULL";
    	elseif($tipo == "r") return str_replace("'", "''", $var);
    	elseif($tipo == "s") return "'" . str_replace("'", "''", $var) . "'";
    	else return $var;
    	
    }
	
	//Mater esta variavel com este valor
	//Coloca-la com true nas paginas que for debugar apos a chamada deste include
	$varBlDebug = false; 
//	$varBlDebug = true; 
        
    if(!function_exists("SQLexecuteQuery")){
        function SQLexecuteQuery($sql){

                $lev = error_reporting (8); //NO WARRING!!
                if($GLOBALS['varBlDebug']){
                        echo "<br>" . $sql . "<br>";
                        if(substr($sql, 0, 6) == "select")	$ret = pg_query ($GLOBALS['connid'], $sql);
                        else $ret = 1;
                } else {
                        $ret = pg_query ($GLOBALS['connid'], $sql);
                }

                error_reporting ($lev); //DEFAULT!!

                if (strlen ($erro = pg_last_error($GLOBALS['connid']))) {
                $message  = date("Y-m-d H:i:s") . " ";
                $message .= "Erro: " . $erro . "<br>\n";
                $message .= "Query: " . $sql . "<br>\n";
                gravaLog_SQLexecuteQuery($message);
                //die($message);
            }
                //gravaLog_SQLexecuteQuery($sql);

                return $ret;		
        }
    }

    function getValue($sql) {

		$ret = null;
		 
//echo "<!-- sql: $sql\n -->";
		$rs = SQLexecuteQuery($sql);
		if($rs && pg_num_rows($rs) > 0){
			$rs_row = pg_fetch_array($rs);
			 $ret = $rs_row[0];
		}			
//echo "<!-- resultado (getValue): " & $ret & "\n-->";
			
 		return $ret;   	
    }
	
	function gravaLog_SQLexecuteQuery($mensagem){
	
		//Arquivo
//		$file = $GLOBALS['ARQUIVO_LOG_SQL_EXECUTE_QUERY'];
		$file = $GLOBALS['raiz_do_projeto'] . "log/log_sql_execute_query.txt";
	
		//Mensagem
		$mensagem = date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . "\n" . $mensagem . "\n";
	
		//Grava mensagem no arquivo
		if ($handle = fopen($file, 'a+')) {
			fwrite($handle, $mensagem);
			fclose($handle);
		} 
	
	}

/*
DESCRIÇÃO:
Função que faz a paginação de uma query

ENTRADA:
$inicial: Indica em qual linha da query vai começar a mostrar
$total_table: Quantidade de registros que a query retornou
$max: Quantidade de registros por página na tela
$qtde_colunas: Quantidade de colunas que tem a linha da tabela do HTML
$img_anterior: Caminho da imagem Anterior
$img_proxima: Caminho da imagem Próxima
$default_add: Nome do arquivo da página
$range: Número do range inicial
$range_qtde: Quantidade de paginas mostradas na paginação
$ncamp: Campo que está ordenando o SQL
$varsel: Variáveis de pesquisa

SAÍDA:
É retornado a paginação na página em que a função foi chamada

*/
function paginacao_query($inicial, $total_table, $max, $qtde_colunas, $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel)
{

//if(b_IsUsuarioReinaldo()) { 
//echo "#<pre>".print_r($GLOBALS['_REQUEST'], true)."</pre>#";
//die("Stop");
//}


//echo "varselD: ".$varsel."<br>";  

// ESTE È O PAGINATION UTILIZADO EM SYS/ADMIN E ATUALIZADO
//echo "inicial: $inicial, total_table: $total_table,  max: $max<hr>";
	if($total_table > $max) // Só mostra a numeração das páginas se a quantidade de registros for maior do que o máximo permitido na tela
	{
		echo "<tr><td colspan='".$qtde_colunas."'>&nbsp;</td></tr>";
		echo "<tr><td colspan='".$qtde_colunas."' align='center'>";
//echo "inicial: $inicial, total_table: $total_table, max: $max, qtde_colunas: $qtde_colunas, range: $range, range_qtde: $range_qtde, ncamp: $ncamp<br>";
//echo "range:".$range."<br>";
	
		if($range != 1) // Mostra a imagem de Anterior
		{
			$prev_range = $range - ($range_qtde - 1) - 1;
			$page_prev = (($prev_range * $max) - ($max - 1)) - 1;
			echo "<a href=".$default_add."?inicial=".$page_prev."&range=".$prev_range."&ncamp=".$ncamp.$varsel."><img src=".$img_anterior." border='0' align='absmiddle'></a>"; 			
		}

		$qtde_pg = ceil($total_table / $max); // Quantidade de páginas que a query vai ter
		
		if(($range + ($range_qtde - 1)) > $qtde_pg) // Retorna a quantidade de páginas de um range
			$limite = $qtde_pg;
		else
			$limite = ($range + $range_qtde - 1);

		for($s = $range ; $s <= $limite ; $s++) // Monta a paginação propriamente dita
		{
			$esta_pag = ceil($inicial/$max)+1;
			$indice = (($s * $max) - ($max - 1)) - 1;
			if($esta_pag!=$s) echo "<a href=".$default_add."?inicial=".$indice."&range=".$range."&ncamp=".$ncamp.$varsel." class='link_azul'><font color='#00008C' size='1' face='Arial, Helvetica, sans-serif'>"; else echo "<b>";
			echo (($esta_pag!=$s)?"":"<span style='background-color:#FFFF00'>");
			echo "<font color='".(($esta_pag!=$s)?"#00008C":"#330000")."' size='1' face='Arial, Helvetica, sans-serif'>".$s."</font>";
			echo (($esta_pag!=$s)?"":"</span>");
			if($esta_pag!=$s) echo "</a>"; else echo "</b>";
			if($s != $limite)
				echo "<font color='#00008C' size='1' face='Arial, Helvetica, sans-serif'> | </font>";
		}
		
		if($limite < $qtde_pg) // Mostra a imagem de Próximo
		{
			$next_range = $range + ($range_qtde - 1) + 1;
			$page_next = (($next_range * $max) - ($max - 1)) - 1;
			echo "<a href=".$default_add."?inicial=".$page_next.$varsel."&range=".$next_range."&ncamp=".$ncamp.$varsel."><img src=".$img_proxima." border='0' align='absmiddle'></a>";
		}
		echo "</td></tr>";
	}
}

function qtde_dias($data1, $data2)
{
/*
    $data1="01-01-2004"; // suponhamos que consultou estas duas datas no banco de dados ou outro lugar qualquer
    $data2="31-01-2004";
    echo "Data inicial: $data1";
    echo "<br>";
    echo "Data final: $data2";
    echo "<br><br>";
*/    
    // manipula data1
    $dia1=substr($data1,0,-8); // extraimos somete o dia inicial
    $mes1=substr($data1,3,-5); // extraimos somete o mes inicial
    $ano1=substr($data1,6);    // extraimos somete o ano inicial

    // manipula data2
    $dia2=substr($data2,0,-8); // extraimos somete o dia final
    $mes2=substr($data2,3,-5); // extraimos somete o mes final
    $ano2=substr($data2,6);    // extraimos somete o ano final


	$data_inicial=mktime(0,0,0,$mes1, $dia1, $ano1); // obtem tempo unix para data1 no formato timestamp
	$data_final=mktime(0,0,0,$mes2, $dia2, $ano2); // obtem tempo unix para data2 no formato timestamp
	$tempo_unix=$data_final - $data_inicial; // acha a diferença de tempo
	$periodo=floor(0.5 +$tempo_unix /(24*60*60)); //conversão para dias. (Para anos adicione *365)
//echo "'".date("Y-m-d H:i:s", $data_final)."' - '".date("Y-m-d h:i:s", $data_inicial)."': ".$tempo_unix." -> periodo: $periodo<br>";	
	if($periodo >= 0)
		return $periodo;
	else
		return -1;
}

function trace_sql($sql, $face, $size, $color, $type)
{
//	if(($face != "Arial" || $face != "Helvetica" || $face != "Sans-serif") || 
//	   ($size < 1 || $size > 3) || (substr($color, 0, 1) != "#" || strlen($color) != 7)) ||
//	   ($type != "b" || $type != "i" || $type != ''))
	if( ($face != "Arial" && $face != "Helvetica" && $face != "Sans-serif") || 
	    ($size < 1 || $size > 3) || ((substr($color, 0, 1) != "#" || strlen($color) != 7)) ||
		($type != "b" && $type != "i" && $type != '') )
	{
		echo "<font face='Arial, Helvetica, sans-serif' size='2' color='#FF0000'><b>Formatação inválida!</b></font>";
	}
	else
	{
		$sql_format = "";
		$sql_font = "";
		
		for($i = 0 ; $i <= strlen($sql) ; $i++)
		{
			$aux = substr($sql, $i, 4);
			if($aux == "from" || $aux == "FROM" || $aux == "From")
			{
				$sql_select = substr($sql, 0, $i - 1) . "<br><br>";
				$sql_rest = substr($sql, $i, strlen($sql) - $i) . "<br><br>";
				break;
			}
		}
	
		$sql_format .= $sql_select;
	
		for($i = 0 ; $i <= strlen($sql_rest) ; $i++)
		{
			$aux = substr($sql_rest, $i, 5);
			if($aux == "where" || $aux == "WHERE" || $aux == "Where")
			{
				$sql_from = substr($sql_rest, 0, $i - 1) . "<br><br>";
				$sql_where = substr($sql_rest, $i, strlen($sql_rest) - $i) . "<br>";
				break;
			}
		}
	
		$sql_format .= $sql_from . $sql_where;
		
		$sql_font .= "<font face='".$face."' size='".$size."' color='".$color."'>";
		if($type == '')
			$sql_font .= $sql_format."</font>";
		else
			$sql_font .= "<".$type.">".$sql_format."</".$type."></font>";
			
		echo $sql_font;
	}
}

function getmicrotime()
{
   list($usec, $sec) = explode(" ", microtime());
   return ((float)$usec + (float)$sec);
}


function verificaCNPJ($string)
{
	$RecebeCNPJ = $string;
	
	if(strlen($RecebeCNPJ) != 14 || $RecebeCNPJ == "00000000000000")
		return 0;
	else
	{
		for($i = 1 ; $i <= 14 ; $i++)
			$Numero[$i] = intval(substr($RecebeCNPJ, $i - 1, 1));		
		
		$soma = 0;
		for($i = 1 ; $i <= 12 ; $i++)
		{
			if($i == 1) $j = 5;

			$soma += $Numero[$i] * $j;
			$j--;

			if($j == 1) $j = 9;
		}
		
		$soma = $soma - (11 * (intval($soma / 11)));
		
		if($soma == 0 || $soma == 1)
			$resultado1 = 0;
		else
			$resultado1 = 11 - $soma;
	
		if($resultado1 == $Numero[13])
		{

			$soma = 0;
			for($i = 1 ; $i <= 13 ; $i++)
			{
				if($i == 1) $j = 6;
	
				$soma += $Numero[$i] * $j;
				$j--;
	
				if($j == 1) $j = 9;
			}

			$soma = $soma - (11 * (intval($soma / 11)));

			if($soma == 0 || $soma==1)
				$resultado2 = 0;
			else
				$resultado2 = 11 - $soma;

			if ($resultado2 == $Numero[14])
				return 1;
			else
				return 0;
		}
		else
			return 0;
	}
}

/*
function verificaCNPJ($cnpj) 
{
	  	    if (strlen($cnpj) <> 14) return 0;
  		    if ($cnpj[0] == $cnpj[1]  && $cnpj[2] ==$cnpj[3]) return 0; 
					$soma1 = ($cnpj[0] * 5) +
					($cnpj[1] * 4)  + ($cnpj[2] * 3)  + ($cnpj[3] * 2) +
					($cnpj[4] * 9)  + ($cnpj[5] * 8)  + ($cnpj[6] * 7) +
					($cnpj[7] * 6)  + ($cnpj[8] * 5) + ($cnpj[9] * 4) +
					($cnpj[10] * 3) + ($cnpj[11] * 2);
					$resto = $soma1 % 11;
					$digito1 = $resto < 2 ? 0 : 11 - $resto;
					$soma2 = ($cnpj[0] * 6) +
					($cnpj[1] * 5) +  ($cnpj[2] * 4)  + ($cnpj[3] * 3) +
					($cnpj[4] * 2) +  ($cnpj[5] * 9)  + ($cnpj[6] * 8) +
					($cnpj[7] * 7) +  ($cnpj[8] * 6) + ($cnpj[9] * 5) +
					($cnpj[10] * 4) + ($cnpj[11] * 3) +	($cnpj[12] * 2);
					$resto = $soma2 % 11;
				    $digito2 = $resto < 2 ? 0 : 11 - $resto;
			return (($cnpj[12] == $digito1) && ($cnpj[13] == $digito2));
}
*/

function verificaCPF($cpf)
{
	$RecebeCPF=$cpf;
		if (strlen($RecebeCPF)!=11)
		{ return 0; }
		else
		if ($RecebeCPF=="00000000000")
		{ return 0; }
		else
		{
			$Numero[1]=intval(substr($RecebeCPF,1-1,1));
			$Numero[2]=intval(substr($RecebeCPF,2-1,1));
			$Numero[3]=intval(substr($RecebeCPF,3-1,1));
			$Numero[4]=intval(substr($RecebeCPF,4-1,1));
			$Numero[5]=intval(substr($RecebeCPF,5-1,1));
			$Numero[6]=intval(substr($RecebeCPF,6-1,1));
			$Numero[7]=intval(substr($RecebeCPF,7-1,1));
			$Numero[8]=intval(substr($RecebeCPF,8-1,1));
			$Numero[9]=intval(substr($RecebeCPF,9-1,1));
			$Numero[10]=intval(substr($RecebeCPF,10-1,1));
			$Numero[11]=intval(substr($RecebeCPF,11-1,1));
			
			$soma=10*$Numero[1]+9*$Numero[2]+8*$Numero[3]+7*$Numero[4]+6*$Numero[5]+5*
			$Numero[6]+4*$Numero[7]+3*$Numero[8]+2*$Numero[9];
			$soma=$soma-(11*(intval($soma/11)));
			
			if ($soma==0 || $soma==1)
			{ $resultado1=0; }
			else
			{ $resultado1=11-$soma; }
		
			if ($resultado1==$Numero[10])
			{
				$soma=$Numero[1]*11+$Numero[2]*10+$Numero[3]*9+$Numero[4]*8+$Numero[5]*7+$Numero[6]*6+$Numero[7]*5+
				$Numero[8]*4+$Numero[9]*3+$Numero[10]*2;
				$soma=$soma-(11*(intval($soma/11)));
			
				if ($soma==0 || $soma==1)
				{ $resultado2=0; }
				else
				{ $resultado2=11-$soma; }
				if ($resultado2==$Numero[11])
				{ return TRUE;}
				else
				{ return 0; }
			}
			else
			{ return 0; }
	 }
}

function mascara_cnpj_cpf($documento ,$tipo)
{
	$mask = $documento;
	if($tipo == 'cnpj')
	{
		$var1 = substr($mask, 0,2);
		$var2 = substr($mask, 2,3);  
		$var3 = substr($mask, 5,3);  
		$var4 = substr($mask, 8,4);      
		$var5 = substr($mask, 12,2);  
		$doc = $var1.".".$var2.".".$var3."/".$var4."-".$var5;
	}
	
	if($tipo == 'cpf')
	{
		$var1 = substr($mask, 0,3);
		$var2 = substr($mask, 3,3);  
		$var3 = substr($mask, 6,3);  
		$var4 = substr($mask, 9,2);      
		$doc = $var1.".".$var2.".".$var3."-".$var4;
	}
	return $doc;
}

function coloca_char_esquerda($string, $qtde_total, $char)
{
	$aux = $string;
	
	for($i = 1 ; $i <= ($qtde_total - strlen($aux)) ; $i ++)
		$completa .= $char;
		
	$completa .= $aux;
	return $completa;
}

function coloca_char_direita($string, $qtde_total, $char)
{
	$aux = $string;
	
	for($i = 1 ; $i <= ($qtde_total - strlen($aux)) ; $i ++)
	{ $completa .= $char; }
	$aux .= $completa;
	return $aux;
}

function nome_arquivo($url)
{
    $separada = explode("/", $url);
    $reverso = array_reverse($separada);
	
	return $reverso[0];
}

function gera_string($inicio, $tamanho_rand, $tipo)
{
	if($tipo == 'A')
		$string = "A1B2C3D4E5F6G7H8I9J0K1L2M3N4O5P6Q7R8S9T0U1V2W3X4Y5Z6";
	
	if($tipo == 'N')
		$string = "1234567890";

	$senha = $inicio;
	
		for($x = 1 ; $x <= $tamanho_rand ; $x++)
		{
			$rand = rand(0,(strlen($string)-1));
			$pos = substr($string,$rand,1);
			$string_formatada .= $pos;
		}	
		$senha .= $string_formatada;	

	return $senha;
}

function formata_data($data,$gravar)
{
	$mask = $data;
	if($gravar == 0)
	{
		$dia = substr($mask,8,2);
		$mes = substr($mask,5,2);
		$ano = substr($mask,0,4);
		//Verifica qual linguagem para formatar a data.
		if (isset($_SESSION['lang'])){
			if($_SESSION['lang'] == 'en'){
				$doc = $mes."/".$dia."/".$ano;
			}else{
		$doc = $dia."/".$mes."/".$ano;
	}
		}else{
			$doc = $dia."/".$mes."/".$ano;
		}
	}
	
	if($gravar == 1)
	{
		$dia = substr($mask,0,2);
		$mes = substr($mask,3,2);
		$ano = substr($mask,6,4);
		$doc = $ano."-".$mes."-".$dia;
	}

	//entra AAAA-MM-DD
	//retorna DDMMAA
	if($gravar == 2)
	{
		$dia = substr($mask,8,2);
		$mes = substr($mask,5,2);
		$ano = substr($mask,2,2);
		$doc = $dia.$mes.$ano;
	}

	return $doc;
}

function verifica_valor_moeda($val)
{
	$valor = $val;
	$tam = strlen($valor);
	
	$virgula = substr($valor,$tam-3,1);
	if($virgula != "," && $virgula != ".")
		return 0;
	else
	{
		$antes = substr($valor,0,$tam-4);
		$depois = substr($valor,$tam-2,2);
		
		if(!valida_campo_numero($antes))
			return 0;
		else
		{ 
			if(!valida_campo_numero($depois))
				return 0;
			else
				return 1;
		}		
	}
}

function verifica_valor_moeda_neg($val)
{
	$valor = $val;
	$tam = strlen($valor);
	
	$virgula = substr($valor,$tam-3,1);
	if($virgula != "," && $virgula != ".")
		return 0;
	else
	{
		$neg = substr($valor,0,1);
		$antes = substr($valor,1,$tam-4);
		$depois = substr($valor,$tam-2,2);
		
		if(!valida_campo_numero($neg) && $neg != '-')
		{
			return 0;
		}
		else
		{
			if(!valida_campo_numero($antes))
				return 0;
			else
			{ 
				if(!valida_campo_numero($depois))
					return 0;
				else
					return 1;
			}
		}

	}
}


function verifica_valor($val)
{
	$valor = $val;
	$tam = strlen($valor);
	
	$virgula = substr($valor,$tam-3,1);
	if($virgula != "," && $virgula != ".")
	{ return 0; }
	else
	{
		$antes = substr($valor,0,$tam-4);		
		$depois = substr($valor,$tam-2,2);
		
		if(!valida_campo_numero($antes))
		{ return 0; }
		else
		{ 
			if(!valida_campo_numero($depois))
			{ return 0; }
			else
			{ return 1; }
		}		
	}
}

function valida_campo_numero($val)
{
	$verifica = $val;
	for ($y = 1 ; $y <= strlen($verifica) ; $y += 1)
	{ 
		$ch = substr($verifica,$y-1,1); 
		if(ord($ch) >= 48 && ord($ch) <= 57)
			{ $alerta = 0; }
		else
			{ $alerta = 1; break; }
	}
	if($alerta == 0)
		{ return 1; }
	else
		{ return 0; }
}

function valida_campo_numero_e_char($val, $char)
{
	$verifica = $val;
	for ($y = 1 ; $y <= strlen($verifica) ; $y += 1)
	{ 
		$ch = substr($verifica,$y-1,1); 
		if((ord($ch) >= 48 && ord($ch) <= 57) ||($ch == $char))
			{ $alerta = 0; }
		else
			{ $alerta = 1; break; }
	}
	if($alerta == 0)
		{ return 1; }
	else
		{ return 0; }
}


function resgata_string($string, $pos)
{
	$aux = $string;
	$fim = substr($aux, strlen($aux) - ($pos - 1), $pos - 1);
	$inicio = substr($aux, 0, strlen($aux) - ($pos - 1));
	
	$fone_formatado = $inicio."-".$fim;
	return $fone_formatado;
}

function verifica_email($email)
{
	$aux = $email;
	$first = substr($aux,0,1);
	$last = substr($aux,strlen($aux)-1,1);
		if(ord($first) == 64 || ord($last) == 64)
		{ return 0; }
		else
		{
			$contador = 0;	
			for ($x = 1 ; $x <= strlen($aux) ; $x++)
			{
				$pos = substr($aux,$x-1,1);
				if(ord($pos) == 64)
				{ $alerta = 1; break; }
				else
				{ $alerta = 0; }
				$contador++;
			}
																							
				if($alerta == 0)
				{ return 0; }
				else
				{
					$ponto = substr($aux,$contador,1); 
					if(ord($ponto) == 46)
					{ return 0; }
					else
					{ return 1; }
				}
		}
}

function data_mais_um($data)
{
	$dia = substr($data, 0, 2);
	$mes = substr($data, 3, 2);
	$ano = substr($data, 6, 4);
	
	if ((($ano % 4) == 0 and ($ano % 100) != 0) or ($ano % 400) == 0)
		{ $bissexto = 1; } // Fevererio tem 29 dias
	else 
		{ $bissexto = 0; } // Fevererio tem 28 dias
		
	if($mes == 2 && $bissexto == 1) { $mes_num = 29; }
	if($mes == 2 && $bissexto == 0) { $mes_num = 28; }
	if($mes == 4 || $mes == 6 || $mes == 9 || $mes == 11) { $mes_num = 30; }
	if($mes == 1 || $mes == 3 || $mes == 5  || $mes == 7 || $mes == 8 || $mes == 10 || $mes == 12) { $mes_num = 31; }
	
	if($mes_num == 30) // Abril - Junho - Setembro - Novembro 
	{
		if($dia <= 29)
		{ $dia++; }
		else
		{
			$dia = 1;
			if($mes != 12)
				{ $mes++; }
			else
				{ $mes = 1;	$ano++;	}
		}
	}
	
	if($mes_num == 31) // Janeiro - Março - Maio - Julho - Agosto - Outubro - Dezembro 
	{
		if($dia <= 30)
		{ $dia++; }
		else
		{
			$dia = 1;
			if($mes != 12)
				{ $mes++; }
			else
				{ $mes = 1;	$ano++;	}
		}
	}

	if($mes_num == 29) // Fevereiro - Bissexto(29 dias)
	{
		if($dia <= 28)
		{ $dia++; }
		else
		{ $dia = 1;	$mes = 3; }
	}

	if($mes_num == 28) // Fevereiro - (28 dias)
	{
		if($dia <= 27)
		{ $dia++; }
		else
		{ $dia = 1;	$mes = 3; }
	}
	
	if(strlen($dia) < 2) { $dia = '0'.$dia; }
	if(strlen($mes) < 2) { $mes = '0'.$mes; }
	
	$result_date = $dia."/".$mes."/".$ano; 
	return $result_date;

}

function data_mais_n($data, $qtde_dias)
{
	$aux = $data;
	for($i = 1 ; $i <= $qtde_dias ; $i++)
	{
		$aux = data_mais_um($aux);
	}
	$data_somada = $aux;
	return $data_somada;
}

function data_menos_um($data)
{
	$dia = substr($data, 0, 2);
	$mes = substr($data, 3, 2);
	$ano = substr($data, 6, 4);
	
	if ((($ano % 4) == 0 and ($ano % 100) != 0) or ($ano % 400) == 0)
		{ $bissexto = 1; } // Fevererio tem 29 dias
	else 
		{ $bissexto = 0; } // Fevererio tem 28 dias
		
	if($dia == 1)
	{
		if($mes == 1)
			$mes_anterior = 12;
		else
			$mes_anterior = $mes - 1;
		
		$mes_num = 0;
		if($mes_anterior == 2 && $bissexto == 1) { $mes_num = 29; }
		if($mes_anterior == 2 && $bissexto == 0) { $mes_num = 28; }
		if($mes_anterior == 4 || $mes_anterior == 6 || $mes_anterior == 9 || $mes_anterior == 11) { $mes_num = 30; }
		if($mes_anterior == 1 || $mes_anterior == 3 || $mes_anterior == 5 || $mes_anterior == 7 || $mes_anterior == 8 || $mes_anterior == 10 || $mes_anterior == 12) { $mes_num = 31; }
		
		if($mes_anterior == 12)	
			$ano--;

		$dia = $mes_num;
		$mes = $mes_anterior;
	}
	else
		$dia--;

	if(strlen($dia) < 2) { $dia = '0'.$dia; }
	if(strlen($mes) < 2) { $mes = '0'.$mes; }
	
	$result_date = $dia."/".$mes."/".$ano; 
	return $result_date;

}

function data_menos_n($data, $qtde_dias)
{
	$aux = $data;
	for($i = 1 ; $i <= $qtde_dias ; $i++)
	{
		$aux = data_menos_um($aux);
	}
	$data_decrementada = $aux;
	return $data_decrementada;
}

function verifica_tel($tel)
{
	$aux = $tel;
	$tam = strlen($aux);
		if($tam < 8)
		{ return 0; }
		else
		{
			if($tam == 9)
			{
				$traco = substr($aux,4,1);
					if(ord($traco) != 45)
					{ return 0; }
					else
					{
						$inicial = substr($aux,0,4); 
						for ($x = 1 ; $x <= strlen($inicial) ; $x++)
						{
							$pos = substr($inicial,$x-1,1);
							if(ord($pos) >= 48 && ord($pos) <= 57)
							{ $alerta = 0; }
							else
							{ $alerta = 1; break;}
						}							
								if($alerta == 1) 
								{ return 0; }
								else
								{
									$final = substr($aux,5,4); 
									for ($x = 1 ; $x <= strlen($final) ; $x++)
									{
										$pos = substr($final,$x-1,1);
										if(ord($pos) >= 48 && ord($pos) <= 57)
										{ $alerta = 0; }
										else
										{ $alerta = 1; break;}							
									}
									
										if($alerta == 1) 
										{ return 0; }
										else	
										{ return 1; }															
								}	
																				
					}	
			}
				if($tam == 8)
				{
					$traco = substr($aux,3,1);
						if(ord($traco) != 45)
						{ return 0; }
						else
						{
							$inicial = substr($aux,0,3); 
							for ($x = 1 ; $x <= strlen($inicial) ; $x++)
							{
								$pos = substr($inicial,$x-1,1);
								if(ord($pos) >= 48 && ord($pos) <= 57)
								{ $alerta = 0; }
								else
								{ $alerta = 1; break;}
							}							
									if($alerta == 1) 
									{ return 0; }
									else
									{
										$final = substr($aux,4,4); 
										for ($x = 1 ; $x <= strlen($final) ; $x++)
										{
											$pos = substr($final,$x-1,1);
											if(ord($pos) >= 48 && ord($pos) <= 57)
											{ $alerta = 0; }
											else
											{ $alerta = 1; break;}							
										}
										
											if($alerta == 1) 
											{ return 0; }
											else	
											{ return 1; }															
									}	
																					
						}	
				}				
		}
}

function monta_ddd_string($campoddd)
{
	$recebeDDD = $campoddd;
	$recebeDDD .= ";";
    for ($x = 1 ; $x <= strlen($recebeDDD) ; $x += 1)
    {
       $ch = substr($recebeDDD,$x-1,1);
       if(ord($ch) >= 48 && ord($ch) <= 57)
	   { $alerta = 1; break; } // Se a string tiver número 
	   else 
	   { $alerta = 0; } // Se a string NÃO tiver número
	}
	
		if($alerta == 1) // Se a string tiver número
		{
		   $aux_tr = 0;
		   $aux_pv = 0;
		   $traco = "";
		   for ($y = 1 ; $y <= strlen($recebeDDD) ; $y += 1)
		   {
		   	  $cha = substr($recebeDDD,$y-1,1); 
		   	  if(ord($cha) == 45)   // Verifica se a string tem traço      
		      { 
				 $traco = $aux_tr; 
			     $pos = $traco-2;
				 $inicial = substr($recebeDDD,$pos,2);          // Indica o DDD inicial
			     $final = substr($recebeDDD,$traco+1,2);      // Indica o DDD final
				    for($s = $inicial ; $s <= $final ; $s++) {      // Monta a sequência dos DDD´s entre inicial e final
				       $var .= $s.";"; }
			  }
			  $aux_tr += 1;
		   }
		   for ($t = 1 ; $t <= strlen($recebeDDD) ; $t += 1)
		   {
		   	  $char = substr($recebeDDD,$t-1,1); 
		   	  if(ord($char) == 59)   // Verifica se a string tem ponto e vírgula      
		      {
			     $pv = $aux_pv;
				 $pos = $pv-3;
				 $tst = substr($recebeDDD,$pos,1);
				    if(ord($tst) == 59) 
					$grav = substr($recebeDDD,$pos+1,2); 
				    $var .= $grav.";";				 
			  }
			  $aux_pv += 1; 
		   }
		   $var = str_replace(";;;;",";",$var);
		   $var = str_replace(";;;",";",$var);
		   $var = str_replace(";;",";",$var);
		   return $var;
		}

/*		if($alerta == 0) // Se a string NÃO tiver número
		{
		   for($i =1, $j = 1 ; $i < 10 ; $j++)    
		   {                                                    
			  if($j > 9) { $i += 1; $j = 1; }        // Este laço monta uma string      
			  if($i == 10) { break; }                 // com todos os DDD's válidos 
			  $var .= $i.$j.";";                         // e joga em $var.   
		   }
		return $var;  
        }   
*/
}

function valida_ddd_string($ddd)
{
	$verifica = $ddd;
	for ($y = 1 ; $y <= strlen($verifica) ; $y += 1)
	{ 
		$ch = substr($verifica,$y-1,1); 
		if((ord($ch) >= 48 && ord($ch) <= 57) || (ord($ch) == 45) || (ord($ch) == 59))
			{ $alerta = 0; }
		else
			{ $alerta = 1; break; }
	}
	if($alerta == 0)
		{ return 1; }
	else
		{ return 0; }
}

function verifica_data($data)
{
	$aux = $data;
	$tam = strlen($aux);
		if($tam < 10)
		{ return 0; }
		else
		{
				$bar1 = substr($aux,2,1);
				$bar2 = substr($aux,5,1);
					if(ord($bar1) != 47 || ord($bar2) != 47)
					{ return 0; }
					else
					{
						$dia = substr($aux,0,2); 
						for ($x = 1 ; $x <= strlen($dia) ; $x++)
						{
							$pos = substr($dia,$x-1,1);
							if(ord($pos) >= 48 && ord($pos) <= 57)
							{ $alerta = 0; }
							else
							{ $alerta = 1; break;}
						}							
								if($alerta == 1) 
								{ return 0; }
								else
								{
									$mes = substr($aux,3,2); 
									for ($x = 1 ; $x <= strlen($mes) ; $x++)
									{
										$pos = substr($mes,$x-1,1);
										if(ord($pos) >= 48 && ord($pos) <= 57)
										{ $alerta = 0; }
										else
										{ $alerta = 1; break;}							
									}
										
										if($alerta == 1) 
										{ return  0; }
										else
										{
											$ano = substr($aux,6,4); 
											for ($x = 1 ; $x <= strlen($ano) ; $x++)
											{
												$pos = substr($ano,$x-1,1);
												if(ord($pos) >= 48 && ord($pos) <= 57)
												{ $alerta = 0; }
												else
												{ $alerta = 1; break;}							
											}
											
											if($alerta == 1) 
											{ return  0; }
											else	
											{ 
												if($mes > 12 || $dia > 31)
												{ return 0; }
												else
												{									
													if ((($ano % 4) == 0 and ($ano % 100) != 0) or ($ano % 400) == 0)
														{ $bissexto = 1; }
													else 
														{ $bissexto = 0; }
													
													if($bissexto == 0)
													{
														if(($mes == 4 || $mes == 6 || $mes == 9 || $mes == 11) && ($dia > 30))
														{ return 0; }
														else
														{
															if($mes == 2 && $dia > 28) 
															{ return 0; }
															else
															{ return 1; }														
														}
													}
													if($bissexto == 1)
													{
														if(($mes == 4 || $mes == 6 || $mes == 9 || $mes == 11) && ($dia > 30))
														{ return 0; }
														else
														{
															if($mes == 2 && $dia > 29) 
															{ return 0; }
															else
															{ return 1; }
														}
													}													
												}
											}											
										}															
								}																				
					}																		
		}			
}

function verifica_tel_ddd($tel)
{
	$aux = $tel;
	$tam = strlen($aux);
		if($tam < 11)
		{ return 0; }
		else
		{
			if($tam == 12)
			{
				$espaco = substr($aux,2,1);
					if(ord($espaco) != 32)
					{ return 0; }
					else
					{
						$traco = substr($aux,7,1);
							if(ord($traco) != 45)
							{ return 0; }
							else
							{
								$ddd = substr($aux,0,2);
								for ($x = 1 ; $x <= strlen($ddd) ; $x++)
								{
									$pos = substr($ddd,$x-1,1);
									if(ord($pos) >= 48 && ord($pos) <= 57)
									{ $alerta = 0; }
									else
									{ $alerta = 1; break;}
								}
								
									if($alerta == 1) 
									{ return 0; }
									else
									{
										$inicial = substr($aux,3,4); 
										for ($x = 1 ; $x <= strlen($inicial) ; $x++)
										{
											$pos = substr($inicial,$x-1,1);
											if(ord($pos) >= 48 && ord($pos) <= 57)
											{ $alerta = 0; }
											else
											{ $alerta = 1; break;}
										}
																							
											if($alerta == 1) 
											{ return 0; }
											else
											{
												$final = substr($aux,8,4); 
												for ($x = 1 ; $x <= strlen($final) ; $x++)
												{
													$pos = substr($final,$x-1,1);
													if(ord($pos) >= 48 && ord($pos) <= 57)
													{ $alerta = 0; }
													else
													{ $alerta = 1; break;}							
												}

													if($alerta == 1) 
														{ return 0; }
													else	
														{ return 1; }
												
											}// fechamnento do else do $inicial não é número								
									} // fechamnento do else do $ddd não é número
								} // fechamento do else do $traco																																
					} // fechamento do else do $espaço
			} // fechamento da condição ($tam == 12)
		} // fechamento do else do $tam

}

function Modulo10($string)
{
	$i16Tot = 0;
	$ui8Especial = TRUE;
	
	for($i = 7 ; $i >= 0 ; $i--)
	{
		if($ui8Especial)
		{
			$i16Aux = ($string[$i] * 2);
			$ui8Especial = FALSE;
		}
		else
		{
			$i16Aux = ($string[$i] * 1);
			$ui8Especial = TRUE;
		}
		
		if($i16Aux != 0)
		{
			if($i16Aux > 9)
			{
				$aux = $i16Aux / 10;
				$aux = floor($aux);
				$aux_mod = $i16Aux % 10;
				$aux_mod = floor($aux_mod);
				$i16Tot = ($aux + $aux_mod) + $i16Tot;
			}
			else
			{
				$i16Tot = $i16Aux + $i16Tot;
			}
		}
	}
	
	if($i16Tot % 10)
	{
		$aux = $i16Tot / 10;
		$aux = floor($aux);
		$i16Dig = (($aux + 1) * 10) - $i16Tot;
	}
	else
		$i16Dig = 0;
	
	$i16Dig = $i16Dig & 0x000F;
	return ($i16Dig);
}

function formata_string($string, $caracter, $pos)
{
	$aux_inic = substr($string, 0, $pos);
	$aux_resto = substr($string, $pos, strlen($string) - $pos);
	
	if(strlen($aux_resto) <= strlen(aux_inic))
		$aux_r = $aux_inic.$caracter.$aux_resto;
	else
	{
		$aux_for = $caracter;
		for($i = $pos ; $i < strlen($string) ; $i += $pos)
		{
			$aux_for .= substr($string, $i, $pos);
			$aux_for .= $caracter;
		}
		$aux_r = $aux_inic.$aux_for;
	}
	return $aux_r;
}












# !!!!!!!!!!!!!!!!  PROVISORIO  !!!!!!!!!!!!!!!!!!!

function mascara_cnpj($cnpj)
{
		$mask = $cnpj;
		$var1 = substr("$mask", 0,2);
		$var2 = substr("$mask", 2,3);  
		$var3 = substr("$mask", 5,3);  
		$var4 = substr("$mask", 8,4);      
		$var5 = substr("$mask", 12,2);  
		$doc = $var1.".".$var2.".".$var3."/".$var4."-".$var5;
		return $doc;
}

function mascara_cpf($cpf)
{
		$mask = $cpf;
		$var1 = substr("$mask", 0,3);
		$var2 = substr("$mask", 3,3);  
		$var3 = substr("$mask", 6,3);  
		$var4 = substr("$mask", 9,2);      
		$doc = $var1.".".$var2.".".$var3."-".$var4;
		return $doc;
}

function monta_data($date)
{
	$mask = $date;
	$dia = substr($mask,8,2);
	$mes = substr($mask,5,2);
	$ano = substr($mask,0,4);
	
	if (isset($_SESSION['lang'])){
		if($_SESSION['lang'] == 'en'){
			$doc = $mes."/".$dia."/".$ano;
		}else{
			$doc = $dia."/".$mes."/".$ano;
		}
	}else{
	$doc = $dia."/".$mes."/".$ano;
	}
	
	return $doc;
}

function monta_data_gravacao($date)
{
	$mask = $date;
	$dia = substr($mask,0,2);
	$mes = substr($mask,3,2);
	$ano = substr($mask,6,4);
	$doc = $ano."-".$mes."-".$dia;
	return $doc;
}

function monta_valor($pin_valor_total)
{
	$aux = $pin_valor_total;
	$tam = strlen($aux);
		if($tam >= 4) 
		{
			$mile = substr($aux,0,$tam-3);
			$cent = substr($aux,$tam-3,3);
			$valor_final = $mile.".".$cent.",00";
		}
		else
		{ $valor_final = $aux.",00"; }
	return $valor_final;
}

function limpa_string($valor)
{
	$aux = $valor;
	$s = "";
	for ($y = 1 ; $y <= strlen($aux) ; $y++)
	{ 
		$ch = substr($aux,$y-1,1); 
		if(ord($ch) >= 48 && ord($ch) <= 57)
		{ $s .= $ch; }
	}
	return $s;
}

function valida_valor_opr($val)
{
	$verifica = $val;
	for ($y = 1 ; $y <= strlen($verifica) ; $y += 1)
	{ 
		$ch = substr($verifica,$y-1,1); 
		if(ord($ch) >= 48 && ord($ch) <= 57)
			{ $alerta = 0; }
		else
			{ $alerta = 1; break; }
	}
	if($alerta == 0)
		{ return 1; }
	else
		{ return 0; }
}




function organiza_casa($valor)
{
	$aux = $valor;
	$tam = strlen($aux);
		if($tam >= 4) 
		{
			$mile = substr($aux,0,$tam-3);
			$cent = substr($aux,$tam-3,3);
			$valor_final = $mile.".".$cent;
			return $valor_final;
		}
		else
		{ 	return $aux; }
}



function define_casa_decimal($valor, $qtde)
{
	$aux = $valor;
	$contador = 0;
	for ($x = 1 ; $x <= strlen($aux) ; $x++)
	{
		$pos = substr($aux,$x-1,1);
		if(ord($pos) == 46)
			{ $alerta = 1; break; }
		else
			{ $alerta = 0;}
		$contador ++;
	}
		if($alerta == 1)
		{
			$antes_virg = substr($aux,0,$contador);
			$pos_peg = $contador + 1;
			$dep_virg = substr($aux,$pos_peg,$qtde);
				if(strlen($dep_virg) == 1) { $dep_virg .= '0'; }
		}
		$valor_final = $antes_virg.",".$dep_virg;
		return $valor_final;
}

function grava_comissao_banco($comissao)
{
	$aux = $comissao;
	for ($x = 1 ; $x <= strlen($aux) ; $x++)
	{
		$pos = substr($aux,$x-1,1);
		if(ord($pos) >= 48 && ord($pos) <= 57)
		{ $alerta = 0; }
		else
		{ $alerta = 1; break;}
	}
		
		if($alerta == 1)
		{
			for ($y = 1 ; $y <= strlen($aux) ; $y++)
			{
				$pos_cch = substr($aux,$y-1,1);
				if(ord($pos_cch) >= 48 && ord($pos_cch) <= 57)
				{ $comic .= $pos_cch; }
			}
		}
		
		if($alerta == 0)
		{ $comic = $aux.'0'; }
	
	return $comic;
}

function verifica_cep($cep)
{
	$aux = $cep;
	$tam = strlen($aux);
		if($tam < 9)
		{ return 0; }
		else
		{
			$traco = substr($aux,5,1);
			if(ord($traco) != 45)
			{ return 0; }
			else
			{
				$inicial = substr($aux,0,5); 
				for ($x = 1 ; $x <= strlen($inicial) ; $x++)
				{
					$pos = substr($inicial,$x-1,1);
					if(ord($pos) >= 48 && ord($pos) <= 57)
					{ $alerta = 0; }
					else
					{ $alerta = 1; break;}
				}
																							
					if($alerta == 1) 
					{ return 0; }
					else
					{
						$final = substr($aux,6,3); 
						for ($x = 1 ; $x <= strlen($final) ; $x++)
						{
							$pos = substr($final,$x-1,1);
							if(ord($pos) >= 48 && ord($pos) <= 57)
							{ $alerta = 0; }
							else
							{ $alerta = 1; break;}							
						}
					
							if($alerta == 1) 
							{ return 0; }
							else	
							{ return 1; }
					}								
			}																																
		}
}

function moeda_char($string)
{
	$aux = $string;
	$contador = 0;
	for ($x = 1 ; $x <= strlen($aux) ; $x++)
	{
		$pos = substr($aux,$x-1,1);
		if(ord($pos) == 46)
			{ $alerta = 1; break; }
		else
			{ $alerta = 0;}
		$contador ++;
	}
	
		if($alerta == 1)
		{
			$antes_virg = substr($aux,0,$contador);
			$tam_antes_virg = strlen($antes_virg);

			if($tam_antes_virg >= 4 && $tam_antes_virg <= 6) 
			{ 
				$mil1 = substr($antes_virg,0,$tam_antes_virg-3);
				$mil2 = substr($antes_virg,$tam_antes_virg-3,3);
				
					if($mil1 == "-")
					{ $antes_format = $mil1." ".$mil2; }
					else
					{ $antes_format = $mil1.".".$mil2; }			
			}
			else
			{ $antes_format = $antes_virg; }
			
			$pos_peg = $contador + 1;
			$dep_virg = substr($aux,$pos_peg,strlen($aux)-1);
			
				if(strlen($dep_virg) == 1) { $depois_format = $dep_virg."0"; }
				if(strlen($dep_virg) == 2) { $depois_format = $dep_virg; }
				if(strlen($dep_virg) > 2) 
				{ 
					$duvidoso = substr($dep_virg,2,1);
					$anterior = substr($dep_virg,1,1);
					$significativo = substr($dep_virg,0,1);
					if($duvidoso > 5) 
					{ 
						$anterior += 1;
						$depois_format = $significativo.$anterior;
					}
					if($duvidoso < 5) 
					{ 
						$depois_format = $significativo.$anterior;
					}
					if($duvidoso == 5) 
					{ 
						if($anterior%2 != 0) 
						{$anterior += 1;}						
						$depois_format = $significativo.$anterior;
					}
				}
				$valor_final = $antes_format.",".$depois_format;
				
		return $valor_final;
		}
		else
		{ 
			$antes_virg = $aux;
			$tam_antes_virg = strlen($antes_virg);
			
			if($tam_antes_virg >= 4 && $tam_antes_virg <= 6) 
			{ 
				$mil1 = substr($antes_virg,0,$tam_antes_virg-3);
				$mil2 = substr($antes_virg,$tam_antes_virg-3,3);
				$antes_format = $mil1.".".$mil2.",00"; 
			}
			else
			{ $antes_format = $antes_virg.",00"; }
		
		return $antes_format; 
		}
}


# Esta função recebe um valor sem casa decimal, apenas números
# e retorna um valor formatado em Moeda
# ENTRADA: 964567
# SAÍDA: 9.645,67
function moeda_numero($string)
{
	$aux = $string;
	$tam = strlen($aux);
	
	if($tam == 1)
	{
		if($aux == 0)
		{ $valor_final = "0,00"; }
		else
		{ $valor_final = "0,0".$aux; }
	}

	if($tam > 1)
	{
		$inicio = substr($aux,0,$tam-2);
		$decimal = substr($aux,$tam-2,2);
		
		$tam_inicio = strlen($inicio);
	
			if($tam_inicio >= 4 && $tam_inicio <= 6) 
			{ 
				$mil1 = substr($inicio,0,$tam_inicio-3);
				$mil2 = substr($inicio,$tam_inicio-3,3);
				
					if($mil1 == "-")
					{ $inicio_format = $mil1." ".$mil2; }
					else
					{ $inicio_format = $mil1.".".$mil2; }			 
			}
			else
			{ $inicio_format = $inicio; }
			
			$valor_final = $inicio_format.",".$decimal;
	}
	return $valor_final;
}

function monta_valor_pgto($valor)
{
	$aux = $valor;
	$s = "";
	for ($y = 1 ; $y <= strlen($aux) ; $y++)
	{ 
		$ch = substr($aux,$y-1,1); 
		if(ord($ch) >= 48 && ord($ch) <= 57)
		{ $s .= $ch; }
	}
	return $s;
}

function retorna_valores_pin($connid,$oprcodigo,$opr_tipo_online) 
{
if(!$connid) return "DIED!";
if($opr_tipo_online) 
{
$sql = "select valor_fixo from pin_valor_fixo t0, pin_valor_lista t1 where t1.valor_lista_cod=t0.valor_lista_cod and opr_codigo = $oprcodigo group by valor_fixo,opr_codigo order by opr_codigo";
$resx=pg_exec($connid,$sql);
$srang="";
while($pgresx=pg_fetch_array($resx)) 
	$srang.=sprintf("%d,",$pgresx['valor_fixo']);
}
else 
{
$sql = "select opr_valor1,opr_valor2,opr_valor3,opr_valor4,opr_valor5,opr_valor6,opr_valor7,opr_valor8,opr_valor9,opr_valor10,opr_valor11min,opr_valor11max from operadoras where opr_codigo=$oprcodigo";
$resx=pg_exec($connid,$sql);
$resid=pg_fetch_array($resx);
$srang="";
for($a=0;$a<13;$a++) {
	if($resid[$a]) 
		$srang.=sprintf("%d,",$resid[$a]);
	}
}
$srang[strlen($srang)-1]="";
return $srang;
}


//#####################################################################################
//###################            EstabelecimentoMovimentacao
//#####################################################################################
function insere_EstabelecimentoMovimentacao(	$var_est_codigo, 
												$var_tipo, 
												$var_origem, 
												$var_mapeamento, 
												$var_mapeamento_aux, 
												$var_valor,
												$var_descricao){
//-----------------------------------------------------------------------------------------------
//Funcao para inserir a movimentacao (Extrato) de um estabelecimento
//-----------------------------------------------------------------------------------------------
// A chamada desta funcao deve ser inserida no codigo DEPOIS da atualizacao do saldo,
// pois ela busca o saldo atualizado do estabelecimento depois da atualizacao do saldo.
//-----------------------------------------------------------------------------------------------
//	$var_est_codigo 		- Codigo do estabelecimento	(Numerico)
//	$var_tipo				- Tipo do lancamento, conforme abaixo (Char)
//	$var_origem				- Codigo da Origem(Numerico)
//	$var_mapeamento			- Codigo do Mapeamento(Numerico)
//	$var_mapeamento_aux		- Campo auxiliar com ids de tabelas (Coringa)(Texto 255) (separador por ;)
//	$var_valor				- Valor do lancamento (Numerico com ponto como separador de centavos)
//							# O valor do lancamento sempre sera positivo, se entrar um valor negativo,
//							# será transformado para positivo.
//	$var_descricao			- Campo auxiliar opcional com uma descricao do lancamento
//-----------------------------------------------------------------------------------------------

//Configuracoes e declaracoes
//-----------------------------------------------------------------------------------------------
	//Tipos possiveis de lancamento
	//-----------------------------------------------------------------------------------------------
	//	C - Credito
	//	D - Debito
	//	I - Informativo
	$tiposAr = array('C','D','I');

	//Variaveis globais
	global $connid, $EM_parametros;
	
	//Altera o error handler
	set_error_handler("errorHandler_EstabelecimentoMovimentacao");

	//Parametros de entrada
	$EM_parametros  = "var_est_codigo='$var_est_codigo', var_tipo='$var_tipo', var_origem='$var_origem', ";
	$EM_parametros .= "var_mapeamento='$var_mapeamento', var_mapeamento_aux='$var_mapeamento_aux', ";
	$EM_parametros .= "var_valor='$var_valor', var_descricao='$var_descricao', ";
	$EM_msg = "Parametros: " . $EM_parametros . "\n";


//Validacoes
//-----------------------------------------------------------------------------------------------
	//Estabelecimento
	if(!isset($var_est_codigo) || $var_est_codigo == '' || !is_numeric($var_est_codigo)){
		$EM_msg .= "Codigo do estabelecimento invalido ou nao informado.\n";
		gravaLog_EstabelecimentoMovimentacao($EM_msg);
		return false;
	}
	
	//Tipo
	if(!isset($var_tipo) || $var_tipo == '' || !in_array(strtoupper($var_tipo), $tiposAr)){
		$EM_msg .= "Codigo do tipo de lancamento invalido ou nao informado.\n";
		gravaLog_EstabelecimentoMovimentacao($EM_msg);
		return false;
	}

	//Origem
	if(!isset($var_origem) || $var_origem == '' || !is_numeric($var_origem)){
		$EM_msg .= "Codigo da origem invalido ou nao informado.\n";
		gravaLog_EstabelecimentoMovimentacao($EM_msg);
		return false;
	}

	//Mapeamento
	if(!isset($var_mapeamento) || $var_mapeamento == '' || !is_numeric($var_mapeamento)){
		$EM_msg .= "Codigo do mapeamento invalido ou nao informado.\n";
		gravaLog_EstabelecimentoMovimentacao($EM_msg);
		return false;
	}
	
	
//Configura valores
//-----------------------------------------------------------------------------------------------
	//Verifica se o estabelecimento eh pre ou pos
	$sql = "SELECT est_tipo_venda from estabelecimentos where est_codigo = ".$var_est_codigo;
	$result = pg_exec($connid,$sql);
	$pgresult = pg_fetch_array($result);
	$est_tipo_venda = strtoupper(trim($pgresult['est_tipo_venda']));

	//Tipo - Operador
	$tipo_operador = '+';	//Para debito, soma o valor ao saldo atual para saber o saldo anterior
	if(strtoupper($var_tipo) == 'C')
		$tipo_operador = '-'; 	//Para credito, subtrai o valor ao saldo atual para saber o saldo anterior

	//Inverte o sinal se pospago
	if($est_tipo_venda == 'POSPAGO') $tipo_operador = ($tipo_operador == '+')?'-':'+';

	//Mapeamento
	$var_mapeamento_aux = str_replace("'", "''", $var_mapeamento_aux);
	
	//Valor
	if(!isset($var_valor) || $var_valor == '' || !is_numeric($var_valor)) $var_valor = 0;
	$var_valor = abs($var_valor);
	

//Insere na tabela
//-----------------------------------------------------------------------------------------------
	//Insert
	$sql  = "insert into tb_estab_movimentacao (
					em_est_codigo, em_tipo, em_emm_origem, em_emm_mapeamento, em_mapeamento_auxiliar, em_lancamento_valor, em_lancamento_descricao, 
					em_saldo_antes, 
					em_saldo_depois
			) values (
					$var_est_codigo, '$var_tipo', $var_origem, $var_mapeamento, '$var_mapeamento_aux', $var_valor, '$var_descricao', 
					(select quantidade_valor_vendas from estabelecimentos where est_codigo = $var_est_codigo) $tipo_operador $var_valor,
					(select quantidade_valor_vendas from estabelecimentos where est_codigo = $var_est_codigo) 
			)";
	pg_exec($connid, $sql);

	//Restaura o error handler
	restore_error_handler();
	
	return true;

}

function gravaLog_EstabelecimentoMovimentacao($EM_mensagem){
        global $raiz_do_projeto;
	//Arquivo
	$file = $raiz_do_projeto . 'log/log_estabelecimento_movimentacao.txt';

	//Mensagem
	$EM_mensagem = date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . "\n" . $EM_mensagem . "\n";

	//Grava mensagem no arquivo
	if ($handle = fopen($file, 'a+')) {
		fwrite($handle, $EM_mensagem);
		fclose($handle);
	} 

}

function errorHandler_EstabelecimentoMovimentacao($errno, $errstr, $errfile, $errline) {

	global $EM_parametros;
	
	$EM_mensagem = "Parametros: " . $EM_parametros . "\n";
	$EM_mensagem .= "Erro ao inserir lancamento de movimentacao.\n";
	
	$EM_mensagem .= "Numero:" . $errno . "\n";
	$EM_mensagem .= "Descricao:" . $errstr . "\n";
	$EM_mensagem .= "Arquivo:" . $errfile . "\n";
	$EM_mensagem .= "Linha:" . $errline . "\n";

	gravaLog_EstabelecimentoMovimentacao($EM_mensagem);

}

function descricaoStatusLiberacao($status_liberacao){

		$status_liberacao_aux = trim($status_liberacao);
		
		$ret = "";
		
		if($status_liberacao_aux == "1")
			$ret = "Banco inválido";
		else if($status_liberacao_aux == "2")
			$ret = "Valor mínimo inválido";
		else if($status_liberacao_aux == "3")
			$ret = "Valor muito acima dos regularmente praticados pelo estabelecimento";
		else if($status_liberacao_aux == "4")
			$ret = "Número do documento inválido";
		else if($status_liberacao_aux == "5")
			$ret = "Primeiro pedido com valor muito alto";
		else if($status_liberacao_aux == "6")
			$ret = "Há pedido pendente de conciliação ou estornado";
		else if($status_liberacao_aux == "7")
			$ret = "Há pedido pendente de conciliação e o pedido atual não é compatível com o perfil de venda do estabelecimento";
		else if($status_liberacao_aux == "8")
			$ret = "Limite insuficiente para conter saldo devedor, pedidos pendentes anteriores e pedido atual";
		else if($status_liberacao_aux == "9")
			$ret = "Pedido com as mesmas características de um já liberado nas últimas horas";
		else if($status_liberacao_aux == "10")
			$ret = "Tipo de pedido inválido, não é INFORMA PAGTO";
		else if($status_liberacao_aux == "11")
			$ret = "Estabelecimento bloqueado por fraude";
		else if($status_liberacao_aux == "12")
			$ret = "Estabelecimento não PREPAGO";
		else if($status_liberacao_aux == "13")
			$ret = "Estabelecimento de teste";
		else if($status_liberacao_aux == "14")
			$ret = "Valor solicitado dividido por 10, porém ainda muito acima dos regularmente praticados pelo estabelecimento";
		else if($status_liberacao_aux == "998")
			$ret = "Pedido correto mas não liberado";
		else if($status_liberacao_aux == "999")
			$ret = "Pedido liberado";
		
		return $ret;

}

function formata_timestamp($data,$gravar)
{
	$mask = $data;
	if($gravar == 0)
	{
		$dia = substr($mask,8,2);
		$mes = substr($mask,5,2);
		$ano = substr($mask,0,4);
		$hora = substr($mask,11,2);
		$min = substr($mask,14,2);
		$seg = substr($mask,17,2);
		$mile = substr($mask,20,5);
		$doc = $dia."/".$mes."/".$ano;
	}
	
	if($gravar == 1)
	{
		$dia = substr($mask,8,2);
		$mes = substr($mask,5,2);
		$ano = substr($mask,0,4);
		$hora = substr($mask,11,2);
		$min = substr($mask,14,2);
		$seg = substr($mask,17,2);
		$mile = substr($mask,20,5);
		$doc = $hora.":".$min.":".$seg;
	}
	if($gravar == 2)
	{
		$dia = substr($mask,8,2);
		$mes = substr($mask,5,2);
		$ano = substr($mask,0,4);
		$hora = substr($mask,11,2);
		$min = substr($mask,14,2);
		$seg = substr($mask,17,2);
		$mile = substr($mask,20,5);
		$doc = $dia."/".$mes."/".$ano." - ".$hora.":".$min.":".$seg;
	}
	if($gravar == 3)
	{
		$dia = substr($mask,0,2);
		$mes = substr($mask,3,2);
		$ano = substr($mask,6,4);
		$hora = substr($mask,11,2);
		$min = substr($mask,14,2);
		$seg = substr($mask,17,2);
		$doc = $ano."-".$mes."-".$dia." ".$hora.":".$min.":".$seg;
	}
	return $doc;
}


/**
 * Get the current charset
 *
 * @return  string      Empty string or "UTF-8/".
 */
function eprepag_getCharset() {
    global $eprepag;

    $charset = $eprepag['charset'];
    if (!empty($_POST['charset'])) {
        if ($_POST['charset'] == 'UTF-8/') {
            $charset = 'UTF-8/';
        } else {
            $charset = '';
        }
    }

    if (!empty($eprepag['POST']['charset'])) {
        if ($eprepag['POST']['charset'] == 'UTF-8/') {
            $charset = 'UTF-8/';
        } else {
            $charset = '';
        }
    }
    return $charset;
}

/**
 * Detect the language of the User Agent/Visitor
 *
 *
 * @access public
 * @param   boolean     Toggle whether to include the language that has been autodetected.
 * @return  string      Return the detected language name
 */
function eprepag_detectLang($use_include = false) {
    global $eprepag;

    $supported_languages = array_keys($eprepag['languages']);
    $possible_languages = explode(',', (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : ''));
    if (is_array($possible_languages)) {
        $charset = eprepag_getCharset();

        foreach($possible_languages as $index => $lang) {
            $preferred_language = strtolower(preg_replace('@^([^\-_;]*)_?.*$@', '\1', $lang));
            if (in_array($preferred_language, $supported_languages)) {
                if ($use_include) {
                    @include(S9Y_INCLUDE_PATH . 'lang/' . $charset . 'eprepag_lang_' . $preferred_language . '.inc.php');
                    $eprepag['autolang'] = $preferred_language;
                }
                return $preferred_language;
            } // endif
        } // endforeach
    } // endif

    return $eprepag['lang'];
}


	function theRealStripTags2($string){
	
	   $tam=strlen($string);
	   // tam have number of chars the string
	
	   $newstring="";
	   // newstring will be returned
	
	   $tag=0;
	   /* if tag = 0 => copy char from string to newstring
		   if tag > 0 => don't copy. Found one or more  '<' and need
		   to search '>'. If we found 3 '<' need to find all the 3 '>'
	   */
	
	   for ($i=0; $i < $tam; $i++){
		   // If I found one '<', $tag++ and continue whithout copy
		   if ($string{$i} == '<'){
			   $tag++;
			   continue;
		   }
	
		   // if I found '>', decrease $tag and continue 
		   if ($string{$i} == '>'){
			   if ($tag){
				   $tag--;
			   }
		   /* $tag never be negative. If string is "<b>test</b>>"
			   (error, of course) $tag will stop in 0
		   */
			   continue;
		   }
	
		   // if $tag is 0, can copy 
		   if ($tag == 0){
			   $newstring .= $string{$i}; // simple copy, only one car
		   }
	   }
	   return $newstring;
	}

	function enviaEmail($to, $cc, $bcc, $subject, $msgEmail, $attach = null) {
		

/*
		$mailVars['to'] = $to;
		if($cc)$mailVars['cc'] = $cc;
		if($bcc)$mailVars['bcc'] = $bcc;
		$mailVars['from'] = 'suporte@e-prepag.com.br';
		$mailVars['fromname'] = 'E-Prepag';
		$mailVars['subject'] = $subject;
		$mailVars['html'] = $msgEmail;
		//return mimemail($mailVars);
*/
/*
		// To send HTML mail, the Content-type header must be set
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		// Additional headers
		$headers .= 'From: E-Prepag <suporte@e-prepag.com.br>' . "\r\n";
		$headers .= 'Reply-To: E-Prepag <suporte@e-prepag.com.br>' . "\r\n";
		if($cc)  $headers .= 'Cc: '  . $cc  . "\r\n";
		if($bcc) $headers .= 'Bcc: ' . $bcc . "\r\n";
		return mail($to, $subject, $msgEmail, $headers);
*/
		//return true;
	
//		if(substr($subject, 0, 41)=="E-Prepag - Relatório de Ativação Ongame -") {
//			$body_plain = html_entity_decode($body_plain, ENT_QUOTES, 'ISO8859-1');
//		} else {
			$body_plain = str_replace("\r\n", "", $msgEmail);
			$body_plain = str_replace("<br>", "\r\n", $msgEmail);
//			$body_plain = theRealStripTags2($body_plain);
			$body_plain = str_replace("\t", "", $body_plain);
			$body_plain = html_entity_decode($body_plain, ENT_QUOTES, 'ISO8859-1');
			$body_plain = str_replace("    ", "", $body_plain);
			$body_plain = str_replace("\r\n\r\n\r\n\r\n", "\r\n", $body_plain);
			$body_plain = str_replace(", \r\n", ", ", $body_plain);
//		}
		
//		if(substr($subject, 0, 41)=="E-Prepag - Relatório de Ativação Ongame -") {
//echo "<hr>Len: ".strlen($body_plain)."<hr><span style='background-color:#CCCC33'>".$body_plain."</span><hr>";
//echo "<hr>Len: ".strlen($msgEmail)."<hr><span style='background-color:#CCCC33'>".$msgEmail."</span><hr>";
//		}
		return enviaEmail3($to, $cc, $bcc, $subject, $msgEmail, $body_plain, $attach);	
	}

	function enviaEmail3($to, $cc, $bcc, $subject, $body_html, $body_plain, $attach = null) {
	
		if (!class_exists('PHPMailer')) {
			require_once($GLOBALS['raiz_do_projeto']."class/phpmailer/class.phpmailer.php");
		}		

		$mail = new PHPMailer();
//		$mail->Host     = "smtp.e-prepag.com.br";	//"localhost";
        //-----Alteração exigida pela BaseNet(11/2017)-------------//
        $mail->Host     = "smtp.basenet.com.br";
        //---------------------------------------------------------//
		$mail->Mailer   = "smtp";
		$mail->From     = "suporte@e-prepag.com.br";
		$mail->SMTPAuth = true;     // turn on SMTP authentication
		$mail->Username = 'suporte@e-prepag.com.br';  // a valid email here
		$mail->Password = '@AnQ1V7hP#E7pQ31'; //'985856'; //'8s}:#t)YTa~5ks))';'850637'; 
		$mail->FromName = "E-Prepag";
		$mail->isHTML(true);
        
        //-----Alteração exigida pela BaseNet(11/2017)-------------//
        $mail->IsSMTP();
        $mail->SMTPSecure = "ssl";
        $mail->Port     = 465;
        //---------------------------------------------------------//
                
                // Overwrite smt details for dev version cause e-prepag.com.br server reject it
                // You can just add your IP or use elseif with your details
                // When run bat files there is not ip address so we need use COMPUTERNAME to check
                if(checkIP() || (class_exists('EmailEnvironment')  && EmailEnvironment::serverId() == 1)) {
                    //  $mail->SMTPDebug  = 1; descomentar para debugar 
                    $mail->IsSMTP();
                    $mail->SMTPSecure = "ssl";
                    $mail->Host     = "email-ssl.com.br";
                    $mail->Port     = 465;
                    $mail->From     = "send@e-prepag.com";
                    $mail->Username = 'send@e-prepag.com';
                    $mail->Password = 'sendeprepag2013';
                    }

		// Reply-to
		$mail->AddReplyTo('suporte@e-prepag.com.br');
		
		//To
		if($to && trim($to) != ""){
			$toAr = explode(",", $to);
			for($i = 0; $i < count($toAr); $i++) $mail->AddAddress($toAr[$i]);
		}

		//Cc
		if($cc && trim($cc) != ""){
			$ccAr = explode(",", $cc);
			for($i = 0; $i < count($ccAr); $i++) $mail->AddCC($ccAr[$i]);
		}

		//Bcc
		if($bcc && trim($bcc) != ""){
			$bccAr = explode(",", $bcc);
			for($i = 0; $i < count($bccAr); $i++) $mail->AddBCC($bccAr[$i]);
		}

		if (!empty($attach)) {
			$mail->AddAttachment($attach);
		}
    	$mail->Subject = $subject;
    	$mail->Body    = $body_html;
    	$mail->AltBody = $body_plain;

//echo print_r($mail, true);

		return $mail->Send();	

	}

	function enviaEmail4($to, $cc, $bcc, $subject, $body_html, $body_plain, $attach = null, $stringAttach = false, $nome = '') {
            
		if (!class_exists('PHPMailer')) {
			require_once($GLOBALS['raiz_do_projeto']."class/phpmailer/class.phpmailer.php");
		}		

		$mail = new PHPMailer();
                //-----Alteração exigida pela BaseNet(11/2017)-------------//
                $mail->Host     = "smtp.basenet.com.br";
                //---------------------------------------------------------//
		$mail->Mailer   = "smtp";
		$mail->From     = "financeiro@e-prepag.com.br";
		$mail->SMTPAuth = true;     // turn on SMTP authentication
		$mail->Username = 'financeiro@e-prepag.com.br';  // a valid email here
		$mail->Password = '8s}:#t)YTa~5ks))'; 
		$mail->FromName = "E-Prepag";
		$mail->isHTML(true);

                //-----Alteração exigida pela BaseNet(11/2017)-------------//
                $mail->IsSMTP();
                $mail->SMTPSecure = "ssl";
                $mail->Port     = 465;
                //---------------------------------------------------------//   
               
                // Overwrite smt details for dev version cause e-prepag.com.br server reject it
                // When run bat files there is not ip address so we need use COMPUTERNAME to check
//Comentar aki quando problema no email
                if(checkIP() || (class_exists('EmailEnvironment')  && EmailEnvironment::serverId() == 1)) {
                    //  $mail->SMTPDebug  = 1; descomentar para debugar 
                    $mail->IsSMTP();
                    $mail->SMTPSecure = "ssl";
                    $mail->Host     = "email-ssl.com.br";
                    $mail->Port     = 465;
                    $mail->From     = "send@e-prepag.com";
                    $mail->Username = 'send@e-prepag.com';
                    $mail->Password = 'sendeprepag2013';
                }

		// Reply-to
		$mail->AddReplyTo('financeiro@e-prepag.com.br');
		
		//To
		if($to && trim($to) != ""){
			$toAr = explode(",", $to);
			for($i = 0; $i < count($toAr); $i++) $mail->AddAddress($toAr[$i]);
		}

		//Cc
		if($cc && trim($cc) != ""){
			$ccAr = explode(",", $cc);
			for($i = 0; $i < count($ccAr); $i++) $mail->AddCC($ccAr[$i]);
		}

		//Bcc
		if($bcc && trim($bcc) != ""){
			$bccAr = explode(",", $bcc);
			for($i = 0; $i < count($bccAr); $i++) $mail->AddBCC($bccAr[$i]);
		}

		if (!empty($attach)) {
                    if ( $stringAttach ) {
                        $mail->AddStringAttachment($attach, $name);
                    } else {
                        $mail->addAttachment($attach);
                    }
                }
                $mail->Subject = $subject;
                $mail->Body    = $body_html;
                $mail->AltBody = $body_plain;

		return $mail->Send();	

	}

	function get_day_of_week($date1) {

		$dia_semana = "???";
		$dia_ingles = date("w", strtotime($date1));
//echo "$date1 -> $dia_ingles";
//echo "($dia_ingles) ";
		if(date('H') >= 6)$cpa="Bom dia";
		if(date('H') >= 13)$cpa="Boa tarde";
		if(date('H') >= 18)$cpa="Boa noite";
//echo $cpa;				
		switch($dia_ingles) 
		{
			case "1": $dia_semana = LANG_SITE_DAY_OF_WEEK_MONDAY; break; 
			case "2": $dia_semana = LANG_SITE_DAY_OF_WEEK_TUESDAY; break; 
			case "3": $dia_semana = LANG_SITE_DAY_OF_WEEK_WEDNESDAY; break; 
			case "4": $dia_semana = LANG_SITE_DAY_OF_WEEK_THURSDAY; break; 
			case "5": $dia_semana = LANG_SITE_DAY_OF_WEEK_FRIDAY; break; 
			case "6": $dia_semana = LANG_SITE_DAY_OF_WEEK_SATURDAY; break; 
			case "0": $dia_semana = LANG_SITE_DAY_OF_WEEK_SUNDAY; break; 
		}
		return $dia_semana;
	}

	function nomeOperadora($dd_operadora) {
		if($dd_operadora=="OG") 
			return "OnGame";
		if ($dd_operadora=="HB") 
			return "Habbo Hotel";
		if ($dd_operadora=="MU")
			return "Mu Online";

		return "?????";
	}

	// $iNumericType 
	//	0 - any char type
	//	1 - only numbers
	//	2 - only chars
	//	3 - alphanumeric
	function is_csv_numeric_global($list, $iNumericType = 1) {
	//echo "list: '$list'<br>";
		$list1 = str_replace(" ", "", $list);
	//echo "list1: '$list1'<br>";
		$alist = explode(",", $list1);
	//echo "alist: <pre>".print_r($alist, true)."</pre><br>";
		$bret = true;
		foreach($alist as $key => $val) {
	//echo $val." - ".((is_numeric($val))?"NUMERIC":"ALPHA")."<br>";
			switch($iNumericType) {
				case 1: 
					$bret = is_numeric($val);
					break;
				case 2: 
					$bret = ctype_alpha($val);
					break;
				case 3: 
					$bret = ctype_alnum($val);
					break;
				default: 
					$bret = true;
					break;
			}
			if(!$bret) {
				break;
			}
		}
		return $bret;
	}

	function b_IsUsuarioReinaldo(){
//echo "<pre>".print_r($GLOBALS['_SESSION'], true)."</pre>";
//echo "<!-- TTTT ".print_r($GLOBALS['_SESSION'], true)." -->\n";

		$stmp = $GLOBALS['_SESSION']['userlogin_bko'];
//echo "<-- TTT stmp: $stmp -->\n";
		if(strtoupper($stmp)=="REINALDO") {
//echo "<!-- TTTT OK -->\n";
			return true;
		}
//echo "<!-- TTTT NOpe -->\n";
		return false;
	}

	function b_IsUsuarioByName($login_name){
		$stmp = $GLOBALS['_SESSION']['userlogin_bko'];
		if(strtoupper($stmp)==$login_name) {
			return true;
		}
		return false;
	}

	function b_IsUsuarioAdminList(){
		if(b_IsUsuarioByName("REINALDO") || b_IsUsuarioByName("WAGNER") || b_IsUsuarioByName("FABIO") || b_IsUsuarioByName("FABNASCI") || b_IsUsuarioByName("GLAUCIA")) {
			return true;
		}
		return false;
	}

        function retorna_ip_acesso_sys_admin() {
                $realip = "";
                if (isset($_SERVER)) {
                        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                                $ip = $_SERVER['HTTP_CLIENT_IP'];
                        } else {
                                $ip = $_SERVER['REMOTE_ADDR'];
                        }
           } else {
                        if (getenv('HTTP_X_FORWARDED_FOR')) {
                                $ip = getenv('HTTP_X_FORWARDED_FOR');
                        } elseif (getenv('HTTP_CLIENT_IP')) {
                                $ip = getenv('HTTP_CLIENT_IP');
                        } else {
                                $ip = getenv('REMOTE_ADDR');
                        }
           }
           return $ip;
        }  
        
        //Função que busca todos os Publishers que fazem o fechamento pelo data de utilização do PIN
        function levantamentoPublisherComFechamentoUtilizacao() {

                // Buscando informações 
                $sql = "select 
                                opr_codigo, 
                                opr_data_inicio_contabilizacao_utilizacao
                        from operadoras
                        where 
                                opr_contabiliza_utilizacao != 0
                        order by opr_codigo
                        ";

                //echo $sql.PHP_EOL; die();
                $rs_publisher = SQLexecuteQuery($sql);
                //echo pg_num_rows($rs_publisher)."<br>";
                if(!$rs_publisher) {
                    echo "Erro na Query de Levantamento de Publishers contendo Fechamento por Utilização de PINs(".$sql.").<br>".PHP_EOL;
                    return array();
                }
                if(pg_num_rows($rs_publisher) == 0) {
                    return array();
                }//end if(pg_num_rows($rs_publisher) == 0)
                else {
                    while($rs_publisher_row = pg_fetch_array($rs_publisher)) {
                        $aux_retorno[$rs_publisher_row['opr_codigo']] = $rs_publisher_row['opr_data_inicio_contabilizacao_utilizacao'];
                    }//end while
                    return $aux_retorno;
                }//end else

        }//end function levantamentoPublisherComFechamentoUtilizacao()
?>
