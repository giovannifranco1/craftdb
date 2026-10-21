# F5 — Zero telemetria — Tasks

**Spec**: `spec.md` · **Contexto**: `context.md` · **Status**: Done (2026-10-21)

> Design inline (Medium; sem decisão arquitetural além de D1–D5): remoção de pacotes/configs + fontes via `@fontsource` importadas nos CSS de entrada do Vite + teste que varre o HTML renderizado.

**Gates**: quick `./vendor/bin/pest <arquivo>` · full `composer test` · build `npm run build` · `php artisan view:cache && php artisan view:clear` · smoke `bash docker/smoke-test.sh`

```
T1 → T2 → T3
```

---

### T1: Remover Nightwatch, Resend e configs de nuvem (TDD)

**Where**: `composer.json`/`.lock`, `bootstrap/providers.php`, `config/{mail,services,filesystems,logging,queue,cache}.php`, `.env.example`, `.env.testing` (se rastreado), `phpunit.xml`, `docker-compose.yml`
**Refs**: ZT-05, ZT-06, ZT-07, ZT-08

- [ ] RED — `tests/Feature/Telemetry/NoExternalServicesTest.php`:
  - `ZT05`: `class_exists(\Laravel\Nightwatch\Nightwatch::class)` (ou a classe principal real do pacote) e `class_exists(\Resend\Laravel\ResendServiceProvider::class)` → false
  - `ZT06`: `config('filesystems.disks.s3')`, `config('services.postmark')`, `config('services.ses')`, `config('services.resend')`, `config('services.slack')`, `config('mail.mailers.postmark')`, `config('mail.mailers.resend')`, `config('mail.mailers.ses')`, `config('mail.mailers.failover')`, `config('logging.channels.slack')`, `config('logging.channels.papertrail')`, `config('queue.connections.sqs')`, `config('cache.stores.dynamodb')` → null; `Route::has` para a rota de webhook do Resend → false (descobrir o nome real com `route:list` antes)
  - `ZT07`: conteúdo de `base_path('.env.example')` e `base_path('docker-compose.yml')` não casa `/AWS_|NIGHTWATCH_|RESEND_|POSTMARK_|SLACK_|PAPERTRAIL_/`
  - `ZT08`: `config('mail.default')` → `log` sem env
- [ ] Rodar → FAIL
- [ ] `composer remove laravel/nightwatch resend/resend-laravel` (antes, remover `ResendServiceProvider` de `bootstrap/providers.php`); se `_ide_helper.php` mudar, `git checkout -- _ide_helper.php`
- [ ] Remover as entradas de config listadas; `config/mail.php` com default `log`; manter `smtp`, `log`, `array`, `sendmail` mailers
- [ ] `.env.example`: remover blocos `AWS_*`, `NIGHTWATCH_*`, `RESEND_*`, `POSTMARK_*`, `SLACK_*`, `PAPERTRAIL_*`; `MAIL_MAILER=log`; `docker-compose.yml`: remover `NIGHTWATCH_ENABLED`; `phpunit.xml`: remover linhas órfãs (`PULSE_ENABLED`, `TELESCOPE_ENABLED`) se não houver uso
- [ ] PASS; gate full

---

### T2: Fontes locais e páginas sem host externo (TDD)

**Where**: `package.json`/`package-lock.json`, `resources/css/{app,canvas,landing-page}.css`, `resources/views/_partials/{head,head-canvas}.blade.php`, `resources/views/errors/{404,500,503}.blade.php`, `resources/views/mcp/authorize.blade.php`, `resources/views/components/layouts/landing-page.blade.php`, `resources/views/components/layouts/app/header.blade.php` (apagar), `vite.config.js` (se precisar de entrada para erros)
**Refs**: ZT-01, ZT-02, ZT-03, ZT-04

