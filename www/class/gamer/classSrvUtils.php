<?php

class Utils {
	
	
	private function __construct() { }
	
	/**
	 * Retorna o Usuário Gamer Logado na Sessão
	*/	
	static public function getUsuarioGamerSessao() {
	
		if(isset($_SESSION['usuarioGames_ser'])) {
			return unserialize($_SESSION['usuarioGames_ser']);
		}
		else {
			return null;
		}	
	}
	
	
	/**
	 * Registra Logs de Eventos em Arquivos Texto
	 *
	 * @param String $arquivo
	 * @param String $mensagem
	*/	
	static public function logEvent($arquivo, $mensagem) {

		$log  = "DATA -> ".date("d/m/Y - H:i:s")."\n";
		$log .= $mensagem;
		$log .= "-------------------------------------------------------------------------------------------------\n";
		
		$fp = fopen($arquivo, 'a+');				
		fwrite($fp, $log);
		fclose($fp);				
	}
	
	
	/**
	 * Tranforma um Array em JSON
	 * 
	 * @param Array $array
	*/
	static public function ArrayToJSON($array) {
	
		if( !is_array( $array ) ){
			return false;
		}
	
		$associative = count( array_diff( array_keys($array), array_keys( array_keys( $array )) ));
		if( $associative ){
	
			$construct = array();
			foreach( $array as $key => $value ){
	
				// We first copy each key/value pair into a staging array,
				// formatting each key and value properly as we go.
	
				// Format the key:
				if( is_numeric($key) ){
					$key = "key_$key";
				}
				$key = "\"".addslashes($key)."\"";
	
				// Format the value:
				if( is_array( $value )){
					$value = Utils::ArrayToJSON( $value );
				} else if( !is_numeric( $value ) || is_string( $value ) ){
					$value = "\"".addslashes($value)."\"";
				}
	
				// Add to staging array:
				$construct[] = "$key: $value";
			}
	
			// Then we collapse the staging array into the JSON form:
			$result = "{ " . implode( ", ", $construct ) . " }";
	
		} else { // If the array is a vector (not associative):
	
			$construct = array();
			foreach( $array as $value ){
	
				// Format the value:
				if( is_array( $value )){
					$value = Utils::ArrayToJSON( $value );
				} else if( !is_numeric( $value ) || is_string( $value ) ){
					$value = "'".addslashes($value)."'";
				}
	
				// Add to staging array:
				$construct[] = $value;
			}
	
			// Then we collapse the staging array into the JSON form:
			$result = "[ " . implode( ", ", $construct ) . " ]";
		}
		
		return $result;	
	}	
		
}

?>