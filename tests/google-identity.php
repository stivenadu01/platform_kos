<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../app/services/GoogleAuthService.php';
define('GOOGLE_CLIENT_ID','betakos-google-test-client');
require __DIR__.'/../app/helpers/google_identity_helper.php';
$_SESSION['google_identity_nonces']=['valid'=>time()+60,'expired'=>time()-60];
if (!consumeGoogleIdentityNonce('valid') || consumeGoogleIdentityNonce('valid') || consumeGoogleIdentityNonce('expired') || consumeGoogleIdentityNonce('unknown')) throw new RuntimeException('Nonce session/replay handling failed');
$options=['private_key_bits'=>2048,'private_key_type'=>OPENSSL_KEYTYPE_RSA];
$xamppConfig=dirname(PHP_BINARY).'/extras/ssl/openssl.cnf';
if (is_file($xamppConfig)) $options['config']=$xamppConfig;
$key=openssl_pkey_new($options);
if ($key===false) throw new RuntimeException('RSA test key could not be generated');
if (!openssl_pkey_export($key,$private,null,$options)) throw new RuntimeException('RSA test key could not be exported');
$details=openssl_pkey_get_details($key);
$encode=static fn($value)=>rtrim(strtr(base64_encode($value),'+/','-_'),'=');
$certs=['keys'=>[['kty'=>'RSA','alg'=>'RS256','use'=>'sig','kid'=>'fixture','n'=>$encode($details['rsa']['n']),'e'=>$encode($details['rsa']['e'])]]];
$handler=static fn()=>new GuzzleHttp\Psr7\Response(200,['Content-Type'=>'application/json','Cache-Control'=>'public,max-age=3600'],json_encode($certs));
$service=new GoogleAuthService($handler);
$payload=['iss'=>'https://accounts.google.com','aud'=>GOOGLE_CLIENT_ID,'sub'=>'123456789','email'=>'betakos.test@gmail.com','email_verified'=>true,'nonce'=>'fixture-nonce','iat'=>time()-5,'exp'=>time()+300,'name'=>'Fixture'];
$token=static fn($claims)=>Firebase\JWT\JWT::encode($claims,$private,'RS256','fixture');
$valid=$service->verifyIdToken($token($payload));
if ($valid['nonce']!=='fixture-nonce' || !$valid['authoritative_email']) throw new RuntimeException('Valid token not accepted');
foreach (['aud'=>'wrong-client','iss'=>'https://attacker.example','exp'=>time()-60,'email_verified'=>false] as $claim=>$value) {
  try { $service->verifyIdToken($token(array_replace($payload,[$claim=>$value]))); }
  catch (Exception $e) { if ($e->getCode()===401) continue; throw $e; }
  throw new RuntimeException('Invalid claim accepted: '.$claim);
}
try { $service->verifyIdToken('invalid.invalid.invalid'); }
catch (Exception $e) { if ($e->getCode()!==401) throw $e; echo "Signature, audience, issuer, expiry, verified email and nonce checks passed\n"; exit; }
throw new RuntimeException('Malformed token accepted');
