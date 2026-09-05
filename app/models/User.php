<?php

/**
 * User Model - Phase 1 Authentication
 *
 * Schema yang digunakan:
 * - users.email_verified_at
 * - user_verification_tokens
 * - password_reset_tokens
 *
 * Token plaintext TIDAK disimpan ke database.
 * Database hanya menyimpan SHA-256 token.
 */


/* =========================================================
   BASIC USER
   ========================================================= */

function findUser($id)
{
  $conn = db();

  $stmt = $conn->prepare("
    SELECT *
    FROM users
    WHERE id_user = ?
    LIMIT 1
  ");

  $stmt->bind_param('i', $id);
  $stmt->execute();

  $user = $stmt->get_result()->fetch_assoc();

  $stmt->close();

  return $user;
}


function findUserByEmail($email)
{
  $conn = db();

  $stmt = $conn->prepare("
    SELECT *
    FROM users
    WHERE email = ?
    LIMIT 1
  ");

  $stmt->bind_param("s", $email);
  $stmt->execute();

  $user = $stmt->get_result()->fetch_assoc();

  $stmt->close();

  return $user;
}


function findUserByNik($nik)
{
  $conn = db();

  $stmt = $conn->prepare("
    SELECT *
    FROM users
    WHERE nik = ?
    LIMIT 1
  ");
  $stmt->bind_param('s', $nik);
  $stmt->execute();
  $user = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  return $user;
}


/**
 * Menyimpan waktu login terakhir pengguna.
 * Dipanggil hanya setelah autentikasi berhasil.
 */
function updateLastLoginAt($id_user)
{
  $conn = db();
  $stmt = $conn->prepare("UPDATE users SET last_login_at = NOW() WHERE id_user = ? LIMIT 1");
  $stmt->bind_param('i', $id_user);
  $success = $stmt->execute();
  $stmt->close();
  return $success;
}


/**
 * Tandai email sebagai terverifikasi setelah identitas tepercaya (mis. Google)
 * berhasil diverifikasi oleh server.
 */
function markUserEmailVerified($id_user)
{
  $conn = db();
  $stmt = $conn->prepare("UPDATE users SET email_verified_at = NOW() WHERE id_user = ? AND email_verified_at IS NULL LIMIT 1");
  $stmt->bind_param('i', $id_user);
  $success = $stmt->execute();
  $stmt->close();
  return $success;
}


/**
 * Rehash password menggunakan PASSWORD_DEFAULT terbaru tanpa mengubah password.
 */
function updateUserPasswordHash($id_user, $passwordRaw)
{
  $conn = db();
  $hash = password_hash($passwordRaw, PASSWORD_DEFAULT);
  $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id_user = ? LIMIT 1");
  $stmt->bind_param('si', $hash, $id_user);
  $success = $stmt->execute();
  $stmt->close();
  return $success;
}


/* =========================================================
   USER LIST
   ========================================================= */

function getUserList($page = 1, $limit = 10, $search = '', $role = '')
{
  $conn = db();

  $page = max(1, (int)$page);
  $limit = max(1, min(100, (int)$limit));
  $offset = ($page - 1) * $limit;

  $where = [];
  $params = [];
  $types = '';

  if ($search !== '') {
    $where[] = "(nama LIKE ? OR email LIKE ?)";

    $safe = "%$search%";

    $params[] = $safe;
    $params[] = $safe;

    $types .= 'ss';
  }

  if ($role !== '') {
    $where[] = "role = ?";
    $params[] = $role;
    $types .= 's';
  }

  $whereSql = $where
    ? "WHERE " . implode(' AND ', $where)
    : '';

  /* COUNT */
  $sqlCount = "
    SELECT COUNT(*) AS total
    FROM users
    $whereSql
  ";

  $stmt = $conn->prepare($sqlCount);

  if ($params) {
    $stmt->bind_param($types, ...$params);
  }

  $stmt->execute();

  $total = (int)$stmt
    ->get_result()
    ->fetch_assoc()['total'];

  $stmt->close();


  /* DATA */
  $sql = "
    SELECT
      id_user,
      nama,
      email,
      no_hp,
      alamat,
      role,
      email_verified_at,
      status,
      created_at
    FROM users
    $whereSql
    ORDER BY id_user DESC
    LIMIT ? OFFSET ?
  ";

  $dataParams = $params;
  $dataTypes = $types . 'ii';

  $dataParams[] = $limit;
  $dataParams[] = $offset;

  $stmt = $conn->prepare($sql);
  $stmt->bind_param($dataTypes, ...$dataParams);
  $stmt->execute();

  $res = $stmt->get_result();

  $data = [];

  while ($row = $res->fetch_assoc()) {
    $data[] = $row;
  }

  $stmt->close();

  return [$data, $total];
}


/* =========================================================
   CREATE USER
   ========================================================= */

function tambahUser($data, $isRegister = false)
{
  $conn = db();

  $nama = trim($data['nama'] ?? '');
  $email = strtolower(trim($data['email'] ?? ''));
  $no_hp = trim($data['no_hp'] ?? '') ?: null;
  $nik = trim($data['nik'] ?? '') ?: null;
  $passwordRaw = $data['password'] ?? '';

  if (!$nama || !$email || !$passwordRaw || !$nik) {
    throw new Exception("Data pengguna tidak lengkap", 422);
  }

  if (!preg_match('/^\d{16}$/', $nik)) {
    throw new Exception('NIK harus terdiri dari 16 digit', 422);
  }

  $password = password_hash($passwordRaw, PASSWORD_DEFAULT);

  $role = $data['role'] ?? 'pelanggan';

  if (!in_array($role, ['pelanggan', 'pemilik', 'admin'], true)) {
    throw new Exception('Role pengguna tidak valid', 422);
  }


  /*
   * REGISTER
   *
   * 1. users.email_verified_at = NULL
   * 2. generate token plaintext
   * 3. simpan SHA-256 token
   * 4. return plaintext token untuk email
   */
  if ($isRegister) {

    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);

    $expired = date(
      'Y-m-d H:i:s',
      strtotime('+1 day')
    );

    $stmt = $conn->prepare("
      INSERT INTO users
        (
          nama,
          email,
          no_hp,
          nik,
          password,
          role,
          email_verified_at,
          status
        )
      VALUES
        (?, ?, ?, ?, ?, ?, NULL, 'aktif')
    ");

    $stmt->bind_param(
      "ssssss",
      $nama,
      $email,
      $no_hp,
      $nik,
      $password,
      $role
    );

    if (!$stmt->execute()) {
      $stmt->close();
      throw new Exception(
        "Gagal membuat akun",
        500
      );
    }

    $idUser = $stmt->insert_id;

    $stmt->close();


    /*
     * Token verifikasi.
     */
    $stmt = $conn->prepare("
      INSERT INTO user_verification_tokens
        (
          id_user,
          token_hash,
          expires_at
        )
      VALUES
        (?, ?, ?)
    ");

    $stmt->bind_param(
      "iss",
      $idUser,
      $tokenHash,
      $expired
    );

    if (!$stmt->execute()) {
      $stmt->close();
      throw new Exception(
        "Gagal membuat token verifikasi",
        500
      );
    }

    $stmt->close();

    return [
      'id_user' => $idUser,
      'email' => $email,
      'token' => $token
    ];
  }


  /*
   * ADMIN / INTERNAL CREATE
   *
   * User yang dibuat oleh admin langsung dianggap
   * terverifikasi karena tidak melalui registrasi publik.
   */
  $verifiedAt = date('Y-m-d H:i:s');

  $stmt = $conn->prepare("
    INSERT INTO users
      (
        nama,
        email,
        no_hp,
        nik,
        password,
        role,
        email_verified_at,
        status
      )
    VALUES
      (?, ?, ?, ?, ?, ?, ?, 'aktif')
  ");

  $stmt->bind_param(
    "sssssss",
    $nama,
    $email,
    $no_hp,
    $nik,
    $password,
    $role,
    $verifiedAt
  );

  $res = $stmt->execute();

  $stmt->close();

  return $res;
}


/* =========================================================
   GOOGLE AUTHENTICATION
   ========================================================= */

function findUserByGoogleSub($googleSub)
{
  $conn = db();

  $stmt = $conn->prepare("
    SELECT *
    FROM users
    WHERE google_sub = ?
    LIMIT 1
  ");
  $stmt->bind_param('s', $googleSub);
  $stmt->execute();
  $user = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  return $user;
}


/**
 * Menghubungkan akun Google terverifikasi ke akun BetaKos yang sudah ada.
 * Dipanggil di dalam transaction setelah email Google diverifikasi server.
 */
function linkGoogleSubToUser($idUser, $googleSub)
{
  $conn = db();

  $idUser = (int) $idUser;
  $googleSub = trim((string) $googleSub);

  if ($idUser <= 0 || $googleSub === '') {
    throw new Exception('Data penghubung akun Google tidak valid.', 422);
  }

  $stmt = $conn->prepare("
    UPDATE users
    SET google_sub = ?
    WHERE id_user = ?
      AND (google_sub IS NULL OR google_sub = '')
    LIMIT 1
  " );
  $stmt->bind_param('si', $googleSub, $idUser);
  $success = $stmt->execute();
  $affected = $stmt->affected_rows;
  $error = $stmt->error;
  $stmt->close();

  if (!$success) {
    error_log('Google account linking DB error: ' . $error);
    throw new Exception('Gagal menghubungkan akun Google.', 500);
  }

  return $affected === 1;
}

function createGoogleUser($data)
{
  $conn = db();

  $nama = trim($data['nama'] ?? '');
  $email = strtolower(trim($data['email'] ?? ''));
  $no_hp = trim($data['no_hp'] ?? '');
  $nik = trim($data['nik'] ?? '');
  $role = trim($data['role'] ?? '');
  $googleSub = trim($data['google_sub'] ?? '');

  if (!$nama || !$email || !$no_hp || !$nik || !$role || !$googleSub) {
    throw new Exception('Data pendaftaran Google belum lengkap', 422);
  }

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    throw new Exception('Email tidak valid', 422);
  }

  if (!preg_match('/^\d{16}$/', $nik)) {
    throw new Exception('NIK harus terdiri dari 16 digit', 422);
  }

  if (!in_array($role, ['pelanggan', 'pemilik'], true)) {
    throw new Exception('Jenis akun tidak valid', 422);
  }

  // Akun Google tetap memiliki password internal agar kompatibel dengan
  // mekanisme login/password dan reset password yang sudah ada.
  $password = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);
  $verifiedAt = date('Y-m-d H:i:s');

  $stmt = $conn->prepare("
    INSERT INTO users
      (nama, email, no_hp, nik, password, role, google_sub, email_verified_at, status)
    VALUES
      (?, ?, ?, ?, ?, ?, ?, ?, 'aktif')
  ");

  $stmt->bind_param(
    'ssssssss',
    $nama,
    $email,
    $no_hp,
    $nik,
    $password,
    $role,
    $googleSub,
    $verifiedAt
  );

  if (!$stmt->execute()) {
    $error = $stmt->error;
    $stmt->close();
    error_log('Google account creation DB error: ' . $error);
    throw new Exception('Gagal membuat akun Google.', 500);
  }

  $idUser = $stmt->insert_id;
  $stmt->close();

  return $idUser;
}


/* =========================================================
   EDIT USER
   ========================================================= */

function editUser($id, $data)
{
  $conn = db();

  $nama = trim($data['nama'] ?? '');
  $email = strtolower(trim($data['email'] ?? ''));
  $no_hp = trim($data['no_hp'] ?? '') ?: null;
  $alamat = trim($data['alamat'] ?? '') ?: null;
  $role = $data['role'] ?? 'pelanggan';

  if (!$nama || !$email) {
    throw new Exception(
      "Nama dan email wajib diisi",
      422
    );
  }

  if (empty($data['password'])) {

    $stmt = $conn->prepare("
      UPDATE users
      SET
        nama = ?,
        email = ?,
        no_hp = ?,
        alamat = ?,
        role = ?
      WHERE id_user = ?
    ");

    $stmt->bind_param(
      "sssssi",
      $nama,
      $email,
      $no_hp,
      $alamat,
      $role,
      $id
    );
  } else {

    $hashed = password_hash(
      $data['password'],
      PASSWORD_DEFAULT
    );

    $stmt = $conn->prepare("
      UPDATE users
      SET
        nama = ?,
        email = ?,
        no_hp = ?,
        alamat = ?,
        password = ?,
        role = ?
      WHERE id_user = ?
    ");

    $stmt->bind_param(
      "ssssssi",
      $nama,
      $email,
      $no_hp,
      $alamat,
      $hashed,
      $role,
      $id
    );
  }

  $res = $stmt->execute();

  $stmt->close();

  return $res;
}


/* =========================================================
   DELETE USER
   ========================================================= */

function hapusUser($id)
{
  $conn = db();

  $stmt = $conn->prepare("
    DELETE FROM users
    WHERE id_user = ?
  ");

  $stmt->bind_param('i', $id);

  $res = $stmt->execute();

  $stmt->close();

  return $res;
}


/* =========================================================
   EMAIL VERIFICATION
   ========================================================= */

function findUserVerificationByToken($token)
{
  $conn = db();

  if (!$token) {
    return null;
  }

  $tokenHash = hash('sha256', $token);

  $stmt = $conn->prepare("
    SELECT
      vt.id_token,
      vt.id_user,
      vt.token_hash,
      vt.expires_at,
      vt.used_at,
      u.email,
      u.nama,
      u.email_verified_at,
      u.status
    FROM user_verification_tokens vt
    INNER JOIN users u
      ON u.id_user = vt.id_user
    WHERE vt.token_hash = ?
    LIMIT 1
  ");

  $stmt->bind_param(
    "s",
    $tokenHash
  );

  $stmt->execute();

  $verification = $stmt
    ->get_result()
    ->fetch_assoc();

  $stmt->close();

  return $verification;
}


/*
 * Backward-compatible alias.
 * AuthController Phase 1 memakai findUserVerificationByToken().
 */
function findUserByToken($token)
{
  return findUserVerificationByToken($token);
}


function verifyUser($id_user, $id_token = null)
{
  $conn = db();

  $conn->begin_transaction();

  try {

    /*
     * Pastikan user masih ada dan belum diverifikasi.
     */
    $stmt = $conn->prepare("
      SELECT
        id_user,
        email_verified_at
      FROM users
      WHERE id_user = ?
      LIMIT 1
    ");

    $stmt->bind_param(
      "i",
      $id_user
    );

    $stmt->execute();

    $user = $stmt
      ->get_result()
      ->fetch_assoc();

    $stmt->close();

    if (!$user) {
      throw new Exception(
        "User tidak ditemukan",
        404
      );
    }


    /*
     * Tandai email sudah terverifikasi.
     */
    $stmt = $conn->prepare("
      UPDATE users
      SET email_verified_at = NOW()
      WHERE id_user = ?
        AND email_verified_at IS NULL
    ");

    $stmt->bind_param(
      "i",
      $id_user
    );

    if (!$stmt->execute()) {
      throw new Exception(
        "Gagal memverifikasi akun",
        500
      );
    }

    $stmt->close();


    /*
     * Token harus digunakan satu kali.
     */
    if ($id_token !== null) {

      $stmt = $conn->prepare("
        UPDATE user_verification_tokens
        SET used_at = NOW()
        WHERE id_token = ?
          AND id_user = ?
          AND used_at IS NULL
      ");

      $stmt->bind_param(
        "ii",
        $id_token,
        $id_user
      );

      if (!$stmt->execute()) {
        throw new Exception(
          "Gagal menyelesaikan token verifikasi",
          500
        );
      }

      $stmt->close();
    } else {

      /*
       * Fallback jika dipanggil tanpa id_token.
       * Semua token aktif user dinonaktifkan.
       */
      $stmt = $conn->prepare("
        UPDATE user_verification_tokens
        SET used_at = NOW()
        WHERE id_user = ?
          AND used_at IS NULL
      ");

      $stmt->bind_param(
        "i",
        $id_user
      );

      $stmt->execute();
      $stmt->close();
    }

    $conn->commit();

    return true;
  } catch (Exception $e) {

    $conn->rollback();

    throw $e;
  }
}


/* =========================================================
   PASSWORD RESET
   ========================================================= */

function savePasswordResetToken($id_user, $token, $expired)
{
  $conn = db();

  if (!$id_user || !$token || !$expired) {
    throw new Exception(
      "Data reset password tidak lengkap",
      422
    );
  }

  $tokenHash = hash('sha256', $token);

  /*
   * Hapus token lama yang masih aktif untuk user ini.
   * Token lama otomatis tidak dapat digunakan lagi.
   */
  $stmt = $conn->prepare("
    DELETE FROM password_reset_tokens
    WHERE id_user = ?
      AND used_at IS NULL
  ");

  $stmt->bind_param(
    "i",
    $id_user
  );

  $stmt->execute();
  $stmt->close();


  /*
   * Simpan token baru.
   */
  $stmt = $conn->prepare("
    INSERT INTO password_reset_tokens
      (
        id_user,
        token_hash,
        expires_at
      )
    VALUES
      (?, ?, ?)
  ");

  $stmt->bind_param(
    "iss",
    $id_user,
    $tokenHash,
    $expired
  );

  $res = $stmt->execute();

  if (!$res) {
    $stmt->close();

    throw new Exception(
      "Gagal menyimpan token reset password",
      500
    );
  }

  $stmt->close();

  return true;
}


function findPasswordResetByToken($token)
{
  $conn = db();

  if (!$token) {
    return null;
  }

  $tokenHash = hash('sha256', $token);

  $stmt = $conn->prepare("
    SELECT
      pr.id_reset,
      pr.id_user,
      pr.token_hash,
      pr.expires_at,
      pr.used_at,
      u.email,
      u.nama,
      u.status
    FROM password_reset_tokens pr
    INNER JOIN users u
      ON u.id_user = pr.id_user
    WHERE pr.token_hash = ?
    LIMIT 1
  ");

  $stmt->bind_param(
    "s",
    $tokenHash
  );

  $stmt->execute();

  $res = $stmt
    ->get_result()
    ->fetch_assoc();

  $stmt->close();

  return $res;
}


function markPasswordResetTokenUsed($id_reset)
{
  $conn = db();

  $stmt = $conn->prepare("
    UPDATE password_reset_tokens
    SET used_at = NOW()
    WHERE id_reset = ?
      AND used_at IS NULL
  ");

  $stmt->bind_param(
    "i",
    $id_reset
  );

  $res = $stmt->execute();

  $affected = $stmt->affected_rows;

  $stmt->close();

  if (!$res || $affected !== 1) {
    throw new Exception(
      "Token reset tidak dapat digunakan",
      400
    );
  }

  return true;
}


function updateUserPassword($id_user, $newPassword)
{
  $conn = db();

  if (!$id_user || !$newPassword) {
    throw new Exception(
      "Data password tidak lengkap",
      422
    );
  }

  if (strlen($newPassword) < 8) {
    throw new Exception(
      "Kata sandi minimal 8 karakter",
      422
    );
  }

  $hashed = password_hash(
    $newPassword,
    PASSWORD_DEFAULT
  );

  $stmt = $conn->prepare("
    UPDATE users
    SET password = ?
    WHERE id_user = ?
  ");

  $stmt->bind_param(
    "si",
    $hashed,
    $id_user
  );

  $res = $stmt->execute();

  $stmt->close();

  if (!$res) {
    throw new Exception(
      "Gagal memperbarui password",
      500
    );
  }

  return true;
}


/* =========================================================
   PEMILIK PROFILE
   ========================================================= */

function updateUserProfile($id_user, $data)
{
  $conn = db();

  $id_user = (int)$id_user;
  $nama = trim($data['nama'] ?? '');
  $no_hp = trim($data['no_hp'] ?? '') ?: null;

  if ($id_user <= 0) {
    throw new Exception('User tidak valid', 422);
  }

  if ($nama === '') {
    throw new Exception('Nama wajib diisi', 422);
  }

  // Email sengaja tidak dapat diubah melalui endpoint profil.
  // UI juga menampilkannya sebagai readonly; backend wajib menegakkan aturan ini
  // agar request yang dibuat manual tidak dapat mengganti email terverifikasi.
  $stmt = $conn->prepare("UPDATE users SET nama = ?, no_hp = ? WHERE id_user = ? LIMIT 1");
  $stmt->bind_param('ssi', $nama, $no_hp, $id_user);

  if (!$stmt->execute()) {
    $stmt->close();
    throw new Exception('Gagal memperbarui profil', 500);
  }

  $affected = $stmt->affected_rows;
  $stmt->close();

  // affected_rows dapat 0 bila data memang tidak berubah; itu bukan error.
  return $affected >= 0;
}

function updateUserFoto($id_user, $foto)
{
  $conn = db();

  $stmt = $conn->prepare("UPDATE users SET foto = ? WHERE id_user = ?");
  $stmt->bind_param('si', $foto, $id_user);

  if (!$stmt->execute()) {
    $stmt->close();
    throw new Exception('Gagal memperbarui foto profil', 500);
  }

  $stmt->close();
  return true;
}


function changeUserPassword($id_user, $password_lama, $password_baru)
{
  $conn = db();

  if (strlen($password_baru) < 8) {
    throw new Exception('Kata sandi baru minimal 8 karakter', 422);
  }

  $stmt = $conn->prepare("SELECT password FROM users WHERE id_user = ? LIMIT 1");
  $stmt->bind_param('i', $id_user);
  $stmt->execute();
  $user = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$user || !password_verify($password_lama, $user['password'])) {
    throw new Exception('Kata sandi lama tidak benar', 400);
  }

  $hash = password_hash($password_baru, PASSWORD_DEFAULT);

  $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id_user = ?");
  $stmt->bind_param('si', $hash, $id_user);

  if (!$stmt->execute()) {
    $stmt->close();
    throw new Exception('Gagal mengubah kata sandi', 500);
  }

  $stmt->close();
  return true;
}
