# F2 — Usuário local implícito + remoção de auth — Tasks

**Design**: `.specs/features/2026-10-21-local-user-no-auth/design.md`
**Status**: Done (2026-10-21)

**Comandos de gate**
- **quick**: `./vendor/bin/pest <arquivo>` (SQLite in-memory)
- **full**: `composer test`
- **routes**: `php artisan route:list --no-ansi`
- **smoke**: `bash docker/smoke-test.sh` (apaga os volumes do stack)

**Regra para todas as tasks de remoção:** depois de apagar um arquivo, rodar `grep -rn "<NomeDaClasse>" app bootstrap config routes resources database tests lang` e remover/ajustar toda referência restante. Gate full ao fim de cada task (suite verde antes de seguir).

---

## Execution Plan

Sequencial: as tasks tocam arquivos compartilhados (`bootstrap/app.php`, `routes/web.php`, `User.php`, layouts), então não há `[P]`.

```
T1 → T2 → T3 → T4 → T5 → T6 → T7 → T8 → T9
```

---

## Task Breakdown

### T1: `EnsureLocalUser` e `craftdb:local-user` sem senha (TDD)

**What**: Remover o parâmetro/uso de senha do usuário local (sem migration: a coluna já é nullable).
**Where**: `app/Actions/Local/EnsureLocalUser.php`, `app/Console/Commands/EnsureLocalUserCommand.php`, `config/craftdb.php`, `tests/Feature/Actions/Local/EnsureLocalUserTest.php`, `tests/Feature/Console/EnsureLocalUserCommandTest.php`
**Depends on**: None
**Reuses**: código da F1
**Refs**: NOAUTH-13

**Steps**:
- [ ] RED — nos testes, trocar todas as chamadas `exec($email, $password)` por `exec($email)`; remover asserções de `Hash::check`; adicionar ao `EnsureLocalUserTest`:

```php
    it('NOAUTH13: creates the local user without a password', function () {
        /** @var TestCase $this */
        $result = $this->action->exec('local@craftdb.local');

        expect($result['user']->fresh()->password)->toBeNull();
    });
```

  e trocar o teste DOCK07 do comando por:

```php
    it('NOAUTH13: prints url and email, never a password line', function () {
        /** @var TestCase $this */
        config([
            'app.url' => 'http://localhost:8080',
            'craftdb.local_user.email' => 'local@craftdb.local',
        ]);

        $this->artisan('craftdb:local-user')
            ->expectsOutputToContain('http://localhost:8080')
            ->expectsOutputToContain('local@craftdb.local')
            ->doesntExpectOutputToContain('Senha')
            ->assertSuccessful();
    });
```

  (remover o teste "does not print the password when the user already exists" e o DOCK10 de senha; manter o DOCK10 de e-mail via config)
- [ ] Rodar os 2 arquivos → FAIL (assinatura antiga)
- [ ] GREEN — `EnsureLocalUser::exec(string $email): array`: `firstOrCreate(['email' => $email], ['name' => 'Local', 'email_verified_at' => now()])` e **manter** as linhas `$user->email_verified_at ??= now(); $user->save();` (o `#[Fillable]` do `User` descarta `email_verified_at` no create); docblock "Never changes an existing user"
- [ ] `users.password` já é nullable (`0001_01_01_000000_create_users_table.php:21`) — nenhuma migration necessária
- [ ] Comando: remover leitura de `local_user.password` e a linha "Senha"; imprimir `URL` e `E-mail`
- [ ] `config/craftdb.php`: remover a chave `password`
- [ ] Rodar os 2 arquivos → PASS

**Done when**:
- [ ] Gate quick: `./vendor/bin/pest tests/Feature/Actions/Local tests/Feature/Console` verde
- [ ] Gate full: `composer test` verde

**Tests**: integration · **Gate**: full

---

### T2: Middleware `AuthenticateLocalUser` (TDD)

**What**: Login automático do usuário local no grupo `web`, uma vez por sessão.
**Where**: `app/Http/Middleware/AuthenticateLocalUser.php` (novo), `bootstrap/app.php`, `tests/Feature/Middleware/AuthenticateLocalUserTest.php` (novo); ajustar testes de "guest → login"
**Depends on**: T1
**Reuses**: `EnsureLocalUser`, `SetCurrentTeamOnLogin`
**Refs**: NOAUTH-01, NOAUTH-02, NOAUTH-03, NOAUTH-05, NOAUTH-23 (+ A10)

**Steps**:
- [ ] RED — criar `tests/Feature/Middleware/AuthenticateLocalUserTest.php`:

