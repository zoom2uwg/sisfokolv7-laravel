# ADR-011: UI Architecture — Blade SSR + Alpine.js (Hybrid Modern)

- **Tanggal:** 2026-06-22
- **Status:** ✅ DISETUJUI
- **Penulis:** ZCode (berdasarkan diskusi arsitektur)
- **Konteks:** DEV_DOCS-053, DEV_DOCS-053b

---

## Keputusan

Kita menggunakan **Blade SSR + Alpine.js + Tailwind CSS** sebagai arsitektur UI untuk Fase 1 (MVP), dengan API layer (Sanctum) diimplementasikan di Fase 2 setelah seluruh infrastruktur MVC selesai dan fungsional.

---

## Konteks

Aplikasi SISFOKOL v7 saat ini adalah **Blade-SSR monolith** dengan:
- Dark theme premium (slate-950 background)
- Tailwind CSS (CDN + Vite)
- Alpine.js (sudah dipakai di beberapa view)
- Plus Jakarta Sans font
- Glassmorphism card effects
- Responsive sidebar (desktop fixed + mobile off-canvas)

Pertanyaan yang muncul: apakah perlu beralih ke Livewire untuk reactivity yang lebih baik?

---

## Opsi yang Dipertimbangkan

| Opsi | Server Load | Client Load | Complexity | Existing Code |
|------|------------|-------------|------------|---------------|
| **A. Blade + Alpine.js** | ⭐⭐⭐⭐⭐ Paling ringan | ⭐⭐⭐⭐ ~15KB | ⭐⭐⭐⭐⭐ Tidak ada dep baru | ⭐⭐⭐⭐⭐ Sudah dipakai |
| B. Blade + Alpine + HTMX | ⭐⭐⭐⭐ Ringan | ⭐⭐⭐⭐ ~29KB | ⭐⭐⭐ Tambah 1 dep | ⭐⭐⭐ Perlu refactor |
| C. Livewire | ⭐⭐ Berat | ⭐⭐⭐ ~30KB+ | ⭐⭐ Tambah dep besar | ⭐⭐ Perlu rewrite |

---

## Keputusan: Opsi A — Blade + Alpine.js

### Alasan:

1. **Sudah ada di codebase** — Alpine.js + Tailwind + Vite sudah terkonfigurasi
2. **Paling ringan untuk server** — render HTML sekali, selesai, 0 state tersimpan
3. **Tidak ada dependency baru** — tidak perlu install apa pun
4. **Cukup untuk kebutuhan MVP** — form, dropdown, modal, search, toggle
5. **Tidak menghambat API Fase 2** — Alpine.js bisa fetch JSON dari API endpoint

### Apa yang TIDAK dipilih dan kenapa:

- **Livewire** — terlalu berat untuk shared hosting, setiap interaksi = request ke server, perlu rewrite views, dependency besar (~2MB)
- **HTMX** — bagus tapi tidak perlu untuk fase ini, bisa ditambahkan nanti jika perlu

---

## Standar UI/UX yang Diterapkan

### 1. Design System

```
Theme:          Dark (slate-950 base)
Font:           Plus Jakarta Sans (Google Fonts)
Icon:           Font Awesome 6.5
Colors:         Indigo (primary), Emerald (success), Amber (warning), Rose (danger)
Border Radius:  rounded-xl (card), rounded-2xl (modal), rounded-full (badge)
Shadow:         shadow-lg (card), shadow-2xl (modal)
Glassmorphism:  backdrop-blur-md + bg-slate-900/50
```

### 2. Responsive Breakpoints

```
Mobile:   < 640px  (sm)  — sidebar hidden, hamburger menu
Tablet:   640-1024px      — sidebar hidden, hamburger menu
Desktop:  ≥ 1024px (lg)  — sidebar fixed visible
```

### 3. Micro-Interactions (Alpine.js)

