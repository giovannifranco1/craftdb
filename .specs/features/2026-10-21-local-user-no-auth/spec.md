# F2 — Usuário local implícito + remoção de auth — Especificação

## Problema

O CraftDB Local roda só na máquina do dev, mas ainda pede login com e-mail e senha, carrega 2FA, cadastro, reset de senha, login social, convites e papéis — herança do SaaS, que foi descontinuado. Isso adiciona atrito ao abrir o app e mantém código, rotas, tabelas e dependências sem uso.

## Objetivos

- [ ] Abrir `http://localhost:8080` leva direto ao dashboard, sem tela de login
- [ ] Nenhuma rota, tela, dependência ou tabela de auth/convites/papéis permanece no projeto
- [ ] Suite de testes verde, sem testes de funcionalidades removidas

## Fora do Escopo

| Item | Motivo |
|---|---|
| Billing, planos, limites, links públicos | F3 |
| Renomear Team → Workspace, telas de time | F4 |
| Nightwatch, Resend, S3, fontes externas | F5 |
| Passport / MCP OAuth / token do MCP stdio | F6 |
| Landing page, "Fale conosco" (`contact-us`) | F7 |
| `/my-favorites` (já quebrada: view inexistente) | Não é auth — registrada em STATE.md |
| Multiusuário em rede (LAN) | Considerações futuras do ROADMAP |

---

## Glossário

| Termo | Definição canônica | Fonte |
|---|---|---|
| Usuário local | O único usuário do app, identificado por `config('craftdb.local_user.email')`, criado/garantido por `EnsureLocalUser` | F1 + D1 |
| Login automático | Autenticar o usuário local na sessão, sem interação, quando não há usuário autenticado | A1 |
| Super root | NÃO existe mais após a F2 — não usar | D1 |
| Workspace / Time | `Team` continua sendo o model; o termo de UI muda só na F4 | ROADMAP |

---

## User Stories

### P1: Abrir o app sem login ⭐ MVP

**User Story**: Como usuário local, quero abrir o CraftDB e cair direto no dashboard, para usar a ferramenta sem digitar credenciais.

**Por que P1**: É o objetivo central da F2.

**Critérios de Aceite**:

1. QUANDO uma requisição web chega sem usuário autenticado ENTÃO o sistema DEVE autenticar o usuário local na sessão antes de qualquer verificação de acesso (NOAUTH-01)
2. QUANDO o usuário local ainda não existe no banco ENTÃO o login automático DEVE criá-lo via `EnsureLocalUser` (verificado e com workspace) antes de autenticar (NOAUTH-02)
3. QUANDO já há usuário autenticado na sessão ENTÃO o sistema NÃO DEVE autenticar de novo, preservando o time atual e o idioma escolhidos na sessão (NOAUTH-03)
4. QUANDO o usuário acessa `/`, `/login` ou `/register` ENTÃO o sistema DEVE redirecionar para o dashboard; as demais rotas da landing (`/features`, `/pricing`, `/about`, `/terms`, `/policy`) ficam intactas até a F7 (NOAUTH-04)
5. QUANDO o usuário acessa `/dashboard`, `/settings/*` ou uma rota de diagrama do próprio time ENTÃO o sistema DEVE responder sem redirecionar para login (NOAUTH-05)

**Teste Independente**: `curl -s -o /dev/null -w '%{http_code}' -L http://localhost:8080/` termina em 200 no dashboard; Pest: request sem `actingAs` a `/dashboard` → 200.

---

### P1: Remover autenticação e contas ⭐ MVP

**User Story**: Como mantenedor, quero remover todo o código de autenticação do SaaS, para não manter funcionalidades que não existem no produto local.

**Por que P1**: Sem a remoção, sobram rotas e telas que levam a fluxos quebrados (logout, 2FA, reset de senha).

**Critérios de Aceite**:

