# 00 — Audit Codebase CI3

Scan codebase CI3 sebelum mulai konversi. Tujuan: inventaris + impact analysis + estimasi effort + identifikasi stuck point.

## Checklist manual

- [ ] Cek struktur: `application/{controllers,models,views,config,libraries,helpers,hooks,third_party}`
- [ ] Daftar semua controller (class + method publik)
- [ ] Daftar semua model
- [ ] Daftar custom library & helper
- [ ] Daftar hook di `config/hooks.php`
- [ ] Cek `config/autoload.php` — apa yang autoload (library/helper/model)
- [ ] Cek `config/config.php`, `config/database.php`, `config/routes.php`
- [ ] Daftar `application/third_party/*` — apakah ada padanan composer/CI4?
- [ ] Cek PHP version (CI3 min 5.6 → CI4 butuh 7.2+, CI4.4+ butuh 7.4+)

## Scan otomatis

```bash
node scripts/audit-ci3.mjs <ci3-application-dir>
```

Output: JSON + ringkasan human — list file per type, ~17 pola CI3 terdeteksi (`extends CI_Controller`, `load->model`, `get_instance()`, `migration->`, `dbforge->`, `security->`, dll), custom `MY_*`, third-party, dan estimasi effort:
- **kecil**: <10 controller, sedikit custom library
- **sedang**: 10-25 controller
- **besar**: >25 controller / banyak custom library / banyak `&get_instance()`

## Impact analysis (sebelum konversi)

Sebelum konversi, petakan dependency antar komponen agar tahu urutan aman:

- [ ] **Controller → library/model**: controller mana yang pakai library X / model Y? (konversi dependency dulu)
- [ ] **Model → dipakai banyak controller**: model mana yang paling banyak dipakai? (konversi dulu, tapi pastikan `$allowedFields` benar agar tidak break banyak controller)
- [ ] **Library → `&get_instance()`**: library mana yang pakai super object? (flag sebagai stuck point, butuh rewrite DI — lihat `06-libraries-helpers.md`)
- [ ] **Controller base (`MY_Controller`)**: controller mana yang `extends MY_Controller`? (konversi MY_Controller **sebelum** controller lain)
- [ ] **Route → custom controller**: route yang hardcode nama controller/method — pastikan nama class tidak berubah saat konversi

Output impact analysis: daftar urutan konversi yang aman (dependency-aware).

## Stuck point yang wajib di-flag

- Library/helper pakai `$this->CI = &get_instance()` → butuh rewrite ke DI/`service()` (lihat `06-libraries-helpers.md`)
- `MY_Controller`/`MY_Model` di `application/core/` → konversi dulu sebelum controller lain
- Third-party yang tidak punya padanan CI4 → flag ke user (out of scope rewrite)
- Migration pakai `dbforge` → lihat `11-migration-seeder-security.md`
