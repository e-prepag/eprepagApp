<?php 

include "../includes/classPrincipal.php"; ?>
<?php  include "../../../incs/topo_bko.php"; ?>
<?php  include "../../financeiro/corte/corte_constantes.php"; 
require_once "/www/includes/bourls.php";

$total_query = "select ug_id from dist_usuarios_games where ug_ativo = '1' " ;

$total_rs_query = SQLexecuteQUERY($total_query);

$total_row = pg_num_rows($total_rs_query);

?>

<script src="../includes/jquery.js" language="javascript"></script>
<script>
function roda_registros_total() {

var id_lan = $("#id_lan").val();
var total_lan = eval($("#limite").val());

		if (id_lan < total_lan ) {


				$.ajax({
								
							type: "POST",
							url: "balanco_gera_registros.php",
							data: {n:id_lan,d:total_lan},
							success: function(html){
									$("#conteudo").html(html);
									id_lan ++;
									
									$("#id_lan").attr('value',id_lan) ; 
									roda_registros_total();
							 } // fim function success
				}); //fim ajax

		}// fim if
}// fim function

function roda_registros_individual() {

	var id_lan = $("#cod_lan").val();

	$.ajax({								
		type: "POST",
		url: "balanco_gera_registros_unitario.php",
		data: {id:id_lan},
		success: function(html){
		$("#conteudo").html(html);
		 } // fim function success
	}); //fim ajax
	
}// fim function

</script>
<script>
$(document).ready( function () { 

$('#res_total').html( $('#total_entrada').val());		

$('#res_saida').html( $('#total_saida').val());	

$('#res_comissao').html( $('#total_comissao').val());	

//roda_registros();
});