```php
<?php

namespace Tests\Feature\Middleware;

use App\Models\Team\Team;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config(['craftdb.local_user.email' => 'local@craftdb.local']);
});

describe('AuthenticateLocalUser', function (): void {
    it('NOAUTH01: authenticates the local user on a request without session user', function () {
        /** @var TestCase $this */
        $this->get('/dashboard')->assertOk();

        expect(Auth::user()?->email)->toBe('local@craftdb.local');
    });

    it('NOAUTH02: creates the local user when it does not exist yet', function () {
        /** @var TestCase $this */
        expect(User::where('email', 'local@craftdb.local')->exists())->toBeFalse();

        $this->get('/dashboard')->assertOk();

        $user = User::where('email', 'local@craftdb.local')->firstOrFail();
        expect($user->ownedTeams()->count())->toBe(1);
    });

    it('NOAUTH03: logs in only once per session, keeping the current team', function () {
        /** @var TestCase $this */
        Event::fake([Login::class]);

        $this->get('/dashboard')->assertOk();
        $user = Auth::user();
        $second = Team::forceCreate(['user_id' => $user->id, 'name' => 'Second']);
        session(['current_team' => $second]);

        $this->get('/dashboard')->assertOk();

        Event::assertDispatchedTimes(Login::class, 1);
        expect(session('current_team')->id)->toBe($second->id);
    });

    it('NOAUTH05: settings pages respond without redirecting to login', function () {
        /** @var TestCase $this */
        $this->get('/settings/profile')->assertOk();
        $this->get('/settings/appearance')->assertOk();
    });
});
```

- [ ] Rodar → FAIL (redirect para `/login`)
- [ ] GREEN — criar `app/Http/Middleware/AuthenticateLocalUser.php`:

```php
<?php

namespace App\Http\Middleware;

use App\Actions\Local\EnsureLocalUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * CraftDB Local has a single implicit user: log it in once per session.
 */
class AuthenticateLocalUser
{
    public function __construct(private EnsureLocalUser $ensureLocalUser) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            Auth::login($this->ensureLocalUser->exec((string) config('craftdb.local_user.email'))['user']);
        }

        return $next($request);
    }
}
```

- [ ] `bootstrap/app.php`: `AuthenticateLocalUser::class` como **primeiro** item de `$middleware->web(append: [...])` e `$middleware->prependToPriorityList(before: \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class, prepend: AuthenticateLocalUser::class);`
- [ ] Rodar → PASS
- [ ] Ajustar testes que afirmavam redirect de guest para login (NOAUTH-23): `resources/views/pages/⚡dashboard/dashboard.test.php:11` → guest recebe 200 no dashboard; `tests/Feature/Actions/Diagram/ExportDiagramToJsonTest.php:206` e `tests/Feature/Actions/Diagram/ExportDiagramToLaravelMigrationTest.php:719` → o guest vira o usuário local, que não é do time do teste: afirmar que o export **não** acontece (`$response->assertStatus(...)` diferente de 200 — hoje `CheckTeamRole` devolve redirect para o dashboard; depois da T6 vira 403 da policy). Usar `expect($response->status())->not->toBe(200)` para valer nas duas fases
- [ ] Detecção de idioma por `cf-ipcountry` (A10): o login automático grava o idioma do usuário na sessão e anula a detecção geo do Cloudflare, que não existe no app local → apagar `tests/Feature/Middleware/SetLocaleGeoTest.php` e remover o ramo `shouldUsePortugueseBrazilFromCloudflare` (e helpers só usados por ele) de `app/Http/Middleware/SetLocale.php`; a escolha de idioma pelo usuário (tela Idioma) continua
- [ ] Falhas conhecidas que a T3/T4 removem: `tests/Feature/Auth/*`, `tests/Feature/Settings/TwoFactorAuthenticationTest.php`, `tests/Feature/Settings/PasswordUpdateTest.php` (dependem de guest/Fortify). Rodar o gate full e listar no relatório exatamente quais falharam

**Done when**:
- [ ] Gate quick: `./vendor/bin/pest tests/Feature/Middleware/AuthenticateLocalUserTest.php` → 4 passed
- [ ] Gate full: `composer test` verde, exceto as falhas conhecidas listadas acima (relatório traz a lista exata)

**Tests**: integration · **Gate**: full (com exceção documentada)

---

### T3: Settings e UI do app sem conta (logout, senha, 2FA, excluir conta, perfil só nome)

