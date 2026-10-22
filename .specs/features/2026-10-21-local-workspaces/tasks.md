# F4 — Workspaces locais — Tasks

**Design**: `design.md` · **Status**: Done (2026-10-21)

**Gates**: quick `./vendor/bin/pest <arquivos>` · full `composer test` · `php artisan view:cache && php artisan view:clear` · smoke `bash docker/smoke-test.sh`

**Regras**: remoção = apagar + grep e ajustar referências (Blade inclusive); testes novos com ID (`WS01…`) no nome; gate full verde ao fim de cada task; não tocar landing, MCP tool names/params, `mcp_token_teams`.

```
T1 → T2 → T3 → T4 → T5 → T6
```

---

### T1: Remover membros (pivot) e acesso por posse (TDD)

**Where**: `app/Models/Concerns/{HasTeams,HasMembers}.php`, `app/Models/Team/{Team,Membership}.php`, `app/Models/User.php`, `app/Models/Scopes/TableAccessible.php`, `app/Models/Column.php`, `app/Policies/{DiagramPolicy,TeamPolicy}.php`, `resources/views/components/ui/⚡team-switcher/team-switcher.php` (troca mínima `teams()` → `ownedTeams()`; o retrabalho é na T3), `app/Http/Middleware/EnsureValidCurrentTeam.php` (troca mínima), MCP não muda (só usa `allTeams()`), `database/migrations/2026_10_21_000003_drop_team_user_table.php`, `tests/Feature/Database/RemoveAuthLeftoversTest.php`
**Depends on**: None · **Refs**: WS-18, WS-19, WS-20

- [ ] RED — `tests/Feature/Workspaces/OwnershipOnlyTest.php`:
  - `WS18`: `Schema::hasTable('team_user')` → false
  - `WS19`: `method_exists(User::class, 'belongsToTeam')`, `'teams'` → false; `class_exists(App\Models\Team\Membership::class)` → false
  - `WS19`: dono vê/edita diagrama do próprio workspace (`Gate::forUser($owner)->allows('view', $diagram)` true); outro usuário → false
  - `WS20`: `Column::accessible()` (mesma assinatura atual) retorna as colunas de uma tabela de um workspace **próprio** do usuário autenticado (hoje retorna vazio)
- [ ] Migration `2026_10_21_000003_drop_team_user_table.php`: `up()` `Schema::dropIfExists('team_user')`; `down()` recria `team_user` (`id`, `team_id` FK `team`, `user_id` FK `users`, timestamps, unique `team_id,user_id`)
- [ ] `HasTeams`: apagar `teams()`, `belongsToTeam()`; `allTeams()` → `$this->ownedTeams()->orderBy('name')->get()`; `HasMembers`: apagar `users()`, `hasUser()`; apagar `Membership.php` e docblocks/imports que o citam
- [ ] Trocar todo `belongsToTeam(` por `ownsTeam(` (grep em app, resources, tests); `TableAccessible` e `Column::accessible` passam a usar `ownedTeams()`
- [ ] `RemoveAuthLeftoversTest`: trocar a asserção `team_user` existe por "não existe" (agora apagada na F4) mantendo o resto
- [ ] Grep `grep -rn "team_user\|belongsToTeam\|->teams()\|Membership\|hasUser(" app resources database/factories database/seeders tests` → só migrations antigas e a nova
- [ ] PASS; gate full

**Tests**: integration · **Gate**: full

---

### T2: Actions `RenameTeam` e `DeleteTeam` (TDD)

**Where**: `app/Actions/Team/{RenameTeam,DeleteTeam}.php`, `tests/Feature/Actions/Team/{RenameTeamTest,DeleteTeamTest}.php`
**Depends on**: T1 · **Refs**: WS-03, WS-04, WS-05

- [ ] RED — `RenameTeamTest`: `WS03` renomeia; nome vazio e >255 → `ValidationException`
- [ ] RED — `DeleteTeamTest`:
  - `WS04`: usuário com 2 workspaces; o apagado tem 2 diagramas com tabelas, colunas, relacionamento, área e nota (usar factories existentes; se faltar factory, criar via models com os campos obrigatórios das migrations) → workspace, diagramas, tabelas, colunas, relacionamentos, áreas e notas desse workspace somem; o outro workspace fica intacto
  - `WS05`: único workspace → `ValidationException` com chave `team`, nada apagado
- [ ] GREEN:

```php
<?php

namespace App\Actions\Team;

use App\Models\Team\Team;
use Illuminate\Support\Facades\Validator;

class RenameTeam
{
    public function exec(Team $team, string $name): Team
    {
        Validator::make(['name' => $name], [
            'name' => ['required', 'string', 'max:255'],
        ])->validate();

        $team->update(['name' => $name]);

        return $team;
    }
}
```

