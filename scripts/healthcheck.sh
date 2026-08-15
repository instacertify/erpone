#!/usr/bin/env bash
# Quick health check after install
set -euo pipefail
ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

docker compose ps
curl -fsS "${APP_URL:-http://localhost}/up" >/dev/null && echo "App health: OK" || echo "App health: check nginx /up"
docker compose exec -T app php artisan about || true
