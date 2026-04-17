<?php
require_once '../../../includes/constantes.php';
require_once $raiz_do_projeto . "backoffice/includes/topo.php";
require_once $raiz_do_projeto . "includes/main.php";
require_once $raiz_do_projeto . "includes/pdv/main.php";

$time_start = getmicrotime();

// Inicialização segura de variáveis do POST/GET
$ncamp     = $_REQUEST['ncamp'] ?? 'vg_valor';
$inicial   = isset($_REQUEST['inicial']) ? (int)$_REQUEST['inicial'] : 0;
$range     = isset($_REQUEST['range']) ? (int)$_REQUEST['range'] : 1;
$ordem     = isset($_REQUEST['ordem']) ? (int)$_REQUEST['ordem'] : 1;
$msg       = $_REQUEST['msg'] ?? '';
$BtnSearch = $_REQUEST['BtnSearch'] ?? null;

$default_add  = nome_arquivo($PHP_SELF);
$img_proxima  = "https://" . $_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'] . "/images/proxima.gif";
$img_anterior = "https://" . $_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'] . "/images/anterior.gif";
$max          = 300;
$range_qtde   = isset($qtde_range_tela) ? $qtde_range_tela : 10;

// Cria instância com LH teste
$usuarioGames = new UsuarioGames(468);

$totalPages = 0;
$currentPage = 1;

$sql  = "SELECT ug.ug_id, ug.ug_login, ug.ug_email, v.vg_valor, v.vg_qtde_itens, v.vg_data_primeira_venda, v.vg_data_ultima_venda, " . PHP_EOL;
$sql .= "   (EXTRACT(epoch FROM (v.vg_data_ultima_venda - v.vg_data_primeira_venda))/(24*3600)) AS ndays, " . PHP_EOL;
$sql .= "   (COALESCE((EXTRACT(epoch FROM (v.vg_data_ultima_venda - v.vg_data_primeira_venda))/(24*3600)), 1) / NULLIF(v.vg_qtde_itens, 0)) AS ndays_per_venda, " . PHP_EOL;
$sql .= "   v.vg_valor_inc, v.vg_qtde_itens_inc, v.vg_data_primeira_venda_inc, v.vg_data_ultima_venda_inc " . PHP_EOL;
$sql .= "FROM dist_usuarios_games ug " . PHP_EOL;
$sql .= "LEFT JOIN ( " . PHP_EOL;
$sql .= "   SELECT vg.vg_ug_id, " . PHP_EOL;
$sql .= "       SUM(CASE WHEN vg.vg_ultimo_status = 5 THEN vgm.vgm_valor * vgm.vgm_qtde ELSE 0 END) AS vg_valor, " . PHP_EOL;
$sql .= "       SUM(CASE WHEN vg.vg_ultimo_status = 5 THEN vgm.vgm_qtde ELSE 0 END) AS vg_qtde_itens, " . PHP_EOL;
$sql .= "       MIN(CASE WHEN vg.vg_ultimo_status = 5 THEN vg.vg_data_inclusao END) AS vg_data_primeira_venda, " . PHP_EOL;
$sql .= "       MAX(CASE WHEN vg.vg_ultimo_status = 5 THEN vg.vg_data_inclusao END) AS vg_data_ultima_venda, " . PHP_EOL;
$sql .= "       SUM(CASE WHEN vg.vg_ultimo_status = 6 THEN vgm.vgm_valor * vgm.vgm_qtde ELSE 0 END) AS vg_valor_inc, " . PHP_EOL;
$sql .= "       SUM(CASE WHEN vg.vg_ultimo_status = 6 THEN vgm.vgm_qtde ELSE 0 END) AS vg_qtde_itens_inc, " . PHP_EOL;
$sql .= "       MIN(CASE WHEN vg.vg_ultimo_status = 6 THEN vg.vg_data_inclusao END) AS vg_data_primeira_venda_inc, " . PHP_EOL;
$sql .= "       MAX(CASE WHEN vg.vg_ultimo_status = 6 THEN vg.vg_data_inclusao END) AS vg_data_ultima_venda_inc " . PHP_EOL;
$sql .= "   FROM tb_dist_venda_games vg " . PHP_EOL;
$sql .= "   INNER JOIN tb_dist_venda_games_modelo vgm ON vg.vg_id = vgm.vgm_vg_id " . PHP_EOL;
$sql .= "   WHERE vg.vg_ultimo_status IN (5, 6) " . PHP_EOL;
$sql .= "       AND vg.vg_ug_id IN (SELECT ug_id FROM dist_usuarios_games WHERE ug_vip = 5) " . PHP_EOL;
$sql .= "   GROUP BY vg.vg_ug_id " . PHP_EOL;
$sql .= ") v ON v.vg_ug_id = ug.ug_id " . PHP_EOL;
$sql .= "WHERE ug.ug_vip = 5 " . PHP_EOL;