**What**: Telas do app autenticado sem nenhum vestígio de conta.
**Where**: `resources/views/components/settings/*`, `resources/views/components/layouts/app/{master,header}.blade.php`, `resources/views/components/diagram/canvas/ui/topbar.blade.php`, `app/Livewire/Actions/Logout.php`, `tests/Feature/Settings/ProfileUpdateTest.php`
**Depends on**: T2
**Refs**: NOAUTH-08, NOAUTH-09, NOAUTH-10
**Nota**: roda antes da remoção do Fortify (T4) para que nenhuma view chame rotas já removidas

**Steps**:
- [ ] RED — reescrever `tests/Feature/Settings/ProfileUpdateTest.php` para: (a) `NOAUTH10` atualizar o nome via `Livewire::test('settings.profile')->set('name', 'Novo')->call('updateProfileInformation')` (usar o nome do método real do componente) e conferir no banco; (b) `NOAUTH10` a página `/settings/profile` não contém `wire:model="email"` nem texto de verificação/excluir conta; criar `tests/Feature/Settings/SettingsNavigationTest.php` com `NOAUTH09` (GET `/settings/profile` mostra links Perfil/Aparência/Idioma/Integrações e **não** contém `settings/password`, `settings/two-factor`, `delete-user`) e `NOAUTH08` (GET `/dashboard` e uma página de diagrama não contêm form de logout: afirmar no HTML renderizado a ausência de `/logout` e dos textos de sair reais dos layouts)
- [ ] Rodar → FAIL
- [ ] `routes/web.php`: remover as rotas `settings/password` (`user-password.edit`) e `settings/two-factor` (`two-factor.show`, incluindo o `use Laravel\Fortify\Features;` se ficar sem uso) — os componentes delas são apagados a seguir
- [ ] Apagar `resources/views/components/settings/⚡password.blade.php`, `⚡two-factor.blade.php`, `two-factor/` (recovery codes), `⚡delete-user-form.blade.php`, `app/Livewire/Actions/Logout.php`
- [ ] `⚡profile.blade.php`: manter só o campo nome (remover e-mail, bloco de verificação `:55-82` e `<livewire:settings.delete-user-form/>` `:114`)
- [ ] `settings/layout.blade.php`: remover links Senha (`:8`) e 2FA (`:9-11`)
- [ ] Remover os forms de logout: `layouts/app/master.blade.php:114`, `layouts/app/header.blade.php:85`, `diagram/canvas/ui/topbar.blade.php:185`
- [ ] Rodar → PASS

**Done when**:
- [ ] `./vendor/bin/pest tests/Feature/Settings` verde
- [ ] Gate full: `composer test` verde

**Tests**: integration · **Gate**: full

---

### T4: Remover Fortify, Socialite e telas de auth

**What**: Tirar as dependências e todo o fluxo de credenciais; `/`, `/login` e `/register` redirecionam para o dashboard.
**Where**: `composer.json`/`composer.lock`, `bootstrap/providers.php`, `bootstrap/app.php`, `routes/web.php`, `routes/auth.php`, `app/Providers/FortifyServiceProvider.php`, `config/fortify.php`, `app/Actions/Fortify/*`, `app/Http/Controllers/AuthController.php`, `app/Exceptions/EmailAlreadyRegisteredException.php`, `app/Http/Middleware/ValidateLoginTermsAcceptance.php`, `app/Http/Middleware/SetLocale.php`, `app/Models/User.php`, `database/factories/UserFactory.php`, views de auth, `config/services.php`, `.env.example`, `lang/*/auth.php`, testes
**Depends on**: T3
**Refs**: NOAUTH-04, NOAUTH-06, NOAUTH-07, NOAUTH-22

**Steps**:
- [ ] RED — criar `tests/Feature/Routes/RemovedAuthRoutesTest.php`:

```php
<?php

namespace Tests\Feature\Routes;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

uses(RefreshDatabase::class);

describe('Removed auth routes', function (): void {
    it('NOAUTH04: redirects /, /login and /register to the dashboard', function (string $uri) {
        /** @var TestCase $this */
        $this->get($uri)->assertRedirect('/dashboard');
    })->with(['/', '/login', '/register']);

    it('NOAUTH06: no credential, 2FA or social routes are registered', function (string $name) {
        expect(Route::has($name))->toBeFalse();
    })->with([
        'login.store', 'logout', 'register.store', 'password.request', 'password.email',
        'password.reset', 'password.update', 'verification.notice', 'verification.verify',
        'verification.send', 'password.confirm', 'two-factor.login', 'two-factor.show',
        'user-password.edit', 'auth.github.redirect', 'auth.github.callback',
        'auth.google.redirect', 'auth.google.callback',
    ]);

    it('NOAUTH07: fortify and socialite are not installed', function () {
        expect(class_exists(\Laravel\Fortify\Fortify::class))->toBeFalse()
            ->and(class_exists(\Laravel\Socialite\Facades\Socialite::class))->toBeFalse();
    });
});
```

