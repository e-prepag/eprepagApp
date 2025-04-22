<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto."includes/security.php";
if($acao == 'novo')
{
    $acao = 'inserir';
}
else
{
    $acao = 'atualizar';
}
?>
<script type="text/javascript">
function validaUsuario()
{
	if (document.frmPreCadastro.ano.value == "")
    {
        alert("Favor informar o Ano dos Dados de Complice.");
        document.frmPreCadastro.ano.focus();
        return false;
    }
    if (document.frmPreCadastro.mes.value == "")
    {
        alert("Favor informar o Mês dos Dados de Complice.");
        document.frmPreCadastro.mes.focus();
        return false;
    }
    if (isNumeric(document.frmPreCadastro.c_custo_mkt_credenciado.value))
    {
        alert("Favor informar o Custo de Marketing do Credenciador.");
        document.frmPreCadastro.c_custo_mkt_credenciado.focus();
        return false;
    }
    if (isNumeric(document.frmPreCadastro.c_custo_risco_credenciador.value))
    {
        alert("Favor informar o Custo de Risco do Credenciador.");
        document.frmPreCadastro.c_custo_risco_credenciador.focus();
        return false;
    }
    if (isNumeric(document.frmPreCadastro.c_custo_outros_credenciador.value))
    {
        alert("Favor informar Outros Custos do Credenciador.");
        document.frmPreCadastro.c_custo_outros_credenciador.focus();
        return false;
    }
    if (isNumeric(document.frmPreCadastro.c_receita_mkt_emissor.value))
    {
        alert("Favor informar Receita de Marketing do Emissor.");
        document.frmPreCadastro.c_receita_mkt_emissor.focus();
        return false;
    }
    if (isNumeric(document.frmPreCadastro.c_receita_outras_emissor.value))
    {
        alert("Favor informar Outras Receitas do Emissor.");
        document.frmPreCadastro.c_receita_outras_emissor.focus();
        return false;
    }
    if (isNumeric(document.frmPreCadastro.c_custo_risco_emissor.value))
    {
        alert("Favor informar Custo de Risco do Emissor.");
        document.frmPreCadastro.c_custo_risco_emissor.focus();
        return false;
    }
    if (isNumeric(document.frmPreCadastro.c_custo_processamento_emissor.value))
    {
        alert("Favor informar Custo de Processamento do Emissor.");
        document.frmPreCadastro.c_custo_processamento_emissor.focus();
        return false;
    }
    if (isNumeric(document.frmPreCadastro.c_custo_mkt_emissor.value))
    {
        alert("Favor informar Custo de Marketing do Emissor.");
        document.frmPreCadastro.c_custo_mkt_emissor.focus();
        return false;
    }
    if (isNumeric(document.frmPreCadastro.c_custo_inadimplencia_emissor.value))
    {
        alert("Favor informar Custo de Inadimplência do Emissor.");
        document.frmPreCadastro.c_custo_inadimplencia_emissor.focus();
        return false;
    }
    if (isNumeric(document.frmPreCadastro.c_custos_outros_emissor.value))
    {
        alert("Favor informar Outros Custos do Emissor.");
        document.frmPreCadastro.c_custos_outros_emissor.focus();
        return false;
    }
    if (isNumeric(document.frmPreCadastro.c_custo_impostos_emissor.value))
    {
        alert("Favor informar Custo de Impostos do Emissor.");
        document.frmPreCadastro.c_custo_impostos_emissor.focus();
        return false;
    }
    if (isNumeric(document.frmPreCadastro.c_receita_credenciador.value))
    {
        alert("Favor informar Receita do Credenciador.");
        document.frmPreCadastro.c_receita_credenciador.focus();
        return false;
    }
    if (isNumeric(document.frmPreCadastro.c_receita_outras_credenciador.value))
    {
        alert("Favor informar Outras Receita do Credenciador.");
        document.frmPreCadastro.c_receita_outras_credenciador.focus();
        return false;
    }
    if (isNumeric(document.frmPreCadastro.c_custo_processamento_front_end_back_end.value))
    {
        alert("Favor informar o custo de Front-End e Back-End.");
        document.frmPreCadastro.c_custo_processamento_front_end_back_end.focus();
        return false;
    }
    
    return true;
}

