<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
ob_start();
require __DIR__.'/../vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__.'/..')->load();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$connection=new mysqli($_ENV['DB_HOST'],$_ENV['DB_USER'],$_ENV['DB_PASS']);
$connection->set_charset('utf8mb4');
$database='betakos_test_navigation_'.bin2hex(random_bytes(5));
function db(){return $GLOBALS['connection'];}
function check($value,$message){if(!$value)throw new RuntimeException($message);}
function executeSql($sql){db()->multi_query($sql);do{if($result=db()->store_result())$result->free();}while(db()->more_results()&&db()->next_result());}
require __DIR__.'/../app/helpers/navigation_helper.php';
require __DIR__.'/../app/controllers/api/ApiNavigationController.php';
function response($body,$status=200){return ['body'=>$body,'status'=>$status];}
try {
 executeSql(str_replace('`betakos`','`'.$database.'`',file_get_contents(__DIR__.'/../data.sql')));
 db()->select_db($database);
 check(navigationCounts(null)===[],'Guest counters exposed');
 foreach(['admin'=>['verification','reports','payments'],'pemilik'=>['properties','claims','overdue','subscription'],'pelanggan'=>['favorites','overdue','claims']] as $role=>$keys){
  $user=db()->query("SELECT id_user,role FROM users WHERE role='$role' ORDER BY id_user LIMIT 1")->fetch_assoc();
  $counts=navigationCounts($user);
  check(array_keys($counts)===$keys,'Wrong role counters');
  foreach($counts as $n)check(is_int($n)&&$n>=0,'Invalid counter');
  $_SESSION['user']=$user;
  $result=(new ApiNavigationController())->counts();
  check($result['body']['data']['user_id']===(int)$user['id_user'],'Missing recipient identity');
  echo "$role counters and API passed\n";
 }
 $owner=['id_user'=>2,'role'=>'pemilik'];
 db()->query("UPDATE kos SET status='draft' WHERE id_pemilik=2");
 check(navigationCounts($owner)['properties']===(int)db()->query('SELECT COUNT(*) n FROM kos WHERE id_pemilik=2')->fetch_assoc()['n'],'Draft badge count incorrect');
 $before=navigationCounts($owner);
 db()->query("UPDATE kos SET status='draft' WHERE id_pemilik<>2");
 db()->query("UPDATE pembayaran_langganan SET status='menunggu' WHERE id_pemilik<>2");
 check(navigationCounts($owner)===$before,'Foreign owner activity leaked');
 $customer=['id_user'=>9,'role'=>'pelanggan'];
 $before=navigationCounts($customer);
 db()->query('DELETE FROM kos_favorit WHERE id_user<>9');
 check(navigationCounts($customer)===$before,'Foreign favorites leaked');
 db()->query("UPDATE pembayaran_langganan SET status='menunggu',provider_pembayaran='midtrans',bukti_pembayaran='/fixture.jpg'");
 check(navigationCounts(['id_user'=>1,'role'=>'admin'])['payments']===0,'Gateway payments counted as manual review');
 db()->query("UPDATE pembayaran_langganan SET provider_pembayaran='manual',bukti_pembayaran=NULL");
 check(navigationCounts(['id_user'=>1,'role'=>'admin'])['payments']===0,'Payment without proof counted');
 db()->query("UPDATE pembayaran_langganan SET bukti_pembayaran='/fixture.jpg'");
 $expected=(int)db()->query('SELECT COUNT(*) n FROM pembayaran_langganan')->fetch_assoc()['n'];
 check($expected>0 && navigationCounts(['id_user'=>1,'role'=>'admin'])['payments']===$expected,'Manual proof review count incorrect');
 echo "Account isolation and actionable payment counters passed\n";
} finally {
 if(!preg_match('/^betakos_test_navigation_[a-f0-9]{10}$/D',$database))throw new RuntimeException('Unsafe cleanup target');
 db()->query('DROP DATABASE IF EXISTS `'.$database.'`');
 echo "Isolated test database removed\n";
 ob_end_flush();
}
