#!/usr/bin/env bash
# Instacertify ERP — one-click installer for Hostinger VPS (Ubuntu 24.04 + Docker)
# Usage: ./scripts/install.sh
# Optional: DOMAIN=instacertify.in EMAIL=nikhil@instacertify.com ./scripts/install.sh

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

DOMAIN="${DOMAIN:-instacertify.in}"
APP_URL="${APP_URL:-https://${DOMAIN}}"
COMPOSE="docker compose"

echo "==> Instacertify ERP installer"
echo "    Domain: ${DOMAIN}"
echo "    App URL: ${APP_URL}"

require_cmd() {
  if ! command -v "$1" >/dev/null 2>&1; then
    echo "ERROR: '$1' is required. Install Docker Engine 29.x and Compose v2 first."
    exit 1
  fi
}

require_cmd docker
$COMPOSE version >/dev/null

if [[ ! -f .env ]]; then
  echo "==> Creating .env from .env.example"
  cp .env.example .env

  # Generate secrets
  APP_KEY="base64:$(openssl rand -base64 32)"
  DB_PASSWORD="$(openssl rand -hex 16)"
  REDIS_PASSWORD="$(openssl rand -hex 16)"
  MINIO_ROOT_PASSWORD="$(openssl rand -hex 16)"
  GRAFANA_ADMIN_PASSWORD="$(openssl rand -hex 12)"

  sed -i "s|^APP_NAME=.*|APP_NAME=\"Instacertify ERP\"|" .env
  sed -i "s|^APP_ENV=.*|APP_ENV=production|" .env
  sed -i "s|^APP_DEBUG=.*|APP_DEBUG=false|" .env
  sed -i "s|^APP_URL=.*|APP_URL=${APP_URL}|" .env
  sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" .env

  sed -i "s|^DB_CONNECTION=.*|DB_CONNECTION=pgsql|" .env
  sed -i "s|^DB_HOST=.*|DB_HOST=postgres|" .env
  sed -i "s|^DB_PORT=.*|DB_PORT=5432|" .env
  sed -i "s|^DB_DATABASE=.*|DB_DATABASE=instacertify|" .env
  sed -i "s|^DB_USERNAME=.*|DB_USERNAME=instacertify|" .env
  sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=${DB_PASSWORD}|" .env

  sed -i "s|^REDIS_HOST=.*|REDIS_HOST=valkey|" .env
  sed -i "s|^REDIS_PASSWORD=.*|REDIS_PASSWORD=${REDIS_PASSWORD}|" .env
  sed -i "s|^CACHE_STORE=.*|CACHE_STORE=redis|" .env
  sed -i "s|^QUEUE_CONNECTION=.*|QUEUE_CONNECTION=redis|" .env
  sed -i "s|^SESSION_DRIVER=.*|SESSION_DRIVER=redis|" .env

  sed -i "s|^FILESYSTEM_DISK=.*|FILESYSTEM_DISK=s3|" .env
  sed -i "s|^AWS_ACCESS_KEY_ID=.*|AWS_ACCESS_KEY_ID=instacertify|" .env
  sed -i "s|^AWS_SECRET_ACCESS_KEY=.*|AWS_SECRET_ACCESS_KEY=${MINIO_ROOT_PASSWORD}|" .env
  sed -i "s|^AWS_DEFAULT_REGION=.*|AWS_DEFAULT_REGION=us-east-1|" .env
  sed -i "s|^AWS_BUCKET=.*|AWS_BUCKET=instacertify|" .env
  sed -i "s|^AWS_ENDPOINT=.*|AWS_ENDPOINT=http://minio:9000|" .env
  sed -i "s|^AWS_USE_PATH_STYLE_ENDPOINT=.*|AWS_USE_PATH_STYLE_ENDPOINT=true|" .env
  sed -i "s|^AWS_URL=.*|AWS_URL=|" .env

  sed -i "s|^MINIO_ROOT_USER=.*|MINIO_ROOT_USER=instacertify|" .env
  sed -i "s|^MINIO_ROOT_PASSWORD=.*|MINIO_ROOT_PASSWORD=${MINIO_ROOT_PASSWORD}|" .env
  sed -i "s|^GRAFANA_ADMIN_PASSWORD=.*|GRAFANA_ADMIN_PASSWORD=${GRAFANA_ADMIN_PASSWORD}|" .env
  sed -i "s|^ERP_DOMAIN=.*|ERP_DOMAIN=${DOMAIN}|" .env

  echo "==> Secrets written to .env (keep this file private)"
else
  echo "==> Using existing .env"
fi

mkdir -p docker/nginx/ssl storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache || true

echo "==> Building and starting containers"
$COMPOSE up -d --build postgres valkey minio minio-init
echo "==> Waiting for database..."
sleep 8
$COMPOSE up -d --build app nginx queue scheduler

echo "==> Installing PHP dependencies inside app container"
$COMPOSE exec -T -u root app bash -lc 'composer install --no-dev --optimize-autoloader --no-interaction'
$COMPOSE exec -T -u root app bash -lc 'chown -R www-data:www-data storage bootstrap/cache'

echo "==> Running migrations and seeders"
$COMPOSE exec -T app php artisan migrate --force
$COMPOSE exec -T app php artisan db:seed --force
$COMPOSE exec -T app php artisan storage:link || true
$COMPOSE exec -T app php artisan filament:assets || true
$COMPOSE exec -T app php artisan optimize || true

# Optional Fail2ban host install hint
if command -v fail2ban-client >/dev/null 2>&1; then
  echo "==> Installing Fail2ban jail for nginx (host)"
  if [[ -f docker/fail2ban/jail.local ]]; then
    sudo cp docker/fail2ban/jail.local /etc/fail2ban/jail.d/instacertify-erp.conf || true
    sudo systemctl reload fail2ban || true
  fi
fi

echo ""
echo "============================================================"
echo " Instacertify ERP is up"
echo " URL:      ${APP_URL}/admin"
echo " Login:    nikhil@instacertify.com"
echo " Password: Legal@123"
echo " Domain:   ${DOMAIN}"
echo "============================================================"
echo " Change the super-admin password after first login."
echo " Enable monitoring later with: docker compose --profile monitoring up -d"
echo ""
