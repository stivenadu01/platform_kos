<?php

class ApiTipeKamarController
{
  public function __construct()
  {
    model('TipeKamar');
    require_once ROOT_PATH . '/app/helpers/upload.php';
  }

  private function ownerId()
  {
    return (int) ($_SESSION['user']['id_user'] ?? 0);
  }

  public function index()
  {
    response(['success' => true, 'data' => getTipeKamarListByPemilik($this->ownerId(), query('id_kos') ?? '')]);
  }

  public function show()
  {
    $id = (int) (query('id_tipe_kamar') ?? 0);
    $data = findTipeKamarByIdPemilik($id, $this->ownerId());
    if (!$data) response(['success' => false, 'message' => 'Tipe kamar tidak ditemukan.'], 404);
    response(['success' => true, 'data' => $data]);
  }

  public function store()
  {
    try {
      $id = createTipeKamar(input(), $this->ownerId());
      response(['success' => true, 'message' => 'Tipe kamar berhasil dibuat.', 'data' => ['id_tipe_kamar' => $id]], 201);
    } catch (Throwable $e) {
      response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 422);
    }
  }

  public function update()
  {
    try {
      updateTipeKamar((int) input('id_tipe_kamar'), input(), $this->ownerId());
      response(['success' => true, 'message' => 'Tipe kamar berhasil diperbarui.']);
    } catch (Throwable $e) {
      response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 422);
    }
  }

  public function destroy()
  {
    try {
      deleteTipeKamar((int) input('id_tipe_kamar'), $this->ownerId());
      response(['success' => true, 'message' => 'Tipe kamar berhasil dihapus.']);
    } catch (Throwable $e) {
      response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 422);
    }
  }

  public function harga()
  {
    $id = (int) query('id_tipe_kamar');
    if (!findTipeKamarByIdPemilik($id, $this->ownerId())) {
      response(['success' => false, 'message' => 'Tipe kamar tidak ditemukan.'], 404);
      return;
    }
    response(['success' => true, 'data' => getHargaTipeKamar($id)]);
  }

  public function simpanHarga()
  {
    try {
      $data = input();
      $tipe = findTipeKamarByIdPemilik((int) ($data['id_tipe_kamar'] ?? 0), $this->ownerId());
      if (!$tipe) response(['success' => false, 'message' => 'Tipe kamar tidak ditemukan.'], 404);
      saveHargaTipeKamar((int) $tipe['id_tipe_kamar'], $data['harga'] ?? [], (int) $tipe['kapasitas'], $this->ownerId());
      response(['success' => true, 'message' => 'Harga tipe kamar berhasil disimpan.']);
    } catch (Throwable $e) {
      response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 422);
    }
  }

  public function fasilitas()
  {
    $id = (int) query('id_tipe_kamar');
    if (!findTipeKamarByIdPemilik($id, $this->ownerId())) response(['success' => false, 'message' => 'Tipe kamar tidak ditemukan.'], 404);
    response(['success' => true, 'data' => getFasilitasTipeKamar($id)]);
  }

  public function simpanFasilitas()
  {
    try {
      $data = input();
      syncFasilitasTipeKamar((int) $data['id_tipe_kamar'], $data['id_fasilitas'] ?? [], $this->ownerId());
      response(['success' => true, 'message' => 'Fasilitas tipe kamar berhasil disimpan.']);
    } catch (Throwable $e) {
      response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 422);
    }
  }

  public function foto()
  {
    $id = (int) query('id_tipe_kamar');
    if (!findTipeKamarByIdPemilik($id, $this->ownerId())) response(['success' => false, 'message' => 'Tipe kamar tidak ditemukan.'], 404);
    response(['success' => true, 'data' => getFotoTipeKamar($id)]);
  }

  public function tambahFoto()
  {
    try {
      $id = (int) params('id_tipe_kamar');
      $foto = request_file('foto');
      if (!$foto) response(['success' => false, 'message' => 'Foto wajib dipilih.'], 422);
      response(['success' => true, 'data' => ['id_foto' => createFotoTipeKamar($id, $this->ownerId(), $foto)]], 201);
    } catch (Throwable $e) {
      response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 422);
    }
  }

  public function thumbnail()
  {
    try {
      setThumbnailFotoTipeKamar((int) params('id_foto'), $this->ownerId());
      response(['success' => true, 'message' => 'Thumbnail berhasil diubah.']);
    } catch (Throwable $e) {
      response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 422);
    }
  }

  public function hapusFoto()
  {
    try {
      deleteFotoTipeKamar((int) params('id_foto'), $this->ownerId());
      response(['success' => true, 'message' => 'Foto tipe kamar berhasil dihapus.']);
    } catch (Throwable $e) {
      response(['success' => false, 'message' => $e->getMessage()], $e->getCode() ?: 422);
    }
  }
}
