<?php
  // ini_set('display_errors', 1);
  // ini_set('display_startup_errors', 1);
  // error_reporting(E_ALL);

require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/main.php";
require_once $raiz_do_projeto . "includes/pdv/main.php";
require_once $raiz_do_projeto . "includes/functions_pagto_reduced.php";
require_once "/www/includes/bourls.php";


if (!function_exists('isVendaDeposito')) {

  function isVendaDeposito($venda_id)
  {

    $msg = "";

    $sql = "select vg_deposito_em_saldo from tb_dist_venda_games vg where vg.vg_id = " . $venda_id;
    $rs_venda = SQLexecuteQuery($sql);
    if (!$rs_venda || pg_num_rows($rs_venda) == 0)
      $msg = "Nenhuma venda encontrada (em isvendaDeposito($venda_id)).\n";

    if ($msg == "") {
      $rs_venda_row = pg_fetch_array($rs_venda);
      $vg_deposito_em_saldo = $rs_venda_row['vg_deposito_em_saldo'];
    }
    $vg_deposito_em_saldo = (($vg_deposito_em_saldo == 1) ? 1 : 0);
    return $vg_deposito_em_saldo;
  }

}

set_time_limit(3000);

$time_start_stats = getmicrotime();

$bDebug = false;
if ($bDebug) {
  echo "Elapsed time (A0): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . "<br>";
  $time_start_stats = getmicrotime();
}

$msg = "";

if (!$venda_id)
  $msg = "Código da venda não fornecido.\n";
elseif (!is_numeric($venda_id))
  $msg = "Código da venda inválido.\n";

if ($msg == "") {
  $isVendaDeposito = isVendaDeposito($venda_id);
}

$vgm_pin_codinterno_tmp = "";
if (!($isVendaDeposito == 1)) {
  //Testa vgm_pin_codinterno = null, caso contrário quer dizer que já houve uma venda (mesmo que o status mostre outra coisa)
  $vgm_pin_codinterno_tmp = get_pins_vendidos($venda_id);
  if (trim($vgm_pin_codinterno_tmp) != "")
    $msgConcilia = "Esta venda já tem PINs vendidos (PINs ID: '$vgm_pin_codinterno_tmp').\n";
}

//Processa acoes
//----------------------------------------------------------------------------------------------------------
if ($msg == "") {

  if (!empty($vgm_id)) {
    $sql = "update tb_dist_venda_games_modelo set vgm_nome_cpf='" . trim($vgm_nome_cpf) . "',vgm_cpf='" . trim($vgm_cpf[$vgm_id]) . "',vgm_cpf_data_nascimento='" . formata_data(trim($vgm_cpf_data_nascimento[$vgm_id]), 1) . " 00:00:00' where ";
    if ($todos == 1)
      $sql .= "vgm_vg_id = " . $venda_id . " ;";
    else
      $sql .= "vgm_id = " . $vgm_id . " ;";
    //die($sql);
    $rs_cpf = SQLexecuteQuery($sql);
    if (pg_affected_rows($rs_cpf) < 1)
      $msg = "Erro ao atualizar os Dados de CPF!";
    unset($vgm_nome_cpf);
    unset($vgm_cpf);
    unset($vgm_cpf_data_nascimento);
  }//end if(!empty($vgm_id))

  if ($BtnCancelar) {
    //atualiza status
    if ($msg == "") {
      $parametros['usuario_obs'] = $usuario_obs;
      $parametros['ultimo_status_obs'] = $ultimo_status_obs;
      $msgConciliaUsuario = cancelaVendaGames($venda_id, $parametros);
    }

  }

  if ($BtnProcessa) {
    //Associa pins, gera venda e credita saldo
    if ($msgConcilia == "") {

      // se não tem modelo -> é depósito em conta
      $sql = "select * from tb_dist_venda_games vg inner join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id where vg.vg_id = " . $venda_id . " ";
      $sql = "select * from tb_dist_venda_games vg where vg.vg_id = " . $venda_id . " ";
      $rs = SQLexecuteQuery($sql);
      if (!$rs || pg_num_rows($rs) == 0) {
        echo "Venda '$venda_id' não foi encontrada<br>";
      } else {
        $rs_vg = pg_fetch_array($rs);
        $vg_deposito_em_saldo = $rs_vg['vg_deposito_em_saldo'];
        $vg_pagto_tipo = $rs_vg['vg_pagto_tipo'];

        if ($vg_deposito_em_saldo == 1) {
          $msgVenda = "É depósito no Saldo da Lan.\n";
          echo $msgVenda . "<br>";

          if ($vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO']) {
            echo "processa '$venda_id' (Pagto Boleto)<br>";
            $raiz_do_projeto = "C:\\Sites\\E-Prepag";

            $ret = conciliacaoAutomaticaBoletoExpressMoneyLH($venda_id);
          } else {
            require_once $raiz_do_projeto . "includes/inc_Pagamentos.php";
            require_once $raiz_do_projeto . "includes/pdv/functions_vendaGames_pag_online.php";

            // Vai processsar
            echo "processa '$venda_id' (Pagto Online)<br>";
            echo conciliacaoAutomaticaPagtoOnlineExpressMoneyLH($venda_id);
          }

        } else {
          // É processamento normal
          $msgConciliaUsuario = "Agendamento: Agendado com sucesso.\n";
          echo $msgConciliaUsuario . "<br>";

          $parametros['showProgress'] = true;
          $parametros['ultimo_status_obs'] = $ultimo_status_obs;
          $msgConcilia = processaVendaGames($venda_id, 1, $parametros);
          if ($msgConcilia == "")
            $msgConciliaUsuario .= "Processamento: Processado com sucesso.\n";
          else
            $msgConciliaUsuario .= "Processamento: " . $msgConcilia;

          //Ativa o processamento de envio de email da venda
          if ($msgConcilia == "")
            $BtnProcessaEmail = 1;
        }
      }
    }
  }

  if ($BtnProcessaEmail) {
    //envia email para o cliente
    if ($msgConcilia == "") {
      $parametros['ultimo_status_obs'] = $ultimo_status_obs;
      $msgConcilia = processaEmailVendaGames($venda_id, $parametros);
      if ($msgConcilia == "")
        $msgConciliaUsuario .= "Envio de email: Enviado com sucesso.\n";
      else
        $msgConciliaUsuario .= "Envio de email: " . $msgConcilia;
    }
  }

  if ($BtnReprocessaPins) {
    require_once "/www/includes/gamer/AES.class.php";
    require_once "/www/class/classGeraPin.php";
    try {
      $pdo = ConnectionPDO::getConnection()->getLink();

      $query1 = "SELECT vgm_id, vgm_vg_id, vgm_pin_valor, vgm_opr_codigo, vgm_ogp_id, vgm_qtde FROM tb_dist_venda_games_modelo WHERE vgm_vg_id = ?";
      $stmt1 = $pdo->prepare($query1);
      $stmt1->execute([$venda_id]);
      $vendas = $stmt1->fetchAll(PDO::FETCH_ASSOC);

      foreach ($vendas as $venda) {
        $vgm_id = $venda['vgm_id'];
        $vgm_pin_valor = $venda['vgm_pin_valor'];
        $vgm_opr_codigo = $venda['vgm_opr_codigo'];
        $vgm_ogp_id = $venda['vgm_ogp_id'];
        $vgm_qtde = $venda['vgm_qtde'];

        // Verifica se a quantidade de registros é igual ou maior que o necessário
        $queryCheck = "SELECT COUNT(*) FROM tb_dist_venda_games_modelo_pins WHERE vgmp_vgm_id = ?";
        $stmtCheck = $pdo->prepare($queryCheck);
        $stmtCheck->execute([$vgm_id]);
        $qtdeRegistrada = $stmtCheck->fetchColumn();

        if ($qtdeRegistrada >= $vgm_qtde) {
          continue;
        }

        for ($i = 0; $i < ($vgm_qtde - $qtdeRegistrada); $i++) {
          // Passo 2: Buscar o pin dispon?vel na tabela pins
          $pin_codinterno = null;

          if ($vgm_ogp_id == 488 && $vgm_opr_codigo == 53) {
            $geraPinEpp = new GeraPinVariavel($vgm_pin_valor, 53, 3, 1);

            $pin_codinterno = $geraPinEpp->gerar();
          } else {
            $query2 = "
                            SELECT p.pin_codinterno 
                            FROM pins p 
                            LEFT JOIN tb_dist_venda_games_modelo_pins vgp ON p.pin_codinterno = vgp.vgmp_pin_codinterno 
                            WHERE p.opr_codigo = ? AND p.pin_valor = ? 
                            AND vgp.vgmp_pin_codinterno IS NULL 
                            AND p.pin_status = '1'
                            LIMIT 1";
            $stmt2 = $pdo->prepare($query2);
            $stmt2->execute([$vgm_opr_codigo, $vgm_pin_valor]);
            $pin = $stmt2->fetch(PDO::FETCH_ASSOC);
            if ($pin) {
              $pin_codinterno = $pin['pin_codinterno'];
            }
          }

          if ($pin_codinterno) {

            // Iniciar a transaction
            $pdo->beginTransaction();

            try {
              // Passo 3: Inserir na tabela tb_dist_venda_games_modelo_pins
              $query3 = "INSERT INTO tb_dist_venda_games_modelo_pins (vgmp_vgm_id, vgmp_pin_codinterno) VALUES (?, ?)";
              $stmt3 = $pdo->prepare($query3);
              $stmt3->execute([$vgm_id, $pin_codinterno]);

              // Passo 4: Atualizar o status do PIN para 6
              $validade_interval = ($vgm_opr_codigo == 166) ? "INTERVAL '60 days'" : "INTERVAL '6 months'";
              $query5 = "UPDATE pins SET pin_status = 6, pin_validade = CURRENT_DATE + $validade_interval WHERE pin_codinterno = ?";
              $stmt5 = $pdo->prepare($query5);
              $stmt5->execute([$pin_codinterno]);

              // Passo 5: Inserir na tabela pins_dist
              $query4 = "INSERT INTO pins_dist SELECT * FROM pins WHERE pin_codinterno = ?";
              $stmt4 = $pdo->prepare($query4);
              $stmt4->execute([$pin_codinterno]);

              // Commit da transaction
              $pdo->commit();

            } catch (Exception $e) {
              $pdo->rollBack();
              echo "Erro ao gerar pins novamente.\n";
            }
          } else {
            echo "Nenhum PIN disponivel para vg_id: {$venda['vgm_vg_id']}.\n";
          }
        }
      }
    } catch (PDOException $e) {
      echo "Erro de conexao: " . $e->getMessage();
    }
  }

}

