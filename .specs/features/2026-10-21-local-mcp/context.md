# Contexto — F6 MCP local sem Passport (+ fix do route:cache)

**Origem:** ROADMAP M1/F6 · pivot total · regra: 1 commit por fase, sem push
**Data:** 2026-10-21
**Escopo:** Large · defaults (subagents + two-stage review)
**Aprovações:** dev ausente — decisões do **Lead** seguindo as opções recomendadas

## Decisões (Lead, dev ausente)

| # | Decisão | Alternativas descartadas |
|---|---|---|
| D1 | **Remover Passport e todo o OAuth do MCP**: pacote `laravel/passport`, `config/passport.php`, guard `api`, `HasApiTokens`/`OAuthenticatable` no `User`, `Registrar::oauthRoutes` (well-known + `oauth/register`), `OAuthRegisterController`, `Mcp/AuthorizeController`, `StoreMcpTokenTeamsListener`, `McpTokenTeam`, view `mcp/authorize`, `McpAddWwwAuthenticateHeader`, `ForceJsonResponseForMcpOAuthRegistration`, `StdioMcpAuth`, `config/craftdb_mcp.php`, `MCP_BEARER_TOKEN`/`MCP_STDIO_*`/`PASSPORT_*` no `.env.example`. Migration nova apaga `oauth_*` e `mcp_token_teams` | Manter Passport com chaves geradas no entrypoint |
| D2 | **MCP sempre age como o usuário local**: `McpAccess::user()` resolve via `EnsureLocalUser` (`config('craftdb.local_user.email')`) em stdio e HTTP; toda checagem de posse continua (nunca "sem usuário = tudo liberado") | Deixar sem usuário (bypass de todas as checagens) |
| D3 | **HTTP `/mcp/craftdb` sem token, protegido por**: bind 127.0.0.1 (já no compose), validação de `Origin` (só ausente ou `localhost`/`127.0.0.1`/`[::1]`/`APP_URL`; senão 403) contra DNS rebinding, e `throttle:mcp` | Token estático local |
| D4 | **Fix do `route:cache` na raiz**: remover `api: routes/ai.php` do `bootstrap/app.php` (as cópias `/api/*` não têm consumidores); entrypoint volta a `php artisan optimize` (ou adiciona `route:cache`) | Continuar sem cache de rotas |
| D5 | **Tela de integrações reescrita**: URL HTTP local para Cursor/Claude Code/Claude Desktop **sem** texto de login/autorização + opção stdio via `docker compose exec -T app php artisan mcp:start craftdb` | — |
| D6 | **`.cursor/mcp.json` sem segredo**: vira config HTTP `{"url": "http://localhost:8080/mcp/craftdb"}` (o token antigo fica registrado no STATE como ação do dev) | Apagar o arquivo |
| D7 | Nomes de tools **`list_teams`/`team_id` mantidos** (contrato com clientes); descrições e instruções falam "workspace" | Renomear para `list_workspaces`/`workspace_id` |

## Fatos (mapeamento 2026-10-21)

- `routes/ai.php` é carregado duas vezes (`bootstrap/app.php` `api:` + `McpServiceProvider::registerRoutes`) → nomes duplicados quebram `route:cache` (reproduzido com `APP_ROUTES_CACHE` temporário)
- `McpAccess` pula todas as checagens quando não há usuário (`teamIdsForUser` → null = todos os diagramas)
- `/mcp/craftdb` usa só `auth:api` + `throttle:mcp`; não está no grupo `web` (sem `AuthenticateLocalUser`, sem CSRF)
- Proxies `trust *` (`bootstrap/app.php` e `AppServiceProvider`) → IP/host do request são falsificáveis; bind 127.0.0.1 é a barreira real; `Origin` é a defesa contra navegador
- `McpToolsAuthorizationTest` usa `passport:client`, `AccessToken`, `withAccessToken` (FREE22) e espera 401 sem auth
- Tela `settings/⚡integrations` promete login OAuth (Cursor/Claude Desktop)
- `.cursor/mcp.json` versionado com JWT real (ver STATE "Segurança")
