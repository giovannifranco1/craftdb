# Contexto — F2 Usuário local implícito + remoção de auth

**Origem:** ROADMAP M1/F2 · pivot total (STATE.md 2026-10-21: o SaaS acaba, o repo vira só o CraftDB Local)
**Data:** 2026-10-21
**Escopo:** Large · defaults do processo mantidos (subagents + two-stage review)

## Decisões do dev

| # | Decisão | Alternativas descartadas |
|---|---|---|
| D1 | Usuário implícito = **só o usuário local** da F1 (e-mail de `config('craftdb.local_user.email')`). Migrations do super root viram no-op; `/feedback`, `EnsureSuperRoot` e `isSuperRoot()` saem | Usar o super root (id 1) como usuário local |
| D2 | Settings que ficam: **Perfil (só nome), Aparência, Idioma, Integrações**. Saem Senha, 2FA, Excluir conta, edição de e-mail e reenvio de verificação. Billing → F3, Times → F4 | Sem Perfil (nome fixo) |
| D3 | F2 remove **convites e papéis** (membros, role middleware, checagens de papel em policies, `invitations:cleanup`, notificação de convite). Criar/trocar time continua até a F4 | Deixar times inteiros para a F4 |
| D4 | **Migration que remove** colunas/tabelas sem uso: `two_factor_*`, `github_*`, `google_*`, `password_reset_tokens`, `invitation`, `role`, `team_user.role_id`. `users.password` fica (nullable) | Manter no schema |
| D5 | Login automático por **middleware no grupo `web`** (`Auth::login` uma vez por sessão) | Guard customizado `Auth::viaRequest` (Design, 2026-10-21) |
| D6 | Schema: **migration nova de limpeza**; migrations antigas mantidas, as do super root viram no-op | Apagar migrations antigas (Design, 2026-10-21) |

## Decisões do agente (padrões sensatos)

| # | Decisão | Motivo |
|---|---|---|
| A1 | Login automático **uma vez por sessão** (`Auth::login` só quando não há usuário autenticado) | `SetCurrentTeamOnLogin` reseta time e idioma a cada evento `Login`; logar a cada request quebraria troca de time/idioma |
| A2 | Rota nomeada `login` continua existindo como **redirect para o dashboard**; `register` idem | Landing (removida só na F7) e o redirect padrão do Laravel para guests usam `route('login')`/`route('register')` |
| A3 | `craftdb:local-user` deixa de imprimir/usar senha; `CRAFTDB_USER_PASSWORD` sai do compose e do README | Sem tela de login, senha não tem uso |
| A4 | Form "Fale conosco" (`contact-us`, grava `feedback`) fica até a F7 | É parte da landing/marketing |
| A5 | `/my-favorites` (já quebrada: view inexistente) fica fora da F2 — registrada em STATE.md | Não é auth |
| A6 | `DatabaseSeeder` é corrigido (chama seeder de papéis inexistente) | Remover papéis o tornaria ainda mais quebrado |
| A7 | Passport/MCP OAuth continuam (F6). O fluxo `oauth/authorize` passa a usar o usuário logado automaticamente | Fase própria |
| A8 | `GET /` redireciona para o dashboard já na F2; as outras rotas da landing ficam até a F7 | O objetivo "abrir localhost:8080 cai no dashboard" exige isso; remover a landing inteira é F7 |
| A9 | Tabela `team_user` fica (sem `role_id`); métodos de membros/papéis/convites saem de `HasMembers`/`HasTeams`/`Membership` | Destino de membros é decisão da F4 (workspaces) |
| A10 | Detecção de idioma por `cf-ipcountry` (Cloudflare) sai na F2, junto com `SetLocaleGeoTest` | O login automático grava o idioma do usuário na sessão e anula a detecção; Cloudflare não existe no app local. A escolha manual de idioma continua |

## Fatos do codebase relevantes (mapeamento 2026-10-21)

- Super root: migrations `2026_03_08_155628_add_super_root_user` (id 1) e `..._insert_super_root_has_team` rodam em todo boot e em todo teste (RefreshDatabase)
- `User::isSuperRoot()` (`id === 1`) faz bypass de limites de plano (`User.php:198,260`) — some na F2; limites de plano continuam valendo para o usuário local até a F3 (aceito)
- `verified` não bloqueia nada (`User` não implementa `MustVerifyEmail`)
- Rotas com `auth`: dashboard, my-favorites, feedback, settings, diagram group (`role:viewer|editor|owner`), invitation accept, `oauth/authorize/teams`
- `Team::currentTeam()` e vários componentes/actions assumem `auth()->user()` não nulo — continuam funcionando com login automático
- `EnsureValidCurrentTeam` (web group) pula quando não há usuário — middleware de login automático precisa rodar antes dele
- ~45 testes cobrem auth/settings removidos (Auth/*, Settings/Password*, TwoFactor*, TeamMembersInvite, parte de SetLocaleGeoTest e de dashboard.test)
- Testes que afirmam "guest é redirecionado ao login": `ExportDiagramToJsonTest:206`, `ExportDiagramToLaravelMigrationTest:719`, `dashboard.test.php:11` (+ 2FA/password reset, removidos)
- `tests/TestCase.php::autenticated()` usa `factory()->withoutTwoFactor()` (state some com 2FA)
- Resíduos jurager/teams: `config/teams.php`, `resources/views/vendor/teams/`, import em `app/Models/Team/Role.php:11`

## Consequências aceitas

- Até a F3, o usuário local segue os limites do plano Free (antes o super root não tinha limite)
- Qualquer pessoa com acesso a `127.0.0.1:8080` usa o app — mitigado por bind só em localhost (F1/D5)
