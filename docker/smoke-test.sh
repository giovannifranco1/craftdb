#!/usr/bin/env bash
# Smoke test do CraftDB Local. ATENÇÃO: roda `docker compose down -v` (apaga os dados do stack).
set -euo pipefail

PORT="${CRAFTDB_PORT:-8080}"
DB_PORT="${CRAFTDB_DB_PORT:-3307}"
URL="http://localhost:${PORT}"

pass() { echo "✅ $*"; }
fail() { echo "❌ $*"; exit 1; }

tinker_count() {
    docker compose exec -T app php artisan tinker --execute="echo $1;" | tr -dc '0-9'
}

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

html="$(curl -s -w '\n%{http_code}' "$URL/dashboard")"
code="${html##*$'\n'}"
[ "$code" = "200" ] && pass "NOAUTH-05 /dashboard 200 sem cookie" || fail "NOAUTH-05 /dashboard retornou $code"

root="$(curl -s -o /dev/null -w '%{http_code} %{redirect_url}' "$URL/")"
[ "$root" = "302 http://localhost:${PORT}/dashboard" ] && pass "NOAUTH-04 / redireciona para /dashboard" || fail "NOAUTH-04 / retornou '$root'"

grep -q "http://localhost:${PORT}/build/" <<<"$html" \
    && pass "DOCK-01 assets em http:// (APP_FORCE_HTTPS=false)" \
    || fail "DOCK-01 assets não usam http://localhost:${PORT}/build/"

logs="$(docker compose logs app)"
grep -q "local@craftdb.local" <<<"$logs" && grep -q "URL:" <<<"$logs" && ! grep -q "Senha" <<<"$logs" \
    && pass "DOCK-07 usuário local e URL no log, sem senha" || fail "DOCK-07 log incorreto (usuário/URL ausentes ou senha presente)"

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

[ "$(tinker_count "App\Models\Diagram::where('name','smoke')->count()")" = "1" ] && pass "DOCK-11 dados persistem" || fail "DOCK-11 diagrama sumiu"

[ "$(tinker_count "App\Models\User::where('email','local@craftdb.local')->count()")" = "1" ] && pass "DOCK-08 usuário não duplicado" || fail "DOCK-08 usuário duplicado"

echo "== Reset (down -v) =="
docker compose down -v
docker compose up -d
wait_healthy
[ "$(tinker_count 'App\Models\Diagram::count()')" = "0" ] && pass "DOCK-13 reset volta ao primeiro boot" || fail "DOCK-13 dados sobreviveram ao down -v"

echo "Smoke test OK"
