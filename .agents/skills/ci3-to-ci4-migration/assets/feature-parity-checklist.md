# Feature Parity Checklist

Template per-fitur. Def of done = feature parity (input/output/edge-case sama). Salin block ini per fitur.

## Fitur: [nama fitur]

- **CI3 route/endpoint:** `METHOD /path` → controller::method
- **CI3 input (param/body):** ...
- **CI3 output (view/response):** ...
- **CI3 edge case:** (input kosong? input invalid? limit? pagination? error handling?)
- **CI4 status:**
  - [ ] belum
  - [ ] konversi
  - [ ] test
- **CI4 test result:**
  - output sama? [ ] ya / [ ] tidak
  - edge case sama? [ ] ya / [ ] tidak
- **Catatan:** ...

---

## Checklist global (jalankan di akhir migrasi)

- [ ] Semua route CI3 ada di CI4 (`node scripts/feature-parity-check.mjs <ci3> <ci4>` → 0 missing)
- [ ] Semua controller method publik CI3 ada di CI4
- [ ] Semua model method publik CI3 ada di CI4
- [ ] Session behavior sama (login/logout/flashdata)
- [ ] Form validation behavior sama (error message, rule)
- [ ] Upload behavior sama (validation, storage path)
- [ ] Email sending behavior sama
- [ ] Database query result shape sama (row/result/array)
- [ ] Pagination behavior sama (jumlah per page, links, offset)
- [ ] CSRF behavior sama (token, form)
- [ ] XSS protection behavior sama (esc di output)
- [ ] Custom library behavior sama (terutama yg pakai &get_instance())
- [ ] DB migration & seeder jalan (`php spark migrate` tanpa error, seed data benar)
- [ ] Smoke test: akses semua route utama, tidak ada 500/fatal

## Def of done

Migrasi selesai HANYA jika semua checklist di atas tercentang. Bukan "jalan tanpa error", tapi **feature parity**. Setelah ini, jalankan code-quality gate (`output-quality-checklist.md`) — sekunder.
