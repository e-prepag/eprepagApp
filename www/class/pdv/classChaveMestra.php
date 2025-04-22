<?php

require_once "/www/db/connect.php";
require_once "/www/db/ConnectionPDO.php";

class ChaveMestra{

    private $conexao;

    public function __construct(){
		
		$conexao = ConnectionPDO::getConnection();
		$this->conexao = $conexao->getLink();
		
	}	
	
	public function verificaSenha($usuario, $senha){
	
		$sql = "select count(*) as quantidade from dist_usuarios_games_chave where usuario = :USUARIO and chave = :SENHA;";
		$query = $this->conexao->prepare($sql);
		$query->bindParam(":USUARIO", $usuario);
		$query->bindParam(":SENHA", $senha);
		$query->execute();
		$rowChave = $query->fetch(PDO::FETCH_ASSOC);
		
		$file = fopen("/www/log/chave_mestra.txt", "a+");
		fwrite($file, "data: " .date("d-m-Y H:s:s")."\n"); 
		fwrite($file, "usuario: " .$usuario."\n");
	//	fwrite($file, "senha: " .$senha."\n");
		fwrite($file, "dados: " .json_encode($rowChave)."\n");
	    fwrite($file, str_repeat("*", 60)."\n");
		fclose($file);
		
	    return $rowChave["quantidade"];
	}
	
	public function inserirSeguro($liberado, $usuario){
		
		$sql = "select usuario from dist_usuarios_games_chave_seguro where usuario = :USUARIO and ip = :IP;";
		$query = $this->conexao->prepare($sql);
		$query->bindValue(":USUARIO", $usuario);
		$query->bindValue(":IP", $_SERVER["REMOTE_ADDR"]);
		$query->execute();
		$rowSeguro = $query->fetch(PDO::FETCH_ASSOC);
		
		if($rowSeguro == false){
			
			$sql = "insert into dist_usuarios_games_chave_seguro(ip,liberado,usuario)values(:IP,:LIBERADO,:USUARIO);";
			$query = $this->conexao->prepare($sql);
			$query->bindValue(":IP", $_SERVER["REMOTE_ADDR"]);
			$query->bindValue(":LIBERADO", $liberado);
			$query->bindValue(":USUARIO", $usuario);
			$query->execute();
			
			if($query->rowCount() > 0){
				return true;
			}
			
			return false;
			
		}else{
			return true;
		}
		
	}
	
	public function inserirChaveMestra($usuario){
		
		$chave = $this->gerarSenha();
		$retorno = $this->verificaSenha($usuario, $chave);
		
		if($retorno == 0){
			
			$sql = "select usuario from dist_usuarios_games_chave where usuario = :USUARIO;";
			$query = $this->conexao->prepare($sql);
			$query->bindValue(":USUARIO", $usuario);
			$query->execute();
			$rowUsuario = $query->fetch(PDO::FETCH_ASSOC);
			
			if($rowUsuario != false){
			     return false;
			}else{
				
				$sql = "insert into dist_usuarios_games_chave(usuario,chave)values(:USUARIO,:CHAVE);";
				$query = $this->conexao->prepare($sql);
				$query->bindValue(":USUARIO", $usuario);
				$query->bindValue(":CHAVE", $chave);
				$query->execute();
				
				if($query->rowCount() > 0){
					
					$sql = "select chave from dist_usuarios_games_chave where usuario = :USUARIO;";
					$query = $this->conexao->prepare($sql);
					$query->bindValue(":USUARIO", $usuario);
					$query->execute();
					$rowChave = $query->fetch(PDO::FETCH_ASSOC);
					
					return $rowChave["chave"];
				}
				
				return false;
				
			}
			
		}
	
	}
	
	private function verificaSeguro(){
				
		$sql = "SELECT liberado FROM dist_usuarios_games_chave_seguro where ip = :IP;";
		$query = $this->conexao->prepare($sql);
		$query->bindValue(":IP", $_SERVER["REMOTE_ADDR"]);
		$query->execute();
		$rowIP = $query->fetch(PDO::FETCH_ASSOC);
		
		if($rowIP != false){
			if($rowIP["liberado"] == "S"){
				return true;
			}
			return false;
		}
		
		return false;
		
	}
	
	private function gerarSenha(){
		
		$tamanho = 15;
		$posibilidades = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz@*{}";
		$chaveFinal = "";
		
		for($num = 0; $num < $tamanho; $num++){
			
			$letra = $posibilidades[rand(0, (strlen($posibilidades) - 1))];
			$chaveFinal .= $letra;
			
		}
		
		return $chaveFinal;
	}
	
	public function verificarIPUtilizado($usuario){
		// Leva em consideração a quatidade de utilização nos ultimos 7 dias ordernando pela maior utilização que tenha pedido vinculado
        $sql = "select count(*) as qtde, ugl_ip from dist_usuarios_games_log where ugl_ug_id = :USUARIO and ugl_data_inclusao >= (CURRENT_TIMESTAMP - INTERVAL '7 day') and ugl_uglt_id = 5 group by ugl_ip order by qtde desc limit 1;";
		$query = $this->conexao->prepare($sql);
		$query->bindValue(":USUARIO", $usuario);
		$query->execute();
		$rowIP = $query->fetch(PDO::FETCH_ASSOC);
			
		if($rowIP != false){
			if($_SERVER["REMOTE_ADDR"] == $rowIP["ugl_ip"]){
			
			    return true;
		    }else{
			    return $this->verificaSeguro();		
			}
		     
	    }else{
			return false; 
	    }
		
	}
	
}

?>