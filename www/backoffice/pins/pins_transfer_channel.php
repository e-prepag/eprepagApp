<?php   
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";

	set_time_limit ( 3000 ) ;

	$cor1 = "#F5F5FB";
	$cor2 = "#F5F5FB";
	$cor3 = "#FFFFFF";

	$days_for_mean = 7;
	$prazo_vermelho_vezes = 1;
	$prazo_amarelo_vezes = 2;

	$ChkTreinamento = "1";

	// Em operação -> deixar ($bCommit = true) & ($bPrint = false)
	$bCommit = true;
	$bPrint = false;


	$channel_from = array (
							's' => 'Site',
							'p' => 'POS',
							'r' => 'Rede',
							);
	
	$channel_to = array (
							's' => 'r',
							'p' => 'p',
							'r' => 's',
							);
	
	if(isset($_SESSION["tipo_acesso_pub"]) && $_SESSION["tipo_acesso_pub"]=='PU') {
		$fopr = $_SESSION["opr_codigo_pub"];
		$Submit = "Buscar";
	}

	if(!isset($ncamp) || !$ncamp) $ncamp = 'opr_nome';
	if(!isset($nscamp) || !$nscamp) $nscamp = 'ec_uf, pin_valor';
	

	$sql = "select opr_codigo, opr_nome from operadoras where opr_status='1' and opr_pin_online = 0 ";
	if(!$ChkTreinamento && $fopr <> 78) $sql .=" and (opr_codigo <> 78) "; 
	$sql .= " order by opr_nome";
	$resopr = pg_exec($connid, $sql);

?>
<script>
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}

//Validar a Busca
function validar()
{
    if (document.getElementById('fopr').value == '')
    {
		alert('Selecione a Operadora para Efetuar a Busca!');
		return false;
    }else if (document.getElementById('pin_valor').value == '')
		{
			alert('Selecione o Valor para Efetuar a Busca!');
			return false;
		}else return true;
}

//Validar a Alteração
function alterar() {
    if (document.getElementById('fopr').value == '')  {
		alert('Selecione a Operadora para Alterar!');
		return false;
    }else if (document.getElementById('pin_valor').value == '') {
		alert('Selecione o Valor para Alterar!');
		return false;
	}else if (document.getElementById('pin_canal_from').value == 't') {
		alert('Selecione o Canal para Alterar!');
		return false;
	}else if (document.getElementById('pin_canal_to').value == 't') {
		alert('Selecione o Canal Destino para Alterar!');
		return false;
	}else if (document.getElementById('pin_qtde').value == '') {
		alert('Informe a Qtde para Alterar!');
		return false;
	} else {
		return true;
	}
}


// Carrega valores reflesh 
function carga_valor(){
    $.ajax({
        type: "POST",
        url: "/ajax/gamer/ajaxValorComPesquisaValores.php",
        data: "id="+document.getElementById('fopr').value+"&valor=<?php if(isset($pin_valor)) echo $pin_valor;?>",
        success: function(html){
            $('#mostraValores').html(html);
        },
        error: function(){
            alert('erro valor');
        }
    });
}