function isNumeric(pVal)
{
	var reTipo = '(^[0-9.,]+$)'; // é a expressão regular apropriada
	if (!reTipo.test(pVal)&&(pVal!=''))
	{
		alert(pVal + " NÃO contém apenas dígitos.");
		return false;
	}
	else return true;
}

function isTipo(pVal)
{
	var reTipo = /^\d+$/; // é a expressão regular apropriada
	if (!reTipo.test(pVal)&&(pVal!=''))
	{
		alert(pVal + " NÃO contém apenas dígitos.");
		return false;
	}
	else return true;
}

function lTrim(sStr){
	while (sStr.charAt(0) == " ") 
		sStr = sStr.substr(1, sStr.length - 1);
	return sStr;
}

function rTrim(sStr){
	while (sStr.charAt(sStr.length - 1) == " ") 
		sStr = sStr.substr(0, sStr.length - 1);
	return sStr;
}

function alltrim(sStr){
	return rTrim(lTrim(sStr));
}

function isTipo2(str) {
    str = alltrim(str);
    return /^[-+]?[0-9]+(\.[0-9]+)?$/.test(str);
}
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="1">BackOffice - Money</a></li>
        <li><a href="index.php">Listar</a></li>
        <li class="active"><?php if($acao == 'atualizar') echo "Edi&ccedil;&atilde;o"; else echo"Cadastro"; ?></li>
    </ol>
