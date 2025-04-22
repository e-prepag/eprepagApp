<?php


//require '/www/includes/constantes.php';
//require "/www/db/connect.php";
//require "/www/db/ConnectionPDO.php";	
//require "/www/consulta_cpf/config.inc.cpf.php";
require "/www/sftp/connect.php";
require "/www/sftp/classSFTPconnection.php";

function verificaContagem(){
	$conexao = ConnectionPDO::getConnection()->getLink();
	$sql = "select contagem from trocaAutomaticaConsulta where date(data_inclusao) = CURRENT_DATE;";
	$comando = $conexao->prepare($sql);
	$comando->execute();
    $resultado = $comando->fetch(PDO::FETCH_ASSOC);
	return $resultado;
}

function qtdeTrocaAutomatica(){
	$conexao = ConnectionPDO::getConnection()->getLink();
    $resultado = verificaContagem();	
	if($resultado == false){
		$sql = "insert into trocaAutomaticaConsulta(contagem,data_inclusao)values(1, CURRENT_TIMESTAMP);";
		$comando = $conexao->prepare($sql);
		$comando->execute();
		$resultado = $comando->fetch(PDO::FETCH_ASSOC);
		return 1;
	}else{
		$sql = "update trocaAutomaticaConsulta set contagem = contagem + 1 where date(data_inclusao) = CURRENT_DATE;";
		$comando = $conexao->prepare($sql);
		$comando->execute();
	    $resultado = verificaContagem();	
		return $resultado["contagem"];
	}	
}

function trocaOrigemAutomatica($environment){
	
	    if(isset($vetorReverso) && isset($vetorLegenda)){
			 global $vetorReverso;
		     global $vetorLegenda;
		}else{
			$vetorLegenda = array(
				CPF_PARTNER_CREDIFY => 'CREDIFY',
				CPF_PARTNER_OMNIDATA => 'OMNIDATA',
				CPF_CONSULTA_CACHE => 'Nosso CACHE',
				CPF_CONSULTA_HUB => 'Hub do Densenvolvedor',
				CPF_PARTNER_CAF => 'CAF'
			);

			//Definindo Vetor Reverso
			$vetorReverso = array(
				CPF_PARTNER_CREDIFY => 'CPF_PARTNER_CREDIFY',
				CPF_PARTNER_OMNIDATA => 'CPF_PARTNER_OMNIDATA',
				CPF_CONSULTA_CACHE => 'CPF_CONSULTA_CACHE',
				CPF_CONSULTA_HUB => 'CPF_CONSULTA_HUB',
				CPF_PARTNER_CAF => 'CPF_PARTNER_CAF'
			);
		}
		
		/*
	   
		$conteudoArquivo = '<?php
			// '.date('Y-m-d H:i:s').'
			// Constante que define o Parceiro de Integração. Onde (CREDIFY = 1) ou (OMNIDATA = 2) ou (Consulta CACHE = 3)
			define("CPF_PARTNER_ENVIRONMET",'.$vetorReverso[$environment].');
			?>';
			
        $newfile = fopen("/www/consulta_cpf/environment.cpf.php", 'w');
        if(fwrite($newfile, $conteudoArquivo)) {
            $msg = "Sucesso na atualização das configurações!<br>O novo ambiente de consulta é ".$vetorLegenda[$environment]."!";
            fclose($newfile);
            $nome_arquivo = "environment.cpf.php";
            $arquivo = "/www/consulta_cpf/".$nome_arquivo;
            if(SFTP_TRANSFER && file_exists($arquivo)){
                $arq = trim(str_replace('/', '\\', $arquivo)); 
                
                //enviar para os servidores via sFTP
                $sftp = new SFTPConnection($server, $port);
                $sftp->login($user, $pass);
                $sftp->uploadFile("/www/consulta_cpf/".$nome_arquivo, "E-Prepag/www/web/prepag2/consulta_cpf/".$nome_arquivo);

                //$msg .= "<br><br>Arquivo de configuração enviado ao servidor Windows 2003";
            }
                
        }
		
		apagaTroca();
		
		*/
		
        //else $msg = "Erro ao salvar as configurações contacte o Administrador imediatamente!";
}

function apagaTroca(){
	$conexao = ConnectionPDO::getConnection()->getLink();
	$sql = "delete from trocaAutomaticaConsulta where date(data_inclusao) = CURRENT_DATE;";
	$comando = $conexao->prepare($sql);
	$comando->execute();
	
	return $comando->rowCount();
}

?>