1. QUANDO a aplicação sobe ENTÃO NÃO DEVEM existir rotas de: registro (POST), login (POST), logout, reset/esqueci senha, verificação de e-mail, confirmação de senha, 2FA, login social GitHub/Google (NOAUTH-06)
2. QUANDO o `composer.json` é inspecionado ENTÃO `laravel/fortify` e `laravel/socialite` NÃO DEVEM estar presentes, e o app DEVE subir sem eles (NOAUTH-07)
3. QUANDO qualquer tela do app autenticado é renderizada (layouts `app/master` e `app/header`, topbar do diagrama, settings) ENTÃO NÃO DEVE haver botão/link de logout, login, cadastro ou login social. CTAs da landing que apontam para os redirects `login`/`register` são permitidos até a F7 (NOAUTH-08)
4. QUANDO o usuário abre Configurações ENTÃO DEVEM existir só Perfil, Aparência, Idioma e Integrações (mais Billing/Times até F3/F4), e NÃO DEVEM existir Senha, 2FA ou Excluir conta (NOAUTH-09)
5. QUANDO o usuário abre o Perfil ENTÃO DEVE poder alterar apenas o nome; o e-mail NÃO DEVE ser editável e NÃO DEVE haver aviso/reenvio de verificação (NOAUTH-10)

**Teste Independente**: `php artisan route:list` não lista as rotas citadas; `composer show laravel/fortify` falha; Pest de Settings cobre Perfil (nome) e ausência das telas removidas.

---

### P1: Usuário local único ⭐ MVP

**User Story**: Como usuário local, quero que exista um só usuário no app, para que meus dados e permissões sejam sempre os mesmos.

**Por que P1**: Hoje o banco tem o super root (id 1) e o usuário local (id 2), com permissões diferentes.

**Critérios de Aceite**:

1. QUANDO as migrations rodam num banco vazio ENTÃO o sistema NÃO DEVE criar o super root nem o time dele (NOAUTH-11)
2. QUANDO a aplicação sobe ENTÃO NÃO DEVEM existir a rota `/feedback`, o middleware `super.root` nem o link para feedback no menu (NOAUTH-12)
3. QUANDO `craftdb:local-user` roda ENTÃO o sistema DEVE garantir o usuário local e imprimir a URL de acesso, sem imprimir ou exigir senha (NOAUTH-13)
4. QUANDO o stack Docker sobe ENTÃO `CRAFTDB_USER_PASSWORD` NÃO DEVE ser usado nem documentado (NOAUTH-14)

**Teste Independente**: após `migrate:fresh`, `User::count()` = 0; após `craftdb:local-user`, = 1; saída do comando não contém "Senha".

---

### P1: Remover convites e papéis ⭐ MVP

**User Story**: Como usuário local, quero que o app não fale em convidar membros ou papéis, já que só existe eu.

**Por que P1**: Convites dependem de e-mail e de outros usuários, que não existem mais.

**Critérios de Aceite**:

1. QUANDO a aplicação sobe ENTÃO NÃO DEVEM existir rotas, telas, comandos agendados ou notificações de convite (`invitation.accept`, `team.members.edit`, `team.invitations.edit`, `invitations:cleanup`, `TeamInvitationNotification`) (NOAUTH-15)
2. QUANDO o usuário acessa um diagrama de um time que ele possui ENTÃO o acesso DEVE ser decidido por posse do time, sem middleware `role` nem checagem de papel (editor/viewer) em policies, gates (`createDiagramOnTeam`) ou `DiagramLimits`. Os métodos de membros/papéis/convites de `HasMembers`, `HasTeams` e `Membership` (`roles()`, `withPivot('role_id')`, `addUser`, `updateUser`, `inviteUser`, `inviteAccept`, `userHasRole`, `AddMemberToTeam`) DEVEM ser removidos; a tabela `team_user` fica (sem `role_id`) e seu destino é decidido na F4 (NOAUTH-16)
3. QUANDO o usuário cria, edita ou troca de time ENTÃO o comportamento DEVE continuar igual ao atual (limites de plano inclusos, até a F3); a tela de time (`settings/⚡team`) DEVE ser enxugada, sem seções de membros, papéis ou convites (NOAUTH-17)

**Teste Independente**: `route:list` sem rotas de convite/membros; Pest de diagrama (create/edit/export) verde sem papéis.

---

### P1: Schema e código limpos ⭐ MVP

