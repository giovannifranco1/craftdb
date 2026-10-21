# STATE

## Decisões
- 2026-10-21: **Regra do dev (via Lead, dev ausente)** — cada fase do ROADMAP vira **exatamente UM commit**, **nunca fazer push**; nos gates de commit escolher 1 commit e seguir direto para a fase seguinte, sem esperar teste do dev (ele testa só no final). Fases seguem na branch atual `feature/local-user-no-auth`. Aprovações de spec/design/tasks seguem as opções recomendadas, registradas no `context.md` de cada feature.
- 2026-10-21: **Pivot total** — o SaaS (craftdb.app) será descontinuado; este repo vira só o CraftDB Local. F2/F3/F5 removem auth, billing e telemetria de vez (sem flag de modo, sem fork).
- 2026-10-21 (F1): entrypoint não roda `route:cache`. Causa raiz: `routes/ai.php` é carregado duas vezes — `bootstrap/app.php` (`api:` → prefixo `/api`) e `McpServiceProvider::registerRoutes()` (sem prefixo) — duplicando nomes como `mcp.oauth.protected-resource`, e `route:cache` lança LogicException (o SaaS em produção também não consegue rodar `optimize`). Fix candidato: remover a linha `api:` do `bootstrap/app.php` (some só a cópia `/api/mcp/...`, sem consumidores no repo). Pendente de decisão do dev; até lá o entrypoint usa `config:cache` e o build faz `event:cache` + `view:cache`.

## Lições
- 2026-10-21 (F1): Docker Compose lê o `.env` da raiz para interpolar `${VAR}` — é o `.env` de dev do Laravel. Variáveis do stack local usam prefixo `CRAFTDB_` (ex.: `CRAFTDB_APP_KEY`) para não herdar valores de dev.

## Deferred
- 2026-10-21 (F2): `HasMembers::purge()` sem chamadas e `users()` com pivot vazio → revisar na F4 (workspaces).
- 2026-10-21 (F2): `User.php` (~157) carrega `owner/users` com comentário "invited members inherit" (plano herdado por membros) → remover na F3 (billing).
- 2026-10-21 (F2): `DiagramPolicy::view` usa `belongsToTeam` (consulta `team_user`) → simplificar para posse na F4.
- 2026-10-21 (F2): `/my-favorites` aponta para view inexistente (`livewire.my-favorites`) — quebrada antes da F2.
- 2026-10-21 (F2): links do starter-kit (`github.com/laravel/livewire-starter-kit`) em `layouts/app/header.blade.php`.
- 2026-10-21 (F2): `config/landingpage.php:79` anuncia Fortify/2FA/recuperação de senha → reescrever/remover na F7.
- 2026-10-21 (F2): Docker não provisiona chaves do Passport (`oauth/authorize` dá 500 `Invalid key supplied`); com chaves geradas o fluxo abre a tela de consentimento sem pedir login → provisionar (ou eliminar) na F6.
- 2026-10-21 (F2): `App\Models\Team\Membership` ficou sem uso real (só docblocks) → apagar junto com a decisão de `team_user` na F4.
