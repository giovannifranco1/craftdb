# F3 — Remoção de billing, limites e links públicos — Tasks

**Design**: `.specs/features/2026-10-21-remove-billing-limits/design.md`
**Status**: Done (2026-10-21)

**Gates**: quick `./vendor/bin/pest <arquivos>` · full `composer test` · routes `php artisan route:list --no-ansi` · smoke `bash docker/smoke-test.sh` (apaga volumes)

**Regras para todas as tasks**
- Remoção = apagar arquivo + `grep -rn "<Nome>" app bootstrap config routes resources database tests lang` e ajustar toda referência (Blade inclusive: `@can`, `@if`, `wire:`, `x-data`, `route()`).
- Testes que existiam só para limite/plano/assinatura são **apagados**; testes que criavam assinatura só para liberar um recurso são **ajustados** para rodar sem assinatura (mantendo a intenção).
- Testes novos levam o ID no nome (`FREE01...`). Gate full verde ao fim de cada task.
- Não tocar landing (`resources/views/pages/landing-page`, `config/landingpage.php`, `lang/*/landing-page.php`) — F7.

Execução sequencial (arquivos compartilhados: `User.php`, layouts, `topbar`, `CanvasComponent`). Ordem: cada task remove os **consumidores** do que a seguinte apaga — MCP → gates premium → links públicos → limites → Cashier:

```
T1 → T2 → T3 → T4 → T5 → T6 → T7
```

---

### T1: MCP sem planos (TDD)

**What**: MCP funciona sem plano nem limite; checagem token↔time com `AuthorizationException`. (A classe `PlanFeatureException` só é apagada na T2.)
**Where**: `app/Mcp/McpAccess.php`, `app/Mcp/Tools/{ListDiagramsTool,ListTeamsTool}.php`, `app/Mcp/Servers/CraftdbServer.php`, `app/Mcp/Server/CraftdbCallTool.php`, `resources/views/mcp/authorize.blade.php`, `app/Exceptions/PlanFeatureException.php`, `app/Livewire/CanvasComponent.php` (catch), `tests/Feature/Mcp/McpToolsAuthorizationTest.php`
**Depends on**: None
**Refs**: FREE-19, FREE-20, FREE-21, FREE-22, FREE-23

**Steps**:
- [ ] RED — em `McpToolsAuthorizationTest`: substituir "free user gets no diagrams" e "pro subscription" por:
  - `FREE20`: usuário sem assinatura com 2 diagramas → `list_diagrams` retorna os 2 (mesmo setup de token/times do arquivo)
  - `FREE19`: tool de leitura (`get_diagram`) num diagrama do próprio time sem assinatura → sucesso
  - `FREE22`: token restrito a outro time → resposta de erro de autorização (o teste existente de token↔time deve continuar passando com a nova exceção)
  - `FREE21`: resposta de `list_teams` e as instruções do `CraftdbServer` não contêm `Free`, `Pro`, `Team plan`, `upgrade`, `/settings/billing`
- [ ] Rodar → FAIL
- [ ] `McpAccess`: remover `assertWorkspacePremium` (usa `hasTeamWorkspacePremiumAccess`) e a checagem de limite (`hasReachedDiagramLimit` + `DiagramLimitExceededException`); renomear `premiumTeamIdsForUser` → `teamIdsForUser` (times do usuário ∩ `mcp_token_teams`) e atualizar callers; `assertTokenAllowsTeam` lança `Illuminate\Auth\Access\AuthorizationException` com mensagem "This token is not allowed to access this team."
- [ ] `ListTeamsTool` (remove uso de `hasTeamWorkspacePremiumAccess`, `isTeamPlan`/`isPro` e `DiagramLimits::forUser`) e `CraftdbServer`: sem plano, contagem vs limite, aviso e URL de upgrade (lista nome, id e contagem de diagramas)
- [ ] `CraftdbCallTool`: remover `PlanFeatureException|DiagramLimitExceededException` do catch (o catch de `AuthorizationException` existente cobre FREE-22 — confirmar)
- [ ] `mcp/authorize.blade.php`: remover badge de plano e comentário
- [ ] Grep: `grep -rn "PlanFeatureException\|premiumTeamIds\|assertWorkspacePremium\|hasTeamWorkspacePremiumAccess\|isTeamPlan\|isPro()\|DiagramLimit\|hasReachedDiagramLimit" app/Mcp resources/views/mcp tests/Feature/Mcp` → vazio
- [ ] Rodar → PASS; gate full

