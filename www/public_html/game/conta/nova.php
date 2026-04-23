<?php
require_once __DIR__ . '/../../../includes/constantes_url.php';
$request_uri = $_SERVER['REQUEST_URI'];
// Obtém o script principal chamado
$script_name = $_SERVER['SCRIPT_NAME'];
// Se a URI acessada não for exatamente igual ao script chamado, bloqueia o acesso
if ($request_uri !== $script_name) {
    http_response_code(403);
    die("Acesso negado.");
}
require_once "../../../includes/constantes.php";
require_once DIR_CLASS . "gamer/controller/HeaderController.class.php";
require_once RAIZ_DO_PROJETO . 'consulta_cpf/config.inc.cpf.php';
$usuarios = new UsuarioGames;
// error_reporting(E_ALL); 
// ini_set("display_errors", 1); 

if (isset($_POST['login']) && !empty($_POST['login'])) {

    $fileLog = fopen("/www/arquivos_gerados/logs/cadastro_games.txt", "a+");
    if ($fileLog) {
    fwrite($fileLog, "Dta requisão: " . date("d-m-Y H:i:s") . "\n");
    }
    $postLog = $_POST;
    $chavesSensiveis = ['senha', 'conf_senha', 'cpf', 'data_nascimento', 'telefone', 'celular', 'ipAdress', 'location', 'device', 'g-recaptcha-response'];
    foreach ($chavesSensiveis as $key) {
        if (isset($postLog[$key])) {
            $postLog[$key] = '***REMOVIDO***';
        }
    }
    fwrite($fileLog, "Dados recebidos: " . json_encode($postLog) . "\n");
    fwrite($fileLog, str_repeat("*", 50) . "\n\r");
    fclose($fileLog);

    //if($_SERVER["REMOTE_ADDR"] == "201.93.162.169"){

    if (!empty($_POST["g-recaptcha-response"])) {
        $captcha_secret = getenv("AMBIENTE") == "HOMOLOGACAO" ? "6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe" : getenv("RECAPTCHA_SECRET_KEY");

        $tokenInfo = ["secret" => $captcha_secret, "response" => $_POST["g-recaptcha-response"], "remoteip" => $_SERVER["REMOTE_ADDR"]];

        $recaptcha = curl_init();
        curl_setopt_array($recaptcha, [
            CURLOPT_URL => "https://www.google.com/recaptcha/api/siteverify",
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => http_build_query($tokenInfo)

        ]);
        $retorno = json_decode(curl_exec($recaptcha), true);
        curl_close($recaptcha);

        if ($retorno["success"] != true || (isset($retorno["error-codes"]) && !empty($retorno["error-codes"]))) {
            $erros[] = "<p>Processo invalidado por RECAPTCHA.</p>";
        }
    } else {
        $erros[] = "<p>Você deve realizar a verificação do RECAPTCHA para prosseguir.</p>";
    }

    //}

    function verificaPOST($referer, $POST)
    {

        //if (strpos($referer,"bronzato.com.br")===false && strpos($referer,"contause.digital")===false){return false;}
        $flag = true;
        foreach ($_POST as $xa => $xb) {
            $xb = serialize($xb);
            if (strpos($xb, "dbms_pipe.receive_message") !== false || strpos($xb, "DBMS_PIPE.RECEIVE_MESSAGE") !== false || strpos($xb, "delete") !== false || strpos($xb, "delete") !== false || strpos($xb, "update") !== false || strpos($xb, "select") !== false) {
                return false;
            }

            if (strpos($xb, "dbms_pipe.receive_message") !== false || strpos($xb, "DBMS_PIPE.RECEIVE_MESSAGE") !== false || strpos(hexToStr($xb), "delete") !== false || strpos(hexToStr($xb), "update") !== false || strpos(hexToStr($xb), "select") !== false) {
                return false;
            }
        }

        if ($flag) {
            return true;
        } else {
            return false;
        }
    }

    function strToHex($string)
    {
        $hex = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $ord = ord($string[$i]);
            $hexCode = dechex($ord);
            $hex .= substr('0' . $hexCode, -2);
        }
        return strToUpper($hex);
    }

    function hexToStr($hex)
    {
        $string = '';
        for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
            $string .= chr(hexdec($hex[$i] . $hex[$i + 1]));
        }
        return $string;
    }

    require_once DIR_CLASS . "util/Validate.class.php";
    require_once DIR_CLASS . "util/Login.class.php";

    $erros = array();
    $validate = new Validate;
    $clsLogin = new Login($_POST['senha']);

    if ($validate->qtdCaracteres($_POST['login'], 5, 100) > 0)
        $erros[] = "<p>O Login deve ter mais de 5 caracteres.</p>";

    if ($validate->email($_POST['email']) > 0 || $_POST['email'] != $_POST['conf_mail'])
        $errros[] = "<p>A confirmação de e-mail está incorreta. Verifique os dados inseridos.</p>";

    if ($clsLogin->valida() > 0 || $_POST['senha'] != $_POST['conf_senha']) {
        $erros[] = "<p>Senha não atinge os níveis de segurança desejados.</p>";
    }
    if (!isset($_POST['termos_uso'])) {
        $erros[] = "<p>Você deve aceitar os termos de uso.</p>";
    }
    if (!isset($_POST['termos_responsaveis_uso'])) {
        $erros[] = "<p>Você deve aceitar os termos de responsabilidade para pais e responsáveis.</p>";
    }

    $cpfComMascara = $_POST['cpf'];
    $cpf = str_replace([".", "-"], "", $cpfComMascara);
    $dataNascimento = $_POST['dtNasc'];

    if (!verificaPOST("", $_POST)) {
        $erros[] = "<p>Não foi possivel continuar seu processo.</p>";
    } else {

        if (strpos($dataNascimento, "/") !== false) {
            $dataNascimento = str_replace(" ", "", $dataNascimento);
            $dataQuebrada = explode("/", $dataNascimento);
            $verificaNumeroDePartes = (count($dataQuebrada) == 3) ? true : false;
            $verificaParteVazia = (array_search("", $dataQuebrada) === false) ? true : false;
            if ($verificaNumeroDePartes === true && $verificaParteVazia === true && strlen($dataNascimento) == 10) {

                $url = "https://api.eprepag.com.br/webhook/ajaxCpf.php";

                $postFields = [
                    'cpf' => $cpf,
                    'dataNascimento' => $dataNascimento
                ];

                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Content-Type: application/x-www-form-urlencoded",
                    "X-Requested-With: XMLHttpRequest"
                ]);

                $response = curl_exec($ch);

                if (curl_errno($ch)) {
                    $erros[] = "<p>Erro ao verificar o CPF. Tente novamente. Se o problema persistir, entre em contato com o suporte.</p>";
                }

                curl_close($ch);
                $retorno = json_decode($response, true);

                if (is_array($retorno)) {
                    array_walk_recursive($retorno, function (&$item) {
                        if (is_string($item)) {
                            $item = mb_convert_encoding($item, 'ISO-8859-1', 'UTF-8');
                        }
                    });
                }

                if ((!isset($retorno["erros"]) || empty($retorno["erros"])) && isset($retorno["nome"]) && isset($retorno["data_nascimento"])) {

                    if ($retorno["data_nascimento"] != $dataNascimento) {
                        $erros[] = "<p>O cpf não foi validado na receita federal.</p>";
                    } else {
                        $dd = $retorno["data_nascimento"];
                        list($ano, $mes, $dia) = explode('-', DateTime::createFromFormat('d/m/Y', $dd)->format("Y-m-d"));
                        $hoje = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
                        $nascimento = mktime(0, 0, 0, $mes, $dia, $ano);
                        $idade = floor((((($hoje - $nascimento) / 60) / 60) / 24) / 365.25);

                        //echo "<script>console.log(".$ano.")</script>";
                        if (UsuarioGames::Validar_CPF_Via_Calculo($cpf) == false) {
                            $erros[] = "<p>O CPF é inválido.</p>";
                        } else if ($idade >= 16) {
                            if ($idade > $GLOBALS["IDADE_MAXIMA"]) {
                                $cpfService = new classCPF(false);
                                if (!$cpfService->cpfEstaNaWhiteList($cpf)) {
                                    $erros[] = "<p>Identificamos que o titular do CPF informado possui mais de " . $GLOBALS["IDADE_MAXIMA"] . " anos. Para continuar, é necessário validação de identidade pelo time de Risco e Compliance (RC). Envie um e-mail para rc@e-prepag.com.br solicitando a liberação do CPF.</p>";
                                }
                            }

                            if (empty($erros)) {
                                $nome = $retorno["nome"];
                                $loginCadastro = preg_replace('/[^a-zA-Z0-9_.@-]/', '', (string)$_POST['login']);
                                $nomeCadastro = preg_replace('/[\x00-\x1F\x7F]/u', '', (string)$nome);
                                $cpfCadastro = substr($cpf, 0, 3) . "." . substr($cpf, 3, 3) . "." . substr($cpf, 6, 3) . "-" . substr($cpf, 9, 2);
                                $dataNascimentoCadastro = preg_match('/^\d{2}\/\d{2}\/\d{4}$/', (string)$_POST['dtNasc']) ? (string)$_POST['dtNasc'] : '';
                                $emailCadastro = filter_var((string)$_POST['email'], FILTER_SANITIZE_EMAIL);

                                if ($loginCadastro === '' || $dataNascimentoCadastro === '' || !filter_var($emailCadastro, FILTER_VALIDATE_EMAIL)) {
                                    $erros[] = "<p>Dados de cadastro inválidos.</p>";
                                }

                                if (empty($erros)) {
                                    $usuarios = new UsuarioGames;
                                    $usuarios->setLogin($loginCadastro);
                                    $usuarios->setNome($nomeCadastro);
                                    $usuarios->setNomeCPF($nomeCadastro);
                                    $usuarios->setCPF($cpfCadastro);
                                    $usuarios->setDataNascimento($dataNascimentoCadastro);
                                    $usuarios->setEmail($emailCadastro);
                                    $usuarios->setSenha($_POST['senha']);
                                }
                            }
                        } else {
                            $erros[] = "<p>A idade mínima para o cadastro é de 16 anos.</p>";
                        }
                    }
                } else {
                    $cpfFinal = substr($cpf, 0, 3) . "." . substr($cpf, 3, 3) . "." . substr($cpf, 6, 3) . "-" . substr($cpf, 9, 2);
                    $sql = "select * from usuarios_games where ug_cpf = $1;";
                    $buscaUsuario = SQLexecuteQueryParams($sql, [$cpfFinal]);

                    $linhas = pg_num_rows($buscaUsuario);

                    if ($linhas <= 2) {
                        $erros[] = "<p>Detectamos que você possui mais de um cadastro na E-Prepag. Se você já realizou alguma compra diretamente de um game, é possível que já tenha um cadastro ativa conosco. Verifique o endereço de e-mail utilizado.</p>
                        <p>Em caso de dúvidas, <a href=\'" . EPREPAG_URL_HTTPS_COM . "/support\'>clique aqui para entrar em contato com o suporte da E-Prepag</a>.</p>";
                    } else if (isset($retorno["erros"]) && !empty($retorno["erros"])) {
                        $erros[] = "<p>" . $retorno["erros"] . "</p>";
                    } else {
                        $erros[] = "<p>Erro desconhecido ao verificar o CPF, se o problema persistir, por favor <a href=\'" . EPREPAG_URL_HTTPS_COM . "/support\'>clique aqui para entrar em contato com o suporte da E-Prepag</a>.</p>";
                    }
                }
            } else {
                $erros[] = "<p>A data de nascimento digitada está invalida</p>";
            }
        } else {
            $erros[] = "<p>A data de nascimento digitada está invalida</p>";
        }
    }

    if (empty($erros)) {

        $erro_location = false;
        $ipAdress = $_SERVER["HTTP_X_FORWARDED_FOR"] ?: $_SERVER["REMOTE_ADDR"] ?: "Desconhecido";
        if (isset($_POST["location"]) && !empty($_POST["location"])) {

            preg_match('/^Lat:\s*(-?\d+(\.\d+)?),\s*Lon:\s*(-?\d+(\.\d+)?)/', $_POST['location'], $matches);

            $lat = floatval($matches[1]);
            $lon = floatval($matches[3]);

            if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
                $erro_location = true;
            }
        } else {
            $erro_location = true;
        }

        if ($erro_location) {
            $location_ip = consultarGeoIP($ipAdress);
            if ($location_ip) {
                $location = "Lat: " . $location_ip['results']['latitude'] . ", " . "Lon: " . $location_ip['results']['longitude'];
            } else {
                $erros[] = "<p>Para seguir com o cadastro, precisamos da sua autorização para acessar sua localização. Essa informação nos ajuda a garantir mais segurança no processo. Sua geolocalização será usada somente para esse fim e protegida conforme a Lei Geral de Proteção de Dados (LGPD).</p>";
            }
        }

        if (empty($erros)) {

            $location = $_POST["location"] ?: "Desconhecido";
            $device = $_POST["device"] . " | " . $_SERVER['HTTP_USER_AGENT'];
            $version = "v1 Termos Uso | v1 Termo Respons. ou pais";

            $params_termos = [
                "location" => $location,
                "device" => $device,
                "version" => $version,
                "ipAdress" => $ipAdress
            ];

            $insere = $usuarios->inserirMelhorado($params_termos);
            if (is_array($insere)) {
                $erros = $insere;
            } else {
                Util::redirect("/game/");
            }
        }
    }
}

