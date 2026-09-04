# Midtrans QRIS BetaKos

## 1. Database

Jalankan migration setelah migration subscription/payment Phase 3 dan konfigurasi metode pembayaran Phase 5:

`app/migrations/phase5_midtrans_qris.sql`

Migration ini non-destructive. Data langganan dan pembayaran manual yang sudah ada tetap dipertahankan.

## 2. Environment

Tambahkan di `.env` server/development:

```env
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxxxxxxxxxxxxx
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_API_URL=https://api.sandbox.midtrans.com
```

Untuk production gunakan Server Key Production dan endpoint Production. Jangan pernah menaruh Server Key di JavaScript/frontend atau repository.

## 3. Notification URL

Di Midtrans Dashboard, atur HTTP Notification URL ke:

`https://DOMAIN-BETAKOS/api/payment/midtrans/notification`

Endpoint ini memang public karena dipanggil server Midtrans. Endpoint tidak memakai session/CSRF; keamanan utamanya adalah signature verification dan validasi order + nominal di database.

## 4. Flow

Owner memilih paket → backend menentukan nominal dari database → BetaKos membuat order lokal → Midtrans membuat Dynamic QRIS → QR ditampilkan → pembayaran diterima → Midtrans mengirim notification → BetaKos memverifikasi signature dan nominal → pembayaran ditandai terverifikasi → subscription diaktifkan/diperpanjang.

Status dari gateway selain settlement tidak mengaktifkan Pro. Notification yang sama dapat dikirim ulang tanpa mengaktifkan subscription dua kali.

## 5. Fallback

Transfer bank/e-wallet manual tetap tersedia. QRIS tidak menggantikan histori atau alur pembayaran manual yang sudah ada.