**Tests**: integration · **Gate**: full

---

### T2: Recursos premium liberados (TDD)

**What**: Remover todos os gates premium e o modal de upgrade; liberar criação de time.
**Where**: `app/Actions/Diagram/{CreateNewDiagram,ImportToExistingDiagram,ExportDiagramToJson,ExportDiagramToLaravelMigration}.php`, `app/Http/Controllers/Pages/DiagramController.php`, `app/Policies/TeamPolicy.php`, `app/Models/User.php`, Livewire/Blade: `diagram/canvas/overlays/⚡export/*`, `diagram/ui/⚡graph/*`, `diagram/canvas/lists/⚡area-list/*`, `⚡note-list/*`, `diagram/canvas/ui/controls.blade.php`, `diagram/canvas/ui/topbar.blade.php`, `diagram/ui/card/index.blade.php`, `_partials/create/create-step-2.blade.php`, `components/ui/pro-feature-modal.blade.php`, `layouts/app.blade.php`, `layouts/app/master.blade.php`, `layouts/canvas/index.blade.php`, `resources/js/app.js`, `config/pro-features.php`
**Depends on**: T1
**Refs**: FREE-05, FREE-06, FREE-07, FREE-08, FREE-09

**Steps**:
- [ ] RED — criar `tests/Feature/Plans/PremiumFeaturesUnlockedTest.php` com, para um usuário **sem assinatura** (`$this->autenticated()`):
  - `FREE06`: GET `route('diagram.export-json', ...)` e `route('diagram.export-laravel-migration', ...)` de um diagrama do próprio time → 200 (usar a forma de criar diagrama dos testes existentes de export)
  - `FREE07`: criar área e nota pelos componentes `⚡area-list`/`⚡note-list` (mesma API usada em `area-list.test.php`/`note-list.test.php`, sem `subscriptions()->create`) → registro criado
  - `FREE07`: componente `⚡graph` renderiza sem `PlanFeatureException`
  - `FREE05`: `ImportToExistingDiagram` com um SQL de 2 tabelas → 2 tabelas criadas (usar fixture/SQL dos testes de parse existentes)
  - `FREE08`: `Gate::forUser($user)->allows('create', Team::class)` → true
- [ ] Rodar → FAIL
- [ ] Remover os gates listados em **Where** (actions sem `hasDiagramPremiumAccess`/delete+throw; exports sem gate e sem bypass `environment('testing')`; controller sem `abort(403)` de plano, mantendo `Gate::authorize('view')`; Blade mostra sempre importar/exportar/grafo/áreas/notas)
- [ ] `TeamPolicy::create(User $user): bool { return true; }`; remover `canCreateTeam` do `User`
- [ ] `User`: apagar `hasDiagramPremiumAccess` e `hasTeamWorkspacePremiumAccess` (inclui o bloco "invited members inherit"; remover o item correspondente de `.specs/project/STATE.md`)
- [ ] Apagar `app/Exceptions/PlanFeatureException.php` e todos os `throw`/`catch`/imports (`CanvasComponent`, `area-list.php`, `note-list.php`, `CraftdbCallTool`) — o MCP já não a usa (T1)
- [ ] Apagar `pro-feature-modal`, seus includes, listeners `pro-feature::show`, a store Alpine em `resources/js/app.js` e `config/pro-features.php`
- [ ] Ajustar testes que criavam assinatura só para liberar recurso: `area-list.test.php`, `note-list.test.php` (tirar `subscriptions()->create` do `beforeEach`), `ExportDiagramToJsonTest`/`ExportDiagramToLaravelMigrationTest` (remover mocks de `isPro` e testes "não-pro recebe 403"; manter "não-dono recebe 403")
- [ ] `npm run build` (o `app.js` mudou) → sucesso
- [ ] Grep: `grep -rn "hasDiagramPremiumAccess\|hasTeamWorkspacePremiumAccess\|canCreateTeam\|PlanFeatureException\|pro-feature\|pro_feature" app resources config tests` → vazio
- [ ] Rodar → PASS; gate full

