<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";

require_once $raiz_do_projeto."includes/gamer/constantes.php";

require_once $raiz_do_projeto."class/classDescriptionReport.php";
$descricao = new DescriptionReport('estorno');
echo $descricao->MontaAreaDescricao();

$btn_estorno = $_POST['btn_estorno'] ?? null;
$btn_pesquisar = $_POST['btn_pesquisar'] ?? null;
$scf_id = $_POST['scf_id'] ?? array();
$motivo = trim((string)($_POST['motivo'] ?? ''));
$ug_id = trim((string)($_POST['ug_id'] ?? ''));
$vg_id = trim((string)($_POST['vg_id'] ?? ''));
$mensagem_final = "";

function cadastra_estorno_h($value) {
	return htmlspecialchars((string)$value, ENT_QUOTES, 'ISO-8859-1');
}

if (isset($btn_estorno) && $btn_estorno=="Estornar") {
	//echo "<pre>".print_r($scf_id,true)."</pre>";
	$scf_id_lista = array();
	foreach ((array)$scf_id as $scf_id_param) {
		if (ctype_digit((string)$scf_id_param)) {
			$scf_id_lista[] = (int)$scf_id_param;
		}
	}

	if (empty($scf_id_lista)) {
		$scf_id_lista = array(0);
	}

	$scf_id_placeholders = array();
	foreach ($scf_id_lista as $scf_id_index => $scf_id_param) {
		$scf_id_placeholders[] = "$" . ($scf_id_index + 1);
	}

	$msg = "";
	//Inicia transacao
	$sql = "BEGIN TRANSACTION ";
	$ret = SQLexecuteQuery($sql);
	if(!$ret) $msg = "<font color='#FF0000'><b>Erro ao iniciar transa&cceil;&atilde;o.\n</b></font><br>";

	//Confirmando os IDs
	$sql = "SELECT scf_id from saldo_composicao_fifo WHERE scf_id IN (" . implode(",", $scf_id_placeholders) . ") AND scf_valor=scf_valor_disponivel AND scf_status=1;";
	$rs_dados_confirmados = SQLexecuteQueryParams($sql, $scf_id_lista);
//	echo $sql."<br>";
	while($rs_dados_confirmados && ($rs_dados_confirmados_row = pg_fetch_array($rs_dados_confirmados))) {
		//transacao por transacao
//		echo $rs_dados_confirmados_row['scf_id'].str_repeat("=",30)."<br>";
		if($msg == ""){
			$sql = "UPDATE saldo_composicao_fifo SET scf_valor_disponivel=0, scf_status=0 where scf_id=$1;";
			$rs_saldo_composicao_fifo = SQLexecuteQueryParams($sql, array($rs_dados_confirmados_row['scf_id']));
//			$rs_saldo_composicao_fifo = true;
//			echo $sql."<br>";
			if($rs_saldo_composicao_fifo) {
				$sql = "INSERT INTO saldo_composicao_fifo_utilizado (scf_id, vg_id, scfu_valor) VALUES ($1,0,(select scf_valor from saldo_composicao_fifo where scf_id=$1));";
				$rs_saldo_composicao_fifo_utilizado = SQLexecuteQueryParams($sql, array($rs_dados_confirmados_row['scf_id']));
//				$rs_saldo_composicao_fifo_utilizado = true;
//				echo $sql."<br>";
				if($rs_saldo_composicao_fifo_utilizado) {
					$sql = "UPDATE usuarios_games SET ug_perfil_saldo=ug_perfil_saldo-(select scf_valor from saldo_composicao_fifo where scf_id=$1) WHERE ug_id=(select ug_id from saldo_composicao_fifo where scf_id=$1);";
					$rs_usuarios_games = SQLexecuteQueryParams($sql, array($rs_dados_confirmados_row['scf_id']));
//					$rs_usuarios_games = true;
//					echo $sql."<br>";
					if($rs_usuarios_games) {
						$sql = "INSERT INTO tb_pag_estorno (ug_id,tpe_data,tpe_valor,tpe_motivo,tpe_id_estonador,tpe_tipo_user) VALUES ((select ug_id from saldo_composicao_fifo where scf_id=$1),NOW(),(select scf_valor from saldo_composicao_fifo where scf_id=$1),$2,$3,'G');";
							$rs_estorno = SQLexecuteQueryParams($sql, array($rs_dados_confirmados_row['scf_id'], $motivo, $_SESSION['iduser_bko']));
//						$rs_estorno = true;
//						echo $sql."<br>";
						if($rs_estorno) {
							$sql = "SELECT * FROM usuarios_games ug INNER JOIN saldo_composicao_fifo scf ON (ug.ug_id=scf.ug_id) WHERE scf_id=$1;";
							$rs_dados_sucesso = SQLexecuteQueryParams($sql, array($rs_dados_confirmados_row['scf_id']));
//							echo $sql."<br>";
								if($rs_dados_sucesso_row = pg_fetch_array($rs_dados_sucesso)) {
									$mensagem_final .= "<font color='#FF0000'><b>Estorno do Gamer [".cadastra_estorno_h($rs_dados_sucesso_row['ug_nome'])."] foi realizado com sucesso, seu saldo atual é R$ ".number_format($rs_dados_sucesso_row['ug_perfil_saldo'], 2, ',', '.')." para a venda ID [".cadastra_estorno_h($rs_dados_sucesso_row['vg_id'])."] foi estornado o valor de R$ ".number_format($rs_dados_sucesso_row['scf_valor'], 2, ',', '.').".\n</b></font><br>";
							}//end if($rs_dados_sucesso)
							else {
								$msg .= "<font color='#FF0000'><b>Erro ao selecionar dados de confirmaçãode sucesso da operação(".$rs_dados_confirmados_row['scf_id'].").\n</b></font><br>";
							}//end else if($rs_dados_sucesso)
						}//end if($rs_estorno)
						else {
							$msg .= "<font color='#FF0000'><b>Erro ao inserir o registro de estorno do FIFO (".$rs_dados_confirmados_row['scf_id'].").\n</b></font><br>";
						}//end else if($rs_estorno)
					}//end if($rs_usuarios_games)
					else {
						$msg .= "<font color='#FF0000'><b>Erro ao atualizar o saldo no usuarios_games.\n</b></font><br>";
					}//end else if($rs_usuarios_games)
				}//end if($rs_saldo_composicao_fifo_utilizado)
				else {
					$msg .= "<font color='#FF0000'><b>Erro ao inserir registro na tabela saldo_composicao_fifo_utilizado.\n</b></font><br>";
				}//end else if($rs_saldo_composicao_fifo_utilizado)
			}//end if($rs_saldo_composicao_fifo)
			else {
				$msg .= "<font color='#FF0000'><b>Erro ao atualizar saldo_composicao_fifo.\n</b></font><br>";
			}//end else if($rs_saldo_composicao_fifo)
		} // end if($msg == "")
	}//end while($rs_dados_confirmados && ($rs_dados_confirmados_row = pg_fetch_array($rs_dados_confirmados)))

	//Finaliza transacao
	if($msg == ""){
		$sql = "COMMIT TRANSACTION ";
		$ret = SQLexecuteQuery($sql);
		if(!$ret) $msg .= "<font color='#FF0000'><b>Erro ao comitar transa&ccedil;&atilde;o.\n<br></b></font><br>";
	} else {
		$sql = "ROLLBACK TRANSACTION ";
		$ret = SQLexecuteQuery($sql);
		if(!$ret) $msg .= "<font color='#FF0000'><b>Erro ao dar rollback na transa&ccedil;&atilde;o.\n<br></b></font><br>";
	}
	$mensagem_final .= $msg;
}//end if ($btn_estorno=="Estornar")

