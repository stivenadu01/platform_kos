<?php

class AdminController
{
  public function dashboard()
  {
    model('Admin');
    view('admin/dashboard', [
      'title' => 'Dashboard Admin',
      'summary' => getAdminVerificationSummary()
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
}
