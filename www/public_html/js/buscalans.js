function procuraLan() {
    if (validaBuscaCidade()) {
        $("#bairro").closest("form").submit();
    }
}

function validaBuscaCidade() {
    var erro = false;
    var errorText = "";

    if ($("#estado").val() == "") {
        errorText = "<p>Estado deve ser selecionada.</p>";
        erro = true;
    }

    if ($("#cidade").val() == "") {
        errorText = "<p>Cidade deve ser selecionada.</p>";
        erro = true;
    }

    if ($("#bairro").val() == "") {
        errorText = "<p>Bairro deve ser selecionado.</p>";
        erro = true;
    }

    if ($("#verificationCode").val() == "") {
        errorText = "<p>Captcha deve ser preenchido.</p>";
        erro = true;
    }

    if (erro === false) {
        return true;
    } else {
        waitingDialog.hide();
        $("#error-text").html(errorText);
        $("#modal-load").modal();
        return false;
    }
}

function resizeIframe(obj) {
    obj.style.height = 0;
    obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
}

function ValidaForm() {
    waitingDialog.show('Por favor, aguarde...', { dialogSize: 'sm' });
    setTimeout(function () {
        if (validaBuscaCidade()) {
            MostraLANs();
        }
    }, 500);
    if (typeof grecaptcha !== 'undefined') {
        grecaptcha.reset();
    }
}

function MostraCidade() {
    waitingDialog.show('Buscando cidades...', { dialogSize: 'sm' });
    console.log("MostraCidade");
    setTimeout(function () {
        if (document.form_lanHouses_filtros.estado.value != "") {
            estado = document.form_lanHouses_filtros.estado.value;
            $.ajax({
                type: "POST",
                url: "/creditos/ajax/busca-pdv-cidade.php",
                data: "estado=" + estado,
                beforeSend: function () {
                    $("#SelCidade").html("...");
                },
                success: function (html) {
                    waitingDialog.hide();
                    $("#SelCidade").html(html);
                },
                error: function () {
                    waitingDialog.hide();
                    $("#error-text").html("Erro ao buscar cidades.");
                    $("#modal-load").modal();
                }
            });
        }
        waitingDialog.hide();
    }, 500);
}

function MostraBairro() {
    waitingDialog.show('Buscando bairros...', { dialogSize: 'sm' });
    console.log("MostraBairro");
    setTimeout(function () {
        if (document.form_lanHouses_filtros.cidade.value != "") {
            estado = document.form_lanHouses_filtros.estado.value;
            cidade = document.form_lanHouses_filtros.cidade.value;
            $.ajax({
                type: "POST",
                url: "/creditos/ajax/busca-pdv-bairro.php",
                data: "cidade=" + cidade + "&estado=" + estado,
                beforeSend: function () {
                    $("#SelBairro").html("...");
                },
                success: function (html) {
                    waitingDialog.hide();
                    $("#SelBairro").html(html);
                },
                error: function () {
                    waitingDialog.hide();
                    $("#error-text").html("Erro ao buscar bairros.");
                    $("#modal-load").modal();
                }
            });
        }
        waitingDialog.hide();
    }, 500);
}

function monta_captcha() {
    document.form_lanHouses_filtros.verificationCode.value = "";

    $.ajax({
        type: "POST",
        url: "/creditos/ajax/ajax_captcha.php",
        success: function (html) {
            $("#span_captcha").html(html);
        },
        error: function () {
            $("#error-text").html("Erro ao carregar o captcha.");
            $("#modal-load").modal();
        }
    });
}

function MostraLANs() {
    if (document.form_lanHouses_filtros.cidade.value != "") {
        estado = document.form_lanHouses_filtros.estado.value;
        cidade = document.form_lanHouses_filtros.cidade.value;
        bairro = document.form_lanHouses_filtros.bairro.value;
        verificationCode = document.form_lanHouses_filtros.verificationCode.value;
        $.ajax({
            type: "POST",
            url: "/creditos/ajax/busca-pdv.php",
            data: "cidade=" + cidade + "&bairro=" + bairro + "&estado=" + estado + "&verificationCode=" + verificationCode,
            beforeSend: function () {
                waitingDialog.show('Buscando pontos de venda...', { dialogSize: 'sm' });
            },
            success: function (txt) {
                waitingDialog.hide();
                $("#resultado").html(txt);
                monta_captcha();
            },
            error: function () {
                waitingDialog.hide();
                $("#error-text").html("Erro ao buscar pontos de venda.");
                $("#modal-load").modal();
            }
        });
    }
    waitingDialog.hide();
}

function Reload() {
    document.form_lanHouses_filtros.verificationCode.value = "";
    document.form_lanHouses_filtros.action = "<?php echo $_SERVER['PHP_SELF']; ?>";
    document.form_lanHouses_filtros.submit();
    return false;
}
function montaBoxPdv() {
    var box = true;

    $.ajax({
        type: "POST",
        url: "/creditos/ajax/busca-pdv.php",
        data: { box: "true", estado: $("#estado").val(), cidade: $("#cidade").val(), bairro: $("#bairro").val() },
        success: function (html) {
            $("#box-busca-pdv").html(html);
        }
    });
}
