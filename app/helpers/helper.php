<?php

function model($name)
{
  $name = ucfirst($name);
  require_once ROOT_PATH . "/app/models/{$name}.php";
}

function view($page, $data = [], $layout = 'user')
{
  extract($data);

  ob_start();
  require ROOT_PATH . "/app/views/pages/$page.php";
  $content = ob_get_clean();

  require ROOT_PATH . "/app/views/layouts/$layout.php";
}


require_once __DIR__ . '/request_helper.php';
require_once __DIR__ . '/response_helper.php';
require_once __DIR__ . '/phone_helper.php';


/**
 * Safely embed JSON into HTML/JavaScript contexts.
 * Hex-encodes HTML-sensitive characters to prevent quote/tag breakout.
 */
function json_encode_safe($value, $flags = 0)
{
  return json_encode(
    $value,
    $flags | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
  );
}

function publicPrimaryNavigationPaths()
{
  return ['/', '/cari-kos', '/user/favorit', '/user/kos-saya'];
}

function isPublicPrimaryNavigationPath($path)
{
  return in_array((string)$path, publicPrimaryNavigationPaths(), true);
}

require_once __DIR__ . '/location_helper.php';

function masterIconCatalog()
{
  return [
    // Fasilitas konektivitas & hiburan
    'wifi' => ['label' => 'WiFi', 'path' => '<path d="M5 12.55a11 11 0 0 1 14.08 0"/><path d="M8.5 16.05a6 6 0 0 1 7 0"/><path d="M12 19.5h.01"/>'],
    'router' => ['label' => 'Router', 'path' => '<rect x="3" y="9" width="18" height="8" rx="2"/><path d="M7 13h.01M11 13h.01M15 13h.01M19 13h.01"/><path d="M12 9V5M9 5h6"/>'],
    'tv' => ['label' => 'Televisi', 'path' => '<rect x="3" y="5" width="18" height="13" rx="2"/><path d="m8 21 4-3 4 3"/>'],
    'monitor' => ['label' => 'Monitor', 'path' => '<rect x="3" y="4" width="18" height="14" rx="2"/><path d="M8 21h8M12 18v3"/>'],
    'speaker' => ['label' => 'Speaker', 'path' => '<rect x="6" y="3" width="12" height="18" rx="2"/><circle cx="12" cy="14" r="3"/><path d="M9 7h6"/>'],

    // Fasilitas kamar
    'bed' => ['label' => 'Tempat tidur', 'path' => '<path d="M4 18v-7a2 2 0 0 1 2-2h4a3 3 0 0 1 3 3v1h5a2 2 0 0 1 2 2v3"/><path d="M4 14h16M4 18v3M20 18v3"/>'],
    'bed-double' => ['label' => 'Kasur / Bed', 'path' => '<path d="M3 18v-7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v7"/><path d="M3 14h18M6 9V7M18 9V7M3 18v3M21 18v3"/>'],
    'lamp' => ['label' => 'Lampu', 'path' => '<path d="M9 21h6M10 17h4M8 13a6 6 0 1 1 8 0c-1.5 1-2 2-2 4h-4c0-2-.5-3-2-4Z"/>'],
    'ac' => ['label' => 'AC / Pendingin', 'path' => '<rect x="3" y="5" width="18" height="7" rx="1"/><path d="M7 16c1 0 1 2 0 3M12 16c1 0 1 2 0 3M17 16c1 0 1 2 0 3M6 9h12"/>'],
    'fan' => ['label' => 'Kipas', 'path' => '<circle cx="12" cy="12" r="2"/><path d="M12 10c-1-4 1-7 4-7 2 0 3 2 2 4-1 2-3 3-6 3ZM10 12c-4-1-7 1-7 4 0 2 2 3 4 2 2-1 3-3 3-6ZM12 14c1 4-1 7-4 7-2 0-3-2-2-4 1-2 3-3 6-3ZM14 12c4 1 7-1 7-4 0-2-2-3-4-2-2 1-3 3-3 6Z"/>'],
    'door-open' => ['label' => 'Pintu', 'path' => '<path d="M13 4h6v16h-6"/><path d="M13 12H3"/><path d="m7 8-4 4 4 4"/>'],
    'key-round' => ['label' => 'Kunci', 'path' => '<circle cx="7.5" cy="15.5" r="4.5"/><path d="m11 12 9-9M17 6l3 3M14 9l3 3"/>'],
    'lock' => ['label' => 'Kunci / Keamanan', 'path' => '<rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>'],
    'wardrobe' => ['label' => 'Lemari', 'path' => '<rect x="4" y="3" width="16" height="18" rx="1"/><path d="M12 3v18M9 12h.01M15 12h.01"/>'],
    'archive' => ['label' => 'Lemari / Penyimpanan', 'path' => '<rect x="3" y="4" width="18" height="4" rx="1"/><path d="M5 8v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V8M10 12h4"/>'],
    'table' => ['label' => 'Meja', 'path' => '<path d="M3 8h18M5 8v12M19 8v12M8 4h8v4H8z"/>'],
    'chair' => ['label' => 'Kursi', 'path' => '<path d="M6 10V4h12v6M5 10h14a2 2 0 0 1 2 2v3H3v-3a2 2 0 0 1 2-2ZM6 15v5M18 15v5"/>'],
    'sofa' => ['label' => 'Ruang tamu / Sofa', 'path' => '<path d="M5 11V9a3 3 0 0 1 3-3h8a3 3 0 0 1 3 3v2"/><path d="M4 11a2 2 0 0 0-2 2v4h20v-4a2 2 0 0 0-2-2"/><path d="M4 17v3M20 17v3M6 11h12v6H6z"/>'],
    'armchair' => ['label' => 'Kursi santai', 'path' => '<path d="M7 10V7a5 5 0 0 1 10 0v3"/><path d="M5 10h14a2 2 0 0 1 2 2v5H3v-5a2 2 0 0 1 2-2ZM6 17v3M18 17v3"/>'],

    // Kamar mandi & air
    'bath' => ['label' => 'Kamar mandi', 'path' => '<path d="M4 12h16"/><path d="M5 12v3a7 7 0 0 0 14 0v-3"/><path d="M7 5a4 4 0 0 1 8 0v2"/><path d="M4 12h16"/>'],
    'shower' => ['label' => 'Shower', 'path' => '<path d="M6 4a4 4 0 0 1 8 0v3"/><path d="M4 7h16"/><path d="M6 7v5a6 6 0 0 0 12 0V7"/><path d="M8 17h.01M12 19h.01M16 17h.01"/>'],
    'droplets' => ['label' => 'Air', 'path' => '<path d="M12 2.7s6 6.1 6 11a6 6 0 0 1-12 0c0-4.9 6-11 6-11Z"/><path d="M8 15a4 4 0 0 0 4 4"/>'],
    'water' => ['label' => 'Air Bersih', 'path' => '<path d="M4 10h16M6 10v8a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-8"/><path d="M9 6a3 3 0 0 1 6 0v4M12 3v3"/><path d="M9 15h6"/>'],
    'dispenser' => ['label' => 'Dispenser', 'path' => '<path d="M7 4h10v5H7z"/><path d="M9 9v11M15 9v11"/><path d="M7 20h10M10 4V2h4v2M12 12h.01"/>'],

    // Dapur
    'kitchen' => ['label' => 'Dapur', 'path' => '<path d="M4 3v8a3 3 0 0 0 6 0V3M7 3v18M17 3v18M17 3c3 2 3 6 0 8"/>'],
    'utensils' => ['label' => 'Peralatan makan', 'path' => '<path d="M7 2v8"/><path d="M4 2v8a3 3 0 0 0 6 0V2"/><path d="M7 13v9"/><path d="M17 2v20"/><path d="M17 2c3 2 3 6 0 8"/>'],
    'cooking-pot' => ['label' => 'Peralatan memasak', 'path' => '<path d="M5 8h14"/><path d="M7 8v10a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V8"/><path d="M9 4h6M12 4v4M3 8h18"/>'],
    'flame' => ['label' => 'Kompor / Gas', 'path' => '<path d="M12 3c1.5 3 5 4.5 5 9a5 5 0 0 1-10 0c0-2.5 1.5-4.5 3-6 .5 2 2 2.5 2 4 1-1.5 1.5-3 0-7Z"/>'],
    'refrigerator' => ['label' => 'Kulkas', 'path' => '<rect x="5" y="2" width="14" height="20" rx="2"/><path d="M5 10h14M9 6v2M9 14v4"/>'],
    'microwave' => ['label' => 'Microwave', 'path' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M7 9h7v6H7z"/><path d="M17 9h.01M17 12h.01M17 15h.01"/>'],
    'coffee' => ['label' => 'Minuman / Kopi', 'path' => '<path d="M4 8h13v6a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4V8Z"/><path d="M17 10h1a3 3 0 0 1 0 6h-1M7 4c0 1 1 1 1 2M11 4c0 1 1 1 1 2"/>'],

    // Laundry & utilitas
    'washing-machine' => ['label' => 'Mesin cuci', 'path' => '<rect x="4" y="3" width="16" height="18" rx="2"/><circle cx="12" cy="13" r="4"/><path d="M8 6h.01M11 6h.01M14 6h.01"/>'],
    'laundry' => ['label' => 'Laundry', 'path' => '<path d="M5 9h14l-1 11H6L5 9Z"/><path d="M8 9V6h8v3M8 13h8M10 16h4"/>'],
    'shirt' => ['label' => 'Pakaian / Jemur', 'path' => '<path d="m8 5 4 2 4-2 4 3-3 4-2-1v9H9v-9l-2 1-3-4 4-3Z"/>'],
    'zap' => ['label' => 'Listrik', 'path' => '<path d="m13 2-9 12h7l-1 8 9-12h-7l1-8Z"/>'],
    'battery-charging' => ['label' => 'Daya / Listrik', 'path' => '<rect x="5" y="7" width="14" height="12" rx="2"/><path d="M9 4h6M12 10l-2 4h3l-1 3 3-4h-3l1-3Z"/>'],
    'wind' => ['label' => 'Ventilasi', 'path' => '<path d="M9.5 4A2.5 2.5 0 1 1 12 6.5H3M5 9h12a2.5 2.5 0 1 1-2.5 2.5H13M9.5 14H4a2.5 2.5 0 1 0 2.5 2.5H9"/>'],
    'sun' => ['label' => 'Cahaya Matahari', 'path' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>'],
    'balcony' => ['label' => 'Balkon', 'path' => '<path d="M4 21h16M6 21V9h12v12M3 9h18M8 9V5h8v4M9 13v8M15 13v8"/>'],
    'curtain' => ['label' => 'Gorden / Tirai', 'path' => '<path d="M4 3h16M5 3v18M19 3v18M3 21h18"/><path d="M8 4c-1 4-1 8 1 11M16 4c1 4 1 8-1 11"/>'],
    'clothesline' => ['label' => 'Ruang jemur', 'path' => '<path d="M3 7h18M6 7v10M18 7v10"/><path d="M8 11h8M8 14h8"/><path d="M9 11v3M12 11v3M15 11v3"/>'],
    'tree-pine' => ['label' => 'Taman', 'path' => '<path d="m12 2-4 7h3l-4 6h4l-3 5h8l-3-5h4l-4-6h3l-4-7Z"/><path d="M12 20v2"/>'],
    'palmtree' => ['label' => 'Area outdoor', 'path' => '<path d="M12 22V10"/><path d="M12 10C8 8 5 5 5 2c3 0 6 2 7 5"/><path d="M12 10c4-2 7-5 7-8-3 0-6 2-7 5"/><path d="M12 10C9 6 5 5 2 6c1 3 5 5 10 4"/><path d="M12 10c3-4 7-5 10-4-1 3-5 5-10 4"/>'],

    // Parkir & keamanan
    'bike' => ['label' => 'Parkir motor / Sepeda', 'path' => '<circle cx="5.5" cy="17.5" r="3.5"/><circle cx="18.5" cy="17.5" r="3.5"/><path d="M5.5 17.5 9 9h4l5.5 8.5M9 9l3 8.5M13 9h3l2 3"/>'],
    'car' => ['label' => 'Parkir mobil', 'path' => '<path d="M5 17h14l1-5-2-5H6l-2 5 1 5Z"/><path d="M4 17v3M20 17v3M7 17h.01M17 17h.01"/>'],
    'car-front' => ['label' => 'Mobil', 'path' => '<path d="m5 17 1-7 2-4h8l2 4 1 7"/><path d="M5 17h14M7 13h10M8 17v3M16 17v3M8 9h8"/>'],
    'parking' => ['label' => 'Parkir', 'path' => '<path d="M7 21V3h6a4 4 0 0 1 0 8H7"/><path d="M3 21h18"/>'],
    'camera' => ['label' => 'CCTV', 'path' => '<path d="m4 8 11-4 4 4-11 4-4-4Z"/><path d="M8 12v4M15 9l3 8M7 20h10"/>'],
    'shield-check' => ['label' => 'Keamanan', 'path' => '<path d="M12 3 20 6v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6l8-3Z"/><path d="m9 12 2 2 4-4"/>'],
    'shield' => ['label' => 'Perlindungan', 'path' => '<path d="M12 3 20 6v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6l8-3Z"/>'],
    'credit-card' => ['label' => 'Kartu akses', 'path' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M7 15h4"/>'],
    'user-check' => ['label' => 'Penjaga / Verifikasi', 'path' => '<circle cx="9" cy="8" r="4"/><path d="M3 21a6 6 0 0 1 12 0M16 11l2 2 4-4"/>'],
    'users-round' => ['label' => 'Penghuni / Grup', 'path' => '<path d="M18 21a6 6 0 0 0-12 0"/><circle cx="12" cy="8" r="4"/><path d="M22 19a5 5 0 0 0-4-4.9M18 4.1a4 4 0 0 1 0 7.8"/>'],
    'user-round' => ['label' => 'Penghuni', 'path' => '<circle cx="12" cy="8" r="4"/><path d="M5 21a7 7 0 0 1 14 0"/>'],
    'user-male' => ['label' => 'Putra', 'path' => '<circle cx="12" cy="7" r="4"/><path d="M5 21a7 7 0 0 1 14 0"/><path d="M17 3h4v4"/><path d="m21 3-5 5"/>'],
    'user-female' => ['label' => 'Putri', 'path' => '<circle cx="12" cy="7" r="4"/><path d="M5 21a7 7 0 0 1 14 0"/><path d="M9 14h6M12 14v5"/>'],
    'user-plus' => ['label' => 'Tamu / Penghuni', 'path' => '<circle cx="9" cy="8" r="4"/><path d="M3 21a6 6 0 0 1 12 0M19 8v6M16 11h6"/>'],

    // Kebersihan
    'sparkles' => ['label' => 'Kebersihan', 'path' => '<path d="m12 3-1.5 5.5L5 10l5.5 1.5L12 17l1.5-5.5L19 10l-5.5-1.5L12 3Z"/><path d="m19 16-.7 2.3L16 19l2.3.7L19 22l.7-2.3L22 19l-2.3-.7L19 16Z"/>'],
    'trash-2' => ['label' => 'Tempat sampah', 'path' => '<path d="M3 6h18M8 6V4h8v2M19 6l-1 15H6L5 6M10 11v6M14 11v6"/>'],
    'pencil' => ['label' => 'Edit', 'path' => '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z"/>'],
    'plus' => ['label' => 'Tambah', 'path' => '<path d="M12 5v14M5 12h14"/>'],
    'x' => ['label' => 'Tutup', 'path' => '<path d="m18 6-12 12M6 6l12 12"/>'],
    'eye' => ['label' => 'Lihat detail', 'path' => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>'],
    'recycle' => ['label' => 'Daur ulang', 'path' => '<path d="m7 19-4-4 4-4M3 15h10a4 4 0 0 0 3.5-2M17 5l4 4-4 4M21 9H11a4 4 0 0 0-3.5 2M9 3l-4 4 4 4M5 7l5 0a4 4 0 0 1 3.5 2"/>'],

    // Aturan / ketentuan
    'heart-off' => ['label' => 'Tidak menerima pasangan', 'path' => '<path d="M10.7 5.2A5 5 0 0 0 4 6.8c-2.4 3.5 0 7 8 13.2 2-1.6 3.6-3 4.8-4.3"/><path d="M17.3 14.8C22 11 22.7 8.5 21 6.2a5 5 0 0 0-7.5-1"/><path d="m3 3 18 18"/>'],
    'ban' => ['label' => 'Dilarang', 'path' => '<circle cx="12" cy="12" r="9"/><path d="m5.6 5.6 12.8 12.8"/>'],
    'volume-x' => ['label' => 'Larangan berisik', 'path' => '<path d="M11 5 6 9H3v6h3l5 4V5Z"/><path d="m16 9 5 6M21 9l-5 6"/>'],
    'moon-off' => ['label' => 'Jam malam', 'path' => '<path d="M12 3a9 9 0 0 0 9 9 9 9 0 0 1-14.5 7.2"/><path d="M3 3l18 18"/>'],
    'paw-print' => ['label' => 'Hewan peliharaan', 'path' => '<path d="M8 12c-2 0-4 2-4 4 0 2 2 3 4 2l2-1h4l2 1c2 1 4 0 4-2 0-2-2-4-4-4-1-4-7-4-8 0Z"/><circle cx="6.5" cy="7" r="2"/><circle cx="17.5" cy="7" r="2"/><circle cx="10" cy="4.5" r="2"/><circle cx="14" cy="4.5" r="2"/>'],
    'clock-3' => ['label' => 'Jam / Waktu', 'path' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>'],
    'calendar-days' => ['label' => 'Jadwal', 'path' => '<rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 10h18M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01M16 18h.01"/>'],
    'clipboard-check' => ['label' => 'Aturan / Ketentuan', 'path' => '<rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4V2h6v2M9 13l2 2 4-4"/>'],
    'info' => ['label' => 'Informasi', 'path' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>'],
    'alert-circle' => ['label' => 'Peringatan', 'path' => '<circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/>'],
    'check-circle-2' => ['label' => 'Tersedia / Selesai', 'path' => '<path d="M21 12a9 9 0 1 1-3-6.7"/><path d="m9 12 2 2 4-4"/>'],
    'circle-x' => ['label' => 'Tidak tersedia', 'path' => '<circle cx="12" cy="12" r="9"/><path d="m15 9-6 6M9 9l6 6"/>'],
    'circle-help' => ['label' => 'Bantuan', 'path' => '<circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.5 2.5 0 1 1 4 2c-1.2.8-1.5 1.2-1.5 2.5M12 17h.01"/>'],

    // Lokasi & lingkungan
    'map-pin' => ['label' => 'Lokasi', 'path' => '<path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>'],
    'map' => ['label' => 'Peta', 'path' => '<path d="m3 6 6-3 6 3 6-3v15l-6 3-6-3-6 3V6Z"/><path d="M9 3v15M15 6v15"/>'],
    'navigation' => ['label' => 'Navigasi', 'path' => '<polygon points="3 11 21 3 13 21 11 13 3 11"/>'],
    'building-2' => ['label' => 'Gedung', 'path' => '<path d="M6 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16M3 21h18M9 7h2M13 7h2M9 11h2M13 11h2M9 15h2M13 15h2"/>'],
    'graduation-cap' => ['label' => 'Kampus', 'path' => '<path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c3.5 2.2 8.5 2.2 12 0v-5"/><path d="M22 10v6"/>'],
    'school' => ['label' => 'Sekolah', 'path' => '<path d="m4 6 8-4 8 4-8 4-8-4Z"/><path d="M6 9v6l6 3 6-3V9M20 8v7"/>'],
    'hospital' => ['label' => 'Rumah sakit', 'path' => '<path d="M3 21h18"/><path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"/><path d="M10 7h4M12 5v4M9 21v-5h6v5"/>'],
    'pill' => ['label' => 'Apotek / Kesehatan', 'path' => '<path d="m10.5 20.5 9-9a4.24 4.24 0 0 0-6-6l-9 9a4.24 4.24 0 0 0 6 6Z"/><path d="m8.5 8.5 7 7M6 12h6M9 9v6"/>'],
    'store' => ['label' => 'Toko / Minimarket', 'path' => '<path d="M3 9h18l-2-5H5L3 9Z"/><path d="M5 9v11h14V9M9 20v-6h6v6"/>'],
    'shopping-basket' => ['label' => 'Toko / Belanja', 'path' => '<path d="m3 9 2 11h14l2-11Z"/><path d="M7 9a5 5 0 0 1 10 0"/><path d="M9 13v4M12 13v4M15 13v4"/>'],
    'shopping-bag' => ['label' => 'Pusat perbelanjaan', 'path' => '<path d="M6 8h12l1 13H5L6 8Z"/><path d="M9 8V5a3 3 0 0 1 6 0v3"/>'],
    'utensils-crossed' => ['label' => 'Rumah makan', 'path' => '<path d="m16 2-4 4 8 8"/><path d="M14 10 5 19a2 2 0 0 0 3 3l9-9"/><path d="m2 2 20 20M8 2v6M5 2v6a3 3 0 0 0 6 0V2"/>'],
    'plane' => ['label' => 'Transportasi', 'path' => '<path d="m2 12 20-7-7 20-3-9-10-4Z"/><path d="M12 16 9 13"/>'],
    'bus' => ['label' => 'Bus', 'path' => '<path d="M6 17h12M6 5h12a2 2 0 0 1 2 2v10H4V7a2 2 0 0 1 2-2Z"/><path d="M4 10h16M7 20v-3M17 20v-3M7 14h.01M17 14h.01"/>'],
    'train-front' => ['label' => 'Kereta', 'path' => '<rect x="5" y="3" width="14" height="16" rx="2"/><path d="M5 11h14M9 19l-2 3M15 19l2 3M8 7h.01M16 7h.01M8 15h.01M16 15h.01"/>'],
    'footprints' => ['label' => 'Jarak / Berjalan', 'path' => '<path d="M4 16c1.5 0 3-1.5 3-3.5S5.5 9 4 9s-2.5 1.5-2.5 3.5S2.5 16 4 16ZM14 22c1.5 0 3-1.5 3-3.5S15.5 15 14 15s-2.5 1.5-2.5 3.5S12.5 22 14 22Z"/><path d="M7 9c1-2 2-4 4-4s3 1.5 3 3M12 14c1-2 2-4 4-4s3 1.5 3 3"/>'],

    // Aktivitas & pendukung
    'briefcase' => ['label' => 'Area kerja', 'path' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 12h18M10 12v2h4v-2"/>'],
    'book-open' => ['label' => 'Belajar', 'path' => '<path d="M2 4.5A2.5 2.5 0 0 1 4.5 2H11v18H4.5A2.5 2.5 0 0 1 2 17.5v-13ZM22 4.5A2.5 2.5 0 0 0 19.5 2H13v18h6.5a2.5 2.5 0 0 0 2.5-2.5v-13Z"/>'],

    // Navigasi & antarmuka aplikasi
    'home' => ['label' => 'Beranda', 'path' => '<path d="m3 11 9-8 9 8"/><path d="M5 10v10h14V10M9 20v-6h6v6"/>'],
    'search' => ['label' => 'Cari', 'path' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>'],
    'filter' => ['label' => 'Filter', 'path' => '<path d="M4 5h16l-6 7v5l-4 2v-7L4 5Z"/>'],
    'heart' => ['label' => 'Favorit', 'path' => '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z"/>'],
    'share-2' => ['label' => 'Bagikan', 'path' => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 10.5 6.8-4M8.6 13.5l6.8 4"/>'],
    'history' => ['label' => 'Riwayat', 'path' => '<path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5M12 7v5l3 2"/>'],
    'star' => ['label' => 'Langganan', 'path' => '<path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9L12 3Z"/>'],
    'log-out' => ['label' => 'Keluar', 'path' => '<path d="M10 17l5-5-5-5"/><path d="M15 12H3"/><path d="M15 4h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-4"/>'],
    'menu' => ['label' => 'Menu', 'path' => '<path d="M4 6h16M4 12h16M4 18h16"/>'],
    'smartphone' => ['label' => 'Aplikasi', 'path' => '<rect x="6" y="2" width="12" height="20" rx="2"/><path d="M10 18h4"/>'],
    'image' => ['label' => 'Foto', 'path' => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="2"/><path d="m21 15-4.5-4.5L5 21"/>'],
    'wallet' => ['label' => 'Pembayaran', 'path' => '<path d="M4 6h14a2 2 0 0 1 2 2v10H4a2 2 0 0 1-2-2V6a3 3 0 0 1 3-3h12"/><path d="M16 12h4"/>'],
    'chevron-right' => ['label' => 'Lanjut', 'path' => '<path d="m9 18 6-6-6-6"/>'],
    'chevron-left' => ['label' => 'Kembali', 'path' => '<path d="m15 18-6-6 6-6"/>'],
    'chevron-down' => ['label' => 'Buka', 'path' => '<path d="m6 9 6 6 6-6"/>'],
    'arrow-left' => ['label' => 'Kembali', 'path' => '<path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>'],
    'plus-circle' => ['label' => 'Tambah', 'path' => '<circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/>'],
    'chart-column' => ['label' => 'Dashboard', 'path' => '<path d="M4 20V10h4v10M10 20V4h4v16M16 20v-7h4v7M2 20h20"/>'],
    'flag' => ['label' => 'Laporan', 'path' => '<path d="M5 21V4"/><path d="M5 5h11l-1 4 3 3H5"/>'],
    'folder-cog' => ['label' => 'Data master', 'path' => '<path d="M3 7h7l2 2h9v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/><circle cx="15" cy="14" r="2"/><path d="M15 10v1M15 17v1M11 14h1M18 14h1"/>'],
    'settings' => ['label' => 'Pengaturan', 'path' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1-2.8 2.8-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.6v.2h-4V21a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1L4.2 17l.1-.1a1.7 1.7 0 0 0 .3-1.9A1.7 1.7 0 0 0 3 14H2.8v-4H3a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9L4.2 7 7 4.2l.1.1a1.7 1.7 0 0 0 1.9.3A1.7 1.7 0 0 0 10 3V2.8h4V3a1.7 1.7 0 0 0 1 1.6 1.7 1.7 0 0 0 1.9-.3l.1-.1L19.8 7l-.1.1a1.7 1.7 0 0 0-.3 1.9 1.7 1.7 0 0 0 1.6 1h.2v4H21a1.7 1.7 0 0 0-1.6 1Z"/>'],
    'refresh-cw' => ['label' => 'Perbarui', 'path' => '<path d="M20 7v5h-5"/><path d="M4 17v-5h5"/><path d="M6.1 8A7 7 0 0 1 18 6l2 6M18 16a7 7 0 0 1-12 2l-2-6"/>'],
  ];
}

function masterIconKeys()
{
  return array_keys(masterIconCatalog());
}

function masterIconLabel($icon)
{
  $catalog = masterIconCatalog();
  return $catalog[$icon]['label'] ?? ucfirst(str_replace('-', ' ', (string)$icon));
}

function masterIconSvg($icon, $class = 'h-5 w-5')
{
  $catalog = masterIconCatalog();
  $path = $catalog[$icon]['path'] ?? $catalog['map-pin']['path'];
  return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="'.htmlspecialchars($class, ENT_QUOTES, 'UTF-8').'" aria-hidden="true">'.$path.'</svg>';
}
