<?php 
	set_time_limit ( 18000 ) ;

	$run_silently = "OK";
	require_once '../../../includes/constantes.php';
    require_once $raiz_do_projeto."backoffice/includes/topo_bko_inc.php";
    require_once $raiz_do_projeto."includes/main.php";
?>
<?php



	if($BtnSearch=="Buscar") {
		$inicial     = 0;
		$range       = 1;
		$total_table = 0;
		$limit = 0;
	}

//echo "tf_ativo: ".$tf_ativo."<br>";
	if(!isset($BtnSearch)){
		$tf_ativo="1";
	}
//echo "tf_ativo: ".$tf_ativo."<br>";

	$default_add  = nome_arquivo($PHP_SELF);

	if(isset($BtnSearch)){
	
		//Validacao
		//------------------------------------------------------------------------------------------------------------------
		$msg = "";


		//Busca emails
		//------------------------------------------------------------------------------------------------------------------
		if($msg == ""){

			$filtro = array();
			if($tf_n) 		$filtro['n'] = $tf_n;
			if($tf_email) 	$filtro['email'] = $tf_email;
			if($tf_tipo) 	$filtro['tipo'] = $tf_tipo;
			if($tf_ativo =='1'|| $tf_ativo=='2') $filtro['ativo'] = $tf_ativo;	
			if($dd_opr_codigo) $filtro['dd_opr_codigo'] = $dd_opr_codigo;
			//if(			)	$filtro[''] = 
			
			if($tf_v_data_inclusao_ini && $tf_v_data_inclusao_fim) {
				$filtro['data_inclusao_ini'] = formata_data($tf_v_data_inclusao_ini,1);
				$filtro['data_inclusao_fim'] = formata_data($tf_v_data_inclusao_fim,1);
			}
			

			if ($tf_produto && is_array($tf_produto)) {
					if (count($tf_produto) == 1) {
					$tf_produto = $tf_produto[0];
					} else {
					$tf_produto = implode("|",$tf_produto);
					}	
				}
			if ($tf_produto && $tf_produto != "") {
				$tf_produto = explode("|",$tf_produto);	
			}

			$i = 0;
			$num_col = count($tf_produto);
			while ($i < $num_col) {
				
				$filtro['produto'.$i] = $tf_produto[$i];

				$i++;

			}

			/////////////////////////// PIN////////////////////////

			if ($tf_pins && is_array($tf_pins)) {
					if (count($tf_pins) == 1) {
					$tf_pins = $tf_pins[0];
					} else {
					$tf_pins = implode("|",$tf_pins);
					}	
				}
			if ($tf_pins && $tf_pins != "") {
				$tf_pins = explode("|",$tf_pins);	
			}

			$i = 0;
			$num_col_pin = count($tf_pins);
			while ($i <= $num_col_pin) {
				
				$filtro['pin'.$i] = $tf_pins[$i];

				$palavra = urlencode($filtro['pin'.$i]);

				$varsel .= "&tf_pins[]=".$palavra;
//echo $varsel;
				$i++;

			}

			 include $raiz_do_projeto . "includes/gamer/inc_newsletter_obter.php";
//$filtro['produto0'] = '';
//echo "<pre>".print_r($filtro,true)."</pre>";
//die("AKI");

			///////////////////////////////////////////////////////////

			$rs_newsletter = null;
			$orderBy = " order by tipo, email "; 

			$ret = obter($filtro, $orderBy, null, $rs_newsletter);
			
				
		}
	}
	
	

 $ordem = ($ordem == 1)?2:1; 
                      
						$mensagem = "";

						$mensagem .= "Tipo\tEMail\tN\tAtivo" . PHP_EOL . PHP_EOL;

						while($rs_newsletter_row = pg_fetch_array($rs_newsletter)){
							$cor1 = ($cor1 == $cor2)?$cor3:$cor2;
							$tipo = $rs_newsletter_row['tipo'] ;
							$email = $rs_newsletter_row['email'] ;
							$n = $rs_newsletter_row['n'] ;
							$ativo = $rs_newsletter_row['ativo'] ;
							$size = count($rs_newsletter_row)/2 - 4 ; 
							$roll = 0;

							$mensagem .= $tipo."\t".$email."\t".$n."\t".$ativo;
							
							

									while ($roll < $size) {
									$produtos[$roll] = $rs_newsletter_row['produto'.$roll] ;

										if ($limit < 1) {
											$mensagem .="\t".$produtos[$roll];
							
										}

									$roll++;
									}

							$mensagem .= PHP_EOL;
												
					
						$size2 = 0;
						$total = count($produtos);
						
						
						?>
                     
          <?php  }  ?>
          
          
 <?php
 //echo $mensagem;
 //die("antes file ret");
  $file_ret = grava_arquivo_emails($mensagem); 
 
 ?>
 <a href="/includes/download/download.php?f=<?php echo $file_ret; ?>&fc=<?php echo $tf_tipo."_".date("YmdHis").".txt"; ?>">Arquivo TXT com todos os registros</a>

</html>
<?php 

	// Tomado de CodeIgniter (ver POS lista_transacoes_gr.php)
	function grava_arquivo_emails($mensagem) {

		global $raiz_do_projeto;
    
		$file_path = $raiz_do_projeto . "backoffice/includes/download/tmp/";
		$web_path = "/includes/download/tmp/";
		$expiration = 20;

		// -----------------------------------
		// Remove old images	
		// -----------------------------------
				
		list($usec, $sec) = explode(" ", microtime());
		$now = ((float)$usec + (float)$sec);
				
		$current_dir = @opendir($file_path);
		
                if(is_dir($file_path)) {
                    while($filename = @readdir($current_dir)) {
                            if ($filename != "." and $filename != ".." and $filename != "index.html") {
                                    $name = str_replace(".txt", "", $filename);

                                    if (($name + $expiration) < $now) {
                                            @unlink($file_path.$filename);
                                    }
                            }
                    }
                    @closedir($current_dir);
                }
                
		//Arquivo
		$file = $file_path.$now.".txt";
	
		//Grava mensagem no arquivo
		if ($handle = fopen($file, 'a+')) {
			fwrite($handle, $mensagem);
			fclose($handle);
		} 
		
//		$file_return = 'http://'.$_SERVER['HTTP_HOST'].$web_path.$now.".txt";
		$file_return = $now.".txt";

		return $file_return;
	}

?>