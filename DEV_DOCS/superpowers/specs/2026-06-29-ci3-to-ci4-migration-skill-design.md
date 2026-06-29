# Design Spec: Skill CI3 → CI4 Migration

- **Tanggal:** 2026-06-29
- **Status:** Draft
- **Penulis:** ZCode (berdasarkan brainstorming session)
- **Konteks:** Pembuatan skill ZCode reusable untuk konversi CodeIgniter 3 ke CodeIgniter 4

---

## Ringkasan Eksekutif

Membuat skill ZCode bernama `ci3-to-ci4-migration` yang memandu dan meng-otomatisasi migrasi project CodeIgniter 3 (CI3) ke CodeIgniter 4 (CI4) dengan target **feature parity**. Skill berbentuk **hybrid**: panduan (guide) untuk bagian yang butuh judgment manual + helper scripts untuk konversi mekanis yang aman (regex-based). Skill mengikuti pola progressive disclosure sesuai panduan skill-creator dan gaya skill existing di project ini (`smart-debugging`, `nwidart-module-management`).

---

## Konteks & Motivasi

### Mengapa CI3 → CI4 Bukan Upgrade Biasa

CI4 ditulis ulang dari nol — bukan upgrade inkremental dari CI3. Tidak ada tools otomatis yang bisa konversi 100% tanpa intervensi manual. Perbedaan fundamental:

| Area | CI3 | CI4 |
|------|-----|-----|
| Struktur | `application/{controllers,models,views,config,libraries,helpers}` | `app/{Controllers,Models,Views,Config,Libraries,Helpers}` + `system/`, `writable/`, `public/` |
| Namespacing | procedural, `$this->load->...` | wajib namespace + `use` statements |
| Routing | `routes.php` wildcard `$route['x/y']='c/m'` | `Config/Routes.php` `$routes->get/post()` |
| Database | Active Record `$this->db->...` | Query Builder + Model berbasis namespace |
| Config | array di `config/` | class `Config\` + `.env` |
| PHP min | 5.6+ | 7.2+ (CI4.4+ butuh 7.4+) |

### Mengapa Perlu Skill

Migrasi CI3→CI4 berulang, manual, dan rawan error. Pola konversi dapat distandarisasi. Bagian mekanis (rename file, regex `input->post`→`request->getPost`) dapat di-otomatisasi dengan aman, sementara bagian judgment (logika bisnis, library kustom, integrasi pihak ketiga) butuh panduan. Skill memastikan konsistensi + feature parity setiap kali migrasi dijalankan.

---

## Keputusan Desain (dari brainstorming)

| Aspek | Keputusan | Alasan |
|-------|-----------|--------|
| Gaya kerja | **Hybrid: guide + scripts** | Mekanis via script (aman, cepat), judgment via panduan. Pure-guide lambat; pure-automation berisiko false positive. |
| Starting state | **Dua-duanya** (setup CI4 + konversi) | Fleksibel: handle CI4 belum ada (setup dari nol) maupun sudah ada (langsung konversi). |
| Urutan konversi | **Fleksibel** (per-modul & layer-by-layer) | Skill sediakan decision tree; pilih per-modul untuk project besar (testable), layer-by-layer untuk project kecil. |
| Definition of done | **Feature parity** | Bukan sekadar "jalan tanpa error", tapi semua fitur CI3 berfungsi sama di CI4 termasuk edge case. |

---

## Arsitektur Skill

### Identitas

- **Nama skill:** `ci3-to-ci4-migration`
- **Lokasi:** `.agents/skills/ci3-to-ci4-migration/` (folder khusus, match konvensi skill existing & discovery ZCode)
- **Bahasa:** Bahasa Indonesia (match `smart-debugging`, `nwidart-module-management`)

### Frontmatter

```yaml
---
name: ci3-to-ci4-migration
description: >
  Panduan + helper scripts untuk migrasi project CodeIgniter 3 (CI3) ke CodeIgniter 4 (CI4)
  dengan feature-parity. Gunakan skill ini setiap kali user menyebut konversi/migrasi/upgrade
  CodeIgniter 3 ke 4, pindah aplikasi CI3 ke CI4, atau minta bantuan convert controller/model/routing
  CI3 ke sintaks CI4 — bahkan kalau user tidak eksplisit bilang "migrasi".
