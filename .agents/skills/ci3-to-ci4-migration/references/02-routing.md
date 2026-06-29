# 02 — Routing

Konversi `application/config/routes.php` (CI3) → `app/Config/Routes.php` (CI4).

## Mapping

### Default controller
CI3:
```php
$route['default_controller'] = 'welcome';
```
CI4:
```php
$routes->setDefaultController('Welcome::index');
```

### Route sederhana
CI3:
```php
$route['foo/bar'] = 'foo/bar';   // controller/method
```
CI4:
```php
$routes->get('foo/bar', 'Foo::bar');
```

### Route dengan parameter
CI3:
```php
$route['user/(:num)'] = 'user/view/$1';
```
CI4:
```php
$routes->get('user/(:num)', 'User::view/$1');
```

### 404 override
CI3:
```php
$route['404_override'] = 'errors/show_404';
```
CI4:
```php
$routes->set404Override('Errors::show_404');
```

### HTTP verb spesifik
CI3:
```php
$route['form/submit']['post'] = 'form/submit';
```
CI4:
```php
$routes->post('form/submit', 'Form::submit');
```

## Gotcha

- CI4 case-sensitive: controller `Foo` di-route sebagai `Foo::method`, bukan `foo/method`. Nama class harus PascalCase.
- CI4 wajib eksplisit verb (`get`/`post`/...) — tidak ada "ANY" implisit seperti CI3 wildcard. Pakai `$routes->add()` untuk ANY (tapi tidak recommended).
- Regex route kompleks + group + filter → manual, lihat docs CI4 `Routing`.

## Mekanis?

Sebagian. Route sederhana `$route['x']='c/m'` bisa dibantu `feature-parity-check.mjs` untuk verifikasi parity, tapi penulisan `Routes.php` manual (butuh keputusan verb + nama class PascalCase).