**Tests**: integration · **Gate**: full

---

### T3: Remover links públicos (TDD)

**What**: Sem compartilhamento público; "Compartilhar" só com link interno.
**Where**: `routes/web.php`, `app/Http/Controllers/Pages/DiagramController.php` (`showPublic`), `resources/views/pages/diagram/public.blade.php`, `app/Policies/DiagramPolicy.php` (`share`), `app/Models/Diagram.php` (`isPublic`, `makePublic`, `makePrivate`, `getPublicUrl`), `app/Livewire/Contracts/PublicCanvas.php`, `app/View/Components/Layouts/CanvasPublic.php` (+ registro em `AppServiceProvider`), `resources/views/components/layouts/canvas/public.blade.php`, `resources/views/components/diagram/public/` (inteiro), `resources/css/canvas-public.css` (+ `vite.config.js`), `resources/js/canvas/data/_svg-lines.js:65`, `resources/views/components/diagram/canvas/⚡share.blade.php`, `diagram/canvas/ui/topbar.blade.php`, `docs/PUBLIC_SHARING.md`, `tests/Feature/Policies/DiagramOwnershipTest.php`
**Depends on**: T2
**Refs**: FREE-14, FREE-15, FREE-16

**Steps**:
- [ ] RED — criar `tests/Feature/Plans/PublicLinksRemovedTest.php`:
  - `FREE14`: `Route::has('diagram.public')` → false
  - `FREE15`: GET da página do diagrama (`route('diagram.show', ...)`) → 200; contém o texto real de "copiar link interno" do topbar; não contém `makePublic`, `revokePublic` nem os textos de tornar público/revogar
  - `FREE16`: `method_exists(Diagram::class, 'makePublic')` e `'getPublicUrl'` → false; `class_exists` falso para `App\View\Components\Layouts\CanvasPublic` e `App\Livewire\Contracts\PublicCanvas`
- [ ] Rodar → FAIL
- [ ] Apagar/remover tudo de **Where**; no `topbar`, remover o embed do `⚡share` e manter "copiar link interno" + slug; apagar `⚡share.blade.php` inteiro (estado Alpine `publicUrl` com `getPublicUrl()` quebraria) e o listener órfão `share::public-url-generated`
- [ ] `_svg-lines.js`: remover só a condição do componente público, mantendo o caminho do canvas privado; `npm run build` → sucesso
- [ ] `DiagramOwnershipTest`: remover asserções de `share`
- [ ] O canvas público usa `DiagramLimits`/`tableColumnsLimit` (`components/diagram/public/canvas/elements/⚡table/table.php`, `_partials/table/footer.blade.php`) — some aqui junto com o diretório, antes da T4 apagar `DiagramLimits`
- [ ] Strings de link público em `lang/*.json` (grep cada chave antes)
- [ ] Grep: `grep -rniE "public_token|makePublic|makePrivate|getPublicUrl|isPublic\(|diagram\.public|PublicCanvas|CanvasPublic|canvas-public|public-view" app bootstrap config routes resources tests vite.config.js` → só `PublicLinksRemovedTest`
- [ ] Rodar → PASS; gate full

**Tests**: integration · **Gate**: full

---

### T4: Sem limites (TDD)

**What**: Remover todos os limites de diagramas, tabelas e colunas.
**Where**: `app/Support/DiagramLimits.php`, `app/Exceptions/{DiagramLimitExceeded,TableLimitExceeded,TableColumnLimitExceeded}Exception.php`, `config/diagram.php`, `app/Models/User.php`, `app/Models/Diagram.php` (`TABLE_LIMIT`), `app/Livewire/{BaseComponent,CanvasComponent}.php`, `app/Actions/Diagram/{AddColumnToTable,CreateNewDiagram,ImportToExistingDiagram}.php`, `app/Actions/Parse/{ParseLaravelMigration,ParseMySQLSchema}.php`, Livewire: `diagram/⚡create/create.php`, `canvas/lists/⚡table-list/table-list.php`, `canvas/overlays/⚡import/import.php`, `canvas/elements/⚡table/table.php`, `canvas/ui/⚡limit/`, `layouts/canvas/index.blade.php`, `canvas/_partials/table/footer.blade.php` (o footer privado; o público saiu na T3). O MCP já não usa limites (T1)
**Depends on**: T3
**Refs**: FREE-01, FREE-02, FREE-03, FREE-04