//Mostra a pagina
//----------------------------------------------------------------------------------------------------------
//Recupera a venda
if ($msg == "") {
  $sql = "select * from tb_dist_venda_games vg " .
    "where vg.vg_id = " . $venda_id;
  $rs_venda = SQLexecuteQuery($sql);
  if (!$rs_venda || pg_num_rows($rs_venda) == 0)
    $msg = "Nenhuma venda encontrada.\n";
  $rs_venda_row = pg_fetch_array($rs_venda);
  $vg_ug_id = $rs_venda_row['vg_ug_id'];
  $vg_ultimo_status = $rs_venda_row['vg_ultimo_status'];
  $vg_ultimo_status_obs = $rs_venda_row['vg_ultimo_status_obs'];
  $vg_usuario_obs = $rs_venda_row['vg_usuario_obs'];
  $vg_pagto_tipo = $rs_venda_row['vg_pagto_tipo'];
  $vg_data_inclusao = $rs_venda_row['vg_data_inclusao'];
  $vg_pagto_data_inclusao = $rs_venda_row['vg_pagto_data_inclusao'];
  $vg_pagto_data = $rs_venda_row['vg_pagto_data'];
  $vg_pagto_banco = $rs_venda_row['vg_pagto_banco'];
  $vg_pagto_local = $rs_venda_row['vg_pagto_local'];
  $vg_pagto_valor_pago = $rs_venda_row['vg_pagto_valor_pago'];
  $vg_pagto_num_docto = $rs_venda_row['vg_pagto_num_docto'];
  $vg_concilia = $rs_venda_row['vg_concilia'];
  $vg_data_concilia = $rs_venda_row['vg_data_concilia'];
  $vg_user_id_concilia = trim($rs_venda_row['vg_user_id_concilia']);
  $vg_dep_codigo = $rs_venda_row['vg_dep_codigo'];
  $vg_bol_codigo = $rs_venda_row['vg_bol_codigo'];

  $pagto_num_docto = preg_split("/\|/", $vg_pagto_num_docto);
}
if ($bDebug)
  echo "Elapsed time (A1): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . "<br>";

//Recupera modelos
if ($msg == "") {
  $sql = "select * from tb_dist_venda_games vg " .
    "left outer join tb_dist_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id " .	// inner join
    "where vg.vg_id = " . $venda_id .
    " order by vgm_id";
  $rs_venda_modelos = SQLexecuteQuery($sql);
  if (!$rs_venda_modelos || pg_num_rows($rs_venda_modelos) == 0)
    $msg = "Nenhum produto encontrado (ABCD).\n";
  else {
    $total_geral = 0;
    $qtde_itens = 0;
    $qtde_produtos = 0;
    $vgm_pin_codinterno = "";
    while ($rs_venda_modelos_row = pg_fetch_array($rs_venda_modelos)) {
      $qtde = $rs_venda_modelos_row['vgm_qtde'];
      $valor = $rs_venda_modelos_row['vgm_valor'];
      $perc_desconto = $rs_venda_modelos_row['vgm_perc_desconto'];
      $vgm_pin_codinterno .= $rs_venda_modelos_row['vgm_pin_codinterno'];

      //Dados de CPF informado para o pedido
      if (!empty($rs_venda_modelos_row['vgm_cpf'])) {
        $vgm_nome_cpf[$rs_venda_modelos_row['vgm_id']] = $rs_venda_modelos_row['vgm_nome_cpf'];
        $vgm_cpf[$rs_venda_modelos_row['vgm_id']] = $rs_venda_modelos_row['vgm_cpf'];
        $vgm_cpf_data_nascimento[$rs_venda_modelos_row['vgm_id']] = $rs_venda_modelos_row['vgm_cpf_data_nascimento'];
        $vgm_descricao_modelo[$rs_venda_modelos_row['vgm_id']] = $rs_venda_modelos_row['vgm_nome_produto'] . ($rs_venda_modelos_row['vgm_nome_modelo'] != "" ? $rs_venda_modelos_row['vgm_nome_modelo'] : "");
      } //end if(!empty($rs_venda_modelos_row['vgm_cpf']))

      $qtde_itens += $qtde;
      $qtde_produtos += 1;
      $geral = $valor * $qtde;
      $desconto = $geral * $perc_desconto / 100;
      $repasse = $geral - $desconto;
      $qtde_total += $qtde;
      $total_geral += $geral;
      $total_desconto += $desconto;
      $total_repasse += $repasse;
    }
    $vgm_pin_codinterno = str_replace(",", ", ", $vgm_pin_codinterno);
    pg_result_seek($rs_venda_modelos, 0);
  }
}
if ($bDebug)
  echo "Elapsed time (A2): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . "<br>";

//Recupera historico da venda
if ($msg == "") {
  $sql = "select * from tb_dist_venda_games_historico vgh 
                         where vgh.vgh_vg_id = " . $venda_id . "
                         order by vgh_data_inclusao desc";
  if ($bDebug)
    echo "SQL historico: " . $sql . "<br>";
  $rs_venda_hist = SQLexecuteQuery($sql);
}
if ($bDebug)
  echo "Elapsed time (A3): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . "<br>";

//Recupera dados do usuario
if ($msg == "") {
  $sql = "select * from dist_usuarios_games ug " .
    "where ug.ug_id = " . $vg_ug_id;
  $rs_usuario = SQLexecuteQuery($sql);
  if (!$rs_usuario || pg_num_rows($rs_usuario) == 0)
    $msg = "Nenhum cliente encontrado.\n";
  else {
    $rs_usuario_row = pg_fetch_array($rs_usuario);
    $ug_login = $rs_usuario_row['ug_login'];

    $ug_responsavel = $rs_usuario_row['ug_responsavel'];
    $ug_email = $rs_usuario_row['ug_email'];
    $ug_nome_fantasia = $rs_usuario_row['ug_nome_fantasia'];
    $ug_cnpj = $rs_usuario_row['ug_cnpj'];

    $ug_cidade = $rs_usuario_row['ug_cidade'];
    $ug_estado = $rs_usuario_row['ug_estado'];
    $ug_tel_ddi = $rs_usuario_row['ug_tel_ddi'];
    $ug_tel_ddd = $rs_usuario_row['ug_tel_ddd'];
    $ug_tel = $rs_usuario_row['ug_tel'];

    $ug_tipo_cadastro = $rs_usuario_row['ug_tipo_cadastro'];
    $ug_nome = $rs_usuario_row['ug_nome'];
    $ug_cpf = $rs_usuario_row['ug_cpf'];
    $ug_rg = $rs_usuario_row['ug_rg'];

  }
}

