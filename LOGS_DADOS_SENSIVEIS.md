## Mapeamento inicial de logs com dados sensíveis

**Objetivo**: apontar arquivos de log e pontos de código que **podem registrar dados sensíveis** (CPF, PIN/pin_codigo, credenciais, IP, tokens de reset de senha, etc.) para futura correção/anonimização.

> Observação: este documento é apenas um levantamento estático do código. Ainda é necessário validar cada caso em produção/homologação.

---

### 1. Cadastro / Conta Gamer

- **Log de cadastro de conta gamer**
  - **Arquivo de código**: `www/public_html/game/conta/nova.php`
  - **Arquivo de log**: `/www/arquivos_gerados/logs/cadastro_games.txt`
  - **Como é gravado**:
    - Ao receber `POST` com criação de conta, o sistema grava a data/hora e os dados recebidos.
    - Campos sensíveis são explicitamente filtrados antes do `json_encode`:
      - `senha`, `conf_senha`, `cpf`, `data_nascimento`, `telefone`, `celular`, `ipAdress`, `location`, `device`, `g-recaptcha-response` são substituídos por `***REMOVIDO***`.
  - **Risco**:
    - **Baixo para CPF/senha**, pois são mascarados.
    - Ainda assim, o log contém outros dados pessoais (ex.: e‑mail, possivelmente nome) → precisa de avaliação LGPD.

---

### 2. Fluxos de “Esqueci minha senha” (Gamer e PDV)

- **Logs de solicitações e alterações de senha – Gamer**
  - **Arquivos de código**:
    - `www/public_html/game/conta/esqueci-minha-senha/functions-esqueci-minha-senha.php`
    - `www/public_html/game/conta/esqueci-minha-senha/atualizacao.php`
    - `www/public_html/game/conta/esqueci-minha-senha/atualiza-senha.php`
    - `www/public_html/game/conta/esqueci-minha-senha/configuracoes-email.php`
  - **Arquivos de log**:
    - `/www/arquivos_gerados/logs/logEsqueciMinhaSenha.log`
    - `/www/arquivos_gerados/logs/envioEmailEsqueciMinhaSenha.log`
  - **Dados registrados** (exemplos extraídos do código):
    - ID do usuário (`ug_id`)
    - IP do usuário
    - Código de validação de troca de senha (`codigoValidacao` – token)
    - Mensagens de status (acesso negado, código inválido, senha atualizada, etc.)
  - **Risco**:
    - **Token de reset de senha** é altamente sensível (pode permitir troca de senha se reaproveitado).
    - IP + ID de usuário são dados pessoais.

- **Logs de solicitações e alterações de senha – PDV (créditos)**
  - **Arquivos de código**:
    - `www/public_html/creditos/esqueci-minha-senha/functions-esqueci-minha-senha.php`
    - `www/public_html/creditos/esqueci-minha-senha/atualizacao.php`
    - `www/public_html/creditos/esqueci-minha-senha/atualiza-senha.php`
    - `www/public_html/creditos/esqueci-minha-senha/configuracoes-email.php`
  - **Arquivos de log**:
    - `/www/arquivos_gerados/logs/logEsqueciMinhaSenha_pdv.log`
    - `/www/arquivos_gerados/logs/envioEmailEsqueciMinhaSenha_pdv.log`
  - **Dados registrados**:
    - ID do usuário PDV
    - Código de validação de troca de senha (token)
    - Mensagens sobre tentativa de atualização de senha (incluindo casos de forçar senhas fora do padrão ou não coincidentes)
  - **Risco**:
    - Mesmos riscos do fluxo gamer (token de reset + identificação do usuário).

---

### 3. Logs de Login (Gamer, PDV, Backoffice)

- **Login Gamer / ExpressMoney**
  - **Arquivos de código**:
    - `www/includes/gamer/functions.php` (função `gravaLog_Login`)
    - `www/class/gamer/classGamesUsuario.php` (chamadas a `gravaLog_Login`)
  - **Arquivo de log**:
    - `RAIZ_DO_PROJETO/arquivos_gerados/logs/log_login.txt`
  - **Dados registrados**:
    - Mensagens de sucesso/falha de login; em vários pontos o login do usuário é incluído na mensagem (ex.: `"Login de gamer falhou ($login)."`)
  - **Risco**:
    - O campo de login pode ser **e‑mail ou CPF** (dependendo de como o sistema é usado), logo esse log pode conter identificadores pessoais.

- **Login PDV (créditos)**
  - **Arquivos de código**:
    - `www/includes/pdv/functions.php` (função `gravaLog_Login`)
    - `www/public_html/creditos/loginEf2.php` (várias chamadas a `gravaLog_Login`)
  - **Arquivo de log**:
    - `RAIZ_DO_PROJETO/arquivos_gerados/logs/log_dist_login.txt`
  - **Dados registrados**:
    - Mensagens como:
      - `"Login ou senha inválidos: '$login_usuario'."`
      - `"Login com sucesso: '$login_usuario'."`
      - Mensagens de PDV inativo contendo o login do PDV.
  - **Risco**:
    - O campo `$login_usuario` normalmente identifica o PDV (pode ser CNPJ, código interno, e‑mail, etc.) → **dado identificável**.

