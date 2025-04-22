<?php

function getEnvVariable($varName) {
    // Verifica se a variável de ambiente já está definida
    $value = getenv($varName);

    if ($value === false) {
        // Carrega o arquivo .env
        if (file_exists('/www/.env')) {
            $lines = file('/www/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            foreach ($lines as $line) {
                // Ignora comentários
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }

                // Divide a linha em nome e valor
                list($name, $val) = explode('=', $line, 2);
                
                // Remove espaços e aspas
                $name = trim($name);
                $val = trim($val, " \t\n\r\0\x0B\"");

                // Se o nome da variável do .env for o mesmo, define ela
                if ($name === $varName) {
                    // Definindo a variável de ambiente no processo atual
                    putenv("$name=$val");
                    return $val;
                }
            }
        }

        // Se não encontrar no .env, retorna null ou algum valor padrão
        return null;
    }

    // Retorna o valor da variável já existente
    return $value;
}

// Funï¿½ï¿½o de execuï¿½ï¿½o de Instruï¿½ï¿½o no DB
function SQLexecuteQuery($sql) {
    $ret = pg_query ($GLOBALS['connid'], $sql);
    if (strlen ($erro = pg_last_error($GLOBALS['connid']))) {
    		$message  = date("Y-m-d H:i:s") . " ";
    		$message .= "Erro: " . $erro . "<br>\n";
    		$message .= "Query: " . $sql . "<br>\n";
    		gravaLog_SQLexecuteQuery($message);
    }
    return $ret;		
}//end function SQLexecuteQuery($sql)

//Gerador de LOG de erro de instruï¿½ï¿½es no DB
function gravaLog_SQLexecuteQuery($mensagem) {
    //Arquivo
    $file = $GLOBALS['raiz_do_projeto'] . "log/log_sql_execute_query.txt";
    //Mensagem
    $mensagem = date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . "\n" . $mensagem . "\nDEBUG:\n".print_r(debug_backtrace(),true)."\n";
    //Grava mensagem no arquivo
    if ($handle = fopen($file, 'a+')) {
            fwrite($handle, $mensagem);
            fclose($handle);
    } 
}//end function gravaLog_SQLexecuteQuery($mensagem)

//Funï¿½ï¿½o para captura de tempo
function getmicrotime() {
   list($usec, $sec) = explode(" ", microtime());
   return ((float)$usec + (float)$sec);
}//end function getmicrotime()

function is_moeda($val) {

        if(strlen($val) < 4) return false;
        if(strrpos($val, ",") != strlen($val) - 3) return false;
        if(!is_numeric(substr($val, 0, 1))) return false;
        $val = str_replace('.','',$val);
        $val = str_replace(',','.',$val);

        return is_numeric($val);
}

function is_hora($val) {

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
        
}//end function is_DateTimeEx

function SQLaddFields($var, $tipo) {

    if(is_null($var)) return "NULL";
    elseif($tipo == "r") return str_replace("'", "''", $var);
    elseif($tipo == "s") return "'" . str_replace("'", "''", $var) . "'";
    else return $var;

}
	
function space_tbl_parse($string) {
	$textarea=$string;
	$tabela[]=array();
	$linhas=explode("\n",$textarea);
	for($a=0;$a<count($linhas);$a++)
	{
		$colunas_tmp=explode(" ",$linhas[$a]);
		$c=0;
		for($b=0;$b<count($colunas_tmp);$b++)
		{
			if(trim($colunas_tmp[$b]))
				{
					$tabela[$a][$c++]=$colunas_tmp[$b];
				}
		}		
	}
        return $tabela;
}

function find_special_char($string) {
        for($a=0;$a<strlen($string);$a++)
                if((ord($string[$a]) >= 33 && ord($string[$a]) <= 64) || 	
                   (ord($string[$a]) >= 91 && ord($string[$a]) <= 96) || 
                   (ord($string[$a]) >= 123 && ord($string[$a]) <= 126))
                        return 1;
        return 0;
}

function find_special_character($string) {
	$aux = 0;
	for($i = 0 ; $i < strlen($string) ; $i++)
	{
		$char = substr($string, $i, 1);
		if((ord($char) >= 48 && ord($char) <= 57) || (ord($char) >= 65 && ord($char) <= 90))
			$aux = 0;
		else
		{
			$aux = 1;
			break;
		}
	}
	return $aux;
}

