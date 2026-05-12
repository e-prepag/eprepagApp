<?php
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto . "includes/main.php";

if (!headers_sent()) {
	header("Content-Type: text/html; charset=ISO-8859-1", true);
}

if (empty($_SESSION['iduser_bko'])) {
	http_response_code(403);
	die("Acesso negado");
}

const PARTNER_RETURN_ALL_PHRASE = 'quency';

$dd_partner_id = trim((string)($_POST['dd_partner_id'] ?? ''));
$dd_phrase = trim((string)($_POST['dd_phrase'] ?? ''));
$valid_user = !empty($_SESSION['partner_return_all_valid']);

if (!$valid_user && $dd_phrase !== '') {
	$valid_user = hash_equals(PARTNER_RETURN_ALL_PHRASE, $dd_phrase);
	if ($valid_user) {
		$_SESSION['partner_return_all_valid'] = true;
	}
}

$h = static function ($value): string {
	return htmlspecialchars((string)$value, ENT_QUOTES, 'ISO-8859-1');
};

$safeUrl = static function ($url): string {
	$url = trim((string)$url);
	if ($url === '') {
		return '#';
	}

	$scheme = parse_url($url, PHP_URL_SCHEME);
	if (!in_array(strtolower((string)$scheme), array('http', 'https'), true)) {
		return '#';
	}

	return $url;
};

$getPartnerInfo = static function ($partnerId): ?array {
	global $partner_list;

	foreach ((array)$partner_list as $partner) {
		if ((string)($partner['partner_id'] ?? '') === (string)$partnerId) {
			return $partner;
		}
	}

	return null;
};

$isPartnerIdValid = static function ($partnerId) use ($getPartnerInfo): bool {
	return $partnerId !== '' && $getPartnerInfo($partnerId) !== null;
};

if ($dd_partner_id !== '' && !$isPartnerIdValid($dd_partner_id)) {
	$dd_partner_id = '';
	$mensagem_erro = 'Parceiro informado e invalido.';
}

function partner_return_all_get_parceiros(callable $getPartnerInfo): array
{
	$sql = "select ip_store_id as parceiro, count(*) as n
			from tb_integracao_pedido
			group by ip_store_id
			order by ip_store_id";

	$rs = SQLexecuteQuery($sql);
	$parceiros = array();

	if ($rs) {
		while ($row = pg_fetch_assoc($rs)) {
			$partnerId = (string)($row['parceiro'] ?? '');
			$partnerInfo = $getPartnerInfo($partnerId);

			$parceiros[] = array(
				'parceiro' => $partnerId,
				'n' => (int)($row['n'] ?? 0),
				'nome' => (string)($partnerInfo['partner_name'] ?? 'Parceiro desconhecido'),
				'valido' => $partnerInfo !== null,
			);
		}
	}

	usort($parceiros, static function ($a, $b): int {
		$cmp = strcasecmp($a['nome'], $b['nome']);
		if ($cmp !== 0) {
			return $cmp;
		}

		return strnatcasecmp($a['parceiro'], $b['parceiro']);
	});

	return $parceiros;
}

