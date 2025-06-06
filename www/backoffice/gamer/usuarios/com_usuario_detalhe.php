<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/main.php";
require_once $raiz_do_projeto . "includes/gamer/main.php";
//$varBlDebug = true;

//error_reporting(E_ALL); 
//ini_set("display_errors", 1); 

$varsel = "&usuario_id=$usuario_id";

$max = $qtde_reg_tela;
$default_add = nome_arquivo($PHP_SELF);
$img_anterior = "https://" . $_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'] . "/images/anterior.gif";
$img_proxima = "https://" . $_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'] . "/images/proxima.gif";
if (!$range)
  $range = 1;
$range_qtde = $qtde_range_tela;

$msg = "";
$msgAcao = "";

if (!$usuario_id)
  $msg = "Código do usuário não fornecido.\n";
elseif (!is_numeric($usuario_id))
  $msg = "Código do usuário inválido.\n";

//Processa Acoes
if ($msg == "") {

  //Alterar Dados do Estabelecimento
  if ($acao && $acao == "a") {

    if (!$v_campo || trim($v_campo) == '') {
      $msgAcao = "Item a ser alterado não especificado.\n";
    }

    if ($msgAcao == "") {
      $cad_usuarioGames = new UsuarioGames($usuario_id);
      if ($v_campo == 'email')
        $cad_usuarioGames->setEmail($v_valor_new);
      if ($v_campo == 'ativo')
        $cad_usuarioGames->setAtivo($v_valor_new);
      if ($v_campo == 'news')
        $cad_usuarioGames->setNews($v_valor_new);
      if ($v_campo == 'cielo') {
        if ($v_valor_new == '2') {
          $v_valor_new = '0';
        }
        $cad_usuarioGames->setUseCielo($v_valor_new);
      }
      $instUsuarioGames = new UsuarioGames();
      $msgAcao = $instUsuarioGames->atualizar($cad_usuarioGames);
    }
  }
}

//Recupera dados do usuario
if ($msg == "") {
  $sql = "select * from usuarios_games ug " .
    "where ug.ug_id = " . $usuario_id;
  $rs_usuario = SQLexecuteQuery($sql);
  if (!$rs_usuario || pg_num_rows($rs_usuario) == 0)
    $msg = "Nenhum cliente encontrado.\n";
  else {
    $rs_usuario_row = pg_fetch_array($rs_usuario);
    $ug_id = $rs_usuario_row['ug_id'];
    $ug_ativo = $rs_usuario_row['ug_ativo'];
    $ug_data_inclusao = $rs_usuario_row['ug_data_inclusao'];
    $ug_data_ultimo_acesso = $rs_usuario_row['ug_data_ultimo_acesso'];
    $ug_qtde_acessos = $rs_usuario_row['ug_qtde_acessos'];
    $ug_email = $rs_usuario_row['ug_email'];
    $ug_nome = $rs_usuario_row['ug_nome'];
    $ug_cpf = $rs_usuario_row['ug_cpf'];
    $ug_login = $rs_usuario_row['ug_login'];
    $ug_rg = $rs_usuario_row['ug_rg'];
    $ug_data_nascimento = $rs_usuario_row['ug_data_nascimento'];
    $ug_sexo = $rs_usuario_row['ug_sexo'];
    $ug_endereco = $rs_usuario_row['ug_endereco'];
    $ug_numero = $rs_usuario_row['ug_numero'];
    $ug_complemento = $rs_usuario_row['ug_complemento'];
    $ug_bairro = $rs_usuario_row['ug_bairro'];
    $ug_cidade = $rs_usuario_row['ug_cidade'];
    $ug_estado = $rs_usuario_row['ug_estado'];
    $ug_cep = $rs_usuario_row['ug_cep'];
    $ug_tel_ddi = $rs_usuario_row['ug_tel_ddi'];
    $ug_tel_ddd = $rs_usuario_row['ug_tel_ddd'];
    $ug_tel = $rs_usuario_row['ug_tel'];
    $ug_cel_ddi = $rs_usuario_row['ug_cel_ddi'];
    $ug_cel_ddd = $rs_usuario_row['ug_cel_ddd'];
    $ug_cel = $rs_usuario_row['ug_cel'];
    $ug_news = $rs_usuario_row['ug_news'];
    $ug_habbo_id = $rs_usuario_row['ug_habbo_id'];
    $ug_use_cielo = $rs_usuario_row['ug_use_cielo'];
    $ug_nome_da_mae = $rs_usuario_row['ug_nome_da_mae'];
    //Capturando o Histórico de Observações
    $sql = "SELECT to_char(ugo_data,'DD/MM/YYYY HH24:MI:SS') as data,* FROM usuarios_games_obs WHERE ug_id = " . $rs_usuario_row['ug_id'] . ";";
    $rs_usuario_obs = SQLexecuteQuery($sql);
    $ug_obs = "";
    while ($rs_usuario_obs_row = pg_fetch_array($rs_usuario_obs)) {
      $ug_obs .= "Em " . $rs_usuario_obs_row['data'] . PHP_EOL . "Autor: " . $rs_usuario_obs_row['ugo_user_insert'] . PHP_EOL . "Observação:" . PHP_EOL . $rs_usuario_obs_row['ug_obs'] . PHP_EOL . str_repeat("-", 40) . PHP_EOL;
    }//end while

  }
}
$msg .= $msgAcao;


