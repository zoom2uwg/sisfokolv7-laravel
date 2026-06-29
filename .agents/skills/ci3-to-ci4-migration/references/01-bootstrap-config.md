# 01 — Bootstrap & Config CI4

Pakai reference ini saat project CI4 belum ada (step 2 workflow). Jika CI4 sudah ada, skip ke `02-routing.md`.

## Setup project CI4

```bash
composer create-project codeigniter4/appstarter ci4-project
cd ci4-project
```

Struktur hasil:
```
app/{Controllers,Models,Views,Config,Libraries,Helpers}
system/      (core, jangan diubah)
writable/    (cache, logs, sessions, uploads)
public/      (index.php, web root)
.env         (konfigurasi)
spark        (CLI tool, mirip artisan)
```

## .env

Copy `env` → `.env`, set:
```ini
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost:8080'
database.default.hostname = localhost
database.default.database = mydb
database.default.username = root
database.default.password = ''
```

## Konversi config (array → class)

### config/config.php → app/Config/App.php
- `$config['base_url']` → `public $baseURL` (di .env: `app.baseURL`)
- `$config['index_page']` → `public $indexPage = ''` (CI4 pakai mod_rewrite)
- `$config['encryption_key']` → `app/Config/Encryption.php` `$key`
- `$config['csrf_token_name']`/`csrf_regenerate` → `app/Config/Security.php`/`App.php`

### config/database.php → app/Config/Database.php + .env
- Array `$db['default']` → class property + override via .env `database.default.*`

### config/autoload.php → service registration
- `$autoload['libraries']` (session, database) → otomatis tersedia di CI4 via service/factory
- `$autoload['helper']` → set di `app/Config/Autoload.php` `$helpers` atau di `BaseController::$helpers`
- `$autoload['packages']` (third_party) → composer atau `app/ThirdParty/`

## Spark CLI (setara artisan Laravel)

CI4 punya CLI `spark` untuk scaffolding & DB:

```bash
php spark serve                      # jalankan dev server
php spark make:controller Foo        # scaffold controller
php spark make:model FooModel
php spark make:migration AddXTable
php spark make:seeder FooSeeder
php spark make:filter AuthFilter
php spark make:command FooCommand
php spark routes                     # list route terdaftar
php spark migrate                    # jalankan DB migration
php spark migrate:rollback
php spark migrate:status
php spark db:seed FooSeeder
```

Gunakan `spark` untuk scaffolding saat konversi (lebih cepat + konsisten dari pada buat manual).

## Verifikasi

```bash
php spark serve
```
Buka `http://localhost:8080` — harus muncul welcome page CI4 tanpa error.
