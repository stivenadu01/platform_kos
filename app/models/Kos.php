<?php

function getKosByPemilik($id_pemilik)
{
  $conn = db();

  $stmt = $conn->prepare("
    SELECT
      k.id_kos,
      k.nama_kos,
      k.alamat,
      k.latitude,
      k.longitude,
      k.jenis,
      k.deskripsi,
      k.status,
      k.created_at,
      k.updated_at,
      COUNT(DISTINCT km.id_kamar) AS jumlah_kamar,
      COUNT(DISTINCT CASE WHEN km.status = 'tersedia' THEN km.id_kamar END) AS kamar_tersedia,
      COUNT(DISTINCT CASE WHEN km.status = 'terisi' THEN km.id_kamar END) AS kamar_terisi
    FROM kos k
    LEFT JOIN kamar km ON km.id_kos = k.id_kos
    WHERE k.id_pemilik = ?
    GROUP BY k.id_kos
    ORDER BY k.id_kos DESC
  ");

  $stmt->bind_param('i', $id_pemilik);
  $stmt->execute();

  $result = $stmt->get_result();
  $data = $result->fetch_all(MYSQLI_ASSOC);

  $stmt->close();

  return $data;
}


function findKosById($id_kos, $id_pemilik)
{
  $conn = db();

  $stmt = $conn->prepare("
    SELECT
      id_kos,
      id_pemilik,
      nama_kos,
      alamat,
      latitude,
      longitude,
      jenis,
      deskripsi,
      status,
      created_at,
      updated_at
    FROM kos
    WHERE id_kos = ?
      AND id_pemilik = ?
    LIMIT 1
  ");

  $stmt->bind_param('ii', $id_kos, $id_pemilik);
  $stmt->execute();

  $data = $stmt->get_result()->fetch_assoc();

  $stmt->close();

  return $data;
}


function createKos($id_pemilik, $data)
{
  $conn = db();

  $nama_kos = trim($data['nama_kos']);
  $alamat = trim($data['alamat']);
  $latitude = (float) $data['latitude'];
  $longitude = (float) $data['longitude'];
  $jenis = $data['jenis'];
  $deskripsi = trim($data['deskripsi'] ?? '');

  $stmt = $conn->prepare("
    INSERT INTO kos (
      id_pemilik,
      nama_kos,
      alamat,
      latitude,
      longitude,
      jenis,
      deskripsi,
      status
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, 'draft')
  ");

  $stmt->bind_param(
    'issddss',
    $id_pemilik,
    $nama_kos,
    $alamat,
    $latitude,
    $longitude,
    $jenis,
    $deskripsi
  );

  $success = $stmt->execute();
  $id = $stmt->insert_id;

  $stmt->close();

  return $success ? $id : false;
}


function updateKos($id_kos, $id_pemilik, $data)
{
  $conn = db();

  $nama_kos = trim($data['nama_kos']);
  $alamat = trim($data['alamat']);
  $latitude = (float) $data['latitude'];
  $longitude = (float) $data['longitude'];
  $jenis = $data['jenis'];
  $deskripsi = trim($data['deskripsi'] ?? '');

  $stmt = $conn->prepare("
    UPDATE kos
    SET
      nama_kos = ?,
      alamat = ?,
      latitude = ?,
      longitude = ?,
      jenis = ?,
      deskripsi = ?
    WHERE id_kos = ?
      AND id_pemilik = ?
  ");

  $stmt->bind_param(
    'ssddssii',
    $nama_kos,
    $alamat,
    $latitude,
    $longitude,
    $jenis,
    $deskripsi,
    $id_kos,
    $id_pemilik
  );

  $success = $stmt->execute();

  $stmt->close();

  return $success;
}


function deleteKos($id_kos, $id_pemilik)
{
  $conn = db();

  $stmt = $conn->prepare("
    DELETE FROM kos
    WHERE id_kos = ?
      AND id_pemilik = ?
  ");

  $stmt->bind_param('ii', $id_kos, $id_pemilik);

  $success = $stmt->execute();

  $stmt->close();

  return $success;
}
