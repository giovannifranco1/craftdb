# F1 — Docker + MySQL + boot automático — Tasks

**Design**: `.specs/features/2026-10-09-docker-local-boot/design.md`
**Status**: Done (2026-10-21)

**Comandos de gate**
- **quick**: `./vendor/bin/pest <arquivo>` (SQLite in-memory)
- **full**: `composer test`
- **build**: `docker compose build app`
- **smoke**: `bash docker/smoke-test.sh` (sobe o stack real, ver T7)

---

## Execution Plan

### Phase 1: Fundação PHP + imagem (Parallel OK)

```
T1 [P] ─┐
T2 [P] ─┼──→ T3
T4 [P]  │
```

### Phase 2: Boot e stack (Sequential)

```
T3 ─┐
T4 ─┴──→ T5 ──→ T6
```

### Phase 3: Verificação e docs (Sequential)

```
T1, T6 ──→ T7 ──→ T8
```

---

## Task Breakdown

### T1: Flag `APP_FORCE_HTTPS` + `config/craftdb.php` [P]

**What**: Criar `config/craftdb.php` (usuário local + `force_https`) e condicionar `URL::forceHttps()` à flag.
**Where**: `config/craftdb.php` (novo), `app/Providers/AppServiceProvider.php:63-66` (modify)
**Depends on**: None
**Reuses**: `AppServiceProvider::configureUrls()` existente
**Refs**: DOCK-01, DOCK-10 (config consumida por T3)

**Tools**: MCP: NONE · Skill: NONE

**Steps**:
- [ ] Criar `config/craftdb.php`:

```php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Usuário local (CraftDB Local)
    |--------------------------------------------------------------------------
    |
    | Criado no boot do container pelo comando `craftdb:local-user`.
    |
    */

    'local_user' => [
        'email' => env('CRAFTDB_USER_EMAIL', 'local@craftdb.local'),
        'password' => env('CRAFTDB_USER_PASSWORD', 'craftdb'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Forçar HTTPS
    |--------------------------------------------------------------------------
    |
    | Fora de local/testing as URLs são forçadas para HTTPS. O docker-compose
    | local seta false, já que o app roda em http://localhost.
    |
    */

    'force_https' => (bool) env('APP_FORCE_HTTPS', true),

];
```

- [ ] Em `app/Providers/AppServiceProvider.php`, trocar:

```php
        // Force HTTPS in production and staging (homolog) environments
        if (! $app->environment(['local', 'testing'])) {
            $this->configureUrls();
        }
```

por:

```php
        // Force HTTPS in production and staging (homolog) environments, unless disabled (CraftDB Local)
        if (! $app->environment(['local', 'testing']) && config('craftdb.force_https')) {
            $this->configureUrls();
        }
```

- [ ] Rodar `composer test` — suite continua verde (nenhum teste depende do default)

**Done when**:
- [ ] `php artisan tinker --execute="var_dump(config('craftdb.force_https'));"` → `bool(true)`
- [ ] `APP_FORCE_HTTPS=false php artisan tinker --execute="var_dump(config('craftdb.force_https'));"` → `bool(false)`
- [ ] Gate full passa: `composer test` (mesma contagem de testes do baseline)

**Tests**: none (config; comportamento HTTP verificado no smoke T7 — DOCK-01 checa URLs `http://` no HTML)
**Gate**: full

---

### T2: Action `EnsureLocalUser` (TDD) [P]

**What**: Action idempotente que garante usuário local verificado e com workspace.
**Where**: `app/Actions/Local/EnsureLocalUser.php` (novo), `tests/Feature/Actions/Local/EnsureLocalUserTest.php` (novo)
**Depends on**: None
**Reuses**: `App\Actions\Team\CreateNewTeam`, cast `password => hashed` de `App\Models\User`
**Refs**: DOCK-06, DOCK-08, DOCK-10

**Tools**: MCP: NONE · Skill: `nano-disciplines:tdd`

**Steps**:
- [ ] RED — criar `tests/Feature/Actions/Local/EnsureLocalUserTest.php`:

```php
<?php

namespace Tests\Feature\Actions\Local;

use App\Actions\Local\EnsureLocalUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

uses(RefreshDatabase::class);

/**
 * @property EnsureLocalUser $action
 */
beforeEach(function (): void {
    /** @var TestCase $this */
    $this->action = app(EnsureLocalUser::class);
});

describe('EnsureLocalUser', function (): void {
    it('DOCK06: creates a verified local user with a workspace', function () {
        /** @var TestCase $this */
        $result = $this->action->exec('local@craftdb.local', 'craftdb');

        $user = $result['user']->fresh();

        expect($result['created'])->toBeTrue()
            ->and($user->email)->toBe('local@craftdb.local')
            ->and($user->email_verified_at)->not->toBeNull()
            ->and(Hash::check('craftdb', $user->password))->toBeTrue()
            ->and($user->ownedTeams()->count())->toBe(1)
            ->and($user->ownedTeams()->first()->name)->toBe('Local Workspace');
    });

    it('DOCK08: running twice does not duplicate the user nor change the password', function () {
        /** @var TestCase $this */
        $this->action->exec('local@craftdb.local', 'craftdb');
        $result = $this->action->exec('local@craftdb.local', 'outra-senha');

        $user = $result['user']->fresh();

        expect($result['created'])->toBeFalse()
            ->and(User::where('email', 'local@craftdb.local')->count())->toBe(1)
            ->and(Hash::check('craftdb', $user->password))->toBeTrue()
            ->and($user->ownedTeams()->count())->toBe(1);
    });

    it('DOCK10: uses the given email and password', function () {
        /** @var TestCase $this */
        $result = $this->action->exec('dev@example.com', 'segredo123');

        expect($result['user']->email)->toBe('dev@example.com')
            ->and(Hash::check('segredo123', $result['user']->fresh()->password))->toBeTrue();
    });

    it('DOCK06: reuses an existing unverified user without team, verifying it and creating the workspace', function () {
        /** @var TestCase $this */
        $existing = User::factory()->unverified()->create(['email' => 'existing@example.com']);

        $result = $this->action->exec('existing@example.com', 'craftdb');

        $user = $result['user']->fresh();

        expect($result['created'])->toBeFalse()
            ->and($user->id)->toBe($existing->id)
            ->and($user->email_verified_at)->not->toBeNull()
            ->and($user->ownedTeams()->count())->toBe(1);
    });

    it('DOCK06: reuses the super root when the email matches, keeping its single team', function () {
        /** @var TestCase $this */
        $superRoot = User::findOrFail(1);

        $result = $this->action->exec($superRoot->email, 'craftdb');

        $user = $result['user']->fresh();

        expect($result['created'])->toBeFalse()
            ->and($user->id)->toBe(1)
            ->and($user->email_verified_at)->not->toBeNull()
            ->and($user->ownedTeams()->count())->toBe(1);
    });
});
```

- [ ] Rodar `./vendor/bin/pest tests/Feature/Actions/Local/EnsureLocalUserTest.php` → FAIL (`Class "App\Actions\Local\EnsureLocalUser" not found`)
- [ ] GREEN — criar `app/Actions/Local/EnsureLocalUser.php`:

```php
<?php

namespace App\Actions\Local;

use App\Actions\Team\CreateNewTeam;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EnsureLocalUser
{
    public function __construct(private CreateNewTeam $createNewTeam) {}

    /**
     * Ensure the local user exists, is verified and owns a workspace.
     * Never changes the password of an existing user.
     *
     * @return array{user: User, created: bool}
     */
    public function exec(string $email, string $password): array
    {
        return DB::transaction(function () use ($email, $password): array {
            $user = User::where('email', $email)->first();
            $created = $user === null;

            $user ??= User::create([
                'name' => 'Local',
                'email' => $email,
                'password' => $password,
                'email_verified_at' => now(),
            ]);

            if ($user->email_verified_at === null) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }

            if (! $user->ownedTeams()->exists()) {
                $this->createNewTeam->exec([
                    'name' => 'Local Workspace',
                    'user_id' => $user->id,
                ]);
            }

            return ['user' => $user, 'created' => $created];
        });
    }
}
```

- [ ] Rodar o teste → PASS (5 testes)

**Done when**:
- [ ] Gate quick passa: `./vendor/bin/pest tests/Feature/Actions/Local/EnsureLocalUserTest.php` → 5 passed
- [ ] `./vendor/bin/phpstan analyse app/Actions/Local` sem erros

**Tests**: integration (Pest Feature + RefreshDatabase)
**Gate**: quick

---

### T3: Comando `craftdb:local-user` (TDD)

**What**: Comando Artisan que lê `config('craftdb.local_user')`, chama `EnsureLocalUser` e imprime URL + credenciais.
**Where**: `app/Console/Commands/EnsureLocalUserCommand.php` (novo), `tests/Feature/Console/EnsureLocalUserCommandTest.php` (novo)
**Depends on**: T1, T2
**Reuses**: `EnsureLocalUser` (T2), `config/craftdb.php` (T1), padrão de `app/Console/Commands/CleanupAcceptedInvitations.php`
**Refs**: DOCK-07, DOCK-10