```php
<?php

namespace App\Actions\Team;

use App\Actions\Diagram\DeleteDiagram;
use App\Models\Team\Team;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteTeam
{
    public function __construct(private DeleteDiagram $deleteDiagram) {}

    /**
     * Delete a workspace and all of its diagrams. The last workspace can't be deleted.
     */
    public function exec(Team $team): void
    {
        if ($team->owner->ownedTeams()->count() <= 1) {
            throw ValidationException::withMessages([
                'team' => __('You cannot delete your only workspace.'),
            ]);
        }

        DB::transaction(function () use ($team): void {
            $team->diagrams()->each(fn ($diagram) => $this->deleteDiagram->exec($diagram));
            $team->delete();
        });
    }
}
```

- [ ] PASS; gate full

**Tests**: integration · **Gate**: full

---

### T3: Tela `settings/workspaces`, seletor e middleware (TDD)

**Where**: `resources/views/components/settings/⚡workspaces.blade.php` (novo), `resources/views/components/settings/{⚡team.blade.php,team/}` (apagar), `resources/views/components/settings/layout.blade.php`, `resources/views/components/ui/⚡team-switcher/*`, `resources/views/components/layouts/app/master.blade.php`, `diagram/canvas/ui/topbar.blade.php`, `routes/web.php`, `app/Policies/TeamPolicy.php`, `app/Http/Middleware/EnsureValidCurrentTeam.php`, `app/Listeners/SetCurrentTeamOnLogin.php`, `app/Livewire/MyFavorites.php`, testes de team existentes
**Depends on**: T2 · **Refs**: WS-01, WS-02, WS-03, WS-04, WS-05, WS-06, WS-07, WS-08, WS-09, WS-11, WS-12, WS-13

- [ ] RED — `tests/Feature/Workspaces/WorkspacesPageTest.php` (Livewire `settings.⚡workspaces` com `$this->autenticated()`):
  - `WS01` lista os workspaces com contagem de diagramas e marca o atual
  - `WS02` `create` com nome → existe, é o atual (`session('current_team')->id`)
  - `WS03` renomeia um workspace que não é o atual; renomear o atual atualiza `session('current_team')` (o seletor mostra o novo nome)
  - `WS04`/`WS08` apagar com confirmação errada → não apaga; com o nome exato → apaga com diagramas
  - `WS05` apagar o único → erro na tela, nada apagado
  - `WS06` apagar o atual (havendo outro) → sessão aponta para o outro
  - `WS07` `makeCurrent` → sessão troca e redireciona para `dashboard`
  - `WS09` GET `/dashboard` com 1 workspace mostra o nome do workspace atual e link para `workspaces.index`
  - `WS11` sessão com workspace apagado → GET `/dashboard` troca para o primeiro por id e mostra o aviso
  - `WS12` GET `/settings/profile` contém "Workspaces" no nav e não contém "Team Settings"
  - `WS13` `Route::has` falso para `team.create`, `team.edit`, `dashboard.my-favorites`; verdadeiro para `workspaces.index`
- [ ] `TeamPolicy`: `view`, `update` (renomeia `updateTeam`), `delete` = `ownsTeam`
- [ ] Criar o componente (design §`⚡workspaces`); modal de apagar com Flux (`flux:modal`) e input de confirmação, botão desabilitado até o texto ser igual ao nome; ao renomear o atual, regravar `session('current_team')` com o model atualizado; erros via `ValidationException` das Actions
- [ ] Rotas: `settings/workspaces` (`workspaces.index`); remover `team.create`, `team.edit`, `/my-favorites`; apagar `MyFavorites.php`, `settings/⚡team.blade.php`, `settings/team/`
- [ ] Nav de settings: grupo "Workspaces" → `workspaces.index`; menus (`master`, `topbar`): "Criar time" sai, switcher fica
- [ ] Switcher: sempre visível, mostra nome atual, lista os demais (só `ownedTeams`), item "Gerenciar workspaces"; remover cache de contagem em sessão; corrigir `Team::currentTeam()` chamado por item do loop (calcular uma vez)
- [ ] `EnsureValidCurrentTeam` e `SetCurrentTeamOnLogin`: fallback `ownedTeams()->orderBy('id')->first()`; mensagem `__('The current workspace no longer exists. Switched to :name.', ['name' => ...])`
- [ ] Migrar/apagar testes antigos: `tests/Feature/Settings/TeamCreateTest.php` → coberto por `WorkspacesPageTest` (apagar); `resources/views/components/ui/⚡team-switcher/team-switcher.test.php` (colocado; ajustar textos/sessão); `tests/Feature/Settings/SettingsNavigationTest.php` (nav); `tests/Feature/Plans/PremiumFeaturesUnlockedTest.php` teste `FREE08` (usa `route('team.create')` e `settings.team.⚡create`) → reescrever para criar workspace via `settings.⚡workspaces`, mantendo o nome FREE08
- [ ] Grep `grep -rn "team.edit\|team.create\|team-created\|settings.team" tests resources app routes` → vazio
- [ ] `view:cache` OK; PASS; gate full

