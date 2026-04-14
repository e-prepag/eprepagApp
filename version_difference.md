📘 Migração PHP 7.3 → PHP 8.4
📌 Visão geral

Essa migração envolve:

⚠️ Breaking changes importantes
🚀 Melhorias de performance (JIT, engine nova)
🔒 Mais tipagem e segurança
🧩 Mudanças em extensões (PDO, pgsql, curl, gd, etc.)
⚠️ BREAKING CHANGES PRINCIPAIS
❌ Funções removidas / depreciadas
Removidas
each() ❌
create_function() ❌
ereg_* (já deprecated antes, agora removidas) ❌
Mudança de comportamento
Strings não são mais automaticamente convertidas para número em comparações:
"10abc" == 10 // PHP 7.3: true
              // PHP 8+: false
⚠️ Erros agora são Exceptions (Fatal mudou)

Muitas coisas que eram warnings agora quebram:

strlen([]);

// PHP 7.3 → Warning
// PHP 8+ → TypeError (fatal)
⚠️ Ordem de parâmetros

Parâmetros opcionais antes de obrigatórios agora geram erro:

function teste($opcional = null, $obrigatorio) {} // ❌ PHP 8+
🚀 NOVAS FEATURES IMPORTANTES
🧠 JIT (PHP 8.0+)
Melhora performance CPU-bound
Pouco impacto em aplicações web tradicionais
🔤 Tipagem forte melhorada
Union Types (PHP 8.0)
function test(int|string $id) {}
Mixed type
function test(mixed $value) {}
Return types mais rígidos
🧱 Attributes (Annotations nativas)
#[Route('/api')]
function index() {}
🧩 Named Arguments
foo(name: "José", age: 30);
🔁 Match expression
$result = match($status) {
    1 => 'ok',
    2 => 'erro',
};
🧬 Constructor Property Promotion
class User {
    public function __construct(
        public string $name
    ) {}
}
🔒 Nullsafe operator
$user?->profile?->email;
🧵 Fibers (PHP 8.1)

Base para async (ReactPHP, etc.)

📦 Enums (PHP 8.1)
enum Status {
    case ACTIVE;
    case INACTIVE;
}
🧠 Readonly properties (PHP 8.1+)
class User {
    public readonly string $name;
}
🧱 Classes finalizadas (PHP 8.2+)
readonly class
true, false como tipos
🧬 PHP 8.3 / 8.4 (principais)
PHP 8.3
json_validate()
Constantes tipadas
Melhorias em DateTime
PHP 8.4 (preview geral)
Property hooks (get/set direto)
Melhorias em performance e tipagem
🧩 PDO (MUDANÇAS IMPORTANTES)
⚠️ Default mudou (CRÍTICO)
Antes (PHP 7.3)
PDO::ATTR_ERRMODE = PDO::ERRMODE_SILENT
Agora (PHP 8+)
PDO::ATTR_ERRMODE = PDO::ERRMODE_EXCEPTION

👉 Isso quebra código antigo silenciosamente.

⚠️ Fetch mode mais rígido
$stmt->fetch(PDO::FETCH_ASSOC);
Tipos inválidos agora geram erro
Mais validação interna
⚠️ Tipagem mais forte
Parâmetros inválidos → TypeError
bindParam() exige tipo correto
⚠️ Emulação de prepared statements

Dependendo do driver:

PDO::ATTR_EMULATE_PREPARES
Comportamento mais consistente
Melhor segurança contra SQL Injection
🐘 PostgreSQL (pgsql / pdo_pgsql)
⚠️ Mudanças importantes
Tipagem mais rígida
pg_query($conn, []);
// PHP 7.3: warning
// PHP 8+: TypeError
⚠️ Retornos mais previsíveis
false vs resource agora mais consistente
Funções retornam tipos mais corretos
⚠️ Conversão automática menor

Antes:

pg_query($conn, "SELECT " . $_GET['id']);

Agora:

Mais propenso a erro → exige sanitização melhor
🔒 Segurança
Incentivo forte ao uso de:
pg_query_params()
⚠️ pdo_pgsql
Melhor compatibilidade com tipos PostgreSQL
Erros mais explícitos
Timezones mais corretos
🧩 EXTENSÕES DO SEU DOCKER
📦 zip / libzip
Mudanças
Necessário libzip >= 1.2
Melhor suporte a streams
🌐 curl
Mudanças
Tipagem mais rígida
curl_init() retorna objeto (CurlHandle)
$ch = curl_init(); // agora objeto, não resource
🖼️ GD
Mudanças importantes
Agora usa objetos ao invés de resources:
$image = imagecreate(...);
// PHP 7 → resource
// PHP 8 → GdImage
🗄️ mysqli
Mudanças
Exceptions podem ser ativadas:
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
Melhor tipagem
🐬 pdo_mysql
Mesmo comportamento do PDO geral
Mais rigor em tipos
Melhor suporte a charset
🐘 pgsql / pdo_pgsql

(já explicado acima)

🧵 xml (libxml2)
Mudanças
Mais validação
Errors como exceptions em alguns casos
🖼️ freetype / jpeg / png (gd)

Sem grandes mudanças diretas, mas:

Build do GD mudou (configure obrigatório)
Melhor compatibilidade com imagens modernas
🌐 openssl / certificados
TLS mais restritivo
Certificados inválidos falham mais facilmente
⚠️ OUTROS PONTOS CRÍTICOS
🔐 Sessões
Mais seguras por padrão
SameSite aplicado automaticamente
🧠 Comparações
0 == "abc" // PHP 7: true
           // PHP 8: false
🔢 Funções matemáticas e strings
Mais validação
Menos conversão automática
✅ CHECKLIST DE MIGRAÇÃO
🔥 Obrigatório revisar
 PDO error mode
 Comparações (==)
 Funções que recebem arrays/strings
 Uso de resource (agora objetos)
 curl, gd, pgsql
 prepared statements
 Tipagem de funções
🚀 Recomendado
 Usar declare(strict_types=1);
 Migrar para exceptions
 Usar enums
 Usar typed properties
💡 DICA FINAL (IMPORTANTE)

Como você tem código legado (PHP 5.6/7.x), os maiores riscos são:

⚠️ Comparações fracas (==)
⚠️ PDO silencioso → agora quebra
⚠️ Tipos inválidos virando fatal
⚠️ Resources → Objects