**Tools**: MCP: NONE · Skill: `nano-disciplines:tdd`

**Steps**:
- [ ] RED — criar `tests/Feature/Console/EnsureLocalUserCommandTest.php`:

```php
<?php

namespace Tests\Feature\Console;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

describe('craftdb:local-user', function (): void {
    it('DOCK07: prints url, email and password on first run', function () {
        /** @var TestCase $this */
        config([
            'app.url' => 'http://localhost:8080',
            'craftdb.local_user.email' => 'local@craftdb.local',
            'craftdb.local_user.password' => 'craftdb',
        ]);

        $this->artisan('craftdb:local-user')
            ->expectsOutputToContain('http://localhost:8080')
            ->expectsOutputToContain('local@craftdb.local')
            ->expectsOutputToContain('craftdb')
            ->assertSuccessful();

        expect(User::where('email', 'local@craftdb.local')->exists())->toBeTrue();
    });

    it('DOCK07: does not print the password when the user already exists', function () {
        /** @var TestCase $this */
        config([
            'craftdb.local_user.email' => 'local@craftdb.local',
            'craftdb.local_user.password' => 'senha-secreta-123',
        ]);

        $this->artisan('craftdb:local-user')->assertSuccessful();

        $this->artisan('craftdb:local-user')
            ->expectsOutputToContain('local@craftdb.local')
            ->doesntExpectOutputToContain('senha-secreta-123')
            ->assertSuccessful();
    });

    it('DOCK10: uses email and password from config', function () {
        /** @var TestCase $this */
        config([
            'craftdb.local_user.email' => 'dev@example.com',
            'craftdb.local_user.password' => 'segredo123',
        ]);

        $this->artisan('craftdb:local-user')->assertSuccessful();

        expect(User::where('email', 'dev@example.com')->exists())->toBeTrue();
    });
});
```

- [ ] Rodar `./vendor/bin/pest tests/Feature/Console/EnsureLocalUserCommandTest.php` → FAIL (`The command "craftdb:local-user" does not exist`)
- [ ] GREEN — criar `app/Console/Commands/EnsureLocalUserCommand.php`:

```php
<?php

namespace App\Console\Commands;

use App\Actions\Local\EnsureLocalUser;
use Illuminate\Console\Command;

class EnsureLocalUserCommand extends Command
{
    protected $signature = 'craftdb:local-user';

    protected $description = 'Ensure the CraftDB Local user exists and print its credentials';

    public function handle(EnsureLocalUser $ensureLocalUser): int
    {
        $email = (string) config('craftdb.local_user.email');
        $password = (string) config('craftdb.local_user.password');

        $result = $ensureLocalUser->exec($email, $password);

        $this->newLine();
        $this->info('CraftDB Local está pronto');
        $this->line('  URL:    '.config('app.url'));
        $this->line('  E-mail: '.$email);
        $this->line('  Senha:  '.($result['created'] ? $password : '(inalterada — definida no primeiro boot)'));
        $this->newLine();

        return self::SUCCESS;
    }
}
```

- [ ] Comando é descoberto automaticamente (Laravel carrega `app/Console/Commands`; `bootstrap/app.php` só registra `routes/console.php` adicionalmente) — confirmar com `php artisan list | grep craftdb`
- [ ] Rodar o teste → PASS (3 testes)

**Done when**:
- [ ] Gate quick passa: `./vendor/bin/pest tests/Feature/Console/EnsureLocalUserCommandTest.php` → 3 passed
- [ ] `php artisan list | grep craftdb:local-user` mostra o comando

**Tests**: integration (Pest Feature + RefreshDatabase)
**Gate**: quick

---

### T4: `.dockerignore` + `Dockerfile` multi-stage [P]

**What**: Imagem de produção FrankenPHP com vendor `--no-dev` e assets Vite compilados, sem Node no runtime.
**Where**: `.dockerignore` (novo), `Dockerfile` (novo)
**Depends on**: None
**Reuses**: sequência de `composer setup` (`composer.json`)
**Refs**: DOCK-04

**Tools**: MCP: NONE · Skill: NONE

**Steps**:
- [ ] Criar `.dockerignore`:

```
.git
.github
.cursor
.idea
.vscode
.specs
.env
.env.*
!.env.example
node_modules
vendor
public/build
public/hot
storage/logs/*
storage/framework/cache/*
storage/framework/sessions/*
storage/framework/views/*
storage/*.key
bootstrap/cache/*.php
database/*.sqlite
tests
_ide_helper.php
docker-compose.yml
Dockerfile
```

- [ ] Criar `Dockerfile`:

```dockerfile
# syntax=docker/dockerfile:1

ARG PHP_IMAGE=dunglas/frankenphp:1-php8.4-bookworm

# --- vendor: dependências PHP de produção ---
FROM ${PHP_IMAGE} AS vendor
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN install-php-extensions intl zip bcmath gd pcntl
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

# --- assets: build do Vite (precisa do CSS do Flux em vendor/) ---
FROM node:22-bookworm-slim AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY vite.config.js tailwind.config.js ./
COPY resources ./resources
COPY app ./app
COPY --from=vendor /app/vendor/livewire/flux ./vendor/livewire/flux
COPY --from=vendor /app/vendor/laravel/framework/src/Illuminate/Pagination/resources/views ./vendor/laravel/framework/src/Illuminate/Pagination/resources/views
RUN npm run build

# --- app: runtime ---
FROM ${PHP_IMAGE} AS app
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN install-php-extensions pdo_mysql intl zip bcmath gd pcntl opcache
WORKDIR /app

COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

RUN composer dump-autoload --no-dev --optimize --classmap-authoritative \
    && php artisan package:discover --ansi \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache /data \
    && chown -R www-data:www-data storage bootstrap/cache /data \
    && chmod +x docker/entrypoint.sh \
    && rm /usr/bin/composer

ENV SERVER_NAME=:80 \
    APP_ENV=production \
    APP_DEBUG=false

EXPOSE 80
ENTRYPOINT ["docker/entrypoint.sh"]
CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]
```

- [ ] Confirmar o path do Caddyfile na imagem: `docker run --rm dunglas/frankenphp:1-php8.4-bookworm ls /etc/frankenphp /etc/caddy 2>&1`; ajustar o `CMD` se o arquivo estiver em `/etc/caddy/Caddyfile`
- [ ] Confirmar o document root padrão da imagem (`/app/public`) no Caddyfile listado acima
- [ ] Até T5 existir, criar `docker/entrypoint.sh` temporário com `#!/bin/sh` + `exec "$@"` para o build passar (T5 substitui)
- [ ] Build: `docker build -t craftdb-local:test .`
- [ ] Se `npm ci` falhar por `optionalDependencies` `linux-x64-gnu` em host arm64: aplicar `nano-disciplines:debug`; correção esperada é remover as entradas fixadas de `optionalDependencies` em `package.json` e regenerar `package-lock.json` (registrar no STATE.md)

**Done when**:
- [ ] `docker build -t craftdb-local:test .` termina com sucesso
- [ ] `docker run --rm --entrypoint sh craftdb-local:test -c 'test -f public/build/manifest.json && echo OK'` → `OK`
- [ ] `docker run --rm --entrypoint sh craftdb-local:test -c 'command -v node || echo NO_NODE'` → `NO_NODE`
- [ ] `docker run --rm --entrypoint php craftdb-local:test -m | grep -E 'pdo_mysql|intl'` → ambos listados

**Tests**: none (infra; gate build)
**Gate**: build

---

### T5: `docker/entrypoint.sh`

**What**: Entrypoint idempotente: resolve `APP_KEY`, espera MySQL, `optimize`, `migrate --force`, `craftdb:local-user`, `exec`.
**Where**: `docker/entrypoint.sh` (novo, executável)
**Depends on**: T3, T4
**Reuses**: comando `craftdb:local-user` (T3)
**Refs**: DOCK-02, DOCK-03, DOCK-05, DOCK-07, DOCK-12

**Tools**: MCP: NONE · Skill: NONE

**Steps**:
- [ ] Criar `docker/entrypoint.sh`:

