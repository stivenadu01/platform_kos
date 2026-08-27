<?php

class ApiAdminController
{
  public function __construct()
  {
    model('Admin');
  }

  private function admin()
  {
    return $_SESSION['user'];
  }

  public function verificationList()
  {
    $status = query('status', 'menunggu');

    response([
      'success' => true,
      'data' => getAdminVerifikasiList($status),
      'summary' => getAdminVerificationSummary()
    ]);
  }

  public function verificationShow()
  {
    $id = (int)params('id');
    $data = getAdminVerifikasiDetail($id);

    if (!$data) {
      response(['success' => false, 'message' => 'Pengajuan verifikasi tidak ditemukan'], 404);
    }

    response(['success' => true, 'data' => $data]);
  }

  public function verificationDecision()
  {
    try {
      $data = input();
      $id = (int)($data['id_verifikasi'] ?? 0);
      $keputusan = $data['keputusan'] ?? '';
      $catatan = trim($data['catatan'] ?? '');

      if ($id <= 0) throw new Exception('Pengajuan tidak valid', 422);
      if ($keputusan === 'ditolak' && $catatan === '') {
        throw new Exception('Catatan wajib diisi ketika pengajuan ditolak', 422);
      }

      prosesVerifikasiKos($id, $this->admin()['id_user'], $keputusan, $catatan);

      response([
        'success' => true,
        'message' => $keputusan === 'disetujui'
          ? 'Kos berhasil diverifikasi dan diaktifkan.'
          : 'Pengajuan ditolak. Pemilik dapat memperbaiki data lalu mengajukan kembali.'
      ]);
    } catch (Exception $e) {
      response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
    }
  }



  public function userList()
  {
    $page = max(1, (int)query('page', 1));
    $limit = max(1, min(50, (int)query('limit', 10)));
    $search = trim((string)query('search', ''));
    $role = trim((string)query('role', ''));
    $verification = trim((string)query('verification', ''));
    $status = trim((string)query('status', ''));

    response([
      'success' => true,
      'data' => getAdminUserList($page, $limit, $search, $role, $verification, $status),
      'summary' => getAdminUserSummary()
    ]);
  }

  public function userVerify()
  {
    try {
      $id = (int)(input('id_user') ?: query('id_user', 0));
      adminVerifyUser($id);
      response(['success' => true, 'message' => 'Pengguna berhasil diverifikasi.']);
    } catch (Exception $e) {
      response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
    }
  }

  public function userStatus()
  {
    try {
      $data = input();
      $id = (int)($data['id_user'] ?? 0);
      $status = trim($data['status'] ?? '');
      adminUpdateUserStatus($id, $status, (int)$this->admin()['id_user']);
      response(['success' => true, 'message' => 'Status pengguna berhasil diperbarui.']);
    } catch (Exception $e) {
      response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
    }
  }

  public function userCreate()
  {
    try {
      $data = input();
      $id = adminCreateUser($data);
      response(['success' => true, 'message' => 'Pengguna berhasil ditambahkan dan langsung terverifikasi.', 'data' => ['id_user' => $id]], 201);
    } catch (Exception $e) {
      response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
    }
  }

  public function submitVerification()
  {
    try {
      $data = input();
      $id_kos = (int)($data['id_kos'] ?? 0);
      if ($id_kos <= 0) throw new Exception('Kos tidak valid', 422);
      ajukanVerifikasiKos($id_kos, $_SESSION['user']['id_user']);

      response([
        'success' => true,
        'message' => 'Kos berhasil diajukan untuk verifikasi admin.'
      ]);
    } catch (Exception $e) {
      response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
    }
  }
}