**Steps**:
- [ ] RED — criar `tests/Feature/Plans/NoLimitsTest.php` (usuário sem assinatura):
  - `FREE01`: criar 5 diagramas via `CreateNewDiagram` (mesmos dados dos testes de `⚡create`) → 5 criados
  - `FREE02`: `ParseMySQLSchema`/`CreateNewDiagram` com SQL de 120 `CREATE TABLE` gerado em loop → 120 tabelas
  - `FREE02`: `AddColumnToTable` 120 vezes na mesma tabela → 120 colunas
  - `FREE04`: `class_exists(App\Support\DiagramLimits::class)` e as 3 exceções → false
- [ ] Rodar → FAIL
- [ ] Remover limites em cada arquivo de **Where**; footers mostram só a contagem (sem "/ limite"); `BaseComponent`/`CanvasComponent` sem `catch` de limite e sem `validateTableLimit()` (o catch de `PlanFeatureException` já saiu na T2) (remover chamadas em `table-list.php` e `import.php`); `⚡table` sem `tableColumnsLimit`
- [ ] `config/diagram.php`: remover chaves de limite; se ficar vazio, apagar o arquivo e todo `config('diagram...')`
- [ ] Apagar `⚡limit` e seu mount; `limit.test.php`; teste "prevents adding tables beyond limit" de `table-list.test.php`
- [ ] Grep: `grep -rn "DiagramLimits\|LimitExceeded\|table_limit\|table_columns_limit\|diagram_limit\|TABLE_LIMIT\|validateTableLimit\|validateDiagramLimit\|hasReachedDiagramLimit" app resources config tests` → vazio (exceto o próprio `NoLimitsTest` checando `class_exists`)
- [ ] Rodar → PASS; gate full

**Tests**: integration · **Gate**: full

---

### T5: Remover Cashier, billing e preço regional (TDD)

**What**: Tirar Stripe/Cashier e tudo de billing/região.
**Where**: `composer.json`/`.lock`, `config/{cashier,billing}.php`, `config/app.php` (`cloudflare_proxy`), `app/Models/User.php`, `app/Http/Controllers/BillingController.php`, `app/Enums/BillingCountry.php`, `app/Http/Middleware/{SetHeaders,EnsureValidCurrentTeam}.php`, `bootstrap/app.php`, `app/Support/Helpers.php`, `routes/web.php`, `resources/views/components/settings/⚡billing.blade.php`, `settings/layout.blade.php`, `layouts/app/{master,header}.blade.php`, `diagram/canvas/ui/topbar.blade.php`, `database/factories/{BillingSubscriptionFactory,UserFactory}.php`, `tests/TestCase.php`, `.env.example`
**Depends on**: T4
**Refs**: FREE-03 (badge), FREE-10, FREE-11, FREE-12, FREE-13

**Steps**:
- [ ] RED — criar `tests/Feature/Plans/BillingRemovedTest.php`:
  - `FREE10`: `Route::has` falso para `billing.index`, `billing.checkout`, `billing.portal`, `billing.success`, `cashier.webhook`, `cashier.payment`; POST `/stripe/webhook` → 404 (ou 405)
  - `FREE11`: `class_exists(Laravel\Cashier\Cashier::class)` → false
  - `FREE12`/`FREE03`: GET `/dashboard` e `/settings/profile` não contêm `settings/billing` nem os textos do badge de plano (usar as strings reais de `master.blade.php`)
  - `FREE13`: `class_exists` falso para `App\Enums\BillingCountry`, `App\Http\Middleware\SetHeaders`, `App\Http\Controllers\BillingController`