- [ ] Rodar → FAIL
- [ ] Apagar `tests/Feature/Auth/` inteiro e `tests/Feature/Settings/TwoFactorAuthenticationTest.php`, `tests/Feature/Settings/PasswordUpdateTest.php` se ainda existirem (NOAUTH-22)
- [ ] `routes/web.php`: trocar a rota `/` por `Route::redirect('/', '/dashboard')->name('landing.index');` e adicionar `Route::redirect('/login', '/dashboard')->name('login');` e `Route::redirect('/register', '/dashboard')->name('register');`; remover `'verified'` de todas as rotas; remover `require __DIR__ . '/auth.php';`
- [ ] Apagar `routes/auth.php` (as rotas de convite dele são removidas aqui; o resto de convites sai na T6)
- [ ] `composer remove laravel/fortify laravel/socialite`
- [ ] Apagar: `app/Providers/FortifyServiceProvider.php` (+ linha em `bootstrap/providers.php`), `config/fortify.php`, `app/Actions/Fortify/` inteiro, `app/Http/Controllers/AuthController.php`, `app/Exceptions/EmailAlreadyRegisteredException.php`, `app/Http/Middleware/ValidateLoginTermsAcceptance.php` (+ registro em `bootstrap/app.php`), `resources/views/components/auth/`, `resources/views/components/layouts/auth.blade.php`, `resources/views/components/layouts/auth/`, `resources/views/components/auth-header.blade.php`, `resources/views/components/auth-session-status.blade.php`, `resources/views/components/input-otp.blade.php`, `resources/views/flux/icon/github.blade.php`, `resources/views/flux/icon/google.blade.php`, entradas `github`/`google` de `config/services.php`, `GITHUB_*`/`GOOGLE_*` de `.env.example`, strings de termos de login em `lang/en/auth.php` e `lang/pt_BR/auth.php` (`:20-26`)
- [ ] `app/Http/Middleware/SetLocale.php`: remover a checagem de rotas `login`/`login.store`/`register`/`register.store` se ainda existir após a T2
- [ ] `app/Models/User.php`: remover `TwoFactorAuthenticatable`, `two_factor_*`/`github_*`/`google_*` de `#[Fillable]`/`#[Hidden]`, `PROVIDERS`, `isProvider`, `isAuthenticatedWithGoogleOrGithub`, `isAuthenticatedWithGithub`, `isAuthenticatedWithGoogle`
- [ ] `database/factories/UserFactory.php`: remover campos 2FA e o state `withoutTwoFactor()`; `tests/TestCase.php::autenticated()` usa `User::factory()->create()`; trocar `withoutTwoFactor()` em todo `tests/` e `resources/views/**/*.test.php`
- [ ] `settings/⚡team.blade.php:31,132`: remover o uso de `isAuthenticatedWithGoogleOrGithub()` (sair do time sem senha) — se a ação "sair do time" só existe para membros, remover a ação inteira (o usuário é dono)
- [ ] Grep de verificação: `grep -rn "Fortify\|Socialite\|two_factor\|TwoFactor\|github_\|google_\|isAuthenticatedWith" app bootstrap config routes resources database/factories tests lang` → só migrations antigas e `resources/views/pages/landing-page`
- [ ] Rodar → PASS

**Done when**:
- [ ] `./vendor/bin/pest tests/Feature/Routes/RemovedAuthRoutesTest.php` verde
- [ ] `composer show laravel/fortify` → erro "not found"
- [ ] Gate full: `composer test` 100% verde

**Tests**: integration · **Gate**: full

---

### T5: Remover o super root e `/feedback`

**What**: Um único usuário no app.
**Where**: migrations `2026_03_08_155628_add_super_root_user.php` e `2026_03_08_160245_insert_super_root_has_team.php`, `app/Http/Middleware/EnsureSuperRoot.php`, `app/Http/Controllers/FeedbackController.php`, `routes/web.php`, `bootstrap/app.php`, `resources/views/components/ui/menu.blade.php`, view da listagem de feedback, `app/Models/User.php`, `.env.example`, `tests/Feature/Actions/Local/EnsureLocalUserTest.php`
**Depends on**: T4
**Refs**: NOAUTH-11, NOAUTH-12

**Steps**:
- [ ] RED — criar `tests/Feature/Database/SuperRootRemovedTest.php`:

```php
<?php

namespace Tests\Feature\Database;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

uses(RefreshDatabase::class);

it('NOAUTH11: a fresh database has no users', function () {
    expect(User::count())->toBe(0);
});

it('NOAUTH12: feedback route and super.root middleware are gone', function () {
    /** @var TestCase $this */
    expect(Route::has('feedback.index'))->toBeFalse()
        ->and(app('router')->getMiddleware())->not->toHaveKey('super.root');
});
```

- [ ] Rodar → FAIL
- [ ] Migrations do super root: corpo de `up()` e `down()` vira vazio, com comentário `// No-op: CraftDB Local has no super root (F2).`; remover imports não usados
- [ ] Apagar `EnsureSuperRoot` (+ alias em `bootstrap/app.php`), `FeedbackController`, rota `/feedback`, link em `ui/menu.blade.php:5-6` e a view da listagem (descobrir via `grep -rn "feedback" resources/views` — não apagar o form `contact-us`, que é F7)
- [ ] `User.php`: remover `isSuperRoot()` e os bypasses em `:198,260` (limites passam a valer sempre — F3 remove limites)
- [ ] `EnsureLocalUserTest`: remover o teste que reaproveita o super root (id 1) — o caso "e-mail de usuário existente" segue coberto pelo teste do usuário não verificado
- [ ] `.env.example`: remover `SUPER_ROOT_*`
- [ ] Nota: bancos vindos da F1 mantêm o user id 1 órfão — inofensivo (NOAUTH-19), nunca usado pelo login automático
- [ ] Testes que criam diagramas e dependiam do bypass do super root: rodar a suite; se algum quebrar por limite de plano, usar o usuário de `autenticated()` com plano adequado conforme os helpers existentes (não alterar limites — F3)
- [ ] Rodar → PASS

**Done when**:
- [ ] `./vendor/bin/pest tests/Feature/Database/SuperRootRemovedTest.php` verde
- [ ] `grep -rn "isSuperRoot\|SUPER_ROOT\|super.root\|FeedbackController" app bootstrap config routes resources tests` vazio
- [ ] Gate full: `composer test` verde

**Tests**: integration · **Gate**: full

---

### T6: Remover convites, membros e papéis

**What**: Autorização por posse do time; nada de convites/papéis.
**Where**: `app/Models/Team/{Invitation,Role,Owner,Membership}.php`, `app/Enums/Role.php`, `app/Models/Concerns/{HasMembers,HasTeams}.php`, `app/Http/Middleware/CheckTeamRole.php`, `app/Http/Controllers/InvitationController.php`, `app/Exceptions/InvitationException.php`, `app/Notifications/TeamInvitationNotification.php`, `app/Console/Commands/CleanupAcceptedInvitations.php`, `app/Actions/Diagram/AddMemberToTeam.php`, `app/Policies/{DiagramPolicy,TeamPolicy}.php`, `app/Providers/AppServiceProvider.php` (gate), `app/Support/DiagramLimits.php`, `bootstrap/app.php`, `routes/web.php`, `routes/console.php`, `database/seeders/{DatabaseSeeder,DefaultRolesSeeder}.php`, `config/teams.php`, `resources/views/vendor/teams/`, `resources/views/components/settings/team/⚡members.blade.php`, `resources/views/components/settings/⚡team.blade.php`, `resources/views/components/settings/layout.blade.php`, `app/Models/Team/Role.php`, docs, `tests/Feature/Settings/TeamMembersInviteTest.php`
**Depends on**: T5
**Refs**: NOAUTH-15, NOAUTH-16, NOAUTH-17, NOAUTH-20

**Steps**:
- [ ] RED — criar `tests/Feature/Routes/RemovedTeamMembershipTest.php`:

```php
<?php

namespace Tests\Feature\Routes;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

uses(RefreshDatabase::class);

it('NOAUTH15: no invitation or member management routes, commands or role middleware', function (string $name) {
    expect(Route::has($name))->toBeFalse();
})->with(['invitation.accept', 'team.members.edit', 'team.invitations.edit']);

it('NOAUTH15: invitations:cleanup command is gone', function () {
    expect(Artisan::all())->not->toHaveKey('invitations:cleanup');
});

it('NOAUTH16: role middleware alias is gone', function () {
    expect(app('router')->getMiddleware())->not->toHaveKey('role');
});

it('NOAUTH20: db:seed runs without errors', function () {
    /** @var TestCase $this */
    $this->artisan('db:seed')->assertSuccessful();
});
```

  e em `tests/Feature/Policies/DiagramOwnershipTest.php` (novo): `NOAUTH16` dono do time pode `createOnTeam`/`update`/`share`; usuário que não é dono recebe `false` (usar `Gate::forUser($user)->allows(...)` com a assinatura real das policies).
