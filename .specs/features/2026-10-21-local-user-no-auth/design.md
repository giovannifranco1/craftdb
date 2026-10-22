# F2 — Usuário local implícito + remoção de auth — Design

**Spec**: `.specs/features/2026-10-21-local-user-no-auth/spec.md`
**Contexto**: `.specs/features/2026-10-21-local-user-no-auth/context.md` (D1–D4 do dev, A1–A9 travadas)
**Status**: Approved (2026-10-21)

---

## Visão Geral da Arquitetura

Um middleware novo no grupo `web` autentica o usuário local quando a sessão não tem usuário. Todo o resto da F2 é **remoção**: Fortify, Socialite, convites, papéis e super root saem do código, e uma migration nova limpa o schema. O guard `web` (sessão) continua, porque `auth`, Passport e `Team::currentTeam()` dependem dele.

```mermaid
graph TD
    R[Request web] --> S[StartSession]
    S --> L[AuthenticateLocalUser<br/>novo, 1º do append web]
    L -->|Auth::check()| P[segue]
    L -->|sem usuário| E[EnsureLocalUser::exec email]
    E --> AL[Auth::login local user<br/>dispara Login uma vez]
    AL --> SC[SetCurrentTeamOnLogin<br/>time + idioma na sessão]
    SC --> P
    P --> SL[SetLocale] --> EV[EnsureValidCurrentTeam] --> A[auth middleware da rota] --> C[Controller / Livewire]
```

### Abordagem do login automático

| Abordagem | Prós | Contras |
|---|---|---|
| **A. Middleware no grupo `web` com `Auth::login` uma vez por sessão** ⭐ | Simples; sessão normal (Passport OAuth e `Team::currentTeam()` funcionam); `Login` dispara uma vez e `SetCurrentTeamOnLogin` inicializa time/idioma | Um passo a mais no pipeline |
| B. Guard customizado (`Auth::viaRequest`) que sempre devolve o usuário local | Sem sessão de auth | `Auth::login`/eventos não existem; Passport `oauth/authorize` e `SetCurrentTeamOnLogin` deixam de funcionar; mais invasivo |
| C. Trocar o redirect de guest do `auth` middleware por login automático | Só toca o `auth` | Rotas sem `auth` (landing, Livewire update) ficam sem usuário; `EnsureValidCurrentTeam` roda antes e pula |

### Abordagem do schema

| Abordagem | Prós | Contras |
|---|---|---|
| **A. Manter migrations antigas + 1 migration nova de limpeza; migrations do super root viram no-op** ⭐ | Banco novo e banco da F1 chegam ao mesmo schema pelo mesmo caminho; sem reescrever histórico | Banco novo cria tabelas/colunas que a migration seguinte apaga (custo desprezível) |
| B. Apagar as migrations que criam o que sai + migration de limpeza com guards `hasTable/hasColumn` | Histórico enxuto | Dois caminhos de schema (novo vs F1) a testar; mexer em migrations com FKs cruzadas (`team_user` → `role`) é frágil |

---

## Análise de Reuso

### Componentes Existentes

| Componente | Localização | Como Usar |
|---|---|---|
| `EnsureLocalUser` | `app/Actions/Local/EnsureLocalUser.php` | Resolver/criar o usuário local no middleware (assinatura vira `exec(string $email)`) |
| `SetCurrentTeamOnLogin` | `app/Listeners/SetCurrentTeamOnLogin.php` | Mantido: inicializa time e idioma no primeiro login automático da sessão |
| `EnsureValidCurrentTeam` | `app/Http/Middleware/EnsureValidCurrentTeam.php` | Mantido, roda depois do login automático |
| `HasTeams::ownsTeam()` | `app/Models/Concerns/HasTeams.php` | Base da autorização por posse (substitui `userHasRole`) |
| `TestCase::autenticated()` | `tests/TestCase.php:27` | Mantido; troca `withoutTwoFactor()` por `create()` |
| `docker/smoke-test.sh` | F1 | Ajustado: sem senha no log, `/` → dashboard |

### Pontos de Integração

| Sistema | Método de Integração |
|---|---|
| Grupo `web` (`bootstrap/app.php`) | `AuthenticateLocalUser` como **primeiro** item do `web(append: ...)` + `prependToPriorityList(before: Authenticate::class, prepend: AuthenticateLocalUser::class)` para garantir que roda antes do `auth` da rota |
| Passport / MCP OAuth (F6) | Inalterado; `oauth/authorize` encontra o usuário já logado |
| Docker (F1) | entrypoint segue chamando `craftdb:local-user` (garante o usuário antes do 1º request) |

---

## Componentes

