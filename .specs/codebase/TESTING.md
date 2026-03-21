# Infraestrutura de Testes

## Frameworks

**Unitários/Integração:** Pest 4 (sobre PHPUnit como runner)
**E2E:** Não presente
**Análise estática:** Larastan 3 (PHPStan para Laravel) — configurado em `phpstan.neon`
**Cobertura:** Configurado via phpunit.xml (source: app/)

## Organização dos testes

**Localização:** `tests/`
**Nomenclatura:** `{Feature}Test.php` — ex: `RegistrationTest.php`, `TeamCreateTest.php`
**Estrutura:**
```
tests/
├── Feature/
│   ├── Auth/
│   │   ├── RegistrationTest.php
│   │   ├── AuthenticationTest.php
│   │   ├── EmailVerificationTest.php
│   │   ├── PasswordConfirmationTest.php
│   │   ├── PasswordResetTest.php
│   │   ├── PasswordUpdateTest.php
│   │   └── TwoFactorAuthenticationTest.php
│   ├── Settings/
│   │   ├── ProfileUpdateTest.php
│   │   ├── PasswordUpdateTest.php
│   │   └── (outros settings tests)
│   └── Actions/Parse/
│       └── (fixtures para testes de parser SQL/migration)
└── Unit/
    └── (testes unitários de classes isoladas)
```

**Total:** 26 arquivos de teste identificados

## Padrões de teste

### Testes de Feature (HTTP/Livewire)

**Abordagem:** Testes de integração completos que exercitam o stack inteiro
**Localização:** `tests/Feature/`
**Padrão observado:**
```php
// Pest syntax com helpers de Laravel
test('users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticated();
});
```

**Auth tests cobrem:**
- Registro com criação automática de equipe
- Login com email/senha
- Verificação de email obrigatória
- Reset de senha
- Autenticação de dois fatores (2FA)
- Confirmação de senha para ações sensíveis

### Testes de Livewire

**Abordagem:** Testa componentes Livewire via `Livewire::test()`
**Localização:** `tests/Feature/Settings/`
**Padrão observado:**
```php
test('team can be created', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(CreateTeam::class)
        ->set('name', 'My Team')
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

# Análise estática
./vendor/bin/phpstan analyse
```

**Configuração:** SQLite in-memory para isolamento total entre testes — sem estado persistente

## Cobertura

**Atual:** Foco em Auth flows e Settings — cobertura de Actions/Diagram/Billing limitada nos testes identificados
**Metas:** Não documentadas explicitamente
