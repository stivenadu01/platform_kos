<?php

class ApiLaporanController
{
  public function __construct()
  {
    // Model Kos memuat fungsi buatLaporanKos() dan getLaporanKosByUser().
    model('Kos');
  }

  private function user()
  {
    return $_SESSION['user'] ?? null;
  }

  public function store()
  {
    try {
      $user = $this->user();
      if (!$user || ($user['role'] ?? '') !== 'pelanggan') {
        throw new Exception('Hanya pelanggan yang dapat melaporkan kos.', 403);
      }

      $data = input();
      $id_kos = (int)($data['id_kos'] ?? 0);
      $alasan = trim((string)($data['alasan'] ?? ''));
      $deskripsi = trim((string)($data['deskripsi'] ?? ''));

      $id = buatLaporanKos((int)$user['id_user'], $id_kos, $alasan, $deskripsi);

      response([
        'success' => true,
        'message' => 'Laporan berhasil dikirim. Terima kasih atas bantuannya menjaga kualitas informasi BetaKos.',
        'data' => ['id_laporan' => $id]
      ], 201);
    } catch (Exception $e) {
      response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
    }
  }

  public function mine()
  {
    try {
      $user = $this->user();
      if (!$user || ($user['role'] ?? '') !== 'pelanggan') {
        throw new Exception('Akses hanya untuk pelanggan.', 403);
      }

      response([
        'success' => true,
        'data' => getLaporanKosByUser((int)$user['id_user'])
      ]);
    } catch (Exception $e) {
      response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
    }
  }
}