?>
	<link rel="stylesheet" type="text/css" href="/css/cssClassLista.css" />
	<script>
	function validaCheckbox(formObj, chkName){
		var chkObj = formObj[chkName];
		if(!chkObj) return false;
		var msg = "";

		if(chkObj.length == undefined){
			if(!chkObj.checked) msg = "Nenhum depósito selecionado.";
		}else {
			var blAchou = false;
			for(var i=0; i < chkObj.length; i++)
				if(chkObj[i].checked) blAchou = true;
			if(!blAchou) msg = "Nenhum depósito selecionado.";
		}

		if(msg == "") return true;
		else {
			alert(msg);
			return false;
		}
	}//end function validaCheckbox(formObj, chkName)

	function VerificaMotivo() {
		if(validaCheckbox(document.form1,'scf_id[]')) {
			if(document.form1.motivo.value=="") {
				alert("Você tem informar o motivo do estorno!");
				return false;
			} //end if(document.form1.motivo.value=="")
			else {
				if (confirm("Você tem certeza que deseja realizar\no estorno para este depósito?"))
					return true;
				else return false;
			}//end else if(document.form1.motivo.value=="")
		}//end if(validaCheckbox(document.form1,'scf_id[]'))
		else {
			return false;
		}//end else if(validaCheckbox(document.form1,'scf_id[]'))
	}//end function VerificaMotivo()

        $(function(){

            $("#checkall").click(function(){

                var res = this.checked;

                $(':checkbox').each(function() {
                    this.checked = res;
                });
            });

          });
	</script>
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<form id="form1" name="form1" method="post" action="<?php echo cadastra_estorno_h($_SERVER['PHP_SELF'] ?? ''); ?>">
<table class="table txt-preto fontsize-pp">
    <tr>
        <td valign="top">
            <table width="100%" border="0" align="center">
                <tr>
                    <td valign="top">
                        <table class="table">
                                <tr>
                                    <td colspan="4" bgcolor="#DDDDDD">&nbsp;&nbsp;Dados do Usu&aacute;rio</td>
                                </tr>
                                <tr>
                                    <td align="right">ID do Usu&aacute;rio: </td>
                                    <td>
											<input name="ug_id" type="text" id="ug_id" size="20" maxlength="10" value="<?php echo cadastra_estorno_h($ug_id); ?>"/>
									</td>
                                    <td align="right">ID da Venda: </td>
                                    <td>
											<input name="vg_id" type="text" id="vg_id" size="20" maxlength="10" value="<?php echo cadastra_estorno_h($vg_id); ?>"/>
									</td>
                                </tr>
                                <tr>
                                    <td colspan="4" align="center"><input name="btn_pesquisar" type="submit" id="btn_pesquisar" class="btn btn-info btn-sm" value="Pesquisar" /></td>
                                </tr>
                            </table>
                    </td>
                </tr>
                <tr align="center">
                <td>
