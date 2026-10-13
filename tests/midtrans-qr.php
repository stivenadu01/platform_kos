<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__.'/../app/services/MidtransService.php';
$_ENV['MIDTRANS_SERVER_KEY']='sandbox-test-key';
$_ENV['MIDTRANS_IS_PRODUCTION']='false';
$_ENV['MIDTRANS_API_URL']='https://api.sandbox.midtrans.com';
$service=new MidtransService();
$extract=new ReflectionMethod(MidtransService::class,'extractQrCodeUrl');
$old='https://api.sandbox.midtrans.com/v2/qris/test/qr-code';
$new='https://merchants-app.sbx.midtrans.com/v4/qris/gopay/A1202610130633535HWhrT8NbiID/qr-code';
$actions=[['name'=>'generate-qr-code','url'=>$old],['name'=>'generate-qr-code-v2','url'=>$new]];
if ($extract->invoke($service,['actions'=>$actions])!==$new) throw new RuntimeException('New QR format not preferred');
if ($extract->invoke($service,['actions'=>[$actions[0]]])!==$old) throw new RuntimeException('Legacy support broken');
if ($extract->invoke($service,['actions'=>[['name'=>'generate-qr-code-v2','url'=>'https://attacker.example/qr']]])!=='') throw new RuntimeException('Unexpected QR host accepted');
$ref='A1202610130633535HWhrT8NbiID';
$nested='50'.sprintf('%02d',strlen($ref)).$ref;
$qr='62'.sprintf('%02d',strlen($nested)).$nested;
if ($service->getQrisCodeUrl('test',$qr)!==$new) throw new RuntimeException('Stored QR recovery broken');
if ($service->getQrisCodeUrl('test','invalid')!==$old) throw new RuntimeException('Invalid EMV handling broken');
echo "QR v2 preference, legacy fallback, host validation and stored EMV recovery passed\n";