$controller = new HeaderController;
$controller->setHeader();

require_once RAIZ_DO_PROJETO . "public_html/game/includes/termos-de-uso.php";
$termosDeUso = strip_tags($termosDeUso);
?>
<script src="/js/valida.js"></script>
<script src="/js/validaSenha.js"></script>
<script>
    const msgLocationError = "Para seguir com o cadastro, precisamos da sua autorização para acessar sua localização. Essa informação nos ajuda a garantir mais segurança no processo. Sua geolocalização será usada somente para esse fim e protegida conforme a Lei Geral de Proteção de Dados (LGPD).";

    <?php
    if (!empty($erros)) {
        print "manipulaModal(1,`" . implode($erros) . "`,'Atenção');";
    }
    ?>
    $(function() {

        // Tenta obter localização (se o usuário permitir)
        new Promise((resolve, reject) =>
            navigator.geolocation.getCurrentPosition(resolve, reject, {
                timeout: 10000
            })
        ).catch((error) => {
            //manipulaModal(1, msgLocationError, 'Erro');
            //return null;
        });

        $("#cadastro").submit(async function(e) {

            e.preventDefault();

            if (grecaptcha.getResponse() == "" || grecaptcha.getResponse().length == 0) {
                manipulaModal(1, "Você deve fazer a verificação do RECAPTCHA para finalizar seu cadastro.", 'Erro');
                return false;
            }

            if ($("#termos_uso").is(":checked") && $("#termos_responsaveis_uso").is(":checked")) {
                if (!valida()) {
                    return false;
                }

                var erro = validaFormSenha();
                if (erro.length > 0) {
                    manipulaModal(1, erro.join("<br>"), 'Erro');
                    return false;
                }
            } else {
                manipulaModal(1, "Você deve concordar com os termos de uso e termos dos responsáveis.", 'Erro');
                return false;
            }

            const dispositivo = navigator.userAgent;
            const linguagem = navigator.language;
            const plataforma = navigator.platform;

            // Tenta obter localização (se o usuário permitir)
            const pos = await new Promise((resolve, reject) =>
                navigator.geolocation.getCurrentPosition(resolve, reject, {
                    timeout: 10000
                })
            ).catch((error) => {
                //manipulaModal(1, msgLocationError, 'Erro');
                //return null;
            });

            var localizacao;
            if (!pos || !pos.coords || typeof pos.coords.latitude === 'undefined' || typeof pos.coords.longitude === 'undefined') {
                localizacao = 'Desconhecido';
            } else {
                localizacao = `Lat: ${pos.coords.latitude}, Lon: ${pos.coords.longitude}`;
            }

            if (localizacao === "") {
                //console.log("Localização não obtida.");
                //return false;
            }
            $("#location").val(localizacao);
            $("#device").val(`${dispositivo} | ${plataforma} | ${linguagem}`);

            this.submit();

        });
    });
