<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);  // Exibe todos os tipos de erros
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/gamer/chave.php";
require_once $raiz_do_projeto . "includes/gamer/AES.class.php";
require_once $raiz_do_projeto . "class/util/Login.class.php";
require_once __DIR__ . "/../../libs/PHPGangsta/GoogleAuthenticator.php";
require_once "/www/class/classSecureEncryption.php";

$con = ConnectionPDO::getConnection();
if (!$con->isConnected()) {
    // retornar os erros: $con->getErrors();
    die('Erro#2');
}
$ga = new PHPGangsta_GoogleAuthenticator();
if (!$_SESSION['secret']) {
    $secret = $ga->createSecret();
    $_SESSION['secret'] = $secret;
} else {
    $secret = $_SESSION['secret'];
    if (isset($_POST['cad_senhaAtual']) && $_SESSION["token_csrf"] == $_POST["token_csrf"]) {
        $erros = 0;
        $msg = "";

        $pdo = $con->getLink();
        $sql = "SELECT * FROM usuarios WHERE id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(array($_SESSION["iduser_bko"]));

        $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($fetch) == 1) {

            $pgrow = $fetch[0];
            $bcrypt = new SecureEncryption();

            if (!$bcrypt->verifyPassword($senha_decript, $pgrow['shn_password'])) {
                $msg = "Senha atual incorreta.";
                $color = "txt-vermelho";
            } else {

                $tokenValido = empty($pgrow['chave_autenticador']) ? true : $ga->verifyCode($pgrow['chave_autenticador'], $_POST['token_old'], 2);
                $tokenNovo = $ga->verifyCode($secret, $_POST['token'], 2);
                if (!$tokenNovo) {
                    $msg = "Token novo inválido.";
                    $color = "txt-vermelho";
                } else if (!$tokenValido) {
                    $msg = "Token antigo inválido.";
                    $color = "txt-vermelho";
                } else {
                    $update = "UPDATE usuarios set chave_autenticador = ? where id = ?";
                    $stmt = $pdo->prepare($update);
                    $stmt->execute(array($secret, $_SESSION["iduser_bko"]));
                    if ($stmt->rowCount() == 1) {
                        $msg = "Autenticador alterado com sucesso.";
                        $color = "txt-verde";
                        $_SESSION['secret'] = null;
                    } else {
                        $msg = "Erro ao alterar autenticador. Entre em contato com o suporte.";
                        $color = "txt-vermelho";
                    }
                }
            }
        } else {
            $msg = "Senha atual incorreta.";
            $color = "txt-vermelho";
        }
    } else {
        $msg = "Preencha sua senha atual.";
        $color = "txt-vermelho";
    }
}
$_SESSION["token_csrf"] = bin2hex(random_bytes(32));
$qrCodeUrl = $ga->getQRCodeGoogleUrl('E-Prepag bko', $secret);
?>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao(); ?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><?php echo $sistema->item->getDescricao(); ?></li>
    </ol>
</div>
<?php
if (isset($msg)) {
?>
    <div class="col-md-12 espacamento <?php echo $color; ?>">
        <strong><?php echo $msg ?></strong>
    </div>
<?php
}
if (isset($msg) && $color == "txt-verde") {
?>
    <table class="pull-left" style="margin: 15px;">
        <tr>
            <td colspan="2" class="<?php echo $color; ?>"><?php echo $msg; ?>
            <td>
        </tr>
        <tr>
            <td colspan="2"><a href="/index.php" class="btn pull-left btn-success">Clique aqui para voltar ao início.</a>
            <td>
        </tr>
    </table>
<?php
} else {
?>
    <div class="col-md-7 espacamento txt-preto">
        <form name="form1" action="" method="post">
            <input type="hidden" name="token_csrf" value="<?= $_SESSION["token_csrf"] ?>">
            <div class="mb-3">
                <label style="margin-top: 15px;" for="token_old">
                    Insira o Token atual (somente se tiver):
                </label>
                <input type="text" name="token_old" id="token_old" class="form-control">
            </div>
            <div class="mb-3">
                <label style="margin-top: 15px;" for="cad_senhaAtual">
                    Insira sua senha atual:
                </label>
                <input type="password" name="cad_senhaAtual" id="cad_senhaAtual" class="form-control">
            </div>
            <div class="text-left" style="margin: 15px;">
                <label class="">QR Code:</label>
                <div>
                    <img src="<?= $qrCodeUrl ?>" class="img-fluid" />
                </div>
            </div>

            <div class="text-left mt-3" style="margin: 15px;">
                <label class="">Chave de segurança:</label>
                <div onclick='(() => {
                        const authCode = document.getElementById("authCode").innerText;
                    navigator.clipboard.writeText(authCode).then(()=> {
                    const message = document.getElementById("copyMessage");
                    message.innerText = "Copiado!";
                    setTimeout(() => {
                    message.innerText = "Clique para copiar";
                    }, 2000);
                    }).catch(err => {
                    console.error("Erro ao copiar:", err);
                    });
                    })()' style="cursor: pointer;">
                    <p id="authCode" style="font-size: 15px; letter-spacing: 0.5px; margin-bottom: 0px;">
                        <?= $secret ?>
                    </p>
                    <small style="color: #333;" id="copyMessage">Clique para copiar</small>
                </div>
            </div>

            <div class="mb-3">
                <label style="margin-top: 15px;" for="token">
                    Insira o novo Token gerado:
                </label>
                <input type="text" name="token" id="token" class="form-control">
            </div>

            <div class="d-grid gap-2 mt-3" style="margin-top: 15px;">
                <button type="submit" class="btn btn-success">Salvar</button>
            </div>
        </form>
    </div>
<?php
}
require_once $raiz_do_projeto . "backoffice/includes/rodape_bko.php";
?>
</body>

</html>