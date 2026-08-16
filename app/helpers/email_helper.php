<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendVerificationEmail($email, $token)
{
  $mail = new PHPMailer(true);

  try {
    $verifyUrl = BASE_URL . "/verify?token=" . urlencode($token);

    // SMTP Config (Gmail)
    $mail->isSMTP();
    $mail->Host = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USERNAME'];
    $mail->Password = $_ENV['SMTP_PASSWORD'];
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    // Email
    $mail->setFrom('no-reply@betakos.com', 'BetaKos');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Verifikasi Akun Anda';
    $mail->Body = "
    <div style='font-family: Arial, sans-serif; background:#f4f4f4; padding:40px 0'>
      <div style='max-width:500px;margin:auto;background:white;padding:30px;border-radius:10px;text-align:center'>
    
        <img src='" . BASE_URL . "/assets/icon/logo.png' style='width:60px;margin-bottom:20px'>
    
        <h2 style='margin-bottom:10px;'>Verifikasi Akun Anda</h2>
    
        <p style='color:#555;font-size:14px'>
          Terima kasih telah mendaftar di <b>BetaKos</b>.
        </p>
    
        <p style='color:#555;font-size:14px;margin-bottom:25px'>
          Klik tombol di bawah untuk mengaktifkan akun Anda.
        </p>
    
        <a href='$verifyUrl'
           style='display:inline-block;padding:12px 20px;
                  background:#2563eb;color:white;
                  text-decoration:none;border-radius:6px;
                  font-weight:bold'>
          Verifikasi Sekarang
        </a>
    
        <p style='font-size:12px;color:#888;margin-top:25px'>
          Atau copy link ini:
        </p>
    
        <p style='font-size:12px;color:#555;word-break:break-all'>
          $verifyUrl
        </p>
    
        <p style='font-size:11px;color:#aaa;margin-top:20px'>
          Link berlaku selama 24 jam.
        </p>
    
      </div>
    </div>
    ";

    $mail->send();
    return true;
  } catch (Exception $e) {
    throw new Exception("Email gagal dikirim: " . $mail->ErrorInfo, 500);
  }
}

function sendResetToken($email, $token)
{
  $mail = new PHPMailer(true);

  try {
    $resetUrl = BASE_URL . "/reset-password?token=" . urlencode($token);

    // SMTP Config (Gmail)
    $mail->isSMTP();
    $mail->Host = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USERNAME'];
    $mail->Password = $_ENV['SMTP_PASSWORD'];
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    // Email
    $mail->setFrom('no-reply@betakos.com', 'BetaKos');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Reset Kata Sandi';
    $mail->Body = "
    <div style='font-family: Arial, sans-serif; background:#f4f4f4; padding:40px 0'>
      <div style='max-width:500px;margin:auto;background:white;padding:30px;border-radius:10px;text-align:center'>

        <img src='" . BASE_URL . "/assets/icon/logo.png' style='width:60px;margin-bottom:20px'>

        <h2 style='margin-bottom:10px;'>Reset Kata Sandi</h2>

        <p style='color:#555;font-size:14px'>
          Anda menerima email ini karena ada permintaan reset kata sandi untuk akun Anda.
        </p>

        <p style='color:#555;font-size:14px;margin-bottom:25px'>
          Klik tombol di bawah untuk membuat kata sandi baru.
        </p>

        <a href='$resetUrl'
           style='display:inline-block;padding:12px 20px;
                  background:#2563eb;color:white;
                  text-decoration:none;border-radius:6px;
                  font-weight:bold'>
          Reset Sekarang
        </a>

        <p style='font-size:12px;color:#888;margin-top:25px'>
          Atau salin link ini:
        </p>

        <p style='font-size:12px;color:#555;word-break:break-all'>
          $resetUrl
        </p>

        <p style='font-size:11px;color:#aaa;margin-top:20px'>
          Link berlaku selama 1 jam.
        </p>

      </div>
    </div>
    ";

    $mail->send();
    return true;
  } catch (Exception $e) {
    throw new Exception("Email gagal dikirim: " . $mail->ErrorInfo, 500);
  }
}