</script>
<table width="894" border="0" cellspacing="0" cellpadding="0">
  <tr> 
    <td width="891" height="102" valign="top">
    <table width="894" border="0" cellpadding="0" cellspacing="2">
          <tr> 
            <td colspan="5">
				<table width="894" border="0" cellpadding="0" cellspacing="0" dwcopytype="CopyTableCell">
					<tr> 
					    <td width="894" height="21" bgcolor="00008C">
							<font face="Arial, Helvetica, sans-serif" size="2" color="#FFFFFF"><b>Registrador de Balan&ccedil;os hist&oacute;ricos</b></font></td>
				  </tr>
				</table>
		      <table border='0' width="100%" cellpadding="2" cellspacing="0">
        			<tr bgcolor=""> 
			          <td><img src="Logotipo-Engrenagens.png"/>&nbsp;</td>
			          <td><input name="id_lan" type="hidden" id="id_lan" value="0">
		             <input name="limite" type="hidden" id="limite" value="<?=$total_row?>"></td>
		        </tr>
        			<tr bgcolor="">
        			  <td colspan="2"><div align="center"><strong>SUPORTE DE INSTALA&Ccedil;&Atilde;O DOS BALANCOS</strong></div></td>
        			</tr>
        			<tr bgcolor="">
        			  <td colspan="2"><p>1) Crie a tabela dist_balancos na seguinte estrutura:</p>
     			     <p><font color="#009933" size="2">CREATE TABLE dist_balancos<br>
     			       (<br>
ug_id bigserial NOT NULL,<br>
ug_saldo double precision,<br>
ug_limite double precision,<br>
ug_data_balanco timestamp without time zone NOT NULL,<br>
ug_id_lan integer, -- id - da lan house<br>
ug_valor_balanco double precision,<br>
ug_tipo_lan integer,<br>
CONSTRAINT dist_balancos_pkey PRIMARY KEY (ug_id),<br>
CONSTRAINT dist_balancos_ug_id_lan_fkey FOREIGN KEY (ug_id_lan)<br>
REFERENCES dist_usuarios_games (ug_id) MATCH SIMPLE<br>
ON UPDATE NO ACTION ON DELETE NO ACTION<br>
) <br>
WITHOUT OIDS;<br>
ALTER TABLE dist_balancos OWNER TO epp_prod;<br>
COMMENT ON TABLE dist_balancos IS 'Tabela de balan&ccedil;os das lan houses';<br>
COMMENT ON COLUMN dist_balancos.ug_id_lan IS 'id - da lan house';<br>
     			     </font></p>
     			     <p><font color="#009933" size="2">-- Index: fki_</font></p>
     			     <p><font color="#009933" size="2">-- DROP INDEX fki_;</font></p>
     			     <p><font color="#009933" size="2">CREATE INDEX fki_<br>
     			       ON dist_balancos<br>
     			       USING btree<br>
     			       (ug_id_lan);</font></p>
     			     <p>2) Execute :  <a href="http://dev.e-prepag.com.br:<?php echo $server_port ;?>/bkov2_prepag/dist_commerce/balanco/balanco_gera_registros(visao).php">http://dev.e-prepag.com.br:<?php echo $server_port ;?>/bkov2_prepag/dist_commerce/balanco/balanco_gera_registros(visao).php</a> </p>
     			     <p>Essa tela ir&aacute; criar o ponto inicial na tabela de balancos seu motor esta nas paginas:</p>
     			     <p> <a href="http://dev.e-prepag.com.br:<?php echo $server_port ;?>/bkov2_prepag/dist_commerce/balanco/balanco_gera_registros(visao).php">http://dev.e-prepag.com.br:<?php echo $server_port ;?>/bkov2_prepag/dist_commerce/balanco/balanco_gera_registros.php</a></p>
     			     <p>solocita dois parametros N e D, mas n&atilde;o &eacute; necess&aacute;rio passa-los pois a interface controla isso.</p>
     			     <p>N = o numero do PDVde um select feito na interface que esta ativa</p>
     			     <p>D = total de PDVs encontradas no estado ativo</p>
     			     <p> Voc&ecirc; poder&aacute; configurar das seguinte forma:</p>
     			     <p>- A data mais antiga do balanco na linha 41: </p>
     			     <p> $data = $BALANCO_DATA_ABERTURA;</p>
     			     <p>neste caso a variavel dinamica usa como data de termino a abertura do PDV, pois a gera&ccedil;&atilde;o &eacute; feita do presente ao passado</p>
     			     <p>- O intervalo entre balancos pode ser modificado na linha 46:</p>
     			     <p> $n_dias = 10;</p>
     			     <p>neste caso setei para dez ent&atilde;o o processo seguir&aacute; 2010-10-26 --&gt;&gt; 2010-10-16--&gt;&gt;2010-10-06--&gt;&gt;2010-09-27--.... At&eacute; a data de abertura dela , utilizando como base dos calculos as vendas e os pagamentos de boleto para afirmar o saldo.</p>
     			     <p>3) Execute :   <a href="http://dev.e-prepag.com.br:<?php echo $server_port ;?>/bkov2_prepag/dist_commerce/balanco/balanco_visao.php">http://dev.e-prepag.com.br:<?php echo $server_port ;?>/bkov2_prepag/dist_commerce/balanco/balanco_visao.php</a> </p>
     			     <p>Esta pagina serve para calcular o tempo da opera&ccedil;&atilde;o de inser&ccedil;&atilde;o de balancos periodicos da data atual,  traz estat&iacute;sticas de tempo e qual venda recente foi encontrada, os balancos nessa parte s&oacute; ser&atilde;o gerados se houver alguma venda ou pagamento com data mais recente que o ultimo balanco encontrado.</p>
     			     <p>A pagina autom&aacute;tica que realiza a inser&ccedil;&atilde;o real esta em:</p>
     			     <p>.\bkvo2_prepag\dist_commerce\balanco\executa_balanco.php</p>
     			     <p><font color="#FF0000">Essa simplesmente executa e n&atilde;o exibe nenhuma informa&ccedil;&atilde;o apenas realiza as inser&ccedil;&otilde;es o desempenho dela esta de acordo com o calculo que a outra pagina faz.</font></p>
     			     <p><font color="#000000">4) </font>Pronto est&aacute; instalado e poder&aacute; ser visualizadas pelas seguintes paginas:<br>
     			     </p>
     			     <p><strong>BACKOFFICE</strong></p>
     			     <p> <a href="http://dev.e-prepag.com.br:<?php echo $server_port ;?>/bkov2_prepag/dist_commerce/com_pesquisa_extrato_geral_rapida.php">http://dev.e-prepag.com.br:<?php echo $server_port ;?>/bkov2_prepag/dist_commerce/com_pesquisa_extrato_geral_rapida.php</a> <br>
     			       <a href="http://dev.e-prepag.com.br:<?php echo $server_port ;?>/bkov2_prepag/dist_commerce/com_pesquisa_balancos.php">http://dev.e-prepag.com.br:<?php echo $server_port ;?>/bkov2_prepag/dist_commerce/com_pesquisa_balancos.php</a> <br>
     			       <br>
     			       <strong>MINHA LAN HOUSE:</strong></p>
     			     <p><a href="http://dev.e-prepag.com.br/prepag2/dist_commerce/balanco/balanco_consulta.php">http://dev.e-prepag.com.br/prepag2/dist_commerce/balanco/balanco_consulta.php</a><br>
     			       <a href="http://dev.e-prepag.com.br/prepag2/dist_commerce/conta/lista_extratos_rapido.php">http://dev.e-prepag.com.br/prepag2/dist_commerce/conta/lista_extratos_rapido.php</a></p>
     			     <p>LISTA DE ARQUIVOS E DESCRI&Ccedil;&Atilde;O:</p>
     			     <p><strong>MINHA LAN HOUSE:</strong></p>
     			     <p><font color="#FF0000">web\prepag2\dist_commerce\includes\ajax_salva_configuracao.php</font><br>
  -- componente que grava que tipo de ordena&ccedil;&atilde;o de extrato a pessoa quer</p>
     			     <p><font color="#FF0000">web\prepag2\dist_commerce\conta\lista_extratos_rapido.php</font><br>
     			       -- painel de listagem de extrato</p>
     			     <p><font color="#FF0000">web\prepag2\dist_commerce\balanco\balanco_consulta.php</font><br>
     			       -- lista todos os balan&ccedil;os realizados</p>
     			     <p><font color="#FF0000">web\prepag2\dist_commerce\conta\index.php </font><br>
     			       --atualizar parar mostrar link das novas op&ccedil;&otilde;es <font color="#FF0000">(cuidado pois a interface do DEV apresenta divergencia com a do site que esta no ar)</font></p>
     			     <p>---------------------------------------------------------------------------</p>
     			     <p><strong>BACKOFFICE</strong></p>
     			     <p><font color="#FF0000">web\bkov2_prepag\dist_commerce\com_pesquisa_balancos.php</font><br>
  -- painel do backoffice que lista e busca balancos nos hist&oacute;ricos das lanhouses</p>
     			     <p><font color="#FF0000">web\bkov2_prepag\dist_commerce\index.php </font><br>
     			       -- atualizar pois mostra link das novas op&ccedil;&otilde;es  <font color="#FF0000">(cuidado pois a interface do DEV apresenta divergencia com a do site que esta no ar)</font><br>
   			       </p>
     			     <p><font color="#FF0000">web\bkvo2_prepag\dist_commerce\com_pesquisa_extrato_geral_rapida.php</font><br>
     			       -- painel do backoffice que mostra a lista geral de extrato,balancos,boletos e vendas </p>
     			     <p>------------------------------------------------------------------------------------</p>
     			     <p><font size="6"><strong>FERRAMENTAS:</strong></font></p>
     			     <p>FERRAMENTA 1 - GERADOR DE PONTO INICIAL </p>
     			     <p>&Eacute; obrigat&oacute;rio gerar o ponto inicial dos balancos, pois a ferramenta 2 precisa de uma referencia para gerar os balan&ccedil;os seguintes periodicamente quando for executada. POR SEGURAN&Ccedil;A O CODIGO DE GRAVA&Ccedil;&Atilde;O ESTA TRAVADO.</p>
     			     <p><font color="#FF0000">web\bkov2_prepag\dist_commerce\balanco\balanco_gera_registros(visao).php</font><br>
  -- painel de monitoramento da ferramenta que gera balancos de hist&oacute;ricos anteriores at&eacute; a data de cria&ccedil;&atilde;o ou aonde desejar (alterando c&oacute;dgo)</p>
     			     <p><font color="#FF0000">web\bkov2_prepag\dist_commerce\balanco\balanco_gera_registros_unitario.php</font> (codigo travado) linha 390 e 400<br>
     			       -- a&ccedil;&atilde;o interna de balanco balanco_gera_registros(visao).php</p>
     			     <p><font color="#FF0000">web\bkov2_prepag\dist_commerce\balanco\balanco_gera_registros.php</font> (codigo travado) linha 395 e 405<br>
