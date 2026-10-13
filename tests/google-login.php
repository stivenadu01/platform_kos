<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__.'/../vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__.'/..')->load();
define('ROOT_PATH',__DIR__.'/../'); define('BASE_URL','http://localhost/BetaKos');
session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$connection=new mysqli($_ENV['DB_HOST'],$_ENV['DB_USER'],$_ENV['DB_PASS']);
$source=$_ENV['DB_NAME'];
if (!preg_match('/^[a-zA-Z0-9_]+$/D',$source)) throw new RuntimeException('Invalid source database');
$database='betakos_test_google_'.bin2hex(random_bytes(5));
function db() { return $GLOBALS['connection']; }
function model($name) { require_once ROOT_PATH.'app/models/'.$name.'.php'; }
function input() { return $GLOBALS['testInput']; }
function response($body,$status=200) { return ['body'=>$body,'status'=>$status]; }
function check($value,$message) { if (!$value) throw new RuntimeException($message); }
require ROOT_PATH.'app/services/GoogleAuthService.php';
class FixtureIdentity extends GoogleAuthService {
  public function verifyIdToken(string $idToken): array { return $GLOBALS['claims']; }
}
require ROOT_PATH.'app/controllers/api/ApiAuthController.php';
class FixtureLoginController extends ApiAuthController {
  protected function googleIdentityService(): GoogleAuthService { return new FixtureIdentity(); }
}
$_SERVER['REMOTE_ADDR']='google-fixture-'.bin2hex(random_bytes(5));
try {
  db()->query('CREATE DATABASE `'.$database.'`'); db()->select_db($database);
  db()->query('CREATE TABLE users LIKE `'.$source.'`.users');
  db()->query("INSERT INTO users (nama,email,password,role,email_verified_at) VALUES ('Fixture Owner','betakos.fixture@gmail.com','fixture-hash','pemilik',NOW())");
  $controller=new FixtureLoginController();
  $claims=['sub'=>'fixture-sub','email'=>'betakos.fixture@gmail.com','nama'=>'Fixture','picture'=>'','nonce'=>'fixture-nonce','authoritative_email'=>true];
  $testInput=['credential'=>'fixture.signed.token'];
  $_SESSION=['google_identity_nonces'=>['fixture-nonce'=>time()+300]];
  $result=$controller->googleCredential();
  check($result['body']['success']??false,'Existing account login failed');
  check($result['body']['data']['redirect_url']===BASE_URL.'/pemilik','Wrong role destination');
  check(!isset($_SESSION['user']['password']),'Password leaked to session');
  check(findUserByGoogleSub('fixture-sub')!==null,'Google account not linked');
  $_SESSION=[];
  check($controller->googleCredential()['status']===401,'Nonce replay accepted');
  $claims['sub']='new-sub'; $claims['email']='new.fixture@gmail.com';
  $_SESSION=['google_identity_nonces'=>['fixture-nonce'=>time()+300]];
  $testInput['role']='admin';
  $result=$controller->googleCredential();
  check($result['body']['data']['redirect_url']===BASE_URL.'/register?google=1','New identity registration failed');
  check($_SESSION['google_pending_registration']['role']==='','Client chose admin role');
  check(db()->query('SELECT COUNT(*) n FROM users')->fetch_assoc()['n']==1,'Registration silently created account');
  $claims['sub']='fixture-sub';$claims['email']='betakos.fixture@gmail.com';
  db()->query("UPDATE users SET status='nonaktif'");
  $_SESSION=['google_identity_nonces'=>['fixture-nonce'=>time()+300]];
  check($controller->googleCredential()['status']===403,'Inactive account accepted');
  echo "Existing-account login, account linking, role destination, nonce replay, registration and inactive-account checks passed\n";
} finally {
  if (!preg_match('/^betakos_test_google_[a-f0-9]{10}$/D',$database)) throw new RuntimeException('Unsafe cleanup');
  db()->query('DROP DATABASE IF EXISTS `'.$database.'`');
  echo "Isolated Google test database removed\n";
  session_destroy();
}
