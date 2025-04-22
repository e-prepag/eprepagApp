<?php
/*
 *
 * Página para tratamento de erros e mensagens
 * parâmetros enviados via post: "msg", "link" e "titulo"
 * msg: mensagem a ser exibida no modal e no fundo
 * link: link de redirecionamento apos fechar o modal - não obrigatório
 * titulo: titulo a ser exibido no modal
 *
 */

require_once "../../../includes/constantes.php";
require_once DIR_CLASS . 'gamer/controller/HeaderController.class.php';

$controller = new HeaderController;
$controller->setHeader();
$erro = "";
if (isset($_GET['c'])) {
    $objEncryption = new Encryption();
    $strDecrypt = $objEncryption->decrypt($_GET['c']);

    $vars = explode("&", $strDecrypt);
    $email = explode("=", $vars[0]);
    $id = explode("=", $vars[1]);

    $strId = $objEncryption->encrypt("id");
    $strEmail = $objEncryption->encrypt("email");

    $_SESSION[$strId] = $objEncryption->encrypt($id[1]);
    $_SESSION[$strEmail] = $objEncryption->encrypt($email[1]);

    $usuario = UsuarioGames::getUsuarioGamesById($id[1]);
    
    if(!$usuario->getId()){
        $erro = "Usuário para alteração não encontrado";
    }
    
}else{
    $erro = "Acesso não permitido.";
}
?>
<script src="/js/valida.js"></script>
<script src="/js/validaSenha.js"></script>
<script>
    $(function() {

        $("#alteraEmail").click(function() {

            waitingDialog.show('Por favor, aguarde...', {dialogSize: 'sm'});

            if ($("#senha").val() == "") {
                waitingDialog.hide();
                manipulaModal(1, "A senha deve ser informada.", "Erro");
                return false;
            }

            $.ajax({
                type: "POST",
                dataType: "JSON",
                url: "/game/ajax/dados-acesso.php",
                data: {type: "novoEmail", senha: $("#senha").val()},
                success: function(obj) {

                    waitingDialog.hide();

                    if (obj.erro.length > 0) {
                        manipulaModal(1, obj.erro, "Erro");
                        $("#senha").val('');
                        return false;
                    } else {
                        manipulaModal(2, "E-mail alterado.", "Operação concluída.");
                        $('#modal-load').on('hidden.bs.modal', function () { location.href='/game/conta/meus-dados.php' });
                        return false;
                    }
                },
                error: function() {
                    waitingDialog.hide();
                    manipulaModal(1, "Erro desconhecido, favor entrar em contato com o nosso suporte.", "Erro");
                    return false;
                }
            });
        });
        
<?php
        if($erro != ""){
            print 'manipulaModal(1, "'.$erro.'", "Erro"); $("#modal-load").on("hidden.bs.modal", function () { location.href="/game/conta/dados-acesso.php" });';
        }
?>
        $("#formAlteraEmail").keypress(function(e){
            var key = e.keyCode || e.which;
            
            if(key == "13"){
                $("#alteraEmail").trigger("click");
                return false;
            }
        });

    });
</script>
<?php
    if($erro == ""){
?>
    <div class="container txt-azul-claro bg-branco p-bottom40">
        <div class="col-md-8 top20 txt-preto">
            <div class="row top10">
                <div class="col-md-6 text-right">
                    <label for="login">E-mail:</label>
                </div>
                <div class="col-md-6">
                    <?php echo $email[1] ?>
                </div>
            </div>
            <form method='post' id="formAlteraEmail">
                <div class="row top10">
                    <div class="col-md-6 text-right">
                        <label for="login">Digite sua senha para efetuar a alteração:</label>
                    </div>
                    <div class="col-md-6">
                        <input type="password" class="form-control" id="senha" autocomplete="new-password" name="senha" placeholder="Senha atual">
                    </div>
                </div>
                <div class="row top10">
                    <div class="col-md-6 col-md-offset-6">
                        <a href="javascript:void(0);" id="alteraEmail" class="btn btn-info">Confirmar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php
    }
?>

<?php
require_once RAIZ_DO_PROJETO . "public_html/game/includes/footer.php";
?>