<?php

function endereco_page_transf($preencher_endereco, $is_integracao = FALSE){
    
    if(isset($GLOBALS['_SESSION']['usuarioGames_ser']) && !is_null($GLOBALS['_SESSION']['usuarioGames_ser'])){
            if($preencher_endereco){
                $is_int = $is_integracao;
                if($is_int){
                    require_once RAIZ_DO_PROJETO . 'public_html/prepag2/commerce/includes/cabecalho_int.php';
                    require_once RAIZ_DO_PROJETO . 'public_html/prepag2/commerce/includes/form_endereco.php';
                }else{
                    require_once RAIZ_DO_PROJETO . 'public_html/prepag2/commerce/includes/form_endereco_transf_bradesco.php';
                }
                
                if(!$is_int){
                    require_once RAIZ_DO_PROJETO . 'public_html/game/includes/footer.php';
                }
                die();
            }            
    }
    else {
        echo "<div class='txt-vermelho text-center top50'><p>Sua sessão expirou. Volte no jogo e tente novamente. Obrigado!</p></div>";
        include "rodape.php"; 
        die();
    }
}
