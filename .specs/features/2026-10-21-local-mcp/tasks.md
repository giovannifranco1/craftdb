# F6 — MCP local sem Passport — Tasks

**Spec**: `spec.md` · **Contexto**: `context.md` · **Status**: Done (2026-10-21)

> Design inline: (1) `McpAccess::user()` passa a ser `Auth::user()` (testes com `actingAs`) **ou** o usuário local via `EnsureLocalUser` — nunca null; (2) middleware novo `EnsureLocalMcpOrigin` na rota HTTP no lugar de `auth:api`; (3) remoção de Passport/OAuth de dentro para fora (consumidores antes do pacote); (4) `route:cache` volta tirando a carga duplicada de `routes/ai.php`.

**Gates**: quick `./vendor/bin/pest <arquivos>` · full `composer test` · routes `php artisan route:list --no-ansi` · cache `APP_ROUTES_CACHE=$(mktemp) php artisan route:cache` (apagar o arquivo depois) · smoke `bash docker/smoke-test.sh`

**Regras**: remoção = apagar + grep e ajustar referências; testes com ID (`MCP01…`); gate full verde ao fim de cada task; não tocar landing.

```
T1 → T2 → T3
```

---

### T1: MCP como usuário local + proteção de Origin (TDD)

**Where**: `app/Mcp/McpAccess.php`, `app/Http/Middleware/EnsureLocalMcpOrigin.php` (novo), `routes/ai.php`, `bootstrap/app.php` (alias se precisar), `app/Mcp/Servers/CraftdbServer.php` (tirar `StdioMcpAuth::bootstrapIfStdio`), `app/Mcp/StdioMcpAuth.php` (apagar), `config/craftdb_mcp.php` (apagar), `tests/Feature/Mcp/*`
**Refs**: MCP-01, MCP-02, MCP-03, MCP-04, MCP-05

- [ ] RED — `tests/Feature/Mcp/LocalMcpTest.php`:
  - `MCP01`: `POST /mcp/craftdb` com JSON-RPC `initialize` (copiar o formato de request usado nos testes MCP existentes / `laravel/mcp`) sem `Authorization` e sem `Origin` → 200 e resposta JSON-RPC válida; repetir com `Origin: http://localhost:8080` e `Origin: http://127.0.0.1:8080` → 200
  - `MCP02`: mesmo request com `Origin: http://evil.example` e com `Origin: null` → 403
  - `MCP03`: banco com o usuário local (via `EnsureLocalUser`) dono de 2 workspaces e outro usuário dono de 1 workspace com diagrama → `tools/call list_teams` via HTTP lista só os 2; `list_diagrams` só os diagramas do usuário local
  - `MCP04`: `tools/call get_diagram` (ou outra tool de leitura) num diagrama do outro usuário → resposta de erro de autorização
  - `MCP05`: com nenhum usuário no banco, chamar uma tool via o servidor (HTTP ou `CraftdbServer` direto) → cria o usuário local e responde
- [ ] Rodar → FAIL
- [ ] `McpAccess::user()`: `return Auth::user() ?? app(EnsureLocalUser::class)->exec((string) config('craftdb.local_user.email'))['user'];` (tipo de retorno `User`, nunca null); remover os ramos "sem usuário pula checagem" em `assertDiagramAbility`, `assertCanCreateDiagramOnTeam`, `teamIdsForUser` (agora sempre há usuário); remover `tokenAllowedTeamIds`/`assertTokenAllowsTeam` e uso de `McpTokenTeam`
- [ ] `EnsureLocalMcpOrigin`: sem header `Origin` → passa; com `Origin` cujo host é `localhost`, `127.0.0.1`, `[::1]` ou o host de `config('app.url')` → passa; qualquer outro (inclusive `null`) → `abort(403)`
- [ ] `routes/ai.php`: `Mcp::web('/mcp/craftdb', ...)->middleware([EnsureLocalMcpOrigin::class, 'throttle:mcp'])` (sem `auth:api`); remover `Mcp::oauthRoutes()` e a rota `oauth/authorize/teams` só na T2 (deixar aqui se ainda compilar)
- [ ] stdio: tirar `StdioMcpAuth::bootstrapIfStdio` do `CraftdbServer::boot`; apagar `StdioMcpAuth.php`, `config/craftdb_mcp.php`, `tests/Feature/Mcp/StdioMcpAuthTest.php`
- [ ] `McpToolsAuthorizationTest`: remover `passport:client` do `beforeEach`, o teste FREE22 (token restrito a outro time — conceito removido) e o "401 sem auth" (agora é MCP01); manter os demais com `actingAs`
- [ ] PASS; gate full

---

### T2: Remover Passport e OAuth (TDD)

**Where**: `composer.json`/`.lock`, `config/passport.php`, `config/auth.php` (guard `api`), `app/Models/User.php`, `app/Providers/AppServiceProvider.php` (`configurePassportMcp`, `Passport::*`, limiter `mcp` fica, por IP), `app/Mcp/Registrar.php` (remover `oauthRoutes`/`ensureMcpScope` e o `McpAddWwwAuthenticateHeader` do `web()`; se o override ficar igual ao vendor, apagar a classe e o binding), `app/Http/Controllers/Mcp/{OAuthRegisterController,AuthorizeController}.php`, `app/Listeners/Mcp/StoreMcpTokenTeamsListener.php`, `app/Models/McpTokenTeam.php`, `app/Http/Middleware/{McpAddWwwAuthenticateHeader,ForceJsonResponseForMcpOAuthRegistration}.php` (+ registro global em `bootstrap/app.php`), `resources/views/mcp/authorize.blade.php`, `routes/ai.php` (rotas OAuth), `database/migrations/2026_10_21_000004_remove_passport_tables.php` (novo), `tests/Feature/Mcp/OAuthRegisterAcceptsJsonTest.php` (apagar), `.env.example`, `.cursor/mcp.json`
**Depends on**: T1 · **Refs**: MCP-06, MCP-07, MCP-08, MCP-09, MCP-10