---
```

Deskripsi dibuat "pushy" sesuai panduan skill-creator (model cenderung under-trigger).

### Struktur Folder (Progressive Disclosure)

```
.agents/skills/ci3-to-ci4-migration/
├── SKILL.md                              (~200 baris: workflow + decision tree + router)
├── references/                           (dibaca on-demand per area)
│   ├── 00-audit-checklist.md
│   ├── 01-bootstrap-config.md
│   ├── 02-routing.md
│   ├── 03-controllers.md
│   ├── 04-models-db.md
│   ├── 05-views.md
│   ├── 06-libraries-helpers.md
│   ├── 07-services.md            (session, validation, email, upload, cache)
│   ├── 08-hooks-events-filters.md
│   ├── 09-third-party.md
│   └── 10-php-modernization.md
├── scripts/                              (Node.js .mjs, default --dry-run)
│   ├── audit-ci3.mjs
│   ├── convert-mechanical.mjs
│   ├── rename-files.mjs
│   └── feature-parity-check.mjs
└── assets/
    ├── mapping-table.md                  (tabel sintaks CI3->CI4 lengkap, superset reference)
    └── feature-parity-checklist.md       (template per-fitur)
```

**Alasan progressive disclosure:** 14 area konversi + mapping table lengkap akan melebihi batas body SKILL.md (target <500 baris). Reference dibaca on-demand hanya area yang relevan, sehingga body tetap ramping + model tidak skip detail.

---

## Komponen

### 1. SKILL.md (body, ~200 baris)

Outline isi:

```markdown
# CI3 → CI4 Migration (Hybrid: Guide + Scripts)

## Kapan skill ini dipakai
(konversi/migrasi CI3->CI4, convert controller/model/routing)

## Workflow inti (7 langkah)
1. Audit CI3 source
   - Baca references/00-audit-checklist.md
   - Jalankan: node scripts/audit-ci3.mjs <ci3-path>
   - Output: laporan pola CI3 + estimasi effort
2. Cek keberadaan project CI4
   - Belum ada -> setup via references/01-bootstrap-config.md
   - Sudah ada -> lanjut step 3
3. Pilih strategi urutan (decision tree di bawah)
4. Konversi per area — WAJIB baca reference area sebelum handle
5. Jalankan scripts/convert-mechanical.mjs untuk bagian mekanis (regex aman)
6. Feature-parity check via assets/feature-parity-checklist.md + scripts/feature-parity-check.mjs
7. Testing per fitur (input/output/edge-case sama dengan CI3)

## Decision tree — urutan konversi
- Project kecil (<10 controller, sedikit custom library) -> layer-by-layer OK
- Project besar / banyak modul / banyak custom library -> incremental per-modul
- Ada MY_Controller/MY_Model kritis -> konversi dulu sebelum controller lain
- Hosting constrain PHP version -> cek references/10-php-modernization.md lebih awal

## Router reference (baca sebelum handle area tsb)
| Area | Reference | Mekanis? (script) |
|------|-----------|-------------------|
| Audit | 00-audit-checklist | audit-ci3.mjs |
| Bootstrap/Config | 01-bootstrap-config | - |
| Routing | 02-routing | convert-mechanical.mjs (sebagian) |
| Controller | 03-controllers | convert-mechanical.mjs |
| Model/DB | 04-models-db | rename-files.mjs + convert-mechanical.mjs |
| View | 05-views | convert-mechanical.mjs |
| Library/Helper | 06-libraries-helpers | rename-files.mjs |
| Services | 07-services | convert-mechanical.mjs |
| Hooks/Events | 08-hooks-events-filters | - (judgment) |
| Third-party | 09-third-party | - (judgment) |
| PHP modernisasi | 10-php-modernization | - (judgment) |

