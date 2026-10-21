# Integrações Externas

## Pagamentos

Não há. A F3 removeu Laravel Cashier/Stripe, planos, webhooks (`/stripe/webhook`), rotas de billing e páginas de assinatura. A seção de preços da landing page ainda existe (remoção na F7).

---

## Autenticação

Não há OAuth de login (GitHub/Google), registro ou reset de senha: o CraftDB Local autentica automaticamente o usuário local (ver `AuthenticateLocalUser`). Laravel Passport foi removido na F6 (guard `api`, `config/passport.php`, rotas `oauth/*` e `.well-known/oauth-*`, `Registrar` custom, `McpTokenTeam` etc.; a migration `2026_10_21_000004_remove_passport_tables` apaga `oauth_*` e `mcp_token_teams`).

## MCP local

- Endpoint HTTP: `POST http://localhost:8080/mcp/craftdb`, sem autenticação. Proteção: bind `127.0.0.1` (compose), middleware `EnsureLocalMcpOrigin` (sem `Origin` ou localhost/127.0.0.1/[::1]/host do `APP_URL`; qualquer outro, inclusive com barra invertida, retorna 403) e `throttle:mcp` (por IP). GET/DELETE retornam 405 (padrão do vendor).
- stdio: `php artisan mcp:start craftdb` (sem token); via Docker: `docker compose exec -T app php artisan mcp:start craftdb`.
- Identidade: `McpAccess::user()` = `Auth::user()` ou o binding escopado `craftdb.local-user` (`EnsureLocalUser`), resolvido uma vez por request/processo. Checagens de posse sempre valem; `AddRelationshipTool` confere as duas tabelas. Sem checagem de plano nem limites (F3): `list_diagrams` lista os diagramas do usuário; time de outro usuário lança `AuthorizationException`.
- Nomes `list_teams`/`team_id` mantidos (contrato das tools).
- Rotas: `routes/ai.php` é carregado só pelo `McpServiceProvider` (`bootstrap/app.php` não usa mais `api:`), então `route:cache` funciona; o entrypoint roda `php artisan optimize`.
- Cliente: `.cursor/mcp.json` = `{"mcpServers":{"craftdb":{"url":"http://localhost:8080/mcp/craftdb"}}}`; a página Settings → Integrations mostra a URL para Cursor, Claude Code e Claude Desktop, e a aba stdio.

---

## Detecção de Região

Removida na F3 (header `cf-ipcountry`, `CLOUDFLARE_PROXY`, `BillingCountry`, middleware `SetHeaders`, rota `/debug-cf`). Não há precificação regional.

---

## Storage

Removido na F5 (disco S3 e variáveis `AWS_*`). Apenas discos locais em `config/filesystems.php`.

---

## Zero telemetria (F5)

Sem chamadas a serviços externos em runtime: removidos `laravel/nightwatch` e `resend/resend-laravel` (e o provider em `bootstrap/providers.php`), serviços postmark/ses/resend/slack, mailers postmark/resend/ses/failover/roundrobin, canais de log slack/papertrail, fila sqs e cache dynamodb. Mailer padrão: `log`. Variáveis `AWS_*`, `NIGHTWATCH_*`, `RESEND_*`, `POSTMARK_*`, `SLACK_*`, `PAPERTRAIL_*` removidas. Os defaults do framework Laravel continuam mesclados, porém inertes (não selecionados).

Fontes self-hosted via `@fontsource` em `resources/css/app.css`. Garantias: `tests/Feature/Telemetry/` e check ZT-09 do `docker/smoke-test.sh`. Fora de escopo: landing (F7), formulário `contact-us`.

---

## Parser de SQL

**Serviço:** phpmyadmin/sql-parser 6 (biblioteca para MySQL) + parsers customizados (PostgreSQL, SQLite)
**Propósito:** Parsear schemas SQL importados pelo usuário para criar diagramas automaticamente
**Implementação:**
- `app/Actions/Parse/BaseSQLParse.php` — classe base com lógica compartilhada
- `app/Actions/Parse/ParseMySQLSchema.php` — usa phpmyadmin/sql-parser
- `app/Actions/Parse/ParsePOstgreSQLSchema.php` — parser customizado para PostgreSQL
- `app/Actions/Parse/ParseSQLiteSchema.php` — parser customizado para SQLite
**Uso:** Chamados por `CreateNewDiagram` / `ImportToExistingDiagram` quando usuário faz upload de arquivo `.sql`

---

## Importação de Migrations Laravel

**Serviço:** Parser customizado (não é uma integração externa)
**Propósito:** Criar diagramas a partir de arquivos de migration do Laravel
**Implementação:** `app/Actions/Parse/ParseLaravelMigration.php`
**Uso:** Chamado por `CreateNewDiagram` quando usuário faz upload de arquivo de migration

---

## Documentação de API

**Swagger/OpenAPI disponível:** Não encontrado
**Estilo:** A aplicação não expõe REST API pública — usa Livewire para interações reativas

### Endpoints HTTP identificados (routes/web.php)

**Landing (TODO F7):**
- `GET /`, `/login`, `/register` — redirecionam para `/dashboard`
- `GET /features`, `/pricing`, `/about`, `/terms`, `/policy`

**Dashboard (auth — usuário local automático):**
- `GET /dashboard` — Lista de diagramas

**Diagrama:**
- `GET /{team_id}/diagram/{diagram:slug}` — Editor de diagrama
- `POST /{team_id}/diagram/{diagram:slug}/toggle-favorite` — JSON

**Configurações (auth):**
- `GET /settings/{profile,appearance,language,integrations}`, `/settings/workspaces` (`workspaces.index`: listar, criar, renomear, apagar e alternar workspace; rotas `team.*` e `settings/team*` removidas na F4)
- `/login` e `/register` são redirects para `/dashboard` (TODO F7)

---

## Jobs e Filas

**Sistema de filas:** Database driver (padrão Laravel)
**Localização:** `app/Jobs/` (diretório existe, jobs específicos não mapeados em detalhe)
**Tabela:** `jobs` no banco de dados
**Uso identificado:** Nenhum job de convite/verificação de e-mail (removidos na F2); fila permanece disponível para o formulário de contato e demais usos

---

## Notificações

**Sistema:** Laravel Notifications
**Localização:** `app/Notifications/`
**Canal:** Email (driver configurável via `MAIL_*` no `.env`)
**Uso:** Sem notificações de convite, verificação ou reset de senha (removidas na F2); e-mail restante limitado ao formulário de contato (F7)