- **Login Backoffice (BKO / SYS)**
  - **Arquivos de código**:
    - `www/includes/security.php` (`gravaLog_LoginBKO2`)
    - `www/backoffice/index2.php` (`gravaLog_LoginBKO`)
    - `www/public_html/sys/admin/index2.php` (`gravaLog_LoginSys`)
  - **Arquivos de log**:
    - `RAIZ_DO_PROJETO/arquivos_gerados/logs/log_LoginBKO.txt`
    - Possivelmente outros arquivos `log_Login*.txt`/`log_login*.txt` para SYS.
  - **Dados registrados**:
    - Usuário do backoffice (`$user`, `$login_var`)
    - IP de origem (no BKO é gravado `$_SERVER['REMOTE_ADDR']`/cabecalhos equivalente)
    - Mensagens de sucesso/erro de login
  - **Risco**:
    - Identificadores de usuário interno + IP são dados pessoais; podem ser mantidos, mas precisam de política clara de retenção e acesso.

---

### 4. Logs contendo PIN / pin_codigo ou identificadores de PIN

- **Log de venda PIX – inclui PIN**
  - **Arquivo de código**: `www/includes/gamer/functions_vendaGames.php`
  - **Arquivo de log**: `/www/arquivos_gerados/logs/log_vendaPIX.txt`
  - **Trechos relevantes**:
    - Abre o log e grava:
      - `"ID VENDA CONCILIAÇÃO ONLINE: {venda_id}"`
      - `"ID VENDA PROCESSA VENDA: {venda_id}"`
      - `"ID VENDA PROCESSA VENDA EMAIL: {venda_id}"`
      - Outras mensagens de fluxo de pagamento online.
    - **Ponto crítico**:
      - Mensagem `"PIN GERADO: {pin_codigo} / {venda_id}"` é gravada diretamente no log.
  - **Risco**:
    - **Alto**: o `pin_codigo` é essencialmente a “senha” do produto/recarga.
    - Logar `pin_codigo` em texto claro representa vazamento direto de PINs em caso de acesso indevido ao servidor/log.

- **Log de geração de PIN em PDV**
  - **Arquivo de código**: `www/includes/pdv/functions_vendaGames.php`
  - **Arquivo de log**: `/www/arquivos_gerados/logs/livrodjx.txt`
  - **Dados registrados**:
    - `"PIN GERADO NESSE CARAI: {pin_codinterno}"`
    - `"VGM_ID: {vgm_id}"`
  - **Risco**:
    - `pin_codinterno` é um identificador interno, mas **pode ser traçável até o PIN real** via banco de dados.
    - Com acesso ao banco + logs é possível reconstruir todos os PINs gerados.

- **Logs genéricos ligados a PINs**
  - **Arquivo de código**: `www/includes/gamer/functions.php`
  - **Arquivo de log**: `RAIZ_DO_PROJETO/arquivos_gerados/logs/log_EPP_CASH_PINs.txt`
  - **Uso**:
    - Função `gravaLog_EPP_CASH_PINs($mensagem)` recebe mensagem externa; o conteúdo exato depende de cada chamada (em vários fluxos de EPP CASH).
  - **Risco**:
    - Potencial de gravação de `pin_codigo` ou outros identificadores, dependendo de como a função é chamada (há forte relação semântica com operações de PIN).

- **Geração e envio de arquivos com PIN (estoque/distribuidores)**
  - **Arquivo de código**: `www/backoffice/admin/pins_store/pins_store_envio_email.php`
  - **Comportamento**:
    - Seleciona pins ativos (`pin_codigo` criptografado) e **gera arquivos texto** com `pin_codigo` decriptado para envio a distribuidores.
    - Os arquivos são gravados em diretórios de “arquivos gerados” e opcionalmente compactados com senha.
  - **Risco**:
    - Ainda que esses arquivos sejam parte do fluxo de negócio, **contêm PIN em texto claro**, devendo ser tratados como dados sensíveis (controle de acesso, criptografia em repouso, política de retenção).
    - Logs de depuração associados (`log_Depurador.txt`, `testePINstore.txt`) podem conter mensagens sobre geração/envio desses arquivos; hoje parecem guardar mais estados/textos do que o PIN em si, mas o contexto é sensível.

---

### 5. Logs de download de arquivos (podem expor nomes de arquivos com dados)

