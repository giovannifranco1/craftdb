# F6 — MCP local sem Passport — Especificação

## Problema

O MCP do CraftDB Local ainda exige OAuth/Passport (HTTP) ou um JWT no ambiente (stdio), mas o Docker não gera chaves do Passport — então o MCP não funciona de fábrica. Além disso, `route:cache` quebra por rotas duplicadas, e um JWT real está versionado em `.cursor/mcp.json`.

## Objetivos

- [ ] Agentes de IA usam o MCP local sem token nem login (HTTP e stdio)
- [ ] Toda ação do MCP é feita como o usuário local, com as mesmas regras de posse do app
- [ ] `php artisan route:cache` funciona
- [ ] Nenhum Passport/OAuth/segredo no projeto

## Fora do Escopo

| Item | Motivo |
|---|---|
| Renomear tools `list_teams`/`team_id` | D7 |
| Reescrever histórico git do token | Ação do dev (STATE) |
| Landing | F7 |

## User Stories

### P1: MCP HTTP local sem token ⭐ MVP

1. QUANDO um cliente MCP faz `POST /mcp/craftdb` sem `Authorization` e sem `Origin` (ou com `Origin` localhost/127.0.0.1/[::1]/APP_URL) ENTÃO o sistema DEVE atender como o usuário local (MCP-01)
2. QUANDO o `Origin` é de outro host ENTÃO o sistema DEVE responder 403 sem executar nada (MCP-02)
3. QUANDO `list_teams`/`list_diagrams` são chamadas ENTÃO DEVEM listar só os workspaces/diagramas do usuário local (MCP-03)
4. QUANDO uma tool tenta agir sobre diagrama/workspace de outro usuário (existente no banco) ENTÃO o sistema DEVE negar (MCP-04)

### P1: MCP stdio sem token ⭐ MVP

1. QUANDO `php artisan mcp:start craftdb` roda sem `MCP_BEARER_TOKEN` ENTÃO o servidor DEVE subir e agir como o usuário local (criando-o se não existir) (MCP-05)

### P1: Sem Passport/OAuth ⭐ MVP

1. QUANDO o `composer.json` é inspecionado ENTÃO `laravel/passport` NÃO DEVE estar presente e o app DEVE subir sem ele (MCP-06)
2. QUANDO as rotas são listadas ENTÃO NÃO DEVEM existir `oauth/*`, `.well-known/oauth-*`, `oauth/register`, `oauth/authorize/teams` nem cópias `api/*` (MCP-07)
3. QUANDO o código é inspecionado ENTÃO NÃO DEVEM existir guard `api`, `HasApiTokens`, `StdioMcpAuth`, `McpTokenTeam`, listener/controllers de OAuth do MCP, view `mcp/authorize`, `config/passport.php`, `config/craftdb_mcp.php` (MCP-08)
4. QUANDO as migrations rodam ENTÃO as tabelas `oauth_auth_codes`, `oauth_access_tokens`, `oauth_refresh_tokens`, `oauth_clients`, `oauth_device_codes` e `mcp_token_teams` NÃO DEVEM existir, preservando os dados do app (MCP-09)
5. QUANDO `.env.example`, `docker-compose.yml` e `.cursor/mcp.json` são inspecionados ENTÃO NÃO DEVE haver `PASSPORT_*`, `MCP_BEARER_TOKEN`, `MCP_STDIO_*` nem token (MCP-10)

### P1: route:cache ⭐ MVP

1. QUANDO `php artisan route:cache` roda ENTÃO DEVE concluir sem erro (MCP-11)
2. QUANDO o container sobe ENTÃO o entrypoint DEVE cachear rotas também (MCP-12)

### P2: Tela de integrações

1. QUANDO o usuário abre `settings/integrations` ENTÃO DEVE ver a URL local do MCP e instruções para Cursor, Claude Code e Claude Desktop **sem** menção a login/autorização/token, e a opção stdio via `docker compose exec -T app php artisan mcp:start craftdb` (MCP-13)

### P2: Prova

1. QUANDO o smoke Docker roda ENTÃO uma chamada MCP HTTP real (`initialize` + `tools/call list_teams`) DEVE funcionar sem token e uma com `Origin: http://evil.example` DEVE dar 403 (MCP-14)
2. QUANDO `composer test` e o smoke rodam ENTÃO DEVEM passar (MCP-15)

## Casos Limite

- `Origin: null` (string literal) → tratado como externo (403)
- Usuário local ainda não existe na primeira chamada MCP → criado por `EnsureLocalUser` (MCP-05)
- Banco com tokens/clients antigos → tabelas apagadas pela migration (MCP-09)

## Rastreabilidade

| ID | Story | Fase | Status |
|---|---|---|---|
| MCP-01 | HTTP | Execute | Verified |
| MCP-02 | HTTP | Execute | Verified |
| MCP-03 | HTTP | Execute | Verified |
| MCP-04 | HTTP | Execute | Verified |
| MCP-05 | stdio | Execute | Verified |
| MCP-06 | Sem Passport | Execute | Verified |
| MCP-07 | Sem Passport | Execute | Verified |
| MCP-08 | Sem Passport | Execute | Verified |
| MCP-09 | Sem Passport | Execute | Verified |
| MCP-10 | Sem Passport | Execute | Verified |
| MCP-11 | route:cache | Execute | Verified |
| MCP-12 | route:cache | Execute | Verified |
| MCP-13 | Integrações | Execute | Verified |
| MCP-14 | Prova | Execute | Verified |
| MCP-15 | Prova | Execute | Verified |

**Cobertura:** 15 total, 15 mapeados, 15 verificados (Pest + smoke MCP-12/14)

## Critérios de Sucesso

- [ ] `grep -rniE "passport|oauth|HasApiTokens|MCP_BEARER|bearer|tokenCan|mcp_token_teams|McpTokenTeam|StdioMcpAuth" app bootstrap config routes resources database/factories tests .env.example docker-compose.yml .cursor composer.json` vazio (exceto migrations antigas e a de limpeza, e textos do teste de guarda)
- [ ] `route:cache` OK; `composer test` e smoke verdes

**Status:** Aprovada pelo Lead (dev ausente)
**Data:** 2026-10-21
