<?php 
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";
require_once $raiz_do_projeto."includes/main.php";
require_once $raiz_do_projeto."includes/pdv/main.php";
require_once $raiz_do_projeto."includes/pdv/inc_utf.php";
require_once $raiz_do_projeto."includes/pdv/inc_ufs.php";

	set_time_limit ( 3000 ) ;

	$time_start_stats = getmicrotime();

	$bDebug = false;

	$msg = "";

//echo "lanhouses_id: ".$lanhouses_id."<br>";
//echo "lh_lanhouses_id: ".$lh_lanhouses_id."<br>";
	if(!$bNew) {
		if(!$lanhouses_id) $lanhouses_id = $lh_lanhouses_id;
		if(!$lanhouses_id) $msg = "Código do PDV não fornecido.\n";
		elseif(!is_numeric($lanhouses_id)) $msg = "Código do PDV inválido.\n";
	}

//echo "<pre>".print_r($_POST,true)."</pre>";
	
	// Se for novo registro => é para editar
	//if($bNew) $bEdit = 1;
	$bEdit = 1;
	$bDelete = 0;
	$msg_save = "";

	//Processa acoes
	//----------------------------------------------------------------------------------------------------------
	if($msg == ""){
	
		if($btApagar=="Apagar este registro") {

			$bDelete = 1;
			$sql_deleta = "delete from tb_lanhouses where lanhouses_id = " . $lanhouses_id ."";

			$rs_lhs = SQLexecuteQuery($sql_deleta);
			if(!$rs_lhs) $msg = "Erro ao deletar PDV (lanhouses_id = " . $lanhouses_id .").\n";
			else {
				$msg = "lanhouses_id = " . $lanhouses_id ." foi deletada com sucesso.";
			}

		} elseif($btSalvar=="Salvar") {
			$msg_save = "<font color='blue'>Salvando</font>";

			$lh_db_lanhouse		= translate_extended_ascii_to_utf(substr(str_replace("'", "''", trim($lh_lanhouse)),0,50)); 
			$lh_db_logradouro	= translate_extended_ascii_to_utf(substr(str_replace("'", "''", trim($lh_logradouro)),0,50)); 
			$lh_db_numero		= translate_extended_ascii_to_utf(substr(str_replace("'", "''", trim($lh_numero)),0,50)); 
			$lh_db_complemento	= translate_extended_ascii_to_utf(substr(str_replace("'", "''", trim($lh_complemento)),0,50)); 
			$lh_db_bairro		= translate_extended_ascii_to_utf(substr(str_replace("'", "''", trim($lh_bairro)),0,50)); 
			$lh_db_cidade		= translate_extended_ascii_to_utf(substr(str_replace("'", "''", trim($lh_cidade)),0,50)); 
			$lh_db_cep			= translate_extended_ascii_to_utf(substr(str_replace("'", "''", trim($lh_cep)),0,10)); 
			$lh_db_estado		= substr(str_replace("'", "''", trim($lh_estado)),0,2); 
			$lh_db_data_inclusao = $lh_data_inclusao;
			$lh_db_comentario	= translate_extended_ascii_to_utf(substr(str_replace("'", "''", trim($lh_comentario)),0,50)); 
			$lh_db_promocao		= $lh_promocao;

			if($bNew) {
				$sql_salva = "insert into tb_lanhouses (lanhouse, logradouro, numero, complemento, bairro, cidade, cep, estado, data_inclusao, comentario, promocao) values ('".$lh_db_lanhouse."', '".$lh_db_logradouro."', '".$lh_db_numero."', '".$lh_db_complemento."', '".$lh_db_bairro."', '".$lh_db_cidade."', '".$lh_db_cep."', '".$lh_db_estado."', CURRENT_TIMESTAMP, '".$lh_db_comentario."', ".$lh_db_promocao."); ";

				$rs_lhs = SQLexecuteQuery($sql_salva);
				if(!$rs_lhs) $msg = "Erro ao inserir PDV.\n";
				else {
					// Obtem last id
					$rs_id = SQLexecuteQuery("select currval('sq_lans') as last_id");
					if($rs_id && pg_num_rows($rs_id) > 0){
						$rs_id_row = pg_fetch_array($rs_id);
						$lh_lanhouses_id = $rs_id_row['last_id'];
						$bNew = 0;
					}
				}

			} else {
				$sql_salva = "update tb_lanhouses set lanhouse = '".$lh_db_lanhouse."', logradouro = '".$lh_db_logradouro."', numero = '".$lh_db_numero."', complemento  = '".$lh_db_complemento."', bairro = '".$lh_db_bairro."', cidade = '".$lh_db_cidade."', cep = '".$lh_db_cep."', estado = '".$lh_db_estado."', comentario = '".$lh_db_comentario."', promocao = ".$lh_db_promocao." where lanhouses_id = ".$lh_lanhouses_id."; ";
	
				$rs_lhs = SQLexecuteQuery($sql_salva);
				if(!$rs_lhs) $msg = "Erro ao atualizar PDV.\n";
//				echo "UPDATE BLOCKED<br>";
			}
//echo "$sql_salva<br>";
if($msg) echo "<font color='red'>$msg</font><br>";

		}
	}

	//Mostra a pagina
	//----------------------------------------------------------------------------------------------------------
	//Recupera a LH
	if($msg == ""){
		if($bNew) {
			$lh_lanhouses_id  				= 0;
			$lh_lanhouse      				= "";
			$lh_logradouro    				= "";
			$lh_numero        				= "";
			$lh_complemento   				= "";
			$lh_bairro        				= "";
			$lh_cidade        				= "";
			$lh_cep           				= "";
			$lh_estado        				= "";
			$lh_data_inclusao 				= "";
			$lh_comentario    				= "";
			$lh_promocao      				= "";
		} else {
			if(!$lanhouses_id) $lanhouses_id = $lh_lanhouses_id;
			$sql  = "SELECT * FROM tb_lanhouses " .
					"where lanhouses_id = " . $lanhouses_id;
			$rs_LH = SQLexecuteQuery($sql);
			if(!$rs_LH || pg_num_rows($rs_LH) == 0) {
				$msg = "Nenhum PDV encontrado.\n";
			} else {
				$rs_LH_row = pg_fetch_array($rs_LH);
				$lh_lanhouses_id  				= $rs_LH_row['lanhouses_id'];
				$lh_lanhouse      				= $rs_LH_row['lanhouse'];
				$lh_logradouro    				= $rs_LH_row['logradouro'];
				$lh_numero        				= $rs_LH_row['numero'];
				$lh_complemento   				= $rs_LH_row['complemento'];
				$lh_bairro        				= $rs_LH_row['bairro'];
				$lh_cidade        				= $rs_LH_row['cidade'];
				$lh_cep           				= $rs_LH_row['cep'];
				$lh_estado        				= $rs_LH_row['estado'];
				$lh_data_inclusao 				= $rs_LH_row['data_inclusao'];
				$lh_comentario    				= $rs_LH_row['comentario'];
				$lh_promocao      				= $rs_LH_row['promocao'];
			}
		}
	}

	$cor1 = "#F5F5FB";
	$cor2 = "#F5F5FB";
	$cor3 = "#FFFFFF"; 	