```bash
#!/usr/bin/env bash
set -euo pipefail

KEY_FILE=/data/app.key

log() { echo "[craftdb] $*" >&2; }

# 1. APP_KEY: env > volume > gera nova (DOCK-12)
if [ -z "${APP_KEY:-}" ]; then
    if [ -s "$KEY_FILE" ]; then
        APP_KEY="$(cat "$KEY_FILE")"
        log "APP_KEY carregada de $KEY_FILE"
    else
        APP_KEY="$(php artisan key:generate --show --no-ansi)"
        (umask 077; printf '%s' "$APP_KEY" > "$KEY_FILE")
        log "APP_KEY gerada e salva em $KEY_FILE"
    fi
    export APP_KEY
fi

# 2. Espera o MySQL aceitar conexões (DOCK-02)
log "Aguardando MySQL em ${DB_HOST}:${DB_PORT:-3306}..."
php -r '
$deadline = time() + (int) (getenv("CRAFTDB_DB_WAIT") ?: 60);
$dsn = sprintf("mysql:host=%s;port=%s;dbname=%s", getenv("DB_HOST"), getenv("DB_PORT") ?: "3306", getenv("DB_DATABASE"));
while (true) {
    try {
        new PDO($dsn, getenv("DB_USERNAME"), getenv("DB_PASSWORD"));
        exit(0);
    } catch (PDOException $e) {
        if (time() >= $deadline) {
            fwrite(STDERR, "[craftdb] MySQL indisponível: {$e->getMessage()}\n");
            exit(1);
        }
        sleep(2);
    }
}
'

# 3. Caches de produção (config depende do APP_KEY exportado)
# Sem route:cache: a rota mcp.oauth.protected-resource é registrada duas vezes
# (routes/ai.php + app/Mcp/Registrar.php) e o cache falha. Revisitar na F6.
php artisan config:cache --no-ansi
php artisan event:cache --no-ansi
php artisan view:cache --no-ansi

# 4. Migrations (DOCK-03)
php artisan migrate --force --no-ansi

# 5. Usuário local + banner com credenciais (DOCK-06/07)
php artisan craftdb:local-user --no-ansi

exec "$@"
```

- [ ] `chmod +x docker/entrypoint.sh`
- [ ] `bash -n docker/entrypoint.sh` → sem saída (sintaxe ok)
- [ ] Rebuild: `docker build -t craftdb-local:test .`

**Done when**:
- [ ] `bash -n docker/entrypoint.sh` sem erro
- [ ] `docker build -t craftdb-local:test .` passa
- [ ] Comportamento end-to-end verificado em T7 (DOCK-02/03/05/12)

**Tests**: none (shell de infra; verificado pelo smoke T7)
**Gate**: build

---

### T6: `docker-compose.yml`

**What**: Stack `app` + `mysql` com volumes, portas em `127.0.0.1`, healthchecks e restart limitado.
**Where**: `docker-compose.yml` (novo)
**Depends on**: T5
**Reuses**: `Dockerfile` (T4), rota `/up` (`bootstrap/app.php`)
**Refs**: DOCK-01, DOCK-05, DOCK-11, DOCK-13, DOCK-14, DOCK-15, DOCK-16, DOCK-18

**Tools**: MCP: NONE · Skill: NONE

**Steps**:
- [ ] Criar `docker-compose.yml`:

```yaml
name: craftdb

services:
  mysql:
    image: mysql:8.4
    environment:
      MYSQL_DATABASE: craftdb
      MYSQL_USER: craftdb
      MYSQL_PASSWORD: ${CRAFTDB_DB_PASSWORD:-craftdb}
      MYSQL_RANDOM_ROOT_PASSWORD: "yes"
    volumes:
      - craftdb-mysql:/var/lib/mysql
    ports:
      - "127.0.0.1:${CRAFTDB_DB_PORT:-3307}:3306"
    healthcheck:
      test: ["CMD-SHELL", "mysqladmin ping -h 127.0.0.1 -u craftdb -p\"$$MYSQL_PASSWORD\" --silent"]
      interval: 5s
      timeout: 5s
      retries: 30
      start_period: 20s
    restart: unless-stopped

  app:
    build: .
    image: craftdb-local:latest
    depends_on:
      mysql:
        condition: service_healthy
    environment:
      APP_NAME: CraftDB
      APP_ENV: production
      APP_DEBUG: "false"
      # CRAFTDB_APP_KEY (não APP_KEY): o compose lê o .env da raiz, que é o .env de dev do Laravel
      APP_KEY: ${CRAFTDB_APP_KEY:-}
      APP_URL: http://localhost:${CRAFTDB_PORT:-8080}
      APP_FORCE_HTTPS: "false"
      DB_CONNECTION: mysql
      DB_HOST: mysql
      DB_PORT: "3306"
      DB_DATABASE: craftdb
      DB_USERNAME: craftdb
      DB_PASSWORD: ${CRAFTDB_DB_PASSWORD:-craftdb}
      LOG_CHANNEL: stderr
      LOG_LEVEL: info
      SESSION_DRIVER: database
      CACHE_STORE: database
      QUEUE_CONNECTION: sync
      MAIL_MAILER: log
      NIGHTWATCH_ENABLED: "false"
      CRAFTDB_USER_EMAIL: ${CRAFTDB_USER_EMAIL:-local@craftdb.local}
      CRAFTDB_USER_PASSWORD: ${CRAFTDB_USER_PASSWORD:-craftdb}
    volumes:
      - craftdb-data:/data
    ports:
      - "127.0.0.1:${CRAFTDB_PORT:-8080}:80"
    healthcheck:
      test: ["CMD", "php", "-r", "exit(@file_get_contents('http://127.0.0.1/up') === false ? 1 : 0);"]
      interval: 10s
      timeout: 5s
      retries: 5
      start_period: 30s
    restart: on-failure:3

volumes:
  craftdb-mysql:
  craftdb-data:
```