function formata_mensagem($msg, $tipo) {
	if($tipo == 'erro')
		$msg_final = "<font color='#FF0000' size='2' face='arial, helvetica, sans-serif'><strong>".$msg."</strong></font>";
	
	if($tipo == 'informa')
		$msg_final = "<font color='#666666' size='1' face='arial, helvetica, sans-serif'>".$msg."</font>";
		
	return $msg_final;
}

function formata_mensagem2($msg, $tipo) {
	if($tipo == 'erro')
		$msg_final = "<font color='#FF0000' size='1' face='arial, helvetica, sans-serif'><strong>".$msg."</strong></font>";
	
	if($tipo == 'informa')
		$msg_final = "<font color='#666666' size='1' face='arial, helvetica, sans-serif'>".$msg."</font>";
		
	return $msg_final;
}

function saudacao() {
	if((date('H') >= 18 && date('H') <= 23 ) || (date('H') >= 0 && date('H') <= 5 ))  $msg = "Bom Noite";
	if(date('H') >= 6 && date('H') <= 11)  $msg = "Bom dia";
	if(date('H') >= 12 && date('H') <= 17) $msg = "Boa Tarde";
	
	return $msg;
}


/*
DESCRIï¿½ï¿½O:
Funï¿½ï¿½o que faz a paginaï¿½ï¿½o de uma query

ENTRADA:
$inicial: Indica em qual linha da query vai comeï¿½ar a mostrar
$total_table: Quantidade de registros que a query retornou
$max: Quantidade de registros por pï¿½gina na tela
$qtde_colunas: Quantidade de colunas que tem a linha da tabela do HTML
$img_anterior: Caminho da imagem Anterior
$img_proxima: Caminho da imagem Prï¿½xima
$default_add: Nome do arquivo da pï¿½gina
$range: Nï¿½mero do range inicial
$range_qtde: Quantidade de paginas mostradas na paginaï¿½ï¿½o
$ncamp: Campo que estï¿½ ordenando o SQL
$varsel: Variï¿½veis de pesquisa

SAï¿½DA:
ï¿½ retornado a paginaï¿½ï¿½o na pï¿½gina em que a funï¿½ï¿½o foi chamada

*/
function paginacao_query($inicial, $total_table, $max, $qtde_colunas, $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel) {
	if($total_table > $max) // Sï¿½ mostra a numeraï¿½ï¿½o das pï¿½ginas se a quantidade de registros for maior do que o mï¿½ximo permitido na tela
	{
		echo "<tr><td colspan='".$qtde_colunas."'>&nbsp;</td></tr>";
		echo "<tr><td colspan='".$qtde_colunas."' align='center'>";

		if($range != 1) // Mostra a imagem de Anterior
		{
			$prev_range = $range - ($range_qtde - 1) - 1;
			$page_prev = (($prev_range * $max) - ($max - 1)) - 1;
			echo "<a href=".$default_add."?inicial=".$page_prev."&range=".$prev_range."&ncamp=".$ncamp.$varsel."&btPesquisar=Pesquisar"."><img src=".$img_anterior." border='0' align='absmiddle'></a>"; 			
		}
      
		$qtde_pg = ceil($total_table / $max); // Quantidade de pï¿½ginas que a query vai ter
		
		if(($range + ($range_qtde - 1)) > $qtde_pg) // Retorna a quantidade de pï¿½ginas de um range
			$limite = $qtde_pg;
		else
			$limite = ($range + $range_qtde - 1);

		for($s = $range ; $s <= $limite ; $s++) // Monta a paginaï¿½ï¿½o propriamente dita
		{
                        $esta_pag = ceil($inicial/$max)+1;
			$indice = (($s * $max) - ($max - 1)) - 1;
			if($esta_pag!=$s) echo "<a href=".$default_add."?inicial=".$indice."&range=".$range."&ncamp=".$ncamp.$varsel."&btPesquisar=Pesquisar"." class='link_azul'><font color='#00008C' size='1' face='Arial, Helvetica, sans-serif'>"; else echo "<b>";
			echo (($esta_pag!=$s)?"":"<span style='background-color:#FFFF00'>");
			echo "<font color='".(($esta_pag!=$s)?"#00008C":"#330000")."' size='1' face='Arial, Helvetica, sans-serif'>".$s."</font>";
			echo (($esta_pag!=$s)?"":"</span>");
			if($esta_pag!=$s) echo "</a>"; else echo "</b>";
			if($s != $limite)
				echo "<font color='#00008C' size='1' face='Arial, Helvetica, sans-serif'> | </font>";
		}
		
		if($limite < $qtde_pg) // Mostra a imagem de Prï¿½ximo
		{
			$next_range = $range + ($range_qtde - 1) + 1;
			$page_next = (($next_range * $max) - ($max - 1)) - 1;
			echo "<a href=".$default_add."?inicial=".$page_next.$varsel."&range=".$next_range."&ncamp=".$ncamp.$varsel."&btPesquisar=Pesquisar"."><img src=".$img_proxima." border='0' align='absmiddle'></a>";
		}
		echo "</td></tr>";
	}
}