</script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script src="https://kit.fontawesome.com/e045fafe2e.js" crossorigin="anonymous"></script>
<div class="container txt-cinza bg-branco  p-bottom40">
    <div class="row top10">
        <!-- <div class="alert alert-danger" style="margin: 5px 10px;" role="alert">
          <i class="fas fa-exclamation-triangle"></i>Devido à instabilidade na Receita Federal, seu cadastro na E-prepag não poderá ser finalizado neste momento, mas você pode se cadastrar no Atimopay e comprar créditos para seus games : <a href="https://linktr.ee/atimopay">https://linktr.ee/atimopay</a>
        </div> -->

        <!-- <div class="alert alert-success" style="margin: 5px 10px;" role="alert">
            <i class="fas fa-gamepad"></i> Prefere comprar seus giftcards pelo celular ? Baixe agora mesmo o Atimopay <a
                href="https://linktr.ee/atimopay">clicando aqui</a>.
        </div> -->
        <div class="col-md-12 txt-verde top10">
            <span class="glyphicon glyphicon-triangle-right graphycon-big pull-left" aria-hidden="true"></span><strong
                class="pull-left">
                <h4 class="top20">novo usuário?<span class="hidden-md hidden-lg"><br></span> faça aqui um rápido
                    cadastro!</h4>
            </strong>
        </div>
        <div class="clearfix"></div>
        <div class="col-md-6 col-lg-6 col-sm-12 col-xs-12 borda-colunas-formas-pagamento">
            <form id="cadastro" method="post" class="text-right-lg text-rightmd text-left-sm text-left-xs">
                <input type="hidden" name="location" id="location" value="">
                <input type="hidden" name="device" id="device" value="">
                <div class="row top10">
                    <div class="col-md-6">
                        <label for="login">Login:</label>
                    </div>
                    <div class="col-md-6">
                        <input class="form-control" id="login" char="5" name="login" type="text" value="<?php if (isset($_POST['login']))
                                                                                                            echo htmlspecialchars($_POST['login'], ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>
                <div class="row top20">
                    <div class="col-md-6">
                        <label for="email">Digite seu e-mail:</label>
                    </div>
                    <div class="col-md-6">
                        <input class="form-control" id="email" char="5" maxlength="100" name="email" type="text" value="<?php if (isset($_POST['email']))
                                                                                                                            echo htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>
                <div class="row top10">
                    <div class="col-md-6">
                        <label for="conf_mail">Confirmação de e-mail:</label>
                    </div>
                    <div class="col-md-6">
                        <input class="form-control" id="conf_mail" char="5" maxlength="100" name="conf_mail" type="text"
                            value="<?php if (isset($_POST['conf_mail']))
                                        echo htmlspecialchars($_POST['conf_mail'], ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>

                <div class="row top10">
                    <div class="col-md-6">
                        <label for="cpf">CPF:</label>
                    </div>
                    <div class="col-md-6">
                        <input class="form-control" id="cpf" char="14" maxlength="14" name="cpf" type="text" value="<?php if (isset($_POST['cpf']))
                                                                                                                        echo htmlspecialchars($_POST['cpf'], ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>
                <div class="row top10">
                    <div class="col-md-6">
                        <label for="dtNasc">Data de nascimento:</label>
                    </div>
                    <div class="col-md-6">
                        <input class="form-control" id="dtNasc" char="10" maxlength="10" name="dtNasc" type="text"
                            value="<?php if (isset($_POST['dtNasc']))
                                        echo htmlspecialchars($_POST['dtNasc'], ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>

                <div class="row top10">
                    <div class="col-md-6">
                        <label for="senha">Senha:</label>
                    </div>
                    <div class="col-md-6">
                        <input class="form-control novaSenha" maxlength="35" autocomplete="new-password" char="6"
                            id="senha" name="senha" char="3" type="password" value="">
                    </div>
                </div>
                <div class="row top10">
                    <div class="col-md-6">
                        <label for="conf_senha">Confirmação de senha:</label>
                    </div>
                    <div class="col-md-6">
                        <input class="form-control confirmacaoSenha" maxlength="35" autocomplete="new-password" char="6"
                            id="conf_senha" char="3" name="conf_senha" type="password" value="">
                    </div>

                    <div class="col-md-6 col-md-offset-6 col-sm-12 col-xs-12 txt-preto text-left">
                        <span>Sua senha deve ter:</span>
                        <ul>
                            <li>De 10 a 35 caracteres</li>
                            <li>Letras maiúsculas e minúsculas</li>
                            <li>Números</li>
                            <li>Caracteres especiais (!,?,*,$,%)</li>
                        </ul>
                    </div>

                </div>
                <div class="row top10">
                    <div class="col-md-6 col-md-offset-6">
                        <div class="progress w-auto">
                            <div class="progress-bar hidden progress-bar-danger" style="width: 33.33%">
                                <span class="sr-only">33.33% Complete (danger)</span>
                            </div>
                            <div class="progress-bar hidden progress-bar-warning" style="width: 33.33%">
                                <span class="sr-only">33.33% Complete (warning)</span>
                            </div>
                            <div class="progress-bar hidden progress-bar-success" style="width: 33.33%">
                                <span class="sr-only">33.33% Complete (success)</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row top10" style="display: flex;
                        flex-direction: column;
                        align-items: stretch;
                        margin: 0px 10px;">
                    <div style="text-align: start;">
                        <label for="comment">Termos de uso:</label>
                    </div>
                    <div>
                        <textarea class="form-control" rows="7" style="border-radius: 4px;"
                            readonly="readonly"><?php echo $termosDeUso; ?></textarea>
                    </div>
                </div>
                <div class="row top10 ">
                    <!--                    <div class="col-md-6 col-lg-6 col-sm-10 col-xs-10">
                    </div>-->
                    <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12 text-left">
                        <ul>
                            <li>Este cadastro deve ser utilizado para compras de créditos para uso pessoal.</li>
                            <li>Limite de compras diário de
                                R$<?php echo number_format($GLOBALS['RISCO_GAMERS_TOTAL_DIARIO'], 2) ?>, condicionado ao
                                máximo de <?php echo CPF_QUANTIDADE_LIMITE ?> compras em 30 dias.</li>
                            <li>Precisamos da sua autorização para acessar sua localização. Essa informação nos ajuda a garantir mais segurança no processo. Sua geolocalização será usada somente para esse fim e protegida conforme a Lei Geral de Proteção de Dados (LGPD).</li>
                            <li>Não é permitida a comercialização dos créditos adquiridos. Quer ser um ponto de venda?
                                Acesse: <a href="https://e-prepagpdv.com.br/"
                                    target="_blank">https://e-prepagpdv.com.br/</a></li>
                        </ul>
                    </div>
                </div>

                <div class="row top10">
                    <div class="col-md-8 col-lg-10 col-sm-10 col-xs-10">
                        <label for="termos_responsaveis_uso" style="margin-left: 20px">Termo de Responsabilidade para
                            pais e responsáveis:</label>
                    </div>
                </div>

                <div class="row top10">
                    <div class="cold-md-12 col-lg-12 col-sm-12 col-xs-12 text-left">
                        <ul>
                            <li>Os usuários entre 16 e 18 anos devem certificar-se de ter lido o Termos e
                                Condições de uso da Plataforma E-prepag, juntamente com seus pais ou
                                responsáveis e que todo seu conteúdo tenha sido entendido e aprovado.</li>
                        </ul>
                    </div>
                </div>
                <div class="row top10 ">
                    <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12 text-left"
                        style="display: inline-flex;justify-content: flex-start; margin-left: 20px;">
                        <input id="termos_uso" type="checkbox" char="1"
                            name="termos_uso" value=""><span style="margin-top: 4px; margin-left: 4px;">Li e aceito os termos de uso.</span>
                    </div>
                </div>
                <div class="row top10">
                    <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12 text-left"
                        style="display: inline-flex;justify-content: flex-start; margin-left: 20px;">
                        <input id="termos_responsaveis_uso" type="checkbox" char="1"
                            name="termos_responsaveis_uso" value=""><span style="margin-top: 4px; margin-left: 4px;">Li
                            e aceito os termos de responsabilidade para os pais e responsáveis.</span>
                    </div>
                </div>

                <?php
                $site_key = getenv("AMBIENTE") == "HOMOLOGACAO" ? "6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI" : getenv("RECAPTCHA_SITE_KEY");
                ?>

                <div class="row top10">
                    <div style="padding: 0 0 15px 40px;">
                        <div class="g-recaptcha" data-sitekey="<?= $site_key ?>"></div>
                    </div>
                    <div class="col-md-6 col-md-offset-6">
                        <input type="submit" class="pull-right btn btn-success" value="Prosseguir">
                    </div>
                </div>

            </form>
        </div>
        <div class="col-md-6 col-lg-6 col-sm-12 col-xs-12">
            <div class="banner">
                <a href="/game/guia-jogo-seguro.php">
                    <img
                        src="/css/images/banner-jogo-seguro2.png"
                        style="display: block;
                        object-fit: cover;
                        border-radius: 8px;
                        width: 100%;
                        margin: 0 auto;
                    ">
                </a>
            </div>
            <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12 txt-azul-claro">
                <h2>Seja um ponto de venda</h2>
            </div>
            <div class="col-md-12 col-lg-12 col-sm-12 col-xs-12">
                <h4>Deseja cadastrar seu estabelecimento para vender créditos de games e outros serviços?</h4>
            </div>
            <div class="col-md-1 col-lg-1 col-sm-2 col-xs-2">
                <span class="glyphicon glyphicon-check txt-verde t0"></span>
            </div>
            <div class="col-md-11 col-lg-11 col-sm-10 col-xs-10">
                Mais de 1.000 games
            </div>
            <div class="col-md-1 col-lg-1 col-sm-2 col-xs-2">
                <span class="glyphicon glyphicon-check txt-verde t0"></span>
            </div>
            <div class="col-md-11 col-lg-11 col-sm-10 col-xs-10">
                Sistema 100% online
            </div>
            <div class="col-md-1 col-lg-1 col-sm-2 col-xs-2">
                <span class="glyphicon glyphicon-check txt-verde t0"></span>
            </div>
            <div class="col-md-11 col-lg-11 col-sm-10 col-xs-10">
                Sem custo de cadastro
            </div>
            <div class="col-md-6 col-lg-6 col-sm-12 col-xs-12 top10 align-center">
                <a href="/cadastro-de-ponto-de-venda.php" class="btn btn-info"><em>Faça agora seu cadastro</em></a>
            </div>
            <div class="col-md-6 col-lg-6 col-sm-12 col-xs-12 top10 bottom10 align-center">
                <a link="e-prepagpdv.com.br/" href="#" class="btn redirecionamento btn-info"><em>Veja aqui como
                        funciona</em></a>
            </div>
        </div>
    </div>
</div>
</div>
<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 bg-info align-center borda-direita-basica p-bottom10">
        <h3>
            Quer ser um ponto de venda ?
            <a href="/cadastro-de-ponto-de-venda.php" class="txt-branco" target="_blank"><b><span
                        class="link-destaque">Cadastre-se</span></b></a>
            ou
            <a href="https://e-prepagpdv.com.br/" class="txt-branco" target="_blank"><b><span
                        class="link-destaque">saiba mais</span></b></a>.
        </h3>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"
    integrity="sha512-pHVGpX7F/27yZ0ISY+VVjyULApbDlD0/X0rgGbTqCE7WFW5MezNTWG/dnhtbBuICzsd0WQPgpE4REBLv+UqChw=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    $('#cpf').mask('000.000.000-00', {
        reverse: true
    });
    $('#dtNasc').mask('00/00/0000', {
        placeholder: "__/__/____"
    });
</script>
<?php
require_once RAIZ_DO_PROJETO . "public_html/game/includes/footer.php";
