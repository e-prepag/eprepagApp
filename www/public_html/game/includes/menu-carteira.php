<?php
    $arrCarteira = array("/game/conta/depositos-processamento.php","/game/conta/extrato.php","/game/carteira/detalhe-pedido.php","/game/conta/detalhe-deposito.php","/game/conta/add-saldo.php");
    $arrPedido = array("/game/conta/pedidos.php","/game/conta/detalhe-pedido.php");
?>

<div class="txt-azul-claro espacamento">
    <ul class="nav nav-pills nav-stacked">
        <li role="presentation"><a href="<?php echo ($_SERVER['SCRIPT_NAME'] == '/game/conta/pedidos.php') ? "#" : "/game/conta/pedidos.php";?>" <?php if(in_array($_SERVER['SCRIPT_NAME'],$arrPedido)) echo 'class="bg-verde-claro txt-branco"';?> title="Meus pedidos" alt="Meus pedidos"><strong>Meus pedidos</strong></a></li>
        <li role="presentation">
            <a href="<?php echo ($_SERVER['SCRIPT_NAME'] == "/game/conta/extrato.php") ? '#' : "/game/conta/extrato.php";?>"  <?php if(in_array($_SERVER['SCRIPT_NAME'],$arrCarteira)) echo 'class="bg-verde-claro txt-branco"';?> alt="Clique para ver seu Cartão E-Prepag" title="Clique para ver seu Cartão E-Prepag"><strong>Cartão E-Prepag</strong></a>
        </li>
        <li role="presentation"><a href="<?php echo ($_SERVER['SCRIPT_NAME'] == "/game/conta/meus-dados.php") ? '#" class="bg-verde-claro txt-branco' : '/game/conta/meus-dados.php';?>" alt="Clique para ver seu cadastro" title="Clique para ver seu cadastro"><strong>Meu cadastro</strong></a></li>
        <li role="presentation"><a href="<?php echo ($_SERVER['SCRIPT_NAME'] == "/game/conta/dados-acesso.php") ? '#" class="bg-verde-claro txt-branco' : '/game/conta/dados-acesso.php';?>" alt="Clique para alterar seus dados de segurança" title="Clique para alterar seus dados de acesso"><strong>Editar dados de acesso</strong></a></li>
    </ul>
</div>