function qtde_dias($data1, $data2) {
    
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
    $tempo_unix=$data_final - $data_inicial; // acha a diferenï¿½a de tempo
    $periodo=floor($tempo_unix /(24*60*60)); //conversï¿½o para dias. (Para anos adicione *365)

    if($periodo >= 0)
            return $periodo;
    else
            return -1;
}

function get_nivel($url) {
	$separada = explode("/", $url);

	$string = '';
	for($i = 0 ; $i < count($separada) - 2 ; $i++)
		$string .= "../";
	
	return $string;
}

function time2sec($h, $m, $s) {
	$sec = ($h * 60 * 60) + ($m * 60) + $s;

	return $sec;
}

function sec2time($s) {
	$m = $s / 60;
	$m = number_format($m, 2, '.', '');
	$m_dec = substr($m, strlen($m) - 2, 2);
	$sec = ($h * 60 * 60) + ($m * 60) + $s;

	return $time;
}

function regra3($total_conhecido, $parte_conhecida, $totalx) {
	$x = ($parte_conhecida * $totalx) / $total_conhecido;
	$x = number_format($x, 2, '.', '');
	
	return $x;
}

function time2min($horario) {
	$hora = substr($horario, 0, 2);
	$min = substr($horario, 2, 2);
	
	$minutos = (60 * $hora) + $min;
	return $minutos;
}

function min2time($min) {
	$hora = $min / 60;

	$time = $min;

	$hora = substr($horario, 0, 2);
	$min = substr($horario, 2, 2);
	
	$minutos = (60 * $hora) + $min;
	return $minutos;
}

function verifica_valor_moeda($val) {
	$valor = $val;
	$tam = strlen($valor);
	
	$virgula = substr($valor,$tam-3,1);
	if($virgula != "," && $virgula != ".")
		return 0;
	else
	{
		$antes = substr($valor,0,$tam-4);
		$depois = substr($valor,$tam-2,2);
		
		if(!valida_campo_numero($antes, 0))
			return 0;
		else
		{ 
			if(!valida_campo_numero($depois, 0))
				return 0;
			else
				return 1;
		}		
	}
}

function verificaCNPJ($string) {
	$RecebeCNPJ = $string;
	
	if(strlen($RecebeCNPJ) != 14 || $RecebeCNPJ == "00000000000000") {
		return 0;
	} else {
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
	
		if($resultado1 == $Numero[13]) {

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
			else {
				return 0;
			}
		} else {
			return 0;
		}

	}
}

function verificaCPF($cpf) {

	$RecebeCPF=$cpf;
	
	$RecebeCPF = str_replace(".", "", $RecebeCPF);
	$RecebeCPF = str_replace("-", "", $RecebeCPF);

	return verificaCPF2($RecebeCPF);	
}