**User Story**: Como mantenedor, quero que banco e código não tenham restos de auth, para o schema refletir o produto.

**Por que P1**: Decisão D4 do dev.

**Critérios de Aceite**:

1. QUANDO as migrations rodam ENTÃO NÃO DEVEM existir: colunas `two_factor_*`, `github_*`, `google_*` em `users`; tabelas `password_reset_tokens`, `invitation`, `role`; coluna `team_user.role_id` (a FK é removida antes da coluna — MySQL — e o drop funciona também no SQLite dos testes). `users.password` DEVE existir e ser nullable (NOAUTH-18)
2. QUANDO a migration de limpeza roda num banco que já tem dados (stack da F1) ENTÃO diagramas, times e o usuário local DEVEM ser preservados (NOAUTH-19)
3. QUANDO `php artisan db:seed` roda ENTÃO o sistema DEVE terminar sem erro (NOAUTH-20)
4. QUANDO o código é inspecionado ENTÃO NÃO DEVEM sobrar — nem referências a eles (registro em `bootstrap/providers.php`, `bootstrap/app.php`, `routes/console.php`, `SetLocale`): `app/Actions/Fortify/*`, `FortifyServiceProvider`, `AuthController`, `InvitationController`, `InvitationException` (e seu handler), `EmailAlreadyRegisteredException`, `app/Livewire/Actions/Logout.php`, `CleanupAcceptedInvitations`, `TeamInvitationNotification`, `DefaultRolesSeeder`, models `Invitation`/`Role`/`Owner`, enum `Role`, `CheckTeamRole`, `EnsureSuperRoot`, `FeedbackController`, `ValidateLoginTermsAcceptance`, `SetCurrentTeamOnLogin` só se não for mais necessário, views de auth e layouts `auth*`, componentes Livewire de senha/2FA/excluir conta/membros/convites, `routes/auth.php` (os redirects de A2 vão para `routes/web.php`), `config/fortify.php`, `config/teams.php`, views `vendor/teams`, docs de papéis (`SETUP_ROLES.md`, `docs/TEAM_ROLES_SETUP.md`). A verificação por grep exclui `resources/views/pages/landing-page` (F7) (NOAUTH-21)

**Teste Independente**: `migrate:fresh` + `Schema::hasColumn` checks em Pest; `grep -rn "Fortify\|Socialite\|Invitation" app config routes resources` sem resultados; smoke Docker sobre volume existente.

---

### P2: Testes alinhados

**User Story**: Como mantenedor, quero a suite refletindo o produto local, para que um teste verde signifique algo.

**Por que P2**: Testes de funcionalidade removida precisam sair; o resto precisa continuar passando.

**Critérios de Aceite**:

1. QUANDO a suite roda ENTÃO NÃO DEVEM existir testes de auth/2FA/reset/registro/convites/senha (NOAUTH-22)
2. QUANDO testes antes verificavam "guest é redirecionado ao login" ENTÃO DEVEM passar a verificar o login automático (NOAUTH-23)
3. QUANDO `composer test` roda ENTÃO DEVE passar 100% (NOAUTH-24)

---

## Casos Limite

- QUANDO o banco tem um usuário com o mesmo e-mail do usuário local ENTÃO o login automático usa esse usuário (comportamento de `EnsureLocalUser`, já testado na F1)
- QUANDO o banco é novo ENTÃO o super root nunca existe (NOAUTH-11). QUANDO o banco veio da F1 e ainda tem o super root (id 1) ENTÃO ele e o time dele permanecem como dados órfãos (a migration não destrói dados), nunca usados pelo login automático e nunca listados para o usuário local, que só vê os próprios times (NOAUTH-19)
- QUANDO o fluxo OAuth do MCP (`/oauth/authorize`, `/oauth/authorize/teams`) é aberto no navegador ENTÃO ele DEVE funcionar com o usuário do login automático e a tela `mcp.authorize` NÃO DEVE linkar para login (A7)
- QUANDO a sessão expira ENTÃO o próximo request autentica de novo, sem tela de login (NOAUTH-01)
- QUANDO uma rota da API MCP (`auth:api`) é chamada sem token ENTÃO continua retornando 401 — o login automático vale só para o grupo `web` (A7)

