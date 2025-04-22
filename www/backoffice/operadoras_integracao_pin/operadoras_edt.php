<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."includes/security.php";
require_once $raiz_do_projeto."includes/gamer/constantesPinEpp.php";

if ($acao == 'novo') {
    $acao = 'inserir';
} else {
    $acao = 'atualizar';
}
?>
<style>
    .errorField {
        border: 1px solid red;
    }

    .help {
        padding: 7px 0 7px 0;
    }

    .help a {
        cursor: help;
        text-decoration: dashed;
    }
</style>
<script type="text/javascript">
    function validaUsuario() {

        if (document.frmPreCadastro.opr_codigo.value == "") {
            alert("Favor Selecione o Publisher.");
            document.frmPreCadastro.opr_codigo.focus();
            return false;
        }
        if (document.frmPreCadastro.opr_product_type.value == "") {
            alert("Favor Selecionar o Produto Contratado.");
            document.frmPreCadastro.opr_product_type.focus();
            return false;
        }
        if ((document.frmPreCadastro.opr_product_type.value == "3") || (document.frmPreCadastro.opr_product_type.value == "2")) {
            alert("Favor Selecione outro tipo de integração pois PINs Moeda tem que passar por revisão.");
            document.frmPreCadastro.opr_product_type.focus();
            return false;
        }
        if (document.frmPreCadastro.opr_use_check.value == "") {
            alert("Favor Selecionar se Utiliza Checagem de Utilização.");
            document.frmPreCadastro.opr_use_check.focus();
            return false;
        }
        else if (document.frmPreCadastro.opr_use_check.value == "1") {
            if (document.frmPreCadastro.opr_partner_check.value == "") {
                alert("Favor Informar a URL de Confirmação de Utilização.");
                document.frmPreCadastro.opr_partner_check.focus();
                return false;
            }
            if (document.frmPreCadastro.opr_partner_dominio.value == "") {
                alert("Favor Informar o Domínio de Confirmação de Utilização.");
                document.frmPreCadastro.opr_partner_dominio.focus();
                return false;
            }
        }
        // Validando IPs digitados
        var lista_ip = $("#opr_ip");
        var numero_range = $("input[name='ip_inicial[]']");
        var numero_range_final = $("input[name='ip_final[]']");

        if ( lista_ip.val() === '' && (numero_range[0].value==='' && numero_range_final[0].value==='' ) ) {
            lista_ip.addClass('errorField');
            $('#ip_inicial_1').addClass('errorField');
            $('#ip_final_1').addClass('errorField');
            alert('É necessário preencher pelo menos um dos dois campos de IP (lista ou range)');
            return false;
        } else {
            lista_ip.removeClass('errorField');
            $('#ip_inicial_1').removeClass('errorField');
            $('#ip_final_1').removeClass('errorField');
        }

        // Validando Lista caso esteja preenchida
        if ( lista_ip.val() !== '' ) {
            var ips = lista_ip.val().split(';');
            var len_ip = ips.length;
            for (var i = 0; i < len_ip; i++) {
                if (isIP(ips[i]) == false) {
                    lista_ip.addClass('errorField');
                    lista_ip.focus();
                    alert('Voce digitou algum IP invalido na lista (' + ips[i] + ')');
                    return false;
                } else {
                    lista_ip.removeClass('errorField');
                }
            }
        }

        // Validando os ranges de IPS
        // numero de ip inicial ? o mesmo que o de ip final
        var ranged_errors = 0;
        for (var j = 0; j < numero_range.length; j++) {
            var input_inicio = $('#ip_inicial_' + (j + 1));
            var input_final = $('#ip_final_' + (j + 1));

            if ( input_inicio.val() != '' && input_final.val() == '' ) {
                input_final.focus();
                input_final.addClass('errorField');
                alert('Você esqueceu de preencher o IP final de um dos campos.');
                return false;
            } else {
                input_final.removeClass('errorField');
            }
            if ( input_final.val() != '' && input_inicio.val() == '' ) {
                input_inicio.focus();
                input_inicio.addClass('errorField');
                alert('Você esqueceu de preencher o IP inicio de um dos campos.');
                return false;
            } else {
                input_inicio.removeClass('errorField');
            }

            if (input_inicio.val() != '' && isIP(input_inicio.val()) == false) {
                input_inicio.addClass('errorField');
                ranged_errors++;
            } else {
                input_inicio.removeClass('errorField');
            }

            if (input_final.val() != '' && isIP(input_final.val()) == false) {
                input_final.addClass('errorField');
                ranged_errors++;
            } else {
                input_final.removeClass('errorField');
            }

        }

        if (ranged_errors > 0) {
            alert('Voce digitou algum numero de IP incorreto.');
            return false;
        }

        // email homologação
        var email_homol = $('#opr_partner_email');
        if ( email_homol.val() === '' ) {
            alert("Favor informar o Email de Contato para Homologação do Publisher.");
            email_homol.focus();
            return false;
        }
        var regexEmail = /^[^@\\s]+@([^@\\s]+\\.)+[^@\\s]+$/;
        if ( ! email_homol.val().test(regexEmail) ) {
            alert('E-mail para homologação inválido');
            email_homol.focus();
            return false;
        }

        /*if (document.frmPreCadastro.opr_partner_email.value == "") {

            document.frmPreCadastro.opr_partner_email.focus();
            return false;
        }*/
        return true;
    }

    function isIP(ip) {
        return ip.match(/^(\d|[1-9]\d|1\d\d|2([0-4]\d|5[0-5]))\.(\d|[1-9]\d|1\d\d|2([0-4]\d|5[0-5]))\.(\d|[1-9]\d|1\d\d|2([0-4]\d|5[0-5]))\.(\d|[1-9]\d|1\d\d|2([0-4]\d|5[0-5]))$/);
    }

    function isEmail(pVal) {
            var reTipo = /^.+@.+\..{2,3}$/;//expressão regular que valida email
            if (!reTipo.test(pVal)) {
                alert(pVal + " NÃO é um E-Mail válido.");
                return false;
            }
            else return true;
    }

    function isURL(pVal) {
        var reTipo = /^(([\w]+:)?\/\/)?(([\d\w]|%[a-fA-f\d]{2,2})+(:([\d\w]|%[a-fA-f\d]{2,2})+)?@)?([\d\w][-\d\w]{0,253}[\d\w]\.)+[\w]{2,4}(:[\d]+)?(\/([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)*(\?(&?([-+_~.\d\w]|%[a-fA-f\d]{2,2})=?)*)?(#([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)?$/; // é a expressão regular apropriada para URL
        if (!reTipo.test(pVal)) {
            alert(pVal + " NÃO é uma URL válida.");
            return false;
        }
        else return true;
    }

    function reload(pID) {
        document.frmPreCadastro.acao.value = 'editar';
        document.frmPreCadastro.action = "index.php?opr_codigo=" + pID;
        document.frmPreCadastro.submit();
    }

    function showHelpIP() {
        $("#help_div").dialog({
            title: 'Dúvidas sobre o preenchimento dos campos de IP\'s',
            width: 500,
            buttons: [
                {
                    text: "Fechar",
                    click: function () {
                        $(this).dialog("close");
                    }
                }
            ]
        });
    }
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="1">BackOffice - Money</a></li>
        <li><a href="index.php">Operadoras com Integração de PINs</a></li>
        <li class="active"><?php if ($acao == 'atualizar') echo "Edi&ccedil;&atilde;o"; else echo "Cadastro"; ?></li>
    </ol>
</div>
<div class="col-md-12">
    <form method="post" name="frmPreCadastro" id="frmPreCadastro" onsubmit="return validaUsuario();">
            <input type="hidden" name="acao" value="<?php echo $acao; ?>"/>
            <fieldset>
                <legend>Dados da Integra&ccedil;&atilde;o</legend>
                <table class="table txt-preto fontsize-pp">
                    <tr>
                        <td>* Nome:</td>
                        <td>
                            <select id='opr_codigo' name='opr_codigo' onchange="reload(this.value);">
                                <option value=''<?php echo(($opr_codigo == "") ? " selected" : "") ?>
                                onchange="reload(this.value);">Selecione uma Operadora</option>
                                <?php
                                $sql = "SELECT
										opr_codigo,
										opr_nome
									FROM operadoras
									ORDER BY opr_nome;";
                                $rs_opr_codigo = SQLexecuteQuery($sql);
                                while ($rs_opr_codigo_row = pg_fetch_array($rs_opr_codigo)) {
                                    echo "<option value='" . $rs_opr_codigo_row['opr_codigo'] . "'" . (($opr_codigo == (string)$rs_opr_codigo_row['opr_codigo']) ? " selected" : "") . ">" . $rs_opr_codigo_row['opr_nome'] . "</option>\n";
                                }
                                ?>
                            </select>
                        </td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>* Produto Contratado:</td>
                        <td>
                            <select id='opr_product_type' name='opr_product_type'>
                                <option>Selecione o Produto Contratado</option>
                                <?php
                                foreach ($PRODUCT_TYPE as $key => $val) {
                                    echo "<option value='" . $key . "'" . (($opr_product_type == (string)$key) ? " selected" : "") . ">" . $val . "</option>\n";
                                }
                                ?>
                            </select>
                        </td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>* Usa Confirma&ccedil;&atilde;o de Utiliza&ccedil;&atilde;o:</td>
                        <td>
                            <select id='opr_use_check' name='opr_use_check'>
                                <option value=''>Selecione</option>
                                <?php
                                foreach ($USE_CHECK as $key => $val) {
                                    echo "<option value='" . $key . "'" . (($opr_use_check == (string)$key) ? " selected" : "") . ">" . $val . "</option>\n";
                                }
                                ?>
                            </select>
                        </td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td> URL de Confirma&ccedil;&atilde;o:</td>
                        <td><input name="opr_partner_check" type="text" id="opr_partner_check" size="60" maxlength="256"
                                   value="<?php if(isset($opr_partner_check)) echo $opr_partner_check; ?>" onBlur="isURL(this.value);"/></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td> Dom&iacute;nio:</td>
                        <td><input name="opr_partner_dominio" type="text" id="opr_partner_dominio" size="60"
                                   maxlength="256" value="<?php if(isset($opr_partner_dominio)) echo $opr_partner_dominio; ?>"/></td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="2">
                    <fieldset>
                        <legend>IPs</legend>
                    <table style="font-size:10px">
                    <tr>
                        <td>
                            <nobr>* IPs dos Servidores da Integra&ccedil;&atilde;o:</nobr>
                        </td>
                        <td>
                            <div class="help">
                                <a href="javascript:;" style="text-decoration: none" onclick="showHelpIP()">
                                    <span style="text-decoration: underline">Tem d&uacute;vidas de como preencher os IP's?</span>
                                    <img src="/images/icon-help-16.png" title="Ajuda"/>
                                </a>
                            </div>
                        </td>
                        <td>&nbsp;</td>
                    </tr>
                    <?php
                    if(!isset($opr_ip))
                        $opr_ip = null;
                    
                    //echo "opr_ip:".$opr_ip;
                    if (isset($opr_ip) && strpos($opr_ip, ';')) {
                        $ips = explode(';', $opr_ip);
                        $singles_ip = '';
                        $ranged = array();
                        foreach ($ips as $ip) {
                            if (strpos($ip, '-') !== false) {
                                list($inicial, $final) = explode('-', $ip);
                                $ranged[] = array(
                                    'inicial' => $inicial,
                                    'final' => $final,
                                );
                            } else {
                                $singles_ip .= $ip . ';';
                            }
                        }
                    }
                    else $singles_ip = $opr_ip;
                    //echo "singles_ip:".$singles_ip;
                    // Limpando ';' desnecessarios
                    $singles_ip = trim(implode(';', array_filter(explode(';', $singles_ip))));
                    $primeiro_range = '';
                    $segundo_range = '';

                    if (isset($ranged) && count($ranged) > 0) {
                        $primeiro_range = $ranged[0]['inicial'];
                        $segundo_range = $ranged[0]['final'];
                    }
                    ?>
                    <tr id="tr_lista_ip">
                        <td>Lista de IP's:</td>
                        <td><input name="opr_ip" type="text" id="opr_ip" size="60" maxlength="256"
                                   value="<?php echo $singles_ip; ?>" autocomplete="off" /><br>
                            <span style="font-size:9px; color:red;">Os IPs devem estar separados por ponto-e-vírgula(;)</span>
                        </td>
                    </tr>
                    <tbody id="lista_range_ip">
                    <tr>
                        <td>Range de IP's:</td>
                        <td>Inicial <input type="text" id="ip_inicial_1" maxlength="15" value="<?php echo $primeiro_range; ?>"
                                           name="ip_inicial[]"/> - Final <input type="text" maxlength="15"
                                                                                value="<?php echo $segundo_range; ?>"
                                                                                id="ip_final_1" name="ip_final[]"/>
                            <a style="text-decoration: none" href="javascript:;" id="mais" name="mais">
                                <img src="/images/add.gif" border="0" alt="Adicionar Valor"
                                     title="Adicionar Valor"/>
                            </a>&nbsp;
                            <a href="javascript:;" id="menos" name="menos">
                                <img src="/images/excluir.gif" border="0" alt="Excluir Valor"
                                     title="Excluir Valor"/>
                            </a>
                        </td>
                    </tr>
                    <?php
                    $i_ranged = 1;
                    if (isset($ranged) && count($ranged) > 1) {
                        for ($i = 1; $i < count($ranged); $i++) {
                            $i_ranged++;
                            $n = 1;
                            ?>
                            <tr id="linha_range_<?php echo $i_ranged;?>">
                            <td>Range de IP's <?php echo $i_ranged; ?>:</td>
                            <td>
                                Inicial <input type="text" maxlength="15" value="<?php echo $ranged[$i]['inicial']; ?>"
                                               id="ip_inicial_<?php echo $i_ranged; ?>" name="ip_inicial[]"/>
                                - Final <input type="text" maxlength="15" value="<?php echo $ranged[$i]['final']; ?>"
                                               id="ip_final_<?php echo $i_ranged; ?>" name="ip_final[]"/>
                            </td>
                            </tr><?php
                        }
                    }
                    ?>
                    </tbody>
                    </table>
                    </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <td>* Email para Homologa&ccedil;&atilde;o:</td>
                        <td>
                            <input name="opr_partner_email" type="text" id="opr_partner_email" size="40" maxlength="100"
                                   value="<?php if(isset($opr_partner_email)) echo $opr_partner_email; ?>"/>
                        </td>
                        <td>&nbsp;</td>
                    </tr>
                </table>
            </fieldset>
            <table width="90%" border="0" align="center" cellpadding="0" cellspacing="0" style="font-size:10px">
                <tr>
                    <td colspan="3">&nbsp;* Campos Obrigat&oacute;rios</td>
                </tr>
                <tr>
                    <td colspan="3" align="center"><input type="submit" class="btn btn-sm btn-info" name="Submit"
                                                          value="<?php if ($acao == 'atualizar') echo 'Atualizar'; else echo 'Cadastrar'; ?>"/>
                    </td>
                </tr>
            </table>
        </form>
</div>
<div id="help_div" style="display: none">
    Preenchimento de IP's:<br/>
    <ul>
        <li>
            Sobre IP's<br />
            O primeiro número possível para um IP é xxx.xxx.xxx.1<br />
            O último número possível para um IP é xxx.xxx.xxx.254<br />
            Ex: <br />
            Ip's com o final xxx.xxx.xxx.0 ou xxx.xxx.xxx.256 são inválidos<br />
            Então um range: 192.168.1.1 - 192.168.1.256 é inválido pois o 256 não é um endereço válido.<br />&nbsp;
        </li>
        <li>
            Lista:<br/>
            Preencha todos os IP's que você deseja bloquear, um a um por ponto-e-virgula (;)<br/>
            <blockquote>Ex: 192.168.5.89;192.168.9.45</blockquote>
            <br/>Neste exemplo apenas os dois IP's serão bloqueados<br/>&nbsp;
        </li>
        <li>Range:<br/>
            Defina uma sequencia de IP's a serem bloqueados.<br/>
            <blockquote>Ex: 192.168.0.15-192.168.0.20</blockquote>
            <br/>Neste exemplo todos os IP's que estão entre e inclusive serão considerados
            <br/>(192.168.0.15,192.168.0.16,192.168.0.17,192.168.0.18,192.168.0.19,192.168.0.20)<br/>&nbsp;
        </li>
    </ul>
    <p>Você pode definir quantos IP's únicos ou faixas / sequencias de IP's achar necessário.</p>
</div>
<script>
    var qtd_itens = <?php echo $i_ranged;?>;
    $("#mais").click(function () {
        qtd_itens++;
        var nova_lista = '<tr id="linha_range_' + qtd_itens + '">' +
            '<td>Range de IP\'s ' + (qtd_itens) + ':</td>' +
            '<td>Inicial <input type="text" maxlength="15" id="inicial_' + qtd_itens + '" name="ip_inicial[]"/> - Final <input type="text" maxlength="15" id="final_' + qtd_itens + '" name="ip_final[]"/>' +
            '</td></tr>';

        $("#lista_range_ip").append(nova_lista);
    });

    $("#menos").click(function () {
        $('#linha_range_' + qtd_itens).remove();
        if (qtd_itens > 1) {
            qtd_itens--;
        } else {
            $('#ip_inicial_1').val('');
            $('#ip_final_1').val('');
        }
    })
</script>
