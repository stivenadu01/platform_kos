<?php

function getPemilikOnboardingStatus($id_pemilik)
{
  $conn = db();
  $id_pemilik = (int)$id_pemilik;

  $stmt = $conn->prepare("SELECT nama, email, no_hp FROM users WHERE id_user = ? LIMIT 1");
  $stmt->bind_param('i', $id_pemilik);
  $stmt->execute();
  $user = $stmt->get_result()->fetch_assoc() ?: [];
  $stmt->close();
  $profileComplete = trim((string)($user['nama'] ?? '')) !== ''
    && filter_var($user['email'] ?? '', FILTER_VALIDATE_EMAIL)
    && trim((string)($user['no_hp'] ?? '')) !== '';

  // Satu query menentukan kos fokus beserta kelengkapan utamanya. Semua tahap
  // berikutnya selalu diperiksa pada kos yang sama agar progres tidak tercampur.
  $stmt = $conn->prepare("SELECT k.id_kos, k.nama_kos, k.status,
      EXISTS(SELECT 1 FROM kos_foto f WHERE f.id_kos = k.id_kos) AS has_photo,
      EXISTS(SELECT 1 FROM verifikasi_kos v WHERE v.id_kos = k.id_kos AND v.status IN ('menunggu','disetujui')) AS has_submission
    FROM kos k WHERE k.id_pemilik = ?
    ORDER BY (k.status IN ('menunggu_verifikasi','aktif')) ASC, k.id_kos ASC
    LIMIT 1");
  $stmt->bind_param('i', $id_pemilik);
  $stmt->execute();
  $focusKos = $stmt->get_result()->fetch_assoc() ?: null;
  $stmt->close();

  $kosId = (int)($focusKos['id_kos'] ?? 0);
  $kosComplete = $kosId > 0 && (int)($focusKos['has_photo'] ?? 0) === 1;
  $verificationComplete = $kosId > 0 && (
    in_array((string)($focusKos['status'] ?? ''), ['menunggu_verifikasi', 'aktif'], true)
    || (int)($focusKos['has_submission'] ?? 0) === 1
  );

  $focusType = null;
  if ($kosId > 0) {
    $stmt = $conn->prepare("SELECT t.id_tipe_kamar, t.nama_tipe,
        EXISTS(SELECT 1 FROM harga_kamar h WHERE h.id_tipe_kamar = t.id_tipe_kamar) AS has_price,
        EXISTS(SELECT 1 FROM tipe_kamar_foto f WHERE f.id_tipe_kamar = t.id_tipe_kamar) AS has_photo,
        EXISTS(SELECT 1 FROM kamar km WHERE km.id_tipe_kamar = t.id_tipe_kamar) AS has_room
      FROM tipe_kamar t WHERE t.id_kos = ?
      ORDER BY (has_price = 1 AND has_photo = 1) DESC, t.id_tipe_kamar ASC
      LIMIT 1");
    $stmt->bind_param('i', $kosId);
    $stmt->execute();
    $focusType = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();
  }

  $typeComplete = $focusType
    && (int)$focusType['has_price'] === 1
    && (int)$focusType['has_photo'] === 1;
  $roomComplete = $focusType && (int)($focusType['has_room'] ?? 0) === 1;
  $typeId = (int)($focusType['id_tipe_kamar'] ?? 0);

  $kosAction = $kosId <= 0
    ? ['/pemilik/kos/tambah', 'Tambah Kos']
    : (!$kosComplete ? ['/pemilik/kos/foto?id=' . $kosId, 'Tambah Foto Kos'] : ['/pemilik/kos?id=' . $kosId, 'Lihat Kos']);

  if ($typeId <= 0) {
    $typeAction = ['/pemilik/tipe-kamar/tambah?id_kos=' . $kosId, 'Buat Tipe Kamar'];
  } elseif (!(int)$focusType['has_price']) {
    $typeAction = ['/pemilik/tipe-kamar/edit?id_tipe_kamar=' . $typeId, 'Lengkapi Harga'];
  } elseif (!(int)$focusType['has_photo']) {
    $typeAction = ['/pemilik/tipe-kamar/foto?id_tipe_kamar=' . $typeId, 'Tambah Foto Kamar'];
  } else {
    $typeAction = ['/pemilik/kamar?id_kos=' . $kosId . '&context=kos', 'Lihat Tipe Kamar'];
  }

  $roomAction = $typeId > 0
    ? ['/pemilik/kamar/kelola?id_tipe_kamar=' . $typeId, 'Tambah Unit Kamar']
    : ['/pemilik/kamar?id_kos=' . $kosId . '&context=kos', 'Buka Tipe Kamar'];

  $steps = [
    ['key'=>'profil','label'=>'Profil Pemilik','description'=>'Lengkapi nama dan nomor HP aktif.','complete'=>$profileComplete,'action_url'=>'/pemilik/profil','action_label'=>'Lengkapi Profil'],
    ['key'=>'kos','label'=>'Informasi Kos','description'=>'Tambahkan data dan minimal satu foto kos.','complete'=>$kosComplete,'action_url'=>$kosAction[0],'action_label'=>$kosAction[1]],
    ['key'=>'tipe_kamar','label'=>'Tipe Kamar','description'=>'Atur kapasitas, harga, dan foto kamar.','complete'=>$typeComplete,'action_url'=>$typeAction[0],'action_label'=>$typeAction[1]],
    ['key'=>'kamar','label'=>'Unit Kamar','description'=>'Masukkan minimal satu nomor kamar.','complete'=>$roomComplete,'action_url'=>$roomAction[0],'action_label'=>$roomAction[1]],
    ['key'=>'verifikasi','label'=>'Verifikasi Kos','description'=>'Kirim kos untuk diperiksa admin.','complete'=>$verificationComplete,'action_url'=>'/pemilik/kos','action_label'=>'Ajukan Verifikasi'],
  ];

  $completed = count(array_filter($steps, static fn($step) => $step['complete']));
  $next = null;
  foreach ($steps as $step) {
    if (!$step['complete']) { $next = $step; break; }
  }

  return [
    'completed'=>$completed,
    'total'=>count($steps),
    'percent'=>(int)round(($completed / count($steps)) * 100),
    'complete'=>$next === null,
    'steps'=>$steps,
    'next'=>$next,
    'focus'=>['id_kos'=>$kosId ?: null,'nama_kos'=>$focusKos['nama_kos'] ?? null,'id_tipe_kamar'=>$typeId ?: null],
  ];
}
