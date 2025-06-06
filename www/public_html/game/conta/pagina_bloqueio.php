<?php require_once __DIR__ . '/../../../includes/constantes_url.php'; ?>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Bloqueado</title>
  <style>
	
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
      padding: 14px ;
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
    <img src="<?= EPREPAG_URL_HTTPS ?>/sys/imagens/epp_logo.png"/>

    <h1>Voc<?php echo utf8_encode("ê"); ?> est<?php echo utf8_encode("á"); ?> bloqueado de realizar login</h1>
    <p>Para desbloquear seu acesso entre em contato com o time de suporte com o bot<?php echo utf8_encode("ã"); ?>o abaixo</p>
    <a href="<?= EPREPAG_URL_HTTPS ?>/game/suporte.php" rel="noopener noreferrer">Clique Aqui</a>
  </div>
</body>
</html>