- [ ] RED — `tests/Feature/Telemetry/NoExternalRequestsTest.php`, helper que extrai do HTML todos os valores de `src=`, `href=` em `<link>`/`<script>`/`<img>`/`<source>`/`<iframe>`, `content=` de `<meta property="og:*">`, e `@import`/`url(` inline; falha se algum começar com `http://`, `https://` ou `//` e não começar com `config('app.url')`. Casos:
  - `ZT01`: GET `/dashboard`, `/settings/profile`, `/settings/workspaces`, a página de um diagrama (`$this->autenticated()` + diagrama do time), `/rota-inexistente` (404)
  - `ZT01`: renderizar `view('errors.500')` e `view('errors.503')` diretamente
  - `ZT01`: GET da tela `mcp.authorize` se for possível renderizar sem Passport configurado; senão renderizar a view com dados mínimos (ver a view para as variáveis exigidas) e registrar no relatório
  - `ZT03`: GET `/features` → sem host externo, sem `goadopt`, `og:url`/`og:image` sem `craftdb.app`
  - `ZT04`: após `npm run build`, nenhum arquivo `public/build/assets/*.css` contém `url(http`, `url(//` ou `@import url(http` (pular com `markTestSkipped` se `public/build/manifest.json` não existir)
- [ ] Rodar → FAIL
- [ ] `npm install @fontsource/poppins @fontsource/outfit @fontsource/kalam @fontsource/space-mono` (versões atuais estáveis)
- [ ] Em `resources/css/app.css` e `resources/css/canvas.css` (e `landing-page.css`), importar só os pesos usados, por exemplo:

```css
@import '@fontsource/poppins/300.css';
@import '@fontsource/poppins/400.css';
@import '@fontsource/poppins/400-italic.css';
@import '@fontsource/poppins/500.css';
@import '@fontsource/poppins/600.css';
@import '@fontsource/poppins/700.css';
@import '@fontsource/outfit/400.css';
@import '@fontsource/outfit/600.css';
@import '@fontsource/outfit/700.css';
@import '@fontsource/kalam/300.css';
@import '@fontsource/kalam/400.css';
@import '@fontsource/kalam/700.css';
@import '@fontsource/space-mono/400.css';
@import '@fontsource/space-mono/700.css';
```

  (os `@import` vêm antes de qualquer regra; conferir com Tailwind 4 que os imports de pacote resolvem; o canvas e a landing só importam as famílias que usam; Outfit: conferir os pesos usados em `app-logo`, erros e landing)
- [ ] Remover todos os `<link rel="preconnect">`/`<link href="https://fonts.googleapis.com...">` de `head`, `head-canvas`, erros, `mcp/authorize`, landing
- [ ] Páginas de erro e `mcp/authorize` (HTML standalone): carregar o CSS do app via `@vite('resources/css/app.css')` (ou o CSS que já tenham) para ter as fontes locais
- [ ] Landing: remover o bloco GoAdopt (`@if(app()->environment('production')) ... goadopt ... @endif`); `og:url` → `{{ url('/') }}`, `og:image` → `{{ asset('preview.png') }}`
- [ ] Apagar `layouts/app/header.blade.php` (confirmar sem referências)
- [ ] `npm run build` → OK; `view:cache` OK; PASS; gate full
- [ ] Conferir visualmente que nada mudou de fonte: comparar a `font-family` computada de `body` (Poppins), de uma nota do canvas (Kalam) e do logo (Outfit) via teste HTML/CSS ou registrar no relatório o seletor/regra que garante cada uma

---

### T3: Docker, smoke e verificação

**Where**: `docker/smoke-test.sh`, docs na fase Docs
**Refs**: ZT-09, ZT-10

- [ ] `docker/smoke-test.sh`: novo check `ZT-09` — HTML de `/dashboard` e da página de um diagrama criado no próprio script (ou só `/dashboard` + `/settings/workspaces` se criar diagrama for complexo) não contém `fonts.googleapis|fonts.gstatic|goadopt|craftdb.app`; e o CSS referenciado no HTML é servido localmente (200)
- [ ] `bash docker/smoke-test.sh` → OK (rebuild com o código atual)
- [ ] Grep do critério de sucesso da spec → vazio (listar exceções aceitas)
- [ ] `composer test` verde; phpstan sem erros novos vs `507fceb`

---

## Validação

T1 → T2 → T3 sequenciais (T2 depende do build limpo; T3 do código final). Testes co-localizados: T1/T2 integration; T3 smoke. Cobertura: ZT-05..08 → T1 · ZT-01..04 → T2 · ZT-09, ZT-10 → T3 · 10/10.
