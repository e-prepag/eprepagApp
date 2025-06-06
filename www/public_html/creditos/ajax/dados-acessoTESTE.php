<?php

// TESTE PDV


/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once "../../../includes/constantes.php";
require_once DIR_CLASS . "pdv/controller/OffLineController.class.php";

$controller = new OfflineController;
$retorno = new stdClass();
$retorno->erro = '';
$retorno->sucesso = false;

if(Util::isAjaxRequest()){
    if($_POST['type'] == "esqueciMinhaSenha"){
		
		function verificaPOST($referer,$POST){
					
			//if (strpos($referer,"bronzato.com.br")===false && strpos($referer,"contause.digital")===false){return false;}
			$flag=true;
			foreach($_POST as $xa=>$xb){
				$xb = serialize($xb);
				if (strpos($xb,"dbms_pipe.receive_message")!==false || strpos($xb,"DBMS_PIPE.RECEIVE_MESSAGE")!==false || strpos($xb,"delete")!==false || strpos($xb,"delete")!==false || strpos($xb,"update")!==false || strpos($xb,"select")!==false ){
						return false;
				}
				
				if (strpos($xb,"dbms_pipe.receive_message")!==false || strpos($xb,"DBMS_PIPE.RECEIVE_MESSAGE")!==false ||strpos(hexToStr($xb),"delete")!==false || strpos(hexToStr($xb),"update")!==false || strpos(hexToStr($xb),"select")!==false ){
						return false;
				}
			}
			
			if ($flag){return true;}else{return false;}
		}

		function strToHex($string){
			$hex = '';
			for ($i=0; $i<strlen($string); $i++){
				$ord = ord($string[$i]);
				$hexCode = dechex($ord);
				$hex .= substr('0'.$hexCode, -2);
			}
			return strToUpper($hex);
		}

		function hexToStr($hex){
			$string='';
			for ($i=0; $i < strlen($hex)-1; $i+=2){
				$string .= chr(hexdec($hex[$i].$hex[$i+1]));
			}
			return $string;
		}
		
		if(!verificaPOST("", $_POST)){ 
		    
			/*$ff = fopen("/www/log/aaa.txt", "a+");
			fwrite($ff, (verificaPOST("", $_POST) == true)? 1: 0);
			fclose($ff);*/
		
			$retorno->erro = htmlentities("Nao foi possivel continuar seu processo.");
		}else{
			if(isset($_POST['login']) && $_POST['login'] != "" && strlen($_POST['login']) <= 100 && $controller->relembraSenha($_POST)){
				$retorno->sucesso = true;
				
			}else{
				$retorno->erro = htmlentities("Login inválido ou não encontrado.");
			}
		}
    }
    
    print json_encode($retorno);
    
}else{
    print "Acesso negado.";
}