</div>
<div class="txt-preto fontsize-pp col-md-12">
<font face="Verdana,Arial" size="2">
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" name="frmPreCadastro" id="frmPreCadastro" onsubmit="return validaUsuario();">
    <input type="hidden" name="acao" id="acao" value="<?php if(isset($acao)) echo $acao; ?>" />
        <fieldset>
            <legend>Dados de Complice</legend>
            <table width="90%" border="0" align="center" cellpadding="1" cellspacing="0" style="font-size:10px">
                <tr>
                    <td>* Ano: </td>
                    <td>
                        <?php 
                        if(isset($acao) && $acao == 'atualizar') {
                            echo $ano; 
                        ?>
                        <input type="hidden" name="ano" id="ano" value="<?php if(isset($ano)) echo $ano; ?>" />
                        <?php
                        } //end if($acao == 'atualizar')
                        else {
                        ?>
                        <select name="ano" id="ano" class="combo_normal">
                                <option value="" >Selecione</option>
                        <?php  for($i =  date('Y'); $i >= $ANO_INICIO_OPERACAO ; $i--) { ?>
                                <option value="<?php  echo $i ?>" <?php  if(isset($ano) && $ano == $i) echo "selected" ?>><?php  echo $i ?></option>
                        <?php  } ?>
                        </select>
                        <?php
                        }//end else do if($acao == 'atualizar')
                        ?>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>* Mês: </td>
                    <td>
                        <?php 
                        if($acao == 'atualizar') {
                            echo mesNome($mes); 
                        ?>
                        <input type="hidden" name="mes" id="mes" value="<?php if(isset($mes)) echo $mes; ?>" />
                        <?php
                        } //end if($acao == 'atualizar')
                        else {
                        ?>
                        <select name="mes" id="mes" class="combo_normal">
                                    <option value="" >Selecione</option>
                            <?php
                                for ($codigoMes=1; $codigoMes<=12; $codigoMes++){
                                       if (strlen($codigoMes) == 1){
                                               $codigoMes = '0'.$codigoMes;
                                       }

                                       echo '<option value="'.$codigoMes.'"';
                                       if ($mes == $codigoMes){
                                               echo ' SELECTED';
                                       }
                                       echo '>'.mesNome($codigoMes).'</option>';
                                }
                                ?>
                        </select>
                        <?php
                        }//end else do if($acao == 'atualizar')
                        ?>
                    </td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>* <a href="javascript:info_c_receita_credenciador();">Receita do Credenciador</a>: </td>
                    <td>
                        <input name="c_receita_credenciador" type="text" id="c_receita_credenciador" size="15" maxlength="15" value="<?php if(isset($c_receita_credenciador)) echo number_format($c_receita_credenciador, 2, ',', '.'); ?>" />
                    </td>
                    <td width="40%">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="3">
                        <div id='info_c_receita_credenciador' name='info_c_receita_credenciador' onclick='javascript:$("#info_c_receita_credenciador").hide("slow");'>
                            <fieldset>
                                <legend>Detalhes</legend>
                                Receita total com taxa de desconto cobrada dos estabelecimentos credenciados
                            </fieldset>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>* <a href="javascript:info_c_receita_outras_credenciador();">Outras Receita do Credenciador</a>: </td>
                    <td>
                        <input name="c_receita_outras_credenciador" type="text" id="c_receita_outras_credenciador" size="15" maxlength="15" value="<?php if(isset($c_receita_outras_credenciador)) echo number_format($c_receita_outras_credenciador, 2, ',', '.'); ?>" />
                    </td>
                    <td width="40%">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="3">
                        <div id='info_c_receita_outras_credenciador' name='info_c_receita_outras_credenciador' onclick='javascript:$("#info_c_receita_outras_credenciador").hide("slow");'>
                            <fieldset>
                                <legend>Detalhes</legend>
                                - Receitas provenientes dos serviços de gerenciamento de contas prestados aos estabelecimentos credenciados, tais como segunda via de extrato, etc.<br>
                                - Demais receitas provenientes do relacionamento entre credenciador e os estabelecimentos credenciados
                            </fieldset>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>* <a href="javascript:info_c_custo_mkt_credenciado();">Custo de Marketing do Credenciador</a>: </td>
                    <td>
                        <input name="c_custo_mkt_credenciado" type="text" id="c_custo_mkt_credenciado" size="15" maxlength="15" value="<?php if(isset($c_custo_mkt_credenciado)) echo number_format($c_custo_mkt_credenciado, 2, ',', '.'); ?>" />
                    </td>
                    <td width="40%">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="3">
                        <div id='info_c_custo_mkt_credenciado' name='info_c_custo_mkt_credenciado' onclick='javascript:$("#info_c_custo_mkt_credenciado").hide("slow");'>
                            <fieldset>
                                <legend>Detalhes</legend>
                                Custo incorrido nas atividades de marketing e propaganda, tais como:<br>
                                - Custo sobre as vendas (salários dos vendedores, comissão paga aos emissores pela captura de novos clientes, etc.)<br>
                                - Custo de gerenciamento de vendas<br>
                                - Custo de promoções para estimular uso dos cartões de pagamento na rede
                            </fieldset>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>* <a href="javascript:info_c_custo_risco_credenciador();">Custo de Risco do Credenciador</a>: </td>
                    <td>
                        <input name="c_custo_risco_credenciador" type="text" id="c_custo_risco_credenciador" size="15" maxlength="15" value="<?php if(isset($c_custo_risco_credenciador)) echo number_format($c_custo_risco_credenciador, 2, ',', '.'); ?>" />
                    </td>
                    <td width="40%">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="3">
                        <div id='info_c_custo_risco_credenciador' name='info_c_custo_risco_credenciador' onclick='javascript:$("#info_c_custo_risco_credenciador").hide("slow");'>
                            <fieldset>
                                <legend>Detalhes</legend>
                                - Custo de gerenciamento dos riscos incorridos no processamento de afiliação de estabelecimentos e nos processos de tomada de decisão<br>
                                - Custo com chargebacks<br>
                                - Assunção de falha de inadimplência de banco emissor, se for o caso
                            </fieldset>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>* <a href="javascript:info_c_custo_outros_credenciador();">Outros Custos do Credenciador</a>: </td>
                    <td>
                        <input name="c_custo_outros_credenciador" type="text" id="c_custo_outros_credenciador" size="15" maxlength="15" value="<?php if(isset($c_custo_outros_credenciador)) echo number_format($c_custo_outros_credenciador, 2, ',', '.'); ?>" />
                    </td>
                    <td width="40%">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="3">
                        <div id='info_c_custo_outros_credenciador' name='info_c_custo_outros_credenciador' onclick='javascript:$("#info_c_custo_outros_credenciador").hide("slow");'>
                            <fieldset>
                                <legend>Detalhes</legend>
                                - Custo incorrido nas atividades e processos referentes ao credenciamentos de novos estabelecimentos, tais como custo de entrada de dados, treinamento de pessoal, instalação de equipamentos, etc.<br>
                                - Despesas administrativas<br>
                                - Custo com pagamento de impostos diretos (ISS, PIS, Cofins, etc)<br>
                                - Custo de serviços:<br>
                                &nbsp;&nbsp;- Custo de serviços a dministrativos (pessoal, material, transporte , etc)<br>
                                &nbsp;&nbsp;- Custo pelos serviços de conciliação<br>
                                &nbsp;&nbsp;- Custo do serviço de help desk de equipamentos, disponibilizado para os estabelecimentos<br>
                                &nbsp;&nbsp;- Custo dos serviços prestados aos estabelecimentos<br>
                                - Outros custos não relacionados anteriormente
                            </fieldset>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>* <a href="javascript:info_c_custo_processamento_front_end_back_end();">Custo de Processamento Front-End e Back-End</a>: </td>
                    <td>
                        <input name="c_custo_processamento_front_end_back_end" type="text" id="c_custo_processamento_front_end_back_end" size="15" maxlength="15" value="<?php if(isset($c_custo_processamento_front_end_back_end)) echo number_format($c_custo_processamento_front_end_back_end, 2, ',', '.'); ?>" />
                    </td>
                    <td width="40%">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="3">
                        <div id='info_c_custo_processamento_front_end_back_end' name='info_c_custo_processamento_front_end_back_end' onclick='javascript:$("#info_c_custo_processamento_front_end_back_end").hide("slow");'>
                            <fieldset>
                                <legend>Detalhes</legend>
                                - De preciação dos equipamentos instalados nos estabelecimentos, incluindo perdas e baixas<br>
                                - Custo dos equipamentos instalados nos estabeleciemtos credenciados<br>
                                - Custo de manutenção dos equipamentos instalados nos estabelecimentos credenciados<br>
                                - Custo referente aos processos de compensação e de liquidação das transações com cartões de pagamentos<br>
                                - Custo como com Sistema de Gerenciamento de info rmaçõe s?MIS (Management Information System)
                            </fieldset>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>* <a href="javascript:info_c_receita_mkt_emissor();">Receita de Marketing do Emissor</a>: </td>
                    <td>
                        <input name="c_receita_mkt_emissor" type="text" id="c_receita_mkt_emissor" size="15" maxlength="15" value="<?php if(isset($c_receita_mkt_emissor)) echo number_format($c_receita_mkt_emissor, 2, ',', '.'); ?>" />
                    </td>
                    <td width="40%">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="3">
                        <div id='info_c_receita_mkt_emissor' name='info_c_receita_mkt_emissor' onclick='javascript:$("#info_c_receita_mkt_emissor").hide("slow");'>
                            <fieldset>
                                <legend>Detalhes</legend>
                                Recursos obtidos junto aos credenciadores ou bandeiras, provenientes de repasses para aplicação em marketing e propaganda
                            </fieldset>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>* <a href="javascript:info_c_receita_outras_emissor();">Outras Receitas do Emissor</a>: </td>
                    <td>
                        <input name="c_receita_outras_emissor" type="text" id="c_receita_outras_emissor" size="15" maxlength="15" value="<?php if(isset($c_receita_outras_emissor)) echo number_format($c_receita_outras_emissor, 2, ',', '.'); ?>" />
                    </td>
                    <td width="40%">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="3">
                        <div id='info_c_receita_outras_emissor' name='info_c_receita_outras_emissor' onclick='javascript:$("#info_c_receita_outras_emissor").hide("slow");'>
                            <fieldset>
                                <legend>Detalhes</legend>
                                Outras receitas oriundas das atividades de emissor de cartões de pagamentos, tais como:<br>
                                - Receita de float<br>
                                - Receita de seguros<br>
                                - Receita advinda da adesão ao programa de pontuação e da conversão dos pontos em bens e serviços<br>
                                - Receita de tarifa por inatividade<br>
                                - Receita com reversão de chargeback<br>
                                - Outras receitas não relacionadas anteriormente( excluindo-se receitas de crédito)
                            </fieldset>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>* <a href="javascript:info_c_custo_risco_emissor();">Custo de Risco do Emissor</a>: </td>
                    <td>
                        <input name="c_custo_risco_emissor" type="text" id="c_custo_risco_emissor" size="15" maxlength="15" value="<?php if(isset($c_custo_risco_emissor)) echo number_format($c_custo_risco_emissor, 2, ',', '.'); ?>" />
                    </td>
                    <td width="40%">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="3">
                        <div id='info_c_custo_risco_emissor' name='info_c_custo_risco_emissor' onclick='javascript:$("#info_c_custo_risco_emissor").hide("slow");'>
                            <fieldset>
                                <legend>Detalhes</legend>
                                Custo incorrido no gerenciamento de risco referente a fraudes, perdas e chargeback , bem como as perdas propriamente ditas
                            </fieldset>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>* <a href="javascript:info_c_custo_processamento_emissor();">Custo de Processamento do Emissor</a>: </td>
                    <td>
                        <input name="c_custo_processamento_emissor" type="text" id="c_custo_processamento_emissor" size="15" maxlength="15" value="<?php if(isset($c_custo_processamento_emissor)) echo number_format($c_custo_processamento_emissor, 2, ',', '.'); ?>" />
                    </td>
                    <td width="40%">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="3">
                        <div id='info_c_custo_processamento_emissor' name='info_c_custo_processamento_emissor' onclick='javascript:$("#info_c_custo_processamento_emissor").hide("slow");'>
                            <fieldset>
                                <legend>Detalhes</legend>
                                Despesas incorridas no processamento de transações com cartões de pagamentos, tais como:<br>
                                - Despesas incorridas no processo de autorização<br>
                                - Despesas incorridas nos processos de conferência de saldos e limites<br>
                                - Despesas com depreciação de equipamentos<br>
                                - Despesas com terceirizados (pessoal e empresas contratadas)<br>
                            </fieldset>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>* <a href="javascript:info_c_custo_mkt_emissor();">Custo de Marketing do Emissor</a>: </td>
                    <td>
                        <input name="c_custo_mkt_emissor" type="text" id="c_custo_mkt_emissor" size="15" maxlength="15" value="<?php if(isset($c_custo_mkt_emissor)) echo number_format($c_custo_mkt_emissor, 2, ',', '.'); ?>" />
                    </td>
                    <td width="40%">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="3">
                        <div id='info_c_custo_mkt_emissor' name='info_c_custo_mkt_emissor' onclick='javascript:$("#info_c_custo_mkt_emissor").hide("slow");'>
                            <fieldset>
                                <legend>Detalhes</legend>
                                Custo com marketing e propaganda relacionados à atividade de emissor de cartões de pagamentos
                            </fieldset>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>* <a href="javascript:info_c_custo_inadimplencia_emissor();">Custo de Inadimplência do Emissor</a>: </td>
                    <td>
                        <input name="c_custo_inadimplencia_emissor" type="text" id="c_custo_inadimplencia_emissor" size="15" maxlength="15" value="<?php if(isset($c_custo_inadimplencia_emissor)) echo number_format($c_custo_inadimplencia_emissor, 2, ',', '.'); ?>" />
                    </td>
                    <td width="40%">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="3">
                        <div id='info_c_custo_inadimplencia_emissor' name='info_c_custo_inadimplencia_emissor' onclick='javascript:$("#info_c_custo_inadimplencia_emissor").hide("slow");'>
                            <fieldset>
                                <legend>Detalhes</legend>
                                Custo com recuperação de crédito por inadimplência, bem como com o provisionamento para fazer face aos créditos de liquidação duvidosa, conforme Resolução 2.682
                            </fieldset>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>* <a href="javascript:info_c_custos_outros_emissor();">Outros Custos do Emissor</a>: </td>
                    <td>
                        <input name="c_custos_outros_emissor" type="text" id="c_custos_outros_emissor" size="15" maxlength="15" value="<?php if(isset($c_custos_outros_emissor)) echo number_format($c_custos_outros_emissor, 2, ',', '.'); ?>" />
                    </td>
                    <td width="40%">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="3">
                        <div id='info_c_custos_outros_emissor' name='info_c_custos_outros_emissor' onclick='javascript:$("#info_c_custos_outros_emissor").hide("slow");'>
                            <fieldset>
                                <legend>Detalhes</legend>
                                Outros custos, em reais, incorridos nas atividades de emissor de cartões de pagamentos, tais como:<br>
                                - Custo com provisões civis e trabalhistas<br> 
                                - Custo advindo das atividades de estabelecimento de relacionamento comercial com os portadores do cartões de pagamentos, tais como centrais de help desk , sistema de gerenciamento de informações ? MIS, portal na Internet, etc.<br>
                                - Custo atribuído à compra ou fabricação dos cartões de pagamento, bem como ao processo de inserção dos dados no cartão<br>
                                - Custo referente aos serviços de postagem ou de entrega dos cartões de pagamento aos portadores<br>
                                - Custo referente ao processo de cobrança das faturas encaminhadas aos portadores (postagem, tarifas interbancárias, etc.)<br>
                                - Custo com despesas administrativas, incluindo gasto de pessoal<br>
                                - Outros custos não relacionados anteriormente<br>
                            </fieldset>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>* <a href="javascript:info_c_custo_impostos_emissor();">Custo de Impostos do Emissor</a>: </td>
                    <td>
                        <input name="c_custo_impostos_emissor" type="text" id="c_custo_impostos_emissor" size="15" maxlength="15" value="<?php if(isset($c_custo_impostos_emissor)) echo number_format($c_custo_impostos_emissor, 2, ',', '.'); ?>" />
                    </td>
                    <td width="40%">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="3">
                        <div id='info_c_custo_impostos_emissor' name='info_c_custo_impostos_emissor' onclick='javascript:$("#info_c_custo_impostos_emissor").hide("slow");'>
                            <fieldset>
                                <legend>Detalhes</legend>
                                Despesas efetuadas com o pagamento de impostos diretos (ISS, PIS, Cofins)
                            </fieldset>
                        </div>
                    </td>
                </tr>
            </table>
        </fieldset>
        <br>
       	
	<table width="90%" border="0" align="center" cellpadding="0" cellspacing="0" style="font-size:10px">
	<tr>
		<td colspan="3">&nbsp;* Campos Obrigat&oacute;rios</td>
	</tr>
	<tr>
		<td colspan="3" align="center"><input type="submit" name="Submit" value="<?php if(isset($acao) && $acao == 'atualizar') echo "Atualizar"; else echo"Cadastrar"; ?>"/></td>
	</tr>
	</table>