- [ ] RED — `tests/Feature/Mcp/PassportRemovedTest.php`:
  - `MCP06`: `class_exists(\Laravel\Passport\Passport::class)` → false
  - `MCP07`: `Route::has` falso para `passport.authorizations.authorize`, `passport.token`, `mcp.authorize.approve`, `mcp.oauth.protected-resource`, `mcp.oauth.authorization-server`; nenhuma rota com URI começando em `oauth/`, `.well-known/oauth` ou `api/` (`collect(Route::getRoutes())`)
  - `MCP08`: `config('auth.guards.api')` → null; `in_array(HasApiTokens::class ...)` não aplicável sem o pacote → `class_uses_recursive(User::class)` não contém nenhum trait do namespace `Laravel\Passport`; `class_exists` falso para `App\Mcp\StdioMcpAuth`, `App\Models\McpTokenTeam`
  - `MCP09`: `Schema::hasTable` falso para `oauth_auth_codes`, `oauth_access_tokens`, `oauth_refresh_tokens`, `oauth_clients`, `oauth_device_codes`, `mcp_token_teams`
  - `MCP10`: `.env.example` e `docker-compose.yml` sem `/PASSPORT_|MCP_BEARER|MCP_STDIO/`; `.cursor/mcp.json` é JSON válido sem `env`/token e com `mcpServers.craftdb.url` = `http://localhost:8080/mcp/craftdb`
- [ ] Rodar → FAIL
- [ ] Remover consumidores (lista do **Where**) antes de `composer remove laravel/passport`; `_ide_helper.php` regenerado → `git checkout -- _ide_helper.php`
- [ ] Migration `2026_10_21_000004_remove_passport_tables.php`: `up()` `dropIfExists` das 6 tabelas; `down()` vazio com comentário "irreversível: Passport removido" (as migrations antigas do Passport ficam, como as do F2/F3 — mas elas leem `config('passport.connection')`: trocar por `null`/conexão padrão se quebrarem sem o config)
- [ ] `.cursor/mcp.json` → `{"mcpServers":{"craftdb":{"url":"http://localhost:8080/mcp/craftdb"}}}`; `.env.example` sem `PASSPORT_*`, `MCP_BEARER_TOKEN`, `MCP_STDIO_REQUIRES_BEARER`
- [ ] Grep do critério de sucesso → só migrations e o teste de guarda
- [ ] PASS; gate full; rollback/migrate num SQLite **temporário** (`T=$(mktemp); DB_CONNECTION=sqlite DB_DATABASE=$T php artisan migrate:fresh --force; rm -f $T`)

---

### T3: route:cache, tela de integrações e smoke MCP (TDD)

**Where**: `bootstrap/app.php` (`api:`), `docker/entrypoint.sh`, `resources/views/components/settings/⚡integrations.blade.php`, `lang/pt_BR.json`, `docker/smoke-test.sh`
**Depends on**: T2 · **Refs**: MCP-11, MCP-12, MCP-13, MCP-14, MCP-15

- [ ] RED — `tests/Feature/Mcp/RouteCacheTest.php` `MCP11`: `Artisan::call('route:cache')` com `APP_ROUTES_CACHE` apontando para arquivo temporário (via `putenv`/`$_ENV` antes, ou rodar `php artisan route:cache` num `Process` com env) → exit 0; apagar o arquivo
- [ ] RED — `tests/Feature/Settings/IntegrationsPageTest.php` `MCP13`: GET `/settings/integrations` contém `http://localhost` + `/mcp/craftdb`, contém `docker compose exec -T app php artisan mcp:start craftdb`, e **não** contém (case-insensitive) `log in`, `login`, `authorize`, `autoriz`, `token`, `OAuth`
- [ ] Rodar → FAIL
- [ ] `bootstrap/app.php`: remover `api: __DIR__.'/../routes/ai.php'` do `withRouting`
- [ ] `docker/entrypoint.sh`: trocar o bloco de `config:cache` pelo `php artisan optimize --no-ansi` (config + route + event + view) e remover o comentário sobre a falha do `route:cache`; conferir que `event:cache`/`view:cache` do build continuam fazendo sentido (podem sair do Dockerfile se o `optimize` no boot cobre)
- [ ] Tela de integrações: textos sem login/autorização; blocos Cursor (deeplink + JSON com `url`), Claude Code (`claude mcp add --transport http craftdb <url>`), Claude Desktop (JSON com `url`) e stdio (`docker compose exec -T app php artisan mcp:start craftdb`, com JSON de exemplo `command`/`args`); strings novas com tradução pt_BR
- [ ] `docker/smoke-test.sh` `MCP-14`: `curl` POST JSON-RPC `initialize` em `/mcp/craftdb` (headers `Content-Type: application/json`, `Accept: application/json, text/event-stream`) → 200; capturar `Mcp-Session-Id` se houver e fazer `tools/call` `list_teams` → resposta contém "Local Workspace"; mesmo POST com `Origin: http://evil.example` → 403; o entrypoint cacheou rotas (`docker compose exec -T app test -f bootstrap/cache/routes-v7.php` ou o nome real)
- [ ] `bash docker/smoke-test.sh` → OK; `composer test` verde; phpstan sem erros novos vs `e1cf913`

---

## Validação

T1 → T2 → T3 (T2 depende de T1 ter tirado os consumidores de token; T3 de não haver mais rotas OAuth duplicadas). Cobertura: MCP-01..05 → T1 · MCP-06..10 → T2 · MCP-11..15 → T3 · 15/15.
