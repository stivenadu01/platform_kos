<?php
// CLI integration test. Always uses a new isolated database and removes it.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->load();
date_default_timezone_set('Asia/Makassar');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$connection = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS']);
$connection->set_charset('utf8mb4');
$database = 'betakos_test_notifications_' . bin2hex(random_bytes(5));
function db() { return $GLOBALS['connection']; }
function model($name) { require_once __DIR__ . '/../app/models/' . $name . '.php'; }
function query($key,$default=null) { return $GLOBALS['testQuery'][$key] ?? $default; }
function input() { return ['user_id'=>$_SESSION['user']['id_user']] + ($GLOBALS['testInput'] ?? []); }
function response($body,$status=200) { return ['body'=>$body,'status'=>$status]; }
function check($condition,$message) { if (!$condition) throw new RuntimeException($message); }
function executeSql($sql) {
  db()->multi_query($sql);
  do { if ($result=db()->store_result()) $result->free(); } while (db()->more_results() && db()->next_result());
}
try {
  $dump = str_replace('`betakos`','`'.$database.'`',file_get_contents(__DIR__.'/../data.sql'));
  executeSql($dump);
  db()->select_db($database);
  db()->query("SET time_zone='+08:00'");
  executeSql(file_get_contents(__DIR__.'/../app/migrations/betakos_notifications_migration.sql'));
  model('Notifikasi');
  require __DIR__.'/../app/controllers/api/ApiNotifikasiController.php';
  $controller=new ApiNotifikasiController();
  foreach (['admin','pemilik','pelanggan'] as $role) {
    $user=db()->query("SELECT id_user,role FROM users WHERE role='$role' ORDER BY id_user LIMIT 1")->fetch_assoc();
    $_SESSION['user']=$user;
    $first=$controller->index(); $second=$controller->index();
    check($first['body']['success']??false,'Source queries failed for '.$role);
    check($first['body']['data']['latest']===$second['body']['data']['latest'],'Repeated polling duplicated events');
    foreach ($first['body']['data']['items'] as $item) check(str_ends_with($item['tanggal'],'+08:00'),'Timezone missing');
    echo "$role source queries and idempotency passed\n";
  }
  $_SESSION['user']=['id_user'=>2,'role'=>'pemilik'];
  $own=$controller->index()['body']['data'];
  db()->query("INSERT INTO notifikasi (id_user,event_key,judul,pesan,tujuan,tanggal) VALUES (9,SHA2('foreign-fixture',256),'Fixture','Fixture','/user/kos-saya',NOW())");
  $foreign=db()->insert_id;
  $testInput=['id'=>(int)$foreign]; $controller->read();
  check(db()->query('SELECT dibaca_pada FROM notifikasi WHERE id_notifikasi='.(int)$foreign)->fetch_assoc()['dibaca_pada']===null,'Read crossed account boundary');
  $id=(int)$own['items'][0]['id'];
  $testInput=['perangkat'=>'11111111-1111-4111-8111-111111111111','ids'=>[$id,(int)$foreign]];
  check($controller->claim()['body']['data']['ids']===[$id],'Claim crossed account boundary');
  check($controller->claim()['body']['data']['ids']===[],'Duplicate device claim accepted');
  $testInput=['upto'=>$own['latest']]; $controller->read();
  check($controller->index()['body']['data']['unread']===0,'Read-all failed');
  $testInput=['id'=>'bad']; check($controller->read()['status']===422,'Invalid notification ID accepted');
  $testInput=['perangkat'=>'bad','ids'=>[$id]]; check($controller->claim()['status']===422,'Invalid device accepted');
  echo "Recipient isolation, read-all, validation and device deduplication passed\n";
} finally {
  if (!preg_match('/^betakos_test_notifications_[a-f0-9]{10}$/D',$database)) throw new RuntimeException('Unsafe cleanup target');
  db()->query('DROP DATABASE IF EXISTS `'.$database.'`');
  echo "Isolated test database removed\n";
}