- [ ] Nota: a imagem FrankenPHP usa `XDG_DATA_HOME=/data`, então o volume `craftdb-data` também guarda `/data/caddy` (inofensivo, sem HTTPS)
- [ ] `docker compose config --quiet` → sem saída (YAML válido)
- [ ] `docker compose config | grep 'APP_KEY: ""'` → presente mesmo com o `.env` de dev na raiz (prova que o `APP_KEY` de dev não vaza)

**Done when**:
- [ ] `docker compose config --quiet` sem erro
- [ ] `docker compose config | grep -E 'host_ip: 127.0.0.1'` → 2 ocorrências
- [ ] Comportamento end-to-end verificado em T7

**Tests**: none (infra; verificado pelo smoke T7)
**Gate**: build

---

### T7: `docker/smoke-test.sh` + rodar o stack real

**What**: Script de verificação end-to-end dos critérios de infra; rodar e corrigir o que falhar (inclui incompatibilidades de migration em MySQL).
**Where**: `docker/smoke-test.sh` (novo, executável); correções pontuais em `database/migrations/*` se necessário
**Depends on**: T1, T6
**Reuses**: —
**Refs**: DOCK-01, DOCK-02, DOCK-03, DOCK-05, DOCK-07, DOCK-09, DOCK-11, DOCK-12, DOCK-13, DOCK-14, DOCK-15, DOCK-16, DOCK-18

**Tools**: MCP: `chrome-devtools` (DOCK-09: login + criar diagrama no navegador) · Skill: `nano-disciplines:debug` (se migration falhar)

**Steps**:
- [ ] Criar `docker/smoke-test.sh`:

```bash
#!/usr/bin/env bash
# Smoke test do CraftDB Local. ATENÇÃO: roda `docker compose down -v` (apaga os dados do stack).
set -euo pipefail

PORT="${CRAFTDB_PORT:-8080}"
DB_PORT="${CRAFTDB_DB_PORT:-3307}"
URL="http://localhost:${PORT}"

pass() { echo "✅ $*"; }
fail() { echo "❌ $*"; exit 1; }

wait_healthy() {
    for _ in $(seq 1 60); do
        status="$(docker inspect -f '{{.State.Health.Status}}' "$(docker compose ps -q app)" 2>/dev/null || true)"
        [ "$status" = "healthy" ] && return 0
        sleep 3
    done
    docker compose logs app | tail -50
    fail "app não ficou healthy"
}

echo "== Primeiro boot (volumes zerados) =="
docker compose down -v --remove-orphans >/dev/null 2>&1 || true
docker compose up -d --build
wait_healthy
pass "DOCK-18 app healthy"

code="$(curl -s -o /dev/null -w '%{http_code}' "$URL/login")"
[ "$code" = "200" ] && pass "DOCK-01 /login 200" || fail "DOCK-01 /login retornou $code"

html="$(curl -s "$URL/login")"
grep -q "http://localhost:${PORT}/build/" <<<"$html" \
    && pass "DOCK-01 assets em http:// (APP_FORCE_HTTPS=false)" \
    || fail "DOCK-01 assets não usam http://localhost:${PORT}/build/"

logs="$(docker compose logs app)"
grep -q "local@craftdb.local" <<<"$logs" && grep -q "Senha:  craftdb" <<<"$logs" \
    && pass "DOCK-07 credenciais no log" || fail "DOCK-07 credenciais ausentes no log"

status_out="$(docker compose exec -T app php artisan migrate:status --no-ansi)"
grep -q "Pending" <<<"$status_out" \
    && fail "DOCK-03 migrations pendentes" || pass "DOCK-03 migrations aplicadas"

ports="$(docker compose ps --format '{{.Ports}}')"
grep -qE '0\.0\.0\.0|\[::\]' <<<"$ports" \
    && fail "DOCK-15 porta exposta fora de 127.0.0.1" || pass "DOCK-15 portas só em 127.0.0.1"

nc -z 127.0.0.1 "$DB_PORT" && pass "DOCK-14 porta MySQL aberta em 127.0.0.1:${DB_PORT}" || fail "DOCK-14 porta ${DB_PORT} fechada"
tables="$(docker compose exec -T mysql mysql -ucraftdb -p"${CRAFTDB_DB_PASSWORD:-craftdb}" craftdb -e 'SHOW TABLES' 2>/dev/null)"
grep -q diagram <<<"$tables" && pass "DOCK-14 credenciais craftdb funcionam" || fail "DOCK-14 login MySQL falhou"

key_before="$(docker compose exec -T app cat /data/app.key)"
docker compose exec -T app php artisan tinker --execute="App\Models\Diagram::forceCreate(['name' => 'smoke', 'slug' => 'smoke', 'style' => 'neutral', 'team_id' => App\Models\Team\Team::first()->id]);" >/dev/null

echo "== Restart (down + up) =="
docker compose down
docker compose up -d
wait_healthy
key_after="$(docker compose exec -T app cat /data/app.key)"
[ "$key_before" = "$key_after" ] && pass "DOCK-12 APP_KEY persistida" || fail "DOCK-12 APP_KEY mudou"

count="$(docker compose exec -T app php artisan tinker --execute="echo App\Models\Diagram::where('name','smoke')->count();")"
[ "$(echo "$count" | tr -dc '0-9')" = "1" ] && pass "DOCK-11 dados persistem" || fail "DOCK-11 diagrama sumiu"

users="$(docker compose exec -T app php artisan tinker --execute="echo App\Models\User::where('email','local@craftdb.local')->count();")"
[ "$(echo "$users" | tr -dc '0-9')" = "1" ] && pass "DOCK-08 usuário não duplicado" || fail "DOCK-08 usuário duplicado"

echo "== Reset (down -v) =="
docker compose down -v
docker compose up -d
wait_healthy
count="$(docker compose exec -T app php artisan tinker --execute="echo App\Models\Diagram::count();")"
[ "$(echo "$count" | tr -dc '0-9')" = "0" ] && pass "DOCK-13 reset volta ao primeiro boot" || fail "DOCK-13 dados sobreviveram ao down -v"

echo "Smoke test OK"
```

