<?php

class UserController
{
  public function home()
  {
    model('Kos');
    model('Kos');

    view('home', [
      'title' => 'Temukan Kos di Kupang',
      'kosUnggulan' => getKosUnggulanUntukHome(6)
    ]);
  }

  public function search()
  {
    view('user/cari-kos', [
      'title' => 'Cari Kos'
    ]);
  }

  public function detailKos()
  {
    model('Kos');

    $id_kos = (int) params('id');
    if ($id_kos <= 0) {
      response([
        'success' => false,
        'message' => 'Kos tidak ditemukan.'
      ], 404);
    }

    $kos = getDetailKosPublik($id_kos);

    if (!$kos) {
      response([
        'success' => false,
        'message' => 'Kos tidak ditemukan atau sudah tidak tersedia.'
      ], 404);
    }

    view('user/detail-kos', [
      'title' => $kos['nama_kos'] . ' - BetaKos',
      'kos' => $kos
    ]);
  }

  public function favorit()
  {
    $user = $_SESSION['user'] ?? null;
    if (!$user || ($user['role'] ?? '') !== 'pelanggan') {
      response([
        'success' => false,
        'message' => 'Akses hanya untuk pelanggan.'
      ], 403);
    }

    view('user/favorit', [
      'title' => 'Favorit - BetaKos'
    ]);
  }

  public function laporan()
  {
    model('Kos');

    $user = $_SESSION['user'] ?? null;
    if (!$user || ($user['role'] ?? '') !== 'pelanggan') {
      response([
        'success' => false,
        'message' => 'Akses hanya untuk pelanggan.'
      ], 403);
    }

    $laporan = getLaporanKosByUser((int)$user['id_user']);

    view('user/laporan', [
      'title' => 'Laporan Saya - BetaKos',
      'laporan' => $laporan
    ]);
  }

  public function profil()
  {
    view('user/placeholder', [
      'title' => 'Profil Saya',
      'heading' => 'Profil Saya',
      'message' => 'Halaman profil mahasiswa akan dibuat pada fase berikutnya.'
    ]);
  }
}
