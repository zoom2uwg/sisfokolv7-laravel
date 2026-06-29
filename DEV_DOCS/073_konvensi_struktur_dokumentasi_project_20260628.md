# Konvensi Struktur Dokumentasi Project SISFOKOL

**Tanggal:** 28 Juni 2026  
**Oleh:** ZCode  
**Tujuan:** Panduan untuk semua Agentic AI (Antigravity, Kiro, Opencode, ZCode) tentang konvensi penyimpanan dokumentasi

---

## Ringkasan

Dokumen ini menjelaskan konvensi struktur folder dokumentasi di project SISFOKOL v7. Semua Agentic AI WAJIB mengikuti konvensi ini untuk menjaga konsistensi dan kemudahan pencarian dokumen.

---

## Struktur Folder Utama

```
D:\laragon\www\sisfokolv7\
├── DEV_DOCS/                    ← Dokumen development utama
├── ADR/                         ← Architecture Decision Records
├── docs/
│   ├── superpowers/
│   │   ├── plans/               ← Implementation plans (dari writing-plans skill)
│   │   └── specs/               ← Design specs (dari brainstorming skill)
│   ├── dev-reports/             ← DEPRECATED → pindah ke DEV_DOCS/
│   ├── analisis-sisfokol/       ← Analisis bisnis
│   ├── dokumen-proyek-sis/      ← Dokumen proyek
│   └── *.md                     ← Dokumen analisis referensi
└── ...
```

---

## 1. `DEV_DOCS/` — Dokumen Development Utama

### Isi
Semua dokumentasi hasil development, analisis, laporan, panduan.

### Naming Convention
```
XXX_[judul_deskriptif]_[YYYYMMDD].md
```

- `XXX` — Nomor sequential (001, 002, ..., 072, dst)
- `[judul_deskriptif]` — Judul dengan underscore, lowercase
- `[YYYYMMDD]` — Tanggal pembuatan

### Contoh
```
DEV_DOCS/
├── 001_kickoff_keputusan_scope_dan_stack_20260620.md
├── 013_walkthrough_epic_1_setup_fondasi_20260620.md
├── 053_master_implementation_plan_konsolidasi_20260622.md
├── 054_memory_handoff_konteks_terkini_20260622.md
├── 064_analisis_keamanan_crudlfix_tenant_rbac_20260625.md
├── 071_analisis_crudlfix_vs_tall_stack_20260628.md
├── 072_panduan_livewire_crudlfix_hybrid_20260626.md
└── ...
```

### Kategori Dokumen di DEV_DOCS

| Prefix | Kategori | Contoh |
|--------|----------|--------|
| `kickoff` | Kickoff/keputusan awal | `001_kickoff_*.md` |
| `implementation_plan` | Rencana implementasi | `053_implementation_plan_*.md` |
| `implementation_report` | Laporan implementasi | `015_implementation_report_*.md` |
| `dev_report` | Laporan development harian | `055_dev_report_*.md` |
| `audit` | Hasil audit/verifikasi | `057_audit_*.md` |
| `analisis` | Analisis mendalam | `071_analisis_*.md` |
| `panduan` | Panduan penggunaan | `072_panduan_*.md` |
| `memory_handoff` | Konteks untuk handoff | `054_memory_handoff_*.md` |
| `walkthrough` | Walkthrough modul/epic | `013_walkthrough_*.md` |
| `sprint_plan` | Sprint planning | `017_sprint_plan_*.md` |
| `fix` | Bug fix report | `068_fix_*.md` |

### Aturan Wajib
1. **SELALU** gunakan numbering sequential terbaru (cek `ls DEV_DOCS | tail -5`)
2. **SELALU** sertakan tanggal di akhir nama file
3. **JANGAN** gunakan spasi di nama file (pakai underscore)
4. **JANGAN** duplikasi dokumen yang sama

---

## 2. `docs/superpowers/` — Working Documents dari Skills

### Isi
Dokumen yang di-generate oleh ZCode superpowers skills. Ini adalah **working documents** untuk proses design & planning.

### Struktur
```
docs/superpowers/
├── plans/     ← Dari writing-plans skill
└── specs/     ← Dari brainstorming skill
```

### Naming Convention
```
YYYY-MM-DD-[topic].md           ← plans/
YYYY-MM-DD-[topic]-design.md    ← specs/
```

### Contoh
```
docs/superpowers/
├── plans/
│   ├── 2026-06-25-crudlfix-v2-plan.md
│   └── 2026-06-26-hybrid-crudlfix-livewire.md
└── specs/
    ├── 2026-06-25-crudlfix-v2-design.md
    └── 2026-06-26-hybrid-crudlfix-livewire-design.md
```

### Kapan Pakai
- Saat menggunakan skill `brainstorming` → hasilnya ke `specs/`
- Saat menggunakan skill `writing-plans` → hasilnya ke `plans/`

