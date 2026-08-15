# Instacertify ERP

Employee-friendly ERP for **Instacertify** (hosted at [instacertify.in](https://instacertify.in)).

Inspired by Zoho / ERPNext / Odoo / QuickBooks / Freshdesk — simple navigation, editable records, INR-first billing with USD quotes.

## Stack

| Layer | Software | Version |
| --- | --- | --- |
| OS | Ubuntu Server LTS | 24.04 |
| Web | Nginx | 1.28.x |
| Language | PHP | 8.5.x |
| Framework | Laravel | 13.x |
| DB | PostgreSQL | 18.4 |
| Frontend | Livewire + Alpine.js | 4.x / 3.x |
| CSS | Tailwind CSS | 4.3.x |
| Admin | Filament | 5.x |
| Cache/Queue | Valkey + Horizon | 9.1.1 / 5.x |
| Files | MinIO | current |
| Excel | PhpSpreadsheet | 2.x |
| PDF | Chromium + Browsershot | current |
| Containers | Docker Engine + Compose | 29.x / v2 |

## Modules (foundation)

- CRM (customers & contacts)
- Sales (leads & quotations — INR / USD)
- Projects & tasks
- Calendar
- Team chat
- File storage (MinIO)
- Testing tracker
- Sample management
- HRMS
- Billing / invoices / payments
- Admin settings (GST API, optimize, bulk Excel export)

## One-click install (Hostinger VPS)

On a fresh Ubuntu 24.04 VPS with Docker Engine 29.x + Compose v2:

```bash
git clone <this-repo> erpone
cd erpone
chmod +x scripts/install.sh
./scripts/install.sh
```

Open `https://instacertify.in/admin`

**Super admin (seeded):**

- Email: `nikhil@instacertify.com`
- Password: `Legal@123`

Change the password after first login.

Optional monitoring stack:

```bash
docker compose --profile monitoring up -d
```

## Local development

```bash
cp .env.example .env
# For local SQLite smoke tests you can set DB_CONNECTION=sqlite
composer install
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

Admin panel: `http://localhost:8000/admin`

## Brand

- Primary: `#065175`
- Highlight: `#ec691f`

## Admin settings

Under **Settings → ERP Settings** (admin / super admin only):

- Company profile & timezone
- Currency (INR primary, USD quote option + exchange rate)
- GST API credentials attach + connection test
- Module toggles
- Optimize / clear caches
- Bulk Excel download (admin only)

## Next iterations

This PR establishes the framework. Follow-up work will deepen each module (workflows, permissions matrix, chat UI, calendar views, GST e-invoice posting, OpenSearch, etc.).