//Recupera dados da forma de pagamento
if ($msg == "") {
  if ($vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO']) {
    $sql = "select * from dist_boleto_bancario_games bbg " .
      "where bbg.bbg_vg_id = " . $venda_id;
    $rs_boleto = SQLexecuteQuery($sql);
    if (!$rs_boleto || pg_num_rows($rs_boleto) == 0)
      $msg = "Nenhum boleto encontrado.\n";
    else {
      $rs_boleto_row = pg_fetch_array($rs_boleto);
      $bbg_boleto_codigo = $rs_boleto_row['bbg_boleto_codigo'];
      $bbg_data_inclusao = $rs_boleto_row['bbg_data_inclusao'];
      $bbg_bco_codigo = $rs_boleto_row['bbg_bco_codigo'];
      $bbg_documento = $rs_boleto_row['bbg_documento'];
      $bbg_valor = $rs_boleto_row['bbg_valor'];
      $bbg_valor_taxa = $rs_boleto_row['bbg_valor_taxa'];
      $bbg_data_venc = $rs_boleto_row['bbg_data_venc'];
      $bbg_data_pago = $rs_boleto_row['bbg_data_pago'];
      $bbg_pago = $rs_boleto_row['bbg_pago'];
    }

  } elseif ($vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['REDECARD_MASTERCARD'] || $vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['REDECARD_DINERS']) {
    $sql = "select * from tb_venda_games_redecard vgrc " .
      "where vgrc.vgrc_vg_id = " . $venda_id;
    $rs_redecard = SQLexecuteQuery($sql);
    if (!$rs_redecard || pg_num_rows($rs_redecard) == 0)
      $msg = "Nenhum redecard encontrado.\n";
    else {
      $rs_redecard_row = pg_fetch_array($rs_redecard);
      $vgrc_id = $rs_redecard_row['vgrc_id'];
      $vgrc_vg_id = $rs_redecard_row['vgrc_vg_id'];
      $vgrc_ug_id = $rs_redecard_row['vgrc_ug_id'];
      $vgrc_parcelas = $rs_redecard_row['vgrc_parcelas'];
      $vgrc_data_inclusao = $rs_redecard_row['vgrc_data_inclusao'];
      $vgrc_total = $rs_redecard_row['vgrc_total'];
      $vgrc_transacao = $rs_redecard_row['vgrc_transacao'];
      $vgrc_bandeira = $rs_redecard_row['vgrc_bandeira'];
      $vgrc_codver = $rs_redecard_row['vgrc_codver'];
      $vgrc_data_envio1 = $rs_redecard_row['vgrc_data_envio1'];
      $vgrc_ret2_data = $rs_redecard_row['vgrc_ret2_data'];
      $vgrc_ret2_nr_cartao = $rs_redecard_row['vgrc_ret2_nr_cartao'];
      $vgrc_ret2_origem_bin = $rs_redecard_row['vgrc_ret2_origem_bin'];
      $vgrc_ret2_numautor = $rs_redecard_row['vgrc_ret2_numautor'];
      $vgrc_ret2_numcv = $rs_redecard_row['vgrc_ret2_numcv'];
      $vgrc_ret2_numautent = $rs_redecard_row['vgrc_ret2_numautent'];
      $vgrc_ret2_numsqn = $rs_redecard_row['vgrc_ret2_numsqn'];
      $vgrc_ret2_codret = $rs_redecard_row['vgrc_ret2_codret'];
      $vgrc_ret2_msgret = $rs_redecard_row['vgrc_ret2_msgret'];
      $vgrc_ret4_ret = $rs_redecard_row['vgrc_ret4_ret'];
      $vgrc_ret4_codret = $rs_redecard_row['vgrc_ret4_codret'];
      $vgrc_ret4_msgret = $rs_redecard_row['vgrc_ret4_msgret'];
      $vgrc_usuario_ip = $rs_redecard_row['vgrc_usuario_ip'];
      $vgrc_ret2_endereco = $rs_redecard_row['vgrc_ret2_endereco'];
      $vgrc_ret2_numero = $rs_redecard_row['vgrc_ret2_numero'];
      $vgrc_ret2_complemento = $rs_redecard_row['vgrc_ret2_complemento'];
      $vgrc_ret2_cep = $rs_redecard_row['vgrc_ret2_cep'];
      $vgrc_ret2_respavs = $rs_redecard_row['vgrc_ret2_respavs'];
      $vgrc_ret2_msgavs = $rs_redecard_row['vgrc_ret2_msgavs'];

      $vgrc_ret2_numprg = $rs_redecard_row['vgrc_ret2_numprg'];
      $vgrc_ret2_nr_hash_cartao = $rs_redecard_row['vgrc_ret2_nr_hash_cartao'];
      $vgrc_ret2_cod_banco = $rs_redecard_row['vgrc_ret2_cod_banco'];
    }

  }
}

if ($bDebug)
  echo "Elapsed time (A4): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . "<br>";

//Se conciliado, Recupera dados do usuario que conciliou
if ($msg == "") {

  if ($vg_concilia == 1) {

    if ($vg_user_id_concilia == "") {
      $shn_nome = "Anonymous";
    } else {
      $sql = "select * from usuarios urpp " .
        "where urpp.id = '" . $vg_user_id_concilia . "'";
      $rs_urpp = SQLexecuteQuery($sql);
      if (!$rs_urpp || pg_num_rows($rs_urpp) == 0) {
        $shn_nome = "Anonymous";
      } else {
        $rs_urpp_row = pg_fetch_array($rs_urpp);
        $shn_nome = $rs_urpp_row['shn_nome'];
      }
    }
  }
}
if ($bDebug)
  echo "Elapsed time (A5): " . number_format(getmicrotime() - $time_start_stats, 2, '.', '.') . "<br>";

$msg = $msgConciliaUsuario . $msg;

$cor1 = "#F5F5FB";
$cor2 = "#F5F5FB";
$cor3 = "#FFFFFF";


ob_end_flush();
?>
<script type="text/javascript" src="/js/jquery.mask.min.js"></script>
<script src="/js/formataNome.js"></script>
<script language="javascript">
  $(".cpf").mask("999.999.999-99");
  $(".data_nasci").mask("99/99/9999");
  $(document).ready(function () {
    document.getElementById("vgm_cpf[1]").style.visibility = 'hidden';
    document.getElementById("vgm_cpf_data_nascimento[1]").style.visibility = 'hidden';
    document.getElementById("botao[1]").style.visibility = 'hidden';
  });

  function yesnoCheck() {
    if (document.getElementById('todos').checked) {
      document.getElementById("vgm_cpf[1]").style.visibility = 'visible';
      document.getElementById("vgm_cpf_data_nascimento[1]").style.visibility = 'visible';
      document.getElementById("botao[1]").style.visibility = 'visible';
    } else {
      document.getElementById("vgm_cpf[1]").style.visibility = 'hidden';
      document.getElementById("vgm_cpf_data_nascimento[1]").style.visibility = 'hidden';
      document.getElementById("botao[1]").style.visibility = 'hidden';
    }
  }

  var searching = false;
  function salvaDadosCPF(vgm_id) {
    /*
    Precisa chamar o mesmo ajax que está na alteração de cadastro de gamer q atualiza o nome no hidden $("#vgm_nome_cpf").val(); e altera $("#vgm_id").val(vgm_id); e submit o form.
    */
    if (vgm_id != 1) {
      document.getElementById('todos').checked = false;
      yesnoCheck();
    }

    if ($("input[name='vgm_cpf[" + vgm_id + "]']").val().trim().length === 14 && $("input[name='vgm_cpf_data_nascimento[" + vgm_id + "]']").val().trim().length === 10 && !searching) {
      $.ajax({
        type: "POST",
        url: "/ajax/ajaxCpf.php",
        dataType: "json",
        data: { cpf: $("input[name='vgm_cpf[" + vgm_id + "]']").val().trim(), dataNascimento: $("input[name='vgm_cpf_data_nascimento[" + vgm_id + "]']").val().trim() },
        beforeSend: function () {
          searching = true;
          $("span[name='loading[" + vgm_id + "]']").removeClass("hidden");
          $("span[name='loading[" + vgm_id + "]']").html("<img src='/images/ajax-loader.gif' width='30' height='30' title='Consultando CPF...'>");
        },
        success: function (txt) {
          searching = false;
          if (txt.erros.length > 0) {
            $("span[name='loading[" + vgm_id + "]']").addClass("hidden");
            alert(txt.erros);
          } else {
            var nome_cpf = fix_name_js(txt.nome.substr(0, 480));
            $("#vgm_nome_cpf").val(nome_cpf);
            $("span[name='loading[" + vgm_id + "]']").addClass("hidden");
            $("#vgm_id").val(vgm_id);
            $("#form5").submit();
          }
        },
        error: function (x, y) {
          $("span[name='loading[" + vgm_id + "]']").addClass("hidden");
          searching = false;
          return false;
        }
      });
    }
    else {
      alert('Preencha os campos corretamente!');
      document.getElementById("vgm_cpf[" + vgm_id + "]").select();
    }

  }
  function GP_popupAlertMsg(msg) { //v1.0
    document.MM_returnValue = alert(msg);
  }
  function GP_popupConfirmMsg(msg) { //v1.0
    document.MM_returnValue = confirm(msg);
  }
