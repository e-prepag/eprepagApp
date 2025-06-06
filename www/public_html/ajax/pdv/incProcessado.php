<?php require_once __DIR__ . '/../../../includes/constantes_url.php'; ?>
<?php
//script equivalente a C:/Sites/E-Prepag/www/web/prepag2/dist_commerce/includes/incProcessado_dr.php

// include do arquivo contendo IPs DEV
require_once "../../../includes/constantes.php";
require_once DIR_INCS . "configIP.php";
require_once DIR_INCS . "pdv/constantes.php";
$server_url = "" . EPREPAG_URL . "";
if(checkIP()) {
    $server_url = $_SERVER['SERVER_NAME'];
}

if(isset($_SESSION['dist_usuarioGames_ser']) && !is_null($_SESSION['dist_usuarioGames_ser']))
{

    //Dados da LAN House
    $usuarioGames = unserialize($GLOBALS['_SESSION']['dist_usuarioGames_ser']);

    $sql = "
    select p.pin_valor,vgm.vgm_nome_produto,vgm.vgm_nome_modelo,p.pin_codinterno, p.pin_vencimento, p.pin_codigo, p.pin_lote_codigo, p.pin_serial, vgmp.vgmp_impressao_qtde, vgmp.vgmp_impressao_ult_data, vgm.vgm_id, vgm_pin_request 
    from pins_dist p 
        inner join tb_dist_venda_games_modelo_pins vgmp on p.pin_codinterno = vgmp.vgmp_pin_codinterno 
        inner join tb_dist_venda_games_modelo vgm on vgm.vgm_id = vgmp.vgmp_vgm_id 
        inner join tb_dist_venda_games vg on vg.vg_id = vgm.vgm_vg_id 
    where vg.vg_id = ".$GLOBALS['_SESSION']['venda']." 
        and vg.vg_ug_id = ".$usuarioGames->getId()." 
    order by vgmp.vgmp_impressao_ult_data desc, vgmp.vgmp_impressao_qtde, p.pin_serial;";

    $rs = SQLexecuteQuery($sql);
    if(!$rs) {
    ?>
        <p class="text-red">Nenhum produto encontrado (ERRO: WM390).</p>
    <?php
        die();
    } //end if(!$rs)
    
    //Calculando se a quantidade de PINs é igual a quantidade de registros 
    $totalRegistros = pg_num_rows($rs);
    $sql = "select sum(vgm_qtde) as total from tb_dist_venda_games_modelo where vgm_vg_id = ".$GLOBALS['_SESSION']['venda'];
    $rsTotal = SQLexecuteQuery($sql);
    $rsTotalRow = pg_fetch_array($rsTotal);
    $totalPins = $rsTotalRow['total'];
    ?>
    <div class="">
        <form name="form1" id="form1" target="_blank" action="/creditos/imprimir_cupom.php" method="post">
            <input type="hidden" id="tf_v_codigo_detalhe" name="tf_v_codigo_detalhe" value='<?php echo $GLOBALS['_SESSION']['venda']; ?>'>
            <input type="hidden" id="imprimir_ou_csv" name="imprimir_ou_csv" value="">
        <?php
        //Variavel para habilitar botão de envio de email
        $podeEnviarEmail = true;
        
        if($totalPins > 1) {
            $checkbox = true;
            if($totalPins == $totalRegistros || $totalRegistros > 0) {
        ?>
        <p class="txt-azul"><em><strong>Pedido processado</strong></em></p>
        <p class="txt-verde"><em>Selecione a forma que deseja entregar o PIN ao seu cliente</em></p>
        <div class="txt-preto pull-left col-md-12 text-left">
            <input type="checkbox" id="checkall">
            <label for="checkall" class="fontweightnormal">Selecionar Todos</label>
        </div>
        <?php
            }//end if($totalPins == $totalRegistros || $totalRegistros > 0)
        ?>
        <!-- NOVO BLOCO -->
        <div class="row text-center espacamento">
            <table class="table bg-branco borda-fina txt-cinza">
                <tbody>
        <!-- FIM NOVO BLOCO -->
        <!--<table class='box-lan-datagrid-pin'>-->
    <?php
            // contador para incrementar o nome do checkbox e funcionar o novo layout
            // para verificar o checkbox repetir até a quantidade total de PINs do pedido (pg_num_rows($rs))
            $contador = 1;
            while($rs_row = pg_fetch_array($rs)) {
                if($rs_row['vgm_pin_request'] > 0)
                    $podeEnviarEmail = false;
    ?>    
                <tr>
                    <td id="td2emitir<?php echo $contador;?>">
    <?php 
                if($rs_row['vgmp_impressao_qtde'] > 0) 
                { 
    ?>
                    <span class="txt-verde glyphicon glyphicon-ok t0"></span>
    <?php 
        } 
    ?>
                    </td>
                    <td>
                        <input type="checkbox"  value="<?php echo $rs_row['pin_codinterno']; ?>" id="emitir<?php echo $contador;?>" name="emitir<?php echo $contador;?>">
                        <label for="emitir<?php echo $contador;?>"></label>
                    </td>

                    <td id="tdemitir<?php echo $contador;?>">
    <?php //Verificando se já foi impresso
                    if($rs_row['vgmp_impressao_qtde'] > 0) {
                        echo "<span class='pull-left'>Emitido</span> ";
                        $sql = "select * from tb_dist_venda_games_produto_email where vgpe_pin_codinterno = ".$rs_row['pin_codinterno'].";";
                        $rs_forma = SQLexecuteQuery($sql);
                        if($rs_forma) {
                                $total_email = 0;
                                $lista_emails = "";
                                while($rs_forma_row = pg_fetch_array($rs_forma)) {
                                        $total_email++;
                                        if(empty($lista_emails))
                                                $lista_emails .= $rs_forma_row['vgpe_email'];
                                        else $lista_emails .= ",\n ".$rs_forma_row['vgpe_email'];
                                } //end while
                                //echo "[$total_email]";
                                if($total_email >= $rs_row['vgmp_impressao_qtde'] ) {
        ?>
                                    <span class="glyphicon glyphicon-envelope t0 left" alt='Email para: <?php echo $lista_emails; ?>' title='Email para: <?php echo $lista_emails; ?>'></span>
                                    <div style="display:none"><?php echo $lista_emails;?></div>
        <?php
                                }
                                elseif($total_email == 0){
        ?>
                                    <span class="glyphicon glyphicon-print t0 left" alt='Impresso' title='Impresso'></span>
        <?php
                                }
                                else {
        ?>
                                    <span class="glyphicon glyphicon-envelope t0 left"alt='Email para: <?php echo $lista_emails; ?>' title='Email para: <?php echo $lista_emails; ?>'></span>
                                    <span class="glyphicon glyphicon-print t0 left" alt='Impresso' title='Impresso'></span>
        <?php                                
                                }
                        }//end if($rs_forma)

                }//end if($rs_row['vgmp_impressao_qtde'] > 0)
    ?>
                    </td>
                    <td><?php echo $rs_row['vgm_nome_produto'].": ".$rs_row['vgm_nome_modelo']; ?></td>
                    <td class="txt-verde">R$ <?php echo number_format($rs_row['pin_valor'], 2, ',', '.'); ?></td>
            </tr>
        <?php
                $contador++;
            }//end while($rs_row = pg_fetch_array($rs))
            if($totalPins > $totalRegistros) {
                $podeEnviarEmail = false;
                $subTotal = $totalPins - $totalRegistros;
                for($i = $subTotal; $i>0; $i-- ) {
        ?>    
                <tr>
                    <td colspan="5">
                        PIN ainda em processamento. Aguarde um instante.
                    </td>
                </tr>
        <?php                
                }//end for
                echo "\n<script language='JavaScript' type='text/JavaScript'>refresh_snipet = 1;</script>";
            }//end if($totalPins > $totalRegistros)
        ?>
        </table>
        <?php
        }//end if($totalPins > 1)
        else {
            if($totalPins == $totalRegistros) {
                
                $rs_row = pg_fetch_array($rs);
                if($rs_row['vgm_pin_request'] > 0)
                    $podeEnviarEmail = false;
            
        ?>
            <p class="txt-azul"><em><strong>Pedido processado</strong></em></p>
            <p class="txt-verde"><em>Selecione a forma que deseja entregar o PIN ao seu cliente</em></p>
            <input type="checkbox"  value="<?php echo $rs_row['pin_codinterno']; ?>" id="emitir" name="emitir" checked="checked" style="display:none">
        <?php
            //Colocar aqui o checkbox invisivel com o unico PIN da venda
            }//end if($totalPins == $totalRegistros)
            else {
                echo "\n<script language='JavaScript' type='text/JavaScript'> refresh_snipet = 1;</script>";
        ?>
            <div class="row text-center espacamento">
                PIN ainda em processamento. Aguarde um instante.
            </div>
        <?php        
            }//end else if($totalPins == $totalRegistros)
        }//end else do if(pg_num_rows($rs) > 1)


        ?>
        </form>
    </div>
    <script language="javascript">
    function showValues() {
      var str = $('form').serialize();
      return str;
    }

    var iptCheckBox = <?php echo (isset($checkbox)) ? "true" : "false";?>;
    //funcao de Envio de Email
    $(function(){
        
            $("#checkall").click(function(){

                var res = this.checked;

                $(':checkbox').each(function() {
                    this.checked = res;
                });
            });

        
       $("#emailPin").click(function(){

            if($(":checkbox:checked").length > 0 || iptCheckBox === false)
            {
                $.ajax({
                      type: 'POST',
                      url: '/creditos/ajax/emailCupom.php',
                      data: showValues(),
                      beforeSend: function(){
                          $('#box-lan-hope').html("<img src='/imagens/loading1.gif' border='0' title='Pedido aguardando processamento....'/><p class='text-red'>Pedido aguardando processamento.</p>");
                      },
                      success: function(html){
                          //$('#box-lan-hope').html(html);
                          $('#box-lan-hope').html(html);
                          //console.log(html);
                      },
                      error: function(){
                              alert('Erro Valor');
                      }
                  });
            }else
            {
                $(".errorBox").html("Selecione uma opção.");
            }
       });

       $("#imprimirPin").click(function(){
            var id = false;
            $("input:checkbox").each(function()
            {
                if($(this).is(":checked"))
                {
                    id = $(this).attr("id");
                    var td = $("#td"+id);
                    var td2 = $("#td2"+id);
                    var txt = td.html();
                    if(typeof txt != "undefined")
                    {

                        if(txt.indexOf("glyphicon-print") < 0)
                        {
                            if(txt.indexOf("Emitido") < 0)
                            {
                                td.append("<span class='pull-left'>Emitido</span> ");
                            }
                            td.append("<span class='glyphicon glyphicon-print t0 left' alt='Impresso' title='Impresso'></span>");
                        }

                        if(td2.html().indexOf("glyphicon-ok") < 0)
                        {
                            td2.html("<span class='txt-verde glyphicon glyphicon-ok t0'></span>")
                        }
                    }
                }
            });
            $('#imprimir_ou_csv').val('imprimir');

            if(id !== false || iptCheckBox == false)
            {                
                document.form1.submit();
                $(':checkbox').each(function() {
                    if($(this).attr("id") !== "emitir")
                        this.checked = false;
                });
                
            }else
            {
                $(".errorBox").html("Selecione uma opção.");
            }    
       });

        $("#downloadPin").click(function(){
            
            $('#imprimir_ou_csv').val('csv');
            
            if($(":checkbox:checked").length > 0 || iptCheckBox === false)
            {
                $.ajax({
                      type: 'POST',
                      url: '/creditos/imprimir_cupom.php',
                      data: showValues(),
                      beforeSend: function(){
                        $('#downloadPin').html("<span><i>Iniciando download..</i></span>");
                      },
                      
                      success: function(html){
                        $('#downloadPin').html(html);
                        $('#downloadPin').html("<span>Download</span>");
                      },
                      error: function(){
                          alert('Problema no download do PIN');
                      }
                  });
            }else
            {
                $(".errorBox").html("Selecione uma opção.");
            }
        });
    });
    </script>
<?php
    if($totalPins == $totalRegistros || $totalRegistros > 0) {
?>
    <p class="txt-vermelho errorBox"></p>
    <p>
<?php
     
       if($podeEnviarEmail) {
?>      
            <button type="button" class="btn btn-success" id="emailPin" title="Clique aqui para enviar o PIN por email">Enviar por e-mail</button> 
            <button type="button" class="btn btn-success"  id="downloadPin" title="Clique aqui para baixar as informações do PIN em formato CSV/Excel" value="">Download</button>
<?php
       } //if($podeEnviarEmail)
?>      
            <button type="button" class="btn btn-success" id="imprimirPin" value="">Imprimir</button>
			<a class="btn btn-success" href="<?= EPREPAG_URL_HTTPS ?>/creditos/pedidos.php">Meus pedidos</a>
    </p>
<?php 

	
    } //end if($totalPins == $totalRegistros || $totalRegistros > 0)
}else {
?>
    <p>Seu login expirou. Por favor, faça novamente o login para imprimir seu cupom.</p>
<?php
}//end else do if(isset($_SESSION['dist_usuarioGames_ser']) && !is_null($_SESSION['dist_usuarioGames_ser']))
?>
</body>
</html>