- [ ] Colunas obrigatórias de `diagram` (conferidas em `database/migrations/2025_10_21_204314_create_diagram_table.php`): `team_id`, `name`, `slug`, `style` (`neutral|colorful`) — já no `forceCreate` do script
- [ ] `chmod +x docker/smoke-test.sh` e rodar `bash docker/smoke-test.sh`
- [ ] Para cada ❌: aplicar `nano-disciplines:debug` (root cause antes do fix); correções de migration MySQL vão como commit separado `fix(database): ...`
- [ ] DOCK-05 manual: `docker compose run --rm -e DB_PASSWORD=errada -e CRAFTDB_DB_WAIT=6 app true; echo "exit=$?"` → `exit=1` e mensagem `[craftdb] MySQL indisponível` no output
- [ ] DOCK-16 manual: `CRAFTDB_PORT=8090 docker compose up -d` → `curl -s -o /dev/null -w '%{http_code}' http://localhost:8090/login` → `200` e o log mostra `URL:    http://localhost:8090`; voltar com `docker compose up -d`
- [ ] DOCK-09 manual (chrome-devtools MCP): abrir `http://localhost:8080/login`, entrar com `local@craftdb.local` / `craftdb`, criar um diagrama; screenshot como evidência

**Done when**:
- [ ] `bash docker/smoke-test.sh` termina com `Smoke test OK`
- [ ] DOCK-05, DOCK-09 e DOCK-16 verificados manualmente com a evidência acima
- [ ] Gate full continua verde: `composer test`

**Tests**: none (smoke de infra é o próprio teste; suite Pest não sobe Docker)
**Gate**: smoke + full

---

### T8: Documentação "Rodando com Docker"

**What**: Seção no README em PT e EN.
**Where**: `docs/README.md`, `docs/README.en.md` (modify)
**Depends on**: T7
**Reuses**: estrutura atual dos READMEs
**Refs**: DOCK-17

**Tools**: MCP: NONE · Skill: NONE

**Steps**:
- [ ] Adicionar em `docs/README.md` a seção (EN equivalente em `docs/README.en.md`, título "Running with Docker"):

````markdown
## Rodando com Docker

Requisito: Docker com Compose v2.

```bash
docker compose up -d          # sobe app + MySQL (primeira vez faz o build)
docker compose logs app       # mostra URL e credenciais do usuário local
docker compose down           # para (dados continuam)
docker compose down -v        # reset completo (apaga banco e chave)
```

Acesse http://localhost:8080 e entre com `local@craftdb.local` / `craftdb`.

### Banco de dados

O MySQL fica em `127.0.0.1:3307` — banco `craftdb`, usuário `craftdb`, senha `craftdb`.

### Variáveis

Defina antes do primeiro `up` (num arquivo `.env` ao lado do `docker-compose.yml` ou no shell):