ob_end_flush();
?>
<script language="javascript">
function GP_popupAlertMsg(msg) { //v1.0
  document.MM_returnValue = alert(msg);
}
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="2">BackOffice - Lan Houses</a></li>
        <li><a href="com_pesquisa_lanhouses.php?varsel=<?php echo $_SESSION['busca_lh_varsel_list'] ?>">Voltar</a></li>
        <li class="active">LH - Cadastro para Busca no Site</li>
    </ol>
</div>
<table>
  <tr> 
    <td>

		<?php if(!$bDelete) { ?>
<form method=post action="com_lanhouses_detalhe.php">

    <table class="table txt-preto fontsize-p">
			<?php if($msg_save) { ?>
          <tr bgcolor="#FFFFFF"> 
            <td colspan="2" bgcolor="#ECE9D8"><?php echo $msg_save; ?></font></td>
          </tr>
			<?php } ?>
			<?php if($msg) { ?>
          <tr bgcolor="#FFFFFF"> 
            <td colspan="2" bgcolor="#ECE9D8"><?php echo "<font color='red'>".$msg."</font>"; ?></font></td>
          </tr>
			<?php } ?>
          <tr bgcolor="#FFFFFF"> 
            <td colspan="2" bgcolor="#ECE9D8">PDV</font></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>ID</b></td>
            <td><?php 
				if($bEdit) {
					echo $lh_lanhouses_id;
					echo "<input type='hidden' name='lanhouses_id' value='$lanhouses_id'>\n";
					echo "<input type='hidden' name='lh_lanhouses_id' value='$lh_lanhouses_id'>\n";
				} else {
					echo $lh_lanhouses_id;
				}
				?></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Nome</b></td>
            <td><?php 
				if($bEdit) {
					echo "<input type='text' name='lh_lanhouse' value='".translate_utf_to_extended_ascii($lh_lanhouse)."' size='50' maxlength='50' class='texto'>";
				} else {
					echo translate_utf_to_extended_ascii($lh_lanhouse);
				}
				?></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Logradouro</b></td>
            <td><?php 
				if($bEdit) {
					echo "<input type='text' name='lh_logradouro' value='".translate_utf_to_extended_ascii($lh_logradouro)."' size='50' maxlength='50' class='texto'>";
				} else {
					echo translate_utf_to_extended_ascii($lh_logradouro);
				}
				?></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Número</b></td>
            <td><?php 
				if($bEdit) {
					echo "<input type='text' name='lh_numero' value='".translate_utf_to_extended_ascii($lh_numero)."' size='50' maxlength='50' class='texto'>";
				} else {
					echo translate_utf_to_extended_ascii($lh_numero);
				}
				?></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Complemento</b></td>
            <td><?php 
				if($bEdit) {
					echo "<input type='text' name='lh_complemento' value='".translate_utf_to_extended_ascii($lh_complemento)."' size='50' maxlength='50' class='texto'>";
				} else {
					echo translate_utf_to_extended_ascii($lh_complemento);
				}
				?></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Bairro</b></td>
            <td><?php 
				if($bEdit) {
					echo "<input type='text' name='lh_bairro' value='".translate_utf_to_extended_ascii($lh_bairro)."' size='50' maxlength='50' class='texto'>";
				} else {
					echo translate_utf_to_extended_ascii($lh_bairro);
				}
				?></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Cidade</b></td>
            <td><?php 
				if($bEdit) {
					echo "<input type='text' name='lh_cidade' value='".translate_utf_to_extended_ascii($lh_cidade)."' size='50' maxlength='50' class='texto'>";
				} else {
					echo translate_utf_to_extended_ascii($lh_logradouro);
				}
				?></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>CEP</b></td>
            <td><?php 
				if($bEdit) {
					echo "<input type='text' name='lh_cep' value='$lh_cep' size='50' maxlength='50' class='texto'>";
				} else {
					echo $lh_cep;
				}
				?></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>UF</b></td>
            <td><?php 
				if($bEdit) {
				?>
				<select name="lh_estado" class="form2">
					<option value=""<?php if($lh_estado=="") echo " selected" ?>>Escolha UF</option>
				<?php 
					foreach($ufs as $key => $uf) {
				?><option value="<?php echo $uf ?>"<?php if($lh_estado==$uf) echo " selected" ?>><?php echo $uf ?></option>
				<?php 
					}
				?>
				</select>

				<?php
				} else {
					echo $lh_estado;
				}
				?></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Comentário</b></td>
            <td><?php 
				if($bEdit) {
					echo "<input type='text' name='lh_comentario' value='".translate_utf_to_extended_ascii($lh_comentario)."' size='50' maxlength='50' class='texto'>";
				} else {
					echo translate_utf_to_extended_ascii($lh_comentario);
				}
				
//				echo "<br>&nbsp;&nbsp;áéíóúàèìòùãõâêîôäëïöüçÁÉÍÓÚÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ";
				?>
				
				</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Promocao</b></td>
            <td><?php 
				if($bEdit) {
				?>
				<select name="lh_promocao" class="form2">
					<option value="0"<?php if(!$lh_promocao) echo " selected" ?>>Não</option>
					<option value="1"<?php if($lh_promocao) echo " selected" ?>>Sim</option>
				</select>
				<?php
				} else {
					echo $lh_promocao;
				}
				?></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>Data Inclusão</b></td>
            <td><?php 
				if($bEdit) {
					echo formata_data_ts($lh_data_inclusao, 0, true, true); 
				} else {
					echo formata_data_ts($lh_data_inclusao, 0, true, true); 
				}
				?></td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          <tr bgcolor="#F5F5FB"> 
            <td><b>&nbsp;</b></td>
            <td><?php 
				if($bEdit) {
					?>
					<input type='hidden' name='bNew' value='<?php echo $bNew ?>'>
					<input type='hidden' name='bEdit' value='1'>

                    <input type='submit' class="btn btn-info" name='btSalvar' value='Salvar'> 
					<?php 
					if(!$bNew) {
					?>&nbsp;&nbsp;-&nbsp;&nbsp;
                    <input type="submit" name="btApagar" value="Apagar este registro" onclick="return confirm('Você quer mesmo apagar este registro?')" class="btn btn-danger">
					<?php
					}
				} else {
					echo "&nbsp;"; 
				}
				?></td>
          </tr>

		</table>
</form>
		
		<?php }	 else {	
			echo $msg;
			?>

		<?php }	 ?>

    </td>
  </tr>
</table>
<?php 
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
</html>
