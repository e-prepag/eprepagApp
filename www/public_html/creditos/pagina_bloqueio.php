<?php require_once __DIR__ . '/../../includes/constantes_url.php'; ?>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Bloqueado</title>
  <style>
    body {
      font-family: 'Helvetica Neue', sans-serif;
      font-weight: 500;
    }
    .blocked-page {
      width: 100%;
      height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
    }

    .blocked-page a {
      width: 200px;
      padding: 14px;
      border-radius: 3px;
      background-color: #2886af;
      color: #fff;
      text-align: center;
      text-decoration: none;
    }
  </style>
</head>

<body>
  <div class="blocked-page">
    <img src="<?= EPREPAG_URL_HTTPS ?>/sys/imagens/epp_logo.png" />

    <h1>Voc&ecirc; est&aacute; bloqueado de realizar login</h1>
    <p>Caso n&atilde;o entenda o motivo do bloqueio, entre em contato com o time de suporte com o bot&atilde;o abaixo
      para desbloquear seu acesso.</p>
    <a href="<?= EPREPAG_URL_HTTPS ?>/game/suporte.php" rel="noopener noreferrer">Clique Aqui</a>
    <br>
    <?php
    if (isset($_GET['msg']) && trim($_GET['msg']) !== '') {
      echo "<p>Motivo: " . htmlspecialchars(utf8_encode($_GET['msg'])) . "</p>";
    }
    ?>

  </div>
</body>

</html>