//Carga Valor onChange
$(document).ready(function () {
<?php
	if(!isset($pin_canal_from) || !$pin_canal_from) {
?>
		$("#pin_canal_from option[value='s']").attr('selected', 'selected');
<?php
	}
?>
<?php
	if(!isset($pin_canal_to) || !$pin_canal_to) {
?>
		$("#pin_canal_to option[value='r']").attr('selected', 'selected');
<?php
	}
?>
			
		$('#fopr').change(function(){
			var id = $(this).val();
			$.ajax({
				type: "POST",
				url: "/ajax/gamer/ajaxValorComPesquisaValores.php",
				data: "id="+id,
				success: function(html){
					$('#mostraValores').html(html);
				},
				error: function(){
					alert('erro valor');
				}
			});
		});

		$('#pin_canal_from').change(function(){
			var id = $(this).val();
			<?php 
/*
				$aux = "";
				foreach($channel_from as $key => $value) {
					if (strlen($aux)>0)
					{
						echo "\t else ";
					}
					echo "if(id==\"".$key."\") {\n";
					echo "\t$(\"#pin_canal_to option[value='".$channel_to[$key]."']\").attr('selected', 'selected');\n\t}";
					$aux = "??";
				}
*/
			?>
			});
		
		$("#pin_qtde").keydown(function(event) {
			// Allow only backspace and delete
			if ( event.keyCode == 46 || event.keyCode == 8 ) {
				// let it happen, don't do anything
			}
			else {
				// Ensure that it is a number and stop the keypress
				if (event.keyCode < 48 || (event.keyCode > 57 && event.keyCode < 96 ) || event.keyCode > 105 || event.shiftKey) {
					event.preventDefault(); 
				}
			}
		});
		
});
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="1">BackOffice - Money</a></li>
        <li><a href="index.php">Voltar</a></li>
        <li class="active">Alteração do Canal do PIN <?php 
		if($bCommit) {
			if($bPrint) {
				echo "<font color='blue'>Atualiza BD</font>";
			}
		} else {
			echo "<font color='red'>Não atualiza BD</font>";
		}
		?></li>
    </ol>
