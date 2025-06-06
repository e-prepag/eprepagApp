<?php

var_dump($_POST);

?>
<html>
  <head>
    <title>hCaptcha Demo</title>
    <script src="https://js.hcaptcha.com/1/api.js" async defer></script>
  </head>
  <body>
    <form action="" method="POST">
      <input type="text" name="email" placeholder="Email" />
      <input type="password" name="password" placeholder="Password" />
      <div class="h-captcha" data-sitekey="cf431eba-155c-4d7f-a313-bf9d69cdf7e2"></div>
      <br />
      <input type="submit" value="Submit" />
    </form>
  </body>
</html>