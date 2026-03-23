# Integrações Externas

## Pagamentos

**Serviço:** Stripe via Laravel Cashier 16
**Propósito:** Processamento de assinaturas, checkout, portal de gerenciamento
**Implementação:** `app/Http/Controllers/BillingController.php`
**Configuração:** `config/billing.php` — define planos, preços e Price IDs do Stripe

**Planos e preços:**
```
Free:              $0 / R$0 — 3 diagramas, 30 tabelas, 50 colunas/tabela
Pro USD mensal:    $9/mês
Pro BRL mensal:    R$49.90/mês
Pro USD anual:     preço anual com desconto
Pro BRL anual:     preço anual com desconto
Team USD mensal:   $24.99/mês — Tudo Pro + colaboração multi-usuário
Team BRL mensal:   R$59.99/mês — Mesmas features
Team USD anual:    preço anual com desconto
Team BRL anual:    preço anual com desconto
```

**Environment variables de preço** (valores nominais, não Price IDs):
```
BILLING_PRO_USD_PRICE=9
BILLING_PRO_BRL_PRICE=49.90
BILLING_TEAM_USD_PRICE=24.99
BILLING_TEAM_BRL_PRICE=59.99
# variantes anuais equivalentes
```

**Autenticação:** `STRIPE_KEY` (público) + `STRIPE_SECRET` (privado) no `.env`
**Price IDs Stripe:** `STRIPE_PRICE_PRO_USD`, `STRIPE_PRICE_PRO_BRL`, `STRIPE_PRICE_TEAM_USD`, `STRIPE_PRICE_TEAM_BRL` + variantes `_ANNUAL` — todos via `.env`

**Fluxo de checkout:**
1. BillingController detecta país via `cf-ipcountry` header (Cloudflare)
2. Seleciona Price ID correspondente (USD ou BRL)
3. Redireciona para Stripe Checkout: `$user->newSubscription()->checkout()`
4. Stripe retorna para `/settings/billing/success`

**Webhooks:** Gerenciados automaticamente pelo Laravel Cashier em `/stripe/webhook`

---

## Autenticação OAuth

### GitHub

**Serviço:** GitHub OAuth via Laravel Socialite 5
**Propósito:** Login/registro via conta GitHub
**Implementação:** `app/Actions/Fortify/CreateUserFromOAuth.php`, `app/Http/Controllers/AuthController.php`
**Configuração:** `config/services.php` — `GITHUB_CLIENT_ID`, `GITHUB_CLIENT_SECRET`
**Campos no banco:** `users.github_id`, `users.github_token`, `users.github_refresh_token`

### Google

**Serviço:** Google OAuth via Laravel Socialite 5
**Propósito:** Login/registro via conta Google
**Implementação:** Mesmos arquivos do GitHub
**Configuração:** `config/services.php` — `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`
**Campos no banco:** `users.google_id`, `users.google_token`, `users.google_refresh_token`

---

## Detecção de Região

**Serviço:** Cloudflare (proxy reverso)
**Propósito:** Detectar país do usuário para aplicar precificação regional (USD vs BRL)
**Implementação:** `BillingController::checkout()` lê header `cf-ipcountry`
**Configuração:** `CLOUDFLARE_PROXY=true` no `.env` (habilita leitura do header)
**Enum:** `app/Enums/BillingCountry` — `USD` (default) ou `BRL` (Brasil)

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

**Landing:**
- `GET /` — Página inicial
- `GET /features`, `/pricing`, `/about`, `/terms`, `/policy`

**Dashboard (auth + verified):**
- `GET /dashboard` — Lista de diagramas
- `GET /my-favorites` — Diagramas favoritos

**Diagrama:**
- `GET /{team_id}/diagram/{diagram:slug}` — Editor de diagrama
- `POST /{team_id}/diagram/{diagram:slug}/toggle-favorite` — JSON
- `POST /{team_id}/diagram/{diagram:slug}/make-public` — JSON
- `POST /{team_id}/diagram/{diagram:slug}/revoke-public` — JSON

**Público (sem auth):**
- `GET /diagram/public/{token}` — Visualização pública de diagrama

**Billing:**
- `GET /settings/billing/checkout/{plan}` — Redireciona para Stripe
- `GET /settings/billing/portal` — Redireciona para portal Stripe
- `GET /settings/billing/success` — Pós-checkout

**Auth (Fortify + Social):**
- Rotas padrão Fortify (login, register, forgot-password, verify-email)
- `GET /auth/{provider}/redirect`, `GET /auth/{provider}/callback`

**Convites de equipe** (`InvitationController`):
- `POST /{team_id}/invite` — Envia convite por email
- `GET /invite/accept/{token}` — Aceita convite (com ou sem auth)
- `DELETE /invite/{invitation}` — Cancela convite

**Administração:**
- `GET /feedback` — Acesso super root apenas (middleware `super.root`)

---

## Jobs e Filas

**Sistema de filas:** Database driver (padrão Laravel)
**Localização:** `app/Jobs/` (diretório existe, jobs específicos não mapeados em detalhe)
**Tabela:** `jobs` no banco de dados
**Uso identificado:** Notificações por email (convites de equipe, verificação de email)

---

## Notificações

**Sistema:** Laravel Notifications
**Localização:** `app/Notifications/`
**Canal:** Email (driver configurável via `MAIL_*` no `.env`)
**Uso:** Convites de equipe, verificação de email, reset de senha
