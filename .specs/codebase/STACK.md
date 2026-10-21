# Tech Stack

**Analisado em:** 2026-03-16

## Core

- Framework: Laravel 13
- Linguagem: PHP ^8.2 no composer.json (dependências do lock exigem >=8.3; a imagem Docker usa 8.4)
- Runtime: PHP-FPM / Artisan CLI (SaaS) · FrankenPHP na imagem Docker do CraftDB Local
- Gerenciador de pacotes: Composer (PHP) + NPM (JS)

## Frontend

- Abordagem: Livewire 4 + Flux 2 (componentes UI)
- Estilização: Tailwind CSS 4 (via @tailwindcss/vite)
- Interatividade client-side: Alpine.js (bundled com Livewire 4) — usado para os Alpine data stores do canvas
- Visualização: D3.js 7 para grafo de FK e grafo de diagrama (dentro do canvas)
- Canvas customizado: `resources/js/canvas/classes/InfiniteCanvas.js` — HTML5 Canvas 2D, pan/zoom, touch
- Gerenciamento de estado: Livewire (estado server-side) + Alpine.js stores (estado client-side do canvas)

## Backend

- Estilo de API: Sem REST API dedicada — Livewire para interações reativas
- Banco de dados: Eloquent ORM + SQLite (dev) / MySQL (prod) / MySQL 8.4 em container (CraftDB Local)
- Autenticação: Sem login — usuário local automático (`AuthenticateLocalUser`); o MCP age como o usuário local (sem Passport/OAuth/token — removidos na F6)
- Filas: Database driver (jobs definidos em app/)
- Cache: File driver (local) / Redis opcional (prod)

## Testes

- Unitários/Integração: Pest 4 (PHPUnit como base)
- E2E: Não presente
- Análise estática: Larastan 3 (PHPStan para Laravel)

## Serviços Externos

- Pagamentos/região: nenhum (Cashier/Stripe e `cf-ipcountry` removidos na F3)
- Storage: disco local (S3/AWS removido na F5)
- Telemetria/e-mail externo: nenhum (Nightwatch e Resend removidos na F5; mailer padrão `log`)
- Fontes: self-hosted via `@fontsource` (Poppins, Outfit, Kalam, Space Mono; subsets latin e latin-ext), bundladas pelo Vite — sem Google Fonts

## Ferramentas de Desenvolvimento

- Bundler: Vite 6 com laravel-vite-plugin 2
- Formatação: Laravel Pint
- IDE helpers: barryvdh/laravel-ide-helper 3
- Parser SQL: phpmyadmin/sql-parser 6 (MySQL); parsers customizados para PostgreSQL e SQLite em `app/Actions/Parse/BaseSQLParse.php`

## Distribuição local (Docker)

- `Dockerfile` multi-stage: `vendor` (composer, `--no-dev`) → `assets` (Node 22, `npm run build`) → `app` (`dunglas/frankenphp:1-php8.4-bookworm`, sem Node em runtime; `event:cache` e `view:cache` no build)
- `docker-compose.yml`: serviços `app` (127.0.0.1:8080) + `mysql:8.4` (127.0.0.1:3307), volumes `craftdb-mysql` e `craftdb-data` (`/data/app.key`)
- `docker/entrypoint.sh`: APP_KEY (env > volume > gera) → espera MySQL → `config:cache` → `migrate --force` → `craftdb:local-user` → `frankenphp run`
- Variáveis do stack usam prefixo `CRAFTDB_` porque o Compose lê o `.env` de dev da raiz

