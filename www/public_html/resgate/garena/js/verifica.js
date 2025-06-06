$(document).ready(function () {

    let timeout = null;

    $('#codigo').on('input', function () {
        clearTimeout(timeout); // Limpa o último timeout
        let identificador = $(this).val().trim();
        let logo_padrao = $('#img-padrao').text();

        // Só faz a requisição se tiver pelo menos 19 caracteres, por exemplo
        if (identificador.length < 19) {
            $('#logo-jogo').attr('src', logo_padrao);
            return;
        }

        timeout = setTimeout(function () {
            $.ajax({
                url: '/resgate/garena/verifica-logo.php?pin=' + identificador,
                method: 'GET',
                success: function (resposta) {
                    // Supondo que a API retorna algo como: { jogo: "free_fire" }
                    switch (resposta.jogo) {
                        case 'free_fire':
                            $('#logo-jogo').attr('src', '/sys/imagens/Free_Fire.png');
                            break;
                        case 'delta_force':
                            $('#logo-jogo').attr('src', '/sys/imagens/Delta_Force.png');
                            break;
                        default:
                            $('#logo-jogo').attr('src', logo_padrao);
                    }
                },
                error: function (xhr) {
                    console.log("Status HTTP:", xhr.status); // Ex: 404, 500, etc.
                    console.log("Resposta do servidor:", xhr.responseText);
                    $('#logo-jogo').attr('src', logo_padrao);
                }
            });
        }, 500); // Aguarda 500ms depois do último input
    });
});