</form>
</font>
</div>
<script type="text/javascript">
$("#info_c_custo_mkt_credenciado").hide("slow");
function info_c_custo_mkt_credenciado() { 
    $("#info_c_custo_mkt_credenciado").show("slow");
}

$("#info_c_custo_risco_credenciador").hide("slow");
function info_c_custo_risco_credenciador() { 
    $("#info_c_custo_risco_credenciador").show("slow");
}

$("#info_c_custo_outros_credenciador").hide("slow");
function info_c_custo_outros_credenciador() { 
    $("#info_c_custo_outros_credenciador").show("slow");
}

$("#info_c_receita_mkt_emissor").hide("slow");
function info_c_receita_mkt_emissor() { 
    $("#info_c_receita_mkt_emissor").show("slow");
}

$("#info_c_receita_outras_emissor").hide("slow");
function info_c_receita_outras_emissor() { 
    $("#info_c_receita_outras_emissor").show("slow");
}

$("#info_c_custo_risco_emissor").hide("slow");
function info_c_custo_risco_emissor() { 
    $("#info_c_custo_risco_emissor").show("slow");
}

$("#info_c_custo_processamento_emissor").hide("slow");
function info_c_custo_processamento_emissor() { 
    $("#info_c_custo_processamento_emissor").show("slow");
}

$("#info_c_custo_mkt_emissor").hide("slow");
function info_c_custo_mkt_emissor() { 
    $("#info_c_custo_mkt_emissor").show("slow");
}

$("#info_c_custo_inadimplencia_emissor").hide("slow");
function info_c_custo_inadimplencia_emissor() { 
    $("#info_c_custo_inadimplencia_emissor").show("slow");
}

$("#info_c_custos_outros_emissor").hide("slow");
function info_c_custos_outros_emissor() { 
    $("#info_c_custos_outros_emissor").show("slow");
}

$("#info_c_custo_impostos_emissor").hide("slow");
function info_c_custo_impostos_emissor() { 
    $("#info_c_custo_impostos_emissor").show("slow");
}

$("#info_c_receita_credenciador").hide("slow");
function info_c_receita_credenciador() { 
    $("#info_c_receita_credenciador").show("slow");
}

$("#info_c_receita_outras_credenciador").hide("slow");
function info_c_receita_outras_credenciador() { 
    $("#info_c_receita_outras_credenciador").show("slow");
}

$("#info_c_custo_processamento_front_end_back_end").hide("slow");
function info_c_custo_processamento_front_end_back_end() { 
    $("#info_c_custo_processamento_front_end_back_end").show("slow");
}

</script>