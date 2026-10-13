<?php
class ApiNavigationController
{
  public function counts()
  {
    header('Cache-Control: private, no-store');
    try { return response(['success'=>true,'data'=>['user_id'=>(int)$_SESSION['user']['id_user'],'counts'=>navigationCounts($_SESSION['user'])]]); }
    catch (Throwable $e) { return response(['success'=>false,'message'=>'Ringkasan aktivitas belum tersedia.'],500); }
  }
}