</script>
<div class="col-md-12">
  <ol class="breadcrumb top10">
    <li><a href="#" class="muda-aba" ordem="2">BackOffice - Lan Houses</a></li>
    <li><a href="com_pesquisa_vendas.php">Pesquisa de vendas</a></li>
    <li class="active">Detalhe de Venda</li>
  </ol>
</div>
<table class="table txt-preto fontsize-p">
  <tr>
    <td>
      <?php if ($msg != "") { ?>
        <table class="table txt-vermelho fontsize-p">
          <tr>
            <td align="center">
              <font color="#FF0000" size="2" face="Arial, Helvetica, sans-serif">
                <?php echo str_replace("\n", "<br>", $msg) ?></font>
            </td>
          </tr>
        </table>
      <?php } ?>

      <table class="table txt-preto">
        <tr bgcolor="#FFFFFF">
          <td colspan="2" bgcolor="#ECE9D8">Venda</font>
          </td>
        </tr>
        <tr bgcolor="#F5F5FB">
          <td width="150"><b>C&oacute;digo</b></td>
          <td><?php echo $venda_id ?></td>
        </tr>
        <tr bgcolor="#F5F5FB">
          <td><b>Data</b></td>
          <td><?php echo formata_data_ts($vg_data_inclusao, 0, true, true) ?></td>
        </tr>
        <tr bgcolor="#F5F5FB">
          <td><b>Valor</b></td>
          <td>
            <?php echo number_format($total_geral, 2, ',', '.') ?>
          </td>
        </tr>
        <tr bgcolor="#F5F5FB">
          <td><b>Repasse</b></td>
          <td>
            <?php echo number_format($total_repasse, 2, ',', '.') ?>
          </td>
        </tr>
        <tr bgcolor="#F5F5FB">
          <td><b>Qtde Produtos</b></td>
          <td><?php echo number_format($qtde_produtos, 0, '', '.') ?></td>
        </tr>
        <tr bgcolor="#F5F5FB">
          <td><b>Qtde Itens</b></td>
          <td><?php echo number_format($qtde_itens, 0, '', '.') ?></td>
        </tr>
        <tr bgcolor="#F5F5FB">
          <td><b>Status</b></td>
          <?php if ($vg_ultimo_status == $GLOBALS['STATUS_VENDA']['VENDA_CANCELADA']) { ?>
            <td title="<?php echo "vg_ultimo_status: '" . $vg_ultimo_status . "'"; ?>">
              <font color="FF0000"><?php echo $GLOBALS['STATUS_VENDA_DESCRICAO'][$vg_ultimo_status] ?></font>
            </td>
          <?php } else { ?>
            <td title="<?php echo "vg_ultimo_status: '" . $vg_ultimo_status . "'"; ?>">
              <?php echo $GLOBALS['STATUS_VENDA_DESCRICAO'][$vg_ultimo_status] ?></td>
          <?php } ?>
        </tr>
        <tr bgcolor="#F5F5FB">
          <td><b>PINs vendidos (IDs)</b></td>
          <td><?php
          echo $vgm_pin_codinterno_tmp . " (";

          if ($sem_pins) {
            echo '<font color="#FF0000">ATENÇÃO: Essa venda teve erro ao gerar PINs, <form name="formPins" id="formPins" method="post" action=""><input type="submit" name="BtnReprocessaPins" value="Gerar PINs novamente." onclick="return GP_popupConfirmMsg("Os PINs serão reprocessados. Deseja continuar?");"></form></font>';
          } else if (trim($vgm_pin_codinterno_tmp) == "") {
            echo "<font color='#0000FF'>Sem PINs vendidos</font>";
          } else {
            echo "<font color='#FF0000'>ATENÇÃO: Já tem PINs vendidos</font>";
          }

          echo ")";

          ?></td>
        </tr>
        <tr bgcolor="#F5F5FB">
          <td><b>PINs</b></td>
          <td>
            <?php
            if (!empty($vgm_pin_codinterno_tmp)) {
              ?>
              <table class="table txt-preto fontsize-pp">
                <tr bgcolor="F0F0F0" class="texto">
                  <td align="center"><b>Valor do PIN</b></td>
                  <td align="center"><b>PIN cod_interno</b></td>
                  <td align="center"><b>No Série</b></td>
                  <td align="center"><b>PIN</b></td>
                </tr>
                <?php
                $sql = "select pin_valor,pin_codinterno,pin_serial,CASE WHEN pin_caracter IS NULL THEN pin_codigo ELSE pin_caracter END as case_serial from pins where pin_codinterno IN ($vgm_pin_codinterno_tmp) order by pin_valor desc;";
                $rs_pins = SQLexecuteQuery($sql);
                while ($rs_pins_row = pg_fetch_array($rs_pins)) {
                  ?>
                  <tr bgcolor="F0F0F0" class="texto">
                    <td align="center">R$ <?php echo number_format($rs_pins_row['pin_valor'], 2, ',', '.'); ?></td>
                    <td align="center"><?php echo $rs_pins_row['pin_codinterno']; ?></td>
                    <td align="center"><?php echo $rs_pins_row['pin_serial']; ?></td>
                    <td align="center"><?php echo $rs_pins_row['case_serial']; ?></td>
                  </tr>
                <?php
                }//end while
            }//end if(!empty($vgm_pin_codinterno_tmp))
            ?>
            </table>
          </td>
        </tr>
        <tr bgcolor="#F5F5FB">
          <td><b>Observações</b></td>
          <td><?php echo str_replace("\n", "<br>", $vg_ultimo_status_obs) ?></td>
        </tr>
        <tr bgcolor="#F5F5FB">
          <td><b>Observações ao usuário</b></td>
          <td><?php echo str_replace("\n", "<br>", $vg_usuario_obs) ?></td>
        </tr>
        <tr bgcolor="#F5F5FB">
          <td><b>Conciliação</b></td>
          <td><?php echo ($vg_concilia == 1 ? "Conciliado" : "Não conciliado") ?></td>
        </tr>
      </table>

      <table class="table txt-preto">
        <tr bgcolor="#FFFFFF">
          <td colspan="2" bgcolor="#ECE9D8">Produtos</font>
          </td>
        </tr>
        <tr>
          <td>
            <table class="table">
              <tr bgcolor="F0F0F0" class="texto">
                <td align="center"><b>Produto</b></td>
                <td align="center"><b>Quantidade</b></td>
                <td align="right"><b>Preço Unitário</b></td>
                <td align="right"><b>Preço Total</b></td>
                <td align="center"><b>Desconto</b></td>
                <td align="center"><b>Repasse</b></td>
              </tr>
              <?php

              $qtde_total = 0;
              $total_geral = 0;
              $total_desconto = 0;
              $total_repasse = 0;
              while ($rs_venda_modelos_row = pg_fetch_array($rs_venda_modelos)) {
                $qtde = $rs_venda_modelos_row['vgm_qtde'];
                $valor = $rs_venda_modelos_row['vgm_valor'];
                $perc_desconto = $rs_venda_modelos_row['vgm_perc_desconto'];
                $geral = $valor * $qtde;
                $desconto = $geral * $perc_desconto / 100;
                $repasse = $geral - $desconto;

                $qtde_total += $qtde;
                $total_geral += $geral;
                $total_desconto += $desconto;
                $total_repasse += $repasse;

                ?>
                <tr class="texto" bgcolor="#F5F5FB">
                  <td width="200">
                    &nbsp;&nbsp;
                    <?php echo $rs_venda_modelos_row['vgm_nome_produto'] ?>
                    <?php if ($rs_venda_modelos_row['vgm_nome_modelo'] != "") { ?> -
                      <?php echo $rs_venda_modelos_row['vgm_nome_modelo'] ?>  <?php } ?>
                  </td>
                  <td align="center"><?php echo $qtde ?></td>
                  <td align="right"><?php echo number_format($valor, 2, ',', '.') ?></td>
                  <td align="right"><?php echo number_format($geral, 2, ',', '.') ?></td>
                  <td align="right"><?php echo number_format($desconto, 2, ',', '.') ?></td>
                  <td align="right"><?php echo number_format($repasse, 2, ',', '.') ?></td>
                </tr>
              <?php } ?>
              <tr bgcolor="F0F0F0" class="texto">
                <td colspan="2">&nbsp;</td>
                <td align="right"><b>Total</b></td>
                <td align="right"><b><?php echo number_format($total_geral, 2, ',', '.') ?></b></td>
                <td align="right"><b><?php echo number_format($total_desconto, 2, ',', '.') ?></b></td>
                <td align="right"><b><?php echo number_format($total_repasse, 2, ',', '.') ?></b></td>
              </tr>
            </table>
          </td>
        </tr>
      </table>

      <table class="table">
        <tr bgcolor="#FFFFFF" class="texto">
          <td bgcolor="#ECE9D8">Histórico</td>
        </tr>
        <tr bgcolor="#FFFFFF" class="texto">
          <td>
            <table border='0' width="100%" cellpadding="0" cellspacing="01" class="texto" bgcolor="ffffff">
              <tr bgcolor="#ECE9D8">
                <td align="center" width="150">Data</td>
                <td align="center" width="250">Status</td>
                <td align="center" width="494">Observações</td>
              </tr>
              <?php if ($rs_venda_hist && pg_num_rows($rs_venda_hist) > 0) { ?>
                <?php while ($rs_venda_hist_row = pg_fetch_array($rs_venda_hist)) {
                  if ($cor1 == $cor2) {
                    $cor1 = $cor3;
                  } else {
                    $cor1 = $cor2;
                  } ?>
                  <tr bgcolor="<?php echo $cor1 ?>">
                    <td align="center">
                      <?php echo formata_data_ts($rs_venda_hist_row['vgh_data_inclusao'], 0, true, true) ?></td>
                    <?php $vgh_status = $rs_venda_hist_row['vgh_status']; ?>
                    <?php $statusNome = $GLOBALS['STATUS_VENDA_DESCRICAO'][$vgh_status]; ?>
                    <td><?php echo substr($statusNome, 0, strpos($statusNome, '.')) ?></td>
                    <td><?php echo str_replace("\n", "<br>", $rs_venda_hist_row['vgh_status_obs']) ?></td>
                  </tr>
                <?php } ?>
              <?php } ?>
            </table>

          </td>
        </tr>
      </table>


      <table class="table">
        <tr bgcolor="#FFFFFF">
          <td colspan="2" bgcolor="#ECE9D8" class="texto">Usuário</font>
          </td>
        </tr>

        <tr bgcolor="#F5F5FB">
          <td width="150"><b>C&oacute;digo</b></td>
          <td><a style="text-decoration:none"
              href="/pdv/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $vg_ug_id ?>"><?php echo $vg_ug_id ?></a>
          </td>
        </tr>
        <?php if ($_SESSION["visualiza_dados"] == "S") { ?>
          <tr bgcolor="#F5F5FB">
            <td width="150"><b>Login</b></td>
            <td><a style="text-decoration:none"
                href="/pdv/usuarios/com_usuario_detalhe.php?usuario_id=<?php echo $vg_ug_id ?>"><?php echo $ug_login ?></a>
            </td>
          </tr>
        <?php } ?>
        <?php if ($ug_tipo_cadastro == "PJ") { ?>
          <tr bgcolor="#F5F5FB">
            <td><b>Nome Fantasia</b></td>
            <td><?php echo $ug_nome_fantasia ?></td>
          </tr>
          <?php if ($_SESSION["visualiza_dados"] == "S") { ?>
            <tr bgcolor="#F5F5FB">
              <td><b>CNPJ</b></td>
              <td><?php echo $ug_cnpj ?></td>
            </tr>
          <?php } ?>
          <tr bgcolor="#F5F5FB">
            <td><b>Responsável</b></td>
            <td><?php echo $ug_responsavel ?></td>
          </tr>
        <?php } ?>

        <?php if ($ug_tipo_cadastro == "PF") { ?>
          <tr bgcolor="#F5F5FB">
            <td><b>Nome</b></td>
            <td><?php echo $ug_nome ?></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>CPF</b></td>
            <td><?php echo $ug_cpf ?></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>RG</b></td>
            <td><?php echo $ug_rg ?></td>
          </tr>
        <?php } ?>
        <?php if ($_SESSION["visualiza_dados"] == "S") { ?>
          <tr bgcolor="#F5F5FB">
            <td><b>Email</b></td>
            <td><?php echo $ug_email ?></td>
          </tr>
        <?php } ?>
        <tr bgcolor="#F5F5FB">
          <td><b>Telefone</b></td>
          <td>(<?php echo $ug_tel_ddi ?>) (<?php echo $ug_tel_ddd ?>) <?php echo $ug_tel ?></td>
        </tr>
        <tr bgcolor="#F5F5FB">
          <td><b>Cidade</b></td>
          <td><?php echo $ug_cidade ?></td>
        </tr>
        <tr bgcolor="#F5F5FB">
          <td><b>Estado</b></td>
          <td><?php echo $ug_estado ?></td>
        </tr>

      </table>

      <?php
      $iformaAlpha = getCodigoCaracterParaPagto($vg_pagto_tipo);
      ?>
      <table class="table">
        <tr bgcolor="#FFFFFF">
          <td colspan="2" bgcolor="#ECE9D8">Dados do Pagamento</font>
          </td>
        </tr>
        <tr bgcolor="#F5F5FB">
          <td width="150"><b>Forma de Pagamento</b></td>
          <td><?php echo $GLOBALS['FORMAS_PAGAMENTO_DESCRICAO'][$iformaAlpha] ?></td>
        </tr>
        <tr bgcolor="#F5F5FB">
          <td><b>Banco</b></td>
          <td><?php echo $GLOBALS['PAGTO_BANCOS'][$vg_pagto_banco] ?></td>
        </tr>
        <tr bgcolor="#F5F5FB">
          <td><b>Data Inclusão</b></td>
          <td><?php if ($vg_pagto_data_inclusao)
            echo formata_data_ts($vg_pagto_data_inclusao, 0, true, true) ?></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>Local</b></td>
            <td><?php echo $GLOBALS['PAGTO_LOCAIS'][$vg_pagto_banco][$vg_pagto_local] ?></td>
        </tr>
        <tr bgcolor="#F5F5FB">
          <td><b>Data Informada</b></td>
          <td><?php if ($vg_pagto_data)
            echo formata_data_ts($vg_pagto_data, 0, false, false) ?></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td valign="top"><b>Valor Pago</b></td>
            <td>
            <?php if ($vg_pagto_valor_pago)
            echo number_format($vg_pagto_valor_pago, 2, ',', '.') ?><br>
            <?php /* if($vg_pagto_valor_pago != $total_geral){?><font color="FF0000">Atenção!!! Valor informado pelo usuário é diferente do valor da compra.<br>Certifique-se de que o pedido está correto.</font><?php  } */ ?>
          </td>
        </tr>
        <?php
        $pagto_nome_docto_Ar = preg_split("/;/", $PAGTO_NOME_DOCTO[$vg_pagto_banco][$vg_pagto_local]);
        for ($i = 0; $i < count($pagto_nome_docto_Ar); $i++) {
          ?>
          <tr bgcolor="#F5F5FB">
            <td><b><?php echo (trim($pagto_nome_docto_Ar[$i]) == "" ? "Nro Documento" : $pagto_nome_docto_Ar[$i]); ?></b></td>
            <td><?php echo $pagto_num_docto[$i] ?></td>
          </tr>
        <?php } ?>

        <?php $arquivos = buscaArquivosIniciaCom($FOLDER_COMMERCE_UPLOAD, 'nome', 'asc', "money_comprovante_" . $venda_id . "_");
        if (count($arquivos) > 0) { ?>
          <tr bgcolor="#F5F5FB">
            <td><b>Comprovante</b></td>
            <td><?php for ($j = 0; $j < count($arquivos); $j++) { ?><a style="text-decoration:none" target="_blank"
                  href="/pdv/pagamentos/com_pagto_compr_down.php?venda=<?php echo $venda_id ?>&arquivo=<?php echo $arquivos[$j] ?>">Comprovante
                  <?php echo ($j + 1) ?></a><br><?php } ?></td>
          </tr>
        <?php } ?>
      </table>
      <table class="table txt-preto">
        <tr bgcolor="#FFFFFF">
          <td colspan="2" bgcolor="#ECE9D8">Dados do CPF Informado para este Pedido</font>
          </td>
        </tr>
        <tr>
          <td>
            <?php if (isset($vgm_cpf) && count($vgm_cpf) > 0) { ?>
              <form name="form5" id="form5" method="post" action="">
                <input type="hidden" name="vgm_id" id="vgm_id" value="">
                <input type="hidden" name="vgm_nome_cpf" id="vgm_nome_cpf" value="">
                <input type="hidden" name="venda_id" id="venda_id" value="<?php echo $venda_id; ?>">
                <table class="table">
                  <tr bgcolor="F0F0F0" class="texto">
                    <td align="left"><b>Produto</b></td>
                    <td align="left"><b>CPF</b></td>
                    <td align="left"><b>Nome do CPF</b></td>
                    <td align="left"><b>Data Nascimento</b></td>
                    <td align="right"></td>
                    <td align="right"></td>
                  </tr>
                  <tr bgcolor="F0F0F0" class="texto">
                    <td colspan="" align="left"><input type="checkbox" name="todos" id="todos" value="1"
                        onclick="javascript:yesnoCheck();" /><b>Utilizar o mesmo CPF em todos os modelos na alteração.</b>
                    </td>
                    <td align="left"><input type="text" name="vgm_cpf[1]" id="vgm_cpf[1]" value="" maxlength="14"
                        size="14" class="texto cpf" /></td>
                    <td align="left"></td>
                    <td align="left"><input type="text" name="vgm_cpf_data_nascimento[1]" id="vgm_cpf_data_nascimento[1]"
                        value="" maxlength="10" size="10" class="texto data_nasci" /></td>
                    <td align="right"><span name="botao[1]" id="botao[1]"
                        class="glyphicon glyphicon-save-file graphycon-big" aria-hidden="true"
                        style="font-size: 22px;top:0px !important;cursor:pointer;cursor:hand;" alt="Salvar" title="Salvar"
                        onclick="salvaDadosCPF(1);"></span></td>
                    <td align="right"><span name="loading[1]"></span></td>
                  </tr>
                  <?php
                  foreach ($vgm_cpf as $key => $value) {
                    ?>
                    <tr class="texto" bgcolor="#F5F5FB">
                      <td align="left"><?php echo $vgm_descricao_modelo[$key]; ?></td>
                      <td align="left"><input type="text" name="vgm_cpf[<? echo $key; ?>]" id="vgm_cpf[<? echo $key; ?>]"
                          value="<?php echo $value; ?>" maxlength="14" size="14" class="texto cpf" /></td>
                      <td align="left"><?php echo $vgm_nome_cpf[$key]; ?></td>
                      <td align="left"><input type="text" name="vgm_cpf_data_nascimento[<? echo $key; ?>]"
                          id="vgm_cpf_data_nascimento[<? echo $key; ?>]"
                          value="<?php echo formata_data_ts($vgm_cpf_data_nascimento[$key], 0, false, false); ?>"
                          maxlength="10" size="10" class="texto data_nasci" /></td>
                      <td align="right"><span class="glyphicon glyphicon-save-file graphycon-big" aria-hidden="true"
                          style="font-size: 22px;top:0px !important;cursor:pointer;cursor:hand;" alt="Salvar" title="Salvar"
                          onclick="salvaDadosCPF(<?php echo $key; ?>);"></span></td>
                      <td align="right"><span name="loading[<?php echo $key; ?>]"></span></td>
                    </tr>
                    <?php
                  } //end foreach ($vgm_cpf as $key => $value)
                  ?>
                </table>
              </form>
            <?php } else
              echo "Este pedido possui produtos que não exigem CPF."; ?>
          </td>
        </tr>
      </table>

      <?php if ($vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO']) { ?>
        <table class="table">
          <tr bgcolor="#FFFFFF">
            <td colspan="2" bgcolor="#ECE9D8">Dados do Boleto</font>
            </td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td width="250"><b>Código</b></td>
            <td><?php echo $bbg_boleto_codigo ?></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td width="250"><b>Data de Inclusão</b></td>
            <td><?php if ($bbg_data_inclusao)
              echo formata_data_ts($bbg_data_inclusao, 0, true, true) ?></td>
            </tr>
            <tr bgcolor="#F5F5FB">
              <td><b>Banco</b></td>
              <td><?php echo str_pad($bbg_bco_codigo, 3, "0", STR_PAD_LEFT); ?></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>Data de Vencimento</b></td>
            <td><?php if ($bbg_data_venc)
              echo formata_data_ts($bbg_data_venc, 0, false, false) ?></td>
            </tr>
            <tr bgcolor="#F5F5FB">
              <td><b>Valor</b></td>
              <td><?php if ($bbg_valor)
              echo number_format($bbg_valor, 2, ',', '.') ?></td>
            </tr>
            <tr bgcolor="#F5F5FB">
              <td><b>Taxa de Serviço Bancário</b></td>
              <td><?php echo number_format((($bbg_valor_taxa > 0) ? $bbg_valor_taxa : 0), 2, ',', '.') ?></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>N. Docto</b></td>
            <td><?php echo $bbg_documento ?></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>Quitação</b></td>
            <td>
              <?php echo ((is_null($bbg_pago) || $bbg_pago == 0) ? ("Não quitado") : ("Quitado em " . formata_data_ts($bbg_data_pago, 0, false, false))) ?>
            </td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>Ver boleto</b></td>
            <?php
            $token = date('YmdHis') . "," . $venda_id . "," . $vg_ug_id;
            $objEncryption = new Encryption();
            $token_crypt = $objEncryption->encrypt($token);

            //echo "bbg_bco_codigo: '$bbg_bco_codigo'<br>";
          
            switch ($bbg_bco_codigo) {
              case $BOLETO_MONEY_BANCO_ITAU_COD_BANCO:
                $sboletoURL = "/SICOB/BoletoWebItauCommerceLH.php";
                break;
              case $BOLETO_MONEY_CAIXA_COD_BANCO:
                $sboletoURL = "/SICOB/BoletoWebCaixaDistCommerce.php";
                break;
              case $BOLETO_MONEY_BRADESCO_COD_BANCO:
                $sboletoURL = "/boletos/pdv/boleto_bradesco.php";
                break;
              case $BOLETO_BANCO_BANESPA_COD_BANCO:
                $sboletoURL = "/SICOB/BoletoWebBanespaCommerceLH.php";
                break;
              default:
                $sboletoURL = "";
                break;
            }
            ?>
            <td>
              <?php if ($sboletoURL) { ?>
                <a style="text-decoration:none"
                  href="https://<?php echo $_SERVER["SERVER_NAME"] . $sboletoURL; ?>?token=<?php echo $token_crypt ?>"
                  target="_blank">Boleto</a>*
                &nbsp;&nbsp;&nbsp;*link válido por 5 min, após este período recarregar a página para pode acessá-lo.
              <?php } else { ?>
                Sem boleto
              <?php } ?>
            </td>
          </tr>
        </table>

      <?php } elseif ($vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['REDECARD_MASTERCARD'] || $vg_pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['REDECARD_DINERS']) { ?>
        <table class="table">
          <tr bgcolor="#FFFFFF">
            <td colspan="2" bgcolor="#ECE9D8">Dados Redecard</font>
            </td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td width="150"><b>Código</b></td>
            <td><?php echo $vgrc_id ?></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td width="150"><b>Data de Inclusão</b></td>
            <td><?php if ($vgrc_data_inclusao)
              echo formata_data_ts($vgrc_data_inclusao, 0, true, true) ?></td>
            </tr>
            <tr bgcolor="#F5F5FB">
              <td><b>Parcelas</b></td>
              <td><?php echo $vgrc_parcelas ?></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>Valor</b></td>
            <td><?php if ($vgrc_total)
              echo number_format($vgrc_total, 2, ',', '.') ?></td>
            </tr>
            <tr bgcolor="#F5F5FB">
              <td><b>Data de Envio</b></td>
              <td><?php if ($vgrc_data_envio1)
              echo formata_data_ts($vgrc_data_envio1, 0, true, true) ?></td>
            </tr>
            <tr bgcolor="#F5F5FB">
              <td><b>IP do usuário</b></td>
              <td><?php echo $vgrc_usuario_ip ?></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>TRANSACAO</b></td>
            <td><?php echo $vgrc_transacao ?></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>BANDEIRA</b></td>
            <td><?php echo $vgrc_bandeira ?></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>CODVER</b></td>
            <td><?php echo $vgrc_codver ?></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>DATA</b></td>
            <td><?php echo $vgrc_ret2_data ?></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>NR_CARTAO</b></td>
            <td><?php echo $vgrc_ret2_nr_cartao ?></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>ORIGEM_BIN</b></td>
            <td><?php echo $vgrc_ret2_origem_bin ?></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>NUMAUTOR</b></td>
            <td><?php echo $vgrc_ret2_numautor ?></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>NUMCV</b></td>
            <td><?php echo $vgrc_ret2_numcv ?></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>NUMAUTENT</b></td>
            <td><?php echo $vgrc_ret2_numautent ?></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>NUMSQN</b></td>
            <td><?php echo $vgrc_ret2_numsqn ?></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>CODRET</b></td>
            <td><?php echo $vgrc_ret2_codret ?></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>MSGRET</b></td>
            <td><?php echo urldecode($vgrc_ret2_msgret) ?></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>ENDERECO</b></td>
            <td><?php echo $vgrc_ret2_endereco ?></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>NUMERO</b></td>
            <td><?php echo $vgrc_ret2_numero ?></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>COMPLEMENTO</b></td>
            <td><?php echo $vgrc_ret2_complemento ?></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>CEP</b></td>
            <td><?php echo $vgrc_ret2_cep ?></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>RESPAVS</b></td>
            <td><?php echo $vgrc_ret2_respavs ?></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>MSGAVS</b></td>
            <td><?php echo urldecode($vgrc_ret2_msgavs) ?></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>NUMPRG</b></td>
            <td><?php echo $vgrc_ret2_numprg ?></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>NR_HASH_CARTAO</b></td>
            <td><?php echo $vgrc_ret2_nr_hash_cartao ?></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>COD_BANCO</b></td>
            <td><?php echo $vgrc_ret2_cod_banco ?></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>Retorno Etapa 4</b></td>
            <td><?php echo $vgrc_ret4_ret ?></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>Retorno Etapa 4 - CODRET</b></td>
            <td><?php echo $vgrc_ret4_codret ?></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>Retorno Etapa 4 - MSGRET</b></td>
            <td><?php echo urldecode($vgrc_ret4_msgret) ?></td>
          </tr>
        </table>

      <?php } ?>


      <?php if ($vg_concilia == 1) { ?>

        <table class="table">
          <tr bgcolor="#FFFFFF">
            <td colspan="2" bgcolor="#ECE9D8" class="texto">Conciliação</font>
            </td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td width="150"><b>Data</b></td>
            <td width="744"><?php echo formata_data_ts($vg_data_concilia, 0, true, true) ?></td>
          </tr>
          <tr bgcolor="#F5F5FB">
            <td><b>Conciliado por</b></td>
            <td><?php echo $shn_nome ?></td>
          </tr>
          <?php if (!is_null($vg_dep_codigo) && $vg_dep_codigo != null) { ?>
            <tr bgcolor="#F5F5FB">
              <td><b>Código do depósito</b></td>
              <td><a style="text-decoration:none"
                  href="/financeiro/pedidos/depositos/altera.php?DepCod=<?php echo $vg_dep_codigo ?>"
                  target="_blank"><?php echo $vg_dep_codigo ?></a></td>
            </tr>
            <?php $sql = "select * from depositos_pendentes dep where dep_codigo = " . $vg_dep_codigo;
            $rs_dep = SQLexecuteQuery($sql);
            if (!$rs_dep || pg_num_rows($rs_dep) == 0) { ?>
              <tr bgcolor="#F5F5FB">
                <td><b>Dados do depósito</b></td>
                <td>Depósito não encontrado</td>
              </tr>
            <?php } else {
              $rs_dep_row = pg_fetch_array($rs_dep); ?>
              <tr bgcolor="#F5F5FB">
                <td><b>Doc Equivalente</b></td>
                <td><?php echo $rs_dep_row['dep_documento'] ?></td>
              </tr>
              <tr bgcolor="#F5F5FB">
                <td><b>Data Depósito</b></td>
                <td><?php echo formata_data($rs_dep_row['dep_data'], 0) ?></td>
              </tr>
              <tr bgcolor="#F5F5FB">
                <td><b>Banco</b></td>
                <td><?php echo $rs_dep_row['dep_banco'] ?></td>
              </tr>
            <?php } ?>
          <?php } ?>
          <?php if (!is_null($vg_bol_codigo) && $vg_bol_codigo != null) { ?>
            <tr bgcolor="#F5F5FB">
              <td><b>Código do boleto</b></td>
              <td><a style="text-decoration:none"
                  href="/financeiro/pedidos/boletos/altera.php?BolCod=<?php echo $vg_bol_codigo ?>"
                  target="_blank"><?php echo $vg_bol_codigo ?></a></td>
            </tr>
            <?php $sql = "select * from boletos_pendentes bol where bol_codigo = " . $vg_bol_codigo;
            $rs_bol = SQLexecuteQuery($sql);
            if (!$rs_bol || pg_num_rows($rs_bol) == 0) { ?>
              <tr bgcolor="#F5F5FB">
                <td><b>Dados do boleto</b></td>
                <td>Boleto não encontrado</td>
              </tr>
            <?php } else {
              $rs_bol_row = pg_fetch_array($rs_bol); ?>
              <tr bgcolor="#F5F5FB">
                <td><b>Doc Equivalente</b></td>
                <td><?php echo $rs_bol_row['bol_documento'] ?></td>
              </tr>
              <tr bgcolor="#F5F5FB">
                <td><b>Data Depósito</b></td>
                <td><?php echo formata_data($rs_bol_row['bol_data'], 0) ?></td>
              </tr>
              <tr bgcolor="#F5F5FB">
                <td><b>Banco</b></td>
                <td><?php echo $rs_bol_row['bol_banco'] ?></td>
              </tr>
            <?php } ?>
          <?php } ?>
        </table>

      <?php }

      if ($vg_ultimo_status == $GLOBALS['STATUS_VENDA']['PEDIDO_EFETUADO'] || $vg_ultimo_status == $GLOBALS['STATUS_VENDA']['PEDIDO_EM_STANDBY'] || $vg_ultimo_status == $GLOBALS['STATUS_VENDA']['AGUARDANDO_PROCESSAMENTO']) { ?>
        <form name="form2" method="post" action="com_venda_detalhe.php">
          <input type="hidden" name="venda_id" value="<?php echo $venda_id ?>">
          <table class="table">
            <tr bgcolor="#FFFFFF">
              <td colspan="2" bgcolor="#ECE9D8" class="texto">Processa venda</font>
              </td>
            </tr>
            <tr bgcolor="#F5F5FB" align="center">
              <td colspan="2"><b>Observações</b></td>
            </tr>
            <tr bgcolor="#F5F5FB">
              <td valign="top" colspan="2" align="center">
                <textarea cols="40" rows="8" name="ultimo_status_obs"><?php echo $vg_ultimo_status_obs ?></textarea>
              </td>
            </tr>
            <tr bgcolor="#F5F5FB">
              <td align="center" width="50%"><input type="submit" name="BtnProcessa" value="Processar"
                  class="botao_search"></td>
              <td align="center" width="50%"><input type="submit" name="BtnProcessaAgendamento"
                  value="Agendar Processamento" class="botao_search"></td>
            </tr>
          </table>
        </form>
      <?php }

      if ($vg_ultimo_status == $GLOBALS['STATUS_VENDA']['PROCESSAMENTO_REALIZADO'] || $vg_ultimo_status == $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']) { ?>
        <form name="form3" method="post" action="com_venda_detalhe.php">
          <input type="hidden" name="venda_id" value="<?php echo $venda_id ?>">
          <table class="table">
            <tr bgcolor="#FFFFFF">
              <td colspan="2" bgcolor="#ECE9D8" class="texto">Processa envio de email</font>
              </td>
            </tr>
            <tr bgcolor="#F5F5FB">
              <td colspan="2"><b>Observações</b></td>
            </tr>
            <tr bgcolor="#F5F5FB">
              <td valign="top" colspan="2">
                <textarea cols="40" rows="8" name="ultimo_status_obs"><?php echo $vg_ultimo_status_obs ?></textarea>
              </td>
            </tr>
            <tr bgcolor="#F5F5FB">
              <td colspan="2" align="center"><input type="submit" name="BtnProcessaEmail" value="Processar Email"
                  class="botao_search"></td>
            </tr>
          </table>
        </form>
      <?php }

      if ($vg_ultimo_status == $GLOBALS['STATUS_VENDA']['PEDIDO_EFETUADO'] || $vg_ultimo_status == $GLOBALS['STATUS_VENDA']['PEDIDO_EM_STANDBY'] || $vg_ultimo_status == $GLOBALS['STATUS_VENDA']['AGUARDANDO_PROCESSAMENTO']) { ?>
        <form name="form4" method="post" action="com_venda_detalhe.php">

          <table class="table">
            <tr bgcolor="#FFFFFF">
              <td bgcolor="#ECE9D8" colspan="2">Cancelamento</font>
              </td>
            </tr>
            <tr bgcolor="#F5F5FB">
              <td align="center"><b>Observações</b></td>
              <td align="center"><b>Observações ao usuário</b></td>
            </tr>
            <tr bgcolor="#F5F5FB">
              <td align="center"><textarea cols="40" rows="8"
                  name="ultimo_status_obs"><?php echo $vg_ultimo_status_obs ?></textarea></td>
              <td align="center"><textarea cols="40" rows="8" name="usuario_obs"><?php echo $vg_usuario_obs ?></textarea>
              </td>
            </tr>
            <tr bgcolor="#F5F5FB">
              <td colspan="2" align="center">
                <input style="padding: 10px;" id="code_payment" type="number" oninput="mascara_numeros(this)"
                  placeholder="Informe o ID aqui" required maxlength="19" />
                <br>
                <button type="button" id="btn_concilia" class="btn btn-success">Conciliar Manualmente</button>
              </td>
              <div class="modal fade" id="modalResultado" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h4 class="modal-title"></h4>

                      <div class="modal-body">
                      </div>
                      <div class="modal-footer">
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </tr>
            <tr bgcolor="#F5F5FB">
              <td align="center" colspan="2"><input type="submit" name="BtnCancelar" value="Cancelar Venda"
                  class="botao_search" onClick="return confirm('Deseja realmente cancelar esta venda ?');"></td>
            </tr>
          </table>
        </form>
        <input type="hidden" id="venda_id" name="venda_id" value="<?php echo $venda_id; ?>">
        <input type="hidden" id="dados_operador" name="dados_operador" value="<?php echo $_SESSION["userlogin_bko"]; ?>">
        <script>
          $('.fecha-modal').on('click', function () {
            $('.modal-title').empty();
            $('.modal-body h5').empty();
            $('.modal-body p').empty();
            $('.modal-footer').empty();
          });
          function mascara_numeros(input) {
            console.log(input.value);
            let parsed = Number.parseInt(input.value);
            if (Number.isNaN(parsed)) {
              input.value = input.value.replace(/[^0-9]/g, '');
            }

            if (input.value.length === 20) {
              $('.modal-title').append('Ops...');
              $('.modal-body').append('<h5>Você utrapassou o limite de caracteres!</h5><p>Insira um código com <strong>19 caracteres</strong>!</p>');
              $('#modalResultado').modal();
            }
          }
          $('#btn_concilia').on('click', function () {
            let valor_code_payment = $('#code_payment').val();

            let parsed = Number.parseInt(valor_code_payment);

            if (Number.isNaN(parsed)) {
              $('.modal-title').append('Ops...');
              $('.modal-body').append('<h5>Houve um erro. Tente mais tarde...</h5>');
              $('#modalResultado').modal();
            } else {
              $.ajax({
                url: "https://<?php echo $server_url_complete; ?>/pdv/vendas/ajax/request-api-rest.php",
                method: "POST",
                data: {
                  id: $("#code_payment").val(),
                  dados_operador: $("#dados_operador").val(),
                  venda_id: $("#venda_id").val()
                }
              }).done(function (mensagem) {
                if (mensagem == 'Conciliação manual não foi realizada!') {
                  $('.modal-title').append('Ops!');
                  $('.modal-body').append('<h5>' + mensagem + '</h5>');
                  $('.modal-footer').append('<button type="button" class="btn btn-default fecha-modal" data-dismiss="modal">Fechar</button>');
                  $('#modalResultado').modal();
                  console.log(mensagem);
                } else if (mensagem == 'Concialização realizada com sucesso!') {
                  $('.modal-title').append('Sucesso!');
                  $('.modal-body').append('<h5>' + mensagem + '</h5>');
                  $('.modal-footer').append('<button type="button" class="btn btn-default fecha-modal" onclick="window.location.reload()" data-dismiss="modal">Fechar</button>');
                  $('#modalResultado').modal();
                  console.log(mensagem);
                } else {
                  $('.modal-title').append('Ops!');
                  $('.modal-body').append('<h5>' + mensagem + '</h5>');
                  $('.modal-footer').append('<button type="button" class="btn btn-default fecha-modal" data-dismiss="modal">Fechar</button>');
                  $('#modalResultado').modal();
                  console.log(mensagem);
                }

              });
            }
          });
        </script>
      <?php }


      $varsel = "&tf_v_codigo=$tf_v_codigo&tf_v_status=$tf_v_status";
      $varsel .= "&tf_v_data_inclusao_ini=$tf_v_data_inclusao_ini&tf_v_data_inclusao_fim=$tf_v_data_inclusao_fim";
      $varsel .= "&tf_v_data_concilia_ini=$tf_v_data_concilia_ini&tf_v_data_concilia_fim=$tf_v_data_concilia_fim";
      $varsel .= "&tf_v_concilia=$tf_v_concilia&tf_d_forma_pagto=$tf_d_forma_pagto&tf_d_banco=$tf_d_banco&tf_d_local=$tf_d_local";
      $varsel .= "&tf_d_data_ini=$tf_d_data_ini&tf_d_data_fim=$tf_d_data_fim";
      $varsel .= "&tf_d_data_inclusao_ini=$tf_d_data_inclusao_ini&tf_d_data_inclusao_fim=$tf_d_data_inclusao_fim";
      $varsel .= "&tf_d_valor_pago=$tf_d_valor_pago&tf_d_num_docto=$tf_d_num_docto";
      $varsel .= "&tf_u_codigo=$tf_u_codigo&tf_u_nome_fantasia=$tf_u_nome_fantasia&tf_u_email=$tf_u_email&tf_u_responsavel=$tf_u_responsavel";
      $varsel .= "&tf_u_cnpj=$tf_u_cnpj&tf_v_repasse=$tf_v_repasse";
      $varsel .= "&tf_u_nome=$tf_u_nome&tf_u_rg=$tf_u_rg&tf_u_cpf=$tf_u_cpf";
      $varsel .= "&tf_v_valor=$tf_v_valor&tf_v_qtde_produtos=$tf_v_qtde_produtos&tf_v_qtde_itens=$tf_v_qtde_itens";
      ?>
      <table class="table">
        <tr bgcolor="#F5F5FB">
          <td colspan="2" align="center">
            <input disabled type="button" name="BtnAnterior" value="Anterior não conciliado" class="botao_search"
              onClick="window.location='com_fila_vendas.php?venda_id=<?php echo $venda_id ?>&fila_ncamp=<?php echo $fila_ncamp ?>&fila_ordem=1<?php echo $varsel ?>';">
            &nbsp;&nbsp;&nbsp;
            <input type="button" name="BtnVoltar" value="Voltar" class="botao_search"
              onClick="window.location='index.php'">
            &nbsp;&nbsp;&nbsp;
            <input disabled type="button" name="BtnProximo" value="Próximo não conciliado" class="botao_search"
              onClick="window.location='com_fila_vendas.php?venda_id=<?php echo $venda_id ?>&fila_ncamp=<?php echo $fila_ncamp ?>&fila_ordem=2<?php echo $varsel ?>';">
          </td>
        </tr>
      </table>

    </td>
  </tr>
