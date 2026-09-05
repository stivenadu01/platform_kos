<?php

class AdminController
{
  public function dashboard()
  {
    model('Admin');
    view('admin/dashboard', [
      'title' => 'Dashboard Admin',
      'dashboard' => getAdminDashboardData()
    ], 'admin');
  }

  public function profil()
  {
    model('User');
    $user = findUser((int)($_SESSION['user']['id_user'] ?? 0));
    if (!$user) {
      response(['success' => false, 'message' => 'Data profil tidak ditemukan.'], 404);
    }
    unset($user['password']);
    view('admin/profil', [
      'title' => 'Profil & Pengaturan Akun',
      'profile' => $user
    ], 'admin');
  }

  public function pengguna()
  {
    model('Admin');
    view('admin/pengguna', [
      'title' => 'Manajemen Pengguna',
      'summary' => getAdminUserSummary()
    ], 'admin');
  }

  public function verifikasi()
  {
    model('Admin');
    view('admin/verifikasi', [
      'title' => 'Verifikasi Kos',
      'pengajuan' => getAdminVerifikasiList('menunggu')
    ], 'admin');
  }

  public function laporan()
  {
    model('Admin');
    view('admin/laporan', [
      'title' => 'Laporan Kos',
      'summary' => getAdminLaporanSummary()
    ], 'admin');
  }

  public function metodePembayaran()
  {
    view('admin/metode-pembayaran', [
      'title' => 'Metode Pembayaran Langganan',
    ], 'admin');
  }

  public function langganan()
  {
    view('admin/langganan', [
      'title' => 'Verifikasi Langganan',
    ], 'admin');
  }
}