- [ ] Rodar → FAIL
- [ ] `composer remove laravel/cashier` (reverter `_ide_helper.php` se for regenerado: `git checkout -- _ide_helper.php`)
- [ ] Apagar arquivos de **Where** que saem inteiros; `User` sem `Billable`, `isPro`, `isTeamPlan`, caches e `clearSubscriptionCaches`; `TestCase::setUp` sem a chamada; `UserFactory` sem `withActiveProSubscription`
- [ ] Rotas `billing.*` e `/debug-cf` fora de `routes/web.php`; `SetHeaders` fora de `bootstrap/app.php`; `EnsureValidCurrentTeam` sem isenção `billing.*`; `config/app.php` sem `cloudflare_proxy`; `Helpers.php`: remover **só** `formatPriceAmount` e o `use App\Enums\BillingCountry;` (o arquivo tem outros helpers em uso)
- [ ] Remover links "Billing" (settings layout, master, header ×2, topbar) e badge de plano do `master.blade.php`
- [ ] `.env.example`: remover `STRIPE_*`, `BILLING_*`, `CLOUDFLARE_PROXY`
- [ ] Grep: `grep -rniE "cashier|stripe|subscri|\bisPro\b|isTeamPlan|BillingCountry|cf-ipcountry|cloudflare_proxy|billing\." app bootstrap config routes resources database/factories tests | grep -vE "landing-page|landingpage"` → só `BillingRemovedTest`
- [ ] Rodar → PASS; gate full

**Tests**: integration · **Gate**: full

---

### T6: Migration de limpeza (TDD)

**What**: Apagar tabelas/colunas de billing e `public_token`.
**Where**: `database/migrations/2026_10_21_000002_remove_billing_leftovers.php` (novo), `tests/Feature/Database/RemoveBillingLeftoversTest.php` (novo)
**Depends on**: T5
**Refs**: FREE-17

**Steps**:
- [ ] RED — `tests/Feature/Database/RemoveBillingLeftoversTest.php`:

```php
<?php

namespace Tests\Feature\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('FREE17: drops billing tables and columns and diagram.public_token', function () {
    expect(Schema::hasTable('subscriptions'))->toBeFalse()
        ->and(Schema::hasTable('subscription_items'))->toBeFalse()
        ->and(Schema::hasColumn('diagram', 'public_token'))->toBeFalse();

    foreach (['stripe_id', 'pm_type', 'pm_last_four', 'trial_ends_at'] as $column) {
        expect(Schema::hasColumn('users', $column))->toBeFalse();
    }
});
```

- [ ] Rodar → FAIL
- [ ] GREEN — `database/migrations/2026_10_21_000002_remove_billing_leftovers.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CraftDB Local (F3): remove what Stripe/Cashier billing and public share links left behind.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('subscription_items');
        Schema::dropIfExists('subscriptions');

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['stripe_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['stripe_id', 'pm_type', 'pm_last_four', 'trial_ends_at']);
        });

        Schema::table('diagram', function (Blueprint $table) {
            $table->dropUnique(['public_token']);
        });

        Schema::table('diagram', function (Blueprint $table) {
            $table->dropColumn('public_token');
        });
    }

    /**
     * Approximate inverse (Cashier subscription tables are not restored) — enough for rollback in dev.
     */
    public function down(): void
    {
        Schema::table('diagram', function (Blueprint $table) {
            $table->uuid('public_token')->nullable()->unique();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('stripe_id')->nullable()->index();
            $table->string('pm_type')->nullable();
            $table->string('pm_last_four', 4)->nullable();
            $table->timestamp('trial_ends_at')->nullable();
        });
    }
};
```

- [ ] Se `dropUnique(['public_token'])` falhar por nome de índice, conferir com `Schema::getIndexes('diagram')` e usar o nome real
- [ ] Rollback com SQLite **temporário**: `T=$(mktemp); DB_CONNECTION=sqlite DB_DATABASE=$T php artisan migrate:fresh --force && DB_CONNECTION=sqlite DB_DATABASE=$T php artisan migrate:rollback --step=1 --force && DB_CONNECTION=sqlite DB_DATABASE=$T php artisan migrate --force; rm -f $T`
- [ ] Rodar → PASS; gate full

