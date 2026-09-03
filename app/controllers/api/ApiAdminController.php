<?php

class ApiAdminController
{
  public function __construct()
  {
    model('Admin');
    model('Langganan');
    model('MetodePembayaranLangganan');
  }

  private function admin()
  {
    return $_SESSION['user'];
  }

  public function dashboard()
  {
    try {
      response(['success' => true, 'data' => getAdminDashboardData()]);
    } catch (Throwable $e) {
      error_log('Admin dashboard error: ' . $e->getMessage());
      response(['success' => false, 'message' => 'Gagal memuat dashboard admin.'], 500);
    }
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




  public function subscriptions()
  {
    try {
      $status = trim((string)query('status', ''));
      $allowed = ['', 'aktif', 'menunggu', 'berakhir', 'dibatalkan', 'akan_berakhir'];
      if (!in_array($status, $allowed, true)) {
        response(['success' => false, 'message' => 'Filter subscription tidak valid.'], 422);
      }

      response([
        'success' => true,
        'data' => getAdminLanggananList($status),
        'summary' => getAdminSubscriptionSummary()
      ]);
    } catch (Throwable $e) {
      error_log('Admin subscription list error: ' . $e->getMessage());
      response(['success' => false, 'message' => 'Gagal memuat data langganan.'], 500);
    }
  }

  public function subscriptionShow()
  {
    try {
      $id = (int)params('id');
      $data = getAdminLanggananDetail($id);
      if (!$data) {
        response(['success' => false, 'message' => 'Subscription tidak ditemukan.'], 404);
      }
      response(['success' => true, 'data' => $data]);
    } catch (Throwable $e) {
      error_log('Admin subscription detail error: ' . $e->getMessage());
      response(['success' => false, 'message' => 'Gagal memuat detail subscription.'], 500);
    }
  }

  public function subscriptionPayments()
  {
    try {
      $status = trim((string)query('status', 'menunggu'));
      $allowed = ['menunggu', 'diverifikasi', 'ditolak', 'dibatalkan'];
      if (!in_array($status, $allowed, true)) {
        response(['success' => false, 'message' => 'Filter pembayaran tidak valid.'], 422);
      }
      response([
        'success' => true,
        'data' => getAdminPembayaranLangganan($status)
      ]);
    } catch (Throwable $e) {
      error_log('Admin subscription payment list error: ' . $e->getMessage());
      response(['success' => false, 'message' => 'Gagal memuat pembayaran langganan.'], 500);
    }
  }

  public function subscriptionPaymentShow()
  {
    try {
      $id = (int)params('id');
      $data = getAdminPembayaranLanggananDetail($id);
      if (!$data) {
        response(['success' => false, 'message' => 'Pembayaran langganan tidak ditemukan.'], 404);
      }
      response(['success' => true, 'data' => $data]);
    } catch (Throwable $e) {
      error_log('Admin subscription payment detail error: ' . $e->getMessage());
      response(['success' => false, 'message' => 'Gagal memuat detail pembayaran.'], 500);
    }
  }

  public function paymentMethodSummary()
  {
    try {
      response(['success' => true, 'data' => getAdminMetodePembayaranSummary()]);
    } catch (Throwable $e) {
      error_log('Admin payment method summary error: ' . $e->getMessage());
      response(['success' => false, 'message' => 'Gagal memuat ringkasan metode pembayaran.'], 500);
    }
  }

  public function paymentMethods()
  {
    try {
      response(['success' => true, 'data' => getMetodePembayaranLangganan(false)]);
    } catch (Throwable $e) {
      error_log('Admin payment methods list error: ' . $e->getMessage());
      response(['success' => false, 'message' => 'Gagal memuat metode pembayaran.'], 500);
    }
  }

  public function paymentMethodSave()
  {
    try {
      $data = input();
      $id = simpanMetodePembayaranLangganan($data, (int)$this->admin()['id_user']);
      response(['success' => true, 'message' => 'Metode pembayaran berhasil disimpan.', 'data' => ['id_metode_pembayaran' => $id]]);
    } catch (Exception $e) {
      response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
    }
  }

  public function paymentMethodStatus()
  {
    try {
      $data = input();
      $id = (int)($data['id_metode_pembayaran'] ?? 0);
      ubahStatusMetodePembayaranLangganan($id, !empty($data['is_aktif']));
      response(['success' => true, 'message' => 'Status metode pembayaran berhasil diperbarui.']);
    } catch (Exception $e) {
      response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
    }
  }

  public function subscriptionPaymentDecision()
  {
    try {
      $data = input();
      $id = (int)($data['id_pembayaran_langganan'] ?? 0);
      $keputusan = trim((string)($data['keputusan'] ?? ''));
      $catatan = trim((string)($data['catatan'] ?? ''));

      if ($id <= 0) throw new Exception('Pembayaran tidak valid.', 422);
      prosesVerifikasiPembayaranLangganan($id, (int)$this->admin()['id_user'], $keputusan, $catatan);

      response([
        'success' => true,
        'message' => $keputusan === 'diverifikasi'
          ? 'Pembayaran disetujui dan subscription Pro berhasil diaktifkan/diperpanjang.'
          : 'Pembayaran ditolak. Pemilik dapat melihat catatan penolakan.'
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