$cor1 = "#F5F5FB";
$cor2 = "#F5F5FB";
$cor3 = "#FFFFFF";

ob_end_flush();
?>
<script language="javascript">
  function fcnEditarCadastro(cod) {
    form1.action = 'com_usuario_detalhe_salva.php?acao=edt&usuario_id=' + cod;
    form1.submit();
  }
  function GP_popupAlertMsg(msg) { //v1.0
    document.MM_returnValue = alert(msg);
  }
  function GP_popupConfirmMsg(msg) { //v1.0
    document.MM_returnValue = confirm(msg);
  }
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script language="javascript">

document.addEventListener("DOMContentLoaded", function () {
  const btn = document.getElementById("btn-remove-auth");

  btn.addEventListener("click", function () {
    Swal.fire({
      title: "Tem certeza?",
      text: "Esta ação removerá o autenticador do usuário!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Sim, remover",
      cancelButtonText: "Cancelar"
    }).then((result) => {
      if (result.isConfirmed) {
        const id = document.getElementById("codigo-gamer").innerText;

        Swal.fire({
          didOpen: () => {
            Swal.showLoading();
          }
        });

        fetch("/gamer/usuarios/ajaxRemoverAutenticador.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          body: `id=${encodeURIComponent(id)}&codigo=${encodeURIComponent('Gz8#kV2!mP$Xr9@tQw')}`,
          credentials: "include"
        })
          .then(response => response.json())
          .then(data => {
            Swal.close();

            if (data.situacao === "success") {
              Swal.fire(
                "Sucesso!",
                data.msg,
                "success"
              ).then(() => {
                document.getElementById("btn-remove-auth").style.display = "none";
                document.getElementById("txt-auth").innerText = "Não";
              });
            } else {
              Swal.fire(
                "Erro!",
                data.msg,
                "error"
              );
            }
          })
          .catch(error => {
            Swal.close();
            Swal.fire("Erro!", "Erro na requisição.", "error");
            console.error(error);
          });
      }
    });
  });
});

  function fcnAlterar(v_campo, v_valor_old, v_texto_adicional) {

    var msg = '';
    var v_valor_new_aux = '';

    var v_valor_new = prompt('Entre com o novo valor:\n' + v_texto_adicional, v_valor_old);

    if (v_valor_new == null) return;
    //		msg = 'Alteração cancelada.';

    if (trimAll(v_valor_new) == '')
      msg = 'Valor inválido.';
    else if (trimAll(v_valor_new) == trimAll(v_valor_old))
      msg = 'Novo valor igual ao valor antigo.';

    if (msg != '') {
      alert(msg);
    } else {
      form1.v_campo.value = v_campo;
      form1.v_valor_old.value = v_valor_old;
      form1.v_valor_new.value = v_valor_new;
      form1.action = '?acao=a<?php echo $varsel ?>';
      //		alert('?acao=a<?php echo $varsel ?>');
      form1.submit();
    }
  }


  function trimAll(sString) {
    while (sString.substring(0, 1) == ' ')
      sString = sString.substring(1, sString.length);
    while (sString.substring(sString.length - 1, sString.length) == ' ')
      sString = sString.substring(0, sString.length - 1);

    return sString;
  }

</script>
<form name="form1" method="post" action="est_perfil_informa_estab.php">
  <input type="hidden" name="v_campo" value="">
  <input type="hidden" name="v_valor_old" value="">
  <input type="hidden" name="v_valor_new" value="">
