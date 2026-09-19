<?php

function getWhatsAppBillRecipients($idTagihan)
{
  $conn = db();
  $sql = "
    SELECT
      t.id_tagihan, t.nomor_tagihan, t.tanggal_mulai, t.tanggal_selesai,
      t.tanggal_jatuh_tempo, t.total_tagihan, t.total_dibayar,
      GREATEST(t.total_tagihan - t.total_dibayar, 0) AS sisa_tagihan,
      t.status, km.nomor_kamar, k.id_kos, k.nama_kos, k.id_pemilik,
      p.id_penghuni, p.nama AS nama_penghuni, p.no_hp
    FROM tagihan t
    INNER JOIN kamar km ON km.id_kamar = t.id_kamar
    INNER JOIN kos k ON k.id_kos = km.id_kos
    INNER JOIN tagihan_penghuni tp ON tp.id_tagihan = t.id_tagihan
    INNER JOIN penghuni p ON p.id_penghuni = tp.id_penghuni
    WHERE t.id_tagihan = ?
    ORDER BY p.id_penghuni ASC
  ";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param('i', $idTagihan);
  $stmt->execute();
  $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
  return $rows;
}

function getAutomaticWhatsAppReminderCandidates($date)
{
  $conn = db();
  $stmt = $conn->prepare("
    SELECT DISTINCT t.id_tagihan
    FROM tagihan t
    WHERE t.status IN ('belum_lunas', 'sebagian')
      AND t.total_tagihan > t.total_dibayar
      AND DATEDIFF(t.tanggal_jatuh_tempo, ?) IN (3, 0, -3)
    ORDER BY t.id_tagihan ASC
  ");
  $stmt->bind_param('s', $date);
  $stmt->execute();
  $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
  return array_map('intval', array_column($rows, 'id_tagihan'));
}

function createWhatsAppNotification(array $data)
{
  $conn = db();
  $stmt = $conn->prepare("
    INSERT INTO notifikasi_whatsapp (
      id_pemilik, id_penghuni, id_tagihan, id_pembayaran, jenis,
      nomor_tujuan, isi_pesan, status, provider, idempotency_key
    ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', 'fonnte', ?)
  ");

  $idPembayaran = $data['id_pembayaran'] ?? null;
  $stmt->bind_param(
    'iiiissss',
    $data['id_pemilik'],
    $data['id_penghuni'],
    $data['id_tagihan'],
    $idPembayaran,
    $data['jenis'],
    $data['nomor_tujuan'],
    $data['isi_pesan'],
    $data['idempotency_key']
  );

  try {
    $stmt->execute();
    $id = (int) $stmt->insert_id;
    $stmt->close();
    return $id;
  } catch (mysqli_sql_exception $e) {
    $stmt->close();
    if ((int) $e->getCode() === 1062) {
      // Pengiriman yang sudah queued tidak boleh diduplikasi. Pengiriman gagal
      // dapat dicoba kembali maksimal tiga kali dengan kunci yang sama.
      $stmt = $conn->prepare("
        SELECT id_notifikasi, status, jumlah_percobaan
        FROM notifikasi_whatsapp
        WHERE idempotency_key=?
        LIMIT 1
      ");
      $stmt->bind_param('s', $data['idempotency_key']);
      $stmt->execute();
      $existing = $stmt->get_result()->fetch_assoc();
      $stmt->close();

      if (
        $existing &&
        $existing['status'] === 'failed' &&
        (int) $existing['jumlah_percobaan'] < 3
      ) {
        $id = (int) $existing['id_notifikasi'];
        $stmt = $conn->prepare("
          UPDATE notifikasi_whatsapp
          SET status='pending', error_message=NULL, updated_at=CURRENT_TIMESTAMP
          WHERE id_notifikasi=? AND status='failed' AND jumlah_percobaan < 3
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $claimed = $stmt->affected_rows === 1;
        $stmt->close();
        return $claimed ? $id : null;
      }

      return null;
    }
    throw $e;
  }
}

function markWhatsAppNotificationQueued($idNotifikasi, array $providerResult)
{
  $conn = db();
  $requestId = $providerResult['request_id'] ?? null;
  $messageId = $providerResult['message_id'] ?? null;
  $stmt = $conn->prepare("
    UPDATE notifikasi_whatsapp
    SET status='queued', provider_request_id=?, provider_message_id=?,
        jumlah_percobaan=jumlah_percobaan+1, sent_at=CURRENT_TIMESTAMP,
        error_message=NULL, updated_at=CURRENT_TIMESTAMP
    WHERE id_notifikasi=?
  ");
  $stmt->bind_param('ssi', $requestId, $messageId, $idNotifikasi);
  $stmt->execute();
  $stmt->close();
}

function markWhatsAppNotificationFailed($idNotifikasi, $errorMessage)
{
  $conn = db();
  $safeError = mb_substr(trim((string) $errorMessage), 0, 500);
  $stmt = $conn->prepare("
    UPDATE notifikasi_whatsapp
    SET status='failed', jumlah_percobaan=jumlah_percobaan+1,
        error_message=?, updated_at=CURRENT_TIMESTAMP
    WHERE id_notifikasi=?
  ");
  $stmt->bind_param('si', $safeError, $idNotifikasi);
  $stmt->execute();
  $stmt->close();
}