| Variável | Padrão | Para quê |
|---|---|---|
| `CRAFTDB_PORT` | `8080` | Porta do app |
| `CRAFTDB_DB_PORT` | `3307` | Porta do MySQL |
| `CRAFTDB_DB_PASSWORD` | `craftdb` | Senha do MySQL (só no primeiro boot) |
| `CRAFTDB_USER_EMAIL` | `local@craftdb.local` | E-mail do usuário local (só no primeiro boot) |
| `CRAFTDB_USER_PASSWORD` | `craftdb` | Senha do usuário local (só no primeiro boot) |
| `CRAFTDB_APP_KEY` | gerada e salva no volume | Chave da aplicação |

Se a porta 8080 ou 3307 já estiver em uso, troque com `CRAFTDB_PORT=8090 docker compose up -d`.

Tudo fica publicado só em `127.0.0.1` — nada é exposto na sua rede.
````

**Done when**:
- [ ] Seção presente nos dois arquivos com: subir, parar, logs/credenciais, reset, acesso ao MySQL, variáveis (DOCK-17)
- [ ] Comandos da seção conferidos contra o `docker-compose.yml` real (portas, nomes de variáveis)

**Tests**: none (docs)
**Gate**: —

---

## Parallel Execution Map

```
Phase 1:
  ├── T1 [P]
  ├── T2 [P]
  └── T4 [P]
  T1 + T2 ──→ T3

Phase 2:
  T3 + T4 ──→ T5 ──→ T6

Phase 3:
  T1 + T6 ──→ T7 ──→ T8
```

T2 e T3 usam SQLite in-memory por processo — parallel-safe. T4 não roda testes Pest.

---

## Validação pré-aprovação

### Task Granularity Check

| Task | Escopo | Status |
|---|---|---|
| T1 | 1 config + 1 condição | ✅ Granular (coeso) |
| T2 | 1 Action + teste | ✅ Granular |
| T3 | 1 comando + teste | ✅ Granular |
| T4 | Dockerfile + .dockerignore | ✅ Coeso (o ignore só existe para o build) |
| T5 | 1 script | ✅ Granular |
| T6 | 1 arquivo | ✅ Granular |
| T7 | 1 script + correções que ele revelar | ⚠️ OK — correções de migration viram commits próprios |
| T8 | 2 arquivos de doc espelhados | ✅ Coeso |

### Diagram-Definition Cross-Check

| Task | Depends On (corpo) | Diagrama mostra | Status |
|---|---|---|---|
| T1 | None | — | ✅ |
| T2 | None | — | ✅ |
| T3 | T1, T2 | T1 + T2 → T3 | ✅ |
| T4 | None | — | ✅ |
| T5 | T3, T4 | T3 + T4 → T5 | ✅ |
| T6 | T5 | T5 → T6 | ✅ |
| T7 | T1, T6 | T1 + T6 → T7 | ✅ |
| T8 | T7 | T7 → T8 | ✅ |

### Test Co-location Validation

`TESTING.md` não tem matriz formal; regra aplicada: Actions/comandos (lógica PHP) → Pest Feature; infra/config/docs → none, com verificação por gate build/smoke.

| Task | Camada | Exige | Task diz | Status |
|---|---|---|---|---|
| T1 | config + provider | none (sem lógica nova testável sem subir env production) | none + smoke DOCK-01 | ✅ |
| T2 | Action | integration | integration | ✅ |
| T3 | Command | integration | integration | ✅ |
| T4 | infra | none | none (build) | ✅ |
| T5 | infra | none | none (build + smoke) | ✅ |
| T6 | infra | none | none (smoke) | ✅ |
| T7 | smoke | — | smoke | ✅ |
| T8 | docs | none | none | ✅ |

### Cobertura da spec

| ID | Tasks |
|---|---|
| DOCK-01 | T1, T6, T7 |
| DOCK-02 | T5, T7 |
| DOCK-03 | T5, T7 |
| DOCK-04 | T4 |
| DOCK-05 | T5, T6, T7 |
| DOCK-06 | T2 |
| DOCK-07 | T3, T5, T7 |
| DOCK-08 | T2, T7 |
| DOCK-09 | T7 |
| DOCK-10 | T1, T2, T3 |
| DOCK-11 | T6, T7 |
| DOCK-12 | T5, T7 |
| DOCK-13 | T6, T7 |
| DOCK-14 | T6, T7 |
| DOCK-15 | T6, T7 |
| DOCK-16 | T6, T7 |
| DOCK-17 | T8 |
| DOCK-18 | T6, T7 |

18/18 mapeados.
