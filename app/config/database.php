<?php

function db()
{
  $host = $_ENV['DB_HOST'];
  $user = $_ENV['DB_USER'];
  $pass = $_ENV['DB_PASS'];
  $name = $_ENV['DB_NAME'];

  static $conn;
  if ($conn === null) {
    $conn = new mysqli($host, $user, $pass, $name);

    if ($conn->connect_error) {
      die("Koneksi gagal: " . $conn->connect_error);
    }


    // 🔥 FIX TIMEZONE MYSQL
    $conn->query("SET time_zone = '+08:00'");
    // atau:
    // $conn->query("SET time_zone = 'Asia/Makassar'");
    $conn->query("SET NAMES utf8mb4");
  }

  return $conn;
}
