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
  $profileComplete = trim((string)($user['nama'] ?? '')) !== '' && filter_var($user['email'] ?? '', FILTER_VALIDATE_EMAIL) && trim((string)($user['no_hp'] ?? '')) !== '';

  $stmt = $conn->prepare("SELECT id_kos, status FROM kos WHERE id_pemilik = ? ORDER BY id_kos ASC");
  $stmt->bind_param('i', $id_pemilik);
  $stmt->execute();
  $kosRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
  $kosComplete = false;
  $kosSetup = ['has_any' => count($kosRows) > 0, 'incomplete_id' => null, 'missing' => []];
  if (count($kosRows) > 0) {
    foreach ($kosRows as $row) {
      $kosId = (int)$row['id_kos'];
      $stmt = $conn->prepare("SELECT EXISTS(SELECT 1 FROM kos_foto WHERE id_kos = ?) AS has_photo");
      if (!$stmt) {
        // Fallback hanya bila instalasi lama belum memiliki tabel foto_kos.
        $hasPhoto = false;
      } else {
        $stmt->bind_param('i', $kosId);
        $stmt->execute();
        $checkPhoto = $stmt->get_result()->fetch_assoc() ?: [];
        $stmt->close();
        $hasPhoto = (int)($checkPhoto['has_photo'] ?? 0) === 1;
      }
      if ($hasPhoto) { $kosComplete = true; break; }
      if ($kosSetup['incomplete_id'] === null) {
        $kosSetup['incomplete_id'] = $kosId;
        $kosSetup['missing'] = ['foto'];
      }
    }
  }

  $typeComplete = false;
  $typeSetup = ['has_any' => false, 'incomplete_id' => null, 'missing' => []];
  if ($kosComplete) {
    $stmt = $conn->prepare("SELECT t.id_tipe_kamar FROM tipe_kamar t INNER JOIN kos k ON k.id_kos = t.id_kos WHERE k.id_pemilik = ?");
    $stmt->bind_param('i', $id_pemilik);
    $stmt->execute();
    $typeRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $typeSetup['has_any'] = count($typeRows) > 0;
    foreach ($typeRows as $row) {
      $typeId = (int)$row['id_tipe_kamar'];
      $stmt = $conn->prepare("SELECT EXISTS(SELECT 1 FROM harga_kamar WHERE id_tipe_kamar = ?) AS has_price, EXISTS(SELECT 1 FROM tipe_kamar_fasilitas WHERE id_tipe_kamar = ?) AS has_facility, EXISTS(SELECT 1 FROM tipe_kamar_foto WHERE id_tipe_kamar = ?) AS has_photo");
      $stmt->bind_param('iii', $typeId, $typeId, $typeId);
      $stmt->execute();
      $check = $stmt->get_result()->fetch_assoc() ?: [];
      $stmt->close();
      if ((int)($check['has_price'] ?? 0) && (int)($check['has_photo'] ?? 0)) { $typeComplete = true; break; }
      if ($typeSetup['incomplete_id'] === null) {
        $typeSetup['incomplete_id'] = $typeId;
        $typeSetup['missing'] = [];
        if (!(int)($check['has_price'] ?? 0)) $typeSetup['missing'][] = 'harga';
        // Fasilitas bersifat opsional pada form tipe kamar, jadi tidak boleh membuat onboarding macet.
        if (!(int)($check['has_photo'] ?? 0)) $typeSetup['missing'][] = 'foto';
      }
    }
  }

  $roomComplete = false;
  if ($kosComplete) {
    $stmt = $conn->prepare("SELECT EXISTS(SELECT 1 FROM kamar km INNER JOIN kos k ON k.id_kos = km.id_kos WHERE k.id_pemilik = ?) AS has_room");
    $stmt->bind_param('i', $id_pemilik);
    $stmt->execute();
    $roomComplete = (int)($stmt->get_result()->fetch_assoc()['has_room'] ?? 0) === 1;
    $stmt->close();
  }

  $verificationComplete = false;
  if ($kosComplete) {
    $stmt = $conn->prepare("SELECT EXISTS(SELECT 1 FROM kos WHERE id_pemilik = ? AND status IN ('menunggu_verifikasi', 'aktif')) AS submitted");
    $stmt->bind_param('i', $id_pemilik);
    $stmt->execute();
    $verificationComplete = (int)($stmt->get_result()->fetch_assoc()['submitted'] ?? 0) === 1;
    $stmt->close();
  }

  $steps = [
    ['key'=>'profil','label'=>'Lengkapi Profil','description'=>'Lengkapi informasi pemilik.','complete'=>$profileComplete,'sidebar_route'=>'/pemilik/profil','sidebar_selector'=>'[data-onboarding="sidebar-profil"]','fast_selector'=>'[data-onboarding="fast-profil-save"]','form_selector'=>'[data-onboarding="profil-field-nama"]'],
    ['key'=>'kos','label'=>'Tambahkan Kos','description'=>'Tambahkan kos dan minimal satu foto kos.','complete'=>$kosComplete,'sidebar_route'=>'/pemilik/kos','sidebar_selector'=>'[data-onboarding="sidebar-kos"]','fast_selector'=>'[data-onboarding="fast-tambah-kos"]','form_selector'=>'[data-onboarding="kos-field-nama"]'],
    ['key'=>'tipe_kamar','label'=>'Tambahkan Tipe Kamar','description'=>'Buat tipe kamar lengkap dengan harga, fasilitas, dan foto.','complete'=>$typeComplete,'sidebar_route'=>'/pemilik/kamar','sidebar_selector'=>'[data-onboarding="sidebar-kamar"]','fast_selector'=>'[data-onboarding="fast-tambah-tipe-kamar"]','form_selector'=>'[data-onboarding="tipe-field-nama"]'],
    ['key'=>'kamar','label'=>'Tambahkan Kamar','description'=>'Tambahkan minimal satu unit kamar fisik.','complete'=>$roomComplete,'sidebar_route'=>'/pemilik/kamar','sidebar_selector'=>'[data-onboarding="sidebar-kamar"]','fast_selector'=>'[data-onboarding="fast-tambah-kamar"]','form_selector'=>'[data-onboarding="kamar-field-kos"]'],
    ['key'=>'verifikasi','label'=>'Ajukan Verifikasi','description'=>'Ajukan kos untuk diperiksa Admin.','complete'=>$verificationComplete,'sidebar_route'=>'/pemilik/kos','sidebar_selector'=>'[data-onboarding="sidebar-kos"]','fast_selector'=>'[data-onboarding="fast-ajukan-verifikasi"]','form_selector'=>null],
  ];
  $completed = count(array_filter($steps, static fn($s) => $s['complete']));
  $next = null; foreach ($steps as $step) { if (!$step['complete']) { $next = $step; break; } }
  return ['completed'=>$completed,'total'=>count($steps),'percent'=>(int)round($completed/count($steps)*100),'complete'=>$next===null,'steps'=>$steps,'next'=>$next,'type_setup'=>$typeSetup,'kos_setup'=>$kosSetup];
}
