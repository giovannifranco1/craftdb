# STATE

## Decisões
- 2026-10-21 (F1): entrypoint não roda `route:cache`. Causa raiz: `routes/ai.php` é carregado duas vezes — `bootstrap/app.php` (`api:` → prefixo `/api`) e `McpServiceProvider::registerRoutes()` (sem prefixo) — duplicando nomes como `mcp.oauth.protected-resource`, e `route:cache` lança LogicException (o SaaS em produção também não consegue rodar `optimize`). Fix candidato: remover a linha `api:` do `bootstrap/app.php` (some só a cópia `/api/mcp/...`, sem consumidores no repo). Pendente de decisão do dev; até lá o entrypoint usa `config:cache` e o build faz `event:cache` + `view:cache`.

## Lições
- 2026-10-21 (F1): Docker Compose lê o `.env` da raiz para interpolar `${VAR}` — é o `.env` de dev do Laravel. Variáveis do stack local usam prefixo `CRAFTDB_` (ex.: `CRAFTDB_APP_KEY`) para não herdar valores de dev.
