<?php

class ApiAdminLaporanController
{
  public function __construct()
  {
    model('Admin');
  }

  private function admin()
  {
    return $_SESSION['user'];
  }

  public function index()
  {
    $status = trim((string)query('status', ''));
    $page = max(1, (int)query('page', 1));
    $limit = max(1, min(50, (int)query('limit', 10)));
    $search = trim((string)query('search', ''));

    response([
      'success' => true,
      'data' => getAdminLaporanList($page, $limit, $status, $search),
      'summary' => getAdminLaporanSummary()
    ]);
  }

  public function show()
  {
    $id = (int)params('id');
    $data = getAdminLaporanDetail($id);
    if (!$data) {
      response(['success' => false, 'message' => 'Laporan tidak ditemukan.'], 404);
    }
    response(['success' => true, 'data' => $data]);
  }

  public function decision()
  {
    try {
      $data = input();
      $id = (int)($data['id_laporan'] ?? 0);
      $status = trim((string)($data['status'] ?? ''));
      $catatan = trim((string)($data['catatan_admin'] ?? ''));

      prosesLaporanKos($id, (int)$this->admin()['id_user'], $status, $catatan);

      response([
        'success' => true,
        'message' => 'Laporan berhasil diperbarui.'
      ]);
    } catch (Exception $e) {
      response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
    }
  }
}
