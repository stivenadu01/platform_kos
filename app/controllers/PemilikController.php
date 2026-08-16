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


  public function kos()
  {
    model('Kos');

    $user = $this->owner();

    $data = getKosByPemilik($user['id_user']);

    view('pemilik/kos/index', [
      'title' => 'Kos Saya',
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

  public function kamar()
  {
    view('pemilik/kamar/index', [
      'title' => 'Kelola Kamar',
      'layout' => 'pemilik'
    ]);
  }

  public function tambahKamar()
  {
    view('pemilik/kamar/tambah', [
      'title' => 'Tambah Kamar',
      'layout' => 'pemilik'
    ]);
  }

  public function editKamar()
  {
    view('pemilik/kamar/edit', [
      'title' => 'Edit Kamar',
      'layout' => 'pemilik'
    ]);
  }

  public function penghuni()
  {
    view('pemilik/penghuni/index', [
      'title' => 'Kelola Penghuni',
      'layout' => 'pemilik'
    ]);
  }


  public function tambahPenghuni()
  {
    view('pemilik/penghuni/tambah', [
      'title' => 'Tambah Penghuni',
      'layout' => 'pemilik'
    ]);
  }


  public function editPenghuni()
  {
    view('pemilik/penghuni/edit', [
      'title' => 'Edit Penghuni',
      'layout' => 'pemilik'
    ]);
  }
}
