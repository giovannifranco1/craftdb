# F7 — Remoção da landing + página Apoiar — Tasks

**Spec**: `spec.md` · **Contexto**: `context.md` · **Status**: Done (2026-10-21)

> Design inline: remoção da landing de fora para dentro (rotas → controller → views/CSS/config/lang/imagens); `redirectGuestsTo('/dashboard')` no `bootstrap/app.php`; página `/support` como componente Livewire de página simples (sem estado, só leitura de config) no layout do app, padrão dos settings/dashboard; links em `config/craftdb.php` → `support`.

**Gates**: quick `./vendor/bin/pest <arquivos>` · full `composer test` · build `npm run build` · `php artisan view:cache && php artisan view:clear` · `APP_ROUTES_CACHE=$(mktemp) php artisan route:cache` · smoke `bash docker/smoke-test.sh`

```
T1 → T2 → T3
```

---

### T1: Remover a landing (TDD)

**Refs**: LP-01, LP-02, LP-03, LP-04 (parte do build)

- [ ] RED — `tests/Feature/Landing/LandingRemovedTest.php`:
  - `LP01`: `Route::has` falso para `landing.index`, `landing.features`, `landing.pricing`, `landing.about`, `landing.terms`, `landing.policy`, `login`, `register`; GET `/features`, `/pricing`, `/about`, `/terms`, `/policy`, `/login`, `/register` → 404; GET `/` → redirect `/dashboard`
  - `LP02`: rota de teste temporária `Route::middleware(['web', 'auth'])->get('/_lp02', fn () => 'ok')` registrada no teste, com o `AuthenticateLocalUser` **desligado** para esse request (ex.: `withoutMiddleware(AuthenticateLocalUser::class)`) → redirect para `/dashboard`, sem exceção
  - `LP03`: `class_exists(App\Http\Controllers\LandingPageController::class)` false; `file_exists` false para `config_path('landingpage.php')`, `resource_path('css/landing-page.css')`, `resource_path('views/pages/landing-page')`, `resource_path('views/components/landing-page')`, `resource_path('views/components/layouts/landing-page.blade.php')`, `lang_path('en/landing-page.php')`, `lang_path('pt_BR/landing-page-legal.php')`, `resource_path('markdown')`, `resource_path('images/landing-page')`, `resource_path('images/auth')`, `public_path('preview.png')`; `vite.config.js` sem `landing-page.css`
- [ ] Rodar → FAIL
- [ ] `routes/web.php`: `Route::redirect('/', '/dashboard');` (sem nome; ajustar se algum lugar usar `route('landing.index')` — só a landing usava); remover `/login`, `/register`, `/features`, `/pricing`, `/about`, `/terms`, `/policy` e o import do controller
- [ ] `bootstrap/app.php` → `$middleware->redirectGuestsTo('/dashboard');`
- [ ] Apagar todos os arquivos/pastas do LP03; `vite.config.js` sem o input; conferir `resources/js/app.js` (`import.meta.glob(["../images/**"])` continua ok)
- [ ] Ajustar testes: `RemovedAuthSurfaceTest` NOAUTH04 (agora só `/` redireciona; `/login` e `/register` → 404, renomear o teste mantendo o ID e explicando que a F7 removeu os redirects); `NoExternalRequestsTest` remover ZT03 (A4)
- [ ] `npm run build` OK; `view:cache` OK; route:cache OK; PASS; gate full

---

### T2: Remover "Fale conosco" e criar a página Apoiar (TDD)

**Refs**: LP-05, LP-06, LP-07, LP-08, LP-09, LP-10, LP-11, LP-12

- [ ] RED — `tests/Feature/Support/SupportPageTest.php`:
  - `LP07`: GET `/support` → 200, contém "Apoiar o projeto"/"Support the project" (usar a string real com `__()`)
  - `LP08`: com `config(['craftdb.support.repo_url' => 'https://example.test/repo', 'craftdb.support.github_sponsors_url' => 'https://example.test/sponsors', 'craftdb.support.kofi_url' => 'https://example.test/kofi'])` → os 3 hrefs aparecem com `target="_blank"` e `rel="noopener noreferrer"`; com todos `null` → nenhum aparece
  - `LP09`: com `pix_key` definido → a chave aparece e há botão de copiar; com `null` → seção Pix ausente
  - `LP10`: tudo `null` → texto "em breve"/"coming soon" presente
  - `LP11`: adicionar `/support` ao dataset do ZT01 em `tests/Feature/Telemetry/NoExternalRequestsTest.php` (com links configurados, para provar que `<a href>` externos são permitidos e nada é carregado)
  - `LP12`: GET `/dashboard` e a página de um diagrama contêm link para `route('support')` (menu do usuário) e o rodapé do app linka `/support`; nenhuma das duas contém o modal/form de contato
  - `LP05`/`LP06`: `Schema::hasTable('feedback')` false; `class_exists(App\Models\Feedback::class)` false
