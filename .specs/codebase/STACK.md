# Tech Stack

**Analisado em:** 2026-03-16

## Core

- Framework: Laravel 12
- Linguagem: PHP ^8.2
- Runtime: PHP-FPM / Artisan CLI
- Gerenciador de pacotes: Composer (PHP) + NPM (JS)

## Frontend

- Abordagem: Livewire 4 + Flux 2 (componentes UI)
- Estilização: Tailwind CSS 4 (via @tailwindcss/vite)
- Visualização: D3.js 7 para renderização do canvas de diagramas
- Canvas customizado: `resources/js/canvas/InfiniteCanvas.js`
- Gerenciamento de estado: Livewire (estado server-side, sem store client-side)

## Backend

- Estilo de API: Sem REST API dedicada — Livewire para interações reativas
- Banco de dados: Eloquent ORM + SQLite (dev) / MySQL (prod)
- Autenticação: Laravel Fortify (email/senha, 2FA, verificação de email) + Laravel Socialite (GitHub, Google)
- Filas: Database driver (jobs definidos em app/)
- Cache: File driver (local) / Redis opcional (prod)

## Testes

- Unitários/Integração: Pest 4 (PHPUnit como base)
- E2E: Não presente
- Análise estática: Larastan 3 (PHPStan para Laravel)

## Serviços Externos

- Pagamentos: Stripe via Laravel Cashier 16
- OAuth: GitHub, Google (via Laravel Socialite)
- Detecção de região: Cloudflare header `CF-IPCountry`
- Storage: AWS S3 (configurado, não verificado se em uso ativo)

## Ferramentas de Desenvolvimento

- Bundler: Vite 6 com laravel-vite-plugin 2
- Formatação: Laravel Pint
- IDE helpers: barryvdh/laravel-ide-helper 3
- Parser SQL: phpmyadmin/sql-parser 6