- **Download genérico de arquivos – área pública**
  - **Arquivos de código**:
    - `www/public_html/includes/dld.php`
    - `www/public_html/creditos/dld.php`
  - **Comportamento**:
    - Validam `BASE_DIR` e registram um `log_entry` via `error_log(..., 3, LOG_FILE)`.
    - O `log_entry` inclui:
      - IP do cliente
      - Caminho/nome do arquivo baixado (`$fname`)
      - Outros metadados do download.
  - **Risco**:
    - Se os arquivos baixados forem relatórios com CPF, PIN ou dados sensíveis no nome (ex.: `relatorio_CPF_12345678901.csv`, `pins_2026-03-04.txt`), **o próprio log de download expõe o identificador no nome do arquivo**.
    - Mesmo sem o conteúdo, o nome do arquivo pode ser dado pessoal (ex.: CPF no nome).

---

### 6. Outros logs de negócio com possível dado sensível indireto

- **Logs de bloqueio/risco de pagamento online**
  - **Arquivos de código**:
    - `www/includes/pdv/functions.php` (`gravaLog_BloqueioPagtoOnline`, `gravaLog_PagtoOnlineUsuariosBloqueadosParaVIP`)
    - `www/public_html/creditos/formas_pagamento.php`
  - **Arquivos de log**:
    - `RAIZ_DO_PROJETO/arquivos_gerados/logs/log_BloqueioPagtoOnline_LH.txt`
    - `RAIZ_DO_PROJETO/arquivos_gerados/logs/log_Money_PagtoOnlineUsuariosBloqueadosParaVIP_LH.txt`
  - **Dados registrados**:
    - Id do usuário/PDV (`idusuario`) e métricas de risco (quantidade de vendas, valores diários/semanais, etc.).
  - **Risco**:
    - Os logs não trazem CPF direto, mas **identificam o usuário/PDV** e associam comportamento transacional (padrões de compra) → dado pessoal sob LGPD.

- **Logs diversos de pagamento/integrações**
  - **Arquivos de código (exemplos)**:
    - `www/includes/gamer/functions.php` (`log_pagamento_TMP.txt`, `log_Pagto_Insert.txt`, `log_pagamento_MCOIN.txt`, `log_obterIdVendaValido.txt`, `log_TEMPORARIO.txt`, etc.)
    - `www/includes/gamer/functions_vendaGames.php` (`log_pagamento_TMP_conciliacao.txt`, `monitorprocessapagtoonline*.txt`, `log_Debug.txt`, `monitor_integracao_pedidos_duplicados.txt`)
    - `www/includes/pdv/functions.php` (`log_pagamento_TMP_dist.txt`, `log_Pagto_Insert.txt`)
  - **Dados registrados**:
    - Em geral: IDs de venda, tipo de pagamento, mensagens de erro e estados internos.
    - Dependendo de como as mensagens são montadas em cada ponto, podem incluir:
      - IDs de usuário/PDV
      - Dados de transação que, combinados com banco, permitem reidentificação de clientes.
  - **Risco**:
    - Sensibilidade mais voltada a **metadados de transação** do que a credenciais diretas.
    - Ainda assim relevante para LGPD (rastreamento de comportamento).

---

### 7. Observações sobre CPF e senhas em logs

- **CPF**:
  - A busca por `cpf` no código mostra forte uso em consultas e telas, mas **não foram encontrados pontos evidentes que gravem CPF diretamente em arquivos de log**.
  - Exceção potencial: qualquer função de log genérica (`gravaLog_*`) pode receber mensagens formatadas manualmente com CPF (não há validação). Isso depende de chamadas futuras/novas.
  - Recomendado:
    - Adotar guideline de **nunca incluir CPF em mensagens de log**.
    - Se necessário logar referência ao cliente, usar IDs internos anonimizados.

- **Senhas de usuário**:
  - Não há gravação de senha de usuário em texto plano em arquivos de log identificados.
  - Há:
    - Senhas de SMTP e de banco em código/configuração (sensível, mas fora do escopo de “logs de aplicação”).
    - Logs de fluxo de “esqueci minha senha” que registram **tokens**, não a nova senha em si.

---

### 8. Próximos passos recomendados

- **Classificar os logs por criticidade**, por exemplo:
  - **Crítico**: logs com `pin_codigo`, tokens de reset de senha, credenciais, ou que permitam reconstrução de PINs (ex.: `log_vendaPIX.txt`, `livrodjx.txt`, `log_EPP_CASH_PINs.txt`, `logEsqueciMinhaSenha*.log`).
  - **Alto**: logs com identificadores diretos de usuário/PDV + IP (ex.: `log_login.txt`, `log_LoginBKO.txt`, logs de bloqueio de pagamento).
  - **Médio/Baixo**: logs com apenas IDs internos de venda/usuário, sem ligação direta ao cliente final.
- **Para cada log crítico/alto**, avaliar:
  - **Remoção completa** ou redução do nível de detalhe.
  - **Anonimização/pseudonimização** (ex.: hash de IDs, não logar PIN completo).
  - **Criptografia em repouso** e política de retenção (tempo máximo).
  - **Controle de acesso** a diretórios como `/www/arquivos_gerados/logs/`.

