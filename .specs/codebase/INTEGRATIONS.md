# Integrações Externas

## Pagamentos

Não há. A F3 removeu Laravel Cashier/Stripe, planos, webhooks (`/stripe/webhook`), rotas de billing e páginas de assinatura. A seção de preços da landing page ainda existe (remoção na F7).

---

## Autenticação

Não há OAuth de login (GitHub/Google), registro ou reset de senha: o CraftDB Local autentica automaticamente o usuário local (ver `AuthenticateLocalUser`). O único OAuth presente é o do MCP (Laravel Passport, scope `mcp:use`; F6). No MCP não há checagem de plano nem de limites (F3): `list_diagrams` lista todos os diagramas dos times permitidos ao token; time não permitido lança `AuthorizationException`.

---

## Detecção de Região

Removida na F3 (header `cf-ipcountry`, `CLOUDFLARE_PROXY`, `BillingCountry`, middleware `SetHeaders`, rota `/debug-cf`). Não há precificação regional.

---

## Storage

**Serviço:** AWS S3
**Propósito:** Armazenamento de arquivos (configurado, uso específico não identificado)
**Configuração:** `config/filesystems.php` — `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET`

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
