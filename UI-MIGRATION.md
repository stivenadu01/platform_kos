# BetaKos — Marketplace UI migration

## Scope delivered

Shared visual foundation for all owner, customer, admin, auth and error pages:
warm orange brand, system typography, readable metadata, shared focus styles,
reduced motion, bottom navigation, layer tokens and reproducible Tailwind build.

Page-level changes:

- Owner property/type cards: separate photo from white information and action area;
  settings grouped under an expandable menu, primary management action remains visible.
- Owner resident, billing and history filters: shared mobile bottom sheet, inline desktop filters.
- Owner child/context pages: no bottom navigation; back uses history with module fallback.
- Dashboard setup progress instead of automatic welcome interruption; pending verification
  is not described as already published.
- Public home: shorter introduction and direct category shortcuts. Catalog/favorites use
  explicit responsive columns. Room types/prices appear earlier in property details.
- Admin dashboard: verification queue promoted above aggregate statistics.
- Shared modal keyboard containment, payment green preserved, missing image fallback provided.

Cleanup: removed the owner topbar MutationObserver/back-link lookup, old welcome modal
and its unused methods, unused photo-overlay styles, remote font request, and duplicate
explicit Alpine init calls (Alpine already invokes init methods).

## Navigation continuation

- Public landing and search now share Jelajahi; the unused home view was removed.
- Public primary navigation: Jelajahi, Favorit, Kos Saya, Akun. Owner navigation:
  Beranda, Properti, Penghuni, Keuangan, Akun. Account pages replace the more drawer.
- Property cards open a direct unit workspace, with room search, type and status filters.
  Types/prices and photos remain available in advanced settings.
- Room details combine basic data/status, active residents and bills. Pro-only data
  requests remain conditional and existing API permissions remain enforced.
- Active and historical residents have a dedicated detail view, with associated room bills.
- Advanced menus use the shared ellipsis icon and a visible text label.
- Help is a desktop side panel and mobile full-screen panel. The heading and caption
  stay above the independently scrolling body; the footer stays below it.
- Existing routes remain compatible. Controllers add owner-scoped detail lookup and
  presentation routes; no billing rules or database schema changed in this continuation.

## Build

Run `npm ci`, `npm run check`, then `npm run build`.
`public/assets/css/input.css` is the source; `app.css` is generated and shipped.
Do not edit generated CSS directly. No npm packages are required on the PHP host.

## Verification and limitations

- PHP parser: 132 files; not a substitute for PHP runtime tests.
- JavaScript files, inline view scripts, literal master icon keys: checked.
- Mocked onboarding scenarios: checked.
- Mocked room loading for free/Pro accounts, combined room filters, and route guards:
  checked. These do not replace authentication/database integration tests.
- No database migration is needed. Financial models and payment API logic are unchanged.
- Browser component test is supplied (`npx playwright install chromium --only-shell`,
  then `npm run test:responsive`). It could not run in the delivery environment:
  the browser download was invalid. No visual/device pass is claimed.
- These are shared-theme and targeted flow changes, not evidence that every authenticated
  page and all error/permission states have passed end-to-end testing.

## Release gate — test locally before production

Use 360/390px phones, tablet and 1440px desktop; also enlarged text and keyboard navigation.
Check direct child links, refresh, back fallback, scoped pages, no desktop bottom nav,
long names/prices, empty lists, missing images, sidebar/logout, modal layering and keyboard.

Test: new owner setup → add kos → photos → type/price → rooms → request verification;
new resident → current/upcoming/overdue bills → partial/full payment → history;
customer search/filter → detail → favorite → residence/history;
admin approve/reject; free/paid subscription eligibility and checkout.

The service-worker cache version changed. Allow the new worker to activate and reload;
if the old CSS persists on an installed PWA, close and reopen it.

## Follow-up quality work

Validate the complete page set against real data; tune mobile density after screenshots;
finish replacing remaining local color/layout utility combinations with shared components
only where repeated behavior warrants it. Do not delete legacy routes before checking
external links and backward compatibility.

## Implementasi ruang kerja dan badge — 13 Oktober 2026

- Tema bersama diterapkan melalui workspace-redesign.css pada ketiga layout. Navigasi
  pemilik/admin dikelompokkan berdasarkan pekerjaan; halaman formulir, tabel, profil,
  keuangan dan daftar memakai komponen, fokus keyboard dan hierarki visual yang sama.
- Pencarian publik: hero pencarian, chip filter yang dapat dihapus, ketersediaan kamar,
  kartu satu kolom di ponsel, gambar lazy load, serta reset pada hasil kosong.
- Detail kos: ringkasan harga dari konfigurasi tipe kamar, pintasan tipe/fasilitas/aturan/lokasi,
  kontak pemilik dan tindakan ponsel tetap menggunakan alur yang tersedia.
- Pemilik: pencarian kos dan tab status dengan jumlah; navigasi dalam konteks properti;
  tab status unit dengan jumlah sesuai pencarian/tipe; nomor kamar diurutkan numerik;
  tab penghuni/klaim dan badge pada navigasi desktop, ponsel, serta akun.
- Admin: antrean verifikasi/laporan/bukti pembayaran manual pada ringkasan; panel
  pemeriksaan samping di desktop dan penuh di ponsel; persetujuan melalui pemeriksaan
  data/foto; status verifikasi dan langganan tersimpan di URL; badge status di tab.
- Endpoint GET /api/navigation/counts membutuhkan login dan memakai identitas sesi.
  Hitungan berasal dari query terikat pemilik/pelanggan; jumlah nol disembunyikan pada
  navigasi, tab status tetap menampilkan nol. Pembayaran gateway dan pembayaran manual
  tanpa bukti tidak dihitung sebagai pekerjaan pemeriksaan admin.
- Hitungan diperbarui setiap 30 detik pada tab terlihat, ketika tab kembali aktif,
  dan setelah mutasi API berhasil. Aset memakai hash konten; cache PWA naik ke v13.

Validasi: PHP lint, build Tailwind, pemeriksaan sintaks navigation.js, dan tests/navigation.php
lulus pada PHP XAMPP 8.2.12. Pengujian menggunakan database betakos_test_navigation_* yang
selalu dibersihkan; memeriksa peran, identitas API, isolasi akun dan bukti pembayaran.
Browser: pencarian/filter/reset diuji pada 390px; pencarian pemilik dan status kamar diuji
pada sesi pemilik aktif. Panel admin dan badge diuji memakai data contoh sintetis pada
390px dan desktop. Ini bukan pengujian keputusan admin/pembayaran nyata. Tidak ada
migrasi database baru untuk perubahan UI ini. Halaman pratinjau sementara dibersihkan.
