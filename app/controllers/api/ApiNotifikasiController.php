<?php

class ApiNotifikasiController
{
  private function userId(): int { return (int)$_SESSION['user']['id_user']; }

  public function index()
  {
    try {
      model('Notifikasi');
      sinkronNotifikasi($_SESSION['user']);
      $uid = $this->userId();
      $before = max(0, (int)query('before', 0));
      $stmt = db()->prepare('SELECT id_notifikasi id,judul,pesan,tujuan,tanggal,dibaca_pada FROM notifikasi WHERE id_user=? AND (?=0 OR id_notifikasi<?) ORDER BY id_notifikasi DESC LIMIT 21');
      $stmt->bind_param('iii', $uid, $before, $before);
      $stmt->execute(); $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
      $more = count($items) > 20; $items = array_slice($items, 0, 20);
      // PHP/MySQL uses Asia/Makassar; transmit explicit offsets to browsers.
      foreach ($items as &$item) $item['tanggal'] = (new DateTimeImmutable($item['tanggal']))->format(DATE_ATOM);
      unset($item);
      $stmt = db()->prepare('SELECT COUNT(CASE WHEN dibaca_pada IS NULL THEN 1 END) unread,COALESCE(MAX(id_notifikasi),0) latest FROM notifikasi WHERE id_user=?');
      $stmt->bind_param('i', $uid); $stmt->execute(); $counts = $stmt->get_result()->fetch_assoc(); $stmt->close();
      return response(['success'=>true,'data'=>['items'=>$items,'more'=>$more,'unread'=>(int)$counts['unread'],'latest'=>(int)$counts['latest'],'user_id'=>$uid]]);
    } catch (Throwable $e) {
      error_log('BetaKos notifications: ' . $e->getMessage());
      return response(['success'=>false,'message'=>'Notifikasi belum dapat dimuat. Silakan coba lagi.'],500);
    }
  }

  public function read()
  {
    $data = input(); $uid = $this->userId();
    if ((int)($data['user_id'] ?? 0) !== $uid) return response(['success'=>false,'message'=>'Sesi berubah. Muat ulang halaman.'],409);
    $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
    // Snapshot bound prevents read-all from consuming events arriving afterwards.
    $upto = filter_var($data['upto'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
    if (!$id && !$upto) return response(['success'=>false,'message'=>'Notifikasi tidak valid.'],422);
    $stmt = db()->prepare('UPDATE notifikasi SET dibaca_pada=COALESCE(dibaca_pada,NOW()) WHERE id_user=? AND id_notifikasi ' . ($id ? '=?' : '<=?'));
    $target = $id ?: $upto; $stmt->bind_param('ii', $uid, $target); $stmt->execute(); $stmt->close();
    return response(['success'=>true]);
  }

  public function claim()
  {
    $data = input(); $uid = $this->userId(); $device = $data['perangkat'] ?? '';
    if ((int)($data['user_id'] ?? 0) !== $uid) return response(['success'=>false,'message'=>'Sesi berubah. Muat ulang halaman.'],409);
    if (!is_string($device) || !preg_match('/^[a-f0-9]{8}(?:-[a-f0-9]{4}){3}-[a-f0-9]{12}$/D', $device) || !is_array($data['ids'] ?? null) || count($data['ids']) > 20) {
      return response(['success'=>false,'message'=>'Permintaan notifikasi tidak valid.'],422);
    }
    $won = [];
    $stmt = db()->prepare('INSERT IGNORE INTO notifikasi_perangkat (id_notifikasi,perangkat) SELECT id_notifikasi,? FROM notifikasi WHERE id_notifikasi=? AND id_user=? AND dibaca_pada IS NULL');
    foreach ($data['ids'] as $value) {
      $id = filter_var($value,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
      if (!$id) continue;
      $stmt->bind_param('sii',$device,$id,$uid); $stmt->execute();
      if ($stmt->affected_rows === 1) $won[] = $id;
    }
    $stmt->close(); return response(['success'=>true,'data'=>['ids'=>$won]]);
  }
}