- [ ] Rodar → FAIL
- [ ] `DiagramPolicy::createOnTeam/update/share` e gate `createDiagramOnTeam` (`AppServiceProvider.php:204`): trocar `userHasRole(Role::EDITOR)` por `$user->ownsTeam($team)`; `TeamPolicy`: remover métodos/ramos de convite e membros; `DiagramLimits`: remover uso de papéis se houver
- [ ] `routes/web.php`: remover `role:viewer|editor|owner` do grupo de diagrama (fica `['auth', 'throttle:60,1']`), remover rotas `team.members.edit` e `team.invitations.edit`; `bootstrap/app.php`: remover alias `role` e o renderable de `InvitationException`; `routes/console.php`: remover o `Schedule::command('invitations:cleanup')`
- [ ] `HasMembers`/`HasTeams`/`Membership`: remover `roles()`, `withPivot('role_id')`, `addUser`, `updateUser`, `deleteUser` (se só usado por membros), `inviteUser`, `inviteAccept`, `userHasRole`, `invitations()`, `hasPendingInvitationForEmail`, `hasReachedMaxInvitationsPerDay`, e o `role_id` de `Membership`
- [ ] Apagar: `Invitation`, `Role` (model), `Owner`, enum `Role`, `CheckTeamRole`, `InvitationController`, `InvitationException`, `TeamInvitationNotification`, `CleanupAcceptedInvitations`, `AddMemberToTeam`, `DefaultRolesSeeder`, `config/teams.php`, `resources/views/vendor/teams/`, `settings/team/⚡members.blade.php`, `tests/Feature/Settings/TeamMembersInviteTest.php`, `SETUP_ROLES.md`, `docs/TEAM_ROLES_SETUP.md`; enxugar `docs/TEAM_MANAGEMENT.md` (remover seções de convites/papéis/membros)
- [ ] `DatabaseSeeder`: remover `DefaultRolesSeeder` e as factories de usuários de teste (o seeder vira `EnsureLocalUser` via `$this->command->call('craftdb:local-user')` ou vazio — escolher vazio se `craftdb:local-user` já cobre)
- [ ] `settings/⚡team.blade.php`: remover seções de membros, papéis, convites e "sair do time"; `settings/layout.blade.php`: remover link de membros (`:16-24` mantém só Time e Criar time)
- [ ] `resources/views/components/diagram/⚡create/create.test.php:516-518` (caso multiusuário): ajustar para posse de time
- [ ] `ExportDiagramToJsonTest` e `ExportDiagramToLaravelMigrationTest` (testes "does not export for the implicit local user outside the team"): trocar `expect($response->status())->not->toBe(200)` por `assertForbidden()` agora que `CheckTeamRole` saiu (a policy `view` responde 403)
- [ ] Grep: `grep -rn "Invitation\|userHasRole\|role_id\|CheckTeamRole\|Enums\\\\Role\|invitations:cleanup\|Jurager" app bootstrap config routes resources database/seeders tests` → só migrations antigas
- [ ] Rodar → PASS

**Done when**:
- [ ] `./vendor/bin/pest tests/Feature/Routes tests/Feature/Policies` verde
- [ ] Gate full: `composer test` verde

**Tests**: integration · **Gate**: full

---

### T7: Migration de limpeza do schema

**What**: Remover colunas e tabelas mortas, preservando dados.
**Where**: `database/migrations/2026_10_21_000001_remove_auth_leftovers.php` (novo), `tests/Feature/Database/RemoveAuthLeftoversTest.php` (novo)
**Depends on**: T6
**Refs**: NOAUTH-18, NOAUTH-19

**Steps**:
- [ ] RED — criar `tests/Feature/Database/RemoveAuthLeftoversTest.php`:

```php
<?php

namespace Tests\Feature\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('NOAUTH18: drops auth columns from users and keeps password nullable', function () {
    foreach ([
        'two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at',
        'github_id', 'github_token', 'github_refresh_token',
        'google_id', 'google_token', 'google_refresh_token',
    ] as $column) {
        expect(Schema::hasColumn('users', $column))->toBeFalse();
    }

    expect(Schema::hasColumn('users', 'password'))->toBeTrue();
});

it('NOAUTH18: drops invitation, role and password reset tables and team_user.role_id', function () {
    expect(Schema::hasTable('invitation'))->toBeFalse()
        ->and(Schema::hasTable('role'))->toBeFalse()
        ->and(Schema::hasTable('password_reset_tokens'))->toBeFalse()
        ->and(Schema::hasColumn('team_user', 'role_id'))->toBeFalse()
        ->and(Schema::hasTable('team_user'))->toBeTrue();
});
```