### 1. `App\Http\Middleware\AuthenticateLocalUser` (novo)
- **Propósito**: Autenticar o usuário local quando não há usuário na sessão (NOAUTH-01/02/03).
- **Interface**: `handle(Request $request, Closure $next): Response`
- **Lógica**: `if (! Auth::check()) { Auth::login($ensureLocalUser->exec(config('craftdb.local_user.email'))['user']); }` → `$next($request)`
- **Dependências**: `EnsureLocalUser`, guard `web`
- **Reusa**: `EnsureLocalUser`

### 2. `EnsureLocalUser` + `craftdb:local-user` (ajuste)
- `exec(string $email)`: sem parâmetro de senha; cria com `password = null` (NOAUTH-13).
- Comando imprime só URL e e-mail (sem linha "Senha").
- `config/craftdb.php`: remove `local_user.password`.

### 3. Rotas (`routes/web.php`, `routes/auth.php`)
- `GET /`, `/login`, `/register` → `Route::redirect(..., '/dashboard')` (NOAUTH-04); as demais rotas da landing ficam (F7).
- Remove `verified` (não faz nada), `role:...` do grupo de diagramas, `super.root` + `/feedback`, rotas de convite/membros/`team.invitations.edit`, rotas de senha/2FA em settings.
- `routes/auth.php` apagado (Socialite + convite) e `require` removido.

### 4. Remoção Fortify / Socialite
- `composer remove laravel/fortify laravel/socialite`.
- Apaga `FortifyServiceProvider` (+ `bootstrap/providers.php`), `config/fortify.php`, `app/Actions/Fortify/*`, `AuthController`, `EmailAlreadyRegisteredException`, `ValidateLoginTermsAcceptance` (+ registro), views `components/auth/*` e layouts `auth*`, `auth-header`, `auth-session-status`, `input-otp`, ícones `flux/icon/{github,google}`, strings de termos em `lang/*/auth.php`.
- `User`: remove `TwoFactorAuthenticatable`, campos `two_factor_*`/`github_*`/`google_*` de `Fillable`/`Hidden`, `PROVIDERS`, `isProvider`, `isAuthenticatedWith*`, `isSuperRoot` e bypasses de limite.
- `UserFactory`: remove estados 2FA (`withoutTwoFactor`) e campos.
- `SetLocale`: remove nomes de rota `login`/`register`.

### 5. Convites e papéis
- Apaga `Invitation`, `Role` (model e enum), `Owner`, `CheckTeamRole` (+ alias), `InvitationController`, `InvitationException` (+ handler em `bootstrap/app.php`), `TeamInvitationNotification`, `CleanupAcceptedInvitations` (+ schedule em `routes/console.php`), `DefaultRolesSeeder`, `AddMemberToTeam`, `config/teams.php`, `resources/views/vendor/teams/`, docs `SETUP_ROLES.md` / `docs/TEAM_ROLES_SETUP.md` / trecho de papéis em `docs/TEAM_MANAGEMENT.md`.
- `HasMembers`/`HasTeams`/`Membership`: remove `roles()`, `withPivot('role_id')`, `addUser`, `updateUser`, `inviteUser`, `inviteAccept`, `userHasRole`, `invitations()`, `hasPendingInvitationForEmail`, `hasReachedMaxInvitationsPerDay`.
- `DiagramPolicy::createOnTeam/update/share` e gate `createDiagramOnTeam`: `userHasRole(EDITOR)` → `ownsTeam($team)` (NOAUTH-16). `TeamPolicy`: remove ramos de membros/convites.
- Livewire: apaga `settings/team/⚡members`; enxuga `settings/⚡team` (remove sair do time por senha/membros) e o nav de `settings/layout.blade.php` (NOAUTH-17).

### 6. Super root
- Migrations `add_super_root_user` e `insert_super_root_has_team`: corpo de `up()`/`down()` vira no-op (NOAUTH-11).
- Apaga `EnsureSuperRoot` (+ alias), `FeedbackController`, rota `/feedback`, link no `ui/menu.blade.php` e view da listagem de feedback. `contact-us` (grava `feedback`) fica até a F7.
- `.env.example`: remove `SUPER_ROOT_*`, `GITHUB_*`, `GOOGLE_*`.

### 7. UI do app autenticado (NOAUTH-08/09/10)
- Remove formulários de logout em `layouts/app/master`, `layouts/app/header`, `diagram/canvas/ui/topbar`; remove `app/Livewire/Actions/Logout.php`.
- Settings: apaga `⚡password`, `⚡two-factor` (+ `two-factor/⚡recovery-codes`), `⚡delete-user-form`; `⚡profile` só com nome (sem e-mail, verificação, excluir conta).

### 8. Migration de limpeza `2026_10_21_000000_remove_auth_leftovers` (nova)
- `users`: drop `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`, `github_*`, `google_*`; `password` → nullable.
- `team_user`: `dropForeign(['role_id'])` → `dropColumn('role_id')` (SQLite: Laravel reconstrói a tabela).
- `Schema::dropIfExists('invitation')`, `('role')`, `('password_reset_tokens')` (nessa ordem, por FKs).
- `down()`: recria colunas/tabelas com a forma original (para `migrate:rollback` não quebrar).
- Banco da F1: super root órfão preservado (NOAUTH-19).

