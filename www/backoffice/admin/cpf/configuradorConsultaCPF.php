<?php
//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . 'backoffice/includes/topo.php';
require_once $raiz_do_projeto . 'consulta_cpf/config.inc.cpf.php';
require_once $raiz_do_projeto . 'sftp/connect.php';
require_once $raiz_do_projeto . 'sftp/classSFTPconnection.php';
require_once "/www/includes/bourls.php";
if (!function_exists("enviaEmail3")) {
    require_once $raiz_do_projeto . "includes/main.php";
    require_once $raiz_do_projeto . "includes/pdv/main.php";
    require_once $raiz_do_projeto . "includes/pdv/functions.php";
}

//var_dump($_SESSION);

/* 
    CONTROLLER
 */
//Settando inicialmente a variável $environment
if (!isset($environment))
    $environment = CPF_PARTNER_ENVIRONMET;
//Verificando se executou o click no botão atualizar
if (isset($BtnSearch) && $BtnSearch) {
    //Variavel de OUTPUT
    $msg = '';

    $con = ConnectionPDO::getConnection();
    $pdo = $con->getLink();

    $select = $pdo->prepare("select * from consulta_api where ativo = 'A';");
    $select->execute();

    if ($select->rowCount() > 0) {
        $sql = "update consulta_api set ativo = 'I' where ativo = 'A';";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    }

    $sql = "update consulta_api set ativo = 'A' where parceiro_consulta = :PARCEIRO;";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":PARCEIRO", ($apiEnv ? $apiEnv : 2));
    $stmt->execute();

    $corpoEmail = "O provedor de verificação de CPF mudou para " . $vetorLegenda[$environment] . "\n";
    $corpoEmail .= "Alterado por: " . $_SESSION["userlogin_bko"] . "\n";
    $corpoEmail .= "Data: " . date("d-m-Y H:i:s") . "\n";

    enviaEmail3("suporte@e-prepag.com.br", "", "", "Mudança Provedor CPF", $corpoEmail, "");


    //Montando Arquivo com a Configuração ATUAL 
    $conteudoArquivo = '<?php
// ' . date('Y-m-d H:i:s') . '
// Constante que define o Parceiro de Integração. Onde (CREDIFY = 1) ou (OMNIDATA = 2) ou (Consulta CACHE = 3)
define("CPF_PARTNER_ENVIRONMET",' . $vetorReverso[$environment] . ');
?>';
    $newfile = fopen($raiz_do_projeto . "consulta_cpf/environment.cpf.php", 'w');
    if (fwrite($newfile, $conteudoArquivo)) {
        $msg = "Sucesso na atualização das configurações!<br>O novo ambiente de consulta é " . $vetorLegenda[$environment] . "!";
        fclose($newfile);
        $nome_arquivo = "environment.cpf.php";
        $arquivo = $raiz_do_projeto . "consulta_cpf/" . $nome_arquivo;
        if (SFTP_TRANSFER && file_exists($arquivo)) {
            $arq = trim(str_replace('/', '\\', $arquivo));

            //enviar para os servidores via sFTP
            $sftp = new SFTPConnection($server, $port);
            $sftp->login($user, $pass);
            $sftp->uploadFile($raiz_do_projeto . "consulta_cpf/" . $nome_arquivo, "E-Prepag/www/web/prepag2/consulta_cpf/" . $nome_arquivo);

            $msg .= "<br><br>Arquivo de configuração enviado ao servidor Windows 2003";

        }

    } else
        $msg = "Erro ao salvar as configurações contacte o Administrador imediatamente!";
} // end if($BtnSearch)
/*
    FIM CONTROLLER
 */
?>
<link href="<?php echo $url; ?>:<?php echo $server_port; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="<?php echo $url; ?>:<?php echo $server_port; ?>/js/global.js"></script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice -
                <?php echo $currentAba->getDescricao(); ?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a
                href="<?php echo $sistema->item->getLink(); ?>"><?php echo $sistema->item->getDescricao(); ?></a></li>
    </ol>
</div>

<div class="col-md-12 txt-preto">
    <form id="buscaBanner" name="buscaBanner" method="post">
        <div>
            <div class="text-left p5 left">
                <span class=" left col-md-3">Ambiente de Consulta de CPF do Site:</span>
                <span class="left col-md-3">
                    <select name="environment" id="environment" class="form-control right">
                        <?php
                        foreach ($vetorLegenda as $key => $value) {
                            ?>
                            <option value="<?php echo $key; ?>" <?php if ($environment == $key)
                                   echo "selected" ?>>
                                <?php echo $value; ?>
                            </option>
                            <?php
                        }//end foreach
                        ?>
                    </select>
                </span>
            </div>
            <div class="text-left p5 left">
                <span class=" left col-md-3">Ambiente de Consulta de CPF da API:</span>
                <span class="left col-md-3">
                    <select name="apiEnv" id="apiEnv" class="form-control right">
                        <?php
                        $con = ConnectionPDO::getConnection();
                        $pdo = $con->getLink();

                        // Consulta para obter os dados
                        $query = "SELECT codigo, parceiro_consulta, ativo FROM consulta_api WHERE integrado = 'S' ORDER BY codigo";
                        $stmt = $pdo->prepare($query);
                        $stmt->execute();
                        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        // Definir o primeiro código ativo como selecionado
                        $selectedCode = null;
                        foreach ($dados as $row) {
                            if ($row['ativo'] === 'A' && $selectedCode === null) {
                                $selectedCode = $row['codigo'];
                            }
                        }

                        // Gerar as opções do select
                        foreach ($dados as $row) {
                            $selected = ($row['codigo'] == $selectedCode) ? "selected" : "";
                            echo "<option value='" . $row['codigo'] . "' $selected >" . $row['parceiro_consulta'] . "</option>";
                        }
                        ?>
                    </select>
                </span>
            </div>
        </div>
        <div class="col-md-2 pull-right" style="margin-top: 10px;">
            <button type="submit" name="BtnSearch" value="Alterar" class="btn pull-right btn-success "
                onClick='return confirm("Deseja realmente alterar as configurações da consulta do CPF?");'>Alterar</button>
        </div>
    </form>
</div>
<div class="col-md-12 borda bloco bg-cinza-claro top20">
    <?php
    if (isset($msg))
        echo $msg;
    ?>
</div>
<div class="bloco row">
    <?php
    require_once $raiz_do_projeto . "backoffice/includes/rodape_bko.php";
    ?>
</div>
</body>

</html>