function partner_return_all_payments_list(string $partnerId, callable $h): void
{
	$sql = "select
				ip.ip_id,
				pc.datainicio,
				pc.numcompra,
				pc.total,
				pc.idcliente,
				pc.cliente_nome,
				ip.ip_store_id,
				ip.ip_order_id,
				ip.ip_amount,
				ip.ip_currency_code,
				ip.ip_vg_id,
				ip.ip_client_id,
				ip.ip_client_email,
				pc.status,
				ip.ip_status_confirmed
			from tb_integracao_pedido ip
				left join tb_pag_compras pc on ip.ip_vg_id = pc.idvenda
				inner join tb_venda_games vg on vg.vg_id = pc.idvenda
			where vg.vg_integracao_parceiro_origem_id is not null";

	$params = array();
	if ($partnerId !== '') {
		$params[] = $partnerId;
		$sql .= " and vg.vg_integracao_parceiro_origem_id::text = $" . count($params);
	}

	$sql .= " order by pc.datainicio desc";

	$rs = count($params) ? SQLexecuteQueryParams($sql, $params) : SQLexecuteQuery($sql);
	if (!$rs) {
		echo "<p style='color:red'>Erro ao recuperar transacao de integracao.</p>\n";
		return;
	}

	if (pg_num_rows($rs) === 0) {
		echo "<p>Nenhum registro encontrado.</p>\n";
		return;
	}

	echo "<table cellpadding='2' cellspacing='2' border='1' bordercolor='#cccccc' style='border-collapse:collapse;'>\n";
	echo "<tr style='text-align:center;font-weight:bold'>";
	echo "<td>ID</td><td>data</td><td>numcompra</td><td>total (R$)</td><td>idcliente</td><td>cliente_nome</td>";
	echo "<td>store_id</td><td>order_id</td><td>amount</td><td>curr</td><td>vg_id</td><td>client_id</td><td>client_email</td><td>status</td><td>confirmed</td>";
	echo "</tr>\n";

	while ($row = pg_fetch_assoc($rs)) {
		$status = (string)($row['status'] ?? '');
		$statusColor = getStatusColor($status);
		$statusDescription = getStatusDescription($status);
		$storeId = (string)($row['ip_store_id'] ?? '');
		$confirmed = ((string)($row['ip_status_confirmed'] ?? '') === '1');

		echo "<tr>";
		echo "<td>" . $h($row['ip_id'] ?? '') . "</td>";
		echo "<td><nobr>" . $h($row['datainicio'] ?? '') . "</nobr></td>";
		echo "<td>" . $h($row['numcompra'] ?? '') . "</td>";
		echo "<td align='right'>" . number_format(((float)($row['total'] ?? 0) / 100), 2, ',', '.') . "</td>";
		echo "<td align='center'>" . $h($row['idcliente'] ?? '') . "</td>";
		echo "<td>" . $h($row['cliente_nome'] ?? '') . "</td>";
		echo "<td align='center'><nobr>" . $h(getPartner_name_By_ID($storeId)) . " (" . $h($storeId) . ")</nobr></td>";
		echo "<td>" . $h($row['ip_order_id'] ?? '') . "</td>";
		echo "<td>" . $h($row['ip_amount'] ?? '') . "</td>";
		echo "<td align='center'>" . $h($row['ip_currency_code'] ?? '') . "</td>";
		echo "<td>" . $h($row['ip_vg_id'] ?? '') . "</td>";
		echo "<td>" . $h($row['ip_client_id'] ?? '') . "</td>";
		echo "<td>" . $h($row['ip_client_email'] ?? '') . "</td>";
		echo "<td title='status: " . $h($status) . "' align='center'><font color='" . $h($statusColor) . "'>" . $h($statusDescription) . "</font></td>";
		echo "<td align='center'><font color='" . ($confirmed ? "#0000FF" : "#CCCCFF") . "'>" . ($confirmed ? "Sim" : "Nao") . "</font></td>";
		echo "</tr>\n";
	}

	echo "</table>\n";
}

$rs_parceiros = $valid_user ? partner_return_all_get_parceiros($getPartnerInfo) : array();
$partnerInfo = ($valid_user && $dd_partner_id !== '') ? $getPartnerInfo($dd_partner_id) : null;
$partnerName = $partnerInfo ? (string)$partnerInfo['partner_name'] : 'Todos os parceiros';
$partnerUrl = $partnerInfo ? $safeUrl($partnerInfo['partner_url'] ?? '') : '#';
?>
<!DOCTYPE html>
<html>
<head>
	<title>Return from E-Prepag</title>
	<style>
		body,
		p,
		a,
		td,
		th,
		b {
			font-family: tahoma, arial;
			font-size: 10px;
		}

		input.main,
		select.main {
			color: #0000CC;
			background-color: #FFFFCC;
			text-align: right;
		}
	</style>
</head>
<body>
<?php if ($valid_user) { ?>
	<h3><font face="arial, sans serif"><?php echo $h($partnerName); ?> - Return from E-Prepag</font></h3>

	<?php if ($partnerInfo && $partnerUrl !== '#') { ?>
		<p>
			<font face="arial, sans serif">
				<a href="<?php echo $h($partnerUrl); ?>" rel="noopener noreferrer" target="_blank">Go to the initial test page for <?php echo $h($partnerName); ?></a>
			</font>
		</p>
	<?php } ?>

	<p><font face="arial, sans serif">Return from a payment request</font></p>
	<p><font face="arial, sans serif"><?php echo $h(date("Y-m-d H:i:s")); ?></font></p>

	<?php if (!empty($mensagem_erro)) { ?>
		<p style="color:red"><?php echo $h($mensagem_erro); ?></p>
	<?php } ?>

	<form method="post" action="partner_return_all.php">
		Partner:
		<select name="dd_partner_id" class="form2">
			<option value="" <?php echo ($dd_partner_id === '') ? "selected" : ""; ?>>Todos</option>
			<?php foreach ($rs_parceiros as $parceiro) { ?>
				<?php
				$partnerId = (string)$parceiro['parceiro'];
				$label = $parceiro['nome'] . " (ID: " . $partnerId . ") " . $parceiro['n'] . " registro" . (($parceiro['n'] > 1) ? "s" : "");
				if (!$parceiro['valido']) {
					$label .= " - nao configurado";
				}
				?>
				<option value="<?php echo $h($partnerId); ?>" <?php echo ($dd_partner_id === $partnerId) ? "selected" : ""; ?>><?php echo $h($label); ?></option>
			<?php } ?>
		</select>
		<br>
		<input type="submit" value="Atualiza">
	</form>

	<?php partner_return_all_payments_list($dd_partner_id, $h); ?>
<?php } else { ?>
	<h3 style="color:red">Unknown user (partner)</h3>
	<form method="post" action="partner_return_all.php">
		<input type="password" name="dd_phrase" id="dd_phrase" value="" autocomplete="off">
		<input type="submit" value="manda">
	</form>
<?php } ?>
</body>
</html>