<p align="center">
<?php
if (isset($btn_pesquisar) && $btn_pesquisar=="Pesquisar") {


	$sql = "SELECT scf.*,to_char(scf_data_deposito,'DD/MM/YYYY HH:MI:SS') as data_deposito, ug_nome
	 FROM saldo_composicao_fifo scf
		INNER JOIN tb_venda_games vg ON (scf.vg_id=vg.vg_id)
		INNER JOIN usuarios_games ug ON (scf.ug_id=ug.ug_id) ";
	$sql_aux = array("scf_valor=scf_valor_disponivel", "scf_status=1");
	$sql_params = array();
	if ($ug_id !== "" && ctype_digit($ug_id)) {
		$sql_params[] = (int)$ug_id;
		$sql_aux[] = "scf.ug_id = $" . count($sql_params);
	}
	if ($vg_id !== "" && ctype_digit($vg_id)) {
		$sql_params[] = (int)$vg_id;
		$sql_aux[] = "scf.vg_id = $" . count($sql_params);
	}
	$sql .= ' WHERE ' . implode(' AND ', $sql_aux);
	$sql .= ' GROUP BY scf.scf_id,scf.ug_id,scf_data_deposito,scf_valor,scf_valor_disponivel,scf_status,scf_canal,scf_comissao,scf_id_pagamento,scf.vg_id,data_deposito,ug_nome
			  ORDER BY scf_data_deposito DESC';
	//echo str_replace("\n", "<br>\n", $sql)."<br>";
	//die($sql);
	$rsResposta = count($sql_params) ? SQLexecuteQueryParams($sql, $sql_params) : SQLexecuteQuery($sql);
	}//end if ($btn_pesquisar=="Pesquisar")