</table>
<?php
require_once $raiz_do_projeto . "backoffice/includes/rodape_bko.php";
?>

</html>
<?php

//Testa vgm_pin_codinterno = null, caso contrario já houve uam venda
function get_pins_vendidos($vg_id)
{
  $vgm_pin_codinterno = "";
  $msg = "";

  $sql_mod = "select vgm_id from tb_dist_venda_games_modelo where vgm_vg_id = $vg_id;";
  //echo "$sql_mod<br>";
  $rs_mod = SQLexecuteQuery($sql_mod);
  if (!$rs_mod || pg_num_rows($rs_mod) == 0) {
    $msg = "Nenhum modelo encontrado (vg_id = $vg_id).\n";
  } else {
    while ($rs_mod_row = pg_fetch_array($rs_mod)) {
      ;
      $vgm_id = $rs_mod_row['vgm_id'] . "";
      $sql_pin = "select vgmp_pin_codinterno from tb_dist_venda_games_modelo_pins where vgmp_vgm_id = $vgm_id;";
      $rs_pin = SQLexecuteQuery($sql_pin);
      if (!$rs_pin || pg_num_rows($rs_pin) == 0) {
        $msg = "Nenhum PIN encontrado.\n";
      } else {
        while ($rs_pin_row = pg_fetch_array($rs_pin)) {
          if ($vgm_pin_codinterno)
            $vgm_pin_codinterno .= ", ";
          $vgm_pin_codinterno .= $rs_pin_row['vgmp_pin_codinterno'] . "";
        }
      }
    }
  }

  if ($msg != "") {
    $GLOBALS['sem_pins'] = true;
  }

  return $vgm_pin_codinterno;
}

?>