---

## Rastreabilidade de Requisitos

| ID | Story | Fase | Status |
|---|---|---|---|
| NOAUTH-01 | P1: Abrir sem login | Execute | Verified |
| NOAUTH-02 | P1: Abrir sem login | Execute | Verified |
| NOAUTH-03 | P1: Abrir sem login | Execute | Verified |
| NOAUTH-04 | P1: Abrir sem login | Execute | Verified |
| NOAUTH-05 | P1: Abrir sem login | Execute | Verified |
| NOAUTH-06 | P1: Remover auth | Execute | Verified |
| NOAUTH-07 | P1: Remover auth | Execute | Verified |
| NOAUTH-08 | P1: Remover auth | Execute | Verified |
| NOAUTH-09 | P1: Remover auth | Execute | Verified |
| NOAUTH-10 | P1: Remover auth | Execute | Verified |
| NOAUTH-11 | P1: Usuário único | Execute | Verified |
| NOAUTH-12 | P1: Usuário único | Execute | Verified |
| NOAUTH-13 | P1: Usuário único | Execute | Verified |
| NOAUTH-14 | P1: Usuário único | Execute | Verified |
| NOAUTH-15 | P1: Convites e papéis | Execute | Verified |
| NOAUTH-16 | P1: Convites e papéis | Execute | Verified |
| NOAUTH-17 | P1: Convites e papéis | Execute | Verified |
| NOAUTH-18 | P1: Schema limpo | Execute | Verified |
| NOAUTH-19 | P1: Schema limpo | Execute | Verified |
| NOAUTH-20 | P1: Schema limpo | Execute | Verified |
| NOAUTH-21 | P1: Schema limpo | Execute | Verified |
| NOAUTH-22 | P2: Testes | Execute | Verified |
| NOAUTH-23 | P2: Testes | Execute | Verified |
| NOAUTH-24 | P2: Testes | Execute | Verified |

**Cobertura:** 24 total, 24 mapeados, 24 verificados (Pest + smoke Docker + checagens T8/T9)

---

## Critérios de Sucesso

- [ ] `docker compose up -d` → abrir `http://localhost:8080` → redirect para o dashboard, sem login
- [ ] `composer test` verde; `bash docker/smoke-test.sh` verde (ajustado para sem senha)
- [ ] Nenhuma referência a Fortify, Socialite, convites, papéis ou super root em `app/`, `config/`, `routes/`, `resources/` (exceto `resources/views/pages/landing-page`, F7)

---

## Notas Técnicas

- O login automático é um middleware no grupo `web`, antes de `EnsureValidCurrentTeam`; usa `Auth::login` só quando `! Auth::check()` (A1)
- O guard `web` continua (Passport e `auth` middleware dependem dele); só o fluxo de credenciais sai
- `users.password` vira nullable porque o usuário local não tem senha
- Migrations antigas (super root) viram no-op em vez de serem apagadas, para bancos existentes não perderem o registro na tabela `migrations`
- Scopes afetados: rotas, middleware, providers, actions, models, Livewire/Blade (layouts, settings, menus), migrations, composer, Docker (entrypoint/compose/README/smoke), testes, docs

---

## Self-Review

- [x] Placeholder scan — sem TBD/TODO
- [x] Consistência interna — D1–D4 e A1–A7 refletidos nos critérios
- [x] Scope check — billing, workspaces, telemetria, MCP e landing explicitamente fora
- [x] Ambiguity check — destino de `/login` e `/register`, sessão expirada, super root órfão e API MCP definidos
- [x] YAGNI — sem modo multiusuário nem flag de modo (pivot total)

**Reviewer:** `spec-document-reviewer` (Large) — 2 issues corrigidas (`/` → dashboard; NOAUTH-08 restrito ao app autenticado) + recomendações aplicadas (HasMembers/HasTeams, lista de remoção completa, FK `role_id`, OAuth MCP)
**Status:** Aprovada pelo dev (2026-10-21)
**Data:** 2026-10-21