?>
<table class="table txt-preto fontsize-pp">
<?php
if(isset($rsResposta) && $rsResposta && pg_num_rows($rsResposta) != 0) {
?>
	<tr>
        <td align="center">&nbsp;</td>
        <td align="left" colspan="4"><?php echo "Encontrado".(((($rsResposta) ? pg_num_rows($rsResposta) : 0)>0)?"s":"")." ".(($rsResposta) ? pg_num_rows($rsResposta) : 0)." registro".(((($rsResposta) ? pg_num_rows($rsResposta) : 0)>0)?"s":"")."";?></td>
        <td align="center">&nbsp;</td>
    </tr>
	<tr>
        <td bgcolor="#DDDDDD" align="center">
            <input type="checkbox" id="checkall">
            <label for="checkall" class="fontweightnormal">Selecionar Todos</label>
        </td>
        <td bgcolor="#DDDDDD" align="center">Nome usu&aacute;rio</td>
        <td bgcolor="#DDDDDD" align="center">Data</td>
        <td bgcolor="#DDDDDD" align="center">Valor R$</td>
        <td bgcolor="#DDDDDD" align="center">Tipo Pagto.</td>
        <td bgcolor="#DDDDDD" align="center">ID da Venda</td>
    </tr>
<?php
} //end if(((($rsResposta) ? pg_num_rows($rsResposta) : 0) != 0) && ($rsResposta))
$backcolor1 = "#ccffff";
$backcolor2 = "#ffffff";
$bck = $backcolor1;
if(isset($rsResposta) && $rsResposta){
    while ($pgResposta = pg_fetch_array ($rsResposta)) {
?>
        <tr<?php echo " bgcolor='".$bck."'" ?>>
            <td align="center"><input name="scf_id[]" type="checkbox" id="scf_id[]" value="<?php echo (int)$pgResposta['scf_id']; ?>"/></td>
            <td align="left"><nobr><a href="/gamer/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo (int)$pgResposta['ug_id'];?>"><?php echo cadastra_estorno_h($pgResposta['ug_nome']);?></a></nobr></td>
            <td align="center"><nobr><?php echo cadastra_estorno_h($pgResposta['data_deposito']);?></nobr></td>
            <td align="right"><nobr><?php echo number_format($pgResposta['scf_valor'], 2, ',', '.');?></nobr></td>
            <td align="center"><nobr><?php echo cadastra_estorno_h($FORMAS_PAGAMENTO_DESCRICAO[$pgResposta['scf_id_pagamento']] ?? '');?></nobr></td>
            <td align="center"><nobr><a href="/gamer/vendas/com_venda_detalhe.php?venda_id=<?php echo (int)$pgResposta['vg_id'];?>"><?php echo (int)$pgResposta['vg_id'];?></a></nobr></td>
        </tr>
<?php
        if ($bck == $backcolor1)
            $bck = $backcolor2;
        else $bck = $backcolor1;
} //end while ($pgResposta = pg_fetch_array ($rsResposta))
}
if(isset($rsResposta) && $rsResposta && ((($rsResposta) ? pg_num_rows($rsResposta) : 0) != 0)) {
?>
	<tr>
        <td align="center">&nbsp;</td>
        <td align="center" colspan="4">
				Motivo do Estorno: <input name="motivo" type="text" id="motivo" size="80" maxlength="256" value="<?php echo cadastra_estorno_h($motivo); ?>"/><br>
			<input name="btn_estorno" type="submit" id="btn_estorno" value="Estornar" OnClick='javascript: return VerificaMotivo();'/>
		</td>
        <td align="center">&nbsp;</td>
    </tr>
<?php
} //end if(((($rsResposta) ? pg_num_rows($rsResposta) : 0) != 0) && ($rsResposta))
if (isset($btn_estorno) && $btn_estorno=="Estornar") {
?>
	<tr>
        <td align="center">&nbsp;</td>
        <td align="center" colspan="4">
		<?php
			echo $mensagem_final;
		?>
		</td>
        <td align="center">&nbsp;</td>
    </tr>
<?php
}//end if ($btn_estorno=="Estornar")
?>
</table>
</p>
               </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
?>