function verificaCPF2($cpf) {

	$RecebeCPF=$cpf;

        if (strlen($RecebeCPF)!=11)
        { return 0; }
        else
        if ($RecebeCPF=="00000000000" || $RecebeCPF=="11111111111")
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

function mascara_cnpj_cpf($documento,$tipo) {
	$mask = $documento;
	$strtam=strlen($documento);
	if($tipo == 'cnpj')
	{
					$dv=substr($documento,$strtam-2,2);
					$dr=substr($documento,$strtam-6,4);
					$ca3=substr($documento,$strtam-9,3);
					$ca2=substr($documento,$strtam-12,3);
					$ca1=substr($documento,0,$strtam-12);

		$doc = "$ca1.$ca2.$ca3/$dr-$dv";
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

function coloca_char_esquerda($string, $qtde_total, $char) {
	$aux = $string;
	
	for($i = 1 ; $i <= ($qtde_total - strlen($aux)) ; $i ++)
	{ $completa .= $char; }
	$completa .= $aux;
	return $completa;
}

function coloca_char_direita($string, $qtde_total, $char) {
	$aux = $string;
	
	for($i = 1 ; $i <= ($qtde_total - strlen($aux)) ; $i ++)
	{ $completa .= $char; }
	$aux .= $completa;
	return $aux;
}

function gera_string($inicio, $tamanho_rand, $tipo) {
	if($tipo == 'A')
	{ $string = "A1B2C3D4E5F6G7H8I9J0K1L2M3N4O5P6Q7R8S9T0U1V2W3X4Y5Z6"; }
	
	if($tipo == 'N')
	{ $string = "1234567890"; }

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

function formata_data($data,$gravar) {
	$mask = $data;
	if($gravar == 0)
	{
		$dia = substr($mask,8,2);
		$mes = substr($mask,5,2);
		$ano = substr($mask,0,4);
		$doc = $dia."/".$mes."/".$ano;
	}
	
	if($gravar == 1)
	{
		$dia = substr($mask,0,2);
		$mes = substr($mask,3,2);
		$ano = substr($mask,6,4);
		$doc = $ano."-".$mes."-".$dia;
	}
	return $doc;
}

function verifica_valor($val) {
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

function valida_campo_numero($numero, $tamanho = 0) {
	$verifica = $numero;
	
	if(strlen($numero) < $tamanho)
		return 0;
	else
	{	
		for ($y = 1 ; $y <= strlen($verifica) ; $y += 1)
		{ 
			$ch = substr($verifica, $y-1, 1); 
			if(ord($ch) >= 48 && ord($ch) <= 57)
				$alerta = 0;
			else
			{
				$alerta = 1;
				break;
			}
		}
	
		if($alerta == 0)
			return 1;
		else
			return 0;
	}
}

function resgata_string($string, $pos) {
	$aux = $string;
	$fim = substr($aux, strlen($aux) - ($pos - 1), $pos - 1);
	$inicio = substr($aux, 0, strlen($aux) - ($pos - 1));
	
	$fone_formatado = $inicio."-".$fim;
	return $fone_formatado;
}

function verifica_email($email) {
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

function data_mais_um($data) {
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
	
	if($mes_num == 31) // Janeiro - Marï¿½o - Maio - Julho - Agosto - Outubro - Dezembro 
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

function data_mais_n($data, $qtde_dias) {
	$aux = $data;
	for($i = 1 ; $i <= $qtde_dias ; $i++)
	{
		$aux = data_mais_um($aux);
	}
	$data_somada = $aux;
	return $data_somada;
}

function data_menos_um($data) {
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

function data_menos_n($data, $qtde_dias) {
	$aux = $data;
	for($i = 1 ; $i <= $qtde_dias ; $i++)
	{
		$aux = data_menos_um($aux);
	}
	$data_decrementada = $aux;
	return $data_decrementada;
}

function nome_arquivo($url) {
    $separada = explode("/", $url);
    $reverso = array_reverse($separada);
	
	return $reverso[0];
}

function verifica_telEx($tel, $blComTraco = true) {

	if($blComTraco){
		return eregi("^[0-9]{4}-[0-9]{4}$", $tel);
	}else{
		return eregi("^[0-9]{8}$", $tel);
	}
	
}

function verifica_tel($tel) {
	$aux = $tel;
	$tam = strlen($aux);
        if($tam < 8)
        { return 0; }
        else {
                if($tam == 9) {
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

function monta_ddd_string($campoddd) {
	$recebeDDD = $campoddd;
	$recebeDDD .= ";";
    for ($x = 1 ; $x <= strlen($recebeDDD) ; $x += 1)
    {
       $ch = substr($recebeDDD,$x-1,1);
       if(ord($ch) >= 48 && ord($ch) <= 57)
	   { $alerta = 1; break; } // Se a string tiver nï¿½mero 
	   else 
	   { $alerta = 0; } // Se a string Nï¿½O tiver nï¿½mero
	}
	
        if($alerta == 1) // Se a string tiver nï¿½mero
        {
           $aux_tr = 0;
           $aux_pv = 0;
           $traco = "";
           for ($y = 1 ; $y <= strlen($recebeDDD) ; $y += 1)
           {
                  $cha = substr($recebeDDD,$y-1,1); 
                  if(ord($cha) == 45)   // Verifica se a string tem traï¿½o      
              { 
                         $traco = $aux_tr; 
                     $pos = $traco-2;
                         $inicial = substr($recebeDDD,$pos,2);          // Indica o DDD inicial
                     $final = substr($recebeDDD,$traco+1,2);      // Indica o DDD final
                            for($s = $inicial ; $s <= $final ; $s++) {      // Monta a sequï¿½ncia dos DDDï¿½s entre inicial e final
                               $var .= $s.";"; }
                  }
                  $aux_tr += 1;
           }
           for ($t = 1 ; $t <= strlen($recebeDDD) ; $t += 1)
           {
                  $char = substr($recebeDDD,$t-1,1); 
                  if(ord($char) == 59)   // Verifica se a string tem ponto e vï¿½rgula      
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

}

function valida_ddd_string($ddd) {
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

function verifica_data($data) {
	$aux = $data;
	$tam = strlen($aux);
        if($tam < 10)
        { return 0; }
        else {
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

function verifica_tel_ddd($tel) {
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

                                    }// fechamnento do else do $inicial nï¿½o ï¿½ nï¿½mero								
                            } // fechamnento do else do $ddd nï¿½o ï¿½ nï¿½mero
                            } // fechamento do else do $traco																																
                } // fechamento do else do $espaï¿½o
            } // fechamento da condiï¿½ï¿½o ($tam == 12)
        } // fechamento do else do $tam

}

function Modulo10($string) {
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

function valida_dv($codigo, $tipo_codigo) {
	if($tipo_codigo == 'estab')
	{
		if(strlen($codigo) < 10 || substr($codigo, 0, 1) != 9)
			return 0;
		else
		{
			$codigo_inic = substr($codigo, 1, 8);
			$dv_digitado = substr($codigo, 9, 1);
		}
	}
	
	if($tipo_codigo == 'term')
	{
		if(strlen($codigo) < 9)
			return 0;
		else
		{
			$codigo_inic = substr($codigo, 0, 8);
			$dv_digitado = substr($codigo, 8, 1);
		}
	}
	
	$dv_certo = Modulo10($codigo_inic);
	
	if($dv_certo == $dv_digitado)
		return 1;
	else
		return 0;

}

function formata_string($string, $caracter, $pos) {
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

function mascara_cnpj($cnpj) {
		$mask = $cnpj;
		$var1 = substr("$mask", 0,2);
		$var2 = substr("$mask", 2,3);  
		$var3 = substr("$mask", 5,3);  
		$var4 = substr("$mask", 8,4);      
		$var5 = substr("$mask", 12,2);  
		$doc = $var1.".".$var2.".".$var3."/".$var4."-".$var5;
		return $doc;
}

function mascara_cpf($cpf) {
		$mask = $cpf;
		$var1 = substr("$mask", 0,3);
		$var2 = substr("$mask", 3,3);  
		$var3 = substr("$mask", 6,3);  
		$var4 = substr("$mask", 9,2);      
		$doc = $var1.".".$var2.".".$var3."-".$var4;
		return $doc;
}

function monta_data($date) {
	$mask = $date;
	$dia = substr($mask,8,2);
	$mes = substr($mask,5,2);
	$ano = substr($mask,0,4);
	$doc = $dia."/".$mes."/".$ano;
	return $doc;
}

function monta_data_gravacao($date) {
	$mask = $date;
	$dia = substr($mask,0,2);
	$mes = substr($mask,3,2);
	$ano = substr($mask,6,4);
	$doc = $ano."-".$mes."-".$dia;
	return $doc;
}

function monta_valor($pin_valor_total) {
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

function limpa_string($valor) {
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

function valida_valor_opr($val) {
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

function organiza_casa($valor) {
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

function define_casa_decimal($valor, $qtde) {
	$aux = $valor;
	$contador = 0;
	for ($x = 1 ; $x <= strlen($aux) ; $x++) {
		$pos = substr($aux,$x-1,1);
		if(ord($pos) == 46)
			{ $alerta = 1; break; }
		else
			{ $alerta = 0;}
		$contador ++;
	}
        if($alerta == 1) {
                $antes_virg = substr($aux,0,$contador);
                $pos_peg = $contador + 1;
                $dep_virg = substr($aux,$pos_peg,$qtde);
                        if(strlen($dep_virg) == 1) { $dep_virg .= '0'; }
        }
        $valor_final = $antes_virg.",".$dep_virg;
        return $valor_final;
}

function grava_comissao_banco($comissao) {
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

function verifica_cepEx($cep, $blComTraco = true) {

	if($blComTraco){
		return preg_match("/^[0-9]{5}-[0-9]{3}$/i", $cep);
	}else{
		return preg_match("/^[0-9]{8}$/i", $cep);
	}
	
}

function verifica_cep($cep) {
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

function moeda_char($string) {
	$aux = $string;
	$contador = 0;
	for ($x = 1 ; $x <= strlen($aux) ; $x++) {
		$pos = substr($aux,$x-1,1);
		if(ord($pos) == 46)
			{ $alerta = 1; break; }
		else
			{ $alerta = 0;}
		$contador ++;
	}
	
        if($alerta == 1) {
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
        else { 
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


# Esta funï¿½ï¿½o recebe um valor sem casa decimal, apenas nï¿½meros
# e retorna um valor formatado em Moeda
# ENTRADA: 964567
# SAï¿½DA: 9.645,67
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

function gera_id() {
    $id = date('ymdHis') . coloca_char_esquerda(rand(1,99999), 5, '0');
    return $id;
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
												$var_descricao) {
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
        //							# serï¿½ transformado para positivo.
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

function gravaLog_EstabelecimentoMovimentacao($EM_mensagem) {

	//Arquivo
	$file = $GLOBALS['raiz_do_projeto'] . 'log/log_estabelecimento_movimentacao.txt';

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

// 15/02/2006 - Karlos Fernandes
// Funï¿½ï¿½o criada para Timestamp
// 0 apresenta data XX/XX/XXXX
// 1 apresenta hora YY:YY:YY
// 2 apresenta timestamp XX/XX/XXXX as YY:YY:YY


function formata_timestamp($data,$gravar) {
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
		$doc = $dia."/".$mes."/".$ano." as ".$hora.":".$min.":".$seg;
	}
	return $doc;
}

function imprimeComboSeuBanco($mensagem) {
	//URL Bancos
	$URL_BANCOS[0] = array("Bradesco Pessoa Fï¿½sica", "http://www.bradesco.com.br/");
	$URL_BANCOS[1] = array("Bradesco Pessoa Jurï¿½dica", "http://www.bradesco.com.br/br/pj/default.shtm?paramPag=AbaPFPJ");
	$URL_BANCOS[2] = array("Banco do Brasil Pessoa Fï¿½sica", "https://www2.bancobrasil.com.br/aapf/aai/login.pbk?loginSCD=true");
	$URL_BANCOS[3] = array("Banco do Brasil Pessoa Jurï¿½dica", "https://office.bancobrasil.com.br/servlet/carregaoffice");
	//$URL_BANCOS[4] = array("Caixa Econï¿½mica Federal", "https://internetcaixa.caixa.gov.br/NASApp/SIIBC/index_verif.processa");
?>
		<script language="javascript">
		<!--
		function fcnSeuBanco(form) { //v1.0
		
			if(form.seuBanco.value == ''){
				alert('Selecione seu banco');
			} else {
				window.open(form.seuBanco.value,'banco','');
			}
		}
		//-->
		</script>
	  	<form name="formSeuBanco" method="post" action="">
			<table border="0" cellspacing="0" cellpadding="0" align="center">
                <?php if($mensagem != ""){?><tr align="center"><td><font size='2' face='Arial, Helvetica, sans-serif' color='#000099'><?php echo $mensagem ?></font></td></tr><?php }?>
                <tr align="center"> 
                  <td>
					<select name="seuBanco">
						<option value="">Selecione seu Banco</option>
						<?php for($i = 0; $i < count($URL_BANCOS); $i++) {?>
							<option value="<? echo $URL_BANCOS[$i][1] ?>"><?php echo $URL_BANCOS[$i][0] ?></option>
						<?php }?>
					</select>
					<input name="btnSeuBanco" type="button" class="<?php if( $GLOBALS['_SESSION']['is_integration'] == true) echo "cleanLeftMargin botao_simples int-btn1 grad1"; else echo"botao_amex"; ?>" value="Ir para seu Banco" onClick="fcnSeuBanco(document.formSeuBanco);">
					</td>
                </tr>
              </table>
		</form>

<?php
}

function gravaLog_PagtoPINEPP($mensagem) {
	//Arquivo
	$file = $GLOBALS['raiz_do_projeto'] . "log/log_PagtoPINEPP.txt";

	//Mensagem
	$mensagem = date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . "\n" . $mensagem . "\n";

	//Grava mensagem no arquivo
	if ($handle = fopen($file, 'a+')) {
		fwrite($handle, $mensagem);
		fclose($handle);
	} 
}

//Funï¿½ï¿½o que busca todos os Publishers que fazem o fechamento pelo data de utilizaï¿½ï¿½o do PIN
function levantamentoPublisherComFechamentoUtilizacao() {

        // Buscando informaï¿½ï¿½es 
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
            echo "Erro na Query de Levantamento de Publishers contendo Fechamento por Utilizaï¿½ï¿½o de PINs(".$sql.").<br>".PHP_EOL;
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

//Funï¿½ï¿½o que busca todos os Publishers que fazem o fechamento pelo data de utilizaï¿½ï¿½o do PIN para Publisher Internacionais
function levantamentoPublisherComFechamentoUtilizacaoInternacional() {

        // Buscando informaï¿½ï¿½es 
        $sql = "select 
                        opr_codigo, 
                        opr_data_inicio_contabilizacao_utilizacao
                from operadoras
                where 
                        opr_contabiliza_utilizacao != 0
                        and opr_vinculo_empresa = ".$GLOBALS['IDENTIFICACAO_EMPRESA_ADMINISTRADORA_CARTAO']." 
                        and opr_data_inicio_operacoes is not null
                        and opr_internacional_alicota != 0
                order by opr_codigo
                ";

        //echo $sql.PHP_EOL; die();
        $rs_publisher = SQLexecuteQuery($sql);
        //echo pg_num_rows($rs_publisher)."<br>";
        if(!$rs_publisher) {
            echo "Erro na Query de Levantamento de Publishers contendo Fechamento por Utilizaï¿½ï¿½o de PINs(".$sql.").<br>".PHP_EOL;
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
    
}//end function levantamentoPublisherComFechamentoUtilizacaoInternacional()

//Funï¿½ï¿½o que busca todos os Publishers que fazem o fechamento pelo data de utilizaï¿½ï¿½o do PIN para Publisher com compï¿½liance Municipal => Cidade: Sï¿½o Paulo
function levantamentoPublisherComFechamentoUtilizacaoMunicipal() {

        // Buscando informaï¿½ï¿½es 
        $sql = "select 
                        opr_codigo, 
                        opr_data_inicio_contabilizacao_utilizacao
                from operadoras
                where 
                        opr_contabiliza_utilizacao != 0
                        and opr_vinculo_empresa = ".$GLOBALS['IDENTIFICACAO_EMPRESA_ADMINISTRADORA_CARTAO']." 
                        and opr_data_inicio_operacoes is not null
                        and opr_internacional_alicota = 0
                        and UPPER(opr_estado) = 'SP'
                        and UPPER(TRIM(opr_cidade)) = UPPER('Sao Paulo')
                order by opr_codigo
                ";

        //echo $sql.PHP_EOL; die();
        $rs_publisher = SQLexecuteQuery($sql);
        //echo pg_num_rows($rs_publisher)."<br>";
        if(!$rs_publisher) {
            echo "Erro na Query de Levantamento de Publishers contendo Fechamento por Utilizaï¿½ï¿½o de PINs(".$sql.").<br>".PHP_EOL;
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
    
}//end function levantamentoPublisherComFechamentoUtilizacaoMunicipal()

// Funï¿½ï¿½o que valida o CPF em relaï¿½ï¿½o a sua estrutura e digitos verificadores
function validaAlgoritimoCPF($cpf) {
        $cpf = str_replace(".","",str_replace("-","",$cpf));
        if($cpf == '') return false;

        // Elimina CPFs invalidos conhecidos
        if (strlen($cpf) != 11 ||
                $cpf == '00000000000' ||
                $cpf == '11111111111' ||
                $cpf == '22222222222' ||
                $cpf == '33333333333' ||
                $cpf == '44444444444' ||
                $cpf == '55555555555' ||
                $cpf == '66666666666' ||
                $cpf == '77777777777' ||
                $cpf == '88888888888' ||
                $cpf == '99999999999')
                return false;

        // Valida 1o digito
        $add = 0;
        for ($i=0; $i < 9; $i ++)
                $add += (substr($cpf,$i,1) * (10 - $i));
        $rev = 11 - ($add % 11);
        if ($rev == 10 || $rev == 11)
                $rev = 0;
        if ($rev != (substr($cpf,9,1)*1))
                return false;

        // Valida 2o digito
        $add = 0;
        for ($i = 0; $i < 10; $i ++)
                $add += substr($cpf,$i,1) * (11 - $i);
        $rev = 11 - ($add % 11);
        if ($rev == 10 || $rev == 11)
                $rev = 0;
        if ($rev != (substr($cpf,10,1)*1))
                return false;

        return true;
}//end validaAlgoritimoCPF()

function modal_includes($fancybox=true){
    $url = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=="on") ? "s" : "") . '://' . $_SERVER['SERVER_NAME'];
            
    $html = '';
    
    if($fancybox){
        $html .= '<link rel="stylesheet" href="'.$url.'/js/fancybox/jquery.fancybox.css" type="text/css" />' . PHP_EOL;
        $html .= '<script src="'.$url.'/js/fancybox/jquery.fancybox.js"></script>' . PHP_EOL;    
    }
    
    
    $html .= '<link rel="stylesheet" href="'.$url.'/css/modal.css" type="text/css" />' . PHP_EOL;
    $html .= '<script src="'.$url.'/js/modal.js"></script>' . PHP_EOL;
            
    echo $html;
}

function fix_name($str){
    $name = explode(' ', strtolower($str));
    foreach( $name as $k=>$n ){
        if(strlen($n)<=2)
            continue;
        
       $name[$k] = ucfirst($n);
    }
    return implode(' ', $name);
}

function get_day_of_week($date1) {
    require_once RAIZ_DO_PROJETO . "public_html/sys/includes/language/eprepag_lang_pt.inc.php";
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

function get_day_of_week_short($date1) {

    $dia_semana = "???";
    $dia_ingles = date("w", strtotime($date1));
//echo "$date1 -> $dia_ingles";
//echo "($dia_ingles) ";

    switch($dia_ingles) 
    {
            case "1": $dia_semana = "2aF"; break; 
            case "2": $dia_semana = "3aF"; break; 
            case "3": $dia_semana = "4aF"; break; 
            case "4": $dia_semana = "5aF"; break; 
            case "5": $dia_semana = "6aF"; break; 
            case "6": $dia_semana = "Sab"; break; 
            case "0": $dia_semana = "Dom"; break; 
    }
    return $dia_semana;
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

//Gerador de LOG de alteraï¿½ï¿½es dos manuais
function gravaLog_Manuais($mensagem) {
    //Arquivo
    $file = $GLOBALS['raiz_do_projeto'] . "log/log_manuais.txt";
    //Mensagem
    $mensagem = date('Y-m-d H:i:s') . " " . $_SERVER["SCRIPT_FILENAME"] . "\n" . $mensagem . "\nDEBUG:\n".print_r(debug_backtrace(),true)."\n";
    //Grava mensagem no arquivo
    if ($handle = fopen($file, 'a+')) {
            fwrite($handle, $mensagem);
            fclose($handle);
    } 
}//end function gravaLog_SQLexecuteQuery($mensagem)
?>