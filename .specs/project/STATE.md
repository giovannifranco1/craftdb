# STATE

## Segurança (ação do dev)
- 2026-10-21 (F6): `.cursor/mcp.json` versionado (commit `7b07eca`, já em `origin/main` e `origin/develop`) contém um JWT real em `MCP_BEARER_TOKEN`, assinado pelas chaves Passport do `.env` de dev. A F6 remove Passport e o segredo do arquivo, mas o token continua no histórico remoto. **Ação do dev:** se essas chaves Passport foram usadas em algum ambiente (ex.: SaaS em produção), rotacioná-las/revogar o token; reescrever histórico fica a critério do dev (destrutivo, não feito pelo agente).

## Decisões
- 2026-10-21: **Regra do dev (via Lead, dev ausente)** — cada fase do ROADMAP vira **exatamente UM commit**, **nunca fazer push**; nos gates de commit escolher 1 commit e seguir direto para a fase seguinte, sem esperar teste do dev (ele testa só no final). Fases seguem na branch atual `feature/local-user-no-auth`. Aprovações de spec/design/tasks seguem as opções recomendadas, registradas no `context.md` de cada feature.
- 2026-10-21: **Pivot total** — o SaaS (craftdb.app) será descontinuado; este repo vira só o CraftDB Local. F2/F3/F5 removem auth, billing e telemetria de vez (sem flag de modo, sem fork).

## Lições
- 2026-10-21 (F1): Docker Compose lê o `.env` da raiz para interpolar `${VAR}` — é o `.env` de dev do Laravel. Variáveis do stack local usam prefixo `CRAFTDB_` (ex.: `CRAFTDB_APP_KEY`) para não herdar valores de dev.

## Deferred
- 2026-10-21 (F2): `HasMembers::purge()` sem chamadas e `users()` com pivot vazio → revisar na F4 (workspaces).
- 2026-10-21 (F2): `DiagramPolicy::view` usa `belongsToTeam` (consulta `team_user`) → simplificar para posse na F4.
- 2026-10-21 (F2): `/my-favorites` aponta para view inexistente (`livewire.my-favorites`) — quebrada antes da F2.
- 2026-10-21 (F2): links do starter-kit (`github.com/laravel/livewire-starter-kit`) em `layouts/app/header.blade.php`.
- 2026-10-21 (F2): `config/landingpage.php:79` anuncia Fortify/2FA/recuperação de senha → reescrever/remover na F7.
- 2026-10-21 (F2): `App\Models\Team\Membership` ficou sem uso real (só docblocks) → apagar junto com a decisão de `team_user` na F4.
- 2026-10-21 (F4): ~60 chaves `__()` sem tradução pt_BR (páginas MCP/integrações, erros, tooltips do canvas) — pré-existentes; revisar numa passada de i18n (F7 ou M2).
- 2026-10-21 (F4): `session('current_team')` guarda o model Team serializado e é re-buscado a cada uso (até 4–5 finds por página; rename precisa regravar a sessão) → guardar só `current_team_id` e resolver com `ownedTeams()->find()` (mudança de arquitetura em ~8 pontos + testes; fazer numa fase própria).
- 2026-10-21 (F5): aviso do esbuild em todo build desde a F1 — comentário CSS sem `/* */` ("min-height: 177px !important; Ajuste conforme sua necessidade", grep em resources/css) → corrigir num quick fix.
- 2026-10-21 (F6): `docs/README.md` seção "Deploy em Produção" (OAuth GitHub/Google, checklist SaaS) defasada desde a F2 → remover/reescrever na F7.
