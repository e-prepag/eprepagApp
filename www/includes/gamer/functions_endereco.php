<?php

function endereco_page_transf(bool $preencher_endereco, bool $is_integracao = false): void
{

    if (!empty($GLOBALS['_SESSION']['usuarioGames_ser'])) {
        if ($preencher_endereco) {
            $is_int = $is_integracao;
            if ($is_int) {
                require_once RAIZ_DO_PROJETO . 'public_html/prepag2/commerce/includes/cabecalho_int.php';
                require_once RAIZ_DO_PROJETO . 'public_html/prepag2/commerce/includes/form_endereco.php';
            } else {
                require_once RAIZ_DO_PROJETO . 'public_html/prepag2/commerce/includes/form_endereco_transf_bradesco.php';
            }

            if (!$is_int) {
                require_once RAIZ_DO_PROJETO . 'public_html/game/includes/footer.php';
            }
            die();
        }
    } else {
        echo "<div class='txt-vermelho text-center top50'><p>Sua sesso expirou. Volte no jogo e tente novamente. Obrigado!</p></div>";
        include RAIZ_DO_PROJETO . 'public_html/includes/rodape.php';
        die();
    }
}