### 9. Docker
- `docker-compose.yml`: remove `CRAFTDB_USER_PASSWORD`; README (PT/EN): remove a linha da senha e "entre com..." vira "abre direto no dashboard" (NOAUTH-14).
- `docker/smoke-test.sh`: DOCK-07 passa a checar URL/e-mail (sem senha); novo check `/` → 302 para `/dashboard` e `/dashboard` → 200 sem cookie.

### 10. Testes (NOAUTH-22/23/24)
- Apaga `tests/Feature/Auth/*`, `Settings/PasswordUpdateTest`, `Settings/TwoFactorAuthenticationTest`, `Settings/TeamMembersInviteTest`; ajusta `ProfileUpdateTest` (só nome), `SetLocaleGeoTest` (rotas `login`/`register` → outra rota pública), `dashboard.test.php:11`, `ExportDiagramToJsonTest:206`, `ExportDiagramToLaravelMigrationTest:719` (guest → login automático).
- Novos: `tests/Feature/Middleware/AuthenticateLocalUserTest.php` (NOAUTH-01..05), `tests/Feature/Database/RemoveAuthLeftoversTest.php` (NOAUTH-11, 18), teste de rotas removidas (NOAUTH-06, 12, 15), atualização dos testes de `EnsureLocalUser`/comando (NOAUTH-13).

---

## Modelos de Dados

```
users (após F2)
- id, name, email, email_verified_at, password (nullable), locale, is_dark, remember_token,
  stripe/cashier columns (F3), timestamps
- sem: two_factor_*, github_*, google_*

team_user (após F2)
- id, team_id (FK team), user_id (FK users), timestamps   ← sem role_id

Removidas: invitation, role, password_reset_tokens
```

---

## Tratamento de Erros

| Cenário | Tratamento | Impacto no Usuário |
|---|---|---|
| Sessão expirada | Próximo request não tem usuário → login automático de novo | Nenhum |
| Usuário local apagado do banco à mão | `EnsureLocalUser` recria no próximo request sem sessão | Workspace novo vazio |
| Rota removida acessada (link antigo) | 404 padrão | Página de erro |
| API MCP sem token | `auth:api` continua 401 (middleware só no grupo `web`) | Nenhum para o app |

---

## Decisões Técnicas (apenas não-óbvias)

| # | Decisão | Escolha | Justificativa |
|---|---|---|---|
| T1 | Ordem do middleware | 1º do `web(append)` + `prependToPriorityList` antes de `Authenticate` | Precisa de sessão (StartSession vem antes) e precisa rodar antes de `SetLocale`, `EnsureValidCurrentTeam` e do `auth` da rota |
| T2 | `Auth::login` só se `! Auth::check()` | Uma vez por sessão | `SetCurrentTeamOnLogin` reseta time/idioma a cada `Login` (A1) |
| T3 | Migrations do super root | Corpo vazio, arquivo mantido | Bancos existentes já registraram a migration; apagar o arquivo deixaria a tabela `migrations` inconsistente |
| T4 | `users.password` | nullable, mantida | O guard `web`/`Authenticatable` ainda referencia a coluna; usuário local não tem senha |
| T5 | `team_user` | Fica sem `role_id` | Destino de membros é decisão da F4 (A9) |

---

## Riscos

- **Superfície grande** (~80 arquivos): risco de referência esquecida → mitigação: grep de verificação (NOAUTH-21) e suite + smoke ao fim de cada task.
- **SQLite `dropForeign`**: Laravel faz rebuild da tabela; validar cedo no teste de migration.
- **Componentes que assumem `auth()->user()`**: continuam OK porque o login automático garante usuário em todo request `web`; Livewire `/livewire/update` também está no grupo `web`.

---

## Reuse Validation

**Componentes reusáveis identificados:**
- `app/Actions/Local/EnsureLocalUser.php` — resolver/criar usuário — usado em: middleware, comando
- `app/Listeners/SetCurrentTeamOnLogin.php` — init de time/idioma — usado em: login automático
- `app/Models/Concerns/HasTeams.php::ownsTeam()` — autorização por posse — usado em: DiagramPolicy, gate
- `tests/TestCase.php::autenticated()` — helper de testes — usado em: suite inteira

**Padrões reusáveis identificados:**
- Middleware no grupo `web` via `bootstrap/app.php` — aplicado em: `AuthenticateLocalUser`
- Action com `exec()` — mantido em `EnsureLocalUser`

**Áreas exploradas SEM reuso (justificativa):**
- Fortify/Socialite — removidos por definição
- `HasMembers` (convites/papéis) — removido (D3)

**Áreas NÃO exploradas:**
- (vazio)
