# Concerns

## Documentação defasada
- AGENTS.MD cita Laravel 12; composer.json exige `laravel/framework ^13.0`

## Áreas frágeis
- Global scope `TableAccessible`: desligado em `Table` (comentado em Table.php:47) mas ainda referenciado em `Diagram.php:90`
- Acoplamento alto com usuário autenticado: 44 arquivos / 90 usos de `auth()`, `Auth::`, `->user()`; `currentTeam` em 13 arquivos (workspace atual; nome interno `Team`)
- Nome interno `Team`/`team`/`team_id` diverge do termo de UI "Workspace"; MCP mantém `list_teams`/`team_id` (F6). Renomeação interna é uma consideração futura
- MCP local (F6, resolvido): sem Passport/token; `McpAccess::user()` usa o usuário local. HTTP `/mcp/craftdb` é sem auth, protegido só por bind `127.0.0.1` + `EnsureLocalMcpOrigin` + `throttle:mcp` — se o bind for exposto em rede, qualquer host alcança as tools. Checagens de plano/limite foram removidas na F3
- Seção de preços da landing page (`/pricing`) ainda existe apesar de não haver planos (F7)

## Tech debt
- `ParsePOstgreSQLSchema.php` (typo no nome) e `ParseSQLiteSchema.php` vazios (0 linhas)
- Migrations seedam usuário super-root com senha padrão `changeme`
- Testes rodam só em SQLite in-memory; compatibilidade com MySQL nunca foi validada

## Telemetria / saída de rede
- Resolvido na F5 (2026-10-21): Nightwatch, Resend, S3 e demais serviços externos removidos; Google Fonts substituído por fontes self-hosted (`@fontsource`); og:url/og:image usam `url()`/`asset()`; script GoAdopt removido da landing
- Guardas: `tests/Feature/Telemetry/` e check ZT-09 do smoke test
- Pendente fora da F5: landing pages (F7), formulário `contact-us`
