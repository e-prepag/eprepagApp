<?php

header("Content-Type: text/html; charset=ISO-8859-1", true);
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') or die('Non-ajax');

require_once "../../../includes/constantes.php";
require_once DIR_INCS . "main.php";
require_once DIR_INCS . "pdv/main.php";

// Step 1
$username = getInputRequest('username');
if (preg_match('/[\[\]{}*;()"\']/', $username) || $username == "") {

    die("Erro: O user $username não pode ser vazio ou possuir caracteres não permitidos. (, ), [, ], {, }, *, ;, \", ')");
}
$email = getInputRequest('email');
if (!isValidEmail($email)) {
    die("Erro: O e-mail fornecido é inválido.");
}
$email_confirmacao = getInputRequest('email_confirmacao');
if (!isValidEmail($email_confirmacao)) {
    die("Erro: O e-mail de confirmação é inválido.");
}
$password = getInputRequest('password');
$password_confirmacao = getInputRequest('password_confirmacao');

// Step 2
$telefone_contato = getInputRequest('telefone_contato');
$celular_contato = "";
$skype_contato = "";

//$nome_representante = getInputRequest('nome_representante');
//$cpf_representante = getInputRequest('cpf_representante');
//$data_nascimento = getInputRequest('data_nascimento');
//$rg_representante = getInputRequest('rg_representante');
//$nome_representante = 'Nao informado';
//$data_nascimento = '31/12/1969';
$rg_representante = 'Nao informado';

// Step 3
$fantasia_empresa = "";
$razao_social_empresa = "PDV INICIANDO COM E-Prepag";
$cnpj_empresa = getInputRequest('cnpj_empresa');
$tipo_estabelecimento_empresa = "Outros"; //getInputRequest('tipo_estabelecimento_empresa')
$outro_estabelecimento = "";
$faturamento_medio = "1";
$cep_empresa = "00000000";
$estado_empresa = "SP";
$cidade_empresa = "";
$bairro_empresa = "";
$endereco_empresa = "";
$tipo_endereco = "";
$numero_empresa = "";
$complemento_empresa = "";
$site_empresa = "";
$inscricao_estadual_empresa = "";

// Step 4
$aux_nome_socios = "";
$nome_socios = "";

$aux_cpf_socios = "";
$cpf_socios = "";

$aux_data_nascimento_socios = "";
$data_nascimento_socios = "";

//Colocando dados do primeiro sÃ³cio como representante da empresa
$cpf_representante = "00000000000";
$nome_representante = sanitizeInput(getInputRequest('name'));
$data_nascimento = "";

$aux_porcentagem_socios = "";
$porcentagem_socios = "";

// Step 5
//$termos = getInputRequest('termos');

$telefone = splitTelphoneNumber($telefone_contato); //$telefone_contato);
$ddd_telefone = $telefone['ddd'];
$numero_telefone = $telefone['number'];

$celular = "";
$ddd_celular = "";
$numero_celular = "";

$como_conheceu_eprepag = sanitizeInput(getInputRequest('como_conheceu_eprepag'));

/*
    Se o usuÃ¡rio escolheu a opÃ§Ã£o "outro" do select "Como conheceu a E-Prepag?"
    SerÃ¡ incorporado a resposta do campo aberto em baixo
*/
if ($como_conheceu_eprepag == "outro") {
    $como_conheceu_eprepag = "OUTRO: " . sanitizeInput(getInputRequest('campo_outro_input'));
}

if (!empty($_POST["g-recaptcha-response"])) {

    $tokenInfo = ["secret" => "6Lc4XtkkAAAAAJYRV2wnZk_PrI7FFNaNR24h7koQ", "response" => $_POST["g-recaptcha-response"], "remoteip" => $_SERVER["REMOTE_ADDR"]];

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
        echo "Processo invalidado por RECAPTCHA.<br>";
        exit;
    }

} else {
    echo "Você deve realizar a verificação do RECAPTCHA para prosseguir.<br>";
    exit;
}