**Tests**: integration · **Gate**: full

---

### T7: Docker sobre volume existente, docs e verificação final

**What**: Provar FREE-18 no MySQL, limpar README e verificar tudo.
**Where**: `docs/README.md`, `docs/README.en.md`, `lang/*.json` (strings restantes), `docker/smoke-test.sh` (se precisar)
**Depends on**: T6
**Refs**: FREE-18, FREE-24, FREE-25, critérios de sucesso

**Steps**:
- [ ] Stack de pé com o código da F2 (volume com dados): criar diagrama marcador `f2-keep` com 2 tabelas e 1 coluna via tinker (mesmo padrão da T8 da F2, `Team::whereHas('owner', ...)`); preencher `public_token` dele (`Str::uuid()`)
- [ ] `docker compose up -d --build` (sem `-v`) → migration da F3 roda; `migrate:status` mostra `2026_10_21_000002_remove_billing_leftovers` Ran; `f2-keep` e suas tabelas/colunas existem; `/dashboard` 200 sem cookie (FREE-18)
- [ ] README PT/EN: remover subseção "Stripe e Webhook", variáveis `STRIPE_*`/`BILLING_*` e itens de Stripe do checklist da seção "Deploy em Produção" (o resto da seção fica — F5/F7)
- [ ] `lang/*.json`: remover chaves de plano/limite/billing/link público/upgrade sem uso (grep cada chave em `app` e `resources` antes; nunca as usadas pela landing)
- [ ] Grep de sucesso da spec → vazio
- [ ] `composer test` → verde; `bash docker/smoke-test.sh` → `Smoke test OK` (FREE-25)
- [ ] `./vendor/bin/phpstan analyse --memory-limit=1G` → sem erros novos vs. o commit da F2 (`977c35e`): comparar por arquivo+mensagem

**Tests**: — · **Gate**: full + smoke

---

## Validação pré-aprovação

### Granularity

| Task | Escopo | Status |
|---|---|---|
| T1 | MCP | ✅ coeso |
| T2 | gates premium | ⚠️ muitos arquivos, mas um conceito (remoção atômica) |
| T3 | links públicos | ✅ coeso |
| T4 | limites | ⚠️ idem |
| T5 | Cashier/billing/região | ⚠️ remoção atômica (pacote) |
| T6 | 1 migration + teste | ✅ |
| T7 | verificação | ✅ |

### Cross-check

| Task | Depends on | Diagrama | Status |
|---|---|---|---|
| T1 | None | — | ✅ |
| T2 | T1 | T1→T2 | ✅ |
| T3 | T2 | T2→T3 | ✅ |
| T4 | T3 | T3→T4 | ✅ |
| T5 | T4 | T4→T5 | ✅ |
| T6 | T5 | T5→T6 | ✅ |
| T7 | T6 | T6→T7 | ✅ |

### Test co-location

| Task | Camada | Exige | Task diz | Status |
|---|---|---|---|---|
| T1–T5 | Actions/Livewire/rotas/MCP | integration | integration | ✅ |
| T6 | Migration | integration | integration | ✅ |
| T7 | Infra/docs | smoke | smoke + full | ✅ |

### Cobertura

| ID | Task | ID | Task | ID | Task |
|---|---|---|---|---|---|
| FREE-01 | T4 | FREE-10 | T5 | FREE-19 | T1 |
| FREE-02 | T4 | FREE-11 | T5 | FREE-20 | T1 |
| FREE-03 | T4, T5 | FREE-12 | T5 | FREE-21 | T1 |
| FREE-04 | T4 | FREE-13 | T5 | FREE-22 | T1 |
| FREE-05 | T2 | FREE-14 | T3 | FREE-23 | T1 |
| FREE-06 | T2 | FREE-15 | T3 | FREE-24 | T1–T5, T7 |
| FREE-07 | T2 | FREE-16 | T3 | FREE-25 | T7 |
| FREE-08 | T2 | FREE-17 | T6 | | |
| FREE-09 | T2 | FREE-18 | T7 | | |

25/25 mapeados.