```
Transition:     x-transition (ease-out 100ms / ease-in 75ms)
Hover:          hover:bg-slate-800 transition
Active:         active:scale-95
Focus:          focus:ring-2 focus:ring-indigo-500 focus:outline-none
Dropdown:       x-show + x-transition + x-cloak
Modal:          x-show + backdrop-blur + x-transition
Toggle:         x-model + transition-colors
Loading:        Alpine.js polling + spinner animation
Toast:          Auto-dismiss with setTimeout + x-show
```

### 4. Reusable Components (Blade Components)

Lihat file `DEV_DOCS-053c` untuk detail component library.

---

## Struktur Folder Views

```
resources/views/
├── layouts/
│   ├── app.blade.php              ← Main layout (sudah ada)
│   └── partials/
│       ├── menu.blade.php         ← Sidebar menu (sudah ada)
│       ├── navbar.blade.php       ← Top navbar (extract dari app.blade.php)
│       ├── footer.blade.php       ← Footer (extract dari app.blade.php)
│       └── flash.blade.php        ← Flash messages (extract dari app.blade.php)
│
├── components/                    ← Reusable Blade Components
│   ├── ui/                        ← UI primitives
│   │   ├── alert.blade.php        ← Toast/alert notification
│   │   ├── badge.blade.php        ← Status badge
│   │   ├── button.blade.php       ← Button variants
│   │   ├── card.blade.php         ← Glassmorphism card
│   │   ├── modal.blade.php        ← Modal dialog
│   │   ├── stat-card.blade.php    ← Dashboard stat card
│   │   └── empty-state.blade.php  ← Empty state illustration
│   │
│   ├── form/                      ← Form components
│   │   ├── input.blade.php        ← Text input with label + error
│   │   ├── select.blade.php       ← Select dropdown
│   │   ├── textarea.blade.php     ← Textarea
│   │   ├── checkbox.blade.php     ← Checkbox
│   │   └── group.blade.php        ← Form group (label + input + error)
│   │
│   └── table/                     ← Table components
│       ├── table.blade.php        ← Responsive table wrapper
│       ├── thead.blade.php        ← Table header
│       ├── row.blade.php          ← Table row with hover
│       └── pagination.blade.php   ← Pagination links
│
├── partials/                      ← Shared partials
│   ├── impersonation_banner.blade.php  ← Sudah ada
│   ├── search-form.blade.php      ← Reusable search form
│   ├── delete-confirm.blade.php   ← Delete confirmation modal
│   └── loading-spinner.blade.php  ← Loading indicator
│
├── auth/                          ← Auth views (sudah ada)
├── admin/                         ← Admin views (sudah ada)
├── academic/                      ← Academic module views (sudah ada)
├── evaluation/                    ← Evaluation views (sudah ada)
├── finance/                       ← Finance views (sudah ada)
├── presence/                      ← Presence views (sudah ada)
└── ...                            ← Other module views
```

---

## Aturan Implementasi

1. **Semua view HARUS** menggunakan `layouts/app.blade.php` sebagai parent
2. **Komponen reusable** menggunakan Blade component syntax (`<x-ui.card>`)
3. **Micro-interaction** menggunakan Alpine.js (`x-data`, `x-show`, `x-transition`)
4. **Responsive** — mobile-first, sidebar auto-hide di mobile
5. **Dark theme** — semua warna mengacu ke slate color palette
6. **Glassmorphism** — card menggunakan `backdrop-blur-md + bg-slate-900/50`
7. **Font** — Plus Jakarta Sans (sudah terkonfigurasi)
8. **Icon** — Font Awesome 6.5 (sudah terkonfigurasi)
9. **Accessibility** — `aria-label`, `sr-only` untuk screen reader
10. **Performance** — CDN fallback untuk Tailwind, Vite untuk production

---

## Referensi

- **ADR-002** — Rebuild sebagai Laravel 11 modular monolith
- **DEV_DOCS-053** — Master implementation plan
- **DEV_DOCS-053b** — Verifikasi API-Driven MVC
- **layouts/app.blade.php** — Existing layout (sudah modern)

---

*Keputusan ini mengikat untuk seluruh pengembangan UI/UX di Fase 1.*