$cad_usuarioGames = new UsuarioGames(
    null, // $ug_id
    $username,
    $password,
    null, // $ug_blAtivo
    0, // $ug_blStatusBusca
    null, // $ug_dDataInclusao
    null, // $ug_dDataUltimoAcesso
    null, // $ug_iQtdeAcessos
    $fantasia_empresa,
    $razao_social_empresa,
    $cnpj_empresa,
    $nome_representante, // Responsavel legal Ã© o mesmo que o representante
    $email,
    $endereco_empresa,
    $tipo_endereco,
    $numero_empresa,
    $complemento_empresa,
    $bairro_empresa,
    $cidade_empresa,
    $estado_empresa,
    $cep_empresa,
    '55', // DDI
    $ddd_telefone,
    $numero_telefone,
    '55',
    $ddd_celular,
    $numero_celular,
    '55',
    '', //$ug_sFaxDDD
    '', //$ug_sFax
    null, //$ug_sRACodigo
    null, //$ug_sRAOutros
    '55', //$ug_sContato01TelDDI
    $ddd_telefone, //$ug_sContato01TelDDD
    $numero_telefone, // $ug_sContato01Tel
    $nome_representante, // $ug_sContato01Nome
    null, // $ug_sContato01Cargo
    null, // $ug_sObservacoes
    'PJ', // $ug_cTipoCadastro
    $nome_representante, // $ug_sNome
    $cpf_representante, // $ug_sCPF
    $rg_representante, // $ug_sRG
    null, // $ug_dDataNascimento
    '', // $ug_cSexo
    null, // $ug_sPerfilSenhaReimpressao
    2, // $ug_iPerfilFormaPagto
    null, // $ug_fPerfilLimite
    null, // $ug_fPerfilSaldo
    $inscricao_estadual_empresa,
    $site_empresa,
    null, // $ug_i_abertura_ano
    null, // $ug_i_abertura_mes
    '', // $ug_s_cartoes
    $faturamento_medio,
    $nome_representante, // $ug_s_repr_legal_nome
    $rg_representante, // $ug_s_repr_legal_rg
    $cpf_representante, // $ug_s_repr_legal_cpf
    '55',
    $ddd_telefone, // $ug_s_repr_legal_tel_ddi
    $numero_telefone,
    '55',
    $ddd_celular, // $ug_s_repr_legal_cel_ddd
    $numero_celular,
    $email, // $ug_s_repr_legal_email
    $skype_contato,
    1, // $ug_bl_repr_venda_igual_repr_legal
    $nome_representante, // $ug_s_repr_venda_nome
    $rg_representante, // $ug_s_repr_venda_rg
    $cpf_representante, // $ug_s_repr_venda_cpf             =
    '55', // $ug_s_repr_venda_tel_ddi         =
    $ddd_telefone, // $ug_s_repr_venda_tel_ddd         =
    $numero_telefone, // $ug_s_repr_venda_tel             =
    '55', // $ug_s_repr_venda_cel_ddi         =
    $ddd_celular, // $ug_s_repr_venda_cel_ddd         =
    $numero_celular, // $ug_s_repr_venda_cel             =
    '', // $ug_s_repr_venda_email             =
    $skype_contato, // $ug_s_repr_venda_msn             =
    null, // $ug_s_dados_bancarios_01_banco
    null, // $ug_s_dados_bancarios_01_agencia
    null, // $ug_s_dados_bancarios_01_conta
    null, // $ug_s_dados_bancarios_01_abertura
    null, // $ug_s_dados_bancarios_02_banco
    null, // $ug_s_dados_bancarios_02_agencia
    null, // $ug_s_dados_bancarios_02_conta
    null, // $ug_s_dados_bancarios_02_abertura
    1, // $ug_i_computadores_qtde
    '', // $ug_s_comunicacao_visual
    'n', // $ug_sNews
    2, // $ug_iRiscoClassif
    null, // $ug_perfil_limite_ref
    $como_conheceu_eprepag, // $ug_ficou_sabendo
    null, // $ug_Substatus
    null,
    null,
    null,
    null,
    null,
    $tipo_estabelecimento_empresa,
    null,
    null,
    null,
    null,
    null,
    null,
    null,
    null,
    $nome_socios,
    $cpf_socios,
    $data_nascimento_socios,
    $porcentagem_socios
);

$cad_usuarioGames->setReprLegalDataNascimento($data_nascimento);
//$msg = UsuarioGames::validarCampos($cad_usuarioGames, 1);
$cad_usuarioGames->setAtivo(0);
$cad_usuarioGames->setSubstatus(1);

if ($cad_usuarioGames->getUgIdNexCafe()) {
    $encryption = new Encryption();
    $cad_usuarioGames->setUgIdNexCafe($encryption->decrypt($cad_usuarioGames->getUgIdNexCafe()));
}