-- a&ccedil;&atilde;o interna de balanco balanco_gera_registros(visao).php</p>
     			     <p>-----------------------------------------------------------------------------------</p>
     			     <p>FERRAMENTA 2 - GERADOR PER&Iacute;ODICO </p>
     			     <p><font color="#FF0000">web\bkvo2_prepag\dist_commerce\balanco\balanco_visao.php</font> (Apenas Exibi&ccedil;&atilde;o)<br>
     			       -- painel da ferramenta que acompanha o gerador do balanco mais recente</p>
     			     <p><font color="#FF0000">web\bkov2_prepag\dist_commerce\balanco\balanco_simula.php</font> (somente exibi&ccedil;ao)<br>
-- a&ccedil;&atilde;o interna de balanco_vis&atilde;o.php traz os valores de tempo, vendas, query ,numeros e etc</p>
     			     <p><font color="#FF0000">web\bkvo2_prepag\dist_commerce\balanco\executa_balanco.php<font color="#000000"> (codigo travado**)</font></font></p>
     			     <p><font color="#FF0000">** Aten&ccedil;&atilde;o para funcionar tudo no DEV eu usei o Decrypt, pois na &eacute;poca da tarefa 7 estavamos trabalhando com encripta&ccedil;&atilde;o de limites e saldo, a opera&ccedil;&atilde;o foi cancelada devido a complexidade das partes do sistema que trabalham com os limites e o saldo, aqui no dev todas as lan est&atilde;o com seu saldo encriptado ent&atilde;o n&atilde;o se esque&ccedil;a de comentar as linhas que usam o decrypt e descomentar as linhas comentadas, o c&oacute;digo tem coment&aacute;rios que ir&atilde;o facilitar.</font></p>
     			     <p><font color="#FF0000">- linhas 21,97,106,114,119 e 137</font></p>
     			     <p><font color="#FF0000">
                 <font color="#000000">-- realiza o balanco mais recente sem exibir estatisticas</font></font>, seria como um motor, ele selecionar&aacute; todas as lan houses ativas e ir&aacute; gravar 1 balan&ccedil;o para cada uma. N&atilde;o possui interface, nem est&aacute;tistica, mas segue o tempo de desempenho simulado nas paginas de simula&ccedil;&atilde;o.</p></td>
     			  </tr>
        			<tr bgcolor="">
        			  <td colspan="2">&nbsp;</td>
        			</tr>
		      </table>            </td>
     	  </tr>
            <tr>
            <td colspan='2' id ='conteudo'>  </td>
            </tr>
		</table>
      
    
    </td>
      </tr>

      </table>
      </body></html>