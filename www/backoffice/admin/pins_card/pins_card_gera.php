<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";
require_once $raiz_do_projeto . "class/classPinsCard.php";

// Configurado para naun expirar em 10 minutos de execução
// no DEV foi conseguido gerar pouco mais de 47000 PINs neste intervalo de tempo
set_time_limit(6000);

$publisher_array	= VetorOperadorasCard();

$opr_codigo 		= isset($_POST['opr_codigo'])	? (int) $_POST['opr_codigo']	: null;
$distributor_codigo 	= isset($_POST['pin_operacao'])	? (int) $_POST['pin_operacao']	: null;
$qtde			= isset($_POST['pin_qtde'])	? (int) $_POST['pin_qtde']	: null;
$pin_valor 		= isset($_POST['pin_valor'])	? (int) $_POST['pin_valor']	: null;
$testeSubmit		= isset($_POST['BtnRegistrar'])	? $_POST['BtnRegistrar']	: null;

$time_start_stats = getmicrotime();

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 
?>
<script language="javascript">
function GP_popupConfirmMsg(msg) { //v1.0
  document.MM_returnValue = confirm(msg);
}
function verifica()
{
    if ((event.keyCode<47)||(event.keyCode>58)){
          alert("Somente numeros sao permitidos");
          event.returnValue = false;
    }
}

// Carrega distribuidoras reflesh 
function carga_distribuidora(){
		var pin_operacao = <?php echo intval($distributor_codigo);?>;
                $(document).ready(function(){
			$.ajax({
				type: "POST",
				url: "ajaxDistribuidoras.php",
				data: "id="+document.getElementById('opr_codigo').value+"&pin_operacao="+pin_operacao,
				success: function(html){
					$('#mostraDistribuidoras').html(html);
                                        carga_valor();
				},
				error: function(){
					alert('erro valor');
				}
			});
		});
}

// Carrega valores reflesh 
function carga_valor(){
		$(document).ready(function(){
			var pin_valor = <?php echo intval($pin_valor);?>;
                        var opr_codigo = $('#opr_codigo').val();
                        $.ajax({
				type: "POST",
				url: "ajaxValor.php",
				data: "id="+document.getElementById('pin_operacao').value+"&opr_codigo="+opr_codigo+"&pin_valor="+pin_valor,
				success: function(html){
					$('#mostraValores').html(html);
				},
				error: function(){
					alert('erro valor');
				}
			});
                });
}

//Carga onChange
$(document).ready(function () {
    
		$('#pin_operacao').change(function(){
			var id = $(this).val();
                	var opr_codigo = $('#opr_codigo').val();
                        var pin_valor = <?php echo intval($pin_valor);?>;
                        $.ajax({
				type: "POST",
				url: "ajaxValor.php",
				data: "id="+id+"&opr_codigo="+opr_codigo+"&pin_valor="+pin_valor,
				success: function(html){
					$('#mostraValores').html(html);
				},
				error: function(){
					alert('erro valor');
				}
			});
                });
                
		$('#opr_codigo').change(function(){
			var id = $(this).val();
                        var pin_operacao = <?php echo intval($distributor_codigo);?>;
                        $.ajax({
				type: "POST",
				url: "ajaxDistribuidoras.php",
				data: "id="+id+"&pin_operacao="+pin_operacao,
				success: function(html){
					$('#mostraDistribuidoras').html(html);
                                        carga_valor();
				},
				error: function(){
					alert('erro valor');
				}
			});
		});
});
</script>
<div class="col-md-12">
        <ol class="breadcrumb top10">
            <li><a href="#" class="muda-aba" ordem="1">BackOffice - Money</a></li>
            <li class="active">Gerar PINs Cartões</li>
        </ol>
    </div>
    <div class="col-md-12 txt-preto fontsize-pp">
<?php
include "pins_card_menu.php";
?>
<table width="100%" class="txt-preto fontsize-pp">
  <tr>
    <td><form name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <table class="table">
          <tr>
            <td width="30%" class=" bg-azul-claro"><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><b> Publisher:<b></font></td>
            <td width="70%" colspan="2" bgcolor="#f5f5fb"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">
		        <select name="opr_codigo" id="opr_codigo" class="combo_normal">
					<option value=''<?php if(!$opr_codigo) echo "selected"?>>Selecione o Publisher</option>
			        <?php foreach ($publisher_array as $key => $value) { ?>
				    <option value=<?php echo "\"".$key.(($opr_codigo==$key)?"\" selected":"\""); ?>><?php echo $value; ?></option>
					<?php } ?>
              </select>
              </font></td>
          </tr>
	<tr>
            <td width="30%" class=" bg-azul-claro"><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><b> Distribuidora:<b></font></td>
            <td width="70%" colspan="2" bgcolor="#f5f5fb"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">
                    <div id='mostraDistribuidoras'>
