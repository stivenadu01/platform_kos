<?php

class PemilikController
{
  private function owner()
  {
    return $_SESSION['user'];
  }


  public function dashboard()
  {
    view('pemilik/dashboard', [
      'title' => 'Dashboard Pemilik',
      'layout' => 'pemilik'
    ]);
  }


  public function profil()
  {
    $user = $this->owner();

    model('User');
    $profile = findUser((int)$user['id_user']);

    if (!$profile) {
      response([
        'success' => false,
        'message' => 'Data profil tidak ditemukan'
      ], 404);
    }

    unset($profile['password']);

    view('pemilik/profil/index', [
      'title' => 'Profil Saya',
      'layout' => 'pemilik',
      'profile' => $profile
    ]);
  }


  public function kos()
  {
    model('Kos');

    $user = $this->owner();

    $data = getKosByPemilik($user['id_user']);

    view('pemilik/kos/index', [
      'title' => 'Properti',
      'layout' => 'pemilik',
      'kos' => $data
    ]);
  }


  public function tambahKos()
  {
    view('pemilik/kos/tambah', [
      'title' => 'Tambah Kos',
      'layout' => 'pemilik'
    ]);
  }


  public function editKos()
  {
    model('Kos');

    $id_kos = (int) query('id');
    $user = $this->owner();

    if (!$id_kos) {
      response([
        'success' => false,
        'message' => 'ID kos tidak valid'
      ], 400);
    }

    $kos = findKosById($id_kos, $user['id_user']);

    if (!$kos) {
      response([
        'success' => false,
        'message' => 'Kos tidak ditemukan'
      ], 404);
    }

    if (($kos['status'] ?? '') === 'menunggu_verifikasi') {
      response([
        'success' => false,
        'message' => 'Kos yang sedang menunggu verifikasi tidak dapat diedit.'
      ], 409);
    }

    view('pemilik/kos/edit', [
      'title' => 'Edit Kos',
      'layout' => 'pemilik',
      'kos' => $kos
    ]);
  }

  public function fotoKos()
  {
    model('Kos');

    $id_kos = (int) query('id');
    $user = $this->owner();

    if (!$id_kos) {
      response([
        'success' => false,
        'message' => 'ID kos tidak valid'
      ], 400);
    }

    $kos = findKosById($id_kos, $user['id_user']);

    if (!$kos) {
      response([
        'success' => false,
        'message' => 'Kos tidak ditemukan'
      ], 404);
    }

    view('pemilik/kos/foto', [
      'title' => 'Kelola Foto Kos',
      'layout' => 'pemilik',
      'kos' => $kos
    ]);
  }

  public function pembayaran()
  {
    view('pemilik/pembayaran/index', [
      'title' => 'Keuangan Kos',
      'layout' => 'pemilik'
    ]);
  }

  public function detailPembayaran()
  {
    $id_tagihan = (int) query('id_tagihan');

    if (!$id_tagihan) {
      response([
        'success' => false,
        'message' => 'ID tagihan tidak valid.'
      ], 400);
    }

    model('Tagihan');
    $tagihan = findTagihanByIdPemilik($id_tagihan, (int) $_SESSION['user']['id_user']);

    if (!$tagihan) {
      response([
        'success' => false,
        'message' => 'Tagihan tidak ditemukan.'
      ], 404);
    }

    view('pemilik/pembayaran/detail', [
      'title' => 'Detail Tagihan',
      'layout' => 'pemilik',
      'tagihan' => $tagihan
    ]);
  }


  public function langganan()
  {
    view('pemilik/langganan/index', [
      'title' => 'Langganan BetaKos',
      'layout' => 'pemilik',
      'upgrade_requested' => query('upgrade') === '1'
    ]);
  }

  public function langgananCheckout()
  {
    view('pemilik/langganan/checkout', [
      'title' => 'Checkout BetaKos Pro',
      'layout' => 'pemilik'
    ]);
  }

  public function akun()
  {
    view('account', ['title'=>'Akun Pemilik','layout'=>'pemilik','accountOwner'=>true]);
  }

  public function detailProperti()
  {
    model('Kos');
    $kos = findKosById((int)query('id_kos'), (int)$this->owner()['id_user']);
    if (!$kos) { response(['success'=>false,'message'=>'Kos tidak ditemukan.'],404); return; }
    view('pemilik/kos/detail', ['title'=>$kos['nama_kos'],'layout'=>'pemilik','kos'=>$kos]);
  }

  public function detailPenghuni()
  {
    model('Penghuni');
    $penghuni = findPenghuniByIdPemilik((int)query('id_penghuni'), (int)$this->owner()['id_user']);
    if (!$penghuni) { response(['success'=>false,'message'=>'Penghuni tidak ditemukan.'],404); return; }
    view('pemilik/penghuni/detail', ['title'=>'Detail Penghuni','layout'=>'pemilik','penghuni'=>$penghuni]);
  }

  public function langgananRiwayat()
  {
    view('pemilik/langganan/riwayat', [
      'title' => 'Riwayat Langganan',
      'layout' => 'pemilik'
    ]);
  }

