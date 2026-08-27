<?php

class ApiClaimController
{
  public function __construct()
  {
    model('Claim');
  }

  public function mine()
  {
    $user = $_SESSION['user'] ?? null;
    if (!$user || ($user['role'] ?? '') !== 'pelanggan') {
      response(['success' => false, 'message' => 'Akses hanya untuk pelanggan.'], 403);
    }

    response([
      'success' => true,
      'data' => getClaimRiwayatByUser((int)$user['id_user'])
    ]);
  }

  public function history()
  {
    $user = $_SESSION['user'] ?? null;
    if (!$user || ($user['role'] ?? '') !== 'pelanggan') {
      response(['success' => false, 'message' => 'Akses hanya untuk pelanggan.'], 403);
    }

    response([
      'success' => true,
      'data' => getRiwayatKosByUser((int)$user['id_user'])
    ]);
  }

  public function bills()
  {
    $user = $_SESSION['user'] ?? null;
    if (!$user || ($user['role'] ?? '') !== 'pelanggan') {
      response(['success' => false, 'message' => 'Akses hanya untuk pelanggan.'], 403);
    }

    model('Tagihan');
    response([
      'success' => true,
      'data' => getTagihanListByUser((int)$user['id_user'])
    ]);
  }

  public function billShow()
  {
    $user = $_SESSION['user'] ?? null;
    if (!$user || ($user['role'] ?? '') !== 'pelanggan') {
      response(['success' => false, 'message' => 'Akses hanya untuk pelanggan.'], 403);
    }

    $id_tagihan = (int)query('id_tagihan', 0);
    if ($id_tagihan <= 0) {
      response(['success' => false, 'message' => 'ID tagihan tidak valid.'], 422);
    }

    model('Tagihan');
    $data = findTagihanByIdUser($id_tagihan, (int)$user['id_user']);
    if (!$data) {
      response(['success' => false, 'message' => 'Tagihan tidak ditemukan.'], 404);
    }

    response(['success' => true, 'data' => $data]);
  }

  public function candidates()
  {
    $user = $_SESSION['user'] ?? null;
    if (!$user || ($user['role'] ?? '') !== 'pelanggan') {
      response(['success' => false, 'message' => 'Akses hanya untuk pelanggan.'], 403);
    }

    response([
      'success' => true,
      'data' => getClaimCandidatesByUser((int)$user['id_user'])
    ]);
  }

  public function store()
  {
    try {
      $user = $_SESSION['user'] ?? null;
      if (!$user || ($user['role'] ?? '') !== 'pelanggan') {
        throw new Exception('Akses hanya untuk pelanggan.', 403);
      }

      $data = input();
      $id = createClaimRiwayat(
        (int)$user['id_user'],
        (int)($data['id_penghuni'] ?? 0),
        trim((string)($user['nik'] ?? '')),
        trim((string)($data['catatan_mahasiswa'] ?? ''))
      );

      response(['success' => true, 'message' => 'Claim berhasil diajukan.', 'data' => ['id_claim' => $id]], 201);
    } catch (Exception $e) {
      response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
    }
  }

  public function ownerIndex()
  {
    $user = $_SESSION['user'] ?? null;
    if (!$user || ($user['role'] ?? '') !== 'pemilik') {
      response(['success' => false, 'message' => 'Akses hanya untuk pemilik.'], 403);
    }

    response([
      'success' => true,
      'data' => getClaimRiwayatByPemilik((int)$user['id_user'], trim((string)query('status', '')))
    ]);
  }

  public function decision()
  {
    try {
      $user = $_SESSION['user'] ?? null;
      if (!$user || ($user['role'] ?? '') !== 'pemilik') {
        throw new Exception('Akses hanya untuk pemilik.', 403);
      }

      $data = input();
      decideClaimRiwayat(
        (int)($data['id_claim'] ?? 0),
        (int)$user['id_user'],
        trim((string)($data['keputusan'] ?? '')),
        trim((string)($data['catatan_pemilik'] ?? ''))
      );

      response(['success' => true, 'message' => 'Claim berhasil diproses.']);
    } catch (Exception $e) {
      response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 500);
    }
  }
}