<?php 
                    if(strlen($opr_codigo)>0) { 
                        if(isset($distributor_codigo)){
?>
                        <input type="hidden" id="pin_operacao" value="<?php echo $distributor_codigo;?>">
<?php
                        }
?>
                    <script language="javascript">
                    carga_distribuidora();
                    </script>
                    <?php
                    }
                    else {?>
                       <select name="pin_operacao" id="pin_operacao" class="combo_normal">
                            <option value="" >Selecione o Publisher</option>
                       </select>
                    <?php } ?>
                    </div>
              </font></td>
          </tr>
		  <tr bgcolor="#F5F5FB">
            <td width="30%" class=" bg-azul-claro"><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><b> Valor do PIN: </b></font></td>
            <td width="70%" colspan="2" bgcolor="#f5f5fb"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">
				<div id='mostraValores'>
				<?php if(strlen($distributor_codigo)>0) { ?>
				<script language="javascript">
				carga_valor();
				</script>
				<?php
				}
				else {?>
				   <select name="pin_valor" id="pin_valor" class="combo_normal">
					<option value="" >Selecione a Distribuidora</option>
				   </select>
				<?php } ?>
			  </div>
              </font></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td width="30%" class=" bg-azul-claro"><font color="#FFFFFF" size="2" face="Arial, Helvetica, sans-serif"><b> Quantidade de PINs no Lote: </b></font></td>
            <td width="70%" colspan="2" bgcolor="#f5f5fb"><font color="#666666" size="2" face="Arial, Helvetica, sans-serif">
                    <input name="pin_qtde" id="pin_qtde" type="text" value="<?php echo $qtde; ?>" size="25" maxlength="25" onKeypress="return verifica();">
            </font></td>
          </tr>
          <tr bgcolor="#FFFFFF">
            <td>&nbsp;</td>
            <td colspan="2">&nbsp;</td>
          </tr>
          <?php if($msg != ""){ ?>
          <tr bgcolor="#FFFFFF">
            <td colspan="3"><font color="red" size="2"><?php echo str_replace("\n", "<br>", $msg)?></font></td>
          </tr>
          <?php } ?>
          <tr>
            <td colspan="3" align="center">
      		<input name="BtnRegistrar" type="submit" id="BtnRegistrar" value="Registrar" class="btn btn-sm btn-info" onClick="GP_popupConfirmMsg('Deseja Gerar PINs neste Formato?');return document.MM_returnValue">
            </td>
          </tr>
           <tr>
            <td colspan="3" align="left">
      		<font color="blue" size="2"><hr>ATEN&Ccedil;&Atilde;O: Os PINS Cartões gerados pela E-Prepag possuem tamanho fixo de <b>18 Posições</b>.
                    <hr>FLUXO de Funcionamento: Para geração de PINs Cartões pela E-PREPAG deve-se:
			<ol>
				<li>Gerar PINs selecionando o Publisher, o Distribuidor, o Valor e Quantidade de PINs Cartão;
				<li>Publicar os PINS gerados;
				<li>Ativar os PINs Publicados;
				<li>E finalmente gerar o arquivo com os PINs ativado.
			</ol>
			<hr>
			</font>
            </td>
          </tr>
          <tr><td colspan="3"> <p>&nbsp;</p><?php
                if ($testeSubmit == 'Registrar') {
                        if (!is_null($opr_codigo)&& (!empty($opr_codigo))) {
                            if (!is_null($distributor_codigo)&& (!empty($distributor_codigo))) {
                                    if (!is_null($pin_valor)) { //&&(!empty($pin_valor))
                                            if (!is_null($qtde)&&$qtde<>0) {
                                                    $ps = new Pins_Card();
                                                    echo "<pre>";
                                                            $ps->gera_lote(intval($opr_codigo), intval($distributor_codigo), intval($pin_valor), intval($qtde));
                                                    echo "</pre>";
                                            } else {
                                                    echo "&Eacute; necessario informar a Quantidade.<br>";
                                            }
                                    } else {
                                            echo "&Eacute; necessario selecionar um Valor.<br>";
                                    }
                            } else {
                                    echo "&Eacute; necessario selecionar uma Distribuidora.<br>";
                            }
                        } else {
                                echo "&Eacute; necessario selecionar um Publisher.<br>";
                        }
                }
          ?></td></tr>
		  <tr><td colspan="3"> <?php require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";?></td></tr>
        </table>
      </form></td>
  </tr>
</table>
<table class="table txt-preto fontsize-pp">
  <tr align="center"> 
	<td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td><td bgcolor="#FFFFFF" class="texto"><?php echo " Segundos: ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.')." "; ?></font></td><td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td>
  </tr>
</table>
</html>
<?php
if (!empty($pin_valor)) {
	$fp = fopen($raiz_do_projeto . 'log/geracao_pin.txt', 'a+');
	$conteudo = PHP_EOL."====================Inicio Gift Card============================".PHP_EOL;
	$conteudo .= " Segundos: ".number_format(getmicrotime() - $time_start_stats, 2, '.', '.').PHP_EOL;
	$conteudo .= " Qtde de PINs : ".number_format($qtde, 0, '.', '.').PHP_EOL;
	$conteudo .= "=====================Fim Gift Card==============================".PHP_EOL;
	fwrite($fp, $conteudo);
	fclose($fp);
}//end if (!empty($pin_valor))
?>