  public function langgananPembayaran()
  {
    view('pemilik/langganan/pembayaran', [
      'title' => 'Pembayaran Langganan',
      'layout' => 'pemilik'
    ]);
  }

  public function kamar()
  {
    view('pemilik/kamar/index', [
      'title' => 'Kelola Kamar',
      'layout' => 'pemilik'
    ]);
  }

  public function riwayat()
  {
    view('pemilik/riwayat/index', [
      'title' => 'Riwayat Operasional',
      'layout' => 'pemilik'
    ]);
  }

  public function kelolaKamar()
  {
    model('TipeKamar');
    $type = findTipeKamarByIdPemilik((int) query('id_tipe_kamar'), (int) $this->owner()['id_user']);
    $target = $type
      ? '/pemilik/kos/detail?id_kos=' . (int) $type['id_kos'] . '&id_tipe_kamar=' . (int) $type['id_tipe_kamar']
      : '/pemilik/kos';
    header('Location: ' . BASE_URL . $target, true, 302);
    exit;
  }

  public function operasionalKamar()
  {
    model('Kamar');
    model('Langganan');
    $room = findKamarByIdPemilik((int)query('id_kamar'), (int)$this->owner()['id_user']);
    if (!$room) { response(['success'=>false,'message'=>'Kamar tidak ditemukan.'],404); return; }
    $subscription = getStatusLanggananPemilik((int)$this->owner()['id_user']);
    view('pemilik/kamar/operasional', [
      'title' => 'Detail Kamar',
      'canManageOccupancy' => !empty($subscription['is_pro']),
      'layout' => 'pemilik'
    ]);
  }

  public function redirectOperasionalKamarLegacy()
  {
    $id_kamar = (int) query('id_kamar');
    header('Location: ' . BASE_URL . '/pemilik/kamar/detail' . ($id_kamar > 0 ? '?id_kamar=' . $id_kamar : ''), true, 302);
    exit;
  }

  public function tambahKamar()
  {
    $mode = query('mode') === 'bulk' ? 'bulk' : 'single';

    view('pemilik/kamar/tambah', [
      'title' => $mode === 'bulk' ? 'Tambah Banyak Kamar' : 'Tambah Satu Kamar',
      'layout' => 'pemilik',
      'mode' => $mode
    ]);
  }

  public function editKamar()
  {
    $id_kamar = (int) query('id_kamar');
    header('Location: ' . BASE_URL . '/pemilik/kamar/detail' . ($id_kamar > 0 ? '?id_kamar=' . $id_kamar : ''), true, 302);
    exit;
  }

  public function hargaKamar()
  {
    $id_kamar = (int) query('id_kamar');
    header('Location: ' . BASE_URL . '/pemilik/kamar/detail' . ($id_kamar > 0 ? '?id_kamar=' . $id_kamar . '&section=data' : ''), true, 302);
    exit;
  }

  public function redirectTipeKamarLegacy()
  {
    header('Location: ' . BASE_URL . '/pemilik/kamar', true, 302);
    exit;
  }

  public function tambahTipeKamar()
  {
    view('pemilik/tipe-kamar/form', [
      'title' => 'Tambah Tipe Kamar',
      'layout' => 'pemilik'
    ]);
  }

  public function editTipeKamar()
  {
    view('pemilik/tipe-kamar/form', [
      'title' => 'Edit Tipe Kamar',
      'layout' => 'pemilik'
    ]);
  }

  public function fotoTipeKamar()
  {
    model('TipeKamar');
    $id_tipe_kamar = (int) query('id_tipe_kamar');
    $user = $this->owner();

    if (!$id_tipe_kamar) {
      response(['success' => false, 'message' => 'ID tipe kamar tidak valid'], 400);
    }

    $tipe = findTipeKamarByIdPemilik($id_tipe_kamar, $user['id_user']);
    if (!$tipe) {
      response(['success' => false, 'message' => 'Tipe kamar tidak ditemukan'], 404);
    }

    view('pemilik/tipe-kamar/foto', [
      'title' => 'Kelola Foto Tipe Kamar',
      'layout' => 'pemilik',
      'tipe' => $tipe
    ]);
  }
  public function penghuni()
  {
    view('pemilik/penghuni/index', [
      'title' => 'Penghuni Aktif',
      'layout' => 'pemilik'
    ]);
  }

  public function claim()
  {
    view('pemilik/claim/index', [
      'title' => 'Claim Riwayat Kos',
      'layout' => 'pemilik'
    ]);
  }

  public function tambahPenghuni()
  {
    view('pemilik/penghuni/form', [
      'title' => 'Tambah Penghuni',
      'layout' => 'pemilik',
      'mode' => 'tambah'
    ]);
  }

  public function editPenghuni()
  {
    view('pemilik/penghuni/form', [
      'title' => 'Edit Penghuni',
      'layout' => 'pemilik',
      'mode' => 'edit'
    ]);
  }
}