- [ ] Rodar → FAIL
- [ ] GREEN — criar `database/migrations/2026_10_21_000001_remove_auth_leftovers.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CraftDB Local (F2): remove what auth, social login, invitations and roles left behind.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_user', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });

        Schema::dropIfExists('invitation');
        Schema::dropIfExists('role');
        Schema::dropIfExists('password_reset_tokens');

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['github_id']);
            $table->dropUnique(['google_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at',
                'github_id', 'github_token', 'github_refresh_token',
                'google_id', 'google_token', 'google_refresh_token',
            ]);
        });
    }

    /**
     * Approximate inverse (original FK/index names and seeded roles are not restored) — enough for rollback in dev.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->string('github_id')->nullable()->unique();
            $table->text('github_token')->nullable();
            $table->text('github_refresh_token')->nullable();
            $table->string('google_id')->nullable()->unique();
            $table->text('google_token')->nullable();
            $table->text('google_refresh_token')->nullable();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('role', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name')->nullable();
            $table->string('description')->nullable();
        });

        Schema::create('invitation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('team');
            $table->foreignId('role_id')->constrained('role');
            $table->string('email');
            $table->string('token', 64);
            $table->timestamp('expires_at');
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
            $table->unique(['team_id', 'email', 'token']);
        });

        Schema::table('team_user', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->constrained('role');
        });
    }
};
```

- [ ] Rodar → PASS; rodar também `php artisan migrate:fresh --database=sqlite` contra um arquivo temporário (`DB_DATABASE=/tmp/f2.sqlite`) e `migrate:rollback --step=1` + `migrate` para provar o `down()`; apagar o arquivo
- [ ] Se `dropUnique(['github_id'])` falhar por nome de índice, usar o nome real (`users_github_id_unique`) — conferir com `Schema::getIndexes('users')`

**Done when**:
- [ ] `./vendor/bin/pest tests/Feature/Database` verde
- [ ] rollback + migrate de novo sem erro (SQLite)
- [ ] Gate full: `composer test` verde
- [ ] NOAUTH-19 verificado em T8 (MySQL, volume da F1)

**Tests**: integration · **Gate**: full

---

### T8: Docker, README e smoke sem senha

**What**: Stack e docs coerentes com o login automático; validar migração sobre o volume da F1 (MySQL).
**Where**: `docker-compose.yml`, `docs/README.md`, `docs/README.en.md`, `docker/smoke-test.sh`
**Depends on**: T7
**Refs**: NOAUTH-14, NOAUTH-19, NOAUTH-04, NOAUTH-05

**Steps**:
- [ ] ANTES de mudar o compose: com o stack da F1 de pé (volume com dados), criar um diagrama de marcação `docker compose exec -T app php artisan tinker --execute="App\Models\Diagram::forceCreate(['name'=>'f1-keep','slug'=>'f1-keep','style'=>'neutral','team_id'=>App\Models\Team\Team::whereHas('owner', fn(\$q)=>\$q->where('email','local@craftdb.local'))->first()->id]);"` (ajustar a relação ao nome real em `Team`)
- [ ] `docker-compose.yml`: remover `CRAFTDB_USER_PASSWORD`
- [ ] `docker compose up -d --build` (sem `-v`) → migrations da F2 rodam sobre o banco da F1; conferir que `f1-keep` existe, o usuário local existe e `curl -s -o /dev/null -w '%{http_code}' http://localhost:8080/dashboard` → `200` sem cookie (NOAUTH-19, NOAUTH-05)
- [ ] READMEs: remover a linha `CRAFTDB_USER_PASSWORD` da tabela e trocar "entre com `local@craftdb.local` / `craftdb`" por "o app abre direto no dashboard, sem login" (PT e EN)
- [ ] `docker/smoke-test.sh`: DOCK-07 passa a checar `local@craftdb.local` e `URL:` no log e **não** `Senha`; adicionar check NOAUTH-04 `curl -s -o /dev/null -w '%{http_code} %{redirect_url}' "$URL/"` → `302 http://localhost:${PORT}/dashboard` e NOAUTH-05 `/dashboard` → 200 sem cookie
- [ ] `bash docker/smoke-test.sh` → `Smoke test OK`

**Done when**:
- [ ] Evidência de NOAUTH-19 (diagrama `f1-keep` preservado após migrar)
- [ ] `bash docker/smoke-test.sh` → OK

**Tests**: none (infra; smoke) · **Gate**: smoke

---