</form>
<div class="col-md-12">
  <ol class="breadcrumb top10">
    <li><a href="#" class="muda-aba" ordem="1">BackOffice - Money</a></li>
    <li><a href="com_pesquisa_usuarios.php">Pesquisar</a></li>
    <li class="active">Usuário</li>
  </ol>
</div>
<table class="table">
  <tr>
    <td width="891" valign="top">
      <?php if ($msg != "") { ?>
        <table class="table txt-preto fontsize-pp">
          <tr>
            <td align="center" class="texto">
              <font color="#FF0000"><?php echo str_replace("\n", "<br>", $msg) ?></font>
            </td>
          </tr>
        </table>
      <?php } ?>

      <table class="table fontsize-pp txt-preto">
        <tr bgcolor="#FFFFFF" class="texto">
          <td colspan="4" bgcolor="#ECE9D8">Dados Administrativos</font>
          </td>
        </tr>
        <tr bgcolor="#F5F5FB" class="texto">
          <td width="140"><b>C&oacute;digo</b></td>
          <td id="codigo-gamer" width="307"><?php echo $ug_id ?></td>
          <?php
          $sql = "select to_char(max(ugc_data_cancelamento), 'DD/MM/YYYY HH24:MI:SS') as ugc_data_cancelamento from usuarios_games_cancelado where ug_id=" . $usuario_id;
          $rs = SQLexecuteQuery($sql);
          $rs_row = pg_fetch_array($rs);
          //echo "pg_num_rows(rs): ".pg_num_rows($rs).$rs_row['ugc_data_cancelamento'].$rs_row['ug_id']."<br>";
          
          if (!$rs || pg_num_rows($rs) == 0 || empty($rs_row['ugc_data_cancelamento'])) {
            ?>
            <td width="140"><a class="link_azul" href="#"
                Onclick="if(confirm('Deseja alterar o Status deste usuário ?')) window.open('com_usuario_detalhe_selecao.php?v_campo=ativo&ativo=<?php echo $ug_ativo ?><?php echo $varsel ?>','selecao', 'status=0,width=500,height=200,top=0,left=0');return false;"><b>Status</b></a>
            </td>
            <td width="307"><?php echo $GLOBALS['STATUS_USUARIO_LEGENDA'][$ug_ativo]; ?></td>
            <?php
          } else {
            //$rs_row = pg_fetch_array($rs);
            ?>
            <td width="140"><b>Usu&aacute;rio Suspendeu em:</b></td>
            <td width="307"><?php echo $rs_row['ugc_data_cancelamento']; ?></td>
            <?php
          }
          ?>
        </tr>
        <tr bgcolor="#F5F5FB" class="texto">
          <td><b>Qtde de Acessos</b></td>
          <td><?php echo $ug_qtde_acessos ?></td>
          <td><b>Data Último Acesso</b></td>
          <td><?php echo formata_data_ts($ug_data_ultimo_acesso, 0, true, true) ?></td>
        </tr>
        <tr bgcolor="#F5F5FB" class="texto">
          <td><b>Data de Cadastro</b></td>
          <td><?php echo formata_data_ts($ug_data_inclusao, 0, true, true) ?></td>
          <td width="140"><a class="link_azul" href="#"
              Onclick="if(confirm('Deseja alterar o Opção de Pagamento Cielo deste usuário ?')) window.open('com_usuario_detalhe_selecao.php?v_campo=cielo&ug_use_cielo=<?php echo $ug_use_cielo ?><?php echo $varsel ?>','selecao', 'status=0,width=500,height=200,top=0,left=0');return false;"><b>Pagto.
                Cielo</b></a></td>
          <td width="307"><?php echo ($ug_use_cielo == 1) ? "Ativo" : "Inativo" ?></td>
        </tr>
        <tr bgcolor="#F5F5FB" class="texto">
          <td><b>Usuário VIP?</b></td>
          <td><?php
          $usuarioGames1 = new UsuarioGames($ug_id);
          $meu_ip = '201.93.162.169';

          if ($_SERVER['REMOTE_ADDR'] == $meu_ip) {
            // teste
            //echo $usuarioGames1->getStatusVipOuNao();
            //$bret = $usuarioGames1->b_IsLogin_pagamento_vip();
            //echo (($bret)?"<font color='blue'>SIM</font>":"não");
            $bret = $usuarioGames1->b_IsLogin_pagamento_vip();
            //echo (($bret)?"<font color='blue'>SIM</font>":"não");
            echo (($bret) ? "<font color='blue'>SIM</font>" : "não");
          } else {
            $bret = $usuarioGames1->b_IsLogin_pagamento_vip();
            echo (($bret) ? "<font color='blue'>SIM</font>" : "não");
          }


          ?></td>
          <td width="140"><b>Lista Extrato?</b></td>
          <td width="307"><?php
          $bret = $usuarioGames1->b_IsLogin_extrato_UG();
          echo (($bret) ? "<font color='blue'>SIM</font>" : "não");
          ?></td>
        </tr>
        <tr bgcolor="#F5F5FB" class="texto">
          <td><b>Possui um autenticador</b></td>
          <td id="txt-auth"><?php
          $sql = "SELECT ug_chave_autenticador FROM usuarios_games WHERE ug_id = " . $usuario_id;
          $res_te = SQLexecuteQuery($sql);
          if ($res_te_row = pg_fetch_array($res_te)) {
            $autenticador = !empty($res_te_row['ug_chave_autenticador']);
            echo $autenticador ? "Sim" : "Não";
          } else {
            echo "Não encontrado";
          }

          ?></td>
          <td></td>
          <td><button type="button" class="btn btn-danger <?php echo $autenticador ? "" : "d-none" ?>"
              id="btn-remove-auth">Remover autenticador
              &#128274;</button></td>
        </tr>
        <tr bgcolor="#ECE9D8" class="texto">
          <td align="left">
            <nobr>Dados Pessoais</nobr>
          </td>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td align="right"><input type="button" value="Editar cadastro"
              Onclick="fcnEditarCadastro(<?php echo $ug_id; ?>)" class="btn btn-sm btn-info"> </td>
        </tr>
        <tr bgcolor="#F5F5FB" class="texto">
          <td><b>Email</b>
            <?php
            /*<a class="link_azul" href="#" Onclick="if(confirm('Deseja alterar o Email deste usuário ?')) fcnAlterar('email','<?php echo $ug_email ?>','');return false;"><b>Email</b></a></td>
             */
            ?>

          <td><?php echo $ug_email ?></td>
          <td><b>Nome</b></td>
          <td><?php echo $ug_nome ?></td>
        </tr>
        <tr bgcolor="#F5F5FB" class="texto">
          <td><b>CPF</b></td>
          <td><?php echo $ug_cpf ?></td>
          <td><b>RG</b></td>
          <td><?php echo $ug_rg ?></td>
        </tr>
        <tr bgcolor="#F5F5FB" class="texto">
          <td><b>Data de Nascimento</b></td>
          <td><?php echo formata_data($ug_data_nascimento, 0) ?></td>
          <td><b>Login</b></td>
          <td><?php echo $ug_login ?></td>
        </tr>
        <tr bgcolor="#F5F5FB" class="texto">
          <td><b>Nome da Mãe do Usuário</b></td>
          <td><?php echo $ug_nome_da_mae; ?></td>
          <td><b>Sexo</b></td>
          <td><?php echo $ug_sexo ?></td>
        </tr>
        <tr bgcolor="#F5F5FB" class="texto">
          <td><b>Telefone</b></td>
          <td>(<?php echo $ug_tel_ddi ?>) (<?php echo $ug_tel_ddd ?>) <?php echo $ug_tel ?></td>
          <td><b>Celular</b></td>
          <td>(<?php echo $ug_cel_ddi ?>) (<?php echo $ug_cel_ddd ?>) <?php echo $ug_cel ?></td>
        </tr>
        <tr bgcolor="#F5F5FB" class="texto">
          <td><b>Nome (cpf)</b></td>
          <td><?php echo $rs_usuario_row['ug_nome_cpf']; ?></td>
          <td>
            <a class="link_azul" href="#"
              Onclick="if(confirm('Deseja alterar o Newsletter deste usuário ?')) window.open('com_usuario_detalhe_selecao.php?v_campo=news&news=<?php echo $ug_news ?><?php echo $varsel ?>','selecao', 'status=0,width=500,height=200,top=0,left=0');return false;">
              <a class="link_azul" href="#"
                Onclick="if(confirm('Deseja alterar o Newsletter deste usuário ?')) fcnAlterar('news','<?php echo $ug_news ?>','');return false;"><b>Newsletter</b></a>
          </td>
          <td><?php
          if ($ug_news == 'h')
            echo "Sim - HTML";
          elseif ($ug_news == 't')
            echo "Sim - Texto";
          else
            echo "Não";
          ?></td>
        </tr>
        <tr bgcolor="#FFFFFF" class="texto">
          <td colspan="4" bgcolor="#ECE9D8">Endereço</font>
          </td>
        </tr>
        <tr bgcolor="#F5F5FB" class="texto">
          <td><b>Endereço</b></td>
          <td colspan="3"><?php echo $ug_endereco ?></td>
        </tr>
        <tr bgcolor="#F5F5FB" class="texto">
          <td><b>Número</b></td>
          <td><?php echo $ug_numero ?></td>
          <td><b>Complemento</b></td>
          <td><?php echo $ug_complemento ?></td>
        </tr>
        <tr bgcolor="#F5F5FB" class="texto">
          <td><b>Bairro</b></td>
          <td><?php echo $ug_bairro ?></td>
          <td><b>Cidade</b></td>
          <td><?php echo $ug_cidade ?></td>
        </tr>
        <tr bgcolor="#F5F5FB" class="texto">
          <td><b>CEP</b></td>
          <td><?php echo $ug_cep ?></td>
          <td><b>Estado</b></td>
          <td><?php echo $ug_estado ?></td>
        </tr>
      </table>
      <div class="col-md-12 bg-cinza-claro txt-preto">
        <p class="top20">Pedidos</p>
      </div>

      <?php

      $varsel = "&usuario_id=" . $usuario_id;
      if (!$ncamp_v)
        $ncamp_v = " vg_data_inclusao ";
      if (!$ordem_v)
        $ordem_v = 1;
      if (!$inicial_v)
        $inicial_v = 0;
      $sql = "select vg.vg_id, vg.vg_data_inclusao, vg.vg_pagto_tipo, vg.vg_ultimo_status, vg.vg_concilia, 
							sum(vgm.vgm_valor * vgm.vgm_qtde) as valor, sum(vgm.vgm_qtde) as qtde_itens, count(*) as qtde_produtos 
					 from tb_venda_games vg 
					 inner join tb_venda_games_modelo vgm on vgm.vgm_vg_id = vg.vg_id 
 					 where vg.vg_ug_id = " . $usuario_id . "
					 group by vg.vg_id, vg.vg_data_inclusao, vg.vg_pagto_tipo, vg.vg_ultimo_status, vg.vg_concilia ";
      //$rs_venda = SQLexecuteQuery($sql);
      //$total_table = pg_num_rows($rs_venda);
      $sql .= " order by " . $ncamp_v . " " . ($ordem_v == 1 ? "desc" : "asc");
      //$sql .= " limit " . $max . " offset " . $inicial_v;
      $sql .= " limit 50;";
      $rs_venda = SQLexecuteQuery($sql);
      $total_table = pg_num_rows($rs_venda);

      ?>
      <div id="Layer1" class="" style="position:static; width:100%; height:150px; z-index:1; overflow: auto;">
        <table class="table txt-preto fontsize-pp table-bordered">
          <?php $ordem_v = ($ordem_v == 1) ? 2 : 1; ?>
          <tr bgcolor="#ECE9D8">
            <td align="center"><a style="text-decoration:none"
                href="<?php echo $default_add . "?ordem_v=" . $ordem_v . "&ncamp_v=vg_id&inicial_v=" . $inicial_v . $varsel ?>"
                class="link_branco">Pedido</a></td>
            <td align="center"><a style="text-decoration:none"
                href="<?php echo $default_add . "?ordem_v=" . $ordem_v . "&ncamp_v=vg_data_inclusao&inicial_v=" . $inicial_v . $varsel ?>"
                class="link_branco">Data</a></td>
            <td align="center"><a style="text-decoration:none"
                href="<?php echo $default_add . "?ordem_v=" . $ordem_v . "&ncamp_v=vg_pagto_tipo&inicial_v=" . $inicial_v . $varsel ?>"
                class="link_branco">Forma de Pagamento</a><br>(ord. pelo código)</td>
            <td align="center"><a style="text-decoration:none"
                href="<?php echo $default_add . "?ordem_v=" . $ordem_v . "&ncamp_v=vg_ultimo_status&inicial_v=" . $inicial_v . $varsel ?>"
                class="link_branco">Status</a><br>(ord. pelo código)</td>
            <td align="center"><a style="text-decoration:none"
                href="<?php echo $default_add . "?ordem_v=" . $ordem_v . "&ncamp_v=valor&inicial_v=" . $inicial_v . $varsel ?>"
                class="link_branco">Valor</a></td>
            <td align="center"><a style="text-decoration:none"
                href="<?php echo $default_add . "?ordem_v=" . $ordem_v . "&ncamp_v=qtde_itens&inicial_v=" . $inicial_v . $varsel ?>"
                class="link_branco">Qtde Itens</a></td>
            <td align="center"><a style="text-decoration:none"
                href="<?php echo $default_add . "?ordem_v=" . $ordem_v . "&ncamp_v=qtde_produtos&inicial_v=" . $inicial_v . $varsel ?>"
                class="link_branco">Qtde Produtos</a></td>
            <td align="center"><a style="text-decoration:none"
                href="<?php echo $default_add . "?ordem_v=" . $ordem_v . "&ncamp_v=vg_concilia&inicial_v=" . $inicial_v . $varsel ?>"
                class="link_branco">Conciliação</a></td>
          </tr>
          <?php if (!$rs_venda || pg_num_rows($rs_venda) == 0) { ?>
            <tr>
              <td align="center" colspan="4">Nenhum pedido encontrado</td>
            </tr>
          <?php } else { ?>
            <?php while ($rs_venda_row = pg_fetch_array($rs_venda)) {
              if ($cor1 == $cor2) {
                $cor1 = $cor3;
              } else {
                $cor1 = $cor2;
              }
              $status = $rs_venda_row['vg_ultimo_status'];
              $pagto_tipo = $rs_venda_row['vg_pagto_tipo'];
              if ($pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['DEP_DOC_TRANSF'])
                $pagto_tipo = "Transf, DOC, Dep";
              elseif ($pagto_tipo == $GLOBALS['FORMAS_PAGAMENTO']['BOLETO_BANCARIO'])
                $pagto_tipo = "Boleto";
              else
                $pagto_tipo = $GLOBALS['FORMAS_PAGAMENTO_DESCRICAO_NUMERICO'][$pagto_tipo];
              ?>
              <tr bgcolor="<?php echo $cor1 ?>">
                <td class="texto" align="center"><a style="text-decoration:none"
                    href="/gamer/vendas/com_venda_detalhe.php?venda_id=<?php echo $rs_venda_row['vg_id'] ?>"><?php echo $rs_venda_row['vg_id'] ?></a>
                </td>
                <td class="texto" align="center">
                  <?php echo formata_data_ts($rs_venda_row['vg_data_inclusao'], 0, true, true) ?></td>
                <td class="texto"><?php echo $pagto_tipo ?></td>
                <td class="texto">
                  <?php echo substr($GLOBALS['STATUS_VENDA_DESCRICAO'][$status], 0, strpos($GLOBALS['STATUS_VENDA_DESCRICAO'][$status], '.')) ?>
                </td>
                <td class="texto" align="right"><?php echo number_format($rs_venda_row['valor'], 2, ',', '.') ?></td>
                <td class="texto" align="center"><?php echo $rs_venda_row['qtde_itens'] ?></td>
                <td class="texto" align="center"><?php echo $rs_venda_row['qtde_produtos'] ?></td>
                <?php if (
                  $status == $GLOBALS['STATUS_VENDA']['DADOS_PAGTO_RECEBIDO'] ||
                  $status == $GLOBALS['STATUS_VENDA']['PAGTO_CONFIRMADO'] ||
                  $status == $GLOBALS['STATUS_VENDA']['PROCESSAMENTO_REALIZADO'] ||
                  $status == $GLOBALS['STATUS_VENDA']['VENDA_REALIZADA']
                ) { ?>
                  <td class="texto" align="center"><a style="text-decoration:none"
                      href="/gamer/vendas/com_venda_detalhe.php?venda_id=<?php echo $rs_venda_row['vg_id'] ?>"><?php echo (($rs_venda_row['vg_concilia'] == 0) ? "Conciliar" : "Conciliado") ?></a>
                  </td>
                <?php } else { ?>
                  <td>&nbsp;</td>
                <?php } ?>
              </tr>
            <?php } ?>
            <?php	//paginacao_query($inicial_v, $total_table, $max, '4', $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel."&inicial=$inicial", "inicial_v"); ?>
          <?php } ?>
        </table>
      </div>
      <div class="col-md-12 bg-cinza-claro top10 txt-preto">
        <p class="top20">Histórico</p>
      </div>
      <?php
      $varsel = "&usuario_id=" . $usuario_id;
      if (!$ncamp)
        $ncamp = "ugl_data_inclusao";
      if (!$ordem)
        $ordem = 1;
      if (!$inicial)
        $inicial = 0;
      $sql = "select * from usuarios_games_log ugl " .
        "where ugl.ugl_ug_id = " . $usuario_id;
      //$rs_usuario_log = SQLexecuteQuery($sql);
      //$total_table = pg_num_rows($rs_usuario_log);
      $sql .= " order by " . $ncamp . " " . ($ordem == 1 ? "desc" : "asc");
      //$sql .= " limit " . $max . " offset " . $inicial;
      $sql .= " limit 100;";
      $rs_usuario_log = SQLexecuteQuery($sql);
      $total_table = pg_num_rows($rs_usuario_log);
      ?>
      <div id="Layer1" class="" style="position:static; width:100%; height:150px; z-index:1; overflow: auto;">
        <table class="table txt-preto fontsize-pp table-bordered">
          <?php $ordem = ($ordem == 1) ? 2 : 1; ?>
          <tr bgcolor="#ECE9D8">
            <td align="center"><a style="text-decoration:none"
                href="<?php echo $default_add . "?ordem=" . $ordem . "&ncamp=ugl_data_inclusao&inicial=" . $inicial . $varsel ?>"
                class="link_branco">Data</a></td>
            <td align="center"><a style="text-decoration:none"
                href="<?php echo $default_add . "?ordem=" . $ordem . "&ncamp=ugl_uglt_id&inicial=" . $inicial . $varsel ?>"
                class="link_branco">Tipo</a> (ord. pelo código)</td>
            <td align="center"><a style="text-decoration:none"
                href="<?php echo $default_add . "?ordem=" . $ordem . "&ncamp=ugl_vg_id&inicial=" . $inicial . $varsel ?>"
                class="link_branco">Pedido</a></td>
            <td align="center"><a style="text-decoration:none"
                href="<?php echo $default_add . "?ordem=" . $ordem . "&ncamp=ugl_ip&inicial=" . $inicial . $varsel ?>"
                class="link_branco">IP</a></td>
            <td align="center">Obs</td>
          </tr>
          <?php if (!$rs_usuario_log || pg_num_rows($rs_usuario_log) == 0) { ?>
            <tr>
              <td align="center" colspan="4">Nenhum histórico encontrado</td>
            </tr>
          <?php } else { ?>
            <?php while ($rs_usuario_log_row = pg_fetch_array($rs_usuario_log)) {
              if ($cor1 == $cor2) {
                $cor1 = $cor3;
              } else {
                $cor1 = $cor2;
              } ?>
              <tr bgcolor="<?php echo $cor1 ?>">
                <td align="center"><?php echo formata_data_ts($rs_usuario_log_row['ugl_data_inclusao'], 0, true, true) ?>
                </td>
                <?php $ugl_uglt_id = $rs_usuario_log_row['ugl_uglt_id']; ?>
                <td><?php echo $GLOBALS['USUARIO_GAMES_LOG_TIPOS_DESCRICAO'][$ugl_uglt_id] ?></td>
                <td align="center"><a style="text-decoration:none"
                    href="/gamer/vendas/com_venda_detalhe.php?venda_id=<?php echo $rs_usuario_log_row['ugl_vg_id'] ?>"><?php echo $rs_usuario_log_row['ugl_vg_id'] ?></a>
                </td>
                <td align="center"><?php echo $rs_usuario_log_row['ugl_ip'] ?></td>
                <td align="center"><?php echo $rs_usuario_log_row['ugl_obs'] ?></td>
              </tr>
            <?php } ?>
            <?php	//paginacao_query($inicial, $total_table, $max, '4', $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp, $varsel."&inicial_v=".$inicial_v); ?>
          <?php } ?>
        </table>
      </div>

      <div class="col-md-12 bg-cinza-claro top20 txt-preto">
        <p class="top20">Histórico de Bloqueio para Utilização de Saldo</p>
      </div>
      <?php
      $varsel_bloqueio = "&usuario_id=" . $usuario_id;
      if (!$ncamp_bloqueio)
        $ncamp_bloqueio = "ughb_data";
      if (!$ordem_bloqueio)
        $ordem_bloqueio = 1;
      if (!$inicial_bloqueio)
        $inicial_bloqueio = 0;
      $sql = "select * from usuarios_games_historico_bloqueio " .
        "where ughb_ug_id = " . $usuario_id;
      //$rs_usuario_bloqueio = SQLexecuteQuery($sql);
      //$total_table = pg_num_rows($rs_usuario_bloqueio);
      $sql .= " order by " . $ncamp_bloqueio . " " . ($ordem_bloqueio == 1 ? "desc" : "asc");
      //$sql .= " limit " . $max . " offset " . $inicial_bloqueio;
      $sql .= " limit 5;";
      //echo $sql;
      $rs_usuario_bloqueio = SQLexecuteQuery($sql);
      $total_table = pg_num_rows($rs_usuario_bloqueio);
      ?>
      <div id="Layer1" class="" style="position:static; width:100%; height:150px; z-index:1; overflow: auto;">
        <table class="table">
          <?php $ordem_bloqueio = ($ordem_bloqueio == 1) ? 2 : 1; ?>
          <tr bgcolor="#ECE9D8">
            <td align="center"><a style="text-decoration:none"
                href="<?php echo $default_add . "?ordem_bloqueio=" . $ordem_bloqueio . "&ncamp_bloqueio=ughb_data&inicial_bloqueio=" . $inicial_bloqueio . $varsel_bloqueio ?>"
                class="link_branco">Data</a></td>
            <td align="center"><a style="text-decoration:none"
                href="<?php echo $default_add . "?ordem_bloqueio=" . $ordem_bloqueio . "&ncamp_bloqueio=ughb_autor&inicial_bloqueio=" . $inicial_bloqueio . $varsel_bloqueio ?>"
                class="link_branco">Usuário DB</a></td>
            <td align="center"><a style="text-decoration:none"
                href="<?php echo $default_add . "?ordem_bloqueio=" . $ordem_bloqueio . "&ncamp_bloqueio=ughb_ug_flag_usando_saldo&inicial_bloqueio=" . $inicial_bloqueio . $varsel_bloqueio ?>"
                class="link_branco">Status</a></td>
          </tr>
          <?php if (!$rs_usuario_bloqueio || pg_num_rows($rs_usuario_bloqueio) == 0) { ?>
            <tr>
              <td align="center" colspan="4">Nenhum histórico encontrado</td>
            </tr>
          <?php } else { ?>
            <?php while ($rs_usuario_bloqueio_row = pg_fetch_array($rs_usuario_bloqueio)) {
              if ($cor1 == $cor2) {
                $cor1 = $cor3;
              } else {
                $cor1 = $cor2;
              } ?>
              <tr bgcolor="<?php echo $cor1 ?>">
                <td align="center"><?php echo formata_data_ts($rs_usuario_bloqueio_row['ughb_data'], 0, true, true) ?></td>
                <td align="center"><?php echo $rs_usuario_bloqueio_row['ughb_autor'] ?></td>
                <td align="center">
                  <?php if ($rs_usuario_bloqueio_row['ughb_ug_flag_usando_saldo'] == 1)
                    echo "Bloqueado";
                  elseif ($rs_usuario_bloqueio_row['ughb_ug_flag_usando_saldo'] == 0)
                    echo "Desbloqueado";
                  else
                    echo "Status NÃO identificado"; ?>
                </td>
              </tr>
            <?php } ?>
            <?php	//echo "$ordem_bloqueio, $total_table, $max, '4', $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp_bloqueio, $varsel_bloqueio.\"&ordem_bloqueio=\".$ordem_bloqueio";
              //	paginacao_query($inicial_bloqueio, $total_table, $max, '4', $img_anterior, $img_proxima, $default_add, $range, $range_qtde, $ncamp_bloqueio, $varsel_bloqueio."&ordem_bloqueio=".$ordem_bloqueio); ?>
          <?php } ?>
        </table>
      </div>

      <div class="col-md-12 bg-cinza-claro txt-preto">
        <p class="top20">Observações</p>
        <textarea cols="40" rows="8" name="ug_obs" disabled class="texto"><?php echo $ug_obs ?></textarea>
      </div>
    </td>
  </tr>
</table>
<?php

require_once $raiz_do_projeto . "backoffice/includes/rodape_bko.php";
?>

</html>