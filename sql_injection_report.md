# SQL Injection Audit

scope: `www/public_html`

files_scanned: 434

### www/public_html/teste.php

[MEDIUM] line 190
code:
```php
$pdo->exec($statement);
```
risk:
raw SQL file statement exec

suggest:
offline only / validate source / params

[LOW] line 167
code:
```php
UPDATE {$table}
```
risk:
dynamic table/columns from parsed SQL

suggest:
allowlist table/columns

### www/public_html/sys/admin/vendas/vendas_estab/exportar_excel.php

[HIGH] line 19
code:
```php
$sql = base64_decode($_POST['sql'] ?? "", true);
```
risk:
POST supplies SQL text

suggest:
server-side query id / params

[HIGH] line 24
code:
```php
$res = SQLexecuteQuery($sql);
```
risk:
executes user SELECT via blocklist

suggest:
prepared export query

### www/public_html/creditos/pagamento/finaliza_venda.php

[HIGH] line 212
code:
```php
$sql = "SELECT * FROM tb_pag_compras WHERE numCompra='" . $numOrder . "'";
```
risk:
concat payment order in SQL

suggest:
params

[HIGH] line 226
code:
```php
$sql = "UPDATE tb_pag_compras SET cliente_nome='" . str_replace("'", "''", $usuarioGames->getNome()) . "', idcliente=" . $usuarioGames->getId() . ", status=1, cesta='" . str_replace("'", "''", montaCesta_pag()) . "', total=" . (100 * ($total_carrinho + $taxas)) . " WHERE numcompra='" . $numOrder . "'";
```
risk:
manual escaping + concat UPDATE

suggest:
params

[HIGH] line 456
code:
```php
$sql = "UPDATE tb_pag_compras SET idvenda=" . $venda_id . " WHERE numcompra='" . $numOrder . "'";
```
risk:
concat UPDATE payment order

suggest:
params

[HIGH] line 536
code:
```php
$sql .= ", '" . $GLOBALS['_SESSION']['NOME_CPF'] . "', '" . $GLOBALS['_SESSION']['CPF_LH'] . "',to_date('" . $GLOBALS['_SESSION']['DATA_NASCIMENTO'] . "','DD/MM/YYYY')";
```
risk:
session/request-derived CPF fields concat

suggest:
params

[HIGH] line 573
code:
```php
$sql .= ") VALUES (" . $venda_id . ", " . $codeProd . ", '" . $rs_row['ogp_nome'] . "', ...";
```
risk:
large INSERT concat with mixed values

suggest:
params

[HIGH] line 576
code:
```php
$sql .= ", '" . $GLOBALS['_SESSION']['NOME_CPF'] . "', '" . $GLOBALS['_SESSION']['CPF_LH'] . "',to_date('" . $GLOBALS['_SESSION']['DATA_NASCIMENTO'] . "','DD/MM/YYYY')";
```
risk:
session/request-derived CPF fields concat

suggest:
params

### www/public_html/creditos/conta/venda_detalhe.php

OK

### www/public_html/busca-pdv.php

OK

### www/public_html/completar_cadastro.php

OK

### www/public_html/canal-de-denuncia.php

OK

### www/public_html/sys/admin/commerce/com_pesquisa_integracao.php

OK

### www/public_html/sys/admin/vendas_cartoes/vendas_estab/pquery.php

OK

### www/public_html/sys/admin/vendas_cartoes/agrupado/vendas_ano.php

OK

### www/public_html/ajax/gamer/boleto_express_finaliza.php

OK

### www/public_html/ajax/pdv/finaliza_vendaExLH.php

OK

### www/public_html/game/conta/detalhe-pedido.php

OK

### www/public_html/game/pagamento/pagto_compr_online.php

OK

### www/public_html/prepag2/commerce/conta/pagto_compr_online.php

OK

### FINAL SUMMARY

files_scanned: 434
files_with_risk: 3
findings:
- HIGH: 8
- MEDIUM: 1
- LOW: 1

top_risks:
- POST SQL export
- finaliza_venda raw payment SQL
- teste.php raw SQL exec

next_step:
review HIGH first