if ($ordem == 1) {
    $sql .= " order by " . $ncamp . " desc; ";
} else {
    $sql .= " order by " . $ncamp . " asc; ";
}

$sql_limit = str_replace(";", "", $sql);
$sql_limit .= " limit " . $max . " offset " . $inicial . ";";

$sql_count = "SELECT count(ug_id) as total FROM dist_usuarios_games WHERE ug_vip = 5;";
$rs_count = SQLexecuteQuery($sql_count);
$row_count = pg_fetch_array($rs_count);
$total_table = $row_count['total'];

// Cálculos para paginação
$totalPages = ceil($total_table / $max);
$currentPage = floor($inicial / $max) + 1;

// Forma mais limpa de calcular o "Exibindo resultados X a Y"
$reg_ate = min($max + $inicial, $total_table);

// Executa a query COM os limites da página atual
$rs = SQLexecuteQuery($sql_limit);

ob_end_flush();
?>
<script language="javascript">
    function GP_popupAlertMsg(msg) {
        document.MM_returnValue = alert(msg);
    }

    function GP_popupConfirmMsg(msg) {
        document.MM_returnValue = confirm(msg);
    }
</script>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo isset($currentAba) ? $currentAba->getOrdem() : ''; ?>">BackOffice - <?php echo isset($currentAba) ? $currentAba->getDescricao() : ''; ?></a></li>
        <li class="active"><?php echo isset($sistema) ? $sistema->menu[0]->getDescricao() : ''; ?></li>
        <li class="active"><a href="<?php echo isset($sistema) ? $sistema->item->getLink() : ''; ?>"><?php echo isset($sistema) ? $sistema->item->getDescricao() : ''; ?></a></li>
    </ol>
</div>

<form name="form1" method="post" action="com_pesquisa_usuarios_platinum.php">
    <input type="hidden" name="ncamp" value="<?php echo htmlspecialchars($ncamp); ?>">
    <input type="hidden" name="ordem" value="<?php echo htmlspecialchars($ordem); ?>">
    <input type="hidden" name="range" value="<?php echo htmlspecialchars($range); ?>">

    <table class="table">
        <tr bgcolor="#F5F5FB">
            <td align="right"><input type="submit" name="BtnSearch" value="Buscar" class="btn btn-info"></td>
        </tr>
        <?php if ($msg != "") { ?>
            <tr class="texto">
                <td align="center"><br><br>
                    <font color="#FF0000"><?php echo htmlspecialchars($msg); ?></font>
                </td>
            </tr>
        <?php } ?>
    </table>
</form>