## Prinsip wajib
- COMMENT jangan DELETE: kode CI3 lama di-comment dgn label [tgl|agent], bukan hapus
- Mekanis via script, judgment via manual — JANGAN regex-sendiri bagian berisiko
- Feature-parity WAJIB sebelum claim selesai (def of done = feature parity)
- Setiap file CI4 hasil konversi: baris 1 `<?php`, ada namespace, extends base yang benar
```

### 2. `references/` — isi per file

Setiap file punya pola: **pola CI3 → padanan CI4 (dengan code example) → gotcha → mekanis/manual?**

**`00-audit-checklist.md`** — scan codebase CI3
- Cek struktur `application/{controllers,models,views,config,libraries,helpers,hooks,third_party}`
- Inventaris: controller (class+method), model, custom library/helper, hook, `config/autoload.php`, third-party
- Cek PHP version (CI3 min 5.6 → CI4 butuh 7.2+/7.4+)
- Jalankan `scripts/audit-ci3.mjs` → laporan + estimasi effort (kecil/sedang/besar)

**`01-bootstrap-config.md`** — setup CI4 + konversi config
- Setup: `composer create-project codeigniter4/appstarter`
- Struktur CI4: `app/{Controllers,Models,Views,Config,Libraries,Helpers}`, `system/`, `writable/`, `public/`
- `.env` (base_url, DB, dll)
- Mapping config: `config/config.php`(array) → `app/Config/App.php`(class); `config/database.php` → `Database.php`+`.env`; `config/autoload.php` → service/filters
- Key: base_url, index_page, encryption_key, sess_*, csrf_*

**`02-routing.md`** — routing
- `$route['default_controller']='x'` → `$routes->setDefaultController('x')`
- `$route['foo/bar']='c/m'` → `$routes->get('foo/bar','C::m')`
- `$route['x/(:num)']='c/m/$1'` → `$routes->get('x/(:num)','C::m/$1')`
- `$route['404_override']` → `$routes->set404Override()`
- HTTP verb: `$route['x']['post']=...` → `$routes->post()`
- Regex/group/filter → manual

**`03-controllers.md`** — controller
- `class Foo extends CI_Controller` → `namespace App\Controllers; class Foo extends BaseController`
- `$this->load->model('foo_model','fm')` → `use App\Models\FooModel; $fm = new FooModel();` (manual: namespace + use)
- `$this->load->view('x',$data)` → `return view('x',$data)`
- `$this->input->post('x')` → `$this->request->getPost('x')` (mekanis)
- `$this->input->get('x')` → `$this->request->getGet('x')` (mekanis)
- `$this->uri->segment(n)` → `$this->request->uri->getSegment(n)` (mekanis)
- `$this->output->set_content_type()` → `return $this->response->setHeader()`
- Gotcha: CI3 echo-based, CI4 return-based — controller method WAJIB `return`

**`04-models-db.md`** — model & DB
- File: `models/foo_model.php`(snake) → `Models/FooModel.php`(PascalCase) — rename-files.mjs
- `class Foo_model extends CI_Model` → `namespace App\Models; class FooModel extends Model`
- Wajib set: `$table`, `$primaryKey`, `$allowedFields` (mass-assignment CI4)
- `$this->db->get('t')->result()` → `$model->findAll()` / `db_connect()->table('t')->get()->getResult()`
- `where()->get()->result()` → Query Builder chain
- `insert('t',$d)` → `$model->insert($d)`; `update/delete` sama
- `row()/result_array()` → `first()/getResultArray()`
- `$this->db->query()` raw → `db_connect()->query()`; transactions sama pola

**`05-views.md`** — view
- `$this->load->view('x',$data)` → `return view('x',$data)`
- `$this->load->view('header');...;view('footer')` → layout `extend('layout') + section('content')`
- `$this->load->vars()` → lewat argumen `view(...,$data)`
- helper di view: `$this->load->helper('form')` → `helper('form')` (auto di BaseController)

**`06-libraries-helpers.md`** — custom lib/helper
- Library `application/libraries/Foo.php` `class Foo` → `app/Libraries/Foo.php` `namespace App\Libraries; class Foo`
- `$this->load->library('foo')` → `use App\Libraries\Foo; $foo = new Foo();`
- `$this->CI = &get_instance()` (CI3 super object) → DI / `service()` / `Config\Services` (paling sering jadi stuck point — highlight!)
- Helper `helpers/foo_helper.php` → `app/Helpers/foo_helper.php` (function-based, no namespace)
- `$this->load->helper('foo')` → `helper('foo')`
- `application/core/MY_Controller.php` → extend `BaseController` / custom base di `app/Controllers/`

**`07-services.md`** — session, validation, email, upload, cache
- Session: `set_userdata('k',$v)` → `session()->set('k',$v)`; `userdata('k')` → `get('k')`; `set_flashdata` → `setFlashdata` (mekanis)
- Form validation: `set_rules('f','L','required')` → `service('validation')->setRules([...])` atau config; `run()` → `validate()`
- Email: `$this->load->library('email')` → `service('email')` / `email()`
- Upload: `$this->upload->do_upload('f')` → `$file=$this->request->getFile('f'); $file->move(WRITEPATH.'uploads')`
- Cache: `$this->load->driver('cache')` → `cache()->save/get`

**`08-hooks-events-filters.md`**
- `$hook['pre_controller']=...` → `Events::on('pre_controller',...)` + Filters `app/Config/Filters.php`
- Mapping point: pre_system, pre_controller, post_controller_constructor, post_controller

**`09-third-party.md`**
- `application/third_party/foo.php` + `require_once` → composer require / `app/ThirdParty/` + autoload
- Cek apakah library punya versi CI4 / composer package
- Highlight: library yang pakai `&get_instance()` biasanya perlu rewrite

**`10-php-modernization.md`**
- PHP 5.6 → 7.4/8.x (CI4.4+ butuh 7.4)
- Untyped → typed properties, return types, param types
- Constructor promotion, nullables, union types (8.x), null coalescing `??`, short array `[]`
- Opsional, tapi recommended

### 3. `scripts/` — API

Semua script Node.js (`.mjs`), **default `--dry-run`** (print diff, tidak ubah file). `--apply` untuk eksekusi.

| Script | Input | Output | Yang dihandle (SAFE, regex) |
|--------|-------|--------|------------------------------|
| `audit-ci3.mjs` | `<ci3-path>` | JSON + ringkasan human: list controller/model/library, pola CI3 terdeteksi, custom MY_, third-party, estimasi effort | Scan saja, tidak ubah |
| `convert-mechanical.mjs` | `<file\|dir> [--apply]` | Diff per file | `input->post/get`, `session->set_userdata/userdata/set_flashdata`, `load->view`(call→`view()`), `load->helper`, `uri->segment` |
| `rename-files.mjs` | `<ci3-path> <ci4-path> [--apply]` | Daftar rename+pindah | `models/foo_model.php`→`Models/FooModel.php`; `controllers/Foo.php`→`Controllers/Foo.php`; `libraries/`→`Libraries/`; `helpers/*_helper.php`→`Helpers/` |
| `feature-parity-check.mjs` | `<ci3-path> <ci4-path>` | Diff report: endpoint CI3 yang belum ada di CI4, method controller/model hilang | Bandingkan struktur (route+method), bukan behavior test |

**Catatan batas script:** `convert-mechanical.mjs` konversi **call** `$this->load->view('x',$data)` → `view('x',$data)`, tapi **TIDAK menambah prefix `return`** — itu manual karena kontekstual (tergantung apakah statement terakhir di method). Demikian juga `$this->input->post()` → `$this->request->getPost()` itu mekanis, tapi adaptasi logic di sekitarnya tetap manual.

**Yang TIDAK disentuh script** (judgment manual):
- `extends CI_Controller/CI_Model` (butuh namespace + file move + use statement)
- `$this->load->model()` (butuh keputusan namespace + import)
- `$this->load->library()` custom (butuh namespace + DI rewrite)
- Business logic, query builder rewrite, hooks→events

### 4. `assets/` — referensi lengkap

**`mapping-table.md`** — tabel sintaks CI3→CI4 lengkap (semua 14 area, satu tempat, searchable). Superset dari reference, untuk quick lookup saat debug area tertentu.

**`feature-parity-checklist.md`** — template per-fitur:
```markdown
## Fitur: [nama]
- CI3 route/endpoint: ...
- CI3 input (param/body): ...
- CI3 output (view/response): ...
- CI3 edge case: ...
- CI4 status: [ ] belum / [ ] konversi / [ ] test
- CI4 test result: output sama? edge case sama?
- Catatan: ...
```

---

## Data Flow / Workflow Eksekusi

```
User: "migrate ci3 project X ke ci4"
        │
        ▼
[SKILL.md trigger] → baca workflow + decision tree
        │
        ▼
[Step 1] audit-ci3.mjs <ci3-path>  ──▶ laporan pola CI3 + estimasi
        │
        ▼
[Step 2] CI4 sudah ada?
        ├─ belum ──▶ references/01-bootstrap-config.md (setup CI4)
        └─ sudah  ──▶ skip ke step 3
        │
        ▼
[Step 3] decision tree: per-modul vs layer-by-layer
        │
        ▼
[Step 4-5] per area:
   baca references/<area>.md
   ──▶ rename-files.mjs (jika perlu pindah file)
   ──▶ convert-mechanical.mjs --dry-run → review → --apply
   ──▶ konversi manual (namespace, use, extends, business logic)
        │
        ▼
[Step 6] feature-parity-check.mjs <ci3> <ci4>
   + assets/feature-parity-checklist.md (per fitur)
        │
        ▼
[Step 7] testing per fitur → claim selesai (def of done = feature parity)
```

---

## Error Handling & Safety

1. **Script default `--dry-run`** — tidak ada perubahan file tanpa review eksplisit (`--apply`)
2. **COMMENT jangan DELETE** — kode CI3 lama di-comment dengan label `[tgl|agent]`, bukan dihapus (match `smart-debugging` skill). Rollback = uncomment.
3. **Script hanya sentuh bagian aman** — tidak regex-sendiri `extends CI_*`, `$this->load->model/library`, business logic
4. **Verifikasi post-konversi per file** — baris 1 `<?php`, ada namespace, extends base yang benar
5. **Tidak claim selesai sebelum feature-parity check** — def of done = feature parity, bukan "jalan tanpa error"

---

## Eval Plan (Test Prompts)

3 realistic test prompts (casual, path konkret, sesuai yang user ketik asli) + rubrik lulus.

### Test prompt 1 — Full migration from scratch
> *"aku punya project ci3 lama di `D:\laragon\www\simtold`, mau migrate ke ci4. ada sekitar 20 controller 15 model, ada library custom buat auth pake &get_instance(). gimana mulainya?"*

**Lulus kalau skill:**
- Trigger otomatis (description pushy)
- Mulai dari step 1 audit → jalankan `audit-ci3.mjs` (atau baca `00-audit-checklist.md`)
- Decision tree: project besar + custom library kritis → pilih **incremental per-modul**, prioritaskan library auth dulu
- Sorot `&get_instance()` di `06-libraries-helpers.md` sebagai stuck point
- Jangan langsung konversi 20 controller sekaligus

### Test prompt 2 — Per-file conversion (kasual, typo)
> *"bantuin convert controller Auth.php ini dr ci3 ke ci4, bingungnya di session flashdata sama form validation. ini file nya `application/controllers/Auth.php`"*

**Lulus kalau skill:**
- Baca `references/03-controllers.md` + `07-services.md` sebelum handle
- Cek apakah file sudah di-move ke `app/Controllers/` (rename-files.mjs) atau konversi in-place
- Mapping `set_flashdata` → `setFlashdata`, `form_validation->set_rules` → `service('validation')`
- Kasih `namespace App\Controllers;` + `extends BaseController`
- Highlight: method controller CI4 WAJIB `return`

### Test prompt 3 — CI4 sudah ada, convert sisa model
> *"ci4 project udah jalan di `D:\laragon\www\simv4`, tinggal mindahin model ci3 dari `D:\laragon\www\simold\application\models`. banyak yg pake active record + query builder"*

**Lulus kalau:**
- Skip bootstrap (CI4 sudah ada)
- Pakai `rename-files.mjs` untuk `foo_model.php` → `FooModel.php`
- Baca `04-models-db.md`: Active Record → Query Builder, wajib set `$table/$primaryKey/$allowedFields`
- Jalankan `convert-mechanical.mjs --dry-run` dulu, minta review sebelum `--apply`
- Feature-parity check di akhir

### Rubrik umum (semua prompt)
- ✅ Skill trigger tanpa `/skill` eksplisit (description cukup pushy)
- ✅ Model baca reference area YANG RELEVAN saja (tidak baca semua reference)
- ✅ Default `--dry-run` untuk script mekanis — tidak apply tanpa review
- ✅ Prinsip "COMMENT jangan DELETE" dipatuhi
- ✅ Tidak claim selesai sebelum feature-parity check
- ❌ FAIL: langsung regex-sendiri bagian berisiko (model load, library, extends), atau apply script tanpa dry-run

### Cara eksekusi eval
1. Build skill dulu (spec → plan → implement)
2. Letakkan skill di `.agents/skills/ci3-to-ci4-migration/`
3. Fresh turn, kasih test prompt ke model (biar description trigger, atau `/skill ci3-to-ci4-migration <prompt>`)
4. Cek output + trace vs rubrik
5. Iterasi skill sampai 3/3 lulus

---

## Out of Scope (YAGNI)

Hal yang **tidak** dicakup skill ini (untuk hindari over-engineering):

1. **Migration dari framework lain** (Laravel, Symfony, dll) — hanya CI3→CI4
2. **CI4 → CI4 upgrade** (mis. 4.2 → 4.5) — out of scope
3. **Automated behavior/functional testing** — `feature-parity-check.mjs` cek struktur (route+method) saja, bukan menjalankan test fungsional. Behavior test diserahkan ke user.
4. **CI3 → CI3 refactor/modernisasi** — skill fokus ke CI4, bukan rapikan CI3
5. **Database schema migration** — skema DB tidak diubah; hanya layer akses DB yang dikonversi
6. **Deployment/CI-CD setup** — out of scope
7. **Konversi library pihak ketiga yang tidak punya padanan CI4** — di-flag di `09-third-party.md` tapi rewrite penuh di luar scope (diserahkan ke user)

---

## Open Questions

Tidak ada. Semua keputusan desain utama sudah resolved di brainstorming. Detail isi tiap reference/scripts akan finalisasi saat implementasi (writing-plans → execute).

---

## Next Step

Spec ini → di-review user → invoke `writing-plans` skill untuk susun implementation plan (urutan pembuatan: SKILL.md → references → scripts → assets → eval).
