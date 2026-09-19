<?php

/**
 * Normalisasi nomor seluler Indonesia untuk penyimpanan internal.
 * Format hasil: 08xxxxxxxxxx.
 */
function normalizeIndonesianPhone($value)
{
  $phone = preg_replace('/[^0-9+]/', '', trim((string) $value));

  if (str_starts_with($phone, '+62')) {
    $phone = '0' . substr($phone, 3);
  } elseif (str_starts_with($phone, '62')) {
    $phone = '0' . substr($phone, 2);
  }

  if (!preg_match('/^08[1-9][0-9]{7,10}$/', $phone)) {
    throw new Exception(
      'Nomor HP harus berupa nomor seluler Indonesia yang valid (contoh: 081234567890).',
      422
    );
  }

  return $phone;
}

/**
 * Format nomor tujuan WhatsApp dalam format internasional tanpa tanda +.
 */
function formatPhoneForWhatsApp($value)
{
  $phone = normalizeIndonesianPhone($value);
  return '62' . substr($phone, 1);
}
