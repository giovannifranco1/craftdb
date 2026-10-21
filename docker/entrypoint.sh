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
        APP_KEY="$(php -r 'echo "base64:".base64_encode(random_bytes(32));')"
        (umask 077; printf '%s' "$APP_KEY" > "$KEY_FILE")
        log "APP_KEY gerada e salva em $KEY_FILE"
    fi
    export APP_KEY
fi

# 2. Espera o MySQL aceitar conexões (DOCK-02)
log "Aguardando MySQL em ${DB_HOST:-?}:${DB_PORT:-3306}..."
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

# 3. Cache de config (depende do APP_KEY exportado; event e view são cacheados no build).
# Sem route:cache: routes/ai.php é carregado duas vezes (bootstrap/app.php `api:` +
# McpServiceProvider), duplicando nomes de rota. Ver .specs/project/STATE.md.
php artisan config:cache --no-ansi

# 4. Migrations (DOCK-03)
php artisan migrate --force --no-ansi

# 5. Usuário local + banner com credenciais (DOCK-06/07)
php artisan craftdb:local-user --no-ansi

exec "$@"
