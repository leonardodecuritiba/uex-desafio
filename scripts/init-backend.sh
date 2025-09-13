#!/usr/bin/env bash
set -euo pipefail

# Bootstrap Laravel app with Sanctum and base tooling

if [ -f ".bootstrap.done" ]; then
  echo "[backend] Already bootstrapped. Skipping."
  exit 0
fi

if [ ! -f "artisan" ]; then
  echo "[backend] Creating new Laravel app (handling non-empty dir)..."
  tmpdir="$(mktemp -d)"
  composer create-project laravel/laravel "$tmpdir"
  cp -a "$tmpdir"/. ./
  rm -rf "$tmpdir"
fi

echo "[backend] Ensuring env is configured for Docker & Postgres..."
cp -n .env.example .env || true
php -r "file_exists('.env') || copy('.env.example', '.env');"

echo "[backend] Updating .env for dockerized services..."
sed -i "s/^# DB_CONNECTION=.*/DB_CONNECTION=pgsql/" .env
sed -i "s/^# DB_HOST=.*/DB_HOST=db/" .env
sed -i "s/^# DB_PORT=.*/DB_PORT=${DB_PORT:-5432}/" .env
sed -i "s/^# DB_DATABASE=.*/DB_DATABASE=${DB_DATABASE:-uex}/" .env
sed -i "s/^# DB_USERNAME=.*/DB_USERNAME=${DB_USERNAME:-uex}/" .env
sed -i "s/^# DB_PASSWORD=.*/DB_PASSWORD=${DB_PASSWORD:-secret}/" .env

if ! grep -q '^SESSION_DOMAIN=' .env; then echo 'SESSION_DOMAIN=localhost' >> .env; fi
if ! grep -q '^SANCTUM_STATEFUL_DOMAINS=' .env; then echo 'SANCTUM_STATEFUL_DOMAINS=localhost,localhost:5173' >> .env; fi
if ! grep -q '^FRONTEND_URL=' .env; then echo 'FRONTEND_URL=http://localhost:5173' >> .env; fi

read -p "Deseja continuar a execução? (s/N) " confirm
case "$confirm" in
  [sS]|[sS][iI][mM])
    echo "➡️  Continuando..."
    ;;
  *)
    echo "❌ Execução abortada pelo usuário."
    exit 1
    ;;
esac

php artisan key:generate --force
php artisan config:clear

echo "[backend] Installing Sanctum and tooling..."
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider" --force
composer require --dev laravel/pint friendsofphp/php-cs-fixer phpunit/phpunit pestphp/pest pestphp/pest-plugin-laravel nunomaduro/collision

read -p "Deseja continuar a execução? (s/N) " confirm
case "$confirm" in
  [sS]|[sS][iI][mM])
    echo "➡️  Continuando..."
    ;;
  *)
    echo "❌ Execução abortada pelo usuário."
    exit 1
    ;;
esac

echo "[backend] Preparing Clean Architecture folders..."
mkdir -p app/Domain app/Application app/Infrastructure
mkdir -p app/Domain/Contacts/Entities app/Domain/Contacts/ValueObjects app/Domain/Contacts/Repositories
mkdir -p app/Application/Auth/UseCases app/Application/Contacts/UseCases
mkdir -p app/Infrastructure/Http/Controllers app/Infrastructure/Http/Requests app/Infrastructure/Http/Resources
mkdir -p app/Infrastructure/Persistence app/Infrastructure/Services

cat > app/Domain/README.md <<'MD'
# Domain Layer
- Entities, Value Objects and Ports (interfaces).
- Pure PHP: no framework dependencies.
MD

cat > app/Application/README.md <<'MD'
# Application Layer
- Use cases (command/query) orchestrating domain + ports.
MD

cat > app/Infrastructure/README.md <<'MD'
# Infrastructure Layer
- Adapters for HTTP, DB, and external services.
MD

echo "[backend] Running initial migrations (user, password reset, sanctum)"
php artisan migrate || true

touch .bootstrap.done
echo "[backend] Done."