if ($cad_usuarioGames->getTipoEstabelecimento() == "Outros") {
    $temp_tipo_estabelecimento_empresa = $outro_estabelecimento;
    $sql = "select te_id from tb_tipo_estabelecimento where UPPER(te_descricao)='" . strtoupper(str_replace("'", '"', $temp_tipo_estabelecimento_empresa)) . "'";
    $rs_select_tipo_estabelecimento = SQLexecuteQuery($sql);
    if ($rs_select_tipo_estabelecimento_row = pg_fetch_array($rs_select_tipo_estabelecimento)) {
        //echo  "rs_select_tipo_estabelecimento_row [te_id]: ".$rs_select_tipo_estabelecimento_row['te_id']."<br>";
        $cad_usuarioGames->setTipoEstabelecimento($rs_select_tipo_estabelecimento_row['te_id']);
    }//end if ($rs_select_tipo_estabelecimento_row = pg_fetch_array($rs_select_tipo_estabelecimento))
    else {
        $outro_estabelecimento = utf8_encode(str_replace("'", '"', $outro_estabelecimento));
        $sql = "INSERT INTO tb_tipo_estabelecimento (te_ativo,te_descricao) VALUES (0,'" . $outro_estabelecimento . "');"; //".utf8_decode($resposta)."

        $rs_tipo_estabelecimento = SQLexecuteQuery($sql);
        if (!$rs_tipo_estabelecimento) {
            //echo "Erro ao salvar informa&ccedil;&otilde;es do tipo de estabelecimento.<br>";
        } else {
            $sql = "select te_id from tb_tipo_estabelecimento where UPPER(te_descricao)='" . strtoupper(str_replace("'", '"', $outro_estabelecimento)) . "'";
            $rs_select_tipo_estabelecimento_inserido = SQLexecuteQuery($sql);
            $rs_select_tipo_estabelecimento_inserido_row = pg_fetch_array($rs_select_tipo_estabelecimento_inserido);
            $cad_usuarioGames->setTipoEstabelecimento($rs_select_tipo_estabelecimento_inserido_row['te_id']);
        }
    }
}

$instUsuarioGames = new UsuarioGames;

// Insere os dados no banco e retorna a resposta
$msg = $instUsuarioGames->inserirPDO($cad_usuarioGames, $_POST);

if ($msg == "") {
    $pdo = ConnectionPDO::getConnection()->getLink();
    $stmt = $pdo->prepare('SELECT * FROM dist_usuarios_games WHERE ug_email ILIKE ?');
    $stmt->execute(array($email));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $_SESSION["cadastrado"] = true;

        $senha = 'P@8v#Xz4!Tm9';

        $url = "https://www.e-prepag.com.br/creditos/layout/ajaxEmail.php?type=email&email=" . urlencode($email) . "&username=" . urlencode($username) . "&password=" . urlencode($senha);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_exec($ch);
        curl_close($ch);
    } else {
        die("Não foi possível cadastrar o usuário");
    }
}

echo str_replace("\n", "<br>", $msg);

function getInputRequest($varname, $filter = FILTER_DEFAULT)
{
    if (array_key_exists($varname, $_POST)) {
        $content = filter_input(INPUT_POST, $varname, (int) $filter);
        if (!empty($content)) {
            return utf8_decode($content);
        }
    }
    return null;
}

function getInputRequestVector($var)
{
    $array_req = array();
    if (is_array($var)) {
        foreach ($var as $content) {
            if (!empty($content)) {
                $array_req[] = utf8_decode($content);
            }
        }
    }
    return $array_req;
}

function splitTelphoneNumber($number, $isMobile = false)
{
    $digitMobile = ($isMobile) ? '9,10' : '10';
    $regex = '/\(([0-9]{2})\)\s([0-9\-]{' . $digitMobile . '})/';

    preg_match_all($regex, $number, $matches);

    if (is_null($matches) || count($matches) === 0) {
        return false;
    }

    //    var_dump($regex);exit;

    return array('ddd' => $matches[1][0], 'number' => str_replace('-', '', $matches[2][0]));
}

// Função para remover caracteres especiais indesejados
function sanitizeInput($input)
{
    return preg_replace('/[\[\]{}*;()"\']/', '', $input);
}

// Função para validar e-mail
function isValidEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}
