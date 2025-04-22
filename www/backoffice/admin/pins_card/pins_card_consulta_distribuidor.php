<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";
require_once $raiz_do_projeto . "class/classPinsCard.php";
require_once $raiz_do_projeto . "class/classIntegracaoPinCard.php";
require_once $raiz_do_projeto . "class/classIntegracaoPin.php";

set_time_limit ( 30000 ) ;
?>
<script language="JavaScript">
<!--
function reload() {
        if(document.form1.pin_codigo.value.length != 18) {
            alert('O tamanho do PIN está errado!\nDeve ser 18 posições!');
            return false;
        }
        else if(document.form1.acao.value == '') {
            alert('É necessário a seleção da ação para execução!');
            return false;
        }
        else return true;
}
function verifica()
{
        if ((event.keyCode<47)||(event.keyCode>58)){
                  alert("Somente numeros sao permitidos");
                  event.returnValue = false;
        }
}
-->
</script>
<?php

$operacao_array		= VetorDistribuidorasCard();
$acao			= isset($_POST['acao'])			? $_POST['acao']		: null;
$pin_codigo		= isset($_POST['pin_codigo'])		? $_POST['pin_codigo']		: null;
$time_start_stats 	= getmicrotime();

//Vericações e junto ao distribuidor
$msg_pin = "";
	
//Recupera as vendas
if($btExecutar=='Executar'){
    
                
    //Variavel contendo o Código do Distribuidor
    $cod_distrib = retornaID_Distibuidora($pin_codigo);

    //Arquivo contendo o Include Dinâmico
    $tmp_arq = $raiz_do_projeto . "partners_cards/".$operacao_array[$cod_distrib]."/config.inc.".$operacao_array[$cod_distrib].".php";
    
    //Testando se o PIN pertence a algum distribuidor integrado
    if(array_key_exists($cod_distrib, $operacao_array) && file_exists($tmp_arq)) {
        

            //incluindo a classe dinamicamente de acordo com o PIN informado
            require_once ($tmp_arq);

            //Instanciando o objeto dinamicamente de acordo com o PIN informado
            $teste = new $operacao_array[$cod_distrib];
            //Carregando os parametros para execução
            $params_distributor = array(
                            'pin'		=> $pin_codigo,
                            );

            switch ($acao) {
                    case 'Consulta':
                        $teste->Req_EfetuaConsulta($params_distributor,$resposta, INQUIRY);
                        $msg_pin = $resposta;
                        break;
                    case 'Utilizacao':
                        $teste->Req_EfetuaConsulta($params_distributor,$resposta, REDEEM);
                        $msg_pin = $resposta;
                        break;
                    case 'Reversao':
                        $teste->Req_EfetuaConsulta($params_distributor,$resposta, REVERSE);
                        $msg_pin = $resposta;
                        break;
            } //end switch
            
    }//end if(array_key_exists($cod_distrib, $operacao_array))
    else {
        $msg_pin = "<font color='red'>PIN não pertence a um Distribuidor Cadastrado.</font>";
    }
    
}//end if($btExecutar=='Executar')
?>
<div class="col-md-12">
     <ol class="breadcrumb top10">
         <li><a href="#" class="muda-aba" ordem="1">BackOffice - Money</a></li>
         <li class="active">Comunicação com o Distribuidor</li>
     </ol>
 </div>
<div class="col-md-12 txt-preto fontsize-pp">
<?php
include "pins_card_menu.php";
?>
<form name="form1" method="post" action="pins_card_consulta_distribuidor.php" onsubmit="javascript:return reload();">
<table class="txt-preto fontsize-pp table">
    <tr valign="top" align="center">
      <td>
        <table class="txt-preto fontsize-pp table">
    	        <tr bgcolor="F0F0F0">
                    <td class="texto" align="center" width="400"><b>Ação</b></td>
                    <td class="texto" align="center"><b>PIN Card</b></td>
    	        </tr>
    	        <tr bgcolor="F5F5FB">
                    <td class="texto" align="center"><nobr>&nbsp;
                              <select name="acao" id="acao" class="combo_normal">
                                      <option value=''<?php if(!$acao) echo "selected"?>>Selecione a Ação</option>
                                      <option value='Consulta'<?php echo (($acao=="Consulta")?" selected":""); ?>>Consultar Somente no Distribuidor</option>
                                      <option value='Utilizacao'<?php echo (($acao=="Utilizacao")?" selected":""); ?>>Utilizar Somente no Distribuidor</option>
                                      <?php
                                      if(b_IsBKOUsuarioAdminPINs()) {
                                      ?>
                                      <option value='Reversao'<?php echo (($acao=="Reversao")?" selected":""); ?>>Reverter a Utilização Somente no Distribuidor</option>
                                      <?php
                                      }//end b_IsBKOUsuarioAdminPINs
                                      ?>
                              </select>
                    </td>
                    <td class="texto" align="center"><nobr>&nbsp;
                              <input name="pin_codigo" type="text" class="form" id="pin_codigo" value="<?php echo $pin_codigo ?>" size="18" maxlength="18" onKeypress="return verifica();">
                    </td>
    	        </tr>
            	<tr bgcolor="F5F5FB">
                    <td class="texto" align="center" colspan="2">&nbsp;<input type="submit" name="btExecutar" value="Executar" class="btn btn-sm btn-info"></td>
		</tr>
	</table>
        <?php
        if(!empty($msg_pin)) {
        ?>
	<table class="txt-preto fontsize-pp table">
    	        <tr bgcolor="F0F0F0">
    	          <td class="texto" align="center" width="5%"><b>Resultado da <?php echo $acao; ?></b>&nbsp;</td>
    	        </tr>
                <?php
                if(is_array($msg_pin)){
                    echo "<tr><td class='texto'><pre>".print_r($msg_pin,true)."</pre></td></tr>";
                }
                else echo "<tr><td class='texto'>".$msg_pin."</td></tr>";
                ?>
	</table>
        <?php
        } //end if(!empty($msg_pin))
        ?>
      </td>
    </tr>
</table>
<br>&nbsp;
<table class="txt-preto fontsize-pp table">
    <tr align="center"> 
          <td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td><td bgcolor="#FFFFFF" class="texto">Processamento em <?php echo number_format(getmicrotime() - $time_start_stats, 2, '.', '.') ?> s.</font></td><td bgcolor="#FFFFFF" class="texto" width="10%">&nbsp;</td>
    </tr>
</table>

</form>
</div>
</body>
</html>
<?php
require_once $raiz_do_projeto."backoffice/includes/rodape_bko.php";
/*
function retornaID_Distibuidora($pin){
	
        return substr($pin,0,2);
	
}//end function retornaID_Distibuidora
*/
?>