### Aturan
1. **JANGAN** pindahkan ke DEV_DOCS (ini working documents)
2. **BOLEH** commit ke git untuk tracking
3. **BOLEH** referensi dari DEV_DOCS jika perlu

---

## 3. `docs/dev-reports/` — DEPRECATED

### Status
**DEPRECATED** — Tidak digunakan lagi.

### Alasan
Duplikasi dengan `DEV_DOCS/`. Semua dev reports harus masuk ke `DEV_DOCS/` dengan numbering sequential.

### Migrasi
Jika ada file di sini, pindahkan ke `DEV_DOCS/` dengan naming:
```
DEV_DOCS/XXX_dev_report_[judul]_[YYYYMMDD].md
```

---

## 4. `docs/` (Root) — Dokumen Analisis & Referensi

### Isi
Dokumen analisis bisnis, data dictionary, workflow, dokumen proyek.

### Contoh
```
docs/
├── ANALISIS_MENDALAM_KESIAPAN_SISFOKOL.md
├── DOC_03_BUSINESS_PROCESS_WORKFLOW_MAP.md
├── DOC_04_DATA_DICTIONARY_AND_ERD.md
├── REFACTOR_PLAN_LARAVEL_11.md
├── SISFOKOL_Refactoring_Laravel11_Plan.pdf
├── analisis-sisfokol/
├── dokumen-proyek-sis/
└── ...
```

### Aturan
1. **Bebas** naming (tidak perlu sequential)
2. **Utamakan** markdown (.md) untuk dokumen teks
3. **PDF/DOCX** hanya untuk dokumen formal yang perlu format khusus

---

## 5. `ADR/` — Architecture Decision Records

### Isi
Keputusan arsitektur yang sudah diapprove. Bersifat **mengikat** untuk seluruh pengembangan.

### Naming Convention
```
XXX_[judul_deskriptif].md
```

### Contoh
```
ADR/
├── 003_multi_tenant_saas.md
├── 006_granular_rbac.md
├── 009_plugin_system.md
├── 010_rbac_menu_field_level.md
└── 011_ui_architecture_blade_alpine_ssr_20260622.md
```

### Aturan
1. **HANYA** untuk keputusan arsitektur yang sudah diapprove
2. **JIKA** ada update, tambahkan section "Changelog" di akhir
3. **JIKA** ada ADR baru, update `project-context.md` di `.agents/steering/`

---

## 6. `.agents/steering/` — Agent Steering Documents

### Isi
Konteks project dan guidelines untuk semua Agentic AI.

### File
```
.agents/steering/
├── project-context.md      ← Konteks project utama
└── karpathy-guidelines.md  ← Coding guidelines
```

### Aturan
1. **WAJIB** dibaca oleh SEMUA Agentic AI sebelum mulai bekerja
2. **UPDATE** jika ada perubahan arsitektur atau keputusan penting
3. **BACKUP** sebelum edit (`backups/md/[nama].bak_YYYYMMDD`)

---

## Ringkasan Konvensi

| Lokasi | Untuk Apa | Naming | Sequential? |
|--------|-----------|--------|-------------|
| `DEV_DOCS/` | **SEMUA** dokumen development | `XXX_[judul]_[YYYYMMDD].md` | ✅ Ya |
| `docs/superpowers/specs/` | Design specs | `YYYY-MM-DD-[topic]-design.md` | ❌ No |
| `docs/superpowers/plans/` | Implementation plans | `YYYY-MM-DD-[topic].md` | ❌ No |
| `docs/dev-reports/` | DEPRECATED → ke DEV_DOCS | - | - |
| `docs/` (root) | Analisis bisnis, referensi | Bebas | ❌ No |
| `ADR/` | Architecture Decision Records | `XXX_[judul].md` | ✅ Ya |
| `.agents/steering/` | Agent steering | Fixed names | ❌ No |

---

## Checklist untuk Agentic AI

### Sebelum Membuat Dokumen Baru
- [ ] Cek apakah dokumen serupa sudah ada
- [ ] Tentukan kategori (dev report, analisis, panduan, dll)
- [ ] Cek numbering terbaru di DEV_DOCS

### Saat Membuat Dokumen
- [ ] Gunakan naming convention yang benar
- [ ] Sertakan tanggal di nama file
- [ ] Gunakan underscore (bukan spasi)
- [ ] Tambahkan header dengan metadata (tanggal, penulis, status)

### Setelah Membuat Dokumen
- [ ] Commit ke git
- [ ] Update referensi di dokumen lain jika perlu
- [ ] Update `project-context.md` jika ada keputusan penting

---

## Referensi

- `DEV_DOCS/054_memory_handoff_konteks_terkini_20260622.md` — Memory/handoff utama
- `.agents/steering/project-context.md` — Konteks project
- `.agents/steering/karpathy-guidelines.md` — Coding guidelines

---

*Dokumen ini dibuat oleh ZCode pada 28 Juni 2026 untuk konsistensi dokumentasi project.*