### T9: Verificação final

**What**: Provar a remoção completa e o fluxo OAuth do MCP.
**Where**: — (só verificação; correções pontuais se algo aparecer)
**Depends on**: T8
**Refs**: NOAUTH-21, NOAUTH-22, NOAUTH-24, A7

**Steps**:
- [ ] `grep -rniE "fortify|socialite|invitation|userHasRole|role_id|super.?root|two_factor|logout" app bootstrap config routes resources lang --include=*.php --include=*.blade.php --include=*.json | grep -v "resources/views/pages/landing-page"` → vazio (exceto comentários das migrations no-op, que estão em `database/`)
- [ ] `php artisan route:list --no-ansi | grep -iE "login|register|password|two-factor|invitation|feedback|auth/"` → só os redirects `login`/`register`
- [ ] OAuth MCP (A7): com o stack de pé, abrir `http://localhost:8080/oauth/authorize?client_id=x&response_type=code` (ou o fluxo real documentado em `settings/⚡integrations`) e confirmar que não redireciona para login (pode responder erro de client inválido — o importante é não ser redirect de auth)
- [ ] `composer test` 100% verde
- [ ] `./vendor/bin/phpstan analyse --memory-limit=1G` sem erros novos em relação ao `develop`

**Done when**:
- [ ] Todos os checks acima com saída registrada

**Tests**: — · **Gate**: full + smoke

---

## Validação pré-aprovação

### Task Granularity Check

| Task | Escopo | Status |
|---|---|---|
| T1 | Action + comando + config + 1 migration | ✅ Coeso (mesma funcionalidade) |
| T2 | 1 middleware + registro + testes | ✅ Granular |
| T3 | UI de conta | ✅ Coeso |
| T4 | Remoção Fortify/Socialite | ⚠️ Muitos arquivos, mas é uma remoção atômica (meia remoção não compila) |
| T5 | Super root | ✅ Coeso |
| T6 | Convites/papéis | ⚠️ Muitos arquivos, remoção atômica (FKs/relacionamentos cruzados) |
| T7 | 1 migration + teste | ✅ Granular |
| T8 | Docker/docs/smoke | ✅ Coeso |
| T9 | Verificação | ✅ |

### Diagram-Definition Cross-Check

| Task | Depends On (corpo) | Diagrama | Status |
|---|---|---|---|
| T1 | None | — | ✅ |
| T2 | T1 | T1 → T2 | ✅ |
| T3 | T2 | T2 → T3 | ✅ |
| T4 | T3 | T3 → T4 | ✅ |
| T5 | T4 | T4 → T5 | ✅ |
| T6 | T5 | T5 → T6 | ✅ |
| T7 | T6 | T6 → T7 | ✅ |
| T8 | T7 | T7 → T8 | ✅ |
| T9 | T8 | T8 → T9 | ✅ |

### Test Co-location Validation

| Task | Camada | Exige | Task diz | Status |
|---|---|---|---|---|
| T1 | Action/Command | integration | integration | ✅ |
| T2 | Middleware | integration | integration | ✅ |
| T3 | Livewire/Blade | integration | integration | ✅ |
| T4 | Rotas/providers | integration | integration (RemovedAuthRoutesTest) | ✅ |
| T5 | Migrations/rotas | integration | integration | ✅ |
| T6 | Policies/rotas/seeder | integration | integration | ✅ |
| T7 | Migration | integration | integration | ✅ |
| T8 | Infra | none | smoke | ✅ |
| T9 | — | — | full + smoke | ✅ |

### Cobertura da spec

| ID | Tasks |
|---|---|
| NOAUTH-01 | T2 |
| NOAUTH-02 | T2 |
| NOAUTH-03 | T2 |
| NOAUTH-04 | T4, T8 |
| NOAUTH-05 | T2, T8 |
| NOAUTH-06 | T3, T4 |
| NOAUTH-07 | T4 |
| NOAUTH-08 | T3 |
| NOAUTH-09 | T3 |
| NOAUTH-10 | T3 |
| NOAUTH-11 | T5 |
| NOAUTH-12 | T5 |
| NOAUTH-13 | T1 |
| NOAUTH-14 | T8 |
| NOAUTH-15 | T6 |
| NOAUTH-16 | T6 |
| NOAUTH-17 | T6 |
| NOAUTH-18 | T7 |
| NOAUTH-19 | T8 |
| NOAUTH-20 | T6 |
| NOAUTH-21 | T9 |
| NOAUTH-22 | T4, T9 |
| NOAUTH-23 | T2 |
| NOAUTH-24 | T9 |

24/24 mapeados.
