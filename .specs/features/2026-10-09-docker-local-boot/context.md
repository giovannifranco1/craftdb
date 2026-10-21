# Contexto — F1 Docker + MySQL + boot automático

**Origem:** pivot "CraftDB Local" (nota Maestri "Discovery CraftDB Local", `.specs/project/PROJECT.md`, `ROADMAP.md`)
**Data:** 2026-10-09
**Escopo:** Large · defaults do processo mantidos (subagents + two-stage review)

## Decisões do dev

| # | Decisão | Alternativas descartadas |
|---|---|---|
| D1 | Banco: **MySQL** em container próprio | Postgres (sugerido antes; dev tem mais experiência e testes com MySQL), SQLite |
| D2 | Imagem do app: **FrankenPHP** (Caddy + PHP num processo) | serversideup/php, php-fpm + nginx manual |
| D3 | Login **continua** na F1; usuário entra com um **usuário local criado no boot** (e-mail já verificado, com workspace), credenciais exibidas no log | Juntar F1+F2 (auto-login já agora); cadastro normal com verificação via log |
| D4 | Porta do MySQL publicada em **127.0.0.1:3307** (configurável) | 5432/3306 padrão (conflito com banco local), não expor |
| D5 | App publicado só em **127.0.0.1** | 0.0.0.0 (exporia na rede) |
| D6 | Boot via **entrypoint shell + comando Artisan** `craftdb:local-user` (Action testável) | Serviço `init` one-shot; tudo em shell (Design, 2026-10-20) |
| D7 | HTTPS forçado controlado por **flag `APP_FORCE_HTTPS`** (default true; compose seta false) | `APP_ENV=local` no container (Design, 2026-10-20) |

## Fatos do codebase relevantes
- `.env.example` usa `DB_CONNECTION=sqlite`; sessions, cache e queue usam driver `database`
- Não há SQL específico de MySQL; 33 migrations nunca validadas em MySQL (testes rodam em SQLite in-memory)
- Migrations `2026_03_08_155628_add_super_root_user` e `..._insert_super_root_has_team` já criam o usuário id 1 + team usando `SUPER_ROOT_*` (padrão `changeme`), **sem** `email_verified_at`
- Rotas do dashboard exigem `auth` + `verified`
- `composer setup` já descreve o fluxo: install → key:generate → migrate → npm build
- Não há jobs `ShouldQueue`; único agendamento é `invitations:cleanup` (removido na F2)
- Nightwatch está em composer.json (`NIGHTWATCH_TOKEN`); telemetria é tratada na F5

## Consequências aceitas (temporárias)
- Usuário local fica sujeito aos limites do plano Free até a F3
- Google Fonts e Nightwatch ainda presentes até a F5
