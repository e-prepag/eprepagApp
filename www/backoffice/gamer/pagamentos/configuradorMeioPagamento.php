<?php require_once __DIR__ . '/../../../includes/constantes_url.php'; ?>
<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . 'sftp/connect.php';
require_once $raiz_do_projeto . 'sftp/classSFTPconnection.php';
if (b_IsBKOUsuarioAdminMeiosPagamentos()) {
    /* 
    CONTROLLER
 */
    if (isset($BtnSearch) && $BtnSearch) {
        // Variável de OUTPUT
        $msg = '';

        try {
            $conexao = ConnectionPDO::getConnection()->getLink();

            $conexao->beginTransaction();

            // Array com todas as configurações a serem salvas
            $configuracoes = array(
                'PAGAMENTO_BRADESCO' => $conf_bradesco,
                'PAGAMENTO_BANCO_BRASIL' => $conf_banco_brasil,
                'PAGAMENTO_ITAU' => $conf_itau,
                'PAGAMENTO_BOLETO' => $conf_boleto,
                'BANCO_BOLETO' => $banc_boleto,
                'PAGAMENTO_EPREPAG_CASH' => $conf_eprepag_cash,
                'PAGAMENTO_CIELO' => $conf_cielo,
                'PAGAMENTO_PIX' => $conf_pix,
                'PAGAMENTO_PIX_PROVEDOR' => $conf_pix_provedor,
                'PAGAMENTO_PIX_PROVEDOR2' => $conf_pix_provedor2,
                'PAGAMENTO_PIX_CHAVEAMENTO' => $troca,
                'VALOR_TROCA' => $valor_troca
            );

            foreach ($configuracoes as $chave => $valor) {
                // Verifica se a chave já existe
                $sqlCheck = "SELECT id FROM configuracao_pagamentos WHERE chave = :chave";
                $queryCheck = $conexao->prepare($sqlCheck);
                $queryCheck->bindValue(":chave", $chave);
                $queryCheck->execute();
                $existe = $queryCheck->fetch(PDO::FETCH_ASSOC);

                if ($existe) {
                    // Se existe, atualiza
                    $sqlUpdate = "UPDATE configuracao_pagamentos 
                             SET valor = :valor, data_atualizacao = CURRENT_TIMESTAMP 
                             WHERE chave = :chave";
                    $queryUpdate = $conexao->prepare($sqlUpdate);
                    $queryUpdate->bindValue(":chave", $chave);
                    $queryUpdate->bindValue(":valor", $valor);
                    $queryUpdate->execute();
                } else {
                    // Se não existe, insere
                    $sqlInsert = "INSERT INTO configuracao_pagamentos (chave, valor) 
                             VALUES (:chave, :valor)";
                    $queryInsert = $conexao->prepare($sqlInsert);
                    $queryInsert->bindValue(":chave", $chave);
                    $queryInsert->bindValue(":valor", $valor);
                    $queryInsert->execute();
                }
            }

            // Atualiza o status dos provedores PIX
            $sqlUpdate = "UPDATE provedor_pix SET ativo = 'I'";
            $queryUpdate = $conexao->prepare($sqlUpdate);
            $queryUpdate->execute();

            $sqlAtiva = "UPDATE provedor_pix SET ativo = 'A' WHERE nome = :NOME";
            $queryAtiva = $conexao->prepare($sqlAtiva);
            $queryAtiva->bindValue(":NOME", $conf_pix_provedor);
            $queryAtiva->execute();

            $conexao->commit();

            $msg = "Configurações salvas com sucesso!";
        } catch (Exception $e) {
            // Em caso de erro, desfaz todas as alterações
            if ($conexao->inTransaction()) {
                $conexao->rollBack();
            }
            $msg = "Erro ao salvar as configurações: " . $e->getMessage();
        }
    } // end if($BtnSearch)
    /*
    FIM CONTROLLER
 */
    require_once($raiz_do_projeto . "includes/config.MeiosPagamentos.php");
    //Definindo Array Default no caso do include estar conrrompidom
    if (!is_array($vetorHabilita)) {
        //Vetor que possui o status ativado e desativado
        $vetorHabilita = array(
            '0' => 'Desativado',
            '1' => 'Ativado'
        );
    } //end if(!is_array($vetorHabilita))

    if (!isset($vetoropcao)) {
        $vetoropcao = array(
            'blupay' => 'Casa do Crédito',
            'cielo' => 'Cielo',
            'mercadopago' => 'Mercado Pago'
        );
    }

    if (!isset($vetortroca)) {
        $vetortroca = array(
            "a" => "Ativa",
            "i" => "Inativa"
        );
    }
    require_once "/www/includes/bourls.php";
?>
    <link href="https://<?php echo $server_url_complete; ?>/css/jquery-ui-1.9.2.custom.min.css" rel="stylesheet">
    <script src="https://<?php echo $server_url_complete; ?>/js/jquery-ui-1.9.2.custom.min.js"></script>
    <script src="https://<?php echo $server_url_complete; ?>/js/global.js"></script>
    <div class="col-md-12">
        <ol class="breadcrumb top10">
            <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao(); ?></a></li>
            <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
            <li class="active"><a href="<?php echo $sistema->item->getLink(); ?>"><?php echo $sistema->item->getDescricao(); ?></a></li>
        </ol>
    </div>
    <div class="col-md-12">
        <form id="buscaBanner" name="buscaBanner" method="post">
            <div class="text-left left row">
                <span class="p5 left col-md-2"><img src="<?= EPREPAG_URL_HTTPS ?>/imagens/pag/pagto_bradesco.gif" width="110" height="40" border="0"></span>
                <span class="p5 left col-md-3">
                    <select name="conf_bradesco" id="conf_bradesco" class="form-control right">
                        <?php
                        foreach ($vetorHabilita as $key => $value) {
                        ?>
                            <option value="<?php echo $key; ?>" <?php if (PAGAMENTO_BRADESCO == $key) echo "selected" ?>><?php echo $value; ?></option>
                        <?php
                        } //end foreach
                        ?>
                    </select>
                </span>
            </div>
            <div class="text-left left row top10">
                <span class="p5 left col-md-2"><img src="<?= EPREPAG_URL_HTTPS ?>/imagens/pag/pagto_bancodobrasil.gif" width="110" height="40" border="0"></span>
                <span class="p5 left col-md-3">
                    <select name="conf_banco_brasil" id="conf_banco_brasil" class="form-control right">
                        <?php
                        foreach ($vetorHabilita as $key => $value) {
                        ?>
                            <option value="<?php echo $key; ?>" <?php if (PAGAMENTO_BANCO_BRASIL == $key) echo "selected" ?>><?php echo $value; ?></option>
                        <?php
                        } //end foreach
                        ?>
                    </select>
                </span>
            </div>
            <div class="text-left left row top10">
                <span class="p5 left col-md-2"><img src="<?= EPREPAG_URL_HTTPS ?>/imagens/pag/pagto_itau_shopline.gif" width="110" height="40" border="0"></span>
                <span class="p5 left col-md-3">
                    <select name="conf_itau" id="conf_itau" class="form-control right">
                        <?php
                        foreach ($vetorHabilita as $key => $value) {
                        ?>
                            <option value="<?php echo $key; ?>" <?php if (PAGAMENTO_ITAU == $key) echo "selected" ?>><?php echo $value; ?></option>
                        <?php
                        } //end foreach
                        ?>
                    </select>
                </span>
            </div>
            <div class="text-left left row top10">
                <span class="p5 left col-md-2"><img src="<?= EPREPAG_URL_HTTPS ?>/imagens/pag/pagto_boleto.gif" width="110" height="40" border="0"></span>
                <span class="p5 left col-md-3">
                    <select name="conf_boleto" id="conf_boleto" class="form-control right">
                        <?php
                        foreach ($vetorHabilita as $key => $value) {
                        ?>
                            <option value="<?php echo $key; ?>" <?php if (PAGAMENTO_BOLETO == $key) echo "selected" ?>><?php echo $value; ?></option>
                        <?php
                        } //end foreach
                        ?>
                    </select>
                </span>
            </div>
            <div class="text-left left row top10">
                <span class="p5 left col-md-2"><label style="margin-top: 5px;font-size: 13px;font-weight: bolder;">EMISSOR BOLETO</label></span>
                <span class="p5 left col-md-3">
                    <select name="banc_boleto" id="banc_boleto" class="form-control right">
                        <?php
                        foreach ($vetoropcao_boleto as $key => $value) {
                        ?>
                            <option value="<?php echo $key; ?>" <?php if (BANCO_BOLETO == $key) echo "selected" ?>><?php echo $value; ?></option>
                        <?php
                        } //end foreach
                        ?>
                    </select>
                </span>
            </div>
            <div class="text-left left row top10">
                <span class="p5 left col-md-2"><img src="<?= EPREPAG_URL_HTTPS ?>/imagens/pag/pagto_eprepag.gif" width="110" height="40" border="0"></span>
                <span class="p5 left col-md-3">
                    <select name="conf_eprepag_cash" id="conf_eprepag_cash" class="form-control right">
                        <?php
                        foreach ($vetorHabilita as $key => $value) {
                        ?>
                            <option value="<?php echo $key; ?>" <?php if (PAGAMENTO_EPREPAG_CASH == $key) echo "selected" ?>><?php echo $value; ?></option>
                        <?php
                        } //end foreach
                        ?>
                    </select>
                </span>
            </div>
            <div class="text-left left row top10">
                <span class="p5 left col-md-2"><img src="<?= EPREPAG_URL_HTTPS ?>/imagens/pag/pagto_cielo.gif" width="110" height="40" border="0"></span>
                <span class="p5 left col-md-3">
                    <select name="conf_cielo" id="conf_cielo" class="form-control right">
                        <?php
                        foreach ($vetorHabilita as $key => $value) {
                        ?>
                            <option value="<?php echo $key; ?>" <?php if (PAGAMENTO_CIELO == $key) echo "selected" ?>><?php echo $value; ?></option>
                        <?php
                        } //end foreach
                        ?>
                    </select>
                </span>
            </div>
            <div style="border: 1px solid #eee; padding: 10px;" class="text-left left row top10">
                <span class="p5 left col-md-2"><img src="<?= EPREPAG_URL_HTTPS ?>/imagens/pag/pagto_forma_pix.gif" width="110" height="40" border="0"></span>
                <span class="p5 left col-md-3">
                    <select name="conf_pix" id="conf_pix" class="form-control right">
                        <?php
                        foreach ($vetorHabilita as $key => $value) {
                        ?>
                            <option value="<?php echo $key; ?>" <?php if (PAGAMENTO_PIX == $key) echo "selected" ?>><?php echo $value; ?></option>
                        <?php
                        } //end foreach
                        ?>
                    </select>
                    <label style="margin: 10px;display: flex;font-size: 13px;align-items: center;">
                        Troca automatica de valores
                        <img id="icone-obs" style="width: 33px;
							height: 33px;
							margin-left: 5px;
							cursor: pointer;
							background-color: #eee;
							padding: 10px;
							border-radius: 10px;
							"
                            src="<?= EPREPAG_URL_HTTPS ?>/css/images/icone-interrogacao.png" />
                        <div style="position: absolute;
							width: 350px;
							background-color: rgba(0,0,0,.8);
							left: 270px;
							padding: 10px;
							border-radius: 10px;
							top: 20px;
							font-weight: 500;
							display: none;
							color: white;"
                            id="obs">
                            Funcionamento: <br><br>
                            A <span style="color: red;">troca automática de valores</span> é um mecanismo que verifica na hora da venda o valor total do pedido.
                            Caso o valor seja maior que o valor de troca, a transação é realizada pelo segundo provedor, caso contrario é realizado pelo provedor padrão.<br><br>

                            Observação: <br><br>
                            Caso a troca automática de valores estiver desativada, o sistema considerara o Provedor pix padrão selecionado.
                        </div>
                    </label>
                    <select name="troca" id="troca" class="form-control right">
                        <?php
                        foreach ($vetortroca as $key => $value) {
                        ?>
                            <option value="<?php echo $key; ?>" <?php if (PAGAMENTO_PIX_CHAVEAMENTO == $key) echo "selected" ?>><?php echo $value; ?></option>
                        <?php
                        } //end foreach
                        ?>
                    </select>
                    <label style="margin: 10px;font-size: 13px;">Provedor de serviço PIX padrão</label>
                    <select name="conf_pix_provedor" id="conf_pix_provedor" class="form-control right">
                        <?php
                        foreach ($vetoropcao as $key => $value) {
                        ?>
                            <option value="<?php echo $key; ?>" <?php if (PAGAMENTO_PIX_PROVEDOR == $key) echo "selected" ?>><?php echo $value; ?></option>
                        <?php
                        } //end foreach
                        ?>
                    </select>
                    <label style="margin: 10px;font-size: 13px;">Provedor PIX para valores acima da margem de troca</label>
                    <select name="conf_pix_provedor2" id="conf_pix_provedor2" class="form-control right">
                        <?php
                        foreach ($vetoropcao as $key => $value) {
                        ?>
                            <option value="<?php echo $key; ?>" <?php if (PAGAMENTO_PIX_PROVEDOR2 == $key) echo "selected" ?>><?php echo $value; ?></option>
                        <?php
                        } //end foreach
                        ?>
                    </select>
                    <label style="margin: 10px;font-size: 13px;">Valor de troca</label>
                    <input name="valor_troca" id="valor_troca" class="form-control right" type="number" value="<?= VALOR_TROCA ?>" />
                </span>
            </div>
            <div class="col-md-2 pull-right top10">
                <button type="submit" name="BtnSearch" value="Alterar" class="btn pull-right btn-success " onClick='return confirm("Deseja realmente alterar as configurações dos Meios de Pagamentos?");'>Alterar</button>
            </div>
        </form>
    </div>
    <?php
    if (isset($msg)) echo '<div class="col-md-12 borda bloco bg-cinza-claro top20">' . $msg . '</div>';
    ?>
    <div class="bloco row">
    <?php
} //end if(b_IsBKOUsuarioAdminMeiosPagamentos())
else {
    echo "Seu usuário não possui acesso a este programa";
} //end else do if(b_IsBKOUsuarioAdminMeiosPagamentos())
require_once $raiz_do_projeto . "backoffice/includes/rodape_bko.php";
    ?>
    </div>
    <script>
        $(document).ready(() => {

            $("#icone-obs").on("click", (event) => {
                $("#obs").toggle();
            });
        });
    </script>
    </body>

    </html>