</div>
<table class="table txt-preto">
    <td valign="top" bgcolor="#FFFFFF">
      <table width="100%" border="0" cellspacing="0" cellpadding="3" height="100%">
        <tr valign="top">
          <td height="100%">   
            <form name="form1" method="post" action="<?php  echo $PHP_SELF ?>">
                <table class="table">
                <tr> 
                  <td>Operadora:</td>
                  <td> 
					<select name="fopr" id="fopr" class="combo_normal">
                      <option value="">Todas as Operadoras</option>
                      <?php  while ($pgopr = pg_fetch_array ($resopr)) { ?>
                      <option value="<?php  echo $pgopr['opr_codigo'] ?>" <?php  if(isset($fopr) && $pgopr['opr_codigo'] == $fopr) echo "selected" ?>><?php  echo $pgopr['opr_nome'] ?></option>
                      <?php  } ?>
                    </select>
					</td>
                  <td>Valor:</font>
				  </td>
                  <td> 
					  <div id='mostraValores'>
						<?php if(isset($fopr) && strlen($fopr)>0) { ?>
						<script language="javascript">
						carga_valor();
						</script>
						<?php
						}
						else {?>
						   <select name="pin_valor" id="pin_valor" class="combo_normal">
							<option value="">Selecione a Operadora</option>
						   </select>
						<?php } ?>
					  </div>
                  </td>
                </tr> 
                <tr> 
				  <td>Canal:</td>
                  <td> 
                    <select name="pin_canal_from" id="pin_canal_from" class="combo_normal">
					<?php
						foreach($channel_from as $key => $value) {
							echo "<option value='$key' ";
							if(isset($pin_canal_from) && trim($pin_canal_from) == $key )
								echo "selected";
							echo ">$value</option>";
						}
					?>
                    </select>
                    </td>
				  <td>Canal Destino:</td>
                  <td> 
                    <select name="pin_canal_to" id="pin_canal_to" class="combo_normal">
                    <?php
						foreach($channel_from as $key => $value) {
							echo "<option value='$key' ";
							if(isset($pin_canal_to) && trim($pin_canal_to) == $key )
								echo "selected";
							echo ">$value</option>";
						}
					?>
                    </select>
                    </td>
                </tr> 
                <tr> 
				  <td>Quantidade:</td>
                  <td> 
					<input name="pin_qtde" id="pin_qtde" type="text" value="<?php if(isset($pin_qtde)) echo $pin_qtde; ?>" size="5" maxlength="5">
				  </td>
				  <td>&nbsp;</td>
				  <td><input type="submit" name="Submit" value="Buscar" class="btn btn-sm btn-info" onclick="return validar();"></td>
                </tr>
              </table>
              <br>
			  <?php
			  if(isset($Submit) && $Submit){
			  ?>
              <table class="table">
                <tr>
					<td width="25%" colspan="4"><strong>Situação Atual Antes da Alteração do Canal</strong></td>
                </tr> 
                <tr> 
                  <td width="25%" align="center"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif">Operadora</font></strong></td>
                  <td width="15%" align="center"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif">Valor</font></strong></td>
                  <td width="18%"  align="center"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif">Canal</font></strong></td>
                  <td width="25%" align="center"><strong><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif">Quantidade</font></strong></td>
 				</tr>
				<?php
					$sql = "select t1.opr_nome, t0.pin_valor, count(t0.pin_valor) as quantidade, t0.pin_canal 
							from pins t0, operadoras t1 
							where (t0.opr_codigo=t1.opr_codigo) and 
								  (t0.opr_codigo = '".$fopr."') and 
								  (t0.pin_valor='".$pin_valor."') and 
								  (pin_canal='".$pin_canal_from."') and
								  (pin_status='1') 
							group by t1.opr_nome, t0.pin_valor, t0.pin_canal 
							order by t0.pin_canal";
if($bPrint) echo str_replace("\n", "<br>\n",$sql)."<br>";

					$ret = SQLexecuteQuery($sql);
					$qtdeMaiorEstoque = false;
                    if($ret){
                        while($ret_row = pg_fetch_array($ret)){
                            if (($ret_row['quantidade']<$pin_qtde)&&($ret_row['pin_canal']==$pin_canal_from)) {
                                $qtdeMaiorEstoque = true;
                            }
                            echo "<tr><td><font size='2' face='Arial, Helvetica, sans-serif'>".$ret_row['opr_nome']."</font></td>";
                            echo "<td align='right'><font size='2' face='Arial, Helvetica, sans-serif'>".$ret_row['pin_valor']."</font></td>";
                            echo "<td align='center'><font size='2' face='Arial, Helvetica, sans-serif'>".$channel_from[$ret_row['pin_canal']]."</font></td>";
                            echo "<td align='right'><font size='2' face='Arial, Helvetica, sans-serif'>".$ret_row['quantidade']."</font></td></tr>";
                        }
                    }
				?>
				 <tr>
					<td align="center" colspan="4">
					<?php
					if ($qtdeMaiorEstoque) {
					?>
						<strong><font size="2" face="Arial, Helvetica, sans-serif">Quantidade Informada Maior que Dispon&iacute;vel em Estoque.</font></strong>
					<?php
					}
					else {
					?>
						<input type="submit" name="Move" value="Alterar" class="botao_search" onclick="javascript: if (confirm('Deseja Realmente Mudar o Canal\nConforme Dados Relacionados?')) return alterar(); else return false;">
					<?php
					}
					?>
					</td>
				</tr>
              </table>
			  <?php
				}
				?>
           </form>
          </td>
		</tr>
		<?php
		if(isset($Move) && $Move){
			$msg = "";
			if(!($fopr>0)) {
				$msg = "Erro: Escolha umoperadora ($fopr)<br>";
			}
			if($pin_canal_from == $pin_canal_to) {
				$msg = "Erro: Canais de origem e destino são iguais ('$pin_canal_from' -&gt; '$pin_canal_to')<br>";
			}
			if(!($pin_qtde>0)) {
				$msg = "Erro: Escolha uma quantidade de PINs>0<br>";
			}
			if(!($pin_valor>0)) {
				$msg = "Erro: Escolha um valor de PIN>0<br>";
			}

			if($msg == "") {
				//Inicia transacao
				$sql = "BEGIN TRANSACTION ";
				$ret = SQLexecuteQuery($sql);
				if(!$ret) $msg .= "Erro ao iniciar transação.\n";
				

				if($msg == ""){
					$sql = "select pin_codinterno, (select opr_nome from operadoras o where o.opr_codigo = p.opr_codigo) as opr_nome from pins p where opr_codigo=$fopr and pin_valor=$pin_valor and pin_status='1' and pin_canal='$pin_canal_from' order by pin_codinterno desc limit $pin_qtde;";
					//die($sql);
if($bPrint) echo "$sql<br>";

					$rs_transacao = SQLexecuteQuery($sql);
					if (!$rs_transacao) {
						$msg .="Erro ao selecionar os PINs a serem alterados!";
					}
				}

				if($msg == ""){
					if (pg_num_rows($rs_transacao) >= $pin_qtde) {
						$i = 1;
						while($rs_transacao_row = pg_fetch_array($rs_transacao)){
							// Atualiza nova ordem
							$iorder = $key + 1;
							$sql = "update pins set pin_canal = '".$pin_canal_to."' where pin_codinterno = ".$rs_transacao_row['pin_codinterno']." and opr_codigo=$fopr and pin_valor=$pin_valor and pin_status='1' and pin_canal='$pin_canal_from' ;";
if($bPrint) echo "$sql<br>";
							if($bCommit) {
								$rs_update = SQLexecuteQuery($sql);
								if (!$rs_update) {
									$msg .="Erro ao Atualizar o PIN :".$rs_transacao_row['pin_codinterno'];
								} else {
									$msg_transfere = "(".($i++).") Transfere PIN (opr='".$rs_transacao_row['opr_nome']."' [$fopr], pin_valor='$pin_valor', pin_codinterno='".$rs_transacao_row['pin_codinterno']."') de canal '$pin_canal_from' para canal '$pin_canal_to'";
									echo "<font face='Arial, Helvetica, sans-serif' size='2' color='darkgreen'>".$msg_transfere."</font><br>";
									gravaLog_PINsTransferChannel($msg_transfere);
								}
							} else {
								echo "Não atualiza BD (2)<br>";
							}
						}
					} else {
						$msg .="Quantidade de PIN em estoque de canal $pin_canal_from &eacute; de :".pg_num_rows($rs_transacao)." e a quantidade solicitada para alteração &eacute; de: ".$pin_qtde;
					}
				}

				//Finaliza transacao
				if($msg == ""){
					$sql = "COMMIT TRANSACTION ";
if($bPrint) echo "$sql<br>";
					if($bCommit) {
						$ret = SQLexecuteQuery($sql);
						if(!$ret) $msg .= "Erro ao comitar transação.\n";
						else $msg = "Alterado com sucesso";
					} else {
						echo "Não atualiza BD (3)<br>";
					}
				} else {
					$sql = "ROLLBACK TRANSACTION ";
if($bPrint) echo "$sql<br>";
					if($bCommit) {
						$ret = SQLexecuteQuery($sql);
						if(!$ret) $msg .= "Erro ao dar rollback na transação.\n";
					} else {
						echo "Não atualiza BD<br>";
					}
				}
			}


			echo "<tr><td colspan='9' align='center'><font style='font-size:12px;font-family:Arial, Helvetica, sans-serif;color:red'>".$msg."</font></td></tr>";
		}
		?>
      </table>
   </td>
  </tr>
</table>
 <?php 
    require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
 ?>
</body>
</html>

<?php
	function gravaLog_PINsTransferChannel($mensagem){
	
		//Arquivo
                global $raiz_do_projeto;
                
		$file = $raiz_do_projeto . "log/log_PINsTransferChannel.txt";
	
		//Mensagem
		$mensagem = date('Y-m-d H:i:s') . " " . $GLOBALS['_SERVER']["SCRIPT_FILENAME"] . ", userlogin_bko: '".$_SESSION['userlogin_bko']."'\n     " . $mensagem . "\n";
	
		//Grava mensagem no arquivo
		if ($handle = fopen($file, 'a+')) {
			fwrite($handle, $mensagem);
			fclose($handle);
		}	
	}


?>