- [ ] Rodar → FAIL
- [ ] `config/craftdb.php` → bloco `support`:

```php
    'support' => [
        'repo_url' => env('CRAFTDB_SUPPORT_REPO_URL'),
        'github_sponsors_url' => env('CRAFTDB_SUPPORT_GITHUB_SPONSORS_URL'),
        'kofi_url' => env('CRAFTDB_SUPPORT_KOFI_URL'),
        'buymeacoffee_url' => env('CRAFTDB_SUPPORT_BUYMEACOFFEE_URL'),
        'pix_key' => env('CRAFTDB_SUPPORT_PIX_KEY'),
    ],
```

- [ ] Página: `resources/views/pages/⚡support/support.php` + `support.blade.php` (padrão do dashboard) ou SFC equivalente; rota `Route::livewire('/support', 'pages::support')->middleware('auth')->name('support')` no grupo certo; layout do app; botões com ícones já disponíveis (Flux heroicons `star`, `heart`, `gift`; lucide `coffee`/`github` via `<x-lucide-*>`); Pix com `flux:input` readonly + copiar (Alpine `navigator.clipboard`)
- [ ] Menus: trocar "Contact Us" por "Support the project" (`route('support')`) em `layouts/app/master.blade.php` e `diagram/canvas/ui/topbar.blade.php`; rodapé `_partials/footer.blade.php`: o ♥ vira link para `route('support')`
- [ ] Apagar `resources/views/components/ui/⚡contact-us/` (e seus includes em `master` e `layouts/canvas/index`), `App\Models\Feedback`; migration nova `2026_10_21_000005_drop_feedback_table.php` (`dropIfExists('feedback')`; `down()` vazio com comentário)
- [ ] Strings novas em `__()` com tradução em `lang/pt_BR.json` (e `lang/en.json` se o projeto mantiver chaves lá); remover chaves do contato (grep antes)
- [ ] `.env.example`: bloco comentado `# CRAFTDB_SUPPORT_*` documentando as 5 variáveis
- [ ] PASS; gate full; `view:cache` OK

---

### T3: Limpezas, README e verificação final

**Refs**: LP-04, LP-13, LP-14, LP-15

- [ ] `resources/css/filepond.css:17`: transformar o texto solto em comentário `/* ... */`; `npm run build` sem `css-syntax-error`
- [ ] `tailwind.config.js`: confirmar que nada o carrega (sem `@config` em CSS, sem postcss config) → apagar; remover de `package.json` as devDependencies usadas só por ele (`@tailwindcss/forms`, `tailwindcss-animate`) e `daisyui` se sem uso (grep em `resources/`, `vite.config.js`); `npm install` para atualizar o lock; `npm run build` OK
- [ ] README PT/EN: remover "🚀 Deploy em Produção" (PT), reescrever §6 "Sistema de Equipes" → "Workspaces" (curto, sem papéis/convites) e §7 "Autenticação e Segurança" → "Usuário local (sem login)" (curto); atualizar o índice; adicionar seção curta "Apoie o projeto" apontando para `/support` e para as variáveis `CRAFTDB_SUPPORT_*`; **não** mexer na linha de licença (A2)
- [ ] `docker/smoke-test.sh`: check `LP-15` — `/support` 200 e sem `fonts.googleapis|goadopt|craftdb.app`; `/features` → 404
- [ ] `bash docker/smoke-test.sh` → OK; `composer test` verde; phpstan sem erros novos vs `fa847b7`
- [ ] Grep do critério de sucesso → listar hits legítimos

---

## Validação

T1 → T2 → T3. Cobertura: LP-01..04 → T1 (LP-04 também T3) · LP-05..12 → T2 · LP-13..15 → T3 · 15/15.
