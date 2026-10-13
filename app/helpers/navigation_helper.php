<?php

function navigationCounts(?array $user): array
{
  if (empty($user['id_user'])) return [];
  $uid=(int)$user['id_user']; $role=$user['role'] ?? '';
  $queries=[];
  if ($role==='admin') {
    $queries=[
      'verification'=>"SELECT COUNT(*) n FROM verifikasi_kos WHERE status='menunggu'",
      'reports'=>"SELECT COUNT(*) n FROM laporan_kos WHERE status IN ('menunggu','diproses')",
      'payments'=>"SELECT COUNT(*) n FROM pembayaran_langganan WHERE status='menunggu' AND COALESCE(provider_pembayaran,'')<>'midtrans' AND COALESCE(bukti_pembayaran,'')<>''"
    ];
  } elseif ($role==='pemilik') {
    $queries=[
      'properties'=>"SELECT COUNT(*) n FROM kos WHERE id_pemilik=? AND status IN ('draft','ditolak')",
      'claims'=>"SELECT COUNT(*) n FROM claim_riwayat c JOIN penghuni p ON p.id_penghuni=c.id_penghuni JOIN kamar km ON km.id_kamar=p.id_kamar JOIN kos k ON k.id_kos=km.id_kos WHERE k.id_pemilik=? AND c.status='menunggu'",
      'overdue'=>"SELECT COUNT(*) n FROM tagihan t JOIN kamar km ON km.id_kamar=t.id_kamar JOIN kos k ON k.id_kos=km.id_kos WHERE k.id_pemilik=? AND t.status IN ('belum_lunas','sebagian') AND t.tanggal_jatuh_tempo<CURDATE()",
      'subscription'=>"SELECT COUNT(*) n FROM pembayaran_langganan WHERE id_pemilik=? AND status='menunggu'"
    ];
  } elseif ($role==='pelanggan') {
    $queries=[
      'favorites'=>"SELECT COUNT(*) n FROM kos_favorit WHERE id_user=?",
      'overdue'=>"SELECT COUNT(DISTINCT t.id_tagihan) n FROM tagihan t JOIN tagihan_penghuni tp ON tp.id_tagihan=t.id_tagihan JOIN penghuni p ON p.id_penghuni=tp.id_penghuni WHERE p.id_user=? AND t.status IN ('belum_lunas','sebagian') AND t.tanggal_jatuh_tempo<CURDATE()",
      'claims'=>"SELECT COUNT(*) n FROM claim_riwayat WHERE id_user=? AND status='menunggu'"
    ];
  }
  $counts=[];
  foreach ($queries as $key=>$sql) {
    $stmt=db()->prepare($sql);
    if (str_contains($sql,'?')) $stmt->bind_param('i',$uid);
    $stmt->execute(); $counts[$key]=(int)($stmt->get_result()->fetch_assoc()['n'] ?? 0); $stmt->close();
  }
  return $counts;
}

function initialNavigationCounts(): array
{
  static $counts=null;
  if ($counts!==null) return $counts;
  try { return $counts=navigationCounts($_SESSION['user'] ?? null); }
  catch (Throwable $e) { error_log('BetaKos navigation counts unavailable'); return $counts=[]; }
}

function navigationBadge(string $key, string $label, bool $informational=false): string
{
  if (empty($_SESSION['user']) || !preg_match('/^[a-z]+$/D',$key)) return '';
  $label=htmlspecialchars($label,ENT_QUOTES,'UTF-8');
  $count='$store.navigation.counts.'.$key;
  return '<span x-data x-cloak class="bk-nav-badge'.($informational?' is-info':'').'" x-show="('.$count.' || 0) > 0" x-text="'.$count.' > 99 ? \'99+\' : '.$count.'" :aria-label="('.$count.' || 0) + \' '.$label.'\'"></span>';
}
