# Concerns

## Documentação defasada
- AGENTS.MD cita Laravel 12; composer.json exige `laravel/framework ^13.0`

## Áreas frágeis
- Global scope `TableAccessible`: desligado em `Table` (comentado em Table.php:47) mas ainda referenciado em `Diagram.php:90`
- Acoplamento alto com usuário autenticado: 44 arquivos / 90 usos de `auth()`, `Auth::`, `->user()`; `currentTeam` em 13 arquivos
- Limites de plano espalhados por 22 arquivos (isPro, isTeamPlan, hasDiagramPremiumAccess...)
- MCP (`McpAccess`, `StdioMcpAuth`) depende de Passport + plano premium mesmo em stdio

## Tech debt
- `ParsePOstgreSQLSchema.php` (typo no nome) e `ParseSQLiteSchema.php` vazios (0 linhas)
- Migrations seedam usuário super-root com senha padrão `changeme`
- Testes rodam só em SQLite in-memory; compatibilidade com MySQL nunca foi validada

## Telemetria / saída de rede
- Laravel Nightwatch (`NIGHTWATCH_TOKEN`), Resend (mail), Google Fonts em 7 views, og:url/og:image e URLs de upgrade apontando para craftdb.app
