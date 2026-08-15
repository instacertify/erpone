# Fix Hostinger 403 Forbidden after Git deploy

## Why you see `403 Forbidden — Access to this resource on the server is denied!`

Hostinger is serving the **repo root**. Laravel’s entry file is **`public/index.php`**.  
If the root has no `index.html` and listing is disabled → **403**.

Also: the Hostinger form that only shows Vite/React/Other is a **Node deployer**. This ERP is **Laravel/PHP** and needs PHP + `public` as document root (or VPS + Docker).

## Fix in Hostinger hPanel (shared / Git deploy)

1. Open **Websites → Manage → Files** (or **Advanced → Document Root**)
2. Set **Document root / public_html** to the app’s **`public`** folder  
   Example:
   - `/domains/instacertify.in/public_html/public`  
   - or `/home/USER/domains/instacertify.in/public_html/erpone/public`
3. Confirm these exist inside `public/`:
   - `index.php`
   - `.htaccess`
4. In SSH (or terminal in panel), from the project root:

```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
# Configure DB in .env (Hostinger MySQL if shared hosting)
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan optimize
chmod -R ug+rwx storage bootstrap/cache
```

5. Clear cache / purge CDN if enabled, then open:
   - `https://instacertify.in/admin/login`

## If you used Framework = Other / Node deploy

That path will keep failing for this ERP. Prefer:

### Recommended: Hostinger VPS + Docker
1. VPS · Ubuntu 24.04  
2. Install Docker  
3. Run:

```bash
cd /opt
git clone https://github.com/instacertify/erpone.git instacertify-erp
cd instacertify-erp
chmod +x scripts/*.sh
DOMAIN=instacertify.in ./scripts/install.sh
```

## Quick checks

| Check | Expected |
| --- | --- |
| Opening domain | Laravel/Filament page, not 403 |
| Document root ends with | `/public` |
| `public/index.php` | Present |
| PHP version | **8.3+** (ideally **8.5**) |
| Database | Postgres (VPS/Docker) or compatible DB on shared |

## Still 403?

- Permissions: `storage` and `bootstrap/cache` must be writable  
- Wrong branch/folder deployed  
- SSL/CDN caching old deny response — purge and retry in a private window  