**Tests**: integration · **Gate**: full

---

### T4: Diagramas em qualquer workspace + dashboard (TDD)

**Where**: `resources/views/components/diagram/canvas/elements/⚡note/note.php`, `app/Actions/Diagram/AddRelationship.php`, `app/Policies/DiagramPolicy.php` (`create`), `resources/views/components/layouts/app/master.blade.php` (breadcrumb), `resources/views/components/diagram/⚡create/create.php`, `resources/views/pages/⚡dashboard/dashboard.php`
**Depends on**: T3 · **Refs**: WS-14, WS-15, WS-16, WS-17

- [ ] RED — `tests/Feature/Workspaces/CrossWorkspaceTest.php`:
  - `WS14` usuário com workspaces A (atual) e B; diagrama em B: atualizar nota (componente `⚡note`) e `AddRelationship` entre duas tabelas de B → sucesso
  - `WS15` página do diagrama de B: breadcrumb/link aponta para `/{B}/diagram/...` e mostra o nome de B
  - `WS16` dashboard em A (contador X) → trocar para B → contador de B (sem cache vazado)
  - `WS17` `toggleFavorite` do dashboard em diagrama de outro usuário → negado; no próprio → alterna
- [ ] Contador do menu (`resources/views/components/ui/menu.blade.php`, `session('diagrams_count')`) deixa de depender de cache em sessão alimentado pelo switcher antigo: calcular a contagem do workspace atual (ou incluir o id na chave) para refletir a troca
- [ ] `Team::currentTeam()` fallback com o mesmo critério do middleware/listener (`ownedTeams()->orderBy('id')->first()`)
- [ ] Ajustes do design §"Diagramas em qualquer workspace" (posse em vez de "time atual"; chave de cache com id do workspace; autorização no `toggleFavorite`)
- [ ] PASS; gate full

**Tests**: integration · **Gate**: full

---

### T5: Textos "Workspace" e limpeza de traduções

**Where**: views do app (exceto landing), `lang/pt_BR.json`, `lang/en.json`, `app/**` com strings de UI
**Depends on**: T4 · **Refs**: WS-10, WS-22

- [ ] Trocar strings visíveis "Team/Teams/Time/Equipe/Members/Membros/Convidar" por Workspace(s) — em Blade **e** em PHP que gera texto de UI (alerts/flash do Livewire, middleware, policies, exceções; ex. `Team.php`, `EnsureValidCurrentTeam`, switcher) (ex.: dashboard vazio "Você não tem workspaces. Crie um para começar.", "Switch Team" → "Switch workspace", "Team Owner" some com a tela antiga)
- [ ] `lang/pt_BR.json`: chaves novas da F4 (todas as strings novas de T2–T4 com tradução pt_BR) e remover as mortas (membros/convites/papéis/times — grep cada chave)
- [ ] Teste `tests/Feature/Workspaces/WorkspaceWordingTest.php` `WS10`: GET `/dashboard`, `/settings/workspaces`, `/settings/profile` e a página de um diagrama → o HTML visível (sem atributos) não casa `/\b(Team|Teams|Equipe|Membros|Members)\b/` nem `/\bTime\b/` (usar `strip_tags` sobre o corpo)
- [ ] Grep do critério de sucesso da spec; listar no relatório os hits internos aceitos (variáveis, rotas, params)
- [ ] PASS; gate full

**Tests**: integration · **Gate**: full

---

### T6: Docker sobre volume existente e verificação

**Depends on**: T5 · **Refs**: WS-21, WS-23

- [ ] Stack de pé com o código da F3: criar 2º workspace "ws-keep" com um diagrama marcador `f3-keep` (tinker); rebuild `docker compose up -d --build` sem `-v`; `migrate:status` mostra `2026_10_21_000003_drop_team_user_table` Ran; os dois workspaces e o diagrama existem; `/dashboard` 200 sem cookie (WS-21)
- [ ] `composer test` verde; `bash docker/smoke-test.sh` → OK; phpstan sem erros novos vs `289d37a`
- [ ] Se o Docker estiver indisponível: registrar BLOCKED para WS-21 e seguir com WS-23 só via `composer test` (não fingir a evidência)

**Tests**: — · **Gate**: full + smoke

---

## Validação

| Task | Depends | Status |
|---|---|---|
| T1 | — | ✅ |
| T2 | T1 | ✅ |
| T3 | T2 | ✅ |
| T4 | T3 | ✅ |
| T5 | T4 | ✅ |
| T6 | T5 | ✅ |

Testes co-localizados: T1–T5 integration; T6 smoke. ✅

### Cobertura
WS-01…13 → T3 (03/04/05 também T2) · WS-14…17 → T4 · WS-18…20 → T1 · WS-21 → T6 · WS-22 → T3, T5 · WS-23 → T6 · 23/23
