<?php

class ApiAturanController
{
  public function __construct() { model('Aturan'); }

  public function publicIndex()
  {
    try { response(['success'=>true,'data'=>getAllAturan(true)]); }
    catch(Throwable $e){ error_log('Aturan public error: '.$e->getMessage()); response(['success'=>false,'message'=>'Gagal memuat aturan.'],500); }
  }

  public function ownerList()
  {
    try { response(['success'=>true,'data'=>getAllAturan(true)]); }
    catch(Throwable $e){ error_log('Aturan owner list error: '.$e->getMessage()); response(['success'=>false,'message'=>'Gagal memuat aturan.'],500); }
  }

  public function ownerByKos()
  {
    $id=(int)query('id_kos'); $user=$_SESSION['user']??[];
    if($id<=0) response(['success'=>false,'message'=>'Kos tidak valid.'],422);
    try { response(['success'=>true,'data'=>getAturanByKos($id,(int)($user['id_user']??0))]); }
    catch(Throwable $e){ error_log('Aturan kos error: '.$e->getMessage()); response(['success'=>false,'message'=>'Gagal memuat aturan kos.'],500); }
  }

  public function adminIndex()
  {
    try { response(['success'=>true,'data'=>getAturanAdmin(trim((string)query('search','')),trim((string)query('status','')))]); }
    catch(Throwable $e){ error_log('Admin aturan list error: '.$e->getMessage()); response(['success'=>false,'message'=>'Gagal memuat aturan.'],500); }
  }
  public function adminShow(){ $row=getAturanById((int)params('id')); if(!$row) response(['success'=>false,'message'=>'Aturan tidak ditemukan.'],404); response(['success'=>true,'data'=>$row]); }
  public function adminStore(){ try{$id=createAturan(input());response(['success'=>true,'message'=>'Aturan berhasil ditambahkan.','data'=>['id_aturan'=>$id]],201);}catch(Throwable $e){response(['success'=>false,'message'=>$e->getMessage()],$e->getCode()?:500);} }
  public function adminUpdate(){ try{updateAturan((int)params('id'),input());response(['success'=>true,'message'=>'Aturan berhasil diperbarui.']);}catch(Throwable $e){response(['success'=>false,'message'=>$e->getMessage()],$e->getCode()?:500);} }
  public function adminDelete(){ try{deleteAturan((int)params('id'));response(['success'=>true,'message'=>'Aturan berhasil dihapus.']);}catch(Throwable $e){response(['success'=>false,'message'=>$e->getMessage()],$e->getCode()?:500);} }
}
