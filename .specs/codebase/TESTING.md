# Infraestrutura de Testes

## Frameworks

**Unitários/Integração:** Pest 4 (sobre PHPUnit como runner)
**E2E:** Não presente
**Análise estática:** Larastan 3 (PHPStan para Laravel) — configurado em `phpstan.neon`
**Cobertura:** Configurado via phpunit.xml (source: app/)

## Organização dos testes

**Localização:** `tests/`
**Nomenclatura:** `{Feature}Test.php` — ex: `AuthenticateLocalUserTest.php`, `WorkspacesPageTest.php`
**Estrutura:**
```
tests/
├── Feature/
│   ├── Middleware/AuthenticateLocalUserTest.php
│   ├── Routes/RemovedAuthSurfaceTest.php
│   ├── Database/ (FreshDatabaseTest, RemoveAuthLeftoversTest)
│   ├── Policies/DiagramOwnershipTest.php
│   ├── Settings/
│   │   ├── ProfileUpdateTest.php
│   │   ├── SettingsNavigationTest.php
│   │   └── (outros settings tests)
│   ├── Workspaces/ (OwnershipOnlyTest, WorkspacesPageTest, CrossWorkspaceTest, WorkspaceWordingTest)
│   ├── Actions/Team/ (RenameTeamTest, DeleteTeamTest)
│   └── Actions/Parse/
│       └── (fixtures para testes de parser SQL/migration)
└── Unit/
    └── (testes unitários de classes isoladas)
```

**Total:** 288 testes (suíte completa)

## Padrões de teste

### Testes de Feature (HTTP/Livewire)

**Abordagem:** Testes de integração completos que exercitam o stack inteiro
**Localização:** `tests/Feature/`
**Padrão observado:**
```php
// Pest syntax com helpers de Laravel
test('local user is logged in automatically', function () {
    $this->get('/dashboard')->assertOk();
    $this->assertAuthenticated();
});
```

**Testes de usuário local/auth cobrem:**
- Login automático do usuário local (uma vez por sessão)
- `/login` e `/register` redirecionam para `/dashboard`; rotas de auth removidas retornam 404
- Banco novo (migrations do zero) e migration `remove_auth_leftovers`
- Autorização de diagramas/workspaces por posse (`ownsTeam`); workspaces extras e de outro dono via helpers `secondTeam()` / `foreignTeam()` em `tests/TestCase.php`
- Página de workspaces: criar (vira atual), renomear, apagar com confirmação por nome (recusa o último), alternar; terminologia "workspace" na UI

**Suíte atual:** 288 testes passando

### Testes de Livewire

**Abordagem:** Testa componentes Livewire via `Livewire::test()`
**Localização:** `tests/Feature/Settings/`, `tests/Feature/Workspaces/`
**Padrão observado:**
```php
test('workspace can be created', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test('settings.⚡workspaces')
        ->set('createName', 'My Workspace')
        ->call('create')
        ->assertHasNoErrors();
});
```

### Testes de Parser

**Abordagem:** Testa Actions de parse com fixtures reais
**Localização:** `tests/Feature/Actions/Parse/`
**Fixtures:** Arquivos SQL e migrations Laravel de exemplo para verificar parsing correto

## Configuração

**phpunit.xml:**
```xml
<testsuites>
    <testsuite name="Unit">
        <directory>tests/Unit</directory>
    </testsuite>
    <testsuite name="Feature">
        <directory>tests/Feature</directory>
    </testsuite>
    <testsuite name="Components">
        <!-- Blade components tests -->
    </testsuite>
</testsuites>

<source>
    <include>
        <directory>app</directory>
    </include>
</source>

<!-- Env para testes -->
<php>
    <env name="DB_CONNECTION" value="sqlite"/>
    <env name="DB_DATABASE" value=":memory:"/>
    <env name="MAIL_MAILER" value="array"/>
</php>
```

## Execução

**Comandos:**
```bash
composer test        # Roda Pest com cobertura
php artisan test     # Via Artisan
./vendor/bin/pest    # Diretamente

# Filtrar por suite
./vendor/bin/pest --group=auth
./vendor/bin/pest tests/Feature/Auth/
./vendor/bin/pest tests/Feature/Telemetry/   # F5: NoExternalServicesTest e NoExternalRequestsTest (renderiza páginas e falha em qualquer host externo)

# Análise estática
./vendor/bin/phpstan analyse

# Smoke test do stack Docker (sobe do zero; roda `docker compose down -v`)
bash docker/smoke-test.sh   # inclui ZT-09: sem hosts externos em /dashboard e /settings/workspaces
```

**Configuração:** SQLite in-memory para isolamento total entre testes — sem estado persistente

## Cobertura

**Atual:** Auth/usuário local, Settings, Parse e regressões da F3 (suíte: 263 testes passando). Testes da F3: `tests/Feature/Plans/{PremiumFeaturesUnlockedTest,NoLimitsTest,PublicLinksRemovedTest,BillingRemovedTest}.php` (funcionalidades sempre liberadas, sem limites, links públicos e billing removidos) e `tests/Feature/Database/RemoveBillingLeftoversTest.php` (migration `2026_10_21_000002_remove_billing_leftovers`)
**Metas:** Não documentadas explicitamente
