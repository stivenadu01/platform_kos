<?php

function getKampusUntukHome($limit = 6)
{
  $conn = db();
  $limit = max(1, min((int) $limit, 12));

  $sql = "
    SELECT id_kampus, nama_kampus, alamat, latitude, longitude
    FROM kampus
    ORDER BY nama_kampus ASC
    LIMIT {$limit}
  ";

  $result = $conn->query($sql);
  $data = [];

  while ($row = $result->fetch_assoc()) {
    $data[] = $row;
  }

  return $data;
}

function getSemuaKampus()
{
  $conn = db();

  $result = $conn->query("
    SELECT id_kampus, nama_kampus, alamat, latitude, longitude
    FROM kampus
    ORDER BY nama_kampus ASC
  ");

  $data = [];
  while ($row = $result->fetch_assoc()) {
    $data[] = $row;
  }

  return $data;
}
