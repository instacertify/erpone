# Hostinger one-click / VPS install guide

## Prerequisites

- Hostinger VPS with **Ubuntu 24.04 LTS**
- Docker Engine **29.x** and Docker Compose **v2**
- Domain DNS A record pointing to the VPS (`instacertify.in`)
- Ports 80/443 open

## Install

```bash
git clone <repo-url> /opt/instacertify-erp
cd /opt/instacertify-erp
chmod +x scripts/*.sh
DOMAIN=instacertify.in ./scripts/install.sh
```

The installer:

1. Creates a secure `.env` with generated secrets
2. Starts Postgres 18.4, Valkey 9.1.1, MinIO, PHP-FPM 8.5, Nginx 1.28, Horizon, scheduler
3. Runs migrations + seeds the super admin
4. Optionally configures Fail2ban if present on the host

## Login

- URL: `https://instacertify.in/admin`
- User: `nikhil@instacertify.com`
- Password: `Legal@123` (change immediately)

## TLS

Place certificates in `docker/nginx/ssl/` and extend `docker/nginx/default.conf` for HTTPS, or terminate TLS at Hostinger / Cloudflare and proxy to port 80.

## Updates

```bash
git pull
docker compose up -d --build
docker compose exec app php artisan migrate --force
docker compose exec app php artisan optimize
```
