<?php

// Capture relevant status snapshots without modifying business records.
// Unique event keys make concurrent polling idempotent.
function sinkronNotifikasi(array $user): void
{
  $uid = (int)$user['id_user'];
  $role = $user['role'];
  $sources = [];
  if ($role === 'admin' || $role === 'pemilik') {
    $sources[] = ['verifikasi',
      "SELECT v.id_verifikasi id, v.status, COALESCE(v.tanggal_verifikasi,v.tanggal_pengajuan) tanggal, k.nama_kos nama FROM verifikasi_kos v JOIN kos k ON k.id_kos=v.id_kos WHERE " . ($role === 'admin' ? "v.status='menunggu'" : 'k.id_pemilik=?'),
      'Verifikasi kos', $role === 'admin' ? '/admin/verifikasi' : '/pemilik/kos'];
    $sources[] = ['langganan',
      "SELECT id_pembayaran_langganan id,status,updated_at tanggal,nomor_order nama FROM pembayaran_langganan WHERE " . ($role === 'admin' ? "status='menunggu'" : 'id_pemilik=?'),
      'Pembayaran BetaKos Pro', $role === 'admin' ? '/admin/langganan' : '/pemilik/langganan/riwayat'];
  }
  if ($role === 'pelanggan' || $role === 'pemilik') {
    $sources[] = ['klaim',
      'SELECT c.id_claim id,c.status,c.updated_at tanggal,k.nama_kos nama FROM claim_riwayat c JOIN penghuni p ON p.id_penghuni=c.id_penghuni JOIN kamar km ON km.id_kamar=p.id_kamar JOIN kos k ON k.id_kos=km.id_kos WHERE ' . ($role === 'pemilik' ? 'k.id_pemilik=?' : 'c.id_user=?'),
      'Klaim penghuni', $role === 'pemilik' ? '/pemilik/claim' : '/user/riwayat-kos'];
    $sources[] = ['tagihan',
      'SELECT DISTINCT t.id_tagihan id,t.status,t.updated_at tanggal,t.nomor_tagihan nama FROM tagihan t JOIN kamar km ON km.id_kamar=t.id_kamar JOIN kos k ON k.id_kos=km.id_kos ' . ($role === 'pelanggan' ? 'JOIN tagihan_penghuni tp ON tp.id_tagihan=t.id_tagihan JOIN penghuni p ON p.id_penghuni=tp.id_penghuni WHERE p.id_user=?' : 'WHERE k.id_pemilik=?'),
      'Tagihan kos', $role === 'pemilik' ? '/pemilik/pembayaran' : '/user/kos-saya'];
  }
  if ($role === 'admin' || $role === 'pelanggan') {
    $sources[] = ['laporan',
      'SELECT l.id_laporan id,l.status,l.updated_at tanggal,k.nama_kos nama FROM laporan_kos l JOIN kos k ON k.id_kos=l.id_kos WHERE ' . ($role === 'admin' ? "l.status IN ('menunggu','diproses')" : 'l.id_user=?'),
      'Laporan kos', $role === 'admin' ? '/admin/laporan' : '/user/laporan'];
  }
  $insert = db()->prepare('INSERT INTO notifikasi (id_user,event_key,judul,pesan,tujuan,tanggal) VALUES (?,?,?,?,?,?) ON DUPLICATE KEY UPDATE event_key=VALUES(event_key)');
  foreach ($sources as [$type, $sql, $title, $path]) {
    $stmt = db()->prepare($sql . ' ORDER BY tanggal DESC LIMIT 100');
    if (str_contains($sql, '?')) $stmt->bind_param('i', $uid);
    $stmt->execute();
    foreach ($stmt->get_result() as $row) {
      $key = hash('sha256', $type . ':' . $row['id'] . ':' . $row['status'] . ':' . $row['tanggal']);
      $message = mb_substr($row['nama'] . ' · Status: ' . str_replace('_', ' ', $row['status']) . '.', 0, 500);
      $date = $row['tanggal'];
      $insert->bind_param('isssss', $uid, $key, $title, $message, $path, $date);
      $insert->execute();
    }
    $stmt->close();
  }
  $insert->close();
}
