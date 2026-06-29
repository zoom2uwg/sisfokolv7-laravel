---
description: how to run PHP commands (artisan, scripts) for this repo using PHP 8.3
---

# PHP 8.3 Execution Rule for sisfokolv7

This repo requires **PHP 8.3** (Laravel 11; project standard = 8.3.31).
**Never use the bare `php` command** — on this machine `php` resolves to PHP 8.2.30,
which is *not* the project standard. Always use `php83`.

## The `php83` command

`php83` is a `.bat` alias that points to the PHP 8.3.31 binary:

```
D:\laragon\bin\php\php-8.3.31-nts-Win32-vs16-x64\php.exe
```

## How to invoke — by shell

### PowerShell / cmd

```powershell
php83 artisan <command>
php83 D:\composer\composer.phar <command>
```

### Git Bash (use the `.bat` extension — bare `php83` is not found here)

```bash
php83.bat artisan <command>
php83.bat -l <file.php>
php83.bat D:/composer/composer.phar <command>

# or equivalently:
cmd //c "php83 artisan <command>"
```

> The bare `php` in Git Bash = `/d/laragon/bin/php/php-8.2.30-Win32-vs16-x64/php` (PHP 8.2.30).
> It runs, but it is **not** the project standard. Always prefer `php83`.

## Common commands

```bash
# Artisan
php83.bat artisan migrate
php83.bat artisan db:seed
php83.bat artisan test
php83.bat artisan tinker

# Clear caches
php83.bat artisan cache:clear
php83.bat artisan config:clear
php83.bat artisan view:clear
php83.bat artisan route:clear

# Lint a single PHP file
php83.bat -l app/Livewire/Crudlfix/CrudlfixPage.php

# Composer
php83.bat D:/composer/composer.phar install
php83.bat D:/composer/composer.phar require <package>
```

## Working directory

Always run from the Laravel app directory:

```
D:\laragon\www\sisfokolv7\sisfokol-laravel
```

The root project (`D:\laragon\www\sisfokolv7`) holds `ADR/`, `DEV_DOCS/`, `DOCS/`,
`.agents/` — but `artisan` and `composer.json` live in `sisfokol-laravel/`.

## Why not the default `php`?

| Command | Version | OK? |
|---------|---------|-----|
| `php` (Git Bash default) | 8.2.30 | ⚠️ works, but not the project standard |
| `php83` / `php83.bat` | 8.3.31 | ✅ project standard |

Standardizing on 8.3 avoids subtle behavior differences (enum cases, readonly
property edge cases, `#[\Override]` attribute, etc.) between the developer
machine and the intended runtime.
