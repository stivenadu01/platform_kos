<?php


class ApiFotoController
{
  public function __construct()
  {
    model('Foto');
    require_once ROOT_PATH . '/app/helpers/upload.php';
  }


  private function owner()
  {
    return $_SESSION['user'];
  }


  /**
   * GET /api/pemilik/foto?id_kos=1
   */
  public function index()
  {
    $user = $this->owner();

    $id_kos = (int) params('id_kos');

    if (!$id_kos) {
      response([
        'success' => false,
        'message' => 'ID kos wajib diberikan.'
      ], 422);
    }


    /*
     * Pastikan kos milik pemilik.
     */
    $conn = db();

    $stmt = $conn->prepare("
      SELECT id_kos, nama_kos
      FROM kos
      WHERE id_kos = ?
        AND id_pemilik = ?
      LIMIT 1
    ");

    $stmt->bind_param(
      'ii',
      $id_kos,
      $user['id_user']
    );

    $stmt->execute();

    $kos = $stmt
      ->get_result()
      ->fetch_assoc();

    $stmt->close();


    if (!$kos) {
      response([
        'success' => false,
        'message' => 'Kos tidak ditemukan atau bukan milik Anda.'
      ], 404);
    }


    response([
      'success' => true,
      'data' => [
        'kos' => $kos,
        'foto' => getFotoByKos(
          $id_kos,
          $user['id_user']
        )
      ]
    ]);
  }


  /**
   * POST /api/pemilik/foto
   */
  public function store()
  {
    $user = $this->owner();

    $id_kos = (int) params('id_kos');

    $file = request_file('foto');


    if (!$id_kos) {
      response([
        'success' => false,
        'message' => 'ID kos wajib diberikan.'
      ], 422);
    }


    if (!$file) {
      response([
        'success' => false,
        'message' => 'Foto wajib dipilih.'
      ], 422);
    }


    try {

      $id_foto = createFotoKos(
        $id_kos,
        $user['id_user'],
        $file
      );


      response([
        'success' => true,
        'message' => 'Foto kos berhasil ditambahkan.',
        'data' => [
          'id_foto' => $id_foto
        ]
      ], 201);
    } catch (Throwable $e) {

      response(
        [
          'success' => false,
          'message' => $e->getMessage()
        ],
        $e->getCode() >= 400 && $e->getCode() < 600
          ? $e->getCode()
          : 500
      );
    }
  }


  /**
   * PUT /api/pemilik/foto/thumbnail
   */
  public function thumbnail()
  {
    $user = $this->owner();

    $id_foto = (int) params('id_foto');


    if (!$id_foto) {
      response([
        'success' => false,
        'message' => 'ID foto wajib diberikan.'
      ], 422);
    }


    try {

      setThumbnailFoto(
        $id_foto,
        $user['id_user']
      );


      response([
        'success' => true,
        'message' => 'Thumbnail berhasil diubah.'
      ]);
    } catch (Throwable $e) {

      response(
        [
          'success' => false,
          'message' => $e->getMessage()
        ],
        $e->getCode() >= 400 && $e->getCode() < 600
          ? $e->getCode()
          : 500
      );
    }
  }


  /**
   * DELETE /api/pemilik/foto/{id}
   */
  public function destroy()
  {
    $user = $this->owner();

    $id_foto = (int) params('id_foto');


    if (!$id_foto) {
      response([
        'success' => false,
        'message' => 'ID foto tidak valid.'
      ], 422);
    }


    try {

      deleteFotoKos(
        $id_foto,
        $user['id_user']
      );


      response([
        'success' => true,
        'message' => 'Foto berhasil dihapus.'
      ]);
    } catch (Throwable $e) {

      response(
        [
          'success' => false,
          'message' => $e->getMessage()
        ],
        $e->getCode() >= 400 && $e->getCode() < 600
          ? $e->getCode()
          : 500
      );
    }
  }
}
