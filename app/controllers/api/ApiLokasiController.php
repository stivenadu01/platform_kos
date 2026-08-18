<?php

class ApiLokasiController
{
  public function search()
  {
    $q = trim($_GET['q'] ?? '');

    if ($q === '' || mb_strlen($q) < 3) {
      response([
        'success' => false,
        'message' => 'Masukkan minimal 3 karakter untuk mencari lokasi.'
      ], 422);
    }

    // Gunakan beberapa variasi query. Ini membantu pencarian singkat seperti
    // "stik" agar tetap menemukan STIKOM Uyelindo.
    $queries = [
      $q . ', Kupang, Nusa Tenggara Timur, Indonesia',
      $q . ', Kota Kupang, Indonesia',
      $q . ', Indonesia'
    ];

    $data = [];
    $seen = [];

    foreach ($queries as $query) {
      $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
        'q' => $query,
        'format' => 'jsonv2',
        'addressdetails' => 1,
        'limit' => 8,
        'countrycodes' => 'id',
        'accept-language' => 'id'
      ]);

      $ch = curl_init($url);
      curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 8,
        CURLOPT_CONNECTTIMEOUT => 4,
        CURLOPT_HTTPHEADER => [
          'Accept: application/json',
          'User-Agent: PlatformKosKupang/1.0 (location search)'
        ]
      ]);

      $body = curl_exec($ch);
      $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
      $curlError = curl_error($ch);
      curl_close($ch);

      if ($body === false || $curlError || $httpCode < 200 || $httpCode >= 300) {
        continue;
      }

      $items = json_decode($body, true);
      if (!is_array($items)) continue;

      foreach ($items as $item) {
        if (!isset($item['lat'], $item['lon'])) continue;

        $lat = (float)$item['lat'];
        $lng = (float)$item['lon'];
        $displayName = trim($item['display_name'] ?? $q);
        $key = $lat . ',' . $lng;

        if (isset($seen[$key])) continue;
        $seen[$key] = true;

        $data[] = [
          'nama' => $displayName,
          'latitude' => $lat,
          'longitude' => $lng,
          'type' => $item['type'] ?? null
        ];
      }

      if (count($data) >= 6) break;
    }

    // Prioritaskan hasil yang nama tempatnya benar-benar mengandung input.
    $needle = mb_strtolower($q);
    usort($data, function ($a, $b) use ($needle) {
      $aName = mb_strtolower($a['nama']);
      $bName = mb_strtolower($b['nama']);
      $aPos = mb_strpos($aName, $needle);
      $bPos = mb_strpos($bName, $needle);
      $aScore = $aPos === false ? 999999 : $aPos;
      $bScore = $bPos === false ? 999999 : $bPos;
      return $aScore <=> $bScore;
    });

    response([
      'success' => true,
      'data' => array_slice($data, 0, 6)
    ]);
  }
}