<table class="fontsize-pp txt-preto">
    <tr>
        <td valign="top">
            <table class="table">
                <tr bgcolor="#ECE9D8" class="">
                    <?php include "categorias_usuarios.php";?>
                </tr>
            </table>

            <?php if ($total_table > 0) { ?>
                <table class="table">
                    <tr bgcolor="#00008C">
                        <td height="11" colspan="3" bgcolor="#FFFFFF">
                            <table class="">
                                <tr>
                                    <td colspan="20" class="texto">
                                        Exibindo resultados <strong><?php echo $inicial + 1 ?></strong>
                                        a <strong><?php echo $reg_ate ?></strong> de <strong><?php echo $total_table ?></strong>
                                    </td>
                                </tr>
                                <?php $ordem_inversa = ($ordem == 1) ? 2 : 1; ?>
                                <tr bgcolor="#ECE9D8" class="texto">
                                    <td align="center" colspan="3">&nbsp;</td>
                                    <td align="center" colspan="5"><b>Vendas Completas</b></td>
                                    <td align="center"><strong>
                                            <font class="texto">&nbsp;</font>
                                        </strong></td>
                                    <td align="center" colspan="4"><b>Vendas Incompletas</b></td>
                                </tr>
                                <tr bgcolor="#ECE9D8" class="texto">
                                    <td align="center"><b>Código</b></td>
                                    <td align="center"><b>Login</b></td>
                                    <td align="center"><b>Email</b></td>

                                    <td align="center"><strong>
                                            <font class="texto">
                                                <nobr>Vendas R$</nobr>
                                            </font>
                                        </strong></td>
                                    <td align="center"><strong>
                                            <font class="texto">
                                                <nobr>n Vendas</nobr>
                                            </font>
                                        </strong></td>
                                    <td align="center"><strong>
                                            <font class="texto">
                                                <nobr>Ticket médio</nobr>
                                            </font>
                                        </strong></td>
                                    <td align="center"><strong>
                                            <font class="texto">
                                                <nobr>Data última venda</nobr>
                                            </font>
                                        </strong></td>
                                    <td align="center"><strong>
                                            <font class="texto">
                                                <nobr>Status</nobr>
                                            </font>
                                        </strong></td>

                                    <td align="center"><strong>
                                            <font class="texto">&nbsp;</font>
                                        </strong></td>

                                    <td align="center"><strong>
                                            <font class="texto">
                                                <nobr>Pedidos R$</nobr>
                                            </font>
                                        </strong></td>
                                    <td align="center"><strong>
                                            <font class="texto">
                                                <nobr>n Pedidos</nobr>
                                            </font>
                                        </strong></td>
                                    <td align="center"><strong>
                                            <font class="texto">
                                                <nobr>Ticket médio</nobr>
                                            </font>
                                        </strong></td>
                                    <td align="center"><strong>
                                            <font class="texto">
                                                <nobr>Data último pedido</nobr>
                                            </font>
                                        </strong></td>
                                </tr>
                                <?php
                                $cor1 = isset($query_cor1) ? $query_cor1 : '#FFFFFF';
                                $cor2 = isset($query_cor1) ? $query_cor1 : '#FFFFFF';
                                $cor3 = isset($query_cor2) ? $query_cor2 : '#F5F5F5';

                                if (((($rs) ? pg_num_rows($rs) : 0) != 0) && ($rs)) {
                                    while ($pgrs = pg_fetch_array($rs)) {
                                        @$taxa_aproveitamento = 100. * $pgrs['vg_valor'] / ($pgrs['vg_valor'] + $pgrs['vg_valor_inc']);
                                ?>
                                        <tr bgcolor="<?php echo $cor1 ?>" class="texto" title="Taxa de aproveitamento: <?php echo number_format($taxa_aproveitamento, 2, ',', '.') ?>%">
                                            <td align="center"><a href="com_usuario_detalhe.php?usuario_id=<?php echo $pgrs['ug_id']; ?>" target="_blank"><?php echo $pgrs['ug_id']; ?></a></td>
                                            <td align="center" style="max-width: 350px;font-size: 10px;">
                                                <nobr><?php echo trim((($pgrs['ug_login']) ? $pgrs['ug_login'] : "-")) ?></nobr>
                                            </td>
                                            <td align="center" style="max-width: 310px;font-size: 10px;"><?php echo trim((string)($pgrs['ug_email'] ?? "")); ?></td>
                                            <?php
                                            $vg_qtde_itens = (($pgrs['vg_qtde_itens'] > 0) ? $pgrs['vg_qtde_itens'] : 1);
                                            ?>
                                            <td align="right"><?php echo number_format($pgrs['vg_valor'], 2, ',', '.') ?></td>
                                            <td align="right"><?php echo $vg_qtde_itens ?></td>
                                            <td align="right"><?php echo number_format($pgrs['vg_valor'] / $vg_qtde_itens, 2, ',', '.') ?></td>
                                            <td align="right" title="Primeira venda: '<?php echo substr($pgrs['vg_data_primeira_venda'], 0, 19) ?>'
Dias entre 1a e última vendas: <?php echo number_format($pgrs['ndays'], 2, ',', '.') ?> 
Média de dias por venda: <?php echo number_format($pgrs['ndays_per_venda'], 2, ',', '.') ?>">
                                                <nobr><?php echo substr($pgrs['vg_data_ultima_venda'], 0, 19) ?></nobr>
                                            </td>
                                            <?php
                                            $status = qtde_dias(substr($pgrs['vg_data_ultima_venda'], 8, 2) . "-" . substr($pgrs['vg_data_ultima_venda'], 5, 2) . "-" . substr($pgrs['vg_data_ultima_venda'], 0, 4), date('d-m-Y'));
                                            if ($status <= $ATRASO_LANS_DIAS_LIM_1) {
                                                $status_label   =   "<font color='#66CC00'>Frequente</font>";
                                            } elseif ($status > $ATRASO_LANS_DIAS_LIM_1 && $status <= $ATRASO_LANS_DIAS_LIM_2) {
                                                $status_label   =   "<font color='#FFCC00'>Atrasado</font>";
                                            } elseif ($status > $ATRASO_LANS_DIAS_LIM_2) {
                                                $status_label   =   "<font color='red'>Abandonou</font>";
                                            }
                                            ?>
                                            <td align="right" title="<?php echo $status . " dias sem comprar" ?>"><?php echo $status_label ?></td>

                                            <td align=""><strong>
                                                    <font class="texto">&nbsp;</font>
                                                </strong></td>

                                            <?php
                                            $vg_qtde_itens_inc = (($pgrs['vg_qtde_itens_inc'] > 0) ? $pgrs['vg_qtde_itens_inc'] : 1);
                                            ?>
                                            <td align="right"><?php echo number_format($pgrs['vg_valor_inc'], 2, ',', '.') ?></td>
                                            <td align="right"><?php echo $vg_qtde_itens_inc ?></td>
                                            <td align="right"><?php echo number_format($pgrs['vg_valor_inc'] / $vg_qtde_itens_inc, 2, ',', '.') ?></td>
                                            <td align="right" title="Primeira venda: '<?php echo substr($pgrs['vg_data_primeira_venda_inc'], 0, 19) ?>'">
                                                <nobr><?php echo substr($pgrs['vg_data_ultima_venda_inc'], 0, 19) ?></nobr>
                                            </td>
                                        </tr>
                                <?php
                                    }
                                }
                                ?>
                                <tr>
                                    <td colspan="13" bgcolor="#FFFFFF" class="texto">
                                        <?php echo (isset($search_msg) ? $search_msg : "") . number_format(getmicrotime() - $time_start, 2, ',', '.') . (isset($search_unit) ? $search_unit : "s"); ?>
                                    </td>
                                </tr>
                            <?php
                        } else {
                            ?>
                                <tr bgcolor="#ECE9D8" class="texto">
                                    <td align="center" colspan="13"><b>Não foram encontrados registros</b></td>
                                </tr>
                            <?php
                        }
                            ?>
                            </table>
                        </td>
                    </tr>
                </table>

                <?php if ($totalPages > 1): ?>
                    <div style="margin-top: 20px; text-align: center;">
                        <ul class="pagination">
                            <li class="<?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                                <?php $prevInicial = max(0, $inicial - $max); ?>
                                <a href="?inicial=<?php echo $prevInicial; ?>&ncamp=<?php echo htmlspecialchars($ncamp); ?>&ordem=<?php echo htmlspecialchars($ordem); ?>&range=<?php echo htmlspecialchars($range); ?>" aria-label="Anterior">
                                    <span aria-hidden="true">&laquo; Anterior</span>
                                </a>
                            </li>

                            <?php
                            // Opcional: limitar exibição para não quebrar layout caso haja muitas páginas (ex: exibir apenas +- 5 págs ao redor da atual)
                            $startPage = max(1, $currentPage - 5);
                            $endPage = min($totalPages, $currentPage + 5);

                            for ($i = $startPage; $i <= $endPage; $i++):
                                $calcInicial = ($i - 1) * $max;
                                $activeClass = ($currentPage == $i) ? 'active' : '';
                            ?>
                                <li class="<?php echo $activeClass; ?>">
                                    <a href="?inicial=<?php echo $calcInicial; ?>&ncamp=<?php echo htmlspecialchars($ncamp); ?>&ordem=<?php echo htmlspecialchars($ordem); ?>&range=<?php echo htmlspecialchars($range); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <li class="<?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                                <?php $nextInicial = min(($totalPages - 1) * $max, $inicial + $max); ?>
                                <a href="?inicial=<?php echo $nextInicial; ?>&ncamp=<?php echo htmlspecialchars($ncamp); ?>&ordem=<?php echo htmlspecialchars($ordem); ?>&range=<?php echo htmlspecialchars($range); ?>" aria-label="Próxima">
                                    <span aria-hidden="true">Próxima &raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php endif; ?>
        </td>
    </tr>
</table>
<?php
require_once $raiz_do_projeto . "backoffice/includes/rodape_bko.php";
?>

